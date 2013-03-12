<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$pic_id and $pic_id= intval($pic_id);
$third_nav=array('choose'=>$_lang['select_head_pic'],'upload'=>$_lang['upload_head_pic']);
if($kekezu->_sys_config['user_intergration']==2){
	unset($third_nav['choose']);
	$show="upload";
	include 'keke_client/ucenter/client.php';
	$user_swf =uc_avatar($uid);
}else{
	$show or $show="choose";
	$user_swf =keke_user_avatar_class::avatar_html($uid);
}
for($i=1;$i<21;$i++){
   $sys_pic[$i] =  $i;
}
if($ac=='set_pic'){
	$url = "index.php?do=$do&view=$view&op=$op&show=$show";
    abs(intval($pic_id)) and   $id = keke_user_avatar_class::set_user_sys_pic($uid, $pic_id);
    if($id){
    	$kekezu->_cache_obj->del ( "keke_witkey_member_ext" );
    	kekezu::show_msg ( $_lang['system prompt'], $url , '1', $_lang['submit success'], 'alert_right' ) ;
    }else{
    	kekezu::show_msg ( $_lang['system prompt'], $url , '1', $_lang['submit failure'], 'alert_error' )  ;
    }
}
require keke_tpl_class::template ( "user/" . $do . "_" . $op );
