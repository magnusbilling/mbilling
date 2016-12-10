<?php
/**
 * Modelo para a tabela "Alarm".
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
 * 17/08/2012
 */

class CallShop extends Model
{
	protected $_module = 'callshop';
	public $priceSum;
	public $callshopdestination;
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
		return 'pkg_sip';
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
			array('id_user', 'required'),
			array('id_user, calllimit', 'numerical', 'integerOnly'=>true),
            array('name, callerid, context, fromuser, fromdomain, md5secret, secret, fullcontact', 'length', 'max'=>80),
            array('regexten, insecure, regserver, vmexten, callingpres, mohsuggest, allowtransfer, callshoptime', 'length', 'max'=>20),
            array('amaflags, dtmfmode, qualify', 'length', 'max'=>7),
            array('callgroup, pickupgroup, auth, subscribemwi, usereqphone, autoframing', 'length', 'max'=>10),
            array('DEFAULTip, accountcode, ipaddr, maxcallbitrate, rtpkeepalive', 'length', 'max'=>15),
            array('host', 'length', 'max'=>31),
            array('language', 'length', 'max'=>2),
            array('mailbox', 'length', 'max'=>50),
            array('nat, rtptimeout, rtpholdtimeout', 'length', 'max'=>3),
            array('deny, permit', 'length', 'max'=>95),
            array('port', 'length', 'max'=>5),
            array('type', 'length', 'max'=>6),
            array('disallow, allow, setvar, useragent', 'length', 'max'=>100),
            array('lastms', 'length', 'max'=>11),
            array('defaultuser, cid_number, outboundproxy', 'length', 'max'=>40),
		);
	}

	/**
     * @return array relational rules.
     */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
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
		return parent::afterSave();
	}
}