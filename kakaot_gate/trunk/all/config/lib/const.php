<?
	//관리자(CMS) 코드
    define("ADMIN_CHECK", json_encode(array(
											"websen"		=> "websen",
											"zipline"		=> "zipline",
											"kf"			=> "kf",
											"qpos"			=> "qpos",
											"momo"			=> "momo",
											"radical"		=> "radical",
											"graminside"	=> "graminside",
											"playtika"		=> "playtika",
											"vango"			=> "vango",
											"jamsa"			=> "jamsa",
											"eanland"		=> "eanland",
											"taebaek"		=> "taebaek",
											"jisan"			=> "jisan",
											"kamo"			=> "kamo",
											"boyang"		=> "boyang",
											"doja"			=> "doja",
											"tb_cms"		=> "tb_cms",
											"phoenix"		=> "phoenix",
											"phoenixhnr"	=> "phoenixhnr",
											"yy_cms"		=> "yy_cms"

											), JSON_UNESCAPED_UNICODE)
		  );
	
	//관리자(CMS) URL
    define("ADMIN_URL", json_encode(array(
											"websen_insert"			=> "",
											"websen_info"			=> "",
											"websen_info_date"		=> "",
											"websen_cancel"			=> "",
											"websen_kakao_resend"	=> "",
											"websen_use"			=> "",
											"websen_unuse"		=> "",
											"websen_insert_around"	=> "",
											"websen_order_block_day"	=> "",
											"websen_order_block_select"	=> "",

											"zipline_insert"		=> "",
											"zipline_info"			=> "",
											"zipline_info_date"		=> "",
											"zipline_cancel"		=> "",
											"zipline_kakao_resend"	=> "",
											"zipline_use"			=> "",
											"zipline_unuse"		=> "",
											"zipline_insert_around"	=> "",
											"zipline_order_block_day"	=> "",
											"zipline_order_block_select"	=> "",

											"kf_insert"			=> "",
											"kf_info"			=> "",
											"kf_info_date"		=> "",
											"kf_cancel"			=> "",
											"kf_kakao_resend"	=> "",
											"kf_use"			=> "",
											"kf_unuse"		=> "",
											"kf_insert_around"	=> "",
											"kf_order_block_day"	=> "",
											"kf_order_block_select"	=> "",
											"kf_restored"		=> "",

											"qpos_insert"		=> "",
											"qpos_info"			=> "api/gate/order_info.php",
											"qpos_info_date"	=> "api/gate/order_info_date.php",
											"qpos_cancel"		=> "api/gate/order_cancel.php",
											"qpos_kakao_resend"	=> "api/gate/kakao_resend.php",
											"qpos_use"			=> "api/gate/order_use.php",
											"qpos_unuse"		=> "api/gate/order_unuse.php",
											"qpos_insert_around"	=> "api/gate/order_insert_around.php",
											"qpos_order_block_day"	=> "api/gate/order_block_day.php",
											"qpos_order_block_select"	=> "api/gate/order_block_select.php",
											"qpos_restored"		=> "api/gate/order_restored.php",


											"momo_insert"		=> "/api/gate/order_insert.php",
											"momo_info"			=> "/api/gate/order_info.php",
											"momo_info_date"	=> "/api/gate/order_info_date.php",
											"momo_cancel"		=> "/api/gate/order_cancel.php",
											"momo_kakao_resend"	=> "/api/gate/kakao_resend.php",

											"radical_insert"		=> "/api/gate/order_insert.php",
											"radical_info"			=> "/api/gate/order_info.php",
											"radical_info_date"	=> "/api/gate/order_info_date.php",
											"radical_cancel"		=> "/api/gate/order_cancel.php",
											"radical_kakao_resend"	=> "/api/gate/kakao_resend.php",

											"graminside_insert"			=> "/api/gate/order_insert.php",
											"graminside_info"			=> "/api/gate/order_info.php",
											"graminside_info_date"		=> "/api/gate/order_info_date.php",
											"graminside_cancel"			=> "/api/gate/order_cancel.php",
											"graminside_kakao_resend"	=> "/api/gate/kakao_resend.php",

											"playtika_insert"			=> "/api/gate/order_insert.php",
											"playtika_info"				=> "/api/gate/order_info.php",
											"playtika_info_date"		=> "/api/gate/order_info_date.php",
											"playtika_cancel"			=> "/api/gate/order_cancel.php",
											"playtika_kakao_resend"		=> "/api/gate/kakao_resend.php",

											"vango_insert"				=> "/api/gate/order_insert.php",
											"vango_info"				=> "/api/gate/order_info.php",
											"vango_info_date"			=> "/api/gate/order_info_date.php",
											"vango_cancel"				=> "/api/gate/order_cancel.php",
											"vango_kakao_resend"		=> "/api/gate/kakao_resend.php",

											"jamsa_insert"				=> "/api/gate/order_insert.php",
											"jamsa_info"				=> "/api/gate/order_info.php",
											"jamsa_info_date"			=> "/api/gate/order_info_date.php",
											"jamsa_cancel"				=> "/api/gate/order_cancel.php",
											"jamsa_kakao_resend"		=> "/api/gate/kakao_resend.php",

											"eanland_insert"			=> "/api/gate/order_insert.php",
											"eanland_info"				=> "/api/gate/order_info.php",
											"eanland_info_date"			=> "/api/gate/order_info_date.php",
											"eanland_cancel"			=> "/api/gate/order_cancel.php",
											"eanland_kakao_resend"		=> "/api/gate/kakao_resend.php",

											"taebaek_insert"			=> "/api/gate/order_insert.php",
											"taebaek_info"				=> "/api/gate/order_info.php",
											"taebaek_info_date"			=> "/api/gate/order_info_date.php",
											"taebaek_cancel"			=> "/api/gate/order_cancel.php",
											"taebaek_kakao_resend"		=> "/api/gate/kakao_resend.php",

											"jisan_insert"			=> "/api/gate/order_insert.php",
											"jisan_info"				=> "/api/gate/order_info.php",
											"jisan_info_date"			=> "/api/gate/order_info_date.php",
											"jisan_cancel"			=> "/api/gate/order_cancel.php",
											"jisan_kakao_resend"		=> "/api/gate/kakao_resend.php",

											"kamo_insert"		=> "/api/gate/order_insert.php",
											"kamo_info"			=> "/api/gate/order_info.php",
											"kamo_info_date"	=> "/api/gate/order_info_date.php",
											"kamo_cancel"		=> "/api/gate/order_cancel.php",
											"kamo_kakao_resend"	=> "/api/gate/kakao_resend.php",
											"kamo_use"			=> "/api/gate/order_use.php",
											"kamo_unuse"		=> "/api/gate/order_unuse.php",
											"kamo_insert_around"	=> "/api/gate/order_insert_around.php",
											"kamo_order_block_day"	=> "/api/gate/order_block_day.php",
											"kamo_order_block_select"	=> "/api/gate/order_block_select.php",

											"boyang_insert"			=> "/api/gate/order_insert.php",
											"boyang_info"			=> "/api/gate/order_info.php",
											"boyang_info_date"		=> "/api/gate/order_info_date.php",
											"boyang_cancel"			=> "/api/gate/order_cancel.php",
											"boyang_kakao_resend"	=> "/api/gate/kakao_resend.php",
											"boyang_use"			=> "/api/gate/order_use.php",
											"boyang_unuse"			=> "/api/gate/order_unuse.php",

											"tb_cms_insert"			=> "/api/gate/order_insert.php",
											"tb_cms_info"			=> "/api/gate/order_info.php",
											"tb_cms_info_date"		=> "/api/gate/order_info_date.php",
											"tb_cms_cancel"			=> "/api/gate/order_cancel.php",
											"tb_cms_kakao_resend"	=> "/api/gate/kakao_resend.php",
											"tb_cms_use"			=> "/api/gate/order_use.php",
											"tb_cms_unuse"			=> "/api/gate/order_unuse.php",

											"phoenix_insert"		=> "/api/gate/order_insert.php",
											"phoenix_info"			=> "/api/gate/order_info.php",
											"phoenix_info_date"		=> "/api/gate/order_info_date.php",
											"phoenix_cancel"		=> "/api/gate/order_cancel.php",
											"phoenix_kakao_resend"	=> "/api/gate/kakao_resend.php",
											
											"phoenixhnr_insert"			=> "api/gate/order_insert.php",
											"phoenixhnr_info"			=> "api/gate/order_info.php",
											"phoenixhnr_info_date"		=> "api/gate/order_info_date.php",
											"phoenixhnr_cancel"			=> "api/gate/order_cancel.php",
											"phoenixhnr_kakao_resend"	=> "api/gate/kakao_resend.php",

											"yy_cms_insert"			=> "/api/gate/order_insert.php",
											"yy_cms_info"			=> "/api/gate/order_info.php",
											"yy_cms_info_date"		=> "/api/gate/order_info_date.php",
											"yy_cms_cancel"			=> "/api/gate/order_cancel.php",
											"yy_cms_kakao_resend"	=> "/api/gate/kakao_resend.php"
											
										  ), JSON_UNESCAPED_UNICODE)
		  );

	//관리자(CMS) URL
    define("ADMIN_URL_KAKAO", json_encode(array(
											"qpos_insert"		=> "api/gate/order_insert_kakao.php",
											"qpos_complete"		=> "api/gate/order_complete.php",
											"qpos_cancel"		=> "api/gate/order_cancel_kamo.php",

											"kamo_insert"		=> "/api/gate/order_insert_kakao.php",
											"kamo_complete"		=> "/api/gate/order_complete.php",
											"kamo_cancel"		=> "/api/gate/order_cancel_kamo.php",
											"kamo_cancel_test"		=> "/api/gate/order_cancel_kamo_test.php",
											
											"websen_insert"		=> "/api/gate/order_insert_kakao.php",
											"websen_complete"		=> "/api/gate/order_complete.php",
											"websen_cancel"		=> "/api/gate/order_cancel_kamo.php",
											"websen_cancel_test"		=> "/api/gate/order_cancel_kamo_test.php",
											
											"zipline_insert"		=> "/api/gate/order_insert_kakao.php",
											"zipline_complete"		=> "/api/gate/order_complete.php",
											"zipline_cancel"		=> "/api/gate/order_cancel_kamo.php",
											"zipline_cancel_test"		=> "/api/gate/order_cancel_kamo_test.php",

											"boyang_insert"			=> "/api/gate/order_insert_kakao.php",
											"boyang_complete"		=> "/api/gate/order_complete.php",
											"boyang_cancel"			=> "/api/gate/order_cancel_kamo.php",
											"boyang_cancel_test"	=> "/api/gate/order_cancel_kamo_test.php",

											"kf_insert"			=> "/api/gate/order_insert_kakao.php",
											"kf_complete"		=> "/api/gate/order_complete.php",
											"kf_cancel"			=> "/api/gate/order_cancel_kamo.php",
											"kf_cancel_test"	=> "/api/gate/order_cancel_kamo_test.php",

											"playtika_insert"			=> "/api/gate/order_insert_kakao.php",
											"playtika_complete"		=> "/api/gate/order_complete.php",
											"playtika_cancel"			=> "/api/gate/order_cancel_kamo.php",
											"playtika_cancel_test"	=> "/api/gate/order_cancel_kamo_test.php",

											"tb_cms_insert"			=> "/api/gate/order_insert_kakao.php",
											"tb_cms_complete"		=> "/api/gate/order_complete.php",
											"tb_cms_cancel"			=> "/api/gate/order_cancel_kamo.php",
											"tb_cms_cancel_test"	=> "/api/gate/order_cancel_kamo_test.php",
											
											"phoenix_insert"		=> "/api/gate/order_insert_kakao.php",
											"phoenix_complete"		=> "/api/gate/order_complete.php",
											"phoenix_cancel"		=> "/api/gate/order_cancel_kamo.php",
											
											"phoenixhnr_insert"		=> "api/gate/order_insert_kakao.php",
											"phoenixhnr_complete"	=> "api/gate/order_complete.php",
											"phoenixhnr_cancel"		=> "api/gate/order_cancel_kamo.php",

											
											"radical_insert"		=> "/api/gate/order_insert_kakao.php",
											"radical_complete"	=> "/api/gate/order_complete.php",
											"radical_cancel"		=> "/api/gate/order_cancel_kamo.php",

											"yy_cms_insert"		=> "/api/gate/order_insert_kakao.php",
											"yy_cms_complete"	=> "/api/gate/order_complete.php",
											"yy_cms_cancel"		=> "/api/gate/order_cancel_kamo.php"


										  ), JSON_UNESCAPED_UNICODE)
		  );
?>