<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>登录图度</title>
{{include file="^icon.tpl"}}
<link href="{{$options.sites.static}}/css/login.css" type="text/css" rel="stylesheet" />
<!--[if IE 6]>
<script type="text/javascript" src="{{$options.sites.static}}/js/ie6-fix.js"></script>
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
                    {{if $error == 'notexist'}}
                    <div class="login-err-box" id="err_box">
                    <div class="tips_box png"><p><strong>此帐号不存在！</strong></p></div>
                    <div class="info_box">
                        <p><strong>可能的原因：</strong></p>
                        <ol>
                            <li>图度登录地址输入有误，请检查该地址是否正确；</li>
                            <li>帐号输入有误，请检查是否输入正确；</li>
                            <li>帐号不存在，请确保您的图度管理员已在后台添加您的帐号。</li>
                        </ol>
                    </div>
                    </div>
                    {{elseif $error}}
                    <div class="login-err-box" id="err_box">
                    <div class="tips_box png"><p><strong>密码输入错误！</strong></p></div>
                    <div class="info_box">
                        <p><strong>可能的原因：</strong></p>
                        <ol>
                            <li>密码输入有误；</li>
                            <li>忘记密码，请联系您的图度管理员重新设置密码；</li>
                            <li>您的帐号已被停用，具体原因请咨询您的图度管理员。</li>
                        </ol>
                    </div>
                    </div>
                    {{else}}
                    <div class="company-info" style="height:340px;padding:30px 0;overflow:hidden">
                        {{$org.intro|escape:'html'|nl2br}}
                    </div>
                    {{/if}}
                </div>
            </div>
            <div class="login-right">
                <div class="login-box">
                    <div class="login-box-header">
                        <div class="login-logo"><img src="{{$options.sites.www}}/logo?oid={{$org.orgid}}" class="png" border="0"></div>
                    </div>
                    <div class="login-box-body">
                        <form action="/login/" method="post" class="login-form" id="loginform">
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
                                    <td align="left"><div class="input-text-wrap"><input id="uid" name="uid" type="text" size="30" class="input-text" style="width:280px;"><img id="avatar" src="{{$options.sites.static}}/images/spacer.gif" style="display:none;width:34px;height:34px" /></div></td>
                                </tr>
                                <tr>
                                    <th align="right">密码：</th>
                                    <td align="left"><input id="password" name="password" type="password" size="30" class="input-text" style="width:280px;"></td>
                                </tr>
                                <tr id="seccode-tr" style="display:none">
                                    <th align="right" valign="top" style="padding-top:22px;padding-bottom:0">验证码：</th>
                                    <td align="left" style="padding-bottom:0"><input name="seccode" type="text" size="30" class="input-text" style="width:280px;" /><br /><img id="img-seccode" src="{{$options.sites.static}}/images/spacer.gif" style="margin:14px 10px 10px 0;vertical-align:middle" /><a href="javascript:void(0)" onclick="refreshSeccode();return false;">点击换一张</a></td>
                                </tr>
                                <tr>
                                    <th align="right">&nbsp;</th>
                                    <td align="left"><label for="remember"><input id="remember" name="remember" type="checkbox" value="1" /><span class="gray">保持登录状态</span></label></td>
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
    <p class="footer-link"><a href="{{$options.sites.www}}/about/privacy.html" target="_blank">隐私保护</a><a href="{{$options.sites.www}}/help/index.html" target="_blank">相关帮助</a><a href="{{$options.sites.www}}/suggest" target="_blank">反馈建议</a></p>
    <p class="copyright"><a href="http://www.miibeian.gov.cn/" target="_blank" style="margin-right:5px">粤ICP备09187954号</a> Copyright © 2012 tudu.com</p>
</div>

{{include file="index#index^script.tpl"}}
<script type="text/javascript">
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

    $('#uid').bind('focus', function(){
        var v = this.value;
        if (v.indexOf('@') == -1) {
            this.value = v + '@{{$org.orgid}}';
            setLocation(this, 0);
        }
    });

    document.getElementById('loginform').onsubmit = function(){
        return login();
    };

    var name = getCookie("uid");

    if (name) {
        document.getElementById('uid').value = name;

        var arr = name.split('@'),
            name = arr[0] + '@' + (-1 != arr[1].indexOf('.') ? arr[1].substr(0, arr[1].indexOf('.')) : arr[1]);

        var avatar = document.getElementById('avatar');
        avatar.src = '{{$options.sites.www}}/logo/?email=' + name;
        avatar.style.display = '';
        $('#password').focus();
    } else {
        $('#uid').focus();
    }

    {{if $error && $error != 'timeout'}}
    refreshSeccode();
    {{/if}}
})()
</script>
<script src="{{$options.sites.www}}/seccode/check?cb=checkSeccode&ns=login" type="text/javascript"></script>
</body>
</html>
