<?php
/**
 * 网银在线回调测试页面
 */ 
define ( "IN_KEKE", true );
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'app_comm.php');
$pay_arr = kekezu::get_payment_config ( 'chinabank' );
@extract ( $pay_arr );
$key = $safekey;
$v_oid = trim ( $_POST ['v_oid'] ); // 商户发送的v_oid定单编号   
$v_pmode = trim ( $_POST ['v_pmode'] ); // 支付方式（字符串）   
$v_pstatus = trim ( $_POST ['v_pstatus'] ); //支付状态 ：20 成功,30 失败
$v_pstring = trim ( $_POST ['v_pstring'] ); // 支付结果信息
$v_amount = trim ( $_POST ['v_amount'] ); // 订单实际支付金额
$v_moneytype = trim ( $_POST ['v_moneytype'] ); //订单实际支付币种    
$remark1 = trim ( $_POST ['remark1'] ); //备注字段1
$remark2 = trim ( $_POST ['remark2'] ); //备注字段2
$v_md5str = trim ( $_POST ['v_md5str'] ); //拼凑后的MD5校验值  

/* 重新计算md5的值 */
$text = "{$v_oid}{$v_pstatus}{$v_amount}{$v_moneytype}{$key}";
$md5string = strtoupper ( md5 ( $text ) );
/* 判断返回信息，如果支付成功，并且支付结果可信，则做进一步的处理 */

list ( $_, $charge_type, $uid, $obj_id, $order_id, $model_id ) = explode ( '-', $v_oid, 6 );
$fac_obj = new pay_return_fac_class ( $charge_type, $model_id, $uid, $obj_id, $order_id, $v_amount, 'chinabank' );

if ($v_md5str == $md5string) {
	if ($v_pstatus == "20" && $_ == 'charge') {
		$response = $fac_obj->load ( );
		/* charge */
		$fac_obj->return_notify ( 'chinabank',$response);
	} else {
		$fac_obj->return_notify ( 'chinabank');
	}
} else {
	$fac_obj->return_notify ( 'chinabank');
}