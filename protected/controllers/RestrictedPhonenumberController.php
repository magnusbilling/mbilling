<?php
/**
 * Acoes do modulo "RestrictedPhonenumber".
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

class RestrictedPhonenumberController extends Controller
{
	public $attributeOrder     = 'id';
	public $extraValues        = array('idUser' => 'username');
	public $filterByUser        = true;
    	public $defaultFilterByUser = 'b.id_user';
    	public $join                = 'JOIN pkg_user b ON t.id_user = b.id';
    	
	public $fieldsFkReport = array(
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		)
	);

	public function init()
	{
		$this->instanceModel = new RestrictedPhonenumber;
		$this->abstractModel = RestrictedPhonenumber::model();
		$this->titleReport   = Yii::t('yii','Config');
		parent::init();
	}
}