<?php defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
if(!isset($status)){
	$status = 404;
}
require  keke_tpl_class::template ($do);