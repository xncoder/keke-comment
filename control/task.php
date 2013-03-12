<?php

/**
 * 任务详细页、任务首页的入口文件
 * @copyright keke-tech
 * @author Monkey
 * @version v 2.0
 * 2010-8-11上午08:05:04
 */

defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$rs_indus  = kekezu::get_classify_indus('task','total');
$nav_active_index = "task";
$page_title = $_lang ['task_index'] . '-' . $_K ['html_title'];

if (isset ( $task_id )) {
    // ext: 是否延时
	$task_ext_obj = new Keke_witkey_task_ext_class ();
	$task_ext_obj->setWhere ( 'a.task_id=' . intval ( $task_id ) );
	$task_info = $task_ext_obj->query_keke_witkey_task ();
	$task_info = kekezu::k_stripslashes ( $task_info ['0'] );
	$prom_rule = keke_prom_class::get_prom_rule ( "bid_task" );
	$task_info ['uid'] != $uid && $uid != ADMIN_UID && $task_info ['task_status'] == 1 and kekezu::show_msg ( $_lang ['friendly_notice'], 'index.php?do=task_list', 2, $_lang ['task_auditing'] );
	$task_info ['uid'] != $uid && $task_info ['task_status'] == 0 and kekezu::show_msg ( $_lang ['friendly_notice'], 'index.php?do=task_list', 2, $_lang ['task_not_pay'] );
	
	$union_hand = keke_union_class::hand_link ($task_info);
	if ($task_info ['task_union'] == 2 && $task_info ['r_task_id'] && intval ( $u ) == 1) {
		$union_obj = new keke_union_class ( $task_id );
		$union_obj->view_task ();
	}
    // view 包括{职位需求,推荐记录:work, 留言:comment, 评价:mark, 工具:tools}
	if ($view == 'misc' && in_array ( $t, array (1, 2, 3, 4 ) ) && $user_info ['group_id']) {
		switch ($t) {
			case 1 : //审核通过
				$res = keke_task_config::task_audit_pass ( array ($task_id ) );
				break;
			case 2 : //审核不通过
				$res = keke_task_config::task_audit_nopass ( $task_id );
				break;
			case 3 : //推荐
				$res = db_factory::execute ( 'update ' . TABLEPRE . 'witkey_task set is_top=1 where task_id=' . $task_id );
				break;
			case 4 : //冻结
				$res = keke_task_config::task_freeze ( $task_id );
				break;
		}
		$res and kekezu::show_msg ( $_lang ['operate_notice'], "index.php?do=task&task_id=$task_id", '1', $_lang ['operate_success'], 'alert_right' ) or kekezu::show_msg ( $_lang ['operate_notice'], "index.php?do=task&task_id=$task_id", '1', $_lang ['operate_fail'], 'alert_error' );
	}
    // 工具	
	if ($view == 'tools') {
		$payitem_arr = keke_payitem_class::get_payitem_info ( 'employer', $model_list [$task_info ['model_id']] ['model_code'] ); //获取该任务所有的增值服务 
		$exist_payitem_arr = keke_payitem_class::payitem_exists ( $uid, false, '', $payitem_arr ); //获取已购买的增值服务 
		$payitem_arr_desc = unserialize ( $task_info ['payitem_time'] ); //获取任务属性描述
		$payitem_standard = keke_payitem_class::payitem_standard (); //收费标准
		

		foreach ( $payitem_arr_desc as $k => $v ) {
			if ($v > time ()) {
				$sy_time_str = $v - time ();
				$sy_time_desc [$k] = kekezu::time2Units ( $sy_time_str );
			} else {
				$sy_time_desc [$k] = '0' . $_lang ['day'];
			}
		}
		
		if (isset ( $formhash ) && kekezu::submitcheck ( $formhash )) {
			//var_dump(258);die();
			$payitem_num = ( array ) $payitem_num;
			if (! array_filter ( $payitem_num )) {
				kekezu::show_msg ( $_lang ['friendly_notice'], 'index.php?do=task&task_id=' . $task_id . '&view=tools', 3, $_lang ['no_choose_any_tools'] );
			}
			$keys_arr = array_keys ( $payitem_arr_desc );
			$pay_item = $task_info ['pay_item'];
			foreach ( array_filter ( $payitem_num ) as $k => $v ) {
				if (intval ( $v ) > 0 && ! stristr ( $pay_item, "$k" )) {
					$pay_item = $pay_item . ",$k";
				}
				if (in_array ( $payitem_arr [$k] ['item_code'], $keys_arr )) {
					//非地图的增值服务
					$payitem_arr_desc [$payitem_arr [$k] ['item_code']] > time () and $payitem_arr_desc [$payitem_arr [$k] ['item_code']] = 3600 * 24 * $v + $payitem_arr_desc [$payitem_arr [$k] ['item_code']] or $payitem_arr_desc [$payitem_arr [$k] ['item_code']] = time () + 3600 * 24 * $v;
				} else {
					//地图增值服务   
					db_factory::execute ( sprintf ( "update %switkey_task set point='%s',city='%s' where task_id=%d", TABLEPRE, $_POST ['point'], $province, $task_id ) );
				
	//更新任务属性  
				}
				$cost_res = keke_payitem_class::payitem_cost ( $payitem_arr [$k] ['item_code'], $v, 'task', 'spend', $task_id, $task_id );
			
	//生成使用记录  
			}
			$pay_item = ltrim ( $pay_item, "," );
			if (strlen ( $pay_item )) {
				db_factory::execute ( sprintf ( "update %switkey_task set pay_item='%s' where task_id=%d", TABLEPRE, $pay_item, $task_id ) ); //更新任务属性
			}
			$res = keke_payitem_class::set_payitem_time ( $payitem_arr_desc, $task_id, 'task' );
			//更新增值服务结束时间
			//var_dump($res);die();
			$res || $cost_res and kekezu::show_msg ( $_lang ['operate_notice'], "index.php?do=task&task_id=$task_id&view=tools", '1', $_lang ['operate_success'], 'alert_right' );
		}
	}
    // task id是否存在	
	if (! $task_info) {
		kekezu::show_msg ( $_lang ['operate_notice'], "index.php?do=index", '1', $_lang ['task_not_exsit_has_delete'], 'error' );
	}
	if ($task_info ['point']) {
		$point = explode ( ',', $task_info ['point'] );
		$px = $point ['0'];
		$py = $point ['1'];
	}
	$model_info = $model_list [$task_info ['model_id']];
	$model_code = $model_info ['model_code'];
	keke_lang_class::package_init ( "task" );
	keke_lang_class::loadlang ( $model_info ['model_dir'] );
	keke_lang_class::loadlang ( "task_info" );
	$page_keyword = $task_info['task_title']. '-' .$kekezu->_sys_config['seo_keyword'];
	$page_description = $task_info['task_title']. '-' .$kekezu->_sys_config['seo_desc'];
    // 载入task/xx/control/task_info.php
	$model_info and (require S_ROOT . "/task/" . $model_info ['model_dir'] . "/control/task_info.php") or kekezu::show_msg ( $_lang ['error'], "index.php?do=index", 3, $_lang ['task_model_not_exist'], 'error' );
} else {
	
	$clean_industry_arr = array ();
	kekezu::get_tree ( $rs_indus, $clean_industry_arr, '' );
	/**
	 * 进行中任务统计*
	 */
	$count_advance_task_sql = 'select count(*) count from ' . TABLEPRE . 'witkey_task where task_status in (2,3)';
	
	$advance_task = db_factory::get_count ( $count_advance_task_sql, 0, null, 180 );
	
	$model_list = $kekezu->_model_list; // 获取任务区间
	$task_cash_cove = kekezu::get_cash_cove ( '', true );
	$task_obj = new Keke_witkey_task_class ();
	$page_obj = $kekezu->_page_obj;
	$page_obj->setAjax ( 1 );
	$page_obj->setAjaxDom ( "task_list" );
	isset ( $page ) or $page = 1;
	isset ( $page_size ) or $page_size = 12;
	isset ( $t ) or $t = 'new';
	$url = "index.php?do=task&t=$t";
	$sql = " task_union=0 ";
	switch ($t) {
		
		case "new" :
			$sql .= sprintf ( " and task_status=2 order by start_time desc " );
			
			break;
		case "h" :
			/**
			 * 24小时任务
			 */
			$sql .= sprintf ( " and  sub_time<'%s' and task_status='2' order by start_time desc ", time () + 24 * 3600 );
			break;
		case "t" :
			/**
			 * 高金额任务*
			 */
			$sql .= " and task_status in (2,3) order by task_cash desc ";
			break;
		case "u" :
			/**
			 * 联盟任务 ,1：上游站推广的任务,2:要推广的任务
			 */
			$sql = " 1=1 and task_status in (2,3) and `r_task_id` > '0' and task_union=2 order by start_time desc ";
			break;
	}
	
	$task_obj->setWhere ( $sql );
	$count = intval ( $task_obj->count_keke_witkey_task () );
	$pages = $page_obj->getPages ( $count, $page_size, $page, $url );
	
	$task_obj->setWhere ( $sql . $pages ['where'] );
	$task_list = $task_obj->query_keke_witkey_task ( 1, 300 );
	
	function is_tender($model_code) {
		if (in_array ( $model_code, array ("dtender", "tender" ) )) {
			return 1;
		} else {
			return 0;
		}
	}
	require keke_tpl_class::template ( "task" );
}
