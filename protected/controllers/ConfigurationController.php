<?php
/**
 * Acoes do modulo "Configuration".
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

class ConfigurationController extends Controller
{
	public $attributeOrder = 'config_group_title DESC';
	public $defaultFilter  = 'status =1';
	public $filterByUser = false;

	public function init()
	{

		$this->instanceModel = new Configuration;
		$this->abstractModel = Configuration::model();
		$this->titleReport   = Yii::t('yii','Config');
		parent::init();
	}

	public function actionLayout()
	{

		$model = Configuration::model()->findByAttributes(array('config_key'=> 'layout'));
		$model->status = $_POST['status'];
		if($_POST['status'] == 0){ 
			$model->config_value = 0;
		}
		$model->save();

		echo json_encode(array(
                $this->nameSuccess => true,
                $this->nameMsg => '',
            ));
	}

	public function actionTheme()
	{
		$model = Configuration::model()->findByAttributes(array('config_key'=> $_POST['field']));
		$model->config_value = $_POST['value'];
		$model->save();
		
		echo json_encode(array(
                $this->nameSuccess => true,
                $this->nameMsg => '',
            ));
	}

	public function actionSetData()
	{
		$model = Configuration::model()->findByAttributes(array('config_key'=> 'admin_email'));
		$model->config_value = $_POST['email'];
		$model->save();

		$model = Configuration::model()->findByAttributes(array('config_key'=> 'base_country'));
		$model->config_value = $_POST['countryiso'];
		$model->save();

		$model = Configuration::model()->findByAttributes(array('config_key'=> 'base_currency'));
		$model->config_value = $_POST['currency'];
		$model->save();

		Yii::app()->session['base_country'] = $_POST['countryiso'];
		Yii::app()->session['email'] =$_POST['email'];
		Yii::app()->session['currency'] =$_POST['currency'];
		
		echo json_encode(array(
          	$this->nameSuccess => true,
          	$this->nameMsg => 'Success',
       	));
	}
}