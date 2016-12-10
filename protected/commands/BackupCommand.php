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
class BackupCommand extends CConsoleCommand 
{


	public function run($args)
	{
		define('LOGFILE', 'protected/runtime/Backup.log');
		define('DEBUG', 0);
	
		$config = LoadConfig::getConfig();

		Yii::app()->language = Yii::app()->sourceLanguage = isset($config['global']['base_language']) ? $config['global']['base_language']  : Yii::app()->language;

        $data = date("d-m-Y");

		$configFile = '/etc/asterisk/res_config_mysql.conf';
		$array		= parse_ini_file($configFile);
		$username	= $array['dbuser'];
		$password	= $array['dbpass'];
		$base	= $array['dbname'];

        $data = date("d-m-Y");
        $comando = "/usr/bin/mysqldump -u".$username." -p".$password." ".$base." --ignore-table=".$base.".pkg_portabilidade --ignore-table=".$base.".pkg_cdr_archive --ignore-table=".$base.".pkg_cdr_failed > /tmp/base.sql";

        exec($comando);
        $comando = "tar czvf /usr/local/src/backup_voip_Magnus.$data.tgz /tmp/base.sql /etc/asterisk";
        exec($comando);
        exec("rm -f /tmp/base.sql");
	}
}