<?php
/**
 * Actions of module "User".
 *
 * MagnusBilling <info@magnusbilling.com>
 * 15/04/2013
 */

class UserController extends Controller
{

	public $attributeOrder = 'credit DESC';
	public $titleReport    = 'User';
	public $subTitleReport = 'User';
	
	public $extraValues    = array('idGroup' => 'name,id_user_type', 'idPlan' => 'name', 'idUser' => 'username');
	public $nameFkRelated  = 'idUser';

	public $fieldsFkReport = array(
		'id_group' => array(
			'table' => 'pkg_group_user',
			'pk' => 'id',
			'fieldReport' => 'name'
		),
		'id_plan' => array(
			'table' => 'pkg_plan',
			'pk' => 'id',
			'fieldReport' => 'name'
		),		
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		)
	);

	public $fieldsInvisibleClient = array(
		'active_paypal',
		'boleto',
		'boleto_day',
		'callshop',
		'creditlimit',
		'currency',
		'description',
		'enableexpire',
		'expirationdate',
		'expiredays',
		'firstusedate',
		'id_group',
		'idGroupname',
		'id_offer',
		'id_user',
		'id_plan',
		'idAgentlogin',
		'creationdate',
		'lastuse',
		'typepaid',
		'loginkey',
		'last_notification',
		'restriction',
		'plan_day',
		'record_call',
		'idGroupid_user_type',
		'idPlanname'
	);
	public $fieldsInvisibleAgent = array(
		'id_group',
		'idGroupname',
		'enableexpire',
		'expirationdate',
		'record_call',
		'id_offer',
		'loginkey'
	);

	public function init()
	{
		$this->instanceModel = new User;
		$this->abstractModel = User::model();
		parent::init();
	}

	public function actionCredit()
	{
		if(!Yii::app()->session['id_user'])
			exit();
		$sql      = "SELECT credit FROM pkg_user WHERE id = :id";		
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id", $_POST['id'], PDO::PARAM_STR);
		$result = $command->queryAll();

		$credit 	= array('rows' => array('credit' => $result[0]['credit']));

		echo json_encode($credit);
	}

	public function actionReport()
	{
		//altera as colunas para nao mostrar tipo de usuario
		$destino = ',{"header":"UserType","dataIndex":"idGroupid_user_type"}';
		if (preg_match("/$destino/", $_POST['columns'])) {
			$_POST['columns'] = preg_replace("/$destino/", '' , $_POST['columns']);
		}
		parent::actionReport();
	}
	public function actionCsv()
	{
		//altera as colunas para nao mostrar tipo de usuario
		$destino = ',{"header":"UserType","dataIndex":"idGroupid_user_type"}';
		if (preg_match("/$destino/", $_GET['columns'])) {
			$_GET['columns'] = preg_replace("/$destino/", '' , $_GET['columns']);
		}
		parent::actionCsv();
	}

	public function columnsReplace($arrayColumns)
	{
		for ($i=0; $i < count($arrayColumns); $i++) { 
			if ($arrayColumns[$i] != 't.credit' && substr($arrayColumns[$i], 0,1) != '(') {	
				$arrayColumns[$i] = 't.'.$arrayColumns[$i];
			}
		}
		return $arrayColumns;
	}

	public function extraFilter ($filter)
	{
		$filter = isset($this->filter) ? $filter.$this->filter : $filter;

		$filter = $filter . ' AND ' .$this->defaultFilter;
		$filter = $this->filterReplace($filter);

		if(Yii::app()->getSession()->get('user_type')  == 2)
			$filter .= ' AND id_user = '.Yii::app()->getSession()->get('id_user');
		else if (Yii::app()->getSession()->get('user_type')  == 3)
			$filter .= ' AND id = '.Yii::app()->getSession()->get('id_user');

		return $filter;
	}



	public function actionGetNewUsername()
	{
		echo json_encode(array(
			$this->nameSuccess => true,
			'newUsername' => Util::getNewUsername()
		));
	}

	public function actionGetNewPassword()
	{
		echo json_encode(array(
			$this->nameSuccess => true,
			'newPassword' => Util::gerarSenha(8, true, true, true, false)
		));
	}

	public function actionGetNewPinCallingcard()
	{
		$existsVoucher =  true;
		while ($existsVoucher)
		{
			$randVoucher = Util::gerarSenha(6, false, false, true, false);
			$sql = "SELECT count(id) FROM pkg_voucher 
			WHERE voucher LIKE :randVoucher OR (SELECT count(id) FROM pkg_user WHERE callingcard_pin LIKE :randVoucher) > 0";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":randVoucher", $randVoucher, PDO::PARAM_STR);
			$countVoucher = $command->queryAll();

			if (count($countVoucher) > 0) {
				$existsVoucher = false;
				break;
			}			
		}
		echo json_encode(array(
			$this->nameSuccess => true,
			'newCallingcardPin' => $randVoucher
		));
	}


}
?>