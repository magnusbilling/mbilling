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
class MasiveCallCommand extends CConsoleCommand 
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
		

		if (!defined('PID')){			
			exec("mkdir -p /var/run/magnus/");
			
			define("PID", "/var/run/magnus/MasiveCallPid.php");
		}
			


		if (Process :: isActive()) {
			if(DEBUG >= 1) echo " PROCESS IS ACTIVE ";
			die();
		} else {
			Process :: activate();
		}
		
		
		$UNIX_TIMESTAMP = "UNIX_TIMESTAMP(";

		$tab_day = array(1 => 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
		$num_day = date('N');
		$name_day = $tab_day[$num_day];	


		$sql ="SELECT pkg_campaign.id, frequency, pkg_campaign.name FROM pkg_campaign  JOIN pkg_user  ON pkg_campaign.id_user = pkg_user.id
			WHERE  pkg_campaign.status = 1 
			AND pkg_campaign.startingdate <= '".date('Y-m-d H:i:s')."' AND pkg_campaign.expirationdate > '".date('Y-m-d H:i:s')."' 
			AND pkg_campaign.$name_day = 1 AND  pkg_campaign.daily_start_time <= '".date('H:i:s')."'  AND pkg_campaign.daily_stop_time > '".date('H:i:s')."' 
			AND pkg_campaign.type = 1";
		$campaignResult = Yii::app()->db->createCommand($sql)->queryAll();
		if(DEBUG == 2) echo $sql."\n\n\n";
		
		if(DEBUG >= 1) echo "\nFound " . count($campaignResult) . " Campaign\n\n";

		foreach ($campaignResult as $campaign) {


			



			if(DEBUG >= 1) echo  "SEARCH NUMBER IN CAMPAIGN ". $campaign['name'] ."\n";	

			$nbpage = $campaign['frequency'];

			$sql ="SELECT pkg_phonenumber.id as pkg_phonenumber_id, pkg_phonenumber.number, pkg_campaign.id as pkg_campaign_id, pkg_campaign.forward_number,  pkg_campaign.name as pkg_campaign_name,
			pkg_user.id AS id_user, pkg_user.id_plan, pkg_campaign.id_plan AS campaign_id_plan, pkg_user.username, pkg_campaign.type, pkg_campaign.description, pkg_phonenumber.name, pkg_phonenumber.city AS number_city, try, restrict_phone , pkg_user.id_user AS id_agent
			FROM pkg_phonenumber , pkg_phonebook , pkg_campaign_phonebook, pkg_campaign, pkg_user 
			WHERE pkg_phonenumber.id_phonebook = pkg_phonebook.id AND pkg_campaign_phonebook.id_phonebook = pkg_phonebook.id 
			AND pkg_campaign_phonebook.id_campaign = pkg_campaign.id AND pkg_campaign.id_user = pkg_user.id AND pkg_campaign.status = 1 
			AND pkg_campaign.startingdate <= '".date('Y-m-d H:i:s')."' AND pkg_campaign.expirationdate > '".date('Y-m-d H:i:s')."' 
			AND pkg_campaign.$name_day = 1 AND  pkg_campaign.daily_start_time <= '".date('H:i:s')."'  AND pkg_campaign.daily_stop_time > '".date('H:i:s')."' 
			AND pkg_phonenumber.status = 1  AND  pkg_phonenumber.creationdate < '".date('Y-m-d H:i:s')."' AND pkg_campaign.type = 1
			AND pkg_campaign.id = ".$campaign['id']."
			LIMIT 0, $nbpage";
			if(DEBUG == 2) echo $sql."\n\n";
			
			$callResult = Yii::app()->db->createCommand($sql)->queryAll();
			if(DEBUG >= 1) echo 'Found ' . count($callResult) . ' Numbers in Campaign '."\n";
			



			if (count($callResult) == 0)
			{
				if(DEBUG >= 1) echo  "NO PHONE FOR CALL"."\n\n\n";
				continue;
			}

			if ($campaign['frequency'] <= 60) {
				//se for menos de 60 por minutos divido 60 pela frequncia e depois somo o resultado para mandar 1 chamada a cada segundos resultante da divisao.
				$sleep = 60 / $campaign['frequency'];
			
			}else{
				//divido a frequencia por 60 e depois mando o resultado em cada segundo.
				$sleep = $campaign['frequency'] / 60;
			}
			
			$i == 0;
			$ids = '';
			$sleepNext = 1;
			foreach ($callResult as $phone) 
			{
				$i++;		



				if ($phone['campaign_id_plan'] > 0) 
					$phone['id_plan'] = $phone['campaign_id_plan'];				


				if($phone['id_agent'] > 1){
					$id_plan_agent  = $phone['id_plan'];
					$sql = "SELECT credit, username, id_plan FROM pkg_user WHERE id = ".$phone['id_agent'];
					$resultAgent = Yii::app()->db->createCommand($sql)->queryAll();
					$phone['id_plan']  = $resultAgent[0]['id_plan'];
				}else{
					$id_plan_agent = 0;
				}
			

				$name_number = $phone['name'];
				$destination = $phone['number'];


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

				if ($phone['try'] > 1) 
				{
					$sql = "UPDATE pkg_phonenumber SET status = 0 WHERE id = ".$phone['pkg_phonenumber_id'];
					Yii::app()->db->createCommand($sql)->execute();
					if(DEBUG >= 1 ) echo  "DISABLE NUMBER  " . $destination . " AFTER TWO TRYING\n\n\n";
					if(DEBUG == 2 ) echo  $sql."\n\n";
					continue;
				}

				if(CreditUser :: checkGlobalCredit($phone['id_user']) === false)
				{
					if(DEBUG >= 1 ) echo " USER NO CREDIT FOR CALL ".$phone['username']."\n\n\n";
					continue;
				}

				
				$destination = Portabilidade :: getDestination($destination, true, false,$phone['id_plan']);
				

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

				if($callTrunk[0]['status'] == 0 || $phone['try'] > 0 || ($callTrunk[0]['inuse'] >= $callTrunk[0]['maxuse'] && $callTrunk[0]['maxuse'] != -1) )
				{
					$sql            = "SELECT * FROM pkg_trunk WHERE id = '".$callTrunk[0]['failover_trunk']."' ";
					$resultTrunk    = Yii::app()->db->createCommand($sql)->queryAll();

					if (count($resultTrunk) > 0) 
					{
						$idTrunk        = $resultTrunk[0]['id'];
						$trunkcode      = $resultTrunk[0]['trunkcode'];
						$trunkprefix    = $resultTrunk[0]['trunkprefix'];
						$removeprefix   = $resultTrunk[0]['removeprefix'];
						$providertech   = $resultTrunk[0]['providertech'];
						$inuse          = $resultTrunk[0]['inuse'];
						$maxuse         = $resultTrunk[0]['maxuse'];
						$status         = $resultTrunk[0]['status'];
						$failover_trunk = $resultTrunk[0]['failover_trunk'];
					}
					else
					{
						//desativa o numero de falhou na 1º tentativa e nao tem backup
						$sql = "UPDATE pkg_phonenumber SET status = 0 WHERE id = ".$phone['pkg_phonenumber_id'];
						Yii::app()->db->createCommand($sql)->execute();
						continue;
					}
					
				}
				else
				{
					$idTrunk = $callTrunk[0]['id_trunk'];
					$trunkcode = $callTrunk[0]['trunkcode'];
					$trunkprefix = $callTrunk[0]['trunkprefix'];
					$removeprefix = $callTrunk[0]['removeprefix'];
					$providertech = $callTrunk[0]['providertech'];
					$inuse = $callTrunk[0]['inuse'];
					$maxuse = $callTrunk[0]['maxuse'];
					$status = $callTrunk[0]['status'];
					$failover_trunk = $callTrunk[0]['failover_trunk'];
				}
				

				if(substr("$destination", 0, 5) == 11113)/*Retira o techprefix de numeros portados*/
			     {
			          $destination = preg_replace("/11113[0-9][0-9]/","",$destination);
			     }
			     $extension = $destination;
			     
				//retiro e adiciono os prefixos do tronco
				if(strncmp($destination, $removeprefix, strlen($removeprefix)) == 0)
					$destination = substr($destination, strlen($removeprefix));
				$destination = $trunkprefix.$destination;


				$sql = "SELECT callerid FROM pkg_sip WHERE id_user =".$phone["id_user"];
				$resultCallerID    = Yii::app()->db->createCommand($sql)->queryAll();
			

				$dialstr = "$providertech/$trunkcode/$destination";

				// gerar os arquivos .call
				$call = "Action: Originate\n";
				$call = "Channel: " . $dialstr . "\n";
				$call .= "Callerid: " . $resultCallerID[0]["callerid"] . "\n";
				$call .= "Account:  MC!" . $phone["pkg_campaign_name"] . "\n";
				//$call .= "MaxRetries: 1\n";
				//$call .= "RetryTime: 100\n";
				//$call .= "WaitTime: 45\n";
				$call .= "Context: billing\n";
				$call .= "Extension: " . $extension . "\n";
				$call .= "Priority: 1\n";
				$call .= "Set:CALLED=" . $extension . "\n";
				$call .= "Set:USERNAME=" . $phone["username"] . "\n";
				$call .= "Set:IDCARD=" . $phone["id_user"] . "\n";
				$call .= "Set:PHONENUMBER_ID=" . $phone['pkg_phonenumber_id'] . "\n";
				$call .= "Set:PHONENUMBER_CITY=" . $phone['number_city'] . "\n";
				$call .= "Set:CAMPAIGN_ID=" . $phone['pkg_campaign_id'] . "\n";
				$call .= "Set:RATE_ID=" . $callTrunk[0]['idRate'] . "\n";
				$call .= "Set:AGENT_ID=" . $phone['id_agent'] . "\n";
				$call .= "Set:AGENT_ID_PLAN=" . $id_plan_agent. "\n";

				if(DEBUG == 2 ) echo  $call."\n\n";
		
	            	$aleatorio = str_replace(" ", "", microtime(true));
				$arquivo_call = "/tmp/$aleatorio.call";
				$fp = fopen("$arquivo_call", "a+");
				fwrite($fp, $call);
				fclose($fp);				

			
				touch("$arquivo_call", mktime(date("H"), date("i"), date("s") + $sleepNext, date("m"), date("d"), date("Y")));
				chown("$arquivo_call", "asterisk");
				chgrp("$arquivo_call", "asterisk");
				chmod("$arquivo_call", 0755);
				exec("mv $arquivo_call /var/spool/asterisk/outgoing/$aleatorio.call");
				

				if ($campaign['frequency'] <= 60) {
					$sleepNext += $sleep;
				}else{
					//a cada multiplo do resultado, passo para o proximo segundo
					if (($i % $sleep) == 0)
						$sleepNext += 1;
				}

				
				$ids.= $phone['pkg_phonenumber_id'].',';
							
			}
			$sql = "UPDATE pkg_phonenumber SET  status = 2, try = try + 1 WHERE id IN (".substr($ids, 0,-1).")";
			Yii::app()->db->createCommand($sql)->execute();	
			echo "Campain ".$campaign['name'] ." sent ".$i." calls \n\n";
		}				
	}	
}
?>