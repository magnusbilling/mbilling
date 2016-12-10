<?php
/**
 * Acoes do modulo "Sip".
 *
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
 * 23/06/2012
 */

class SipController extends Controller
{
	public $attributeOrder     = 'regseconds DESC';
	public $extraValues        = array( 'idUser' => 'username' );

	public $filterByUser        = true;
	public $defaultFilterByUser = 'b.id_user';
	public $join                = 'JOIN pkg_user b ON t.id_user = b.id';

	public $fieldsFkReport = array(
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		)
	);

	public function init() {
		$this->instanceModel = new Sip;
		$this->abstractModel = Sip::model();
		parent::init();
	}

	public function actionRealtime()
	{
		Yii::log(print_r($_REQUEST,true),'info');

		if (isset($_GET['update']) ) {

			$values = '';
			foreach ($_POST as $key => $value) {
				$values .= $key ."='".$value."',";
			}
			$sql = "UPDATE pkg_sip SET ".substr($values,0,-1)." WHERE name = :name";
			try {
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":name", $_GET['name'], PDO::PARAM_STR);
				$command->execute();
			} catch (Exception $e) {
				
			}
			 
		}elseif (isset($_GET['single']) AND isset($_POST['host'])){
		
			$sql = "SELECT  type, regexten, callerid, dtmfmode, insecure,  context, secret, defaultuser, 
							qualify, disallow, directmedia, cid_number, allowtransfer, host, 
							accountcode, qualify, allow, nat, regseconds, ipaddr, fullcontact, useragent 
							FROM pkg_sip WHERE name = :name AND host = :host";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":name", $_GET['name'], PDO::PARAM_STR);
			$command->bindValue(":host", $_POST['host'], PDO::PARAM_STR);
			$resultSip=  $command->queryAll();
	
			if (count($resultSip) == 0) {
				Yii::log("CONTA SIP NAO ENCONTRDA",'error');
				$sql = "SELECT  type ,  dtmfmode, insecure,  context, secret, user AS defaultuser, 
								user AS fromuser,  qualify, disallow, directmedia, host, qualify, 
								allow, nat FROM pkg_trunk WHERE trunkcode = :trunkcode";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":trunkcode", $_POST['name'], PDO::PARAM_STR);
				$resultSip=  $command->queryAll();

				if (count($resultSip) == 0) {
					$sql = "SELECT  secret, user  FROM pkg_trunk WHERE host = :host";
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":host", $_POST['name'], PDO::PARAM_STR);
					$resultSip=  $command->queryAll();

					if (count($resultSip) == 0) {
						return;
					}else{
						echo "register=>".$resultSip[0]['user'].':'.$resultSip[0]['secret'].'@'.$_POST['name'];
						exit;
					}


				}
			}
			$line = "";
				
			$i=0;
			foreach ($resultSip[0] as $key => $value) {
				//Yii::log(print_r($key,true),'error');
				if (strlen($value) > 0)
					$line .= $key."=".$value."&";

				$i++;
			}
			Yii::log($line,'error');
			echo substr($line,0, -1);
		}

	}

	public function extraFilter( $filter ) {
		$filter = isset( $this->filter ) ? $filter.$this->filter : $filter;

		$filter = $filter . ' AND ' .$this->defaultFilter;
		$filter = $this->filterReplace( $filter );

		if ( Yii::app()->getSession()->get( 'user_type' )  > 1 && $this->filterByUser ) {
			$filter .= ' AND ('. $this->defaultFilterByUser . ' = '.Yii::app()->getSession()->get( 'id_user' );
			$filter .= ' OR t.id_user = '.Yii::app()->getSession()->get( 'id_user' ).')';
		}
		return $filter;
	}

	public function actionDestroy() {

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


			$values = $this->getAttributesRequest();

			foreach ( $values as $key => $id ) {
				$sql = "SELECT name FROM pkg_sip WHERE id = :id";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id", $id, PDO::PARAM_INT);
				$resultSip=  $command->queryAll();


				$sql = "DELETE FROM $dbname.$table WHERE username = :username";
				$con->createCommand( $sql );
				$con->bindParam(":username", $$resultSip[0]['name'], PDO::PARAM_STR);
				$con->execute();
			}

		}


		parent::actionDestroy();
	}

	public function getAttributesModels( $models, $itemsExtras = array() ) {
		$attributes = false;
		$configFile = '/etc/kamailio/kamailio.cfg';

		foreach ( $models as $key => $item ) {
			$attributes[$key] = $item->attributes;
			if ( file_exists( $configFile ) && $item->attributes['secret'] == '' ) {
				$attributes[$key]['secret'] = $item->attributes['sippasswd'];
			}

			if ( isset( $_SESSION['isClient'] ) && $_SESSION['isClient'] ) {
				foreach ( $this->fieldsInvisibleClient as $field ) {
					unset( $attributes[$key][$field] );
				}
			}

			if ( isset( $_SESSION['isAgent'] ) && $_SESSION['isAgent'] ) {
				foreach ( $this->fieldsInvisibleAgent as $field ) {
					unset( $attributes[$key][$field] );
				}
			}

			foreach ( $itemsExtras as $relation => $fields ) {
				$arrFields = explode( ',', $fields );
				foreach ( $arrFields as $field ) {
					$attributes[$key][$relation . $field] = $item->$relation->$field;
					if ( $_SESSION['isClient'] ) {
						foreach ( $this->fieldsInvisibleClient as $field ) {
							unset( $attributes[$key][$field] );
						}
					}

					if ( $_SESSION['isAgent'] ) {
						foreach ( $this->fieldsInvisibleAgent as $field ) {
							unset( $attributes[$key][$field] );
						}
					}
				}
			}
		}
		return $attributes;
	}
}
