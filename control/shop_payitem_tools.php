<?php
defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$nav_active_index = 'shop';
$uid != $service_info ['uid'] and kekezu::show_msg ( $_lang['friendly_notice'], 'index.php?do=index', 3, $_lang['you_can_not_access_this_page']);
$payitem_arr = keke_payitem_class::get_payitem_info ( 'employer', $model_list [$service_info ['model_id']] ['model_code'] );
$exist_payitem_arr = keke_payitem_class::payitem_exists ( $uid, false, '', $payitem_arr ); 
$payitem_arr_desc = unserialize ( $service_info ['payitem_time'] );
$payitem_standard = keke_payitem_class::payitem_standard (); 
if ($formhash) {
	is_array($payitem_num) or $payitem_num=array();
	if (! array_filter ( $payitem_num )) {
		kekezu::show_msg ( $_lang['friendly_notice'], 'index.php?do=service&sid='.$sid.'&view=tools', 3, $_lang['no_choose_any_tools'] );
	}
	$keys_arr = array_keys ( $payitem_arr_desc );
	$pay_item = $service_info ['pay_item'];
	foreach ( array_filter ( $payitem_num ) as $k => $v ) {
		if (intval ( $v ) > 0 && ! stristr ( $pay_item, "$k" )) {
			$pay_item = $pay_item . ",$k";
		}
		if (in_array ( $payitem_arr [$k] ['item_code'], $keys_arr )) { 
			$payitem_arr_desc [$payitem_arr [$k] ['item_code']] > time () and $payitem_arr_desc [$payitem_arr [$k] ['item_code']] = 3600 * 24 * $v + $payitem_arr_desc [$payitem_arr [$k] ['item_code']] or $payitem_arr_desc [$payitem_arr [$k] ['item_code']] = time () + 3600 * 24 * $v;
		} else { 
			db_factory::execute ( sprintf ( "update %switkey_service set point='%s',city='%s' where service_id=%d", TABLEPRE, $_POST ['point'], $province, $sid ) ); 
		}
		$cost_res = keke_payitem_class::payitem_cost ( $payitem_arr [$k] ['item_code'], $v, 'service', 'spend', $sid, $sid ); 
	}
	$pay_item = ltrim ( $pay_item, "," );
	if (strlen ( $pay_item )) {
		db_factory::execute ( sprintf ( "update %switkey_service set pay_item='%s' where service_id=%d", TABLEPRE, $pay_item, $sid ) ); 
	}
	$res = keke_payitem_class::set_payitem_time ( $payitem_arr_desc, $sid, 'service' ); 
	$res || $cost_res and kekezu::show_msg ( $_lang['friendly_notice'], "index.php?do=service&sid=$sid&view=tools", '3', '²Ù×÷³É¹¦', 'success' );
}
require keke_tpl_class::template ( "shop_payitem_tools" );
