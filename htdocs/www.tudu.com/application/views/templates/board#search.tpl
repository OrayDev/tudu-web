<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.board_search}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
</head>
<body>

<div>
<div class="position" style="float:left;width:250px;">
    <p><strong class="title">{{$LANG.search_result}}</strong> ({{$LANG.label_total|sprintf:$pageinfo.recordcount}}) | <a href="/board/search-form">{{$LANG.re_search}}</a></p>
</div>
<div style="margin-left:250px;clear:right">
    <form method="get" action="/board/search">
    {{foreach from=$params.bid item=bid}}
    <input type="hidden" name="bid[]" value="{{$bid}}" />
    {{/foreach}}
    <table cellpadding="2" cellspacing="2" border="0" align="right">
        <tr>
            <td>{{$LANG.sender}}{{$LANG.cln}}<input class="input_text" name="from" type="text" value="{{$params.from}}" style="width:100px" /></td>
            <td>{{$LANG.receiver}}{{$LANG.cln}}<input class="input_text" name="to" type="text" value="{{$params.to}}" style="width:100px" /></td>
            <td><select name="status" style="width:100px">
            <option value=""{{if !isset($params.status)}} selected="selected"{{/if}}>{{$LANG.status}}</option>
            <option value="0"{{if $params.status === 0}} selected="selected"{{/if}}>{{$LANG.tudu_status_0}}</option>
            <option value="1"{{if $params.status == 1}} selected="selected"{{/if}}>{{$LANG.tudu_status_1}}</option>
            <option value="2"{{if $params.status == 2}} selected="selected"{{/if}}>{{$LANG.tudu_status_2}}</option>
            <option value="3"{{if $params.status == 3}} selected="selected"{{/if}}>{{$LANG.tudu_status_3}}</option>
            <option value="4"{{if $params.status == 4}} selected="selected"{{/if}}>{{$LANG.tudu_status_4}}</option>
            </select></td>
            <td><input type="text" id="keyword" name="keyword" class="input_text" title="{{$LANG.subject_keyword}}" value="{{$params.keyword}}" style="width:120px" /></td>
            <td><button type="submit">{{$LANG.search}}</button></td>
        </tr>
    </table>
    </form>
</div>
<div class="clear"></div>
</div>

<form action="" method="post" class="panel">
    <div class="panel-body">
    <div id="float-toolbar" class="float-toolbar">
    {{include file="board#search^toolbar_tudu.tpl"}}
    <table cellspacing="0" class="grid_thead">
      <tr>
        <td width="30" align="center" style="padding-left:0;"><input name="checkall" type="checkbox" value="{{$tudu.tuduid}}"></td>
        <td class="title_line" align="center" width="40"><div style="padding-left:4px"><span class="mailtitle"></span></div></td>
        <td class="title_line"><div class="space">{{$LANG.subject}}</div></td>
        <td width="100" class="title_line"><div class="space">{{$LANG.sender}}</div></td>
        <td width="100" class="title_line"><div class="space">{{$LANG.receiver}}</div></td>
        <td width="110" class="title_line"><div class="space">{{$LANG.endtime}}</div></td>
        <td width="90" class="title_line"><div class="space">{{$LANG.column_reply}}</div></td>
        <td width="100" class="title_line"><div class="space">{{$LANG.lastpost}}</div></td>
      </tr>
    </table>
    </div>
	<div id="toolbar">{{include file="board#search^toolbar_tudu.tpl"}}</div>
    {{include file="board#tudu^list.tpl"}}
    {{include file="board#search^toolbar_tudu.tpl"}}
    </div>
</form>

<script type="text/javascript">
<!--
var _LABEL_COUNT = {
    {{foreach item=item from=$labels name=label}}'{{$item.labelalias}}' : {{$item.unreadnum}}{{if $smarty.foreach.label.index + 1 < count($labels)}},{{/if}}{{/foreach}}
};

TOP.Frame.title('{{$LANG.board_search}}');
TOP.Label.focusLabel('board');
TOP.Frame.hash('m=boardsearch&{{$query}}');

$('input[name="checkall"]').click(function(){
	TOP.checkBoxAll('tid[]', this.checked, document.body);
});

{{if $access.delete}}
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
    if (o.attr('privacy')) {
        return ;
    }

    o.find('td.lastupdate').click(function(){
        location = '/tudu/view?tid=' + o.attr('id').replace('tudu-', '') + '&page=last&back={{$smarty.server.REQUEST_URI|escape:'url'}}';
    });
});

new FixToolbar({
	src: '#toolbar',
	target: '#float-toolbar'
});

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

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/delete',
        data: {tid: tuduId},
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, 'success');

            if (ret.success) {
                location.reload();
            }
        },
        error: function(res) {
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
-->
</script>
</body>
</html>
