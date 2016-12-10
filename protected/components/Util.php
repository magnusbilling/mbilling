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

class Util {

	public static function arrayFindByProperty($array, $key, $value) {
		$results = array();

		if(is_array($array)) {
			if(isset($array[$key]) && $array[$key] == $value) {
				$results[] = $array;
			}

			foreach ($array as $subArray) {
				$results = array_merge($results, Util::arrayFindByProperty($subArray, $key, $value));
			}
		}

		return $results;
	}

	public static function insertLOG($action, $id_user = NULL, $ip, $description) {
		
		$sql = "INSERT INTO pkg_log (id_user, description, action, ip ) 
				VALUES (:id_user , :info , :action ,:ip )";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":action", $action, PDO::PARAM_STR);
		$command->bindValue(":id_user", $id_user, PDO::PARAM_STR);
		$command->bindValue(":info", $description, PDO::PARAM_STR);
		$command->bindValue(":ip", $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
		$command->execute();
	}
	public static function getNewUsername()
	{
		$existsUsername = true;
		
		$config = LoadConfig::getConfig();

		$generate_username = $config['global']['username_generate'];
		$length = $config['global']['generate_length'] == 0 ? 5 : $config['global']['generate_length'];
		$prefix= $config['global']['generate_prefix'] == 0 ? '' : $config['global']['generate_prefix'];
		if($generate_username == 1){		
		
			while ($existsUsername)
			{
				$randUserName = $prefix.Util::gerarSenha($length, false, false, true, false)."\n";
				$countUsername = Signup::model()->count(array(
					'condition' => "username LIKE '$randUserName'"
				));
				
				$existsUsername = ($countUsername > 0);
			}
		}
		else
			$randUserName = Util::getNewUsername2();

		return trim($randUserName);
	}

	public static function getNewUsername2()
	{
		$existsUsername = true;

		while ($existsUsername)
		{
			$randUserName = mt_rand(10000, 99999);
			$countUsername = Signup::model()->count(array(
				'condition' => "username LIKE '$randUserName'"
			));

			$existsUsername = ($countUsername > 0);
		}
		return $randUserName;
	}

	public static function gerarSenha ($tamanho, $maiuscula, $minuscula, $numeros, $codigos)
	{
		$maius = "ABCDEFGHIJKLMNOPQRSTUWXYZ";
		$minus = "abcdefghijklmnopqrstuwxyz";
		$numer = "0123456789";
		$codig = '!@#%';

		$base = '';
		$base .= ($maiuscula) ? $maius : '';
		$base .= ($minuscula) ? $minus : '';
		$base .= ($numeros) ? $numer : '';
		$base .= ($codigos) ? $codig : '';

		srand((float) microtime() * 10000000);
		$senha = '';
		for ($i = 0; $i < $tamanho; $i++) {
		$senha .= substr($base, rand(0, strlen($base)-1), 1);
		}
		return $senha;
	}

	public static function getNewLock_pin()
	{
		$existsLock_pin = true;

		while ($existsLock_pin)
		{
			$randLock_Pin = mt_rand(100000, 999999);
			$countLock_pin = Signup::model()->count(array(
				'condition' => "callingcard_pin LIKE '$randLock_Pin'"
			));

			$existsLock_pin = ($countLock_pin > 0);
		}
		return $randLock_Pin;
	}
}
?>