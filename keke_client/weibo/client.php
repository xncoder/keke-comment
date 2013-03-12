<?php

if (! defined ( 'IN_KEKE' ) && ! defined ( 'ADMIN_KEKE' )) {
	exit ( 'Access Denied' );
}

class oauth_api_factory {
	/**
	 * 获得 oauth链接<b>对象</b>  参数可传可不传    空参数会读取上次执行时传入的参数
	 * @param $wb_type 微博类型
	 * @param $app_key
	 * @param $app_secret
	 * @return object
	 */
	static function get_o($wb_type, $app_key, $app_secret) {
		global $_oauth_api_operate_obj, $_oauth_api_operate_param;
//		var_dump($wb_type, $app_key, $app_secret);
		if ($wb_type) {
			$_oauth_api_operate_param ['wb_type'] = $wb_type;
		} else {
			$wb_type = $_oauth_api_operate_param ['wb_type'];
		}
		if ($app_key) {
			$_oauth_api_operate_param [$wb_type] ['app_key'] = $app_key;
		} else {
			$app_key = $_oauth_api_operate_param [$wb_type] ['app_key'];
		}
		if ($app_secret) {
			$_oauth_api_operate_param [$wb_type] ['app_secret'] = $app_secret;
		} else {
			$app_secret = $_oauth_api_operate_param [$wb_type] ['app_secret'];
		}

		if ($_oauth_api_operate_obj [$wb_type]) {
			return $_oauth_api_operate_obj [$wb_type];
		} else {
			if (! file_exists ( S_ROOT . "./keke_client/weibo/" . $wb_type . "/" . $wb_type . "_oauth_client_class.php" )) {
				return false;
			} else {
				include_once S_ROOT . "./keke_client/weibo/" . $wb_type . "/" . $wb_type . "_oauth_client_class.php";
				$class_name = "{$wb_type}_oauth_client_class";
				$_oauth_api_operate_obj [$wb_type] = new $class_name ( $app_key, $app_secret );
				return $_oauth_api_operate_obj [$wb_type];
			}
		
		}
	
	}
	
	//获得通讯链接
	static function get_auth_url($callback, $wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->get_auth_url ( $callback );
	}
	
	//获得连接token
	static function get_access_token($wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->get_access_token ();
	}
	
	//清除连接状态
	static function clear_access_token($wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->clear_access_token ();
	}
	
	/**
	 * 创建访问token
	 * @param $oauth_verifier (当$wb_type是sina时,这个值是新浪返回的$_GET['code]')
	 * @param $wb_type 微博类型 sina/ten etc.
	 * @param $app_key
	 * @param $app_secret
	 * @param $more 这个是
	 */
	static function create_access_token($oauth_verifier = false, $wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->create_access_token ( $oauth_verifier );
	}
	
	//获得当前登录用户信息
	static function get_login_info($wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->get_login_info ();
	}
	
	//获得oauth连接对象    供自由读取使用   如  $o->get(url);
	static function get_operate($wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->get_operate ();
	}
	
	//获得微博官方api提供的sdk client文件
	static function get_client($wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->get_client ();
	}
	
	//获得错误信息  程序运行异常时用它调错误详细
	static function get_error($wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->get_error ();
	}
	
	//发布微博
	static function post_wb($msg, $img = null, $wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->post_wb ( $msg, $img );
	}
	
	//获得微博列表
	static function get_wb_list($page = 0, $page_size = 0, $wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->get_wb_list ( $page, $page_size );
	}
 	//获取粉丝列表
	static function get_fans_list($uid_or_name=null,$page = 0, $page_size = 0, $wb_type = null, $app_key = null, $app_secret = null){
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->get_followers($uid_or_name, $page_size,$page);
	} 
	
	//根据sid获得单条微博数据
	static function get_wb_info($sid, $wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->get_wb_info ( $sid );
	}
	
	//根据UID关注某人
	static function follow_wb_user($u_id, $wb_type = null, $app_key = null, $app_secret = null) {
		if (strtolower(CHARSET)=='gbk'){
			$u_id = kekezu::gbktoutf($u_id);
		}
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->follow_wb_user ( $u_id );
	}
	
	//转发一条微博
	static function repost_wb($sid, $text = null, $wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->repost_wb ( $sid, $text );
	}
	
	//评论一条微博
	static function send_comment($sid, $text = null, $wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->send_comment ( $sid, $text );
	}
	
	//用户数据格式化  将api返回的结果统一格式
	static function user_data_format($data, $wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->user_data_format ( $data );
	}
	
	//微博数据格式化  将api返回的结果统一格式
	static function wb_data_format($data, $wb_type = null, $app_key = null, $app_secret = null) {
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->wb_data_format ( $data );
	}
	
	static function query_sid($mid,$wb_type = null, $app_key = null, $app_secret = null){
		$o = oauth_api_factory::get_o ( $wb_type, $app_key, $app_secret );
		return $o->mid_2_sid($mid);
	}
}

//此为抽象类  子类必须实现的东西
abstract class base_client_class {
	public $_app_key;
	public $_app_secret;
	protected $_error_info;
	
	function __construct($app_key, $app_secret) {
		$this->_app_key = $app_key;
		$this->_app_secret = $app_secret;
	}
	
	//获得授权链接的
	abstract function get_auth_url($callback);
	
	//验证是否有授权
	abstract function get_access_token();
	
	//销毁授权
	abstract function clear_access_token();
	
	//通过授权
	abstract function create_access_token($oauth_verifier = false);
	
	//获得当前授权用户的用户信息
	abstract function get_login_info();
	
	//获得对应的认证类  用于调取数据
	abstract function get_operate();
	
	//获得对应的工具类  用于调取数据
	abstract function get_client();
	
	//用户数据格式化  将api返回的结果统一格式
	abstract function user_data_format($data);
	
	//微博数据格式化  将api返回的结果统一格式
	abstract  function wb_data_format($data);
	
	//发送微博的  非必要实现  可覆盖
	abstract function post_wb($msg, $img);
	
	//数据format
	

	//以下3个函数为错误信息获取
	/**
	 * 实现该接口程序遵循以下规则,在执行函数或者读取数据时  如果遇到错误
	 * 函数本身返回false   再将错误数据保存在  $_error_info中
	 * 开发者在控制层读到false时自行决定是否获得错误信息
	 * 
	 * 但是为了避免错误信息读到上次操作遗留的error信息
	 * 可能留下错误的函数记得在函数头写上  $this->_error_info = null;或者  $this->set_error(null);
	 * 
	 * */
	protected function set_error($error) {
		$this->_error_info = $error;
	}
	
	public function get_error() {
		return $this->_error_info;
	}
}

?>