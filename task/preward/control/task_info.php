<?php
defined('IN_KEKE') or exit('Access Denied');
$nav_active_index = 'task';
$basic_url = 'index.php?do=task&task_id='.$task_id;
$task_obj = preward_task_class::get_instance($task_info);
$task_info= $task_obj->_task_info;
$model_id = $task_info ['model_id'];
//时间类处理
$time_obj = new preward_time_class();
$time_obj->validtaskstatus();
// $task_obj->task_jg_timeout();
// $task_obj->task_xg_timeout();
//keke_task_class::hp_timeout();

$cover_cash = kekezu::get_cash_cove('',true);
$trust_mode=$task_obj->_trust_mode;//担保模式
$process_can = $task_obj->process_can();//可操作按钮
$process_desc = $task_obj->process_desc();//按钮文字
$status_arr = $task_obj->get_task_status();//任务状态数组
$task_status = $task_obj->_task_status;//当前任务状态
$delay_rule = $task_obj->_delay_rule;//延期规则
$task_config = $task_obj->_task_config;//任务配置
$delay_total = sizeof($delay_rule);//可延期次数
$delay_count=intval($task_info['is_delay']);//已延期次数
$task_obj->plus_view_num();//查看加一
$stage_desc = $task_obj->get_task_stage_desc (); //任务阶段样式
$wiki_cash = floatval($task_info['single_cash'])*(100-floatval($task_info['profit_rate']))/100 ; //中标威客实际所得
$time_desc = $task_obj->get_task_timedesc (); //任务时间描述
$related_task = $task_obj->get_task_related ();//获取相关任务
$if_can_hand = intval($task_obj->check_work_if_standard('hand'));//是否还可以交稿
$max_work_num = $task_obj->get_work_count('max');//可交稿件总数
$browing_history = $task_obj->browing_history($task_id,$task_info['task_cash']."元",$task_info['task_title']);
$show_payitem = $task_obj->show_payitem();
$sub_task_user_level =$g_info = $task_obj->_g_userinfo;
//威客实际中标金额
if($task_config['task_rate'] > 0){
	$cash = $task_info['task_cash'] * ( 1 - $task_config['task_rate']/100 ) ;
}else{
	$cash = $task_info['task_cash'];
}
switch ($op){
	case 'message':
		$title=$_lang['send_msg'];
		if($sbt_edit){
			$task_obj->send_message($title, $tar_content, $to_uid, $to_username,'','json');			
		}else{
			require keke_tpl_class::template('message');			
		}
		die();
		break;
	case 'reqedit':
       if($task_info['ext_desc']){
		$title = $_lang['edit_supply_demand'];
		}else{
		$title =$_lang['supply_demand'];
		}
		if ($sbt_edit) {
			$task_obj->set_task_reqedit ( $tar_content, '', 'json' );
		} else
			$ext_desc = $task_info ['ext_desc'];
		require keke_tpl_class::template ( 'task/task_reqedit' );
		die ();
		break;
	case "taskdelay" : //延期
		$title = $_lang['task_delay'];
		if($sbt_edit){
			$task_obj->set_task_delay($delay_day, $delay_cash,'','json');
		}else{
			$min_cash = intval($task_config['min_delay_cash']);//配置最小延期金额
			$max_day  = intval($task_config['max_delay']);//配置最大延期天数
			$this_min_cash = intval($delay_rule[$delay_count]['defer_rate']*$task_info['task_cash']/100);//本次最小延期金额
			$min_cash>$this_min_cash and $real_min = $min_cash or $real_min = $this_min_cash;//真正最小金额
			$credit_allow =  intval($kekezu->_sys_config ['credit_is_allow']);//金币开启
			require keke_tpl_class::template("task/task_delay");
		}		
		die();
		break;
	case "work_hand"://提交稿件
		$title=$_lang['hand_work'];
		if($sbt_edit){			
			$task_obj->work_hand($tar_content, $file_ids,$workhide,'','json');
		}else{			
			$workhide_exists = keke_payitem_class::payitem_exists($uid,'workhide','work');//可以隐藏交稿
			keke_payitem_class::payitem_cost ('payitem_cost', $uid, 'task', 'spend', $task_id, $task_id );
			
			require keke_tpl_class::template ( 'task/reward_work' );
		}		
		die();
		break;
	case "report"://举报、投诉
		$transname = keke_report_class::get_transrights_name($type);
		$title = $transname.$_lang['submit'];
		if($sbt_edit){
			$task_obj->set_report($obj, $obj_id, $to_uid, $to_username, $type, $file_url, $tar_content);
		}
			require keke_tpl_class::template("report");
		die();
		break;	
	case "work_choose"://选稿
		//echo 1;die;
		$task_obj->work_choose($work_id, $to_status,'','json');
		break;	
	case "mark" ://评价
		$title = $_lang['each_mark'];
		$model_code = $task_obj->_model_code;
		require S_ROOT.'control/mark.php';
		die();
		break;
	case "work_del"://稿件删除
		$task_obj->del_work($work_id,'','json');
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
}

switch ($view){
	case "work":
		$search_condit = $task_obj->get_search_condit();
		$work_status = $task_obj->get_work_status();
		intval ( $page ) and $p ['page'] = intval ( $page ) or $p ['page']='1';
		intval ( $page_size ) and $p ['page_size'] = intval ( $page_size ) or $p['page_size']=10;
		$p['url'] = $basic_url."&view=work&page_size=".$p ['page_size']."&page=".$p ['page'];
		if($st){
			$p['url'] .="&st=".$st;
		}
		if($ut){
			$p['url'] .="&ut=".$ut;
		}
		$p ['anchor'] = '#work_list';
		$w['work_id'] = $work_id;//稿件编号
		$w['work_status'] = $st;//稿件状态
		$w['user_type']   = $ut;//用户类型  my自己
		$work_arr = $task_obj->get_work_info ($w, " work_id asc ", $p ); //稿件信息
		$pages = $work_arr ['pages'];
		$work_info = $work_arr ['work_info'];	
		$mark      = $work_arr['mark'];
		///*检测是否有新留言**/
		$has_new  = $task_obj->has_new_comment($p ['page'],$p ['page_size']);			
		break;
	case "comment":
	$comment_obj = keke_comment_class::get_instance('task'); 
		$url = $basic_url."&view=comment";
		intval($page) or $page = 1;
		$comment_arr = $comment_obj->get_comment_list($task_id, $url, $page); 
		$comment_data = $comment_arr['data'];
		$comment_page = $comment_arr['pages'];
		$reply_arr = $comment_obj->get_reply_info($task_id);
	
	    switch ($op){
	    	case "reply": //回复任务留言
	    		$comment_arr = array("obj_id"=>$task_id,"origin_id"=>$task_id,"obj_type"=>"task","p_id"=>$pid,
	    		 "uid"=>$uid, "username"=>$username,"content"=>$content,"on_time"=>time()); 
	    		$res = $comment_obj->save_comment($comment_arr,$task_id,1); 
	    		if($res!=3&&$res!=2){
	    			$v1 =  $comment_obj->get_comment_info($res);
	    			$tmp ='replay_comment';
	    			require keke_tpl_class::template ( "task/task_comment_reply" );
	    		}else{
	    			echo $res;
	    		}
	    		die();
	    		break;
	    	case "add": //添加任务留言 
	    		$comment_arr = array("obj_id"=>$task_id,"origin_id"=>$task_id,"obj_type"=>"task",
	    		"uid"=>$uid, "username"=>$username,"content"=>$content,"on_time"=>time());
	    		$res = $comment_obj->save_comment($comment_arr,$task_id); 
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
	    			//更新个人信息 
	    			$res = $comment_obj->del_comment($comment_id,$task_id,$comment_info['p_id']);
	    		}else{
	    			kekezu::keke_show_msg("", $_lang['not_priv'],"error","json");
	    		}
	    		$res and kekezu::keke_show_msg("", $_lang['delete_success'],"","json") or kekezu::keke_show_msg("",$_lang['system_is_busy'],"error","json");
	    		break;	
	    } 
		break;
	case "mark":
		$mark_count = $task_obj->get_mark_count();//评价统计
		intval ( $page ) and $p ['page'] = intval ( $page ) or $p ['page']='1';
		intval ( $page_size ) and $p ['page_size'] = intval ( $page_size ) or $p['page_size']='10';
		$p['url'] = $basic_url."&view=mark&page_size=".$p ['page_size']."&page=".$p ['page'];
		$p ['anchor'] = '';
		$w['model_code'] = $model_code;//互评模型
		$w['origin_id']   = $task_id;//互评源 task_id
		$w['mark_status'] = $st;//评价状态
		//$ut=='my' and $w['uid'] = $uid;//我的评价
		$w['mark_type'] = $ut;//来自的评论
		$mark_arr = keke_user_mark_class::get_mark_info($w,$p,' mark_id desc ',"mark_status>0");
		$mark_info = $mark_arr['mark_info'];
		$pages     = $mark_arr['pages'];
		break;
	 
	case "base":
	default:
		$task_file = $task_obj->get_task_file();
		$kekezu->init_prom();
		$can_prom = $kekezu->_prom_obj->is_meet_requirement ( "bid_task", $task_id );
		if($task_info['task_status']==8){
			$list_work = db_factory::query(' select DISTINCT(uid) uid,username from '.TABLEPRE.'witkey_task_work where task_id='.intval($task_id).' and work_status in (4,6)');
		}
		if($task_info['task_status']==2&&$task_info['uid']==$uid){
			$item_list= keke_payitem_class::get_payitem_config ( 'employer', null, null, 'item_id' );
		}
		break;
}

if($task_info['r_task_id']){
require keke_tpl_class::template ( "task_info");
}else{
require keke_tpl_class::template ( "task/" . $model_info ['model_code'] . "/tpl/" . $_K ['template'] . "/task_info" );
}