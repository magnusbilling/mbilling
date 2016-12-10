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
class sqlInject {	

	public static function sanitize($src){
		$codes = array(
			'UPDATE ' ,
			'SELECT ' ,
			' SET ' ,
			' OR ' ,
			' TABLE ',
			'DELETE FROM',
			' DATABASE ',
			'DROP TABLE',
			'DROP DATABASE',
			'SCHEMA',
			'CONCAT',
			'foreign_key',
			'TRUNCATE ',
			'CREATE '
		);

		foreach ($src as $key => $value) {
			foreach ($codes as  $code) {

				if (is_array($value)) {
					foreach ($value as $key => $valuearray) {
						if(stripos($valuearray, $code)){				

							$info = 'Trying SQL inject, code: '. $value . '. Controller => '.Yii::app()->controller->id;
							$id_user = isset(Yii::app()->session['id_user']) ? Yii::app()->session['id_user'] : 'NULL';					
							Util::insertLOG('EDIT',$id_user,$_SERVER['REMOTE_ADDR'],$info);
							echo json_encode(array(
								'rows' => array(),
								'count' => 0,
								'sum' => array(),
								'msg' => 'SQL INJECT FOUND'
							));
							exit;
						}
					}
				}else{
					if(stripos($value, $code)){					

						$info = 'Trying SQL inject, code: '. $value . '. Controller => '.Yii::app()->controller->id;
						$id_user = isset(Yii::app()->session['id_user']) ? Yii::app()->session['id_user'] : 'NULL';					
						Util::insertLOG('EDIT',$id_user,$_SERVER['REMOTE_ADDR'],$info);
						echo json_encode(array(
							'rows' => array(),
							'count' => 0,
							'sum' => array(),
							'msg' => 'SQL INJECT FOUND'
						));
						exit;
					}
				}

				
			}
		}
	}	
}
?>