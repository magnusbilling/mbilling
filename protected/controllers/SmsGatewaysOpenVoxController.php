<?php
/**
 * Acoes do modulo "Call".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author  Adilson Leffa Magnus.
 * @copyright   Todos os direitos reservados.
 * ###################################
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 19/09/2012
 */

//menu advanced -> Asterisk API - user admin - pass - admin - deny = 0.0.0.0/0.0.0.0 - Permit = ip/mask
//http://201.175.52.12/mbilling/index.php/smsGatewaysOpenVox/send?text=%text%&host=10.3.3.3&user=admin&pass=admin&channel=4&number=%number%

class SmsGatewaysOpenVoxController extends Controller
{

    public function actionSend() {
        error_reporting( E_ALL );
        ini_set( "display_errors", 1 );
        $agi=new AGI_AsteriskManager;

        $text = $_GET['text'];//YOUR MESSAGE
        $vst_host=$_GET['host']; //YOUR VOXSTACK GSM GATEWAY IP ADDRESS
        $vst_user=$_GET['user']; //Corresponding to your GSM gateway API settings
        $vst_pwd=$_GET['pass']; //Corresponding to your GSM gateway API settings
        $vst_port="5038"; //Corresponding to your GSM gateway API settings

        $span=$_GET['channel']; //YOUR SIMcard for sending sms
        $destination=$_GET['number']; //YOUR DESTINATION NUMBER

        $agi_status=$agi->connect( $vst_host.":".$vst_port, $vst_user, $vst_pwd );
        if ( !$agi_status ) {
            $msg="Failed to connected Asterisk,exit..";
            echo $msg;
            exit();
        }
        $type="gsm";
        $method="send";
        $sync="sms";

        $message=mb_convert_encoding( $text, "utf-8", mb_detect_encoding( $text ) ); //if text in russian
        $timeout="30";
        $id=date( "mdhis" );
        $agi->Command( "$type $method $sync $span $destination \"$message\" $timeout $id" );
        echo 'sussess';
        exit( 0 );
    }

}
