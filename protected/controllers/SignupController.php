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
			$signup->prefix_local = '0/55,*/5511/8,*/5511/9';
			
			$signup->username = Util::getNewUsername();
			$signup->callingcard_pin = Util::getNewLock_pin();
			$signup->loginkey = trim(Util::gerarSenha(20, true, true, true, false));
			$signup->credit = $_POST['Signup']['ini_credit'];
			unset($_POST['Signup']['ini_credit']);

			$signup->typepaid = 0;
			$signup->language = $_SESSION['language'] == 'pt_BR' ? 'br' : $_SESSION['language'];

			$signup->attributes=$_POST['Signup'];

			$sucess = $signup->save();	

		

			if($sucess){
				$fields = "id_user, accountcode, name, allow, host, insecure, defaultuser, secret";
				$values = " :id_user, :username, :username, 'g729,gsm', 'dynamic', 'no', :username, :password";
				$sql = "INSERT INTO pkg_sip ($fields) VALUES ($values)";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_user", $signup->id, PDO::PARAM_STR);
				$command->bindValue(":username", $signup->username, PDO::PARAM_STR);
				$command->bindValue(":password", $password, PDO::PARAM_STR);
				$sucess = $command->execute();

				
				$this->redirect(array('view','id'=>$signup->id, 'username' => $signup->username, 'password' => $signup->password, 'id_user' => $_POST['Signup']['id_user']));
			}
				
		}
		//if exist get id, find agent plans else get admin plans
		$sql = "SELECT pkg_plan.id, pkg_plan.name, pkg_plan.id_user, pkg_plan.ini_credit FROM pkg_plan JOIN pkg_user 
					ON pkg_plan.id_user = pkg_user.id WHERE signup = 1 ";
		if (isset($_GET['id'])) {		
			$sql .= "AND username = :id";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", $_GET['id'], PDO::PARAM_STR);
		}else{
			$sql .= " AND pkg_plan.id_user = 1";
			$command = Yii::app()->db->createCommand($sql);			
		}		
		$plan = $command->queryAll();

		if($config['global']['signup_auto_pass'] > 5)
			$pass = Util::gerarSenha($config['global']['signup_auto_pass'], true, true, true, false);
		else
			$pass = 0;

		//render to ADD form
		$this->render('add',array('signup'=>$signup, 'plan'=>$plan, 'autoPassword' => $pass));
	}
}