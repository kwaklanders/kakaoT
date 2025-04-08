<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>
<?

$order_info = $_REQUEST["order_info"];

//파싱
$order_info_json = json_decode($order_info, true);

$agent_code		 = $order_info_json["agent_code"];
$order_num		 = $order_info_json["order_num"];
$buy_name		 = $order_info_json["buy_name"];
$buy_hp			 = $order_info_json["buy_hp"];
$buy_count		 = $order_info_json["buy_count"];
$channel_code	 = $order_info_json["channel_code"];
$buy_date		 = $order_info_json["buy_date"];

error_log("    ");
error_log("    ");
error_log(">>>>>>>>> START >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
error_log("[qpos api request]");
error_log("    ");
error_log("order_num:".$order_num."");
error_log("buy_date:".$buy_date."");
error_log("order_info:".json_encode($order_info_json, JSON_UNESCAPED_UNICODE)."");
error_log(">>>>>>>>> END   >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
error_log("    ");
error_log("    ");


//등록된 주문
$success_order_list = array();

//등록된 바코드
$success_barcode_list = array();

//상세수량
$detail_count = 0;

//트랜잭션 시작
$tran = true;
mysql_query("SET AUTOCOMMIT=0");
mysql_query("BEGIN");

//주문 each
foreach($order_info_json["order_list"] as $k=>$v)
{
	//전화번호 정규화
	$buy_hp = str_replace("-", "", str_replace("+82-", "", $buy_hp));

	$buy_hp_case = substr($buy_hp,0,2);

	if($buy_hp_case != "01")
	{
		$buy_hp = "0".$buy_hp;
	}

	insert_agree_y($buy_hp);

	//정규식패턴....{01|0,1,6,7,8,9}{[0-9]{3}|[0-9]{4}}{[0-9]{4}}preg_match()

	$option_count = (int)$v["stock"];

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
	$strSql .= " and salti_option_mapping.product_code = ".$v["product_code"]." ";
	$strSql .= " and salti_option_mapping.option_code = ".$v["product_detail_code"]." ";
	$strSql .= " and salti_option_mapping.channel_code = ".$channel_code." ";

	$result_salti_product_mapping = mysql_query($strSql);
	$rtncount_salti_product_mapping = mysql_num_rows($result_salti_product_mapping);

	//미조회 딜코드+옵션+채널
	if( $rtncount_salti_product_mapping == 0 )
	{
		$tran = false;

		$rtn_data["header"]["result_code"]			= "0001";
		$rtn_data["header"]["result_msg"]			= "상품정보 미조회";

		$rtn_data["body"]["product_code"]			= $v["product_code"];
		$rtn_data["body"]["product_detail_code"]	= $v["product_detail_code"];
		$rtn_data["body"]["channel_code"]			= $channel_code;

		echo_json_encode($rtn_data);
		exit;
	}
	//중복
	else if( $rtncount_salti_product_mapping > 1 )
	{
		$tran = false;

		$rtn_data["header"]["result_code"]			= "0002";
		$rtn_data["header"]["result_msg"]			= "옵션정보 중복(관리자 문의)";

		$rtn_data["body"]["product_code"]			= $v["product_code"];
		$rtn_data["body"]["product_detail_code"]	= $v["product_detail_code"];
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
			$mem_code			= $rows_salti_product_mapping["mem_code"];
			break;
		}

		//상품상세정보
		$product_info = get_product_info($v["product_code"], $v["product_detail_code"]);

		//주문정보
		unset($order_info);
		$order_info["agent_code"] = $agent_code;
		$order_info["order_num"] = $order_num;
		$order_info["mem_code"] = $product_info["product_info"]["mem_code"];
		$order_info["channel_code"] = $channel_code;
		$order_info["buy_name"] = $buy_name;
		$order_info["buy_hp"] = aes256encode(str_replace(".", "", str_replace(" ", "", str_replace("-", "", $buy_hp))));
		$order_info["buy_hp_prefix"] = right($buy_hp, 4);
		$order_info["buy_count"] = $buy_count;
		$order_info["status"] = 11;
		$order_info["buy_date"] = $buy_date;

		//주문상세정보
		unset($order_detail_info);
		$order_detail_info["agent_code"] = $agent_code;
		$order_detail_info["order_num"] = $order_num; //주문번호
		$order_detail_info["mem_code"] = $product_info["product_info"]["mem_code"];	//시설코드
		$order_detail_info["channel_code"] = $channel_code;	//채널코드
		$order_detail_info["product_code"] = $product_info["product_info"]["product_code"];	//상품코드
		$order_detail_info["product_detail_code"] = $product_info["product_info"]["product_detail_code"];	//기초상품코드
		$order_detail_info["ticket_option_code"] = $product_info["product_info"]["ticket_option_code"];	//권종코드:대인/소인....
		$order_detail_info["option_name"] = $product_info["product_info"]["option_name"];	//옵션명
		$order_detail_info["barcode"] = $v["barcode"];	//바코드
		$order_detail_info["count"] = $v["stock"];	//수량

		$order_detail_info["sale_price"] = $product_info["product_info"]["sale_price"];	//정상금액
		$order_detail_info["dis_price"] = $product_info["product_info"]["dis_price"];	//판매금액
		$order_detail_info["bill_price"] = $product_info["product_info"]["bill_price"];	//채널정산금액
		$order_detail_info["remit_price"] = $product_info["product_info"]["remit_price"];	//시설정산금액

		$order_detail_info["product_detail_idx"] = $product_info["product_info"]["product_detail_idx"];	//상품상세idx
		$order_detail_info["option_mapping_idx"] = $option_mapping_idx;	//맵핑idx..

		//주문등록(중복은 ignore..)
		$result = insert_order($order_info);

		if( $result )
		{
			//주문상세 중복확인
			$order_detail_exists = exists_order_detail_cms($v["barcode"]);

			if( $order_detail_exists != 0 )
			{
				$rtn_data["header"]["result_code"]			= "0003";
				$rtn_data["header"]["result_msg"]			= "주문 상세번호 중복";

				$rtn_data["body"]["barcode"]				= $v["barcode"];

				$tran = false;

				echo_json_encode($rtn_data);
				exit;
			}
			else
			{
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
						$pos_detail_info["agent_code"] = $order_info["agent_code"]; //주문번호
						$pos_detail_info["order_num"] = $order_info["order_num"]; //주문번호
						$pos_detail_info["mem_code"] = $order_info["mem_code"]; //시설코드
						$pos_detail_info["channel_code"] = $order_info["channel_code"]; //채널코드
						$pos_detail_info["barcode"] = $v["barcode"];	//바코드
						$pos_detail_info["count"] = $v["stock"];	//수량
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
							$detail_count++;

							$success_order_list[] = $order_num;
							$success_barcode_list[] = $v["barcode"];

							$rtn_data["header"]["result_code"]			= "0000";
							$rtn_data["header"]["result_msg"]			= "주문등록 성공";

							$rtn_data["body"]["order_num"]				= $order_num;
						}
						//주문상세 등록실패
						else
						{
							$rtn_data["header"]["result_code"]			= "0004";
							$rtn_data["header"]["result_msg"]			= "주문 상세등록 실패(pos)";

							$rtn_data["body"]["order_num"]				= $order_num;

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

					$rtn_data["body"]["order_num"]				= $order_num;

					echo_json_encode($rtn_data);
					exit;
				}
			}//주문상세 중목 if end...

		}// 주문등록 result if end...
		else
		{
			$rtn_data["header"]["result_code"]			= "0006";
			$rtn_data["header"]["result_msg"]			= "주문등록 실패(order)";

			$rtn_data["body"]["order_num"]				= $order_num;

			$tran = false;

			echo_json_encode($rtn_data);
			exit;
		}

	} //맵핑 조회 if end..

}// foreach end

//트랜잭선 처리
if(!$tran)
{
	$check_all = false;

	$result = mysql_query("ROLLBACK");
}
else
{
	if($buy_count == $detail_count)
	{
		$check_all = true;

		$result = mysql_query("COMMIT");
	}
	else
	{
		$check_all = false;

		$result = mysql_query("ROLLBACK");

		$rtn_data["header"]["result_code"]			= "0007";
		$rtn_data["header"]["result_msg"]			= "주문등록 실패(buy_count 와 stock 수량 다름)";

		$rtn_data["body"]["buy_count"]				= $buy_count;
	}
}

mysql_query("SET AUTOCOMMIT=1");

if( count($success_order_list) > 0 )
{
	$result_data = ticket_confirm($order_num);

	if($result_data["result_code"] == "0000")
	{
		$rtn_data["body"]["ticket_code"]		= $result_data["ticket_code"];
	}
	else
	{
		$rtn_data["header"]["result_code"]			= "0008";
		$rtn_data["header"]["result_msg"]			= "ticket_code 에러(관리자 문의)";
	}
}

echo_json_encode($rtn_data);

error_log("    ");
error_log("    ");
error_log(">>>>>>>>> START >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
error_log("[qpos api response]");
error_log("    ");
error_log("[json encrypt==>".json_encode($rtn_data, JSON_UNESCAPED_UNICODE)."]");
error_log("[buy_count:".$buy_count."]/[detail_count:".$detail_count."]");
error_log(">>>>>>>>> END   >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
error_log("    ");
error_log("    ");


//발권
function ticket_confirm($order_num)
{
	$confirm_date = date("Y-m-d H:i:s");
	$ticket_code = set_ticket_code();

	//트랜잭션 시작
	mysql_query("SET AUTOCOMMIT=0");
	mysql_query("BEGIN");

	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " barcode ";
	$strSql .= " from salti_pos_detail ";
	$strSql .= " where order_num = '".$order_num."' ";

	$rtnvalue = mysql_query($strSql);

	while($rows = mysql_fetch_assoc($rtnvalue))
	{
		$strSql = "";
		$strSql .= "  ";
		$strSql .= " update salti_pos_detail ";
		$strSql .= " set ";
		$strSql .= " confirm_status = 1 ";
		$strSql .= " , confirm_date = '".$confirm_date."' ";
		$strSql .= " , ticket_code = '".$ticket_code."' ";
		$strSql .= " where barcode = '".$rows["barcode"]."' ";

//		echo $strSql."<br>";
//		exit;

		$result = mysql_query($strSql);
		$update_count = mysql_affected_rows();
	}

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

//약관동의
function insert_agree_y($buy_hp)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " insert into salti_agree_hp ";
	$strSql .= " ( ";
	$strSql .= " phoneNum ";
	$strSql .= " ,agree_date ";
	$strSql .= " ,ch_date ";
	$strSql .= " ,regdate ";
	$strSql .= " ) ";
	$strSql .= " values ";
	$strSql .= " ( ";
	$strSql .= " '".aes256encode($buy_hp)."' ";
	$strSql .= " ,now() ";
	$strSql .= " ,now() ";
	$strSql .= " ,now() ";
	$strSql .= " ) ";

	mysql_query($strSql);
}

//주문등록
function insert_order($order_info)
{
	//주문등록
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " insert ignore into salti_order ";
	$strSql .= " ( ";
	$strSql .= "   agent_code ";
	$strSql .= " , order_num ";
	$strSql .= " , pos_type ";
	$strSql .= " , mem_code ";
	$strSql .= " , channel_code ";
	$strSql .= " , buy_name ";
	$strSql .= " , buy_hp ";
	$strSql .= " , buy_hp_prefix ";
	$strSql .= " , buy_count ";
	$strSql .= " , status ";
	$strSql .= " , buy_date ";
	$strSql .= " , sale_price ";
	$strSql .= " , actual_sale_price ";
	$strSql .= " ) values ( ";
	$strSql .= "   '".$order_info["agent_code"]."' ";
	$strSql .= " , '".$order_info["order_num"]."' ";
	$strSql .= " , 2 ";	//0:무인발권기/1:포스/2:채널
	$strSql .= " ,  ".$order_info["mem_code"]." ";
	$strSql .= " ,  ".$order_info["channel_code"]." ";
	$strSql .= " , '".$order_info["buy_name"]."' ";
	$strSql .= " , '".$order_info["buy_hp"]."' ";
	$strSql .= " , '".$order_info["buy_hp_prefix"]."' ";
	$strSql .= " ,  ".$order_info["buy_count"]." ";
	$strSql .= " ,  ".$order_info["status"]." ";
	$strSql .= " , '".$order_info["buy_date"]."' ";
	$strSql .= " , '".$order_info["sale_price"]."' ";
	$strSql .= " , '".$order_info["actual_sale_price"]."' ";
	$strSql .= " ) ";
//echo "salti_order";
//echo $strSql."<br>";
//echo "<br>";
	//	exit;
	$result = mysql_query($strSql);

	return $result;
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
//exit;
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




//알림톡 발송
function order_send($order_num)
{

	//구매완료 알림톡발송
	//주문정보
	$order_info = get_order_info_cms($order_num);
//echo_json_encode($order_info);
//echo "<br><br>";

	//주문상세정보
	$order_detail_info = get_order_detail_info_cms($order_num);
//echo_json_encode($order_detail_info);
//echo "<br><br>";

	foreach($order_detail_info["list"] as $k=>$order_detail)
	{
		$product_code = $order_detail["product_code"];
	}
	//발송문자정보
	$sms_info = getProductSmsInfoNotiCms($product_code);
//echo_json_encode($sms_info);
//echo "<br><br>";
//exit;

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

	$msg_arr["order_num"]		= $order_info["list"]["order_num"];
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
		$result = send_kakao_standard_cms($send_type, $sms_num, $sender_key, $callback, $order_info["list"]["buy_hp"], $template_code, $button_yn, $button_name, $button_url, $content, $order_info["list"]["order_num"], $send_num);
//		echo "알림톡발송응답 : ".$result . "<br>";
	}
}

?>