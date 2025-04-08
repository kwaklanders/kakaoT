<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>

<?
//파라미터
$order_info = $_REQUEST["order_info"];

//파싱
$order_info_json = json_decode($order_info, true);

$strSql = "";
$strSql .= " ";
$strSql .= " select ";
$strSql .= " status, order_num, barcode, detail_use_date, detail_cancel_date ";
$strSql .= " from salti_order_detail ";
$strSql .= " where barcode =  '".$order_info_json["barcode"]."' ";

$rsRows = mysql_query($strSql);
$rsCount = mysql_num_rows($rsRows);

if( $rsCount == 0 )
{
	$rtn_data["header"]["result_code"]		= "3003";
	$rtn_data["header"]["result_msg"]		= "조회되는 주문정보 없음";
}
else
{
	while($rows = mysql_fetch_array($rsRows))
	{
		if( $rows["status"] == 0 )
		{
			//트랜잭션 시작
			mysql_query("SET AUTOCOMMIT=0");
			mysql_query("BEGIN");

			$strSql = "";
			$strSql .= " update salti_order_detail ";
			$strSql .= " set ";
			$strSql .= " status = 2, ";
			$strSql .= " detail_cancel_date = '".date("Y-m-d H:i:s")."' ";
			$strSql .= " where barcode = '" . $rows["barcode"]."' ";

//			echo $strSql;
//			exit;

			$result_order_detail = mysql_query($strSql);
			$order_detail_update_count = mysql_affected_rows();

			if( $result_order_detail && ($order_detail_update_count == 1) )
			{
				$strSql = "";
				$strSql .= " update salti_pos_detail ";
				$strSql .= " set ";
				$strSql .= " status = 2, ";
				$strSql .= " cancel_date = '".date("Y-m-d H:i:s")."' ";
				$strSql .= " where barcode = '" . $rows["barcode"]."' ";

				$result_pos_detail = mysql_query($strSql);
				$pos_detail_update_count = mysql_affected_rows();

				if( $result_pos_detail && ($pos_detail_update_count == 1) )
				{
					$result = check_order($rows["order_num"]);

					if($result)
					{
						$check_flag = true;
					}
					else
					{
						$check_flag = false;
					}
				}
				else
				{
					$check_flag = false;
				}
			}
			else
			{
				$check_flag = false;
			}

			if($check_flag)
			{
				$rtn_data["header"]["result_code"]		= "3000";
				$rtn_data["header"]["result_msg"]		= "취소성공";

				$rtn_data["body"]["barcode"]			= $rows["barcode"];

				mysql_query("COMMIT");
			}
			else
			{
				$rtn_data["header"]["result_code"]		= "3004";
				$rtn_data["header"]["result_msg"]		= "주문취소 에러(관리자 문의)";

				$rtn_data["body"]["barcode"]			= $rows["barcode"];

				mysql_query("ROLLBACK");
			}

			mysql_query("SET AUTOCOMMIT=1");
		}
		else if ( $rows["status"] == 1 )
		{
			$rtn_data["header"]["result_code"]		= "3001";
			$rtn_data["header"]["result_msg"]		= "사용된 티켓";

			$rtn_data["body"]["barcode"]			= $rows["barcode"];
			$rtn_data["body"]["use_date"]			= $rows["detail_use_date"];
		}
		else if ( $rows["status"] == 2 )
		{
			$rtn_data["header"]["result_code"]		= "3002";
			$rtn_data["header"]["result_msg"]		= "이미 취소된 티켓";

			$rtn_data["body"]["barcode"]			= $rows["barcode"];
			$rtn_data["body"]["cancel_date"]		= $rows["detail_cancel_date"];
		}
	}
}

echo json_encode($rtn_data, JSON_UNESCAPED_UNICODE);

function check_order($order_num)
{
	$check_flag = false;

	$strSql = "";
	$strSql .= " ";
	$strSql .= " select ";
	$strSql .= " buy_count ";
	$strSql .= " from salti_order ";
	$strSql .= " where order_num = '".$order_num."' ";

	$rtnvalue = mysql_query($strSql);

	while($rows = mysql_fetch_assoc($rtnvalue))
	{
		$buy_count = $rows["buy_count"];
	}

	$strSql = "";
	$strSql .= " ";
	$strSql .= " select ";
	$strSql .= " status ";
	$strSql .= " from salti_order_detail ";
	$strSql .= " where order_num = '".$order_num."' ";

	$rtnvalue_deatil = mysql_query($strSql);

	$cancel_count = 0;

	while($rows = mysql_fetch_assoc($rtnvalue_deatil))
	{
		if($rows["status"] == 2)
		{
			$cancel_count ++;
		}
	}

	if($buy_count == $cancel_count)
	{
		$strSql = "";
		$strSql .= " update salti_order ";
		$strSql .= " set ";
		$strSql .= " status = 5, ";
		$strSql .= " cancel_date = '".date("Y-m-d H:i:s")."' ";
		$strSql .= " where order_num = '" . $order_num."' ";

		$result = mysql_query($strSql);
		$update_count = mysql_affected_rows();

		if( $result && ($update_count == 1) )
		{
			$check_flag = true;
		}
		else 
		{
			$check_flag = false;
		}
	}
	else
	{
		$check_flag = true;
	}

	return $check_flag;
}
