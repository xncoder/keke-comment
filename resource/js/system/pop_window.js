/**
 * 弹窗公用js
 */
var s = $('.messages');
msgshow(s);

// 消息
$('.messages .close').click(function() {
	var s = $(this).parent('.messages');
	msghide(s);
});

// 显示消息
function msgshow(ele) {
	var t = setTimeout(function() {
		ele.slideDown(200);
		clearTimeout(t);
	}, 400);
};
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