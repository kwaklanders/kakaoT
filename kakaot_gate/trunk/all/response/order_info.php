<?require_once($_SERVER["DOCUMENT_ROOT"]."/all/skin/common.php");?>

<?
//주문조회(건별)
$order_raw = $_REQUEST["order_info"];

$order_info = json_decode($order_raw, true);

if( count($order_info) == 0 )
{
	$return_data["result_code"] = "9999";
	$return_data["result_msg"] = "파라미터가 없습니다.";

	echo_json_encode($return_data);
	exit;
}

//관리자 코드 확인
$check_admin = const_json(ADMIN_CHECK, $order_info["admin_code"]);

if( $check_admin == "" || $check_admin == null )
{
	$return_data["result_code"] = "9999";
	$return_data["result_msg"] = "관리자 코드를 확인해주세요.";

	echo_json_encode($return_data);
	exit;
}

$objResponse = new Response;

$return_json = $objResponse->order_info($order_info);

if (  $order_info["agent_code"] == "kakao_mo" )
{
	header("Content-type: application/json; charset=utf-8");
	echo_json_encode($return_json);
}
else
{
	echo_json_encode($return_json);
}


?>