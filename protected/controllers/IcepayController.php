<?php

/**
 * Url for moip ruturn http://ip/billing/index.php/icepay .
 */
class IcepayController extends BaseController
{


	public function actionIndex()
	{
		if (isset($_POST))
		{
			$sql = "SELECT * FROM pkg_method_pay WHERE payment_method = 'IcePay'";
			$resultMethod = Yii::app()->db->createCommand($sql)->queryAll();

			if(preg_match("/ /", $resultMethod[0]['show_name'])){
				$type= explode(" ", $resultMethod[0]['show_name']);
				$typePayment = 'ICEPAY_'.$type[0];
			}else{
				$typePayment = 'ICEPAY_'.$resultMethod[0]['show_name'];
			}

			require_once( "lib/icepay/icepay.php" );
			$method = new $typePayment( $resultMethod[0]['username'] , $resultMethod[0]['pagseguro_TOKEN']);

			if ( !$method->OnSuccess() ){
				$data = $method->GetData();

				$sql ="DELETE FROM pkg_refill_icepay WHERE id = :id";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id", $data->orderID, PDO::PARAM_STR);
				$command->execute();

				echo '<h1>Oops, some error occured</h1>
    				<p>Error description : OnSuccess FALSE '.$data->statusCode.' </p>';
				exit();
			} 

			$data = $method->GetData();

			/*
				stdClass Object ( [status] => OK 
				[statusCode] => Payment Completed simulation via Test Mode 
				[merchant] => 24984 
				[orderID] => @101278590 
				[paymentID] => 12437189 
				[reference] => 
				[transactionID] => 12437189 
				[checksum] => 6f941ba70719e4a1f19d6086247b222b954393a9 )

				http://www.thantel.com/mbilling/index.php/icepay?Status=OPEN&StatusCode=Merchant+server+returned+error+or+not+reachable.&Merchant=24984&OrderID=5&PaymentID=12437479&Reference=&TransactionID=0050001667513203&Checksum=caab79e08074dd380e551ed14b3244f8eaf7a28d&PaymentMethod=IDEAL
			*/

			if ( $data->status == "OK" || $data->status == "OPEN" ){
				echo '<h1>Thank You! You have successfully completed the payment!</h1>';
				$sql ="SELECT * FROM pkg_refill_icepay WHERE id = :id";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id", $data->orderID, PDO::PARAM_STR);
				$result = $command->queryAll();

				$sql ="DELETE FROM pkg_refill_icepay WHERE id = :id";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":id", $data->orderID, PDO::PARAM_STR);
				$command->execute();
				
				if (isset($result[0]['credit'])) {
				
					$monto = $result[0]['credit'];
					$description = 'Ycepay No.' . $data->paymentID;				
					Process ::releaseUserCredit($result[0]['id_user'], $monto, $description, $data->paymentID);						
				
				}
				else{
					echo "paymente id= $data->orderID not found";
				}

			}
			else{
				echo '<h1>Oops, some error occured</h1>';
    				echo '<p>Error description: '. $data->statusCode.'</p>';
			}		
		    
		}
		else{
			echo 'not allow';
		}
	}
}