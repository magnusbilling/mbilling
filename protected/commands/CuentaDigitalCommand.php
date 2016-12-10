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
class CuentaDigitalCommand extends CConsoleCommand 
{


	public function run($args)
	{
		define('LOGFILE', 'protected/runtime/CuentaDigital.log');
		define('DEBUG', 0);

		if (!defined('PID')) {
		define("PID", "/var/run/magnus/CuentaDigitalPid.php");
		}	

		if (Process :: isActive()) {
			$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " PROCESS IS ACTIVE ") : null;
			die();
		} else {
			Process :: activate();
		}
		$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " START PAYMENT CUENTADIGITAL ") : null;

		
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

		$lines = file('https://www.cuentadigital.com/exportacion.php?control=50dccff9ad9dc1946ff9b9020b5acafe&fecha=' . $date . '');		

		for ($i = 0; $i < count($lines); $i++)
		{
			if (list($fecha, $monto_pago, $monto, $codigo_barras, $codigo_opcional, $medio_pago, $num_pago) = preg_split("/\//", $lines[$i]))
			{
				$monto = preg_replace("/\./", "", $monto);
				$monto = preg_replace("/\,/", ".", $monto);
				$monto = ($monto * $cambio) * 0.875;

				$description = $medio_pago . ' ' . $codigo_barras;
				$sql = "SELECT * FROM pkg_user WHERE username = '$codigo_opcional'";
				$resultUser = Yii::app()->db->createCommand($sql)->queryAll();
				if (count($resultUser) > 0 ) 
				{
					Process ::releaseUserCredit($resultUser[0]['id'], $monto, $description, $codigo_barras);
				}
			}
		}
	}   
}