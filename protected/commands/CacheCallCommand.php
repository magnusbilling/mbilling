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
class CacheCallCommand extends CConsoleCommand 
{


	public function run($args)
	{
		define('LOGFILE', 'protected/runtime/CacheCallPid.log');
		define('DEBUG', 0);

		if (!defined('PID')) {
		define("PID", "/var/run/magnus/CacheCallPid.php");
		}	

		if (Process :: isActive()) {
			$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " PROCESS IS ACTIVE ") : null;
			die();
		} else {
			Process :: activate();
		}
		$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " START NOTIFY CLIENT ") : null;

		$nb_record = 100;
		$wait_time = 10;
		$cache_path = '/etc/asterisk/cache_mbilling.sqlite';
		if (empty ($cache_path)) {
	      echo "Path to the cache is not defined\n";
	      $log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " Path to the cache is not defined ") : null;
	      exit;
	    }
	    elseif(!file_exists($cache_path)){
	    	echo "File doesn't exist or permission denied\n";
	      	$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " File doesn't exist or permission denied ") : null;
	      	exit;
	    }
	    $fields = "uniqueid, sessionid, id_user, starttime, sessiontime, real_sessiontime, calledstation, terminatecauseid, ".
            "stoptime, sessionbill, id_plan, id_trunk, src, sipiax, buycost, id_prefix,agent_bill";
        $fields_failed = "uniqueid, sessionid, id_user, starttime,  calledstation, terminatecauseid, ".
            " id_plan, id_trunk, src, sipiax, id_prefix";

	    $db = new SQLite3($cache_path);
	    $i=0;
	    for (;;) {

	    	$insert = '';
        	$rowid = '';
        	try { 
        		$result = $db->query('SELECT rowid, '.$fields.' FROM pkg_cdr LIMIT 100') or die('Query failed');
        	} catch (Exception $e) {
	    		sleep(1);
        		continue;
        	}     		
        	
  		    while ($row = $result->fetchArray(SQLITE3_ASSOC)){
	    		//print_r($row);
	    		$rowid .= $row['rowid'].',';
	    		unset($row['rowid']);
	    		$value = "'".implode( "','", $row );
	    		$insert .= "($value'),";
		    		
	    	}


	    	if (strlen($insert) > 5) {
		    	$sql = "INSERT INTO pkg_cdr ($fields) VALUES ".substr($insert,0,-1).';';
	    		Yii::app()->db->createCommand($sql)->execute();
				echo $sql."\n";

				$sql = "DELETE FROM pkg_cdr WHERE rowid IN (".substr($rowid, 0,-1).")";
				echo $sql."\n";
				$db->exec($sql);
			}


            if (($i % 10) == 0) {
            	echo "$i is a multiple of 10<br />";
           
            	$insertFailed = '';
            	$rowidFailed = '';
            	$resultFailed = $db->query('SELECT rowid, '.$fields_failed.' FROM pkg_cdr_failed LIMIT 100') or die('Query failed');
      		    while ($rowFailed = $resultFailed->fetchArray(SQLITE3_ASSOC)){
		    		//print_r($rowFailed);
		    		$rowidFailed .= $rowFailed['rowid'].',';

		    		unset($rowFailed['rowid']);
		    		$value = "'".implode( "','", $rowFailed );
		    		$insertFailed .= "($value'),";
 		    		
		    	}
		    	if (strlen($insertFailed) > 5) {
			    	$sql = "INSERT INTO pkg_cdr_failed ($fields_failed) VALUES ".substr($insertFailed,0,-1).';';
		    		Yii::app()->db->createCommand($sql)->execute();
					echo $sql."\n";

					$sql = "DELETE FROM pkg_cdr_failed WHERE rowid IN (".substr($rowidFailed, 0,-1).")";
					echo $sql."\n";
					$db->exec($sql);
				}
            }
            $i++; 
            echo "Waiting ....\n";
            sleep($wait_time); 	

	    }   
	}
}