<?php	defined ( 'IN_KEKE' ) or exit('Access Denied');
$ops = array ('pub', 'task', 'shop','g_pub','credit');
if($task_open==0){
	unset($ops[1]);
}
if($shop_open==0){
	unset($ops[0],$ops[2],$ops[3]);
}
$ops = array_merge($ops);
in_array($op,$ops) or $op =$ops[1];
$sub_nav = array(
	array ("pub" => array ($_lang['pub_goods'], "doc-new" )),
	array ("task" => array ($_lang['my_work'], "doc-lines-stright" ),
		  "g_pub"=>array ($_lang['g_pub'],"notepad"),
 		   "shop" => array ($_lang['sell_goods'], "box" )),
	array ("credit" => array ($_lang['credit_grade'], "cert" ))
);
if($task_open==0){
	unset($sub_nav[1]['task']);
}
if($shop_open==0){
	unset($sub_nav[0],$sub_nav[1]['shop'],$sub_nav[1]['g_pub']);
}
$op=='task' and $model_type='task' or $model_type='shop';
$model_list=kekezu::get_table_data ( '*', 'witkey_model', " model_type = '$model_type' and model_status=1", 'model_id asc ', '', '', 'model_id', 3600 );
$model_fds = array_keys($model_list);
$model_id or $model_id = intval($model_fds['0']);
switch ($op){
	case "pub":
		 header("Location:index.php?do=shop_release");
		break;
	case "task":
		require 'user_'.$view.'_'.$op.'.php';	
		break;
	case "credit":
		require 'user_credit.php';
		break;
	case "g_pub":
		require 'user_g_pub.php';
		break;
	case "shop":
		$role = 1;
		require 'user_finance_order.php';
		break;
}