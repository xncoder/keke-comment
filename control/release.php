<?php	defined ( 'IN_KEKE' ) or exit('Access Denied');
 kekezu::check_login();
keke_lang_class::package_init("task");
keke_lang_class::loadlang($do);
$mode_arr  = array("professional","guide","onekey");
in_array($pub_mode,$mode_arr) or $pub_mode='professional';
switch($pub_mode){
	case "professional":
		break;
	case "guide":
		break;
	case "onekey":
		$init_info = array("t_id"=>$t_id);
		$r_step or $r_step = "step2";
		$t_id or kekezu::show_msg($_lang['warning'],$_SERVER['HTTP_REFERER'],3,$_lang['onekey_pub_notice'],"warning");
		break;
}
$page_title= $_lang['pub_task'].'--' . $_K ['html_title'];
$model_list = kekezu::get_table_data ( '*', 'witkey_model', " model_type = 'task' and model_status='1'", 'model_id asc ', '', '', 'model_id', 3600 );
if(!$model_id){
	$model_ids = array_keys($model_list);
	$model_id = $model_ids['0'];
}
$model_id and $model_info = $model_list[$model_id] or kekezu::keke_show_msg("index.php","{lang:no_model}","error");
if($model_id==4){
$stage_nav=array("1"=>array("step1",$_lang['stage_nav_step1_a'],$_lang['stage_nav_step1_b']),
				"2"=>array("step2",$_lang['stage_nav_step2_a'],$_lang['stage_nav_step2_b']),
				"3"=>array("step3",$_lang['stage_nav_step3_a'],$_lang['stage_nav_step3_b_4']),
				"4"=>array("step4",$_lang['stage_nav_step4_a'],$_lang['stage_nav_step4_b']));
}else{
$stage_nav=array("1"=>array("step1",$_lang['stage_nav_step1_a'],$_lang['stage_nav_step1_b']),
				"2"=>array("step2",$_lang['stage_nav_step2_a'],$_lang['stage_nav_step2_b']),
				"3"=>array("step3",$_lang['stage_nav_step3_a'],$_lang['stage_nav_step3_b']),
				"4"=>array("step4",$_lang['stage_nav_step4_a'],$_lang['stage_nav_step4_b']));
}
$r_step or $r_step='step1';
$basic_url = "index.php?do=release&pub_mode=$pub_mode&t_id=$t_id&model_id=".$model_id."&r_step=".$r_step;
if($ac=='show_map'){
	$title=$_lang['task_map_set'];
	$user_info ['residency']&&$local = explode(',', $user_info['residency']);
	if ($_K['map_api']=='baidu'){
		require keke_tpl_class::template('task/task_map_baidu');
	}else{
		require keke_tpl_class::template('task/task_map_google');
	}
	die();
}
if($act=='agreement'){
	$title=kekezu::lang("agreement");
	require keke_tpl_class::template("task/release_agree");
}
require S_ROOT."./task/".$model_info['model_dir']."/control/release.php";