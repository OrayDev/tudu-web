<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>图度后台</title>
{{include file="^style.tpl"}}
<!--[if IE 6]>
<script type="text/javascript" src="{{$options.sites.static}}/js/ie6-fix.js"></script>
<script type="text/javascript">
DD_belatedPNG.fix('.png, background,img');
</script>
<![endif]-->
<script type="text/javascript">
if (top != self) {
	top.location = self.location.href;
}
</script>
</head>
<body style="padding:0;margin:0">
{{include file="^header.tpl"}}
{{include file="^nav.tpl"}}
<div class="container-main">
    <div class="home">
        <div class="home-right">
            <div class="user-info-wrap">
                <div class="user-info" style="height: 217px">
                    <div class="info-item user-logo">
                        <div class="info-title"><span>组织LOGO</span></div>
                        <p><a href="{{$basepath}}/org/#{{$basepath}}/org/info/logo"><img src="{{$options.sites.www}}/logo/?oid={{$org.orgid}}&t=1&r={{0|rand:9999}}" border="0"></a></p>
                    </div>
                    <div class="info-item">
                        <div class="info-title"><span>登录地址</span></div>
                        <p class="user-url">{{$host}}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="home-left">
            <div class="functional-block">
                <table border="0" cellspacing="10" cellpadding="0" width="100%">
                    <tr>
                        <td><div class="functional-accounts"><a href="{{$basepath}}/frame/#{{$basepath}}/user/user/" class="png"><p class="functional-title">帐号</p><span style="line-height:20px">已开通{{$count.user}}个帐号</span></a></div></td>
                        <td><div class="functional-structure"><a href="{{$basepath}}/frame/#{{$basepath}}/user/department/" class="png"><p class="functional-title">组织架构</p><span style="line-height:20px">已建部门数：共{{$count.dept}}个</span></a></div></td>
                        <td><div class="functional-permissions"><a href="{{$basepath}}/frame/#{{$basepath}}/user/role/" class="png"><p class="functional-title">权限</p><span style="line-height:20px">已建权限组：共{{$count.role}}个</span></a></div></td>
                    </tr>
                    <tr>
                        <td><div class="functional-group"><a href="{{$basepath}}/frame/#{{$basepath}}/user/group/" class="png"><p class="functional-title">群组</p><span style="line-height:20px">已建群组：共{{$count.group}}个</span></a></div></td>
                        <td><div class="functional-board"><a href="{{$basepath}}/frame/#{{$basepath}}/board/board/" class="png"><p class="functional-title">分区管理</p><span style="line-height:20px">已建分区：共{{$count.board}}个</span></a></div></td>
                        <td><div class="functional-safety"><a href="{{$basepath}}/frame/#{{$basepath}}/secure/index/" class="png"><p class="functional-title">系统安全</p><span style="line-height:20px">安全等级：{{if $secure >= 80}}高{{elseif $secure >= 55}}中{{else}}低{{/if}}</span></a></div></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="footer">
    <a href="http://www.tudu.com/about/about.html" target="_blank">关于我们</a> |
    <a href="http://www.tudu.com/about/contact.html" target="_blank">联系我们</a> |
    <a href="http://www.tudu.com/about/privacy.html" target="_blank">隐私保护</a> |
    <a href="http://www.tudu.com/about/copyright.html" target="_blank">版权声明</a> |
    <a href="http://www.tudu.com/help/index.html" target="_blank">相关帮助</a>
    <p>Copyright © 2013 tudu.com</p>
</div>
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
{{if $guidetips}}
<script src="{{$options.sites.static}}/js/guide.js" type="text/javascript"></script>
{{/if}}
<script type="text/javascript">
<!--
{{if $guidetips}}
var BASE_PATH = '{{$basepath}}';
Guide.init();
{{/if}}
-->
</script>
</body>
</html>