<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>
<?

$order_info = $_REQUEST["order_info"];

//파싱
$order_info_json = json_decode($order_info, true);

$agent_code				= $order_info_json["agent_code"];
$order_num				= $order_info_json["order_num"];
$barcode				= $order_info_json["barcode"];
$channel_code			= $order_info_json["channel_code"];
$product_code			= $order_info_json["product_code"];
$product_detail_code	= $order_info_json["product_detail_code"];
$stock					= $order_info_json["stock"];

error_log("    ");
error_log("    ");
error_log(">>>>>>>>> START >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
error_log("[qpos api request order_insert_resend]");
error_log("    ");
error_log("order_num:".$order_num."");
error_log("order_info:".json_encode($order_info_json, JSON_UNESCAPED_UNICODE)."");
error_log(">>>>>>>>> END   >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
error_log("    ");
error_log("    ");

//트랜잭션 시작
$tran = true;

mysql_query("SET AUTOCOMMIT=0");
mysql_query("BEGIN");

if($barcode == "" || $barcode == null)
{
	$tran_check = false;

	$rtn_data["header"]["result_code"]			= "0006";
	$rtn_data["header"]["result_msg"]			= "주문 상세번호 값이 없습니다.";

	$rtn_data["body"]["barcode"]				= $barcode;

	echo_json_encode($rtn_data);
	exit;
}
else
{
	//주문상세 중복확인
	$order_detail_exists = exists_order_detail_cms($barcode);

	if( $order_detail_exists != 0 )
	{
		$rtn_data["header"]["result_code"]			= "0003";
		$rtn_data["header"]["result_msg"]			= "주문 상세번호 중복";

		$rtn_data["body"]["barcode"]				= $barcode;

		$tran = false;

		echo_json_encode($rtn_data);
		exit;
	}
	else 
	{
		//채널 옵션맵핑 정보 확인
		$strSql = "";
		$strSql .= " ";
		$strSql .= " select ";
		$strSql .= " idx, ";
		$strSql .= " product_code, ";
		$strSql .= " option_code, ";
		$strSql .= " channel_code, ";
		$strSql .= " deal_code, ";
		$strSql .= " option_name ";
		$strSql .= " from salti_option_mapping ";
		$strSql .= " where 1=1 ";
		$strSql .= " and salti_option_mapping.product_code = ".$product_code." ";
		$strSql .= " and salti_option_mapping.option_code = ".$product_detail_code." ";
		$strSql .= " and salti_option_mapping.channel_code = ".$channel_code." ";

		$result_salti_product_mapping = mysql_query($strSql);
		$rtncount_salti_product_mapping = mysql_num_rows($result_salti_product_mapping);

		//미조회 딜코드+옵션+채널
		if( $rtncount_salti_product_mapping == 0 )
		{
			$rtn_data["header"]["result_code"]			= "0001";
			$rtn_data["header"]["result_msg"]			= "상품정보 미조회";

			$rtn_data["body"]["product_code"]			= $product_code;
			$rtn_data["body"]["product_detail_code"]	= $product_detail_code;
			$rtn_data["body"]["channel_code"]			= $channel_code;

			echo_json_encode($rtn_data);
			exit;
		}
		//중복
		else if( $rtncount_salti_product_mapping > 1 )
		{
			$rtn_data["header"]["result_code"]			= "0002";
			$rtn_data["header"]["result_msg"]			= "옵션정보 중복(관리자 문의)";

			$rtn_data["body"]["product_code"]			= $product_code;
			$rtn_data["body"]["product_detail_code"]	= $product_detail_code;
			$rtn_data["body"]["channel_code"]			= $channel_code;

			echo_json_encode($rtn_data);
			exit;
		}
		//성공
		else
		{
			while($rows_salti_product_mapping = mysql_fetch_array($result_salti_product_mapping))
			{
				$option_mapping_idx	= $rows_salti_product_mapping["idx"];
				$option_name		= $rows_salti_product_mapping["option_name"];
				break;
			}

			//상품상세정보
			$product_info = get_product_info($product_code, $product_detail_code);

			//주문상세정보
			$order_detail_info["agent_code"] = $agent_code;
			$order_detail_info["order_num"] = $order_num; //주문번호
			$order_detail_info["mem_code"] = $product_info["product_info"]["mem_code"];	//시설코드
			$order_detail_info["channel_code"] = $channel_code;	//채널코드
			$order_detail_info["product_code"] = $product_info["product_info"]["product_code"];	//상품코드
			$order_detail_info["product_detail_code"] = $product_info["product_info"]["product_detail_code"];	//기초상품코드
			$order_detail_info["ticket_option_code"] = $product_info["product_info"]["ticket_option_code"];	//권종코드:대인/소인....
			$order_detail_info["option_name"] = $product_info["product_info"]["option_name"];	//옵션명
			$order_detail_info["barcode"] = $barcode;	//바코드
			$order_detail_info["count"] = $stock;	//수량

			$order_detail_info["sale_price"] = $product_info["product_info"]["sale_price"];	//정상금액
			$order_detail_info["dis_price"] = $product_info["product_info"]["dis_price"];	//판매금액
			$order_detail_info["bill_price"] = $product_info["product_info"]["bill_price"];	//채널정산금액
			$order_detail_info["remit_price"] = $product_info["product_info"]["remit_price"];	//시설정산금액

			$order_detail_info["product_detail_idx"] = $product_info["product_info"]["product_detail_idx"];	//상품상세idx
			$order_detail_info["option_mapping_idx"] = $option_mapping_idx;	//맵핑idx..

			//주문상세 등록
			$result = insert_order_detail($order_detail_info);

			if( $result )
			{
				//상품상세pkg정보
				$product_detail_pkg_info_array = get_product_detail_pkg_info($product_info["product_info"]["product_detail_idx"]);	//상품상세idx;

				if ( $product_detail_pkg_info_array["product_detail_pkg_info"][0]["enter_id"] == "fly" )
				{
					$fly_code_str = search_fly_block($product_info["product_info"]["product_detail_idx"]);

					if ( $fly_code_str["fly_code"] == "S" )
					{
						$ex_block = 1;
					}
					else if ( $fly_code_str["fly_code"] == "D" )
					{
						$ex_block = 2;
					}
					else if ( $fly_code_str["fly_code"] == "O" )
					{
						$ex_block = 5;
					}
					else
					{
						$ex_block = 1;
					}
				}

				//pkg만큼 pos_detail에 주문 등록
				foreach($product_detail_pkg_info_array["product_detail_pkg_info"] as $k_pkg=>$v_pkg)
				{
					unset($pos_detail_info);

					//유효기간
					$valid_start_date = "";
					$valid_end_date = "";

					$confirm_status = 1;
					$confirm_date = date("Y-m-d H:i:s");

					//유효시작일 기간권
					if( $v_pkg["valid_start_type"] == 0 )
					{
						$valid_start_date = date("Y-m-d", strtotime(  date("Y-m-d" ) . $v_pkg["valid_start_count"]." days" ));
					}
					//유효시작일 날짜지정
					else if( $v_pkg["valid_start_type"] == 1 )
					{
						$valid_start_date = $v_pkg["valid_start_date"];
					}
					//유효시작일 예약일
					else if( $v_pkg["valid_start_type"] == 2 )
					{
						//채널옵션의 예약일 협의(옵션의 예약일의 구조를 정의해야함) 필요....
						$valid_start_date = $v_pkg["valid_start_date"];
					}

					//유효시작일 기간권
					if( $v_pkg["valid_end_type"] == 0 )
					{
						$valid_end_date = date("Y-m-d", strtotime(  date("Y-m-d" ) . $v_pkg["valid_end_count"]." days" ));
					}
					//유효시작일 날짜지정
					else if( $v_pkg["valid_end_type"] == 1 )
					{
						$valid_end_date = $v_pkg["valid_end_date"];
					}
					//유효시작일 예약일
					else if( $v_pkg["valid_end_type"] == 2 )
					{
						//채널옵션의 예약일 협의(옵션의 예약일의 구조를 정의해야함) 필요....
						$valid_end_date = $v_pkg["valid_end_date"];
					}

					//주문상세정보
					$pos_detail_info["agent_code"] = $agent_code; //주문번호
					$pos_detail_info["order_num"] = $order_num; //주문번호
					$pos_detail_info["mem_code"] = $product_info["product_info"]["mem_code"]; //시설코드
					$pos_detail_info["channel_code"] = $channel_code; //채널코드
					$pos_detail_info["barcode"] = $barcode;	//바코드
					$pos_detail_info["count"] = $stock;	//수량
					$pos_detail_info["option_mapping_idx"] = $option_mapping_idx;	//맵핑idx..

					$pos_detail_info["product_code"] = $v_pkg["product_code"];	//상품코드
					$pos_detail_info["product_detail_code"] = $v_pkg["product_detail_code"];	//기초상품코드
					$pos_detail_info["ticket_option_code"] = $v_pkg["ticket_option_code"];	//권종코드:대인/소인....
					$pos_detail_info["sale_price"] = $v_pkg["sale_price"];	//정상금액
					$pos_detail_info["dis_price"] = $v_pkg["dis_price"];	//판매금액
					$pos_detail_info["bill_price"] = $v_pkg["bill_price"];	//채널정산금액
					$pos_detail_info["remit_price"] = $v_pkg["remit_price"];	//시설정산금액

					$pos_detail_info["product_detail_idx"] = $v_pkg["product_detail_idx"];	//상품상세idx

					$pos_detail_info["product_detail_pkg_idx"] = $v_pkg["product_detail_pkg_idx"];	//상품상세pkgidx
					$pos_detail_info["pkg_type"] = $v_pkg["pkg_type"];	//패키지 타입
					$pos_detail_info["enter_id"] = $v_pkg["enter_id"];	//입장소아이디
					$pos_detail_info["option_name"] = $v_pkg["option_name"];	//옵션명

					$pos_detail_info["valid_start_date"] = $valid_start_date;	//유효기간 시작일
					$pos_detail_info["valid_end_date"] = $valid_end_date;	//유효기간 종료일
					$pos_detail_info["ex_block"] = $ex_block;

					if ( $v_pkg["enter_id"] == "fly" )
					{
						$result_pos_detail = insert_pos_detail_fly($pos_detail_info);
					}
					else
					{
						$result_pos_detail = insert_pos_detail($pos_detail_info);
					}

					//주문상세 등록성공
					if( $result_pos_detail )
					{
						$rtn_data["header"]["result_code"]			= "0000";
						$rtn_data["header"]["result_msg"]			= "주문등록 성공";

						$rtn_data["body"]["barcode"]				= $barcode;
					}
					//주문상세 등록실패
					else
					{
						$rtn_data["header"]["result_code"]			= "0004";
						$rtn_data["header"]["result_msg"]			= "주문 상세등록 실패(pos)";

						$rtn_data["body"]["barcode"]				= $barcode;

						$tran = false;

						echo_json_encode($rtn_data);
						exit;
					}

					if( !$tran )
					{
						break;
					}
				}// 주문상세pkg for end
			}
			else
			{
				$rtn_data["header"]["result_code"]			= "0005";
				$rtn_data["header"]["result_msg"]			= "주문 상세등록 실패(detail)";

				$rtn_data["body"]["barcode"]				= $barcode;

				$tran = false;

				echo_json_encode($rtn_data);
				exit;
			}
		}
	}

}

//트랜잭선 처리
if(!$tran)
{
	$result = mysql_query("ROLLBACK");
}
else
{
	$result = mysql_query("COMMIT");
}

mysql_query("SET AUTOCOMMIT=1");

if($tran)
{
	$result_data = ticket_confirm($order_num, $barcode);

	if($result_data["result_code"] == "0000")
	{
		$rtn_data["body"]["ticket_code"]		= $result_data["ticket_code"];
	}
	else
	{
		$rtn_data["header"]["result_code"]			= "0008";
		$rtn_data["header"]["result_msg"]			= "ticket_code 에러(관리자 문의)";
		
		echo_json_encode($rtn_data);
		exit;
	}
}
echo_json_encode($rtn_data);

error_log("    ");
error_log("    ");
error_log(">>>>>>>>> START >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
error_log("[qpos api response order_insert_resend]");
error_log("    ");
error_log("[json encrypt==>".json_encode($rtn_data, JSON_UNESCAPED_UNICODE)."]");
error_log(">>>>>>>>> END   >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
error_log("    ");
error_log("    ");


//발권
function ticket_confirm($order_num, $barcode)
{
	//트랜잭션 시작
	mysql_query("SET AUTOCOMMIT=0");
	mysql_query("BEGIN");

	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " ticket_code ";
	$strSql .= " ,confirm_date ";
	$strSql .= " from salti_pos_detail ";
	$strSql .= " where order_num = '".$order_num."' ";
	$strSql .= " and ticket_code is not null ";

	$ticket_code  = mysql_result(mysql_query($strSql),0,0);
	$confirm_date = mysql_result(mysql_query($strSql),0,1);

	$strSql = "";
	$strSql .= "  ";
	$strSql .= " update salti_pos_detail ";
	$strSql .= " set ";
	$strSql .= " confirm_status = 1 ";
	$strSql .= " , confirm_date = '".$confirm_date."' ";
	$strSql .= " , ticket_code = '".$ticket_code."' ";
	$strSql .= " where barcode = '".$barcode."' ";

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

	if($check_flag)
	{
		$result_data["result_code"] = "0000";
		$result_data["ticket_code"] = $ticket_code;

		mysql_query("COMMIT");
	}
	else
	{
		$result_data["result_code"] = "9999";

		mysql_query("ROLLBACK");
	}

	mysql_query("SET AUTOCOMMIT=1");

	return $result_data;

}

//상품상세pkg조회
function get_product_detail_pkg_info($product_detail_idx)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " salti_product_detail_pkg.* ";
	$strSql .= " , salti_product_detail_pkg.idx as product_detail_pkg_idx ";

	$strSql .= " from salti_product_detail ";
	$strSql .= " inner join salti_product on salti_product_detail.product_code = salti_product.product_code ";
	$strSql .= " inner join salti_product_detail_pkg on salti_product_detail.idx = salti_product_detail_pkg.product_detail_idx ";
	$strSql .= " where 1=1 ";
	$strSql .= " and salti_product_detail_pkg.product_detail_idx = '".$product_detail_idx."' ";
//	echo $strSql;
//	exit;

	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	$return["count"] = $rsCount;
	$return["product_detail_pkg_info"] = array();

	if( $rsCount > 0 )
	{
		while($rows=mysql_fetch_assoc($rsList))
		{
			$return["product_detail_pkg_info"][] = $rows;
		}
	}

	return $return;
}

//상품상세조회
function get_product_info($product_code, $option_code)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " salti_product.* ";
	$strSql .= " , '' as split ";
	$strSql .= " , salti_product_detail.* ";
	$strSql .= " , salti_product_detail.idx as product_detail_idx ";
	$strSql .= " from salti_product_detail ";
	$strSql .= " inner join salti_product on salti_product_detail.product_code = salti_product.product_code ";
	$strSql .= " where 1=1 ";
	$strSql .= " and salti_product.product_code = '".$product_code."' ";
	$strSql .= " and salti_product_detail.idx = '".$option_code."' ";
//echo $strSql;
//exit;
	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	$return["count"] = $rsCount;
	$return["product_info"] = array();

	if( $rsCount > 0 )
	{
		while($rows=mysql_fetch_assoc($rsList))
		{
			$return["product_info"] = $rows;
			break;
		}
	}

	return $return;
}

//주문상세등록
function insert_order_detail($order_detail_info)
{
	$strSql = "";
	$strSql .= " insert into salti_order_detail ( ";
	$strSql .= "  ";
	$strSql .= "   agent_code ";
	$strSql .= " , order_num ";
	$strSql .= " , mem_code ";
	$strSql .= " , channel_code ";
	$strSql .= " , product_code ";
	$strSql .= " , product_detail_code ";
	$strSql .= " , ticket_option_code ";
	$strSql .= " , option_name ";
	$strSql .= " , barcode ";
	$strSql .= " , count ";
	$strSql .= " , sale_price ";
	$strSql .= " , dis_price ";
	$strSql .= " , bill_complate_price ";
	$strSql .= " , remit_complate_price ";
	$strSql .= " , product_detail_idx ";
	$strSql .= " , option_mapping_idx ";


	$strSql .= " ) values ( ";
	$strSql .= "  ";
	$strSql .= "   '".$order_detail_info["agent_code"]."' ";
	$strSql .= " ,  '".$order_detail_info["order_num"]."' ";
	$strSql .= " ,  ".$order_detail_info["mem_code"]." ";
	$strSql .= " ,  ".$order_detail_info["channel_code"]." ";
	$strSql .= " ,  ".$order_detail_info["product_code"]." ";
	$strSql .= " ,  ".$order_detail_info["product_detail_code"]." ";
	$strSql .= " , '".$order_detail_info["ticket_option_code"]."' ";
	$strSql .= " , '".$order_detail_info["option_name"]."' ";
	$strSql .= " , '".$order_detail_info["barcode"]."' ";
	$strSql .= " ,  ".$order_detail_info["count"]." ";
	$strSql .= " ,  ".$order_detail_info["sale_price"]." ";
	$strSql .= " ,  ".$order_detail_info["dis_price"]." ";
	$strSql .= " ,  ".$order_detail_info["bill_price"]." ";
	$strSql .= " ,  ".$order_detail_info["remit_price"]." ";
	$strSql .= " ,  ".$order_detail_info["product_detail_idx"]." ";
	$strSql .= " ,  ".$order_detail_info["option_mapping_idx"]." ";

	$strSql .= " ) ";

//echo "salti_order_detail";
//echo $strSql."<br>";
//echo "<br>";
//exit;
	$result = mysql_query($strSql);

	return $result;
}

//주문상세pkg등록
function insert_pos_detail($pos_detail_info)
{
	$strSql = "";
	$strSql .= " insert into salti_pos_detail ( ";
	$strSql .= "  ";
	$strSql .= "   agent_code ";
	$strSql .= " , order_num ";
	$strSql .= " , mem_code ";
	$strSql .= " , channel_code ";
	$strSql .= " , product_code ";
	$strSql .= " , product_detail_code ";
	$strSql .= " , pos_id ";
	$strSql .= " , enter_id ";
	$strSql .= " , ticket_option_code ";
	$strSql .= " , option_name ";
	$strSql .= " , barcode ";
	$strSql .= " , count ";
	$strSql .= " , valid_start_date ";
	$strSql .= " , valid_end_date ";
	$strSql .= " , sale_price ";
	$strSql .= " , dis_price ";
	$strSql .= " , bill_price ";
	$strSql .= " , remit_price ";
	$strSql .= " , product_detail_idx ";
	$strSql .= " , option_mapping_idx ";

	$strSql .= " , product_detail_pkg_idx ";

	$strSql .= " ) values ( ";
	$strSql .= "  ";
	$strSql .= "   '".$pos_detail_info["agent_code"]."' ";
	$strSql .= " ,  '".$pos_detail_info["order_num"]."' ";
	$strSql .= " ,  ".$pos_detail_info["mem_code"]." ";
	$strSql .= " ,  ".$pos_detail_info["channel_code"]." ";
	$strSql .= " ,  ".$pos_detail_info["product_code"]." ";
	$strSql .= " ,  ".$pos_detail_info["product_detail_code"]." ";
	$strSql .= " , '".$pos_detail_info["pos_id"]."' ";
	$strSql .= " , '".$pos_detail_info["enter_id"]."' ";
	$strSql .= " , '".$pos_detail_info["ticket_option_code"]."' ";
	$strSql .= " , '".$pos_detail_info["option_name"]."' ";
	$strSql .= " , '".$pos_detail_info["barcode"]."' ";
	$strSql .= " ,  ".$pos_detail_info["count"]." ";
	$strSql .= " , '".$pos_detail_info["valid_start_date"]."' ";
	$strSql .= " , '".$pos_detail_info["valid_end_date"]."' ";
	$strSql .= " ,  ".$pos_detail_info["sale_price"]." ";
	$strSql .= " ,  ".$pos_detail_info["dis_price"]." ";
	$strSql .= " ,  ".$pos_detail_info["bill_price"]." ";
	$strSql .= " ,  ".$pos_detail_info["remit_price"]." ";
	$strSql .= " ,  ".$pos_detail_info["product_detail_idx"]." ";
	$strSql .= " ,  ".$pos_detail_info["option_mapping_idx"]." ";

	$strSql .= " ,  ".$pos_detail_info["product_detail_pkg_idx"]." ";

	$strSql .= " ) ";

//echo "salti_pos_detail";
//echo $strSql."<br>";
//echo "<br>";

	$result = mysql_query($strSql);


	return $result;
}

//주문상세pkg등록
function insert_pos_detail_fly($pos_detail_info)
{
	$strSql = "";
	$strSql .= " insert into salti_pos_detail ( ";
	$strSql .= "  ";
	$strSql .= "   agent_code ";
	$strSql .= " , order_num ";
	$strSql .= " , mem_code ";
	$strSql .= " , channel_code ";
	$strSql .= " , product_code ";
	$strSql .= " , product_detail_code ";
	$strSql .= " , pos_id ";
	$strSql .= " , enter_id ";
	$strSql .= " , ticket_option_code ";
	$strSql .= " , option_name ";
	$strSql .= " , barcode ";
	$strSql .= " , count ";
	$strSql .= " , valid_start_date ";
	$strSql .= " , valid_end_date ";
	$strSql .= " , sale_price ";
	$strSql .= " , dis_price ";
	$strSql .= " , bill_price ";
	$strSql .= " , remit_price ";
	$strSql .= " , product_detail_idx ";
	$strSql .= " , option_mapping_idx ";

	$strSql .= " , product_detail_pkg_idx ";
	$strSql .= " , ex_block ";

	$strSql .= " ) values ( ";
	$strSql .= "  ";
	$strSql .= "   '".$pos_detail_info["agent_code"]."' ";
	$strSql .= " ,  '".$pos_detail_info["order_num"]."' ";
	$strSql .= " ,  ".$pos_detail_info["mem_code"]." ";
	$strSql .= " ,  ".$pos_detail_info["channel_code"]." ";
	$strSql .= " ,  ".$pos_detail_info["product_code"]." ";
	$strSql .= " ,  ".$pos_detail_info["product_detail_code"]." ";
	$strSql .= " , '".$pos_detail_info["pos_id"]."' ";
	$strSql .= " , '".$pos_detail_info["enter_id"]."' ";
	$strSql .= " , '".$pos_detail_info["ticket_option_code"]."' ";
	$strSql .= " , '".$pos_detail_info["option_name"]."' ";
	$strSql .= " , '".$pos_detail_info["barcode"]."' ";
	$strSql .= " ,  ".$pos_detail_info["count"]." ";
	$strSql .= " , '".$pos_detail_info["valid_start_date"]."' ";
	$strSql .= " , '".$pos_detail_info["valid_end_date"]."' ";
	$strSql .= " ,  ".$pos_detail_info["sale_price"]." ";
	$strSql .= " ,  ".$pos_detail_info["dis_price"]." ";
	$strSql .= " ,  ".$pos_detail_info["bill_price"]." ";
	$strSql .= " ,  ".$pos_detail_info["remit_price"]." ";
	$strSql .= " ,  ".$pos_detail_info["product_detail_idx"]." ";
	$strSql .= " ,  ".$pos_detail_info["option_mapping_idx"]." ";

	$strSql .= " ,  ".$pos_detail_info["product_detail_pkg_idx"]." ";

	$strSql .= " , '".$pos_detail_info["ex_block"]."' ";

	$strSql .= " ) ";

//echo "salti_pos_detail";
//echo $strSql."<br>";
//echo "<br>";

	$result = mysql_query($strSql);


	return $result;
}

//플라이 블럭 서치
function search_fly_block($product_detail_idx)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " fly_code ";
	$strSql .= " from salti_option_mapping ";
	$strSql .= " where option_code = '".$product_detail_idx."' ";


	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	if ( $rsCount > 0 )
	{
		while($rows=mysql_fetch_assoc($rsList))
		{
			$return["fly_code"] = $rows["fly_code"];
		}
	}

	return $return;
}

?>