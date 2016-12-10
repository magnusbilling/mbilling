<?php
/**
 * Acoes do modulo "CallOnLine".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author  Adilson Leffa Magnus.
 * @copyright   Todos os direitos reservados.
 * ###################################
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 19/09/2012
 */

class StatusSystemController extends Controller
{

    public function init()
    {
        parent::init();
        if(Yii::app()->session['user_type'] != 1){
            echo json_encode(array(
                $this->nameSuccess => false,
                $this->nameMsg => 'Operation no allow',
            ));
            exit;
        }
    }

    public function actionReload()
    {
        exec('asterisk -rx reload');
        echo json_encode(array(
            'success' => true,
            'msg' => Yii::t('yii', 'Asterisk restarted was successful.')
            ));

        exit();
    }

    public function actionstatusSystem()
    {

        //error_reporting(E_ALL);
        //ini_set("display_errors", 1);

        if ((isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) || $_SERVER['HTTP_HOST'] == 'localhost')
        {
            echo '{"rows":{"cpuMediaUso":"0.03%","cpuPercent":"0.00%","cpuCount":1,"cpuModel":" Intel(R) Xeon(R) CPU E5-2670 v2 @ 2.50GHz","ipAddr":"172.31.0.176","kernel":"2.6.32-431.20.3.el6.x86_64 (SMP)","uptime":"6 days 14:50:47","memTotal":"3.63 GB","memUsed":"1.96 GB","memFree":"1.67 GB","menPercent":"54 %","memCached":"0.47 GB","networkin":"1.13 KB\/s","networkout":"1.99 KB\/s"}}';
            exit;
        }

        $sysinfo = new sysinfo;

        $loadavg = $sysinfo->loadavg(true);
        $cpu_info = $sysinfo->cpu_info();

        foreach ($sysinfo->network() as $net_name => $net) {
            $net_name = trim($net_name);

            if (($net_name != 'eth0' && $net_name != 'venet0' && $net_name != 'eth1' && $net_name != 'eth2') || preg_match('/w.g./', $net_name))
                continue;

            $tx = new average_rate_calculator($_SESSION["netstats"][$net_name]["tx"], 10); // 30s max age
            $rx = new average_rate_calculator($_SESSION["netstats"][$net_name]["rx"], 10); // 30s max age

            $rx->add($net["rx_bytes"]);
            $tx->add($net["tx_bytes"]);
            $network =  number_format($rx->average() / 1000, 2) . " ";
            $network .= number_format($tx->average() /  1000, 2);
        }
        $memory = $sysinfo->memory();
        $network = isset($network) ? explode(' ',$network): array(0 => 0, 1=> 0);

        $status = array(
            'cpuMediaUso' => $loadavg['avg'][0] . '%',
            'cpuPercent' => number_format($loadavg['cpupercent'], 2) . '%',
            'cpuCount' => $cpu_info['cpus'],
            'cpuModel' => $cpu_info['model'],
            'ipAddr' => $sysinfo->ip_addr(),
            'kernel' => $sysinfo->kernel(),
            'uptime' => $sysinfo->formtSecundsDay($sysinfo->uptime()),
            'memTotal' => number_format($memory["ram"]["total"] / 1024000, 2) . ' GB',
            'memUsed' => number_format($memory["ram"]["t_used"] / 1024000, 2) .' GB',
            'memFree' => number_format($memory["ram"]["t_free"] / 1024000,2).' GB',
            'menPercent' => $memory["ram"]["percent"] . ' %',
            'memCached' => number_format($memory["ram"]["cached"] / 1024000,2).' GB',
            'networkin' => $network[0]. ' KB/s',
            'networkout' => $network[1]. ' KB/s',
            );


       echo json_encode(array(
            'rows' => $status,
        ));
    }

}

class average_rate_calculator
{
    var $_max_age;
    var $_values;
    var $cpu_regexp2 = "";

    /** Constructor
     * @param   array   A reference to an array to use for storage. This will be populated with key/value pairs that store the time/value, respectively.
     *          Because it is passed by reference, it can be stored externally in a session or database, allowing persistant use of this object
     *          across page loads.
     * @param  int  The maximum age of values to store, in seconds
     */
    function average_rate_calculator(&$storage_array, $max_age)
    {
        $this->_max_age = $max_age;
        if (!is_array($storage_array)) {
            $storage_array = array();
        }
        $this->_values = &$storage_array;
    }
    /** Adds a value to the array
     * @param  float    The value to add
     * @param  int  The timestamp to use for this value, defaults to now
     */
    function add($value, $timestamp = null)
    {
        if (!$timestamp)
            $timestamp = time();
        $this->_values[$timestamp] = $value;
    }
    /** Calculate the average per second value
     * @return  The average value, as a rate per second
     */
    function average()
    {
        $this->_clean();

        $avgs = array();
        $last_time = false;
        $last_val = false;
        foreach ($this->_values as $time => $val) {
            if ($last_time) {
                $avgs[] = ($val - $last_val) / ($time - $last_time);
            }
            $last_time = $time;
            $last_val = $val;
        }
        // return the average of all our averages
        if ($count = count($avgs)) {
            return array_sum($avgs) / $count;
        } else {
            return 'unknown';
        }
    }
    /** Clean old values out of the array
     */
    function _clean()
    {
        $too_old = time() - $this->_max_age;

        foreach (array_keys($this->_values) as $key) {
            if ($key < $too_old) {
                unset($this->_values[$key]);
            }
        }
    }
}

class sysinfo
{
    function rfts($strFileName, $intLines = 0, $intBytes = 4096, $booErrorRep = true)
    {
        global $error;
        $strFile = "";
        $intCurLine = 1;

        if (file_exists($strFileName)) {
            if ($fd = fopen($strFileName, 'r')) {
                while (!feof($fd)) {
                    $strFile .= fgets($fd, $intBytes);
                    if ($intLines <= $intCurLine && $intLines != 0) {
                        break;
                    } else {
                        $intCurLine++;
                    }
                }
                fclose($fd);
            } else {
                if ($booErrorRep) {
                    $addError('fopen(' . $strFileName . ')', 'file can not read by phpsysinfo',
                        __line__, __file__);
                }
                return "ERROR";
            }
        } else {

            return "ERROR";
        }
        return $strFile;
    }

    function chostname()
    {
        $result = $this->rfts('/proc/sys/kernel/hostname', 1);
        if ($result == "ERROR") {
            $result = "N.A.";
        } else {
            $result = gethostbyaddr(gethostbyname(trim($result)));
        }
        return $result;
    }

    // get the IP address of our canonical hostname
    function ip_addr()
    {
        if (!($result = getenv('SERVER_ADDR'))) 
        {
            $result = gethostbyname($this->chostname());
        }
        return $result;
    }

    function kernel()
    {
        $buf = $this->rfts('/proc/version', 1);
        if ($buf == "ERROR") 
        {
            $result = "N.A.";
        } else 
        {
            if (preg_match('/version (.*?) /', $buf, $ar_buf)) 
            {
                $result = $ar_buf[1];

                if (preg_match('/SMP/', $buf)) 
                {
                    $result .= ' (SMP)';
                }
            }
        }
        return $result;
    }

    function uptime()
    {
        $buf = $this->rfts('/proc/uptime', 1);
        $ar_buf = preg_split('/ /', $buf);
        $result = trim($ar_buf[0]);

        return $result;
    }

    function cpu_info()
    {
        $bufr = $this->rfts('/proc/cpuinfo');

        if ($bufr != "ERROR") {
            $bufe = explode("\n", $bufr);

            $results = array('cpus' => 0, 'bogomips' => 0);
            $ar_buf = array();

            foreach ($bufe as $buf) {
                if (trim($buf) != "") {
                    list($key, $value) = explode(':', trim($buf), 2);
                    // All of the tags here are highly architecture dependant.
                    // the only way I could reconstruct them for machines I don't
                    // have is to browse the kernel source.  So if your arch isn't
                    // supported, tell me you want it written in.
                    $key = trim($key);
                    switch ($key) {
                        case 'model name':
                            $results['model'] = $value;
                            break;
                        case 'cpu MHz':
                            $results['cpuspeed'] = sprintf('%.2f', $value);
                            break;
                        case 'cycle frequency [Hz]': // For Alpha arch - 2.2.x
                            $results['cpuspeed'] = sprintf('%.2f', $value / 1000000);
                            break;
                        case 'clock': // For PPC arch (damn borked POS)
                            $results['cpuspeed'] = sprintf('%.2f', $value);
                            break;
                        case 'cpu': // For PPC arch (damn borked POS)
                            $results['model'] = $value;
                            break;
                        case 'L2 cache': // More for PPC
                            $results['cache'] = $value;
                            break;
                        case 'revision': // For PPC arch (damn borked POS)
                            $results['model'] .= ' ( rev: ' . $value . ')';
                            break;
                        case 'cpu model': // For Alpha arch - 2.2.x
                            $results['model'] .= ' (' . $value . ')';
                            break;
                        case 'cache size':
                            $results['cache'] = $value;
                            break;
                        case 'bogomips':
                            $results['bogomips'] += $value;
                            break;
                        case 'BogoMIPS': // For alpha arch - 2.2.x
                            $results['bogomips'] += $value;
                            break;
                        case 'BogoMips': // For sparc arch
                            $results['bogomips'] += $value;
                            break;
                        case 'cpus detected': // For Alpha arch - 2.2.x
                            $results['cpus'] += $value;
                            break;
                        case 'system type': // Alpha arch - 2.2.x
                            $results['model'] .= ', ' . $value . ' ';
                            break;
                        case 'platform string': // Alpha arch - 2.2.x
                            $results['model'] .= ' (' . $value . ')';
                            break;
                        case 'processor':
                            $results['cpus'] += 1;
                            break;
                        case 'Cpu0ClkTck': // Linux sparc64
                            $results['cpuspeed'] = sprintf('%.2f', hexdec($value) / 1000000);
                            break;
                        case 'Cpu0Bogo': // Linux sparc64 & sparc32
                            $results['bogomips'] = $value;
                            break;
                        case 'ncpus probed': // Linux sparc64 & sparc32
                            $results['cpus'] = $value;
                            break;
                    }
                }
            }
        }
        //print_r($results);

        return $results;
    }

    function network()
    {
        //$rx recebe
        //$tx envia

        $results = array();

        $bufr = $this->rfts('/proc/net/dev');
        if ($bufr != "ERROR") {
            $bufe = explode("\n", $bufr);
            foreach ($bufe as $buf) {
                if (preg_match('/:/', $buf)) {
                    list($dev_name, $stats_list) = preg_split('/:/', $buf, 2);
                    $stats = preg_split('/\s+/', trim($stats_list));
                    $results[$dev_name] = array();

                    $results[$dev_name]['rx_bytes'] = $stats[0];
                    $results[$dev_name]['rx_packets'] = $stats[1];
                    $results[$dev_name]['rx_errs'] = $stats[2];
                    $results[$dev_name]['rx_drop'] = $stats[3];

                    $results[$dev_name]['tx_bytes'] = $stats[8];
                    $results[$dev_name]['tx_packets'] = $stats[9];
                    $results[$dev_name]['tx_errs'] = $stats[10];
                    $results[$dev_name]['tx_drop'] = $stats[11];

                    $results[$dev_name]['errs'] = $stats[2] + $stats[10];
                    $results[$dev_name]['drop'] = $stats[3] + $stats[11];
                }
            }
        }
        return $results;
    }

    function memory()
    {
        $results['ram'] = array();
        $results['swap'] = array();
        $results['devswap'] = array();

        $bufr = $this->rfts('/proc/meminfo');
        if ($bufr != "ERROR") {
            $bufe = explode("\n", $bufr);
            foreach ($bufe as $buf) {
                if (preg_match('/^MemTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $results['ram']['total'] = $ar_buf[1];
                } else
                    if (preg_match('/^MemFree:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                        $results['ram']['t_free'] = $ar_buf[1];
                    } else
                        if (preg_match('/^Cached:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                            $results['ram']['cached'] = $ar_buf[1];
                        } else
                            if (preg_match('/^Buffers:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                                $results['ram']['buffers'] = $ar_buf[1];
                            } else
                                if (preg_match('/^SwapTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                                    $results['swap']['total'] = $ar_buf[1];
                                } else
                                    if (preg_match('/^SwapFree:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                                        $results['swap']['free'] = $ar_buf[1];
                                    }
            }

            $results['ram']['t_used'] = $results['ram']['total'] - $results['ram']['t_free'];
            $results['ram']['percent'] = round(($results['ram']['t_used'] * 100) / $results['ram']['total']);
            $results['swap']['used'] = $results['swap']['total'] - $results['swap']['free'];

            // If no swap, avoid divide by 0
            //
            if (trim($results['swap']['total'])) {
                $results['swap']['percent'] = round(($results['swap']['used'] * 100) / $results['swap']['total']);
            } else {
                $results['swap']['percent'] = 0;
            }

            // values for splitting memory usage
            if (isset($results['ram']['cached']) && isset($results['ram']['buffers'])) {
                $results['ram']['app'] = $results['ram']['t_used'] - $results['ram']['cached'] -
                    $results['ram']['buffers'];
                $results['ram']['app_percent'] = round(($results['ram']['app'] * 100) / $results['ram']['total']);
                $results['ram']['buffers_percent'] = round(($results['ram']['buffers'] * 100) /
                    $results['ram']['total']);
                $results['ram']['cached_percent'] = round(($results['ram']['cached'] * 100) / $results['ram']['total']);
            }

            $bufr = $this->rfts('/proc/swaps');
            if ($bufr != "ERROR") {
                $swaps = explode("\n", $bufr);
                for ($i = 1; $i < (sizeof($swaps)); $i++) {
                    if (trim($swaps[$i]) != "") {
                        $ar_buf = preg_split('/\s+/', $swaps[$i], 6);
                        $results['devswap'][$i - 1] = array();
                        $results['devswap'][$i - 1]['dev'] = $ar_buf[0];
                        $results['devswap'][$i - 1]['total'] = $ar_buf[2];
                        $results['devswap'][$i - 1]['used'] = $ar_buf[3];
                        $results['devswap'][$i - 1]['free'] = ($results['devswap'][$i - 1]['total'] - $results['devswap'][$i -
                            1]['used']);
                        $results['devswap'][$i - 1]['percent'] = round(($ar_buf[3] * 100) / $ar_buf[2]);
                    }
                }
            }
        }
        return $results;
    }

    // grabs a key from sysctl(8)
    function grab_key2($key)
    {
        return $this->execute_program('sysctl', "-n $key");
    }

    function grab_key($key)
    {
        $s = $this->execute_program('sysctl', $key);
        $s = ereg_replace($key . ': ', '', $s);
        $s = ereg_replace($key . ' = ', '', $s); // fix Apple set keys

        return $s;
    }


    function execute_program($programname, $args = '', $booErrorRep = true)
    {

        global $error;
        $buffer = '';
        $program = $this->find_program($programname);


        // see if we've gotten a |, if we have we need to do patch checking on the cmd
        if ($args) {
            $args_list = preg_split('/ /', $args);
            for ($i = 0; $i < count($args_list); $i++) {
                if ($args_list[$i] == '|') {
                    $cmd = $args_list[$i + 1];
                    $new_cmd = find_program($cmd);
                    $args = ereg_replace("\| $cmd", "| $new_cmd", $args);
                }
            }
        }
        // we've finally got a good cmd line.. execute it
        echo $program . $args;
        if ($fp = popen("($program $args > /dev/null) 3>&1 1>&2 2>&3", 'r')) {
            print_r($fp);
            while (!feof($fp)) {
                $buffer .= fgets($fp, 4096);
            }
            pclose($fp);
            $buffer = trim($buffer);
        }
        if ($fp = popen("$program $args", 'r')) {
            while (!feof($fp)) {
                $buffer .= fgets($fp, 4096);
            }
            pclose($fp);
        }
        $buffer = trim($buffer);


        return $buffer;
    }

    // Find a system program.  Do path checking
    function find_program($program)
    {
        global $addpaths;

        $path = array('/bin', '/sbin', '/usr/bin', '/usr/sbin', '/usr/local/bin',
            '/usr/local/sbin');

        if (isset($addpaths) && is_array($addpaths)) {
            $path = array_merge($path, $addpaths);
        }

        if (function_exists("is_executable")) {
            while ($this_path = current($path)) {
                if (is_executable("$this_path/$program")) {
                    return "$this_path/$program";
                }
                next($path);
            }
        } else {
            return strpos($program, '.exe');
        }
        ;

        return;
    }


    function loadavg($bar = false)
    {
        $buf = $this->rfts('/proc/loadavg');
        if ($buf == "ERROR") {
            $results['avg'] = array('N.A.', 'N.A.', 'N.A.');
        } else {
            $results['avg'] = preg_split("/\s/", $buf, 4);
            unset($results['avg'][3]); // don't need the extra values, only first three
        }
        if ($bar) {
            $buf = $this->rfts('/proc/stat', 1);
            if ($buf != "ERROR") {
                sscanf($buf, "%*s %f %f %f %f", $ab, $ac, $ad, $ae);
                // Find out the CPU load
                // user + sys = load
                // total = total
                $load = $ab + $ac + $ad; // cpu.user + cpu.sys
                $total = $ab + $ac + $ad + $ae; // cpu.total

                // we need a second value, wait 1 second befor getting (< 1 second no good value will occour)
                sleep(1);
                $buf = $this->rfts('/proc/stat', 1);
                sscanf($buf, "%*s %f %f %f %f", $ab, $ac, $ad, $ae);
                $load2 = $ab + $ac + $ad;
                $total2 = $ab + $ac + $ad + $ae;
                $results['cpupercent'] = ($total2 != $total) ? ((100 * ($load2 - $load)) / ($total2 -
                    $total)) : 0;
            }
        }
        return $results;
    }

    function formtSecundsDay($seg)
    {
        //qtos dias
        //qtos dias
        $dia = 0;
        //inicia-se o resto da divisao
        $resto = $seg;
        //verifica se passou  de 1 dia a viagem
        if ($seg > 86400) {
            //busca a quantidade de dias
            $dia = (int)$seg / 86400;
            //busca o restante dos segundos após ter ultrapassado X dias
            $resto = 86400 / $seg;
        }
        $total = date('H:i:s', mktime(null, null, $seg));
        return (int)$dia . ' days ' . $total;
    }


    function draw_box($text, $value, $total_width = 200)
    {
        $tooltip = $text . ": " . $value;

        $out = "<div class=\"databox\" style=\"width:" . $total_width . "px;\">\n";
        $out .= " <div class=\"dataname\">" . $text . "</div>\n";
        $out .= " <div class=\"datavalue\"><a href=\"#\" title=\"" . $tooltip . "\">" .
            $value . "</a></div>\n";
        $out .= "</div>\n";

        return $out;
    }
}

?>