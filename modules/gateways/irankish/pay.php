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

$gatewaymodule 	= 'irankish';
$GATEWAY 		= getGatewayVariables($gatewaymodule);

$license 		= $GATEWAY['zipmarket_license'];
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://zipmarket.ir/api/json.php');
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/json'));
curl_setopt($curl, CURLOPT_POSTFIELDS, "license=$license&secret_key=whmcs_irankish&domain=". $_SERVER['SERVER_NAME']);
curl_setopt($curl, CURLOPT_TIMEOUT, 400);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$result = json_decode(curl_exec($curl));
curl_close($curl);

if ($result->status == 100){

	if (!$GATEWAY['type']) die('Module Not Activated');

	$amount 					= intval($_POST['amount']);
	$invoiceid 					= $_POST['invoiceid']; 
	$email 						= $_POST['email'];
	$CallbackURL 				= $CONFIG['SystemURL'] .'/modules/gateways/irankish/callback.php?invoiceid='. $invoiceid;
	$payMerchantCode 			= $GATEWAY['webgate_id'];
	
	$wsdl 						= "https://ikc.shaparak.ir/XToken/Tokens.xml";
	$client 					= new nusoap_client($wsdl,true);
	$client->soap_defencoding 	= 'UTF-8'; 
	$params['amount'] 			= $amount;
	$params['merchantId'] 		= $payMerchantCode;
	$params['invoiceNo'] 		= $invoiceid;
	$params['paymentId'] 		= $invoiceid;
	$params['revertURL'] 		= $CallbackURL;
	$result 					= $client->call("MakeToken", array($params));

	if ($result['MakeTokenResult']['token']) {
		
		$token = $result['MakeTokenResult']['token'];
		
		echo "<form id='irankishpeyment' action='https://ikc.shaparak.ir/tpayment/payment/Index' method='post'>
		<input type='hidden' name='token' value='$token' />
		<input type='hidden' name='merchantId' value='$payMerchantCode'>
		</form><script>document.forms['irankishpeyment'].submit()</script>";

	} else {
		print_r($result);
		exit;
	}
} else {
	echo "WHMCS irankish License Module Error : ". $result->status;
	exit;
}
?>