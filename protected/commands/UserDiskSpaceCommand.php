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
class UserDiskSpaceCommand extends CConsoleCommand 
{

	public $titleReport;
	public $subTitleReport;
	public $fieldsCurrencyReport;
	public $fieldsPercentReport;
	public $rendererReport;
	public $fieldsFkReport;

	public function run($args)
	{
		define('LOGFILE', 'protected/runtime/UserDiskSpace.log');
		define('DEBUG', 0);

		if (!defined('PID')) {
		define("PID", "/var/run/magnus/UserDiskSpacePid.php");
		}	

		if (Process :: isActive()) {
			$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " PROCESS IS ACTIVE ") : null;
			die();
		} else {
			Process :: activate();
		}
		$log = DEBUG >=1 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  " START NOTIFY CLIENT ") : null;

		
		$config = LoadConfig::getConfig();

		Yii::app()->language = Yii::app()->sourceLanguage = isset($config['global']['base_language']) ? $config['global']['base_language']  : Yii::app()->language;

		$sql = "SELECT * FROM pkg_user WHERE disk_space > 0";
		$userResult = Yii::app()->db->createCommand($sql)->queryAll();
		


		$log = DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__.  $sql) : null;

		if (count($userResult) == 0){
			echo "NO USER TO SEND INVOICE $sql";
			exit(DEBUG >=3 ? Log :: writeLog(LOGFILE, ' line:' .__LINE__. " NO USER TO SEND INVOICE") : null);
		}
				ini_set("memory_limit", "4024M");
		foreach ($userResult as $user)
		{
			$userDiskSpace = $user['disk_space'];

			$totalDiskSpave = @exec("ls -lR  /var/spool/asterisk/monitor/".$user['username']."* | grep -v '^d' | awk '{total += $5} END {print total}'");
			$totalMonitorGB = is_numeric($totalDiskSpave) ? $totalDiskSpave / 1000000000 : 0;			

			if ($totalMonitorGB > $userDiskSpace) {
				echo 'Superou '. $userDiskSpace. ' '. $totalDiskSpave."\n";
			}else{
				echo "User have disk space\n";
				continue;
			}


			$lastFile = exec('ls -tr /var/spool/asterisk/monitor/*'.$user['username'].'* | head -n 1 ');

			if (file_exists($lastFile)) {
				$lastFileTime =  filemtime($lastFile);

				echo "Older file found=".date('Y-m-d', $lastFileTime)."\n";
				$lastFileTime +=604800;
				echo "DELETE files older than ".date('Y-m-d', $lastFileTime)."\n";

				exec("rm -f /var/spool/asterisk/monitor/*".$user['username']."*".$lastFileTime."*");

			}else{
				continue;
			}
			
			$mail = new Mail(Mail::$TYPE_USER_DISK_SPACE, $user['id']);
			$mail->replaceInEmail(Mail::$TIME_DELETE, date('Y-m-d',$lastFileTime));
			$mail->replaceInEmail(Mail::$ACTUAL_DISK_USAGE, $totalMonitorGB);
			$mail->replaceInEmail(Mail::$DISK_USADE_LIMIT, $userDiskSpace);
			try {
				$mail->send();
			} catch (Exception $e) {
				//error SMTP
			}

			if ($config['global']['admin_received_email'] == 1 && strlen($config['global']['admin_email'])) {
				try {
					$mail->send($config['global']['admin_email']);
				} catch (Exception $e) {
					
				}
				
			}	

		}
	}
}