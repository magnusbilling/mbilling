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
 * 17/01/2016
 */

/*


*/

class BDServiceController extends Controller
{

	public function init()
	{

		$config = LoadConfig::getConfig();

		$startSession = strlen(session_id()) < 1 ? session_start() : null;
		if (!Yii::app()->session['id_user']) {
			$user      = $_POST['user'];
			$password  = $_POST['pass'];

			$condition = "(username COLLATE utf8_bin LIKE :user OR username LIKE :user OR email COLLATE utf8_bin LIKE :user)";

			$sql       = "SELECT pkg_user.id, username, id_group, id_plan, pkg_user.firstname, pkg_user.lastname , 
											id_user_type, id_user, loginkey, active, password 
											FROM pkg_user JOIN pkg_group_user ON id_group = pkg_group_user.id  
											WHERE $condition";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":user", $user, PDO::PARAM_STR);
			$result = $command->queryAll();

			if(!isset($result[0]['username']) || sha1($result[0]['password']) != $password){
				Yii::app()->session['logged'] = false;
				echo json_encode(array(
					'success' => false,
					'msg' => 'Usuário e/ou login incorretos'
				));
				exit;
			}			
			
			if(!$result) {
				Yii::app()->session['logged'] = false;
				echo json_encode(array(
					'success' => false,
					'msg' => 'Usuário e/ou login incorretos'
				));
				exit;
			}

			if($result[0]['active'] == 0){
				Yii::app()->session['logged'] = false;
				echo json_encode(array(
					'success' => false,
					'msg' => 'Username is disabled'
				));
				exit;
			}
			$user                                = $result[0];

			Yii::app()->session['isAdmin']       = $user['id_user_type'] == 1 ? true : false;
			Yii::app()->session['isAgent']       = $user['id_user_type'] == 2 ? true : false;
			Yii::app()->session['isClient']      = $user['id_user_type'] == 3 ? true : false;
			Yii::app()->session['isClientAgent'] = false;
			Yii::app()->session['id_plan']       = $user['id_plan'];
			Yii::app()->session['credit']        = isset($user['credit']) ? $user['credit'] : 0;
			Yii::app()->session['username']      = $user['username'];
			Yii::app()->session['logged']        = true;
			Yii::app()->session['id_user']       = $user['id'];
			Yii::app()->session['id_agent']       = is_null($user['id_user']) ? 1 : $user['id_user'];
			Yii::app()->session['name_user']     = $user['firstname']. ' ' . $user['lastname'];
			Yii::app()->session['id_group']      = $user['id_group'];
			Yii::app()->session['user_type']     = $user['id_user_type'];

			$sql = "SELECT m.id, action, show_menu, text, module, icon_cls, 
								m.id_module FROM pkg_group_module gm 
								INNER JOIN pkg_module m ON gm.id_module = m.id 
								WHERE id_group =". $user['id_group'];
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_group", $user['id_group'], PDO::PARAM_STR);
			$result = $command->queryAll();


			Yii::app()->session['currency']      = $config['global']['base_currency'];

		}
		
		if (!isset(Yii::app()->session['id_user'])) {
			echo "You not have permission to open this page. Contact administrator!";
			exit;
		}
		$config = LoadConfig::getConfig();

		$sql = "INSERT IGNORE INTO pkg_configuration  VALUES (1000, 'BDService Username', 'BDService_username', '', 'BDService username', 'global', '1');";
		Yii::app()->db->createCommand($sql)->execute();

		$sql = "INSERT IGNORE INTO pkg_configuration  VALUES (1001, 'BDService token', 'BDService_token', '', 'BDService token', 'global', '1');";
		Yii::app()->db->createCommand($sql)->execute();

		$sql = "INSERT IGNORE INTO pkg_configuration  VALUES (1002, 'BDService flexiload values', 'BDService_flexiload', '10-1000', 'BDService flexiload values', 'global', '1');";
		Yii::app()->db->createCommand($sql)->execute();

		$sql = "INSERT IGNORE INTO pkg_configuration  VALUES (1003, 'BDService bkash values', 'BDService_bkash', '50-2500', 'BDService bkash values', 'global', '1');";
		Yii::app()->db->createCommand($sql)->execute();

		$sql = "INSERT IGNORE INTO pkg_configuration  VALUES (1004, 'BDService currency translation', 'BDService_cambio', '0.01', 'BDService currency translation', 'global', '1');";
		Yii::app()->db->createCommand($sql)->execute();
		$sql = "INSERT IGNORE INTO pkg_configuration  VALUES (1005, 'BDService agent profit', 'BDService_agent', '2', 'BDService agent profit', 'global', '1');";
		Yii::app()->db->createCommand($sql)->execute();


		$sql = "CREATE TABLE IF NOT EXISTS `pkg_BDService` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `id_user` int(11) NOT NULL,
			  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;";
		Yii::app()->db->createCommand($sql)->execute();

		$sql = "INSERT IGNORE INTO pkg_BDService (id) VALUES (15254);";
		Yii::app()->db->createCommand($sql)->execute();

		parent::init();
	}

	public function actionRead()
	{
		

		$sql = "SELECT id FROM pkg_BDService ORDER BY id DESC";
		$resultID = Yii::app()->db->createCommand($sql)->queryAll();

		
		$id = $resultID[0]['id'] + 1;


		$allow_values_flexiload = $config['global']['BDService_flexiload'];
		$allow_values_bkash = $config['global']['BDService_bkash'];
		$cambio = $config['global']['BDService_cambio'];
		$agent_comission = $config['global']['BDService_agent'];

		$user= $config['global']['BDService_username'];
		$token= $config['global']['BDService_token'];


		if (isset($_POST['traking']) && isset($_POST['id'])) {
			$url = "http://takasend.net/ezzeapi/status?id=".$_POST['id']."&user=$user&key=$token";
			$result = file_get_contents($url);
			echo '<div id="container"><div id="form"> <div id="box-4">';
			echo "<font color=red>$result</font>";
			echo '</div></div></div>';

			$this->fistForm();
			exit;

		}

		if (isset($_POST['number'])) {


			if ($_POST['amount'] < $_POST['min'] ) {
				echo '<div id="container"><div id="form"> <div id="box-4">';
				echo "<font color=red>Amount is < then minimal allowed</font>";
				echo '</div></div></div>';

				$this->secondForm($_POST['min'],$_POST['max']);

			}else if ($_POST['amount'] > $_POST['max'] ) {
				echo '<div id="container"><div id="form"> <div id="box-4">';
				echo "<font color=red>Amount is > then maximum allowed</font>";
				echo '</div></div></div>';

				$this->secondForm($_POST['min'],$_POST['max']);

			}
			elseif (strlen($_POST['number']) > 15  || strlen($_POST['number']) < 11) {
				echo '<div id="container"><div id="form"> <div id="box-4">';
				echo "<font color=red>Number invalid, try again</font>";
				echo '</div></div></div>';

				$this->secondForm($_POST['min'],$_POST['max']);
			}
			else{

				$cost = $_POST['amount'] * $cambio;

				$sql = "SELECT credit, creditlimit, id_user FROM pkg_user WHERE id = :id";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_STR);
				$resultUser = $command->queryAll();

				if ($resultUser[0]['credit'] + $resultUser[0]['creditlimit'] < $cost) {
					echo '<div id="container"><div id="form"> <div id="box-4">';
					echo "<font color=red>You no have enough credit to transfer</font>";
					echo "<br><a href='/mbilling/index.php/bDService/read'>Back</a>";
					echo '</div></div></div>';
					exit;
				}
				

				$this->secondForm($_POST['min'],$_POST['max']);

				//check if agent have credit
				if ($resultUser[0]['id_user'] > 1) {
					$sql = "SELECT credit, creditlimit, id_user FROM pkg_user WHERE id = :id";
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_STR);
					$resultAgent = $command->queryAll();

					if ($resultAgent[0]['credit'] + $resultAgent[0]['creditlimit'] < $cost) {					

						echo '<div id="container"><div id="form"> <div id="box-4">';
						echo "<font color=red>Your Agent no have enough credit to transfer</font>";
						echo "<br><a href='/mbilling/index.php/bDService/read'>Back</a>";
						echo '</div></div></div>';
						exit;
					}
				}					
				

				
				$url = "http://takasend.net/ezzeapi/request/".$_POST['service']."?number=".$_POST['number']."&amount=".$_POST['amount']."&type=".$_POST['type']."&id=".$id."&user=".$user."&key=".$token."";
				$result = file_get_contents($url);

				//$result = 'SUCCESS';
				$sql = "INSERT INTO  pkg_BDService (id_user) VALUES (:id)";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_STR);
				$command->execute();

				if (preg_match("/ERROR|error/", $result)) {
					echo '<div id="container"><div id="form"> <div id="box-4">';
					echo "<font color=red>".$result."</font>";
					echo '</div></div></div>';
					$this->secondForm($_POST['min'],$_POST['max']);
				}
				elseif (preg_match("/SUCCESS|success/", $result)) {
					
					$number = $_POST['number'];
					$sql = "UPDATE  pkg_user SET credit = credit - :cost WHERE id = :id";
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":cost", $cost, PDO::PARAM_STR);					
					$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_STR);
					$command->execute();

					$costUser = $cost * -1;
					$values = ":id,:costUser,'Credit tranfered to mobile ".$number." via ".$_POST['service'].", ID: ".$id." ',1";
					$sql = "INSERT INTO pkg_refill (id_user,credit,description,payment) VALUES ($values)";
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":costUser", $costUser, PDO::PARAM_STR);					
					$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_STR);					
					$command->execute();


					if ($resultUser[0]['id_user'] > 1) {
						$costAgent = $cost - ($cost * ($agent_comission / 100));
						$sql = "UPDATE  pkg_user SET credit = credit - :costAgent WHERE id = :id";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":costAgent", $costAgent, PDO::PARAM_STR);					
						$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_STR);					
						$command->execute();

						$costAgent = $costAgent * -1;
						$values = ":id,:costAgent,'Credit tranfered to mobile ".$number." via ".$_POST['service']." , ID: ".$id." ' ,1";
						$sql = "INSERT INTO pkg_refill (id_user,credit,description,payment) VALUES ($values)";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":costAgent", $costAgent, PDO::PARAM_STR);					
						$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_STR);					
						$command->execute();

					}

					echo '<div id="container"><div id="form"> <div id="box-4">';
					echo "Confirm the transfer via ". $_POST['service'] ." : ". $_POST['amount'] . ' BDT to mobile  '. $_POST['number']."<br><br>";
					echo "ID to traking: ". $id."<br><br>";
					echo "Total in euros: ". $_POST['amount'] * $cambio."<br>";
					echo "<br><a id='backLink' href='/mbilling/index.php/bDService/read'>Back</a>";
					echo '</div></div></div>';
				}
				
			}

		}

		else if (!isset($_POST['service'])) {			
			$this->fistForm();
		}
		elseif (isset($_POST['service'])) {

			if ($_POST['service'] == 'flexiload')
				$values = explode("-", $allow_values_flexiload);
			else
				$values = explode("-", $allow_values_bkash);
			

			$minValue = $values[0] ;
			$maxValue = $values[1] ;;
			$this->secondForm($minValue,$maxValue);
		}
		
	}


	public function fistForm()
	{
		echo '<div id="container"><div id="form"> <div id="box-4">';
		echo '<form action="" id="form1" method="POST">
			<fieldset class="well">
				<div class="control-group">
					
					<div class="control-label">
						<label >Select Service to new transfer<span class="star">&nbsp;*</span></label>
					</div>
					<div class="controls">
						<select name="service">
						  <option value="flexiload">Flexiload</option>
						  <option value="bkash">Bkash</option>
						</select>
					</div>
					</div>

					<br>
					<div class="controls">
						<button type="submit" class="btn btn-primary">Next</button>
					</div>
		
			</fieldset>
		</form>';
		echo '</div></div></div>';


		echo '<br><br><br><br><div id="container"><div id="form"> <div id="box-4">';
		echo '<form action="" id="formTraking" method="POST">
			<fieldset class="well">
				<div class="control-group">
					
					<div class="control-label">
						<label >Insert ID to traking<span class="star">&nbsp;*</span></label>
					</div>
					
					<div class="controls">
						<input type="text" name="id" value = "">						
					</div>
					<input type="hidden" name="traking" value="1">
					<br>
					<div class="controls">
						<button type="submit" class="btn btn-primary">Traking</button>
					</div>
		
			</fieldset>
		</form>';
		echo '</div></div></div>';
	}

	public function secondForm($min,$max)
	{
		$_POST['number'] = isset($_POST['number']) ? $_POST['number'] : '';
		$_POST['amount'] = isset($_POST['amount']) ? $_POST['amount'] : '';

		$type1 = $_POST['service'] == 'flexiload' ? 'Prepaid' : 'Personal';
		$type2 = $_POST['service'] == 'flexiload' ? 'Postpaid' : 'Agent';

		$selected1 = isset($_POST['type']) && $_POST['type'] == 1 ? 'selected' : '';
		$selected2 = isset($_POST['type']) && $_POST['type'] == 2 ? 'selected' : '';
		echo '<div id="container"><div id="form"> <div id="box-4">';
		echo '<form action="" id="form2" method="POST">
			<fieldset class="well">
				<div class="control-group">
					<div class="control-label">
						<label >Selected Service: '.ucfirst($_POST['service']).'</label>
					</div>
					<br>
					<div class="control-label">
						<label >Number<span class="star">&nbsp;*</span></label>
					</div>
					<div class="controls">
						<input type="text" name="number" size="25" required="" aria-required="true" value = "'.$_POST['number'].'">
					</div>
					
					<div class="control-label">
						<label >Amount<span class="star">&nbsp;(Min: '.$min.' BDT, Max: '.$max.' BDT) </span></label>
					</div>
					<div class="controls">
						<input id="valid_age" onkeyup="callAjax(this.value, '.trim($config['global']['BDService_cambio']).')" type="number" name="amount" value = "'.$_POST['amount'].'">						
					</div>
					<div id="rsp_age" style="font-size: 11px; color: red;"></div>
					<input type="hidden" name="service" value="'.$_POST['service'].'">
					<input type="hidden" name="type" value="1">
					<input type="hidden" name="min" value="'.$min.'">
					<input type="hidden" name="max" value="'.$max.'">
					<br>
					<div class="controls">
						<button type="submit" class="btn btn-primary">Send Credit</button>
					</div>
		
			</fieldset>
		</form>';
		echo '</div></div></div>';
	}




}

?>

<style type="text/css">

  #box-4 {

    margin: 20px 100px 20px;
    padding: 20px;
    border-bottom: 1px solid #CCC;
    -moz-border-radius-topleft: 5px;
    -moz-border-radius-topright: 5px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
    background: #EEE;
    }


  * {
      margin: 0;
      padding: 0;
  }
   
  fieldset {
      border: 0;
  }
   
  body, input, select, textarea, button {
      font-family: sans-serif;
      font-size: 1em;
  }
   
  .grupo:after {
      content: ".";
      display: block;
      height: 0;
      clear: both;
      visibility: hidden;
  }
   
  .campo {
      margin-bottom: 1em;
  }
   
  .campo label {
      margin-bottom: 0.2em;
      color: #666;
      display: block;
  }
   
  fieldset.grupo .campo {
      float:  left;
      margin-right: 1em;
  }
   
  .campo input[type="text"],
  .campo input[type="email"],
  .campo input[type="url"],
  .campo input[type="tel"],
  .campo select,
  .campo textarea {
      padding: 0.2em;
      border: 1px solid #CCC;
      box-shadow: 2px 2px 2px rgba(0,0,0,0.2);
      display: block;
  }
   
  .campo select option {
      padding-right: 1em;
  }
   
  .campo input:focus, .campo select:focus, .campo textarea:focus {
      background: #FFC;
  }
   
  .campo label.checkbox {
      color: #000;
      display: inline-block;
      margin-right: 1em;
  }
   
  .botao {
      font-size: 1.5em;
      background: #F90;
      border: 0;
      margin-bottom: 1em;
      color: #FFF;
      padding: 0.2em 0.6em;
      box-shadow: 2px 2px 2px rgba(0,0,0,0.2);
      text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
  }
   
  .botao:hover {
      background: #FB0;
      box-shadow: inset 2px 2px 2px rgba(0,0,0,0.2);
      text-shadow: none;
  }
   
  .botao, select, label.checkbox {
      cursor: pointer;
  }

</style>



<script type="text/javascript">

  function callAjax(value, cambio)
  {
  	euro = value * cambio;
  	document.getElementById("rsp_age").innerHTML=euro.toFixed(2) +" EUR"; 

  }

</script>
