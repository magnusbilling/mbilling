<?php
/**
 * Acoes do modulo "Did".
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
 * 24/09/2012
 */

class DidController extends Controller
{
	public $attributeOrder        = 't.id';
	public $extraValues           = array('idUser' => 'username');
	public $config;

	public $fieldsFkReport = array(
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		)
	);
	
	public $fieldsInvisibleClient = array(
		'id_user',
		'id_didgroup',
		'activated',
		'creationdate',
		'startingdate',
		'expirationdate',
		'description',
		'billingtype',
		'selling_rate',
	);

	public function init()
	{
		$this->instanceModel = new Did;
		$this->abstractModel = Did::model();
		$this->titleReport   = Yii::t('yii','Did');
		parent::init();
		
		//for agents add filter for show only numbers free
		$this->filter        = Yii::app()->getSession()->get('isAgent')  ? ' AND reserved = 0 ' : false;

		
	}

	public function extraFilter($filter)
	{
		if(isset($_GET['buy'])){
			//return to combo buy credit
			$filter = 'reserved = 0';
			return $filter;
		}
		
		$filter = isset($this->filter) ? $filter.$this->filter : $filter;

		$filter = $filter . ' AND ' .$this->defaultFilter;
		$filter = $this->filterReplace($filter);

		if(Yii::app()->getSession()->get('user_type')  > 1 && $this->filterByUser)
		{
			$filter .= ' AND '. $this->defaultFilterByUser . ' = '.Yii::app()->getSession()->get('id_user');
		}
		return $filter;
	}

	public function actionBuy()
	{	
		$success = false;

		$id_did = isset($_POST['id']) ? json_decode($_POST['id']): null;
		$id_user = Yii::app()->session['id_user'];

		$did = Did::model()->findByPk($id_did);

		$user= User::model()->findByPK(Yii::app()->session['id_user']);

		$totalDid = $did->fixrate + $did->connection_charge;
		
		if($user->credit < $totalDid )
			$this->msgSuccess = 'you not have credit';		
		elseif($did->reserved == 1){
			$this->msgSuccess = 'Did already active';
		}
		else{			
			if($user->id_user == 1)//se for cliente do master
			{
				$sql = "UPDATE pkg_did SET id_user = $id_user, reserved = 1 WHERE id = :id_did";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(':id_did', $id_did, PDO::PARAM_INT);
				$command->execute();


				//discount credit of customer
				$priceDid = $did->connection_charge + $did->fixrate;


				$sql = "UPDATE pkg_user SET credit = credit -  :priceDid WHERE id = :id_user";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(':id_user', $id_user, PDO::PARAM_INT);
				$command->bindValue(':priceDid', $priceDid, PDO::PARAM_STR);
				$command->execute();

				$fields = "id_user, id_did, status, month_payed";
				$values = ":id_user, :id_did, 1, 1";
				$sql = "INSERT INTO pkg_did_use ($fields) VALUES ($values)";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(':id_user', $id_user, PDO::PARAM_INT);
				$command->bindValue(':id_did', $id_did, PDO::PARAM_INT);
				$command->execute();


				$fields = "id_user, id_did, destination, priority, voip_call";
				$values = ":id_user, :id_did, :destination, 1, 1";
				$sql = "INSERT INTO pkg_did_destination ($fields) VALUES ($values)";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(':id_user', $id_user, PDO::PARAM_INT);
				$command->bindValue(':id_did', $id_did, PDO::PARAM_INT);
				$command->bindValue(':destination', 'SIP/'.$user->username, PDO::PARAM_STR);
				$command->execute();

				if ($priceDid > 0)// se tiver custo
				{
					//adiciona a recarga e pagamento do 1º mes
	 				$credit      = '-'.$did->fixrate;
					$description = Yii::t('yii','Monthly payment Did'). ' '.$did->did;
					

					$fields = "id_user, credit, description, payment";
					$values = ":id_user, :credit, :description, 1";
					$sql = "INSERT INTO pkg_refill ($fields) VALUES ($values)";
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(':id_user', $id_user, PDO::PARAM_INT);
					$command->bindValue(':credit', $credit, PDO::PARAM_STR);
					$command->bindValue(':description', $description, PDO::PARAM_STR);
					$command->execute();


					//adiciona a recarga e pagamento do custo de ativaçao
					if ($did->connection_charge > 0)
					{
	 					$credit      = '-'.$did->connection_charge;
						$description = Yii::t('yii','Activation Did'). ' '.$did->did;
						
						$fields = "id_user, credit, description, payment";
						$values = ":id_user, :credit, :description, 1";
						$sql = "INSERT INTO pkg_refill ($fields) VALUES ($values)";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':id_user', $id_user, PDO::PARAM_INT);
						$command->bindValue(':credit', $credit, PDO::PARAM_STR);
						$command->bindValue(':description', $description, PDO::PARAM_STR);
						$command->execute();

					}					

					$mail = new Mail(Mail :: $TYPE_DID_CONFIRMATION, $id_user);
					$mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $user->credit);
					$mail->replaceInEmail(Mail::$DID_NUMBER_KEY, $did->did);
					$mail->replaceInEmail(Mail::$DID_COST_KEY, '-'.$did->fixrate);
					$mail->send();
				}

				$success = true;
				$this->msgSuccess = 'The Did is activated for you.';
			}
			else
			{
				$this->msgSuccess = 'Not allow ' .$user->id_user;
			}						
		}

		echo json_encode(array(
			$this->nameSuccess => $success,
			$this->nameMsg => $this->msgSuccess
		));
	}


	public function actionRead()
	{		
		//altera o sort se for a coluna username.
		if (isset($_GET['sort']) && $_GET['sort'] === 'username')
			$_GET['sort'] = 'id_user';

		parent::actionRead();
	}

	public function actionLiberar()
	{
		
		if (isset($_POST['id'])) {
			
			$id = json_decode($_POST['id']);			

			$sql = "UPDATE pkg_did SET reserved = 0, id_user = NULL WHERE id = :id";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(':id', $id, PDO::PARAM_INT);
			$command->execute();

			$sql = "DELETE FROM pkg_did_destination WHERE id_did = :id_did";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(':id_did', $id, PDO::PARAM_INT);
			$command->execute();

			$sql = "UPDATE pkg_did_use SET releasedate = '".date('Y-m-d H:i:s')."', status = 0  WHERE 
							id_did = :id_did AND releasedate = '0000-00-00 00:00:00' AND status = 1";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(':id_did', $id, PDO::PARAM_INT);
			$command->execute();

			echo json_encode(array(
				$this->nameSuccess => true,
				$this->nameMsg => $this->msgSuccess
			));
		}else{
			echo json_encode(array(
				$this->nameSuccess => false,
				$this->nameMsg => 'Did not selected'
			));
		}

	}

	public function actionImportFromCsv()
	{
		$values = $this->getAttributesRequest();

		if (Yii::app()->getSession()->get('user_type')  == 2)
		{
			echo json_encode(array(
			$this->nameSuccess => false,
			$this->nameMsg => $this->msgError
			));
			exit;
		}

		$handle = fopen($_FILES['file']['tmp_name'], "r");

		$sql = array();

		while($row = fgetcsv($handle))
		{
			if(isset($row[0]))
			{
				$number = trim($row[0]);
				$fixrate = isset($row[1]) ? trim($row[1]) : '0';
				$connection = isset($row[2]) ? trim($row[2]) : '0';

				$sql[] = "($number, $fixrate, '$connection')";
			}
		}
		SqlInject::sanitize($sql);
		$sql = 'INSERT INTO pkg_did (did,  fixrate, connection_charge) VALUES '.implode(',', $sql).';';
		try {
			$this->success = $result = Yii::app()->db->createCommand($sql)->execute();
		}
		catch (Exception $e) {
			$this->success = false;
			$errors = $this->getErrorMySql($e);
		}



		if(isset($row[1]))
		{
			fclose($handle);
			$this->msg = $this->success ? $this->msgSuccess : $errors;
		}
		else
		{
			$this->success = true;
			$this->msg = 'Operacao realizada com sucesso.';
		}


		echo json_encode(array(
			$this->nameSuccess => $this->success,
			$this->nameMsg => $this->msg
		));
	}
}