<?php  defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$nav_active_index = 'shop';
$page_title=$_lang['weike_shop'].'- '.$_K['html_title'];
$rs_indus  = kekezu::get_classify_indus('shop','total');
 $clean_industry_arr = array();
 kekezu::get_tree($rs_indus, $clean_industry_arr, '');
 !$status && $status = 'hot' ;
 $fields = 'service_id,pic,ad_pic,leave_num,title,content,price,sale_num';
 $table = 'witkey_service';
 $where = 'service_status=2';
 switch ($status){
 	case 'latest' :
 		$order = 'on_time desc';
 		break;
 	case 'highprice' :
 		$order = 'price desc';
 		break;
 	case 'hot' :
 		$order = 'views desc';
 }
 $services_list = $kekezu -> get_table_data($fields, $table, $where, $order, '', '0,16', 'service_id', 60*60);
 $top2 = array_splice($services_list,0,2);
$sql = " select count(order_id) from %switkey_order where model_id in(6,7) and order_status in ('ok','accept','send') ";
$count_record = db_factory::get_count ( sprintf($sql,TABLEPRE));
require $kekezu->_tpl_obj->template ('shop');
 