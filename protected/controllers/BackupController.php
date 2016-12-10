<?php
/**
 * Acoes do modulo "Call".
 *
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
 * 17/08/2012
 */

class BackupController extends Controller
{
	private $diretory = "/usr/local/src/";
	public function actionRead()
	{
		if (Yii::app()->session['isAdmin'] == false)
			exit;
		if (!Yii::app()->session['id_user'])
            exit;
		$result = $this->scan_dir($this->diretory,1);

		$values = array();
		$start = $_GET['start'];
		$limit = $_GET['limit'];

		for ($i=0; $i < count($result); $i++) {

			if ($i < $start) {
				continue;
			}

			if (!preg_match("/backup_voip_Magnus/", $result[$i]) ) {
				continue;
			}
			$size = filesize($this->diretory.$result[$i]) / 1000000;
			$values []= array(
				'id' => $i, 
				'name' => $result[$i],
				'size' => number_format($size,2) . ' MB');
		}
		

		//
		# envia o json requisitado
		echo json_encode(array(
			$this->nameRoot => $values,
			$this->nameCount => $i,
			$this->nameSum => array()
		));
	}

	public function scan_dir($dir) {
	    $ignored = array('.', '..', '.svn', '.htaccess');

	    $files = array();    
	    foreach (scandir($dir) as $file) {
	        if (in_array($file, $ignored)) continue;
	        $files[$file] = filemtime($dir . '/' . $file);
	    }

	    arsort($files);
	    $files = array_keys($files);

	    return ($files) ? $files : false;
	}

	public function actionDestroy()
	{
		if (Yii::app()->session['isAdmin'] == false)
			exit;
		$ids = json_decode($_POST['ids']);
		foreach ($ids as $key => $value) {
			unlink($this->diretory.$value);
		}

		# retorna o resultado da execucao
		echo json_encode(array(
			$this->nameSuccess => $this->success,
			$this->nameMsg => $this->success
		));
	}

	public function actionSave()
	{
		if (Yii::app()->session['isAdmin'] == false)
			exit;
		exec("php /var/www/html/mbilling/cron.php Backup");

		echo json_encode(array(
			$this->nameSuccess => $this->success,
			$this->nameRoot => $this->attributes,
			$this->nameMsg => $this->msg . ' Backup in process, this task can spend many time to finish.'
		));

	}

	public function actionRecovery()
	{
		if (Yii::app()->session['isAdmin'] == false)
			exit;
		$name = json_decode($_POST['id']);
		
		if (file_exists('/var/www/html/mbilling/tmp/base.sql')) {
			unlink("/var/www/html/mbilling/tmp/base.sql");

		}		
		exec("tar xzvf /usr/local/src/".$name);

		exec("rm -rf /var/www/html/mbilling/etc");

		if (file_exists('/var/www/html/mbilling/tmp/base.sql')) {

			$configFile = '/etc/asterisk/res_config_mysql.conf';
			$array		= parse_ini_file($configFile);
			$username	= $array['dbuser'];
			$password	= $array['dbpass'];
			$base	= $array['dbname'];

			$sql = "DROP database $base";
			Yii::app()->db->createCommand($sql)->execute();

			$sql = "CREATE database $base";
			Yii::app()->db->createCommand($sql)->execute();			

			exec("mysql -u $username -p$password $base < /var/www/html/mbilling/tmp/base.sql > /dev/null 2>/dev/null &");

			echo json_encode(array(
				$this->nameSuccess => $this->success,
				$this->nameRoot => $this->attributes,
				'msg' => $this->msg
			));
		}
		else{
			echo json_encode(array(
				$this->nameSuccess => false,
				$this->nameRoot => $this->attributes,
				'msg' => "file not exists"
			));
		}

		exec("rm -rf /var/www/html/mbilling/tmp");
	}
}