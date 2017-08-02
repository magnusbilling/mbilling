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

class ServicesProcess {

	public static function activation($values)
	{
		$success = false;
		$msg = 'Error';
		foreach ($values['id_services'] as $key => $id_service) {

			 
			$modelServicesUse = ServicesUse::model()->find( 'id = :key AND status = 2',
				array(
					':key' => (int) $id_service
					));

			if (!count($modelServicesUse))
				continue;
			
			ServicesProcess::updateUser('activation',$modelServicesUse);
	
			$modelServicesUse->reservationdate =  date('Y-m-d H:i:s');
			$modelServicesUse->status = 1;
			$modelServicesUse->reminded = 0;
			$modelServicesUse->month_payed = 1;
			$modelServicesUse->id_method =$values['id_method'];
			$modelServicesUse->save();


			$success = true;
			$msg = 'Service was activated';

			if(isset($userBuyingNewService)){


				$result['paid'] = false;
				$result['rows'] = array(array('id' =>$modelServicesUse->id));
				
			}
		}
		return json_encode(array(
				'success' => $success,
				'msg' => $msg
			));

	}

	public static function release($id_services)
	{
		$modelServicesUse = ServicesUse::model()->findByPk((int) $id_services);
		if ($modelServicesUse->status == 1) {
			$priceToreturn = ServicesProcess::checkStatus($modelServicesUse);


			if ($priceToreturn > 0) {
				//expired
				//have days yet.
				$modelUser = User::model()->findByPk((int) $modelServicesUse->id_user);
				$modelServicesUse->idServices->price = $priceToreturn;
				ServicesProcess::updateUser('release',$modelServicesUse);
			}

			$modelServicesUse->releasedate = date('Y-m-d H:i:s');
			$modelServicesUse->status = 0;
			$modelServicesUse->save();
		}elseif ($modelServicesUse->status == 2 ) {
			ServicesUse::model()->deleteByPk((int) $id_services);
		}
		echo json_encode(array(
			'success' => true,
			'msg' => 'Service was canceled'
		));
	}

	public static function buyService($values)
	{
		if ($values['isClient']) 
		{
			$modelUser = User::model()->findByPK((int) $values['id_user']);
			$id_agent = is_null($modelUser->id_user) ? 1 : $modelUser->id_user;
		}
		
		$modelServices = Services::model()->findByPk((int) $values['id_services']);

		return array(
			'amount'       => $modelServices->price
			);
	}


	public static function checkStatus($modelServicesUse)
	{
		$data = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s", strtotime($modelServicesUse->reservationdate)) . " +$modelServicesUse->month_payed month"));
		

		if ($data > date("Y-m-d")) {

			$month_payed = $modelServicesUse->month_payed - 1;
			//data do ultimo vencimento
			$data = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s", strtotime($modelServicesUse->reservationdate)) . " +$month_payed month"));


			$secondsUsedThisMonth =time() - strtotime($data);

			$secondsInMonth = 60 * 60 * 24 * date('t') ;
			$pricePerSecond = $modelServicesUse->idServices->price / $secondsInMonth; 


			return $modelServicesUse->idServices->price - ($pricePerSecond * $secondsUsedThisMonth);

		}elseif($data == date("Y-m-d")){
			return 0;
			//echo 'vence Hoje';
		}elseif($data < date("Y-m-d")){
			//echo 'Expired';
			return -1;
		}
	}

	public static function updateUser($method,$modelServicesUse,$updateUserCredit = true)
	{
		$signal = $method == 'activation' ? '+' : '-';

		switch ($modelServicesUse->idServices->type) {


			case 'disk_space':
				if($modelServicesUse->idUser->disk_space < 0 && $method == 'activation'){
					$modelServicesUse->idServices->disk_space++;
				}
				//desativa gravacoes se o usuario ficar com espaço em disco menor que 1
				if($method != 'activation' && 
					($modelServicesUse->idUser->disk_space - $modelServicesUse->idServices->disk_space < 1))
				{
					$sql = "UPDATE pkg_sip SET record_call = 0 WHERE id_user = :id_user";
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":id_user", $modelServicesUse->id_user, PDO::PARAM_INT);
					$command->execute();
				}
				$updateService = 'disk_space = disk_space '. $signal.' ' .$modelServicesUse->idServices->disk_space;
				break;
			case 'sipAccountLimit':
				if($modelServicesUse->idUser->sipaccountlimit < 0 && $method == 'activation'){
					$modelServicesUse->idServices->sipaccountlimit++;
				}
				//deleta as contas voip que superam o limite do servico comprado.
				if($method != 'activation' )
				{

					$modelSip = Sip::model()->findAll('id_user = :id_user', array(':id_user'=>$modelServicesUse->id_user));
					$totalSipAccounts = count($modelSip);
					$newLimit = $modelServicesUse->idUser->sipaccountlimit - $modelServicesUse->idServices->sipaccountlimit;
					$limitToDelete = $totalSipAccounts - $newLimit - 1;

  					if($limitToDelete > 0){						
						$sql = "DELETE FROM pkg_sip WHERE id_user = :id_user ORDER BY id DESC LIMIT :limits";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id_user", $modelServicesUse->id_user, PDO::PARAM_INT);
						$command->bindValue(":limits", $limitToDelete, PDO::PARAM_INT);
						$command->execute();
						
					}
					
					try {
						include_once("/var/www/html/mbilling/protected/commands/AGI.Class.php");
						$asmanager = new AGI_AsteriskManager;
						$conectaServidor = $conectaServidor = $asmanager->connect( 'localhost', 'magnus', 'magnussolution' );
						$server = $asmanager->Command( "sip reload" );	
					} catch (Exception $e) {
						
					}
					
				}
				$updateService = 'sipaccountlimit = sipaccountlimit ' . $signal.' ' . $modelServicesUse->idServices->sipaccountlimit;
				break;
			case 'calllimit':
			if($modelServicesUse->idUser->calllimit < 0 && $method == 'activation'){
					$modelServicesUse->idServices->calllimit++;
				}
				$updateService = 'calllimit = calllimit  '. $signal.' '.$modelServicesUse->idServices->calllimit;
				break;
		}
		if (isset($updateService)) {		
			$sql ="UPDATE pkg_user SET $updateService WHERE id = :id";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", $modelServicesUse->id_user, PDO::PARAM_INT);
			$command->execute();
		}

		$signal = $method == 'activation' ? '-' : '+';
		$credit = $signal.$modelServicesUse->idServices->price;
		$description = Yii::t('yii',$method) . ' '.  Yii::t('yii','Service') . ' '. $modelServicesUse->idServices->name;

		$sql = "INSERT INTO pkg_refill (id_user,credit,description,payment) VALUES (:key,:key1,:key2,:key3)";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":key", $modelServicesUse->id_user, PDO::PARAM_INT);
		$command->bindValue(":key1", $credit, PDO::PARAM_STR);
		$command->bindValue(":key2", $description, PDO::PARAM_STR);
		$command->bindValue(":key3", 1, PDO::PARAM_INT);
		$command->execute();	
		
		if ($updateUserCredit == true) {
			//add or remove user credit
			$resultCard = User::model()->findByPk($modelServicesUse->id_user);
			$creditOld = $resultCard->credit;
			$resultCard->credit = $credit > 0 ? $resultCard->credit + $credit : $resultCard->credit - ($credit * -1);
			$resultCard->saveAttributes(array('credit'=>$resultCard->credit));
		}

		if ($method == 'activation' )
			$mail = new Mail(Mail::$TYPE_SERVICES_ACTIVATION, $modelServicesUse->id_user);
		else
			$mail = new Mail(Mail::$TYPE_SERVICES_RELEASED, $modelServicesUse->id_user);
		

		$mail->replaceInEmail(Mail::$SERVICE_NAME, $modelServicesUse->idServices->name);
		$mail->replaceInEmail(Mail::$SERVICE_PRICE, $modelServicesUse->idServices->price); 
		try {
			@$mail->send();
		} catch (Exception $e) {
			//error SMTP
		}
		
	}

	public function payService($modelServicesUse)
	{
		$sql = "UPDATE pkg_services_use set month_payed = month_payed + 1, reminded = 0, status = 1 WHERE id = :id";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id", $modelServicesUse->id, PDO::PARAM_INT);
		$command->execute();

		$description = Yii::t('yii','Monthly payment Service') . ' '. $modelServicesUse->idServices->name;
		Process::releaseUserCredit($modelServicesUse->id_user, '-'.$modelServicesUse->idServices->price, $description);

		$mail = new Mail(Mail::$TYPE_SERVICES_PAID, $modelServicesUse->id_user);		
		$mail->replaceInEmail(Mail::$SERVICE_NAME, $modelServicesUse->idServices->name);
		$mail->replaceInEmail(Mail::$SERVICE_PRICE, $modelServicesUse->idServices->price); 
		try {
			//@$mail->send();
		} catch (Exception $e) {
			//error SMTP
		}
	}

	public function releaseService($modelServicesUse)
	{
		$sql = "UPDATE pkg_services_use set status = 0 , reminded = 0, releasedate = '".date('Y-m-d H:i:s')."' WHERE id = :id";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id", $modelServicesUse->id, PDO::PARAM_INT);
		$command->execute();

		ServicesProcess::updateUser('release',$modelServicesUse,false);
	}

	public function checkIfServiceToPayAfterRefill($id_user)
	{		

		$criteria=new CDbCriteria();
		$criteria->addCondition('status = 2');	
		$criteria->addCondition('id_user = :id_user');
		$criteria->params [':id_user']= $id_user;	
		$modelServicesUse = ServicesUse::model()->findAll($criteria);

		foreach ($modelServicesUse as $key => $service){
			$modelUser = User::model()->findByPk((int) $id_user);

			//se o cliente tem credito para pagar o servico, cobrar imediatamente.
			if ($modelUser->credit >= $service->idServices->price) {
				Yii::log('ativar servico '.$service->id,'error');
				
				$sql = "UPDATE pkg_services_use set status = 1 , month_payed = month_payed+1, reminded = 0, reservationdate = '".date('Y-m-d H:i:s')."' WHERE id = :id";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id", $service->id, PDO::PARAM_INT);
				$command->execute();
				
				ServicesProcess::updateUser('activation',$service);
			}
			
		}
		
	}
}
?>