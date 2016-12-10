<?php
/**
 * View to modulo "PlacetoPay Check transaction".
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
 * MagnusSolution.com <info@magnussolution.com>
 * 2016-03-31
 */

class PlaceToPayCommand extends CConsoleCommand 
{
    var $config;

    public function run($args)
    {
        // incluye las librerias de PlacetoPay
        try {
              include ('/var/www/html/mbilling/lib/PlacetoPay/classes/EGM/PlacetoPay.php');
        } catch (Exception $e) {
            echo 'error';
            exit;
        }
      

        $sql = "SELECT * FROM pkg_method_pay WHERE payment_method LIKE 'PlacetoPay'";
        $methodPay = Yii::app()->db->createCommand( $sql )->queryAll();

        // define los datos propios del comercio
        define('P2P_CustomerSiteID', $methodPay[0]['P2P_CustomerSiteID']);

        // A continuacion se describen la serie de pasos a realizar para resolver
        // el estado de las transaaciones pendientes
        try {
            // 1. Inicializa el objeto de PlacetoPay
            $p2p = new PlacetoPay();
            $sql = 'SELECT * FROM pkg_refill WHERE description LIKE "%pendiente%" AND payment = 0 ';
            $pagosResult = Yii::app()->db->createCommand( $sql )->queryAll();
            // 3. Realiza la consulta a la base de datos, por aquellas transacciones que estan pendientes
            // y cuya antiguedad es superior a 5 minutos
            foreach ($pagosResult as $pago) {
                // 4. Consulta la respuesta de la operacion
                $rc = $p2p->queryPayment(P2P_CustomerSiteID, $pago['id'], 'COP', $pago['credit']);
                if ((($rc == PlacetoPay::P2P_ERROR) && ($p2p->getErrorCode() != 'HTTP')) || ($rc == PlacetoPay::P2P_DECLINED)) {
                    echo 'actualice la BD, no se hizo el pago';

                    $id = $pago['id'];
                    $id_user = $pago['id_user'];
                    $description = "Recarga PlaceToPay <font color=red>rechazada</font>. Referencia: $id, Autorizacion/CUS: ". $p2p->getAuthorization();                    
                    $sql = "UPDATE pkg_refill SET description = '$description' WHERE id = ".$id;
                    Yii::app()->db->createCommand( $sql )->execute();



                } else if (($rc == PlacetoPay::P2P_APPROVED) || ($rc == PlacetoPay::P2P_DUPLICATE)) {
                    echo 'actualice la BD, asiente el pago';

                    $id = $pago['id'];
                    $id_user = $pago['id_user'];
                    $description = "Recarga PlaceToPay <font color=green>Aprobada</font>. Referencia: $id, Autorizacion/CUS: ". $p2p->getAuthorization(). ', '. $p2p->getFranchiseName();
                    
                    $sql = "UPDATE pkg_refill SET description = '$description', payment = 1 WHERE id = ".$id;
                    Yii::app()->db->createCommand( $sql )->execute();

                    $sql = "UPDATE pkg_user SET credit = credit + ".$pago['credit']." WHERE id = ".$id_user;
                    Yii::app()->db->createCommand( $sql )->execute();


                    $sql = "SELECT * FROM pkg_user WHERE id =". $id_user;
                    $resultUser  = Yii::app()->db->createCommand( $sql )->queryAll();
        
                    if ( $resultUser[0]['country'] == 57 && $pago['credit'] > 0) {
                        $sql = "INSERT INTO pkg_invoice (id_user) VALUES ($id_user)";
                        Yii::app()->db->createCommand( $sql )->execute();
                        $invoice_number = Yii::app()->db->lastInsertID;
                        $sql = "UPDATE pkg_refill SET invoice_number = '$invoice_number' WHERE id = ".$id;
                        Yii::app()->db->createCommand( $sql )->execute();
                    }
            


                    $mail = new Mail(Mail::$TYPE_REFILL, $id_user);
                    $mail->replaceInEmail(Mail::$ITEM_ID_KEY, $id);
                    $mail->replaceInEmail(Mail::$ITEM_AMOUNT_KEY, $pago['credit']);
                    $mail->replaceInEmail(Mail::$DESCRIPTION, $description);
                    $mail->send();



                } else if ($rc == PlacetoPay::P2P_PENDING) {
                    echo 'no haga nada';
                } else {
                    echo 'genere un log, pudo ser un problema de telecomunicaciones';
                }
            }
            unset($dbConn); 
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

?>