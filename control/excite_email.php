<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$user_info = kekezu::get_user_info($excite_uid);
if($user_info){
	if($user_info['status']=='3'){
		$md5_code = md5($user_info['uid'].','.$user_info['username'].','.$user_info['email']);
		if($md5_code==$excite_code){
			$res = db_factory::execute(sprintf("update %switkey_space set status='1' where uid='%d'",TABLEPRE,$excite_uid));
			$res and kekezu::show_msg($_lang['operate_notice'],'index.php?do=login',3,$_lang['your_username_has_activation_seccess'],'success') or kekezu::show_msg($_lang['operate_notice'],'index.php',3,$_lang['your_username_activation_fail'],'warning');
		}else{
			kekezu::show_msg($_lang['operate_notice'],'index.php',3,$_lang['user_activation_number_error'],'warning');
		}
	}else{
		kekezu::show_msg($_lang['operate_notice'],'index.php',3,$_lang['your_username_activated_not_repeat'],'warning');
	}
}else{
	kekezu::show_msg($_lang['operate_notice'],'index.php',3,$_lang['not_exist_wait_activation_user'],'warning');
}