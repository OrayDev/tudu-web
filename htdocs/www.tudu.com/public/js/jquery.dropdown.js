/**
 * 
 * jQuery Dropdown Menu plugin
 * 
 * Copyright (c) 2010 - 2010 http://www.oray.com
 * $Id$
 */

/**
 * 
 * @class $.Dropdown
 * @constructor
 * @params Object setting
 */
$.Dropdown = function(setting) {
	$.extend(this, $.Dropdown.defaults, setting || {});
	
	this.init();
};

/**
 * 默认的设置项
 * 
 * target    触发元素
 * wrapCls   外围容器 css Class
 * menuCls   菜单css Class
 * menuCss   菜单inner Css样式
 * separaCls 分隔线css Class
 * itemCls   菜单项css Class
 * maxHeight 菜单最大高度
 * maxWidth  菜单最大宽度
 * width     固定宽度
 * anime     动画显示
 * onShow    菜单下拉时触发
 * onSelect  菜单项选中时触发
 */
$.Dropdown.defaults = {
	id: null,
	target: null,
	menuCls: 'option-menu',
	menuCss: '',
	wrapCls: 'option-menu-wrap',
	menuBody: null,
	separaCls: 'menu-step',
	itemCls: 'menu-item',
	itemHoverCls: 'menu-over',
	maxHeight: null,
	maxWidth: null,
	offsetLeft: 0,
	offsetTop: 0,
	width: null,
	anime: 'slide',
	animeSpeed: 'fast',
	items: [],
	onShow: function(){},
	onSelect: function(){},
	onHide: function(){},
	resetpos: null,
	separate: null,
	order: null
};

(function($){

$.Dropdown.prototype = {

	/**
	 * 菜单对象
	 */
	_menu: null,
	
	/**
	 * 触发元素的jQuery对象
	 */
	_target: null,
	
	/**
	 * 菜单是否可用
	 */
	_enabled: true,
	
	/**
	 * 当前菜单是否显示
	 */
	isShow: false,
	
	/**
	 * 
	 */
	onShow: function(){},
	
	/**
	 * 
	 */
	onSelect: function(){},
	
	/**
	 * 隐藏菜单触发事件
	 */
	onHide: function(){},
	
	/**
	 * 初始化
	 */
	init: function() {
		var _this = this;
		
		if (this.target) {
			this._target = $(this.target);
			
			this._target.bind('click', function(e){
				
				if (_this._enabled) {
					_this.toggle(e);
				} else {
					_this.hide();
				}
				
				e.cancelBubble = true;
				if (e.stopPropagation) {
					e.stopPropagation();
				}
			});
		}
		
		$(document.body).bind('click', function(){
			_this.hide();
		});
	},
	
	/**
	 * 更新是否创建分隔线
	 */
	updateSeparate: function(val) {
		this.separate = val;
		
	},
	
	/**
	 * 
	 */
	_initMenu: function() {
		this._wrap = $('<div>').addClass(this.wrapCls);
		this._menu = $('<div>');
		
		if (this.id) {
			this._wrap.attr('id', this.id);
		}

		this._wrap.css({'position': 'absolute', 'display': 'none'});
		this._menu.addClass(this.menuCls);

		if (this.maxWidth) {
			this._wrap.css('width', this.maxWidth);
			if (!this.menuCss) {
				this.menuCss = {'width': (parseInt(this.maxWidth.replace('px', '')) - 2) + 'px'};
			} else {
				this.menuCss.width = (parseInt(this.maxWidth.replace('px', '')) - 2) + 'px';
			}
		}

		this._menu.css(this.menuCss);
		if (this.menuBody) {
			this._menu.append(this.menuBody);
		}

		if (this.items && this.items.length) {
			this._initMenuItem();
		}

		this._wrap.append(this._menu);
		$(document.body).append(this._wrap);
		
	},

	/**
	 * 初始化菜单项目
	 */
	_initMenuItem: function() {
		for (var i = 0, c = this.items.length; i < c; i++) {
			this.addItem(this.items[i]);
		}
	},
	
	/**
	 * 显示菜单
	 */
	show: function(e) {
		if (!this._menu) {
			this._initMenu();
		}
		
		/*if (this.separate) {
			this._wrap.find('.option-menu .menu-item').each(function() {
				if (!$(this).next().size()) {return ;}
				if ($(this).attr('issystem') != $(this).next().attr('issystem')) {
					$(this).after('<div class="menu-step"></div>');
				}
			});
			this.separate = false;
		}*/
		
		this._wrap.css({'left': '-9999px'}).show();
		
		var srcEle = e.srcElement ? $(e.srcElement) : $(e.target),
			o      = this._target ? this._target : srcEle,
			offset = o.offset(),
			oH     = o.outerHeight(),
			mH     = this._wrap.height(),
			bodyH  = $(window).height(),
			sTop   = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop;
		this._wrap.hide();
		
		// 超过浏览器高度时，显示在上面
		if (offset.top + oH + mH < bodyH || offset.top - sTop < mH || this.alwaysBottom) {
			this._wrap.css({'top': offset.top + oH + this.offsetTop + 'px', 'left': offset.left + this.offsetLeft});
		} else {
			this._wrap.css({'top': offset.top - mH + this.offsetTop + 'px', 'left': offset.left + this.offsetLeft});
		}
		
		if (this.maxHeight && mH > this.maxHeight) {
			this._wrap.find('.option-menu').css({'height': this.maxHeight});
		}
		
		if (this.anime) {
			var _this = this,
				func  = null;
			switch (this.anime) {
				case 'fade':
					func = 'fadeIn';
					break;
				case 'slide':
				default:
					func = 'slideDown';
					break;
			}
			this._wrap[func].call(this._wrap, this.animeSpeed, function(){_this.isShow = true;});
		} else {
			this._wrap.show();
		}
		
		this.isShow = true;
		
		this.onShow();
		
		if (this.resetpos) {
			var offset = this._target.offset();
			this._wrap.css({
				left: offset.left + this._target.outerWidth(true) + 'px',
				top: offset.top + 'px'
			});
			var wf = this._wrap.offset(),
				mH = this._wrap.find('.option-menu').height(),
				dH = $(document.body).height();
			if (wf.top + mH > dH) {
				this._wrap.css({'top': wf.top - (wf.top + mH - dH) - 20 + 'px'});
			}
		}
		
		return this;
	},
	
	/**
	 * 隐藏菜单
	 */
	hide: function() {
		if (!this._menu) {
			return this;
		}

		if (!this.isShow) {
			return ;
		}
		
		if (this.anime) {
			var _this = this,
				func  = null;
			switch (this.anime) {
				case 'fade':
					func = 'fadeOut';
					break;
				case 'slide':
				default:
					func = 'slideUp';
					break;
			}
			this._wrap[func].call(this._wrap, this.animeSpeed, function(){_this.isShow = false;});
		} else {
			this._wrap.hide();
		}
		
		this.isShow = false;
		
		this.onHide();
		
		return this;
	},
	
	/**
	 * 添加菜单项
	 */
	addItem: function(params) {
		var _this = this;
		if (params == '-') {
			this._menu.append('<div class="' + this.separaCls + '"></div>');
			return this;
		}
		
		var item = $('<div>').addClass(this.itemCls);
		
		for (var k in params) {
			switch (k) {
			case 'body':
				item.html(params[k]);
				break;
			case 'event':
				for (var k in params.event) {
					if (typeof(params.event[k]) == 'function') {
						item.bind(k, function(){params.event[k].call(item)});
					}
				}
				break;
			case 'data':
				item.data = params.data;
				break;
			default:
				item.attr(k, params[k]);
			}
		}
		
		item
		.bind('click', function(){_this.onSelect.call(item);})
		.bind('mouseover', function(){$(this).addClass(_this.itemHoverCls);})
		.bind('mouseout', function(){$(this).removeClass(_this.itemHoverCls);});
		
		this._menu.append(item);
		
		/*if (typeof count != 'undefined' && count && typeof params['name'] != 'undefined') {
			if (params['name'] == 'sent' || params['name'] == 'ignore') {
				return false;
			}
			if (params['name'] != 'starred' || params['name'] != 'todo' || params['name'] != 'review') {
				if (params['name'] == 'drafts') {
					count[1] = count[0];
				}
				
				if (count[1] > 0) {
					item.find('.lab_tudu_count').text('(' + count[1] + ')');
					if (params['name'] != 'drafts') {
						item.addClass('b');
					}
				} else {
					item.find('.lab_tudu_count').text('');
					item.removeClass('b');
				}
			}
			
			if (params['name'] == 'starred' || params['name'] == 'todo' ||  params['name'] == 'review') {
				_this.updateCount(item, count);
			}
		}*/
	},
	
	/**
	 * 更新标签未读数
	 */
	/*updateCount: function(obj, count) {
		if (count[1] > 0) {
			obj
			.find('.lab_tudu_count')
			.text('(' + count[1] + '/' + count[0] + ')');
		} else if(count[0] == 0){
			obj
			.find('.lab_tudu_count')
			.text('');
		} else {
			obj
			.find('.lab_tudu_count')
			.text('(' + count[0] + ')');
		}
		if (count[1] > 0) {
			obj.addClass('b');
		} else {
			obj.removeClass('b');
		}
	},*/
	
	/**
	 * 清除菜单
	 */
	clear: function() {
		if (this._wrap) {
			this._wrap.find('.option-menu').empty();
		}
	},
	
	/**
	 * 菜单内容
	 */
	setBody: function(body) {
		if (this._wrap) {
			this._wrap.find('.option-menu').html(body);
		}
	},
	
	/**
	 * 
	 */
	toggle: function(e) {
		if (this.isShow) {
			this.hide();
		} else {
			this.show(e);
		}
	},
	
	/**
	 * 禁止菜单动作
	 */
	disabled: function() {
		this._enabled = false;
	},
	
	/**
	 * 可用
	 */
	enabled: function() {
		this._enabled = true;
	},
	
	/**
	 * 销毁菜单对象
	 */
	destroy: function() {
		this._target.unbind('click');
		if (this._menu) {
			this._menu.remove();
		}
	}
};

$.fn.dropdown = function(params) {
	params.srcElement = this;
	params.target     = this;
	var o = new $.Dropdown(params);
	
	return o;
};

})(jQuery);