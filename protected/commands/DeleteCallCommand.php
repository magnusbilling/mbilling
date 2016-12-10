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
class DeleteCallCommand extends CConsoleCommand 
{


	public function run($args)
	{
		define('LOGFILE', 'protected/runtime/DeleteCallPid.log');
		define('DEBUG', 0);

		if (!defined('PID')) {
		define("PID", "/var/run/magnus/DeleteCallPid.php");
		}	

		if (Process :: isActive()) {
			$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " PROCESS IS ACTIVE ") : null;
			die();
		} else {
			Process :: activate();
		}
		$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " START NOTIFY CLIENT ") : null;

		ini_set('memory_limit', '-1');
		$backdate = $this->subDayIntoDate(date('Ymd'),15);

		$sql = "DELETE FROM pkg_cdr WHERE sessiontime = 0 AND starttime < '".$backdate."' LIMIT 1000";
		Yii::app()->db->createCommand($sql)->execute();   
	}

	function subDayIntoDate($date,$days) {
	     $thisyear = substr ( $date, 0, 4 );
	     $thismonth = substr ( $date, 4, 2 );
	     $thisday =  substr ( $date, 6, 2 );
	     $nextdate = mktime ( 0, 0, 0, $thismonth, $thisday - $days, $thisyear );
	     return strftime("%Y-%m-%d", $nextdate);
	}
}