/**
 * 增值应用js
 */
var App = App || {};

App = {
    // 应用介绍窗口
    tpl: {
        listNull: [
            '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="app-list">',
            '<tr><td align="center" class="td-null">您还没有安装任何应用</td></tr></table>'
        ].join(''),
        winBody: [
            '<div id="loading-bar" style="padding:10px 0;text-align:center;line-height:16px;"><img src="' + BASE_PATH + '/img/loading.gif" style="vertical-align:middle;" />&nbsp;正在加载</div>',
            '<div class="app-info" style="display:none;"><div class="app-info-header">',
            '<table width="100%" border="0" cellspacing="0" cellpadding="0"><col width="70" /><col /><col width="200" /><tr>',
            '<td align="center" height="70"><div class="td-space"><img name="logo" src=""></div></td>',
            '<td height="70" style="line-height:2em;"><div class="td-space"><p><strong name="appname" class="f14"></strong></p><p name="description" class="gray"></p></div></td>',
            '<td height="70"><div class="td-space"><a class="app-btn-2" name="url" href="" style="display:none;"><span class="icon icon-app-entering"></span>进入设置</a><a href="javascript:void(0)" name="install" class="app-btn-1" style="margin-top:20px;"><span class="icon icon-app-add"></span>安装此应用</a></div></td>',
            '</tr></table></div><div class="app-info-body"><div id="img" class="fl" style="width:530px;">',
            '<div class="app-info-img"><div id="arrow" style="display:none">',
            '<a href="javascript:void(0);" class="arrow prev"><span>上一页</span></a>',
            '<a href="javascript:void(0);" class="arrow next"><span>下一页</span></a>',
            '</div><div class="slides_container"></div></div>',
            '</div><div class="fr" style="width:195px;"><div class="app-info-text" style="padding: 20px 0;">',
            '<div class="first"><p><strong>考勤应用功能包括</strong></p></div>',
            '<div><p><strong>特性简介：</strong></p><p name="content"></p></div>',
            '<!-- div><p><strong>此应用可能调用：</strong></p><ul name="app-transfer"></ul></div -->',
            '<div><p><strong>最近更新：</strong><span name="last-update-time"></span></p><p><strong>版本号：</strong><span name="version"></span></p><p><strong>支持语言：</strong><span name="language"></span></p></div>',
            '</div></div><div class="clear"></div></div></div>'
        ].join(''),
        appItem: [
            '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="app-list">',
            '<col width="80" /><col /><col width="200" /><col width="200" /><tr>',
            '<td align="center"><div class="td-space"><img name="logo" src="" /></div></td>',
            '<td><div class="td-space"><p><strong class="f14" name="appname"></strong></p><p class="gray" name="description"></p></div></td>',
            '<td><div class="td-space">免费</div></td>',
            '<td><div class="td-space" name="app-btn"></div></td>',
            '</tr></table>'
        ].join(''),
        listItem: [
            '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="app-list">',
            '<col width="80" /><col /><col width="150" /><col width="150" /><col width="250" /><tr>',
            '<td align="center"><div class="td-space"><img name="logo" src="" /></div></td>',
            '<td><div class="td-space"><p><strong class="f14" name="appname"></strong></p><p class="gray" name="description"></p></div></td>',
            '<td><div class="td-space">免费</div></td>',
            '<td><div class="td-space" name="status"></div></td>',
            '<td><div class="td-space"><a name="url" href="" class="app-btn-2" target="main"><span class="icon icon-app-entering"></span>进入设置</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a name="del" href="javascript:void(0)">删除</a></div></td>',
            '</tr></table>'
        ].join('')
    },

    // 应用介绍窗口
    introduceWin: null,

    // 提示窗口
    tipsWin: null,

    // 是否正在加载
    loadingList: false,

    // 是否不需要再加载了
    loadingFinish: false,

    slides: null,

    /**
     * 初始化应用列表
     */
    init: function() {
        var _o = this,
            back = null,
            page = 2;

        $('#app-list').find(".app-list").bind('mouseover', function(){
            $(this).find('tr').addClass("over");
        }).bind('mouseout', function(){
            $(this).find('tr').removeClass("over");
        }).bind('click', function(e){
            var appId = $(this).attr('id').replace('app-', '');
            _o.introduceApp(appId);
            Util.stopEventBuddle(e);
        });

        $(".app-list .app-btn-1, .app-list .app-btn-2").bind('click', function(e){
            Util.stopEventBuddle(e);
        });

        $(window).bind('scroll', function(){
            var scrollTop = document.documentElement ? document.documentElement.scrollTop : document.body.scrollTop,
                offset    = $('#btm-bar').offset(),
                btmTop    = offset.top;

            if (scrollTop + $(window).height() > btmTop) {
                _o.loadApps(0, {}, page, back, function(ret){
                    if (ret.data && ret.data.length) {
                        page ++;
                    }
                });
            }
        });
    },

    /**
     * 加载应用列表
     */
    loadApps: function(isinstall, params, page, back, callback) {
        var _o = this;
        if (_o.loadingFinish || _o.loadingList) {
            return ;
        }

        $('#loading-bar').show();

        var query = [];
        for(var k in params) {
            query.push(k + '=' + params[k]);
        }
        if (undefined !== page && null !== page) {
            query.push('page=' + page);
        }

        var url = BASE_PATH + '/appstore/appstore/load.apps?isinstall=' + isinstall + '&' + query.join('&'),
            list = $('#app-list');

        _o.loadingList = true;
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: url,
            success: function(ret) {
                _o.loadingList = false;
                $('#loading-bar').hide();
                if (ret.success) {
                    if (ret.data) {
                        var apps = ret.data;
                        if (apps.length) {
                            // 已使用列表
                            if (isinstall) {
                                for (var i = 0, c = apps.length; i < c; i++) {
                                    var app = apps[i],
                                        item = $(_o.tpl.listItem);
                                    if ($('#app-' + app.appid.replace(/\./g, '_')).size()) {
                                        continue ;
                                    }
                                    item.attr({'id': 'app-' + app.appid.replace(/\./g, '_')});
                                    item.find('[name="logo"]').attr('src', app.logo);
                                    item.find('[name="appname"]').text(app.appname);
                                    item.find('[name="description"]').text(app.description);
                                    if (app.isinstall) {
                                        item.find('[name="status"]').text('启用');
                                    } else {
                                        item.find('[name="status"]').text('停用');
                                    }
                                    item.find('[name="url"]').attr('href', app.url + '/admin/index').attr('target', 'main');
                                    item.find('[name="del"]').bind('click', function(e){
                                        _o.deleteApp(app.appid, app.status, app.url + '/admin/index');
                                        Util.stopEventBuddle(e);
                                    });

                                    item
                                    .bind('mouseover', function(){$(this).find('tr').addClass('over')})
                                    .bind('mouseout', function(){$(this).find('tr').removeClass('over')})
                                    .bind('click', function(){
                                        _o.introduceApp(app.appid);
                                        Util.stopEventBuddle(e);
                                    });

                                    list.append(item);
                                }
                            // 应用列表
                            } else {
                                for (var i = 0, c = apps.length; i < c; i++) {
                                    var app = apps[i],
                                        item = $(_o.tpl.appItem);
                                    if ($('#app-' + app.appid.replace(/\./g, '_')).size()) {
                                        continue ;
                                    }
                                    item.attr({'id': 'app-' + app.appid.replace(/\./g, '_')});
                                    item.find('[name="logo"]').attr('src', app.logo);
                                    item.find('[name="appname"]').text(app.appname);
                                    item.find('[name="description"]').text(app.description);
                                    if (app.isinstall) {
                                        item.find('[name="app-btn"]').append('<a href="'+app.url+'/admin/index" class="app-btn-2" target="main"><span class="icon icon-app-entering"></span>进入设置</a>');
                                    } else {
                                        item.find('[name="app-btn"]').append('<a href="javascript:void(0)" class="app-btn-1"><span class="icon icon-app-add"></span>安装此应用</a>');
                                        item.find('[name="app-btn"] a').bind('click', function(e){
                                            _o.introduceApp(app.appid);
                                            Util.stopEventBuddle(e);
                                        });
                                    }

                                    item
                                    .bind('mouseover', function(){$(this).find('tr').addClass('over')})
                                    .bind('mouseout', function(){$(this).find('tr').removeClass('over')})
                                    .bind('click', function(){
                                        _o.introduceApp(app.appid);
                                        Util.stopEventBuddle(e);
                                    });

                                    list.append(item);
                                }
                            }
                        }
                    } else {
                        _o.loadingFinish = true;
                    }

                    if (typeof(callback) == 'function') {
                        callback.call(_o, ret)
                    }
                }
            },
            error: function(res) {
                _o.loadingList = false;
            }
        });
    },
    
    /**
     * 介绍应用窗口
     */
    introduceApp: function(appId) {
        var _o = this,
            _$ = Frame.queryParent;

        if (null === _o.introduceWin) {
            _o.introduceWin = Admin.window({
                width: 800,
                id: 'introduce-win',
                title: '应用介绍',
                form: false,
                body: _o.tpl.winBody,
                draggable: true,
                onShow: function() {
                    _$('.window-body').attr('style', 'border-bottom:1px solid #99ac71;');
                    if (_o.slides === null) {
                        _o.slides = new _TOP.Slides();
                    }
                    _o.appInfo(appId);
                },
                onClose: function() {
                    _o.slides.destroy();
                    _o.introduceWin.destroy();
                    _o.introduceWin = null;
                },
                init: function() {
                    this.find('input[name="close"]').click(function() {
                        _o.introduceWin.close();
                    });
                }
            });
        }

        _o.introduceWin.show();
    },

    /**
     * 获取介绍应用的信息
     */
    appInfo: function(appId) {
        var _o = this,
            _$ = Frame.queryParent;
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: BASE_PATH + '/appstore/appstore/app.info?appid=' + appId,
            success: function(ret) {
                if (ret.success && ret.data) {
                    var app = ret.data.app;
                    _$('[name="appname"]').text(app.appname);
                    _$('[name="description"]').text(app.description);

                    _$('[name="logo"]').attr('src', app.logo);
                    _$('[name="content"]').html(app.content);

                    // 处理时间日期
                    _$('[name="last-update-time"]').text(app.lastupdatetime);

                    _$('[name="version"]').text(app.version);
                    _$('[name="language"]').text(app.languages);

                    if (app.isinstall) {
                        _$('[name="url"]').attr('href', app.url + '/admin/index').attr('target', 'main').show();
                        _$('[name="install"]').hide();
                        _$('[name="url"]').bind('click', function(){
                            _o.introduceWin.close();
                        })
                    } else {
                        _$('[name="install"]').bind('click', function(){
                            _o.installApp(app.appid);
                        });
                    }

                    /*var permissions = ret.data.permissions;
                    if (permissions !== null) {
                        for (var i = 0; i < permissions.length; i++) {
                            _$('[name="app-transfer"]').append('<li>' + permissions[i].permission + '</li>');
                        }
                    }*/

                    var attach = ret.data.attach,
                        num = 0;
                    if (attach !== null) {
                        for (var i = 0; i < attach.length; i++) {
                            if (attach[i].type == 'photo') {
                                _$('.slides_container').append('<a><img src="'+attach[i].url+'" /></a>');
                                num ++;
                            }
                        }
                        if (num > 0) {
                            _o.slides.play({
                                target: '.app-info-img',
                                play: 3000,
                                pause: 500,
                                slideSpeed: 350,
                                hoverPause: false,
                                pagination: true,
                                paginationClass: 'pagination',
                                currentClass: 'current',
                                isArrow: true
                            });
                        }
                    }

                    _$('#loading-bar').hide();
                    _$('div.app-info').show();
                    _o.introduceWin.center();
                }
            },
            error: function(res) {
            }
        });
    },

    /**
     * 初始化已使用列表
     */
    initList: function() {
        var _o = this,
            back = null,
            page = 2;

        $('#app-list').find(".app-list").bind('mouseover', function(){
            $(this).find('tr').addClass("over");
        }).bind('mouseout', function(){
            $(this).find('tr').removeClass("over");
        }).bind('click', function(e){
            var appId = $(this).attr('id').replace('app-', '');
            _o.introduceApp(appId);
            Util.stopEventBuddle(e);
        });

        $('.app-list [name="del"]').bind('click', function(e){
            Util.stopEventBuddle(e);
        });
        $('a[name="url"]').bind('click', function(e){
            Util.stopEventBuddle(e);
        });

        $(window).bind('scroll', function(){
            var scrollTop = document.documentElement ? document.documentElement.scrollTop : document.body.scrollTop,
                offset    = $('#btm-bar').offset(),
                btmTop    = offset.top;

            if (scrollTop + $(window).height() > btmTop) {
                _o.loadApps(1, {}, page, back, function(ret){
                    if (ret.data && ret.data.length) {
                        page ++;
                    }
                });
            }
        });
    },

    /**
     * 安装应用
     */
    installApp: function(appId) {
        var _o = this;

        if (!confirm('确认要安装该应用吗？')) {
            return false;
        }

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {appid: appId},
            url: BASE_PATH + '/appstore/appstore/install',
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success ? 'success' : null);
                // 更改按钮并跳转
                if (ret.success && ret.data) {
                    appId = appId.replace(/\./g, '_');
                    $('#app-' + appId).find('.app-btn-1').remove();
                    $('#app-' + appId).find('[name="app-btn"]').append('<a href="'+ret.data.url+'/admin/index" class="app-btn-2"><span class="icon icon-app-entering"></span>进入设置</a>');
                }
				if (_o.introduceWin !== null) {
					_o.introduceWin.close();
				}

                Frame.queryParent('#mainframe')[0].contentWindow.location = '/app/' + appId + '/admin/index';
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                return false;
            }
        });
    },

    /**
     * 删除应用
     */
    deleteApp: function(appId, status, url) {
        var _o = this;
        status = parseInt(status);
        if (status == 1) {
            if (null === _o.tipsWin) {
                _o.tipsWin = Admin.window({
                    width: 350,
                    id: 'tips-win',
                    title: '提示',
                    form: false,
                    body: '<p style="color:#000000;margin:10px 0;">该应用为开启状态，不能直接删除，若确认要删除该应用，请先把状态设置为停用。</p>',
                    footer: '<input class="btn" name="setting" type="button" value="进入设置" /><input class="btn" name="cancel" type="button" value="取消" />',
                    draggable: true,
                    onShow: function() {},
                    onClose: function() {
                        _o.tipsWin.destroy();
                        _o.tipsWin = null;
                    },
                    init: function() {
                        this.find('input[name="setting"]').click(function() {
                            location = url;
                            _o.tipsWin.close();
                        });
                        this.find('input[name="close"], input[name="cancel"]').click(function() {
                            _o.tipsWin.close();
                        });
                    }
                });
            }
            _o.tipsWin.show();
            
        } else {
            _o.confirmDel(appId);
        }
    },

    /**
     * 确认删除
     */
    confirmDel: function(appId) {
        var _o = this;
        if (null === _o.tipsWin) {
            _o.tipsWin = Admin.window({
                width: 350,
                id: 'tips-win',
                title: '提示',
                form: false,
                body: '<p style="color:#000000;margin:10px 0;">确认要删除该应用吗？</p>',
                footer: '<input class="btn" name="confirm" type="button" value="确定" /><input class="btn" name="cancel" type="button" value="取消" />',
                draggable: true,
                onShow: function() {},
                onClose: function() {
                    _o.tipsWin.destroy();
                    _o.tipsWin = null;
                },
                init: function() {
                    this.find('input[name="confirm"]').click(function() {
                        _o.sumbitDel(appId);
                        _o.tipsWin.close();
                    });
                    this.find('input[name="close"], input[name="cancel"]').click(function() {
                        _o.tipsWin.close();
                    });
                }
            });
        }
        _o.tipsWin.show();
    },

    /**
     * 提交删除
     */
    sumbitDel: function(appId) {
        var _o = this;
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {appid: appId},
            url: BASE_PATH + '/appstore/appstore/delete',
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success ? 'success' : null);
                if (ret.success) {
                    appId = appId.replace(/\./g, '_');
                    $('#app-' + appId).remove();
                    if ($('#app-list').find('.app-list').size() <= 0) {
                        $('#app-list').append(_o.tpl.listNull);
                    }
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                return false;
            }
        });
    }
};