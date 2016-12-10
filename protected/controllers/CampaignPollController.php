<?php
/**
 * Acoes do modulo "CampaignPoll".
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
 * 28/10/2012
 */

class CampaignPollController extends Controller
{
	public $attributeOrder        = 't.id';
	public $extraValues           = array('idCampaign' => 'name');
	public $FilterByUser	      = 'pkg_campaign.id_user';
	public $join                  = 'JOIN pkg_campaign ON pkg_campaign.id = id_campaign';

	public $fieldsFkReport = array(
		'id_campaign' => array(
			'table' => 'pkg_campaign',
			'pk' => 'id',
			'fieldReport' => 'name'
		)
	);

	public function init()
	{
		$this->instanceModel = new CampaignPoll;
		$this->abstractModel = CampaignPoll::model();
		$this->titleReport   = Yii::t('yii','Poll');
		parent::init();
	}

	public function extraFilter ($filter)
	{
		$filter = isset($this->filter) ? $filter.$this->filter : $filter;

		$filter = $filter . ' AND ' .$this->defaultFilter;

		$filter = preg_replace('/name/', 't.name', $filter);

		$filter = $this->filterReplace($filter);

		if(Yii::app()->getSession()->get('user_type')  == 2)
			$filter .= ' AND pkg_campaign.id_user IN  (SELECT id FROM pkg_user WHERE id_user = '.Yii::app()->getSession()->get('id_user').' ) ';


		else if(Yii::app()->getSession()->get('user_type')  > 1 && $this->filterByUser)
		{
			$filter .= ' AND '. $this->defaultFilterByUser . ' = '.Yii::app()->getSession()->get('id_user');
		}

		return $filter;
	}
	
	/**
	 * Exclui os audios da enquete
	 */
	public function actionDestroy()
	{
		parent::actionDestroy();
		if($this->success == true)
		{
			# recebe os parametros da exclusao
			$values = $this->getAttributesRequest();
			$arrayPkAlias = explode('.', $this->primaryKey);
			$namePk = $arrayPkAlias[count($arrayPkAlias) - 1];
			$ids = array();
				
			if(isset($_POST['filter']))
			{
				$filter = isset($_POST['filter']) ? $_POST['filter'] : null;
				$filter = $filter ? $this->createCondition(json_decode($filter)) : $this->defaultFilter;

				$this->filter = $filter = $this->extraFilter($filter);

				$records = $this->abstractModel->findAll(array(
					'select' => 't.'.$namePk,
					'join'      => $this->join,
					'condition' => $filter
				));
				
				$records = $this->getAttributesModels($records);

				foreach($records as $record)
				{
					array_push($ids, $record[$namePk]);

				}
			}
			else 
			{
				# Se existe a chave 0, indica que existe um array interno (mais de 1 registro selecionado)
				if(array_key_exists(0, $values))
				{
					# percorre o array para excluir o(s) registro(s)
					foreach($values as $value)
					{
						array_push($ids, $value[$namePk]);
					}
				}
				else
				{
					array_push($ids, $values[$namePk]);
				}
			}

			foreach ($ids as $id) 
			{
				//deleta os audios da enquete
				$uploaddir = "resources/sounds/";
				$uploadfile = $uploaddir .'idPoll_'. $id .'.gsm';
				if (file_exists($uploadfile)) 
				{
					chown($uploadfile,666);
					unlink($uploadfile);
				}
			}	
		}		
	}


}