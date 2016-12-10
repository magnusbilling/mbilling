<?php
/**
 * Actions of module "Firewall".
 *
 * MagnusBilling <info@magnusbilling.com>
 * 01/02/2014
 * Defaults!/usr/bin/fail2ban-client !requiretty
 */

class FirewallController extends Controller
{

	public $attributeOrder        = 'date DESC';

	public function init()
	{
		$this->instanceModel = new Firewall;
		$this->abstractModel = Firewall::model();
		$this->titleReport   = Yii::t('yii','Firewall');
		parent::init();
	}

	public function actionRead()
	{
      	$this->readStatus();
    	return parent::actionRead();
	}

	public function readStatus()
	{
      	exec("sudo fail2ban-client status asterisk-iptables", $status);

      	$sql = 'TRUNCATE TABLE pkg_firewall';
      	Yii::app()->db->createCommand($sql)->execute() !== false;

      	foreach ($status as  $value) {

      		if (preg_match("/IP list/", $value)) {
      			$line = explode("	", $value);
      			if (isset($line[1])) {
      				
	      			$ips = explode(" ", $line[1]);
		      			foreach ($ips as $ip) {
	      				$date = $this->readLog($ip);
	      				$obs = $this->readLogAsterisk($ip);
	      				
	      				$sql = "INSERT INTO pkg_firewall (ip,action, date, description) VALUES ('$ip',0, '$date', '$obs')";
	    					Yii::app()->db->createCommand($sql)->execute() !== false;
	      			}
      			}
      		}
      	}
      	$status = array();
      	$value = '';
      	exec("sudo fail2ban-client status ip-blacklist", $status);
      	foreach ($status as  $value) {

      		if (preg_match("/IP list/", $value)) {
      			$line = explode("	", $value);
      			if (isset($line[1])) {
      				
	      			$ips = explode(" ", $line[1]);
		      			foreach ($ips as $ip) {
	      				$obs = 'Permanently IP BlackList';
	      				
	      				$sql = "INSERT INTO pkg_firewall (ip,action, date, description) VALUES ('$ip',1, NOW(), '$obs')";
	    					Yii::app()->db->createCommand($sql)->execute() !== false;
	      			}
      			}
      		}
      	}

      	$status = array();
      	$value = '';
      	exec("sudo fail2ban-client status ssh-iptables", $status);
      	foreach ($status as  $value) {

      		if (preg_match("/IP list/", $value)) {
      			$line = explode("	", $value);
      			if (isset($line[1])) {
      				
	      			$ips = explode(" ", $line[1]);
		      		foreach ($ips as $ip) {		      			
	      				$obs = 'Try connect via ssh';
	      				
	      				$sql = "INSERT INTO pkg_firewall (ip,action, date, description) VALUES ('$ip',0, NOW(), '$obs')";
	    					Yii::app()->db->createCommand($sql)->execute() !== false;
	      			}
      			}
      		}
      	}

      	$status = array();
      	$value = '';
      	exec("sudo fail2ban-client status mbilling_login", $status);
      	foreach ($status as  $value) {

      		if (preg_match("/IP list/", $value)) {
      			$line = explode("	", $value);
      			if (isset($line[1])) {
      				
	      			$ips = explode(" ", $line[1]);
		      			foreach ($ips as $ip) {
	      				$obs = $this->readLogMBilling($ip);
	      				
	      				$sql = "INSERT INTO pkg_firewall (ip,action, date, description) VALUES ('$ip',0, NOW(), '$obs')";
	    					Yii::app()->db->createCommand($sql)->execute() !== false;
	      			}
      			}
      		}
      	}
	}

	public function readLog($ip) {
		exec("tac /var/log/fail2ban.log | egrep -m 1 '$ip' ", $line);
		if (!isset($line[0])) {
			return true;
		}
		$array = explode(",", $line[0]);
		return $array[0];
	}

	public function readLogAsterisk($ip) {
		exec("tac /var/log/asterisk/messages | egrep -m 10 '$ip' ", $line);
		$log = '';
		foreach ($line as $value) {
			$logs = explode(" ", $value);
			if (isset($logs[13])) {
				$log .= 'Registration from ' .$logs[6]. ' ' . $logs[12]. ' ' . $logs[13] . "\n";
			}else{
				$log .= 'Registration from ' .$logs[6]. ' ' . $logs[11]. ' ' . $logs[12] . "\n";
			}
			
		}
		$log = preg_replace("/\"|'/", "", $log);
		return $log;
	}

	public function readLogMBilling($ip) {
		exec("tac /var/www/html/mbilling/protected/runtime/application.log | egrep -m 10 '$ip' ", $line);
		$log = '';
		foreach ($line as $value) {
			$logs = explode(" ", $value);

			if (preg_match("/Username or password is wrong/", $value)) {
				$log .= $value . "\n";
			}
			
		}
		$log = preg_replace("/\"|'/", "", $log);
		return $log;
	}



	public function actionSave()
	{
		$values = $this->getAttributesRequest();

		//if is Edit
		if (isset($values['id']) && $values['id'] > 0 ) {
			$sql = 'SELECT ip FROM pkg_firewall WHERE id = :id';
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", $values['id'], PDO::PARAM_STR);
			$result = $command->queryAll();


			$values['ip'] = $result[0]['ip'];


			if($values['action'] == 0){
				//add in asterisk-iptables
				@exec("sudo fail2ban-client set asterisk-iptables banip ".$values['ip']);

				//unbanip 
				@exec("sudo fail2ban-client set ip-blacklist unbanip ".$values['ip']);

				@exec("sudo fail2ban-client set mbilling_login unbanip ".$values['ip']);
				
			}
			elseif ($values['action'] == 1) {
				
				//unbanip asterisk-iptables
				@exec("sudo fail2ban-client set asterisk-iptables unbanip ".$values['ip']);

				//ban in ip-blacklist
				@exec("sudo fail2ban-client set ip-blacklist banip ".$values['ip']);

				//ban in ip-blacklist
				@exec("sudo fail2ban-client set mbilling_login banip ".$values['ip']);

			}

			//update status table
			$sql = 'UPDATE pkg_firewall SET action = :action WHERE id = :id';
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", $values['id'], PDO::PARAM_INT);
			$command->bindValue(":action", $values['action'], PDO::PARAM_STR);
			$command->execute();

			//generate new blacklist
			$sql = 'SELECT * FROM pkg_firewall WHERE action = 1';
			$result = Yii::app()->db->createCommand($sql)->queryAll();
			
			$file = "/var/www/html/mbilling/resources/ip.blacklist";
			$fp = fopen($file, "a+");
			$date = date("d/m/Y H:i:s");
			exec("sudo echo '' > $file");
			foreach ($result as $ip)
			{
			    fwrite($fp, $ip['ip']." [".$date."]\n");
			}
			fclose($fp);		

		}

		else{//if is save
			
			if($values['action'] == 0){
		 		@exec("sudo fail2ban-client set asterisk-iptables banip ".$values['ip']);
			}
			elseif($values['action'] == 1){

				if (isset($values['ip'])) {
					$ip = $values['ip'];
				}
				else{
					$sql = 'SELECT ip FROM pkg_firewall WHERE id = :id';
					$command = Yii::app()->db->createCommand($sql);
					$command->bindValue(":id", $values['id'], PDO::PARAM_STR);
					$result = $command->queryAll();

					$ip = $result[0]['ip'];
				}
				$date = date("Y-m-d H:i:s");
				$obs = 'Permanently IP BlackList';
				$sql = "INSERT INTO pkg_firewall (ip,action, date, description) VALUES (:ip,1, :date, :obs)";
		    		$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":ip", $ip, PDO::PARAM_STR);
				$command->bindValue(":date", $date, PDO::PARAM_STR);
				$command->bindValue(":obs", $obs, PDO::PARAM_STR);
				$command->execute();

				$sql = 'SELECT * FROM pkg_firewall WHERE action = 1';
				$result = Yii::app()->db->createCommand($sql)->queryAll();
				
				$file = "/var/www/html/mbilling/resources/ip.blacklist";
				$fp = fopen($file, "a+");
				$date = date("d/m/Y H:i:s");
				exec("sudo echo '' > $file");
				foreach ($result as $ip)
				{
				    fwrite($fp, $ip['ip']." [".$date."]\n");
				}
				fclose($fp);
			}
		}


	 	echo json_encode(array(
			$this->nameSuccess => true,
			$this->nameMsg => $this->msgSuccess,
			$this->nameRoot => array(array('id' => 0 ))
		));
	}

	public function actionDestroy()
	{
		$values = $this->getAttributesRequest();
		$namePk = 'id';
		$ids = array();

		# Se existe a chave 0, indica que existe um array interno (mais de 1 registro selecionado)
		if(array_key_exists(0, $values))
		{
			# percorre o array para excluir o(s) registro(s)
			foreach($values as $value)
			{
				array_push($ids, $value[$namePk]);
			}
		}
		else
		{
			array_push($ids, $values[$namePk]);
		}

		foreach ($ids as $value) {			

			$sql = 'SELECT * FROM pkg_firewall WHERE id = :id';
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(":id", $value, PDO::PARAM_STR);
			$result = $command->queryAll();

			if($result[0]['action'] == 0){
				@exec("sudo fail2ban-client set asterisk-iptables unbanip ".$result[0]['ip']);

				@exec("sudo fail2ban-client set mbilling_login unbanip ".$result[0]['ip']);

				@exec("sudo fail2ban-client set ssh-iptables unbanip ".$result[0]['ip']);
			}				
			elseif($result[0]['action'] == 1){
				$sql = "DELETE FROM pkg_firewall WHERE ip = :ip";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":ip", $result[0]['ip'], PDO::PARAM_STR);
				$command->execute();

				@exec("sudo fail2ban-client set ip-blacklist unbanip ".$result[0]['ip']);

				@exec("sudo fail2ban-client set ssh-iptables unbanip ".$result[0]['ip']);

				$sql = 'SELECT * FROM pkg_firewall WHERE action = 1';
				$result = Yii::app()->db->createCommand($sql)->queryAll();
				
				$file = "/var/www/html/mbilling/resources/ip.blacklist";
				$fp = fopen($file, "a+");

				foreach ($result as $ip)
				{
				    fwrite($fp, $ip['ip']." [".$ip['date']."]\n");
				}

				fclose($fp);
			}
		}

	 	echo json_encode(array(
			$this->nameSuccess => true,
			$this->nameMsg => $this->msgSuccess
		));
	}
}