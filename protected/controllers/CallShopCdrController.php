<?php
/**
 * Acoes do modulo "CallShopCdr".
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

class CallShopCdrController extends Controller
{
	public $attributeOrder     = 't.date DESC';
	public $select = 't.id, t.sessionid, t.id_user, t.id_prefix, t.status, buycost, price, calledstation, t.date, sessiontime, cabina, (((price - buycost) / buycost) * 100) markup';
	public $extraValues           = array('idUser' => 'username', 'idPrefix' => 'destination');
	public $defaultFilter  		= 'c.callshop = 1';
	public $defaultFilterByUser   = 't.id_user';
	public $join                  = 'INNER JOIN pkg_user c ON t.id_user = c.id';
	public $config;
	public $fieldsFkReport = array(
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		),
		'id_prefix' => array(
			'table' => 'pkg_prefix',
			'pk' => 'id',
			'fieldReport' => 'destination'
		)
	);

	public function init()
	{
		if (!Yii::app()->session['id_user'])
            exit;
		$this->instanceModel = new CallShopCdr;
		$this->abstractModel = CallShopCdr::model();
		$this->titleReport   = Yii::t('yii','CallShop');
		parent::init();
	}
	public function actionCsv()
	{
		$_GET['columns'] = preg_replace('/idPrefixdestination/', 'id_prefix', $_GET['columns']);
		parent::actionCsv();
	}

	public function actionReport()
	{

		$destino = '{"header":"Destino","dataIndex":"idPrefixdestination"},';
		$destinoNew = '{"header":"Destino","dataIndex":"id_prefix"},';
		if (preg_match("/$destino/", $_POST['columns'])) {
			$_POST['columns'] = preg_replace("/$destino/", $destinoNew , $_POST['columns']);
		}



		//gerar total a pagar no pdf
		$config = LoadConfig::getConfig();
		$filter = $this->createCondition(json_decode($_POST['filter']));
		$filter = $this->extraFilter($filter);
		$sql = 'SELECT SUM(price) priceSum FROM '.$this->abstractModel->tableName().' t '.$this->join.' WHERE '.$filter;
		$sumResult = Yii::app()->db->createCommand($sql)->queryAll();
		$this->titleReport   = $config['global']['base_currency'] . ' ' .round($sumResult[0]['priceSum'],2);
		$this->subTitleReport = Yii::t('yii','priceSun');

		$this->join  = '';
		$this->defaultFilter = 1;
		parent :: actionReport();
	}

	public function getAttributesModels($models, $itemsExtras = array())
	{
		$attributes = false;
		$sql = 'SELECT SUM(price) priceSum FROM '.$this->abstractModel->tableName().' t '.$this->join.' WHERE '.$this->filter;
		$sumResult = Yii::app()->db->createCommand($sql)->queryAll();
		$priceSum = $sumResult[0]['priceSum'];
		$this->titleReport   = $priceSum;
		
		$namePk = $this->abstractModel->primaryKey();
		foreach ($models as $key => $item)
		{
		    $attributes[$key] = $item->attributes;
		    $attributes[$key]['priceSum'] = $priceSum; //add	
		    if(isset($_SESSION['isClient']) && $_SESSION['isClient'])
			{
				foreach ($this->fieldsInvisibleClient as $field) {
       				unset($attributes[$key][$field]);
				}
			}
			
			if(isset($_SESSION['isAgent']) && $_SESSION['isAgent'])
			{
				foreach ($this->fieldsInvisibleAgent as $field) {
       				unset($attributes[$key][$field]);
				}
			}

		    if(!is_array($namePk) && $this->nameOtherFkRelated && get_class($this->abstractModel) === get_class($item)) {
		        if(count($this->extraFieldsRelated)) {
		            $resultSubRecords = $this->abstractModelRelated->findAll(array(
		                    'select' => implode(',', $this->extraFieldsRelated),
		                    'condition' => $this->nameFkRelated . ' = ' . $attributes[$key][$namePk]
		            ));

		            $subRecords = array();

		            if(count($this->extraValuesOtherRelated)) {
		                $attributesSubRecords = array();

		                foreach($resultSubRecords as $itemModelSubRecords) {
		                    $attributesSubRecords = $itemModelSubRecords->attributes;

		                    foreach($this->extraValuesOtherRelated as $relationSubRecord => $fieldsSubRecord)
		                    {
		                        $arrFieldsSubRecord = explode(',', $fieldsSubRecord);
		                        foreach($arrFieldsSubRecord as $fieldSubRecord)
		                        {
		                            $attributesSubRecords[$relationSubRecord . $fieldSubRecord] = $itemModelSubRecords->$relationSubRecord ? $itemModelSubRecords->$relationSubRecord->$fieldSubRecord : null;
		                        }
		                    }

		                    array_push($subRecords, $attributesSubRecords);
		                }
		            }
		            else {
		                foreach($resultSubRecords as $modelSubRecords) {
		                    array_push($subRecords, $modelSubRecords->attributes);
		                }
		            }
		        }
		        else {
		            $resultSubRecords = $this->abstractModelRelated->findAll(array(
		                'select' => $this->nameOtherFkRelated,
		                'condition' => $this->nameFkRelated . ' = ' . $attributes[$key][$namePk]
		            ));

		            $subRecords = array();
		            foreach($resultSubRecords as $keyModelSubRecords => $modelSubRecords) {
		                array_push($subRecords, (int) $modelSubRecords->attributes[$this->nameOtherFkRelated]);
		            }
		        }

		        $attributes[$key][$this->nameOtherFkRelated] = $subRecords;
		    }

		    foreach($itemsExtras as $relation => $fields)
		    {
		        $arrFields = explode(',', $fields);
		        foreach($arrFields as $field)
		        {
		            $attributes[$key][$relation . $field] = $item->$relation ? $item->$relation->$field : null;
		        }
		    }
		}

		return $attributes;
	}
}