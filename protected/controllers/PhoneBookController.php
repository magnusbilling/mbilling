<?php
/**
 * Acoes do modulo "PhoneBook".
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

class PhoneBookController extends Controller
{
	public $attributeOrder        = 't.name ASC';
	public $extraValues           = array('idUser' => 'username');
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
	public $fieldsInvisibleClient = array(
		'id_user',
		'idCardusername'
		);

	public function init()
	{
		$this->instanceModel = new PhoneBook;
		$this->abstractModel = PhoneBook::model();
		$this->titleReport   = Yii::t('yii','Phone Book');		
		
		parent::init();
	}

	public function extraFilter ($filter)
	{
		$filter = isset($this->filter) ? $filter.$this->filter : $filter;

		$filter = $filter . ' AND ' .$this->defaultFilter;
		$filter = $this->filterReplace($filter);

		 if(Yii::app()->getSession()->get('user_type')  > 1 && $this->filterByUser)
        {
            $filter .= ' AND ('. $this->defaultFilterByUser . ' = '.Yii::app()->getSession()->get('id_user');
            $filter .= ' OR t.id_user = '.Yii::app()->getSession()->get('id_user').')';
        }

		return $filter;
	}
	

	public function actionRead()
	{
		$filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
		$filter = $this->createCondition(json_decode($filter));
		$this->filter =  !preg_match("/status/", $filter) ? ' AND status = 1' : '';
		parent::actionRead();
	}

}