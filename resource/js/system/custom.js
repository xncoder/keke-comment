
//jQuery

    $(function(){
		$(".top,.scrollTop").click(function(){
			$("html,body").animate({scrollTop: $("#pageTop").offset().top});
		})	
    		
    	//搜索栏聚焦
    	$(".togg").focus(function(){
    		$(this).removeClass("c999");
    		if(this.value==L.input_task_service||this.value=='输入任务'||this.value=='输入商品'){
    			this.value='';
			}
    		;
    	}).blur(function(){
    		$(this).addClass("c999");
    			this.value==''?this.value=$(this).attr(this.title?'title':'original-title'):'';
    	})
			$('.operate a,a.prev,a.next,a.small_nav,.border_n a ').not('.nav .operate a').hover(function(){
				$(this).children('.icon16').not('.deep_style .icon16,.deep_style .icon32').addClass("reverse");
				}, function(){
				$(this).children('.icon16').not('.deep_style .icon16,.deep_style .icon32').removeClass("reverse");
			});
			//评论鼠标移动事件显示工具栏
			$(".top1,.comment_item").hover(function(){
				$(this).children('.operate').removeClass('hidden');
				
			},function(){
				$(this).children('.operate').addClass('hidden');
			});
		
        //为表格内容区域添加奇偶行不同色
        $("table tbody tr:odd").not('table.jqTransformTextarea tr').addClass("odd");
        //为列表添加奇偶行不同色
        $(".list dd:odd").not('dd.tags').addClass("odd");
        //为列表隐藏工具栏
        $(".list dd").children('.operate').addClass('hidden');
        
        //为表格内容区添加鼠标事件
        $('table tbody tr,.list dd,.category_list .item,.case_con').not('.list dd.tags,table.jqTransformTextarea tr').hover(function(){
            $(this).addClass("hover").children('.operate').removeClass('hidden');
        }, function(){
            $(this).removeClass("hover").children('.operate').addClass('hidden');
        });
        
		$(".tar_comment").click(function(event){
			tarClick($(this),event);event.stopPropagation();
		})
		$(".tar_comment").blur(function(event){
			tarBlur($(this),event);event.stopPropagation();
		})
        
    	$(".tar_comment").live("click",function(event){
    		tarClick($(this),event);event.stopPropagation();
    	})
    	$(".tar_comment").live("blur",function(event){
    		tarBlur($(this),event);event.stopPropagation();
    	})
        var tarClick = function(obj,event){
        	if($(obj).val()== L.i_want_say){
        		$(obj).val('').css({height:"50px"}).next().show();
        	}
        	event.stopPropagation();
        }
        var tarBlur = function(obj,event){
        	$("html,body").click(function(event){
        		if(!$(event.target).hasClass("answer-zone")){
        			$(obj).val(L.i_want_say).css({height:"23px"}).next().hide().find(".answer_word").text(L.input_100_words);
        		}
        	})
        }
        
        
        var s = $('.messages');
        //msgshow(s);

        // 消息
        $('.messages .close').click(function() {
        	var s = $(this).parent('.messages');
        	msghide(s);
        });

        // 关闭消息
        function msghide(ele) {
        	ele.animate({
        		opacity : .01
        	}, 200, function() {
        		ele.slideUp(200, function() {
        			ele.remove();
        		});
        	});
        };
	
        // input框
        $(function(){
        	
        	$("input[type=text],input[type=password").addClass("txt_input"),
        	$("this").removeClass("search_input");
        });
     // icon图标
        $('.deep_style .icon16,.deep_style .icon32').addClass('reverse');
   //返回顶部
        
        $.waypoints.settings.scrollThrottle = 30;
        $('#wrapper').waypoint(function(event, direction){
            $('.top').toggleClass('fadeIn').toggleClass('hidden', direction === "up");
        },{
            offset: '-1%'
        });
        
      
        $(".shop .small_list li.item,.case_con,.example .text_ov,.goods_words,.goods,aside.mb_10 .grid_8").hover(
        		function(){$(this).css('z-index','2');},
        		function(){$(this).css('z-index','1');}
    	);

    });
    
  //菜单固定浮动
    if ($.browser.msie && ($.browser.version == "6.0") && !$.support.style && location.href.indexOf('do=browser') < 0) {
	}
	else {
    
        if ( $(".second_menu").length > 0 ) { 
        	
        	$('.section').waypoint(function(event, direction) {
    			$(this).children('.second_menu').toggleClass('fixed-top', direction === "down");
    			event.stopPropagation();
    		});
        } 
	}
    
var checkall = function(){
    if ($('#checkbox').attr('value') == 0) {
    	$("#checkbox").attr("value",1);
    	$('input[type=checkbox]').attr('checked', true);
    }  else {
    	$("#checkbox").attr("value",0);
        $('input[type=checkbox]').attr('checked', false);
    }

}
     //解决select 宣染
/*	function jq_select(){
	$("#reload_indus div.jqTransformSelectWrapper ul li a").click(function(){
			 $("#indus_id").removeClass("jqTransformHidden").css('display:none');
			 $("select").jqTransSelect().addClass("jqTransformHidden");
		});
	}*/
	
	/**
	 * 获取任务行业
	 * @param indus_pid
	 */
	function showIndus(indus_pid){
		if(indus_pid){
			$.post("index.php?do=ajax&view=indus",{indus_pid: indus_pid}, function(html){
				var str_data = html;
				if (trim(str_data) == '') {
					$("#indus_id").html('<option value="-1"> '+L.select_a_subsector+' </option>');
				}
				else {
					$("#indus_id").html(str_data);
					$("#reload_indus div.jqTransformSelectWrapper ul li a").triggerHandler("click");
				}
			},'text');
		}
	}


/**
 * 需求字数检查
 * 
 * @param obj
 *            需求对象
 * @param 最大长度
 */
function checkInner(obj,maxLength,e){
	var  len   = obj.value.length;
		e.keyCode==8?len-=1:len+=1;
		len<0?len=0:'';
	
	var Remain = Math.abs(maxLength-len);
 
	if(maxLength>=len){
       
        $("#length_show").text(L.has_input_length+len+','+L.can_also_input+Remain+L.word);
	}else{
		$("#length_show").text(L.can_input+maxLength+L.word+','+L.has_exceeded_length+Remain+L.word);
	}
}



/**
 * 
 * @param string  form 表单ID或者操作链接
 * @param int     type 操作类型，为链接时默认为1；为表单时为2；
 * @param boolean check 是否验证表单。默认为false，需验证请设置为true 
 */
function formSub(form,type,check){

	var t      = type=='form'?'form':'url';//操作类型 1为链接型，二为表单型
	var c      = check==true?true:false;//是否需验证表单 true为验证,默认为false
	var pass   = true;//默认为通过 ,当表单验证不过时为false;
	switch(t){
		case 'url'://链接
			var url = form;
			break;
		case 'form'://表单
			if(c==true){
				pass = checkForm(document.getElementById(form));
			}
			break;
	}
	if(pass==true){
		if(t=='url'){
			showWindow('sitesub',url,'get','0');return false;
		}else{
			showWindow('sitesub',form,'post','0');return false;
		}
	}
}