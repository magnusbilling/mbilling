<?php
/**
 * Modelo para a tabela "Rate".
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
 * 30/07/2012
 */

class Rate extends Model
{
	protected $_module = 'rate';
	/**
	 * Retorna a classe estatica da model.
	 * @return Rate classe estatica da model.
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
		$startSession = strlen(session_id()) < 1 ? session_start() : null;
		if(Yii::app()->session['isAgent'] || Yii::app()->session['id_agent'] > 1)
			return 'pkg_rate_agent';
		else
			return 'pkg_rate';
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
		if(Yii::app()->session['isAgent'])
		{
			return array(
	            array('id_plan', 'required'),
	            array('id_plan, id_prefix, initblock, billingblock, minimal_time_charge', 'numerical', 'integerOnly'=>true),
	            array('rateinitial', 'length', 'max'=>15),
			);
		}
		else
		{
			return array(
	            array('id_plan', 'required'),
	            array('id_plan, id_prefix, id_trunk, buyrateinitblock, buyrateincrement, initblock, billingblock, package_offer, minimal_time_charge, minimal_time_buy', 'numerical', 'integerOnly'=>true),
	            array('buyrate, rateinitial, additional_grace,status', 'length', 'max'=>15),
			);
		}

	}
	/**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		if(Yii::app()->session['isAgent'])
		{
			return array(
			'idPlan' => array(self::BELONGS_TO, 'Plan', 'id_plan'),
			'idPrefix' => array(self::BELONGS_TO, 'Prefix', 'id_prefix'),
			);
		}
		else
		{
			return array(
			'idTrunk' => array(self::BELONGS_TO, 'Trunk', 'id_trunk'),
			'idPlan' => array(self::BELONGS_TO, 'Plan', 'id_plan'),
			'idPrefix' => array(self::BELONGS_TO, 'Prefix', 'id_prefix'),
			);
		}
	}
}