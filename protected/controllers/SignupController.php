<?php

/**
 * Url for customer register http://ip/billing/index.php/user/add .
 */
class SignupController extends BaseController
{

	public function actions()
	{
		return array(
		'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
				),
		);
	}
	public function actionView($id){
		$signup = Signup::model()->findByAttributes(array('username'=>$_GET['username'] , 'password' => $_GET['password'], 'id' => $id));
		if (count($signup) < 1) {
			$this->redirect(array('add'));
		}
		$loginkey = isset($_GET['loginkey']) ? true : false;


		if (!$loginkey) {

			$mail = new Mail(Mail::$TYPE_SIGNUP, $id);
			$mail->send();
		}

		$signup->active = 1;
		$signup->lastname = strlen($signup->lastname) < 1 ? 'lastname' : $signup->lastname;
		$signup->state = strlen($signup->state) < 1 ? 'state' : $signup->state;
		$signup->city = strlen($signup->city) < 1 ? 'city' : $signup->city;
		$signup->phone = strlen($signup->phone) < 1 ? '10000000000' : $signup->phone;
		$signup->password2 = $signup->password;
		$signup->save();
		
		$this->render('view', array('signup'=>$signup));
	}

	public function actionAdd()
	{
		$signup=new Signup();
		$config = LoadConfig::getConfig();
		if(isset($_POST['Signup']))
		{

			$result = GroupUser::model()->findAllByAttributes(array("id_user_type"=> 3));

			$password = trim($_POST['Signup']['password']);

			$signup->id_group = $result[0]['id'];
			$signup->active = $_POST['Signup']['id_user'] > 1 ? 1 : 2;


			if ($config['global']['base_language'] == 'pt_BR') {
				$phone = $_POST['Signup']['phone'];
				if (substr($phone, 0,2) == 55 && strlen($phone) > 11)
					$ddd = substr($phone, 2,2);					
				elseif(substr($phone, 0,1) == 0)
					$ddd = substr($phone, 1,2);
				elseif(strlen($phone) == 10 || (strlen($phone) == 11 && substr($phone, 2,1) == 9))
					$ddd = substr($phone,0,2);
				else
					$ddd = 11;				
				$signup->prefix_local = '0/55,*/55'.$ddd.'/8,*/55'.$ddd.'/9';
			}else{
				$signup->prefix_local = '';
			}
			
			if (!isset($_POST['Signup']['username']))
				$signup->username = Util::getNewUsername();

			$signup->callingcard_pin = Util::getNewLock_pin();
			$signup->loginkey = trim(Util::gerarSenha(20, true, true, true, false));
			$signup->credit = $_POST['Signup']['ini_credit'];
			unset($_POST['Signup']['ini_credit']);

			$signup->typepaid = 0;
			$signup->language = $_SESSION['language'] == 'pt_BR' ? 'br' : $_SESSION['language'];
			$signup->calllimit = $config['global']['start_user_call_limit'];
			$signup->attributes=$_POST['Signup'];
			$signup->company_name = $_POST['Signup']['company_name'];
			$sucess = $signup->save();
	
			//print_r($_POST);
			//add the new user to SuperLogica
			$this->createUserinSuperLogica();

			if($sucess)
			{
				$modelSip = new Sip();
				$modelSip->id_user = $signup->id;
				$modelSip->accountcode = $signup->username;
				$modelSip->name = $signup->username;
				$modelSip->allow = 'g729,gsm';
				$modelSip->host = 'dynamic';
				$modelSip->insecure = 'no';
				$modelSip->defaultuser = $signup->username;
				$modelSip->secret = $password;
				$modelSip->callerid = $signup->phone;
				$modelSip->cid_number = $signup->phone;
				$modelSip->save();


				$select = 'accountcode, name, defaultuser, secret, regexten, amaflags, callerid, language, cid_number, 
								disallow, allow, directmedia, context, dtmfmode, insecure, nat, qualify, type, host, calllimit';
				$modelSip =Sip::model()->findAll( array(
						'select'=>$select,
						'condition' => "host != 'dynamic' OR  calllimit > 0"
					) );

				if (count($modelSip))        
				  	AsteriskAccess::instance()->writeAsteriskFile($modelSip, '/etc/asterisk/sip_magnus_user.conf','name');
    		

				$this->redirect(array('view','id'=>$signup->id, 'username' => $signup->username, 'password' => $signup->password, 'id_user' => $_POST['Signup']['id_user']));
			}
				
		}
		//if exist get id, find agent plans else get admin plans
		if (isset($_GET['id'])) {
			$filter = "AND username = :id";
			$params = array(":id" => $_GET['id']);
		}else{
			$filter = "AND t.id_user = :id";
			$params = array(":id" => 1);
		}

		$modelPlan = Plan::model()->findAll(array(
			'condition' => 'signup = 1 ' . $filter,
			'join' => 'JOIN pkg_user ON t.id_user = pkg_user.id',
			'params' => $params
		));

		if($config['global']['signup_auto_pass'] > 5)
			$pass = Util::gerarSenha($config['global']['signup_auto_pass'], true, true, true, false);
		else
			$pass = 0;

		//render to ADD form
		$this->render('add',	
			array(
				'signup'=>$signup, 
				'plan'=>$modelPlan, 
				'autoPassword' => $pass, 
				'autoUser' => $config['global']['auto_generate_user_signup'],
				'language' =>$config['global']['base_language'],
				'termsLink' => $config['global']['accept_terms_link']
				)
			);
	}

	public function createUserinSuperLogica()
	{	
		$modelMethodPay = Methodpay::model()->find("payment_method = 'SuperLogica' AND active = 1");
		if (count($modelMethodPay)) {	

			$response  = SLUserSave::saveUserSLCurl($this,$modelMethodPay->SLAppToken
										,$modelMethodPay->SLAccessToken,false);

			if(isset( $response[0]->data->id_sacado_sac))
				$this->id_sacado_sac = $response[0]->data->id_sacado_sac;
		}
		return;
	}
}