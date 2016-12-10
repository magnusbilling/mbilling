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
class NotifyClientCommand extends CConsoleCommand 
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

	
		if (!defined('PID')) {
		define("PID", "/var/run/magnus/NotifyClientPid.php");
		}



		if (Process :: isActive()) {
			echo " PROCESS IS ACTIVE ";
			die();
		} else {
			Process :: activate();
		}
		if(DEBUG >= 1) echo (" START NOTIFY CLIENT ");	

		$config = LoadConfig::getConfig();

		$delayNotifications =  $config['global']['delay_notifications'];

		$delayClause = "( ";

		if ($delayNotifications <= 0)
			$delayClause .= "last_notification < CURDATE() + 1 OR ";
		else
			$delayClause .= "last_notification < CURDATE() - " . $delayNotifications . " OR ";
		$delayClause .= "last_notification IS NULL )";
		
 		$filter = 'credit_notification > 0  AND active = 1 AND credit + creditlimit < credit_notification AND ' . $delayClause;

 		$card = User::model()->findAll(array(
			'select' => 'id, email, id_user',
			'condition' => $filter,
			'order' => 'id'
		));

		foreach ($card as $mycard)
		{
			if ($mycard->id_user == NULL || $mycard->id_user == '') {
				$sql = "UPDATE pkg_user SET id_user = 1 WHERE id = $mycard->id";
				Yii::app()->db->createCommand($sql)->execute();
			}

			$sql = "SELECT * FROM pkg_smtp WHERE id_user = " . $mycard->id_user;
			$smtpResult = Yii::app()->db->createCommand($sql)->queryAll();

			if (count($smtpResult) == 0) {
				continue;
			}

			if (strlen($mycard->email) > 0) 
			{
				$mail = new Mail(Mail :: $TYPE_REMINDER, $mycard->id);
				try {
					$mail->send();
				} catch (Exception $e) {
					//error SMTP
				}

				if ($config['global']['admin_received_email'] == 1 && strlen($config['global']['admin_email'])) {
					try {
						$mail->send($config['global']['admin_email']);
					} catch (Exception $e) {
						
					}
					
				}

				
				if(DEBUG >= 1) echo ("Notifique email" . $mycard->email ."\n" ) ;
			}

			$sql = "UPDATE pkg_user SET last_notification = now() WHERE id = $mycard->id";
			if(DEBUG >= 2) echo ($sql."\n") ;
			Yii::app()->db->createCommand($sql)->execute();
		}
		sleep(1);
	}
}
?>