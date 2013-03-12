<?php

/** 
 * @author shangk
 * @property 订金招标发布类 
 */
keke_lang_class::load_lang_class('dtender_release_class');
class dtender_release_class extends keke_task_release_class {
	public $_cash_cove; //招标区间
	
	public static function get_instance($model_id,$pub_mode='professional') {
		static $obj = null;
		if ($obj == null) {
			$obj = new dtender_release_class ( $model_id,$pub_mode);
		}
		return $obj;
	}
	function __construct($model_id,$pub_mode='professional') {
		parent::__construct ( $model_id,$pub_mode);
		$this->_std_obj->_release_info['txt_task_cash'] and $cash = $this->_std_obj->_release_info['txt_task_cash'] or $cash=$this->_task_config['min_cash'];
		$this->_default_max_day = keke_task_release_class::get_default_max_day($cash, $model_id,$this->_task_config['min_day']);
		$this->get_task_config ();
		$this->priv_init();
		$this->cash_cove_init();
	}
	
	/**
	 * 发布任务权限判断
	 */
	public function priv_init() {
		$priv_arr = dtender_priv_class::get_priv ('',$this->_model_id, $this->_user_info, '2' );
		$this->_priv = $priv_arr ['pub'];
	}
	

	public function cash_cove_init(){
		global $kekezu;
		$this->_cash_cove = $kekezu->get_cash_cove('dtender');
	
		
	}
	/**
	 * 初始化任务配置
	 * @return   void
	 */
	public function get_task_config() {
		global $model_list;
		$model_list [$this->_model_id] ['config'] and $this->_task_config = unserialize ( $model_list [$this->_model_id] ['config'] ) or $this->_task_config = array ();
	}

	/**
	 * 发布模式进行信息
	 * @param $std_cache_name session名
	 * @param $data 外部传入参数
	 */
	function pub_mode_init($std_cache_name, $data = array()) {
		global $kekezu;
		global $_lang;
		$release_info = $this->_std_obj->_release_info;
		switch ($this->_pub_mode) {
			case "professional" :
				break;
			case "guide" :
				break;
			case "onekey" :
				if (! $release_info) {
					$sql = " select model_id,task_title,task_desc,indus_id,indus_pid,
						task_cash,task_cash_coverage,contact from %switkey_task where task_id='%d' and model_id='%d'";
					$task_info = db_factory::get_one ( sprintf ( $sql, TABLEPRE, $data ['t_id'] ,$this->_model_id));
					$task_info or kekezu::show_msg($_lang['operate_notice'],$_SERVER['HTTP_REFERER'],3,$_lang['not_exsist_relation_task_and_not_user_onekey'],"warning");
					
					$release_info = $this->onekey_mode_format($task_info);
					$release_info ['task_cash_cove'] = intval ( $task_info ['task_cash_coverage'] );
					$this->save_task_obj ( $release_info, $std_cache_name ); //信息保存
				}
				break;
		}
	}
	
	
	/**
	 * 任务发布
	 * 		此方法只是用来产生任务记录
	 * @param $obj_name session存储对象名
	 */
	public function pub_task() {
		$task_obj = $this->_task_obj;
		$std_obj = $this->_std_obj;
		//发布信息公共处理部
		$this->public_pubtask();		
		$real_cash = $this->_task_config['deposit'];//任务佣金	
		//$task_obj->setTask_cash ($real_cash); //任务金额重置			
		$task_obj->setReal_cash ($real_cash ); //实际佣金重置		
		$this->set_dtask_status($this->get_total_cash($real_cash), $real_cash);		
		$task_obj->setStart_time ( time () ); //任务开始时间
		$task_obj->setEnd_time ( time () + intval ( $this->_task_config['bid_time']+$this->_task_config ['select_time']  ) * 24 * 3600 ); //任务投稿期
		$task_obj->setSub_time ( time () + intval ( $this->_task_config ['select_time'] ) * 24 * 3600 ); //任务选稿期		
		$task_obj->setTask_cash_coverage($this->_std_obj->_release_info['task_cash_cove']);
		$cash_cove = $this->_cash_cove;
		$cove = $cash_cove [$this->_std_obj->_release_info['task_cash_cove']];
		$task_cash = $cove ['end_cove'];
		$task_cash or $task_cash = $cove ['start_cove'];
		$task_obj->setTask_cash ( $task_cash );
		$task_id = $task_obj->create_keke_witkey_task ();
		return $task_id;
	}	
	
	public function set_dtask_status($total_cash, $task_cash) {
		global $kekezu;
		$basic_config = $kekezu->_sys_config;
		$balance = $this->_user_info ['balance'];
		$credit = $this->_user_info ['credit'];
		if ($balance + $credit >= $total_cash) { //用户金额满足总花费的情况下
			if ($this->_task_config['open_audit']=='close') { //
				$task_status = '2'; //任务状态可设置为成功发布
			} else{ //发布金额小于最小金额,需要审核
				$task_status = "1"; //任务状态设置为需要审核
			}
			if ($basic_config ['credit_is_allow'] == '2') { //金币关
				$cash_cost = $task_cash;
				$credit_cost = '0';
			} else { //开
				if ($credit >= $task_cash) { //满足总金
					$credit_cost = $task_cash;
					$cash_cost = '0';
				} else {
						$credit_cost = $credit;
						$cash_cost = $task_cash - $credit;
					}
			}
		} else {
			$task_status = "0"; //延迟支付
		}
		$this->_task_obj->setTask_status ( $task_status ); //设置任务状态
		$this->_task_obj->setCash_cost ( $cash_cost ); //现金花费
		$this->_task_obj->setCredit_cost ( $credit_cost ); //金币花费
	}
	
}