<?php
/**
 * Modelo para a tabela "Call".
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Todos os direitos reservados.
 * ###################################
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 19/09/2012
 */

class Servers extends Model
{
	protected $_module = 'servers';
	/**
	 * Retorna a classe estatica da model.
	 *
	 * @return Prefix classe estatica da model.
	 */
	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	/**
	 *
	 *
	 * @return nome da tabela.
	 */
	public function tableName() {
		return 'pkg_servers';
	}

	/**
	 *
	 *
	 * @return nome da(s) chave(s) primaria(s).
	 */
	public function primaryKey() {
		return 'id';
	}

	/**
	 *
	 *
	 * @return array validacao dos campos da model.
	 */
	public function rules() {
		return array(
			array( 'host', 'required' ),
			array( 'status, weight', 'numerical', 'integerOnly'=>true ),
			array( 'host', 'length', 'max'=>100 ),
			array( 'description', 'length', 'max'=>500 ),
			array( 'password, username', 'length', 'max'=>50 ),
			array( 'type, port', 'length', 'max'=>20 ),
			array( 'password', 'checkpassword' ),
		);
	}

	public function checkpassword() {
		$sql = "SELECT password FROM pkg_user WHERE password = :password";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":password", $this->password, PDO::PARAM_STR);
		$records =  $command->queryAll();

		if ( count( $records ) > 0 ) {
			$this->addError( $attribute, Yii::t( 'yii', 'This password in in use' ) );
		}
	}

	public function afterSave() {
		$sql = "SELECT * FROM pkg_servers WHERE type = 'sipproxy' AND status = 1";
		$resultOpensips = Yii::app()->db->createCommand( $sql )->queryAll();


		foreach ( $resultOpensips as $key => $server ) {


			$hostname = $server['host'];
			$dbname = 'opensips';
			$table = 'dispatcher';
			$user = $server['username'];
			$password = $server['password'];
			$port = $server['port'];

			$dsn='mysql:host='.$hostname.';dbname='.$dbname;

			try {
				$con=new CDbConnection( $dsn, $user, $password );
			} catch ( Exception $e ) {
				return;
			}

			$con->active=true;

			$sql = "TRUNCATE $dbname.$table";
			$con->createCommand( $sql )->execute();
			$sql = "SELECT * FROM pkg_servers WHERE (type = 'asterisk' OR type = 'mbilling')  
						AND status = 1 AND weight > 0";
			$resultFS = Yii::app()->db->createCommand( $sql )->queryAll();

			foreach ( $resultFS as $key => $server ) {
				$sql = "INSERT INTO $dbname.$table (setid,destination,weight,description) VALUES ('1','sip:".$server['host'].":5060','".$server['weight']."','".$server['description']."')";
				$con->createCommand( $sql )->execute();

			}
		}
	}

}
