<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$type or exit(kekezu::show_msg($_lang['oprerate_notice'],"index.php?do=login",2,$_lang['type_no_empty'],"warning"));
$page_title=$_lang['login'].'- '.$_K['html_title'];
$oa = new keke_oauth_login_class ( $type );
$api_name = keke_glob_class::get_open_api();
$login_obj = new keke_user_login_class ();
$oauth_obj = new Keke_witkey_member_oauth_class ();
$oauth_url = $kekezu->_sys_config ['website_url'] . "/index.php?do=oauth_register&type=$type";
$oauth_type_arr = keke_glob_class::get_oauth_type ();
if ($type && ! $_SESSION ['auth_' . $type] ['last_key']) {
	if ($type=='sina' && $error_code=='21330'){
		kekezu::show_msg($_lang['notice_message'], $kekezu->_sys_config ['website_url'].'/index.php?do=login',1,$_lang['login_in_fail'],"alert_right");
	}
	$oauth_vericode = $oauth_vericode;
	$oa->login ( $call_back, $oauth_url );
} else {
	$oauth_user_info = $oa->get_login_user_info ();
}
$taobao_user_id=$_SESSION ['auth_' . $type] ['last_key']['taobao_user_id'];
$nick=$_SESSION ['auth_' . $type] ['last_key']['nick'];
$bind_info = keke_register_class::is_oauth_bind ( $type, $oauth_user_info ['account'] );
if ($oauth_user_info && $bind_info) {
	$user_info = kekezu::get_user_info ( $bind_info ['uid'] );
	$login_user_info = $login_obj->user_login ( $user_info ['username'], $user_info ['password'], null, 1 );
	$login_obj->save_user_info ( $login_user_info, 1 );
}
if (kekezu::submitcheck($formhash)) {
	$login_user_info = $login_obj->user_login ( $txt_account,md5($pwd_password) , $txt_code, 1 );
	keke_register_class::register_binding ( $oauth_user_info, $login_user_info, $type );
	$login_obj->save_user_info ( $login_user_info, 1 );
}
require keke_tpl_class::template ( $do );