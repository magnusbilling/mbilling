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
?>
<div id="load" ><?php echo Yii::t('yii','Please wait while loading...') ?></div>

<script languaje="JavaScript">
window.onload = function () {
        var form = document.getElementById("buyForm");
        form.submit();
    };

    function OnFormSubmit() {
        alert("Submitting form.");
    }
</script>
<?php
$url = "http://finance.yahoo.com/d/quotes.csv?s=ARSUSD=X&f=l1";
$handle = @fopen( $url, 'r' );
if ( $handle ) {
	$result = fgets( $handle, 4096 );
	fclose( $handle );
}
$cambio = trim($result);
$precioPesos = ($_GET['amount'] * 1.15) / $cambio;

$firstname = isset($card->firstname) ? $card->firstname : '';
$lastname  = isset($card->lastname) ? $card->lastname : '';
$address   = isset($card->address) ? $card->address : '';

$city      = isset($card->city) ? $card->city : '';
$state     = isset($card->state) ? $card->state : '';
$zipcode   = isset($card->zipcode) ? $card->zipcode : '';
$phone     = isset($card->phone) ? $card->phone : '';
$email     = isset($card->email) ? $card->email : '';
?>

<form method="GET" action="<?php echo $methodPay->url ?>" target="_parent" id="buyForm">
<input type="hidden" value="credito voip" name="concepto">
<input name="precio" type="hidden" value="<?php echo $precioPesos;?>">
<input type="hidden" value="<?php echo $methodPay->username?>" name="id">
<input name="hacia" type="hidden" value="<?php echo $email;?>">
<input name="codigo" type="hidden" value="<?php echo $_SESSION["username"]?>">
<input type="hidden" value="7" name="venc">
<input type="button" name="concepto" value="usuario_<?php echo $_SESSION["username"];?>">
</form>

<style type="text/css">
#load{
position:absolute;
z-index:1;
border:3px double #999;
background:#f7f7f7;
width:300px;
height:100px;
margin-top:-150px;
margin-left:-150px;
top:50%;
left:50%;
text-align:center;
line-height:100px;
font-family:"Trebuchet MS", verdana, arial,tahoma;
font-size:12pt;
}
</style>