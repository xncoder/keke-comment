<?php
//die('sina_oauth');
require_once ('saetv2.ex.class.php');

class sina_oauth_client_class extends base_client_class {
	public $_sina_weibo_oauth;
	public $_sina_weibo_client;
	
	function __construct($app_key, $app_secret) {
		// http://open.weibo.com/apps/4229254781 添加测试用户
// 		$app_key = '4229254781';
// 		$app_secret = 'df8a743fdf1dbe69b76b9038bcb900c4';
		$this->_app_key = $app_key;
		$this->_app_secret = $app_secret;
		parent::__construct ( $app_key, $app_secret );
		$this->_sina_weibo_oauth = new SaeTOAuthV2 ( $app_key, $app_secret );
	}
	/**
	 * 获得授权跳转链接 step1 (step1获得授权页面链接,step2新浪授权页面登陆,并返回access_token等数据)
	 * 
	 * @see $this->create_access_token()(step2)
	 * @param $callback 最后需要回跳的地址        	
	 */
	function get_auth_url($callback) {
		$this->_error_info = null;
		$o = $this->_sina_weibo_oauth;
		$aurl = $o->getAuthorizeURL ( $callback );
		return $aurl;
	}
	/**
	 * 创建授权 step2
	 * 
	 * @param $oauth_verifier 创建授权所需要的参数数组
	 *        	array('code'=>'','request_uri'=>'')
	 */
	function create_access_token($oauth_verifier = false) {
		$this->_error_info = null;
		$o = $this->_sina_weibo_oauth;
		$o->__construct ( $this->_app_key, $this->_app_secret );
		$keys = $oauth_verifier;
		$last_key = $o->getAccessToken ( 'code', $keys );
		if (! $last_key ['uid']) {
			kekezu::error_handler( 001, 'access_token不存在或者已过期' );
			return false;
		}
		$_SESSION ['auth_sina'] ['last_key'] = $last_key; // last_key 保存
		$this->init_client (); // client储存 此函数使用sina官方下载的client
		return $last_key ['uid'];
	}
	// 验证是否有授权
	function get_access_token() {
		return $_SESSION ['auth_sina'] ['last_key'];
	}
	// 销毁授权
	function clear_access_token() {
		$this->init_client ();
		$c = $this->_sina_weibo_client;
		// $r = $c->oauth->post (
		// "http://api.t.sina.com.cn/account/end_session.json" );
		$r = $c->oauth->get ( 'https://api.weibo.com/2/account/end_session.json', array (
				'access_token' => $_SESSION ['auth_sina'] ['last_key'] ['access_token'] 
		) );
		unset ( $_SESSION ['auth_sina'] );
		return $r;
	}
	/**
	 * 初始化客户端(授权后)
	 */
	private function init_client() {
		if (! $this->_sina_weibo_client) {
			$this->_sina_weibo_client = new SaeTClientV2 ( $this->_app_key, $this->_app_secret, $_SESSION ['auth_sina'] ['last_key'] ['access_token'] );
		}
	
	}
	// 获得当前登录用户
	function get_login_info() {
		global $_K;
		$this->init_client ();
		$c = $this->get_client ();
		$auth_user_info = $c->show_user_by_id ( $_SESSION ['auth_sina'] ['last_key'] ['uid'] );
		if (strtolower ( CHARSET ) == 'gbk') {
			$auth_user_info = kekezu::utftogbk ( $auth_user_info );
		}
		if ($auth_user_info ['error']) {
			unset ( $_SESSION ['auth_sina'] );
			kekezu::error_handler ( 001, '用户数据获取失败！错误代码:' . keke_debug::dump ( $auth_user_info ['error'] ) );
			return false;
		}
		return $auth_user_info;
	}
	
	// 微博更新
	function post_wb($msg, $img) {
		$this->_error_info = null;
		$this->init_client ();
		$c = $this->get_client ();
		global $_K;
		if (strtolower ( $_K ['charset'] ) == 'gbk') {
			$msg = kekezu::gbktoutf ( $msg );
		}
		if (! $img) {
			$r = $c->update ( $msg );
		} else {
			$r = $c->upload ( $msg, $img );
		}
		if (strtolower ( $_K ['charset'] ) == 'gbk') {
			$r = kekezu::utftogbk ( $r );
		}
		if ($r ['error']) {
			kekezu::error_handler ( 001, '发送失败' . keke_debug::dump ( $r ) );
			return false;
		}
		return $r ['idstr'];
	
	}
	
	/**
	 * 获取微博列表时间线
	 */
	function get_wb_list($page = 0, $page_size = 20, $uid_or_name = '') {
		global $_K;
		$this->_error_info = null;
		$this->init_client ();
		$c = $this->get_client ();
		$page = max ( ( int ) $page, 1 );
		$page_size = $page_size ? $page_size : 20;
		if (! $uid_or_name) {
			$uid_or_name = $_SESSION ['auth_sina'] ['last_key'] ['uid'];
		}
		$func = is_numeric ( $uid_or_name ) ? 'user_timeline_by_id' : 'user_timeline_by_name';
		$r = $c->$func ( $page, $page_size );
		if (strtolower ( $_K ['charset'] ) == 'gbk') {
			$r = kekezu::utftogbk ( $r );
		}
		return $r;
	}
	/**
	 */
	function get_wb_info($sid) {
		$this->_error_info = null;
		$this->init_client ();
		$c = $this->get_client ();
		$r = $c->show_status ( $sid );
		if (strtolower ( CHARSET ) == 'gbk') {
			$r = kekezu::utftogbk ( $r );
		}
		if ($r ['error']) {
			kekezu::error_handler ( 001, '微博信息获取失败' . keke_debug::dump ( $r ) );
			return false;
		}
		return $r;
	}
	/**
	 * 获取粉丝列表
	 * 
	 * @param
	 *        	uid	false	int64	需要查询的用户UID。
	 * @param
	 *        	screen_name	false	string	需要查询的用户昵称。
	 * @param
	 *        	count	false	int	单页返回的记录条数，默认为50，最大不超过200。
	 * @param
	 *        	cursor
	 *        	false	int	返回结果的游标，下一页用返回值里的next_cursor，上一页用previous_cursor，默认为0。
	 */
	function get_followers($uid_or_name = null, $count = false, $cursor = 0) {
		$this->init_client ();
		$c = $this->get_client ();
		if (is_null ( $uid_or_name )) {
			$uid_or_name = $_SESSION ['auth_sina'] ['last_key'] ['uid'];
		}
		$func = is_numeric ( $uid_or_name ) ? 'followers_by_id' : 'followers_by_name';
		! $cursor && $cursor = 0;
		$r = $c->$func ( $uid_or_name, $cursor, $count );
		if (strtolower ( CHARSET ) == 'gbk') {
			$r = kekezu::utftogbk ( $r );
		}
		if ($r ['error']) {
			kekezu::error_handler ( 001, '获取粉丝列表失败' . keke_debug::dump ( $r ) );
			return false;
		}
		return $r ['users'];
	}
	
	/**
	 * 根据UID或者scree_name加关注某个用户
	 */
	function follow_wb_user($u_id) {
		global $_K;
		$this->_error_info = null;
		$this->init_client ();
		$c = $this->get_client ();
		$func = ctype_digit ( $u_id ) ? follow_by_id : follow_by_name; // 检测是数字还是字符串
		$r = $c->$func ( $u_id );
		if (strtolower ( $_K ['charset'] ) == 'gbk') {
			$r = kekezu::utftogbk ( $r );
		}
		if ($r ['error']) {
			// error_handler(001,'关注'.$u_id.'失败'.keke_debug::dump($r));//加关注失败的原因可能是自己关注自己,这种情况下程序不好判断,注释此处(此处错误抛出仅因为方便调试)
			return false;
		}
		return $r;
	}
	/**
	 * 通过微博（评论、私信）MID获取其ID
	 * mid to sid
	 */
	function mid_2_sid($mid) {
		$this->_error_info = null;
		$this->init_client ();
		$c = $this->get_client ();
		$r = $c->queryid ( $mid, 1, 0, 0, 1 );
		if ($r ['error']) {
			kekezu::error_handler ( 001, 'MID获取其ID失败' . keke_debug::dump ( $r ) );
			return false;
		}
		return $r ['id'];
	}
	
	// 根据SID转发一条微博
	function repost_wb($sid, $text = null) {
		global $_K;
		$this->_error_info = null;
		$this->init_client ();
		if (strtolower ( $_K ['charset'] ) == 'gbk' && $text) {
			$text = kekezu::gbktoutf ( $text );
		}
		$c = $this->get_client ();
		$r = $c->repost ( $sid, $text );
		if (strtolower ( $_K ['charset'] ) == 'gbk') {
			$r = kekezu::utftogbk ( $r );
		}
		if ($r ['error']) {
			kekezu::error_handler ( 001, '转发一条微博失败' . keke_debug::dump ( $r ) );
			return false;
		}
		return $r;
	
	}
	
	// 根据SID评论一条问微博
	function send_comment($sid, $text = null) {
		global $_K;
		$this->_error_info = null;
		$this->init_client ();
		$c = $this->get_client ();
		if (strtolower ( $_K ['charset'] ) == 'gbk') {
			$text = kekezu::gbktoutf ( $text );
		}
		$r = $c->send_comment ( $sid, $text );
		if (strtolower ( $_K ['charset'] ) == 'gbk') {
			$r = kekezu::utftogbk ( $r );
		}
		if ($r ['error']) {
			kekezu::error_handler ( 001, '评论微博失败' . keke_debug::dump ( $r ) );
			return false;
		}
		return $r;
	}
	
	// 用户数据格式化
	function user_data_format($data) {
		global $k;
		$r = array ();
		$r ['account'] = $data ['id'];
		$r ["name"] = $data ['name'];
		$r ["location"] = $data ['location'];
		$r ['img'] = $data ['profile_image_url'];
		$r ['url'] = "http://t.sina.com.cn/{$data['id']}/";
		$r ['fans_count'] = $data ['followers_count'];
		$r ['gz_count'] = $data ['friends_count'];
		$r ['wb_count'] = $data ['statuses_count'];
		$r ['hf_count'] = $data ['bi_followers_count']; // 互粉术
		$r ['faver_count'] = $data ['favourites_count']; // 收藏数
		$r ['sex'] = $data ['gender'] == 'm' ? '男' : $data ['gender'] == 'f' ? '女' : '保密';
		$r ['create_at'] = strtotime ( $data ['created_at'] ); // 创建的日期(时间戳)
		/*if (strtolower ( CHARSET ) == 'gbk') {
			$r = kekezu::utftogbk ( $r );
		}*/
		return $r;
	}
	
	// 微博数据格式化
	function wb_data_format($data) {
		$r = array ();
		$r ['id'] = $data ['id'];
		$r ['text'] = $data ['text'];
		$r ['uid'] = $data ['user'] ['id'];
		$r ['username'] = $data ['user'] ['name'];
		$r ['img'] = $data ['bmiddle_pic'];
		$r ['url'] = "http://api.t.sina.com.cn/{$r['uid']}/statuses/{$r['id']}";
		return $r;
	}
	
	function get_operate() {
		return $this->_sina_weibo_oauth;
	}
	
	/**
	 * 返回一个实例化的object
	 */
	function get_client() {
		return $this->_sina_weibo_client;
	}

}