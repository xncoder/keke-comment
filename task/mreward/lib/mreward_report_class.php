<?php

/** 
 * 多人悬赏的仲裁，举报处理
 * @version 2.0
 * 
 */
keke_lang_class::load_lang_class('mreward_report_class');
class mreward_report_class extends keke_report_class {
	
	public static function get_instance($report_id, $report_info = null, $obj_info = null) {
		static $obj = null;
		if ($obj == null) {
			$obj = new mreward_report_class ( $report_id, $report_info, $obj_info );
		}
		return $obj;
	}
	public function __construct($report_id, $report_info, $obj_info) {
		parent::__construct ( $report_id, $report_info, $obj_info );
	}
	/**
	 * 处理多人悬赏任务的举报
	 * 对稿件的举报成立，就是取消中标，任务在公示期内可以重置任务状态为进行中,
	 * 扣除威客的能力值，不超出中标所得金额,或者威客加入黑名单X天
	 * 对任务的举报成立，扣除雇主的信誉值，不超出任务赏金值
	 * @since keke_report_class
	 */
	function process_report($op_result, $type) {
		global $_lang;
		$op_result = $this->op_result_format ( $op_result );
		$trans_name = $this->get_transrights_name ( $this->_report_info ['report_type'] );
		//判断举报是否成立
		if ($op_result ['action'] != 'pass') {
			$this->process_notify('nopass',$this->_report_info, $this->_user_info, $this->_to_user_info, $op_result ['result']);
			return $this->change_status ( $this->_report_id, 3,$op_result, $op_result ['result'] );
		} else { //举报成立 
			//重置任务状态，并且任务在公示期
// 			var_dump($op_result ['reset_task']);
// 			var_dump($op_result ['delay_days']);
// 			var_dump($this->_obj_info);die();
			if ($op_result ['reset_task'] == 1 && $this->_obj_info ['origin_status'] == 5 && $op_result ['delay_days']) {
				$end_time = time () + $op_result ['delay_days'] * 3600 * 24;
				$sql = sprintf ( "update %switkey_task set task_status = 2,end_time = %d where task_id = %d ", TABLEPRE, $end_time, $this->_obj_info ['task_id'] );
				db_factory::execute ( $sql );
			}
			//取消中标
			if ($op_result ['cancel_bid'] == 1 && $this->_report_info ['obj'] == 'work') {
				$sql = sprintf ( "update %switkey_task_work set work_status = 8 ", TABLEPRE );
				db_factory::execute ( $sql );
			}
			//扣信誉/能力值
			if ($op_result ['credit_value']) {
				$this->_credit_info ['type'] == $_lang['able_value'] and $type = 2 or $type = 1;
				$this->less_credit ( $op_result ['credit_value'], $type );
			}
			//加入黑名单
			if ($op_result ['freeze_user'] && $op_result ['freeze_day']) {
				$this->to_black ( $op_result ['freeze_day'] );
			}
			
			
			//更新举报记录，完成举报
			$report_obj = new Keke_witkey_report_class ();
			$report_obj->setReport_id ( $this->_report_id );
			$report_obj->setReport_status ( 4 );
			$report_obj->setOp_result ( $op_result ['result'] );
			$report_obj->setOp_time ( time () );
			$report_obj->setOp_uid ( $op_result ['op_uid'] );
			$report_obj->setOp_username ( $op_result ['op_username'] );
			$this->process_notify('pass',$this->_report_info, $this->_user_info, $this->_to_user_info, $op_result ['result']);
			return $report_obj->edit_keke_witkey_report ();
		
		}
	}
	/**
	 * 多人悬赏任务没有维权,此处为空方法
	 *  
	 * @see keke_report_class::process_rights()
	 */
	function process_rights($op_result, $type) {
		return true; 
	}

}

?>