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
class SmsCommand extends CConsoleCommand 
{

	var $success;
	public $nameRoot = 'rows';
	public $nameCount = 'count';
	public $nameSuccess = 'success';
	public $nameMsg = 'msg';

	public function run($args)
	{
		if (isset($args[0])){
			if($args[0] == 'log')
				define('DEBUG', 1);
			elseif ($args[0] == 'logAll') {
				define('DEBUG', 2);
			}
		}			
		else
			define('DEBUG', 0);

		if (!defined('PID'))
		define("PID", "/var/run/magnus/SmsPid.php");


		if(DEBUG == 0){
			if (Process :: isActive()) {
					echo " PROCESS IS ACTIVE ";
					Yii::log(" PROCESS IS ACTIVE ", 'error');				
				die();
			} else {
				Process :: activate();
			}
		}
		
		$UNIX_TIMESTAMP = "UNIX_TIMESTAMP(";

		$tab_day = array(1 => 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
		$num_day = date('N');
		$name_day = $tab_day[$num_day];

		$filter = isset($args[1]) ? " AND pkg_campaign.name = '$args[1]'" : '';


		$sql ="SELECT pkg_campaign.id, frequency, pkg_campaign.name FROM pkg_campaign  JOIN pkg_user  ON pkg_campaign.id_user = pkg_user.id
			WHERE  pkg_campaign.status = 1 
			AND pkg_campaign.startingdate <= '".date('Y-m-d H:i:s')."' AND pkg_campaign.expirationdate > '".date('Y-m-d H:i:s')."' 
			AND pkg_campaign.$name_day = 1 AND  pkg_campaign.daily_start_time <= '".date('H:i:s')."'  AND pkg_campaign.daily_stop_time > '".date('H:i:s')."' 
			AND  pkg_campaign.type = 0 $filter";
		$campaignResult = Yii::app()->db->createCommand($sql)->queryAll();
		if(DEBUG == 2) echo $sql."\n\n\n";
		
		if(DEBUG >= 1) echo "\nFound " . count($campaignResult) . " Campaign\n\n";
	
		foreach ($campaignResult as $campaign) {

			

			if(DEBUG >= 1) echo  "SEARCH NUMBER IN CAMPAIGN ". $campaign['name'] ."\n";
			
			if (isset($args[1]) && $campaign['name'] != $args[1]) {
				continue;
			}
			
			$nbpage = $campaign['frequency'];


			$sql ="SELECT pkg_phonenumber.id, pkg_phonenumber.number, pkg_phonenumber.name, pkg_campaign.description, pkg_user.username, pkg_user.password, pkg_user.credit, pkg_user.id AS id_user 
			FROM pkg_phonenumber , pkg_phonebook , pkg_campaign_phonebook, pkg_campaign, pkg_user 
			WHERE pkg_phonenumber.id_phonebook = pkg_phonebook.id AND pkg_campaign_phonebook.id_phonebook = pkg_phonebook.id 
			AND pkg_campaign_phonebook.id_campaign = pkg_campaign.id AND pkg_campaign.id_user = pkg_user.id AND pkg_campaign.status = 1 
			AND pkg_campaign.startingdate <= '".date('Y-m-d H:i:s')."' AND pkg_campaign.expirationdate > '".date('Y-m-d H:i:s')."' 
			AND pkg_campaign.$name_day = 1 AND  pkg_campaign.daily_start_time <= '".date('H:i:s')."'  AND pkg_campaign.daily_stop_time > '".date('H:i:s')."' 
			AND pkg_phonenumber.status = 1  AND  pkg_phonenumber.creationdate < '".date('Y-m-d H:i:s')."' AND pkg_user.credit > 1 AND pkg_campaign.type = 0
			AND pkg_campaign.id = ".$campaign['id']." ORDER BY RAND( ) 
			LIMIT 0, $nbpage  ";

			if(DEBUG == 2) echo $sql."\n\n";
			
			$smsResult = Yii::app()->db->createCommand($sql)->queryAll();
			if(DEBUG >= 1) echo 'Found ' . count($smsResult) . ' Numbers in Campaign '."\n";

		
			if (count($smsResult) == 0)
			{
				if(DEBUG >= 1) echo  "NO PHONE FOR SEND SMS"."\n\n\n";
				continue;
			}

			include_once('/var/www/html/mbilling/protected/controllers/SmsSendController.php');	
		

			$send = new SmsSendController(false);
			foreach ($smsResult as $sms) 
			{

				if (date("s") > 55){
					exit;
				}
		
				if(CreditUser :: checkGlobalCredit($sms['id_user']) === false)
				{
					if(DEBUG >= 1 ) echo " USER NO CREDIT FOR CALL ".$sms['username']."\n\n\n";
					continue;
				}

				$text = preg_replace("/\%name\%/", $sms['name'], $sms['description']);

				if ($sms['number'] == '') {
					$sql = "DELETE FROM pkg_phonenumber WHERE id =".$sms['id'];
					Yii::app()->db->createCommand($sql)->execute();
					continue;
				}
				echo $sms['username'] ." -" . $sms['password'] ." -" . $sms['number'] ." -" . $text;
				$send->actionIndex($sms['username'], $sms['password'], $sms['number'], $text, $sms['id'],true);
			}
		}		
	}
}