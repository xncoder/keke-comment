<?php
defined ( 'IN_KEKE' ) or exit('Access Denied');
$page_title= $_lang['enterprise_auth'];
$step_arr=array("step1"=>array( $_lang['step_one'], $_lang['fill_in_enterprise_info']),
				"step2"=>array( $_lang['step_two'], $_lang['background_exam_and_verify']),
				"step3"=>array( $_lang['step_three'], $_lang['auth_pass']));
$auth_step= keke_auth_enterprise_class::get_auth_step($auth_step,$auth_info);
$verify   = 0;
$ac_url = $origin_url . "&op=$op&auth_code=$auth_code&ver=".intval($ver);
switch ($auth_step){
	case "step1":
		break;
	case "step2":				 
		if(isset($formhash)&&kekezu::submitcheck($formhash))
		{
		$type = array('image/png','image/jpg','image/jpeg','image/gif','image/pjpeg');
		in_array($_FILES["licen_pic"]["type"],$type)? $ext=1:$ext=0;
		if($_FILES["licen_pic"]["size"]>4*1024*1024||$ext==0)
		  {
		   	kekezu::show_msg ( $_lang['upload_error'], $ac_url,"1", $_lang['pic_max_error'], "error" );		  	
		  }
		  else
		  {
		   $auth_obj->add_auth($fds,'licen_pic');
		  }
		} 
		break;
	case "step3":
		break;
}
if($auth_info['auth_status']==1){
	$auth_tips =$_lang['congratulations_pass_enterprise_auth'];
	$auth_style = 'success';
}elseif($auth_info['auth_status']==2){
	$auth_tips =$_lang['regrettalby_not_pass_enterprise_auth'];
	$auth_style = 'warning';
}else{
	$auth_tips =$_lang['please_wait_patiently'];
	$auth_style = 'warning';
}
if($auth_info['auth_status']==1 and  $auth_step=='step2'){
	$auth_step = 'step3';
}
require keke_tpl_class::template ( 'auth/' . $auth_dir . '/tpl/' . $_K ['template'] . '/auth_add' );