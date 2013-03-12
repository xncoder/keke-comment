<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );

/**
 * 商城大厅
 * @copyright keke-tech
 * @author Monkey
 * @version v 2.0
 * 2010-8-11上午08:15:51
 */


$_K['is_rewrite'] = 0;
$nav_active_index = 'shop';
$page_title=$_lang['shop_map'].'- '.$_K['html_title'];
$page_keyword = $_lang['shop_map']. '-' .$kekezu->_sys_config['seo_keyword'];
$page_description = $_lang['shop_map'] . '-' .$kekezu->_sys_config['seo_desc'];

/*初始化信息*/
keke_lang_class::package_init ( "shop_map" );
keke_lang_class::loadlang ( $do );
$feed_time = time()-3600*24; 
$dynamic_arr = kekezu::get_feed(" feedtype='pub_task' or feedtype='work_accept' and $feed_time>feed_time ", "feed_time desc", 10); //动态信息
$website_url = "index.php?" . $_SERVER ['QUERY_STRING'];
//当前连接
 $task_indus_type = kekezu::get_industry (0);
 //获取行业分类 
 
$task_cash_arr =   keke_search_class::get_cash_cove();
//任务赏金数组 
$indus_all_arr = $kekezu->_indus_arr;
//所有行业的数组
$where_arr = get_where_arr();
if($area){
$area = str_replace("slt_city", "", $area);
 $map_city = $province.','.$city.','.$area;
}

 
/*分页*/
 $sql = "select a.* from " . TABLEPRE . "witkey_service as a where FIND_IN_SET('4',a.pay_item) and  point is not null and ";  
 $where = get_where ( $path );
 
 unset($indus_id);  
$url = "index.php?do=shop_list&page_size=$page_size&min=$min&max=$max&path=$path";
$page_size = intval ( $page_size ) ? intval ( $page_size ) : 10;
$count = db_factory::execute ( $sql . $where );
$page = $page ? $page : 1;
$pages = $kekezu->_page_obj->getPages ( $count, $page_size, $page, $url ); 
$where .= $pages ['where'];  
//数组
 
$service_arr = db_factory::query ( $sql . $where );  
$check_arr =  keke_search_class::get_path_url( $where_arr, $path );
//生成链接 
$check_url_arr = $check_arr ['url'];
$check_all = $check_arr ['all'];
$select_arr = $check_arr['selected'];
//已选择的条件  
$cookie_arr = unserialize ( $_COOKIE ['shop_map'] );
//获取cookie数组
$cookie_arr = str_replace("&hid_save_cookie=1", "", $cookie_arr);
($hid_save_cookie||$path=='H2') and  keke_search_class::save_cookie($cookie_arr, $website_url, $select_arr,$hid_save_cookie,$search_key,'shop_map');
	
//清空历史记录
if ($hid_del_cookie) {
	$res = setcookie ( 'shop_map', '' );
	$res and kekezu::echojson ( '', 1 );
	die();
}
 
/**
 * 
 *	获取查询条件
 * @param string $path
 */
function get_where($path) {
global $task_cash_arr, $search_key,$min,$max,$ord,$indus_id,$map_city;
global $_lang;
	$where = "a.service_status!=1 and a.point >0  ";
	$map_city and $where .="  and a.city like '%".$map_city."%' ";
	$url_info = keke_search_class::get_analytic_url($path);
	$indus_id and $where .=sprintf(" and a.indus_id = %d",$indus_id);
	$url_info ['A'] and $where .= sprintf ( " and a.indus_pid = %d", $url_info ['A'] ); 
	//任务所属行业 
	$url_info ['C'] and $where .= sprintf ( " and a.model_id = %d", $url_info ['C'] ); 
	//任务类型
	!$_COOKIE['kekeshop_map_search_cash']&&$url_info ['B'] and $where .= kekezu::get_between_where('a.price', $task_cash_arr [$url_info ['B']] ['min'], $task_cash_arr [$url_info ['B']] ['max'] ); //获取赏金  
 
	//发布时间
	switch ($url_info ['D']) {
		case 1 :
			$where .= " and DATE_SUB(CURDATE(),INTERVAL  3 day) <= date(from_unixtime(a.on_time)) ";
			break;
		case 2 :
			$where .= " and DATE_SUB(CURDATE(),INTERVAL 7 day) <= date(from_unixtime(a.on_time)) ";
			break; 
		case 3 :
			$where .= " and DATE_SUB(CURDATE(),INTERVAL 30 day) <= date(from_unixtime(a.on_time)) ";
			break; 
		case 4 :
			$time = time () + 24 * 3600;
			$where .= " and a.on_time<$time ";
			break; 
	} 
	if($_COOKIE['kekeshop_map_search_cash']){
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
	$ord or $where .= " order by a.on_time desc"; 

	return $where;
}

/**
 * 
 *查询条数组
 */
function get_where_arr(){ 
	global $_K,$task_indus_type,$search_key;
	global $_lang;
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
 
 


if($_K['map_api']=='baidu'){
	$points = ' var point = new Array();
	';$arr_marker='';$addverlay='';
	$infoWindow=' var infoWindow = new Array();
	';$addEventListener='';
	$map_script='';
	foreach ($service_arr as $k=>$v) {
		$v['user_pic'] = keke_user_class::get_user_pic($v['uid']);
		$v['start_time'] = kekezu::time2Units(time()-$v['on_time']) ; 
		$point = explode(',',$v ['point']);
		$v['point'] = $point['1'].','.$point['0'];
		$points .= ' point['.$k.'] = new BMap.Point(' . $v ['point'] . ');
		';
		$arr_marker .= 'new BMap.Marker(point[' . $k . ']),';
		$addverlay .= 'map.addOverlay(marker[' . $k . ']);';
		$infoWindow .= ' infoWindow['.$k.'] = new BMap.InfoWindow("<div class=basic_style map_info><div class=fl_l mr_10><a target=_blank  href=index.php?do=space&member_id='.$v['uid'].'>'.$v['user_pic'].'</a></div><strong><a  target=_blank href=index.php?do=service&sid='.$v['service_id'].'   target=_blank>'.$v['title'].'</a></strong><div class=font12><a href=index.php?do=space&member_id='.$v['uid'].' target=_blank  class=font12>'.$v['username'].'</a></b>&nbsp;&nbsp;'.$v['start_time']. $_lang['front_release'] .'</div></div>");
		';
		$addEventListener .= 'marker[' . $k . '].addEventListener("mouseover", function(){this.openInfoWindow(infoWindow[' . $k . ']);});';
	}
	$k_baidu_api = $_K['baidu_api'];
	$map_script.=<<<END
	<script type="text/javascript" src="$k_baidu_api"></script>
	<script type="text/javascript">
	var map = new BMap.Map("container");
	map.enableScrollWheelZoom();
	map.addControl(new BMap.NavigationControl());  
	map.addControl(new BMap.ScaleControl());  
	map.addControl(new BMap.OverviewMapControl());  
	map.addControl(new BMap.MapTypeControl());
	local_search('$province,$city,$area');
	//map.centerAndZoom(new BMap.Point(113.937226, 30.937862), 15);
	$points
	var marker = [$arr_marker];
	$addverlay
	map.setViewport(point);
	$infoWindow
	$addEventListener
	function openMyWin(id){
	map.openInfoWindow(infoWindow[id],point[id]);
	}
	
	function local_search(address){
		var options = {onSearchComplete: function(results){  
		   if (local.getStatus() == BMAP_STATUS_SUCCESS){  	
				
				map.centerAndZoom(results.getPoi(0).point,15);   
		   }
		 }  
		};
		var local = new BMap.LocalSearch(map, options);									
		local.search(address);
	}
</script>
END;

	}else{
	foreach ($service_arr as $k=>$v) {
	$v['user_pic'] = keke_user_class::get_user_pic($v['uid']);
	$v['start_time'] = kekezu::time2Units(time()-$v['start_time']) ;
	$arr_point .= 'new google.maps.LatLng(' . $v ['point'] . '),';
	$arr_marker .= '  new google.maps.Marker({ position: point[' . $k . '], map: map}),';
	$arr_infoWindow .= 'new google.maps.InfoWindow({content:"<div class=basic_style map_info><div class=fl_l mr_10><a target=_blank  href=index.php?do=space&member_id='.$v['uid'].'>'.$v['user_pic'].'</a></div><strong><a  target=_blank href=index.php?do=task&task_id='.$v['task_id'].'   target=_blank>'.$v['task_title'].'</a></strong><div class=font12><a href=index.php?do=space&member_id='.$v['uid'].' target=_blank  class=font12>'.$v['username'].'</a></b>&nbsp;&nbsp;'.$v['start_time']. $_lang['front_release'] .'</div></div>"}),';
	$addEventListener.='google.maps.event.addListener(marker['.$k.'],"mouseover", function() { clearOverlays();infoWindow['.$k.'].open(map,marker['.$k.']);});';
}
    $k_google_api = $_K['google_api'];
	$map_script.=<<<END
	<script type="text/javascript" src="$k_google_api"></script>
	<script type="text/javascript">
	var myOptions = {center: new google.maps.LatLng(30.937862, 113.937226),	zoom: 6,mapTypeId: google.maps.MapTypeId.ROADMAP};
	var map = new google.maps.Map(document.getElementById('container'),myOptions);
	var point = [$arr_point];
	var marker = [$arr_marker];
	var infoWindow = [$arr_infoWindow];
	$addEventListener

	function clearOverlays() {
	if (infoWindow) {
	for (var i=0;i<infoWindow.length;i++) {
	infoWindow[i].close();
}
}
}

function openMyWin(id){
clearOverlays();
infoWindow[id].open(map,marker[id]);
}
</script>
END;


}

require  keke_tpl_class::template ( $do);
