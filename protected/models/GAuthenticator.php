<?php
/**
 * Modelo para a tabela "GAuthenticator".
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
 * 01/04/2016
 */

class GAuthenticator extends Model
{
	protected $_module = 'gauthenticator';
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
		return 'pkg_user';
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
			array( 'googleAuthenticator_enable', 'numerical', 'integerOnly'=>true ),
			array( 'google_authenticator_key', 'length', 'max'=>50 )
		);
	}
	public function beforeSave()
	{
		if ($this->googleAuthenticator_enable != 1 && strlen($this->google_authenticator_key) > 5) {
			$values = json_decode($_POST['rows']);
			$this->googleAuthenticator($this->id,$values->code);
		}else{
			if ($this->googleAuthenticator_enable == 1 && strlen($this->google_authenticator_key) > 5 )
				$this->google_authenticator_key = '';			

			$this->googleAuthenticator_enable = $this->googleAuthenticator_enable == 1 ? 2 : $this->googleAuthenticator_enable;
			if ($this->googleAuthenticator_enable == 0)
				$this->google_authenticator_key = '';
		}

		return parent::beforeSave();
	}
	public function googleAuthenticator($id_user,$code)
	{
		require_once ('lib/GoogleAuthenticator/GoogleAuthenticator.php');

		$ga = new PHPGangsta_GoogleAuthenticator();

		$sql = "SELECT google_authenticator_key, username FROM pkg_user WHERE id = :id_user";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id_user", $id_user, PDO::PARAM_STR);
		$result = $command->queryAll();

		$secret = $result[0]['google_authenticator_key'];
		$oneCodePost = $code;

		$checkResult = $ga->verifyCode($secret, $oneCodePost, 2);

		if (!$checkResult) {
		    echo json_encode(array(
				'success' => false,
				'rows' => array(),
				'errors' => Yii::t('yii','Invalid Code')
			));
		    	$info = 'Username '.Yii::app()->session['username'].' try inactive GoogleToken with Invalid Code to user '.$result[0]['username'];
			Util::insertLOG('EDIT',Yii::app()->session['id_user'],$_SERVER['REMOTE_ADDR'],$info);
			exit;
		}else{
			$this->google_authenticator_key = '';
		}


	}
}
