<?php
define('IN_KEKE', 1);
include ("../../../app_comm.php");
include_once '../client.php';

$_app_id  = $kekezu->_sys_config["alipay_app_id"];
$_app_secret = $kekezu->_sys_config["alipay_app_secret"];

$alipay_obj= oauth_api_factory::get_o("alipay", $_app_id, $_app_secret);
$alipay_config = $alipay_obj->_config;

 
?>
<!DOCTYPE HTML>
<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<?php
//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyReturn();
if($verify_result) {//验证成功
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//请在这里加上商户的业务逻辑程序代码
	
	//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
    //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
    $user_id	= $_GET['user_id'];	//支付宝用户id
    $token		= $_GET['token'];	//授权令牌
   // KEKE_DEBUG AND file_put_contents('log.txt', var_export($_GET,1),FILE_APPEND);
	//执行商户的业务程序
	
	echo "验证成功<br />";
	echo "token:".$token;
	$_SESSION['auth_alipay']['last_key'] = array('user_id'=>$_GET['user_id'],'token'=>$_GET['token'],'real_name'=>$_GET['real_name']);
	if($url = $_SESSION['alipay_callback_url']){
		header("Location:".$url);
	}
    
	//etao专用
	if($_GET['target_url'] != "") {
		//程序自动跳转到target_url参数指定的url去
	}

	//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else {
    //验证失败
    //如要调试，请看alipay_notify.php页面的verifyReturn函数，比对sign和mysign的值是否相等，或者检查$responseTxt有没有返回true
    echo "验证失败";
}
?>
        <title>支付宝快捷登录接口</title>
	</head>
    <body>
    </body>
</html>