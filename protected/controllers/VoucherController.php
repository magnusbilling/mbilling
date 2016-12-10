<?php
/**
 * Acoes do modulo "Voucher".
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
 * 20/09/2012
 */

class VoucherController extends Controller
{
	public $attributeOrder        = 't.id';
	public $extraValues        = array('idUser' => 'username');
	public $fieldsInvisibleClient = array(
		'tag',
		'creationdate',
		'expirationdate',
		'used',
		'currency',
	);
	public $fieldsInvisibleAgent = array(
		'tag',
	);

	public function init()
	{
		$this->instanceModel = new Voucher;
		$this->abstractModel = Voucher::model();
		$this->titleReport   = Yii::t('yii','Voucher');
		parent::init();
	}

	public function actionSample()
	{
		$this->abstractModel->sample();
	}


	public function actionSave()
	{

		if(Yii::app()->getSession()->get('isClient'))
		{
			$values = $this->getAttributesRequest();

			if(isset($_SESSION['isClient']) && $_SESSION['isClient'])
			{
				foreach ($this->fieldsInvisibleClient as $field) {
	   				unset($values[$field]);
				}
			}

			if(isset($_SESSION['isAgent']) && $_SESSION['isAgent'])
			{
				foreach ($this->fieldsInvisibleAgent as $field) {
	   				unset($values[$field]);
				}
			}

			$result = $this->abstractModel->findAll(array(
				'select' => 'voucher, credit',
				'condition' => "voucher= '".$values['voucher']."' AND used = 0",
			));

			if(isset($result[0]['voucher']))
			{
				$sql = "UPDATE pkg_voucher SET voucher= ".$values['voucher'].", 
								username = :username, used = 1, 
								usedate = now() WHERE voucher= :voucher";

				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":username", Yii::app()->getSession()->get('login'), PDO::PARAM_STR);
				$command->bindValue(":voucher", $values['voucher'], PDO::PARAM_STR);
				$command->execute();

				$this->success = true;
				$this->msg = 'Operacao realizada com sucesso.';

				$sql = "UPDATE pkg_card SET credit = credit + :credit WHERE username = :username";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":username", Yii::app()->getSession()->get('login'), PDO::PARAM_STR);
				$command->bindValue(":credit", $result[0]['credit'], PDO::PARAM_STR);
				$command->execute();

				$model = new Refill;
				$model->id_user = Yii::app()->getSession()->get('userid');
				$model->credit = $result[0]['credit'];
				$model->description = 'Voucher '.$values['voucher'];
				$model->added_invoice = 1;
				$idLogrefill = $model->save() ? $model->save() : false;

				$model = new Payment;
				$model->id_user = Yii::app()->getSession()->get('userid');
				$model->payment = $result[0]['credit'];
				$model->id_logrefill = $idLogrefill;
				$model->description = 'Voucher '.$values['voucher'];
			}
			else
			{
				$this->success = false;
				$this->msg = 'Voucher inexistente';
			}			
			
			# retorna o resultado da execucao
			echo json_encode(array(
				$this->nameSuccess => $this->success,
				$this->nameMsg => $this->msg
			));
		}
		else
		{

			$values = array_key_exists($this->nameRoot, $_POST) ? json_decode($_POST[$this->nameRoot], true) : $_POST;

			if(isset($values['quantity']) && $values['quantity'] > 1){
				for ($i=0; $i < $values['quantity']; $i++) { 
					
					$voucher = $this->geraVoucher();
					$sql = "INSERT INTO pkg_voucher (id_plan, voucher, credit, tag, language, prefix_local) VALUES 
										(:id_plan, :voucher, :credit, :tag, :language, :prefix_local)";
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":id_plan", $values['id_plan'], PDO::PARAM_STR);
					$command->bindValue(":voucher", $voucher , PDO::PARAM_STR);
					$command->bindValue(":credit", $values['credit'] , PDO::PARAM_STR);
					$command->bindValue(":tag", $values['tag'] , PDO::PARAM_STR);
					$command->bindValue(":language", $values['language'] , PDO::PARAM_STR);
					$command->bindValue(":prefix_local", $values['prefix_local'] , PDO::PARAM_STR);
					$command->execute();
				}

				$newRecord = $this->abstractModel->findAll(array(
					'select'    => $this->select,
					'join'      => $this->join
				));

				echo json_encode(array(
					$this->nameSuccess => true,
					$this->nameRoot => $this->getAttributesModels($newRecord, $this->extraValues),
					$this->nameMsg => $this->msgSuccess
				));
				exit;

			}else{
				parent::actionSave();
			}

			
		}
	}

	public function getAttributesRequest() {

		if (isset($_POST[$this->nameRoot])) {
			$values = json_decode($_POST[$this->nameRoot], true);
			if (isset($values['quantity']) && $values['quantity'] == 1) {
				unset($values['quantity']);
				unset($values['idUserusername']);
				$values['voucher'] = $this->geraVoucher();
			}
		}else{
			$values = array_key_exists($this->nameRoot, $_POST) ? json_decode($_POST[$this->nameRoot], true) : $_POST;
		}

		return $values;
	}


	public function extraFilter ($filter)
	{

		$filter = isset($this->filter) ? $filter.$this->filter : $filter;
		$filter = $filter . ' AND ' .$this->defaultFilter;
		$filter = $this->filterReplace($filter);
		if (isset($this->defaultFilterAgent))
		{
			if(Yii::app()->getSession()->get('user_type')  == 1)
				$filter .= ' AND '. $this->defaultFilterAgent . ' = 1';
			else if(Yii::app()->getSession()->get('user_type')  == 2)
				$filter .= ' AND '. $this->defaultFilterAgent . ' = '.Yii::app()->getSession()->get('id_user');
		}

		if(Yii::app()->getSession()->get('user_type')  == 3)
			$filter .= ' AND t.username = '.Yii::app()->getSession()->get('login');


		return $filter;
	}

	public function geraVoucher()
	{
		$existsVoucher =  true;
		while ($existsVoucher)
		{
			$randVoucher = Util::gerarSenha(6, false, false, true, false);
			$sql = "SELECT count(id) FROM pkg_voucher WHERE voucher LIKE :randVoucher 
				OR (SELECT count(id) FROM pkg_user WHERE callingcard_pin LIKE :randVoucher) > 0";
	
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":randVoucher", $randVoucher, PDO::PARAM_STR);
			$countVoucher   = $command->queryAll();


			if (count($countVoucher) > 0) {
				$existsVoucher = false;
				break;
			}
		}

		return $randVoucher;
	}
}