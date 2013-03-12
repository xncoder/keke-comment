<?php
defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$new_task_arr  = db_factory::query("select a.task_id,a.task_title,a.task_status,a.start_time,b.uid,b.username,b.residency,point from ".TABLEPRE."witkey_task a left join  ".TABLEPRE."witkey_space b on a.uid = b.uid where a.task_status >1 and pay_item like '%4%' and ifnull(point,0)>0 order by a.start_time desc limit 0,50 ",1,3600*24);
if($_K['map_api']=='baidu'){
	foreach ($new_task_arr as $k=>$v) {
		$v['user_pic'] = keke_user_class::get_user_pic($v['uid']);
		$v['start_time'] = kekezu::time2Units(time()-$v['start_time']) ;
		$point = explode(',',$v ['point']);
		$v['point'] = $point['1'].','.$point['0'];
		$arr_point .= 'new BMap.Point(' . $v ['point'] . '),';
		$arr_marker .= 'new BMap.Marker(point[' . $k . ']),';
		$addverlay .= 'map.addOverlay(marker[' . $k . ']);';
		$arr_infoWindow .= 'new BMap.InfoWindow("<div class=basic_style map_info><div class=fl_l mr_10><a target=_blank  href=index.php?do=space&member_id='.$v['uid'].'>'.$v['user_pic'].'</a></div><strong><a  target=_blank href=index.php?do=task&task_id='.$v['task_id'].'   target=_blank>'.$v['task_title'].'</a></strong><div class=font12><a href=index.php?do=space&member_id='.$v['uid'].' target=_blank  class=font12>'.$v['username'].'</a></b>&nbsp;&nbsp;'.$v['start_time'].$_lang['before_release'].'</div></div>"),';
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
	map.centerAndZoom(new BMap.Point(113.937226, 30.937862), 15);
	var point = [$arr_point];
	var marker = [$arr_marker];
	$addverlay
	var infoWindow = [$arr_infoWindow];
	$addEventListener
	var ind = 0;
	var timer = null;
	show();
	function openMyWin(id){
	map.openInfoWindow(infoWindow['id'],point['id']);
	}
	function show(){  
		  timer = setInterval(
			  function(){    
				      if(ind == infoWindow.length){     
					         ind = 0;     
						}    
					map.addOverlay(marker['ind']);
					map.setViewport(point); 
 				   openMyWin(ind);
				  ind++;  
				},
			5000);
	}
</script>
END;
	}else{
	foreach ($new_task_arr as $k=>$v) {
	$v['user_pic'] = keke_user_class::get_user_pic($v['uid']);
	$v['start_time'] = kekezu::time2Units(time()-$v['start_time']) ;
	$arr_point .= 'new google.maps.LatLng(' . $v ['point'] . '),';
	$arr_marker .= '  new google.maps.Marker({ position: point[' . $k . '], map: map}),';
	$arr_infoWindow .= ' new google.maps.InfoWindow({content:"<div class=basic_style map_info><div class=fl_l mr_10><a target=_blank  href=index.php?do=space&member_id='.$v['uid'].'>'.$v['user_pic'].'</a></div><strong><a  target=_blank href=index.php?do=task&task_id='.$v['task_id'].'   target=_blank>'.$v['task_title'].'</a></strong><div class=font12><a href=index.php?do=space&member_id='.$v['uid'].' target=_blank  class=font12>'.$v['username'].'</a></b>&nbsp;&nbsp;'.$v['start_time'].$_lang['before_release'].'</div></div>"}),';
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
var ind = 0;
var timer = null;
show();
function clearOverlays() {
if (infoWindow) {
for (var i=0;i<infoWindow.length;i++) {
infoWindow['i'].close();
}
}
}
function openMyWin(id){
clearOverlays();
infoWindow['id'].open(map,marker['id']);
}
function show(){  
	timer = setInterval(
		 function(){	
		 	 if(ind == infoWindow.length){     
				 ind = 0;     
			}    
			openMyWin(ind);
			 ind++;  
		},
	5000);
}
</script>
END;
}
require  keke_tpl_class::template ( $do);
