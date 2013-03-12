<?php
defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
switch ($action) {
	case "load_sale" :
		$shop_id and $list = db_factory::query(' select price,title,service_id,pic from '.TABLEPRE.'witkey_service where shop_id = '.$shop_id.' and service_status=2 order by on_time desc limit 0,4');
		break;
}
require keke_tpl_class::template ('ajax/ajax_shop');