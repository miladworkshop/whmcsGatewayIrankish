<?php
/*
	author	: Milad Maldar
	URL		: http://miladworkshop.ir
*/

if(file_exists('../../../init.php')){require( '../../../init.php' );}else{require("../../../dbconnect.php");}
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

require_once('nusoap.php');

$gatewaymodule 		= 'irankish';
$GATEWAY 			= getGatewayVariables($gatewaymodule);

$invoiceid 			= $_GET['invoiceid'];

if(!empty($invoiceid)){
	if (!$GATEWAY['type']) die('Module Not Activated');

	$whmcs_url		= $CONFIG['SystemURL'];
	$token 			= trim($_POST['token']);
	$resultCode 	= trim($_POST['resultCode']);
	$paymentId 		= trim($_POST['paymentId']);
	$referenceId 	= trim($_POST['referenceId']);

	$results 		= select_query( "tblinvoices", "", array( "id" => $paymentId ) );
	$data 			= mysql_fetch_array($results);
	$db_amount 		= strtok($data['total'],'.');


	if ($resultCode == '100') {

		$wsdl = "https://ikc.shaparak.ir/XVerify/Verify.xml";
		$client = new nusoap_client($wsdl,true);
		$client->soap_defencoding='UTF-8';
		$params['token'] 			= $token;
		$params['referenceNumber'] 	= $referenceId;
		$params['merchantId'] 		= $GATEWAY['webgate_id'];
		$params['sha1Key'] 			= $GATEWAY['shaKey'];
		
		$result = $client->call("KicccPaymentsVerification", array($params));
		
		$amount = $result['KicccPaymentsVerificationResult'];
		
		if($GATEWAY['Currencies'] == 'toman'){
			$amount = $result['KicccPaymentsVerificationResult']/10;
		}
		
		$cartNumber = $_POST['cardNo'];
		
		if ($amount ==  $db_amount)
		{
			addInvoicePayment($paymentId, $referenceId, $amount, 0, $gatewaymodule);
			logTransaction($GATEWAY["name"], array(
				'invoiceid' 	=> $paymentId,
				'order_id' 		=> $paymentId,
				'amount' 		=> $amount ." ". $GATEWAY['Currencies'],
				'tran_id' 		=> $paymentId,
				'refcode' 		=> $referenceId,
				'CardNumber'	=> $cartNumber,
				'status' 		=> "OK"
			), "موفق");
			
			if ($GATEWAY['send_telegram_ok'] == "Yes") {
				
				$pm = "یک تراکنش موفق در سیستم ثبت شد ( درگاه پرداخت ایران کیش )
				----------------------------------------------------------------------------------------------\n

				Gateway : irankish

				Price : $amount $GATEWAY[Currencies]
				Ref Code : $referenceId
				Order ID : $paymentId
				Invoice ID : $paymentId
				Customer Cart Number : $cartNumber";

				$chat_id 	= $GATEWAY['telegram_chatid'];
				$botToken 	= $GATEWAY['telegram_botToken'];
				$data 		= array('chat_id' => $chat_id, 'text' => $pm);

				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, "http://telegram.europe.miladworkshop.ir/bot{$botToken}/sendMessage");
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				curl_setopt($curl, CURLOPT_TIMEOUT, 10);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_exec($curl);
				curl_close($curl);
			}
			
		} else {
			logTransaction($GATEWAY["name"] ,  array('invoiceid'=>$paymentId,'order_id'=>$paymentId,'amount'=>$amount,'tran_id'=>$paymentId,'status'=>$result), "ناموفق");
			
			if ($GATEWAY['send_telegram_error'] == "Yes") {
				
				$pm = "گزارش تراکنش ناموفق / خطا ( درگاه پرداخت ایران کیش )
				----------------------------------------------------------------------------------------------\n
				
				Gateway : irankish
				
				Pay Price : $amount $GATEWAY[Currencies]
				Invoice Price : $db_amount $GATEWAY[Currencies]
				Order ID : $paymentId
				Invoice ID : $paymentId
				
				Error Code : مبلغ پرداخت شده با مبلغ فاکتور یکسان نیست";
				
				$chat_id 		= $GATEWAY['telegram_chatid'];
				$botToken 		= $GATEWAY['telegram_botToken'];
				$data = array('chat_id' => $chat_id, 'text' => $pm);
				$curl = curl_init();

				curl_setopt($curl, CURLOPT_URL, "http://telegram.europe.miladworkshop.ir/bot$botToken/sendMessage");
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				curl_setopt($curl, CURLOPT_TIMEOUT, 10);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_exec($curl);
				curl_close($curl);
			}
			
		}
	} else {
		logTransaction($GATEWAY["name"] ,  array('invoiceid'=>$paymentId,'order_id'=>$paymentId,'amount'=>$amount,'tran_id'=>$paymentId,'status'=>$resultCode), "ناموفق") ; 

		if ($GATEWAY['send_telegram_error'] == "Yes") {
			
			$pm = "گزارش تراکنش ناموفق / خطا ( درگاه پرداخت ایران کیش )
			----------------------------------------------------------------------------------------------\n
			
			Gateway : irankish
			
			Order ID : $invoiceid
			Invoice ID : $invoiceid
			
			Error Code : $resultCode";
			
			$chat_id 		= $GATEWAY['telegram_chatid'];
			$botToken 		= $GATEWAY['telegram_botToken'];
			$data = array('chat_id' => $chat_id, 'text' => $pm . "\n\n----------------------------------------------------------------------------------------------\n" . base64_decode("V0hNQ1MgVGVsZWdyYW0gTm90aWZpY2F0aW9uIEJ5IE1pbGFkLmlu"));
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, "http://telegram.europe.miladworkshop.ir/bot$botToken/sendMessage");
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			curl_setopt($curl, CURLOPT_TIMEOUT, 10);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_exec($curl);
			curl_close($curl);
		}
	}
	$action = $whmcs_url ."/viewinvoice.php?id=". $invoiceid;
	header('Location: '. $action);
} else {
	echo "invoice id is blank";
}
?>