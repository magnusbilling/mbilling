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
class DidCheckCommand extends CConsoleCommand 
{


	public function run($args)
	{
		define('LOGFILE', 'protected/runtime/DidCheck.log');
		define('DEBUG', 0);

		if (!defined('PID')) {
		define("PID", "/var/run/magnus/DidCheckPid.php");
		}	

		if (Process :: isActive()) {
			$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " PROCESS IS ACTIVE ") : null;
			die();
		} else {
			Process :: activate();
		}
		$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " START NOTIFY CLIENT ") : null;

		
		$config = LoadConfig::getConfig();

		Yii::app()->language = Yii::app()->sourceLanguage = isset($config['global']['base_language']) ? $config['global']['base_language']  : Yii::app()->language;


		$sql = "SELECT id_did, reservationdate, month_payed, fixrate, pkg_user.id, credit, email, did, typepaid, creditlimit, reminded, username, pkg_user.id_user ".
			"FROM `pkg_did_use` `t` INNER JOIN pkg_user ON (pkg_user.id=t.id_user) INNER JOIN pkg_did ON (t.id_did=pkg_did.id) AND pkg_did.fixrate > 0 ".
			"WHERE releasedate IS NULL OR releasedate < '1984-01-01 00:00:00' AND t.status= 1 AND pkg_did.billingtype <> 3 AND fixrate > 0 ".
			"ORDER BY pkg_user.id ASC";
		$didResult = Yii::app()->db->createCommand($sql)->queryAll();
		$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  $sql) : null;

		if (!isset($didResult[0]['id_did']))
			exit(DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__. " NO DID IN USE ") : null);

		
		$daytopay = 5;
		$oneday = 60 * 60 * 24;

		foreach ($didResult as $mydids)
		{
			$day_remaining = 0;

			$diff_reservation_daytopay = (strtotime($mydids['reservationdate'])) - (intval($daytopay) * $oneday);
			$timestamp_datetopay = mktime(date('H', $diff_reservation_daytopay), date("i", $diff_reservation_daytopay), date("s", $diff_reservation_daytopay), 
				date("m", $diff_reservation_daytopay) + $mydids['month_payed'], date("d", $diff_reservation_daytopay), date("Y", $diff_reservation_daytopay));

			$day_remaining = time() - $timestamp_datetopay;

			$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " DAYS TO PAY ".$daytopay) : null;
			$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " NOW :" . time() . " - DATE FOR PAY= $timestamp_datetopay") : null;
			$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " day_remaining=$day_remaining <=" . (intval($daytopay) * $oneday)) : null;
			$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " IN DAYS FOR PAY = $day_remaining ") : null;

			if ($day_remaining >= 0)
			{

				if ($mydids['id_user'] > 1)
				{
					$agent = User::model()->findByPk($mydids['id_user']);
					$mydids['credit'] = $agent->credit;
				}

				if ($day_remaining <= (intval($daytopay) * $oneday))
				{
					$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " USER ".$mydids['username']." HAVE TO PAY THE DID ". $mydids['did'] ." NOW ") : null;

					if (($mydids['credit'] + $mydids['typepaid'] * $mydids['creditlimit']) >= $mydids['fixrate'])
					{
						if ($mydids['id_user'] <= 1) 
						{
							$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " USER ".$mydids['username']. " HAVE ENOUGH CREDIT TO PAY FOR THE DID ". $mydids['did']) : null;


							$sql = "UPDATE pkg_did_use set month_payed = month_payed + 1 WHERE id_did = '" . $mydids['id_did'] . "' AND status = 1 AND ( releasedate IS NULL OR releasedate < '1984-01-01 00:00:00') ";
							$result = Yii::app()->db->createCommand($sql)->execute();
							$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  $sql) : null;

							//adiciona a recarga e pagamento
							$refill = new Refill;
							$refill->id_user = $mydids['id'];
							$refill->credit = -$mydids['fixrate'];
							$refill->description = Yii::t('yii','Monthly payment Did'). ' '.$mydids['did'];
							$refill->payment = 1;
							$refill->save();

							$mail = new Mail(Mail :: $TYPE_DID_PAID, $mydids['id']);
							$mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $mydids['credit'] - $mydids['fixrate']);
							$mail->replaceInEmail(Mail::$DID_NUMBER_KEY, $mydids['did']);
							$mail->replaceInEmail(Mail::$DID_COST_KEY, -$mydids['fixrate']);
							$mail->send();
							$sendAdmin = $config['global']['admin_received_email'] == 1 ? $mail->send($config['global']['admin_email']) : NULL;
						}						
						else
						{
							$description = Yii::t('yii','Monthly payment Did'). ' '.$mydids['did'];

							$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " AGENT '" . $agent->username . "' HAVE ENOUGH CREDIT TO PAY FOR THE DID ". $mydids['did']) : null;
							

							$sql = "UPDATE pkg_did_use set month_payed = month_payed + 1 WHERE id_did = '" . $mydids['id_did'] . "' AND status = 1 AND ( releasedate IS NULL OR releasedate < '1984-01-01 00:00:00') ";
							$result = Yii::app()->db->createCommand($sql)->execute();
							$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  $sql) : null;

							//adiciona a recarga e pagamento
							$sql = "INSERT INTO pkg_refill (id_user, credit, description, payment) VALUES ('".$mydids['id_user']."', '".$mydids['fixrate']."', '".$description."', 1)";
							Yii::app()->db->createCommand($sql)->execute();							
							$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  $sql) : null;							

							$mail = new Mail(Mail :: $TYPE_DID_PAID, $mydids['id'], $mydids['id_user']);
							$mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $mydids['credit'] - $mydids['fixrate']);
							$mail->replaceInEmail(Mail::$DID_NUMBER_KEY, $mydids['did']);
							$mail->replaceInEmail(Mail::$DID_COST_KEY, -$mydids['fixrate']);
							$mail->send();
							$sendAdmin = $config['global']['admin_received_email'] == 1 ? $mail->send($config['global']['admin_email']) : NULL;							
						}
					}				 		
					else
					{
						$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " USER ".$mydids['username']. " DONT HAVE ENOUGH CREDIT TO PAY FOR THE DID ". $mydids['did'] ." NOTIFY NOW ") : null;
						
						if ($mydids['id_user'] > 1) 
							$mail = new Mail(Mail::$TYPE_DID_UNPAID, $mydids['id'], $mydids['id_user']);
						else
							$mail = new Mail(Mail::$TYPE_DID_UNPAID, $mydids['id']);

						$mail->replaceInEmail(Mail::$DAY_REMAINING_KEY, date("d", $day_remaining));
						$mail->replaceInEmail(Mail::$DID_NUMBER_KEY, $mydids['did']);
						$mail->replaceInEmail(Mail::$DID_COST_KEY, $mydids['fixrate']);
						$mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, number_format($mydids['credit'],2));
						$mail->send();										

						$sendAdmin = $config['global']['admin_received_email'] == 1 ? $mail->send($config['global']['admin_email']) : NULL;
				 	}
				}
				else
				{
					if (($mydids['credit'] + $mydids['typepaid'] * $mydids['creditlimit']) >= $mydids['fixrate'])
					{
						$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " USER ".$mydids['username']. " HAVE ENOUGH CREDIT TO PAY FOR THE DID ". $mydids['did']) : null;

				 		if ($mydids['id_user'] <= 1) 
						{
							$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " USER ".$mydids['username']. " HAVE ENOUGH CREDIT TO PAY FOR THE DID ". $mydids['did']) : null;

								$sql = "UPDATE pkg_did_use set month_payed = month_payed + 1 WHERE id_did = '" . $mydids['id_did'] . "' AND status = 1 AND ( releasedate IS NULL OR releasedate < '1984-01-01 00:00:00') ";
							$result = Yii::app()->db->createCommand($sql)->execute();
							$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  $sql) : null;

							//adiciona a recarga e pagamento
							$refill = new Refill;
							$refill->id_user = $mydids['id'];
							$refill->credit = -$mydids['fixrate'];
							$refill->description = Yii::t('yii','Monthly payment Did'). ' '.$mydids['did'];
							$refill->payment = 1;
							$refill->save();

							$mail = new Mail(Mail :: $TYPE_DID_PAID, $mydids['id']);
							$mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $mydids['credit'] - $mydids['fixrate']);
							$mail->replaceInEmail(Mail::$DID_NUMBER_KEY, $mydids['did']);
							$mail->replaceInEmail(Mail::$DID_COST_KEY, -$mydids['fixrate']);
							$mail->send();
							$sendAdmin = $config['global']['admin_received_email'] == 1 ? $mail->send($config['global']['admin_email']) : NULL;
						}						
						else
						{
							$description = Yii::t('yii','Monthly payment Did'). ' '.$mydids['did'];

							$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " AGENT '" . $agent->username . "' HAVE ENOUGH CREDIT TO PAY FOR THE DID ". $mydids['did']) : null;
							

							$sql = "UPDATE pkg_did_use set month_payed = month_payed + 1 WHERE id_did = '" . $mydids['id_did'] . "' AND status = 1 AND ( releasedate IS NULL OR releasedate < '1984-01-01 00:00:00') ";
							$result = Yii::app()->db->createCommand($sql)->execute();
							$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  $sql) : null;

							//adiciona a recarga e pagamento
							$sql = "INSERT INTO pkg_refill (id_user, credit, description, payment) VALUES ('".$mydids['id_user']."', '".$mydids['fixrate']."', '".$description."', 1)";
							$result = Yii::app()->db->createCommand($sql)->execute();							
							$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  $sql) : null;							
							

							$mail = new Mail(Mail :: $TYPE_DID_PAID, $mydids['id'], $mydids['id_user']);
							$mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $mydids['credit'] - $mydids['fixrate']);
							$mail->replaceInEmail(Mail::$DID_NUMBER_KEY, $mydids['did']);
							$mail->replaceInEmail(Mail::$DID_COST_KEY, -$mydids['fixrate']);
							$mail->send();
							$sendAdmin = $config['global']['admin_received_email'] == 1 ? $mail->send($config['global']['admin_email']) : NULL;							
						}
					}
					else
					{
						$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " RELEASE THE DID  ". $mydids['did'] ." ON THE USER  ".$mydids['username']." ") : null;
			 	
						$sql = "UPDATE pkg_did set id_user = NULL, reserved = 0 WHERE id='" . $mydids['id_did'] . "'";
						$result = Yii::app()->db->createCommand($sql)->execute();
						$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  $sql) : null;

						$sql = "UPDATE pkg_did_use set releasedate = now(), status = 0 WHERE id_did = '" . $mydids['id_did'] . "'";
						$result = Yii::app()->db->createCommand($sql)->execute();
						$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  $sql) : null;

						$sql = "DELETE FROM pkg_did_destination WHERE id_did =" . $mydids['id_did'];
						$result = Yii::app()->db->createCommand($sql)->execute();
						$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  $sql) : null;

						if ($mydids['id_user'] > 1) 
							$mail = new Mail(Mail::$TYPE_DID_RELEASED, $mydids['id'], $mydids['id_user']);
						else
							$mail = new Mail(Mail::$TYPE_DID_RELEASED, $mydids['id']);

						$mail->replaceInEmail(Mail::$DID_NUMBER_KEY, $mydids['did']);
						$mail->replaceInEmail(Mail::$DID_COST_KEY, $mydids['fixrate']);
						$mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $mydids['credit']);
						$mail->send();
						$mail->send($config['global']['admin_email']);$sendAdmin = $config['global']['admin_received_email'] == 1 ? $mail->send($config['global']['admin_email']) : NULL;
					}				
				}
			}
			else
			{
				$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " NOT DIDS FOR PAY TODAY ") : null;
			}
		}
	}
}