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
class CallChartCommand extends CConsoleCommand 
{

	private $host = 'localhost';
    private $user = 'magnus';
    private $password = 'magnussolution';


	public function run($args)
	{
		include("/var/www/html/mbilling/protected/commands/AGI.Class.php");

		define('LOGFILE', 'protected/runtime/CallChartPid.log');
		define('DEBUG', 0);

		if (!defined('PID')) {
		define("PID", "/var/run/magnus/CallChartPid.php");
		}	

		if (Process :: isActive()) {
			$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " PROCESS IS ACTIVE ") : null;
			//die();
		} else {
			Process :: activate();
		}
		$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " START NOTIFY CLIENT ") : null;

		for ($i=0; $i < 5; $i++) {


			$asmanager = new AGI_AsteriskManager;
	        $asmanager->connect($this->host, $this->user, $this->password);

	        $server = $asmanager->Command("core show channels");
	        $arr = explode("\n", $server["data"]);
	        $asmanager->disconnect();
	        $total  = 0;

	        foreach ($arr as $key => $value) {
	        	if (preg_match("/Up/", $value)) {
	        		$total++;
	        	}        	
	        }

	        $total = intval($total / 2);

	        if($i == 0){
	        	
	        	$sql = "INSERT INTO pkg_call_chart (date, answer, total) VALUES ('".date('Y-m-d H:i:s')."', $total, 0)";
	        	Yii::app()->db->createCommand($sql)->execute();	 


	        	$id = Yii::app()->db->lastInsertID;
	        	$total1 = $total;
	        }else{
	        	if ($total > $total1) {
	        		$sql = "UPDATE pkg_call_chart SET answer = $total WHERE id = ".$id;
	        		Yii::app()->db->createCommand($sql)->execute();
	        	}
	        }

	        if (date('H:i') == '23:52') {
	            $sql = "DELETE FROM pkg_call_chart WHERE date < '".date('Y-m-d')."'";
				Yii::app()->db->createCommand($sql)->execute();
	        }     

	        sleep(12);
        }
	}
}