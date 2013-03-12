<?php
/*
	*功能：联盟主动通知调用的页面
	*版本：1.1
	*日期：2012-02-28
	'说明：
	'以下代码只是为了方便用户测试而提供的样例代码，用户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。

*/
///////////页面功能说明///////////////
//创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
//该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
//该通知页面主要功能是：根据联盟的处理结果，来做网站的业务逻辑处理。

define ( "IN_KEKE", true );
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'app_comm.php');
require_once 'keke_service_class.php';
require_once 'keke_tool_class.php';
require_once ('config.php');
$keke_interface = keke_tool_class::keke_interface();//接口数组
$keke_interface = array_flip($keke_interface);//
//构造联盟业业务类
$union_obj = new keke_service_class($config,$keke_interface[$_GET['service']]);
//客户端通知验证结果
$_GET and $verify_result = $union_obj->return_verify() or $verify_result = $union_obj->notify_verify ();
$_GET = keke_service_class::data_merge();//回调数据获取
//KEKE_DEBUG and $fp = file_put_contents ( 'log.txt', var_export ( $_GET, 1 ), FILE_APPEND );
list($log,$model_code,$task_id) = explode("-",$_GET['outer_task_id'],3);
if ($verify_result) {
	$response = $union_obj->union_task_response($model_code, $task_id, $_GET);//本地业务处理
	keke_tool_class::notify($response['url'],$response['notice'],$response['type']);
} else {
	keke_tool_class::notify($_K['siteurl'].'/index.php', '验证失败','error');
}