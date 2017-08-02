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

class AGI_MassiveCall
{
    public function send($agi, &$MAGNUS, &$Calc)
    {
        $agi->answer();
        $now = time();

        if ($MAGNUS->dnid == 'failed' || !is_numeric($MAGNUS->dnid)) {
            $agi->verbose("Hangup becouse dnid is OutgoingSpoolFailed",25);
            $MAGNUS->hangup($agi);
        }
        
        $idPhonenumber = $agi->get_variable("PHONENUMBER_ID", true);
        $phonenumberCity = $agi->get_variable("PHONENUMBER_CITY", true);
        $idCampaign = $agi->get_variable("CAMPAIGN_ID", true);
        $idRate = $agi->get_variable("RATE_ID", true);
        $MAGNUS->id_user = $agi->get_variable("IDCARD", true);
        $MAGNUS->username = $agi->get_variable("USERNAME", true);
        $MAGNUS->id_agent = $agi->get_variable("AGENT_ID", true);
        $destination = $MAGNUS->dnid;

        $sql = "SELECT * FROM pkg_campaign WHERE id=$idCampaign";
        $resultCampaign = Yii::app()->db->createCommand( $sql )->queryAll();
        $agi->verbose($sql,25);
        
        $forward_number = $resultCampaign[0]['forward_number'];


        /*VERIFICA SE CAMPAÃ‘A TEM ENCUESTA*/
        $sql = "SELECT * FROM pkg_campaign_poll WHERE id_campaign=$idCampaign";
        $resultPoll = Yii::app()->db->createCommand( $sql )->queryAll();

        $agi->verbose($sql,25);
        $idPoll = $resultPoll[0]['id'];
        $repeat = $resultPoll[0]['repeat'];


       
        if(isset($resultCampaign[0]['audio_2']) && strlen($resultCampaign[0]['audio_2']) > 5){

            $executeAudio2 = true; 
            $sql = "SELECT name FROM pkg_phonenumber WHERE id = ". $idPhonenumber;
            $resultPhoneNumber = Yii::app()->db->createCommand( $sql )->queryAll();
            $agi->verbose($sql,25);

            //verifica se tem nome no numero
            if(isset($resultPhoneNumber[0]['name']) && strlen($resultPhoneNumber[0]['name']) > 3){
                $agi->verbose("TTS",25);
                $name  = utf8_encode($resultPhoneNumber[0]['name']);
                $file = $idPhonenumber.date("His") ;
                $name = urlencode($name);

                //http://api.voicerss.org/?key=0ed8d233c8534591a7abf4b620606bc2&src=Adilson&hl=pt-br
                $tts_url = preg_replace('/\$name/', $name,  $MAGNUS->config['global']['tts_url']);

                if (preg_match("/google/", $tts_url)) {
                    $token = AGI_MassiveCall::make_token($resultPhoneNumber[0]['name']);
                    $tts_url = preg_replace("/tk=/", "tkold=", $tts_url);
                    $tts_url .= "&tk=$token";
                }
                $agi->verbose($tts_url,8);
                exec("wget -q -U Mozilla -O \"/tmp/$file.mp3\" \"$tts_url\"");
                $executeTTS = true;
                exec("mpg123 -w /tmp/$file.wav /tmp/$file.mp3 && rm -rf /tmp/$file.mp3");
                exec("sox -v 2.0 /tmp/$file.wav /tmp/$file2.wav && rm -rf /tmp/$file.wav");
                exec("sox /tmp/$file2.wav -c 1 -r 8000 /tmp/$file.wav && rm -rf /tmp/$file2.wav ");                
            }            
        }

        /*AUDIO FOR CAMPAIN*/
        $audio = "/var/www/html/mbilling/resources/sounds/idCampaign_".$idCampaign;
        
        //se tiver audio 2 passar direto
        if(isset($executeAudio2)){
            $agi->stream_file($audio, '#');

        }
        else{
            // CHECK IF NEED AUTORIZATION FOR EXECUTE POLL OR IS EXISTE FORWARD NUMBER
            if (strlen($forward_number) > 2 || ($resultPoll[0]['request_authorize'] == 1) ){
                $res_dtmf = $agi->get_data($audio, 5000, 1);
            }            
            else{
                $agi->stream_file($audio, ' #');
            }
        }        


        if(isset($executeTTS)){
            $agi->stream_file("/tmp/".$file, ' #');
            exec("rm -rf /tmp/$file*");
        }
            
        
        if(isset($executeAudio2)){
            /*Execute audio 2*/
            $audio = "/var/www/html/mbilling/resources/sounds/idCampaign_".$idCampaign."_2";

            // CHECK IF NEED AUTORIZATION FOR EXECUTE POLL OR IS EXISTE FORWARD NUMBER
            if (strlen($forward_number) > 2 || ($resultPoll[0]['request_authorize'] == 1) )
                $res_dtmf = $agi->get_data($audio, 5000, 1);
            else{
                $agi->stream_file($audio, ' #');
               
            }
            
        }

        $agi->verbose('RESULT DTMF ' .$res_dtmf['result'],25);

        if (strlen($resultCampaign[0]['audio']) < 5 && strlen($forward_number) > 2) {
            $res_dtmf['result'] = 1;
            $agi->verbose('CAMPAIN SEM AUDIO, ENVIA DIRETO PARA '.$forward_number);
        }

        

        //CHECK IF IS FORWARD EXTERNAL CALLL
        $agi->verbose("forward_number $forward_number , res_dtmf: ". $res_dtmf['result'] . ", digit_authorize: ". $resultCampaign[0]['digit_authorize'], 10);
        
        if(strlen($forward_number) > 2 && ($res_dtmf['result'] == $resultCampaign[0]['digit_authorize'] || $resultCampaign[0]['digit_authorize'] == '-1') )
        {

            $agi->verbose("have Forward number $forward_number");

            $sql = "UPDATE pkg_phonenumber SET info = 'Forward DTMF 1' WHERE id ='" . $idPhonenumber . "'";
            Yii::app()->db->createCommand( $sql )->execute();
            $agi->verbose($sql,25);


            $max_long = 2147483647;
            $time2call = 3600;
            $dialparams = $MAGNUS->agiconfig['dialcommand_param_sipiax_friend'];
            $dialparams = str_replace("%timeout%", min($time2call * 1000, $max_long), $dialparams);
            $dialparams = str_replace("%timeoutsec%", min($time2call, $max_long), $dialparams);

            $sql = "SELECT record_call FROM pkg_user WHERE id=$MAGNUS->id_user";
            $agi->verbose($sql,25);
            $resultrecord = Yii::app()->db->createCommand( $sql )->queryAll();            
            $MAGNUS->record_call = $resultrecord[0]['record_call'];

            $forwardOption = explode("|", $forward_number);
            $forwardOptionType = $forwardOption[0];

            $agi->verbose(print_r($forwardOption,true));

            if ($forwardOptionType == 'sip') {

                $sql = "SELECT name FROM pkg_sip WHERE id = " . $forwardOption[1];
                $resultSip = Yii::app()->db->createCommand( $sql )->queryAll();
                $agi->verbose($dialstr,25);

                $dialstr = 'SIP/'.$resultSip[0]['name'];

                if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1)
                {
                    $command_mixmonitor = "MixMonitor {$username}.{$MAGNUS->destination}.{$MAGNUS->uniqueid}.".$MAGNUS->mix_monitor_format.",b";
                    $myres = $agi->execute($command_mixmonitor);
                    $agi->verbose( $command_mixmonitor,5);
                }

                $myres = $MAGNUS->run_dial($agi, $dialstr);
                $dialstatus = $agi->get_variable("DIALSTATUS");
                $dialstatus = $dialstatus['data'];

                if ($dialstatus == "NOANSWER")
                {
                    $agi->stream_file('prepaid-callfollowme', '#');
                }
                elseif (($dialstatus == "BUSY" || $dialstatus == "CHANUNAVAIL") || ($dialstatus == "CONGESTION"))
                {
                    $agi->stream_file('prepaid-isbusy', '#');
                }
            }
            elseif ($forwardOptionType == 'queue') {
                $result_did[0]['id_queue']  = $forwardOption[1];                
                $result_did[0]['did'] = $destination;
                $agi->set_variable("CALLERID(num)",$destination);
                $agi->set_callerid($destination);
                AGI_Queue::callQueue($agi, $MAGNUS, $Calc, $result_did, 'torpedo');
            }
            elseif ($forwardOptionType == 'ivr') {
                $result_did[0]['id_ivr']  = $forwardOption[1];
                $result_did[0]['did'] = $destination;
                AGI_Ivr::callIvr($agi, $MAGNUS, $Calc, $result_did, 'torpedo');
            }
            elseif ($forwardOptionType == 'group') {

                $agi->verbose("Call group $group ",25);

                $sql = "SELECT name FROM pkg_sip WHERE `group` = '". $forwardOption[1]."'";
                $agi->verbose( $sql,25);
                $resultGroup = Yii::app()->db->createCommand( $sql )->queryAll();
                if (count($resultGroup) == 0) {
                    $agi->verbose( 'GROUP NOT FOUND');
                    $agi->stream_file('prepaid-invalid-digits', '#');
                    
                }else{
                    $group = '';
                    foreach ($resultGroup as $key => $value) {
                        $group .= "SIP/".$value['name']."&";
                    }
                    $dialstr = substr($group, 0,-1).$dialparams;                    
                    $agi->verbose( "DIAL $dialstr",25);
                    $MAGNUS->run_dial($agi, $dialstr);
                }               


            }elseif ($forwardOptionType == 'custom') {
                $agi->set_variable("CALLERID(num)",$destination);
                if(preg_match('/AGI/', $forwardOption[1]))
                {
                    $agi = explode("|", $forwardOption[1]);
                    $agi->exec_agi($agi[1].",$destination,$idCampaign,$idPhonenumber");
                }else{
                    $MAGNUS->run_dial($agi, $forwardOption[1]);
                }

            }            

            $agi->set_variable("CALLERID(num)",$destination);


            



            if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1)
                $myres = $agi->execute("StopMixMonitor");
        }
         
        //execute poll if exist 
        if (count($resultPoll) > 0)
        {

            foreach ($resultPoll as $poll) {

                if ($dtmf_result == -1) {
                    break;
                }
                sleep(1);
                $dtmf_result == ''; 

                if ($poll['id'] == 18 && $dtmf_result > 0) {
                    continue;
                }

                if ($poll['id'] == 20 && $dtmf_result > 0) {
                    continue;
                }

                for ($i=0; $i < 12; $i++) { 
              
                    $audio = "/var/www/html/mbilling/resources/sounds/idPoll_".$poll['id'];

                    if($poll['request_authorize'] == 1){
                        $agi->verbose('Request authorize',5);
                        //IF CUSTOMER MARK 1 EXECUTE POLL
                        if ($res_dtmf['result'] == $resultPoll[0]['digit_authorize']){
                            $agi->verbose('Authorized',5);
                            $res_dtmf = $agi->get_data($audio, 5000, 1);
                        }                            
                        else{
                            $dtmf_result = -1;
                            $agi->verbose('NOT authorized',5);
                            break;
                        }
                            
                    }
                    else
                        $res_dtmf = $agi->get_data($audio, 5000, 1);

                    //GET RESULT OF POLL
                    $dtmf_result = $res_dtmf['result'];

                    $agi->verbose("Cliente votou na opcao: $dtmf_result",5);

                    //Hungaup call if the fisrt poll dtmf is not numeric
                    if ($i == 0 && !is_numeric($dtmf_result)) {
                        $agi->verbose('nao votou nada na 1º enquete',5);
                        break;
                    }
                    
                    if ($repeat > 0 ) {
                        for ($i=0; $i < $repeat; $i++) { 

                            if ($i > 0) {
                                $agi->stream_file('prepaid-invalid-digits', ' #');

                                $res_dtmf = $agi->get_data($audio, 5000, 1);

                                //GET RESULT OF POLL
                                $dtmf_result = $res_dtmf['result'];
                            }
                            
                            if ($i == 2) {
                                $agi->verbose('Client press invalid option after two try');
                                $dtmf_result = 'error';
                                break;
                            }

                            if (is_numeric($dtmf_result)) {
                                $agi->verbose("dtmf_result es numerico ",8);
                                $sql = "SELECT option".$dtmf_result." as resposta_option FROM pkg_campaign_poll WHERE id = ".$poll['id'];
                                $agi->verbose($sql,25);
                                $resultResOption = Yii::app()->db->createCommand( $sql )->queryAll();
                                
                                $agi->verbose('$i'.$i . " " .$repeat,25);
                                if ($resultResOption[0]['resposta_option'] == '' && $i >= $repeat - 1) {
                                    $agi->verbose("Client press invalid option after try $repeat, hangup call on poll ". $poll['id']);
                                    $agi->stream_file('prepaid-invalid-digits', ' #');
                                    $dtmf_result = 'error';
                                    break;
                                }
                                else if ($resultResOption[0]['resposta_option'] == '' ) {
                                    $agi->verbose("Client press invalid option $dtmf_result on poll ". $poll['id'],8);

                                }else{
                                    $agi->verbose("Client press number: $dtmf_result",8);
                                    break; 
                                }
                            }                        
                        }
                    }     



                    if($resultPoll[0]['option'.$dtmf_result] != 'repeat')
                        break;
                }

                if (is_numeric($dtmf_result) && $dtmf_result >= 0)
                {
                    //si esta hangup en la opcion, corlgar.
                    if (preg_match('/hangup/', $poll['option'.$dtmf_result])) {

                        $agi->verbose('desligar chamadas',25);

                        $newIdPoll = explode('_', $poll['option'.$dtmf_result]);

                        //si tiene una id en el hangup, executar el audio
                        if (isset($newIdPoll[1])) {
                            $audio = "/var/www/html/mbilling/resources/sounds/idPoll_".$newIdPoll[1];
                            $res_dtmf = $agi->get_data($audio, 5000, 1);
                        }                       

                        $sql = "INSERT INTO pkg_campaign_poll_info (id_campaign_poll, resposta, number, city) VALUES ('".$poll['id']."',  '$dtmf_result' ,  '$destination', '$phonenumberCity')";
                        Yii::app()->db->createCommand( $sql )->execute();
                        $agi->verbose($sql,25);
                        
                        break;
                        
                    }
                    elseif (preg_match('/create/', $poll['option'.$dtmf_result])) {
                        
                        $sql = "SELECT id, ini_credit FROM pkg_plan WHERE signup = 1 LIMIT 1";
                        $resultPlan = Yii::app()->db->createCommand( $sql )->queryAll();
                        $agi->verbose($sql,25);
                        if (count($resultPlan) > 0) {

                            $id_plan = $resultPlan[0]['id'];
                            $credit = $resultPlan[0]['ini_credit'];                            

                            $sql = "SELECT id FROM pkg_group_user WHERE id_user_type = 3 LIMIT 1";
                            $resultGroup = Yii::app()->db->createCommand( $sql )->queryAll();
                            $agi->verbose($sql,25);  
                            $id_group = $resultGroup[0]['id'];
                            $password = AGI_MassiveCall::gerarSenha(8, true, true, true, false);
                            $callingcard_pin = AGI_MassiveCall::getNewLock_pin($agi);
                            

                            $fields = "username, password, id_user, id_plan, credit, id_group, active, prefix_local, callingcard_pin, loginkey, typepaid";
                            $values = "'$destination', '$password', 1, '$id_plan' , '$credit', '$id_group', '1', '11', '$callingcard_pin', '', '0'";
                            $sql = "INSERT INTO pkg_user ($fields) VALUES ($values)";
                            try {
                                $sucess = Yii::app()->db->createCommand( $sql )->execute();
                                $agi->verbose($sql,25);    
                            } catch (Exception $e) {
                                $sucess = false;
                            }

                            if($sucess){
                                $idUser = Yii::app()->db->lastInsertID;
                                $fields = "id_user, accountcode, name, allow, host, insecure, defaultuser, secret";
                                $values = "$idUser, '$destination', '$destination', 'g729,gsm,g726,alaw,ulaw', 'dynamic', 'no',  '$destination', '$password'";
                                $sql = "INSERT INTO pkg_sip ($fields) VALUES ($values)";
                                Yii::app()->db->createCommand( $sql )->execute();
                            }
                  
                        }else{
                            $agi->verbose('NOT HAVE PLAN ENABLE ON SIGNUP',25);
                        }

                    }
                    else{

                        $sql = "INSERT INTO pkg_campaign_poll_info (id_campaign_poll, resposta, number, city) VALUES ('".$poll['id']."',  '$dtmf_result' ,  '$destination', '$phonenumberCity')";
                        Yii::app()->db->createCommand( $sql )->execute();
                        $agi->verbose($sql,25);
                  

                        if (preg_match('/SIP|sip/', $poll['option'.$res_dtmf['result']]) ) {

                            if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1)
                            {
                                $command_mixmonitor = "MixMonitor {$MAGNUS->username}.{$destination}.{$MAGNUS->uniqueid}.".$this->mix_monitor_format.",b";
                                $myres = $agi->execute($command_mixmonitor);
                            }

                            $dialstr = $poll['option'.$res_dtmf['result']];
                            $dialstr = preg_replace("/number/", $destination,  $dialstr);
                            $agi->set_variable("CALLERID(num)",$destination);
                            $agi->set_callerid($destination);
                            $agi->verbose('CALL SEND TO SIP IN POLL -> '. $dialstr,25);

                
                            $myres = $MAGNUS->run_dial($agi, $dialstr);

                            if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1)
                                $myres = $agi->execute("StopMixMonitor");
                        }
                    }

                    $agi->verbose($sql,25);

                }
                else {
                    $agi->verbose('Cliente no marco nada',8);
                    break;
                }


            }
                      
            $agi->stream_file('prepaid-final', ' #');
        }
               

        $sql = "SELECT * FROM pkg_rate WHERE id = $idRate";
        $resultRate = Yii::app()->db->createCommand( $sql )->queryAll();
        $agi->verbose($sql,25);

        $id_prefix        = $resultRate[0]['id_prefix'];
        /*buy rate*/
        $buyrate          = $resultRate[0]['buyrate'];
        $buyrateinitblock = $resultRate[0]['buyrateinitblock'];
        $buyrateincrement = $resultRate[0]['buyrateincrement'];

        /*sell rate*/
        $rateinitial      = $resultRate[0]['rateinitial'];
        $initblock        = $resultRate[0]['initblock'];
        $billingblock     = $resultRate[0]['billingblock'];

        $trunk            = $resultRate[0]['id_trunk'];


        $duration = time() - $now;

        /* ####     CALCUL BUYRATE COST   #####*/
        $buyratecost = $MAGNUS->calculation_price($buyrate, $duration, $buyrateinitblock, $buyrateincrement);
        $sellratecost = $MAGNUS->calculation_price($rateinitial, $duration, $initblock, $billingblock);
        $agi->verbose( "[TEMPO DA LIGAÃ‡AO] " . $duration,8);

        $sql = "SELECT id_plan FROM pkg_user WHERE id=$MAGNUS->id_user";
        $resultCard = Yii::app()->db->createCommand( $sql )->queryAll();
        $agi->verbose($sql,25);


        $MAGNUS->id_plan = $resultCampaign[0]['id_plan'] > 0 ? $resultCampaign[0]['id_plan'] : $resultCard[0]['id_plan'];
        if($duration > 1){


            if($resultCampaign[0]['enable_max_call'] == 1 ){
                //desativa a campanha se o limite de chamadas foi alcançado
                $setStatus = $resultCampaign[0]['secondusedreal'] < 1 ? ",status = 0" : '';
                
                //diminui 1 do total de chamadas permitidas completas , se o tempo da chamada for superior ao tempo do audio
                if ($duration >= $resultCampaign[0]['nb_callmade']) {    
                    $sql = "UPDATE pkg_campaign SET secondusedreal = secondusedreal - 1 $setStatus WHERE id ='" . $idCampaign . "'";
                    Yii::app()->db->createCommand( $sql )->execute();
                    $agi->verbose($sql,25);
                }
            }
            

            /*create campaign cdr*/
            $sql = "INSERT INTO pkg_cdr (uniqueid, sessionid, id_user, id_campaign, id_plan, calledstation, sipiax, buycost,  sessionbill , sessiontime , id_prefix, stoptime ,starttime, id_trunk, src) 
            VALUES ('" . $MAGNUS->uniqueid . "', '" . $MAGNUS->channel . "', '" . $MAGNUS->id_user . "', '" . $idCampaign . "', '" . $MAGNUS->id_plan . "', '" . $destination . "',5, " . $buyratecost . "," . $sellratecost . ", " . $duration . ", " . $id_prefix .
                " , CURRENT_TIMESTAMP , CURRENT_TIMESTAMP, $trunk,'" . $MAGNUS->username . "')";
            Yii::app()->db->createCommand( $sql )->execute();
            $agi->verbose($sql,25);

            

            if(!is_null($MAGNUS->id_agent) && $MAGNUS->id_agent > 1){
                $id_call = Yii::app()->db->lastInsertID;                
                $MAGNUS->id_plan_agent = $agi->get_variable("AGENT_ID_PLAN", true);
                $agi->verbose($MAGNUS->id_plan_agent);
                $agi->verbose( '$MAGNUS->id_agent'.$MAGNUS->id_agent. ' $id_call'. $id_call. '$destination'. $destination,10);
                Calc::updateSystemAgent($agi, $MAGNUS, $id_call, $MAGNUS->id_agent, $destination, $sellratecost, $duration);
            }
            else
            {
                $sql = "UPDATE pkg_user SET credit= credit - " . $MAGNUS->round_precision(abs($sellratecost)) . " ,  lastuse=now() WHERE id='" . $MAGNUS->id_user . "'";
                Yii::app()->db->createCommand( $sql )->execute();
                $agi->verbose($sql,25);
            }

            $sql = "UPDATE pkg_phonenumber SET status = '3' WHERE id ='" . $idPhonenumber . "'";
            Yii::app()->db->createCommand( $sql )->execute();
            $agi->verbose($sql,25);

            $sql = "UPDATE pkg_trunk SET secondusedreal = secondusedreal + $duration WHERE id='" . $trunk . "'";
            Yii::app()->db->createCommand( $sql )->execute();
            $agi->verbose($sql,25);

            $sql = "UPDATE pkg_provider SET credit = credit -  $buyratecost WHERE id=(SELECT id_provider FROM pkg_trunk WHERE id=$trunk)";
            Yii::app()->db->createCommand( $sql )->execute();
            $agi->verbose($sql,25);
            
            
            return true;
        }
    }

    function gerarSenha ($tamanho, $maiuscula, $minuscula, $numeros, $codigos)
    {
        $maius = "ABCDEFGHIJKLMNOPQRSTUWXYZ";
        $minus = "abcdefghijklmnopqrstuwxyz";
        $numer = "0123456789";
        $codig = '!@#%';

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
        return $senha;
    }

    function getNewLock_pin($agi)
    {
        $existsLock_pin = true;
        while ($existsLock_pin)
        {
            $randLock_Pin = mt_rand(100000, 999999);
            $sql = "SELECT callingcard_pin FROM pkg_user WHERE callingcard_pin LIKE '$randLock_Pin'";
            $countLock_pin = Yii::app()->db->createCommand( $sql )->queryAll();
            $countLock_pin = count($countLock_pin);
            $existsLock_pin = ($countLock_pin > 0);
        }
        return $randLock_Pin;
    }

    function make_token($line){
        $text = $line;
        $time = round(time() / 3600);
        $chars = unpack('C*', $text);
        $stamp = $time;
        
        foreach ($chars as $key => $char) {
            $stamp = AGI_MassiveCall::make_rl($stamp + $char, '+-a^+6');
        }       

        $stamp = AGI_MassiveCall::make_rl($stamp, '+-3^+b+-f');

        if ($stamp < 0) {
            $stamp = ($stamp & 2147483647) + 2147483648;
        }
        $stamp %= pow(10,6);
        return ($stamp . '.' . ($stamp ^ $time));
        
    }

    function make_rl($num, $str) {
        for ($i=0; $i < strlen($str) - 2; $i+=3) { 
            $d = substr($str, $i+2, 1);
            if (ord($d) >= ord('a')) {
                $d = ord($d) - 87;
            }else {
                $d = round($d);
            }
            if (substr($str, $i+1, 1) == '+') {
                $d = $num >> $d;
            } else {
                $d = $num << $d;
            }
            if (substr($str, $i, 1) == '+') {
                $num = $num + $d & 4294967295;
            } else {
                $num = $num ^ $d;
            }
        }
        return $num;
    }

}