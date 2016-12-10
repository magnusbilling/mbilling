<?php
/**
 * Acoes do modulo "CallSummaryCallShop".
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
 * MagnusSolution.com <info@magnussolution.com>
 * 17/08/2012
 */

class CallSummaryCallShopController extends Controller
{
    public $attributeOrder  = 'day DESC';
    public $limit           = 7;
    public $group           = 'day';
    public $select          = 't.id, DATE(date) AS day, date as starttime, c.username AS idCardusername, cabina,
            sum(sessiontime) AS sessiontime, 
            sum(price) AS price, 
            count(*) as nbcall,
            sum(buycost) AS buycost,
            sum(price) - sum(buycost) AS lucro';

    public $join            = 'JOIN pkg_user c ON t.id_user = c.id';


    public $fieldsInvisibleClient = array(
        'id',
        'id_user_package_offer',
        'id_did',
        'id_prefix',
        'id_ratecard',
        'id_tariffgroup',
        'id_trunk',
        'real_sessiontime',
        'root_cost',
        'sessionid',
        'sipiax',
        'src',
        'stoptime',
        'markup',
        'calledstation',
        'idCardusername',
        'idTrunktrunkcode',
        'id_user',
        'id_user',
        'sumasr',
        );

    public $fieldsInvisibleAgent = array(
        'uniqueid',
        'id',
        'id_user_package_offer',
        'id_did',
        'id_prefix',
        'id_ratecard',
        'id_tariffgroup',
        'id_trunk',
        'real_sessiontime', 
        'root_cost',
        'sessionid',
        'sipiax',
        'src',
        'stoptime',
        'markup',
        'buycost',
        'calledstation',
        'idCardusername',
        'idTrunktrunkcode',
        'id_user',
        'id_user',
        'sumlucro',
        'sumbuycost',
        'sumasr',
        'asr'
        );


    public function init()
    {
        ini_set('memory_limit', '-1');
        $this->instanceModel = new CallSummaryCallShop;
        $this->abstractModel = CallSummaryCallShop::model();
        $this->titleReport   = Yii::t('yii','Calls Summary Callshop');

        parent::init();
    }

    public function actionRead()
    {
         # recebe os parametros para o filtro
        $filter = isset($_GET['filter']) ? $_GET['filter'] : null;
        $filter = $filter ? $this->createCondition(json_decode($filter)) : $this->defaultFilter;

        $limit  = strlen($filter) > 2 && preg_match("/date/", $filter) ? $_GET[$this->nameParamLimit] : $this->limit;

        //nao permite mais de 31 registros
        $limit  = $limit > 31 ? $limit = 31 : $limit;
        $_GET[$this->nameParamLimit]  = $limit;


        parent::actionRead();

    }
    public function recordsExtraSum($select = '*', $join = '', $filter = '', $group = '', $limit= '', $records = array())
    {   
        foreach ($records as $key => $value) {
            $records[0]->sumsessiontime  += $value['sessiontime'] / 60 ;
            $records[0]->sumprice += $value['price'];
            $records[0]->sumbuycost += $value['buycost'];

            $records[0]->sumlucro += $value['price'] - $value['buycost'];
            $records[0]->sumaloc_all_calls +=  $value['sessiontime'] / $value['nbcall'];

            $records[0]->sumnbcall += $value['nbcall'];
        }

        $this->nameSum = 'sum';

        return $records;
    }

    public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        foreach ($models as $key => $item)
        {
            $attributes[$key]                          = $item->attributes;
            $attributes[$key]['nbcall']                = $item->nbcall;
            $attributes[$key]['day']                   = $item->day;
            $attributes[$key]['sessiontime']           = $item->sessiontime / 60;
            $attributes[$key]['aloc_all_calls']        = $item->sessiontime / $item->nbcall;
            $attributes[$key]['sumsessiontime']        = $item->sumsessiontime;
            $attributes[$key]['sumprice']        = $item->sumprice;
            $attributes[$key]['sumbuycost']            = $item->sumbuycost;
            $attributes[$key]['sumlucro']              = $item->sumlucro;
            $attributes[$key]['sumaloc_all_calls']     = $item->sumaloc_all_calls;
            $attributes[$key]['sumnbcall']             = $item->sumnbcall;
            $attributes[$key]['sumasr']                = $item->sumasr;
            $attributes[$key]['idCardusername']        = $item->idCardusername;


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

    public function extraFilter ($filter)
    {

        $filter = $this->filterReplace($filter);

        if(Yii::app()->getSession()->get('user_type')  == 3)
            $filter .= ' AND t.id_user = '.Yii::app()->getSession()->get('id_user');

        return $filter;
    }

}