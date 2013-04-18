
/**
 * 表单向导类
 * 参数列表
 * container: 父元素，表单/或非表单均可，会自动查找下面对应input
 * rules: 表单域匹配规则
 * messages: 显示各种提示
 * submit: 表单提交处理方法，仅在form参数是表单时有作用 
 * hintWidth: 提示宽度
 * 
 * @param object params
 * @return
 */
var Formwizard = function(params) {
	this._config = params;
	this.init();
};

Formwizard.rules = {
	/**
	 * 必须填写
	 */
	require: function(r) {
		if (!this.value 
			|| !this.value.length
			|| !this.value.replace(/^\s+|\s+$/g, ''))
		{
			return false == r;
		}
		
		return true == r;
	},
	
	/**
	 * 匹配正则表达式
	 */
	match: function(exp) {
		if (this.value && exp.test && !exp.test(this.value)) {
			return false;
		}
		
		return true;
	},
	
	/**
	 * 
	 */
	equals: function(v) {
		if (v.val) {
			v = v.val();
		}
		
		return v == this.value;
	},
	
	/**
	 * 
	 */
	notequals: function(v) {
		if (v.val) {
			v = v.val();
		}
		
		return v != this.value;
	},
	
	/**
	 * 邮箱
	 */
	email: function(v) {
		if (this.value) {
			var r = /[^@]+@[^\.]+(\.\w+)*/;
			return r.test(this.value) == v;
		} 
		
		return true == v;
	}
};

Formwizard.prototype = {
	/**
	 * 配置内容
	 */
	_config: null,
	
	/**
	 * 表单域提示信息
	 */
	_hint: null,
	
	/**
	 * 
	 */
	_warns: null,
	
	/**
	 * 表单域容器
	 */
	_ct: null,
	
	/**
	 * 是否合法
	 */
	_isValid: true,
	
	/**
	 * 初始化
	 */
	init: function() {
		this._el = $('<div class="hint float-hint"><table board="0" cellspacing="0" cellpadding="0"><tr><td class="hint-body" valign="middle"></td></tr></table></div>').hide();
		
		if (this._config.hintWidth) {
			this._el.css('width', this._config.hintWidth + 'px');
		}
		
		this._el.appendTo(document.body);
		
		this._warns = {};
		
		if (!this._config.container
			|| !this._config.messages)
		{
			return ;
		}
		
		if (typeof(this._config.container) == 'string') {
			this._config.container = $(this._config.container);
		}
		
		this._ct = this._config.container;
		
		var me = this;
		this._ct.find('input, select, textarea').bind('focus blur', function(e){
			var sc = e.srcElement ? e.srcElement : e.target;
			
			if (e.type == 'focus') {
				me.showHint(sc);
			} else {
				me.hideHint();
				me.checkItem(this);
			}
		});
		
		$(window).bind('resize', function(){
			me.hideHint();
			if (document.activeElement) {
				me.showHint(document.activeElement);
			}
		});
		
		if (this._ct.attr('tagName').toLowerCase() == 'form')
		{
			this._ct.bind('submit', function(){return false;});
			this._ct.bind('submit', function(e){
				if (!me.checkValid()) {
					return false;
				}
				
				if (me._config.submitHandler) {
					me._config.submitHandler.call(this, e);
				}
			});
		}
	},
	
	/**
	 * 
	 */
	showHint: function(sc, text) {
		if (!sc.name || undefined === this._config.messages[sc.name]) {
			return ;
		}
		
		if (!text) {
			text = this._config.messages[sc.name].hint;
		}
		if (!text) {
			return ;
		}
		
		var o = $(sc),
			offset = o.offset();
		
		if (this._warns[sc.name]) {
			this.hideError(sc.name);
		}
		
		this._el.css({
			top: offset.top + 1 + 'px',
			left: offset.left + o.outerWidth() + 10 + 'px'
		}).show().find('.hint-body').html(text);
	},
	
	/**
	 * 
	 */
	hideHint: function() {
		this._el.hide();
	},
	
	/**
	 * 显示错误
	 */
	showError: function(obj, text) {
		
		if (!this._warns[obj.name]) {
			var t = $('<div class="hint float-hint hint-warn"><table board="0" cellspacing="0" cellpadding="0"><tr><td class="hint-body" valign="middle"></td></tr></table></div>').hide();
			
			if (this._config.hintWidth) {
				t.css('width', this._config.hintWidth + 'px');
			}
			
			this._warns[obj.name] = t;
		}
		
		var o = $(obj),
			e = this._warns[obj.name],
			offset = o.offset();
	
		e.appendTo(document.body);
		e.css({
			top: offset.top + 1 + 'px',
			left: offset.left + o.outerWidth() + 10 + 'px'
		}).show().find('.hint-body').html(text);
	},
	
	/**
	 * 
	 */
	hideError: function(k) {
		if (this._warns && this._warns[k]) {
			this._warns[k].remove();
			delete this._warns[k];
		}
	},
	
	/**
	 * 检查输入合法性
	 */
	checkValid: function() {
		var r = true;
		
		var me = this;
		this._ct.find('input, select, textarea').each(function(){
			r &= me.checkItem(this);
		});
		
		return r;
	},
	
	checkItem: function(item) {
		if (!item.name || !this._config.rules[item.name]) {
			return true;
		}
		
		var rs  = this._config.rules[item.name],
			msgs  = this._config.messages,
			fk  = null,
			ret = true;
		
		for (var k in rs) {
			if (typeof (Formwizard.rules[k]) == 'function') {
				if (!Formwizard.rules[k].call(item, rs[k])) {
					fk = k;
					break;
				}
			} else if (typeof rs[k] == 'function') {
				if (!rs[k].call(item)) {
					fk = k;
					break;
				}
			}
		}
		
		if (fk && msgs[item.name] && msgs[item.name][fk]) {
			this.showError(item, msgs[item.name][fk]);
			
			ret = false;
		} else {
			this.hideError(item.name);
		}
		
		return ret;
	}
};