/**
 * 
 * @version $Id: autocomplete.js 1592 2012-02-17 13:50:46Z cutecube $
 */
if (undefined !== UI) { 

/**
 * 输入框自动完成
 * <code>
 * var pwdLevel = new UI.LevelBar({
 *    options: [{value: 1, text: 'Text', id: 't-1'} ...]
 * });
 * </code>
 * 
 * 参数列表
 * levels: 级别设置 {key: {cls: 'low', value: 0}}
 * cls: css class
 * select: mixed 替换的select元素，目标必须是
 * id: 指定元素ID
 * scope: 元素作用域
 * name: 表单域名称
 * readOnly: 是否只读
 * width: 宽度
 * 
 * @class UI.LevelBar
 */
UI.AutoComplete = function(params) {
	params = UI.apply(UI.AutoComplete.defaultConfig, params);
	UI.AutoComplete.superclass.constructor.call(this, params);
};

/**
 * 默认配置项
 */
UI.AutoComplete.defaultConfig = {
	textColumn: 'text',
	valueColumn: 'value'
};

UI.extend(UI.AutoComplete, UI.Component, {
	
	/**
	 * 输入框实例
	 */
	_input: null,
	
	/**
	 * 输入框内容（值）
	 */
	_val: null,
	
	/**
	 * 初始化
	 */
	init: function() {
		this._levels = {};

		// 创建传值的表单域
		if (this._config.input) {
			if (typeof this._config.input == 'string') {
				this._input = UI.Dom.get(this._config.input);
			} else {
				this._input = this._config.input;
			}
		}
		
		// 初始化输入框事件 
		if (this._input) {
			
			this._val = this._input.val();
			
			var id = this._id;
			this._input.bind('keyup', function(e) {
				var o = UI.get(id),
					v = o.getValue()
					code = e.keyCode ? e.keyCode : e.which;
				
				if (!this.value) {
					o.hide();
				}
				
				if (code != 13 && code != 38 && code != 40) {
					o.getData(function(ret){
						if (!this._isRendered) {
							this.appendTo(this._scope);
						}
						
						if (!ret || !ret.length) {
							return ;
						}
						
						this.buildMenu(this.formatData(ret)).show();
					});
				}
				
			}).bind('blur', function(){
				var o = UI.get(id);
				if (!o.isHover()) {
					o.hide();
				}
			}).bind('keydown', function(e){
				var o = UI.get(id);
				var code = e.keyCode ? e.keyCode : e.which;
				
				if (code == 40) {
					o.next();
				} else if (code == 38) {
					o.prev();
				} else if (code == 13) {
					if (o.isHover()) {
						this.value = o.getValue();
					}
					o.hide();
					e.returnValue = false;
					if (e.prevendDefault) {
						e.prevendDefault()
					}
					UI.Dom.cancelBuddle(e);
					return false;
				}
			});
		}
	},
	
	/**
	 * 显示提示菜单
	 */
	show: function() {
		if (!this._isRendered) {
			this.appendTo(this._scope);
		}
		
		this._el.show();
	},
	
	/**
	 * 隐藏提示菜单
	 */
	hide: function() {
		if (!this._isRendered) {
			return ;
		}
		
		this._el.hide();
	},
	
	/**
	 * 光标指向下一条记录
	 * 
	 */
	next: function() {
		var focus = this._el.find('div.' + UI.clsPfx + 'ac-item-hover');

		if (focus.size()) {
			focus.removeClass(UI.clsPfx + 'ac-item-hover');
			var n = focus.next();
			if (n.size()) {
				n.addClass(UI.clsPfx + 'ac-item-hover');
			} else {
				this._el.find('div.' + UI.clsPfx + 'ac-item::eq(0)').addClass(UI.clsPfx + 'ac-item-hover');
			}
		} else {
			this._el.find('div.' + UI.clsPfx + 'ac-item:eq(0)').addClass(UI.clsPfx + 'ac-item-hover');
		}
	},
	
	/**
	 * 光标指向前一条记录
	 * 
	 */
	prev: function() {
		var focus = this._el.find('div.' + UI.clsPfx + 'ac-item-hover');

		if (focus.size()) {
			focus.removeClass(UI.clsPfx + 'ac-item-hover');
			var p = focus.prev();
			if (p.size()) {
				p.addClass(UI.clsPfx + 'ac-item-hover');
			} else {
				this._el.find('div.' + UI.clsPfx + 'ac-item:last-child').addClass(UI.clsPfx + 'ac-item-hover');
			}
		} else {
			//alert(this._el.find('div.' + UI.clsPfx + 'ac-item:last-child').size());
			this._el.find('div.' + UI.clsPfx + 'ac-item:last-child').addClass(UI.clsPfx + 'ac-item-hover');
		}
	},
	
	/**
	 * 获取输入框内容
	 */
	getValue: function() {
		if (!this._el) {
			return '';
		}
		
		return this._el.find('div.' + UI.clsPfx + 'ac-item-hover').attr('_value');
	},
	
	/**
	 * 
	 */
	getData: function(callback) {
		if (typeof this._config.dataSource == 'function' && typeof callback == 'function') {
			return this._config.dataSource.call(this, callback);
		}
		
		if (typeof callback == 'function') {
			return callback.call(this, this.formatData(this._config.dataSource));
		}
		
		return this.formatData(this._config.dataSource);
	},
	
	/**
	 * 格式化数据
	 * 
	 */
	formatData: function(data) {
		if (typeof(data) == 'string') {
			return [{value: data, text: data}];
		}
		
		if (UI.isArray(data)) {
			var ret = [];
			for (var i = 0, c = data.length; i < c; i++) {
				ret[ret.length] = this.formatItem(data[i]);
			}
		}
		
		return ret;
	},
	
	/**
	 * 格式化项目
	 * 
	 */
	formatItem: function(item) {
		if (typeof(data) == 'string') {
			return {value: item, text: item};
		}
		
		var ret = {};
		if (typeof(item) == 'object') {
			ret.text  = item[this._config.textColumn];
			ret.value = item[this._config.valueColumn];
		}
		
		return ret;
	},
	
	/**
	 * 填充下拉菜单内容
	 * 
	 * @param mixed items
	 */
	buildMenu: function(items) {
		this._el.empty();
		
		if (!items || !items.length) {
			return ;
		}
		
		var html = [];
		for (var i = 0, c = items.length; i < c; i++) {
			html[html.length] = '<div class="'+UI.clsPfx+'ac-item" _value="'+items[i].value+'">'+items[i].text+'</div>';
		}
		
		this._el.html(html.join(''));
		
		return this;
	},
	
	/**
	 * 
	 */
	isHover: function() {
		if (!this._el) {
			return false;
		}
		return this._el.find('div.' + UI.clsPfx + 'ac-item-hover').size() > 0;
	},
	
	/**
	 * 
	 * @override
	 */
	_render: function() {
		this._el = UI.Dom.convert('<div id="'+this._id+'" class="'+UI.clsPfx+'ac-menu"></div>', this._scope);
		
		if (this._config.width) {
			this._el.css('width', this._config.width + 'px');
		} else if (this._input) {
			this._el.css('width', this._input.outerWidth() + 'px');
		}
		
		if (this._input) {
			var offset = this._input.offset();
			
			this._el.css({
				top: (offset.top + this._input.outerHeight()) + 'px',
				left: offset.left + 'px'
			});
		}
		
		// 项目事件移动
		var id = this._id;
		this._el.bind('mouseover mouseout click', function(e){
			var se = e.srcElement ? UI.Dom.get(e.srcElement) : UI.Dom.get(e.target);
			
			var item = se.closest('.' + UI.clsPfx + 'ac-item');
			if (item.size()) {
				if (e.type == 'mouseover') {
					item.addClass(UI.clsPfx + 'ac-item-hover');
				} else if (e.type == 'mouseout') {
					item.removeClass(UI.clsPfx + 'ac-item-hover');
				} else {
					var val = item.attr('_value');
					var o   = UI.get(id);
					o.hide();
					o._input.val(val).focus();
				}
			}
		});
	}
});

}