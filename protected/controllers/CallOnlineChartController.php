<?php
/**
 * Acoes do modulo "Call".
 *
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
 * 19/09/2012
 */

class CallOnlineChartController extends Controller
{

	public function init()
	{
		if (!Yii::app()->session['id_user'])
            exit;
		$this->instanceModel = new CallOnlineChart;
		$this->abstractModel = CallOnlineChart::model();
		$this->titleReport   = Yii::t('yii','CallOnlineChart');
		parent::init();
	}

	public function actionRead()
	{

		$filter = isset($_GET['filter']) ? json_decode($_GET['filter']) : null;

        	if (isset($filter) && $filter[0]->value == 'hour') {
        		$sql = "SELECT id,  date , SUM(total) AS total, SUM(answer) AS answer
            	FROM pkg_call_chart WHERE 1 
            	GROUP BY DATE_FORMAT( DATE, '%Y-%m-%d %H' )  ORDER BY id DESC LIMIT 24 ";
        	}else{
        		$sql = "SELECT id, date, total, answer  FROM pkg_call_chart WHERE 1 ORDER BY id DESC LIMIT 20 ";
        	}
        	$records = Yii::app()->db->createCommand($sql)->queryAll();		

		# envia o json requisitado
		echo json_encode(array(
			$this->nameRoot => $records,
			$this->nameCount => 0
		));
		
	}

}