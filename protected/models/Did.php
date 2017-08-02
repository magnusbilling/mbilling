<?php
/**
 * Modelo para a tabela "Did".
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
 * 24/09/2012
 */

class Did extends Model
{
	protected $_module = 'did';
	public $username;
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
		return 'pkg_did';
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
			array('did', 'required'),
            	array('minimal_time_charge, charge_of, block_expression_1, block_expression_2,block_expression_3, initblock, increment, id_user, cbr_em, activated, reserved, secondusedreal, billingtype,
            		send_to_callback_1,send_to_callback_3,send_to_callback_3,cbr,cbr_ua', 'numerical', 'integerOnly'=>true),
            	array('fixrate', 'numerical'),
            	array('did', 'length', 'max'=>50),
            	array('expression_1, expression_2,expression_2,TimeOfDay_monFri,TimeOfDay_sat,TimeOfDay_sun,workaudio,noworkaudio', 'length', 'max'=>150),
            	array('connection_charge, selling_rate_1, selling_rate_2,selling_rate_3, connection_sell', 'length', 'max'=>15),
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

	public function afterSave()
	{
		if (isset($_FILES["workaudio"]) && strlen($_FILES["workaudio"]["name"]) > 1) 
		{
			$uploaddir = "resources/sounds/";
			if (file_exists($uploaddir .'idDidAudioProWork_'. $this->id.'.wav')) {
				unlink($uploaddir .'idDidAudioProWork_'. $this->id.'.wav');
			}
			$typefile = explode('.', $_FILES["workaudio"]["name"]);
			$uploadfile = $uploaddir .'idDidAudioProWork_'. $this->id .'.'. $typefile[1];
			move_uploaded_file($_FILES["workaudio"]["tmp_name"], $uploadfile);
		}

		if (isset($_FILES["noworkaudio"]) && strlen($_FILES["noworkaudio"]["name"]) > 1) 
		{
			$uploaddir = "resources/sounds/";
			if (file_exists($uploaddir .'idDidAudioProNoWork_'. $this->id.'.wav')) {
				unlink($uploaddir .'idDidAudioProNoWork_'. $this->id.'.wav');
			}
			$typefile = explode('.', $_FILES["noworkaudio"]["name"]);
			$uploadfile = $uploaddir .'idDidAudioProNoWork_'. $this->id .'.'. $typefile[1];
			move_uploaded_file($_FILES["noworkaudio"]["tmp_name"], $uploadfile);
		}

		return parent::afterSave();
	}

	public function beforeSave()
	{

		if (isset($_FILES["workaudio"]) && strlen($_FILES["workaudio"]["name"]) > 1)
		{
			$typefile = explode('.', $_FILES["workaudio"]["name"]);
			$this->workaudio = "resources/sounds/idDidAudioProWork_".$this->id .'.'. $typefile[1];
		}

		if (isset($_FILES["noworkaudio"]) && strlen($_FILES["noworkaudio"]["name"]) > 1)
		{
			$typefile = explode('.', $_FILES["noworkaudio"]["name"]);
			$this->noworkaudio = "resources/sounds/idDidAudioProNoWork_".$this->id .'.'. $typefile[1];
		}

		$this->id_user = $this->getIsNewRecord() && Yii::app()->session['isAdmin'] ? NULL : $this->id_user;
		$this->startingdate = date('Y-m-d H:i:s');
		$this->expirationdate = '2030-08-21 00:00:00';
		$this->selling_rate_1 = $this->selling_rate_1 == '' ? '0.0000' : $this->selling_rate_1;
		$this->selling_rate_2 = $this->selling_rate_2 == '' ? '0.0000' : $this->selling_rate_2;
		$this->selling_rate_3 = $this->selling_rate_3 == '' ? '0.0000' : $this->selling_rate_3;
		return parent::beforeSave();
	}
}