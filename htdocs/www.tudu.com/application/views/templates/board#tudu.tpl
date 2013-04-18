<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$board.boardname|escape:'html'}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/card.js?1001" type="text/javascript"></script>
<script type="text/javascript">
<!--
var LH = 'm=board&bid={{$board.boardid}}';
if (top == this) {
    location = '/frame#' + LH;
}
-->
</script>

</head>
<body>

{{include file="^boardnav.tpl" showSearch = true}}
<div class="board_subarea">
    <!-- <div class="board_option"><select name="" style="width:160px;"><option>跳转至版块</option></select></div> -->
    {{strip}}
    <p><strong class="f14">{{$board.boardname|escape:'html'}}</strong><!-- &nbsp;&nbsp;(&nbsp;共1250个图度,11345个回复&nbsp;)-->{{if $access.modify}}&nbsp;&nbsp;<a href="/board/modify?bid={{$board.boardid}}">[{{$LANG.modify}}]</a>{{/if}}&nbsp;&nbsp;<a href="javascript:void(0)" id="board-attention">[{{if !$board.isattention}}{{$LANG.add_attention}}{{else}}{{$LANG.remove_attention}}{{/if}}]</a>
    {{if $access.close}}&nbsp;&nbsp;<a href="javascript:void(0);" onclick="closeBoard('{{$board.boardid}}', {{if $board.status != 2}}true{{else}}false{{/if}})">[{{if $board.status != 2}}{{$LANG.close_board}}{{else}}{{$LANG.open_board}}{{/if}}]</a>{{/if}}
    {{*&nbsp;&nbsp;<a href="javascript:void(0);" onclick="clearBoard('{{$board.boardid}}')">[{{$LANG.clear_board}}]</a>*}}
    {{if $access.delete}}&nbsp;&nbsp;<a href="javascript:void(0);" onclick="deleteBoard('{{$board.boardid}}')">[{{$LANG.delete_board}}]</a>{{/if}}
    </p>
    {{/strip}}
    {{if $board.attribute}}
    <p><span class="gray">{{$LANG.attribute}}{{$LANG.cln}}</span>{{foreach name=attribute item=attribute from=$board.attribute}}{{if !$smarty.foreach.attribute.first}}、{{/if}}{{$attribute}}{{/foreach}}</p>
    {{/if}}
    <p>
        {{if $board.moderators}}
        <span class="gray">{{$LANG.moderators}}{{$LANG.cln}}</span>
        {{foreach name=foo item=moderator from=$board.moderators}}{{if !$smarty.foreach.foo.first}},{{/if}}{{$moderator}}{{/foreach}}&nbsp;&nbsp;
        {{/if}}
        {{if $board.groups}}
        <input id="board-groups" type="hidden" value="{{foreach item=item from=$board.groups}}{{$item|cat:"\n"}}{{/foreach}}" />
        <span class="gray">{{$LANG.join_members}}{{$LANG.cln}}</span>
        <span id="groups-name"></span>
        {{/if}}
    </p>
    {{if $board.memo}}
    <div class="fold_box">
    <span href="javascript:void(0);" class="icon icon_fold"></span>
    {{$board.memo|default:"&nbsp;"}}
    </div>
    {{/if}}
</div>

<form action="" method="post" class="panel">
    <div class="panel-body">
    {{if count($classes) > 0}}
    <div class="board-class-list">
        <a href="/board/?bid={{$board.boardid}}"{{if !$pageinfo.query.cid}} class="class-current"{{/if}}>全部</a>
        {{foreach item=class from=$classes}}
        <a href="/board/?bid={{$board.boardid}}&cid={{$class.classid}}"{{if $pageinfo.query.cid == $class.classid}} class="class-current"{{/if}}>{{$class.classname}}</a>
        {{/foreach}}
    </div>
    {{/if}}
    <div id="float-toolbar" class="float-toolbar">
    {{include file="board#index^toolbar_tudu.tpl"}}
    <table cellspacing="0" class="grid_thead">
      <tr>
        <td width="30" align="center" style="padding-left:0;"><input name="checkall" type="checkbox" value="{{$tudu.tuduid}}"></td>
        <td width="20">&nbsp;</td>
        <td class="title_line" align="center" width="40"><div style="padding-left:4px"><span class="mailtitle"></span></div></td>
        <td class="title_line"><div class="space">{{$LANG.subject}}</div></td>
        <td width="100" class="title_line"><div class="space">{{$LANG.sender}}</div></td>
        <td width="100" class="title_line"><div class="space">{{$LANG.receiver}}</div></td>
        <td width="110" class="title_line"><div class="space">{{$LANG.endtime}}</div></td>
        <td width="90" class="title_line"><div class="space">{{$LANG.tudu_percent}}</div></td>
        <td width="100" class="title_line" style="padding-left:0"><div class="space">{{$LANG.lastpost}}</div></td>
      </tr>
    </table>
    </div>
    <div id="toolbar">{{include file="board#index^toolbar_tudu.tpl"}}</div>
    {{include file="board#tudu^list.tpl"}}
    </div>
</form>

<div class="pop_wrap" id="move-tudu-win" style="display:none;position:absolute;background:#ebf4d8;">
<form id="move-tudu" action="/tudu-mgr/move" method="post" enctype="multipart/form-data">
    <div class="pop">
        <div class="pop_header"><strong></strong><a class="icon icon_close close"></a></div>
        <div class="pop_body" id="reply-edit">
            <input type="hidden" name="fbid" value="{{$board.boardid}}" />
            <table cellspacing="2" cellpadding="3" border="0">
                <tr>
                    <td>{{$LANG.move_to}}{{$LANG.cln}}</td>
                </tr>
                <tr>
                    <td>
                    <select name="bid" style="width:195px">
                        <option value="">{{$LANG.select_board}}</option>
                        {{foreach from=$boards item=item}}
                        {{if $item.type == 'zone' && $item.children}}
                        <optgroup label="{{$item.boardname|escape:'html'}}">
                            {{foreach from=$item.children item=child}}
                            <option value="{{$child.boardid}}"{{if $tudu.boardid == $child.boardid}} selected="selected"{{/if}}{{if $child.isclassify}} _classify="1"{{/if}}>{{$child.boardname|escape:'html'}}</option>
                            {{/foreach}}
                        </optgroup>
                        {{/if}}
                        {{/foreach}}
                    </select>
                    </td>
                </tr>
                <tr id="toclass" style="display:none">
                    <td>
                    <select name="cid" style="width:195px">
                        <option value="">{{$LANG.select_class}}</option>
                    </select>
                    </td>
                </tr>
            </table>
        </div>

        <div class="pop_footer">
        <div>
        <button name="confirm" type="submit" class="btn">{{$LANG.confirm}}</button><button type="button" class="btn close">{{$LANG.cancel}}</button>
        </div>
        </div>
    </div>
</form>
</div>
<script type="text/javascript">
<!--

var _LABEL_COUNT = {
    {{foreach item=item from=$labels name=label}}'{{$item.labelalias}}' : {{$item.unreadnum}}{{if $smarty.foreach.label.index + 1 < count($labels)}},{{/if}}{{/foreach}}
};

TOP.Frame.title('{{$board.boardname|escape:'html'}}');
TOP.Label.focusLabel('board');
TOP.Frame.hash(LH);

$('input[name="checkall"]').click(function(){
    TOP.checkBoxAll('tid[]', this.checked, document.body);
});

{{if $access.deletetudu}}
$('button[name="delete"]').click(function(){
    deleteTudu();
});
{{/if}}

$('button[name="send"]').click(function(){
    location = '/tudu/modify?bid={{$board.boardid}}';
});

$("table.grid_list_2").mouseover(function(){
    $(this).addClass("over")
}).mouseout(function(){
    $(this).removeClass("over")
}).each(function(){
    var o = $(this);
    var tuduId = o.attr('id').replace('tudu-', '');

    if (o.attr('privacy')) {
        return ;
    }

    o.find('td.lastupdate').click(function(){
        location = '/tudu/view?tid=' + o.attr('id').replace('tudu-', '') + '&page=last&back={{$smarty.server.REQUEST_URI|escape:'url'}}';
    });

    o.find('a.icon_attention').bind('click', function(){
        var star = $(this),
        isstar = star.hasClass('attention');

        star.toggleClass('attention');

        var func = isstar ? 'unstar' : 'star';
        return starTudu(tuduId, func);
    });
});

var card = new Card();
$('table.g_in_table td.sender').children('.space').children().each(function(){
    $(this).bind('mouseover', function(){
        card.show(this, 500);
    })
    .bind('mouseout', function(){
        card.hide();
    });
});
$('table.g_in_table td.accepter').children('.space').children().children().each(function(){
    $(this).bind('mouseover', function(){
        card.show(this, 500);
    })
    .bind('mouseout', function(){
        card.hide();
    });
});

new FixToolbar({
    src: '#toolbar',
    target: '#float-toolbar'
});

$(".icon_fold").click(function(){
    $(this).toggleClass("icon_unfold");
    $(".fold_box").toggleClass("unfold_box")
});

{{if $board.groups}}
/**
 * 处理板块参与人名称
 */
window.onload = function() {
    setGroups();
}
function setGroups() {
    TOP.Cast.load(function(){
        var avaliable = $('#board-groups').val().split("\n"),
            users = TOP.Cast.get('users'),
            groups = TOP.Cast.get('groups'),
            names = [],
            titles = [],
            full = false;

        for (var j = 0, c = avaliable.length; j < c; j++) {
            for (var i = 0, ul = users.length; i < ul; i++) {
                if (typeof avaliable[j] == 'undefined' || -1 === avaliable[j].indexOf('@')) {
                    continue ;
                }

                if (avaliable[j] == users[i].username) {
                    titles.push('<'+users[i].username+'>'+users[i].truename);
                    if (full) {
                        break;
                    }
                    if (names.length > 6) {
                        names.push('...');
                        full = true;
                        break;
                    }
                    names.push(users[i].truename);
                }
            }
            for (var i = 0, gl = groups.length; i < gl; i++) {
                if (typeof avaliable[j] == 'undefined' || -1 !== avaliable[j].indexOf('@')) {
                    continue ;
                }

                if (avaliable[j] == groups[i].groupid) {
                    titles.push(groups[i].groupname+'<'+TOP.TEXT.GROUP+'>');
                    if (full) {
                        break;
                    }
                    if (names.length > 6) {
                        names.push('...');
                        full = true;
                        break;
                    }
                    names.push(groups[i].groupname);
                }
            }
        }

        if (names.length > 0 && titles.length) {
            $('#groups-name').attr('title', titles.join(','));
            $('#groups-name').text(names.join(','));
        } else {
            $('#groups-name').text('-');
        }
    });
}
{{/if}}
function starTudu(tuduId, fun) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/star',
        data: {tid: tuduId, fun: fun},
        success: function(ret) {
            if (ret.data && ret.data) {
                TOP.Label.setLabels(ret.data).refreshMenu();
            }
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}

function deleteTudu(tuduId) {
    if (!tuduId) {
        tuduId = getSelectId();
    }

    if (!tuduId.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    if (!confirm(TOP.TEXT.CONFIRM_DELETE_TUDU)) {
        return ;
    }

    $('#checkall').attr('checked', false);

    TOP.showMessage(TOP.TEXT.DELETING_TUDU, 0, 'success');
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/delete',
        data: {tid: tuduId},
        success: function(ret) {
            TOP.showMessage(ret.message, 10000, 'success');

            if (ret.success) {
                location.reload();
            }
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}

$('#board-attention').bind('click', function(){attentionBoard('{{$board.boardid}}', {{if !$board.isattention}}'add'{{else}}'remove'{{/if}})});
function attentionBoard(boardId, type) {
    if (!boardId) {
        return ;
    }

    if (type != 'remove') {
        type = 'add';
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {bid: boardId, type: type},
        url: '/board/attention',
        success: function(ret) {
            TOP.showMessage(ret.message, 10000, 'success');

            if (!ret.success) {
                return ;
            }

            var text = '',
                _$   = TOP.getJQ();

            if (type == 'add') {
                type = 'remove';
                var text = '[{{$LANG.remove_attention}}]';
                var boardName = '{{$board.boardname|escape:'html'|truncate:12}}';
                TOP.Frame.Boards.append(boardId, boardName);
            } else {
                type = 'add';
                var text = '[{{$LANG.add_attention}}]';
                TOP.Frame.Boards.remove(boardId);
            }

            $('#board-attention')
            .text(text)
            .unbind('click')
            .bind('click', function(){attentionBoard(boardId, type)});
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}

function deleteBoard(boardId) {
    if (!confirm(TOP.TEXT.CONFIRM_DELETE_BOARD)) {
        return ;
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {bid: boardId},
        url: '/board/delete',
        success: function(ret){
            TOP.showMessage(ret.message, 10000, 'success');
            if (ret.success) {
                TOP.getJQ()('#b_' + boardId).remove();
                location = '/board/';
            }
        },
        error: function(res){
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}

{{if 0}}
function clearBoard(boardId) {
    if (!confirm(TOP.TEXT.CONFIRM_CLEAR_BOARD)) {
        return ;
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {bid: boardId},
        url: '/board/clear',
        success: function(ret){
            TOP.showMessage(ret.message, 10000, 'success');
            if (ret.success) {
                location.reload();
            }
        },
        error: function(res){
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}
{{/if}}

function closeBoard(boardId, isClose) {
    if (isClose) {
        if (!confirm(TOP.TEXT.CONFIRM_CLOSE_BOARD)) {
            return ;
        }
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {bid: boardId, isclose: isClose ? 1 : 0},
        url: '/board/close',
        success: function(ret){
            TOP.showMessage(ret.message, 10000, 'success');
            if (ret.success) {
                location.reload();
            }
        },
        error: function(res){
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}

function getSelectId() {
    var ret = [];
    $(':checkbox[name="tid[]"]:checked').each(function(){
        ret.push(this.value);
    });

    return ret;
}

{{if $access.movetudu}}
$('button[name="move"]').bind('click', function(){
    var tuduIds = getSelectId();

    if (tuduIds.length <= 0) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    moveBoard(tuduIds.join(','));
});

var _CLASSES = {};
function moveBoard(tuduids) {
    var Win = TOP.Frame.TempWindow;
    Win.append($('#move-tudu-win').html(), {
        id: 'move-tudu-win',
        width: 250,
        onClose: function(){
          Win.destroy();
        }
    });

    Win.find('#move-tudu').submit(function(){return false;});
    Win.find('#move-tudu').submit(function(){
        var form = $(this);

        if (Win.find('select[name="bid"] option:selected').attr('_classify') == '1'
            && Win.find('select[name="cid"]').size() > 0
            && !Win.find('select[name="cid"]').val())
        {
            Win.find('select[name="cid"]').focus();
            return TOP.showMessage(TOP.TEXT.BOARD_MUST_CLASSIFY);
        }

        var data = {
            'fbid': '{{$board.boardid}}',
            'bid': Win.find('select[name="bid"]').val(),
            'cid': Win.find('select[name="cid"]').val(),
            'tid': tuduids
        };

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret){
                TOP.showMessage(ret.message, 5000, ret.success ? 'success': null);

                if (ret.success) {
                    Win.close();
                    location.reload();
                }
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
            }
        });
    });

    Win.find('select[name="bid"]').bind('change', function(){
        var bid = this.value,
            select = $(this),
            classify = select.find('option:selected').attr('_classify');
        if (undefined == _CLASSES[bid]) {
            $.ajax({
                type: 'GET',
                dataType: 'json',
                url: '/tudu/classes?bid=' + bid,
                success: function(ret){
                    if (ret.success) {
                        _CLASSES[bid] = ret.data;

                        if (!classify && _CLASSES[bid].length) {
                            _CLASSES[bid] = [{
                                classid: '',
                                classname: '{{$LANG.not_specify}}'
                            }].concat(_CLASSES[bid]);
                        }

                        _fillSelect(_CLASSES[bid]);
                    }
                },
                error: function(res){
                    Win.find('select[name="cid"]').empty();
                    Win.find('#toclass').hide();
                    return ;
                }
            });
        } else {
            _fillSelect(_CLASSES[bid]);
        }
    });

    Win.show();

    function _fillSelect(ret) {
        var o = Win.find('select[name="cid"]'),
            p = Win.find('#toclass');
        o.find('option:not(:eq(0))').remove();

        if (null === ret || !ret.length) {
            p.hide();
            return o.attr('disabled', true);
        }

        p.show();
        for (var i = 0, c = ret.length; i < c; i++) {
            o.append('<option value="'+ret[i].classid+'" title="'+ret[i].classname+'">'+ret[i].classname+'</option>');
        }

        o.attr('disabled', false);
    }
}
{{/if}}
-->
</script>
</body>
</html>
