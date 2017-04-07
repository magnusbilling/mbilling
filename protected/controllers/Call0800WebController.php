<?php
/**
 * Acoes do modulo "Call".
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
 * 19/09/2012
 */

class Call0800WebController extends BaseController
{

	public function actionIndex()
	{
		$config = LoadConfig::getConfig();
		Yii::app()->setLanguage($config['global']['base_language']);

		if(!isset($_POST['number'])){

			$this->render('index',array(	
				'send' => false
				));
			
		}
		else{

			$destination = isset($_POST['number']) ? $_POST['number'] : '';
			$user = isset($_GET['user']) ? $_GET['user'] : '';

			$sql = "SELECT  * FROM pkg_sip  WHERE name = :user ";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":user", $user, PDO::PARAM_STR);
			$resultSip = $command->queryAll();

			if(!isset($resultSip[0]['id']) )
			{
				$error_msg = Yii::t('yii','Error : User no Found!');
				echo $error_msg;
				exit;
			}

			$sql = "SELECT  * FROM pkg_user  WHERE id = :id_user ";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $resultSip[0]['id_user'], PDO::PARAM_STR);
			$resultUser = $command->queryAll();

			if(!isset($resultUser[0]['id']) )
			{
				$error_msg = Yii::t('yii','Error : User no Found!');
				echo $error_msg;
				exit;
			}

			$dialstr = "SIP/$user"; 
	 
	             
			// gerar os arquivos .call
			$call = "Channel: " . $dialstr . "\n"; 
			$call .= "Callerid: " . $destination . "\n";
			$call .= "Context: billing\n";
			$call .= "Extension: " . $user . "\n";
			$call .= "Priority: 1\n";
			$call .= "Set:IDUSER=" . $resultUser[0]['id']. "\n";
			$call .= "Set:SECCALL=" . $destination. "\n";


			$aleatorio = str_replace(" ", "", microtime(true));
			$arquivo_call = "/tmp/$aleatorio.call";
			$fp = fopen("$arquivo_call", "a+");
			fwrite($fp, $call);
			fclose($fp);

			if (isset($_POST['time'])) {			
				$time = strtotime($_POST['time']);
 			}else{
 				$time = time();
 			}           
                    
			touch("$arquivo_call", $time);
			chown("$arquivo_call", "asterisk");
			chgrp("$arquivo_call", "asterisk");
			chmod("$arquivo_call", 0755);             

			system("mv $arquivo_call /var/spool/asterisk/outgoing/$aleatorio.call");

			$this->render('index',array(	
				'send' => true
				));

		}
		
	}

	public function actionCallback()
	{		
		$config = LoadConfig::getConfig();
		if (isset($_GET['l'])) {

			$data = explode('|', $_GET['l']);
			
			Yii::log(print_r($data,true), 'error');


			if (!isset($data[2]) ) {
				echo 'Your number is required';
				
			}
			else if (strlen($data[2]) < 4) {
				echo 'The minimum length for your number is 4';
				
			}
			else if (!isset($data[3]) ) {
				echo 'Destination is required';
				
			}else if (strlen($data[3]) < 4) {
				echo 'The minimum length for destination is 4';
				
			}else{

				$user= $data[0];
				$pass = $data[1];	



				$sql = "SELECT pkg_user.id, username,firstname, lastname, credit, pkg_user.id_user, id_plan,
				'".$config['global']['base_currency']."' AS currency, secret, pkg_user.prefix_local 
				FROM pkg_sip join pkg_user ON pkg_sip.id_user = pkg_user.id WHERE pkg_sip.name = :user" ;
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":user", $user, PDO::PARAM_STR);
				$result = $command->queryAll();


				if(!isset($result[0]['username']) || strtoupper(MD5($result[0]['secret'])) != $pass){
					echo 'User or password is invalid';
					exit;
				}

				if ($result[0]['id_user'] > 1)
				{
					$sql = "SELECT * FROM pkg_user WHERE id = :id_user";
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":id_user", $result[0]['id_user'], PDO::PARAM_STR);
					$resultAgent = $command->queryAll();

					//VERIFICA SE O AGENT TEM CREDITO
					if(isset($resultAgent[0]['credit']) && $resultAgent[0]['credit'] <= 0)
					{
						echo Yii::t('yii','You don t have enough credit to call');
						exit;
					}
				}

				$prefix_local = $result[0]['prefix_local'];


				if(isset($result[0]['credit']) && $result[0]['credit'] <= 0.5)
				{
					echo 'You don t have enough credit to call';
					exit;
				}


				$yournumber = $data[2];
				$destination = $data[3];


				if (preg_match("/->/", $destination)) {
					$destination = explode("->", $destination);
					$destination = preg_replace("/-|\(|\)| /", "", $destination[1]);
					Yii::log(print_r($destination,true), 'error');
				}
				elseif (preg_match("/ - /", $destination)) {
					$destination = explode(" - ", $destination);
					$destination = preg_replace("/-|\(|\)| /", "", $destination[1]);
					Yii::log(print_r($destination,true), 'error');
				}


				$yournumber = $this->number_translation($prefix_local,$yournumber);
				$destination = $this->number_translation($prefix_local,$destination);

				/*protabilidade*/		
				$yournumber = Portabilidade :: getDestination($yournumber, true, false,$result[0]['id_plan']);


				$SearchTariff = new SearchTariff();
				$callTrunk = $SearchTariff->find($yournumber, $result[0]['id_plan'], $result[0]['id']);
				
				if (count($callTrunk) == 0)
				{
					echo Yii::t('yii','Prefix not found to you number');
					
					exit;
				}


				$destination = Portabilidade :: getDestination($destination, true, false,$result[0]['id_plan']);

				$callTrunkDestination = $SearchTariff->find($destination, $result[0]['id_plan'], $result[0]['id']);
		
				if (count($callTrunkDestination) == 0)
				{
					echo $sql;
					echo Yii::t('yii','Prefix not found to destination');
					
					exit;
				}


				if(substr("$yournumber", 0, 4) == 1111)
		          	$yournumber = str_replace(substr($yournumber, 0, 7), "", $yournumber);

		          if(substr("$destination", 0, 4) == 1111)
		          	$destination = str_replace(substr($destination, 0, 7), "", $destination);

		          $yournumber 	 = $yournumber;
                    $providertech   = $callTrunk[0]['rc_providertech'];
                    $ipaddress      = $callTrunk[0]['rc_providerip'];
                    $removeprefix   = $callTrunk[0]['rc_removeprefix'];
                    $prefix         = $callTrunk[0]['rc_trunkprefix'];

                    if (strncmp($yournumber, $removeprefix, strlen($removeprefix)) == 0)
                         $yournumber = substr($yournumber, strlen($removeprefix));


                    $dialstr = "$providertech/$ipaddress/$prefix$yournumber";

                    // gerar os arquivos .call
                    $call = "Channel: " . $dialstr . "\n";
                    $call .= "Callerid: " . $user . "\n";
                    $call .= "Context: billing\n";
                    $call .= "Extension: " . $yournumber. "\n";
                    $call .= "Priority: 1\n";
                    $call .= "Set:CALLED=" . $yournumber. "\n";
                    $call .= "Set:TARRIFID=" . $callTrunk[0]['id_rate']. "\n";
                    $call .= "Set:SELLCOST=" . $callTrunk[0]['rateinitial']. "\n";
                    $call .= "Set:BUYCOST=" . $callTrunk[0]['buyrate']. "\n";
                    $call .= "Set:CIDCALLBACK=1\n";
                    $call .= "Set:IDUSER=" . $result[0]['id']. "\n";
                    $call .= "Set:IDPREFIX=" . $callTrunk[0]['id_prefix']. "\n";
                    $call .= "Set:IDTRUNK=" . $callTrunk[0]['id_trunk']. "\n";
                    $call .= "Set:IDPLAN=" . $result[0]['id_plan']. "\n";
                    
                    $call .= "Set:SECCALL=" . $destination . "\n";
                    
                   //Yii::log(print_r($call,true), 'error');
                    
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
				
		
				echo Yii::t('yii','CallBack Success');
			}
			
		}
	}

}

/*
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>              
<script type="text/javascript">	
	function submitForm() {
		var number = document.getElementById('number').value;
		var user = document.getElementById('user').value;
		if (number == '') {
			alert('Numero invalido');
			exit;
		}
	    	$.ajax({
	          type: "GET",
	          url: "http://ip/mbilling/index.php/callFree?user="+user+"&number="+number,
	          success: function(returnValue){
	               alert("Su telefono va llamar");
	          },
	          error: function(request,error) {
	          	alert("error");
	          }
	    	});
	}
</script>
<form method='GET' >
    <input name="number" type="text" class="input" id="number" size="10" style="font-family: 'Handlee', cursive" />
    <input type="hidden" name="user" id='user' value="prueba">
    <input name="button" type="button" value="Ll&aacute;mame" onclick="return submitForm();">
</form>

*/