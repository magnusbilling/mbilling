
<?php
/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2016 MagnusBilling. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 *
 */
class UpdateMysqlCommand extends CConsoleCommand
{

	var $success;

	public function run( $args ) {
		$config = LoadConfig::getConfig();

		$version =  $config['global']['version'];
		$language =  $config['global']['base_language'];

		echo $version;

		if ( preg_match( "/^4\./", $version ) ) {
			$sql = "ALTER TABLE `pkg_rate` DROP `disconnectcharge_after`;
			ALTER TABLE `pkg_rate` DROP additional_block_charge;
			ALTER TABLE `pkg_rate` DROP additional_block_charge_time;
			ALTER TABLE `pkg_rate` DROP rounding_threshold;
			ALTER TABLE `pkg_rate` DROP minimal_cost;
			ALTER TABLE `pkg_rate` DROP rounding_calltime;
			DELETE FROM  `pkg_configuration` WHERE  `config_group_title` LIKE  'agi-conf2';
			DELETE FROM  `pkg_configuration` WHERE  `config_group_title` LIKE  'agi-conf3';
			";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}


			$version = '5.0.0';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
		}

		if ( $version == '5.0.0' ) {
			$sql = "ALTER TABLE  `pkg_method_pay` ADD  `P2P_CustomerSiteID` VARCHAR( 100 ) NOT NULL DEFAULT  '',
				ADD  `P2P_KeyID` VARCHAR( 50 ) NOT NULL DEFAULT  '',
				ADD  `P2P_Passphrase` VARCHAR( 50 ) NOT NULL DEFAULT  '',
				ADD  `P2P_RecipientKeyID` VARCHAR( 30 ) NOT NULL DEFAULT  '',
				ADD  `P2P_tax_amount` VARCHAR( 10 ) NOT NULL DEFAULT  '0'";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			$version = '5.0.1';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
		}
		if ( $version == '5.0.1' ) {
			$version = '5.0.2';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}
		if ( $version == '5.0.2' ) {
			$sql = "
				ALTER TABLE  `pkg_user` CHANGE  `address`  `address` VARCHAR( 20 )  DEFAULT  '';
				ALTER TABLE `pkg_user` CHANGE `zipcode` `zipcode` VARCHAR( 20 ) DEFAULT '';
				ALTER TABLE `pkg_user` CHANGE `mobile` `mobile` VARCHAR( 20 ) DEFAULT '';
				ALTER TABLE `pkg_user` CHANGE `company_name` `company_name` VARCHAR( 20 ) DEFAULT '';
				ALTER TABLE `pkg_user` CHANGE `callshop` `callshop` VARCHAR( 20 ) DEFAULT '';";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.0.3';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.0.3' ) {
			$sql = "
			INSERT INTO `pkg_configuration` (`id`, `config_title`, `config_key`, `config_value`, `config_description`, `config_group_title`, `status`) VALUES (NULL, 'fm.transfer-to.com show selling price', 'fm_transfer_show_selling_price', '0', 'Show recommended selling price in the form after select the amount. Set the total to increase. Less than 99%', 'global', '1');
			INSERT INTO `pkg_configuration` (`id`, `config_title`, `config_key`, `config_value`, `config_description`, `config_group_title`, `status`) VALUES (NULL, 'fm.transfer-to.com print Header', 'fm_transfer_print_header', 'Change it in configuration menu', 'Description to print header', 'global', '1');
			INSERT INTO `pkg_configuration` (`id`, `config_title`, `config_key`, `config_value`, `config_description`, `config_group_title`, `status`) VALUES (NULL, 'fm.transfer-to.com print Footer', 'fm_transfer_print_footer', 'Change it in configuration menu', 'Description to print footer', 'global', '1');
			INSERT INTO `pkg_configuration` (`id`, `config_title`, `config_key`, `config_value`, `config_description`, `config_group_title`, `status`) VALUES (NULL, 'fm.transfer-to.com Currency', 'fm_transfer_currency', '€', 'Set the transfer-to currency', 'global', '1');";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.0.4';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.0.4' ) {
			$sql = "
			ALTER TABLE  `pkg_user` ADD  `googleAuthenticator_enable` TINYINT( 1 ) NOT NULL DEFAULT  '0';
			ALTER TABLE  `pkg_user` ADD  `google_authenticator_key` VARCHAR( 50 ) NOT NULL DEFAULT  '';
			ALTER TABLE  `pkg_user` CHANGE  `lastname`  `lastname` VARCHAR( 50 ) NOT NULL DEFAULT  '';
			ALTER TABLE  `pkg_user` CHANGE  `firstname`  `firstname` VARCHAR( 50 ) NOT NULL DEFAULT  '';
			ALTER TABLE  `pkg_user` CHANGE  `city`  `city` VARCHAR( 50 ) NOT NULL DEFAULT  '';
			ALTER TABLE  `pkg_user` CHANGE  `state`  `state` VARCHAR( 50 ) NOT NULL DEFAULT  '';
			ALTER TABLE  `pkg_user` CHANGE  `country`  `country` VARCHAR( 50 ) NOT NULL DEFAULT  '';
			ALTER TABLE  `pkg_user` CHANGE  `email`  `email` VARCHAR( 50 ) NOT NULL DEFAULT  '';
			ALTER TABLE  `pkg_user` CHANGE  `phone`  `phone` VARCHAR( 50 ) NOT NULL DEFAULT  '';
			";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.0.5';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}
		if ( $version == '5.0.5' ) {
			$sql = "ALTER TABLE  `pkg_rate_agent` ADD  `minimal_time_charge` SMALLINT( 2 ) NOT NULL DEFAULT  '0'";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.0.7';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}


		if ( $version == '5.0.7' ) {	

		
			$sql = "INSERT INTO `pkg_configuration` VALUES (NULL, 'Sip trunk short duration call', 'trunk_short_duration_call', '3', 'SIP TRUNK short duration call', 'global', '1');
			INSERT INTO `pkg_configuration` VALUES (NULL, 'Sip trunk short total calls', 'trunk_short_total_calls', '0', 'Sip trunk short total calls', 'global', '1');
			ALTER TABLE  `pkg_trunk` ADD  `short_time_call` INT( 11 ) NOT NULL DEFAULT  '0';";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.0.8';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();	

        	}

        	if ( $version == '5.0.8' ) {	

		
			$sql = "ALTER TABLE  `pkg_plan` ADD  `play_audio` SMALLINT( 1 ) NOT NULL DEFAULT  '0'";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$sql = "UPDATE  `pkg_plan` SET play_audio = ( SELECT config_value FROM pkg_configuration WHERE config_key =  'play_audio' )";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.0.9';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();	

        	}

        	if ( $version == '5.0.9' ) {	

		
			$sql = "ALTER TABLE  `pkg_trunk` ADD  `fromuser` VARCHAR( 80 ) NOT NULL DEFAULT  ''";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$sql = "UPDATE  `pkg_trunk` SET fromuser = user WHERE 1";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
					

			$version = '5.1.0';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();	

        	}

        	if ( $version == '5.1.0' ) {	

		
			$sql = "ALTER TABLE  `pkg_callshop` CHANGE  `buycost`  `buycost` DECIMAL( 15, 5 ) NOT NULL DEFAULT  '0.00000';
        			ALTER TABLE  `pkg_callshop` CHANGE  `markup`  `markup` DECIMAL( 15, 5 ) NOT NULL DEFAULT  '0.00000';";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
								

			$version = '5.1.1';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();	

        	}

        	if ( $version == '5.1.1' ) {	

		
			$sql = "INSERT INTO `pkg_configuration` VALUES (NULL, 'Enable IAX', 'enable_izx', '3', 'Enable IAX', 'global', '0');";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$sql = "CREATE TABLE IF NOT EXISTS `pkg_iax` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `id_user` int(11) NOT NULL DEFAULT '0',
			  `name` varchar(80) COLLATE utf8_bin NOT NULL,
			  `accountcode` varchar(20) COLLATE utf8_bin NOT NULL,
			  `regexten` varchar(20) COLLATE utf8_bin NOT NULL,
			  `amaflags` char(7) COLLATE utf8_bin DEFAULT NULL,
			  `callgroup` char(10) COLLATE utf8_bin DEFAULT NULL,
			  `callerid` varchar(80) COLLATE utf8_bin NOT NULL,
			  `canreinvite` varchar(20) COLLATE utf8_bin NOT NULL,
			  `context` varchar(80) COLLATE utf8_bin NOT NULL,
			  `DEFAULTip` char(15) COLLATE utf8_bin DEFAULT NULL,
			  `dtmfmode` char(7) COLLATE utf8_bin NOT NULL DEFAULT 'RFC2833',
			  `fromuser` varchar(80) COLLATE utf8_bin NOT NULL,
			  `fromdomain` varchar(80) COLLATE utf8_bin NOT NULL,
			  `host` varchar(31) COLLATE utf8_bin NOT NULL,
			  `insecure` varchar(20) COLLATE utf8_bin NOT NULL,
			  `language` char(2) COLLATE utf8_bin DEFAULT NULL,
			  `mailbox` varchar(50) COLLATE utf8_bin NOT NULL,
			  `md5secret` varchar(80) COLLATE utf8_bin NOT NULL,
			  `nat` varchar(25) COLLATE utf8_bin DEFAULT 'yes',
			  `permit` varchar(95) COLLATE utf8_bin NOT NULL,
			  `deny` varchar(95) COLLATE utf8_bin NOT NULL,
			  `mask` varchar(95) COLLATE utf8_bin NOT NULL,
			  `pickupgroup` char(10) COLLATE utf8_bin DEFAULT NULL,
			  `port` char(5) COLLATE utf8_bin NOT NULL DEFAULT '',
			  `qualify` char(7) COLLATE utf8_bin DEFAULT 'yes',
			  `restrictcid` char(1) COLLATE utf8_bin DEFAULT NULL,
			  `rtptimeout` char(3) COLLATE utf8_bin DEFAULT NULL,
			  `rtpholdtimeout` char(3) COLLATE utf8_bin DEFAULT NULL,
			  `secret` varchar(80) COLLATE utf8_bin NOT NULL,
			  `type` char(6) COLLATE utf8_bin NOT NULL DEFAULT 'friend',
			  `username` varchar(80) COLLATE utf8_bin NOT NULL,
			  `disallow` varchar(100) COLLATE utf8_bin NOT NULL,
			  `allow` varchar(100) COLLATE utf8_bin NOT NULL,
			  `musiconhold` varchar(100) COLLATE utf8_bin NOT NULL,
			  `regseconds` int(11) NOT NULL DEFAULT '0',
			  `ipaddr` char(15) COLLATE utf8_bin NOT NULL DEFAULT '',
			  `cancallforward` char(3) COLLATE utf8_bin DEFAULT 'yes',
			  `trunk` char(3) COLLATE utf8_bin DEFAULT 'no',
			  `useragent` varchar(200) COLLATE utf8_bin NOT NULL DEFAULT '',
			  `requirecalltoken` varchar(3) COLLATE utf8_bin NOT NULL DEFAULT 'no',
			  `calllimit` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `cons_pkg_iax_name` (`name`),
			  KEY `name` (`name`),
			  KEY `host` (`host`),
			  KEY `ipaddr` (`ipaddr`),
			  KEY `port` (`port`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$sql = "INSERT INTO pkg_module VALUES (NULL, 't(''Iax'')', 'iax', 'sipbuddies', '1')";
			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}
			$idSubModule = Yii::app()->db->lastInsertID;
			

			$sql = "INSERT INTO pkg_group_module VALUES ((SELECT id FROM pkg_group_user WHERE id_user_type = 1 LIMIT 1), '".$idSubModule."', 'crud', '1', '1', '1');";
			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}
								

			$version = '5.1.2';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();	

        	}

        	if ( $version == '5.1.2' ) {
			$sql = "ALTER TABLE  `pkg_rate` ADD  `minimal_time_buy` SMALLINT( 2 ) NOT NULL DEFAULT  '0'";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.1.3';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}
		if ( $version == '5.1.3' ) {
        		$sql = "
        		ALTER TABLE  `pkg_user` CHANGE  `address`  `address` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;
        		INSERT INTO `pkg_configuration`  VALUES (NULL, 'Use CDR Cache', 'cache', '0', 'Use CDR cache', 'global', '0');";
        		try {
					Yii::app()->db->createCommand( $sql )->execute();
				} catch ( Exception $e ) {

				}

        		$sql = 'INSERT INTO pkg_configuration  VALUES (NULL, "AGI 1 - Use amd macro", "amd","", "Use amd. Set to CM(amd) . 
				Add this macro in your extension_magnus.conf

				[macro-amd]
				exten => s,1,AMD
				exten => s,n,Noop(AMD_NUMERO - ${CALLERID(num)})
				exten => s,n,Noop(AMD_STATUS - ${AMDSTATUS})
				exten => s,n,Noop(AMD_CAUSE - ${AMDCAUSE})
				exten => s,n,GotoIf($[${AMDSTATUS}=HUMAN]?humn:mach)
				exten => s,n(mach),SoftHangup(${CHANNEL})
				exten => s,n,Hangup()
				exten => s,n(humn),WaitForSilence(20)", "agi-conf1", 1);';
        		try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			
        		$version = '5.1.4';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.1.4' ) {
			$sql = "ALTER TABLE  `pkg_did` ADD  `block_expression_1` SMALLINT( 2 ) NOT NULL DEFAULT  '0';
			ALTER TABLE  `pkg_did` ADD  `block_expression_2` SMALLINT( 2 ) NOT NULL DEFAULT  '0';";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.1.5';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.1.5' ) {
			$sql = "ALTER TABLE  `pkg_did` ADD  `expression_3` varchar( 150 ) NOT NULL DEFAULT  '*';
				ALTER TABLE  `pkg_did` ADD  `selling_rate_3` decimal( 15,5 ) NOT NULL DEFAULT  '0.00000';
				ALTER TABLE  `pkg_did` ADD  `block_expression_3` SMALLINT( 2 ) NOT NULL DEFAULT  '0';";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.1.6';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.1.6' ) {
			
			$sql = "INSERT INTO `pkg_configuration` VALUES (NULL, 'BDService Url', 'BDService_url', 'http://takasend.net', 'Default http://takasend.net', 'global', '1');";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.1.7';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}


		if ( $version == '5.1.7' ) {
			
			$sql = "ALTER TABLE  `pkg_did` ADD  `charge_of` int( 1 ) NOT NULL DEFAULT  '1';";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.1.8';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}


		if ( $version == '5.1.8' ) {
			
			$sql = "ALTER TABLE  `pkg_method_pay` ADD  `client_id` VARCHAR( 500 ) NULL DEFAULT NULL ,
			ADD  `client_secret` VARCHAR( 500 ) NULL DEFAULT NULL";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.1.9';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.1.9' ) {
			
			$sql = "ALTER TABLE  `pkg_user` CHANGE  `company_name`  `company_name` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			$sql = "ALTER TABLE  `pkg_user` ADD  `doc` VARCHAR( 50 ) NULL DEFAULT NULL ;";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$sql = "ALTER TABLE  `pkg_queue_status` CHANGE  `holdtime`  `holdtime` VARCHAR( 11 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT  '';
			ALTER TABLE  `pkg_queue_status` CHANGE  `totalCalls`  `totalCalls` INT( 11 ) NULL DEFAULT NULL;
			ALTER TABLE  `pkg_queue_status` CHANGE  `answeredCalls`  `answeredCalls` INT( 11 ) NULL DEFAULT NULL; 
			ALTER TABLE  `pkg_sip` CHANGE `callshopnumber` `callshopnumber` VARCHAR(15) NULL DEFAULT NULL;
			ALTER TABLE  `pkg_queue_status` CHANGE  `callduration`  `callduration` INT( 11 ) NULL DEFAULT NULL; 
			ALTER TABLE  `pkg_queue_status` CHANGE  `callerId`  `callerId` VARCHAR( 60 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			$version = '5.2.0';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.2.0' ) {
			
			$sql = "ALTER TABLE  `pkg_refill` ADD  `invoice_number` VARCHAR( 50 ) NOT NULL DEFAULT  '';";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			$version = '5.2.1';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.2.1' ) {
			
			$sql = "ALTER TABLE  `pkg_provider` CHANGE  `credit`  `credit` DECIMAL( 20, 5 ) NOT NULL DEFAULT  '0.00000'";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			$version = '5.2.2';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}
		if ( $version == '5.2.2' ) {

			$sql = "CREATE TABLE IF NOT EXISTS pkg_firewall (
				  id int(11) NOT NULL AUTO_INCREMENT,
				  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  ip varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
				  action int(1) NOT NULL,
				  description text NOT NULL,
				  PRIMARY KEY (id)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			
			$sql = "DELETE FROM pkg_group_module WHERE id_module = (SELECT id FROM pkg_module WHERE module = 'webphone') ";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			$sql = "DELETE FROM pkg_module WHERE module = 'webphone'";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.2.3';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}
		
		if ( $version == '5.2.3' ) {

			$sql = "UPDATE pkg_user SET password = sha1(password) WHERE id_group IN (SELECT id FROM pkg_group_user WHERE id_user_type = 1)";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			$version = '5.2.4';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.2.4' ) {

			$sql = "ALTER TABLE  `pkg_user` ADD  `id_sacado_sac` INT( 11 ) NULL DEFAULT NULL";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			$sql = "ALTER TABLE  `pkg_method_pay` ADD  `SLAppToken` VARCHAR( 50 ) NULL DEFAULT NULL ,
				ADD  `SLAccessToken` VARCHAR( 50 ) NULL DEFAULT NULL ,
				ADD  `SLSecret` VARCHAR( 50 ) NULL DEFAULT NULL,
				ADD  `SLIdProduto` INT( 11 ) NULL DEFAULT NULL,
				ADD  `SLvalidationtoken` VARCHAR( 100 ) NULL DEFAULT NULL ;";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			$version = '5.2.5';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.2.5' ) {

			$sql = "ALTER TABLE  `pkg_log` CHANGE  `description`  `description` TEXT CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.2.6';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.2.6' ) {

			$password = Util::gerarSenha(20,true,true,true,false);

			$sql = "CREATE USER 'mbillingUser'@'localhost' IDENTIFIED BY '$password';
			GRANT FILE ON * . * TO  'mbillingUser'@'localhost' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;
			GRANT ALL PRIVILEGES ON  `mbilling` . * TO  'mbillingUser'@'localhost' WITH GRANT OPTION ;
			FLUSH PRIVILEGES;";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			exec('echo "[general]
dbhost = 127.0.0.1
dbname = mbilling
dbuser = mbillingUser
dbpass = '.$password.'
" > /etc/asterisk/res_config_mysql.conf');

			exec("echo '
<Directory \"/var/www/html/mbilling/yii\">
    deny from all
</Directory>
' >> /etc/httpd/conf/httpd.conf");	
			$version = '5.3.0';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.3.0' ) {

			$sql = "INSERT INTO pkg_configuration VALUES (NULL, 'Generate password automatically on Signup Form', 'signup_auto_pass', '0', 'Set the number of caracter to password. EX: if you have pass with 10 digits, set it to 10. Minimo value 6', 'global', '1');";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			try {
				exec("rm -rf /var/www/html/mbilling/protected/Freeswitch*");	
			} catch (Exception $e) {
				
			}

			$version = '5.3.1';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.3.1' ) {

			$sql = "ALTER TABLE  `pkg_prefix` CHANGE  `prefix`  `prefix` VARCHAR( 18 ) NOT NULL;";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$version = '5.3.2';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.3.2' ) {

			$sql = "ALTER TABLE  `pkg_rate_callshop` ADD  `minimal_time_charge` INT( 11 ) DEFAULT  '0'";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$sql = "INSERT INTO pkg_configuration VALUES (NULL, 'Social Media Network to show in customer panel', 'social_media_network', '', 'Social Media Network to show in customer panel', 'global', '1');";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			

			$version = '5.3.3';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}
		if ( $version == '5.3.3' ) {				

			$version = '5.3.4';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}
		if ( $version == '5.3.4' ) {				

			$sql = "ALTER TABLE  `pkg_did` ADD  `send_to_callback_1` TINYINT( 1 ) NOT NULL DEFAULT  '0',
			ADD  `send_to_callback_2` TINYINT( 1 ) NOT NULL DEFAULT  '0',
			ADD  `send_to_callback_3` TINYINT( 1 ) NOT NULL DEFAULT  '0'";

			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			
			$version = '5.3.5';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}

		if ( $version == '5.3.5' ) {				

			$sql = "ALTER TABLE  `pkg_sip` ADD  `ringfalse` TINYINT( 1 ) NOT NULL DEFAULT  '0'";

			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			
			$version = '5.3.6';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}


		if ( $version == '5.3.6' ) {				

			$sql = "ALTER TABLE  `pkg_user` 
			ADD  `state_number` VARCHAR( 40 ) DEFAULT NULL AFTER  `company_website` ,
			
			ADD  `disk_space` INT( 10 ) NOT NULL DEFAULT  '-1',
			ADD  `sipaccountlimit` INT( 10 ) NOT NULL DEFAULT  '-1',
			ADD  `calllimit` INT( 10 ) NOT NULL DEFAULT  '-1',
			ADD mix_monitor_format VARCHAR(5) DEFAULT 'gsm';
			ALTER TABLE  `pkg_sip` ADD  `record_call` TINYINT( 1 ) NOT NULL DEFAULT  '0';
			";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$sql = 'INSERT INTO pkg_templatemail VALUES

				(NULL, 1, \'services_unpaid\', \'usuario\', \'VoIP\', \'Aviso de Vencimento de serviço\', \'<p>Olá $firstname$ $lastname$, </p>\r\n<p>Você tem serviços com vencimento em aberto e não possiu saldo para o pagamento. Por favor entre no link $service_pending_url$ para iniciar o pagamento. </p>\r\n<br> \r\n<p>Atenciosamente,<br>\r\n \', \'br\'),
				(NULL, 1, \'services_unpaid\', \'usuario\', \'VoIP\', \'Aviso de Vencimiento de servicio\', \'<p>Hola $firstname$ $lastname$, </p>\r\n<p>Usted tien servicios por vencer o vencido. Por favor entre en este link $service_pending_url$ para iniciar el pago.</p> \r\n<p>Saludos,<br>\r\n \', \'es\'),
				(NULL, 1, \'services_unpaid\', \'username\', \'VoIP\', \'Balance Due Alert for your\', \'<p>Hello $firstname$ $lastname$, </p>\r\n<p>You have services pendent. Please use this link $service_pending_url$ to start the payment</p>\r\n\r\n<br> \r\n<p>Best Regards<br>\r\n \', \'en\'),

				(NULL, 1, \'services_activation\', \'usuario\', \'VoIP\', \'Ativação de serviço\', \'<p>Olá $firstname$ $lastname$, </p>\r\n<p>Foi ativado o serviço $service_name$ com valor de $service_price$. </p>\r\n<br>\r\n\r\n<p>Este valor sera descontado do credito de sua conta automaticamente todos os meses.</p>\r\n\r\n<br> \r\n<p>Atenciosamente,<br>\r\n \', \'br\'),
				(NULL, 1, \'services_activation\', \'usuario\', \'VoIP\', \'Activacion de servicio\', \'<p>Hola $firstname$ $lastname$, </p>\r\n<p>Fue activado el servicio $service_name$ con importe $service_price$.</p>\r\n<br>\r\n\r\n<p>Este importe sera descontado del credito de su cuenta automaticamente todos los meses..</p>\r\n\r\n<br> \r\n<p>Saludos,<br>\r\n \', \'es\'),
				(NULL, 1, \'services_activation\', \'username\', \'VoIP\', \'Service activation\', \'<p>Hello $firstname$ $lastname$, </p>\r\n<p>The service $service_name$ was activated. Service price: $service_price$ .</p>\r\n<br>\r\n\r\n<p>This amount will be charged of your account every month.</p>\r\n\r\n<br> \r\n<p>Best Regards<br>\r\n \', \'en\'),

				(NULL, 1, \'services_pending\', \'usuario\', \'VoIP\', \'Serviço pendente de pagamento\', \'<p>Olá $firstname$ $lastname$, </p>\r\n<p>Foi solicitado o serviço $service_name$ com valor de $service_price$. </p>\r\n
				<p>A ativaçao do serviço esta pendente de pagamento.</p>\r\n
				<p>Link para pagamento $service_pending_url$.</p>\r\n
				<br>\r\n\r\n<p></p>\r\n\r\n<br> \r\n<p>Atenciosamente,<br>\r\n \', \'br\'),
				(NULL, 1, \'services_pending\', \'usuario\', \'VoIP\', \'Servicio pendente de pagao\', \'<p>Hola $firstname$ $lastname$, </p>\r\n<p>Fue solicitado la activacion del servicio $service_name$ con importe $service_price$.</p>\r\n<p>La activacion del servicio esta pendiente de pago.</p>\r\n
				<p>Link para el pago: $service_pending_url$.</p>\r\n<br>\r\n\r\n<p>.</p>\r\n\r\n<br> \r\n<p>Saludos,<br>\r\n \', \'es\'),
				(NULL, 1, \'services_pending\', \'username\', \'VoIP\', \'Service pending\', \'<p>Hello $firstname$ $lastname$, </p>\r\n<p>The service $service_name$ was pending. Service price: $service_price$ .</p>\r\n
				<p>Please make the payment to active the service.</p>\r\n
				<p>Payment Link:  $service_pending_url$.</p>\r\n
				<br>\r\n\r\n<br> \r\n<p>Best Regards<br>\r\n \', \'en\'),

				(NULL, 1, \'services_released\', \'usuario\', \'VoIP\', \'Cancelamento de serviço\', \'<p>Olá $firstname$ $lastname$, </p>\r\n<p>Foi desativado o serviço $service_name$ com valor de $service_price$. </p>\r\n<br>\r\n\r\n<p></p>\r\n\r\n<br> \r\n<p>Atenciosamente,<br>\r\n \', \'br\'),
				(NULL, 1, \'services_released\', \'usuario\', \'VoIP\', \'Baja de servicio\', \'<p>Hola $firstname$ $lastname$, </p>\r\n<p>Fue dado de baja el servicio $service_name$ con importe $service_price$.</p>\r\n<br>\r\n\r\n<p>.</p>\r\n\r\n<br> \r\n<p>Saludos,<br>\r\n \', \'es\'),
				(NULL, 1, \'services_released\', \'username\', \'VoIP\', \'Service canceled\', \'<p>Hello $firstname$ $lastname$, </p>\r\n<p>The service $service_name$ was canceled. Service price: $service_price$ .</p>\r\n<br>\r\n\r\n<br> \r\n<p>Best Regards<br>\r\n \', \'en\'),

				(NULL, 1, \'services_paid\', \'usuario\', \'VoIP\', \'Serviço Pago\', \'<p>Olá $firstname$ $lastname$, </p>\r\n<p>Foi pago o serviço $service_name$ com valor de $service_price$. </p>\r\n<br>\r\n\r\n<p></p>\r\n\r\n<br> \r\n<p>Atenciosamente,<br>\r\n \', \'br\'),
				(NULL, 1, \'services_paid\', \'usuario\', \'VoIP\', \'Servicio pago\', \'<p>Hola $firstname$ $lastname$, </p>\r\n<p>Fue pago el servicio $service_name$ con importe $service_price$.</p>\r\n<br>\r\n\r\n<p>.</p>\r\n\r\n<br> \r\n<p>Saludos,<br>\r\n \', \'es\'),
				(NULL, 1, \'services_paid\', \'username\', \'VoIP\', \'Service paid\', \'<p>Hello $firstname$ $lastname$, </p>\r\n<p>The service $service_name$ was paid. Service price: $service_price$ .</p>\r\n<br>\r\n\r\n<br> \r\n<p>Best Regards<br>\r\n \', \'en\'),


				(NULL, 1, \'user_disk_space\', \'usuario\', \'VoIP\', \'Armazenamento em disco superado\', \'<p>Olá $firstname$ $lastname$, </p>\r\n<p>Sua conta VoIP número $cardnumber$ superou o limite de $disk_usage_limit$ GB.</p>\r\n<br>\r\n\r\n<p>Para manter o serviço foi deletado automaticamente os audios anteriores a $time_deleted$.</p>\r\n\r\n<br> \r\n<p>Atenciosamente,<br>\r\n \', \'br\'),
				(NULL, 1, \'user_disk_space\', \'usuario\', \'VoIP\', \'Armazenamento en disco superado\', \'<p>Hola $firstname$ $lastname$, </p>\r\n<p>Su cuenta VoIP número $cardnumber$ supero el limite de $disk_usage_limit$ GB.</p>\r\n<br>\r\n\r\n<p>Para mantener el servicio fue borrado automaticamente los audios anteriores a $time_deleted$.</p>\r\n\r\n<br> \r\n<p>Saludos,<br>\r\n \', \'es\'),
				(NULL, 1, \'user_disk_space\', \'username\', \'VoIP\', \'Disk space surpassed\', \'<p>Hello $firstname$ $lastname$, </p>\r\n<p>Your account $cardnumber$ surpassed the disk space limit of $disk_usage_limit$ GB.</p>\r\n<br>\r\n\r\n<p>To keep the service was deleted the records before than $time_deleted$.</p>\r\n\r\n<br> \r\n<p>Best Regards<br>\r\n \', \'en\');';
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$sql = "
			ALTER TABLE  `pkg_campaign` ADD  `id_plan` INT( 11 ) NULL DEFAULT NULL AFTER  `id_user`;
			ALTER TABLE `pkg_campaign`  ADD CONSTRAINT `fk_pkg_plan_pkg_campaign` FOREIGN KEY (`id_plan`) REFERENCES `pkg_plan` (`id`);
			";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$sql = "INSERT INTO pkg_configuration VALUES 
				(NULL, 'Link to signup terms', 'accept_terms_link', '', 'Set a link to signup terms', 'global', '1'),
				(NULL, 'Auto gernerate user in Signup form', 'auto_generate_user_signup', '1', 'Auto gernerate user in Signup form', 'global', '1'),
				(NULL, 'Notificação de  Pagamento de serviços', 'service_daytopay', '5', 'Total Dias anterior ao vencimento que o MagnusBilling avisara o cliente para pagar os serviços', 'global', '1');
				";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}

			$sql = "CREATE TABLE IF NOT EXISTS `pkg_estados` (
					  `id` int(11) NOT NULL,
					  `nome` varchar(45) NOT NULL,
					  `sigla` varchar(2) NOT NULL,
					  PRIMARY KEY (`id`,`sigla`),
					  UNIQUE KEY `sigla_UNIQUE` (`sigla`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;

					INSERT INTO `pkg_estados` (`id`, `nome`, `sigla`) VALUES
					(1, 'Acre', 'AC'),
					(2, 'Alagoas', 'AL'),
					(3, 'Amazonas', 'AM'),
					(4, 'Amapá', 'AP'),
					(5, 'Bahia', 'BA'),
					(6, 'Ceará', 'CE'),
					(7, 'Distrito Federal', 'DF'),
					(8, 'Espírito Santo', 'ES'),
					(9, 'Goiás', 'GO'),
					(10, 'Maranhão', 'MA'),
					(11, 'Minas Gerais', 'MG'),
					(12, 'Mato Grosso do Sul', 'MS'),
					(13, 'Mato Grosso', 'MT'),
					(14, 'Pará', 'PA'),
					(15, 'Paraíba', 'PB'),
					(16, 'Pernambuco', 'PE'),
					(17, 'Piauí', 'PI'),
					(18, 'Paraná', 'PR'),
					(19, 'Rio de Janeiro', 'RJ'),
					(20, 'Rio Grande do Norte', 'RN'),
					(21, 'Rondônia', 'RO'),
					(22, 'Roraima', 'RR'),
					(23, 'Rio Grande do Sul', 'RS'),
					(24, 'Santa Catarina', 'SC'),
					(25, 'Sergipe', 'SE'),
					(26, 'São Paulo', 'SP'),
					(27, 'Tocantins', 'TO');";
			try {
				Yii::app()->db->createCommand( $sql )->execute();
			} catch ( Exception $e ) {

			}
			/*
			$sql = "INSERT INTO pkg_module VALUES (NULL, 't(''Services'')', NULL, 'prefixs', NULL)";
			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}
			$idServiceModule = Yii::app()->db->lastInsertID;
			

			$sql = "INSERT INTO pkg_group_module VALUES ((SELECT id FROM pkg_group_user WHERE id_user_type = 1 LIMIT 1), '".$idServiceModule."', 'crud', '1', '1', '1');";
			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}

			$sql = "INSERT INTO pkg_module VALUES (NULL, 't(''Services'')', 'services', 'offer', '".$idServiceModule."')";
			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}
			$idSubModule = Yii::app()->db->lastInsertID;
			

			$sql = "INSERT INTO pkg_group_module VALUES ((SELECT id FROM pkg_group_user WHERE id_user_type = 1 LIMIT 1), '".$idSubModule."', 'crud', '1', '1', '1');";
			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}

			$sql = "INSERT INTO pkg_module VALUES (NULL, 't(''Services Use'')', 'servicesuse', 'offer', '".$idServiceModule."')";
			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}
			$idSubModule = Yii::app()->db->lastInsertID;
			

			$sql = "INSERT INTO pkg_group_module VALUES ((SELECT id FROM pkg_group_user WHERE id_user_type = 1 LIMIT 1), '".$idSubModule."', 'crud', '1', '1', '1');";
			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}
			*/

			$sql = "
				CREATE TABLE IF NOT EXISTS `pkg_services` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(100) NOT NULL,
					  `type` varchar(50) NOT NULL,
					  `status` tinyint(1) NOT NULL DEFAULT '1',
					  `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `description` text,
					  `disk_space` int(11) DEFAULT NULL,
					  `sipaccountlimit` int(11) DEFAULT NULL,
					  `calllimit` int(11) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

				CREATE TABLE IF NOT EXISTS `pkg_services_module` (
				  `id_services` int(11) NOT NULL,
				  `id_module` int(11) NOT NULL,
				  `action` varchar(45) NOT NULL,
				  `show_menu` tinyint(1) NOT NULL DEFAULT '1',
				  `createShortCut` tinyint(1) NOT NULL DEFAULT '0',
				  `createQuickStart` tinyint(1) NOT NULL DEFAULT '0',
				  PRIMARY KEY (`id_services`,`id_module`),
				  KEY `fk_pkg_services_module_pkg_module` (`id_module`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;


				ALTER TABLE `pkg_services_module`
				  ADD CONSTRAINT `fk_pkg_services_pkg_services_module` FOREIGN KEY (`id_services`) REFERENCES `pkg_services` (`id`),
				  ADD CONSTRAINT `fk_pkg_services_module_pkg_module` FOREIGN KEY (`id_module`) REFERENCES `pkg_module` (`id`);	

			

				CREATE TABLE IF NOT EXISTS `pkg_services_use` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `id_user` int(11) DEFAULT NULL,
				  `id_services` int(11) NOT NULL,
				  `reservationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `releasedate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
				  `status` int(11) DEFAULT '0',
				  `month_payed` int(11) DEFAULT '0',
				  `reminded` tinyint(4) NOT NULL DEFAULT '0',
				  `id_method` int(11) DEFAULT NULL,
				  PRIMARY KEY (`id`),
				  KEY `fk_pkg_user_pkg_services_use` (`id_user`),
				  KEY `fk_pkg_services_pkg_services_use` (`id_services`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

				ALTER TABLE `pkg_services_use`
				  ADD CONSTRAINT `fk_pkg_services_pkg_services_use` FOREIGN KEY (`id_services`) REFERENCES `pkg_services` (`id`),
				  ADD CONSTRAINT `fk_pkg_user_pkg_services_use` FOREIGN KEY (`id_user`) REFERENCES `pkg_user` (`id`);


				CREATE TABLE IF NOT EXISTS `pkg_services_plan` (
				  `id_services` int(11) NOT NULL,
				  `id_plan` int(11) NOT NULL,
				  PRIMARY KEY (`id_services`,`id_plan`),
				  KEY `fk_pkg_services_pkg_services_plan` (`id_services`),
				  KEY `fk_pkg_plan_pkg_services_plan` (`id_plan`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;


				ALTER TABLE `pkg_services_plan`
				  ADD CONSTRAINT `fk_pkg_services_pkg_services_plan` FOREIGN KEY (`id_services`) REFERENCES `pkg_services` (`id`) ON DELETE CASCADE,
				  ADD CONSTRAINT `fk_pkg_plan_pkg_services_plan` FOREIGN KEY (`id_plan`) REFERENCES `pkg_plan` (`id`) ON DELETE CASCADE;";



			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}

			$version = '5.4.0';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}
		if ( $version == '5.4.0' ) {
			$sql = "CREATE TABLE IF NOT EXISTS `pkg_group_user_group` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
				  	`id_group_user` int(11) NOT NULL,
				  	`id_group` int(11) NOT NULL,
				  	PRIMARY KEY (`id`),
				  	KEY `fk_pkg_pkg_group_user_pkg_group` (`id_group_user`),
				  	KEY `fk_pkg_group_pkg_pkg_group_user_group` (`id_group`)
					) 	ENGINE=InnoDB DEFAULT CHARSET=utf8;


				ALTER TABLE `pkg_group_user_group`
				  ADD CONSTRAINT `fk_pkg_pkg_group_user_pkg_group` FOREIGN KEY (`id_group_user`) REFERENCES `pkg_group_user` (`id`) ON DELETE CASCADE,
				  ADD CONSTRAINT `fk_pkg_group_pkg_pkg_group_user_group` FOREIGN KEY (`id_group`) REFERENCES `pkg_group_user` (`id`) ON DELETE CASCADE;";

			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}

			$sql = "INSERT INTO pkg_module VALUES (NULL, 't(''Group to Admins'')', 'groupusergroup', 'prefixs', 12)";
			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}
			$idServiceModule = Yii::app()->db->lastInsertID;
			

			$sql = "INSERT INTO pkg_group_module VALUES ((SELECT id FROM pkg_group_user WHERE id_user_type = 1 LIMIT 1), '".$idServiceModule."', 'crud', '1', '1', '1');";
			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}

			$sql = "ALTER TABLE  `pkg_user` CHANGE  `address`  `address` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;";
			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}
			$sql = "INSERT INTO `pkg_configuration`  VALUES (NULL, 'Start User Call Limit', 'start_user_call_limit', '-1', 'Default call limit for new user', 'global', '0');";
			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}
			$version = '5.4.1';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}
		if ( $version == '5.4.1' ) {
			$sql = "ALTER TABLE  `pkg_did` ADD  `cbr` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `billingtype`,
			ADD `cbr_em` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `initblock`,
			ADD `cbr_ua` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `initblock`,
			ADD  `TimeOfDay_monFri` VARCHAR( 150 ) NULL DEFAULT NULL ,
			ADD  `TimeOfDay_sat` VARCHAR( 150 ) NULL DEFAULT NULL ,
			ADD  `TimeOfDay_sun` VARCHAR(150 ) NULL DEFAULT NULL ,
			ADD  `workaudio` VARCHAR( 150 ) NULL DEFAULT NULL ,
			ADD  `noworkaudio` VARCHAR( 150 ) NULL DEFAULT NULL;
			UPDATE  `pkg_did` SET  `TimeOfDay_monFri` =  '09:00-12:00|14:00-18:00';
			UPDATE  `pkg_did` SET  `TimeOfDay_sat` =  '09:00-12:00';
			UPDATE  `pkg_did` SET  `TimeOfDay_sun` =  '00:00';
			ALTER TABLE  `pkg_callback` ADD  `id_did` INT( 11 ) NOT NULL AFTER  `id_user`;
			";
			try {
				Yii::app()->db->createCommand($sql)->execute();
			} catch (Exception $e) {
				
			}
			$version = '5.4.2';
			$sql = "UPDATE pkg_configuration SET config_value = '".$version."' WHERE config_key = 'version' ";
			Yii::app()->db->createCommand( $sql )->execute();
		}
	}	
}