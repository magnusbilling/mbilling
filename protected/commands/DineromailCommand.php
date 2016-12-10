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
class DineromailCommand extends CConsoleCommand 
{


	public function run($args)
	{
		define('LOGFILE', 'protected/runtime/Dineromail.log');
		define('DEBUG', 0);

		if (!defined('PID')) {
		define("PID", "/var/run/magnus/DineromailPid.php");
		}	

		if (Process :: isActive()) {
			$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " PROCESS IS ACTIVE ") : null;
			die();
		} else {
			Process :: activate();
		}
		$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " START DINERMAIL MODULE ") : null;

		
		$config = LoadConfig::getConfig();

		Yii::app()->language = Yii::app()->sourceLanguage = isset($config['global']['base_language']) ? $config['global']['base_language']  : Yii::app()->language;

		$url = "http://finance.yahoo.com/d/quotes.csv?s=ARSUSD=X&f=l1";
		$handle = @fopen( $url, 'r' );
		if ( $handle ) {
			$result = fgets( $handle, 4096 );
			fclose( $handle );
		}
		$cambio = trim($result);

	

		$date = date('Ymd');

		$url = "https://argentina.dineromail.com/Vender/ConsultaPago.asp?Email=ventas@addphone.net&Acount=04162482&Pin=1XP4XQ18VV&StartDate=" . $date . "&EndDate=" . $date . "&XML=1";

		$xml = simplexml_load_file($url);

		foreach ($xml->Pays->Pay as $pagos)
		{

			$monto = $pagos->Trx_MontoNeto;
			$codigo = $pagos->Trx_Number;
			$medio_pago = $pagos->Trx_PaymentMethod . ' ' . $pagos->Trx_PaymentMean;
			$codigo_opcional = $pagos[0]->attributes(); //iduser


			$usuario = $pagos->Items->Item->Item_Description;
			$usuario = explode(",", $usuario);
			$usuario = $usuario[1];
			$usuario = str_replace(" ", "", $usuario);

			$monto = str_replace(",", ".", $monto);
    			$monto = ($monto * $cambio) * 0.875;


			$description = $medio_pago . ', Nro. de transaccion ' . $codigo;


			$sql = "SELECT * FROM pkg_user WHERE username = '$usuario'";
			$resultUser = Yii::app()->db->createCommand($sql)->queryAll();
			if (count($resultUser) > 0 ) 
			{
				echo $resultUser[0]['id'].' '. $monto.' '.$description.' '.$codigo;
				Process ::releaseUserCredit($resultUser[0]['id'], $monto, $description, $codigo);
			}			
		}
	}   
}

