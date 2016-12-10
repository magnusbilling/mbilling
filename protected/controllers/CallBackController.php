<?php
/**
 * Acoes do modulo "CallBack".
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
 * 19/09/2012
 */

class CallBackController extends Controller
{
	public $attributeOrder        = 't.id';
	public $fieldsInvisibleClient = array(
		'variable'
		);

	public function init()
	{
		$this->instanceModel = new CallBack;
		$this->abstractModel = CallBack::model();
		$this->titleReport   = Yii::t('yii','CallBack');
		parent::init();
	}


	public function getAttributesModels($models, $itemsExtras = array())
	{
		$attributes = false;
		foreach ($models as $key => $item)
		{
			$attributes[$key] = $item->attributes;

			if(Yii::app()->getSession()->get('isClient'))//retira no nome do tronco para clientes
			{
				$channel = explode("/",$attributes[$key]['channel']);
				unset($attributes[$key]['channel']);
				$attributes[$key]['channel'] = isset($channel[2]) ? $channel[2] : $channel[1];
			}


			foreach($itemsExtras as $relation => $fields)
			{
				$arrFields = explode(',', $fields);
				foreach($arrFields as $field)
				{
					$attributes[$key][$relation . $field] = $item->$relation->$field; 
				}
			}
		}

		return $attributes;
	}

}