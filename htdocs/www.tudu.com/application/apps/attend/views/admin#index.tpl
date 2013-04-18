<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>考勤应用设置</title>

<link href="{{$options.sites.static}}/admin/css/style.css?1005" type="text/css" rel="stylesheet" />
<link href="{{$options.sites.static}}/admin/css/skin_{{if null !== $user.option.skin}}{{$user.option.skin}}{{else}}8{{/if}}.css?1003" type="text/css" rel="stylesheet" id="css-skin" />
<script type="text/javascript" src="{{$options.sites.static}}/admin/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="{{$options.sites.static}}/js/jquery.extend.js"></script>
<script type="text/javascript" src="{{$options.sites.static}}/admin/js/frame.js"></script>
<script type="text/javascript" src="{{$options.sites.static}}/js/attend/admin.js"></script>
</head>
<body>

<div class="msgbox">
    <p>提示：部门负责人均默认拥有自己所属部门的排班设置和考勤统计权限</p>
</div>
<form id="theform" method="post" action="/app/attend/admin/save">
<input type="hidden" id="currentstatus" value="{{$app.status}}" />
<fieldset class="form-field">
<legend><strong class="f14 text-title">应用状态</strong></legend>
<div class="line-bold"></div>
<table border="0" cellspacing="2" cellpadding="3" class="table-form table-form-f12" style="line-height:2.4em; margin:20px 0;width:550px">
    <tr>
        <th align="right" valign="top" width="100">应用状态{{$LANG.cln}}</th>
        <td>
            {{strip}}
            <label for="status-on"><input type="radio" name="status" value="1" id="status-on"{{if $app.status == 1}} checked="checked"{{/if}} /> 启用</label>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <label for="status-off"><input type="radio" name="status" value="2" id="status-off"{{if $app.status == 2}} checked="checked"{{/if}} /> 停用</label>
            {{/strip}}
        </td>
    </tr>
</table>
</fieldset>

<fieldset class="form-field">
    <legend><strong class="f14 text-title">请选择相关人员</strong></legend>
    <div class="line-bold"></div>
    <table border="0" cellspacing="2" cellpadding="3" class="table-form table-form-f12" style="margin:20px 0 40px 0">
        <tr>
            <th width="110" align="right"><a href="javascript:void(0)" id="link-admin">最高权限人</a>：</th>
            <td width="200"><textarea class="text" id="admin" name="admin" rows="1">{{if is_array($roles.admin)}}{{"\n"|implode:$roles.admin}}{{/if}}</textarea></td>
            <td><span class="gray" style="display:none;">拥有考勤应用所有设置操作，不受部门限制。</span></td>
        </tr>
        <tr>
            <td></td>
            <td><label for="more"><input type="checkbox" id="more"{{if $roles.def || $roles.sc || $roles.sum}} checked="checked" {{/if}}/> 更多设置</label></td>
            <td></td>
        </tr>
        <tbody id="more-tbody"{{if !$roles.def && !$roles.sc && !$roles.sum}} style="display:none"{{/if}}>
        <tr>
            <th align="right"><a href="javascript:void(0)" id="link-def">考勤流程设置</a>：</th>
            <td><textarea class="text" id="def" name="def" rows="1" >{{if is_array($roles.def)}}{{"\n"|implode:$roles.def}}{{/if}}</textarea></td>
            <td><span class="gray" style="display:none;">可设置请假排班，加班出差等审批规则，不受部门限制。</span></td>
        </tr>
        <tr>
            <th align="right"><a href="javascript:void(0)" id="link-sc">排班设置</a>：</th>
            <td><textarea class="text" id="sc" name="sc" rows="1">{{if is_array($roles.sc)}}{{"\n"|implode:$roles.sc}}{{/if}}</textarea></td>
            <td><span class="gray" style="display:none;">可设置不同员工排班方案及排班计划，只适用于自身部门。</span></td>
        </tr>
        <tr>
            <th align="right"><a href="javascript:void(0)" id="link-sum">考勤统计</a>：</th>
            <td><textarea class="text" id="sum" name="sum" rows="1">{{if is_array($roles.sum)}}{{"\n"|implode:$roles.sum}}{{/if}}</textarea></td>
            <td><span class="gray" style="display:none;">可查看员工的考勤情况，只适用于自身部门。</span></td>
        </tr>
        </tbody>
        <tr>
            <th align="right"></th>
            <td colspan="2"></td>
        </tr>
    </table>
</fieldset>

<fieldset class="form-field">
<legend><strong class="f14 text-title">其他设置</strong></legend>
<div class="line-bold"></div>
<table border="0" cellspacing="2" cellpadding="3" class="table-form table-form-f12" style="line-height:2.4em; margin:20px 0;width:550px">
    <tr>
        <th align="right" valign="top" width="100">下班签退提醒{{$LANG.cln}}</th>
        <td>
            {{strip}}
            <label for="checkoutremind-on"><input type="radio" name="checkoutremind" value="1" id="checkoutremind-on"{{if !$app.settings || $app.settings.checkoutremind == 1}} checked="checked"{{/if}} /> 是</label>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <label for="checkoutremind-off"><input type="radio" name="checkoutremind" value="0" id="checkoutremind-off"{{if $app.settings.checkoutremind == 0}} checked="checked"{{/if}} /> 否</label>
            {{/strip}}
        </td>
    </tr>
</table>
</fieldset>

<div class="tool-btm"><div class="toolbar toolbar-bottom"><input type="submit" class="btn wd50" value="保存"></input></div></div>
</form>
<script type="text/javascript">
<!--
$(function(){
	new FixToolbar({
	    target: 'div.tool-btm'
	});

	{{if $app.status == 0}}
    Attend.Admin.initApp();
	{{else}}
	Attend.Admin.init();
	{{/if}}
});
-->
</script>
</body>
</html>
