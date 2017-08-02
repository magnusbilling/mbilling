<?php
/**
 * Modelo para a tabela "Sip".
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

class Sip extends Model
{
	protected $_module = 'sip';
	private $lineStatus;
	/**
	 * Retorna a classe estatica da model.
	 *
	 * @return Sip classe estatica da model.
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
		return 'pkg_sip';
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
			array( 'id_user, calllimit, ringfalse, record_call', 'numerical', 'integerOnly'=>true ),
			array( 'name, callerid, context, fromuser, fromdomain, md5secret, secret, fullcontact', 'length', 'max'=>80 ),
			array( 'regexten, insecure, regserver, vmexten, callingpres, mohsuggest, allowtransfer', 'length', 'max'=>20 ),
			array( 'amaflags, dtmfmode, qualify', 'length', 'max'=>7 ),
			array( 'callgroup, pickupgroup, auth, subscribemwi, usereqphone, autoframing', 'length', 'max'=>10 ),
			array( 'DEFAULTip, ipaddr, maxcallbitrate, rtpkeepalive', 'length', 'max'=>15 ),
			array( 'nat, host', 'length', 'max'=>31 ),
			array( 'language', 'length', 'max'=>2 ),
			array( 'mailbox', 'length', 'max'=>50 ),
			array( 'accountcode, group', 'length', 'max'=>30 ),
			array( 'rtptimeout, rtpholdtimeout', 'length', 'max'=>3 ),
			array( 'deny, permit', 'length', 'max'=>95 ),
			array( 'port', 'length', 'max'=>5 ),
			array( 'type', 'length', 'max'=>6 ),
			array( 'disallow, allow, setvar, useragent', 'length', 'max'=>100 ),
			array( 'lastms, directmedia', 'length', 'max'=>11 ),
			array( 'defaultuser, cid_number, outboundproxy, sippasswd', 'length', 'max'=>40 ),
			array( 'defaultuser', 'checkusername' ),
			array( 'secret', 'checksecret' ),
			array( 'defaultuser', 'unique', 'caseSensitive' => 'false' ),
		);
	}

	public function checkusername( $attribute, $params ) {
		if ( preg_match( '/ /', $this->defaultuser ) )
			$this->addError( $attribute, Yii::t( 'yii', 'No space allow in defaultuser' ) );
	}
	public function checksecret( $attribute, $params ) {
		if ( preg_match( '/ /', $this->secret ) )
			$this->addError( $attribute, Yii::t( 'yii', 'No space allow in password' ) );
		if (  $this->secret == '123456' ||  $this->secret == '12345678' ||  $this->secret == '012345')
			$this->addError( $attribute, Yii::t( 'yii', 'No use sequence in the pasword' ) );
		if (  $this->secret == $this->defaultuser)
			$this->addError( $attribute, Yii::t( 'yii', 'Password cannot be equal username' ) );
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

		

		$sql = "SELECT username, sipaccountlimit FROM pkg_user WHERE id = ".$this->id_user;
		$CardResult = Yii::app()->db->createCommand( $sql )->queryAll();


		$sql = "SELECT count(*) as total FROM pkg_sip WHERE id_user = ". $this->id_user;
		$countSipResult = Yii::app()->db->createCommand( $sql )->queryAll();

		if ($this->getIsNewRecord() && !Yii::app()->session['isAdmin'] && $CardResult[0]['sipaccountlimit'] > 0 && $countSipResult[0]['total'] >= $CardResult[0]['sipaccountlimit']) {
			echo json_encode(array(
				'success' => false,
				'rows' => array(),
				'errors' => 'Limit sip acount exceeded'
			));
			exit;
		}

		$this->accountcode = $CardResult[0]['username'];
		$this->name = $this->defaultuser == '' ?  $this->accountcode : $this->defaultuser;

		if ( $this->getIsNewRecord() ) {
			$this->regseconds = 1;
			$this->context = 'billing';
			$this->regexten = $this->name;
			if ( !$this->callerid )
				$this->callerid = $this->name;

		}
		$this->cid_number = $this->callerid;

		$this->allow = preg_replace( "/,0/", "", $this->allow );
		$this->allow = preg_replace( "/0,/", "", $this->allow );
		return parent::beforeSave();
	}

	public function afterSave() {

		$this->generateFileText();
		if (isset($_POST['rows'])) {

			$values = json_decode( $_POST['rows'] );

			$sql = "SELECT * FROM pkg_servers WHERE type = 'sipproxy' AND status = 1";
			$resultOpensips = Yii::app()->db->createCommand( $sql )->queryAll();

			foreach ( $resultOpensips as $key => $server ) {

				$hostname = $server['host'];
				$dbname = 'opensips';
				$table = 'subscriber';
				$user = $server['username'];
				$password = $server['password'];
				$port = $server['port'];

				$dsn='mysql:host='.$hostname.';dbname='.$dbname;

				$con=new CDbConnection( $dsn, $user, $password );
				$con->active=true;
				if ( $this->getIsNewRecord() ) {
					$sql = "INSERT INTO $dbname.$table (username,domain,ha1,accountcode) VALUES 
							('$this->defaultuser','$hostname','".md5($this->defaultuser.':'.$hostname.':'.$this->secret )."','$this->accountcode')";
					$con->createCommand( $sql )->execute();
				}else {
					$sql = "UPDATE $dbname.$table SET ha1 = '".md5($this->defaultuser.':'.$hostname.':'.$this->secret )."', 
							username = '$this->defaultuser' WHERE username = '$this->defaultuser'";
					$con->createCommand( $sql )->execute();
				}
			}
		}

		return parent::afterSave();
	}

	public function afterDelete() {
		$this->generateFileText();

	}

	public function generateFileText() {

		if (  preg_match( "/magnusbilling.com|localhost/", $_SERVER['HTTP_HOST'] ) ) {
			return;
		}

		$select = 'id, accountcode, name, defaultuser, secret, regexten, amaflags, callerid, language, cid_number, disallow, allow, directmedia, context, dtmfmode, insecure, nat, qualify, type, host, calllimit '; // add

		if ($_SERVER['HTTP_HOST'] == 'localhost')
			$filter = '';
		else
			$filter = "host != 'dynamic' OR  calllimit > 0";
		
		$list_friend = Sip::model()->findAll( array(
				'select'=>$select,
				'condition' => $filter
			) );

		$buddyfile = '/etc/asterisk/sip_magnus_user.conf';


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

						if ( strlen( $data['directmedia'] ) > 1 ) $line   .= 'directmedia='.$data['directmedia']."\n";
						if ( strlen( $data['context'] ) > 1 )$line        .= 'context='.$data['context']."\n";
						if ( strlen( $data['dtmfmode'] ) > 1 )$line       .= 'dtmfmode='.$data['dtmfmode']."\n";
						if ( strlen( $data['insecure'] ) > 1 )$line       .= 'insecure='.$data['insecure']."\n";
						if ( strlen( $data['nat'] ) > 1 )$line            .= 'nat='.$data['nat']."\n";
						if ( strlen( $data['qualify'] ) > 1 )$line        .= 'qualify='.$data['qualify']."\n";
						if ( strlen( $data['type'] ) > 1 )$line           .= 'type='.$data['type']."\n";
						if ( strlen( $data['regexten'] ) > 1 )$line       .= 'regexten='.$data['regexten']."\n";
						if ( strlen( $data['amaflags'] ) > 1 )$line       .= 'amaflags='.$data['amaflags']."\n";
						if ( strlen( $data['cid_number'] ) > 1 )$line     .= 'cid_number='.$data['cid_number']."\n";
						if ( strlen( $data['language'] ) > 1 )$line       .= 'language='.$data['language']."\n";
						if ( strlen( $data['defaultuser'] ) > 1 )$line       .= 'defaultuser='.$data['defaultuser']."\n";
						if ( strlen( $data['fromuser'] ) > 1 )$line       .= 'fromuser='.$data['fromuser']."\n";
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


		$list_friend = Sip::model()->findAll( array(
				'select'=>$select
			) );
		$subscriberfile = '/etc/asterisk/sip_magnus_subscriber.conf';
		$subscriber = '[subscribe]';
		if ( is_array( $list_friend ) ) {
			$fsubs = fopen( $subscriberfile, "w" );
			foreach ( $list_friend as $key=>$data ) {
				if ( strlen( $data['defaultuser'] ) > 1 )
					$subscriber .= 'exten => '.$data['defaultuser'].',hint,SIP/'.$data['defaultuser']."\n";
			}
			fwrite( $fsubs, $subscriber );
			fclose( $fsubs );
		}

		$asmanager = new AGI_AsteriskManager;
		$conectaServidor = $conectaServidor = $asmanager->connect( 'localhost', 'magnus', 'magnussolution' );
		$server = $asmanager->Command( "sip reload" );

	}
}
