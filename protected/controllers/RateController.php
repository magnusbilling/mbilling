<?php
/**
 * Acoes do modulo "Rate".
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
 * 30/07/2012
 */

class RateController extends Controller
{
	public $attributeOrder = 't.id';
	public $extraValues    = array(
		'idTrunk' => 'trunkcode',
		'idPlan' => 'name',
		'idPrefix' => 'destination,prefix',
	);

	public $fieldsFkReport = array(
		'id_plan' => array(
			'table' => 'pkg_plan',
			'pk' => 'id',
			'fieldReport' => 'name'
		),
		'id_trunk' => array(
			'table' => 'pkg_trunk',
			'pk' => 'id',
			'fieldReport' => 'trunkcode'
		),
		'id_prefix' => array(
			'table' => 'pkg_prefix',
			'pk' => 'id',
			'fieldReport' => 'prefix'
		),
		'id' => array(
			'table' => 'pkg_prefix',
			'pk' => 'id',
			'fieldReport' => 'destination'
		)
	);

	public $fieldsInvisibleClient = array(
		'additional_grace',
		'id_trunk',
		'idTrunktrunkcode',
		'buyrate',
		'buyrateinitblock',
		'buyrateincrement',
		'connectcharge',
		'disconnectcharge',
		'startdate',
		'stopdate',
		'starttime',
		'endtime',
		'musiconhold',
		'rounding_calltime',
		'rounding_threshold',
		'additional_block_charge',
		'additional_block_charge_time',
		'disconnectcharge_after',
		'minimal_cost',
		'minimal_time_charge',
		'package_offer'
	);

	public $fieldsInvisibleAgent = array(
		'additional_grace',
		'id_trunk',
		'idTrunktrunkcode',
		'buyrate',
		'buyrateinitblock',
		'buyrateincrement',
		'connectcharge',
		'disconnectcharge',
		'startdate',
		'stopdate',
		'starttime',
		'endtime',
		'musiconhold',
		'rounding_calltime',
		'rounding_threshold',
		'additional_block_charge',
		'additional_block_charge_time',
		'disconnectcharge_after',
		'minimal_cost',
		'package_offer'
	);

	public $FilterByUser;

	public function init() {

		$this->instanceModel = new Rate;
		$this->abstractModel = Rate::model();
		$this->titleReport   = Yii::t( 'yii', 'Ratecard' );

		parent::init();
		if ( !Yii::app()->session['isAdmin'] ) {

			$this->FilterByUser = 'pkg_plan.id_user';
			$this->join         = 'JOIN pkg_plan ON pkg_plan.id = id_plan';

			$this->extraValues    = array(
				'idPlan' => 'name',
				'idPrefix' => 'destination,prefix',
			);
		}
	}

	public function extraFilter( $filter ) {
		$filter = isset( $this->filter ) ? $filter.$this->filter : $filter;

		$filter = $filter . ' AND ' .$this->defaultFilter;
		$filter = $this->filterReplace( $filter );

		if ( Yii::app()->getSession()->get( 'isClient' ) ) {
			$filter .= ' AND t.id_plan = '.Yii::app()->getSession()->get( 'id_plan' );
		}
		else if ( Yii::app()->getSession()->get( 'isAgent' ) ) {
				$filter .= ' AND pkg_plan.id_user = '.Yii::app()->getSession()->get( 'id_user' );
			}

		return $filter;
	}



	public function actionReport() {
		$this->replaceToExport();
		parent::actionReport();
	}

	public function actionCsv() {

		$this->replaceToExport();
		parent::actionCsv();
	}

	public function replaceToExport() {

		//altera as colunas para poder pegar o destino das tarifas
		$destino = '{"header":"Prefixo","dataIndex":"idPrefixprefix"},';
		$destinoNew = '{"header":"Prefixo","dataIndex":"id_prefix"},';
		if ( preg_match( "/$destino/", $_GET['columns'] ) ) {
			$_GET['columns'] = preg_replace( "/$destino/", $destinoNew , $_GET['columns'] );
		}

		$destino = '{"header":"Destino","dataIndex":"idPrefixdestination"},';
		$destinoNew = '{"header":"Destino","dataIndex":"id"},';
		if ( preg_match( "/$destino/", $_GET['columns'] ) ) {
			$_GET['columns'] = preg_replace( "/$destino/", $destinoNew , $_GET['columns'] );
		}
	}

	public function actionImportFromCsv() {

		if(!Yii::app()->session['id_user'] || Yii::app()->session['isClient'] == true)
			exit();

		$values = $this->getAttributesRequest();
		$idPlan = $values['id_plan'];
		$idTrunk = $values['id_trunk'];

		ini_set( "memory_limit", "1500M" );
		ini_set( "upload_max_filesize", "3M" );
		ini_set( "max_execution_time", "120" );

		$handle = fopen( $_FILES['file']['tmp_name'], "r" );
		$sqlPrefix = array();

		while ( ( $row = fgetcsv( $handle, 32768, $values['delimiter'] ) ) !== FALSE ) {


			$checkDelimiter = $values['delimiter'] == ',' ? ';' : ',';

			//erro do separador
			if ( preg_match( "/$checkDelimiter/", $row[0] ) ) {
				echo json_encode( array(
						$this->nameSuccess => false,
						'errors' => Yii::t( 'yii', 'ERROR: CSV delimiter, please select ( '.$checkDelimiter .' ) on the import form' )
					) );
				exit;
			}

			if ( isset( $row[1] ) ) {
				if ( !isset( $row[0] ) || $row[0] == '' ) {
					echo json_encode( array(
							$this->nameSuccess => false,
							'msg' => 'Prefix not exit in the CSV file . Line: ' .print_r( $row, true )
						) );
					exit;
				}
				$prefix = $row[0];
				$destination = ( $row[1] == '' ) ? 'ROC' : trim( $row[1] );
				$destination = utf8_encode( $destination );

				$sql = 'SELECT id, destination FROM pkg_prefix WHERE prefix = :prefix LIMIT 1';
				try {
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":prefix", $prefix, PDO::PARAM_STR);
					$resultPrefix = $command->queryAll();
				} catch ( Exception $e ) {
					Yii::log( print_r( $e, true ), 'info' );
					echo json_encode( array(
							$this->nameSuccess => false,
							'msg' => print_r( $e->getMessage(), true )
						) );
					exit;
				}

				if ( count( $resultPrefix ) > 0 ) {
					if ( $resultPrefix[0]['destination'] != $destination ) {
						$sql = "UPDATE pkg_prefix SET destination = :destination  WHERE prefix = :prefix";
						try {
							$command = Yii::app()->db->createCommand($sql);
							$command->bindValue(":prefix", $prefix, PDO::PARAM_STR);
							$command->bindValue(":destination", $destination, PDO::PARAM_STR);
							$command->execute();
						} catch ( Exception $e ) {
							echo json_encode( array(
									$this->nameSuccess => false,
									'msg' => $this->getErrorMySql( $e )
								) );
							exit;
						}

					}
				}
				else {
					$sqlPrefix[] = "($prefix, '$destination')";
				}
			}
		}




		if ( count( $sqlPrefix ) > 0 ) {
			SqlInject::sanitize($sqlPrefix);
			$sqlInsertPrefix = 'INSERT INTO pkg_prefix (prefix, destination) VALUES '.implode( ',', $sqlPrefix ).';';
			try {
				Yii::app()->db->createCommand( $sqlInsertPrefix )->execute();
			} catch ( Exception $e ) {
				echo json_encode( array(
						$this->nameSuccess => false,
						'msg' => $this->getErrorMySql( $e )
					) );
				exit;
			}

		}


		$handle = fopen( $_FILES['file']['tmp_name'], "r" );
		$sqlRate = array();
		while ( ( $row = fgetcsv( $handle, 32768, $values['delimiter'] ) ) !== FALSE ) {
			if ( isset( $row[1] ) ) {
				if ( !isset( $row[0] ) || $row[0] == '' ) {
					echo json_encode( array(
							$this->nameSuccess => false,
							'msg' => 'Prefix not exit in the CSV file . Line: ' .print_r( $row, true )
						) );
					exit;
				}
				$prefix = $row[0];
				$destination = ( $row[1] == '' ) ? 'ROC' : trim( $row[1] );
				$destination = utf8_encode( $destination );
				$price = $row[2] == '' ? '0.0000' : $row[2];
				$buyprice = isset( $row[3] )? $row[3] : $row[2];
				$initblock = isset( $row[4] )? $row[4] : 1;
				$billingblock = isset( $row[5] )? $row[5] : 1;
				$buyrateinitblock = isset( $row[6] )? $row[6] : 1;
				$buyrateincrement = isset( $row[7] )? $row[7] : 1;


				$sql = 'SELECT id, destination FROM pkg_prefix WHERE prefix = :prefix LIMIT 1';
				try {
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":prefix", $prefix, PDO::PARAM_STR);
					$resultPrefix = $command->queryAll();

				} catch ( Exception $e ) {
					echo json_encode( array(
							$this->nameSuccess => false,
							'msg' => print_r( $e, true )
						) );
					exit;
				}

				if ( count( $resultPrefix ) > 0 ) {
					$idPrefix =  $resultPrefix[0]['id'];
				}
				else {
					$sqlInsertPrefix = "INSERT INTO pkg_prefix (prefix, destination) VALUES (:prefix, :destination)";
					try {
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":prefix", $prefix, PDO::PARAM_STR);
						$command->bindValue(":destination", $destination, PDO::PARAM_STR);
						$command->execute();
					} catch ( Exception $e ) {
						echo json_encode( array(
								$this->nameSuccess => false,
								'msg' => $this->getErrorMySql( $e )
							) );
						exit;
					}

					$idPrefix = Yii::app()->db->lastInsertID;
				}
				if ( Yii::app()->session['isAdmin'] )
					$sqlRate[] = "($idPrefix, $idPlan, $price, $buyprice, $idTrunk, $initblock, $billingblock, $buyrateinitblock, $buyrateincrement, 1)";
				else if ( Yii::app()->session['isAgent'] )
					$sqlRate[] = "($idPrefix, $idPlan, $price, $initblock, $billingblock)";
			}
		}


		fclose( $handle );


		if ( count( $sqlRate ) > 0 ) {
			SqlInject::sanitize($sqlRate);
			if ( Yii::app()->session['isAdmin'] )
				$sqlRate = 'INSERT INTO pkg_rate (id_prefix, id_plan, rateinitial, buyrate, id_trunk, initblock, billingblock, buyrateinitblock, buyrateincrement,status) VALUES '.implode( ',', $sqlRate ).';';
			else if ( Yii::app()->session['isAgent'] )
				$sqlRate = 'INSERT INTO pkg_rate_agent (id_prefix, id_plan, rateinitial,  initblock, billingblock) VALUES '.implode( ',', $sqlRate ).';';

			try {
				$this->success = Yii::app()->db->createCommand( $sqlRate )->execute();
			} catch ( Exception $e ) {
				echo json_encode( array(
						$this->nameSuccess => false,
						'msg' => $this->getErrorMySql( $e )
					) );
				exit;
			}

			$this->success = true;
			$this->msg = $this->msgSuccess;
		}else {
			$this->success = false;
			$this->msg = ' Upload Erro';
		}

		echo json_encode( array(
				$this->nameSuccess => $this->success,
				'msg' => $this->msg
			) );
	}
}
