/*
 * jQuery window plug-in 1.1
 *
 * Copyright (c) 2010 - 2011 Oray
 * 
 * $Id: jquery.window-1.1.0.source.js 1387 2011-12-14 02:07:40Z web_op $
 */

(function ($) {
	
$.window = function (options) {
	var self = this;

	this.opts = $.extend({}, $.window.defaults, options);
	this.fixIE = ($.browser.msie && ($.browser.version<7));
	
	this.id = Math.random();
	
	if (!this.opts.applyTo) return;
	var container = $(this.opts.applyTo).css({zIndex: 8001});
	this.dialog = {
		container: container,
		header: container.find('.window-header'),
		body: container.find('.window-body'),
		footer: container.find('.window-footer')
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
	}

	if (this.fixIE) {
		this.iframe = $('<iframe src="javascript:false;">')
			.css({opacity:0, position:'absolute', zIndex:1000, width:'100%', top:0, left:0})
			.hide()
			.appendTo('body');
	}
	
	if (this.opts.draggable && $.fn.drag) {
		this.dialog.container.drag(this.opts.drag);
	}
	
	this.mask =  $('<div class="mask">').css({zIndex: 8000}).appendTo(document.body).hide();
	this.dialog.container.hide().appendTo(document.body);

	this.dialog.container.find('.' + this.opts.closeCls).click(function (e) {
		e.preventDefault();
		self.close();
	});
	
	this.dialog.container.css({'height': this.opts.height, 'width': this.opts.width});

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
	},
	
	close: function(){
		if (this.iframe) this.iframe.hide();
		this.mask.hide();
		this.dialog.container.hide();
		if ($.isFunction(this.fn.close)) {
			this.fn.close.apply(this, [this.dialog]);
		}
		this.unbindEvents();
	},
	
	resizeMask: function(){
		if(this.fixIE) {
			var height = Math.max(document.body.offsetHeight, this.bh.height());
			var width = document.body.offsetWidth;
			this.mask.css({
				height: height,
				width: width
			});
			if (this.iframe) {
				this.iframe.css({height: height,width: width});
			}
		} else {
			this.mask.css('position', 'fixed');
		}
	},
	
	center: function(){
		var x = (document.body.offsetWidth - $(this.dialog.container).width()) /2;
		var y = 0;
		if (this.dialog.container.height() < Math.min(this.bh.height(), document.documentElement.offsetHeight)) {
			y = ((Math.min(this.bh.height(), document.documentElement.offsetHeight) - this.dialog.container.height()) / 2 + Math.max(document.documentElement.scrollTop, document.body.scrollTop))
		}
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
	}
	
}

$.fn.window = function (options) {
	
	// if nothing is selected, return nothing; can't chain anyway
	if (!this.length) {
		options && options.debug && window.console && console.warn( "nothing selected, can't open window, returning nothing" );
		return;
	}
	
	// check if a window for this element was already created
	var window = $.data(this[0], 'window');
	if (window) {
		return window;
	}
	
	window = new $.window($.extend(options, {applyTo: this[0], id: this[0].id}));
	$.data(this[0], 'window', window);
	
	return window;
};

// default config
$.window.defaults = {
	id: 'window',
	width: false,
	height: false,
	closeCls: 'close',
	draggable: false,
	show: null,
	close: null
};

})(jQuery);
