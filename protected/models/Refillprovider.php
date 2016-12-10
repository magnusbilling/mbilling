<?php
/**
 * Modelo para a tabela "Refillprovider".
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
 * 18/07/2012
 */

class Refillprovider extends Model
{
	protected $_module = 'refillprovider';
	/**
	 * Retorna a classe estatica da model.
	 * @return Refilltrunk classe estatica da model.
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
		return 'pkg_refill_provider';
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
			array('credit, id_provider', 'required'),
			array('id_provider, payment', 'numerical', 'integerOnly'=>true),
            array('description', 'length', 'max'=>500)

		);
	}
    /**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idProvider' => array(self::BELONGS_TO, 'Provider', 'id_provider'),
		);
	}

	public function afterSave()
	{

		$values = isset($_POST['rows']) ? json_decode($_POST['rows']): null;

		if($this->getIsNewRecord())
		{
			$resultProvider = Provider::model()->findByPk($this->id_provider);
			$creditOld = $resultProvider->credit;
			$this->description = $this->description.', '.Yii::t('yii','Old credit').' '.round($creditOld, 2);

			//add credit
			$resultProvider->credit = $this->credit > 0 ? $resultProvider->credit + $this->credit : $resultProvider->credit - ($this->credit * -1);
			$resultProvider->saveAttributes(array('credit'=>$resultProvider->credit));

		}
		return parent::afterSave();
	}

}