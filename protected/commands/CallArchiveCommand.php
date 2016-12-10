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
class CallArchiveCommand extends CConsoleCommand 
{


	public function run($args)
	{

		define('LOGFILE', 'protected/runtime/CallArchivePid.log');
		define('DEBUG', 0);

		if (!defined('PID')) {
		define("PID", "/var/run/magnus/CallArchivePid.php");
		}	

		if (Process :: isActive()) {
			echo ' PROCESS IS ACTIVE';
			$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " PROCESS IS ACTIVE ") : null;
			die();
		} else {
			Process :: activate();
		}
		$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " START NOTIFY CLIENT ") : null;



		$config = LoadConfig::getConfig();
		$prior_x_month = $config['global']['archive_call_prior_x_month'];

		echo 'ok';
		$condition = "DATE_SUB(NOW(),INTERVAL $prior_x_month MONTH) > starttime";

		$sql = "CREATE TABLE IF NOT EXISTS pkg_cdr_failed_archive (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `id_user` int(11) NOT NULL,
			  `id_plan` int(11) DEFAULT NULL,
			  `id_trunk` int(11) DEFAULT NULL,
			  `id_prefix` int(11) DEFAULT NULL,
			  `sessionid` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
			  `uniqueid` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
			  `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `calledstation` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
			  `sipiax` int(11) DEFAULT '0',
			  `src` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
			  `terminatecauseid` int(1) DEFAULT '1',
			  PRIMARY KEY (`id`),
			  KEY `id_user` (`id_user`),
			  KEY `id_plan` (`id_plan`),
			  KEY `id_trunk` (`id_trunk`),
			  KEY `calledstation` (`calledstation`),
			  KEY `terminatecauseid` (`terminatecauseid`),
			  KEY `id_prefix` (`id_prefix`),
			  KEY `uniqueid` (`uniqueid`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
					Yii::app()->db->createCommand($sql)->execute();


		$c = 0;
		$tables = array('pkg_cdr','pkg_cdr_failed');
		foreach ($tables as $key => $table) {
	
			$sql = "SELECT count(*) AS count FROM $table WHERE $condition ";
			//echo $sql."\n\n";
			$result = Yii::app()->db->createCommand($sql)->queryAll();

			$loop = number_format($result[0]['count'] / 10000,0);
			
			if ($table == 'pkg_cdr')
				$func_fields = "id_user, id_plan, id_prefix, id_trunk, sessionid, uniqueid, starttime, stoptime, sessiontime, calledstation, sessionbill, sipiax, src, buycost, real_sessiontime, terminatecauseid, agent_bill";
			else
				$func_fields = "id_user, id_plan, id_prefix, id_trunk, sessionid, uniqueid, starttime, calledstation, sipiax, src, terminatecauseid";
			
			if ($c == 0) {
				$condition = $condition . " ORDER BY id LIMIT 10000";
				$c++;
			}

			for ($i=0; $i < $loop; $i++) {
				echo "New insert \n";
				$sql = "INSERT INTO ".$table."_archive ($func_fields) SELECT $func_fields FROM ".$table." WHERE $condition";
				//echo $sql."\n";
				Yii::app()->db->createCommand($sql)->execute();

				$sql = "DELETE FROM $table WHERE $condition";
				Yii::app()->db->createCommand($sql)->execute();
				sleep(60);
			}

		}


		
		      
	}
}