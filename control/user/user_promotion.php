<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$shows = array ('reg','pub_task','bid_task','service');
if($task_open==0){
	unset($shows[1],$shows[2]);
}
if($shop_open==0){
	unset($shows[3]);
}
$shows = array_merge($shows);
in_array($show,$shows) or $show =$shows[0];
$third_nav=array("reg"=>array($_lang['reg'], $_lang['desc_reg']),
				 "pub_task"=>array($_lang['pub_task'], $_lang['desc_pub_task']),
				 "bid_task"=>array($_lang['bid_task'], $_lang['desc_bid_task']),
				 "service"=>array($_lang['service'], $_lang['desc_service']));
if($task_open==0){
	unset($third_nav['pub_task'],$third_nav['bid_task']);
}
if($shop_open==0){
	unset($third_nav['service']);
}
$effect_mode = keke_prom_class::get_prom_rule($show);
switch ($show){
	case "reg":
		$effect_mode = keke_prom_class::get_prom_rule($effect_mode['auth_step']);
		$global_config = $kekezu->get_table_data ( '*', 'witkey_basic_config', ' type="prom"', '', '', '', 'k' );		
		break;
	case "pub_task":
	case "bid_task":
	case "service":
		$model_list = kekezu::get_table_data("model_id,model_name","witkey_model","model_status='1'","","","","model_id",3600);
		$indus_list = $kekezu->_indus_arr;
		break;
}
require keke_tpl_class::template("user/user_".$op);