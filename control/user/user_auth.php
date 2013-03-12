<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
keke_lang_class::package_init ( 'auth' );
keke_lang_class::loadlang ( 'auth_add' );  
$keys = array_keys ( $auth_item_list );
$auth_code or $auth_code = $keys ['0']; 
$auth_code or kekezu::show_msg ( $_lang['param_error'], "index.php?do=auth",3,'','warning' );
if($auth_item_list[$auth_code]){
	$auth_class = "keke_auth_".$auth_code."_class";
	$auth_obj = new $auth_class ( $auth_code ); 
	$auth_item = $auth_item_list [$auth_code]; 
	$auth_dir = $auth_item ['auth_dir']; 
	$auth_info = $auth_obj->get_user_auth_info ( $uid,0,$show_id); 
	require S_ROOT."./auth/$auth_code/control/auth_add.php";
}else{
	kekezu::show_msg($_lang['param_unlaw_or_no_open'],"index.php",3,'','warning');
}