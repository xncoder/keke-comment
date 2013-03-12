<?php

define('IN_DISCUZ', TRUE);

define('UC_CLIENT_VERSION', '1.5.0');	//note UCenter 版本标识
define('UC_CLIENT_RELEASE', '20081031');
define('DEBUGUC', 0);
define('API_DELETEUSER', 1);		//note 用户删除 API 接口开关
define('API_RENAMEUSER', 1);		//note 用户改名 API 接口开关
define('API_GETTAG', 1);		//note 获取标签 API 接口开关
define('API_SYNLOGIN', 1);		//note 同步登录 API 接口开关
define('API_SYNLOGOUT', 1);		//note 同步登出 API 接口开关
define('API_UPDATEPW', 1);		//note 更改用户密码 开关
define('API_UPDATEBADWORDS', 1);	//note 更新关键字列表 开关
define('API_UPDATEHOSTS', 1);		//note 更新域名解析缓存 开关
define('API_UPDATECLIENT', 1);		//note 更新客户端缓存 开关
define('API_UPDATECREDIT', 1);		//note 更新用户积分 开关
define('API_GETCREDITSETTINGS', 1);	//note 向 UCenter 提供积分设置 开关
define('API_GETCREDIT', 1);		//note 获取用户的某项积分 开关
define('API_UPDATECREDITSETTINGS', 1);	//note 更新应用积分设置 开关

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');

define('DISCUZ_ROOT', '../');

define('IN_KEKE',true);

//note 普通的 http 通知方式
if(!defined('IN_UC')) {

	error_reporting(0);
	set_magic_quotes_runtime(0);

	defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	require_once DISCUZ_ROOT.'/config/config_ucenter.php';
	require_once DISCUZ_ROOT.'/config/config.inc.php';

	 
	$_DCACHE = $get = $post = array();

	$code = @$_GET['code'];
	parse_str(_authcode($code, 'DECODE', UC_KEY), $get);
	if(TRUE == DEBUGUC)
	{
		$get['realtime'] = date('Y-m-d H:i:s', $get['time']);
		$debugStr = date('Y-m-d H:i:s', time()) . ':' . print_r($get, 1);
		$debugLogFile = dirname(__FILE__)  . '/uc_client_log.txt';
		file_put_contents($debugLogFile, $debugStr, FILE_APPEND);
	}
	if(MAGIC_QUOTES_GPC) {
		$get = _stripslashes($get);
	}

	$timestamp = time();

	if($timestamp - $get['time'] > 3600) {
		exit('Authracation has expiried');
	}
	if(empty($get)) {
		exit('Invalid Request');
	}
	$action = $get['action'];

	require_once DISCUZ_ROOT.'./keke_client/ucenter/lib/xml.class.php';
	$post = xml_unserialize(file_get_contents('php://input'));

	if(in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'getcredit','updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings'))) {
		require_once DISCUZ_ROOT.'./keke_client/ucenter/include/db_mysql.class.php';
		$GLOBALS['db'] = new dbstuff;
		$GLOBALS['db']->connect(DBHOST, DBUSER, DBPASS, DBNAME, 0, true);
		$GLOBALS['tablepre'] = TABLEPRE;
		
		$uc_note = new uc_note();
		exit($uc_note->$get['action']($get, $post));
	} else {
		exit(API_RETURN_FAILED);
	}

	//note include 通知方式
} else {

	/*	require_once DISCUZ_ROOT.'./connfig/config_ucenter.php';
	 require_once DISCUZ_ROOT.'./keke_client/ucenter/include/db_mysql.class.php';
	 require_once DISCUZ_ROOT.'/config/config.inc.php';
	 $GLOBALS['db'] = new dbstuff;
	 $GLOBALS['db']->connect(DBHOST, DBUSER, DBPASS, DBNAME, 0, true);
	 $GLOBALS['tablepre'] = TABLEPRE;*/
	//unset($uc_dbhost, $uc_dbuser, $uc_dbpw, $uc_dbname, $uc_connect);
}

class uc_note {

	var $dbconfig = '';
	var $db = '';
	var $tablepre = '';
	var $appdir = '';

	function _serialize($arr, $htmlon = 0) {
		if(!function_exists('xml_serialize')) {
			include_once DISCUZ_ROOT.'./keke_client/ucenter/lib/xml.class.php';
		}
		return xml_serialize($arr, $htmlon);
	}

	function uc_note() {
		$this->appdir = DISCUZ_ROOT;
		$this->dbconfig = $this->appdir.'./config/config.inc.php';
		$this->db = $GLOBALS['db'];
		$this->tablepre = TABLEPRE;
	}
	/*通讯测试 */
	function test($get, $post) {
		return API_RETURN_SUCCEED;
	}
	/**
	 * 删除用户
	 * @param unknown_type $get
	 * @param unknown_type $post
	 */
	function deleteuser($get, $post) {
		$uids = $get['ids'];
		!API_DELETEUSER && exit(API_RETURN_FORBIDDEN);

		$db = $this->db;
		$tablepre = $this->tablepre;
		$db->query("delete from ".$tablepre."witkey_member where uid in($uids)");
		$db->query("delete from ".$tablepre."witkey_space where uid in($uids)");
		return API_RETURN_SUCCEED;
	}
	/**
	 * 重命名
	 * @param unknown_type $get
	 * @param unknown_type $post
	 */
	function renameuser($get, $post) {
		$uid = $get['uid'];
		$usernameold = $get['oldusername'];
		$usernamenew = $get['newusername'];
		if(!API_RENAMEUSER) {
			return API_RETURN_FORBIDDEN;
		}

		$db = $this->db;
		$tablepre = $this->tablepre;
		$db->query("update ".$tablepre."witkey_member set username='$usernamenew''' where uid=$uid and username='$usernameold'");
		$db->query("update ".$tablepre."witkey_space set username='$usernamenew' where uid=$uid and username='$usernameold'");

		return API_RETURN_SUCCEED;
	}

	function gettag($get, $post) {
		$name = $get['id'];
		if(!API_GETTAG) {
			return API_RETURN_FORBIDDEN;
		}

		$return = array();
		return $this->_serialize($return, 1);
	}
	/**
	 * 登录
	 * @param unknown_type $get
	 * @param unknown_type $post
	 */
	function synlogin($get, $post) {
		$syn_uid = $get['uid'];
		$syn_username = $get['username'];
		if(!API_SYNLOGIN) {
			return API_RETURN_FORBIDDEN;
		}

		require_once  $this->appdir.'./app_comm.php';
		$_SESSION['uid'] = $syn_uid;
		$_SESSION['username'] = $syn_username;
		//kekezu::prom_check();
		//最新登录时间
		$space_obj = new Keke_witkey_space_class();
		$space_obj->setUid($syn_uid);
		$space_obj->setLast_login_time(time());
		$space_obj->edit_keke_witkey_space();
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		return API_RETURN_SUCCEED;
		//		_setcookie('Example_auth', _authcode($uid."\t".$username, 'ENCODE'));
	}

	function synlogout($get, $post) {
		if(!API_SYNLOGOUT) {
			return API_RETURN_FORBIDDEN;
		}
		//note 同步登出 API 接口
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		require_once  $this->appdir.'./app_comm.php';
		$_SESSION['uid'] = '';
		$_SESSION['username'] = '';
		setcookie ( 'user_login', '' );
		setcookie ( 'prom_cke', '' );
		setcookie ( 'score_log', '' );
		unset($_COOKIE);
		unset($_SESSION);
		return API_RETURN_SUCCEED;
	}
	/**
	 * 更新密码
	 * @param unknown_type $get
	 * @param unknown_type $post
	 */
	function updatepw($get, $post) {
		if(!API_UPDATEPW) {
			return API_RETURN_FORBIDDEN;
		}
/*		$username = $get['username'];
		$password = $get['password'];

		$db = $this->db;
		$tablepre = $this->tablepre;
		
		$db->query("update ".$tablepre."witkey_space set password = '$password' where username = '$username'");
		$db->query("update ".$tablepre."witkey_member set password = '$password' where username = '$username'");
*/
		return API_RETURN_SUCCEED;
	}

	function updatebadwords($get, $post) {
		if(!API_UPDATEBADWORDS) {
			return API_RETURN_FORBIDDEN;
		}
		//		$cachefile = $this->appdir.'./keke_client/ucenter/data/cache/badwords.php';
		//		$fp = fopen($cachefile, 'w');
		//		$data = array();
		//		if(is_array($post)) {
		//			foreach($post as $k => $v) {
		//				$data['findpattern'][$k] = $v['findpattern'];
		//				$data['replace'][$k] = $v['replacement'];
		//			}
		//		}
		//		$s = "<?php\r\n";
		//		$s .= '$_CACHE[\'badwords\'] = '.var_export($data, TRUE).";\r\n";
		//		fwrite($fp, $s);
		//		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	function updatehosts($get, $post) {
		if(!API_UPDATEHOSTS) {
			return API_RETURN_FORBIDDEN;
		}
		/*		$cachefile = $this->appdir.'./keke_client/ucenter/data/cache/hosts.php';
		 $fp = fopen($cachefile, 'w');
		 $s = "<?php\r\n";
		 $s .= '$_CACHE[\'hosts\'] = '.var_export($post, TRUE).";\r\n";
		 fwrite($fp, $s);
		 fclose($fp);*/
		return API_RETURN_SUCCEED;
	}

	function updateapps($get, $post) {
		if(!API_UPDATEAPPS) {
			return API_RETURN_FORBIDDEN;
		}
		$UC_API = $post['UC_API'];
		if(empty($post) || empty($UC_API)) {
			return API_RETURN_SUCCEED;
		}
		$cachefile = $this->appdir.'./keke_client/ucenter/data/cache/apps.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	function updateclient($get, $post) {
		if(!API_UPDATECLIENT) {
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = $this->appdir.'./keke_client/ucenter/data/cache/settings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	function updatecredit($get, $post) {
		if(!API_UPDATECREDIT) {
			return API_RETURN_FORBIDDEN;
		}
		$credit = $get['credit'];
		$amount = $get['amount'];
		$uid = $get['uid'];

		require_once $this->appdir.'./keke_client/ucenter/data/cache/creditsettings.php'; 
        
		$this->db->query("UPDATE ".$this->tablepre."witkey_space SET experience_value=experience_value+'$amount' WHERE uid='$uid'");

		
         
		return API_RETURN_SUCCEED;
 
	}

	function getcredit($get, $post) {
		if(!API_GETCREDIT) {
			return API_RETURN_FORBIDDEN;
		}
		$uid = intval($get['uid']);
		$db = $this->db;
		echo $credit = $db->result_first("select experience_value FROM ".$this->tablepre."witkey_space WHERE uid='$uid'");
		//return intval($credit); 
	}

	function getcreditsettings($get, $post) {
		if(!API_GETCREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}

		$credits = array();
		$credits[1] = array("积分","个");
		return $this->_serialize($credits);
	}


	function updatecreditsettings($get, $post) {
		if(!API_UPDATECREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}

		$outextcredits = array();

		foreach($get['credit'] as $appid => $credititems) {
			if($appid == UC_APPID) {
				foreach($credititems as $value) {
					$outextcredits[$value['appiddesc'].'|'.$value['creditdesc']] = array(
						'creditsrc' => $value['creditsrc'],
						'title' => $value['title'],
						'unit' => $value['unit'],
						'ratio' => $value['ratio']
					);
				}
			}
		}

		$cachefile = $this->appdir.'./keke_client/keke/data/cache/creditsettings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'creditsettings\'] = '.var_export($outextcredits, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		return API_RETURN_SUCCEED;
	}
}

//note 使用该函数前需要 require_once $this->appdir.'./config.inc.php';
function _setcookie($var, $value, $life = 0, $prefix = 1) {
	global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
	setcookie(($prefix ? $cookiepre : '').$var, $value,
	$life ? $timestamp + $life : 0, $cookiepath,
	$cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
}

function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function _stripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = _stripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}