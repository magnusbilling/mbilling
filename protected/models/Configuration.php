<?php
/**
 * Modelo para a tabela "Configuration".
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

class Configuration extends Model
{
	protected $_module = 'configuration';
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
		return 'pkg_configuration';
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
			array('config_key', 'required'),
            array('status', 'numerical', 'integerOnly'=>true),
            array('config_title, config_key', 'length', 'max'=>100),
            array('config_value', 'length', 'max'=>200),
            array('config_description', 'length', 'max'=>500),
            array('config_group_title', 'length', 'max'=>64),
            array('config_value', 'checkConfg'),
		);
	}

	public function checkConfg($attribute,$params)
	{
		$error = false;
		//validation values
		
		
		if ($this->config_key == 'base_country')
		{
			
			if($this->config_value == 'ARG' || $this->config_value == 'ESP' || $this->config_value == 'MEX')
				$this->updateSqlConfigEs();
			elseif ($this->config_value == 'BRL') {
				$this->updateSqlConfigBr();
			}else{
				$this->updateSqlConfigEn();
			}
			
		}

		if ($this->config_key == 'base_language')
		{
			$valuesAllow = array('es','en','pt_BR');
			$this->config_value = $this->config_value == 'br' ? 'pt_BR' : $this->config_value;
			if (!in_array($this->config_value, $valuesAllow))
			{
				$error = true;
			}
			
			if($this->config_value == 'en')
				$this->updateSqlConfigEn();
			elseif ($this->config_value == 'pt_BR') {
				$this->updateSqlConfigBr();
			}
			elseif ($this->config_value == 'es') {
				$this->updateSqlConfigEs();
			}			
		}

		if ($this->config_key == 'template')
		{
			$valuesAllow = array('green-classic','gray-classic','blue-classic','yellow-classic','red-classic','orange-classic','purple-classic',
				'green-neptune','gray-neptune','blue-neptune','yellow-neptune','red-neptune','orange-neptune','purple-neptune',
				'green-crisp','gray-crisp','blue-crisp','yellow-crisp','red-crisp','orange-crisp','purple-crisp',);

			if (!in_array($this->config_value, $valuesAllow))
			{
				$error = true;
			}
		}

		if ($error) {
			$this->addError($attribute,Yii::t('yii','ERROR: Invalid option'));
		}
	}

	public function updateSqlConfigEn()
	{

		$sql = array(
			"UPDATE pkg_configuration SET config_title = 'SIP Account for spy call', config_description = 'SIP Account for spy call' WHERE  config_key = 'channel_spy'",
			"UPDATE pkg_configuration SET config_title = 'System Currency', config_description = 'System Currency' WHERE  config_key = 'base_currency'",
			"UPDATE pkg_configuration SET config_title = 'Language', config_description = 'Allowed values \nen English \nes Espanhol \npt_BR Portugues' WHERE config_key = 'base_language'",
			"UPDATE pkg_configuration SET config_title = 'Version', config_description = 'MBilling Version' WHERE  config_key = 'version'",
			"UPDATE pkg_configuration SET config_title = 'License', config_description = 'MBilling License' WHERE  config_key = 'licence'",
			"UPDATE pkg_configuration SET config_title = 'Server IP', config_description = 'Ip do servidor MBilling' WHERE  config_key = 'ip_servers'",
			"UPDATE pkg_configuration SET config_title = 'Template', config_description = 'Allowed values:\ngreen, gray, blue, yellow, red, orange, purple' WHERE  config_key = 'template'",
			"UPDATE pkg_configuration SET config_title = 'Country', config_description = 'Allowed values\nUSA United States,\nBRL Brasil,\nARG Argentina,\nNLD Netherlands,\nESP Spanish,\nITA Italy,\nMEX Mexico' WHERE  config_key = 'base_country'",
			"UPDATE pkg_configuration SET config_title = 'Desktop layout', config_description = 'Active Desktop template, only to FULL version\n1 - Enable (Only to full version)\n0 - Disable' WHERE  config_key = 'layout'",
			"UPDATE pkg_configuration SET config_title = 'Wallpaper', config_description = 'Default Wallpaper, only FULL version.' WHERE  config_key = 'wallpaper'",
			"UPDATE pkg_configuration SET config_title = 'SMTP Host', config_description = 'SMTP Hostname' WHERE  config_key = 'smtp_host'",
			"UPDATE pkg_configuration SET config_title = 'SMTP UserName', config_description = 'SMTP server Username' WHERE  config_key = 'smtp_username'",
			"UPDATE pkg_configuration SET config_title = 'SMTP Password', config_description = 'SMTP server Password' WHERE  config_key = 'smtp_password'",
			"UPDATE pkg_configuration SET config_title = 'SMTP Encryption', config_description = 'SMTP Encryption: tls, ssl or blank' WHERE  config_key = 'smtp_encryption'",
			"UPDATE pkg_configuration SET config_title = 'SMTP Port', config_description = 'SMTP Port' WHERE  config_key = 'smtp_port'",
			"UPDATE pkg_configuration SET config_title = 'Admin Email', config_description = 'Email for receive notifications' WHERE  config_key = 'admin_email'",
			"UPDATE pkg_configuration SET config_title = 'Send email copy to admin', config_description = 'Send copy for admin email' WHERE  config_key = 'admin_received_email'",
			"UPDATE pkg_configuration SET config_title = 'Days notification', config_description = 'Number of days to generate low balance warning to customers' WHERE  config_key = 'delay_notifications'",
			"UPDATE pkg_configuration SET config_title = 'Rounding calls', config_description = 'Round the lead time as charging sales.\n1: Yes\n0: No' WHERE  config_key = 'bloc_time_call'",
			"UPDATE pkg_configuration SET config_title = 'Days to pay offers', config_description = 'Set how many days before maturity you wanna collect the bid offers' WHERE  config_key = 'planbilling_daytopay'",
			"UPDATE pkg_configuration SET config_title = 'Agent refill limit', config_description = 'Limit to agent refill yours customers' WHERE  config_key = 'agent_limit_refill'",
			"UPDATE pkg_configuration SET config_title = 'Archive cdr', config_description = 'Calls to file before 10 months.' WHERE  config_key = 'archive_call_prior_x_month'",
			"UPDATE pkg_configuration SET config_title = 'Payment, Accepted values', config_description = 'Accepted values ​​in module payments, no enable in FREE version.' WHERE  config_key = 'purchase_amount'",
			"UPDATE pkg_configuration SET config_title = 'Decimal precision', config_description = 'Decimal precision.' WHERE  config_key = 'decimal_precision'",
			"UPDATE pkg_configuration SET config_title = 'Active paypal for new customer', config_description = 'Active paypal for new customer. \n\n0 - Disable (RECOMENDED )\n1 - Enable' WHERE  config_key = 'paypal_new_user'",
			"UPDATE pkg_configuration SET status = 0  WHERE  config_key = 'portabilidadeUsername'",
			"UPDATE pkg_configuration SET status = 0  WHERE  config_key = 'portabilidadePassword'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Answer Call', config_description = 'If enabled the MBilling answers the call that starts.\nDefault: 0' WHERE  config_key = 'answer_call' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - User DNID', config_description = 'If the client does not need active schedule again the number he wish to call after entering the PIN.\n\n1 - Enable (DEFAULT)\n0 - Disable' WHERE  config_key = 'use_dnid' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Notices with Audio', config_description = 'Notices with Audio, if disable, MBilling will send code 603.\n\n1 - Ativo\n0 - Desativado\n' WHERE  config_key = 'play_audio' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Intro Prompt', config_description = 'To specify a prompt to play at the beginning of the calls' WHERE  config_key = 'intro_prompt' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Recording calls', config_description = 'Enables recording of all customers.\nCAUTION, THIS OPTION REQUIRES A LOT OF SERVER PERFORMANCE. SO YOU CAN RECORD CUSTOMER SPECIFIC.\n\n0: Disable\n1: Enable' WHERE  config_key = 'record_call' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - International prefixes', config_description = 'List the prefixes you want stripped off if the call number' WHERE  config_key = 'international_prefixes' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Say sell price', config_description = 'Play the initial cost of the tariff.\n\n0 - No\n1 - Yes' WHERE  config_key = 'say_rateinitial' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Say Duration', config_description = 'Play the amount of time that the user can call.\n\n0 - No\n1 - Yes' WHERE  config_key = 'say_timetocall' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - CallerID Authentication', config_description = 'Active CallerID Authentication.\n\n0 - Disable\n1 - Enable' WHERE  config_key = 'cid_enable' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - FailOver LCR/LCD', config_description = 'If anable and have two hidden tariff in de plan, MBilling gonna get the cheaper' WHERE  config_key = 'failover_lc_prefix' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Dial Command Params', config_description = 'More info: http://voip-info.org/wiki-Asterisk+cmd+dial' WHERE  config_key = 'dialcommand_param' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Internal Call, Dial Command Params', config_description = 'Dial paramater for call between users.\n\nby default (3600000  =  1HOUR MAX CALL).' WHERE  config_key = 'dialcommand_param_sipiax_friend' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - DID Dial Command Params', config_description = 'Dial paramater to DID calls' WHERE  config_key = 'dialcommand_param_call_2did' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Failover Retry Limit', config_description = 'Define how many time we want to authorize the research of the failover trunk when a call fails' WHERE  config_key = 'failover_recursive_limit' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Number of attempt', config_description = 'Number of attempts to dial the number\n Minimum value 1' WHERE  config_key = 'number_try' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Outbound Call', config_description = 'Define the order to make the outbound call<br>YES -> SIP/number@trunk - NO  SIP/trunk/number<br>Both should work exactly the same but i experimented one case when gateway was supporting number@trunk, So in case of trouble, try it out.' WHERE  config_key = 'switchdialcommand' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Say Balance After Call', config_description = 'Play the balance to the user after the call\n\n0 - No\n1 - Yes' WHERE  config_key = 'say_balance_after_call' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Answer Call', config_description = 'If enabled the MBilling answers the call that starts.\nDefault: 0' WHERE  config_key = 'answer_call' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - User DNID', config_description = 'If the client does not need active schedule again the number he wish to call after entering the PIN.\n\n1 - Enable (DEFAULT)\n0 - Disable' WHERE  config_key = 'use_dnid' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Notices with Audio', config_description = 'Notices with Audio, if disable, MBilling will send code 603.\n\n1 - Ativo\n0 - Desativado\n' WHERE  config_key = 'play_audio' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Intro Prompt', config_description = 'To specify a prompt to play at the beginning of the calls' WHERE  config_key = 'intro_prompt' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Recording calls', config_description = 'Enables recording of all customers.\nCAUTION, THIS OPTION REQUIRES A LOT OF SERVER PERFORMANCE. SO YOU CAN RECORD CUSTOMER SPECIFIC.\n\n0: Disable\n1: Enable' WHERE  config_key = 'record_call' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - International prefixes', config_description = 'List the prefixes you want stripped off if the call number' WHERE  config_key = 'international_prefixes' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Say sell price', config_description = 'Play the initial cost of the tariff.\n\n0 - No\n1 - Yes' WHERE  config_key = 'say_rateinitial' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Say Duration', config_description = 'Play the amount of time that the user can call.\n\n0 - No\n1 - Yes' WHERE  config_key = 'say_timetocall' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - CallerID Authentication', config_description = 'Active CallerID Authentication.\n\n0 - Disable\n1 - Enable' WHERE  config_key = 'cid_enable' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - FailOver LCR/LCD', config_description = 'If anable and have two hidden tariff in de plan, MBilling gonna get the cheaper' WHERE  config_key = 'failover_lc_prefix' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Dial Command Params', config_description = 'More info: http://voip-info.org/wiki-Asterisk+cmd+dial' WHERE  config_key = 'dialcommand_param' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Internal Call, Dial Command Params', config_description = 'Dial paramater for call between users.\n\nby default (3600000  =  1HOUR MAX CALL).' WHERE  config_key = 'dialcommand_param_sipiax_friend' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - DID Dial Command Params', config_description = 'Dial paramater to DID calls' WHERE  config_key = 'dialcommand_param_call_2did' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Failover Retry Limit', config_description = 'Define how many time we want to authorize the research of the failover trunk when a call fails' WHERE  config_key = 'failover_recursive_limit' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Number of attempt', config_description = 'Number of attempts to dial the number\n Minimum value 1' WHERE  config_key = 'number_try' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Outbound Call', config_description = 'Define the order to make the outbound call<br>YES -> SIP/number@trunk - NO  SIP/trunk/number<br>Both should work exactly the same but i experimented one case when gateway was supporting number@trunk, So in case of trouble, try it out.' WHERE  config_key = 'switchdialcommand' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Say Balance After Call', config_description = 'Play the balance to the user after the call\n\n0 - No\n1 - Yes' WHERE  config_key = 'say_balance_after_call' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Answer Call', config_description = 'If enabled the MBilling answers the call that starts.\nDefault: 0' WHERE  config_key = 'answer_call' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - User DNID', config_description = 'If the client does not need active schedule again the number he wish to call after entering the PIN.\n\n1 - Enable (DEFAULT)\n0 - Disable' WHERE  config_key = 'use_dnid' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Notices with Audio', config_description = 'Notices with Audio, if disable, MBilling will send code 603.\n\n1 - Ativo\n0 - Desativado\n' WHERE  config_key = 'play_audio' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Intro Prompt', config_description = 'To specify a prompt to play at the beginning of the calls' WHERE  config_key = 'intro_prompt' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Recording calls', config_description = 'Enables recording of all customers.\nCAUTION, THIS OPTION REQUIRES A LOT OF SERVER PERFORMANCE. SO YOU CAN RECORD CUSTOMER SPECIFIC.\n\n0: Disable\n1: Enable' WHERE  config_key = 'record_call' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - International prefixes', config_description = 'List the prefixes you want stripped off if the call number' WHERE  config_key = 'international_prefixes' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Say sell price', config_description = 'Play the initial cost of the tariff.\n\n0 - No\n1 - Yes' WHERE  config_key = 'say_rateinitial' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Say Duration', config_description = 'Play the amount of time that the user can call.\n\n0 - No\n1 - Yes' WHERE  config_key = 'say_timetocall' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - CallerID Authentication', config_description = 'Active CallerID Authentication.\n\n0 - Disable\n1 - Enable' WHERE  config_key = 'cid_enable' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - FailOver LCR/LCD', config_description = 'If anable and have two hidden tariff in de plan, MBilling gonna get the cheaper' WHERE  config_key = 'failover_lc_prefix' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Dial Command Params', config_description = 'More info: http://voip-info.org/wiki-Asterisk+cmd+dial' WHERE  config_key = 'dialcommand_param' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Internal Call, Dial Command Params', config_description = 'Dial paramater for call between users.\n\nby default (3600000  =  1HOUR MAX CALL).' WHERE  config_key = 'dialcommand_param_sipiax_friend' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - DID Dial Command Params', config_description = 'Dial paramater to DID calls' WHERE  config_key = 'dialcommand_param_call_2did' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Failover Retry Limit', config_description = 'Define how many time we want to authorize the research of the failover trunk when a call fails' WHERE  config_key = 'failover_recursive_limit' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Number of attempt', config_description = 'Number of attempts to dial the number\n Minimum value 1' WHERE  config_key = 'number_try' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Outbound Call', config_description = 'Define the order to make the outbound call<br>YES -> SIP/number@trunk - NO  SIP/trunk/number<br>Both should work exactly the same but i experimented one case when gateway was supporting number@trunk, So in case of trouble, try it out.' WHERE  config_key = 'switchdialcommand' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Say Balance After Call', config_description = 'Play the balance to the user after the call\n\n0 - No\n1 - Yes' WHERE  config_key = 'say_balance_after_call' AND config_group_title = 'agi-conf2'",
			
		);
		foreach ($sql as $value) {
			Yii::app()->db->createCommand($value)->execute();
		}
	}

	public function updateSqlConfigBr()
	{
		$sql = array(
			"UPDATE pkg_configuration SET config_title = 'Ramal Voip para Espiar chamadas', config_description = 'Ramal Voip para Espiar chamada' WHERE  config_key = 'channel_spy'",
			"UPDATE pkg_configuration SET config_title = 'Moeda', config_description = 'Moeda usada no sistema' WHERE  config_key = 'base_currency'",
			"UPDATE pkg_configuration SET config_title = 'Idioma do MBilling', config_description = 'Valores permitidos\nen English \nes Espanhol \nbr Português' WHERE config_key = 'base_language'",
			"UPDATE pkg_configuration SET config_title = 'Versão', config_description = 'Versão MBilling' WHERE  config_key = 'version'",
			"UPDATE pkg_configuration SET config_title = 'Licença', config_description = 'Licença MBilling' WHERE  config_key = 'licence'",
			"UPDATE pkg_configuration SET config_title = 'Server IP', config_description = 'Ip do servidor MBilling' WHERE  config_key = 'ip_servers'",
			"UPDATE pkg_configuration SET config_title = 'Template', config_description = 'Valores permitidos:\ngreen, gray, blue, yellow, red, orange, purple' WHERE  config_key = 'template'",
			"UPDATE pkg_configuration SET config_title = 'País', config_description = 'Valores permitidos\nUSA United States,\nBRL Brasil,\nARG Argentina,\nNLD Netherlands,\nESP Spanish,\nITA Italy,\nMEX Mexico' WHERE  config_key = 'base_country'",
			"UPDATE pkg_configuration SET config_title = 'Desktop layout', config_description = 'Ative Desktop template, somente para versão FULL\n1 - Ativado (somente ative se tiver a versão FULL)\n0 - Desativado' WHERE  config_key = 'layout'",
			"UPDATE pkg_configuration SET config_title = 'Wallpaper', config_description = 'Default Wallpaper para a versão FULL.' WHERE  config_key = 'wallpaper'",
			"UPDATE pkg_configuration SET config_title = 'SMTP Host', config_description = 'SMTP Hostname' WHERE  config_key = 'smtp_host'",
			"UPDATE pkg_configuration SET config_title = 'SMTP UserName', config_description = 'SMTP server Username' WHERE  config_key = 'smtp_username'",
			"UPDATE pkg_configuration SET config_title = 'SMTP Password', config_description = 'SMTP server Password' WHERE  config_key = 'smtp_password'",
			"UPDATE pkg_configuration SET config_title = 'SMTP Encryption', config_description = 'SMTP Encryption: tls, ssl ou vazio' WHERE  config_key = 'smtp_encryption'",
			"UPDATE pkg_configuration SET config_title = 'SMTP Port', config_description = 'SMTP Port' WHERE  config_key = 'smtp_port'",
			"UPDATE pkg_configuration SET config_title = 'Email do administrador', config_description = 'Email usado para receber notificações' WHERE  config_key = 'admin_email'",
			"UPDATE pkg_configuration SET config_title = 'Enviar copias para o admin', config_description = 'Receber no email do administrador copias dos emails gerados pelo sistema' WHERE  config_key = 'admin_received_email'",
			"UPDATE pkg_configuration SET config_title = 'Intervalo de notificação', config_description = 'Intervalo para notificar os cliente de que estão com pouco credito.' WHERE  config_key = 'delay_notifications'",
			"UPDATE pkg_configuration SET config_title = 'Arredondar tempo', config_description = 'Arredonda o tempo da chamada respeitando a tarifa de venda.\n1: Sim\n0: Não' WHERE  config_key = 'bloc_time_call'",
			"UPDATE pkg_configuration SET config_title = 'Notificação de  Pacotes de Ofertas', config_description = 'Total Dias anterior ao vencimento que o MBilling avisara o cliente para pagar o pacote de oferta.' WHERE  config_key = 'planbilling_daytopay'",
			"UPDATE pkg_configuration SET config_title = 'Limite de recarga de revendedores', config_description = 'Credito máximo que um revendedor para usar para recarga. Este total é calculado levando em consideração o saldo do revendedor. \n\nEX: Se o revendedor tem R$100 e o limite de recarga esta em 5, quer dizer que o total de credito entre todos os clientes do revendedor, não pode superar R$500.' WHERE  config_key = 'agent_limit_refill'",
			"UPDATE pkg_configuration SET config_title = 'Arquivar ligações', config_description = 'Arquivar ligações anterior a determinada quantidade de meses.' WHERE  config_key = 'archive_call_prior_x_month'",
			"UPDATE pkg_configuration SET config_title = 'Valores aceitos nas formas de pagamento', config_description = 'Modulo indisponível na versão free.' WHERE  config_key = 'purchase_amount'",
			"UPDATE pkg_configuration SET config_title = 'Decimal precisão', config_description = 'Número de zeros apôs a virgula.' WHERE  config_key = 'decimal_precision'",
			"UPDATE pkg_configuration SET config_title = 'Paypal para novos cliente', config_description = 'Permitir recargas automáticas para cliente novos. \n\n0 - Desativado (RECOMENDADO )\n1 - Ativado' WHERE  config_key = 'paypal_new_user'",
			"UPDATE pkg_configuration SET status = 1  WHERE  config_key = 'portabilidadeUsername'",
			"UPDATE pkg_configuration SET status = 1  WHERE  config_key = 'portabilidadePassword'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Answer Call', config_description = 'Se ativado, o MBilling atendera a chamada no inicio. \n\n1 - Ativo \n0 - Desativado (DEFAULT)' WHERE  config_key = 'answer_call' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - User DNID', config_description = 'Se desativar, será solicitado ao cliente que marque o número que deseja chamar.\n\n1 - Ativo (DEFAULT)\n0 - Desativado' WHERE  config_key = 'use_dnid' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Avisos de audio', config_description = 'Notifica os cliente com audio, se estiver desativado será retornado erro 603.\n\n1 - Ativo\n0 - Desativado\n' WHERE  config_key = 'play_audio' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Audio inicial', config_description = 'Audio para ser executado no inicio de cada chamada. Deixe em branco para não executar nada\n' WHERE  config_key = 'intro_prompt' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Gravar chamadas', config_description = 'Grava todos os usuarios\n\n0: Não\n1: Sim\n\nEsta opção vai gravar a ligações de todos os clientes, e isso pode diminuir sua capacidade de chamadas simultâneas. Você pode ativar a gravação somente em clientes específicos no cadastro do cliente.' WHERE  config_key = 'record_call' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - International prefixes', config_description = 'Lista de prefixos que o MBilling vai retirar do numero discado.' WHERE  config_key = 'international_prefixes' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Informar preço do minuto', config_description = 'Informa ao cliente o valor do minuto no inicio de cada chamada.\n\n0 - Não\n1 - Sim' WHERE  config_key = 'say_rateinitial' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Informar tempo disponível', config_description = 'informa no inicio da chamada o tempo total de minutos disponível ' WHERE  config_key = 'say_timetocall' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Autenticação por CallerID', config_description = 'Ativar autenticação por CallerID.\n\n0 - Não\n1 - Sim' WHERE  config_key = 'cid_enable' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Backup por LCR/LCD Prefix', config_description = 'Se ativo e existir dois prefixo idênticos no mesmo plano, o MBilling vai usar 1º o de menor custo.
		Se o tronco da 1º tarifa nao completar a chamada, e não tiver um tronco backup, o MBilling usará a segunda tarifa' WHERE  config_key = 'failover_lc_prefix' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Comando Dial', config_description = 'Mais informações em  : http://voip-info.org/wiki-Asterisk+cmd+dial' WHERE  config_key = 'dialcommand_param' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Internal Call, Dial Command Params', config_description = 'Parâmetros para o DIAL usado em ligações entre clientes.\n\nby default (3600000  =  1HOUR MAX CALL).' WHERE  config_key = 'dialcommand_param_sipiax_friend' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - DID Dial Command Params', config_description = '%timeout% is the value of the paramater : Max time to Call a DID no billed' WHERE  config_key = 'dialcommand_param_call_2did' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Limite de tronco backup', config_description = 'Limite máximo de busca uso de tronco backup' WHERE  config_key = 'failover_recursive_limit' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Tentativas de discagem', config_description = 'Números de tentativas para marcar o numero. Usado para callingcard\n\nValor minimo 1' WHERE  config_key = 'number_try' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Outbound Call', config_description = 'Order para o comando DIAL\n\n1 - SIP/number@trunk\n0 - SIP/trunk/number\n\nAs duas formas trabalham iguais, mas alguns gateways so aceitam SIP/number@trunk.' WHERE  config_key = 'switchdialcommand' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Informar saldo apôs chamada', config_description = 'Diz o credito do cliente ao final da chamada\n\n0 - Não\n1 - Sim' WHERE  config_key = 'say_balance_after_call' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Answer Call', config_description = 'Se ativado, o MBilling atendera a chamada no inicio.\n\n1 - Ativo\n0 - Desativado (DEFAULT)' WHERE  config_key = 'answer_call' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - User DNID', config_description = 'Se desativar, será solicitado ao cliente que marque o número que deseja chamar.\n\n1 - Ativo (DEFAULT)\n0 - Desativado' WHERE  config_key = 'use_dnid' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Avisos de audio', config_description = 'Notifica os cliente com audio, se estiver desativado será retornado erro 603.\n\n1 - Ativo\n0 - Desativado\n' WHERE  config_key = 'play_audio' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Audio inicial', config_description = 'Audio para ser executado no inicio de cada chamada. Deixe em branco para não executar nada\n' WHERE  config_key = 'intro_prompt' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Gravar chamadas', config_description = 'Grava todos os usuarios\n\n0: Não\n1: Sim\n\nEsta opção vai gravar a ligações de todos os clientes, e isso pode diminuir sua capacidade de chamadas simultâneas. Você pode ativar a gravação somente em clientes específicos no cadastro do cliente.' WHERE  config_key = 'record_call' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - International prefixes', config_description = 'Lista de prefixos que o MBilling vai retirar do numero discado.' WHERE  config_key = 'international_prefixes' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Informar preço do minuto', config_description = 'Informa ao cliente o valor do minuto no inicio de cada chamada.\n\n0 - Não\n1 - Sim' WHERE  config_key = 'say_rateinitial' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Informar tempo disponível', config_description = 'informa no inicio da chamada o tempo total de minutos disponível ' WHERE  config_key = 'say_timetocall' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Autenticação por CallerID', config_description = 'Ativar autenticação por CallerID.\n\n0 - Não\n1 - Sim' WHERE  config_key = 'cid_enable' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Backup por LCR/LCD Prefix', config_description = 'Se ativo e existir dois prefixo idênticos no mesmo plano, o MBilling vai usar 1º o de menor custo.\nSe o tronco da 1º tarifa nao completar a chamada, e não tiver um tronco backup, o MBilling usará a segunda tarifa' WHERE  config_key = 'failover_lc_prefix' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Comando Dial', config_description = 'Mais informações em  : http://voip-info.org/wiki-Asterisk+cmd+dial' WHERE  config_key = 'dialcommand_param' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Internal Call, Dial Command Params', config_description = 'Parâmetros para o DIAL usado em ligações entre clientes.\n\nby default (3600000  =  1HOUR MAX CALL).' WHERE  config_key = 'dialcommand_param_sipiax_friend' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - DID Dial Command Params', config_description = '%timeout% is the value of the paramater : Max time to Call a DID no billed' WHERE  config_key = 'dialcommand_param_call_2did' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Limite de tronco backup', config_description = 'Limite máximo de busca uso de tronco backup' WHERE  config_key = 'failover_recursive_limit' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Tentativas de discagem', config_description = 'Números de tentativas para marcar o numero. Usado para callingcard\n\nValor minimo 1' WHERE  config_key = 'number_try' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Outbound Call', config_description = 'Order para o comando DIAL\n\n1 - SIP/number@trunk\n0 - SIP/trunk/number\n\nAs duas formas trabalham iguais, mas alguns gateways so aceitam SIP/number@trunk.' WHERE  config_key = 'switchdialcommand' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Informar saldo apôs chamada', config_description = 'Diz o credito do cliente ao final da chamada\n\n0 - Não\n1 - Sim' WHERE  config_key = 'say_balance_after_call' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Answer Call', config_description = 'Se ativado, o MBilling atendera a chamada no inicio.\n\n1 - Ativo\n0 - Desativado (DEFAULT)' WHERE  config_key = 'answer_call' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - User DNID', config_description = 'Se desativar, será solicitado ao cliente que marque o número que deseja chamar.\n\n1 - Ativo (DEFAULT)\n0 - Desativado' WHERE  config_key = 'use_dnid' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Avisos de audio', config_description = 'Notifica os cliente com audio, se estiver desativado será retornado erro 603.\n\n1 - Ativo\n0 - Desativado\n' WHERE  config_key = 'play_audio' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Audio inicial', config_description = 'Audio para ser executado no inicio de cada chamada. Deixe em branco para não executar nada\n' WHERE  config_key = 'intro_prompt' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Gravar chamadas', config_description = 'Grava todos os usuarios\n\n0: Não\n1: Sim\n\nEsta opção vai gravar a ligações de todos os clientes, e isso pode diminuir sua capacidade de chamadas simultâneas. Você pode ativar a gravação somente em clientes específicos no cadastro do cliente.' WHERE  config_key = 'record_call' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - International prefixes', config_description = 'Lista de prefixos que o MBilling vai retirar do numero discado.' WHERE  config_key = 'international_prefixes' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Informar preço do minuto', config_description = 'Informa ao cliente o valor do minuto no inicio de cada chamada.\n\n0 - Não\n1 - Sim' WHERE  config_key = 'say_rateinitial' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Informar tempo disponível', config_description = 'informa no inicio da chamada o tempo total de minutos disponível ' WHERE  config_key = 'say_timetocall' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Autenticação por CallerID', config_description = 'Ativar autenticação por CallerID.\n\n0 - Não\n1 - Sim' WHERE  config_key = 'cid_enable' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Backup por LCR/LCD Prefix', config_description = 'Se ativo e existir dois prefixo idênticos no mesmo plano, o MBilling vai usar 1º o de menor custo.\nSe o tronco da 1º tarifa nao completar a chamada, e não tiver um tronco backup, o MBilling usará a segunda tarifa' WHERE  config_key = 'failover_lc_prefix' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Comando Dial', config_description = 'Mais informações em  : http://voip-info.org/wiki-Asterisk+cmd+dial' WHERE  config_key = 'dialcommand_param' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Internal Call, Dial Command Params', config_description = 'Parâmetros para o DIAL usado em ligações entre clientes.\n\nby default (3600000  =  1HOUR MAX CALL).' WHERE  config_key = 'dialcommand_param_sipiax_friend' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - DID Dial Command Params', config_description = '%timeout% is the value of the paramater : Max time to Call a DID no billed' WHERE  config_key = 'dialcommand_param_call_2did' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Limite de tronco backup', config_description = 'Limite máximo de busca uso de tronco backup' WHERE  config_key = 'failover_recursive_limit' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Tentativas de discagem', config_description = 'Números de tentativas para marcar o numero. Usado para callingcard\n\nValor minimo 1' WHERE  config_key = 'number_try' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Outbound Call', config_description = 'Order para o comando DIAL\n\n1 - SIP/number@trunk\n0 - SIP/trunk/number\n\nAs duas formas trabalham iguais, mas alguns gateways so aceitam SIP/number@trunk.' WHERE  config_key = 'switchdialcommand' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Informar saldo apôs chamada', config_description = 'Diz o credito do cliente ao final da chamada\n\n0 - Não\n1 - Sim' WHERE  config_key = 'say_balance_after_call' AND config_group_title = 'agi-conf3'",	
		);
		foreach ($sql as $value) {
			Yii::app()->db->createCommand($value)->execute();
		}
	}

	public function updateSqlConfigEs()
	{
		$sql = array(
			"UPDATE pkg_configuration SET config_title = 'Ramal Voip para Espiar llamadas', config_description = 'Ramal Voip para Espiar llamadas' WHERE  config_key = 'channel_spy'",
			"UPDATE pkg_configuration SET config_title = 'Moneda', config_description = 'Moneda' WHERE  config_key = 'base_currency'",
			"UPDATE pkg_configuration SET config_title = 'Idioma', config_description = 'Opciones \nen Ingles \nes Español \npt_BR Portugues' WHERE config_key = 'base_language'",
			"UPDATE pkg_configuration SET config_title = 'Version', config_description = 'MBilling Version' WHERE  config_key = 'version'",
			"UPDATE pkg_configuration SET config_title = 'Licencia', config_description = 'MBilling Licencia' WHERE  config_key = 'licence'",
			"UPDATE pkg_configuration SET config_title = 'IP Servidor', config_description = 'Ip Servidor Magnus' WHERE  config_key = 'ip_servers'",
			"UPDATE pkg_configuration SET config_title = 'Color Entrono Grafico', config_description = 'Opciones:\ngreen, gray, blue, yellow, red, orange, purple' WHERE  config_key = 'template'",
			"UPDATE pkg_configuration SET config_title = 'País', config_description = 'Allowed values\nUSA United States,\nBRL Brasil,\nARG Argentina,\nNLD Netherlands,\nESP Spanish,\nITA Italy,\nMEX Mexico' WHERE  config_key = 'base_country'",
			"UPDATE pkg_configuration SET config_title = 'Entorno Web, Escritorio', config_description = 'Activar Escritorio, solo en version FULL\n1 - Activo (Solo valido Version FULL)\n0 - Desactivado' WHERE  config_key = 'layout'",
			"UPDATE pkg_configuration SET config_title = 'Fondo Escritorio', config_description = 'Fondo de escritorio, Activo solo en version FULL.' WHERE  config_key = 'wallpaper'",
			"UPDATE pkg_configuration SET config_title = 'SMTP Servidor', config_description = 'SMTP Servidor' WHERE  config_key = 'smtp_host'",
			"UPDATE pkg_configuration SET config_title = 'SMTP Usuario', config_description = 'SMTP Usuario' WHERE  config_key = 'smtp_username'",
			"UPDATE pkg_configuration SET config_title = 'SMTP Password', config_description = 'SMTP Password' WHERE  config_key = 'smtp_password'",
			"UPDATE pkg_configuration SET config_title = 'SMTP Encryption', config_description = 'SMTP Encryption: tls, ssl o en blanco' WHERE  config_key = 'smtp_encryption'",
			"UPDATE pkg_configuration SET config_title = 'SMTP Puerto', config_description = 'SMTP Puerto' WHERE  config_key = 'smtp_port'",
			"UPDATE pkg_configuration SET config_title = 'Email Administrador', config_description = 'Email Para notificaciones' WHERE  config_key = 'admin_email'",
			"UPDATE pkg_configuration SET config_title = 'Envio copia Email admin', config_description = 'Envio copia Email Administrador' WHERE  config_key = 'admin_received_email'",
			"UPDATE pkg_configuration SET config_title = 'Dia Notificaciones', config_description = 'Numero de dias para aviso de Saldo' WHERE  config_key = 'delay_notifications'",
			"UPDATE pkg_configuration SET config_title = 'Redondeo de llamada', config_description = 'Redondeo costo llamada.\n1: Si\n0: No' WHERE  config_key = 'bloc_time_call'",
			"UPDATE pkg_configuration SET config_title = 'Dia de pago de las Ofertas', config_description = 'Numero de dias, antes del vencimiento de la Oferta' WHERE  config_key = 'planbilling_daytopay'",
			"UPDATE pkg_configuration SET config_title = 'Limite de Recarga por Agente', config_description = 'Limite de Recarga Agente-Cliente' WHERE  config_key = 'agent_limit_refill'",
			"UPDATE pkg_configuration SET config_title = 'Archivo Cdr', config_description = 'Numero de meses, maximo 10Meses.' WHERE  config_key = 'archive_call_prior_x_month'",
			"UPDATE pkg_configuration SET config_title = 'Pagos, Valores Aceptados', config_description = 'Valores Aceptados, No desponibles en la version FREE' WHERE  config_key = 'purchase_amount'",
			"UPDATE pkg_configuration SET config_title = 'Decimales', config_description = 'Numero de decimales a tener en cuenta.' WHERE  config_key = 'decimal_precision'",
			"UPDATE pkg_configuration SET config_title = 'Paypal activo para nuevos clientes', config_description = 'Paypal activo para nuevos clientes. \n\n0 - Desactivado (RECOMENDADO )\n1 - Activado' WHERE  config_key = 'paypal_new_user'",
			"UPDATE pkg_configuration SET status = 0  WHERE  config_key = 'portabilidadeUsername'",
			"UPDATE pkg_configuration SET status = 0  WHERE  config_key = 'portabilidadePassword'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Llamada Contestada', config_description = 'Si se activa, el MBilling responde a la llamada.\nDefault: 0' WHERE  config_key = 'answer_call' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - User DNID', config_description = 'Si el cliente no necesita programación activa de nuevo el número que desea llamar después de introducir el PIN.\n\n1 - Activado (DEFECTO)\n0 - Desactivado' WHERE  config_key = 'use_dnid' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Avisos de audio', config_description = 'Avisos de Audio, Si esta disabilidado, MBilling envia code 603.\n\n1 - Ativo\n0 - Desativado\n' WHERE  config_key = 'play_audio' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Intro Prompt', config_description = 'Para especificar un mensaje para dar inicio de las llamadas' WHERE  config_key = 'intro_prompt' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Grabacion de llamadas', config_description = 'Activa la grabacion de llamadas, para los clientes.\nPRECAUCION, ESTA OPCION REQUIERE UN ALTO RENDIMIENTO DE NUESTRO SERVIDOR.\n\n0: Desactivado\n1: Activado' WHERE  config_key = 'record_call' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Prefijo Internacional', config_description = 'Prefijos para quitar, Ejemplo, llamada a España 00349x., prefijo a quitar 00' WHERE  config_key = 'international_prefixes' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Locucion Precio de Venta', config_description = 'Locucion Precio de Vanta de Tarifa.\n\n0 - No\n1 - Si' WHERE  config_key = 'say_rateinitial' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Locucion Duracion', config_description = 'Locucion, de la cantidad de tiempo que el cliente puede llamar.\n\n0 - No\n1 - Si' WHERE  config_key = 'say_timetocall' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Autentificacion por Caller-Id', config_description = 'Activa la Autentificacion por CallerID.\n\n0 - Desactivado\n1 - Activado' WHERE  config_key = 'cid_enable' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Conmutacion por error LCR/LCD', config_description = 'Si habilita, MBilling va a cursar llamada, por el mas barato' WHERE  config_key = 'failover_lc_prefix' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Parametros comando Dial ', config_description = 'Mas info: http://voip-info.org/wiki-Asterisk+cmd+dial' WHERE  config_key = 'dialcommand_param' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - LLamadas Internas, Parametros comando Dial ', config_description = 'Parametros de llamada entre usuario.\n\nby defecto (3600000  =  1HOUR MAX CALL).' WHERE  config_key = 'dialcommand_param_sipiax_friend' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Parametros del Parametro DID', config_description = 'Parametros DID' WHERE  config_key = 'dialcommand_param_call_2did' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Conmutacion por error, Limite de Reintentos', config_description = 'Definir el número de tiempo que queremos autorizar la investigación del TRONCAL de conmutación por error cuando falla una llamada' WHERE  config_key = 'failover_recursive_limit' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Número de intentos', config_description = 'Número de intentos para marcar el número\n Valor Minimo 1' WHERE  config_key = 'number_try' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Llamadas Salientes', config_description = 'Marcacion Llamadas Salientes<br>YES -> SIP/number@trunk - NO  SIP/trunk/number<br>Ambos deben trabajar exactamente igual, pero yo he experimentado uno de los casos, cuando la puerta de enlace estaba apoyando número@TRUNK, Asi que en caso de problemas, probarlo.' WHERE  config_key = 'switchdialcommand' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 1 - Locucion Saldo Despues de la Llamada', config_description = 'Locucion de saldo restantes en la cuenta despues de la llamada\n\n0 - No\n1 - Si' WHERE  config_key = 'say_balance_after_call' AND config_group_title = 'agi-conf1'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Respuesta de llamada', config_description = 'Si esta activa MBilling Responde a la llamada que inicia.\nDefault: 0' WHERE  config_key = 'answer_call' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - User DNID', config_description = 'Si el cliente no necesita programación activa de nuevo el número que desea llamar después de introducir el PIN.\n\n1 - Enable (DEFAULT)\n0 - Disable' WHERE  config_key = 'use_dnid' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Avisos de audio', config_description = 'Avisos de Audio, Si esta disabilidado, MBilling envia code 603.\n\n1 - Ativo\n0 - Desativado\n' WHERE  config_key = 'play_audio' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Intro Prompt', config_description = 'Para especificar un mensaje para dar inicio de las llamadas' WHERE  config_key = 'intro_prompt' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Grabacion de llamadas', config_description = 'Activa la grabacion de llamadas, para los clientes.\nPRECAUCION, ESTA OPCION REQUIERE UN ALTO RENDIMINENTO DE NUESTRO SERVIDOR.\n\n0: Desactivado\n1: Activado' WHERE  config_key = 'record_call' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Prefijo Internacional', config_description = 'Prefijos para quitar, Ejemplo, llamada a España 00349x., prefijo a quitar 00' WHERE  config_key = 'international_prefixes' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Locucion Precio de Venta', config_description = 'Locucion Precio de Vanta de Tarifa.\n\n0 - No\n1 - Yes' WHERE  config_key = 'say_rateinitial' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Locucion Duracion', config_description = 'Locucion, de la cantidad de tiempo que el cliente puede llamar.\n\n0 - No\n1 - Si' WHERE  config_key = 'say_timetocall' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Autentificacion por Caller-Id', config_description = 'Activa la Autentificacion por CallerID\n\n0 - Desactivado\n1 - Activado' WHERE  config_key = 'cid_enable' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Conmutacion por error LCR/LCD', config_description = 'Si habilita, MBilling va a cursar llamada, por el mas barato' WHERE  config_key = 'failover_lc_prefix' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Parametros comando Dial', config_description = 'Mas info: http://voip-info.org/wiki-Asterisk+cmd+dial' WHERE  config_key = 'dialcommand_param' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - LLamadas Internas, Parametros comando Dial ', config_description = 'Parametros de llamada entre usuario.\n\nby defecto (3600000  =  1HOUR MAX CALL).' WHERE  config_key = 'dialcommand_param_sipiax_friend' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Parametros del Parametro DID', config_description = 'Parametros DID' WHERE  config_key = 'dialcommand_param_call_2did' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Conmutacion por error, Limite de Reintentos', config_description = 'Definir el número de tiempo que queremos autorizar la investigación del TRONCAL de conmutación por error cuando falla una llamada' WHERE  config_key = 'failover_recursive_limit' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Número de intentos', config_description = 'Número de intentos para marcar el número\n Valor Minimo 1' WHERE  config_key = 'number_try' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Llamadas Salientes', config_description = 'Marcacion Llamadas Salientes<br>YES -> SIP/number@trunk - NO  SIP/trunk/number<br>Ambos deben trabajar exactamente igual, pero yo he experimentado uno de los casos, cuando la puerta de enlace estaba apoyando número@TRUNK, Asi que en caso de problemas, probarlo.' WHERE  config_key = 'switchdialcommand' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 2 - Locucion Saldo Despues de la Llamada', config_description = 'Locucion de saldo restantes en la cuenta despues de la llamada\n\n0 - No\n1 - Si' WHERE  config_key = 'say_balance_after_call' AND config_group_title = 'agi-conf2'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Respuesta de llamada', config_description = 'Si esta activa MBilling Responde a la llamada que inicia.\nDefault: 0' WHERE  config_key = 'answer_call' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - User DNID', config_description = 'Si el cliente no necesita programación activa de nuevo el número que desea llamar después de introducir el PIN.\n\n1 - Enable (DEFAULT)\n0 - Disable' WHERE  config_key = 'use_dnid' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Avisos de audio', config_description = 'Avisos de Audio, Si esta disabilidado, MBilling envia code 603.\n\n1 - Ativo\n0 - Desativado\n' WHERE  config_key = 'play_audio' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Intro Prompt', config_description = 'Para especificar un mensaje para dar inicio de las llamadas' WHERE  config_key = 'intro_prompt' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Grabacion de llamadas', config_description = 'Activa la grabacion de llamadas, para los clientes.\nPRECAUCION, ESTA OPCION REQUIERE UN ALTO RENDIMINENTO DE NUESTRO SERVIDOR.\n\n0: Desactivado\n1: Activado' WHERE  config_key = 'record_call' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Prefijo Internacional', config_description = 'Prefijos para quitar, Ejemplo, llamada a España 00349x., prefijo a quitar 00' WHERE  config_key = 'international_prefixes' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Locucion Precio de Venta', config_description = 'Locucion Precio de Vanta de Tarifa.\n\n0 - No\n1 - Yes' WHERE  config_key = 'say_rateinitial' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Locucion Duracion', config_description = 'Locucion, de la cantidad de tiempo que el cliente puede llamar.\n\n0 - No\n1 - Si' WHERE  config_key = 'say_timetocall' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Autentificacion por Caller-Id', config_description = 'Activa la Autentificacion por CallerID\n\n0 - Desactivado\n1 - Activado' WHERE  config_key = 'cid_enable' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Conmutacion por error LCR/LCD', config_description = 'Si habilita, MBilling va a cursar llamada, por el mas barato' WHERE  config_key = 'failover_lc_prefix' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Parametros comando Dial', config_description = 'Mas info: http://voip-info.org/wiki-Asterisk+cmd+dial' WHERE  config_key = 'dialcommand_param' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - LLamadas Internas, Parametros comando Dial ', config_description = 'Parametros de llamada entre usuario.\n\nby defecto (3600000  =  1HOUR MAX CALL).' WHERE  config_key = 'dialcommand_param_sipiax_friend' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Parametros del Parametro DID', config_description = 'Parametros DID' WHERE  config_key = 'dialcommand_param_call_2did' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Conmutacion por error, Limite de Reintentos', config_description = 'Definir el número de tiempo que queremos autorizar la investigación del TRONCAL de conmutación por error cuando falla una llamada' WHERE  config_key = 'failover_recursive_limit' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Número de intentos', config_description = 'Número de intentos para marcar el número\n Valor Minimo 1' WHERE  config_key = 'number_try' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Llamadas Salientes', config_description = 'Marcacion Llamadas Salientes<br>YES -> SIP/number@trunk - NO  SIP/trunk/number<br>Ambos deben trabajar exactamente igual, pero yo he experimentado uno de los casos, cuando la puerta de enlace estaba apoyando número@TRUNK, Asi que en caso de problemas, probarlo.' WHERE  config_key = 'switchdialcommand' AND config_group_title = 'agi-conf3'",
			"UPDATE pkg_configuration SET config_title = 'AGI 3 - Locucion Saldo Despues de la Llamada', config_description = 'Locucion de saldo restantes en la cuenta despues de la llamada\n\n0 - No\n1 - Si' WHERE  config_key = 'say_balance_after_call' AND config_group_title = 'agi-conf3'",
			);
		foreach ($sql as $value) {
			Yii::app()->db->createCommand($value)->execute();
		}
	}
	

}