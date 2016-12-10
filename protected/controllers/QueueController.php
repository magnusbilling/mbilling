<?php
/**
 * Acoes do modulo "Queue".
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
 * 17/08/2012
 */

class QueueController extends Controller
{
	public $attributeOrder 		  = 'id';
	public $extraValues           = array('idUser' => 'username');

	private $host = 'localhost';
    private $user = 'magnus';
    private $password = 'magnussolution';

	public $fieldsFkReport = array(
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		)
	);

	public function init()
	{
		$this->instanceModel = new Queue;
		$this->abstractModel = Queue::model();
		$this->titleReport   = Yii::t('yii','Queue');
		parent::init();
	}

	public function actionResetQueueStats()
	{
      
		$filter = isset($_POST['filter']) ? $_POST['filter'] : null;
		$filter = $this->createCondition(json_decode($filter));
		$this->filter = $filter = $this->extraFilter($filter);
		

		$id = json_decode($_POST['ids']);

		$ids = array();
		
		foreach ($id as $key => $value) {
			array_push($ids, $value);
		}
		$uniID = count($ids) == 1 ? true : false;

		$sql = "TRUNCATE pkg_queue_status";
		Yii::app()->db->createCommand($sql)->execute();

		$filter = "t.id IN (".PreparedStatementHelper::arrayToParams($ids).")";	
		
		
		$sql       = "SELECT name FROM pkg_queue t  WHERE $filter";
		$command = Yii::app()->db->createCommand($sql);		
		PreparedStatementHelper::bindArrayParams($ids,$command);
		$result = $command->queryAll();

		foreach ($result as $key => $queue) {
			try {
				$asmanager = new AGI_AsteriskManager;
        		$asmanager->connect($this->host, $this->user, $this->password);
        		$asmanager->Command("queue reset stats ".$queue['name']);
				$asmanager->disconnect();

				$sussess = true;
			} catch (Exception $e) {
				$sussess = true;
				$this->msgSuccess = "Error";
			}			
		}
		echo json_encode(array(
			$this->nameSuccess => $sussess,
			$this->nameMsg =>  $this->msgSuccess
		));		
			
	}
}