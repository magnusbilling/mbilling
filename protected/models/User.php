<?php
/**
 * Modelo para a tabela "Boleto".
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2016 MagnusBilling. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v3
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 19/09/2012
 */

class User extends Model
{
	protected $_module = 'user';
	protected $newPassword = NULL;
	/**
	 * Return the static class of model.
	 *
	 * @return User classe estatica da model.
	 */
	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	/**
	 *
	 *
	 * @return name of table.
	 */
	public function tableName() {
		return 'pkg_user';
	}

	/**
	 *
	 *
	 * @return name of primary key(s).
	 */
	public function primaryKey() {
		return 'id';
	}

	/**
	 *
	 *
	 * @return array validation of fields of model.
	 */
	public function rules() {
		return array(
			array( 'username, password', 'required' ),
			array( 'id_user, id_group, id_plan, id_offer, active, enableexpire, expiredays,
				 typepaid, creditlimit, credit_notification, restriction, callingcard_pin, callshop, plan_day,
				record_call, active_paypal, boleto, boleto_day', 'numerical', 'integerOnly'=>true ),
			array( 'language', 'length', 'max'=>5 ),
			array( 'username, zipcode, phone, mobile, vat', 'length', 'max'=>20 ),
			array( 'city, state, country, loginkey', 'length', 'max'=>40 ),
			array( 'lastname, firstname, company_name, redial, prefix_local', 'length', 'max'=>50 ),
			array( 'company_website', 'length', 'max'=>60 ),
			array( 'address, email, description, doc', 'length', 'max'=>100 ),
			array( 'credit', 'numerical' ),
			array( 'expirationdate, password, lastuse', 'length', 'max'=>100 ),
			array( 'username', 'checkusername' ),
			array( 'password', 'checksecret' ),
			array( 'id_group_agent', 'checkGroupUserAgent' ),
			array( 'username', 'unique', 'caseSensitive' => 'false' ),

		);
	}

	public function checkusername( $attribute, $params ) {
		if ( preg_match( '/ /', $this->username ) )
			$this->addError( $attribute, Yii::t( 'yii', 'No space allow in username' ) );
	}

	public function checksecret( $attribute, $params ) {
		if ( preg_match( '/ /', $this->password ) )
			$this->addError( $attribute, Yii::t( 'yii', 'No space allow in password' ) );
		if (  $this->password == '123456' ||  $this->password == '12345678' ||  $this->password == '012345')
			$this->addError( $attribute, Yii::t( 'yii', 'No use sequence in the pasword' ) );
		if (  $this->password == $this->username)
			$this->addError( $attribute, Yii::t( 'yii', 'Password cannot be equal username' ) );
	}

	public function checkGroupUserAgent( $attribute, $params ) {
		if ( Yii::app()->session['user_type'] == 1 && $this->id_group_agent > 0 ) {
			$sql = "SELECT * FROM pkg_group_user WHERE id_user_type = 3 AND id = :id";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", $this->id_group_agent, PDO::PARAM_INT);
			$records = $command->queryAll();

			if ( count( $records ) == 0 ) {
				$this->addError( $attribute, Yii::t( 'yii', 'Group no allow for Agent users' ) );
			}
		}
	}


	public function relations() {
		return array(
			'idGroup' => array( self::BELONGS_TO, 'GroupUser', 'id_group' ),
			'idPlan' => array( self::BELONGS_TO, 'Plan', 'id_plan' ),
			'idUser' => array( self::BELONGS_TO, 'User', 'id_user' ),
		);
	}

	public function beforeSave() {

		$methodModel = Methodpay::Model()->findAll(
				"payment_method=:field1 AND active=:field12", 
				array(
					"field1" => 'SuperLogica',
					"field12" => '1'
				)
			);
		$groupType = GroupUser::model()->findAll(
				"id=:field1",
				array(
					'field1' => $this->id_group
				)
			);		
		
		if ( $this->getIsNewRecord() ) {
			
			if (Yii::app()->session['isAdmin'] == true && $groupType[0]->id_user_type == 1) {
				$this->password = sha1($this->password);
			}
			
			if (count($methodModel) > 0 && $groupType[0]->id_user_type == 3) {

				if (strlen($this->lastname) < 5) {
					$error = Yii::t( 'yii','lastname');
				}else if (strlen($this->firstname) < 5) {
					$error = Yii::t( 'yii','firstname');
				}else if (strlen($this->doc) < 11) {
					$error = Yii::t( 'yii','CPF');
				}else if (!preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/',$this->email)){
					$error = Yii::t( 'yii','email');
				}
				if (isset($error)) {
					echo json_encode(array(
						'success' => false,
						'rows' => array(),
						'errors' => Yii::t('yii',$error) .' '. Yii::t('yii','is required')
					));
					exit();
				}			
				if (isset($methodModel[0]->SLAppToken)) {			
					$response  = SLUserSave::saveUserSLCurl($this,$methodModel[0]->SLAppToken
										,$methodModel[0]->SLAccessToken);
				}

				$this->id_sacado_sac = $response[0]->data->id_sacado_sac;

			}
		

			if ( Yii::app()->session['user_type'] == 2 ) {
				$this->id_user  = Yii::app()->getSession()->get( 'id_user' );

				$sql = "SELECT id_group_agent FROM pkg_user WHERE id = :id";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id", $this->id_user, PDO::PARAM_INT);
				$result = $command->queryAll();

				$this->id_group = $result[0]['id_group_agent'];

			}
			else
				$this->id_user  = 1;

		}else{	
			if (isset($methodModel[0]->SLAppToken)) {	
				$response  = SLUserSave::saveUserSLCurl($this,$methodModel[0]->SLAppToken
										,$methodModel[0]->SLAccessToken,false);
			}	

			$rows = array_key_exists('rows', $_POST) ? json_decode($_POST['rows'], true) : $_POST;

			$sql = "SELECT gu.id_user_type as typeActual
					FROM pkg_user u 
						INNER JOIN pkg_group_user gu
							ON u.id_group = gu.id
					where u.id = :id";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", $this->id, PDO::PARAM_INT);
			$groupUserAtualResult = $command->queryAll();


			if ( isset($groupUserAtualResult[0]['typeActual']) && $groupUserAtualResult[0]['typeActual'] == 1 && isset($rows['password'])) {
				Util::insertLOG('EDIT',Yii::app()->session['id_user'],$_SERVER['REMOTE_ADDR'],'User try change the password');

				echo json_encode(array(
					'success' => false,
					'rows' => array(),
					'errors' => Yii::t('yii','You are not allowed to edit this field')
				));
				exit;
			}
			
			if ( isset($groupType[0]->id_user_type) && $groupType[0]->id_user_type != $groupUserAtualResult[0]['typeActual']) {
				echo json_encode(array(
					'success' => false,
					'rows' => array(),
					'errors' => Yii::t('yii','You cannot change user type group')
				));
				exit;
			}
		}
		
		$this->id_plan = $this->id_plan < 1 ? NULL : $this->id_plan;
		$this->id_group_agent = $this->id_group_agent === 0 || !is_numeric( $this->id_group_agent ) ? NULL : $this->id_group_agent;
		$this->id_offer       = $this->id_offer === 0 ? NULL : $this->id_offer;
		$this->expirationdate = $this->enableexpire == 0 ? '0000-00-00 00:00:00' : $this->expirationdate;

		return parent::beforeSave();
	}

	public function afterSave() {

		if ( $this->isNewRecord ) {

			$sql = "SELECT id_user_type FROM pkg_group_user WHERE id = :id";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", $this->id_group, PDO::PARAM_INT);
			$groupUserResult = $command->queryAll();


			if ( $groupUserResult[0]['id_user_type'] == 3 ) {

				$model = new Sip;
				$model->id_user = $this->id;
				$model->accountcode = $this->username;
				$model->name = $this->username;
				$model->allow = 'g729,gsm,alaw,ulaw';
				$model->host = 'dynamic';
				$model->insecure = 'no';
				$model->defaultuser = $this->username;
				$model->secret = $this->password;
				$model->save();
			}

		}elseif ( isset( $this->id_group_agent ) and $this->id_group_agent > 1 ) {
			$sql = "UPDATE pkg_user SET id_group = :id_group WHERE id_user = :id_user";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $this->id, PDO::PARAM_INT);
			$command->bindValue(":id_group", $this->id_group_agent, PDO::PARAM_INT);
			$command->execute();



		}else {
			$sql = "UPDATE pkg_sip SET accountcode = :accountcode WHERE id_user = :id_user";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":accountcode", $this->username, PDO::PARAM_STR);
			$command->bindValue(":id_user", $this->id, PDO::PARAM_INT);
			$command->execute();
			if($_SERVER['HTTP_HOST'] != 'localhost'){
				$asmanager = new AGI_AsteriskManager;
				$asmanager->connect( 'localhost', 'magnus', 'magnussolution' );
				$asmanager->Command( "sip reload" );
				$asmanager->disconnect();
			}
		}


		if ( $this->id_offer > 0 ) {
			$sql = "SELECT id_offer FROM pkg_offer_use WHERE id_user = :id_user 
					AND releasedate = '0000-00-00 00:00:00' AND status = 1";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $this->id, PDO::PARAM_INT);
			$result =  $command->queryAll();


			if ( count( $result ) > 0 ) {
				$sql = "UPDATE pkg_offer_use SET releasedate = now(), status = 0 
								WHERE id_user= :id AND releasedate = '0000-00-00 00:00:00' 
								AND status = 1";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id", $this->id, PDO::PARAM_INT);
				$command->execute();
			}

			$sql = "INSERT INTO pkg_offer_use (id_user, id_offer, status, month_payed) 
						VALUES (:id_user, :id_offer, 1, 1)";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $this->id, PDO::PARAM_INT);
			$command->bindValue(":id_offer", $this->id_offer, PDO::PARAM_INT);
			$command->execute();
		}
		else if ( $this->id_offer == 0 ) {
				$sql = "UPDATE pkg_offer_use SET releasedate = now(), status = 0 WHERE id_user = :id_user 
							AND releasedate = '0000-00-00 00:00:00' AND status = 1";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_user", $this->id, PDO::PARAM_INT);
				$command->execute();
			}

		$this->createCallshopRates();

		parent::afterSave();
	}

	public function createCallshopRates() {
		$sql = "SELECT id_plan, id_user_type, id_user FROM pkg_user JOIN pkg_group_user 
				ON id_group = pkg_group_user.id WHERE pkg_user.id = :id_user";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id_user", $this->id, PDO::PARAM_INT);
		$resultUser =  $command->queryAll();

		if ( $resultUser[0]['id_user_type'] != 3 ) {
			return;
		}

		$sql = "SELECT * FROM pkg_rate_callshop WHERE id_user = :id_user";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id_user", $this->id, PDO::PARAM_INT);
		$resultCallshop =  $command->queryAll();

		if ( $this->callshop == 0 ) {
			$sql = 'DELETE FROM pkg_rate_callshop WHERE id_user = :id_user';
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $this->id, PDO::PARAM_INT);
			$command->execute();
		}
		elseif ( $this->callshop == 1 && count( $resultCallshop ) == 0 ) {
			//Create rates for callshop
			if ( $resultUser[0]['id_user'] > 1 ) {
				//if is a agent client, select in pkg_rate_agent
				$sql = "SELECT prefix, destination, rateinitial, initblock, billingblock 
								FROM pkg_rate_agent JOIN pkg_prefix 
								ON pkg_rate_agent.id_prefix = pkg_prefix.id
								WHERE id_plan = '".$resultUser[0]['id_plan']."'";
			}else {

				$sql = "SELECT prefix, destination, rateinitial, initblock, billingblock 
								FROM pkg_rate JOIN pkg_prefix ON pkg_rate.id_prefix = pkg_prefix.id
								WHERE  pkg_rate.status = 1 AND id_plan = :id_plan";
			}
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_plan", $resultUser[0]['id_plan'], PDO::PARAM_INT);
			$resultPrefix =  $command->queryAll();



			$sqlRate = array();
			for ( $i = 0; $i < count( $resultPrefix ); $i++ ) {
				$sqlRate[] = "('".$this->id."', '".$resultPrefix[$i]['prefix']."', '".$resultPrefix[$i]['destination']."', '".$resultPrefix[$i]['rateinitial']."',
					'".$resultPrefix[$i]['initblock']."', '".$resultPrefix[$i]['billingblock']."')";
			}
			$sqlRateAgent = 'INSERT INTO pkg_rate_callshop (id_user , dialprefix,  destination , 
							buyrate, minimo , block)
							VALUES '.implode( ',', $sqlRate ).';';
			try {
				Yii::app()->db->createCommand( $sqlRateAgent )->execute();
			} catch ( Exception $e ) {

			}

		}
	}
}
?>
