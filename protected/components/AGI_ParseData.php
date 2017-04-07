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

class AGI_ParseData {


    public function ManagerConnect($host,$user,$password, $command) {

        $asmanager = new AGI_AsteriskManager;
        try {
           $asmanager->connect($host, $user, $password); 
        } catch (Exception $e) {
            return false;
        }
        $data = @$asmanager->Command($command);
        $data =$data['data'];
        @$asmanager->disconnect();
        return $data;
    }


    public function SipShowPeers()
    {
        $sql = "SELECT * FROM pkg_servers WHERE type = 'asterisk' AND status = 1";
        $resultAsterisk = Yii::app()->db->createCommand($sql)->queryAll();

        array_push($resultAsterisk, array(
            'host' => 'localhost',
            'username' => 'magnus',
            'password' => 'magnussolution'
        ));
        $result =array();
        foreach ($resultAsterisk as $key => $server) {
            $data = $this->ManagerConnect($server['host'],$server['username'],$server['password'],"sip show peers");
            if (strlen($data) < 10)
                continue;          
            $linesSipResult  = explode("\n", $data);


            $columns = preg_split("/\s+/", $linesSipResult[1]);

            $index = array();
            
            for ($i=0; $i < 10; $i++)
              $index[] = @strpos($linesSipResult[1], $columns[$i]);
            
            foreach ($linesSipResult as $key => $line) {
              $element = array();
              foreach ($index as $key => $value) {
                $startIndex = $value;
                $lenght = @$index[$key+1]-$value;
                @$element[$columns[$key]] = trim(isset($index[$key+1])? substr($line, $startIndex,$lenght):substr($line, $startIndex) );
              }         
              $result[] = $element;

            }
        }
        return $result;     
    
    }

    public function CoreShowChannels()
    {
        
        $sql = "SELECT * FROM pkg_servers WHERE type = 'asterisk' AND status = 1";
        $resultAsterisk = Yii::app()->db->createCommand($sql)->queryAll();

        array_push($resultAsterisk, array(
            'host' => 'localhost',
            'username' => 'magnus',
            'password' => 'magnussolution'
        ));
        $channels = array();
        foreach ($resultAsterisk as $key => $server) {

            $columns = array('Channel','Context','Exten','Priority','Stats','Application','Data','CallerID','Accountcode','Amaflags','Duration','Bridged');  

            $data = $this->ManagerConnect($server['host'],$server['username'],$server['password'],"core show channels concise");

            if (!isset($data)) {
                return;
            }
           
            $linesCallsResult  = explode("\n", $data);


            if (count($linesCallsResult) < 1) {
                return;
            }
            
            for ($i=0; $i < count($linesCallsResult); $i++) { 
                $call = explode("!", $linesCallsResult[$i]);
                if (!preg_match("/\//", $call[0])) {
                    continue;
                }
                $call['server']=$server['host'];
                $channels[]=$call;        
                
            }

        }
        return $channels;    
    }

    public function CoreShowChannel($channel,$type='core',$server='_%')
    {

        $sql = "SELECT * FROM pkg_servers WHERE type = 'asterisk' AND status = 1 AND host LIKE '$server'";
        $resultAsterisk = Yii::app()->db->createCommand($sql)->queryAll();
        if($server == '_%' || $server == 'localhost'){
            array_push($resultAsterisk, array(
                'host' => 'localhost',
                'username' => 'magnus',
                'password' => 'magnussolution'
            ));
        }
            
        foreach ($resultAsterisk as $key => $server) {


            $data = @$this->ManagerConnect($server['host'],$server['username'],$server['password'],$type." show channel ".$channel);

            if (!isset($data) || strlen($data) < 10 || preg_match("/is not a known channe/", $data)) {
                continue;
            }
            $linesCallResult  = explode("\n", $data);
            if (count($linesCallResult) < 1) {
                continue;
            }
            $result = array();
            for ($i=2; $i < count($linesCallResult); $i++) { 
                if (preg_match("/level 1: /", $linesCallResult[$i])) {
                    $data = explode("=", substr($linesCallResult[$i], 9) );
                }
                elseif (preg_match("/: /", $linesCallResult[$i])) {
                    $data = explode(":", $linesCallResult[$i]);
                }
                elseif (preg_match("/=/", $linesCallResult[$i])) {
                    $data = explode("=", $linesCallResult[$i]);
                }
                $key = isset($data[0]) ? $data[0] : '';
                $value = isset($data[1]) ? $data[1] : '';
                
                if ($key == 'SIPCALLID') {
                    $result[trim($key)]= $this->CoreShowChannel($value,'sip',$server['host']); 
                   
                }else{
                    $result[trim($key)]=trim($value);
                }
                
            }
            break;
        }   

        
        return $result;
         
    }

}
?>