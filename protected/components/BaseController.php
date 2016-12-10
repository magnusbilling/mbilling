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

class BaseController extends CController
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
		SqlInject::sanitize($_REQUEST);
		$config = LoadConfig::getConfig();	
		if(!isset($_SESSION['language']))
		{					
			$_SESSION['language'] = $config['global']['base_language'];
			Yii::app()->language = Yii::app()->sourceLanguage = isset($_SESSION['language']) ? $_SESSION['language']  : Yii::app()->language;
		}
	}
}