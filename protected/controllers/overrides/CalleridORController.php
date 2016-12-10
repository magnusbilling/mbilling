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
Yii::import('application.controllers.CalleridController');
class CalleridORController extends CalleridController
{

	public function actionReport()
	{
		
		$this->titleReport   = Yii::t('yii','Boleto teste');
		parent::actionReport();	
	}

	public function actionRead()
	{
		parent::actionRead();
	}
	public function actionSave()
	{
		parent::actionSave();
	}

}