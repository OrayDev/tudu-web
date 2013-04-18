<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>密保邮箱</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/org.js?1000" type="text/javascript"></script>
</head>
<body>
<div class="title-bar"><strong class="f14">验证邮件已发到新的密保邮箱</strong></div>
<p class="f14" style="margin-top:8px;">新邮箱（<strong class="red">{{$auth.email}}</strong>）需验证后才生效，请登录新的密保邮箱进行验证。</p>
<p>&nbsp;</p>
{{if $loginurl}}
<p><input onclick="window.open('{{$loginurl}}')" type="button" value="登录邮箱" class="btn-big" /></p>
{{/if}}
<p>&nbsp;</p>
<div class="tips-list">
    <strong>没收到验证邮件？</strong>
    <ul>
        <li>请看看是否在邮箱的垃圾邮件目录里</li>
        <li>稍等一会儿，若仍旧没收到验证邮件，请 <a href="javascript:void(0)" onclick="Org.Email.send('{{$auth.emailauthid}}');">点击这里</a> 重新发送</li>
    </ul>
</div>
</body>
</html>
