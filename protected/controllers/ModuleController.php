<?php
/**
 * Actions of module "Module".
 *
 * MagnusBilling <info@magnusbilling.com>
 * 15/04/2013
 */

class ModuleController extends Controller
{
	public $titleReport        = 'Module';
	public $subTitleReport     = 'Module';
	public $extraValues        = array('idModule' => 'text');
	public $nameModelRelated   = 'GroupModule';
	public $nameFkRelated      = 'id_module';
	public $nameOtherFkRelated = 'id_group';
	public $filterByUser       = false;
	public $attributeOrder     = 't.id';
	public $fieldsFkReport 	   = array(
		'id_module' => array(
			'table' => 'module',
			'pk' => 'id',
			'fieldReport' => 'text'
		)
	);


	public function init()
	{
		$this->instanceModel = new Module;
		$this->abstractModel = Module::model();
		$this->abstractModelRelated = GroupModule::model();
		parent::init();
	}

}
?>