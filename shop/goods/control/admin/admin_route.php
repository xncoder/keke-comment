<?php
defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
keke_lang_class::package_init ( "shop" );
keke_lang_class::loadlang("goods_process");
$views = array ( 'config','list', 'order','process','edit','order_detail');
$view = in_array ( $view, $views ) ? $view : "list";
require "goods_$view.php";
