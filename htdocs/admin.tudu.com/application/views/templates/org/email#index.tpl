<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>修改密保邮箱</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<style type="text/css">
.tui-ac-menu {
    position:absolute;
    background: #ffffff;
    border-style:solid;
    border-width:1px;
}
.autocomplete-menu {
    border-color:#1A4937;
}
.autocomplete-menu .tui-ac-item {
    padding:0 3px;
    height:26px;
    line-height:26px;
    color:#666;
    cursor:normal;
}
.autocomplete-menu .tui-ac-item-hover {
    background-color: #629582;
    color:#fff
}
</style>
</head>

<body>
<form action="{{$basepath}}/org/email/save" method="post" id="emailform">
    <p class="gray" style="margin:8px 0;">忘记密码时，可用密保邮箱找回密码。</p>
    <table border="0" cellspacing="0" cellpadding="5" class="table-form">
        <tr>
            <th align="right">现用邮箱：</th>
            <td>{{$email.email|default:'-'}}</td>
        </tr>
        <tr>
            <th align="right">新邮箱：</th>
            <td><input id="email" class="text-big" name="email"  type="text" autocomplete="off" size="45" style="width:360px;"></td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <td><input type="submit" value="保存修改" class="btn-big"></td>
        </tr>
    </table>
</form>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/www/register.source.js" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/base.js" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/autocomplete.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/org.js?1000" type="text/javascript"></script>
<script type="text/javascript">
<!--
$(function() {
	Org.Email.init();
});
-->
</script>
</body>
</html>