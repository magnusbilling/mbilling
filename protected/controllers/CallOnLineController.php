<?php
/**
 * Acoes do modulo "CallOnLine".
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
 * 19/09/2012
 */

class CallOnLineController extends Controller
{
    public $attributeOrder     = 't.duration DESC, status ASC';
    public $extraValues        = array('idUser' => 'username,credit');

    private $host = 'localhost';
    private $user = 'magnus';
    private $password = 'magnussolution';

    public $fieldsInvisibleClient = array(
        'canal',
        'tronco'
        );

    public $fieldsInvisibleAgent = array(
        'canal',
        'tronco'
        );


    public function init()
    {       
        $this->instanceModel = new CallOnLine;
        $this->abstractModel = CallOnLine::model();
        $this->titleReport   = Yii::t('yii','CallOnLine');
        
        parent::init();

        if (Yii::app()->getSession()->get('isAgent'))
        {
            $this->filterByUser        = true;
            $this->defaultFilterByUser = 'b.id_user';
            $this->join                = 'JOIN pkg_user b ON t.id_user = b.id';
        }        
    }



    public function actionRead()
    {
        //altera o sort se for a coluna idUsercredit.
        if (isset($_GET['sort']) && $_GET['sort'] === 'idUsercredit')
            $_GET['sort'] = '';

        if($_SERVER['HTTP_HOST'] != 'localhost')
            $this->asteriskCommand();
        return parent::actionRead();
    }

    public function actionCheck()
    {
        $this->asteriskCommand();
    }

    public function actionDestroy()
    {
        $asmanager = new AGI_AsteriskManager;
        $conectaServidor = $conectaServidor = $asmanager->connect($this->host, $this->user, $this->password);
        $values = $this->getAttributesRequest();
        $result = $this->abstractModel->findByPk($values['id']);

        if(count($result) > 0)
        {
            $server = $asmanager->Command("hangup request $result->canal");
            $success = true;
            $msn = Yii::t('yii', 'Operation was successful.').Yii::app()->language;
        }
        else
        {
            $success = false;
            $msn = Yii::t('yii', 'Disallowed action');
        }

        echo json_encode(array(
                'success' => $success,
                'msg' => $msn
            ));
        exit();
    }

    public function asteriskCommand ()
    {
        $modelClear = $this->instanceModel;
        $success = $modelClear->deleteAll();

        $sql = "SELECT * FROM pkg_servers WHERE type = 'asterisk' AND status = 1";
        $resultAsterisk = Yii::app()->db->createCommand($sql)->queryAll();

        array_push($resultAsterisk, array(
            'host' => 'localhost',
            'username' => 'magnus',
            'password' => 'magnussolution'
        ));

        foreach ($resultAsterisk as $key => $server) {

            $this->host = $server['host'];

            if (isset($server['port']) && strlen($server['port']) && is_numeric($server['port'])) {
                $this->host .= ':'.$server['port'];
            }
            $this->user = $server['username'];
            $this->password =  $server['password'];

 
            $asmanager = new AGI_AsteriskManager;
            $conectaServidor = $conectaServidor = $asmanager->connect($this->host, $this->user, $this->password);
            $server = $asmanager->Command("core show channels concise");
            if (!isset($server['data'])) {
                continue;
            }
            $arr = explode("\n", $server["data"]);


            if ($arr[0] != "")
            {
                $sql = array();
                foreach ($arr as $temp)
                {

                    $linha = explode("!", $temp);
                    if(!isset($linha[1]))
                        continue;

                    $canal = $linha[0];
                    if (preg_match("/Congestion/", $linha[5]) || preg_match("/Down/", $linha[5]) || preg_match("/Busy/", $linha[5]) ) {
                       //echo '<pre>';
                        //print_r($linha);
                        $asmanager->Command("hangup request $canal");
                        continue;
                    }
                    


                    $tronco = isset($linha[6]) ? $linha[6] : 1;
                    $tronco = explode("/", $tronco);
                    $tronco = isset($tronco[1]) ? $tronco[1] : 0;
                    $username = isset($linha[8]) ? $linha[8] : 0;

                    //torpedo de voz
                    if (isset($linha[1]) && $linha[1] == 'magnus_campaign_callback') 
                    {
                        $ndiscado = isset($linha[2]) ? $linha[2] : false;
                        $username = isset($linha[7]) ? $linha[7] : false;
                        $tronco = 'Torpedo de Voz';
                    }

                    if (!$canal)
                        continue;

                    $result = $asmanager->Command("core show channel $canal");


                    $arr2 = explode("\n", $result["data"]);
                    //echo '<pre>';
                      //      print_r($arr2);
                    foreach ($arr2 as $temp2)
                    {
                        //echo '<pre>';

                        //pegando o callerid
                        if (strstr($temp2, 'Caller ID Name')) 
                        {
                            $arr3 = explode("Caller ID Name:", $temp2);
                            $callerid = trim(rtrim($arr3[1]));
                        }
                        //pegando numero discado
                        if (strstr($temp2, 'DNID Digits')) 
                        {
                            $arr3 = explode("DNID Digits:", $temp2);
                            $ndiscado = trim(rtrim($arr3[1]));
                        }

                        if (!isset($ndiscado) || $ndiscado == '(N/A)')
                        {
                            //print_r($temp2);
                            if (strstr($temp2, 'dst=')) 
                            {
                                $arr3 = explode("dst=", $temp2);
                                $ndiscado = trim(rtrim($arr3[1]));
                                if ($ndiscado == 's')
                                    $ndiscado = '(N/A)';
                            
                            }                       

                        }

                        //pega codec
                        if (strstr($temp2, 'NativeFormat'))
                        {
                            $arr3 = explode("NativeFormat:", $temp2);
                            $arr3 = explode("(", $arr3[0]);
                            $codec = preg_replace("/\)/","", $arr3[1]);
                        }
                        //pega status
                        if (strstr($temp2, 'State:'))
                        {
                            $arr3 = explode("State:", $temp2);
                            $status = trim(rtrim($arr3[1]));
                        }

                        if (strstr($temp2, 'billsec'))
                        {
                            $arr3 = explode("billsec=", $temp2);
                            $cdr = trim(rtrim($arr3[1]));
                        }
                        //pega status
                        if (strstr($temp2, 'SIPURI='))
                        {
                            $arr3 = explode("SIPURI=", $temp2);            

                            if (preg_match("/@/", $arr3[1])) {
                                $arr3 = explode("@", rtrim($arr3[1]));
                                $arr3 = explode(":", rtrim($arr3[1]));
                                $from_ip = trim(rtrim($arr3[0]));
                            }
                            else{
                                $arr3 = explode(":", rtrim($arr3[1]));
                                $from_ip = trim(rtrim($arr3[1]));
                            }

                            

                        }

                        if (!isset($from_ip)) {
                            $from_ip = 'IAX';
                        }

                        //$reinvite = 'no';
                        //eh uma chamada sip?
                        if (strstr($temp2, 'SIPCALLID')) 
                        {
                            $arr3 = explode("=", $temp2);
                            $sipid = $arr3[1];
                            //pegando informacoes sobre a chamada sip
                            $result = $asmanager->Command("sip show channel $sipid");
                            $arr4 = explode("\n", $result["data"]);
                            $arr_data = array_shift($arr4);
                            foreach ($arr4 as $sipvar)
                            {
                                //variavel que diz se houve reinvite
                                if (strstr($sipvar, 'Audio IP'))
                                {
                                    if (strstr($sipvar, 'local'))
                                        $reinvite = 'no';
                                    elseif (strstr($sipvar, 'Outside bridge'))
                                        $reinvite = 'yes';
                                }
                            }
                        }  

                        $reinvite = isset( $reinvite) ?  $reinvite : 'no';

                        
                        if (strstr($temp2, 'USERNAME'))
                        {
                            $arr3 = explode("USERNAME=", $temp2);
                            $username = trim(rtrim($arr3[1]));
                        }

                        if (strstr($temp2, 'CAMPAIGN_ID'))
                        {
                            $tronco = explode("/",$canal);
                            $tronco = explode("-",$tronco[1]);
                            $tronco = $tronco[0];
                            $canal = 'Torpedo';
                            $from_ip = 'local';
                        }

                                    
                    }

                    //print_r($arr2);
                     //ajusta para torpedo de voz.
                    /*if ($ndiscado == '(N/A)')
                    {
                        $isCampaign = false;                 


                        for ($i=37; $i < 51; $i++) { 
                            if (!isset($arr2[$i])) {
                               continue;
                            }
                            if (preg_match('/CAMPAIGN_ID/', $arr2[$i])) {
                                $isCampaign = true;
                                $id_campaign = $arr2[$i];
                            }

                            if (preg_match('/CALLED/', $arr2[$i])) {
                                  $ndiscado = substr($arr2[$i],7);
                            }

                            if (preg_match('/USERNAME/', $arr2[$i])) {
                                  $usernameTorpedo = substr($arr2[$i],9);
                            }

                        }

                        
                        if($isCampaign == false){
                            continue; 
                        }

                                 
                        $ndiscado = isset($ndiscado) ? $ndiscado: '(N/A)';

                        $username = $usernameTorpedo != '(N/A)' ? $usernameTorpedo: $username;
                        $tronco = explode("/",$canal);
                        $tronco = explode("-",$tronco[1]);
                        $tronco = $tronco[0];
                        $canal = 'Torpedo';
                        $from_ip = 'local';
                    }*/


                    if (strlen($ndiscado) > 16) {
                        $tech = substr($ndiscado, 0 ,6);
                        $resultUser = User::model()->findAll(array(
                            'select' => 'id',
                            'condition' => "callingcard_pin = '".$tech."'"
                            ));
                        

                        if (isset($resultUser[0]['id'])) {
                            $ndiscado = substr($ndiscado, 6);
                        }else{
                            $resultUser = User::model()->findAll(array(
                                'select' => 'id',
                                'condition' => "username = '".$username."'"
                            ));
                        }
                    }else{

                        $resultUser = User::model()->findAll(array(
                            'select' => 'id',
                            'condition' => "username = '".$username."'"
                        ));
                    }                


                    $id_user = isset($resultUser[0]['id']) ? $resultUser[0]['id'] : 'NULL';

                    //$status = explode(" ", $status);

                    if($id_user == 'NULL')
                        $tronco = 'DID Call';

           
                    if (preg_match("/billing/", $linha[1])  && $ndiscado != '(N/A)' && !preg_match("/Down/", $status) && !preg_match("/Busy/", $status) )
                    {
                        $sql[] = "(NULL, $id_user, '$canal', '$tronco', '$ndiscado', '$codec', '$status', '$cdr', '$reinvite','$from_ip', '$this->host')";

                    }
                }

                if (count($sql) > 0) {
                    $sql = 'INSERT INTO pkg_call_online VALUES '.implode(',', $sql).';';

                    Yii::app()->db->createCommand($sql)->execute() !== false;
                }
            }
        }

    }

    public function actionSpyCall()
    {
        $config = LoadConfig::getConfig();

        if(count($config['global']['channel_spy']) == 0){
            echo json_encode(array(
                'success' => false,
                'msg' => 'Invalid SIP for spy call'
            ));
            exit;
        }

        $dialstr = 'SIP/'.$config['global']['channel_spy'];
        // gerar os arquivos .call
        $call = "Action: Originate\n";
        $call .= "Channel: " . $dialstr . "\n";
        $call .= "Callerid: " . Yii::app()->session['username']  . "\n";
        $call .= "Context: billing\n";
        $call .= "Extension: 5555\n";
        $call .= "Priority: 1\n";
        $call .= "Set:USERNAME=" . Yii::app()->session['username'] . "\n";
        $call .= "Set:SPY=1\n";
        $call .= "Set:CHANNELSPY=" . $_POST['channel'] . "\n";

        Yii::log($call, 'info');
        $aleatorio = str_replace(" ", "", microtime(true));
        $arquivo_call = "/tmp/$aleatorio.call";
        $fp = fopen("$arquivo_call", "a+");
        fwrite($fp, $call);
        fclose($fp);

        touch("$arquivo_call", mktime(date("H"), date("i"), date("s") + 1, date("m"), date("d"), date("Y")));
        chown("$arquivo_call", "asterisk");
        chgrp("$arquivo_call", "asterisk");
        chmod("$arquivo_call", 0755);
        exec("mv $arquivo_call /var/spool/asterisk/outgoing/$aleatorio.call");


        echo json_encode(array(
                'success' => true,
                'msg' => 'Start Spy'
            ));
    }
}