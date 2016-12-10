<?php
/**
 * Modelo para a tabela "Campaign".
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

class CampaignPoll extends Model
{
	protected $_module = 'campaignpoll';
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
		return 'pkg_campaign_poll';
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
			array('name, id_campaign', 'required'),
            array('ordem_exibicao, repeat, id_campaign, digit_authorize, request_authorize', 'numerical', 'integerOnly'=>true),
            array('name, arq_audio', 'length', 'max'=>100),
            array('option0, option1, option2, option3, option4, option5, option6, option7, option8, option9', 'length', 'max'=>30),
            array('description', 'length', 'max'=>300),
            

		);
	}

	/**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idCampaign' => array(self::BELONGS_TO, 'Campaign', 'id_campaign')
		);
	}

	public function beforeSave()
	{
		if (isset($_FILES["arq_audio"]) && strlen($_FILES["arq_audio"]["name"]) > 1)
		{
			$typefile = explode('.', $_FILES["arq_audio"]["name"]);
			$this->arq_audio = "resources/sounds/idPoll_".$this->id .'.'. $typefile[1];
		}
		return parent::beforeSave();
	}

	public function afterSave()
	{

		if (strlen($_FILES["arq_audio"]["name"]) > 1) 
		{	
			$uploaddir = "resources/sounds/";
			if (file_exists($uploaddir .'idPoll_'. $this->id.'.wav')) {
				unlink($uploaddir .'idPoll_'. $this->id.'.wav');
			}
			$typefile = explode('.', $_FILES["arq_audio"]["name"]);
			$uploadfile = $uploaddir .'idPoll_'. $this->id .'.'. $typefile[1];
			move_uploaded_file($_FILES["arq_audio"]["tmp_name"], $uploadfile);
		}

		return parent::afterSave();
	}
}