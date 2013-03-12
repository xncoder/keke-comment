<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$url = "index.php?do=space&member_id=$member_id&view=statistic";
$credit_level = unserialize ( $member_info ['seller_level'] );
$saler_aid = keke_user_mark_class::get_user_aid ( $member_id, '2', null, '1' );
$sql = sprintf ( "SELECT sum(sale_num)as num FROM %switkey_service where uid=%d", TABLEPRE, $member_id );
$pub_service = db_factory::query( $sql );
$pub_service_num = $pub_service['0']['num'];
 $sql = sprintf ( "select * from %switkey_order where model_id in(6,7) and seller_uid=%d", TABLEPRE, $member_id ); 
$buy_service_num = db_factory::execute ( $sql ); 
$page or $page = 1;
$page_size or $page_size=10;
$p_url = "index.php?do=space&member_id=$member_id&view=statistic&come=$come";
$page_obj = $kekezu->_page_obj;
$page_obj->setAjax(1);
$page_obj->setAjaxDom('ajax_dom');
$page_obj->setAjaxCove(1);
if($isajax){
	switch ($sx){
		case 'good':
			if($come=='gz'||empty($come)){
				$result = keke_user_mark_class::get_user_mark($member_id,1,1,1);
			}else{
				$result = keke_user_mark_class::get_user_mark($member_id,2,2,1);
			}
 			break;
		case 'middle':
			if($come=='gz'||empty($come)){
				$result = keke_user_mark_class::get_user_mark($member_id,1,1,2);
			}else{
				$result = keke_user_mark_class::get_user_mark($member_id,2,2,2);
			}
			break;
		case 'bad':
			if($come=='gz'||empty($come)){
				$result = keke_user_mark_class::get_user_mark($member_id,1,1,3);
			}else{
				$result = keke_user_mark_class::get_user_mark($member_id,2,2,3);
			}
			break;
		case 'all':
			if($come=='gz'||empty($come)){
				$result = keke_user_mark_class::get_user_mark($member_id,1,1);
			}else{
				$result = keke_user_mark_class::get_user_mark($member_id,2,2);
			}
			break;
	}
	$p_url .="&isajax=true&sx=$sx";
	$pages = $page_obj->page_by_arr($result, $page_size, $page, $p_url);
	$result = $pages['data'];
	if($m_ajax)
	{
		require keke_tpl_class::template ( SKIN_PATH . "/space/{$type}_{$view}" );
	}else {
 		require keke_tpl_class::template ( SKIN_PATH . "/space/p_sx" );
	}
	die;
}
if($come=='gz'||empty($come)){
	$result = keke_user_mark_class::get_user_mark($member_id,1);
}else{
	$result = keke_user_mark_class::get_user_mark($member_id,2);
}
$pages = $page_obj->page_by_arr($result, $page_size, $page, $p_url);
$result = $pages['data'];
require keke_tpl_class::template ( SKIN_PATH . "/space/{$type}_{$view}" );
