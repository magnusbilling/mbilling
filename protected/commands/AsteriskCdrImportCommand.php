<?php
/**
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
 *
 */
class AsteriskCdrImportCommand extends CConsoleCommand
{


	public function run($args)
	{
		$hostname = 'localhost';
		$dbname = 'anatel';
		$table = 'numerosOI';
		$user = 'root';
		$password = 'magnus';


		$dsn='mysql:host='.$hostname.';dbname='.$dbname;

		$con=new CDbConnection($dsn,$user,$password);
		$con->active=true;


		$sql = "SELECT `id` ,  `number`  from numerosOI WHERE ok = 0 AND numero2 != '' LIMIT 100000";
		echo $sql."\n\n";
		$result = $con->createCommand($sql)->queryAll();


		
		foreach($result as $row) {



			$number =  $row['number'];

			$number = preg_replace('/"/', '', $number);

			$number = trim($number);
			$sql = "UPDATE numerosOI SET numero2 = '$number', ok = 1 WHERE id = ". $row['id'];
			$con->createCommand($sql)->execute();
			//echo $sql."\n\n";
	    		
		}


			exit;

		$fields = "uniqueid, sessionid, id_user, starttime, sessiontime, real_sessiontime, calledstation, terminatecauseid,
		stoptime, sessionbill, id_plan, id_trunk, src, sipiax, buycost, id_offer, id_prefix, id_did";
		

		$sql = 'INSERT INTO pkg_cdr ('.$fields.') VALUES '.implode(',', $sqlCdr).';';
		Yii::app()->db->createCommand($sql)->execute();

		$sql = "UPDATE pkg_user SET credit= credit - $credit, lastuse=now() WHERE id ='" . $id_user. "'";
		Yii::app()->db->createCommand($sql)->execute();
		
    }

	function calculation_price($buyrate, $duration, $initblock, $increment)
    {

        $ratecallduration = $duration;
        $buyratecost = 0;
        if ($ratecallduration < $initblock)
            $ratecallduration = $initblock;
        if (($increment > 0) && ($ratecallduration > $initblock))
        {
            $mod_sec = $ratecallduration % $increment;
            if ($mod_sec > 0)
                $ratecallduration += ($increment - $mod_sec);
        }
        $ratecost = '';
        $ratecost -= ($ratecallduration / 60) * $buyrate;
        $ratecost = $ratecost * -1;
        return $ratecost;

    }
}