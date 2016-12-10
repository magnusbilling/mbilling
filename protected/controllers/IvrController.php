<?php
/**
 * Acoes do modulo "Ivr".
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
 * 19/09/2012
 */

class IvrController extends Controller
{
	public $attributeOrder        = 't.id';
	public $extraValues           = array('idUser' => 'username');

	public $fieldsFkReport = array(
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		)
	);
	
	public function init()
	{
		$this->instanceModel = new Ivr;
		$this->abstractModel = Ivr::model();
		$this->titleReport   = Yii::t('yii','Ivr');
		parent::init();
	}

	public function getAttributesModels($models, $itemsExtras = array())
	{
		$attributes = false;
		$namePk = $this->abstractModel->primaryKey();
		foreach ($models as $key => $item)
		{
		    	$attributes[$key] = $item->attributes;


		    	for ($i=0; $i <= 10; $i++) { 
		    		

		    	    	$itemOption= explode("|", $item->{'option_'.$i});


				$attributes[$key]['type_'.$i] = $itemOption[0];
				if (isset($itemOption[1]) && $itemOption[0] == 'ivr') {
					$attributes[$key]['id_ivr_'.$i] = $itemOption[1];
					if (is_numeric($itemOption[1])) {
						$sql = "SELECT name FROM pkg_ivr WHERE id = :id";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id", $itemOption[1], PDO::PARAM_STR);
						$result = $command->queryAll();

						$attributes[$key]['id_ivr_'.$i.'_name'] = isset($result[0]['name']) ? $result[0]['name'] : '';
					}else{
						$attributes[$key]['id_ivr_'.$i.'_name'] = '';
					}
				}
				else if (isset($itemOption[1]) && $itemOption[0] == 'queue') {
					$attributes[$key]['id_queue_'.$i] = $itemOption[1];
					if (is_numeric($itemOption[1])) {
						$sql = "SELECT name FROM pkg_queue WHERE id = :id";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id", $itemOption[1], PDO::PARAM_STR);
						$result = $command->queryAll();
						$attributes[$key]['id_queue_'.$i.'_name'] = isset($result[0]['name']) ? $result[0]['name'] : '';
					}else{
						$attributes[$key]['id_queue_'.$i.'_name'] = '';
					}
				}
				else if (isset($itemOption[1]) && $itemOption[0] == 'sip') {
					$attributes[$key]['id_sip_'.$i] = $itemOption[1];

					if (is_numeric($itemOption[1])) {
						$sql = "SELECT name FROM pkg_sip WHERE id = :id";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id", $itemOption[1], PDO::PARAM_STR);
						$result = $command->queryAll();
						$attributes[$key]['id_sip_'.$i.'_name'] = isset($result[0]['name']) ? $result[0]['name'] : '';
					}else{
						$attributes[$key]['id_sip_'.$i.'_name'] = '';
					}
					
				}
				else if (isset($itemOption[1]) && preg_match("/number|group|custom|hangup/", $itemOption[0])) {
					$attributes[$key]['extension_'.$i] = $itemOption[1];
				}



				$itemOption= explode("|", $item->{'option_out_'.$i});
	
				$attributes[$key]['type_out_'.$i] = $itemOption[0];
				if (isset($itemOption[1]) && $itemOption[0] == 'ivr') {
					$attributes[$key]['id_ivr_out_'.$i] = $itemOption[1];
					if (is_numeric($itemOption[1])) {
						$sql = "SELECT name FROM pkg_ivr WHERE id = :id";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id", $itemOption[1], PDO::PARAM_STR);
						$result = $command->queryAll();
						$attributes[$key]['id_ivr_out_'.$i.'_name'] = isset($result[0]['name']) ? $result[0]['name'] : '';
					}else{
						$attributes[$key]['id_ivr_out_'.$i.'_name'] = '';
					}
				}
				else if (isset($itemOption[1]) && $itemOption[0] == 'queue') {
					$attributes[$key]['id_queue_out_'.$i] = $itemOption[1];
					if (is_numeric($itemOption[1])) {
						$sql = "SELECT name FROM pkg_queue WHERE id = :id";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id", $itemOption[1], PDO::PARAM_STR);
						$result = $command->queryAll();
						$attributes[$key]['id_queue_out_'.$i.'_name'] = isset($result[0]['name']) ? $result[0]['name'] : '';
					}else{
						$attributes[$key]['id_queue_out_'.$i.'_name'] = '';
					}
				}
				else if (isset($itemOption[1]) && $itemOption[0] == 'sip') {			

					$attributes[$key]['id_sip_out_'.$i] = $itemOption[1];
					if (is_numeric($itemOption[1])) {
						$sql = "SELECT name FROM pkg_sip WHERE id = :id";
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(":id", $itemOption[1], PDO::PARAM_STR);
						$result = $command->queryAll();
						$attributes[$key]['id_sip_out_'.$i.'_name'] = isset($result[0]['name']) ? $result[0]['name'] : '';
					}else{
						$attributes[$key]['id_sip_out_'.$i.'_name'] = '';
					}
				}
				else if (isset($itemOption[1]) && preg_match("/number|group|custom|hangup/", $itemOption[0])) {
					$attributes[$key]['extension_out_'.$i] = $itemOption[1];
				}			

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
}