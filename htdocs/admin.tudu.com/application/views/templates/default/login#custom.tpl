<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>后台登陆</title>
<link href="{{$options.sites.www}}/css/custom.css" type="text/css" rel="stylesheet">
{{if array_key_exists('selected', $loginskin) && $loginskin.selected.type == 'color'}}
<style type="text/css">
body {background-color:{{$loginskin.selected.color}};}
</style>
{{/if}}
<!--[if lt IE 9]>
<script src="{{$options.sites.www}}/js/html5.js" type="text/javascript"></script>
<style type="text/css">
.login-box{
    background-color:#1b1b1b;
    filter:alpha(opacity=40);
}
.login-box .logo,
.login-box form{
    position:relative;
}
.cover-layer{
    background-color:#000;
    filter:alpha(opacity=20);
    *width:100%;
}
.content{
    *padding-top:100px;
}
.cover-layer{
    *top:200px;
}
</style>
<![endif]-->
<script type="text/javascript">
<!--
if(self != top) {
    top.location = self.location;
}
-->
</script>
</head>
<body>
<div class="main" id="main">
    <div class="cover-layer"></div>
    <div class="content" id="content">
        <div class="info-box">
            <div class="title">{{if !$org.orgname}}办公，变得如此轻松和简单...{{else}}{{if $org.orgname}}{{$org.orgname}}{{else}}{{$org.entirename}}{{/if}}{{/if}}</div>
            <div>
                {{$org.intro|escape:'html'|nl2br}}
            </div>
        </div>
        <div class="login-box">
            <div class="logo"><img src="{{$options.sites.www}}/logo?oid={{$org.orgid}}&white=1" border="0" class="png"></div>
            <form action="{{$options.sites.www}}/login/login-admin" method="post" id="loginform">
                <table width="100%" border="0" cellspacing="0" cellpadding="7">
                    <tr>
                        <th align="right"></th>
                        <td><div class="login-title">图度后台管理系统</div></td>
                    </tr>
                    <tr>
                        <th align="right"></th>
                        <td style="padding-top:0; padding-bottom:0; height:22px;"><label class="tips">{{$err}}</label></td>
                    </tr>
                    <tr>
                        <th align="right">帐号：<input type="hidden" name="email" value="{{$address}}" /></th>
                        <td><div class="input-text-wrap"><input id="uid" name="uid" type="text" class="text" size="30" value="{{$address}}" disabled="disabled"><img id="avatar" src="{{$options.sites.www}}/logo/?email={{$address}}" style="width:34px;height:34px" /></div></td>
                    </tr>
                    <tr>
                        <th align="right">密码：</th>
                        <td><input id="password" name="password" type="password" size="30" autocomplete="off" class="text"></td>
                    </tr>
                    <tr id="seccode-tr">
                        <th align="right" valign="top" style="padding-top:18px;padding-bottom:0">验证码：</th>
                        <td align="left" style="padding-bottom:0"><input name="seccode" type="text" size="30" class="text"><br /><label><img id="img-seccode" src="{{$options.sites.www}}/images/spacer.gif" style="margin:14px 10px 10px 0;vertical-align:middle" />&nbsp;&nbsp;<a href="javascript:void(0)" onclick="refreshSeccode();return false;">点击换一张</a></label></td>
                    </tr>
                    <tr>
                        <th align="right">&nbsp;</th>
                        <td><button class="btn-login" type="submit">登录</button></td>
                    </tr>
                </table>

            </form>
        </div>
        <div class="clear"></div>
    </div>
    <footer>
        <p align="left">
            <a href="{{$options.sites.www}}/about/about.html" target="_blank">关于我们</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{$options.sites.www}}/about/contact.html" target="_blank">联系我们</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{$options.sites.www}}/about/privacy.html" target="_blank">隐私保护</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{$options.sites.www}}/about/copyright.html" target="_blank">版权声明</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{$options.sites.www}}/help/index.html" target="_blank">相关帮助</a>
        </p>
        <p class="copyright"><a href="http://www.miibeian.gov.cn/" target="_blank" style="margin-right:5px">粤ICP备09187954号</a>  Copyright &copy; 2012 tudu.com</p>
    </footer>
</div>
<!--背景图-->
{{if array_key_exists('selected', $loginskin) && $loginskin.selected.type == 'pic'}}
<div id="background-image">
{{*
    {{if $loginskin.selected.issystem}}
    <img src="{{$options.sites.www}}/img/login/{{$loginskin.selected.pic}}" width="1680" height="1123" />
    {{else}}
    <img src="{{$options.sites.www}}/file/?hash={{$loginskin.selected.pic}}&type=loginpic" width="1680" height="1123" />
    {{/if}}
*}}
</div>
{{/if}}
</body>
<script src="{{$options.sites.www}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script type="text/javascript">
function refreshSeccode() {
    document.getElementById('img-seccode').src = '{{$options.sites.www}}/seccode/?ns=adlogin&sz=100x36&rand=' + Math.random();
    document.getElementById('seccode-tr').style.display = '';
}
$(function(){
    refreshSeccode();
    $('#password').focus();

    function onResize(){
        $('#main').css({'margin-top': 'auto'});
        var offset = $('#content').offset();
        if (offset.top < 0) {
            $('#main').css({'margin-top': '0px'});
        }
    }
    window.onresize = onResize;
    onResize();
});
</script>
<script src="{{$options.sites.www}}/seccode/check?cb=checkSeccode&ns=adlogin" type="text/javascript"></script>
{{if array_key_exists('selected', $loginskin) && $loginskin.selected.type == 'pic'}}
<script type="text/javascript" src="{{$options.sites.www}}/js/jquery.fullscreen.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    var img = new Image();
    img.width = 1680;
    img.height = 1123;

    {{if $loginskin.selected.issystem}}
    var src="{{$options.sites.www}}/images/login/{{$loginskin.selected.pic}}";
    {{else}}
    var src="{{$options.sites.www}}/file/?hash={{$loginskin.selected.pic}}&type=loginpic";
    {{/if}}

    $(img).load(function() {
        $(this).hide();
        $("#background-image").append(this);
        //$(this).fadeIn("fast");
        $(this).show();
        $("#background-image").fullscreenBackground();
    }).error(function(){
    }).attr('src', src);
});
</script>
{{/if}}
</html>