<?php
/**
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
 *
 */
class Calc
{    
    public $lastcost = 0;
    public $lastbuycost = 0;
    public $answeredtime = 0;
    public $real_answeredtime = 0;
    public $dialstatus = 0;
    public $usedratecard = 0;
    public $usedtrunk = 0;
    public $freetimetocall_used = 0;
    public $dialstatus_rev_list;
    public $tariffObj = array();
    public $freetimetocall_left = array();
    public $freecall = array();
    public $offerToApply = array();
    public $number_trunk = 0;
    public $idCallCallBack=0;
    public $agent_bill=0;

    function Calc()
    {
        $this->dialstatus_rev_list = Magnus::getDialStatus_Revert_List();
    }

    function init()
    {
        $this->number_trunk = 0;
        $this->answeredtime = 0;
        $this->real_answeredtime = 0;
        $this->dialstatus = '';
        $this->usedratecard = '';
        $this->usedtrunk = '';
        $this->lastcost = '';
        $this->lastbuycost = '';
    }

    function calculateAllTimeout(&$MAGNUS, $credit,$agi)
    {
        if (!is_array($this->tariffObj) || count($this->tariffObj) == 0)
            return false;

        for ($k = 0; $k < count($this->tariffObj); $k++)
        {
            $res_calcultimeout = $this->calculateTimeout($MAGNUS, $credit, $k,$agi);            
            
            if (substr($res_calcultimeout, 0, 5) == 'ERROR' || $res_calcultimeout < 1){
                return false;
            }else{
                return true;
            }
                
        }

        return true;
        
    }

    function calculateTimeout(&$MAGNUS, $credit, $K = 0,$agi)
    {
        $rateinitial                   = $MAGNUS->round_precision(abs($this->tariffObj[$K]['rateinitial']));
        $initblock                     = $this->tariffObj[$K]['initblock'];
        $billingblock                  = $this->tariffObj[$K]['billingblock'];
        $connectcharge                 = $MAGNUS->round_precision(abs($this->tariffObj[$K]['connectcharge']));
        $disconnectcharge              = $MAGNUS->round_precision(abs($this->tariffObj[$K]['disconnectcharge']));
        $minimal_time_charge           = $this->tariffObj[$K]['minimal_time_charge'];
        $id_offer                      = $package_offer = $this->tariffObj[$K]['package_offer'];
        $id_rate                       = $this->tariffObj[$K]['id_rate'];
        $initial_credit                = $credit;
        $this->freetimetocall_left[$K] = 0;
        $this->freecall[$K]            = false;
        $this->offerToApply[$K]        = null;

        if ($id_offer == 1 && $MAGNUS->id_offer > 0 && $K == 0)
        {
            $sql = "SELECT pkg_offer.id, packagetype, billingtype, pkg_offer_use.reservationdate, freetimetocall ".
                    "FROM pkg_offer_use  INNER JOIN pkg_offer ON pkg_offer_use.id_offer = pkg_offer.id ".
                    "WHERE pkg_offer.id= " . $MAGNUS->id_offer . " AND pkg_offer_use.id_user = ".$MAGNUS->id_user." ORDER BY packagetype ASC";
            $agi->verbose($sql,25);
            $result_packages = Yii::app()->db->createCommand( $sql )->queryAll();
            

            if (!empty($result_packages))
            {
                $package_selected = false;
                $freetimetocall = $result_packages[0]["freetimetocall"];
                $packagetype = $result_packages[0]["packagetype"];
                $billingtype = $result_packages[0]["billingtype"];
                $startday = date('d', strtotime($result_packages[0]['reservationdate']));
                $id_offer = $result_packages[$K]['id'];
                switch ($packagetype)
                {    
                    case 0:
                        $agi->verbose("offer Unlimited calls");
                        $this->freecall[0] = true;
                        $package_selected = true;
                        $this->offerToApply[$K] = array("id" => $id_offer, "label" => "Unlimited calls", "type" => $packagetype);
                        break;
                    case 1:

                        if ($freetimetocall > 0)
                        {
                            $agi->verbose('FREE CALLS');
                            $number_calls_used = $MAGNUS->freeCallUsed($agi, $MAGNUS->id_user, $id_offer, $billingtype, $startday);
                            
                            if ($number_calls_used < $freetimetocall)
                            {
                                $this->freecall[$K] = true;
                                $package_selected = true;
                                $this->offerToApply[$K] = array("id" => $id_offer, "label" => "Number of Free calls", "type" => $packagetype);
                                $agi->verbose(print_r($this->offerToApply[$K],true),6);
                            }
                        }
                        break;
                    case 2:
                        if ($freetimetocall > 0)
                        {
                            $this->freetimetocall_used = $MAGNUS->packageUsedSeconds($agi, $MAGNUS->id_user, $id_offer, $billingtype, $startday);
                            $this->freetimetocall_left[$K] = $freetimetocall - $this->freetimetocall_used;

                            if ($this->freetimetocall_left[$K] < 0)
                                $this->freetimetocall_left[$K] = 0;
                            if ($this->freetimetocall_left[$K] > 0)
                            {
                                $package_selected = true;
                                $this->offerToApply[$K] = array("id" => $id_offer, "label" => "Free minutes", "type" => $packagetype);
                                $agi->verbose(print_r($this->offerToApply[$K],true),6);
                            }
                        }
                        break;
                }
            }
        }      


        $credit -= $connectcharge;
        $this->tariffObj[$K]['timeout'] = 0;
        $this->tariffObj[$K]['timeout_without_rules'] = 0;
        $this->tariffObj[$K]['freetime_include_in_timeout'] = $this->freetimetocall_left[$K];
        $agi->verbose("Credit $credit",20);
        if ($credit < 0 && !$this->freecall[$K] && $this->freetimetocall_left[$K] <= 0)
        {
            return "ERROR CT1";
            /*NO  CREDIT TO CALL */
        }

        $TIMEOUT = 0;
        $answeredtime_1st_leg = 0;
        if ($rateinitial <= 0)/*Se o preÃ§o for 0, entao retornar o timeout em 3600 s*/
        {
            $this->tariffObj[$K]['timeout'] = 3600;
            $this->tariffObj[$K]['timeout_without_rules'] = 3600;
            $TIMEOUT = 3600;
            return $TIMEOUT;
        }

        if ($this->freecall[$K])/*usado para planos gratis*/
        {
            $this->tariffObj[$K]['timeout'] = 3600;
            $TIMEOUT = 3600;
            $this->tariffObj[$K]['timeout_without_rules'] = 3600;
            $this->tariffObj[$K]['freetime_include_in_timeout'] = 3600;
             return $TIMEOUT;
        }
        if ($credit < 0 && $this->freetimetocall_left[$K] > 0)
        {
            $this->tariffObj[$K]['timeout'] = $this->freetimetocall_left[$K];
            $TIMEOUT = $this->freetimetocall_left[$K];
            $this->tariffObj[$K]['timeout_without_rules'] = $this->freetimetocall_left[$K];
            return $TIMEOUT;
        }

        if ($MAGNUS->mode == 'callback')
        {
            $credit -= $calling_party_connectcharge;
            $credit -= $calling_party_disconnectcharge;
            $num_min = $credit / ($rateinitial + $calling_party_rateinitial);
            $answeredtime_1st_leg = intval($agi->get_variable('ANSWEREDTIME', true));
        }
        else
        { 
            $num_min = $credit / $rateinitial;/*numero de minutos*/
        }

        $num_sec = intval($num_min * 60) - $answeredtime_1st_leg;/*numero de segundos - o tempo que gastou para completar*/
        if ($billingblock > 0)
        {
            $mod_sec = $num_sec % $billingblock;
            $num_sec = $num_sec - $mod_sec;
        }
        $TIMEOUT = $num_sec;

        /*Call time to speak without rate rules... idiot rules*/
        $num_min_WR = $initial_credit / $rateinitial;
        $num_sec_WR = intval($num_min_WR * 60);
        $this->tariffObj[$K]['timeout_without_rules'] = $num_sec_WR + $this->freetimetocall_left[$K];
        $this->tariffObj[$K]['timeout'] = $TIMEOUT + $this->freetimetocall_left[$K];


        return $TIMEOUT + $this->freetimetocall_left[$K];
    }

    function calculateCost(&$MAGNUS, $callduration, $K = 0, $agi)
    {
        $this->usedratecard           = $this->usedratecard < 0 ? 0 : $this->usedratecard;
        $K                            = $this->usedratecard;
        $buyrate                      = $MAGNUS->round_precision(abs($this->tariffObj[$K]['buyrate']));
        $buyrateinitblock             = $this->tariffObj[$K]['buyrateinitblock'];
        $buyrateincrement             = $this->tariffObj[$K]['buyrateincrement'];
        $rateinitial                  = $MAGNUS->round_precision(abs($this->tariffObj[$K]['rateinitial']));
        $initblock                    = $this->tariffObj[$K]['initblock'];
        $billingblock                 = $this->tariffObj[$K]['billingblock'];
        $connectcharge                = $MAGNUS->round_precision(abs($this->tariffObj[$K]['connectcharge']));
        $disconnectcharge             = $MAGNUS->round_precision(abs($this->tariffObj[$K]['disconnectcharge']));
        $additional_grace_time        = $this->tariffObj[$K]['additional_grace'];


        $this->freetimetocall_used = 0;

        $agi->verbose( "CALCULCOST: K=$K - CALLDURATION:$callduration - freetimetocall_used=$this->freetimetocall_used",10);
        $cost = 0;
        $cost -= $connectcharge;        

        $buyratecallduration = $callduration;

        $buyratecost = 0;
        if ($buyratecallduration < $buyrateinitblock)
            $buyratecallduration = $buyrateinitblock;
        if (($buyrateincrement > 0) && ($buyratecallduration > $buyrateinitblock))
        {
            $mod_sec = $buyratecallduration % $buyrateincrement;
             /* 12 = 30 % 18*/
            if ($mod_sec > 0)
                $buyratecallduration += ($buyrateincrement - $mod_sec);
             /* 30 += 18 - 12*/
        }
        $buyratecost -= ($buyratecallduration / 60) * $buyrate;
        if ($this->freecall[$K])
        {
            $this->lastcost = 0;
            $this->lastbuycost = $buyratecost;
            $agi->verbose( "CALCUL COST: K=$K - BUYCOST: $buyratecost - SELLING COST: $cost",10);
            return;
        }
        if ($callduration < $initblock)
        {
            $callduration = $initblock;
        }

        if (($billingblock > 0) && ($callduration > $initblock))
        {
            $mod_sec = $callduration % $billingblock;
            if ($mod_sec > 0)
            {
                $callduration += ($billingblock - $mod_sec);
            }
        }
        if ($this->freetimetocall_left[$K] >= $callduration)
        {
            $this->freetimetocall_used = $callduration;
            $callduration = 0;
        }
        $cost -= ($callduration / 60) * $rateinitial;
        
        $agi->verbose( "CALCULCOST: 1. COST: $cost - ($callduration/60) * $rateinitial ",10);
        

        $cost = $MAGNUS->round_precision($cost);

        $agi->verbose( "CALCULCOST: K=$K - BUYCOST:$buyratecost - SELLING COST:$cost",10);
        if ($cost > (0 - $minimal_call_cost))
        {
            $this->lastcost = 0 - $minimal_call_cost;
        }
        else
        {
            $this->lastcost = $cost;
        }
        $this->lastbuycost = $buyratecost;

        $agi->verbose( '$this->lastbuycost = ' . $this->lastbuycost,10 );
        $agi->verbose( '$this->lastcost = ' . $this->lastcost,10 );
    }

    function array_csort()
    {
        $args = func_get_args();
        $marray = array_shift($args);
        $i = 0;
        $msortline = "return(array_multisort(";
        foreach ($args as $arg)
        {
            $i++;
            if (is_string($arg))
            {
                foreach ($marray as $row)
                {
                    $sortarr[$i][] = $row[$arg];
                }
            }
            else
            {
                $sortarr[$i] = $arg;
            }
            $msortline .= "\$sortarr[" . $i . "],";
        }
        $msortline .= "\$marray));";
        eval($msortline);
        return $marray;
    }

    function updateSystem(&$MAGNUS, &$agi, $calledstation, $doibill = 1, $didcall = 0, $callback = 0)
    {
        $agi->verbose('Update System',6);
        $this->usedratecard = $this->usedratecard < 0 ? 0 : $this->usedratecard;
        $K = $this->usedratecard;
        $id_offer = $MAGNUS->id_offer;
        $additional_grace_time = $this->tariffObj[$K]['additional_grace'];
        $sessiontime = $this->answeredtime;
        $dialstatus = $this->dialstatus;

        if ($sessiontime > 0)
        {
            $this->freetimetocall_used = 0;
            //adiciona o tempo adicional
            if (substr($additional_grace_time, -1) == "%")
            {
                $additional_grace_time = str_replace("%", "", $additional_grace_time);
                $additional_grace_time = $additional_grace_time / 100;
                $additional_grace_time = str_replace("0.", "1.", $additional_grace_time);
                $sessiontime = $sessiontime * $additional_grace_time;
            }
            else
            {
                if ($sessiontime > 0)
                {
                    $sessiontime = $sessiontime + $additional_grace_time;
                }
            }

            if (($id_offer != -1) && ($this->offerToApply[$K] != null))
            {
                $id_offer = $this->offerToApply[$K]["id"];

                $this->calculateCost($MAGNUS, $sessiontime, 0,$agi);
                
                switch ($this->offerToApply[$K]["type"])
                {
                        /*Unlimited*/
                    case 0:
                        $this->freetimetocall_used = $sessiontime;
                        break;
                        /*free calls*/
                    case 1:
                        $this->freetimetocall_used = $sessiontime;
                        break;
                        /*free minutes*/
                    case 2:
                        if ($sessiontime > '60')
                        {
                            $restominutos = $sessiontime % 60;
                            $calculaminutos = ($sessiontime - $restominutos) / 60;
                            if ($restominutos > '0')
                                $calculaminutos++;
                            $sessiontime = $calculaminutos * 60;
                        } elseif ($sessiontime < '1')
                        {
                            $sessiontime = 0;
                        }
                        else
                        {
                            $sessiontime = 60;
                        }

                        $this->freetimetocall_used = $sessiontime;

                        break;
                }

                

                /* calculcost could have change the duration of the call*/
                $sessiontime = $this->answeredtime;
                /* add grace time*/

                $sql = "INSERT INTO pkg_offer_cdr (id_user, id_offer, used_secondes) VALUES ('" . $MAGNUS->id_user . "', '$id_offer', '$this->freetimetocall_used')";
                try {
                     Yii::app()->db->createCommand( $sql )->execute(); 
                } catch (Exception $e) {
                    $agi->verbose($e->getMessage(),1);
                }
                
                $agi->verbose($sql,25);
            }
            else
            {
                $this->calculateCost($MAGNUS, $sessiontime, 0,$agi);
            }
        }
        else
        {
            $sessiontime = 0;
        }
        $agi->verbose( 'Sessiontime'. $sessiontime,10);

        if (($id_offer != -1) && ($this->offerToApply[$K] != null) && $sessiontime > 0)
            $sessiontime = $this->freetimetocall_used;

        $id_prefix   = $this->tariffObj[0]['id_prefix'];
        $id_plan     = $this->tariffObj[$K]['id_plan'];
        $buycost = 0;
        if ($doibill == 0 || $sessiontime < $this->tariffObj[$K]['minimal_time_charge'])
        {
            $cost = 0;
            $buycost = abs($this->lastbuycost);
        }
        else
        {
            $cost = $this->lastcost;
            $buycost = abs($this->lastbuycost);
        }

        $buyrateapply = $this->tariffObj[$K]['buyrate'];
        $rateapply    = $this->tariffObj[$K]['rateinitial'];
        $agi->verbose( "CALL: used tariff K=$K - (sessiontime=$sessiontime :: dialstatus=$dialstatus)",10);

        if ($didcall)
            $calltype = 2;
        elseif ($callback == 2)
            $calltype = 7;
        elseif ($callback)
            $calltype = 4;
        else
            $calltype = 0;
        if (strlen($this->dialstatus_rev_list[$dialstatus]) > 0)
            $terminatecauseid = $this->dialstatus_rev_list[$dialstatus];
        else
            $terminatecauseid = 0;

        if($callback == 2)//muda o termino para transferencia
            $terminatecauseid = 1;


        $real_sessiontime      = (!is_numeric($this->real_answeredtime)) ? 'NULL' : "'" . $this->real_answeredtime . "'";

        if ($calltype == '4' && $sessiontime > '0')
        {
            $terminatecauseid = '1';
        }

        /*recondeo call*/
        if ($MAGNUS->config["global"]['bloc_time_call']== 1 && $cost != 0 )
        {
            $initblock = ($this->tariffObj[$K]['initblock'] < 1) ? 1 : $this->tariffObj[$K]['initblock'];
            $billingblock = ($this->tariffObj[$K]['billingblock'] < 1) ? 1 : $this->tariffObj[$K]['billingblock'];

            if ($sessiontime > $initblock)
            {
                $restominutos = $sessiontime % $billingblock;
                $calculaminutos = ($sessiontime - $restominutos) / $billingblock;
                if ($restominutos > '0')
                    $calculaminutos++;
                $sessiontime = $calculaminutos * $billingblock;

            } elseif ($sessiontime < '1')
                $sessiontime = 0;

            else
                $sessiontime = $initblock;
        }
        $calldestinationPortabilidade =  $calledstation;
        if($MAGNUS->portabilidade == 1)
        {
            if (substr($calledstation, 0, 4) == '1111')
                $calledstation = str_replace(substr($calledstation, 0, 7), "", $calledstation);
        }
        $cost = $cost + $MAGNUS->round_precision(abs($MAGNUS->callingcardConnection));

        $agi->verbose($cost .'+' .  $MAGNUS->round_precision(abs($MAGNUS->callingcardConnection)) . ' = '.$cost,25);

        $costCdr = $cost;
        if ($sessiontime > 0)
        {           

            AGI_Callback::chargeFistCall($agi, $MAGNUS, $this, $sessiontime);

            if ($didcall == 0 && $callback == 0)
                $myclause_nodidcall = " , redial='" . $calledstation . "' ";
            else
                $myclause_nodidcall = '';
            if (!defined($myclause_nodidcall))
                $myclause_nodidcall = null;

            /*Update the global credit */
            $MAGNUS->credit = $MAGNUS->credit + $cost;
            /*CALULATION CUSTO AND SELL RESELLER */
           
            if(!is_null($MAGNUS->id_agent) && $MAGNUS->id_agent > 1){
                $agi->verbose( '$MAGNUS->id_agent'.$MAGNUS->id_agent. ' $calledstation'. $calldestinationPortabilidade, $this->real_answeredtime ,1);
                $cost = $this->agent_bill = $this->updateSystemAgent($agi, $MAGNUS, $MAGNUS->id_agent, $calldestinationPortabilidade , $MAGNUS->round_precision(abs($cost)), $this->real_answeredtime);
            }
            else
            {
                $sql = "UPDATE pkg_user SET credit= credit - " . $MAGNUS->round_precision(abs($cost)) . " $myclause_nodidcall,  lastuse=now() WHERE username='" . $MAGNUS->username . "'";
                try {
                     Yii::app()->db->createCommand( $sql )->execute(); 
                } catch (Exception $e) {
                    $agi->verbose($e->getMessage(),1);
                }
                $agi->verbose( $sql,25);
                $agi->verbose("Update credit username $MAGNUS->username, ".$MAGNUS->round_precision(abs($cost)),6);
            }


            $sql = "UPDATE pkg_trunk SET secondusedreal = secondusedreal + $sessiontime, call_answered = call_answered + 1 WHERE id='" . $this->usedtrunk . "'";
            try {
                 Yii::app()->db->createCommand( $sql )->execute(); 
            } catch (Exception $e) {
                $agi->verbose($e->getMessage(),1);
            }
            $agi->verbose($sql,25);

            $sql = "UPDATE pkg_provider SET credit = credit -  $buycost WHERE id='" . $this->tariffObj[$K]['id_provider'] . "'";
            try {
                 Yii::app()->db->createCommand( $sql )->execute(); 
            } catch (Exception $e) {
                $agi->verbose($e->getMessage(),1);
            }
            $agi->verbose($sql,25);

            if ($MAGNUS->callshop == 1)
            {
                $sql = "SELECT dialprefix, destination, buyrate, minimo, block FROM pkg_rate_callshop WHERE dialprefix = SUBSTRING('$calledstation',1,length(dialprefix)) and id_user=$MAGNUS->id_user ORDER BY LENGTH(dialprefix) DESC";
                $resultCallShop = Yii::app()->db->createCommand( $sql )->queryAll();
                $agi->verbose($sql,25);

                $buyrate = $resultCallShop[0]['buyrate'] > 0 ? $resultCallShop[0]['buyrate'] : $cost;
                $initblock = $resultCallShop[0]['minimo'];
                $increment = $resultCallShop[0]['block'];

                $sellratecost_callshop = $MAGNUS->calculation_price($buyrate, $sessiontime, $initblock, $increment);

                $cabina = explode("-", $MAGNUS->channel);
                $cabina = explode("/", $cabina[0]);
                $cabina = $cabina[1];

                $sql = "DELETE FROM pkg_callshop WHERE sessionid = '$MAGNUS->channel'";
                try {
                     Yii::app()->db->createCommand( $sql )->execute(); 
                } catch (Exception $e) {
                    $agi->verbose($e->getMessage(),1);
                }
                $agi->verbose($sql,25);

                $sql_COLUMN = "sessionid, id_user, id_prefix, status, price, buycost, calledstation, cabina, sessiontime";
                $sql = "INSERT INTO pkg_callshop ($sql_COLUMN) VALUES ('$MAGNUS->channel', $MAGNUS->id_user, $id_prefix, '0', $sellratecost_callshop, ". $MAGNUS->round_precision(abs($cost)) .", $calledstation, '$cabina', $sessiontime)";
                $agi->verbose($sql);
                try {
                     Yii::app()->db->createCommand( $sql )->execute(); 
                } catch (Exception $e) {
                    $agi->verbose($e->getMessage(),1);
                }
            }
        }


        if ($MAGNUS->config["global"]['cache'] == 1) {
            $starttime = " datetime(strftime('%s', 'now') - $sessiontime, 'unixepoch', 'localtime')";
            $stoptime = "datetime('now', 'localtime')";
        } else {
            $starttime = "SUBDATE(CURRENT_TIMESTAMP, INTERVAL $sessiontime SECOND) ";
            $stoptime = "now()";
        }



        if ($terminatecauseid == 1) {
            $table = 'pkg_cdr';
            $fields = "uniqueid, sessionid, id_user, starttime, sessiontime, real_sessiontime, calledstation, terminatecauseid, ".
            "stoptime, sessionbill, id_plan, id_trunk, src, sipiax, buycost, id_prefix, agent_bill";

            $value = "'$MAGNUS->uniqueid', '$MAGNUS->channel', $MAGNUS->id_user, $starttime, ".
            "'$sessiontime', $real_sessiontime, '$calledstation', '$terminatecauseid', $stoptime,'" . $MAGNUS->round_precision(abs($costCdr)) . "', ".
            "$MAGNUS->id_plan, $this->usedtrunk, '$MAGNUS->CallerID', '$calltype', '$buycost', $id_prefix, $this->agent_bill";
            
            $sql = "INSERT INTO pkg_cdr ($fields) VALUES ($value)";
            $agi->verbose($sql,25);
        }else{
            $table = 'pkg_cdr_failed';
            $fields = "uniqueid, sessionid, id_user, starttime,  calledstation, terminatecauseid, ".
            " id_plan, id_trunk, src, sipiax, id_prefix";

            $value = "'$MAGNUS->uniqueid', '$MAGNUS->channel', $MAGNUS->id_user, $starttime, ".
            " '$calledstation', '$terminatecauseid', ".
            "$MAGNUS->id_plan, $this->usedtrunk, '$MAGNUS->CallerID', '$calltype', $id_prefix";
            
            $sql = "INSERT INTO pkg_cdr_failed ($fields) VALUES ($value)";
            $agi->verbose($sql,25);
        }

        if ($MAGNUS->config["global"]['cache'] == 1) {
            $MAGNUS->sqliteInsert($agi,$fields,$value,$table);
        }else{
            try {
                Yii::app()->db->createCommand( $sql )->execute();
             } catch (Exception $e) {
                $agi->verbose( $e->getMessage(),1);
            }
        }

        
    }

    function updateSystemAgent($agi, $MAGNUS,$id_agent, $calledstation, $cost, $sessiontime)
    {

        $sql = "SELECT rateinitial, initblock, billingblock, minimal_time_charge ".
                "FROM pkg_plan ".
                "LEFT JOIN pkg_rate_agent ON pkg_rate_agent.id_plan=pkg_plan.id ".
                "LEFT JOIN pkg_prefix ON pkg_rate_agent.id_prefix=pkg_prefix.id ".
                "WHERE prefix = SUBSTRING('$calledstation',1,length(prefix)) and ".
                "pkg_plan.id='$MAGNUS->id_plan_agent' ORDER BY LENGTH(prefix) DESC ";

        $result = Yii::app()->db->createCommand( $sql )->queryAll();
        $agi->verbose($sql,25);


        


        if(!is_array($result))
            $sellratecost_customer = $cost;
        else
        {
            

            $customer_sell = $result[0]['rateinitial'];
            $customer_initblock = $result[0]['initblock'];
            $customer_billingblock = $result[0]['billingblock'];

            $buyratecallduration_agent = $sessiontime;
            if ($buyratecallduration_agent < $customer_initblock)
                $buyratecallduration_agent = $customer_initblock;
            if ($buyratecallduration_agent > $customer_initblock)
            {
                $mod_sec_agent = $buyratecallduration_agent % $customer_billingblock;
                if ($mod_sec_agent > 0)
                    $buyratecallduration_agent += ($customer_billingblock - $mod_sec_agent);
            }
            $sellratecost_customer -= ($buyratecallduration_agent / 60) * $customer_sell;
            $sellratecost_customer = $sellratecost_customer * -1;
        }

        if ( $sessiontime < $result[0]['minimal_time_charge'])
        {
             $agi->verbose("Tempo meno que o tempo minimo para",15);
            $sellratecost_customer = 0;
        }
            
        $agi->verbose( "Sellratecost_customer: $sellratecost_customer",15);

        $sql = "UPDATE pkg_user SET credit = credit - " . $cost . " WHERE id='" . $id_agent . "'";
        try {
             Yii::app()->db->createCommand( $sql )->execute(); 
        } catch (Exception $e) {
            $agi->verbose($e->getMessage(),1);
        }
        $agi->verbose($sql,25);
        $agi->verbose("Update credit Agent $MAGNUS->agentUsername, ".$cost,15);

        $sql = "UPDATE pkg_user SET credit= credit - ".$MAGNUS->round_precision(abs($sellratecost_customer))." $myclause_nodidcall,  lastuse=now() WHERE id='" . $MAGNUS->id_user . "'";
        try {
             Yii::app()->db->createCommand( $sql )->execute(); 
        } catch (Exception $e) {
            $agi->verbose($e->getMessage(),1);
        }
        $agi->verbose($sql,25);
        $agi->verbose("Update credit customer Agent $MAGNUS->username, ".$MAGNUS->round_precision(abs($sellratecost_customer)),6);
    
        return $sellratecost_customer;
        
    }

    function sendCall($agi, $destination, &$MAGNUS, $typecall = 0)
    {
        $max_long = 2147483647;

        if(substr("$destination", 0, 4) == 1111)/*Retira o techprefix de numeros portados*/
        {
            $destination = str_replace(substr($destination, 0, 7), "", $destination);
        }
        $old_destination = $destination;

        $sql = "UPDATE pkg_call_chart SET total = total + 1 WHERE date > '".date('Y-m-d H:i')."' ";
        $agi->verbose($sql,25);
        try {
             Yii::app()->db->createCommand( $sql )->execute(); 
        } catch (Exception $e) {
            $agi->verbose($e->getMessage(),1);
        }

        for ($k = 0; $k < count($this->tariffObj); $k++)
        {
            $destination = $old_destination;
            if ($this->tariffObj[$k]['id_trunk'] != '-1')
            {
                $this->usedtrunk = $this->tariffObj[$k]['id_trunk'];
                $usetrunk_failover = 1;
            }
            else
                return false;/*se nao tem tronco retornar erro*/


            $prefix         = $this->tariffObj[$k]['rc_trunkprefix'];
            $tech           = $this->tariffObj[$k]['rc_providertech'];
            $ipaddress      = $this->tariffObj[$k]['rc_providerip'];
            $removeprefix   = $this->tariffObj[$k]['rc_removeprefix'];
            $timeout        = $this->tariffObj[0]['timeout'];
            $failover_trunk = $this->tariffObj[$k]['rt_failover_trunk'];
            $addparameter   = $this->tariffObj[$k]['rt_addparameter_trunk'];
            $cidgroupid     = false;
            $inuse          = $this->tariffObj[$k]['inuse'];
            $maxuse         = $this->tariffObj[$k]['maxuse'];
            $allow_error    = $this->tariffObj[$k]['allow_error'];


            if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0)
                $destination = substr($destination, strlen($removeprefix));
            if ($typecall == 1)
                $timeout = 3600;

            $dialparams = str_replace("%timeout%", min($timeout * 1000, $max_long), $MAGNUS->agiconfig['dialcommand_param']);
            $dialparams = str_replace("%timeoutsec%", min($timeout, $max_long), $dialparams);

            $ramal = explode("-", $MAGNUS->channel);
            $ramal = explode("/", $ramal[0]);

            $sql = "SELECT directmedia FROM pkg_sip WHERE name='$ramal[1]'";
            $resultDirectmedia = Yii::app()->db->createCommand( $sql )->queryAll();
            $agi->verbose($sql,25);

            if ($resultDirectmedia[0]['directmedia'] == 'yes' && $this->tariffObj[$k]['rc_directmedia'] == 'yes')
            {
                $agi->verbose( "DIRECT MEDIA ACTIVE",10);
                $dialparams = preg_replace("/,L/", "", $dialparams);
                $dialparams = preg_replace("/,rRL/", "", $dialparams);
                $dialparams = preg_replace("/,RrL/", "", $dialparams);
            }

            if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1)
            {
                if(substr($MAGNUS->destination, 0, 4) == 1111)/*Retira o techprefix de numeros portados*/
                {
                    $number = str_replace(substr($MAGNUS->destination, 0, 7), "", $MAGNUS->destination);
                }else{
                    $number = $MAGNUS->destination;
                }

                $myres = $agi->execute("MixMonitor {$MAGNUS->username}.{$number}.{$MAGNUS->uniqueid}.".$MAGNUS->mix_monitor_format.",b");
                $agi->verbose("MixMonitor {$MAGNUS->username}.{$number}.{$MAGNUS->uniqueid}.".$MAGNUS->mix_monitor_format.",b",10);
            }


            $pos_dialingnumber = strpos($ipaddress, '%dialingnumber%');
            $ipaddress = str_replace("%cardnumber%", $MAGNUS->cardnumber, $ipaddress);
            $ipaddress = str_replace("%dialingnumber%", $prefix . $destination, $ipaddress);



            if ($pos_dialingnumber !== false)
                $dialstr = "$tech/$ipaddress" . $dialparams;
            else
            {
                if ($MAGNUS->agiconfig['switchdialcommand'] == 1)
                    $dialstr = "$tech/$prefix$destination@$ipaddress" . $dialparams;
                else
                    $dialstr = "$tech/$ipaddress/$prefix$destination" . $dialparams;
            }

            if (strlen($addparameter) > 0)
            {
                $addparameter = str_replace("%usename%", $MAGNUS->username, $addparameter);
                $addparameter = str_replace("%dialingnumber%", $prefix . $destination, $addparameter);
                $dialstr = "$tech/$ipaddress/$prefix$destination" . $addparameter;
            }

            $outcid = 0;

            if ($this->tariffObj[$k]['credit_control'] == 1 && $this->tariffObj[$k]['credit'] <= 0){
                $agi->verbose( "Provider not have credit",3);
                $this->tariffObj[$k]['status'] = 0;
            }


            if($this->tariffObj[$k]['status'] == 1)
            {
                if ($this->groupTrunk($agi, $ipaddress, $maxuse))
                {

                    $dialedpeername = $agi->get_variable("SIPTRANSFER");
                    $this->dialedpeername = $dialedpeername['data'];

                    if ($this->dialedpeername == 'yes')
                    {
                        $agi->execute("hangup request $this->channel");
                        $MAGNUS->hangup($agi);
                    }

                    $sql = "UPDATE pkg_trunk SET  call_total = call_total + 1 WHERE id='" . $this->usedtrunk . "'";
                    try {
                         Yii::app()->db->createCommand( $sql )->execute(); 
                    } catch (Exception $e) {
                        $agi->verbose($e->getMessage(),1);
                    }
                    $agi->verbose($sql,25);

                    try {
                        $myres = $MAGNUS->run_dial($agi, $dialstr);
                    } catch (Exception $e) {
                    }


                    $answeredtime            = $agi->get_variable("ANSWEREDTIME");
                    $this->real_answeredtime = $this->answeredtime = $answeredtime['data'];
                    $dialstatus              = $agi->get_variable("DIALSTATUS");
                    $this->dialstatus        = $dialstatus['data'];


                }
                else{
                    $agi->verbose('THE TRUNK '.$ipaddress.' CANNOT BE USED BECOUSE MAXIMUM NUMBER IS REACHED, SEND TO NEXT TRUNK');
                    $this->answeredtime = $answeredtime['data'] = 0;
                    $this->dialstatus = 'CONGESTION';
                }              

            }
            //if the trunk is inactive
            else{
                $agi->verbose('THE TRUNK '.$ipaddress.' IS INACTIVE, SEND TO NEXT TRUNK');
                $this->answeredtime = $answeredtime['data'] = 0;
                $this->dialstatus = 'CONGESTION';
            }

            if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1)
            {
                $myres = $agi->execute("StopMixMonitor");
                $agi->verbose( "EXEC StopMixMonitor (" . $MAGNUS->uniqueid . ")",6);
            }


            if ($allow_error == 1 && $this->dialstatus == "BUSY")
                $this->dialstatus = "CONGESTION";


            $loop_failover = 0;

            while ($loop_failover <= $MAGNUS->agiconfig['failover_recursive_limit'] 
                && is_numeric($failover_trunk) && $failover_trunk >= 0 
                && ($this->dialstatus == "CHANUNAVAIL" || $this->dialstatus == "CONGESTION"))
            {      


                $loop_failover++;
                $this->real_answeredtime = $this->answeredtime = 0;
                $this->usedtrunk = $failover_trunk;
                $agi->verbose( "K=$k -> ANSWEREDTIME=" . $this->answeredtime . "-DIALSTATUS=" . $this->dialstatus,10);
                $destination = $old_destination;
               
                $sql = "SELECT trunkprefix, providertech, providerip, removeprefix, failover_trunk, status, inuse, maxuse, if_max_use, allow_error, credit_control, credit FROM pkg_trunk LEFT JOIN pkg_provider ON pkg_trunk.id_provider = pkg_provider.id WHERE pkg_trunk.id='$failover_trunk'";
                $result = Yii::app()->db->createCommand( $sql )->queryAll();
                $agi->verbose($sql,25);
                
                if (is_array($result) && count($result) > 0)
                {
                    $prefix              = $result[0]['trunkprefix'];
                    $tech                = $result[0]['providertech'];
                    $ipaddress           = $result[0]['providerip'];
                    $removeprefix        = $result[0]['removeprefix'];
                    $next_failover_trunk = $result[0]['failover_trunk'];
                    $status              = $result[0]['status'];
                    $inuse               = $result[0]['inuse'];
                    $maxuse              = $result[0]['maxuse'];
                    $allow_error         = $result[0]['allow_error'];
              

                    if ($result[0]['credit_control'] == 1 && $result[0]['credit'] <= 0){
                        $agi->verbose( "Provider Backup not have credit",3);
                        $status = 0;
                  }

                    if ($status == 0)
                    {
                        $agi->verbose("Failover trunk cannot be used because it is disabled",3);
                        break;
                    }


                    $addparameter      = str_replace("15", "", $addparameter);
                    $pos_dialingnumber = strpos($ipaddress, '%dialingnumber%');
                    $ipaddress         = str_replace("%cardnumber%", $MAGNUS->cardnumber, $ipaddress);
                    $ipaddress         = str_replace("%dialingnumber%", $prefix . $destination, $ipaddress);
                    if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0)
                    {
                        $destination = substr($destination, strlen($removeprefix));
                    }
                    $agi->verbose("Now using failover trunk -> TRUNK => $ipaddress -> ERROR => $this->dialstatus ",6);
                    $dialparams = preg_replace("/\%timeout\%/", min($timeout * 1000, $max_long), $MAGNUS->agiconfig['dialcommand_param']);


                    if ($pos_dialingnumber !== false)
                    {
                        $dialstr = "$tech/$ipaddress" . $dialparams;
                    }
                    else
                    {
                        if ($MAGNUS->agiconfig['switchdialcommand'] == 1)
                        {
                            $dialstr = "$tech/$prefix$destination@$ipaddress" . $dialparams;
                        }
                        else
                        {
                            $dialstr = "$tech/$ipaddress/$prefix$destination" . $dialparams;
                        }
                    }

                    if (strlen($addparameter) > 0)
                    {
                        $addparameter = str_replace("%cardnumber%", $MAGNUS->cardnumber, $addparameter);
                        $addparameter = str_replace("%dialingnumber%", $prefix . $destination, $addparameter);
                        $dialstr = "$tech/$ipaddress/$prefix$destination/" . $addparameter;
                    }
                    $agi->verbose( "FAILOVER app_callingcard: Dialing '$dialstr' with timeout of '$timeout'.",15);

                    if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1)
                    {
                        if(substr($MAGNUS->destination, 0, 4) == 1111)/*Retira o techprefix de numeros portados*/
                        {
                            $number = str_replace(substr($MAGNUS->destination, 0, 7), "", $MAGNUS->destination);
                        }else{
                            $number = $MAGNUS->destination;
                        }

                        $myres = $agi->execute("MixMonitor {$MAGNUS->username}.{$number}.{$MAGNUS->uniqueid}.".$MAGNUS->mix_monitor_format.",b");
                        $agi->verbose("MixMonitor {$MAGNUS->username}.{$number}.{$MAGNUS->uniqueid}.".$MAGNUS->mix_monitor_format.",b",10);
                    }

                    if ($this->groupTrunk($agi, $ipaddress, $maxuse))
                    {

                        $sql = "UPDATE pkg_trunk SET  call_total = call_total + 1 WHERE id='" . $this->usedtrunk . "'";
                        try {
                             Yii::app()->db->createCommand( $sql )->execute(); 
                        } catch (Exception $e) {
                            $agi->verbose($e->getMessage(),1);
                        }
                        $agi->verbose($sql,25);

                        try {
                            $myres = $MAGNUS->run_dial($agi, $dialstr);
                        } catch (Exception $e) {
                        }


                        $agi->verbose( "DIAL FAILOVER $dialstr",10);                    

                        $answeredtime            = $agi->get_variable("ANSWEREDTIME");
                        $this->real_answeredtime = $this->answeredtime = $answeredtime['data'];
                        $dialstatus              = $agi->get_variable("DIALSTATUS");
                        $this->dialstatus        = $dialstatus['data'];

                    }                    
                    else{
                        $agi->verbose('THE TRUNK '.$ipaddress.' CANNOT BE USED BECOUSE MAXIMUM NUMBER IS REACHED, SEND TO NEXT TRUNK');
                        $this->answeredtime = $answeredtime['data'] = 0;
                        $this->dialstatus = 'CONGESTION';
                    }

                    if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1)
                    {
                        $myres = $agi->execute("StopMixMonitor");
                        $agi->verbose( "EXEC StopMixMonitor (" . $MAGNUS->uniqueid . ")",6);
                    }

                    if ($allow_error == 1 && $this->dialstatus == "BUSY")
                        $this->dialstatus = "CONGESTION";

                    $agi->verbose( "[FAILOVER K=$k]:[ANSTIME=" . $this->answeredtime . "-DIALSTATUS=" . $this->dialstatus,15);
                }
                /* IF THE FAILOVER TRUNK IS SAME AS THE ACTUAL TRUNK WE BREAK */
                if ($next_failover_trunk == $failover_trunk)
                    break;
                else
                    $failover_trunk = $next_failover_trunk;
            }

            if (($this->dialstatus == "CANCEL")) {
                return true;
            }

            if($this->tariffObj[$k]['status'] != 1)/*Change dialstatus of the trunk for send for LCR/LCD prefix*/
            {
                if ($MAGNUS->agiconfig['failover_lc_prefix'])
                    continue;
            }


            /* END FOR LOOP FAILOVER */
            /*# Ooh, something actually happened! */
            if ($this->dialstatus == "BUSY")
            {
                $this->real_answeredtime = $this->answeredtime = 0;
                if ($MAGNUS->play_audio == 1)
                    $agi->stream_file('prepaid-isbusy', '#');
                else{
                    $agi->execute((busy), busy);
                }

            } elseif ($this->dialstatus == "NOANSWER")
            {
                $this->real_answeredtime = $this->answeredtime = 0;
                if ($MAGNUS->play_audio == 1)
                    $agi->stream_file('prepaid-noanswer', '#');
                else{
                    $agi->execute((congestion), Congestion);
                }

            } elseif ($this->dialstatus == "CANCEL")
            {
                $this->real_answeredtime = $this->answeredtime = 0;
            } elseif (($this->dialstatus == "CHANUNAVAIL") || ($this->dialstatus == "CONGESTION"))
            {
                $this->real_answeredtime = $this->answeredtime = 0;
                /* Check if we will failover for LCR/LCD prefix - better false for an exact billing on resell */
                if ($MAGNUS->agiconfig['failover_lc_prefix'])
                {
                    $agi->verbose("Call send for backup trunk -> ERROR => $this->dialstatus",6);
                    continue;
                }
                return false;
            }

            $this->usedratecard = $k;
            $agi->verbose( "USED TARIFF=" . $this->usedratecard,10);
            return true;
        }
         /* End for */
        $this->usedratecard = $k - $loop_failover;
        $agi->verbose( "USEDRATECARD - FAIL =" . $this->usedratecard,10);
        return false;
    }    

    function groupTrunk($agi, $ipaddress, $maxuse)
    {
        if ($maxuse > 0 ) {
            
            $agi->verbose('Trunk have channels limit',8);
            //Set group to count the trunk call use
            $agi->set_variable("GROUP()",$ipaddress);
            
            $asmanager = new AGI_AsteriskManager();
            $asmanager->connect('localhost', 'magnus', 'magnussolution');
            
            $group = $asmanager->command("group show channels");
            $asmanager->disconnect();

            $arr = explode("\n", $group["data"]);
            $count = 0;
            if ($arr[0] != "")
            {
                foreach ($arr as $temp) {
                    $linha = explode("  ", $temp);                            

                    if (trim($linha[4]) == $ipaddress) {
                    
                        $channel = $asmanager->command("core show channel $linha[0]");
                        $arr2 = explode("\n", $channel["data"]);

                        foreach ($arr2 as $temp2)
                        {
                            if (strstr($temp2, 'State:'))
                            {
                                $arr3 = explode("State:", $temp2);
                                $status = trim(rtrim($arr3[1]));
                            }
                        }

                        if (preg_match("/Up |Ring /", $status)) {
                            $count++;
                        }
                        
                    }                   
                }                
            }

            if ($count > $maxuse){
                $agi->verbose('Trunk '. $ipaddress . ' have  ' . $count . ' calls, and the maximun call is '. $maxuse,2);
                return false;
            }                
            else{
                return true;
            }
        }
        else{
            return true;
        }
        
    }
};
?>