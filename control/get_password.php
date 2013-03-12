<?php defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$uid and header ( "location:index.php" );
$page_title=$_lang['find_back_password'].'- '.$_K['html_title'];
$step_arr = array ('step1', 'step2');
in_array ( $get_step, $step_arr ) or $get_step = 'step1';
$api_name = keke_glob_class::get_open_api();
$j = $_K['siteurl'].'/index.php?do=get_password&get_step=step1';
switch($get_step){
    case "step1" :	
		if (kekezu::submitcheck($formhash)) {
		$user_info = kekezu::get_user_info($txt_account,true);		
		$img = new Secode_class ();
		$check_code = $img->check ( $txt_code, 1 );
		$check_code or kekezu::show_msg($_lang['friendly_notice'],"",3,$_lang['you_input_auth_code_error']);
		header ( "location:index.php?do=get_password&get_step=step2&account=$txt_account" );
	    die ();
		}
		break;
    case "step2" :
    	if (kekezu::submitcheck($formhash)) {
    		        $user_info = kekezu::get_user_info($txt_account,true);			
						$pass_info = reset_set_password($user_info);
						$v_arr = array($_lang['username']=>$user_info['username'],$_lang['website_name']=>$kekezu->_sys_config['website_name'],$_lang['password']=>$pass_info['code'],$_lang['safe_code']=>$pass_info['sec_code'] ); 
						keke_shop_class::notify_user($user_info['uid'], $user_info['username'], 'get_password', $_lang['find_back_password'],$v_arr);
						kekezu::show_msg($_lang['friendly_notice'],$j,1,$_lang['your_new_password_in_email']);			     
		} else {
			        $user_info = kekezu::get_user_info($account,true);			      
					$email_auth = db_factory::query(sprintf("select * from %switkey_auth_email where uid=%d and auth_status=1",TABLEPRE,$user_info['uid']));
					$email_auth and $email_info = $email_auth[0][email];
					$email_str=explode('@',$email_info);
					$leng = strlen($email_str[0]);
					$i = intval($leng/2);
					$re_str = '*';
					$re_str = str_pad($re_str,$leng-$i,'*',STR_PAD_LEFT);
					$email_info = substr_replace($email_info,$re_str,$i,$leng-$i);
					$kf_phone = $kekezu->_sys_config['kf_phone'];					
 		}
		break;
}
function reset_set_password($user_info){
	$code = kekezu::randomkeys(6);
	$user_code = md5($code);
	$slt = kekezu::randomkeys(6);
	$user_seccode = keke_user_class::get_password($code, $slt);
	$sql = "update %switkey_member set password = '%s' , rand_code = '%s' where uid=%d";
	$sql = sprintf($sql,TABLEPRE,$user_code,$slt,$user_info['uid']); 
	$res = db_factory::execute($sql);
	$sql = "update %switkey_space set  password = '%s' , sec_code = '%s' where uid=%d";
	$sql = sprintf($sql,TABLEPRE,$user_code,$user_seccode,$user_info['uid']);
	db_factory::execute($sql);
	$pass_info ['code'] = $pass_info ['sec_code'] = $code;
	keke_user_class::user_edit ( $user_info['username'], '', $code, '',1);
	return $pass_info; 
}
if (isset ( $check_username ) && ! empty ( $check_username )) {
	 $res =  keke_user_class::check_username ( $check_username );
	 if($res==1){
	  	echo  '用户名不存在';
	 }else{
	 	echo  1;
	 }
	 die ();
}
require keke_tpl_class::template ( $do );