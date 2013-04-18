/**
 * 用户管理
 */
var User = {

    tpl: {
        group: '<input type="hidden" name="userid" value="" /><p style="margin:30px 0;" align="center">群组名称：<input class="text" name="groupname" type="text" size="40" maxlength="20" /></p>',
        groupItem: '<span style="margin-right:5px"></span>',
        avatar: [
            '<div class="avatarform">',
            '<form id="uploadform" action="' + BASE_PATH + '/user/user/upload" method="post" enctype="multipart/form-data">',
            '<p class="gray">(请点击“浏览”，在您电脑中选择您要上传的照片。)</p>',
            '<p>选择照片：<input class="input_file" name="avatar-file" id="avatar-file" type="file" /><input type="submit" class="btn" id="uploadbtn" name="confirm" value="上传"></p>',
            '</form>',
            '<div class="line_green"></div>',
            '<div class="edit_wrap" id="avatar-edit" style="display:none">',
            '<form id="avatarform" action="' + BASE_PATH + '/user/user/updateavatar" method="post">',
            '<input type="hidden" name="x" /><input type="hidden" name="y" />',
            '<input type="hidden" name="width" /><input type="hidden" name="height" />',
            '<input type="hidden" name="hash" value="" />',
            '<div class="edit_left"><p>编辑头像</p><div class="edit_box_big"><img src="" id="avatar-src" style="display:none" /></div></div>',
            '<div class="edit_right"><p>缩略图</p><div class="edit_box_small"><img src="" id="avatar-preview" width="80" height="80" style="display:none" /></div></div>',
            '<div class="clear"></div>',
            '</form>',
            '</div></div>'
        ].join(''),
        listItem: [
            '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">',
            '<tr>',
            '<td class="td-first" width="30" align="center"><input name="userid[]" type="checkbox" value="" /></td>',
            '<td width="120"><div class="td-space"><span class="icon"></span>&nbsp;<span name="truename"></span></div></td>',
            '<td width="270"><div class="td-space" name="userid"></div></td>',
            '<td width="95"><div class="td-space" name="status"></div></td>',
            '<td width="95"><div class="td-space" name="deptname"></div></td>',
            '<td width="95"><div class="td-space" name="createtime"></div></td>',
            '<td class="td-last"><div class="td-space group-ct">&nbsp;</div></td>',
            '</tr>',
            '</table>'
        ].join('')
    },

    groupWin: null,

    avatarWin: null,

    groups: null,

    users: {},

    depts: null,

    sortLabel: {
        DESC: '↓',
        ASC: '↑'
    },

    sortList: null,
    sortType: null,

    focusUsers: [],

    isListInited: false,

    counts: {total: 0, disabled: 0, temp: 0, normal: 0},

    // 正在加载中
    loadingList: false,

    // 所有页已加载完成
    finishLoaded: false,

    disableUsers: {},

    disableDepts: {},

    // 准备加载的页面
    listPage: 1,

    /**
     * 初始化用户管理页面
     */
    init: function() {
        var o = this,
            url = location.hash,
            rs = false,
            query = {};

        if (url.indexOf('#') != -1) {
            key = url.split('#');
            con = key[1].split('&');
            for (var i=0;i<con.length;i++) {
                condition = con[i].split('=');
                if (condition[1].length) {
                    $('#'+condition[0]).val(condition[1]);
                    rs = true;
                }
            }
            if (rs) {
                $('#list-title').text('搜索结果');
                $('#search-back').show();
                _search();
            }
        }
        $(':checkbox[name="checkall"]').click(function(){
            Admin.checkBoxAll('userid[]', this.checked, document.body);

            var selectCount = $(':checkbox[name="userid[]"]:checked').size();
        });
        $(':checkbox[name="userid[]"]').click(function(){
            var selectCount = $(':checkbox[name="userid[]"]:checked').size();
        });

        $('#keywords').keyhint();

        var back = BASE_PATH + '/user/user/' + location.hash;

        // 排序
        $('table.table-header a[name^="sort-"]').bind('click', function(){
            var label = $(this).find('.sort-label'),
                type = 'DESC',
                key  = this.name.replace('sort-', '');

            if (label.text() !== User.sortLabel.DESC) {
                $('table.table-header .sort-label').text('');
                label.text(User.sortLabel.DESC);
            } else {
                type = 'ASC',
                $('table.table-header .sort-label').text('');
                label.text(User.sortLabel.ASC);
            }

            query.sort = key;
            query.sorttype = type;
            o.listPage = 1;

            $('#user-list').empty();
            User.loadUsers(query, o.listPage, back, function(ret){
                if (ret.data.records && ret.data.records.length) {
                    o.listPage ++;
                }
            });
        });

        // 解锁
        $('input[name="unlock"]').click(function(){
            User.unlock(null, _reloadList);
        });

        // 删除用户
        $('input[name="delete"]').click(function(){
            User.deleteUser(null, _reloadList);
        });

        // 新增用户
        $('input[name="add"]').click(function(){
            location.assign(BASE_PATH + '/user/user/add');
        });

        // 批量修改用户数据 - 数据POST操作 
        $('input[name="edit"]').click(function(){
            User.edit();
        });

        var genderSelect = new UI.SingleSelect({
            select: '#gender',
            id: 'gender-select',
            cls: 'select',
            menuCls: 'option'
        });
        genderSelect.appendTo($('#gender').parent());

        var statusSelect = new UI.SingleSelect({
            select: '#status',
            id: 'status-select',
            cls: 'select',
            menuCls: 'option',
            css: {marginLeft:'8px'}
        });
        statusSelect.appendTo($('#status').parent());

        var groupSelect = new UI.SingleSelect({
            select: '#groups',
            id: 'group-select',
            cls: 'select',
            menuCls: 'option'
        });
        groupSelect.appendTo($('#groups').parent());

        var roleSelect = new UI.SingleSelect({
            select: '#role',
            id: 'role-select',
            cls: 'select',
            menuCls: 'option',
            css: {marginLeft:'8px'}
        });
        roleSelect.appendTo($('#role').parent());

        var deptSelect = new UI.SingleSelect({
            select: '#dept',
            id: 'dept-select',
            cls: 'select',
            menuCls: 'option',
            css: {marginLeft:'8px'}
        });
        deptSelect.appendTo($('#dept').parent());

        var fGrp = $('#float-toolbar select[name="addgroup"]');
        var floatGrp = new UI.SingleSelect({
            select: fGrp,
            cls: 'select',
            css: {width: '120px'},
            menuCls: 'option'
        });
        floatGrp.appendTo(fGrp.parent());

        var aGrp = $('#toolbar select[name="addgroup"]');
        var addGrp = new UI.SingleSelect({
            select: aGrp,
            cls: 'select',
            menuCls: 'option'
        });
        addGrp.appendTo(aGrp.parent());

        addGrp.bind('change', function(){
            var value = this.getValue();
            if(value){
                if (value == '^new') {
                    User.addToNewGruop();
                } else {
                    var groupId=value;
                    User.addToGroup(groupId);
                }
            }

            this.select('');
        });

        floatGrp.bind('change', function(){
            var value = this.getValue();
            if(value){
                if (value == '^new') {
                    User.addToNewGruop();
                } else {
                    var groupId=value;
                    User.addToGroup(groupId);
                }
            }

            this.select('');
        });

        $('input.text').bind('focus blur mouseover mouseout', function(e) {
            if (e.type == 'focus' || e.type == 'mouseover') {
                $(this).addClass('text-hover');
            } else {
                if (e.type == 'blur' || document.activeElement != this) {
                    $(this).removeClass('text-hover');
                }
            }
        });

        // 搜索
        $('#starttime').datepick({
            showOtherMonths: true,
            selectOtherMonths: true,
            showAnim: 'slideDown',
            showSpeed: 'fast',
            width:'183px',
            firstDay: 0,
            onSelect: function(dates){
                $('#endtime').datepick('option', {minDate: dates});
            }
        });

        $('#endtime').datepick({
            minDate: new Date(),
            showOtherMonths: true,
            selectOtherMonths: true,
            showAnim: 'slideDown',
            showSpeed: 'fast',
            firstDay: 0,
            onSelect: function(dates){$('#starttime').datepick('option', {maxDate: dates});}
        });

        $('#keywords').bind('keyup', function(e){
            var keyCode = e.keyCode ? e.keyCode : e.which;
            if (keyCode == 13) {
                $('#list-title').text('搜索结果');
                $('#search-back').show();
                _search();
            }
        });

        $('#search').bind('click', function(){
            $('#list-title').text('搜索结果');
            $('#search-back').show();
            _search();
        });

        $('#search-back').bind('click', function(){
            $('#list-title').text('帐号');
            $(this).hide();
            $('#keywords').val('').blur();
            $('#starttime, #endtime, #gender, #dept, #role, #status, #groups').val('');

            if (statusSelect) {
                statusSelect.select('');
            }
            if (genderSelect) {
                genderSelect.select('');
            }
            if (deptSelect) {
                deptSelect.select('');
            }
            if (roleSelect) {
                roleSelect.select('');
            }

            _TOP.hash(BASE_PATH + '/user/user/');

            _reloadList();
        });

        var fuid = Cookie.get('FOCUS-USER');
        if (fuid) {
            User.focusUsers = fuid.split(',');
            Cookie.set('FOCUS-USER', null);
        }

        $(window).bind('scroll', function(){
            var scrollTop = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop,
                offset    = $('#btm-bar').offset(),
                btmTop    = offset.top;

            if (scrollTop + $(window).height() > btmTop - 80) {
                $('#loading-bar').show();
                User.loadUsers(query, o.listPage, back, function(ret){
                    if (ret.data.records && ret.data.records.length) {
                        o.listPage ++;
                    }
                });
            }
        });

        _reloadList();

        $('#user-title a[_status]').bind('click', function(){
            var status = $(this).attr('_status')
            $('#status').val(status);
            if (statusSelect) {
                statusSelect.select(status);
            }

            $('#list-title').text('搜索结果');
            $('#search-back').show();
            _search();
        });

        function _reloadList() {
            query = {};
            $('#user-list').empty();
            $('#loading-bar').show();
            o.listPage = 1;
            User.finishLoaded = false;
            User.loadUsers({}, o.listPage, back, function(ret){
                if (ret.data.records && ret.data.records.length) {
                    o.listPage ++;
                }
            }, true);
        }

        function _search(){
            $('#keywords').focus();

            query['keyword']   = $('#keywords').val().toLowerCase().split(/;|；/);
            query['starttime'] = $('#starttime').val();
            query['endtime']   = $('#endtime').val();
            query['gender']    = $('#gender').val();
            query['deptid']    = $('#dept').val();
            query['roleid']    = $('#role').val();
            query['status']    = $('#status').val();
            query['groupid']   = $('#groups').val();
            query['search']    = 1;

            $('#keywords').blur();

            var hash = 'keywords='+query.keyword+'&starttime='+query.starttime+'&endtime='+query.endtime;
            hash += '&gender='+query.gender+'&deptid='+query.deptid+'&roleid='+query.roleid;
            hash += '&status='+query.status+'&groupid='+query.groupid;

            location.hash = hash;

            _TOP.hash(BASE_PATH + '/user/user/#' + hash);

            $('#user-list').empty();
            User.loadUsers(query, 1, BASE_PATH + '/user/user/#' + hash)
        }
    },

    /**
     * 更新账号数目
     *
     * @param {Object} count
     */
    updateListNums: function(count) {
        $('#user-total').text(count.total);
        if (count.disabled) {
            $('#user-span-disabled').show().find('#user-disabled').text(count.disabled);
        } else {
            $('#user-span-disabled').hide().find('#user-disabled').text(0);
        }
        if (count.temp) {
            $('#user-span-temp').show().find('#user-temp').text(count.temp);
        } else {
            $('#user-span-temp').hide().find('#user-temp').text(0);
        }
        if (count.locked) {
            $('#user-span-locked').show().find('#user-locked').text(count.locked);
        } else {
            $('#user-span-locked').hide().find('#user-locked').text(0);
        }
    },

    /**
     * 加载用户
     *
     * @param {Object} params
     * @param {Int}    page
     * @param {String} back
     * @param {Object} callback
     */
    loadUsers: function(params, page, back, callback, isReload) {
        $('#null-list').hide();
        if (typeof isReload == 'undefined') {
            isReload = false;
        }
        if (params.search || (User.sortList != params.sort || User.sortType != params.sorttype)) {
            User.finishLoaded = false;
        }
        if (User.loadingList || User.finishLoaded) {
            $('#loading-bar').hide();
            return ;
        }

        var tpl  = this.tpl.listItem,
            list = $('#user-list');

        var query = [];
        for(var k in params) {
            query.push(k + '=' + params[k]);
        }
        if (undefined !== page && null !== page) {
            query.push('p=' + page);
        }
        var url = BASE_PATH + '/user/user/list?' + query.join('&');

        User.loadingList = true;

        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: url,
            success: function(ret) {
                User.loadingList = false;
                User.sortList = params.sort;
                User.sortType = params.sorttype;
                $('#loading-bar').hide();
                if (ret && ret.success && ret.data) {
                    var total = ret.data.total ? ret.data.total : 0,
                        users = ret.data.records ? ret.data.records : [];

                    var datetime = Date.parse(new Date()) / 1000;

                    if (users.length) {
                        for (var i = 0, c = users.length; i < c; i++) {
                            var user = users[i],
                                item = $(tpl);

                            if ($('#user-' + user.userid).size()) {
                                continue ;
                            }
 
                            item.attr({
                                'id': 'user-' + user.userid, 
                                '_userid': user.userid,
                                '_status': user.status
                            });
 
                            item.find('input[name="userid[]"]').val(user.userid).bind('click', function(e){Util.stopEventBuddle(e);});
                            item.find('span[name="truename"]').text(user.truename);
                            item.find('div[name="userid"]').text(user.username);
                            item.find('div[name="status"]').text(User.formatStatus(user.status));
                            item.find('div[name="deptname"]').text(user.deptname);
                            item.find('div[name="createtime"]').text(User.formatDate(user.createtime));
                            User.appendGroup(item, user.groups);

                            if (Util.inArray(user.userid, User.focusUsers)) {
                                item.find('tr').addClass('focus');
                            }

                            if (user.unlocktime && user.unlocktime > datetime) {
                                item.find('span.icon').addClass('icon-user-lock');
                                item.find('div[name="status"]').append('<span class="gray lock-label">[锁定]</span>');
                            } else if (user.status == 0) {
                                item.find('span.icon').addClass('icon-user-disable');
                            } else if (user.status == 2) {
                                item.find('span.icon').addClass('icon-user-temp');
                            }

                            item.bind('mouseover', function(){$(this).find('tr').addClass('over')})
                                .bind('mouseout', function(){$(this).find('tr').removeClass('over')})
                                .bind('click', function(){
                                    location = BASE_PATH + '/user/user/edit?userid=' + $(this).attr('_userid') + '&back='+encodeURIComponent(back);
                                });

                            list.append(item);
                        }
                    } else {
                        if (!params.search) {
                            User.finishLoaded = true;
                        } else {
                            if ($('#user-list').find('table').size() <= 0) {
                                $('#null-list').show();
                            }
                        }
                    }

                    if (params.search) {
                        var count = {total: 0, disabled: 0, normal: 0, temp: 0};
                        $('#user-list .table-list[id]:visible').each(function(){
                            count.total++;
                            var o = $(this);
                            if (o.attr('_status') == 2) {
                                count.temp++;
                            } else if (o.attr('_status') == 0) {
                                count.disabled++;
                            }
                        });
                        User.updateListNums(count);
                    } else {
                        if (ret.data.count) {
                            var c = ret.data.count,
                                isChange = false;

                            for (var k in ret.data.count) {
                                if (User.counts[k] != ret.data.count[k]) {
                                    isChange = true;
                                }
                                User.counts[k] = ret.data.count[k];
                            }
                            User.updateListNums(User.counts);

                            if (isChange && User.isListInited && !isReload) {
                                Message.show('用户数据有变动，请点击“刷新”更新列表');
                            }
                        }
                    }

                    if (typeof(callback) == 'function') {
                        callback.call(User, ret);
                    }

                    User.isListInited = true;
                }
            },
            error: function(res) {
                User.loadingList = false;
            }
        });
    },

    // 列表排序
    sortList: function(key, type) {
        var list = [];
        $('#user-list table.table-list').each(function(){
            list.push({id: this.id, col: $(this).attr('_' + key)});
        });

        var l = list.length, temp;

        for (var i = 0 ; i < l; i++) {
            for (var j = 0; j < l - i - 1; j++) {
                if ((type == 'DESC' && list[j].col <= list[j + 1].col)
                    || (type == 'ASC' && list[j].col > list[j + 1].col))
                {
                    temp = {id: list[j].id, col: list[j].col};
                    list[j] = list[j+1];
                    list[j+1] = temp;
                }
            }
        }

        for (var idx = 0; idx < l; idx++) {
            $('#'+list[idx].id).appendTo('#user-list');
        }
    },

    // 显示群组
    appendGroup: function(obj, grps) {
        if (!Util.isArray(grps)) {
            grps = [grps];
        }

        var container = obj.find('.group-ct');

        for (var i = 0, c = grps.length; i < c; i++) {
            var groupid = grps[i];

            if (groupid.length == 0) {
                return;
            }

            var gid = groupid.replace('^', '_');

            if (typeof this.groups[groupid] !== 'object') {
                return;
            }

            if (obj.find('#' + obj.attr('id') + '-group-' + gid).size()) {
                continue ;
            }

            var ele = $(this.tpl.groupItem).text(this.groups[groupid].name);

            ele
            .attr({'id': obj.attr('id') + '-group-' + gid});

            container.append(ele);
        }
    },

    /**
     * 批量修改用户数据 - 数据POST操作 
     */
    edit: function(userid) {
        if (!userid) {
            userid = [];
            $(':checkbox[name="userid[]"]:checked').each(function(){
                userid.push(this.value);
            });
        } else {
            userid = [userid];
        }

        if (!userid.length) {
            return Message.show('没有选择任何要操作的用户');
        }

        var url;

        if (userid.length == 1) {
            url = BASE_PATH + '/user/user/edit?userid=' + userid.join('') + '&back='+encodeURIComponent(BASE_PATH + '/user/user/' + location.hash);
        } else {
            url = BASE_PATH + '/user/user/batch?userid=' + userid.join(',') + '&back='+encodeURIComponent(BASE_PATH + '/user/user/' + location.hash);
        }

        location = url;
    },

    /**
     * 删除用户
     */
    deleteUser: function(userid, callback) {
        if (!userid) {
            userid = [];
            $(':checkbox[name="userid[]"]:checked').each(function(){
                userid.push(this.value);
            });
        }

        if (!userid.length) {
            return Message.show('没有选择任何要操作的用户');
        }

        if (!confirm('确定删除选定的用户吗？')) {
            return false;
        }

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {userid: userid.join(',')},
            url: BASE_PATH + '/user/user/delete',
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);
                if (ret.success) {
                    //for (var i = 0, c = userid.length; i < c; i++) {
                        //$('#u-' + userid[i]).remove();
                    //}
                    //$(':checkbox[name="userid[]"], #checkall').attr('checked', false);

                    User.finishLoaded = false;
                    if (undefined !== callback && typeof(callback) == 'function') {
                        callback.call(User);
                    }
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
            }
        });
    },

    /**
     * 解锁
     */
    unlock: function(userid, callback) {
        if (!userid) {
            userid = [];
            $(':checkbox[name="userid[]"]:checked').each(function(){
                userid.push(this.value);
            });
        }

        if (!userid.length) {
            return Message.show('没有选择任何要操作的用户');
        }
        $(':checkbox[name="userid[]"], #checkall').attr('checked', false);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {userid: userid.join(',')},
            url: BASE_PATH + '/user/user/unlock',
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);
                for (var i = 0, c = userid.length; i < c; i++) {
                    $('#u-' + userid[i]).find('.lock-label').remove();
                    $('#u-' + userid[i]).find('.icon-user-lock')
                    .removeClass('icon-user-lock')
                    .addClass('icon-user');
                    $('#u-' + userid[i]).removeAttr('_locked');
                }

                if (undefined !== callback && typeof (callback) == 'function') {
                    callback.call(User);
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
            }
        });
    },

    /**
     * 添加群组成员
     */
    addToGroup: function(groupId) {
        var key = [];
        $(':checkbox[name="userid[]"]:checked').each(function(){
            key.push(this.value);
        });

        if (!key.length) {
            return Message.show('没有选中的用户');;
        }

        $(':checkbox[name="userid[]"], #checkall').attr('checked', false);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {groupid: groupId, key: key},
            url: BASE_PATH + '/user/group/add.member',
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);
                if (ret.success) {
                    location.reload();
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
            }
        });
    },

    /**
     * 添加到新建群组
     */
    addToNewGruop: function() {
        var me = this, userid = [];
        $(':checkbox[name="userid[]"]:checked').each(function(){
            userid.push(this.value);
        });

        var me = this;
        $(':checkbox[name="userid[]"], #checkall').attr('checked', false);
        if (null === me.groupWin) {
            me.groupWin = Admin.window({
                id: 'user-groupwin',
                width: 400,
                title: '添加到群组',
                body: me.tpl.group,
                formid: 'user-groupform',
                footer: '<input name="submit" type="submit" value="确定"><input name="close" type="button" value="关闭" />',
                action: BASE_PATH + '/user/group/create',
                draggable: true,
                onClose: function() {
                    me.groupWin.destroy();
                    me.groupWin = null;
                },
                init: function() {
                    var form = this.find('form');

                    this.find('input[name="close"]').click(function() {
                        me.groupWin.close();
                    });

                    form.submit(function(){return false;});
                    form.submit(function(){
                        var name = form.find('input[name="groupname"]').val();
                        if (!name) {
                            Message.show('请输入群组名称');
                        }

                        var data = form.serializeArray();
                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            data: data,
                            url: form.attr('action'),
                            success: function(ret) {
                                Message.show(ret.message, 5000, ret.success);
                                if (ret.success) {
                                    if (ret.data) {
                                        $('<option value="'+ret.data.groupid+'">'+name+'</option>').insertAfter($('select[name="addgroup"] option:eq(0)'));
                                        me.groups[ret.data.groupid] = name;
                                    }
                                    me.groupWin.close();
                                }
                            },
                            error: function(res) {
                                Message.show(Message.PROCESSING_ERROR);
                            }
                        });
                    });
                }
            });
        }

        Frame.queryParent('#user-groupform input[name="groupname"]').val('');
        Frame.queryParent('#user-groupform input[name="userid"]').val(userid.join(','));

        me.groupWin.show();
    },

    /**
     * 初始化创建页面
     */
    initCreate: function() {
        var groupIndex = 0;

        $('input.text').bind('focus blur mouseover mouseout', function(e) {
            if (e.type == 'focus' || e.type == 'mouseover') {
                $(this).addClass('text-hover');
            } else {
                if (e.type == 'blur' || document.activeElement != this) {
                    $(this).removeClass('text-hover');
                }
            }
        });

        Cookie.set('FOCUS-USER', null);
        if (Cookie.get('USER-ADD-DETAIL') == 'OPEN') {
            $('#moreinfo-icon').removeClass('icon-fold').addClass('icon-unfold');
            $('#more-detail').text('收起帐号详细信息');
            $('#user-base').css('margin-bottom', 0);
            $('#info').show();
        } else if (Cookie.get('USER-ADD-DETAIL') == 'CLOSE') {
            $('#moreinfo-icon').removeClass('icon-unfold').addClass('icon-fold');
            $('#more-detail').text('展开帐号详细信息');
            $('#user-base').css('margin-bottom', '40px');
            $('#info').hide();
        }

        $('#moreinfo-icon, #more-detail').bind('click', function(){ 
            var icon = $('#moreinfo-icon');
            if (icon.hasClass('icon-fold')) {
                $('#moreinfo-icon').removeClass('icon-fold').addClass('icon-unfold');
                $('#more-detail').text('收起帐号详细信息');
                $('#user-base').css('margin-bottom', 0);
                $('#info').show();
                Cookie.set('USER-ADD-DETAIL', 'OPEN');
                window.scrollTo(0, $('#more-detail').offset().top);
            } else {
                $('#moreinfo-icon').removeClass('icon-unfold').addClass('icon-fold');
                $('#more-detail').text('展开帐号详细信息');
                $('#user-base').css('margin-bottom', '40px');
                $('#info').hide();
                Cookie.set('USER-ADD-DETAIL', 'CLOSE');
            }
        });

        $('#userid').blur(function(){
            if (this.value) {
                $.ajax({
                    type: 'GET',
                    dataType: 'json',
                    url: BASE_PATH + '/user/user/check?userid=' + this.value,
                    success: function(ret) {
                        if (ret.message) {
                            $('#hint-userid').text(ret.message)
                            .removeClass()
                            .addClass(ret.success ? 'gray' : 'red').show();
                        }
                    },
                    error: function(res){}
                });
            }
        }).focus(function(){$('#hint-userid').hide();});

        $('#department').change(function(){
            if (this.value == '^new') {
                $('#new-dept').show();
            } else {
                $('#new-dept').hide();
            }
        });

        $('#bir-year, #bir-month').change(function(){
            var day = $('#bir-day');
            User.fillDateSelect();

            day.val('1');
        });

        new FixToolbar({
            target: 'div.tool-btm'
        });

        $('#userid').keyhint();
        $('#groupname').keyhint();

        $('#create-group').bind('click', function(){
            var gn = $('#groupname');
            gn.focus();
            var name = gn.val();

            if (!Util.trim(name)) {
                return Message.show('请输入群组名称');
            }
            groupIndex++;
            var obj = $('<p><input type="checkbox" name="newgroup[]" checked="checked" value="'+groupIndex+'" /><input type="hidden" name="groupname-'+groupIndex+'" value="'+name+'" />'+name+'</p>')

            $('#group-list').append(obj);
            gn.val('');
        });

        $('#password, #truename').focus(function(){
            $('#hint-' + this.id).show();
        }).blur(function(){
            $('#hint-' + this.id).hide();
        });

        $(':radio[name="isshow"]').each(function(){
            $(this).click(function(){
                if (this.id == 'yes' && this.checked) {
                    $('#hint-order').show();
                } else {
                    $('#hint-order').hide();
                }
            });
        });

        this.initCastTree(true);

        $('#theform').submit(function(){return false;});
        $('#theform').submit(function(){
            var form = $(this);

            var userid = Util.trim($('#userid').val()),
                password = Util.trim($('#password').val()),
                truename = Util.trim($('#truename').val());

            if (!userid) {
                return Message.show('请填写正确的帐号名');
            }

            if (!password || password.length < 6) {
                return Message.show('请输入6-16位字英文母数字或字符作为登录密码')
            }

            if (!truename) {
                return Message.show('请输入用户真实姓名');
            }

            var data = form.serializeArray();
            form.disable();

            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: data,
                url: form.attr('action'),
                success: function(ret) {
                    Message.show(ret.message, 5000, ret.success);
                    if (ret.success) {
                        Cookie.set('FOCUS-USER', userid);

                        setTimeout(function(){
                            location = BASE_PATH + '/user/user/add';
                        }, 800);
                    } else {
                        form.enable();
                    }
                },
                error: function(res) {
                    Message.show(Message.PROCESSING_ERROR, 5000);
                    form.enable();
                }
            });
        });
    },

    /**
     * 初始化创建页面
     */
    initEdit: function() {
        var groupIndex = 0;

        $('input.text').bind('focus blur mouseover mouseout', function(e) {
            if (e.type == 'focus' || e.type == 'mouseover') {
                $(this).addClass('text-hover');
            } else {
                if (e.type == 'blur' || document.activeElement != this) {
                    $(this).removeClass('text-hover');
                }
            }
        });

        if (Cookie.get('USER-EDIT-DETAIL') == 'OPEN') {
            $('#moreinfo-icon').removeClass('icon-fold').addClass('icon-unfold');
            $('#more-detail').text('收起帐号详细信息');
            $('#user-base').css('margin-bottom', 0);
            $('#info').show();
        } else if (Cookie.get('USER-EDIT-DETAIL') == 'CLOSE') {
            $('#moreinfo-icon').removeClass('icon-unfold').addClass('icon-fold');
            $('#more-detail').text('展开帐号详细信息');
            $('#user-base').css('margin-bottom', '40px');
            $('#info').hide();
        }

        $('#moreinfo-icon, #more-detail').bind('click', function(){ 
            var icon = $('#moreinfo-icon');
            if (icon.hasClass('icon-fold')) {
                $('#moreinfo-icon').removeClass('icon-fold').addClass('icon-unfold');
                $('#more-detail').text('收起帐号详细信息');
                $('#user-base').css('margin-bottom', 0);
                $('#info').show();
                Cookie.get('USER-EDIT-DETAIL', 'OPEN');
                window.scrollTo(0, $('#more-detail').offset().top);
            } else {
                $('#moreinfo-icon').removeClass('icon-unfold').addClass('icon-fold');
                $('#more-detail').text('展开帐号详细信息');
                $('#user-base').css('margin-bottom', '40px');
                $('#info').hide();
                Cookie.get('USER-EDIT-DETAIL', 'CLOSE')
            }
        });

        $('a[name="avatar"]').click(function(){
            User.avatar();
        });

        new FixToolbar({
            target: 'div.tool-btm'
        });

        $('#department').change(function(){
            if (this.value == '^new') {
                $('#new-dept').show();
            } else {
                $('#new-dept').hide();
            }
        });

        $('#unlock').bind('click', function(){
            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: {userid: $('#userid').val()},
                url: BASE_PATH + '/user/user/unlock',
                success: function(ret) {
                    Message.show(ret.message, 5000, ret.success);
                    if(ret.success) {
                        $('#lockinfo').remove();
                    }
                },
                error: function(res) {
                    Message.show(Message.PROCESSING_ERROR);
                }
            });
        });

        $('#pass-poxy').bind('focus', function(){
            $(this).blur().hide();
            $('#password').show().focus();
        });
        $('#password').bind('blur', function(){
            if (!Util.trim(this.value)) {
                $(this).hide();
                $('#pass-poxy').show();
            }
        });

        User.fillDateSelect();
        $('#bir-year, #bir-month').change(function(){
            var day = $('#bir-day');
            User.fillDateSelect();

            day.val('1');
        });

        $('#groupname').keyhint();

        $('#create-group').bind('click', function(){
            var gn = $('#groupname');
            gn.focus();
            var name = gn.val();
            
            if (!Util.trim(name)) {
                return Message.show('请输入群组名称');
            }
            groupIndex++;
            var obj = $('<p><input type="checkbox" name="newgroup[]" checked="checked" value="'+groupIndex+'" /><input type="hidden" name="groupname-'+groupIndex+'" value="'+name+'" />'+name+'</p>')

            $('#group-list').append(obj);
            gn.val('');
        });

        $('#password, #truename').focus(function(){
            $('#hint-' + this.id).show();
        }).blur(function(){
            $('#hint-' + this.id).hide();
        });

        $(':radio[name="isshow"]').each(function(){
            $(this).click(function(){
                if (this.id == 'yes' && this.checked) {
                    $('#hint-order').show();
                } else {
                    $('#hint-order').hide();
                }
            });
        });

        var me = this;
        this.initCastTree(true, function() {
            if (User.disableDepts) {
                for (var k in User.disableDepts) {
                    getCheckbox('id', 'd-' + k, $('#cast-tree')).state('normal');

                    User.castCheckDepts(User.disableDepts[k].parentid);
                }
            }

            if (User.disableUsers) {
                for (var k in User.disableUsers) {
                    var userNode = me._castTree.find('u-' + User.disableUsers[k].userid, true);
                    if (!userNode) {
                        if (!User.disableUsers[k].deptid) {
                            User.disableUsers[k].deptid = '_root';
                        }
                        var deptNode = me._castTree.find('d-' + User.disableUsers[k].deptid, true), content;

                        content = '<input type="checkbox" name="castuser[]" value="{userid}" />{name}';
                        var node = new $.treenode({
                            data: {
                                id: 'u-' + User.disableUsers[k].userid,
                                userid: User.disableUsers[k].userid,
                                name: User.disableUsers[k].truename,
                                deptid: User.disableUsers[k].deptid
                            },
                            isLeaf: true,
                            content: content
                        });

                        deptNode.appendChild(node);

                        var checkbox = new $.checkbox({
                            name: 'castuser[]',
                            id: 'u-' + User.disableUsers[k].userid,
                            data: {
                                deptid: User.disableUsers[k].deptid
                            },
                            replace: $(':checkbox[name="castuser[]"]'),
                            states: {
                                normal: {
                                    value: User.disableUsers[k].userid,
                                    cls: ''
                                },
                                checked: {
                                    value: '',
                                    cls: 'checkbox-checked'
                                }
                            }
                        });

                        checkbox.state('checked');
                    }

                    getCheckbox('id', 'u-' + k, $('#cast-tree')).state('normal');

                    var deptid = User.disableUsers[k].deptid;
                    deptid = deptid.replace('^', '_');
                    dept = getCheckbox('id', 'd-' + deptid, $('#cast-tree'));
                    dept = dept.items[0];
                    if (dept) {
                        if (dept.state() !== 'normal') {
                            dept.state('half');
                        }
                        User.castCheckDepts(dept.data.parentid);
                    }
                }
            }
        });

        Cookie.set('FOCUS-USER', $('#userid').val());

        $('#theform').submit(function(){return false;});
        $('#theform').submit(function(){
            var form = $(this);

            var truename = Util.trim($('#truename').val());

            if (!truename) {
                return Message.show('请输入用户真实姓名');
            }

            var data = form.serializeArray();
            form.disable();

            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: data,
                url: form.attr('action'),
                success: function(ret) {
                    Message.show(ret.message, 5000, ret.success);
                    if (ret.success) {
                        setTimeout(function(){
                            location = BASE_PATH + '/user/user';
                        }, 800);
                    } else {
                        form.enable();
                    }
                },
                error: function(res) {
                    Message.show(Message.PROCESSING_ERROR, 5000);
                    form.enable();
                }
            });
        });
    },

    isDisable: true,

    /**
     * 批量编辑页面
     */
    initBatchEdit: function() {
        var groupIndex = 0;

        $('input.text').bind('focus blur mouseover mouseout', function(e) {
            if (e.type == 'focus' || e.type == 'mouseover') {
                $(this).addClass('text-hover');
            } else {
                if (e.type == 'blur' || document.activeElement != this) {
                    $(this).removeClass('text-hover');
                }
            }
        });

        $('#ndquota').bind('keyup', function(){
            this.value = this.value.replace(/[^0-9.]+/, '');
        })
        .blur(function(){
            $(this).val(this.value);
        });

        var me = this;
        $(':checkbox[name^="edit-"]').click(function(){
            var id = this.id.replace('check-', '');
            if (id == 'cast') {
                if (this.checked) {
                    me.isDisable = false;
                    getCheckbox('name', 'castdept[]', $('#cast-tree')).enabled();
                    getCheckbox('name', 'castuser[]', $('#cast-tree')).enabled();
                } else {
                    me.isDisable = true;
                    getCheckbox('name', 'castdept[]', $('#cast-tree')).disabled();
                    getCheckbox('name', 'castuser[]', $('#cast-tree')).disabled();
                }
                return;
            }

            if (id == 'role') {
                return $(':checkbox[name="roleid[]"]').attr('disabled', !this.checked);
            }

            if (id == 'group') {
                return $(':checkbox[name="groupid[]"], #groupname').attr('disabled', !this.checked);
            }

            if (id == 'netdisk') {
                return $('#ndquota').attr('disabled', !this.checked);
            }

            $('#' + id).attr('disabled', !this.checked);
        });

        var uids = [];
        $(':hidden[name="userid[]"]').each(function(){
            uids.push(this.value);
        });
        Cookie.set('FOCUS-USER', uids.join(','));

        new FixToolbar({
            target: 'div.tool-btm'
        });

        $('div.tag-wrap table.flagbg').each(function(){
            var o = $(this);
            o.find('td.tag-close').bind('click', function(){
                if ($('div.tag-wrap table.flagbg').size() <= 1) {
                    if (confirm('清除所有要编辑用户将取消本次批量编辑操作，确认取消？')) {
                        Cookie.set('FOCUS-USER', null);
                        location = BASE_PATH + '/user/user/';
                        return ;
                    } else {
                        return false;
                    }
                }

                o.remove();

                var uids = [];
                $(':hidden[name="userid[]"]').each(function(){
                    uids.push(this.value);
                });
                Cookie.set('FOCUS-USER', uids.join(','));
            });
        });

        $('#department').change(function(){
            if (this.value == '^new') {
                $('#new-dept').show();
            } else {
                $('#new-dept').hide();
            }
        });

        $('#groupname').keyhint();

        $('#create-group').bind('click', function(){
            var gn = $('#groupname');
            
            if (gn.is(':disabled')) {
                return ;
            }

            gn.focus();
            var name = gn.val();

            if (!Util.trim(name)) {
                return Message.show('请输入群组名称');
            }
            groupIndex++;
            var obj = $('<p><input type="checkbox" name="newgroup[]" checked="checked" value="'+groupIndex+'" /><input type="hidden" name="groupname-'+groupIndex+'" value="'+name+'" />'+name+'</p>')

            $('#group-list').append(obj);
            gn.val('');
        });

        this.initCastTree(true, function(){
            if (me.isDisable) {
                getCheckbox('name', 'castdept[]', $('#cast-tree')).disabled();
                getCheckbox('name', 'castuser[]', $('#cast-tree')).disabled();
            }
        });

        $(':checkbox[name="groupid[]"], :checkbox[name="newgroup[]"], #groupname').attr('disabled', 'disabled');
        $(':checkbox[name="roleid[]"], #groupname').attr('disabled', 'disabled');

        $('#theform').submit(function(){return false;});
        $('#theform').submit(function(){
            var form = $(this);
            
            if ($(':checkbox[name^="edit-"]').size() <= 0) {
                return Message.show('没有任何需要修改的项目');
            }

            var data = form.serializeArray();
            form.disable();

            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: data,
                url: form.attr('action'),
                success: function(ret) {
                    Message.show(ret.message, 5000, ret.success);
                    if (ret.success) {
                        setTimeout(function(){
                            location = BASE_PATH + '/user/user';
                        }, 800);
                    } else {
                        form.enable();
                    }
                },
                error: function(res) {
                    Message.show(Message.PROCESSING_ERROR, 5000);
                    form.enable();
                }
            });
        });
    },

    /**
     * 批量导入页面
     */
    initImport: function(flashUrl, cookies) {
        var swfu = new SWFUpload({
            upload_url: _TOP.SITES.tudu + BASE_PATH + "/user/user/upload-csv",
            flash_url: flashUrl,
            button_placeholder_id : "spanButtonPlaceHolder",
            post_params: {'cookies': cookies},
            file_types: "*.csv;*.CSV",
            file_types_description: "CSV(逗号分隔)",
            file_queue_limit: "1",
            button_width: "50",
            button_height: "22",
            button_text_left_padding: 20,
            button_text_top_padding: 1,
            button_window_mode: 'transparent',
            file_queued_handler: User.fileQueued,
            file_queue_error_handler: User.fileQueueError,
            file_dialog_complete_handler: User.fileDialogComplete,
            upload_success_handler: User.uploadSuccess,
            upload_complete_handler: User.uploadComplete
        });
    },

    fileQueued: function(file) {
        try {
            $('#csvfile').val(file.name);
        } catch (ex) {
            this.debug(ex);
        }
    },

    fileQueueError: function(file, errorCode, message) {
        try {
            switch (errorCode) {
            case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
                Message.show('只能上传单个文件');
                break;
            case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
                Message.show('文件太大了');
                break;
            case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
                Message.show('不能上传0字节文件');
                break;
            case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
                Message.show('文件类型错误');
                break;
            default:
                Message.show('Error' + errorCode);
                break;
            }
        } catch (ex) {
            this.debug(ex);
        }
    },

    fileDialogComplete: function(numFilesSelected, numFilesQueued) {
        try {
            if (numFilesSelected > 0 && numFilesSelected <= 1) {
                Message.show('正在提交数据，请稍后...', 5000, true);
                $('#btn-upload').attr('disabled', true);
                $('#csvfile').attr('disabled', true);
                this.startUpload();
            }
        } catch (ex)  {
            this.debug(ex);
        }
    },

    uploadSuccess: function(file, response) {
        try {
            var ret;
            eval('ret='+response+';');
            
            if (ret.success && ret.data) {
                $('#import-list').empty();
                var userInfo = ret.data,
                    remark = '',
                    userId = '',
                    status = '',
                    gender = '',
                    error = false;
                for (var i=0; i < userInfo.length; i++) {
                    if (!userInfo[i].email) {
                        remark = '失败，账号为空';
                        error = true;
                    }
                    if (!userInfo[i].truename && !userInfo[i].email) {
                        remark = '失败，真实姓名、账号为空';
                        error = true;
                    }
                    if (!userInfo[i].truename && userInfo[i].email) {
                        if (userInfo[i].email.indexOf('@') == -1) {
                            userInfo[i].truename = userInfo[i].email;
                        } else {
                            info = userInfo[i].email.split('@');
                            userInfo[i].truename = info[0];
                        }
                    }
                    if (!userInfo[i].gender || !(userInfo[i].gender == '男' || userInfo[i].gender == '女')) {
                        userInfo[i].gender = '男';
                    }
                    if (!userInfo[i].status || !(userInfo[i].status == '正式' || userInfo[i].status == '临时' || userInfo[i].status == '停用')) {
                        userInfo[i].status = '正式';
                    }
                    if (!userInfo[i].deptname) {
                        userInfo[i].deptname = '-';
                    }
                    if (!userInfo[i].rolename || !(userInfo[i].rolename == '高级管理员' || userInfo[i].rolename == '高级用户' || userInfo[i].rolename == '普通用户')) {
                        userInfo[i].rolename = '普通用户';
                    }
                    if (!userInfo[i].truename) {
                        userInfo[i].truename = '-';
                    }
                    if (!userInfo[i].email) {
                        userInfo[i].email = '-';
                    }
                    if (error) {
                        var html = '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list"><tr>';
                        html += '<td width="120"><div class="td-space"><span class="icon icon-user"></span>&nbsp;'+userInfo[i].truename+'</div></td>';
                        html += '<td width="270"><div class="td-space">'+userInfo[i].email+'</div></td>';
                        html += '<td width="95"><div class="td-space">'+userInfo[i].gender+'</div></td>';
                        html += '<td width="95"><div class="td-space">'+userInfo[i].status+'</div></td>';
                        html += '<td width="95"><div class="td-space">'+userInfo[i].deptname+'</div></td>';
                        html += '<td width="95"><div class="td-space">'+userInfo[i].rolename+'</div></td>';
                        html += '<td class="td-last red"><div class="td-space">'+remark+'</div></td></tr></table>';
                    } else {
                        if (userInfo[i].email.indexOf('@') == -1) {
                            userId = userInfo[i].email;
                        } else {
                            info = userInfo[i].email.split('@');
                            userId = info[0];
                        }
                        if (userInfo[i].status == '临时') {
                            status = 2;
                        } else if (userInfo[i].status == '停用') {
                            status = 0;
                        } else {
                            status = 1;
                        }
                        if (userInfo[i].gender == '女') {
                            gender = 0;
                        } else {
                            gender = 1;
                        }

                        var html = '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list"><tr>';
                        html += '<td width="120"><div class="td-space"><span class="icon icon-user"></span>&nbsp;'+userInfo[i].truename+'<input type="hidden" name="user[]" value="'+i+'" /><input type="hidden" value="'+userId+'" name="userid-'+i+'" /><input type="hidden" name="truename-'+i+'" value="'+userInfo[i].truename+'" /></div></td>';
                        html += '<td width="140"><div class="td-space">'+userInfo[i].email+'<input type="hidden" name="email-'+i+'" value="'+userInfo[i].email+'" /></div></td>';
                        html += '<td width="95"><div class="td-space">'+userInfo[i].gender+'<input type="hidden" name="gender-'+i+'" value="'+gender+'" /></div></td>';
                        html += '<td width="95"><div class="td-space">'+userInfo[i].status+'<input type="hidden" name="status-'+i+'" value="'+status+'" /></div></td>';
                        html += '<td width="200"><div class="td-space">'+userInfo[i].deptname+'<input type="hidden" name="deptname-'+i+'" value="'+userInfo[i].deptname+'" /></div></td>';
                        html += '<td width="95"><div class="td-space">'+userInfo[i].rolename+'<input type="hidden" name="rolename-'+i+'" value="'+userInfo[i].rolename+'" /></div></td>';
                        html += '<td class="td-last"><div class="td-space"><span id="remark-'+i+'">'+remark+'</span></div></td></tr></table>';
                    }
                    $('#import-list').append(html);
                    remark = '';
                    userId = '';
                    status = '';
                    gender = '';
                    error = false;
                }
                // 提交数据
                var userForm = $('#userform'),
                    data = userForm.serializeArray();

                data = User.batchData(1, data);
                User.length = data.length;
                User.idx = 0;
                User.importData(data[0], function(){User.importCallback(data);});
            } else {
                Message.show(ret.message);
            }
        } catch (ex) {
            this.debug(ex);
        }
    },

    uploadComplete: function(file) {
        if (this.getStats().files_queued === 0) {
            $('#btn-upload').attr('disabled', false);
            $('#csvfile').attr('disabled', false);
        }
    },

    length: null,

    idx: null,

    // 提交导入数据（回调函数）
    importCallback: function(data) {
        var me = this;
        if (me.length > 1) {
            me.idx++;
            if (data[me.idx]){
                User.importData(data[me.idx], function(){User.importCallback(data);});
            }
        }
    },

    // 提交导入数据
    importData: function(data, callback) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: BASE_PATH + '/user/user/import',
            success: function(ret) {
                if (ret.success && ret.data) {
                    var data = ret.data;
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].message) {
                            $('#remark-' + data[i].num).text(data[i].message);
                        }
                        if (!data[i].success) {
                            $('#remark-' + data[i].num).addClass('red');
                        }
                    }

                    if (typeof(callback) == 'function') {
                        callback.call();
                    }
                } else {
                    Message.show(ret.message);
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR, 5000);
            }
        });
    },

    // 分批数据   --> num为几条记录为一批
    batchData: function(num, data) {
        var ret = new Array(),
            item = [];

        if (!num) {
            ret = {0: data};
            return ret;
        }

        $('input[name="user[]"]').each(function(){
            item.push($(this).val());
        });

        if (!item.length || item.length <= num) {
            ret = {0: data};
            return ret;
        }

        var i = 0, j = 0, arr = new Array();
        for (var key in data) {
            if (data[key]['name'] == 'user[]') {
                i++;
            }

            if (i == num + 1) {
                i = 0;
                ret[j] = arr;
                j++;
                arr = new Array();
            }

            arr.push(data[key]);
        }
        ret[j] = arr;

        return ret;
    },

    // 填充出生日期表格
    fillDateSelect: function() {
        var year  = $('#bir-year').val();
        var month = $('#bir-month').val();
        var day   = $('#bir-day');

        if (!year || !month) {
            return false;
        }

        if (month > 11) {
            month = 0;
            ++year;
        }
        var d = new Date(year, month, 1), i = 0, day = $('#bir-day');
        var days = new Date(d.getTime() - 86400 * 1000).getDate();

        day.empty();
        day.append('<option value="">-</option>');
        for (i; i < days; i++) {
            d = i + 1;
            day.append('<option value="'+d+'">'+d+'</option>');
        }
    },

    /**
     * 上传头像
     */
    avatar: function() {
        var me = this, jcrop_api = null;
        if (!this.avatarWin) {
            this.avatarWin = Admin.window({
                width: 460,
                id: 'user-avatarwin',
                draggable: true,
                form: false,
                title: '更改头像',
                body: me.tpl.avatar,
                footer: '<button id="avatarsubmit" disabled="disabled">确定</button>',
                onShow: function() {
                    //$('select').hide();
                },
                onClose: function() {
                    //$('select').show();
                    me.avatarWin.destroy();
                    me.avatarWin = null;
                },
                init: function(){
                    Frame.queryParent('#avatarsubmit').click(function(){
                        var avatarform = Frame.queryParent('#avatarform');
                        var hash = avatarform.find('input[name="hash"]').val();

                        if (!hash) {
                            me.avatarWin.close();
                            return ;
                        }

                        var data = {
                            hash: avatarform.find('input[name="hash"]').val(),
                            x: avatarform.find('input[name="x"]').val(),
                            y: avatarform.find('input[name="y"]').val(),
                            width: avatarform.find('input[name="width"]').val(),
                            height: avatarform.find('input[name="height"]').val()
                        };

                        $('form').disable();

                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            data: data,
                            url: avatarform.attr('action'),
                            success: function(ret) {
                                Message.show(ret.message, 5000, ret.success);

                                if (ret.success) {
                                    if (ret.data.avatar) {
                                        Frame.queryParent('#avatars').val(ret.data.avatar);
                                        Frame.queryParent('#avatar-preview').attr('src', BASE_PATH + '/user/user/avatar/?hash=' + ret.data.avatar).show();
                                        $('#avatar-img').attr('src', BASE_PATH + '/user/user/avatar/?hash=' + ret.data.avatar).show();
                                        $('#avatars').val(ret.data.avatar);
                                    }

                                    $('form').enable();
                                    me.avatarWin.close();
                                }
                            }
                        });
                    });

                    Frame.queryParent('#uploadform').submit(function(){return false;});
                    Frame.queryParent('#uploadform').submit(function(e){
                        var form = Frame.queryParent(this);
                        var userform = $('#userform');
                        var avatarform = Frame.queryParent('#avatarform');

                        userform.disable();

                        Frame.queryParent('#avatar-src').attr('src', '');
                        Frame.queryParent('#avatar-preview').attr('src', '');

                        Frame.getJQ().ajaxUpload({
                            url: form.attr("action"),
                            file: Frame.queryParent('#avatar-file'),
                            data: {},
                            dataType: "json",
                            success: function(ret){
                                Message.show(ret.message, 5000, ret.success);

                                if (ret.success) {
                                    Frame.queryParent('#avatar-edit').show();
                                    Frame.queryParent('#avatarsubmit').enable();

                                    avatarScale = 1;
                                    Frame.queryParent('#avatar-src')
                                    .css({width: 'auto', height: 'auto'});

                                    avatarform.find(':hidden[name="hash"]').val(ret.data.hash);
                                    avatarform.find('img')
                                    .attr('src', BASE_PATH + '/user/user/avatar/?hash=' + ret.data.hash + '&rnd=' + Math.random())
                                    .hide();

                                    me.avatarWin.center();

                                    Frame.queryParent('#avatar-src').bind('load', function(){
                                        avatarform.find('img').show();

                                        var w = this.width;
                                        var h = this.height;

                                        if (w > 235 || h > 195) {
                                            $('.edit_box_big')
                                            .css({width: 235 + 'px', height: 195 + 'px'});

                                            if (w > h) {
                                                avatarScale = 235 / w;
                                            } else {
                                                avatarScale = 195 / h;
                                            }

                                            this.style.width  = w * avatarScale;
                                            this.style.height = h * avatarScale;
                                        }

                                        var width  = w * avatarScale,
                                            height = h * avatarScale;

                                        Frame.queryParent('#avatar-src')
                                        .css({width: width + 'px', height: height + 'px'});
                                        Frame.queryParent('.edit_box_big')
                                        .css({width: width + 'px', height: height + 'px'});

                                        if (jcrop_api) {
                                            jcrop_api.destroy();
                                        }

                                        jcrop_api = Frame.getJQ().Jcrop('#avatar-src', {
                                            onChang: _avatarChangeSize,
                                            onSelect: _avatarChangeSize,
                                            aspectRatio: 1
                                        });

                                        Frame.queryParent('#avatar-src').unbind('load');
                                    });
                                }

                                userform.enable();
                            },
                            error: function(res){
                                Message.show('头像文件上传失败');
                                userform.enable();
                                form.enable();
                            }
                        });
                    });
                }
            });
        }

        function _avatarChangeSize(coords) {
            var form = Frame.queryParent('#avatarform');
            if (parseInt(coords.w) > 0)
            {
                var rx = 98 / coords.w;
                var ry = 98 / coords.h;

                Frame.queryParent('#avatar-preview').css({
                    width: Math.round(rx * Frame.queryParent('#avatar-src').width()) + 'px',
                    height: Math.round(ry * Frame.queryParent('#avatar-src').height()) + 'px',
                    marginLeft: '-' + Math.round(rx * coords.x) + 'px',
                    marginTop: '-' + Math.round(ry * coords.y) + 'px'
                });

                form.find('input[name="x"]').val(Math.round(coords.x / avatarScale));
                form.find('input[name="y"]').val(Math.round(coords.y / avatarScale));
                form.find('input[name="width"]').val(Math.round(coords.w / avatarScale));
                form.find('input[name="height"]').val(Math.round(coords.h / avatarScale));
            } else {
                form.find('input[name!="hash"]').val('');
                Frame.queryParent('#avatar-preview').css({
                    width: '96px',
                    height: '96px',
                    marginLeft: 0,
                    marginTop: 0
                });
            }
        }

        this.avatarWin.show();
        Frame.queryParent('#avatarform input').val('');
        Frame.queryParent('#avatar-edit').hide();
    },

    /**
     * 初始化组织架构图
     */
    initCastTree: function(checkAll, callback) {
        var me = this;

        this._castTree = new $.tree({
            id: 'cast-tree',
            idKey: 'id',
            idPrefix: 'cast-',
            cls: 'cast-tree'
        });

        this._castTree.appendTo($('#tree-ct'));

        var content;
        for (var i = 0, c = depts.length; i < c; i++) {
            content = '<input type="checkbox" name="castdept[]" value="{deptid}" />{name}';
            if (depts[i].deptid == '_root') {
                depts[i].deptname = _ORG_NAME;
            }
            var node = new $.treenode({
                data: {
                    id: 'd-' + depts[i].deptid,
                    deptid: depts[i].deptid,
                    name: depts[i].deptname,
                    parentid: depts[i].parentid
                },
                autoEcIcon: false,
                content: content
            });

            node.bind('expand', function(){
                me.castExpandDept(this, checkAll, callback);
            });

            if (depts[i].parentid) {
                var parent = me._castTree.find('d-' + depts[i].parentid, true);

                if (!parent) {
                    continue;
                }

                parent.appendChild(node);
            } else {
                me._castTree.appendNode(node);
            }

            var checkbox = new $.checkbox({
                name: 'castdept[]',
                id: 'd-' + depts[i].deptid,
                data: {parentid: depts[i].parentid},
                replace: $(':checkbox[name="castdept[]"]'),
                states: {
                    normal: {
                        value: depts[i].deptid,
                        cls: ''
                    },
                    half: {
                        value: '',
                        cls: 'checkbox-half'
                    },
                    checked: {
                        value: '',
                        cls: 'checkbox-checked'
                    }
                }
            });

            /*checkbox.bind('change', function() {
                var id = this.id.replace('d-', '');
                if (this.state() == 'normal') {
                    User.disableDepts[id] = {deptid: id, parentid: this.data.perentid};
                } else {
                    delete User.disableDepts[id];
                }
            });*/
        }

        var deptChecks = getCheckbox('name', 'castdept[]', $('#cast-tree'))
        
        var root = this._castTree.getNode('d-_root', true);
        if (root != null) {
            root.expand();
        }

        deptChecks.bind('click', function(e) {
            if (this.state() === 'half') {
                this.setState('checked');
            }
        });

        $('#tree-ct').bind('click', function(e){
            var src = e.srcElement ? e.srcElement : e.target;
            src = $(src);

            if (src.hasClass('tree-ec-icon')) {
                return ;
            } else if (src.hasClass('checkbox')) {
                var checkbox = getCheckbox('id', src.attr('id'), $('#cast-tree'));
                if (checkbox.items[0]) {
                    checkbox = checkbox.items[0];
                }

                var type = 'dept', id = src.attr('id');

                if (src.attr('_name') == 'castdept[]') {
                    var ct = src.parents('li.tree-node:eq(0)').find('ul:eq(0)');
                    var state = checkbox.state() == 'normal' ? 'normal' : 'checked';
                    id = id.replace('d-', '');
                    getCheckbox('name', 'castdept[]', ct).state(state);
                    getCheckbox('name', 'castuser[]', ct).state(state);

                    User.castCheckDepts(checkbox.data.parentid);
                } else {
                    type = 'user';
                    id   = id.replace('u-', '');

                    User.castCheckDepts(checkbox.data.deptid);
                }

                return ;
            } else {
                var node = src.parents('.tree-node:eq(0)');
                var id = node.attr('id');

                id = id.replace('cast-', '').replace('cast-', '');
                getCheckbox('id', id, node).each(function(){
                    var st = 'normal';
                    if (this.state() == 'normal') {
                        st = 'checked';
                    }

                    this.state(st);
                });
            }
        });

        if (checkAll) {
            getCheckbox('name', 'castdept[]').setState('checked');
        }
    },

    /**
     * 
     */
    castCheckDepts: function(deptid) {
        if (!deptid) {
            return ;
        }

        deptid = deptid.replace('^', '_');

        var checkbox = getCheckbox('id', 'd-' + deptid);
        checkbox = checkbox.items[0];

        if (!checkbox) {
            return ;
        }

        var ct = $('#cast-d-' + deptid + '> ul');

        var depts = getCheckbox('name', 'castdept[]', ct);
        var users = getCheckbox('name', 'castuser[]', ct);

        var state = checkbox.state();

        var disable = 0;
        if (users.len() > 0) {
            users.each(function(){
                if (this.state() == 'normal') {
                    disable++;
                }
            });

            if (disable >= users.len()) {
                state = 'normal';
            } else {
                state = disable == 0 ? 'checked' : 'half';
            }
        }

        disable = 0;
        if (depts.len() > 0) {
            depts.each(function() {
                if (this.state() == 'normal') {
                    disable ++;
                }
            });

            if (disable >= depts.len()) {
                state = state == 'normal' ? 'normal' : 'half';
            } else if (disable == 0) {
                state = state == 'checked' ? 'checked' : 'half';
            } else {
                state = 'half';
            }
        }

        checkbox.state(state);

        if (checkbox.data.parentid) {
            User.castCheckDepts(checkbox.data.parentid);
        }
    },

    /**
     * 展开部门节点
     */
    castExpandDept: function(node, checkAll, callback) {
        var me = this;
        if (!this._castTree) {
            return ;
        }

        var deptid = node.data.deptid;
        var checkbox = getCheckbox('id', node.data.id, node.nodeBody);
        checkbox = checkbox.items[0];
        var state = checkbox.state();

        if (node && !node.isLeaf && node.isExpanded) {
            if (undefined === this.users[deptid]) {
                node.nodeBody.append('<img src="' + _TOP.SITES['static'] + '/img/loading.gif" name="icon-loading" style="vertical-align:middle;margin-left:5px" />');
                $.ajax({
                    type: 'GET',
                    url: BASE_PATH + '/user/user/users?deptid=' + deptid,
                    dataType: 'json',
                    success: function(ret) {
                        node.nodeBody.find('img[name="icon-loading"]').remove();

                        if (ret.success && ret.data) {
                            me.users[deptid] = ret.data;
                        }

                        _fillUsers(node, me.users[deptid]);
                    },
                    error: function(res) {
                        node.nodeBody.find('img[name="icon-loading"]').remove();
                    }
                });
            }
        }

        // 填充列表
        function _fillUsers(n, users) {
            var content;
            for (var i = 0, l = users.length; i < l; i++) {
                var node = me._castTree.find('u-' + users[i].userid, true);
                if (node) {
                    continue;
                }

                content = '<input type="checkbox" name="castuser[]" value="{userid}" />{name}';
                var node = new $.treenode({
                    data: {
                        id: 'u-' + users[i].userid,
                        userid: users[i].userid,
                        name: users[i].truename,
                        deptid: users[i].deptid
                    },
                    isLeaf: true,
                    content: content
                });

                n.appendChild(node);

                var checkbox = new $.checkbox({
                    name: 'castuser[]',
                    id: 'u-' + users[i].userid,
                    data: {deptid: users[i].deptid},
                    replace: $(':checkbox[name="castuser[]"]'),
                    states: {
                        normal: {
                            value: users[i].userid,
                            cls: ''
                        },
                        checked: {
                            value: '',
                            cls: 'checkbox-checked'
                        }
                    }
                });

                if (checkAll) {
                    checkbox.state('checked');
                }
            }

            if (typeof(callback) == 'function') {
                callback.call();
            }
        }
    },

    /**
     * 焦点某行
     *
     * @param {Object} userid
     */
    focusUser: function(userid) {
        $('#user-list tr.focus').removeClass('focus');
        if (!Util.isArray(userid)) {
            userid = [userid];
        }
        for (var i = 0, c = userid.length; i < c; i++) {
            if ($('#u-' + userid[i].replace('^', '_')).size()) {
                $('#u-' + userid[i].replace('^', '_')).rowFocus();
            }
        }
    },

    /**
     * 格式化日期
     *
     * @param {Object} ts
     */
    formatDate: function(ts) {
        var d = new Date();
        d.setTime(ts * 1000);

        var ret = [
            d.getFullYear(), 
            d.getMonth() > 9 ? d.getMonth() + 1 : '0' + (d.getMonth() + 1),
            d.getDate() > 9 ? d.getDate() : '0' + d.getDate()
        ];

        return ret.join('-');
    },

    /**
     * 格式化状态
     *
     * @param {Object} status
     */
    formatStatus: function(status) {
        return status == 0 
               ? '停用'
               : (status == 2 ? '临时' : '正式');
    }
};