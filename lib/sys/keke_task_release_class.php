<?php

/**
 * @author       Administrator
 * std_obj 为类的成员对象。用来保存release_info发布信息,att_info附加项信息
 */
keke_lang_class::load_lang_class ( 'keke_task_release_class' );
abstract class keke_task_release_class {
	public $_uid;
	public $_username;
	public $_user_info; //当前用户信息
	public $_kf_info; //客服信息
	

	public $_priv; //发布权限获取
	public $_default_max_day; //默认预算下的最大天数
	

	public $_pub_mode; //发布模式
	

	public $_model_id; //模型编号
	public $_model_info; //模型信息
	public $_task_config; //任务配置
	public $_pay_item; //雇主付费项
	public $_inited = false;
	public $_task_obj; //任务对象
	

	public $_std_obj; //成员对象，存放任务发布信息,增值服务信息，保存入session来实现跨页面传递任务数据
	function __construct($model_id, $pub_mode = 'professional') {
		global $kekezu;
		$this->_task_obj = new Keke_witkey_task_class ();
		$this->_model_id = $model_id;
		$this->_pub_mode = $pub_mode;
		
		$this->_model_info = $kekezu->_model_list [$model_id];
		
		$this->_std_obj = new stdClass ();
		$this->_std_obj->_release_info = array (); //任务发布信息
		

		$this->_std_obj->_att_info = array (); //任务增值服务信息
		

		$this->init ();
	
	}
	function init() {
		if (! $this->_inited) {
			$this->user_info_init ();
			$this->get_rand_kf ();
			
			$this->pay_item_init ();
		
		}
		$this->_inited = true;
	}
	/**
	 * 初始化当前用户信息
	 */
	function user_info_init() {
		global $user_info, $uid, $username;
		$this->_user_info = $user_info;
		$this->_uid = $uid;
		$this->_username = $username;
	}
	/**
	 * 获取附加费用配置
	 * @return   void
	 */
	public function pay_item_init() {
		$this->_pay_item = keke_payitem_class::get_payitem_config ( 'employer', $this->_model_info ['model_code'] );
	}
	/**
	 * 随机抽取客服
	 * @return   void
	 */
	public function get_rand_kf() {
		$this->_kf_info = kekezu::get_rand_kf ();
	}
	
	/**
	 * 获取任务绑定父级行业（没有则读取全行业）
	 * @return   void
	 */
	public function get_bind_indus() {
		global $kekezu;
		if ($this->_model_info ['indus_bid']) {
			$bind_indus = implode ( ',', array_filter ( explode ( ',', $this->_model_info ['indus_bid'] ) ) );
			return kekezu::get_table_data ( '*', "witkey_industry", "indus_id in (select indus_pid from " . TABLEPRE . "witkey_industry where indus_id in({$bind_indus}))", 'listorder desc', '', '', 'indus_id', null );
		} else {
			return $this->_indus_arr = $kekezu->_indus_p_arr;
		
		}
	}
	/**
	 * 获取发布子行业信息(也可用于异步获取)
	 * @param int $indus_pid 父级行业
	 * @param string $ajax异步请求名
	 */
	public function get_task_indus($indus_pid = '', $ajax = '') {
		global $kekezu;
		global $_lang;
		if ($indus_pid > 0) {
			if ($this->_model_info ['indus_bid']) {
				$indus_ids = kekezu::get_table_data ( '*', "witkey_industry", "indus_id in ({$this->_model_info['indus_bid']}) and indus_pid = $indus_pid", 'listorder desc', '', '', 'indus_id', null );
			} else {
				$indus_ids = kekezu::get_table_data ( '*', "witkey_industry", " indus_pid = $indus_pid", 'listorder desc', '', '', 'indus_id', null );
			}
			switch ($ajax == 'show_indus') {
				case "0" :
					return $indus_ids;
					break;
				case "1" :
					$option .= '<option value=""> ' . $_lang ['please_son_industry'] . ' </option>';
					foreach ( $indus_ids as $v ) {
						$option .= '<option value=' . $v [indus_id] . '>' . $v [indus_name] . '</option>';
					}
					CHARSET == 'gbk' and $option = kekezu::gbktoutf ( $option );
					echo $option;
					die ();
					break;
			}
		} else
			return false;
	}
	
	/**
	 * 任务附件入库
	 * @param $task_id 任务编号
	 * @param $title 任务标题
	 */
	function save_task_file($task_id, $title) {
		$release_info = $this->_std_obj->_release_info;
		if ($release_info ['file_ids']) {
			$file_obj = new Keke_witkey_file_class ();
			$file_arr = array_filter ( explode ( ',', $release_info ['file_ids'] ) );
			foreach ( $file_arr as $v ) {
				$file_obj->setFile_id ( $v );
				$file_obj->setUid ( $this->_uid );
				$file_obj->setUsername ( $this->_username );
				$file_obj->setTask_id ( $task_id );
				$file_obj->setTask_title ( $title );
				$file_obj->edit_keke_witkey_file ();
			}
		}
	}
	/**
	 * 发送消息    任务发布成功后提示用户
	 * @param int $task_id 任务编号
	 * @param int $task_status 任务状态 默认发布成功
	 */
	public function notify_user($task_id, $task_status = '2') {
		global $_K;
		global $_lang;
		$task_obj = $this->_task_obj;
		$model_code = $this->_model_info ['model_code'];
		$status_arr = call_user_func ( array ($model_code . "_task_class", 'get_task_status' ) ); //任务状态数组
		$url = '<a href="' . $_K ['siteurl'] . '/index.php?do=task&task_id=' . $task_id . '"  target="_blank">' . $task_obj->getTask_title () . '</a>';
		$v = array ('model_name'=>$this->_model_info['model_name'],'task_id'=>$task_id, Conf::$msgTpl['task_title']=>$task_obj->getTask_title(), Conf::$msgTpl['task_id']=>$task_id, Conf::$msgTpl['task_link']=>$url, Conf::$msgTpl['task_status']=>$status_arr[$task_status], $_lang['start_time']=>date('Y-m-d H:i:s',$task_obj->getStart_time()), Conf::$msgTpl['hand_work_timeout']=>date('Y-m-d H:i:s',$task_obj->getSub_time()), Conf::$msgTpl['choose_timeout']=>date('Y-m-d H:i:s',$task_obj->getEnd_time()) );

		keke_msg_class::notify_user($this->_uid, $this->_username, "task_pub", $_lang ['pub_task_notice'],$v);
	}
	
	/**
	 * 计算任务总金额
	 * 发布费用+增值服务费用
	 * @param float $task_cash 任务金额（无附加等）
	 * @return float $total_cash;
	 */
	public function get_total_cash($task_cash) {
		
		$total_cash = $task_cash + $this->_std_obj->_att_cash; //任务金额+增值费用
		return $total_cash;
	}
	/**
	 * 任务发布状态设置以及任务花费金额与金币的计算
	 * 返回根据后台配置的最小金额得出的当前任务可发布状态
	 * @param $total_cash 任务总金额(含有增值费用)
	 * @param $task_cash  任务金额(不含增值费用)
	 * @param $is_trust 是否担保
	 */
	public function set_task_status($total_cash, $task_cash) {
		global $kekezu;
		$basic_config = $kekezu->_sys_config;
		$balance = $this->_user_info ['balance'];
		$credit = $this->_user_info ['credit'];
		$credit = 0;
		$kekezu->_sys_config['credit_is_allow']==1 and $credit = $this->_user_info ['credit'];
		
		if ($balance + $credit >= $total_cash) { //用户金额满足总花费的情况下
			$model_code = $this->_model_info ['model_code'];
			switch ($model_code) {
				case "tender" :
					$this->_task_config ['zb_audit'] == 2 and $task_status = "2" or $task_status = "1";
					break;
				case "match" :
					$task_status = "2";
					break;
				default :
					if ($task_cash >= $this->_task_config ['audit_cash']) { //发布金额满足最小金额限制，用户余额满足 不需审核
						$task_status = '2'; //任务状态可设置为成功发布
					} elseif ($task_cash < $this->_task_config ['audit_cash']) { //发布金额小于最小金额,需要审核
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
			}
		} else {
			$task_status = "0"; //延迟支付
		}
		$this->_task_obj->setTask_status ( $task_status ); //设置任务状态
		$this->_task_obj->setCash_cost ( $cash_cost ); //现金花费
		$this->_task_obj->setCredit_cost ( $credit_cost ); //金币花费
	}
	/**
	 * 提交验证
	 */
	private function submit_check() {
		global $kekezu, $_lang;
		$std_obj = $this->_std_obj; //成员对象
		$r_info = $std_obj->_release_info; //任务发布信息
		$pass = true;
		if (empty ( $r_info ) || ! is_array ( $r_info )) {
			$pass = false;
		} else {
			$tmp = array ();
			$tmp ['t'] = trim ( $r_info ['txt_title'] );
			$tmp ['i'] = trim ( $r_info ['indus_id'] );
			$tmp ['pi'] = trim ( $r_info ['indus_pid'] );
			//$tmp['d']  = strtotime($r_info['txt_task_day']);
			if($kekezu->get_cash_cove ( $this->_model_info ['model_code'] )){
				$c = intval ( $r_info ['task_cash_cove'] );
			}else{
				$c = floatval ( $r_info ['txt_task_cash'] );
			}
			$tmp ['c'] = $c;
			if (sizeof ( $tmp ) != sizeof ( array_filter ( $tmp ) )) {
				$pass = false;
			}
		}
		$pass === false && kekezu::show_msg ( $_lang ['operate_notice'], $_SERVER ['HTTP_REFERER'], 3, $_lang ['key_information_missed'], 'warning' );
	}
	/**
	 * 任务发布通用块设置
	 * 用来处理各任务通用的设置
	 */
	public function public_pubtask() {
		$this->submit_check ();
		$std_obj = $this->_std_obj; //成员对象
		$release_info = $std_obj->_release_info; //任务发布信息
		$task_obj = $this->_task_obj; //任务对象
		$user_info = $this->_user_info;
		$txt_task_title = kekezu::str_filter ( $release_info ['txt_title'] ); //任务标题
		$task_obj->setTask_title ( $txt_task_title );
		$task_obj->setModel_id ( $this->_model_id ); //设定任务类型
		$task_obj->setProfit_rate ( $this->_task_config ['task_rate'] ); //当前比例
		$task_obj->setTask_fail_rate ( $this->_task_config ['task_fail_rate'] ); //失败返金抽成比
		$task_obj->setTask_cash ( $release_info ['txt_task_cash'],'',true ); //任务金额
		$task_obj->setReal_cash ( $release_info ['txt_task_cash'] * (100 - $this->_task_config ['task_rate']) / 100,'',true ); //实际佣金
		$task_obj->setStart_time ( time () ); //任务开始时间
		$time_arr = getdate ();
		$rel_time = $time_arr ['hours'] * 3600 + $time_arr ['minutes'] * 60 + $time_arr ['seconds'];
		$task_obj->setSub_time ( strtotime ( $release_info ['txt_task_day'] ) + $rel_time ); //任务投稿期
		

		$task_obj->setEnd_time ( strtotime ( $release_info ['txt_task_day'] ) + $this->_task_config ['choose_time'] * 24 * 3600 + $rel_time ); //任务选稿期
		$task_obj->setIndus_id ( $release_info ['indus_id'] ); //任务行业
		$task_obj->setIndus_pid ( $release_info ['indus_pid'] );
		
		$tar_content = kekezu::str_filter ( $release_info ['tar_content'] );
		$task_obj->setTask_desc ( $tar_content ); //任务需求
		$task_obj->setUid ( $this->_uid );
		$task_obj->setUsername ( $this->_username );
		/**增值服务项录入**/
		$att_info = array_filter ( $std_obj->_att_info ); //增值项信息
		

		$keys_arr = array_keys ( $att_info );
		//增值服务借宿时间设置
		$payitem_arr [top] = 1000000000;
		$payitem_arr [urgent] = 1000000000;
		foreach ( $att_info as $k => $v ) {
			$v [item_code] == 'top' and $payitem_arr [top] = time () + 3600 * 24 * $v [item_num];
			$v [item_code] == 'urgent' and $payitem_arr [urgent] = time () + 3600 * 24 * $v [item_num];
		}
		
		$payitem_time = serialize ( $payitem_arr );
		
		$att_ids = implode ( ",", array_keys ( $att_info ) ); //增值项编号串
		$task_obj->setPay_item ( $att_ids );
		$task_obj->setPayitem_time ( $payitem_time );
		$task_obj->setAtt_cash ( floatval ( $std_obj->_att_cash )); //增值项金额
		

		//$contact = serialize ( $release_info ['cont'] ); //联系方式
		$task_obj->setContact ( $release_info['txt_mobile'] );
		$task_obj->setKf_uid ( $this->_kf_uid ); //指定客服
		//任务附件
		$file_arr = array_filter ( explode ( ',', $release_info ['file_ids'] ) );
		$file_s = implode ( ',', $file_arr );
		$task_obj->setTask_file ( $file_s );
	}
	
	/**
	 * 任务信息成功产生的追加操作
	 */
	public function update_task_info($task_id, $obj_name) {
		
		global $_K, $_lang;
		$std_obj = $this->_std_obj;
		$release_info = $std_obj->_release_info; //任务信息
		$att_info = $std_obj->_att_info; //增值信息
		

		$user_info = $this->_user_info; //用户信息
		$task_obj = $this->_task_obj; //任务对象
		if ($task_id) {
			db_factory::execute ( "update " . TABLEPRE . "witkey_space set pub_num = pub_num+1 where uid=$this->_uid " );
		
			//任务附件保存
			$release_info ['file_ids'] and $this->save_task_file ( $task_id, $release_info ['txt_title'] );
			$task_status = $task_obj->getTask_status (); //任务状态
			/**订单产生**/
			$task_title = $task_obj->getTask_title ();
			
			switch ($task_status) {
				case "2" :
					//产生订单+结算
					

					$this->create_task_order ( $task_id, $this->_model_id, $release_info, $att_info );
					
					$this->create_prom_event ( $task_id ); /*任务发布成功。产生推广事件*/
					
					$feed_arr = array ("feed_username" => array ("content" => $this->_username, "url" => "index.php?do=space&member_id={$this->_uid}" ), "action" => array ("content" => $_lang ['pub_task'], "url" => "" ), "event" => array ("content" => " $task_title", "url" => "index.php?do=task&task_id=$task_id" ) );
					kekezu::save_feed ( $feed_arr, $this->_uid, $this->_username, 'pub_task', $task_id );
					//发送消息
					$this->notify_user ( $task_id, '2' );
					$j_step = 'step4';
					$status = '2';
					break;
				case "1" : //进入审核
					//产生订单+结算
					$this->create_task_order ( $task_id, $this->_model_id, $release_info, $att_info );
					$this->create_prom_event ( $task_id ); /*任务发布成功。产生推广事件*/
					/*$feed_arr = array ("feed_username" => array ("content" => $this->_username, "url" => "index.php?do=space&member_id= $this->_uid  " ), "action" => array ("content" => "发布了任务 ", "url" => "" ), "event" => array ("content" => " $task_title", "url" => "index.php?do=task&task_id=$task_id" ) );
					kekezu::save_feed ( $feed_arr, $this->_uid, $this->_username, 'pub_task', $task_id );*/
					//发送消息
					$this->notify_user ( $task_id, '1' );
					$j_step = 'step4';
					$status = '1';
					
					break;
				case "0" : //金额不够
					

					$total_cash = $this->get_total_cash ( $release_info ['txt_task_cash'] );
					$pay_cash = $total_cash - ($user_info ['balance'] + $user_info ['credit']);
					$pay_cash = ceil ( $pay_cash );
					$order_id = $this->create_task_order ( $task_id, $this->_model_id, $release_info, $att_info, 'wait' );
					//发送消息
					$this->notify_user ( $task_id, '0' );
					//担保交易
					$jump_url = "index.php?do=pay&order_id=$order_id";
					$this->del_task_obj ( $obj_name ); //清除缓存
					header ( "location:" . $jump_url );
					die ();
					break;
			}
		}
		
		$this->del_task_obj ( $obj_name ); //清除缓存
		header ( "Location:" . $_K ['siteurl'] . "/index.php?do=release&model_id=$this->_model_id&r_step=" . $j_step . "&task_id=$task_id&status=" . $status );
	}
	
	/**
	 * 产生推广事件
	 * @param $task_id 任务编号
	 */
	public function create_prom_event($task_id) {
		global $kekezu;
		$task_obj = $this->_task_obj;
		if ($this->_model_info ['model_code'] != 'tender') {
			$this->_model_info ['model_code'] == 'dtender' and $task_cash = $task_obj->getReal_cash () or $task_cash = $task_obj->getTask_cash ();
			$kekezu->init_prom ();
			$prom_obj = $kekezu->_prom_obj;
			if ($prom_obj->is_meet_requirement ( "pub_task", $task_id )) {
				$prom_obj->create_prom_event ( "pub_task", $this->_uid, $task_id, $task_cash );
			}
		}
	}
	/**
	 * 获取任务基本配置
	 * @return   void
	 */
	public abstract function get_task_config();
	/**
	 * 任务发布函数
	 */
	public abstract function pub_task();
	
	/**
	 * 发布模式进行信息
	 * @param $std_cache_name session名
	 * @param $data 外部传入参数
	 */
	public abstract function pub_mode_init($std_cache_name, $data = array());
	/**
	 * 任务订单、财务记录产生产生  增值项的obj_id可能会为空。因为任务可能没直接通过
	 * @param int $task_id	任务编号
	 * @param int $model_id 模型ID
	 * @param array $release_info 发布信息
	 * @param array $att_info 增值项信息
	 * @param string $order_status 订单状态
	 */
	public function create_task_order($task_id, $model_id, $release_info, $att_info = array(), $order_status = 'ok') {
		global $uid, $username;
		global $_lang;
		$oder_obj = new Keke_witkey_order_class (); //订单记录表对象
		$order_detail = new Keke_witkey_order_detail_class (); //订单详细对下岗
		$task_cash = $release_info ['txt_task_cash']; //任务金额
		/**订单产生**/
		$order_name = $release_info ['txt_title']; //订单名称
		$order_amount = $this->get_total_cash ( $task_cash ); //订单总金
		$order_body = $_lang ['pub_task'] . "<a href=\"index.php?do=task&task_id=$task_id\">" . $order_name . "</a>"; //订单备注
		

		/**构造订单标题，详细**/
		if (! empty ( $att_info )) {
			foreach ( $att_info as $k => $v ) {
				$order_name .= "#" . $v ['item_name'];
				$order_body .= $_lang ['use_payitem_service'] . $v ['item_name'] . "<br>";
			}
		}
		
		/**订单产生**/
		$order_id = keke_order_class::create_order ( $model_id, $uid, $username, $order_name, $order_amount, $order_body, $order_status );
		
		if ($order_id) {
			keke_order_class::create_order_detail ( $order_id, $release_info ['txt_title'], 'task', intval ( $task_id ), $task_cash );
			/** 财务结算**/
			if ($this->_task_obj->getTask_status () != 0) {
					$this->_model_info ['model_code'] == 'tender' and $site_profit = $task_cash;
					$taskinfo = $this->_task_obj;
					$data = array(':model_name'=>$this->_model_info ['model_name'],':task_id'=>$taskinfo->_task_id,':task_title'=>$taskinfo->_task_title);
					keke_finance_class::init_mem('pub_task', $data);
					$fina_id = keke_finance_class::cash_out ( $this->_uid, $task_cash, 'pub_task', $site_profit, 'task', $task_id ); //财务插入
					/** 更新财务记录的订单号*/
					if($fina_id){
						keke_order_class::update_fina_order ( $fina_id, $order_id );
						if($this->_task_obj->getTask_status ()==2){
							keke_union_class::union_task_submit($this->_user_info,$task_id);//联盟
						}
					}
				/**增值服务记录产生**/
				if (! empty ( $att_info )) {
					$payitem_list = keke_payitem_class::get_payitem_config (); //可购买服务
					
					foreach ( $att_info as $k => $v ) {
						//$remain = keke_payitem_class::payitem_exists ( $this->_uid, $v ['item_code'] );
						//$remain > 0 or 
						keke_payitem_class::payitem_cost ( $v ['item_code'], $v ['item_num'], $payitem_list [$v ['item_code']] ['item_type'], 'buy', $task_id, $task_id ); //买
						

						//用
						$v ['record_id'] = $pay_id = keke_payitem_class::payitem_cost ( $v ['item_code'], $v ['item_num'], $payitem_list [$v ['item_code']] ['item_type'], 'spend', $task_id, $task_id );
						
						$pay_id and db_factory::execute ( sprintf ( " update %switkey_task set point='%s',city='%s' where task_id = '%d'", TABLEPRE, $release_info ['point'], $release_info ['province'], $task_id ) );
					
					}
				}
				if (! empty ( $att_info )) { //循环产生增值项订单详细
					foreach ( $att_info as $v ) {
						keke_order_class::create_order_detail ( $order_id, $v ['item_name'], 'payitem', intval ( $v ['record_id'] ), $v ['item_cash'] );
					}
				}
			}
			return $order_id;
		}
	}
	/**
	 * 附加费用保存
	 * @param int $item_id      增值服务项编号
	 * @param string $item_code 增值项代码
	 * @param string $item_name 增值服务名称
	 * @param float $item_cash  增值服务金额
	 * @param string $obj_name  任务信息session 保存名
	 * @return void
	 */
	public function save_pay_item($item_id, $item_code, $item_name, $item_cash, $obj_name, $item_num) {
		
		$att_info = $this->_std_obj->_att_info; //增值服务数组
		

		if ($att_info [$item_id] ['item_cash'] != $item_cash) { //不存在某项或其值改变时保存
			CHARSET == 'gbk' and $item_name = kekezu::utftogbk ( $item_name );
			$att_info [$item_id] ['item_code'] = $item_code;
			$att_info [$item_id] ['item_name'] = $item_name;
			$att_info [$item_id] ['item_cash'] = $item_cash;
			$att_info [$item_id] ['item_num'] = $item_num;
		}
		$this->_std_obj->_att_info = array_filter ( $att_info ); //重新保存增值项信息
		$this->save_task_obj ( array (), $obj_name ); //重新保存session

		$total_cash = $this->get_total_cash ( $this->_std_obj->_release_info ['txt_task_cash'] );
		kekezu::echojson(keke_curren_class::output( $total_cash, 2 ));
		
		die ();
	}
	/**
	 * 获取任务增值费用信息 
	 *@return $att_info;
	 */
	public function get_pay_item() {
		return $this->_std_obj->_att_info; //增值服务信息
	}
	/**
	 * 移除付费项	 
	 * @param $item_id  待移除项id
	 * @param $obj_name 任务信息session 保存名
	 */
	public function remove_pay_item($item_id, $obj_name) {
		$att_info = $this->_std_obj->_att_info; //增值服务数组
		if ($att_info [$item_id]) {
			unset ( $att_info [$item_id] );
		}
		$this->_std_obj->_att_info = array_filter ( $att_info ); //重新保存增值服务信息
		$this->save_task_obj ( array (), $obj_name ); //重新保存任务session
		$total_cash = $this->get_total_cash ( $this->_std_obj->_release_info ['txt_task_cash'] );
		kekezu::echojson ( keke_curren_class::output( $total_cash, 2 ) );
		die ();
	}
	/**
	 * 计算增值服务费总额
	 * @param array $att_info 增值服务数组信息
	 * @return float 增值服务总金额
	 */
	public function solve_pay_item($att_info = array()) {
		$att_cash = '0';
		if (is_array ( $att_info )) {
			foreach ( $att_info as $v ) {
				$att_cash += floatval ( $v ['item_cash'] );
			}
		}
		
		return $att_cash;
	}
	/**
	 * 保存当前发布信息
	 * @param array $release_info 任务发布信息
	 * @param string $obj_name    外部传入的任务对象session名
	 * @return   void
	 */
	public function save_task_obj($release_info = array(), $obj_name) {
		//var_dump($release_info);die();
		global $kekezu,$_lang;
		empty ( $release_info ) or $this->_std_obj->_release_info = $release_info; //将任务信息保存入成员对象中
		if($release_info['txt_task_cash']<0){
			//任务金额为负数
			kekezu::show_msg($_lang['operate_notice'],'index.php?do=release',3,$_lang['task_cash_negatvie'],'warning');
		}
		$this->_std_obj->_att_cash = $this->solve_pay_item ( $this->_std_obj->_att_info ); //增值服务总额
		$this->_model_info ['model_code'] == 'tender' and $this->_std_obj->_release_info ['txt_task_cash'] = $this->_task_config [zb_fees];
		$_SESSION [$obj_name] = serialize ( $this->_std_obj ); //将对象保存入session
	}
	/**
	 * 获取任务信息
	 * @param string $obj_name    外部传入的任务对象session名
	 * @return   void
	 */
	public function get_task_obj($obj_name) {
		$_SESSION [$obj_name] and $this->_std_obj = unserialize ( $_SESSION [$obj_name] );
	}
	
	/**
	 * 销毁任务对象
	 * @return   void
	 */
	public function del_task_obj($obj_name) {
		if (isset ( $_SESSION [$obj_name] )) {
			unset ( $_SESSION [$obj_name] );
		}
	}
	/**
	 * 页面进入权限判断
	 * @param string $r_step 任务当前步骤
	 * @param int $model_id 任务模型
	 * @param array $relese_info 任务发布信息
	 * @param int $task_id  发布完成的任务编号  ，第四步有用
	 */
	public function check_access($r_step, $model_id, $release_info, $task_id = null, $output = 'normal') {
		global $_lang;
		switch ($r_step) {
			case "step1" :
				break;
			case "step2" : //没有进过第一步
				$release_info ['step1'] or kekezu::keke_show_msg ( "index.php?do=release&pub_mode=$this->_pub_mode&model_id=$model_id", $_lang ['you_not_choose_task_model'], "error", $output );
				break;
			case "step3" : //没有进过第二步
				if (! $release_info ['step2'] && ! $release_info ['step1']) { //没进过前2步
					kekezu::keke_show_msg ( "index.php?do=release&pub_mode=$this->_pub_mode&model_id=$model_id", $_lang ['you_not_choose_task_model_and_not_in'], "error", $output );
				} elseif (! $release_info ['step2']) {
					kekezu::keke_show_msg ( "index.php?do=release&pub_mode=$this->_pub_mode&model_id=$model_id&r_step=step2", $_lang ['you_not_fill_requirement_and_not_in'], "error", $output );
				}
				break;
			case "step4" : //无法查到刚才的任务记录。此页面10分钟类有效
				$sql = sprintf ( " select task_id from %switkey_task where task_id = '%d' and start_time>%d", TABLEPRE, $task_id, time () - 600 );
				$task_info = db_factory::get_one ( $sql );
				$task_info or kekezu::keke_show_msg ( "index.php?do=release&pub_mode=$this->_pub_mode", $_lang ['the_page_timeout_notice'], "error", $output );
				return $task_info;
				break;
		}
	}
	/**
	 * 用户联系方式
	 * @param int $contact_type 选择联系方式
	 */
//	public function user_contact($contact_type) {
//		$user_info = $this->_user_info;
//		$arr = array ();
//		if ($contact_type == '2') { //选择已有方式才会响应
//			$arr ['cont'] ['mobile'] = $user_info ['mobile'];
//			$arr ['cont'] ['qq'] = $user_info ['qq'];
//			$arr ['cont'] ['email'] = $user_info ['email'];
//			$arr ['cont'] ['msn'] = $user_info ['msn'];
//		}
//		return $arr;
//	}
	/**
	 * 转发模式关联数据格式化
	 */
	public function onekey_mode_format($data) {
		return array ("txt_title" => $data ['task_title'], "tar_content" => $data ['task_desc'], "indus_id" => $data ['indus_id'], "indus_pid" => $data ['indus_pid'], "step1" => '1',"txt_mobile"=>$data['contact']);
	}
	/**
	 * 发布权限判断
	 */
	public function check_pub_priv($url = '', $output = "normal") {
		global $_lang;
		$this->_priv ['pass'] and kekezu::keke_show_msg ( $url, $_lang ['can_pub'], '', $output ) or kekezu::keke_show_msg ( $url, $this->_priv ['notice'] . $_lang ['not_rights_pub_task'], "error", $output );
	}
	/**
	 * 根据金额获取最大天数
	 */
	public static function get_default_max_day($task_cash, $model_id, $min_day) {
		$max = kekezu::get_show_day ( floatval ( $task_cash ), $model_id );
		$max >= $min_day or $max += $min_day;
		return date ( 'Y-m-d', time () + $max * 24 * 3600 );
	}
}