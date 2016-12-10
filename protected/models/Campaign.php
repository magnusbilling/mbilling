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

class Campaign extends Model
{
	protected $_module = 'campaign';
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
		return 'pkg_campaign';
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
			array('name, id_user', 'required'),
			array('id_user, digit_authorize, restrict_phone, secondusedreal, enable_max_call, nb_callmade, type, monday, tuesday, wednesday, thursday, friday, saturday, sunday, status, frequency', 'numerical', 'integerOnly'=>true),
			array('name, forward_number, audio, audio_2', 'length', 'max'=>100),
			array('startingdate, expirationdate', 'length', 'max'=>50),
			array('daily_start_time, daily_stop_time', 'length', 'max'=>8),
			array('description', 'length', 'max'=>160),
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
		$values = $_POST;		

		//Yii::log(print_r($_POST,true),'info');

		

		if (isset($values['type_0'])){

			if ($values['type_0'] == 'undefined' || $values['type_0'] == '') {
				$this->{'forward_number'} = '';
			}
			elseif (preg_match("/group|number|custom|hangup/", $values['type_0'] )) {
				
				$this->{'forward_number'} = $values['type_0'].'|'.$values['extension_0'];
			}
			else{
				$this->{'forward_number'} = $values['type_0'].'|'.$values['id_'.$values['type_0'].'_0'];
			}
			
		}

		Yii::log('$this->{forward_number}' . $this->{'forward_number'},'info');

		//only allow edit max complet call, if campaign is inactive
		if($this->status == 1 && !$this->getIsNewRecord() ){
			unset($this->secondusedreal);
		}
		if (isset($_FILES["audio"]) && strlen($_FILES["audio"]["name"]) > 1)
		{
			$typefile = explode('.', $_FILES["audio"]["name"]);
			$this->audio = "resources/sounds/idCampaign_".$this->id .'.'. $typefile[1];
		}

		if (isset($_FILES["audio_2"]) && strlen($_FILES["audio_2"]["name"]) > 1)
		{
			$typefile = explode('.', $_FILES["audio_2"]["name"]);
			$this->audio_2 = "resources/sounds/idCampaign_".$this->id .'_2.'. $typefile[1];
		}
		return parent::beforeSave();
	}


	public function afterSave()
	{

		if (isset($_FILES["audio"]) && strlen($_FILES["audio"]["name"]) > 1)
		{
			$uploaddir = "resources/sounds/";
			if (file_exists($uploaddir .'idCampaign_'. $this->id.'.wav')) {
				unlink($uploaddir .'idCampaign_'. $this->id.'.wav');
			}
			$typefile = explode('.', $_FILES["audio"]["name"]);
			$uploadfile = $uploaddir .'idCampaign_'. $this->id .'.'. $typefile[1];
			move_uploaded_file($_FILES["audio"]["tmp_name"], $uploadfile);
		}
		if (isset($_FILES["audio_2"]) && strlen($_FILES["audio_2"]["name"]) > 1)
		{
			$uploaddir = "resources/sounds/";
			if (file_exists($uploaddir .'idCampaign_'. $this->id.'_2.wav')) {
				unlink($uploaddir .'idCampaign_'. $this->id.'_2.wav');
			}
			$typefile = explode('.', $_FILES["audio_2"]["name"]);
			$uploadfile = $uploaddir .'idCampaign_'. $this->id .'_2.'. $typefile[1];
			move_uploaded_file($_FILES["audio_2"]["tmp_name"], $uploadfile);
		}

		return parent::afterSave();
	}
}