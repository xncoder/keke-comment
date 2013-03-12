<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
switch ($ajax) {
	case "user_detail" :
		$user_info 		= keke_user_class::get_user_info ( $user_id );
		$take_num  		= intval($user_info['take_num']);
		$accepted_num   = intval($user_info['accepted_num']);
		$auth_info	    = keke_auth_fac_class::get_submit_auth_record ( $user_id, 1 );
		$order_count	= intval ( db_factory::get_count ( sprintf ( " select count(order_id) from %switkey_order where order_status='confirm' and seller_uid='%d' and model_id in(6,7)", TABLEPRE, $user_id ) ) );
		$buyer_aid	    = keke_user_mark_class::get_user_aid ( $user_id, '2', null, '1' );
		break;
	case 'show_secode':
		require keke_tpl_class::template('ajax/ajax_menu');
		exit();
	break;
	case "set_comment" :
		$comment_arr = array ("obj_id" => $obj_id, "origin_id" => $origin_id, "obj_type" => $obj_type, "p_id" => $p_id, "uid" => $uid, "username" => $username, "content" => $tar_content, "on_time" => time () );
		kekezu::set_leave ( $comment_arr, $type, $pk, '', 'json' );
		break;
	case "load_comment" :
	case "load_reply" :
		if ($comment_id) {
			$comm_info = db_factory::get_one ( sprintf ( " select * from %switkey_comment where comment_id = '%d'", TABLEPRE, $comment_id ) );
		} else {
			die ();
		}
		break;
	case "del_comment" :
		kekezu::del_comment ( $pk, $obj_type, $comment_id, $origin_id, '', 'json' );
		break;
	case "prom_link" :
		$title = $_lang['get_promote_links'];;
		$link = $_K [siteurl] . "/index.php?do=prom&u=" . $user_info ['uid'] . "&p=" . $prom_code;
		$o and $link .= "&o=" . $o;
		$l and $link .= "&l=" . $l;
		$promtext or $promtext = $_K ['html_title'];
		break;
	case "prom_list" :
		$model_list = $kekezu->_model_list;
		$page_obj = $kekezu->_page_obj;
		$page_obj->setAjax ( '1' );
		$page_obj->setAjaxDom ( 'ajax_list' );
		$page or $page = 1;
		$page_size or $page_size = 10;
		$url = "index.php?do=ajax&view=menu&ajax=prom_list&prom_code=$prom_code";
		$prom_rule = keke_prom_class::get_prom_rule ( $prom_code );
		switch ($prom_code) {
			case "bid_task" :
				$table_title = array ($_lang['belong_model'], $_lang['task_title'], $_lang['task_cash'], $_lang['pro_cash'] );
				$task_obj = new Keke_witkey_task_class ();
				$where = " task_status='2' ";
				$prom_rule ['model'] and $where .= " and model_id in (" . $prom_rule [model] . ") ";
				$prom_rule ['indus_string'] and $where .= " and indus_id in (" . $prom_rule [indus_string] . ") ";
				$task_obj->setWhere ( $where );
				$count = intval ( $task_obj->count_keke_witkey_task () );
				$pages = $page_obj->getPages ( $count, $page_size, $page, $url );
				$task_obj->setWhere ( $where . $pages ['where'] );
				$prom_list = $task_obj->query_keke_witkey_task ();
				break;
			case "service" :
				$table_title = array ($_lang['belong_model'], $_lang['goods_title'], $_lang['goods_cash'], $_lang['pro_cash'] );
				$ser_obj = new Keke_witkey_service_class ();
				$where = " service_status='2' ";
				$prom_rule ['model'] and $where .= " and model_id in (" . $prom_rule [model] . ") ";
				$prom_rule ['indus_string'] and $where .= " and indus_id in (" . $prom_rule [indus_string] . ") ";
				$ser_obj->setWhere ( $where );
				$count = intval ( $ser_obj->count_keke_witkey_service () );
				$pages = $page_obj->getPages ( $count, $page_size, $page, $url );
				$ser_obj->setWhere ( $where . $pages ['where'] );
				$prom_list = $ser_obj->query_keke_witkey_service ();
				break;
		}
		break;
}
require keke_tpl_class::template ( "ajax/ajax_" . $view );
