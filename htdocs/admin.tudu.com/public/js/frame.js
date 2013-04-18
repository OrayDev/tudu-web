if (typeof getTop == 'undefined') {
	function getTop() {
		return parent;
	}
}

var _TOP = getTop();

var BASE_PATH = _TOP.BASE_PATH ? _TOP.BASE_PATH : '';

window.Admin   = _TOP.Admin || {};
window.Message = _TOP.Message || {};
window.Util    = _TOP.Util || {};
window.Cookie  = _TOP.Cookie || {};

// 复制上层定义的jQuery扩展方法
_TOP.jQueryExtend(jQuery);

$(function(){
_TOP.title(document.title);
_TOP.hash(location.pathname + (location.search ? location.search : ''));
var lc = $('#css-skin'),
	tc = Frame.queryParent('#css-skin');
if (tc.attr('href') != lc.attr('href')) {
	tc.attr('href', lc.attr('href'));
}
});

var Frame = {
	
	queryParent: function(query) {
		return _TOP.jQuery(query);
	},
	
	getJQ: function() {
		return _TOP.jQuery;
	}
};

var FixToolbar = function(params) {
    if (!params.target) {
        return ;
    }
    var me  = this,
    	win = $(window);

    this.src    = typeof params.src == 'string' ? $(params.src) : params.src;
    this.target = typeof params.target == 'string' ? $(params.target) : params.target;

    if (this.src) {
        _showTarget();
        
	    win.bind('scroll', function(){
	    	_showTarget();
	    });
    }
    
    this.target.css('width', $(document.body).width() + 'px');
    
    if ($.browser.msie && $.browser.version<7) {
	    var iframe = $('<iframe src="javascript:false;">')
	    	.css({opacity:0, position:'absolute', zIndex:-1, width:'100%', height: this.target.height(), top:0, left:0})
	    	.appendTo(this.target);
	}
    
    win.bind('resize', function(){
        me.target.css('width', $(document.body).width() + 'px');
    });

    function _showTarget() {
    	var scrollTop = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop;

        if (me.src[0].offsetTop < scrollTop) {
            me.target.show();
        } else {
            me.target.hide();
        }
    };
};