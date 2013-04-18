<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>增值应用 - 已使用列表</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/app.js?1001" type="text/javascript"></script>
</head>
<body>
{{include file="appstore/^nav.tpl" tab="list"}}
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="app-list">
	<col />
	<col width="150" />
	<col width="150" />
	<col width="250" />
	<tr>
		<th align="left"><div class="td-space">应用名称</div></th>
		<th align="left"><div class="td-space">收费类型</div></th>
		<th align="left"><div class="td-space">状态</div></th>
		<th align="left" class="td-last"><div class="td-space">操作</div></th>
	</tr>
</table>
<div id="app-list">
{{foreach item=app from=$apps}}
<table id="app-{{$app.appid|replace:'.':'_'}}" width="100%" border="0" cellspacing="0" cellpadding="0" class="app-list">
	<col width="80" />
	<col />
	<col width="150" />
	<col width="150" />
	<col width="250" />
	<tr>
		<td align="center"><div class="td-space"><img name="logo" src="{{$app.logo}}" /></div></td>
		<td><div class="td-space"><p><strong class="f14" name="appname">{{$app.appname}}</strong></p><p class="gray" name="description">{{$app.description}}</p></div></td>
		<td><div class="td-space">免费</div></td>
		<td><div class="td-space" name="status">{{if $app.status == 1}}启用{{else}}停用{{/if}}</div></td>
		<td><div class="td-space"><a name="url" href="{{$app.url}}/admin/index" class="app-btn-2"><span class="icon icon-app-entering"></span>进入设置</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a name="del" href="javascript:void(0)" onclick="App.deleteApp('{{$app.appid}}', {{$app.status}}, '{{$app.url}}/admin/index')">删除</a></div></td>
	</tr>
</table>
{{foreachelse}}
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="app-list">
	<tr>
		<td align="center" class="td-null">您还没有安装任何应用</td>
	</tr>
</table>
{{/foreach}}
</div>

<div id="loading-bar" style="padding:10px 0;text-align:center;line-height:16px;display:none;"><img src="{{$options.site.static}}/admin/img/loading.gif" style="vertical-align:middle;" />&nbsp;正在加载</div>
<div id="btm-bar"></div>

<script type="text/javascript">
<!--
$(function() {
	_TOP.switchMod('appstore');
	App.initList();
});
-->
</script>
</body>
</html>