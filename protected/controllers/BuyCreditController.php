<?php

/**
 * Url for customer register http://ip/billing/index.php/user/add .
 */
class BuyCreditController extends BaseController
{

	public function init()
	{		
		$startSession = strlen(session_id()) < 1 ? session_start() : null;			
		parent::init();
	}

	public function actionMethod(){

		$config = LoadConfig::getConfig();

		if (isset($_GET['l'])) {
			$data = explode('|', $_GET['l']);
			$user= $data[0];
			$pass = $data[1];
			$_GET['amount'] = $data[2];

			$sql = "SELECT pkg_user.id, 'username',firstname, lastname, credit, pkg_user.id_user, id_plan, 
			pkg_user.id_user, '".$config['global']['base_currency']."' AS currency, secret
			FROM pkg_sip join pkg_user ON pkg_sip.id_user = pkg_user.id WHERE pkg_sip.name = :user" ;
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":user", $user, PDO::PARAM_STR);
			$result = $command->queryAll();


		
			if(!isset($result[0]['username']) || strtoupper(MD5($result[0]['secret'])) != $pass){
				echo 'User or password is invalid';
				exit;
			}

			$sql = "SELECT id, active FROM pkg_method_pay WHERE payment_method = :payment_method";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":payment_method", 'Paypal', PDO::PARAM_STR);
			$resultPaypal = $command->queryAll();


			$_GET['id_method'] = $resultPaypal[0]['id'];

			$_SESSION['id_user'] = $result[0]['id'];
			$_SESSION['id_agent'] = $result[0]['id_user'];
			$_SESSION["username"] = $user;
			$_SERVER['argv'][0] = 'cron';

			$_SESSION['language'] = $config['global']['base_language'];

			$_SESSION['currency'] = $config['global']['base_currency'];
		}


		$methodPay= BuyCredit::model()->findByPK($_GET['id_method']);
		
		if ($methodPay->active == 0 || $methodPay->id_user != Yii::app()->getSession()->get('id_agent'))
			exit('invalid option');

		$card      = User::model()->findByPK(Yii::app()->getSession()->get('id_user'));


		if ($methodPay->payment_method == 'BoletoBancario') {
			$this->actionBoletoBancario();
		}
		else if ($methodPay->payment_method == 'SuperLogica') {
			SLUserSave::criarBoleto($methodPay,$card);
		}
		else{
			$this->render(strtolower($methodPay->payment_method),array(
			'methodPay' => $methodPay,
			'card'      => $card
			));
		}
		
	}

	public function actionIndex()
	{

		if (Yii::app()->getSession()->get('isClient')) 
		{
			$modelCard = User::model()->findByPK(Yii::app()->getSession()->get('id_user'));
			$id_agent = is_null($modelCard->id_user) ? 1 : $modelCard->id_user;
		}

		$methodPay= BuyCredit::model()->findAllByAttributes(array('active'=> '1' , 'id_user' => $id_agent ));

	
		if (isset($_REQUEST['amount']) && isset($_REQUEST['method']))
		{
			$methodPayName = BuyCredit::model()->findByPK($_POST['method']);
			$this->redirect(array($methodPayName->payment_method,'id'=>$_POST['method'], 'amount' => $_POST['amount']));
			exit;
		}

		$this->render('index',array(
			'methodPay'    =>$methodPay, 
			'amount'       => $amount,
			'basecurrency' => $basecurrency,
			));
	}


	public function actionBoletoBancario()
	{
		$dataVencimento =  date("Y-m-d ", mktime(0, 0, 0, date("m"), date("d") + 12, date("Y"))) . date('H:i:s');
		$sql      = "INSERT INTO pkg_boleto (id_user, date, description, status, payment, vencimento) 
						VALUES (:user,  :dataPedido , 'Credito', '0', :amount, :dataVencimento)";		
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":user", $_SESSION['id_user'], PDO::PARAM_STR);
		$command->bindValue(":dataPedido", date("Y-m-d H:i:s"), PDO::PARAM_STR);
		$command->bindValue(":amount", $_GET['amount'], PDO::PARAM_STR);
		$command->bindValue(":dataVencimento", $dataVencimento, PDO::PARAM_STR);
		$command->execute();
		$idBoleto = Yii::app()->db->getLastInsertID();
		$this->redirect(array('Boleto/secondVia','id'=>$idBoleto));

	}
}