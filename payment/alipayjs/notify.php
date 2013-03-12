<?php
/*
	*功能：支付宝主动通知调用的页面（通知页）
	*版本：3.0
	*日期：2010-05-21
	'说明：
	'以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
	'该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

*/
///////////页面功能说明///////////////
//创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
//该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
//该页面调试工具请使用写文本函数log_result，该函数已被默认开启，见alipay_notify.php中的函数notify_verify
//TRADE_FINISHED(表示交易已经成功结束，通用即时到帐反馈的交易状态成功标志);
//TRADE_SUCCESS(表示交易已经成功结束，高级即时到帐反馈的交易状态成功标志);
//该通知页面主要功能是：对于返回页面（return_url.php）做补单处理。如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
/////////////////////////////////////


define ( "IN_KEKE", true );
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'app_comm.php');
require_once ("alipay_notify.php");

$_input_charset = strtoupper ( CHARSET );
$payment_config = kekezu::get_payment_config ( 'alipayjs' );
$payment_config or die ( "支付配置错误，支付无法完成，请联系管理员。" );

$seller_id = $payment_config ['seller_id'];
$security_code = $payment_config ['safekey'];
$seller_email = $payment_config ['account']; //签约支付宝账号或卖家支付宝帐户
$sign_type = "MD5";
$transport = "http";

//构造通知函数信息
$alipay = new alipay_notify ( $seller_id, $security_code, $sign_type, $_input_charset, $transport );
//计算得出通知验证结果
$verify_result = $alipay->notify_verify ();
$out_trade_no = $_POST ['out_trade_no']; //获取订单号
$total_fee = $_POST ['total_fee']; //获取总价格 

$notify_type = $_POST ['notify_type']; //消息提示类型 trade_status_sync即时、batch_trans_notify 批量
// chmod('log.txt',0777);
// KEKE_DEBUG and $fp = file_put_contents ( 'log.txt', var_export ( $_POST, 1 ), FILE_APPEND );

if ($verify_result) {
	echo "success";
	if ($notify_type == 'trade_status_sync') {
		if ($_POST ['trade_status'] == 'TRADE_FINISHED' || $_POST ['trade_status'] == 'TRADE_SUCCESS') { //交易成功结束 
			list ( $_, $charge_type, $uid, $obj_id, $order_id, $model_id ) = explode ( '-', $out_trade_no, 6 );
			$fac_obj = new pay_return_fac_class ( $charge_type, $model_id, $uid, $obj_id, $order_id, $total_fee, 'alipayjs' );
			$fac_obj->load ();
		}
	}
} else {
	echo "error";
}