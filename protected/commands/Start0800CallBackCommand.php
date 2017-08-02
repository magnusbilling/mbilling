<?php
/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2017 MagnusBilling. All rights reserved.
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
class Start0800CallBackCommand extends CConsoleCommand 
{
	private $host = 'localhost';
	private $user = 'magnus';
	private $password = 'magnussolution';

	public function run($args)
	{	
		ini_set("max_execution_time", "900");
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
			
			define("PID", "/var/run/magnus/Start0800CallBackCommandPid.php");
		}



		if (Process :: isActive()) {
			echo " PROCESS IS ACTIVE ";
			die();
		} else {
			Process :: activate();
		}
		
		include("/var/www/html/mbilling/protected/commands/AGI.Class.php");
		$asmanager = new AGI_AsteriskManager;
		$asmanager->connect($this->host, $this->user, $this->password);

		for (;;){
			
			if (date('s') == 58) {
				exit;
			}
			sleep(1);

			$voltarChamar = time() - 1800;
			$voltarChamar = date('Y-m-d H:i:s',$voltarChamar);
			$sql = "UPDATE pkg_callback SET status = 1 WHERE status = 2 AND last_attempt_time < '$voltarChamar'";
			Yii::app()->db->createCommand($sql)->execute();

			//esperar 60 segundos antes de tentar ligar para o cliente.
			$timeToNewCallback = date('Y-m-d H:i:s',time() - 60); ;
			$sql ="SELECT * FROM pkg_callback WHERE  status = 1 AND entry_time < '".$timeToNewCallback."' ";
			$callbackResult = Yii::app()->db->createCommand($sql)->queryAll();
			if(DEBUG == 2) echo $sql."\n\n\n";
			
			if(DEBUG >= 1) echo "\nFound " . count($callbackResult) . " callback\n\n";

			foreach ($callbackResult as $callback) {
				
				$sql = "SELECT * FROM pkg_did_destination WHERE id_did = ".$callback['id_did'];
				$didResult = Yii::app()->db->createCommand($sql)->queryAll();

				$sql = "SELECT * FROM pkg_queue WHERE id = ".$didResult[0]['id_queue'];
				$queueResult = Yii::app()->db->createCommand($sql)->queryAll();

				if (!count($queueResult)) {
					echo "User not have QUEUE";
					continue;
				}

				$server = $asmanager->Command('queue show "'.$queueResult[0]['name'].'"');

		
				$agent = '';
		        	foreach (explode("\n", $server["data"]) as $key => $value) {
		        		//Quantos operadores estao com status not in use
		        	 	if (!preg_match("/paused/", $value) && preg_match("/Not in use/", $value)) {
		        	 		$agent = explode(" ", substr(trim($value),4));
		        	 		$agent = $agent[0];
		        	 		break;
		        	 	}
		        	}

		        	if (strlen($agent) < 2) {
		        		echo "Nao tem agent livre para receber chamada\n";
		        		exit;
		        	}

		        	echo "Agent $agent esta livre para receber chamadas\n";


		        	$dialstr = "SIP/$agent";


				// gerar os arquivos .call
				$call = "Channel: " . $dialstr . "\n"; 
				$call .= "Callerid: " . $callback['exten'] . "\n";
				$call .= "Context: billing\n";
				$call .= "Extension: " . $agent . "\n";
				$call .= "Priority: 1\n";
				$call .= "Set:IDUSER=" . $callback['id_user']. "\n";
				$call .= "Set:SECCALL=" . $callback['exten']. "\n";
				$call .= "Set:IDCALLBACK=" . $callback['id']. "\n";


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

				
				$sql = "UPDATE pkg_callback SET num_attempt = num_attempt + 1, last_attempt_time = NOW(), status = 2 WHERE id = ".$callback['id'];
				Yii::app()->db->createCommand($sql)->execute();								
				
			}
		}				
	}	
}
?>