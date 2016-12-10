<?php
/**
 * Modelo para a tabela "Iax".
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
 * 19/06/2016
 */

class Iax extends Model
{
	protected $_module = 'iax';
	/**
	 * Retorna a classe estatica da model.
	 *
	 * @return Iax classe estatica da model.
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
		return 'pkg_iax';
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
			array( 'id_user', 'required' ),
			array( 'id_user,calllimit ', 'numerical', 'integerOnly'=>true ),
			array( 'name, callerid, context, fromuser, fromdomain, md5secret, secret', 'length', 'max'=>80 ),
			array( 'regexten, insecure', 'length', 'max'=>20 ),
			array( 'amaflags, dtmfmode, qualify', 'length', 'max'=>7 ),
			array( 'callgroup, pickupgroup', 'length', 'max'=>10 ),
			array( 'DEFAULTip, ipaddr', 'length', 'max'=>15 ),
			array( 'nat, host', 'length', 'max'=>31 ),
			array( 'language', 'length', 'max'=>2 ),
			array( 'mailbox', 'length', 'max'=>50 ),
			array( 'accountcode', 'length', 'max'=>30 ),
			array( 'rtpholdtimeout', 'length', 'max'=>3 ),
			array( 'deny, permit', 'length', 'max'=>95 ),
			array( 'port', 'length', 'max'=>5 ),
			array( 'type', 'length', 'max'=>6 ),
			array( 'disallow, allow, useragent', 'length', 'max'=>100 ),
			array( 'username', 'checkusername' ),
			array( 'username', 'unique', 'caseSensitive' => 'false' ),
		);
	}

	public function checkusername( $attribute, $params ) {
		if ( preg_match( '/ /', $this->username ) )
			$this->addError( $attribute, Yii::t( 'yii', 'No space allow in username' ) );
	}

	/*
	 * @return array regras de relacionamento.
	 */
	public function relations() {
		return array(
			'idUser' => array( self::BELONGS_TO, 'User', 'id_user' ),
		);
	}

	public function beforeSave() {
		$config = LoadConfig::getConfig();

		$sql = "SELECT username FROM pkg_user WHERE id = :id";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id", $this->id_user, PDO::PARAM_INT);

		$CardResult =  $command->queryAll();

		$this->accountcode = $CardResult[0]['username'];
		$this->name = $this->username == '' ?  $this->accountcode : $this->username;

		if ($config['global']['asterisk_version'] == '1.8') {
			$this->nat = 'yes';
		}else{
			$this->nat = 'force_rport,comedia';
		}

		if ( $this->getIsNewRecord() ) {
			$this->regseconds = 1;
			$this->context = 'billing';
			$this->regexten = $this->name;
			if ( !$this->callerid )
				$this->callerid = $this->name;

		}
		$this->allow = preg_replace( "/,0/", "", $this->allow );
		$this->allow = preg_replace( "/0,/", "", $this->allow );
		return parent::beforeSave();
	}

	public function afterSave() {

		$this->generateFileText();
		return parent::afterSave();
	}

	public function afterDelete() {
		$this->generateFileText();

	}

	public function generateFileText() {

		if ( $_SERVER['HTTP_HOST'] == 'localhost' || preg_match( "/magnusbilling.com/", $_SERVER['HTTP_HOST'] ) ) {
			return;
		}

		$select = 'id, accountcode, name, username, secret, regexten, amaflags, callerid, language, disallow, 
							allow, context, dtmfmode, insecure, nat, qualify, type, host, calllimit';
		$list_friend = Iax::model()->findAll( array(
				'select'=>$select
			) );

		$buddyfile = '/etc/asterisk/iax_magnus_user.conf';


		if ( is_array( $list_friend ) ) {

			$fd = fopen( $buddyfile, "w" );

			if ( $fd ) {
				foreach ( $list_friend as $key=>$data ) {
					$line = "\n\n[".$data['name']."]\n";
					if ( fwrite( $fd, $line ) === FALSE ) {
						echo "Impossible to write to the file ($buddyfile)";
						break;
					}
					else {
						$line = '';

						$host = explode( ':', $data['host'] );
						$line.= 'host='.$host[0]."\n";

						if ( isset( $host[1] ) )
							$line.= 'port='.$host[1]."\n";

						$line.= 'fromdomain='.$host[0]."\n";
						$line.= 'accountcode='.$data['accountcode']."\n";
						$line.= 'disallow='.$data['disallow']."\n";

						$codecs = explode( ",", $data['allow'] );
						foreach ( $codecs as $codec )
							$line .= 'allow=' . $codec . "\n";

						if ( strlen( $data['context'] ) > 1 )$line        .= 'context='.$data['context']."\n";
						if ( strlen( $data['dtmfmode'] ) > 1 )$line       .= 'dtmfmode='.$data['dtmfmode']."\n";
						if ( strlen( $data['insecure'] ) > 1 )$line       .= 'insecure='.$data['insecure']."\n";
						if ( strlen( $data['nat'] ) > 1 )$line            .= 'nat='.$data['nat']."\n";
						if ( strlen( $data['qualify'] ) > 1 )$line        .= 'qualify='.$data['qualify']."\n";
						if ( strlen( $data['type'] ) > 1 )$line           .= 'type='.$data['type']."\n";
						if ( strlen( $data['regexten'] ) > 1 )$line       .= 'regexten='.$data['regexten']."\n";
						if ( strlen( $data['amaflags'] ) > 1 )$line       .= 'amaflags='.$data['amaflags']."\n";
						if ( strlen( $data['language'] ) > 1 )$line       .= 'language='.$data['language']."\n";
						if ( strlen( $data['username'] ) > 1 )$line       .= 'username='.$data['username']."\n";
						if ( strlen( $data['fromuser'] ) > 1 )$line       .= 'fromuser='.$data['fromuser']."\n";
						if ( strlen( $data['callerid'] ) > 1 )$line       .= 'callerid='.$data['callerid']."\n";
						if ( strlen( $data['secret'] ) > 1 )$line       .= 'secret='.$data['secret']."\n";

						if ( $data['calllimit'] > 0 )
							$line.= 'call-limit='.$data['calllimit']."\n";

						if ( fwrite( $fd, $line ) === FALSE ) {
							echo gettext( "Impossible to write to the file" )." ($buddyfile)";
							break;
						}
					}
				}

				fclose( $fd );
			}
		}

		$asmanager = new AGI_AsteriskManager;
		$conectaServidor = $conectaServidor = $asmanager->connect( 'localhost', 'magnus', 'magnussolution' );
		$server = $asmanager->Command( "iax2 reload" );

	}
}
