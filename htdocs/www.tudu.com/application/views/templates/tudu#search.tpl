<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$label.displayname}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script type="text/javascript">
var TOP = getTop();
</script>

</head>
<body>
<div class="position">
    <p><strong class="title">{{$LANG.search_result}}</strong> ({{$LANG.label_total|sprintf:$pageinfo.recordcount}}){{if !$coreseek}}<span class="title">|</span><a href="javascript:void(0)" id="adv_search">重新搜索</a>{{/if}}</p>
</div>

{{if !$coreseek}}
<div style="float:left;line-height:28px;margin:0 5px 3px;">
<form method="get" action="/tudu/">
<input type="hidden" name="search" value="query" />
<input type="hidden" name="unread" value="{{$params.unread}}" />

        {{$LANG.sender}}{{$LANG.cln}}<input type="text" class="input_text" style="width:100px;" name="from" id="inputfrom" value="{{$params.from}}" />
        {{$LANG.receiver}}{{$LANG.cln}}<input type="text" class="input_text" style="width:100px;" name="to" id="inputto" value="{{$params.to}}" />
        <select name="status" style="width:100px">
        <option value=""{{if !isset($params.status)}} selected="selected"{{/if}}>{{$LANG.status}}</option>
        <option value="0"{{if $params.status === 0}} selected="selected"{{/if}}>{{$LANG.tudu_status_0}}</option>
        <option value="1"{{if $params.status === 1}} selected="selected"{{/if}}>{{$LANG.tudu_status_1}}</option>
        <option value="2"{{if $params.status === 2}} selected="selected"{{/if}}>{{$LANG.tudu_status_2}}</option>
        <option value="3"{{if $params.status === 3}} selected="selected"{{/if}}>{{$LANG.tudu_status_3}}</option>
        <option value="4"{{if $params.status === 4}} selected="selected"{{/if}}>{{$LANG.tudu_status_4}}</option>
        </select>
        <select name="bid" style="width:150px">
        <option value=""{{if !isset($params.bid)}} selected="selected"{{/if}}>{{$LANG.belong_board}}</option>
        {{foreach from=$boards item=board}}
        {{if $board.type == 'zone' && $board.children}}
        <optgroup label="{{$board.boardname}}">
            {{foreach from=$board.children item=item}}
            <option value="{{$item.boardid}}"{{if $params.bid == $item.boardid}} selected="selected"{{/if}}>{{$item.boardname}}</option>
            {{/foreach}}
        </optgroup>
        {{/if}}
        {{/foreach}}
        </select>
        <select name="cat" style="width:120px">
        {{foreach from=$labels item=label}}
        <option value="{{$label.labelalias}}"{{if $params.cat == $label.labelalias}} selected="selected"{{/if}}>{{if $label.issystem}}{{assign var="displayname" value="label_"|cat:$label.labelalias}}{{$LANG[$displayname]}}{{else}}{{$label.labelalias}}{{/if}}</option>
        {{/foreach}}
        </select>
        <input type="text" id="keyword" name="keyword" class="input_text" title="{{$LANG.subject_keyword}}" value="{{$params.keyword}}" style="width:120px" />
        <button type="submit" style="height: 24px;line-height:18px;width: 90px;vertical-align:middle">{{$LANG.search}}</button>
</form>
</div>
<div class="clear"></div>
{{/if}}

<div id="float-toolbar" class="float-toolbar">
{{include file="tudu#index^toolbar.tpl"}}
<table cellspacing="0" class="grid_thead">
  <tr>
    <td width="30" align="center" style="padding-left:0"><input name="checkall" type="checkbox" /></td>
    {{if in_array('star', $columns)}}<td width="20">&nbsp;</td>{{/if}}
    <td class="title_line" align="center" width="40"><div style="padding-left:4px"><span class="mailtitle"></span></div></td>
{{foreach from=$columns item=column}}
    {{if $column == 'sender'}}
    <td width="100" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="submitSort('?{{$query}}', 3,{{$sort[1]}});return false;">{{$LANG.sender}}{{if $sort[0]==3}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'subject'}}
    <td class="title_line"><div class="space">{{if !$coreseek}}<a href="javascript:void(0);" onClick="submitSort('?{{$query}}', 1,{{$sort[1]}});return false;">{{$LANG.subject}}{{if $sort[0]==1}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a>{{else}}{{$LANG.subject}}{{/if}}</div></td>
    {{elseif $column == 'accepter_endtime'}}
    <td width="110" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="submitSort('?{{$query}}', 4,{{$sort[1]}});return false;">{{$LANG.column_accepter}}{{if $sort[0]==4}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a>/<a href="javascript:void(0);" onClick="submitSort('?{{$query}}', 2,{{$sort[1]}});return false;">{{$LANG.column_endtime}}{{if $sort[0]==2}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'reply'}}
    <td width="90" class="title_line"><div class="space">{{$LANG.column_reply}}</div></td>
    {{elseif $column == 'lastpost'}}
    <td width="100" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="submitSort('?{{$query}}', 0,{{$sort[1]}});return false;">{{$LANG.lastpost}}{{if $sort[0]==0}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'endtime'}}
    <td width="110" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="submitSort('?{{$query}}', 2,{{$sort[1]}});return false;">{{$LANG.column_endtime}}{{if $sort[0]==2}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'starttime'}}
    <td width="130" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="submitSort('?{{$query}}', 5,{{$sort[1]}});return false;">{{$LANG.meeting_time}}{{if $sort[0]==5}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{/if}}
{{/foreach}}
  </tr>
</table>
</div>
<form action="" method="post" class="panel">
    <div class="panel-body">
    <div id="toolbar">{{include file="tudu#index^toolbar.tpl"}}</div>
    {{include file="tudu#index^list.tpl"}}
    </div>
</form>

<div style="display:none">
<table id="label-tpl" cellspacing="0" cellpadding="0" class="flagbg"><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr><tr><td class="falg_line"></td><td class="tag_txt"></td><td class="tag_close"></td><td class="falg_line"></td></tr><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr></table>
</div>
<script src="{{$options.sites.static}}/js/tudu/list.js?1005" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/card.js?1001" type="text/javascript"></script>
<script type="text/javascript">
<!--
$(function(){
    var LH = 'm=tudu&{{$query}}';

    TOP.Label.setLabels({{format_label labels=$labels}});
    TOP.Frame.title('{{$LANG.search_tudu}}');
    TOP.Frame.hash(LH);

    setTimeout(function(){
        TOP.Label.refreshMenu().focusLabel('{{if $label.issystem}}{{$label.labelalias}}{{else}}{{$label.labelid}}{{/if}}');
    }, 100);

    Tudu.List.setLabels(TOP.Label.getLabels()).setSortType('{{$sort[0]}}');
    Tudu.List.init('{{$label.labelalias}}');

    new FixToolbar({
        src: '#toolbar',
        target: '#float-toolbar'
    });

    $('#adv_search').click(function(){
        TOP.Frame.SearchForm.show();
    });

    new $.autocomplete({
        target: $('#inputfrom'),
        data: {users: TOP.Cast.get('users')},
        loadMethod: function() {
            var _v = this,
                keyword = $('#inputfrom').val();
            TOP.Cast.load(function(){
                TOP.Contact.load(function(){
                    _v.data.users = TOP.Cast.get('users');
                    _v._initMatchList(keyword);
                })
            });
        },
        columns: {users: ['truename', 'username', 'pinyin']},
        width: 155,
        arrowSupport: true,
        template: {
            users:'{truename} <span class="gray">&lt;{username}&gt;</span>'
        },
        onSelect: function(item){
            $('#inputfrom').val(item.data.truename);
        }
    });

    new $.autocomplete({
        target: $('#inputto'),
        data: {users: TOP.Cast.get('users')},
        loadMethod: function() {
            var _v = this,
                keyword = $('#inputto').val();
            TOP.Cast.load(function(){
                TOP.Contact.load(function(){
                    _v.data.users = TOP.Cast.get('users');
                    _v._initMatchList(keyword);
                })
            });
        },
        columns: {users: ['truename', 'username', 'pinyin']},
        width: 155,
        arrowSupport: true,
        template: {
            users:'{truename} <span class="gray">&lt;{username}&gt;</span>'
        },
        onSelect: function(item){
            $('#inputto').val(item.data.truename);
        }
    });
});

{{if $params.keyword || $params.words}}
heightlight('{{if !$coreseek}}{{$params.keyword}}{{else}}{{$params.words}}{{/if}}', {{if !$coreseek}}0{{else}}1{{/if}});
{{/if}}

function heightlight(txt, coreseek) {
    txt = txt.split('|');

    var obj = $('table.grid_list_2 td.g_in .subject a');
    if (coreseek) {
        obj = $('table.grid_list_2 td.g_in .subject a, table.grid_list_2 td.g_in .sender a, table.grid_list_2 td.g_in .deadline a, table.grid_list_2 td.g_in .lastupdate cite');
    }

    obj.each(function(){
        var subject = $(this).text();

        for(var i = 0; i < txt.length; i++) {
            var reg = new RegExp(txt[i], 'ig');
            subject = subject.replace(reg, '<span class="result-hlight">' + txt[i] + '</span>');
        }

        $(this).html(subject);
    });
}
-->
</script>
</body>
</html>
