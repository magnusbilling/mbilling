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

class Boleto extends Model
{

	protected $_module = 'boleto';
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
		return 'pkg_boleto';
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
			array('id_user', 'required'),
            array('id_user', 'numerical', 'integerOnly'=>true),
            array('status', 'length', 'max'=>4),
            array('description, vencimento', 'length', 'max'=>200),
            array('payment', 'length', 'max' => 10)
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

	public function beforeSave()
	{
		$values = isset($_POST['rows']) ? json_decode($_POST['rows']): null;

		if(!$this->getIsNewRecord() &&  isset($values->status) && $values->status == 1)
		{
			$description = 'Boleto número'.$this->id;
			Process ::releaseUserCredit($this->id_user, $this->payment, $description, $this->id);

		}	

		return parent::beforeSave();
	}


	public function afterSave()
	{	
		if($this->getIsNewRecord()){
			//Envia boleto para o email do cliente
			$resultCard = User::model()->findByPk($this->id_user);
			if ($resultCard->email != '') {

				$sql = "SELECT * FROM pkg_smtp WHERE id_user = :id_user";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_STR);
				$smtpResult = $command->queryAll();

				
				Yii::import('application.extensions.phpmailer.JPhpMailer');
				$mail = new JPhpMailer;
				$mail->IsSMTP();
				$mail->SMTPAuth = true;
				$mail->Host 		= $smtpResult[0]['host'];				
				$mail->SMTPSecure 	= $smtpResult[0]['encryption'];
				$mail->Username 	= $smtpResult[0]['username'];
				$mail->Password 	= $smtpResult[0]['password'];
				$mail->Port 		= $smtpResult[0]['port'];
				$mail->SetFrom($smtpResult[0]['username']);
				$mail->SetLanguage('br');
				$mail->Subject = 'Boleto gerado';
				$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
				$mail->MsgHTML('<br>Ola, boleto gerado com sucesso, acesse o boleto online <br><br> http://'.$config['global']['ip_servers'].'/mbilling/index.php/boleto/secondVia/?id='.$this->id);
				$mail->AddAddress($resultCard->email);
				$mail->CharSet = 'utf-8'; 
				ob_start();
				@$mail->Send();
				
			}
		}

		return parent::afterSave();
	}

	
}