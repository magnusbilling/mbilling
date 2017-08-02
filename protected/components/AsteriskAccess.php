<?php
/**
 * Classe de com funcionalidades globais
 *
 * MagnusBilling <info@magnusbilling.com>
 * 08/06/2013
 */

class AsteriskAccess {

	private $asmanager;
	private static $instance;

	public static function instance(){
		if(is_null(self::$instance)){
			self::$instance = new AsteriskAccess();
		}
		self::$instance->connectAsterisk();
		return self::$instance;
	} 

	private function AsteriskAccess()
	{
		$this->asmanager = new AGI_AsteriskManager;
	}

	private function connectAsterisk(){
		$this->asmanager->connect('localhost', 'magnus', 'magnussolution');

	}

	public function queueAddMember($member,$queue)
	{
		$this->asmanager->Command("queue add member SIP/".$member ." to ".preg_replace("/ /", "\ ", $queue));
	}
	
	public function queueRemoveMember($member,$queue)
	{
		$this->asmanager->Command("queue remove member SIP/".$member ." from ".preg_replace("/ /", "\ ", $queue));
	}

	public function queuePauseMember($member,$queue,$reason = 'normal')
	{
		$this->asmanager->Command("queue pause member SIP/".$member ." queue ".preg_replace("/ /", "\ ", $queue) . " reason ".$reason);
	}

	public function queueUnPauseMember($member,$queue,$reason = 'normal')
	{
		$this->asmanager->Command("queue unpause member SIP/".$member ." queue ".preg_replace("/ /", "\ ", $queue) . " reason ".$reason);
	}

	public function queueShow($queue)
	{
		return $this->asmanager->Command("queue show " . $queue);	
	}

	public function queueReload()
	{
		return $this->asmanager->Command("queue reload all");
	}

	public function queueReseteStats($queue)
	{
		return $this->asmanager->Command("queue reset stats ".$queue);
	}

	public function sipReload()
	{
		return $this->asmanager->Command("sip reload");
	}

	public function iaxReload()
	{
		return @$this->asmanager->Command("iax reload");
	}

	public function queueGetMemberStatus($member,$campaign_name)
	{
		$queueData = AsteriskAccess::instance()->queueShow($campaign_name);
  		$queueData = explode("\n", $queueData["data"]);
        	$status = "error";
        	foreach ($queueData as $key => $data) {
        		
        		$data = trim($data);

        		if (preg_match("/SIP\/".Yii::app()->session['username']."/", $data)) {
        			$line = explode('(', $data);
        			$status = trim($line[3]);
                   	$status = explode(")", $status);

                   	$status = $status[0];
                   	break;
        		}	               
          }
          return $status;
	}
	//model , file, e o nome para o contexto
	public function writeAsteriskFile($model,$file,$head_field='name')
	{	   	
	   	$rows = Util::getColumnsFromModel($model);

		$fd = fopen($file, "w");

		if ($head_field == 'trunkcode') {
			$registerFile = '/etc/asterisk/sip_magnus_register.conf';
			$fr = fopen($registerFile, "w");
		}
		if (!$fd) 
		{
			echo "</br><center><b><font color=red>".gettext("Could not open buddy file").$file."</font></b></center>";
		} 
		else 
		{
			foreach($rows as $key=>$data) 
			{


				$line = "\n\n[".$data[$head_field]."]\n";

				foreach ($data as $key => $option) {
					if ($key == $head_field)
						continue;
					

					//registrar tronco
					if ($key == 'register_string')
					{
					
						$registerLine = 'register=>'.$data['register_string']."\n";
						
						if (fwrite($fr, $registerLine) === FALSE) {
							echo gettext("Impossible to write to the file")." ($registerLine)";
							break;
						}
						continue;
					}

					$line   .= $key.'='.$option."\n";

				}

				if (fwrite($fd, $line) === FALSE) 
				{
					echo "Impossible to write to the file ($buddyfile)";
					break;
				}
			}
			
			fclose($fd);

			if (preg_match("/sip/", $file))
				AsteriskAccess::instance()->sipReload();
			elseif (preg_match("/iax/", $file))
				AsteriskAccess::instance()->iaxReload();
			else
				AsteriskAccess::instance()->queueReload();
		}		
		
	}
	//call file , time in seconds to create the file
	public static function generateCallFile($callFile,$time=0)
	{
		$aleatorio = str_replace(" ", "", microtime(true));
		$arquivo_call = "/tmp/$aleatorio.call";
		$fp = fopen("$arquivo_call", "a+");
		fwrite($fp, $callFile);
		fclose($fp);
		
		$time += time();		          
               
		touch("$arquivo_call", $time);
		chown("$arquivo_call", "asterisk");
		chgrp("$arquivo_call", "asterisk");
		chmod("$arquivo_call", 0755);             

		system("mv $arquivo_call /var/spool/asterisk/outgoing/$aleatorio.call");
	}

}