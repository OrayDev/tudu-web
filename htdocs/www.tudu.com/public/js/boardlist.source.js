/**
 * 板块首页

 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: boardlist.source.js 2733 2013-01-31 01:41:03Z cutecube $
 */
var BoardList = {
    lang: {
        expand: '[展开分区]',
        collapse: '[收起分区]'
    },

    /**
     * 设置语言
     * @param {Object} lang
     */
    setLang: function(lang) {
        var _o = this;
        for (var i in lang) {
            _o.lang[i] = lang[i];
        }
    },

    /**
     * 初始化
     */
    init: function() {
        var bid = TOP.Cookie.get('BOARDHIDE');
        if (bid !== null)  {
            bid = bid.split(',');
            for (var i = 0; i < bid.length; i++) {
                this.toggle('b-' + bid[i]);
            }
        }

        this.remarkSort();
        var _o = this;

        // 我的板块下板块的排序 ----- 影响左导航的快捷板块的排序
        $('#z-myboard').each(function() {
            $(this).find('.board_body a[name="up"]').bind('click', function(){
                var boardId = $(this).parents('div.category_2').attr('id').replace('my-', '');
                _o.sortAttentionBoard(this, boardId, 'up');
            });
            $(this).find('.board_body a[name="down"]').bind('click', function(){
                var boardId = $(this).parents('div.category_2').attr('id').replace('my-', '');
                _o.sortAttentionBoard(this, boardId, 'down');
            });
        });

        // 分区的排序
        $('.board a.arr_up')
        .bind('mouseover', function(e){
            TOP.stopEventBuddle(e);
            $(this).addClass('arr_up_hover');
        })
        .bind('mouseout', function(e){
            TOP.stopEventBuddle(e);
            $(this).removeClass('arr_up_hover');
        }).bind('click', function(e){
            if (!$(this).hasClass('arr_up_disabled')) {
                var boardId = $(this).parents('div.board').attr('id').replace('z-', '');
                var objBoardId = $(this).parents('div.board').prev().attr('id').replace('z-', '');
                _o.sortBoard(this, boardId, objBoardId, 'up', 'zone');
            }
        });

        $('.board a.arr_down')
        .bind('mouseover', function(e){
            TOP.stopEventBuddle(e);
            $(this).addClass('arr_down_hover');
        })
        .bind('mouseout', function(e){
            TOP.stopEventBuddle(e);
            $(this).removeClass('arr_down_hover');
        }).bind('click', function(e){
            if (!$(this).hasClass('arr_down_disabled')) {
                var boardId = $(this).parents('div.board').attr('id').replace('z-', '');
                var objBoardId = $(this).parents('div.board').next().attr('id').replace('z-', '');
                _o.sortBoard(this, boardId, objBoardId, 'down', 'zone');
            }
        });

        // 板块的排序
        $('.board:not(.attention)').each(function() {
            $(this).find('.board_body a[name="up"]').bind('click', function(){
                if (!$(this).hasClass('gray')) {
                    var boardId = $(this).parents('div.category_2').attr('id');
                    var objBoardId = $(this).parents('div.category_2').prev().attr('id');
                    _o.sortBoard(this, boardId, objBoardId, 'up', 'board');
                }
            });
            $(this).find('.board_body a[name="down"]').bind('click', function(){
                if (!$(this).hasClass('gray')) {
                    var boardId = $(this).parents('div.category_2').attr('id');
                    var objBoardId = $(this).parents('div.category_2').next().attr('id');
                    _o.sortBoard(this, boardId, objBoardId, 'down', 'board');
                }
            });
        });
    },

    /**
     * 分区板块排序
     */
    sortBoard: function(obj, boardId, objBoardId, sort, type){
        if ($(obj).hasClass('arr_down_disabled') || $(obj).hasClass('arr_up_disabled') || $(obj).hasClass('gray')) {
            return ;
        }

        var _o = this;

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '/board/sort',
            data: {bid: boardId.replace('_', '^'), objid: objBoardId.replace('_', '^'), sort: sort},
            success: function(ret) {
                if (ret.success) {
                    if (type == 'zone') {
                        var item = $('#z-' + boardId);
                    } else {
                        var item = $('#' + boardId);
                    }

                    if (sort == 'up') {
                        item.insertBefore(item.prev());
                    } else if (sort == 'down') {
                        item.insertAfter(item.next());
                    }

                    _o.remarkSort();
                }
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESS_ERROR);
            }
        });
    },

    /**
     * 排序我的板块
     * 影响左导航的快捷板块的排序
     */
    sortAttentionBoard: function(obj, boardId, type){
        if ($(obj).hasClass('gray')) {
            return ;
        }

        var _o = this;

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '/board/sort.attention',
            data: {bid: boardId.replace('_', '^'), type: type},
            success: function(ret) {
                if (ret.success) {
                    var _$ = TOP.getJQ();
                       item = $('#my-' + boardId),
                       list = _$('#b_' + boardId);

                    if (type == 'up') {
                        item.insertBefore(item.prev());
                        list.insertBefore(list.prev());
                    } else if (type == 'down') {
                        item.insertAfter(item.next());
                        list.insertAfter(list.next());
                    }

                    _o.remarkSort();
                }
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESS_ERROR);
            }
        });
    },

    /**
     * 调整排序箭头的样式
     */
    remarkSort: function() {
        $('.board a.gray').removeClass('gray');
        $('.board a.arr_up').removeClass('arr_up_disabled');
        $('.board a.arr_down').removeClass('arr_down_disabled');

        $('.board').each(function() {
            $(this).find('.board_body a[name="up"]:first').addClass('gray');
            $(this).find('.board_body a[name="down"]:last').addClass('gray');
        });

        $('.board a.arr_up:first').addClass('arr_up_disabled');
        $('.board a.arr_down:last').addClass('arr_down_disabled');
    },

    /**
     * 显示隐藏的板块（关闭的板块）
     */
    showAll: function() {
        $('div.board div.fold a').removeClass('icon_unfold');
        $('div.board_body').show();
        $('div.board_body .category_2').show();

        this.checkExpand();
        this.setCookie();
    },

    /**
     * 收起、展开分区下板块
     * @param {Object} bid
     */
    toggle: function(bid) {
        $('#' + bid + '-icon').toggleClass("icon_unfold");
        $('#' + bid).toggle();

        this.checkExpand();
        this.setCookie();
    },

    /**
     * 收起、展开全部分区下板块
     */
    toggleAll: function() {
        if (!$('div.board_body:visible').size()) {
            $('div.board div.fold a').removeClass('icon_unfold');
            $('div.board_body').show();
        } else {
            $('div.board div.fold a').addClass('icon_unfold');
            $('div.board_body').hide();
        }

        this.checkExpand();
        this.setCookie();
    },

    /**
     * 展开、收起分区
     * 文字处理
     */
    checkExpand: function() {
        var _o = this;
        if (!$('div.board_body:visible').size()) {
            $('#toggle-all').text(_o.lang.expand);
        } else {
            $('#toggle-all').text(_o.lang.collapse);
        }
    },

    /**
     * 设置Cookie
     */
    setCookie: function() {
        var bid = [];
        $('.board').find('a.icon_fold').each(function(){
            if ($(this).hasClass('icon_unfold')) {
                bid.push($(this).attr('_bid'));
            }
        });

        if (bid.length <= 0) {
            bid = null;
        } else {
            bid = bid.join(',');
        }

        TOP.Cookie.set('BOARDHIDE', bid);
    }
};
