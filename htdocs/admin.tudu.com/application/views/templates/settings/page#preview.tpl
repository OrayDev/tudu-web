<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>预览 - 登录图度</title>
{{if $loginskin.value == 'SYS:default'}}
<link href="{{$options.sites.www}}/css/login.css" type="text/css" rel="stylesheet" />
<!--[if IE 6]>
<script type="text/javascript" src="{{$options.sites.www}}/js/ie6-fix.js"></script>
<script type="text/javascript">
DD_belatedPNG.fix('.png, background,img');
</script>
<![endif]-->
</head>
<body>
<div class="header" style="height:50px;" id="header"></div>
<div class="login-container" id="container-height">
    <div class="png login-cover-layer">
        <div class="login-content" id="main">
            <div class="login-left">
                <div class="login-left-header">
                    <div class="title">{{if !$org.orgname}}办公，变得如此轻松和简单...{{else}}{{if $org.orgname}}{{$org.orgname}}{{else}}{{$org.entirename}}{{/if}}{{/if}}</div>
                </div>
                <div id="msg-ct" class="png{{if !$org.intro && !$error}} login-left-body-common{{else}} login-left-body{{/if}}">
                    <div class="company-info" style="height:340px;padding:30px 0;overflow:hidden">
                        {{$org.intro|escape:'html'|nl2br}}
                    </div>
                </div>
            </div>
            <div class="login-right">
                <div class="login-box">
                    <div class="login-box-header">
                        <div class="login-logo"><img src="{{$options.sites.www}}/logo?oid={{$org.orgid}}" class="png" border="0"></div>
                    </div>
                    <div class="login-box-body">
                        <form action="{{$options.sites.www}}/login/" method="post" class="login-form" id="loginform">
                            <table border="0" cellpadding="0" cellspacing="0" align="center">
                                <tr>
                                    <th align="right">&nbsp;</th>
                                    <td align="left"><div class="login-title">登录图度云办公系统</div></td>
                                </tr>
                                <tr>
                                    <th align="right" style="padding-top:0;padding-bottom:0;"></th>
                                    <td align="left" style="padding-top:0;padding-bottom:0;height:16px"><div class="red" style="font-size:12px;color:#f00">{{if $error}}{{$lang[$error]}}{{/if}}</div></td>
                                </tr>
                                <tr>
                                    <th align="right">帐号：</th>
                                    <td align="left"><div class="input-text-wrap"><input id="uid" name="uid" type="text" size="30" class="input-text" value="{{$address}}" style="width:280px;"><img id="avatar" src="{{$options.sites.www}}/logo/?email={{$address}}" style="width:34px;height:34px" /></div></td>
                                </tr>
                                <tr>
                                    <th align="right">密码：</th>
                                    <td align="left"><input id="password" name="password" type="password" size="30" class="input-text" style="width:280px;"></td>
                                </tr>
                                <tr id="seccode-tr">
                                    <th align="right" valign="top" style="padding-top:22px;padding-bottom:0">验证码：</th>
                                    <td align="left" style="padding-bottom:0"><input name="seccode" type="text" size="30" class="input-text" style="width:280px;" /><br /><img id="img-seccode" src="{{$options.sites.www}}/images/spacer.gif" style="margin:14px 10px 10px 0;vertical-align:middle" /><a href="javascript:void(0)" onclick="refreshSeccode();return false;">点击换一张</a></td>
                                </tr>
                                <tr>
                                    <th align="right">&nbsp;</th>
                                    <td align="left"><label for="remember"><input id="remember" name="remember" type="checkbox" value="1" /><span class="gray">保持登录状态</span></label></td>
                                </tr>
                                <tr>
                                    <th align="right">&nbsp;</th>
                                    <td align="left"><button type="submit" class="btn"><span class="btn-left"><span class="btn-right"><span class="btn-inner" style="*width:40px;">登录</span></span></span></button>&nbsp;&nbsp;<a href="{{$options.sites.www}}/password/?oid={{$org.orgid}}">忘记密码?</a></td>
                                </tr>
                                <tr>
                                    <th align="right">&nbsp;</th>
                                    <td align="left">&nbsp;</td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>

<div class="footer" id="footer">
    <p class="footer-link"><a href="{{$options.sites.www}}/about/privacy.html" target="_blank">隐私保护</a><a href="{{$options.sites.www}}/help/index.html" target="_blank">相关帮助</a><a href="{{$options.sites.www}}/suggest" target="_blank">反馈建议</a></p>
    <p class="copyright"><a href="http://www.miibeian.gov.cn/" target="_blank" style="margin-right:5px">粤ICP备09187954号</a> Copyright © 2012 tudu.com</p>
</div>
</body>
<script src="{{$options.sites.www}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.www}}/seccode/check?cb=checkSeccode&ns=login" type="text/javascript"></script>
<script type="text/javascript">
function refreshSeccode() {
    document.getElementById('img-seccode').src = '{{$options.sites.www}}/seccode/?ns=adlogin&sz=100x36&rand=' + Math.random();
    document.getElementById('seccode-tr').style.display = '';
}
$(function(){
    var f = $('#footer').height(),
    h = $('#container-height').height(),
    mh = $('#main').outerHeight();

    function onResize(){
        var dh = document.body.offsetHeight;
            t = Math.max(30, Math.round(dh/2 - mh/2))
            height = dh - t - f - 1;

        $('#header').css('height', t + 'px');
        height = height > h ? height : h;
        $('#container-height').css('height',height);
    }
    window.onresize = onResize;
    onResize();

    document.getElementById('loginform').onsubmit = function(){
        return false;
    };

    $('input:text,input:password').bind('focus blur mouseover mouseout', function(e) {
        if (e.type == 'focus' || e.type == 'mouseover') {
            $(this).addClass('input-text-hover');
        } else {
            if (e.type == 'blur' || document.activeElement != this) {
                $(this).removeClass('input-text-hover');
            }
        }
    });

    $('#password').focus();
    refreshSeccode();
});
</script>
{{else}}
<link href="{{$options.sites.www}}/css/custom.css" type="text/css" rel="stylesheet">
{{if $loginskin.type == 'color'}}
<style type="text/css">
body {background-color:{{$loginskin.color}};}
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
            <form action="{{$options.sites.www}}/login/" method="post" id="loginform">
                <table width="100%" border="0" cellspacing="0" cellpadding="7">
                    <tr>
                        <th align="right"></th>
                        <td><div class="login-title">登录图度云办公系统</div></td>
                    </tr>
                    <tr>
                        <th align="right"></th>
                        <td style="padding-top:0; padding-bottom:0; height:22px;"><label class="tips">{{if $error}}{{$lang[$error]}}{{/if}}</label></td>
                    </tr>
                    <tr>
                        <th align="right">帐号：</th>
                        <td><div class="input-text-wrap"><input id="uid" name="uid" type="text" class="text" value="{{$address}}" size="30"><img id="avatar" src="{{$options.sites.www}}/logo/?email={{$address}}" style="width:34px;height:34px" /></div></td>
                    </tr>
                    <tr>
                        <th align="right">密码：</th>
                        <td><input id="password" name="password" type="password" size="30" autocomplete="off" class="text"></td>
                    </tr>
                    <tr id="seccode-tr">
                        <th align="right" valign="top" style="padding-top:18px;padding-bottom:0">验证码：</th>
                        <td align="left" style="padding-bottom:0"><input  name="seccode" type="text" size="30" class="text"><br /><label><img id="img-seccode" src="{{$options.sites.www}}/images/spacer.gif" style="margin:14px 10px 10px 0;vertical-align:middle" />&nbsp;&nbsp;<a href="javascript:void(0)" onclick="refreshSeccode();return false;">点击换一张</a></label></td>
                    </tr>
                    <tr>
                        <th align="right">&nbsp;</th>
                        <td><label><input id="remember" name="remember" type="checkbox" value="1">&nbsp;保持登陆状态</label>&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{$options.sites.www}}/password/?oid={{$org.orgid}}">忘记密码</a></td>
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
        <p align="left"><a href="{{$options.sites.www}}/about/privacy.html" target="_blank">隐私保护</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{$options.sites.www}}/help/index.html" target="_blank">相关帮助</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{$options.sites.www}}/suggest" target="_blank">反馈建议</a></p>
        <p class="copyright"><a href="http://www.miibeian.gov.cn/" target="_blank" style="margin-right:5px">粤ICP备09187954号</a>  Copyright &copy; 2012 tudu.com</p>
    </footer>
</div>
<!--背景图-->
{{if $loginskin.type == 'pic'}}
<div id="background-image">
{{*
    {{if $loginskin.issystem}}
    <img src="{{$options.sites.www}}/img/login/{{$loginskin.pic}}" width="1680" height="1123" />
    {{else}}
    <img src="{{$options.sites.www}}/file/?hash={{$loginskin.pic}}&type=loginpic" width="1680" height="1123" />
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
    document.getElementById('loginform').onsubmit = function(){
        return false;
    };

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
{{if $loginskin.type == 'pic'}}
<script type="text/javascript" src="{{$options.sites.www}}/js/jquery.fullscreen.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    var img = new Image();
    img.width = 1680;
    img.height = 1123;

    {{if $loginskin.issystem}}
    var src="{{$options.sites.www}}/images/login/{{$loginskin.pic}}";
    {{else}}
    var src="{{$options.sites.www}}/file/?hash={{$loginskin.pic}}&type=loginpic";
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
{{/if}}
</html>