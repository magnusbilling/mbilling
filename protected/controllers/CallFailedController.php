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
 * 17/08/2012
 */

class CallFailedController extends Controller
{
	public $attributeOrder     = 'starttime DESC';
	public $extraValues        = array(
		'idUser' => 'username',
		'idPlan' => 'name',
		'idTrunk' => 'trunkcode',
		'idPrefix' => 'destination'
		);


	public $fieldsInvisibleClient = array(
		'username',
		'trunk',		
		'id_user',
		'provider_name'
	);

	public $fieldsInvisibleAgent = array(
		'trunk',		
		'id_user',
		'provider_name'
	);

	public $fieldsFkReport = array(
        	'id_user' => array(
            	'table' => 'pkg_user',
            	'pk' => 'id',
            	'fieldReport' => "username "
        ),
        	'id_trunk' => array(
            	'table' => 'pkg_trunk',
            	'pk' => 'id',
            	'fieldReport' => 'trunkcode'
        ),
        	'id_prefix' => array(
            	'table' => 'pkg_prefix',
            	'pk' => 'id',
            	'fieldReport' => 'destination'
        ),
		'id' => array(
			'table' => 'pkg_prefix',
			'pk' => 'id',
			'fieldReport' => 'destination'
		)
    );

	public function init()
	{
		ini_set('memory_limit', '-1');
		$this->instanceModel = new CallFailed;
		$this->abstractModel = CallFailed::model();
		$this->titleReport   = Yii::t('yii','Call Failed');	

		parent::init();
		
		if (!Yii::app()->getSession()->get('isAdmin'))
		{
			$this->join = 'JOIN pkg_user b ON t.id_user = b.id';
			$this->extraValues        = array(
				'idUser' => 'username',
				'idPlan' => 'name',
				'idPrefix' => 'destination',
				);
		}
	}

	public function extraFilter ($filter)
	{
		$filter = isset($this->filter) ? $filter.$this->filter : $filter;

		$filter = $filter . ' AND ' .$this->defaultFilter;
		$filter = $this->filterReplace($filter);

		if (Yii::app()->getSession()->get('isAgent'))
			$filter .= ' AND b.id_user = '.Yii::app()->getSession()->get('id_user');

		else if(Yii::app()->getSession()->get('isClient'))
			$filter .= ' AND t.id_user = '.Yii::app()->getSession()->get('id_user');
		return $filter;
	}

	/**
	 * Cria/Atualiza um registro da model
	 */
	public function actionSave()
	{
		$values = $this->getAttributesRequest();

		if(isset($values['id']) && !$values['id'])
		{
			echo json_encode(array(
				$this->nameSuccess => false,
				$this->nameRoot => 'error',
				$this->nameMsg => 'Operation no allow',
			));
			exit;
		}
		parent::actionSave();
	}

	public function getAttributesRequest() {
		$arrPost = array_key_exists($this->nameRoot, $_POST) ? json_decode($_POST[$this->nameRoot], true) : $_POST;
		//retira capos antes de salvar
		unset($arrPost['starttime']);
		unset($arrPost['callerid']);
		unset($arrPost['id_prefix']);
		unset($arrPost['username']);
		unset($arrPost['trunk']);
		unset($arrPost['terminatecauseid']);
		unset($arrPost['calltype']);
		unset($arrPost['idPrefixdestination']);

		return $arrPost;
	}




}