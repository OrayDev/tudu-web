<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.system_safe}} － {{$LANG.admin_log}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/secure.js?1003" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick-zh-cn.js" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/base.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/select.js?1001" type="text/javascript"></script>
</head>
<body>

{{include file="secure/^nav.tpl" tab="log"}}
<div id="float-toolbar" class="float-toolbar">
<div class="toolbar">
    <div>
        <form method="get" action="{{$basepath}}/secure/log/">
        <span style="margin:5px 5px 10px 10px">帐号/IP：<input id="f-keywords" name="keywords" class="text" type="text" value="{{$params.keywords}}" style="ime-mode:disabled" /></span>
        <span style="margin:5px 5px 10px 10px">操作时间：<input id="f-starttime" name="starttime" style="width:148px;" value="{{$params.starttime}}" class="text" readonly="true" type="text" />&nbsp;至<input id="f-endtime" name="endtime" class="text"  style="width:148px;" value="{{$params.endtime}}" readonly="true" type="text" /></span>
        <span style="margin:5px 5px 10px 10px">
            <select name="module" id="f-module">
                <option value="">所属模块</option>
                <option value="login"{{if $params.module == 'login'}} selected="selected"{{/if}}>登录</option>
                <option value="user"{{if $params.module == 'user'}} selected="selected"{{/if}}>帐号</option>
                <option value="dept"{{if $params.module == 'dept'}} selected="selected"{{/if}}>组织架构</option>
                <option value="role"{{if $params.module == 'role'}} selected="selected"{{/if}}>权限</option>
                <option value="group"{{if $params.module == 'group'}} selected="selected"{{/if}}>群组</option>
                <option value="org"{{if $params.module == 'org'}} selected="selected"{{/if}}>基本设置</option>
                <option value="secure"{{if $params.module == 'secure'}} selected="selected"{{/if}}>系统安全</option>
            </select>
        </span>
        <button class="btn" type="submit" class="btn wd50" name="search">搜索</button>
        </form>
    </div>
    {{pagenavigator url=$pageinfo.url currpage=$pageinfo.currpage query=$pageinfo.query recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl"}}
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list table-header">
    <tr>
        <th width="140" align="left"><div class="td-space">{{$LANG.opera_time}}</div></th>
        <th class="td-first" width="220" align="left"><div class="td-space">{{*$LANG.operator*}}操作帐号</div></th>
        <th width="130" align="left"><div class="td-space">{{$LANG.operator_ip}}</div></th>
        <th width="100" align="left"><div class="td-space">IP所在地</div></th>
        <th width="80" align="left"><div class="td-space">{{*$LANG.operatioin_type*}}操作模块</div></th>
        <th class="td-last" align="left"><div class="td-space">{{$LANG.description}}</div></th>
    </tr>
</table>
</div>
<div id="toolbar">
<div class="toolbar">
    <div>
        <form method="get" action="{{$basepath}}/secure/log/">
        <span style="margin:5px 5px 10px 10px">帐号/IP：<input id="keywords" name="keywords" class="text" type="text" value="{{$params.keywords}}" style="ime-mode:disabled" /></span>
        <span style="margin:5px 5px 10px 10px">操作时间：<input id="starttime" name="starttime" style="width:148px;" value="{{$params.starttime}}" class="text" readonly="true" type="text" />&nbsp;至<input id="endtime" name="endtime" class="text"  style="width:148px;" value="{{$params.endtime}}" readonly="true" type="text" /></span>
        <span style="margin:5px 5px 10px 10px">
            <select name="module" id="module">
                <option value="">所属模块</option>
                <option value="login"{{if $params.module == 'login'}} selected="selected"{{/if}}>登录</option>
                <option value="user"{{if $params.module == 'user'}} selected="selected"{{/if}}>帐号</option>
                <option value="dept"{{if $params.module == 'dept'}} selected="selected"{{/if}}>组织架构</option>
                <option value="role"{{if $params.module == 'role'}} selected="selected"{{/if}}>权限</option>
                <option value="group"{{if $params.module == 'group'}} selected="selected"{{/if}}>群组</option>
                <option value="org"{{if $params.module == 'org'}} selected="selected"{{/if}}>基本设置</option>
                <option value="secure"{{if $params.module == 'secure'}} selected="selected"{{/if}}>系统安全</option>
            </select>
        </span>
        <button class="btn" type="submit" class="btn wd50" name="search">搜索</button>
        </form>
    </div>
    {{pagenavigator url=$pageinfo.url currpage=$pageinfo.currpage query=$pageinfo.query recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl"}}
</div>
<div class="msgbox" id="search-tabs" style="{{if !$params}}display:none;{{/if}}">
<p>搜索结果 （共{{$pageinfo.recordcount}}条）&nbsp;|&nbsp;<a href="{{$basepath}}/secure/log">返回</a></p>
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list table-header">
    <tr>
        <th width="140" align="left"><div class="td-space">{{$LANG.opera_time}}</div></th>
        <th class="td-first" width="220" align="left"><div class="td-space">{{*$LANG.operator*}}操作帐号</div></th>
        <th width="130" align="left"><div class="td-space">{{$LANG.operator_ip}}</div></th>
        <th width="100" align="left"><div class="td-space">IP所在地</div></th>
        <th width="80" align="left"><div class="td-space">{{*$LANG.operatioin_type*}}操作模块</div></th>
        <th class="td-last" align="left"><div class="td-space">{{$LANG.description}}</div></th>
    </tr>
</table>
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">
    <tbody id="log-list">
    {{foreach item=log key=key from=$logs}}
    {{format_log_detail detail=$log.detail action=$log.action module=$log.module subaction=$log.subaction assign="logcontent"}}
    <tr id="{{$key}}">
        <td width="140"><div class="td-space">{{$log.createtime|date_format:'%Y-%m-%d %H:%M'}}</div></td>
        <td width="220"><div class="td-space">{{if strpos($log.userid, 'ACCOUNT^') === false}}{{$log.userid}}@{{$orgid}}{{else}}{{$log.userid|replace:'ACCOUNT^':'ORAY护照-'}}{{/if}}</div></td>
        <td width="130"><div class="td-space">{{$log.ip|default:'未知IP'}}</div></td>
        <td width="100"><div class="td-space">{{$log.local|default:'未知'}}</div></td>
        <td width="80"><div class="td-space">{{$LANG.logmodule[$log.module]|default:'-'}}</div></td>
        <td><div class="td-space">{{if $log.detail}}{{$logcontent|replace:'ACCOUNT^':'ORAY护照-'}}{{elseif $log.module == 'org' || $log.module == 'secure'}}{{$LANG.logdescription[$log.module][$log.action][$log.subaction]|sprintf:$log.target|replace:'ACCOUNT^':'ORAY护照-'}}{{else}}{{$LANG.logdescription[$log.module][$log.action]|sprintf:$log.target|replace:'ACCOUNT^':'ORAY护照-'}}{{/if}}</div></td>
    </tr>
    {{foreachelse}}
    <tr>
        <td colspan="6" style="text-align:center;padding:35px 0">没有找到相关记录</td>
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
    Secure.Log.init(COUNT);

    new FixToolbar({
        src: '#toolbar',
        target: '#float-toolbar'
    });
});
-->
</script>
</body>
</html>