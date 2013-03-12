<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$opps = array ('basic', 'contact', 'skill', 'exp','cert' );
in_array ( $opp, $opps ) or $opp = 'basic';
$third_nav=array("basic"=>array($_lang['basics'], $_lang['realname_ect_setting']),
				"contact"=>array($_lang['contact'], $_lang['qq_msn_ect_setting']),
				"skill"=>array( $_lang['witkey_skills'], $_lang['skills_setting']),
				"exp"=>array( $_lang['personal_exp'], $_lang['exp_ect_description']),
				"cert"=>array( $_lang['skill_cert'], $_lang['skill_cert_setting']));
$ac_url = $origin_url . "&op=$op";
$ext_obj=new Keke_witkey_member_ext_class();
switch ($opp) {
	case "basic" :
		$real_pass=keke_auth_fac_class::auth_check("realname", $uid);
		$user_info['residency'] = explode(',',$user_info['residency']);
		if($sbt_edit){
			$space_obj = keke_table_class::get_instance ( 'witkey_space' );
			$province&&$city and $conf['residency']=$province.','.$city;
			$res=$space_obj->save($conf,$pk);
			$res and kekezu::show_msg ( $_lang['basics_edit_success'], $ac_url . "&opp=$opp", '3','','success' ) or kekezu::show_msg ( $_lang['basics_edit_fail'], $ac_url . "&opp=$opp", '3','','warning' );
		}
		break;
	case "contact" :
		$space_obj = keke_table_class::get_instance ( 'witkey_space' );
		$sect_info = kekezu::get_table_data ( "*", "witkey_member_ext", " type='sect' and uid='$uid' ", "", "", "", "k" );
		if ($sbt_edit) {
			$conf and $res = $space_obj->save ( $conf, $pk );
			if ($sect) {
				foreach ( $sect as $k => $v ) {
					if($sect_info[$k])
						db_factory::execute (sprintf(" update %switkey_member_ext set v1='%s' where k='%s' and uid='%d'",TABLEPRE,$v,$k,$uid));
					else{
						$ext_obj=new Keke_witkey_member_ext_class();
						$ext_obj->_ext_id=null;
						$ext_obj->setK($k);
						$ext_obj->setV1($v);
						$ext_obj->setUid($uid);
						$ext_obj->setType('sect');
						$ext_obj->create_keke_witkey_member_ext();
					} 
				}
			}
			kekezu::show_msg ( $_lang['contact_edit_success'], $ac_url . "&opp=$opp", '3','','success' );
		}
		break;
	case "skill" :
		$user_skill = $user_info['skill_ids'];
		$user_info['indus_id'] and $user_indus=db_factory::get_one(sprintf(" select * from %switkey_industry where indus_id='%d'",TABLEPRE,$user_info['indus_id']));
		$indus_p_arr = $kekezu->_indus_p_arr;
		switch ($ac){
			case "get_skill":
				$skill_arr = kekezu::get_skill();
				$get_skill=$skill_arr[$indus_id];
				if($get_skill){
					kekezu::echojson('1','1',$get_skill);
				}else{
					kekezu::echojson('1','0');
				}die();
				break;
			case "save_skill":
				$skill = kekezu::unescape($skill);
				$sql = sprintf("update %switkey_space set skill_ids = '%s' where uid = '%d'",TABLEPRE,$skill,$uid);
				$res = db_factory::execute($sql);
				$res and kekezu::echojson('1') or kekezu::echojson('0');
				break;
		}
		break;
	case "cert":
		$cert_info=db_factory::query(sprintf(" select * from %switkey_member_ext where uid = '%d' and type='cert'",TABLEPRE,$uid));
		if($ac=='del'){
			$cert_id = intval($cert_id);
			if($cert_id){
				$res=db_factory::execute(sprintf(" delete from %switkey_member_ext where ext_id= '%d' ",TABLEPRE,$cert_id));
				db_factory::execute(sprintf(" delete from %switkey_file where file_id='%d'",TABLEPRE,$f_id));
				if($res){
					kekezu::del_att_file($f_id);
					kekezu::echojson( $_lang['delete_success'],"1") ;die();
				}else{
					kekezu::echojson( $_lang['unknow_error_delete_fail'],"0");die();
				}  
			}else{
				kekezu::echojson( $_lang['delete_fail_select_null'],'0');die();
			}
		}
		elseif($ac=="upload"){
			$ext_obj->_ext_id=null;
			$ext_obj->setUid($uid);
			$ext_obj->setV1(kekezu::utftogbk($v1));
			$ext_obj->setV2($v2);
			$ext_obj->setV3($v3);
			$ext_obj->setType('cert');
			$ext_id=$ext_obj->create_keke_witkey_member_ext();
			if($ext_id){
				kekezu::echojson( $_lang['congratulations_save_succeed'],$ext_id) ;die();
			}else{
				kekezu::echojson( $_lang['error_save_fail'],"0") ;die();
			}
		}
		break;
	case "exp" :
		$exp_info = kekezu::get_table_data ( "*", "witkey_member_ext", " type='exp' and uid='$uid' " );
		$ext_obj=keke_table_class::get_instance("witkey_member_ext");
		$today = date("Y-m-d",time());
		switch ($ac){
			case "del":
				$res=$ext_obj->del('ext_id', $ext_id);
				if($res){
					kekezu::echojson( $_lang['delete_success'],"1");die();
				}else{
					kekezu::echojson( $_lang['delete_fail'],"0");die();
				}
				$res and kekezu::show_msg ( $_lang['personal_exp_delete_success'], $ac_url . "&opp=$opp", '3','','success') or kekezu::show_msg ( $_lang['personal_exp_delete_fail'], $ac_url . "&opp=$opp", '3','','warning' );
				break;
			case "edit":
				if($sbt_add){
					if($ext_id){
						$exp['v4']=time();
						$pk['ext_id']=$ext_id;
						$exp = kekezu::escape($exp);
						$res=$ext_obj->save($exp,$pk);
						$res and kekezu::show_msg ( $_lang['personal_exp_edit_success'], $ac_url . "&opp=$opp", '3','','success' ) or kekezu::show_msg ( $_lang['personal_exp_edit_fail'], $ac_url . "&opp=$opp", '3','','warning' );
					}else{
						kekezu::show_msg ( $_lang['personal_exp_edit_fail_select_null'], $ac_url . "&opp=$opp", '3','','warning' );
					}
				}
				break;
			case "add":
				if($sbt_add){
					if($exp){
						$exp['uid']=$uid;
						$exp['type']='exp';
						$exp['v4']=time();
						$exp = kekezu::escape($exp);
						$res=$ext_obj->save($exp);
						$res and kekezu::show_msg ( $_lang['personal_exp_add_success'], $ac_url . "&opp=$opp", '3','','success' ) or kekezu::show_msg ( $_lang['personal_exp_add_fail'], $ac_url . "&opp=$opp", '3','','warning' );
					}	
				}
				break;
		}
		break;
}
require keke_tpl_class::template ( "user/" . $do . "_" . $op . "_" . $opp );