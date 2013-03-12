<?php
keke_lang_class::load_lang_class ( 'keke_shop_class' );
class keke_shop_class {
	public static function get_service_info($sid) {
		return db_factory::get_one ( sprintf ( " select * from %switkey_service where service_id=%d", TABLEPRE, $sid ) );
	}
	public static function notify_user($uid, $username, $action, $title, $v_arr = array()) {
		$msg_obj = new keke_msg_class ();
		$contact = self::get_contact ( $uid );
		$msg_obj->send_message ( $uid, $username, $action, $title, $v_arr, $contact ['email'], $contact ['mobile'] );
	}
	public static function get_contact($uid) {
		return db_factory::get_one ( sprintf ( " select mobile,email from %switkey_space where uid = '%d'", TABLEPRE, $uid ) );
	}
	public static function access_check($sid, $s_uid, $model_id) {
		global $uid, $kekezu;
		global $_lang;
		$uid == $s_uid and kekezu::keke_show_msg ( "index.php?do=shop", $_lang ['seller_not_to_order_page'], 'error' );
		$order_info = self::check_has_buy ( $sid, $uid );
		$order_status = $order_info ['order_status'];
		$order_id = intval ( $order_info ['order_id'] );
		$model_code = $kekezu->_model_list [$model_id] ['model_code'];
		if (! $order_status) {
			return true;
		} else {
			if ($order_status == 'close') {
				return true;
			} elseif ($order_status == 'confirm') {
				if ($model_code == 'goods') {
					kekezu::keke_show_msg ( "index.php?do=user&view=employer&op=shop&model_id=" . $model_id . "&order_id=" . $order_id, $_lang ['you_has_buy_work'], 'error' );
				} else {
					return true;
				}
			} else {
				kekezu::keke_show_msg ( "index.php?do=user&view=employer&op=shop&model_id=" . $model_id . "&order_id=" . $order_id, $_lang ['your_order_not_process_complete'], 'error' );
			}
		}
	}
	public static function create_service_order($service_info) {
		global $uid, $username, $_K;
		global $_lang;
		$uid == $service_info ['uid'] and kekezu::keke_show_msg ( "index.php?do=shop", $_lang ['seller_can_not_order_self'], 'error' );
		$oder_obj = new Keke_witkey_order_class (); 
		$order_detail = new Keke_witkey_order_detail_class (); 
		$service_cash = $service_info ['price']; 
		switch ($service_info ['model_id']) {
			case "6" :
				$type = $_lang ['work'];
				break;
			case "7" :
				$type = $_lang ['service'];
				break;
		}
		$order_name = $service_info ['title']; 
		$order_body = $_lang ['buy_goods'] . "<a href=\"index.php?do=service&sid=$service_info[service_id]\">" . $order_name . "</a>"; 
		$data = array (':service_id' => $service_info ['service_id'], ':title' => $service_info ['title'] );
		keke_finance_class::init_mem ( 'buy_service', $data );
		$fina_id = keke_finance_class::cash_out ( $uid, $service_cash, 'buy_service', '', 'service', $service_info ['service_id'] );
		$fina_id and $order_status = 'ok' or $order_status = 'wait'; 
		$order_id = keke_order_class::create_order ( $service_info ['model_id'], $service_info ['uid'], $service_info ['username'], $order_name, $service_cash, $order_body, $order_status );
		if ($order_id) {
			$fina_id and keke_order_class::update_fina_order ( $fina_id, $order_id );
			keke_order_class::create_order_detail ( $order_id, $order_name, 'service', intval ( $service_info [service_id] ), $service_cash );
			if ($fina_id) {
				$msg_obj = new keke_msg_class (); 
				$service_url = "<a href=\"" . $_K [siteurl] . "/index.php?do=service&sid=" . $service_info [service_id] . "\">" . $order_name . "</a>";
				$order_url = "<a href=\"" . $_K [siteurl] . "/index.php?do=user&view=witkey&op=shop&model_id=" . $service_info ['model_id'] . "&order_id=" . $order_id . "#userCenter\">#" . $order_id . "</a>";
				$s_notice = array ($_lang ['user_action'] => $username . $_lang ['order_buy'], $_lang ['service_name'] => $service_url, $_lang ['service_type'] => $type, $_lang ['order_link'] => $order_url );
				$contact = db_factory::get_one ( sprintf ( " select mobile,email from %switkey_space where uid='%d'", TABLEPRE, $service_info [uid] ) );
				$msg_obj->send_message ( $service_info ['uid'], $service_info ['username'], "service_order", $_lang ['you_has_new'] . $type . $_lang ['order'], $s_notice, $contact ['email'], $contact ['mobile'] ); 
				$feed_arr = array ("feed_username" => array ("content" => $username, "url" => "index.php?do=space&member_id=" . $uid ), "action" => array ("content" => $_lang ['buy'], "url" => '' ), "event" => array ("content" => $order_name, "url" => "index.php?do=service&sid=$service_info[service_id]" ) );
				kekezu::save_feed ( $feed_arr, $uid, $username, 'service', $service_info ['service_id'], $service_url );
				kekezu::show_msg ( $_lang ['operate_notice'], "index.php?do=user&view=employer&op=shop&model_id={$service_info ['model_id']}&order_id=" . $order_id, '1', $_lang ['order_produce_success'], 'alert_right' );
			} else {
				kekezu::keke_show_msg ( "index.php?do=pay&order_id=$order_id", $_lang ['order_pay_fail_for_cash_little'], "alert_error" );
			}
		} else {
			kekezu::keke_show_msg ( 'index.php?do=shop_order&sid=' . $service_info [service_id], $_lang ['order_produce_fail'], "alert_error" );
		}
	}
	public static function get_sale_info($sid, $w = array(), $p = array(), $order = null, $ext_condit) {
		global $kekezu;
		$where = " select a.order_status,a.order_uid,a.order_username,a.order_amount,a.order_time from " . TABLEPRE . "witkey_order a left join " . TABLEPRE . "witkey_order_detail b on a.order_id=b.order_id where
		b.obj_id='$sid' and b.obj_type = 'service' ";
		$ext_condit and $where .= " and " . $ext_condit;
		$arr = keke_table_class::format_condit_data ( $where, $order, $w, $p );
		$sale_info = db_factory::query ( $arr ['where'] );
		$sale_arr ['sale_info'] = $sale_info;
		$sale_arr ['pages'] = $arr ['pages'];
		return $sale_arr;
	}
	function get_service_comment($sid, $w = array(), $p = array(), $order = null) {
		global $kekezu;
		$comm_obj = new Keke_witkey_comment_class ();
		$where = " select * from " . TABLEPRE . "witkey_comment where obj_id = '$sid' and obj_type = 'service' ";
		$arr = keke_table_class::format_condit_data ( $where, $order, $w, $p );
		$comm_info = db_factory::query ( $arr ['where'] );
		$comm_arr ['comm_info'] = $comm_info;
		$comm_arr ['pages'] = $arr ['pages'];
		return $comm_arr;
	}
	public static function set_report($obj_id, $to_uid, $to_username, $report_type, $file_name, $desc) {
		global $uid;
		global $_lang;
		$service_info = self::get_service_info ( $obj_id );
		$transname = keke_report_class::get_transrights_name ( $report_type ); 
		$service_info ['uid'] == $uid and kekezu::keke_show_msg ( '', $_lang ['can_not_to_self'] . $transname, 'error', 'json' );
		$user_type = '2'; 
		$res = keke_report_class::add_report ( 'product', $obj_id, $to_uid, $to_username, $desc, $report_type, $service_info ['service_status'], $obj_id, $user_type, $file_name );
	}
	public static function get_mark_count($model_code, $sid) {
		return kekezu::get_table_data ( " count(mark_id) count,mark_status", "witkey_mark", "model_code='" . $model_code . "' and origin_id='$sid'", "", "mark_status", "", "mark_status", 3600 );
	}
	public static function get_mark_count_ext($model_code, $sid) {
		return kekezu::get_table_data ( " count(mark_id) c,mark_type", "witkey_mark", "model_code='" . $model_code . "' and origin_id='$sid' and mark_status>0", "", "mark_type", "", "mark_type", 3600 );
	}
	public static function get_hot_service($model_id, $sid, $indus_pid) {
		return kekezu::get_table_data ( " sale_num,service_id,price,title,pic ", "witkey_service", " model_id = '$model_id' and service_id !='$sid' and indus_pid = '$indus_pid' and service_status='2' and sale_num>0", "sale_num desc", "", "3", "", 3600 );
	}
	public static function get_related_service($model_id, $sid, $indus_id) {
		return kekezu::get_table_data ( "pic,service_id,title,price,unite_price", "witkey_service", " model_id = '$model_id' and service_id !='$sid' and indus_id = '$indus_id' and service_status='2'", "", "", "6", "", 3600 );
	}
	public static function get_more_service($uid, $sid) {
		return kekezu::get_table_data ( "service_id,title,pic", "witkey_service", " uid='$uid' and service_status='2' and service_id!='$sid'", "sale_num desc ", "", "5", "", 3600 );
	}
	public static function get_task_info($indus_id) {
		return kekezu::get_table_data ( "task_id,task_title,task_cash", "witkey_task", " indus_id = '$indus_id' and task_status='2'", "", "", "14", "", 3600 );
	}
	public static function plus_view_num($sid, $s_uid) {
		global $uid;
		if (! $_SESSION ['service_view_' . $sid . '_' . $uid] && $uid != $s_uid) {
			db_factory::execute ( sprintf ( " update %switkey_service set views=views+1 where service_id='%d'", TABLEPRE, $sid ) );
			$_SESSION ['service_view_' . $sid . '_' . $uid] = '1';
		}
	}
	public static function plus_sale_num($sid, $sale_cash) {
		return db_factory::execute ( sprintf ( " update %switkey_service set sale_num=sale_num+1,total_sale=total_sale+'%f.2' where service_id = '%d'", TABLEPRE, $sale_cash, $sid ) );
	}
	public static function plus_mark_num($service_id) {
		return db_factory::execute ( sprintf ( "update %switkey_service set mark_num=mark_num+2 where service_id ='%d'", TABLEPRE, $service_id ) );
	}
	public static function check_has_buy($sid, $uid) {
		return db_factory::get_one ( sprintf ( " select a.order_status,a.order_id from %switkey_order a left join %switkey_order_detail b
					on a.order_id = b.order_id where a.order_uid ='%d' and b.obj_id='%d' and obj_type='service'", TABLEPRE, TABLEPRE, $uid, $sid ) );
	}
	static function output_pics($path, $pre = null, $show = false) {
		$tmp = explode ( ',', $path . ',' );
		$tmp = array_unique ( array_filter ( $tmp ) );
		if ($tmp) {
			$s = sizeof ( $tmp );
			if ($show) { 
				return keke_img_class::get_filepath_by_size($tmp [$s-1],$pre);
			} else {
				for($i = 0; $i < $s; $i ++) {
					$tmp [$i] = keke_img_class::get_filepath_by_size($tmp [$i],$pre);
				}
				return $tmp;
			}
		}
		return keke_img_class::get_filepath_by_size('',$pre);
	}
}