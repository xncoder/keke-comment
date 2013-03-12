<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$uid!=$task_info['uid'] and kekezu::show_msg($_lang['friendly_notice'],'index.php?do=index',3,$_lang['cannot_access_page']); 
$payitem_arr = keke_payitem_class::get_payitem_info('employer',$model_list[$task_info['model_id']]['model_code']); 
$exist_payitem_arr = keke_payitem_class::payitem_exists($uid,false ,'',$payitem_arr);
$payitem_arr_desc = unserialize($task_info['payitem_time']);
$payitem_standard = keke_payitem_class::payitem_standard (); 
 foreach ($payitem_arr_desc as $k=>$v) { 
 	if($v>time()){
 		$sy_time_str = $v-time();
 		$sy_time_desc[$k] = kekezu::time2Units($sy_time_str);
 	}else{
 		$sy_time_desc[$k] = '0'.$_lang['day'];
 	} 
 }
require keke_tpl_class::template ( "payitem_tools" );
