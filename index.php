<?php
define ( "IN_KEKE", TRUE );
include 'app_comm.php';

$task_open  = $kekezu->_task_open;
$shop_open  = $kekezu->_shop_open;
$dos = $kekezu->_route;
// 技巧，省略很多判断语句
(!empty($do)&& in_array($do, $dos)) and $do or (!$_GET&&!$_POST and $do=$kekezu->_sys_config['set_index'] or $do='index');

isset($m)&&$m=="user" and  $do ="avatar";
isset($_GET['apu'])&&intval($_GET['apu']) and keke_union_class::pub_redirect($apu);

// 载入语言包: lang\cn\index\register.php
keke_lang_class::package_init("index");
keke_lang_class::loadlang($do);
$kekezu->init_lang();

// 系统全局配置信息
$page_keyword 		= $kekezu->_sys_config['seo_keyword'];
$page_description 	= $kekezu->_sys_config ['seo_desc'];
$kf_phone 			= $kekezu->_sys_config['kf_phone'];

$uid 			= $kekezu->_uid;
$username 		= $kekezu->_username;
$messagecount 	= $kekezu->_messagecount;
$user_info 		= $kekezu->_userinfo;
$indus_p_arr 	= $kekezu->_indus_p_arr;
$indus_c_arr 	= $kekezu->_indus_c_arr;
$indus_arr   	= $kekezu->_indus_arr;
$model_list  	= $kekezu->_model_list;

$nav_arr 		= kekezu::nav_list($kekezu->_nav_list);
$lang_list 		= $kekezu->_lang_list;
$language      	= $kekezu->_lang;
$currency      	= $kekezu->_currency;
$curr_list     	= $kekezu->_curr_list;
$api_open   	= $kekezu->_api_open;
$weibo_list 	= $kekezu->_weibo_list;

$attent_api_open = $kekezu->_attent_api_open;
$attent_list 	 = $kekezu->_weibo_attent;
$style_path 	 = $kekezu->_style_path;
$style_path		 = SKIN_PATH;
$f_c_list 	 	 = keke_curren_class::get_curr_list('code,title');
$flink 			 = kekezu::get_table_data("link_id,link_name,link_url","witkey_link",""," link_id asc","","","",3600);

$log_account=null;
if(isset($_COOKIE['log_account'])){
	$log_account = $_COOKIE['log_account'];
}

kekezu::redirect_second_domain();
// controle/register.php
include S_ROOT . 'control/' . $do . '.php';
