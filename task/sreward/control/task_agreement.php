<?php
defined ( 'IN_KEKE' ) or exit('Access Denied');
$agree_obj		 = sreward_task_agreement::get_instance($agree_id);
$agree_info		 = $agree_obj->_agree_info;
$buyer_uid       = $agree_obj->_buyer_uid;
$seller_uid       = $agree_obj->_seller_uid;
$buyer_username  = $agree_obj->_buyer_username;
$seller_username = $agree_obj->_seller_username;
$agree_status 	 = $agree_obj->_agree_status;
$buyer_status	 = $agree_obj->_buyer_status;
$seller_status	 = $agree_obj->_seller_status;
$process_can     = $agree_obj->process_can();
$user_type 		 = $agree_obj->_user_role;
$step			 = $agree_obj->stage_access_check($user_type);
$stage_nav		 = $agree_obj->agreement_stage_nav();
$basic_url		 = 'index.php?do='.$do.'&agree_id='.$agree_id.'&step='.$step;
$task_status     = $agree_obj->_trust_info['task_status'];
switch ($step){
	case "step1":
		$op == 'sign' and $agree_obj->agreement_stage_one($user_type,'','json');
		break;
	case "step2":
		$buyer_contact	   = $agree_obj->_buyer_contact;
		$buyer_status_arr  = $agree_obj->get_buyer_status();
		$seller_contact	   = $agree_obj->_seller_contact;
		$seller_status_arr = $agree_obj->get_seller_status();
		$stage_list        = $agree_obj->agreement_stage_list($user_type);
		$file_list         = $agree_obj->get_file_list();
		$trust_mode        = $agree_obj->_trust_info['is_trust'];
		switch ($op){
			case "report":
				$title=$_lang['zc_submit'];
				if($sbt_edit){
					$agree_obj->set_report ( $obj, $obj_id, $to_uid,$to_username, $type, $file_url, $tar_content);
				}else{
					require keke_tpl_class::template("report");
				}die();
				break;
			case "confirm":
				$agree_obj->upfile_confirm($file_str,$basic_url);
				break;
			case "accept":
				$agree_obj->accept_confirm('','json');
				break;
		}
		break;
	case "step3":
		switch ($op){
			case "mark":
				$title = $_lang['each_mark'];
				$model_code = $agree_obj->_model_code;
				$obj_id     = $agree_info['work_id'];
				$role_type = $user_type;
				require S_ROOT.'control/mark.php';
				die();
				break;
		}
	case "step4":
		switch ($op){
			case "report":
				$title=$_lang['zc_submit'];
				if($sbt_edit){
					$agree_obj->set_report ( $obj, $obj_id, $to_uid,$to_username, $type, $file_url, $tar_content);
				}else{
					require keke_tpl_class::template("report");
				}die();
				break;
		}
		break;
}
$page_title=$agree_info['agree_title'].'--'.$_K['html_title'];
require keke_tpl_class::template("task/".$model_info['model_dir']."/tpl/".$_K['template']."/agreement/agreement_".$step);
