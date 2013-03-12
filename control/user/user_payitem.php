<?php	defined ( 'IN_KEKE' ) or exit('Access Denied');
$ops = array ('auth', 'toolbox','promotion','payitem_task');
if($task_open==0&&$shop_open==0){
	unset($ops[1],$ops[3]);
}
if(!$auth_item_list){
	unset($ops[0]);
}
$ops = array_merge($ops);
in_array ( $op, $ops ) or $op = $ops[0];
$sub_nav =array(
			array("auth"=>array(kekezu::lang("auth"),"document"),
			   "toolbox"=>array(kekezu::lang("toolbox"),"icon16 box"),
			   "promotion"=>array(kekezu::lang("prom_make_money"),"layers-1"))
			);
if($task_open==0&&$shop_open==0){
	unset($sub_nav[0]['toolbox']);
}
if(!$auth_item_list){
	unset($sub_nav[0]['auth']);
}	
 if($user_info['user_type']!=2){
 	unset($auth_item_list['enterprise']);
 }
require 'user_' . $op . '.php';