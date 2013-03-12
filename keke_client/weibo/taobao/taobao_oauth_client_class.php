<?php
require_once ('top/TopClient.php');

class taobao_oauth_client_class extends base_client_class {
	public $_paramter;
	public $_tao_data;
	public $_taobao_weibo_oauth;
	public $_taobao_weibo_client;
	public $_auth_url = "https://oauth.taobao.com/authorize?";
	public $_taobao_client;
	
	function __construct($app_key, $app_secret) {
		parent::__construct ( $app_key, $app_secret );
		$this->_taobao_weibo_oauth = new TopClient ();
		$this->_app_key = $app_key;
		$this->_app_secret = $app_secret;
		$this->init_paramter ();
	}
	/**
	 * 请求参数初始构造
	 */
	function init_paramter() {
		$this->_paramter = array ('client_id' => $this->_app_key, 'client_secret' => $this->_app_secret, 'response_type' => 'code', 'state' => $this->set_state (), 'scope' => $this->setscope (), 'view' => $this->set_view () );
	}
	/**
	 * 设置状态参数，由应用自定义，颁发授权后会原封不动返回
	 */
	function set_state() {
		return '';
	}
	/**
	 * 权限参数，API组名串，多个组名时，用“，”分隔，目前支持参数：promotion,item,usergrade
	 */
	function setscope() {
		$scope = array ('promotion', 'item', 'usergrade' );
		return implode ( ',', $scope );
	}
	/**
	 * 设置授权页面类型，可选范围：web（PC端） 、 wap（无线端），此参数不传，则默认为web
	 */
	function set_view($view = 'web') {
		return $view;
	}
	
	//获得授权链接的
	function get_auth_url($callback) {
		$this->_paramter ['redirect_uri'] = $_SESSION ['taobao_callback_url'] = $callback;
		return $this->redirect_uri ();
	}
	function redirect_uri() {
		$params = array_filter ( $this->_paramter );
		$redirect_url = $this->_auth_url . http_build_query ( $params );
		return $redirect_url;
	}
	
	//验证是否有授权
	function get_access_token() {
		return $_SESSION ['auth_taobao'] ['last_key'];
	}
	
	//销毁授权
	function clear_access_token() {
		unset ( $_SESSION ['auth_taobao'] );
	}
	
	//通过授权
	function create_access_token($oauth_verifier = false) {
		$this->_error_info = null;
		$top_obj  = $this->_taobao_weibo_oauth;
		$params   = $this->format_token_param();
		$auth_url = str_replace('authorize','token',$this->_auth_url); 
		$last_key = $top_obj->curl($auth_url,$params);
		$last_key = $this->get_json_data($last_key);
		if (!$last_key['taobao_user_id']){
			$this->set_error('access_token不存在或者已过期');
			return false;
		}
		//last_key 保存
		$this->format_last_key($last_key);
		return $last_key['taobao_user_id'];
	}
	/**
	 * 格式化lastkey
	 */
	function format_last_key($last_key){
		$last_key['uid']  = $last_key['taobao_user_id'];
		$last_key['nick'] = $last_key['taobao_user_nick'];
		$_SESSION['auth_taobao']['last_key'] = $last_key;
	}
	/**
	 * 构造请求token参数
	 * @param unknown_type $grant_type
	 */
	function format_token_param($grant_type='authorization_code'){
		$params 			    = array_filter ( $this->_paramter );
		$params['redirect_uri'] = $_SESSION ['taobao_callback_url'];
		$params['grant_type']   = $grant_type;
		$params['code'] 		= $_GET['code'];
		unset($params['response_type']);
		return $params;
	}
	/**
	 * 获取json数据
	 */
	function get_json_data($json){
		$data = array();
		$json  = json_decode($json);
		if(is_object($json)){
			$data = (array)$json;
		}
		CHARSET=='gbk' and $data = kekezu::utftogbk($data);
		return $data;
	}
	/**
	 * 获取登陆信息
	 * @see base_client_class::get_login_info()
	 */
	function get_login_info() {
		
		$p = $_SESSION ['auth_taobao'] ['last_key'];
		$top_obj  = $this->_taobao_weibo_oauth;
		if ($p) {
			if(CHARSET== 'gbk'){
				$p['nick'] = kekezu::utftogbk(urldecode($p['taobao_user_nick']));
			}else{
				$p['nick'] = urldecode($p['taobao_user_nick']);
			} 
			
			return $p;
		} else {
			return false;
		}
	}
	
	//微博更新
	function post_wb($msg, $img) {
		
	}
	
	//时间线
	function get_wb_list($page = 0, $page_size = 0) {
	
	}
	
	function get_wb_info($sid) {
	
	}
	
	//根据UID加关注
	function follow_wb_user($u_name) {
	
	}
	
	//根据SID转发一条微博
	function repost_wb($sid, $text = null) {
	
	}
	
	//根据SID评论一条问微博
	function send_comment($sid, $text = null) {
	
	}
	
	//用户数据格式化
	function user_data_format($data) {
		
		$r = array ();
		
		if (! $data) {
			return false;
		}
		$r ['account'] 			 = $data ['uid'];
		$r ["name"]			 = $data ['nick'];
		$r ["seller_credit"] = $data ['seller_credit'];
		$r ['buyer_credit']  = $data ['buyer_credit'];
		$r ['created'] 		 = $data ['created'];
		$r ['wb_count'] 	 = $data ['email'];
		$r ['last_visit'] 	 = $data ['last_visit'];
		$r ['location'] 	 = $data ['location'];
		return $r;
	}
	
	//微博数据格式化
	function wb_data_format($data) {
		$r = array ();
		$r ['id'] = $data ['id'];
		$r ['text'] = $data ['origtext'];
		$r ['uid'] = $data ['name'];
		$r ['username'] = $data ['nick'];
		$r ['img'] = $data ['image'] [0] ? $data ['image'] [0] . '/120' : null;
		$r ['url'] = "http://t.qq.com/p/t/{$r['id']}";
		return $r;
	}
	
	function get_operate() {
		return $this->_taobao_weibo_oauth;
	}
	
	function get_client() {
		return $this->_taobao_weibo_client;
	}
}

