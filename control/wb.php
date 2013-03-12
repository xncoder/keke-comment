<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
if($wb_type && $focus){
	$url = $kekezu->_sys_config['website_url']."/index.php?do=$do&focus=$focus&wb_type=$wb_type";
	$weibo_obj = new keke_weibo_class($wb_type,$call_back,$url);
	if($weibo_obj->focus_by_uid($focus)){
		kekezu::show_msg($_lang['operate_notice'],"index.php",2,$_lang['focus_success'],'success');
	}else{
		kekezu::show_msg($_lang['operate_notice'],"index.php",20,$_lang['focus_exists'],"warning");
	}
}
die();