<?require_once($_SERVER["DOCUMENT_ROOT"]."/all/skin/common.php");?>

<?
//주문등록
$order_raw = $_REQUEST["order_info"];

$order_info = json_decode($order_raw, true);

if( count($order_info) == 0 )
{
	$return_data["return_code"] = "9999";
	$return_data["return_msg"] = "파라미터가 없습니다.";

	header("Content-type: application/json; charset=utf-8");
	echo_json_encode($return_data);
	exit;
}


if ( $order_info["agent_code"] == "nolbal" )
{
	$count = 0;

	foreach($order_info["order_list"] as $k=>$v)
	{
		if ( $order_info["admin_code"] == "" || $order_info["admin_code"] == null )
		{
			if ( strpos($v["product_code"], "|") !== false )
			{
				$admin_code = explode("|", $v["product_code"]);

				$order_info["admin_code"] = $admin_code[0];

				$order_info["order_list"][$count]["product_code"] = $admin_code[1];
			
				$count ++;
			}
			else
			{
				$order_info["admin_code"] = "qpos";
			}
		}
		else
		{
			continue;
		}

	}
}
else if ( $order_info["agent_code"] == "datepop" )
{
	if ( $order_info["admin_code"] = "qpos_system" )
	{
		$order_info["admin_code"] = "qpos";
	}

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

$return_json = $objResponse->order_unuse($order_info);

header("Content-type: application/json; charset=utf-8");
echo_json_encode($return_json);

?>