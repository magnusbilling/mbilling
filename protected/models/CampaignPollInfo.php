<?php
/**
 * Modelo para a tabela "CampaignPollInfo".
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

class CampaignPollInfo extends Model
{
	protected $_module = 'campaignpollinfo';
	var $sumresposta;
	var $resposta2;
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
		return 'pkg_campaign_poll_info';
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
			array('id_campaign_poll', 'numerical', 'integerOnly'=>true),
			array('number', 'length', 'max'=>18),
			array('obs, resposta', 'length', 'max'=>200)            
          );
	}

	/**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idCampaignPoll' => array(self::BELONGS_TO, 'CampaignPoll', 'id_campaign_poll')
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