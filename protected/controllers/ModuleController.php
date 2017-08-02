<?php
/**
 * Actions of module "Module".
 *
 * Fabio Jr. Policeno <fabiojpoli@hotmail.com> 
 * 15/04/2013
 */

class ModuleController extends Controller
{
	public $defaultSort = null;
	public $defaultSortDir = null;
	public $fixedWhere = null;

	public $attributeOrder     = 't.id';
	public $titleReport = 'Module';
	public $subTitleReport = 'Module';
	public $rendererReport = array(
		'id_module' => 'idModuletext'
	);

	public function init()
	{
		$this->instanceModel = new Module;
		$this->abstractModel = Module::model();
		parent::init();
	}


	public function actionReadTree()
	{
	
		$res = $this->actionRead(false);
		$modules = $res['rows'];
		$result['rows'] = $this->getModuleTree($modules);
		echo CCJSON::encode($result);
	
	}

	private function getModuleTree($modules) {
		$result = array();

	
			foreach ($modules as $model) {
	
				if(empty($model['id_module'])) {
					$childs = $this->getSubModuleTree($modules, $model['id']);

					array_push($result, array(
						'id' => $model['id'],
						'text' => $model['text'],
						'iconCls' => $model['icon_cls'],
						'id_module' => $model['id_module'],
						'rows' => $childs,
						'checked' => false,
						'expanded' => $model['id'] == 1 ? true : false,
						'leaf' => !count($childs)
					));
				}
			}
		

		return $result;
	}

	private function getSubModuleTree($modules, $idOwner) {
		$subModulesOwner = Util::arrayFindByProperty($modules, 'id_module', $idOwner);
		$result = array();

		foreach ($subModulesOwner as $model) {
			if(!empty($model['id_module'])) {
				array_push($result, array(
					'id' => $model['id'],
					'text' => $model['text'],
					'iconCls' => $model['icon_cls'],
					'id_module' => $model['id_module'],
					'module' => $model['module'],
					'checked' => false,
					'leaf' => true
				));
			}
			else {
				array_push($result, array(
					'id' => $model['id'],
					'text' => $model['text'],
					'iconCls' => $model['icon_cls'],
					'id_module' => $model['id_module'],
					'rows' => $this->getSubModuleTree($modules, $model['id']),
					'checked' => false,
					'expanded' => true
				));
			}
		}

		return $result;
	}
}
?>