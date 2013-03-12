<?php
/**
 * 客客推广联盟，基础业务处理函数
 * @author Administrator
 *
 */
class keke_service_class {
	//正式地址
	static $_GATE = 'http://www.kppw.cn/server'; //请求网关
	//测试地址
	//static $_GATE    = 'http://192.168.1.118/server'; //请求网关
	
	public $_app_id; //appID
	public $_app_secret; //appSECRET 
	public $_mysign; //本地签名 
	public $_sign_type; //签名类型
	public $_params; //参数数组		 
	public $_input_charset; //默认编码
	public $_service; //接口名称.缩写
	

	public function __construct($config, $service, $sign_type = 'MD5', $_input_charset = 'GBK') {
		$this->_gateway = self::$_GATE.'/gateway.php?';
		$this->_sign_type = strtoupper ( $sign_type );
		$this->_input_charset = strtoupper ( $_input_charset );
		$keke_interface = keke_tool_class::keke_interface ();
		$this->_service = $service;
		$this->_params ['service'] = $keke_interface [$service];
		$this->basic_param_init ( $config );
	}
	/**
	 * 基本配置构造
	 * @param array $config 外部传递基本配置
	 */
	public function basic_param_init($config) {
		$this->_app_id = $this->_params ['app_id'] = $config ['keke_app_id'];
		$this->_app_secret = $config ['keke_app_secret'];
		$this->_params ['sign_type'] = $this->_sign_type;
		$this->_params ['_input_charset'] = $this->_input_charset;
		$this->_params ['return_url'] = $config ['return_url'];
		$this->_params ['notify_url'] = $config ['notify_url'];
	}
	/**
	 * 联盟任务回调响应分发函数
	 * @param $model_code 模型code
	 * @param $task_id 任务编号
	 * @param $data 响应参数
	 */
	public function union_task_response($model_code, $task_id, $data) {
		$service = $this->_service;
		switch ($service) {
			case "keke_login" : //跳转至任务详细页
				global $_K,$kekezu,$config;
				$user_info = $kekezu->_userinfo;
				$r_task_id = $data ['r_task_id'];
				/**
				 *判断用户是否已经登录，如已经登录则向联盟平台返回releation_id,uid,username
				 *否则就跳到指定的登录页面，登录成功后，把登录用户名返回给平台  
				 */
				 $param = array('releation_id'=>$data['releation_id'],
								'r_task_id'=>$data['r_task_id'],
								'app_uid'=>$data['app_uid'],
								'login_type'=>$data['login_type']
						);
				if($user_info){
					$pid = 0;
					if($data['login_type']==1){
						//检查是否为发布者
						$pid = db_factory::get_count('select uid from '.TABLEPRE.'witkey_task where task_id='.$task_id);
						$url = $_K['siteurl'].'/index.php?do=task&task_id='.$task_id;
					}else{
						$url = $_K['siteurl'].'/index.php?do=release';
					}
					if($pid!=$user_info['uid']){
						require_once S_ROOT . '/keke_client/keke/config.php';
						//对应登陆
						$inter = 'keke_login'; 
						$param['to_uid']=$user_info['uid'];
						$param['to_username']=$user_info['username'];
						$request = keke_tool_class::union_build ( $config, $inter, $param );
						//请求接口
						kekezu::get_remote_data($request);
						if($data['login_type']==2){
							db_factory::execute('update '.TABLEPRE.'witkey_space set `union_user`=1,`union_rid`='.$data['releation_id'].' where uid='.$user_info['uid']);
						}
					}
				}else{
					$url = $_K['siteurl'].'/index.php?do=login_p&task_id='.$data['outer_task_id'].'&'.http_build_query($param);
				}
				header ( 'Location:'.$url );
				exit();
				break;
			default :
				$data ['model_code'] = $model_code;
				$data ['task_id'] = $task_id;
				$task_obj = new keke_union_class ( $task_id, $data );
				return $task_obj->$service ( '', true, $data );
				break;
		}
	}
	/**
	 * 构造请求链接
	 * @param array $para_temp 请求前的参数数组
	 * @return string $request_str 最终请求链接
	 */
	function build_url($para_temp) {
		//待请求参数数组
		

		$para = $this->build_request_para ( $para_temp );
		//把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$request_str = $this->_gateway . $this->create_link_string ( $para);
		return $request_str;
	}
	
	/**
	 * 构造提交表单HTML数据
	 * @param $para_temp 请求参数数组
	 * @param $method 提交方式。两个值可选：post、get
	 * @return 提交表单HTML文本
	 */
	function build_form($para_temp, $method = 'post') {
		//待请求参数数组
		$para = $this->build_request_para ( $para_temp );
		$sHtml = "<form id='kekesubmit' name='kekesubmit'  target='_blank' action='" . $this->_gateway . "' method='" . $method . "'>";
		while ( list ( $key, $val ) = each ( $para ) ) {
			$sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
		}
		$sHtml = $sHtml . "<input type='submit' value='确认提交'></form>";
		return $sHtml;
	}
	/**
	 * 生成签名后的参数数组结果
	 * @param $para_temp 要签名的数组
	 * @return array 已签名的最终请求数组
	 */
	function build_request_para($para_temp) {
		$para_filter = $this->para_filter ( $para_temp ); //参数过滤、除去待签名参数数组中的空值和签名参数
		$para_sort = $this->arg_sort ( $para_filter ); //参数排序、对待签名参数数组排序
		//生成签名结果
		$mysign = $this->build_request_sign ( $para_sort);
		//签名结果与签名方式加入请求提交参数组中
		$para_sort ['sign'] = $mysign;
		$para_sort ['sign_type'] = strtoupper ( trim ( $this->_sign_type ) );
		return $para_sort;
	}
	/**
	 * 生成请求签名
	 * @param $para_sort 请求前的参数数组
	 * @param $ver 是否验证
	 * @return 签名结果
	 */
	function build_request_sign($para_sort,$ver=false) {
		$prestr = $this->create_link_string ( $para_sort,true,$ver) . $this->_app_secret; //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串,待签名的值不得endode
		return $this->sign ( $prestr ); //把最终的字符串签名，获得签名结果
	}
	
	/**
	 * 请求参数过滤
	 * @param array 参数数组
	 */
	function para_filter($params) {
		$para = array ();
		$filter_arr = array ('sign', 'sign_type', 'sim_request', 'inajax_str', 'inajax', 'handlekey', 'ajaxtarget' );
		while ( list ( $key, $val ) = each ( $params ) ) {
			if (in_array ( $key, $filter_arr ) || $val == "") {
				continue;
			} else {
				$tmp         = htmlspecialchars_decode($params [$key]);
				$tmp         = trim(trim(trim($tmp),'<br />'));
				$para [$key] = htmlspecialchars($tmp);
			}
		}
		return $para;
	}
	/**
	 * 数组排序
	 * @param array $para_temp
	 */
	function arg_sort($para_temp) {
		ksort ( $para_temp );
		reset ( $para_temp );
		return $para_temp;
	}
	/**
	 * 拼接请求参数
	 * [对参数进行转码]
	 * @param array $para_temp 待组合参数数组 
	 * @param boolean $sign 是否进行签名
	 * @param boolean $ver 是否是验证
	 */
	function create_link_string($para_temp, $sign = false,$ver=false) {
		$arg = "";
		while ( list ( $key, $val ) = each ( $para_temp ) ) {
			if($sign){
				$val = strip_tags(htmlspecialchars_decode($val));
			}else{
				$ver and $val = urldecode($val) or $val=urlencode($val);
			}
			$arg .= $key . "=" . $val . "&";
		}
		$arg = rtrim ( $arg, "&" );
		//存在转义。去掉转义字符
		get_magic_quotes_gpc () and $arg = stripslashes ( $arg );
		
		return $arg;
	}
	/**
	 * 生成签名
	 * [目前为MD5]
	 * @param string $prestr 待签名字符串
	 */
	function sign($prestr) {
		$sign = '';
		if ($this->_sign_type == 'MD5') {
			$sign = md5 ( $prestr );
		} else {
			die ( $this->_sign_type . "签名方法待后续开发，请先使用MD5签名方式" );
		}
		return $sign;
	}
	/**
	 * 异步请求结果验证
	 * [调用notify_verfiy接口，需传递notigy_id作为查询依据]
	 */
	function notify_verify() {
		$veryfy_url = $this->_gateway . "service=keke.notify.verify" . "&app_id=" . $this->_app_id . "&notify_id=" . $_POST ["notify_id"];
		$veryfy_result = kekezu::get_remote_data ( $veryfy_url);
		
		if (empty ( $_POST )) {
			return false;
		} else {
			$post = $this->para_filter ( $_POST );
			$sort_post = $this->arg_sort ( $post );
			$this->_mysign = $this->build_request_sign ( $sort_post,true);
			/*记录日志*/
			//$this->log_result ( "veryfy_result=" . $veryfy_result . "\n notify_url_log:sign=" . $_POST ["sign"] . "&mysign=" . $this->_mysign . "," . $this->create_link_string ( $sort_post ) );
			
			if (preg_match ( "/true/is", $veryfy_result ) && $this->_mysign == $_POST ["sign"]) {
				return true;
			} else {
				return false;
			}
		}
	}
	/**
	 * 同步信息验证
	 * Enter description here ...
	 */
	function return_verify() {
		$veryfy_url = $this->_gateway . "service=keke.notify.verify" . "&app_id=" . $this->_app_id . "&notify_id=" . $_GET ["notify_id"];
		$veryfy_result = kekezu::get_remote_data ( $veryfy_url);
		if (empty ( $_GET )) {
			return false;
		} else {
			$get = $this->para_filter ( $_GET );
			$sort_get = $this->arg_sort ( $get );
			$this->_mysign = $this->build_request_sign ( $sort_get,true);
			//$this->log_result ( "veryfy_result=" . $veryfy_result . "\n return_url_log:sign=" . $_GET ["sign"] . "&mysign=" . $this->_mysign . "&" . $this->create_link_string ( $sort_get ) );
			if (preg_match ( "/true/is", urldecode ( $veryfy_result ) ) && $this->_mysign == $_GET ["sign"]) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	/**
	 * 记录消息日志
	 * @param string $data
	 */
	function log_result($data) {
		$fp = fopen ( "log.txt", "a" );
		flock ( $fp, LOCK_EX );
		fwrite ( $fp, "执行日期：" . strftime ( "%Y%m%d%H%M%S", time () ) . "\n" . $data . "\n" );
		flock ( $fp, LOCK_UN );
		fclose ( $fp );
	}
	
	/**
	 * 实现多种字符编码方式
	 * @param $input 需要编码的字符串
	 * @param $_output_charset 输出的编码格式
	 * @param $_input_charset 输入的编码格式
	 * return 编码后的字符串
	 */
	function charset_encode($input, $_output_charset, $_input_charset) {
		$output = "";
		if (! isset ( $_output_charset ))
			$_output_charset = $_input_charset;
		if ($_input_charset == $_output_charset || $input == null) {
			$output = $input;
		} elseif (function_exists ( "mb_convert_encoding" )) {
			$output = mb_convert_encoding ( $input, $_output_charset, $_input_charset );
		} elseif (function_exists ( "iconv" )) {
			$output = iconv ( $_input_charset, $_output_charset, $input );
		} else
			die ( "sorry, you have no libs support for charset change." );
		return $output;
	}
	
	/**
	 * 实现多种字符解码方式
	 * @param $input 需要解码的字符串
	 * @param $_output_charset 输出的解码格式
	 * @param $_input_charset 输入的解码格式
	 * return 解码后的字符串
	 */
	function charset_decode($input, $_input_charset, $_output_charset) {
		$output = "";
		isset ( $_input_charset ) or $_input_charset = $this->_input_charset;
		if ($_input_charset == $_output_charset || $input == null) {
			$output = $input;
		} elseif (function_exists ( "mb_convert_encoding" )) {
			$output = mb_convert_encoding ( $input, $_output_charset, $_input_charset );
		} elseif (function_exists ( "iconv" )) {
			$output = iconv ( $_input_charset, $_output_charset, $input );
		} else
			die ( "sorry, you have no libs support for charset changes." );
		return $output;
	}
	/**
	 * 回调数据合并
	 * Enter description here ...
	 */
	static function data_merge() {
		$data = array_filter ( array_merge ( $_GET, $_POST ) );//数据合并
		if(strtoupper($data['_input_charset'])!=strtoupper(CHARSET)){
			$data = kekezu::gbktoutf($data);
		}
		return $data;
	}
}