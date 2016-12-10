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
$configFile = '/etc/asterisk/res_config_mysql.conf';
$array = parse_ini_file($configFile);
return array(
	'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name' => 'MagnusBilling',
	'preload' => array('log'),
	'language' => 'pt_BR',
	'sourceLanguage' => 'pt_BR',
	# autoload das models e componentes
	'import' => array(
		'application.models.*',
		'application.components.*',
		'ext.yii-mail.YiiMailMessage',
	),
	# application components
	'components' => array(
		# criacao de urls amigaveis
		'urlManager' => array(
			'urlFormat' => 'path',
			'rules' => array(
				'<controller:\w+>/<id:\d+>' => '<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
				'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
			),
		),
		'mail' => array(
            'class' => 'ext.yii-mail.YiiMail',
            'transportType' => 'smtp',
            'transportOptions' => array(
		        'host' => '',
	            'encryption' => '',
	            'username' => '',
	            'password' => '',
		        'port' => '',
		        'encryption'=>'',
    		),           
            'viewPath' => 'application.views.mails',
		    'logging' => true,
		    'dryRun' => false
        ),
		# configuracao da conexao com banco de dados
		'db'=>array(
			'connectionString' => 'mysql:host='.$array['dbhost'].';dbname='.$array['dbname'].'',
			'emulatePrepare' => true,
			'username' => $array['dbuser'],
			'password' => $array['dbpass'],
			'charset' => 'utf8',
			//'enableProfiling' => true,
		),
		'coreMessages' => array(
			'basePath' => 'resources/locale/php',
		),
		# exibicao dos logs de erro
		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
				array(
					'class' => 'CFileLogRoute',
					'levels' => 'error, warning, info, fatal',
				),
				# desabilitar para exibir logs da aplicacao

				/*array(
					'class'=>'CWebLogRoute',
				),*/

			),
		),
	),
);
?>