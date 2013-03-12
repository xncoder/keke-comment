<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
if (intval ( $indus_pid ) > 0) {
	$option = '<option value="">'.$_lang['please_choose_industry'].' </option>';
	$indus_info = 	kekezu::get_industry($indus_pid);  
	foreach ($indus_info as $k=>$v) {
 		$option .= '<option value=' . $v ['indus_id'] . '>' . $v['indus_name'] . '</option>';
 	}  
	echo $option;
	die ();
}
if (isset ( $code ) && $code == 'r5tv') {
	$tem_arr = array ($indus_pid );
	$indus_p_arr = kekezu::get_indus_by_index ( 1, $indus_pid );
	foreach ( $indus_p_arr [$indus_pid] as $k => $v ) {
		array_push ( $tem_arr, $v ['indus_id'] );
	}
	$indus_ids = implode ( ',', $tem_arr );
	unset ( $tem_arr );
	$skill_obj = new Keke_witkey_skill_class ();
	$skill_obj->setWhere ( "indus_id in ($indus_ids)" );
	$skill_arr = $skill_obj->query_keke_witkey_skill ();
	if (count ( $skill_arr ) == 0) {
		$option = array ($_lang['no_relation_skill']);
	} else {
		foreach ( $skill_arr as $row ) {
			$option [] = $row [skill_id] . '=>' . $row [skill_name];
		}
	}
	echo implode ( '|', $option );
	exit ();
}
 