/* dropdown menu */
$.Dropdown=function(a){$.extend(this,$.Dropdown.defaults,a||{});this.init()};$.Dropdown.defaults={id:null,target:null,menuCls:"option-menu",menuCss:"",wrapCls:"option-menu-wrap",menuBody:null,separaCls:"menu-step",itemCls:"menu-item",itemHoverCls:"menu-over",maxHeight:null,maxWidth:null,offsetLeft:0,offsetTop:0,width:null,anime:"slide",animeSpeed:"fast",items:[],onShow:function(){},onSelect:function(){},onHide:function(){},resetpos:null,separate:null,order:null};(function(a){a.Dropdown.prototype={_menu:null,_target:null,_enabled:true,isShow:false,onShow:function(){},onSelect:function(){},onHide:function(){},init:function(){var b=this;if(this.target){this._target=a(this.target);this._target.bind("click",function(c){if(b._enabled){b.toggle(c)}else{b.hide()}c.cancelBubble=true;if(c.stopPropagation){c.stopPropagation()}})}a(document.body).bind("click",function(){b.hide()})},updateSeparate:function(b){this.separate=b},_initMenu:function(){this._wrap=a("<div>").addClass(this.wrapCls);this._menu=a("<div>");if(this.id){this._wrap.attr("id",this.id)}this._wrap.css({position:"absolute",display:"none"});this._menu.addClass(this.menuCls);if(this.maxWidth){this._wrap.css("width",this.maxWidth);if(!this.menuCss){this.menuCss={width:(parseInt(this.maxWidth.replace("px",""))-2)+"px"}}else{this.menuCss.width=(parseInt(this.maxWidth.replace("px",""))-2)+"px"}}this._menu.css(this.menuCss);if(this.menuBody){this._menu.append(this.menuBody)}if(this.items&&this.items.length){this._initMenuItem()}this._wrap.append(this._menu);a(document.body).append(this._wrap)},_initMenuItem:function(){for(var b=0,d=this.items.length;b<d;b++){this.addItem(this.items[b])}},show:function(l){if(!this._menu){this._initMenu()}this._wrap.css({left:"-9999px"}).show();var k=l.srcElement?a(l.srcElement):a(l.target),f=this._target?this._target:k,i=f.offset(),h=f.outerHeight(),n=this._wrap.height(),b=a(window).height(),m=document.body.scrollTop?document.body.scrollTop:document.documentElement.scrollTop;this._wrap.hide();if(i.top+h+n<b||i.top-m<n||this.alwaysBottom){this._wrap.css({top:i.top+h+this.offsetTop+"px",left:i.left+this.offsetLeft})}else{this._wrap.css({top:i.top-n+this.offsetTop+"px",left:i.left+this.offsetLeft})}if(this.maxHeight&&n>this.maxHeight){this._wrap.find(".option-menu").css({height:this.maxHeight})}if(this.anime){var j=this,g=null;switch(this.anime){case"fade":g="fadeIn";break;case"slide":default:g="slideDown";break}this._wrap[g].call(this._wrap,this.animeSpeed,function(){j.isShow=true})}else{this._wrap.show()}this.isShow=true;this.onShow();if(this.resetpos){var i=this._target.offset();this._wrap.css({left:i.left+this._target.outerWidth(true)+"px",top:i.top+"px"});var d=this._wrap.offset(),n=this._wrap.find(".option-menu").height(),c=a(document.body).height();if(d.top+n>c){this._wrap.css({top:d.top-(d.top+n-c)-20+"px"})}}return this},hide:function(){if(!this._menu){return this}if(!this.isShow){return}if(this.anime){var c=this,b=null;switch(this.anime){case"fade":b="fadeOut";break;case"slide":default:b="slideUp";break}this._wrap[b].call(this._wrap,this.animeSpeed,function(){c.isShow=false})}else{this._wrap.hide()}this.isShow=false;this.onHide();return this},addItem:function(d){var e=this;if(d=="-"){this._menu.append('<div class="'+this.separaCls+'"></div>');return this}var c=a("<div>").addClass(this.itemCls);for(var b in d){switch(b){case"body":c.html(d[b]);break;case"event":for(var b in d.event){if(typeof(d.event[b])=="function"){c.bind(b,function(){d.event[b].call(c)})}}break;case"data":c.data=d.data;break;default:c.attr(b,d[b])}}c.bind("click",function(){e.onSelect.call(c)}).bind("mouseover",function(){a(this).addClass(e.itemHoverCls)}).bind("mouseout",function(){a(this).removeClass(e.itemHoverCls)});this._menu.append(c)},clear:function(){if(this._wrap){this._wrap.find(".option-menu").empty()}},setBody:function(b){if(this._wrap){this._wrap.find(".option-menu").html(b)}},toggle:function(b){if(this.isShow){this.hide()}else{this.show(b)}},disabled:function(){this._enabled=false},enabled:function(){this._enabled=true},destroy:function(){this._target.unbind("click");if(this._menu){this._menu.remove()}}};a.fn.dropdown=function(c){c.srcElement=this;c.target=this;var b=new a.Dropdown(c);return b}})(jQuery);

/**
 * 分区、版块管理
 */
var Board = Board || {};

Board = {
    // 编辑分区 窗口
    modifyWin: null,

    // 合并分区 窗口
    mergeWin: null,

    // 分区负责人 窗口
    userWin: null,

    // 分区Id
    boardid: null,

    //分区名称
    boardname: null,

    // 用户信息
    user: null,

    // 用户信息
    users: {},

    boards: [],
    
    type: 'zone',

    // 部门列表
    list: null,
    
    // 展开状态记录
    expandStatus: {},

    tpl: {
        modify: '<input type="hidden" name="boardid" value="" /><input type="hidden" name="type" value="zone" /><p style="margin:30px 0;" align="center"><span name="type-name">分区名称</span>：<input class="text" name="boardname" size="40" type="text" value="" maxlength="50" /></p>',
        merge: '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td colspan="2" align="center"><div style="margin:10px 0 10px 5px;" class="gray">源分区的版块将全部并入目标分区，同时删除源分区</div></td></tr><tr><td align="right" width="70">源分区：</td><td width="280"><div style="margin:10px 0 5px 5px;" id="bid-ct"></div></td></tr><tr><td align="right">目标分区：</td><td><div style="margin:20px 0 10px 5px;" id="target-ct"></div></td></tr></table>',
        zone: '<div class="board-item board-parent"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list board-parent"><tr><td class="td-first" align="left"><div class="td-space"><span class="tree-ec-icon"></span><span class="board-name"></span></div></td><td width="210" class="td-last" align="left"><div class="td-space board-leader"></div></td><td width="250" class="td-last" align="left"><div class="td-space"><a name="moderator" href="javascript:void(0);">[分区负责人]</a> <a name="rename" href="javascript:void(0);">[重命名]</a> <a name="delete" href="javascript:void(0);">[删除]</a></div></td><td width="50" align="left"><div class="td-space"><a href="javascript:void(0);" name="up">↑</a> <a href="javascript:void(0);" name="down">↓</a></div></td></tr></table><div class="board-children"></div></div>',
        board: '<div class="board-item"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list"><tr><td class="td-first" align="left"><div class="td-space"><span class="board-name"></span></div></td><td width="210" class="td-last" align="left"><div class="td-space board-leader"></div></td><td width="250" class="td-last" align="left"><div class="td-space"><a name="moderator" href="javascript:void(0);">[版块负责人]</a> <a name="groups" href="javascript:void(0);">[版块参与人]</a> <a name="rename" href="javascript:void(0);">[重命名]</a> <a name="delete" href="javascript:void(0);">[删除]</a></div></td><td width="50" align="left"><div class="td-space"><a href="javascript:void(0);" name="up">↑</a> <a href="javascript:void(0);" name="down">↓</a></div></td></tr></table></div>',
        boardModify: '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td align="right" width="70">所属分区：</td><td><div style="margin:10px 0 5px 0;" id="bid-ct"></div></td></tr><tr id="create-zone" style="display:none;"><td align="right" width="70">分区名称：</td><td><div style="margin:10px 0 5px 0;"><input class="text" disabled="disabled" name="zonename" type="text" value="" maxlength="50" style="width:270px;" /></div></td></tr><tr><td align="right" width="70">版块名称：</td><td><div style="margin:10px 0 5px 0;"><input class="text" name="boardname" type="text" value="" maxlength="50" style="width:270px;" /></div></td></tr></table>'
    },

    /**
     * 初始化分区管理页面
     */
    init: function() {
        var me = this;
        $('input[name="merge"]').click(function() {
            this.mergeWin = Board.boardMergeWin();
        });
        
        this.initCreateMenu();

        this.list = $('#board-list');
        this.list.empty();

        var loader = _TOP.SingleLoader.getLoader('user', {
            url: BASE_PATH + '/user/user/struct.load',
            method: 'GET'
        });

        loader.load(function(ret){
            var i, l = ret.data.users.length;
            for (i = 0; i < l; i++) {
                me.users[ret.data.users[i].userid] = ret.data.users[i].truename;
            }
            me.initBoardList();
        });
    },

    /**
     * 新建分区下拉菜单
     */
    initCreateMenu: function() {
        var markItems = [{
            body: '新建分区',
            event: {
                click: function(){
                    Board.zoneModifyWin();
                }
            }
        },
        {
            body: '新建版块',
            event: {
                click: function(){
                    Board.boardModifyWin();
                }
            }
        }];

        $('div.btn-drop').each(function(){
            new $.Dropdown({
                target: this,
                items: markItems,
                maxHeight: 300
            });
        });
    },

    // 编辑版块窗口
    boardEditWin: null,

    /**
     * 新建版块
     */
    boardModifyWin: function() {
        var me = this;

        if (null === me.boardEditWin) {
            me.boardEditWin = Admin.window({
                width: 400,
                id: 'boardmodify',
                title: '编辑版块',
                body: me.tpl.boardModify,
                formid: 'boardform',
                footer: '<input name="submit" type="submit" class="btn" value="确定" /><input name="close" type="button" class="btn" value="关闭" />',
                action: BASE_PATH + '/board/board/create.board',
                draggable: true,
                onShow: function() {
                },
                onClose: function() {
                    me.boardEditWin.destroy();
                    me.boardEditWin = null;
                },
                init: function() {
                    var form = this.find('form');

                    this.find('input[name="close"]').click(function() {
                        me.boardEditWin.close();
                    });

                    var sbs = this.find('select[name="boardid"]'),
                        opt = [{text: '--请选择--', value: ''}];
                    for (var i = 0, c = me.boards.length; i < c; i++) {
                        if (me.boards[i].type == 'zone') {
                            opt[opt.length] = {
                                text: me.boards[i].boardname,
                                value: me.boards[i].boardid.replace('_', '^')
                            };
                        }
                    }
                    opt[opt.length] = {text: '新建分区', value: 'add-zone'};

                    var sbSelect = new UI.SingleSelect({
                        id: 'src-board',
                        name: 'boardid',
                        cls: 'select',
                        maxHeight: 150,
                        width: 120,
                        options: opt,
                        menuCls: 'option',
                        css: {width:'270px'},
                        scope: _TOP.document.body
                    });
                    sbSelect.appendTo(Frame.queryParent('#bid-ct'));
                    sbSelect.select('');

                    sbSelect.bind('change', function(){
                        me.zoneChange(sbSelect);
                    });

                    form.submit(function(){return false;});
                    form.submit(function(){
                        Board.submitBoard(form, sbSelect);
                    });
                }
            });
        }

        me.boardEditWin.show();
    },

    /**
     * 所属分区改变
     */
    zoneChange: function(select) {
        var id = select.getValue();

        if (id == 'add-zone') {
            Frame.queryParent('#create-zone').show();
            Frame.queryParent('input[name="zonename"]').attr('disabled', false).val('');
        } else {
            Frame.queryParent('#create-zone').hide();
            Frame.queryParent('input[name="zonename"]').attr('disabled', true).val('');
        }
    },
    
    submitBoard: function(form, select) {
        var me = this,
            zoneId = select.getValue(),
            boardname = form.find('input[name="boardname"]').val(),
            data = form.serializeArray();

        if (!zoneId) {
            Message.show('请选择所属分区');
            return false;
        }

        if (zoneId == 'add-zone') {
            if (!form.find('input[name="zonename"]').val()) {
                Message.show('请输入分区名称');
                form.find('input[name="zonename"]').focus();
                return false;
            }
        }

        if (!boardname) {
            Message.show('请输入版块名称');
            form.find('input[name="boardname"]').focus();
            return false;
        }

        form.find('input, button').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);
                if(ret.success) {
                    form.find('input, button').attr('disabled', false);
                    me.boardEditWin.close();

                    Cookie.set('FOCUS-BOARD', ret.data.boardid);
                    //加载新数据到模板中
                    Board.updatelist(ret.data.boards);
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                form.find('input, button').attr('disabled', false);
                return false;
            }
        });
    },

    /**
     * 处理分区列表
     */
    initBoardList: function() {
        var i, c = this.boards.length;
        this.list.empty();
        if (c <= 0) {
            this.list.append('<div id="dept-null" style="padding:30px 0;text-align:center">没有分区及版块</div>');
        } else {
            for (i = 0; i < c; i++) {
                this.appendBoard(this.boards[i]);
            }
        }

        $('#board-list .board-item').each(function(){
            var o = $(this);
            if (o.find('>.board-children .board-item').size()) {
                var icon = o.find('>table.table-list .tree-ec-icon'),
                    boardid = o.attr('id').replace('board-', '');
                icon.addClass('tree-elbow-plus').bind('click', function(){
                    var ico = $(this);
                    if (ico.hasClass('tree-elbow-plus')) {
                        Board.expand(boardid);
                    } else {
                        Board.collspan(boardid);
                    }
                });

                if (!Board.expandStatus[boardid]) {
                    Board.collspan(boardid);
                } else {
                    Board.expand(boardid);
                }
            }
        });

        this.remarkSort();
        this.remarkChildrenSort();
        $('#board-list table').rowHover();

        var fdid = Cookie.get('FOCUS-BOARD');
        if (fdid) {
            Board.focusBoard(fdid);
            Cookie.set('FOCUS-BOARD', null);
        }
    },

    /**
     * 折叠
     */
    collspan: function(boardid) {
        var me = this,
            ele = $('#board-' + boardid);
        ele.find('#board-children-' + boardid).hide();
        ele.find('>table.table-list span.tree-ec-icon').removeClass('tree-elbow-minus').addClass('tree-elbow-plus');

        delete me.expandStatus[boardid];
    },

    /**
     * 展开
     */
    expand: function(boardid) {
        var me = this,
            ele = $('#board-' + boardid);
        var parentid = ele.attr('_parent');
        ele.find('#board-children-' + boardid).show();
        ele.find('>table.table-list span.tree-ec-icon').removeClass('tree-elbow-plus').addClass('tree-elbow-minus');

        me.expandStatus[boardid] = '1';
    },

    /**
     * 分区排序 箭头颜色
     */
    remarkSort: function() {
        $('#board-list .board-item table.board-parent a.lightgray').removeClass('lightgray');
        $('#board-list .board-item table.board-parent:first a[name="up"]').addClass('lightgray');
        $('#board-list .board-item table.board-parent:last a[name="down"]').addClass('lightgray');
    },

    /**
     * 版块排序 箭头颜色
     */
    remarkChildrenSort: function() {
        $('#board-list div.board-parent').each(function(){
            var bid = $(this).attr('id').replace('board-', ''),
                target = $('#board-children-' + bid);
            target.find('.board-item table.table-list a.lightgray').removeClass('lightgray');
            target.find('.board-item table.table-list:first a[name="up"]').addClass('lightgray');
            target.find('.board-item table.table-list:last a[name="down"]').addClass('lightgray');
        });
    },

    /**
     * 添加元素
     * @param {Object} item
     */
    appendBoard: function(item) {
        var me = this,
            parent = item.parentid ? $('#board-' + item.parentid + ' div.board-children:eq(0)') : this.list,
            ele = item.type == 'zone' ? $(this.tpl.zone) : $(this.tpl.board);

        if (!parent.size()) {
            return ;
        }

        ele
        .attr({id: 'board-' + item.boardid, '_parentid': item.parentid, '_type': item.type})
        .find('span.board-name').html(item.boardname);
        ele.find('div.board-children').attr({id: 'board-children-' + item.boardid});
        ele.find('div.board-leader').html(item.moderatorsName+'<input type="hidden" value="'+item.moderators+'" id="moderator-'+item.boardid+'" />');
        if (item.type == 'board') {
            ele.find('div.board-leader').append('<input type="hidden" value="' + item.groups + '" id="groups-' + item.boardid + '" />');
        }
        ele.find('a[name="delete"]').bind('click', function(){
            var bid = ele.attr('id').replace('board-', ''),
                type = ele.attr('_type');
            me.del(bid, type);
        });
        ele.find('a[name="groups"]').bind('click', function(){
            var bid = ele.attr('id').replace('board-', ''),
                type = ele.attr('_type');
            me.updateGroups(bid.replace('_', '^'));
        });
        ele.find('a[name="moderator"]').bind('click', function(){
            var bid = ele.attr('id').replace('board-', ''),
                type = ele.attr('_type');
            me.updateUser(bid.replace('_', '^'), type);
        });
        ele.find('a[name="rename"]').bind('click', function(){
            var bid = ele.attr('id').replace('board-', ''),
                boardName = $('#' + ele.attr('id')).find('.board-name:eq(0)').text(),
                type = ele.attr('_type');
            me.update(bid.replace('_', '^'), boardName, type);
        });
        ele.find('a[name="up"]').bind('click', function(){
            if ($(this).hasClass('lightgray')) {
                return ;
            }

            var bid = ele.attr('id').replace('board-', ''),
                type = ele.attr('_type'),
                parentId = ele.attr('_parentid');
            me.sortBoard(bid.replace('_', '^'), 'up', type, parentId.replace('_', '^'));
        });
        ele.find('a[name="down"]').bind('click', function(){
            if ($(this).hasClass('lightgray')) {
                return ;
            }

            var bid = ele.attr('id').replace('board-', ''),
                type = ele.attr('_type'),
                parentId = ele.attr('_parentid');
            me.sortBoard(bid.replace('_', '^'), 'down', type, parentId.replace('_', '^'));
        });

        parent.append(ele);
    },

    /**
     * 创建分区 模板窗口操作
     */
    zoneModifyWin: function() {
        var me = this;

        if (null === me.modifyWin) {
            me.modifyWin = Admin.window({
                width: 400,
                id: 'board-modifywin',
                title: '编辑分区',
                body: me.tpl.modify,
                formid: 'board-modifyform',
                footer: '<input name="submit" type="submit" class="btn" value="确定" /><input name="close" type="button" class="btn" value="关闭" />',
                action: BASE_PATH + '/board/board/create',
                draggable: true,
                onShow: function() {
                    var form = Frame.queryParent('#board-modifyform');
                    if (me.type == 'zone') {
                        form.find('.window-header .window-header-text strong').text('编辑分区');
                        form.find('span[name="type-name"]').text('分区名称');
                    } else {
                        form.find('.window-header .window-header-text strong').text('编辑版块');
                        form.find('span[name="type-name"]').text('版块名称');
                    }
                    if(!me.boardid) {
                        form.find('input[name="boardid"]').val('');
                        form.find('input[name="boardname"]').val('');
                        form.find('input[name="type"]').val(me.type);
                        form.attr('action', BASE_PATH + '/board/board/create');
                    } else {
                        form.find('input[name="boardid"]').val(me.boardid);
                        form.find('input[name="boardname"]').val(me.boardname);
                        form.find('input[name="type"]').val(me.type);
                        form.attr('action', BASE_PATH + '/board/board/update');
                        Board.focusBoard(me.boardid);
                    }
                },
                onClose: function() {
                    me.boardid = null;
                    me.boardname = null;
                    me.type = 'zone';
                    me.modifyWin.destroy();
                    me.modifyWin = null;
                },
                init: function() {
                    var form = this.find('form');

                    this.find('input[name="close"]').click(function() {
                        me.modifyWin.close();
                    });
        
                    form.submit(function(){return false;});
                    form.submit(function(){
                        Board.modify(form, me.type);
                    });
                }
            });
        }

        me.modifyWin.show();
    },
    
    /**
     * 编辑分区
     */
    modify: function(form, type) {
        var me = this,
            name = form.find('input[name="boardname"]').val(),
            data = form.serializeArray();

        if (!name) {
            if (type == 'zone') {
                Message.show('请输入分区名称');
            } else {
                Message.show('请输入版块名称');
            }
            form.find('input[name="boardname"]').focus();
            return false;
        }

        form.find('input, button').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);
                if(ret.success) {
                    form.find('input, button').attr('disabled', false);
                    me.modifyWin.close();
                    
                    Cookie.set('FOCUS-BOARD', ret.data.boardid);
                    //加载新数据到模板中
                    Board.updatelist(ret.data.boards);
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                form.find('input, button').attr('disabled', false);
                return false;
            }
        });
    },

    /**
     * 更新分区 模板窗口操作
     */
    update: function(bid, bname, type) {
        this.boardid = bid;
        this.boardname = bname;
        this.type = type;
        
        if(!this.boardid) {
            Message.show('参数错误[boardid]');
        }

        Board.zoneModifyWin();
    },
    
    /**
     * 删除分区
     */
    del: function(bid, type) {
        if(!bid) {
            Message.show('参数错误[boardid]');
        }

        if (type == 'zone') {
            if (!confirm('确定删除此分区吗？')) {
                return false;
            }
        } else if (type == 'board') {
            if (!confirm('确定删除此版块吗？')) {
                return false;
            }
        }

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {boardid: bid.replace('_', '^'), type: type},
            url: BASE_PATH + '/board/board/delete',
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);
                if(ret.success) {
                    if (ret.data) {
                        Board.updatelist(ret.data);
                    }
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                return false;
            }
        });
    },

    /**
     * 分区负责人
     */
    updateUser: function(bid, type) {
        this.boardid = bid;
        this.type    = type;
        Board.boardUserWin();
    },

    groupsWin: null,
    _boardGroups: {},
    
    /**
     * 版块参与人窗口
     *
     * @param {Object} bid
     */
    updateGroups: function(bid) {
        this.boardid = bid;
        var me = this;

        if (null === me.groupsWin) {
            me.groupsWin = Admin.window({
                width: 445,
                id: 'board-userwin',
                title: '版块参与人',
                body: '<input type="hidden" name="boardid" />',
                formid: 'board-groupsform',
                footer: '<input name="submit" type="submit" class="btn" value="确定"><input name="close" type="button" class="btn" value="关闭" />',
                action: BASE_PATH + '/board/board/update.groups',
                draggable: true,
                onShow: function() {
                    Board.focusBoard(me.boardid);
                    Frame.queryParent('#board-groupsform input[name="boardid"]').val(me.boardid);
                },
                onClose: function() {
                    me.boardid = null;
                    me._boardGroups = {};
                    Frame.queryParent('#board-groupsform input[name="boardid"]').val('');
                    me.selector.reset();
                    
                    me.groupsWin.destroy();
                    me.groupsWin = null;
                },
                init: function() {
                    var form = this.find('form'),
                        winbody = this.find('.window-body');

                    this.find('input[name="close"]').click(function() {
                        me.groupsWin.close();
                    });

                    me.selector = new _TOP.UserSelector(null, null, null, true);
                    me.selector.renderTo(winbody);

                    form.submit(function(){return false;});
                    form.submit(function(){
                        var groups = me.selector.getGroupId(),
                            users  = me.selector.getUserId(),
                            boardGroup = new Array();
                        boardGroup = boardGroup.concat(groups, users);
                        Board.saveGroups(form, boardGroup);
                    });
                }
            });
        }
        Board.getBoardGroups(me.boardid);
        me.selector.select(me._boardGroups);
        me.groupsWin.show();
    },

    /**
     * 获取版块参与人
     *
     * @param {Object} bid
     */
    getBoardGroups: function(bid) {
        bid = bid.replace('^', '_');
        var me = this,
            userId = $('#groups-' + bid).val(),
            boardGroups = new Array();
        boardGroups = userId.split(",");
        me._boardGroups = boardGroups;
    },

    /**
     * 保存版块参与人
     *
     * @param {Object} form
     * @param {Object} groups
     */
    saveGroups: function(form, groups) {
        var boardid = form.find('input[name="boardid"]').val(),
            data = form.serializeArray(),
            me = this;

        if (!boardid) {
            Message.show('参数错误[boardid]');
            return false;
        }

        /*if (!groups.length) {
            Message.show('你尚未选择版块参与人？');
            return false;
        }*/

        form.find('input, button').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);
                if (ret.success) {
                    form.find('input, button').attr('disabled', false);
                    me.groupsWin.close();
                    Cookie.set('FOCUS-BOARD', boardid);
                    if (ret.data) {
                        Board.updatelist(ret.data);
                    }
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                form.find('input, button').attr('disabled', false);
                return false;
            }
        });
    },
    
    /**
     * 合并分区操作
     */
    merge: function(form) {
        var me = this,
            boardid = form.find('input[name="boardid"]').val(),
            targetid = form.find('input[name="targetid"]').val(),
            data = form.serializeArray();

        if (!boardid) {
            Message.show('请选择需要合并的源分区');
            form.find('input[name="boardid"]').focus();
            return false;
        }

        if (!targetid) {
            Message.show('请选择需要合并的目标分区');
            form.find('input[name="targetid"]').focus();
            return false;
        }

        if (boardid == targetid) {
            Message.show('源分区与目标分区相同');
            form.find('input[name="boardid"]').focus();
            return false;
        }

        if (boardid.charAt(0) == '^') {
            Message.show('不能把默认分区合并到其他分区');
            form.find('input[name="boardid"]').focus();
            return false;
        }

        form.find('input, button, select').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);
                if(ret.success) {
                    form.find('input, button, select').attr('disabled', false);
                    me.mergeWin.close();
                    Cookie.set('FOCUS-BOARD', targetid);

                    //加载新数据到模板中
                    if (ret.data) {
                        Board.updatelist(ret.data);
                    }
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                form.find('input, button, select').attr('disabled', false);
                return false;
            }
        });
    },

    /**
     * 更新类别
     */
    updatelist: function(data) {
        if (data) {
            Board.boards = [];
            for (var i=0; i<data.length; i++) {
                if (data[i].parentid == null) {
                    data[i].parentid = '';
                }
                Board.boards.push({boardid: data[i].boardid.replace('^', '_'), boardname: data[i].boardname, moderators: data[i].moderators, moderatorsName: data[i].moderatorsname, parentid: data[i].parentid.replace('^', '_'), ordernum: data[i].ordernum, type: data[i].type, groups: data[i].groups});
            }
            Board.initBoardList();
        }
    },

    // 分区负责人
    _boardUser: {},

    /**
     * 分区负责人操作窗口显示
     */
    boardUserWin: function() {
        var me = this;

        if (null === me.userWin) {
            me.userWin = Admin.window({
                width: 445,
                id: 'board-userwin',
                title: '分区负责人',
                body: '<input type="hidden" name="boardid" />',
                formid: 'board-userform',
                footer: '<input name="submit" type="submit" class="btn" value="确定"><input name="close" type="button" class="btn" value="关闭" />',
                action: BASE_PATH + '/board/board/updateuser',
                draggable: true,
                onShow: function() {
                    Board.focusBoard(me.boardid);
                    Frame.queryParent('#board-userform input[name="boardid"]').val(me.boardid);
                    if (me.type == 'zone') {
                        Frame.queryParent('#board-userform .window-header .window-header-text strong').text('分区负责人');
                    } else if (me.type == 'board') {
                        Frame.queryParent('#board-userform .window-header .window-header-text strong').text('版块负责人');
                    }
                },
                onClose: function() {
                    me.boardid = null;
                    me._boardUser = {};
                    $('input[name="boardid"]').val('');
                    me.selector.reset();
                    
                    me.userWin.destroy();
                    me.userWin = null;
                },
                init: function() {
                    var form = this.find('form'),
                        winbody = this.find('.window-body');

                    this.find('input[name="close"]').click(function() {
                        me.userWin.close();
                    });

                    me.selector = new _TOP.UserSelector();
                    me.selector.renderTo(winbody);

                    form.submit(function(){return false;});
                    form.submit(function(){
                        var moderators = me.selector.getUserId();
                        Board.saveMember(form, moderators);
                    });
                }
            });
        }
        Board.boardUser(me.boardid);
        me.selector.select(me._boardUser);
        me.userWin.show();
    },

    /**
     * 分区管理员列表
     */
    boardUser: function(bid) {
        bid = bid.replace('^', '_');
        var me = this,
            userId = $('#moderator-' + bid).val(),
            boardUsers = new Array();
        boardUsers = userId.split(",");
        me._boardUser = boardUsers;
    },

    /**
     * 保存分区负责人
     */
    saveMember: function(form, moderators) {
        var boardid = form.find('input[name="boardid"]').val(),
            data = form.serializeArray(),
            me = this;

        if (!boardid) {
            Message.show('参数错误[boardid]');
            return false;
        }

        if (!moderators.length) {
            Message.show('你尚未选择分区负责人？');
            return false;
        }

        form.find('input, button').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);
                if (ret.success) {
                    form.find('input, button').attr('disabled', false);
                    me.userWin.close();
                    Cookie.set('FOCUS-BOARD', boardid);
                    if (ret.data) {
                        Board.updatelist(ret.data);
                    }
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                form.find('input, button').attr('disabled', false);
                return false;
            }
        });
    },

    /**
     * 合并操作窗口显示
     */
    boardMergeWin: function() {
        var me = this;

        if (null === me.mergeWin) {
            me.mergeWin = Admin.window({
                width: 400,
                id: 'board-mergewin',
                title: '合并分区',
                body: me.tpl.merge,
                formid: 'board-mergeform',
                footer: '<input name="submit" type="submit" class="btn" value="确定"><input name="close" type="button" class="btn" value="关闭" />',
                action: BASE_PATH + '/board/board/merge',
                draggable: true,
                onShow: function() {
                    
                },
                onClose: function() {
                    me.mergeWin.destroy();
                    me.mergeWin = null;
                },
                init: function() {
                    var sbs = this.find('select[name="boardid"]'),
                        tbs = this.find('select[name="targetid"]'),
                        opt = [{text: '--请选择--', value: ''}];
                    for (var i = 0, c = me.boards.length; i < c; i++) {
                        if (me.boards[i].type == 'zone') {
                            opt[opt.length] = {
                                text: me.boards[i].boardname,
                                value: me.boards[i].boardid.replace('_', '^')
                            };
                        }
                    }

                    var form = this.find('form');

                    this.find('input[name="close"]').click(function() {
                        me.mergeWin.close();
                    });

                    var sbSelect = new UI.SingleSelect({
                        id: 'src-board',
                        name: 'boardid',
                        cls: 'select',
                        maxHeight: 150,
                        width: 120,
                        options: opt,
                        menuCls: 'option',
                        css: {width:'200px'},
                        scope: _TOP.document.body
                    });
                    sbSelect.appendTo(Frame.queryParent('#bid-ct'));
                    sbSelect.select('');

                    var tbSelect = new UI.SingleSelect({
                        id: 'target-board',
                        name: 'targetid',
                        cls: 'select',
                        maxHeight: 150,
                        options: opt,
                        menuCls: 'option',
                        css: {width:'200px'},
                        scope: _TOP.document.body
                    });
                    tbSelect.appendTo(Frame.queryParent('#target-ct'));
                    tbSelect.select('');

                    form.submit(function(){return false;});
                    form.submit(function(){
                        Board.merge(form);
                    });
                }
            });
        }

        me.mergeWin.show();
    },

    /**
     * 排序
     */
    sortBoard: function(boardid, sort, type, parentId) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {
               'boardid': boardid,
               'sort': sort,
               'type': type,
               'parentid': parentId
            },
            url: BASE_PATH + '/board/board/sort.board',
            success: function(ret) {
                if (ret.success) {
                    Cookie.set('FOCUS-BOARD', boardid);
                    if (ret.data) {
                        Board.updatelist(ret.data);
                    }
                }
            },
            error: function(res){
            }
        });
    },

    focusBoard: function(boardid) {
        $('#board-list tr.focus').removeClass('focus');
        $('#board-' + boardid.replace('^', '_')).rowFocus();
    }
};