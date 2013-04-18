if (undefined !== UI) {

/**
 * 弹出窗口实现
 * 
 * <code>
 * var win = new UI.Window({
 *     
 * });
 * </code>
 * 
 * 参数列表
 * height: 窗口固定高度
 * width:  窗口固定宽度
 * id:     指定元素ID
 * scope:  元素作用域
 * title:  窗口标题
 * body:   窗口内容
 * footer: 窗口底部内容
 * footerRight: 底部内容居右
 * tools:  工具栏功能列表
 * opener: 父级对象
 * modal:  是否模态窗体
 * 
 */
UI.Window = function(params) {
	UI.Window.superclass.constructor.call(this, params);
};

/**
 * 默认工具
 */
UI.Window.Tools = {
	close: {
		element: '<a title="关闭" class="'+UI.clsPfx+'window-close" href="javascript:void(0)" _event="close"><em>关闭</em></a>',
		event: function(window){window.close();}
	}
};

UI.extend(UI.Window, UI.Component, {

	/**
	 * 设置标题内容
	 */
	setTitle: function() {
		if (!this._components) {
			return ;
		}
		this._components.header.find('div.'+clsPfx+'window-title').html(config[k]);
	},

	/**
	 * 更新窗体内容
	 * 
	 * 
	 */
	setBody: function(ele) {
		if (!this._components) {
			return ;
		}
		this._components.body.empty().append(ele);
	},

	/**
	 * 获取窗体各部分Dom
	 */
	getComponents: function(key) {
		if (!this._components) {
			return ;
		}
		
		if (undefined === this._components[key]) {
			throw 'Not exists window component: ' + key;
		}
		
		return this._components[key];
	},
	
	/**
	 * 窗体居中
	 */
	center: function() {
		var s = UI.Dom.get(this._scope),
			sw = s.width(),
			sh = s.height();
		
		var top = Math.max(0, Math.round(sh / 2 - this._el.outerHeight() / 2)),
			left = Math.max(0, Math.round(sw / 2 - this._el.outerWidth() / 2));
		
		this._el.css({
			top: top + 'px',
			left: left + 'px'
		});
		
		return this;
	},
	
	/**
	 * 显示窗体
	 */
	show: function() {
		if (!this._isRendered) {
			this.render();
			
			if (this._el) {
				this._el.appendTo(this._scope);
			}
		}
		
		UI.WindowMask.show();
		this._el.show();
		
		this.triggerEvent('show');
		
		return this;
	},
	
	/**
	 * 关闭窗体
	 */
	hide: function() {
		if (!this._el) {
			return ;
		}
		
		UI.WindowMask.hide();
		this._el.hide();
		
		this.triggerEvent('hide');
		
		return this;
	},
	
	/**
	 * hide 方法别名
	 */
	close: function() {
		this.hide();
		
		return this;
	},
	
	/**
	 * 
	 */
	focus: function() {
		if (!this._el) {
			return ;
		}
		
		this._el
		.removeClass(UI.clsPfx + 'window-blur')
		.css('z-index', 1001);
		
		this.triggerEvent('focus');
		
		return this;
	},
	
	/**
	 * 
	 */
	blur: function() {
		if (!this._el) {
			return ;
		}
		
		this._el
		.addClass(UI.clsPfx + 'window-blur')
		.css('z-index', 1000);
		
		this.triggerEvent('blur');
		
		return this;
	},

	/**
	 * 注销窗体
	 */
	_destroy: function() {
		this._el.unbind();
		for (var k in this._components) {
			this._components[k].unbind();
		}
	},

	/**
	 * 覆盖渲染实现
	 * 
	 * @override
	 * @private
	 */
	_render: function() {
		var clsPfx = UI.clsPfx,
			config = this._config;
		
		this._el = UI.Dom.convert('<div id="'+this._id+'" class="'+clsPfx+'window" style="position:absolute;z-index:1000;display:none"></div>', this._scope);
		
		components = {
			header: UI.Dom.convert('<div class="'+clsPfx+'window-header"><div class="'+clsPfx+'window-tool"></div><div class="'+clsPfx+'window-title"></div></div>', this._scope),
			body:   UI.Dom.convert('<div class="'+clsPfx+'window-body"></div>', this._scope)
		};
		
		this._el.append(components.header).append(components.body);
		
		var css    = typeof(config.css) == 'object' ? config.css : {},
			wtools = UI.Window.Tools,
			toolCt = components.header.find('div.'+clsPfx+'window-tool')
			me     = this;
		for (var k in config) {
			switch(k) {
				// 填充窗体内容
				case 'body':
					components.body.html(config.body);
					break;
				// 窗体脚
				case 'footerRight':
				case 'footer':
					if (!components.footer) {
						components.footer = UI.Dom.convert('<div class="'+clsPfx+'window-footer"><div class="'+clsPfx+'window-footer-right"></div><div class="'+clsPfx+'window-footer-inner"></div></div>', this._scope);
						this._el.append(components.footer);
					}
					
					var items = config[k],
						ct    = k == 'footer'
								? components.footer.find('div.' + clsPfx+'window-footer-inner')
								: components.footer.find('div.' + clsPfx+'window-footer-right');

					for (var i = 0, c = items.length; i < c; i++) {
						var item = items[i];
						if (typeof (item) == 'string') {
							ct.append(item);
						} else if (item instanceof UI.Component) {
							// ...
						} else if (typeof item == 'object' && item.element) {
							var ele = UI.Dom.convert(item.element, this._scope);
							if (item.event && typeof item.event == 'object') {
								for (var k in item.event) {
									var f = item.event[k];
									if (typeof k == 'string' && typeof f == 'function') {
										ele.bind(k, f);
									}
								}
							}
							ct.append(ele);
						}
					}
					
					break;
				// 标题
				case 'title':
					components.header.find('div.'+clsPfx+'window-title').html(config[k]);
					break;
				case 'tools':
					var tools = config[k],
						len   = tools.length;
					
					for (var i = len; i > 0; i--) {
						var tool = tools[len - i];
						
						if (typeof tool == 'string') {
							if (undefined !== wtools[tool]) {
								var ele = UI.Dom.convert(wtools[tool].element, this._scope);
								toolCt.append(ele);
								ele.bind('click', function(){
									wtools[tool].event(me);
								});
							}
						} else {
							toolCt.append(tool);
						}
					}
					break;
				case 'width':
				case 'height':
					css[k] = config[k] + 'px';
					break;
			}
		}
		
		this._el.css(css);
		
		this._components = components;
		this._el.bind('mousedown', function(){
			me.focus();
		});
		UI.Dom.get(this._scope).bind('mousedown', function(){
			me.blur();
		});
	}
});

UI.WindowMask = {
	
	/**
	 * 遮罩层对象
	 */
	_el: null,
	
	/**
	 * 显示的窗体数量
	 */
	_windowCount: 0,
	
	/**
	 * 是否显示
	 */
	_isShow: false,
	
	/**
	 * 显示遮罩
	 */
	show: function() {
		if (!this._el) {
			this._el = UI.Dom.convert('<div class="'+UI.clsPfx+'window-mask"></div>');
			this._el.appendTo(document.body);
		}

		this._windowCount++;
		
		this._el.css('height', document.body.offsetHeight + 'px');
		
		if (!this._isShow) {
			this._el.show();
		}
		
		this._isShow = true;
	},
	
	hide: function() {
		if (!this._el) {
			return ;
		}
		
		this._windowCount--;
		
		if (this._windowCount <= 0) {
			this._el.hide();
			this._isShow = false;
			this._windowCount = 0;
		}
	}
};
}