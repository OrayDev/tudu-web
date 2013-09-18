if (undefined !== UI) { 
	
/**
 * 系统Select替换
 * <code>
 * var select = new UI.Select({
 *    options: [{value: 1, text: 'Text', id: 't-1'} ...]
 * });
 * </code>
 * 
 * 参数列表
 * options: Array({value: mixed, text: string}) 选项列表
 * cls: 外层css class
 * menuCls: 选项公用 css class
 * select: mixed 替换的select元素，目标必须是
 * name: 表单域名称
 * id: 指定元素ID
 * scope: 元素作用域
 * defaultText: null
 * maxHeight: 最大高度
 * 
 * @class UI.SingleSelect
 */
UI.SingleSelect = function(params) {
	UI.SingleSelect.superclass.constructor.call(this, params);
};
UI.extend(UI.SingleSelect, UI.Component, {
	
	/**
	 * 传值用表单域
	 */
	_input: null,
	
	/**
	 * 选项列表
	 */
	_options: null,
	
	/**
	 * 是否显示选项列表
	 */
	_isShow: false,
	
	/**
	 * 当前值
	 */
	_value: null,
	
	/**
	 * 初始化
	 */
	init: function() {
		this._options = [];

		// 从已有的下拉框中填充选项列表
		if (this._config.select) {
			var select = this._config.select;
			if (typeof select == 'string') {
				select = UI.Dom.get(select);
			}
			
			this._input = select;
			var opt = [];
			select.find('option').each(function(){
				opt[opt.length] = {
					value: this.value,
					text:  UI.Dom.get(this).text()
				};
			});
			this._options = opt;
			
			var _this = this;
			select.bind('change', function(){
				_this.select(this.value);
			});
			
			this._value = select.val();
			
			if (!this._config.defaultText) {
				this._config.defaultText = select.find('option:eq(0)').text();
			}
			
			if (!this._config.css) {
				this._config.css = {width: select.outerWidth() + 'px'};
			} else if (!this._config.css.width) {
				this._config.css.width = select.outerWidth() + 'px';
			}
			
		// 创建传值的表单域
		} else if (typeof this._config.name == 'string') {
			this._input = UI.Dom.convert('<input type="hidden" name="' + this._config.name + '" />');
		}

		// 填充选项列表
		if (this._config.options && UI.isArray(this._config.options)) {
			for (var i = 0, c = this._config.options.length; i < c; i++) {
				this.addOption(this._config.options[i]);
			}
		}
	},
	
	/**
	 * 
	 * @memberOf UI.Select
	 * @param object option
	 * @return UI.SingleSelect
	 */
	addOption: function(option) {
		if (typeof(option) == 'object') {
			this._options[this._options.length] = option;
		} else if (typeof(option == 'string')) {
			this._options[this._options.length] = {
				value: option,
				text: option
			};
		}
		
		return this;
	},
	
	/**
	 * 设置选择项
	 * 
	 * @return UI.SingleSelect
	 */
	select: function(value) {

		var arr = this._options;
		for (var i = 0, c = arr.length; i < c; i++) {
			if (value == arr[i].value) {
				this._input.val(value);
				this.updateSelectText(arr[i]);
				break ;
			}
		}
		
		var v = this._value;
		this._value = value;
		
		if (value != v) {
			this.triggerEvent('change');
		}
		
		return this;
	},
	
	/**
	 * 获取当前选中值
	 * 
	 * @return mixed
	 */
	getValue: function() {
		return this._value;
	},
	
	/**
	 * 更新选择框选中文字
	 * 
	 * @param object option
	 * @return 
	 */
	updateSelectText: function(option) {
		if (this._textCt) {
			this._textCt.html(option.text);
		}
		
		return this;
	},
	
	/**
	 * 
	 */
	appendSelectOption: function(option) {
		
	},
	
	/**
	 * 菜单是否显示
	 * 
	 * @return boolean
	 */
	isShow: function(bool) {
		if (undefined !== bool) {
			this._isShow = bool;
		}
		return this._isShow;
	},
	
	/**
	 * 获取菜单实例
	 * 
	 * @return object
	 */
	getMenu: function() {
		if (this._menu) {
			return this._menu;
		}
		
		return null;
	},
	
	/**
	 * 交单是否在菜单上
	 */
	isHover: function() {
		return this._menu.find('div.' + UI.clsPfx + 'select-option-hover').size() > 0;
	},
	
	/**
	 * 显示下拉菜单
	 * 
	 *  @return UI.SingleSelect
	 */
	showMenu: function() {
		if (!this._menu || !this._el) {
			return ;
		}
		
		this._menu.show();
		
		
		this._el.css('z-index', 20);
		this._el.addClass(UI.clsPfx + 'select-expand');
		if (this._config.maxHeight && this._config.maxHeight > 0) {
			if (this._menu.height() > this._config.maxHeight) {
				this._menu.css('height', this._config.maxHeight + 'px');
			}
		}
		
		if (this._config.menuWidth) {
			this._menu.css('width', this._config.menuWidth + 'px');
		} else {
			this._menu.css('width', this._el.outerWidth() - 2 + 'px');
		}
		
		this.isShow(true);
		
		this.triggerEvent('show');
		
		if (UI.browser.msie && UI.browser.version < '7.0') {
			var p = this._el.parent(),
				pos  = p.css('position'),
				zidx = p.css('z-index');
			p.data('mcs', {'position': pos ? pos : 'static', 'z-index': zidx ? zidx : 0});
			p.css({'position': 'relative', 'z-index': 20});
		}
		
		return this;
	},
	
	/**
	 * 隐藏下拉菜单
	 * 
	 * @return UI.SingleSelect
	 */
	hideMenu: function() {
		if (!this._menu || !this._el || !this.isShow()) {
			return ;
		}
		
		this._menu.hide();
		
		this._el.css('z-index', 10);
		this._el.removeClass(UI.clsPfx + 'select-expand');
		this._menu.find('div.' + UI.clsPfx + 'select-option-hover')
		.removeClass(UI.clsPfx + 'select-option-hover');
		this._menu.css('height', 'auto');
		
		this.isShow(false);
		
		this.triggerEvent('hide');
		
		if (UI.browser.msie && UI.browser.version < '7.0') {
			var p   = this._el.parent(),
				mcs = p.data('mcs')
			if (mcs) {
				p.css(mcs);
			}
		}
		
		return this;
	},
	
	/**
	 * 切换菜单
	 * 
	 * @return UI.SingleSelect
	 */
	toggleMenu: function() {
		if (this.isShow()) {
			this.hideMenu();
		} else {
			this.showMenu();
		}
		
		return this;
	},

	/**
	 * 清空菜单内容
	 * 
	 */
	empty: function() {
		this._options = [];
		this._value   = '';
		if (this._menu) {
			this._menu.html('');
			
			var text = this._config.defaultText ? this._config.defaultText : '';
			this._textCt.text(text);
		}
		
		if (this._input) {
			this._input.val('');
		}
	},

	/**
	 * 刷新选择菜单列表
	 * 
	 * @memberOf UI.SingleSelect
	 */
	refreshMenu: function() {
		if (!this._menu) {
			return ;
		}
		
		var menuBody = [];
		for (var i = 0, c = this._options.length; i < c; i++) {
			 var opt  = this._options[i],
				 html = ['<div class="'+UI.clsPfx+'select-option" _value="'+this._options[i].value+'"><div class="'+UI.clsPfx+'select-option-body">'];
			 if (opt.body) {
				 html[html.length] = opt.body;
			 } else if (opt.text) {
				 html[html.length] = opt.text;
			 }
			 html[html.length] = '</div></div>';
			 menuBody[menuBody.length] = html.join('');
		}
		
		this._menu.html(menuBody.join(''));
		
		return this;
	},
	
	/**
	 * 
	 * @override
	 */
	_render: function() {
		this._el     = UI.Dom.convert('<div id="'+this._id+'" class="'+UI.clsPfx+'select"><span class="'+UI.clsPfx+'select-arrow"></span></div>', this._scope);
		this._textCt = UI.Dom.convert('<span class="'+UI.clsPfx+'text"></span>', this._scope);
		this._menu   = UI.Dom.convert('<div class="'+UI.clsPfx+'select-menu"></div>', this._scope);
		
		if (!this._config.tabIndex) {
			this._config.tabIndex = 1;
		}
		this._el.attr('tabindex', this._config.tabIndex);
		
		if (this._config.defaultText) {
			this._textCt.text(this._config.defaultText);
		}
		
		this.refreshMenu();
		
		if (this._config.menuCls) {
			this._menu.addClass(this._config.menuCls);
		}
		
		this._menu.hide();
		
		this._el.prepend(this._textCt);

		this._menu.appendTo(this._el);
		
		if (this._input) {
			this._el.append(this._input);
		}

		var id = this._id;
		this._el.bind('click keydown', function(e){
			var obj = UI.get(id);
			
			UI.Dom.cancelBuddle(e);
			if (e.type == 'keydown') {
				var code = e.keyCode ? e.keyCode : e.which,
					menu = obj.getMenu();
				
				var focus = menu.find('div.' + UI.clsPfx + 'select-option-hover:eq(0)'),
					hoverClass = UI.clsPfx + 'select-option-hover';

				if (code == 38) {
					if (!focus.size()) {
						menu.find('div:last-child').addClass(hoverClass);
					} else {
						focus.removeClass(hoverClass);
						focus.prev().addClass(hoverClass);
					}
				} else if (code == 40) {
					if (!focus.size()) {
						menu.find('div:eq(0)').addClass(hoverClass);
					} else {
						focus.removeClass(hoverClass);
						focus.next().addClass(hoverClass);
					}
				} else if (code == 13) {
					if (focus.size()) {
						focus.removeClass(hoverClass);
						obj.select(focus.attr('_value'));
					}
					obj.toggleMenu();
				}
				
				return ;
			}
			
			obj.toggleMenu();
			obj.triggerEvent('click');

		}).bind('mouseover mouseout', function(e){
			var obj = UI.get(id);
			if (e.type == 'mouseover') {
				obj.getEl().addClass(UI.clsPfx + 'select-hover');
			} else {
				obj.getEl().removeClass(UI.clsPfx + 'select-hover');
			}
		});

		this._menu.bind('mouseover mouseout click', function(e){
			var ele = e.srcElement ? UI.Dom.get(e.srcElement) : UI.Dom.get(e.target);
			var opt = ele.closest('.' + UI.clsPfx + 'select-option');
			
			if (opt.size()) {
				// 移上
				if (e.type == 'mouseover') {
					UI.Dom.cancelBuddle(e);
					opt.addClass(UI.clsPfx + 'select-option-hover');
				// 移出事件 
				} else if (e.type == 'mouseout') {
					UI.Dom.cancelBuddle(e);
					opt.removeClass(UI.clsPfx + 'select-option-hover');
				// 单击
				} else {
					UI.get(id).select(opt.attr('_value'));
				}
			}
		});
		
		this._el.bind('blur', function() {
			var obj = UI.get(id);

			if (obj && !obj.isHover()) {
				obj.hideMenu();
			}
		});
		
		UI.Dom.get(this._scope).bind('click', function() {
			var obj = UI.get(id);
			if (obj && !obj.isHover()) {
				obj.hideMenu();
			}
		});

		if (this._config.selected) {
			this.select(this._config.selected);
		}
		
		if (this._input) {
			this._input.hide();
		}
		
		if (this._value) {
			this.select(this._value);
		}
	}
});

}