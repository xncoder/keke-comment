<?php
include_once 'app_comm.php';
switch ($ac) {
	case "cate_add" :
		$title = $_lang['add_fields_cate'];
		break;
	case "submit_file" :
		if ($type == 2) {
			$title =$_lang['add_vidio_file'];
		} elseif ($type == 3) {
			$title = $_lang['add_normal_file'];
		}
		$max_size = ini_get('upload_max_filesize');
		break;
	case "secode":
		if($v){
			echo  kekezu::check_secode($v);
		}else{
			echo false;
		}
		exit();
		break;
	case 'currency':
		$curr = strtoupper($curr);
		$_SESSION['currency'] = $curr;
		die();
		break;
	case "safe_secode":
		$user_info = $kekezu->_userinfo;
		$sec_code=keke_user_class::get_password ( $sec_code, $user_info['rand_code'] );
		$refer = $_SERVER['HTTP_REFERER'];
		strrpos($refer,"#userCenter")!==FALSE and $refer=str_replace("#userCenter","&ver=1#userCenter", $refer) or $refer.="&ver=1";
		if($sec_code==$user_info['sec_code']){
			$_SESSION['check_secode_'.$user_info['uid']]=1;
			header("Location:".$refer);
		}else{
			kekezu::show_msg($_lang['operate_warn'],$refer,3,$_lang['code_input_wrong'],'warning');
		}
		break;
	case "favor":
		kekezu::set_favor($pk, $keep_type, $model_code,$obj_uid, $obj_id, $obj_name, $origin_id,'','json');
		break;
	case "edittel":  
		if($euid){
		   $space_obj =  new Keke_witkey_space_class();
		   $space_obj->setUid($euid);
		   $space_obj->setPhone($tel);
		   echo  $space_obj->edit_keke_witkey_space();
		}else{
		  echo false;
		}
		exit();
		break;
	case 'del_att':   
			if (kekezu::get_table_data('file_id',"witkey_file","file_id = '$fid'")){
				$b = kekezu::del_att_file($fid);
				if($b){
					kekezu::echojson($_lang['delete_success'],'1');die();
				}else {
					kekezu::echojson($_lang['delete_fail'],0);die();
				}
			}
	case 'select_pic':
		$title =$_lang['edit_user_pic'];
		kekezu::keke_require_once(S_ROOT . './keke_client/ucenter/client.php');
		require keke_tpl_class::template('select_pic');
		exit();
		break;
}
require $template_obj->template ( 'ajax/ajax' );