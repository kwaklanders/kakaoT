<?require_once($_SERVER["DOCUMENT_ROOT"]."/kakao/skin/common.php");?>
<?


$start_date = date("YmdHi00",strtotime("-1439 minute"));
$end_date = date("YmdHi00");


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



if ( $httpcode == "200")
{
	$order_info = array();
	$order_info_list = array();


	foreach($json_data["content"] as $key=>$values)
	{

		$order_num = $values["orderId"];
			

		$objRequests = new KakaoApi;

		$return_data = $objRequests->order_search_detail($order_num);

		echo_json_encode($return_data);


		if ( $return_data["result_code"] == "0000" )
		{
			if ($return_data["list"]["orderBase"]["status"] == "TicketRequest")
			{

				//배열초기화
				unset($order_info_list);

				//insert용 저장
				if ( $return_data["list"]["orderProduct"]["id"] == "" )
				{
					$order_info_list["table_name"]			= "salti_qpos_system";
				}
				else
				{
					$order_info_list["table_name"]			= "salti_websen_tour";
				}
				$order_info_list["deal_code"]			= $return_data["list"]["orderProduct"]["id"];
				$order_info_list["product_name"]		= $return_data["list"]["orderProduct"]["name"];
				$order_info_list["product_option"]		= $return_data["list"]["orderProduct"]["optionContent"];
				$order_info_list["barcode"]				= $return_data["list"]["id"];
				$order_info_list["buy_name"]			= $return_data["list"]["orderTicketReceiver"]["receiverName"];
				$order_info_list["buy_hp"]				= $return_data["list"]["orderTicketReceiver"]["receiverPhoneNumber"];

				$buy_date = $return_data["list"]["orderTicket"]["ticketAcceptedAt"];
				$buy_date_format = date("Y-m-d H:i:s", strtotime($buy_date));

				$order_info_list["buy_date"]			= $buy_date_format;
				$order_info_list["stock"]				= $return_data["list"]["orderProduct"]["quantity"];
				$order_info_list["price"]				= $return_data["list"]["orderProduct"]["settlementBasicPrice"];


				echo "수집 진행 예정 티켓 ===============================<br>";
				echo "구매자명 : [".$order_info_list["buy_name"]."] / 바코드 : [".$order_info_list["barcode"]."] / 연락처 : [".$order_info_list["buy_hp"]."]<br>";

			


				$order_info[] = $order_info_list;
			}

			else
			{
				echo "발송 대기 주문건 아님... barcode : [".$return_data["list"]["id"]."] / buy_name : [".$return_data["list"]["orderTicketReceiver"]["receiverPhoneNumber"]."]<br>";
				continue;

			}


		}
		else
		{

			echo "딜 정보 확인 실패..";
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
				//주문확인처리.....

				$return_order_num = $objRequests->order_check($orderIds);
				//success는 bool이라 0 또는 1로 체크
				if ( $return_data["list"]["success"] == 1 )
				{
					echo_json_encode($return_data["list"]);
					echo "<br>주문 확인 처리 성공!!";
				
				}

				else if ( $return_data["list"]["success"] == 0 )
				{

					foreach ( $return_data["list"]["failedOrderIds"] as $k_checkfail=>$v_checkfail )
					{
						echo "주문 확인 실패 주문 번호 : [".$v_checkfail."]<br>";
					}

				}


				//주문확정처리.....
				
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

						echo "발송 처리 실패 주문번호 : [".$v_failed."]<br>";

					}
				}

			}


			//실패주문정보
			if( count($error_info) > 0 )
			{
				echo "실패처리..";
				//주문실패알림
				//error_send....
			}

		}
		else
		{
			echo "주문 등록 실패";
			echo $return_data["result_msg"];
			//주문 등록 실패
			//error_send....
		}
	}

	//주문 insert....collect curl.......send......
}

else
{
	echo "주문 확인 실패.. 확인 요망<br>";
	echo $json_data_string;
	exit;
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