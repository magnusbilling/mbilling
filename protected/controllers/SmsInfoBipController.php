<?php

/**
 * Url for http://localhost/mbilling/index.php/smsInfoBip/send?user=6964554610&pass=6964554610&number=57325064403&text=test_sms .
 */
class SmsInfoBipController extends BaseController
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

		if (isset($_GET['from']))
			$from = $_GET['from'];
		else
			$from = '55555555555';


		$authorization = base64_encode("$user:$pass");


		$result = exec("
		curl -X POST \
 -H 'Content-Type: application/json' \
 -H 'Accept: application/json' \
 -H 'Authorization: Basic $authorization' \
 -d '{  
   \"from\":\"$from\",
   \"to\":\"$number\",
   \"text\":\"$text\"
}' https://api.infobip.com/sms/1/text/single");

		$result = json_decode($result);

		if (isset($result->messages[0]->status->groupName)) {
			echo 'ok';
		}else{
			echo 'error';
		}




		
	}
}