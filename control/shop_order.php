<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
kekezu::check_login ();
$nav_active_index = 'shop';
keke_lang_class::package_init ( "shop" );
keke_lang_class::loadlang ( "info");
$sid and $sid = intval($_GET['sid']);
$item_config = keke_payitem_class::get_payitem_config ( null, null, null, 'item_id' );
if ($sid) {
	$service_info  = keke_shop_class::get_service_info($sid);
	$model_info  = $model_list [$service_info['model_id']];
    $seller_goods_num = db_factory::get_count(sprintf("select count(service_id) from %switkey_service where model_id=6 and uid=%d and service_status=2",TABLEPRE,$service_info['uid']));
	$service_info or kekezu::show_msg ( $_lang['operate_notice'], "index.php?do=shop", '1', $_lang['goods_not_exist'], 'error' );
	keke_shop_class::access_check($sid,$service_info['uid'],$service_info['model_id']);
    $shop_aid = keke_user_mark_class::get_user_aid ( $service_info['uid'], 2, null, 1 );
	$op or $op = 'buy';
	switch ($op) {
		case "buy" : 
			$owner_info = kekezu::get_user_info ( $service_info ['uid'] );
			$user_level = unserialize ( $owner_info ['seller_level'] );
			$auth_info = keke_auth_fac_class::get_submit_auth_record ( $owner_info ['uid'], 1 );
			break;
		case "confirm" : 
			keke_shop_class::create_service_order($service_info);
			break;
	}
	require keke_tpl_class::template ( "shop/order_sub" );
} else {
	kekezu::show_msg ( $_lang['operate_notice'], "index.php?do=shop", '1', $_lang['param_error'], 'error' );
}