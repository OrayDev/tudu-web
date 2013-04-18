<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>群组管理</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.tree.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/group.js?1002" type="text/javascript"></script>
</head>
<body>

<p style="padding:4px 5px 9px; height:16px;line-height:16px;"><strong class="f14 text-title">群组</strong>&nbsp;<a href="http://service.oray.com/question/706.html" target="_blank" title="群组？" class="icon icon-question"></a></p>
<div id="float-toolbar" class="float-toolbar">
<div class="toolbar">
    <input name="create" class="btn wd85" value="新建群组" type="button" />
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list table-header">
    <tr>
        <th class="td-first" align="left"><div class="td-space">群组名称</div></th>
        <th width="160" class="td-last" align="left"><div class="td-space">操作</div></th>
        <th width="50" align="left"><div class="td-space">排序</div></th>
    </tr>
</table>
</div>
<div id="toolbar">
<div class="toolbar">
	<input name="create" class="btn wd80" value="新建群组" type="button" />
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list table-header">
    <tr>
        <th class="td-first" align="left"><div class="td-space">群组名称</div></th>
        <th width="160" class="td-last" align="left"><div class="td-space">操作</div></th>
        <th width="50" align="left"><div class="td-space">排序</div></th>
    </tr>
</table>
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">
	<tbody id="group-list">
	{{foreach item=group from=$groups name="group"}}
	<tr id="group-{{'^'|str_replace:'_':$group.groupid}}" _gid="{{$group.groupid}}">
		<td class="td-first"><div class="td-space">{{$group.groupname}}</div></td>
		<td width="160"><div class="td-space"><a href="javascript:void(0);" onclick="Group.member('{{$group.groupid}}');">[成员]</a>{{if !$group.issystem}} <a href="javascript:void(0);" onclick="Group.update('{{$group.groupid}}', '{{$group.groupname}}')">[重命名]</a> <a href="javascript:void(0);" onclick="Group.del('{{$group.groupid}}')">[删除]</a>{{/if}}</div></td>
		<td width="50" class="td-last"><div class="td-space"><a href="javascript:void(0);"{{if $smarty.foreach.group.index == 0}} class="lightgray"{{else}} onclick="Group.sortGroup('{{$group.groupid}}', 'up');"{{/if}}>↑</a> <a href="javascript:void(0);"{{if $smarty.foreach.group.index == count($groups) - 1}} class="lightgray"{{else}} onclick="Group.sortGroup('{{$group.groupid}}', 'down');"{{/if}}>↓</a></div></td>
	</tr>
	{{/foreach}}
	</tbody>
</table>
<div class="list-btm-bar"></div>

<script type="text/javascript">
<!--
$(function() {
	_TOP.switchMod('group');
	Group.init();

	new FixToolbar({
        src: '#toolbar',
        target: '#float-toolbar'
    });
});
-->
</script>
</body>
</html>