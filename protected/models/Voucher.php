<?php
/**
 * Modelo para a tabela "Voucher".
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
 * 20/09/2012
 */

class Voucher extends Model
{
	protected $_module = 'voucher';
	/**
	 * Retorna a classe estatica da model.
	 * @return Payment classe estatica da model.
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
		return 'pkg_voucher';
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
			array('voucher', 'required'),
            array('used, id_user, id_plan', 'numerical', 'integerOnly'=>true),
            array('credit', 'numerical'),
            array('voucher, tag, language, prefix_local', 'length', 'max'=>50),
            array('usedate, expirationdate', 'safe')
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

		if($this->used == 0)
		{
			$this->usedate = '0000-00-00 00:00:00';
		}
		else if($this->used == 1)
		{
			$success = false;
			$msg = 'Não é possível usar esta opção';
			# retorna o resultado da execucao
			echo json_encode(array(
				'success' => $success,
				'msg' => $msg
			));
			exit;
		}
		if($this->getIsNewRecord()){
			$this->id_user = NULL;
		}
		return parent::beforeSave();
    }

	public function afeterSave()
	{
		return parent::afeterSave();
	}
}