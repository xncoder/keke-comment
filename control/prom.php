<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$views = array ('index', 'task', 'talent');
in_array ( $view, $views ) and $view or $view = 'index';
$nav_active_index = "prom";
$kekezu->init_prom();
switch (isset ( $_COOKIE ['user_prom_event'] )){
	case 1:
		 $u and $url_data=$kekezu->_prom_obj->extract_prom_cookie();
		 if($u==$url_data['u']){
			 $kekezu->_prom_obj->prom_jump ( $url_data );
		 }else{
		 	$kekezu->_prom_obj->create_prom_cookie ( $_SERVER ['QUERY_STRING'] );	
		 }
		break;
	case 0:
		$u and $kekezu->_prom_obj->create_prom_cookie ( $_SERVER ['QUERY_STRING'] );
		break;
}
require "{$do}_{$view}.php";
 