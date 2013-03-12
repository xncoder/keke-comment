<?php
/**
 * @copyright keke-tech
 * @author Chen
 * @version v 2.0
 * 2011-11-07 11:31:34
 */
defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
$ops = array ("config", "control", "priv","cash_rule");
in_array ( $op, $ops ) or $op = 'config';
$ac_url="index.php?do=model&model_id=$model_id&view=config&op=$op";
kekezu::empty_cache();
switch ($op) {
	case "config" : //基本配置
		if($sbt_edit){
			$model_obj=keke_table_class::get_instance("witkey_model");
			! empty ( $fds ['indus_bid'] ) and $fds['indus_bid'] = implode ( ",", $fds ['indus_bid'] ) or $fds['indus_bid'] = '';
			$fds['on_time']=time();
			$fds=kekezu::escape($fds);
			$res=$model_obj->save($fds,$pk);
			
			$res and kekezu::admin_show_msg ( $_lang['update_success'],$ac_url, 3,'','success' ) or kekezu::admin_show_msg ( $_lang['update_fail'],$ac_url, 3,'','warning');
			}else{
				$indus_arr = $kekezu->_indus_arr;//任务行业
				$indus_index =kekezu::get_indus_by_index ();//索引行业
			}
		break;
	case "control" : //流程配置
		if($ac){
			switch ($ac){
				case "del_time_rule":
					$rule_id and keke_task_config::del_time_rule($rule_id);
					break;
				case "del_delay_rule":
					$rule_id and keke_task_config::del_delay_rule($rule_id);
					
					break;
			}
		}elseif($sbt_edit){
			$res.=keke_task_config::set_time_rule($model_id,$timeOld,$timeNew); //时间规则配置
			$res.=keke_task_config::set_delay_rule($model_id,$delayOld,$delayNew);//延期规则配置
			
			is_array($conf) and $res.=keke_task_config::set_task_ext_config($model_id,$conf);
			
			$res and kekezu::admin_show_msg ( $_lang['update_success'],$ac_url, 3 ,'','success') or kekezu::admin_show_msg ( $_lang['update_fail'],$ac_url, 3,'','warning');
			
		}else{
			$confs = unserialize($model_info['config']);
			is_array($confs)&&extract($confs);//配置解压
			$delay_rule=keke_task_config::get_delay_rule($model_id);//延期规则
			$cash_cove = kekezu::get_cash_cove('match');
		}
		break;
	case "priv" : //权限配置
		if ($sbt_edit) {
			if ($fds ['allow_times']){
				$perm_item_obj = new Keke_witkey_priv_item_class ();
					foreach ( $fds ['allow_times'] as $k => $v ) {
						$perm_item_obj->setWhere ( " op_id = '$k'" );
						$perm_item_obj->setAllow_times ( intval ( $v ) );
						$perm_item_obj->edit_keke_witkey_priv_item ();
					}
			}
			kekezu::admin_show_msg ( $model_info['model_name'].$_lang['permissions_config_update_success'], "$ac_url",'3','','success');
		} else {
			$perm_item = keke_privission_class::get_model_priv_item($model_id);//权限配置项
		}
		break;
		case "cash_rule"://金额区间
		switch($ac){
			case "del":
				 
				$res = db_factory::execute(sprintf(" delete from %switkey_task_cash_cove where cash_rule_id='%d'",TABLEPRE,$rule_id));
				$res and kekezu::admin_show_msg ($_lang['op_success'], "index.php?do=$do&model_id=$model_id&view=config&op=control", 3,'','success' ) or kekezu::admin_show_msg ($_lang['op_fail'], "index.php?do=$do&model_id=$model_id&view=config&op=control", 3, '', 'warning' );
				break;
			case "edit":
			case "add":
				if($sbt_edit){
					$fds['on_time']   = time();
					$fds['cove_desc'] = sprintf('%.2f',$fds['start_cove']).$_lang['y'].'-'.sprintf('%.2f',$fds['end_cove']).$_lang['y'];
					$fds['model_code']= $model_info['model_code'];
					$cove_obj = keke_table_class::get_instance("witkey_task_cash_cove");
					$res = $cove_obj->save($fds,$pk);
					$res and kekezu::admin_show_msg ($_lang['op_success'], $ac_url.'&op=control', 3,'','success' ) or kekezu::admin_show_msg ($_lang['op_fail'], $ac_url.'&ac='.$ac, 3, '', 'warning' );
				}else{
					$cash_cove = kekezu::get_cash_cove('match');
					$cove_info = $cash_cove[$rule_id];
					$cash_cove = end($cash_cove);
					$end   = intval($cash_cove['end_cove']);
					if($cove_info){
						$start_cove=intval($cove_info['start_cove']);
					}else{
						$start_cove=$end;
					}
					require keke_tpl_class::template('task/'.$model_info['model_dir'].'/control/admin/tpl/task_cove');
					die();
				}
				break;
		}
		break;
}
if($sbt_edit){
		//清除配置缓存
	$file_obj = new keke_file_class();
	$file_obj->delete_files(S_ROOT."./data/data_cache/");
	$log_op_arr = array("config"=>$_lang['basic_config'],"control"=>$_lang['control_config'],"priv"=>$_lang['private_config']);
	$log_msg = $_lang['revised_match_task'].$log_op_arr[$op];
	kekezu::admin_system_log($log_msg);
}
require keke_tpl_class::template ( 'task/' . $model_info ['model_dir'] . '/control/admin/tpl/task_' . $op );