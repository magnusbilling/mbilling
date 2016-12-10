<?php

/**
 * Url for paypal ruturn http://http://billing3.cwiz.com.br/mbilling/index.php/superLogica .
 */
class SuperLogicaController extends CController
{


	public function actionIndex()
	{
		defined('YII_DEBUG') or define('YII_DEBUG',true);
		// specify how many levels of call stack should be shown in each log message
		defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);


		Yii::log(print_r($_REQUEST, true), 'error');
		echo json_encode(array("status"=>200));

		$sql = "SELECT * FROM pkg_method_pay WHERE payment_method = 'SuperLogica'";
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		$pppToken = $result[0]['SLAppToken'];
		$accessToken= $result[0]['SLAccessToken'];
		$secret= $result[0]['SLSecret'];
		$validationtoken= $result[0]['SLvalidationtoken'];
	

		if (!isset($_POST['validationtoken']) || $validationtoken != $_POST['validationtoken']) {
			Yii::log('invalid token', 'info');
			exit();
		}


		if(!isset($_POST['data']['id_recebimento_recb'])){
			Yii::log('No POST', 'info');	
			exit();
		}

		if(!isset($_POST['data']['id_sacado_sac'])){
			Yii::log('No exists id sacado', 'info');				
			exit();
		}
		$id_recebimento_recb = $_POST['data']['id_recebimento_recb'];
		$id_sacado_sac = $_POST['data']['id_sacado_sac'];

		$sql = "SELECT * FROM pkg_user WHERE id_sacado_sac = :id_sacado_sac" ;
		Yii::log(print_r($sql,true),'error');
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id_sacado_sac", $id_sacado_sac, PDO::PARAM_INT);
		$resultUser = $command->queryAll();

		$id_user = $resultUser[0]['id'];

		$amount = $_POST['data']['vl_total_recb'];

		if ($_POST['data']['fl_status_recb'] == '1')
		{
			$sql = "SELECT * FROM pkg_boleto WHERE id_user = :id_user AND 
						description LIKE :description AND status = 0";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $id_user, PDO::PARAM_INT);	
			$command->bindValue(':description', "%superlogica%".$id_recebimento_recb."%", PDO::PARAM_STR);	
			$resultBoleto = $command->queryAll();



			if (count($resultBoleto) > 0) {

	  			$sql = "UPDATE pkg_boleto SET status= 1, description = :description  WHERE id = :id";
	  			Yii::log(print_r($sql,true),'error');
	  			$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id", $resultBoleto[0]['id'], PDO::PARAM_INT);
				$command->bindValue(':description', "Boleto Pago ID: ".
							$_POST['data']['st_nossonumero_recb'], PDO::PARAM_STR);	
			
				$command->execute();

	  			Process ::releaseUserCredit($resultBoleto[0]['id_user'], $resultBoleto[0]['payment'], 'Boleto Pago', $resultBoleto[0]['description']);

	  		}		

		}	
	}
}