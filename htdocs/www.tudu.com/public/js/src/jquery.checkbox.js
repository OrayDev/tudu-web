/**
 * 
 * jQuery 多状态checkbox插件
 */

var _CHECKBOXES = {};

(function($){

$.checkbox = function(config) {
	this._cfg = $.extend({}, $.checkbox.defaultConfig, config || {});
	this.init();
};

$.checkbox.AUTO_ID = 0;

$.checkbox.defaultConfig = {
	/**
	 * 状态列表
	 * 键名是状态名称，数据格式为对象
	 * 列表的顺序为点击的切换顺序
	 * 
	 * 
	 * @type {Object}
	 */
	states: {
		normal: {
			value: 0,
			cls: ''
		},
		half: {
			value: 0,
			cls: 'checkbox-half'
		},
		checked: {
			value: 1,
			cls: 'checkbox-checked'
		}
	},
	baseCls: 'checkbox',
	disableCls: 'checkbox-disabled',
	name: 'checkbox',
	css: {}
};

$.checkbox.prototype = {
	
	/**
	 * 当前状态
	 * 
	 * @type int
	 */
	_state: null,
	
	/**
	 * 对象设置信息
	 * 
	 * @type {Object}
	 */
	_cfg: null,
	
	/**
	 * 
	 * @type {Object}
	 */
	_el: null,
	
	/**
	 * 
	 */
	_input: null,
	
	/**
	 * 支持事件
	 */
	_evt: ['click', 'change'],
	
	/**
	 * 绑定事件
	 * 
	 * @type {Object}
	 */
	_events: null,
	
	/**
	 * 是否禁用
	 */
	_disabled: false,
	
	/**
	 * 初始化
	 * 
	 * @return
	 */
	init: function() {
		var me = this;
		this._el = $('<div></div>').addClass(this._cfg.baseCls);
		this._input = $('<input type="hidden" name="'+this._cfg.name+'" />');
		
		this._el.attr({'_name': this._cfg.name, '_jqcheckbox': 'jqcheckbox'}).css(this._cfg.css);
		
		if (!this._cfg.id) {
			this._cfg.id = 'checkbox-' + (++$.checkbox.AUTO_ID);
		}
		
		this.id = this._cfg.id;
		
		this._el.attr('id', this._cfg.id);
		_CHECKBOXES[this._cfg.id] = this;
		
		this._el.append(this._input);
		
		var st = [];
		for (var k in this._cfg.states) {
			st.push(k);
		}
		
		if (this._cfg.replace) {
			this.replace(this._cfg.replace);
		} else if (this._cfg.appendTo) {
			this.appendTo(this._cfg.appendTo);
		}
		
		this._events = {};
		
		this._el.bind('click', function(e) {
			if (me._disabled) {
				return ;
			}
			
			_clickEvent();
			me.triggerEvent('click', [e]);
		})
		.bind('mouseover', function() {
			me._el.addClass('checkbox-hover');
		})
		.bind('mouseout', function() {
			me._el.removeClass('checkbox-hover');
		})
		.bind('mousedown', function() {})
		.bind('mouseup', function() {});
		
		$('label[for="'+this._cfg.id+'"]').bind('click', function(e) {
			e.preventDefault();
			return false;
		})
		.bind('click', function() {me._el.click();})
		.bind('mouseover', function(){me._el.addClass('checkbox-hover');})
		.bind('mouseout', function(){me._el.removeClass('checkbox-hover');});
		
		this.state(st[0]);
		
		function _clickEvent() {
			if (me._disabled) {
				return ;
			}
			
			var idx = st.indexOf(me.state());
			if (-1 === idx || st.length - 1 <= idx) {
				idx = 0;
			} else {
				idx++;
			}
			
			me.state(st[idx]);
		}
	},
	
	/**
	 * 设置状态值
	 * @param key
	 * @param val
	 * @return
	 */
	setValue: function(key, val) {
		var obj;
		if (undefined  == val && typeof key == 'object') {
			obj = key;
		} else {
			obj = {};
			obj[key] = val;
		}
		
		for (var k in obj) {
			if (undefined !== this._cfg.states[k]) {
				this._cfg.states[k].value = obj[k];
			}
		}
				
		return this;
	},
	
	/**
	 * 绑定事件
	 * 
	 * @param event
	 * @param callback
	 * @return
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
	 * @param event
	 * @param callback
	 * @return
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
	 * @return
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
	 * 获取/设置当前状态
	 * 
	 * @param status
	 * @return
	 */
	state: function(state) {
		if (undefined === state) {
			return this._state;
		} else {
			if (typeof this._cfg.states[state] != 'undefined') {
				this._setState(state);
			}
			
			return this;
		}
	},
	
	/**
	 * 
	 * @return
	 */
	disabled: function() {
		this._disabled = true;
		this._input.attr('disabled', 'disabled');
		this._el.addClass(this._cfg.disableCls);
	},
	
	/**
	 * 
	 * @return
	 */
	enabled: function() {
		this._disabled = false;
		this._input.attr('disabled', false);
		this._el.removeClass(this._cfg.disableCls);
	},
	
	appendTo: function(parent) {
		this._el.appendTo(parent);
	},
	
	replace: function(place) {
		if (typeof place == 'string') {
			place = $(place);
		}
		place.after(this._el);
		place.remove();
	},
	
	/**
	 * 设置当前状态
	 * 
	 * @param state
	 * @return
	 */
	_setState: function(state) {
		var st = this._cfg.states[state];
		
		if (this._state != state) {
			this._state = state;
			this._el.removeClass().addClass(this._cfg.baseCls + ' ' + st.cls);
			this._input.val(st.value);
			
			this.triggerEvent('change');
		}
	}
};

$.checkboxgroup = function(items) {
	this.items = items || [];
};

$.checkboxgroup.prototype = {
	
	items: null,
	
	disabled: function() {
		$.each(this.items, function(){
			this.disabled();
		});
	},
	
	enabled: function() {
		$.each(this.items, function(){
			this.enabled();
		});
	},
	
	state: function(state) {
		$.each(this.items, function(){
			if (this._disabled) {
				return ;
			}
			this.state(state);
		});
		
		return this;
	},
	
	bind: function(event, callback) {
		$.each(this.items, function(){
			this.bind(event, callback);
		});
		
		return this;
	},
	
	unbind: function(event) {
		$.each(this.items, function(){
			this.unbind(event);
		});
		
		return this;
	},
	
	each: function(callback) {
		for (var i = 0, c = this.items.length; i < c; i++) {
			callback.call(this.items[i]);
		}
		
		return this;
	},
	
	size: function() {
		return this.items ? this.items.length : 0;
	}
};

if (undefined === Array.prototype.indexOf) {
	Array.prototype.indexOf = function(val) {
		for (var i = 0, c = this.length; i < c; i++) {
			if (val == this[i]) {
				return i;
			}
		}
		
		return -1;
	};
}

})(jQuery);

function getCheckbox(key, val, parent) {
	if (!parent) {
		parent = $(document.body);
	}
	
	if (key != 'id') {
		key = '_' + key;
	}
	
	var ret = [];
	parent.find('div[_jqcheckbox="jqcheckbox"]['+key+'="'+val+'"]').each(function(){
		ret.push(_CHECKBOXES[this.id]);
	});
	
	return new $.checkboxgroup(ret);
}