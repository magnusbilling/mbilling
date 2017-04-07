<?php
/**
 * Acoes do modulo "Call".
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
 * 19/09/2012
 */

class CampaignRestrictPhoneController extends Controller
{
	public $attributeOrder        = 't.id';


	public function init()
	{
		$this->instanceModel = new CampaignRestrictPhone;
		$this->abstractModel = CampaignRestrictPhone::model();
		$this->titleReport   = Yii::t('yii','Campaign Restrict Phone');
		parent::init();
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

		ini_set("memory_limit", "1500M");
		ini_set("upload_max_filesize", "3M");
		ini_set("max_execution_time", "120");

		$handle = fopen($_FILES['file']['tmp_name'], "r");
		$sqlPrefix = array();
		while (($row = fgetcsv($handle, 32768, ',')) !== FALSE)
		{
			if(isset($row[0]) && is_numeric($row[0]))
				$sqlRate[] = "(".$row[0].")";
		}
		fclose($handle);


		if ($this->success) {
			$sqlRate = 'INSERT INTO pkg_campaign_restrict_phone (number) VALUES '.implode(',', $sqlRate).';';
			try {
				$this->success = Yii::app()->db->createCommand($sqlRate)->execute() !== false;
				$this->msg = $this->msgSuccessLot;
			} catch (Exception $e) {
				$this->success = false;
				$this->msg = $this->getErrorMySql($e);
			}
		}

		echo json_encode(array(
			$this->nameSuccess => $this->success,
			$this->nameMsg => $this->msg
		));
	}

	public function actionDeleteDuplicados()
	{
		$sql = "ALTER TABLE pkg_campaign_restrict_phone DROP INDEX number";
		Yii::app()->db->createCommand($sql)->execute();

		$sql = "ALTER IGNORE TABLE  `pkg_campaign_restrict_phone` ADD UNIQUE (`number`)";
		Yii::app()->db->createCommand($sql)->execute();

		$sql = "ALTER TABLE pkg_campaign_restrict_phone DROP INDEX number";
		Yii::app()->db->createCommand($sql)->execute();

		//adiciona index na coluna numero
		$sql = "ALTER TABLE  pkg_campaign_restrict_phone ADD INDEX (  `number` )";
		Yii::app()->db->createCommand($sql)->execute();
		
		echo json_encode(array(
			$this->nameSuccess => true,
			$this->nameMsg => 'Números duplicado deletados com sucesso'
		));

	}

}