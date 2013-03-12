<?php

defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$nav_active_index = 'task';
$basic_url = 'index.php?do=task&task_id=' . $task_id;
$task_obj = match_task_class::get_instance ( $task_info );
$model_id = $task_info ['model_id'];
$task_info = $task_obj->_task_info;
$cover_id = $task_obj->_task_info['task_cash_coverage'];
$cover_cash = kekezu::get_cash_cove('',true);
//时间类处理
$cash_cove = $task_obj->_cash_cove;
//match_report_class::process_rights(array('action'=>'nopass'));
$process_can = $task_obj->process_can (); //可操作按钮
$process_desc = $task_obj->process_desc (); //按钮文字
$status_arr = $task_obj->get_task_status (); //任务状态数组
$task_status = $task_obj->_task_status; //当前任务状态
$task_config = $task_obj->_task_config; //任务配置
$task_obj->plus_view_num (); //查看加一
$stage_desc = $task_obj->get_task_stage_desc (); //任务阶段样式
$time_desc = $task_obj->get_task_timedesc (); //任务时间描述
$related_task = $task_obj->get_task_related (); //获取相关任务
$browing_history = $task_obj->browing_history ( $task_id, $cash_cove, $task_info ['task_title'] );
$show_payitem = $task_obj->show_payitem ();
$wiki_info = $task_obj->work_exists (); //抢标威客信息
$guid      = $task_info['uid'];
$g_info = $task_obj->_g_userinfo;
$wuid      = intval($wiki_info['uid']);
$wuid and $w_info = kekezu::get_user_info($wuid);
switch ($op) {
	case 'message' :
		$title = $_lang ['send_msg'];
		if ($sbt_edit) {
			$task_obj->send_message ( $title, $tar_content, $to_uid, $to_username, '', 'json' );
		}
		require keke_tpl_class::template ( 'message' );
		die ();
		break;
	case 'reqedit' :
        if($task_info['ext_desc']){
		$title = $_lang['edit_supply_demand'];
		}else{
		$title =$_lang['supply_demand'];
		}
		if ($sbt_edit) {
			$task_obj->set_task_reqedit ( $tar_content, '', 'json' );
		}
		$ext_desc = $task_info ['ext_desc'];
		require keke_tpl_class::template ( 'task/task_reqedit' );
		die ();
		break;
	case "work_hand" : //威客抢标
		$title = $_lang['match_high_bid'];
		if ($sbt_edit) {
			$task_obj->work_bid ( $con, '', 'json' );
		}
		$consume = kekezu::get_cash_consume($task_config['deposit']);
		$m_handed = $task_obj->work_exists('',"uid='{$uid}'",-1);
		require keke_tpl_class::template ("task/match/tpl/" . $_K ['template'] . '/match_work');
		die ();
		break;
	case "work_give_up"://放弃投标
		$task_obj->work_give_up('','json');
		break;
	case "work_cancel"://淘汰投标
		$task_obj->work_cancel('','json');
		break;
	case "task_host" : //托管
		$title = $_lang['match_task_host'];
		if($sbt_edit){
			$task_obj->task_host ($host_cash,'', 'json' );
		}
		$limit = $task_obj->_host_half;
		require keke_tpl_class::template ("task/match/tpl/" . $_K ['template'] . '/match_work');
		die ();
		break;
	case "work_start" : //接受。开始工作
		$task_obj->work_start('', 'json' );
		break;
	case "work_over"://确认完工
		$title = $_lang['match_work_over'];
		if($sbt_edit){
			$task_obj->work_over($tar_content, $file_id,$file_name,intval($modify),'','json');
		}
		require keke_tpl_class::template ("task/match/tpl/" . $_K ['template'] . '/match_work');
		die ();
		break;
	case "task_accept"://工作验收
		$task_obj->task_accept('','json');
		break;
	case "send_notice"://发送提醒
		$task_obj->send_notice($type,'','json');
		break;
	case "get_contact" : //获取联系方式
		$title = $_lang['match_get_contact'];
		if($uid==$task_obj->_guid){
			$match_info = $task_obj->get_match_work($wiki_info['work_id']);
			$contact = unserialize($match_info['witkey_contact']);
		}else{
			$contact['mobile'] = $task_info['contact'];$type=2;
			$contact['qq'] = $task_obj->_g_userinfo['qq'];
			$contact['email'] = $task_obj->_g_userinfo['email'];
			$contact['msn'] = $task_obj->_g_userinfo['msn'];
		}
		require keke_tpl_class::template ("task/match/tpl/" . $_K ['template'] . '/match_work');
		die ();
		break;
	case "mark" : //评价
		$title = $_lang ['each_mark'];
		$model_code = $task_obj->_model_code;
		require S_ROOT . 'control/mark.php';
		die ();
		break;
	case "work_del" : //稿件删除
		$task_obj->del_work ( $work_id, '', 'json' );
		break;
	case "comment" : //相关留言
		switch ($obj_type) {
			case "task" :
				break;
			case "work" :
				$tar_content and $task_obj->set_work_comment ( $obj_type, $obj_id, $tar_content, $p_id, '', 'json' );
				break;
		}
		break;
	case "report" : //举报、投诉
		$transname = keke_report_class::get_transrights_name ( $type );
		$title = $transname . $_lang ['submit'];
		if ($sbt_edit) {
			$task_obj->set_report ( $obj, $obj_id, $to_uid, $to_username, $type, $file_url, $tar_content );
		}
		require keke_tpl_class::template ( "report" );
		die ();
		break;
}

switch ($view) {
	case "work" :
		$search_condit = $task_obj->get_search_condit ();
		$work_status = $task_obj->get_work_status ();
		intval ( $page ) and $p ['page'] = intval ( $page ) or $p ['page'] = '1';
		intval ( $page_size ) and $p ['page_size'] = intval ( $page_size ) or $p ['page_size'] = '10';
		$p ['url'] = $basic_url . "&view=work&ut=$ut&page_size=" . $p ['page_size'] . "&page=" . $p ['page'];
		$p ['anchor'] = '#work_list';
		$w ['work_id'] = $work_id; //稿件编号
		$w ['work_status'] = $st; //稿件状态
		$w ['user_type'] = $ut; //用户类型  my自己
		$work_arr = $task_obj->get_work_info ( $w, " work_id asc ", $p ); //稿件信息
		$pages = $work_arr ['pages'];
		$work_info = $work_arr ['work_info'];
		$mark = $work_arr ['mark'];
		///*检测是否有新留言**/
		$has_new = $task_obj->has_new_comment ( $p ['page'], $p ['page_size'] );
		break;
	case "comment" :
		$comment_obj = keke_comment_class::get_instance ( 'task' );
		$url = $basic_url . "&view=comment";
		intval ( $page ) or $page = 1;
		$comment_arr = $comment_obj->get_comment_list ( $task_id, $url, $page );
		$comment_data = $comment_arr ['data'];
		$comment_page = $comment_arr ['pages'];
		$reply_arr = $comment_obj->get_reply_info ( $task_id );
		
		switch ($op) {
			case "reply" : //回复任务留言
				$comment_arr = array ("obj_id" => $task_id, "origin_id" => $task_id, "obj_type" => "task", "p_id" => $pid, "uid" => $uid, "username" => $username, "content" => $content, "on_time" => time () );
				$res = $comment_obj->save_comment ( $comment_arr, $task_id, 1 );
				if ($res != 3 && $res != 2) {
					$v1 = $comment_obj->get_comment_info ( $res );
					$tmp = 'replay_comment';
					require keke_tpl_class::template ( "task/task_comment_reply" );
				} else {
					echo $res;
				}
				die ();
				break;
			case "add" : //添加任务留言 
				$comment_arr = array ("obj_id" => $task_id, "origin_id" => $task_id, "obj_type" => "task", "uid" => $uid, "username" => $username, "content" => $content, "on_time" => time () );
				$res = $comment_obj->save_comment ( $comment_arr, $task_id );
				if ($res != 3 && $res != 2) {
					$v = $comment_obj->get_comment_info ( $res );
					$tmp = 'pub_comment';
					require keke_tpl_class::template ( "task/task_comment_reply" );
				} else {
					echo $res;
				}
				die ();
				break;
			case "del" :
				$comment_info = $comment_obj->get_comment_info ( $comment_id );
				if ($uid == ADMIN_UID || $user_info ['group_id'] == 7) {
					//更新个人信息 
					$res = $comment_obj->del_comment ( $comment_id, $task_id, $comment_info ['p_id'] );
				} else {
					kekezu::keke_show_msg ( "", $_lang ['not_priv'], "error", "json" );
				}
				$res and kekezu::keke_show_msg ( "", $_lang ['delete_success'], "", "json" ) or kekezu::keke_show_msg ( "", $_lang ['system_is_busy'], "error", "json" );
				break;
		}
		break;
	case "mark" :
		$mark_count = $task_obj->get_mark_count (); //评价统计
		intval ( $page ) and $p ['page'] = intval ( $page ) or $p ['page'] = '1';
		intval ( $page_size ) and $p ['page_size'] = intval ( $page_size ) or $p ['page_size'] = '10';
		$p ['url'] = $basic_url . "&view=mark&page_size=" . $p ['page_size'] . "&page=" . $p ['page'];
		$p ['anchor'] = '';
		$w ['model_code'] = $model_code; //互评模型
		$w ['origin_id'] = $task_id; //互评源 task_id
		$w ['mark_status'] = $st; //评价状态
		//$ut=='my' and $w['uid'] = $uid;//我的评价
		$w ['mark_type'] = $ut; //来自的评论
		$mark_arr = keke_user_mark_class::get_mark_info ( $w, $p, ' mark_id desc ', "mark_status>0" );
		$mark_info = $mark_arr ['mark_info'];
		$pages = $mark_arr ['pages'];
		break;
	
	case "base" :
	default :
		$task_file = $task_obj->get_task_file ();
		$kekezu->init_prom ();
		$can_prom = $kekezu->_prom_obj->is_meet_requirement ( "bid_task", $task_id );
		if($task_info['task_status']==2&&$task_info['uid']==$uid){
			$item_list= keke_payitem_class::get_payitem_config ( 'employer', null, null, 'item_id' );
		}
		break;
}
if($task_status==2){
	$cutclock   = max(0,-$task_obj->_cutduwn);//到时时间);
   // var_dump($task_obj->_cutduwn);
	$cutdown    =$cutclock+time();

}
 
require keke_tpl_class::template ( "task/" . $model_info ['model_code'] . "/tpl/" . $_K ['template'] . "/task_info" );