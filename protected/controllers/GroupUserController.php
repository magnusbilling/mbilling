<?php
/**
 * Actions of module "GroupUser".
 *
 * MagnusBilling <info@magnusbilling.com>
 * 15/04/2013
 */

class GroupUserController extends Controller
{
	public $attributeOrder          = 't.id';
	public $titleReport             = 'GroupUser';
	public $subTitleReport          = 'GroupUser';
	public $nameModelRelated        = 'GroupModule';
	public $extraFieldsRelated      = array('show_menu', 'action', 'id_module', 'createShortCut', 'createQuickStart');
	public $extraValuesOtherRelated = array('idModule' => 'text');
	public $nameFkRelated           = 'id_group';
	public $nameOtherFkRelated      = 'id_module';
	public $extraValues             = array('idUserType' => 'name');

	public $filterByUser = false;

	public function init()
	{
		$this->instanceModel = new GroupUser;
		$this->abstractModel = GroupUser::model();
		$this->abstractModelRelated = GroupModule::model();
		parent::init();
	}

	public function actionGetUserType()
	{
		$filter = isset($_POST['filter']) ? $_POST['filter'] : null;
		$filter = $filter ? $this->createCondition(json_decode($filter)) : $this->defaultFilter;

		
		$sql = "SELECT id_user_type FROM pkg_group_user WHERE ".$filter;
		$result = Yii::app()->db->createCommand($sql)->queryAll();

		echo json_encode(array(
			$this->nameRoot => $result[0]['id_user_type'] == 1 ? true : false
		));
	}


	public function actionIndex()
	{
		$filter = isset($_POST['filter']) ? $_POST['filter'] : null;
		$filter = $filter ? $this->createCondition(json_decode($filter)) : $this->defaultFilter;

		
		$sql = "SELECT id FROM pkg_group_user WHERE ".$filter;
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		$ids = array();
		foreach ($records as $value) {
		
			$ids[] = $value['id'];
		}


		echo json_encode(array(
			$this->nameRoot => $ids
		));
	}
}
?>