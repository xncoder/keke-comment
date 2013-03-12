<?php
defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$third_nav = array ("my" => array ($_lang ['my_tools'], $_lang ['my_buy_tools'] ), "list" => array ($_lang ['service_record'], $_lang ['value_added_services_using_the_record'] ), "buy" => array ($_lang ['service_purchase'], $_lang ['purchase_value_added_services'] ) );
$shows = array ('buy', 'list', 'my' );
$ord_arr = array (" record_id desc " => $_lang ['record_num_desc'], " record_id asc " => $_lang ['record_num_asc'], " on_time desc  " => $_lang ['produce_desc'], " on_time asc " => $_lang ['produce_asc'] );
in_array ( $show, $shows ) or $show = 'buy';
switch ($show) {
	case 'buy' :
		$payitem_list = keke_payitem_class::get_payitem_config ( null, null, null, 'item_code' ); 
		$payitem_type = keke_glob_class::get_payitem_type (); 
		$payitem_standard = keke_payitem_class::payitem_standard (); 
		if ($item_code) { 
			in_array ( $item_code, array ('top', 'urgent', 'workhide', 'map' ) ) or kekezu::show_msg ( $_lang ['operate_notice'], 'index.php?do=user&view=payitem&op=toolbox', 3, $_lang ['param_error'], 'warning' );
			$item_info = $payitem_list [$item_code]; 
			$ac_url = $origin_url . "&op=$op&show=buy&item_code=" . $item_code; 
			require "control/payitem/$item_code/control/user_buy.php";
			die ();
		}
		break;
	case 'my' : 
		$payitem_config = keke_payitem_class::get_payitem_config (null, null, null, 'item_code',2);
		$p ['page'] = intval ( $page );
		$wh ['use_type'] = 'buy';
		$p ['url'] = $origin_url . "&op=$op&show=$show&wh['item_code']={$wh['item_code']}&wh['use_type']={$wh['use_type']}&ord=$ord&page=$page&p['page_size']={$p['page_size']}";
		$p ['anchor'] = "userCenter";
		$wh ['uid'] = $uid;
		$payitem_arr = keke_payitem_class::get_payitem_record ( $wh, $ord, $p );
		$payitem_list = $payitem_arr ['list'];
		$pages = $payitem_arr ['page'];
		break;
	case 'list' : 
		$payitem_config = keke_payitem_class::get_payitem_config (null, null, null, 'item_code',2);
		$p ['page'] = intval ( $page );
		$wh ['use_type'] = 'spend';
		$p ['url'] = $origin_url . "&op=$op&show=$show&wh['item_code']={$wh['item_code']}&wh['use_type']={$wh['use_type']}&ord=$ord&page=$page&p['page_size']={$p['page_size']}";
		$p ['anchor'] = "userCenter";
		$wh ['uid'] = $uid;
		$payitem_arr = keke_payitem_class::get_payitem_record ( $wh, $ord, $p );
		$payitem_list = $payitem_arr ['list'];
		$pages = $payitem_arr ['page'];
		break;
}
require keke_tpl_class::template ( "user/user_" . $op . "_" . $show );