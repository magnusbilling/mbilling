<?php
/**
 * Acoes do modulo "Trunk".
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
 * 23/06/2012
 */

class TrunkController extends Controller
{
	public $extraValues    = array('idProvider' => 'provider_name', 'failoverTrunk' => 'trunkcode');
	public $nameFkRelated  = 'failover_trunk';
	public $attributeOrder = 'id';
	public $fieldsFkReport = array(
		'id_provider' => array(
			'table' => 'pkg_provider',
			'pk' => 'id',
			'fieldReport' => 'provider_name'
		),
		'failover_trunk' => array(
			'table' => 'pkg_trunk',
			'pk' => 'id',
			'fieldReport' => 'trunkcode'
		)
	);
	public function init()
	{
		$this->instanceModel = new Trunk;
		$this->abstractModel = Trunk::model();
		$this->titleReport   = Yii::t('yii','Trunk');

		parent::init();
	}
}