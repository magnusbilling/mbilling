<?php

/**
 * Url for http://localhost/MBilling_3/index.php/smsMobileApp/send?user=demo&pass=magnus&number=325064403&text=test_sms .
 */
class SmsMobileAppController extends BaseController
{


	public function init()
	{
		$config = LoadConfig::getConfig();
		parent::init();
	}

	public function actionSend()
	{
		$UNIX_TIMESTAMP = "UNIX_TIMESTAMP(";

		
		if (isset($_GET['text']))
			$text = $_GET['text'];
		else
			exit;

		if (isset($_GET['user']))
			$user = $_GET['user'];
		else
			exit;

		if (isset($_GET['pass']))
			$pass = $_GET['pass'];
		else
			exit;

		if (isset($_GET['number']))
			$number = $_GET['number'];
		else
			exit;

		$sql = "
			CREATE TABLE IF NOT EXISTS pkg_sms_android_app (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  `number` varchar(50) NOT NULL DEFAULT '',
			  `result` varchar(50) NOT NULL DEFAULT '',
			  `text` text NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `number` (`number`),
			  KEY `id` (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
			";
		try {
	       	Yii::app()->db->createCommand($sql)->execute();
       	} catch (Exception $e) {
       		//
       	}


		$sql = "SELECT * FROM pkg_user WHERE username = :user AND password = :pass";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":user", $user, PDO::PARAM_STR);
		$command->bindValue(":pass", $pass, PDO::PARAM_STR);
		$resultUser = $command->queryAll();

		if (count($resultUser) == 0) {
			exit;
		}

		$sql = "INSERT INTO pkg_sms_android_app (number,result,text) VALUES (:number,0,:text)";
    		try {
    			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":number", $number, PDO::PARAM_STR);
			$command->bindValue(":text", $text, PDO::PARAM_STR);
			$command->execute();

	       	echo 'sucess';
       	} catch (Exception $e) {
       		//
       	}
	}

	public function actionGetNumber()
	{
		$sql = "SELECT id,number,text FROM pkg_sms_android_app  WHERE result = 0 
					ORDER BY RAND( ) LIMIT :query";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":query", $_SERVER['QUERY_STRING'], PDO::PARAM_STR);
		$result = $command->queryAll();

		$numeros = array();
		$ids = array();
		if (count($result) > 0) {
			foreach ($result as $key => $value) {
				$ids[]=$value['id'];
				$numeros[]= $value['number'];
			}
		}else{
			exit;
		}

		$text = $result[0]['text'];

		
		$sql = "DELETE FROM pkg_sms_android_app WHERE id IN (".implode(",", $ids).")";
		Yii::app()->db->createCommand($sql)->execute();

		$numeros = implode(":", $numeros);


		$arrayName = $text.":".$numeros;
		
	
		echo $arrayName;
		exit; 
	}

	public function actionReceiveNumber()
	{
		
		$sms = explode(":", $_GET['sms']);
		$number = $sms[1];
		$text = $sms[2];

		$sql = "SELECT * FROM pkg_cdr WHERE calledstation LIKE :number AND sipiax = 6 ORDER BY id DESC LIMIT 1";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(':number', "%".$number."%", PDO::PARAM_STR);
		$resultSMS = $command->queryAll();

		if(count($resultSMS) > 0){
			$sql = "INSERT INTO pkg_sms (id_user, prefix, date, telephone, sms, result, rate ) 
			VALUES (:id_user,:prefix,'".date('Y-m-d H:i:s')."',:number,:text,2,0)";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $resultSMS[0]['id_user'], PDO::PARAM_STR);
			$command->bindValue(":prefix", $resultSMS[0]['prefix'], PDO::PARAM_STR);
			$command->bindValue(":number", $number, PDO::PARAM_STR);
			$command->bindValue(":text", preg_replace("/\'/", "", $text), PDO::PARAM_STR);
			$command->execute();
		}
		
	}
}