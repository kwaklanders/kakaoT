<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>

<?
//파라미터
$order_info = $_REQUEST["order_info"];

//파싱
$order_info_json = json_decode($order_info, true);

$get_date		= $order_info_json["get_date"];
$agent_code		= $order_info_json["agent_code"];
$product_code	= $order_info_json["product_code"];

$strSql = "";
$strSql .= " ";
$strSql .= " select ";
$strSql .= " order_num, barcode, detail_use_date ";
$strSql .= " from salti_order_detail  ";
$strSql .= " where status = '1' ";
$strSql .= " and DATE_FORMAT(detail_use_date,'%Y-%m-%d') = '".$get_date."' ";
$strSql .= " and agent_code = '".$agent_code."'  ";
$strSql .= " and product_code = '".$product_code."' ";

//echo $strSql;
//exit;

$rsRows = mysql_query($strSql);
$rsCount = mysql_num_rows($rsRows);

if( $rsCount == 0 )
{
	$rtn_data["header"]["result_code"]		= "2001";
	$rtn_data["header"]["result_msg"]		= "조회되는 주문정보 없음";
}
else
{
	$rtn_data["header"]["result_code"]		= "2000";
	$rtn_data["header"]["result_msg"]		= "사용티켓 조회성공";

	while($rows = mysql_fetch_array($rsRows))
	{
		unset($rtn_data_raw);

		$rtn_data_raw["order_num"]			 = $rows["order_num"];
		$rtn_data_raw["barcode"]			 = $rows["barcode"];
		$rtn_data_raw["use_date"]			 = $rows["detail_use_date"];

		$rtn_data["body"][] = $rtn_data_raw;
	}
}

echo json_encode($rtn_data, JSON_UNESCAPED_UNICODE);