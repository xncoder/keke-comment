<?php
defined ( 'ADMIN_KEKE' )or exit ( 'Access Denied' );
keke_lang_class::package_init('admin');
keke_lang_class::loadlang('admin_{$do}_$view');
$views = array ('account','gettask','posttask','getlist','postlist','finance','prompub');
$view = (! empty ( $view ) && in_array ( $view, $views )) ? $view : 'account';
if (file_exists ( ADMIN_ROOT . 'admin_'.$do.'_' . $view . '.php' )) {
	require S_ROOT.'/keke_client/keke/keke_service_class.php';
	include S_ROOT.'/keke_client/keke/config.php';
    $gate = keke_service_class::$_GATE;
    if($view!='account'&&!$config['application']){
    	exit('<div style="text-align:center">联盟应用未启用!<div>');
    }else{
		require_once ADMIN_ROOT . 'admin_'.$do.'_' . $view . '.php';
    }
} else {
	kekezu::admin_show_msg ($_lang['404_page'],'',3,'','warning');
}