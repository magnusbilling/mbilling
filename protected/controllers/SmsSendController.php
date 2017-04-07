<?php

/**
 * Url for Send SMSr http://ip/mbilling/index.php/smsSend/?username=user&password=magnus&number=55dddn&text=sms-text.
 */
class SmsSendController extends BaseController
{

	var $success;
	public $nameRoot = 'rows';
	public $nameCount = 'count';
	public $nameSuccess = 'success';
	public $nameMsg = 'msg';

	public function init()
	{

		parent::init();
		if(!isset($_SESSION['language']))
		{
			$config = LoadConfig::getConfig();
			Yii::app()->language = Yii::app()->sourceLanguage = isset($config['global']['base_language']) ? $config['global']['base_language']  : Yii::app()->language;
		}
	}

	public function actionIndex($username = null, $password = null, $destination = null, $text = null, $id_phonenumber = null,$campaign = false)
	{
		if (!defined('DEBUG'))
		{
			define('DEBUG', 0);
			define('LOGFILE', 'protected/runtime/Sms.log');
		}

		$destination = isset($_GET['number']) ? $_GET['number'] : $destination;
		$text        = isset($_GET['text']) ? $_GET['text'] : $text;
		$username    = isset($_GET['username']) ? $_GET['username'] : $username;
		$password    = isset($_GET['password']) ? $_GET['password'] : $password ;

		if(!$destination || !$text || !$username || !$password)
			exit(Yii::t('yii','Disallowed action'));

		$fields = 'id, username, credit, lastname, firstname, address, city, state, country, zipcode, phone, email, 
					lastuse, active, id_plan, id_user';
		$sql = "SELECT $fields FROM pkg_user WHERE username = :username AND password = :password";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":username", $username, PDO::PARAM_STR);
		$command->bindValue(":password", $password, PDO::PARAM_STR);
		$resultUser = $command->queryAll();


		//CHECK ACCESS
		if(!isset($resultUser[0]['id']) )
		{
			$error_msg = Yii::t('yii','Error : Autentication Error!');
			echo json_encode(array(
				$this->nameSuccess => false,
				'rows' => array(array('id'=>0)),
				'errors' => $error_msg
			));
			exit;
		}

		
        	//VERIFICA SE O CLIENTE TEM CREDITO
		if(CreditUser :: checkGlobalCredit($resultUser[0]['id']) === false)
		{
			$error_msg = Yii::t('yii','Error : You don t have enough credit to call you SMS!');
			echo json_encode(array(
				$this->nameSuccess => false,
				'rows' => array(array('id'=>0)),
				'errors' => $error_msg
			));
			exit;
		}		


		/*protabilidade*/		
		$destination = Portabilidade :: getDestination($destination, true, false,$resultUser[0]['id_plan']);
			

		$destination = "999" . $destination;
		$date_msn = date("Y-m-d") . date(" H:i:s");

		//PEGA O PREÇO DE VENDA DO AGENT
		if($resultUser[0]['id_user'] > 1) {

			$sql = "SELECT * FROM pkg_rate_agent
							LEFT JOIN pkg_prefix ON pkg_rate_agent.id_prefix=pkg_prefix.id
							WHERE prefix = SUBSTRING(:destination,1,length(prefix)) and pkg_rate_agent.id_plan= :id_plan 
							ORDER BY LENGTH(prefix) DESC";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":destination", $destination, PDO::PARAM_STR);
			$command->bindValue(":id_plan", $resultUser[0]['id_plan'], PDO::PARAM_INT);
			$callClientAgent = $command->queryAll();

			if (count($callClientAgent) == 0)
			{
				$error_msg = Yii::t('yii','Prefix not found in Agent'. ' '. $destination);
				echo json_encode(array(
					$this->nameSuccess => false,
					'errors' => $error_msg
				));
				exit;
			}
			$rateInitialClientAgent = $callClientAgent[0]['rateinitial'];
			

			$sql = "SELECT * FROM pkg_user WHERE id = :id";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", $resultUser[0]['id_user'], PDO::PARAM_INT);
			$callPlanAgent = $command->queryAll();

			$resultUser[0]['id_plan'] = $callPlanAgent[0]['id_plan'];
		}else{
			$rateInitialClientAgent = 0;
		}
		

		$sql = "SELECT pkg_rate.id AS idRate, rateinitial, buyrate, pkg_prefix.id AS id_prefix, pkg_rate.id_trunk, 
					rt_trunk.trunkcode, rt_trunk.trunkprefix, rt_trunk.removeprefix, rt_trunk.providertech, rt_trunk.inuse, 
					rt_trunk.maxuse, rt_trunk.status, rt_trunk.failover_trunk, rt_trunk.link_sms, rt_trunk.sms_res 
					FROM pkg_rate
					LEFT JOIN pkg_plan ON pkg_rate.id_plan=pkg_plan.id
					LEFT JOIN pkg_trunk AS rt_trunk ON pkg_rate.id_trunk=rt_trunk.id
					LEFT JOIN pkg_prefix ON pkg_rate.id_prefix=pkg_prefix.id
					WHERE prefix = SUBSTRING(:destination,1,length(prefix)) and pkg_plan.id= :id_plan 
					ORDER BY LENGTH(prefix) DESC";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":destination", $destination, PDO::PARAM_STR);
		$command->bindValue(":id_plan", $resultUser[0]['id_plan'], PDO::PARAM_INT);
		$callTrunk = $command->queryAll();



		if (count($callTrunk) == 0)
		{
			$error_msg = Yii::t('yii','Prefix not found'. ' '. $destination);
			echo json_encode(array(
				$this->nameSuccess => false,
				'errors' => $error_msg
			));
			exit;
		}
		else
		{
			//RETIRO O 9991111
			if(substr($destination, 0, 7) == '9991111')
				$destination = substr($destination, 10);

			else if(substr($destination, 0, 3) == '999')
				$destination = substr($destination, 3);


			if (!isset($callTrunk[0]['link_sms'])) {
				$error_msg = Yii::t('yii','No sms link');
				echo json_encode(array(
					$this->nameSuccess => false,
					'rows' => array(array('id'=>0)),
					'errors' => $error_msg
				));
				exit;
			}

			$linkSms      = isset($callTrunk[0]['link_sms']) ? $callTrunk[0]['link_sms'] : NULL;
			$trunkPrefix  = isset($callTrunk[0]['trunkprefix']) ? $callTrunk[0]['trunkprefix'] : NULL;
			$trunkCode    = isset($callTrunk[0]['trunkcode']) ? $callTrunk[0]['trunkcode'] : NULL;
			$removePrefix = isset($callTrunk[0]['removeprefix']) ? $callTrunk[0]['removeprefix'] : NULL;
			$smsRes       = isset($callTrunk[0]['sms_res']) ? $callTrunk[0]['sms_res'] : NULL;

			$buyRate      = isset($callTrunk[0]['buyrate']) ? $callTrunk[0]['buyrate'] : NULL;
			$rateInitial  = isset($callTrunk[0]['rateinitial']) ? $callTrunk[0]['rateinitial'] : NULL;
			$id_prefix    = isset($callTrunk[0]['id_prefix']) ? $callTrunk[0]['id_prefix'] : NULL;


			//retiro e adiciono os prefixos do tronco
			if(strncmp($destination, $removePrefix, strlen($removePrefix)) == 0)
			    $destination = substr($destination, strlen($removePrefix));
			$destination = $trunkPrefix . $destination;

			//Adiciona barras invertidas a uma string
			$text = addslashes((string )$text);
			//CODIFICA O TESTO DO SMS
			$text = urlencode($text);

			$linkSms = preg_replace("/\%number\%/", $destination, $linkSms);
			$linkSms = preg_replace("/\%text\%/", $text, $linkSms);
			$linkSms = preg_replace("/\%id\%/", $id_phonenumber, $linkSms);


			
			if (strlen($linkSms) < 10) {
				$error_msg = Yii::t('yii','Your SMS is not send!') . ' ' .Yii::t('yii','Not have link in trunk');
				echo json_encode(array(
					$this->nameSuccess => false,
					'errors' => $error_msg
				));
				exit;
			}
			if(!$res = @file_get_contents($linkSms,false)) {
				echo json_encode(array(
					$this->nameSuccess => false,
					'rows' => array(array('id'=>0)),
					'errors' => "ERROR, contact us"
					
				));
				exit;
			}
           	
            	//DESCODIFICA O TESTO DO SMS PARA GRAVAR NO BANCO DE DADOS
            	$text = urldecode($text);


           	$sussess = !$smsRes == '' && !preg_match("/$smsRes/",  $res) ? false: true;
           	
           	if ($sussess)
           	{
				$terminateCauseid = 1;
				$sessionTime      = 60;
				$rateInitial      = strlen($text) > 160 ? $rateInitial * 2 : $rateInitial;
				$error_msg        = Yii::t('yii','Send');
				$this->success          = true;

				$sql = "INSERT INTO pkg_sms (id_user , prefix , telephone , sms , result , rate)
					VALUES (:id_user, :id_prefix, :destination, :text, :sussess, :rateInitial)";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_prefix", $id_prefix, PDO::PARAM_INT);
				$command->bindValue(":id_user", $resultUser[0]['id'], PDO::PARAM_INT);
				$command->bindValue(":destination", $destination, PDO::PARAM_STR);
				$command->bindValue(":text", $text, PDO::PARAM_STR);
				$command->bindValue(":rateInitial", $rateInitial, PDO::PARAM_STR);
				$command->bindValue(":sussess", $sussess, PDO::PARAM_STR);
				$command->execute();

				//RETIRA CREDITO DO CLIENTE
				if($resultUser[0]['id_user'] > 1){
					$sql = "UPDATE pkg_user set credit=credit- :rateInitialClientAgent WHERE id= :id" ;
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":rateInitialClientAgent", $rateInitialClientAgent, PDO::PARAM_STR);
					$command->bindValue(":id", $resultUser[0]['id'], PDO::PARAM_INT);
				}
				else{
					$sql = "UPDATE pkg_user set credit=credit- :rateInitial WHERE id= :id" ;
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":rateInitial", $rateInitial, PDO::PARAM_STR);
					$command->bindValue(":id", $resultUser[0]['id'], PDO::PARAM_INT);
				}		
				
				$command->execute();

				if($id_phonenumber > 0)
				{
					$sql = "UPDATE pkg_phonenumber SET try = try + 1 , status = 3 WHERE id = :id_phonenumber";
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":id_phonenumber", $id_phonenumber, PDO::PARAM_INT);
					$command->execute();
				}


				//RETIRA CREDITO DO REVENDEDOR
				if ($resultUser[0]['id_user'] > 1)
				{
					$sql = "UPDATE pkg_user set credit=credit- :buyRate WHERE id= :id_user";
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":id_user", $resultUser[0]['id_user'], PDO::PARAM_INT);
					$command->bindValue(":buyRate", $buyRate, PDO::PARAM_STR);
					$command->execute();
				}
           	}
           	else
           	{
				$buyRate          = 0;
				$terminateCauseid = 4;
				$rateInitial      = 0;
				$sessionTime      = 0;
				$error_msg        = Yii::t('yii','Your SMS is not send!');
				$this->success          = false;
           	}

           	$sessionid = "SMS/$destination-".date('His');
           	$uniqueid  = "$destination-".date('His');

           	$sql = " INSERT INTO pkg_cdr (sessionid , uniqueid , id_user , starttime , stoptime , sessiontime, 
           					calledstation, sessionbill, id_plan, id_trunk, src, buycost,  terminatecauseid, 
           					id_prefix, sipiax, agent_bill) VALUES (:sessionid, :uniqueid, :id_user, :date_msn,
           					:date_msn, :sessionTime, :destination, :rateInitial, :id_plan, :id_trunk , 
           					:username , :buyRate , :terminateCauseid , :id_prefix, '6', :rateInitialClientAgent)";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $resultUser[0]['id'], PDO::PARAM_INT);
			$command->bindValue(":sessionid", $sessionid, PDO::PARAM_STR);
			$command->bindValue(":uniqueid", $uniqueid, PDO::PARAM_STR);
			$command->bindValue(":date_msn", $date_msn, PDO::PARAM_STR);
			$command->bindValue(":sessionTime", $sessionTime, PDO::PARAM_STR);
			$command->bindValue(":destination", $destination, PDO::PARAM_STR);
			$command->bindValue(":rateInitial", $rateInitial, PDO::PARAM_STR);
			$command->bindValue(":id_plan", $resultUser[0]['id_plan'], PDO::PARAM_INT);
			$command->bindValue(":id_trunk", $callTrunk[0]['id_trunk'], PDO::PARAM_INT);
			$command->bindValue(":buyRate", $buyRate, PDO::PARAM_STR);
			$command->bindValue(":username", $username, PDO::PARAM_STR);
			$command->bindValue(":terminateCauseid", $terminateCauseid, PDO::PARAM_INT);
			$command->bindValue(":id_prefix", $id_prefix, PDO::PARAM_INT);
			$command->bindValue(":rateInitialClientAgent", $rateInitialClientAgent, PDO::PARAM_STR);
			$command->execute();

			echo json_encode(array(
				$this->nameSuccess => $this->success,
				'rows' => array(array('id'=>0)),
				'errors' => $error_msg
				
			));

		}
	}
}
