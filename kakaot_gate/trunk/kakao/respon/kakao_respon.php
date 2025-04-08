<?require_once($_SERVER["DOCUMENT_ROOT"]."/kakao/skin/common.php");?>
<?

$cmd = $_REQUEST["cmd"];
$deal_code_array = $_REQUEST["deal_code"];
$start_date = $_REQUEST["start_date"];
$end_date = $_REQUEST["end_date"];


echo "현재 수집 CMS : ".$cmd."<br>";


if ( $cmd == "websen_tour" )
{

	kakao_collect_websen($deal_code_array, $start_date, $end_date);

}

if ( $cmd == "qpos_system" )
{
	kakao_collect_qpos($deal_code_array, $start_date, $end_date);

}
if ( $cmd == "kf" )
{
	kakao_collect_kf($deal_code_array, $start_date, $end_date);
}
if ( $cmd == "songdo" )
{
	kakao_collect_songdo($deal_code_array, $start_date, $end_date);
}
if ( $cmd == "topsten" )
{
	kakao_collect_topsten($deal_code_array, $start_date, $end_date);
}
if ( $cmd == "hoban" )
{
	kakao_collect_hoban($deal_code_array, $start_date, $end_date);
}
if ( $cmd == "gram" )
{
	kakao_collect_gram($deal_code_array, $start_date, $end_date);
}


function kakao_collect_websen($deal_code_array, $start_date, $end_date)
{
	$url = KAKAO_URL."/v1/shopping/orders";


	//모든 카카오 요청에서 헤더는 고정..
	$headers = array(
		"Authorization: ".WEBSEN_APP_KEY,
		"Target-Authorization: ".WEBSEN_API_KEY,
		"channel-ids: 101",
		"Content-type: application/json;charset=UTF-8"
	);

	$post_data = array();

	$post_data["page"] = "1";
	$post_data["order_modified_at_start"] = $start_date;//$start_date;
	$post_data["order_modified_at_end"] = $end_date;//$end_date;

	//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
	$post_data["order_status"] = "TicketRequest";

	$rtn_data = curl_get($url, $headers, $post_data);

	$httpcode = $rtn_data["httpcode"];
	$json_data_string = $rtn_data["body"];
	$json_data = json_decode($json_data_string, true);

	$orderIds = array();


	if ( count($json_data["content"]) > "0" )
	{
		$order_info = array();
		$order_info_list = array();


		foreach($json_data["content"] as $key=>$values)
		{
			$order_num = $values["orderId"];
			

			$objRequests = new KakaoApi;

			$return_data = $objRequests->order_search_detail($order_num);

			if ( $return_data["result_code"] == "0000" )
			{
				foreach($deal_code_array as $k=>$v)
				{
					if ( $v != $return_data["list"]["orderProduct"]["id"] )
					{
						continue;
					}
					else
					{
						if ( strpos($orderIds["orderIds"], $return_data["list"]["id"]) !== false)
						{
							continue;
						}
						else
						{
							$orderIds["orderIds"][] = $return_data["list"]["id"];
						}
					}
				}
			}
		}

		if ( count($orderIds) > 0 )
		{
			$return_order_num = $objRequests->order_check($orderIds);

			//success는 bool이라 0 또는 1로 체크
			if ( $return_order_num["list"]["success"] == 1 )
			{
				foreach($orderIds["orderIds"] as $v_id)
				{
					$url = KAKAO_URL."/v1/shopping/order?order_id=".$v_id;

					//모든 카카오 요청에서 헤더는 고정..
					$headers = array(
						"Authorization: ".WEBSEN_APP_KEY,
						"Target-Authorization: ".WEBSEN_API_KEY,
						"channel-ids: 101",
						"Content-type: application/json;charset=UTF-8"
					);

					$post_data = array();

					$post_data["page"] = "1";
//					$post_data["order_modified_at_start"] = $start_date;//$start_date;
//					$post_data["order_modified_at_end"] = $end_date;//$end_date;
//
//					//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
//					$post_data["order_status"] = "TicketSendWaiting";

					$rtn_data = curl_get($url, $headers, $post_data);

					$httpcode = $rtn_data["httpcode"];
					$json_data_string = $rtn_data["body"];
					$json_data = json_decode($json_data_string, true);

					if ( $httpcode == "200" )
					{
						if ( $json_data["orderBase"]["status"] == "TicketSendWaiting" )
						{
							unset($order_info_list);

							foreach($deal_code_array as $k=>$v)
							{
								if ( $v != $json_data["orderProduct"]["id"])
								{
									continue;
								}
								else
								{

									//함수마다 테이블명 바꿔주기
									$order_info_list["table_name"]			= "salti_websen_tour";
									$order_info_list["user_id"]				= "websen_tour";
									$order_info_list["deal_code"]			= $json_data["orderProduct"]["id"];
									$order_info_list["product_name"]		= $json_data["orderProduct"]["name"];
									$order_info_list["product_option"]		= $json_data["orderProduct"]["optionContent"];
									$order_info_list["barcode"]				= $json_data["id"];
									$order_info_list["buy_name"]			= $json_data["orderTicketReceiver"]["receiverName"];
									$order_info_list["buy_hp"]				= $json_data["orderTicketReceiver"]["receiverPhoneNumber"];
				
									$buy_date = $json_data["orderTicket"]["ticketAcceptedAt"];
									$buy_date_format = date("Y-m-d H:i:s", strtotime($buy_date));
				
									$order_info_list["buy_date"]			= $buy_date_format;
									$order_info_list["stock"]				= $json_data["orderProduct"]["quantity"];
									$order_info_list["price"]				= $json_data["orderProduct"]["settlementBasicPrice"];
				
				
									echo "수집 진행 예정 티켓 ===============================<br>";
									echo "구매자명 : [".$order_info_list["buy_name"]."] / 바코드 : [".$order_info_list["barcode"]."] / 연락처 : [".$order_info_list["buy_hp"]."]<br>";

									$order_info[] = $order_info_list;

								}
							}

						}
						else
						{
							continue;
						}
					}
					else
					{
						continue;
					}
				}

				
				if ( count($order_info) > 0 )
				{
					$order_info_string = json_encode($order_info, JSON_UNESCAPED_UNICODE);
					$objRequests = new KakaoApi;

					$return_data = $objRequests->order_insert_collect($order_info_string);

					if ( $return_data["result_code"] == "0000" )
					{
						$json_data = json_decode($return_data["body"], true);

						$success_info = $json_data["success_info"];
						$error_info = $json_data["error_info"];

						$orderIds = array();
						foreach( $success_info as $k_success_info=>$v_success_info)
						{
							$orderIds["orderIds"][] = $v_success_info;
						}


						//성공주문정보
						if( count($success_info) > 0 )
						{

							$return_order_num = $objRequests->order_send($orderIds);


							//success는 bool이라 0 또는 1로 체크
							if ( $return_order_num["list"]["success"] == 1 )
							{
								echo "주문 등록 및 확인 처리 완료...";
							}
							else if ( $return_order_num["list"]["success"] == 0)
							{

								foreach( $return_order_num["list"]["failedOrderIds"] as $k_failed=>$v_failed )
								{

									//잔디 에러 보내기 추가하기..
									echo "발송 처리 실패 주문번호 : [".$v_failed."]<br>";

								}
							}

						}
					}
					else
					{
						echo "insert 실패";
					}
						
					
				}
				else
				{
					echo "order_info 데이터 없음";
				}


			}
			else
			{
				echo "order_check 실패";
			}

		}
	}
}

//웹센 종료!!!

//큐포스 실행!!
function kakao_collect_qpos($deal_code_array, $start_date, $end_date)
{

	$url = KAKAO_URL."/v1/shopping/orders";


	//모든 카카오 요청에서 헤더는 고정..
	$headers = array(
		"Authorization: ".WEBSEN_APP_KEY,
		"Target-Authorization: ".WEBSEN_API_KEY,
		"channel-ids: 101",
		"Content-type: application/json;charset=UTF-8"
	);

	$post_data = array();

	$post_data["page"] = "1";
	$post_data["order_modified_at_start"] = $start_date;//$start_date;
	$post_data["order_modified_at_end"] = $end_date;//$end_date;

	//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
	$post_data["order_status"] = "TicketRequest";

	$rtn_data = curl_get($url, $headers, $post_data);

	$httpcode = $rtn_data["httpcode"];
	$json_data_string = $rtn_data["body"];
	$json_data = json_decode($json_data_string, true);


	$orderIds = array();


	if ( count($json_data["content"]) > "0" )
	{
		$order_info = array();
		$order_info_list = array();


		foreach($json_data["content"] as $key=>$values)
		{
			$order_num = $values["orderId"];
			

			$objRequests = new KakaoApi;

			$return_data = $objRequests->order_search_detail($order_num);

			if ( $return_data["result_code"] == "0000" )
			{
				if ($return_data["list"]["orderBase"]["status"] == "TicketRequest")
				{
					foreach($deal_code_array as $k=>$v)
					{
						if ( $v != $return_data["list"]["orderProduct"]["id"] )
						{
							continue;
						}
						else
						{
							if ( strpos($orderIds["orderIds"], $return_data["list"]["id"]) !== false)
							{
								continue;
							}
							else
							{
								$orderIds["orderIds"][] = $return_data["list"]["id"];
							}
						}
					}

				}
			}
		}

		if ( count($orderIds) > 0 )
		{
			$return_order_num = $objRequests->order_check($orderIds);

			//success는 bool이라 0 또는 1로 체크
			if ( $return_order_num["list"]["success"] == 1 )
			{
				foreach($orderIds["orderIds"] as $v_id)
				{
					$url = KAKAO_URL."/v1/shopping/order?order_id=".$v_id;

					//모든 카카오 요청에서 헤더는 고정..
					$headers = array(
						"Authorization: ".WEBSEN_APP_KEY,
						"Target-Authorization: ".WEBSEN_API_KEY,
						"channel-ids: 101",
						"Content-type: application/json;charset=UTF-8"
					);

					$post_data = array();

					$post_data["page"] = "1";
//					$post_data["order_modified_at_start"] = $start_date;//$start_date;
//					$post_data["order_modified_at_end"] = $end_date;//$end_date;
//
//					//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
//					$post_data["order_status"] = "TicketSendWaiting";

					$rtn_data = curl_get($url, $headers, $post_data);

					$httpcode = $rtn_data["httpcode"];
					$json_data_string = $rtn_data["body"];
					$json_data = json_decode($json_data_string, true);

					if ( $httpcode == "200" )
					{
						if ( $json_data["orderBase"]["status"] == "TicketSendWaiting" )
						{
							unset($order_info_list);

							//함수마다 테이블명 바꿔주기
							$order_info_list["table_name"]			= "salti_qpos_system";
							$order_info_list["user_id"]				= "qpos_system";
							$order_info_list["deal_code"]			= $json_data["orderProduct"]["id"];
							$order_info_list["product_name"]		= $json_data["orderProduct"]["name"];
							$order_info_list["product_option"]		= $json_data["orderProduct"]["optionContent"];
							$order_info_list["barcode"]				= $json_data["id"];
							$order_info_list["buy_name"]			= $json_data["orderTicketReceiver"]["receiverName"];
							$order_info_list["buy_hp"]				= $json_data["orderTicketReceiver"]["receiverPhoneNumber"];
		
							$buy_date = $json_data["orderTicket"]["ticketAcceptedAt"];
							$buy_date_format = date("Y-m-d H:i:s", strtotime($buy_date));
		
							$order_info_list["buy_date"]			= $buy_date_format;
							$order_info_list["stock"]				= $json_data["orderProduct"]["quantity"];
							$order_info_list["price"]				= $json_data["orderProduct"]["settlementBasicPrice"];
		
		
							echo "수집 진행 예정 티켓 ===============================<br>";
							echo "구매자명 : [".$order_info_list["buy_name"]."] / 바코드 : [".$order_info_list["barcode"]."] / 연락처 : [".$order_info_list["buy_hp"]."]<br>";

							$order_info[] = $order_info_list;
						}
						else
						{
							continue;
						}
					}
					else
					{
						continue;
					}
				}
				
				if ( count($order_info) > 0 )
				{

					$order_info_string = json_encode($order_info, JSON_UNESCAPED_UNICODE);
					$objRequests = new KakaoApi;

					$return_data = $objRequests->order_insert_collect($order_info_string);

					if ( $return_data["result_code"] == "0000" )
					{
						$json_data = json_decode($return_data["body"], true);

						$success_info = $json_data["success_info"];
						$error_info = $json_data["error_info"];

						$orderIds = array();
						foreach( $success_info as $k_success_info=>$v_success_info)
						{
							$orderIds["orderIds"][] = $v_success_info;
						}


						//성공주문정보
						if( count($success_info) > 0 )
						{

							$return_order_num = $objRequests->order_send($orderIds);


							//success는 bool이라 0 또는 1로 체크
							if ( $return_order_num["list"]["success"] == 1 )
							{
								echo "주문 등록 및 확인 처리 완료...";
							}
							else if ( $return_order_num["list"]["success"] == 0)
							{

								foreach( $return_order_num["list"]["failedOrderIds"] as $k_failed=>$v_failed )
								{

									//잔디 에러 보내기 추가하기..
									echo "발송 처리 실패 주문번호 : [".$v_failed."]<br>";

								}
							}

						}
					}
					else
					{
						echo "insert 실패";
					}
						
					
				}
				else
				{
					echo "order_info 데이터 없음";
				}


			}
			else
			{
				echo "order_check 실패";
			}

		}
	}
}

//큐포스 종료!!

//조원관광진흥 스토어 시행!!


function kakao_collect_kf($deal_code_array, $start_date, $end_date)
{
	$url = KAKAO_URL."/v1/shopping/orders";


	//모든 카카오 요청에서 헤더는 고정..
	$headers = array(
		"Authorization: ".WEBSEN_APP_KEY,
		"Target-Authorization: ".KF_API_KEY,
		"channel-ids: 101",
		"Content-type: application/json;charset=UTF-8"
	);

	$post_data = array();

	$post_data["page"] = "1";
	$post_data["order_modified_at_start"] = $start_date;//$start_date;
	$post_data["order_modified_at_end"] = $end_date;//$end_date;

	//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
	$post_data["order_status"] = "TicketRequest";

	$rtn_data = curl_get($url, $headers, $post_data);

	$httpcode = $rtn_data["httpcode"];
	$json_data_string = $rtn_data["body"];
	$json_data = json_decode($json_data_string, true);

	$orderIds = array();


	if ( count($json_data["content"]) > "0" )
	{
		$order_info = array();
		$order_info_list = array();


		foreach($json_data["content"] as $key=>$values)
		{
			$order_num = $values["orderId"];
			

			$objRequests = new KakaoApi;

			$return_data = $objRequests->order_search_detail($order_num);

			if ( $return_data["result_code"] == "0000" )
			{
				if ($return_data["list"]["orderBase"]["status"] == "TicketRequest")
				{
					foreach($deal_code_array as $k=>$v)
					{
						if ( $v != $return_data["list"]["orderProduct"]["id"] )
						{
							continue;
						}
						else
						{
							if ( strpos($orderIds["orderIds"], $return_data["list"]["id"]) !== false)
							{
								continue;
							}
							else
							{
								$orderIds["orderIds"][] = $return_data["list"]["id"];
							}
						}
					}
				}
			}
		}



		if ( count($orderIds) > 0 )
		{
			$return_order_num = $objRequests->order_check($orderIds);

			//success는 bool이라 0 또는 1로 체크
			if ( $return_order_num["list"]["success"] == 1 )
			{
				foreach($orderIds["orderIds"] as $v_id)
				{
					$url = KAKAO_URL."/v1/shopping/order?order_id=".$v_id;

					//모든 카카오 요청에서 헤더는 고정..
					$headers = array(
						"Authorization: ".WEBSEN_APP_KEY,
						"Target-Authorization: ".WEBSEN_API_KEY,
						"channel-ids: 101",
						"Content-type: application/json;charset=UTF-8"
					);

					$post_data = array();

					$post_data["page"] = "1";
//					$post_data["order_modified_at_start"] = $start_date;//$start_date;
//					$post_data["order_modified_at_end"] = $end_date;//$end_date;
//
//					//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
//					$post_data["order_status"] = "TicketSendWaiting";

					$rtn_data = curl_get($url, $headers, $post_data);

					$httpcode = $rtn_data["httpcode"];
					$json_data_string = $rtn_data["body"];
					$json_data = json_decode($json_data_string, true);

					if ( $httpcode == "200" )
					{
						if ( $json_data["orderBase"]["status"] == "TicketSendWaiting" )
						{
							foreach($deal_code_array as $k=>$v)
							{
								if ( $v != $json_data["orderProduct"]["id"])
								{
									continue;
								}
								else
								{
									unset($order_info_list);

									//함수마다 테이블명 바꿔주기
									$order_info_list["table_name"]			= "salti_websen_tour";
									$order_info_list["user_id"]				= "websen_tour";
									$order_info_list["deal_code"]			= $json_data["orderProduct"]["id"];
									$order_info_list["product_name"]		= $json_data["orderProduct"]["name"];
									$order_info_list["product_option"]		= $json_data["orderProduct"]["optionContent"];
									$order_info_list["barcode"]				= $json_data["id"];
									$order_info_list["buy_name"]			= $json_data["orderTicketReceiver"]["receiverName"];
									$order_info_list["buy_hp"]				= $json_data["orderTicketReceiver"]["receiverPhoneNumber"];
				
									$buy_date = $json_data["orderTicket"]["ticketAcceptedAt"];
									$buy_date_format = date("Y-m-d H:i:s", strtotime($buy_date));
				
									$order_info_list["buy_date"]			= $buy_date_format;
									$order_info_list["stock"]				= $json_data["orderProduct"]["quantity"];
									$order_info_list["price"]				= $json_data["orderProduct"]["settlementBasicPrice"];
				
				
									echo "수집 진행 예정 티켓 ===============================<br>";
									echo "구매자명 : [".$order_info_list["buy_name"]."] / 바코드 : [".$order_info_list["barcode"]."] / 연락처 : [".$order_info_list["buy_hp"]."]<br>";

									$order_info[] = $order_info_list;
								}
							}

						}
						else
						{
							continue;
						}
					}
					else
					{
						continue;
					}
				}

				
				if ( count($order_info) > 0 )
				{

					$order_info_string = json_encode($order_info, JSON_UNESCAPED_UNICODE);
					$objRequests = new KakaoApi;

					$return_data = $objRequests->order_insert_collect($order_info_string);

					if ( $return_data["result_code"] == "0000" )
					{
						$json_data = json_decode($return_data["body"], true);

						$success_info = $json_data["success_info"];
						$error_info = $json_data["error_info"];

						$orderIds = array();
						foreach( $success_info as $k_success_info=>$v_success_info)
						{
							$orderIds["orderIds"][] = $v_success_info;
						}


						//성공주문정보
						if( count($success_info) > 0 )
						{

							$return_order_num = $objRequests->order_send($orderIds);


							//success는 bool이라 0 또는 1로 체크
							if ( $return_order_num["list"]["success"] == 1 )
							{
								echo "주문 등록 및 확인 처리 완료...";
							}
							else if ( $return_order_num["list"]["success"] == 0)
							{

								foreach( $return_order_num["list"]["failedOrderIds"] as $k_failed=>$v_failed )
								{

									//잔디 에러 보내기 추가하기..
									echo "발송 처리 실패 주문번호 : [".$v_failed."]<br>";

								}
							}

						}
					}
					else
					{
						echo "insert 실패";
					}
						
					
				}
				else
				{
					echo "order_info 데이터 없음";
				}


			}
			else
			{
				echo "order_check 실패";
			}

		}
	}

}


function kakao_collect_songdo($deal_code_array, $start_date, $end_date)
{
	$url = KAKAO_URL."/v1/shopping/orders";


	//모든 카카오 요청에서 헤더는 고정..
	$headers = array(
		"Authorization: ".WEBSEN_APP_KEY,
		"Target-Authorization: ".WEBSEN_API_KEY,
		"channel-ids: 101",
		"Content-type: application/json;charset=UTF-8"
	);

	$post_data = array();

	$post_data["page"] = "1";
	$post_data["order_modified_at_start"] = $start_date;//$start_date;
	$post_data["order_modified_at_end"] = $end_date;//$end_date;

	//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
	$post_data["order_status"] = "TicketRequest";
//	$post_data["order_status"] = "TicketSendWaiting";
//	$post_data["order_status"] = "TicketSendComplete";

	$rtn_data = curl_get($url, $headers, $post_data);

	$httpcode = $rtn_data["httpcode"];
	$json_data_string = $rtn_data["body"];
	$json_data = json_decode($json_data_string, true);

	$orderIds = array();


	if ( count($json_data["content"]) > "0" )
	{
		$order_info = array();
		$order_info_list = array();


		foreach($json_data["content"] as $key=>$values)
		{
			$order_num = $values["orderId"];
			

			$objRequests = new KakaoApi;

			$return_data = $objRequests->order_search_detail($order_num);

			if ( $return_data["result_code"] == "0000" )
			{
				foreach($deal_code_array as $k=>$v)
				{
					if ( $v != $return_data["list"]["orderProduct"]["id"] )
					{
						continue;
					}
					else
					{
						$orderIds["orderIds"][] = $return_data["list"]["id"];
					}
				}
			}
		}

		if ( count($orderIds) > 0 )
		{
			$return_order_num = $objRequests->order_check($orderIds);

//			$return_order_num["list"]["success"] = true;

			//success는 bool이라 0 또는 1로 체크
			if ( $return_order_num["list"]["success"] == 1 )
			{
				foreach($orderIds["orderIds"] as $v_id)
				{
					$url = KAKAO_URL."/v1/shopping/order?order_id=".$v_id;

					//모든 카카오 요청에서 헤더는 고정..
					$headers = array(
						"Authorization: ".WEBSEN_APP_KEY,
						"Target-Authorization: ".WEBSEN_API_KEY,
						"channel-ids: 101",
						"Content-type: application/json;charset=UTF-8"
					);

					$post_data = array();

					$post_data["page"] = "1";
//					$post_data["order_modified_at_start"] = $start_date;//$start_date;
//					$post_data["order_modified_at_end"] = $end_date;//$end_date;
//
//					//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
//					$post_data["order_status"] = "TicketSendWaiting";

					$rtn_data = curl_get($url, $headers, $post_data);

					$httpcode = $rtn_data["httpcode"];
					$json_data_string = $rtn_data["body"];
					$json_data = json_decode($json_data_string, true);

					if ( $httpcode == "200" )
					{
						if ( $json_data["orderBase"]["status"] == "TicketSendWaiting" || $json_data["orderBase"]["status"] == "TicketSendComplete")
						{
							unset($order_info_list);

							foreach($deal_code_array as $k=>$v)
							{
								if ( $v != $json_data["orderProduct"]["id"])
								{
									continue;
								}
								else
								{

									//함수마다 테이블명 바꿔주기
									$order_info_list["table_name"]			= "salti_songdo_cms";
									$order_info_list["user_id"]				= "songdo";
									$order_info_list["deal_code"]			= $json_data["orderProduct"]["id"];
									$order_info_list["product_name"]		= $json_data["orderProduct"]["name"];
									$order_info_list["product_option"]		= $json_data["orderProduct"]["optionContent"];
									$order_info_list["barcode"]				= $json_data["id"];
									$order_info_list["buy_name"]			= $json_data["orderTicketReceiver"]["receiverName"];
									$order_info_list["buy_hp"]				= $json_data["orderTicketReceiver"]["receiverPhoneNumber"];
				
									$buy_date = $json_data["orderTicket"]["ticketAcceptedAt"];
									$buy_date_format = date("Y-m-d H:i:s", strtotime($buy_date));
				
									$order_info_list["buy_date"]			= $buy_date_format;
									$order_info_list["stock"]				= $json_data["orderProduct"]["quantity"];
									$order_info_list["price"]				= $json_data["orderProduct"]["settlementBasicPrice"];
				
				
									echo "수집 진행 예정 티켓 ===============================<br>";
									echo "구매자명 : [".$order_info_list["buy_name"]."] / 바코드 : [".$order_info_list["barcode"]."] / 연락처 : [".$order_info_list["buy_hp"]."]<br>";

									$order_info[] = $order_info_list;

								}
							}

						}
						else
						{
							continue;
						}
					}
					else
					{
						continue;
					}
				}

				
				if ( count($order_info) > 0 )
				{
					$order_info_string = json_encode($order_info, JSON_UNESCAPED_UNICODE);
					$objRequests = new KakaoApi;

					$return_data = $objRequests->order_insert_collect_songdo($order_info_string);

					if ( $return_data["result_code"] == "0000" )
					{
						$json_data = json_decode($return_data["body"], true);

						$success_info = $json_data["success_info"];
						$error_info = $json_data["error_info"];

						$orderIds = array();
						foreach( $success_info as $k_success_info=>$v_success_info)
						{
							$orderIds["orderIds"][] = $v_success_info;
						}


						//성공주문정보
						if( count($success_info) > 0 )
						{

							$return_order_num = $objRequests->order_send($orderIds);


							//success는 bool이라 0 또는 1로 체크
							if ( $return_order_num["list"]["success"] == 1 )
							{
								echo "주문 등록 및 확인 처리 완료...";
							}
							else if ( $return_order_num["list"]["success"] == 0)
							{

								foreach( $return_order_num["list"]["failedOrderIds"] as $k_failed=>$v_failed )
								{

									//잔디 에러 보내기 추가하기..
									echo "발송 처리 실패 주문번호 : [".$v_failed."]<br>";

								}
							}

						}
					}
					else
					{
						echo "insert 실패";
					}
						
					
				}
				else
				{
					echo "order_info 데이터 없음";
				}


			}
			else
			{
				echo "order_check 실패";
			}

		}
	}
}

//웹센 종료!!!


function kakao_collect_hoban($deal_code_array, $start_date, $end_date)
{
	$url = KAKAO_URL."/v1/shopping/orders";


	//모든 카카오 요청에서 헤더는 고정..
	$headers = array(
		"Authorization: ".WEBSEN_APP_KEY,
		"Target-Authorization: ".WEBSEN_API_KEY,
		"channel-ids: 101",
		"Content-type: application/json;charset=UTF-8"
	);

	$post_data = array();

	$post_data["page"] = "1";
	$post_data["order_modified_at_start"] = $start_date;//$start_date;
	$post_data["order_modified_at_end"] = $end_date;//$end_date;

	//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
	$post_data["order_status"] = "TicketRequest";
//	$post_data["order_status"] = "TicketSendWaiting";
//	$post_data["order_status"] = "TicketSendComplete";

	$rtn_data = curl_get($url, $headers, $post_data);

	$httpcode = $rtn_data["httpcode"];
	$json_data_string = $rtn_data["body"];
	$json_data = json_decode($json_data_string, true);

	$orderIds = array();


	if ( count($json_data["content"]) > "0" )
	{
		$order_info = array();
		$order_info_list = array();


		foreach($json_data["content"] as $key=>$values)
		{
			$order_num = $values["orderId"];
			

			$objRequests = new KakaoApi;

			$return_data = $objRequests->order_search_detail($order_num);

			if ( $return_data["result_code"] == "0000" )
			{
				foreach($deal_code_array as $k=>$v)
				{
					if ( $v != $return_data["list"]["orderProduct"]["id"] )
					{
						continue;
					}
					else
					{
						$orderIds["orderIds"][] = $return_data["list"]["id"];
					}
				}
			}
		}

		if ( count($orderIds) > 0 )
		{
			$return_order_num = $objRequests->order_check($orderIds);

//			$return_order_num["list"]["success"] = true;

			//success는 bool이라 0 또는 1로 체크
			if ( $return_order_num["list"]["success"] == 1 )
			{
				foreach($orderIds["orderIds"] as $v_id)
				{
					$url = KAKAO_URL."/v1/shopping/order?order_id=".$v_id;

					//모든 카카오 요청에서 헤더는 고정..
					$headers = array(
						"Authorization: ".WEBSEN_APP_KEY,
						"Target-Authorization: ".WEBSEN_API_KEY,
						"channel-ids: 101",
						"Content-type: application/json;charset=UTF-8"
					);

					$post_data = array();

					$post_data["page"] = "1";
//					$post_data["order_modified_at_start"] = $start_date;//$start_date;
//					$post_data["order_modified_at_end"] = $end_date;//$end_date;
//
//					//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
//					$post_data["order_status"] = "TicketSendWaiting";

					$rtn_data = curl_get($url, $headers, $post_data);

					$httpcode = $rtn_data["httpcode"];
					$json_data_string = $rtn_data["body"];
					$json_data = json_decode($json_data_string, true);

					if ( $httpcode == "200" )
					{
						if ( $json_data["orderBase"]["status"] == "TicketSendWaiting" || $json_data["orderBase"]["status"] == "TicketSendComplete")
						{
							unset($order_info_list);

							foreach($deal_code_array as $k=>$v)
							{
								if ( $v != $json_data["orderProduct"]["id"])
								{
									continue;
								}
								else
								{

									//함수마다 테이블명 바꿔주기
									$order_info_list["table_name"]			= "salti_hoban_cms";
									$order_info_list["user_id"]				= "hoban_cms";
									$order_info_list["deal_code"]			= $json_data["orderProduct"]["id"];
									$order_info_list["product_name"]		= $json_data["orderProduct"]["name"];
									$order_info_list["product_option"]		= $json_data["orderProduct"]["optionContent"];
									$order_info_list["barcode"]				= $json_data["id"];
									$order_info_list["buy_name"]			= $json_data["orderTicketReceiver"]["receiverName"];
									$order_info_list["buy_hp"]				= $json_data["orderTicketReceiver"]["receiverPhoneNumber"];
				
									$buy_date = $json_data["orderTicket"]["ticketAcceptedAt"];
									$buy_date_format = date("Y-m-d H:i:s", strtotime($buy_date));
				
									$order_info_list["buy_date"]			= $buy_date_format;
									$order_info_list["stock"]				= $json_data["orderProduct"]["quantity"];
									$order_info_list["price"]				= $json_data["orderProduct"]["settlementBasicPrice"];
				
				
									echo "수집 진행 예정 티켓 ===============================<br>";
									echo "구매자명 : [".$order_info_list["buy_name"]."] / 바코드 : [".$order_info_list["barcode"]."] / 연락처 : [".$order_info_list["buy_hp"]."]<br>";

									$order_info[] = $order_info_list;

								}
							}

						}
						else
						{
							continue;
						}
					}
					else
					{
						continue;
					}
				}

				
				if ( count($order_info) > 0 )
				{
					$order_info_string = json_encode($order_info, JSON_UNESCAPED_UNICODE);
					$objRequests = new KakaoApi;

					$return_data = $objRequests->order_insert_collect_hoban($order_info_string);

					if ( $return_data["result_code"] == "0000" )
					{
						$json_data = json_decode($return_data["body"], true);

						$success_info = $json_data["success_info"];
						$error_info = $json_data["error_info"];

						$orderIds = array();
						foreach( $success_info as $k_success_info=>$v_success_info)
						{
							$orderIds["orderIds"][] = $v_success_info;
						}


						//성공주문정보
						if( count($success_info) > 0 )
						{

							$return_order_num = $objRequests->order_send($orderIds);


							//success는 bool이라 0 또는 1로 체크
							if ( $return_order_num["list"]["success"] == 1 )
							{
								echo "주문 등록 및 확인 처리 완료...";
							}
							else if ( $return_order_num["list"]["success"] == 0)
							{

								foreach( $return_order_num["list"]["failedOrderIds"] as $k_failed=>$v_failed )
								{

									//잔디 에러 보내기 추가하기..
									echo "발송 처리 실패 주문번호 : [".$v_failed."]<br>";

								}
							}

						}
					}
					else
					{
						echo "insert 실패";
					}
						
					
				}
				else
				{
					echo "order_info 데이터 없음";
				}


			}
			else
			{
				echo "order_check 실패";
			}

		}
	}
}

function kakao_collect_topsten($deal_code_array, $start_date, $end_date)
{
	$url = KAKAO_URL."/v1/shopping/orders";


	//모든 카카오 요청에서 헤더는 고정..
	$headers = array(
		"Authorization: ".WEBSEN_APP_KEY,
		"Target-Authorization: ".WEBSEN_API_KEY,
		"channel-ids: 101",
		"Content-type: application/json;charset=UTF-8"
	);

	$post_data = array();

	$post_data["page"] = "1";
	$post_data["order_modified_at_start"] = $start_date;//$start_date;
	$post_data["order_modified_at_end"] = $end_date;//$end_date;

	//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
	$post_data["order_status"] = "TicketRequest";
//	$post_data["order_status"] = "TicketSendWaiting";
//	$post_data["order_status"] = "TicketSendComplete";

	$rtn_data = curl_get($url, $headers, $post_data);

	$httpcode = $rtn_data["httpcode"];
	$json_data_string = $rtn_data["body"];
	$json_data = json_decode($json_data_string, true);

	$orderIds = array();


	if ( count($json_data["content"]) > "0" )
	{
		$order_info = array();
		$order_info_list = array();


		foreach($json_data["content"] as $key=>$values)
		{
			$order_num = $values["orderId"];
			

			$objRequests = new KakaoApi;

			$return_data = $objRequests->order_search_detail($order_num);

			if ( $return_data["result_code"] == "0000" )
			{
				foreach($deal_code_array as $k=>$v)
				{
					if ( $v != $return_data["list"]["orderProduct"]["id"] )
					{
						continue;
					}
					else
					{
						$orderIds["orderIds"][] = $return_data["list"]["id"];
					}
				}
			}
		}

		if ( count($orderIds) > 0 )
		{
			$return_order_num = $objRequests->order_check($orderIds);

//			$return_order_num["list"]["success"] = true;

			//success는 bool이라 0 또는 1로 체크
			if ( $return_order_num["list"]["success"] == 1 )
			{
				foreach($orderIds["orderIds"] as $v_id)
				{
					$url = KAKAO_URL."/v1/shopping/order?order_id=".$v_id;

					//모든 카카오 요청에서 헤더는 고정..
					$headers = array(
						"Authorization: ".WEBSEN_APP_KEY,
						"Target-Authorization: ".WEBSEN_API_KEY,
						"channel-ids: 101",
						"Content-type: application/json;charset=UTF-8"
					);

					$post_data = array();

					$post_data["page"] = "1";
//					$post_data["order_modified_at_start"] = $start_date;//$start_date;
//					$post_data["order_modified_at_end"] = $end_date;//$end_date;
//
//					//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
//					$post_data["order_status"] = "TicketSendWaiting";

					$rtn_data = curl_get($url, $headers, $post_data);

					$httpcode = $rtn_data["httpcode"];
					$json_data_string = $rtn_data["body"];
					$json_data = json_decode($json_data_string, true);

					if ( $httpcode == "200" )
					{
						if ( $json_data["orderBase"]["status"] == "TicketSendWaiting" || $json_data["orderBase"]["status"] == "TicketSendComplete")
						{
							unset($order_info_list);

							foreach($deal_code_array as $k=>$v)
							{
								if ( $v != $json_data["orderProduct"]["id"])
								{
									continue;
								}
								else
								{

									//함수마다 테이블명 바꿔주기
									$order_info_list["table_name"]			= "salti_topsten_cms";
									$order_info_list["user_id"]				= "topsten";
									$order_info_list["deal_code"]			= $json_data["orderProduct"]["id"];
									$order_info_list["product_name"]		= $json_data["orderProduct"]["name"];
									$order_info_list["product_option"]		= $json_data["orderProduct"]["optionContent"];
									$order_info_list["barcode"]				= $json_data["id"];
									$order_info_list["buy_name"]			= $json_data["orderTicketReceiver"]["receiverName"];
									$order_info_list["buy_hp"]				= $json_data["orderTicketReceiver"]["receiverPhoneNumber"];
				
									$buy_date = $json_data["orderTicket"]["ticketAcceptedAt"];
									$buy_date_format = date("Y-m-d H:i:s", strtotime($buy_date));
				
									$order_info_list["buy_date"]			= $buy_date_format;
									$order_info_list["stock"]				= $json_data["orderProduct"]["quantity"];
									$order_info_list["price"]				= $json_data["orderProduct"]["settlementBasicPrice"];
				
				
									echo "수집 진행 예정 티켓 ===============================<br>";
									echo "구매자명 : [".$order_info_list["buy_name"]."] / 바코드 : [".$order_info_list["barcode"]."] / 연락처 : [".$order_info_list["buy_hp"]."]<br>";

									$order_info[] = $order_info_list;

								}
							}

						}
						else
						{
							continue;
						}
					}
					else
					{
						continue;
					}
				}

				
				if ( count($order_info) > 0 )
				{
					$order_info_string = json_encode($order_info, JSON_UNESCAPED_UNICODE);
					$objRequests = new KakaoApi;

					$return_data = $objRequests->order_insert_collect_songdo($order_info_string);

					if ( $return_data["result_code"] == "0000" )
					{
						$json_data = json_decode($return_data["body"], true);

						$success_info = $json_data["success_info"];
						$error_info = $json_data["error_info"];

						$orderIds = array();
						foreach( $success_info as $k_success_info=>$v_success_info)
						{
							$orderIds["orderIds"][] = $v_success_info;
						}


						//성공주문정보
						if( count($success_info) > 0 )
						{

							$return_order_num = $objRequests->order_send($orderIds);


							//success는 bool이라 0 또는 1로 체크
							if ( $return_order_num["list"]["success"] == 1 )
							{
								echo "주문 등록 및 확인 처리 완료...";
							}
							else if ( $return_order_num["list"]["success"] == 0)
							{

								foreach( $return_order_num["list"]["failedOrderIds"] as $k_failed=>$v_failed )
								{

									//잔디 에러 보내기 추가하기..
									echo "발송 처리 실패 주문번호 : [".$v_failed."]<br>";

								}
							}

						}
					}
					else
					{
						echo "insert 실패";
					}
						
					
				}
				else
				{
					echo "order_info 데이터 없음";
				}


			}
			else
			{
				echo "order_check 실패";
			}

		}
	}
}

function kakao_collect_gram($deal_code_array, $start_date, $end_date)
{
	$url = KAKAO_URL."/v1/shopping/orders";


	//모든 카카오 요청에서 헤더는 고정..
	$headers = array(
		"Authorization: ".GRAM_APP_KEY,
		"Target-Authorization: ".GRAM_API_KEY,
		"channel-ids: 101",
		"Content-type: application/json;charset=UTF-8"
	);

	$post_data = array();

	$post_data["page"] = "1";
	$post_data["order_modified_at_start"] = $start_date;//$start_date;
	$post_data["order_modified_at_end"] = $end_date;//$end_date;

	//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
	$post_data["order_status"] = "TicketRequest";
//	$post_data["order_status"] = "TicketSendWaiting";
//	$post_data["order_status"] = "TicketSendComplete";



	$rtn_data = curl_get($url, $headers, $post_data);

	$httpcode = $rtn_data["httpcode"];
	$json_data_string = $rtn_data["body"];
	$json_data = json_decode($json_data_string, true);


	$orderIds = array();


	if ( count($json_data["content"]) > "0" )
	{
		$order_info = array();
		$order_info_list = array();


		foreach($json_data["content"] as $key=>$values)
		{
			$order_num = $values["orderId"];
			

			$objRequests = new KakaoApi;

			$return_data = $objRequests->order_search_detail_gram($order_num);

			if ( $return_data["result_code"] == "0000" )
			{
				foreach($deal_code_array as $k=>$v)
				{
					if ( $v != $return_data["list"]["orderProduct"]["id"] )
					{
						continue;
					}
					else
					{
						$orderIds["orderIds"][] = $return_data["list"]["id"];
					}
				}
			}
		}

		if ( count($orderIds) > 0 )
		{
			$return_order_num = $objRequests->order_check_gram($orderIds);

			echo_json_encode($return_order_num);


//			$return_order_num["list"]["success"] = true;

			//success는 bool이라 0 또는 1로 체크
			if ( $return_order_num["list"]["success"] == 1 )
			{
				foreach($orderIds["orderIds"] as $v_id)
				{
					$url = KAKAO_URL."/v1/shopping/order?order_id=".$v_id;

					//모든 카카오 요청에서 헤더는 고정..
					$headers = array(
						"Authorization: ".GRAM_APP_KEY,
						"Target-Authorization: ".GRAM_API_KEY,
						"channel-ids: 101",
						"Content-type: application/json;charset=UTF-8"
					);

					$post_data = array();

					$post_data["page"] = "1";
//					$post_data["order_modified_at_start"] = $start_date;//$start_date;
//					$post_data["order_modified_at_end"] = $end_date;//$end_date;
//
//					//발송 요청 상태 주문건만 요청... 취소 / 발송 완료 등의 상태값 주문건 제외..
//					$post_data["order_status"] = "TicketSendWaiting";

					$rtn_data = curl_get($url, $headers, $post_data);

					$httpcode = $rtn_data["httpcode"];
					$json_data_string = $rtn_data["body"];
					$json_data = json_decode($json_data_string, true);

					if ( $httpcode == "200" )
					{
						if ( $json_data["orderBase"]["status"] == "TicketSendWaiting" || $json_data["orderBase"]["status"] == "TicketSendComplete")
						{
							unset($order_info_list);

							foreach($deal_code_array as $k=>$v)
							{
								if ( $v != $json_data["orderProduct"]["id"])
								{
									continue;
								}
								else
								{

									//함수마다 테이블명 바꿔주기
									$order_info_list["table_name"]			= "salti_graminside";
									$order_info_list["user_id"]				= "graminside";
									$order_info_list["deal_code"]			= $json_data["orderProduct"]["id"];
									$order_info_list["product_name"]		= $json_data["orderProduct"]["name"];
									$order_info_list["product_option"]		= $json_data["orderProduct"]["optionContent"];
									$order_info_list["barcode"]				= $json_data["id"];
									$order_info_list["buy_name"]			= $json_data["orderTicketReceiver"]["receiverName"];
									$order_info_list["buy_hp"]				= $json_data["orderTicketReceiver"]["receiverPhoneNumber"];
				
									$buy_date = $json_data["orderTicket"]["ticketAcceptedAt"];
									$buy_date_format = date("Y-m-d H:i:s", strtotime($buy_date));
				
									$order_info_list["buy_date"]			= $buy_date_format;
									$order_info_list["stock"]				= $json_data["orderProduct"]["quantity"];
									$order_info_list["price"]				= $json_data["orderProduct"]["settlementBasicPrice"];
				
				
									echo "수집 진행 예정 티켓 ===============================<br>";
									echo "구매자명 : [".$order_info_list["buy_name"]."] / 바코드 : [".$order_info_list["barcode"]."] / 연락처 : [".$order_info_list["buy_hp"]."]<br>";

									$order_info[] = $order_info_list;

								}
							}

						}
						else
						{
							continue;
						}
					}
					else
					{
						continue;
					}
				}

				
				if ( count($order_info) > 0 )
				{
					$order_info_string = json_encode($order_info, JSON_UNESCAPED_UNICODE);
					$objRequests = new KakaoApi;

					$return_data = $objRequests->order_insert_collect_gram($order_info_string);

					if ( $return_data["result_code"] == "0000" )
					{
						$json_data = json_decode($return_data["body"], true);

						$success_info = $json_data["success_info"];
						$error_info = $json_data["error_info"];

						$orderIds = array();
						foreach( $success_info as $k_success_info=>$v_success_info)
						{
							$orderIds["orderIds"][] = $v_success_info;
						}


						//성공주문정보
						if( count($success_info) > 0 )
						{

							$return_order_num = $objRequests->order_send_gram($orderIds);


							//success는 bool이라 0 또는 1로 체크
							if ( $return_order_num["list"]["success"] == 1 )
							{
								echo "주문 등록 및 확인 처리 완료...";
							}
							else if ( $return_order_num["list"]["success"] == 0)
							{

								foreach( $return_order_num["list"]["failedOrderIds"] as $k_failed=>$v_failed )
								{

									//잔디 에러 보내기 추가하기..
									echo "발송 처리 실패 주문번호 : [".$v_failed."]<br>";

								}
							}

						}
					}
					else
					{
						echo "insert 실패";
					}
						
					
				}
				else
				{
					echo "order_info 데이터 없음";
				}


			}
			else
			{
				echo "order_check 실패";
			}

		}
	}
}

function curl_get($url, $headers, $post_data)
	{

		$url = $url . "?" . http_build_query($post_data);

		$ch = curl_init();

		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch,CURLOPT_HEADER, 0);
		curl_setopt($ch,CURLOPT_HTTPHEADER, $headers); //header 지정하기
		curl_setopt($ch, CURLOPT_POST, 0);

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));

		$return["httpcode"] = $httpcode;
		$return["body"] = $res;

		return $return;
	}

?>