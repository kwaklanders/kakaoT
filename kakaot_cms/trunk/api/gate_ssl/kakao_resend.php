<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>

<?
//파라미터
$order_info = $_REQUEST["order_info"];

//파싱
$order_info_json = json_decode($order_info, true);

$order_num = $order_info_json["order_num"];

//구매완료 알림톡발송
//주문정보
$order_info = get_order_info_cms($order_num);

if($order_info["result_code"] == "0000")
{
	//주문상세정보
	$order_pos_detail_info = get_order_pos_detail_info_cms($order_num);

	$product_code = $order_pos_detail_info["list"][0]["product_code"];

	//발송문자정보
	$sms_info = getProductSmsInfoNotiCms($product_code);

	$send_type			= $sms_info["send_type"];
	$sms_num			= $sms_info["sms_num"];
	$sms_type			= $sms_info["sms_type"];
	$callback			= $sms_info["callback"];
	$template_code		= $sms_info["template_code"];
	$button_yn			= $sms_info["button_yn"];
	$content			= $sms_info["content"];
	$button_name		= $sms_info["button_name"];
	$button_url			= $sms_info["button_url"];
	$sender_key			= $sms_info["sender_key"];

	$msg_arr["order_num"]		= $order_num;
	$msg_arr["ticket_code"]		= $ticket_code;
	$msg_arr["callback"]		= $callback;
	$msg_arr["recipient_num"]	= aes256decode($order_info["list"]["buy_hp"]);
	$msg_arr["template_code"]	= $template_code;
	$msg_arr["button_yn"]		= $button_yn;
	$msg_arr["content"]			= $content;

	$msg_arr["buy_date"]		= $order_info["list"]["buy_date"];

	$content = set_content_cms($content, $msg_arr);
	$button_url = set_content_cms($button_url, $msg_arr);

	if( $sms_info["sms_type"] == 2 )
	{
		$result = send_kakao_standard_cms($send_type, $sms_num, $sender_key, $callback, $order_info["list"]["buy_hp"], $template_code, $button_yn, $button_name, $button_url, $content, $order_num, $send_num);
	}

	$log_info["mgr_idx"] = $_SESSION["mgr_idx"];
	$log_info["work_type"] = "update";
	$log_info["work_page"] = "order";
	$log_info["work_name"] = "재발송";
	$log_info["key_name"] = "order_num";
	$log_info["key_value"] = $order_num;
	$log_info["content"] = "알림톡 재발송";

	set_log($log_info);

	$return_data = json_decode($result, true);

	$rtn_data["header"]["result_code"]	= $return_data["result_code"];
	$rtn_data["header"]["result_msg"]	= $return_data["result_msg"];

	$rtn_data["body"]["order_num"]		= $order_num;

	echo json_encode($rtn_data, JSON_UNESCAPED_UNICODE);
}
else
{
	$rtn_data["header"]["result_code"]	= $order_info["result_code"];
	$rtn_data["header"]["result_msg"]	= $order_info["result_msg"];

	$rtn_data["body"]["order_num"]		= $order_num;

	echo json_encode($rtn_data, JSON_UNESCAPED_UNICODE);
}