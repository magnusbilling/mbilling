<?php
/**
 * Modelo para a tabela "Diddestination".
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
 * 24/09/2012
 */

class Diddestination extends Model
{
	protected $_module = 'diddestination';

	/**
	 * Retorna a classe estatica da model.
	 * @return Prefix classe estatica da model.
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
		return 'pkg_did_destination';
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
			array('id_user, id_queue, id_sip, id_ivr, id_did, priority, activated, secondusedreal, voip_call', 'numerical', 'integerOnly'=>true),
			array('destination', 'length', 'max'=>120),
		);
	}

	/**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idDid' => array(self::BELONGS_TO, 'Did', 'id_did'),
			'idUser' => array(self::BELONGS_TO, 'User', 'id_user'),
			'idIvr' => array(self::BELONGS_TO, 'Ivr', 'id_ivr'),
			'idQueue' => array(self::BELONGS_TO, 'Queue', 'id_queue'),
			'idSip' => array(self::BELONGS_TO, 'Sip', 'id_sip')
		);
	}

	public function beforeSave()
	{
		$this->voip_call = isset($this->voip_call) ? $this->voip_call : 1;

		if($this->isNewRecord)
		{
			$did = Did::model()->findByPk($this->id_did);

			$sql = "SELECT id_user_type FROM pkg_group_user WHERE id = (SELECT id_group FROM pkg_user WHERE id = :id_user)";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $this->id_user, PDO::PARAM_STR);
			$resultGroup = $command->queryAll();

			if (isset($resultGroup[0]['id_user_type']) && $resultGroup[0]['id_user_type'] != 3) {
				echo json_encode(array(
					'success' => false,
					'rows' => '[]',
					'errors' => Yii::t('yii','You only can set DID to CLIENTS')
				));
				exit;
			}

			if ($did->reserved == 0)
			{
				$priceDid = $did->connection_charge + $did->fixrate;

				$user = User::model()->findByPk($this->id_user);
				$user->credit = $user->credit + $user->creditlimit;
				if ($user->credit < $priceDid)
				{
					echo json_encode(array(
						'success' => false,
						'rows' => '[]',
						'errors' => Yii::t('yii','Customer not have credit for buy Did'). ' - '.$did->did
					));
					exit;
				}
			}
		}

		return parent::beforeSave();
	}

	public function afterSave()
	{
		if($this->isNewRecord)
		{
			$did = Did::model()->findByPk($this->id_did);
			if ($did->id_user == NULL && $did->reserved == 0)//se for ativaçao adicionar o pagamento e cobrar
			{
				$did->reserved = 1;
				$did->id_user = $this->id_user;
				$did->save();

				//discount credit of customer
				$priceDid = $did->connection_charge + $did->fixrate;

				if ($priceDid > 0)// se tiver custo
				{

					$user = User::model()->findByPk($this->id_user);

					if($user->id_user == 1)//se for cliente do master
					{
						//adiciona a recarga e pagamento do custo de ativaçao
						if ($did->connection_charge > 0)
						{
							$refill              = new Refill;
							$refill->id_user     = $this->id_user;
							$refill->credit      = '-'.$did->connection_charge;
							$refill->description = Yii::t('yii','Activation Did'). ' '.$did->did;
							$refill->payment     = 1;
							$refill->save();
						}

						//adiciona a recarga e pagamento do 1º mes
						$refill              = new Refill;
						$refill->id_user     = $this->id_user;
						$refill->credit      = '-'.$did->fixrate;
						$refill->description = Yii::t('yii','Monthly payment Did'). ' '.$did->did;
						$refill->payment     = 1;
						$refill->save();

						$mail = new Mail(Mail :: $TYPE_DID_CONFIRMATION, $this->id_user);
						$mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $user->credit);
						$mail->replaceInEmail(Mail::$DID_NUMBER_KEY, $did->did);
						$mail->replaceInEmail(Mail::$DID_COST_KEY, '-'.$did->fixrate);
						$mail->send();
					}
					else
					{
						$user = User::model()->findByPk($this->user);
						$user->credit = $user->credit - $priceDid;
						$user->save();						
					}
				}

				//adiciona a recarga e pagamento
				$use = new DidUse;
				$use->id_user = $this->id_user;
				$use->id_did = $this->id_did;
				$use->status = 1;
				$use->month_payed = 1;
				$use->save();


				$config = LoadConfig::getConfig();
				if(isset($mail))
					$sendAdmin = $config['global']['admin_received_email'] == 1 ? $mail->send($config['global']['admin_email']) : NULL;
			}
		}
		return parent::afterSave();
	}

}