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

class QueueMemberDashBoardController extends Controller
{

	public $attributeOrder 		  = 'id';
	public $extraValues        = array('idQueue' => 'name');
	


	public function init()
	{
		$this->instanceModel = new QueueMemberDashBoard;
		$this->abstractModel = QueueMemberDashBoard::model();
		$this->titleReport   = Yii::t('yii','Queue Member DashBoard');

		parent::init();
	}

	public function getAttributesModelsCustom($attributes, $key, $item)
	{


		if ($attributes[$key]['agentStatus'] == 'On Hold' || $attributes[$key]['agentStatus'] == 'In use') {
		
			$sql = "SELECT * FROM pkg_queue_status WHERE id_agent = :id_agent";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_agent", $attributes[$key]['id'], PDO::PARAM_STR);
			$result = $command->queryAll();			
			
			return array(
				array(
					'key' => 'number',
					'value' => $result[0]['callerId']
				)
			);

		}	
			
	}
}