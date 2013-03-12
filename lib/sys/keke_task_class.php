<?php
/**
 * 
 * 任务业务处理基类 ...
 * @author Administrator
 * @method init 任务信息初始化
 * =>模型配置初始化	 	task_config_init
 * =>当前任务信息   		task_info_init
 * =>雇主基本信息		employer_info_init
 * =>当前威客信息		witkey_info_init
 * =>获取随机客服信息	kf_info_init
 * =>任务时间规则
 * =>任务延期规则
 * check_if_favor       检测是否收藏
 * check_if_report      检测是否举报(维权|投诉)
 * check_if_master		  检测是否雇主
 * check_if_over_delay  检测是否超过延期次数    
 * check_if_good_mark   检测是否好评
 * 
 * get_work_fields     获取由于模型对应稿件表使用字段
 * get_search_condit    获取稿件区搜索统计
 * get_task_file		 获取任务附件
 * get_task_related	            获取相关任务
 * get_task_timedesc 	        获取任务时间描述
 * get_comment 			  获取任务(稿件)留言
 * get_user_special      获取用户指定字段信息
 * set_task_status		    任务状态变更
 * set_task_favor		    任务收藏
 * set_task_reqedit		   	    任务需求补充
 * set_task_delay		    任务延期加价
 * set_task_mark		    任务(稿件)评价
 * set_mark				评价
 * set_report             交易维权
 * set_comment          留言回复
 * del_work              稿件删除
 * notify_user      	      消息通知
 * 
 * process_can 		      当前操作判断
 * work_hand    		      任务稿件提交
 * work_choose 		      任务稿件选择		 
 * has_new_comment    稿件有信息的回复
 */
keke_lang_class::load_lang_class('keke_task_class');
abstract class keke_task_class {
	
	public $_task_obj; //任务对象
	public $_task_id; //任务编号
	public $_task_title; //任务标题
	public $_task_status; //任务状态
	public $_task_config; //任务配置
	public $_task_info; //任务信息
	

	public $_profit_rate; //任务提成比
	public $_fail_rate; //任务失败返金提成比
	
	public $_trust_mode;//担保模式

	public $_guid; //雇主编号
	public $_gusername; //雇主姓名
	public $_g_userinfo; //雇主信息
	

	public $_uid; //当前用户编号
	public $_username; //当前用户名
	public $_userinfo; //当前用户信息
	public $_priv; //威客操作权限
	

	public $_kf_uid; //客服编号
	public $_kf_userinfo; //客服信息
	

	public $_process_can; //操作状态数组
	

	public $_model_id; //模型编号
	public $_model_code; //模型类型
	public $_model_name; //模型名称
	

	public $_msg_obj; //消息对象
	

	public function __construct($task_info) {
		$this->_task_info = $task_info;
		$task_info['is_trust'] and $this->_trust_mode="1" or $this->_trust_mode="0";//担保模式
		$this->_task_id = $task_info ['task_id'];
		$this->_task_title = $task_info ['task_title'];
		$this->_task_status = $task_info ['task_status'];
		$this->_profit_rate = $task_info ['profit_rate'];
		$this->_fail_rate = $task_info ['task_fail_rate'];
		$this->init_task ();
		$this->_task_obj = new Keke_witkey_task_class ();
		$this->_msg_obj = new keke_msg_class ();
	
	}
	public function init_task() {
		$this->task_config_init ();
		$this->employer_info_init ();
		$this->witkey_info_init ();
		$this->kf_info_init ();
	
	}
	/**
	 * 任务配置初始化
	 */
	public function task_config_init() {
		global $model_list;
		$model_info = $model_list [$this->_task_info ['model_id']];
		if ($model_info) {
			$this->_model_id = $model_info ['model_id'];
			$this->_model_code = $model_info ['model_code'];
			$this->_model_name = $model_info ['model_name'];
			$model_info ['config'] and $task_config = unserialize ( $model_info ['config'] );
			$this->_task_config = $task_config;
		}
	}
	/**
	 * 雇主信息初始化
	 */
	public function employer_info_init() {
		$task_info = $this->_task_info;
		$this->_guid = $task_info ['uid'];
		$this->_gusername = $task_info ['username'];
		$this->_g_userinfo = kekezu::get_user_info ( $task_info ['uid'] );
	}
	/**
	 * 威客信息初始化
	 */
	public function witkey_info_init() {
		global $user_info;
		$this->_uid = $user_info ['uid'];
		$this->_username = $user_info ['username'];
		$this->_userinfo = $user_info;
	}
	/**
	 * 客服信息初始化
	 */
	public function kf_info_init() {
		$this->_kf_uid = $this->_task_info ['kf_uid'];
		$this->_kf_userinfo = kekezu::get_user_info ( $this->_task_info ['kf_uid'] );
	}
	/**
	 * 获取不同模型下的不同稿件表使用字段。
	 * @return array[tab=>表名,pk=>主键名,st=>状态字段名
	 */
	public function get_work_fields() {
		$mode_code = $this->_model_code;
		$fields_arr = array ();
		if ($mode_code == 'dtender' || $mode_code == 'tender') {
			$fields_arr ['tab'] = "witkey_task_bid";
			$fields_arr ['pk'] = "bid_id";
			$fields_arr ['st'] = "bid_status";
		} else {
			$fields_arr ['tab'] = "witkey_task_work";
			$fields_arr ['pk'] = "work_id";
			$fields_arr ['st'] = "work_status";
		}
		return $fields_arr;
	}
	/**
	 * 获取稿件区搜索条件稿件统计。
	 */
	public function get_search_condit() {
		$search_condit = array ();
		$fields_arr = $this->get_work_fields ();
		$search_condit = kekezu::get_table_data ( " count($fields_arr[pk]) count,$fields_arr[st]", $fields_arr ['tab'], " task_id='" . $this->_task_id . "' ", "", $fields_arr ['st'], "", $fields_arr ['st'], 3600 );
		return $search_condit;
	}
	/**
	 * 获取任务附件
	 */
	public function get_task_file() {
		return kekezu::get_table_data ( "*", "witkey_file", " obj_type = 'task' and work_id='0' and task_id = '" . $this->_task_id . "'", "", "", "", "file_id", 3600 );
	}
	/**
	 * 获取相关任务信息
	 * @param boolean $same		同类任务
	 * @param boolean $lasted   最新任务
	 * @return array ['same'=>
	 * 'lasted'=>]
	 */
	public function get_task_related($same = true, $lasted = true) {
		$related = array ();
		if ($same) {
			$same_task = db_factory::query ( sprintf ( " select task_title,task_id,task_cash,task_cash_coverage,model_id from %switkey_task where indus_id = '%d' and model_id='%d' and task_id!='%d' and task_status in(2,3) and focus_num>0 order by task_cash desc limit 0,5", TABLEPRE, $this->_task_info ['indus_id'], $this->_model_id, $this->_task_id ) );
			$same_task and $related ['same'] = $same_task;
		}
		if ($lasted) {
			$favor_task = db_factory::query ( sprintf ( " select task_title,task_id,task_cash,task_cash_coverage,model_id from %switkey_task where indus_id = '%d' and model_id='%d' and task_status in(2,3) and task_id!='%d' order by view_num desc limit 0,5", TABLEPRE, $this->_task_info ['indus_id'], $this->_model_id, $this->_task_id ) );
			$favor_task and $related ['favor'] = $favor_task;
		}
		return $related;
	}
	/**
	 * 获取任务(稿件留言)
	 * @param string $obj_type 留言对象
	 * @param int $obj_id     对象编号
	 * @param int $task_id 任务编号
	 * @param int $work_uid    投稿人
	 */
	
	public static function get_comment($obj_type, $task_id, $obj_id, $work_uid) {
		global $uid;
		$condit = "  from " . TABLEPRE . "witkey_comment where obj_type='$obj_type' and obj_id='$obj_id' and origin_id='$task_id'";
		$comment_arr = db_factory::query ( " select * " . $condit );
		if ($comment_arr && $work_uid == $uid) {
			db_factory::execute ( sprintf ( " update %switkey_comment set status = '1' where obj_id= '%d' and origin_id='$task_id'", TABLEPRE, $obj_id ) );
		}
		return $comment_arr;
	}
	public static function get_task_comment($task_id, $url, $page) {
		$comment_obj = keke_table_class::get_instance ( "witkey_comment" );
		return $comment_arr = $comment_obj->get_grid ( "obj_id='$task_id' and obj_type='task' and p_id=0 order by comment_id desc", $url, $page, 10, null, 1, 'task_leave' );
	
	}
	/**
	 * 任务阶段描述
	 */
	public function get_task_stage_desc() {
		$status_arr = $this->_task_status_arr; //状态数组
		$size = sizeof ( $status_arr );
		$arr = array ();
		for($i = 0; $i <= $size; $i ++) {
			if ($i < $this->_task_status) {
				$arr [$i] = 't_l passed'; //状态样式
			} elseif ($this->_task_status < $i) {
				$arr [$i] = 'no'; //状态样式
			} else {
				$arr [$i] = 'selected'; //状态样式
			}
		}
		return $arr;
	}
	/**
	 * 任务延期加价(投稿中才可延期)
	 * @param int   $delay_day 延期天数
	 * @param float $delay_cash 延期金额
	 * @param string $url 跳转链接
	 * @param string $output 消息输出类型
	 * @param $trust_response 担保回调响应
	 * @return show_msg
	 */
	public function set_task_delay($delay_day, $delay_cash, $url = '', $output = 'normal', $trust_response = false) {
		global $kekezu,$user_info;
		global $_lang;
		
		$basic_config = $kekezu->_sys_config;
		kekezu::check_login ( $url, $output ); //检测登录
		if ($this->check_if_over_delay ( $delay_day, $delay_cash, $url, $output )) {
			$task_info = $this->_task_info;
				$delay_obj = new Keke_witkey_task_delay_class ();
				$task_obj = $this->_task_obj;
				$mycredit = $this->_userinfo ['credit']; //用户金币
				$mycash = $this->_userinfo ['balance']; //用户现金
				$basic_config ['credit_is_allow'] != 1 and $mycredit = '0'; //金币未开启使用。禁用金币
				/*金额不足*/
				if ($delay_cash > $mycredit + $mycash) {
					$repay_cash = $delay_cash - $mycredit - $mycash; //需要充值金额
					kekezu::keke_show_msg ( "index.php?do=user&view=finance&op=recharge&type=delay&cash=" . $repay_cash, $_lang['your_account_balance_not_enough']."<a href=\"index.php?do=user&view=finance&op=recharge&type=delay&cash=" . $repay_cash . "\"".$_lang['recharge_page']."</a>".$_lang['recharge'], "error", $output );
				} else { //创建延期记录
					$delay_obj->setDelay_cash ( $delay_cash );
					$delay_obj->setOn_time ( time () );
					$delay_obj->setDelay_status ( 1 );
					$delay_obj->setUid ( $this->_uid );
					$delay_obj->setDelay_day ( $delay_day );
					$delay_obj->setTask_id ( $this->_task_id );
					$delay_id = $delay_obj->create_keke_witkey_task_delay ();
					if ($delay_id) {
						$credit_cost = '0'; //默认金币不开启
						if ($basic_config ['credit_is_allow'] == 1) { //开启金币
							//少于延期金额扣除所有金币,否则扣除延期金额对应金币
							$mycredit < $delay_cash and $credit_cost = $mycredit or $credit_cost = $delay_cash;
						}
						//花费金币少于延期金额,说明金币不够
						$credit_cost < $delay_cash and $cash_cost = $delay_cash - $credit_cost or $cash_cost = '0';
						$data = array(':task_id'=>$this->_task_id,':task_title'=>$this->_task_title);
						keke_finance_class::init_mem('task_delay', $data);
						keke_finance_class::cash_out ( $this->_uid, $delay_cash, 'task_delay', $cash_cost * $this->_profit_rate, 'task', $this->_task_id );
						
						/**任务更改**/
						//任务投稿中，直接更改任务结束时间
						$add_time = $delay_day * 24 * 3600;
						$real_cash_add = $delay_cash * (100 - $this->_profit_rate) / 100;
						db_factory::execute ( sprintf ( " update %switkey_task set end_time=end_time+'%s',sub_time=sub_time+'%s',is_delay =ifnull(is_delay,0)+1
							,credit_cost=credit_cost+'%s',cash_cost=cash_cost+'%s',real_cash=real_cash+'%s',task_cash=task_cash+'%s' where task_id='%d'", TABLEPRE, $add_time, $add_time, $credit_cost, $cash_cost, $real_cash_add, $delay_cash, $this->_task_id ) );
						//订单更改
						db_factory::updatetable ( TABLEPRE . "witkey_order", array ("order_amount" =>($task_info['task_cash']+$delay_cash) ), array ("order_id" =>$task_info['order_id']) );
						if ($this->_model_id == 3) {
							$add_single = $delay_cash / intval ( $this->_task_info [work_count] );
							db_factory::execute ( sprintf ( "update %switkey_task set single_cash = single_cash+'%s' where task_id='%d'", TABLEPRE, $add_single, $this->_task_id ) );
						}
						if($this->_model_code=='mreward'){
							mreward_task_class::task_delay($this->_task_id,$task_info['task_cash'],$delay_cash);
						}
						
						kekezu::keke_show_msg ( $url, $_lang['task_delay_success'], "", $output );
					} else {
						kekezu::keke_show_msg ( $url, $_lang['task_delay_fail'], "error", $output );
					}
				}
		}
	}
	/**
	 * 任务留言，任务留言回复
	 * @param array $comment_arr
	 * @param boolen $is_reply
	 * @return json
	 */
	public static function set_task_comment($comment_arr, $is_reply = false) {
		strtolower ( CHARSET ) == 'gbk' and $comment_arr ['content'] = kekezu::utftogbk ( $comment_arr ['content'] );
		$comment_arr ['content'] = kekezu::escape ( kekezu::str_filter ( $comment_arr ['content'] ) );
		$lid = db_factory::inserttable ( TABLEPRE . "witkey_comment", $comment_arr, 1 );
		//任务留言数加1
		$is_reply or db_factory::execute ( sprintf ( "update %switkey_task set leave_num = leave_num+1 where task_id = '%d'", TABLEPRE, $comment_arr ['obj_id'] ) );
		return $lid;
	}
	
	/**
	 * 
	 * 获取评论详细信息
	 */
	public static function get_comment_info($comment_id) {
		$commnet_obj = new Keke_witkey_comment_class ();
		$commnet_obj->setWhere ( "comment_id = $comment_id" );
		$comment_info = $commnet_obj->query_keke_witkey_comment ();
		$comment_info = $comment_info [0];
		return $comment_info;
	}
	
	/**
	 * 获取用户表中指定字段的信息   主要用于批量处理是获取指定值   
	 * @param unknown_type $uid  用户编号
	 * @param unknown_type $special_fields  特殊字段     可以","分隔   默认为查询手机，邮箱
	 */
	public function get_user_special($uid, $special_fields = 'mobile,email') {
		return db_factory::get_one ( sprintf ( " select %s from %switkey_space where uid = '%d' ", $special_fields, TABLEPRE, $uid ) );
	}
	/**
	 * 获取不同状态的任务稿件信息   用户业务类调取数据
	 * @param int $work_id  稿件编号  当稿件编号存在时  第二参数无效
	 * @param int or string  $work_status 稿件状态  需要查询多种状态信息、可以传递","分隔的字符串
	 */
	public function get_task_work($work_id = '', $work_status = '') {
		$fields_arr = $this->get_work_fields ();
		$tab = $fields_arr ['tab'];
		$pk = $fields_arr ['pk'];
		$st = $fields_arr ['st'];
		if ($work_id) {
			return db_factory::get_one ( sprintf ( " select * from %s%s where %s='%d'", TABLEPRE, $tab, $pk, $work_id ) );
		} else {
			$where = " 1 = 1 and task_id = '" . $this->_task_id . "' ";
			strpos ( $work_status, "," ) == FALSE and $where .= " and $st = '$work_status' " or $where .= " and $st in ($work_status) ";
			return kekezu::get_table_data ( "*", $tab, $where );
		}
	}
	/**
	 * 获取任务稿件信息
	 * * @param int or string $file_ids 单或","分隔字符串
	 */
	public static function get_work_file($file_ids) {
		$sql = " select * from " . TABLEPRE . "witkey_file where obj_type='work' and  file_id ";
		strpos ( $file_ids, "," ) !== FALSE and $sql .= " in (" . $file_ids . ")" or $sql .= " = '$file_ids' ";
		return db_factory::query ( $sql );
	}
	public function get_mark_count() {
		return kekezu::get_table_data ( " count(mark_id) count,mark_status", "witkey_mark", "model_code='" . $this->_model_code . "' and origin_id='$this->_task_id'", "", "mark_status", "", "mark_status", 3600 );
	}
	/**
	 * 获取来自雇主或者威客的评价信息
	 * *
	 */
	public function get_mark_count_ext() {
		return kekezu::get_table_data ( " count(mark_id) c,mark_type", "witkey_mark", "model_code='" . $this->_model_code . "' and origin_id='$this->_task_id' and mark_status>0", "", "mark_type", "", "mark_type" );
	}
	/**
	 * 更改任务状态
	 * @param int $to_status 变更状态
	 * @return boolean
	 */
	public function set_task_status($to_status) {
		return db_factory::execute ( sprintf ( " update %switkey_task set task_status='%d' where task_id='%d'", TABLEPRE, $to_status, $this->_task_id ) );
	}
	
	/**
	 * 任务补充需求
	 * @param string $ext_desc 补充内容
	 * @param string $url    操作提示链接  具体参见 kekezu::keke_show_msg
	 * @param string $output 消息输出方式 具体参见 kekezu::keke_show_msg
	 * @return show_msg
	 */
	public function set_task_reqedit($ext_desc, $url = '', $output = 'normal') {
		global $_lang;
		if ($this->check_if_can_reqedit ( $url, $output )) {
			$task_obj = $this->_task_obj;
			$task_obj->setWhere ( "task_id = '$this->_task_id'" );
			CHARSET == 'gbk' and $ext_desc = kekezu::utftogbk ( $ext_desc );
			$task_obj->setExt_desc ( $ext_desc );
			$res = $task_obj->edit_keke_witkey_task ();
			if ($res) {
				kekezu::keke_show_msg ( $url, $_lang['task_need_add_edit_success'], "", $output );
			} else {
				kekezu::keke_show_msg ( $url, $_lang['task_need_add_edit_fail'], "error", $output );
			}
		}
	}
	/**
	 * 稿件评论
	 * @param string $obj   评论对象  task/work/case/shop/space
	 * @param int $obj_id  对象编号
	 * @param string $content 回复内容
	 * @param int $pid     上级回复编号
	 * @param string $url    操作提示链接  具体参见 kekezu::keke_show_msg
	 * @param string $output 消息输出方式 具体参见 kekezu::keke_show_msg
	 */
	public function set_work_comment($obj, $work_id, $content, $pid = '', $url = '', $output = 'normal') {
		global $_lang,$kekezu;
		$comment_desc = kekezu::str_filter (kekezu::escape($content) );
		CHARSET == 'gbk' and $comment_desc = kekezu::utftogbk ( $comment_desc );
		$obj == 'task' and $str[op] = $_lang['task'] or $str = $_lang['workl'];
		//$this->_priv ['comment'] ['pass'] or kekezu::keke_show_msg ( $url, $this->_priv ['comment'] ['notice'] . $_lang['no_reply_rights'], "error", $output );
		kekezu::k_match(array($kekezu->_sys_config['ban_content']),$comment_desc) and kekezu::keke_show_msg('',$_lang['sensitive_word'],'error','json');
		$comment_obj = new Keke_witkey_comment_class ();
		$comment_obj->_comment_id = null;
		$comment_obj->setContent ( $comment_desc );
		$comment_obj->setObj_id ( intval ( $work_id ) );
		$comment_obj->setOrigin_id ( intval ( $this->_task_id ) );
		$comment_obj->setP_id ( intval ( $pid ) );
		$comment_obj->setOn_time ( time () );
		$comment_obj->setObj_type ( $obj );
		$comment_obj->setUid ( $this->_uid );
		$comment_obj->setUsername ( $this->_username );
		$comment_id = $comment_obj->create_keke_witkey_comment ();
		 
		 
		if ($comment_id) {
			$this->plus_work_comment_num ( $work_id );
			kekezu::echojson($_lang['comment_success'],1,htmlspecialchars($comment_desc));die();
			//kekezu::keke_show_msg ( $url, $str . $_lang['comment_success'], "", $output );
		} else {
			kekezu::echojson($_lang['comment_fail'],0,htmlspecialchars($comment_desc));die();
			//kekezu::keke_show_msg ( $url, $str . $_lang['comment_fail'], "error", $output );
		}
	 
	}
	/**
	 * 任务（稿件）举报
	 * @param string $obj 举报对象
	 * @param $obj_id 对象编号
	 * @param $report_type 举报类型
	 * @param $to_uid 被举报人
	 * @param $to_username 被举报人姓名
	 * @param $file_name 上传文件路径
	 * @return json 
	 */
	public function set_report($obj, $obj_id, $to_uid, $to_username, $report_type, $file_name, $desc) {
		global $_lang;
		//$this->_priv ['report'] ['pass'] or kekezu::keke_show_msg ( '', $this->_priv ['report'] ['notice'] . $_lang['no_report_rights'], "error", 'json' );
		$this->_uid != $this->_guid and $user_type = '1' or $user_type = '2';
		$res = keke_report_class::add_report ( $obj, $obj_id, $to_uid, $to_username, $desc, $report_type, $this->_task_status, $this->_task_id, $user_type, $file_name );
	}
	/**
	 * 任务（稿件）举报
	 * @param int $work_id 稿件编号
	 * @param string $url    操作提示链接  具体参见 kekezu::keke_show_msg
	 * @param string $output 消息输出方式 具体参见 kekezu::keke_show_msg
	 */
	public function del_work($work_id, $url = '', $output = 'normal') {
		global $user_info;
		global $_lang;
		$user_info ['group_id'] != '7' and kekezu::keke_show_msg ( $url, $_lang['you_no_rights_delete_manuscript'], "error", $output );
		$fields_arr = $this->get_work_fields ();
		$tab = $fields_arr ['tab'];
		$pk = $fields_arr ['pk'];
		$st = $fields_arr ['st'];
		$res = db_factory::execute ( sprintf ( " delete from %s%s where %s = '%d'", TABLEPRE, $tab, $pk, $work_id ) );
		//$num = db_factory::execute ( sprintf ( "delete from %switkey_comment where origin_id='%d' and obj_id = '%d'", TABLEPRE, $this->_task_id, $work_id ) );
		if ($res) {
			db_factory::execute ( sprintf ( "update %switkey_task set work_num = work_num-1 where task_id = '%d'", TABLEPRE, $this->_task_id ) );
			kekezu::keke_show_msg ( $url, $_lang['manuscript_delete_success'], "", $output );
		} else {
			kekezu::keke_show_msg ( $url, $_lang['manuscript_delete_fail'], "error", $output );
		}
	}
	/**
	 * 任务稿件提交
	 */
	abstract function work_hand($work_desc, $hdn_att_file, $hidework = '2', $url = '', $output = 'normal');
	
	/**
	 * 任务稿件选择
	 */
	abstract function work_choose($work_id, $to_status, $url = '', $output = 'normal',$trust_response=false);
	/**
	 * 任务操作判断
	 */
	abstract function process_can();
	
	/**
	 * 检测是否有新的回复
	 * @param $page 当前页码
	 * @param $limit 分页条数
	 */
	public function has_new_comment($page, $limit) {
		$new_arr = array ();
		$fields_arr = $this->get_work_fields ();
		$tab = $fields_arr ['tab'];
		$pk = $fields_arr ['pk'];
		$pre = ($page - 1) * $limit;
		$work_ids = kekezu::get_table_data ( $pk, $tab, " task_id ='$this->_task_id'", "", "", " $pre,$limit", $pk );
		if ($work_ids) {
			$new_arr = kekezu::get_table_data ( "count(comment_id) count,obj_id", "witkey_comment", " obj_id in (" . implode ( ",", array_keys ( $work_ids ) ) . ") and status=0 and origin_id='$this->_task_id'", "", " obj_id ", "", "obj_id" );
		}
		return $new_arr;
	}
	/**
	 * 消息通知雇主(威客)
	 * @param string $action  消息动作
	 * @param string $title	    消息标题
	 * @param array  $v_arr   消息内容参数
	 * @param int    $notify_type  1=>威客,2=>雇主
	 * @param int    $uid     接收方UID
	 */
	public function notify_user($action, $title, $v_arr = array(), $notify_type = 2, $uid = null) {
		switch ($notify_type) {
			case 1:
				! $uid and $user_info = $this->_userinfo or $user_info = kekezu::get_user_info ( $uid ); //默认为当前用户信息
				break;
			case 2:
				$user_info = $this->_g_userinfo;
				break;
		}
		$this->_msg_obj->send_message ( $user_info ['uid'], $user_info ['username'], $action, $title, $v_arr, $user_info ['email'], $user_info ['mobile'] );
	}
	/**
	 * 检测是否超出延期次数
	 * @param int $delay_day 延期天数
	 * @param float $delay_cash 延期金额
	 * @param  string $url    跳转链接
	 * @param  string $output 消息输出类型
	 * @return boolean or show_msg
	 */
	public function check_if_over_delay($delay_day, $delay_cash, $url = '', $output = 'normal') {
		global $_lang;
		$delay_rule = $this->_delay_rule; //延期规则
		$rule_count = sizeof ( $delay_rule ); //延期规则个数
		if ($rule_count == '0') { //没有延期规则
			kekezu::keke_show_msg ( $url, $this->_model_name . $_lang['task_model_no_open_delay'], "error", $output );
			return false;
		} else {
			if ($this->_process_can ['delay']) { //可以延期
				$delay_count = intval ( $this->_task_info ['is_delay'] );
				$min_cash = intval ( $this->_task_config ['min_delay_cash'] ); //配置最小延期金额
				$max_day = intval ( $this->_task_config ['max_delay'] ); //配置最大延期天数
				$this_min_cash = intval ( $delay_rule [$delay_count] ['defer_rate'] * $this->_task_info ['task_cash'] / 100 ); //本次最小延期金额
				$min_cash > $this_min_cash and $real_min = $min_cash or $real_min = $this_min_cash; //真正最小金额
				if ($delay_count < $rule_count) { //还可延期
					if ($delay_cash < $real_min) { //小于当前延期最小金额
						kekezu::keke_show_msg ( $url, $_lang['the_delay_money_less_min'] . $real_min . $_lang['yuan_and_delay_fail'], "error", $output );
						return false;
					} elseif ($delay_day > $max_day) {
						kekezu::keke_show_msg ( $url, $_lang['the_delay_days_more_max'] . $max_day . $_lang['tian_and_delay_fail'], "error", $output );
						return false;
					} else {
						return true;
					}
				} else {
					kekezu::keke_show_msg ( $url, $_lang['task_delay_times_has_reached'], "error", $output );
					return false;
				}
			} else {
				kekezu::keke_show_msg ( $url, $_lang['task_no_ongoing_no_delay'], "error", $output );
				return false;
			}
		}
	}
	/**
	 * 检测是否为雇主
	 * @param string $url    操作提示链接  具体参见 kekezu::keke_show_msg
	 * @param string $output 消息输出方式 具体参见 kekezu::keke_show_msg
	 * @return boolean or show_msg
	 */
	public function check_if_master($url = '', $output = 'normal') {
		global $_lang;
		if ($this->_uid != $this->_guid)
			return TRUE;
		else {
			kekezu::keke_show_msg ( $url, $_lang['can_not_operate_youself_task'], "error", $output );
			return false;
		}
	}
	/**
	 * 检测是否好评
	 * @param int     $mark_id 互评编号
	 * @param  int  $to_status 变更状态
	 * @param string $url    操作提示链接  具体参见 kekezu::keke_show_msg
	 * @param string $output 消息输出方式 具体参见 kekezu::keke_show_msg
	 * @return boolean or show_msg
	 */
	public function check_if_good_mark($mark_id, $to_status, $url = '', $output = 'normal') {
		global $_lang;
		$mark_status = db_factory::get_count ( sprintf ( " select mark_status from %switkey_mark where mark_id='%d'", TABLEPRE, $mark_id ) );
		if ($mark_status == 1 && $mark_status != $to_status) { //阻止好评变更为其他状态
			kekezu::keke_show_msg ( $url, $_lang['good_values_can_not_update'], "error", $output );
		} else
			return true;
	}
	/**
	 * 检测是否可以进行互评
	 * @param string $url    操作提示链接  具体参见 kekezu::keke_show_msg
	 * @param string $output 消息输出方式 具体参见 kekezu::keke_show_msg
	 * @return boolean or show_msg
	 */
	public function check_if_can_mark($url = '', $output = 'normal') {
		global $_lang;
		if ($this->_process_can ['work_mark'] || $this->_process_can ['task_mark']) {
			return true;
		} else {
			kekezu::keke_show_msg ( $url, $_lang['current_status_can_not_mark'], "error", $output );
		}
	}
	/**
	 * 检测是否可以补充需求
	 * @param string $url    操作提示链接  具体参见 kekezu::keke_show_msg
	 * @param string $output 消息输出方式 具体参见 kekezu::keke_show_msg
	 * @return boolean or show_msg
	 */
	public function check_if_can_reqedit($url = '', $output = 'normal') {
		global $_lang;
		if ($this->_process_can ['reqedit']) {
			return true;
		} else {
			kekezu::keke_show_msg ( $url, $_lang['current_status_not_add_need'], "error", $output );
		}
	}
	/**
	 * 检测是否可以交稿
	 * @param string $url    操作提示链接  具体参见 kekezu::keke_show_msg
	 * @param string $output 消息输出方式 具体参见 kekezu::keke_show_msg
	 * @return boolean or show_msg
	 */
	public function check_if_can_hand($url = '', $output = 'normal') {
		global $_K;
		global $_lang;
		$this->_priv ['work_hand'] ['pass'] or kekezu::keke_show_msg ( $url, $_lang['no_submit_work_rights'].$this->_priv ['work_hand'] ['notice'] , "error", $output );
		if ($this->_process_can ['work_hand']) {
				return true;
		} else {
			kekezu::keke_show_msg ( $url, $_lang['current_status_can_not_submit'], "error", $output );
		}
	}
	/**
	 * 检测是否可以选稿
	 * @param string $url    操作提示链接  具体参见 kekezu::keke_show_msg
	 * @param string $output 消息输出方式 具体参见 kekezu::keke_show_msg
	 * @return boolean or show_msg
	 */
	public function check_if_can_choose($url = '', $output = 'normal') {
		global $_lang;
		if ($this->_process_can ['work_choose']) {
			return true;
		} else {
			kekezu::keke_show_msg ( $url, $_lang['current_status_can_not_choose'], "error", $output );
		}
	}
	/**
	 * 权限格式化
	 */
	public function user_priv_format($priv) {
		$arr = array ();
		if ($this->_uid == $this->_guid) {
			foreach ( $priv as $k => $v ) {
				$arr [$k] ['pass'] = true;
			}
		} else {
			$arr = $priv;
		}
		return $arr;
	}
	/**
	 * 任务查看数量更新
	 * @return boolean
	 */
	public function plus_view_num() {
		if (! $_SESSION ['task_views_' . $this->_task_id . '_' . $this->_uid] && $this->_uid != $this->_guid) {
			$_SESSION ['task_views_' . $this->_task_id . '_' . $this->_uid] = 1;
			return db_factory::execute ( sprintf ( 'update %switkey_task set view_num=view_num+1 where task_id=%d', TABLEPRE, $this->_task_id ) );
		}
	}
	/**
	 * 任务交稿数量更新
	 * @return boolean
	 */
	public function plus_work_num() {
		return db_factory::execute ( sprintf ( "update %switkey_task set work_num=work_num+1 where task_id ='%d'", TABLEPRE, $this->_task_id ) ); //更新任务稿件数量
	}
	/**
	 * 用户中标次数更新
	 * @param int $uid 用户ID
	 * @return boolean
	 */
	public function plus_accepted_num($uid) {
		return db_factory::execute ( sprintf ( "update %switkey_space set accepted_num = accepted_num+1 where uid='%d'", TABLEPRE, intval ( $uid ) ) );
	}
	/**
	 * 用户交稿数量更新
	 * @return boolean
	 */
	public function plus_take_num() {
		return db_factory::execute ( sprintf ( "update %switkey_space set take_num = take_num+1 where uid ='%d'", TABLEPRE, $this->_uid ) );
	}
	/**
	 * 任务留言量更新
	 * @return boolean
	 */
	public function plus_leave_num() {
		return db_factory::execute ( sprintf ( "update %switkey_task set leave_num=leave_num+1 where task_id ='%d'", TABLEPRE, $this->_task_id ) );
	}
	/**
	 * 任务关注数更新
	 * @return boolean
	 */
	public function plus_focus_num() {
		return db_factory::execute ( sprintf ( "update %switkey_task set focus_num=focus_num+1 where task_id ='%d'", TABLEPRE, $this->_task_id ) );
	}
	/**
	 * 悬赏
	 * 稿件留言数更新
	 * @return boolean
	 */
	public function plus_work_comment_num($work_id) {
		$fields_arr = $this->get_work_fields ();
		$tab = $fields_arr ['tab'];
		$pk = $fields_arr ['pk'];
		return db_factory::execute ( sprintf ( "update %s%s set comment_num=comment_num+1 where %s ='%d'", TABLEPRE, $tab, $pk, $work_id ) );
	}
	/**
	 * 任务评价数更新 每次+2
	 */
	public function plus_mark_num() {
		return db_factory::execute ( sprintf ( "update %switkey_task set mark_num=ifnull(mark_num,0)+2 where task_id ='%d'", TABLEPRE, $this->_task_id ) );
	}
	public static function process_desc() {
		global $_lang;
		return array ("reqedit" => $_lang['add_need'], "delay" => $_lang['task_delay'], "work_choose" => $_lang['task_choose_work'], "task_mark" => $_lang['task_mark'], "task_pay" => $_lang['bounty_hosting'], "work_comment" => $_lang['work_reply'], "work_hand" => $_lang['i_want_submit_work'], "task_comment" => $_lang['task_comment'], "task_complaint" => $_lang['task_complaint'], "task_report" => $_lang['task_report'], "work_report" => $_lang['work_report'], "work_choose" => $_lang['task_choose_work'], "work_complaint" => $_lang['work_complaint'], "task_vote" => $_lang['task_vote'], "work_vote" => $_lang['work_vote'], "work_mark" => $_lang['work_mark'], "task_rights" => $_lang['task_rights'], "work_rights" => $_lang['work_rights'] );
	}
	/**
	 * 
	 * 获评到期
	 * @param $days 互评的最大期限时间
	 */
	public static function hp_timeout($days = 7) {
		$mark_list = db_factory::query ( sprintf ( "select * from %switkey_mark where mark_status=0 and mark_time<'%d'-'%d'", TABLEPRE, time (), 7 * 24 * 3600 ) );
		if (is_array ( $mark_list )) {
			foreach ( $mark_list as $v ) {
				keke_user_mark_class::exec_mark_process ( $v [mark_id], '', 1 );
			}
		}
	}
	/**
	 * 
	 * 更新置顶和加急的结束时间
	 * @param string $payitme_time ------数据库里面存储的时间戳
	 * @param string $top_time  -------- 增加的置顶时间戳
	 * @param string $urgent  -------- 增加的加急时间戳
	 * @return serialize
	 */
	public static function get_payitem($payitem_time, $top_time = false, $urgent_time = false) {
		
		$payite_arr = unserialize ( $payitem_time );
		if ($top_time) {
			$payite_arr [top] > time () and $payite_arr [top] = $payite_arr [top] + $top_time or $payite_arr [top] = time () + $top_time;
		}
		if ($urgent_time) {
			$payite_arr [urgent] > time () and $payite_arr [urgent] = $payite_arr [urgent] + $urgent_time or $payite_arr [urgent] = time () + $urgent_time;
		}
		$new_payitme_time = serialize ( $payite_arr );
		return $new_payitme_time;
	}
	
	/**
	 * 任务浏览记录
	 * @param int $task_id
	 * @param string $task_cash
	 * @param string $task_title
	 */
	function browing_history($task_id, $task_cash, $task_title) {
		$histroy = array ();
		if(	$_COOKIE ['task_browing_history_' . $this->_uid] ){
		 
		if(get_magic_quotes_gpc ()){
		   $_COOKIE ['task_browing_history_' . $this->_uid] =  kekezu::k_stripslashes(	$_COOKIE ['task_browing_history_' . $this->_uid]);
		} 
	    $temp = unserialize($_COOKIE ['task_browing_history_' . $this->_uid]);
	  
	}else{

		$temp = array ();
	}
		
		
		$len = sizeof ( $temp );
		if (! $temp [$task_id]) {
			$arr = array ($task_id, $task_cash, $task_title );
			$len >= 5 and array_pop ( $temp );
			array_unshift ( $temp, $arr );
		}
		foreach ( $temp as $v ) {
			$histroy [$v [0]] = $v;
		}
		setcookie ( 'task_browing_history_' . $this->_uid, serialize ( $histroy ),NULL, COOKIE_PATH, COOKIE_DOMAIN,NULL,TRUE  );
		return $histroy;
	}
	/**
	 * 是否有互评过
	 * @param $obj_ids 
	 */
	function has_mark($obj_ids){
		global $uid;
		$data = array();
		if($uid&&$obj_ids){
			$data=kekezu::get_table_data("mark_id,obj_id,mark_status,mark_count","witkey_mark","mark_status>0 and by_uid='$uid' and origin_id='$this->_task_id' and obj_id in ($obj_ids)","","","","obj_id",null);
		}
		return $data;
	}
	/**
	 * 显示任务的增值项
	 */
	function show_payitem() {
		global $_K;
		$str = '';
		$item_config = keke_payitem_class::get_payitem_config ( null, null, null, 'item_id' );
		
		$item_arr = array_filter(explode ( ",", $this->_task_info ['pay_item'] ));
		$time_arr = unserialize ( $this->_task_info ['payitem_time'] );
		if (! empty ( $item_arr ) && is_array ( $item_arr )) {
			foreach ( $item_arr as $v ) {
				if ($item_config [$v] ['item_code'] == 'top') {
					time () < intval ( $time_arr [top] )&&$item_config [$v] ['small_pic'] and $str .= "<img src='" . $_K ['siteurl'] . '/' . $item_config [$v] ['small_pic'] . "'>";
				
				} elseif ($item_config [$v] ['item_code'] == 'urgent') {
					time () < intval ( $time_arr [urgent] )&&$item_config [$v] ['small_pic']  and $str .= "<img src='" . $_K ['siteurl'] . '/' . $item_config [$v] ['small_pic'] . "'>";
				} else {
					$item_config [$v] ['small_pic'] and $str .= "<img src='" . $_K ['siteurl'] . '/' . $item_config [$v] ['small_pic'] . "'>";
				}
			}
		}
		
		return $str;
	}
	/**
	 * 雇主评价
	 */
	public function em_mark(){
		global $_lang;
		$aid	    = keke_user_mark_class::get_user_aid ($this->_guid,1, null, '1' );
		$str        = '';
		foreach($aid as $k=>$v){
			$str .= '<p><span>'.$v['aid_name'];
			$str .='<em class="cc00">'.$v['avg'].$_lang['fen'].'</em></span>';
			$str .= keke_user_mark_class::gen_star2($v['avg']);
			$str .= '</p></span>';
		}
		return $str;
	}
	/**
	 * 获取稿件图片
	 */
	public function work_pic($file_ids){
		global $_K;
		$exts = array('gif','jpg','jpeg','png');
		$info = db_factory::query(' select file_name,save_name from '.TABLEPRE.'witkey_file where file_id in ('.$file_ids.')');
		$pic  = array();
		foreach($info as $v){
			$t = explode('.',$v['file_name']);
			$ext = strtolower($t[sizeof($t)-1]);
			if(in_array($ext,$exts)){
				$pic[] = $_K['siteurl'].'/'.$v['save_name'];
			}
		}
		return implode(',',$pic);
	}
        /**
         * 通知联盟任务结束
         * @param $indetify 标识  1：成功  -1：失败
         * @param $bid_uid 中标威客  null/int/array
         */
        public function union_task_close($indetify,$bid_uid=null){
       		if($this->_task_info['task_union']&&$this->_task_info ['task_union']!=2&&in_array($indetify,array(1,-1))) {
				$u = new keke_union_class ( $this->_task_id );
       			$resp = array();
				$resp['r_task_id'] = $u->_r_task_id;
				$resp['indetify']  = $indetify;
				if($indetify==1){
					$resp['bid_uid'] = implode(',',(array)$bid_uid);
				}
				$u->task_close ($resp);
			}
        }
        /**
         * @return 返回单人悬赏任务状态
         */
        public  static function show_task_status() {
        	global $_lang;
        	return array ("0" => $_lang ['task_no_pay'], "1" => $_lang ['task_wait_audit'], "2" => $_lang ['task_vote_choose'], "3" => $_lang ['task_choose_work'], "4" => $_lang ['task_vote'], "5" => $_lang ['task_gs'], "6" =>$_lang['task_jfing'], "7" => $_lang ['freeze'], "8" => $_lang ['task_over'], "9" => $_lang ['fail'], "10" => $_lang ['task_audit_fail'], "11" => $_lang ['arbitrate'], '13' => $_lang ['agreement_frozen'] );
        }
}