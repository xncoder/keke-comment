<?php
/**
 * 普通招标职位配置页语言包
 * @version kppw2.0
 * @author deng
 * @2011-12-22
 */
$lang = array(
/*task_config.php*/
      'update_success'=>'修改成功',
      'update_fail'=>'修改失败',
      'permissions_config_update_success'=>'权限配置修改成功！',
      'has_update_tender_task'=>'修改了普通招标职位的',
/*task_config.htm*/

      'industry_binding'=>'指定行业',
      'choose_industry'=>'选择行业',
      'industry_binding_notice'=>'(如果指定行业后,则职位的行业类型将是这里指定行业类型；如果不指定行业，则职位类型将是系统指定的所有行业类型.)',
      'model_about'=>'模型简介',
      'limit_50_bytes'=>'限50字节',
      'model_description'=>'模型描述',
      'last_modify'=>'上次修改时间',
      'task_min_money_error'=>'职位最小金额不正确，长度2-5',
      'yuan_over'=>'元以上',
      'continue'=>'持续',
      'day_not_null'=>'天数不能为空! 天数的长度1-2',
      'first_rule_not_delete'=>'第一条规则不能被删除！',
      'no_less_than_reward_total'=>'次 不低于悬赏总金额的',
      'percent_not_null'=>'百分比不能为空！',

	  'cash_cove_config'=>'金额区间设置',
	  'add_new_cove'=>'添加新区间',
      'edit_tender_interval'=>'编辑区间',
      'tender_interval_edit_success'=>'招标区间编辑成功！',
      'add_tender_interval'=>'添加招标区间',
      'cash_interval'=>'金额区间',
      'interval_description'=>'区间描述',
	  'start_cove_can_not_be_null'=>'开始金额不得为空',
	  'enter_start_cove'=>'请填写开始金额',
	  'end_cove_can_not_be_null'=>'结束金额不得为空,且须比开始区间大',
	  'enter_end_cove'=>'请填写结束金额',

/*task_priv.htm*/
      'item_name'=>'项目名称',     
      'user_identity'=>'用户身份',
      'times_limit'=>'次数限制',


/*task_control.htm*/
      'task_commission_strategy'=>'职位佣金策略',
      'tender_audit_set'=>'招标审核设定',
      'open_audit'=>'开启审核',
      'closed_audit'=>'关闭审核',
      'audit_notice'=>'(如果开启审核，职位发布后需要管理员审核通过后才会发布)',
      'platform_service_cost'=>'平台服务费',
      'task_royalty_lenght_notice'=>'职位提成为正整数，长度：1-3',
      'task_royalty_is_positive_integer'=>'职位提成为正整数',
      'service_cost_notice'=>'(发布普通招标职位时网站要收取的服务费，按每笔计算,0为不收费)',
      'task_time_rule_set'=>'职位时间规则设定',
      'bid_max_time_set'=>'投标最大时间设置',
      'time_input_error'=>'时间输入有误',
      'task_bid_time_notice'=>'职位投标时间最少为1天，最多30天',
      'bid_time_notice'=>'天(投标时间不与招标预算关联，这里只设置最大值,职位投标时间最少为1天，最多30天)',
      'task_choose_works_time_notice'=>'天(职位选稿时间最少为1天，最多7天)',
      'choose_max_time_set'=>'选标最大时间设置',
      'task_choose_tender_notice'=>'职位选标时间最少为1天，最多7天',
      'in_a_bid_to_choose'=>'竞标中选标'
);