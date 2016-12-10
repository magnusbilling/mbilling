<?php
/**
 * Url for moip ruturn http://ip/billing/index.php/gerencianet .
 */
require_once("/var/www/html/mbilling/lib/gerencianet/vendor/autoload.php");

use Gerencianet\Exception\GerencianetException;
use Gerencianet\Gerencianet;

class GerencianetController extends BaseController
{
	public function actionIndex()
	{
		Yii::log(print_r($_REQUEST,true),'error');
		if (isset($_GET['id_user']) && isset($_GET['id']) && isset($_GET['amount']) && isset($_POST['notification'])) {

			$sql      = "SELECT * FROM pkg_method_pay WHERE payment_method = 'GerenciaNet'";
			Yii::log(print_r($sql,true),'error');
			$resultMethod   = Yii::app()->db->createCommand($sql)->queryAll();
			
			$clientId = $resultMethod[0]['client_id']; // insira seu Client_Id, conforme o ambiente (Des ou Prod)
			$clientSecret = $resultMethod[0]['client_secret']; // insira seu Client_Secret, conforme o ambiente (Des ou Prod)
			
			$options = [
			  'client_id' => $clientId,
			  'client_secret' => $clientSecret,
			  'sandbox' => false // altere conforme o ambiente (true = desenvolvimento e false = producao)
			];

			$sql = "SELECT * FROM pkg_refill WHERE description LIKE :description";
			Yii::log(print_r($sql,true),'error');
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(':description', "%".$_POST['notification']."%", PDO::PARAM_STR);
			$resultRefill = $command->queryAll();


			if (count($resultRefill) < 1) {
				$sql = "INSERT INTO pkg_refill (id_user,credit,description,payment) VALUES 
							(:id_user, :amount, :description , '0')";
				Yii::log(print_r($sql,true),'error');
				
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(':id_user', $_GET['id_user'], PDO::PARAM_INT);
				$command->bindValue(':amount', $_GET['amount'], PDO::PARAM_STR);
				$command->bindValue(':description', 'Boleto gerado, Status:Aguardando ID:'.$_POST['notification'], PDO::PARAM_STR);
				$resultRefill = $command->queryAll();

				$id_refill = Yii::app()->db->lastInsertID;
				sleep(1);
				$sql = "SELECT * FROM pkg_refill WHERE id = :id_refill";
				Yii::log(print_r($sql,true),'error');
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(':id_refill', $id_refill, PDO::PARAM_INT);
				$resultRefill = $command->queryAll();
			}

			if ($resultRefill[0]['payment'] == 1) {
				return;
			}


			$token = $_POST['notification'];

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
			    Yii::log('statusAtual' . $statusAtual,'error');
			    // Com estas informações, você poderá consultar sua base de dados e atualizar o status da transação especifica, uma vez que você possui o "charge_id" e a String do STATUS
			  	switch ($statusAtual) {
			  		case 'paid':
			  			echo "o boleto foi pago";
			  			$description = "Boleto gerado, Status:Pago dia ".date("y-m-d").", ID:".$token;
			  			$sql = "UPDATE pkg_refill SET description= '".$description."', payment =1 WHERE id = :id";
			  			Yii::log(print_r($sql,true),'error');
			  			$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':id', $resultRefill[0]['id'], PDO::PARAM_INT);
						$command->execute();


			  			$sql = "UPDATE pkg_user SET credit= credit + :credit WHERE id = :id";
			  			Yii::log(print_r($sql,true),'error');
			  			$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':id', $resultRefill[0]['id_user'], PDO::PARAM_INT);
						$command->bindValue(':credit', $resultRefill[0]['credit'], PDO::PARAM_STR);
						$command->execute();
			  			Process ::releaseUserCredit($resultRefill[0]['id_user'], $resultRefill[0]['credit'], $description, $token);						
			  			break;
			  		case 'unpaid':
			  			echo "o boleto nao foi pago";
			  			$description = "Boleto gerado, Status:Não foi pago, ID:".$token;
			  			$sql = "UPDATE pkg_refill SET description= :description WHERE id = :id";
			  			Yii::log(print_r($sql,true),'error');
			  			$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':id', $resultRefill[0]['id'], PDO::PARAM_INT);
						$command->bindValue(':description', $description, PDO::PARAM_STR);
						$command->execute();
			  			break;
			  		case 'refunded':
			  			echo "Pagamento devolvido pelo lojista ou pelo intermediador Gerencianet.";
			  			$description = "Boleto gerado, Status:Pagamento devolvido pelo lojista ou pelo intermediador Gerencianet, ID:".$token;
			  			$sql = "UPDATE pkg_refill SET description= :description WHERE id = :id";
			  			Yii::log(print_r($sql,true),'error');
			  			$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':id', $resultRefill[0]['id'], PDO::PARAM_INT);
						$command->bindValue(':description', $description, PDO::PARAM_STR);
						$command->execute();
			  			break;
			  		case 'contested':
			  			echo "Pagamento em processo de contestação.";
			  			$description = "Boleto gerado, Status:Pagamento em processo de contestação, ID:".$token;
			  			$sql = "UPDATE pkg_refill SET description= :description WHERE id = :id";
			  			Yii::log(print_r($sql,true),'error');
			  			$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':id', $resultRefill[0]['id'], PDO::PARAM_INT);
						$command->bindValue(':description', $description, PDO::PARAM_STR);
						$command->execute();
			  			break;
			  		case 'canceled':
			  			echo "Cobrança cancelada pelo vendedor ou pelo pagador.";

			  			$description = "Boleto gerado, Status:Cobrança cancelada pelo vendedor ou pelo pagador, ID:".$token;
			  			$sql = "UPDATE pkg_refill SET description= '".$description."' WHERE id =".$resultRefill[0]['id'];
			  			Yii::log(print_r($sql,true),'error');
			  			$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':id', $resultRefill[0]['id'], PDO::PARAM_INT);
						$command->bindValue(':description', $description, PDO::PARAM_STR);
						$command->execute();
			  			break;
			  		case 'waiting':
			  			Yii::log("Cobrança Aguardando pagamento",'error'); echo "Cobrança Aguardando pagamento";
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