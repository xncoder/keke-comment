$(function(){
	var loading = parseInt($(".process li.selected").index()) + 1;
	$(".progress_bar").css("width", loading * 20 + "%");
})

/** 稿件提交 */
function workHand() {
	if (check_user_login()) {
		if (uid == guid) {
			showDialog(L.t_hand_forbidden, 'alert',L.operate_notice, '', 0);
			return false;
		} else {
			var is_bided = parseInt($("#is_bided").html());
			if(is_bided==0){
				showDialog(L.t_have_handed,'alert',L.operate_notice,'',0);
			}else{
				showWindow("work_hand",basic_url+'&op=work_hand',"get",'0');return false;
			}			
		}
	}
}
/**
 * 选择稿件
 * @param work_id 稿件编号
 * @param to_status 变更状态
 * @returns {Boolean}
 */
function workBid(work_id,to_status){
	if(guid!=uid){
		showDialog(L.t_master_can_operate_work,"alert",L.operate_notice);return false;
	}else{
		var url=basic_url+'&op=work_choose&work_id='+work_id;
			$.post(url,{to_status:to_status},function(json){
				if(json.status==1){					
					$(".work_pass").remove();$("#work_7_"+work_id).remove();					
					var divStatus=$('<div class="work_status_big work_'+to_status+'_big qualified_big1 po_ab"></div>');
					$("#"+work_id).find(".work_status_big").remove();
					divStatus.appendTo($("#"+work_id));
					showDialog(json.data,"right",json.msg,"location.href='" + basic_url + "&view=work'");return false;
				}else{
					showDialog(json.data,"error",json.msg);return false;
				}
			},'json')
	}
}

/**
 * 稿件编辑
 * @param work_id
 */
function workEdit(work_id) {
	if (check_user_login()) {		
		showWindow("work_edit",basic_url+'&op=work_edit&bid_id='+work_id,"get",'0');return false;
	}
}
/**
 * 赏金托管
 * @returns {Boolean}
 */
function task_pay(){
	if(check_user_login()){
		if(uid!=guid){
			showDialog(L.t_only_master_can_host_amount,'error',L.operate_notice);return false;
		}else{
			var url = basic_url + "&op=hosted_amount";
			showWindow('hosted_amount',url,'get',0);return false;
		}
	}
}

/**
 * 确认计划完成(威客)
 * @returns {Boolean}
 */
function plan_complete(plan_id,plan_step){
	if(check_user_login()){
		var url = basic_url +"&op=plan_complete";
		$.post(url,{plan_id:plan_id,plan_step:plan_step},function(json){
			if(json.status==1){
				$("#complate_"+plan_id).remove();
				$("#plan_status_"+plan_id).html(L.t_wait_pay);
				showDialog(json.data,"right",json.msg);return false;
			}else{
				showDialog(json.data,"alert",json.msg);return false;
			}
		},'json')
	}
}

/**
 * 确认付款
 * @returns {Boolean}
 */
function plan_confirm(plan_id,plan_step){
	if(check_user_login()){
		var url = basic_url +"&op=plan_confirm";
		$.post(url,{plan_id:plan_id,plan_step:plan_step},function(json){
			if(json.status==1){
				$("#confirm_"+plan_id).remove();
				$("#plan_status_"+plan_id).html(L.t_work_over);
				showDialog(json.data,'right',json.msg);return false;
			}else{
				showDialog(json.data,'error',json.msg);return false;
			}
		},'json')
	}
}

//添加任务计划
function add_task_plan(){	
	var i = parseInt($("#plan i:last").html());	
	var k = i+1;	
	if(k>5){
		showDialog(L.t_work_plan_stage_limit,'error',L.operate_notice);return false;
	}else{		
		var append_html = "<div id=\"plan_step_"+k+"\" name=\"plan_step_"+k+"\" class=\"pb_10 pl_10\">"+
		"<div class=\"rowElem clearfix\">"+
		"<label>"+L.t_plan_amount+"：</label>"+
		"<input type=\"text\" size=\"3\" name=\"plan_amount[]\" class=\"txt_input\" id=\"plan_amount_"+k+"\" value=\"\" onkeyup=\"clearstr(this)\" limit=\"required:true;type:float\" maxlength=\"5\" msg=\""+L.t_plan_amount_fill_error+"\" tilte=\""+L.t_fill_in_plan_amount+"\" msgArea=\"span_plan_cash_"+k+"\">"+
		"<span class=\"ml_5\">元</span>"+
		"<label>,"+L.t_start_time+"：</label>"+
		"<input type=\"text\" size=\"9\" name=\"start_time[]\" class=\"txt_input\" id=\"start_time_"+k+"\" onkeyup=\"clearstr(this)\" limit=\"required:true;type:date;than:end_time_"+i+"\" maxlength=\"12\"  onclick=\"showcalendar(event, this, 0)\" msg=\""+L.t_start_time_fill_error+"\" tilte=\""+L.t_fill_in_start_time+"\" msgArea=\"span_plan_cash_"+k+"\">"+
		"<label>,"+L.t_end_time+"：</label>"+
		"<input type=\"text\" size=\"9\" name=\"end_time[]\" class=\"txt_input\" id=\"end_time_"+k+"\" onkeyup=\"clearstr(this)\" limit=\"required:true;type:date;than:start_time_"+k+"\" maxlength=\"12\"  onclick=\"showcalendar(event, this, 0)\" msg=\""+L.t_end_time_fill_error+"\" tilte=\""+L.t_fill_in_end_time+"\" msgArea=\"span_plan_cash_"+k+"\">"+
		"<label>,"+L.t_work_target+"：</label>"+
		"<input type=\"text\" size=\"11\" name=\"plan_title[]\" class=\"txt_input\" id=\"plan_target_"+k+"\" value=\"\" limit=\"required:true\" maxlength=\"20\" msg=\""+L.t_target_fill_error+"\" tilte=\""+L.t_fill_in_target+"\" msgArea=\"span_plan_cash_"+k+"\">"+
		"<button type=\"button\"  class=\"mt_5\" value=\""+L.del+"\" id=\"del_plan\" name=\"del_plan\" onclick=\"del_task_plan("+k+");\" >"+L.del+"</button>"+
	"</div><span id=\"span_plan_cash_"+k+"\"></span><i style=\"display:none;\">"+k+"</i></div>"		
		$("#plan_add").append(append_html);
	}
	form_valid(); 
}

//删除融资规则
function del_task_plan(k){
	$("div #plan_step_"+k).remove();
}
//检验计划金额与总金额是否相符
function check_cash(){
	var totle_cash = parseFloat($("#quote").val());
	var i = parseInt($("#plan i:last").html());
	var rule_cash=0;
	for(var j=1;j<=i;j++){		
		var cash = parseFloat($("#plan_amount_"+j).val());		
		rule_cash +=cash;
	}	
	if(rule_cash!=totle_cash){
		showDialog(L.t_reset_plan_amount,'error',L.operate_notice,'$("#plan_amount_1").focus()',0);		 
		return false;
	}else{
		return true;
	}
	
}