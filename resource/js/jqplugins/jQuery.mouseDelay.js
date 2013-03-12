/*!
 * jQuery.mouseDelay.js v1.2
 * http://www.planeart.cn/?p=1073
 * Copyright 2011, TangBin
 * Dual licensed under the MIT or GPL Version 2 licenses.
 */
(function ($, plugin) {
	var data = {}, id = 1, etid = plugin + 'ETID';
	
	// 延时构造器
	$.fn[plugin] = function (speed, group) {
		id ++;	
		group = group || this.data(etid) || id;
		speed = speed || 150;
		
		// 缓存分组名称到元素
		if (group === id) this.data(etid, group);
		
		// 暂存官方的hover方法
		this._hover = this.hover;
		
		// 伪装一个hover函数，并截获两个回调函数交给真正的hover函数处理
		this.hover = function (over, out) {
			over = over || $.noop;
			out = out || $.noop;
			this._hover(function (event) {
				var elem = this;
				clearTimeout(data[group]);
				data[group] = setTimeout(function () {
					over.call(elem, event);
				}, speed);
			}, function (event) {
				var elem = this;
				clearTimeout(data[group]);
				data[group] = setTimeout(function () {
					out.call(elem, event);
				}, speed);
			});
			
			return this;
		};
		
		return this;
	};
	
	// 冻结选定元素的延时器
	$.fn[plugin + 'Pause'] = function () {
		clearTimeout(this.data(etid));
		return this;
	};
	
	// 静态方法
	$[plugin] = {
		// 获取一个唯一分组名称
		get: function () {
			return id ++;
		},
		// 冻结指定分组的延时器
		pause: function (group) {
			clearTimeout(data[group]);
		}
	};
	
})(jQuery, 'mouseDelay');