<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>权限管理</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.tree.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/role.js?1003" type="text/javascript"></script>
</head>
<body>

<p style="padding:4px 5px 9px; height:16px;line-height:16px;"><strong class="f14 text-title">权限</strong></p>
<div class="float-toolbar" id="float-toolbar">
    <div class="toolbar">
        <input name="create" type="button" class="btn wd85" value="添加权限组"/>
    </div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">
        <tr>
            <th class="td-first" align="left"><div class="td-space">权限组</div></th>
            <th width="185" class="td-last" align="left"><div class="td-space">操作</div></th>
        </tr>
    </table>
</div>
<div id="toolbar">
<div class="toolbar">
	<input name="create" type="button" class="btn wd85" value="添加权限组"/>
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list table-header">
    <tr>
        <th class="td-first" align="left"><div class="td-space">权限组</div></th>
        <th width="185" class="td-last" align="left"><div class="td-space">操作</div></th>
    </tr>
</table>
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list table-header">
	<tbody id="role-list">
	{{foreach from=$roles item=role}}
	<tr id="role-{{'^'|str_replace:'_':$role.roleid}}">
		<td class="td-first"><div class="td-space"><span class="icon{{if $role.issystem}} icon-group{{else}} icon-group-senior{{/if}}"></span>&nbsp;{{$role.rolename|escape:'html'}}</div></td>
		<td class="td-last" width="185"><div class="td-space"><a href="{{$basepath}}/user/role/modify.access?roleid={{$role.roleid}}">[权限]</a> <a href="javascript:void(0);" onclick="Role.member('{{$role.roleid}}')">[成员]</a>{{if !$role.issystem}} <a href="javascript:void(0);" onclick="Role.update('{{$role.roleid}}', '{{$role.rolename}}');">[重命名]</a> <a href="javascript:void(0);" onclick="Role.del('{{$role.roleid}}');">[删除]</a>{{/if}}</div></td>
	</tr>
	{{/foreach}}
	</tbody>
</table>
<div class="list-btm-bar"></div>
<script type="text/javascript">
<!--
$(function() {
	_TOP.switchMod('role');
	Role.init();

	new FixToolbar({
        src: '#toolbar',
        target: '#float-toolbar'
    });
});
-->
</script>
</body>
</html>