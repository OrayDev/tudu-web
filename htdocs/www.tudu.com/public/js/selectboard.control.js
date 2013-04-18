/**
 * 板块选择控件

 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: selectboard.control.js 2733 2013-01-31 01:41:03Z cutecube $
 */
var BoardSelector = function(params) {
    this._settings = {};
    this.setParam(params);

    this.init();
}
BoardSelector.defaultSettings = {
    appendTo: null,
    selected: null,
    sort: false
};
BoardSelector.prototype = {
    _panels: null,
    _resultPanel: null,
    _boardsPanel: null,
    _settings: null,
    _boards: null,
    init: function() {
        var _o = this,
            ctLeft = $('<div>').addClass('contact_selector_left'),
            ctRight = $('<div>').addClass('contact_selector_right'),
            leftInner = $('<div>').addClass('selector_inner'),
            rightInner = $('<div>').addClass('selector_inner'),
            leftTop = $('<div>').addClass('selector_left_title'),
            rightTop = $('<div>').addClass('selector_right_title');

        _o._panels = $('<div>').addClass('contact_selector');
        _o._boardsPanel = $('<div>').addClass('contact_select_groups');

        _o._boardsPanel.attr('style', 'height: 99%; border-top: none');
        if (!_o._settings.sort) {
            _o._panels.css({
                'padding': 0
            });
        }

        _o._panels
        .append(ctLeft)
        .append($('<div>').addClass('contact_selector_center'))
        .append(ctRight);

        _o._resultPanel = $('<div>').addClass('contact_selected');
        ctRight
        .append(rightTop)
        .append(rightInner.append(_o._resultPanel));

        ctLeft
        .append(leftTop)
        .append(leftInner.append(_o._boardsPanel));

        if (_o._boards === null) {
            _o.loadBoard();
        }

        if (_o._settings.appendTo != null) {
            _o._panels.appendTo(_o._settings.appendTo);
        }
    },
    /**
     * 设置参数
     */
    setParam: function(key, val) {
        var params = null;
        if (typeof(key) == 'object') {
            params = key;
        } else if (typeof(key) == 'string' && val) {
            params = {};
            params[key] = val;
        }

        if (null == params) {
            return ;
        }

        this._settings = $.extend({}, BoardSelector.defaultSettings, params);
    },

    _selectIndex: 0,

    _orderNum: 0,

    select: function(id) {
        var _o = this;

        var node = _o.boardTree.find(id, true);
        if (node == null) {
            return ;
        }
        node.hide();

        var data = node.getData();

        if (data.boardid && this._resultPanel.find(':hidden[name^="boardid"][value="'+data.boardid+'"]').size()) {
            return ;
        }

        _o._selectIndex ++;
        _o._orderNum ++;

        var a = $('<div>').attr('href', 'javascript:void(0);').addClass('contact_item');
        a.append('<input type="hidden" name="member[]" value="'+this._selectIndex+'" />');
        for (var k in data) {
            if (k == 'id') {
                continue ;
            }

            if (k == 'name') {
                if (_o._settings.sort) {
                    a.append('<span class="contact_sort"><a class="icon_arrow arr_up" href="javascript:void(0);"></a><a class="icon_arrow arr_down" href="javascript:void(0);"></a></span>');
                }
                a.append('<span>' + data[k] + '</span>');
            }

            if (k == 'boardid' && data[k]) {
                a.append('<input type="hidden" name="'+k+'-'+this._selectIndex+'" value="'+data[k]+'" />');
                if (_o._settings.sort) {
                    a.append('<input type="hidden" name="ordernum-'+this._selectIndex+'" value="'+_o._orderNum+'" />');
                }
            }
        }

        a.mousemove(function(){
            a.addClass("contact_item_hover");
        }).mouseout(function(){
            a.removeClass("contact_item_hover");
        });

        if (_o._settings.sort) {
            a.find('a.arr_up')
            .bind('mouseover', function(){
                $(this).addClass('arr_up_hover');
            })
            .bind('mouseout', function(){
                $(this).removeClass('arr_up_hover');
            })
            .bind('click', function(e){
                if ($(this).hasClass('arr_up_disabled')) {
                    TOP.stopEventBuddle(e);
                    return ;
                }
                var b = $(this).parent().parent();
                b.insertBefore(b.prevAll('div.contact_item:first'));
                _o.tidyOrderClass();
                _o.updateOrderNum();
                TOP.stopEventBuddle(e);
            });

            a.find('a.arr_down')
            .bind('mouseover', function(){
                $(this).addClass('arr_down_hover');
            })
            .bind('mouseout', function(){
                $(this).removeClass('arr_down_hover');
            })
            .bind('click', function(e){
                if ($(this).hasClass('arr_down_disabled')) {
                    TOP.stopEventBuddle(e);
                    return ;
                }
                var b = $(this).parent().parent();
                b.insertAfter(b.nextAll('div.contact_item:first'));
                _o.tidyOrderClass();
                _o.updateOrderNum();
                TOP.stopEventBuddle(e);
            });
        }

        a.bind('click', function(){
            $(this).remove();
            _o.unselect(data);
            if (_o._settings.sort) {
                _o.tidyOrderClass();
                _o.updateOrderNum();
                _o._orderNum --;
            }
        });

        _o._resultPanel.append(a);
        _o.tidyOrderClass();
    },

    /**
     * 取消选择
     */
    unselect: function(item) {
        var node = this.boardTree.find('b-' + item.boardid.replace('^', '_'), true);
        if (node) {
            node.show();
        }
    },

    /**
     * 更新排序序号
     */
    updateOrderNum: function() {
        var order = 1;
        this._resultPanel.find(':hidden[name^="ordernum"]').each(function(){
            $(this).val(order);
            order ++;
        });
        this._orderNum = order;
    },
    /**
     * 排序箭头样式
     */
    tidyOrderClass: function() {
        this._resultPanel.find('a.arr_up').removeClass('arr_up_disabled');
        this._resultPanel.find('a.arr_down').removeClass('arr_down_disabled');

        this._resultPanel.find('a.arr_up:first').addClass('arr_up_disabled');
        this._resultPanel.find('a.arr_down:last').addClass('arr_down_disabled');
    },

    boardTree: null,

    initSelected: function() {
        if (!this._settings.selected) {
            return ;
        }

        var _o = this,
            se = _o._settings.selected;
        for (var i = 0, c = se.length; i < c; i++) {
            if (se[i].boardid && se[i].boardid.indexOf('^') != -1) {
                se[i].boardid = se[i].boardid.replace('^', '_');
            }
            _o.select('b-' + se[i].boardid);
        }
    },

    initBoardList: function() {
        var _o = this,
            selectbox = $('<div>').addClass('select_box panel_body');

        selectbox.attr('style', 'height: 100%');

        _o.boardTree = new $.tree({
            id: 'board-tree',
            idKey: 'id',
            idPrefix: 'board-',
            cls: 'cast-tree',
            template: '{name}'
        });
        _o.boardTree.appendTo(selectbox);

        var boards = _o._boards;
        if (!boards) {
            return ;
        }

        for (var i in boards) {
            if (boards[i].boardid && boards[i].boardid.indexOf('^') != -1) {
                boards[i].boardid = boards[i].boardid.replace('^', '_');
            }

            if (boards[i].boardname) {
                boardName = boards[i].boardname.replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            var node = new $.treenode({
                    data: {
                    id: 'z-' + boards[i].boardid,
                    name: boardName
                },
                events: {
                    click: function(e){_o.boardTree.find(this.id.replace('board-', ''), true).toggle();TOP.stopEventBuddle(e);}
                }
            });

            _o.boardTree.appendNode(node);
            node.expand();

            if (typeof boards[i].children != 'undefined') {
                var children = boards[i].children, chBoardName;
                for (var i in children) {
                    if (children[i].boardid && children[i].boardid.indexOf('^') != -1) {
                        children[i].boardid = children[i].boardid.replace('^', '_');
                    }
                    if (children[i].parentid && children[i].parentid.indexOf('^') != -1) {
                        children[i].parentid = children[i].parentid.replace('^', '_');
                    }
                    if (children[i].boardname) {
                        chBoardName = children[i].boardname.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    }

                    var node = new $.treenode({
                            data: {
                            id: 'b-' + children[i].boardid,
                            boardid: children[i].boardid,
                            name: chBoardName
                        },
                        isLeaf: true,
                        events: {
                            mouseover: function(){$(this).addClass('tree-node-over');},
                            mouseout: function(){$(this).removeClass('tree-node-over');},
                            click: function(e){
                                _o.select(this.id.replace('board-', ''));
                                TOP.stopEventBuddle(e);
                            }
                        }
                    });

                    var parent = _o.boardTree.find('z-' + children[i].parentid, true);
                    if (parent != null) {
                        parent.appendChild(node);
                    }
                }
            }
        }

        _o._boardsPanel.append(selectbox);
    },

    loadBoard: function() {
        var _o = this;
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: '/board/board.list',
            success: function(ret) {
                if (ret.success) {
                    if (ret.data) {
                        _o._boards = ret.data;
                        _o.initBoardList();
                        _o.initSelected();
                    }
                }
            },
            error: function(res) {
            }
        });
    }
};