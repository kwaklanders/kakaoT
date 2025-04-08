<?
class Response {


	public function order_insert($json_array)
	{

		$post_data["order_info"] = josn_encode($json_array, JSON_UNESCAPED_UNICODE);

		$url = const_json(ADMIN_URL, $json_array["admin_code"]."_insert");



	}



}