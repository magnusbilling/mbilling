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
//not check credit and send call to any number, active or inactive
class SipProxyAccountsCommand extends CConsoleCommand 
{


	public function run($args)
	{	
		if (isset($args[0])){
			if($args[0] == 'log')
				define('DEBUG', 1);
			elseif ($args[0] == 'logAll') {
				define('DEBUG', 2);
			}
		}			
		else
			define('DEBUG', 0);


		if (!defined('PID'))
			define("PID", "/var/run/magnus/SipProxyPid.php");


		if (Process :: isActive()) {
			if(DEBUG >= 1) echo " PROCESS IS ACTIVE ";
			die();
		} else {
			Process :: activate();
		}

		$sql = "SELECT * FROM pkg_sip";
		$resultSips = Yii::app()->db->createCommand( $sql )->queryAll();
		
		
		$sql = "SELECT * FROM pkg_servers WHERE type = 'sipproxy' AND status = 1";
		$resultOpensips = Yii::app()->db->createCommand( $sql )->queryAll();


		foreach ( $resultOpensips as $key => $server ) {



			$hostname = $server['host'];
			$dbname = 'opensips';
			$table = 'subscriber';
			$user = $server['username'];
			$password = $server['password'];
			$port = $server['port'];

			$dsn='mysql:host='.$hostname.';dbname='.$dbname;

			$con=new CDbConnection( $dsn, $user, $password );
			$con->active=true;
			

			$sql = "TRUNCATE $table";
			$con->createCommand( $sql )->execute();

			foreach ($resultSips as $key => $sip) {

				$sql = "INSERT INTO $dbname.$table (username,domain,ha1,accountcode) VALUES ('".$sip['defaultuser']."', '$hostname','".md5($sip['defaultuser'].':'.$hostname.':'.$sip['secret'] )."', '".$sip['accountcode']."')";
				$con->createCommand( $sql )->execute();
			}			
			
		}		
	}	
}
?>
