<?php
/**
 * Acoes do modulo "Campaign".
 *
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
 * 28/10/2012 
 * index.php/mBillingSoftphone/read?l=felipe|137DCEC44002170DB2D2DCD9C70DBEBF
 */

class MBillingSoftphoneController extends BaseController
{
	public $attributeOrder   = 'id';
	public $filterByUser 	 = false;
	private $host = 'localhost';
    private $user = 'magnus';
    private $l;
    private $password = 'magnussolution';

	public function actionRead()
	{
		$config = LoadConfig::getConfig();

		if (isset($_GET['l'])) {
			$data = explode('|', $_GET['l']);
			$user= $data[0];
			$pass = $data[1];
		

			$sql = "SELECT 'username', firstname, lastname, credit,
					'".$config['global']['base_currency']."' AS currency, secret
					FROM pkg_sip join pkg_user ON pkg_sip.id_user = pkg_user.id WHERE pkg_sip.name = :user" ;
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":user", $user, PDO::PARAM_STR);
			$result = $command->queryAll();

			print_r($result);
			exit;

		
			if(!isset($result[0]['username']) || strtoupper(MD5($result[0]['secret'])) != $pass){
				echo 'false';
				exit;
			}

			unset($result[0]['secret']);

			$result[0]['credit'] = number_format($result[0]['credit'],2);

			if(count($result) == 0){
				echo 'false';
				exit;
			}
			//$result[0]['version'] = 'MPhone-1.0.5';

			$result = json_encode(array(
				$this->nameRoot => $result,
				$this->nameCount => 1,
				$this->nameSum => ''
			));


			$result = json_decode($result,true);

			Yii::log(print_r($result,true),'error');
			echo '<pre>';
			print_r($result);
		}
	}
	public function actionTotalPerCall()
	{
		if (isset($_GET['l'])) {
			$user= $_GET['l'];
			$sql =" SELECT * FROM pkg_sip WHERE name = :user";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":user", $user, PDO::PARAM_STR);
			$result = $command->queryAll();

			Yii::log(print_r($result,true),'error');
			if (isset($result[0]['callshopnumber']) && strlen($result[0]['callshopnumber']) > 5) {
				$sessiontime = $result[0]['callshoptime'];
				$ndiscado = $result[0]['callshopnumber'];
				Yii::log(substr($ndiscado, 0,1),'error');

				
				$sql =" SELECT prefix_local FROM pkg_user WHERE id = :id_user";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_user", $result[0]['id_user'], PDO::PARAM_INT);
				$resultUser = $command->queryAll();


				$ndiscado = $this->number_translation($resultUser[0]['prefix_local'],$ndiscado);
				
				$sql = "SELECT * FROM pkg_rate_callshop WHERE dialprefix = SUBSTRING(:ndiscado,1,length(dialprefix)) 
                  				AND id_user= :id_user   ORDER BY LENGTH(dialprefix) DESC LIMIT 1";
                  	Yii::log($sql,'error');
                  	$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_user", $result[0]['id_user'], PDO::PARAM_INT);
				$command->bindValue(":ndiscado", $ndiscado, PDO::PARAM_STR);
				$resultCallShop = $command->queryAll();


            		$buyrate = $resultCallShop[0]['buyrate'] > 0 ? $resultCallShop[0]['buyrate'] : $cost;
				$initblock = $resultCallShop[0]['minimo'];
				$increment = $resultCallShop[0]['block'];

				$sellratecost_callshop = $this->calculation_price($buyrate, $sessiontime, $initblock, $increment);

				echo number_format($sellratecost_callshop,2);
            }

			Yii::log(print_r($_REQUEST,true),'error');
		}else{
			echo '0,00';
		}
	}

	public function number_translation($translation,$destination)
    	{
    		$config = LoadConfig::getConfig();
       	$regexs = split(",", $translation);

		foreach ($regexs as $key => $regex) {

			$regra = split( '/', $regex );
			$grab = $regra[0];
			$replace = $regra[1];
			$digit =$regra[2];
			         

			$number_prefix = substr($destination,0,strlen($grab));


			if (strtoupper($config['global']['base_country'])  == 'BRL' || strtoupper($config['global']['base_country'])  == 'ARG')
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
		return $destination;
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

	public function actionCallshopTotal()
	{
		$config = LoadConfig::getConfig();
		if (isset($_GET['l'])) {
			$data = explode('|', $_GET['l']);
			$user= $data[0];
			$pass = $data[1];

			$sql = "SELECT 'username',firstname, lastname, credit, '".$config['global']['base_currency']."' AS currency, secret
								FROM pkg_sip join pkg_user ON pkg_sip.id_user = pkg_user.id WHERE pkg_sip.name = :user" ;
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":user", $user, PDO::PARAM_STR);
			$result = $command->queryAll();


			if(!isset($result[0]['username']) || strtoupper(MD5($result[0]['secret'])) != $pass){
				echo 'false';
				exit;
			}
			if(count($result) == 0){
				echo 'false';
				exit;
			}

			unset($result[0]['secret']);

			
			$sql = "SELECT SUM(price) price FROM pkg_callshop WHERE status = 0 AND cabina = :user";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":user", $user, PDO::PARAM_STR);
			$resultCallShop = $command->queryAll();


			if(count($resultCallShop) == 0){
				$result[0]['credit'] = '0.00';

			}else{
				$result[0]['credit'] = number_format($resultCallShop[0]['price'],2);
			}			

			if(count($result) == 0){
				echo 'false';
				exit;
			}

			$result = json_encode(array(
				$this->nameRoot => $result,
				$this->nameCount => 1,
				$this->nameSum => ''
			));
			$result = json_decode($result,true);
			echo '<pre>';
			print_r($result);

		}
	}

}