<?
class Response {
	
	//주문등록
	public function order_insert($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);

		if ( $json_array["agent_code"] == "kakao_mo" )
		{
			$url = const_json(ADMIN_URL_KAKAO, $json_array["admin_code"]."_insert");

			$return = $this->curl_post($url, $post_data);
		}
		else
		{
			$url = const_json(ADMIN_URL, $json_array["admin_code"]."_insert");

			if ( $json_array["admin_code"] == "jisan" )
			{
				$return = $this->curl_post_jisan($url, $post_data);
			}
			else
			{
				$return = $this->curl_post($url, $post_data);
			}
		}

		$return_json = json_decode($return, true);

		return $return_json;
	}
		
	//주문등록(회차)
	public function order_insert_around($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);


		$url = const_json(ADMIN_URL, $json_array["admin_code"]."_insert_around");

		if ( $json_array["admin_code"] == "jisan" )
		{
			$return = $this->curl_post_jisan($url, $post_data);
		}
		else
		{
			$return = $this->curl_post($url, $post_data);
		}

		$return_json = json_decode($return, true);

		return $return_json;
	}
		
	//주문등록(회차)
	public function order_block_day($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);


		$url = const_json(ADMIN_URL, $json_array["admin_code"]."_order_block_day");

		if ( $json_array["admin_code"] == "jisan" )
		{
			$return = $this->curl_post_jisan($url, $post_data);
		}
		else
		{
			$return = $this->curl_post($url, $post_data);
		}

		$return_json = json_decode($return, true);

		return $return_json;
	}		

	//주문등록(회차)
	public function order_block_select($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);


		$url = const_json(ADMIN_URL, $json_array["admin_code"]."_order_block_select");

		if ( $json_array["admin_code"] == "jisan" )
		{
			$return = $this->curl_post_jisan($url, $post_data);
		}
		else
		{
			$return = $this->curl_post($url, $post_data);
		}

		$return_json = json_decode($return, true);

		return $return_json;
	}

	//주문조회(건별)
	public function order_info($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);

		$url = const_json(ADMIN_URL, $json_array["admin_code"]."_info");

		if ( $json_array["admin_code"] == "jisan" )
		{
			$return = $this->curl_post_jisan($url, $post_data);
		}
		else
		{
			$return = $this->curl_post($url, $post_data);
		}

		$return_json = json_decode($return, true);

		return $return_json;
	}

	//주문조회(일자별)
	public function order_info_date($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);

		$url = const_json(ADMIN_URL, $json_array["admin_code"]."_info_date");

		if ( $json_array["admin_code"] == "jisan" )
		{
			$return = $this->curl_post_jisan($url, $post_data);
		}
		else
		{
			$return = $this->curl_post($url, $post_data);
		}

		$return_json = json_decode($return, true);

		return $return_json;
	}

	//주문취소
	public function order_cancel($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);


		if ( $json_array["agent_code"] == "kakao_mo" )
		{
			$url = const_json(ADMIN_URL_KAKAO, $json_array["admin_code"]."_cancel");

			$return = $this->curl_post($url, $post_data);
		}
		else
		{
			$url = const_json(ADMIN_URL, $json_array["admin_code"]."_cancel");

			if ( $json_array["admin_code"] == "jisan" )
			{
				$return = $this->curl_post_jisan($url, $post_data);
			}
			else
			{
				$return = $this->curl_post($url, $post_data);
			}
		}

		$return_json = json_decode($return, true);

		return $return_json;
	}

	//주문취소
	public function order_cancel_kamo($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);

		$url = const_json(ADMIN_URL_KAKAO, $json_array["admin_code"]."_cancel");

		$return = $this->curl_post($url, $post_data);

		$return_json = json_decode($return, true);

		return $return_json;
	}

	//주문취소
	public function order_cancel_kamo_test($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);

		$url = const_json(ADMIN_URL_KAKAO, $json_array["admin_code"]."_cancel_test");

		$return = $this->curl_post($url, $post_data);

		$return_json = json_decode($return, true);

		return $return_json;
	}

	public function order_use($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);

		$url = const_json(ADMIN_URL, $json_array["admin_code"]."_use");


		if ( $json_array["admin_code"] == "jisan" )
		{
			$return = $this->curl_post_jisan($url, $post_data);
		}
		else
		{
			$return = $this->curl_post($url, $post_data);
		}

		$return_json = json_decode($return, true);

		return $return_json;
	}

	//주문취소
	public function order_unuse($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);

		$url = const_json(ADMIN_URL, $json_array["admin_code"]."_unuse");

		if ( $json_array["admin_code"] == "jisan" )
		{
			$return = $this->curl_post_jisan($url, $post_data);
		}
		else
		{
			$return = $this->curl_post($url, $post_data);
		}

		$return_json = json_decode($return, true);

		return $return_json;
	}


	//주문취소
	public function order_restored($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);

		$url = const_json(ADMIN_URL, $json_array["admin_code"]."_restored");

		if ( $json_array["admin_code"] == "jisan" )
		{
			$return = $this->curl_post_jisan($url, $post_data);
		}
		else
		{
			$return = $this->curl_post($url, $post_data);
		}

		$return_json = json_decode($return, true);

		return $return_json;
	}

	//알림톡 재발송
	public function kakao_resend($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);

		$url = const_json(ADMIN_URL, $json_array["admin_code"]."_kakao_resend");

		if ( $json_array["admin_code"] == "jisan" )
		{
			$return = $this->curl_post_jisan($url, $post_data);
		}
		else
		{
			$return = $this->curl_post($url, $post_data);
		}

		$return_json = json_decode($return, true);

		return $return_json;
	}

	public function order_complete($json_array)
	{
		$post_data["order_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);

		$url = const_json(ADMIN_URL_KAKAO, $json_array["admin_code"]."_complete");

		$return = $this->curl_post($url, $post_data);
//		exit;
		$return_json = json_decode($return, true);

		return $return_json;

	}

	// http API연동 POST방식..
	public function curl_post($URL, $POST_DATA = NULL, array $options = array())
	{
		if( $POST_DATA != NULL )
		{
			$defaults = array(
					CURLOPT_POST => 1,
					CURLOPT_HEADER => 0,
					CURLOPT_URL => $URL,
					CURLOPT_FRESH_CONNECT => 1,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_FORBID_REUSE => 1,
					CURLOPT_TIMEOUT => 15,
					CURLOPT_POSTFIELDS => http_build_query($POST_DATA)
			);
		}
		else
		{
			$defaults = array(
					CURLOPT_POST => 1,
					CURLOPT_HEADER => 0,
					CURLOPT_URL => $URL,
					CURLOPT_FRESH_CONNECT => 1,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_FORBID_REUSE => 1,
					CURLOPT_TIMEOUT => 15
			);
		}

		$ch = curl_init();
		curl_setopt_array($ch, ($options + $defaults));

		$result = curl_exec($ch);

		curl_close($ch);

		$result = str_replace("\r", "", str_replace("\n", "", $result));

		return $result;
	}

		// http API연동 POST방식..
	public function curl_post_jisan($URL, $POST_DATA = NULL, array $options = array())
	{
		if( $POST_DATA != NULL )
		{
			$defaults = array(
					CURLOPT_POST => 1,
					CURLOPT_HEADER => 0,
					CURLOPT_URL => $URL,
					CURLOPT_FRESH_CONNECT => 1,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_FORBID_REUSE => 1,
					CURLOPT_TIMEOUT => 15,
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_SSLVERSION => 1,
					CURLOPT_POSTFIELDS => http_build_query($POST_DATA)
			);
		}
		else
		{
			$defaults = array(
					CURLOPT_POST => 1,
					CURLOPT_HEADER => 0,
					CURLOPT_URL => $URL,
					CURLOPT_FRESH_CONNECT => 1,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_FORBID_REUSE => 1,
					CURLOPT_TIMEOUT => 15
			);
		}

		$ch = curl_init();
		curl_setopt_array($ch, ($options + $defaults));

		$result = curl_exec($ch);

		curl_close($ch);

		$result = str_replace("\r", "", str_replace("\n", "", $result));

		return $result;
	}
}
?>