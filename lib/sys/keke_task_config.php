<?php
/**
 * 任务配置控制类
 * @author Chen
 * 
 */
keke_lang_class::load_lang_class('keke_task_config');
class keke_task_config {
	/**
	 * 获取任务时间规则
	 * @param int $model_id 模型ID
	 * @param int $cache_time 缓存时间
	 */
	public static function get_time_rule($model_id, $cache_time = null) {
		return kekezu::get_table_data ( "*", "witkey_task_time_rule", "model_id='$model_id'", "rule_cash", "", "", "", "", $cache_time );
	}
	/**
	 * 获取任务延期规则
	 * @param int $model_id 模型ID
	 * @param int $cache_time 缓存时间
	 */
	public static function get_delay_rule($model_id, $cache_time = null) {
		return kekezu::get_table_data ( "*", "witkey_task_delay_rule", "model_id='$model_id'", "defer_rate", "", "", "", "", $cache_time );
	}
	/**
	 * 设置任务时间规则
	 * @param int $model_id 模型ID
	 * @param array $timeOld 旧配置
	 * @param array $timeNew 新配置
	 * @return boolean
	 */
	public static function set_time_rule($model_id, $timeOld = array(), $timeNew = array()) {
		if (is_array ( $timeOld )) {
			foreach ( $timeOld as $k => $v ) {
				$res = db_factory::execute ( sprintf ( " update %switkey_task_time_rule set rule_day='%d',rule_cash='%s' where day_rule_id='%d' and model_id='%d'", TABLEPRE, $v ['rule_day'], $v ['rule_cash'], $k, $model_id ) );
			}
		}
		if (is_array ( $timeNew )) {
			foreach ( $timeNew as $v2 ) {
				! empty ( $v2['rule_day'] )&&!empty($v2['rule_cash']) and $res = db_factory::execute ( sprintf ( " insert into %switkey_task_time_rule values('','%f','%d','%d')", TABLEPRE,  floatval ( $v2 ['rule_cash'] ), intval ( $v2 ['rule_day'] ),$model_id ) );
			}
		}
		return $res;
	
	}

	/**
	 * 设置任务延期规则
	 * @param int $model_id 模型ID
	 * @param array $delayOld 旧配置
	 * @param array $delayNew 新配置
	 * @return boolean
	 */
	public static function set_delay_rule($model_id, $delayOld = array(), $delayNew = array()) {
		if (is_array ( $delayOld )) {
			foreach ( $delayOld as $k => $v ) {
				$res = db_factory::execute ( sprintf ( " update %switkey_task_delay_rule set defer_rate='%d' where defer_rule_id='%d' and model_id='%d'", TABLEPRE, $v ['defer_rate'], $k, $model_id ) );
			}
		}
		if (is_array ( $delayNew )) {
			foreach ( $delayNew as $v2 ) {
				! empty ( $v2['defer_times'] )&&!empty($v2['defer_rate']) and $res = db_factory::execute ( sprintf ( " insert into %switkey_task_delay_rule values('','%d','%s','%d')", TABLEPRE, intval ( $v2 ['defer_times'] ), intval ( $v2 ['defer_rate'] ), $model_id ) );
			}
		}
		return $res;
	}
	/**
	 * 任务扩展配置保存
	 * @param int $model_id 模型ID
	 * @param array $conf 扩展配置
	 * @return boolean
	 */
	public static function set_task_ext_config($model_id, $conf = array()) {
		return db_factory::execute ( sprintf ( " update %switkey_model set config='%s' where model_id='%d'", TABLEPRE, kekezu::k_input(serialize ( $conf )), $model_id ) );
		
	}
	/**
	 * 删除时间规则
	 * @param int $rule_id 规则编号
	 * @return boolean
	 */
	public static function del_time_rule($rule_id) {
		return db_factory::execute ( sprintf ( " delete from %switkey_task_time_rule where day_rule_id='%d'", TABLEPRE, $rule_id ) );
	}

	/**
	 * 删除延期规则
	 * @param int $rule_id 规则编号
	 * @return boolean
	 */
	public static function del_delay_rule($rule_id) {
		return db_factory::execute ( sprintf ( " delete from %switkey_task_delay_rule where defer_rule_id='%d'", TABLEPRE, $rule_id ) );
	}
	/**
	 * 冻结task,任务状态为!('6','7','8','10','11')
	 * (2,3,4,5) 可以冻结
	 * @param int/array $task_ids
	 */
	public static function task_freeze($task_ids) {
		global $admin_info;
		global $_lang,$_K;
		
		if ($task_ids && is_array ( $task_ids )) {
			$ids = implode ( ',', $task_ids );
			//生成要冻结的记录,并发送冻结通知,生成一系统日志
			$sql2 = sprintf ( "select task_id,task_status,task_title,uid from %switkey_task where task_id in(%s) and task_status in (2,3,4,5)", TABLEPRE, $ids );
			$task_arr = db_factory::query ( $sql2 );
			foreach ( $task_arr as $v ) {
				$sql3 = sprintf ( "insert into %switkey_task_frost (frost_status,task_id,frost_time,admin_uid,admin_username) 
        					values('%d','%d','%d','%d','%s')", TABLEPRE, $v ['task_status'], $v ['task_id'], time (), $admin_info ['uid'], $admin_info ['username'] );
				db_factory::execute ( $sql3 );
				kekezu::admin_system_log ( $_lang['freeze_task'].":{$v['task_title']}" );
				$arr = array($_lang['username']=>$v['username'],Conf::$msgTpl['task_id']=>$v['task_id'],Conf::$msgTpl['task_title']=>$v['task_title'],$_lang['reson']=>$_lang['admin_frize'],$_lang['sitename']=>$_K['sitename']); 
				keke_msg_class::notify_user($v['uid'], $v['username'], 'task_freeze', $_lang['freeze_notcie'],$arr);
			}
		} elseif ($task_ids) { //单条冻结
			$ids = $task_ids;
			$sql2 = sprintf ( "select task_id,task_status,task_title,uid from %switkey_task where task_id = %d and task_status  in (2,3,4,5)", TABLEPRE, $task_ids );
			$task_info = db_factory::get_one($sql2);
			$sql3 = sprintf ( "insert into %switkey_task_frost (frost_status,task_id,frost_time,admin_uid,admin_username) 
        					values(%d,%d,%d,%d,'%s')", TABLEPRE, $task_info ['task_status'], $task_info ['task_id'], time (), $admin_info ['uid'], $admin_info ['username'] );
			db_factory::execute ( $sql3 );
			$arr = array($_lang['username']=>$v['username'],Conf::$msgTpl['task_id']=>$task_info['task_id'],Conf::$msgTpl['task_title']=>$task_info['task_title'],$_lang['reson']=>$_lang['admin_frize'],$_lang['sitename']=>$_K['sitename']); 
			kekezu::admin_system_log ( $_lang['freeze_task'].":{$task_info['task_title']}" );
			keke_msg_class::notify_user($task_info['uid'] , $task_info['username'], 'task_freeze', $_lang['freeze_notcie'],$arr); 
		}
		$sql = sprintf ( "update %switkey_task set task_status = '7' where task_id in(%s) and task_status   in (2,3,4,5)", TABLEPRE, $ids );
		return db_factory::execute ( $sql ); //执行冻结
	}
	/**
	 * 取消冻结,还原弱冻结任务到之前的状态，删除冻结记录
	 * @param int/array $task_ids
	 */
	public static function task_unfreeze($task_ids) {
		global $admin_info;
		global $_lang,$_K;
		if ($task_ids && is_array ( $task_ids )) { //批量恢复
			$ids = implode ( ',', $task_ids );
			//要恢复的任务，删除 的冻结记录
			$sql = sprintf ( "select task_id,task_title,task_status,end_time,sub_time,uid from %switkey_task where task_status=7 and task_id in(%s)", TABLEPRE, $ids );
			$task_arr = db_factory::query ( $sql );
			foreach ( $task_arr as $v ) {
				$sqlf = sprintf ( "select task_id,frost_status,frost_time from %switkey_task_frost", TABLEPRE, $v ['task_id'] );
				$frost_info = db_factory::get_one ( $sqlf );
				$end_time = (time () - $frost_info ['frost_time']) + $v[end_time];
				$sub_time = (time () - $frost_info ['frost_time']) +$v[sub_time];
				$sql2 = sprintf ( "update %switkey_task set task_status = %d,end_time='%s',sub_time='%s'  where task_id = '%d'", TABLEPRE, $frost_info ['frost_status'], $end_time,$sub_time, $v ['task_id'] );
				db_factory::execute ( $sql2 );
				db_factory::execute ( sprintf ( "delete from %switkey_task_frost where task_id = '%d'", TABLEPRE, $frost_info ['task_id'] ) );
				kekezu::admin_system_log ( $_lang['unfreeze_task'].":{$v['task_title']}" );
				
				$arr = array($_lang['username']=>$v['username'],Conf::$msgTpl['task_id']=>$v['task_id'],Conf::$msgTpl['task_title']=>$v['task_title'],$_lang['reson']=>$_lang['admin_unfrize'],$_lang['sitename']=>$_K['sitename']); 
				keke_msg_class::notify_user( $v['uid'],  $v['username'], 'task_unfrize', $_lang['task_unfrize'],$arr);
				
			 }
		} elseif (task_ids) { //单条恢复
			$sql = sprintf ( "select task_id,task_title,task_status,end_time,sub_time,uid from %switkey_task where task_status=7 and task_id ='%d'", TABLEPRE, $task_ids );
			$task_info = db_factory::get_one ( $sql );
			$sqlf = sprintf ( "select task_id,frost_status,frost_time from %switkey_task_frost", TABLEPRE, $task_info ['task_id'] );
			$frost_info = db_factory::get_one ( $sqlf );
			$end_time = (time () - $frost_info ['frost_time']) + $task_info[end_time];
			$sub_time = (time () - $frost_info ['frost_time']) +$task_info[sub_time];
			$sql2 = sprintf ( "update %switkey_task set task_status = %d,end_time='%s',sub_time='%s' where task_id = '%d'", TABLEPRE, $frost_info ['frost_status'], $end_time, $sub_time, $task_info ['task_id'] );
			
			db_factory::execute ( $sql2 );
			db_factory::execute ( sprintf ( "delete from %switkey_task_frost where task_id = '%d'", TABLEPRE, $frost_info ['task_id'] ) );
			kekezu::admin_system_log ( $_lang['unfreeze_task'].":{$task_info['task_title']}" );
			$arr = array($_lang['username']=>$task_info['username'],Conf::$msgTpl['task_id']=>$task_info['task_id'],Conf::$msgTpl['task_title']=>$task_info['task_title'],$_lang['reson']=>$_lang['admin_unfrize'],$_lang['sitename']=>$_K['sitename']); 
				keke_msg_class::notify_user( $task_info['uid'],  $task_info['username'], 'task_unfrize', $_lang['task_unfrize'],$arr);
			 }
		return true;
	}
	/**
	 * 任务通过审核  task_staus = 1 是待审核的任务 将任务状态改2
	 * @param int/array $task_ids
	 */
	public static function task_audit_pass($task_ids) {
		global $_lang;
		if ($task_ids && is_array ( $task_ids )) {
			$ids = implode ( ',', $task_ids );
			$task_arr = db_factory::query ( sprintf ( "select task_id,task_title,task_status,uid,username,start_time,sub_time,end_time,payitem_time from %switkey_task where task_id in(%s) and task_status=1", TABLEPRE, $ids ) );
			foreach ( $task_arr as $v ) {
				kekezu::admin_system_log ( $_lang['audit_task'].":{$v['task_title']}".$_lang['pass'] );
				 
				//增值服务到期时间更新
				$payitem_add_time =time()- $v['start_time'];//所需增加的时间
				$payitem_arr = unserialize($v['payitem_time']);
				intval($payitem_arr['top'])>0 or $top_add_time = false;
				intval($payitem_arr['urgent'])>0 or $urgent_add_time = false;
				$payitem_time = keke_task_class::get_payitem($v['payitem_time'],$top_add_time,$urgent_add_time);
				
				//更改任务状态
				$sub_time = time()+($v['sub_time']-$v['start_time']);
				$end_time = time()+($v['end_time']-$v['start_time']);
				$res = db_factory::execute ( sprintf ( "update %switkey_task set task_status=2 ,start_time='%d',sub_time='%d',end_time='%d',payitem_time='%s'  where task_id in(%s)", TABLEPRE,time(),$sub_time,$end_time,$payitem_time,$v['task_id']) );
				
				$info = db_factory::get_one('select union_user,union_assoc from '.TABLEPRE.'witkey_space where uid='.$v['uid']);
				keke_union_class::union_task_submit($info,$v['task_id']);//联盟
				
				$feed_arr = array ("feed_username" => array ("content" =>$v['username'], "url" => "index.php?do=space&member_id={$v['uid']}" ), "action" => array ("content" => $_lang['pub_task'], "url" => "" ), "event" => array ("content" => "{$v['task_title']}", "url" => "index.php?do=task&task_id={$v['task_id']}" ) );
				kekezu::save_feed ( $feed_arr,$v['uid'],$v['username'], 'pub_task',$v['task_id']);
			}
		} elseif ($task_ids) {
				
			$ids = $task_ids;
			$task_info = db_factory::get_one ( sprintf ( "select task_id,task_title,task_status,uid,username,start_time,sub_time,end_time,payitem_time from %switkey_task where task_id = '%d' and task_status=1", TABLEPRE, $ids ) );
			if ($task_info) {
				//增值服务到期时间更新
				$payitem_add_time =time()- $task_info['start_time'];//所需增加的时间 
				$payitem_arr = unserialize($task_info['payitem_time']);
			
				$payitem_arr['top']>1000000000 and $top_add_time = $payitem_add_time or $top_add_time=false;
				$payitem_arr['urgent']>1000000000 and $urgent_add_time = $payitem_add_time or $urgent_add_time=false;
			
				$payitem_time = keke_task_class::get_payitem($task_info['payitem_time'],$top_add_time,$urgent_add_time);
				
              
				$sub_time = time()+(intval($task_info['sub_time'])-intval($task_info['start_time']));
				$end_time = time()+($task_info['end_time']-$task_info['start_time']);
				$sql =  sprintf ( "update %switkey_task set task_status=2 ,start_time='%d',sub_time='%d',end_time='%d',payitem_time='%s'  where task_id  ='%d' ", TABLEPRE,time(),$sub_time,$end_time,$payitem_time,$task_info['task_id'] ) ;
		
				$res = db_factory::execute ($sql);
				
				$info = db_factory::get_one('select union_user,union_assoc from '.TABLEPRE.'witkey_space where uid='.$task_info['uid']);
				keke_union_class::union_task_submit($info,$task_info['task_id']);//联盟
				
				kekezu::admin_system_log ( $_lang['audit_task'].":{$task_info['task_title']}".$_lang['pass'] );
				$feed_arr = array ("feed_username" => array ("content" =>$task_info['username'], "url" => "index.php?do=space&member_id={$task_info['uid']}" ), "action" => array ("content" => $_lang['pub_task'], "url" => "" ), "event" => array ("content" => "{$task_info['task_title']}", "url" => "index.php?do=task&task_id={$task_info['task_id']}" ) );
				kekezu::save_feed ( $feed_arr,$task_info['uid'],$task_info['username'], 'pub_task',$task_info['task_id']);
			}
		}
		return $res;
	}
	/**
	 * 
	 * 该方法用于任务的推荐
	 * @param int $task_id
	 */
	public static function task_recommend($task_id){
		return db_factory::execute(sprintf("update %switkey_task set is_top=1 where task_id='%d' ",TABLEPRE,$task_id));
	}
	/**
	 * 
	 * 该方法用于取消任务的推荐
	 * @param int $task_id
	 */
	public static function task_unrecommend($task_id){
		return db_factory::execute(sprintf("update %switkey_task set is_top=0 where task_id='%d' ",TABLEPRE,$task_id));
	}
	/**
	 * 任务审核不过，将task_staus =1 改为  10,审核失败
	 * 审核失败后，任务退款给雇主
	 * @param unknown_type $task_ids
	 * @param $trust_response 担保回调响应
	 */
	public static function task_audit_nopass($task_ids,$trust_response=false) {
		global $kekezu;
		global $_lang;
		if ($task_ids && is_array ( $task_ids )) {
			$ids = implode ( ',', $task_ids );
			$task_arr = db_factory::get_one ( sprintf ( "select task_id,task_title,task_status,task_cash,uid from %switkey_task where task_id in(%s)", TABLEPRE, $ids ) );
			foreach ( $task_arr as $v ) {
				
				$res = db_factory::execute ( sprintf ( "update %switkey_task set task_status=10 where task_id ='%d' ", TABLEPRE, $v ['task_id'] ) );
				$data = array(':model_name'=>$kekezu->_model_list[$v['model_id']]['model_name'],':task_id'=>$v['task_id'],':task_title'=>$v['task_title']);
				keke_finance_class::init_mem('task_fail', $data);
				keke_finance_class::cash_in ( $v ['uid'], $v ['task_cash'], 0, 'task_fail', 'admin', 'task', $v ['task_id'] );
				kekezu::admin_system_log ( $_lang['audit_task'].":{$v['task_title']}".$_lang['not_pass'] );
				
			}
		
		} elseif ($task_ids) {
			$ids = $task_ids;
			$task_info = db_factory::get_one ( sprintf ( "select task_id,task_title,task_status,task_cash,uid,is_trust,trust_type,model_id from %switkey_task where task_id = '%d'", TABLEPRE, $ids ) );
			if ($task_info) {
				if($task_info['is_trust']&&$trust_response==false){
					$trust_data['refund'] = array($ids,$task_info['task_cash']);
					$jump_url = keke_trust_fac_class::trust_task_request("pt_refund",$kekezu->_model_list[$task_info['model_id']]['model_dir'],$ids,$task_info['trust_type'],$trust_data);
					header("Location:".$jump_url);die();	
				}else{
					$res = db_factory::execute ( sprintf ( "update %switkey_task set task_status=10 where task_id  ='%d' ", TABLEPRE, $ids ) );
					switch($task_info['is_trust']){
						case "1":
							$fina_cash = $task_info['task_cash'];
							$data = array ("uid" => $task_info ['uid'], "username" => $task_info ['username'], "obj_id" => $ids, "fina_cash" => $fina_cash, "fina_time" => time (), "fina_action" => "task_fail");
							keke_finance_class::finance_trust ( $data,$task_info['trust_type'], 'in' );
							break;
						case "0":
							$data = array(':model_name'=>$kekezu->_model_list[$v['model_id']]['model_name'],':task_id'=>$task_info['task_id'],':task_title'=>$task_info['task_title']);
							keke_finance_class::init_mem('task_fail', $data);
							keke_finance_class::cash_in ( $task_info ['uid'], $task_info ['task_cash'], 0, 'task_fail', 'admin', 'task', $task_info ['task_id'] );
							break;
					}
					kekezu::admin_system_log ( $_lang['audit_task'].":{$task_info['task_title']}".$_lang['not_pass'] );
				}
			}
		}
		return $res;
	}
	/**
	 * 任务批量删除，可以删除的任务 0,8,9,10
	 * @param $model 1=>悬赏模式，2=>招标模式， 两种模式稿件表不一样
	 * @param int/array $task_ids
	 */
	public static function task_del($task_ids,$model=1) {
		global $_lang;
		if(is_array($task_ids)){
			//批量任务删除
			$ids = implode ( ",", $task_ids ) or $ids = $task_ids;
			foreach ($task_ids as $v) {
				self::del_sign_task($v, $model);
			}
		}else{
			$ids = $task_ids;
			self::del_sign_task($task_ids, $model);
		}
		return db_factory::execute ( sprintf ( "delete from %switkey_task where task_status in(0,8,9,10) and task_id in(%s)", TABLEPRE, $ids ) );
	}
	/**
	 * 删除单任务
	 * @param int $task_id
	 * @param int $model
	 */
	public static function del_sign_task($task_id,$model){
		global $_lang;
		//单任务删除
		if($model===1){
			//悬赏稿件
			$sql = sprintf("delete from %switkey_task_work where task_id='%d'",TABLEPRE,$task_id);
		}else{
			//招标稿件
			$sql = sprintf("delete from %switkey_task_bid where task_id ='%d'",TABLEPRE,$task_id);
		}
		//删除稿件
		db_factory::execute($sql);
		//删除附件
		//先查询附件
			
		$file_sql = sprintf("select save_name from %switkey_file where task_id = '%d' ",TABLEPRE,$task_id);
		$files = db_factory::query($file_sql);
		//删除文件
		foreach ($files as $v){
			keke_file_class::del_file($v['save_name']);
		}
		//删除文件记录
		db_factory::execute(sprintf("delete from %switkey_file where task_id ='%d' ",TABLEPRE,$task_id));
		//添加系统日志
		$del_title = db_factory::get_count(sprintf("select task_title from %switkey_task where task_id='%d'",TABLEPRE,$task_id));
		kekezu::admin_system_log($_lang['delete_task'].":{$del_title}");
	}
	/***任务后台编辑动作***/
	public static function can_operate($status) {
		global $_lang;
		$operate = array ();
		switch ($status) {
			case "1" : //待审核
				$operate ['pass'] = $_lang['pass_audit'];
				$operate ['nopass'] = $_lang['no_pass_audit'];
				break;
			case "2" : //投稿
			case "3" : //选稿
			case "4" : //投票
			case "5" : //公示
			case "6" : //交付
				$operate ['freeze'] = $_lang['freeze_task'];
				break;
			case "7" : //冻结
				$operate ['unfreeze'] = $_lang['unfreeze_task'];
		}
		return $operate;
	}
	/**
	 * 获取用户参加的任务统计。分模型 
	 * @param int $uid
	 */
	public static function get_user_join_task($uid = '') {
		global $user_info;
		$count_arr = array ();
		$uid or $uid = $user_info ['uid']; //不传递默认为当前用户信息
		/*悬赏类任务统计**/
		$reward_sql = " select count(c.task_id) count,c.model_id from (select DISTINCT a.task_id,b.model_id from %switkey_task_work a left join %switkey_task b on a.task_id=b.task_id where a.uid='%d') c  group by c.model_id";
		$reward_arr = db_factory::query ( sprintf ( $reward_sql, TABLEPRE, TABLEPRE, $uid ), 3600 );
		/**招标类任务统计**/
		$tender_sql = " select count(c.task_id) count,c.model_id from (select DISTINCT a.task_id,b.model_id from %switkey_task_bid a left join %switkey_task b on a.task_id=b.task_id where a.uid='%d') c  group by c.model_id";
		$tender_arr = db_factory::query ( sprintf ( $tender_sql, TABLEPRE, TABLEPRE, $uid ), 3600 );
		/**合并**/
		$total_arr = array_merge ( $reward_arr, $tender_arr );
		foreach ( $total_arr as $v ) {
			$count_arr [$v ['model_id']] =intval($v ['count']);
		}
		return $count_arr;
	}
	/**
	 * 任务稿件、附件、留言、收藏、举报删除
	 * @param $model_id 模型ID
	 * @param $task_id 任务ID 可能为数组
	 * @param $is_array 判断传递ID是否为数组
	 */
	public static function delete_task_releate_item($model_id,$task_id,$is_array=false){
		global $kekezu;
			$model_code = $kekezu->_model_list[$model_id]['model_code'];
			$model_code=='tender'||$model_code=='dtender' and $tab_work = "task_bid" or $tab_work='task_work';
			
	}
}