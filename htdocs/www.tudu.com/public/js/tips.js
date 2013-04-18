(function(){

$.tips = function(param) {
	if (typeof(param) == 'object') {
		this._settings = $.extend({}, param);
	}
	
	this.init();
};

$.tips.prototype = {
	/**
	 * 对象支持事件列表
	 */
	_events: {'show': true, 'close': true},
	
	/**
	 * 时间回调列表
	 */
	_eventCallback: null,
	
	/**
	 * 
	 */
	_settings: null,
	
	/**
	 * 
	 */
	closeClass: 'close',
	
	/**
	 * 提示父元素
	 */
	parent: null,
	
	/**
	 * DOM元素ID全椎
	 */
	idPrefix: 'tips-',
	
	/**
	 * 
	 */
	_$: null,
	
	/**
	 * 初始化对象
	 */
	init: function() {
		var scope = window;
		if (this._settings.frame) {
			scope = $(this._settings.frame)[0].contentWindow;
			this._$ = scope.jQuery;
		} else {
			this._$ = $;
		}
		
		this.parent = this._$(this._settings.parent ? this._settings.parent : scope.document.body);
		var position = this.parent.css('position');
		if (position !== 'absolute' && position !== 'relative') {
			this.parent.css({'position': 'relative', 'z-index': 800});
		}
		
		this._eventCallback = {};
		
		this._initEl();
		if (this._el) {
			this._el.appendTo(this.parent[0]);
		}
	},
	
	/**
	 * 初始化DOM对象
	 */
	_initEl: function() {
		var _this = this;
		this._el = this._$('<div>', {'class': 'tips'});
		this._close = this._$('<a>', {'title': 'Close', 'class': 'icon icon-close', 'href': 'javascript:void(0);'});
		this._arrow = this._$('<i>');
		this._dl = this._$('<dl>');
		this._icon = this._$('<span>', {'class': 'tips-icon'});
		
		this._el.append(this._icon)
				.append(this._close.addClass(this.closeClass))
		        .append(this._arrow)
		        .append(this._dl);
		for (var k in this._settings) {
			switch (k) {
				case 'id':
					this._el.attr('id', this.idPrefix + this._settings[k]);
					break;
				case 'cls':
					this._el.addClass(this._settings[k]);
					break;
				case 'width':
				case 'height':
				case 'top':
				case 'left':
				case 'right':
				case 'bottom':
					this._el.css(k, this._settings[k]);
					break;
				case 'depend':
					var e = this._$(this._settings[k]);
					if (!e.size()) {
						this._el = null;
						return this._el;
					}
					break;
				case 'under':
				case 'above':
				case 'leftside':
				case 'rightside':
					var o = this._$(this._settings[k]);
					if (!o.size()) {
						this._el = null;
						return this._el;
					}
					
					var offset = o.offset(),
					h = o.height(),
					w = o.width();
					
					var left = (k == 'under' || k == 'above') 
							 ? w/2 + offset.left - 20 
						     : (k == 'leftside') ? offset.left - 260 : offset.left + w + 10;

					var top = (k == 'under' || k == 'above') ? h + offset.top : (h/2 + offset.top) - 45;
					
					if (k == 'above') {
						top = offset.top - 95;
					}
					this._el.css({'left': left, 'top': top});
					break;
				case 'arrow':
					this._arrow.addClass('arrow-'+this._settings[k]);
					break;
				case 'title':
					this._dl.append('<dt>' + this._settings[k] + '</dt>');
					break;
				case 'content':
					this._dl.append('<dd>'+this._settings[k]+'</dd>');
					break;
				case 'onclose':
				case 'onClose':
					this.addEventListener('close', this._settings[k]);
					break;
				case 'onshow':
				case 'onShow':
					this.addEventListener('show', this._settings[k]);
					break;
			}
		}
		
		if (this._el) {
			if (this._$.browser.msie && this._$.browser.version < 7) {
				this._el.append('<iframe frameborder="0" src="javascript:\'\';" style="height:100%;display:none;"></iframe>');
			}
			
			this._el.find('.' + this.closeClass).bind('click', function(){
				_this.close(true);
			});
		}
		
		return this._el;
	},
	
	/**
	 * 添加绑定事件
	 */
	addEventListener: function(event, callback) {
		if (typeof(this._events[event]) !== 'undefined'
			&& typeof(callback) == 'function')
		{
			if (undefined == this._eventCallback[event]) {
				this._eventCallback[event] = [];
			}
			
			this._eventCallback[event].push(callback);
		}
	},
	
	/**
	 * 移除绑定事件
	 */
	removeEventListener: function(event, callback) {
		var _this = this;
		if (typeof(_this._eventCallback[event]) == 'undefined') {
			return this;
		}
		
		if (typeof(callback) != 'function') {
			var arr = _this._eventCallback[event],
				ret = [];
			for (var i = 0, c = arr.length; i < c; i++) {
				if (callback != arr[i]) {
					ret.push(arr[i]);
				}
			}
			
			_this._eventCallback[event] = ret;
		} else {
			delete _this._eventCallback[event];
		}
		
		return this;
	},
	
	/**
	 * 触发事件
	 */
	_triggerEvent: function(event) {
		if (undefined != this._eventCallback[event]) {
			for(var i = 0, c = this._eventCallback[event].length; i < c ; i++) {
				this._eventCallback[event][i].call(this);
			}
		}
	},
	
	/**
	 * 显示提示
	 */
	show: function() {
		if (this._el) {
			this._el.fadeIn();
			
			this._triggerEvent('show');
		}
	},
	
	/**
	 * 关闭显示
	 */
	close: function(isRemove) {
		if (this._el) {
			var _this = this;
			this._el.fadeOut('normal', function(){
				if (isRemove) {
					_this._el.remove();
				}
			});
			
			this._triggerEvent('close');
		}
	}
};

/**
 * 函数方式调用
 */
$.fn.tips = function(param) {
	param.parent = this;
	return new $.tips(param);
}

})(jQuery);

var _TIPS_WIN = '<div class="pop_wrap" style="display:none;position:absolute;background:#ebf4d8;"><div class="pop"><div class="pop_header"><strong></strong><span class="icon icon_close close"></span></div><div class="pop_body"></div><div class="pop_footer"></div></div></div>';

var Tips = {
	
	_tips: {},
	
	_queue: {},
	
	unshow: function(path, id) {
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: '/frame/tips?status=1&tipsid=' + id,
			success: function(){},
			error: function(){}
		});
		
		if (this._tips && this._tips[path]) {
			for (var i = 0, c = this._tips[path].length; i < c; i++) {
				if (this._tips[path][i] && this._tips[path][i].id == id) {
					this._tips[path][i] = null;
					break ;
				}
			}
		}
		
		return this;
	},
	
	/**
	 * 
	 */
	show: function(id) {
		var data = this._tips[id],
			t    = null;
		
		if (undefined === this._tips[id]) {
			this._queue[id] = true;
			return ;
		}
		
		switch (data.type) {
			case 'window':
				var t = Tips.window(data);
				break;
			case 'tips':
			default:
				var t = new $.tips(data);
				if (!data.autoclean) {
					t.addEventListener('close', function(){Tips.unshow(data.path, this._settings.id);});
				}
				break;
		}
		
		if (!t) {
			return ;
		}
		
		t.show();
		
		if (data.timeout) {
			var time = parseInt(data.timeout);
			setTimeout(function(){Tips.unshow(path, id)}, time);
		}
		
		if (data.autoclean) {
			Tips.unshow(data.path, id);
		}
		
		delete this._tips[id];
	},
	
	/**
	 * 显示提示
	 */
	showTips: function(data) {
		var _this = this;
		for (var i = 0, c = data.length; i < c; i++) {
			if (!data[i]) {
				continue ;
			}
			
			this._tips[data[i].id] = data[i];
			
			if (data[i].autoshow === "false"
				&& !this._queue[data[i].id])
			{
				continue ;
			}
			
			this.show(data[i].id);
			this._queue[data[i].id] = false;
		}
	}
};

/**
 * 支持对话框提醒
 */
Tips.window = function(param) {
	var o = $(_TIPS_WIN),
		id = param.id;
	if (param.title) {
		o.find('div.pop_header strong').html(param.title);
	}
	
	if (param.content) {
		o.find('div.pop_body').html(param.content);
	}
	
	if (undefined !== param.width) {
		param.width = parseInt(param.width);
	}
	
	$(param.parent).append(o);
	var win = o.window(param);
	
	return win;
};