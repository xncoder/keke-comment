<?php	defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
kekezu::admin_check_role ( 28 );
$config_tpl_obj = new Keke_witkey_template_class ();
$tpl_arr = $config_tpl_obj->query_keke_witkey_template ();
$skins    = get_skin_type();
function get_skin_type(){
	$skins = array();
	if($fp = opendir(S_ROOT.SKIN_PATH.'/theme')){
		while($skin=readdir($fp)){
			$skin = str_replace(array('.','svn'),'',$skin);
			$skin&&$skins[$skin] = $skin.'_skin';
		}
	}
	return array_filter($skins);
}
if ($sbt_edit) {	
	if ($sbt_edit == $_lang['use']) {		
		$config_tpl_obj->setWhere ( 'tpl_id=' . $rdo_is_selected );
		$config_tpl_obj->setIs_selected ( 1 );
		$res = $config_tpl_obj->edit_keke_witkey_template ();
		if(is_array($skin)&&!empty($skin)){
			foreach($skin as $k=>$v){
				db_factory::execute(sprintf(" update %switkey_template set tpl_pic ='%s' where tpl_title='%s'",TABLEPRE,$v,$k));
			}
		}
		$config_tpl_obj = new Keke_witkey_template_class ();
		$config_tpl_obj->setWhere ( 'tpl_id!=' . $rdo_is_selected );
		$config_tpl_obj->setIs_selected ( 2 );
		$res = $config_tpl_obj->edit_keke_witkey_template ();
		$config_tpl_obj->setWhere ( " is_selected =1 limit 1 " );
		$config_tpl_arr = $config_tpl_obj->query_keke_witkey_template ();
		if ($res) {
			$kekezu->_cache_obj->del ( "keke_witkey_template" );
			$kekezu->_cache_obj->set ( "keke_witkey_template", $config_tpl_arr );
			kekezu::admin_show_msg ( $_lang['tpl_config_set_success'], 'index.php?do=config&view=tpl',3,'','success' );
		}
	}
	if ($sbt_edit == $_lang['from_dir_install'] || $sbt_edit == 'uploadreturn') {
		if (! $txt_newtplpath) {
			kekezu::admin_show_msg ( $_lang['not_enter_dir'], 'index.php?do=config&view=tpl',3,'','warning' );
		}
		if (file_exists ( S_ROOT . "./tpl/$txt_newtplpath/modinfo.txt" )) {
			$file_obj = new keke_file_class ();
			$modinfo = $file_obj->read_file ( S_ROOT . "./tpl/$txt_newtplpath/modinfo.txt" );
			$mods = explode ( ';', $modinfo );
			$modinfo = array ();
			foreach ( $mods as $m ) {
				if (! $m)
					continue;
				$m1 = explode ( '=', trim ( $m ) );
				$modinfo [$m1 ['0']] = $m1 ['1'];
			}
			$txt_newtplpath!=$modinfo['tpl_path'] and kekezu::admin_show_msg($_lang['tpl_path_do_not_match']."tpl/$txt_newtplpath/modinfo.txt",'index.php?do=config&view=tpl',3,'','warning');
			$config_tpl_obj->setWhere ( "tpl_path ='$txt_newtplpath'" );
			if ($config_tpl_obj->count_keke_witkey_template ()) {
				kekezu::admin_show_msg ( $_lang['tpl_alerady_install'], 'index.php?do=config&view=tpl',3,'','warning' );
			}
			$config_tpl_obj->setDevelop ( $modinfo ['develop'] );
			$config_tpl_obj->setOn_time ( time () );
			$config_tpl_obj->setTpl_path ( $txt_newtplpath );
			$config_tpl_obj->setTpl_title ( $modinfo ['tpl_title'] );
			$config_tpl_obj->setTpl_desc ( $modinfo ['tpl_desc'] );
			$config_tpl_obj->setIs_selected(1);
			$config_tpl_obj->create_keke_witkey_template ();
			kekezu::admin_show_msg ( $_lang['tpl_install_success'], 'index.php?do=config&view=tpl',3,'','success' );
		} else {
			kekezu::admin_show_msg ( $_lang['tpl_not_exists_or_configinfo_err'], 'index.php?do=config&view=tpl',3,'','warning' );
		}
	}
	if ($sbt_edit == $_lang['local_upload']) {
		$upload_obj = new keke_upload_class ( UPLOAD_ROOT, array ("zip" ), UPLOAD_MAXSIZE );
		$files = $upload_obj->run ( 'uploadtplfile', 1 ); 
		if ($files != 'The uploaded file is Unallowable!') {
			$mod_file = $files ['0'] ['saveName'];
			if ($mod_file) {
				$mod_file = "data/uploads/" . UPLOAD_RULE . $mod_file;
			}
		}
		$file_obj = new keke_file_class ();
		$dirs = array ();
		$fso = opendir ( "../../tpl" );
		while ( $flist = readdir ( $fso ) ) {
			if (is_dir ( "../../tpl/" . $flist )) {
				$dirs [$flist] = $flist;
			}
		}
		closedir ( $fso );
		include '../../lib/helper/keke_zip_class.php';
		$zip_obj = new zip_file ( "../../" . $mod_file );
		$zip_obj->set_options ( array ('inmemory' => 1 ) );
		$zip_obj->extractZip ( "../../" . $mod_file, '../../' );
		unlink ( "../../" . $mod_file );
		$fso = opendir ( "../../tpl" );
		while ( $flist = readdir ( $fso ) ) {
			if (is_dir ( "../../tpl/" . $flist )) {
				if (! $dirs [$flist]) {
					$newaddfile = $flist;
					break;
				}
			}
		}
	}
	if (! $newaddfile) {
		kekezu::admin_show_msg ( $_lang['tpl_upload_success'], 'index.php?do=config&view=tpl',3,'','success' );
	} else {
		kekezu::admin_show_msg ( $_lang['tpl_upload_success_install'], 'index.php?do=config&view=tpl&sbt_edit=uploadreturn&txt_newtplpath=' . $newaddfile,3,'','success' );
	}
}
if ($delid) {
	$config_tpl_obj->setWhere ( 'tpl_id=' . intval ( $delid ) );
	$res = $config_tpl_obj->del_keke_witkey_template ();
	if ($res) {
		$kekezu->_cache_obj->del ( "keke_witkey_template" );
		kekezu::admin_show_msg ( $_lang['tpl_config_unloading_success'], 'index.php?do=config&view=tpl',3,'','warning' );
	}
}
require $template_obj->template ( 'control/admin/tpl/admin_config_' . $view );