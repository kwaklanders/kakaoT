<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>
<?

$nowdate = date("Y-m-d H:i:s");

$today = date("Y-m-d H:i:s", strtotime("-10 minute", strtotime($nowdate)));
$before_date = date("Y-m-d H:i:s", strtotime("-1 day", strtotime($nowdate)));

//네이버 1시간 미결제 취소 삭제대상 조회
$strSql = "";
$strSql .= " select ";
$strSql .= " order_num ";
$strSql .= " from salti_order ";
$strSql .= " where status = 0 ";
$strSql .= " and channel_code = '7021' ";
$strSql .= " and DATE_FORMAT(buy_date, '%Y-%m-%d %H:%i:%s') <= '".$today."' ";
$strSql .= " and DATE_FORMAT(buy_date, '%Y-%m-%d %H:%i:%s') >= '".$before_date."' ";
$strSql .= " order by idx asc limit 10 ";

echo $strSql."<br>";
//exit;

$order_search_value = mysql_query($strSql);
$order_search_count = mysql_num_rows($order_search_value);

if($order_search_count > 0)
{
	//트랜잭션 시작
	mysql_query("SET AUTOCOMMIT=0");
	mysql_query("BEGIN");
	
	while($order_rows = mysql_fetch_assoc($order_search_value))
	{
		//salti_order 삭제
		$strSql = "";
		$strSql .= " delete from salti_order where order_num =  '".$order_rows["order_num"]."' ";

		$order_del_result	= mysql_query($strSql);
		$order_del_count	= mysql_affected_rows();

		
//		$order_del_result	= true;
//		$order_del_count	= 1;
				
		if( $order_del_result && ($order_del_count == 0 || $order_del_count == 1) )
		{
			$check_flag = true;
		}
		else 
		{
			$check_flag = false;
			break;
		}
		
//		echo $strSql." | order_del_count:".$order_del_count." | ".$order_rows["order_num"]."<br>";
				
		//order_num 삭제 성공
		if($check_flag)
		{
			//salti_order_detail 삭제조회
			$strSql = "";
			$strSql .= " select idx from salti_order_detail where order_num = '".$order_rows["order_num"]."' ";
			
			$detail_search_value = mysql_query($strSql);
			$detail_search_count = mysql_num_rows($detail_search_value);
			
			if($detail_search_count > 0)
			{
				while($detail_rows = mysql_fetch_assoc($detail_search_value))
				{
					//salti_order_detail 삭제
					$strSql = "";
					$strSql .= " delete from salti_order_detail where idx =  '".$detail_rows["idx"]."' ";

					$detail_del_result = mysql_query($strSql);
					$detail_del_count  = mysql_affected_rows();

//					$detail_del_result = true;
//					$detail_del_count  = 1;
					
					if( $detail_del_result && ($detail_del_count == 0 || $detail_del_count == 1) )
					{
						$check_flag = true;
					}
					else 
					{
						$check_flag = false;
						break;
					}
					
//					echo $strSql." | detail_del_count:".$detail_del_count." | ".$order_rows["order_num"]."<br>";
				}
			}
			
			if($check_flag)
			{
				//salti_pos_detail 삭제조회
				$strSql = "";
				$strSql .= " select ";
				$strSql .= " salti_pos_detail.mem_code ";
				$strSql .= " , salti_pos_detail.idx ";
				$strSql .= " , salti_pos_detail.ex_date ";
				$strSql .= " , salti_pos_detail.ex_hours ";
				$strSql .= " , salti_pos_detail.ex_minute ";
				$strSql .= " , salti_member.mem_type ";
				$strSql .= " , salti_pos_detail.count ";
				$strSql .= " from salti_pos_detail ";
				$strSql .= " inner join salti_member on salti_pos_detail.mem_code = salti_member.mem_code ";
				$strSql .= " where salti_pos_detail.order_num = '".$order_rows["order_num"]."' ";
								
				$pos_search_value = mysql_query($strSql);
				$pos_search_count = mysql_num_rows($pos_search_value);
				
				if($pos_search_count > 0)
				{
					while($pos_rows = mysql_fetch_assoc($pos_search_value))
					{
						//salti_pos_detail 삭제
						$strSql = "";
						$strSql .= " delete from salti_pos_detail where idx =  '".$pos_rows["idx"]."' ";
						
						$pos_del_result = mysql_query($strSql);
						$pos_del_count  = mysql_affected_rows();
						
						if( $pos_del_result && ($pos_del_count == 0 || $pos_del_count == 1) )
						{
							//회차형 블럭 복구
							if ( $pos_rows["mem_type"] == "9" )
							{
								$strSql = "";
								$strSql .= " select ";
								$strSql .= " block ";
								$strSql .= " from salti_product_block_ticket ";
								$strSql .= " where mem_code = '".$pos_rows["mem_code"]."' ";
								$strSql .= " and start_date = '".$pos_rows["ex_date"]."' ";
								$strSql .= " and hour = '".$pos_rows["ex_hours"]."' ";
								$strSql .= " and minute = '".$pos_rows["ex_minute"]."' ";

								$now_block = mysql_result(mysql_query($strSql),0,0);

								$result_block = (int)$now_block + (int)$pos_rows["count"];

								$strSql = "";
								$strSql .= " update ";
								$strSql .= " salti_product_block_ticket ";
								$strSql .= " set block = '".$result_block."' ";
								$strSql .= " where mem_code = '".$pos_rows["mem_code"]."' ";
								$strSql .= " and start_date = '".$pos_rows["ex_date"]."' ";
								$strSql .= " and hour = '".$pos_rows["ex_hours"]."' ";
								$strSql .= " and minute = '".$pos_rows["ex_minute"]."' ";

								$block_update = mysql_query($strSql);

								if ( $block_update )
								{
									$check_flag = true;
								}
								else
								{
									$check_flag = false;
								}

							}
							//날짜지정형 블럭 복구
							else if ( $pos_rows["mem_type"] == "10" )
							{
								$check_flag = true;
							}
							//숙박 블럭 복구
							else if ( $pos_rows["mem_type"] == "0" )
							{
								$check_flag = true;
							}
							else
							{
								$check_flag = true;
							}
						}
						else 
						{
							$check_flag = false;
							break;
						}
						
						echo $strSql." | pos_del_count:".$pos_del_count." | ".$order_rows["order_num"]."<br>";
					}
				}
			}
		}
	}//while 종료


	if($check_flag)
	{
		mysql_query("COMMIT");

		$return_data["result_code"] = "0000";
		$return_data["result_msg"]	= "성공";
	}
	else
	{
		mysql_query("ROLLBACK");

		$return_data["result_code"] = "9999";
		$return_data["result_msg"]	= "실패";
	}

	mysql_query("SET AUTOCOMMIT=1");
	
	echo_json_encode($return_data);
}

?>