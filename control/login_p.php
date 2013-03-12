<?php
defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$uid and header ( "location:index.php" );
keke_lang_class::loadlang ( 'login', 'index' );
keke_lang_class::loadlang ( 'register', 'index' );
if ($is_login == 1) {
	if ($task_id) {
		$t_id = explode ( '-', $task_id );
		$_K ['refer'] = "index.php?do=task&task_id=" . $t_id [2];
	} else {
		$_K ['refer'] = "index.php?do=release";
	}
	$_K ['do'] = $do;
	$login_obj = new keke_user_login_class ();
	$user_info = $login_obj->user_login ( $txt_account, md5 ( $pwd_password ), "", 2 );
	if ($releation_id) {
		$service = 'keke_login';
		$param = array ('releation_id' => $releation_id,
						 'to_uid' => $user_info ['uid'],
						 'to_username' => $user_info ['username'],
						 'r_task_id' => intval($r_task_id),
						 'login_type'=>max($login_type,1),
						 'app_uid'=>$app_uid);
		keke_union_class::union_request ( $service, $param );
		$login_type==2 and db_factory::execute('update '.TABLEPRE.'witkey_space set `union_user`=1,`union_rid`='.$releation_id.' where uid='.$user_info['uid']);
	}
	if ($user_info) {
		$login_obj->save_user_info ( $user_info );
		header ( 'location:' . $_K ['refer'] );
	}
} elseif ($is_register == 1) {
	$_K ['do'] = $do;
	if ($task_id) {
		$_K ['refer'] = "index.php?do=task&task_id=$task_id";
	} else {
		$_K ['refer'] = "index.php?do=task&release";
	}
	$reg_obj = new keke_register_class ();
	$reg_uid = $reg_obj->user_register ( $txt_account, md5 ( $pwd_password ), $txt_email, '', 0, $pwd_password );
	$user_info = keke_user_class::get_user_info ( $reg_uid );
	if ($releation_id) {
		$service = 'keke_login';
		$param = array ('releation_id' => $releation_id,
						 'to_uid' => $user_info ['uid'],
						 'to_username' => $user_info ['username'],
						 'r_task_id' => $r_task_id,
						 'login_type'=>max($login_type,1),
						 'app_uid'=>$app_uid);
		keke_union_class::union_request ( $service, $param );
		$login_type==2 and db_factory::execute('update '.TABLEPRE.'witkey_space set `union_user`=1,`union_rid`='.$releation_id.' where uid='.$user_info['uid']);
	}
	$reg_obj->register_login ( $user_info );
}
require keke_tpl_class::template ( 'login_p' );
