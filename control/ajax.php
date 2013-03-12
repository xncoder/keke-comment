<?php defined ( 'IN_KEKE' ) or exit('Access Denied');
$_K['is_rewrite'] = 0 ;
$views = array('ajax','upload','indus','score','code','share','menu','message','file','task','shop');
in_array($view,$views) or $view ="ajax";
require 'ajax/ajax_'.$view.'.php';