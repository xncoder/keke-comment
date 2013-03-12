<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$action = $action?$action:"pub";
$member_id = intval($member_id); 
$kekezu->_page_obj->setAjax(1);
$kekezu->_page_obj->setAjaxDom('task_list');
if($action == 'pub'){ 
	$sql ="select * from ".TABLEPRE."witkey_task where ";
	$where = " uid=$member_id and task_status!=0 and task_status!=1";
	$ord   = max($ord,1);
	$ord==1 and $where .=" order by start_time desc" or $where .=" order by start_time asc" ;
	$url = "index.php?do=space&member_id=$member_id&view=task&page_size=$page_size&action=$action&ord=$ord";
	$page_size = 15;
	$count = db_factory::execute ( $sql.$where );
	$page = $page ? $page : 1;
	$pages = $kekezu->_page_obj->getPages ( $count, $page_size, $page, $url );
	$where .=$pages['where']; 
	$task_arr = db_factory::query($sql.$where);
}elseif($action == 'join'){
	$sql ="select a.work_id,b.* from ".TABLEPRE."witkey_task_work as a left join ".TABLEPRE."witkey_task as b on a.task_id = b.task_id ";
	$where = " where a.uid = $member_id group by b.task_id";
	$ord==1 and $where .=" order by b.start_time desc" or $where .=" order by b.start_time asc" ;
	$url = "index.php?do=space&member_id=$member_id&view=task&page_size=$page_size&action=$action&ord=$ord";
	$page_size = 15;
	$count = db_factory::execute ( $sql.$where );
	$page = $page ? $page : 1;
	$pages = $page_obj->getPages ( $count, $page_size, $page, $url );
	$where .=$pages['where']; 
	$task_arr = db_factory::query($sql.$where);
}
$cash_cove = kekezu::get_cash_cove('',true);
require keke_tpl_class::template ( SKIN_PATH . "/space/{$type}_{$view}" );
