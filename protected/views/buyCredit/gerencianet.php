<?php header ('Content-type: text/html; charset=ISO-8859-1'); ?>
<?php
/**
 * View to modulo "PlacetoPay".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package	MagnusBilling
 * @author	Adilson Leffa Magnus.
 * @copyright	Todos os direitos reservados.
 * ###################################
 * =======================================
 * MagnusSolution.com <info@magnussolution.com>
 * 2016-03-18
 */
?>
<style type="text/css">
#load{
position:absolute;
z-index:1;
border:3px double #999;
background:#f7f7f7;
width:300px;
height:100px;
margin-top:-150px;
margin-left:-150px;
top:50%;
left:50%;
text-align:center;
line-height:100px;
font-family:"Trebuchet MS", verdana, arial,tahoma;
font-size:12pt;
}
</style>

<div id="load" ><?php echo Yii::t('yii','Please wait while loading...') ?></div>

<?php

require_once("lib/gerencianet/vendor/autoload.php");

use Gerencianet\Exception\GerencianetException;
use Gerencianet\Gerencianet;
	
	$card->doc = preg_replace("/-|\.|\//", "", $card->doc);
	if (!isset($card->email) || strlen($card->email) < 10 || !preg_match("/@/", $card->email)) {
		echo "Email invalido, por favor verifique seu email";
		exit;
	}
	elseif (!isset($card->doc) || strlen($card->doc) < 10 ) {
		echo "Voce precisa cadastrar seu CPF/CNPJ";
		exit;
	}
	elseif (!preg_match("/^[1-9]{2}9?[0-9]./", $card->phone)) {

		echo $card->phone."Voce precisa cadastrar seu telefone: FORMATO DDD numero";
		exit;
	}
	$tipo = strlen($card->doc) == 11 ? 'fisica' : 'juridica';

	if ($tipo == 'juridica') {
		if (!isset($card->company_name) || strlen($card->company_name) < 10 ) {
			echo "Voce precisa cadastrar o nome da empresa";
			exit;
		}
	}

		
	if(!isset($_GET['id']))
	{

		$amount = $_GET['amount'].'00';
		
		$clientId = $methodPay->client_id; // insira seu Client_Id, conforme o ambiente (Des ou Prod)
		$clientSecret = $methodPay->client_secret; // insira seu Client_Secret, conforme o ambiente (Des ou Prod)
		
		$options = [
		  'client_id' => $clientId,
		  'client_secret' => $clientSecret,
		  'sandbox' => false // altere conforme o ambiente (true = desenvolvimento e false = producao)
		];

		$item_1 = [
		    'name' => "usuario, ".$_SESSION["username"], // nome do item, produto ou serviço
		    'amount' => 1, // quantidade
		    'value' => intval($amount) // valor (1000 = R$ 10,00)
		];

		$items =  [
		    $item_1
		];
		
		$metadata = array('notification_url'=>'http://'.$_SERVER['HTTP_HOST'].'/mbilling/index.php/gerencianet?id_user='.$card->id.'&id='.time().'&amount='.$_GET['amount']);
		$metadata = array('notification_url'=>'http://argentina.magnusbilling.com:8059/MBilling_5/index.php/gerencianet?id_user='.$card->id.'&id='.time().'&amount='.$_GET['amount']);
		

		$body  =  [
		    'items' => $items,
		    'metadata' => $metadata
		];

		try {
		    $api = new Gerencianet($options);
		    $charge = $api->createCharge([], $body);
		    print_r($charge);
		} catch (GerencianetException $e) {
		    print_r($e->code);
		    print_r($e->error);
		    print_r($e->errorDescription);
		} catch (Exception $e) {
		    print_r($e->getMessage());
		}

		if (isset($charge['data']['charge_id'])) {
			//echo "Processando Pagamento ID: ". $charge['data']['charge_id']." .....<br>";
		}
		else{
			exit;
		}

		sleep(1);
	}else{

		$charge['data']['charge_id'] = $_GET['id'];
	}


	$params = [
	  'id' => $charge['data']['charge_id']
	];

	$firstname = isset($card->firstname) ? $card->firstname : '';
	$lastname  = isset($card->lastname) ? $card->lastname : '';
	$address   = isset($card->address) ? $card->address : '';

	$city      = isset($card->city) ? $card->city : '';
	$state     = isset($card->state) ? $card->state : '';
	$zipcode   = isset($card->zipcode) ? $card->zipcode : '';
	$phone     = isset($card->phone) ? $card->phone : '';
	$email     = isset($card->email) ? $card->email : '';
	$cpf     = isset($card->doc) ? $card->doc : '';

	

	$customer = [
	  'name' => $firstname . ' '.$lastname, // nome do cliente
	  'cpf' => $cpf , // cpf válido do cliente
	  'phone_number' => $phone, // telefone do cliente
	  'email' => $email
	];

	if($tipo == 'juridica'){
		unset($customer['cpf']);
		$customer ['juridical_person']=  array(
			 'corporate_name' => $card->company_name,
			 'cnpj' => $cpf,
			);
	}


	$dataVencimento =  date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y")));


	$bankingBillet = [
	  'expire_at' => $dataVencimento, // data de vencimento do boleto (formato: YYYY-MM-DD)
	  'customer' => $customer
	];

	$payment = [
	  'banking_billet' => $bankingBillet // forma de pagamento (banking_billet = boleto)
	];


	$body = [
	  'payment' => $payment
	];

	try {
	    $api = new Gerencianet($options);
	    $charge = $api->payCharge($params, $body);

	    if ($charge['code'] == 200) {
	    	header('Location: '. $charge['data']['link']);
	    	}
	    else{
	    	print_r($charge);
	    }
	} catch (GerencianetException $e) {
		echo 'Error';
	    print_r($e->code);
	    print_r($e->error);
	    print_r($e->errorDescription);
	} catch (Exception $e) {
		echo 'Error2';
	    print_r($e->getMessage());
	}


?>
