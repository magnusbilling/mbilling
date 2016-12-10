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

class QueueMemberDashBoard extends Model
{

	protected $_module = 'dashboardqueue';
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
		return 'pkg_queue_agent_status';
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
			array('id_queue id_user', 'numerical', 'integerOnly'=>true),
			array('agentStatus, callId', 'length', 'max'=>25)
		);
	}

	/*
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idQueue' => array(self::BELONGS_TO, 'Queue', 'id_queue'),
		);
	}

}