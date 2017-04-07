<?php
/**
 * Acoes do modulo "PhoneNumber".
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
 * 28/10/2012
 */

class PhoneNumberController extends Controller
{
	public $attributeOrder        = 't.id';
	public $extraValues           = array('idPhonebook' => 'name');
	public $filterByUser          = 'g.id_user';
	public $join                  = 'JOIN pkg_phonebook g ON g.id = id_phonebook';
	public $select                = 't.id, id_phonebook, number, t.name, t.status, t.info, t.city';

	public $fieldsFkReport = array(
        'id_phonebook' => array(
            'table' => 'pkg_phonebook',
            'pk' => 'id',
            'fieldReport' => 'name'
        )
    );

	public function init()
	{
		$this->instanceModel = new PhoneNumber;
		$this->abstractModel = PhoneNumber::model();
		$this->titleReport   = Yii::t('yii','Phone Number');
		parent::init();
	}

	public function extraFilter ($filter)
	{
		$filter = isset($this->filter) ? $filter.$this->filter : $filter;

		$filter = $filter . ' AND ' .$this->defaultFilter;
		$filter = $this->filterReplace($filter);

		if(Yii::app()->getSession()->get('user_type')  == 2)
			$filter .= ' AND g.id_user IN  (SELECT id FROM pkg_user WHERE id_user = '.Yii::app()->getSession()->get('id_user').' ) ';


		else if(Yii::app()->getSession()->get('user_type')  > 1 && $this->filterByUser)
		{
			$filter .= ' AND '. $this->defaultFilterByUser . ' = '.Yii::app()->getSession()->get('id_user');
		}

		return $filter;
	}	



	public function actionCsv($value='')
	{
		$_GET['columns'] = preg_replace('/status/', 't.status', $_GET['columns']);
		$_GET['columns'] = preg_replace('/name/', 't.name', $_GET['columns']);

		parent::actionCsv();
	}

	public function actionReport($value='')
	{
		$_POST['columns'] = preg_replace('/status/', 't.status', $_POST['columns']);
		$_POST['columns'] = preg_replace('/name/', 't.name', $_POST['columns']);

		
		parent::actionReport();
	}

	public function getAttributesRequest() {
		$arrPost = array_key_exists($this->nameRoot, $_POST) ? json_decode($_POST[$this->nameRoot], true) : $_POST;
		
		//alterar para try = 0 se activar os numeros
		if($this->abstractModel->tableName() == 'pkg_phonenumber'){
			if (isset($arrPost['status']) && $arrPost['status'] == 1) {				
				$arrPost['try'] = '0';
			}
		}
		
		return $arrPost;
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
		$idPhonebook = $values['id_phonebook'];

		$handle = fopen($_FILES['file']['tmp_name'], "r");

		$sql = array();

		while($row = fgetcsv($handle)) 
		{
			if (!isset($i)) 
			{
				$rowColunm = $row;
				array_push($rowColunm, 'id_phonebook');
				if ($row[0] == 'number') {
					$colunas =  implode(",", $rowColunm);
					$i=true;
					continue;
				}else{
					$colunas = 'number,id_phonebook';
				}				
				
			}
			
			/*$pattern = array("'é'", "'è'", "'ë'", "'ê'", "'É'", "'È'", "'Ë'", "'Ê'", "'á'",
			 "'à'", "'ä'", "'â'", "'å'", "'Á'", "'À'", "'Ä'", "'Â'", "'Å'", "'ó'", "'ò'", 
			 "'ö'", "'ô'", "'Ó'", "'Ò'", "'Ö'", "'Ô'", "'í'", "'ì'", "'ï'", "'î'", "'Í'", 
			 "'Ì'", "'Ï'", "'Î'", "'ú'", "'ù'", "'ü'", "'û'", "'Ú'", "'Ù'", "'Ü'", "'Û'", 
			 "'ý'", "'ÿ'", "'Ý'", "'ø'", "'Ø'", "'œ'", "'Œ'", "'Æ'", "'ç'", "'Ç'", "'\''", "'#'");*/


			//$row = preg_replace($pattern, "", $row);			
			array_push($row, $idPhonebook);
			$values = implode("','", $row);
			$sql[] = "('$values')";			

			
		}

		SqlInject::sanitize($sql);
		
		$sql = 'INSERT INTO pkg_phonenumber ('.$colunas.') VALUES '.implode(',', $sql).';';
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

	public function actionReprocesar()
	{
		$module = $this->instanceModel->getModule();

		if(!AccessManager::getInstance($module)->canUpdate()){
				header('HTTP/1.0 401 Unauthorized');
	 			die("Access denied to save in module: $module");
		}

		# recebe os parametros para o filtro
		if(isset($_POST['filter']) && strlen($_POST['filter']) > 5)
			$filter =  $_POST['filter'];
		else{
			echo json_encode(array(
				$this->nameSuccess => false,
				$this->nameMsg => 'Por favor realizar um filtro para reprocesar' 
			));
			exit;
		}
		$filter = $filter ? $this->createCondition(json_decode($filter)) : '';	

		if (!preg_match('/honebook/', $filter)) {
			echo json_encode(array(
				$this->nameSuccess => false,
				$this->nameMsg => 'Por favor filtre uma agenda para reprocesar' 
			));
			exit;
		}else{
			$filter = preg_replace("/idPhonebookname/", 'g.name', $filter);
		}


		$sql = "UPDATE pkg_phonenumber a  JOIN pkg_phonebook g ON a.id_phonebook = g.id SET a.status = 1, a.try = 0 WHERE a.status = 2 AND $filter";
		Yii::app()->db->createCommand($sql)->execute();

		echo json_encode(array(
			$this->nameSuccess => true,
			$this->nameMsg => 'Números atualizados com sucesso'
		));

	}
}