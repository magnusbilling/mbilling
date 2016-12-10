<?php
/**
 * Acoes do modulo "TemplateMail".
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
 * 10/08/2012
 * https://www.google.com/settings/security/lesssecureapps
 */

class TemplateMailController extends Controller
{
	public $attributeOrder = 't.id';


	public function init()
	{
		$config = LoadConfig::getConfig();
		$this->instanceModel = new TemplateMail;
		$this->abstractModel = TemplateMail::model();
		$this->titleReport   = Yii::t('yii','Mails');

		
		parent::init();

	}

	public function actionRead()
	{
		if(Yii::app()->getSession()->get('isAdmin'))
			$this->filter =  ' AND id_user = 1';
		
		parent::actionRead();
	}

}