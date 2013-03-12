<?php
/**
 * 支付请求URL生成
 */

defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );

//require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'app_comm.php');
require 'alipay_function.php';
require 'alipay_service.php';

/**
 * 生成批量付款URL
 * @param array $payment_config 商家号配置信息组数 (必填)
 * @param string $detail_data 打款明细数组
 * @param string $service  付款类型(可空）
 * @param string $sign_type 签名类型(可空）
 * @param $method 请求响应方式 form，返回表单。url。返回链接
 * @return string url 
 * 
 */
function get_batch_url($payment_config, $detail_data, $method = 'form', $service = "batch_trans_notify", $sign_type = 'MD5') {
	global $_K;
	$body = $subject = "提现批量打款";
	$pay_date = date ( Ymd );
	$batch_no = $pay_date . date ( hms );
	$detail_data = array_filter ( $detail_data );
	$batch_obj  = pay_batch_fac_class::get_instance('alipayjs');
	$detail_data = $batch_obj->stack_batch($detail_data);
	$parameter = array (
			 "service" => $service,
			 "partner" => $payment_config ['seller_id'],
			 "email" => $payment_config ['account'],
			 "account_name" => $payment_config ['account_name'],
			 "notify_url" => $_K ['siteurl'] ."/payment/alipayjs/batch_notify.php",
			 "_input_charset" => strtoupper(CHARSET),
			 "pay_date" => $pay_date,
			 "batch_no" => $batch_no,
			 "batch_num" => $detail_data['batch_num'],
			 "batch_fee"=>$detail_data['batch_fee'],
			 "detail_data"=>$detail_data['detail_data']
	);
	$alipay = new alipay_service ( $parameter, $payment_config ['safekey'], $sign_type,'batch');
	if ($method == 'form') {
		return $alipay->build_postform ('get');
	} else {
		return $alipay->create_url ();
	}
}

/**
 * 用户充值生成付款url 
 * @param string $charge_type  充值类型（order_charge订单充值。user_charge余额充值）
 * @param float $pay_amount 金额 (必填)
 * @param array $payment_config 商家号配置信息组数 (必填)
 * @param string $subject 订单标题 (必填)
 * @param int $order_id 订单ID（必填)
 * @param int $model_id 模型ID（无值表示余额充值，无付款动作)
 * @param int $obj_id   对象ID（ 可空)
 * @param string $service  付款类型(可空）
 * @param string $sign_type 签名类型(可空）
 * @param $method 请求响应方式 form，返回表单。url。返回链接
 * @return string url 
 */
function get_pay_url($charge_type, $pay_amount, $payment_config, $subject, $order_id, $model_id = null, $obj_id = null, $service = null, $sign_type = 'MD5', $method = 'form') {
	global $_K, $uid, $username;
	$charge_type == 'order_charge' and $t = "订单充值" or $t = "余额充值";
	if($service===null){
		$service =  "create_direct_pay_by_user";
	}
	$body = $t . "(from:" . $username . ")";
	$parameter = array ("service" => $service, "partner" => $payment_config ['seller_id'], "return_url" => $_K ['siteurl'] . '/payment/alipayjs/return.php', "notify_url" => $_K ['siteurl'] . '/payment/alipayjs/notify.php', "_input_charset" => CHARSET, "subject" => $subject, "body" => $body, "out_trade_no" => "charge-{$charge_type}-{$uid}-{$obj_id}-{$order_id}-{$model_id}", "total_fee" => $pay_amount, "payment_type" => "1", "show_url" => $_K ['siteurl'] . "/index.php?do=user&view=finance", "seller_email" => $payment_config ['account'],"extend_param"=>"isv^kk11");
	$alipay = new alipay_service ( $parameter, $payment_config ['safekey'], $sign_type );
	if ($method == 'form') {
		return $alipay->build_postform ();
	} else {
		return $alipay->create_url ();
	}
}