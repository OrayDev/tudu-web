<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>图度验证</title>
<style type="text/css">
<!--
html, body, form, div,p{
    margin:0;
    padding:0;
}
body{
    font-family: Arial, Helvetica, sans-serif;
    font-size:12px;
    line-height:1.6em;
}
.f14{
    font-size:14px;
}
.red{
    color:#f00;
}
.gray{
    color:#999;
}
.lock-content{
    background:url(/images/icon/panel_bg.gif) repeat-y;
}
.lock-inner{
    background:url(/images/icon/panel_top.gif) 0 top no-repeat;
}
.lock{
    background:url(/images/icon/panel_bottom.gif) 0 bottom no-repeat;
    padding:30px 20px;
    min-height:110px;
    _height:110px;
    position:relative;
}
.lock input.text{
    width:180px;
    border:1px solid #737373;
    background:url(/images/icon/dot.gif) #fff repeat-x;
    vertical-align:middle;
    margin-right:5px;
    font-family:verdana,simsun,sans-serif;
    font-size:14px;
    font-weight:bold;
    height:22px;
    line-height:22px;
    padding:2px 3px;
}
.lock input.focus{
    background:url(/images/icon/line_yellow.jpg) repeat-x;
    background-color:#fefbbd;
    border:1px solid #ac8539;
}
.lock-btn{
    height: 26px;
    line-height: normal;
    padding: 3px 10px;
    vertical-align: middle;
    font-weight:bold;
    font-size:14px;
}
.lock-logo{
    margin-bottom:10px;
}
.lock-title{
    background:url(/images/icon/lock.gif) 0 0 no-repeat;
    color:#2b425d;
    font-size:14px;
    height:50px;
    line-height:50px;
    padding-left:50px;
    margin-bottom:15px;
}
.tips{
    position:absolute;
    color:#f00;
    top:75px;
    left:124px;
    text-align:center;
}
-->
</style>

<script src="{{$options.sites.static}}/js/jquery-1.4.2.js" type="text/javascript"></script>
<script src="/static/js?f=lang&lang={{$user.option.language}}&ver=1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/all.js?v2003" type="text/javascript"></script>
</head>
<body>
<form id="authform" method="post" action="/foreign/index/auth">
<input type="hidden" name="tid" value="{{$tudu.tuduid}}" />
<input type="hidden" name="fid" value="{{$user.uniqueid}}" />
<input type="hidden" name="ts" value="{{$tsid}}" />


<div style="width:428px;margin:135px auto 0;">
    <div class="lock-logo"><img src="/logo?orgid=" /></div>
    <div class="lock-content">
        <div class="lock-inner">

            <div class="lock" style="height:auto">
                {{if $tudu.password && $tudu.isauth}}
                <p class="lock-title"><strong>本图度采用了双重加密</strong><strong class="gray">（包括验证码和密码）</strong></p>
                <p class="tips"></p>
                <table border="0" cellspacing="2" cellpadding="0">
                    <tr>
                        <td width="100" align="right"><label for="authcode" class="f14">请输入验证码：</label></td>
                        <td><input id="authcode" class="text" name="authcode" type="text" /></td>

                        <td><label for="save"><input id="remember" name="remember" type="checkbox" value="1" style="vertical-align:middle;" />记住验证码</label></td>
                    </tr>
                </table>
                <p class="gray">（若忘记验证码请查看您的图度邮件或咨询图度发起人）</p>
                <div style="border-bottom:#ccc 1px solid;height:0;margin:10px 0"></div>
                <table border="0" cellspacing="2" cellpadding="0">
                    <tr>
                        <td width="100" align="right"><label for="password" class="f14">请输入密码：</label></td>

                        <td><input id="password" class="text" name="password" type="password" autocomplete="off" /></td>
                    </tr>
                </table>
                <p class="gray">（若忘记密码请咨询图度发起人）</p>
                {{elseif $tudu.password}}
                <p class="lock-title"><strong>本图度采用了密码加密</strong></p>
                <p class="tips"></p>
                <table border="0" cellspacing="2" cellpadding="0">
                    <tr>
                        <td width="100" align="right"><label for="password" class="f14">请输入密码：</label></td>

                        <td><input id="password" class="text" name="password" type="password" autocomplete="off" /></td>
                    </tr>
                </table>
                <p class="gray">（若忘记密码请咨询图度发起人）</p>
                {{elseif $tudu.isauth}}
                <p class="lock-title"><strong>本图度采用了验证码加密</strong></p>
                <p class="tips"></p>
                <table border="0" cellspacing="2" cellpadding="0">
                    <tr>
                        <td width="100" align="right"><label for="authcode" class="f14">请输入验证码：</label></td>
                        <td><input id="authcode" class="text" name="authcode" type="text" /></td>

                        <td><label for="save"><input id="remember" name="remember" type="checkbox" value="1" style="vertical-align:middle;" />记住验证码</label></td>
                    </tr>
                </table>
                <p class="gray">（若忘记验证码请查看您的图度邮件或咨询图度发起人）</p>
                {{/if}}
                <p style="margin-top:20px;"><input name="" type="submit" class="lock-btn" value="提交" /></p>

            </div>

        </div>
    </div>
</div>

</form>

<script type="text/javascript">
<!--
$(function(){
    $(".lock .text").focus(function(){
        $(this).addClass("focus");
    }).blur(function(){
        $(this).removeClass("focus");
    })
})

var authcode = Cookie.get('AUTH-{{$tudu.tuduid}}');
if (authcode && $('input[name="authcode"]').size()) {
	$('input[name="authcode"]').val(authcode);
}

$('#authform').submit(function(){
	return false;
});
$('#authform').submit(function(){
    var form = $(this);

    if (form.find('input[name="password"]').size()) {
        var pwd = form.find('input[name="password"]').val();
        if (!pwd.replace(/\s+/g, '')) {
            return alert('请输入图度访问密码');
        }
    }

    if (form.find('input[name="authcode"]').size()) {
        var auth = form.find('input[name="authcode"]').val();
        if (!auth.replace(/\s+/g, '')) {
            return alert('请输入图度访问验证码');
        }
    }

    var data = form.serializeArray();

    form.find('input').attr('disabled', 'disabled');

    $('#submitbtn').val('正在提交...');

    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: data,
        url: form.attr('action'),
        success: function(ret) {
            if (ret.success) {
            	if ($('#remember:checked').size()) {
                    Cookie.set('AUTH-{{$tudu.tuduid}}', auth, {expires: 10*86400000*365});
                } else {
                	Cookie.set('AUTH-{{$tudu.tuduid}}', null);
                }
                location = '/foreign/tudu/view?tid={{$tudu.tuduid}}&ts={{$tsid}}&fid={{$user.uniqueid}}'
            } else {
                $('p.tips').text(ret.message);
                form.find('input').attr('disabled', false);
                $('#submitbtn').val('提交');
            }
        },
        error: function(res) {
        	form.find('input').attr('disabled', false);
        	$('#submitbtn').val('提交');
            return alert('网络繁忙，请稍候再试');
        }
    });
});
-->
</script>
</body>
</html>