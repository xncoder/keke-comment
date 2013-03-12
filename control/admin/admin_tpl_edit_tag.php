<?php	defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
kekezu::admin_check_role (29);
$indus_arr = kekezu::get_industry (); 
$art_cat_arr = kekezu::get_table_data("*","witkey_article_category","","","","","art_cat_id",null);
$url = "index.php?do=tpl&view=edit_tag&tagid=$tagid";
$template_arr = db_factory::query ( " select tpl_title from " . TABLEPRE . "witkey_template" );
$tag_type_arr = keke_glob_class::get_tag_type ();
$task_status = get_task_status ();
function get_task_status() {
	global $_lang;
	return array ("0" => $_lang['task_no_pay'], "1" => $_lang['task_wait_audit'], "2" => $_lang['task_vote_choose'], "3" => $_lang['task_choose_work'], "4" => $_lang['task_vote'], "5" => $_lang['task_gs'], "6" => "½»¸¶", "7" => $_lang['freeze'], "8" => $_lang['task_over'], "9" => $_lang['fail'], "10" => $_lang['task_audit_fail'], "11" => $_lang['arbitrate'], '12' => $_lang['assure_return_cash'] );
}
$tag_obj = new Keke_witkey_tag_class ();
if ($tagid) {
	$tag_obj->setWhere ( "tag_id='{$tagid}'" );
	$taginfo = $tag_obj->query_keke_witkey_tag ();
	$taginfo = $taginfo ['0'];
}
if ($submit) {
	$txt_tagname or kekezu::admin_show_msg ($_lang['enter_tag_name'], $url,3,'','warning' );
	$tag_obj2 = new Keke_witkey_tag_class ();
	$tag_obj2->setWhere ( "tagname = '{$txt_tagname}' and tag_id!='$tagid'" );
	if ($tag_obj2->query_keke_witkey_tag ()) {
		kekezu::admin_show_msg ($_lang['tag_name_inuse_please_replace'], $url,3,'','warning' );
	}
	$tag_obj->setTagname ( $txt_tagname );
	$tag_obj->setTag_type ( $tag_type );
	$tag_obj->setTask_indus_id ( $slt_task_indus_id );
	$tag_obj->setTask_ids ( $txt_task_indus_ids );
	if ($slt_task_type > 0) {
		$tag_obj->setTask_type ( $slt_task_type );
	}
	$tag_obj->setTask_status ( $slt_task_status );
	$txt_start_time1 = $txt_start_time1 ? kekezu::sstrtotime ( $txt_start_time1 ) : 0;
	$tag_obj->setStart_time1 ( $txt_start_time1 );
	$txt_start_time2 = $txt_start_time2 ? kekezu::sstrtotime ( $txt_start_time2 ) : 0;
	$tag_obj->setStart_time2 ( $txt_start_time2 );
	$txt_end_time1 = $txt_end_time1 ? kekezu::sstrtotime ( $txt_end_time1 ) : 0;
	$tag_obj->setEnd_time1 ( $txt_end_time1 );
	$txt_end_time2 = $txt_end_time2 ? kekezu::sstrtotime ( $txt_end_time2 ) : 0;
	$tag_obj->setEnd_time2 ( $txt_end_time2 );
	$tag_obj->setLeft_day ( $txt_left_day ? $txt_left_day : 0 );
	$tag_obj->setLeft_hour ( $txt_left_hour ? $txt_left_hour : 0 );
	$tag_obj->setTask_cash1 ( $txt_task_cash1 ? $txt_task_cash1 : 0 );
	$tag_obj->setTask_cash2 ( $txt_task_cash2 ? $txt_task_cash2 : 0 );
	$tag_obj->setProm_cash1 ( $txt_prom_cash1 ? $txt_prom_cash1 : 0 );
	$tag_obj->setProm_cash2 ( $txt_prom_cash2 ? $txt_prom_cash2 : 0 );
	$tag_obj->setCache_time ( $txt_cache_time ? $txt_cache_time : 0 );
	$tag_obj->setUsername ( $txt_username );
	$tag_obj->setOpen_is_top ( $rdo_open_is_top );
	$tag_obj->setArt_cat_id ( $slt_art_cat_id );
	$tag_obj->setArt_cat_ids ( $txt_art_cat_ids );
	$txt_art_time1 = kekezu::sstrtotime ( $txt_art_time1 ) ? kekezu::sstrtotime ( $txt_art_time1 ) : 0;
	$tag_obj->setArt_time1 ( $txt_art_time1 );
	$txt_art_time2 = kekezu::sstrtotime ( $txt_art_time2 ) ? kekezu::sstrtotime ( $txt_art_time2 ) : 0;
	$tag_obj->setArt_time2 ( $txt_art_time2 );
	$tag_obj->setArt_ids ( $txt_art_ids );
	$tag_obj->setArt_iscommend ( $ckb_art_iscommend ? 1 : 0 );
	$tag_obj->setArt_hasimg ( $ckb_art_hasimg ? 1 : 0 );
	$tag_obj->setCat_type ( $rdo_cat_type );
	$temp = $rdo_cat_type == 2 ? $slt_art_cat_cat_id : $slt_task_cat_cat_id;
	$tag_obj->setCat_cat_id ( $temp );
	$tag_obj->setCat_cat_ids ( $txt_cat_cat_ids );
	$tag_obj->setCat_loadsub ( $cat_loadsub ? 1 : 0 );
	$tag_obj->setCat_onlyrecommend ( $cat_onlyrecommend ? 1 : 0 );
	$tag_obj->setTag_sql ( $tar_custom_sql  );
	if ($tag_type == 6) {
		$code =$model_id;
	} else {
		$code = $tar_custom_code;
	}
	$tag_obj->setCode ( $code );
	$tag_obj->setTpl_type ( $cbk ? implode ( ",", $cbk ) : $_K ['template'] );
	$tag_obj->setTag_code ( $tag_code );
	$tag_obj->setLoadcount ( $txt_loadcount ? $txt_loadcount : 9 );
	$txt_perpage ? $tag_obj->setPerpage ( $txt_perpage ) : '';
	$tag_obj->setTplname ( $txt_tplname );
	if ($rdo_cat_type != 2) {
		$tag_obj->setListorder ( $slt_task_order );
	} else {
		$tag_obj->setListorder ( $slt_art_order );
	}
	if ($tagid) {
		$tag_obj->setWhere ( "tag_id='{$tagid}'" );
		$res = $tag_obj->edit_keke_witkey_tag ();
		$kekezu->_cache_obj->del ( "tag_list_cache" );
		kekezu::admin_system_log ($_lang['edit_tag'] . $tagid );
	} else {
		$res = $tag_obj->create_keke_witkey_tag ();
		kekezu::admin_system_log ($_lang['create_tag'] . $res );
	}
	$kekezu->_cache_obj->del ( 'tag_list_cache' );
	if ($res) {
		kekezu::admin_show_msg ($_lang['tag_change_success'], "index.php?do=tpl&view=taglist&tag_type=$tag_type&type=$type",3,'','success' );
	} else {
		kekezu::admin_show_msg ($_lang['tag_change_fail'], "index.php?do=tpl&view=edit_tag&tagid=$tagid&tag_type=$tag_type&type=$type",3,'','warning' );
	}
}
function indusfenglei_select($m, $id, $index) {
	global $indus_arr;
	$n = str_pad ( '', $m, '-', STR_PAD_RIGHT );
	$n = str_replace ( "-", "&nbsp;&nbsp;", $n );
	foreach ( $indus_arr as $indus ) {
		if ($indus ['indus_pid'] == $id) {
			if ($indus ['indus_id'] == $index) {
				echo "        <option value=\"" . $indus ['indus_id'] . "\" selected=\"selected\">" . $n . "|----" . $indus ['indus_name'] . "</option>\n";
			} else {
				echo "        <option value=\"" . $indus ['indus_id'] . "\">" . $n . "|--" . $indus ['indus_name'] . "</option>\n";
			}
			indusfenglei_select ( $m + 1, $indus ['indus_id'], $index );
		}
	}
}
function articlefenglei_select($m, $id, $index) {
	global $art_cat_arr;
	$n = str_pad ( '', $m, '-', STR_PAD_RIGHT );
	$n = str_replace ( "-", "&nbsp;&nbsp;", $n );
	foreach ( $art_cat_arr as $k => $v ) {
		if ($v ['art_cat_pid'] == $id) {
			if ($v ['art_cat_id'] == $index) {
				echo "        <option value=\"" . $v ['art_cat_id'] . "\" selected=\"selected\">" . $n . "|----" . $v ['cat_name'] . "</option>\n";
			} else {
				echo "        <option value=\"" . $v ['art_cat_id'] . "\">" . $n . "|--" . $v ['cat_name'] . "</option>\n";
			}
			articlefenglei_select ( $m + 1, $v ['art_cat_id'], $index );
		}
	}
}
require  $kekezu->_tpl_obj->template ( 'control/admin/tpl/admin_tpl_' . $view . '_' . $tag_type_arr [$tag_type] ['2'] );
