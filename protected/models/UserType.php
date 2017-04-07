<?php
/**
 * Modelo para a tabela "Boleto".
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
 * 19/09/2012
 */

class UserType extends Model
{
	protected $_module = 'usertype';
	/**
	 * Return the static class of model.
	 * @return GroupUser classe estatica da model.
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return name of table.
	 */
	public function tableName()
	{
		return 'pkg_user_type';
	}

	/**
	 * @return name of primary key(s).
	 */
	public function primaryKey()
	{
		return 'id';
	}

	/**
	 * @return array validation of fields of model.
	 */
	public function rules()
	{
		return array(
			array('name', 'required'),
			array('name', 'length', 'max'=>100),
		);
	}
}
?>