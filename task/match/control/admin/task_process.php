<?php
/**
 * @copyright keke-tech
 * @author Chen
 * @version v 2.0
 * @since control/admin/admin_trans_process
 * 2011-11-01 11:31:34
 */
defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
//实例化处理对象
$process_obj=match_report_class::get_instance($report_id,$report_info,$obj_info);
$report_info = $process_obj->_report_info;
$user_info = $process_obj->_user_info;
$to_userinfo  = $process_obj->_to_user_info;
$process_can = $process_obj->_process_can;
$credit_info = $process_obj->_credit_info;
$cash = $process_obj->_obj_info['cash']; 
$url = "index.php?do=trans&view=process&type=$type&report_id=$report_id";
$match_task = $process_obj->_match_task;
$match_work = $process_obj->_match_work;
if(!empty($op_result)){
	switch ($type) {
		case "rights"://维权
			$res=$process_obj->process_rights($op_result,$type);
			break;
		case 'report':
			$res = $process_obj->process_report($op_result,$type);
			if($op_result['action']=='pass'){
				$res  and kekezu::admin_show_msg($_lang['operate_notice'],$url,3,$action_arr[$type]['1'].$_lang['operation_completed'],'success') or kekezu::admin_show_msg($_lang['operate_notice'],$url,3,$action_arr[$type]['1'].$_lang['operate_fail'],'warning');
			}else{
				$url = "index.php?do=trans&view=report&type=$type&report_status=3";
			    $res  and kekezu::admin_show_msg($_lang['operate_notice'],$url,3,$action_arr[$type]['1'].$_lang['operation_completed'],'success') or kekezu::admin_show_msg($_lang['operate_notice'],$url,3,$action_arr[$type]['1'].$_lang['operate_fail'],'warning');
			}
		break;		
	}
}

require keke_tpl_class::template ( 'task/' . $model_info ['model_dir'] . "/control/admin/tpl/task_$view");