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
 class Signup extends Model
 {
 	public $verifyCode;
 	public $password2;


 	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return nome da tabela.
	 */
	public function tableName()
	{
		return 'pkg_user';
	}

	/**
	 * @return array validacao dos campos da model.
	 */
	public function rules()
	{	
		
		return array(
			array('password,lastname, firstname, email, city, state, phone, id_plan, id_user', 'required'),
			array('phone, zipcode, vat, mobile', 'numerical'),
			array('password, password2', 'length', 'min'=>8),
			array('lastname,zipcode, firstname, city, country', 'length', 'min'=>4),
			array('state', 'length', 'min'=>2),
			array('doc', 'length', 'min'=>11),
			array('address', 'length', 'max'=>40),
			array('mobile, phone', 'length', 'min'=>10),
			array('email', 'match', 'pattern'=>'/[A-Za-z0-9\\._-]+@[A-Za-z]+\\.[A-Za-z]+/'),
			array('email', 'unique'),
			array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),
			array('password', 'compare', 'compareAttribute'=>'password2'), 
		);
	}

	public function beforeSave()
	{	

		$sql = "SELECT * FROM pkg_method_pay WHERE payment_method = 'SuperLogica' 
								AND active = 1";
		$methodResult = Yii::app()->db->createCommand( $sql )->queryAll();
		if (count($methodResult) > 0) {	

			$response  = SLUserSave::saveUserSLCurl($this,$methodResult[0]['SLAppToken']
										,$methodResult[0]['SLAccessToken'],false);

			if(isset( $response[0]->data->id_sacado_sac))
				$this->id_sacado_sac = $response[0]->data->id_sacado_sac;
		}
		return parent::beforeSave();
	}
 }
 ?>