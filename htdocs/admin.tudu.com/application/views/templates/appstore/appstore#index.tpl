<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>增值应用 - 应用列表</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/app.js?1001" type="text/javascript"></script>
</head>
<body>
{{include file="appstore/^nav.tpl" tab="index"}}
<div class="app-box">
	<div class="app-box-content">
		<p><strong class="f14">图度应用汇总</strong></p>
		<p class="gray">由图度推出的各类增值应用，用户在此可选择适合自身企业的应用。</p>
	</div>
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="app-list">
	<col />
	<col width="200" />
	<col width="200" />
	<tr>
		<th align="left"><div class="td-space">应用名称</div></th>
		<th align="left"><div class="td-space">收费类型</div></th>
		<th align="left" class="td-last"><div class="td-space">操作</div></th>
	</tr>
</table>
<div id="app-list">
{{foreach item=app from=$apps}}
<table id="app-{{$app.appid|replace:'.':'_'}}" width="100%" border="0" cellspacing="0" cellpadding="0" class="app-list">
	<col width="80" />
	<col />
	<col width="200" />
	<col width="200" />
	<tr>
		<td align="center"><div class="td-space"><img name="logo" src="{{$app.logo}}" /></div></td>
		<td><div class="td-space"><p><strong class="f14" name="appname">{{$app.appname}}</strong></p><p class="gray" name="description">{{$app.description}}</p></div></td>
		<td><div class="td-space">免费</div></td>
		<td><div class="td-space" name="app-btn">
		    {{if !$app.isinstall}}
		    <a href="javascript:void(0)" onclick="App.installApp('{{$app.appid}}')" class="app-btn-1"><span class="icon icon-app-add"></span>安装此应用</a>
		    {{else}}
		    <a href="{{$app.url}}/admin/index" class="app-btn-2"><span class="icon icon-app-entering"></span>进入设置</a>
		    {{/if}}
	    </div></td>
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
	App.init();
});
-->
</script>
</body>
</html>