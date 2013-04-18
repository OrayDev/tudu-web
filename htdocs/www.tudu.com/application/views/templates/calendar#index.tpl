<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>{{$LANG.calendar}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script type="text/javascript">
var LH = 'm=calendar&type={{$type}}&sd={{$startdate}}{{if $params.unid}}&unid={{$params.unid}}{{/if}}{{if $params.date}}&date={{$params.date}}{{/if}}';
if (top == this) {
    location = '/frame#' + LH;
}
var TOP = getTop();
</script>
</head>

<body>
<div class="position">
  <p><strong class="title">{{$LANG.calendar}}</strong><a href="javascript:void(0)" onclick="Calendar.showWindow();">[{{$LANG.select_query_object}}]</a><a href="/calendar/export?type={{$type}}&sd={{$startdate}}&unid={{$params.unid}}">[{{$LANG.caledndar_download}}]</a>
  {{if $isquery}}
  ({{$LANG.search_item}} <strong id="search-object"></strong> {{$LANG.record_num}}<span id="total"></span>{{$LANG.tudu_unit}})
  {{/if}}
  </p>
</div>

<div class="panel-body">
  <div id="float-toolbar" class="float-toolbar">
  {{include file="calendar^toolbar.tpl"}}
  <table cellspacing="0" class="grid_thead">
      <tr>
        <td style="line-height:20px;"><div class="space"><!--a href="javascript:void(0);" onClick="submitSort('?{{$query}}', 1,{{$sort[1]}});return false;"-->{{$LANG.subject}}{{if $sort[0]==1}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}<!--/a--></div></td>
        <td width="90" class="title_line" style="line-height:20px;"><div class="space">{{$LANG.percent}}</div></td>
        <td width="650" style="line-height:20px;padding:0">
        <table cellspacing="0" cellpadding="0" border="0" class="gantt_header" width="100%" height="22">
        <tr>
        {{foreach item=item from=$headers name=header}}
        {{if $type == 'week'}}
        {{assign var=weekday value='D'|date:$item|strtolower}}
        {{assign var=weekkey value='date_'|cat:$weekday}}
        <td width="{{$tdwidth}}">{{$LANG[$weekkey]}}({{$item|date_format:'%m-%d'}})</td>
        {{else}}
        {{if ($smarty.foreach.header.index + 1) % 5 == 0 || $smarty.foreach.header.index == 0}}
        <td width="{{$tdwidth}}" style="overflow:hidden;"><div>{{$item|date_format:'%d'}}</div></td>
        {{else}}
        <td width="{{$tdwidth}}" class="half_border"><div>&nbsp;&nbsp;&nbsp;</div></td>
        {{/if}}
        {{/if}}
        {{/foreach}}
        </tr>
        </table>
        </td>
      </tr>
  </table>
  </div>
  <div id="toolbar">{{include file="calendar^toolbar.tpl"}}</div>
  {{include file="calendar^list.tpl"}}
</div>

<div class="pop_wrap pop_edit" id="cast-win-src" style="display:none;position:absolute;background:#ebf4d8;width:470px">
    <div id="castwin" class="pop">
    <form id="queryform" method="get" action="/calendar/" target="main">
    <div class="pop_header"><strong>{{$LANG.select_query_object}}</strong><a class="icon icon_close close"></a></div>
    <div class="pop_body" style="padding: 10px">
        <div class="calendar_cast_tree_panel">
        </div>
    </div>
    <div class="pop_footer"><button type="submit" class="btn" >{{$LANG.confirm}}</button><button type="button" class="btn close">{{$LANG.cancel}}</button></div>
    </div>
    </form>
</div>

<script type="text/javascript">
var Calendar = {

    _castTree: null,

    _win: null,

    _data: {},

    searchWin: function() {
        var _o = this;

        if (null !== _o._win) {
            return ;
        }

        _o._win = TOP.Frame.TempWindow;
        _o._win.append($('#cast-win-src').html(), {
            width: 295,
            draggable: true,
            onShow: function() {
                _o.initCastTree();
            },
            onClose: function() {
                _o._castTree.clear();
                _o._castTree = null;
                _o._win.destroy();
                _o._win = null;
            }
        });

        _o._win.find('#queryform').submit(function(){return false;});
        _o._win.find('#queryform').submit(function(){
            var unid = [], url;
            _o._win.find('input[name="unid[]"]').each(function(){
                if (this.value) {
                    unid.push(this.value);
                }
            });

            if (unid.length >= 10) {
                if (!confirm(TOP.TEXT.CONFIRM_SEARCH_CALENDAR)) {
                    return false;
                }
            }

            var url = '/calendar/?type=week&unid=' + unid.join(',');;
            if (TOP.Cookie.get('CALENDAR') == 'MONTH') {
                url='/calendar/?type=month&unid=' + unid.join(',');
            }

            TOP.getJQ()('#mainframe')[0].contentWindow.location = url;

            _o._win.close();
        });
    },

    showWindow: function() {
        if (null === this._win) {
            this.searchWin();
        }
        this._win.show();
    },

    getCast: function() {
        var _o = this;
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: '/calendar/cast',
            success: function(ret) {
                if (ret.success) {
                    if (ret.data && ret.data.users && ret.data.depts) {
                        _o._data = {
                            users: ret.data.users,
                            depts: ret.data.depts
                        };

                        _o.initCastTree();
                    }
                }
            },
            error: function(res) {
            }
        });
    },

    get: function(key) {
        if (undefined == key) {
            return this._data;
        }

        if (null == this._data || typeof(this._data[key]) == 'undefined') {
            return false;
        }

        return this._data[key];
    },

    initCastTree: function() {
        var _o = this,
            depts = Calendar.get('depts'),
            users = Calendar.get('users'),
            _$ = TOP.getJQ();

        if (_$('#cast-tree').size()) {
            return ;
        }

        _o._castTree = new _$.tree({
            id: 'cast-tree',
            idKey: 'id',
            idPrefix: 'cast-',
            cls: 'cast-tree'
        });


        // 填充部门
        for (var i = 0, c = depts.length; i < c; i++) {
            var deptname = depts[i].deptid == '^root' ? TOP._ORGNAME : depts[i].deptname;

            var node = new _$.treenode({
                data: {
                    id: 'd-' + depts[i].deptid.replace('^', '_'),
                    deptid: depts[i].deptid,
                    name: deptname,
                    parentid: depts[i].parentid
                },
                isLeaf: false,
                content: '<input type="checkbox" name="deptid[]" value="{deptid}" />{name}',
                events: {
                    click: function(e){_o._castTree.find(this.id.replace('cast-', ''), true).toggle();TOP.stopEventBuddle(e);}
                }
            });

            if (!depts[i].parentid) {
                depts[i].parentid = '^root';
            }

            if (depts[i].parentid) {
                var parent = _o._castTree.find('d-' + depts[i].parentid.replace('^', '_'), true);
                if (parent) {
                    parent.appendChild(node);
                } else {
                    _o._castTree.appendNode(node);
                }
            } else {
                _o._castTree.appendNode(node);
            }

            var checkbox = new _$.checkbox({
                name: 'deptid[]',
                id: 'dept-' + depts[i].deptid.replace('^', '_'),
                replace: node.ele.find(':checkbox[name="deptid[]"]'),
                states: {
                    normal: {
                        value: '',
                        cls: ''
                    },
                    half: {
                        value: '',
                        cls: 'checkbox-half'
                    },
                    checked: {
                        value: depts[i].deptid,
                        cls: 'checkbox-checked'
                    }
                }
            });
            checkbox
            .bind('click', function(e){
                TOP.stopEventBuddle(e);
                if (this.state() === 'half') {
                    this.state('checked');
                }
            });
        }

        for (var i = 0, c = users.length; i < c; i++) {
            var deptid = users[i].deptid ? users[i].deptid : '_root';
            var node = new _$.treenode({
                data: {
                    id: 'u-' + users[i].userid,
                    uniqueid: users[i].uniqueid,
                    name: users[i].truename,
                    deptid: deptid
                },
                content: '<input type="checkbox" name="unid[]" value="{uniqueid}" deptid="{deptid}" />{name}',
                isLeaf: true,
                events: {click: function(e){TOP.stopEventBuddle(e);}}
            });

            var dept = _o._castTree.find('d-' + deptid, true);
            if (dept) {
                dept.appendChild(node);

                var checkbox = new _$.checkbox({
                    name: 'unid[]',
                    id: 'user-' + users[i].userid,
                    replace: node.ele.find(':checkbox[name="unid[]"]'),
                    states: {
                        normal: {
                            value: '',
                            cls: ''
                        },
                        half: {
                            value: '',
                            cls: 'checkbox-half'
                        },
                        checked: {
                            value: users[i].uniqueid,
                            cls: 'checkbox-checked'
                        }
                    }
                });
                checkbox
                .bind('click', function(e){
                    TOP.stopEventBuddle(e);
                    if (this.state() == 'half') {
                        this.state('checked');
                    }
                });
            }
        }

        var root = _o._castTree.find('d-_root', true);
        if (root) {
            root.expand();
        }

        if (_o._castTree.nodes.length) {
            _o._win.find('div.calendar_cast_tree_panel').empty();
            _o._castTree.appendTo(_o._win.find('div.calendar_cast_tree_panel'));
        } else if (_o._win) {
            _o._win.find('div.calendar_cast_tree_panel').html('<div style="padding:20px 0;text-align:center">{{$LANG.nothing_to_query}}</div>');
        }

        var deptChecks = TOP.getCheckbox('name', 'deptid[]', _$('#cast-tree'));
        var userChecks = TOP.getCheckbox('name', 'unid[]', _$('#cast-tree'));

        deptChecks.bind('click', function() {
            var el = _$('#cast-d-' + this.id.replace('dept-', ''));
            _checkAll(el.find('ul'), this.state());

            var node = _o._castTree.find('d-' + this.id.replace('dept-', ''), true);

            if (node.get('parentid')) {
                _checkParent(node.get('parentid'));
            }

            var ct = node.get('parentid') ? _$('#cast-d-' + node.get('parentid')) : _$('#cast-tree');

            if (_checkIsAll($('#cast-tree'))) {
            	TOP.getCheckbox('id', 'dept-_root').state('checked');
            } else {
            	TOP.getCheckbox('id', 'dept-_root').state('half');
            }
        });

        userChecks.bind('click', function() {
            var uid = this.id.replace('user-', '');
            var node = _o._castTree.find('u-' + uid, true);

            if (node.get('deptid')) {
                _checkParent(node.get('deptid'));
            }

            if (_checkIsAll($('#cast-tree'))) {
            	TOP.getCheckbox('id', 'dept-_root').state('checked');
            } else {
            	TOP.getCheckbox('id', 'dept-_root').state('half');
            }
        });

        function _checkParent(deptid) {
            deptid = deptid.replace('^', '_');
            var node = _o._castTree.find('d-' + deptid, true),
                a = TOP.getCheckbox('id', 'dept-' + deptid, _$('#cast-tree')),
                ct = _$('#cast-d-' + deptid + ' ul'),
                st;

            if (_checkIsAll(ct)) {
                st = 'checked';
            } else if (ct.find('div.checkbox-checked').size()) {
                st = 'half';
            } else {
                st = 'normal';
            }

            a.state(st);

            if (node.get('parentid')) {
                _checkParent(node.get('parentid'));
            }
        }

        function _checkIsAll(ct) {
            var depts = getCheckbox('name', 'deptid[]', ct),
                users = getCheckbox('name', 'unid[]', ct),
                isall = true;

            depts.each(function(){
                if (this.state() !== 'checked') {
                    isall = false;
                    return ;
                }
            });

            if (isall) {
                users.each(function(){
                    if (this.state() !== 'checked') {
                        isall = false;
                        return ;
                    }
                });
            }

            return isall;
        }

        function _checkAll(ct, state) {
        	TOP.getCheckbox('name', 'deptid[]', ct).state(state);
        	TOP.getCheckbox('name', 'unid[]', ct).state(state);
        }
    }
};
Calendar.Tips = {
    html: '<div class="float_tips"><div class="float_tips_body"></div></div>',

    ele: null,

    show: function(content, left, top) {
        if (null === this.ele) {
            this.ele = $(this.html);
            this.ele.appendTo(document.body).hide();
        }

        this.ele.find('.float_tips_body').html(content);
        this.ele.show();

        var width = this.ele.width(),
            bodyWidth = $(document.body).width(),
            pos = {
                left : left ? left + 20 : 0,
                top : top ? top + 10 : 0
            };


        if (width + left > bodyWidth) {
            pos.left = bodyWidth - width - 25;
        }

        this.ele.css({
            left : pos.left + 'px',
            top : pos.top + 'px'
        });
     },

    hide: function() {
        if (this.ele) {
            this.ele.find('.float_tips_body').empty();
            this.ele.hide();
        }
    }
};

Calendar.toggleCal = function() {
    var panel = $('.panel-calendar');
    if ($('.panel-calendar:visible').size()) {
        panel.hide();
    } else {
        panel.show();
    }
};

$(function(){
    TOP.Frame.title('{{$LANG.calendar}}');
    TOP.Label.focusLabel('calendar');
    TOP.Frame.hash(LH);

    $('button[name="week"]').click(function(){
        TOP.Cookie.set('CALENDAR' ,'WEEK');
    });
    $('button[name="month"]').click(function(){
        TOP.Cookie.set('CALENDAR' ,'MONTH');
    });
    $('#tudu-list table.grid_list_2').each(function(){
        var o = $(this), tuduid = o.attr('_tuduid');

        o.mouseover(function(e){
            o.addClass('over');

            var subject   = o.find('a[name="subject"]').attr('_title'),
                startTime = o.attr('_st'),
                endTime   = o.attr('_et');

            o.find('a[name="subject"]:eq(0),div.gantt_bar:eq(0),div.gantt_bar:eq(1)').mousemove(function(e){
                var scrollTop = document.documentElement ? document.documentElement.scrollTop : document.body.scrollTop;

                var html = '<p>'+subject+'</p><p>{{$LANG.time}}{{$LANG.cln}}'+startTime+' - '+endTime+'</p>';

                if (o.attr('_previd')) {
                    var p = o.parent().find('table.grid_list_2[_tuduid="'+o.attr('_previd')+'"]');
                    html += '<p>{{$LANG.prev_tudu}}{{$LANG.cln}}'+p.find('a[name="subject"]').attr('_title')+'</p>';
                }

                Calendar.Tips.show(html, e.clientX, e.clientY + scrollTop);
            })
            .mouseout(function(){
                Calendar.Tips.hide();
            });
        })
        .mouseout(function(){
            o.removeClass('over');
        });

        initCollspan(o);
    });

    var searches = [], unids = [];
    $('div.grid_list_title').each(function(){
        var o = $(this);
        o.find('span.toggle_tudu').click(function(){
            var o = $(this),
                wrap = o.parents('div.grid_list_group'),
                expanded = o.hasClass('icon_elbow_minus');

            wrap.find('div.grid_list_group_ct').toggle();
            if (expanded) {
                o.removeClass('icon_elbow_minus').addClass('icon_elbow_plus');
            } else {
                o.removeClass('icon_elbow_plus').addClass('icon_elbow_minus');
            }
        });

        unids.push(o.find('h3').text());
    });

    TOP.Cast.load(function(){
        var users = TOP.Cast.get('users');
        for (var idx = 0, l = unids.length; idx < l ;idx++) {
            for (var i = 0, c = users.length; i < c; i++) {
                if (users[i].uniqueid == unids[idx]) {
                    $('#tudu-group-' + users[i].uniqueid).find('h3').text(users[i].truename);
                    searches.push(users[i].truename);
                }
            }
        }

        $('#search-object').text(searches.join(';'));
        $('#total').text($('#tudu-list table.grid_list_2').size());
    });

    new FixToolbar({
        src: '#toolbar',
        target: '#float-toolbar'
    });
    
    Calendar.getCast();
    Calendar.searchWin();
});

function initCollspan(o) {
    var treeicon = o.find('.tree-ec-icon'),
        tuduid   = o.attr('_tuduid'),
        chct     = o.next('div.gantt_children_list');
    treeicon.click(function(){
        var me = $(this);

        if (me.hasClass('tree-elbow-minus')) {
            me.removeClass('tree-elbow-minus');

            chct.hide();
        } else {
            me.addClass('tree-elbow-minus');

            if (!chct.find('.grid_list_2').size()) {
                if (tuduid.indexOf('ch-') != -1) {
                    var tid = tuduid.replace('ch-', '');
                } else {
                    var tid = tuduid;
                }
                chct.load('/calendar/children?currUrl={{$smarty.server.REQUEST_URI|escape:'url'}}&type={{$type}}&sd={{$startdate}}&ed={{$enddate}}&tid=' + tid, function(){
                    chct.find('.grid_list_2').each(function(){
                        var child = $(this), id = child.attr('_tuduid');
                        child.mouseover(function(e){
                            child.addClass('over');

                            var subject   = child.find('a[name="subject"]').attr('_title'),
                                startTime = child.attr('_st'),
                                endTime   = child.attr('_et');

                            child.find('a[name="subject"]:eq(0),div.gantt_bar:eq(0)').mousemove(function(e){
                                var scrollTop = document.documentElement ? document.documentElement.scrollTop : document.body.scrollTop;

                                var html = '<p>'+subject+'</p><p>{{$LANG.time}}{{$LANG.cln}}'+startTime+' - '+endTime+'</p>';

                                if (child.attr('_previd')) {
                                    var p = child.parent().find('table.grid_list_2[_tuduid="'+child.attr('_previd')+'"]');
                                    html += '<p>{{$LANG.prev_tudu}}{{$LANG.cln}}'+p.find('a[name="subject"]').attr('_title')+'</p>';
                                }

                                Calendar.Tips.show(html, e.clientX, e.clientY + scrollTop);
                            })
                            .mouseout(function(){
                                Calendar.Tips.hide();
                            });
                        })
                        .mouseout(function(){
                            child.removeClass('over');
                        });

                        initCollspan(child);
                    });
                });
            }

            chct.show();
        }
    });
}
</script>
</body>
</html>