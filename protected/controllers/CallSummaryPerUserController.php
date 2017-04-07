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

class CallSummaryPerUserController extends Controller
{
    var $config;
    public $attributeOrder  = 't.id_user DESC';
    public $group           = 't.id_user';
    public $select          = 'SQL_CACHE t.id, t.id_user, starttime, c.username AS idUserusername, 
            sum(sessionbill) AS sessionbill, 
            count(*) as nbcall,
            sum(buycost) AS buycost
            ';
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
        'calledstation',
        'idTrunktrunkcode',
        'id_user',
        'id_user',
        'sumlucro',
        'sumbuycost'
        );


    public function init()
    {
        
        $this->instanceModel = new CallSummaryPerUser;
        $this->abstractModel = CallSummaryPerUser::model();
        $this->titleReport   = Yii::t('yii','Calls Summary');
        parent::init();

        if(Yii::app()->session['isAgent']  == 2){
            $this->select   = 'SQL_CACHE t.id, t.id_user, starttime, c.username AS idUserusername,
            sum(agent_bill) AS sessionbill, 
            count(*) as nbcall,
            sum(sessionbill) AS buycost';
        }
        else if(Yii::app()->session['isClientAgent'] == true){
             $this->select   = 'SQL_CACHE t.id, t.id_user, starttime, c.username AS idUserusername,
            sum(agent_bill) AS sessionbill, 
            count(*) as nbcall';
        }
        
    }


    public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        foreach ($models as $key => $item)
        {
            $attributes[$key]                          = $item->attributes;
            $attributes[$key]['nbcall']                = $item->nbcall;
            $attributes[$key]['day']                   = $item->day;
            $attributes[$key]['lucro']                 = $item->sessionbill - $item->buycost;
            $attributes[$key]['sessiontime']           = $item->sessiontime / 60;
            $attributes[$key]['aloc_all_calls']        = $item->aloc_all_calls;
            $attributes[$key]['sumsessionbill']        = $item->sumsessionbill;
            $attributes[$key]['sumbuycost']            = $item->sumbuycost;
            $attributes[$key]['sumlucro']              = $item->sumlucro;
            $attributes[$key]['sumnbcall']             = $item->sumnbcall;
            $attributes[$key]['idUserusername']        = $item->idUserusername;
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


    public function filterReplace($filter)
    {

       if(preg_match('/c.username/', $filter)){
            if (!preg_match("/JOIN pkg_user/", $this->join)) 
                $this->join .= ' LEFT JOIN pkg_user c ON t.id_user = c.id';
            $filter = preg_replace('/c.username/', "c.username", $filter);
        }

        if(preg_match('/pkg_trunk.trunkcode/', $filter)){
            if (!preg_match("/JOIN pkg_trunk/", $this->join)) 
                $this->join .= ' LEFT JOIN pkg_trunk ON t.id_trunk = pkg_trunk.id';
        }     

        return $filter;
    }

    public function extraFilter ($filter)
    {

        $config = LoadConfig::getConfig();

        $filter = $this->filterReplace($filter);

        //is agent, get only agent customers
        if(Yii::app()->getSession()->get('user_type')  == 2)
            $filter .= ' AND t.id_user IN (SELECT id FROM pkg_user WHERE id_user =  '.Yii::app()->getSession()->get('id_user').')  ';

        else if(Yii::app()->getSession()->get('user_type')  == 3)
            $filter .= ' AND t.id_user = '.Yii::app()->getSession()->get('id_user');

        if (!Yii::app()->getSession()->get('isClient')) {
            $summary_per_user_days = $config['global']['summary_per_user_days'] * -1;
            if (!preg_match("/starttime/", $filter)) {
                $filter .= " AND starttime > '". date('Y-m-d', strtotime("$summary_per_user_days days"))."' ";
            }
        }else{
            $filter .= " AND c.id_user < 2";
        }

         

        return $filter;
    }

}