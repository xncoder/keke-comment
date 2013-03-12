<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
if ($sbt_mark) {
	$tar_content = kekezu::escape($tar_content);
	$aid = implode(",",array_keys($star));
	$aid_star = implode(",",array_values($star));
	$res = keke_user_mark_class::exec_mark($mark_id, $tar_content,$mark_status,$aid,$aid_star);
} else {
	$mark_arr = keke_user_mark_class::get_mark_info ( array ('model_code' => $model_code, 'obj_id' => $obj_id,'by_uid'=>$uid,'uid'=>$to_uid) );
	$mark_info = $mark_arr ['mark_info'] ['0'];
     $mark_info or   kekezu::show_msg($_lang['operate_notice'],"","",$_lang['mark_sya_busy_try_later'],"error"); 
	$aid_list = keke_user_mark_class::get_mark_aid ( $role_type );
    $aid_info=keke_user_mark_class::get_user_aid($mark_info['by_uid'],$mark_info['mark_type'],$mark_info['mark_status'],2,$mark_info['model_code'],$obj_id);
}
require keke_tpl_class::template ( "mark" );