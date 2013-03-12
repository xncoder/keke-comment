<?php

/** 
 * @author Administrator
 * @version v2.0
 * @property oauth登录工厂类，不同平台的oauth登录的处理
 * 主要平台有,1：新浪微博，腾讯微博，QQ，网易微博，搜狐微博，淘宝平台，支付宝平台
 * 本类的主要功能：1.用户登录，登出，获取oauth登录信息
 */
class keke_oauth_login_class extends keke_oauth_base_class {
	public $_url ;
 	function __construct($wb_type) {
 		parent::__construct ( $wb_type );
	}
	/**
	 * 登陆
	 * @param bool/mix $call_back
	 * @param string $url
	 * @return boolean
	 */
	function login($call_back,$url) {
// 		$url = str_replace('localhost', 'vipbird.com', $url);
// 		$url = str_replace('192.168.1.106', 'vipbird.com', $url);
		global $oauth_verifier,$code,$_K;
		if (isset($code) && $this->_wb_type=='sina'){//sina oauth登陆获取access token 需要$code以及;
			$oauth_verifier = array('code'=>$code,'redirect_uri'=>$url);
		}
		if ($call_back) {
			if(isset($code) && $this->_wb_type=='sina'){
				if($oauth_verifier){
					oauth_api_factory::create_access_token ( $oauth_verifier, $this->_wb_type, $this->_app_id, $this->_app_secret );
					header ( "Location:$url");
					die ();
				}else{
					header("Location:$_K[siteurl]/index.php?do=login");die();
				}
			}else{
				oauth_api_factory::create_access_token ( $oauth_verifier, $this->_wb_type, $this->_app_id, $this->_app_secret );
				header ( "Location:$url");
				die ();
			}
		}
		$this->_url = $url;
		if (oauth_api_factory::get_access_token ( $this->_wb_type, $this->_app_id, $this->_app_secret )) {
			return true;
		} else {
			$aurl = oauth_api_factory::get_auth_url ("$url&call_back=1", $this->_wb_type, $this->_app_id, $this->_app_secret );
 			header ( 'Location:' . $aurl );
			die ();
		}
	}
	/**
	 * 获取当前登录用户登录信息 
	 */
	function get_login_user_info(){
		$user_auth_info = oauth_api_factory::get_login_info ( $this->_wb_type, $this->_app_id, $this->_app_secret );
		if($user_auth_info){
			return oauth_api_factory::user_data_format ( $user_auth_info, $this->_wb_type, $this->_app_id, $this->_app_secret );	
		}else{
			return false;
		}
		
	}
	function logout() {
		oauth_api_factory::clear_access_token($this->_wb_type, $this->_app_id, $this->_app_secret);
	}
	function get_user_info() {
	}

}

?>