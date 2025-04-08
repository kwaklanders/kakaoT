<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>

<?
//파라미터
$order_info = $_REQUEST["order_info"];

//파싱
$order_info_json = json_decode($order_info, true);

$barcode		= $order_info_json["barcode"];

$strSql = "";
$strSql .= " ";
$strSql .= " select  ";
$strSql .= " status, barcode, detail_use_date, detail_cancel_date  ";
$strSql .= " from salti_order_detail  ";
$strSql .= " where barcode = '".$barcode."' ";

$rsRows = mysql_query($strSql);
$rsCount = mysql_num_rows($rsRows);

if( $rsCount != 1 )
{
	$rtn_data["header"]["result_code"]		= "1003";
	$rtn_data["header"]["result_msg"]		= "주문 미조회";
}
else
{
	while($rows = mysql_fetch_array($rsRows))
	{
		if( $rows["status"] == 0 )
		{
			$rtn_data["header"]["result_code"]		= "1000";
			$rtn_data["header"]["result_msg"]		= "미사용";

			$rtn_data["body"]["barcode"]			= $rows["barcode"];
		}
		else if( $rows["status"] == 1 )
		{
			$rtn_data["header"]["result_code"]		= "1001";
			$rtn_data["header"]["result_msg"]		= "사용";

			$rtn_data["body"]["barcode"]			= $rows["barcode"];
			$rtn_data["body"]["use_date"]			= $rows["detail_use_date"];
		}
		else if( $rows["status"] == 2 )
		{
			$rtn_data["header"]["result_code"]		= "1002";
			$rtn_data["header"]["result_msg"]		= "취소";

			$rtn_data["body"]["barcode"]			= $rows["barcode"];
			$rtn_data["body"]["cancel_date"]		= $rows["detail_cancel_date"];
		}
		else
		{
			$rtn_data["header"]["result_code"]		= "1003";
			$rtn_data["header"]["result_msg"]		= "주문조회 에러(관리자문의)";
		}
	}
}

echo json_encode($rtn_data, JSON_UNESCAPED_UNICODE);