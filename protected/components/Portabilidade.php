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
	
	public function getDestination($destination, $cron = false, $agi, $id_plan = NULL)
	{
		if (!isset($cron)) {
			define('LOGFILE', 'protected/runtime/Portabilidade.log');
			define('DEBUG', 0);
		}
		$config = LoadConfig::getConfig();
		$destination_e164 = $destination;


		$sql = "SELECT portabilidadeFixed, portabilidadeMobile FROM pkg_plan  WHERE id = '$id_plan' LIMIT 1";
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		
		$mobile = false;
        	$fixed = false;

        	if (strlen($destination)  >= 10 && substr($destination, 0, 2) == 55) {

            	if ( in_array(substr($destination, 2, 1), array(1,2,8,9))  && substr($destination, 4, 1) >= 7 ) {
                	$mobile= true;
            	}else if( substr($destination, 4, 1) >= 7  ){
                	$mobile= true;
            	}else{
                	$fixed = true;
            	}

            	if ( ( $mobile == true && $result[0]['portabilidadeMobile']  == 1 ) || ( $fixed == true && $result[0]['portabilidadeFixed'] == 1 ) )
            	{

				if(strlen($config['global']['portabilidadeUsername']) > 3 && strlen($config['global']['portabilidadePassword']) > 3)
				{
					$user = $config['global']['portabilidadeUsername'];
					$pass = $config['global']['portabilidadePassword'];
					$url = "http://magnusbilling.com/portabilidade/consulta_numero.php?user=".$user."&pass=".$pass."&seache_number=" . $destination . "";
					if(!$operadora = @file_get_contents($url,false))
                        		$operadora = '55999';           	

					$company = str_replace("55", "", $operadora);
					$destination = "1111" . $company . $destination;
				}
				else
				{
					$ddd = substr($destination, 2);

					$sql = "SELECT company FROM pkg_portabilidade  WHERE number = '$ddd' ORDER BY id DESC LIMIT 1";
					$result = Yii::app()->db->createCommand($sql)->queryAll();
					
					if(is_array($result) && isset($result[0]['company']))
					{
					    $company = str_replace("55", "", $result[0]['company']);
					    $destination = "1111" . $company . $destination;              
					}
					else
					{
						//echo $destination;
				    	if(strlen($ddd) == 11){
				        	$sql = "SELECT company FROM pkg_portabilidade_prefix WHERE number = ".substr($ddd,0,7)." ORDER BY number DESC LIMIT 1";
				    	}else{
				        	$sql = "SELECT company FROM pkg_portabilidade_prefix WHERE number = ".substr($ddd,0,6)." ORDER BY number DESC LIMIT 1";
				    	}
				     	$result = Yii::app()->db->createCommand($sql)->queryAll();
				    

					    if(is_array($result) && isset($result[0]['company']))
					    {
					        	$company = str_replace("55", "", $result[0]['company']);
					        	$destination = "1111" . $company . $destination;
					     }else{
					        	$company = 399;
					        	$destination = "1111" . $company . $destination;
					   	}
					}
					//nao aceita chamadas com 8 digitos nos DDD com nono digito, somente NEXTEL
					if(isset($company) &&  $company != 377 && strlen($ddd) == 10 && in_array(substr($ddd,0,1), array('1','2','8','9') ) && $fixed == false )
					{
					    $company = 399;
					   	$destination = "1111" . $company . $destination;                    
					}
				}                
            	}
        	}
        	return $destination;        	
	}
}
?>