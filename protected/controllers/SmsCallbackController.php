<?php
/**
 * Acoes do modulo "Call".
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

class SmsCallbackController extends Controller
{

	public function actionRead()
	{
		$config = LoadConfig::getConfig();

		$destination = isset($_GET['number']) ? $_GET['number'] : '';
		$callerid = isset($_GET['callerid']) ? $_GET['callerid'] : '';
		$date = date('Y-m-d H:i:s');		

		
		$sql = "SELECT * FROM pkg_callerid WHERE cid = :callerid AND activated = 1 ";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":cid", $callerid, PDO::PARAM_STR);
		$resultCallerid = $command->queryAll();



		if(!isset($resultCallerid[0]['id']) )
		{
			$error_msg = Yii::t('yii','Error : Autentication Error!');
			echo $error_msg;
			exit;
		}

		$sql = "SELECT * FROM pkg_user WHERE id = :id";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":id", $resultCallerid[0]['id_user'], PDO::PARAM_INT);
		$resultUser = $command->queryAll();


		//CHECK ACCESS
		if(!isset($resultUser[0]['id']) )
		{
			$error_msg = Yii::t('yii','Error : Autentication Error!');
			echo $error_msg;
			exit;
		}

		/*protabilidade*/
		$callerid = Portabilidade :: getDestination($callerid, true, false,$resultUser[0]['id_plan']);
			


		$sql = "SELECT pkg_rate.id AS idRate, rateinitial, buyrate, pkg_prefix.id AS id_prefix, pkg_rate.id_trunk, 
						rt_trunk.trunkcode, rt_trunk.trunkprefix, rt_trunk.removeprefix, rt_trunk.providertech, 
						rt_trunk.inuse, rt_trunk.maxuse, rt_trunk.status, rt_trunk.failover_trunk, rt_trunk.link_sms, rt_trunk.sms_res 
						FROM pkg_rate
						LEFT JOIN pkg_plan ON pkg_rate.id_plan=pkg_plan.id
						LEFT JOIN pkg_trunk AS rt_trunk ON pkg_rate.id_trunk=rt_trunk.id
						LEFT JOIN pkg_prefix ON pkg_rate.id_prefix=pkg_prefix.id
						WHERE prefix = SUBSTRING(:callerid,1,length(prefix)) and pkg_plan.id= :id_plan 
						ORDER BY LENGTH(prefix) DESC";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":callerid", $callerid, PDO::PARAM_STR);
		$command->bindValue(":id_plan", $resultUser[0]['id_plan'], PDO::PARAM_INT);
		$callTrunk = $command->queryAll();

		if(substr("$callerid", 0, 4) == 1111)
	         	$callerid = str_replace(substr($callerid, 0, 7), "", $callerid);     


		if (count($callTrunk) == 0)
		{
			$error_msg = Yii::t('yii','Prefix not found');
			echo $error_msg;
			exit;
		}
		else
		{		


            $providertech   = $callTrunk[0]['providertech'];
            $ipaddress      = $callTrunk[0]['trunkcode'];
            $removeprefix   = $callTrunk[0]['removeprefix'];
            $prefix         = $callTrunk[0]['trunkprefix'];

            if (strncmp($callerid, $removeprefix, strlen($removeprefix)) == 0)
                 $callerid = substr($callerid, strlen($removeprefix));

             $dialstr = "$providertech/$ipaddress/$prefix$callerid";
 
             
            // gerar os arquivos .call
            $call = "Channel: " . $dialstr . "\n"; 
            $call .= "Callerid: " . $callerid . "\n";
            $call .= "Context: billing\n";
            $call .= "Extension: " . $callerid . "\n";
            $call .= "Priority: 1\n";
            $call .= "Set:CALLED=" . $callerid. "\n";
            $call .= "Set:TARRIFID=" . $callTrunk[0]['idRate']. "\n";
            $call .= "Set:SELLCOST=" . $callTrunk[0]['rateinitial']. "\n";
            $call .= "Set:BUYCOST=" . $callTrunk[0]['buyrate']. "\n";
            $call .= "Set:CIDCALLBACK=1\n";
            $call .= "Set:IDUSER=" . $resultUser[0]['id']. "\n";
            $call .= "Set:IDPREFIX=" . $callTrunk[0]['id_prefix']. "\n";
            $call .= "Set:IDTRUNK=" . $callTrunk[0]['id_trunk']. "\n";
            $call .= "Set:IDPLAN=" . $resultUser[0]['id_plan']. "\n";
            $call .= "Set:SECCALL=" . $destination. "\n";
            //echo $call;


            $aleatorio = str_replace(" ", "", microtime(true));
            $arquivo_call = "/tmp/$aleatorio.call";
            $fp = fopen("$arquivo_call", "a+");
            fwrite($fp, $call);
            fclose($fp);

            touch("$arquivo_call", mktime(date("H"), date("i"), date("s") + 1, date("m"), date("d"), date("Y")));
            chown("$arquivo_call", "asterisk");
            chgrp("$arquivo_call", "asterisk");
            chmod("$arquivo_call", 0755);        
 
            system("mv $arquivo_call /var/spool/asterisk/outgoing/$aleatorio.call");
		}
	}
}

