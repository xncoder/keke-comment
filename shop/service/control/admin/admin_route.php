<?php
defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
keke_lang_class::package_init ( "shop" );
keke_lang_class::loadlang("service_process");
$views = array ('config','list', 'order', 'op','process' ,'edit');
$view = in_array ( $view, $views ) ? $view : "list";
require "service_$view.php";
