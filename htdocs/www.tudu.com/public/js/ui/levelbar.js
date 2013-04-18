if (undefined !== UI) { 

/**
 * 
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
UI.LevelBar = function(params) {
	UI.LevelBar.superclass.constructor.call(this, params);
};
UI.extend(UI.LevelBar, UI.Component, {
	
	/**
	 * 传值用表单域
	 */
	_input: null,
	
	/**
	 * 级别列表
	 */
	_levels: null,
	
	/**
	 * 当前级别
	 */
	_level: null,
	
	/**
	 * 初始化
	 */
	init: function() {
		this._levels = {};

		// 创建传值的表单域
		if (typeof this._config.name == 'string') {
			this._input = UI.Dom.convert('<input type="hidden" name="' + this._config.name + '" />');
		}
	
		// 填充选项列表
		if (this._config.levels && UI.isObject(this._config.levels)) {
			for (var k in this._config.levels) {
				this.addLevel(k, this._config.levels[k]);
			}
		}
		
		// 默认值
		if (this._config.defaultLevel) {
			this.setLevel(this._config.defaultLevel);
		}
	},

	/**
	 * 
	 * @memberOf UI.Select
	 * @param string key
	 * @param object level
	 * @return UI.SingleSelect
	 */
	addLevel: function(key, level) {
		if (typeof(level) == 'object') {
			this._levels[key] = level;
		}
		
		return this;
	},

	/**
	 * 设置选择项
	 * 
	 * @return UI.SingleSelect
	 */
	setLevel: function(key) {
		if (undefined !== this._levels[key]) {
			this._level = key;
			
			if (this._isRendered) {
				this._el.find('div.' + UI.clsPfx + 'level-item').removeClass(UI.clsPfx + 'level-item-current ' + UI.clsPfx + 'level-item-prev');
				var o = this._el.find('div[_key="'+key+'"]');
				o.addClass(UI.clsPfx + 'level-item-current');
				o.prevAll('div').addClass(UI.clsPfx + 'level-item-prev');
			}
			
		// 空
		} else {
			this._level = null;
			
			if (this._isRendered) {
				this._el.find('div.' + UI.clsPfx + 'level-item').removeClass(UI.clsPfx + 'level-item-current ' + UI.clsPfx + 'level-item-prev');
			}
		}
		
		return this;
	},

	/**
	 * 添加 css class
	 */
	addClass: function(cls) {
		this._el.addClass(cls);
		return this;
	},
	
	/**
	 * 移除 css class
	 */
	removeClass: function(cls) {
		this._el.removeClass(cls);
		return this;
	},
	
	/**
	 * 
	 * @override
	 */
	_render: function() {
		this._el = UI.Dom.convert('<div id="'+this._id+'" class="'+UI.clsPfx+'level-bar"></div>', this._scope);
		
		var body = [],
			levels = this._levels,
			count  = 0;
		for (var k in levels) {
			var cls = count == 0 ? ' ' + UI.clsPfx + 'level-item-first' : '';

			var html = '<div class="'+UI.clsPfx+'level-item '+cls+'" _key="'+k+'"></div>';
			
			body[body.length] = html;
			
			count ++;
		}
		
		this._el.html(body.join(''));
		
		if (!this._config.readOnly) {
			// ...
		}
	}
});

}