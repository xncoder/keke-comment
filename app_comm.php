<?php
error_reporting(E_ALL|E_STRICT);
// keke_base_class 和 keke_core_class 包含了核心常用函数
require (dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . 'lib/inc/keke_base_class.php'); 
require (dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . 'lib/inc/keke_core_class.php'); 
$basic_config  = $kekezu->_sys_config;
$model_list = $kekezu->_model_list;
$nav_list = $kekezu->_nav_list;
// 关闭安全模式
if((bool)ini_get('safe_mode')==true){
	ini_set('safe_mode','Off');
}
// 关闭魔术引号，更安全
if((bool)get_magic_quotes_runtime()==true){
	ini_set('magic_quotes_runtime','Off');
}
// get 方法获得上一次生成cache的时间
$exec_time_traver = kekezu::exec_js('get');
// 设置exec_time_traver为true （表达式or后的部分不会执行）
(!isset($exec_time_traver)||$exec_time_traver<time()) and $exec_time_traver = true or $exec_time_traver = false;
// 获取HTTP请求的参数
$_R = $_REQUEST;
$_R = kekezu::k_input($_R);  
// extract：从数组中将变量导入到当前的符号表,返回成功导入到符号表中的变量数目。 
// 技巧：可以将HTTP请求的参数名直接作为变量名使用，减少了变量声明
$_R and extract ($_R,EXTR_SKIP);
isset($inajax) and $_K['inajax']= $inajax; 
// 销毁uid和username，作用是？
unset ( $uid, $username);
