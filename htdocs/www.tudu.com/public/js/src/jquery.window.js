(function ($) {

$.window = function (options) {
	var self = this;

	//console.log($.window)
	this.opts = $.extend({}, $.window.defaults, options);
	this.fixIE = ($.browser.msie && ($.browser.version<7));
	
	this.id = Math.random();
	
	if (!this.opts.applyTo) return;
	var container = $(this.opts.applyTo);
	container.css('z-index', 8001);
	this.dialog = {
		container: container,
		header: container.find('.pop_header'),
		body: container.find('.pop_body'),
		footer: container.find('.pop_footer')
	};
	
	this.bh = $('#fix-height');
	if (!this.bh.size()) {
		this.bh = $('<div>')
		.attr('id', 'fix-height')
		.css({
			 position: 'absolute',
			 //background: '#f00',
			 height: '100%',
			 width: 1,
			 left: -100,
			 top:0
		})
		.appendTo(document.body);
	}

	this.fn = {
		show: this.opts.show,
		close: this.opts.close
	};

	this.mask =  $('<div class="mask">').appendTo(document.body).hide();
	this.mask.css('z-index', 8000);
	this.dialog.container.hide().appendTo(document.body);

	this.dialog.container.find('.' + this.opts.closeCls).click(function (e) {
		e.preventDefault();
		self.close();
	});

	this.dialog.container.css({'height': this.opts.height, 'width': this.opts.width});
	
	if (this.fixIE) {
		this.iframe = $('<iframe frameborder="0" src="javascript:false;">')
			.css({opacity:0, position:'absolute', zIndex:-1, width:'100%', height: this.mask.height(), top:0, left:0})
			.hide()
			.appendTo(this.mask);
	}
	
	if (this.opts.draggable) {
		//this.dialog.container.drag(this.opts.drag);
		this.dragDelegate = new $.dragable({
			ele: this.dialog.container[0],
			triggerEle: this.dialog.header[0]
		});
	}

	return this;

};

$.window.prototype = {
	show: function(){
		if (this.iframe) this.iframe.show();
		this.mask.show();
		this.resizeMask();
		this.dialog.container.show();
		this.center();
		
		if ($.isFunction(this.fn.show)) {
			this.fn.show.apply(this, [this.dialog]);
		}
		this.bindEvents();
		this.opts.onShow();
	},
	
	close: function(){
		if (this.iframe) this.iframe.hide();
		this.mask.hide();
		this.dialog.container.hide();
		if ($.isFunction(this.fn.close)) {
			this.fn.close.apply(this, [this.dialog]);
		}
		this.unbindEvents();
		this.opts.onClose();
	},
	
	resizeMask: function(){
		if(this.fixIE) {
			var height = Math.max(document.body.offsetHeight, this.bh.height());
			var width = document.body.offsetWidth;
			this.mask.css({
				height: height,
				width: width
			});
			//if (this.iframe) {
			//	this.iframe.css({height: height,width: width});
			//}
		}
	},
	
	center: function(){
		var x = (document.body.offsetWidth - $(this.dialog.container).width()) /2;
		var y = ((Math.min(this.bh.height(), document.documentElement.offsetHeight) - this.dialog.container.height()) / 2 + Math.max(document.documentElement.scrollTop, document.body.scrollTop))
		this.dialog.container.css({left: x, top: y});
	},
	
	bindEvents:function () {
		var self = this;
		if (this.fixIE)
			$(window).bind('resize', function(){self.resizeMask()});
	},
	
	unbindEvents:function () {
		var self = this;
		if (this.fixIE)
			$(window).unbind('resize', function(){self.resizeMask()});
	},
	
	destroy: function() {
		this.mask.remove();
		this.dialog.container.remove();
		return null;
	},
	
	hide: function() {
		if (this.iframe) this.iframe.hide();
		this.mask.hide();
		this.dialog.container.hide();
	},
	
	find: function(query) {
		return this.dialog.container.find(query)
	}
}



$.fn.window = function (options) {
	var self = this[0];
	
	return self && self.window ?
	//var ret =  self && self.window ?
		self.window :
		self.window = new $.window($.extend(options, {applyTo: self, id: self.id}));
	
	//return new $.window($.extend(options, {applyTo: self}));

};


$.window.defaults = {
	id: 'window',
	width: 500,
	height: 'auto',
	close: true,
	closeCls: 'close',
	draggable: false,
	onOpen: function(){},
	onShow: function(){},
	onClose: function(){}
};

/*
*/


$.window.prototype2 = {
	id: null,
	
	defaults: {
		id: 'window',
		width: 500,
		height: 'auto',
		close: true,
		closeCls: 'close',
		draggable: false,
		onOpen: null,
		onShow: null,
		onClose: null
	},
	
	show2: function () {
		
		//this.mask.setSize(Ext.lib.Dom.getViewWidth(true), Ext.lib.Dom.getViewHeight(true));
		//

		console.log(this);
		
		//alert(this.dialog.container.attr('id'));
		
		return;
		
		//this.container.bind('resize', this.mask.setSize);
		//('body').resize(function(){alert(0)});
		//this.bh._height = this.bh.height(); 
	
		this.mask.show().setSize();
		if (this.dialog.iframe)
				this.dialog.iframe.show();
		this.dialog.container.show();//.focus();
		this.center();
		this.bindEvents();
		
		if ($.isFunction(this.fn.onShow)) {
			this.fn.onShow.apply(this, [this.dialog]);
		}
	},	
	
	
	container: 'body',
	
	dialog: {},
	
	fn: {},
	
	init: function (options) {
		
		//alert($.window.fn.container);
		
		console.log(this);

		return;

		this.opts = $.extend({}, $.window.defaults, options);
		
		this.dialog.container = $('<div>');
		
		//alert(this.opts.applyTo);
		
		if (this.opts.applyTo) {
			this.dialog.container = $(this.opts.applyTo);
			this.dialog.header = this.dialog.container.find('.window-header');
			this.dialog.body = this.dialog.container.find('.window-body');
			this.dialog.footer = this.dialog.container.find('.window-footer');
		}
		
		this.fn.onShow = this.opts.onShow;
		this.fn.onClose = this.opts.onClose;
		/*
		if(this.dialog.data) {
			return false;
		}
		if(typeof data == 'object') {
			data = (data instanceof jQuery) ? data :$(data);
			if(data.parent().parent().size()>0) {

				this.dialog.parentNode = data.parent();
				
				if(!this.opts.persist) {
					this.dialog.original = data.clone(true);
				}
			}

			
		} else if (typeof data=='string'||typeof data=='number') {
			data = $('<div>').html(data);
		} else {
			if (console) {
				console.log('SimpleModal Error: Unsupported data type: '+typeof data);
			}
			return false;
		}
		
		this.dialog.data = data.addClass('modalData');
		data = null;
		*/
		
		this.create();
		
		
		this.resize();
		
		//this.show();
		
		//	this.opts.onShow.apply(this, [this.dialog]);
		//}
		
		return this;
	},
	
	create: function () {
		
		var _this = this;

		this.container = $(this.container);//.mask().unmask();
		

		this.mask = $('<div class="mask">');
		
		
		if (!this.dialog.header){
			this.dialog.header = $('<div>').addClass('window-header');
			this.dialog.container.append(this.dialog.header);
		}
		
		if (!this.dialog.body) {
			this.dialog.body = $('<div>').addClass('window-body');
			this.dialog.container.append(this.dialog.body);
		}
		
		if (!this.dialog.container) {
			this.dialog.container = $('<div>')
				.attr('id', this.opts.id)
				.addClass('window')
				.css({'height': this.opts.height, 'width': this.opts.width})
				.hide();
		}
		
		if (this.dialog.footer)
			this.dialog.container.append(this.dialog.footer);
		this.mask.appendTo(this.container).hide()
		this.dialog.container.hide().appendTo('body');

		
		//this.dialog.body.html('sa<br />dasdsadsa<br />dasdsadsa<br />dasdsadsa<br />dasdsadsa<br />dasdsadsa<br />dasdsadsa<br />dasdsad');
		
		
		this.container.find('.' + this.opts.closeCls).click(function (e) {
			e.preventDefault();
			_this.close();
		});
		
		
		if (this.fixIE) {
			this.dialog.iframe = $('<iframe src="javascript:false;">')
				.css({opacity:0,position:'absolute', zIndex:1000, width:'100%', top:0, left:0})
				.hide()
				.appendTo('body');
		}

		if (this.opts.draggable && $.fn.drag) {
			this.dialog.container.drag(this.opts.drag);
		}
		
		this.mask.setSize = function(){
			if(_this.fixIE) {
				var height = Math.max(_this.container.height(), _this.bh.height());
				var width = _this.container.width();
				_this.mask.css({
					height: height,
					width: width
				});
				if (_this.dialog.iframe) {
					_this.dialog.iframe.css({height: height,width: width});
				}
			} else {
				_this.mask.css('position', 'fixed');
			}
		}

		this.center = function(){
			//alert(0)
			//if ($.browser.msie)
			//	$(document.body).css('height', '100%');
			////alert(1)
			var x = (_this.container.width() - $(_this.dialog.container).width()) /2;
			var y = ((Math.min(_this.bh.height(), document.documentElement.offsetHeight) - _this.dialog.container.height()) / 2 + Math.max(document.documentElement.scrollTop, document.body.scrollTop))
			_this.dialog.container.css({left: x, top: y});
		}
		


		/*
		this.dialog.container = $('<div>').attr('id', this.opts.containerId).addClass('window').css(this.opts.containerCss).append(this.opts.close?'<a class="modalCloseImg '+this.opts.closeClass
		+'" title="'+this.opts.closeTitle+'"></a>':'').hide().appendTo('body');
		
		if (this.opts.top) {
			this.dialog.container.css('top', this.opts.top)
		}
		
		if (this.opts.left) {
			this.dialog.container.css('left', this.opts.left)
		}
		*/
		/*
		this.dialog.container.append(this.dialog.data.hide());
		*/
	},
	
	resize: function (){
		//alert(this.dialog.container.attr('id'));
		this.dialog.container.css({'height': this.opts.height, 'width': this.opts.width});
	},
	
	bindEvents:function () {
		if (this.fixIE)
			$(window).bind('resize', this.mask.setSize);

		$(window).bind('resize', this.center);
		
		////$(document).keyup(function(event){
		//});

		/*
		var modal=this;
		$('.'+this.opts.closeClass).click(function (e) {
			e.preventDefault();
			modal.close();
		});
		*/
	},
	
	unbindEvents:function () {
		if (this.fixIE)
			$(window).unbind('resize', this.mask.setSize);
		
		$(window).unbind('resize', this.center);
		
		/*
		$('.'+this.opts.closeClass).unbind('click');
		*/
	},
	
	//resizeMask
	
	showMask: function() {
		/*
		if($.browser.msie && ($.browser.version<7)) {
			var height = this.container.outerHeight();
			var width = this.container.outerWidth();
			this.mask.css({
				position: 'absolute',
				height: height + 'px',
				width: width + 'px'
			});
		} else if (this.container[0].tagName == 'BODY') {
			this.mask.css('position', 'fixed');
		}
		*/
	},
	
    _center: function(){
		// var xy = this.el.getAlignToXY(this.container, 'c-c');
		// this.setPosition(xy[0], xy[1]);
		// return this;
		var x = (this.container.width() - $(this.dialog.container).width()) /2;
		var y = ((this.bh.height() - this.dialog.container.height()) / 2 + Math.max(document.documentElement.scrollTop, document.body.scrollTop))
		
		//alert(this.bh.height());
		
		//alert(y);
		
		//var h =document.body.scrollHeight;
		//alert(h);
		
		//alert(document.documentElement)
		

		//var x = (this.container.height() - $(this.dialog.container).height()) / 2;

		//alert([x, y])
		this.dialog.container.css({left: x, top: y});
		
    },

	close: function (external) {
		this.mask.hide()
			if (this.dialog.iframe)
				this.dialog.iframe.hide();
		this.dialog.container.hide();
		this.unbindEvents();
		
		if ($.isFunction(this.fn.onClose)) {
			this.fn.onClose.apply(this, [this.dialog]);
		}
		
		/*
		if(!this.dialog.data) {
			return false;
		} if ($.isFunction(this.opts.onClose)&&!external) {
			this.opts.onClose.apply(this, [this.dialog]);
		} else {
			if (this.dialog.parentNode) {
				if (this.opts.persist) {
					this.dialog.data.hide().appendTo(this.dialog.parentNode);
				} else {
					this.dialog.data.remove();
					this.dialog.original.appendTo(this.dialog.parentNode);
				}
			} else {
				this.dialog.data.remove();
			}
			this.dialog.container.remove();
			this.dialog.overlay.remove();
			if( this.dialog.iframe) {
				this.dialog.iframe.remove();
			}
			this.dialog = {};
		}
		
		*/
	}
};


})(jQuery);


/**
 * 元素拖放实现
 * 
 * 
 * @namespace OUI.Widget
 * @constructor
 */
$.dragable = function(config) {
	$.extend(this, config || {});
	
	if (!this.ele) return ;
	
	this.triggerEle = this.triggerEle || this.ele;
	
	this.init();
};

(function($){

$.dragable.prototype = {
	
	/**
	 * 拖放元素的对象
	 * 
	 * @field
	 * @private
	 */
	ele: null,
	
	/**
	 * 触发拖放效果的元素对象
	 * 
	 * @field
	 * @private
	 */
	triggerEle: null,
	
	/**
	 * 范围约束元素的DOM对象
	 * 
	 * @field
	 * @private
	 */
	constrainEle: null,
	
	/**
	 * 范围约束
	 * [top, right, bottom, left]
	 * 
	 * @field
	 * @public
	 */
	constrain: null,
	
	/**
	 * 是否锁定水平位置
	 * 
	 * @field
	 * @public
	 */
	lockX: false,
	
	/**
	 * 是否锁定垂直位置
	 * 
	 * @field
	 * @public
	 */
	lockY: false,
	
	/**
	 * 鼠标横坐标
	 * 
	 * @field
	 * @private
	 */
	_mouseX: null,
	
	/**
	 * 鼠标纵坐标
	 * 
	 * @field
	 * @private
	 */
	_mouseY: null,
	
	/**
	 * 初始化
	 */
	init: function() {
		var instance = this;
		$(this.ele).css({
			position: 'absolute'
		});
		
		if (!this.constrainEle && !this.constrain) {
			this._getDocumentConstrain();
		}
		
		$(this.triggerEle)
		.css('cursor', 'move')
		.bind('mousedown', function(e){instance._startDrag(e);});
		
		$(window).bind('resize', function(){instance._getDocumentConstrain();});
	},
	
	/**
	 * 使用文档作限制范围
	 * 
	 * @return
	 */
	_getDocumentConstrain: function() {
		var bt = !this.ele.style.borderTopWidth.replace('px') ? 0 : parseInt(this.ele.style.borderTopWidth.replace('px')),
			br = !this.ele.style.borderRightWidth.replace('px') ? 0 : parseInt(this.ele.style.borderRightWidth.replace('px')),
			bl = !this.ele.style.borderLeftWidth.replace('px') ? 0 : parseInt(this.ele.style.borderLeftWidth.replace('px')),
			bb = !this.ele.style.borderBottomWidth.replace('px') ? 0 : parseInt(this.ele.style.borderBottomWidth.replace('px'));
		
		this.constrain = [
			0 + bt,
			document.body.clientWidth - $(this.ele).width() - br,
			document.body.clientHeight - $(this.ele).height() - bb,
			0 + bl
		];
	},
	
	/**
	 * 是否可选择内容
	 * 
	 * @private
	 * @param bool
	 * @return
	 */
	_setSelectable: function(bool) {
		if (!this.ele) return; // maybe element was removed ? 
		document.body.unselectable = this.ele.unselectable = bool ? "off" : "on"; // IE
		document.body.onselectstart = this.ele.onselectstart = function(){ return bool; }; // IE
		if (document.selection && document.selection.empty) document.selection.empty(); // IE
		if (this.ele.style) this.ele.style.MozUserSelect = bool ? "" : "none"; // FF
		document.body.style.MozUserSelect = bool ? "" : 'none';
	},
	
	/**
	 * 禁止拖放
	 * 
	 * @public
	 * @return
	 */
	disableDrag: function() {
		
	},
	
	/**
	 * 允许拖放
	 * 
	 * @public
	 * @return
	 */
	enableDrag: function() {
		
	},
	
	/**
	 * 开始拖放触发事件
	 * 
	 * @event
	 */
	onDrag: function() {},
	
	/**
	 * 释放触发事件 
	 */
	onDrop: function() {},
	
	/**
	 * 拖放实现
	 * 
	 * @private
	 * @param e
	 * @return
	 */
	_startDrag: function (e) {
		var data = e.data || {}, instance = this;
		var offset = {
			left: e.pageX - this.ele.offsetLeft,
			top:  e.pageY - this.ele.offsetTop
		};
		var constrain = this.constrain || (function(ct){
			var ret = [
	           ct.offsetTop,
	           ct.offsetLeft + ct.offsetWidth - $(instance.ele).width() - parseInt(instance.ele.style.borderRightWidth.replace('px')) - 1,
	           ct.offsetTop + ct.offsetHeight - $(instance.ele).height() - parseInt(instance.ele.style.borderBottomWidth.replace('px')) - 1,
	           ct.offsetLeft
			];
			
			this.constrain = ret;
			
			return ret;
		})(this.constrainEle);
		
		var data = {offset: offset, constrain: constrain};
		
		this._setSelectable(false);
		
		this.onDrag();
		
		$(document).bind('mouseup', function(e){instance._endDrag(e);});
		$(document).bind('mousemove', data, function(e){instance._drag(e);});
	},
	
	/**
	 * 鼠标拖动
	 *
	 * @param e
	 * @return
	 */
	_drag: function(e) {
		var data = e.data || {};
		var pos = {
			left: e.pageX - data.offset.left,
			top:  e.pageY - data.offset.top
		};
		
		if (data.constrain[0] != undefined && pos.top < data.constrain[0]) {
			pos.top = data.constrain[0];
		}
		
		if (data.constrain[2] != undefined && pos.top > data.constrain[2]) {
			pos.top = data.constrain[2];
		}
		
		if (data.constrain[1] != undefined && pos.left > data.constrain[1]) {
			pos.left = data.constrain[1];
		}
		
		if (data.constrain[3] != undefined && pos.left < data.constrain[3]) {
			pos.left = data.constrain[3];
		}
		
		if (!this.lockX) {
			this.ele.style.left = pos.left + 'px';
		}
		
		if (!this.lockY) {
			this.ele.style.top  = pos.top + 'px';
		}
	},
	
	/**
	 * 结束拖放
	 * 
	 * @param e
	 * @return
	 */
	_endDrag: function(e) {
		$(document).unbind('mousemove');
		$(document).unbind('mouseup');
		this._setSelectable(true);
		
		this.onDrop();
	}
};
})(jQuery);