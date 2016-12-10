<?php
/**
 * Modelo para a tabela "Trunk".
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
 * 25/06/2012
 */
class Trunk extends Model
{
	protected $_module = 'trunk';

		/**
	 * Retorna a classe estatica da model.
	 * @return Trunk classe estatica da model.
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
		return 'pkg_trunk';
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
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('trunkcode, id_provider, allow, providertech, host', 'required'),
			array('allow_error, id_provider, failover_trunk, secondusedreal, register, call_answered, call_total, inuse, maxuse, status, if_max_use', 'numerical', 'integerOnly'=>true),
			array('nat, trunkcode, sms_res', 'length', 'max'=>50),
			array('trunkprefix, providertech, removeprefix, secret, context, insecure, disallow', 'length', 'max'=>20),
			array('providerip, user,fromuser, allow, host, fromdomain', 'length', 'max'=>80),
			array('addparameter', 'length', 'max'=>120),
			array('link_sms', 'length', 'max'=>250),
			array('directmedia, dtmfmode, qualify', 'length', 'max'=>7),
			array('type, language', 'length', 'max'=>6),
		);
	}
    /**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idProvider' => array(self::BELONGS_TO, 'Provider', 'id_provider'),
			'failoverTrunk' => array(self::BELONGS_TO, 'trunk', 'failover_trunk'),
			'trunks' => array(self::HAS_MANY, 'trunk', 'failover_trunk'),
		);
	}

	public function beforeSave(){

		if($this->getIsNewRecord() && strlen($this->fromuser) == 0)
		{
			$this->fromuser = $this->user;
		}
		
		$this->short_time_call = $this->status == 1 ? 0 : $this->short_time_call;
		$this->trunkcode = preg_replace("/ /", "-", $this->trunkcode);
		$this->allow = preg_replace("/,0/", "", $this->allow);
		$this->allow = preg_replace("/0,/", "", $this->allow);
		$this->providerip = $this->providertech != 'sip' &&  $this->providertech != 'iax2' ? $this->host : $this->trunkcode;

		$this->failover_trunk = $this->failover_trunk === 0 ? NULL : $this->failover_trunk;
		return parent::beforeSave();
	}

	public function afterSave()
	{	
		
		$this->generateFileText();
		
		return parent::afterSave();
	}

	public function afterDelete()
	{
		$this->generateFileText();
	}


	public function generateFileText()
	{

		if ($_SERVER['HTTP_HOST'] == 'localhost' || preg_match("/magnusbilling.com/", $_SERVER['HTTP_HOST']))
        	{
        		return;
        	}

		$fg_query_adition_sip = 'id, trunkcode, fromdomain, providerip, user, secret, disallow, allow, directmedia, context, maxuse, dtmfmode, insecure, nat, qualify, type, host, providertech, register, language, fromuser'; // add
		$list_friend = Trunk::model()->findAll(array('select'=>$fg_query_adition_sip));
		$buddyfile = '/etc/asterisk/sip_magnus.conf';
		$buddyfileiax = '/etc/asterisk/iax_magnus.conf';
		$registerFile = '/etc/asterisk/sip_magnus_register.conf';
		$registerFileIax = '/etc/asterisk/iax_magnus_register.conf';

		if (is_array($list_friend))
		{
			

			$fr = fopen($registerFile, "w");
			$fri = fopen($registerFileIax, "w");

			$fd = fopen($buddyfile, "w");

			$fi = fopen($buddyfileiax, "w");

			if (!$fd)
			{
				echo "</br><center><b><font color=red>".gettext("Could not open buddy file").$buddyfile."</font></b></center>";
			}

			else
			{
				foreach($list_friend as $key=>$data)
				{
					if ($data['providertech'] == 'SIP' || $data['providertech'] == 'sip' )
					{
						$line = "\n\n[".$data['trunkcode']."]\n";
						if (fwrite($fd, $line) === FALSE)
						{
							echo "Impossible to write to the file ($buddyfile)";
							break;
						}
						else
						{
							$line = '';

							$host = explode(':', $data['host']);
							$line.= 'host='.$host[0]."\n";

							if (isset($host[1]))
								$line.= 'port='.$host[1]."\n";

							if (strlen($data['fromdomain']) > 0)
								$line.= 'fromdomain='.$data['fromdomain']."\n";

							if (strlen($data['user']) > 0){
								$line.= 'username='.$data['user']."\n";
								$line.= 'user='.$data['user']."\n";
							}
							if (strlen($data['fromuser']) > 0){
								$line.= 'fromuser='.$data['fromuser']."\n";
							}
							if (strlen($data['secret']) > 0)
								$line.= 'secret='.$data['secret']."\n";
							$line.= 'disallow='.$data['disallow']."\n";

							$codecs = explode(",", $data['allow']);
							foreach ($codecs as $codec)
								$line .= 'allow=' . $codec . "\n";

							$line.= 'directmedia='.$data['directmedia']."\n";
							$line.= 'context='.$data['context']."\n";
							$line.= 'dtmfmode='.$data['dtmfmode']."\n";
							$line.= 'insecure='.$data['insecure']."\n";
							$line.= 'nat='.$data['nat']."\n";
							$line.= 'qualify='.$data['qualify']."\n";
							$line.= 'type='.$data['type']."\n";

							if (strlen($data['language']) > 0)
								$line.= 'language='.$data['language']."\n";


							if ($data['maxuse'] > 0) 
								$line.= 'call-limit='.$data['maxuse']."\n";
							

							//registrar tronco
							if ($data['register'])
							{
								//se tiver porta adicionar
								if (isset($host[1]))
									$host[0] = $host[0] .':'.$host[1];

								$registerLine = '';
								$registerLine .= 'register=>'.$data['user'].':'.$data['secret'].'@'.$host[0]."/".$data['user']."\n";
								
								if (fwrite($fr, $registerLine) === FALSE) {
									echo gettext("Impossible to write to the file")." ($registerLine)";
									break;
								}
							}


							if (fwrite($fd, $line) === FALSE) {
								echo gettext("Impossible to write to the file")." ($buddyfile)";
								break;
							}
						}
					}

					if ($data['providertech'] == 'IAX2' || $data['providertech'] == 'iax2') 
					{
						$line = "\n\n[".$data['trunkcode']."]\n";
						if (fwrite($fi, $line) === FALSE) 
						{
							echo "Impossible to write to the file ($buddyfileiax)";
							break;
						} 
						else 
						{
							$line = '';

							$host = explode(':', $data['host']);
							$line.= 'host='.$host[0]."\n";

							if (isset($host[1]))
								$line.= 'port='.$host[1]."\n";

							$line.= 'fromdomain='.$host[0]."\n";

							if (strlen($data['user']) > 0){
								$line.= 'username='.$data['user']."\n";
								$line.= 'user='.$data['user']."\n";
								$line.= 'fromuser='.$data['user']."\n";
							}
							if (strlen($data['secret']) > 0) 
								$line.= 'secret='.$data['secret']."\n";
							$line.= 'disallow='.$data['disallow']."\n";

							$codecs = explode(",", $data['allow']);
							foreach ($codecs as $codec)
								$line .= 'allow=' . $codec . "\n";

							$line.= 'directmedia='.$data['directmedia']."\n";
							$line.= 'context='.$data['context']."\n";
							$line.= 'dtmfmode='.$data['dtmfmode']."\n";
							$line.= 'insecure='.$data['insecure']."\n";
							$line.= 'nat='.$data['nat']."\n";
							$line.= 'qualify='.$data['qualify']."\n";
							$line.= 'type='.$data['type']."\n";
							
							if (strlen($data['language']) > 0)
								$line.= 'language='.$data['language']."\n";

							
							if ($data['maxuse'] > 0) 
								$line.= 'calllimit='.$data['maxuse']."\n";

							//registrar tronco
							if ($data['register'])
							{
								//se tiver porta adicionar
								if (isset($host[1]))
									$host[0] = $host[0] .':'.$host[1];


								$registerLine = '';
								$registerLine .= 'register=>'.$data['user'].':'.$data['secret'].'@'.$host[0]."/".$data['user']."\n";
								
								if (fwrite($fri, $registerLine) === FALSE) {
									echo gettext("Impossible to write to the file")." ($registerLine)";
									break;
								}
							}

							if (fwrite($fi, $line) === FALSE) {
								echo gettext("Impossible to write to the file")." ($buddyfileiax)";
								break;
							}
						}
					}

				}
				fclose($fd);
				fclose($fi);
				fclose($fr);
				fclose($fri);
			}
			$asmanager = new AGI_AsteriskManager;
			$conectaServidor = $conectaServidor = $asmanager->connect('localhost', 'magnus', 'magnussolution');
			$server = $asmanager->Command("sip reload");
			$server = $asmanager->Command("iax2 reload");
			$asmanager->disconnect();
		}
	}
}