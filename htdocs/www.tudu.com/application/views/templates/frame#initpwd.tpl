<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.init_password}}</title>
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
.lock th{
    font-weight:normal;
    font-size:14px;
}
.lock td,th{
    padding:8px 0;
}
-->
</style>
<script type="text/javascript" src="{{$options.sites.static}}/js/jquery-1.4.4.js"></script>
<script src="/static/js?f=lang&lang={{$user.option.language}}&ver=1003" type="text/javascript"></script>
</head>
<body>

<div style="width:428px;margin:135px auto 0;">
<form id="theform" action="/frame/initpwd.update" method="post">
    <div class="lock-logo"><img src="/logo?oid={{$org.orgid}}" /></div>
    <div class="lock-content">
        <div class="lock-inner">
            <div class="lock">
            <table border="0" cellspacing="1" cellpadding="4">
                <tr>
                    <td colspan="2">
                    <p style="text-align:center"><strong style="color:#2b425d; font-size:16px;">{{$LANG.init_password}}</strong></p>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                    <ul style="padding:0;margin:0;line-height:22px;"><li>{{$LANG.init_pwd_hint1}}</li><li>{{$LANG.init_pwd_hint2}}</li></ul>
                    </td>
                </tr>
                <tr id="msg-tr" style="display:none">
                    <td style="padding:3px 0"></td>
                    <td style="padding:3px 0;"><span class="red" id="msg">&nbsp;</span></td>
                </tr>
                <tr>
                    <td align="right"><label for="password" style="font-size:14px;">{{$LANG.new_password}}{{$LANG.cln}}</label></td>
                    <td><input name="password" id="password" type="password" class="text" maxlength="16" autocomplete="off" onfocus="this.className='text focus';" onblur="this.className='text';" style="width:230px" /></td>
                </tr>
                <tr>
                    <td align="right"><label for="repassword" style="font-size:14px;">{{$LANG.confirm_password}}{{$LANG.cln}}</label></td>
                    <td><input name="repassword" id="repassword" type="password" class="text" maxlength="17" autocomplete="off" onfocus="this.className='text focus';" onblur="this.className='text';" style="width:230px" /></td>
                </tr>
                <tr>
                    <td align="right"></td>
                    <td><input type="submit" value="{{$LANG.submit}}" /></td>
                </tr>
            </table>
            </div>
        </div>
    </div>
</form>
</div>
<script type="text/javascript">
<!--
$(function(){
	$('#theform').submit(function(){return false;});
    $('#theform').submit(function(){
        if (!checkPassword()) {
            $('#password').focus();
            return false;
        }

        var form = $(this);

        var data = form.serializeArray();

        form.find('input, button, select').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: form.attr('action'),
            data: data,
            success: function(ret) {
                if (ret.success) {
                    alert(ret.message);
                    location = '/frame/';
                } else {
                	$('#msg').text(ret.message);
                    $('#msg-tr').show();
                }
                form.find('input, button, select').attr('disabled', false);
            },
            error: function(res) {
                alert(TEXT.PROCESS_ERROR);
                form.find('input, button, select').attr('disabled', false);
            }
        });
    });
});

function checkPassword() {
    var lev = 0;

    var password = $('#password').val();

    if (password != $('#repassword').val()) {
        $('#msg').text('{{$LANG.confirm_password_unmatch}}');
        $('#msg-tr').show();
        return false;
    }

    if ((/[a-zA-Z]+/).test(password)) {
        lev++;
    }

    if ((/[0-9]+/).test(password)) {
        lev++;
    }

    if ((/[^0-9a-zA-Z]+/).test(password)) {
        lev++;
    }

    {{if $user.option.passwordlevel < 1}}
    if (password.length < 6) {
        $('#msg').text(TEXT.PASSWORD_TOO_SHORT);
        $('#msg-tr').show();
        return false;
    }
    {{/if}}

    {{if $user.option.passwordlevel >= 2}}
    if (password.length < 8 || lev < 3) {
        $('#msg').text(TEXT.PASSWORD_SAFE_LEVEL_HEIGHT);
        $('#msg-tr').show();
        return false;
    }
    {{elseif $user.option.passwordlevel == 1}}

    if (password.length < 8 || lev < 1) {
        $('#msg').text(TEXT.PASSWORD_SAFE_LEVEL_MIDDLE);
        $('#msg-tr').show();
        return false;
    }
    {{/if}}

    $('#pwd-info').hide();
    return true;
}
-->
</script>
</body>
</html>