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

class TrunkReportController extends Controller
{

	public $attributeOrder = 'id';
	public $select = 'id, trunkcode, call_answered, call_total, secondusedreal, 
	( call_answered / call_total) * 100 AS asr,
	secondusedreal / call_answered AS acd';
	public function init()
	{
		$this->instanceModel = new TrunkReport;
		$this->abstractModel = TrunkReport::model();
		$this->titleReport   = Yii::t('yii','Trunk Report');

		parent::init();
	}

    public function actionClear()
    {
        # recebe os parametros para o filtro
        if(isset($_POST['filter']) && strlen($_POST['filter']) > 5)
            $filter =  $_POST['filter'];
        else{
            echo json_encode(array(
                $this->nameSuccess => false,
                $this->nameMsg => 'Por favor realizar um filtro para reprocesar' 
            ));
            exit;
        }
        $filter = $filter ? $this->createCondition(json_decode($filter)) : '';  



        $sql = "UPDATE pkg_trunk set call_answered = 0,  call_total = 0, secondusedreal = 0 WHERE $filter";
        Yii::app()->db->createCommand($sql)->execute();

        echo json_encode(array(
            $this->nameSuccess => true,
            $this->nameMsg => $this->msgSuccess
        ));

    }


	public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        foreach ($models as $key => $item)
        {
            $attributes[$key]                          = $item->attributes;
            $attributes[$key]['acd']                = $item->acd;
            $attributes[$key]['asr']                   = $item->asr > 0 ? $item->asr : 0;

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


            foreach($itemsExtras as $relation => $fields)
            {
                $arrFields = explode(',', $fields);
                foreach($arrFields as $field)
                {
                    $attributes[$key][$relation . $field] = $item->$relation->$field;
                    if($_SESSION['isClient']) {
                        foreach ($this->fieldsInvisibleClient as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }

                    if($_SESSION['isAgent']) {
                        foreach ($this->fieldsInvisibleAgent as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }
                }
            }
        }

        return $attributes;
    	}
}