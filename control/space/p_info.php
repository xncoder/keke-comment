<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$skill_obj = new Keke_witkey_member_ext_class();
$indus_arr =  $kekezu->_indus_arr ;
 $skill_obj->setWhere(" uid = ".intval($member_id)." and type='cert' order by ext_id desc ");
 $skill_info = $skill_obj->query_keke_witkey_member_ext();
 foreach ($skill_info as $k=>$v) {
	$v['v1'] = preg_replace("/\..*/", "",  $v['v1']);
 	$skill_desc_arr[$k] = $v; 
 }
$skill_obj->setWhere("uid = ".intval($member_id)." and type='exp' order by ext_id desc limit 0, 5");
$skill_exp_arr = $skill_obj->query_keke_witkey_member_ext();
$sect_info = kekezu::get_table_data ( "*", "witkey_member_ext", " type='sect' and uid='$member_id' ", "", "", "", "k" );
require keke_tpl_class::template(SKIN_PATH."/space/{$type}_{$view}");
