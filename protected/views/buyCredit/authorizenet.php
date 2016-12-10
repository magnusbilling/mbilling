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
require_once( "lib/anet/AuthorizeNet.php");


define("AUTHORIZENET_TRANSACTION_KEY", $methodPay->pagseguro_TOKEN);
define("AUTHORIZENET_API_LOGIN_ID", $methodPay->username);


if (preg_match("/sandbox/", $methodPay->show_name)) {
    define("AUTHORIZENET_SANDBOX", true);
}


$sale           = new AuthorizeNetAIM;
$sale->amount   = $_GET['amount'];
$sale->card_num = $_GET['cc'];

$sale->exp_date = $_GET['ed']; //'04/15';
$sale->invoice_num = date('YmdHis');


$sale->first_name = isset($card->firstname) ? $card->firstname : '';
$sale->last_name  = isset($card->lastname) ? $card->lastname : '';
$sale->address   = isset($card->address) ? $card->address : '';
$sale->city      = isset($card->city) ? $card->city : '';
$sale->state     = isset($card->state) ? $card->state : '';
$sale->phone     = isset($card->phone) ? $card->phone : '';
$sale->country  = isset($card->country) ? $card->country : '';


$response = $sale->authorizeAndCapture();
if ($response->approved) {
    $transaction_id = $response->transaction_id;
    $description = 'AuthorizaNet, CreditCard ' . $response->card_type .' ID:'. $response->transaction_id;

    $_SERVER['argv'][0] = 'cron'; 
    $model = new Refill;
    $model->id_user = $_SESSION["id_user"];
    $model->credit = $response->amount;
    $model->description = $description;
    $model->payment = 1;
    $model->save();

    echo json_encode(array(
        'success' => true,
        'msg' =>  'Thank You! You have successfully completed the payment!'
    ));
    exit;


}
else{
    echo json_encode(array(
        'success' => false,
        'msg' =>  'Oops, some error occured <br>'. $response->response_reason_text
    ));
    exit;
}
?>
