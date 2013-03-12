<?php
/**
 * 多人悬赏任务初始化配置文件
 */

defined('ADMIN_KEKE') or 	exit('Access Denied');

$init_menu = array(
	$_lang['task_manage']=>'index.php?do=model&model_id=12&view=list&status=0',
	$_lang['task_config']=>'index.php?do=model&model_id=12&view=config',
);


$init_config = array(
	'model_id'=>12,
	'model_code'=>'match',
	'model_name'=>$_lang['match'],
	'model_dir'=>'match',
	'model_type'=>'task',
	'model_dev'=>'kekezu',
	'model_status'=>1,
	'audit_cash'=>10,//审核金额
	'min_cash'=>10,//任务最小金额
	'task_rate'=>20,//任务提成比例
	'task_fail_rate'=>10,//任务失败返金抽成比例
	'defeated'=>1,//任务退款处理
	'min_day'=>2,//任务发布最少天数
	'is_auto_adjourn'=>1,//是否自动选稿
	'adjourn_num'=>2,//自动选稿中标稿件数
	'choose_time'=>4,//选稿周期	
	'is_comment'=>1,//任务评论是否公开
	'open_select'=>1,//是否开启交稿期选稿
);