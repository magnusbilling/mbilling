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

class UserRate extends Model
{
	protected $_module = 'userrate';
	protected $newPassword = NULL;
	/**
	 * Return the static class of model.
	 * @return User classe estatica da model.
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
		return 'pkg_user_rate';
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
			array('id_user, id_prefix', 'required'),
			array('id_user, id_prefix, initblock, billingblock', 'numerical', 'integerOnly'=>true),
			array('rateinitial', 'length', 'max'=>10)

		);
	}


	/**
	 * @return array roles of relationship.
	 */
	public function relations()
	{
		return array(
			'idPrefix' => array(self::BELONGS_TO, 'Prefix', 'id_prefix'),
			'idUser' => array(self::BELONGS_TO, 'User', 'id_user')
		);
	}


}
?>