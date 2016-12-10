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
class PortabilidadeCommand extends CConsoleCommand 
{
	public function run($args)
	{
		shell_exec('mkdir -p /usr/src/ChipCerto');
		shell_exec('rm -rf /usr/src/ChipCerto/*');
		shell_exec('cd /usr/src/ChipCerto && wget ftp://'.$_SERVER['argv']['2'].':'.$_SERVER['argv']['3'].'@ftp.portabilidadecelular.com:2157/portabilidade.tar.bz2 && tar -jxvf portabilidade.tar.bz2');
		shell_exec('cd /usr/src/ChipCerto && wget ftp://'.$_SERVER['argv']['2'].':'.$_SERVER['argv']['3'].'@ftp.portabilidadecelular.com:2157/prefix_anatel.csv');

		if(!file_exists("/usr/src/ChipCerto/prefix_anatel.csv"))
			exit;		

		if(filesize("/usr/src/ChipCerto/prefix_anatel.csv") < '100')
			exit;

		$sql = "CREATE TABLE IF NOT EXISTS pkg_portabilidade_prefix (
		  `id` int(11) NOT NULL auto_increment,
		  `number` bigint(15) NOT NULL,
		  `company` int(5) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `number` (`number`),
		  KEY `company` (`company`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
		Yii::app()->db->createCommand($sql)->execute();

		$sql = "TRUNCATE pkg_portabilidade_prefix";
		Yii::app()->db->createCommand($sql)->execute();


		$sql = "LOAD DATA LOCAL INFILE '/usr/src/ChipCerto/prefix_anatel.csv' INTO TABLE pkg_portabilidade_prefix FIELDS TERMINATED BY ';'  LINES TERMINATED BY '\n'  (number, company);";
     	Yii::app()->db->createCommand($sql)->execute();

 
     	if(!file_exists("/usr/src/ChipCerto/exporta.csv"))
			exit;		

		if(filesize("/usr/src/ChipCerto/exporta.csv") < '10000')
			exit;

		$sql = "CREATE TABLE IF NOT EXISTS pkg_portabilidade (
		  `id` int(11) NOT NULL auto_increment,
		  `number` bigint(15) NOT NULL,
		  `company` int(5) NOT NULL,
		  `date` varchar(30) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `number` (`number`),
		  KEY `company` (`company`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
		Yii::app()->db->createCommand($sql)->execute();

		$sql = "TRUNCATE pkg_portabilidade";
		Yii::app()->db->createCommand($sql)->execute();		

		$sql = "LOAD DATA LOCAL INFILE '/usr/src/ChipCerto/exporta.csv' INTO TABLE pkg_portabilidade FIELDS TERMINATED BY ';'  LINES TERMINATED BY '\n'  (id, number, company, date);";
     	Yii::app()->db->createCommand($sql)->execute();
	}
}