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

class Methodpay extends Model
{
	protected $_module = 'methodpay';
	/**
	 * Retorna a classe estatica da model.
	 *
	 * @return GroupUserActionSubModule classe estatica da model.
	 */
	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	/**
	 *
	 *
	 * @return nome da tabela.
	 */
	public function tableName() {
		return 'pkg_method_pay';
	}

	/**
	 *
	 *
	 * @return nome da(s) chave(s) primaria(s).
	 */
	public function primaryKey() {
		return 'id';
	}

	/**
	 *
	 *
	 * @return array validacao dos campos da model.
	 */
	public function rules() {
		return array(
			array( 'id, payment_method, country', 'required' ),
			array( 'active, id_user, fee, SLIdProduto', 'numerical', 'integerOnly'=>true ),
			array( 'obs, client_id, client_secret', 'length', 'max'=>500 ),
			array( 'P2P_tax_amount', 'length', 'max'=>10 ),			
			array( 'P2P_CustomerSiteID, P2P_KeyID, P2P_Passphrase, P2P_RecipientKeyID, username', 'length', 'max'=>100 ),
			array( 'boleto_convenio,boleto_banco,pagseguro_TOKEN,url, show_name, SLvalidationtoken', 'length', 'max'=>100 ),
			array( 'boleto_taxa, boleto_instrucoes, boleto_carteira, boleto_inicio_nosso_numeroa, boleto_conta_corrente, boleto_agencia', 'length', 'max'=>100 ),
			array( 'boleto_estado_emp, boleto_cpf_emp, boleto_cidade_emp, boleto_end_emp, boleto_nome_emp', 'length', 'max'=>100 ),
			array( 'SLAppToken,SLAccessToken,SLSecret', 'length', 'max'=>50 ),
		);
	}

	/**
	 *
	 *
	 * @return array regras de relacionamento.
	 */
	public function relations() {
		return array(
			'idUser' => array( self::BELONGS_TO, 'User', 'id_user' ),
		);
	}


	public function beforeSave() {
		return parent::beforeSave();
	}

	public function afterSave() {
		return parent::afterSave();
	}


}
