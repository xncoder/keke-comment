<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$opps = array ('change_password', "sec_code" );
in_array ( $opp, $opps ) or $opp = "change_password";
$ac_url = "index.php?do=$do&view=$view&op=$op";
$third_nav = array ("change_password" => array ($_lang['change_pwd'], $_lang['change_pwd'] ),
				   "sec_code" => array ($_lang['safe_code_set'], $_lang['change_safe_code'] ) 
				) ;
switch ($opp) {
	case "change_password" :
		if ($check_old) {
			if(md5($check_old)==$user_info['password']){
				$notice = true;
			}else{
				$notice = $_lang['pwd_enter_err'];
			}
			echo $notice;
			die();
		}
		if ($new_password) {
			$old_pass = $old_password;
			$new_pass = $new_password;
			$new_equal = $new_equal;
			if ($basic_config ['user_intergration'] != "2" && $old_pass == $new_pass) {
				kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit failure'], 'alert_error' ) ;
			} elseif ($basic_config ['user_intergration'] != "2" && md5 ( $old_pass ) != $user_info ['password']) {
				kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit failure'], 'alert_error' ) ;
			} elseif ($new_pass != $new_equal) {
				kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit failure'], 'alert_error' ) ;
			}
			$v = array ($_lang['new_pwd'] => $new_pass );
			keke_msg_class::notify_user($user_info ['uid'], $user_info ['username'], 'update_password', $_lang['change_pwd'],$v);
			$user_obj = new Keke_witkey_space_class ();
			$user_obj->setWhere ( "uid='$uid'" );
			$user_obj->setPassword ( md5 ( $new_password ) );
			$user_obj->edit_keke_witkey_space ();
			$member_obj = new Keke_witkey_member_class ();
			$member_obj->setWhere ( "uid='$uid'" );
			$member_obj->setPassword ( md5 ( $new_password ) );
			$res = $member_obj->edit_keke_witkey_member ();
			$flag = keke_user_class::user_edit ( $username, $old_password, $new_password, '', 0 ) > 0 ? 1 : 0;
			if ($flag && $res == 1){
				setcookie('rememberme','');
				unset ( $_SESSION );
				unset($_COOKIE['rememberme']);
			}
			$flag && $res == 1 ? kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit success'], 'alert_right' ) : kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit failure'], 'alert_error' ) ;
		}
		break;
	case "sec_code" :
		if ($check_old) {
			$pwd = keke_user_class::get_password ( $check_old, $user_info ['rand_code'] );
			if($pwd==$user_info['sec_code']){
				$notice = true;
			}else{
				$notice = $_lang['safe_code_enter_err'];
				CHARSET=='utf' and $notice =  kekezu::utftogbk($notice);
			}echo $notice;
			die();
		}
		if ($new_sec_code) {
			$pwd = keke_user_class::get_password ( $old_sec_code, $user_info ['rand_code'] );
			if ($pwd != $user_info ['sec_code']) {
				kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit failure'], 'alert_error' ) ;
			} elseif ($new_sec_code == $old_sec_code) {
				kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit failure'], 'alert_error' ) ;
			} elseif ($new_sec_code != $new_equal) {
				kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit failure'], 'alert_error' ) ;
			}
			$message_obj = new keke_msg_class ();
			$v = array ($_lang['safe_code'] => $new_sec_code );
			$message_obj->send_message ( $user_info ['uid'], $user_info ['username'], 'update_sec_code', $_lang['change_safe_code'], $v, $user_info ['email'], $user_info ['mobile'] );
			$user_obj = new Keke_witkey_space_class ();
			$user_obj->setWhere ( "uid='$uid'" );
			$user_obj->setSec_code ( keke_user_class::get_password ( $new_sec_code, $user_info ['rand_code'] ) );
			$res = $user_obj->edit_keke_witkey_space ();
			$res and kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit success'], 'alert_right' )  or kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit failure'], 'alert_error' ) ;
		}
		break;
}
require keke_tpl_class::template('user/user_'.$op.'_' . $opp);
