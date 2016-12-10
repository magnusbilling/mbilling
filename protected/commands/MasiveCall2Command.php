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
class MasiveCall2Command extends CConsoleCommand 
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
			define("PID", "/var/run/magnus/MasiveCall2Pid.php");

		if(DEBUG == 0){
			if (Process :: isActive()) {
					echo " PROCESS IS ACTIVE ";
					Yii::log(" PROCESS IS ACTIVE ", 'error');				
				die();
			} else {
				Process :: activate();
			}
		}
		


		Yii::log(" iniciando o script ", 'error');

		$UNIX_TIMESTAMP = "UNIX_TIMESTAMP(";

		$tab_day = array(1 => 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
		$num_day = date('N');
		$name_day = $tab_day[$num_day];	

		$totalCalls = 0;

		for ($i=0; $i < 55; $i++) {



			//evita que o loop demore muito
			echo date("s")."\n";
			if (date("s") > 55){
				echo "Ligações geradas total $totalCalls";
				$fp = fopen("/root/campanha.log", "a");
				$escreve = fwrite($fp, date('Y-m-d H:i:s'). " => Ligações geradas total $totalCalls\n");
				fclose($fp);
				exit;
			}

			sleep(1);
			$sql ="SELECT pkg_campaign.id, frequency, pkg_campaign.name FROM pkg_campaign  JOIN pkg_user  ON pkg_campaign.id_user = pkg_user.id
				WHERE  pkg_campaign.status = 1 
				AND pkg_campaign.startingdate <= CURRENT_TIMESTAMP AND pkg_campaign.expirationdate > CURRENT_TIMESTAMP 
				AND pkg_campaign.$name_day = 1 AND  pkg_campaign.daily_start_time <= CURRENT_TIME  AND pkg_campaign.daily_stop_time > CURRENT_TIME 
				AND pkg_user.credit > 1 AND pkg_campaign.type = 1";
			$campaignResult = Yii::app()->db->createCommand($sql)->queryAll();
			if(DEBUG == 2) echo $sql."\n\n\n";
			
			if(DEBUG >= 1) echo "\nFound " . count($campaignResult) . " Campaign\n\n";

			
			foreach ($campaignResult as $campaign) {

				if(DEBUG >= 1) echo  "SEARCH NUMBER IN CAMPAIGN ". $campaign['name'] ."\n";

				$nbpage = $campaign['frequency'];
				//$nbpage = 10;

				$sql ="SELECT pkg_phonenumber.id as pkg_phonenumber_id, pkg_phonenumber.number, pkg_campaign.id as pkg_campaign_id, pkg_campaign.forward_number, pkg_phonenumber.id_phonebook AS id_phonebook,
				pkg_user.id , pkg_user.id_plan, pkg_user.username, pkg_campaign.type, pkg_campaign.description, pkg_phonenumber.name, pkg_phonenumber.city AS number_city, try, pkg_user.credit, restrict_phone, pkg_user.id_user AS id_agent
				FROM pkg_phonenumber , pkg_phonebook , pkg_campaign_phonebook, pkg_campaign, pkg_user 
				WHERE pkg_phonenumber.id_phonebook = pkg_phonebook.id AND pkg_campaign_phonebook.id_phonebook = pkg_phonebook.id 
				AND pkg_campaign_phonebook.id_campaign = pkg_campaign.id AND pkg_campaign.id_user = pkg_user.id AND pkg_campaign.status = 1 
				AND pkg_campaign.startingdate <= CURRENT_TIMESTAMP AND pkg_campaign.expirationdate > CURRENT_TIMESTAMP 
				AND pkg_campaign.$name_day = 1 AND  pkg_campaign.daily_start_time <= CURRENT_TIME  AND pkg_campaign.daily_stop_time > CURRENT_TIME 
				AND pkg_phonenumber.status = 1  AND  pkg_phonenumber.creationdate < CURRENT_TIMESTAMP AND pkg_user.credit > 1 AND pkg_campaign.type = 1
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

				if($callResult[0]['id_agent'] > 1){

					$id_plan_agent  = $callResult[0]['id_plan'];

					$sql = "SELECT credit, username, id_plan FROM pkg_user WHERE id = ".$callResult[0]['id_agent'];
					$resultAgent = Yii::app()->db->createCommand($sql)->queryAll();
					$callResult[0]['id_plan']  = $resultAgent[0]['id_plan'];

					if ($resultAgent[0]['credit'] < 1) {
						if(DEBUG >= 1) echo  "AGENT ".$resultAgent[0]['username']." HAVE NOT ENOUGH CREDIT"."\n\n\n";
						continue;
					}
				}else{
					$id_plan_agent = 0;
				}

				$limitTrunk = 0;
				foreach ($callResult as $phone) 
				{
					if ($phone['number'] == '') {
						$sql = "UPDATE pkg_phonenumber SET status = 0 WHERE id = ".$phone['pkg_phonenumber_id'];
						echo $sql;
						Yii::app()->db->createCommand($sql)->execute();
						continue;
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
					

					if($phone['credit'] < 2)
					{
						if(DEBUG >= 1 ) echo " USER NO CREDIT FOR CALL ".$phone['username']."\n\n\n";
						continue;
					}

					
				
					$destination = Portabilidade :: getDestination($destination, true, false,$callResult[0]['id_plan']);
							


					$max_len_prefix = 11;
					$prefixclause = '(';
			        while ($max_len_prefix > 1)
			        {   
			        	if($max_len_prefix == 4 && substr($destination, 0,4) == '1111'){
			        		break;
			        	}    
			            $prefixclause .= "prefix='" . substr($destination, 0, $max_len_prefix) . "' OR ";
			            $max_len_prefix--;
			        }
			        $prefixclause = substr($prefixclause, 0, -3).")";
	
					$sql = "SELECT pkg_rate.id AS idRate, pkg_rate.id_trunk, rt_trunk.trunkcode, rt_trunk.trunkprefix, rt_trunk.removeprefix, rt_trunk.providertech, rt_trunk.inuse, 
									rt_trunk.maxuse, rt_trunk.status, rt_trunk.failover_trunk
							FROM pkg_rate
							LEFT JOIN pkg_plan ON pkg_rate.id_plan=pkg_plan.id
							LEFT JOIN pkg_trunk AS rt_trunk ON pkg_rate.id_trunk=rt_trunk.id
							LEFT JOIN pkg_prefix ON pkg_rate.id_prefix=pkg_prefix.id

							WHERE $prefixclause and pkg_plan.id='".$callResult[0]['id_plan']."' 
							ORDER BY pkg_prefix.prefix DESC";
					$callTrunk = Yii::app()->db->createCommand($sql)->queryAll();
					
					if(count($callTrunk) == 0)
					{
						if(DEBUG >= 1 )
							echo($sql);

						$sql = "UPDATE pkg_phonenumber SET status = 0 WHERE id = ".$phone['pkg_phonenumber_id'];
						Yii::app()->db->createCommand($sql)->execute();
						
						if(DEBUG >= 1 ) echo " NO FOUND RATE TO CALL ".$phone['username']."  DESTINATION $destination \n\n";
						continue;
					}
					//$callTrunk[0]['maxuse'] = 5;

					$callTrunk[0]['maxuse'] = $callTrunk[0]['maxuse'] > 0 ? ceil($callTrunk[0]['maxuse'] / 55) : $callTrunk[0]['maxuse'];


					echo 'TRONCO PRICIPAL LIMITE => ' . $callTrunk[0]['maxuse']."\n";

					if( $limitTrunk > 0 && $limitTrunk > $callTrunk[0]['maxuse'] && $callTrunk[0]['maxuse'] > 0 && $callTrunk[0]['failover_trunk'] > 0 ){

						echo $destination . " limite do tronco principal superador, passar para o tronco backup\n";
						//$callTrunk[0]['failover_trunk'] = 2;

						$sql            = "SELECT * FROM pkg_trunk WHERE id = '".$callTrunk[0]['failover_trunk']."' ";
						$resultTrunk    = Yii::app()->db->createCommand($sql)->queryAll();

						$limitTrunkBackup = $resultTrunk[0]['maxuse'] > 0 ? ceil($resultTrunk[0]['maxuse'] / 55) : $resultTrunk[0]['maxuse'];
						echo 'TRONCO BACKUP LIMITE => ' . $limitTrunkBackup."\n";
						if($limitTrunkBackup > $resultTrunk[0]['maxuse']  ){
							echo "desativa  o os resto dos numeros porque superou o maximo de canal do tronco backup\n";
							$sql = "UPDATE pkg_phonenumber SET status = 0 WHERE id = ".$phone['pkg_phonenumber_id'];
							Yii::app()->db->createCommand($sql)->execute();
							continue;
						}

						$limitTrunkBackup++;


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
							echo 'desativa o numero de falhou na 1º tentativa e nao tem backup';
							$sql = "UPDATE pkg_phonenumber SET status = 0 WHERE id = ".$phone['pkg_phonenumber_id'];
							Yii::app()->db->createCommand($sql)->execute();
							continue;
						}
											
					}
					else
					{

						echo $destination . " enviado pelo tronco principal\n";

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

					$limitTrunk++;

					

					if(substr("$destination", 0, 4) == 1111)/*Retira o techprefix de numeros portados*/
				     {
				          $destination = preg_replace("/1111[0-9][0-9][0-9]/","",$destination);
				     }

				     $extension = $destination;

					//retiro e adiciono os prefixos do tronco
					if(strncmp($destination, $removeprefix, strlen($removeprefix)) == 0)
						$destination = substr($destination, strlen($removeprefix));
					$destination = $trunkprefix.$destination;
					
					$dialstr = "$providertech/$trunkcode/$destination";
					//$dialstr = "SIP/24315";
					// gerar os arquivos .call
					$call = "Channel: " . $dialstr . "\n";
					$call .= "Callerid: " . $phone["username"] . "\n";		
					$call .= "Context: billing\n";
					$call .= "Extension: " . $extension . "\n";
					$call .= "Priority: 1\n";
					$call .= "Set:CALLED=" . $extension . "\n";
					$call .= "Set:USERNAME=" . $phone["username"] . "\n";
					$call .= "Set:IDCARD=" . $phone["id"] . "\n";
					$call .= "Set:PHONENUMBER_ID=" . $phone['pkg_phonenumber_id'] . "\n";
					$call .= "Set:PHONENUMBER_CITY=" . $phone['number_city'] . "\n";
					$call .= "Set:PHONEBOOK_ID=" . $phone['id_phonebook'] . "\n";
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

					touch("$arquivo_call", mktime(date("H"), date("i"), date("s") + 1, date("m"), date("d"), date("Y")));
					chown("$arquivo_call", "asterisk");
					chgrp("$arquivo_call", "asterisk");
					chmod("$arquivo_call", 0755);
					exec("mv $arquivo_call /var/spool/asterisk/outgoing/$aleatorio.call");

					$sql = "UPDATE pkg_phonenumber SET status = 2, try = try + 1 WHERE id = ".$phone['pkg_phonenumber_id'];
					Yii::app()->db->createCommand($sql)->execute();
					$totalCalls++;
					echo "\n";
				}			


				unset($limitTrunkBackup);
			}
		}
	}	
}
?>