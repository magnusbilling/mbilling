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
class Process{

    function isActive() {
        $pid = Process::getPID();
        
        if ($pid == null) {
            $ret = false;
        } else {
            $ret = posix_kill ( $pid, 0 );
        }

        if ($ret == false) {
            Process::activate();
        }

        return $ret;
    }
    
    function activate() {
        $pidfile = PID;
        $pid = Process::getPID();

        if ($pid != null && $pid == getmypid()) {
            return "Already running!\n";
        } else {
            $fp = fopen($pidfile,"w+");
            if ($fp) {
                if (!fwrite($fp,"<"."?php\n\$pid = ".getmypid().";\n?".">")) {
                    die("Can not create pid file!\n");
                }

                fclose($fp);
            } else {
                die("Can not create pid file!\n");
            }
        }
    }
    
    function getPID() {
        if (file_exists(PID)) {
            require(PID);
            return $pid;
        } else {
            return null;
        }
    }

    public function releaseUserCredit($id_user, $monto, $description, $codigo = 0, $paymount_tipe = 0, $admin_email = NULL)
    {
        if (!isset($_SESSION['id_user'])) {
            $_SESSION['isAgent'] = false;
            $_SESSION['id_user'] = $id_user;
            $_SESSION['user_type'] = 1;
        }
        

        $sql = "SELECT * FROM pkg_refill WHERE description LIKE '%$codigo%' AND id_user = $id_user";
        $refillResult = Yii::app()->db->createCommand($sql)->queryAll();

        if(count($refillResult) > 0){

            if($refillResult[0]['payment'] == 1){
                //recarga ja efetuado e pagamento ja confirmado
                return false;
            }else{
                //marca recarga como pago
                $sql = "UPDATE pkg_refill SET payment = 1 WHERE id = ".$refillResult[0]['id'];
                Yii::log($sql, 'error');
                Yii::app()->db->createCommand($sql)->execute();
            }
        }
        else{   
            $_SERVER['argv'][0] = 'cron';    
            //adiciona a recarga
            $refill = new Refill;
            $refill->id_user = $id_user;
            $refill->credit = $monto;
            $refill->description = $description;
            $refill->payment = 1;
            $refill->save();
        }
        if (!isset($_SESSION['id_user'])) {
            unset($_SESSION['isAgent']);
            unset($_SESSION['id_user']);
            unset($_SESSION['user_type']);
        }
    }
}
?>