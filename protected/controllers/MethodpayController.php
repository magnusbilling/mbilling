<?php
/**
 * Acoes do modulo "Methodpay".
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
 * 04/07/2012
 */

class MethodpayController extends Controller
{
	public $attributeOrder = 'id';
	public $extraValues    = array('idUser' => 'username');

	public $fieldsFkReport = array(
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		)
	);
	
	public function init()
	{
		$this->instanceModel = new Methodpay;
		$this->abstractModel = Methodpay::model();
		$this->titleReport   = Yii::t('yii','Payment Methods');
		parent::init();
	}

	public function actionRead2()
	{

		$config = LoadConfig::getConfig();
		$values = explode(":", $config['global']['purchase_amount']);

		$amount = array();
		
		foreach ($values as $key => $value) {


			array_push($amount, array(
				'id' => $key + 1,
				'amount' => $value)
				);
		}

		echo json_encode(array(
			$this->nameRoot => $amount,
			$this->nameCount => 10,
			$this->nameSum => array()
		));
		
	}

	public function extraFilter ($filter)
	{
		$filter = isset($this->filter) ? $filter.$this->filter : $filter;

		$filter = $filter . ' AND ' .$this->defaultFilter;
		$filter = $this->filterReplace($filter);

		if(Yii::app()->getSession()->get('user_type')  > 1 && $this->filterByUser)
		{
			if(Yii::app()->getSession()->get('isAgent'))
				$filter .= ' AND t.id_user = 1 AND active = 1';
			else if(Yii::app()->getSession()->get('isClient')){

				$filter .= ' AND t.id_user = '.Yii::app()->getSession()->get('id_agent').' AND active = 1';
			}
			
		}
		return $filter;
	}
}