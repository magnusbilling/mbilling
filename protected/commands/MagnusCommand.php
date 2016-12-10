<?php
/**
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
 *
 */
class MagnusCommand extends CConsoleCommand
{
	var $config;
	var $directory = '/var/www/html/mbilling/protected/commands/';

	public function run( $args ) {
		define( 'LOGFILE', 'protected/runtime/magnus.log' );
		define( 'DEBUG', 0 );

		if ( function_exists( 'pcntl_signal' ) ) {
			pcntl_signal( SIGHUP, SIG_IGN );
		}

		error_reporting( E_ALL ^ ( E_NOTICE | E_WARNING ) );

		require_once $this->directory.'AGI.Class.php';
		require_once $this->directory.'AGI_AsteriskManager.Class.php';		
		require_once $this->directory.'AGI_Authenticate.Class.php';
		require_once $this->directory.'AGI_Calc.Class.php';
		require_once $this->directory.'AGI_Callback.Class.php';
		require_once $this->directory.'AGI_Magnus.Class.php';
		require_once $this->directory.'AGI_Portabilidade.Class.php';
		require_once $this->directory.'AGI_Queue.Class.php';
		require_once $this->directory.'AGI_MassiveCall.Class.php';
		require_once $this->directory.'AGI_Ivr.Class.php';
		require_once $this->directory.'AGI_SearchTariff.Class.php';

		$agi = new AGI();		

		$agi->verbose( "Start MBilling AGI" );
		$MAGNUS = new Magnus();
		$MAGNUS->load_conf( $agi, null, 0, 1 );
		$MAGNUS->get_agi_request_parameter( $agi );

		if ( $MAGNUS->dnid == 'failed' ) {
			$agi->verbose( "Hangup becouse dnid is OutgoingSpoolFailed", 25 );
			$MAGNUS->hangup( $agi );
			exit;
		}


		if ( substr( $MAGNUS->dnid, 0, 4 ) == 1111 ) {
			if ( $MAGNUS->play_audio == 0 ) {
				$agi->execute( ( congestion ), Congestion );
				$MAGNUS->hangup( $agi );
			}

			$agi->stream_file( 'prepaid-final', '#' );
			$MAGNUS->hangup( $agi );
		}

		/*check if did call*/
		$mydnid = substr( $MAGNUS->dnid, 0, 1 ) == '0' ? substr( $MAGNUS->dnid, -10 ) : $MAGNUS->dnid;

		$sql = "SELECT pkg_did.id_user AS id_user, pkg_did.id, pkg_did_destination.id AS id_destination, billingtype, id_plan, destination,  voip_call, username, connection_charge, pkg_did.selling_rate_1, pkg_did.selling_rate_2, pkg_did.expression_1, pkg_did.expression_2, did, connection_sell, id_ivr, id_queue, id_sip ".
			", minimal_time_charge, initblock, increment, block_expression_1, block_expression_2, block_expression_3, expression_3, selling_rate_3 ".
			"FROM pkg_did_destination ".
			"INNER JOIN pkg_did ON pkg_did_destination.id_did = pkg_did.id ".
			"INNER JOIN pkg_user ON pkg_did_destination.id_user = pkg_user.id ".
			"WHERE pkg_did_destination.activated=1 AND pkg_did.activated=1 ".
			"and did LIKE '%$mydnid' ".
			"ORDER BY priority ASC ";
		$result_did = Yii::app()->db->createCommand( $sql )->queryAll();
		$agi->verbose( $sql, 25 );
		$mode = 'standard';
		$Calc = new Calc();

		$sql = "SELECT * FROM pkg_sip WHERE name = '$mydnid'";
		$resultIsSip = Yii::app()->db->createCommand( $sql )->queryAll();

		if ( count( $result_did ) > 0 && count( $resultIsSip ) < 1) {

			if ($result_did[0]['block_expression_1'] == 1) {
				$agi->verbose("try blocked number match with expression 1, ". $MAGNUS->CallerID. ' '.$result_did[0]['expression_2'],1); 					
				if ( strlen($result_did[0]['expression_1']) > 1 && ereg($result_did[0]['expression_1'], $MAGNUS->CallerID) ) {
            			$agi->verbose("Call blocked becouse this number becouse match with expression 1, ". $MAGNUS->CallerID. ' FROM did '.$result_did[0]['did'],1);     
        				$MAGNUS->hangup( $agi );
	        		}
	        	}    	

	        	if ($result_did[0]['block_expression_2'] == 1) {
	        		$agi->verbose("try blocked number match with expression 2, ". $MAGNUS->CallerID. ' '.$result_did[0]['expression_2'],1); 
					if ( strlen($result_did[0]['expression_2']) > 1 && ereg($result_did[0]['expression_2'], $MAGNUS->CallerID)) {
		            		$agi->verbose("Call blocked becouse this number becouse match with expression 2, ". $MAGNUS->CallerID. ' FROM did '.$result_did[0]['did'],1); 
		            		$MAGNUS->hangup( $agi );
		        		}
	        	}

	        	if ($result_did[0]['block_expression_3'] == 1) {        		
	        		$agi->verbose("try blocked number match with expression 3, ". $MAGNUS->CallerID. ' '.$result_did[0]['expression_3'],1); 
				if (  	strlen($result_did[0]['expression_3']) > 0 && (ereg($result_did[0]['expression_3'], $MAGNUS->CallerID) || $result_did[0]['expression_3'] == '*') &&  
						strlen($result_did[0]['expression_1']) > 1 && !ereg($result_did[0]['expression_1'], $MAGNUS->CallerID) &&
						strlen($result_did[0]['expression_2']) > 1 && !ereg($result_did[0]['expression_2'], $MAGNUS->CallerID)
					) {
	            		$agi->verbose("Call blocked becouse this number becouse match with expression 3, ". $MAGNUS->CallerID. ' FROM did '.$result_did[0]['did'],1); 
	            		$MAGNUS->hangup( $agi );
	        		}
        		}


			switch ( $result_did[0]['voip_call'] ) {
			case 2:
				$mode = 'ivr';
				break;
			case 3:
				//callingcard
				$mode = 'standard';
				$agi->answer();
				sleep( 2 );
				$MAGNUS->callingcardConnection = $result_did[0]['connection_sell'];
				$MAGNUS->agiconfig['answer']        = 1;
				$MAGNUS->agiconfig['cid_enable']    = 1;
				$MAGNUS->agiconfig['use_dnid']      = 0;
				$MAGNUS->agiconfig['number_try']    = 3;
				$MAGNUS->CallerID = is_numeric( $MAGNUS->CallerID ) ? $MAGNUS->CallerID :  $agi->request['agi_calleridname'];
				$agi->verbose( 'CallerID ' .$MAGNUS->CallerID );
				break;
			case 4:
				$mode = 'portalDeVoz';
				break;
			case 5:
				$agi->verbose( 'RECEIVED ANY CALLBACK', 5 );
				AGI_Callback::callbackCID( $agi, $MAGNUS, $Calc, $result_did );
				break;
			case 6:
				if ( !$agi->get_variable( "SECCALL", true ) ){
					$agi->verbose( 'RECEIVED 0800 CALLBACK', 5 );
					AGI_Callback::callback0800( $agi, $MAGNUS, $Calc, $result_did );
				}
				break;
			case 7:
				$mode = 'queue';
				break;
			case 8:
				$mode = 'callgroup';
				break;
			default:
				$mode = 'did';
				break;
			}

		}

		if ( $agi->get_variable( "CIDCALLBACK", true ) ) {

			AGI_Callback::chargeFistCall( $agi, $MAGNUS, $Calc, 0 );

			$MAGNUS->agiconfig['answer']    = 1;
			$MAGNUS->agiconfig['cid_enable']= 1;
			$MAGNUS->agiconfig['use_dnid']  = 0;
			$MAGNUS->agiconfig['number_try']= 3;
		}

		if ( substr( $MAGNUS->dnid , 0, 3 ) == '000' ) {
			$agi->verbose( 'RECEIVED ANY CALLBACK BY SOFTPHONE', 5 );
			AGI_Callback::callbackCID( $agi, $MAGNUS, $Calc, $MAGNUS->dnid );
		}


		if ( $agi->get_variable( "PHONENUMBER_ID", true ) > 0 && $agi->get_variable( "CAMPAIGN_ID", true ) > 0 )
			$mode = 'massive-call';



		$MAGNUS->mode = $mode;


		if ( $agi->get_variable( "SPY", true ) == 1 ) {

			$channel = $agi->get_variable( "CHANNELSPY", true );

			$agi->verbose( 'SPY CALL '.$channel );

			$agi->execute( "ChanSpy", $channel, "bqE" );
			$agi->stream_file( 'prepaid-final', '#' );
			$MAGNUS->hangup( $agi );
			exit;
		}


		if ( $agi->get_variable( "MEMBERNAME", true ) || $agi->get_variable( "QUEUEPOSITION", true ) ) {
			$agi->answer();
			$Calc->init();
			$MAGNUS->init();
			AGI_Queue::recIvrQueue( $agi, $MAGNUS, $Calc, $result_did );
		}

		$agi->verbose( "Type Call ". $mode, 10 );

		if ( $mode == 'standard' ) {
			if ( $MAGNUS->agiconfig['answer_call'] == 1 ) {
				$agi->answer();
				$status_channel = 6;
			}
			else {
				$status_channel = 4;
			}

			/* Play intro message */
			if ( strlen( $MAGNUS->agiconfig['intro_prompt'] ) > 0 ) {
				$agi->stream_file( $MAGNUS->agiconfig['intro_prompt'], '#' );
			}

			//get if the call have the second number
			if ( $agi->get_variable( "SECCALL", true ) ) {

				$agi->stream_file( 'prepaid-secondCall', '#' );
				$MAGNUS->agiconfig['use_dnid']  = 1;
				$MAGNUS->destination  = $MAGNUS->extension = $MAGNUS->dnid = $agi->get_variable( "SECCALL", true );
				$sql = "SELECT username FROM pkg_user WHERE id = ".$agi->get_variable( "IDUSER", true );
				$result_accountcode = Yii::app()->db->createCommand( $sql )->queryAll();
				$MAGNUS->accountcode = isset( $result_accountcode[0]['username'] ) ? $result_accountcode[0]['username'] : NULL;
			}

			$cia_res = AGI_Authenticate::authenticateUser( $agi, $MAGNUS );

			/* CALL AUTHENTICATE AND WE HAVE ENOUGH CREDIT TO GO AHEAD */
			if ( $cia_res == 0 ) {

				for ( $i = 0; $i < $MAGNUS->agiconfig['number_try']; $i++ ) {
					$Calc->init();
					$MAGNUS->init();

					$stat_channel = $agi->channel_status( $MAGNUS->channel );

					/* CHECK IF THE CHANNEL IS UP*/
					if ( ( $MAGNUS->agiconfig['answer_call'] == 1 ) && ( $stat_channel["result"] != $status_channel ) )
						$MAGNUS->hangup( $agi );

					/* CREATE A DIFFERENT UNIQUEID FOR EACH TRY*/
					if ( $i > 0 ) {
						$MAGNUS->uniqueid = $MAGNUS->uniqueid + 1000000000;
					}

					$MAGNUS->extension = $MAGNUS->dnid;


					if ( $MAGNUS->agiconfig['use_dnid'] == 1 && strlen( $MAGNUS->dnid ) > 2 && $i == 0 ) {
						$MAGNUS->destination = $MAGNUS->dnid;
					}



					$sql = "SELECT name, pkg_user.username FROM pkg_sip INNER JOIN pkg_user ON pkg_sip.id_user=pkg_user.id WHERE name='$MAGNUS->dnid'";
					$result = Yii::app()->db->createCommand( $sql )->queryAll();
					$agi->verbose( $sql, 25 );

					$interno = isset( $result[0]['name'] ) ? $result[0]['name'] : 0;

					if ( strlen( $interno ) > 3 ) {
						$agi->verbose( "CALL TO SIP", 15 );
						$cia_res = $MAGNUS->call_sip( $agi, $Calc, $i, $MAGNUS->dnid, $call_transfer_pstn );
					}
					else {
						$ans = $MAGNUS->checkNumber( $agi, $Calc, $i, true );
						$agi->verbose( "teste", 15 );
						if ( $ans == 1 ) {
							/* PERFORM THE CALL*/
							$result_callperf = $Calc->sendCall( $agi, $MAGNUS->destination, $MAGNUS );

							if ( !$result_callperf ) {
								if ( $MAGNUS->play_audio == 0 ) {
									$agi->execute( ( congestion ), Congestion );

								}
								else {
									$prompt = "prepaid-dest-unreachable";
									$agi->stream_file( $prompt, '#' );
								}
							}
							/* INSERT CDR  & UPDATE SYSTEM*/
							$Calc->updateSystem( $MAGNUS, $agi, $MAGNUS->destination );

							if ( $MAGNUS->agiconfig['say_balance_after_call'] == 1 ) {
								$MAGNUS->sayBalance( $agi, $MAGNUS->credit );
							}
						}
					}
					$MAGNUS->agiconfig['use_dnid'] = 0;
				} /*END FOR*/
			}
			else {
				$agi->verbose( "Authentication ERROR  (cia_res:" . $cia_res . ")", 3 );
			}
		}
		elseif ( $mode == 'did' || $mode == 'callgroup' ) {

			if ( $MAGNUS->agiconfig['answer_call'] == 1 ) {
				$agi->verbose( "ANSWER CALL", 6 );
				$agi->answer();
			}


			$Calc->init();
			$MAGNUS->init();

			if ( strlen( $mydnid ) > 0 ) {
				$agi->verbose( "DID CALL - CallerID=" . $MAGNUS->CallerID . " -> DID=" . $mydnid, 6 );

				if ( is_array( $result_did ) ) {
					$MAGNUS->call_did( $agi, $Calc, $result_did );
				}
			}

			/* MOVE VOUCHER TO LET CUSTOMER ONLY REFILL*/
		}
		elseif ( $mode == 'ivr' ) {
			$agi->answer();

			$Calc->init();
			$MAGNUS->init();
			if ( strlen( $mydnid ) > 0 ) {
				$agi->verbose( "DID IVR - CallerID=" . $MAGNUS->CallerID . " -> DID=" . $mydnid, 6 );
				//$MAGNUS->CallIvr($agi, $Calc, $result_did);
				AGI_Ivr::callIvr( $agi, $MAGNUS, $Calc, $result_did );

			}
		}
		elseif ( $mode == 'queue' ) {
			$agi->answer();

			$Calc->init();
			$MAGNUS->init();
			if ( strlen( $mydnid ) > 0 ) {
				$agi->verbose( "DID QUEUE - CallerID=" . $MAGNUS->CallerID . " -> DID=" . $mydnid, 6 );
				//$MAGNUS->CallIvr($agi, $Calc, $result_did);
				AGI_Queue::callQueue( $agi, $MAGNUS, $Calc, $result_did );

			}
		}
		elseif ( $mode == 'massive-call' ) {
			AGI_MassiveCall::send( $agi, $MAGNUS, $Calc );
		}
		elseif ( $mode == 'portalDeVoz' ) {
			$agi->answer();
			$res_dtmf     = $agi->get_data( 'prepaid-enter-dest', 5000, 10 );
			$MAGNUS->dnid = $res_dtmf["result"];

			//verifica se o cliente tem um reenvio para um fixo ou celular
			$sql = "SELECT pkg_did.id, pkg_did_destination.id, destination, voip_call,did ".
				"FROM pkg_did_destination ".
				"INNER JOIN pkg_did ON pkg_did_destination.id_did = pkg_did.id ".
				"WHERE pkg_did_destination.activated=1 AND pkg_did.activated=1 ".
				"and did LIKE '$MAGNUS->dnid' AND voip_call = 0 ".
				"ORDER BY priority ASC ";
			$result_did = Yii::app()->db->createCommand( $sql )->queryAll();
			$agi->verbose( $sql, 25 );

			if ( count( $result_did ) > 0 ) {
				$MAGNUS->accountcode = $MAGNUS->extension = $MAGNUS->destination = $MAGNUS->dnid;
				//transformar em uma chamada $mode == 'standard' para reenviar a um fixo ou celular
				if ( AGI_Authenticate::authenticateUser( $agi, $MAGNUS ) == 0 ) {
					$Calc->init();
					$MAGNUS->init();

					/* CHECK IF THE CHANNEL IS UP*/
					if ( ( $MAGNUS->agiconfig['answer_call'] == 1 ) && ( $stat_channel["result"] != $status_channel ) )
						$MAGNUS->hangup( $agi );

					$MAGNUS->extension = $MAGNUS->destination = $MAGNUS->dnid = $result_did[0]['destination'];

					if ( $MAGNUS->checkNumber( $agi, $Calc, 0, true ) == 1 ) {
						if ( !$Calc->sendCall( $agi, $MAGNUS->destination, $MAGNUS ) )
							$agi->stream_file( "prepaid-dest-unreachable", '#' );

						$Calc->updateSystem( $MAGNUS, $agi, $MAGNUS->destination );
					}
				}
			}
			else {
				$sql        = "SELECT name, pkg_user.username FROM pkg_sip INNER JOIN pkg_user ON pkg_sip.id_user=pkg_user.id WHERE name='$MAGNUS->dnid'";
				$result       = Yii::app()->db->createCommand( $sql )->queryAll();
				$agi->verbose( $sql, 25 );


				if ( count( $result ) > 0 ) {
					$agi->verbose( 'Call to user '.$result[0]['name'], 15 );
					$MAGNUS->extension = $MAGNUS->destination = $MAGNUS->dnid = $result[0]['name'];
					$cia_res = $MAGNUS->call_sip( $agi, $Calc, 0, $MAGNUS->dnid, $call_transfer_pstn );
				}

				else {
					$agi->verbose( 'User no found', 15 );
					$agi->stream_file( 'prepaid-invalid-digits', '#' );
				}

			}
		}




		$siptransfer = $agi->get_variable( "SIPTRANSFER" );
		if ( $siptransfer['data'] == 'yes' ) { // transferencia
			$agi->verbose( "SIPTRANSFER", 15 );

			$MAGNUS->agiconfig['cid_enable'] = 0;
			$MAGNUS->agiconfig['say_timetocall'] = 0;
			if ( strlen( $MAGNUS->CallerID ) < 6 ) {
				$sql = "SELECT calledstation FROM pkg_cdr WHERE sessionid = '$MAGNUS->channel' AND uniqueid = '$MAGNUS->uniqueid' AND starttime > '" . date( "Y-m-d" ) . "' LIMIT 1";
				$result = Yii::app()->db->createCommand( $sql )->queryAll();
				$agi->verbose( $sql, 25 );
				$MAGNUS->CallerID = $result[0][0];

			}
			$MAGNUS->CallerID = $MAGNUS->transform_number_ar_br( $agi, $MAGNUS->CallerID );
			$MAGNUS->dnid = $MAGNUS->destination = $MAGNUS->CallerID;

			$cia_res = AGI_Authenticate::authenticateUser( $agi, $MAGNUS );

			if ( $cia_res == 0 ) {
				$MAGNUS->agiconfig['use_dnid'] = 1;

				$resfindrate = AGI_SearchTariff::find( $MAGNUS, $agi, $Calc );
				$agi->verbose( print_r( $Calc->tariffObj, true ), 10 );
				$Calc->usedratecard = 0;
				/* IF FIND RATE*/
				if ( $resfindrate != 0 ) {
					$agi->verbose( "CREDIT $MAGNUS->credit", 15 );
					$res_all_calcultimeout = $Calc->calculateAllTimeout( $MAGNUS, $MAGNUS->credit );
					$agi->verbose( print_r( $res_all_calcultimeout, true ), 10 );

					if ( $res_all_calcultimeout ) {
						$dialtime = $agi->get_variable( "DIALEDTIME" );
						$dialtime = $dialtime['data'];

						$answeredtime = $agi->get_variable( "ANSWEREDTIME" );
						$answeredtime = $answeredtime['data'];

						$Calc->answeredtime = $dialtime + $answeredtime + 30;
						$Calc->dialstatus = 'ANSWERED';
						$Calc->usedtrunk = $Calc->tariffObj[0]['rc_id_trunk'];
						$agi->verbose( "Calc -> answeredtime=" . $Calc->answeredtime . " $Calc->usedtrunk", 15 );

						$Calc->updateSystem( $MAGNUS, $agi, $MAGNUS->destination, 1, 0, 2 );

					}
					else {
						$msj = "TRANSFERS 1ST LEG -> ERROR - BILLING FOR THE 1ST LEG - calculateAllTimeout: CALLED=$called_party";
						$agi->verbose( $msj, 15 );
					}
				}
				else {
					$msj = "TRANSFERS 1ST LEG -> BILLING FOR THE 1ST LEG - findTariff: CALLED=$called_party - Calc->usedratecard=".$Calc->usedratecard."]";
					$agi->verbose( $msj, 15 );
				}
			}
			else {
				$msj = "[TRANSFERS 1ST LEG -> ERROR - AUTHENTICATION USERNAME]";
				$agi->verbose( $msj, 15 );
			}
		}
		Yii::log( "End AGI script ", 'error' );
		$MAGNUS->hangup( $agi );		

	}
}
