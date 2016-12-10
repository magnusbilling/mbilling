<?php
/**
 * Acoes do modulo "Did".
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
 * 24/09/2012
 */

class SmtpsController extends Controller
{
	public $attributeOrder        = 't.id';
	public $extraValues           = array('idUser' => 'username');


	public $fieldsFkReport = array(
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		)
	);
	
	public $fieldsInvisibleClient = array(
		'id_user',
		'password',
		'username',
		'host',
		'port',
		'encryption'
	);

	public function init()
	{
		$this->instanceModel = new Smtps;
		$this->abstractModel =  Smtps::model();
		$this->titleReport   =  Yii::t('yii','Smtp');

		parent::init();
	}

	public function actionRead()
	{
		if(Yii::app()->getSession()->get('isAdmin'))
			$this->filter =  ' AND id_user = 1';
		
		parent::actionRead();
	}
	public function actionTestMail()
	{
		$sql = "SELECT username, email FROM pkg_user WHERE id = :id";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_INT);
		$emailUserResult = $command->queryAll();

		if (!preg_match("/@/", $emailUserResult[0]['email'] )) {

			echo json_encode(array(
	               $this->nameSuccess => false,
	               $this->nameMsg => 'PLEASE CONFIGURE A VALID EMAIL TO USER '. $emailUserResult[0]['username'],
	          ));
			exit;			
		}

		$sql = "SELECT * FROM pkg_smtp WHERE id = :id";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id", $_POST['id'], PDO::PARAM_INT);
		$smtpResult = $command->queryAll();

		Yii::import('application.extensions.phpmailer.JPhpMailer');
		$language = Yii::app()->language == 'pt_BR' ? 'br' : Yii::app()->language;
		$mail = new JPhpMailer;
		$mail->IsSMTP();
		$mail->SMTPAuth = true;
		$mail->Host 		= $smtpResult[0]['host'];				
		$mail->SMTPSecure 	= $smtpResult[0]['encryption'];
		$mail->Username 	= $smtpResult[0]['username'];
		$mail->Password 	= $smtpResult[0]['password'];
		$mail->Port 		= $smtpResult[0]['port'];
		$mail->SetFrom($smtpResult[0]['username']);
		$mail->SetLanguage($language);
		$mail->Subject = 'MagnusBilling email test';
		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
		$mail->MsgHTML('<br>Hi, this is a email from MagnusBilling.');
		$mail->AddAddress($emailUserResult[0]['email'] );
		$mail->CharSet = 'utf-8'; 
		
		ob_start();
		$mail->Send();
		$output = ob_get_contents();
		ob_end_clean();
		
		if (preg_match("/Erro/", $output)) {
	        $sussess = false;
	    	}else{
	    		$output = $this->msgSuccess;
	    		$sussess = true;
	    	}

		echo json_encode(array(
           	$this->nameSuccess => $sussess,
           	$this->nameMsg => $output
       	));
	}

}