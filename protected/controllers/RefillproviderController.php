<?php
/**
 * Acoes do modulo "Refillprovider".
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
 * 18/07/2012
 */

class RefillproviderController extends Controller
{
	public $attributeOrder = 'id';
	public $extraValues    = array('idProvider' => 'provider_name');
	public $filterByUser   = false;
	public $fieldsFkReport = array(
        'id_provider' => array(
            'table' => 'pkg_provider',
            'pk' => 'id',
            'fieldReport' => 'provider_name'
        )
    );

	public function init()
	{
		$this->instanceModel = new Refillprovider;
		$this->abstractModel = Refillprovider::model();
		$this->titleReport   = Yii::t('yii','Refill Provider');

		parent::init();
	}

}