<?php
/**
 * Acoes do modulo "Prefix".
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
 * 01/08/2012
 */

class PrefixController extends Controller
{
	public $attributeOrder = 'id';
	public $filterByUser   = false;

	public function init()
	{
		$this->instanceModel = new Prefix;
		$this->abstractModel = Prefix::model();
		$this->titleReport   = Yii::t('yii','Prefix');

		parent::init();
	}
}