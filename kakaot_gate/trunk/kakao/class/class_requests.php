<?
class KakaoApi {


//변경 주문건 가져오기 종료

//주문 별 상세 정보 가져오기.

	public function order_search_detail($order_num)
	{
		$url = KAKAO_URL."/v1/shopping/order";


		$headers = array(
			"Authorization: ".WEBSEN_APP_KEY,
			"Target-Authorization: ".WEBSEN_API_KEY,
			"channel-ids: 101",
			"Content-type: application/json"
		);

		$post_data = array();

		$post_data["order_id"] = $order_num;



		$rtn_data = $this->curl_get($url, $post_data ,$headers);


		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

//		echo $json_data_string;
//		echo "<br>";

		$return_data = array();

		if ( $httpcode == "200")
		{

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;

		}
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;

	}

	public function order_search_detail_gram($order_num)
	{
		$url = KAKAO_URL."/v1/shopping/order";


		$headers = array(
			"Authorization: ".GRAM_APP_KEY,
			"Target-Authorization: ".GRAM_API_KEY,
			"channel-ids: 101",
			"Content-type: application/json"
		);

		$post_data = array();

		$post_data["order_id"] = $order_num;



		$rtn_data = $this->curl_get($url, $post_data ,$headers);


		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

//		echo $json_data_string;
//		echo "<br>";

		$return_data = array();

		if ( $httpcode == "200")
		{

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;

		}
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;

	}

	public function order_insert_collect($order_info_string)
	{

		$url = "http://collect.salti.co.kr/page/api_gate/order_insert_kakao.php";
		
		$post_data = array();

		$post_data["order_info_string"] = $order_info_string;

//		echo $url;
//		echo http_build_query($post_data);
//		exit;

		$rtn_data = $this->curl_post_collect($url, $post_data);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];

//		echo_json_encode($json_data_string);
//		echo "<br>";

		$return_data = array();



		if ( $httpcode == "200")
		{

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["body"] = $json_data_string;
		}
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]";

		}
		

		return $return_data;

	}

	public function order_insert_collect_hoban($order_info_string)
	{

		$url = "http://collect.salti.co.kr/page/api_gate/order_insert_kakao_hoban.php";
		
		$post_data = array();

		$post_data["order_info_string"] = $order_info_string;

//		echo $url;
//		echo http_build_query($post_data);
//		exit;

		$rtn_data = $this->curl_post_collect($url, $post_data);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];

//		echo_json_encode($json_data_string);
//		echo "<br>";

		$return_data = array();



		if ( $httpcode == "200")
		{

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["body"] = $json_data_string;
		}
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]";

		}
		

		return $return_data;

	}


	public function order_insert_collect_songdo($order_info_string)
	{

		$url = "http://collect.salti.co.kr/page/api_gate/order_insert_kakao_songdo.php";
		
		$post_data = array();

		$post_data["order_info_string"] = $order_info_string;

//		echo $url;
//		echo http_build_query($post_data);
//		exit;

		$rtn_data = $this->curl_post_collect($url, $post_data);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];

//		echo_json_encode($json_data_string);
//		echo "<br>";

		$return_data = array();



		if ( $httpcode == "200")
		{

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["body"] = $json_data_string;
		}
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]";

		}
		

		return $return_data;

	}

	public function order_insert_collect_gram($order_info_string)
	{

		$url = "http://collect.salti.co.kr/page/api_gate/order_insert_kakao_gram.php";
		
		$post_data = array();

		$post_data["order_info_string"] = $order_info_string;

//		echo $url;
//		echo http_build_query($post_data);
//		exit;

		$rtn_data = $this->curl_post_collect($url, $post_data);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];

//		echo_json_encode($json_data_string);
//		echo "<br>";

		$return_data = array();



		if ( $httpcode == "200")
		{

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["body"] = $json_data_string;
		}
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]";

		}
		

		return $return_data;

	}
//주문 별 상세 정보 가져오기 종료.

//주문 확인처리.. put

	public function order_check($orderIds)
	{
		$url = KAKAO_URL."/v1/shopping/tickets/confirm";

		$headers = array(
			"Authorization: ".WEBSEN_APP_KEY,
			"Target-Authorization: ".WEBSEN_API_KEY,
			"channel-ids: 101",
			"Content-type: application/json"
		);


		$rtn_data = $this->curl_put($url, $orderIds, $headers);


		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		if ( $httpcode == "200")
		{

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;

		}
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//
	public function order_check_gram($orderIds)
	{
		$url = KAKAO_URL."/v1/shopping/tickets/confirm";

		$headers = array(
			"Authorization: ".GRAM_APP_KEY,
			"Target-Authorization: ".GRAM_API_KEY,
			"channel-ids: 101",
			"Content-type: application/json"
		);


		$rtn_data = $this->curl_put($url, $orderIds, $headers);


		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		if ( $httpcode == "200")
		{

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;

		}
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}
//주문 발송 완료 처리

	public function order_send($orderIds)
	{
		$url = KAKAO_URL."/v1/shopping/tickets/complete";

		$headers = array(
			"Authorization: ".WEBSEN_APP_KEY,
			"Target-Authorization: ".WEBSEN_API_KEY,
			"channel-ids: 101",
			"Content-type: application/json"
		);


		$rtn_data = $this->curl_put($url, $orderIds, $headers);


		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

//		echo $json_data_string;
//		echo "<br>";

		$return_order_num = array();

		if ( $httpcode == "200")
		{

			$return_order_num["result_code"] = "0000";
			$return_order_num["result_msg"] = "ok[".$httpcode."]";
			$return_order_num["list"] = $json_data;

		}
		else
		{
			$return_order_num["result_code"] = "9999";
			$return_order_num["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_order_num;
	}

	public function order_send_gram($orderIds)
	{
		$url = KAKAO_URL."/v1/shopping/tickets/complete";

		$headers = array(
			"Authorization: ".GRAM_APP_KEY,
			"Target-Authorization: ".GRAM_API_KEY,
			"channel-ids: 101",
			"Content-type: application/json"
		);


		$rtn_data = $this->curl_put($url, $orderIds, $headers);


		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

//		echo $json_data_string;
//		echo "<br>";

		$return_order_num = array();

		if ( $httpcode == "200")
		{

			$return_order_num["result_code"] = "0000";
			$return_order_num["result_msg"] = "ok[".$httpcode."]";
			$return_order_num["list"] = $json_data;

		}
		else
		{
			$return_order_num["result_code"] = "9999";
			$return_order_num["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_order_num;
	}
//주문 발송 완료 처리 종료



	// http API연동 POST방식..
	public function curl_post($url, $post_data, $headers)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //header 지정하기
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); // Post 값  Get 방식처럼적는다.

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo "httpcode : ". $httpcode;
		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));


		$return["httpcode"] = $httpcode;
		$return["body"] = $res;

		return $return;
	}

	// http API연동 POST방식..
	public function curl_post_collect($url, $post_data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data)); // Post 값  Get 방식처럼적는다.

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo "httpcode : ". $httpcode;
		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));


		$return["httpcode"] = $httpcode;
		$return["body"] = $res;

		return $return;
	}


	// http API연동 GET방식..
	public function curl_get($url, $post_data, $headers)
	{

		$url = $url . "?" . http_build_query($post_data);

		$ch = curl_init();

		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch,CURLOPT_HEADER, false);
		curl_setopt($ch,CURLOPT_HTTPHEADER, $headers); //header 지정하기

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);


		$res = str_replace("\r", "", str_replace("\n", "", $res));

		$return_data["httpcode"] = $httpcode;
		$return_data["body"] = $res;
		return $return_data;
	}

	// http API연동 PATCH방식..
	public function curl_put($url, $post_data, $headers)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //header 지정하기
//		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // patch, put, delete 등
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data, JSON_UNESCAPED_UNICODE)); // Post 값  Get 방식처럼적는다.


		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo "httpcode : ". $httpcode;
		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));

		$return["httpcode"] = $httpcode;
		$return["body"] = $res;

		return $return;
	}

// http API연동 DELETE방식..
	public function curl_delete($url, $post_data, $headers)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //header 지정하기
//		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // patch, put, delete 등
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); // Post 값  Get 방식처럼적는다.

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo "httpcode : ". $httpcode;
		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));

		$return["httpcode"] = $httpcode;
		$return["body"] = $res;

		return $return;
	}

}

class KakaoApiKf {


//변경 주문건 가져오기 종료

//주문 별 상세 정보 가져오기.

	public function order_search_detail($order_num)
	{
		$url = KAKAO_URL."/v1/shopping/order";


		$headers = array(
			"Authorization: ".WEBSEN_APP_KEY,
			"Target-Authorization: ".KF_API_KEY,
			"channel-ids: 101",
			"Content-type: application/json"
		);

		$post_data = array();

		$post_data["order_id"] = $order_num;



		$rtn_data = $this->curl_get($url, $post_data ,$headers);


		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

//		echo $json_data_string;
//		echo "<br>";

		$return_data = array();

		if ( $httpcode == "200")
		{

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;

		}
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;

	}


	public function order_insert_collect($order_info_string)
	{

		$url = "http://collect.salti.co.kr/page/api_gate/order_insert_kakao.php";
		
		$post_data = array();

		$post_data["order_info_string"] = $order_info_string;

//		echo $url;
//		echo http_build_query($post_data);
//		exit;

		$rtn_data = $this->curl_post_collect($url, $post_data);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];

//		echo_json_encode($json_data_string);
//		echo "<br>";

		$return_data = array();



		if ( $httpcode == "200")
		{

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["body"] = $json_data_string;
		}
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]";

		}
		

		return $return_data;

	}

//주문 별 상세 정보 가져오기 종료.

//주문 확인처리.. put

	public function order_check($orderIds)
	{
		$url = KAKAO_URL."/v1/shopping/tickets/confirm";

		$headers = array(
			"Authorization: ".WEBSEN_APP_KEY,
			"Target-Authorization: ".KF_API_KEY,
			"channel-ids: 101",
			"Content-type: application/json"
		);


		$rtn_data = $this->curl_put($url, $orderIds, $headers);


		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		if ( $httpcode == "200")
		{

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;

		}
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}
//주문 발송 완료 처리

	public function order_send($orderIds)
	{
		$url = KAKAO_URL."/v1/shopping/tickets/complete";

		$headers = array(
			"Authorization: ".WEBSEN_APP_KEY,
			"Target-Authorization: ".KF_API_KEY,
			"channel-ids: 101",
			"Content-type: application/json"
		);


		$rtn_data = $this->curl_put($url, $orderIds, $headers);


		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

//		echo $json_data_string;
//		echo "<br>";

		$return_order_num = array();

		if ( $httpcode == "200")
		{

			$return_order_num["result_code"] = "0000";
			$return_order_num["result_msg"] = "ok[".$httpcode."]";
			$return_order_num["list"] = $json_data;

		}
		else
		{
			$return_order_num["result_code"] = "9999";
			$return_order_num["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_order_num;
	}
//주문 발송 완료 처리 종료



	// http API연동 POST방식..
	public function curl_post($url, $post_data, $headers)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //header 지정하기
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); // Post 값  Get 방식처럼적는다.

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo "httpcode : ". $httpcode;
		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));


		$return["httpcode"] = $httpcode;
		$return["body"] = $res;

		return $return;
	}

	// http API연동 POST방식..
	public function curl_post_collect($url, $post_data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data)); // Post 값  Get 방식처럼적는다.

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo "httpcode : ". $httpcode;
		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));


		$return["httpcode"] = $httpcode;
		$return["body"] = $res;

		return $return;
	}


	// http API연동 GET방식..
	public function curl_get($url, $post_data, $headers)
	{

		$url = $url . "?" . http_build_query($post_data);

		$ch = curl_init();

		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch,CURLOPT_HEADER, false);
		curl_setopt($ch,CURLOPT_HTTPHEADER, $headers); //header 지정하기

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);


		$res = str_replace("\r", "", str_replace("\n", "", $res));

		$return_data["httpcode"] = $httpcode;
		$return_data["body"] = $res;
		return $return_data;
	}

	// http API연동 PATCH방식..
	public function curl_put($url, $post_data, $headers)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //header 지정하기
//		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // patch, put, delete 등
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data, JSON_UNESCAPED_UNICODE)); // Post 값  Get 방식처럼적는다.


		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo "httpcode : ". $httpcode;
		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));

		$return["httpcode"] = $httpcode;
		$return["body"] = $res;

		return $return;
	}

// http API연동 DELETE방식..
	public function curl_delete($url, $post_data, $headers)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //header 지정하기
//		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // patch, put, delete 등
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); // Post 값  Get 방식처럼적는다.

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo "httpcode : ". $httpcode;
		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));

		$return["httpcode"] = $httpcode;
		$return["body"] = $res;

		return $return;
	}

}
?>