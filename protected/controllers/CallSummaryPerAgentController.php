<?php
/**
 * Acoes do modulo "Call".
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
 * 17/08/2012
 */

class CallSummaryPerAgentController extends Controller
{
    var $config;
    public $attributeOrder  = 'c.id_user DESC';
    public $limit           = 7;
    public $group           = 'c.id_user';
    public $select          = 't.id, t.id_user, c.id_user AS idUserusername,  sum(sessionbill) AS sessionbill, count(*) as nbcall,
            sum(buycost) AS buycost, starttime, sum(sessionbill) - sum(buycost) AS lucro, 
            pkg_trunk.trunkcode AS idTrunktrunkcode';
    public $join            = 'JOIN pkg_user c ON t.id_user = c.id
                            JOIN pkg_trunk ON t.id_trunk = pkg_trunk.id';


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
        'idTrunktrunkcode',
        'id_user',
        'id_user',
        'lucro',
        'sumlucro',
        'sumbuycost',
        'buycost'
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
        'idTrunktrunkcode',
        'id_user',
        'id_user',
        'sumlucro',
        'sumbuycost'
        );


    public function init()
    {
        $config = LoadConfig::getConfig();
        ini_set('memory_limit', '-1');
        $this->instanceModel = new CallSummaryPerUser;
        $this->abstractModel = CallSummaryPerUser::model();
        $this->titleReport   = Yii::t('yii','Calls Summary');
        parent::init();
    }


    public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        foreach ($models as $key => $item)
        {

            //$sql = "SELECT username FROM pkg_user WHERE id = ". $item->idUserusername;

            $resultUser = User::model()->findAll(array(
                    'select' => 'username',
                    'condition' => 'id = '. $item->idUserusername
                ));

            $attributes[$key]                          = $item->attributes;
            $attributes[$key]['nbcall']                = $item->nbcall;
            $attributes[$key]['day']                   = $item->day;
            $attributes[$key]['lucro']                 = $item->lucro;
            $attributes[$key]['aloc_all_calls']        = $item->aloc_all_calls;
            $attributes[$key]['sumsessionbill']        = $item->sumsessionbill;
            $attributes[$key]['sumbuycost']            = $item->sumbuycost;
            $attributes[$key]['sumlucro']              = $item->sumlucro;
            $attributes[$key]['sumnbcall']             = $item->sumnbcall;
            $attributes[$key]['idUserusername']        = $resultUser[0]->username;
            $attributes[$key]['idTrunktrunkcode']      = $item->idTrunktrunkcode;

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
        $config = LoadConfig::getConfig();
        $filter = $this->filterReplace($filter);      


        if(Yii::app()->getSession()->get('user_type')  == 2)
            $filter .= ' AND c.id_user = '.Yii::app()->getSession()->get('id_user');

        else if(Yii::app()->getSession()->get('user_type')  == 3)
            $filter .= ' AND t.id_user = '.Yii::app()->getSession()->get('id_user');

        if (!Yii::app()->getSession()->get('isClient')) {
            $summary_per_user_days = $config['global']['summary_per_agent_days'] * -1;
            if (!preg_match("/starttime/", $filter)) {
                $filter .= " AND starttime > '". date('Y-m-d', strtotime("$summary_per_user_days days"))."' ";
            }
        }

        $filter .= " AND c.id_user > 1 ";

        return $filter;
    }

}