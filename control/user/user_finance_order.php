<?php
defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
keke_lang_class::loadlang ( 'user_finance_order', 'user' );
if ($model_id) {
    // 查询订单情况
	if ($role == 1) { 
		$order_count = kekezu::get_table_data ( "model_id,count(order_id) count", "witkey_order", "model_id IN(6,7) and `seller_uid`=$uid ", "", "model_id=6,model_id=7", "", "model_id", 3600 );
	} else { 
        // op = shop, role = 2
		$order_count = kekezu::get_table_data ( "model_id,count(order_id) count", "witkey_order", "model_id IN(6,7) and `order_uid`=$uid ", "", "model_id=6,model_id=7", "", "model_id", 3600 );
	}
    // 获取model_id,model_name,该model下的order数量，作为顶部菜单
	$third_nav = array ();
	foreach ( $model_list as $v ) {
		$third_nav [] = array ("1" => $v ['model_id'], "2" => $v ['model_name'], "3" => intval ( $order_count [$v ['model_id']] ['count'] ) );
	}
    // 生成分页数据
	$page = intval ( $page );
	$page or $page = 1;
	$page_size = intval ( $page_size );
	$page_size or $page_size = 10;
	$url = "index.php?do=user&view=$view&op=shop&model_id=$model_id&page_size=$page_size&status=$status&page=$page";
    //{confirm:确认资料文件,arbitral:交易维权} 
    //ac : action的缩写，包括del等
	if (isset ( $ac ) && $order_id && $model_id) {
		$model_info = $kekezu->_model_list [$model_id];
		$class_name = $model_info ['model_code'] . "_" . $model_info ['model_type'] . "_class";
		$obj = new $class_name ( $task_info );
		$res = $obj->dispose_order ( $order_id, $ac );
	} elseif ($mark) {
        // 双方互评
		$title = $_lang ['both_mark'];
		$obj_id = $obj_id;
		$order_id = $order_id;
		$model_code = $kekezu->_model_list[$model_id]['model_code'];
		require S_ROOT . 'control/mark.php';
		die ();
	} elseif ($download) {
        // 文件下载
		$title = $_lang ['works_file_upload'];
		$view = "file";
		$ajax = "goods_filedown";
		require "control/ajax/ajax_file.php";
		die();
	} else {
		$model_list = $kekezu->_model_list;
		$obj_arr = keke_order_class::get_order_obj (); 
        // 连接四张表 order/order_detail/service/space
		$sql = " select a.*,b.obj_type,b.obj_id,c.`submit_method`,d.`mobile` from " . TABLEPRE . "witkey_order a left join " . TABLEPRE . "witkey_order_detail b on a.order_id = b.order_id 
			left join " . TABLEPRE . "witkey_service c on c.`service_id`=b.`obj_id`
			left join " . TABLEPRE . "witkey_space d on d.`uid`=a.`seller_uid` where b.obj_type = 'service' ";
		$model_id and $sql .= " and a.`model_id`=$model_id ";
		$role == '1' and $sql .= " and seller_uid = '$uid' " or $sql .= " and order_uid = '$uid' ";
        // 状态数组填充语言文本
		$status_arr = array ("wait" => $_lang ['wait_buyer_pay'], "ok" => $_lang ['buyer_has_pay'], 'accept' => $_lang ['seller_has_accept'], "send" => $_lang ['seller_has_severice'], "confirm" => $_lang ['confirm_complete'], "close" => $_lang ['trans_close'], "arbitral" => $_lang ['order_arbitral'], 'arb_confirm' => $_lang ['confirm_complete'] );
		$role == 2 and $status_arr ['ok'] = $_lang ['has_pay'];
		$ord_arr = array ('a.order_id desc' => $_lang ['order_id_desc'], "a.order_id asc" => $_lang ['order_id_asc'] );
		$order_obj = new Keke_witkey_order_class ();
		$page_obj = $kekezu->_page_obj;
		$order_id && $order_id != $_lang ['please_input_order_id'] and $sql .= " and a.order_id = " . $order_id;
		$order_title && $order_title != $_lang ['please_input_order_name'] and $sql .= " and a.order_name like '%$order_title%'";
		$status and $sql .= " and a.order_status = '$status'";
		$ord and $sql .= " order by $ord " or $sql .= " order by order_id desc ";
		$count = intval ( db_factory::execute ( $sql ) );
		$pages = $page_obj->getPages ( $count, $page_size, $page, $url, '#userCenter' );
		$order_arr = db_factory::query ( $sql . $pages ['where'] );
	}
	if ($action == 'delete') {
		$detail_obj = new Keke_witkey_order_detail_class ();
		$task_obj = new Keke_witkey_task_class ();
		$order_obj->setWhere ( "order_id = $order_id" );
		$order_obj->del_keke_witkey_order ();
		$detail_obj->setWhere ( "order_id = $order_id" );
		$detail_info = $detail_obj->query_keke_witkey_order_detail ();
		$detail_info = $detail_info ['0'];
		$detail_obj->setWhere ( "order_id = $order_id" );
		$detail_obj->del_keke_witkey_order_detail ();
		$task_id = $detail_info ['obj_id'];
		$task_obj->setWhere ( "task_id = $task_id" );
		$res = $task_obj->del_keke_witkey_task ();
		kekezu::echojson ( '', 1 );
	}
}
function get_mark_info($order_id, $obj_id, $order_uid, $seller_uid) {
	global $uid, $role;
	if ($role == 1) { 
		$mark_type = 1;
		$auid = $order_uid;
	} else { 
		$mark_type = 2;
		$auid = $seller_uid;
	}
	$mark_info = db_factory::get_one ( sprintf ( "select * from %switkey_mark where obj_id=%d and origin_id=%d and mark_type=%d and uid=$auid and by_uid=$uid", TABLEPRE, $order_id, $obj_id, $mark_type ) );
	return $mark_info;
}
require keke_tpl_class::template ( "user/user_finance_order_service" );
