<?php 
require_once('lib/alipay_core.function.php');
require_once('lib/alipay_notify.class.php');
require_once('lib/alipay_service.class.php');
require_once('lib/alipay_submit.class.php');

class alipay_oauth_client_class extends base_client_class{
	public $_alipay_weibo_oauth;
	public $_alipay_weibo_client;
    public $_config;
	
	function __construct($app_key,$app_secret){
		global $_K;
		$this->_app_key = $app_key;
		$this->_app_secret = $app_secret;
		parent::__construct($app_key,$app_secret);
		$this->_config = array(
		  'partner'			=>	$this->_app_key,
		  'key'				=>	$this->_app_secret,
		  'return_url'		=>	$_K['siteurl']."/keke_client/weibo/alipay/return_url.php",
		  'sign_type'		=>	'MD5',
		  'input_charset'	=>	strtoupper(CHARSET),
		  'transport'		=>	'http',
		);
		$this->_alipay_weibo_oauth = new AlipayService($this->_config);
	}
	
	
	
	//获得授权链接的
	function get_auth_url($callback){
		
		//防钓鱼时间戳
		
	$anti_phishing_key  = '';
	$exter_invoke_ip = '';
	 //构造要请求的参数数组
	$parameter = array(
			"service"			=> "alipay.auth.authorize",
			"target_service"	=> 'user.auth.quick.login',
			"partner"			=> trim($this->_config['partner']),
			"_input_charset"	=> trim($this->_config['input_charset']),
	        "return_url"		=> trim($this->_config['return_url']),
	        //"anti_phishing_key"	=> $anti_phishing_key,
			//"exter_invoke_ip"	=> $exter_invoke_ip
	);
	$_SESSION['alipay_callback_url'] = $callback;
  // var_dump($parameter);die();
	//构造快捷登录接口
	//$alipayService = new AlipayService($this->_config);
	return   $this->_alipay_weibo_oauth->alipay_auth_authorize($parameter);
	
	}
	
	//验证是否有授权
	function get_access_token(){
		return $_SESSION['auth_alipay']['last_key'];
	}
	
	//销毁授权
	function clear_access_token(){
		unset($_SESSION['auth_alipay']);
	}
	
	/**
	 * 
	 * alipay 的授权在reutrn_url 保存在session中
	 * @see base_client_class::create_access_token()
	 */
	function create_access_token($oauth_verifier=false){
	 
		$alipayNotify = new AlipayNotify($this->_config);
		$verify_result = $alipayNotify->verifyReturn();
		if($verify_result) {//验证成功
			 
			$user_id	= $_GET['user_id'];	//支付宝用户id
			$token		= $_GET['token'];	//授权令牌
			//file_put_contents('log.txt', var_export($_GET,1),FILE_APPEND);
			//last_key 保存
			$_SESSION['auth_alipay']['last_key'] = $_GET;
		}
		
		
		//$this->init_client();
		return false;//$last_key['name'];
	}
	
	private function init_client(){
		 
		
	}
	
	function get_login_info(){
		global $_K;
		$p= $_SESSION['auth_alipay']['last_key'];
		return $p;
		 
	}
	
	//微博更新
	function post_wb($msg,$img){
		 
		
	}
	
	//时间线
	function get_wb_list($page=0,$page_size=0){
		 
	}
	
	function get_wb_info($sid){
		 
	}
	
	
	
	//根据UID加关注
	function follow_wb_user($u_name){
		 
	}
	
	//根据SID转发一条微博
	function repost_wb($sid,$text=null){
		 
		
	}
	
	//根据SID评论一条问微博
	function send_comment($sid,$text=null){
		
		 
	}
	
	//用户数据格式化
	function user_data_format($data){
		$r = array();
		 
		if(!$data){
		 	return false;
		}
		 
		$r['account'] = $data['user_id'];
		$r["name"]=$data['real_name'];
		$r["location"]="";//$data['location'];
		$r['img']=$data['figureurl'];
		$r['url']=$data['token'];
	 	$r['wb_count']="";
		$r['sex'] = $data['gender'];
		 
		return $r;
	}
	
	//微博数据格式化
	function wb_data_format($data){
		$r = array();
	 
		return $r;
	}
	
	
	
	function get_operate(){
		return $this->_alipay_weibo_oauth;
	}
	
	function get_client(){
		return $this->_alipay_weibo_client;
	}
}

