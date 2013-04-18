<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>后台登陆</title>
<link href="{{$options.sites.www}}/css/login.css" type="text/css" rel="stylesheet" />
<!--[if IE 6]>
<script type="text/javascript" src="{{$options.sites.www}}/js/ie6-fix.js"></script>
<script type="text/javascript">
DD_belatedPNG.fix('.png, background,img');
</script>
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
<div class="header" style="height:50px;" id="header"></div>
<div class="login-container" id="container-height">
    <div class="png login-cover-layer">
        <div class="login-content" id="main">
            <div class="login-left">
                <div class="login-left-header">
                    <div class="title">办公，变得如此轻松和简单...</div>
                </div>
                <div class="png login-left-body" style="background-image:url({{$options.sites.static}}/img/login_bg.png);background-position:-20px 50%"></div>
            </div>
            <div class="login-right">
                <div class="login-box">
                    <div class="login-box-header">
                        <div class="login-logo"><a href="{{$options.sites.www}}/"><img src="{{$options.sites.www}}/images/logo.png" class="png" border="0"></a></div>
                    </div>
                    <div class="login-box-body" style="background-color:#dff3ff">
                        <form action="{{$options.sites.www}}/login/login-admin" method="post" class="login-form" id="loginform">
                            <table border="0" cellpadding="0" cellspacing="0" align="center">
                                <tr>
                                    <th align="right">&nbsp;</th>
                                    <td align="left"><div class="login-title">图度后台管理系统</div></td>
                                </tr>
                                <tr>
                                    <th align="right"></th>
                                    <td align="left" style="padding-top:0;padding-bottom:0;height:16px"><div class="red" style="font-size:12px;color:#f00">{{$err}}</div></td>
                                </tr>
                                <tr>
                                    <th align="right">帐号：<input type="hidden" name="email" value="{{$address}}" /></th>
                                    <td align="left"><div class="input-text-wrap"><input name="uid" id="uid" value="{{$address}}" type="text" size="30" class="input-text" style="width:280px;" disabled="disabled" /><img id="avatar" src="{{$options.sites.www}}/logo/?email={{$address}}" style="width:34px;height:34px" /></div></td>
                                </tr>
                                <tr>
                                    <th align="right">密码：</th>
                                    <td align="left"><input name="password" id="password" type="password" size="30" class="input-text" style="width:280px;" maxlength="16" /></td>
                                </tr>
                                <tr id="seccode-tr">
                                    <th align="right" valign="top" style="padding-top:22px;padding-bottom:0">验证码：</th>
                                    <td align="left" style="padding-bottom:0"><input name="seccode" type="text" size="30" class="input-text" style="width:280px;" /><br /><img id="img-seccode" src="{{$options.sites.www}}/images/spacer.gif" style="margin:14px 10px 10px 0;vertical-align:middle" /><a href="javascript:void(0)" onclick="refreshSeccode();return false;">点击换一张</a></td>
                                </tr>
                                <tr>
                                    <th align="right">&nbsp;</th>
                                    <td align="left"><button type="submit" class="btn"><span class="btn-left"><span class="btn-right"><span class="btn-inner" style="*width:40px;">登录</span></span></span></button></td>
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
    <p class="footer-link">
    <a href="{{$options.sites.www}}/about/about.html" target="_blank">关于我们</a>
    <a href="{{$options.sites.www}}/about/contact.html" target="_blank">联系我们</a>
    <a href="{{$options.sites.www}}/about/privacy.html" target="_blank">隐私保护</a>
    <a href="{{$options.sites.www}}/about/copyright.html" target="_blank">版权声明</a>
    <a href="{{$options.sites.www}}/help/index.html" target="_blank">相关帮助</a>
    </p>
    <p class="copyright"><a href="http://www.miibeian.gov.cn/" target="_blank" style="margin-right:5px">粤ICP备09187954号</a> Copyright © 2012 tudu.com</p>
</div>

<script src="{{$options.sites.www}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script type="text/javascript">

function refreshSeccode() {
    document.getElementById('img-seccode').src = '{{$options.sites.www}}/seccode/?ns=adlogin&sz=100x36&rand=' + Math.random();
    document.getElementById('seccode-tr').style.display = '';
}

(function(){
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

    $('input:text,input:password').bind('focus blur mouseover mouseout', function(e) {
        if (e.type == 'focus' || e.type == 'mouseover') {
            $(this).addClass('input-text-hover');
        } else {
            if (e.type == 'blur' || document.activeElement != this) {
                $(this).removeClass('input-text-hover');
            }
        }
    });

    refreshSeccode();
    $('#password').focus();
})();
</script>
<script src="{{$options.sites.www}}/seccode/check?cb=checkSeccode&ns=adlogin" type="text/javascript"></script>
</body>
</html>