<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>添加账号</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.tree.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.checkbox.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/user.js?1010" type="text/javascript"></script>
</head>
<body>
{{assign var="currUrl" value=$smarty.server.REQUEST_URI|urlencode}}
{{include file="user/^nav.tpl" tab="add"}}
<form action="{{$basepath}}/user/user/create" id="theform" method="post">
    <table id="user-base" border="0" cellspacing="2" cellpadding="3" class="table-form table-form-f12" style="margin-top:20px;margin-bottom:40px;">
        <tr>
            <th width="110" align="right"><label for="account">登录帐号：</label></th>
            <td><input name="userid" type="text" class="text" id="userid" title="请输入英文、数字" maxlength="16" style="ime-mode:disabled;width:250px" />&nbsp;&nbsp;@{{$org.orgid}}<input type="hidden" name="domainid" value="{{$domain.domainid}}" /><span id="hint-userid" style="margin-left:10px;"></span></td>
        </tr>
        <tr>
            <th align="right"><label for="password">密码：</label></th>
            <td><input name="password" type="text" class="text" id="password"  value="{{$org.defaultpassword}}" maxlength="16" style="ime-mode:disabled;width:250px" />&nbsp;&nbsp;<span class="gray" id="hint-password" style="display:none">用于登录图度系统。最长不超过16字符</span></td>
        </tr>
        <tr>
            <th align="right"><label for="name">真实姓名：</label></th>
            <td><input name="truename" type="text" class="text" id="truename" value="" style="width:250px" />&nbsp;&nbsp;<span class="gray" id="hint-truename" style="display:none">姓名在通讯录中显示</span></td>
        </tr>
        <tr>
            <th align="right">帐号状态：</th>
            <td><select name="status" style="width:256px"><option value="1">正式</option><option value="2">临时</option></select></td>
        </tr>
        <tr>
            <th align="right">性别：</th>
            <td><select name="gender" style="width:256px"><option value="1">男</option><option value="0">女</option></select></td>
        </tr>
        <tr>
            <th valign="top" align="right"><p style=" padding-top:3px;">所属部门：</p></th>
            <td>
                <p><select name="deptid" id="department" style="width:256px;">
                <option value="">请选择部门</option>
                {{foreach item=item from=$depts}}
                {{if $item.deptid != '^root'}}
                <option value="{{$item.deptid}}">{{$item.prefix}}{{$item.deptname}}</option>
                {{/if}}
                {{/foreach}}
                <option value="^new">新建部门</option>
                </select></p>
                <p style="margin-top:8px;display:none" id="new-dept"><select name="dept-parent" style="width:160px;">
                <option value="">无上级部门</option>
                {{foreach item=item from=$depts}}
                {{if $item.deptid != '^root'}}
                <option value="{{$item.deptid}}">{{$item.prefix}}{{$item.deptname}}</option>
                {{/if}}
                {{/foreach}}
                </select>&nbsp;
                    <input name="deptname" type="text" class="text" value="" style="width:83px;" /> <span class="gray" style="margin-left:5px;">选择新部门所属上级部门，并填写新部门名称</span>
                </p>
            </td>
        </tr>
        <tr>
            <th valign="top" align="right"><p style=" padding-top:3px;">帐号权限：</p></th>
            <td>
                <div id="role-list" {{if count($roles) > 5}} class="box"{{/if}}>
                {{foreach item=item from=$roles}}
                <p><label for="role-{{'^'|str_replace:'_':$item.roleid}}"><input type="checkbox" value={{$item.roleid}} name="roleid[]" id="role-{{'^'|str_replace:'_':$item.roleid}}"{{if $item.roleid == '^user'}} checked="checked"{{/if}} />{{$item.rolename}}</label>{{if $item.roleid == '^user'}}(基本权限){{elseif $item.roleid == '^advanced'}}(中高层管理者权限){{elseif $item.roleid == '^admin'}}(拥有前后台最高权限){{/if}}</p>
                {{/foreach}}
                </div>
            </td>
        </tr>
        <tr>
            <th valign="top" align="right"><p style="padding-top:3px;">网盘空间：</p></th>
            <td>
                <p><input name="maxndquota" type="text" class="text" value="10" size="10" maxlength="6" style="ime-mode:disabled" onkeyup="(this.v=function(){this.value=this.value.replace(/[^0-9.]+/,'');}).call(this)" /> MB</p>
            </td>
        </tr>
        <tr>
            <th align="right"> </th>
            <td><p style="margin:10px 0;"><a name="moreinfo" id="moreinfo-icon" href="javascript:void(0);" class="icon icon-fold"></a> <a name="moreinfo" href="javascript:void(0);"><span id="more-detail">展开帐号详细信息</span></a></p></td>
        </tr>
    </table>
    <table id="info" style="display:none; margin:0 0 40px 25px;" border="0" cellspacing="2" cellpadding="3" class="table-form table-form-f12">
        <tr>
            <th valign="top" align="right"><p style=" padding-top:3px;">显示在通讯录：</p></th>
            <td>
                <p><label for="yes"><input id="yes" checked="checked" name="isshow" type="radio" value="1" />是</label>&nbsp;&nbsp;显示排序为 <input name="ordernum" type="text" class="text" value="1" size="10" maxlength="4" style="ime-mode:disabled" />&nbsp;&nbsp;<span class="gray" id="hint-order">输入的数字越大，排序越靠前</span></p>
                <p style="margin-top:8px;"><label for="no"><input id="no" name="isshow" type="radio" value="0" />否</label></p>
            </td>
        </tr>
        <tr>
            <th align="right"><label for="position">职位：</label></th>
            <td><input name="position" type="text" id="position" class="text" style="width:250px;" maxlength="50" /></td>
        </tr>
        <tr>
            <th valign="top"  align="right">所属群组：</th>
            <td>
                <div id="group-list" {{if count($groups) > 5}} class="box"{{/if}}>
                {{foreach item=item from=$groups}}
                <p><label for="group-{{'^'|str_replace:'_':$item.groupid}}"><input type="checkbox" value={{$item.groupid}} name="groupid[]" id="group-{{'^'|str_replace:'_':$item.groupid}}"{{if $item.groupid == '^all'}} checked="checked"{{/if}} />{{$item.groupname}}</label></p>
                {{/foreach}}
                </div>
                <p style="margin:8px 0;"><input name="groupname" id="groupname" type="text" class="text" title="请输入新群组名称" maxlength="20" style="width:250px;" />&nbsp;<a id="create-group" class="icon icon-add" href="javascript:void(0);"></a><span class="gray" style="margin-left:10px;">一般用于跨部门的群体组合，如全体部门经理组 <a href="http://service.oray.com/question/706.html" target="_blank">更多帮助</a></span></p>
            </td>
        </tr>
        <tr>
            <th valign="top" align="right">组织架构视图：</th>
            <td>
                <p style="margin-bottom:8px;">在此设置帐号可见的组织架构</p>
                <div class="box">
                <div id="tree-ct"></div>
                </div>
            </td>
        </tr>
        <tr>
            <th align="right"><label for="user-email">邮箱：</label></th>
            <td><input id="user-email" name="email" type="text" class="text" style="width:250px;" /></td>
        </tr>
        <tr>
            <th align="right"><label for="id-number">身份证号：</label></th>
            <td><input id="id-number" name="idnumber" type="text" class="text" style="width:250px;" /></td>
        </tr>
        <tr>
            <th align="right">出生日期：</th>
            <td>
                {{assign var="year" value=$smarty.now|date_format:"%Y"}}
                <select style="width:90px;" name="bir-year" id="bir-year">
                <option value="">-</option>
                {{section name=year loop=70}}
                <option value="{{math equation="$year-x" x=$smarty.section.year.index}}" {{if $year - $userinfo.birthyear == $smarty.section.year.index}} selected="selected"{{/if}}>{{math equation="$year-x" x=$smarty.section.year.index}}</option>
                {{/section}}
                </select>
                <select style="width:80px;" name="bir-month" id="bir-month">
                <option value="">-</option>
                {{section name=month loop=12}}
                <option value="{{math equation="x+1" x=$smarty.section.month.index}}" {{if $userinfo.birthmonth - 1 == $smarty.section.month.index}} selected="selected"{{/if}}>{{math equation="x+1" x=$smarty.section.month.index}}</option>
                {{/section}}
                </select>
                <select style="width:78px;" name="bir-day" id="bir-day"><option value="">-</option></select>
            </td>
        </tr>
        <tr>
            <th align="right"><label for="cell-phone">手机号：</label></th>
            <td><input id="mobile" name="mobile" type="text" class="text" style="width:250px;" maxlength="30" /></td>
        </tr>
        <tr>
            <th align="right"><label for="phone">办公电话：</label></th>
            <td><input id="tel" name="tel" type="text" class="text" style="width:250px;" /></td>
        </tr>
        <tr>
            <th align="right"> </th>
            <td></td>
        </tr>
    </table>
    <div class="tool-btm"><div class="toolbar"><input type="submit" class="btn wd50" value="提 交"/></div>

</form>

<script type="text/javascript">
<!--
var depts = [];
{{foreach from=$depts item=item}}
depts.push({deptid:'{{$item.deptid|replace:"^":"_"}}', deptname: '{{$item.deptname|escape:'html'}}', parentid: '{{$item.parentid|replace:"^":"_"}}'});
{{/foreach}}
var _ORG_NAME = '{{$org.orgname}}';
$(function() {
    _TOP.switchMod('user');
    User.depts = depts;
    User.initCreate();
});
-->
</script>
</body>
</html>