/**
 * 首页js
 */

$(function(){
	//网站统计
	$("#slider").easySlider({
		auto: true,
		continuous: true,
		vertical: true,
		speed: 200,
		nextId: "down",
		prevId: "up"
	});
	 
	//首页幻灯
	$('#slides').slides({
		  preload: true,
		  preloadImage: SITEURL+'/resource/img/system/loading.gif',
		  play: 5000,
		  pause: 2500,
		  hoverPause: true
	});
	$("#indus li a").click(function(){
		$(this).addClass("selected").parent().siblings().find("a").removeClass("selected");
	})

	var shopObj = $('.shop .box_detail .small_list a:not(.first a,.last a)');
		shopObj.live("mouseover",function(){
		var imgObj = $(this).children('img');
			imgObj.attr('src',imgObj.attr("big"));
			$(this).parent().css('z-index','2');
		});
		shopObj.live("mouseout",function(){
		var imgObj = $(this).children('img');
			imgObj.attr('src',imgObj.attr("small"));
			$(this).parent().css('z-index','1');
		});
	//商品
});
function indusLinkInit(list_type){
	
	$(".indus_list>li").each(function(){
		if($(this).attr("id")=="s_all_li"){
			$(this).find("a").each(function(i,n){
				href = n.href;
				if(list_type=='task_list'){ 
					var url = href.replace('shop_list',list_type);
				}else{
					var url = href.replace('task_list',list_type);
				}
				n.href=url;
			})
		}else{
			href= $(this).find('a').attr('href');
			if(list_type=='task_list'){ 
				var url = href.replace('shop_list',list_type);
				 $(this).find('a').attr("class","");
			}else{
				var url = href.replace('task_list',list_type);
				 $(this).find('a').attr("class","purple");
			} 
			$(this).find('a').attr('href',url);
		}
	})
	
}