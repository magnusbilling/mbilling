<?php
/**
 * Modelo para a tabela "TemplateMail".
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

class TemplateMail extends Model
{
	protected $_module = 'templatemail';
	/**
	 * Retorna a classe estatica da model.
	 * @return TemplateMail classe estatica da model.
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
		return 'pkg_templatemail';
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
            array('mailtype', 'length', 'max'=>50),
            array('fromname, fromemail, language', 'length', 'max'=>70),
            array('subject', 'length', 'max' => 130),
            array('messagehtml', 'length', 'max' => 3000)
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