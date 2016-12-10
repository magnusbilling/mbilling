<?php
/**
 * Acoes do modulo "Refill".
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
 * 23/06/2012
 */

class RefillChartController extends Controller
{
    public $attributeOrder      = 'date DESC';

    public function actionRead()
    {
        $filter = isset($_GET['filter']) ? json_decode($_GET['filter']) : null;

        if (isset($filter) && $filter[0]->value == 'day') {
            $sql = "SELECT id, DATE_FORMAT( DATE,  '%Y-%m-%d' ) AS CreditMonth , SUM( credit ) AS sumCreditMonth
            FROM pkg_refill WHERE 1 GROUP BY DATE_FORMAT( DATE,  '%Y%m%d' ) ORDER BY id DESC LIMIT 30";
       
        }else{
            $sql = "SELECT id, DATE_FORMAT( DATE,  '%Y-%m' ) AS CreditMonth , SUM(credit) AS sumCreditMonth
            FROM pkg_refill WHERE 1 GROUP BY EXTRACT(YEAR_MONTH FROM date)  ORDER BY id DESC LIMIT 20 ";
         
        }
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        
        # envia o json requisitado
        echo json_encode(array(
            $this->nameRoot => $records,
            $this->nameCount => 25
        ));

    }
}