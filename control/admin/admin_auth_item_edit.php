<?php defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
$auth_code or kekezu::admin_show_msg ( $_lang['error_param'], "index.php?do=auth",3,'','warning');
if ($sbt_edit){
	$big_icon = $hdn_big_icon;
	$small_before_icon = $hdn_small_before_icon;
	$small_after_icon = $hdn_small_after_icon;
	keke_auth_fac_class::edit_item($auth_code, $fds,$pk,$big_icon,$small_after_icon,$small_before_icon);
}
kekezu::admin_system_log($_lang['edit_auth'] . $auth_code);
if($auth_code!='weibo') 
	require  $template_obj->template('control/admin/tpl/admin_'. $do .'_'. $view);
else 
	require  S_ROOT.'./auth/'.$auth_item['auth_dir'].'/control/admin/auth_config.php';
function get_fid($path){
	if(!path){
		return false;
	}
	$querystring = substr(strstr($path, '?'), 1);
	parse_str($querystring, $query);
	return $query['fid'];
}