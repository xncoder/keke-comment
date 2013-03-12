<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );

/**
 * 商城大厅
 * @copyright keke-tech
 * @author Monkey
 * @version v 2.0
 * 2010-8-11上午08:15:51
 */


$_K['is_rewrite']=0;
$url_info = keke_search_class::get_analytic_url($path);
$page_title = $kekezu->_indus_p_arr[$url_info[A]][indus_name].$model_list[$url_info[C]][model_name].$_lang ['shop_list'] . '-' . $_K ['html_title'];
$page_keyword = $kekezu->_indus_p_arr[$url_info[A]][indus_name].$model_list[$url_info[C]][model_name].$_lang ['shop_list'] . '-' .$kekezu->_sys_config['seo_keyword'];
$page_description = $kekezu->_indus_p_arr[$url_info[A]][indus_name].$model_list[$url_info[C]][model_name].$_lang ['shop_list'] . '-' .$kekezu->_sys_config['seo_desc'];
/*页面头文件  */ 
$nav_active_index = 'shop';
keke_lang_class::package_init ( "shop_list" );
keke_lang_class::loadlang ( $do );

/*初始化信息*/
$item_config = keke_payitem_class::get_payitem_config ( null, null, null, 'item_id' );
$feed_time = time()-3600*24;  
$dynamic_arr = kekezu::get_feed(" feedtype='pub_service'", "feed_time desc", 10); //动态信息
//$dynamic_arr = kekezu::get_feed(" feedtype='pub_task' or feedtype='work_accept' and $feed_time>feed_time ", "feed_time desc", 10 ); // 动态信息

$website_url = "index.php?" . $_SERVER ['QUERY_STRING'];
//当前连接 
$task_cash_arr = keke_search_class::get_cash_cove();
//任务赏金数组 
$task_indus_type = kekezu::get_classify_indus ('shop');
//获取行业分类 
$indus_all_arr = $kekezu->_indus_arr;
//所有行业的数组 
$where_arr = get_where_arr();
//条件大数组 

//var_dump($indus_all_arr);
/*查询*/ 
$sql = "select a.*,substring(
		payitem_time,
		instr(a.payitem_time,'top')+4+LENGTH('top'),10) as top_time from " . TABLEPRE . "witkey_service as a where "; 
$where = get_where ( $path );
//echo $where;


//unset($indus_id); 
$url = "index.php?do=shop_list&page_size=$page_size&min=$min&max=$max&path=$path";
//排序  
$page_size = intval ( $page_size ) ? intval ( $page_size ) : 20;
$count = db_factory::execute ( $sql . $where );
$page = $page ? $page : 1;
$pages = $kekezu->_page_obj->getPages ( $count, $page_size, $page, $url );
$where .= $pages ['where'];  

/*结果数组赋值*/ 

$service_arr = db_factory::query ( $sql . $where );
//商品数组
$check_arr = keke_search_class::get_path_url( $where_arr, $path );
//生成链接
 
$check_url_arr = $check_arr ['url'];
$check_all = $check_arr ['all'];
//每个索引条件的全部 
$select_arr = $check_arr['selected'];
//已选择的条件 
$cookie_arr = unserialize ( $_COOKIE ['shop_save_cookie'] );
//获取cookie数组
$cookie_arr = str_replace("&hid_save_cookie=1", "", $cookie_arr);
 
($hid_save_cookie||$path=='H2') and  keke_search_class::save_cookie($cookie_arr, $website_url, $select_arr,$hid_save_cookie,$search_key,'shop_save_cookie');
	
	 
//清空历史记录
if ($hid_del_cookie) {
	$res = setcookie ( 'shop_save_cookie', '' );
	$res and kekezu::echojson ( '', 1 );
	die();
} 
//获取查询条件
function get_where($path) {
	global $task_cash_arr, $search_key,$min,$max,$ord,$indus_id;
	$where = " (service_status='2' or service_status='5') ";
	$url_info = keke_search_class::get_analytic_url($path);
	$indus_id and $where .=sprintf(" and a.indus_id = %d",$indus_id);
	$url_info ['A'] and $where .= sprintf ( " and a.indus_pid = %d", $url_info ['A'] ); 
	//任务所属行业 

	$url_info ['C'] and $where .= sprintf ( " and a.model_id = %d", $url_info ['C'] ); 

	//任务类型
	!$_COOKIE['kekeshop_list_search_cash']&&$url_info ['B'] and $where .= kekezu::get_between_where('a.price', $task_cash_arr [$url_info ['B']] ['min'], $task_cash_arr [$url_info ['B']] ['max'] ); //获取赏金  
 
	//发布时间
	switch ($url_info ['D']) {
		case 1 :
			$where .= " and DATE_SUB(CURDATE(),INTERVAL  1 day) <= date(from_unixtime(a.on_time)) ";
			break;
		case 2 :
			$where .= " and DATE_SUB(CURDATE(),INTERVAL 3 day) <= date(from_unixtime(a.on_time)) ";
			break; 
		case 3 :
			$where .= " and DATE_SUB(CURDATE(),INTERVAL 7 day) <= date(from_unixtime(a.on_time)) ";
			break; 
		case 4 :
			$where .= " and DATE_SUB(CURDATE(),INTERVAL 30 day) <= date(from_unixtime(a.on_time)) ";
			 
			break; 
	} 
	
	if($_COOKIE['kekeshop_list_search_cash']){
		intval ( $min ) or $min = 0;
		intval ( $max ) or $max = 0;
		$min and $where .= " and a.price>'$min' ";
		$max and $where .= " and a.price < '$max' "; 
	}
	switch ($url_info ['H']) { 
		case 1 : $where .= " and a.service_id = '$search_key'"; break;
		case 2 : $where .= " and a.title like '%$search_key%'"; break;
		case 3 : $where .= " and a.username = '$search_key'"; break;
	} 
	$ord == 1 and $where .=" order by a.price asc";	
	//增值服务置顶	
	$ord ==2 and $where .=" order by a.price desc";
	$ord or $where .= " order by (CASE WHEN substring(
		payitem_time,
		instr(a.payitem_time,'top')+4+LENGTH('top'),10)>UNIX_TIMESTAMP() THEN a.on_time ELSE 0 END) desc, a.on_time desc"; 
	
	return $where;
} 

 

function get_where_arr(){
	global $task_indus_type,$search_key,$_lang;
	$where_arr = array (
		"A" => $task_indus_type, 
	//任务分类 
		"B" => array (
	//任务赏金 
			"1" => array ("name" => $_lang['task_cash_s1'] ), 
			"2" => array ("name" => "100-500" ), 
			"3" => array ("name" => "500-1000" ),
			"4" => array ("name" => "1000-5000" ),
			"5" => array ("name" => "5000-20000" ),
			"6" => array ("name" => $_lang['task_cash_s2'] ) ),
		"C" => array (
	//商品种类
			"7" => array ("name" => $_lang['service'] ),  
			"6" => array ("name" => $_lang['works_code'] ) ), 
		"D" => array (
	//发布时间
			"1" => array ("name" => $_lang['nearly_a_day'] ), 
			"2" => array ("name" => $_lang['nearly_three_day'] ), 
			"3" => array ("name" => $_lang['nearly_a_week'] ), 
			"4" => array ("name" => $_lang['nearly_a_month'] ) ),  
		
		"H" => array ( 
			"2" => array ("name" => $_lang['shop_name'] .":$search_key" ), 
	//任务标题
			"3" => array ("name" => $_lang['task_pub_people'] .":$search_key" ) ) )
	//任务发布人
		;
	
	return $where_arr;
}

 

require $kekezu->_tpl_obj->template ( $do );