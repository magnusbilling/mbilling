<?php
/**
 * Acoes do modulo "CallShop".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author  Adilson Leffa Magnus.
 * @copyright   Todos os direitos reservados.
 * ###################################
 * =======================================
 * MagnusSolution.com <info@magnussolution.com>
 * 19/09/2012
 */
Yii::import('application.controllers.BuyCreditController');
class BuyCreditORController extends BuyCreditController
{

	public function actionMethod(){

		echo 'okteste';
		exit;
		$methodPay= BuyCredit::model()->findByPK($_GET['id_method']);
		if ($methodPay->payment_method == 'BoletoBancario') {
			$this->actionBoletoBancario();
		}else{
			parent::actionMethod();
		}
	}
    public function actionBoletoBancario()
    {

    	print_r($_REQUEST);
    	exit;
    		$sql = "SELECT id FROM pkg_boleto WHERE id=".$idBoleto;
    		echo $sql;

		$resultBoleto = Yii::app()->db->createCommand($sql)->queryAll();

		print_r($resultBoleto);
		exit;
    		$url = "http://api.superlogica.net:80/v2/financeiro/clientes";
									
    		$app_token = "UJ9U11Xob7uZ";
		$access_token = "8MXXfEhkIVcw";

		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 

		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded",
		                                           "app_token: ".$app_token,
		                                           "access_token:". $access_token
		                                           ));



        $SLparams = array(	"ID_SACADO_SAC"			=>Yii::app()->session['id_user'],
				            "ST_NOMEREF_SAC"		=>$this->username,
				            "ID_PRODUTO_PRD"	=> date('d'),
				            "NM_QUANTIDADE_COMP "			=>1,
				            "VL_UNITARIO_PRD"				=>$this->doc,
				            "ST_CEP_SAC"			=>$this->zipcode,
				            "ST_ENDERECO_SAC"		=>$this->address,
				            "ST_CIDADE_SAC"			=>$this->city,
				            "ST_ESTADO_SAC"			=>$this->state,
				            "ST_EMAIL_SAC"			=>$this->email,
				            "SENHA"					=>$this->password,
				            "SENHA_CONFIRMACAO"		=>$this->password,
				            "ST_TELEFONE_SAC"		=>$this->phone
				            );
    }
}?>