

$(function(){
			var x = 5;
			var y = 5;		
			$("a.file_down").each(function(){
				var href = $(this).attr('temp');
				var aa = href.split('.');
				var exten = aa[1];				
				if(is_display(exten)){
					$(this).mouseover(function(e){						
						var divAppend="<div id='tooltip'><img src='"+ href +"' style='width:70%;border-radius:5px;box-shadow:2px 2px 2px gray;' /></div>";						
						$("body").append(divAppend);
						$("#tooltip").css({
							"position":"absolute",
							"z-index":40,
							"top":(e.pageY+y)+'px',
							"left":(e.pageX+x)+'px'
						}).show('fase');
					}).mouseout(function(){
						$("#tooltip").remove();
					})
				}				
			})
			var loading = parseInt($(".process li.selected").index()) + 1;
			$(".progress_bar").css("width", loading * 33.3 + "%");
			if(task_status==9){
				$(".progress_bar").css({width:"100%",background:"grey"}); 
			}
			
		})
		
		function is_display(str){
			var aa = 0;
			var ext_display = Array('jpg','bmp','png','gif');	
			var length = ext_display.length;
			for(var i=0;i<length-1;i++){
				if(ext_display[i]==str){
					aa = 1;
				}
			}
			return aa;
		}

/** 稿件提交 */
function workHand() {
	if (check_user_login()) {
		if(if_can_hand==0){
			showDialog(L.t_work_num_than_expected,'alert',L.operate_notice,'',0);
			return false;
		}else{
			if (uid == guid) {
				showDialog(L.t_hand_forbidden, 'alert',L.operate_notice, '', 0);
				return false;
			} else {
				showWindow("work_hand",basic_url+'&op=work_hand',"get",'0');return false;
			}
		}
		
	}
}



/**
 * 计件悬赏选择稿件
 * @param work_id 稿件编号
 * @param to_status 变更状态
 * @returns {Boolean}
 */
function workChoose(work_id,to_status){
	if(guid!=uid){
		showDialog(L.t_master_can_operate_work,"alert",L.operate_notice);return false;
	}else{
		var url = basic_url+"&op=work_choose&work_id="+work_id;
		//alert(url);
		$.post(url,{to_status:to_status},function(json){
			if(json.status==1){ 
				$("#work_6_"+work_id).remove();
				$("#work_7_"+work_id).remove();
				var divstatus = $("<div class='work_status_big work_"+to_status+"_big qualified_big1 po_ab'></div>");
				divstatus.appendTo($("#"+work_id));
				showDialog(json.data,'right',json.msg);return false;
			}else{
				showDialog(json.data,'alert',json.msg);return false;
			}
		},'json')
	}
}

/**
 *取消稿件中标
 */

function workCancel(work_id){
	var url = basic_url+"op=work_cancel&work_id="+work_id;
	$.post(url,'',function(json){
		if(json.status==1){
			$("#work_cancel_"+work_id).remove();
			showDialog(json.data,'right',json.msg);return false;
		}else{
			showDialog(json.data,'alert',json.msg);return false;
		}
	},'json')
}