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
 * Model to table "group_module".
 *
 * Columns of table 'group_module':
 *
 * @property integer $id_group.
 * @property integer $id_module.
 *
 * Relations of model:
 * @property GroupUser $idGroup.
 * @property Module $idModule.
 *
 * MagnusBilling <info@magnusbilling.com>
 * 15/04/2013
 */

class GroupModule extends Model
{
	protected $_module = 'groupmodule';

	/**
	 * Return the static class of model.
	 *
	 * @return GroupModule classe estatica da model.
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
		return 'pkg_group_module';
	}

	/**
	 *
	 *
	 * @return name of primary key(s).
	 */
	public function primaryKey() {
		return array( 'id_group', 'id_module' );
	}

	/**
	 *
	 *
	 * @return array validation of fields of model.
	 */
	public function rules() {
		return array(
			array( 'id_group, id_module', 'required' ),
			array( 'id_group, id_module, show_menu', 'numerical', 'integerOnly'=>true ),
			array( 'action', 'length', 'max'=>5 ),
		);
	}

	/**
	 *
	 *
	 * @return array roles of relationship.
	 */
	public function relations() {
		return array(
			'idGroup' => array( self::BELONGS_TO, 'GroupUser', 'id_group' ),
			'idModule' => array( self::BELONGS_TO, 'Module', 'id_module' ),
		);
	}
}
?>
