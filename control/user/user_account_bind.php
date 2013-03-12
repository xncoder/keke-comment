<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$oauth_obj = new Keke_witkey_member_oauth_class();
$api_name = keke_glob_class::get_open_api();
$oauth_url = $kekezu->_sys_config['website_url']."/index.php?do=$do&view=$view&op=$op&ac=$ac&type=$type";
$res = kekezu::get_table_data('*','witkey_member_oauth',"uid=$uid","","source",6,"source");
$url = "index.php?do=$do&view=$view&op=$op";
if (is_array ( $api_open )) {
	foreach ( $api_open as $key => $value ) {
		$value = array ("open" => $value );
		if ($res [$key]) {
			$t [$key] = array_merge ( $value, $res [$key] );
		} else {
			$t [$key] = $value;
		}
	}
}
switch ($ac) {
	case 'bind':   
		if($type){
			switch($type=="alipay_trust"){
				case true:
					$interface = "sns_bind";
					require S_ROOT."/payment/alipay_trust/order.php";
					header("Location:".$request);
					break;
				case false:					
					$oa = new keke_oauth_login_class($type);
					if(!$_SESSION['auth_'.$type]['last_key']){						
						 $oauth_vericode = $oauth_vericode;
						 $oa->login($call_back,$oauth_url);						 						 
					}else{						
					   $oauth_user_info = $oa->get_login_user_info();
					}
					$is_bind = db_factory::get_count("select count(id) from ".TABLEPRE."witkey_member_oauth  where source ='$type' and oauth_id='{$oauth_user_info['account']}' and uid='$uid'");
					$is_bind and kekezu::show_msg($_lang['operate_notice'],$url,3,$_lang['account_been_bind'],'warning');
					$oauth_obj->setAccount($oauth_user_info['name']);
					$oauth_obj->setOauth_id($oauth_user_info['account']);
					$oauth_obj->setSource($type);
					$oauth_obj->setUid($uid);
					$oauth_obj->setUsername($username);
					$oauth_obj->setOn_time(time());
					$oauth_obj->create_keke_witkey_member_oauth() and kekezu::show_msg($_lang['operate_notice'],$url,2,$_lang['bind_success'],'success')  or kekezu::show_msg($_lang['operate_notice'],$url,2,$_lang['bind_fail'],'warning');
			break;
			}
		}		
	break;
	case 'unbind':  
		if(abs(intval($id))){
			switch($type=="alipay_trust"){
				case true:
					$interface = "cancel_bind";
					require S_ROOT."/payment/alipay_trust/order.php";
					header("Location:".$request);
					break;
				case false:
				   unset($_SESSION['auth_'.$type]['last_key']);
				   $oauth_obj->setId($id);
				   $oauth_obj->del_keke_witkey_member_oauth() and kekezu::show_msg($_lang['operate_notice'],$url,2,$_lang['unbind_success'],'success')  or kekezu::show_msg($_lang['operate_notice'],$url,2,$_lang['unbind_fail'],'warning') ;
				break;
			}
		}
	break;
}
require keke_tpl_class::template ( "user/" . $do ."_" . $op );
