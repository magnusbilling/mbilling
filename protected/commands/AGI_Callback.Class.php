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

class AGI_Callback
{
	function callbackCID($agi,$MAGNUS, $Calc, $mydnid){
	    	$agi->verbose("MAGNUS CID CALLBACK");
	    	$MAGNUS->agiconfig['cid_enable']=1;

	    	if ($MAGNUS->dnid == 'failed' || !is_numeric($MAGNUS->dnid)) {
			    $agi->verbose("Hangup becouse dnid is OutgoingSpoolFailed",25);
			    $MAGNUS->hangup($agi);
			    exit;
			}	

	    	$agi->verbose('CallerID '.$MAGNUS->CallerID);   	
	    	

	    	if (strlen($MAGNUS->CallerID)>1 && is_numeric($MAGNUS->CallerID)) {
	        	$cia_res = AGI_Authenticate::authenticateUser($agi, $MAGNUS);
	  

	        	if ($cia_res==0) {

	        		
		    		if (substr($MAGNUS->dnid , 0,4) == '0000'){
		    			$MAGNUS->destination = substr($MAGNUS->dnid ,4);
		    		}	    			
		    		elseif (substr($MAGNUS->dnid , 0,3) == '000' ){
		    			$MAGNUS->destination = $MAGNUS->CallerID;
		    		}
		    			
		    		else{
		    			$MAGNUS->destination = $MAGNUS->countryCode.$MAGNUS->CallerID;
		    		}
	            		


	            	$agi->verbose('$MAGNUS->destination =>' .$MAGNUS->destination);
	            	
	          		/*protabilidade*/
					$MAGNUS->destination = $MAGNUS->transform_number_ar_br($agi, $MAGNUS->destination);


	          		$agi->verbose($MAGNUS->countryCode,15);
		          	$agi->verbose($MAGNUS->destination,15);
		          	$resfindrate = SearchTariff::find($MAGNUS, $agi, $Calc);
		          	if(substr("$MAGNUS->destination", 0, 4) == 1111)
		          		$MAGNUS->destination = str_replace(substr($MAGNUS->destination, 0, 7), "", $MAGNUS->destination);
		     

	          		$Calc->usedratecard = 0;
	          		if ($resfindrate != 0){
	               		$res_all_calcultimeout = $Calc->calculateAllTimeout($MAGNUS, $MAGNUS->credit,$agi);
		               	if ($res_all_calcultimeout)
		               	{
		                    $destination 	 = $MAGNUS->destination;
		                    $providertech   = $Calc->tariffObj[0]['rc_providertech'];
		                    $ipaddress      = $Calc->tariffObj[0]['rc_providerip'];
		                    $removeprefix   = $Calc->tariffObj[0]['rc_removeprefix'];
		                    $prefix         = $Calc->tariffObj[0]['rc_trunkprefix'];

		                    if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0)
		                         $destination = substr($destination, strlen($removeprefix));


		                    $dialstr = "$providertech/$ipaddress/$prefix$destination";
		                   // $dialstr = 'SIP/24315';

		                    // gerar os arquivos .call
		                    $call = "Channel: " . $dialstr . "\n";
		                    $call .= "Callerid: " . $MAGNUS->CallerID . "\n";
		                    $call .= "Context: billing\n";
		                    $call .= "Extension: " . $MAGNUS->destination . "\n";
		                    $call .= "Priority: 1\n";
		                    $call .= "Set:CALLED=" . $MAGNUS->destination. "\n";
		                    $call .= "Set:TARRIFID=" . $Calc->tariffObj[0]['id_rate']. "\n";
		                    $call .= "Set:SELLCOST=" . $Calc->tariffObj[0]['rateinitial']. "\n";
		                    $call .= "Set:BUYCOST=" . $Calc->tariffObj[0]['buyrate']. "\n";
		                    $call .= "Set:CIDCALLBACK=1\n";
		                    $call .= "Set:IDUSER=" . $MAGNUS->id_user. "\n";
		                    $call .= "Set:IDPREFIX=" . $Calc->tariffObj[0]['id_prefix']. "\n";
		                    $call .= "Set:IDTRUNK=" . $Calc->tariffObj[0]['id_trunk']. "\n";
		                    $call .= "Set:IDPLAN=" . $MAGNUS->id_plan. "\n";
		                    if (substr($MAGNUS->dnid , 0,3) == '000') {
		                    	$call .= "Set:SECCALL=" . $MAGNUS->destination = substr($MAGNUS->dnid,3). "\n";
		                    }
		                    $agi->verbose($call);

		                    $aleatorio = str_replace(" ", "", microtime(true));
		                    $arquivo_call = "/tmp/$aleatorio.call";
		                    $fp = fopen("$arquivo_call", "a+");
		                    fwrite($fp, $call);
		                    fclose($fp);

		                    touch("$arquivo_call", mktime(date("H"), date("i"), date("s") + 1, date("m"), date("d"), date("Y")));
		                    chown("$arquivo_call", "asterisk");
		                    chgrp("$arquivo_call", "asterisk");
		                    chmod("$arquivo_call", 0755);
		                    exec("mv $arquivo_call /var/spool/asterisk/outgoing/$aleatorio.call");
		                    $agi->answer();
		                    $MAGNUS->hangup($agi);
		                    exit;              
		                }

		                $MAGNUS->hangup($agi);
	            	}
	            	else
	            	{
	                $agi->verbose("NO TARIFF FOUND");
	                $MAGNUS->hangup($agi);
	            	}
	        	}
	        	else
	        		$MAGNUS->hangup($agi);
	    	}else
	    		$MAGNUS->hangup($agi);
	}

	function callback0800($agi,$MAGNUS, $Calc, $mydnid){

		$agi->verbose("MAGNUS 0800 CALLBACK");

	    	if ($MAGNUS->dnid == 'failed' || !is_numeric($MAGNUS->dnid)) {
		    $agi->verbose("Hangup becouse dnid is OutgoingSpoolFailed",25);
		    $MAGNUS->hangup($agi);
		    exit;
		}
		$destination = $MAGNUS->CallerID;


		$sql = "SELECT config_value FROM pkg_configuration  WHERE config_key = 'callback_remove_prefix'";
		$resultCallbackRemove = $result = Yii::app()->db->createCommand( $sql )->queryAll();
		$removeprefix = $resultCallbackRemove[0]['config_value'];
		if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0)          
              $destination = substr($destination, strlen($removeprefix));
          

          $sql = "SELECT config_value FROM pkg_configuration  WHERE config_key = 'callback_add_prefix'";
		$resultCallbackAdd = $result = Yii::app()->db->createCommand( $sql )->queryAll();
		$addprefix = $resultCallbackAdd[0]['config_value'];
		$destination = $addprefix.$destination;

		$user = $mydnid[0]['username'];

		$sql = "SELECT name FROM pkg_sip WHERE id_user = ".$mydnid[0]['id_user'];
		$resultUser = $result = Yii::app()->db->createCommand( $sql )->queryAll();
		if (count($resultUser) < 1) {
			$agi->verbose("Username not have SIP ACCOUNT");
			$MAGNUS->hangup($agi);
			return;
		}
		$destino = $resultUser[0]['name'];
		$id_user = $mydnid[0]['id_user'];

		$sql = "SELECT config_value FROM pkg_configuration  WHERE config_key = 'answer_callback'";
		$resultAnswerCallback = $result = Yii::app()->db->createCommand( $sql )->queryAll();

		if($resultAnswerCallback[0]['config_value'] == 1){
			$agi->answer();
            	sleep(2);
			$agi->stream_file('prepaid-callback', '#');
		}

		$dialstr = "SIP/$destino";


		// gerar os arquivos .call
		$call = "Channel: " . $dialstr . "\n"; 
		$call .= "Callerid: " . $destination . "\n";
		$call .= "Context: billing\n";
		$call .= "Extension: " . $user . "\n";
		$call .= "Priority: 1\n";
		$call .= "Set:IDUSER=" . $id_user. "\n";
		$call .= "Set:SECCALL=" . $destination. "\n";


		$agi->verbose($call);

		$aleatorio = str_replace(" ", "", microtime(true));
		$arquivo_call = "/tmp/$aleatorio.call";
		$fp = fopen("$arquivo_call", "a+");
		fwrite($fp, $call);
		fclose($fp);

		touch("$arquivo_call", mktime(date("H"), date("i"), date("s") + 1, date("m"), date("d"), date("Y")));
		chown("$arquivo_call", "asterisk");
		chgrp("$arquivo_call", "asterisk");
		chmod("$arquivo_call", 0755);

		system("mv $arquivo_call /var/spool/asterisk/outgoing/$aleatorio.call");
		$MAGNUS->hangup($agi);

	}


	function chargeFistCall($agi,$MAGNUS, $Calc, $sessiontime = 0)
	{

		if ($MAGNUS->dnid == 'failed' || !is_numeric($MAGNUS->dnid)) {
		     $agi->verbose("Hangup becouse dnid is OutgoingSpoolFailed",25);
		     $MAGNUS->hangup($agi);
		     exit;
		}

		if($agi->get_variable("IDPREFIX", true) > 0){		

		    $agi->verbose("Callback: CHARGE FOR THE 1ST LEG callback_username=$MAGNUS->username",10);
		    $sell = $agi->get_variable("SELLCOST", true);
		    $buycost = $agi->get_variable("BUYCOST", true);
		    $iduser = $agi->get_variable("IDUSER", true);
		    $called = $agi->get_variable("CALLED", true);
		    $idPlan = $agi->get_variable("IDPLAN", true);
		    $idTrunk = $agi->get_variable("IDTRUNK", true);
		    $idPrefix = $agi->get_variable("IDPREFIX", true);
		    $called = $agi->get_variable("CALLED", true);



		    if($sessiontime==0){

		    	$sell30 = $sell / 2;
		    	$buycost30 = $buycos / 2 ;

		    	//desconto 1 minuto assim que o cliente atende a chamada
		    	$sql = "UPDATE pkg_user SET credit= credit - " . $MAGNUS->round_precision(abs($sell30)) . " WHERE id='" . $iduser . "'";
			    Yii::app()->db->createCommand( $sql )->execute();
			    $agi->verbose($sql);
			    $sessiontime1fsLeg = 30;

			     $fields = "uniqueid, sessionid, id_user, starttime, sessiontime, real_sessiontime, calledstation, terminatecauseid, ".
			        "stoptime, sessionbill, id_plan, id_trunk, src, sipiax, buycost, id_prefix";

			    $value = "'$MAGNUS->uniqueid', '$MAGNUS->channel', $iduser, SUBDATE(CURRENT_TIMESTAMP, INTERVAL $sessiontime1fsLeg SECOND), ".
			    "'$sessiontime1fsLeg', $sessiontime1fsLeg, '$called', '1', now(),'" . $MAGNUS->round_precision(abs($sell30)) . "', ".
			    "$idPlan, $idTrunk, '$called', '4', '$buycost30', $idPrefix";
			    
			    $sql = "INSERT INTO pkg_cdr ($fields) VALUES ($value)";
			    $agi->verbose($sql);
			    Yii::app()->db->createCommand( $sql )->execute();
	            $Calc->idCallCallBack = Yii::app()->db->lastInsertID;
		    }
		    elseif($sessiontime){	    	

		    	$selltNew = ($sell / 60) * $sessiontime;

		    	$sessiontime = $sessiontime + 30;
		    	$sell = ($sell / 60) * $sessiontime;
		    	$buycost = ($buycost / 60) * $sessiontime;

		    	$sql = "UPDATE pkg_cdr SET sessiontime = $sessiontime, sessionbill = $sell, buycost =  $buycost WHERE id='" . $Calc->idCallCallBack . "'";
	        	Yii::app()->db->createCommand( $sql )->execute();
	        	$agi->verbose($sql,25);

	        	$sql = "UPDATE pkg_user SET credit= credit - " . $MAGNUS->round_precision(abs($selltNew)) . " WHERE id='" . $MAGNUS->id_user . "'";
			    Yii::app()->db->createCommand( $sql )->execute();
			    $agi->verbose($sql,25);
		    }
	     }		
	}
}

?>