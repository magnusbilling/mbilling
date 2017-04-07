<?php
/**
 * Acoes do modulo "Rate".
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
 * 30/07/2012
 */

class RateCallshopController extends Controller
{
	public $attributeOrder = 't.id';

	public function init()
	{
		$this->instanceModel = new RateCallshop;
		$this->abstractModel = RateCallshop::model();
		$this->titleReport   = Yii::t('yii','Ratecard'). ' '. Yii::t('yii','CallShop');
		parent::init();
	}

	public function actionSave()
	{
		$values = $this->getAttributesRequest();
		if (Yii::app()->getSession()->get('isAdmin') && ( isset($values['id']) &&  $values['id'] == 0 )) 
		{
			echo json_encode(array(
				$this->nameSuccess => false,
				$this->nameMsg => $this->msgError
			));
			exit;
		}
		parent::actionSave();
	}

	
	public function actionImportFromCsv()
	{

		$module = $this->instanceModel->getModule();

		if(!AccessManager::getInstance($module)->canCreate()||
		   !AccessManager::getInstance($module)->canUpdate()){
				header('HTTP/1.0 401 Unauthorized');
	 			die("Access denied to save in module: $module");
		}

		$values = $this->getAttributesRequest();

		$id_user = Yii::app()->getSession()->get('id_user');

		$handle = fopen($_FILES['file']['tmp_name'], "r");
		$sqlPrefix = array();
		$sqlRate = array();
		while($row = fgetcsv($handle)) 
		{
			if(isset($row[1]))
			{
				$prefix = $row[0];
				$destination = ($row[1] == '') ? 'ROC' : trim($row[1]);
				$price = $row[2] == '' ? '0.0000' : $row[2];

				$sqlRate[] = "($id_user, $prefix, '$destination' , $price)";
			}
		}

		$sqlRate = 'INSERT INTO pkg_rate_callshop (id_user, dialprefix, destination, buyrate) VALUES '.implode(',', $sqlRate).';';
		$this->success = Yii::app()->db->createCommand($sqlRate)->execute() !== false;

		fclose($handle);
		$this->msg = $this->success ? $this->msgSuccess : $errors;

		echo json_encode(array(
			$this->nameSuccess => $this->success,
			$this->nameMsg => $this->msg
		));
	}
}