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
class ServicesCheckCommand extends CConsoleCommand 
{	
	private $userNotify = array();

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
			define('DEBUG', 5);


		define('LOGFILE', 'protected/runtime/ServicesCheck.log');
	
		if (!defined('PID')) {
		define("PID", "/var/run/magnus/ServicesCheckPid.php");
		}

		if (Process :: isActive()) {
			if(DEBUG >= 1) echo  " PROCESS IS ACTIVE " ;
			die();
		} else {
			Process :: activate();
		}
		if(DEBUG >= 1) echo " START NOTIFY CLIENT ";
		
		$config = LoadConfig::getConfig();

		Yii::app()->language = Yii::app()->sourceLanguage = isset($config['global']['base_language']) ? $config['global']['base_language']  : Yii::app()->language;

		$modelServiceUser = ServicesUse::model()->findAll(array(
			'condition' => 'status=1'
			));

		if (!count($modelServiceUser)){
			if(DEBUG >= 1) echo " NO SERVICE IN USE ";
			exit;
		}

		$daytopay = $config['global']['service_daytopay'];
		$oneday = 60 * 60 * 24; //86400 day seconds


		foreach ($modelServiceUser as $service)
		{
			echo "\n\n";
			$day_remaining = 0;

			//5 days before activation
			$diff_reservation_daytopay = (strtotime($service['reservationdate'])) - (intval($daytopay) * $oneday);
			
			//add the month_payed in the reservationdate - the notificationday
			$timestamp_datetopay = mktime(date('H', $diff_reservation_daytopay), date("i", $diff_reservation_daytopay), date("s", $diff_reservation_daytopay), 
				date("m", $diff_reservation_daytopay) + $service['month_payed'], date("d", $diff_reservation_daytopay), date("Y", $diff_reservation_daytopay));

			$day_remaining = time() - $timestamp_datetopay;
			echo "ACTIVATION DATE  ".$service['reservationdate']."\n";
			echo "MONT PAYD        ".$service['month_payed']."\n";
			echo "NEXT NOTIFY DATE ".date('Y-m-d H:i:s',$timestamp_datetopay)."\n";	
			//total days after the start notification
			echo "DAYS  REMAINING  ".date('d',$day_remaining). ' :  '.$day_remaining."\n";

			//pega o

			/*
				o cliente tem 1 ou mais servicos por vencer nos proximos dias.
				Envia um email por servico informando que tem o servico por vencer e que ele nao tem saldo para pagar
				Informar um link no email para ele colocar credito para pagar o servico.
				Quando ele abrir o link, informar os servicos que estao por vencer e mostrar o valor que ele deveria pagar.

				quando for liberado o credito para o cliente, sempre verificar se tem servico por pagar, e se o valor credito for 
			*/

			// tem servico para ser pago, vendido ou nao
			if ($day_remaining >= 0)
			{
				//ainda nao venceu
				if ($day_remaining <= (intval($daytopay) * $oneday))
				{
					if(DEBUG >= 1) echo " USER ".$service->idUser->username." HAVE SERVICE TO BE PAID \n";

					if ( $this->checkIfUserHaveCredit($service) != true)
					{
						//venceu e nao tem credito, avisar por email.
						if(DEBUG >= 1) echo " USER ".$service->idUser->username." DONT HAVE ENOUGH CREDIT TO PAY FOR THE SERVICE NOTIFY NOW \n ";	
						$this->notifyUser($service,2);						
				 	}
				}
				else
				{
					if(DEBUG >= 1) echo " USER ".$service->idUser->username."  HAVE EXPIRED SERVICE \n";
					if ($this->checkIfUserHaveCredit($service) != true) {

						//verificar se ja passou o $daytopay da data de vencimento
						if( (date('d',$day_remaining) / 2) >  $daytopay ){
							if(DEBUG >= 1) echo " RELEASE THE SERVICE THE USER ".$service->idUser->username."\n " ;			 	
							ServicesProcess::releaseService($service);
							
						}else{
							if(DEBUG >= 1) echo " ALREADY EXPIRED BUT INSIDE THE WINDOW. USER ".$service->idUser->username."\n " ;
							$this->notifyUser($service,3);
						}

						

					}									
				}
			}
			else
			{
				if(DEBUG >= 1) echo " NO SERVICE FOR PAY \n" ;
			}		
		}
	}

	public function checkIfUserHaveCredit($service)
	{
		if ( ($service->idUser->credit + ($service->idUser->typepaid * $service->idUser->creditlimit) ) >= $service->idServices->price)
		{
			//venceu e tem credito
	 		echo " USER ".$service->idUser->username." HAVE ENOUGH CREDIT TO PAY FOR THE SERVICE \n";
	 		ServicesProcess::payService($service);
	 		return true;				
		}	 	
	 	return false;	 	
	}

	public function notifyUser($service,$type)
	{
		$config = LoadConfig::getConfig();
		if (!in_array($service->idUser, $this->userNotify)) {

			echo 'Send notify email';
			$this->userNotify[]=$service->idUser;

			$service->reminded = $type;
			$service->save();

			$link = $config['global']['ip_servers']."/mbilling/index.php/buyCredit/payServiceLink?id_user=".$service->id_user;
			echo $link;
			$mail = new Mail(Mail::$TYPE_SERVICES_UNPAID, $service->id_user);		
			$mail->replaceInEmail(Mail::$SERVICE_NAME, $service->idServices->name);
			$mail->replaceInEmail(Mail::$SERVICE_PRICE, $service->idServices->price); 
			$mail->replaceInEmail(Mail::$SERVICE_PENDING_URL, $link); 
			try {
				@$mail->send();
			} catch (Exception $e) {
				//error SMTP
			}
		}else
			echo 'user already notificed';
	}
}