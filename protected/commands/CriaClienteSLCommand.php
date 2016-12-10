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
class CriaClienteSLCommand extends CConsoleCommand 
{


	public function run($args)
	{
		define('LOGFILE', 'protected/runtime/CriarClienteSP.log');
		define('DEBUG', 0);

		if (!defined('PID')) {
			define("PID", "/var/run/magnus/CriarClienteSPPid.php");
		}	

		if (Process :: isActive()) {
			$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " PROCESS IS ACTIVE ") : null;
			die();
		} else {
			Process :: activate();
		}
		
    	$sql = "SELECT * FROM pkg_user WHERE id_sacado_sac IS NULL AND id_group > 1";
		$resultUser = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($resultUser as $key => $user) {
			$sql = "SELECT * FROM pkg_method_pay WHERE payment_method = 'SuperLogica' 
								AND active = 0";
			$methodResult = Yii::app()->db->createCommand( $sql )->queryAll();
			if (count($methodResult) > 0) {	

				$response  = $this->saveUserSLCurl($user,$methodResult[0]['SLAppToken']
											,$methodResult[0]['SLAccessToken']);

				if (isset( $response[0]->data->id_sacado_sac)){
					$id_sacado_sac = $response[0]->data->id_sacado_sac;
					$sql = "UPDATE pkg_user SET id_sacado_sac = $id_sacado_sac WHERE id=".$user['id'];
					echo $sql;
					Yii::app()->db->createCommand( $sql )->execute();
				}else{
					$sql = "UPDATE pkg_user SET id_sacado_sac = -1 WHERE id=".$user['id'];
					echo $sql;
					Yii::app()->db->createCommand( $sql )->execute();
				}
					
			}
		}

    }

    public function saveUserSLCurl($model,$SLAppToken,$SLAccessToken){
		$url = "http://api.superlogica.net:80/v2/financeiro/clientes";
		
		$params = array(	"ST_NOME_SAC"			=>$model['firstname'].' '.$model['lastname'],
					            "ST_NOMEREF_SAC"		=>$model['username'],
					            "ST_DIAVENCIMENTO_SAC"	=>date('d'),
					            "ST_CGC_SAC "			=>$model['vat'],
					            "ST_RG_SAC"				=>$model['doc'],
					            "ST_CEP_SAC"			=>$model['zipcode'],
					            "ST_ENDERECO_SAC"		=>$model['address'],
					            "ST_CIDADE_SAC"			=>$model['city'],
					            "ST_ESTADO_SAC"			=>$model['state'],
					            "ST_EMAIL_SAC"			=>$model['email'],
					            "SENHA"					=>$model['password'],
					            "SENHA_CONFIRMACAO"		=>$model['password'],
					            "ST_TELEFONE_SAC"		=>$model['phone']
				            );

		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 

		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded",
		                                           "app_token: ".$SLAppToken,
		                                           "access_token:". $SLAccessToken
		                                           ));



		$params['identificador']= '10000'.$model['id'];

		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		$response = (array)json_decode(curl_exec($ch));

		print_r($response);
		curl_close($ch);
		if(isset($response[0]->status) && $response[0]->status!=200){
			
			echo json_encode(array(
					'success' => false,
					'rows' => array(),
					'errors' => Yii::t('yii',$response[0]->msg)
				));

		}
		
		return $response;
	} 
}

