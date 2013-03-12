<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$ac_url = $origin_url . "&op=credit";
keke_lang_class::loadlang('user_credit','user');
switch ($view) {
	case "employer" :
		$credit_level = unserialize ( $user_info ['buyer_level'] );
		$saler_aid = keke_user_mark_class::get_user_aid ( $uid, 1, null, 1 );
		$user_type = 2;
		$witkey_result = keke_user_mark_class::get_user_mark($uid,1,1);
		break;
	case "witkey" :
		$able_level = unserialize ( $user_info ['seller_level'] );
		$buyer_aid = keke_user_mark_class::get_user_aid ( $uid, 2, null, 1 );
		$user_type = 1;
		$witkey_result = keke_user_mark_class::get_user_mark($uid,1,2);
		break;
}
$found_count = kekezu::get_table_data ( " sum(fina_cash) cash,sum(fina_credit) credit,count(fina_id) count,fina_action ", "witkey_finance", " uid='$uid' and fina_action in ('pub_task','task_bid','buy_service','sale_service') ", "", " fina_action ", "", "fina_action" );
$page or $page = 1;
$page_size or $page_size=10;
$url = "index.php?do=$do&view=$view&op=$op";
!empty($witkey_result) and $pages = $kekezu->_page_obj->page_by_arr($witkey_result, $page_size, $page, $url);
$witkey_result = $pages['data'];
require keke_tpl_class::template ( 'user/user_credit' );