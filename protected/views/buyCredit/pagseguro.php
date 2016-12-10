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
$precioReal = ($_GET['amount'])."00";


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

<form method="POST" action="https://pagseguro.uol.com.br/security/webpagamentos/webpagto.aspx" target="_parent" id="buyForm">
  <input type="hidden" name="email_cobranca" value="<?php echo $methodPay->username?>"  />
  <input type="hidden" name="ref_transacao" value="<?php echo $Referencia;?>"  />
  <input type="hidden" name="tipo" value="CP"  />
  <input type="hidden" name="moeda" value="BRL"  />
  <input type="hidden" name="cliente_nome" value="<?php echo $firstname .' '. $lastname?>"  />
  <input type="hidden" name="cliente_cep" value="<?php echo $zipcode;?>"  />
  <input type="hidden" name="cliente_end" value="<?php echo $address;?>"  />
  <input type="hidden" name="cliente_num" value="<?php echo $_SESSION['id_user']?>"  />
  <input type="hidden" name="cliente_compl" value=""  />
  <input type="hidden" name="cliente_bairro" value="centro"  />
  <input type="hidden" name="cliente_cidade" value="<?php echo $city;?>"  />
  <input type="hidden" name="cliente_uf" value="<?php echo $state;?>"  />
  <input type="hidden" name="cliente_pais" value="<?php echo $country;?>"  />
  <input type="hidden" name="cliente_ddd" value="11"  />
  <input type="hidden" name="cliente_tel" value="40040435"  />
  <input type="hidden" name="cliente_email" value="<?php echo $email;?>"  />
  <input type="hidden" name="item_id_1" value="<?php echo $Referencia;?>"  />
  <input type="hidden" name="item_descr_1" value="Credito voip"  />
  <input type="hidden" name="item_quant_1" value="1"  />
  <input type="hidden" name="item_valor_1" value="<?php echo round($precioReal ,2);?>"  />
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