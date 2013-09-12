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
 * optionCls: 选项公用 css class
 * select: mixed 替换的select元素，目标必须是
 * name: 表单域名称
 * id: 指定元素ID
 * scope: 元素作用域
 * defaultText: null
 * 
 * @class UI.Select
 */
UI.SingleSelect = UI.extend(UI.Component, {
	
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
			select.find('option').each(function(){
				this.addOption({
					value: this.value,
					text:  this.innerText
				});
			});
		// 创建传值的表单域
		} else if (typeof this._config.name == 'string') {
			this._input = UI.Dom.convert('<input type="hidden" name="' + this._config.name + '" />');
		}
	
		// 填充选项列表
		if (this._config.options && UI.isArray(this._config.options)) {
			for (var i = 0, c = this._config.options; i < c; i++) {
				this.addOption(this._config.options[i]);
			}
		}
		
		// 
		if (this._config.scope) {
			this._scope = this._config.scope;
		}
	},
	
	/**
	 * 
	 * @memberOf UI.Select
	 * @param object option
	 * @return UI.SingleSelect
	 */
	addOption: function(option) {
		if (typeof option == 'object') {
			this._options[this._options.length] = option;
		}
		
		return this;
	},
	
	/**
	 * 设置选择项
	 * 
	 * @return UI.SingleSelect
	 */
	select: function(value) {
		for (var i = 0, c = this._options.length; i < c; i++) {
			if (value == this._options[i].value) {
				this._input.val(value);
				this.updateSelectText(this._options[i]);
				break ;
			}
		}
		
		return this;
	},
	
	/**
	 * 更新选择框选中文字
	 * 
	 * @param object option
	 * @return 
	 */
	updateSelectText: function(option) {
		if (this._isRendered && this._textCt) {
			this._textCt.innerText = option.text;
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
	 * 显示下拉菜单
	 * 
	 *  @return UI.SingleSelect
	 */
	showMenu: function() {
		this._menu.show();
		
		this.isShow(true);
		
		this.triggerEvent('show');
		
		return this;
	},
	
	/**
	 * 隐藏下拉菜单
	 * 
	 * @return UI.SingleSelect
	 */
	hideMenu: function() {
		this._menu.hide();
		
		this.isShow(false);
		
		this.triggerEvent('hide');
		
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
	 * 
	 * @override
	 */
	_render: function() {
		this._el     = UI.Dom.convert('<div class="'+UI.clsPfx+'select"><span class="'+UI.clsPfx+'select-arrow"></span></div>', this._scope);
		this._textCt = UI.Dom.convert('<span class="'+UI.clsPfx+'text"></span>', this._scope);
		this._menu   = UI.Dom.convert('<div class="'+UI.clsPfx+'select-menu"></div>', this._scope);
		
		if (this._config.defaultText) {
			this._textCt.text(this._config.defaultText);
		}
		
		var menuBody = [];
		for (var i = 0, c = this._options.length; i < c; i++) {
			 var opt  = this._options[i],
				 html = ['<div class="'+UI.clsPfx+'select-option" _value="'+this._options[i].value+'"></div class="'+UI.clsPfx+'-select-option-body">'];
			 if (opt.body) {
				 html[html.length] = opt.body;
			 } else if (opt.text) {
				 html[html.length] = opt.text;
			 }
			 html[html.length] = '</div></div>';
			 menuBody[menuBody.length] = html.join('');
		}
		
		this._menu.html(menuBody.join(''));
		
		this._el.prepend(this._textCt);
		
		var id = this._id;
		this._el.bind('click', function(){
			var obj = UI.get(id);
			obj.toggleMenu();
			obj.triggerEvent('click');
		});
		
		Dom.get(this._el).bind('blur', function() {
			var obj = UI.get(id);
			obj.hideMenu();
		});
	}
});

}