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

class Model extends CActiveRecord
{
	private $limiteUserModules = array(
		'refillprovider',
		'provider',
		'trunk',
		'configuration'
		);

	public function init(){

		
		$startSession = strlen(session_id()) < 1 ? session_start() : null;

		if (in_array($this->_module, $this->limiteUserModules) && !Yii::app()->session['isAdmin']) {

		 	echo json_encode(array(
				'success' => false,
				'msg' => 'Access denied to find in module' . $this->_module,
			));
		 	exit;
		}
		parent::init();
		
	}
	public function beforeFind() {

		//permite salvar cron
		if(isset($_SERVER['argv'][0]) && preg_match("/cron/", $_SERVER['argv'][0])){
			//echo '';
			return parent::beforeFind();
		}
		
		$action = isset(Yii::app()->session['action'][$this->_module]) ? Yii::app()->session['action'][$this->_module] : '';

		
		if(strpos($action, 'r') !== false) {
			return parent::beforeFind();
		}
		else {
		    header('HTTP/1.0 401 Unauthorized');
		 	die("Access denied to find in module: $this->_module");
		}
	}

	public function beforeSave() {

		//permite salvar cron
		if(isset($_SERVER['argv'][0]) && preg_match("/cron/", $_SERVER['argv'][0])){
			//echo 'Is Cron';
			return parent::beforeSave();
		}
			


		$action = isset(Yii::app()->session['action'][$this->_module]) ? Yii::app()->session['action'][$this->_module] : '';
		$pk = $this->primaryKey();
		$id = is_array($pk) ? false : $this->$pk;
		$actionModule = $id ? 'update' : 'create';

		if(($id && strpos($action, 'u') !== false) || (!$id && strpos($action, 'c') !== false)) {
			return parent::beforeSave();
		}
		else {
			header('HTTP/1.0 401 Unauthorized');
		 	die("Access denied to $actionModule in module: $this->_module");
		}
	}

	public function beforeDelete() {
		$action = isset(Yii::app()->session['action'][$this->_module]) ? Yii::app()->session['action'][$this->_module] : '';

		if(strpos($action, 'd') !== false) {
			return parent::beforeDelete();
		}
		else {
			header('HTTP/1.0 401 Unauthorized');
		 	die("Access denied to delete in module: $this->_module");
		}
	}
}