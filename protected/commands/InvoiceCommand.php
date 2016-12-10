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
class InvoiceCommand extends CConsoleCommand 
{

	public $titleReport;
	public $subTitleReport;
	public $fieldsCurrencyReport;
	public $fieldsPercentReport;
	public $rendererReport;
	public $fieldsFkReport;

	public function run($args)
	{
		define('LOGFILE', 'protected/runtime/Invoice.log');
		define('DEBUG', 0);

		if (!defined('PID')) {
		define("PID", "/var/run/magnus/InvoicePid.php");
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


		if (isset($args[0]) && is_numeric($args[0])) {
			$day = $args[0];
			echo "INVOCE DAY $day \n";

			if ($day == date('d')) {
				echo "enviar a factura \n";
			}
			else{
				echo "Not have invoice to send today \n";
				exit;
			}
				


		}else{
			//use the user creation date
		}

		if (isset($args[1]))
			$filterUser = " AND username = '".$args[1]."'";
		else
			$filterUser = '';


		$sql = "SELECT * FROM pkg_user WHERE active = 1 $filterUser";
		$userResult = Yii::app()->db->createCommand($sql)->queryAll();
		$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  $sql) : null;

		if (count($userResult) == 0){
			echo "NO USER TO SEND INVOICE $sql";
			exit(DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__. " NO USER TO SEND INVOICE") : null);
		}
			
		foreach ($userResult as $user)
		{
			$today = date('Y-m-d');
			$lastMonth = date('Y-m-d', strtotime("-30 days",strtotime($today)));
			$columns= "starttime,calledstation,sessiontime,sessionbill";
			$filter = "id_user = ".$user['id']." AND starttime < '$today' AND starttime > '$lastMonth'";
			$sql = "SELECT $columns FROM pkg_cdr WHERE $filter ";
			$cdrResult = Yii::app()->db->createCommand($sql)->queryAll();
			if (count($cdrResult) < 1) {
			 	continue;
			}
			$sql = "SELECT id, sum(sessiontime) /60 AS sessiontime, sum(sessionbill) AS sessionbill, count(*) as nbcall FROM pkg_cdr WHERE $filter ";
			$cdrSumResult = Yii::app()->db->createCommand($sql)->queryAll();
			

			$tax = $config['global']['invoice_tax'];
			$taxTotal = "1.".$tax;


			$title = "Invoice Amount: ". number_format($cdrSumResult[0]['sessionbill'] * $taxTotal ,2) . ' ' . $config['global']['base_currency'];
			$subTitle = "Total duration calls (Minutes): ".number_format($cdrSumResult[0]['sessiontime'],0);
			$subTitle2 = "Total amount calls: ".number_format($cdrSumResult[0]['sessionbill'],2). ' ' . $config['global']['base_currency'];
			$subTitle3 = "Tax: ". number_format($cdrSumResult[0]['sessionbill'] * '0.'.$tax ,2). ' %';

			$columns ='[
			{"header":"Date","dataIndex":"starttime"},
			{"header":"Number","dataIndex":"calledstation"},
			{"header":"Duration","dataIndex":"sessiontime"},
			{"header":"Amount","dataIndex":"sessionbill"}
			]';
			$columns = json_decode($columns, true);			
		
		
			$report                 = new Report();
			$report->orientation    = 'P';
			$report->fileReport     = $patchInvoice = $user['username'].'-'.date('Y-m-d').'.pdf';
			$report->title          = $title;
			$report->subTitle       = $subTitle;
			$report->subTitle2 	    = $subTitle2;
			$report->subTitle3 	    = $subTitle3;
			$report->user 	   	    = utf8_decode('Username: '.$user['username']);
			$report->userName  	    = utf8_decode('Name: '.$user['lastname'] .' ' .$user['lastname']);
			$report->address 	    = utf8_decode('Address: '.$user['address']);
			$report->city 	    	    = utf8_decode('City: '.$user['city']);
			$report->states 	    = utf8_decode('State: '.$user['state']);
			$report->zipcode 	    = utf8_decode('Zipcode: '.$user['zipcode']);
			$report->columns        = $columns;
			$report->columnsTable   = $this->getColumnsTable();
			$report->fieldsCurrency = $this->fieldsCurrencyReport;
			$report->fieldsPercent  = $this->fieldsPercentReport;
			$report->fieldsFk       = $this->fieldsFkReport;
			$report->renderer       = $this->rendererReport;
			$report->fieldGroup     = NULL;
			$report->records        = $cdrResult;
			$report->logo = '/var/www/html/mbilling/protected/views/invoices/logo.png';
			$report->pathFileReport = '/var/www/html/mbilling/protected/views/invoices/';
			$report->generate();

			$user['id_user'] = is_numeric($user['id_user']) ? $user['id_user'] : 1;
			
			$sql = "SELECT * FROM pkg_smtp WHERE id_user = " . $user['id_user'];
			$smtpResult = Yii::app()->db->createCommand($sql)->queryAll();
			
			if (count($smtpResult) > 0) {
		
				$smtp_host 		= $smtpResult[0]['host'];
				$smtp_encryption 	= $smtpResult[0]['encryption'];
				$smtp_username 	= $smtpResult[0]['username'];
				$smtp_password 	= $smtpResult[0]['password'];
				$smtp_port 		= $smtpResult[0]['port'];

				$message = 'Hello '. $user['lastname'] .' ' .$user['lastname'];
				$to_email = $user['email'];

				if($smtp_encryption == 'null' )
					$smtp_encryption = '';

				Yii::import('application.extensions.phpmailer.JPhpMailer');
				$mail = new JPhpMailer;
				$mail->IsSMTP();
				$mail->SMTPAuth = true;
				$mail->Host 		= $smtp_host;		
				$mail->SMTPSecure 	= $smtp_encryption;
				$mail->Username 	= $smtp_username;
				$mail->Password 	= $smtp_password;
				$mail->Port 		= $smtp_port;
				$mail->AddAttachment($report->fileReport);
				$mail->SetFrom($smtp_username);
				$mail->SetLanguage(Yii::app()->language == 'pt_BR' ? 'br' : Yii::app()->language);
				$mail->Subject = 'INVOICE';
				$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
				$mail->MsgHTML($message);
				$mail->AddAddress($to_email);
				$mail->CharSet = 'utf-8'; 

				if ($config['global']['admin_received_email'] == 1 && strlen($config['global']['admin_email'])) {
					$mail->AddAddress($config['global']['admin_email']);
				}
				ob_start();
				try {
					$mail->Send();
										
						
				} catch (Exception $e) {
					//
				}
				
				$output = ob_get_contents();
				ob_end_clean();

			}
			exec("mv -f $report->fileReport /tmp/$patchInvoice");			

		}
	}

	public function getColumnsTable() {
		$command = Yii::app()->db->createCommand('SHOW COLUMNS FROM pkg_cdr');
		return $command->queryAll();
	}
}