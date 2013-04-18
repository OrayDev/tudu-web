<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>超级管理员密码修改</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
</head>

<body>
<div class="title-bar"><strong class="f14">修改密码</strong></div>
<input type="hidden" id="orgid" value="{{$admin.orgid}}" />
<input type="hidden" id="userid" value="{{$admin.userid}}" />
<form action="{{$basepath}}/org/password/save" method="post" id="pwdform">
    <p class="gray" style="margin:8px 0;">由6-16个字符组成，禁止使用全数字、全字母或连续字符作为密码，建议设置使用英文字母加数字或符号的组合密码。</p>
    <table border="0" cellspacing="0" cellpadding="5" class="table-form">
        <tr>
            <th align="right">现用密码：</th>
            <td><input class="text-big" name="oldpwd" type="password" autocomplete="off" size="45" style="width:360px;"></td>
        </tr>
        <tr>
            <th align="right">新密码：</th>
            <td><input class="text-big" name="pwd"  type="password" autocomplete="off" size="45" style="width:360px;"></td>
        </tr>
        <tr>
            <th align="right"></th>
            <td><p class="gray password-tip">密码强度：</span> 弱<span id="pwdlevel"></span>强</p></td>
        </tr>
        <tr>
            <th align="right">确认密码：</th>
            <td><input class="text-big" name="repwd"  type="password" autocomplete="off" size="45" style="width:360px;"></td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <td><input name="" type="submit" value="保存修改" class="btn-big"></td>
        </tr>
    </table>
</form>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/org.js?1000" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/base.js?1000" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/levelbar.js?1000" type="text/javascript"></script>
<script type="text/javascript">
<!--
$(function() {
	Org.Password.init();
});
-->
</script>
</body>
</html>