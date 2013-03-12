<?php
/**
 * 短信发送接口V2.0
 * @author Chen tao
 *
 */
class sms {
	static $gateway="http://59.42.249.36/sms/http/Sms3.aspx?";
	static $charset = "utf-8";
	protected $_method;
	private $_action;
	private $_params;
	public $_error;
	
	public function __construct($mobiles,$content,$action='sendsms',$method="post"){
		$this->_action = $action;
		$this->_method = strtolower($method);
		$this->init_params($mobiles,$content);
	}
	private function init_params($mobiles,$content){
		global $kekezu;
		strtolower(CHARSET)==self::$charset or $content = kekezu::gbktoutf($content);
		$this->_params = array(
				'action'=>$this->_action,
				'username'=>$kekezu->_sys_config['mobile_username'],
				'userpwd'=>$kekezu->_sys_config['mobile_password'],
				'timing'=>'',
				'mobiles'=>$mobiles,
				'content'=>$content
		);
	}
	public function send(){
		$url = self::$gateway;
		$q   = http_build_query($this->_params);
		if(function_exists("curl_init")){
			$this->_method=='get' and $url.=$q;
			$m	 = kekezu::curl_request($url,false,$this->_method,$this->_params);
		}elseif(function_exists('fsockopen')){
			$url.=$q;
			$m   = kekezu::socket_request($url,false);
		}else{
			$url.=$q;
			$m 	 = file_get_contents($url);
		}
		return $this->error($m);
	}
	private function error($e){
		if($e<0){
			$err = array(
				'-1'=>'用户名或密码错误',
				'-2'=>'余额不足',
				'-3'=>'号码太长，不能超过1000条一次提交',
				'-4'=>'无合法号码',
				'-5'=>'内容包含不合法文字',
				'-6'=>'内容太长',
				'-7'=>'内容为空',
				'-8'=>'定时时间格式不对',
				'-9'=>'修改密码失败',
				'-10'=>'用户当前不能发送短信',
				'-11'=>'Action参数不正确',
				'-100'=>'系统错误'
			);
			chdir(dirname(__FILE__));
			KEKE_DEBUG or @file_put_contents('log.txt',var_export(array(
					'时间'=>date('Y-m-d H:i:s',time()),
					'错误码'=>$e,
					'详细'=>$err[$e]
				),1),FILE_APPEND);
		}
		return $e;
	}
}