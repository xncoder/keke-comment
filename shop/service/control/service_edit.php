<?php
defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$ser_id = intval ( $ser_id );
$price_unit = keke_shop_release_class::get_price_unit ();
$service_unit = keke_shop_release_class::get_service_unit ();
if ($sbt_edit) {
	if(CHARSET=='gbk'){
		$title = kekezu::utftogbk($title);
		$content = kekezu::utftogbk($content);
		$unite_price = kekezu::utftogbk($unite_price);
	}
	$s_obj = new Keke_witkey_service_class();
	$s_obj->setWhere(" service_id='$ser_id' ");
	$s_obj->setTitle($title);
	$s_obj->setPrice($price);
	$s_obj->setUnite_price($unite_price);
	$s_obj->setPic($pic);
	$s_obj->setContent($content);
	$s_obj->setIndus_id($indus_id);
	$s_obj->setIndus_pid($indus_pid);
	$res = $s_obj->edit_keke_witkey_service();
	$res and kekezu::echojson('',1) or kekezu::echojson('',0);
} else {
	$title = $_lang ['edit_service'];
	$ext = '*.jpg;*.jpeg;*.gif;*.png;*.bmp';
	$model_list [$model_id] ['config'] && $config = unserialize ( $model_list [$model_id] ['config'] );
	$ser_info = db_factory::get_one ( sprintf ( " select floor(price) price,indus_id,indus_pid,title,unite_price,pic,
				content,service_time,unit_time from %switkey_service where service_id='%d' and uid='%d'", TABLEPRE, $ser_id, $uid ) );
	$ser_info['pic']&&$f_info = db_factory::get_one(sprintf(" select file_id,file_name from %switkey_file where obj_type='service'
					and obj_id='%d'", TABLEPRE,$ser_id) );
	$fid	   = intval($f_info['file_id']);
	$file_name = $f_info['file_name'];
}
require keke_tpl_class::template ( "shop/service/tpl/default/service_edit" );