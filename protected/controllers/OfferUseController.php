<?php
/**
 * Acoes do modulo "OfferUse".
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
 * 28/10/2012
 */

class OfferUseController extends Controller
{
	public $attributeOrder        = 't.id';
	public $extraValues           = array('idOffer' => 'label', 'idUser' => 'username');

	public $fieldsFkReport = array(
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		),
		'id_offer' => array(
			'table' => 'pkg_offer',
			'pk' => 'id',
			'fieldReport' => 'label'
		)
	);
	
	public function init()
	{
		$this->instanceModel = new OfferUse;
		$this->abstractModel = OfferUse::model();
		$this->titleReport   = Yii::t('yii','Offer Use');
		parent::init();
	}

}