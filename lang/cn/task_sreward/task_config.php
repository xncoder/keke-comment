<?php
/**
 * 单人悬赏职位配置页语言包
 * 数组键的定义:
 * 单词与单词之间必须用下划线来连接,首字母必须用小写，适用于所有的lang数组；
 * @version kppw2.0
 * @author xl
 */
$lang = array(
/*task_config.php*/
	'edit_successfully'=>'修改成功!',
	'edit_fail'=>'修改失败！',
	'edit_rights_config_successfully'=>'权限配置修改成功',
	'edit_single_reward_task'=>'修改了单人悬赏职位的',
/*task_control.htm*/

	'task_conmission_tactic'=>'职位佣金策略',
	'task_auditing_cash_set'=>'职位审核金额设定',
	'txt_task_auditing_cash_msg'=>'填写正确职位审核金额',
	'txt_task_auditing_cash_title'=>'职位审核金额允许小数',
	'task_cash_notice'=>'(大于这个金额的职位不需要审核，否则需要管理员审核)',
	'set_task_min_cash'=>'职位最小金额设定',
	'task_min_cash_msg'=>'职位最小金额填写错误',
	'task_min_cash_title'=>'职位最小金额为可以含小数',
	'task_min_cash_notice'=>'(职位的最小金额为大于零正整数)',
	'task_deduct_rate'=>'职位提成比例',
	'task_deduct_rate_msg'=>'职位提成比例值为正整数，长度：1-2',
	'task_deduct_rate_title'=>'职位提成比例值为正整数,0-100',
	'deduct_rate_from_fail_task'=>'职位失败返金抽成比例',
	'fail_task_deduct_rate_msg'=>'职位失败返金提成比例值为正整数，长度：1-2',
	'fail_task_deduct_rate_title'=>'职位失败返金提成比例值为正整数,0-100',
	'task_automate_choose'=>'职位自动选稿',
	'task_automate_choose_msg'=>'请填写分配人数',
	'task_automate_choose_title'=>'请填写平分人数!',
	'task_automate_choose_notice'=>'(设置前x人中标平分佣金,不填写则默认关闭)',
	'deal_fail_task'=>'职位失败处理',
	'return_cash'=>'返还现金',
	'return_credit'=>'返还代金券',
	'deal_fail_task_notice'=>'(扣除拥金后的钱)',
	'set_task_time_rule'=>'职位时间规则设定',
	'time_rule'=>'时间规则',
	'check_min_cash_rule'=>'请仔细填写规则允许最小金额',
	'yuan_over'=>'元以上',
	'time_rule_day_msg'=>'天数必须为大于1的整数',
	'time_rule_day_title'=>'请仔细填写天数，不得少于1天',
	'delete_rule'=>'删除规则',
	'add_rule'=>'添加规则',
	'task_public_time'=>'职位公示期时间',
	'task_public_time_msg'=>'公示期时间不对',
	'task_public_time_title'=>'职位公示期最小时间不能小于零',
	'task_public_time_notice'=>'天',
	'task_min_day'=>'职位最少天数',
	'task_min_day_msg'=>'职位最小时间不对,最少1天',
	'task_min_day_title'=>'职位最小时间为1天',
	'task_vote_time'=>'职位投票期时间',
	'task_vote_time_msg'=>'投票期时间不对,长度:1',
	'task_vote_time_title'=>'职位投票期最小时间为1天',
	'task_vote_time_notice'=>'天（职位没有定稿时，通过投票决定，不得少于1天）',
	'limit_new_register_user_vote_time'=>'新注册用户投票时间限制:',
	'new_register_user_vote_time_msg'=>'新注册用户投票时间限制时间不对',
	'new_register_user_vote_time_title'=>'可以对新注册用户不做投票时间限制',
	'new_register_user_vote_time_notice'=>'小时（0为不作限制）',
	'set_choose_time'=>'选稿时间设置',
	'choose_time_msg'=>'选稿时间输入有误',
	'choose_time_title'=>'职位选稿时间最少为1天，最多20天',
	'choose_time_notice'=>'天(职位选稿时间最少为1天，最多20天)',
	'choose_in_doing'=>'进行中选稿',
	'set_delay_rule'=>'延期规则设定',
	'delay_min_cash'=>'延期最小金额',
	'delay_min_cash_msg'=>'每次延期金额最少金额填写错误',
	'delay_min_cash_title'=>'职位延期最少金额为1元',
	'limit_delay_day'=>'延期天数限制',
	'delay_day_msg'=>'每次最大延期天数不正确',
	'delay_day_title'=>'职位最大延期天数不得小于2天',
	'max_delay'=>'最大延期',
	'set_delay'=>'延期设置',
	'delay_rule_notice'=>'不低于悬赏总金额的',
	'delay_rule_msg'=>'比例填写错误',
	'delay_rule_title'=>'职位延期比例为0-100',
	'add_rule'=>'添加规则',
	'set_deliver'=>'交付设定',
	'sign_default_agreement_time'=>'协议默认签署时间',
	'confirm_default_agreement_time'=>'协议完成时间限制',
	'default_agreement_time_notice'=>'进入交付期后、超过此期限系统将默认双方签署协议。',
	'agree_time_more_than_1'=>'默认签署时限默认不得少于1天',
	'agree_complete_2'=>'默认完成时限默认不得少于2天,不得小于默认签署时间',
	'confrim_agreement_time_notice'=>'超过此期限雇佣双方未完成交付，交付过程将被冻结。一方提请仲裁后由客服介入。',
	'set_task_comment'=>'职位评论设置',
	'if_public'=>'是否公开',
	'if_public_checkbox'=>'(勾选则评论在职位进行中隐藏，职位结束公开)',
	'save_config'=>'保存设置',
/*task_priv.htm*/
	'project_name'=>'项目名称',
	'user_status'=>'用户身份',
	'limit_count'=>'次数限制',

/*task_config.htm*/
	'if_change_model_status'=>'是否为私有模型',
	'model_status_notice'=>'(私有模型不会出现在发布职位的选择列表上)',
	'bind_industry'=>'指定行业',
	'choose_industry'=>'选择行业',
	'choose_industry_nitice'=>'(如果指定行业后,则职位的行业类型将是这里指定行业类型；如果不指定行业，则职位类型将是系统指定的所有行业类型.)',
	'model_synopsis'=>'模型简介',
	'model_synopsis_notice'=>'(限50字节)',
	'model_description'=>'模型描述',
	'edit_time_last_time'=>'上次修改时间',
	'no_delete_the_first_rule'=>'第一条规则不能被删除!',
	'add_cash_rul_day_msg'=>'天数不能为空! 天数的长度1-2',
	' persist '=>'持续',
	'task_min_cash_show'=>'职位最小金额不正确，长度2-5',
	'add_adjourn_rul_msg'=>'百分比不能为空！',

);