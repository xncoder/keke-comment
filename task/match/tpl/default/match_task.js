$(function(){
	var loading = parseInt($(".process li.selected").index()) + 1;
	$(".progress_bar").css("width", loading * 16.5 + "%");
	if(task_status==9){
		$(".progress_bar").css({width:"100%",background:"grey"}); 
	}

});
/** 威客抢标 */
function workHand() {
	if (check_user_login()) {
		if (uid == guid) {
			showDialog(L.operation_invalid_the_high_bids_released_task, 'alert', L.operate_notice, '', 0);
			return false;
		} else {
			showWindow("work_hand", basic_url + '&op=work_hand', "get", '0');
			return false;
		}
	}
}
/** 查看联系方式*/
function getContact(){
	if (check_user_login()) {
		if (uid!=guid&&uid!=wuid) {
			showDialog(L.non_employers_not_view_contact, 'alert', L.operate_notice, '', 0);
			return false;
		} else {
			showWindow("get_contact", basic_url + '&op=get_contact', "get", '0');
			return false;
		}
	}
}
/** 放弃投标*/
function giveUp(){
	if (check_user_login()) {
		if (uid!=wuid) {
			showDialog(L.non_tender_witkey_not_los_bid, 'alert', L.operate_notice, '', 0);
			return false;
		} else {
			var url = basic_url + "&op=work_give_up";
			showDialog(L.sure_give_up_tender,'confirm',L.operate_notice,function(){
				getJson(url,basic_url+'&view=work');
			});return false;
		}
	}
}
/** 发送提醒*/
function sendNotice(type){
	if (check_user_login()) {
		if (uid!=guid&&uid!=wuid) {
			showDialog(L.non_employment_can_not_send_message, 'alert', L.operate_notice, '', 0);
			return false;
		} else {
			var url = basic_url + "&op=send_notice&type=" + type;
			getJson(url);
		}
	}
}
/** 淘汰稿件*/
function workCancel(){
	if (check_user_login()) {
		if (uid!=guid) {
			showDialog(L.only_employers_can_out_work, 'alert', L.operate_notice, '', 0);
			return false;
		} else {
			var url = basic_url + "&op=work_cancel";
			showDialog(L.sure_out_user,'confirm',L.operate_notice,function(){
				getJson(url,basic_url+'&view=work');
			});return false;
		}
	}
}
/** 赏金托管*/
function taskHost(){
	if (check_user_login()) {
		if (uid!=guid) {
			showDialog(L.only_employers_host, 'alert', L.operate_notice, '', 0);
			return false;
		} else {
			var url = basic_url + "&op=task_host";
			showWindow('task_host',url,'get','0');return false;
		}
	}
}
/** 开始工作*/
function workStart(){
	if (check_user_login()) {
		if (uid!=wuid) {
			showDialog(L.only_tender_work_confirm, 'alert', L.operate_notice, '', 0);
			return false;
		} else {
			var url = basic_url + "&op=work_start";
			showDialog(L.start_work,'confirm',L.operate_notice,function(){
				getJson(url,basic_url+'&view=work');
			});return false;
		}
	}
}
/** 确认完工*/
function workOver(modify){
	if (check_user_login()) {
		if (uid!=wuid) {
			showDialog(L.only_tender_confirm_completion, 'alert', L.operate_notice, '', 0);
			return false;
		} else {
			var url = basic_url + "&op=work_over&modify="+modify;
			showWindow('work_over',url,'get','0');return false;
		}
	}
}
/** 工作验收*/
function taskAccept(){
	if (check_user_login()) {
		if (uid!=guid) {
			showDialog(L.only_employer_acceptance, 'alert', L.operate_notice, '', 0);
			return false;
		} else {
			var url = basic_url + "&op=task_accept";
			showDialog(L.confirm_acceptance_work,'confirm',L.operate_notice,function(){
				getJson(url,basic_url+'&view=work');
			});return false;
		}
	}
}
var timer='';
/** 倒计时*/
function cutClock(){
	if(uid!=wuid){ 
		var timeClock = setTimeout("cutClock();",1000);
		--cutclock;
		if(cutclock<2){
			$("#workhand").removeClass("disabled").bind("click",function(){
				workHand();
			});
			$("#cutdown").remove();
			clearTimeout(timer);
			clearTimeout(timeClock);
		}
	}
}
$(function(){
	if(uid&&uid!=guid&&task_status==2){
	
		cut_time();
		cutClock();
	}
})
function cut_time() {
    
	var ed = cutdown;
    
	var djs = d_time(ed);
  
	var str = L.has_left + djs[0] + L.day + djs[1] + L.hour + djs[2]
					+ L.minutes + djs[3] + L.seconds;

	$("#cutdown").children("span").html(str);
	timer=setTimeout("cut_time()", 1000);
}