<?php
/**
 * 普通招标业务类
 * @method init 任务信息初始化
 * =>任务状态数组信息
 * =>任务基本需求
 * check_if_bided        检测是否中标			 
 * 
 * get_task_stage_desc	        获取任务阶段描述
 * get_task_timedesc 	        获取任务时间描述
 * get_task_work		        获取任务指定状态的稿件信息
 * get_work_info      	        获取任务稿件信息
 *
 * start_vote                   发起投票
 * set_task_vote      			 任务投票进行
 * set_work_status   			 稿件状态变更
 * set_task_sp_end_time			更改任务公示时间
 *
 * dispose_witkey_prom   		 威客推广结算
 * dispose_employer_prom  		 雇主推广结算
 * dispose_task		   		 任务金额结算
 * dispose_task_return    		 任务金额返还
 *
 * auto_choose    	    	          自动选稿
 *
 *时间类
 * time_task_gs   	       	     任务公示
 * time_task_vote     		     任务投票
 * time_task_end      		     任务结束
 *
 * process_can 	    	                当前操作判断
 * work_hand  		      	      任务交稿
 * work_choose 	      	                任务选稿
 */
keke_lang_class::load_lang_class ( 'tender_task_class' );
class tender_task_class extends keke_task_class {
	
	public $_task_status_arr; 
	// 任务状态数组
	public $_work_status_arr;
	 // 稿件状态数组
	

	public $_delay_rule;
	 // 延期规则
	

	public $_cove_obj;
	public $_cash_cove_obj;
	public $_task_bid_obj;
	public $_cash_arr;
	protected $_inited = false;
	
	public static function get_instance($task_info) {
		static $obj = null;
		if ($obj == null) {
			$obj = new tender_task_class ( $task_info );
		}
		return $obj;
	}
	public function __construct($task_info) {
		parent::__construct ( $task_info );
		$this->init ();
	}
	
	public function init() {
		if (! $this->_inited) {
			$this->status_init ();
			$this->delay_rule_init ();
			$this->wiki_priv_init ();
		}
		$this->_inited = true;
		
		$this->_task_bid_obj = new Keke_witkey_task_bid_class ();
		$this->_cash_cove_obj = new Keke_witkey_task_cash_cove_class ();
		$this->_cash_arr = $this->_cash_cove_obj->query_keke_witkey_task_cash_cove ();
	
	}
	/**
	 * 任务(稿件)状态数组信息
	 */
	public function status_init() {
		$this->_task_status_arr = $this->get_task_status ();
		$this->_work_status_arr = $this->get_work_status ();
	}
	/**
	 * 任务延期规则
	 */
	public function delay_rule_init() {
		$this->_delay_rule = keke_task_config::get_delay_rule ( $this->_model_id, '3600' );
	}
	/**
	 * 威客权限动作判断
	 */
	public function wiki_priv_init() {
		$arr = tender_priv_class::get_priv ( $this->_task_id, $this->_model_id, $this->_userinfo );
		$this->_priv = $this->user_priv_format ( $arr );
	}
	
	/**
	 * 获取任务区间
	 */
	public function get_task_coverage() {
		$covers = kekezu::get_cash_cove ();
		/*
		 * $this->_cash_cove_obj->setWhere("cash_rule_id =
		 * ".$this->_task_info['task_cash_coverage']); $cover_info =
		 * $this->_cash_cove_obj->query_keke_witkey_task_cash_cove();
		 */
		$cover_info = $covers [$this->_task_info ['task_cash_coverage']];
		
		return $cover_info ['cove_desc'];
	
	}
	
	public function work_hand($work_desc, $hdn_att_file, $hidework = '2', $url = '', $output = 'normal') {
	}
	
	/**
	 * 任务交稿
	 * 
	 * @param $work_desc string
	 * 交稿描述
	 * @param $hidework int
	 * 稿件隐藏 1=>隐藏,2=>不隐藏 默认为不隐藏
	 * @param $work_file string
	 * 稿件附件编号串 eg:1,2,3,4,5
	 * @param $mobile string
	 * 用户手机 通过手机认证的不支持修改
	 * @param $qq string
	 * 用户QQ
	 * @param $url string
	 * 消息提示链接 具体参见 kekezu::keke_show_msg
	 * @param $output string
	 * 消息输出方式 具体参见 kekezu::keke_show_msg
	 * @see keke_task_class::work_hand()
	 */
	public function tender_work_hand($work_info, $url = '', $output = 'normal') {
		global $kekezu, $_K;
		global $_lang;
		if ($this->check_if_can_hand ( $url, $output )) {
			// 判断是否已交稿
			$this->_task_bid_obj->setWhere ( "task_id = $this->_task_id and uid = $this->_uid and bid_status=0" );
			$is_hand = $this->_task_bid_obj->count_keke_witkey_task_bid ();
			$is_hand and kekezu::keke_show_msg ( '', $_lang['you_haved_tender'], 'error', $output );
			$this->_task_bid_obj->setUid ( $this->_uid );
			$this->_task_bid_obj->setUsername ( $this->_username );
			$this->_task_bid_obj->setArea ( $work_info ['area'] );
			$this->_task_bid_obj->setCycle ( $work_info ['task_over_time'] );
			$this->_task_bid_obj->setQuote ( $work_info ['txt_cash'] );
			$this->_task_bid_obj->setTask_id ( $this->_task_id );
			$this->_task_bid_obj->setBid_time ( time () );
			$this->_task_bid_obj->setHidden_status ( $work_info ['workhide'] );
			$this->_task_bid_obj->setMessage ( $work_info ['tar_content'] );
			$res = $this->_task_bid_obj->create_keke_witkey_task_bid ();
			$work_info ['workhide'] == 1 and keke_payitem_class::payitem_cost ( "workhide", '1', 'work', 'spend', $res, $this->_task_id );
			// 通知雇主有人交稿
			$this->plus_work_num (); 
			// 更新任务稿件数量
			$this->plus_take_num ();
			 // 更新用户交稿数量
			$url = '<a href ="' . $_K ['siteurl'] . '/index.php?do=task&task_id=' . $this->_task_id . '">' . $this->_task_title . '</a>';
			$v_arr = array ($_lang['username'] => "$this->_gusername", $_lang['user'] => $this->_username, $_lang['call'] => $_lang['you'], $_lang['task_title'] => $url, $_lang['website_name'] => $kekezu->_sys_config ['website_name'] );
			keke_shop_class::notify_user ( $this->_guid, $this->_gusername, 'task_hand', $_lang['hand_work_notice'], $v_arr );
			kekezu::keke_show_msg ( $url, $_lang['tender_success'], 'right', $output );
		
		}
	}
	
	/**
	 * 中标
	 * 
	 * @param $url string
	 * 消息提示链接 具体参见 kekezu::keke_show_msg
	 * @param $output string
	 * 消息输出方式 具体参见 kekezu::keke_show_msg
	 * @see keke_task_class::work_choose()
	 */
	public function work_choose($work_id, $to_status, $url = '', $output = 'json', $trust_response = false) {
		global $_K;
		global $_lang;
		$bid_info = $this->select_bid_check ( $work_id, $url );
		$status_arr = $this->get_work_status ();
		// 改变稿件状态值
		if ($this->set_work_status ( $work_id, $to_status )) {
			if ($to_status == 4) {
				 // 中标提示用户
				$this->set_task_status ( 4 );
				 // *更改任务状态为工作中**/
				$this->plus_accepted_num ( $bid_info ['uid'] );
				
				// 写入feed
				$feed_arr = array ("feed_username" => array ("content" => $bid_info ['username'], "url" => "index.php?do=space&member_id=$bid_info[uid]" ), "action" => array ("content" => $_lang['success_bid_haved'], "url" => "" ), "event" => array ("content" => "$this->_task_title", "url" => "index.php?do=task&task_id={$this->_task_id}" ) );
				kekezu::save_feed ( $feed_arr, $bid_info ['uid'], $bid_info ['username'], 'work_accept', $this->_task_info ['task_id'] );
			}
			//设置稿件为淘汰
			$to_status == 7 and $action = "task_unbid" or $action="task_bid";
			// 通知威客
			$url = '<a href ="index.php?do=task&task_id=' . $this->_task_id . '" target="_blank" >' . $this->_task_title . '</a>';
			$v = array ($_lang['work_status'] => $status_arr [$to_status],
					 $_lang['task_id'] => $this->_task_id, $_lang['task_title'] => $url,
                     $_lang['bid_cash']=>$bid_info['quote']
					);
			
			$this->notify_user ( $action, $_lang['work'] . $status_arr [$to_status], $v, '1', $bid_info ['uid'] ); 
			// 通知威客
			
			kekezu::keke_show_msg ( $url, $_lang['choose_tender_success'], '', $output );
		} else {
			kekezu::keke_show_msg ( $url, $_lang['choose_tender_fail'], 'error', $output );
		}
	}
	
	/**
	 * 选标前检测
	 */
	public function select_bid_check($work_id, $url) {
		global $_lang;
		// 判断是否为雇主
		$this->_uid != $this->_guid and kekezu::keke_show_msg ( $url, $_lang['sorry_you_not_rights_operate'] );
		// 判断是否已中标或淘汰
		$this->_task_bid_obj->setWhere ( " bid_id = " . $work_id );
		$bid_info = $this->_task_bid_obj->query_keke_witkey_task_bid ();
		$bid_info = $bid_info ['0'];
		$bid_info ['bid_status'] and kekezu::keke_show_msg ( $url, $_lang['please_not_repeat_bid'] );
		// 判断是否为选标时间
		$this->_task_info ['task_status'] != 3 && $this->_task_config ['open_select'] != 'open' and kekezu::keke_show_msg ( $url, $_lang['present_status_not_choose_work'] );
		
		return $bid_info;
	
	}
	
	/**
	 * 获取任务稿件信息 支持分页，用户前端稿件列表
	 * 
	 * @param $w array
	 * 前端查询条件数组
	 * ['work_status'=>稿件状态
	 * 'user_type'=>用户类型 --有值表示自己
	 * ......]
	 * @param $p array
	 * 前端传递的分页初始信息数组
	 * ['page'=>当前页面
	 * 'page_size'=>页面条数
	 * 'url'=>分页链接
	 * 'anchor'=>分页锚点]
	 * @return array work_list
	 */
	public function get_work_info($w = array(), $order = null, $p = array()) {
		global $kekezu, $_K,$uid;
		$work_arr = array ();
		
		$sql = " select a.* from " . TABLEPRE . "witkey_task_work a left join " . TABLEPRE . "witkey_space b on a.uid=b.uid";
		
		$count_sql = " select count(a.work_id) from " . TABLEPRE . "witkey_task_work a left join " . TABLEPRE . "witkey_space b on a.uid=b.uid";
		$where = " where a.task_id = '$this->_task_id' ";
		
		if (! empty ( $w )) {
			$w ['user_type'] == 'my' and $where .= " and a.uid = '$this->_uid'";
			isset ( $w ['work_status'] ) and $where .= " and a.work_status = '" . intval ( $w ['work_status'] ) . "'";
		/**
		 * 待添加*
		 */
		}
		$where .= " order by work_time desc ";
		if (! empty ( $p )) {
			$page_obj = $kekezu->_page_obj;
			$count = intval ( db_factory::get_count ( $count_sql . $where ) );
			$pages = $page_obj->getPages ( $count, $p ['page_size'], $p ['page'], $p ['url'], $p ['anchor'] );
			$where .= $pages ['where'];
		}
		$work_info = db_factory::query ( $sql . $where );
		$work_arr ['work_info'] = $work_info;
		$work_arr ['pages'] = $pages;
		$work_ids = implode ( ',',array_keys ( $work_info ));
		/*更新查看状态*/
		$work_ids&&$uid==$this->_task_info['uid'] and db_factory::execute('update '.TABLEPRE.'witkey_task_bid set is_view=1 where bid_id in ('.$work_ids.') and is_view=0');
		
		return $work_arr;
	}
	
	/**
	 * 设置稿件状态
	 */
	
	function set_work_status($work_id, $status) {
		
		$this->_task_bid_obj->setWhere ( "bid_id = $work_id and task_id = $this->_task_id" );
		$this->_task_bid_obj->setBid_status ( $status );
		$res = $this->_task_bid_obj->edit_keke_witkey_task_bid ();
		
		return $res;
	
	}
	
	/**
	 * 操作判断
	 * //注意用户权限的判断
	 * 雇主不受威客权限的限制、、拥有威客的所有权限
	 * 威客严格受到条件约束
	 * 威客限制：查看任务
	 * 留言
	 * 举报
	 * 
	 * @see keke_task_class::process_can()
	 */
	public function process_can() {
		$wiki_priv = $this->_priv; 
		// 威客权限数组
		$process_arr = array ();
		$status = intval ( $this->_task_status );
		$task_info = $this->_task_info;
		$config = $this->_task_config;
		$g_uid = $this->_guid;
		$uid = $this->_uid;
		$user_info = $this->_userinfo;
		
		switch ($status) {
			case "2" : 
				// 投标中
				switch ($g_uid == $uid) {
					 // 雇主
					case "1" :
						$process_arr['tools'] = true;
						//工具
						$process_arr ['reqedit'] = true; 
						// 补充需求
						if ($config ['open_select'] == 'open') {
							$process_arr ['work_choose'] = true; 
							// 开启投稿中选稿
						}
						$process_arr ['work_comment'] = true; 
						// 稿件回复
						break;
					case "0" : 
						// 威客
						$process_arr ['work_hand'] = true; 
						// 提交稿件
						$process_arr ['task_comment'] = true;
						 // 任务回复
						$process_arr ['task_report'] = true; 
						// 任务举报
						break;
				}
				
				break;
			case "3" : // 选标中
				switch ($g_uid == $uid) { 
					// 雇主
					case "1" :
						// 选稿
						$process_arr ['work_choose'] = true;
						// 稿件回复 
						$process_arr ['work_comment'] = true; 
						break;
					case "0" : 
						// 威客
						// 任务回复
						$process_arr ['task_comment'] = true; 
						// 任务举报
						$process_arr ['task_report'] = true; 
						break;
				}
				
				break;
			case "4" :
				 // 工作中
				$bid_info = $this->get_bid_info ();
				switch ($g_uid == $uid) {
					 // 雇主
					case "1" :
						// 留言回复
						$process_arr ['work_comment'] = true; 
						//$bid_info ['ext_status'] == 1 and $process_arr ['work_over'] = true;
						break;
					case "0" :
						// 任务回复
						$process_arr ['task_comment'] = true; 
						$this->_uid == $bid_info ['uid'] && $bid_info ['ext_status'] != 1 and $process_arr ['pub_agreement'] = true;
						 // 任务举报
						$process_arr ['task_report'] = true;
						break;
				}
				 // 稿件举报
				$process_arr ['work_report'] = true;
				break;
			
			case "5" : 
				// 威客交付中
				$bid_info = $this->get_bid_info ();
				
				switch ($g_uid == $uid) {
					 // 雇主
					case "1" :
						
						$bid_info ['ext_status'] == 1 and $process_arr ['work_over'] = true;
						break;
					case "0" :
						$this->_uid == $bid_info ['uid'] && $bid_info ['ext_status'] != 1 and $process_arr ['pub_agreement'] = true;
						break;
				}
				break;
			
			case "8" : 
				// 已结束
				switch ($g_uid == $uid) { 
					// 雇主
					case "1" :
						// 留言回复
						$process_arr ['work_comment'] = true; 
						// 稿件评价
						$process_arr ['work_mark'] = true; 
						

						break;
					case "0" :
						// 任务回复
						$process_arr ['task_comment'] = true; 
						 // 任务评价
						$process_arr ['task_mark'] = true;
						

						break;
				}
				break;
		}
		 // 任务投诉
		$uid != $g_uid and $process_arr ['task_complaint'] = true;
		$process_arr ['work_complaint'] = true; 
		// 稿件投诉
		if($user_info['group_id']){
			//管理员
			switch ($status){
				case 1:
					//审核
					$process_arr['task_audit'] = true;
					break;
				case 2:
					//推荐
					$process_arr['task_recommend']=true;
					$process_arr['task_freeze'] = true;
					break;
				default:
					if($status>1&&$status<8){
						$process_arr['task_freeze'] = true;
					}
			}
			
		}
		$this->_process_can = $process_arr;
		return $process_arr;
	}
	/**
	 *
	 * @return 返回普通招标任务状态
	 */
	public static function get_task_status() {
		global $_lang;
		return array ("0" => $_lang['task_no_pay'], "1" => $_lang['task_wait_audit'], "2" => $_lang['tendering'], "3" => $_lang['choose_tendering'], "4" => $_lang['working'], "7" => $_lang['freeze'], "8" => $_lang['task_over'], "9" => $_lang['fail'], "10" => $_lang['task_audit_fail']);
	
	}
	
	/**
	 *
	 * @return 返回普通招标稿件状态
	 *
	 */
	public static function get_work_status() {
		global $_lang;
		return array ('4' => $_lang['task_bid'], '7' => $_lang['task_out'], '8' => $_lang['task_can_not_choose_bid'] );
	
	}
	
	/**
	 * 任务阶段时间描述
	 */
	public function get_task_timedesc() {
		global $_lang;
		$status_arr = $this->_task_status_arr;
		$task_status = $this->_task_status;
		$task_info = $this->_task_info;
		$time_desc = array ();
		switch ($task_status) {
			case "0" :
				$time_desc ['ext_desc'] = $_lang['task_nopay_can_not_look'];
				break;
			case "1" :
				$time_desc ['ext_desc'] = $_lang['wait_patient_to_audit'];
				break;
			case "2" : 
				// 投标中
				// 时间状态描述
				$time_desc ['time_desc'] = $_lang['from_hand_bid_deadline']; 
				// 当前状态结束时间
				$time_desc ['time'] = $task_info ['sub_time']; 
				//$time_desc ['ext_desc'] = $_lang['task_doing_can_tender'];
				$time_desc ['ext_desc'] = $_lang['bidding_and_eagerly_tender'];
				if ($this->_task_config ['open_select'] == 'open') { 
					// 开启进行选稿
					$time_desc ['g_action'] = $_lang['present_state_employer_can_choose']; 
				}
				break;
			case "3" : 
				// 选标中
				// 时间状态描述
				$time_desc ['time_desc'] = $_lang['from_choose_deadline']; 
				// 当前状态结束时间
				$time_desc ['time'] = $task_info ['end_time']; 
				//$time_desc ['ext_desc'] = $_lang['task_choosing_tender']; 
				
				$time_desc ['ext_desc'] = $_lang['bidding_and_wait_employer_choose'];
				break;
			case "4" : // 工作中
				//$time_desc ['ext_desc'] = $_lang['employer_haved_choose_and_witkey_working']; 
				
				$time_desc ['ext_desc'] = $_lang['bidder_working'];
				break;
			case "5" : // 交付中
				//$time_desc ['ext_desc'] = $_lang['task_in_jf_rate'];
				
				$time_desc ['ext_desc'] = $_lang['task_over_for_jf'];
				break;
			case "7" : // 冻结中
				//$time_desc ['ext_desc'] = $_lang['task_diffrent_opnion_and_web_in'];
				
				$time_desc ['ext_desc'] = $_lang['task_frozen_can_not_operate'];
				break;
			case "8" : // 结束
				//$time_desc ['ext_desc'] = $_lang['task_haved_complete']; 
				
				$time_desc ['ext_desc'] = $_lang['task_over_congra_witkey'];
				break;
			case "9" : // 失败
				//$time_desc ['ext_desc'] = $_lang['task_timeout_and_no_works_fail']; 
				
				$time_desc ['ext_desc'] = $_lang['pity_task_fail'];
				break;
			case "10"://未通过审核
				$time_desc ['ext_desc'] =  $_lang['fail_audit_please_repub']; 
				
				break;
			case "11" : // 仲裁
				//$time_desc ['ext_desc'] = $_lang['task_arbitrating'];
				
				$time_desc ['ext_desc'] = $_lang['wait_for_task_arbitrate'];
				break;
		}
		
		return $time_desc;
	}
	
	// 获取中标稿件
	function get_bid_info() {
		$this->_task_bid_obj->setWhere ( " task_id = $this->_task_id and bid_status = 4" );
		$bid_info = $this->_task_bid_obj->query_keke_witkey_task_bid ();
		$bid_info = $bid_info ['0'];
		if ($bid_info) {
			return $bid_info;
		} else {
			return false;
		}
	}
	
	// 改变稿件状态
	function set_bid_status($bid_id, $bid_status) {
		$this->_task_bid_obj->setWhere ( " bid_id = $bid_id" );
		$this->_task_bid_obj->setBid_status ( $bid_status );
		$res = $this->_task_bid_obj->edit_keke_witkey_task_bid ();
		if ($res) {
			return $res;
		} else {
			return false;
		}
	
	}
	
	// 改变协议状态
	function set_agreement_status($bid_id, $status) {
		$this->_task_bid_obj->setWhere ( " bid_id = $bid_id" );
		$this->_task_bid_obj->setExt_status ( $status );
		$res = $this->_task_bid_obj->edit_keke_witkey_task_bid ();
		if ($res) {
			return $res;
		} else {
			return false;
		}
	}
	
	public function dispose_order($order_id) {
		global $kekezu, $_K;
		global $_lang;
		// 后台配置
		$task_config = $this->_task_config;
		$task_info = $this->_task_info; 
		// 任务信息
		$url = $_K ['siteurl'] . '/index.php?do=task&task_id=' . $this->_task_id;
		$task_status = $this->_task_status;
		$order_info = db_factory::get_one ( sprintf ( "select order_amount,order_status from %switkey_order where order_id='%d'", TABLEPRE, intval ( $order_id ) ) );
		$order_amount = $order_info ['order_amount'];
		if ($order_info ['order_status'] == 'ok') {
			$task_status == 1 && $notice = $_lang['task_pay_success_and_wait_admin_audit'];
			$task_status == 2 && $notice = $_lang['task_pay_success_and_task_pub_success'];
			return pay_return_fac_class::struct_response ( $_lang['operate_notice'], $notice, $url, 'success' );
		} else {
			$data = array(':model_name'=>$this->_model_name,':task_id'=>$this->_task_id,':task_title'=>$this->_task_title);
			keke_finance_class::init_mem('pub_task', $data);
			$res = keke_finance_class::cash_out ( $task_info ['uid'], $order_amount, 'pub_task',$task_info['task_cash']);
			 // 支付费用
			switch ($res == true) {
				case "1" :
					 // 支付成功
					/**
					 * 雇主推广事件产生
					 */
					$kekezu->init_prom ();
					if ($kekezu->_prom_obj->is_meet_requirement ( "pub_task", $this->_task_id )) {
						$kekezu->_prom_obj->create_prom_event ( "pub_task", $this->_guid, $task_info ['task_id'], $task_info ['task_cash'] );
					}
					
					keke_union_class::union_task_submit($this->_g_userinfo,$this->_task_id);//联盟
						$feed_arr = array ("feed_username" => array ("content" =>$task_info['username'], "url" => "index.php?do=space&member_id={$task_info['uid']}" ), "action" => array ("content" => $_lang['pub_task'], "url" => "" ), "event" => array ("content" => "{$task_info['task_title']}", "url" => "index.php?do=task&task_id={$task_info['task_id']}" ) );
						kekezu::save_feed ( $feed_arr,$task_info['uid'],$task_info['username'], 'pub_task',$task_info['task_id']);
					
					/**更新任务的现金金币消耗*/
					$consume = kekezu::get_cash_consume($task_info['task_cash']);
					db_factory::execute(sprintf(" update %switkey_task set cash_cost='%s',credit_cost='%s' where task_id='%d'",TABLEPRE,$consume['cash'],$consume['credit'],$this->_task_id));
					
					// 更改订单状态到已付款状态
					db_factory::updatetable ( TABLEPRE . "witkey_order", array ("order_status" => "ok" ), array ("order_id" => "$order_id" ) );
					if ($order_amount < $task_config ['audit_cash']) {
						 // 如果订单的金额比发布任务时配置的审核金额要小
						$this->set_task_status ( 1 ); 
						// 状态更改为审核状态
						return pay_return_fac_class::struct_response ( $_lang['operate_notice'], $_lang['task_pay_success_and_wait_admin_audit'], $url, 'success' );
					} else {
						$this->set_task_status ( 2 );
						 // 状态更改为进行状态
						return pay_return_fac_class::struct_response ( $_lang['operate_notice'], $_lang['task_pay_success_and_task_pub_success'], $url, 'success' );
					}
					break;
				case "0" :
					 // 支付失败
					$pay_url = $_K ['siteurl'] . "/index.php?do=pay&order_id=$order_id"; 
					// 支付跳转链接
					return pay_return_fac_class::struct_response ( $_lang['operate_notice'], $_lang['task_pay_error_and_please_repay'], $pay_url, 'warning' );
					break;
			}
		}
	}
	
	
	/**
	 * 获取用户中心雇主当前操作
	 * @param $m_id 模型编号
	 * @param $t_id 任务编号
	 * @param $url  操作链接
	 */
	public static function master_opera($m_id,$t_id,$url,$t_cash) {
		global $uid,$_K,$do,$view,$_lang;
		$status = db_factory::get_count ( sprintf ( ' select task_status from %switkey_task where task_id=%d and uid=%d', TABLEPRE, $t_id, $uid ), 0, 'task_status', 600 );
		$order_info = db_factory::get_one(sprintf("select order_id from %switkey_order_detail where obj_id=%d",TABLEPRE,$t_id));				
		$site   = $_K['siteurl'].'/';
		$button = array();
		 //查看
		$button['view'] = array(
				'href'=>$site.'index.php?do=task&task_id='.$t_id,
				'desc'=>$_lang['view'], 
				'ico'=>'book');
		 //一键发布
		$button['onkey'] = array(
				'href'=>$site.'index.php?do=release&t_id='.$t_id.'&model_id='.$m_id.'&pub_mode=onekey',
				'desc'=>$_lang['one_key_pub'], 
				'ico'=>'book');
		//使用链接
		//描述
		 //删除
		//点击操作
		//图标
		$button['del'] = array(
				'href'=>$site.$url.'&ac=del&task_id='.$t_id,
				'desc'=>$_lang['delete'],
				'click'=>'return del(this);',
				'ico'=>'trash');
		switch ($status) {
			case 0 : 
				//待付款
				//付款
				//点击操作
				$button['pay'] = array(
				'href'=>$site.'index.php?do='.$do.'&view='.$view.'&task_id='.$t_id.'&model_id='.$m_id.'&ac=pay',
				'desc'=>$_lang['payment'], 
				'click'=>"return pay(this,$t_cash,$order_info[order_id]);",
				'ico'=>'loop');
				break;
			case 2 : 
				//进行中
				//工具箱
				$button['tool'] = array(
				'href'=>$site.'index.php?do=task&task_id='.$t_id.'&view=tools',
				'desc'=>$_lang['toolbox'], 
				'ico'=>'trash');
				break;
			case 3 : 
				//选稿中
				//选标
				$button['view']['desc'] = '';  
				$button['view']['href'] = $site.'index.php?do=task&task_id='.$t_id.'&view=work';
				break;
			case 4 :
				 //投票中
				 //确认工作
				$button['confirm_work'] = array(
						'click'=>"work_over('index.php?do=task&task_id=$t_id&op=work_over')",
						'desc'=>$_lang['confirm_work'], 
						'ico'=>'book',
						'href'=>'javascript:void(0);'
				);
				break;
	 
		}
		if(!in_array($status,array(0,8,9,10))){
			//非代付款、结束、失败、审核失败。不得出现删除按钮
			unset($button['del']);
		}
		return $button;
	}
	/**
	 * 获取用户中心威客当前操作
	 * @param $m_id 模型编号
	 * @param $t_id 任务编号
	 * @param $w_id 稿件编号
	 * @param $url  操作链接
	 */
	public static function wiki_opera($m_id,$t_id, $w_id,$url) {
		global $uid,$_K,$do,$view,$_lang;
		$status = db_factory::get_count ( sprintf ( ' select task_status from %switkey_task where task_id=%d', TABLEPRE, $t_id, $uid ), 0, 'task_status', 600 );
		$site   = $_K['siteurl'].'/';
		$button = array();
		 //查看稿件
		$button['view'] = array(
				'href'=>$site.'index.php?do=task&task_id='.$t_id.'&view=work&ut=my&work_id='.$w_id,
				'desc'=>$_lang['view_work'],
				'ico'=>'book');
		switch ($status) {
			case 2 : 
				//进行中
				//分享任务
				$button['share'] = array(
				'href'=>'javascript:void(0);',
				'desc'=>$_lang['share'],
				'click'=>'share('.$t_id.');',
				'ico'=>'share');
				break;
			case 4 :
				 //工作中
				 //确认工作
				$button['start_work'] = array(
						'click'=>"work_over('index.php?do=task&task_id=$t_id&op=pub_agreement')",
						'desc'=>$_lang['confirm_work'],
						'ico'=>'book',
						'href'=>'javascript:void(0);'
				);
 
			case 8:
				//结束
			case 9:
				//失败
				//使用链接
				//描述删除
				//点击操作
				//图标
				$button['del'] = array(
				'href'=>$site.$url.'&ac=del&work_id='.$w_id,
				'desc'=>$_lang['delete'],
				'click'=>'return del(this);',
				'ico'=>'trash');
				break;
		}
		return $button;
	}

}