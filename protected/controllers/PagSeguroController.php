<?php

/**
 * Url for moip ruturn http://ip/billing/index.php/pagSeguro .
 * https://pagseguro.uol.com.br/preferences/automaticReturn.jhtml
 */
class PagSeguroController extends BaseController
{	
	public function actionIndex()
	{

		$sql = "SELECT pkg_method_pay.id_user , pagseguro_TOKEN FROM pkg_user INNER JOIN pkg_method_pay 
					ON pkg_method_pay.id_user = pkg_user.id WHERE payment_method = 'Pagseguro'";

		if (isset($_GET['agent'])){
			$sql .= " AND username = :username";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":username", addslashes(strip_tags(trim($_GET['agent']))), PDO::PARAM_STR);
		}			
		else{
			$sql .= " AND pkg_user.id = :id";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", "1", PDO::PARAM_STR);
		}		
		$resultUser = $command->queryAll();


		$idUser     = $resultUser[0]['id_user'];
		$TOKEN      = $resultUser[0]['pagseguro_TOKEN'];
		define('TOKEN', $TOKEN);

		if (count($_POST) > 0)
		{
			// POST recebido, indica que é a requisição do NPI.
			$npi = new PagSeguroNpi();
			$result = $npi->notificationPost();

			$transacaoID = isset($_POST['TransacaoID']) ? $_POST['TransacaoID'] : '';

			if ($result == "VERIFICADO")
			{
				$StatusTransacao = $_POST['StatusTransacao'];
				$monto = str_replace(",", ".", $_POST['ProdValor_1']);
				$usuario = explode("-", $_POST['Referencia']);
    				$usuario = addslashes(strip_tags(trim($usuario[1])));
    				$description = "Pagamento confirmado, PAGSEGURO:" . $transacaoID;

    			if ($StatusTransacao == 'Aprovado')
    			{
    				$sql = "SELECT * FROM pkg_user WHERE username = :usuario";
    				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":usuario", $usuario, PDO::PARAM_STR);
				$resultUser = $command->queryAll();
				if (count($resultUser) > 0 )
				{
					Process ::releaseUserCredit($resultUser[0]['id'], $monto, $description, $transacaoID);
				}
    			}
			}  else {
				echo 'error';
			}
		}
		else
		{
		    echo '<h3>Obrigado por efetuar a compra.</h3>';
		}
	}
}



class PagSeguroNpi {
	private $timeout = 20; // Timeout em segundos
	public function notificationPost() {
		$postdata = 'Comando=validar&Token='.TOKEN;
		foreach ($_POST as $key => $value) {
			$valued    = $this->clearStr($value);
			$postdata .= "&$key=$valued";
		}
		return $this->verify($postdata);
	}
	private function clearStr($str) {
		if (!get_magic_quotes_gpc()) {
			$str = addslashes($str);
		}
		return $str;
	}

	private function verify($data) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://pagseguro.uol.com.br/pagseguro-ws/checkout/NPI.jhtml");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$result = trim(curl_exec($curl));
		curl_close($curl);
		return $result;
	}

}