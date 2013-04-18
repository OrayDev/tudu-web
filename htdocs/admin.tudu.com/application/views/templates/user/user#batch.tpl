<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>帐号－批量编辑</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.checkbox.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.tree.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/user.js?1010" type="text/javascript"></script>
</head>
<body>
<div class="title-bar"><strong class="f14 text-title">批量编辑</strong> | <a href="{{if $back}}{{$back}}{{else}}{{$basepath|cat:'/user/user'}}{{/if}}">返回</a></div>
<p style="margin:8px 0;">您正使用多帐号编辑功能，对以下帐号属性进行统一编辑</p>
<form action="{{$basepath}}/user/user/batch.update" method="post" id="theform" class="bulk-editing">
    <div class="tag-wrap">
    {{foreach item=item from=$modifies}}
        <table cellspacing="0" cellpadding="0" class="flagbg" style="background-color:#729C3B;">
            <tr class="falg-rounded-wrap">
                <td class="falg-rounded"></td>
                <td colspan="2"></td>
                <td class="falg-rounded"></td>
            </tr>
            <tr>
                <td class="falg-line"></td>
                <td class="tag-txt"><input type="hidden" value="{{$item.userid}}" name="userid[]" />{{$item.truename}}</td>
                <td class="tag-close">&nbsp;</td>
                <td class="falg-line"></td>
            </tr>
            <tr class="falg-rounded-wrap">
                <td class="falg-rounded"></td>
                <td colspan="2"></td>
                <td class="falg-rounded"></td>
            </tr>
        </table>
    {{/foreach}}
    </div>
    <fieldset class="form-field">
        <legend><label for="check-password"><input id="check-password" type="checkbox" value="1" name="edit-password" /><strong>修改密码</strong></label></legend>
        <div class="line"></div>
        <div class="edit-field">
            <input name="password" id="password" type="text" class="text" value="" maxlength="16" style="ime-mode:disabled;width:250px" disabled="disabled" />
        </div>
    </fieldset>
    <fieldset class="form-field">
        <legend><label for="check-status"><input id="check-status" name="edit-status" type="checkbox" value="1" /><strong>修改帐号状态</strong></label></legend>
        <div class="line"></div>
        <div class="edit-field">
            <select id="status" name="status" style="width:256px;" disabled="disabled">
                <option value="">帐号状态</option>
                <option value="1">正式</option>
                <option value="2">临时</option>
                <option value="0">停用</option>
            </select>
        </div>
    </fieldset>
    <fieldset class="form-field">
        <legend><label for="check-department"><input id="check-department" type="checkbox" name="edit-department" value="1" /><strong>修改所属部门</strong></label></legend>
        <div class="line"></div>
        <div class="edit-field">
            <p><select name="deptid" id="department" style="width:256px;" disabled="disabled">
            <option value="">请选择部门</option>
            {{foreach item=item from=$depts}}
            {{if $item.deptid != '^root'}}
            <option value="{{$item.deptid}}"{{if $user.deptid == $item.deptid}} selected="selected"{{/if}}>{{$item.prefix}}{{$item.deptname}}</option>
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
                <input name="deptname" type="text" class="text" value="" style="width:83px;" />
            </p>
        </div>
    </fieldset>
    <fieldset class="form-field">
        <legend><label for="check-netdisk"><input id="check-netdisk" type="checkbox" value="1" name="edit-netdisk" /><strong>修改网盘空间</strong></label></legend>
        <div class="line"></div>
        <div class="edit-field">
            <input name="ndquota" id="ndquota" type="text" class="text" value="" style="ime-mode:disabled;width:250px" disabled="disabled" onkeyup="(this.v=function(){this.value=this.value.replace(/[^0-9.]+/,'');}).call(this)" /><span class="gray">&nbsp;MB</span>
        </div>
    </fieldset>
    <fieldset class="form-field">
        <legend><label for="check-role"><input id="check-role" type="checkbox" name="edit-role" value="1" /><strong>修改帐号权限</strong></label></legend>
        <div class="line"></div>
        <div class="edit-field">
        <div id="role-list" {{if count($roles) > 5}} class="box" style="width:250px"{{/if}}>
        {{foreach item=item from=$roles}}
        <p><label for="role-{{'^'|str_replace:'_':$item.roleid}}"><input type="checkbox" value={{$item.roleid}} name="roleid[]" id="role-{{'^'|str_replace:'_':$item.roleid}}" />{{$item.rolename}}</label>{{if $item.roleid == '^user'}}(基本权限){{elseif $item.roleid == '^advanced'}}(中高层管理者权限){{elseif $item.roleid == '^admin'}}(拥有前后台最高权限){{/if}}</p>
        {{/foreach}}
        </div>
        </div>
    </fieldset>
    <fieldset class="form-field">
        <legend><label for="check-group"><input id="check-group" type="checkbox" name="edit-group" /><strong>修改所属群组</strong></label></legend>
        <div class="line"></div>
        <div class="edit-field">
        <div id="group-list" {{if count($groups) > 5}} class="box"{{/if}}>
        {{foreach item=item from=$groups}}
        <p><label for="group-{{'^'|str_replace:'_':$item.groupid}}"><input type="checkbox" value={{$item.groupid}} name="groupid[]" id="group-{{'^'|str_replace:'_':$item.groupid}}" />{{$item.groupname}}</label></p>
        {{/foreach}}
        </div>
        <p style="margin:8px 0;"><input name="groupname" id="groupname" type="text" class="text" title="请输入新群组名称" style="width:250px" />&nbsp;<a id="create-group" class="icon icon-add" href="javascript:void(0);"></a></p>
        </div>
    </fieldset>
    <fieldset class="form-field" style="margin-bottom:40px">
        <legend><label for="check-cast"><input id="check-cast" type="checkbox" name="edit-cast" /><strong>修改组织架构视图</strong></label></legend>
        <div class="line"></div>
        <div class="edit-field">
        <div class="box">
        <div id="tree-ct"></div>
        </div>
        </div>
    </fieldset>
<div class="tool-btm"><div class="toolbar"><input type="submit" class="btn wd50" value="保 存"/>&nbsp;<input type="button" class="btn wd50" value="取 消" onclick="location.href='{{$back|default:"/user/user"}}'" /></div>
</form>
<script type="text/javascript">
<!--
var users = [],depts = [];
{{foreach from=$users item=item}}
users.push({userid:'{{$item.userid|escape:'html'}}', truename: '{{$item.truename|escape:'html'}}', deptid: '{{$item.deptid|replace:"^":"_"|default:"_root"}}', checked: true});
{{/foreach}}
{{foreach from=$depts item=item}}
depts.push({deptid:'{{$item.deptid|replace:"^":"_"}}', deptname: '{{$item.deptname|escape:'html'}}', parentid: '{{$item.parentid|replace:"^":"_"}}', checked: true});
{{/foreach}}
var _ORG_NAME = '{{$org.orgname}}';
_TOP.switchMod('user');
User.users = users;
User.depts = depts;
User.initBatchEdit();
-->
</script>
</body>
</html>
