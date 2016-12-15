<?php
/**
 * Acoes do modulo "Call".
 *
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
 * 19/09/2012
 */

class Call0800WebController extends BaseController
{

	public function actionIndex()
	{
		$config = LoadConfig::getConfig();
		Yii::app()->setLanguage($config['global']['base_language']);

		if(!isset($_POST['number'])){

			$this->render('index',array(	
				'send' => false
				));
			
		}
		else{

			$destination = isset($_POST['number']) ? $_POST['number'] : '';
			$user = isset($_GET['user']) ? $_GET['user'] : '';

			$sql = "SELECT  * FROM pkg_sip  WHERE name = :user ";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":user", $user, PDO::PARAM_STR);
			$resultSip = $command->queryAll();

			if(!isset($resultSip[0]['id']) )
			{
				$error_msg = Yii::t('yii','Error : User no Found!');
				echo $error_msg;
				exit;
			}

			$sql = "SELECT  * FROM pkg_user  WHERE id = :id_user ";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $resultSip[0]['id_user'], PDO::PARAM_STR);
			$resultUser = $command->queryAll();

			if(!isset($resultUser[0]['id']) )
			{
				$error_msg = Yii::t('yii','Error : User no Found!');
				echo $error_msg;
				exit;
			}

			$dialstr = "SIP/$user"; 
	 
	             
			// gerar os arquivos .call
			$call = "Channel: " . $dialstr . "\n"; 
			$call .= "Callerid: " . $destination . "\n";
			$call .= "Context: billing\n";
			$call .= "Extension: " . $user . "\n";
			$call .= "Priority: 1\n";
			$call .= "Set:IDUSER=" . $resultUser[0]['id']. "\n";
			$call .= "Set:SECCALL=" . $destination. "\n";


			$aleatorio = str_replace(" ", "", microtime(true));
			$arquivo_call = "/tmp/$aleatorio.call";
			$fp = fopen("$arquivo_call", "a+");
			fwrite($fp, $call);
			fclose($fp);

			if (isset($_POST['time'])) {			
				$time = strtotime($_POST['time']);
 			}else{
 				$time = time();
 			}           
                    
			touch("$arquivo_call", $time);
			chown("$arquivo_call", "asterisk");
			chgrp("$arquivo_call", "asterisk");
			chmod("$arquivo_call", 0755);             

			system("mv $arquivo_call /var/spool/asterisk/outgoing/$aleatorio.call");

			$this->render('index',array(	
				'send' => true
				));

		}
		
	}

	public function actionCallback()
	{		
		$config = LoadConfig::getConfig();
		if (isset($_GET['l'])) {

			$data = explode('|', $_GET['l']);
			
			Yii::log(print_r($data,true), 'error');


			if (!isset($data[2]) ) {
				echo 'Your number is required';
				
			}
			else if (strlen($data[2]) < 4) {
				echo 'The minimum length for your number is 4';
				
			}
			else if (!isset($data[3]) ) {
				echo 'Destination is required';
				
			}else if (strlen($data[3]) < 4) {
				echo 'The minimum length for destination is 4';
				
			}else{

				$user= $data[0];
				$pass = $data[1];			

				$sql = "SELECT pkg_user.id, username,firstname, lastname, credit, pkg_user.id_user, id_plan,
				'".$config['global']['base_currency']."' AS currency, secret, pkg_user.prefix_local 
				FROM pkg_sip join pkg_user ON pkg_sip.id_user = pkg_user.id WHERE pkg_sip.name = :user" ;
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":user", $user, PDO::PARAM_STR);
				$result = $command->queryAll();



				if(!isset($result[0]['username']) || strtoupper(MD5($result[0]['secret'])) != $pass){
					echo 'User or password is invalid';
					exit;
				}

				if ($result[0]['id_user'] > 1)
				{
					$sql = "SELECT * FROM pkg_user WHERE id = :id_user";
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":id_user", $result[0]['id_user'], PDO::PARAM_STR);
					$resultAgent = $command->queryAll();

					//VERIFICA SE O AGENT TEM CREDITO
					if(isset($resultAgent[0]['credit']) && $resultAgent[0]['credit'] <= 0)
					{
						echo Yii::t('yii','You don t have enough credit to call');
						exit;
					}
				}

				$prefix_local = $result[0]['prefix_local'];


				if(isset($result[0]['credit']) && $result[0]['credit'] <= 0.5)
				{
					echo 'You don t have enough credit to call';
					exit;
				}


				$yournumber = $data[2];
				$destination = $data[3];


				if (preg_match("/->/", $destination)) {
					$destination = explode("->", $destination);
					$destination = preg_replace("/-|\(|\)| /", "", $destination[1]);
					Yii::log(print_r($destination,true), 'error');
				}
				elseif (preg_match("/ - /", $destination)) {
					$destination = explode(" - ", $destination);
					$destination = preg_replace("/-|\(|\)| /", "", $destination[1]);
					Yii::log(print_r($destination,true), 'error');
				}


				$yournumber = $this->number_translation($prefix_local,$yournumber);
				$destination = $this->number_translation($prefix_local,$destination);

				/*protabilidade*/		
				$yournumber = Portabilidade :: getDestination($yournumber, true, false,$result[0]['id_plan']);


				$sql = "SELECT pkg_rate.id AS idRate, rateinitial, buyrate, pkg_prefix.id AS id_prefix, pkg_rate.id_trunk, rt_trunk.trunkcode, rt_trunk.trunkprefix, rt_trunk.removeprefix, rt_trunk.providertech, rt_trunk.inuse, rt_trunk.providerip, 
							rt_trunk.maxuse, rt_trunk.status, rt_trunk.failover_trunk, rt_trunk.link_sms, rt_trunk.sms_res 
					FROM pkg_rate
					LEFT JOIN pkg_plan ON pkg_rate.id_plan=pkg_plan.id
					LEFT JOIN pkg_trunk AS rt_trunk ON pkg_rate.id_trunk=rt_trunk.id
					LEFT JOIN pkg_prefix ON pkg_rate.id_prefix=pkg_prefix.id
					WHERE prefix = SUBSTRING(:yournumber,1,length(prefix)) and pkg_plan.id= :id_plan 
					ORDER BY LENGTH(prefix) DESC";

				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":yournumber", $yournumber, PDO::PARAM_STR);
				$command->bindValue(":id_plan", $result[0]['id_plan'], PDO::PARAM_STR);
				$callTrunk = $command->queryAll();


				if (count($callTrunk) == 0)
				{
					echo Yii::t('yii','Prefix not found to you number');
					
					exit;
				}


				$destination = Portabilidade :: getDestination($destination, true, false,$result[0]['id_plan']);


				$sql = "SELECT pkg_rate.id AS idRate, rateinitial, buyrate, pkg_prefix.id AS id_prefix, pkg_rate.id_trunk, rt_trunk.trunkcode, rt_trunk.trunkprefix, rt_trunk.removeprefix, rt_trunk.providertech, rt_trunk.inuse, rt_trunk.providerip, 
							rt_trunk.maxuse, rt_trunk.status, rt_trunk.failover_trunk, rt_trunk.link_sms, rt_trunk.sms_res 
					FROM pkg_rate
					LEFT JOIN pkg_plan ON pkg_rate.id_plan=pkg_plan.id
					LEFT JOIN pkg_trunk AS rt_trunk ON pkg_rate.id_trunk=rt_trunk.id
					LEFT JOIN pkg_prefix ON pkg_rate.id_prefix=pkg_prefix.id

					WHERE prefix = SUBSTRING(:destination,1,length(prefix)) and pkg_plan.id= :id_plan 
					ORDER BY LENGTH(prefix) DESC";
	
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":destination", $destination, PDO::PARAM_STR);
				$command->bindValue(":id_plan", $result[0]['id_plan'], PDO::PARAM_STR);
				$callTrunkDestination = $command->queryAll();

	
				if (count($callTrunkDestination) == 0)
				{
					echo $sql;
					echo Yii::t('yii','Prefix not found to destination');
					
					exit;
				}


				if(substr("$yournumber", 0, 4) == 1111)
		          	$yournumber = str_replace(substr($yournumber, 0, 7), "", $yournumber);

		          if(substr("$destination", 0, 4) == 1111)
		          	$destination = str_replace(substr($destination, 0, 7), "", $destination);

		          $yournumber 	 = $yournumber;
                    $providertech   = $callTrunk[0]['providertech'];
                    $ipaddress      = $callTrunk[0]['providerip'];
                    $removeprefix   = $callTrunk[0]['removeprefix'];
                    $prefix         = $callTrunk[0]['trunkprefix'];

                    if (strncmp($yournumber, $removeprefix, strlen($removeprefix)) == 0)
                         $yournumber = substr($yournumber, strlen($removeprefix));


                    $dialstr = "$providertech/$ipaddress/$prefix$yournumber";

                    // gerar os arquivos .call
                    $call = "Channel: " . $dialstr . "\n";
                    $call .= "Callerid: " . $user . "\n";
                    $call .= "Context: billing\n";
                    $call .= "Extension: " . $yournumber. "\n";
                    $call .= "Priority: 1\n";
                    $call .= "Set:CALLED=" . $yournumber. "\n";
                    $call .= "Set:TARRIFID=" . $callTrunk[0]['idRate']. "\n";
                    $call .= "Set:SELLCOST=" . $callTrunk[0]['rateinitial']. "\n";
                    $call .= "Set:BUYCOST=" . $callTrunk[0]['buyrate']. "\n";
                    $call .= "Set:CIDCALLBACK=1\n";
                    $call .= "Set:IDUSER=" . $result[0]['id']. "\n";
                    $call .= "Set:IDPREFIX=" . $callTrunk[0]['id_prefix']. "\n";
                    $call .= "Set:IDTRUNK=" . $callTrunk[0]['id_trunk']. "\n";
                    $call .= "Set:IDPLAN=" . $result[0]['id_plan']. "\n";
                    
                    $call .= "Set:SECCALL=" . $destination . "\n";
                    
                   //Yii::log(print_r($call,true), 'error');
                    
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
				
		
				echo Yii::t('yii','CallBack Success');
			}
			
		}
	}

}