<?php
/* *
 * 类名：AlipayService
 * 功能：支付宝各接口构造类
 * 详细：构造支付宝各接口请求参数
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */
require_once ("alipay_submit.class.php");
class AlipayService {
	public $_alipay_gateway	="http://capi.p21.alipay.net/cooperate/gateway.do?";/** 支付宝网关地址（新）*/
	public $_private_key_path;//商户私钥路径
	public $_partner; //合作伙伴编号
	public $_sign_type; //签名类型
	public $_input_charset; //页面编码
	public $_interface; //调用接口简写名	
	public $_parameter; //参数数组
	

	function __construct($interface, $payment_config, $sign_type = 'DSA', $input_charset = 'GBK') {
		$this->_interface		 = $interface;
		$this->_sign_type 		 = $sign_type;
		$this->_input_charset	 = strtoupper ( $input_charset );
		$this->_private_key_path = S_ROOT.'/payment/alipay_trust/key/rsa_private_key.pem';
		$this->basic_param_init ( $payment_config );
	}
	/**
	 * 链接基本参数构造
	 */
	function basic_param_init($payment_config) {
		$this->_parameter ['service'] = $this->get_interface ( trim ( $this->_interface ) );
		$this->_parameter ['partner'] = $payment_config ['seller_id'];
		$this->_partner               = $payment_config ['seller_id'];
	}
	/**
	 * 构造即时到帐接口
	 * @param $para_temp 请求参数数组
	 * @return 表单提交HTML信息
	 */
	function create_direct_pay_by_user($para_temp) {
		//设置按钮名称
		$button_name  = $_lang['confirm'];
		//生成表单提交HTML文本信息
		$alipaySubmit = new AlipaySubmit ( $this->_private_key_path, $this->_sign_type, $this->_input_charset );
		return $alipaySubmit->buildForm ( $para_temp, $this->_alipay_gateway, "get", $button_name );
	}
	/**
	 * 获取接口名称
	 * @param string $shortening 接口简写
	 */
	function get_interface($shortening) {
		$interface_arr = array (
				"sns_bind"=>"sns.account.bind",
				"cancel_bind"=>"sns.cancel.account.bind",
				"create" => "alipay.witkey.task.create",
				 "confirm" => "alipay.witkey.task.confirm",
				 "append" => "alipay.witkey.task.amount.append",
				 "pt_pay" => "alipay.witkey.task.pay.by.platform",
				 "pt_confirm" => "alipay.witkey.task.confirm.by.platform",
				 "pt_cancel" => "alipay.witkey.task.cancel.confirm.by.platform",
				 "pt_refund" => "alipay.witkey.task.left.amount.handle" );
		return $interface_arr [$shortening];
	}
	/**
	 * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
	 * return 时间戳字符串
	 */
	function query_timestamp() {
		$url			 = $this->_alipay_gateway . "service=query_timestamp&partner=" . trim ( $this->_partner );
		$encrypt_key	 = "";
		
		$doc = new DOMDocument ();
		$doc->load ( $url );
		$itemEncrypt_key = $doc->getElementsByTagName ( "encrypt_key" );
		return	$itemEncrypt_key->item ( 0 )->nodeValue;
	}
	/**
	 * 构造支付宝其他接口URL
	 * @param array $task_info 任务信息数组
	 * @param array $extra_info 额外信息数组
	 * @param string $return 返回类型  form表单  url 链接
	 * @param string $method 表单请求类型 get,post
	 * @return 表单提交HTML信息/支付宝返回XML处理结果
	 */
	function alipay_interface($task_info=array(),$extra_info=array(), $return = "form", $method = "get") {
		global $_K;
		$_SESSION['trust_'.$task_info['task_id']]=$this->_interface;//记录当前动作
		$alipaySubmit			  	= new AlipaySubmit ( $this->_private_key_path, $this->_sign_type, $this->_input_charset );
		$para_temp = $this->_parameter;
		
		if($this->_interface!="sns_bind"||$this->_interface!="cancel_bind"){
			$para_temp ['outer_task_id']= "{$task_info['model_code']}-{$task_info ['task_id']}"; //任务编号
		}
		
		$para_temp ['return_url'] 	= $_K ['siteurl'] . '/payment/alipay_trust/return.php'; //同步提示链接
		$para_temp ['notify_url']	= $_K ['siteurl'] . '/payment/alipay_trust/notify.php'; //异步提示链接
		
		$func_name 					= $this->_interface . "_param";
		$params						= $this->$func_name ( $task_info,$extra_info);
		$params and $para_temp		= array_merge ( $para_temp, $params );
		/**待与公共参数组合*/
		switch ($return) {
			case "form" :
				//设置按钮名称
				$button_name		= $_lang['confirm'];
				//生成表单提交HTML文本信息
				$request_str		= $alipaySubmit->buildForm ( array_filter ( $para_temp ), $this->_alipay_gateway, $method, $button_name);
				break;
			case "url" :
				$request_str	    = $alipaySubmit->buildRequestParaToString ( array_filter ( $para_temp ));
				$request_str        = $this->_alipay_gateway.$request_str;
				break;
		}
		return $request_str;
	}
	/**
	 * 构造用户绑定请求参数
	 * @param array $task_info 任务参数数组
	 * @param array $extra_info 额外参数数组
	 */
	function sns_bind_param($task_info=array(),$extra_info=array()) {
		return  array (
				'type'=>'common',//用户类型
				'sns_user_id'=>$extra_info['sns_user_id'],//授权用户ID
				'sns_user_name'=>$extra_info['sns_user_name']//授权用户名
		);
	}
	/**
	 * 构造用户解除绑定请求参数
	 * @param array $task_info 任务参数数组
	 * @param array $extra_info 额外参数数组
	 */
	function cancel_bind_param($task_info=array(),$extra_info=array()) {
		return  array (
				'key'=>$extra_info['bind_key'],//用户类型
				'sns_user_id'=>$extra_info['sns_user_id'],//授权用户ID
				'sns_user_name'=>$extra_info['sns_user_name']//授权用户名
		);
	}
	/**
	 * 构造任务发布请求参数
	 * @param array $task_info 任务参数数组
	 * @param array $extra_info 额外参数数组
	 */
	function create_param($task_info,$extra_info=array()) {
		$params =  array (
				'outer_task_freeze_no'=>$task_info ['order_id'],//冻结流水号(订单号)
				'task_amount'=>$task_info ['task_cash'],//任务金额
				'task_type'=>'keke_20',
				'task_title'=>$task_info ['task_title'],
				'task_expired_time'=>date ( 'Ymdhis', $task_info ['end_time'] ),
				'outer_account_name'=>$task_info ['username'],
				'outer_account_id'=>$task_info ['uid']
				);
			if($task_info['pay_item']&&$task_info['att_cash']){
				$params['additional_profit_amount']=$task_info ['att_cash'];
				$params['additional_profit_transfer_no']=$task_info ['task_id'];
			}
			return $params;
	}
	/**
	 * 任务延期加价参数构造
	 * @param array $task_info 任务参数数组
	 * @param array $extra_info 额外参数数组
	 */
	function append_param($task_info,$extra_info){
		$params =  array (
				'outer_task_freeze_no'=>"{$extra_info['type']}-{$extra_info['day']}-{$extra_info['cash']}-{$task_info['order_id']}",//冻结流水号(订单号)
				'task_amount'=>$task_info ['task_cash'],//任务金额
				'task_expired_time'=>date ( 'Ymdhis', $task_info ['end_time'] ),
				'outer_account_id'=>$task_info ['uid']
				);
			if($extra_info['att_cash']){
				$params['additional_profit_amount']=$extra_info ['cash'];
				$params['additional_profit_transfer_no']=$task_info ['task_id'];
			}
		return $params;
	}
	/**
	 * 平台任务批量打款参数构造
	 * @param array $task_info 任务参数数组
	 * @param array $extra_info 额外参数数组（传递中标威客集）
	 */
	function pt_pay_param($task_info,$extra_info=array()){
		return array ('outer_account_id'=>$task_info ['uid'],//合作网站用户ID
					'alipay_user_id'=>$this->_partner,//支付宝用户号
					'transfer_detail'=>$this->build_transfer_detail($extra_info),//支付宝用户号
		);
	}
	/**
	 * 任务中标参数构造
	 * @param array $task_info 任务参数数组
	 * @param array $extra_info 额外参数数组（传递中标威客集）
	 */
	function confirm_param($task_info,$extra_info){
		$params = array ('outer_account_id'=>$task_info ['uid'],//合作网站用户ID
					'alipay_user_id'=>$task_info['oauth_id'],//支付宝用户号
					'transfer_detail'=>$this->build_transfer_detail($extra_info)
		);
		$task_info['sp_end_time'] and $params['announce_period']=date("Ymdhis",$task_info['sp_end_time']);
		return $params;
	}
	/**
	 * 平台任务完成打款参数构造
	 * @param array $task_info 任务参数数组
	 * @param array $extra_info 额外参数数组（传递中标威客集）
	 */
	function pt_confirm_param($task_info,$extra_info){
		return array (
					'transfer_detail'=>$this->build_transfer_detail($extra_info)
		);
	}
	/**
	 * 平台任务撤销中标参数构造
	 * @param array $task_info 任务参数数组
	 * @param array $extra_info 额外参数数组（传递中标威客集）
	 */
	function pt_cancel_param($task_info,$extra_info){
		return array (
					'cancel_transfer_detail'=>$this->build_transfer_detail($extra_info)
		);
	}
	/**
	 * 平台任务退款参数构造
	 * @param array $task_info 任务参数数组
	 * @param array $extra_info 额外参数数组（传递中标威客集）
	 */
	function pt_refund_param($task_info,$extra_info=array()){
		//获取管理员绑定信息
		$pt_mamager = keke_trust_fac_class::verify_bind(ADMIN_UID,'alipay_trust');
		return array (
					'outer_account_id'=>$pt_mamager['uid'],//分润账户USERID
					'alipay_user_id'=>$pt_mamager['oauth_id'],//分润账户支付宝用户号
					'refund_detail'=>$this->build_transfer_detail($extra_info['refund_detail']),//赏金退还明细
					'platform_detail'=>$this->build_transfer_detail($extra_info['platform_detail']),//平台分润明细
					'transfer_detail'=>$this->build_transfer_detail($extra_info['transfer_detail'])//赏金支付明细
		);
	}
	/**
	 * 打款明细信息串构造
	 *   信息集格式为：收款方Email_1^金额1^备注1|收款方Email_2^金额2^备注2
	 */
	function build_transfer_detail($transfer_param){
		$param_str 	  = '';
		$attr_str     = '';
		$index        = sizeof($transfer_param);
		
		for ($i = 0; $i < $index; $i++) {
			$attr_str = implode("~*@^",$transfer_param[$i]);
			$param_str.='*@|$'.$attr_str;
		}
		return ltrim($param_str,"*@|$");
	}
}