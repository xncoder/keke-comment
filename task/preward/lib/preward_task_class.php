<?php
/**
 * 计件悬赏业务类
 * @method init 任务信息初始化
 * =>任务状态数组信息
 * =>任务基本需求
 * check_if_bided        检测是否中标			 
 * 
 * get_task_stage_desc	        获取任务阶段描述
 * get_task_timedesc 	        获取任务时间描述
 * get_task_work		        获取任务指定状态的稿件信息
 * get_work_info      	        获取任务稿件信息 *
 
 * set_work_status   			 稿件状态变更
 
 * dispose_witkey_prom   		 威客推广结算
 * dispose_employer_prom  		 雇主推广结算
 * dispose_task		   		 任务金额结算
 * dispose_task_return    		 任务金额返还
 *

 *
 *时间类 
 * time_task_end      		     任务结束
 *
 * process_can 	    	                当前操作判断
 * work_hand  		      	      任务交稿
 * work_choose 	      	                任务选稿
 */
keke_lang_class::load_lang_class ( 'preward_task_class' );
class preward_task_class extends keke_task_class {
	//任务状态数组
	public $_task_status_arr; 
	//稿件状态数组
	public $_work_status_arr; 
	
   //延期规则
	public $_delay_rule; 
	

	protected $_inited = false;
	
	public static function get_instance($task_info) {
		static $obj = null;
		if ($obj == null) {
			$obj = new preward_task_class ( $task_info );
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
			$this->task_requirement_init ();
			$this->delay_rule_init ();
			$this->wiki_priv_init ();
			$this->mark_init ();
		}
		$this->_inited = true;
	
	}
	
	/**
	 * 互评统计
	 */
	public function mark_init() {
		$m = $this->get_mark_count_ext ();
		$t = $this->_task_info;
		$t ['mark'] ['all'] = intval ( $m [1] ['c'] + $m [2] ['c'] );
		$t ['mark'] ['master'] = intval ( $m [2] ['c'] );
		$t ['mark'] ['wiki'] = intval ( $m [1] ['c'] );
		$this->_task_info = $t;
	}
	
	/**
	 * 任务，稿件状态数组	 
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
		$arr = preward_priv_class::get_priv ( $this->_task_id, $this->_model_id, $this->_userinfo );
		$this->_priv = $this->user_priv_format ( $arr );
	}
	
	/**
	 * 任务基本需求
	 */
	public function task_requirement_init() {
	
	}
	/**
	 * 任务状态说明
	 */
	public function get_task_timedesc() {
		global $_lang;
		$status_arr = $this->_task_status_arr;
		$task_status = $this->_task_status;
		$task_info = $this->_task_info;
		$time_desc = array ();
		switch ($task_status) {
			case "0":
				//未付款
				//追加描述
				$time_desc ['ext_desc'] = $_lang['task_nopay_can_not_look']; 
				break;
			case "1": 
				 //待审核
				 //追加描述
				$time_desc ['ext_desc'] = $_lang['wait_patient_to_audit']; 
				break;
			case '2' :
				 //投稿中
				//时间状态描述
				$time_desc ['time_desc'] = $_lang ['from_hand_work_deadline']; 
				//当前状态结束时间
				$time_desc ['time'] = $task_info ['sub_time']; 
				//$time_desc ['ext_desc'] = $_lang ['task_working_and_can_hand_work'];
				//追加描述
				$time_desc ['ext_desc'] = $_lang['hand_work_and_reward_trust']; 
				if ($this->_task_config ['open_select'] == 'open') {
					$time_desc ['g_action'] = $_lang ['present_state_employer_can_choose'];
				}
				break;
			case '3' : 
				//选稿中
				//时间状态描述
				$time_desc ['time_desc'] = $_lang ['from_choose_deadline']; 
				$time_desc ['time'] = $task_info ['end_time'];
				//$time_desc ['ext_desc'] = $_lang ['task_choosing_and_wait_employer_choose'];
				$time_desc ['ext_desc'] = $_lang['work_choosing_and_wait_employer_choose']; 
				break;
			case "7" : 
				//冻结中
				
				$time_desc ['ext_desc'] =$_lang['task_frozen_can_not_operate'];
				break;
			case "8" : 
				//结束
				//$time_desc ['ext_desc'] = $_lang ['task_haved_complete'];
				 //追加描述
				$time_desc ['ext_desc'] = $_lang['task_over_congra_witkey']; 
				//追加描述
				break;
			case "9" : 
				//失败
				//$time_desc ['ext_desc'] = $_lang ['task_timeout_and_no_works_fail'];

				//追加描述
				$time_desc ['ext_desc'] = $_lang['pity_task_fail']; 
				//追加描述
				break;
			case "10":
				//未通过审核
				$time_desc ['ext_desc'] = $_lang['fail_audit_please_repub'];
				 //追加描述
				break;
			case "11" : 
				//仲裁
				//$time_desc ['ext_desc'] = $_lang ['task_arbitrating'];
				$time_desc ['ext_desc'] = $_lang['wait_for_task_arbitrate'];
		}
		return $time_desc;
	}
	
	/**
	 * 获取任务稿件信息  支持分页，用户前端稿件列表
	 * @param array $w 前端查询条件数组
	 * ['work_status'=>稿件状态	
	 * 'user_type'=>用户类型 --有值表示自己
	 * ......]
	 * @param string $order 排列条件
	 * @param array $p 前端传递的分页初始信息数组
	 * ['page'=>当前页面
	 * 'page_size'=>页面条数
	 * 'url'=>分页链接
	 * 'anchor'=>分页锚点]
	 * @return array work_list
	 */
	public function get_work_info($w = array(), $order = null, $p = array()) {
		global $kekezu, $_K, $uid;
		$work_arr = array ();
		$sql = " select a.*,b.seller_credit,b.seller_good_num,b.seller_total_num,b.seller_level from " . TABLEPRE . "witkey_task_work a left join " . TABLEPRE . "witkey_space b on a.uid=b.uid";
		
		$count_sql = " select count(a.work_id) from " . TABLEPRE . "witkey_task_work a left join " . TABLEPRE . "witkey_space b on a.uid=b.uid";
		$where = " where a.task_id = '$this->_task_id' ";
		
		if (! empty ( $w )) {
			$w ['work_id'] and $where .= " and a.work_id='" . $w ['work_id'] . "'";
			$w ['user_type'] == 'my' and $where .= " and a.uid = '$this->_uid'";
			isset ( $w ['work_status'] ) and $where .= " and a.work_status = '" . intval ( $w ['work_status'] ) . "'";
		/**待添加**/
		}
		$where .= " order by (CASE WHEN  a.work_status!=0 THEN 100 ELSE 0 END) desc,";
		if (! empty ( $order )) {
			$where .= $order;
		} else {
			$where .= ",work_time asc ";
		}
		
		if (! empty ( $p )) {
			$page_obj = $kekezu->_page_obj;
			$page_obj->setAjax ( 1 );
			$page_obj->setAjaxDom ( "gj_summery" );
			$count = intval ( db_factory::get_count ( $count_sql . $where ) );
			$pages = $page_obj->getPages ( $count, $p ['page_size'], $p ['page'], $p ['url'], $p ['anchor'] );
			$pages ['count'] = $count;
			$where .= $pages ['where'];
		}
		$work_info = db_factory::query ( $sql . $where );
		$work_info = kekezu::get_arr_by_key ( $work_info, 'work_id' );
		$work_arr ['work_info'] = $work_info;
		$work_arr ['pages'] = $pages;
		$work_ids = implode ( ',', array_keys ( $work_info ) );
		$work_arr ['mark'] = $this->has_mark ( $work_ids );
		/*更新查看状态*/
		$work_ids && $uid == $this->_task_info ['uid'] and db_factory::execute ( 'update ' . TABLEPRE . 'witkey_task_work set is_view=1 where work_id in (' . $work_ids . ') and is_view=0' );
		
		return $work_arr;
	}
	
	/**
	 * 返回任务稿件数信息	 
	 * 'max'=>'可交稿件最大数'
	 */
	public function get_work_count($where) {
		//总共可交稿件数
		if ($where == 'max') {
			$work_count = intval ( $this->_task_info ['work_count'] );
			$count = $work_count * (1 + intval ( $this->_task_config ['work_percent'] ) / 100);
		} else {
			$count = db_factory::get_count ( sprintf ( "select count(work_id) from %switkey_task_work where %s and task_id='%d'", TABLEPRE, $where, $this->_task_id ) );
		}
		return intval ( $count );
	}
	/**
	 * 判断所交稿件数(合格稿件数)是否达标	 
	 *@param $type 'hand'=>'所交稿件' 'hege'=>合格稿件
	 */
	public function check_work_if_standard($type) {
		//所需稿件数
		$work_count = intval ( $this->_task_info ['work_count'] ); 
		//合格稿件数		
		if ($type == 'hand') {
			//总共可交稿件数
			$totle_count = $this->get_work_count ( "max" );
			//已交稿件数
			$hand_count = $this->get_work_count ( "work_status in(0,6)" );
			if ($hand_count < $totle_count) {
				return true;
				 //可交
			} else {
				return false;
			}
		} elseif ($type == 'hege') {
			$hege_count = $this->get_work_count ( "work_status=6" );
			if ($work_count > $hege_count) {
				return true;
			} else {
				return false;
			}
		}
	}
	/**
	 * 任务交稿
	 * @param string $work_desc 交稿描述
	 * @param int    $hidework 稿件隐藏  1=>隐藏,2=>不隐藏  默认为不隐藏
	 * @param string $file_ids 稿件附件编号串  eg:1,2,3,4,5
	 * @param string $url    操作提示链接  具体参见 kekezu::keke_show_msg
	 * @param string $output 消息输出方式 具体参见 kekezu::keke_show_msg
	 * @see keke_task_class::work_hand()
	 */
	public function work_hand($work_desc, $file_ids, $hidework = '2', $url = '', $output = 'normal') {
		global $_lang;
		global $_K;
		if ($this->check_if_can_hand ( $url, $output )) {
			if ($this->check_work_if_standard ( 'hand' )) {
				$work_obj = new Keke_witkey_task_work_class ();
				$work_obj->setHide_work ( $hidework );
				$work_obj->setTask_id ( $this->_task_id );
				$work_obj->setUid ( $this->_uid );
				$work_obj->setUsername ( $this->_username );
				CHARSET == 'gbk' and $work_desc = kekezu::utftogbk ( $work_desc );
				$work_obj->setWork_desc ( $work_desc );
				$work_obj->setWork_status ( 0 );
				$work_obj->setWork_title ( $this->_task_title );
				$work_obj->setWork_time ( time () );
				
				if ($file_ids) {
					$file_arr = array_unique ( array_filter ( explode ( ',', $file_ids ) ) );
					$f_ids = implode ( ',', $file_arr );
					$work_obj->setWork_file ( $f_ids );
					$work_obj->setWork_pic($this->work_pic($f_ids));
				}
				$work_id = $work_obj->create_keke_witkey_task_work ();
				$hidework == '1' and keke_payitem_class::payitem_cost ( "workhide", '1', 'work', 'spend', $work_id, $this->_task_id );
				if ($work_id) {
					//更新附件表信息
					$f_ids and db_factory::execute ( sprintf ( "update %switkey_file set work_id='%d',task_title='%s',obj_id='%d' where file_id in (%s)", TABLEPRE, $work_id, $this->_task_title, $work_id, $f_ids ) );
					//更新任务交稿数
					$this->plus_work_num ();
					//更新用户交稿数
					$this->plus_take_num ();
					
					//发站内信
					$notice_url = "<a href=\"$_K[siteurl]/index.php?do=task&task_id={$this->_task_id}\">$this->_task_title</a>";
					$g_notice = array ($_lang ['user'] => $this->_username, $_lang ['call'] => $_lang ['you'], $_lang ['task_title'] => $notice_url ); 
					

					$this->notify_user ( 'task_hand', $_lang ['task_hand'], $g_notice, '2', $this->_guid );
					
					kekezu::keke_show_msg ( $url, $_lang ['congratulate_you_hand_work_success'], '', $output );
				} else {
					kekezu::keke_show_msg ( $url, $_lang ['hand_work_fail_and_operate_agian'], 'error', $output );
				}
			} else {
				kekezu::keke_show_msg ( $url, $_lang ['hand_work_fail_for_the_work_full'], 'error', $output );
			}
		
		}
	
	}
	/**
	 * 任务选稿
	 * @param string $url    消息提示链接  具体参见 kekezu::keke_show_msg
	 * @param string $output 消息输出方式 具体参见 kekezu::keke_show_msg
	 * @see keke_task_class::work_choose()
	 */
	public function work_choose($work_id, $to_status, $url = '', $output = 'normal', $trust_response = false) {
		global $_K, $kekezu;
		global $_lang;
		kekezu::check_login ( $_K ['siteurl'] . '/index.php?do=login', $output );
		//是否可以选稿
		$this->check_if_operated ( $work_id, $to_status, $url, $output );
		$work_status_arr = $this->_work_status_arr;
		//test联盟任务改变状态
		if ($this->set_work_status ( $work_id, $to_status )) {
			$title_url = "<a href =" . $_K ['siteurl'] . "/index.php?do=task&task_id=" . $this->_task_id . " target=\"_blank\">" . $this->_task_title . "</a>";
			$work_info = $this->get_task_work ( $work_id );
			if ($to_status == 6) {
				//威客中标一些操作
				$this->work_choosed ( $work_info, $title_url );
				//判断当前稿件否是最后一个稿件，如果是改变任务状态						
				if (! $this->check_work_if_standard ( 'hege' )) {
					if ($this->set_task_status ( 8 )) {
						/** 雇主上线推广结算*/
						$kekezu->init_prom ();
						$kekezu->_prom_obj->dispose_prom_event ( "bid_task", $work_info['uid'], $this->_task_id );
						/**
						 * 通知联盟
						 */
						if ($this->_task_info ['task_union']>1) {
							$bid_uid = array();
							$ids = db_factory::query('select uid from '.TABLEPRE.'witkey_task_work where work_status=6 and task_id='.$this->_task_id);
							foreach($ids as $v){
								$bid_uid[] = $v['uid'];
							}
							$this->union_task_close(1,$bid_uid);//通知联盟
						}
					}
				}
			} elseif ($to_status == 7) {
				$arr = array ($_lang ['username'] => $work_info ['username'], Conf::$msgTpl ['task_title'] => $this->_task_title, $_lang ['website_name'] => $_K ['sitename'], Conf::$msgTpl ['task_id'] => $this->_task_id );
				keke_msg_class::notify_user ( $work_info ['uid'], $work_info ['username'], 'task_unbid', $_lang ['work_fail1'], $arr );
			}
			kekezu::keke_show_msg ( $url, $_lang ['work'] . $work_status_arr [$to_status] . $_lang ['set_success'], '', $output );
		} else {
			kekezu::keke_show_msg ( $url, $_lang ['work'] . $work_status_arr [$to_status] . $_lang ['set_fail'], 'error', $output );
		}
	
	}
	
	/**
	 * 威客中标后打钱操作	 
	 * @param array $work_info
	 */
	public function work_choosed($work_info, $title_url) {
		global $_K, $kekezu;
		global $_lang;
		$kekezu->init_prom ();
		//给威客打钱
		$single_cash = floatval ( $this->_task_info ['single_cash'] );
		$profit_cash = $single_cash * intval ( $this->_task_info ['profit_rate'] ) / 100;
		$real_cash = $single_cash * (1 - intval ( $this->_task_info ['profit_rate'] ) / 100);
		$data = array (':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
		keke_finance_class::init_mem ( 'task_bid', $data );
		keke_finance_class::cash_in ( $work_info ['uid'], $real_cash, 0, 'task_bid', '', 'task', $this->_task_id, $profit_cash );
		//通知威客				
		$url = '<a href ="' . $_K ['siteurl'] . '/index.php?do=task&task_id=' . $this->_task_id . '">' . $this->_task_title . '</a>';
		$status_arr = self::get_work_status();
		$v = array ($_lang ['work_status'] => $status_arr [6],$_lang ['username'] => $work_info ['username'], $_lang ['website_name'] => $kekezu->_sys_config ['website_name'], $_lang ['task_id'] => $this->_task_id, $_lang ['task_title'] => $url, $_lang ['bid_cash'] => $real_cash );
		$this->notify_user ( "task_bid", $_lang ['work_bid'], $v, '1', $work_info ['uid'] );
		
		/* $arr = array($_lang['username']=>$work_info ['username'],$_lang["model_name"]=>$this->_model_code,$_lang["task_id"]=>$this->_task_id,$_lang["task_title"]=>$this->_task_title,$_lang["work_title"]=>$work_info['work_title'],$_lang["cash"]=>$real_cash);
		keke_msg_class::notify_user( $work_info['uid'],  $work_info ['username'], 'task_id', $_lang['work_bid'],$arr); */
		
		//写入feed表 
		$feed_arr = array ("feed_username" => array ("content" => $work_info ['username'], "url" => "index.php?do=space&member_id=$work_info[uid]" ), "action" => array ("content" => $_lang ['success_bid_haved'], "url" => "" ), "event" => array ("content" => "$this->_task_title", "url" => "index.php?do=task&task_id=$this->_task_id", 'cash' => $real_cash ) );
		kekezu::save_feed ( $feed_arr, $work_info ['uid'], $work_info ['username'], 'work_accept', $this->_task_id );
		
		//更改威额稿件被采纳次数
		$this->plus_accepted_num ( $work_info ['uid'] );
		$this->plus_mark_num (); 
		//更新互评次数;
		/** 威客上线推广产生、结算*/
		if ($kekezu->_prom_obj->is_meet_requirement ( "bid_task", $this->_task_id )) {
			$kekezu->_prom_obj->create_prom_event ( "bid_task", $work_info ['uid'], $this->_task_id, $single_cash );
			$kekezu->_prom_obj->dispose_prom_event ( "bid_task", $work_info ['uid'], $work_info ['work_id'] );
		}
		/**威客对雇主记录**/
		keke_user_mark_class::create_mark_log ( $this->_model_code, '1', $work_info ['uid'], $this->_guid, $work_info ['work_id'], $this->_task_info ['single_cash'], $this->_task_id, $work_info ['username'], $this->_gusername );
		/**雇主对威客记录**/
		keke_user_mark_class::create_mark_log ( $this->_model_code, '2', $this->_guid, $work_info ['uid'], $work_info ['work_id'], $real_cash, $this->_task_id, $this->_gusername, $work_info ['username'] );
	}
	
	/**
	 * 取消稿件中标(待定)
	 */
	/* public function work_cancel($work_id, $url, $output) {
		global $_K;
		global $_lang;
		$this->_task_status == '8' and kekezu::keke_show_msg ( $url, $_lang['present_task_status_not_cancel_bid'], 'error', $output );
		$work_info = $this->get_task_work ( $work_id );
		$work_info['work_status'] != '6' and kekezu::keke_show_msg ( $url, $_lang['present_work_not_bid_and_not_cancel_bid'], 'error', $output );
		$this->_userinfo ['group_id'] != 7 and kekezu::keke_show_msg ( $url, $_lang['you_not_rights_operate_to_the_work'], 'error', $output );
		//改变稿件状态
		if ($this->set_work_status ( $work_id, 0 )) {
			//扣除中标者所得金额
			$cash = floatval ( $this->_task_info['single_cash'] ) * floatval ( $this->_task_info ['profit_rate'] );
			keke_finance_class::cash_out ( $work_info ['uid'], $cash, 0, 'sdfsd' );
			$task_url = "<a href=\"{$_K[siteurl]}/index.php?do=task&task_id=$this->_task_id\">$this->_task_title</a>";
			kekezu::notify_user ( $_lang['cancel_bid_notice'], $_lang['you_in_task'] . $task_url . $_lang['de_hand_work_jh'] . $work_id . $_lang['by_site_kf_cancel_bid'], $work_info['uid'] );
			kekezu::keke_show_msg ( $url, $_lang['work_cancel_bid_set_success'], '', $output );
		} else {
			kekezu::keke_show_msg ( $url, $_lang['work_cancel_bid_set_fail'], 'error', $output );
		}
	} */
	
	/**
	 * 判断是否可以选稿
	 * 任务是否处于选稿状态，合格稿件是否达到所需要稿件数,当前稿件是否可以被操作 
	 */
	public function check_if_operated($work_id, $to_status, $url = '', $output = 'normal') {
		global $_lang;
		if ($this->check_if_can_choose ( $url, $output )) {
			$work_status = db_factory::get_count ( sprintf ( "select work_status from %switkey_task_work where work_id='%d'", TABLEPRE, $work_id ) );
			switch (intval ( $work_status )) {
				case 0 :
					if ($to_status == 6) {
						if ($this->check_work_if_standard ( 'hege' )) {
							return true;
						} else {
							kekezu::keke_show_msg ( $url, $_lang ['task_hg_work_full_and_not_operate_bid_work'], 'error', $output );
						}
					} else {
						return true;
					}
					break;
				case 6 :
					kekezu::keke_show_msg ( $url, $_lang ['task_bid_work_full_and_not_operate_choose_work'], 'error', $output );
					break;
				case 7 :
					kekezu::keke_show_msg ( $url, $_lang ['task_not_recept_work_full_and_not_operate_choose_work'], 'error', $output );
					break;
				case 8 :
					kekezu::keke_show_msg ( $url, $_lang ['task_not_operate_work_and_not_operate_choose_work'], 'error', $output );
					break;
			}
		} else {
			kekezu::keke_show_msg ( $url, $_lang ['now_status_can_not_choose'], "error", $output );
		}
	}
	
	/**
	 * 操作判断
	 * //注意用户权限的判断   
	 * 雇主不受威客权限的限制、、拥有威客的所有权限
	 * 威客严格受到条件约束
	 * 威客限制：查看任务       
	 * 留言        
	 * 举报	
	 * @see keke_task_class::process_can()
	 */
	public function process_can() {
		//威客权限数组
		$wiki_priv = $this->_priv; 		
		$process_arr = array ();
		$status = intval ( $this->_task_status );
		$task_info = $this->_task_info;
		$config = $this->_task_config;
		$g_uid = $this->_guid;
		$uid = $this->_uid;
		$user_info = $this->_userinfo;
		switch ($status) {
			case '2' :
				 //交稿期
				if ($uid == $g_uid) {
					//工具
					$process_arr ['tools'] = true; 
					//补充需求
					$process_arr ['reqedit'] = true; 
					 //延期加价
					sizeof ( $this->_delay_rule ) > 0 and $process_arr ['delay'] = true;
					if ($config ['open_select'] == 'open' && $this->check_work_if_standard ( 'hege' )) {
						$process_arr ['work_choose'] = true; 
						//开启投稿中选稿
					}
					$process_arr ['work_comment'] = true;
					 //稿件回复
				} else {
					$process_arr ['work_hand'] = true; 
					//交稿
					$process_arr ['task_comment'] = true; 
					//任务评论
					$process_arr ['task_report'] = true;
					 //任务举报
				}
				
				$process_arr ['work_report'] = true; 
				//稿件举报 
				$process_arr ['work_cancel'] = true;
				 //取消稿件中标
				break;
			case '3' : 
				//选稿期
				if ($uid == $g_uid) {
					if ($this->check_work_if_standard ( 'hege' )) {
						$process_arr ['work_choose'] = true;
					}
					$process_arr ['work_comment'] = true;
					 //稿件留言
				} else {
					$process_arr ['task_comment'] = true;
					$process_arr ['task_report'] = true;
				}
				$process_arr ['work_report'] = true;
				$process_arr ['work_cancel'] = true;
				 //取消稿件中标
				break;
			case '8' :
				 //任务结束
				if ($uid == $g_uid) {
					$process_arr ['work_comment'] = true; 
					//稿件留言
				

				} else {
					$process_arr ['task_comment'] = true;
				
				}
				break;
		
		}
		$process_arr ['work_mark'] = true; 
		//稿件评论
		$process_arr ['task_mark'] = true; 
		//任务评价
		$uid != $g_uid and $process_arr ['task_complaint'] = true; 
		//任务投诉
		$process_arr ['work_complaint'] = true; 
		//稿件投诉
		if ($user_info ['group_id']) {
			 //管理员
			
			switch ($status) {
				case 1 : 
					//审核
					$process_arr ['task_audit'] = true;
					break;
				case 2 :
					 //推荐
					
					$task_info['is_top'] or $process_arr ['task_recommend'] = true;
					$process_arr ['task_freeze'] = true;
					break;
				default :
					if ($status > 1 && $status < 8) {
						$process_arr ['task_freeze'] = true;
					}
			}
		
		}
		$this->_process_can = $process_arr;
		return $process_arr;
	}
	
	/**
	 * 任务金额返还
	 */
	public function dispose_task_return($action) {
		$uid = $this->_uid;
		$prom_obj = $kekezu->_prom_obj;
		$refund_type = $this->_task_config ['defeated']; 
		//退款方式
		$task_cash = floatval ( $this->_task_info ['task_cash'] );
		 //实际金额		
		$refund_rate = floatval ( $this->_task_info ['task_fail_rate'] ) / 100;
		 //任务失败返金抽成比例
		//合格稿件数
		$hege_count = $this->get_work_count ( "work_status=6" );
		$hege_count and $use_cash = floatval ( $this->_task_info ['single_cash'] ) * $hege_count;
		$work_count = intval ( $this->_task_info ['work_count'] ); 
		//所需稿件		
		switch ($refund_type) {
			case '1' : 
				//退现金
				$credit = floatval ( $this->_task_info ['credit_cost'] );
				 //发布任务所花费代金券
				$cash = floatval ( $this->_task_info ['cash_cost'] ); 
				//发布任务所现金				
				if ($hege_count > 0) { 
					//合格稿件未达标退款
					$sy = $credit - $use_cash;
					if ($sy >= 0) {
						$refund_credit = $sy;
						$refund_cash = $cash;
					} else {
						$refund_credit = $credit;
						$refund_cash = $cash - abs ( $sy );
					}
				} else { 
					//未选稿退款
					$refund_cash = $cash;
					$refund_credit = $credit;
				}
				break;
			case '2' : 
				//退金币
				$refund_cash = 0;
				if ($hege_count) {
					$refund_credit = $task_cash - $use_cash;
				} else {
					$refund_credit = $task_cash;
				}
				break;
		}
		$ref_cash = $refund_cash * (1 - $refund_rate);
		$ref_credit = $refund_credit * (1 - $refund_rate);
		$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
		keke_finance_class::init_mem ( $action, $data );
		keke_finance_class::cash_in ( $this->_guid, $ref_cash, $ref_credit, $action, '', 'task', $this->_task_id, ($refund_cash + $refund_credit) * $refund_rate );
		return array ('refund_cash' => $refund_cash, 'refund_credit' => $refund_credit );
	}
	
	/**
	 * 交稿过期处理(时间类)
	 */
	public function task_jg_timeout() {
		global $_K, $kekezu;
		global $_lang;
		$prom_obj = $this->_prom_obj;
		if ($this->_task_status == '2') {
			if (time () > intval ( $this->_task_info ['sub_time'] )) {
				$task_url = "<a href=\"$_K[siteurl]/index.php?do=task&task_id=$this->_task_id\">$this->_task_title</a>";
				if (intval ( $this->_task_info ['work_num'] ) > 0) { 
					//如果有稿件
					if ($this->set_task_status ( 3 )) {
						//发消息 
						$arr = array ($_lang ['username'] => $this->_gusername, $_lang ["model_name"] => $this->_model_name, Conf::$msgTpl ["task_id"] => $this->_task_id, Conf::$msgTpl ["task_title"] => $this->_task_title, $_lang ["tb"] => $_lang ['hand_work'], "next" => $_lang ['choose_work'] );
						keke_msg_class::notify_user ( $this->_guid, $this->_gusername, 'timeout', '任务选稿', $arr );
					}
				} else {
					if ($this->set_task_status ( 9 )) {
						 //任务失败
						//返金
						$refund = $this->dispose_task_return ( 'task_fail' );
						/** 终止雇主的此次推广事件*/
						$kekezu->init_prom ();
						$p_event = $kekezu->_prom_obj->get_prom_event ( $this->_task_id, $this->_guid, "pub_task" );
						$kekezu->_prom_obj->set_prom_event_status ( $p_event ['parent_uid'], $this->_gusername, $p_event ['event_id'], '3' );
						$this->union_task_close(-1);//通知联盟
						//发消息
						$refund ['refund_cash'] || $refund ['refund_credit'] and $send_str = $_lang ['sys_haved_return'];
						$refund ['refund_cash'] and $send_str .= $_lang ['task_cach_'] . $refund ['refund_cash'] . $_lang ['yuan'];
						$refund ['refund_credit'] and $send_str .= $_lang ['task_credit_'] . $refund ['refund_credit'];
					}
				}
			}
		}
	}
	
	/**
	 * 选稿期任务结束（时间类）	
	 */
	public function task_xg_timeout() {
		global $_K, $kekezu;
		global $_lang;
		if ($this->_task_status == '3' && time () > intval ( $this->_task_info ['end_time'] )) {
			if ($this->set_task_status ( 8 )) { 
				//任务结束
				$kekezu->init_prom ();
				//是否有稿件中标
				$hege_count = $this->get_work_count ( "work_status=6" );
				if (intval ( $hege_count ) == 0) {
					//是否开启自动选稿
					if (intval ( $this->_task_config ['is_auto_adjourn'] ) == 1) {
						//自动选稿的稿件数
						$auto_num = intval ( $this->_task_config ['adjourn_num'] ); 
						$auto_num > intval ( $this->_task_info ['work_num'] ) and $auto_num = intval ( $this->_task_info ['work_num'] );
						$auto_num > intval ( $this->_task_info ['work_count'] ) and $auto_num = intval ( $this->_task_info ['work_count'] );
						$work_list = db_factory::query ( sprintf ( "select * from %switkey_task_work where task_id='%d' and work_status=0 order by work_time asc limit 0,%d", TABLEPRE, $this->_task_id, $auto_num ) );
						if ($work_list) {
							foreach ( $work_list as $v ) {
								$this->set_work_status ( $v ['work_id'], 6 );
								$title_url = "<a href=\"$_K[siteurl]/index.php?do=task&task_id=$this->_task_id\">$this->_task_title</a>";
								$this->work_choosed ( $v, $title_url );
							}
						}
					}
				}
				//返金
				$refund = $this->dispose_task_return ( 'task_remain_return' );
				//发消息
				$refund ['refund_cash'] || $refund ['refund_credit'] and $send_str = $_lang ['sys_haved_return'];
				$refund ['refund_cash'] and $send_str .= $_lang ['task_cach_'] . $refund ['refund_cash'] . $_lang ['yuan'];
				$refund ['refund_credit'] and $send_str .= $_lang ['task_credit_'] . $refund ['refund_credit'];
				/** 结算雇主推广事件*/
				$kekezu->_prom_obj->dispose_prom_event ( "pub_task", $this->_guid, $this->_task_id );
			}
		}
	}
	
	/**
	 * @return 返回计件悬赏任务状态
	 */
	
	public static function get_task_status() {
		global $_lang;
		return array ("0" => $_lang ['task_no_pay'], "1" => $_lang ['task_wait_audit'], "2" => $_lang ['task_vote_choose'], "3" => $_lang ['task_choose_work'], "7" => $_lang ['freeze'], "8" => $_lang ['task_over'], "9" => $_lang ['fail'], "10" => $_lang ['task_audit_fail']);
	}
	
	/**
	 * @return 返回计件悬赏稿件状态
	 * 
	 */
	public static function get_work_status() {
		global $_lang;
		return array ('6' => $_lang ['hg'], '7' => $_lang ['not_recept'], '8' => $_lang ['task_can_not_choose_bid'] );
	}
	/**
	 * 设置稿件状态
	 */
	public function set_work_status($work_id, $to_status) {
		return db_factory::execute ( sprintf ( "update %switkey_task_work set work_status='%d' where work_id='%d'", TABLEPRE, $to_status, $work_id ) );
	}
	/**
	 * 
	 * 订单处理
	 * @param int $order_id //订单id
	 */
	public function dispose_order($order_id) {
		global $kekezu, $_K;
		global $_lang;
		//后台配置
		$task_config = $this->_task_config;
		//任务信息
		$task_info = $this->_task_info; 
		$url = $_K ['siteurl'] . '/index.php?do=task&task_id=' . $this->_task_id;
		$task_status = $this->_task_status;
		$order_info = db_factory::get_one ( sprintf ( "select order_amount,order_status from %switkey_order where order_id='%d'", TABLEPRE, intval ( $order_id ) ) );
		$order_amount = $order_info ['order_amount'];
		if ($order_info ['order_status'] == 'ok') {
			$task_status == 1 && $notice = $_lang ['task_pay_success_and_wait_admin_audit'];
			$task_status == 2 && $notice = $_lang ['task_pay_success_and_task_pub_success'];
			return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $notice, $url, 'success' );
		} else {
			$data = array (':model_name' => $this->_model_name, ':task_id' => $this->_task_id, ':task_title' => $this->_task_title );
			keke_finance_class::init_mem ( 'pub_task', $data );
			//支付费用
			$res = keke_finance_class::cash_out ( $this->_task_info ['uid'], $order_amount, 'pub_task' ); 
			if ($res) { 
				//支付成功
				/** 雇主推广事件产生*/
				$kekezu->init_prom ();
				if ($kekezu->_prom_obj->is_meet_requirement ( "pub_task", $this->_task_id )) {
					$kekezu->_prom_obj->create_prom_event ( "pub_task", $this->_guid, $this->_task_id, $this->_task_info ['task_cash'] );
				} 
					keke_union_class::union_task_submit($this->_g_userinfo,$this->_task_id);//联盟
					//feed
				$feed_arr = array ("feed_username" => array ("content" => $task_info ['username'], "url" => "index.php?do=space&member_id={$task_info['uid']}" ), "action" => array ("content" => $_lang ['pub_task'], "url" => "" ), "event" => array ("content" => "{$task_info['task_title']}", "url" => "index.php?do=task&task_id={$task_info['task_id']}" ) );
				kekezu::save_feed ( $feed_arr, $task_info ['uid'], $task_info ['username'], 'pub_task', $task_info ['task_id'] );
				
				/**更新任务的现金金币消耗*/
				$consume = kekezu::get_cash_consume ( $task_info ['task_cash'] );
				db_factory::execute ( sprintf ( " update %switkey_task set cash_cost='%s',credit_cost='%s' where task_id='%d'", TABLEPRE, $consume ['cash'], $consume ['credit'], $this->_task_id ) );
				
				//更改订单状态到已付款状态
				db_factory::updatetable ( TABLEPRE . "witkey_order", array ("order_status" => "ok" ), array ("order_id" => "$order_id" ) );
				if ($order_amount < $task_config ['audit_cash']) {
					 //如果订单的金额比发布任务时配置的最小金额要小
					//状态更改为审核状态
					$this->set_task_status ( 1 ); 
					return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $_lang ['task_pay_success_and_wait_admin_audit'], $url, 'success' );
				} else {
					//状态更改为进行状态
					$this->set_task_status ( 2 ); 
					return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $_lang ['task_pay_success_and_task_pub_success'], $url, 'success' );
				}
			} else { 
				//支付失败
				//支付跳转链接
				$pay_url = $_K ['siteurl'] . "/index.php?do=pay&order_id=$order_id"; 
				return pay_return_fac_class::struct_response ( $_lang ['operate_notice'], $_lang ['task_pay_error_and_please_repay'], $pay_url, 'warning' );
			}
		}
	}
	
	/**
	 * 获取用户中心雇主当前操作
	 * @param $m_id 模型编号
	 * @param $t_id 任务编号
	 * @param $url  操作链接
	 */
	public static function master_opera($m_id, $t_id, $url,$t_cash) {
		global $uid, $_K, $do, $view, $_lang;
		$status = db_factory::get_count ( sprintf ( ' select task_status from %switkey_task where task_id=%d and uid=%d', TABLEPRE, $t_id, $uid ), 0, 'task_status', 600 );
		$order_info = db_factory::get_one(sprintf("select order_id from %switkey_order_detail where obj_id=%d",TABLEPRE,$t_id));				
		$site = $_K ['siteurl'] . '/';
		$button = array ();
		$button ['view'] = array ('href' => $site . 'index.php?do=task&task_id=' . $t_id, 'desc' => $_lang ['view'], 
'ico' => 'book' );		 
		$button ['onkey'] = array ('href' => $site . 'index.php?do=release&t_id=' . $t_id . '&model_id=' . $m_id . '&pub_mode=onekey', 'desc' => $_lang ['one_key_pub'],		
'ico' => 'book' );
	
		$button ['del'] = array ('href' => $site . $url . '&ac=del&task_id=' . $t_id, 
'desc' => $_lang ['delete'], 		
'click' => 'return del(this);', 
'ico' => 'trash' ); 
		
		switch ($status) {
			case 0 : 
				$button ['pay'] = array ('href' => $site . 'index.php?do=' . $do . '&view=' . $view . '&task_id=' . $t_id . '&model_id=' . $m_id . '&ac=pay', 'desc' => $_lang ['payment'], 
'click' => "return pay(this,$t_cash,$order_info[order_id]);", 				
'ico' => 'loop' );
				break;
			case 2 : 
			
				$button ['tool'] = array ('href' => $site . 'index.php?do=task&task_id=' . $t_id . '&view=tools', 'desc' => $_lang ['toolbox'], 
'ico' => 'trash' );
				
				$button ['addprice'] = array ('click' => "taskDelay('index.php?do=task&task_id=$t_id')", 'desc' => $_lang ['delay_makeup'],
'ico' => 'book', 'href' => 'javascript:void(0)' );
				break;
			case 3:
			
				$button ['view'] ['desc'] = $_lang ['choose_work'];
				$button ['view'] ['href'] = $site . 'index.php?do=task&task_id=' . $t_id . '&view=work';
				break;
			case 4 :
				
				$button ['view'] ['desc'] = $_lang ['vote'];
				
				$button ['view'] ['href'] = $site . 'index.php?do=task&task_id=' . $t_id . '&view=work';
				break;
		
		}
		if (! in_array ( $status, array (0, 8, 9, 10 ) )) {
			
			unset ( $button ['del'] );
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
	public static function wiki_opera($m_id, $t_id, $w_id, $url) {
		global $uid, $_K, $do, $view, $_lang;
		$status = db_factory::get_count ( sprintf ( ' select task_status from %switkey_task where task_id=%d', TABLEPRE, $t_id, $uid ), 0, 'task_status', 600 );
		$site = $_K ['siteurl'] . '/';
		$button = array ();
		$button ['view'] = array ('href' => $site . 'index.php?do=task&task_id=' . $t_id . '&view=work&ut=my&work_id=' . $w_id, 'desc' => $_lang ['view_work'], 
'ico' => 'book' );
		switch ($status) {
			case 2 :
				$button ['share'] = array ('href' => 'javascript:void(0);', 'desc' => $_lang ['share'], 
'click' => 'share(' . $t_id . ');', 'ico' => 'share' );
				break;
			
			case 8 :
			case 9 : 

				$button ['del'] = array ('href' => $site . $url . '&ac=del&work_id=' . $w_id, 
'desc' => $_lang ['delete'],				
'click' => 'return del(this);',				
'ico' => 'trash' ); 				
				break;
		}
		return $button;
	}
}