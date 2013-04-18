<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>权限管理</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/role.js?1003" type="text/javascript"></script>
</head>
<body>

<div class="title-bar"><strong class="f14 text-title">编辑权限</strong> - <span class="icon{{if $role.issystem}} icon-group{{else}} icon-group-senior{{/if}}"></span><strong>{{$role.rolename}}</strong> | <a href="{{if $back}}{{$back}}{{else}}{{$basepath|cat:'/user/role'}}{{/if}}">返回</a></div>
<form id="accessform" action="{{$basepath}}/user/role/save.access" method="post" class="permission">
    <input type="hidden" name="roleid" value="{{$role.roleid}}" />
    {{if $role.issystem && $role.adminlevel == 3}}
    <fieldset class="form-field">
        <legend><strong>后台权限</strong></legend>
        <div class="line"></div>
        <table border="0" cellspacing="2" cellpadding="3">
            <tr>
                <td width="135">可登录图度后台</td>
                <td width="85"><label for="admin-log-allow"><input id="admin-log-allow" name="access-login" type="radio" value="1" checked="checked" disabled />允许</label></td>
                <td><label for="admin-log-not"><input id="admin-log-not" name="access-login" type="radio" value="0" disabled />不允许</label></td>
            </tr>
        </table>
    </fieldset>
    {{/if}}
    <fieldset class="form-field">
        <legend><strong>基本设置</strong></legend>
        <div class="line"></div>
        <table border="0" cellspacing="2" cellpadding="3">
            <tr>
                <td width="135">自定义皮肤</td>
                <td width="85"><label for="skin-allow"><input id="skin-allow" name="access-102" type="radio" value="1"{{if $accesses.102.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td width="85"><label for="skin-not"><input id="skin-not" name="access-102" type="radio" value="0"{{if !$accesses || $accesses.102.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray" id="role-skin" style="display:none">在前台更换皮肤；</span></td>
            </tr>
        </table>
    </fieldset>
    <fieldset class="form-field">
        <legend><strong>版块</strong></legend>
        <div class="line"></div>
        <table border="0" cellspacing="2" cellpadding="3">
            <tr>
                <td width="135">新建版块</td>
                <td width="85"><label for="board-new-allow"><input id="board-new-allow" name="access-201" type="radio" value="1"{{if $accesses.201.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td width="85"><label for="board-new-not"><input id="board-new-not" name="access-201" type="radio" value="0"{{if !$accesses || $accesses.201.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-new-board" style="display:none">创建版块；</span></td>
            </tr>
            <tr>
                <td>编辑版块</td>
                <td><label for="board-edit-allow"><input id="board-edit-allow" name="access-202" type="radio" value="1"{{if $accesses.202.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="board-edit-not"><input id="board-edit-not" name="access-202" type="radio" value="0"{{if !$accesses || $accesses.202.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-edit-board" style="display:none">可修改版块（仅针对于分区和版块的创建人、负责人）；</span></td>
            </tr>
            <tr>
                <td>删除版块</td>
                <td><label for="board-del-allow"><input id="board-del-allow" name="access-203" type="radio" value="1"{{if $accesses.203.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="board-del-not"><input id="board-del-not" name="access-203" type="radio" value="0"{{if !$accesses || $accesses.203.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-del-board" style="display:none">可删除版块（仅针对于分区和版块的创建人、负责人）；</span></td>
            </tr>
            <tr>
                <td>关闭版块</td>
                <td><label for="board-close-allow"><input id="board-close-allow" name="access-204" type="radio" value="1"{{if $accesses.204.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="board-close-not"><input id="board-close-not" name="access-204" type="radio" value="0"{{if !$accesses || $accesses.204.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-close-board" style="display:none">可临时关闭版块（仅针对于分区和版块的创建人、负责人）；</span></td>
            </tr>
        </table>
    </fieldset>
    <fieldset class="form-field">
        <legend><strong>主题</strong></legend>
        <div class="line"></div>
        <table border="0" cellspacing="2" cellpadding="3">
            <tr>
                <td width="135">发起图度</td>
                <td width="85"><label for="tudu-new-allow"><input id="tudu-new-allow" name="access-301" type="radio" value="1"{{if $accesses.301.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td width="85"><label for="tudu-new-not"><input id="tudu-new-not" name="access-301" type="radio" value="0"{{if !$accesses || $accesses.301.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-new-tudu" style="display:none">允许创建图度任务；</span></td>
            </tr>
            <tr>
                <td>发起讨论</td>
                <td><label for="discuss-new-allow"><input id="discuss-new-allow" name="access-501" type="radio" value="1"{{if $accesses.501.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="discuss-new-not"><input id="discuss-new-not" name="access-501" type="radio" value="0"{{if !$accesses || $accesses.501.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-new-discuss" style="display:none">允许创建讨论；</span></td>
            </tr>
            <tr>
                <td>发起公告</td>
                <td><label for="notice-new-allow"><input id="notice-new-allow" name="access-502" type="radio" value="1"{{if $accesses.502.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="notice-new-not"><input id="notice-new-not" name="access-502" type="radio" value="0"{{if !$accesses || $accesses.502.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-new-notice" style="display:none">允许创建公告；</span></td>
            </tr>
            <tr>
                <td>发起会议</td>
                <td><label for="meeting-new-allow"><input id="meeting-new-allow" name="access-504" type="radio" value="1"{{if $accesses.504.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="meeting-new-not"><input id="meeting-new-not" name="access-504" type="radio" value="0"{{if !$accesses || $accesses.504.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-new-meeting" style="display:none">允许创建会议；</span></td>
            </tr>
            <tr>
                <td>发表回复</td>
                <td><label for="reply-post-allow"><input id="reply-post-allow" name="access-302" type="radio" value="1"{{if $accesses.302.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="reply-post-not"><input id="reply-post-not" name="access-302" type="radio" value="0"{{if !$accesses || $accesses.302.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-new-reply" style="display:none">可回复图度任务/讨论/会议；</span></td>
            </tr>
            <tr>
                <td>编辑图度</td>
                <td><label for="tudu-edit-allow"><input id="tudu-edit-allow" name="access-303" type="radio" value="1"{{if $accesses.303.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="tudu-edit-not"><input id="tudu-edit-not" name="access-303" type="radio" value="0"{{if !$accesses || $accesses.303.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-edit-tudu" style="display:none">可修改图度（针对于分区和版块的创建人/负责人，以及该图度的发起人）；</span></td>
            </tr>
            <tr>
                <td>编辑回复</td>
                <td><label for="reply-edit-allow"><input id="reply-edit-allow" name="access-304" type="radio" value="1"{{if $accesses.304.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="reply-edit-not"><input id="reply-edit-not" name="access-304" type="radio" value="0"{{if !$accesses || $accesses.304.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-edit-reply" style="display:none">可对自己的回复进行修改；</span></td>
            </tr>
            <tr>
                <td>删除图度</td>
                <td><label for="tudu-del-allow"><input id="tudu-del-allow" name="access-305" type="radio" value="1"{{if $accesses.305.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="tudu-del-not"><input id="tudu-del-not" name="access-305" type="radio" value="0"{{if !$accesses || $accesses.305.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-del-tudu" style="display:none">可删除图度（针对于分区和版块的创建人/负责人，以及该图度的发起人）；</span></td>
            </tr>
            <tr>
                <td>删除回复</td>
                <td><label for="reply-del-allow"><input id="reply-del-allow" name="access-306" type="radio" value="1"{{if $accesses.306.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="reply-del-not"><input id="reply-del-not" name="access-306" type="radio" value="0"{{if !$accesses || $accesses.306.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-del-reply" style="display:none">可删除回复（针对于分区和版块的创建人/负责人，以及该回复的发起人）；</span></td>
            </tr>
            <tr>
                <td>转发图度</td>
                <td><label for="tudu-transpond-allow"><input id="tudu-transpond-allow" name="access-307" type="radio" value="1"{{if $accesses.307.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="tudu-transpond-not"><input id="tudu-transpond-not" name="access-307" type="radio" value="0"{{if !$accesses || $accesses.307.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-forward-tudu" style="display:none">当您是图度任务执行人时，是否可转发该图度任务；</span></td>
            </tr>
            <tr>
                <td>添加到图度组</td>
                <td><label for="tudu-merge-allow"><input id="tudu-merge-allow" name="access-308" type="radio" value="1"{{if $accesses.308.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="tudu-merge-not"><input id="tudu-merge-not" name="access-308" type="radio" value="0"{{if !$accesses || $accesses.308.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-merge-tudu" style="display:none">当您是图度任务执行人或发起人时，是否可添加到图度组；</span></td>
            </tr>
        </table>
    </fieldset>
    <fieldset class="form-field">
        <legend><strong>附件</strong></legend>
        <div class="line"></div>
        <table border="0" cellspacing="2" cellpadding="3">
            <tr>
                <td width="135">发布附件</td>
                <td width="85"><label for="ext-publish-allow"><input id="ext-publish-allow" name="access-402" type="radio" value="1"{{if $accesses.402.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td width="85"><label for="ext-publish-not"><input id="ext-publish-not" name="access-402" type="radio" value="0"{{if !$accesses || $accesses.402.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-attach" style="display:none">可在创建图度和回复中上传附件；</span></td>
            </tr>
        </table>
    </fieldset>
    <fieldset class="form-field">
        <legend><strong>工作流权限</strong></legend>
        <div class="line"></div>
        <table border="0" cellspacing="2" cellpadding="3">
            <tr>
                <td width="135">新建工作流</td>
                <td width="85"><label for="flow-create-allow"><input id="flow-create-allow" name="access-511" type="radio" value="1"{{if $accesses.511.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td width="85"><label for="flow-create-not"><input id="flow-create-not" name="access-511" type="radio" value="0"{{if !$accesses || $accesses.511.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-create-flow" style="display:none">可新建工作流；</span></td>
            </tr>
            <tr>
                <td width="135">编辑工作流</td>
                <td width="85"><label for="flow-update-allow"><input id="flow-update-allow" name="access-512" type="radio" value="1"{{if $accesses.512.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td width="85"><label for="flow-update-not"><input id="flow-update-not" name="access-512" type="radio" value="0"{{if !$accesses || $accesses.512.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-update-flow" style="display:none">可修改工作流（仅针对于工作流的创建人）；</span></td>
            </tr>
            <tr>
                <td width="135">删除工作流</td>
                <td width="85"><label for="flow-delete-allow"><input id="flow-delete-allow" name="access-513" type="radio" value="1"{{if $accesses.513.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td width="85"><label for="flow-delete-not"><input id="flow-delete-not" name="access-513" type="radio" value="0"{{if !$accesses || $accesses.513.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-delete-flow" style="display:none">可删除工作流（仅针对于工作流的创建人）；</span></td>
            </tr>
        </table>
    </fieldset>
    <fieldset class="form-field" style="margin-bottom:40px;">
        <legend><strong>图度Talk权限</strong></legend>
        <div class="line"></div>
        <table border="0" cellspacing="2" cellpadding="3">
            <tr>
                <td width="135">发起讨论组</td>
                <td width="85"><label for="talk-discuss-allow"><input id="talk-discuss-allow" name="access-1001" type="radio" value="1"{{if !$accesses || $accesses.1001.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td width="85"><label for="talk-discuss-not"><input id="talk-discuss-not" name="access-1001" type="radio" value="0"{{if $accesses && $accesses.1001.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-talk-discuss" style="display:none">可在聊天工具Talk上发起讨论组；</span></td>
            </tr>
            <tr>
                <td>添加外部好友</td>
                <td width="85"><label for="talk-add-allow"><input id="talk-add-allow" name="access-1002" type="radio" value="1"{{if $accesses.1002.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="talk-add-not"><input id="talk-add-not" name="access-1002" type="radio" value="0"{{if !$accesses || $accesses.1002.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-talk-add" style="display:none">可在聊天工具Talk上添加外部好友；</span></td>
            </tr>
            <tr>
                <td>被外部好友添加</td>
                <td width="85"><label for="talk-added-allow"><input id="talk-added-allow" name="access-1003" type="radio" value="1"{{if $accesses.1003.value == 1}} checked="checked"{{/if}} />允许</label></td>
                <td><label for="talk-added-not"><input id="talk-added-not" name="access-1003" type="radio" value="0"{{if !$accesses || $accesses.1003.value == 0}} checked="checked"{{/if}} />不允许</label></td>
                <td><span class="gray role-intro" id="role-talk-added" style="display:none">他人可通过聊天工具Talk上查找到您，并添加为好友；</span></td>
            </tr>
        </table>
    </fieldset>

<div class="tool-btm"><div class="toolbar toolbar-bottom"><input name="save" type="button" class="btn wd50" value="提 交"/>&nbsp;<input name="cancel" type="button" class="btn wd50" value="取 消"/></div></div>
</form>


<script type="text/javascript">
<!--
$(function() {
    Cookie.set('FOCUS-ROLE', '{{$role.roleid}}');
    _TOP.switchMod('role');
    Role.initModify();
});
-->
</script>

</body>
</html>