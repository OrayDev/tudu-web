/**
 * 新手指引Javascript
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com
 * @version    $Id: guide.source.js 2733 2013-01-31 01:41:03Z cutecube $
 */
var Guide = {
    container: null,
    mask: null,
    bh: null,

    /**
     * 初始化新手指引
     */
    init: function() {
        this.initMask();
        this.initStartDialog();
    },

    /**
     * 新手浏览指引
     */
    initView: function() {
        var resizeTimer = null;

        function onResize() {
            var bh = $('#fix-height');
            var container = $('#pic');
            var x = (document.body.offsetWidth - container.width()) / 2;
            var y = 0;
            if (container.height() < Math.min(bh.height(), document.documentElement.offsetHeight)) {
                y = ((Math.min(bh.height(), document.documentElement.offsetHeight) - container.height()) / 2 + Math.max(document.documentElement.scrollTop, document.body.scrollTop))
            }

            $('#pic').css({top: y, left: x});
            resizeTimer = null;
        }

        window.onresize = function() {
            if (resizeTimer == null) {
                resizeTimer = setTimeout(function(){
                    onResize();
                }, 100);
            }
        }

        $('#pic02 a').click(function(){
            $('#pic02').hide();
            $('#pic03').show();
        });

        $('#pic03 a').click(function(){
            $('#pic03').hide();
            $('#pic04').show();
            setTimeout(function(){
                $('#pic04').hide();
                $('#pic05').show();
                setTimeout(function(){
                    $('#pic05').hide();
                    $('#pic06').show();
                    setTimeout(function(){
                        $('#pic06').hide();
                        $('#pic07').show();
                        setTimeout(function(){
                            $('#pic07').hide();
                            $('#pic08').show();
                        }, 3000);
                    }, 3000);
                }, 3000);
            }, 3000);
        });

        $('#pic08 a').click(function(){
            $('#pic08').hide();
            $('#pic09').show();
            setTimeout(function(){
                $('#pic09').hide();
                $('#pic10').show();
                setTimeout(function(){
                    $('#pic10').hide();
                    $('#pic11').show();
                }, 3000);
            }, 3000);
        });

        $('#pic11 a').click(function(){
            $('#pic11').hide();
            $('#pic02').show();
        });

        $('.close').click(function(){
            window.open('','_self','');
            window.close();
        });

        onResize();
    },

    /**
     * 初始化灰色遮罩
     */
    initMask: function() {
        this.bh = $('#fix-height');
        if (!this.bh.size()) {
            this.bh = $('<div>')
            .attr('id', 'fix-height')
            .css({
                 position: 'absolute',
                 height: '100%',
                 width: 1,
                 left: -100,
                 top:0
            })
            .appendTo(document.body);
        }

        this.mask = $('<div class="mask">').css({
            zIndex: 8000,
            'background-color': '#7e7e7e',
            'display': 'block',
            'position': 'fixed'
        }).appendTo(document.body);
    },

    /**
     * 初始化开始窗口
     */
    initStartDialog: function() {
        var me = this;

        this.container = $('<div class="guide-window">');
        this.container.css({'height': '220px', 'width': '383px'});
        this.container.html('<span class="start"></span>');

        var close = $('<div class="close">');
        close.appendTo(this.container);

        var view = $('<div class="view">');
        view.appendTo(this.container);
        view.html('<a href="' + BASE_PATH + '/guide/" target="_blank"></a>');

        var x = (document.body.offsetWidth - $(this.container).width()) / 2;
        var y = 0;
        if (this.container.height() < Math.min(this.bh.height(), document.documentElement.offsetHeight)) {
            y = ((Math.min(this.bh.height(), document.documentElement.offsetHeight) - this.container.height()) / 2 + Math.max(document.documentElement.scrollTop, document.body.scrollTop))
        }
        this.container.css({top: y, left: x}).appendTo(document.body);

        this.container.find('.close').click(function(e) {
            e.preventDefault();
            me.closeDialog();
        });

        this.container.find('.view').click(function() {
            me.closeDialog();
        });
    },

    /**
     * 关闭指引
     */
    closeDialog: function() {
        this.mask.remove();
        this.bh.remove();
        this.container.remove();
        // 提交更新状态，下次不再出现
        this.submitStatus();
    },

    /**
     * 更新状态
     */
    submitStatus: function() {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {},
            url: BASE_PATH + '/guide/tips',
            success: function(){},
            error: function(){}
        });
    }
};
