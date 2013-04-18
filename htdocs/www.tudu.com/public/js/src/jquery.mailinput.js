/**
 * 联系人输入框扩展
 */
(function($){

$.mailinput = function(config) {
	$.extend(this, config || {});
	
	this.init();
};

/**
 * prototype
 */
$.mailinput.prototype = {
	/**
	 * 替换的输入元素
	 */
	target: null,
	
	/**
	 * 元素模板
	 */
	itemTemplate: null,
	
	/**
	 * 元素ID
	 */
	id: null,
	
	/**
	 * 是否支持多行输入
	 */
	multiLine: false,
	
	/**
	 * 分隔符
	 */
	separator: ';',
	
	/**
	 * 字体大小
	 */
	fontSize: 12,
	
	/**
	 * 可用最大对象
	 */
	maxCount: null,
	
	/**
	 * 输入框初始长度
	 */
	baseWidth: 14,
	
	/**
	 * 
	 */
	tabIndex: null,
	
	/**
	 * 是否按住ctrl
	 */
	_onCtrl: false,
	
	/**
	 * 是否按住shift
	 */
	_onShift: false,
	
	/**
	 * 
	 */
	_ele: null,
	
	/**
	 * 模拟的输入框
	 */
	_ipbox: null,
	
	/**
	 * 输入框
	 */
	_input: null,
	
	/**
	 * 输入显示
	 */
	_inputDisp: null,
	
	/**
	 * jQuery
	 */
	jq: $,
	
	/**
	 * 
	 */
	_enabled: true,
	
	/**
	 * 初始化
	 */
	init: function() {
		var ct = this.target.parent(),
			instance = this;
		this.target.attr('readonly', false).hide();
		
		if (null === this.tabIndex) {
			this.tabIndex = this.target.attr('tabindex');
		}
		
		this._ele = this.jq('<div>');
		this._ele
		.addClass('mail_input' + (this.cls ? ' ' + this.cls : ''))
		.css({cursor: 'text', outline: 'none', width: this.width ? this.width : 'auto'})
		.append(this._createInput());
		
		if (this.id) {
			this._ele.attr('id', this.id);
		}
		
		this._initEvents();
		
		if (!$.browser.msie) {
			this._ele.attr('tabindex', 0);
		}
		
		ct.append(this._ele);
		
		if (this.autoComplete) {
			this.autoComplete.jq = this.jq;
			$.extend(this.autoComplete, {target: this._input, attachTo: this._ele});
			this._autoComp = new $.autocomplete(this.autoComplete);
		}
		
		this._checkInputLen();
	},
	
	_createInput: function() {
		var instance = this;
		this._ipbox = this.jq('<div>');
		this._input = this.jq('<input>');
		this._input
		.attr({id: this.id + '-ip', type: 'text', autocomplete: 'off', tabindex: instance.tabIndex})
		.css({
			'width': '100%',
			'border': 'none',
			'background': 'none',
			'padding': '1',
			'margin': '0',
			'font-slsize': this.fontSize + 'px',
			'float': 'left',
			'outline': 'none',
			'-webkit-appearance': 'none'
		})
		.bind('keyup', function(e){instance._inputKeyup(e)})
		.bind('keydown', function(e){instance._inputKeydown(e)})
		.bind('blur', function(){
			var text = instance._input.val() + '';
			
			if (text && instance._autoComp && !instance._autoComp.isOver && !instance.isOver) {
				instance.appendItem(text);
				instance.setText('');
			}
			
			if (instance._autoComp && !instance._autoComp.isOver) {
				instance._autoComp.clearMatchList();
			}
		})
		.bind('click', function(e){
			TOP.stopEventBuddle(e);
		});
		
		this._inputDisp = this.jq('<div>');
		this._inputDisp
		.css({
			'float': 'left', 
			'width': '1px', 
			'height': '1px', 
			'fontSize': this.fontSize + 'px', 
			'overflow': 'auto', 
			'white-space': 'nowrap', 
			'word-spacing': '0px',
			'*overflow': 'hidden'
		});
		
		this._ipbox
		.css({
			'float': 'left',
			'width': this.baseWidth + 'px',
			'overflow': 'hidden',
			'margin-right': '-' + this.baseWidth + 'px'
		})
		.append(this._inputDisp)
		.append(this._input);
		
		return this._ipbox;
	},
	
	/**
	 * 
	 */
	_initEvents: function() {
		var instance = this;
		
		this._ele
		.bind('click', function(e){
			instance._ipbox.find(':text').focus();
			instance._ele.find('.mail_item_selected').removeClass('mail_item_selected');
			instance._setCursorPos();
			return instance._cancelEventBubble(e)
		})
		.bind('keydown', function(e){instance._elBoxKeydown(e);})
		.bind('mouseover', function(){instance.isOver = true})
		.bind('mouseout', function(){instance.isOver = false});
	},
	
	/**
	 * 获取光标在输入框中的位置
	 */
	_getCursorPos: function() {
		var CaretPos = 0;	// IE Support
		var ctrl = this._input[0];
		if (document.selection) {
			ctrl.focus ();
			var Sel = document.selection.createRange ();
			Sel.moveStart ('character', -ctrl.value.length);
			CaretPos = Sel.text.length;
		}
		// Firefox support
		else if (ctrl.selectionStart || ctrl.selectionStart == '0')
			CaretPos = ctrl.selectionStart;
		return (CaretPos);
	},
	
	_setCursorPos: function(pos) {
		var elem = this._input[0];
		var max  = elem.value.length;
		
		if (!pos) pos = max;
		
		if (elem.createTextRange) {
			var range = elem.createTextRange();
			range.move('character', pos);
			range.select();
		} else {
			elem.setSelectionRange(pos, pos);
			elem.focus();
			
			this._fixInputCursor();
		}
	},
	
	/**
	 * 
	 */
	_fixInputCursor: function() {
		if (document.createEvent) {
			elem = this._input[0];
			
			var evt = document.createEvent('KeyboardEvent');
			evt.initKeyEvent('keypress', true, true, null, false, false, false, false, 0, 32);
			elem.dispatchEvent(evt);
			evt = document.createEvent('KeyboardEvent');
			evt.initKeyEvent('keypress', true, true, null, false, false, false, false, 8, 0);
			elem.dispatchEvent(evt);
		}
	},
	
	/**
	 * 是否可选择内容
	 * 
	 * @private
	 * @param bool
	 * @return
	 */
	_setSelectable: function(ele, bool) {
		ele.unselectable = ele.unselectable = bool ? "off" : "on"; // IE
		ele.onselectstart = ele.onselectstart = function(){ return bool; }; // IE
		if (document.selection && document.selection.empty) document.selection.empty(); // IE
		if (ele.style) ele.style.MozUserSelect = bool ? "" : "none"; // FF
		ele.style.MozUserSelect = bool ? "" : 'none';
	},
	
	/**
	 * 
	 */
	_elBoxKeydown: function(e) {
		if (!this._enabled) return ;
		
		var key = e.keyCode ? e.keyCode : e.which;
		
		switch (key) {
			case 13:
				if (evt.preventDefault) {
					evt.preventDefault();
				}
				break;
			case 9:
				this.appendItem(this._input.val());
				this.setText('');
				break;
			case 8:
			case 46:
				var instance = this;
				this._ele.find('.mail_item_selected').each(function(){
					instance.removeItem(instance.jq(this));
				});
				
				if (key == 8 && !this.hasFocus()) {
					evt = window.event ? window.event : e;
					evt.keyCode = 0;
					evt.returnValue = false;
					
					if (evt.preventDefault) {
						evt.preventDefault();
					}
				}
				
				this.focus();
				
				break;
		}
		
		this._cancelEventBubble(e);
	},
	
	/**
	 * 
	 */
	_inputKeyup: function(e) {
		if (!this._enabled) return ;
		
		var key = e.keyCode ? e.keyCode : e.which;
		switch (key) {
			case 13:
				this._input.focus();
				break;
			// backspace & del
			case 8:
			case 46:
				this._inputDisp.text(this._input.val() + 'WW');
				break;
			// ctrl
			case 17:
				this._onCtrl = false;
				break;
			// shift
			case 16:
				this._onShift = false;
				break;
			// , ;
			case 188:
			case 186:
			case 59:
				var text = this._format(this._input.val());
				if (text) {
					this.appendItem(text);
					this.setText('');
					this.focus();
				}
				
				break;
			default:
				if (this.maxCount > 0) {
					var count = this._ele.find('.mail_item').size();
					
					if (this.maxCount <= count) {
						this._input.val('');
						return this._cancelEventBubble(e);
					}
				}
			
				var text = this._input.val();
				
				this._inputDisp.text(text + 'WW');
				break;
		}
		
		this._updateInputLen();
		return this._cancelEventBubble(e);
	},
	
	/**
	 * 输入框事件
	 */
	_inputKeydown: function(e) {
		if (!this._enabled) return ;
		
		var key = e.keyCode ? e.keyCode : e.which;
		var pos = this._getCursorPos();

		switch (key) {
			// enter
			case 13:
				if (this._autoComp && this._autoComp.isShow) {
					this._autoComp.confirmSelect();
				}
				break;
			// backspace
			case 8:
				if (pos <= 0 && this._ipbox.prev().size()) {
					this.removeItem(this._ipbox.prev());
					this._input.focus();
				} else {
					this._inputDisp.text(this._input.val() + 'WW');
				}
				
				break;
			// del
			case 46:
				var len = this._input.val().length;
				if (pos >= len && this._ipbox.next().size()) {
					this.removeItem(this._ipbox.next());
					this._input.focus();
				} else {
					this._inputDisp.text(this._input.val() + 'WW');
				}
				break;
			// home
			case 36:
				break;
			// end
			case 35:
				break;
			// left
			case 37:
				if (pos == 0 && this._ipbox.prev().size()) {
					this._ipbox.insertBefore(this._ipbox.prev());
					this.focus();
				}
				break;
			// up
			case 38:
				if (this._autoComp && this._autoComp.isShow) {
					this._autoComp.moveUp();
					return this._cancelEventBubble(e);
				}
				
				if (!this.multiLine) {
					return this._cancelEventBubble(e);
				}
				break;
			// right
			case 39:
				var len = this._input.val().length;
				if (pos >= len && this._ipbox.next().size()) {
					this._ipbox.insertAfter(this._ipbox.next());
					this.focus();
				}
				break;
			// down
			case 40:
				if (this._autoComp && this._autoComp.isShow) {
					this._autoComp.moveDown();
					return this._cancelEventBubble(e);
				}
				
				if (!this.multiLine) {
					return this._cancelEventBubble(e);
				}
				break;
			// ctrl
			case 17:
				this._onCtrl = true;
				break;
			// shift
			case 16:
				this._onShift = true;
				break;
			// tab
			case 9:
				var text = this._format(this._input.val());
				if (text) {
					this.appendItem(text);
					this.setText('');
				}
				break;

			default:
				if (this.maxCount > 0) {
					var count = this._ele.find('.mail_item').size();
					
					if (this.maxCount <= count) {
						this._input.val('');
						return this._cancelEventBubble(e);
					}
				}
				
				var text = this._input.val();
				
				this._inputDisp.text(text + 'WW');
				break;
		}
		
		this._updateInputLen();
		return this._cancelEventBubble(e);
	},
	
	/**
	 * 取消事件冒泡
	 */
	_cancelEventBubble: function(e) {
		e.cancelBubble = true;
		if (e.stopPropagation) {
			e.stopPropagation();
		}
		e.returnValue = false;
		return false;
	},
	
	_format: function(str) {
		if (!str) {
			return '';
		}
		
		var reg = new RegExp('/(^\s*)|(\s*$)/');
		
		str = str.replace(',', '').replace(';', '').replace(reg, '');
		
		return str;
	},
	
	disabled: function() {
		this._input.val('');
		this._input.attr('readonly', true);
		this._ele.addClass('mail_input_disabled');
		this._enabled = false;
	},
	
	enabled: function() {
		this._input.removeAttr('readonly');
		this._ele.removeClass('mail_input_disabled');
		this._enabled = true;
	},
	
	/**
	 * 
	 */
	focus: function() {
		if (!this._input) {
			return ;
		}
		
		var instance = this;
		if ($.browser.msie) {
			setTimeout(function(){
				if (!instance._input) {
					return ;
				}
				instance._input.focus();
				instance._setCursorPos();
			}, 100);
		} else {
			this._input.focus();
		}
	},
	
	/**
	 * 
	 */
	hasFocus: function() {
		var e = this._input[0];
		
		if (e.hasFocus) {
			return e.hasFocus();
		}
		
		return document.activeElement == e;
	},
	
	/**
	 * 添加项目触发事件
	 */
	onAppend: function(item) {
		
	},
	
	/**
	 * 删除项目触发事件
	 */
	onRemove: function() {
		
	},
	
	// 添加项目
	appendItem: function(item, params, callback) {
		var obj = this.jq('<div>');
			instance = this;
		obj
		.addClass('mail_item')
		.css('float', 'left')
		.bind('mouseover', function(){
			if (!instance._enabled) return ;
			instance.jq(this).addClass('mail_item_hover');
		})
		.bind('mouseout', function(){
			if (!instance._enabled) return ;
			instance.jq(this).removeClass('mail_item_hover');
		})
		.bind('click', function(e){
			if (!instance._enabled) return ;
			instance.jq('.mail_item_selected').not(this).removeClass('mail_item_selected');
			instance.jq(this).toggleClass('mail_item_selected');
			instance._ele.focus();
			instance._cancelEventBubble(e);
		});
		/*.bind('dblclick', function(e) {
			var o = $(this);
			instance._ipbox.insertBefore(o);
			instance.setText(o.text());
			instance._ele.focus();
			o.remove();
		});*/
		
		this._setSelectable(obj[0], false);
		
		item = this._format(item);
		this._ipbox.before(obj.html(item + this.separator));
		
		if (params) {
			for (var k in params) {
				obj.attr(k, params[k]);
			}
		}
		
		if (!callback) {
			callback = this.onAppend;
		}
		
		callback.call(this, obj);
	},
	
	/**
	 * 获取已输入项目
	 */
	getItems: function(filter) {
		if (!filter) {
			filter = '';
		}
		return this._ele.find('.mail_item' + filter);
	},
	
	/**
	 * 设置输入框文字
	 */
	setText: function(text) {
		if (!this._input) {
			return ;
		}
		
		this._input.val(text);
		this._inputDisp.text(text + 'WW');
		this._input.focus();
	},
	
	getText: function(text) {
		if (!this._input) {
			return '';
		}
		
		return this._input.val();
	},
	
	/**
	 * 设置实际输入框的宽度
	 */
	_updateInputLen: function() {
		if (!this._input || !this._ipbox) {
			return ;
		}
		try {
			if (this._input && !this._input.val()) {
				this._ipbox.css('width', this.baseWidth + 'px');
			} else {
				var width = Math.max(this._inputDisp[0].scrollWidth, this.baseWidth);
				
				this._ipbox.css('width', width + 'px');
				this._input[0].scrollLeft = 0;
			}
		} catch (e) {}
	},
	
	/**
	 * for IE
	 */
	_checkInputLen: function() {
		var instance = this;
		this._updateInputLen();
		setTimeout(function(){
			if (instance && instance._input && instance._ipbox) {
				instance._checkInputLen();
			}
		}, 100);
	},
	
	// 移除项目
	removeItem: function(item) {
		var instance = this;
		item.remove();
		item.each(function() {instance.onRemove(this);});
		
	},
	
	/**
	 * 情况所有项目
	 */
	removeAll: function() {
		var instance = this;
		this._ele.find('.mail_item').each(function(){
			var item = instance.jq(this);
			item.remove();
			instance.onRemove(item);
		});
	},
	
	// 清空内容
	clear: function() {
		this._input.val('');
		this._inputDisp.empty();
		this._ele.find('.mail_item').remove();
		this._ele.find('.mail_item_separator').remove();
	}
};

/**
 * 完成提示
 */
$.autocomplete = function(config) {
	$.extend(this, config || {});
	
	this.init();
}

/**
 * 
 */
$.autocomplete.prototype = {
	
	/**
	 * 固定宽度
	 */
	width: null,
	
	/**
	 * 数据源
	 * 
	 * @var Array 
	 */
	data: null,
	
	/**
	 * 数据获取URL
	 * 
	 */
	url: null,
	
	/**
	 * 检索字段
	 * 不指定对记录进行匹配
	 * 
	 * @var Array | null
	 */
	columns: null,
	
	/**
	 * 是否为可见状态
	 */
	isShow: false,
	
	/**
	 * 
	 */
	isOver: false,
	
	/**
	 * 显示格式
	 */
	template: null,
	
	/**
	 * 目标输入框
	 */
	target: null,
	
	/**
	 * 依附元素
	 */
	attachTo: null,
	
	/**
	 * 
	 */
	maxHeight: 200,
	
	/**
	 * 
	 */
	_el: null,
	
	/**
	 * 
	 */
	_matches: [],
	
	jq: $,
	
	/**
	 * 初始化
	 */
	init: function() {
		if (!this.target) {
			return false;
		}
		
		var instance = this;
		this._el = this.jq('<div>');
		
		this._el
		.addClass('ac_list')
		.hide()
		.appendTo(this.target.parents('body'));
		
		var width = (this.width ? this.width : (this.attachTo ? this.attachTo.width() : null));
		
		this._el.css('width', width + 'px');
		
		if (!this.attachTo) {
			this.attachTo = this.target;
		}
		
		this._el
		.bind('mouseover', function(){instance.isOver = true;})
		.bind('mouseout', function(){instance.isOver = false;});
		
		this.jq(window).bind('click', function(){
			instance._el.hide();
		});
		this.target.bind('keyup', function(e){
			var	key = e.keyCode ? e.keyCode : e.which;
			if (instance.isShow && (key == 38 || key == 40 || key == 13)) {
				if (instance.arrowSupport) {
					if (key == 38) {
						return instance.moveUp();
					}
					
					if (key == 40) {
						return instance.moveDown();
					}
				}
				
				return ;
			}
			
			if (!this.value) {
				instance.clearMatchList();
				instance._el.hide();
			} else {
				instance.initMatchList(instance.target.val());
			}
		}).bind('keydown', function(e){
			var	e   = window.event ? window.event : e,
				key = e.keyCode ? e.keyCode : e.which;
			if (key == 13 && instance.isShow) {
				e.keyCode = e.which = 0;
				e.returnValue = false;
				if (e.preventDefault) {
					e.preventDefault();
				}
				instance._cancelEventBubble(e);
				instance.confirmSelect();
				return false;
			}
		}).attr({'autocomplete': 'off'});
		
		this._ul = this.jq('<ul>');
		
		this._el.append(this._ul);
	},
	
	/**
	 * 数据加载完成触发事件
	 */
	onLoaded: function(ret) {
		
	},
	
	/**
	 * 被选中触发事件
	 */
	onSelect: function(item) {
		
	},
	
	/**
	 * 选择匹配数据
	 */
	match: function(key) {
		key = key.toLowerCase();
		var datas = isArray(this.data) ? {'': this.data} : this.data;
		this._matches = {};
		
		if (datas) {
			for (var p in datas) {
				if (this['no' + p] != undefined && this['no' + p]) {
					continue;
				}
				
				var cols = (this.columns && this.columns[p] != undefined) ? this.columns[p] : this.columns,
					data = datas[p];
				this._matches[p] = [];
				
				var colCount = cols && cols.length ? cols.length : 0;
				for (var k in data) {
					if (colCount) {
						for (var i = 0; i < colCount; i++) {
							if (data[k][cols[i]] != undefined
								&& data[k][cols[i]].toLowerCase().indexOf(key) != -1) 
							{
								this._matches[p].push(data[k]);
								break;
							}
						}
					} else {
						if (typeof(data[k].indexOf) == 'function'
							&& data[k].toLowerCase().indexOf(key) != -1)
						{
							this._matches[p].push(data[k]);
						}
					}
				}
			}
		}
	},
	
	/**
	 * 初始化列表项目
	 */
	_initList: function(keyword) {
		this._ul.empty();
		
		if (this.maxHeight) {
			this._el.css({'height': this.maxHeight + 'px', 'z-index': 10005});
		}
		var instance = this;
		
		for (var k in this._matches) {
			var ms = this._matches[k];
			var tpl = this.template && this.template[k] != undefined ? this.template[k] : this.template;
			
			for (var i = 0, c = ms.length; i < c; i++) {
				var item = this.jq('<li>');
				
				item[0].data = ms[i];
				item
				.bind('mouseover', function(){
					instance._el.find('.ac_item_over').removeClass('ac_item_over');
					instance.jq(this).addClass('ac_item_over');
				})
				.bind('click', function(e){
					instance.hide();
					instance.onSelect(this);
					return instance._cancelEventBubble(e);
				});
				
				if (!tpl) {
					var text = ms[i] + '';
					text = text.replace(keyword, '<strong>' + keyword + '</strong>');
					item.text(text);
				} else {
					var m = tpl.match(/\{\w+\}/gm);
					var text = tpl;
					
					for (var p in m) {
						var key = m[p] + '';
						key = key.replace(/[\{\}]/g, '');
						var val = ms[i][key];
						
						if (!val) {
							val = '';
						}
						
						text = text.replace('{' + key + '}', val.replace(keyword, '<strong>' + keyword + '</strong>'));
					}
					item.html(text);
				}
				
				this._ul.append(item);
			}
		}
	},
	
	initMatchList: function(keyword) {
		var instance = this;
		var isNull = true;
		
		if (isArray(this.data) && this.data.length > 0) {
			isNull = false;
		} else {
			for (var k in this.data) {
				if (this.data[k] && this.data[k].length) {
					isNull = false;
					break;
				}
			}
		}
		
		if (isNull) {
			if (this.url) {
				$.ajax({
					type: 'GET',
					dataType: 'json',
					url: this.url,
					success: function(ret) {
						instance.onLoaded(ret);
						
						instance._initMatchList(keyword);
					},
					error: function(res) {
						return false;
					}
				});
			} else if (this.loadMethod) {
				this.loadMethod.call(this);
			}
		} else {
			if (this.loadMethod) {
				this.loadMethod.call(this);
			}
			this._initMatchList(keyword);
		}
	},
	
	/**
	 * 
	 */
	_initMatchList: function(keyword) {
		this.match(keyword);
		
		var count = 0;
		
		for (var k in this._matches) {
			count += (this._matches[k] && this._matches[k].length ? this._matches[k].length : 0);
		}
		
		if (count > 0) {
			this._initList(keyword);
			this._el.show();
			this.isShow = true;
			var height = this.maxHeight;
			
			if (this.maxHeight > 0) {
				height = this._ul.height();
				
				if (height < this.maxHeight) {
					this._el.css('height', height + 'px');
				}
			}
			
			var offset = this.attachTo.offset();
			
			this._el
			.css({left: offset.left + 'px', top: offset.top + this.attachTo.height() + 5 + 'px'})
			
			this._ul.find('li:first').addClass('ac_item_over');
		} else {
			this.clearMatchList();
		}
	},
	
	clearMatchList: function() {
		this._matches = {};
		this._ul.empty();
		this.hide();
		this.isShow = false;
	},
	
	/**
	 * 
	 */
	hide: function() {
		this._el.hide();
	},
	
	/**
	 * 焦点上移动
	 */
	moveUp: function() {
		var onfocus = this._ul.find('.ac_item_over');
		if (!onfocus.size() || !onfocus.prev().size()) {
			this._ul.find('li:last').addClass('ac_item_over');
			this._el[0].scrollTop = this._el[0].scrollHeight;
			return ;
		}
		
		if (onfocus.prev()) {
			onfocus.removeClass('ac_item_over');
			onfocus.prev().addClass('ac_item_over');
			
			var top = onfocus.prev()[0].offsetTop;
			
			if (top < this._el[0].scrollTop) {
				this._el[0].scrollTop = top;
			}
		}
	},
	
	/**
	 * 焦点向下移动
	 */
	moveDown: function() {
		var onfocus = this._ul.find('.ac_item_over');
		if (!onfocus.size() || !onfocus.next().size()) {
			this._ul.find('li:first').addClass('ac_item_over');
			this._el[0].scrollTop = 0;
			return ;
		}
		
		if (onfocus.next()) {
			onfocus.removeClass('ac_item_over');
			onfocus.next().addClass('ac_item_over');
			
			var top = onfocus.next()[0].offsetTop;
			
			if (top >= this._el[0].scrollTop + this._el.height() || top < this._el[0].scrollTop) {
				this._el[0].scrollTop = top;
			}
		}
	},
	
	/**
	 * 是否有选中项目
	 */
	hasSelect: function() {
		return this._ul.find('.ac_item_over').size() > 0;
	},
	
	/**
	 * 确认选中
	 */
	confirmSelect: function() {
		var item = this._ul.find('.ac_item_over');
		
		if (item.size()) {
			this.hide();
			this.onSelect(item[0]);
		}
	},
	
	/**
	 * 取消事件冒泡
	 */
	_cancelEventBubble: function(e) {
		e.cancelBubble = true;
		if (e.stopPropagation) {
			e.stopPropagation();
		}
		
		return false;
	}
};

})(jQuery);

function isArray(obj) {
	return Object.prototype.toString.apply(obj) == '[object Array]';
}