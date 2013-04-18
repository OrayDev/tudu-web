<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.system_safe}} － 前台登录日志</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/secure.js?1003" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick-zh-cn.js" type="text/javascript"></script>
</head>
<body>

{{include file="secure/^nav.tpl" tab="loginlog"}}
<div id="float-toolbar" class="float-toolbar">
<div class="toolbar">
    <div>
        <form action="{{$basepath}}/secure/log/login" method="get">
        <span style="margin:5px 5px 10px 10px">姓名/帐号/IP：<input id="f-keywords" name="keywords" class="text" type="text" value="{{$params.keywords}}" /></span>
        <span style="margin:5px 5px 10px 10px">登录时间：<input id="f-starttime" name="starttime" style="width:148px;" value="{{$params.starttime}}" class="text" readonly="true" type="text" />&nbsp;至<input id="f-endtime" name="endtime" class="text"  style="width:148px;" value="{{$params.endtime}}" readonly="true" type="text" /></span>
        <button class="btn" type="submit" class="btn wd50" name="search">搜索</button>
        </form>
    </div>
    {{pagenavigator url=$pageinfo.url currpage=$pageinfo.currpage query=$pageinfo.query recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl"}}
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list table-header">
    <tr>
        <th class="td-first" width="140" align="left"><div class="td-space">登录时间</div></th>
        <th width="100" align="left"><div class="td-space">姓名</div></th>
        <th width="220" align="left"><div class="td-space">登录帐号</div></th>
        <th width="130" align="left"><div class="td-space">来源IP</div></th>
        <th class="td-last" align="left"><div class="td-space">IP所在地</div></th>
    </tr>
</table>
</div>

<div id="toolbar">
<div class="toolbar">
	<div>
	    <form action="{{$basepath}}/secure/log/login" method="get">
		<span style="margin:5px 5px 10px 10px">姓名/帐号/IP：<input id="keywords" name="keywords" class="text" type="text" value="{{$params.keywords}}" /></span>
		<span style="margin:5px 5px 10px 10px">登录时间：<input id="starttime" name="starttime" style="width:148px;" value="{{$params.starttime}}" class="text" readonly="true" type="text" />&nbsp;至<input id="endtime" name="endtime" class="text"  style="width:148px;" value="{{$params.endtime}}" readonly="true" type="text" /></span>
		<button class="btn" type="submit" class="btn wd50" name="search">搜索</button>
		</form>
	</div>
	{{pagenavigator url=$pageinfo.url currpage=$pageinfo.currpage query=$pageinfo.query recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl"}}
</div>
<div class="msgbox" id="search-tabs" style="{{if !$params}}display:none;{{/if}}">
<p>搜索结果 （共{{$pageinfo.recordcount}}条）&nbsp;|&nbsp;<a href="{{$basepath}}/secure/log/login">返回</a></p>
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list table-header">
    <tr>
        <th class="td-first" width="140" align="left"><div class="td-space">登录时间</div></th>
        <th width="100" align="left"><div class="td-space">姓名</div></th>
        <th width="220" align="left"><div class="td-space">登录帐号</div></th>
        <th width="130" align="left"><div class="td-space">来源IP</div></th>
        <th class="td-last" align="left"><div class="td-space">IP所在地</div></th>
    </tr>
</table>
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">
	<tbody id="log-list">
	{{foreach item=log key=key from=$logs}}
	<tr id="{{$log.loginlogid}}">
		<td width="140"><div class="td-space">{{$log.createtime|date_format:'%Y-%m-%d %H:%M'}}</div></td>
		<td width="100"><div class="td-space">{{$log.truename}}</div></td>
		<td width="220"><div class="td-space">{{$log.address}}</div></td>
		<td width="130"><div class="td-space">{{$log.ip|default:'未知IP'}}</div></td>
		<td><div class="td-space">{{$log.local|default:'未知'}}</div></td>
	</tr>
	{{foreachelse}}
	<tr>
        <td colspan="5" style="text-align:center;padding:35px 0">没有找到相关记录</td>
    </tr>
	{{/foreach}}
	</tbody>
</table>
<div class="list-btm-bar"></div>

<script type="text/javascript">
<!--
var COUNT = {{$pageinfo.recordcount}};
$(function() {
	_TOP.switchMod('secure');
	Secure.loginLog.init(COUNT);

	new FixToolbar({
        src: '#toolbar',
        target: '#float-toolbar'
    });
});
-->
</script>
</body>
</html>
