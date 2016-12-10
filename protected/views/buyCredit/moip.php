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


/*
$url = "http://finance.yahoo.com/d/quotes.csv?s=BRLUSD=X&f=l1";
$handle = @fopen( $url, 'r' );
if ( $handle ) {
	$result = fgets( $handle, 4096 );
	fclose( $handle );
}
$cambio = trim($result);
*/
$precioReal = ($_GET['amount']);


$Referencia= date('YmdHis');
$Referencia= $Referencia.'-'.$_SESSION["username"];

$firstname = isset($card->firstname) ? $card->firstname : '';
$lastname  = isset($card->lastname) ? $card->lastname : '';
$address   = isset($card->address) ? $card->address : '';

$city      = isset($card->city) ? $card->city : '';
$state     = isset($card->state) ? $card->state : '';
$zipcode   = isset($card->zipcode) ? $card->zipcode : '';
$phone     = isset($card->phone) ? $card->phone : '';
$email     = isset($card->email) ? $card->email : '';
$country  = isset($card->country) ? $card->country : '';
?>


<form method="POST" action="<?php echo $methodPay->url ?>" target="_parent" id="buyForm">
<input type='hidden' name='id_carteira' value='<?php echo $methodPay->username?>'/>
<input type='hidden' name='valor' value='<?php echo round($precioReal);?>00'/>
<input type='hidden' name='nome' value='Credito VoIP'/>
<input type='hidden' name='id_transacao' value='<?php echo $Referencia;?>'/>
<input id="pagador_nome" type="hidden" name="pagador_nome" value="<?php echo $firstname .' '. $lastname?>"/>
<input id="pagador_email" type="hidden" name="pagador_email" value="<?php echo $email;?>"/>
<input id="pagador_telefone" type="hidden" name="pagador_telefone" value="<?php echo $phone;?>"/>
<input id="pagador_logradouro" type="hidden" name="pagador_logradouro" value="<?php echo $address;?>"/>
<input id="pagador_numero" type="hidden" name="pagador_numero" value="10"/>
<input id="pagador_bairro" type="hidden" name="pagador_bairro" value="Centro"/>
<input id="pagador_cep" type="hidden" name="pagador_cep" value="<?php echo $zipcode;?>"/>
<input id="pagador_cidade" type="hidden" name="pagador_cidade" value="<?php echo $city;?>"/>
<input id="pagador_estado" type="hidden" name="pagador_estado" value="<?php echo $state;?>"/>
<input id="pagador_pais" type="hidden" name="pagador_pais" value="Brasil"/>
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
