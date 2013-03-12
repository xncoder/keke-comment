//用此程序时，需要jquery包配合使用，因为用到部分jquery的东西
//需要延时加载的图片要这样定义：<img name="lazyImg" lazy_src="<%=photourl%>" width="110" height="83" border="0" alt="<%=trmNm%>">
var allImgObjs = new Array();
var times = 400;//检查滚动条高度的时间间隔
var myTimer =null;
var myTop = -1;
var currentTop = 0;
var allImgObjs;

//将上面的代码注释掉，是因为我站代码底部有js需要延时加载；如果用上面的代码，则需要在底部延时加载的代码加载完才会加载图片；
//将上面的代码封装成下面的函数，可以在任意地方调用
function loadPics(){
	allImgObjs = $("img[name='lazyImg']");	
	LazyImg();
}

function LazyImg()
{
	var scrollsTop =GetScrollTop();//滚动据页顶距离
	var winTop = $(window).height();//当前窗口高度。
	currentTop = scrollsTop-(-winTop);//待加载位置高度
	if(myTop != currentTop )
	{
		clearInterval(myTimer);		
		myTop = currentTop;//当前位置
		for(var i=0;i<allImgObjs.length;i++)
		{
			if(currentTop > $(allImgObjs[i]).offset().top)//高于图片所在位置
			{
				if($(allImgObjs[i]).attr("src") == null || $(allImgObjs[i]).attr("src").length < 1)
				{
					$(allImgObjs[i]).attr("src",$(allImgObjs[i]).attr("lazy_src"));
//加"?"+new Date()是取随机数。加个随机数，主要是考虑图片每次可以保证从服务器上下载最新的，不过这样做的代价是浏览器每次都是从服务器上下载图片，而不是读缓存
//					$(allImgObjs[i]).attr("src",$(allImgObjs[i]).attr("lazy_src"));
				}				
			}			
		}
		myTimer = setInterval(function(){LazyImg()},times);
	}
	else
	{
		clearInterval(myTimer);
		myTimer = setInterval(function(){LazyImg()},times);
	}
}
function IeTrueBody(){
return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body;
}
function GetScrollTop(){
  return $.browser.msie? IeTrueBody().scrollTop : window.pageYOffset;
}