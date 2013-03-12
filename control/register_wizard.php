<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$uid or kekezu::show_msg($_lang['friendly_notice'],'index.php',3,$_lang['access_message'],'warning');
$step_arr = array(1,'p2','e2',3);
if(!isset($step)){
	$step = 1;
} 
$page_title=$_lang['perfect_data'].$_lang['one_step'].'-'.$_lang['choose_identity'].'-'.$_K['html_title'];
$space_obj = new keke_table_class('witkey_space');
$ac_url = 'index.php?do=register_wizard';
$user_info['residency'] = explode(',',$user_info['residency']);
$user_type = intval($user_info['user_type']);
if(($step=='e2'||$step=='p2')&&$user_type>0){		
	$user_type==2 and $step='e2' or $step = 'p2';	
}
switch ($step) {
	case "e2":  
		$page_title=$_lang['perfect_data'].$_lang['two_step'].'-'.$_lang['fill_data'].'-'.$_K['html_title'];
		$enter_info = db_factory::get_one(sprintf("select * from %switkey_auth_enterprise where uid='%d'",TABLEPRE,$uid));
		$real_pass=keke_auth_fac_class::auth_check("enterprise", $uid);
		$refer = isset($refer)?$refer:"index.php";
		if (isset($formhash)&&kekezu::submitcheck($formhash)) {
			$enter_obj = new keke_table_class('witkey_auth_enterprise');
			$fds['uid']=$uid;
			$fds['username']=$user_info['username'];
			$fds['auth_status']=3;
			$enter_obj->save($fds,array('enterprise_auth_id'=>$enter_info['enterprise_auth_id']));			
			$province&&$city and $conf['residency']=$province.','.$city;
			$space_obj->save($conf,$pk);
			header ( "location:index.php?do=register_wizard&step=3&type=e" );				
		}		
	break; 
	case "p2" :	
		$page_title=$_lang['perfect_data'].$_lang['two_step'].'-'.$_lang['fill_data'].'-'.$_K['html_title'];	
		$real_pass=keke_auth_fac_class::auth_check("realname", $uid);
		$user_skill = isset($user_skill)?$user_skill:"";
		if(isset($formhash)&&kekezu::submitcheck($formhash)){	
			$province&&$city and $conf['residency']=$province.','.$city.','.$area;
			$conf['skill_ids'] = kekezu::unescape($skills);					
			$res=$space_obj->save($conf,$pk);
			header ( "location:index.php?do=register_wizard&step=3&type=p" );
		 }
	break; 	
	case "3":
		$page_title=$_lang['perfect_data'].$_lang['three_step'].'-'.$_lang['data_success'].'-'.$_K['html_title'];
	break;
}
require keke_tpl_class::template ( "register_wizard_$step" );