<?php

final class match_time_class extends time_base_class {

	function __construct() {
		parent::__construct ();
	}
	function validtaskstatus() {
		$this->task_tb_timeout();
		$this->task_host_timeout();
		$this->task_other_timeout();	
	}
	/**
	 * 投标到期处理
	 */
	public function task_tb_timeout() {		
		$task_list = db_factory::query(sprintf("select * from %switkey_task where task_status=2 and end_time<'%s' and model_id=12",TABLEPRE,time()));
		if(is_array($task_list)){
			foreach($task_list as $v){
				$task_obj = new match_task_class($v);
				$task_obj->task_tb_timeout();
			}
		}		
	}
	/**
	 * 托管到期处理
	 */
	function task_host_timeout() {
		$task_list = db_factory::query(sprintf("select * from %switkey_task where task_status=3 and end_time<'%s' and model_id=12",TABLEPRE,time()));
		if(is_array($task_list)){
			foreach($task_list as $v){
				$task_obj = new match_task_class($v);
				$task_obj->task_host_timeout();
			}
		}
		
	}
	/**
	 * 工作阶段到期处理
	 * 	[威客确认到期|威客确认完成到期|雇主确认验收到期]
	 */
	function task_other_timeout() {
		$task_list = db_factory::query(sprintf("select * from %switkey_task where task_status in (4,5,6) and end_time<'%s' and model_id=12",TABLEPRE,time()));
		if(is_array($task_list)){
			foreach($task_list as $v){
				$task_obj = new match_task_class($v);
				$task_obj->task_other_timeout();
			}
		}
		
	}
}