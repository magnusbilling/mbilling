<?php
/**
 * Acoes do modulo "Plan".
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
 * 27/07/2012
 */

class PlanController extends Controller
{
	public $attributeOrder     = 'id';
	public $extraValues        = array('idUser' => 'username');
	

	public function init()
	{
		$this->instanceModel        = new Plan;
		$this->abstractModel        = Plan::model();
		$this->titleReport          = Yii::t('yii','Plan');
		parent::init();
	}

	public function actionDestroy()
	{
		# recebe os parametros da exclusao
		$values = $this->getAttributesRequest();

		if(Yii::app()->getSession()->get('isAgent'))	{
			$sql = "DELETE FROM pkg_rate_agent WHERE id_plan = '".$values['id']."'";
			Yii::app()->db->createCommand($sql)->execute();
		}		
			

		parent::actionDestroy();
	}

	public function actionRead()
	{
		if(Yii::app()->getSession()->get('isAdmin'))
			$this->filter =  ' AND id_user = 1';
		
		parent::actionRead();
	}

}