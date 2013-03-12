<?php
defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
$ops = array ('basic', 'order', 'comm', 'mark');
in_array ( $op, $ops ) or $op = 'basic';
keke_lang_class::loadlang('public','shop');
keke_lang_class::loadlang('task_edit','task');
if ($op == 'basic') { 
	$service_obj = new service_shop_class();
	$service_info = db_factory::get_one(sprintf("select * from %switkey_service where service_id='%d'",TABLEPRE,$service_id));
	$ac_url="index.php?do=model&model_id=7&view=edit&service_id=".$service_id;
	$status_arr = $service_obj->get_service_status();
	unset($status_arr[1]);
	if($sbt_edit){
		kekezu::admin_system_log($_lang['to_witkey_service_name_is'].$service_info[title].$_lang['in_edit_operate']);
		service_shop_class::set_on_sale_num($pk['service_id'],$fds['service_status']);
		$service_obj = keke_table_class::get_instance('witkey_service');	
	    $c = $service['content'];
	    $fds=kekezu::escape($service);
	    $service['content'] = $c;
	    isset($service['is_top']) or $service['is_top'] = 0;
		$res = $service_obj->save($service,array("service_id"=>$service_id));
		kekezu::admin_show_msg($_lang['service_edit_success'],'index.php?do=model&model_id=7&view=list',2,$_lang['service_edit_success'],'success');
	}
}else{
	require S_ROOT.'/shop/'.$model_info ['model_dir'].'/control/admin/shop_misc.php';
}
require keke_tpl_class::template ( 'shop/'.$model_info['model_dir'].'/control/admin/tpl/service_edit_' .$op );