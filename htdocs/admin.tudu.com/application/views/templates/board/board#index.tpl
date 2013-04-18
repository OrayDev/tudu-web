<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>分区管理</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.tree.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/base.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/select.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/board.js?1003" type="text/javascript"></script>
</head>
<body>

<p style="padding:4px 5px 9px; height:16px;line-height:16px;"><strong class="f14 text-title">分区管理</strong>&nbsp;<a href="http://service.oray.com/question/539.html" target="_blank" title="版块分区？" class="icon icon-question"></a></p>
<div id="float-toolbar" class="float-toolbar">
<div class="toolbar">
    <div class="btn-drop"><div class="icon btn-arrow"></div><input name="create" type="button" class="btn wd90 select-menu" value="新建分区" style="margin:0" /></div><span class="toolbar-space"></span><input name="merge" type="button" class="btn wd80" value="合并分区" />
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">
    <tr>
        <th class="td-first" align="left"><div class="td-space">分区名称</div></th>
        <th width="210" align="left"><div class="td-space">分区负责人</div></th>
        <th width="250" align="left"><div class="td-space">操作</div></th>
        <th width="50" align="left" class="td-last"><div class="td-space">排序</div></th>
    </tr>
</table>
</div>

<div id="toolbar">
<div class="toolbar">
	<div class="btn-drop"><div class="icon btn-arrow"></div><input name="create" type="button" class="btn wd90 select-menu" value="新建分区" style="margin:0" /></div><span class="toolbar-space"></span><input name="merge" type="button" class="btn wd80" value="合并分区" />
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">
	<tr>
		<th class="td-first" align="left"><div class="td-space">分区名称</div></th>
		<th width="210" align="left"><div class="td-space">分区负责人</div></th>
		<th width="250" align="left"><div class="td-space">操作</div></th>
		<th width="50" align="left" class="td-last"><div class="td-space">排序</div></th>
	</tr>
</table>
</div>
<div id="board-list">
</div>
<div class="list-btm-bar"></div>

{{assign var="org" value='@'|cat:$orgid}}
<script type="text/javascript">
<!--
var boards = [];
{{foreach from=$boards item=item}}
{{if $item.boardid != '^system' && $item.boardid != '^app-attend'}}
boards.push({boardid: '{{$item.boardid|replace:'^':'_'}}', boardname: '{{$item.boardname|escape:'html'|escape:'javascript'}}', parentid: '{{$item.parentid|replace:"^":"_"}}', ordernum: '{{$item.ordernum}}', type: '{{$item.type}}', moderators: '{{foreach name=foo item=moderator from=$item.moderators key=key}}{{if !$smarty.foreach.foo.first}},{{/if}}{{$key}}{{/foreach}}', moderatorsName: '{{if $item.moderators|@count <= 0}}-{{else}}{{foreach name=foo item=moderator from=$item.moderators}}{{if !$smarty.foreach.foo.first}},{{/if}}{{$moderator|escape:"javascript"}}{{/foreach}}{{/if}}', groups: '{{if $item.type == "board"}}{{foreach name=groups item=group from=$item.groups}}{{if !$smarty.foreach.groups.first}},{{/if}}{{if $group|is_group}}group_{{/if}}{{$group|replace:"$org":""|escape:"javascript"}}{{/foreach}}{{/if}}'});
{{/if}}
{{/foreach}}
Board.boards = boards;
$(function() {
	_TOP.switchMod('board');
	Board.init();

	new FixToolbar({
        src: '#toolbar',
        target: '#float-toolbar'
    });
});
-->
</script>
</body>
</html>