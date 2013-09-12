/**
 * UI
 * UI库命名空间
 * 静态方法封装对象
 * 
 * @class UI
 */
var UI = {

	/**
	 * 版本号
	 */
	version: '1.0',
	
	/**
	 * 浏览器判断
	 */
	browser: {
		msie: $.browser.msie,
		firefox: $.browser.mozilla,
		opera: $.browser.opera,
		webkit: $.browser.webkit,
		version: $.browser.version
	},
	
	/**
	 * css class 前缀定义
	 * 
	 */
	clsPfx: 'tui-',
	
	/**
	 * 自动ID
	 */
	autoId: 0,
	
	/**
	 * UI对象列表，[ID,引用]键值对
	 */
	components: {},
	
	/**
	 * 模拟类继承操作
	 * 
	 * <code>
	 * var A = function(){};
	 * A.prototype = {
	 *     methodOfA: function() {}
	 * };
	 * 
	 * var B = UI.extend(A, {
	 *     methodOfB: function(){}
	 * });
	 * </code>
	 * 
	 * @memberOf UI
	 */
	extend: (function() {
		var _cp = function(obj, overrides) {
			for (var k in overrides) {
				if (k == 'constructor') {
					continue ;
				}
				obj[k] = overrides[k];
			}
		};
		
		return function(parent, overrides) {
			var cls;
			if (xFive.isFunction(overrides.constructor)) {
				cls = overrides.constructor;
			} else {
				cls = xFive.isObject(parent) ? Object.prototype.constructor : parent.prototype.constructor;
			}
			
			cls.prototype = {};
			
			_cp(cls.prototype, parent.prototype);
			
			if (xFive.isObject(overrides)) {
				_cp(cls.prototype, overrides);
			}
			
			cls.prototype.parentClass = parent.prototype;
			
			return cls;
		};
	})(),
	
	/**
	 * 合并参数类表中所有对象的成员，并返回包含所有成员的对象。
	 * 如果存在同一成员名称，靠前传入的参数成员将被覆盖。
	 * 
	 * @param {Object} arguments[]
	 * @returns {Object}
	 * @memberOf UI
	 */
	apply: function() {
		var o = {};
		for (var i = 0, c = arguments.length; i < c; i++) {
			if (xFive.isObject(arguments[i])) {
				for (var k in arguments[i]) {
					o[k] = arguments[i][k];
				}
			}
		}
		
		return o;
	},
	
	/**
	 * 动态加载外部js文件
	 * 通过回调函数捕捉加载结果
	 * 
	 * @param string   url
	 * @param function callback
	 * @memberOf UI
	 */
	require: function(url, doc, callback) {
		var o = doc.createElement('script');
		o.type = 'text/javascript';
		o.src  = url;
		
		if (typeof callback == 'function') {
			if (UI.browser.msie) {
				o.onreadystatechange = function() {
					if (o.readyState == 4) {
						callback.call(doc, true);
					}
				};
			} else {
				o.onload = function() {
					callback.call(doc, true);
				};
			}
			
			o.onerror = function() {
				callback.call(doc, false);
			};
		}
		
		doc.appendChild(o);
	},
	
	/**
	 * 获取自动ID
	 */
	getAutoId: function() {
		return UI.autoId ++;
	},
	
	/**
	 * 指定对象是否数组
	 * 
	 * @param mixed obj
	 * @return boolean
	 */
	isArray: function(obj) {
		var a = [];
		return (typeof(obj) == typeof(a));
	},
	
	/**
	 * 添加UI对象
	 * 
	 * @param string id
	 * @param UI.Component obj
	 */
	setComponent: function(id, obj) {
		this.components[id] = obj;
	},
	
	/**
	 * 移除UI对象
	 * 
	 * @param string id
	 */
	deleteComponent: function(id) {
		if (this.component[id]) {
			delete this.components[id];
		}
	},
	
	/**
	 * 通过ID获取对象
	 * 
	 * @param string id
	 */
	get: function(id) {
		return this.components[id];
	}
};

/**
 * 封装DOM操作方法
 */
UI.Dom = {
	/**
	 * 查找Dom对象
	 * 
	 * @param string select
	 * @param mixed  scope
	 * @return jQuery
	 */
	get: function(select, scope) {
		return jQuery(select, scope);
	},
	
	/**
	 * 通过html获取Dom对象
	 * 
	 * @param string html
	 * @return jQuery
	 */
	convert: function(html, scope) {
		return jQuery(html, scope);
	},
	
	/**
	 * 创建标签对象
	 * 
	 * @param string tag
	 * @return jQuery
	 */
	create: function(tag, scope) {
		return jQuery(tag, scope);
	}
};

/**
 * UI插件基类
 * 
 * @abstract
 * @class UI.Component
 */
UI.Component = function(params) {
	this.setConfig(params).init(params);
	
	this._id = typeof(params[id]) == 'string' ? params[id] : 'tui-' + UI.getAutoId();
	
	UI.setComponent(this._id, this);
};

/**
 * 
 * @memberOf UI.Component
 */
UI.Component.prototype = {
	/**
	 * 配置内容
	 * 
	 * @memberOf UI.Component
	 * @private
	 */
	_config: null,
	
	/**
	 * 文档对象
	 * 
	 * @memberOf UI.Component
	 * @private
	 */
	_el: null,
	
	/**
	 * 事件绑定列表
	 * 
	 * @memberOf UI.Component
	 * @private
	 */
	_events: null,
	
	/**
	 * 是否已渲染
	 * 
	 * @memberOf UI.Component
	 * @private
	 */
	_isRendered: false,
	
	/**
	 * 元素和插件对象的唯一标识
	 * 
	 * @memberOf UI.Component
	 * @private
	 */
	_id: null,
	
	/**
	 * DOM元素所属文档
	 * 
	 * @memberOf UI.Component
	 * @private
	 */
	_scope: window,
	
	/**
	 * 设置插件配置内容
	 * 
	 * @memberOf UI.Component
	 * @param mixed key
	 * @param mixed val
	 * @return UI.Component
	 */
	setConfig: function(key, val) {
		if (null === this._config) {
			this._config = {};
		}
	
		if (typeof key == 'string' && undefined !== val) {
			this._config[key] = val;
		} else if (typeof key == 'object') {
			this._config = UI.apply(this._config, key);
		}
		
		return this;
	},
	
	/**
	 * 获取配置项内容
	 * 
	 * @memberOf UI.Component
	 * @param mixed key
	 * @return mixed
	 */
	getConfig: function(key) {
		if (undefined === key) {
			return this._config;
		}
		
		return this._config[key];
	}

	/**
	 * 初始化
	 * 
	 * @memberOf UI.Component
	 */
	init: function() {

	},
	
	/**
	 * 渲染界面元素
	 * 
	 * @memberOf UI.Component
	 * @final
	 */
	render: function() {
		
		if (!this._isRendered) {
			this._render();
		}
		
		this._isRendered = true;
	},
	
	/**
	 * 实际渲染过程方法，子类重写方法
	 * 
	 * @memberOf UI.Component
	 * @abstract
	 */
	_render: function() {
		
	},
	
	/**
	 * 添加到文档中指定位置
	 * 
	 * @memberOf UI.Component
	 */
	appendTo: function(container) {
		if (!this._isRendered) {
			this.render();
		}
		
		if (null !== this._el) {
			this._el.appendTo(container);
		}
		
		return this;
	},
	
	/**
	 * 替换文档中指定元素
	 * 
	 * @memberOf UI.Component
	 */
	replace: function(target) {
		if (!this._isRendered) {
			this.render();
		}
		
		if (typeof target == 'string') {
			target = $(target);
		}
		target.after(this._el);
		target.remove();
	},
	
	/**
	 * 绑定事件
	 * 
	 * @param string   event
	 * @param function callback
	 * @return UI.Component
	 */
	bind: function(event, callback) {
		if (typeof this._events[event] == 'undefined') {
			this._events[event] = [];
		}
		
		if (typeof callback == 'function') {
			this._events[event].push(callback);
		}
		
		return this;
	},
	
	/**
	 * 取消事件绑定
	 * 
	 * @param string event
	 * @param mixed  callback
	 * @return UI.Component
	 */
	unbind: function(event, callback) {
		if (typeof this._events[event] == 'undefined') {
			return ;
		}
		
		if (undefined === callback) {
			delete this._events[event];
			return this;
		}
		
		for (var i = 0, c = this._events[event].length; i < c; i++) {
			
			if (callback == this._events[event][i]) {
				delete this._events[event][i];
			}
		}
		
		return this;
	},
	
	/**
	 * 触发事件
	 * 
	 * @param event
	 * @return UI.Component
	 */
	triggerEvent: function(event, args) {
		if (typeof this._events[event] == 'undefined') {
			return this;
		}
		
		if (!args) {
			args = [];
		}
		
		for (var i = 0, c = this._events[event].length; i < c; i++) {
			this._events[event][i].apply(this, args);
		}
		
		return this;
	},
	
	/**
	 * 获取当前对象ID
	 * 
	 * @return string
	 */
	getId: function() {
		return this._id;
	},
	
	/**
	 * 销毁对象
	 * 
	 * @return null
	 */
	destroy: function() {
		for (var k in this._events) {
			delete this._events[k];
		}
		delete this._events;
		delete this._config;
		
		this._el.remove();
		delete this._el;
		
		UI.deleteComponent(this._id);
		
		return null;
	},
	
	/**
	 * 子类实现的销毁方法
	 */
	_destroy: function() {
		
	}
};