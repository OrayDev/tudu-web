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
<!--
var LH = 'm=tudu/converge&search={{$label.labelalias}}&page={{$pageinfo.currpage}}';
if (top == this) {
	location = '/frame#' + LH;
}
-->
</script>

</head>
<body>
<div id="float-toolbar" class="float-toolbar">
{{include file="tudu#index^toolbar.tpl"}}
<table cellspacing="0" class="grid_thead">
  <tr>
    <td width="30" align="center" style="padding-left:0"><input name="checkall" type="checkbox" /></td>
    {{if in_array('star', $columns)}}<td width="20">&nbsp;</td>{{/if}}
    <td class="title_line" align="center" width="40"><div style="padding-left:4px"><span class="mailtitle"></span></div></td>
{{foreach from=$columns item=column}}
    {{if $column == 'sender'}}
    <td width="100" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 3,{{$sort[1]}});return false;">{{$LANG.sender}}{{if $sort[0]==3}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'subject'}}
    <td class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 1,{{$sort[1]}});return false;">{{$LANG.subject}}{{if $sort[0]==1}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'accepter_endtime'}}
    <td width="110" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 4,{{$sort[1]}});return false;">{{$LANG.column_accepter}}{{if $sort[0]==4}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a>/<a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 2,{{$sort[1]}});return false;">{{$LANG.column_endtime}}{{if $sort[0]==2}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'reply'}}
    <td width="90" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 6,{{$sort[1]}});return false;">{{$LANG.tudu_percent}}{{if $sort[0]==6}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'lastpost'}}
    <td width="100" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 0,{{$sort[1]}});return false;">{{$LANG.lastpost}}{{if $sort[0]==0}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'endtime'}}
    <td width="110" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 2,{{$sort[1]}});return false;">{{$LANG.column_endtime}}{{if $sort[0]==2}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'starttime'}}
    <td width="130" class="title_line" style="padding-left:0"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 5,{{$sort[1]}});return false;">{{$LANG.meeting_time}}{{if $sort[0]==5}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{/if}}
{{/foreach}}
  </tr>
</table>
</div>
{{if $label}}
<div class="position">
  {{if !$params.unread}}
  {{if $label.issystem}}
  {{strip}}
  <p>{{if !in_array($label.labelalias, array('drafts', 'notice', 'discuss'))}}<span style="float:right;"><span class="icon icon_view_gantt"></span>&nbsp;<a href="/tudu/?search={{$label.labelalias}}&chart=gantt&type=week" name="ganttchart">{{$LANG.switch_gantt_view}}</a></span>{{/if}}<strong class="title">{{$label.displayname}}</strong>{{if $label.labelalias != 'drafts'}}({{$LANG.label_total|sprintf:$label.totalnum}} <span id="ur_ct"{{if $label.unreadnum <= 0}} style="display:none"{{/if}}>{{$LANG.among}}{{$LANG.comma}}<a href="/tudu/converge?search={{$label.labelalias}}&unread=1" class="red">{{$LANG.unread_tudu}}</a>&nbsp;<span id="l_unread">{{$label.unreadnum}}</span> {{$LANG.tudu_unit}}</span>){{else}}({{$LANG.label_total|sprintf:$label.totalnum}}{{$LANG.tudu}}){{/if}}
  <span id="mark_all_read"{{if $label.labelalias == 'drafts' || $label.unreadnum <= 0}} style="display:none"{{/if}}>&nbsp;&nbsp;{{$LANG.mark_all}}<a href="javascript:void(0)" class="red" onclick="Tudu.markLabelRead('{{$label.labelalias}}');">{{$LANG.read}}</a></span>
  </p>
  {{/strip}}
  {{else}}
  <p><strong class="title">{{$label.displayname}}</strong>{{if $label.labelalias != 'drafts'}}({{$LANG.label_total|sprintf:$label.totalnum}} <span id="ur_ct"{{if $label.unreadnum <= 0}} style="display:none"{{/if}}>{{$LANG.among}}{{$LANG.comma}}<a href="/tudu/converge?search={{$label.labelalias}}&unread=1" class="red">{{$LANG.unread_tudu}}</a>&nbsp;<span id="l_unread">{{$label.unreadnum}}</span> {{$LANG.tudu_unit}}</span>){{else}}({{$LANG.label_total|sprintf:$label.totalnum}}{{$LANG.tudu}}){{/if}}</p>
  {{/if}}
  {{else}}
  {{if $label.issystem}}
  <p><strong class="title">{{$LANG.unread_tudu}}</strong>| <a href="/tudu/?search={{$label.labelalias}}">{{$LANG.back}} "{{$label.displayname}}"</a></p>
  {{else}}
  <p><strong class="title">{{$LANG.unread_tudu}}</strong>| <a href="/tudu/converge?search={{$label.labelalias}}">{{$LANG.back}} "{{$label.displayname}}"</a></p>
  {{/if}}
  <div class="searchtip">{{$LANG.label_unread|sprintf:$label.displayname:$label.unreadnum}}{{$LANG.unread_tudu}}</div>
  {{/if}}
</div>
{{/if}}
<form action="" method="post" class="panel">
<div class="panel-body">
  <div id="toolbar">{{include file="tudu#index^toolbar.tpl"}}</div>
  {{include file="tudu#converge^list.tpl"}}
</div>
</form>

<div style="display:none">
<table id="label-tpl" cellspacing="0" cellpadding="0" class="flagbg"><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr><tr><td class="falg_line"></td><td class="tag_txt"></td><td class="tag_close"></td><td class="falg_line"></td></tr><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr></table>
</div>
{{if $label.labelalias == 'todo'}}
<script src="{{$options.sites.static}}/js/xheditor-1.1.9/xheditor-zh-cn.min.js?1001" type="text/javascript"></script>
{{/if}}
<script src="{{$options.sites.static}}/js/tudu/list.js?1008" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/card.js?1001" type="text/javascript"></script>
<script type="text/javascript">
<!--
TOP.Label.setLabels({{format_label labels=$labels}});
TOP.Frame.title('{{$label.displayname}}');
TOP.Frame.hash(LH);

setTimeout(function(){
    TOP.Label.refreshMenu().focusLabel('{{$label.labelalias}}');
}, 100);

Tudu.List.setLabels(TOP.Label.getLabels()).setSortType('{{$sort[0]}}');
Tudu.List.init('{{$label.labelalias}}');

new FixToolbar({
	src: '#toolbar',
	target: '#float-toolbar'
});

var _OFFSET = 1;
$('a[name="show_outdate"]').bind('click', function(){
	var a = $(this);

    a.attr('_offset', ++_OFFSET);

    var parent = a.parent('div.load_more_bar:eq(0)');

    loadTudu(parent[0], {
        label: parent.attr('_type'),
        pagesize:20,
        page: _OFFSET,
        sorttype: '{{$sort.0}}',
        sortasc: '{{if $sort.1 == 1}}0{{else}}1{{/if}}'
    });
});

function loadTudu(target, params) {
	var url = '/tudu/list',
	    query = [];

	query.push('back={{$smarty.server.REQUEST_URI|urlencode}}');
	if (params) {
        for(var k in params) {
            query.push(k + '=' + encodeURIComponent(params[k]));
        }
        url += '?' + query.join('&');
	}

	$.get(url, function(html){
	    var ct = $('<div class="grid_list_group_ct"></div>');

	    ct.html(html);

		$(target).before(ct);

		// 列表鼠标效果，标签等
		ct.find('table.grid_list_2 a').click(function(e){
            TOP.stopEventBuddle(e);
        });

        ct.find("table.grid_list_2")
        .mouseover(function(){
            $(this).addClass("over")
        })
        .mouseout(function(){
            $(this).removeClass("over")
        })
        .each(function(){
            var o = $(this);
            var tuduId = o.attr('id').replace('tudu-', '');
            o.find('td.lastupdate').click(function(){
                location = '/tudu/view?tid=' + tuduId + '&page=last&back=' + encodeURIComponent(location.href);
            });

            o.find('a.icon_attention').bind('click', function(){
                var star = $(this),
                isstar = star.hasClass('attention');

                star.toggleClass('attention');

                var func = isstar ? 'unstar' : 'star';
                return Tudu.star(tuduId, func);
            });

            var labels = o.attr('_labels');

            if (!labels) {
                return ;
            }

            labels = labels.split('|');

            for (var i = 0, c = labels.length; i < c; i++) {
                if (!labels[i] || labels[i].indexOf('^') != -1) {
                    continue;
                }

                Label.append($(this), labels[i]);
            }
        });
	});
}
-->
</script>
</body>
</html>