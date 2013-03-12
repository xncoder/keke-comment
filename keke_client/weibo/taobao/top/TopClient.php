<?php
class TopClient {
	public $appkey;
	
	public $secretKey;
	
	public $gatewayUrl = "http://gw.api.taobao.com/router/rest";
	
	public $format = "json";
	public $_user_get_request_obj;
	
	public $_error;
	protected $signMethod = "md5";
	
	protected $apiVersion = "2.0";
	
	protected $sdkVersion = "top-sdk-php-20110701";
	function __construct() {
		require_once 'request/UserGetRequest.php';
		require_once 'logger/logger.php';
		$this->_user_get_request_obj = new UserGetRequest ();
	}
	protected function generateSign($params) {
		ksort ( $params );
		
		$stringToBeSigned = $this->secretKey;
		foreach ( $params as $k => $v ) {
			if ("@" != substr ( $v, 0, 1 )) {
				$stringToBeSigned .= "$k$v";
			}
		}
		unset ( $k, $v );
		$stringToBeSigned .= $this->secretKey;
		
		return strtoupper ( md5 ( $stringToBeSigned ) );
	}
	
	public function curl($url, $postFields = null) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_FAILONERROR, false );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		
		if (is_array ( $postFields ) && 0 < count ( $postFields )) {
			$postBodyString = "";
			$postMultipart = false;
			foreach ( $postFields as $k => $v ) {
				if ("@" != substr ( $v, 0, 1 )) //判断是不是文件上传
{
					$postBodyString .= "$k=" . urlencode ( $v ) . "&";
				} else //文件上传用multipart/form-data，否则用www-form-urlencoded
{
					$postMultipart = true;
				}
			}
			unset ( $k, $v );
			curl_setopt ( $ch, CURLOPT_POST, true );
			if ($postMultipart) {
				curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postFields );
			} else {
				curl_setopt ( $ch, CURLOPT_POSTFIELDS, substr ( $postBodyString, 0, - 1 ) );
			}
		}
		$reponse = curl_exec ( $ch );
		
		if (curl_errno ( $ch )) {
			throw new Exception ( curl_error ( $ch ), 0 );
		} else {
			$httpStatusCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
			if (200 !== $httpStatusCode) {
				throw new Exception ( $reponse, $httpStatusCode );
			}
		}
		curl_close ( $ch );
		return $reponse;
	}
	/**
	 * 请求用户信息。并跳转
	 */
	function process_user_oauth($top_sign, $top_appkey, $top_parameters, $top_session, $call_back = null) {
		global $kekezu, $_K;
		$format_simple_data = $this->format_parameters ( $top_parameters );
		//	  	 var_dump($format_simple_data);
		if ($format_simple_data ['vistor_role']) { //用户没有登录
			$this->_error = $this->error_handle ( 3 );
		} else if ($format_simple_data ['ts'] + 300 < time ()) {
			$this->_error = $this->error_handle ( 2 );
		} else { //获取用户信息.并重组
			$req = $this->_user_get_request_obj;
			$req->setFields ( "user_id,uid,nick,sex,buyer_credit,seller_credit,location,created,last_visit,birthday,type,status,alipay_no,alipay_account,alipay_account,email,consumer_protection,alipay_bind" );
			//$req->setFields("user_id,uid,nick,email");
			$req->setNick ( $format_simple_data ['vistor_nick'] );
			$this->appkey = $top_appkey;
			// var_dump($this);
			$resp = $this->execute ( $req, $top_session );
			
			if (! empty ( $resp )) {
				$user_data = $this->format_user_info ( $resp );
				return $_SESSION ['taobao_login_info'] = $user_data;
			
			} else {
				$this->_error = $this->error_handle ( 4 );
			}
		}
	
	}
	/**
	 * 格式化回调的上下文参数
	 * Enter description here ...
	 * @param unknown_type $parameters
	 */
	public function format_parameters($top_parameters) {
		$simple_data = explode ( "&", base64_decode ( $top_parameters ) );
		foreach ( $simple_data as $v ) {
			$v2 = explode ( "=", $v );
			$format_simple_data [$v2 [0]] = $v2 [1];
		}
		return $format_simple_data;
	
	}
	/**
	 * 
	 * 格式化用户信息
	 */
	public function format_user_info($user_obj) {
		
		$user_data ['account'] = $user_obj->user->nick;
		$user_data ['uid'] = $user_obj->user->user_id;
		$user_data ['location'] = $user_obj->user->location->state . ',' . $user_obj->user->location->city;
		$user_data ['email'] = $user_obj->user->email;
		return $user_data;
	}
	protected function logCommunicationError($apiName, $requestUrl, $errorCode, $responseTxt) {
		$localIp = isset ( $_SERVER ["SERVER_ADDR"] ) ? $_SERVER ["SERVER_ADDR"] : "CLI";
		$logger = new LtLogger ();
		$logger->conf ["log_file"] = rtrim ( TOP_SDK_WORK_DIR, '\\/' ) . '/' . "logs/top_comm_err_" . $this->appkey . "_" . date ( "Y-m-d" ) . ".log";
		$logger->conf ["separator"] = "^_^";
		$logData = array (date ( "Y-m-d H:i:s" ), $apiName, $this->appkey, $localIp, PHP_OS, $this->sdkVersion, $requestUrl, $errorCode, str_replace ( "\n", "", $responseTxt ) );
		$logger->log ( $logData );
	}
	
	public function execute($request, $session = null) {
		//组装系统参数
		$sysParams ["app_key"] = $this->appkey;
		$sysParams ["v"] = $this->apiVersion;
		$sysParams ["format"] = $this->format;
		$sysParams ["sign_method"] = $this->signMethod;
		$sysParams ["method"] = $request->getApiMethodName ();
		$sysParams ["timestamp"] = date ( "Y-m-d H:i:s" );
		$sysParams ["partner_id"] = $this->sdkVersion;
		
		if (null != $session) {
			$sysParams ["session"] = $session;
		}
		
		//获取业务参数
		$apiParams = $request->getApiParas ();
		
		//签名
		$sysParams ["sign"] = $this->generateSign ( array_merge ( $apiParams, $sysParams ) );
		//var_dump($sysParams);die();
		//系统参数放入GET请求串
		$requestUrl = $this->gatewayUrl . "?";
		foreach ( $sysParams as $sysParamKey => $sysParamValue ) {
			$requestUrl .= "$sysParamKey=" . urlencode ( $sysParamValue ) . "&";
		}
		$requestUrl = substr ( $requestUrl, 0, - 1 );
		
		//发起HTTP请求
		try {
			$resp = $this->curl ( $requestUrl, $apiParams );
		} catch ( Exception $e ) {
			$this->logCommunicationError ( $sysParams ["method"], $requestUrl, "HTTP_ERROR_" . $e->getCode (), $e->getMessage () );
			return false;
		}
		
		//解析TOP返回结果
		$respWellFormed = false;
		if ("json" == $this->format) {
			$respObject = json_decode ( $resp );
			if (null !== $respObject) {
				$respWellFormed = true;
				foreach ( $respObject as $propKey => $propValue ) {
					$respObject = $propValue;
				}
			}
		} else if ("xml" == $this->format) {
			$respObject = @simplexml_load_string ( $resp );
			if (false !== $respObject) {
				$respWellFormed = true;
			}
		}
		
		//返回的HTTP文本不是标准JSON或者XML，记下错误日志
		if (false === $respWellFormed) {
			$this->logCommunicationError ( $sysParams ["method"], $requestUrl, "HTTP_RESPONSE_NOT_WELL_FORMED", $resp );
			return false;
		}
		
		return $respObject;
	}
	public function error_handle($err_id) {
		$error = array ("1" => "获取授权失败!", "2" => "登录超时!", "3" => "你的淘宝登录失败!", "4" => "参数错误!用户信息获取失败" );
		return $error [$err_id];
	}
}