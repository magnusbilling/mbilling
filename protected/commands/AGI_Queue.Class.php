<?php
/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2016 MagnusBilling. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 *
 */

class AGI_Queue
{
	public function callQueue($agi, &$MAGNUS, &$Calc, $result_did, $type = 'queue')
    {
		$agi->verbose("Queue module",5);
		$agi->answer();
		$startTime = time();

		$MAGNUS->destination = $result_did[0]['did'];

		$sql         = "SELECT * FROM pkg_queue WHERE id =".$result_did[0]['id_queue'];
		$agi->verbose($sql,1);
		$resultQueue      = Yii::app()->db->createCommand( $sql )->queryAll();

		$sql         = "SELECT * FROM pkg_user WHERE id =".$resultQueue[0]['id_user'];
		$resultUser  = Yii::app()->db->createCommand( $sql )->queryAll();
		$agi->verbose($sql,1);

		$agi->set_variable("UNIQUEID",$MAGNUS->uniqueid);
		$agi->set_variable("QUEUCALLERID",$MAGNUS->CallerID);
		$agi->set_variable("IDQUEUE",$resultQueue[0]['id']);
		$agi->set_variable("USERNAME",$resultUser[0]['username']);		

		$QueueName  =  $resultQueue[0]['name'];
		$sql = "INSERT INTO pkg_queue_status (id_queue, callId, queue_name, callerId, time, channel, status) VALUES (".$resultQueue[0]['id'].", '".$MAGNUS->uniqueid."', '$QueueName', '".$MAGNUS->CallerID."', '".date('Y-m-d H:i:s')."', '".$MAGNUS->channel."', 'ringing')";
       	$agi->verbose($sql,1);
       	Yii::app()->db->createCommand( $sql )->execute();

       	$agi->set_variable('CHANNEL(language)', $resultQueue[0]['language']);

       	$agi->execute("Queue",$QueueName.',tc,,,,/var/www/html/mbilling/agi.php');
		if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1)
    	{      		
       		$myres = $agi->execute("StopMixMonitor");
        	$agi->verbose( "EXEC StopMixMonitor (" . $MAGNUS->uniqueid . ")",1);
    	}
    	
    	$sql = "DELETE FROM pkg_queue_status WHERE callId = '$MAGNUS->uniqueid' ";
    	$agi->verbose($sql,25);
       	Yii::app()->db->createCommand( $sql )->execute();

       	$stopTime = time();

		$answeredtime = $stopTime - $startTime;

	    $terminatecauseid = 1;

    	$siptransfer = $agi->get_variable("SIPTRANSFER");

    	$linha = exec(" egrep $MAGNUS->uniqueid /var/log/asterisk/queue_log | tail -1" );
    	$linha = explode('|', $linha);

    	$agi->verbose(print_r($linha,true),25);


    	if($linha[4] == 'ABANDON' || $linha[4] == 'EXITEMPTY' || $linha[4] == 'EXITWITHTIMEOUT'){
    		$terminatecauseid = 7;
    	} 
    	
    	$tipo = 8;
    	


       	$agi->verbose('$siptransfer => '. $siptransfer['data'] ,5);
    	if ($siptransfer['data'] != 'yes' && $type == 'queue')
    	{
			$sql = "SELECT id FROM pkg_prefix WHERE prefix = SUBSTRING('".$result_did[0]['did']."',1,length(prefix))LIMIT 1";
	        $resultPrefix = Yii::app()->db->createCommand( $sql )->queryAll();
	        $agi->verbose($sql,25);
	        $id_prefix = $resultPrefix[0]['id'];

	        $fields = "uniqueid, sessionid, id_user, starttime, sessiontime, real_sessiontime, calledstation, terminatecauseid, ".
	        "stoptime, sessionbill, id_plan, id_trunk, src, sipiax, buycost, id_prefix";

	        $value = "'$MAGNUS->uniqueid', '$MAGNUS->channel', ".$resultUser[0]['id'].", CURRENT_TIMESTAMP - INTERVAL $answeredtime SECOND, ".
	        "'$answeredtime', $answeredtime, '".$result_did[0]['did']."', '$terminatecauseid', now(),'$sell_price', ".
	        $resultUser[0]['id_plan']. ", NULL, '$MAGNUS->CallerID', $tipo, '0', $id_prefix";

			$sql = "INSERT INTO pkg_cdr ($fields) VALUES ($value)";
			$agi->verbose($sql,25);
        	Yii::app()->db->createCommand( $sql )->execute();
        	
    	}
    	if ($type == 'queue') {
    		$MAGNUS->hangup($agi);
			exit;
    	}else{
    		return;
    	}
		
    }

    public function recIvrQueue($agi, $MAGNUS, $Calc, $result_did)
    {
    	
    	$agi->verbose('recIvrQueue');
		$operador = preg_replace("/SIP\//", "", $agi->get_variable("MEMBERNAME", true));

		$MAGNUS->uniqueid = $agi->get_variable("UNIQUEID", true);
		$MAGNUS->dnid = $agi->request['agi_extension'];
		$username = $agi->get_variable("USERNAME", true);
		$id_queue = $agi->get_variable("IDQUEUE", true);
		$callerid = $agi->get_variable("QUEUCALLERID", true);
		$oldtime = $agi->get_variable("QEHOLDTIME", true);

		$sql = "UPDATE pkg_queue_status SET status = 'answered', id_agent = (SELECT id FROM pkg_queue_agent_status WHERE agentName = '$operador' AND id_queue = '$id_queue'), oldtime = '$oldtime'  WHERE callId = '$MAGNUS->uniqueid' "; 
		$agi->verbose($sql,25);
       	Yii::app()->db->createCommand( $sql )->execute();

		$agi->verbose("\n\n".$MAGNUS->uniqueid." $operador ATENDEU A CHAMADAS\n\n",6);

		$sql = "SELECT  record_call FROM pkg_user WHERE username = '$username' "; 
		$agi->verbose($sql,25);
       	$resultRecord = Yii::app()->db->createCommand( $sql )->queryAll();
       	$MAGNUS->record_call = $resultRecord[0]['record_call'];
       	

		if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1)
		{
			$agi->verbose( print_r($agi->request, true),25);

			if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1)
			{
				$command_mixmonitor = "MixMonitor {$username}.{$MAGNUS->dnid}.{$MAGNUS->uniqueid}.".$MAGNUS->mix_monitor_format.",b";
				$myres = $agi->execute($command_mixmonitor);
				$agi->verbose( $command_mixmonitor,8);
			}
		}
		exit;
	}
}

?>