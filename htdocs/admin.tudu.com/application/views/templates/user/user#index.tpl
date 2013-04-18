<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>用户管理</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/base.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/select.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/user.js?1010" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick-zh-cn.js" type="text/javascript"></script>
</head>
<body>
<div class="title-bar" id="user-title">
    <strong class="f14 text-title" id="list-title">帐号</strong>（共<span id="user-total">0</span>个<span id="user-span-temp">，其中<a href="javascript:void(0)" _status="2">临时帐号</a><span id="user-temp">0</span>个</span><span id="user-span-disabled">，<a href="javascript:void(0)" _status="0">停用帐号</a><span id="user-disabled">0</span>个</span><span id="user-span-locked">，<a href="javascript:void(0)" _status="locked">锁定帐号</a><span id="user-locked">0</span>个</span>）<span id="search-back" style="display:none"> | <a href="javascript:void(0)">返回用户列表</a></span>
</div>
<div>
    <div class="search-bar" style="padding:5px 0;">
        <table border="0" cellspacing="8" cellpadding="0" style="margin-left:-8;width:1000px;" class="gray">
            <tr>
                <td><label for="keywords">姓名/帐号：<input id="keywords" name="keywords" type="text" class="text" style="width:269px;" title="两个以上用；分隔"/></label></td>
                <td><label for="starttime"> 创建时间：从：</label><input id="starttime" name="starttime" type="text" class="text"  style="width:218px;" value="" readonly="true" />&nbsp;到：<input id="endtime" name="endtime" type="text" class="text" style="width:218px;" value="" readonly="true" /></td>
                <td rowspan="2"><input name="search" class="btn-big" id="search" type="button" value="搜索" /></td>
            </tr>
            <tr>
                <td>
                    <select style="width:160px;" name="gender" id="gender">
                    <option value="">性别</option>
                    <option value="1">男</option>
                    <option value="0">女</option>
                    </select><select style="width:160px;" name="status" id="status">
                    <option value="">帐号状态</option>
                    <option value="1">正式</option>
                    <option value="2">临时</option>
                    <option value="0">停用</option>
                    <option value="locked">锁定</option>
                    </select>
                </td>
                <td>
                    <select style="width:175px;" name="groups" id="groups">
                    <option value="">所属群组</option>
                    {{foreach item=group from=$groups}}
                    <option value="{{$group.groupid}}">{{$group.groupname}}</option>
                    {{/foreach}}
                    </select><select style="width:175px;" name="role" id="role">
                    <option value="">所属权限组</option>
                    {{foreach item=role from=$roles}}
                    <option value="{{$role.roleid}}">{{$role.rolename}}</option>
                    {{/foreach}}
                    </select><select style="width:175px;" name="dept" id="dept">
                    <option value="">所属部门</option>
                    {{foreach item=dept from=$depts}}
                    {{if $dept.deptid != '^root'}}
                    <option value="{{$dept.deptid}}">{{$dept.prefix}}{{$dept.deptname}}</option>
                    {{/if}}
                    {{/foreach}}
                    </select>
                </td>
            </tr>
        </table>
    </div>
</div>

<div id="float-toolbar" class="float-toolbar">
{{include file="user/user#index^toolbar.tpl"}}
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list table-header">
    <tr>
        <th width="30"><input name="checkall" type="checkbox" /></th>
        <th width="120" align="left"><div class="td-space"><a href="javascript:void(0)" name="sort-truename">真实姓名<span class="sort-label"></span></a></div></th>
        <th width="270" align="left"><div class="td-space"><a href="javascript:void(0)" name="sort-uid">帐号<span class="sort-label"></span></a></div></th>
        <th width="95" align="left"><div class="td-space"><a href="javascript:void(0)" name="sort-status">帐号状态<span class="sort-label"></span></a></div></th>
        <th width="95" align="left"><div class="td-space"><a href="javascript:void(0)" name="sort-deptid">所属部门<span class="sort-label"></span></a></div></th>
        <th width="95" align="left"><div class="td-space"><a href="javascript:void(0)" name="sort-createtime">创建时间<span class="sort-label"></span></a></div></th>
        <th class="td-last" align="left"><div class="td-space">所属群组</div></th>
    </tr>
</table>
</div>

<div id="toolbar">
{{include file="user/user#index^toolbar.tpl"}}
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list table-header">
    <tr>
        <th width="30"><input name="checkall" type="checkbox" /></th>
        <th width="120" align="left"><div class="td-space"><a href="javascript:void(0)" name="sort-truename">真实姓名<span class="sort-label"></span></a></div></th>
        <th width="270" align="left"><div class="td-space"><a href="javascript:void(0)" name="sort-uid">帐号<span class="sort-label"></span></a></div></th>
        <th width="95" align="left"><div class="td-space"><a href="javascript:void(0)" name="sort-status">帐号状态<span class="sort-label"></span></a></div></th>
        <th width="95" align="left"><div class="td-space"><a href="javascript:void(0)" name="sort-deptid">所属部门<span class="sort-label"></span></a></div></th>
        <th width="95" align="left"><div class="td-space"><a href="javascript:void(0)" name="sort-createtime">创建时间<span class="sort-label"></span></a></div></th>
        <th class="td-last" align="left"><div class="td-space">所属群组</div></th>
    </tr>
</table>
</div>

<table id="null-list" width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list" style="display:none;">
    <tr>
        <td colspan="7" class="td-null">搜索结果为空</td>
    </tr>
</table>

<div id="user-list" style="cursor:pointer">
</div>

<div id="loading-bar" style="padding:10px 0;text-align:center;line-height:16px"><img src="{{$basepath}}/img/loading.gif" style="vertical-align:middle;" />&nbsp;正在加载</div>
<div id="btm-bar" class="list-btm-bar"></div>

<script type="text/javascript">
<!--
var groups = {};
{{foreach item=item from=$groups name=group}}
groups['{{$item.groupid}}'] = {name: '{{$item.groupname|escape:'html'}}'};
{{/foreach}}
User.groups = groups;
$(function() {
    _TOP.switchMod('user');
    User.init();

    new FixToolbar({
        src: '#toolbar',
        target: '#float-toolbar'
    });
});
-->
</script>
</body>
</html>