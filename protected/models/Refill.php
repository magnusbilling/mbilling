<?php
/**
 * Modelo para a tabela "pkg_ui_authen".
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2016 MagnusBilling. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v3
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 25/06/2012
 */

class Refill extends Model
{
	protected $_module = 'refill';
	var $sumCredit;
	var $sumCreditMonth;
	var $CreditMonth;
	/**
	 * Retorna a classe estatica da model.
	 * @return Admin classe estatica da model.
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return nome da tabela.
	 */
	public function tableName()
	{
		return 'pkg_refill';
	}

	/**
	 * @return nome da(s) chave(s) primaria(s).
	 */
	public function primaryKey()
	{
		return 'id';
	}

	/**
	 * @return array validacao dos campos da model.
	 */
	public function rules()
	{
		return array(
			array('id_user', 'required'),
			array('payment', 'numerical', 'integerOnly'=>true),
			array('credit', 'numerical', 'integerOnly'=>false),
			array('description, invoice_number', 'length', 'max'=>500)
		);
	}
	/*
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idUser' => array(self::BELONGS_TO, 'User', 'id_user'),
		);
	}


	public function beforeSave()
	{	

		if(isset($_SESSION['isAgent']) && $_SESSION['isAgent'] == true)
		{
			$sql = "SELECT SUM(credit) AS totalCredit FROM pkg_user WHERE id_user =  :id_user ";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $_SESSION['id_user'], PDO::PARAM_STR);
			$result = $command->queryAll();

			$totalRefill = $result[0]['totalCredit'] + $this->credit;

			$sql = "SELECT id_user, credit, creditlimit, typepaid   FROM pkg_user WHERE id = :id";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", $_SESSION['id_user'], PDO::PARAM_STR);
			$result = $command->queryAll();

			$userAgent =  $result[0]['typepaid'] == 1 ? $result[0]['credit'] = $result[0]['credit'] + $result[0]['creditlimit'] : $result[0]['credit'];


			$config = LoadConfig::getConfig();			
			$maximunCredit = $config["global"]['agent_limit_refill'] * $userAgent;	
			//Yii::log("$totalRefill > $maximunCredit", 'info');
			if( $totalRefill > $maximunCredit ){
				$limite = $maximunCredit - $totalRefill;
				echo json_encode(array(
					'success' => false,
					'rows' => array(),
					'errors' => Yii::t('yii','Limit refill exceeded, your limit is '.$maximunCredit. '. You have ' .$limite. 'to refill')
				));
				exit;
			}
		}

		if($this->getIsNewRecord())
		{
			$resultCard = User::model()->findByPk($this->id_user);
			$creditOld = $resultCard->credit;
			$this->description = $this->description.', '.Yii::t('yii','Old credit').' '.round($creditOld, 2);

			//add credit
			$resultCard->credit = $this->credit > 0 ? $resultCard->credit + $this->credit : $resultCard->credit - ($this->credit * -1);
			$resultCard->saveAttributes(array('credit'=>$resultCard->credit));			
			
		}	

		return parent::beforeSave();
	}

	public function afterSave()
	{	
		if($this->getIsNewRecord())
		{
			
			$mail = new Mail(Mail::$TYPE_REFILL, $this->id_user);
			$mail->replaceInEmail(Mail::$ITEM_ID_KEY, $this->id);
			$mail->replaceInEmail(Mail::$ITEM_AMOUNT_KEY, $this->credit);
			$mail->replaceInEmail(Mail::$DESCRIPTION, $this->description);
			$mail->send();
			
		}	
		return parent::afterSave();
	}

}