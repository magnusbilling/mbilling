<?php

/**
 * Url for customer register http://ip/billing/index.php/user/add .
 */
class TransferToMobileController extends BaseController
{
	private $url="https://fm.transfer-to.com/cgi-bin/shop/topup?";

	public function init() {

		$config = LoadConfig::getConfig();

		$startSession = strlen(session_id()) < 1 ? session_start() : null;

		if (!Yii::app()->session['id_user']) {
			$user      = $_POST['user'];
			$password  = $_POST['pass'];

			$condition = "(username COLLATE utf8_bin LIKE :user OR username LIKE :user 
				OR email COLLATE utf8_bin LIKE :user)";

			$sql       = "SELECT pkg_user.id, username, id_group, id_plan, pkg_user.firstname, 
							pkg_user.lastname , id_user_type, id_user, loginkey, active, password 
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

			$sql    = "SELECT m.id, action, show_menu, text, module, icon_cls, m.id_module 
							FROM pkg_group_module gm INNER JOIN pkg_module m ON gm.id_module = m.id 
							WHERE id_group = :id_group";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_group", $user['id_group'], PDO::PARAM_STR);
			$result = $command->queryAll();
			Yii::app()->session['currency']      = $config['global']['base_currency'];

		}

		parent::init();
	}


	public function actionMsisdn_info() {

		$config = LoadConfig::getConfig();

		if ( isset( $_GET['number'] ) ) {
			$number = '+'.$_GET['number'];

			if ( isset( $config['global']['fm_transfer_to_username'] ) ) {


				$login  = $config['global']['fm_transfer_to_username'];
				$token   = $config['global']['fm_transfer_to_ token'];
				$agentProfit  = $config['global']['fm_transfer_to_profit'];

				$key=time();
				$md5=md5( $login.$token.$key );

				if ( isset( $_GET['amount'] ) && $_GET['amount'] > 0 ) {
					$product = $_GET['amount'];
					$cost = explode( Yii::app()->session['currency'], $_GET['cost'] );
					$cost = $cost[1];

					if ( Yii::app()->session['currency'] == 'COP' ) {
						$cost = $cost * 3066;
						Yii::app()->session['currency'] = '$';
					}




					$sql = "SELECT credit, creditlimit, id_user FROM pkg_user WHERE id = :id ";
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_INT);
					$resultUser = $command->queryAll();


					if ( $resultUser[0]['credit'] + $resultUser[0]['creditlimit'] < $cost ) {
						echo json_encode( array(
								'success' => false,
								'msg' => 'You no have enough credit to transfer'
							) );
						exit;
					}
					//check if agent have credit
					if ( $resultUser[0]['id_user'] > 1 ) {
						$sql = "SELECT credit, creditlimit, id_user FROM pkg_user WHERE id = :id ";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id", $resultUser[0]['id_user'], PDO::PARAM_INT);
						$resultAgent = $command->queryAll();

						if ( $resultAgent[0]['credit'] + $resultAgent[0]['creditlimit'] < $cost ) {
							echo json_encode( array(
									'success' => false,
									'msg' => 'Your Agent no have enough credit to transfer'
								) );
							exit;
						}
					}

					$url = $this->url."login=$login&key=$key&md5=$md5&destination_msisdn=$number&msisdn=$number&delivered_amount_info=1&product=$product&action=topup";
					$result = file_get_contents( $url );


					$result = explode( "error_txt=", $result );

					if ( preg_match( "/Transaction successful/", $result[1] ) ) {
						echo json_encode( array(
								'success' => true,
								'msg' => $result[1]
							) );

						$sql = "UPDATE  pkg_user SET credit = credit - :cost WHERE id = :id";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_INT);
						$command->bindValue(":cost", $cost, PDO::PARAM_STR);
						$command->execute();

						$costUser = $cost * -1;
						$description = 'Credit tranfered to mobile '. $number;
						$values = ":id_user, :costUser, :description, 1";
						$sql = "INSERT INTO pkg_refill (id_user,credit,description,payment) VALUES ($values)";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_INT);
						$command->bindValue(":costUser", $costUser, PDO::PARAM_STR);
						$command->bindValue(":description", $description, PDO::PARAM_STR);
						$command->execute();


						if ( $resultUser[0]['id_user'] > 1 ) {
							$costAgent = $cost - ( $cost * ( $agentProfit / 100 ) );
							$sql = "UPDATE  pkg_user SET credit = credit - :costAgent WHERE id = :id";
							$command = Yii::app()->db->createCommand($sql);
							$command->bindValue(":id", $resultUser[0]['id_user'], PDO::PARAM_INT);
							$command->bindValue(":costAgent", $costAgent, PDO::PARAM_STR);
							$command->execute();

							$costAgent = $costAgent * -1;
							$description = 'Credit tranfered to mobile '. $number;

							$values = ":id_user, :costAgent , :description,1";
							$sql = "INSERT INTO pkg_refill (id_user,credit,description,payment) VALUES ($values)";
							$command = Yii::app()->db->createCommand($sql);
							$command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_INT);
							$command->bindValue(":costAgent", $costAgent, PDO::PARAM_STR);
							$command->bindValue(":description", $description, PDO::PARAM_STR);
							$command->execute();

						}


					}else {

						echo json_encode( array(
								'success' => false,
								'msg' => $result[1]
							) );
					}

				}else {


					$url = $this->url."login=$login&key=$key&md5=$md5&destination_msisdn=$number&action=msisdn_info";
					$result = file_get_contents( $url );
					//echo '<pre>';
					if ( preg_match( "/Transaction successful/", $result ) ) {

						$result = explode( "\n", $result );
						//print_r($result);

						$product_list= explode( ",", substr( $result[7], 13 ) );
						$retail_price_list= explode( ",", substr( $result[8], 18 ) );

						$local_currency =  explode( "=", $result[6] );
						$local_currency = trim( $local_currency[1] );

						$country =  explode( "=", $result[0] );
						$country = trim( $country[1] );

						$operator =  explode( "=", $result[2] );
						$operator = trim( $operator[1] );

			
						$values = array();
						$i = 0;
						foreach ( $product_list as $key => $product ) {
							$values []= array( "$product", $local_currency . ' '.trim( $product ).' = '.$config['global']['fm_transfer_currency'] . ' '. trim( $retail_price_list[$i] ) );
							$i++;
						}
						
						echo json_encode( array(
								'success' => true,
								'rows' => $values,
								'country' => $country,
								'operator' => $operator,
								'fm_transfer_fee' => $config['global']['fm_transfer_show_selling_price']
							) );

					}else {
						$result = explode( "error_txt=", $result );
						echo json_encode( array(
								'success' => false,
								'msg' => $result[1]
							) );
						exit;
					}
				}
			}else {
				echo json_encode( array(
						'success' => false,
						'msg' => 'Service inactive'
					) );
			}
		}
	}


	public function actionPrintRefill() {

		if ( isset( $_GET['id'] ) ) {
			$config = LoadConfig::getConfig();
			$id_refill =  $_GET['id'];
			$sql = "SELECT * FROM pkg_refill WHERE id = :id_refill AND id_user = :id_user";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_refill", $id_refill, PDO::PARAM_INT);
			$command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_INT);
			$resultRefill = $command->queryAll();

			echo $config['global']['fm_transfer_print_header']."<br>";

			$sql = "SELECT * FROM pkg_user WHERE id = :id";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_INT);
			$resultUser = $command->queryAll();

			echo $resultUser[0]['company_name']."<br>";
			if ($resultUser[0]['vat'] > 0) {
				echo "P. IVA: ".$resultUser[0]['vat']."<br>";
			}
			echo $resultRefill[0]['date']."<br>";
			$number = trim(preg_replace("/Credit tranfered to mobile/", "", $resultRefill[0]['description']));
			$number = explode(" ", $number);
			echo "Mobile: ".$number[0]."<br>";
			$amount = number_format($resultRefill[0]['credit'],2) * -1;
			echo "Amount: <input type=text' style='text-align: right;' size='5' value='$amount'> <br><br>";

			echo $config['global']['fm_transfer_print_footer']."<br><br>";

			echo '<td><a href="javascript:window.print()">Print</a></td>';
		}else {
			echo ' Invalid reffil';
		}
	}

}

