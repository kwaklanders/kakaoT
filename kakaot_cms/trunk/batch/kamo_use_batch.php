<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>
<?

$nowdate = date("Y-m-d H:i:s");

$today = date("Y-m-d H:i:s", strtotime("-300 minute", strtotime($nowdate)));
$before_date = date("Y-m-d H:i:s", strtotime("-1 day", strtotime($nowdate)));

$strSql = "";
$strSql .= " select ";
$strSql .= " salti_pos_detail.order_num as order_num ";
$strSql .= " from salti_pos_detail ";
$strSql .= " inner join salti_order on salti_pos_detail.order_num = salti_order.order_num ";
$strSql .= " where salti_pos_detail.status = 1 ";
$strSql .= " and salti_order.status = 11 ";
$strSql .= " and salti_pos_detail.status = 1 ";
$strSql .= " and salti_pos_detail.channel_dv_status = 'E' ";
$strSql .= " and salti_pos_detail.channel_code = '7021' ";
$strSql .= " group by order_num ";

$rsList = mysql_query($strSql);
$rscount = mysql_num_rows($rsList);

if ( $rscount > 0 )
{
	while ( $order = mysql_fetch_assoc($rsList))
	{
		$order_list[] = $order["order_num"];
	}
	
echo "연동 실패 주문번호 리스트 : [";
echo_json_encode($order_list);
echo "]<br>";

}


//네이버 1시간 미결제 취소 삭제대상 조회
$strSql = "";
$strSql .= " select ";
$strSql .= " salti_pos_detail.order_num as order_num ";
$strSql .= " from salti_pos_detail ";
$strSql .= " inner join salti_order on salti_pos_detail.order_num = salti_order.order_num ";
$strSql .= " where salti_pos_detail.status = 1 ";
$strSql .= " and salti_order.status = 11 ";
$strSql .= " and salti_pos_detail.status = 1 ";
$strSql .= " and salti_pos_detail.channel_dv_status = 'N' ";
$strSql .= " and salti_pos_detail.channel_code = '7021' ";
$strSql .= " and date_format(salti_pos_detail.use_date, '%Y-%m-%d %H:%i:%s') >= '".$today."' ";
$strSql .= " group by order_num ";

$order_search_value = mysql_query($strSql);
$order_search_count = mysql_num_rows($order_search_value);

if($order_search_count > 0)
{
	$idx_array = array();
	$api_array = array();

	//트랜잭션 시작
	mysql_query("SET AUTOCOMMIT=0");
	mysql_query("BEGIN");
	
	while($order_rows = mysql_fetch_assoc($order_search_value))
	{
		$strSql = "";
		$strSql .= " select ";
		$strSql .= " barcode ";
		$strSql .= " , idx ";
		$strSql .= " , use_date ";
		$strSql .= " from salti_pos_detail ";
		$strSql .= " where order_num = '".$order_rows["order_num"]."' ";
		$strSql .= " and status = 1 ";
		$strSql .= " and channel_dv_status = 'N' ";


		$rsList = mysql_query($strSql);
		$rsCount = mysql_num_rows($rsList);

		if ( $rsCount > 0 )
		{
			$barcode_array = array();

			while($order_detail = mysql_fetch_assoc($rsList))
			{
				$use_date = $order_detail["use_date"];
				$barcode = $order_detail["barcode"];
				$idx = $order_detail["idx"];

				$barcode_array[] = $barcode;
				$idx_array[] = $idx;
			}

			$date = DateTime::createFromFormat('Y-m-d H:i:s', $use_date, new DateTimeZone('Asia/Seoul'));
			
			$isoDate = $date->format('Y-m-d\TH:i:sO');


			$api_array["used_at"] = $isoDate;
			$api_array["order_num"] = $order_rows["order_num"];
			$api_array["barcodes"] = $barcode_array;
			
			$header_array = array(
				"Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.eyJhdWQiOiJjb20ua2FrYW9tb2JpbGl0eS5sZWlzdXJlIiwicGFydG5lcl9pZCI6MywiaXNzIjoiaHR0cHM6Ly93d3cua2FrYW9tb2JpbGl0eS5jb20iLCJleHAiOjIwMzE4OTg2OTQsImlhdCI6MTcxNjM2NTg5NCwic2VxIjoxfQ.Mpf4I8uEDioDusz3wcEIXzV5F6KgIda_uFkAF_N1ALMm1H0cY0k_Cg6zoD8A_yymu1mWa---oeYAVlY132GQlQ",
				"Content-Type: application/json;charset=UTF-8"
			);

			$post_data = json_encode($api_array, JSON_UNESCAPED_UNICODE);

			$url = "https://t-leisure-webhook.kakao.com/webhook/partners/QPASS/v1/order-items/status/used";

			$response = curl_kamo_api($url, $post_data, $header_array);


			if ( $response["http_code"] == "200" )
			{
				foreach ( $idx_array as $v )
				{
					$strSql = "";
					$strSql .= " update ";
					$strSql .= " salti_pos_detail ";
					$strSql .= " set channel_dv_status = 'Y' ";
					$strSql .= " where idx = '".$v."' ";

//					echo $strSql."<br>";

					$update = mysql_query($strSql);

					if ( $update )
					{
						mysql_query("COMMIT");
						echo "연동 완료[idx : ".$v."]<br>";
					}
					else
					{
						mysql_query("ROLLBACK");
						echo "연동 완료. 업데이트 실패 [idx : ".$v."]<br>";
					}
				}
			}
			else if ( $response["http_code"] == "409" || $response["http_code"] == "400" )
			{

				if ( strpos($response["response"], "113001") )
				{
					foreach ( $idx_array as $v )
					{
						$strSql = "";
						$strSql .= " update ";
						$strSql .= " salti_pos_detail ";
						$strSql .= " set channel_dv_status = 'Y' ";
						$strSql .= " where idx = '".$v."' ";

						$update = mysql_query($strSql);
						if ( $update )
						{
							mysql_query("COMMIT");
							echo "연동 기완료[idx : ".$v."]<br>";
						}
						else
						{
							mysql_query("ROLLBACK");
							echo "연동 기완료. 업데이트 실패 [idx : ".$v."]<br>";
						}
					}
				}
				else
				{
					foreach ( $idx_array as $v )
					{
						$strSql = "";
						$strSql .= " update ";
						$strSql .= " salti_pos_detail ";
						$strSql .= " set channel_dv_status = 'E' ";
						$strSql .= " , channel_dv_msg = '".$response["response"]."' ";
						$strSql .= " where idx = '".$v."' ";

						$update = mysql_query($strSql);
						if ( $update )
						{
							mysql_query("COMMIT");
							echo "연동 미완료[idx : ".$v."]<br>";
						}
						else
						{
							mysql_query("ROLLBACK");
							echo "연동 미완료. 업데이트 실패 [idx : ".$v."]<br>";
						}
					}
				}
			}
			else
			{
					foreach ( $idx_array as $v )
					{
						$strSql = "";
						$strSql .= " update ";
						$strSql .= " salti_pos_detail ";
						$strSql .= " set channel_dv_status = 'E' ";
						$strSql .= " , channel_dv_msg = '".$response["response"]."' ";
						$strSql .= " where idx = '".$v."' ";

						$update = mysql_query($strSql);
						if ( $update )
						{
							mysql_query("COMMIT");
							echo "연동 미완료[idx : ".$v."]<br>";
						}
						else
						{
							mysql_query("ROLLBACK");
							echo "연동 미완료. 업데이트 실패 [idx : ".$v."]<br>";
						}
					}

//				mysql_query("ROLLBACK");
//				echo "연동 실패 - [order_num : ".$order_rows["order_num"]."] - 응답 [";
//				echo_json_encode($response);
//				echo "] <br>";
			}
		}
		else
		{
			echo "조회 바코드 없음. 주문건 확인 요망." . $order_rows["order_num"] . "<br>";
			break;
		}

	}//while 종료

	mysql_query("SET AUTOCOMMIT=1");
}
else
{
	echo "미사용 처리 주문건 없음.";
}

function curl_kamo_api($url, $post_data, $header)
{
	$ch = curl_init();

    // cURL 옵션 설정
    curl_setopt($ch, CURLOPT_URL, $url); // 요청할 URL
    curl_setopt($ch, CURLOPT_POST, 1); // POST 요청
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); // POST 데이터
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 응답 결과를 문자열로 반환
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // SSL 인증서 검증
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // SSL 인증서 검증
	curl_setopt($ch, CURLOPT_HEADER, false); // 헤더 정보를 포함하지 않음
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    
    // 요청 실행 및 결과 저장
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // HTTP 상태 코드 가져오기

    // 오류가 발생하면 오류 정보 출력
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    }

    // cURL 세션 종료
    curl_close($ch);

    // 결과 반환
    return [
        'http_code' => $httpCode,
        'response' => $response
    ];
}
?>
