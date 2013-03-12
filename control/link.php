<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$link_obj = new Keke_witkey_link_class();
$link_obj->setWhere(" link_pic !='0'  order by listorder asc");
$pic_link_arr = $link_obj->query_keke_witkey_link();
$link_obj->setWhere("  link_pic ='0'  order by listorder asc");
$word_link_arr = $link_obj->query_keke_witkey_link();
require keke_tpl_class::template ( $do );