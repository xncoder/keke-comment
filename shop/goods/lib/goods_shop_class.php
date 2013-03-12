<?php
keke_lang_class::load_lang_class ( 'goods_shop_class' );
class goods_shop_class {
	public static function get_order_status() {
		global $_lang;
		return array ('wait' => $_lang ['wait_buyer_pay'], 'ok' => $_lang ['buyer_haved_pay'], 'send' => $_lang ['seller_haved_service'], 'confirm' => $_lang ['trade_complete'], 'close' => $_lang ['trade_close'], 'arbitral' => $_lang ['order_arbitrate'], 'arb_confirm' => $_lang ['trade_complete'] );
	}
	public static function get_goods_status() {
		global $_lang;
		return array ("2" => $_lang ['on_shelf'], "3" => $_lang ['down_shelf']);
	}
	public static function set_service_status($service_id, $service_status) {
		self::set_on_sale_num($service_id,$service_status);
		$service_obj = new Keke_witkey_service_class ();
		if (is_array ( $service_id )) {
			$service_arr = implode ( ',', array_unique ( $service_id ) );
			$service_obj->setWhere ( 'service_id in(' . $service_arr . ')' );
		} else {
			$service_obj->setWhere ( 'service_id=' . $service_id );
		}
		$service_obj->setService_status ( $service_status );
		$res = $service_obj->edit_keke_witkey_service();
		return $res;
	}
	public static function set_on_sale_num($service_ids, $status = 2) {
		$service_ids = ( array ) $service_ids;
		$service_ids = implode ( ',', $service_ids );
		if ($service_ids) {
			$shop_ids = db_factory::query ( ' select shop_id,service_status ss from ' . TABLEPRE . 'witkey_service where service_id in ('.$service_ids.')');
			if ($shop_ids) {
				foreach($shop_ids as $v){
					$ss = intval($v['ss']);
					if($ss!=$status){
						if($status==3||($ss==2&&$status=-1)){
							$plus = -1;
						}else{
							$plus = 1;
						}
						$ids .=$v['shop_id'].',';
					}
				}
				$ids = rtrim($ids,',');
				$ids and db_factory::execute ( ' update ' . TABLEPRE . 'witkey_shop set on_sale=on_sale+'.$plus.' where shop_id in ('.$ids.')' );
			}
		}
	}
	public function dispose_order($order_id, $action) {
		global $_lang, $uid, $username, $_K, $kekezu;
		$order_info = keke_order_class::get_order_info ( $order_id ); 
		if ($order_info) {
			$s_order_link = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=user&view=witkey&op=shop&model_id=".$order_info['model_id']."&order_id=" . $order_id . "\">" . $order_info ['order_name'] . "</a>";
			$b_order_link = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=user&view=employer&op=shop&model_id=".$order_info['model_id']."&order_id=" . $order_id . "\">" . $order_info ['order_name'] . "</a>";
			if ($uid == $order_info ['order_uid'] || $uid == $order_info ['seller_uid']) {
				$service_info = keke_shop_class::get_service_info ( $order_info ['obj_id'] ); 
				if ($service_info ['service_status'] == '2') { 
					if ($action == 'delete') { 
						keke_order_class::del_order ( $order_id, '', 'json' );
					} else {
						switch ($action) {
							case "ok" : 
								$data = array (':service_id' => $service_info ['service_id'], ':title' => $service_info ['title'] );
								keke_finance_class::init_mem ( 'buy_service', $data );
								$suc = keke_finance_class::cash_out ( $order_info ['order_uid'], $order_info ['order_amount'], 'buy_service', '', 'service', $order_info ['obj_id'] );
								if ($suc) {
									keke_order_class::set_order_status ( $order_id, $action ); 
									$v_arr = array ($_lang ['user_msg'] => $order_info ['order_username'], $_lang ['action'] => $_lang ['haved_confirm_pay'], $_lang ['order_id'] => $order_id, $_lang ['order_link'] => $s_order_link );
									keke_shop_class::notify_user ( $order_info ['seller_uid'], $order_info ['seller_username'], "order_change", $_lang ['goods_order_confirm_pay'], $v_arr );
									$kekezu->init_prom ();
									if ($kekezu->_prom_obj->is_meet_requirement ( "service", $order_info ['obj_id'] )) {
										$kekezu->_prom_obj->create_prom_event ( "service", $order_info ['order_uid'], $order_info ['obj_id'], $order_info ['order_amount'] );
									}
									kekezu::keke_show_msg ( '', $_lang ['order_complete_and_comfirm_pay'], '', 'json' );
								} else {
									kekezu::keke_show_msg ( '', $_lang ['order_pay_fail_for_cash_little'] . '<br>' . $_lang ['click'] . '<a href="' . $_K ['siteurl'] . '/index.php?do=pay&order_id=' . $order_id . '" target="_blank">' . $_lang ['go_recharge'] . '</a>', 'error', 'json' );
								}
								break;
							case "close" : 
								$res = keke_order_class::order_cancel_return ( $order_id ); 
								if ($res) {
									keke_order_class::set_order_status ( $order_id, $action ); 
									$v_arr = array ($_lang ['user_msg'] => $order_info ['order_username'], $_lang ['action'] => $_lang ['close_order_have'], $_lang ['order_id'] => $order_id, $_lang ['order_link'] => $s_order_link );
									keke_shop_class::notify_user ( $order_info ['seller_uid'], $order_info ['seller_username'], "order_change", $_lang ['goods_order_close'], $v_arr );
									kekezu::keke_show_msg ( '', $_lang ['order_deal_complete_and_close'], '', 'json' );
								} else {
									kekezu::keke_show_msg ( '', $_lang ['order_deal_fail_and_link_kf'], 'error', 'json' );
								}
								break;
							case "confirm" : 
								$res = keke_order_class::set_order_status ( $order_id, $action ); 
								if ($res) {
									$model_info = $kekezu->_model_list [$order_info ['model_id']]; 
									$profit = $service_info ['profit_rate'] * $order_info ['order_amount'] / 100; 
									$data = array (':service_id' => $service_info ['service_id'], ':title' => $service_info ['title'] );
									keke_finance_class::init_mem ( 'sale_service', $data );
									keke_finance_class::cash_in ( $order_info ['seller_uid'], $order_info ['order_amount'] - $profit, '0', 'sale_service', '', 'service', $order_info ['obj_id'], $profit );
									keke_shop_class::plus_sale_num ( $order_info ['obj_id'], $order_info ['order_amount'] );
									keke_user_mark_class::create_mark_log ( $model_info ['model_code'], 2, $order_info ['order_uid'], $order_info ['seller_uid'], $order_info['obj_id'], $order_info ['order_amount'] - $profit, $order_info ['obj_id'], $order_info ['order_username'], $order_info ['seller_username'] );
									keke_user_mark_class::create_mark_log ( $model_info ['model_code'], 1, $order_info ['seller_uid'], $order_info ['order_uid'], $order_info['obj_id'], $order_info ['order_amount'], $order_info ['obj_id'], $order_info ['seller_username'], $order_info ['order_username'] );
									keke_shop_class::plus_mark_num ( $order_info ['obj_id'] );
									$kekezu->init_prom ();
									$kekezu->_prom_obj->dispose_prom_event ( "service", $order_info ['order_uid'], $order_info ['obj_id'] );
									$v_arr = array ($_lang ['user_msg'] => $order_info ['order_username'], $_lang ['action'] => $_lang ['buy_work_coplete'], $_lang ['order_id'] => $order_id, $_lang ['order_link'] => $s_order_link );
									keke_shop_class::notify_user ( $order_info ['seller_uid'], $order_info ['seller_username'], "order_change", $_lang ['work_order_complete'], $v_arr );
									kekezu::keke_show_msg ( '', $_lang ['order_deal_complete_the_order_complete'], '', 'json' );
								} else {
									kekezu::keke_show_msg ( '', $_lang ['goods_order_confirm_pay'], 'error', 'json' );
								}
								break;
							case "arbitral" : 
								$res = keke_order_class::set_order_status ( $order_id, $action ); 
								if ($res) {
									if ($uid == $order_info ['order_uid']) {
										$v_arr = array ($_lang ['user'] => $order_info ['order_username'], $_lang ['action'] => $_lang ['buyer_start_arbitrate'], $_lang ['order_id'] => $order_id, $_lang ['order_link'] => $s_order_link );
										keke_shop_class::notify_user ( $order_id ['seller_uid'], $order_info ['seller_username'], "order_change", $_lang ['sevice_order_arbitrate_submit'], $v_arr );
									} else {
										$v_arr = array ($_lang ['user_msg'] => $order_info ['seller_username'], $_lang ['action'] => $_lang ['seller_start_arbitrate'], $_lang ['order_id'] => $order_id, $_lang ['order_link'] => $b_order_link );
										keke_shop_class::notify_user ( $order_info ['order_uid'], $order_info ['order_username'], "order_change", $_lang ['work_order_submit'], $v_arr );
									}
									kekezu::keke_show_msg ( '', $_lang ['order_deal_complete_and_order_in_arbitrate'], '', 'json' );
								} else {
									kekezu::keke_show_msg ( '', $_lang ['goods_order_confirm_pay'], 'error', 'json' );
								}
								break;
						}
					}
				} else { 
					$res = keke_order_class::set_order_status ( $order_id, 'close' ); 
					keke_order_class::order_cancel_return ( $order_id ); 
					$v_arr = array ($_lang ['user_msg'] => $_lang ['system'], $_lang ['action'] => $_lang ['stop_your_order_and_your_cash_return'], $_lang ['order_id'] => $order_id, $_lang ['order_link'] => $b_order_link );
					keke_shop_class::notify_user ( $order_info ['order_uid'], $order_info ['order_username'], "order_change", $_lang ['goods_order_close'], $v_arr );
					$v_arr = array ($_lang ['user_msg'] => $_lang ['system'], $_lang ['action'] => $_lang ['stop_your_order_and_your_cash_return'], $_lang ['order_id'] => $order_id, $_lang ['order_link'] => $s_order_link );
					keke_shop_class::notify_user ( $order_info ['seller_uid'], $order_info ['seller_username'], "order_change", $_lang ['goods_order_close'], $v_arr );
					kekezu::keke_show_msg ( '', $_lang ['goods_down_shelf_and_trade_close'], 'error', 'json' );
				}
			} else {
				kekezu::keke_show_msg ( '', $_lang ['error_order_num_notice'], 'error', 'json' );
			}
		} else {
			kekezu::keke_show_msg ( '', $_lang ['no_exist_goods_order'], 'error', 'json' );
		}
	}
	public static function process_action($role = '1', $order_status) {
		global $_lang;
		$process_arr = array ();
		switch ($order_status) {
			case "wait" : 
				$process_arr ['2'] ['trans'] ['ok'] = $_lang ['order_pay']; 
				$process_arr ['2'] ['trans'] ['close'] = $_lang ['cancel_order']; 
				$process_arr ['1'] ['trans'] [''] = $_lang ['wait_pay']; 
				break;
			case "ok" : 
				$process_arr ['2'] ['after'] ['arbitral'] = $_lang ['trate_rights']; 
				$process_arr ['2'] ['trans'] ['confirm'] = $_lang ['confirm_download']; 
				$process_arr ['1'] ['trans'] [''] = $_lang ['wait_confirm']; 
				$process_arr ['1'] ['after'] ['arbitral'] = $_lang ['trate_rights']; 
				break;
			case "confirm" :
				$process_arr ['2'] ['trans'] ['download'] = $_lang ['file_download']; 
				$process_arr ['1'] ['trans'] ['download'] = $_lang ['look_file']; 
				break;
			case "close" :
				$process_arr ['2'] ['other'] ['delete'] = $_lang ['delete_order']; 
				$process_arr ['1'] ['other'] ['delete'] = $_lang ['delete_order']; 
				break;
			case "arbitral" :
				$process_arr ['2'] ['after'] [''] = $_lang ['wait_kf_deal']; 
				$process_arr ['1'] ['after'] [''] = $_lang ['wait_kf_deal']; 
				break;
		}
		return $process_arr [$role];
	}
}