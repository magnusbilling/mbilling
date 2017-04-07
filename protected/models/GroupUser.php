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
/**
 * Model to table "group_user".
 *
 * Columns of table 'group_user':
 *
 * @property integer $id.
 * @property string $name.
 *
 * Relations of model:
 * @property Module[] $modules.
 * @property GroupModule[] $groupModules.
 * @property User[] $users.
 *
 * MagnusBilling <info@magnusbilling.com>
 * 15/04/2013
 */

class GroupUser extends Model
{
	protected $_module = 'groupuser';
	/**
	 * Return the static class of model.
	 *
	 * @return GroupUser classe estatica da model.
	 */
	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	/**
	 *
	 *
	 * @return name of table.
	 */
	public function tableName() {
		return 'pkg_group_user';
	}

	/**
	 *
	 *
	 * @return name of primary key(s).
	 */
	public function primaryKey() {
		return 'id';
	}

	/**
	 *
	 *
	 * @return array validation of fields of model.
	 */
	public function rules() {
		return array(
			array( 'name', 'required' ),
			array( 'id_user_type', 'numerical', 'integerOnly'=>true ),
			array( 'name', 'length', 'max'=>100 ),
		);
	}

	/**
	 *
	 *
	 * @return array roles of relationship.
	 */
	public function relations() {
		return array(
			'modules' => array( self::MANY_MANY, 'Module', 'group_module(id_group, id_module)' ),
			'groupModules' => array( self::HAS_MANY, 'GroupModule', 'id_group' ),
			'users' => array( self::HAS_MANY, 'User', 'id_group' ),
			'idUserType' => array( self::BELONGS_TO, 'UserType', 'id_user_type' ),
		);
	}
}
?>
