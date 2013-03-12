<?php
require_once '../comm_config.php';
require_once 'top/TopClient.php';
global $kekezu;
$c = new TopClient ();
$c->appkey = '12299577';
$c->secretKey = '9dd83e72678e558ee463e07f5769b59f';
/**
 * 返回数据
 * top_appkey;公钥
 * top_session返回用户session
 * sign。签名 签名规则为base64(md5(top_appkey+top_parameters+top_session+app_secret))
 * top_parameters上下文参数  base64(key1=value1&key2=value2……);
 * =>用户登录。返回 visitor_id和visitor_nick;
 * =>未登录。返回  visitor_role;
 */
$c->process_user_oauth($top_sign, $top_appkey, $top_parameters, $top_session);
if($c->_error){
	$kekezu->show_msg('操作提示',$_K['site_url'],3,$c->_error);
}

