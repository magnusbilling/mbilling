<?php

/**
 * Url for paypal ruturn http://ip/billing/index.php/paypal .
 */
class PaypalController extends BaseController
{

	public function actionIndex()
	{
		$config = LoadConfig::getConfig();
		/*$_POST = array(
			'mc_gross' => '50.00',
			'protection_eligibility' => 'Ineligible',
			'payer_id' => 'WVJ3YK6545HDVC',
			'tax' => '0.00',
			'payment_date' => '15:01:54 Jan 18, 2013 PST',
			'payment_status' => 'Completed',
			'charset' => 'windows-1252',
			'first_name' => 'Anibal',
			'mc_fee' => '4.00',
			'notify_version' => '3.7',
			'custom' => '',
			'payer_status' => 'verified',
			'business' => 'financiero@magnussolution.com.com',
			'quantity' => '1',
			'verify_sign' => 'A9LC3Qajo-H2V8mPq4eIktRPDNMDNt.Rmhgxk0LTN6wGo2lI1cLs',
			'payer_email' => 'amenezes@hotmail.com',
			'txn_id' => '4Y190387AG109562T',
			'payment_type' => 'instant',
			'payer_business_name' => 'eCampus',
			'last_name' => 'de Neto',
			'receiver_email' => 'magnusadilsom@gmail.com',
			'payment_fee' => '4.00',
			'receiver_id' => 'HVUEC4FXXDVDB',
			'txn_type' => 'web_accept',
			'item_name' => 'user, 44767',
			'mc_currency' => 'USD',
			'item_number' => '3202',
			'residence_country' => 'AR',
			'handling_amount' => '0.00',
			'transaction_subject' => 'user, 44767',
			'payment_gross' => '50.00',
			'shipping' => '0.00',
			'ipn_track_id' => 'ed832d58e566b'
		);*/

		Yii::log(print_r($_POST, true), 'error');
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		foreach ($_POST as $key => $value)
		{
		    $value = urlencode(stripslashes($value));
		    $req .= "&$key=$value";
		}
		// post back to PayPal system to validate
		$header = '';
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);


		if(!isset($_POST['item_name'])){
			Yii::log('No POST', 'info');
			exit();
		}

		$sql = "SELECT username, fee FROM pkg_method_pay WHERE payment_method LIKE 'Paypal'";
		$resultUsername = Yii::app()->db->createCommand($sql)->queryAll();

		Yii::log($_POST['business'], 'info');
		if ( count($resultUsername) == 0 || $_POST['business'] != $resultUsername[0]['username']) {
			Yii::log('not allow', 'info');
			exit;
		}

			
		// assign posted variables to local variables
		$item_name = $_POST['item_name'];
		$payment_status = $_POST['payment_status'];
		$monto = $resultUsername[0]['fee'] == 1 ? $_POST['mc_gross'] - $_POST['mc_fee'] : $_POST['mc_gross'];
		$payment_currency = $_POST['mc_currency'];
		$txn_id = $_POST['txn_id'];
		$receiver_email = $_POST['receiver_email'];
		$payer_email = $_POST['payer_email'];
		$item_number = $_POST['item_number'];
		$description = 'Paypal, Nro. de transaccion ' . $txn_id;
		$date = date('Ymd');
		$codigo = $txn_id;
	

		if ($txn_id == "")
		    exit();

		if (!$fp) {
			Yii::log("EPAYMENT PAYPAL: ERROR,  PAYMENT STARTD BUT NO COMPLETE TRANSACTION ID $txn_id !fp ", 'info');
			write_log(LOGFILE_EPAYMENT, basename(__file__) . ' line:' . __line__ . " EPAYMENT PAYPAL: ERROR,  PAYMENT STARTD BUT NO COMPLETE TRANSACTION ID $txn_id !fp ");
			fclose($fp);
		} else {
			Yii::log('EPAYMENT PAYPAL: ERROR, OK CONTINUA TO ADD CREDIT', 'info');
			fputs($fp, $header . $req);
			while (!feof($fp))
			{				
				$res = fgets($fp, 1024);
				if (strcmp($res, "VERIFIED") == 0)
				{
					if ($_POST['payment_status'] == 'Completed')
					{
						Yii::log('PAYMENT VERIFIED', 'info');
						$sql = "SELECT * FROM pkg_user WHERE username = :item_number";
						Yii::log($sql, 'info');
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":item_number", $item_number, PDO::PARAM_STR);
						$resultUser = $command->queryAll();
						if (count($resultUser) > 0 ) 
						{
							//checa se o usaurio ja fez pagamentos
							if($config['global']['paypal_new_user'] == 0)
							{
								$sql = "SELECT count(*) AS total FROM pkg_refill WHERE id_user = :id_user";
								Yii::log($sql, 'info');
								$command = Yii::app()->db->createCommand($sql);
								$command->bindValue(":id_user", $resultUser[0]['id'], PDO::PARAM_STR);
								$resultPaypal = $command->queryAll();

								if ($resultPaypal[0]['total'] == 0) 
								{
									$mail_subject = "RECURRING SERVICES : PAYPAL";
									$mail_content = "SERVICE NAME = PAYPAL";
									$mail_content .= "\n\nCARDID = " . $resultUser[0]['id'];
									$mail_content .= "\nTotal transation = " . $monto;
									$mail_content .= "\nERROR,  PAYMENT STARTD BUT NO COMPLETE, FISRT PAYMENT FOR USER " . $resultUser[0]['username'] . ",   TRANSACTION ID " . $txn_id . "";

									$mail = new Mail(null, $resultUser[0]['id'], null, $mail_content, $mail_subject);
									$mail->send($config['global']['admin_email']);
									fclose($fp);
									exit;
								}else{
									Yii::log($resultUser[0]['id']. ' '. $monto . ' '. $description . ' '. $codigo, 'info');
									Process ::releaseUserCredit($resultUser[0]['id'], $monto, $description, $codigo);
								}
								
							}else{
								Yii::log($resultUser[0]['id']. ' '. $monto . ' '. $description . ' '. $codigo, 'info');
								Process ::releaseUserCredit($resultUser[0]['id'], $monto, $description, $codigo);
							}
						}else{
							Yii::log('USERNAE NOT FOUND'. $sql, 'info');
						}

					}
				}else{
					Yii::log('NOT VERIFIED', 'info');
				}
			}
			fclose($fp);
		}
	}
}