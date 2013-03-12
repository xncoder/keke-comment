<?php
defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
kekezu::admin_check_role ( 135 );
require S_ROOT.'/keke_client/keke/keke_tool_class.php';
$task_list = keke_tool_class::get_task_list($config['keke_app_id']);
if ($ajax && $pid) { 
	$option_str = get_indus ( intval ( $pid ) );
	$options = kekezu::echojson ( '', $option_str ? '1' : '0', $option_str );
	die ();
}
if ($ajax && $ajax == 'modify_title') {
	if (! isset ( $t_key ) || ! isset ( $t_index ) || ! isset ( $t_value )) {
		die ();
	}
	if ($task_list [$t_index] ['keke_task_id'] == $t_key) {
		if (strtolower ( CHARSET ) != 'utf-8') { 
			$t_value = kekezu::utftogbk ( $t_value );
		}
		$task_list [$t_index] ['task_title'] = $t_value;
		file_put_contents ( $task_data_dir, serialize ( $task_list ) );
	}
	die ();
}
if (isset ( $sbt_action ) || isset ( $add, $add_index, $add_id )) { 
	if ($task_list [intval ( $add_index )] && $task_list [intval ( $add_index )] ['keke_task_id'] == intval ( $add_id )) { 
		$task_list_remain = $task_list;
		unset ( $task_list_remain [intval ( $add_index )] ); 
		$task_add = $task_list [intval ( $add_index )]; 
		unset ( $task_list );
		$task_list [] = $task_add;
	}
	$sql = "insert into %switkey_task (`model_id`,`r_task_id`,`task_union`,`task_title`,`task_desc`,`task_cash`,`task_status`,`start_time`,`sub_time`,`indus_id`,`indus_pid`,`task_cash_coverage`) values ";
	$indus_pid = intval ( $p_indus_select );
	$indus_id = intval ( $s_indus_select );
	$files = array();
	while ( list ( $key, $value ) = each ( $task_list ) ) {
		$tmode = explode ( '-', $value ['task_id'] );
		$model_id = db_factory::get_count ( ' select model_id from ' . TABLEPRE . 'witkey_model where model_code="' . $tmode [1] . '"' );
		$sql .= '(' . intval ( $model_id ) . ',' . intval ( $value ['keke_task_id'] );
		$sql .= ',2,"' . kekezu::k_input ( $value ['task_title'] ) . '","' . kekezu::k_input ( $value ['task_desc'] ) . '",' . floatval ( $value ['task_cash'] ) . ',2,' . intval ( $value ['start_time'] ) . ',' . intval ( $value ['sub_time'] ) . ',' . $indus_id . ',' . $indus_pid;
		$sql .= $value ['cash_cove'] ? ',' . get_cover_id ( $value ['cash_cove'] ) . '),' : ',null),';
		$log_ids .= $value ['keke_task_id'] . ',';
		$files[$value ['keke_task_id']] = $value ['task_file'];
	}
	$sql = rtrim ( $sql, ',' );
	$result = db_factory::execute ( sprintf ( $sql, TABLEPRE ) );
	kekezu::admin_system_log ( '[批量]添加联盟任务' . $result );
	if ($result) { 
		insert_file($files);
		$data = array ('log_ids' => rtrim ( $log_ids, ',' ) );
		keke_union_class::union_request ( 'get_task', $data ); 
		kekezu::admin_show_msg ( '提示', '?do=keke&view=getlist', 2, '任务添加成功', 'success' );
	}
	kekezu::admin_show_msg ( '提示', '?do=keke&view=getlist', 2, '任务添加失败', 'warning' );
}
$indus_p_arr = get_indus (); 
function get_indus($pid = '0') { 
	global $kekezu;
	! $pid && $pid = strval ( 0 );
	$indus_arr = kekezu::get_indus_by_index ( '1', $pid ); 
	$str = '';
	while ( list ( $key, $value ) = each ( $indus_arr [$pid] ) ) {
		$str .= '<option value="' . $value ['indus_id'] . '">' . $value ['indus_name'] . '</option>';
	}
	return $str;
}
function get_cover_id($price_range) {
	$cover_arr = explode ( '-', $price_range );
	if (sizeof ( $cover_arr ) < 2) {
		return false;
	}
	$start_cover = floor ( $cover_arr [0] );
	$end_cover = floor ( $cover_arr [1] );
	$sql = "select cash_rule_id from %switkey_task_cash_cove where `start_cove`<=%d and `end_cove`>=%d and `start_cove`+`end_cove`>=%d";
	$cove_id = db_factory::get_count ( sprintf ( $sql, TABLEPRE, $start_cover, $end_cover, $start_cover + $end_cover ) );
	return $cove_id;
}
function insert_file($files) {
	global $admin_info;		
	if ($files) {
		$files = array_filter($files);
		foreach($files as $r_task_id=>$file){
			$file_ids = '';
			$o = explode(',',$file);
			foreach ($o as $v){
				if($v&&strpos($v,'#')!==FALSE){
					$t = explode('#',$v);
					$info = db_factory::get_one(' select task_id,task_title from '.TABLEPRE.'witkey_task where r_task_id='.$r_task_id);
					if($info){
						$file_ids.=db_factory::inserttable(TABLEPRE.'witkey_file',array(
								'obj_type'=>'task',
								'task_id'=>$info['task_id'],
								'work_id'=>0,
								'task_title'=>$info['task_title'],
								'file_name'=>$t[0],
								'save_name'=>$t[1],
								'uid'=>$admin_info['uid'],
								'username'=>$admin_info['username'],
								'on_time'=>time()
							)).',';
					}
				}
			}	
			$file_ids = rtrim($file_ids,',');
			if($file_ids){
				db_factory::execute('update '.TABLEPRE.'witkey_task set task_file="'.$file_ids.'" where task_id='.$info['task_id']);
			}
		}
	}
}
require $template_obj->template ( "control/admin/tpl/admin_{$do}_{$view}" );