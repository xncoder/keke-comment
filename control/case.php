<?php	defined ( 'IN_KEKE' ) or exit('Access Denied');
/**
 * ³É¹¦°¸Àý
 * @copyright keke-tech
 * @author Chen
 * @version v 1.2
 * 2012-01-11 14:10:00
 */


$nav_active_index = "case";
$page_title=$_lang['success_case'].'- '.$_K['html_title'];
$page_keyword = $_lang['success_case']. '-' .$kekezu->_sys_config['seo_keyword'];
$page_description = $_lang['success_case'] . '-' .$kekezu->_sys_config['seo_desc'];
$indus_arr = $kekezu->_indus_arr;
$model_type_arr  = keke_glob_class::get_task_type();
$sql   = ' select a.*';
$task_open and $sql.=',b.view_num,b.work_num,b.task_title,b.indus_id as b_indus_id,b.model_id as b_model_id,b.indus_pid as b_indus_pid';
$shop_open and $sql.=', c.service_id,c.views,c.title,c.indus_id as c_indus_id ,c.indus_pid as c_indus_pid,c.sale_num';
$sql.=' from '.TABLEPRE.'witkey_case a ';
$task_open and $sql.=' left join '.TABLEPRE.'witkey_task b ON a.obj_id = b.task_id ';
$shop_open or $sql.=' where a.obj_type="task" ';
$shop_open and $sql.=' left join '.TABLEPRE.'witkey_service c on  a.obj_id= c.service_id ';
$task_open or $sql.=' where a.obj_type="service" ';
$sql .= " order by a.on_time desc";
$url = "index.php?do=case&page_size=$page_size";
$page_size = 6; 
$c_sql = ' select count(case_id) as c from '.TABLEPRE.'witkey_case where 1=1 ';
$task_open and $c_sql.=' and obj_type="task" ';
$shop_open and $c_sql.=' or obj_type="service" ';

$count = db_factory::get_count($c_sql,0,null,3600);

intval($page) and $page= intval($page) or $page=1 ;
$pages = $kekezu->_page_obj->getPages ( $count, $page_size, $page, $url );
$sql .=$pages['where'];
$case_arr = db_factory::query($sql);

require keke_tpl_class::template ( $do );