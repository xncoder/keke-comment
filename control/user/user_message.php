<?php	defined ( 'IN_KEKE' ) or exit('Access Denied');
$ops = array('inbox','output','send','views','del','mulit_del','mulit_views');
in_array($op,$ops) or $op='inbox';
$opps= array('system','inbox');
 $msg_obj = new keke_table_class('witkey_msg');
$sub_nav=array(
			array("send"=>array( $_lang['write_message'],"doc-edit")),
			array("inbox"=>array( $_lang['inbox'],"contact-card"),
			   	"output"=>array( $_lang['outbox'],"cc")));  
$msg_type = $msg_type?$msg_type:"system";
$op=='output'&&$msg_type='output';
$op=='send'&&$msg_type='write';
$sql = "select * from ".TABLEPRE."witkey_msg where ";
$where  = "1=1 "; 
$w1 =$where." and uid<1 and to_uid=".intval($uid);
$count_system = db_factory::get_count("select count(msg_id) from ".TABLEPRE."witkey_msg where ".$w1);
$w2 =$where." and uid = ".intval($uid)." and msg_status<>1";
$count_output = db_factory::get_count("select count(msg_id) from ".TABLEPRE."witkey_msg where ".$w2);
$w3 = $where." and to_uid = ".intval($uid)." and uid>0 and msg_status<>2 ";
$count_accept =  db_factory::get_count("select count(msg_id) from ".TABLEPRE."witkey_msg where ".$w3);
switch ($msg_type){ 
	case "system":
			$where .=" and uid<1 and to_uid=".intval($uid);
		break;
	case "output":
			$where .=" and msg_status<>1 and uid = ".intval($uid);
		break;
	case "accept":
			$where .=" and msg_status<>2 and to_uid = ".intval($uid)." and uid>0";
		break;
	case "write":
			require 'user_message_send.php';
			die();
		break;
}
$k_where = $where;
$where .= " order by msg_id desc ";
$url = "index.php?do=$do&view=$view&op=$op&msg_type=$msg_type";
$page = $page ? $page : 1;
$count = db_factory::get_count("select count(msg_id) from ".TABLEPRE."witkey_msg where ".$where);
$pages = $kekezu->_page_obj->getPages ( $count, 10, $page, $url );
$data = db_factory::query($sql.$where.$pages[where]);
if($op=='mulit_del' or $op=='mulit_views'){
	$msg_id =  $ckb;
}
$p=$_GET['page'];
switch ($op) {
	case 'mulit_del':
		if($msg_id){
			foreach ($msg_id as $v){
				list($msg_id,$status) = explode(',', $v);
				if($status==0 &&$msg_type == 'output'){
					$res = db_factory::execute("update ".TABLEPRE."witkey_msg set msg_status=1 where msg_id in ($msg_id)");
				}else if($msg_type == 'accept'&&$status == 0){
					$res = db_factory::execute("update ".TABLEPRE."witkey_msg set msg_status=2 where msg_id in ($msg_id)");
				}else{
					msg_del($msg_id);
				}
			}
		}else{
			kekezu::show_msg($_lang['operate_tips'],"index.php?do=$do&view=$view&msg_type=$msg_type",1,"没有选择操作的项","alert_error");
		}
		kekezu::show_msg($_lang['operate_tips'],"index.php?do=$do&view=$view&msg_type=$msg_type&page=".$page,1,"删除成功","alert_right");
	break;
	case 'del':
		if($msg_id){
			if($msg_type == 'output'&&$_GET['msg_status'] == 0){
				$res = db_factory::execute("update ".TABLEPRE."witkey_msg set msg_status=1 where msg_id = ".$msg_id);
			}elseif ($msg_type == 'accept'&&$_GET['msg_status'] == 0){
				$res = db_factory::execute("update ".TABLEPRE."witkey_msg set msg_status=2 where msg_id = ".$msg_id);
			}else{
				msg_del($msg_id);
			}
		}else{
			kekezu::show_msg($_lang['operate_tips'],"index.php?do=$do&view=$view&msg_type=$msg_type",1,"没有选择操作的项","alert_error");
		}
		kekezu::show_msg($_lang['operate_tips'],"index.php?do=$do&view=$view&msg_type=$msg_type&page=".$page,1,"删除成功","alert_right");
	break;
	case 'mulit_views':
		if($msg_id){
			is_array($msg_id) and $msg_id = implode(",", $msg_id); 
			$msg_data = db_factory::query("select * from ".TABLEPRE."witkey_msg where msg_id in ($msg_id)");
			foreach ($msg_data as $v) {
				if($uid==$v['to_uid']&&$v['view_status']<1){
					db_factory::execute("update ".TABLEPRE."witkey_msg set view_status=1 where msg_id = ".intval($v['msg_id']));
				}
			}
		}else{
		 	kekezu::show_msg($_lang['operate_tips'],"index.php?do=$do&view=$view&msg_type=$msg_type",1,"没有选择操作的项","alert_error");
		}
		kekezu::show_msg($_lang['operate_tips'],"index.php?do=$do&view=$view&msg_type=$msg_type",1,$_lang['biaoji_success'],"alert_right");
	break;
	case "views":
		$msg_id and $msg  = $msg_obj->get_table_info("msg_id", $msg_id);	
		if($uid==$msg['to_uid']&&$msg['view_status']<1){
			db_factory::execute("update ".TABLEPRE."witkey_msg set view_status=1 where msg_id = ".intval($msg_id));
		}
		$next = db_factory::get_one($sql.$k_where.' and msg_id<'.$msg_id.' order by msg_id desc limit 0,1');
		$pre  = db_factory::get_one($sql.$k_where.' and msg_id>'.$msg_id.' order by msg_id asc limit 0,1');
		require keke_tpl_class::template ( "user/user_message_view");die();
		break; 
}
function msg_del($msg_id){
	global $msg_obj;
	$res = $msg_obj->del("msg_id",$msg_id);
}
if (isset ( $check_username ) && ! empty ( $check_username )) { 
	$res =  keke_user_class::get_user_info($check_username,1);	  
	if($res){
		echo true;
	}else{
		echo $_lang['username_not_exist'];
	} 
	die ();
}
require keke_tpl_class::template ( "user/" . $do . "_".$view."_system");