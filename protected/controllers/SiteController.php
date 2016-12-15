<?php
/**
 * Actions of module "Pedido".
 *
 * MagnusBilling <info@magnusbilling.com>
 * 05/06/2013
 */

class SiteController extends BaseController
{
	public function actionIndex()
	{
		$config = LoadConfig::getConfig();

		if (isset($_GET['paypal'])) {
			echo isset($config['global']['paypal-softphone']) ? $config['global']['paypal-softphone'] : 0;			
			exit;
		}
		if (isset($_GET['callback'])) {
			echo isset($config['global']['callback-softphone']) ? $config['global']['callback-softphone'] : 0;						
			exit;
		}

		$base_language = $config['global']['base_language'];
		echo 'window.lang = '.json_encode($base_language).';';
		Yii::app()->session['language'] = $base_language;
		Yii::app()->setLanguage(Yii::app()->session['language']);

		$template = $config['global']['template'];
		echo 'window.theme = '.json_encode($template).';';
		Yii::app()->session['theme'] = $template;

		$layout = $config['global']['layout'];
		echo 'window.layout = '.json_encode($layout).';';
		Yii::app()->session['layout'] = $layout;

		$wallpaper = $config['global']['wallpaper'];
		echo 'window.wallpaper = '.json_encode($wallpaper).';';
		Yii::app()->session['wallpaper'] = $wallpaper;
		echo 'window.colorMenu = '.json_encode($config['global']['color_menu']).';';
		echo 'window.moduleExtra = '.json_encode($config['global']['module_extra']).';';
	}	
}
?>