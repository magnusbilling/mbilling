<?php
/**
 * Acoes do modulo "CallShop".
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
 * 19/09/2012
 */
Yii::import('application.controllers.CallShopController');
class CallShopORController extends CallShopController
{
    private $l='callshop';


    public function getAttributesModels($models, $itemsExtras = array())
    {

        $attributes = false;
        foreach ($models as $key => $item)
        {
            $attributes[$key]                          = $item->attributes;

            if (strlen($item->callshopnumber) > 5 && $this->l == 'callshop') {
                $decimal = strlen(Yii::app()->session['decimal']);
                $sql = "SELECT destination,buyrate FROM pkg_rate_callshop WHERE dialprefix = SUBSTRING('$item->callshopnumber' ,1,length(dialprefix)) AND id_user = '".$_SESSION['id_user']."' ORDER BY LENGTH(dialprefix) DESC";
                $resultCallShop = Yii::app()->db->createCommand( $sql )->queryAll();
                $attributes[$key]['callshopdestination']   = isset($resultCallShop[0]['destination']) ? $resultCallShop[0]['destination']."</br> </br><b>". Yii::app()->session['currency']. ' '.number_format($resultCallShop[0]['buyrate'],$decimal).' /min<b>' : 'Not Found';
            }
            if ($this->l == 'callshop') {
                $decimal = strlen(Yii::app()->session['decimal']);
                $sql = 'SELECT SUM(price) priceSum FROM pkg_callshop t WHERE cabina = "'.$item->name.'" AND status = 0';
                $sumResult = Yii::app()->db->createCommand($sql)->queryAll();
                $total = is_numeric($sumResult[0]['priceSum']) ? number_format($sumResult[0]['priceSum'],$decimal) : '0.00';
                $attributes[$key]['callerid'] .= "</br></br><b>Total: ".Yii::app()->session['currency']. ' '.$total."<b>";
            }

            if(isset($_SESSION['isClient']) && $_SESSION['isClient'])
            {
                foreach ($this->fieldsInvisibleClient as $field) {
                    unset($attributes[$key][$field]);
                }
            }
            
            if(isset($_SESSION['isAgent']) && $_SESSION['isAgent'])
            {
                foreach ($this->fieldsInvisibleAgent as $field) {
                    unset($attributes[$key][$field]);
                }
            }


            foreach($itemsExtras as $relation => $fields)
            {
                $arrFields = explode(',', $fields);
                foreach($arrFields as $field)
                {
                    $attributes[$key][$relation . $field] = $item->$relation->$field;
                    if($_SESSION['isClient']) {
                        foreach ($this->fieldsInvisibleClient as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }

                    if($_SESSION['isAgent']) {
                        foreach ($this->fieldsInvisibleAgent as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }
                }
            }
        }

        return $attributes;
    }
}?>