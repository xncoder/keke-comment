<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );

/**
 * 订单充值页面
 * @copyright keke-tech
 * @author Monkey
 * @version v 2.0
 * 2010-8-11上午08:05:04
 */


kekezu::check_login ();
$page_title=$_lang['order_pay'].'- '.$_K['html_title'];

//$payment_list = kekezu::get_payment_config ();

$obj_type&&$obj_id and $order_id = keke_order_class::get_order_id($obj_type,$obj_id);
$order_id = intval ( $order_id );
$order_id and $order_info = keke_order_class::get_order_info ( $order_id );
$order_amount = $order_info ['order_amount'];
function get_href($order_info) {
	switch ($order_info ['obj_type']) {
		case 'task' :
			$a = "index.php?do=task&task_id={$order_info['obj_id']}";
			break;
		case 'service' :
			$a = "index.php?do=service&sid={$order_info['obj_id']}";
			break;
	}
	return $a;
}
$href = get_href ( $order_info );
//计算用户余额
$kekezu->_sys_config ['credit_is_allow'] == 1 and $user_balance = $user_info ['credit'] + $user_info ['balance'] or $user_balance = $user_info ['balance'];
//应付金额
$pay_amount = (float)$order_info ['order_amount'] - (float)$user_balance;
$pay_amount <= 0 and kekezu::show_msg ( $_lang['operate_notice'], "index.php?do=user&view=employer&op=task&model_id=$order_info[model_id]", 2, $_lang['this_order_need_pay'] );




//确认支付方式，返回请求的url
if (isset($pay_mode)) {
	$payment_config = kekezu::get_payment_config($pay_mode);
	if($pay_mode==='tenpay'){
		$service = $bank_type;
	}else{
		$service = null;
	}
	require S_ROOT . "/payment/" . $pay_mode . "/order.php";
	$from = get_pay_url('order_charge',$pay_amount, $payment_config, $order_info['order_name'], $order_id,$model_id,$order_info['obj_id'],$service);
	$title=$_lang['confirm_pay'];
	
	require keke_tpl_class::template ( "pay_cash");die();
}
//获取支付接口配置
//获取支付接口配置
function get_pay_config($paymentname = "", $pay_type = 'online'){
	$where = " 1=1 ";
	$paymentname and $where  .= " and payment='$paymentname' ";
	$pay_type and  $where .= " and type = '$pay_type' ";
	$list=  kekezu::get_table_data ( '*', "witkey_pay_api", $where, "pay_id asc", '', '', '', null );
	$tmp = array();
	foreach ($list as $k=>$v){
	if($v['config']){
		$config = unserialize( $v['config'] );
		if(is_array($config)){
			$v = array_merge($v,$config);
		}
	}
	$tmp[$v ['payment']] = $v;
	}
	return $tmp;
}
//腾讯网银的bank_type，对应的图片名称
function get_ten_bank_type(){
static $bank = array(
	"1001"=>"17",
	"1002"=>"10",
	"1003"=>"2",
	"1004"=>"9",
	"1005"=>"1",
	"1006"=>"4",
	"1008"=>"8",
	"1009"=>"27",
	"1010"=>"18",
	"1020"=>"5",
	"1021"=>"7",
	"1022"=>"3",
	"1024"=>"20",
	"1025"=>"22",
	"1027"=>"6",
	"1032"=>"11",
	"1033"=>"14",
	"1052"=>"19",
	"8001"=>"logo",
	);
	return $bank;
}
$ten_bank_type_arr = get_ten_bank_type();
$payment_list = get_pay_config();
require $kekezu->_tpl_obj->template ( $do );