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

class LogUsersController extends Controller
{
	public $attributeOrder        = 't.date DESC';
	public $extraValues           = array('idUser' => 'username');

	public $fieldsFkReport = array(
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		)
	);
	public function init()
	{
		$this->instanceModel = new LogUsers;
		$this->abstractModel = LogUsers::model();
		$this->titleReport   = Yii::t('yii','LogUsers');
		parent::init();
	}

	public function actionDestroy()
	{
	     echo json_encode(array(
				$this->nameSuccess => false,
				$this->nameMsgErrors => Yii::t('yii','Not allowed delete in this module')
			));
	     exit;
	}

}