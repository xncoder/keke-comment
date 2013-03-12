<?php
class preward_report_class extends keke_report_class {
	public static function get_instance($report_id, $report_info = null, $obj_info = null) {
		static $obj = null;
		if ($obj == null) {
			$obj = new preward_report_class ( $report_id, $report_info, $obj_info );
		}
		return $obj;
	}
	public function __construct($report_id, $report_info, $obj_info) {
		parent::__construct ( $report_id, $report_info, $obj_info );
	}
	function process_report($op_result, $type) {
		keke_lang_class::load_lang_class('preward_report_class');
		global $_lang;
		$op_result = $this->op_result_format ( $op_result );
		$trans_name = $this->get_transrights_name ( $this->_report_info ['report_type'] );
		if ($op_result ['action'] != 'pass') {
			$this->process_notify('nopass',$this->_report_info, $this->_user_info, $this->_to_user_info, $op_result ['result']);
			return $this->change_status ( $this->_report_id, 3,$op_result, $op_result ['result'] );
		} else { 
			if ($op_result ['credit_value']) {
				$this->_credit_info ['type'] == $_lang['able_value'] and $type = 2 or $type = 1;
				$this->less_credit ( $op_result ['credit_value'], $type );
			}
			if ($op_result ['freeze_user'] && $op_result ['freeze_day']) {
				$this->to_black ( $op_result ['freeze_day'] );
			}		
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
	function process_rights($op_result, $type) {
		return true; 
	}
}
?>