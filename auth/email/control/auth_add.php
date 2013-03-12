<?php
defined ( 'IN_KEKE' ) or exit('Access Denied');
$page_title= $_lang['email_auth'];
if($del_auth_id){
	$email_obj = new Keke_witkey_auth_email_class();
	$email_obj->setWhere("email_a_id = " .intval($del_auth_id));
	$email_obj->del_keke_witkey_auth_email(); 
}
$step_arr=array("step1"=>array($_lang['step_one'], $_lang['input_email_address']),
				"step2"=>array($_lang['step_two'], $_lang['auth_email']),
				"step3"=>array($_lang['step_three'], $_lang['auth_result']));
$auth_step= keke_auth_email_class::get_auth_step($auth_step,$auth_info);
$ac_url = $origin_url . "&op=$op&auth_code=$auth_code&ver=".intval($ver);
switch ($auth_step){
	case "step1":
		break;
	case "step2":
		preg_match("/@(.*)/u",$auth_info['email'],$matches);
		$mail_ext=$matches[1];
		if($resend){
			$succ=$auth_obj->send_mail($auth_info['email_a_id'],$auth_info);
			$succ and kekezu::echojson( $_lang['send_success_confirm_as_soon'],"1") or 
			kekezu::echojson( $_lang['email_send_fail'],"0");
			die();
		}
		if($send_mail){
			if(email_exists($email)){
			    kekezu::echojson($_lang['email_already_auth'],0);
			}
			$succ=$auth_obj->add_auth($email);
			$succ and kekezu::echojson( $_lang['send_success_confirm_as_soon'],"1") or kekezu::echojson( $_lang['email_send_fail'],"0");			
			die();
		}
		break;
	case "step3":
			$email_a_id&&$ac=='check_email' and $auth_obj->audit_auth($active_code,$email_a_id);
		break;
}
function email_exists($email){
	global $uid;
	$sql = sprintf('select count(*) from  %switkey_auth_email where email = \'%s\' and auth_status = 1 and uid != \'%d\'',TABLEPRE,$email,$uid);
	return (bool) db_factory::get_count($sql);
}
if($auth_info['auth_status']==1&&$auth_step=='step2'){
	$auth_step = 'step3';
}
if($auth_info['auth_status']==1){
	$auth_tips =$_lang['congratulations_pass_email_auth'];
	$auth_style = 'success';
}elseif($auth_info['auth_status']==2){
	$auth_tips =$_lang['regrettably_not_pass_email_auth'];
	$auth_style = 'warning';
}
require keke_tpl_class::template ( 'auth/' . $auth_dir . '/tpl/' . $_K ['template'] . '/auth_add' );