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
 	public $accept_terms;


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
			array('username,password,lastname, firstname, email, city, state, phone, id_plan, id_user', 'required'),
			array('phone, zipcode, vat, mobile, calllimit, active', 'numerical'),
			array('password, password2', 'length', 'min'=>6),
			array('lastname,firstname, city, country', 'length', 'min'=>4),
			array('state', 'length', 'min'=>2),
			array('zipcode', 'length', 'min'=>8),
			array('doc', 'length', 'min'=>11),			
			array('username', 'length', 'min'=>6),
			array('username', 'checkusername' ),			
			array('password', 'checksecret' ),			
			array('doc', 'checkdoc' ),
			array('state_number', 'length', 'max'=>40),
			array('address', 'length', 'max'=>100),
			array('mobile, phone,', 'length', 'min'=>10),
			array('email', 'checkemail'),
			array('email', 'unique'),
			array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),
			array('password', 'compare', 'compareAttribute'=>'password2'),
			array('accept_terms', 'required', 'requiredValue' => 1, 'message' => 'You must accept the Terms and Conditons in order to register.'), 
		);
	}

	public function checkemail( $attribute, $params ) {
		if (!filter_var($this->email, FILTER_VALIDATE_EMAIL))
			$this->addError( $attribute, Yii::t( 'yii', 'Invalid Email' ) );
	}

	public function checkdoc($attribute, $params)
	{
		$signup=new Signup();
		$config = LoadConfig::getConfig();
		if ($config['global']['base_language'] == 'pt_BR') {
		
			$cpf_cnpj = new ValidaCPFCNPJ($this->doc);
			// Opção de CPF ou CNPJ formatado no padrão
			$formatado = $cpf_cnpj->formata();

			// Verifica se o CPF ou CNPJ é válido
			if ( $formatado )
				$this->doc = $formatado;
			else
				$this->addError( $attribute, Yii::t( 'yii', 'CPF ou CNPJ Inválido' ) );		
		}
	}
	public function checkusername( $attribute, $params ) {
		if ( preg_match( '/ /', $this->username ) )
			$this->addError( $attribute, Yii::t( 'yii', 'No space allow in username' ) );
		if ( !preg_match( '/^[1-9]|^[A-Z]|^[a-z]/', $this->username ) )
			$this->addError( $attribute, Yii::t( 'yii', 'Username need start with numbers or letters' ) );
	}
	public function checksecret( $attribute, $params ) {
		if ( preg_match( '/ /', $this->password ) )
			$this->addError( $attribute, Yii::t( 'yii', 'No space allow in password' ) );
		if (  $this->password == '123456' ||  $this->password == '12345678' ||  $this->password == '012345')
			$this->addError( $attribute, Yii::t( 'yii', 'No use sequence in the pasword' ) );
		if (  $this->password == $this->username)
			$this->addError( $attribute, Yii::t( 'yii', 'Password cannot be equal username' ) );
	}

	public function beforeSave()
	{
		$this->company_name = strtoupper($this->company_name);
		$this->state_number = strtoupper($this->state_number);
		$this->city = strtoupper($this->city);
		$this->address = strtoupper($this->address);
		return parent::beforeSave();
	}
 }
 ?>