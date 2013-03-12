<?php
abstract class wap_release_class extends keke_task_release_class {
	public function __construct($model_id) {
		parent::__construct ( $model_id );
		unset($this->_pay_item);
	}
	public function pub_mode_init($std_cache_name, $data = array()) {}
	public function get_task_config() {}
	public function pub_task() {}
	abstract function wap_release();
	public function wap_public() {
		$_D = $_REQUEST; 
		$task_obj = $this->_task_obj; 
		$user_info = $this->_user_info;
		$txt_task_title = kekezu::str_filter ( $_D ['task_title'] ); 
		$task_obj->setTask_title ( $txt_task_title );
		$task_obj->setModel_id ( $this->_model_id ); 
		$task_obj->setProfit_rate ( $this->_task_config ['task_rate'] ); 
		$task_obj->setTask_fail_rate ( $this->_task_config ['task_fail_rate'] ); 
		$task_obj->setTask_cash ( $_D ['task_cash'] ); 
		$task_obj->setReal_cash ( $_D ['task_cash'] * (100 - $this->_task_config ['task_rate']) / 100 ); 
		$task_obj->setStart_time ( time () ); 
		$task_obj->setSub_time ( strtotime($_D ['end_time']) ); 
		$task_obj->setEnd_time ( strtotime($_D ['end_time']) + $this->_task_config ['choose_time'] * 24 * 3600 ); 
		$task_obj->setIndus_id ( $_D ['indus_id'] ); 
		$task_obj->setIndus_pid ( $_D ['indus_pid'] );
		$tar_content = kekezu::str_filter ( $_D ['task_desc'] );
		$task_obj->setTask_desc ( $tar_content ); 
		$task_obj->setUid ( $this->_uid );
		$task_obj->setUsername ( $this->_username );
		$task_obj->setKf_uid ( $this->_kf_uid ); 
	}
	public function wap_update($task_id) {
		global $_K, $_lang;
		$t = array ('success', '发布成功' );
		$_D = $_REQUEST;
		$user_info = $this->_user_info; 
		$task_obj = $this->_task_obj; 
		if ($task_id) {
			db_factory::execute ( "update " . TABLEPRE . "witkey_space set pub_num = pub_num+1 where uid=$this->_uid " );
			$task_status = $task_obj->getTask_status (); 
			$task_title = $task_obj->getTask_title ();
			switch ($task_status) {
				case "2" :
					$this->wap_order ( $task_id, $this->_model_id );
					$feed_arr = array ("feed_username" => array ("content" => $this->_username, "url" => "index.php?do=space&member_id={$this->_uid}" ), "action" => array ("content" => $_lang ['pub_task'], "url" => "" ), "event" => array ("content" => " $task_title", "url" => "index.php?do=task&task_id=$task_id" ) );
					kekezu::save_feed ( $feed_arr, $this->_uid, $this->_username, 'pub_task', $task_id );
					$this->notify_user ( $task_id, 2 );
					break;
				case "1" : 
					$this->wap_order ( $task_id, $this->_model_id );
					$this->notify_user ( $task_id, 1 );
					break;
				case "0" : 
					$total_cash = $this->get_total_cash ( $_D ['task_cash'] );
					$pay_cash = $total_cash - ($user_info ['balance'] + $user_info ['credit']);
					$pay_cash = ceil ( $pay_cash );
					$order_id = $this->wap_order ( $task_id, $this->_model_id, 'wait' );
					$this->notify_user ( $task_id, 0 );
					$t [0] = 'fail';
					$t [1] = 'Failure to pub, balance shortage ';
					break;
			}
		}
		return $t;
	}
	public function wap_order($task_id, $model_id, $order_status = 'ok') {
		global $uid, $username, $_lang;
		$_D = $_REQUEST;
		$oder_obj = new Keke_witkey_order_class (); 
		$order_detail = new Keke_witkey_order_detail_class (); 
		$task_cash = $_D ['task_cash']; 
		$order_name = $_D ['task_title']; 
		$order_amount = $this->get_total_cash ( $task_cash ); 
		$order_body = $_lang ['pub_task'] . "<a href=\"index.php?do=task&task_id=$task_id\">" . $order_name . "</a>"; 
		$order_id = keke_order_class::create_order ( $model_id, $uid, $username, $order_name, $order_amount, $order_body, $order_status );
		if ($order_id) {
			keke_order_class::create_order_detail ( $order_id, $order_name, 'task', intval ( $task_id ), $task_cash );
			if ($this->_task_obj->getTask_status () != 0) {
				$this->_model_info ['model_code'] == 'tender' and $site_profit = $task_cash;
				$fina_id = keke_finance_class::cash_out ( $this->_uid, $task_cash, 'pub_task', $site_profit, 'task', $task_id ); 
				$fina_id and keke_order_class::update_fina_order ( $fina_id, $order_id );
			}
			return $order_id;
		}
	}
	public function wap_priv(){
		global $_lang;
		$this->_priv ['pass'] or kekezu::echojson(array('r'=>$this->_priv ['notice'] . $_lang ['not_rights_pub_task']),0);
	}
}