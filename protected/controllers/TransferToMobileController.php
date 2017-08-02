<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * Url for customer register http://ip/billing/index.php/user/add .
 */
class TransferToMobileController extends BaseController
{
    private $url = "https://fm.transfer-to.com/cgi-bin/shop/topup?";

    public function init()
    {

        $config = LoadConfig::getConfig();

        $startSession = strlen(session_id()) < 1 ? session_start() : null;

        if (!Yii::app()->session['id_user']) {
            $user     = $_POST['user'];
            $password = $_POST['pass'];

            $condition = "(username COLLATE utf8_bin LIKE :user OR username LIKE :user
				OR email COLLATE utf8_bin LIKE :user)";

            $sql = "SELECT pkg_user.id, username, id_group, id_plan, pkg_user.firstname,
							pkg_user.lastname , id_user_type, id_user, loginkey, active, password
							FROM pkg_user JOIN pkg_group_user ON id_group = pkg_group_user.id
							WHERE $condition";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(":user", $user, PDO::PARAM_STR);
            $result = $command->queryAll();

            if (!isset($result[0]['username']) || sha1($result[0]['password']) != $password) {
                Yii::app()->session['logged'] = false;
                echo json_encode(array(
                    'success' => false,
                    'msg'     => 'Usuário e/ou login incorretos',
                ));
                exit;
            }

            if (!$result) {
                Yii::app()->session['logged'] = false;
                echo json_encode(array(
                    'success' => false,
                    'msg'     => 'Usuário e/ou login incorretos',
                ));
                exit;
            }

            if ($result[0]['active'] == 0) {
                Yii::app()->session['logged'] = false;
                echo json_encode(array(
                    'success' => false,
                    'msg'     => 'Username is disabled',
                ));
                exit;
            }
            $user = $result[0];

            Yii::app()->session['isAdmin']       = $user['id_user_type'] == 1 ? true : false;
            Yii::app()->session['isAgent']       = $user['id_user_type'] == 2 ? true : false;
            Yii::app()->session['isClient']      = $user['id_user_type'] == 3 ? true : false;
            Yii::app()->session['isClientAgent'] = false;
            Yii::app()->session['id_plan']       = $user['id_plan'];
            Yii::app()->session['credit']        = isset($user['credit']) ? $user['credit'] : 0;
            Yii::app()->session['username']      = $user['username'];
            Yii::app()->session['logged']        = true;
            Yii::app()->session['id_user']       = $user['id'];
            Yii::app()->session['id_agent']      = is_null($user['id_user']) ? 1 : $user['id_user'];
            Yii::app()->session['name_user']     = $user['firstname'] . ' ' . $user['lastname'];
            Yii::app()->session['id_group']      = $user['id_group'];
            Yii::app()->session['user_type']     = $user['id_user_type'];

            $sql = "SELECT m.id, action, show_menu, text, module, icon_cls, m.id_module
							FROM pkg_group_module gm INNER JOIN pkg_module m ON gm.id_module = m.id
							WHERE id_group = :id_group";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(":id_group", $user['id_group'], PDO::PARAM_STR);
            $result                         = $command->queryAll();
            Yii::app()->session['currency'] = $config['global']['base_currency'];

        }

        parent::init();
    }

    public function actionRead($asJson = true)
    {
        if (isset($_POST['amount'])) {
            $values = $this->actionMsisdn_info();
        } elseif (isset($_POST['number'])) {
            $values = $this->actionMsisdn_info();
            $this->secondForm($values);
        } else {

            $this->fistForm();
        }

    }

    public function fistForm()
    {
        echo '<div id="container"><div id="form"> <div id="box-4">';
        echo '<form action="" id="form1" method="POST">
			<fieldset class="well">
				<div class="control-group">

					<div class="control-label">
						<label >Number<span class="star">&nbsp;*</span></label>
					</div>
					<div class="controls">
						<input type="text" name="number" size="25" required="" aria-required="true" value = "">
					</div>

					<br>
					<div class="controls" id="fistButtondiv">
						<button id="fistButton" type="submit" onclick="button1();" class="btn btn-primary">Next</button>
					</div>
					<div class="controls" id="fistButtondivWait"></div>

			</fieldset>
		</form>';
        echo '</div></div></div>';

    }

    public function secondForm($values)
    {
        $config = LoadConfig::getConfig();
        $number = $_POST['number'];
        //$_POST['amount'] = isset($_POST['amount']) ? $_POST['amount'] : '';

        $country  = $values['country'];
        $operator = $values['operator'];
        $country  = $values['country'];
        $amount   = '';
        foreach ($values['rows'] as $key => $value) {
            $amount .= "<option value='$value[0]'>$value[1]</option>";
        }

        if ($config['global']['fm_transfer_show_selling_price'] > 0) {
            $valor      = preg_replace("/\%/", '', $config['global']['fm_transfer_show_selling_price']);
            $show_price = 'onchange="showPrice(\'' . $valor . '\')"';
        } else {
            $show_price = '';
        }

        echo '<div id="container"><div id="form"> <div id="box-4">';
        echo '<form action="" id="form2" method="POST">
			<fieldset class="well">
				<div class="control-group">

					<br>
					<div class="control-label">
						<label >Number<span class="star">&nbsp;*</span> : ' . $number . '</label>
					</div>
					<div class="control-label">
						<label >Operator<span class="star">&nbsp;*</span> : ' . $operator . '</label>
					</div>
					<div class="control-label">
						<label >Country<span class="star">&nbsp;*</span> : ' . $country . '</label>
					</div>


					<div class="controls">
					<label >Amount<span class="star">&nbsp;*</span> :</label>
						<select id=amountfiel name="amount" ' . $show_price . '>
						<option value=""></option>
							' . $amount . '
						</select>
					</div>


					<div id="rsp_age" style="font-size: 11px; color: red;"></div>
					<input type="hidden" name="number" value="' . $number . '">
					<input type="hidden" name="rows" value="' . urlencode(json_encode($values['rows'])) . '">
					<br>
					<div class="controls" id="secondButtondiv">
						<button type="submit" id="secondButton" onclick="button2();" class="btn btn-primary">Next</button>
					</div>
					<div class="controls" id="secondButtondivWait"></div>
					<div id="sellingPrice"></div>

			</fieldset>
		</form>';
        echo '</div></div></div>';
    }

    public function actionMsisdn_info()
    {

        $config = LoadConfig::getConfig();

        if (isset($_POST['number'])) {
            $number = preg_match("/\+/", $_POST['number']) ? $_POST['number'] : '+' . $_POST['number'];

            if (isset($config['global']['fm_transfer_to_username'])) {

                $timeToCall = date('Y-m-d H:i', mktime(date('H'), date('i') - 10, date('s'), date('m'), date('d'), date('Y')));

                $sql     = "SELECT * FROM pkg_refill WHERE description LIKE :description AND date BETWEEN '$timeToCall' AND  NOW() AND payment = 1 AND id_user =" . Yii::app()->session['id_user'];
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(":description", "%" . $_POST['number'] . "%", PDO::PARAM_STR);
                //$command->bindValue(":timeToCall", $timeToCall, PDO::PARAM_STR);
                //$command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_STR);
                $result = $command->queryAll();

                if (count($result) > 0) {
                    echo '<div id="container"><div id="form"> <div id="box-4">';
                    echo "<font color=red>You already send credit to this number. Wait minimal 10 minutes to new recharge</font>";
                    echo '</div></div></div>';
                    echo '<a href="../../index.php/transferToMobile/read">Back</a>';
                    exit;
                }

                $login       = $config['global']['fm_transfer_to_username'];
                $token       = $config['global']['fm_transfer_to_ token'];
                $agentProfit = $config['global']['fm_transfer_to_profit'];

                $key = time();
                $md5 = md5($login . $token . $key);

                if (isset($_POST['amount']) && $_POST['amount'] > 0) {

                    $rows    = json_decode(urldecode($_POST['rows']));
                    $product = $_POST['amount'];

                    foreach ($rows as $value) {

                        if ($value[0] == $product) {
                            $cost = $value[1];
                            break;
                        }

                    }

                    Yii::app()->session['currency'] = '€';

                    if (Yii::app()->session['currency'] == 'U$S') {
                        Yii::app()->session['currency'] = '$';
                    }

                    $cost = explode(Yii::app()->session['currency'], $cost);
                    $cost = $cost[1];

                    if (Yii::app()->session['currency'] == 'COP') {
                        $cost                           = $cost * 3066;
                        Yii::app()->session['currency'] = '$';
                    }

                    $sql     = "SELECT credit, creditlimit, id_user FROM pkg_user WHERE id = :id ";
                    $command = Yii::app()->db->createCommand($sql);
                    $command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_INT);
                    $resultUser = $command->queryAll();

                    if ($resultUser[0]['credit'] + $resultUser[0]['creditlimit'] < $cost) {

                        echo '<div id="container"><div id="form"> <div id="box-4"><div class="control-group">';
                        echo '<form action="" id="form1" method="POST">';
                        echo '<font color=red>ERROR:You no have enough credit to transfer</font>';
                        echo '</form>';
                        echo '</div></div></div></div>';
                        exit;
                    }
                    //check if agent have credit
                    if ($resultUser[0]['id_user'] > 1) {
                        $sql     = "SELECT credit, creditlimit, id_user FROM pkg_user WHERE id = :id ";
                        $command = Yii::app()->db->createCommand($sql);
                        $command->bindValue(":id", $resultUser[0]['id_user'], PDO::PARAM_INT);
                        $resultAgent = $command->queryAll();

                        if ($resultAgent[0]['credit'] + $resultAgent[0]['creditlimit'] < $cost) {

                            echo '<div id="container"><div id="form"> <div id="box-4"><div class="control-group">';
                            echo '<form action="" id="form1" method="POST">';
                            echo '<font color=red>ERROR:Your Agent no have enough credit to transfer</font>';
                            echo '</form>';
                            echo '</div></div></div></div>';
                            exit;

                        }
                    }

                    $url = $this->url . "login=$login&key=$key&md5=$md5&destination_msisdn=$number&msisdn=$number&delivered_amount_info=1&product=$product&action=topup";

                    if (preg_match("/5551982464731/", $number)) {
                        //echo $url;
                    }

                    $arrContextOptions = array(
                        "ssl" => array(
                            "verify_peer"      => false,
                            "verify_peer_name" => false,
                        ),
                    );

                    if (!$result = @file_get_contents($url, false, stream_context_create($arrContextOptions))) {
                        $result = '';
                    }

                    if (preg_match("/5551982464731/", $number)) {
                        //print_r($result);
                    }

                    $result = explode("error_txt=", $result);

                    if (preg_match("/Transaction successful/", $result[1])) {

                        echo '<div id="container"><div id="form"> <div id="box-4"><div class="control-group">';
                        echo '<form action="" id="form1" method="POST">';
                        echo '<font color=green>Success: ' . $result[1] . '</font>';
                        echo '</form>';
                        echo '</div></div></div></div>';

                        $sql     = "UPDATE  pkg_user SET credit = credit - :cost WHERE id = :id";
                        $command = Yii::app()->db->createCommand($sql);
                        $command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_INT);
                        $command->bindValue(":cost", $cost, PDO::PARAM_STR);
                        $command->execute();
                        if (preg_match("/5551982464731/", $number)) {
                            echo $sql . "<br>";
                        }

                        $costUser    = $cost * -1;
                        $description = 'Credit tranfered to mobile ' . $number;
                        $values      = ":id_user, :costUser, :description, 1";
                        $sql         = "INSERT INTO pkg_refill (id_user,credit,description,payment) VALUES ($values)";
                        $command     = Yii::app()->db->createCommand($sql);
                        $command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_INT);
                        $command->bindValue(":costUser", $costUser, PDO::PARAM_STR);
                        $command->bindValue(":description", $description, PDO::PARAM_STR);
                        $command->execute();

                        if (preg_match("/5551982464731/", $number)) {
                            echo $sql . "<br>";

                            echo print_r($resultUser) . "<br>";
                        }

                        if ($resultUser[0]['id_user'] > 1) {
                            $costAgent = $cost - ($cost * ($agentProfit / 100));
                            $sql       = "UPDATE  pkg_user SET credit = credit - :costAgent WHERE id = :id";
                            $command   = Yii::app()->db->createCommand($sql);
                            $command->bindValue(":id", $resultUser[0]['id_user'], PDO::PARAM_INT);
                            $command->bindValue(":costAgent", $costAgent, PDO::PARAM_STR);
                            $command->execute();
                            if (preg_match("/5551982464731/", $number)) {
                                echo $sql . "<br>";
                            }
                            $costAgent   = $costAgent * -1;
                            $description = 'Credit tranfered to mobile ' . $number;

                            $values  = ":id_user, :costAgent , :description,1";
                            $sql     = "INSERT INTO pkg_refill (id_user,credit,description,payment) VALUES ($values)";
                            $command = Yii::app()->db->createCommand($sql);
                            $command->bindValue(":id_user", $resultUser[0]['id_user'], PDO::PARAM_INT);
                            $command->bindValue(":costAgent", $costAgent, PDO::PARAM_STR);
                            $command->bindValue(":description", $description, PDO::PARAM_STR);
                            $command->execute();

                            if (preg_match("/5551982464731/", $number)) {
                                echo $sql . "<br>";
                            }

                        }

                    } else {
                        echo '<div id="container"><div id="form"> <div id="box-4"><div class="control-group">';
                        echo '<form action="" id="form1" method="POST">';
                        echo '<font color=red>ERROR: ' . $result[1] . '</font>';
                        echo '</form>';
                        echo '</div></div></div></div>';
                        echo '<a href="../../index.php/transferToMobile/read">Back</a>';
                    }

                } else {

                    $url = $this->url . "login=$login&key=$key&md5=$md5&destination_msisdn=$number&action=msisdn_info";

                    $arrContextOptions = array(
                        "ssl" => array(
                            "verify_peer"      => false,
                            "verify_peer_name" => false,
                        ),
                    );

                    if (!$result = @file_get_contents($url, false, stream_context_create($arrContextOptions))) {
                        $result = '';
                    }

                    //echo '<pre>';
                    if (preg_match("/Transaction successful/", $result)) {

                        $result = explode("\n", $result);
                        //print_r($result);

                        $product_list      = explode(",", substr($result[7], 13));
                        $retail_price_list = explode(",", substr($result[8], 18));

                        $local_currency = explode("=", $result[6]);
                        $local_currency = trim($local_currency[1]);

                        $country = explode("=", $result[0]);
                        $country = trim($country[1]);

                        $operator = explode("=", $result[2]);
                        $operator = trim($operator[1]);

                        $values = array();
                        $i      = 0;
                        foreach ($product_list as $key => $product) {
                            $values[] = array("$product", $local_currency . ' ' . trim($product) . ' = ' . $config['global']['fm_transfer_currency'] . ' ' . trim($retail_price_list[$i]));
                            $i++;
                        }

                        return array(
                            'success'         => true,
                            'rows'            => $values,
                            'country'         => $country,
                            'operator'        => $operator,
                            'fm_transfer_fee' => $config['global']['fm_transfer_show_selling_price'],
                        );

                    } else {
                        $result = explode("error_txt=", $result);

                        echo '<div id="container"><div id="form"> <div id="box-4">';
                        echo "<font color=red>" . $result[1] . "</font>";
                        echo '</div></div></div>';
                        echo '<a href="../../index.php/transferToMobile/read">Back</a>';
                        exit;
                    }
                }
            } else {
                echo json_encode(array(
                    'success' => false,
                    'msg'     => 'Service inactive',
                ));
            }
        }
    }

    public function actionPrintRefill()
    {

        if (isset($_GET['id'])) {
            $config    = LoadConfig::getConfig();
            $id_refill = $_GET['id'];
            $sql       = "SELECT * FROM pkg_refill WHERE id = :id_refill AND id_user = :id_user";
            $command   = Yii::app()->db->createCommand($sql);
            $command->bindValue(":id_refill", $id_refill, PDO::PARAM_INT);
            $command->bindValue(":id_user", Yii::app()->session['id_user'], PDO::PARAM_INT);
            $resultRefill = $command->queryAll();

            echo $config['global']['fm_transfer_print_header'] . "<br>";

            $sql     = "SELECT * FROM pkg_user WHERE id = :id";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(":id", Yii::app()->session['id_user'], PDO::PARAM_INT);
            $resultUser = $command->queryAll();

            echo $resultUser[0]['company_name'] . "<br>";

            echo "Trx ID: " . $resultRefill[0]['id'] . "<br>";

            echo $resultRefill[0]['date'] . "<br>";
            $number = trim(preg_replace("/Credit tranfered to mobile/", "", $resultRefill[0]['description']));
            $number = explode(" ", $number);
            echo "Mobile: " . $number[0] . "<br>";
            $amount = number_format($resultRefill[0]['credit'], 2) * -1;
            echo "Amount: <input type=text' style='text-align: right;' size='5' value='$amount'> <br><br>";

            echo $config['global']['fm_transfer_print_footer'] . "<br><br>";

            echo '<td><a href="javascript:window.print()">Print</a></td>';
        } else {
            echo ' Invalid reffil';
        }
    }

}
?>

<script type="text/javascript">

	function button1(buttonId) {
		document.getElementById("fistButtondiv").style.display = 'none';
	  	document.getElementById("fistButtondivWait").innerHTML = "<font color = green>Wait! </font>";
	}
	function button2(buttonId) {
	  document.getElementById("secondButtondiv").style.display = 'none';
	  	document.getElementById("secondButtondivWait").innerHTML = "<font color = green>Wait! </font>";
	}
	function showPrice(argument) {
		text = document.getElementById('amountfiel').options[document.getElementById('amountfiel').selectedIndex].text;
		var valueAmout = text.split(' ');
		fee = Number('1.'+argument);

		newText = '<b>Selling Price</b>'+' <font color=blue size=7><b>'+valueAmout[3]+ ' '+valueAmout[4] * fee+'</b></font>'
		document.getElementById('sellingPrice').innerHTML = newText;
	}
</script>
