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

class Controller extends BaseController
{
	public $sqlQuery;
	public $success = true;
	public $msg;
	public $select = '*';
	public $join;
	public $nameRoot = 'rows';
	public $nameCount = 'count';
	public $nameSuccess = 'success';
	public $nameMsg = 'msg';
	public $nameMsgErrors = 'errors';
	public $nameParamStart = 'start';
	public $nameParamLimit = 'limit';
	public $nameParamSort = 'sort';
	public $nameParamDir = 'dir';
	public $msgSuccess = 'Operation successful.';
	public $msgSuccessLot = 'Records updated successfully';
	public $msgRecordNotFound = 'Record not found.';
	public $msgRecordAlreadyExists = 'Record already exists.';
	public $defaultFilter = '1';
	public $fieldsFkReport;
	public $fieldsCurrencyReport;
	public $fieldsPercentReport;
	public $rendererReport;
	public $debug = 0;
	public $abstractModel;
	public $instanceModel;
	public $abstractModelRelated;
	public $nameModelRelated;
	public $nameFkRelated;
	public $nameOtherFkRelated;
	public $extraFieldsRelated = array();
	public $titleReport;
	public $subTitleReport;
	public $attributes = array();
	public $extraValues = array();
	public $mapErrorsMySql = array(
		1451 => 'Record to be deleted is related to another. Technical information: ',
		1452 => 'Record to be related not found: <br>Technical Information:',
		0 => 'Technical Information: '
	);

	public $filter;
	public $limit;
	public $group;
	public $msgError;
	public $filterByUser = true;
	public $defaultFilterByUser = 'id_user';
	public $is_ratecard_view;
	public $fieldCard;
	public $fieldsInvisibleClient = array();
	public $fieldsInvisibleAgent = array();
	public $nameSum = 'sum';
	public $recordsSum = array();
	public $saveAttributes = false;
	public $pathFileCsv = 'resources/csv/';
	public $pathFileReport = 'resources/reports/';
	public $nameFileCsv = 'exported';

	public function init()
	{

		if(!Yii::app()->session['id_user'])
			exit();

		if($this->getOverrideModel()){
			$model = explode("/", Yii::app()->controller->id);

			$model = isset($model[1]) ? $model[1] : $model[0].'OR';
			Yii::import('application.models.overrides.'.$model);
			$this->instanceModel = new $model;
			$this->abstractModel = $model::model($model);
		}		

		if($this->getOverride()){
			$this->paramsToSession();
			$this->redirect(array( 'overrides/'.Yii::app()->controller->id.'OR/'.$this->getCurrentAction()) );
		}

		$this->getSessionParams();

		include_once("protected/commands/AGI.Class.php");
		$this->subTitleReport         = Yii::t('yii','report');
		$this->msgSuccess             = Yii::t('yii', 'Operation was successful.');
		$this->msgSuccessLot          = Yii::t('yii', 'Records updated with sucess.');
		$this->msgRecordNotFound      = Yii::t('yii', 'Record not found.');
		$this->msgRecordAlreadyExists = Yii::t('yii', 'Record already exists.');
		$this->msgError = Yii::t('yii','Disallowed action');
		$this->mapErrorsMySql = array(
			1451 => Yii::t('yii', 'Record to be deleted is related to another. Technical information: '),
			1452 => Yii::t('yii', 'Record to be listed there. Technical information: '),
			0 => Yii::t('yii', 'Technical information: '),
		);

		ini_set('session.gc_maxlifetime',36000);
		ini_set("session.gc_divisor", "100");
		ini_set("session.gc_probability", "1");

		$startSession = strlen(session_id()) < 1 ? session_start() : null;

		if (isset($_POST['ws'])) {
			$this->actionLogin();
		}

		if(!isset(Yii::app()->session['language']))
		{
			$language = Yii::app()->db->createCommand("SELECT config_value FROM pkg_configuration  WHERE config_key LIKE 'base_language'")->queryAll();

			Yii::app()->session['language'] = $language[0]['config_value'];
			Yii::app()->language = Yii::app()->sourceLanguage = isset(Yii::app()->session['language']) ? Yii::app()->session['language']  : Yii::app()->language;
		}

		if (!isset($_POST['ws']) && isset(Yii::app()->session['logged']) && Yii::app()->session['logged'] === true && $_SERVER['SCRIPT_FILENAME'] != Yii::app()->session['systemName']) {
			Yii::app()->session->destroy();
		}

		$sql = "SELECT config_value FROM pkg_configuration WHERE config_key = 'log'";
        	$resultlog = Yii::app()->db->createCommand($sql)->queryAll();
        	$this->debug = isset($resultlog[0]['config_value']) ? $resultlog[0]['config_value'] : 0;

		parent::init();

	}


	private function getOverride(){
		if (!file_exists('protected/config/overrides.php'))
			return false;
		
		include_once 'protected/config/overrides.php';
		return isset($GLOBALS['overrides']['controllers'][Yii::app()->controller->id]) && in_array($this->getCurrentAction(), $GLOBALS['overrides']['controllers'][Yii::app()->controller->id]); 
	}

	private function getOverrideModel(){
		if (!file_exists('protected/config/overrides.php'))
			return false;
		
		include_once 'protected/config/overrides.php';
		$uri = explode("/", Yii::app()->getRequest()->getPathInfo());

		$module_name = preg_match("/overrides/", Yii::app()->getRequest()->getPathInfo()) ? substr($uri[1],0,-2) : $uri[0];
		return in_array($module_name, $GLOBALS['overrides']['models']); 
	}

	private function paramsToSession()
	{
		//Yii::log(print_r($_REQUEST,true),'info');
		Yii::app()->session['paramsGet'] = isset($_GET) ? json_encode($_GET) : NULL;
		Yii::app()->session['paramsPost'] = isset($_POST) ? json_encode($_POST) : NULL;

	}

	private function getCurrentAction(){
		$uri = explode("/", Yii::app()->getRequest()->getPathInfo());
		return $uri[1];
	}

	private function getSessionParams()
	{
		if(isset(Yii::app()->session['paramsGet']) && Yii::app()->session['paramsGet'] != NULL)
			$_GET = (array)json_decode(Yii::app()->session['paramsGet']);
		if(isset(Yii::app()->session['paramsPost']) && Yii::app()->session['paramsPost'] != NULL )
			$_POST = (array)json_decode(Yii::app()->session['paramsPost']);
		Yii::app()->session['paramsGet'] = Yii::app()->session['paramsPost'] = NULL;
	}
	/**
	 * Lista os registros da model
	 */
	public function actionRead()
	{
		# recebe os parametros do limit para paginacao
		$start = isset($_GET[$this->nameParamStart]) ? $_GET[$this->nameParamStart] : -1;
		$limit = isset($_GET[$this->nameParamLimit]) ? $_GET[$this->nameParamLimit] : -1;
		

		# recebe o parametro para ordenacao
		if (isset($_GET['group'])){
			$group = json_decode($_GET['group']);
			$_GET[$this->nameParamSort] = $group->property;

			$_GET[$this->nameParamDir] = $group->direction;
		}
		
		$sort = isset($_GET[$this->nameParamSort]) ? $_GET[$this->nameParamSort] : $this->attributeOrder;
 		$dir = isset($_GET[$this->nameParamDir]) ? ' ' . $_GET[$this->nameParamDir] : null;
		$order = $sort ? $sort . $dir : null;

		$order = $this->replaceOrder($order);

		
		# recebe os parametros para o filtro
		$filter = isset($_GET['filter']) ? $_GET['filter'] : null;
		$filterIn = isset($_GET['filterIn']) ? $_GET['filterIn'] : null;

		if($filter && $filterIn)
		{
			$filter = array_merge($filter, $filterIn);
		}
		else if($filterIn)
		{
			$filter = $filterIn;
		}

		$filter = $filter ? $this->createCondition(json_decode($filter)) : $this->defaultFilter;

		$this->filter = $filter = $this->extraFilter($filter);

		$group = isset($this->group) ? $this->group : 1;

		$limit = (strlen($filter) < 2 && isset($this->limit)) ?  $this->limit : $limit;
		if(isset($_GET['log'])){
			echo "SELECT $this->select FROM  ".$this->abstractModel->tableName()." t $this->join WHERE $filter GROUP BY $group LIMIT $limit";
			exit;
		}



		if (strlen($this->sqlQuery) > 5 AND preg_match("/SELECT/", $this->sqlQuery)) {
			$records = $this->abstractModel->findAllBySql($this->sqlQuery);
		}else{
			
				$records = $this->abstractModel->findAll(array(
					'select'    => $this->select,
					'join'      => $this->join,
					'condition' => $filter,
					'order'     => $order,
					'limit'     => $limit,
					'offset'    => $start,
					'group'     => $group
				));
			
		} 
		
		//Yii::log("SELECT $this->select FROM  ".$this->abstractModel->tableName()." t $this->join WHERE $filter", 'error');
		
		if (strlen($group) < 2) {
			$count = $this->abstractModel->count(array(
				'join'      => $this->join,
				'condition' => $filter
			));
		}else{
			$recordCont = $this->abstractModel->findAll(array(
				'select'    => $this->select,
				'join'      => $this->join,
				'condition' => $filter,
				'order'     => $order,
				'group'     => $group
			));
			$count = count($recordCont);
		}

		//verifica se tem valores extras para retornar
		$recordsSum = $this->recordsExtraSum($this->select, $this->join , $filter, $group, $limit, $records);

		# envia o json requisitado
		echo json_encode(array(
			$this->nameRoot => $this->getAttributesModels($records, $this->extraValues),
			$this->nameCount => $count,
			$this->nameSum => $this->getAttributesModels($recordsSum)
		));
	}

	/**
	 * Cria/Atualiza um registro da model
	 */
	public function actionSave()
	{

		$values = $this->getAttributesRequest();		

		if ($this->abstractModel->tableName() != 'pkg_campaign') {
			unset($values['id_phonebook_array'] );
		}		

		if(isset(Yii::app()->session['isClient']) && Yii::app()->session['isClient'])
		{
			foreach ($this->fieldsInvisibleClient as $field) {
   				unset($values[$field]);
			}
		}
		if(isset(Yii::app()->session['isAgent']) && Yii::app()->session['isAgent'])
		{
			foreach ($this->fieldsInvisibleAgent as $field) {
   				unset($values[$field]);
			}
		}

		$subRecords = isset($values[$this->nameOtherFkRelated]) ? $values[$this->nameOtherFkRelated] : false;
		$namePk = $this->abstractModel->primaryKey();

		$isUpdate = is_array($namePk)  ? false :  isset($values[$namePk]) > 0 ? true : false;

		unset($values[$this->nameOtherFkRelated]);

		if(Yii::app()->session['isClient'] && $this->abstractModel->tableName() != 'pkg_user') {
			$values['id_user'] = Yii::app()->session['id_user'];
		}
		# se chave composta
		if(is_array($namePk))
		{
			$id = array();
			$condition = '';

			foreach ($namePk as $pk) {
				$id[$pk] = $values[$pk];
				$condition .= "$pk = $values[$pk] AND ";
			}
			$condition = substr($condition, 0, -5);

			$model = $this->loadModel($id, $this->abstractModel) !== null ? $this->loadModel($id, $this->abstractModel) : $this->instanceModel;
			$model->attributes = $values;

			try {
				$this->success = $model->save();
				$errors = $model->getErrors();

				if(!count($errors)) {
					$newRecord = $this->abstractModel->findAll(array(
						'select' => $this->select,
						'condition' => $condition
					));

					$this->attributes = $this->getAttributesModels($newRecord, $this->extraValues);
				}
			}
			catch (Exception $e) {
				$this->success = false;
				$errors = $this->getErrorMySql($e);
			}

			if($this->success) {
				$nameMsg = $this->nameMsg;	
				$info = 'Module ' . preg_replace("/pkg_/", '', $this->abstractModel->tableName() ) . '  ' . json_encode($values);
				Util::insertLOG('EDIT',Yii::app()->session['id_user'],$_SERVER['REMOTE_ADDR'],$info);

			}
			else {
				$nameMsg = $this->nameMsgErrors;
			}

			$this->msg = $this->success ? $this->msgSuccess : $errors;
		}
		else if((isset($values[$namePk]) && is_array($values[$namePk])) || (isset($_POST['filter']) && strlen($_POST['filter']) > 0 ))
		{
			ini_set("memory_limit", "1500M");
			ini_set("max_execution_time", "120");
			$ids = array();

			if(isset($_POST['filter'])) {
				


				$filter = $this->createCondition(json_decode($_POST['filter']));

				$this->filter = $filter = $this->extraFilter($filter);

				//integra o filtro lookup no updateall 
				if (isset($_POST['defaultFilter'])) {

					$defaultFilter = $_POST['defaultFilter'];
					$defaultFilter = $this->createCondition(json_decode($defaultFilter));
					$defaultFilter = $this->filterReplace($defaultFilter);
					
					$filter .=  ' AND '.$defaultFilter;
				}

				$records = $this->abstractModel->findAll(array(
					'select' => 't.'.$this->abstractModel->primaryKey(),
					'join'=> $this->join,
					'condition' => $filter
				));

				$records = $this->getAttributesModels($records);

				foreach($records as $record)
				{
					array_push($ids, $record[$namePk]);
				}

				$values[$namePk] = $ids;
			}
			else
			{
				$ids = $values[$namePk];
			}

			$strIds = implode(',', $ids);


			//tratar codec no modulo sip e trunk
			if (isset($values['allow'])) {
				foreach ($values['allow'] as $key => $valueAllows) {
					if(strlen($valueAllows) > 1){
						$valueAllow = 1;
						break;
					}						
					else
						$valueAllow = 0;						
				}

				if ($valueAllow == 0)
					unset($values['allow']);
				else{
					$values['allow'] = implode(",", $values['allow']);
					$values['allow'] = preg_replace("/,0/", "", $values['allow']);
					$values['allow'] = preg_replace("/0,/", "", $values['allow']);
				}					
			}

			try {
				unset($values[$namePk]);
				unset($values['password']);
				$setters = '';


				foreach($values as $fieldName => $value) {
					if(isset($value['isPercent']) && is_bool($value['isPercent'])) {
						$v = $value['value'];
						$percent = $v / 100;
						$valuePercent = $value['isPercent'] ? "($fieldName * $percent)" : $v;

						if($value['isAdd']) {
							$valueUpdate = "$fieldName + $valuePercent";
						}
						else if($value['isRemove']) {
							$valueUpdate = "$fieldName - $valuePercent";
						}
						else {
							$valueUpdate = $valuePercent;
						}
					}
					else {
						$valueUpdate = (gettype($value) == 'integer') ? $value: "'$value'";
					}

       				$setters .= "$fieldName = $valueUpdate,";
				}

				$setters = substr($setters, 0, -1);

				$this->abstractModel->setScenario('update');
				$this->abstractModel->setIsNewRecord(false);
				$table = $this->abstractModel->tableName();
				$sql = "UPDATE $table SET $setters WHERE $namePk IN($strIds)";
				$this->success = Yii::app()->db->createCommand($sql)->execute() !== false;
				$this->msg = $this->msgSuccessLot;

				$info = 'Module ' . preg_replace("/pkg_/", '', $this->abstractModel->tableName() ) . " SET $setters WHERE $namePk IN($strIds)";
				$info = preg_replace("/'/", "", $info);
				Util::insertLOG('UPDATE ALL',Yii::app()->session['id_user'],$_SERVER['REMOTE_ADDR'],$info);

			}
			catch (Exception $e) {
				$this->success = false;
				$this->msg = $this->getErrorMySql($e);
			}


			# retorna o resultado da execucao
			echo json_encode(array(
				$this->nameSuccess => $this->success,
				$this->nameMsg => $this->msg
			));

			if(array_key_exists('subRecords', $values)) {
				$this->saveRelated($values);
			}

			return;
		}
		else
		{
			$id = $values[$namePk];
			# verifica se novo cadastro ou atualizacao de existente
			$model = $id ? $this->loadModel($id, $this->abstractModel) : $this->instanceModel;
			$model->attributes = $values;
			
			$filter = $this->extraFilter('');

		
			try {
				if(!$_SESSION['isAdmin'] && $id > 0){
					$newRecord = $this->abstractModel->findAll(array(
						'select' => $this->select,
						'join'=> $this->join,
						'condition' => "t.$namePk = $id $filter"
					));

					if (count($newRecord) < 1)  {
						return "Not allow - t.$namePk = $id $filter";
						exit;
					}
				}

				
				
				
				$this->success = $model->save();
				$errors = $model->getErrors();

				if(!count($errors)) {
					$id = $id ? $id : $model->$namePk;

					if($subRecords !== false) {
						$this->saveRelated($id, $subRecords, $isUpdate);
					}

					$newRecord = $this->abstractModel->findAll(array(
						'select' => $this->select,
						'join'=> $this->join,
						'condition' => "t.$namePk = $id $filter"
					));

					$this->attributes = $this->getAttributesModels($newRecord, $this->extraValues);
				}
			}
			catch (Exception $e) {
				$this->success = false;
				$errors = $this->getErrorMySql($e);
			}

			if($this->success) {
				//insert in log table

				$saveEdit = $id ? 'EDIT' : 'CREATE';
				$info = 'Module ' . preg_replace("/pkg_/", '', $this->abstractModel->tableName() ) . '  ' . json_encode($values);
				Util::insertLOG($saveEdit,Yii::app()->session['id_user'],$_SERVER['REMOTE_ADDR'],$info);
				
				$nameMsg = $this->nameMsg;
			}
			else {
				$nameMsg = $this->nameMsgErrors;
			}

			$this->msg = $this->success ? $this->msgSuccess : $errors;
		}

		# retorna o resultado da execucao
		echo json_encode(array(
			$this->nameSuccess => $this->success,
			$this->nameRoot => $this->attributes,
			$nameMsg => $this->msg
		));
	}

	public function actionReport()
	{
		ini_set("memory_limit", "1024M");
		if (!isset(Yii::app()->session['id_user'])){
			$info = 'Uset try export PDF without login';
			Util::insertLOG('LOGIN',NULL,$_SERVER['REMOTE_ADDR'],$info);
			exit;
		}

		$orientation = $_POST['orientation'];
		$_POST['columns'] = preg_replace('/idUserusername/', 'id_user', $_POST['columns']);
		$_POST['columns'] = preg_replace('/idPrefixdestination/', 'id', $_POST['columns']);
		$_POST['columns'] = preg_replace('/idPrefixprefix/', 'id_prefix', $_POST['columns']);
		$_POST['columns'] = preg_replace('/idPhonebookt.name/', 'id_phonebook', $_POST['columns']);
		$_POST['columns'] = preg_replace('/idDiddid/', 'id_did', $_POST['columns']);
		$columns = json_decode($_POST['columns'], true);

		$filter = isset($_POST['filter']) ? $this->createCondition(json_decode($_POST['filter'])) : null;
		$fieldGroup = json_decode($_POST['group']);
		$sort = json_decode($_POST['sort']);

		$arraySort = ($sort && $fieldGroup) ? explode(' ', implode(' ', $sort)) : null;
		$dirGroup = $arraySort ? $arraySort[array_search($fieldGroup, $arraySort) + 1] : null;
		$firstSort = $fieldGroup ? $fieldGroup.' '.$dirGroup.',' : null;
		$sort = $sort ? $firstSort.implode(',', $sort) : null;


		$sort = $this->replaceOrder($sort);

		//magnus
		$this->filter = $filter = $this->extraFilter($filter);		
    		//end magnus

		$records = $this->abstractModel->findAll(array(
			'select' => $this->getColumnsFromReport($columns, $fieldGroup),
			'join' => $this->join,
			'condition' => $filter,
			'order' => $sort
		));

		$report                 = new Report();
		$report->orientation    = $orientation;
		$report->title          = $this->titleReport;
		$report->subTitle       = $this->subTitleReport;
		$report->columns        = $columns;
		$report->columnsTable   = $this->getColumnsTable();
		$report->fieldsCurrency = $this->fieldsCurrencyReport;
		$report->fieldsPercent  = $this->fieldsPercentReport;
		$report->fieldsFk       = $this->fieldsFkReport;
		$report->renderer       = $this->rendererReport;
		$report->fieldGroup     = $fieldGroup;
		$report->records        = $this->getAttributesModels($records);
		$report->generate();
	}

	public function actionDestroyReport()
	{
		unlink($this->pathFileReport .'report.pdf');
	}

	public function actionCsv()
	{
		if (!isset(Yii::app()->session['id_user'])){
			$info = 'Uset try export CSV without login';
			Util::insertLOG('LOGIN',NULL,$_SERVER['REMOTE_ADDR'],$info);
			exit;
		}					

		$_GET['columns'] = preg_replace('/idUserusername/', 'id_user', $_GET['columns']);
		$_GET['columns'] = preg_replace('/idPrefixdestination/', 'id', $_GET['columns']);
		$_GET['columns'] = preg_replace('/idPrefixprefix/', 'id_prefix', $_GET['columns']);
		$_GET['columns'] = preg_replace('/idPhonebookt.name/', 'id_phonebook', $_GET['columns']);
		$_GET['columns'] = preg_replace('/idDiddid/', 'id_did', $_GET['columns']);

		$columns = json_decode($_GET['columns'], true);
		$filter = isset($_GET['filter']) ? $this->createCondition(json_decode($_GET['filter'])) : null;
		$fieldGroup = json_decode($_GET['group']);
		$sort = json_decode($_GET['sort']);

		$arraySort = ($sort && $fieldGroup) ? explode(' ', implode(' ', $sort)) : null;
		$dirGroup = $arraySort ? $arraySort[array_search($fieldGroup, $arraySort) + 1] : null;
		$firstSort = $fieldGroup ? $fieldGroup.' '.$dirGroup.',' : null;
		$sort = $sort ? $firstSort.implode(',', $sort) : null;
		$sort = $this->replaceOrder($sort);
		$this->filter = $filter = $this->extraFilter($filter);

		/*
			mkdir /var/www/tmpmagnus
			chown -R asterisk:asterisk /var/www/tmpmagnus
			chmod -R 777 /var/www/tmpmagnus
		*/
		$this->pathFileCsv = '/var/www/tmpmagnus/';

		$this->nameFileCsv = $this->nameFileCsv.time();
		$pathCsv = $this->pathFileCsv.$this->nameFileCsv.'.csv';

		$sql = "SELECT ".$this->getColumnsFromReport($columns)." INTO OUTFILE '$this->pathFileCsv$this->nameFileCsv.csv'
		FIELDS TERMINATED BY '\;' LINES TERMINATED BY '\n'
		FROM " .$this->abstractModel->tableName()." t $this->join WHERE $filter";	
		Yii::app()->db->createCommand($sql)->execute();
		header('Content-type: application/csv');
		header('Content-Disposition: inline; filename="' . $pathCsv . '"');
		header('Content-Transfer-Encoding: binary');
		header('Accept-Ranges: bytes');
		ob_clean();
		flush();
		if (readfile($pathCsv))
		{
		  unlink($pathCsv);
		}
	}

	/**
	 * Exclui um registro da model
	 */
	public function actionDestroy()
	{
		if (!isset(Yii::app()->session['id_user'])){
			$info = 'Uset try export CSV without login';
			Util::insertLOG('LOGIN',NULL,$_SERVER['REMOTE_ADDR'],$info);
			exit;
		}
		ini_set("memory_limit", "1024M");
		# recebe os parametros da exclusao
		$values = $this->getAttributesRequest();
		$namePk = $this->abstractModel->primaryKey();
		$arrayPkAlias = explode('.', $this->abstractModel->primaryKey());
		$ids = array();

		
		if( ( isset($_POST['filter']) && strlen($_POST['filter']) > 0 ))
		{
			$filter = isset($_POST['filter']) ? $_POST['filter'] : null;
			$filter = $filter ? $this->createCondition(json_decode($filter)) : $this->defaultFilter;

			$this->filter = $filter = $this->extraFilter($filter);


			$sql = 'DELETE t FROM '.$table = $this->abstractModel->tableName().' t '. $this->join.' WHERE '.$filter;

			# retorna o resultado da execucao
			try {
				$this->success = Yii::app()->db->createCommand($sql)->execute();
				$errors = true;

				$info = 'Module ' . preg_replace("/pkg_/", '', $this->abstractModel->tableName() ) . '  ' . json_encode($values);
				Util::insertLOG('DELETE',Yii::app()->session['id_user'],$_SERVER['REMOTE_ADDR'],$info);

			}
			catch (Exception $e) {
				$this->success = false;
				$errors = $this->getErrorMySql($e);
			}

			$this->msg = $this->success ? $this->msgSuccess : $errors;

			if($this->success) {
				$nameMsg = $this->nameMsg;
			}
			else {
				$nameMsg = $this->nameMsgErrors;
			}

			# retorna o resultado da execucao
			echo json_encode(array(
				$this->nameSuccess => $this->success,
				$nameMsg => $this->msg
			));			
			exit;
		}
		else
		{
			# Se existe a chave 0, indica que existe um array interno (mais de 1 registro selecionado)
			if(array_key_exists(0, $values))
			{
				# percorre o array para excluir o(s) registro(s)
				foreach($values as $value)
				{
					array_push($ids, $value[$namePk]);
				}
			}
			else
			{
				array_push($ids, $values[$namePk]);
			}
		}		


		


		if (Yii::app()->controller->id == 'user') {
			foreach ($ids as $valueid) {
				if ($valueid == 1) {
					$this->success = false;
					$this->msg = Yii::t('yii','Not allowed delete this user');
				}				
			}
		}

		$strIds = implode(',', $ids);

		if($this->nameModelRelated) {
			$this->destroyRelated($values);
		}

		if(!$this->success) {
			# retorna o resultado da execucao da ação anterior
			echo json_encode(array(
				$this->nameSuccess => $this->success,
				$this->nameMsgErrors => $this->msg
			));

			return;
		}

		try {
			$this->success = $this->abstractModel->deleteAll("$namePk IN($strIds)");
		}
		catch (Exception $e) {
			$this->success = false;
			$errors = $this->getErrorMySql($e);
		}

		$this->msg = $this->success ? $this->msgSuccess : $errors;

		if($this->success) {
			$nameMsg = $this->nameMsg;

			$info = 'Module ' . preg_replace("/pkg_/", '', $this->abstractModel->tableName() ) . '  ' . json_encode($values);
			Util::insertLOG('DELETE',Yii::app()->session['id_user'],$_SERVER['REMOTE_ADDR'],$info);

		}
		else {
			$nameMsg = $this->nameMsgErrors;
		}

		# retorna o resultado da execucao
		echo json_encode(array(
			$this->nameSuccess => $this->success,
			$nameMsg => $this->msg
		));
	}

	/**
	 * Retorna o modelo de dados baseado na chave primaria dada na variavel id.
	 * @param integer a identificacao do modelo a ser carregado
	 * @param object model a ser consultado
	 * @return model encontrado
	 */
	public function loadModel($id, $model)
	{
		if(is_array($id))
		{
			$condition = null;
			foreach ($id as $field => $value)
			{
				$condition .= "$field = $value AND ";
			}

			$condition = substr($condition, 0,-5);
			$resultModel = $model->findAll(array(
				'condition' => $condition
			));

			$resultModel = array_key_exists(0, $resultModel) ? $resultModel[0] : null;
		}
		else
		{
			$resultModel = $model->findByPk((int) $id);
			if ($resultModel === null)
			{
				return $this->msgRecordNotFound;
			}
		}

		return $resultModel;
	}


	public function getAttributesModels($models, $itemsExtras = array())
	{
		$attributes = false;
		$namePk = $this->abstractModel->primaryKey();
		foreach ($models as $key => $item)
		{
		    $attributes[$key] = $item->attributes;

		    if(isset($_SESSION['isClient']) && $_SESSION['isClient'])
			{
				foreach ($this->fieldsInvisibleClient as $field) {
       				unset($attributes[$key][$field]);
				}
			}
			
			if(isset($_SESSION['isAgent']) && $_SESSION['isAgent'])
			{
				foreach ($this->fieldsInvisibleAgent as $field) {
       				unset($attributes[$key][$field]);
				}
			}
			

			if(method_exists($this, 'getAttributesModelsCustom')){
				$custom = $this->getAttributesModelsCustom($attributes, $key, $item);
				if (is_array($custom))				
					foreach ($custom as $customValue)
						$attributes[$key][$customValue['key']] = $customValue['value'];			
			}

		    if(!is_array($namePk) && $this->nameOtherFkRelated && get_class($this->abstractModel) === get_class($item)) {
		        if(count($this->extraFieldsRelated)) {
		            $resultSubRecords = $this->abstractModelRelated->findAll(array(
		                    'select' => implode(',', $this->extraFieldsRelated),
		                    'condition' => $this->nameFkRelated . ' = ' . $attributes[$key][$namePk]
		            ));

		            $subRecords = array();

		            if(count($this->extraValuesOtherRelated)) {
		                $attributesSubRecords = array();

		                foreach($resultSubRecords as $itemModelSubRecords) {
		                    $attributesSubRecords = $itemModelSubRecords->attributes;

		                    foreach($this->extraValuesOtherRelated as $relationSubRecord => $fieldsSubRecord)
		                    {
		                        $arrFieldsSubRecord = explode(',', $fieldsSubRecord);
		                        foreach($arrFieldsSubRecord as $fieldSubRecord)
		                        {
		                            $attributesSubRecords[$relationSubRecord . $fieldSubRecord] = $itemModelSubRecords->$relationSubRecord ? $itemModelSubRecords->$relationSubRecord->$fieldSubRecord : null;
		                        }
		                    }

		                    array_push($subRecords, $attributesSubRecords);
		                }
		            }
		            else {
		                foreach($resultSubRecords as $modelSubRecords) {
		                    array_push($subRecords, $modelSubRecords->attributes);
		                }
		            }
		        }
		        else {
		            $resultSubRecords = $this->abstractModelRelated->findAll(array(
		                'select' => $this->nameOtherFkRelated,
		                'condition' => $this->nameFkRelated . ' = ' . $attributes[$key][$namePk]
		            ));

		            $subRecords = array();
		            foreach($resultSubRecords as $keyModelSubRecords => $modelSubRecords) {
		                array_push($subRecords, (int) $modelSubRecords->attributes[$this->nameOtherFkRelated]);
		            }
		        }

		        $attributes[$key][$this->nameOtherFkRelated] = $subRecords;
		    }

		    foreach($itemsExtras as $relation => $fields)
		    {
		        $arrFields = explode(',', $fields);
		        foreach($arrFields as $field)
		        {
		            $attributes[$key][$relation . $field] = $item->$relation ? $item->$relation->$field : null;
		        }
		    }
		}

		return $attributes;
	}

	/**
	 * Obtem os atributos enviados na requisicao
	 * Verifica se a requisicao e via json ou via POST
	 * @return array dos atributos enviados na requisicao
	 */
	public function getAttributesRequest() {
		$arrPost = array_key_exists($this->nameRoot, $_POST) ? json_decode($_POST[$this->nameRoot], true) : $_POST;
		return $arrPost;
	}

	/**
	 * Obtem os erros vindos do SQL
	 */
	public function getErrorMySql($e) {


		if (isset($e->errorInfo)) {		
			$codeErro = array_key_exists($e->errorInfo[1], $this->mapErrorsMySql) ? $e->errorInfo[1] : 0;
		}else{
			return $e->getMessage();
		}
		
	

		if($codeErro == 1451){
			$error = explode("pkg", $e->getMessage());
			$table = explode("CONSTRAINT", $error[1]);

			$table = preg_replace("/(\_|\`,| )/i", "", $table[0]);
			
			switch ($table) {
				case "refill":
					$erro = 'Refill';
					break;
				case "sip":
					$erro = 'Sipbuddies';
					break;
				case "sipura":
					$erro = 'Sipuras';
					break;
				case "callerid":
					$erro = 'Callerid';
					break;
				case "did":
					$erro = 'Did';
					break;
				case "campaign":
					$erro = 'Campaign';
					break;
				case "campaign_phonebook":
					$erro = 'Campaign';
					break;
				case "phonenumber":
					$erro = 'Phone Number';
					break;
				case "refill_provider":
					$erro = 'Refill Provider';
					break;
				case "trunk":
					$erro = 'Trunk';
					break;
				case "rate":
					$erro = 'Ratecard';
					break;
				case "user":
					$erro = 'username';
					break;			

				default:
					$erro = $table;
					break;
			}

			return $this->mapErrorsMySql[$codeErro] ."<br> ".Yii::t('yii','Please, first delete all related records in the module '). Yii::t('yii',$erro) ."<br><br><a target = '_blank' href='http://en.wikipedia.org/wiki/Foreign_key'>http://en.wikipedia.org/wiki/Foreign_key</a>";
		}
			
		else
			return $this->mapErrorsMySql[$codeErro] . $e->getMessage();
	}

	public function createCondition($filter)
	{
		$condition = '1';

		if(!count($filter))
		{
			return $condition;
		}

		foreach ($filter as $f)
		{
			if(!isset($f->type))
			{
				continue;
			}

			$type = $f->type;
			$field = $f->field;	
			$value = $f->value;			

			$comparison = isset($f->comparison) ? $f->comparison : 'st';
			$comparison = isset($f->data->comparison) ? $f->data->comparison : $comparison;


			switch($type){
	            case 'string':
	            	switch ($comparison) {
	                    case 'st':
	                    	$condition .= " AND $field LIKE '$value%'";
	                    break;
	                    case 'ed':
	                    	$condition .= " AND $field LIKE '%$value'";
	                    break;
	                    case 'ct':
	                    	$condition .= " AND $field LIKE '%$value%'";
	                    break;
	                    case 'eq':
	                    	$condition .= " AND $field LIKE '$value'";
	                    break;
	                }
				break;
				case 'boolean':
					$value = (int) $value;
					$condition .= " AND $field = $value";
				break;
	            case 'numeric':
	                switch ($comparison) {
	                    case 'eq':
	                    	$condition .= " AND $field = $value";
	                    break;
	                    case 'lt':
	                    	$condition .= " AND $field < $value";
	                    break;
	                    case 'gt':
	                    	$condition .= " AND $field > $value";
	                    break;
	                }
	            break;
	            case 'datetime':
					switch ($comparison) {
	                    case 'eq':	
	                    	$valueDateNow= explode(" ", $value);
	     	                $condition .= " AND $field LIKE '".$valueDateNow[0]."%'";
	                    break;
	                    case 'lt':
	                    	$condition .= " AND $field < '$value'";
	                    break;
	                    case 'gt':
	                    	$condition .= " AND $field > '$value'";
	                    break;
	                }
	            break;
	            case 'date':
					switch ($comparison) {
	                    case 'eq':
	                    	$valueDateNow= explode(" ", $value);
	     	                $condition .= " AND $field LIKE '".$valueDateNow[0]."%'";
	                    break;
	                    case 'lt':
	                    	$condition .= " AND $field < '$value'";
	                    break;
	                    case 'gt':
	                    	$condition .= " AND $field > '$value'";
	                    break;
	                }
	            break;
	            case 'list':
	            	if(gettype($value[0]) !== 'integer')
	            	{
	            		foreach ($value as &$v)
	            		{
	            			$v = "'" . $v . "'";
	            		}
	            	}

	            	$value = implode(',', $value);

	            	if(isset($f->tableRelated))
	            	{
	            		$value = "SELECT DISTINCT $f->fieldSubSelect FROM $f->tableRelated WHERE $f->fieldWhere = $value";
	            	}
                    $condition .= " AND $field IN($value)";
	            break;
	            case 'notlist':
	            	if(gettype($value[0]) !== 'integer')
	            	{
	            		foreach ($value as &$v)
	            		{
	            			$v = "'" . $v . "'";
	            		}
	            	}

	            	$value = implode(',', $value);

					if(isset($f->tableRelated))
	            	{
	            		$value = "SELECT DISTINCT $f->fieldSubSelect FROM $f->tableRelated WHERE $f->fieldWhere = $value";
	            	}
                    $condition .= " AND $field NOT IN($value)";
	            break;
	        }
		}

		return $condition;
	}

	public function saveRelated($id, $subRecords, $isUpdate)
	{
		if($isUpdate) {
			try {
				$this->abstractModelRelated->deleteAllByAttributes(array(
					$this->nameFkRelated => $id
				));
			}
			catch (Exception $e) {
				$this->success = false;
				$this->msg = $this->getErrorMySql($e);
			}
		}

		if($this->success && is_array($subRecords)) {
			foreach ($subRecords as $item) {
				$nameFkRelated = $this->nameFkRelated;
				$nameOtherFkRelated = $this->nameOtherFkRelated;
				$instanceModelRelated = new $this->nameModelRelated;

				$instanceModelRelated->$nameFkRelated = $id;

				if(count($this->extraFieldsRelated)) {
					foreach ($this->extraFieldsRelated as $field) {
						$instanceModelRelated->$field = $item[$field];
					}

					$valueOtherFkRelated = $item[$nameOtherFkRelated];
				}
				else {
					$valueOtherFkRelated = $item;
				}

				$instanceModelRelated->$nameOtherFkRelated = $valueOtherFkRelated;

				try {
					$this->success = $instanceModelRelated->save();
				}
				catch (Exception $e) {
					$this->success = false;
					$this->msg = $this->getErrorMySql($e);
				}

				if(!$this->success)
				{
					break;
				}
			}
		}

		if(!$this->success) {
			echo json_encode(array(
				$this->nameSuccess => $this->success,
				$this->nameMsgErrors => $this->msg
			));

			exit;
		}
	}

	public function destroyRelated($values)
	{
		$namePk = $this->abstractModel->primaryKey();
		if(array_key_exists(0, $values))
		{
			foreach($values as $value)
			{
				$id = $value[$namePk];

				try {
					$this->abstractModelRelated->deleteAllByAttributes(array(
						$this->nameFkRelated => $id
					));
				}
				catch (Exception $e) {
					$this->success = false;
					$this->msg = $this->getErrorMySql($e);
				}

				if(!$this->success)
				{
					break;
				}
			}
		}
		else
		{
			$id = $values[$namePk];

			try {
				$this->abstractModelRelated->deleteAllByAttributes(array(
					$this->nameFkRelated => $id
				));
			}
			catch (Exception $e) {
				$this->success = false;
				$this->msg = $this->getErrorMySql($e);
			}
		}
	}

	public function getColumnsTable() {
		$command = Yii::app()->db->createCommand('SHOW COLUMNS FROM ' . $this->abstractModel->tableName());
		return $command->queryAll();
	}

	public function getColumnsFromReport($columns, $fieldGroup = null){
		$arrayColumns = array();

		foreach ($columns as $column) {
			$fieldName = $column['dataIndex'];
			if(is_array($this->fieldsFkReport) && array_key_exists($fieldName, $this->fieldsFkReport))
			{
				$fk = $this->fieldsFkReport[$fieldName];
				$table = $fk['table'];
				$pk = $fk['pk'];
				$fieldReport = $fk['fieldReport'];
				if(($fieldName == 'id' && $fieldReport == 'destination')  || ($fieldName == 'idPrefixprefix' && $fieldReport == 'destination') )
				{
					//altera as colunas para poder pegar o destino das tarifas
					$subSelect = "(SELECT $fieldReport FROM $table WHERE $table.$pk = t.id_prefix) $fieldName";
				}
				else
				{
					$subSelect = "(SELECT $fieldReport FROM $table WHERE $table.$pk = t.$fieldName) $fieldName";
				}


				if($fieldName === $fieldGroup)
				{
					array_unshift($arrayColumns, $subSelect);
				}
				else
				{
					array_push($arrayColumns, $subSelect);
				}
			}
			else
			{
				if($fieldName === $fieldGroup)
				{
					array_unshift($arrayColumns, $fieldName);
				}
				else
				{
					array_push($arrayColumns, $fieldName);
				}
			}
		}
		$patterns = array('/credit/', '/description/', '/,id_user/', '/^id_user/', '/^name/');
		$arrayReplace = array('t.credit', 't.description', ',t.id_user', 't.id_user', 't.name');

		
		$arrayColumns = preg_replace($patterns, $arrayReplace, $arrayColumns);

		$arrayColumns = $this->columnsReplace($arrayColumns);

		$columns = implode(',', $arrayColumns);
	
		return $columns;
	}

	public function columnsReplace($arrayColumns)
	{
		return $arrayColumns;
	}

	public function filterReplace($filter)
	{
		//activated is in where clause is ambiguous
		$filter = preg_replace('/activated/', 't.activated', $filter);
		$filter = preg_replace('/status/', 't.status', $filter);
		$filter = preg_replace('/secondusedreal/', 't.secondusedreal', $filter);
		$filter = preg_replace('/creationdate/', 't.creationdate', $filter);
		$filter = preg_replace('/id_package_offer/', 't.id_package_offer', $filter);
		$filter = preg_replace('/id_trunk/', 't.id_trunk', $filter);
		$filter = preg_replace('/id_did/', 't.id_did', $filter);
		$filter = preg_replace('/credit/', 't.credit', $filter);


		//adiciona um join para busca na tabela relacionada
		if(preg_match('/idPrefixprefix/', $filter)){
			if (!preg_match("/JOIN pkg_prefix/", $this->join))
				$this->join .= ' LEFT JOIN pkg_prefix b ON t.id_prefix = b.id';
			$filter = preg_replace('/idPrefixprefix/', "b.prefix", $filter);
		}
		if(preg_match('/idPrefixdestination/', $filter)){
			if (!preg_match("/JOIN pkg_prefix/", $this->join)) 
				$this->join .= ' LEFT JOIN pkg_prefix b ON t.id_prefix = b.id';
			$filter = preg_replace('/idPrefixdestination/', "b.destination", $filter);
		}
		if(preg_match('/idUserusername/', $filter)){
			if (!preg_match("/JOIN pkg_user/", $this->join)) 
				$this->join .= ' LEFT JOIN pkg_user b ON t.id_user = b.id';
			$filter = preg_replace('/idUserusername/', "b.username", $filter);
		}
		if(preg_match('/idDiddid/', $filter)){
			if (!preg_match("/JOIN pkg_did_user/", $this->join)) 
				$this->join .= ' LEFT JOIN pkg_did b ON t.id_did = b.id';
			$filter = preg_replace('/idDiddid/', "b.did", $filter);
		}
		if(preg_match('/idPhonebookname/', $filter)){
			if (!preg_match("/JOIN pkg_phonebook/", $this->join))
				$this->join .= ' LEFT JOIN pkg_phonebook g ON t.phonebook = g.id';
			$filter = preg_replace('/idPhonebookname/', "g.name", $filter);
		}

		return $filter;
	}

	public function replaceOrder($order)
	{	
		if(preg_match('/idPrefixdestination/', $order)){
			if (!preg_match("/JOIN pkg_prefix/", $this->join)) 
				$this->join .= ' LEFT JOIN pkg_prefix b ON t.id_prefix = b.id';
		}
		//ajustar para ordenar corretamente no modulo rates
		$order = preg_replace("/idPrefixprefix/", 't.id_prefix', $order);
		$order = preg_replace("/idPrefixdestination/", 'b.destination', $order);
		$order = preg_replace("/idPhonebookname/", 'b.name', $order);
		$order = preg_replace("/idUserusername/", 't.id_user', $order);
		$order = preg_replace("/idDiddid/", 't.id_did', $order);
		return $order;
	}

	public function extraFilter ($filter)
	{
		$filter = isset($this->filter) ? $filter.$this->filter : $filter;

		$filter = $filter . ' AND ' .$this->defaultFilter;
		$filter = $this->filterReplace($filter);

		if(Yii::app()->getSession()->get('user_type')  > 1 && $this->filterByUser)
		{
			$filter .= ' AND '. $this->defaultFilterByUser . ' = '.Yii::app()->getSession()->get('id_user');
		}
		return $filter;
	}

	public function recordsExtraSum($select, $join , $filter, $group, $records)
	{
		return array();
	}

	public function actionLogin() {
		$user      = $_POST['user'];
		$password  = $_POST['pass'];
		$condition = "(username COLLATE utf8_bin LIKE '$user' OR username LIKE '$user' OR email COLLATE utf8_bin LIKE '$user')";

		$sql       = "SELECT pkg_user.id, username, id_group, id_plan, pkg_user.firstname, pkg_user.lastname , id_user_type, id_user, loginkey, active, password FROM pkg_user JOIN pkg_group_user ON id_group = pkg_group_user.id  WHERE $condition";
		$result    = Yii::app()->db->createCommand($sql)->queryAll();

		if(!isset($result[0]['username']) || sha1($result[0]['password']) != $password){
			Yii::app()->session['logged'] = false;
			echo json_encode(array(
				'success' => false,
				'msg' => 'Usuário e/ou login incorretos'
			));
			exit;
		}			
		
		if(!$result) {
			Yii::app()->session['logged'] = false;
			echo json_encode(array(
				'success' => false,
				'msg' => 'Usuário e/ou login incorretos'
			));
			exit;
		}

		if($result[0]['active'] == 0){
			Yii::app()->session['logged'] = false;
			echo json_encode(array(
				'success' => false,
				'msg' => 'Username is disabled'
			));
			exit;
		}
		$user                                = $result[0];

		Yii::app()->session['isAdmin']       = $user['id_user_type'] == 1 ? true : false;
		Yii::app()->session['isAgent']       = $user['id_user_type'] == 2 ? true : false;
		Yii::app()->session['isClient']      = $user['id_user_type'] == 3 ? true : false;
		Yii::app()->session['isClientAgent'] = false;
		Yii::app()->session['id_plan']       = $user['id_plan'];
		Yii::app()->session['credit']        = isset($user['credit']) ? $user['credit'] : 0;
		Yii::app()->session['username']      = $user['username'];
		Yii::app()->session['logged']        = true;
		Yii::app()->session['id_user']       = $user['id'];
		Yii::app()->session['id_agent']       = is_null($user['id_user']) ? 1 : $user['id_user'];
		Yii::app()->session['name_user']     = $user['firstname']. ' ' . $user['lastname'];
		Yii::app()->session['id_group']      = $user['id_group'];
		Yii::app()->session['user_type']     = $user['id_user_type'];

		$sql                                 = "SELECT m.id, action, show_menu, text, module, icon_cls, m.id_module FROM pkg_group_module gm INNER JOIN pkg_module m ON gm.id_module = m.id WHERE id_group =". $user['id_group'];
		$result                              = Yii::app()->db->createCommand($sql)->queryAll();
		Yii::app()->session['action']        = $this->getActions($result);
		Yii::app()->session['menu'] 		 = $this->getMenu($result);

		$sql                                 = "SELECT config_value FROM pkg_configuration WHERE config_key LIKE 'base_currency' ";
		$resultCurrency                      = Yii::app()->db->createCommand($sql)->queryAll();
		Yii::app()->session['currency']      = $resultCurrency[0]['config_value'];
	}

	private function getActions($modules) {
		$actions = array();

		foreach ($modules as $key => $value) {
			if(!empty($value['action'])) {
				$actions[$value['module']] = $value['action'];
			}
		}

		return $actions;
	}

	private function getMenu($modules) {
		$menu = array();

		foreach ($modules as $value) {
			if(!$value['show_menu']) {
				continue;
			}

			if(empty($value['id_module'])) {
				array_push($menu, array(
					'teste' => 'teste',
					'text' => preg_replace("/ Module/", "", $value['text']),
					'iconCls' => $value['icon_cls'],
					'rows' => $this->getSubMenu($modules, $value['id'])
				));
			}
		}

		return $menu;
	}

	private function getSubMenu($modules, $idOwner) {
		$subModulesOwner = Util::arrayFindByProperty($modules, 'id_module', $idOwner);
		$subMenu = array();

		foreach ($subModulesOwner as $value) {

			if(!$value['show_menu']) {
				continue;
			}

			if(!empty($value['module'])) {
				array_push($subMenu, array(
					'text' => $value['text'],
					'iconCls' => $value['icon_cls'],
					'module' => $value['module'],
					'action'=> $value['action'],
					'leaf' => true
				));
			}
			else {
				array_push($subMenu, array(
					'text' => $value['text'],
					'iconCls' => $value['icon_cls'],
					'rows' => $this->getSubMenu($modules, $value['id'])
				));
			}
		}

		return $subMenu;
	}

	public function actionSoftphone()
	{
	
		if (isset($_GET['l'])) {
			$data = explode('|', $_GET['l']);
			$user= $data[0];
			$pass = $data[1];

			$sql = "SELECT 'username',firstname, lastname, credit,
			(SELECT config_value FROM pkg_configuration WHERE config_key LIKE 'base_currency' ) AS currency, secret

			FROM pkg_sip join pkg_user ON pkg_sip.id_user = pkg_user.id WHERE pkg_sip.name = '".$user."'" ;

			$result    = Yii::app()->db->createCommand($sql)->queryAll();


			if( !isset($result[0]['username']) )
				$error = 'username';
			else if( isset($result[0]['secret']) && strtoupper(MD5($result[0]['secret'])) != strtoupper($pass))			
				$error = 'password';
			
			unset($result[0]['secret']);
			
			if (isset($error)) {
				$result[0]['error_'.$error] = true;

				unset($result[0]['username']);
				unset($result[0]['credit']);
				unset($result[0]['firstname']);
				unset($result[0]['lastname']);
				unset($result[0]['currency']);

				$result = json_encode(array(
					$this->nameRoot => $result,
					$this->nameCount => 1,
					$this->nameSum => ''
				));
				$result = json_decode($result,true);
				echo '<pre>';
				print_r($result);
				exit;
			}

			

			$result[0]['credit'] = number_format($result[0]['credit'],2);

			if(count($result) == 0){
				echo 'false';
				exit;
			}

			$result[0]['version'] = '1.0.7';

			$result = json_encode(array(
				$this->nameRoot => $result,
				$this->nameCount => 1,
				$this->nameSum => ''
			));
			$result = json_decode($result,true);
			echo '<pre>';
			print_r($result);
		}
	}
	
	public function number_translation($translation,$destination)
    	{
		#match / replace / if match length 
		#0/54,4/543424/7,15/549342/9

		//$translation = "0/54,*/5511/8,15/549342/9";   
    
		$regexs = preg_split("/,/", $translation);

		foreach ($regexs as $key => $regex) {

		$regra = preg_split( '/\//', $regex );
		$grab = isset($regra[0]) ? $regra[0] : '';
		$replace = isset($regra[1]) ? $regra[1] : '';
		$digit = isset($regra[2]) ? $regra[2] : '';		    

		$number_prefix = substr($destination,0,strlen($grab));

		if ($grab == '*' && strlen($destination) == $digit) {
			$destination = $replace.$destination;
		}
		else if (strlen($destination) == $digit && $number_prefix == $grab) {
			$destination = $replace.substr($destination,strlen($grab));
		}
		elseif ($number_prefix == $grab)
		{
			$destination = $replace.substr($destination,strlen($grab));
		}      

  
        	}
 		return $destination;
    	}


	public function gerarSenha ($tamanho, $maiuscula, $minuscula, $numeros, $codigos)
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

}