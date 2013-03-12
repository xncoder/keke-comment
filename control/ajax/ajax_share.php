<?php	defined ( 'IN_KEKE' ) or exit('Access Denied');
$ops = array('site','center');
in_array($op,$ops) or $op='site';
$sina_app_id = $kekezu->_sys_config['sina_app_id'];
$sohu_app_id = $kekezu->_sys_config['sohu_app_id'];
$seo_title = $kekezu->_sys_config['seo_title'];
intval($oid) or $oid = null;
switch ($op){
	case 'site':
		break;
	case 'center':
		$title = $_lang['task_share'];
		$tid = intval($task_id);
		if($tid){
			$plats = $kekezu->_api_open;
				unset($plats['taobao']);
			$apis  = keke_glob_class::get_open_api();
			$url   = $_K['siteurl'].'/index.php?do=task&task_id='.$tid;
			$share_title = db_factory::get_count(sprintf(' select task_title from %switkey_task where task_id=%d',TABLEPRE,$tid));
		}
		break;
}
if($share_title){
	$share_title .= "@".$kekezu->_sys_config['website_name'];
}else{
	$share_title = $title.'-'.$kekezu->_sys_config['website_name'];
}
strtolower(CHARSET)=='gbk' and $utitle = urlencode(kekezu::gbktoutf($share_title)) or $utitle = urlencode($share_title);
require $kekezu->_tpl_obj->template ( 'ajax/ajax_share' );