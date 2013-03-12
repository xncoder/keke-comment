<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$page_title=$_lang['login'].'- '.$_K['html_title'];
$uid and header ( "location:index.php" ); 
$open_api_arr = $kekezu->_api_open;
$api_name = keke_glob_class::get_open_api();
$inter = $kekezu->_sys_config ['user_intergration'];
$login_times = intval($_SESSION['login_times']);
if (kekezu::submitcheck(isset($formhash))|| isset($login_type) ==3) {
	 isset($hdn_refer) and $_K['refer'] = $hdn_refer;  
	 isset($_COOKIE['kekeloginrefer']) and $_K['refer'] =  $_COOKIE['kekeloginrefer'];
	 $txt_code = isset($txt_code)?$txt_code:"";
	 $login_type = isset($login_type)?$login_type:"";
	 $ckb_cookie = isset($ckb_cookie)?$ckb_cookie:"";
	$login_obj = new keke_user_login_class();
 	$user_info = $login_obj->user_login($txt_account, md5($pwd_password),$txt_code,$login_type); 
	$login_obj->save_user_info($user_info, $ckb_cookie,$login_type); 
}
require  keke_tpl_class::template ( $do );