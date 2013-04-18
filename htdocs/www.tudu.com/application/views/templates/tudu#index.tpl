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
var LH = '{{if $label.issystem}}m=tudu&search={{$label.labelalias}}&page={{$pageinfo.currpage}}{{else}}m=tudu&search=cat&cat={{$label.labelalias}}&page={{$pageinfo.currpage}}{{/if}}';
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
    <td width="100" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('?{{$query}}', 3,{{$sort[1]}});return false;">{{$LANG.sender}}{{if $sort[0]==3}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'subject'}}
    <td class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('?{{$query}}', 1,{{$sort[1]}});return false;">{{$LANG.subject}}{{if $sort[0]==1}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'accepter_endtime'}}
    <td width="110" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('?{{$query}}', 4,{{$sort[1]}});return false;">{{$LANG.column_accepter}}{{if $sort[0]==4}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a>/<a href="javascript:void(0);" onClick="Tudu.List.sort('?{{$query}}', 2,{{$sort[1]}});return false;">{{$LANG.column_endtime}}{{if $sort[0]==2}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'reply'}}
    <td width="90" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('?{{$query}}', 6,{{$sort[1]}});return false;">{{$LANG.tudu_percent}}{{if $sort[0]==6}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'replynum_viewnum'}}
    <td width="90" class="title_line"><div class="space">{{$LANG.column_reply}}</div></td>
    {{elseif $column == 'lastpost'}}
    <td width="100" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('?{{$query}}', 0,{{$sort[1]}});return false;">{{$LANG.lastpost}}{{if $sort[0]==0}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'endtime'}}
    <td width="110" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('?{{$query}}', 2,{{$sort[1]}});return false;">{{$LANG.column_endtime}}{{if $sort[0]==2}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'starttime'}}
    <td width="130" class="title_line" style="padding-left:0"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('?{{$query}}', 5,{{$sort[1]}});return false;">{{$LANG.meeting_time}}{{if $sort[0]==5}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{/if}}
{{/foreach}}
  </tr>
</table>
</div>
{{if $label}}
<div class="position">
  {{if $unread}}
      {{if $label.issystem}}
      <p><strong class="title">{{$LANG.unread_tudu}}</strong>| <a href="/tudu/?search={{$label.labelalias}}">{{$LANG.back}} "{{$label.displayname}}"</a></p>
      {{else}}
      <p><strong class="title">{{$LANG.unread_tudu}}</strong>| <a href="/tudu/?search=cat&cat={{$label.labelalias}}">{{$LANG.back}} "{{$label.displayname}}"</a></p>
      {{/if}}
      <div class="searchtip">{{$LANG.label_unread|sprintf:$label.displayname:$label.unreadnum}}{{$LANG.unread_tudu}}</div>
  {{elseif $undone}}
      {{if $label.issystem}}
      <p><strong class="title">{{$LANG.tudu_unconfirm}}</strong>| <a href="/tudu/?search={{$label.labelalias}}">{{$LANG.back}} "{{$label.displayname}}"</a></p>
      {{else}}
      <p><strong class="title">{{$LANG.tudu_unconfirm}}</strong>| <a href="/tudu/?search=cat&cat={{$label.labelalias}}">{{$LANG.back}} "{{$label.displayname}}"</a></p>
      {{/if}}
  {{elseif $unfinish && ($label.labelalias == 'sent' || !$label.issystem)}}
      <p><strong class="title">{{$LANG.unfinish_tudu}}</strong>| <a href="{{if $label.labelalias == 'sent'}}/tudu/?search=sent{{else}}/tudu/?search=cat&cat={{$label.labelalias}}{{/if}}">{{$LANG.back}} "{{$label.displayname}}"</a></p>
  {{else}}
      {{if $label.issystem}}
      {{strip}}
      <p>{{if !in_array($label.labelalias, array('drafts', 'notice', 'discuss'))}}<span style="float:right;"><span class="icon icon_view_gantt"></span>&nbsp;<a href="/tudu/?search={{$label.labelalias}}&chart=gantt&type=week" name="ganttchart">{{$LANG.switch_gantt_view}}</a></span>{{/if}}<strong class="title">{{$label.displayname}}</strong>{{if $label.labelalias != 'drafts'}}({{$LANG.label_total|sprintf:$label.totalnum}} <span id="ur_ct"{{if $label.unreadnum <= 0}} style="display:none"{{/if}}>{{$LANG.among}}{{$LANG.comma}}<a href="/tudu/?search={{$label.labelalias}}&unread=1" class="red">{{$LANG.unread_tudu}}</a>&nbsp;<span id="l_unread">{{$label.unreadnum}}</span> {{$LANG.tudu_unit}}</span>{{if $label.labelalias == 'sent' && $label.unfinish > 0}}{{$LANG.comma}}<a href="/tudu/?search=sent&unfinish=1" class="red"{{if $label.unfinish <= 0}} style="display:none;"{{/if}}>{{$LANG.unfinish_tudu}}</a>&nbsp;<span id="l_unfinish">{{$label.unfinish}}</span> {{$LANG.tudu_unit}}{{/if}}){{else}}({{$LANG.label_total|sprintf:$label.totalnum}}{{$LANG.tudu}}){{/if}}
      <span id="mark_all_read"{{if $label.labelalias == 'drafts' || $label.unreadnum <= 0}} style="display:none"{{/if}}>&nbsp;&nbsp;{{$LANG.mark_all}}<a href="javascript:void(0)" class="red" onclick="Tudu.markLabelRead('{{$label.labelalias}}');">{{$LANG.read}}</a>{{if $label.labelalias == 'sent'}}{{$LANG.comma}}{{/if}}</span>{{if $label.labelalias == 'sent'}}<span>&nbsp;{{$LANG.display}}<a href="/tudu/?search={{$label.labelalias}}&undone=1" class="red">{{$LANG.tudu_unconfirm}}</a></span>{{/if}}
      </p>
      {{/strip}}
      {{else}}
      <p><strong class="title">{{$label.displayname}}</strong>{{if $label.labelalias != 'drafts'}}({{$LANG.label_total|sprintf:$label.totalnum}} <span id="ur_ct"{{if $label.unreadnum <= 0}} style="display:none"{{/if}}>{{$LANG.among}}{{$LANG.comma}}<a href="/tudu/?search=cat&cat={{$label.labelalias}}&unread=1" class="red">{{$LANG.unread_tudu}}</a>&nbsp;<span id="l_unread">{{$label.unreadnum}}</span> {{$LANG.tudu_unit}}</span>{{if $label.unfinish > 0}}{{$LANG.comma}}<a href="/tudu/?search=cat&cat={{$label.labelalias}}&unfinish=1" class="red"{{if $label.unfinish <= 0}} style="display:none;"{{/if}}>{{$LANG.unfinish_tudu}}</a>&nbsp;<span id="l_unfinish">{{$label.unfinish}}</span> {{$LANG.tudu_unit}}{{/if}}){{else}}({{$LANG.label_total|sprintf:$label.totalnum}}{{$LANG.tudu}}){{/if}}
      <span id="mark_all_read"{{if $label.unreadnum <= 0}} style="display:none"{{/if}}>&nbsp;&nbsp;{{$LANG.mark_all}}<a href="javascript:void(0)" class="red" onclick="Tudu.markLabelRead('{{$label.labelalias}}');">{{$LANG.read}}</a></span>
      </p>
      {{/if}}
  {{/if}}
</div>
{{/if}}
<form action="" method="post" class="panel">

<div id="toolbar">{{include file="tudu#index^toolbar.tpl"}}</div>
{{include file="tudu#index^list.tpl"}}

</form>

<div style="display:none">
<table id="label-tpl" cellspacing="0" cellpadding="0" class="flagbg"><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr><tr><td class="falg_line"></td><td class="tag_txt"></td><td class="tag_close"></td><td class="falg_line"></td></tr><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr></table>
</div>
<script src="{{$options.sites.static}}/js/tudu/list.js?1010" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/card.js?1001" type="text/javascript"></script>
<script type="text/javascript">
<!--
$(function(){
TOP.Label.setLabels({{format_label labels=$labels}});
TOP.Frame.title('{{$label.displayname}}');
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
});
-->
</script>
</body>
</html>