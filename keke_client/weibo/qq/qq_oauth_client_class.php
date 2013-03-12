<?php 
require_once('oauth.php');
require_once('opent.php');
define( "MB_RETURN_FORMAT" , 'json' );
define( "MB_API_HOST" , 'open.t.qq.com' );
class qq_oauth_client_class extends base_client_class{
	public $_qq_weibo_oauth;
	public $_qq_weibo_client;
    public $_auth_url = "";
	
	function __construct($app_key,$app_secret){
		$this->_app_key = $app_key;
		$this->_app_secret = $app_secret;
		parent::__construct($app_key,$app_secret);
		$this->_qq_weibo_oauth = new MBOpenTOAuth( $this->_app_key,$this->_app_secret);
		 
		 
	}
	
	
	
	//获得授权链接的
	function get_auth_url($callback){
		require_once 'redirect_to_login.php';
		$aurl = redirect_to_login($this->_app_key, $this->_app_secret, $callback);
		//$keys = array('oauth_token'=>$_SESSION['qq_token'],'oauth_token_secret'=>$_SESSION["qq_secret"]);
		//$_SESSION['auth_qq']['keys'] = $keys;
		return $aurl;
	}
	
	//验证是否有授权
	function get_access_token(){
		return $_SESSION['auth_qq']['last_key'];
	}
	
	//销毁授权
	function clear_access_token(){
		unset($_SESSION['auth_qq']);
	}
	
	/**
	 * 通过授权
	 * oauth_token=8226392397541222103&
	 * openid=E76A5A82F2904BD5E93E436DCA20AB08&
	 * oauth_signature=EnY68jM5GUWoDB%2F47Q%2Fq%2BQ0%2Bdfs%3D&
	 * oauth_vericode=1311817775&
	 * timestamp=1319172591
	 * 
	 * @see base_client_class::create_access_token()
	 */
	function create_access_token($oauth_verifier=false){
		include_once 'get_request_token.php';
		 //授权后，获取临时token.php
		 global $oauth_vericode;
		//$request_token = get_request_token($this->_app_key, $this->_app_secret);
		 
		//var_dump($this);
		$this->_error_info = null;
		$o = $this->_qq_weibo_oauth;
		//用临时token取得访问access_token
		/*$o->__construct($this->_app_key,$this->_app_secret,$request_token['oauth_token'] ,$request_token['oauth_token_secret'] );
		$last_key = $o->getAccessToken($oauth_verifier) ;*/
		include_once 'get_access_token.php';
		$last_key = get_access_token($this->_app_key,$this->_app_secret,$_SESSION[qq_token],$_SESSION[qq_secret],$oauth_vericode);
		parse_str($last_key,$access_token);
		/*var_dump($last_key,$access_token);
		die();*/
		if (!$access_token){
			$this->set_error('access_token不存在或者已过期');
			return false;
		}
		
		//last_key 保存
		$_SESSION['auth_qq']['last_key'] = $access_token;
		
		//$this->init_client();
		return true;//$last_key['name'];
	}
	
	private function init_client(){
		 
		
	}
	
	function get_login_info(){
		global $_K;
		$call_back= "";
		$p= $_SESSION['auth_qq']['last_key'];
		//var_dump($p);
		require_once 'get_user_info.php';
		$data = get_user_info($this->_app_key, $this->_app_secret, $p[oauth_token], $p[oauth_token_secret], $p[openid]);
		if(strtolower($_K['charset'])=='gbk'){
		  $data = kekezu::utftogbk($data);
		}
		//$data = $this->user_data_format($data);
		//var_dump($data);die();
		return $data;
		 
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
		$r['account'] = $data['nickname'];
		$r["name"]=$data['nickname'];
		$r["location"]="";//$data['location'];
		$r['img']=$data['figureurl'];
		$r['url']="";
	 	$r['wb_count']="";
		$r['sex'] = $data['gender'];
		 
		return $r;
	}
	
	//微博数据格式化
	function wb_data_format($data){
		$r = array();
		$r['id']=$data['id'];
		$r['text']=$data['origtext'];
		$r['uid']=$data['name'];
		$r['username']=$data['nick'];
		$r['img'] = $data['image'][0]?$data['image'][0].'/120':null;
		$r['url']="http://t.qq.com/p/t/{$r['id']}";
		return $r;
	}
	
	
	
	function get_operate(){
		return $this->_qq_weibo_oauth;
	}
	
	function get_client(){
		return $this->_qq_weibo_client;
	}
}

