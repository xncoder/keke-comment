<?php

class time_fac_class {
	protected $_basic_config;
	
	function __construct() {
		global $kekezu;
		$this->_basic_config = $kekezu->_sys_config;
	}
	function run() {
		global $model_list;
		$model_list = $model_list ? $model_list : kekezu::get_table_data ( 'witkey_model', 'model_status=1', '', null, 'model_id' );
		$this->task_finish_auto_mark();
		foreach ( $model_list as $model_info ) {
			$model_dir = $model_info ['model_dir'];
			if (file_exists ( S_ROOT . "./task/$model_dir" ))
				$m = strtolower ( $model_dir ) . "_time_class";
			if (class_exists ( $m )) {
				$time_obj = new $m ();
				$time_obj->validtaskstatus ();
			}
		}
		keke_task_class::hp_timeout(7);
	}
	/**
	 * 任务结束自动好评
	 */
	function task_finish_auto_mark(){
		//威客的记录集合，更新
		$nomark_wk_list = db_factory::query(sprintf('select `mark_id` from %switkey_mark where mark_status=0 and mark_max_time<%d and mark_type=1',TABLEPRE,time()));
		if(is_array($nomark_wk_list)){
			foreach ($nomark_wk_list as $v){
				keke_user_mark_class::exec_mark_process($v['mark_id'],null,1,"4,5","5.0,5.0");
				//db_factory::execute(sprintf('update %switkey_mark set mark_status=1,mark_content="系统自动好评",aid="4,5",aid_star="5.0,5.0",mark_value=obj_cash where mark_id=%d',TABLEPRE,$v['mark_id']));
			}
		}//雇主的评价集合，更新
		$nomark_gz_list = db_factory::query(sprintf('select `mark_id` from %switkey_mark where  mark_status=0 and mark_max_time<%d and mark_type=2',TABLEPRE,time()));
		if(is_array($nomark_gz_list)){
			foreach ($nomark_gz_list as $v){
				keke_user_mark_class::exec_mark_process($v['mark_id'],null,1,"1,2,3","5.0,5.0,5.0");
				//db_factory::execute(sprintf('update %switkey_mark set mark_status=1,mark_content="系统自动好评",aid="1,2,3",aid_star="5.0,5.0,5.0",mark_value=obj_cash where mark_id=%d',TABLEPRE,$v['mark_id']));
			}
		}
	}

}

?>