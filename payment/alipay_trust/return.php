<?php
/* *
 * 功能：支付宝页面跳转同步通知页面
 * TRADE_FINISHED(表示交易已经成功结束，为普通即时到帐的交易状态成功标识);
 * TRADE_SUCCESS(表示交易已经成功结束，为高级即时到帐的交易状态成功标识);
 */

define ( "IN_KEKE", true );
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'app_comm.php');
require_once ("lib/alipay_notify.class.php");
/** 担保配置*/
$alipaydb_info = kekezu::get_payment_config ( 'alipay_trust', 'trust' );
$payment_config = unserialize ( $alipaydb_info ['config'] );

$_input_charset = strtoupper ( CHARSET );
$sign_type = "DSA";

$uid = $_SESSION['uid'];
$username = $_SESSION['username'];
$user_info = $kekezu->_userinfo;

//计算得出通知验证结果
$alipayNotify = new AlipayNotify ( $payment_config ['seller_id'], $sign_type, $_input_charset );
$_GET and $verify_result = $alipayNotify->verifyReturn() or $verify_result = $alipayNotify->verifyNotify ();

/**
 * 参数判断
 * 	返回sns_user_id标识
 * 		T:当前动作是”用户绑定/解绑“
 * 			返回key标识
 * 				T：当前动作为用户绑定
 * 				F：当前动作为用户解绑
 * 		F:当前动作是”担保任务“
 */

$_GET =$alipayNotify->data_merge($_input_charset);//回调数据获取
switch (isset ( $_GET ['sns_user_id'] )) {
	case "1" ://”用户绑定、解绑“
		switch (isset ( $_GET ['key'] )) {
			case "1" ://”用户绑定“
				$url = $_K [siteurl] . "/index.php?do=user&view=setting&op=account_bind";
				$fac_obj  = keke_trust_fac_class::get_instance("sns_bind");
				if ($verify_result) { //验证成功
					$fac_obj->verify_response($url);
				} else {//验证失败
					$fac_obj->notify($url,"支付宝担保绑定失败","warning");
				}
				break;
			case "0" ://用户解绑
				$url = $_K [siteurl] . "/index.php?do=user&view=setting&op=account_bind";
				$fac_obj  = keke_trust_fac_class::get_instance("cancel_bind");
				if ($verify_result) { //验证成功
					$fac_obj->verify_response($url);
				} else {//验证失败
					$fac_obj->notify($url,"支付宝担保绑定失败","warning");
				}
				break;
		}
		break;
	case "0" ://”担保任务“
		$out_task_id = $_GET ['outer_task_id']; //获取任务编号
		list ($model_code,$task_id) = explode ( '-', $out_task_id,2);
		$interface   = $_SESSION['trust_'.$task_id];//业务动作缩写
		$class = $model_code . "_alipay_trust_class";
		$model_code or exit('不存在的任务模型');
		$fac_obj = new $class ( $task_id,$interface,$_GET);
		if ($verify_result) { //验证成功
			$fac_obj->$interface ( true);
		} else {//验证失败
			$fac_obj->notify ( $_K ['siteurl'] . "/index.php?do=task&task_id=$task_id", "操作失败", "error" );
		}
		break;
}