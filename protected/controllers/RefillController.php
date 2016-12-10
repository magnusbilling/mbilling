<?php
/**
 * Acoes do modulo "Refill".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author  Adilson Leffa Magnus.
 * @copyright   Todos os direitos reservados.
 * ###################################
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 23/06/2012
 */

class RefillController extends Controller
{
    public $attributeOrder      = 'date DESC';
    public $extraValues         = array('idUser' => 'username');
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
        'idUserusername',
        'refill_type'
    );

    public function init()
    {
        $this->instanceModel = new Refill;
        $this->abstractModel = Refill::model();
        $this->titleReport   = Yii::t('yii','Refill');
        parent::init();
    }

    public function extraFilter ($filter)
    {
        $filter = isset($this->filter) ? $filter.$this->filter : $filter;

        $filter = $filter . ' AND ' .$this->defaultFilter;
        $filter = $this->filterReplace($filter);

        if(Yii::app()->getSession()->get('isAdmin'))
            $filter .= ' AND b.id_user = 1';
        else if(Yii::app()->getSession()->get('user_type')  > 1 && $this->filterByUser)
        {
            $filter .= ' AND ('. $this->defaultFilterByUser . ' = '.Yii::app()->getSession()->get('id_user');
            $filter .= ' OR t.id_user = '.Yii::app()->getSession()->get('id_user').')';
        }
        return $filter;
    }

    public function recordsExtraSum($select = '*', $join = '', $filter, $group = '', $limit= '', $records= '')
    {
        $sqlSum = "SELECT EXTRACT(YEAR_MONTH FROM date) AS CreditMonth , SUM(t.credit) AS sumCreditMonth
        FROM pkg_refill t JOIN pkg_user b ON t.id_user = b.id WHERE $filter GROUP BY CreditMonth";

        $this->nameSum = 'sum';
        return $this->abstractModel->findAllBySql($sqlSum);
    }

    public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        $namePk = $this->abstractModel->primaryKey();
        $this->filter = $this->filter == '' ? 1 : $this->filter;
        $sql = "SELECT SUM(t.credit) AS sumCredit FROM pkg_refill t ".$this->join." WHERE ".$this->filter;


        $resultCreditTotal = Yii::app()->db->createCommand($sql)->queryAll();

        foreach ($models as $key => $item)
        {
            $attributes[$key]                   = $item->attributes;
            $attributes[$key]['sumCredit']      = number_format($resultCreditTotal[0]['sumCredit'],2);

            $attributes[$key]['sumCreditMonth'] = $item->sumCreditMonth;
            $attributes[$key]['CreditMonth']    = substr($item->CreditMonth,0,4).'-'.substr($item->CreditMonth,-2);

            if(isset(Yii::app()->session['isClient']) && Yii::app()->session['isClient'])
            {
                foreach ($this->fieldsInvisibleClient as $field) {
                    unset($attributes[$key][$field]);
                }
            }

            if(isset(Yii::app()->session['isAgent']) && Yii::app()->session['isAgent'])
            {
                foreach ($this->fieldsInvisibleAgent as $field) {
                    unset($attributes[$key][$field]);
                }
            }

            if(!is_array($namePk) && $this->nameOtherFkRelated && get_class($this->abstractModel) === get_class($item)) {
                $resultSubRecords = $this->abstractModelRelated->findAll(array(
                    'select' => $this->nameOtherFkRelated,
                    'condition' => $this->nameFkRelated . ' = ' . $attributes[$key][$namePk]
                ));

                $subRecords = array();
                foreach($resultSubRecords as $keyModelSubRecords => $modelSubRecords) {
                    array_push($subRecords, (int) $modelSubRecords->attributes[$this->nameOtherFkRelated]);
                }

                $attributes[$key][$this->nameOtherFkRelated] = $subRecords;
            }

            foreach($itemsExtras as $relation => $fields)
            {
                $arrFields = explode(',', $fields);
                foreach($arrFields as $field)
                {
                    $attributes[$key][$relation . $field] = $item->$relation ? $item->$relation->$field : null;
                    if(Yii::app()->session['isClient']) {
                        foreach ($this->fieldsInvisibleClient as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }

                    if(Yii::app()->session['isAgent']) {
                        foreach ($this->fieldsInvisibleAgent as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }
                }
            }
        }

        return $attributes;
    }

    public function filterReplace($filter)
    {
        //activated is in where clause is ambiguous
        $filter = preg_replace('/activated/', 't.activated', $filter);
        $filter = preg_replace('/status/', 't.status', $filter);
        $filter = preg_replace('/creationdate/', 't.creationdate', $filter);
        $filter = preg_replace('/credit/', 't.credit', $filter);

        if(preg_match('/idUserusername/', $filter))
            $filter = preg_replace('/idUserusername/', "b.username", $filter);

        return $filter;
    }
}