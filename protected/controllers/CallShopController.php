<?php
/**
 * Acoes do modulo "CallShop".
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
 * 19/09/2012
 */

class CallShopController extends Controller
{
    public $attributeOrder        = 't.callerid';
    public $extraValues           = array('idUser' => 'username');
    public $defaultFilterByUser   = 't.id_user';
    public $join                  = 'INNER JOIN pkg_user c ON t.id_user = c.id';
    public $defaultFilter         = 'c.callshop = 1';
    private $host = 'localhost';
    private $user = 'magnus';
    private $l;
    private $password = 'magnussolution';

    public function init()
    {
        if (!Yii::app()->session['id_user'])
            exit;
        $this->instanceModel = new CallShop;
        $this->abstractModel = CallShop::model();
        $this->titleReport   = Yii::t('yii','CallShop');
        $this->l =Yii::app()->session['licence'];
        //include("resources/asterisk/AGI.Class.php");
        //include("resources/asterisk/AGI_AsteriskManager.Class.php");
        parent::init();
    }


    public function actionRead()
    {

        if($_SERVER['HTTP_HOST'] != 'localhost')
            $this->asteriskCommand();
        return parent::actionRead();
    }

    public function actionLiberar()
    {
        if (isset($_GET['name'])) {
            $filter[0]['value'] = $_GET['name'];
        }else{
            $filter = json_decode($_POST['filter'], true);
        }   

        $sql = "UPDATE pkg_sip SET status = 2 WHERE name = :name ";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":name", $filter[0]['value'], PDO::PARAM_STR);
        $command->execute();

        echo json_encode(array(
            $this->nameSuccess => true,
            $this->nameMsg => $this->msgSuccess
        ));

    }

    public function actionCobrar()
    {
        if (isset($_GET['name'])) {
            $filter[0]['value'] = $_GET['name'];
        }else{
            $filter = json_decode($_POST['filter'], true);
        }
        $sql = "UPDATE pkg_sip SET status = 0, callshopnumber= NULL, callshoptime = 0 WHERE name = :name ";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":name", $filter[0]['value'], PDO::PARAM_STR);
        $command->execute();


        $sql = "UPDATE pkg_callshop SET status = 1 WHERE cabina = :name ";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":name", $filter[0]['value'], PDO::PARAM_STR);
        $command->execute();

        echo json_encode(array(
            $this->nameSuccess => true,
            $this->nameMsg => $this->msgSuccess
        ));
    }

    public function asteriskCommand ()
    {
        $asmanager = new AGI_AsteriskManager;
        $conectaServidor = $conectaServidor = $asmanager->connect($this->host, $this->user, $this->password);
        $server = $asmanager->Command("core show channels concise");
        $arr = explode("\n", $server["data"]);
        
        $sql = "DELETE FROM pkg_callshop WHERE status = 3 ";
        $result = Yii::app()->db->createCommand($sql)->execute();

        $sql = "UPDATE pkg_sip SET status = 2 WHERE status = 3 ";
        Yii::app()->db->createCommand($sql)->execute();

        $sql = "UPDATE pkg_sip SET callshopnumber= NULL, callshoptime = 0";
        //Yii::app()->db->createCommand($sql)->execute();

        if ($arr[0] != "")
        {            
            $sql = array();
            foreach ($arr as $temp)
            {
                $linha = explode("!", $temp);
                if(!isset($linha[1]))
                    continue;               

                $canal = $linha[0];

                $username = isset($linha[8]) ? $linha[8] : 0;

                if (!$canal)
                    continue;

                $result = $asmanager->Command("core show channel $canal");

                $arr2 = explode("\n", $result["data"]);

                foreach ($arr2 as $temp2)
                {
                    //pegando numero discado
                    if (strstr($temp2, 'DNID Digits')) 
                    {
                        $arr3 = explode("DNID Digits:", $temp2);
                        $ndiscado = trim(rtrim($arr3[1]));

                        if (substr($ndiscado, 0,2) == 00) {
                             $ndiscado = substr( $ndiscado, 2);
                        }
                    }

                    //pega status
                    if (strstr($temp2, 'State:')) 
                    {
                        $arr3 = explode("State:", $temp2);
                        $status = trim(rtrim($arr3[1]));
                    }else{
                        $status = '';
                    }

                    
                    if (strstr($temp2, ' channel')) 
                    {
                        $arr3 = explode("channel=", $temp2);
                        if (isset($arr3[1])) {                       
                            $ramal = trim(rtrim($arr3[1]));
                            $ramal = explode('-', $ramal);
                            $ramal = explode('/', $ramal[0]);
                            $ramal = isset($ramal[1]) ? $ramal[1] : NULL;
                        }else{
                           $ramal = NULL; 
                        }

                        
                    }
                    

                    if (strstr($temp2, 'billsec')) 
                    {
                        $arr3 = explode("billsec=", $temp2);
                        $seconds = trim(rtrim($arr3[1]));
                    }              
                 
                    

                }       
                $ramal = isset($ramal) ? $ramal: NULL;

                $resultUser = User::model()->findAll(array(
                'select' => 'id, callshop',
                'condition' => "username = '".$username."'"
                ));

                $id_user = isset($resultUser[0]['id']) ? $resultUser[0]['id'] : false;


                $status = explode(" ", (string)$status);               

                if (preg_match("/billing/", $linha[1])  && isset($ndiscado) && $ndiscado != '(N/A)' && $status[0] != 'Down' && is_numeric($ndiscado) && !is_null($ramal))
                {
                    if(isset($resultUser[0]['callshop']) && $resultUser[0]['callshop'] == 1){
      

                        $sql = "UPDATE pkg_sip SET status = 3, callshopnumber = :ndiscado, callshoptime = :seconds 
                                            WHERE name = :name ";
                        $command = Yii::app()->db->createCommand($sql);
                        $command->bindValue(":ndiscado", $ndiscado, PDO::PARAM_STR);
                        $command->bindValue(":seconds", $seconds, PDO::PARAM_STR);
                        $command->bindValue(":name", $ramal, PDO::PARAM_STR);
                        $command->execute();                    
                       

                        $sql = "SELECT id FROM pkg_prefix WHERE prefix = SUBSTRING('$ndiscado',1,length(prefix)) 
                                ORDER BY LENGTH(prefix) DESC LIMIT 1";


                        $command = Yii::app()->db->createCommand($sql);
                        $command->bindValue(":ndiscado", $ndiscado, PDO::PARAM_STR);
                        $resultPrefix = $command->queryAll();

                        
           
                        $columm = "sessionid, id_user, id_prefix, status, price, calledstation, cabina, sessiontime";
                        $values = ":sessionid, :id_user, :id_prefix, '3', 0, :ndiscado, :ramal, :seconds";
                        $sql = "INSERT INTO pkg_callshop ($columm) VALUES ( $values )";
                        $command = Yii::app()->db->createCommand($sql);
                        $command->bindValue(":sessionid", $canal, PDO::PARAM_STR);
                        $command->bindValue(":id_user", $id_user, PDO::PARAM_INT);
                        $command->bindValue(":id_prefix", $resultPrefix[0]['id'], PDO::PARAM_INT);
                        $command->bindValue(":ndiscado", $ndiscado, PDO::PARAM_STR);
                        $command->bindValue(":ramal", $ramal, PDO::PARAM_STR);
                        $command->bindValue(":seconds", $seconds, PDO::PARAM_STR);
                        $command->execute(); 
                        
                        
                                       
                    }
                }

            }
            
        }
    }
}