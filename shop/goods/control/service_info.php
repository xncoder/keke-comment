<?php
defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$nav_active_index = 'shop';
$basic_url = $_K['siteurl']."/index.php?do=service&sid=".$sid;
$payitem_arr = unserialize($service_info['payitem_time']); 
$item_config = keke_payitem_class::get_payitem_config ( null, null, null, 'item_id' );
keke_shop_class::plus_view_num($sid, $owner_info['uid']);
if($uid&&$uid!==$owner_info['uid']){
 $buyer_order = db_factory::get_one("select a.order_status from ".TABLEPRE."witkey_order a left join ".TABLEPRE."witkey_order_detail b on a.order_id=b.order_id where b.obj_type='service' and b.obj_id=$sid and a.order_uid=$uid");
}
$seller_goods_num = db_factory::get_count(sprintf("select count(service_id) from %switkey_service where model_id=6 and uid=%d and service_status=2",TABLEPRE,$owner_info['uid']));
$shop_aid = keke_user_mark_class::get_user_aid ( $owner_info['uid'], 2, null, 1 );
switch ($op){
	case "report" : 
		$transname = keke_report_class::get_transrights_name($type);
		$title=$transname.$_lang['submit'];
		if($sbt_edit){
			$tar_content = kekezu::escape($tar_content);
			keke_shop_class::set_report ($obj_id, $to_uid,$to_username, $type, $file_url, $tar_content);
		}else{
			CHARSET=='gbk' and $to_username = kekezu::utftogbk($to_username);
			require keke_tpl_class::template("report");
		}
		die();
		break;
}
switch ($view) {
	case "sale" :
		$status_arr   = goods_shop_class::get_order_status();
		$sql =" select count(a.order_id) from %switkey_order a left join %switkey_order_detail b
				 on a.order_id=b.order_id where b.obj_id='$sid' and b.obj_type='service' 
				 and day(date(from_unixtime(a.order_time)))=day(curdate()) and a.order_status='confirm'";
		$today_sale   = db_factory::get_count(sprintf($sql,TABLEPRE,TABLEPRE,$sid));
		intval ( $page ) and $p ['page'] = intval ( $page ) or $p ['page']='1';
		intval ( $page_size ) and $p ['page_size'] = intval ( $page_size ) or $p['page_size']='10';
		$p['url'] = $basic_url."&view=sale&page_size=".$p ['page_size']."&page=".$p ['page'];
		$p ['anchor']	  = '#pageTop';
		$w=array();
		$w['a.order_status']="confirm";
		$t=='today'   and $ext_condit = 'day(date(from_unixtime(a.order_time)))=day(curdate())';
		$sale_arr    = keke_shop_class::get_sale_info($sid,$w,$p," a.order_time desc",$ext_condit);
		$sale_list   = $sale_arr['sale_info'];
		$pages      = $sale_arr['pages'];
		break;
	case "comment" :
		$comment_obj = keke_comment_class::get_instance('service'); 
		$url = $basic_url."&view=comment";
		intval($page) or $page = 1;
		$comment_arr = $comment_obj->get_comment_list($sid, $url, $page); 
		$comment_data = $comment_arr['data'];
		$comment_page = $comment_arr['pages'];
		$reply_arr = $comment_obj->get_reply_info($sid);	
	    switch ($op){
	    	case "reply": 
	    		$comment_arr = array("obj_id"=>$sid,"origin_id"=>$sid,"obj_type"=>"service","p_id"=>$pid,
	    		 "uid"=>$uid, "username"=>$username,"content"=>$content,"on_time"=>time()); 
	    		$res = $comment_obj->save_comment($comment_arr,$sid,1); 
	    		if($res!=3&&$res!=2){
	    			$v1 =  $comment_obj->get_comment_info($res);
	    			$tmp ='replay_comment';
	    			require keke_tpl_class::template ( "task/task_comment_reply" );
	    		}else{
	    			echo $res;
	    		}
	    		die();
	    		break;
	    	case "add": 
	    		$comment_arr = array("obj_id"=>$sid,"origin_id"=>$sid,"obj_type"=>"service",
	    		"uid"=>$uid, "username"=>$username,"content"=>$content,"on_time"=>time());
	    		$res = $comment_obj->save_comment($comment_arr,$sid); 
	    		if($res!=3&&$res!=2){
	    			$v = $comment_obj->get_comment_info($res);
	    			$tmp ='pub_comment';
	    			require keke_tpl_class::template ( "task/task_comment_reply" );
	    		}else{
	    			echo $res;
	    		}
	    		die();
	    		break;
	    	case "del": 
	    		$comment_info = $comment_obj->get_comment_info($comment_id);
	    		if( $uid ==ADMIN_UID||$user_info['group_id']==7){
	    			$res = $comment_obj->del_comment($comment_id,$sid,$comment_info['p_id']);
	    		}else{
	    			kekezu::keke_show_msg("", $_lang['do_not_have_access'],"error","json");
	    		}
	    		$res and kekezu::keke_show_msg("", $_lang['delete_success'],"","json") or kekezu::keke_show_msg("",$_lang['system_is_busy'],"error","json");
	    		break;	
	    } 
	    break;
	case "mark":
		$mark_count = keke_shop_class::get_mark_count($model_code,$sid);
		intval ( $page ) and $p ['page'] = intval ( $page ) or $p ['page']='1';
		intval ( $page_size ) and $p ['page_size'] = intval ( $page_size ) or $p['page_size']='10';
		$p['url'] = $basic_url."&view=mark&page_size=".$p ['page_size']."&page=".$p ['page'];
		$p ['anchor']	  = '#pageTop';
		$w['model_code']  = $model_code;
		$w['origin_id']   = $sid;
		$w['mark_status'] = $st;
		$w['mark_type'] = $ut;
		$mark_arr = keke_user_mark_class::get_mark_info($w,$p,' mark_id desc ','mark_status>0');
		$mark_info = $mark_arr['mark_info'];
		$pages     = $mark_arr['pages'];
		break;
}
$item_list= keke_payitem_class::get_payitem_config ( 'employer', 'goods', null, 'item_id' );
require keke_tpl_class::template ( "shop/" . $model_info ['model_code'] . "/tpl/" . $_K ['template'] . "/service_info" );