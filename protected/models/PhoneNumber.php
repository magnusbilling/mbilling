<?php
/**
 * Modelo para a tabela "PhoneNumber".
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
 * 28/10/2012
 */

class PhoneNumber extends Model
{
	protected $_module = 'phonenumber';
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
		return 'pkg_phonenumber';
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
			array('id_phonebook, status, try', 'numerical', 'integerOnly'=>true),
			array('name, city', 'length', 'max'=>40),
			array('number', 'length', 'max'=>30),
			array('info', 'length', 'max'=>200)
			);
	}

	/**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idPhonebook' => array(self::BELONGS_TO, 'PhoneBook', 'id_phonebook')
		);
	}

	public function beforeSave()
	{
		if ($this->status == 1)
			$this->try = 0;

		return parent::beforeSave();
	}

	public function afterSave()
	{
		return parent::afterSave();
	}
}