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

/*
1 * * * * php /var/www/html/mbilling/cron.php cachecall deleteDuplicate
* * * * * php /var/www/html/mbilling/cron.php cachecall 10 100
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


	    if (isset($args[0]) && $args[0] == 'count') {
       		$cache_path = '/etc/asterisk/cache_mbilling.sqlite';
			$db = new SQLite3($cache_path);
        	$result = @$db->query('SELECT count(*) AS count FROM pkg_cdr');
        	$res = $result->fetchArray(SQLITE3_ASSOC);
        	echo "pkg_cdr =". $res['count']."\n\n";

        	$result = @$db->query('SELECT count(*) AS count FROM pkg_cdr_failed');
        	$res = $result->fetchArray(SQLITE3_ASSOC);
        	echo "pkg_cdr_failed =". $res['count']."\n\n";

        	exit;
    	}


		if (Process :: isActive()) {
			$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " PROCESS IS ACTIVE ") : null;
			die();
		} else {
			Process :: activate();
		}
		$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " START NOTIFY CLIENT ") : null;

		$wait_time = isset($args[0]) ? $args[0] : 10;

		$nb_record = isset($args[1]) ? $args[1] : 100;


		if (isset($args[0]) && $args[0] == 'deleteDuplicate') {
			$this->checkDuplicateValues();
			exit;
		}

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
        		$result = @$db->query('SELECT rowid, '.$fields.' FROM pkg_cdr LIMIT '.$nb_record);
        	} catch (Exception $e) {
	    		sleep(1);
        		continue;
        	}

        	


        	if (gettype($result)!='object') {
        		$i++; 
        		echo "result is null";
            	sleep(1);
            	continue; 
        	}
        	
  		    while ($row = $result->fetchArray(SQLITE3_ASSOC)){
	    		//print_r($row);
	    		$rowid .= $row['rowid'].',';
	    		unset($row['rowid']);
	    		$row['real_sessiontime'] = $row['real_sessiontime'] == '' ? 0 : $row['sessiontime'];
	    		$value = "'".implode( "','", $row );
	    		$insert .= "($value'),";
		    		
	    	}


	    	if (strlen($insert) > 5) {
		    	$sql = "INSERT INTO pkg_cdr ($fields) VALUES ".substr($insert,0,-1).';';
	    		Yii::app()->db->createCommand($sql)->execute();
				//echo $sql."\n";

				$sql = "DELETE FROM pkg_cdr WHERE rowid IN (".substr($rowid, 0,-1).")";
				//echo $sql."\n";
				for ($s=0; $s < 30; $s++) { 
					try {
						
						@$db->exec($sql);						
					} catch (Exception $e) {
						echo "try delete again $sql";
						sleep(2);
					}
				}
			}


            //if (($i % 10) == 0) {
            	echo "$i is a multiple of 10<br />\n\n\n\n";
            	sleep(1);
           
            	$insertFailed = '';
            	$rowidFailed = '';
            	for ($s=0; $s < 5; $s++) { 
	            	try {
	            		$resultFailed = @$db->query('SELECT rowid, '.$fields_failed.' FROM pkg_cdr_failed LIMIT '.$nb_record);
	            	} catch (Exception $e) {
	            		sleep(2);
	            	}
	            }
            	
            	if (gettype($resultFailed)!='object') {
	        		$i++; 
	        		echo "result is null";
	            	sleep(1);
	            	continue; 
	        	}
        	
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
					//echo $sql."\n";

					$sql = "DELETE FROM pkg_cdr_failed WHERE rowid IN (".substr($rowidFailed, 0,-1).")";
					//echo $sql."\n";
					for ($s=0; $s < 30; $s++) { 
						try {
							//echo "try delete again $sql";
							@$db->exec($sql);						
						} catch (Exception $e) {
							echo "try delete again $sql";
							sleep(2);
						}
					}					
				}

           // }
            $i++; 
            echo "Waiting ....\n";
            sleep($wait_time); 	

	    }   
	}

	public function checkDuplicateValues()
	{
		$sql = "DELETE n1 FROM pkg_cdr n1, pkg_cdr n2 WHERE n1.id < n2.id AND n1.uniqueid = n2.uniqueid";
		echo $sql;
		Yii::app()->db->createCommand($sql)->execute();

		$sql = "DELETE n1 FROM pkg_cdr_failed n1, pkg_cdr_failed n2 WHERE n1.id < n2.id AND n1.uniqueid = n2.uniqueid";
		Yii::app()->db->createCommand($sql)->execute();
	}
}