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
require_once("/var/www/html/mbilling/lib/gerencianet/vendor/autoload.php");

use Gerencianet\Exception\GerencianetException;
use Gerencianet\Gerencianet;

class GerencianetCommand extends CConsoleCommand 
{


	

	public function run($args)
	{	

		define('LOGFILE', 'protected/runtime/Gerencianet.log');
		define('DEBUG', 0);

		if (!defined('PID')) {
		define("PID", "/var/run/magnus/GerencianetPid.php");
		}	

		if (Process :: isActive()) {
			$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " PROCESS IS ACTIVE ") : null;
			die();
		} else {
			Process :: activate();
		}
		$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " START PAYMENT Gerencianet ") : null;

				
		$sql      = "SELECT * FROM pkg_method_pay WHERE payment_method = 'GerenciaNet'";
		$resultMethod   = Yii::app()->db->createCommand($sql)->queryAll();
		
		$clientId = $resultMethod[0]['client_id']; // insira seu Client_Id, conforme o ambiente (Des ou Prod)
		$clientSecret = $resultMethod[0]['client_secret']; // insira seu Client_Secret, conforme o ambiente (Des ou Prod)
		
		$options = [
		  'client_id' => $clientId,
		  'client_secret' => $clientSecret,
		  'sandbox' => false // altere conforme o ambiente (true = desenvolvimento e false = producao)
		];

		$sql      = "SELECT * FROM pkg_refill WHERE description LIKE '%Status:Aguardando ID:%' AND payment = 0";
		$resultRefill   = Yii::app()->db->createCommand($sql)->queryAll();

		foreach ($resultRefill as $key => $refill) {
			
			$token = explode(" ID:", $refill['description']);
			$token = $token[1];

			echo $token."\n";
			$params = [
			  'token' => $token
			];
			 
			try {
			    $api = new Gerencianet($options);
			    $chargeNotification = $api->getNotification($params, []);
			  // Para identificar o status atual da sua transação você deverá contar o número de situações contidas no array, pois a última posição guarda sempre o último status. Veja na um modelo de respostas na seção "Exemplos de respostas" abaixo.
			  
			  // Veja abaixo como acessar o ID e a String referente ao último status da transação.

			    // Conta o tamanho do array data (que armazena o resultado)
			    $i = count($chargeNotification["data"]);
			    // Pega o último Object chargeStatus
			    $ultimoStatus = $chargeNotification["data"][$i-1];
			    // Acessando o array Status
			    $status = $ultimoStatus["status"];
			    // Obtendo o ID da transação    
			    $charge_id = $ultimoStatus["identifiers"]["charge_id"];
			    // Obtendo a String do status atual
			    $statusAtual = $status["current"];
			    
			    // Com estas informações, você poderá consultar sua base de dados e atualizar o status da transação especifica, uma vez que você possui o "charge_id" e a String do STATUS
			  	switch ($statusAtual) {
			  		case 'paid':
			  			echo "o boleto foi pago";
			  			$description = "Boleto gerado, Status:Pago dia ".date("y-m-d").", ID:".$token;
			  			$sql = "UPDATE pkg_refill SET description= '".$description."' WHERE id =".$refill['id'];
			  			Yii::app()->db->createCommand($sql)->execute();
			  			$sql = "UPDATE pkg_user SET credit= credit + '".$refill['credit']."' WHERE id =".$refill['id_user'];
			  			Yii::app()->db->createCommand($sql)->execute();
			  			Process ::releaseUserCredit($refill['id_user'], $refill['credit'], $description, $token);						
			  			break;
			  		case 'unpaid':
			  			echo "o boleto nao foi pago";
			  			$description = "Boleto gerado, Status:Não foi pago, ID:".$token;
			  			$sql = "UPDATE pkg_refill SET description= '".$description."' WHERE id =".$refill['id'];
			  			Yii::app()->db->createCommand($sql)->execute();
			  			break;
			  		case 'refunded':
			  			echo "Pagamento devolvido pelo lojista ou pelo intermediador Gerencianet.";
			  			$description = "Boleto gerado, Status:Pagamento devolvido pelo lojista ou pelo intermediador Gerencianet, ID:".$token;
			  			$sql = "UPDATE pkg_refill SET description= '".$description."' WHERE id =".$refill['id'];
			  			Yii::app()->db->createCommand($sql)->execute();
			  			break;
			  		case 'contested':
			  			echo "Pagamento em processo de contestação.";
			  			$description = "Boleto gerado, Status:Pagamento em processo de contestação, ID:".$token;
			  			$sql = "UPDATE pkg_refill SET description= '".$description."' WHERE id =".$refill['id'];
			  			Yii::app()->db->createCommand($sql)->execute();
			  			break;
			  		case 'canceled':
			  			echo "Cobrança cancelada pelo vendedor ou pelo pagador.";

			  			$description = "Boleto gerado, Status:Cobrança cancelada pelo vendedor ou pelo pagador, ID:".$token;
			  			$sql = "UPDATE pkg_refill SET description= '".$description."' WHERE id =".$refill['id'];
			  			Yii::app()->db->createCommand($sql)->execute();
			  			break;
			  		case 'waiting':
			  			echo "Cobrança Aguardando pagamento";
			  			break;
			  	}
			   
			 
			    //print_r($chargeNotification);
			} catch (GerencianetException $e) {
			    print_r($e->code);
			    print_r($e->error);
			    print_r($e->errorDescription);
			} catch (Exception $e) {
			    print_r($e->getMessage());
			}
		}
	}   
}