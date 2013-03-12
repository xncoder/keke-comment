<?php
/* *
 * 类名：AlipayNotify
 * 功能：支付宝通知处理类
 * 详细：处理支付宝各接口通知返回
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考

 *************************注意*************************
 * 调试通知返回时，可查看或改写log日志的写入TXT里的数据，来检查通知返回是否正常
 */

require_once ("alipay_core.function.php");
require_once ("alipay_dsa.function.php");

class AlipayNotify {
	/**
	 * HTTPS形式消息验证地址
	 */
	public $_https_verify_url = 'https://capi.p21.alipay.net/cooperate/gateway.do?service=notify_verify&';
	/**
	 * HTTP形式消息验证地址
	 */
	public $_http_verify_url = 'http://capi.p21.alipay.net/trade/notify_query.do?';

	public $_ali_public_key_path;

	public $_transport;
	public $_sign_type;
	public $_partner;
	public $_input_charset;

	function __construct($partner, $sign_type = 'DSA', $_input_charset = 'GBK', $transport = 'http') {
		$this->_ali_public_key_path = S_ROOT . '/payment/alipay_trust/key/alipay_public_key.pem'; //支付宝公钥存放地址
		$this->_partner = $partner;
		$this->_sign_type = $sign_type;
		$this->_input_charset = strtoupper ( $_input_charset );
		$this->_transport = $transport;
	}
	/**
	 * 针对notify_url验证消息是否是支付宝发出的合法消息
	 * @return 验证结果
	 */
	function verifyNotify() {
		if (empty ( $_POST )) { //判断POST来的数组是否为空
			return false;
		} else {
			//获得签名验证结果
			$is_sign = $this->getSignVeryfy ( $_POST, $_POST ['sign'] );
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'true';
			if (! empty ( $_POST ["notify_id"] )) {
				$responseTxt = $this->getResponse ( $_POST ["notify_id"] );
			}

			//写日志记录
			//$log_text = "responseTxt=".$responseTxt."\n notify_url_log:sign=".$_POST["sign"]."&is_sign=".$is_sign.",";
			//$log_text = $log_text.createLinkString($_POST);
			//logResult($log_text);


			//判断responsetTxt是否为true，is_sign是否为true
			//$responseTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//$is_sign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			if (preg_match ( "/true$/i", $responseTxt ) && $is_sign) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * 针对return_url验证消息是否是支付宝发出的合法消息
	 * @return 验证结果
	 */
	function verifyReturn() {
		if (empty ( $_GET )) { //判断POST来的数组是否为空
			return false;
		} else {
			//获得签名验证结果
			$is_sign = $this->getSignVeryfy ( $_GET, $_GET ['sign'] );
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'true';
			if (! empty ( $_GET ["notify_id"] )) {
				$responseTxt = $this->getResponse ( $_GET ["notify_id"] );
			}

			if (preg_match ( "/true$/i", $responseTxt ) && $is_sign) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * 获取返回时的签名验证结果
	 * @param $para_temp 通知返回来的参数数组
	 * @param $sign 支付宝返回的签名结果
	 * @return 获得签名验证结果
	 */
	function getSignVeryfy($para_temp, $sign) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilter ( $para_temp );

		//对待签名参数数组排序
		$para_sort = argSort ( $para_filter );

		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createLinkString ( $para_sort );

		//获得签名验证结果
		$is_sign = false;
		if (strtoupper ( trim ( $this->_sign_type ) ) == 'DSA') {
			$is_sign = verify ( $prestr, trim ( $this->_ali_public_key_path ), $sign );
		}
		return $is_sign;
	}

	/**
	 * 获取远程服务器ATN结果,验证返回URL
	 * @param $notify_id 通知校验ID
	 * @return 服务器ATN结果
	 * 验证结果集：
	 * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
	 * true 返回正确信息
	 * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
	 */
	function getResponse($notify_id) {
		$transport = strtolower ( trim ( $this->_transport ) );
		$partner = trim ( $this->_partner );
		$veryfy_url = '';
		if ($transport == 'https') {
			$veryfy_url = $this->_https_verify_url;
		} else {
			$veryfy_url = $this->_http_verify_url;
		}
		$veryfy_url = $veryfy_url . "partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = getHttpResponse ( $veryfy_url, $this->_input_charset );

		return $responseTxt;
	}
	/**
	 * 解析xml数据
	 * @param $xml_str xml串
	 */
	static function get_xml_toarr($xml_str,$charset='GBK') {
		$xml_str = ltrim ( urldecode ( $xml_str ), "xml=" );
		$arr = explode ( "&", $xml_str );
		$string = <<<XML
$arr[0]
XML;
		$xml_o = simplexml_load_string ( $string );
		$xml_arr = kekezu::objtoarray ( $xml_o );
		if ($charset== "GBK") {
			$xml_arr = kekezu::utftogbk ( $xml_arr );
		}
		return $xml_arr;
	}
	/**
	 * 回调数据合并
	 * Enter description here ...
	 */
	function data_merge($charset='GBK') {
		$data = array_filter ( array_merge ( $_GET, $_POST ) );
		$notify_data =self::get_xml_toarr ( $data ['resultMsg'],$charset); //解析POST过来的xml串
		$notify_data  and $data        = array_merge($data,$notify_data);
		if($data['request']){
			$data = array_merge($data,$data['request']);
			$data['request']['content'] and $data = array_merge($data,$data['request']['content']);
			if($data['request']['param ']){
				$arr1 = $data['request']['param'];
				$arr2['outer_task_id'] = $data[1];
				list($model_code,$task_id) = explode('-',$data[1],2);
				$interface = $_SESSION['trust_'.$task_id];
				switch($interface){
					case 'pt_cancel':
						$arr2['cancel_transfer_detail'] = $data[2];
						break;
					case 'pt_confirm':
						$arr2['transfer_detail'] = $data[5];
						break;
				}
			}
			unset($data['request']);
		}
		if($data['response']){
			$data['response']['task_info'] and $data = array_merge($data,$data['response']['task_info']);
			unset($data['response']);
		}
		unset($data['_form_token']);
		unset($data['resultMsg']);
		return $data; //数据合并
	}
}