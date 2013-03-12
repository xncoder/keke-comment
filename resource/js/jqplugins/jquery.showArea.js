//-----------------------
//作者：亮亮
//邮件：ljlyy[@]126.com
//博客：www.94this.com.cn
//欢迎修改，有什么问题请到博客留言或邮件交流
//-----------------------
function showArea(){
	$("#addr").remove();
	var iptName=$(this).attr("id");
	var iptOffSet=$(this).offset();
	var iptLeft=iptOffSet.left-160;
	var iptTop=iptOffSet.top+20;
	var m_id = $("#hdn_model_id").val();
	var shop_mod = $("#shop_mod").val();
	m_id = isUndefined(m_id)?0:m_id;
	
	var s_list = m_id?"task_list":"search_list";
	if(isUndefined(shop_mod)){
		var lik = "index.php?do="+s_list+"&model_id="+m_id
	}
	else{
		var lik = "shop.php?do=service_list";
	}
	var str="<div id='addr'><span><a href='"+lik+"' target='_self'>全部城市</a><a id='fh'>返回省份列表</a><a id='gb'>[&nbsp;关闭&nbsp;]</a></span><ul></ul><div style='clear:both;'></div></div>";
	areasLen=provinceArr.length;
	var str2="";
	for(var i=0;i<areasLen;i++){
		//str2=str2+"<li id='p"+provinceArr[i][0]+"'><a href='index.php?area="+provinceArr[i][1]+"' target='_self'>"+provinceArr[i][1]+"</a></li>";
		str2=str2+"<li id='p"+provinceArr[i][0]+"'>"+provinceArr[i][1]+"</li>";
	}
	$("body").append(str);
	$("#addr ul").append(str2);
	$("#addr").css({left:iptLeft+"px",top:iptTop+"px"});
	$("#addr span a#fh").bind("click",function(){
		$("#addr ul").empty();
		$("#addr ul").append(str2);
		$("#addr ul li").bind("click",{iptn:iptName},liBind);
	});
	$("#addr span a#gb").bind("click",function(){
		$("#addr").remove();
	});
	$("#addr ul li").bind("click",{iptn:iptName},liBind);
}
function setVal(event){
	var setipt2=event.data.ipt2;
	var iptText=$(this).text();
	var iptVal=$(this).attr("id");
	$("#"+setipt2+"Val").val(iptVal.substring(1,5));
	$("#"+setipt2).val(iptText);
	$("#addr").css("display","none");
}
function liBind(event){
	var setipt=event.data.iptn;
	var liId=$(this).attr("id");
	var liText=$(this).text();
	var pArr=eval(liId);
	pLen=pArr.length;
	if(pLen==0){
		$("#"+setipt+"Val").val(liId.substring(1,5));
		$("#"+setipt).val(liText);
		$("#addr").css("display","none");
		}
	else{
		listr="";
		var m_id = $("#hdn_model_id").val();
		var shop_mod = $("#shop_mod").val();
		m_id = isUndefined(m_id)?0:m_id;
		var s_list = m_id?"task_list":"search_list";
		if(isUndefined(shop_mod)){
			var lik = "index.php?do="+s_list+"&model_id="+m_id
		}
		else{
			var lik = "shop.php?do=service_list";
		}
		for(j=0;j<pLen;j++){
			//地址链接设置,各个地址实际url
		listr=listr+"<li id='c"+pArr[j][1]+"'><a href='"+lik+"&city="+pArr[j][1]+"' target='_self'>"+pArr[j][1]+"</a></li>";
		//	listr=listr+"<li id='c"+pArr[j][0]+"'>"+pArr[j][1]+"</li>";
			}
			$("#addr ul").empty();
			$("#addr ul").append(listr);
			$("#addr ul li").bind("click",{ipt2:setipt},setVal);
		}	
}



