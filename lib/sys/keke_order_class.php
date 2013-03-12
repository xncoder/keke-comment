<?php
keke_lang_class::load_lang_class ( 'keke_order_class' );
class keke_order_class {
	public static function get_order_info($order_id) {
		$sql = "select a.*,b.obj_type,b.obj_id from %switkey_order a left join
		%switkey_order_detail b on a.order_id=b.order_id where a.order_id='%d'";
		return db_factory::get_one ( sprintf ( $sql, TABLEPRE, TABLEPRE, $order_id ) );
	}
	public static function get_order_id($obj_type, $obj_id) {
		$sql = "select order_id from %switkey_order_detail where obj_type='%s' and obj_id='%d'";
		return db_factory::get_count ( sprintf ( $sql, TABLEPRE, $obj_type, $obj_id ) );
	}
	public static function get_order_detail($order_id) {
		$sql = "select * from %switkey_order_detail where order_id = '%d'";
		return db_factory::query ( sprintf ( $sql, TABLEPRE, $order_id ) );
	}
	public static function create_order($model_id, $seller_uid, $seller_username, $order_name, $order_amount, $order_body, $order_status = 'ok') {
		global $uid, $username;
		$order_obj = new Keke_witkey_order_class ();
		$order_obj->_order_id = null;
		$order_obj->setModel_id ( $model_id );
		$order_obj->setOrder_name ( $order_name );
		$order_obj->setOrder_uid ( $uid );
		$order_obj->setOrder_username ( $username );
		$order_obj->setSeller_uid ( $seller_uid );
		$order_obj->setSeller_username ( $seller_username );
		$order_obj->setOrder_body ( $order_body );
		$order_obj->setOrder_amount ( $order_amount );
		$order_obj->setOrder_status ( $order_status );
		$order_obj->setOrder_time ( time () );
		return $order_obj->create_keke_witkey_order ();
	}
	public static function create_user_charge_order($order_type, $pay_type, $money, $obj_id = null, $pay_info = '', $order_status = 'wait', $uid = '', $username = '') {
		global $user_info;
		$uid or $uid = $user_info ['uid'];
		$username or $username = $user_info ['username'];
		$sql = "select order_id,order_status from %switkey_order_charge where uid='%d' and pay_type='%s'";
		$obj_id and $sql .= " and obj_id='$obj_id'";
		$order_info = db_factory::get_one ( sprintf ( $sql, TABLEPRE, $uid, $pay_type ) );
		$status = $order_info ['order_status'];
		$order_id = $order_info ['order_id'];
		if ($obj_id) { 
			$order_id and ($status == 'wait' and $update = true);
		} else {
			$order_id and ($status == 'wait' and $update = true or $create = true);
		}
		$order_id or $create = true; 
		$update and db_factory::execute ( sprintf ( " update %switkey_order_charge set pay_money='%.2f',pay_time='%s' where order_id='%d'", TABLEPRE, $money, time (), $order_id ) );
		if ($create) {
			$order_obj = new Keke_witkey_order_charge_class ();
			$order_obj->_order_id = null;
			$order_obj->setOrder_type ( $order_type );
			$order_obj->setUid ( $uid );
			$order_obj->setPay_info ( $pay_info );
			$order_obj->setPay_type ( $pay_type );
			$order_obj->setObj_id ( $obj_id );
			$order_obj->setUsername ( $username );
			$order_obj->setPay_money ( $money );
			$order_obj->setPay_time ( time () );
			$order_obj->setOrder_status ( $order_status );
			$order_id = $order_obj->create_keke_witkey_order_charge ();
		}
		return $order_id;
	}
	public static function create_order_detail($order_id, $detail_name, $obj_type, $obj_id, $price, $num = '1') {
		$detail_obj = new Keke_witkey_order_detail_class ();
		$detail_obj->_detail_id = null;
		$detail_obj->setOrder_id ( $order_id );
		$detail_obj->setDetail_name ( $detail_name );
		$detail_obj->setObj_id ( $obj_id );
		$detail_obj->setObj_type ( $obj_type );
		$detail_obj->setPrice ( $price );
		$detail_obj->setNum ( $num );
		return $detail_obj->create_keke_witkey_order_detail ();
	}
	public static function del_order($order_id, $url = '', $output = 'normal') {
		global $_lang;
		$res = db_factory::execute ( sprintf ( " delete from %switkey_order where order_id='%d'", TABLEPRE, $order_id ) );
		$res *= db_factory::execute ( sprintf ( " delete from %switkey_order_detail where order_id = '%d'", TABLEPRE, $order_id ) );
		$res and kekezu::keke_show_msg ( $url, $_lang['order_delete_success'], "", $output ) or kekezu::keke_show_msg ( $url, $_lang['order_delete_fail'], "error", $output );
	}
	public static function set_order_status($order_id, $to_status) {
		return db_factory::execute ( sprintf ( " update %switkey_order set order_status='%s' where order_id='%d'", TABLEPRE, $to_status, $order_id ) );
	}
	public static function order_cancel_return($order_id) {
		$fina_info = db_factory::get_one ( sprintf ( " select uid,fina_cash,fina_credit from %switkey_finance where order_id ='%d'", TABLEPRE, $order_id ) );
		if ($fina_info) {
			keke_finance_class::init_mem('order_cancel', array(':order_id'=>$order_id));
			return keke_finance_class::cash_in ( $fina_info ['uid'], $fina_info ['fina_cash'], $fina_info ['fina_credit'], "order_cancel", '', 'order', $order_id );
		} else {
			return true;
		}
	}
	public static function set_report($order_id, $to_uid, $to_username, $report_type, $file_name, $desc) {
		global $uid;
		global $_lang;
		$order_info = self::get_order_info ( $order_id );
		$transname = keke_report_class::get_transrights_name ( $report_type ); 
		if ($order_info ['order_uid'] == $uid || $order_info ['seller_uid'] == $uid) {
			if ($order_info ['order_uid'] == $uid && $uid == $to_uid) {
				kekezu::keke_show_msg ( '', $_lang['buyer_can_not_to_self'] . $transname, 'error', 'json' );
			} elseif ($order_info ['seller_uid'] == $uid && $uid == $to_uid) {
				kekezu::keke_show_msg ( '', $_lang['seller_can_not_to_self'] . $transname, 'error', 'json' );
			}
		} else {
			kekezu::keke_show_msg ( '', $_lang['no_trans_not_to_order'] . $transname, 'error', 'json' );
		}
		$uid == $order_info ['order_uid'] and $user_type = '2' or $user_type = '1'; 
		$res = keke_report_class::add_report ( 'order', $order_info ['obj_id'], $to_uid, $to_username, $desc, $report_type, $order_info ['order_status'], $order_id, $user_type, $file_name );
	}
	public static function update_fina_order($fina_id, $order_id) {
		return db_factory::execute ( sprintf ( " update %switkey_finance set order_id = '%d' where fina_id = '%d'", TABLEPRE, $order_id, $fina_id ) );
	}
	public static function get_order_status() {
		global $_lang;
		return array ("wait" => $_lang['wait_confirm'], "ok" => $_lang['has_pay'], 'fail' => $_lang['pay_fail'], "close" => $_lang['trans_close'] );
	}
	public static function get_order_obj() {
		global $_lang;
		return array ("task" => $_lang['task_trans'], "payitem" => $_lang['payitem_service'], "service" => $_lang['goods_trans'], "hosted" => $_lang['bounty_hosting'] );
	}
}