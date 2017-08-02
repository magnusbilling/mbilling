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
//not check credit and send call to any number, active or inactive
class MasiveCall3Command extends CConsoleCommand 
{


	public function run($args)
	{	
		if (isset($args[0])){
			if($args[0] == 'log')
				define('DEBUG', 1);
			elseif ($args[0] == 'logAll') {
				define('DEBUG', 2);
			}
		}			
		else
			define('DEBUG', 0);


		if (!defined('PID'))
			define("PID", "/var/run/magnus/MasiveCall3Pid.php");


		if (Process :: isActive()) {
			if(DEBUG >= 1) echo " PROCESS IS ACTIVE ";
			die();
		} else {
			Process :: activate();
		}
		
		include("/var/www/html/mbilling/protected/commands/AGI.Class.php");


		$UNIX_TIMESTAMP = "UNIX_TIMESTAMP(";

		$tab_day = array(1 => 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
		$num_day = date('N');
		$name_day = $tab_day[$num_day];	


		$sql ="SELECT pkg_campaign.id, frequency, pkg_campaign.name FROM pkg_campaign  JOIN pkg_user  ON pkg_campaign.id_user = pkg_user.id
			WHERE  pkg_campaign.status = 1 
			AND pkg_campaign.startingdate <= CURRENT_TIMESTAMP AND pkg_campaign.expirationdate > CURRENT_TIMESTAMP 
			AND pkg_campaign.$name_day = 1 AND  pkg_campaign.daily_start_time <= CURRENT_TIME  AND pkg_campaign.daily_stop_time > CURRENT_TIME 
			AND  pkg_campaign.type = 1";
		$campaignResult = Yii::app()->db->createCommand($sql)->queryAll();
		if(DEBUG == 2) echo $sql."\n\n\n";
		
		if(DEBUG >= 1) echo "\nFound " . count($campaignResult) . " Campaign\n\n";

		foreach ($campaignResult as $campaign) {

			if(DEBUG >= 1) echo  "SEARCH NUMBER IN CAMPAIGN ". $campaign['name'] ."\n";

			$nbpage = $campaign['frequency'];

			$sql ="SELECT pkg_phonenumber.id as pkg_phonenumber_id, pkg_phonenumber.number, pkg_campaign.id as pkg_campaign_id, pkg_campaign.forward_number,
			pkg_user.id , pkg_user.id_plan, pkg_campaign.id_plan AS campaign_id_plan, pkg_user.username, pkg_campaign.type, pkg_campaign.description, pkg_phonenumber.name, try, pkg_user.credit, restrict_phone
			FROM pkg_phonenumber , pkg_phonebook , pkg_campaign_phonebook, pkg_campaign, pkg_user 
			WHERE pkg_phonenumber.id_phonebook = pkg_phonebook.id AND pkg_campaign_phonebook.id_phonebook = pkg_phonebook.id 
			AND pkg_campaign_phonebook.id_campaign = pkg_campaign.id AND pkg_campaign.id_user = pkg_user.id AND pkg_campaign.status = 1 
			AND pkg_campaign.startingdate <= CURRENT_TIMESTAMP AND pkg_campaign.expirationdate > CURRENT_TIMESTAMP 
			AND pkg_campaign.$name_day = 1 AND  pkg_campaign.daily_start_time <= CURRENT_TIME  AND pkg_campaign.daily_stop_time > CURRENT_TIME 
			AND  pkg_phonenumber.creationdate < CURRENT_TIMESTAMP AND pkg_campaign.type = 1
			AND pkg_campaign.id = ".$campaign['id']." ORDER BY RAND( ) 
			LIMIT 0, $nbpage";
			if(DEBUG == 2) echo $sql."\n\n";
			
			$callResult = Yii::app()->db->createCommand($sql)->queryAll();
			if(DEBUG >= 1) echo 'Found ' . count($callResult) . ' Numbers in Campaign '."\n";
			


			if (count($callResult) == 0)
			{
				if(DEBUG >= 1) echo  "NO PHONE FOR CALL"."\n\n\n";
				continue;
			}

			foreach ($callResult as $phone) 
			{

				$name_number = $phone['name'];
				$destination = $phone['number'];

				if ($phone['campaign_id_plan'] > 0) 
					$phone['id_plan'] = $phone['campaign_id_plan'];

				if($phone['restrict_phone'] == 1){
					$sql = "SELECT * FROM  pkg_campaign_restrict_phone WHERE number = '".$phone['number']."'";
					$resultRestrict = Yii::app()->db->createCommand($sql)->queryAll();
					if(count($resultRestrict) > 0){
						$sql = "UPDATE pkg_phonenumber SET status = 4 WHERE id = ".$phone['pkg_phonenumber_id'];
						Yii::app()->db->createCommand($sql)->execute();
						if(DEBUG >= 1 ) echo  "NUMBER " . $destination . "IS BLOCK\n\n\n";
						continue;
					}						
				}

				
				$destination = Portabilidade :: getDestination($destination, true, false,$callResult[0]['id_plan']);
					

				$sql = "SELECT pkg_rate.id AS idRate, pkg_rate.id_trunk, rt_trunk.trunkcode, rt_trunk.trunkprefix, rt_trunk.removeprefix, rt_trunk.providertech, rt_trunk.inuse, 
								rt_trunk.maxuse, rt_trunk.status, rt_trunk.failover_trunk
						FROM pkg_rate
						LEFT JOIN pkg_plan ON pkg_rate.id_plan=pkg_plan.id
						LEFT JOIN pkg_trunk AS rt_trunk ON pkg_rate.id_trunk=rt_trunk.id
						LEFT JOIN pkg_prefix ON pkg_rate.id_prefix=pkg_prefix.id

						WHERE prefix = SUBSTRING('$destination',1,length(prefix)) and pkg_plan.id='".$phone['id_plan']."' 
						ORDER BY LENGTH(prefix) DESC";
				$callTrunk = Yii::app()->db->createCommand($sql)->queryAll();




				if(count($callTrunk) == 0)
				{
					$sql = "UPDATE pkg_phonenumber SET status = 0 WHERE id = ".$phone['pkg_phonenumber_id'];
						Yii::app()->db->createCommand($sql)->execute();
					if(DEBUG >= 1 ) echo " NO FOUND RATE TO CALL ".$phone['username']."  DESTINATION $destination \n\n";
					continue;
				}
				
				$idTrunk = $callTrunk[0]['id_trunk'];
				$trunkcode = $callTrunk[0]['trunkcode'];
				$trunkprefix = $callTrunk[0]['trunkprefix'];
				$removeprefix = $callTrunk[0]['removeprefix'];
				$providertech = $callTrunk[0]['providertech'];
				$inuse = $callTrunk[0]['inuse'];
				$maxuse = $callTrunk[0]['maxuse'];
				$status = $callTrunk[0]['status'];
				$failover_trunk = $callTrunk[0]['failover_trunk'];
				
				$extension = $destination;

				if(substr("$destination", 0, 5) == 11113)/*Retira o techprefix de numeros portados*/
			     {
			          $destination = preg_replace("/11113[0-9][0-9]/","",$destination);
			     }

				//retiro e adiciono os prefixos do tronco
				if(strncmp($destination, $removeprefix, strlen($removeprefix)) == 0)
					$destination = substr($destination, strlen($removeprefix));
				$destination = $trunkprefix.$destination;
				
				$dialstr = "$providertech/$trunkcode/$destination";

				// gerar os arquivos .call
				$call = "Action: Originate\n";
				$call = "Channel: " . $dialstr . "\n";
				$call .= "Callerid: " . $phone["username"] . "\n";
				//$call .= "MaxRetries: 1\n";
				//$call .= "RetryTime: 100\n";
				//$call .= "WaitTime: 45\n";
				$call .= "Context: billing\n";
				$call .= "Extension: " . $extension . "\n";
				$call .= "Priority: 1\n";
				$call .= "Set:CALLED=" . $extension . "\n";
				$call .= "Set:USERNAME=" . $phone["username"] . "\n";
				$call .= "Set:IDCARD=" . $phone["id"] . "\n";
				$call .= "Set:PHONENUMBER_ID=" . $phone['pkg_phonenumber_id'] . "\n";
				$call .= "Set:CAMPAIGN_ID=" . $phone['pkg_campaign_id'] . "\n";
				$call .= "Set:RATE_ID=" . $callTrunk[0]['idRate'] . "\n";

				if(DEBUG == 2 ) echo  $call."\n\n";
		
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

				$sql = "UPDATE pkg_phonenumber SET try = try + 1 WHERE id = ".$phone['pkg_phonenumber_id'];
				Yii::app()->db->createCommand($sql)->execute();								
			}
		}				
	}	
}
?>