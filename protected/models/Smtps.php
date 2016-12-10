<?php
/**
 * Modelo para a tabela "Smtps".
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
 * 24/09/2012
 */

class Smtps extends Model
{
	protected $_module = 'smtps';
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
		return 'pkg_smtp';
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
			array('id_user', 'numerical', 'integerOnly'=>true),
            	array('host, username, ', 'length', 'max'=>100),
            	array('password', 'length', 'max'=>30),
            	array('encryption, port', 'length', 'max'=>10),
		);
	}

	/**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idUser' => array(self::BELONGS_TO, 'User', 'id_user')
		);
	}

	public function afterSave()
	{		

		return parent::afterSave();
	}

	public function beforeSave()
	{
		if($this->getIsNewRecord()){


			$sql = "SELECT id_group FROM pkg_user WHERE id = :id_user";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", $this->id_user, PDO::PARAM_STR);
			$records = $command->queryAll();


			$sql = "SELECT id FROM pkg_smtp WHERE id_user = :id_user";
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_INT);
			$records = $command->queryAll();
			if (count($records) > 0) {
				echo json_encode(array(
					'success' => false,
					'rows' => array(),
					'errors' => Yii::t('yii','Do you already have a SMTP')
				));
				exit;
			}

			if (Yii::app()->session['isAgent'] == 1) {
				$sql =" INSERT INTO pkg_templatemail (`id_user`, `mailtype`, `fromemail`, `fromname`, `subject`, `messagehtml`, `language`) 
								SELECT :id_user, `mailtype`, :username, `fromname`, `subject`, `messagehtml`, `language` FROM 
								pkg_templatemail WHERE id_user = 1 AND 
								( mailtype = 'signup'  OR mailtype = 'signupconfirmed' OR mailtype = 'reminder' OR mailtype = 'refill') ;";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_INT);
				$command->bindValue(":username", $this->username, PDO::PARAM_STR);
				$command->execute();
			}
		}
		if (Yii::app()->session['isAgent'] == 1) {
			$this->id_user = Yii::app()->session['id_user'];
		}else{
			$this->id_user = 1;
		}
		return parent::beforeSave();
	}
}