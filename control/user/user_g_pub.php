<?php
defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
/**
 * 商品管理
 * @copyright keke-tech
 * @author Chen
 * @version v 2.0
 * 2011-10-9 12:10
 */
if ($model_id) {
	/**
	 * 统计
	 */
	$count_arr = kekezu::get_table_data ( "model_id,count(service_id) count", "witkey_service", " model_id IN(6,7) and uid='$uid'", "", "model_id", "", "model_id", 3600 );
	/**
	 * 三级横向菜单
	 */
	$third_nav = array ();
	foreach ( $model_list as $v ) {
		$third_nav [] = array ("1" => $v ['model_id'], "2" => $s . $v ['model_name'], "3" => $count_arr [$v ['model_id']] ['count'] );
	}
	intval ( $page_size ) or $page_size = '10';
	intval ( $page ) or $page = '1';
	$url = $origin_url . "&op=$op&model_id=$model_id&page_size=$page_size&status=$status&page=$page";
	$model_id = intval ( $model_id );
	$model_code = $model_list [$model_id] ['model_code'];
	switch ($model_code) {
		case "goods" :
			$status_arr = goods_shop_class::get_goods_status (); //商品状态数组
			break;
		case "service" :
			$status_arr = service_shop_class::get_service_status (); //商品状态数组
			break;
	}
	if (isset ( $ac )) {
		$ser_id = intval ( $ser_id );
		if ($ser_id) {
			switch ($ac) {
				case "del" :
					$res = db_factory::execute ( sprintf ( " delete from %switkey_service where service_id='%d'", TABLEPRE, $ser_id ) );
				    if($res){
				    	$res1 = db_factory::execute(sprintf("update %switkey_shop set on_sale = on_sale-1 where uid=$uid",TABLEPRE));
				        $res1 and kekezu::show_msg ( $_lang ['operate_notice'], $url, 1, $_lang ['g_delete_success'], 'alert_right' ) or kekezu::show_msg ( $_lang ['operate_notice'], $url, 1, $_lang ['g_delete_fail'], 'alert_error' );
				    }
					
					break;
				case "edit" :
					$url = "$origin_url&op=$op&model_id=$model_id&ac=edit&ser_id=$ser_id";
					require S_ROOT . '/shop/' . $model_code . '/control/' . $model_code . '_edit.php';
					break;
				case "check" :
					$res = db_factory::get_count ( sprintf ( " select a.order_id from %switkey_order a left join 
					%switkey_order_detail b on a.order_id=b.order_id where a.model_id='%d' and b.obj_id='%d'
					 and b.obj_type='service' and a.order_status not in ('close','arb_confirm')", TABLEPRE, TABLEPRE, $model_id, $ser_id ) );
					$res and kekezu::echojson ( '', 1 ) or kekezu::echojson ( '', 0 );
					die ();
					break;
			}
		} else {
			kekezu::show_msg ( $_lang ['operate_notice'], $url, 3, $_lang ['please_choose_delete_sid'], "alert_error" );
		}
	}
	/**排序数组**/
	$ord_arr = array (" service_id desc " => $_lang ['service_id_desc'], " service_id asc " => $_lang ['service_id_asc'], " on_time desc " => $_lang ['pub_time_desc'], " on_time asc " => $_lang ['pub_time_asc'] );
	$page_obj = $kekezu->_page_obj; //分页对象
	$s_obj = new Keke_witkey_service_class ();
	
	$where = " model_id = '$model_id' and uid= '$uid'";
	
	/**搜索条件 start**/
	($service_status === '0') and $where .= " and service_status='$service_status'" or ($service_status and $where .= " and service_status = '$service_status' ");
	$service_id && $service_id != $_lang ['please_input_service_id'] and $where .= " and service_id = '$service_id' ";
	$ord and $where .= " order by $ord " or $where .= " order by service_id desc ";
	/**搜索条件 end**/
	
	$s_obj->setWhere ( $where );
	$count = intval ( $s_obj->count_keke_witkey_service () );
	
	$pages = $page_obj->getPages ( $count, $page_size, $page, $url, '#userCenter' );
	
	$s_obj->setWhere ( $where . $pages ['where'] );
	
	$s_info = $s_obj->query_keke_witkey_service ();
}
require keke_tpl_class::template ( "user/" . $do . "_" . $op );