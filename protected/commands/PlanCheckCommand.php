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
class PlanCheckCommand extends CConsoleCommand 
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


		define('LOGFILE', 'protected/runtime/PlanCheck.log');
	
		if (!defined('PID')) {
		define("PID", "/var/run/magnus/PlanCheckPid.php");
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


		$sql = "SELECT reservationdate, month_payed, pkg_user.id, credit, email, label, typepaid, creditlimit, username, 
		pkg_user.id_offer, price, pkg_user.id
		FROM pkg_user
		INNER JOIN pkg_offer_use ON pkg_offer_use.id_user = pkg_user.id
		INNER JOIN pkg_offer ON pkg_offer.id  = pkg_user.id_offer
		WHERE releasedate IS NULL OR releasedate < '1984-01-01 00:00:00'
		AND pkg_user.active= 1
		ORDER BY pkg_user.id ASC";

		$planResult = Yii::app()->db->createCommand($sql)->queryAll();
		if(DEBUG >= 1) echo  $sql;

		if (count($planResult) == 0){
			if(DEBUG >= 1) echo " NO PLAN IN USE ";
			exit;
		}


		$daytopay = $config['global']['planbilling_daytopay'];
		$oneday = 60 * 60 * 24;

		foreach ($planResult as $plans)
		{
			$day_remaining = 0;


			$diff_reservation_daytopay = (strtotime($plans['reservationdate'])) - (intval($daytopay) * $oneday);
			$timestamp_datetopay = mktime(date('H', $diff_reservation_daytopay), date("i", $diff_reservation_daytopay), date("s", $diff_reservation_daytopay), 
				date("m", $diff_reservation_daytopay) + $plans['month_payed'], date("d", $diff_reservation_daytopay), date("Y", $diff_reservation_daytopay));

			$day_remaining = time() - $timestamp_datetopay;

			if(DEBUG >= 3) echo " DAYS TO PAY ".$daytopay;
			if(DEBUG >= 3) echo " NOW :" . time() . " - timestamp_datetopay=$timestamp_datetopay";
			if(DEBUG >= 3) echo " day_remaining=$day_remaining <=" . (intval($daytopay) * $oneday);

			if ($day_remaining >= 0)
			{
				if ($day_remaining <= (intval($daytopay) * $oneday))
				{
					if(DEBUG >= 1) echo " USER ".$plans['username']." HAVE TO PAY THE PLAN NOW ";

					if (($plans['credit'] + $plans['typepaid'] * $plans['creditlimit']) >= $plans['price'])
					{
				 		if(DEBUG >= 1) echo " USER ".$plans['username']." HAVE ENOUGH CREDIT TO PAY FOR THE PLAN ";

						$sql = "UPDATE pkg_offer_use set month_payed = month_payed + 1 WHERE id_user = '".$plans['id']."' AND id_offer = '" . $plans['id_offer'] . "' AND status = 1 AND ( releasedate IS NULL OR releasedate < '1984-01-01 00:00:00') ";
						$result = Yii::app()->db->createCommand($sql)->execute();
						if(DEBUG >= 1) echo $sql;

						//adiciona a recarga e pagamento
						$refill = new Refill;
						$refill->id_user = $plans['id'];
						$refill->credit = -$plans['price'];
						$refill->description = Yii::t('yii','Monthly payment Plan'). ' '.$plans['label'];
						$refill->payment = 1;
						$refill->save();

						$mail = new Mail(Mail :: $TYPE_PLAN_PAID, $plans['id']);
						$mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $plans['credit'] - $plans['price']);
						$mail->replaceInEmail(Mail::$PLAN_LABEL, $plans['label']);
						$mail->replaceInEmail(Mail::$PLAN_COST, -round($plans['price'], 2));
						$mail->send();
						$sendAdmin = $config['global']['admin_received_email'] == 1 ? $mail->send($config['global']['admin_email']) : NULL;
					}
					else
					{
						if(DEBUG >= 1) echo " USER ".$plans['username']." DONT HAVE ENOUGH CREDIT TO PAY FOR THE PLAN NOTIFY NOW ";

						$mail = new Mail(Mail::$TYPE_PLAN_UNPAID, $plans['id']);
						$mail->replaceInEmail(Mail::$DAY_REMAINING_KEY, date("d", $day_remaining));
						$mail->replaceInEmail(Mail::$PLAN_LABEL, $plans['label']);
						$mail->replaceInEmail(Mail::$PLAN_COST, round($plans['price'], 2));
						$mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, number_format($plans['credit'],2));
						$mail->send();
						$sendAdmin = $config['global']['admin_received_email'] == 1 ? $mail->send($config['global']['admin_email']) : NULL;
				 	}
				}
				else
				{
					if (($plans['credit'] + $plans['typepaid'] * $plans['creditlimit']) >= $plans['price'])
					{
				 		if(DEBUG >= 1) echo " USER ".$plans['username']." HAVE ENOUGH CREDIT TO PAY FOR THE PLAN " ;

						$sql = "UPDATE pkg_offer_use set month_payed = month_payed + 1 WHERE id_user = '".$plans['id']."' AND  id_offer = '" . $plans['id_offer'] . "' AND status = 1 AND ( releasedate IS NULL OR releasedate < '1984-01-01 00:00:00') ";
						$result = Yii::app()->db->createCommand($sql)->execute();
						if(DEBUG >= 3) echo $sql ;

						//adiciona a recarga e pagamento
						$refill = new Refill;
						$refill->id_user = $plans['id'];
						$refill->credit = -$plans['price'];
						$refill->description = Yii::t('yii','Monthly payment Plan'). ' '.$plans['label'];
						$refill->payment = 1;
						$refill->save();

						$mail = new Mail(Mail :: $TYPE_PLAN_PAID, $plans['id']);
						$mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $plans['credit'] - $plans['price']);
						$mail->replaceInEmail(Mail::$PLAN_LABEL, $plans['label']);
						$mail->replaceInEmail(Mail::$PLAN_COST, -round($plans['price'], 2));
						$mail->send();
						$sendAdmin = $config['global']['admin_received_email'] == 1 ? $mail->send($config['global']['admin_email']) : NULL;
					}
					else
					{
						if(DEBUG >= 1) echo " RELEASE THE PLAN THE USER ".$plans['username']." " ;			 	

						$sql = "UPDATE pkg_offer_use set releasedate = now(), status = 0 
						WHERE id_offer = '" . $plans['id_offer'] . "' AND status = 1 AND id_user = '" . $plans['id'] . "'
						AND ( releasedate IS NULL OR releasedate < '1984-01-01 00:00:00') ";
						Yii::app()->db->createCommand($sql)->execute();
						if(DEBUG >= 3) echo $sql ;

						$sql = "UPDATE pkg_user SET id_offer = 0 WHERE id=" . $plans['id'];
						Yii::app()->db->createCommand($sql)->execute();
						if(DEBUG >= 3) echo $sql ;

						$mail = new Mail(Mail::$TYPE_PLAN_RELEASED, $plans['id']);
						$mail->replaceInEmail(Mail::$PLAN_LABEL, $plans['label']);
						$mail->replaceInEmail(Mail::$PLAN_COST, round($plans['price'], 2));
						$mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $plans['credit']);
						$mail->send();
						$sendAdmin = $config['global']['admin_received_email'] == 1 ? $mail->send($config['global']['admin_email']) : NULL;

					}				
				}
			}
			else
			{
				if(DEBUG >= 1) echo " NO PLAN FOR PAY \n" ;
			}		
		}
	}
}