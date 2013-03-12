<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
/**
 * @copyright keke-tech
 * @author shang
 * @version v 2.0
 * @todo google_map for kppw2.0
 * 2011-11-19早上11:49:00
 */

$_K['is_rewrite'] = 0;
/*初始化信息  */ 
$nav_active_index = "task";
$page_title=$_lang['task_map'].'-'.$_K['html_title'];
$page_keyword = $_lang['task_map']. '-' .$kekezu->_sys_config['seo_keyword'];
$page_description = $_lang['task_map'] . '-' .$kekezu->_sys_config['seo_desc'];
keke_lang_class::package_init ( "task" );
keke_lang_class::loadlang ( $do );
$feed_time = time () - 3600 * 24; 
$dynamic_arr = kekezu::get_feed ( " feedtype='pub_task' or feedtype='work_accept' and $feed_time>feed_time ", "feed_time desc", 5 ); //动态信息
$website_url = "index.php?" . $_SERVER ['QUERY_STRING']; //当前连接
$cove_arr = kekezu::get_table_data("*","witkey_task_cash_cove","","","","","cash_rule_id");
$task_cash_arr = keke_search_class::get_cash_cove();//任务赏金数组   
$cash_coverage = kekezu::get_cash_cove('',true);
$where_arr = get_where_arr();//获取数组 

$end_time_arr = keke_glob_class::get_taskstatus_desc ();//任务时间描述
if(isset($area)&&$area){
 $area = str_replace("slt_city", "", $area);
 $map_city = $province.','.$city.','.$area;
}

$model_open = get_model();//开启的任务模型

/*分页*/
$model_where = ' 1=1 ';
if(isset($model_ids)&&$model_ids){ 
	$model_val = implode(',', array_filter(explode("M", $model_ids))); 
	$model_where =" a.model_id in ($model_val)   " ;
}else{
	$model_ids = '';
}
 
$sql = "select a.* from " . TABLEPRE . "witkey_task as a left join ".TABLEPRE."witkey_task_cash_cove d on a.task_cash_coverage=d.cash_rule_id where FIND_IN_SET('4',a.pay_item) and  point is not null  and $model_where ";


if(isset($path)&&$path){
	$where = get_where ( $path );
	$path = $path;
}/*else if(isset($map_city)){
	$where = get_where ( $path );
}*/else{
	$where = '';
	
}

 $map_city and $sql .=" and a.city like '%".$map_city."%' ";
 
$min = isset($min)?$min:'';
$max = isset($max)?$max:'';
$page_size = isset($page_size)&&intval ( $page_size ) ? intval ( $page_size ) : 10;


$url = "index.php?do=task_map&path=$path&min=$min&max=$max&model_ids=$model_ids&page_size=$page_size";

 
$count = db_factory::execute ( $sql . $where );
$page = isset($page) ? intval($page) : 1;

$pages = $page_obj->getPages ( $count, $page_size, $page, $url ); 

$path or $where .=' order by a.task_id desc ';

$task_list_arr = db_factory::query ( $sql . $where. $pages ['where'] );  

$check_arr =keke_search_class::get_path_url ( $where_arr, $path );//查询条件
$check_url_arr = $check_arr ['url'];
$check_all = $check_arr ['all']; 
$select_arr = $check_arr['selected'];  
//获取cookie数组 
$cookie_arr = isset($_COOKIE ['map_save_cookie'])?unserialize ( $_COOKIE ['map_save_cookie'] ):'';
$cookie_arr = str_replace("&hid_save_cookie=1", "", $cookie_arr);
$hid_save_cookie = isset($hid_save_cookie)?$hid_save_cookie:'';

($hid_save_cookie||$path=='H2') and  keke_search_class::save_cookie($cookie_arr, $website_url, $select_arr,$hid_save_cookie,$search_key,'map_save_cookie');
	 
//清空历史记录
if (isset($hid_del_cookie)&&$hid_del_cookie) {
	$res = setcookie ( 'map_save_cookie', '' );
	$res and kekezu::echojson ( '', 1 );
	die();
} 
//获取任务金额条件
function get_cash_where($min_cash, $max_cash) {
	$where = " and ((a.task_cash_coverage >0  and d.end_cove <= $max_cash and d.start_cove>=$min_cash  ) or (a.task_cash_coverage =0 or isnull(a.task_cash_coverage) and a.task_cash>=$min_cash and a.task_cash<$max_cash))";
  
	return $where;
}

//获取查询条件
function get_where($path) {
	global $task_cash_arr, $search_key, $min, $max,$ord,$model_ids,$indus_id,$model_open,$map_city; 
	global $_lang;
	$url_info = keke_search_class::get_analytic_url($path);
	$url_info ['F'] or $where = "  and (a.task_status=2 or a.task_status=3)"; 

 
	switch ($url_info ['F']){
		case 2:
			$where = " and a.task_status=2 ";
			break;
		case 3:
			$where = " and a.task_status=3";
			break;
		case 8:
			$where = " and a.task_status=8";
			break;
	}
	
	$indus_id and $where .=sprintf(" and   a.indus_id = %d",$indus_id);
	$url_info ['A'] and $where .= sprintf ( " and a.indus_pid = '%d'", $url_info ['A'] ); //任务所属行业 
	$url_info ['B']&&!$model_ids and $where .= sprintf ( " and a.model_id = '%d'", intval ( $url_info ['B'] ) ); //任务类型//

	! $_COOKIE ['keketask_map_search_cash'] && $url_info ['C'] and $where .= get_cash_where ( $task_cash_arr [$url_info ['C']] ['min'], $task_cash_arr [$url_info ['C']] ['max'] ); //获取赏金  
 
	switch ($url_info ['D']) { //赏金托管
		case 1 :
			$where .= " and a.model_id = 4";
			break;
		case 2 :
			$where .= " and a.model_id != 4";
			break; 
		case 3 : 
			$where .= " and a.alipay_trust =1 ";
			break;
	} 
	switch ($url_info ['E']) {
		case 1 : $where .= " and DATE_ADD(CURDATE(),INTERVAL  7 day) >= date(from_unixtime(a.end_time)) "; break;
		case 2 : $where .= " and DATE_ADD(CURDATE(),INTERVAL 3 day) >= date(from_unixtime(a.end_time))  "; break; 
		case 3 : $where .= " and DATE_ADD(CURDATE(),INTERVAL 30 day) >= date(from_unixtime(a.end_time)) "; break; 
		case 4 :
			$time = time () + 24 * 3600;
			$where .= " and a.end_time<$time ";
			break; 
	} 
	$model_open and $where .=" and a.model_id in ( $model_open) "; 
	switch ($url_info ['G']) { //其他
		case 1 :
			$where .= " and FIND_IN_SET( '1', a.pay_item ) ";
			break;
		case 2 :
			$where .= " and FIND_IN_SET( '2', a.pay_item ) and substring( payitem_time, instr(a.payitem_time,'top')+4+LENGTH('top'),10)>UNIX_TIMESTAMP() " ;
			break;
		case 3:
			$where .= " and FIND_IN_SET( '3', a.pay_item ) and substring( a.payitem_time, instr(a.payitem_time,'urgent')+4+LENGTH('urgent'),10)>UNIX_TIMESTAMP() ";
			break; 
		case 4 :
			$where .= " and a.is_delay = 1 ";
			break;  
	}
	if ($_COOKIE ['keketask_map_search_cash']) {
		intval ( $min ) or $min = 0;
		intval ( $max ) or $max = 0;
		$min and $where .= " and a.task_cash>'$min' ";
		$max and $where .= " and a.task_cash < '$max' ";
		$where .=" and  (d.end_cove < $max and d.start_cove<$max )";
	}
	
	switch ($url_info ['H']) { 
		case 1 :
			$where .= " and a.task_id = '$search_key'";
			break;
		case 2 :
			$where .= " and a.task_title like '%$search_key%'";
			break;
		case 3 :
			$where .= " and a.username = '$search_key'";
			break;
	} 
	switch ($ord){
		case 1:
			$order_by = " order by a.start_time desc";
			break;
		case 2:
			$order_by = " order by a.start_time asc";
			break;
		case 3:
			$order_by = " order by a.task_cash desc";
			break;
		case 4:
			$order_by = " order by a.task_cash asc";
			break; 
	} 

	$ord or $order_by = " order by (CASE WHEN substring( payitem_time, instr(a.payitem_time,'top')+4+LENGTH('top'),10)>UNIX_TIMESTAMP() THEN a.start_time ELSE 0 END) desc,a.start_time  desc ";	  

	return $where . $order_by;

}

 
/**
 * 
 * 搜索条件数组
 * @param unknown_type $search_key
 */
function get_where_arr(){
	global $model_list,$search_key;
	global $_lang;
	$task_indus_type = kekezu::get_industry ( 0 );//获取行业分类
	foreach ( $model_list as $v ) {
		if ($v ['model_id'] != 6 && $v ['model_id'] != 7) {
		$v['model_status']==1 and 	$display_model [$v ['model_id']] = $v;
		}
	} 
//条件大数组 
$where_arr = array (
	"A" => $task_indus_type, //任务分类
	"B" => $display_model, //任务模式 
	"C" => array (//任务赏金 
		"1" => array ("name" => $_lang['task_cash_s1'] ), 
		"2" => array ("name" => "100-500" ), 
		"3" => array ("name" => "500-1000" ), 
		"4" => array ("name" => "1000-5000" ), 
		"5" => array ("name" => "5000-20000" ), 
		"6" => array ("name" => $_lang['task_cash_s2'] ) ), 
	"D" => array (//赏金托管
		"1" => array ("name" => $_lang['no_trust'] ), 
		"2" => array ("name" => $_lang['trusted'] ), 
		"3" => array ("name" => $_lang['zfb_trust'] ) ),
	"E" => array (//发布时间
		"1" => array ("name" => $_lang['in_week_end'] ), 
		"2" => array ("name" => $_lang['in_three_end'] ), 
		"3" => array ("name" => $_lang['in_month_end'] ), 
		"4" => array ("name" => $_lang['soon_end'] ) ), 
	"F" => array (//任务状态
		"2" => array ("name" => $_lang['working'] ), 
		"3" => array ("name" => $_lang['choosing'] ), 
		"8" => array ("name" => $_lang['ending'] ), 
		), 
	"G" => array (//其他选项 
		
		"2" => array ("name" => $_lang['recomend'] ), 
		"4" => array ("name" => $_lang['delay_makeup'] ), 
		"3" => array ("name" => $_lang['hurried_task'] ) ), 
	
	"H" => array ( 
		"1" => array ("name" => $_lang['task_num'] . ":$search_key" ), //任务编号
		"2" => array ("name" => $_lang['task_title'] . ":$search_key" ), //任务标题
		"3" => array ("name" => $_lang['task_releaser'] . ":$search_key" ) ),
	
		); 
	return $where_arr;	
}





if($_K['map_api']=='baidu'){
	$points = ' var point = new Array();
	';$arr_marker='';$addverlay='';
	$infoWindow=' var infoWindow = new Array();
	';$addEventListener='';
	$map_script='';
	foreach ($task_list_arr as $k=>$v) {
		$v['user_pic'] = keke_user_class::get_user_pic($v['uid']);
		$v['start_time'] = kekezu::time2Units(time()-$v['start_time']) ;
		$point = explode(',',$v ['point']);
		$v['point'] = $point['1'].','.$point['0']; 
		$points .= ' point['.$k.'] = new BMap.Point(' . $v ['point'] . ');
		';
		$arr_marker .= 'new BMap.Marker(point[' . $k . ']),';
		$addverlay .= 'map.addOverlay(marker[' . $k . ']);';
		$infoWindow .= ' infoWindow['.$k.'] = new BMap.InfoWindow("<div class=basic_style map_info><div class=fl_l mr_10><a target=_blank  href=index.php?do=space&member_id='.$v['uid'].'>'.$v['user_pic'].'</a></div><strong><a  target=_blank href=index.php?do=task&task_id='.$v['task_id'].'   target=_blank>'.$v['task_title'].'</a></strong><div class=font12><a href=index.php?do=space&member_id='.$v['uid'].' target=_blank  class=font12>'.$v['username'].'</a></b>&nbsp;&nbsp;'.$v['start_time']. $_lang['front_release'] .'</div></div>");
		';
		$addEventListener .= 'marker[' . $k . '].addEventListener("mouseover", function(){this.openInfoWindow(infoWindow[' . $k . ']);});';
	}
	$baidu_api = $_K['baidu_api'];
	$map_script.=<<<END
	<script type="text/javascript" src="$baidu_api"></script>
	<script type="text/javascript">
		window.onerror = function(){return true;};
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

	foreach ($task_list_arr as $k=>$v) {
		$v['user_pic'] = keke_user_class::get_user_pic($v['uid']);
		$v['start_time'] = kekezu::time2Units(time()-$v['start_time']) ;
		$arr_point .= 'new google.maps.LatLng(' . $v ['point'] . '),';
		$arr_marker .= '  new google.maps.Marker({ position: point[' . $k . '], map: map}),';
		$arr_infoWindow .= ' new google.maps.InfoWindow({content:"<div class=basic_style map_info><div class=fl_l mr_10><a target=_blank  href=index.php?do=space&member_id='.$v['uid'].'>'.$v['user_pic'].'</a></div><strong><a  target=_blank href=index.php?do=task&task_id='.$v['task_id'].'   target=_blank>'.$v['task_title'].'</a></strong><div class=font12><a href=index.php?do=space&member_id='.$v['uid'].' target=_blank  class=font12>'.$v['username'].'</a></b>&nbsp;&nbsp;'.$v['start_time']. $_lang['front_release'] .'</div></div>"}),';
		$addEventListener.='google.maps.event.addListener(marker['.$k.'],"mouseover", function() { clearOverlays();infoWindow['.$k.'].open(map,marker['.$k.']);});';
	}
	$google_api = $_K['google_api'];
$map_script.=<<<END
	<script type="text/javascript" src="$google_api"></script>
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

function get_model(){
	global $model_list;
	foreach ($model_list as $v) {
		$v['model_status']==1 and  $model_arr[] = $v['model_id'];
	}
	$res = implode(',', $model_arr);
	return $res; 
}

require  keke_tpl_class::template ($do);  
