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

/*
<?php
if (isset($_GET['user']) && isset($_GET['password']))
  header('Location: http://186.225.143.142/mbilling/index.php/authentication/login?remote=1&user='.$_GET['user'].'&password='.strtoupper(MD5($_GET['password'])));
?>

<form action="" method="GET">
  <input type="text" name="user" size="18" placeholder="Username">
  <input type="password" name="password" size="18" placeholder="Password">  
  <button type="submit">Login</button>
</form>
*/

class AuthenticationController extends BaseController
{
	private $menu = array();

	public function actionLogin() {
		$user      = $_REQUEST['user'];
		$password  = $_REQUEST['password'];

		$config = LoadConfig::getConfig();

		$sql = "SELECT id_user_type FROM pkg_group_user WHERE id = ( SELECT id_group FROM pkg_user WHERE username =  :user ) ";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":user", $user, PDO::PARAM_STR);
		$resultAdmin = $command->queryAll();

		if (isset($resultAdmin[0]['id_user_type']) && $resultAdmin[0]['id_user_type'] == 1) {
			$password = sha1($password);
		}

		//check remote login
		if(isset($_REQUEST['remote'])){
			$condition = "((username COLLATE utf8_bin = :user OR email COLLATE utf8_bin LIKE :user)  OR ";
			$condition .= " (pkg_user.id = (SELECT id_user FROM pkg_sip WHERE name COLLATE utf8_bin = :user) ) )";
			$sql       = "SELECT * FROM pkg_user WHERE $condition ";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":user", $user, PDO::PARAM_STR);
			$result = $command->queryAll();

			if(!isset($result[0]['username']) || strtoupper(MD5($result[0]['password'])) != $password)
			{
				$sql       = "SELECT * FROM pkg_sip WHERE name = :name ";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":name", $user, PDO::PARAM_STR);
				$resultSIP = $command->queryAll();	

				if(!isset($resultSIP[0]['secret']) || strtoupper(MD5($resultSIP[0]['secret'])) != $password){
					exit;
				}
				$password = $resultSIP[0]['secret'];
			}
			else
				$password = $result[0]['password'];
		}


		$condition = "((username COLLATE utf8_bin = :user OR email LIKE :user) AND password COLLATE utf8_bin = :pass) OR ";
		$condition .= " (pkg_user.id = (SELECT id_user FROM pkg_sip WHERE name COLLATE utf8_bin = :user AND secret COLLATE utf8_bin = :pass) )";
		$join = " JOIN pkg_group_user ON id_group = pkg_group_user.id ";	


		$sql       = "SELECT pkg_user.id, username, id_group, id_plan, pkg_user.firstname, pkg_user.lastname , id_user_type, id_user, loginkey, active, credit, last_login, google_authenticator_key, googleAuthenticator_enable FROM pkg_user $join WHERE $condition";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":user", $user, PDO::PARAM_STR);
		$command->bindValue(":pass", $password, PDO::PARAM_STR);
		$result = $command->queryAll();
				
		$loginkey = isset($_POST['loginkey']) ? $_POST['loginkey'] : false;

		if (strlen($loginkey) > 5 && $loginkey == $result[0]['loginkey']) 
		{
			$sql= "UPDATE pkg_user SET active = 1 , loginkey = '' WHERE username LIKE :user AND password LIKE :password";		
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":user", $user, PDO::PARAM_STR);
			$command->bindValue(":password", $password, PDO::PARAM_STR);
			$command->execute();

			$result[0]['action'] = true;
			$result[0]['active'] = 1;
			$mail = new Mail(Mail::$TYPE_SIGNUPCONFIRM, $result[0]['id']);
			$mail->send();
		}

		if(!$result) {
			Yii::app()->session['logged'] = false;
			echo json_encode(array(
				'success' => false,
				'msg' => 'Username or password is wrong'
			));
			$nameMsg = $this->nameMsg;

			$info = 'Username or password is wrong - User '.$user .' from IP - '.$_SERVER['REMOTE_ADDR'];
			Yii::log($info, 'error');			
			Util::insertLOG('LOGIN',NULL,$_SERVER['REMOTE_ADDR'],$info);

			return;
		}

		if($result[0]['active'] != 1){
			Yii::app()->session['logged'] = false;
			echo json_encode(array(
				'success' => false,
				'msg' => 'Username is disabled'
			));

			$info = 'Username '.$user.' is disabled';
			Util::insertLOG('LOGIN',NULL,$_SERVER['REMOTE_ADDR'],$info);

			return;
		}	

		
		$user                                = $result[0];

		Yii::app()->session['isAdmin']       = $user['id_user_type'] == 1 ? true : false;
		Yii::app()->session['isAgent']       = $user['id_user_type'] == 2 ? true : false;
		Yii::app()->session['isClient']      = $user['id_user_type'] == 3 ? true : false;
		Yii::app()->session['isClientAgent'] = isset( $user['id_user']) &&  $user['id_user'] > 1 ? true : false;
		Yii::app()->session['id_plan']       = $user['id_plan'];
		Yii::app()->session['credit']        = isset($user['credit']) ? $user['credit'] : 0;
		Yii::app()->session['username']      = $user['username'];
		Yii::app()->session['logged']        = true;
		Yii::app()->session['id_user']       = $user['id'];
		Yii::app()->session['id_agent']      = is_null($user['id_user']) ? 1 : $user['id_user'];
		Yii::app()->session['name_user']     = $user['firstname']. ' ' . $user['lastname'];
		Yii::app()->session['id_group']      = $user['id_group'];
		Yii::app()->session['user_type']     = $user['id_user_type'];
		Yii::app()->session['systemName']    = $_SERVER['SCRIPT_FILENAME'];	

		Yii::app()->session['licence']   	  = $config['global']['licence'];
		Yii::app()->session['email']   	  = $config['global']['admin_email'];
		Yii::app()->session['currency']      = $config['global']['base_currency'];
		Yii::app()->session['language']      = $config['global']['base_language'];
		Yii::app()->session['decimal']       = $config['global']['decimal_precision'];
		Yii::app()->session['base_country']  = $config['global']['base_country'];
		Yii::app()->session['version']  	  = $config['global']['version'];
		Yii::app()->session['asterisk_version']  = $config['global']['asterisk_version'];
		Yii::app()->session['social_media_network']  = $config['global']['social_media_network'];
		Yii::app()->session['fm_transfer_show_selling_price']  =  preg_replace("/%/", "", $config['global']['fm_transfer_show_selling_price']);


		$sql = "SELECT count(*) as config_value FROM pkg_user WHERE credit != :credit";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":credit", 0, PDO::PARAM_STR);
		$result = $command->queryAll();

		Yii::app()->session['userCount']  	  = $result[0]['config_value'];

		if ($user['googleAuthenticator_enable'] > 0) {				

			require_once ('lib/GoogleAuthenticator/GoogleAuthenticator.php');
			$ga = new PHPGangsta_GoogleAuthenticator();


			if ($user['google_authenticator_key'] != '') {
				$secret = $user['google_authenticator_key'];
				Yii::app()->session['newGoogleAuthenticator']  = false;
				if ($user['googleAuthenticator_enable'] == 2) {
					Yii::app()->session['showGoogleCode']  = true;
				}else{
					Yii::app()->session['showGoogleCode'] =false;
				}
			}else{
				$secret = $ga->createSecret();
				$sql = "UPDATE pkg_user set google_authenticator_key = :secret WHERE id = :id";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":secret", $secret , PDO::PARAM_STR);
				$command->bindValue(":id", $user['id'], PDO::PARAM_STR);
				$result = $command->execute();
				Yii::app()->session['newGoogleAuthenticator']  = true;

			}

			Yii::app()->session['googleAuthenticatorKey']  = $ga->getQRCodeGoogleUrl('MBilling-'.$user['username'].'-'.$user['id'], $secret);
	
			Yii::app()->session['checkGoogleAuthenticator']  = true;
		}else{
			Yii::app()->session['showGoogleCode'] =false;
			Yii::app()->session['newGoogleAuthenticator']  = false;
			Yii::app()->session['checkGoogleAuthenticator']  = false;
		}

		
		$sql = "SELECT id_group FROM pkg_group_user_group WHERE id_group_user = :id_group";
        	$command = Yii::app()->db->createCommand($sql);
        	$command->bindValue(":id_group", Yii::app()->getSession()->get('id_group'), PDO::PARAM_STR);
        	$resultGroupAllowed = $command->queryAll();
        	Yii::app()->session['adminLimitUsers'] = count($resultGroupAllowed);

		if (isset($_REQUEST['remote'])) {
			header("Location: ../..");
		}
		echo json_encode(array(
			'success' => Yii::app()->session['username'],
			'msg' => Yii::app()->session['name_user']
		));

		Util::insertLOG('LOGIN',Yii::app()->session['id_user'],$_SERVER['REMOTE_ADDR'],'');

	}

	private function mountMenu()
	{
		if (Yii::app()->session['isClient']) {			

			$sql ="(SELECT m.id, action, show_menu, text, module, icon_cls, m.id_module, gm.createShortCut, gm.createQuickStart
					FROM pkg_group_module gm 
					INNER JOIN pkg_module m ON gm.id_module = m.id
					WHERE id_group = :id_group)
				UNION 
					(SELECT m.id, action, show_menu, text, module, icon_cls, m.id_module, gm.createShortCut, gm.createQuickStart 
					FROM pkg_services_module gm 
					INNER JOIN pkg_module m ON gm.id_module = m.id 
					WHERE gm.id_services IN (SELECT id_services FROM pkg_services_use WHERE id_user = :id_user AND status = 1)) 
				ORDER BY id, LENGTH(ACTION) DESC, show_menu DESC";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_group", Yii::app()->session['id_group'] , PDO::PARAM_INT);
			$command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_INT);
			$result = $command->queryAll();
			//remove duplicate on permissions
			$result = Util::unique_multidim_array($result,'id');
		}else{
			$sql   = "SELECT m.id, action, show_menu, text, module, icon_cls, m.id_module, gm.createShortCut, 
								gm.createQuickStart FROM pkg_group_module gm 
								INNER JOIN pkg_module m ON gm.id_module = m.id 
								WHERE id_group = :id_group";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_group", Yii::app()->session['id_group'], PDO::PARAM_STR);
			$result = $command->queryAll();
		}		

		Yii::app()->session['action']        = $this->getActions($result);
		Yii::app()->session['menu'] 		 = $this->getMenu($result);
	}
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

	public function actionLogoff() {
		Yii::app()->session['logged']        = false;
		Yii::app()->session['id_user']       = false;
		Yii::app()->session['id_agent']      = false;
		Yii::app()->session['name_user']     = false;
		Yii::app()->session['menu']          = array();
		Yii::app()->session['action']        = array();
		Yii::app()->session['currency']      = false;
		Yii::app()->session['language']      = false;
		Yii::app()->session['isAdmin']       = true;
		Yii::app()->session['isClient']      = false;
		Yii::app()->session['isAgent']       = false;
		Yii::app()->session['isClientAgent'] = false;
		Yii::app()->session['id_plan']       = false;
		Yii::app()->session['credit']        = false;
		Yii::app()->session['username']      = false;
		Yii::app()->session['id_group']      = false;
		Yii::app()->session['user_type']	  = false;
		Yii::app()->session['decimal']= false;
		Yii::app()->session['licence']	  = false;
		Yii::app()->session['email']	       = false;
		Yii::app()->session['userCount']	  = false;
		Yii::app()->session['systemName']    = false;
		Yii::app()->session['base_country']  = false;
		Yii::app()->session['version']	  = false;

		Yii::app()->session->clear();
		Yii::app()->session->destroy();


		echo json_encode(array(
			'success' => true
		));
	}

	public function actionCheck() {
		if(Yii::app()->session['logged']) {
			$this->mountMenu();
			$id_user       = Yii::app()->session['id_user'];
			$id_agent      = Yii::app()->session['id_agent'];
			$nameUser      = Yii::app()->session['name_user'];
			$logged        = Yii::app()->session['logged'];
			$menu          = Yii::app()->session['menu'];
			$currency      = Yii::app()->session['currency'];
			$language      = Yii::app()->session['language'];
			$isAdmin       = Yii::app()->session['isAdmin'];
			$isClient      = Yii::app()->session['isClient'];
			$isAgent       = Yii::app()->session['isAgent'];
			$isClientAgent = Yii::app()->session['isClientAgent'];
			$id_plan       = Yii::app()->session['id_plan'];
			$credit        = Yii::app()->session['credit'];
			$username      = Yii::app()->session['username'];
			$id_group      = Yii::app()->session['id_group'];
			$user_type 	= Yii::app()->session['user_type'];
			$decimal       = Yii::app()->session['decimal'];
			$licence 		= Yii::app()->session['licence'];
			$email 		= Yii::app()->session['email'];
			$userCount 	= Yii::app()->session['userCount'];
			$base_country 	= Yii::app()->session['base_country'];
			$version 		= Yii::app()->session['version'];
			$social_media_network = Yii::app()->session['social_media_network'];
			$fm_transfer_show_selling_price 	= Yii::app()->session['fm_transfer_show_selling_price'];
			$checkGoogleAuthenticator = Yii::app()->session['checkGoogleAuthenticator'];
			$googleAuthenticatorKey = Yii::app()->session['googleAuthenticatorKey'];
			$newGoogleAuthenticator = Yii::app()->session['newGoogleAuthenticator'];
			$showGoogleCode = Yii::app()->session['showGoogleCode'];		
		
		}
		else {
			$id_user       = false;
			$id_agent      = false;
			$nameUser      = false;
			$logged        = false;
			$menu          = array();
			$currency      = false;
			$language      = false;
			$isAdmin       = false;
			$isClient      = false;
			$isAgent       = false;
			$isClientAgent = false;
			$id_plan       = false;
			$credit        = false;
			$username      = false;
			$id_group      = false;
			$user_type     = false;
			$decimal       = false;
			$licence 		= false;
			$email 		= false;
			$userCount 	= false;
			$base_country	= false;
			$version 		= false;
			$fm_transfer_show_selling_price = false;
			$checkGoogleAuthenticator = false;
			$googleAuthenticatorKey = false;
			$newGoogleAuthenticator =false;
			$showGoogleCode = false;
			$social_media_network = false;

		}

		$language = isset(Yii::app()->session['language']) ? Yii::app()->session['language'] : Yii::app()->sourceLanguage;
		$theme    = isset(Yii::app()->session['theme']) ? Yii::app()->session['theme'] : 'blue-neptune';


		if (file_exists('resources/images/logo_custom.png')) {
			Yii::log('file existe', 'info');
		}

	
		echo json_encode(array(
			'id'            => $id_user,
			'id_agent'      => $id_agent,
			'name'          => $nameUser,
			'success'       => $logged,
			'menu'          => $menu,
			'language'      => $language,
			'theme'         => $theme,
			'currency'      => $currency,
			'language'      => $language,
			'isAdmin'       => $isAdmin,
			'isClient'      => $isClient,
			'isAgent'       => $isAgent,
			'isClientAgent' => $isClientAgent,
			'id_plan'       => $id_plan,
			'credit'        => $credit,
			'username'      => $username,
			'id_group'      => $id_group,
			'user_type'     => $user_type,
			'decimal'       => $decimal,
			'licence' 	 => $licence,
			'email' 	 	 => $email,
			'userCount' 	 => $userCount,
			'base_country'  => $base_country,
			'version' 	 => $version,
			'social_media_network' => $social_media_network,
			'fm_transfer_show_selling_price' => $fm_transfer_show_selling_price,
			'asterisk_version' => Yii::app()->session['asterisk_version'],
			'checkGoogleAuthenticator' => $checkGoogleAuthenticator,
			'googleAuthenticatorKey' => $googleAuthenticatorKey,
			'newGoogleAuthenticator' => $newGoogleAuthenticator,
			'showGoogleCode' => $showGoogleCode,
			'logo'          => file_exists('resources/images/logo_custom.png') ? 'resources/images/logo_custom.png' : 'resources/images/logo.png'
		));
	}

	public function actionGoogleAuthenticator()
	{
		require_once ('lib/GoogleAuthenticator/GoogleAuthenticator.php');

		$ga = new PHPGangsta_GoogleAuthenticator();


		$sql = "SELECT google_authenticator_key FROM pkg_user WHERE id = :id ";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_STR);
		$result = $command->queryAll();

		//Yii::log(print_r($sql,true),'info');
		$secret = $result[0]['google_authenticator_key'];
		$oneCodePost = $_POST['oneCode'];

		$checkResult = $ga->verifyCode($secret, $oneCodePost, 2);

		if ($checkResult) {
		    	$sussess = true;
		    	Yii::app()->session['checkGoogleAuthenticator'] = false;
		    	$sql = "UPDATE pkg_user SET googleAuthenticator_enable = 1 WHERE id = :id";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_STR);
			$command->execute();
		} else {
		    $sussess = false;
		}
		//$sussess = true;

		echo json_encode(array(
			'success' => $sussess,
			'msg' => Yii::app()->session['name_user']
		));

	}

	public function actionChangePassword()
	{
		$passwordChanged = false;
		$id_user          = Yii::app()->session['id_user'];
		$currentPassword = $_POST['current_password'];
		$newPassword     = $_POST['password'];
		$isClient        = Yii::app()->session['isClient'];
		$errors='';

		$condition       = "id LIKE :id_user AND password LIKE :currentPassword";
		$sql  = "SELECT * from pkg_user WHERE $condition";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id_user", $id_user, PDO::PARAM_STR);
		$command->bindValue(":currentPassword", $currentPassword, PDO::PARAM_STR);
		$result = $command->queryAll();

		if(count($result) > 0)
		{
			try
			{
				$sql ="UPDATE pkg_user set password = :newPassword WHERE id LIKE :id_user";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_user", $id_user, PDO::PARAM_STR);
				$command->bindValue(":newPassword", $newPassword, PDO::PARAM_STR);
				$passwordChanged = $command->execute();

			}
			catch (Exception $e)
			{
				$errors = $this->getErrorMySql($e);
			}

			$msg = $passwordChanged ? yii::t('yii','Password change success!') : $errors;
		}
		else
		{
			$msg = yii::t('yii','Current Password incorrect.');
		}

		echo json_encode(array(
			'success' => $passwordChanged,
			'msg' => $msg
		));
	}


	function rfts($strFileName, $intLines = 0, $intBytes = 4096, $booErrorRep = true)
    {

        $strFile = "";
        $intCurLine = 1;

        if (file_exists($strFileName)) {
            if ($fd = fopen($strFileName, 'r')) {
                while (!feof($fd)) {
                    $strFile .= fgets($fd, $intBytes);
                    if ($intLines <= $intCurLine && $intLines != 0) {
                        break;
                    } else {
                        $intCurLine++;
                    }
                }
                fclose($fd);
            }          
        }
        return $strFile;
    }

    function chostname()
    {
        $result = $this->rfts('/proc/sys/kernel/hostname', 1);
        if ($result == "ERROR") {
            $result = "N.A.";
        } else {
            $result = gethostbyaddr(gethostbyname(trim($result)));
        }
        return $result;
    }

    // get the IP address of our canonical hostname
    function ip_addr()
    {
        if (!($result = getenv('SERVER_ADDR'))) 
        {
            $result = gethostbyname($this->chostname());
        }
        return $result;
    }



	public function actionImportLogo()
	{
		if (isset($_FILES['logo']['tmp_name']) && strlen($_FILES['logo']['tmp_name']) > 3) {
	
               $uploaddir = "resources/images/";
			$typefile = explode('.', $_FILES["logo"]["name"]);
			$uploadfile = $uploaddir .'logo_custom.png';
			move_uploaded_file($_FILES["logo"]["tmp_name"], $uploadfile);
          }

          echo json_encode(array(
			'success' => true,
			'msg' => 'Refresh the system to see the new logo'
		));
	}

	public function actionImportWallpapers()
	{
		if (isset($_FILES['logo']['tmp_name']) && strlen($_FILES['logo']['tmp_name']) > 3) {
	
               $uploaddir = "resources/images/wallpapers/";
			$typefile = explode('.', $_FILES["logo"]["name"]);
			$uploadfile = $uploaddir .'Customization.jpg';
			move_uploaded_file($_FILES["logo"]["tmp_name"], $uploadfile);
          }

          $sql = "UPDATE pkg_configuration SET  config_value = 'Customization' WHERE config_key LIKE 'wallpaper'";
		Yii::app()->db->createCommand($sql)->execute();

          echo json_encode(array(
			'success' => true,
			'msg' => 'Refresh the system to see the new logo'
		));
	}

	
}