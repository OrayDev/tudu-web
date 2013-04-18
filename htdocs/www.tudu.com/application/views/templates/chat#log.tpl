<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.chat_log}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<style type="text/css">
<!--
html,body{ height:100%;}
#cast-tree-ct {
    overflow:auto;
    overflow-x:hidden;
}
-->
</style>
</head>
<body class="chat">
<div class="container">
    <div class="c_right">
        <div class="contacts_box">
            <div class="contacts_title">{{$LANG.contacts}}</div>
            <div class="contacts_main">
                <div id="cast-tree-ct">
                </div>
            </div>
            <div class="contacts_title">{{$LANG.group_manage}}</div>
            <div class="contacts_main">
                <div id="cast-tree-pt">
                </div>
            </div>
            <div class="contacts_title">{{$LANG.disscuss_group}}</div>
            <div class="contacts_main">
                <ul id="cast-tree-dt" class="contact_group_list">
                </ul>
            </div>
        </div>
    </div>
    <div class="c_left">
        <div class="fr">

        </div>
        <div class="position">
            <p><strong class="title">{{$LANG.chat_log}}</strong></p>
            <div class="position_right" style="margin-top:-5px;">
            <input type="hidden" id="otherid" value="{{$email}}" />
            {{$LANG.search_condition}}{{$LANG.cln}}
            <select id="target" style="width:95px;margin-right:5px;">
            {{if $email}}<option value="^current" selected="selected">{{$LANG.current_user}}</option>{{/if}}
            <option value="^users">{{$LANG.all_user}}</option>
            <option value="^groups">{{$LANG.disscuss_group}}</option>
            </select>
            <select id="groupid" style="width:95px;margin-right:5px;display:none">
            </select>
            <select id="range" style="width:95px;margin-right:5px;">
            <option value="">{{$LANG.all}}</option>
            <option value="week" selected="selected">{{$LANG.nearly_week}}</option>
            <option value="month">{{$LANG.nearly_month}}</option>
            <option value="3month">{{$LANG.nearly_3month}}</option>
            </select>
            <input class="input_text" id="keyword" type="text" value="" style="margin-right:5px;" title="{{$LANG.search_hint}}" />
            <input id="btnsearch" type="button" class="btn"  value="{{$LANG.search}}" /></div>
        </div>
    <div class="panel" id="log-content">
        <div class="toolbar">
            <div class="tb_empty"><strong>{{$LANG.chat_log}}</strong></div>
        </div>
        <div class="panel-body">
            <div class="chat_box" id="chat-content">
                <div style="padding:20px 10px;text-align: center">
                    {{if !$email}}{{$LANG.select_contact}}{{/if}}
                </div>
            </div>
        </div>
        <div class="toolbar">
            <div class="tb_empty"></div>
        </div>
    </div>
    </div>
</div>
<a id="page-bottom"></a>
<script type="text/javascript">
<!--
var _CAST_TREE, _GROUP_CAST_TREE;
$(function(){

    TOP.Label.focusLabel('');
    TOP.Frame.title('{{$LANG.chat_log}}');
    TOP.Frame.hash('m=chat/log-list{{if $email}}&email={{$email}}{{/if}}');

    // 搜索框s
    $('#target').bind('change', function(){
        if (this.value == '^groups') {
            $('#groupid').show();
        } else {
            $('#groupid').hide();
        }
    });

    _CAST_TREE = new $.tree({
        id: 'cast-tree',
        idKey: 'id',
        idPrefix: 'cast-',
        cls: 'contact-dept-tree',
        template: '{name}'
    });
    _CAST_TREE.appendTo('#cast-tree-ct');

    _GROUP_CAST_TREE = new $.tree({
        id: 'group-cast-tree',
        idKey: 'id',
        idPrefix: 'group-',
        cls: 'contact-dept-tree',
        template: '{name}'
    });
    _GROUP_CAST_TREE.appendTo('#cast-tree-pt');

    $('button[name="tudu"]').click(function(){
        if (!$(':checkbox[name="unid[]"]:checked').size()) return ;

        var unid = $(':checkbox[name="unid[]"]:checked:eq(0)').val();
        location = '/tudu/modify/?to=' + unid;
    });
    TOP.keyhint('#keyword', 'input_tips', true, document.body);

    $('#btnsearch').bind('click', function(){
        search();
    });
    $('#keyword').bind('keyup', function(e){
        var code = e.keyCode ? e.keyCode : e.which;
        if (code == 13) {
            search();
        }
    });

    TOP.Cast.load(function(cast){
        var depts  = TOP.Cast.get('depts'),
            users  = TOP.Cast.get('users'),
            parent = null,
            deptid = null;

        for (var i = 0, c = depts.length; i < c; i++) {
            var deptname = depts[i].deptid == '^root' ? TOP._ORGNAME : depts[i].deptname
            var deptid = depts[i].deptid.replace('^', '_');
            var node = new $.treenode({
                data: {
                    id: 'd-' + deptid,
                    name: deptname
                },
                events: {
                    click: function(e){var d = _CAST_TREE.find(this.id.replace('cast-', ''), true);if (d){d.toggle();};TOP.stopEventBuddle(e);}
                }
            });

            if (depts[i].parentid) {
                parent = _CAST_TREE.find('d-' + depts[i].parentid.replace('^', '_'), true);

                if (parent) {
                    parent.appendChild(node);
                }
            } else {
                _CAST_TREE.appendNode(node);
            }
        }

        for (var j = 0, k = users.length; j < k; j++) {
            var userid = users[j].userid;

            var node = new $.treenode({
                data: {
                    id: 'u-' + j,
                    name: '<span _email="'+users[j].username+'">' + users[j].truename + '</span>',
                    email: users[j].username
                },
                isLeaf: true,
                events: {
                    mouseover: function(e){$(this).find('.tree-node-el:eq(0)').addClass('tree-node-over');TOP.stopEventBuddle(e)},
                    mouseout: function(e){$(this).find('.tree-node-el:eq(0)').removeClass('tree-node-over');TOP.stopEventBuddle(e)},
                    click: function(e){
                        var node = _CAST_TREE.find(this.id.replace('cast-', ''), true);

                        if (node) {
                            $('.tree-node-selected').removeClass('tree-node-selected');
                            $('#cast-tree-dt li').removeClass('selected');
                            $('#cast-' + node.get('id') + ' .tree-node-el').addClass('tree-node-selected');
                            $('#chat-content')
                            .css({padding: '20px 10px', textAlign: 'center'})
                            .text(TOP.TEXT.LOADING_CHAT_LOG);
                            loadChatLog(node.get('email'));

                            $('#otherid').val(node.get('email'));
                        }
                        TOP.stopEventBuddle(e);
                    }
                }
            });

            var deptid = users[j].deptid ? users[j].deptid : '_root';

            var parent = _CAST_TREE.find('d-' + deptid, true);
            if (parent) {
                parent.appendChild(node);
            } else {
                _CAST_TREE.appendNode(node);
            }
        }

        {{if $email}}
        var u = _CAST_TREE.find('{{$email}}', true, 'email');

        if (u) {
            if (u.parent) {
                u.parent.expand();
            }

            $('#cast-' +  u.get('id')).click();
        }
        {{/if}}
    });

    loadGroups(_GROUP_CAST_TREE);
    loadDiscusses();

    $(window).bind('resize', function(){ajustSize();});
    ajustSize();
});

function search(){
    $('#keyword').focus();

    var keyword = $('#keyword').val();
    var range   = $('#range').val();
    var target  = $('#target').val();

    keyword = keyword.replace(/^\s+|\s+$/, '');

    if (!keyword.length || $('#keyword').hasClass('input_tips')) {
        return alert('{{$LANG.search_hint}}');
    }

    var url = '/chat/search?keyword=' + encodeURIComponent(keyword) + '&range=' + range + '&target=' + target;

    if (target == '^current') {
        url += '&otherid=' + $('#otherid').val();
    } else if (target == '^groups') {
        url += '&groupid=' + $('#groupid').val();
    }

    var back = TOP.Frame.hash();
    back = back.replace(/^(#|\?)+/, '');
    var arr = back.split('&');
    var path = null, qs = [];
    for (var i = 0, c = arr.length; i < c; i++) {
        var a = arr[i].split('=');
        if (a.length != 2) continue;

        if (a[0] == 'm') {
            path = a[1];
            continue;
        }

        qs.push(a[0] + '=' + encodeURIComponent(a[1]));
    }
    back = '/' + path + '?' + qs.join('&');

    url += '&back=' + encodeURIComponent(back);

    $('#chat-content').html('<div style="padding:10px 0">' + TOP.TEXT.LOADING_CHAT_LOG + '</div>');
    $('#log-content').load(url, function(){
        replaceSenderName('group');

        TOP.Frame.hash('m=' + url.replace('?', '&').replace(/^\/+?/, ''));

        ajustSize();
    });
}

function highlight(keyword) {
    if (typeof keyword == 'string') {
        keyword = [keyword];
    }
    var len = keyword.length,
        reg = new RegExp('(' + keyword.join('|') + ')', 'ig');
    $('div.chat_content').each(function(){
        var o = $(this);
        var h = o.html();
        h = h.replace(reg, '<span class="result-hlight">$1</span>');
        o.html(h);
    });
}

function replaceSenderName(type) {
    if (type == 'group') {
        $('span.sendername').each(function(){
            var email = this.innerHTML,
                node = $('#cast-tree span[_email="'+email+'"]');
            if (node.size()) {
                this.innerHTML = node.html();
            }
        });
    }

    if ($('#chat-title .sendername').size()) {
        var name = $('.tree-node-selected').text();
        $('#chat-title .sendername').text(name);
    }
}

function ajustSize() {
       var height = Math.max(document.body.clientHeight - 20, $('.c_left').height());

       if ($.msie) {
        $('.contacts_box').height(height - 20);
       } else {
           $('.contacts_box').css('min-height', height - 20 + 'px');
       }
    //$('#cast-tree-ct').height(height - 90);
    //$('#cast-tree-pt').height(height - 90);
}

function loadChatLog(email, params, type) {
    var query = [];
    if (type == 'group') {
        query.push('group=' + email);
        $('#target').val('^groups');
        $('#groupid').show().val(email);
        $('#target option[value="^current"]').remove();
    } else {
        query.push('email=' + email);
        $('#groupid').hide();
        if (!$('#target option[value="^current"]').size()) {
            $('#target').prepend('<option value="^current">{{$LANG.current_user}}</option>');
        }
        $('#target').val('^current');
    }
    $('#keyword').val('').focus().blur();
    $('#range').val('week');

    if (params) {
        for (var k in params) {
            query.push(k + '=' + params[k]);
        }
    }

    if (!$('#uid').size()) {
        $('#chat-content div').text(TOP.TEXT.LOADING_CHAT_LOG);
    }

    $('#cast-tree-dt li').removeClass('selected');

    loadContent('/chat/log-list?' + query.join('&'), function(){
        if (type == 'group') {
            $('a[id="' + email + '"]').parent().addClass('selected');
            $('.tree-node-selected').removeClass('tree-node-selected');
        }
        $('.chat_log').mousemove(function(){
            $(this).addClass('chat_part_over');
            if (type != 'group') {
                $(this).find('a[name="del-log"]').show();
            }
        }).mouseout(function(evt){
            $(this).removeClass('chat_part_over');
            if (type != 'group') {
                var e = $(this),
                    offset = e.offset(),
                    pageTop= document.documentElement ? document.documentElement.scrollTop : document.body.scrollTop,
                    evtTop = evt.clientY + pageTop;
                var isOver = evt.clientX > offset.left && evt.clientX < offset.left + e.width()
                           && evtTop > offset.top && evtTop < offset.top + e.height();
                if (!isOver) {$(this).find('a[name="del-log"]').hide();}
            }
        });

        $('input[name="pageinput"]').bind('keyup', function(){
            this.value = this.value.replace(/[^0-9]+/, '');
        }).blur(function(){
            $('input[name="pageinput"]').val(this.value);
        });
    });
}

function loadChatDetail(email, params, type) {
    var query = [];
    if (type == 'group') {
        query.push('group=' + email);
    } else {
        query.push('email=' + email);
    }

    if (params) {
        for (var k in params) {
            query.push(k + '=' + encodeURIComponent(params[k]));
        }
    }

    if (!$('#uid').size()) {
        $('#chat-content').html('<div style="padding:10px 0">' + TOP.TEXT.LOADING_CHAT_LOG + '</div>');
    }

    $('#cast-tree-dt li').removeClass('selected');

    var back = TOP.Frame.hash();
    back = back.replace(/^(#|\?)+/, '');
    var arr = back.split('&');
    var path = null, qs = [];
    for (var i = 0, c = arr.length; i < c; i++) {
        var a = arr[i].split('=');
        if (a.length != 2) continue;

        if (a[0] == 'm') {
            path = a[1];
            continue;
        }

        qs.push(a[0] + '=' + a[1]);
    }
    back = '/' + path + '?' + qs.join('&');

    var url = '/chat/detail?' + query.join('&');
    url += '&back=' + encodeURIComponent(back);

    loadContent(url, function() {
        $('.chat_log').mousemove(function(){
            $(this).addClass('chat_part_over');
            if (type != 'group') {
                $(this).find('a[name="del-log"]').show();
            }
        }).mouseout(function(){
            $(this).removeClass('chat_part_over');
            if (type != 'group') {
                var e = $(this),
                    offset = e.offset(),
                    pageTop= document.documentElement ? document.documentElement.scrollTop : document.body.scrollTop,
                    evtTop = evt.clientY + pageTop;
                var isOver = evt.clientX > offset.left && evt.clientX < offset.left + e.width()
                           && evtTop > offset.top && evtTop < offset.top + e.height();
                if (!isOver) {$(this).find('a[name="del-log"]').hide();}
            }
        });
    });
}

function loadContent(url, callback) {
    $.ajax({
        type: 'GET',
        dataType: 'text',
        url: url,
        success: function(ret) {
            if (!ret) {return;}
            $('#log-content').html(ret);

            if (typeof (callback) == 'function') {
                callback.call(this);
            }

            replaceSenderName('group');
            ajustSize();

            var m = url.replace(/^\/+?/, '').replace('?', '&');
            TOP.Frame.hash('m=' + m);
        },
        error: function(res) {}
    });
}

function loadDiscusses() {
    var group = {{if $group}}'{{$group}}'{{else}}null{{/if}};
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '/chat/discuss',
        success: function(ret) {
            if (ret.success) {
                if (ret.data && ret.data.discuss) {
                    var discuss = ret.data.discuss;

                    for (var i = 0, c = discuss.length; i < c; i++) {
                        var groupid = discuss[i].groupid,
                            groupname = discuss[i].groupname;

                        if (groupname.length > 12) {
                            groupname = groupname.substring(0,12) + '...';
                        }

                        $('#cast-tree-dt').append('<li><a id="'+groupid+'" title="'+discuss[i].groupname+'">'+groupname+'</a></li>');
                        $('#groupid').append('<option value="'+groupid+'" title="'+discuss[i].groupname+'">'+groupname+'</option>');
                    }
                    if (group) {
                        loadChatLog(group, null, 'group');
                    }
                }
                $('#cast-tree-dt li').find('a').click(function(){
                    loadChatLog(this.id, null, 'group');
                    $('#otherid').val(this.id);
                });

            }
        },
        error: function(res) {
        }
    });
}

function loadGroups(_GROUP_CAST_TREE) {
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '/chat/group',
        success: function(ret) {
            if (ret.success) {
                if (ret.data && ret.data.contacts && ret.data.groups) {
                    var groups    = ret.data.groups,
                        contacts  = ret.data.contacts,
                        parentGroup = null;

                    var defaultNode = new $.treenode({
                        data: {
                            id: 'g-_none',
                            name: '{{$LANG.non_group}}'
                        },
                        events: {
                            click: function(e){var d = _GROUP_CAST_TREE.find(this.id.replace('group-', ''), true);if (d){d.toggle();};TOP.stopEventBuddle(e);}
                        }
                    });

                    _GROUP_CAST_TREE.appendNode(defaultNode);

                    for (var i = 0, c = groups.length; i < c; i++) {
                        var groupid = groups[i].groupid;
                        var node = new $.treenode({
                            data: {
                                id: 'g-' + groupid,
                                name: groups[i].groupname
                            },
                            events: {
                                click: function(e){var d = _GROUP_CAST_TREE.find(this.id.replace('group-', ''), true);if (d){d.toggle();};TOP.stopEventBuddle(e);}
                            }
                        });

                        _GROUP_CAST_TREE.appendNode(node);
                    }

                    for (var j = 0, k = contacts.length; j < k; j++) {

                        var contactid = contacts[j].contactid,
                            groups    = contacts[j].groups,
                            name = contacts[j].displayname ? contacts[j].displayname : contacts[j].email;

                        if (!groups || !groups.length) {
                            groups = ['_none'];
                        }

                        for (var idx = 0,len = groups.length; idx < len; idx++) {
                            var node = new $.treenode({
                                data: {
                                    id: 'c-' + groups[idx] + '-' + j,
                                    name: name,
                                    email: contacts[j].email
                                },
                                isLeaf: true,
                                events: {
                                    mouseover: function(e){$(this).find('.tree-node-el:eq(0)').addClass('tree-node-over');TOP.stopEventBuddle(e)},
                                    mouseout: function(e){$(this).find('.tree-node-el:eq(0)').removeClass('tree-node-over');TOP.stopEventBuddle(e)},
                                    click: function(e){
                                        var node = _GROUP_CAST_TREE.find(this.id.replace('group-', ''), true);

                                        if (node) {
                                            $('.tree-node-selected').removeClass('tree-node-selected');
                                            $('#cast-tree-dt li').removeClass('selected');
                                            $('#group-' + node.get('id') + ' .tree-node-el').addClass('tree-node-selected');
                                            $('#chat-content')
                                            .css({padding: '20px 10px', textAlign: 'center'})
                                            .text(TOP.TEXT.LOADING_CHAT_LOG);
                                            loadChatLog(node.get('email'));
                                        }
                                        $('#otherid').val(node.get('email'));
                                        TOP.stopEventBuddle(e);
                                    }
                                }
                            });

                            parentGroup = _GROUP_CAST_TREE.find('g-' + groups[idx], true);
                            if (parentGroup) {
                                parentGroup.appendChild(node);
                            } else {
                                defaultNode.appendChild(node);
                            }
                        }
                    }

                    // 隐藏空节点
                    for (var i = 0, c = _GROUP_CAST_TREE.nodes.length; i < c; i++) {
                        if (!_GROUP_CAST_TREE.nodes[i]._children.length) {
                            _GROUP_CAST_TREE.nodes[i].hide();
                        }
                    }

                    {{if $email}}
                    var u  = _GROUP_CAST_TREE.find('{{$email}}', true, 'email'),
                        cu = null;
                    if (_CAST_TREE) {
                        cu = _CAST_TREE.find('{{$email}}', true, 'email');
                    }

                    if (u && !cu) {
                        if (u.parent) {
                            u.parent.expand();
                        }

                        $('#group-' +  u.get('id')).click();
                    }
                    {{/if}}
                }
            }
        },
        error: function(res) {
        }
    });
}
function deleteChatLogWin(email)
{
    var chatDelWinTpl = [
        '<div class="pop">',
        '<div class="pop_header"><strong>'+TOP.TEXT.DELETE_LOG_RECORD+'</strong><a class="icon icon_close close"></a></div>',
        '<div class="pop_body">',
        '<div class="screen_lock"><div><span class="icon icon_attention_big"></span><strong>' + TOP.TEXT.DELETE_LOG_RECORD_BY_OTHERID + '</strong></div>',
        '<div style="margin-left:48px"><p><label for="all"><input id="all" name="type" type="radio" value="all" checked="checked" style="margin:0;padding:0;height:13px;width:13px;" /><span style="margin-left:5px;">'+TOP.TEXT.DELETE_ALL+'</span></label></p>',
        '<p><label for="more"><input id="more" name="type" type="radio" value="more" style="margin:0;padding:0;height:13px;width:13px;" /><span style="margin-left:5px;">'+TOP.TEXT.ONLY_DELETE+'</span></label>',
        '<select name="datetime" disabled="disabled" style="margin-left:5px;margin-right:5px;">',
        '<option value="0">'+TOP.TEXT.BEFORE_ONE_DAY+'</option>',
        '<option value="1">'+TOP.TEXT.BEFORE_ONE_WEEK+'</option>',
        '<option value="2">'+TOP.TEXT.BEFORE_ONE_MONTH+'</option>',
        '<option value="3">'+TOP.TEXT.BEFORE_THREE_MONTH+'</option>',
        '</select>'+TOP.TEXT.CHAT_LOG_RECORD+'</p></div></div>',
        '</div>',
        '<div class="pop_footer">',
        '<button type="button" name="delete" class="btn">'+TOP.TEXT.DELETE+'</button>',
        '<button type="button" name="cancel" class="btn">'+TOP.TEXT.CANCEL+'</button>',
        '</div>',
        '</div>'
    ].join('');
    var chatDelWin = TOP.appendWindow('del-log-win', chatDelWinTpl, {
        width: 460,
        draggable: true,
        onShow: function(){
            chatDelWin.find('a.icon_close, button[name="cancel"]').bind('click', function(){
                chatDelWin.close();
                return false;
            });
            chatDelWin.find('input[name="type"]').bind('click', function(e){
                var type = chatDelWin.find('input[name="type"]:checked').val();
                if (type == 'all') {
                    chatDelWin.find('select[name="datetime"]').attr('disabled', true);
                } else {
                    chatDelWin.find('select[name="datetime"]').attr('disabled', false);
                }
                TOP.stopEventBuddle(e);
            });
            chatDelWin.find('select[name="datetime"]').bind('click', function(e){
                TOP.stopEventBuddle(e);
            });
            chatDelWin.find('button[name="delete"]').bind('click', function(){
                if (!confirm(TOP.TEXT.CONFRIM_DELETE_LOG_RECORD)) {
                    return false;
                }

                var type = chatDelWin.find('input[name="type"]:checked').val();
                if (type == 'more') {
                    var datetime = chatDelWin.find('select[name="datetime"] option:selected').val();
                } else {
                    var datetime = 'all';
                }

                chatDelWin.close();

                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: '/chat/delete',
                    data: {fun: 'more', datetime: datetime, otherid: email},
                    success: function(ret) {
                        TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
                        if (ret.success) {
                            //location.reload();
                            var h = TOP.Frame.hash();
                            var email = null;
                            var arr = h.split('&');
                            for (var i = 0, l = arr.length; i < l; i++) {
                                var pair = arr[i].split('=', 2);
                                if (pair.length < 2) {
                                    continue ;
                                }
                                if (pair[0] == 'email') {
                                    email = pair[1];
                                    break;
                                }
                            }

                            if (email) {
                                location = location.pathname + '?email=' + email;
                            } else {
                                location.reload();
                            }
                        }
                    },
                    error: function(res) {
                        TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
                    }
                });
            });
        },
        onClose: function(){
            chatDelWin.destroy();
        }
    });
    chatDelWin.show();
}
function deleteChatLog(logId, email) {
    if (!confirm(TOP.TEXT.CONFRIM_DELETE_LOG_RECORD)) {
        return false;
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/chat/delete',
        data: {logid: logId, fun: 'one', otherid: email},
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
            if (ret.success) {
                //location.reload();
                $('#log-' + logId).remove();
            }
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}
-->
</script>
</body>
</html>
