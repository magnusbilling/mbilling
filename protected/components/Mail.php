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
class Mail {
/**
 * Classe para envio de emails
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package	MagnusBilling
 * @author	Adilson Leffa Magnus.
 * @copyright	Todos os direitos reservados.
 * ###################################
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com> 
 * 05/11/2012
 * 
 * $mail = new Mail(Mail::$TYPE_PAYMENT, 100);
 * $mail->replaceInEmail(Mail::$ITEM_ID_KEY, 1);
 * $mail->replaceInEmail(Mail::$ITEM_NAME_KEY, 'Credito');
 * $mail->replaceInEmail(Mail::$PAYMENT_METHOD_KEY, 'Recarga de credito');
 * $mail->send();
 * $mail->send($emailAdmin);
 */


	private $id_user;
	private $message = '';
	private $title = '';
	private $from_email = '';
	private $from_name = '';
	private $to_email = '';
	private $language = '';


	static public $DESCRIPTION = '$description$';
	
	//mail type
	static public $TYPE_PAYMENT = 'payment';
	static public $TYPE_REFILL = 'refill';
	static public $TYPE_REMINDER = 'reminder';
	static public $TYPE_SIGNUP = 'signup';	
	static public $TYPE_FORGETPASSWORD = 'forgetpassword';
	static public $TYPE_SIGNUPCONFIRM = 'signupconfirmed';
	static public $TYPE_EPAYMENTVERIFY = 'epaymentverify';
	static public $TYPE_REMINDERCALL = 'reminder';
	static public $TYPE_SUBSCRIPTION_PAID = 'subscription_paid';
	static public $TYPE_SUBSCRIPTION_UNPAID = 'subscription_unpaid';
	static public $TYPE_SUBSCRIPTION_DISABLE_CARD = 'subscription_disable_card';

	static public $TYPE_DID_PAID = 'did_paid';
	static public $TYPE_DID_CONFIRMATION = 'did_confirmation';
	static public $TYPE_DID_UNPAID = 'did_unpaid';
	static public $TYPE_DID_RELEASED = 'did_released';
	
	static public $TYPE_PLAN_PAID = 'plan_paid';
	static public $TYPE_PLAN_UNPAID = 'plan_unpaid';
	static public $TYPE_PLAN_RELEASED = 'plan_released';

	static public $TYPE_INVOICE_TO_PAY = 'invoice_to_pay'; 	
	
	static public $TYPE_TEMPLATE1 = 'template1';
	static public $TYPE_TEMPLATE2 = 'template2';
	static public $TYPE_TEMPLATE3 = 'template3';
	static public $TYPE_TEMPLATE4 = 'template4';
	static public $TYPE_TEMPLATE5 = 'template5';
	static public $TYPE_TEMPLATE6 = 'template6';
	static public $TYPE_TEMPLATE7 = 'template7';
	static public $TYPE_TEMPLATE8 = 'template8';
	static public $TYPE_TEMPLATE9 = 'template9';

	static public $PLAN_LABEL = '$planname$';
	static public $PLAN_COST = '$plancost$';

	static public $OBS = '$obs$';

	//Used by mail type = invoice_to_pay
	static public $INVOICE_TITLE_KEY = '$invoice_title$';
	static public $INVOICE_REFERENCE_KEY = '$invoice_reference$';
	static public $INVOICE_DESCRIPTION_KEY = '$invoice_description$';
	static public $INVOICE_TOTAL_KEY = '$invoice_total$';
	static public $INVOICE_TOTAL_VAT_KEY = '$invoice_total_vat$';


	//Used by mail type = modify_ticket
	static public $TICKET_COMMENT_CREATOR_KEY = '$comment_creator$';
	static public $TICKET_COMMENT_DESCRIPTION_KEY = '$comment_description$';
	
	//Used by mail type = did_paid
	static public $BALANCE_REMAINING_KEY = '$balance_remaining$';

	//Used by mail type = subscription_paid OR subscription_unpaid
	static public $SUBSCRIPTION_LABEL = '$subscription_label$';
	static public $SUBSCRIPTION_ID = '$subscription_id$';
	static public $SUBSCRIPTION_FEE = '$subscription_fee$';

	//Used by mail type = did_paid OR did_unpaid OR did_released
	static public $DID_NUMBER_KEY = '$did$';
	static public $DID_COST_KEY = '$did_cost$';
    static public $DID_NUMBER_CONFIRMATION = '$did_confirmation$';
	static public $ITEM_ID_FACTURA = '$id_factura$';
    static public $DIAS_VENCIMENTO = '$dias_vencimento$';
    

	//Used by mail type = did_unpaid  & subscription_unpaid
	static public $DAY_REMAINING_KEY = '$days_remaining$';
	static public $INVOICE_REF_KEY = '$invoice_ref$';

	//Used by mail type = epaymentverify
	static public $TIME_KEY = '$time$';
	static public $PAYMENTGATEWAY_KEY = '$paymentgateway$';

	//Used by mail type = payment
	static public $ITEM_NAME_KEY = '$itemName$';
	static public $ITEM_ID_KEY = '$itemID$';
	static public $PAYMENT_METHOD_KEY = '$paymentMethod$';
	static public $PAYMENT_STATUS_KEY = '$paymentStatus$';

	//used by type = payment and type = epaymentverify
	static public $ITEM_AMOUNT_KEY = '$itemAmount$';

	//used in all mail
	static public $CUSTOMER_ID = '$idcard$';
	static public $USER_ID = '$iduser$';
	static public $CUSTOMER_EMAIL_KEY = '$email$';
	static public $CUSTOMER_FIRSTNAME_KEY = '$firstname$';
	static public $CUSTOMER_LASTNAME_KEY = '$lastname$';
	static public $CUSTOMER_CREDIT_BASE_CURRENCY_KEY = '$credit$';
	static public $CUSTOMER_CREDIT_IN_OWN_CURRENCY_KEY = '$creditcurrency$';
	static public $CUSTOMER_CURRENCY = '$currency$';
	static public $CUSTOMER_CARDNUMBER_KEY = '$cardnumber$';
	static public $CUSTOMER_PASSWORD_KEY = '$password$';
	static public $CUSTOMER_LOGIN = '$login$';
	static public $CUSTOMER_LOGINKEY = '$loginkey$';
	static public $CUSTOMER_CREDIT_NOTIFICATION = '$credit_notification$';

	//used in all mail
	static public $SYSTEM_CURRENCY = '$base_currency$';

	function __construct($type, $id_user = null, $id_agent = null, $msg = null, $title = null)
	{

		if (!empty ($type)) {

			$sql = "SELECT *, IF((typepaid=1) AND (creditlimit IS NOT NULL), credit, credit) AS real_credit , id_user, language FROM  pkg_user WHERE id = '".$id_user."'";
			$result_user = Yii::app()->db->createCommand($sql)->queryAll();


			$sql = "SELECT * FROM  pkg_templatemail WHERE mailtype = '".$type."' AND language = '".$result_user[0]['language']."' AND id_user = " . $result_user[0]['id_user'];
			$result_tmpl = Yii::app()->db->createCommand($sql)->queryAll(); 
			$order = null;
			$order_field = null;
			

			$this->id_agent = $result_user[0]['id_user'];
			
			$this->id_user = is_array($result_user) && sizeof($result_user) > 0 ? $result_user[0]['id'] : null;

			if (is_array($result_tmpl) && sizeof($result_tmpl) > 0) 
			{
				$mail_tmpl = isset($result_tmpl[0]['id']) ? $result_tmpl[0]['id'] : null;
				$this->message = $result_tmpl[0]['messagehtml'];
				$this->title = isset($result_tmpl[0]['subject']) ? $result_tmpl[0]['subject'] : null;
				$this->from_email = isset($result_tmpl[0]['fromemail']) ? $result_tmpl[0]['fromemail'] : null;
				$this->from_name = isset($result_tmpl[0]['from_name']) ? $result_tmpl[0]['from_name'] : null;
				$this->language = isset($result_tmpl[0]['language']) ? $result_tmpl[0]['language'] : null;
			} 
			else {
				Yii::log("Template Type '$type' cannot be found into the database!", 'info');
				exit ("Template Type '$type' cannot be found into the database!");
			}
			
		} elseif (!empty ($msg) || !empty ($title)) {
			$this->message = $msg;
			$this->title = $title;
		} else {
			Yii::log("Error : no Type defined and neither message or subject is provided!", 'info');
			exit ("Error : no Type defined and neither message or subject is provided!");
		}

		if(isset($id_agent) && $id_agent > 1)
		{
			$sql = "SELECT * FROM  pkg_user WHERE id = '".$id_agent."'";
			$result_agent= Yii::app()->db->createCommand($sql)->queryAll(); 
			$result_user[0]['id'] = $result_agent[0]['id'];
			$result_user[0]['username'] = $result_agent[0]['login'];
			$result_user[0]['email'] = $result_agent[0]['email'];
			$result_user[0]['firstname'] = $result_agent[0]['name'];
			$result_user[0]['lastname'] = '';
			$result_user[0]['loginkey'] = '';
			$result_user[0]['real_credit'] = $result_agent[0]['credit'];
			$result_user[0]['credit_notification'] = '';
			$result_user[0]['language'] = $result_agent[0]['language'];
		}        
		if (!empty ($this->message) || !empty ($this->title)) 
		{
			$credit = isset($result_user[0]['real_credit']) ? $result_user[0]['real_credit'] : 0;
			$credit = round($credit, 3);
			$currency = isset($result_user[0]['currency']) ? $result_user[0]['currency'] : NULL ;
			
			
			$result_user[0]['id']                  = isset($result_user[0]['id']) ? $result_user[0]['id'] : null;
			$result_user[0]['username']            = isset($result_user[0]['username']) ? $result_user[0]['username'] : null;
			$result_user[0]['email']               = isset($result_user[0]['email']) ? $result_user[0]['email'] : null;
			$result_user[0]['firstname']           = isset($result_user[0]['firstname']) ? $result_user[0]['firstname'] : null;
			$result_user[0]['lastname']            = isset($result_user[0]['lastname']) ? $result_user[0]['lastname'] : null;
			$result_user[0]['loginkey']            = isset($result_user[0]['loginkey']) ? $result_user[0]['loginkey'] : null;
			$result_user[0]['password']              = isset($result_user[0]['password']) ? $result_user[0]['password'] : null;
			$result_user[0]['credit_notification'] = isset($result_user[0]['credit_notification']) ? $result_user[0]['credit_notification'] : null;

			$credit_currency = isset($credit) ? $credit : 0;
			$this->to_email = isset($result_user[0]['email']) ? $result_user[0]['email'] : NULL;
			$this->replaceInEmail(self :: $CUSTOMER_ID, $result_user[0]['id']);
			$this->replaceInEmail(self :: $USER_ID, $result_user[0]['id']);
			$this->replaceInEmail(self :: $CUSTOMER_CARDNUMBER_KEY, $result_user[0]['username']);
			$this->replaceInEmail(self :: $CUSTOMER_EMAIL_KEY, $result_user[0]['email']);
			$this->replaceInEmail(self :: $CUSTOMER_FIRSTNAME_KEY, $result_user[0]['firstname']);
			$this->replaceInEmail(self :: $CUSTOMER_LASTNAME_KEY, $result_user[0]['lastname']);
			$this->replaceInEmail(self :: $CUSTOMER_LOGIN, $result_user[0]['username']);
			$this->replaceInEmail(self :: $CUSTOMER_LOGINKEY, $result_user[0]['loginkey']);
			$this->replaceInEmail(self :: $CUSTOMER_PASSWORD_KEY, $result_user[0]['password']);
			$this->replaceInEmail(self :: $CUSTOMER_CREDIT_IN_OWN_CURRENCY_KEY, $credit_currency);
			$this->replaceInEmail(self :: $CUSTOMER_CREDIT_BASE_CURRENCY_KEY, $credit);
			$this->replaceInEmail(self :: $CUSTOMER_CURRENCY, $currency);
			$this->replaceInEmail(self :: $CUSTOMER_CREDIT_NOTIFICATION, $result_user[0]['credit_notification']);

			$OBS = !isset($OBS) ? $this->replaceInEmail(self :: $OBS, '') : $OBS;

			$this->replaceInEmail(self :: $SYSTEM_CURRENCY, $currency);
		}
	}

	function replaceInEmail($key, $val)
	{
		$this->message = str_replace($key, $val, $this->message);
		$this->title = str_replace($key, $val, $this->title);
	}

	function getIdCard()
	{
		return $this->id_user;
	}

	function getFromEmail()
	{
		return $this->from_email;
	}
	
	function getToEmail()
	{
		return $this->to_email;
	}
	
	function getMessage()
	{
		return $this->message;
	}
	
	function AddToMessage($msg)
	{
		$this->message = $this->message . $msg;
	}
	
	function getTitle()
	{
		return $this->title;
	}

	function getFromName()
	{
		return $this->from_name;
	}
	
	function setFromEmail($from_email)
	{
		$this->from_email = $from_email;
	}

	function setTitle($title)
	{
		$this->title = $title;
	}
	
	function setMessage($message)
	{
		$this->message = $message;
	}
	
	function setToEmail($to_email)
	{
		$this->to_email = $to_email;
	}
	
	function setFromName($from_name)
	{
		$this->from_name = $from_name;
	}

	function send($to_email = null)
	{
		

		$this->from_email = !empty($this->from_email) ? $this->from_email : $to_email;
		$this->to_email = !empty ($to_email) ? $to_email : $this->to_email;

		if (strlen($this->to_email) < 5){
	        return;
	    	}


		$sql = "SELECT * FROM pkg_smtp WHERE id_user = " . $this->id_agent;
		$smtpResult = Yii::app()->db->createCommand($sql)->queryAll();

		if (count($smtpResult) == 0) {
			return;
		}
		$smtp_host 		= $smtpResult[0]['host'];
		$smtp_encryption 	= $smtpResult[0]['encryption'];
		$smtp_username 	= $smtpResult[0]['username'];
		$smtp_password 	= $smtpResult[0]['password'];
		$smtp_port 		= $smtpResult[0]['port'];



		if($smtp_encryption == 'null' )
			$smtp_encryption = '';

		if($smtp_host == '' || $smtp_username == '' || $smtp_password == '' || $smtp_port == '' )
		{
			return;
		}

		if ($smtp_host == 'mail.magnusbilling.com'){
	        return true;
	    	}

		Yii::import('application.extensions.phpmailer.JPhpMailer');
		$mail = new JPhpMailer;
		$mail->IsSMTP();
		$mail->SMTPAuth = true;
		$mail->Host 		= $smtp_host;		
		$mail->SMTPSecure 	= $smtp_encryption;
		$mail->Username 	= $smtp_username;
		$mail->Password 	= $smtp_password;
		$mail->Port 		= $smtp_port;
		$mail->SetFrom($smtp_username);
		$mail->SetLanguage($this->language == 'pt_BR' ? 'br' : $this->language);
		$mail->Subject = $this->title;
		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
		$mail->MsgHTML($this->message);
		$mail->AddAddress($this->to_email);
		$mail->CharSet = 'utf-8'; 
		ob_start();
		$mail->Send();
		$output = ob_get_contents();
		ob_end_clean();
		
		if (preg_match("/Erro/", $output)) {
	        throw new Exception($output);
	    	}
	    	return true;

	

	}

}