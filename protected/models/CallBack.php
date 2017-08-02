<?php
/**
 * Modelo para a tabela "CallBack".
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

class CallBack extends Model
{
	protected $_module = 'callback';
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
		return 'pkg_callback';
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
			array('id_user,num_attempt, id_server_group,id_did', 'numerical', 'integerOnly'=>true),
			array('uniqueid, server_ip', 'length', 'max'=>40),
            	array('status, callerid', 'length', 'max'=>10),
            	array('channel, exten, account, context, timeout, priority', 'length', 'max'=>60),
            	array('variable', 'length', 'max'=>300)            
		);
	}

	/**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idUser' => array(self::BELONGS_TO, 'User', 'id_user'),
			'idDid' => array(self::BELONGS_TO, 'Did', 'id_did')
		);
	}


	public function beforeSave()
	{
		if($this->getIsNewRecord())
		{
			$config = LoadConfig::getConfig();

			$modelUser = User::model()->findByPk((int) $this->id_user);		
			$this->exten = $this->number_translation( $modelUser->prefix_local, $this->exten, 
								$config['global']['base_country']);	
		}
		return parent::beforeSave();
	}

	public function afterSave()
	{
		return parent::afterSave();
	}
}