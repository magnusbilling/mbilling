<?php
/**
 * Acoes do modulo "Call".
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
 * 19/09/2012
 */

//http://localhost/mbilling/index.php/callApp?number=5511999464731&user=24315&name=magnus&city=torres

//http://localhost/mbilling/index.php/callApp/getReturn?number=5511999464731&user=24315&name=magnus&city=torres

//http://localhost/mbilling/index.php/callApp/getReturn?id=269196
class CallAppController extends BaseController
{

	var $user;
	var $name;
	var $city;
	var $destination;

	public function actionIndex()
	{

		if(!isset($_GET['number'])){

			echo 'error, numer is necessary';
			
		}
		else{

			$this->destination = isset($_GET['number']) ? $_GET['number'] : '';
			$this->user = isset($_GET['user']) ? $_GET['user'] : '';
			$this->name = isset($_GET['name']) ? $_GET['name'] : '';
			$this->city = isset($_GET['city']) ? $_GET['city'] : '';

			$sql = "SELECT  * FROM pkg_user  WHERE username = :username ";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":username", $this->user, PDO::PARAM_STR);
			$resultUser = $command->queryAll();

			if(!isset($resultUser[0]['id']) )
			{
				$error_msg = Yii::t('yii','Error : User no Found!');
				echo $error_msg;
				exit;
			}

			$id_user = $resultUser[0]['id'];

			$sql = "SELECT *  FROM  pkg_campaign WHERE status = 1 AND id_user = :id_user";
	          $command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $id_user, PDO::PARAM_STR);
			$resultCampaign = $command->queryAll();

	          if(isset($resultCampaign[0]['id']) ){
     
	          	$sql = "SELECT *  FROM  pkg_campaign_phonebook WHERE id_campaign = :id_campaign";
		 		$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_campaign", $resultCampaign[0]['id'], PDO::PARAM_STR);
				$resultPhoneBook = $command->queryAll();
	 		}else{
	 			echo "User not have campaign";
	 			exit;
	 		}

	 		if(isset($resultPhoneBook[0]['id_phonebook']) ){
     			$values = ":id_phonebook, :destination, :name, :city, 1 ,1  ";
	          	$sql = "INSERT INTO pkg_phonenumber (id_phonebook, number, name, city, status, try) VALUES ($values)";
		 		$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_phonebook", $resultPhoneBook[0]['id_phonebook'], PDO::PARAM_STR);
				$command->bindValue(":name", $this->name, PDO::PARAM_STR);
				$command->bindValue(":city", $this->city, PDO::PARAM_STR);
				$command->bindValue(":destination", $this->destination, PDO::PARAM_STR);
				$command->execute();
		 		$idNumber = Yii::app()->db->getLastInsertID();
	 		}else{
	 			echo "Campaign not have PhoneBook";
	 			exit;
	 		}

	 		$array =array(
	 			'msg' => 'success',
	 			'id' =>  $idNumber
	 			);	 		

			echo json_encode($array);

		}		
	}

	public function actionGetReturn()
	{
		

		if (!isset($_GET['id'])) {

			if(!isset($_GET['number'])){
				echo 'error, numer is necessary';
				exit;			
			}

			$this->destination = isset($_GET['number']) ? $_GET['number'] : '';
			$this->user = isset($_GET['user']) ? $_GET['user'] : '';
			$this->name = isset($_GET['name']) ? $_GET['name'] : '';
			$this->city = isset($_GET['city']) ? $_GET['city'] : '';


			$sql = "SELECT  * FROM pkg_user  WHERE username = '".$this->user."' ";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":username", $this->user, PDO::PARAM_STR);
			$resultUser = $command->queryAll();


			if(!isset($resultUser[0]['id']) )
			{
				$error_msg = Yii::t('yii','Error : User no Found!');
				echo $error_msg;
				exit;
			}

			$id_user = $resultUser[0]['id'];

			$sql = "SELECT *  FROM  pkg_campaign WHERE status = 1 AND id_user = :id_user";
	          $command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $id_user, PDO::PARAM_STR);
			$resultCampaign = $command->queryAll();
	          if(isset($resultCampaign[0]['id']) ){
     
	          	$sql = "SELECT *  FROM  pkg_campaign_phonebook WHERE id_campaign = :id_campaign";
		 		$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_campaign", $resultCampaign[0]['id'], PDO::PARAM_STR);
				$resultPhoneBook = $command->queryAll();
	 		}else{
	 			echo "User not have campaign";
	 			exit;
	 		}

	 		if(isset($resultPhoneBook[0]['id_phonebook']) ){
     			$sql = "SELECT * FROM pkg_phonenumber WHERE id_phonebook = :id_phonebook AND number = :destination 
     											AND name = :name ORDER BY  id DESC ";
		 		$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_phonebook", $resultPhoneBook[0]['id_phonebook'], PDO::PARAM_STR);
				$command->bindValue(":destination", $this->destination, PDO::PARAM_STR);
				$command->bindValue(":name", $this->name, PDO::PARAM_STR);
				$resultNumber = $command->queryAll();
	 		}else{
	 			echo "Campaign not have PhoneBook";
	 			exit;
	 		}
 		}
 		else{
 			$sql = "SELECT * FROM pkg_phonenumber WHERE id = :id" ;
		 	$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", $_GET['id'], PDO::PARAM_STR);
			$resultNumber = $command->queryAll();
 		}

 		if (isset($resultNumber[0]['status'])) {
 			$status = $resultNumber[0]['status'];
 			$msg = 'success';
 		}else{
 			$status = '';
 			$msg = 'Invalid Number';
 		}

 		$array =array(
 			'msg' => $msg,
 			'status' =>  $status
 			);	 		

		echo json_encode($array);
		
	}
}