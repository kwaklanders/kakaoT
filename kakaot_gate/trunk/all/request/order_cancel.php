<?require_once($_SERVER["DOCUMENT_ROOT"]."/all/skin/common.php");?>

<?
//주문취소
$order_raw = $_REQUEST["order_info"];

$order_info = json_decode($order_raw, true);

if( count($order_info) == 0 )
{
	$return_data["return_code"] = "9999";
	$return_data["return_msg"] = "파라미터가 없습니다.";

	echo_json_encode($return_data);
	exit;
}

//바코드 확인
if( $order_info["barcode"] == "" || $order_info["barcode"] == null )
{
	$return_data["return_code"] = "9999";
	$return_data["return_msg"] = "주문 상세번호를 확인해주세요.";

	echo_json_encode($return_data);
	exit;
}

//관리자 코드 확인
$check_admin = const_json(ADMIN_CHECK, $order_info["admin_code"]);

if( $check_admin == "" || $check_admin == null )
{
	$return_data["return_code"] = "9999";
	$return_data["return_msg"] = "관리자 코드를 확인해주세요.";

	echo_json_encode($return_data);
	exit;
}

$objResponse = new Response;

$return_json = $objResponse->order_cancel($order_info);

echo_json_encode($return_json);


?>