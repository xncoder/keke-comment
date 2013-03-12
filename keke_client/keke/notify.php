<?php
/*
	*功能：联盟异步通知调用的页面
	*版本：1.1
	*日期：2012-02-28
	'说明：
	'以下代码只是为了方便用户测试而提供的样例代码，用户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。

*/
///////////页面功能说明///////////////
//创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
//该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
//该通知页面主要功能是：根据联盟的处理结果，来做网站的业务逻辑处理。
//根据本地验证结果、须向联盟响应success，error标识
//需保证本页面无任何跳转
define ( "IN_KEKE", true );
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'app_comm.php');
require_once 'keke_service_class.php';
require_once 'keke_tool_class.php';
require_once ("config.php");
//构造联盟业业务类
$keke_interface = keke_tool_class::keke_interface();//接口数组
$keke_interface = array_flip($keke_interface);//

$union_obj = new keke_service_class($config,$keke_interface[$_POST['service']]);
$verify_result = $union_obj->notify_verify();//客户端通知验证结果

$_POST = keke_service_class::data_merge();//回调数据获取
//写日志
//KEKE_DEBUG and $fp = file_put_contents ( 'log.txt', var_export ( $_POST, 1 ), FILE_APPEND );
list($log,$model_code,$task_id) = explode("-",$_POST['outer_task_id'],3);
if ($verify_result) {
 	echo "success";
	$union_obj->union_task_response($model_code, $task_id, $_POST);//本地业务处理
} else {
	echo "error";
}