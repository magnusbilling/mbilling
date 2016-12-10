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

class CreditUser {

	public function checkGlobalCredit( $id_user ) {

		$sql = "SELECT id_user, credit, creditlimit, typepaid FROM pkg_user WHERE id=:id_user";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id_user", $id_user, PDO::PARAM_STR);
		$resultUser = $command->queryAll();

		$userCredit =  $resultUser[0]['typepaid'] == 1 ? $resultUser[0]['credit'] = $resultUser[0]['credit'] + $resultUser[0]['creditlimit'] : $resultUser[0]['credit'];

		if ($resultUser[0]['id_user'] > 1 ) {
			$sql = "SELECT id_user, credit, creditlimit, typepaid FROM pkg_user WHERE id= :id_user";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $resultUser[0]['id_user'], PDO::PARAM_STR);
			$resultUgent = $command->queryAll();
			$agentCredit =  $resultUgent[0]['typepaid'] == 1 ? $resultUgent[0]['credit'] = $resultUgent[0]['credit'] + $resultUgent[0]['creditlimit'] : $resultUgent[0]['credit'];

		}
		if ( $userCredit<= 0 || (isset($agentCredit) && $agentCredit <= 0) )
			return false;
		else
			return true;
	}



	public function getCredit( $id_user ) {
		$sql = "SELECT credit, creditlimit, typepaid FROM pkg_user WHERE id=:id_user";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id_user", $id_user, PDO::PARAM_STR);
		$resultUser = $command->queryAll();

		if ( $result[0]['typepaid'] == 1 )
			$result[0]['credit'] = $result[0]['credit'] + $result[0]['creditlimit'];

		return $result[0]['credit'];
	}
}
