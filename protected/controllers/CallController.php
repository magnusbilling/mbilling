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
 * 17/08/2012
 */

class CallController extends Controller
{
	public $attributeOrder     = 'id DESC';
	public $extraValues        = array(
		'idUser' => 'username',
		'idPlan' => 'name',
		'idTrunk' => 'trunkcode',
		'idPrefix' => 'destination',
		'idCampaign' => 'name',
		);


	public $fieldsInvisibleClient = array(
		'username',
		'trunk',
		'buycost',
		'agent',
		'lucro',
		'id_user',
		'id_user',
		'provider_name'
	);

	public $fieldsInvisibleAgent = array(
		'trunk',
		'buycost',
		'agent',
		'lucro',
		'id_user',
		'id_user',
		'provider_name'
	);

	public $fieldsFkReport = array(
        	'id_user' => array(
            	'table' => 'pkg_user',
            	'pk' => 'id',
            	'fieldReport' => "username "
        ),
        	'id_trunk' => array(
            	'table' => 'pkg_trunk',
            	'pk' => 'id',
            	'fieldReport' => 'trunkcode'
        ),
        	'id_prefix' => array(
            	'table' => 'pkg_prefix',
            	'pk' => 'id',
            	'fieldReport' => 'destination'
        ),
		'id' => array(
			'table' => 'pkg_prefix',
			'pk' => 'id',
			'fieldReport' => 'destination'
		)
		,
		'id_campaign' => array(
			'table' => 'pkg_campaign',
			'pk' => 'id',
			'fieldReport' => 'name'
		)
    );

	public function init()
	{
		ini_set('memory_limit', '-1');
		$this->instanceModel = new Call;
		$this->abstractModel = Call::model();
		$this->titleReport   = Yii::t('yii','Calls');

		parent::init();

		if (!Yii::app()->getSession()->get('isAdmin'))
		{
			$this->join = 'JOIN pkg_user b ON t.id_user = b.id';
			$this->extraValues        = array(
				'idUser' => 'username',
				'idPlan' => 'name',
				'idPrefix' => 'destination',
				);

			$this->select = "t.id, t.id_user, t.id_plan, t.id_did,  t.id_prefix, t.id_campaign, t.starttime, t.sessiontime, t.calledstation, t.sessionbill, t.sipiax, t.src, t.terminatecauseid, t.agent_bill";
		}
	}

	public function extraFilter ($filter)
	{
		$filter = isset($this->filter) ? $filter.$this->filter : $filter;

		$filter = $filter . ' AND ' .$this->defaultFilter;
		$filter = $this->filterReplace($filter);

		if (Yii::app()->getSession()->get('isAgent'))
			$filter .= ' AND b.id_user = '.Yii::app()->getSession()->get('id_user');

		else if(Yii::app()->getSession()->get('isClient'))
			$filter .= ' AND t.id_user = '.Yii::app()->getSession()->get('id_user');
		return $filter;
	}

	/**
	 * Cria/Atualiza um registro da model
	 */
	public function actionSave()
	{
		$values = $this->getAttributesRequest();

		if(isset($values['id']) && !$values['id'])
		{
			echo json_encode(array(
				$this->nameSuccess => false,
				$this->nameRoot => 'error',
				$this->nameMsg => 'Operation no allow',
			));
			exit;
		}
		parent::actionSave();
	}

	

	public function getAttributesRequest() {
		$arrPost = array_key_exists($this->nameRoot, $_POST) ? json_decode($_POST[$this->nameRoot], true) : $_POST;
		//retira capos antes de salvar
		unset($arrPost['starttime']);
		unset($arrPost['callerid']);
		unset($arrPost['id_prefix']);
		unset($arrPost['username']);
		unset($arrPost['trunk']);
		unset($arrPost['terminatecauseid']);
		unset($arrPost['calltype']);
		unset($arrPost['agent']);
		unset($arrPost['lucro']);
		unset($arrPost['agent_bill']);
		unset($arrPost['idPrefixdestination']);

		return $arrPost;
	}

	public function actionSample()
	{
		$config = LoadConfig::getConfig();
		$filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
		$filter = $this->createCondition(json_decode($filter));
		$this->filter = $filter = $this->extraFilter($filter);
		

		$ids = json_decode($_REQUEST['ids']);

		$ids = implode(",", $ids);

		$modelCdr = Call::model()->findAll("t.id IN ($ids)");
			
		$folder = '/var/www/tmpmagnus/monitor';

		if (!file_exists($folder)) {
		    mkdir($folder, 0777, true);
		}
		array_map('unlink', glob("$folder/*"));
		
		if (count($modelCdr)) 
		{
			foreach ($modelCdr as $records)
			{				
				$number    = $records->calledstation;
				$day       = $records->starttime;
				$uniqueid  = $records->uniqueid;
				$username  = $records->idUser->username;
	
				$mix_monitor_format = $config['global']['MixMonitor_format'];
				
				exec('cp -rf  /var/spool/asterisk/monitor/*.'.$uniqueid.'.* '.$folder.'/');
			}		

			exec("tar -czf ".$folder."/records_".Yii::app()->session['username'].".tar.gz ".$folder."/*");
			$file_name = 'records_'.Yii::app()->session['username'].'.tar.gz';
			$path = $folder.'/'.$file_name;
			

			echo json_encode(array(
				$this->nameSuccess => true,
				$this->nameMsg =>  'success'
			));			
			
			header('Content-Description: File Transfer');
 			header("Content-Type: application/x-tar");
			header('Content-Disposition: attachment; filename=' . basename($file_name));
			header("Content-Transfer-Encoding: binary");
			header('Accept-Ranges: bytes');
			header( 'Content-type: application/force-download' );
			ob_clean();
			flush();
			readfile($path);				
			
		}
		else
		{
			echo json_encode(array(
				$this->nameSuccess => false,
				$this->nameMsg =>  'Audio no found'
			));
			exit;
		}	
			
	}

	public function actionReport()
	{

		ini_set("memory_limit", "1024M");

		$orientation = $_POST['orientation'];
		$_POST['columns'] = preg_replace('/idUserusername/', 'id_user', $_POST['columns']);
		$_POST['columns'] = preg_replace('/idPrefixdestination/', 'id', $_POST['columns']);
		$_POST['columns'] = preg_replace('/idPrefixprefix/', 'id_prefix', $_POST['columns']);


		$columns = json_decode($_POST['columns'], true);

		$filter = isset($_POST['filter']) ? $this->createCondition(json_decode($_POST['filter'])) : null;
		$fieldGroup = json_decode($_POST['group']);
		$sort = json_decode($_POST['sort']);

		$arraySort = ($sort && $fieldGroup) ? explode(' ', implode(' ', $sort)) : null;
		$dirGroup = $arraySort ? $arraySort[array_search($fieldGroup, $arraySort) + 1] : null;
		$firstSort = $fieldGroup ? $fieldGroup.' '.$dirGroup.',' : null;
		$sort = $sort ? $firstSort.implode(',', $sort) : null;

		//magnus
		$this->filter = $filter = $this->extraFilter($filter);
    		//end magnus

    		if (preg_match("/id_campaign/", $filter)) {

			$filterCampaign = 1;
			foreach (explode("AND", $filter) as $key => $value) {
				if (preg_match('/id_campaign/', $value)) {
					$filterCampaign = preg_replace("/id_campaign/", "id", $value);
					break;
				}			 
			}

			$sql       = "SELECT * FROM pkg_campaign WHERE $filterCampaign";
			$resultCampaign =  Yii::app()->db->createCommand($sql)->queryAll();
			$nameCampaign = $resultCampaign[0]['name'];			
			$timeCampaign = $resultCampaign[0]['nb_callmade'];

			if($timeCampaign > 0){				
			
				$timeCampaign80 = $timeCampaign * 0.8;
				$timeCampaign60 = $timeCampaign * 0.6;
				$timeCampaign40 = $timeCampaign * 0.4;
				$timeCampaign20 = $timeCampaign * 0.2;

				$columns = array(
					array('header' => "100%", 'dataIndex' => 'real_sessiontime'),
					array('header' => "80% a 99% ", 'dataIndex' => 'sessionid'),
					array('header' => "60% a 79%", 'dataIndex' => 'id_plan'),
					array('header' => "40% a 59% ", 'dataIndex' => 'id_did'),
					array('header' => "20% a 39% ", 'dataIndex' => 'id_prefix'),
					array('header' => "Menos que 20% ", 'dataIndex' => 'id_offer'),
					);

				$select = "
				( SELECT COUNT(sessiontime) FROM pkg_cdr t $this->join WHERE $filter AND sessiontime >= $timeCampaign  ) AS real_sessiontime, 
				( SELECT COUNT(sessiontime) FROM pkg_cdr t $this->join WHERE $filter AND sessiontime >= $timeCampaign80 AND sessiontime < $timeCampaign ) AS sessionid,
				( SELECT COUNT(sessiontime) FROM pkg_cdr t $this->join WHERE $filter AND sessiontime >= $timeCampaign60 AND sessiontime < $timeCampaign80) AS id_plan,
				( SELECT COUNT(sessiontime) FROM pkg_cdr t $this->join WHERE $filter AND sessiontime >= $timeCampaign40 AND sessiontime < $timeCampaign60 ) AS id_did,
				( SELECT COUNT(sessiontime) FROM pkg_cdr t $this->join WHERE $filter AND sessiontime >= $timeCampaign20 AND sessiontime < $timeCampaign40 ) AS id_prefix,
				( SELECT COUNT(sessiontime) FROM pkg_cdr t $this->join WHERE $filter AND sessiontime <= $timeCampaign20   ) AS id_offer
				";

				$records = $this->abstractModel->findAll(array(
					'select' => $select,
					'join' => $this->join,
					'condition' => $filter,
					'order' => $sort,
					'limit'     => 1,
				));

				$count = $this->abstractModel->count(array(
					'join'      => $this->join,
					'condition' => $filter
				));

				$this->titleReport = "Estatistica da campanha $nameCampaign";
				$this->subTitleReport = "Total de chamadas $count";			
			}else{
				$select = $this->getColumnsFromReport($columns, $fieldGroup);
				$records = $this->abstractModel->findAll(array(
					'select' => $select,
					'join' => $this->join,
					'condition' => $filter,
					'order' => $sort
				));
			}
		}
		else
		{
			$select = $this->getColumnsFromReport($columns, $fieldGroup);
			$records = $this->abstractModel->findAll(array(
				'select' => $select,
				'join' => $this->join,
				'condition' => $filter,
				'order' => $sort
			));
		}



		$report                 = new Report();
		$report->orientation    = $orientation;
		$report->title          = $this->titleReport;
		$report->subTitle       = $this->subTitleReport;
		$report->columns        = $columns;
		$report->columnsTable   = $this->getColumnsTable();
		$report->fieldsCurrency = $this->fieldsCurrencyReport;
		$report->fieldsPercent  = $this->fieldsPercentReport;
		$report->fieldsFk       = $this->fieldsFkReport;
		$report->renderer       = $this->rendererReport;
		$report->fieldGroup     = $fieldGroup;
		$report->records        = $this->getAttributesModels($records);
		$report->generate();
	}
}