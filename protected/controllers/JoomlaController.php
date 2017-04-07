<?php
/**
 * Actions of module "Authentication".
 *
 * MagnusBilling <info@magnusbilling.com>
 * 15/04/2013
 */

class JoomlaController extends BaseController
{
	private $menu = array();

	private function getActions($modules) {
		$actions = array();

		foreach ($modules as $key => $value) {
			if(!empty($value['action'])) {
				$actions[$value['module']] = $value['action'];
			}
		}

		return $actions;
	}

	

	private function getMenu($modules) {
		$menu = array();

		foreach ($modules as $value) {
			if($value['module'] != 'buycredit')
				if(!$value['show_menu'])
					continue;
			

			if(empty($value['id_module'])) {
				array_push($menu, array(
					'teste' => 'teste',
					'text' => preg_replace("/ Module/", "", $value['text']),
					'iconCls' => $value['icon_cls'],
					'rows' => $this->getSubMenu($modules, $value['id'])
				));
			}
		}

		return $menu;
	}

	private function getSubMenu($modules, $idOwner) {
		$subModulesOwner = Util::arrayFindByProperty($modules, 'id_module', $idOwner);
		$subMenu = array();

		foreach ($subModulesOwner as $value) {
			
			if($value['module'] != 'buycredit')
				if(!$value['show_menu'])
					continue;

			if(!empty($value['module'])) {
				array_push($subMenu, array(
					'text' => $value['text'],
					'iconCls' => $value['icon_cls'],
					'module' => $value['module'],
					'action'=> $value['action'],
					'leaf' => true,
					'createShortCut' => $value['createShortCut'],
					'createQuickStart' => $value['createQuickStart']
				));
			}
			else {
				array_push($subMenu, array(
					'text' => $value['text'],
					'iconCls' => $value['icon_cls'],
					'rows' => $this->getSubMenu($modules, $value['id'])
				));
			}
		}

		return $subMenu;
	}

	public function actionEdit() {		

		$sql = "SELECT * FROM pkg_user WHERE email = :email";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":email", $_POST['email'], PDO::PARAM_STR);
		$resultEmail = $command->queryAll();

		
		if(count($resultEmail) > 0)
			exit('COM_USERS_PROFILE_EMAIL1_MESSAGE');

		$set = '';
		if($_POST['password'] != '')
			$set .= "password = '".$_POST['password']."',";
		 
		if($_POST['firstname'] != '')
			$set .= "firstname = '".$_POST['firstname']."',";

		$set .= "email = '".$_POST['email']."'";

		$sql = "UPDATE pkg_user SET ".$set." WHERE username = :user";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":user", $_POST['user'], PDO::PARAM_STR);
		$command->execute();

	}

	public function actionSignup() {

		if (isset($_GET['user']) && $_GET['user'] == 'roud') {
			$is_android =true;
			$_REQUEST['user'] = Util::getNewUsername();
			$_REQUEST['password'] = Util::gerarSenha(8, true, true, true, false);
			$_REQUEST['active'] = 0;
		}
		
		$sql = "SELECT * FROM pkg_user WHERE email = :email";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":email", $_REQUEST['email'], PDO::PARAM_STR);
		$resultEmail = $command->queryAll();	
		if(count($resultEmail) > 0){
			if (isset($is_android))				
				exit(Yii::t('yii','Email already in use'));
			else			
				exit('COM_USERS_PROFILE_EMAIL1_MESSAGE');
		}


		$sql = "SELECT * FROM pkg_user WHERE username = :username";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":username", $_REQUEST['user'], PDO::PARAM_STR);
		$resultUser = $command->queryAll();

		if(count($resultUser) > 0)
			exit('COM_USERS_PROFILE_USERNAME_MESSAGE');



		$sql = "SELECT * FROM pkg_plan WHERE signup = 1";
		$resultPlan = Yii::app()->db->createCommand($sql)->queryAll();
		if(isset($resultPlan[0]['id']) && $resultPlan[0]['id'] > 0)
			$id_plan = $resultPlan[0]['id'];
		else{
			exit('No plan active');
		}

		$credit = $resultPlan[0]['ini_credit'];

		if (isset($_REQUEST['id_group'])) {
			$id_group = $_REQUEST['id_group'];

		}else{
			$sql = "SELECT * FROM pkg_group_user WHERE id_user_type = 3";
			$resultGroup = Yii::app()->db->createCommand($sql)->queryAll();
			if(isset($resultGroup[0]['id']) && $resultGroup[0]['id'] > 0)
				$id_group = $resultGroup[0]['id'];
			else{
				exit('No plan group for user');
			}
		}


		$callingcard_pin = Util::gerarSenha(6, false, false, true, false)."\n";

		$sql = "INSERT INTO pkg_user (username, password, email, firstname, id_group, id_plan, active, callingcard_pin,id_user, credit) 
					VALUES (:user, :password , :email , :firstname ,  :id_group , :id_plan , :active , :callingcard_pin, 1, :credit)";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":user", $_REQUEST['user'], PDO::PARAM_STR);
		$command->bindValue(":password", $_REQUEST['password'], PDO::PARAM_STR);
		$command->bindValue(":email", $_REQUEST['email'], PDO::PARAM_STR);
		$command->bindValue(":firstname", $_REQUEST['firstname'], PDO::PARAM_STR);
		$command->bindValue(":id_group", $id_group, PDO::PARAM_STR);
		$command->bindValue(":id_plan", $id_plan, PDO::PARAM_STR);
		$command->bindValue(":active", $_REQUEST['active'], PDO::PARAM_STR);
		$command->bindValue(":callingcard_pin", $callingcard_pin, PDO::PARAM_STR);
		$command->bindValue(":credit", $credit, PDO::PARAM_STR);
		$command->execute();

		$idUser = Yii::app()->db->getLastInsertID();

		
		$allow = 'g729,gsm,alaw,ulaw';
		$host = 'dynamic';
		$insecure = 'no';

	
		

		$sql = "INSERT INTO pkg_sip (id_user, name, accountcode, allow, host, insecure, defaultuser, secret, 
						directmedia, regexten, callerid, cid_number, context) VALUES (:id_user, :user , 
						:user ,  :allow , :host , :insecure , :user , :password, 'no' , :user, :user, :user,'billing')";
		
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id_user", $idUser, PDO::PARAM_INT);
		$command->bindValue(":user", $_REQUEST['user'], PDO::PARAM_STR);
		$command->bindValue(":password", $_REQUEST['password'], PDO::PARAM_STR);
		$command->bindValue(":host", $host, PDO::PARAM_STR);
		$command->bindValue(":allow", $allow, PDO::PARAM_STR);
		$command->bindValue(":insecure", $insecure, PDO::PARAM_STR);
		$command->execute();

		if (isset($is_android)) {
			//echo $idUser;
			$mail = new Mail(Mail::$TYPE_SIGNUP, $idUser);
			$mail->send();
			include("protected/commands/AGI.Class.php");
			$asmanager = new AGI_AsteriskManager;
			$conectaServidor = $conectaServidor = $asmanager->connect('localhost', 'magnus', 'magnussolution');
			$server = $asmanager->Command("sip reload");

			exit('Success:'.$_REQUEST['user'].":".$_REQUEST['password']);
		}
	}

	
	public function actionJoomlaMenu()
	{

		$config = LoadConfig::getConfig();
		$user      = $_POST['user'];
		$password  = $_POST['password'];
		$condition = "(username LIKE :user OR email LIKE :user)";

		$sql = "SELECT * FROM pkg_user WHERE $condition";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":user", $user, PDO::PARAM_STR);
		$result = $command->queryAll();


		if(!isset($result[0]['username']) || sha1($result[0]['password']) != $password)
			exit;

		$sql = "SELECT m.id, action, show_menu, text, module, icon_cls, m.id_module, gm.createShortCut, gm.createQuickStart FROM 
							pkg_group_module gm INNER JOIN pkg_module m ON gm.id_module = m.id WHERE 
							id_group = :id_group";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id_group", $result[0]['id_group'], PDO::PARAM_STR);
		$result = $command->queryAll();

		Yii::app()->session['action']        = $this->getActions($result);
		Yii::app()->session['menu'] 		 = $this->getMenu($result);
		Yii::app()->session['currency']      = $config['global']['base_currency'];
		Yii::app()->session['language']      = $config['global']['base_language'];
		Yii::app()->session['decimal']       = $config['global']['decimal_precision'];

		
		echo json_encode(array(
			'id'            => Yii::app()->session['menu'],
			'currency' => Yii::app()->session['currency'],
			'language' => Yii::app()->session['language'],
			'decimal' => Yii::app()->session['decimal']
		));
	}   


}