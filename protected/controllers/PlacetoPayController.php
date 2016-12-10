<?php

/**
 * Url for paypal ruturn http://ip/billing/index.php/placetoPay .
 */
class PlacetoPayController extends BaseController
{

	public function actionIndex() {

		require_once ('lib/PlacetoPay/classes/EGM/PlacetoPay.php');

		define('GNUPG_PROGRAM_PATH', '/usr/bin/gpg');
		define('GNUPG_HOME_DIRECTORY', '/var/www/PlacetoPay/llaves');

		$status = isset($status) ? $status : 'Sin status';

		if (isset($_POST['PaymentResponse'] )) {

			if (!isset($_POST['CustomerSiteID'])) {
				exit;
			}
			$sql = "SELECT * FROM pkg_method_pay WHERE P2P_CustomerSiteID LIKE :CustomerSiteID";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":CustomerSiteID", $_POST['CustomerSiteID'], PDO::PARAM_STR);
			$methodPay = $command->queryAll();


			// define los datos propios del comercio
			define('P2P_CustomerSiteID', $methodPay[0]['P2P_CustomerSiteID']);
			define('P2P_KeyID', $methodPay[0]['P2P_KeyID']);
			define('P2P_Passphrase', $methodPay[0]['P2P_Passphrase']);
			define('P2P_RecipientKeyID', $methodPay[0]['P2P_RecipientKeyID']);
			
			$CustomerSiteID = (isset($_POST['CustomerSiteID']) ? $_POST['CustomerSiteID']: false);
			$PaymentResponse = (isset($_POST['PaymentResponse']) ? $_POST['PaymentResponse']: false);


			if (($CustomerSiteID == P2P_CustomerSiteID) && (!empty($PaymentResponse)))
			{
				// crea una instancia al objeto para procesar PlacetoPay
				// establece la ruta donde esta el ejecutable del gnuPG y el keyring
				$p2p = new PlacetoPay();
				$p2p->setGPGProgramPath(GNUPG_PROGRAM_PATH);
				$p2p->setGPGHomeDirectory(GNUPG_HOME_DIRECTORY);
				
				// determina el estado de la transaccion
				$rc = $p2p->getPaymentResponse(P2P_KeyID, P2P_Passphrase, $PaymentResponse);
				switch($rc) {
					case PlacetoPay::P2P_ERROR:
					//
						//echo "P2P_ERROR<br>";
						$status = "fallida";
						Yii::log( 'P2P_ERROR', 'error' );
						$id =  $p2p->getReference();
						$descr = 'Recarga PlaceToPay <font color=red>fallida</font>, referencia: '.$id.' ';
						$sql = "UPDATE pkg_refill SET description = :descr WHERE id = :id";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id", $id, PDO::PARAM_INT);
						$command->bindValue(":descr", $descr, PDO::PARAM_STR);
						$command->execute();

						break;
					case PlacetoPay::P2P_DECLINED:
						$status = "rechazada";
						//Yii::log( 'P2P_DECLINED', 'error' );
						Yii::log( $p2p->getErrorCode(), 'error' );
						Yii::log( $p2p->getErrorMessage(), 'error' );

						$id =  $p2p->getReference();
						$descr ='Recarga PlaceToPay <font color=red>rechazada</font>, referencia: '.$id.' ';
						$sql = "UPDATE pkg_refill SET description = :descr WHERE id = :id";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id", $id, PDO::PARAM_INT);
						$command->bindValue(":descr", $descr, PDO::PARAM_STR);
						$command->execute();
						
				
						break;
					
					case PlacetoPay::P2P_APPROVED:
						//EL pago esta perfecto
						Yii::log( 'P2P_APPROVED', 'error' );
						$status = "Aprobada";
						$id =  $p2p->getReference();
						
						$sql = "SELECT * FROM pkg_refill WHERE id = :id";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id", $id, PDO::PARAM_STR);
						$resultRefill = $command->queryAll();

						$sql = "SELECT * FROM pkg_user WHERE id = :id";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id", $resultRefill[0]['id_user'], PDO::PARAM_STR);
						$resultUser = $command->queryAll();

						if ( count( $resultUser ) > 0 ) {						

							$id_user = $resultRefill[0]['id_user'];
							$description = "Recarga PlaceToPay <font color=green>Aprobada</font>. Referencia: $id, Autorizacion/CUS: ". $p2p->getAuthorization(). ', '. $p2p->getFranchiseName();
							$monto = $p2p->getTotalAmount();
							$codigo = 	$p2p->getReceipt();	

							if ( $resultUser[0]['country'] == 57 && $monto > 0) {
			                        	$sql = "INSERT INTO pkg_invoice (id_user) VALUES (:id_user)";
			                        	$command = Yii::app()->db->createCommand($sql);
								$command->bindValue(":id_user", $id_user, PDO::PARAM_INT);
								$command->execute();

			                        	$invoice_number = Yii::app()->db->lastInsertID;
			                        	$sql = "UPDATE pkg_refill SET invoice_number = :invoice_number WHERE id = :id";
			                        	$command = Yii::app()->db->createCommand($sql);
								$command->bindValue(":id", $id, PDO::PARAM_INT);
								$command->bindValue(":invoice_number", $invoice_number, PDO::PARAM_STR);
								$command->execute();
			                    }			
							
							$sql = "UPDATE pkg_refill SET description = :description, payment = 1 WHERE id = :id";
			                   	$command = Yii::app()->db->createCommand($sql);
							$command->bindValue(":id", $id, PDO::PARAM_INT);
							$command->bindValue(":description", $description, PDO::PARAM_STR);
							$command->execute();

			                    $sql = "UPDATE pkg_user SET credit = credit + :monto WHERE id = :id_user";
			                    $command = Yii::app()->db->createCommand($sql);
							$command->bindValue(":id_user", $id_user, PDO::PARAM_INT);
							$command->bindValue(":monto", $monto, PDO::PARAM_STR);
							$command->execute();


			                    $mail = new Mail(Mail::$TYPE_REFILL, $id_user);
			                    $mail->replaceInEmail(Mail::$ITEM_ID_KEY, $id);
			                    $mail->replaceInEmail(Mail::$ITEM_AMOUNT_KEY, $monto);
			                    $mail->replaceInEmail(Mail::$DESCRIPTION, $description);
			                    $mail->send();
	
							$sucess = true;
						}else {
							Yii::log( 'USERNAE NOT FOUND'. $sql, 'info' );
						}
						break;
						

					case PlacetoPay::P2P_DUPLICATE:						
						Yii::log( 'P2P_DUPLICATE', 'error' );
						$status = "Duplicada";

						$id =  $p2p->getReference();
						$descr = 'Recarga PlaceToPay <font color=red>Duplicada</font>, referencia: '.$id.' ';
						$sql = "UPDATE pkg_refill SET description = :descr WHERE id = :id";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id", $id, PDO::PARAM_INT);
						$command->bindValue(":descr", $descr, PDO::PARAM_STR);
						$command->execute();						
					
						break;
					case PlacetoPay::P2P_PENDING:
						
						$status = "Pendiente";
						Yii::log( 'P2P_PENDING', 'error' );
						// la entidad financiera aun no ha dado una respuesta del exito o fracaso de la
						// transaccion
						// - almacene en su base de datos la franquicia, autorizacion, recibo, banco, moneda, factor conversion, valor real
						// - tenga en cuenta que el nombre del comprador o su correo pudieron haber sido cambiados en la plataforma
						// - muestre los datos de la transaccion en proceso
						
						break;
				}
			}


			
		}
		else{
			echo "Gracias, estamos procesando su pago!";
		}

		



		?>	


		<?php header ('Content-type: text/html; charset=ISO-8859-1'); ?>
		<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es-CO">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7; IE=EmulateIE9" />
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" />
<title>PlacetoPay - Comprobante de pago</title>
	<style type="text/css">
	body {margin: 0px; font-family: Verdana, Arial, sans-serif; font-size: 10pt;}
	#placetopay-header { width: 550px; margin-left: auto; margin-right: auto; text-align: left; }
	#placetopay-content {
	width: 550px;
	margin-left: auto;
	margin-right: auto;
	border-radius: 10px;
	-moz-border-radius: 10px;
	-webkit-border-radius: 10px;
	-khtml-border-radius: 10px;
	-webkit-box-shadow: 1px 1px 5px 0px #ccc;
	box-shadow: 1px 1px 5px 0px #ccc;
	padding: 30px;
	text-align: center;
}
	#placetopay-footer { width: 550px; margin-left: auto; margin-right: auto; text-align: right; }
	h3 { padding: 5px; font-size: 16pt; }
	table.placetopay {width: 450px;margin-left: auto; margin-right: auto;}
	th.placetopay  {font-weight: bold; background-color: #000; color: #ffffff; padding: 5px; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; -khtml-border-radius: 5px; font-size: 11pt;}
	td.placetopayheader {font-size: 14pt; color: #000; font-weight: bold;}
	td.placetopaytitulo {font-size: 8pt; font-weight: bold; vertical-align: top}
	td.placetopayvalor {font-size: 10pt; text-align: justify;}
	th.placetopay1 {
	font-weight: bold;
	background-color: #00F;
	color: #ffffff;
	padding: 5px;
	border-radius: 5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	-khtml-border-radius: 5px;
	font-size: 11pt;
}
    </style>
</head>



<body>

<center>
  <table width="200" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td><img src="https://www.voziphone.com//images/galeria/imagen_id_512e71389ccf0.png" border=0/></td>
    </tr>
    <tr>
      <td>
       
        <table class="placetopay">
          <tr>
           
          </tr>
          <tr>
            <td class="placetopaytitulo" align="right">Razon Social:&nbsp;</td>
            <td class="placetopayvalor">C.I Inversiones voziphone S.A.S</td>
          </tr>
          <tr>
            <td class="placetopaytitulo" align="right">NIT:&nbsp;</td>
            <td class="placetopayvalor">900172616-9</td>
          </tr>
          <tr>
            <td colspan="2"></td>
          </tr>
          <tr>
            
          </tr>
          <tr>
            <td class="placetopaytitulo" align="right">Estado: </td>
            <td class="placetopayvalor">Transacci&oacute;n <?php echo  $status ?></td>
          </tr>

<tr>
            <td class="placetopaytitulo" align="right">Motivo: </td>
            <td class="placetopayvalor">  <?php echo $p2p->getErrorCode()." - ". $p2p->getErrorMessage()?></td>
          </tr>


          <tr>
            <td class="placetopaytitulo" align="right">Tipo de transacci&oacute;n:&nbsp;</td>
            <td class="placetopayvalor">VENTA NO PRESENCIAL</td>
          </tr>
          <tr>
            <td class="placetopaytitulo" align="right">Fecha y hora:&nbsp;</td>
            <td class="placetopayvalor"><?php echo  $p2p->getTransactionDate() ?></td>
          </tr>
          <tr>
            <td class="placetopaytitulo" align="right">Direcci&oacute;n IP:&nbsp;</td>
            <td class="placetopayvalor"><?php echo $_SERVER['REMOTE_ADDR']?></td>
          </tr>
          <tr>
            <td class="placetopaytitulo" align="right">Cliente:&nbsp;</td>
            <td class="placetopayvalor"><?php echo  $p2p->getShopperName() ?></td>
          </tr>
          <tr>
            <td class="placetopaytitulo" align="right">Email:&nbsp;</td>
            <td class="placetopayvalor"><a href="mailto:<?php echo $p2p->getShopperEmail() ?>"><?php echo $p2p->getShopperEmail() ?></a></td>
          </tr>
          <tr>
            <td class="placetopaytitulo" align="right">Referencia:&nbsp;</td>
            <td class="placetopayvalor"><?php echo  $p2p->getReference() ?></td>
          </tr>

          <tr>
            <td class="placetopaytitulo" align="right">Recarga de Saldo VoIP de:&nbsp;</td>
            <td class="placetopayvalor"><?php echo $p2p->getCurrency() . ' '. number_format($p2p->getTotalAmount(),2) ?></td>
          </tr>
          <tr>
            <td class="placetopaytitulo" align="right">IVA:&nbsp;</td>
            <td class="placetopayvalor"><?php echo $p2p->getCurrency() . " " . number_format($p2p->getTaxAmount(),2) ?></td>
          </tr>
          
          <tr>
            <td class="placetopaytitulo" align="right">Franquicia:&nbsp;</td>
            <td class="placetopayvalor"><?php echo  $p2p->getFranchiseName() ?></td>
          </tr>
          <tr>
            <td class="placetopaytitulo" align="right">Banco:&nbsp;</td>
            <td class="placetopayvalor"><?php echo  $p2p->getBankName() ?></td>
          </tr>
          <tr>
            <td class="placetopaytitulo" align="right">Autorizaci&oacute;n:&nbsp; / CUS:&nbsp;</td>
            <td class="placetopayvalor"><?php echo  $p2p->getAuthorization() ?></td>
          </tr>
          <tr>
            <td class="placetopaytitulo" align="right">Recibo:&nbsp;</td>
            <td class="placetopayvalor"><?php echo  $p2p->getReceipt() ?></td>
          </tr>
          <tr>
            <td class="placetopaytitulo" align="right">Descripcion:&nbsp;</td>
            <td class="placetopayvalor"><?php echo "Compra a Voziphone - Sitio Web" ?></td>
          </tr>

          
          <tr>
            <td colspan="2"></td>
          </tr>
          <tr>
           
          </tr>
          <tr>
            <td class="placetopayvalor" colspan="2"><p>&ldquo;si tiene alguna inquietud cont&aacute;ctenos al tel&eacute;fono 57 (4) 4444777.  V&iacute;a email <a href="mailto:info@voziphone.com">info@voziphone.com</a> o en  nuestro chat online <a href="http://www.voziphone.com">www.voziphone.com</a> &rdquo;</p></td>
          </tr>
        </table>
        <table width="200" border="0" cellpadding="0" cellspacing="0" class="placetopay">
          <tr>
            <td><p>&nbsp;</p>
              <p>&nbsp;</p></td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td><form action="../">
              <?php if($status == 'Aprobada'):?>
              <input type="submit" class="btn btn-primary" value="Volver al Inicio" />
              <?php else:?>
              <input type="submit" class="btn btn-primary" value="Reintentar" />
              <?php endif;?>
            </form></td>
            <td><a href="javascript:window.print()">Imprimir</a></td>
          </tr>
        </table>
        <p>&nbsp;</p>
      </div></td>
    </tr>
    <tr>
      <td><div id="placetopay-footer"> <img src="https://www.placetopay.com/images/customers/PLACETOPAY.png" border="0" alt=""/> </div></td>
    </tr>
  </table>
</center> 

<div id="placetopay-header"></div>
<br />
</body>
</html>

		<?php
	
	}
}
