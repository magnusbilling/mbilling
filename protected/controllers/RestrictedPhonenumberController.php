<?php
/**
 * Acoes do modulo "RestrictedPhonenumber".
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

class RestrictedPhonenumberController extends Controller
{
	public $attributeOrder     = 'id';
	public $extraValues        = array('idUser' => 'username');
	public $filterByUser        = true;
    	public $defaultFilterByUser = 'b.id_user';
    	public $join                = 'JOIN pkg_user b ON t.id_user = b.id';
    	
	public $fieldsFkReport = array(
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		)
	);

	public function init()
	{
		$this->instanceModel = new RestrictedPhonenumber;
		$this->abstractModel = RestrictedPhonenumber::model();
		$this->titleReport   = Yii::t('yii','Config');
		parent::init();
	}
	public function actionImportFromCsv()
	{
		$module = $this->instanceModel->getModule();

		if(!AccessManager::getInstance($module)->canCreate()){
				header('HTTP/1.0 401 Unauthorized');
	 			die("Access denied to save in module: $module");
		}

		if(!Yii::app()->session['id_user'])
			exit();

		ini_set("memory_limit", "1024M");
		ini_set("upload_max_filesize", "3M");
		ini_set("max_execution_time", "90");

		$values = $this->getAttributesRequest();
		$id_user = $values['id_user'];

		$handle = fopen($_FILES['file']['tmp_name'], "r");

		$sql = array();

		while($row = fgetcsv($handle)) 
		{
			if (!isset($i)) 
			{
				$rowColunm = $row;
				array_push($rowColunm, 'id_user');
				$colunas = 'number,id_user';
							
				
			}

			//$row = preg_replace($pattern, "", $row);			
			array_push($row, $id_user);
			$values = implode("','", $row);
			$sql[] = "('$values')";			

			
		}

		SqlInject::sanitize($sql);
		
		$sql = 'INSERT INTO pkg_restrict_phone ('.$colunas.') VALUES '.implode(',', $sql).';';
		Yii::log($sql, 'info');
		try {
			$this->success = $result = Yii::app()->db->createCommand($sql)->execute();
		}
		catch (Exception $e) {
			$this->success = false;
			Yii::log(print_r($e), 'info');
			$this->nameMsg = $this->getErrorMySql($e);
		}

		fclose($handle);	


		echo json_encode(array(
			$this->nameSuccess => $this->success,
			$this->nameMsg => $this->msg
		));
	}
}