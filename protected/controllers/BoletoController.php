<?php
/**
 * Acoes do modulo "Boleto".
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

class BoletoController extends Controller
{
	public $attributeOrder        = 't.date DESC';
	public $extraValues           = array('idUser' => 'username');
	public $fieldsFkReport = array(
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		)
	);
	public $nossoNumero = array();

	public function init()
	{
		if (!Yii::app()->session['id_user'])
            exit;
		$this->instanceModel = new Boleto;
		$this->abstractModel = Boleto::model();
		$this->titleReport   = Yii::t('yii','Boleto');
		parent::init();
	}

	public function actionRetorno()
	{

		$values = $this->getAttributesRequest();
		$banco = $values['banco'];


		$uploaddir = "tmp/";
		$uploadfile = $uploaddir .date('ymdhis').$_FILES["file"]["name"];
		move_uploaded_file($_FILES["file"]["tmp_name"], $uploadfile);


		if ($banco == 'cef') {
			require_once("lib/boletophp/retorno/RetornoBanco.php");
			require_once("lib/boletophp/retorno/RetornoFactory.php");

			$fileName = $uploadfile;

			$cnab240 = RetornoFactory::getRetorno($fileName, "linhaProcessada");


			$retorno = new RetornoBanco($cnab240);

			$nossoNumero = $retorno->processar();

			$boletosOk = "-->Boletos processados <-- </br>";
			$boletosNo = "Total de Boletos não processadors -->  ";
			$boletosNoTotal = 0;
		

			foreach ($nossoNumero as $key => $value) {

				$nosso_numero = explode("         ", $value['nosso_numero']);

				$nosso_numero = intval(substr($nosso_numero[1], 2,8));

				$sql = "SELECT id, id_user, status, payment   FROM pkg_boleto WHERE id = :nosso_numero";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":nosso_numero", $nosso_numero, PDO::PARAM_STR);
				$resultBoleto = $command->queryAll();

				if (count($resultBoleto) > 0) {


					if ($resultBoleto[0]['status'] == 0) {

						$amount = $resultBoleto[0]['payment'];
						$description = 'Boleto número , '.$nosso_numero ;

						Process ::releaseUserCredit($resultBoleto[0]['id_user'], $amount, $description, $nosso_numero);


						$sql = "UPDATE  pkg_boleto  SET status = 1 WHERE id = :nosso_numero";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":nosso_numero", $nosso_numero, PDO::PARAM_STR);
						$command->execute();

						$boletosOk .= $nosso_numero ."</br>";
					}else{
						$boletosNoTotal++;
					}							
					
				}
				else{
					$boletosNoTotal++;	


				}
			}

			if (strlen($boletosOk) < 32) {
				$boletosOk .= "Nenhun boleto dado de baixa</br>";
			}

			if (strlen($boletosNo) < 35) {
				$boletosNo .= "";
			}

			echo json_encode(array(
				$this->nameSuccess => true,
				$this->nameMsg => $boletosOk."</br>".$boletosNo . ' '. $boletosNoTotal
			));
			exit;

		}

	
	}

	

	public function actionSecondVia($idBoleto = NULL)
	{
		if ($_GET['id'] == 'last') {
			$sql = "SELECT id FROM pkg_boleto ORDER BY id DESC LIMIT 1";
			$resultID = Yii::app()->db->createCommand($sql)->queryAll();
			$id = $resultID[0]['id'];
		}elseif (isset($idBoleto) && $idBoleto > 0) {
			$id= $idBoleto;
		}
		elseif (isset($_GET['id']) && $_GET['id'] > 0) {
			$id = $_GET['id'];
		}else{
			echo json_encode(array(
				$this->nameSuccess => false,
				$this->nameMsg => 'Por favor selecionar um boleto, este boleto não é válido'
			));
			exit;
		}

		$sql = "SELECT * FROM pkg_boleto WHERE id = :id";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id", $id, PDO::PARAM_STR);
		$result = $command->queryAll();

		if (preg_match("/superlogica/", $result[0]['description'])) {
			$link = explode("VIA: ", $result[0]['description']);
			if(isset($link[1]))
				header('Location: '.$link[1]);
		}
		

		if (count($result) == 0) {
			$error = true;
		}
		$vencimiento = date('d/m/Y', strtotime($result[0]['vencimento']));
		$data_documento = date('d/m/Y', strtotime($result[0]['date'])); 


		if (isset($error)) {
			echo "<h3>Boleto inexistente</h3>";
			exit;
		}

		if ($result[0]['status'] == 1) {
			echo "<div class='cont'>";
			echo "<img width=320 height=320 src='../../../resources/images/Pago.gif'>";
			echo "</div>";

			echo '<style type="text/css">div.cont{top: 300px;left: 180px;height:50px;position:absolute;}</style>';
		}

		$sql = "SELECT * FROM pkg_method_pay WHERE payment_method = :payment_method";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":payment_method", 'BoletoBancario', PDO::PARAM_STR);
		$resultBoleto = $command->queryAll();



		$sql = "SELECT * FROM pkg_user WHERE id = :id";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id", $result[0]['id_user'], PDO::PARAM_STR);
		$resultUser = $command->queryAll();


		$boleto_banco = $resultBoleto[0]['boleto_banco'];
		$boleto_banco = $boleto_banco == 'Banco do Brasil' ? 'bb' : ($boleto_banco == 'Caixa Economica' ? 'cef' : $boleto_banco);
		

		$especie_doc     = 'R$';
		$inicio_nosso_numero= $resultBoleto[0]['boleto_inicio_nosso_numeroa'];
		$convenio        = $resultBoleto[0]['boleto_convenio'];
		$agencia         = $resultBoleto[0]['boleto_agencia'];
		$conta           = $resultBoleto[0]['boleto_conta_corrente'];
		$carteira        = $resultBoleto[0]['boleto_carteira'];
		$taxa_boleto     = $resultBoleto[0]['boleto_taxa'];
		$instrucoes1     = utf8_decode($resultBoleto[0]['boleto_instrucoes']);
		$cedente         = utf8_decode($resultBoleto[0]['boleto_nome_emp']);
		$endereco        = utf8_decode($resultBoleto[0]['boleto_end_emp']);
		$cidade_cidade   = utf8_decode($resultBoleto[0]['boleto_cidade_emp']);
		$cidade_uf       = $resultBoleto[0]['boleto_estado_emp'];
		$cpf_cnpj        = $resultBoleto[0]['boleto_cpf_emp'];
		$sacado          = utf8_decode($resultUser[0]['firstname'] . ' ' .$resultUser[0]['lastname']);

		$endereco1       = utf8_decode($resultUser[0]['address']);
		$endereco2       = utf8_decode($resultUser[0]['city']. ' - '. $resultUser[0]['state']);
		$data_vencimento = $vencimiento;
		$valor_cobrado   = $result[0]['payment'];
		$data_pedido     = date('d/m/Y');
		$nosso_numero    = $numero_documento = $id;


		if (file_exists("/var/www/html/mbilling/protected/commands/BoletoRemessaBradescoCommand.php") && isset( $_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 1 && $result[0]['registrado'] == 0) {
			
			if (strlen($resultUser[0]['doc']) < 10) {
				echo "ESTE BOLETO NAO PODE SER REGISTRADO SEM O CPF/CNPJ DO CLIENTE";
				exit;
			}
			$valor_boleto=number_format($valor_cobrado, 2, '', '');
			$data_venc_registrar = date('dmy', strtotime($result[0]['vencimento']));
			$boleto = $id ."|".$resultUser[0]['firstname']."|".$resultUser[0]['doc']."|".preg_replace("/,/", "", $valor_boleto) ."|".utf8_decode($resultUser[0]['address'])."|".$resultUser[0]['zipcode']."|".$data_venc_registrar ;
	
			$resultRemessa = exec("php /var/www/html/mbilling/cron.php boletoremessabradesco '$boleto'");
			if (preg_match("/rem_/", $resultRemessa)) {
				echo "<br><br><a href='http://131.72.141.34/mbilling/tmp/".$resultRemessa."'>Baixar arquivo de remessa</a><br><br><br><br>";
			}
		}

		include ('lib/boletophp/boleto_'.$boleto_banco .'.php');
	}
}
?>

