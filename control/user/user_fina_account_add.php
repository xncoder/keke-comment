<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$user_info = kekezu::get_user_info($uid);
$step_list=array(
				"step1"=>array($_lang['step_one'], $_lang['complete_bank_account_info']), 
				"step2"=>array($_lang['step_two'], $_lang['account_setting_successful'])); 
$step or ($_SESSION['bank_zone'] and $step='step2' or $step='step1');
$ac_url=$origin_url."&op=$op&opp=$opp";
switch ($step){
	case "step1":
		if($user_info['user_type']==1){
			$real_pass=keke_auth_fac_class::auth_check("realname", $uid);
			!$real_pass and   kekezu::show_msg( $_lang['realname_auth_not_pass'] ,"index.php?do=user&view=payitem&op=auth&auth_code=realname#userCenter","3",'','warning');
		}elseif($user_info['user_type']==2){
			$enterprise_auth=keke_auth_fac_class::auth_check('enterprise', $uid);
			!$enterprise_auth  and  kekezu::show_msg( $_lang['enterprise_auth_not_pass'],"index.php?do=user&view=payitem&op=auth&auth_code=enterprise&#userCenter","3",'','warning');
		}
		$bank_arr=keke_glob_class::get_bank();
		$bank_zone=$_SESSION['bank_zone'];
		$bank_detail=$_SESSION['bank_zone_detail'];
		if($check_card){
			$card_exists=db_factory::get_count(sprintf(" select card_num from %switkey_member_bank where bind_status=1 and card_num='%s'",TABLEPRE,$check_card));
			if($card_exists){
				$notice= $_lang['this_account_has_been_bound_to_others'];
				echo $notice;
			} 
			else echo true;
			die();
		}
		if(kekezu::submitcheck($formhash)){
			if($type==1){
				$conf[real_name] ='';
			}elseif($type==2) {
				$conf[company] ='';
			}
			$conf or kekezu::show_msg( $_lang['submit_fail_retry_later'],$ac_url."&step=step3&bank_type=$bank_type#userCenter","3",'','warning');;
			$bank_obj=keke_table_class::get_instance("witkey_member_bank");
			$update_card = true;
			$bank_type==1&&$real_pass and $update_card=false;
			$update_card&&db_factory::execute(sprintf("update %switkey_space set idcard='%s' where uid='%d'",TABLEPRE,$conf['card_id'],$uid));
			$conf['uid']		 = $uid;	
			$conf['bank_address']= $province.",".$city.','.$area;
			if($type){
				if($type==2){
					$conf['bank_type']   = 1;
				}elseif($type==1){
					$conf['bank_type']   = 2;
				}
			}else{
				$conf['bank_type']   = 1;
			}
			$conf['on_time']     = time();
			$conf = kekezu::escape($conf);
			$bank_id=$bank_obj->save($conf);
			if($bank_id){
				db_factory::execute(sprintf(" update %switkey_member_bank set bind_status='1' where bank_id='%d'",TABLEPRE,$bank_id));
				unset($_SESSION['bank_zone']);
				unset($_SESSION['bank_zone_detail']);
				kekezu::show_msg($_lang['system prompt'],$ac_url."&step=step2&bank_type=$bank_type&bank_id=$bank_id#userCenter","1",$_lang['account_binding_successful'],'alert_right');
			}else{
				kekezu::show_msg( $_lang['system prompt'],$ac_url."&step=step1&bank_type=$bank_type#userCenter","1",$_lang['account_binding_fail'],'alert_error');
			}
		}
		break;
	case "step2":
		$bank_arr=keke_glob_class::get_bank();
		$bank_info=db_factory::get_one(sprintf(" select * from %switkey_member_bank where bank_id='%d' and uid='%d' and bind_status='1' ",TABLEPRE,$bank_id,$uid));
		$bank_info or kekezu::show_msg( $_lang['no_binding_account_please_bind'],"$ac_url&step=step1","3","","warning");
		break;
}
require keke_tpl_class::template ( "user/" . $do . "_" . $op."_".$opp );
