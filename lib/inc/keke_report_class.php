<?php
/**
 * 仲裁，举报，投诉的业务处理
 * @copyright keke-tech
 * @author shang
 * @version v 2.0
 * 2010-5-28下午16:59:00
 */
keke_lang_class::load_lang_class ( 'keke_report_class' );
abstract class keke_report_class {
	
	public $_report_info; //交易维权记录信息   单条
	public $_obj_info; //交易维权对象信息
	

	public $_user_info; //提交者信息
	public $_to_user_info; //对方信息
	

	public $_report_id; //举报ID
	public $_report_type; //交易维权类型
	public $_report_status; //交易维权状态
	

	public $_obj; //交易维权对象
	

	public $_credit_info; //扣除信誉、能力信息
	

	public $_process_can; //可以进行的处理动作
	

	public $_msg_obj; //短信发送对象
	

	/**
	 * 初始化处理类
	 * @param int $report_id
	 * @param array $report_info 仲裁记录信息，可选
	 * @param atrray $obj_info	仲裁对象信息
	 */
	public function __construct($report_id, $report_info = null, $obj_info = null, $user_info = null, $to_userinfo = null) {
		
		$report_info or $report_info = $this->get_report_info ( $report_id ); //获取交易维权信息
		$this->_msg_obj = new keke_msg_class ();
		$this->_report_info = $report_info;
		$this->_report_id = $report_id;
		$this->_report_status = $report_info ['report_status'];
		$this->_report_type = $report_info ['report_type'];
		$this->_obj = $report_info ['obj'];
		$user_info and $this->_user_info = $user_info or $this->_user_info = kekezu::get_user_info ( $report_info ['uid'] ); //提交者信息
		$to_userinfo and $this->_to_user_info = $to_userinfo or $this->_to_user_info = kekezu::get_user_info ( $report_info ['to_uid'] ); //对方信息
		$obj_info and $this->_obj_info = $obj_info or $this->_obj_info = $this->obj_info_init ( $report_info, $user_info ); //获取交易维权对象信息
		//var_dump($this->_obj_info);die();
		$this->init ();
	}
	public function init() {
		$this->_process_can = $this->process_can_init ();
		//var_dump($this->_process_can );
		$this->_report_type == '2' and $this->_credit_info = $this->credit_info_init ( $this->_obj, $this->_to_user_info );
	}
	
	/**
	 * 获取一条reprot信息
	 * @param int $report_id
	 * @return array()
	 */
	public static function get_report_info($report_id) {
		$sql = sprintf ( "select * from %switkey_report where report_id='%d'", TABLEPRE, $report_id );
		return db_factory::get_one ( $sql );
	}
	/**
	 * 检测是否申请举报(维权|投诉)
	 * $this->_uid对他人($to_uid)的同一对象($obj,obj_id)、同一类型($type)的维权举报不得重复
	 * @param int $type   交易维权类型 1=>维权,2=>举报,3=>投诉
	 * @param string $obj 交易维权对象 task/work/product/order
	 * @param int $to_uid 被动方
	 * @param int $uid 当前用户
	 * @return boolean or show_msg
	 */
	public static function check_if_report($type, $obj, $obj_id, $uid, $to_uid) {
		global $_lang;
		$if_report = db_factory::get_count ( sprintf ( " select report_id from %switkey_report where report_type='%d' and obj='%s'
		 and obj_id='%d' and uid='%d' and to_uid='%d'", TABLEPRE, $type, $obj, $obj_id, $uid, $to_uid ) );
		$trans_name = keke_report_class::get_transrights_name ( $type ); //交易维权中文表示
		if (! $if_report) {
			return true;
		} else {
			kekezu::keke_show_msg ( '', $_lang ['you_has_to_this_user'] . $trans_name . "," . $_lang ['please_not_repeat_operate'], "error", 'json' );
		}
	}
	/**
	 * 获取交易维权对象的信息
	 * @param array $report_info 	维权信息
	 * @param array $user_info      发起方信息
	 * @return array $obj_info
	 */
	public static function obj_info_init($report_info, $user_info) {
		global $kekezu, $_K;
		global $_lang;
		
		switch ($report_info ['obj']) { //交易维权类型
			case "task" : //任务。
				$obj_info = db_factory::get_one ( sprintf ( " select task_id origin_id,uid origin_uid,model_id,task_title origin_title,task_status as origin_status,real_cash as cash from %switkey_task where task_id='%d'", TABLEPRE, $report_info ['origin_id'] ) );
				$re_obj = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=task&task_id=" . $obj_info ['origin_id'] . "\">" . $obj_info ['origin_title'] . "</a>";
				break;
			case "work" : //稿件 
				$model_id = db_factory::get_count ( sprintf ( " select model_id from %switkey_task where task_id='%d'", TABLEPRE, $report_info ['origin_id'] ) );
				$model_id or kekezu::admin_show_msg ( $_lang ['friendly_notice'], 'index.php?do=trans&view=rights', 2, $_lang ['this_task_has_delete'] );
				$model_info = $kekezu->_model_list [$model_id];
				$sql = " select a.task_id origin_id,a.task_title origin_title,a.uid origin_uid,a.model_id,a.task_status origin_status,a.real_cash cash ";
				
				if ($model_info ['model_code'] == 'dtender' || $model_info ['model_code'] == 'tender') { //招标
					

					$sql .= ",b.bid_id obj_id,b.uid obj_uid,b.bid_status obj_status from %switkey_task a left join %switkey_task_bid b on a.task_id=b.task_id where b.bid_id='%d'";
					//获取中标稿件数量
					$bid_count = db_factory::get_count ( sprintf ( " select count(bid_id) from %switkey_task_bid where task_id='%d' and bid_status='4'", TABLEPRE, $report_info ['origin_id'] ) );
				} else { //悬赏
					

					$sql .= ",b.work_id obj_id,b.uid obj_uid,b.work_status obj_status from %switkey_task a left join %switkey_task_work b on a.task_id=b.task_id where b.work_id='%d'";
					//获取中标稿件数量
					$bid_count = db_factory::get_count ( sprintf ( " select count(work_id) from %switkey_task_work where task_id='%d' and work_status not in(0,5,7,8) ", TABLEPRE, $report_info ['origin_id'] ) );
				}
				
				$obj_info = db_factory::get_one ( sprintf ( $sql, TABLEPRE, TABLEPRE, $report_info ['obj_id'] ) );
				//var_dump($obj_info);die();
				$obj_info ['bid_count'] = $bid_count;
				$re_obj = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=task&task_id=" . $obj_info ['origin_id'] . "&view=list_work&work_id=" . $obj_info ['obj_id'] . "\">" . $obj_info ['origin_title'] . "</a>";
				break;
			case "product" : //商品
				

				$obj_info = db_factory::get_one ( sprintf ( " select service_id origin_id,uid origin_uid,model_id,title origin_title from %switkey_service where service_id='%d'", TABLEPRE, $report_info ['obj_id'] ) );
				$re_obj = "<a href=\"" . $_K ['siteurl'] . "/shop.php?do=service&service_id=" . $obj_info ['origin_id'] . "\">" . $obj_info ['origin_title'] . "</a>";
				break;
			case "order" : //订单
				$sql = " select a.obj_id obj_id,b.order_id origin_id,b.order_uid origin_uid,b.seller_uid obj_uid,
					b.order_status obj_status,b.order_amount cash,b.model_id,b.order_name obj_title from %switkey_order b left join 
					%switkey_order_detail a on a.order_id=b.order_id where b.order_id='%d'";
				$obj_info = db_factory::get_one ( sprintf ( $sql, TABLEPRE, TABLEPRE, $report_info ['origin_id'] ) );
				break;
		}
		if ($report_info ['report_status'] == '1') { //变更为处理中状态
			$res = self::change_status ( $report_info ['report_id'], '2' );
			/*受理消息通知用户*/
			self::accept_notify ( $report_info, $user_info, $re_obj );
		}
		
		return $obj_info;
	}
	/**
	 * 举报（投诉） 受理 提示用户
	 * @param array        $report_info
	 * @param array        $user_info 发起方信息
	 * @param string       $re_obj  对象链接
	 */
	public static function accept_notify($report_info, $user_info, $re_obj) {
		global $_lang;
		$trans_name = self::get_transrights_name ( $report_info ['report_type'] ); //交易维权中文
		$re_type = self::get_transrights_obj ( $report_info ['obj'] ); //交易维权对象中文
		

		$result = array ($_lang ['order_rights_id'] => $report_info ['report_id'], $_lang ['order_rights_name'] => $trans_name, $_lang ['order_rights_type'] => $re_type, $_lang ['order_rights_object'] => $re_obj, $_lang ['action'] => $_lang ['attention_follow'] );
		keke_msg_class::notify_user($user_info ['uid'], $user_info ['username'], 'transrights_accept', $trans_name . $_lang['accepted_notice'],$result);
	}
	/**
	 * 举报(投诉)处理结果 提示用户
	 * @param string $process_type 处理类型 pass nopass
	 * @param array $report_info 举报信息
	 * @param array $user_info 举报人信息
	 * @param array $to_userinfo 被举报人信息
	 * @param string $process_result 处理结果
	 */
	public static function process_notify($process_type = 'pass', $report_info, $user_info, $to_userinfo, $process_result) {
		global $_lang;
		
		if ($report_info ['report_type'] != '1') {
			
			$trans_name = self::get_transrights_name ( $report_info ['report_type'] ); //交易维权中文
			$re_type = self::get_transrights_obj ( $report_info ['obj'] ); //交易维权对象中文
			
			if ($process_type == 'pass') { //通过时
				
				$result = array ($_lang ['order_rights_id'] => $report_info ['report_id'], $_lang ['order_rights_name'] => $trans_name, $_lang ['process_result'] => $process_result );
				keke_msg_class::notify_user($user_info ['uid'], $user_info ['username'], 'transrights_pass', $trans_name . $_lang ['process_notice'],$result);
				keke_msg_class::notify_user($to_userinfo ['uid'], $to_userinfo ['username'], 'transrights_pass', $trans_name . $_lang ['process_notice'],$result);
			}
			if ($process_type == 'nopass') { //不通过时
				$result = array ($_lang ['order_rights_id'] => $report_info ['report_id'], $_lang ['order_rights_name'] => $trans_name, $_lang ['process_result'] => $process_result );
				
				keke_msg_class::notify_user($user_info ['uid'], $user_info ['username'], 'transrights_nopass', $trans_name . $_lang ['process_notice'],$result);
				keke_msg_class::notify_user($to_userinfo ['uid'], $to_userinfo ['username'], 'transrights_nopass', $trans_name . $_lang ['process_notice'],$result);
			}
		}
	}
	
	/**
	 * 处理维权 的冻结 给双方发送消息提示
	 * @param $report_id
	 * @param int $report_type 维权类型
	 * @param int $report_status 维权状态
	 * @param int $to_uid 被维权方
	 * @param string $obj 维权对象
	 * @param int $obj_id 对象编号
	 * @param int $origin_id 对象源编号
	 * @param string $desc  维权理由
	 */
	public static function process_freeze($report_id, $report_type, $report_satus, $to_uid, $obj, $obj_id, $origin_id, $desc) {
		global $_K, $user_info;
		global $_lang;
		if ($report_type == '1') {
			$to_user_info = kekezu::get_user_info ( $to_uid ); //被维权方信息
			if ($report_satus == '1') { //未处理的才可以执行此方法
				switch ($obj) {
					case "task" :
					case "work" :
						$task_info = db_factory::get_one ( sprintf ( " select task_title from %switkey_task where task_id='%d'", TABLEPRE, $origin_id ) );
						$res = db_factory::execute ( sprintf ( " update %switkey_task set task_status='11' where task_id='%d' ", TABLEPRE, $origin_id ) );
						$re_type = $_lang ['task'];
						$re_obj = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=task&task_id=" . $origin_id . "\">" . $task_info ['task_title'] . "</a>";
						break;
					case "order" :
						$order_info = db_factory::get_one ( sprintf ( " select order_name origin_title from %switkey_order where order_id='%d'", TABLEPRE, $origin_id ) );
						$res = db_factory::execute ( sprintf ( " update %switkey_order set order_status='arbitral' where order_id='%d' ", TABLEPRE, $origin_id ) );
						$re_type = $_lang ['order'];
						$re_obj = "<a href=\"" . $_K ['siteurl'] . "/index.php?do=user&view=finance&op=order\">" . $order_info ['origin_title'] . "</a>";
						break;
					case "product" :
						break;
				}
				if ($res) { /*冻结时通知用户*/
					$result = array ($_lang ['launch_people'] => $_lang ['you'], $_lang ['order_rights_object'] => $re_obj, $_lang ['order_rights_type'] => $re_type, $_lang ['submit_reason'] => $desc );
					$result2 = array ($_lang ['order_rights_id'] => $report_id, $_lang ['order_rights_name'] => $_lang ['rights'], $_lang ['order_rights_type'] => $re_type, $_lang ['order_rights_object'] => $re_obj, $_lang ['action'] => $_lang ['freeze'] );
					keke_msg_class::notify_user($user_info ['uid'], $user_info ['username'], 'transrights_freeze', $re_type . $_lang ['freeze_notice'], $result);
					keke_msg_class::notify_user($to_user_info ['uid'], $to_user_info ['username'], 'transrights_accept', $re_type . $_lang ['freeze_notice'], $result2);
				}
			}
		}
	}
	/**
	 * 处理维权 的解冻.给双方发送消息提示
	 * @param string $process_type 动作响应  pass nopass
	 * @param string $process_result    解冻时处理方案
	 */
	public function process_unfreeze($process_type = 'pass', $process_result = null) {
		global $_K;
		global $_lang;
		if ($this->_report_info ['report_status'] == '2') { //处理中的才可以执行此方法
			$report_info = $this->_report_info;
			$report_type = $this->_report_type;
			$front_status = $report_info ['front_status'];
			
			$g_info = $this->user_role ( 'gz' ); //雇主信息
			$w_info = $this->user_role ( 'wk' ); //威客信息
			if ($report_type == '1') { //维权
				switch ($report_info ['obj']) {
					case "task" :
					case "work" : //解冻
						if ($process_type == 'pass') {
							$to_status = '8';
							$res = db_factory::execute ( sprintf ( " update %switkey_task set task_status='%d' where task_id='%d' ", TABLEPRE, $to_status, $report_info ['origin_id'] ) );
						} else { //还原状态
							$res = db_factory::execute ( sprintf ( " update %switkey_task set task_status='%d' where task_id='%d' ", TABLEPRE, $front_status, $report_info ['origin_id'] ) );
						}
						break;
					case "order" : //解冻
						if ($process_type == 'pass') {
							$res = db_factory::execute ( sprintf ( " update %switkey_order set order_status='arbitral_confirm' where order_id='%d' ", TABLEPRE, $report_info ['origin_id'] ) );
						} else { //还原状态
							$res = db_factory::execute ( sprintf ( " update %switkey_order set order_status='%s' where order_id='%d' ", TABLEPRE, $front_status, $report_info ['origin_id'] ) );
						}
						break;
					case "product" :
						break;
				}
			}
			if ($res) { /*解冻时通知用户*/
				if ($process_type == 'pass') { //通过时
					$result = array ($_lang ['order_rights_id'] => $this->_report_id, $_lang ['order_rights_name'] => $_lang ['rights'], $_lang ['process_result'] => $process_result );
					$this->_msg_obj->send_message ( $g_info ['uid'], $g_info ['username'], 'transrights_pass', $_lang ['rights_process_notice'], $result, $g_info ['email'] );
					$this->_msg_obj->send_message ( $w_info ['uid'], $w_info ['username'], 'transrights_pass', $_lang ['rights_process_notice'], $result, $w_info ['email'] );
				}
				if ($process_type == 'nopass') { //不通过时
					$result = array ($_lang ['order_rights_id'] => $this->_report_id, $_lang ['order_rights_name'] => $_lang ['rights'], $_lang ['process_result'] => $process_result );
					$this->_msg_obj->send_message ( $g_info ['uid'], $g_info ['username'], 'transrights_nopass', $_lang ['rights_process_notice'], $result, $g_info ['email'] );
					$this->_msg_obj->send_message ( $w_info ['uid'], $w_info ['username'], 'transrights_nopass', $_lang ['rights_process_notice'], $result, $w_info ['email'] );
				}
			}
		}
	}
	/**
	 * 当前交易维权类型可以进行的操作
	 */
	public function process_can_init() {
		$process_can = array ();
		switch ($this->_report_type) {
			case "1" : //维权
				$process_can ['sharing'] = '1'; //分配
				break;
			case "2" : //举报
				if ($this->_obj == 'work') {
					/**当前稿件中标状态、并且没过公示期**/
					if (! in_array ( $this->_obj_info ['obj_status'], array ('0', '5', '7', '8' ) ) && $this->_obj_info ['origin_status'] <= 3) {
						$process_can ['cancel_bid'] = '1';
						if ($this->_obj_info ['bid_count'] == '1') { //只有一个中标稿件
							$process_can ['reset_task'] = '1';
						}
					}
				}
				$process_can ['deduct'] = '1'; // 扣信誉、能力
				$process_can ['freeze_user'] = '1'; //冻结用户
				if ($this->_obj == 'product') {
					$process_can ['product_remove'] = '1'; //商品下架
				}
				break;
			case "3" : //投诉
				$process_can ['reply'] = '1';
				break;
		}
		return $process_can;
	}
	/**
	 * 判断出被举报者角色，来确定扣除信誉还是能力
	 * @param $obj 举报类型
	 * @param $to_user_info 对方信息数组
	 * @param $user_type 发起者用户角色，可选
	 */
	public static function credit_info_init($obj, $to_user_info) {
		global $_lang;
		$credit_arr = array ();
		switch ($obj) {
			case "work" :
			case "bid" :
			case "product" :
				$credit_arr ['type'] = $_lang ['ability_value'];
				$credit_arr ['name'] = "seller_credit";
				$credit_arr ['max_credit'] = $to_user_info ['seller_credit'];
				break;
			case "task" :
				$credit_arr ['type'] = $_lang ['credit_value'];
				$credit_arr ['name'] = "buyer_credit";
				$credit_arr ['max_credit'] = $to_user_info ['buyer_credit'];
				break;
			case "order" :
				break;
		}
		return $credit_arr;
	}
	/**
	 * 
	 * 创建仲裁或者举报记录
	 * @param string $obj 对象
	 * @param int $obj_id  对象ID
	 * @param int $to_uid   被举报的人
	 * @param string $to_username 被举报人姓名
	 * @param string $desc  原因
	 * @param int $report_type  1仲裁 2举报 3投诉
	 * @param int $front_status 冻结前源对象状态
	 * @param int $origin_id 对象为work，值是work所对应task_id
	 * @param int $user_type 举报者角色类型 1=>威客,2=>雇主
	 * @param string $file_name  举报文件路径
	 * @param int $is_hide=1  匿名举报
	 * 
	 */
	public static function add_report($obj, $obj_id, $to_uid, $to_username, $desc, $report_type, $front_status = null, $origin_id = null, $user_type = null, $file_name = NULL, $is_hide = 1) {
		global $uid, $username;
		global $_lang;
		self::check_if_report ( $report_type, $obj, $obj_id, $uid, $to_uid );
		$transname = self::get_transrights_name ( $report_type );
		if (CHARSET == 'gbk') {
			$desc = kekezu::utftogbk ( $desc );
			$to_username = kekezu::utftogbk ( $to_username );
		}
		$report_obj = new Keke_witkey_report_class ();
		$report_obj->setObj ( $obj );
		$report_obj->setObj_id ( $obj_id );
		$report_obj->setUid ( $uid );
		$report_obj->setUsername ( $username );
		$report_obj->setUser_type ( $user_type );
		$report_obj->setOn_time ( time () );
		$report_obj->setOrigin_id ( $origin_id );
		$report_obj->setTo_uid ( $to_uid );
		$report_obj->setTo_username ( $to_username );
		$report_obj->setReport_desc ( $desc );
		$report_obj->setReport_type ( $report_type );
		$report_obj->setFront_status ( $front_status );
		$report_obj->setReport_file ( $file_name );
		$report_obj->setReport_status ( 1 );
		$report_obj->setIs_hide ( $is_hide );
		$report_id = $report_obj->create_keke_witkey_report ();
		if ($report_type == '1') {
			self::process_freeze ( $report_id, $report_type, '1', $to_uid, $obj, $obj_id, $origin_id, $desc ); //维权冻结
		}
		if ($report_id) {
			kekezu::keke_show_msg ( '', $transname . $_lang ['submit_success_wait_website_process'], "", 'json' );
		} else {
			kekezu::keke_show_msg ( '', $transname . $_lang ['submit_fail'], "error", 'json' );
		}
	}
	/**
	 * 改变举报状态
	 * @param int $report_id
	 * @param int $status
	 * @param array $op_result 处理结果数组
	 * @param string $process_result 最终处理结果
	 */
	public static function change_status($report_id, $status, $op_result = null, $process_result = null) {
		$sql = sprintf ( "update %switkey_report set report_status= '%d',op_uid='%d',op_username='%s',op_result='%s',op_time='%d'  where report_id='%d'", TABLEPRE, $status, $op_result ['op_uid'], $op_result ['op_username'], $process_result, $op_result ['op_time'], $report_id );
		return db_factory::execute ( $sql );
	}
	/**
	 * 重置任务
	 * @param int $task_id 任务编号
	 * @param int $delay_days 延长天数
	 */
	public function reset_task($task_id, $delay_days) {
		$end_time = time () + $delay_days * 3600 * 24;
		$sql = sprintf ( "update %switkey_task set task_status = 2,end_time = %d where task_id = %d ", TABLEPRE, $end_time, $task_id );
		return db_factory::execute ( $sql );
	}
	/**
	 * 撤销中标
	 * @param unknown_type $obj_id 稿件编号
	 */
	public function cancel_bid($obj_id) {
		global $kekezu;
		$model_info = $kekezu->_model_list [$this->_obj_info ['model_id']];
		if ($model_info ['model_code'] == 'dtender' || $model_info ['model_code'] == 'tender') { //招标取消中标
			db_factory::execute ( sprintf ( "update %switkey_task_bid set bid_status = 8 where bid_id = '%d'", TABLEPRE, $obj_id ) );
		} else{
			db_factory::execute ( sprintf ( "update %switkey_task_work set work_status = 8 where work_id = '%d'", TABLEPRE, $obj_id ) );
		}
		/** 中标次数-1**/
		db_factory::execute ( sprintf ( " update %switkey_space set accepted_num = accepted_num-1 where uid = '%d'", TABLEPRE, $this->_obj_info ['obj_uid'] ) );
		/** 终止威客的此次推广事件*/
		$kekezu->init_prom ();
		$p_event = $kekezu->_prom_obj->get_prom_event ( $this->_task_id, $this->_obj_info ['obj_uid'], "bid_task" );
		$kekezu->_prom_obj->set_prom_event_status ( $p_event ['parent_uid'], $this->_gusername, $p_event ['event_id'], '3' );
	}
	/**
	 * 删除举报信息
	 * @param $report_id 数组是批量删除，数字是单条删除
	 */
	public static function del_report($report_id) {
		$report_obj = new Keke_witkey_report_class ();
		if (is_array ( $report_id )) {
			$ids = implode ( ',', $report_id );
			$where = "report_id in ($ids)";
		} else {
			$where = "reprot_id = $report_id";
		}
		$report_obj->setWhere ( $where );
		return $report_obj->del_keke_witkey_report ();
	}
	/**
	 * 处理维权/仲裁
	 * @param string $type   交易维权中文 rights，report，complaint  是后台的路由参数
	 * @param array  $op_result 处理提交结果
	 */
	abstract function process_rights($op_result, $type);
	/**
	 * 处理举报
	 * @param string $type   交易维权中文 rights，report，complaint  是后台的路由参数
	 * @param array  $op_result 处理提交结果
	 */
	
	abstract function process_report($op_result, $type);
	/**
	 * 处理投诉
	 ** 给投诉人一个说法.
	 * @param array $report_info
	 * @param array $user_info
	 * @param array $to_userinfo
	 * @param array $op_result
	 */
	static function sub_process_ts($report_info, $user_info, $to_userinfo, $op_result) {
		$op_result = self::op_result_format ( $op_result );
		switch ($op_result ['action']) {
			case "pass" :
				$res = self::change_status ( $report_info ['report_id'], '4', $op_result, $op_result ['process_result'] );
				self::process_notify ( 'pass', $report_info, $user_info, $to_userinfo, $op_result ['process_result'] );
				break;
			case "nopass" :
				$res = self::change_status ( $report_info ['report_id'], '3', $op_result, $op_result ['process_result'] );
				self::process_notify ( 'nopass', $report_info, $user_info, $to_userinfo, $op_result ['process_result'] );
				break;
		}
		return $res;
	}
	/**
	 * 提交处理结果格式化
	 * @param array $op_result
	 */
	public static function op_result_format($op_result) {
		global $admin_info;
		$op_result ['op_uid'] = $admin_info [uid];
		$op_result ['op_username'] = $admin_info [username];
		$op_result ['op_time'] = time ();
		return $op_result;
	}
	/**
	 * 维权、举报 发起双方身份信息返回
	 * @param string $return_type 需要返回信息的角色类型 gz wk third
	 * @return array('gz'=>,'wk'=>'','third'=>);
	 */
	public function user_role($return_type) {
		$userinfo_arr = array ();
		$obj_info = $this->_obj_info;
		$report_info = $this->_report_info;
		if ($this->_report_info ['user_type'] == '1') {
			if ($obj_info ['origin_uid'] != $report_info ['to_uid']) { //投诉双方都是威客
				$userinfo_arr ['wk'] = $this->_user_info;
				$userinfo_arr ['gz'] = kekezu::get_user_info ( $obj_info ['origin_uid'] );
				$userinfo_arr ['third'] = $this->_to_user_info;
			} else { //被投诉方是雇主
				$userinfo_arr ['wk'] = $this->_user_info;
				$userinfo_arr ['gz'] = $this->_to_user_info;
			}
		} else { //投诉方是雇主、被投诉方是威客
			$userinfo_arr ['gz'] = $this->_user_info;
			$userinfo_arr ['wk'] = $this->_to_user_info;
		
		}
		if ($return_type == 'gz') {
			return $userinfo_arr ['gz'];
		} elseif ($return_type == 'wk') {
			return $userinfo_arr ['wk'];
		} else {
			return $userinfo_arr ['third'];
		}
	}
	/**
	 * 获取交易维权中文
	 * @param int $report_type
	 */
	public static function get_transrights_name($report_type = null) {
		global $_lang;
		$type_arr = array ("1" => $_lang ['rights'], "2" => $_lang ['report'], "3" => '投诉建议' );
		if ($report_type) {
			return $type_arr [$report_type];
		} else {
			return $type_arr;
		}
	}
	/**
	 * 获取交易维权类型
	 */
	public static function get_transrights_type() {
		global $_lang;
		return array ("rights" => array ("1", $_lang ['rights'] ), "report" => array ("2", $_lang ['report'] ), "complaint" => array ("3", $_lang ['complaints'] ) );
	}
	/**
	 * 获取交易维权对象
	 */
	public static function get_transrights_obj($obj = null) {
		global $_lang;
		$trans_obj = array ("task" => $_lang ['task'], "work" => $_lang ['work'], "product" => $_lang ['goods'], "order" => $_lang ['order'],"kppw21"=>'建议' );
		if ($obj) {
			return $trans_obj [$obj];
		} else {
			return $trans_obj;
		}
	}
	/**
	 * 获取维权处理状态
	 */
	public static function get_transrights_status() {
		global $_lang;
		return array ("1" => $_lang ['wait_process'], "2" => $_lang ['processing'], "3" => $_lang ['no_set_up'], "4" => $_lang ['has_process'] );
	}
	/**
	 * 扣除信誉，与能力值
	 * @param int $value  值
	 * @param int $type  1信誉,2能力
	 * @return boolen 
	 */
	function less_credit($value, $type) {
		$user_info = $this->_to_user_info;
		$uid = $user_info ['uid'];
		if ($type == 1) {
			$sql = sprintf ( "update %switkey_space set buyer_credit = buyer_credit-%d where uid=%d", TABLEPRE, $value, $uid );
			$b_level = keke_user_mark_class::get_mark_level($user_info['buyer_credit'],2);
			$up_sql  = " UPDATE ".TABLEPRE."witkey_space set buyer_level='".serialize($b_level)."' where uid=".$uid;
		} elseif ($type == 2) {
			$sql = sprintf ( "update %switkey_space set seller_credit = seller_credit-%d where uid=%d", TABLEPRE, $value, $uid );
			$s_level = keke_user_mark_class::get_mark_level($user_info['seller_credit'],1);
			$up_sql = " UPDATE ".TABLEPRE."witkey_space set seller_level='".serialize($s_level)."' where uid=".$uid;
		}
			db_factory::execute($up_sql);
	 	return db_factory::execute ( $sql );
	}
	/**
	 * 加黑名单，账号冻结多少天
	 * @param int $days   天数
	 */
	
	function to_black($days) {
		$uid = $this->_to_user_info ['uid'];
		if ($days) {
			$sql = sprintf ( "update %switkey_space set status=2 where uid=%d", TABLEPRE, $uid );
			db_factory::execute ( $sql );
		} else {
			return false;
		}
		$expire = $days * 3600 * 24;
		//判断是否已经在黑名单
		$sql2 = sprintf ( "select count(*) as c  from %switkey_member_black where uid = %d ", TABLEPRE, $uid );
		if (db_factory::get_count ( $sql2 )) {
			//在黑名单就更新到期时间
			$sql3 = sprintf ( "update %switkey_member_black set expire = if(expire>unix_timestamp(),expire+%d, unix_timestamp()+%d) where uid = %d ", TABLEPRE, $expire, $expire, $uid );
		} else {
			//不在就添加记录
			$sql3 = sprintf ( "insert into %switkey_member_black (uid,expire,on_time) values (%d,%d,%d)", TABLEPRE, $uid, $expire, time () );
		}
		db_factory::execute ( $sql3 );
		return true;
	
	}

}
?>