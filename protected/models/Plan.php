<?php
/**
 * Modelo para a tabela "Plan".
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
 * 24/07/2012
 */

class Plan extends Model
{
	protected $_module = 'plan';
	/**
	 * Retorna a classe estatica da model.
	 * @return SubModule classe estatica da model.
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
		return 'pkg_plan';
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
            array('name', 'required'),
            array('id_user, play_audio,techprefix, lcrtype, signup, portabilidadeMobile, portabilidadeFixed', 'numerical', 'integerOnly'=>true),
            array('name, ini_credit', 'length', 'max'=>50),
            array('techprefix', 'length', 'max'=>5)            
        );
    }

    /*
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idUser' => array(self::BELONGS_TO, 'User', 'id_user'),
		);
	}

    public function beforeSave(){

    	if(Yii::app()->session['user_type'] == 2)
		{
			$this->id_user  = Yii::app()->session['id_user'];
		}
		else
			$this->id_user = 1;
		return parent::beforeSave();
    }

	public function afterSave()
	{
		if($this->getIsNewRecord() && Yii::app()->session['isAgent'])
		{
			//Create rates of Reseller
			$sql = "SELECT id_prefix, rateinitial, initblock, billingblock FROM pkg_rate
				WHERE id_plan = :id_plan";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_plan", Yii::app()->session['id_plan'], PDO::PARAM_STR);
			$resultPrefix = $command->queryAll();


			$sqlRate = array();

			for ($i = 0; $i < count($resultPrefix); $i++)
			{
				$sqlRate[] = "('".$this->id."', '".$resultPrefix[$i]['id_prefix']."', '".$resultPrefix[$i]['rateinitial']."',
					'".$resultPrefix[$i]['initblock']."', '".$resultPrefix[$i]['billingblock']."')";
			}
			$sqlRateAgent = 'INSERT INTO pkg_rate_agent (id_plan , id_prefix,  rateinitial , initblock , billingblock)
			VALUES '.implode(',', $sqlRate).';';
			Yii::app()->db->createCommand($sqlRateAgent)->execute();
		}



		if ($this->portabilidadeMobile == 1) {
			
			$this->importPortabilidade('mobile');
		}
		if ($this->portabilidadeFixed == 1) {
			
			$this->importPortabilidade('fixed');
		}

		return parent::afterSave();
	}


	public function importPortabilidade($type)
	{
		$sqlPrefix = array();

		if ($type == 'mobile'){
			$filter_name = 'Celular';
			$sql = "SELECT id FROM pkg_rate WHERE id_plan = $this->id AND pkg_rate.status = 1 AND id_prefix IN (SELECT id FROM pkg_prefix WHERE prefix LIKE '11113%')";
		}else{
			$filter_name = 'Fixo';
			$sql = "SELECT id FROM pkg_rate WHERE id_plan = $this->id AND pkg_rate.status = 1 AND id_prefix IN (SELECT id FROM pkg_prefix WHERE prefix LIKE '11111%')";
		}
			
		$result = Yii::app()->db->createCommand($sql)->queryAll();

		if (count($result) == 0) {
			$url = "https://www.magnusbilling.com/download/cod_operadora.csv";
			if(!$file = @file_get_contents($url,false))
				return;

			$file = explode("\n", $file);
			
			foreach ($file as $key => $value) {
				$collum = explode(',', $value);
				$prefix = '1111'.substr($collum[0], 2);
				$destination = trim($collum[1]);

				if (preg_match("/$filter_name/", $destination)) {

					$sql = 'SELECT id, destination FROM pkg_prefix WHERE prefix = '.$prefix.' LIMIT 1';
					$resultPrefix = Yii::app()->db->createCommand($sql)->queryAll();

					if (count($resultPrefix) > 0) {
						if($resultPrefix[0]['destination'] != $destination){
							$sql = "UPDATE pkg_prefix SET destination = '".$destination."'  WHERE prefix = '".$prefix."'";
							Yii::app()->db->createCommand($sql)->execute();
						}
					}
					else
					{
						$sqlPrefix[] = "($prefix, '$destination')";
					}

					
				}
			}

			if ( count($sqlPrefix) > 0 ) {
				$sqlInsertPrefix = 'INSERT INTO pkg_prefix (prefix, destination) VALUES '.implode(',', $sqlPrefix).';';
				Yii::app()->db->createCommand($sqlInsertPrefix)->execute();
			}

			foreach ($file as $key => $value) {
				$collum = explode(',', $value);
				$prefix = '1111'.substr($collum[0], 2);
				$destination = trim($collum[1]);
				if (preg_match("/$filter_name/", $destination)) {
					$price =  '0.1000' ;
					$buyprice = '0.0500' ;
					$initblock = 30;
					$billingblock = 6;
					$buyrateinitblock = 30;
					$buyrateincrement = 6;
					$idPlan = $this->id;

					$sql = 'SELECT id, destination FROM pkg_prefix WHERE prefix = '.$prefix.' LIMIT 1';
					$resultPrefix = Yii::app()->db->createCommand($sql)->queryAll();
					if (count($resultPrefix) > 0) {
						$idPrefix =  $resultPrefix[0]['id'];
					}
					else
					{
						$sqlInsertPrefix = "INSERT INTO pkg_prefix (prefix, destination) VALUES ($prefix, '$destination')";
						Yii::app()->db->createCommand($sqlInsertPrefix)->execute();
						$idPrefix = Yii::app()->db->lastInsertID;
					}

					$sql = 'SELECT id FROM pkg_trunk WHERE 1 LIMIT 1';
					$resultTrunk = Yii::app()->db->createCommand($sql)->queryAll();
					$idTrunk = $resultTrunk[0]['id'];

					$sqlRate[] = "($idPrefix, $idPlan, $price, $buyprice, $idTrunk, $initblock, $billingblock, $buyrateinitblock, $buyrateincrement, 1)";
				}
			}
			if (count($sqlRate) > 0 ) {
				$sqlRate = 'INSERT INTO pkg_rate (id_prefix, id_plan, rateinitial, buyrate, id_trunk, initblock, billingblock, buyrateinitblock, buyrateincrement, status) VALUES '.implode(',', $sqlRate).';';
				Yii::app()->db->createCommand($sqlRate)->execute();
			}				
		}
	}
}