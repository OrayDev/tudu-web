/**
 * 调用提醒窗口
 * 快捷方法
 * 入口
 */
var Messager = {
    _TPLS: {
        Window: '<div class="pop"><div class="pop_header"><strong></strong><a class="icon icon_close close"></a></div><div class="pop_body"></div></div>'
    },

    window: function(config){
        var obj = config.id ? $('#' + config.id) : null,
            params = {},
            init = null,
            tpl = Messager._TPLS.Window;

        if (null === obj || !obj.size()) {
            obj = $('<div class="messager"></div>');
        }

        if (config.form !== false) {
            tpl = ['<form method="post" action="?">', tpl, '</form>'].join('');
        }

        obj.html(tpl);

        for (var k in config) {
            switch (k) {
                case 'id':
                    obj.attr('id', config[k]);
                    break;
                case 'formid':
                    obj.find('form').attr('id', config[k]);
                    break;
                case 'formurl':
                case 'action':
                    obj.find('form').attr('action', config[k]);
                    break;
                case 'body':
                    obj.find('.pop_body').html(config[k]);
                    break;
                case 'footer':
                    var footer = $('<div class="pop_footer"></div>');
                    footer.html(config[k]);
                    obj.find('.pop').append(footer);
                    break;
                case 'title':
                    obj.find('.pop_header strong').text(config[k]);
                    break;
                case 'width':
                case 'height':
                case 'autoCloseTime':
                case 'anims':
                case 'onShow':
                case 'onClose':
                case 'showTimer':
                case 'currTime':
                case 'timeFrom':
                case 'destroy':
                case 'initClose':
                    params[k] = config[k];
                    break;
                case 'closeable':
                    if (false === config.closeable) {
                        obj.find('div.pop_header a.close').remove();
                    }
                    break;
                case 'init':
                    init = config[k];
                    break;
            }
        }

        obj.addClass('pop_wrap').css({
            position: 'absolute',
            zIndex: '8000'
        }).appendTo(document.body);

        var messager = obj.messager(params);

        if (typeof(init) == 'function') {
            init.call(obj);
        }

        return messager;
    }
};

/**
 * jQuery Messager
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: jquery.messager.js 2739 2013-02-05 09:49:32Z chenyongfa $
 */
(function ($) {
/**
 * 初始提醒窗口
 *
 * @param {Object} options
 */
$.messager = function(options){
    var self = this;

    // 窗口配置参数
    this.opts = $.extend({}, $.messager.defaults, options);

    if (!this.opts.applyTo) {
        return;
    }

    var container = $(this.opts.applyTo).css({zIndex: 8001});
    this.dialog   = {
        container: container,
        header: container.find('.pop_header'),
        body: container.find('.pop_body'),
        footer: container.find('.pop_footer')
    };

    // 显示窗口、关闭窗口前的方法
    this.fn = {
        show: this.opts.onShow,
        close: this.opts.onClose
    };

    // 创建窗口
    this.dialog.container.hide().appendTo(document.body);

    // 点击关闭窗口
    if (this.opts.initClose) {
        this.dialog.container.find('.' + this.opts.closeCls).click(function(e){
            e.preventDefault();
            self.close();
        });
    }

    // 窗口自动关闭
    if (this.opts.autoCloseTime) {
        setTimeout(function(){self.close();}, this.opts.autoCloseTime);
    }

    // 显示时间
    if (this.opts.showTimer) {
        if (this.opts.currTime !== null) {
            self.setTime();
        } else if (this.opts.timeFrom !== null) {
            self.getServerTime();
        }

        setInterval(
            function(){
                self.showTime(self.dialog.body.find('span.timer'));
            },
            1000
        );
    }

    // 设置窗口大小
    this.dialog.container.css({'height': this.opts.height, 'width': this.opts.width});

    return this;
};

/**
 * 提醒窗口JavaScript方法
 */
$.messager.prototype = {
    /**
     * 显示提醒窗口
     */
    show: function() {
        switch(this.opts.anims.type) {
            case 'slide':
                this.dialog.container.slideDown(this.opts.anims.speed);
                break;
            case 'fade':
                this.dialog.container.fadeIn(this.opts.anims.speed);
                break;
            case 'show':
            default:
                this.dialog.container.show(this.opts.anims.speed);
                break;
        }
        // 窗口定位
        this.position();

        if ($.isFunction(this.fn.show)) {
            this.fn.show.apply(this, [this.dialog]);
        }
    },

    /**
     * 关闭提醒窗口
     */
    close: function(){
        this.dialog.container.hide();

        if (this.opts.destroy) {
            this.destroy();
        }

        this.rePosition();

        if ($.isFunction(this.fn.close)) {
            this.fn.close.apply(this, [this.dialog]);
        }
    },

    /**
     * 重新窗口定位
     */
    rePosition: function() {
        var size = this.getMsgSize() - 1;

        if (size < 0) {
            return;
        }

        var self = this;
        $('div.messager:visible').each(function(){
            if (size >= 0) {
                var bottom = self.opts.height * size;
                $(this).css({
                    bottom: bottom
                });
            }

            size--;
        });
    },

    /**
     * 窗口定位
     * 右下角
     */
    position: function() {
        var bottom = 0,
            size = this.getMsgSize() - 1;

        if (size < 0) {
            size = 0;
        }
        bottom = this.opts.height * size;

        this.dialog.container.css({bottom: bottom, right: 0});
    },

    /**
     * 设置时间
     */
    setTime: function() {
        var self = this;

        if (self.opts.currTime === null) {
            return ;
        }

        setInterval(
            function(){
                self.opts.currTime += 1;
            },
            1000
        );
    },

    /**
     * 从服务器获取时间
     */
    getServerTime: function() {
        var o = this;

        if (o.opts.timeFrom === null) {
            return ;
        }

        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: o.opts.timeFrom,
            success: function(ret) {
                if (ret.success && ret.data && ret.data.currtime) {
                    o.opts.currTime = ret.data.currtime;
                    o.setTime();
                }
            },
            error: function() {}
        });
    },

    /**
     * 获取时间
     */
    getTime: function() {
        if (this.opts.currTime === null) {
            return new Date();
        }

        return new Date(this.opts.currTime * 1000);
    },

    /**
     * 显示时间
     *
     * @param {Object} obj
     */
    showTime: function(obj) {
        var now   = this.getTime(),
            hour  = now.getHours(),
            min   = now.getMinutes(),
            sec   = now.getSeconds(),
            month = parseInt(now.getMonth()) + 1;

        if(hour < 10){hour = "0" + hour;}
        if(min < 10){min = "0" + min;}
        if(sec < 10){sec = "0" + sec;}

        obj.html(now.getFullYear() + '-' + month + '-' + now.getDate() + ' ' + hour + ':' + min + ':' + sec);
    },

    /**
     * 销毁提醒窗口
     */
    destroy: function() {
        this.dialog.container.remove();
        return null;
    },

    /**
     * 获取提醒窗口个数
     */
    getMsgSize: function() {
        return $('div.messager:visible').size();
    }
};

$.fn.messager = function (options) {
    // if nothing is selected, return nothing; can't chain anyway
    if (!this.length) {
        options && options.debug && window.console && console.warn( "nothing selected, can't open messager, returning nothing" );
        return;
    }

    // check if a messager for this element was already created
    var messager = $.data(this[0], 'messager');
    if (messager) {
        return messager;
    }

    messager = new $.messager($.extend(options, {applyTo: this[0], id: this[0].id}));
    $.data(this[0], 'messager', messager);

    return messager;
};

/**
 * 默认配置项
 */
$.messager.defaults = {
    id: 'messager',      // 窗口ID
    width: 380,          // 窗口长度
    height: 176,         // 窗口高度
    form: false,         // 是否带有form表单
    title: 'message',    // 窗口标题
    anims: {type: 'fade', speed: 600},// 显示效果、速度
    autoCloseTime: null, // 显示时间长度，时间到就自动关闭窗口
    onShow: null,        // 显示窗口前的操作
    onClose: null,       // 关闭窗口前的操作
    init: null,          // 初始窗口的所有操作
    closeCls: 'close',   // 关闭窗口样式
    showTimer: false,    // 默认不显示时间
    destroy: true,       // 是否销毁窗口
    currTime: null,      // 服务器返回的时间
    timeFrom: null,      // 时间来源地址 
    initClose: true      // 初始化关闭可用
};
})(jQuery);