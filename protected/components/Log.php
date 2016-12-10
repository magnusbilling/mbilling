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
class Log
{
    public function writeLog($fileLog, $log)
    {
        $string_log = "[" . date("d/m/Y H:i:s") . "]:[$log]\n";
        error_log($string_log, 3, '/var/www/html/mbilling/'.$fileLog);
        unset($string_log);  
    }
}
?>