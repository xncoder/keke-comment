<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$opps = array ('basic', 'contact', 'skill', 'exp', 'cert' );
in_array ( $opp, $opps ) or $opp = 'basic';
$user_type = intval($user_info['user_type']);
$third_nav = array ("basic" => array ($_lang['basics'], $_lang['realname_ect_setting'] ), "contact" => array ($_lang['contact_type'], $_lang['qq_msn_ect_setting'] ), "skill" => array ($_lang['witkey_skills'], $_lang['skills_setting'] ) );
if($user_type){
	switch ($user_type){
		case 1:
			$third_nav ["exp"] = array ($_lang['personal_exp'], $_lang['exp_ect_description'] );
			$third_nav ["cert"] = array ($_lang['skill_cert'], $_lang['skill_cert_setting'] );
			break;
		case 2:	
			$enter_info = db_factory::get_one ( sprintf ( "select * from %switkey_auth_enterprise where uid='%d'", TABLEPRE, $uid ) );
			$third_nav ["cert"] = array ($_lang['Qualifications_cert'], $_lang['skill_cert_setting'] );
			break;
	}
}
$ac_url = $origin_url . "&op=$op";
$ext_obj = new Keke_witkey_member_ext_class ();
switch ($opp) {
	case "basic" :
		$user_type == 2 and $real_pass = keke_auth_fac_class::auth_check ( 'enterprise', $uid ) or $real_pass = keke_auth_fac_class::auth_check ( "realname", $uid );
		if (isset($formhash)&&kekezu::submitcheck($formhash)) {
			$conf['summary'] = htmlspecialchars($conf['summary']);
			$space_obj = keke_table_class::get_instance ( 'witkey_space' );
			$res = $space_obj->save ( $conf, $pk );
			if ($user_type == 2||$conf['user_type']==2) {
				$fds['uid'] = $uid;
				$fds['username'] = $username;
				$enter_obj = new keke_table_class ( 'witkey_auth_enterprise' );
				$res = $enter_obj->save ( $fds, array ('enterprise_auth_id' => $enter_info ['enterprise_auth_id'] ) );
			}
			if($from)
			{
			kekezu::show_msg ( $_lang['system prompt'], $origin_url."&op=space", '1', $_lang['submit success'], 'alert_right' ) ;				
			}
			else
			{
			kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit success'], 'alert_right' ) ;
			}
		}
		break;
	case "contact" :
		$loca= explode ( ',', $user_info ['residency'] );
		$space_obj = keke_table_class::get_instance ( 'witkey_space' );
		$sect_info = kekezu::get_table_data ( "*", "witkey_member_ext", " type='sect' and uid='$uid' ", "", "", "", "k" );
			if (isset($formhash)&&kekezu::submitcheck($formhash)) {
			$province && $city and $conf ['residency'] = $province . ',' . $city.','.$area;
			$conf and $res = $space_obj->save ( $conf, $pk );
			if ($sect) {
				foreach ( $sect as $k => $v ) {
					if ($sect_info [$k])
						db_factory::execute ( sprintf ( " update %switkey_member_ext set v1='%s' where k='%s' and uid='%d'", TABLEPRE, $v, $k, $uid ) );
					else {
						$ext_obj = new Keke_witkey_member_ext_class ();
						$ext_obj->_ext_id = null;
						$ext_obj->setK ( $k );
						$ext_obj->setV1 ( kekezu::escape ( $v ) );
						$ext_obj->setUid ( $uid );
						$ext_obj->setType ( 'sect' );
						$ext_obj->create_keke_witkey_member_ext ();
					}
				}
			}
			kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit success'], 'alert_right' ) ;
		}
		$auth = keke_auth_fac_class::auth_check(array('mobile','email'),$uid);
		$auth = kekezu::get_arr_by_key($auth,'auth_code');
		break;
	case "skill" :
		$user_skill = $user_info ['skill_ids'];
		$user_info ['indus_id'] and $user_indus = db_factory::get_one ( sprintf ( " select * from %switkey_industry where indus_id='%d'", TABLEPRE, $user_info ['indus_id'] ) );
		$indus_p_arr = $kekezu->_indus_p_arr;
		switch ($ac) {
			case "get_skill" :
				$skill_arr = kekezu::get_skill ();
				isset($skill_arr [$indus_id]) and  $get_skill = $skill_arr [$indus_id];
				if (isset($get_skill)&&$get_skill) {
					kekezu::echojson ( '1', '1', $get_skill );
				} else {
					kekezu::echojson ( '1', '0' );
				}
				die ();
				break;
			case "save_skill" :
				$skill = kekezu::unescape ( $skill );
				$sql = sprintf ( "update %switkey_space set skill_ids = '%s' where uid = '%d'", TABLEPRE, $skill, $uid );
				$res = db_factory::execute ( $sql );
				$res and kekezu::echojson ( '1' ) or kekezu::echojson ( '0' );
				break;
		}
		break;
	case "cert" :
		$cert_info = db_factory::query ( sprintf ( " select * from %switkey_member_ext where uid = '%d' and type='cert'", TABLEPRE, $uid ) );
		if ($ac == 'del') {
			$cert_id = intval ( $cert_id );
			if ($cert_id) {
				$res = db_factory::execute ( sprintf ( " delete from %switkey_member_ext where ext_id= '%d' ", TABLEPRE, $cert_id ) );
				if ($res) {
					kekezu::del_att_file ( $f_id );
					kekezu::echojson ( $_lang['delete_success'], "1" );
					die ();
				} else {
					kekezu::echojson ( $_lang['unknow_error_delete_fail'], "0" );
					die ();
				}
			} else {
				kekezu::echojson ( $_lang['delete_fail_select_null'], '0' );
				die ();
			}
		} elseif ($ac == "upload") {
			$ext_obj->_ext_id = null;
			$ext_obj->setUid ( $uid );
			CHARSET == 'gbk' and $v1 = kekezu::utftogbk ( $v1 );
			$ext_obj->setV1 ( kekezu::escape ( $v1 ) );
			$ext_obj->setV2 ( $v2 );
			$ext_obj->setV3 ( $v3 );
			$ext_obj->setType ( 'cert' );
			$ext_id = $ext_obj->create_keke_witkey_member_ext ();
			if ($ext_id) {
				kekezu::echojson ( $_lang['congratulations_save_succeed'], $ext_id );
				die ();
			} else {
				kekezu::echojson ( $_lang['error_save_fail'], "0" );
				die ();
			}
		}
		break;
	case "exp" :
		$exp_info = kekezu::get_table_data ( "*", "witkey_member_ext", " type='exp' and uid='$uid' " );
		$ext_obj = keke_table_class::get_instance ( "witkey_member_ext" );
		$today = date ( "Y-m-d", time () );
		switch ($ac) {
			case "del" :
				$res = $ext_obj->del ( 'ext_id', $ext_id );
				if ($res) {
					kekezu::echojson ( $_lang['delete_success'], "1" );
					die ();
				} else {
					kekezu::echojson ( $_lang['delete_fail'], "0" );
					die ();
				}
				$res and kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit success'], 'alert_right' )  or  kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit failure'], 'alert_error' )  ;
				break;
			case "edit" :
				if (isset($formhash)&&kekezu::submitcheck($formhash)) {
					if ($ext_id) {
						$exp ['v4'] = time ();
						$pk ['ext_id'] = $ext_id;
						$exp = kekezu::escape ( $exp );
						$res = $ext_obj->save ( $exp, $pk );
						$res and kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit success'], 'alert_right' )  or  kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit failure'], 'alert_error' )  ;
					} else {
						 kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit failure'], 'alert_error' )  ;
					}
				}
				break;
			case "add" :
				if (isset($formhash)&&kekezu::submitcheck($formhash)) {
					if ($exp) {
						$exp ['uid'] = $uid;
						$exp ['type'] = 'exp';
						$exp ['v4'] = time ();
						$exp = kekezu::escape ( $exp );
						$res = $ext_obj->save ( $exp );
						$res and kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit success'], 'alert_right' )  or  kekezu::show_msg ( $_lang['system prompt'], $ac_url . "&opp=$opp", '1', $_lang['submit failure'], 'alert_error' )  ;
					}
				}
				break;
		}
		break;
}
require keke_tpl_class::template ( "user/" . $do . "_" . $op . "_" . $opp );