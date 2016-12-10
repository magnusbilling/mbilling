<?php
/**
 * Acoes do modulo "Sms".
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

class SmsController extends Controller
{
	public $attributeOrder     = 'date DESC';
	public $extraValues        = array('idUser' => 'username');



	public function init()
	{
		$this->instanceModel = new Sms;
		$this->abstractModel = Sms::model();
		$this->titleReport   = 'Sms';
		parent::init();
	}

	public function actionSave()
	{
		$values    = $this->getAttributesRequest();
		
		if(Yii::app()->session['isClient']) {
			$values['id_user'] = Yii::app()->session['id_user'];
		}

		$modelUser = User::model()->findByPk($values['id_user']);
		include_once('protected/controllers/SmsSendController.php');
		$sms = SmsSendController::actionIndex($modelUser->username, $modelUser->password, $values['telephone'], $values['sms']);
	}
}