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

class Portabilidade
{

    public function consulta($agi, $MAGNUS, $number){
        $agi->verbose("consulta portabilidade numero ".$number, 25); 

        $sql = "SELECT portabilidadeFixed, portabilidadeMobile FROM pkg_plan  WHERE id = '$MAGNUS->id_plan' LIMIT 1";
        $result = Yii::app()->db->createCommand( $sql )->queryAll();
        $agi->verbose($sql,25);

        //celular SP
        $mobile = false;
        $fixed = false;

        if (strlen($number)  >= 10 && substr($number, 0, 2) == 55) {

            if ( in_array(substr($number, 2, 1), array(1,2,3,4,5,6,7,8,9))  && substr($number, 4, 1) >= 7 ) {
                $mobile= true;
            }else if( substr($number, 4, 1) >= 7  ){
                $mobile= true;
            }else{
                $fixed = true;
            }

            if ( ( $mobile == true && $result[0]['portabilidadeMobile']  == 1 ) || ( $fixed == true && $result[0]['portabilidadeFixed'] == 1 ) )
            {
                
                $MAGNUS->portabilidade = true;
                if(strlen($MAGNUS->config['global']['portabilidadeUsername']) > 3 && strlen($MAGNUS->config['global']['portabilidadePassword']) > 3)
                {
                    $user = $MAGNUS->config['global']['portabilidadeUsername'];
                    $pass = $MAGNUS->config['global']['portabilidadePassword'];
                    $url = "http://magnusbilling.com/portabilidade/consulta_numero.php?user=".$user."&pass=".$pass."&seache_number=" . $number . "";
                    $agi->verbose($url,25);

                    if(!$operadora = @file_get_contents($url,false)) {
                        $operadora = '55999';
                    }
                    $company = str_replace("55", "", $operadora);
                    $number = "1111" . $company . $number;
                }
                else
                {
                    $ddd = substr($number, 2);



                    $sql = "SELECT company FROM pkg_portabilidade_prefix  WHERE number = '".substr($ddd,0,6)."' ORDER BY number DESC LIMIT 1";
                    $agi->verbose($sql,25);
                    $resultNextel = Yii::app()->db->createCommand( $sql )->queryAll();

                    if(is_array($resultNextel) &&  ( $resultNextel[0]['company']  == '55377' || $resultNextel[0]['company']  == '55390' || $resultNextel[0]['company']  == '55391') )
                    {
                        $agi->verbose("é Nextel",15);  
                    }else{

          
                      if(strlen($ddd) == 10 &&  substr($ddd, 2, 1) > 5 ) {
                        $agi->verbose("Numero sem o nono digito, MBilling adicionou" ,8);
                        $ddd = substr($ddd, 0,2) . 9 . substr($ddd,2);
                        $number = "55".$ddd;
                      }
                    }


                    $sql = "SELECT company FROM pkg_portabilidade  WHERE number = '$ddd' ORDER BY id DESC LIMIT 1";
                    $result = Yii::app()->db->createCommand( $sql )->queryAll();
                    $agi->verbose($sql,25);

                    if(is_array($result) && isset($result[0]['company']))
                    {
                        $company = str_replace("55", "", $result[0]['company']);
                        $number = "1111" . $company . $number;
                        $agi->verbose("CONSULTA DA PORTABILIDADE ->" . $result[0]['company'],25);                
                    }
                    else
                    {
                        if(strlen($ddd) == 11){
                            $sql = "SELECT company FROM pkg_portabilidade_prefix WHERE number = ".substr($ddd,0,7)." ORDER BY number DESC LIMIT 1";
                            $result = Yii::app()->db->createCommand( $sql )->queryAll();
                            $agi->verbose($sql,25); 
                        }else{
                             $result = $resultNextel;
                        }
                        

                        if(is_array($result) && isset($result[0]['company']))
                        {
                            $company = str_replace("55", "", $result[0]['company']);
                            $number = "1111" . $company . $number;
                            $agi->verbose("CONSULTA DA PORTABILIDADE ->NUMERO NAO FOI PORTADO->" . $result[0]['company'] ,25);
                        }else{
                            $company = 399;
                            $number = "1111" . $company . $number;
                            $agi->verbose("CONSULTA DA PORTABILIDADE ->Numero sem operadora->" . $number ,3);                        
                        }
                    }
                    //nao aceita chamadas com 8 digitos nos DDD com nono digito, somente NEXTEL
                    if(isset($company) && $company != 377 && strlen($ddd) == 10 && in_array(substr($ddd,0,1), array('1','2','9') ) && $fixed == false)
                    {
                        $company = 399;
                        $agi->verbose("Numero sem o nono digito -> " . $number.', retornando codigo 1111399',1);
                        $number = "1111" . $company . $number;                    
                    }
                }
                $agi->verbose("CONSULTA DA PORTABILIDADE ->" . $number,25);
                
            }
        }

        return $number;
    }
}