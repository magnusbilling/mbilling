<?php
/**
 * Acoes do modulo "Campaign".
 *
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
 * 28/10/2012
 */

class CampaignController extends Controller
{
	public $attributeOrder     = 'id DESC';
	public $nameModelRelated   = 'CampaignPhonebook';
	public $nameFkRelated      = 'id_campaign';
	public $nameOtherFkRelated = 'id_phonebook';
	public $extraValues        = array('idUser' => 'username');


	public $filterByUser        = true;
	public $defaultFilterByUser = 'b.id_user';
	public $join                = 'JOIN pkg_user b ON t.id_user = b.id';
    	

	public $fieldsInvisibleClient = array(
		'id_user',
		'idCardusername',
		'enable_max_call',
		'nb_callmade',
		'secondusedreal'
		);

	public function init()
	{
		$this->instanceModel = new Campaign;
		$this->abstractModel = Campaign::model();
		$this->abstractModelRelated = CampaignPhonebook::model();
		$this->titleReport   = Yii::t('yii','Campaign');
		parent::init();
	}

	public function extraFilter ($filter)
	{
		$filter = isset($this->filter) ? $filter.$this->filter : $filter;

		$filter = $filter . ' AND ' .$this->defaultFilter;
		$filter = $this->filterReplace($filter);

		 if(Yii::app()->getSession()->get('user_type')  > 1 && $this->filterByUser)
        {
            $filter .= ' AND ('. $this->defaultFilterByUser . ' = '.Yii::app()->getSession()->get('id_user');
            $filter .= ' OR t.id_user = '.Yii::app()->getSession()->get('id_user').')';
        }

		return $filter;
	}


	public function getAttributesModels($models, $itemsExtras = array())
	{
		$attributes = false;
		$namePk = $this->abstractModel->primaryKey();
		foreach ($models as $key => $item)
		{
		    	$attributes[$key] = $item->attributes;	  		

	    	    	$itemOption= explode("|", $item->{'forward_number'});
			$attributes[$key]['type_0'] = $itemOption[0];


			if (isset($itemOption[1]) && $itemOption[0] == 'ivr') {
				$attributes[$key]['id_ivr_0'] = $itemOption[1];
				$sql = "SELECT name FROM pkg_ivr WHERE id = :id";
				try {
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":id", $itemOption[1], PDO::PARAM_STR);
					$result = $command->queryAll();

					$attributes[$key]['id_ivr_0'.'_name'] = isset($result[0]['name']) ? $result[0]['name'] : '';
				} catch (Exception $e) {
					
				}
			}
			else if (isset($itemOption[1]) && $itemOption[0] == 'queue') {
				$attributes[$key]['id_queue_0'] = $itemOption[1];
				$sql = "SELECT name FROM pkg_queue WHERE id = :id";
				try {
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":id", $itemOption[1], PDO::PARAM_STR);
					$result = $command->queryAll();
					$attributes[$key]['id_queue_0'.'_name'] = isset($result[0]['name']) ? $result[0]['name'] : '';
				} catch (Exception $e) {
					
				}
			}
			else if (isset($itemOption[1]) && $itemOption[0] == 'sip') {
				$attributes[$key]['id_sip_0'] = $itemOption[1];
				$sql = "SELECT name FROM pkg_sip WHERE id = :id";
				try {
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":id", $itemOption[1], PDO::PARAM_STR);
					$result = $command->queryAll();
					$attributes[$key]['id_sip_0'.'_name'] = isset($result[0]['name']) ? $result[0]['name'] : '';
				} catch (Exception $e) {
					
				}
				
			}
			else if (isset($itemOption[1]) && preg_match("/number|group|custom|hangup/", $itemOption[0])) {
				$attributes[$key]['extension_0'] = $itemOption[1];
			}



		    if(isset($_SESSION['isClient']) && $_SESSION['isClient'])
			{
				foreach ($this->fieldsInvisibleClient as $field) {
       				unset($attributes[$key][$field]);
				}
			}
			
			if(isset($_SESSION['isAgent']) && $_SESSION['isAgent'])
			{
				foreach ($this->fieldsInvisibleAgent as $field) {
       				unset($attributes[$key][$field]);
				}
			}

		    if(!is_array($namePk) && $this->nameOtherFkRelated && get_class($this->abstractModel) === get_class($item)) {
		        if(count($this->extraFieldsRelated)) {
		            $resultSubRecords = $this->abstractModelRelated->findAll(array(
		                    'select' => implode(',', $this->extraFieldsRelated),
		                    'condition' => $this->nameFkRelated . ' = ' . $attributes[$key][$namePk]
		            ));

		            $subRecords = array();

		            if(count($this->extraValuesOtherRelated)) {
		                $attributesSubRecords = array();

		                foreach($resultSubRecords as $itemModelSubRecords) {
		                    $attributesSubRecords = $itemModelSubRecords->attributes;

		                    foreach($this->extraValuesOtherRelated as $relationSubRecord => $fieldsSubRecord)
		                    {
		                        $arrFieldsSubRecord = explode(',', $fieldsSubRecord);
		                        foreach($arrFieldsSubRecord as $fieldSubRecord)
		                        {
		                            $attributesSubRecords[$relationSubRecord . $fieldSubRecord] = $itemModelSubRecords->$relationSubRecord ? $itemModelSubRecords->$relationSubRecord->$fieldSubRecord : null;
		                        }
		                    }

		                    array_push($subRecords, $attributesSubRecords);
		                }
		            }
		            else {
		                foreach($resultSubRecords as $modelSubRecords) {
		                    array_push($subRecords, $modelSubRecords->attributes);
		                }
		            }
		        }
		        else {
		            $resultSubRecords = $this->abstractModelRelated->findAll(array(
		                'select' => $this->nameOtherFkRelated,
		                'condition' => $this->nameFkRelated . ' = ' . $attributes[$key][$namePk]
		            ));

		            $subRecords = array();
		            foreach($resultSubRecords as $keyModelSubRecords => $modelSubRecords) {
		                array_push($subRecords, (int) $modelSubRecords->attributes[$this->nameOtherFkRelated]);
		            }
		        }

		        $attributes[$key][$this->nameOtherFkRelated] = $subRecords;
		    }

		    foreach($itemsExtras as $relation => $fields)
		    {
		        $arrFields = explode(',', $fields);
		        foreach($arrFields as $field)
		        {
		            $attributes[$key][$relation . $field] = $item->$relation ? $item->$relation->$field : null;
		        }
		    }
		}

		return $attributes;
	}


	public function getAttributesRequest() {
		$arrPost = array_key_exists($this->nameRoot, $_POST) ? json_decode($_POST[$this->nameRoot], true) : $_POST;

		/*permite salvar quando tem audio e extrafield*/
		$id_phonebook = array();		
		foreach ($arrPost as $key => $value) {
			if($key == 'id_phonebook_array'){
				if (isset($_POST['id_phonebook_array']) && strlen($value) > 0)			
					$arrPost['id_phonebook']  = explode(",", $_POST['id_phonebook_array'] ) ;				
			}			
		};
	
		return $arrPost;
	}

	public function destroyRelated($values)
	{
		$namePk = $this->abstractModel->primaryKey();
		if(array_key_exists(0, $values))
		{
			foreach($values as $value)
			{
				$id = $value[$namePk];

				try {
					$this->abstractModelRelated->deleteAllByAttributes(array(
						$this->nameFkRelated => $id
					));
				}
				catch (Exception $e) {
					$this->success = false;
					$this->msg = $this->getErrorMySql($e);
				}

				if(!$this->success)
				{
					break;
				}

				//deleta os audios da enquete
				$uploaddir = "resources/sounds/";
				$uploadfile = $uploaddir .'idCampaign_'. $id .'.gsm';
				if (file_exists($uploadfile))
				{
					unlink($uploadfile);
				}
			}
		}
		else
		{
			$id = $values[$namePk];

			//deleta os audios da enquete
			$uploaddir = "resources/sounds/";
			$uploadfile = $uploaddir .'idCampaign_'. $id .'.gsm';
			if (file_exists($uploadfile))
			{
				unlink($uploadfile);
			}

			try {
				$this->abstractModelRelated->deleteAllByAttributes(array(
					$this->nameFkRelated => $id
				));
			}
			catch (Exception $e) {
				$this->success = false;
				$this->msg = $this->getErrorMySql($e);
			}
		}
	}

	public function actionQuick()
	{

		$creationdate = $_POST['startingdate'] . ' '.$_POST['startingtime'];

		$name = Yii::app()->session['username'] . '_'.$creationdate;
        	$msj = isset($_POST['sms_text']) ? $_POST['sms_text'] : false;

        	$tipo = $_POST['type'] == 'CALL' ? 1 : 0; 

        	$fields = "(name, startingdate ,expirationdate, id_user, type, description, frequency, daily_start_time)";
        	$value = ":name, :creationdate,'2030-01-01 00:00:00', :id_user, :tipo, :msj, 10, :startingtime";

      	$sql = "INSERT INTO pkg_campaign $fields VALUES ($value)";
      	$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":name", $name, PDO::PARAM_STR);
		$command->bindValue(":creationdate", $creationdate, PDO::PARAM_STR);
		$command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_INT);
		$command->bindValue(":tipo", $tipo, PDO::PARAM_STR);
		$command->bindValue(":msj", $msj, PDO::PARAM_STR);
		$command->bindValue(":startingtime", $startingtime, PDO::PARAM_STR);
		$command->execute();

      	$id_campaign = Yii::app()->db->getLastInsertID();

      	$sql = "INSERT INTO pkg_phonebook (id_user, name, status) VALUES (:id_user, :name, 1)";
      	$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":name", $name, PDO::PARAM_STR);
		$command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_INT);
		$command->execute();

      	$id_phonebook = Yii::app()->db->getLastInsertID();

      	$sql = "INSERT INTO pkg_campaign_phonebook VALUES (:id_campaign, :id_phonebook)";
      	$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id_campaign", $id_campaign, PDO::PARAM_INT);
		$command->bindValue(":id_phonebook", $id_phonebook, PDO::PARAM_INT);
		$command->execute();

        	if($_POST['type'] == 'CALL'){
        		$audio = "resources/sounds/idCampaign_".$id_campaign;
            	$sql = "UPDATE pkg_campaign SET audio = :audio WHERE id = :id_campaign";
            	$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_campaign", $id_campaign, PDO::PARAM_INT);
			$command->bindValue(":audio", $audio, PDO::PARAM_STR);
			$command->execute();
       	}
	

		if (isset($_FILES['csv_path']['tmp_name']) && strlen($_FILES['csv_path']['tmp_name']) > 3 ) {
		
			$handle = fopen($_FILES['csv_path']['tmp_name'], "r");
			$values = array();
			$sql = array();
			while (($row = fgetcsv($handle, 32768, ',')) !== FALSE)
			{
				if (!isset($i)) 
				{
					$rowColunm = $row;
					array_push($rowColunm, 'id_phonebook,creationdate');
					if ($row[0] == 'number') {
						$colunas =  implode(",", $rowColunm);
						$i=true;
						continue;
					}else{
						$colunas = 'number,id_phonebook,creationdate';
					}				
					
				}


				$pattern = array("'é'", "'è'", "'ë'", "'ê'", "'É'", "'È'", "'Ë'", "'Ê'", "'á'",
				 "'à'", "'ä'", "'â'", "'å'", "'Á'", "'À'", "'Ä'", "'Â'", "'Å'", "'ó'", "'ò'", 
				 "'ö'", "'ô'", "'Ó'", "'Ò'", "'Ö'", "'Ô'", "'í'", "'ì'", "'ï'", "'î'", "'Í'", 
				 "'Ì'", "'Ï'", "'Î'", "'ú'", "'ù'", "'ü'", "'û'", "'Ú'", "'Ù'", "'Ü'", "'Û'", 
				 "'ý'", "'ÿ'", "'Ý'", "'ø'", "'Ø'", "'œ'", "'Œ'", "'Æ'", "'ç'", "'Ç'", "'\''", "'#'");

				$row = preg_replace($pattern, "", $row);

				array_push($row, $id_phonebook, $creationdate);
				$values = implode("','", $row);
				$sql[] = "('$values')";
				
			}
			fclose($handle);
			SqlInject::sanitize($sql);
			$sql = 'INSERT INTO pkg_phonenumber ('.$colunas.') VALUES '.implode(',', $sql).';';

			try {
				$this->success = $result = Yii::app()->db->createCommand($sql)->execute();
			}
			catch (Exception $e) {
				$this->success = false;
				$errors = $this->getErrorMySql($e);
			}

			$this->msg = $this->success ? $this->msgSuccess : $errors;

			
		}

		if (isset($_POST['numbers']) && $_POST['numbers'] != '') {
			$numbers = explode("\n", $_POST['numbers']);

			foreach ($numbers as $key => $number) {
                
	               $sql = "INSERT INTO pkg_phonenumber (id_phonebook, number, creationdate) values 
	               				(:id_phonebook, :number, :creationdate) ";
	               $command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_phonebook", $id_phonebook, PDO::PARAM_INT);
				$command->bindValue(":number", $number, PDO::PARAM_STR);
				$command->bindValue(":creationdate", $creationdate, PDO::PARAM_STR);
				$command->execute();
	           }
		}


		if (isset($_FILES['audio_path']['tmp_name']) && strlen($_FILES['audio_path']['tmp_name']) > 3) {

			//import audio torpedo
               $uploaddir = "resources/sounds/";
               if (file_exists($uploaddir .'idCampaign_'. $id_campaign.'.wav')) {
				unlink($uploaddir .'idCampaign_'. $id_campaign.'.wav');
			}

			$typefile = explode('.', $_FILES["audio_path"]["name"]);
			$uploadfile = $uploaddir .'idCampaign_'. $id_campaign .'.'. $typefile[1];
			move_uploaded_file($_FILES["audio_path"]["tmp_name"], $uploadfile);
          }



		echo json_encode(array(
			$this->nameSuccess => $this->success,
			$this->nameMsg => $this->msg
		));

	}

	public function actionTestCampaign()
	{

		if (isset($_POST['id']) && $_POST['id'] > 0) {
			$id_campaign = json_decode($_POST['id']);
		}else{
			echo json_encode(array(
				$this->nameSuccess => false,
				$this->nameMsg => 'Please Select one campaign'
			));
			exit;
		}

		Yii::log($id_campaign, 'info');
		$tab_day = array(1 => 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
		$num_day = date('N');
		$name_day = $tab_day[$num_day];	

		$nbpage = 10;

		$sql ="SELECT pkg_phonenumber.id as pkg_phonenumber_id, pkg_phonenumber.number, pkg_campaign.id as pkg_campaign_id, pkg_campaign.forward_number,
			pkg_user.id , pkg_user.id_plan, pkg_user.username, pkg_campaign.type, pkg_campaign.description, pkg_phonenumber.name, try, pkg_user.credit, restrict_phone , pkg_user.id_user AS id_agent
			FROM pkg_phonenumber , pkg_phonebook , pkg_campaign_phonebook, pkg_campaign, pkg_user 
			WHERE pkg_phonenumber.id_phonebook = pkg_phonebook.id AND pkg_campaign_phonebook.id_phonebook = pkg_phonebook.id 
			AND pkg_campaign_phonebook.id_campaign = pkg_campaign.id AND pkg_campaign.id_user = pkg_user.id AND pkg_campaign.status = 1 
			AND pkg_campaign.startingdate <= '".date('Y-m-d H:i:s')."' AND pkg_campaign.expirationdate > '".date('Y-m-d H:i:s')."' 
			AND pkg_campaign.$name_day = 1 AND  pkg_campaign.daily_start_time <= '".date('H:i:s')."'  AND pkg_campaign.daily_stop_time > '".date('H:i:s')."' 
			AND pkg_phonenumber.status = 1  AND  pkg_phonenumber.creationdate < '".date('Y-m-d H:i:s')."' AND pkg_user.credit > 1
			AND pkg_campaign.id = :id_campaign	LIMIT 0, :nbpage";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id_campaign", $id_campaign, PDO::PARAM_INT);
		$command->bindValue(":nbpage", $nbpage, PDO::PARAM_INT);
		$campaignResult = $command->queryAll();

	

		if (count($campaignResult) == 0) {

			$sql ="SELECT * FROM pkg_campaign  WHERE id = :id_campaign";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_campaign", $id_campaign, PDO::PARAM_INT);
			$resultCampaign = $command->queryAll();

			if ($resultCampaign[0]['status'] == 0) {
				echo json_encode(array(
					$this->nameSuccess => false,
					$this->nameMsg => 'Please active this campaign'
				));
				exit;
			}


			$sql ="SELECT pkg_user.credit, pkg_user.id_plan FROM pkg_campaign  JOIN pkg_user  
							ON pkg_campaign.id_user = pkg_user.id WHERE pkg_campaign.id = :id_campaign";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_campaign", $id_campaign, PDO::PARAM_INT);
			$resultUser = $command->queryAll();

			if ($resultUser[0]['credit'] < 1) {
				echo json_encode(array(
					$this->nameSuccess => false,
					$this->nameMsg => 'The user not have enough credit'
				));
				exit;
			}			

			if ($resultCampaign[0]['startingdate'] > date('Y-m-d H:i:s')) {
				echo json_encode(array(
					$this->nameSuccess => false,
					$this->nameMsg => 'The startdate is in the future'
				));
				exit;
			}

			if ($resultCampaign[0]['expirationdate'] < date('Y-m-d H:i:s')) {
				echo json_encode(array(
					$this->nameSuccess => false,
					$this->nameMsg => 'The expirationdate is in the past'
				));
				exit;
			}

			$sql ="SELECT id_phonebook FROM pkg_campaign_phonebook WHERE id_campaign = :id_campaign";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_campaign", $id_campaign, PDO::PARAM_INT);
			$resultPhonebook = $command->queryAll();

			if (count($resultPhonebook) == 0) {
				echo json_encode(array(
					$this->nameSuccess => false,
					$this->nameMsg => 'Please select one o more phonebook'
				));
				exit;
			}


			if ($resultCampaign[0]['daily_start_time'] > date('H:i:s')) {
				echo json_encode(array(
					$this->nameSuccess => false,
					$this->nameMsg => 'The start time is out of the hour of work'
				));
				exit;
			}

			if ($resultCampaign[0]['daily_stop_time'] < date('H:i:s')) {
				echo json_encode(array(
					$this->nameSuccess => false,
					$this->nameMsg => 'The stop time is out of the hour of work'
				));
				exit;
			}

			if ($resultCampaign[0][$name_day] == 0 ) {
				echo json_encode(array(
					$this->nameSuccess => false,
					$this->nameMsg => 'Campaign is not active to start in '.$name_day
				));
				exit;
			}

			$sql ="SELECT * FROM pkg_phonenumber  WHERE id_phonebook = :id_phonebook";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_phonebook", $resultPhonebook[0]['id_phonebook'], PDO::PARAM_INT);
			$resultPhonenumber = $command->queryAll();

			if (count($resultPhonenumber) == 0) {
				echo json_encode(array(
					$this->nameSuccess => false,
					$this->nameMsg => 'The phonebook not have numbers'
				));
				exit;
			}else {
				$sql ="SELECT * FROM pkg_phonenumber  WHERE id_phonebook = :id_phonebook AND status = 1";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_phonebook", $resultPhonebook[0]['id_phonebook'], PDO::PARAM_INT);
				$resultPhonenumber = $command->queryAll();

				echo json_encode(array(
					$this->nameSuccess => false,
					$this->nameMsg => 'The phonebook have numbers, but NOT have active numbers'
				));
				exit;
			}



			$sql ="SELECT * FROM pkg_phonebook  WHERE id = :id_phonebook AND status = 0";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_phonebook", $resultPhonebook[0]['id_phonebook'], PDO::PARAM_INT);
			$resultPhonebook = $command->queryAll();

			Yii::log($sql, 'info');
			if (count($resultPhonebook) == 0) {
				echo json_encode(array(
					$this->nameSuccess => false,
					$this->nameMsg => 'The phonebook is inactive'
				));
				exit;
			}


			//tem erro mais nao foi identificado

			echo json_encode(array(
				$this->nameSuccess => false,
				$this->nameMsg => 'error'
			));
			exit;

		}



		if ($campaignResult[0]['type'] == 0) {

			$sql = "SELECT * FROM pkg_user WHERE id = :id ";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", $campaignResult[0]['id'], PDO::PARAM_INT);
			$resultUser = $command->queryAll();


			

			if ($resultUser[0]['id_user'] > 1) {
				$sql = "SELECT * FROM pkg_user WHERE id = :id";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id", $resultUser[0]['id_user'], PDO::PARAM_INT);
				$campaingAgent = $command->queryAll();

				$sql ="SELECT id FROM pkg_rate_agent  WHERE id_plan = :id_plan AND id_prefix IN 
								(SELECT id FROM pkg_prefix WHERE prefix LIKE '999%' ORDER BY prefix DESC)";						
			}
			else{
				$sql ="SELECT id FROM pkg_rate  WHERE id_plan = :id_plan AND pkg_rate.status = 1 AND id_prefix = 
								(SELECT id FROM pkg_prefix  WHERE prefix LIKE '999%' LIMIT 1)";
			
			}
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_plan", $resultUser[0]['id_plan'], PDO::PARAM_INT);
			$resultPrefix = $command->queryAll();

			if (count($resultPrefix) == 0) {
				echo json_encode(array(
					$this->nameSuccess => false,
					$this->nameMsg => 'Not existe the prefix 999 to send SMS'
				));
				exit;
			}
		}else{
			//verificar se exite audio
			$uploaddir = "resources/sounds/";
			Yii::log($uploaddir .'idCampaign_'. $id_campaign.'.wav', 'info');
			if ( !file_exists($uploaddir .'idCampaign_'. $id_campaign.'.wav') && !file_exists($uploaddir .'idCampaign_'. $id_campaign.'.gsm')   ) {
				echo json_encode(array(
					$this->nameSuccess => false,
					$this->nameMsg => 'Not existe audio to this Campaign'
				));
				exit;
			}
		}

		echo json_encode(array(
			$this->nameSuccess => true,
			$this->nameMsg => 'Campaign is ok'
		));
	}
}