<?php
/**
 * Modelo para a tabela "Ivr".
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

class Ivr extends Model
{
	protected $_module = 'ivr';
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
		return 'pkg_ivr';
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
			array('id_user', 'numerical', 'integerOnly'=>true),
			array('monFriStart, monFriStop, satStart, satStop, sunStart, sunStop', 'length', 'max'=>5),
			array('name, option_0, option_1, option_2, option_3, option_4, option_5, option_6, option_7, option_8, option_9, option_10', 'length', 'max'=>50),
			array('option_out_0, option_out_1, option_out_2, option_out_3, option_out_4, option_out_5, option_out_6, option_out_7, option_out_8, option_out_9, option_out_10', 'length', 'max'=>50)
		);
	}

	/**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idUser' => array(self::BELONGS_TO, 'User', 'id_user'),
		);
	}

	public function beforeSave()
	{
		$values = $_POST;

		Yii::log(print_r($values,true),'info');
	
		for ($i=0; $i <= 10; $i++) {
			
			if (isset($values['type_'.$i]) ){			

				if ($values['type_'.$i] == 'repeat') {
					$this->{'option_'.$i} = 'repeat';
				}
				elseif ($values['type_'.$i] == 'undefined' || $values['type_'.$i] == '') {
					$this->{'option_'.$i} = '';
				}
				elseif (preg_match("/group|number|custom|hangup/", $values['type_'.$i] )) {
					
					$this->{'option_'.$i} = $values['type_'.$i].'|'.$values['extension_'.$i];
				}
				else{
					$this->{'option_'.$i} = $values['type_'.$i].'|'.$values['id_'.$values['type_'.$i].'_'.$i];
				}
				
			}

			if (isset($values['type_out_'.$i])){	

				if ($values['type_out_'.$i] == 'repeat') {
					$this->{'option_out_'.$i} = 'repeat';
				}
				elseif ($values['type_out_'.$i] == 'undefined' || $values['type_out_'.$i] == '') {
					$this->{'option_out_'.$i} = '';
				}
				elseif (preg_match("/group|number|custom|hangup/", $values['type_out_'.$i] )) {
					$this->{'option_out_'.$i} = $values['type_out_'.$i].'|'.$values['extension_out_'.$i];
				}
				else{
					$this->{'option_out_'.$i} = $values['type_out_'.$i].'|'.$values['id_'.$values['type_out_'.$i].'_out_'.$i];
				}
				
			}				

		}
		

		return parent::beforeSave();
	}

	public function afterSave()
	{
		if (isset($_FILES["workaudio"]) && strlen($_FILES["workaudio"]["name"]) > 1) 
		{
			$uploaddir = "resources/sounds/";
			if (file_exists($uploaddir .'idIvrDidWork_'. $this->id.'.wav')) {
				unlink($uploaddir .'idIvrDidWork_'. $this->id.'.wav');
			}
			$typefile = explode('.', $_FILES["workaudio"]["name"]);
			$uploadfile = $uploaddir .'idIvrDidWork_'. $this->id .'.'. $typefile[1];
			move_uploaded_file($_FILES["workaudio"]["tmp_name"], $uploadfile);
		}

		if (isset($_FILES["noworkaudio"]) && strlen($_FILES["noworkaudio"]["name"]) > 1) 
		{
			$uploaddir = "resources/sounds/";
			if (file_exists($uploaddir .'idIvrDidNoWork_'. $this->id.'.wav')) {
				unlink($uploaddir .'idIvrDidNoWork_'. $this->id.'.wav');
			}
			$typefile = explode('.', $_FILES["noworkaudio"]["name"]);
			$uploadfile = $uploaddir .'idIvrDidNoWork_'. $this->id .'.'. $typefile[1];
			move_uploaded_file($_FILES["noworkaudio"]["tmp_name"], $uploadfile);
		}

		return parent::afterSave();
	}

}