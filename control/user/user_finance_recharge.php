<?php	defined ( 'IN_KEKE' ) or exit ( 'Access Denied' );
$step_list = array ("step1" => array ($_lang['step_one'],$_lang['input_recharge_money'] ), "step2" => array ($_lang['step_two'], $_lang['choose_pay_account'] ),"step3" => array ($_lang['step_three'], $_lang['waiting_for_background_review'] ));
$step or $step = 'step1';
$ac_url = $origin_url . "&op=$op&ver=".intval($ver);
$offline_pay_list = get_pay_config('','offline');
$payment_list = get_pay_config();
switch ($step) {
	case "step2" :
		switch ($pay_type) { 
			case "online_charge" : 
				$total_money = trim ( sprintf ( "%10.2f", abs ( floatval ( ($cash) ) ) ) );
				$now = time ();
				$randno = mt_rand ( 1000, 9999 );
			   $paytitle = $username . "(" . $_K ['html_title'] . $_lang['balance_recharge'] . trim ( sprintf ( "%10.2f", $total_money ) ) . $_lang['yuan'].")";
				if (isset ( $ajax ) && $ajax == 'confirm') { 
					$payment_config = kekezu::get_payment_config($pay_mode);
					require S_ROOT . "/payment/" . $pay_mode . "/order.php";
					$order_id=keke_order_class::create_user_charge_order('online_charge', $payment_config['payment'],$total_money);
					if($pay_mode==='tenpay'){
						$service = $bank_type;
					}else{
						$service = null;
					}
				   $from = get_pay_url('user_charge',$total_money, $payment_config, $paytitle, $order_id,'0','0',$service);
				   $title=$_lang['confirm_pay'];
				   require keke_tpl_class::template ( "pay_cash");
					die();
				}
				break;
			case "offline_charge" : 
				if (isset($formhash)&&kekezu::submitcheck($formhash)) {
		            $pay_info=kekezu::escape($pay_info);
		            $cash = keke_curren_class::convert(abs($recharge),0,true)+0;
					$order_id=keke_order_class::create_user_charge_order('offline_charge', $pay_account,$cash,'',$pay_info);
					 if($order_id){
						kekezu::show_msg ( $_lang['system prompt'],$ac_url."&op=detail&action=charge#userCenter",'3',"{$_lang['order_submit_success_notice']}",'alert_right');
					}else{
						kekezu::show_msg ( $_lang['system prompt'],$ac_url."&step=step2&show=offline#userCenter",'3',"{$_lang['order_submit_fail']}",'alert_error');
					}
				}
				break;
		}
		break;
}
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
require keke_tpl_class::template ( "user/" . $do . "_" . $view . "_" . $op );
