<?php
defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
$order_obj = keke_table_class::get_instance('witkey_order');
$goods_obj = keke_table_class::get_instance('witkey_service');
$order_status_arr = goods_shop_class::get_order_status();
$wh = "model_id = 6";
$w['order_id'] and $wh .= " and order_id like '%$w[order_id]%' ";
$w['order_username'] and $wh.=" and order_username like '%$w[order_username]%' ";
$w['order_status'] and $wh.=" and order_status = '$w[order_status]' ";
$ord['0']&&$ord['1'] and  $wh .=' order by '.$ord['0'].' '.$ord['1'] or $wh.=" order by order_time desc";
intval ( $page ) or $page = 1;
intval ( $w['page_size'] ) and $page_size = intval ( $w['page_size'] ) or $page_size = '10';
$url_str = "index.php?do=model&model_id=6&view=order&w[order_id]={$w['order_id']}&w[order_username]={$w['order_username']}&w[order_status]={$w['order_status']}&page=$page&w[page_size]=$page_size&ord[0]=$ord[0]&ord[1]=$ord[1]";
$table_arr = $order_obj->get_grid ( $wh, $url_str, $page, $page_size, null,1,'ajax_dom');
$order_arr = $table_arr['data'];
$pages = $table_arr['pages'];
switch ($ac){
	case 'del':
		$ac_name = db_factory::get_count(sprintf("select order_name from %switkey_order where order_id='%d' ",TABLEPRE,$order_id));
		kekezu::admin_system_log($_lang['has_delete_order_name_is'].$ac_name.$_lang['of_witkey_goods_order']);
		$res = $order_obj->del('order_id', $order_id,$url_str);
		$res and kekezu::admin_show_msg($_lang['operate_notice'],$url_str,2,$_lang['delete_success'],'success') or kekezu::admin_show_msg($_lang['operate_notice'],$url_str,2,$_lang['delete_fail'],"warning");
		break;
}
if(isset ( $sbt_action )){
	$keyids = $ckb;	
	if(is_array($keyids)){
		kekezu::admin_system_log($_lang['has_mulit_delete_witkey_goods']);
		$order_obj->del('order_id',$keyids) and kekezu::admin_show_msg($_lang['operate_notice'],$url_str,2,$_lang['mulit_delete_success']) or kekezu::admin_show_msg($_lang['operate_notice'],$url_str,2,$_lang['mulit_delete_fail'],"error");
	}
}
require keke_tpl_class::template ( 'shop/' . $model_info ['model_dir'] . '/control/admin/tpl/goods_' . $view );