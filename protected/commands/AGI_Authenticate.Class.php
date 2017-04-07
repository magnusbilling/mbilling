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
 
class AGI_Authenticate
{
    public function authenticateUser($agi, $MAGNUS)
    {
        $agi->verbose('authenticateUser',15);
        $authentication = false;
        $prompt = '';
        $res = 0;
        $retries = 0;
        $language = 'br';
        $callerID_enable = $MAGNUS->agiconfig['cid_enable'];
        $prompt_entercardnum = "prepaid-enter-pin-number";

        
        /*FIRST TRY WITH THE CALLERID AUTHENTICATION    -%-%-%-%-%-%-*/
        if ($callerID_enable == 1 && is_numeric($MAGNUS->CallerID) && $MAGNUS->CallerID > 0)
        {
            $sql = "SELECT a.cid, a.id_user, a.activated, b.credit, ".
                    "b.id_plan, b.active, b.typepaid, b.creditlimit, b.language, b.username,".
                    "removeinterprefix, b.redial, enableexpire, UNIX_TIMESTAMP(expirationdate), ".
                    "expiredays, UNIX_TIMESTAMP(b.creationdate), b.id, b.restriction, ".
                    "b.id_user, b.callshop, b.id_offer, b.record_call, b.prefix_local , b.country ".
                    "FROM pkg_callerid AS a ".
                    "LEFT JOIN pkg_user AS b ON a.id_user=b.id ".
                    "LEFT JOIN pkg_plan ON b.id_plan=pkg_plan.id ".
                    "WHERE a.cid='" . $MAGNUS->CallerID . "'";
            $agi->verbose($sql,25);
            $result = Yii::app()->db->createCommand( $sql )->queryAll();
            if (count($result) > 0)
            {
                $callerid_activated           = $result[0]['activated'];
                $MAGNUS->credit               = $result[0]['credit'];
                $MAGNUS->id_plan              = $result[0]['id_plan'];
                $MAGNUS->active               = $result[0]['active'];
                $MAGNUS->typepaid             = $result[0]['typepaid'];
                $MAGNUS->creditlimit          = $result[0]['creditlimit'];
                $language                     = $result[0]['language'];
                $MAGNUS->accountcode          = $result[0]['username'];
                $MAGNUS->username             = $result[0]['username'];
                $MAGNUS->removeinterprefix    = $result[0]['removeinterprefix'];
                $MAGNUS->redial               = $result[0]['redial'];
                $MAGNUS->enableexpire         = $result[0]['enableexpire'];
                $MAGNUS->expirationdate       = $result[0]['expirationdate'];
                $MAGNUS->expiredays           = $result[0]['expiredays'];
                $MAGNUS->creationdate         = $result[0]['creationdate'];
                $MAGNUS->id_user              = $result[0]['id'];
                $MAGNUS->id_agent             = $result[0]['id_user'];
                $MAGNUS->restriction          = $result[0]['restriction'];
                $MAGNUS->callshop             = $result[0]['callshop'];
                $MAGNUS->id_offer             = $result[0]['id_offer'];
                $MAGNUS->record_call          = $result[0]['record_call'];
                $MAGNUS->prefix_local         = $result[0]['prefix_local'];
                $MAGNUS->countryCode          = $result[0]['country'];   

                $authentication = true;
                $agi->verbose("AUTHENTICATION BY CALLERID:" . $MAGNUS->CallerID,6);
            }
        }

        /*SECOUND TRY WITH THE ACCOUNTCODE AUTHENTICATION  -%-%-%-%-%-%-*/
        if (strlen($MAGNUS->accountcode) >= 1 && $authentication != true)
        {
            $MAGNUS->username = $MAGNUS->accountcode;

            $sql = "SELECT credit, id_plan, active, typepaid, creditlimit, language, removeinterprefix, ".
                    "redial, enableexpire, UNIX_TIMESTAMP(expirationdate) expirationdate, expiredays, ".
                    "UNIX_TIMESTAMP(a.creationdate) creationdate, a.id, a.restriction, a.id_user, ".
                    "a.callshop, a.id_offer, a.record_call, a.prefix_local, a.country ".
                    "FROM pkg_user AS a ".
                    "LEFT JOIN pkg_plan ON id_plan=pkg_plan.id WHERE username='" . $MAGNUS->username . "'";
            $agi->verbose($sql,25);
            $result = Yii::app()->db->createCommand( $sql )->queryAll();
            if (count($result) > 0)
            {
                $MAGNUS->credit               = $result[0]['credit'];
                $MAGNUS->id_plan              = $result[0]['id_plan'];
                $MAGNUS->active               = $result[0]['active'];
                $MAGNUS->typepaid             = $result[0]['typepaid'];
                $MAGNUS->creditlimit          = $result[0]['creditlimit'];
                $language                     = $result[0]['language'];
                $MAGNUS->removeinterprefix    = $result[0]['removeinterprefix'];
                $MAGNUS->redial               = $result[0]['redial'];
                $MAGNUS->enableexpire         = $result[0]['enableexpire'];
                $MAGNUS->expirationdate       = $result[0]['expirationdate'];
                $MAGNUS->expiredays           = $result[0]['expiredays'];
                $MAGNUS->creationdate         = $result[0]['creationdate'];
                $MAGNUS->id_user              = $result[0]['id'];
                $MAGNUS->id_agent             = $result[0]['id_user'];
                $MAGNUS->restriction          = $result[0]['restriction'];
                $MAGNUS->callshop             = $result[0]['callshop'];
                $MAGNUS->id_offer             = $result[0]['id_offer'];
                $MAGNUS->record_call          = $result[0]['record_call'];
                $MAGNUS->prefix_local         = $result[0]['prefix_local'];
                $MAGNUS->countryCode          = $result[0]['country'];

                $authentication = true;
                $agi->verbose( "AUTHENTICATION BY ACCOUNTCODE:" . $MAGNUS->username, 6);
            }


            //AUTHENTICATION VIA TECHPREFIX
            if (strlen($MAGNUS->dnid) > 16 && $authentication == true) {

                $tech_prefix = substr($MAGNUS->dnid, 0 ,6);

                //verifico se tem algum cliente com este tech
                $sql = "SELECT pkg_user.username, credit, id_plan, active, typepaid, creditlimit, language, removeinterprefix, redial,
                        enableexpire, UNIX_TIMESTAMP(expirationdate), expiredays,
                        UNIX_TIMESTAMP(pkg_user.creationdate), pkg_user.lastname, pkg_user.firstname, pkg_user.email,
                        pkg_user.password, pkg_user.id, pkg_user.restriction, pkg_user.id_user,
                        pkg_user.callshop, pkg_user.id_offer, pkg_user.record_call, pkg_user.prefix_local, pkg_user.country
                        FROM pkg_user LEFT JOIN pkg_plan ON id_plan=pkg_plan.id
                        WHERE callingcard_pin='" . $tech_prefix . "'";
                $result = Yii::app()->db->createCommand( $sql )->queryAll();
                $agi->verbose($sql,25);


                $from = $agi->get_variable("SIP_HEADER(Contact)", true);

                $from = explode('@', $from);
                $from = explode('>', $from[1]);
                $from = explode(':', $from[0]);
                $from = $from[0];

                //verifico se este cliente com tech, é autenticado por ip, e se o ip de origem é do cliente
                $sql = "SELECT * FROM pkg_sip WHERE id_user = '".$result[0]['id']."' AND host = '$from'";
                $resultSIP = Yii::app()->db->createCommand( $sql )->queryAll();
                $agi->verbose($sql,25);

                if (count($result) > 0 && count($resultSIP) > 0)
                {
                    $MAGNUS->credit               = $result[0]['credit'];
                    $MAGNUS->id_plan              = $result[0]['id_plan'];
                    $MAGNUS->active               = $result[0]['active'];
                    $MAGNUS->typepaid             = $result[0]['typepaid'];
                    $MAGNUS->creditlimit          = $result[0]['creditlimit'];
                    $language                     = $result[0]['language'];
                    $MAGNUS->removeinterprefix    = $result[0]['removeinterprefix'];
                    $MAGNUS->redial               = $result[0]['redial'];
                    $MAGNUS->enableexpire         = $result[0]['enableexpire'];
                    $MAGNUS->expirationdate       = $result[0]['expirationdate'];
                    $MAGNUS->expiredays           = $result[0]['expiredays'];
                    $MAGNUS->creationdate         = $result[0]['creationdate'];
                    $MAGNUS->id_user              = $result[0]['id'];
                    $MAGNUS->id_agent             = $result[0]['id_user'];
                    $MAGNUS->restriction          = $result[0]['restriction'];
                    $MAGNUS->callshop             = $result[0]['callshop'];
                    $MAGNUS->id_offer             = $result[0]['id_offer'];
                    $MAGNUS->record_call          = $result[0]['record_call'];
                    $MAGNUS->prefix_local         = $result[0]['prefix_local'];
                    $MAGNUS->countryCode          = $result[0]['country'];
                    $MAGNUS->username             = $result[0]['username'];

                    $authentication = true;

                    $MAGNUS->accountcode = $MAGNUS->username;
                    
                    $MAGNUS->dnid = substr($MAGNUS->dnid, 6);
                    $agi->verbose( "AUTHENTICATION BY TECHPREFIX $tech_prefix - Username: " . $MAGNUS->username . '  '.$MAGNUS->dnid,6);
                }
            }
        }
        if ($authentication == false && !filter_var( $agi->get_variable( "SIP_HEADER(X-AUTH-IP)", true ), FILTER_VALIDATE_IP ) === false ) {
            $agi->verbose( "TRY Authentication via Proxy " );
            $agi->verbose( $agi->get_variable( "SIP_HEADER(P-Accountcode)", true ), 1 );
            $proxyServer = explode("@",$agi->get_variable( "SIP_HEADER(to)", true ));
            $proxyServer = isset($proxyServer[1]) ? substr($proxyServer[1],0, -1) : '';
            $sql = "SELECT * FROM pkg_servers WHERE host = '". $proxyServer."'";
            $result_proxy = Yii::app()->db->createCommand( $sql )->queryAll();
            
            if (count($result_proxy) > 0) {
                if ( $agi->get_variable( "SIP_HEADER(P-Accountcode)", true ) == '<null>' ) {
                    
                    $sql = "SELECT * FROM pkg_sip WHERE host = '". $agi->get_variable( "SIP_HEADER(X-AUTH-IP)", true )."'";
                    $result_SIP = Yii::app()->db->createCommand( $sql )->queryAll();
                    $agi->verbose( $sql, 1 );
                    if ( count( $result_SIP ) > 0 ) {
                        $MAGNUS->accountcode = $result_SIP[0]['accountcode'];                   
                        $agi->set_variable( 'USERNAME', $MAGNUS->accountcode );
                        $agi->verbose( "Authentication via X-AUTH-IP header (".$agi->get_variable( "SIP_HEADER(X-AUTH-IP)", true )."), accountcode" .$MAGNUS->accountcode);
                        $authentication = 'try';
                    }
                }else { 
                    $MAGNUS->accountcode = $agi->get_variable( "SIP_HEADER(P-Accountcode)", true );
                    $agi->verbose( "Authentication via P-Accountcode header " . $MAGNUS->accountcode);              
                    $authentication = 'try';
                }

                if ( $authentication == 'try') {
                    $MAGNUS->username = $MAGNUS->accountcode;

                    $sql = "SELECT credit, id_plan, active, typepaid, creditlimit, language, removeinterprefix, ".
                            "redial, enableexpire, UNIX_TIMESTAMP(expirationdate), expiredays, ".
                            "UNIX_TIMESTAMP(a.creationdate), a.id, a.restriction, a.id_user, ".
                            "a.callshop, a.id_offer, a.record_call, a.prefix_local, a.country ".
                            "FROM pkg_user AS a ".
                            "LEFT JOIN pkg_plan ON id_plan=pkg_plan.id WHERE username='" . $MAGNUS->username . "'";
                    $agi->verbose($sql,25);
                    $result = Yii::app()->db->createCommand( $sql )->queryAll();
                    if (count($result) > 0)
                    {
                        $MAGNUS->credit               = $result[0]['credit'];
                        $MAGNUS->id_plan              = $result[0]['id_plan'];
                        $MAGNUS->active               = $result[0]['active'];
                        $MAGNUS->typepaid             = $result[0]['typepaid'];
                        $MAGNUS->creditlimit          = $result[0]['creditlimit'];
                        $language                     = $result[0]['language'];
                        $MAGNUS->removeinterprefix    = $result[0]['removeinterprefix'];
                        $MAGNUS->redial               = $result[0]['redial'];
                        $MAGNUS->enableexpire         = $result[0]['enableexpire'];
                        $MAGNUS->expirationdate       = $result[0]['expirationdate'];
                        $MAGNUS->expiredays           = $result[0]['expiredays'];
                        $MAGNUS->creationdate         = $result[0]['creationdate'];
                        $MAGNUS->id_user              = $result[0]['id'];
                        $MAGNUS->id_agent             = $result[0]['id_user'];
                        $MAGNUS->restriction          = $result[0]['restriction'];
                        $MAGNUS->callshop             = $result[0]['callshop'];
                        $MAGNUS->id_offer             = $result[0]['id_offer'];
                        $MAGNUS->record_call          = $result[0]['record_call'];
                        $MAGNUS->prefix_local         = $result[0]['prefix_local'];
                        $MAGNUS->countryCode          = $result[0]['country'];

                        $authentication = true;
                        $agi->verbose( "AUTHENTICATION BY SIPPROXY:" . $MAGNUS->username, 1);
                    }
                }
            }else{
                $agi->verbose( "Try send call with X-AUTH-IP, BUT IS INVALID ",$sql,1 );
            }
        
        }

        /* AUTHENTICATE BY PIN */
        if ($authentication == false)
        {
            $agi->verbose('try callingcard',6);

            for ($retries = 0; $retries < 3; $retries++)
            {
                $agi->answer();

                if (($retries > 0) && (strlen($prompt) > 0))
                {
                    $agi->verbose($prompt,3);
                    $agi->stream_file($prompt, '#');
                }
                if ($res < 0)
                {
                    $res = -1;
                    break;
                }

                $res = 0;

                $res_dtmf = $agi->get_data($prompt_entercardnum, 6000, 6);
                $agi->verbose('PIN callingcard '.$res_dtmf["result"],20);

                $MAGNUS->username = $res_dtmf["result"];
 
                if (!isset($MAGNUS->username) || strlen($MAGNUS->username) == 0)
                {
                    $prompt = "prepaid-no-card-entered";
                    $agi->verbose('No user entered',6);
                    continue;
                }

                if (strlen($MAGNUS->username) > 6 || strlen($MAGNUS->username) < 6)
                {
                    $agi->verbose($prompt,6);
                    $prompt = "prepaid-invalid-digits";
                    continue;
                }
                $MAGNUS->accountcode = $MAGNUS->username;

                $sql = "SELECT credit, id_plan, active, typepaid, creditlimit, language, removeinterprefix, redial,
                        enableexpire, UNIX_TIMESTAMP(expirationdate), expiredays,
                        UNIX_TIMESTAMP(pkg_user.creationdate), pkg_user.lastname, pkg_user.firstname, pkg_user.email,
                        pkg_user.password, pkg_user.username, pkg_user.id, pkg_user.restriction, pkg_user.id_user,
                        pkg_user.callshop, pkg_user.id_offer, pkg_user.record_call, pkg_user.prefix_local, pkg_user.country
                        FROM pkg_user LEFT JOIN pkg_plan ON id_plan=pkg_plan.id
                        WHERE callingcard_pin='" . $MAGNUS->username . "'";
                $result = Yii::app()->db->createCommand( $sql )->queryAll();
                $agi->verbose($sql,20);

                if (is_array($result) && count($result) > 0)
                {
                    $MAGNUS->username          = $result[0]['username'];
                    $MAGNUS->credit            = $result[0]['credit'];
                    $MAGNUS->id_plan           = $result[0]['id_plan'];
                    $MAGNUS->active            = $result[0]['active'];                    
                    $MAGNUS->typepaid          = $result[0]['typepaid'];
                    $MAGNUS->creditlimit       = $result[0]['creditlimit'];
                    $language                  = $result[0]['language'];
                    $MAGNUS->removeinterprefix = $result[0]['removeinterprefix'];
                    $MAGNUS->enableexpire      = $result[0]['enableexpire'];
                    $MAGNUS->expirationdate    = $result[0]['expirationdate'];
                    $MAGNUS->expiredays        = $result[0]['expiredays'];
                    $MAGNUS->creationdate      = $result[0]['creationdate'];
                    $MAGNUS->id_user           = $result[0]['id'];
                    $MAGNUS->restriction       = $result[0]['restriction'];
                    $MAGNUS->id_agent          = $result[0]['id_user'];
                    $MAGNUS->callshop          = $result[0]['callshop'];   
                    $MAGNUS->id_offer          = $result[0]['id_offer'];
                    $MAGNUS->record_call       = $result[0]['record_call'];
                    $MAGNUS->prefix_local      = $result[0]['prefix_local'];
                    $MAGNUS->countryCode       = $result[0]['country'];

                    $authentication = true;
                    $MAGNUS->accountcode = $MAGNUS->username;
                    $agi->verbose("AUTHENTICATION BY PIN:" . $MAGNUS->CallerID,6);
                    break;
                }
                else
                {
                    //check if the PIN is a valid voucher
                    $sql = "SELECT * FROM pkg_voucher WHERE voucher = '" . $MAGNUS->username . "' AND used = 0";
                    $result = Yii::app()->db->createCommand( $sql )->queryAll();
                    $agi->verbose($sql,25);
                    if (is_array($result) && count($result) > 0)
                    {
                        $agi->verbose(print_r($result,true));
                        $user = AGI_Authenticate::getNewUsername($agi);
                        $pass = AGI_Authenticate::gerar_senha(8, true, true, true, false);
                        //Cria conta para usuario com voucher
                        $fields= "id_user, username, id_group, id_plan, password, credit, 
                        active, callingcard_pin, callshop, plan_day, boleto_day, language, prefix_local";
                        $values = "1,'".$user."', 3,'".$result[0]['id_plan']."', '".$pass."', '".$result[0]['credit']."', 
                        1, '".$result[0]['voucher']."', 0, 0,0, '".$result[0]['language']."', '".$result[0]['prefix_local']."'";
                        $sql = "INSERT INTO pkg_user ($fields) VALUES ($values)";
                        $agi->verbose($sql,25);
                        try {
                            Yii::app()->db->createCommand( $sql )->execute();
                            $id_user = Yii::app()->db->lastInsertID;
                        } catch (Exception $e) {
                            $agi->verbose( $e->getMessage(),25);
                        }


                        //Marca o voucher como usado
                        $sql = "UPDATE pkg_voucher SET id_user = $id_user, usedate = NOW(), used = 1 WHERE voucher = '" . $MAGNUS->username . "'";
                        $agi->verbose($sql,25);
                        Yii::app()->db->createCommand( $sql )->execute();
                        

                        $sql = "SELECT credit, id_plan, active, typepaid, creditlimit, language, removeinterprefix, redial,
                        enableexpire, UNIX_TIMESTAMP(expirationdate), expiredays,
                        UNIX_TIMESTAMP(pkg_user.creationdate), pkg_user.lastname, pkg_user.firstname, pkg_user.email,
                        pkg_user.password, pkg_user.id, pkg_user.restriction, pkg_user.id_user,
                        pkg_user.callshop, pkg_user.id_offer, pkg_user.record_call, pkg_user.prefix_local, pkg_user.country
                        FROM pkg_user LEFT JOIN pkg_plan ON id_plan=pkg_plan.id
                        WHERE pkg_user.id ='" . $id_user . "'";
                        $agi->verbose($sql,25);
                        $result = Yii::app()->db->createCommand( $sql )->queryAll();
                        

                        if (is_array($result) && count($result) > 0)
                        {
                            $MAGNUS->credit            = $result[0]['credit'];
                            $MAGNUS->id_plan           = $result[0]['id_plan'];
                            $MAGNUS->active            = $result[0]['active'];                    
                            $MAGNUS->typepaid          = $result[0]['typepaid'];
                            $MAGNUS->creditlimit       = $result[0]['creditlimit'];
                            $language                  = $result[0]['language'];
                            $MAGNUS->removeinterprefix = $result[0]['removeinterprefix'];
                            $MAGNUS->enableexpire      = $result[0]['enableexpire'];
                            $MAGNUS->expirationdate    = $result[0]['expirationdate'];
                            $MAGNUS->expiredays        = $result[0]['expiredays'];
                            $MAGNUS->creationdate      = $result[0]['creationdate'];
                            $MAGNUS->id_user           = $result[0]['id'];
                            $MAGNUS->restriction       = $result[0]['restriction'];
                            $MAGNUS->id_agent          = $result[0]['id_user'];
                            $MAGNUS->callshop          = $result[0]['callshop'];   
                            $MAGNUS->id_offer          = $result[0]['id_offer'];
                            $MAGNUS->record_call       = $result[0]['record_call'];
                            $MAGNUS->prefix_local      = $result[0]['prefix_local'];
                            $MAGNUS->countryCode       = $result[0]['country'];

                            $authentication = true;
                            $MAGNUS->accountcode = $MAGNUS->username;
                            $agi->verbose("AUTHENTICATION BY PIN USE VOUCHER:" . $MAGNUS->CallerID,6);
                            break;
                        }

                    }else{
                        $prompt = "prepaid-auth-fail";
                        continue;
                    }                    
                }
            }
        }

        //tech prefix to route
        $sql = "SELECT id FROM pkg_plan WHERE  techprefix = '".substr($MAGNUS->dnid, 0,5)."'";
        $techPrefixresult = Yii::app()->db->createCommand( $sql )->queryAll();
        if (count($techPrefixresult) > 0 && strlen($MAGNUS->dnid) > 13) {
            $MAGNUS->id_plan = $techPrefixresult[0]['id'];
            $MAGNUS->dnid = substr($MAGNUS->dnid, 5);
            $agi->verbose("Changed plan via TechPrefix: Plan used $MAGNUS->id_plan - Number: $MAGNUS->dnid ",15);
        }

        if ($MAGNUS->config['global']['intra-inter'] == '1')
        {
            $ramal = explode("-", $MAGNUS->channel);
            $ramal = explode("/", $ramal[0]);
            $ramal = $ramal[1];

            $sql = "SELECT callerid FROM pkg_sip WHERE name = '$ramal'";
            $callerIDresult = Yii::app()->db->createCommand( $sql )->queryAll();
            $agi->verbose($sql,25);

            $agi->verbose(substr($callerIDresult[0]['callerid'], 0,4) . "  " . substr($MAGNUS->dnid, 0,4),30);

            if (substr($callerIDresult[0]['callerid'], 0,4) == substr($MAGNUS->dnid, 0,4)) {
                $sql = "SELECT name FROM pkg_plan WHERE id = $MAGNUS->id_plan";
                $PlanNameresult = Yii::app()->db->createCommand( $sql )->queryAll();
                $agi->verbose($sql,25);
                $planName = $PlanNameresult[0]['name'];
                $sql = "SELECT id FROM pkg_plan WHERE name LIKE '$planName Intra'";
                $callerIDresult = Yii::app()->db->createCommand( $sql )->queryAll();
                $agi->verbose($sql,25);
                if (count($callerIDresult) > 0) {
                    $agi->verbose("INTRA PLAN FOUND AND CHANGED $MAGNUS->id_plan to ". $callerIDresult[0]['id'] ,5);
                    $MAGNUS->id_plan = $callerIDresult[0]['id'];
                }                
            }
        }    


        /*check if user is a agent user*/
        if(!is_null($MAGNUS->id_agent) && $MAGNUS->id_agent > 1){

            $MAGNUS->id_plan_agent  = $result[0]['id_plan'];            
            $sql                    = "SELECT id_plan, username FROM pkg_user WHERE id='" . $MAGNUS->id_agent . "'";
            $result                 = Yii::app()->db->createCommand( $sql )->queryAll();
            $agi->verbose($sql,20);
            
            $MAGNUS->id_plan        = $result[0]['id_plan'];
            $MAGNUS->agentUsername = $result[0]['username'];

            $agi->verbose("Reseller id_Plan $MAGNUS->id_plan_agent, user id_plan $MAGNUS->id_plan",6);

            $sql                    = "SELECT play_audio FROM pkg_plan WHERE id='" . $MAGNUS->id_plan_agent . "'";
            $resultPlayAudio        = Yii::app()->db->createCommand( $sql )->queryAll();
        }else{
            $sql                    = "SELECT play_audio FROM pkg_plan WHERE id='" . $MAGNUS->id_plan . "'";
            $resultPlayAudio        = Yii::app()->db->createCommand( $sql )->queryAll();
           
        }
        
        $MAGNUS->play_audio     = isset($resultPlayAudio[0]['play_audio']) ? $resultPlayAudio[0]['play_audio'] : false; 
       

        if (strlen($language) > 1 )
            $agi->set_variable('CHANNEL(language)', $language);

        if ($MAGNUS->typepaid == 1)
            $MAGNUS->credit = $MAGNUS->credit + $MAGNUS->creditlimit;

        if (isset($callerid_activated) && $callerid_activated  != "1")
            $prompt = "prepaid-auth-fail";


        if ($MAGNUS->active != "1")
            $prompt = "prepaid-auth-fail";

        if ($MAGNUS->enableexpire > 0)
            $prompt = $MAGNUS->check_expirationdate_customer();

        if (strlen($prompt) > 0)
        {
            if ($MAGNUS->play_audio  == 0)
            {
                $agi->verbose( "Send Congestion $prompt",3);
                $agi->execute((congestion), Congestion);
                return - 2;
            }
            else
            {
                $agi->verbose($prompt,3);
                $agi->stream_file($prompt, '#');
            }

            if ($prompt == "prepaid-card-expired")
            {
                $MAGNUS->accountcode = '';
                $callerID_enable = 0;
            }
            else
            {
               return - 2;
            }
        }
        else
        {
            $authentication = true;
        }

        if ($authentication != true)
        {
            $res = -1;
        }
        //verfica se é cliente de callshop, e se a cabina esta ativa
        if ($MAGNUS->callshop)
        {
            $ramal = explode("-", $MAGNUS->channel);
            $ramal = explode("/", $ramal[0]);
            $ramal = $ramal[1];
            $sql = "SELECT status FROM pkg_sip WHERE name = '$ramal'";
            $result = Yii::app()->db->createCommand( $sql )->queryAll();
            $agi->verbose( $sql,25);
            if ($result[0]['status'] == 0)
            {
                $agi->verbose( "CABINA DISABLED " . $ramal,3); 
                return -1;
            }
        }
        if(isset($agi->username))        
            $agi->username = $MAGNUS->username;
        return $res;
    }
    
    public function gerar_senha ($tamanho, $maiuscula, $minuscula, $numeros, $codigos)
    {
        $maius = "ABCDEFGHIJKLMNOPQRSTUWXYZ";
        $minus = "abcdefghijklmnopqrstuwxyz";
        $numer = "0123456789";
        $codig = '!@#$%&';

        $base = '';
        $base .= ($maiuscula) ? $maius : '';
        $base .= ($minuscula) ? $minus : '';
        $base .= ($numeros) ? $numer : '';
        $base .= ($codigos) ? $codig : '';

        srand((float) microtime() * 10000000);
        $senha = '';
        for ($i = 0; $i < $tamanho; $i++) {
        $senha .= substr($base, rand(0, strlen($base)-1), 1);
        }

        if (substr($senha, 0, 1) == 0) {
            $senha = '9'.substr($senha, 1);
        }
        return trim($senha);
    }

    public function getNewUsername($agi)
    {
        $existsUsername = true;     
        
            while ($existsUsername)
            {
                $randUserName = AGI_Authenticate::gerar_senha(5, false, false, true, false);
                $sql = "SELECT count(id) FROM pkg_user WHERE username LIKE '$randUserName'";
                $countUser   = Yii::app()->db->createCommand( $sql )->queryAll();
                if (count($countUser) > 0) {
                    $existsUsername = false;
                    break;
                }
            }        

        return $randUserName;
    }

};
