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
class Sipuras extends Model
{
	protected $_module = 'sipuras';
	/**
	 * Retorna a classe estatica da model.
	 * @return Sipuras classe estatica da model.
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
		return 'pkg_sipura';
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
			array('macadr, id_user', 'required'),
            array('macadr', 'length', 'max'=>12),
            array('senha_admin, senha_user, Use_Pref_Codec_Only_1, Use_Pref_Codec_Only_2, Preferred_Codec_1, Preferred_Codec_2', 'length', 'max'=>8),
            array('antireset, altera, Enable_Web_Server, STUN_Enable, 
            	NAT_Keep_Alive_Enable_1_, NAT_Keep_Alive_Enable_2_, NAT_Mapping_Enable_1_, 
            	NAT_Mapping_Enable_2_, STUN_Test_Enable, Substitute_VIA_Addr', 'length', 'max'=>3),
            array('lastmov', 'length', 'max'=>20),
            array('marca', 'length', 'max'=>2),
            array('obs', 'length', 'max'=>50),
            array('Proxy_1, Proxy_2', 'length', 'max'=>60),
            array('last_ip, nserie', 'length', 'max'=>15),
            array('Register_Expires_1, Register_Expires_2', 'length', 'max'=>4),
            array('fultmov', 'length', 'max'=>30),
            array('User_ID_1, User_ID_2, Password_1, Password_2', 'length', 'max'=>25),
            array('STUN_Server', 'length', 'max'=>80),
            array('Dial_Plan_1, Dial_Plan_2', 'length', 'max'=>180)
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
		$config = LoadConfig::getConfig();
		$this->Proxy_1 = $this->Proxy_2 = $config['global']['ip_servers'];
		$this->altera = 'si';
		return parent::beforeSave();
	}

	public function afterSave()
	{
		return parent::afterSave();
	}
}