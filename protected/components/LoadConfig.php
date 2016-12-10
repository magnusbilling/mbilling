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
class Loadconfig
{	
	public static function getConfig()
	{
		$sql = "SELECT * FROM pkg_configuration";
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		$config = array();
		foreach ($result as $conf)
			$config[$conf['config_group_title']][$conf['config_key']] = $conf['config_value'];

		return $config;
	}
}
?>