<?php
/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Heitor Gianastasio Pipet de Oliveira.
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

class AccessManager{

	private static $instance;
	private static $config;

	private $module;
	private $action;

	private function __construct($module){
		$this->setModule($module);
	}

	public static function getInstance($module){
		#session_start();
		if(is_null(self::$instance)){
			self::$instance = new AccessManager($module);
			self::$config = require('protected/config/permissions.php');
		} else {
			self::$instance->setModule($module);
		}
		return self::$instance; 
	}

	private function getResult($canDoIt){
		if (in_array($this->module, self::$config['only_admin']) && !Yii::app()->session['isAdmin']) {
			return false;
		}

		return $canDoIt;
	}

	public function canRead(){

		return $this->getResult(strpos($this->action, 'r') !== false);	
	}

	public function canCreate(){
		
		return $this->getResult(strpos($this->action, 'c') !== false);
	}

	public function canUpdate(){
		
		return $this->getResult(strpos($this->action, 'u') !== false);
	}



	public function canDelete() {
		return $this->getResult(strpos($this->action, 'd') !== false);
	}

	private function setModule($module){
		$this->module = $module;
		$this->action = isset(Yii::app()->session['action'][$module]) ? Yii::app()->session['action'][$module] : '';
	}
}