<?php
/**
 * Acoes do modulo "GAuthenticator".
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
 * 19/04/2016
 */

class GAuthenticatorController extends Controller
{
	public $attributeOrder        = 't.googleAuthenticator_enable DESC';

	public function init()
	{
		$this->instanceModel = new GAuthenticator;
		$this->abstractModel = GAuthenticator::model();
		$this->titleReport   = Yii::t('yii','GAuthenticator');
		parent::init();
	}

}