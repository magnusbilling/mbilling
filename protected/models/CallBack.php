<?php
/**
 * Modelo para a tabela "CallBack".
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

class CallBack extends Model
{
	protected $_module = 'callback';
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
		return 'pkg_callback';
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
			array('num_attempt, id_server_group', 'numerical', 'integerOnly'=>true),
			array('uniqueid, server_ip', 'length', 'max'=>40),
            array('status, callerid', 'length', 'max'=>10),
            array('channel, exten, account, context, timeout, priority', 'length', 'max'=>60),
            array('variable', 'length', 'max'=>300)            
		);
	}


	public function beforeSave()
	{
		$this->account = $this->getIsNewRecord() && Yii::app()->getSession()->get('isClient') ? Yii::app()->getSession()->get('login') : $this->account;

		if ($this->getIsNewRecord() || (isset($_POST['id']) && isset($_POST['ddi']) && isset($_POST['ddd']) && isset($_POST['number']) )) 
		{
			$user = Card::model()->findAllByAttributes(array('username' => $this->account));
			
			if(count($user) == 0)
			{
				echo json_encode(array(
					'success' => false,
					'msg' => Yii::t('yii','User not found')
				));
				exit;
			}

			$_SESSION["tariff"] = $user[0]['id_tariffgroup'];

			if($user[0]['credit'] < 1)
			{
				echo json_encode(array(
					'success' => false,
					'msg' => Yii::t('yii','customer not have credit')
				));
				exit;
			}

			$this->uniqueid    =  date('YmD').'-'. date('his');
			$this->status      = 'PENDING';
			$this->server_ip   = 'localhost';
			$this->num_attempt = 0;
			$this->context = 'billing';
			$this->id_server_group = 1;
			$this->timeout = 30000;
			$this->priority=1;
			$this->callerid = $this->account;
			$this->variable = "CALLED=$this->channel,CALLING=$this->exten,CBID=$this->uniqueid,LEG=".$this->account;


			$this->channel = strlen($this->channel) == 5 ? 'SIP/'.$this->channel : $this->channel;


			if (!preg_match('/[A-Z,a-z]/', $this->channel))
			{
				$destination = $this->channel;

				$destination = Portabilidade :: getDestination($destination, true, false,$_SESSION["id_plan"]);
				

				$sql = "SELECT rt_trunk.trunkcode, rt_trunk.trunkprefix, rt_trunk.removeprefix, rt_trunk.providertech
						FROM pkg_plan
						LEFT JOIN pkg_rate ON pkg_rate.id_tariffplan=pkg_plan.id
						LEFT JOIN pkg_trunk AS rt_trunk ON pkg_rate.id_trunk=rt_trunk.id_trunk
						WHERE pkg_rate.dialprefix = SUBSTRING(:destination,1,length(pkg_rate.dialprefix)) AND pkg_rate.status = 1 
						AND pkg_plan.id= :id_plan ORDER BY LENGTH(pkg_rate.dialprefix) DESC";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":destination", $destination, PDO::PARAM_STR);
				$command->bindValue(":id_plan", $_SESSION["tariff"], PDO::PARAM_INT);
				$callTrunk = $command->queryAll();

				if (count($callTrunk) > 0) 
				{
					$trunkcode = $callTrunk[0]['trunkcode'];
					$trunkprefix = $callTrunk[0]['trunkprefix'];
					$removeprefix = $callTrunk[0]['removeprefix'];
					$providertech = $callTrunk[0]['providertech'];
				
					//retiro e adiciono os prefixos do tronco
					if(strncmp($destination, $removeprefix, strlen($removeprefix)) == 0)
						$destination = substr($destination, strlen($removeprefix));
					
					$destination = $trunkprefix.$destination;
					
					$this->channel = "$providertech/$trunkcode/$destination";					
				}
			}
		}	

		return parent::beforeSave();
	}

	public function afterSave()
	{
		return parent::afterSave();
	}
}