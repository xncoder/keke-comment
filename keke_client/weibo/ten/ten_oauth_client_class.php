<?php 
require_once('oauth.php');
require_once('opent.php');
define( "MB_RETURN_FORMAT" , 'json' );
define( "MB_API_HOST" , 'open.t.qq.com' );

class ten_oauth_client_class extends base_client_class{
	public $_ten_weibo_oauth;
	public $_ten_weibo_client;
	
	
	function __construct($app_key,$app_secret){
		$this->_app_key = $app_key;
		$this->_app_secret = $app_secret;
		parent::__construct($app_key,$app_secret);
		$this->_ten_weibo_oauth = new MBOpenTOAuth( $this->_app_key,$this->_app_secret);
	}
	
	
	
	//获得授权链接的
	function get_auth_url($callback){
		$o = $this->_ten_weibo_oauth;
		$keys = $o->getRequestToken($callback);//这里填上你的回调URL
		
		$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false,'');
		$_SESSION['auth_ten']['keys'] = $keys;
		return $aurl;
	}
	
	//验证是否有授权
	function get_access_token(){
		return $_SESSION['auth_ten']['last_key'];
	}
	
	//销毁授权
	function clear_access_token(){
		unset($_SESSION['auth_ten']);
	}
	
	//通过授权
	function create_access_token($oauth_verifier=false){
		$this->_error_info = null;
		$o = $this->_ten_weibo_oauth;
		$o->__construct($this->_app_key,$this->_app_secret,$_SESSION['auth_ten']['keys']['oauth_token'] ,$_SESSION['auth_ten']['keys']['oauth_token_secret'] );
		$last_key = $o->getAccessToken($oauth_verifier) ;
		
		if (!$last_key['name']){
			kekezu::error_handler(001,'access_token不存在或者已过期');
			return false;
		}
		
		//last_key 保存
		$_SESSION['auth_ten']['last_key'] = $last_key;
		$this->init_client();
		return $last_key['name'];
		
	
	}
	
	private function init_client(){
		if (!$this->_ten_weibo_client){
		require_once('api_client.php');
		$this->_ten_weibo_client = new MBApiClient($this->_app_key,$this->_app_secret, $_SESSION['auth_ten']['last_key']['oauth_token'] ,$_SESSION['auth_ten']['last_key']['oauth_token_secret']);
		}
		
	}
	
	function get_login_info(){
		global $_K;
		$this->_error_info = null;
		$this->init_client();
		$c = $this->get_client();
		$data = $c->getUserInfo();
		
		if (!$data['data']&&$data['msg']){
			if (strtolower($_K['charset'])=='gbk'){
				$data = kekezu::utftogbk($data);
			}
			unset($_SESSION['auth_ten']);
			kekezu::error_handler(001,'用户数据获取失败！错误代码:'.$data['msg']);
			return false;
		}
		
		if (strtolower($_K['charset'])=='gbk'){
			$data = kekezu::utftogbk($data);
		}
		
		$auth_user_info = $data;
		
		return $auth_user_info;
	}
	
	/**
	 * 返回值是 生成的微博的id(int)
	 */
	function post_wb($msg,$img){
		$this->_error_info = null;
		global $_K;
		$this->init_client();
		$c = $this->get_client();
		
		if (strtolower($_K['charset'])=='gbk'){
			$msg = kekezu::gbktoutf($msg);
		}
		$p =array(
			'c' => $msg,
			'ip' => $_SERVER['REMOTE_ADDR'],
		 	'j' => '',
			'w' => ''
		);
		if ($img){
			$file_content = file_get_contents($img);
			$f            = array_reverse(explode(".",$img));
			$img_arr =array($f[0],$img,$file_content); 
			$p['p']=$img_arr;
		}
		$r = $c->postOne($p);
		if (strtolower($_K['charset'])=='gbk'){
			$r = kekezu::utftogbk($r);
		}
		if (!$r['data']&&$r['msg']){
// 			unset($_SESSION['auth_ten']);
			kekezu::error_handler(001,'发布失败！错误代码:'.$r['msg']);
			return false;
		}
		return $r['data']['id'];
	}
	
	//时间线
	function get_wb_list($page=0,$page_size=0){
		$this->_error_info = null;
		$this->init_client();
		$c = $this->get_client();
		
		$page_size = $page_size?$page_size:20;
		$p =array(
			'f' => $page,
			'n' => $page_size,		
			't' => 0,
			'l' => '',
			'type' => 1
		);
		
		$r = $c->getMyTweet($p);	
		$r = $r['data'];
		global $_K;
		if (strtolower($_K['charset'])=='gbk'){
			$r = kekezu::utftogbk($r);
		}
		$r = $r['info'];
		
		return $r;
	}
	
	function get_wb_info($sid){
		$this->_error_info = null;
		$this->init_client();
		$c = $this->get_client();	
		$p =array(
			'id' => $sid,
		);
		$r = $c->getOne($p);
		
		global $_K;
		if (strtolower($_K['charset'])=='gbk'){
			$r = kekezu::utftogbk($r);
		}
		
		
		if (!$r['data']&&$r['msg']){
			unset($_SESSION['auth_ten']);
			kekezu::error_handler(001,'微博信息获取失败！:'.$r['msg']);
			return false;
		}
		$r = $r['data'];
		/*腾讯返回的图片地址后面带size才显示,e.g http://app.qpic.cn/mblogpic/a5588650a5da2471a4f8 应该是'http://app.qpic.cn/mblogpic/a5588650a5da2471a4f8/2000
		 * */
		$r['original_pic']=$r['image'][0] ? $r['image'][0].'/2000' : '';
		$r['user']['screen_name'] = $r['name'];
		$r['user']['name']=$r['nick'];//用户昵称,有用的
		return $r;
	}
	
	
	
	//根据UID加关注
	function follow_wb_user($u_name){
		$this->_error_info = null;
		$this->init_client();
		$c = $this->get_client();
		$p =array(
			'type' => 1,
			'n'=>$u_name
		);
		$r = $c->setMyidol($p);
		global $_K;
		if (strtolower($_K['charset'])=='gbk'){
			$r = kekezu::utftogbk($r);
		}
		if ($r['msg']!='ok'){
			kekezu::error_handler(001,'加关注失败！:'.$r['msg']);
			return false;
		}
		$r = $r['data'];
		return $r;
	}
	
	//根据SID转发一条微博
	function repost_wb($sid,$text=null){
		global $_K;
		$this->_error_info = null;
		$this->init_client();
		$c = $this->get_client();
		if(strtolower($_K['charset'])=='gbk'&&$text){
			$text = kekezu::gbktoutf($text);
		}
		$p =array(
			'c' => $text,
			'ip' => $_SERVER['REMOTE_ADDR'], 
			'j' => '',
			'w' => '',
			'type' => 2,
			'r' => $sid 
		);
		$r = $c->postOne($p);
		global $_K;
		if (strtolower($_K['charset'])=='gbk'){
			$r = kekezu::utftogbk($r);
		}
		if (!$r['data']&&!$r['msg']){
			unset($_SESSION['auth_ten']);
			kekezu::error_handler(001,'转发微博注失败！:'.$r['msg']);//$this->set_error($r['msg']);
			return false;
		}
		return $r['data'];
		
	}
	
	//根据SID评论一条问微博
	function send_comment($sid,$text=null){
		global $_K;
		$this->_error_info = null;
		$this->init_client();
		$c = $this->get_client();
		if (strtolower($_K['charset'])=='gbk'){
			$text = kekezu::gbktoutf($text);
		}
		$p =array(
			'c' => $text,
			'ip' => $_SERVER['REMOTE_ADDR'], 
			'j' => '',
			'w' => '',
			'type' => 4,
			'r' => $sid 
		);
		$r = $c->postOne($p);
		
		if (strtolower($_K['charset'])=='gbk'){
			$r = kekezu::utftogbk($r);
		}
		if (!$r['data']&&$r['msg']){
			kekezu::error_handler(001,'微博评论失败！:'.$r['msg']);
			return false;
		}
		$r = $r['data'];
		return $r;
	}
	
	/**
	 * 获取粉丝列表
	 * 获取听众列表/偶像列表
	 *@num: 请求个数(1-30)
	 *@start: 起始位置
	 *@n:用户名 空表示本人
	 *@type: 0 听众 1 偶像
	 */
	function get_followers($uid_or_name = null, $count = false, $cursor = false ){
		$this->init_client();
		$c = $this->get_client();
		if(is_null($uid_or_name)){
			$uid_or_name = $_SESSION['auth_sina']['last_key']['user_id'];
		}
		$p =array(
				'n' => $uid_or_name,
				'num' => $count,
				'start' => $cursor,
				'type' => 0,
		);
		$r = $c->getMyfans($p);
		if (strtolower(CHARSET)=='gbk'){
			$r = kekezu::utftogbk($r);
		}
		return  $r['data']['info'];
	}
	
	
	//用户数据格式化
	function user_data_format($data){
		$r = array();
		$data = $data['data'];
		$r['account'] = $data['name'];
		$r["name"]=$data['nick'];
		$r["location"]=$data['location'];
		$r['img']=$data['head']?$data['head'].'/50':'';
		$r['url']="http://t.qq.com/{$data['name']}";
		$r['fans_count']=$data['fansnum']; 
		$r['isvip']=$data['isvip'];//是否认证用户
		$r['gz_count']=$data['idolnum'];
		$r['wb_count']=$data['tweetnum'];
		$r ['hf_count'] = 0;//$data['bi_followers_count'];//互粉术
		$r ['faver_count'] =0; //$data['favourites_count'];//收藏数
		$r['sex'] = $r['sex']==1?'男':$r['sex']==1?'女':'保密';
		$r ['create_at'] = strtotime($data['birth_year'].'-'.$data['birth_month'].'-'.$data['birth_day']);//创建的日期(时间戳)
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
		return $this->_ten_weibo_oauth;
	}
	
	function get_client(){
		return $this->_ten_weibo_client;
	}
}

