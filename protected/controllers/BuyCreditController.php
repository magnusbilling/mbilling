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

	public function actionMethod($id_user = 0){

		$config = LoadConfig::getConfig();
		$_SESSION['currency'] = $config['global']['base_currency'];
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
		if ($id_user > 0) {
			$modelUser = User::model()->findByPk((int) $id_user);
			$id_user = $modelUser->id;
			$id_agent = $modelUser->id_user;
			$_SESSION['username'] = $modelUser->username;
			$_SESSION['id_user'] = $id_user;

		}else{
			$id_user = Yii::app()->getSession()->get('id_user');
			$id_agent = Yii::app()->getSession()->get('id_agent');
		}

		$methodPay= BuyCredit::model()->findByPK($_GET['id_method']);
		
		if ($methodPay->active == 0 || $methodPay->id_user != $id_agent)
			exit('invalid option');


		if ($methodPay->payment_method == 'BoletoBancario') {
			$this->actionBoletoBancario();
		}
		else if ($methodPay->payment_method == 'SuperLogica') {
			SLUserSave::criarBoleto($methodPay,$modelUser);
		}
		else{
			$this->render(strtolower($methodPay->payment_method),array(
			'methodPay' => $methodPay,
			'card'      => $modelUser
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


	public function actionPayServiceLink()
	{
		$model = new ServicesUse();
		$criteria=new CDbCriteria();	
		if (isset($_GET['id_service_use'])){
			$ids = json_decode($_GET['id_service_use']);
			$criteria->addCondition('status = 2');
		}
		
		else if (isset($_GET['id_user'])) {
			$criteriaUser=new CDbCriteria();
			$id_user = (int) $_GET['id_user'];		
			$criteriaUser->addCondition('id_user = :id_user');
			//$criteriaUser->addInCondition('reminded', array(2,3));
			$criteriaUser->params [':id_user']= $id_user;
			$modelServicesUse = ServicesUse::model()->findAll($criteriaUser);
			$ids = array();
			foreach ($modelServicesUse as $key => $value)
				$ids[]=$value->id;

			$criteria->addCondition('status = 1');
			//$criteria->addCondition('reminded = 2 OR reminded= 3');
		}					
		
		$criteria->addInCondition('id', $ids);		
		
		$modelServicesUse = ServicesUse::model()->findAll($criteria);

	
		if (Yii::app()->session['isAdmin']) {
			$total = 0;
			foreach ($modelServicesUse as $key => $value) {
				$total += $modelServicesUse[0]->idServices->price;
				if($value->id_user != $modelServicesUse[0]->id_user){
					$this->render('payservicelink',array(
						'model' => $model,
						'message' =>'Your cannot process service payment of diferent users.'
					));
					exit;
				}
			}
			if (!count($modelServicesUse)) {
				$this->render('payservicelink',array(
					'model' => $model,
					'message' =>'This service was paid or canceled.'
				));
				exit;
			}
			else if ($modelServicesUse[0]->idUser->credit >= $total) {					
				ServicesProcess::activation(array(
					'id_services' => $ids,
					'id_user' => (int) $modelServicesUse[0]->id_user,
					'id_method' => NULL
					));
				$this->render('payservicelink',array(
					'model' => $model,
					'message' => 'Your services are actived!'
				));

				return;
			}else{
				$this->render('payservicelink',array(
					'model' => $model,
					'message' =>'User not have enogth credit to pay the services.'
				));
				exit;
			}


		}
		
		if (!count($modelServicesUse)) {
			$this->render('payservicelink',array(
						'model' => $model,
						'message' =>'Your selection not have any service pending.'
					));
			exit;
		}

		if ($_POST) {

	
			$total = explode(" ",$_POST['ServicesUse']['total']);
			
			$total = preg_replace("/,/", '',$total[1]);


			if (isset($_POST['ServicesUse']['use_credit']) && $_POST['ServicesUse']['use_credit'] == 1) {					
				
				if ($modelServicesUse[0]->idUser->credit >= $total) {					
					ServicesProcess::activation(array(
						'id_services' => $ids,
						'id_user' => (int) Yii::app()->session['id_user'],
						'id_method' => (int) $_POST['ServicesUse']['id_method']
						));
					$this->render('payservicelink',array(
						'model' => $model,
						'message' => 'Your services are actived!'
					));

					return;
				}else{
					echo $total;
					$total -= $modelServicesUse[0]->idUser->credit;
					echo $total;
					exit;
				}
			}
			if ($_POST['ServicesUse']['id_method'] < 1){
				$model->addError( 'id_method', Yii::t( 'yii', 'Group no allow for Agent users' ) );

			}else{

				if (isset($_GET['id_service_use']))
				{
					$link = $_SERVER['HTTP_REFERER']."index.php/buyCredit/payServiceLink/?id_service_use=".$_GET['id_service_use'];
					$mail = new Mail(Mail::$TYPE_SERVICES_PENDING, $modelServicesUse[0]->id_user);
					$serviceNames='';
					foreach ($modelServicesUse as $key => $value)
						$serviceNames .= $value->idServices->name.', ';
					
					$mail->replaceInEmail(Mail::$SERVICE_NAME, $serviceNames);
					$mail->replaceInEmail(Mail::$SERVICE_PRICE, $total);
					$mail->replaceInEmail(Mail::$SERVICE_PENDING_URL, $link);  
					try {
						@$mail->send();
					} catch (Exception $e) {
						//error SMTP
					}
				}

				$modelMethodPay= Methodpay::model()->findByPk((int) $_POST['ServicesUse']['id_method']);
				$total = $modelMethodPay->payment_method == 'Pagseguro' ? intval($total) : $total;
				

				$this->redirect(array(
					'buyCredit/method',
					'amount'=> $total,
					'id_method'=>(int)$_POST['ServicesUse']['id_method'],
					'id_user' => $modelServicesUse[0]->id_user
					)
				);
			
			}
		}

		$modelMethodPay= Methodpay::model()->findAll('id_user = :key AND active = 1',array(':key' => $modelServicesUse[0]->idUser->id_user));


		$this->render('payservicelink',array(
			'model' => $model,
			'modelMethodPay' => $modelMethodPay,
			'modelServicesUse'      => $modelServicesUse,
			'currency' =>Yii::app()->session['currency']
		));
	}
}