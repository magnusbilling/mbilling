<?php
/**
 * Modelo para a tabela "Queue".
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

class QueueMember extends Model
{
	protected $_module = 'queuemember';
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
		return 'pkg_queue_member';
	}

	/**
	 * @return nome da(s) chave(s) primaria(s).
	 */
	public function primaryKey()
	{
		return 'uniqueid';
	}

	/**
	 * @return array validacao dos campos da model.
	 */
	public function rules()
	{
		return array(
			array('interface, id_user', 'required'),
			array('id_user, paused', 'numerical', 'integerOnly'=>true),
			array('membername', 'length', 'max'=>40),
			array('queue_name, interface', 'length', 'max'=>128)
		);
	}

	/**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idUser' => array(self::BELONGS_TO, 'User', 'id_user')
		);
	}

	public function beforeSave()
	{
		if (isset($this->interface)) {
			$sql = "SELECT * FROM pkg_sip WHERE id = :id OR name = :id ";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", preg_replace("/SIP\//", '', $this->interface), PDO::PARAM_STR);
			$result = $command->queryAll();

			$this->id_user = $result[0]['id_user'];
			$this->interface = 'SIP/'.$result[0]['name'];
		}
		if (isset($this->queue_name)) {
			$sql = "SELECT * FROM pkg_queue WHERE id = :id OR name = :id ";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", $this->queue_name, PDO::PARAM_STR);
			$result = $command->queryAll();
			$this->queue_name = $result[0]['name'];
		}

		return parent::beforeSave();
	}
}