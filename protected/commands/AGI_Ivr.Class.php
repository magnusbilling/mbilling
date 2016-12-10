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

class AGI_Ivr
{
	public function callIvr($agi, &$MAGNUS, &$Calc, $result_did, $type = 'ivr')
    {
		$agi->verbose("Ivr module",5);
		$agi->answer();
		$startTime = time();

		$MAGNUS->destination = $result_did[0]['did'];

		$sql         = "SELECT * FROM pkg_ivr WHERE id =".$result_did[0]['id_ivr'];
		$agi->verbose($sql,1);
		$result      = Yii::app()->db->createCommand( $sql )->queryAll();

		$sql         = "SELECT * FROM pkg_user WHERE id =".$result[0]['id_user'];
		$resultUser  = Yii::app()->db->createCommand( $sql )->queryAll();
		$agi->verbose($sql,1);

		$username    = $resultUser[0]['username'];
		$MAGNUS->record_call    = $resultUser[0]['record_call'];      
		$monFriStart = $result[0]['monFriStart'];
		$monFriStop  = $result[0]['monFriStop'];
		$satStart    = $result[0]['satStart'];
		$satStop     = $result[0]['satStop'];
		$sunStart    = $result[0]['sunStart'];
		$sunStop     = $result[0]['sunStop'];
		$nowDay      = date('D');
		$nowHour     = date('H:i:s');


		if ($nowDay != 'Sun' &&  $nowDay != 'Sat')
		{
		  if ($nowHour > $monFriStart && $nowHour < $monFriStop)
		  {
		      $agi->verbose( "MonFri");
		      $work = true;
		  }
		}

		if ($nowDay == 'Sat')
		{
		  if ($nowHour > $satStart && $nowHour < $satStop)
		  {
		      $agi->verbose( "Sat");
		      $work = true;
		  }
		}

		if ($nowDay == 'Sun')
		{
		  if ($nowHour > $sunStart && $nowHour < $sunStop) 
		  {
		      $agi->verbose( "Sun");
		      $work = true;
		  }
		}

		//esta dentro do hario de atencao
		if ($work){
			$audioURA = 'idIvrDidWork_';
			$optionName = 'option_';
		}			
		else{
			$audioURA = 'idIvrDidNoWork_';
			$optionName = 'option_out_';
		}			

		$agi->verbose( print_r($agi->request, true),25);
		

		$continue = true;
		$insertCDR = false;
		$i =0;
		while ($continue == true) {
			$agi->verbose("EXECUTE IVR ". $result[0]['name']);
			$i++;

			if ($i == 10) {
				$continue = false;
				break;
			}
			$audio = "/var/www/html/mbilling/resources/sounds/".$audioURA.$result_did[0]['id_ivr'];
			$res_dtmf = $agi->get_data($audio, 3000, 1);
			$agi->verbose( print_r($res_dtmf, true),5);
			$option = $res_dtmf['result'];
			$agi->verbose( 'option'. $option);
			//se nao marcou
			if (strlen($option) < 1) 
			{
				$agi->verbose( 'DEFAULT OPTION');
				$option = '10';
				$continue = false;
			}
			//se marca uma opÃ§ao que esta em branco
			else if ($result[0][$optionName.$option] == '') 
			{
				$agi->verbose( 'NUMBER INVALID');
				$agi->stream_file('prepaid-invalid-digits', '#');
				continue;
			}
			


			$dtmf = explode(("|"), $result[0][$optionName.$option]);
			$optionType = $dtmf[0];
			$optionValue = $dtmf[1];
			$agi->verbose("CUSTOMER PRESS $optionType -> $optionValue");

			//check if channel is available
			$asmanager = new AGI_AsteriskManager();
	          $asmanager->connect('localhost', 'magnus', 'magnussolution');	            
	          $resultChannel = $asmanager->command("core show channel ".$MAGNUS->channel);
	          $arr = explode("\n", $resultChannel["data"]);
            	foreach ($arr as $key => $temp) {
                	if (preg_match("/Blocking in/",$temp) )
                	{
                		$arr3 = explode("Blocking in:", $temp);
                    	if (preg_match("/Not Blocking/", $arr3[1])) {
                    		$agi->verbose("Channel unavailable");
                    		$optionType = 'hangup';
                    	}
                	}
            	}      

	          $asmanager->disconnect();
	     

		  	if($optionType == 'sip') // QUEUE
		  	{
		  		$insertCDR = true;
				$sql = "SELECT name FROM pkg_sip WHERE id = ". $optionValue;	
				$agi->verbose($sql,25);		
				$resultSIP      = Yii::app()->db->createCommand( $sql )->queryAll();

				$dialparams = $dialparams = $MAGNUS->agiconfig['dialcommand_param_sipiax_friend'];
				$dialparams = str_replace("%timeout%", 3600, $dialparams);
				$dialparams = str_replace("%timeoutsec%", 3600, $dialparams);
				$dialstr = 'SIP/'.$resultSIP[0]['name'] . $dialparams;
				$agi->verbose( $dialstr,25);
				if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1)
				{
					$command_mixmonitor = "MixMonitor {$username}.{$MAGNUS->destination}.{$MAGNUS->uniqueid}.".$MAGNUS->mix_monitor_format.",b";
					$myres = $agi->execute($command_mixmonitor);
					$agi->verbose( $command_mixmonitor,5);
				}

				$myres = $MAGNUS->run_dial($agi, $dialstr);

				$dialstatus = $agi->get_variable("DIALSTATUS");
                $dialstatus = $dialstatus['data'];

               	if ($dialstatus == "NOANSWER")
                {
                    $answeredtime = 0;
                    $agi->stream_file('prepaid-callfollowme', '#');
                    continue;
                }
                elseif (($dialstatus == "BUSY" || $dialstatus == "CHANUNAVAIL") || ($dialstatus == "CONGESTION"))
                {
                	$agi->stream_file('prepaid-isbusy', '#');
                    continue;
                }
                else{
                	break;
                }

				
			    		
		  	}
		  	else if($optionType == 'repeat') // CUSTOM
		  	{
		  		$agi->verbose("repetir IVR");	  		
		  		continue;
		  	}
		  	else if(preg_match("/hangup/", $optionType )) // hangup
		  	{
		  		$agi->verbose("Hangup IVR");	
		  		$insertCDR	= true;
		  		break;
		  	}
		  	else if($optionType == 'group') // CUSTOM
		  	{	
		  		$agi->verbose("Ccall group $group ",1);
                $max_long = 2147483647;
                $time2call = 3600;
                $dialparams = $MAGNUS->agiconfig['dialcommand_param_call_2did'];
                $dialparams = str_replace("%timeout%", min($time2call * 1000, $max_long), $dialparams);
                $dialparams = str_replace("%timeoutsec%", min($time2call, $max_long), $dialparams);

                $sql = "SELECT name FROM pkg_sip WHERE `group` = '". $optionValue."'";
                $agi->verbose( $sql,1);
                $resultGroup = Yii::app()->db->createCommand( $sql )->queryAll();
                if (count($resultGroup) == 0) {
                	$agi->verbose( 'GROUP NOT FOUND');
					$agi->stream_file('prepaid-invalid-digits', '#');
					continue;
                }
                $group = '';
                foreach ($resultGroup as $key => $value) {
                    $group .= "SIP/".$value['name']."&";
                }
                $dialstr = substr($group, 0,-1).$dialparams;
                
                $agi->verbose( "DIAL $dialstr",1);
                $MAGNUS->run_dial($agi, $dialstr);

		  		$insertCDR	= true;
		  	}
		  	else if(preg_match("/custom/", $optionType )) // CUSTOM
		  	{	
		  		$insertCDR	= true;   		
		  		$myres = $MAGNUS->run_dial($agi, $optionValue);
		  	}
		  	else if($optionType == 'ivr') // QUEUE
		  	{
		  		$result_did[0]['id_ivr'] = $optionValue;
				AGI_Ivr::callIvr($agi, $MAGNUS, $Calc, $result_did, $type);			
		  	}
		  	else if($optionType == 'queue') // QUEUE
		  	{
		  		$insertCDR	= false;
				$result_did[0]['id_queue']  = $optionValue;
				AGI_Queue::callQueue($agi, $MAGNUS, $Calc, $result_did, $type);			
		  	}
		  	else if(preg_match("/^number/", $optionType ))//envia para um fixo ou celular
		  	{	
		  		$insertCDR	= false; 
		  		$agi->verbose("CALL number $optionValue");
		      	$MAGNUS->call_did($agi, $Calc, $result_did, $optionValue);
		  	}



		  	$agi->verbose("FIM do loop");

		  	$continue = false;
		  	$insertCDR	= true;

	  	}		

		$stopTime = time();

		$answeredtime = $stopTime - $startTime;

    	$terminatecauseid = 1;

		$siptransfer = $agi->get_variable("SIPTRANSFER");

    	$linha = end( file( '/var/log/asterisk/queue_log' ) );
    	$linha = explode('|', $linha);
    	$agi->verbose(print_r($linha,true),25);	    	
    	
    	$tipo = 9;
    	
    	if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1)
	    {
	        $myres = $agi->execute("StopMixMonitor");
	        $agi->verbose( "EXEC StopMixMonitor (" . $MAGNUS->uniqueid . ")",5);
	    }


		$agi->verbose('$siptransfer => '. $siptransfer['data'] ,5);

    	if ($siptransfer['data'] != 'yes' && $insertCDR == true && $type == 'ivr')
    	{
    		$MAGNUS->call_did_billing($agi, $Calc, $result_did[0], $answeredtime, $dialstatus); 		
        	
    	}

    	if ($type == 'ivr')
    		$MAGNUS->hangup($agi);
    	else
		return;
    }
}

?>