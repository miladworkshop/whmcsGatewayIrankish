<?php
/*
	author 	: Milad Maldar
	URL		: http://miladworkshop.ir
*/

function irankish_config()
{
    $configarray = array(
		"FriendlyName" 			=> array("Type" => "System", "Value"=>"ماژول درگاه ایران کیش"),
		"webgate_id" 			=> array("FriendlyName" => "مرچنت کد", "Type" => "text", "Size" => "50", ),
		"shaKey" 				=> array("FriendlyName" => "کلید تبادل", "Type" => "text", ),
		"Currencies" 			=> array("FriendlyName" => "واحد پول سیستم", "Type" => "dropdown", "Options" => "rial,toman", "Description" => "لطفا واحد پول سیستم خود را انتخاب کنید.",),
		"send_telegram_ok" 		=> array("FriendlyName" => "اطلاع از تراکنش های موفق", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "ارسال گزارش تراکنش های مالی موفق این درگاه از طریق تلگرام",),
		"send_telegram_error" 	=> array("FriendlyName" => "ارسال هشدار تراکنش های ناموفق و خطاها", "Type" => "dropdown", "Options" => "No,Yes", "Description" => "ارسال گزارش تراکنش های ناموفق و خطاهای این درگاه از طریق تلگرام",),
		"telegram_chatid" 		=> array("FriendlyName" => "Chat ID تلگرام", "Type" => "text", "Description" => "چت آی دی تلگرام خود را وارد کنید - <a href='http://milad.in/telegram-chat-id' target='_blank' style='color:#0000FF'>آموزش دریافت Chat ID تلگرام</a>", ),
		"telegram_botToken" 	=> array("FriendlyName" => "بوت توکن تلگرام", "Type" => "text", "Description" => "یک ربات با استفاده از ربات BotFather ایجاد کنید", ),
		"zipmarket_license" 	=> array("FriendlyName" => "کد لایسنس", "Type" => "text", "Description" => "کد لایسنس دریافت شده از سایت <a href='http://zipmarket.ir' target='_blank' style='color:#0000FF'>زیپ مارکت</a>", ),
		"author" 				=> array("FriendlyName" => "برنامه نویس", "Type" => "", "Description" => "طراحی و برنامه نویسی شده توسط <a href='http://milad.in' target='_blank' style='color:#FF0000'>میلاد مالدار</a>", ),
    );
	return $configarray;
}

function irankish_link($params) {
    $currencies = $params['Currencies'];
    $invoiceid 	= $params['invoiceid'];
    $amount 	= $params['amount'];
    $email 		= $params['clientdetails']['email'];

	$amount = $params['amount']-'.00';
	if($params['Currencies'] == 'toman'){
		$amount = round($amount*10);
	}
	
	$code = '<form method="post" action="modules/gateways/irankish/pay.php">
	<input type="hidden" name="invoiceid" value="'. $invoiceid .'" />
	<input type="hidden" name="amount" value="'. $amount .'" />
	<input type="hidden" name="email" value="'. $email .'" />
	<input type="submit" name="pay" value=" پرداخت " /></form>';
	return $code;
}
?>