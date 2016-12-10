<?php
/**
 * Modelo para a tabela "Payment".
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
 * 25/06/2012
 */

class Payment extends Model
{
	protected $_module = 'payment';
	/**
	 * Retorna a classe estatica da model.
	 * @return Payment classe estatica da model.
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
		return 'pkg_payment';
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
			array('payment, id_user, id_logrefill', 'required'),           
            array('description', 'length', 'max'=>500),                  
		);
	}
	
	/*
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
		return parent::beforeSave();
	}

	public function afterSave()
	{
		$mail = new Mail(Mail::$TYPE_PAYMENT, $this->id_user);
		$mail->replaceInEmail(Mail::$ITEM_ID_KEY, $this->id_logrefill);
		$mail->replaceInEmail(Mail::$ITEM_AMOUNT_KEY, $this->payment);
		$mail->replaceInEmail(Mail::$DESCRIPTION, $this->description);
		$mail->send();

		return parent::afterSave();
	}
    
}