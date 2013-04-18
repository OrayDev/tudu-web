/**
 * 内层框架公用方法
 */
if (typeof getTop != 'function') {
    function getTop() {
        return parent ? parent : window;
    }
}

if (undefined !== TOP) {
    TOP = getTop();
} else {
    var TOP = getTop();
}

$(function(){
    // 针对移动设备调整框架页面大小
    if (TOP.Device && (TOP.Device.Android || TOP.Device.iOS)) {
        var height = document.body.offsetHeight;
        TOP.Frame.setMainHeight(height);
    }
});

var CURRENT_VERSION = '1.7.0';

/*版本自动刷新*/
if ((!TOP.version || TOP.version != CURRENT_VERSION) && TOP.showMessage) {
    TOP.showMessage('<a href="javascript:void(0)" onclick="location.reload(true)">图度系统版本过旧，点击这里刷新页面加载更新内容，否则将造成部分功能异常。</a>', 0);
}

var FixToolbar = function(params) {
    if (!params.src || !params.target) {
        return ;
    }
    var me = this;
    this.src    = typeof params.src == 'string' ? $(params.src) : params.src;
    this.target = typeof params.target == 'string' ? $(params.target) : params.target;

    if (params.width && undefined !== params.width) {
        this.target.css('width', params.width + 'px');
    } else {
        this.target.css('width', $(document.body).width() + 'px');
    }
    _showTarget();

    $(window).bind('scroll', function(){
        _showTarget();
    }).bind('resize', function(){
        if (params.width && undefined !== params.width) {
            me.target.css('width', params.width + 'px');
        } else {
            me.target.css('width', $(document.body).width() + 'px');
        }
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
window.onload = function() {
    $(document.body).bind('click', function(){
        if (!TOP.getJQ()('#side-label-menu').size() || TOP.getJQ()('#side-label-menu').is(":hidden")) {
            return ;
        }
        if (TOP.Label) {
            TOP.Label.hideMenu();
        }
    });
}