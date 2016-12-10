<?php
/**
 * Modelo para a tabela "Rate".
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
 * 30/07/2012
 */

class RateCallshop extends Model
{
	protected $_module = 'ratecallshop';
	/**
	 * Retorna a classe estatica da model.
	 * @return Rate classe estatica da model.
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
		return 'pkg_rate_callshop';
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
            //array('dialprefix', 'required'),
            array('id_user, minimo, block', 'numerical', 'integerOnly'=>true),
            array('dialprefix, destination', 'length', 'max'=>30),
            array('buyrate', 'length', 'max'=>15),
		);
	}


	public function beforeSave()
	{
		return parent::beforeSave();
	}

	public function afterSave()
	{
		return parent::afterSave();
	}
}