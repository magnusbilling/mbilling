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
class Magnus
{
    public $config;
    public $agiconfig;
    public $idconfig = 1;
    public $agentUsername;
    public $CallerID;
    public $channel;
    public $uniqueid;
    public $accountcode;
    public $dnid;
    public $extension;
    public $statchannel;
    public $destination;
    public $credit;
    public $id_plan;
    public $active;
    public $currency = 'usd';
    public $mode = '';
    public $timeout;
    public $tech;
    public $prefix;
    public $username;
    public $typepaid = 0;
    public $removeinterprefix = 1;
    public $restriction = 1;
    public $redial;
    public $enableexpire;
    public $expirationdate;
    public $expiredays;
    public $creationdate;
    public $creditlimit = 0;
    public $id_user;
    public $countryCode;
    public $add_credit;
    public $dialstatus_rev_list;
    public $callshop;
    public $id_plan_agent;
    public $id_offer;
    public $record_call;
    public $mix_monitor_format='gsm';
    public $prefix_local;
    public $id_agent;
    public $portabilidade=false;
    public $play_audio = false;


    public function Magnus()
    {
        $this->dialstatus_rev_list = $this->getDialStatus_Revert_List();
    }


    public function init()
    {
        $this->destination = '';
    }

    public function calculation_price($buyrate, $duration, $initblock, $increment)
    {

        $ratecallduration = $duration;
        $buyratecost = 0;
        if ($ratecallduration < $initblock)
            $ratecallduration = $initblock;
        if (($increment > 0) && ($ratecallduration > $initblock))
        {
            $mod_sec = $ratecallduration % $increment;
            if ($mod_sec > 0)
                $ratecallduration += ($increment - $mod_sec);
        }
        $ratecost -= ($ratecallduration / 60) * $buyrate;
        $ratecost = $ratecost * -1;
        return $ratecost;

    }
    //hangup($agi);
    public function hangup(&$agi)
    {
        $agi->verbose('Hangup Call '. $this->destination .' Username '. $this->username, 6);
        $agi->hangup();
        exit;
    }

    /*  load_conf */
    public function load_conf(&$agi, $config = null, $webui = 0, $idconfig = 1, $optconfig = array())
    {
        $this->idconfig = 1;
        $sql = "SELECT id, config_key as cfgkey, config_value as cfgvalue, config_group_title as cfggname FROM pkg_configuration";
        $config_res = Yii::app()->db->createCommand( $sql )->queryAll();
        $agi->verbose($sql,25);

        foreach ($config_res as $conf)
            $this->config[$conf['cfggname']][$conf['cfgkey']] = $conf['cfgvalue'];

        foreach ($optconfig as $var => $val)
            $this->config["agi-conf$idconfig"][$var] = $val;

        $this->agiconfig = $this->config["agi-conf$idconfig"];

        $sql       = "SELECT config_value FROM pkg_configuration WHERE config_key = 'MixMonitor_format'";
        $mix_monitor_format = Yii::app()->db->createCommand( $sql )->queryAll();

        $this->mix_monitor_format = $mix_monitor_format[0]['config_value'];

        return true;
    }


    public function get_agi_request_parameter($agi)
    {
        $this->accountcode = $agi->request['agi_accountcode'];
        $this->dnid = $agi->request['agi_extension'];        

        $this->CallerID = $agi->request['agi_callerid'];
        $this->channel = $agi->request['agi_channel'];
        $this->uniqueid = $agi->request['agi_uniqueid'];

        $this->lastapp = isset($agi->request['agi_lastapp']) ? $agi->request['agi_lastapp'] : null;

        $stat_channel = $agi->channel_status($this->channel);
        $this->statchannel = $stat_channel["data"];


        if (preg_match('/Local/', $this->channel) && strlen($this->accountcode) < 4) {
            $sql = "SELECT * FROM pkg_sip WHERE name='" . $this->dnid . "'";
            $result = Yii::app()->db->createCommand( $sql )->queryAll();
            $agi->verbose($sql,25);
            $this->accountcode = $result[0]['accountcode'];
        }


        $pos_lt = strpos($this->CallerID, '<');
        $pos_gt = strpos($this->CallerID, '>');
        if (($pos_lt !== false) && ($pos_gt !== false))
        {
            $len_gt = $pos_gt - $pos_lt - 1;
            $this->CallerID = substr($this->CallerID, $pos_lt + 1, $len_gt);
        }
        $msg = ' get_agi_request_parameter = ' . $this->statchannel . ' ; ' . $this->CallerID . ' ; ' . $this->channel . ' ; ' . $this->uniqueid . ' ; ' . $this->accountcode . ' ; ' . $this->dnid;
        $agi->verbose($msg,15);
    }

    public function getDialStatus_Revert_List()
    {
        $dialstatus_rev_list = array();
        $dialstatus_rev_list["ANSWER"] = 1;
        $dialstatus_rev_list["BUSY"] = 2;
        $dialstatus_rev_list["NOANSWER"] = 3;
        $dialstatus_rev_list["CANCEL"] = 4;
        $dialstatus_rev_list["CONGESTION"] = 5;
        $dialstatus_rev_list["CHANUNAVAIL"] = 6;
        $dialstatus_rev_list["DONTCALL"] = 7;
        $dialstatus_rev_list["TORTURE"] = 8;
        $dialstatus_rev_list["INVALIDARGS"] = 9;
        return $dialstatus_rev_list;
    }

    public function checkNumber($agi, &$Calc, $try_num, $call2did = false)
    {
        $res = 0;
        $prompt_enter_dest = 'prepaid-enter-dest';
        $msg = "use_dnid:" . $this->agiconfig['use_dnid'] . " && len_dnid:(" . strlen($this->
            dnid) . " || len_exten:" . strlen($this->extension) . " ) && (try_num:$try_num)";
        $agi->verbose($msg,15 );


        if (($this->agiconfig['use_dnid'] == 1) && $try_num == 0)
        {
            if ($this->extension == 's')
            {
                $this->destination = $this->dnid;
            }
            else
            {
                $this->destination = $this->extension;
            }
            $agi->verbose( "USE_DNID DESTINATION -> " . $this->destination,10);
        }
        else
        {
            $agi->verbose($prompt_enter_dest,25);
            $res_dtmf = $agi->get_data($prompt_enter_dest, 6000, 20);
            $agi->verbose( "RES DTMF -> " . $res_dtmf["result"],10);
            $this->destination = $res_dtmf["result"];
            $this->dnid = $res_dtmf["result"];
        }

        $this->destination = preg_replace("/\-/", "", $this->destination);
        $this->dnid = preg_replace("/\-/", "", $this->dnid);
        $this->destination = preg_replace("/\./", "", $this->destination);
        $this->dnid = preg_replace("/\./", "", $this->dnid);
        $this->destination = preg_replace("/\(/", "", $this->destination);
        $this->dnid = preg_replace("/\(/", "", $this->dnid);
        $this->destination = preg_replace("/\)/", "", $this->destination);
        $this->dnid = preg_replace("/\)/", "", $this->dnid);    


        if (strlen($this->destination) <= 2 && is_numeric($this->destination) && $this->destination >= 0)
        {
            $sql = "SELECT phone FROM pkg_speeddial WHERE id_user='" . $this->id_user . "' AND speeddial='" . $this->destination . "'";
            $result = Yii::app()->db->createCommand( $sql )->queryAll();
            $agi->verbose($sql,25);
            if (is_array($result))
            {
                $this->destination = $result[0][0];
                $this->dnid = $this->destination;
            }
            $agi->verbose( "SPEEDIAL REPLACE DESTINATION -> " . $this->destination,6);
        }

        /*verifica tamanho do numero nao permite numeros menor que 12*/
        if (strlen($this->destination) < 12 && substr($this->dnid, 0, 2) == '55')
        {
            if ($this->play_audio == 0)
            {
                $agi->execute((congestion), Congestion);
            }
            else
            {
                $agi->answer();
                $agi->stream_file('repaid-dest-unreachable', '#');
             }
            $agi->verbose("NUMBER is < 12 digits->" . $this->destination,3);
            $this->hangup($agi);
        }

        if ($this->removeinterprefix)
        {
            $this->destination = substr($this->destination, 0,2) == '00' ? substr($this->destination,2) : $this->destination;
            $agi->verbose( "REMOVE INTERNACIONAL PREFIX -> " . $this->destination,10);
        }

        $this->destination = $this->transform_number_ar_br($agi, $this->destination);       

        
        if($this->dnid == 150)
        {
            $agi->verbose( "SAY BALANCE : $MAGNUS->credit ",10);
            $this->sayBalance($agi, $this->credit);
            
            $prompt = "prepaid-final";
            $agi->verbose( $prompt,10);
            $agi->stream_file($prompt, '#');
            $this->hangup($agi);
        }
        if($this->dnid == 160)
        {
            $sql = "SELECT sessionbill, sessiontime FROM pkg_cdr WHERE id_user = " . $this->id_user ." ORDER BY  starttime DESC LIMIT 1";;
            $result = Yii::app()->db->createCommand( $sql )->queryAll();
            $agi->verbose($sql,25);
            $agi->verbose( "SAY PRICE LAST CALL : ". $result[0]['sessionbill'] ,1);
            $this->sayLastCall($agi, $result[0]['sessionbill'], $result[0]['sessiontime']);
            
            $agi->stream_file('prepaid-final', '#');
            $this->hangup($agi);
        }
        

        
        if($this->restriction == 1 || $this->restriction == 2)
        {
            /*Check if Account have restriction*/
            $sql = "SELECT * FROM pkg_restrict_phone WHERE id_user = '" . $this->id_user . "' AND number = SUBSTRING('" . $this->destination . "',1,length(number)) ORDER BY LENGTH(number) DESC";
            $result = Yii::app()->db->createCommand( $sql )->queryAll();
            $agi->verbose($sql,25);

            $agi->verbose( "RESTRICTED NUMBERS ".$sql,15);

            if ($this->restriction == 1)
            {
                /* NOT ALLOW TO CALL RESTRICTED NUMBERS*/
                if (count($result) > 0)
                {
                    /* NUMBER NOT AUHTORIZED*/
                    $agi->verbose("NUMBER NOT AUHTORIZED - NOT ALLOW TO CALL RESTRICTED NUMBERS",1);
                    $agi->answer();
                    $agi->stream_file('prepaid-dest-unreachable', '#');
                    $this->hangup($agi);
                }
            }
            else if ($this->restriction == 2)
            {
                /* ALLOW TO CALL ONLY RESTRICTED NUMBERS */
                if(count($result) == 0)
                {
                    /*NUMBER NOT AUHTORIZED*/
                    $agi->verbose("NUMBER NOT AUHTORIZED - ALLOW TO CALL ONLY RESTRICTED NUMBERS",1);
                    $agi->answer();
                    $agi->stream_file('prepaid-dest-unreachable', '#');
                    $this->hangup($agi);
                }
            }
        }


        $this->destination = rtrim($this->destination, "#");
        if ($this->destination <= 0)
        {
            $prompt = "prepaid-invalid-digits";
            $agi->verbose($prompt,3);
            if (is_numeric($this->destination))
                $agi->answer();

            $agi->stream_file($prompt, '#');
            $this->hangup($agi);
        }
        $this->destination = str_replace('*', '', $this->destination);
        $this->save_redial_number($agi, $this->destination);
        $data = date("d-m-y");
        $agi->verbose( "USERNAME=" . $this->username . " DESTINATION=" . $this->destination . " PLAN=" . $this->id_plan . " CREDIT=" . $this->credit,6);

        if ($this->play_audio == 0)
        {
            $check_credit = $this->credit + $this->creditlimit;
            if ($check_credit <= 0)
            {
                $agi->verbose("SEND :: congestion Credit < 0",3);
                $agi->execute((congestion), Congestion);
                $this->hangup($agi);
            }
        }

        $agi->destination = $this->destination;
        /*call funtion for search rates*/
        $resfindrate = SearchTariff::find($this, $agi, $Calc);        

        if ($resfindrate == 0)
        {
            $agi->verbose("The number $this->destination, no exist in the plan $this->id_plan",3);
            if ($this->play_audio == 0)
            {
                $agi->verbose("OK - SEND  Congestion destination no found",3);
                $agi->execute((congestion), Congestion);
                $this->hangup($agi);
            }
            else
            {
                $agi->answer();
                $prompt = "prepaid-dest-unreachable";
                $agi->verbose("destination no found",3);
                $agi->stream_file($prompt, '#');
                $this->hangup($agi);
            }
        }
        else
        {
            $agi->verbose( "NUMBER TARIFF FOUND -> " . $resfindrate,10);
        }

        /* CHECKING THE TIMEOUT*/
        $agi->verbose( $this->credit . $resfindrate,10);
        $res_all_calcultimeout = $Calc->calculateAllTimeout($this, $this->credit,$agi);
        $agi->verbose( "NUMBER TARIFF FOUND -> " . $resfindrate,10);

        if($this->id_agent > 1)
        {
            $agi->verbose( "Check reseller credit -> " . $this->id_agent . ' credit '. $this->credit,20);
            $check_agent_credit = CreditUser :: checkGlobalCredit($this->id_agent);
        }

        if (!$res_all_calcultimeout || ( isset($check_agent_credit) &&  $check_agent_credit == false ) )
        {
            if ($this->play_audio == 0)
            {
                $agi->verbose("OK - SEND  Congestion destination no found, Customer no have credit",3);
                $agi->execute((congestion), Congestion);
                $this->hangup($agi);
            }
            $prompt = "prepaid-no-enough-credit";
            $agi->verbose("Customer no have credit",3);
            $agi->answer();
            $agi->stream_file($prompt, '#');
            return - 1;
        }

        /* calculate timeout*/
        $this->timeout = $Calc->tariffObj[0]['timeout'];
        $timeout = $this->timeout;
        $agi->verbose( "timeout ->> $timeout",15);
        $minimal_time_charge = $Calc->tariffObj[0]['minimal_time_charge'];
        $this->say_time_call($agi, $timeout, $Calc->tariffObj[0]['rateinitial']);

        return 1;        
    }

    public function call_sip($agi, &$Calc, $try_num, $dnid, $call_transfer_pstn)
    {
        $res = 0;

        if (($this->agiconfig['use_dnid'] == 1) && (strlen($this->dnid) > 2))
        {
            $this->destination = $this->dnid;
        }

        $this->save_redial_number($agi, $this->destination);

        $sip_buddies = 0;

        $sip_buddies = 1;
        $destsip = $this->dnid;

        $this->tech = 'SIP';
        $this->destination = $this->dnid;

        if ($this->agiconfig['record_call'] == 1 || $this->record_call == 1)
        {
            $command_mixmonitor = "MixMonitor {$this->CallerID}.{$this->destination}.{$this->uniqueid}.".$this->mix_monitor_format.",b";
            $myres = $agi->execute($command_mixmonitor);
            $agi->verbose( $command_mixmonitor,6);
        }


        $dialparams = $this->agiconfig['dialcommand_param_sipiax_friend'];

        $sql = "SELECT name, pkg_user.username, ipaddr FROM pkg_sip, pkg_user WHERE pkg_sip.id_user=pkg_user.id AND name='" . $this->destination . "'";
        $result = Yii::app()->db->createCommand( $sql )->queryAll();
        $agi->verbose($sql,25);

        /*IF call for username ring all sipfriend*/
        $sql2 = "SELECT name FROM pkg_sip WHERE accountcode ='" . $result[0]['name'] . "'";
        $result2 = Yii::app()->db->createCommand( $sql2 )->queryAll();
        $agi->verbose($sql,25);

        if(count($result2) > 1)
        {
            $dialstr = '';
            for ($i = 0; $i < count($result2); $i++)
            {
                $dialstr .= $this->tech . "/" . $result2[$i]['name'] . "&";
            }
            $dialstr = substr($dialstr, 0, -1);
        }
        else
        {
            $dialstr = $this->tech . "/" . $this->destination;
        }

        $sql ="SELECT * FROM pkg_servers WHERE host = '".$result[0]['ipaddr']."' AND type = 'freeswitch'";
        $resultFS = Yii::app()->db->createCommand( $sql )->queryAll();
        $agi->verbose($sql,25);
        if (count($resultFS) > 0) {
            $dialstr .= '@'.$result[0]['ipaddr'];
        }

        $sql ="SELECT * FROM pkg_servers WHERE status = 1 AND type = 'asterisk'";
        $resultAsterisk = Yii::app()->db->createCommand( $sql )->queryAll();
        $agi->verbose($sql,1);
        if (count($resultAsterisk) > 0) {
            $sql = "SELECT register_server_ip, regseconds FROM pkg_sip WHERE name ='" . $this->destination . "'";
            $resultOtherServer = Yii::app()->db->createCommand( $sql )->queryAll();
            $agi->verbose($sql,1);
            if (strlen($resultOtherServer[0]['register_server_ip']) > 1 && $regseconds[0]['regseconds'] < (time() - 7200) ) {
                //usuario esta registrado no server principal
                $dialstr .= '@'.$resultOtherServer[0]['register_server_ip'];
            }
        }
      


        $dialstr .= $this->config["agi-conf1"]['dialcommand_param_sipiax_friend'];

        $myres = $this->run_dial($agi, $dialstr);
        $agi->verbose( "DIAL $dialstr",6);

        $answeredtime = $agi->get_variable("ANSWEREDTIME");
        $answeredtime = $answeredtime['data'];
        $dialstatus = $agi->get_variable("DIALSTATUS");
        $dialstatus = $dialstatus['data'];

        if ($this->agiconfig['record_call'] == 1 || $this->record_call == 1)
        {
            $myres = $agi->execute("StopMixMonitor");
            $agi->verbose( "EXEC StopMixMonitor (" . $this->uniqueid . ")",6);
        }

        $agi->verbose( "[" . $this->tech . " Friend][K=$k]:[ANSWEREDTIME=" . $answeredtime . "-DIALSTATUS=" . $dialstatus . "]",6);


        if ($dialstatus == "BUSY")
        {
            $answeredtime = 0;
            /* Modificado para permitir voicemail cuando la extension este ocupada*/
            $buddyb = $this->destination;
            $agi->answer();
            $agi->execute(VoiceMail, $buddyb."@billing");
        } elseif ($this->dialstatus == "NOANSWER")
        {
            $answeredtime = 0;
            /* Modificado para permitir VoiceMail entre SIP friends*/
            ;
            $buddyu = $this->destination;
            $agi->answer();
            $agi->execute(VoiceMail, $buddyu."@billing");
        } elseif ($dialstatus == "CANCEL")
        {
            $answeredtime = 0;
        } elseif ($dialstatus == "ANSWER")
        {
            $msg = "-> dialstatus : $dialstatus, answered time is " . $answeredtime . " ";
            $agi->verbose( $msg,15);
        } elseif ($k + 1 == $sip_buddies + $iax_buddies)
        {
            $prompt = "prepaid-dest-unreachable";
            $buddy = $this->destination;
            $agi->answer();
            $agi->execute(VoiceMail, $buddy."@billing");
        }

        if (($dialstatus == "CHANUNAVAIL") || ($dialstatus == "CONGESTION"))
            continue;

        if (strlen($this->dialstatus_rev_list[$dialstatus]) > 0)
            $terminatecauseid = $this->dialstatus_rev_list[$dialstatus];
        else
            $terminatecauseid = 0;

        $siptransfer = $agi->get_variable("SIPTRANSFER");
        if ($answeredtime > 0 && $siptransfer['data'] != 'yes' && $terminatecauseid == 1)
        {
            if ($this->config['global']['charge_sip_call'] > 0) {

                $cost = ($this->config['global']['charge_sip_call'] / 60) * $answeredtime;

                $sql = "UPDATE pkg_user SET credit= credit - " . $cost . ", lastuse=now() WHERE id='" . $this->id_user . "'";
                Yii::app()->db->createCommand( $sql )->execute();
                $agi->verbose( $sql,25);
                $agi->verbose("Update credit username $this->username, ".$cost,15);
            }else{
                $cost = 0;
            }

            $sql = "INSERT INTO pkg_cdr (uniqueid, sessionid, id_user, starttime, sessiontime, calledstation, " .
                " terminatecauseid, stoptime, id_plan, id_trunk, src, sipiax, id_prefix, ".
                " buycost,  sessionbill) VALUES " . "('" . $this->uniqueid . "', '" . $this->channel . "', '" . $this->id_user . "',";
            $sql .= " CURRENT_TIMESTAMP - INTERVAL $answeredtime SECOND ";
            $sql .= ", '$answeredtime', '" . $this->destination . "', '$terminatecauseid', now(), '" . $this->id_plan. "', 
             '0', '" .$this->username. "', '1' , NULL, '0', $cost)";
            $agi->verbose($sql,25);
            Yii::app()->db->createCommand( $sql )->execute();

            
            
            return 1;
        }else{

            $fields = "uniqueid, sessionid, id_user, starttime,  calledstation, terminatecauseid, ".
            " id_plan, id_trunk, src, sipiax, id_prefix";

            $value = "'$this->uniqueid', '$this->channel', $this->id_user, now(), ".
            " '$this->destination', '$terminatecauseid', ".
            "NULL, NULL, '$this->CallerID', '0', NULL";
            
            $sql = "INSERT INTO pkg_cdr_failed ($fields) VALUES ($value)";
            $agi->verbose($sql,25);
            Yii::app()->db->createCommand( $sql )->execute();
            return 1;
        }


        /*AND ACTIVE TRANSFER BETEWEN CUSTOMERS*/
        if (($dialstatus == "CHANUNAVAIL") || ($dialstatus == "CONGESTION") || ($dialstatus == "NOANSWER"))
        {
            /* The following section will send the caller to VoiceMail with the unavailable priority.*/
            $agi->verbose("CHANNEL UNAVAILABLE - GOTO VOICEMAIL ($dest_username)",6);
            $agi->answer();
            $agi->stream_file("vm-intro", '#');
            $vm_parameters = $dest_username . '@billing,u';
            $agi->execute(VoiceMail, $vm_parameters);
        }

        if (($dialstatus == "BUSY"))
        {
            $agi->verbose("CHANNEL BUSY - GOTO VOICEMAIL ($dest_username)",6);
            $agi->answer();
            $vm_parameters = $dest_username . '@billing,b';
            $agi->stream_file("vm-intro", '#');
            $agi->execute(VoiceMail, $vm_parameters);
        }

        return - 1;
    }

    public function call_did($agi, &$Calc, $listdestination, $destinationIvr = false)
    {
        $res = 0;

        $this->agiconfig['say_timetocall'] = 0;

        //altera o destino do did caso ele venha de uma IVR
        $listdestination[0]['destination'] = $destinationIvr ? $destinationIvr : $listdestination[0]['destination'];



        $callcount = 0;


        foreach ($listdestination as $inst_listdestination)
        {

            $agi->verbose(print_r($inst_listdestination, true),10);

            $callcount++;
            $msg = "[Magnus] DID call friend: FOLLOWME=$callcount (username:".$inst_listdestination['username']."|destination:".$inst_listdestination['destination']."|id_plan:".$inst_listdestination['id_plan'] .")";
            $agi->verbose($msg,10);

            $this->agiconfig['cid_enable'] = 0;
            $this->accountcode             = $this->username = $inst_listdestination['username'];
            $this->id_plan                 = $inst_listdestination['id_plan'];
            $this->destination             = $inst_listdestination['destination'];
            $did                           = $inst_listdestination['did'];

            if (AGI_Authenticate::authenticateUser($agi, $this) != 0)
            {
                $msg = "DID AUTHENTICATION ERROR";
            }
            else
            {               

                /* IF SIP CALL*/
                if ($inst_listdestination['voip_call'] == 1)
                {
                    $agi->verbose("DID call friend: IS LOCAL !!!",10);

                    $sql = "SELECT name FROM pkg_sip WHERE id = ". $inst_listdestination['id_sip'];
                    $agi->verbose( $sql,25);
                    $resultSip = Yii::app()->db->createCommand( $sql )->queryAll();

                    if (count($resultSip) > 0 ) {
                        $inst_listdestination['destination'] =  "SIP/".$resultSip[0]['name'] ;
                    }else{
                        $agi->stream_file('prepaid-dest-unreachable', '#');
                        continue;
                    }
                    

                    if ($this->agiconfig['record_call'] == 1 || $this->record_call == 1)
                    {
                        $destino = $inst_listdestination['destination'];
                        $command_mixmonitor = "MixMonitor {$this->accountcode}.{$did}.{$this->uniqueid}.".$this->mix_monitor_format.",b";
                        $myres = $agi->execute($command_mixmonitor);
                        $agi->verbose( $command_mixmonitor,6);
                    }


                    $max_long = 2147483647;
                    $time2call = 3600;
                    $dialparams = $this->agiconfig['dialcommand_param_call_2did'];
                    $dialparams = str_replace("%timeout%", min($time2call * 1000, $max_long), $dialparams);
                    $dialparams = str_replace("%timeoutsec%", min($time2call, $max_long), $dialparams);
                    $dialstr = $inst_listdestination['destination'] . $dialparams;

                    $myres = $this->run_dial($agi, $dialstr);
                    $agi->verbose( "DIAL $dialstr",6);

                    $answeredtime = $agi->get_variable("ANSWEREDTIME");
                    $answeredtime = $answeredtime['data'];
                    $dialstatus = $agi->get_variable("DIALSTATUS");
                    $dialstatus = $dialstatus['data'];

                    if ($this->agiconfig['record_call'] == 1  || $this->record_call == 1)
                    {
                        $myres = $agi->execute("StopMixMonitor");
                        $agi->verbose( "EXEC StopMixMonitor (" . $this->uniqueid . ")",6);
                    }

                    $agi->verbose( $inst_listdestination['destination'] . " Friend -> followme=$callcount : ANSWEREDTIME=" . $answeredtime . "-DIALSTATUS=" . $dialstatus,6);

                    if ($dialstatus == "BUSY")
                    {
                        $answeredtime = 0;
                        $agi->stream_file('prepaid-isbusy', '#');
                        if (count($listdestination) > $callcount)
                            continue;
                    }
                    elseif ($dialstatus == "NOANSWER")
                    {
                        $answeredtime = 0;
                        $agi->stream_file('prepaid-callfollowme', '#');
                        if (count($listdestination) > $callcount)
                            continue;
                    }
                    elseif ($dialstatus == "CANCEL")
                    {
                        return 1;
                    }
                    elseif ($dialstatus == "ANSWER")
                    {
                        $agi->verbose( "[Magnus] DID call friend: dialstatus : $dialstatus, answered time is " . $answeredtime . " ",10);
                    }
                    elseif (($dialstatus == "CHANUNAVAIL") || ($dialstatus == "CONGESTION"))
                    {
                        $answeredtime = 0;
                        if (count($listdestination) > $callcount)
                            continue;
                    }
                    else
                    {
                        $agi->stream_file('prepaid-callfollowme', '#');
                        if (count($listdestination) > $callcount)
                            continue;
                    }
                    

                    /* ELSEIF NOT VOIP CALL*/
                }
                /* Call to group*/
                else if ($inst_listdestination['voip_call'] == 8)
                {            
             
                    $agi->verbose("Ccall group $group ",6);
                    $max_long = 2147483647;
                    $time2call = 3600;
                    $dialparams = $this->agiconfig['dialcommand_param_call_2did'];
                    $dialparams = str_replace("%timeout%", min($time2call * 1000, $max_long), $dialparams);
                    $dialparams = str_replace("%timeoutsec%", min($time2call, $max_long), $dialparams);

                    $sql = "SELECT name FROM pkg_sip WHERE `group` = '". $inst_listdestination['destination']."'";
                    $agi->verbose( $sql,25);
                    $resultGroup = Yii::app()->db->createCommand( $sql )->queryAll();
                    $group = '';
                    foreach ($resultGroup as $key => $value) {
                        $group .= "SIP/".$value['name']."&";
                    }
                    $dialstr = substr($group, 0,-1).$dialparams;
                    
                    $agi->verbose( "DIAL $dialstr",6);
                    $myres = $this->run_dial($agi, $dialstr);

                    $answeredtime = $agi->get_variable("ANSWEREDTIME");
                    $answeredtime = $answeredtime['data'];
                    $dialstatus = $agi->get_variable("DIALSTATUS");
                    $dialstatus = $dialstatus['data'];

                    
                }
                /* Call to custom dial*/
                else if ($inst_listdestination['voip_call'] == 9)
                {      
             
                    $agi->verbose("Ccall group $group ",6);                   
                    $dialstr = $inst_listdestination['destination'];
                    $agi->verbose( "DIAL $dialstr",6);
                    $myres = $this->run_dial($agi, $dialstr);

                    $answeredtime = $agi->get_variable("ANSWEREDTIME");
                    $answeredtime = $answeredtime['data'];
                    $dialstatus = $agi->get_variable("DIALSTATUS");
                    $dialstatus = $dialstatus['data'];
                    
                }
                else
                {
                    /* CHECK IF DESTINATION IS SET*/
                    if (strlen($inst_listdestination['destination']) == 0)
                        continue;

                    $this->agiconfig['use_dnid'] = 1;
                    $this->agiconfig['say_timetocall'] = 0;

                    $this->extension = $this->dnid = $this->destination = $inst_listdestination['destination'];
                    $agi->verbose("UPDATE DID -> $this->extension",10);
                    if ($this->checkNumber($agi, $Calc, 0) == 1)
                    {

                        /* PERFORM THE CALL*/
                        $result_callperf = $Calc->sendCall($agi, $this->destination, $this);
                        if (!$result_callperf)
                        {
                            $prompt = "prepaid-callfollowme";
                            $agi->verbose($prompt,10);
                            $agi->stream_file($prompt, '#');
                            continue;
                        }

                        $dialstatus = $Calc->dialstatus;
                        $answeredtime = $Calc->answeredtime;

                        if (($Calc->dialstatus == "NOANSWER") || ($Calc->dialstatus == "BUSY") || ($Calc->dialstatus == "CHANUNAVAIL") || ($Calc->dialstatus == "CONGESTION"))
                            continue;

                        if ($Calc->dialstatus == "CANCEL")
                            break;

                        
                        /* INSERT CDR  & UPDATE SYSTEM*/
                        $Calc->updateSystem($this, $agi, $this->destination, 1, 1);

                        /* CC_DID & CC_DID_DESTINATION - pkg_did.id, pkg_did_destination.id*/
                        $sql = "UPDATE pkg_did SET secondusedreal = secondusedreal + " . $Calc->answeredtime . " WHERE id='" . $inst_listdestination['id'] . "'";
                        Yii::app()->db->createCommand( $sql )->execute();
                        $agi->verbose($sql,25);

                        $sql = "UPDATE pkg_did_destination SET secondusedreal = secondusedreal + " . $Calc->answeredtime . " WHERE id='" . $inst_listdestination[1] . "'";
                        Yii::app()->db->createCommand( $sql )->execute();
                        $agi->verbose($sql,25);

                        /* THEN STATUS IS ANSWER*/
                        break;
                    }
                }
            }
            /* END IF AUTHENTICATE*/

        }
        /* END FOR*/

        $this->voicemail = 1;
        if ($this->voicemail)
        {
            if (($dialstatus == "CHANUNAVAIL") || ($dialstatus == "CONGESTION") || ($dialstatus == "NOANSWER") || ($dialstatus == "BUSY"))
            {
                /* The following section will send the caller to VoiceMail with the unavailable priority.\*/
                $dest_username = $this->username;
                
                $agi->answer();
                $agi->stream_file("vm-intro", '#');
                $vm_parameters = $dest_username . '@billing,s';
                $agi->verbose("CHANNEL ($dialstatus) - GOTO VOICEMAIL ($dest_username)",6);
                $agi->execute(VoiceMail, $vm_parameters);
            }
        }

        if ($answeredtime > 0)
        {
            $this->call_did_billing($agi, $Calc, $inst_listdestination, $answeredtime, $dialstatus);                        
            return 1;
        }
    }

    public function call_did_billing($agi, $Calc, $listdestination, $answeredtime, $dialstatus)
    {

        $connection_sell = $listdestination['connection_sell'];

        //brazil mobile - ^[4,5,6][1-9][7-9].{7}$|^[1,2,3,7,8,9][1-9]9.{8}$
        //brazil fixed - ^[1-9][0-9][1-5].
        $agi->verbose(print_r($listdestination,true));
        if ( strlen($listdestination['expression_1']) > 0 && ereg($listdestination['expression_1'], $this->CallerID) || $listdestination['expression_1'] == '*') {
            $agi->verbose("encontrou a expression1 no callerid". $this->CallerID,10);
            $selling_rate = $listdestination['selling_rate_1'];

        }elseif ( strlen($listdestination['expression_2']) > 0 &&  ereg($listdestination['expression_2'], $this->CallerID) || $listdestination['expression_2'] == '*' ) {
            $agi->verbose("encontrou a expression2 no callerid". $this->CallerID,10);
            $selling_rate = $listdestination['selling_rate_2'];
        }
        elseif ( strlen($listdestination['expression_3']) > 0 &&  ereg($listdestination['expression_3'], $this->CallerID) || $listdestination['expression_3'] == '*' ) {
            $agi->verbose("encontrou a expression3 no callerid". $this->CallerID,10);
            $selling_rate = $listdestination['selling_rate_3'];
        }
        else{
            $selling_rate = 0;   
        }

        if ($connection_sell == 0 && $selling_rate == 0)
            $sell_price = 0;
        else
        {
        
            $selling_rate = $selling_rate;
            $customer_initblock = $listdestination['initblock'];
            $customer_billingblock = $listdestination['increment'];

            $ratecallduration = $answeredtime;
            if ($ratecallduration < $customer_initblock)
                $ratecallduration = $customer_initblock;
            if ($ratecallduration > $customer_initblock)
            {
                $mod_sec_agent = $ratecallduration % $customer_billingblock;
                if ($mod_sec_agent > 0)
                    $ratecallduration += ($customer_billingblock - $mod_sec_agent);
            }
            $sell_price = ($ratecallduration / 60) * $selling_rate;
        }

        $sell_price = $sell_price + $connection_sell;

        if ($answeredtime < $listdestination['minimal_time_charge'])
        {
            $sell_price = 0;
        }

        $agi->verbose(' answeredtime = ' . $answeredtime . ' selling_rate = ' . $selling_rate . ' connection_sell = ' .$connection_sell,10);


        if (strlen($this->dialstatus_rev_list[$dialstatus]) > 0)
            $terminatecauseid = $this->dialstatus_rev_list[$dialstatus];
        else
            $terminatecauseid = 0;  


        /*recondeo call*/
        if ($this->config["global"]['bloc_time_call']== 1 && $sell_price > 0 )
        {
            $initblock = $listdestination['initblock'];
            $billingblock = $listdestination['increment'];

            if ($answeredtime > $initblock)
            {
                $restominutos = $answeredtime % $billingblock;
                $calculaminutos = ($answeredtime - $restominutos) / $billingblock;
                if ($restominutos > '0')
                    $calculaminutos++;
                $answeredtime = $calculaminutos * $billingblock;

            } elseif ($answeredtime < '1')
                $sessiontime = 0;

            else
                $answeredtime = $initblock;
        } 


        $sql = "SELECT id FROM pkg_prefix WHERE prefix = SUBSTRING('".$listdestination['did']."',1,length(prefix))LIMIT 1";
        $resultPrefix = Yii::app()->db->createCommand( $sql )->queryAll();
        $id_prefix = $resultPrefix[0]['id'];

        $fields = "uniqueid, sessionid, id_user, starttime, sessiontime, real_sessiontime, calledstation, terminatecauseid, ".
        "stoptime, sessionbill, id_plan, id_trunk, src, sipiax, buycost, id_prefix";

        $value = "'$this->uniqueid', '$this->channel', $this->id_user, CURRENT_TIMESTAMP - INTERVAL $answeredtime SECOND, ".
        "'$answeredtime', $answeredtime, '".$listdestination['did']."', '$terminatecauseid', now(),'$sell_price', ".
        "$this->id_plan, NULL, '$this->CallerID', '3', '0', $id_prefix";
        
        $sql = "INSERT INTO pkg_cdr ($fields) VALUES ($value)";
        $agi->verbose($sql,25);
        try {
            Yii::app()->db->createCommand( $sql )->execute();
        } catch (Exception $e) {
            
        }

        $sql = "UPDATE pkg_user SET credit= credit - " . $this->round_precision(abs($sell_price)) . " WHERE id='" . $this->id_user . "'";
        Yii::app()->db->createCommand( $sql )->execute();
        $agi->verbose($sql,25);

        $sql = "UPDATE pkg_did SET secondusedreal = secondusedreal + $answeredtime WHERE id='" . $inst_listdestination['id'] . "'";
        Yii::app()->db->createCommand( $sql )->execute();
        $agi->verbose($sql,25);

        $sql = "UPDATE pkg_did_destination SET secondusedreal = secondusedreal + $answeredtime WHERE id='" . $inst_listdestination['id_destination'] . "'";
        Yii::app()->db->createCommand( $sql )->execute();
        $agi->verbose($sql,25);

        

        return;
    }

    public function say_time_call($agi, $timeout, $rate = 0)
    {
        $minutes = intval($timeout / 60);
        $seconds = $timeout % 60;

        $agi->verbose( "TIMEOUT->" . $timeout . " : minutes=$minutes - seconds=$seconds",6);
       

        if ($this->agiconfig['say_rateinitial'] == 1)
        {
            $this->sayRate($agi, $rate);
        }

        if ($this->agiconfig['say_timetocall'] == 1)
        {
            $agi->stream_file('prepaid-you-have', '#');
            if ($minutes > 0)
            {
                if ($minutes == 1)
                {
                    $agi->say_number($minutes);
                    $agi->stream_file('prepaid-minute', '#');
                }
                else
                {
                    $agi->say_number($minutes);
                    $agi->stream_file('prepaid-minutes', '#');
                }
            }
            if ($seconds > 0)
            {
                if ($minutes > 0)
                    $agi->stream_file('vm-and', '#');
                if ($seconds == 1)
                {
                    $agi->say_number($seconds);
                    $agi->stream_file('prepaid-second', '#');
                }
                else
                {
                    $agi->stream_file('prepaid-seconds', '#');
                }
            }
        }
    }

    public function sayBalance($agi, $credit, $fromvoucher = 0)
    {

        $mycur = 1;

        $credit_cur = $credit / $mycur;

        list($units, $cents) = split('[.]', sprintf('%01.2f', $credit_cur));

        $agi->verbose( "[BEFORE: $credit_cur SPRINTF : " . sprintf('%01.2f', $credit_cur) . "]",10);

        if($credit>1)
            $unit_audio = "credit";
        else
            $unit_audio = "credits";

        $cents_audio = "prepaid-cents";

        switch ($cents_audio)
        {
            case 'prepaid-pence':
                $cent_audio = 'prepaid-penny';
                break;
            default:
                $cent_audio = substr($cents_audio, 0, -1);
        }

        /* say 'you have x dollars and x cents'*/
        if ($fromvoucher != 1)
            $agi->stream_file('prepaid-you-have', '#');
        else
            $agi->stream_file('prepaid-account_refill', '#');

        if ($units == 0 && $cents == 0)
        {
            $agi->say_number(0);
            $agi->stream_file($unit_audio, '#');
        }
        else
        {
            if ($units > 1)
            {
                $agi->say_number($units);
                $agi->stream_file($unit_audio, '#');
            }
            else
            {
                $agi->say_number($units);
                $agi->stream_file($unit_audio, '#');
            }

            if ($units > 0 && $cents > 0)
            {
                $agi->stream_file('vm-and', '#');
            }
            if ($cents > 0)
            {
                $agi->say_number($cents);
                if ($cents > 1)
                {
                    $agi->stream_file($cents_audio, '#');
                }
                else
                {
                    $agi->stream_file($cent_audio, '#');
                }

            }
        }
    }

    public function sayLastCall($agi, $rate, $time = 0)
    {
        $rate = preg_replace("/\./", "z", $rate);
        $array = str_split( $rate);
        $agi->stream_file('prepaid-cost-call', '#');
        for ($i=0; $i < strlen($rate); $i++) {            
            if ($array[$i] == 'z'){
                $agi->stream_file('prepaid-point', '#');
                $cents = true;
            }                
            else
                $agi->say_number($array[$i]);
                  
            
        }
        if ($cents) {
            $agi->stream_file('prepaid-cents', '#');
        }

        if($time > 0){
            $agi->say_number($time);
            $agi->stream_file('prepaid-seconds', '#');            
        }
    }

    public function sayRate($agi, $rate)
    {
        $rate = 0.008;   

        $mycur = 1;
        $credit_cur = $rate / $mycur;

        list($units, $cents) = split('[.]', sprintf('%01.3f', $credit_cur));

        if (substr($cents, 2) > 0)
            $point = substr($cents, 2);
        if (strlen($cents) > 2){
            $cents = substr($cents, 0, 2);
        }
           


        if ($units == '')
            $units = 0;
        if ($cents == '')
            $cents = 0;
        if ($point == '')
            $point = 0;
        elseif (strlen($cents) == 1)
            $cents .= '0';


        if($rate>1)
            $unit_audio = "credit";
        else
            $unit_audio = "credits";

        $cent_audio = 'prepaid-cent';
        $cents_audio = 'prepaid-cents';

        /* say 'the cost of the call is '*/
        $agi->stream_file('prepaid-cost-call', '#');
        $this->agiconfig['play_rate_cents_if_lower_one'] = 1;
        if ($units == 0 && $cents == 0 && $this->agiconfig['play_rate_cents_if_lower_one'] == 0 && !($this->agiconfig['play_rate_cents_if_lower_one'] == 1 && $point == 0))
        {
            $agi->say_number(0);
            $agi->stream_file($unit_audio, '#');
        }
        else
        {
            if ($units >= 1)
            {
                $agi->say_number($units);
                $agi->stream_file($unit_audio, '#');
            }
            elseif ($this->agiconfig['play_rate_cents_if_lower_one'] == 0)
            {
                $agi->say_number($units);
                $agi->stream_file($unit_audio, '#');
            }

            if ($units > 0 && $cents > 0)
            {
                $agi->stream_file('vm-and', '#');
            }
            if ($cents > 0 || ($point > 0 && $this->agiconfig['play_rate_cents_if_lower_one'] == 1))
            {

                sleep(2);
                $agi->say_number($cents);
                if ($point > 0)
                {
                    $agi->stream_file('prepaid-point', '#');
                    $agi->say_number($point);
                }
                if ($cents > 1)
                {
                    $agi->stream_file($cents_audio, '#');
                }
                else
                {
                    $agi->stream_file($cent_audio, '#');
                }
            }
        }
    }

    public function checkDaysPackage($agi, $startday, $billingtype)
    {
        if ($billingtype == 0)
        {
            /* PROCESSING FOR MONTHLY*/
            /* if > last day of the month*/
            if ($startday > date("t"))
                $startday = date("t");
            if ($startday <= 0)
                $startday = 1;

            /* Check if the startday is upper that the current day*/
            if ($startday > date("j"))
                $year_month = date('Y-m', strtotime('-1 month'));
            else
                $year_month = date('Y-m');

            $yearmonth = sprintf("%s-%02d", $year_month, $startday);
            $CLAUSE_DATE = " TIMESTAMP(date_consumption) >= TIMESTAMP('$yearmonth')";
        }
        else
        {

            /* PROCESSING FOR WEEKLY*/
            $startday = $startday % 7;
            $dayofweek = date("w");
            /* Numeric representation of the day of the week 0 (for Sunday) through 6 (for Saturday)*/
            if ($dayofweek == 0)
                $dayofweek = 7;
            if ($dayofweek < $startday)
                $dayofweek = $dayofweek + 7;
            $diffday = $dayofweek - $startday;
            $CLAUSE_DATE = "date_consumption >= DATE_SUB(CURRENT_DATE, INTERVAL $diffday DAY) ";
        }

        return $CLAUSE_DATE;
    }

    public function freeCallUsed($agi, $id_user, $id_offer, $billingtype, $startday)
    {
        
        $CLAUSE_DATE = $this->checkDaysPackage($agi,$startday,$billingtype);

        $sql = "SELECT  COUNT(*) AS number_calls FROM pkg_offer_cdr " . "WHERE $CLAUSE_DATE AND id_user = '$id_user' AND id_offer = '$id_offer' ";
        $pack_result = Yii::app()->db->createCommand( $sql )->queryAll();
        $agi->verbose($sql,25);
    
        return count($pack_result ) > 0 ? $pack_result[0]['number_calls'] : 0;
    }

    public function packageUsedSeconds($agi, $id_user, $id_offer, $billingtype, $startday)
    {
        $CLAUSE_DATE = $this->checkDaysPackage($agi,$startday,$billingtype);
        $sql = "SELECT sum(used_secondes) AS used_secondes FROM pkg_offer_cdr " . "WHERE $CLAUSE_DATE AND id_user = '$this->id_user' AND id_offer = '$id_offer' ";
        $agi->verbose($sql,25);
        $pack_result = Yii::app()->db->createCommand( $sql )->queryAll();
        
        return count($pack_result ) > 0 ? $pack_result[0]['used_secondes'] : 0;
    }

    public function apply_rules($phonenumber)
    {
        $this->agiconfig['international_prefixes'] = explode(',', $this->agiconfig['international_prefixes']);
        if (is_array($this->agiconfig['international_prefixes']) && (count($this->agiconfig['international_prefixes']) > 0))
        {
            foreach ($this->agiconfig['international_prefixes'] as $testprefix)
            {
                if (substr($phonenumber, 0, strlen($testprefix)) == $testprefix)
                    return substr($phonenumber, strlen($testprefix));
            }
        }

        if (substr($phonenumber, 0,1) == 0) {
            $phonenumber = substr($phonenumber, 1);
        }

        return $phonenumber;
    }


    public function check_expirationdate_customer()
    {
        if($this->enableexpire == 1 && $this->expirationdate != '00000000000000' && strlen($this->expirationdate) > 5)
        {
            /* expire date */
            if(intval($this->expirationdate - time()) < 0) /* CARD EXPIRED :( */
                $prompt = "prepaid-card-expired";
        } elseif ($this->enableexpire == 3 && $this->creationdate != '00000000000000' && strlen($this->creationdate) > 5 && ($this->expiredays > 0))
        {
            /* expire days since creation */
            $date_will_expire = $this->creationdate + (60 * 60 * 24 * $this->expiredays);
            if(intval($date_will_expire - time()) < 0) /* CARD EXPIRED :( */
                $prompt = "prepaid-card-expired";
        }
        /*Update card status to Expired */
        if($prompt == "prepaid-card-expired")
        {
            $this->active = 0;
            $sql = "UPDATE pkg_user SET status='0' WHERE id='" . $this->id_user . "'";
            Yii::app()->db->createCommand( $sql )->execute();
            $agi->verbose($sql,25);
        }
        return $prompt;
    }

    /*  public function splitable_data used by parameter like interval_len_cardnumbe : 8-10, 12-18, 20 it will build an array with the different interval */
    public function splitable_data($splitable_value)
    {
        $arr_splitable_value = explode(",", $splitable_value);
        foreach ($arr_splitable_value as $arr_value)
        {
            $arr_value = trim($arr_value);
            $arr_value_explode = explode("-", $arr_value, 2);
            if (count($arr_value_explode) > 1)
            {
                if (is_numeric($arr_value_explode[0]) && is_numeric($arr_value_explode[1]) && $arr_value_explode[0] < $arr_value_explode[1])
                {
                    for ($kk = $arr_value_explode[0]; $kk <= $arr_value_explode[1]; $kk++)
                    {
                        $arr_value_to_import[] = $kk;
                    }
                } elseif (is_numeric($arr_value_explode[0]))
                {
                    $arr_value_to_import[] = $arr_value_explode[0];
                } elseif (is_numeric($arr_value_explode[1]))
                {
                    $arr_value_to_import[] = $arr_value_explode[1];
                }

            }
            else
            {
                $arr_value_to_import[] = $arr_value_explode[0];
            }
        }

        $arr_value_to_import = array_unique($arr_value_to_import);
        sort($arr_value_to_import);
        return $arr_value_to_import;
    }

    public function save_redial_number($agi, $number)
    {
        if (($this->mode == 'did') || ($this->mode == 'callback'))
        {
            return;
        }
        $sql = "UPDATE pkg_user SET redial = '{$number}' WHERE username='" . $this->accountcode . "'";
        Yii::app()->db->createCommand( $sql )->execute();
        $agi->verbose($sql,25);
    }

    public function run_dial($agi, $dialstr)
    {
        /* Run dial command */
        if (strlen($this->agiconfig['amd']) > 0) {
            $dialstr .=$this->agiconfig['amd'];
        }
        $res_dial = $agi->execute("DIAL $dialstr");

        return $res_dial;
    }

    public function transform_number_ar_br($agi, $number)
    {

        if (!preg_match("/,/", $this->prefix_local)) {

            if ($this->config['global']['base_country'] == 'ARG' || $this->config['global']['base_country'] == 'arg')
            {               

                if(strlen($this->prefix_local) == 2)
                    $this->prefix_local = '0/54,*/54'.$this->prefix_local.'/8,15/549'.$this->prefix_local.'/10,16/549'.$this->prefix_local.'/10';
                elseif (strlen($this->prefix_local) == 3)
                    $this->prefix_local = '0/54,*/54'.$this->prefix_local.'/7,15/549'.$this->prefix_local.'/9';             
                elseif(strlen($this->prefix_local) == 4)
                    $this->prefix_local = '0/54,*/54'.$this->prefix_local.'/6,15/549'.$this->prefix_local.'/9';             

                $sql = "UPDATE pkg_user set prefix_local = '$this->prefix_local' WHERE id = ".$this->id_user ;          
                Yii::app()->db->createCommand( $sql )->execute();
                $agi->verbose($sql,25);             

            }

            if ($this->config['global']['base_country'] == 'brl' || $this->config['global']['base_country'] == 'BRL')
            {
                if(strlen($this->prefix_local) == 2)
                    $this->prefix_local = '0/55,*/55'.$this->prefix_local.'/8,*/55'.$this->prefix_local.'/9';       
            }

            if ($this->config['global']['base_country'] == 'NLD')
            {
                if(strlen($this->prefix_local) == 0)
                    $this->prefix_local = '0/31/10'; 
            }

            if ($this->config['global']['base_country'] == 'ESP')
            {
                if(strlen($this->prefix_local) == 0)
                    $this->prefix_local = '6/34/9,9/34/9';               
            }

            if ($this->config['global']['base_country'] == 'ITA')
            {
                if(strlen($this->prefix_local) == 0)
                    $this->prefix_local = '3/39/10,0/39/10';                 
            }

            if ($this->config['global']['base_country'] == 'MEX')
            {
                if(strlen($this->prefix_local) == 0)
                    $this->prefix_local = '01/52/12,04/521/13,*,521,10';                 
            }

            $sql = "UPDATE pkg_user set prefix_local = '$this->prefix_local' WHERE id = ".$this->id_user ;          
            Yii::app()->db->createCommand( $sql )->execute();
            $agi->verbose($sql,25);
        }


        $number = $this->number_translation($agi,$this->prefix_local,$number);

        $number = Portabilidade::consulta($agi, $this, $number);

        return $number;
    }

    

    public function number_translation($agi,$translation,$destination)
    {
        #match / replace / if match length 
        #0/54,4/543424/7,15/549342/9

        //$translation = "0/54,*/5511/8,15/549342/9";

    
        $regexs = split(",", $translation);

        foreach ($regexs as $key => $regex) {
    
            $regra = split( '/', $regex );
            $grab = $regra[0];
            $replace = $regra[1];
            $digit =$regra[2];
                        
            $agi->verbose("Grab :$grab Replacement: $replace Phone Before: $destination",25);
            
            $number_prefix = substr($destination,0,strlen($grab));

            if ($this->config['global']['base_country'] == 'brl' || $this->config['global']['base_country'] == 'BRL' || $this->config['global']['base_country'] == 'ARG' || $this->config['global']['base_country'] == 'arg')
            {
                if ($grab == '*' && strlen($destination) == $digit) {
                    $destination = $replace.$destination;
                }
                else if (strlen($destination) == $digit && $number_prefix == $grab) {
                    $destination = $replace.substr($destination,strlen($grab));
                }
                elseif ($number_prefix == $grab)
                {
                    $destination = $replace.substr($destination,strlen($grab));
                }
                       
            }else{                  

                if (strlen($destination) == $digit) {           
                    if ($grab == '*' && strlen($destination) == $digit) {
                        $destination = $replace.$destination;
                    }
                    else if ( $number_prefix == $grab) {
                        $destination = $replace.substr($destination,strlen($grab));
                    }
                } 
            }
        }

        $agi->verbose("Phone After translation: $destination",10);

        return $destination;
    } 

    public function sqliteInsert($agi,$fields,$value,$table)
    {

        $sql = "INSERT INTO $table ($fields) VALUES ($value)";
        $create = false;
        $cache_path = '/etc/asterisk/cache_mbilling.sqlite';
            
        try {
            $db = new SQLite3($cache_path);

            $db->exec('CREATE TABLE IF NOT EXISTS '.$table.' ('.$fields.');');           
            $db->exec($sql);

        } catch (Exception $e) {
            $agi->verbose("\n\nError to connect to cache : $sqliteerror\n\n");
        }     
    }  

    public function round_precision($number)
    {
        $PRECISION = 6;
        return round($number, $PRECISION);
    }
};
?>