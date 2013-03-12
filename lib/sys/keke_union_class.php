<?php
require_once S_ROOT . '/keke_client/keke/keke_tool_class.php';
require_once S_ROOT . '/keke_client/keke/keke_service_class.php';
require_once S_ROOT . '/keke_client/keke/config.php';
class keke_union_class {
	private $_task_id; 
	private $_model_id; 
	public $_r_task_id; 
	private $_model_code; 
	private $_config; 
	private $_data; 
	private $_uid;
	function __construct($task_id, $data = array()) {
		global $config;
		self::doExit ( $config ['application'] );
		if (! empty ( $task_id )) {
			$this->_config = $config;
			$this->_task_id = intval ( $task_id );
			$this->init_task ( $task_id ); 
		}
		$this->_data = $data;
	}
	private function init_task($task_id = '') {
		if (! $this->_task_id && $task_id) {
			$this->_task_id = $task_id;
		}
		$sql = "select `task_id`,`model_id`,`uid`,`task_union`,`r_task_id` from `%switkey_task` where task_id=%d";
		$result = db_factory::get_one ( sprintf ( $sql, TABLEPRE, $this->_task_id ) );
		if (! $result || ! $result ['task_union']) { 
			return false;
		}
		$this->_model_id = $result ['model_id'];
		$this->_r_task_id = $result ['r_task_id'];
		$this->_uid       = $result['uid'];
		$this->_model_code = $this->get_model_code ();
	}
	public static function union_request($service, $comm_data = array(), $return_type = 'url', $method = 'post', $sign_type = 'MD5', $_input_charset = 'GBK') {
		global $config;
		self::doExit ( $config ['application'] );
		$request = keke_tool_class::union_build ( $config, $service, $comm_data, $return_type, $method, $sign_type, $_input_charset );
		kekezu::get_remote_data ( $request );
	}
	static function create_task($task_id, $is_return = false, $data = array(), $indetify = 1, $is_pub = false, $type = 'form') {
		global $config, $kekezu, $_K;
		self::doExit ( $config ['application'] );
		switch ($is_return) {
			case false :
				if(!$is_pub&&$indetify==2&&!$config['auto_commit']){
					return false;
				}
				if (is_array ( $task_id )) {
					$task_info = $task_id;
				} elseif (is_numeric ( $task_id )) {
					$sql = "select `task_id`,`model_id`,`task_cash_coverage`,`task_file`,`task_cash`,`task_title`,`task_desc`,`task_status`,`uid`,`username`,`start_time`,`sub_time` from %switkey_task where task_id=%d and task_union=0";
					$task_info = db_factory::get_one ( sprintf ( $sql, TABLEPRE, intval ( $task_id ) ) );
					if (! $task_info) {
						return false;
					}
				}
				$model_code = $kekezu->_model_list [$task_info ['model_id']] ['model_code'];
				$task_info ['task_cash_coverage'] and $task_info ['cash_coveage'] = self::get_cash_cove ( $model_code, $task_info ['task_cash_coverage'] );
				$task_info ['task_uid'] = $task_info ['uid'];
				$task_info ['task_owner'] = $task_info ['username'];
				$task_info ['outer_task_id'] = "{$config['log']}-{$model_code}-{$task_info['task_id']}";
				$task_info ['task_amount'] = $task_info ['task_cash'];
				if ($task_info ['task_file']) {
					$files = db_factory::query ( 'select CONCAT("' . $_K ['siteurl'] . '/",`save_name`) file,`file_name` from ' . TABLEPRE . 'witkey_file where file_id in (' . $task_info ['task_file'] . ')' );
					if ($files) {
						$file = '';
						foreach ( $files as $v ) {
							$file .= $v ['file_name'] . '#' . $v ['file'] . ',';
						}
						$files = rtrim ( $file, ',' );
						$task_info ['task_file'] = $files;
					}
				}
				$task_info ['indetify'] = $indetify;
				if ($is_pub) {
					$releation_id = db_factory::get_count ( ' select union_rid from ' . TABLEPRE . 'witkey_space where uid=' . $task_info ['task_uid'] );
					$task_info ['is_pub'] = $is_pub;
					$task_info ['releation_id'] = $releation_id;
					db_factory::execute('update '.TABLEPRE.'witkey_space set union_assoc=1 where uid='.$task_info ['task_uid']);
				}
				$inter = 'create_task'; 
				$request = keke_tool_class::union_build ( $config, $inter, $task_info, $type );
				return $request;
				break;
			case true :
				$response = array ();
				$url = $_K ['siteurl'] . "/index.php?do=task&task_id=" . $data ['task_id'];
				$response ['url'] = $url;
				switch ($data ['is_success']) {
					case "T" : 
						$data ['is_pub'] == 2 and $task_union = 3 or $task_union = 1;
						$sql = sprintf ( " update %switkey_task set r_task_id ='%d',task_union=%d where task_id='%d'", TABLEPRE, $data ['r_task_id'], $task_union, $data ['task_id'] );
						$res = db_factory::execute ( $sql );
						$response ['type'] = "success";
						$response ['notice'] = "联盟任务发布成功";
						break;
					case "F" :
						$response ['type'] = "error";
						$response ['notice'] = "联盟任务发布失败";
						break;
				}
				return $response;
				break;
		}
	}
	public function task_close($data = array(), $is_return = false, $resp = array()) {
		global $config;
		self::doExit ( $config ['application'] );
		switch ($is_return) {
			case false :
				$comm_data = array ();
				$inter = 'task_close'; 
				$url = keke_tool_class::union_build ( $config, $inter, $data );
				kekezu::get_remote_data ( $url );
				db_factory::execute ( 'update ' . TABLEPRE . 'witkey_space set `union_user`=0,`union_assoc`=0,`union_rid`=0 where uid=' . $this->_uid );
				break;
			case true :
				switch ($resp ['indetify']) {
					case 1 : 
						db_factory::execute ( ' update ' . TABLEPRE . 'witkey_task set task_status=8 where r_task_id=' . $resp ['r_task_id'] );
						break;
					case - 1 : 
						db_factory::execute ( ' update ' . TABLEPRE . 'witkey_task set task_status=9 where r_task_id=' . $resp ['r_task_id']);
						break;
				}
		}
	}
	public static function pub_redirect($app_uid) {
		global $kekezu, $config;
		self::doExit ( $config ['application'] );
		$service = 'keke_login';
		$comm_data = array ('from_uid' => $kekezu->_uid, 'from_username' => $kekezu->_username, 'login_type' => 2, 'app_uid' => $app_uid );
		$jump_url = keke_tool_class::union_build ( $config, $service, $comm_data );
		self::jump ( $jump_url );
	}
     public static function union_task_submit($uinfo,$task_id){
       $url = '';
       $task_info = db_factory::get_one('select * from '.TABLEPRE.'witkey_task where task_id='.$task_id);
		if($uinfo['union_user']&&!$uinfo['union_assoc']){
			$url = keke_union_class::create_task($task_info,false,array(),2,true,'url');
		}else{
			$url = keke_union_class::create_task($task_info,false,array(),2,false,'url');
		}
		kekezu::get_remote_data($url);
       }
	public function view_task() {
		global $uid, $username;
		$r_task_id = $this->_r_task_id;
		if (! $r_task_id) {
			return false;
		}
		$inter = 'keke_login';
		$comm_data = array ('r_task_id' => intval ( $r_task_id ), 'from_uid' => $uid, 'from_username' => $username );
		$jump_url = keke_tool_class::union_build ( $this->_config, $inter, $comm_data );
		self::jump ( $jump_url );
	}
	static function get_cash_cove($model_code, $rule_id) {
		global $kekezu;
		$cove_arr = $kekezu->get_cash_cove ( $model_code );
		$cove = $cove_arr [$rule_id];
		return $cove ['start_cove'] . '-' . $cove ['end_cove'];
	}
	static function get_task_list() {
		global $config;
		self::doExit ( $config ['application'] );
		$inter = 'get_task'; 
		$config ['return_url'] = str_replace ( '&', '|', 'http://' . $_SERVER [SERVER_NAME] . $_SERVER [REQUEST_URI] );
		return keke_tool_class::union_build ( $config, $inter );
	}
	private function get_model_code() {
		global $kekezu;
		$model_arr = $kekezu->_model_list;
		return $model_arr [$this->_model_id] ['model_code'];
	}
	public static function jump($url) {
		header ( "Location:" . $url );
		exit ();
	}
	public static function doExit($application = 1) {
		if(!$application){
			return false;
		}
	}
	public static function hand_link($task_info) {
		global $_K, $config;
		if ($config ['application']) {
			if ($task_info ['task_union'] == 2 && $task_info ['r_task_id']) {
				return $_K ['siteurl'] . '/index.php?do=task&task_id=' . $task_info ['task_id'] . '&u=1';
			}
		} else {
			return false;
		}
	}
}