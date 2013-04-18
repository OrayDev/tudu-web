<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$tudu.subject}}</title>
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
.loci-tips{
    position:absolute;
    color:#f00;
    top:75px;
    left:124px;
    text-align:center;
}
-->
</style>
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/tudu.js?1017" type="text/javascript"></script>
<script type="text/javascript">
<!--
var LH = 'm=view&tid={{$tudu.tuduid}}';
if (top == this) {
	location = '/frame#' + LH;
}
-->
</script>
</head>
<body>
<div style="width:428px;margin:100px auto 0;">
    <div class="lock-content">
        <div class="lock-inner">

            <form id="theform" action="/tudu/auth" method="post" class="lock">
            <input type="hidden" name="tuduid" value="{{$tudu.tuduid}}">
            <p class="lock-title"><strong>{{$LANG.password_title}}</strong></p>
            <p class="lock-tips"></p>
            <table cellpadding="0" cellspacing="2" border="0">
            <tr>
            <td width="100" align="right"><label for="password" class="f14">{{$LANG.input_privacy_pwd}}</label></td>
            <td><input type="password" name="password" id="password" class="text" value="" maxlength="16" /></td>
            </tr>
            </table>
            <p class="gray">({{$LANG.password_tips}})</p>
            <p style="margin-top:15px;"><input type="submit" id="submitbtn" value="{{$LANG.submit}}" />&nbsp;<input type="button" id="back" value="{{$LANG.back}}" /></p>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
<!--
$(function(){
	_TUDU_ID = '{{$tudu.tuduid}}';
	TOP.Frame.hash(LH);

	$('#back').bind('click', function(){
		location = '{{$query.back|default:'/tudu/?search=inbox'}}';
	});

	$('#theform').submit(function(){return false;});
	$('#theform').submit(function(){
		if (!$('#password').val()) {
            $('#password').focus();
            return TOP.showMessage(TOP.TEXT.PASSWORD_NOT_EMPTY);
        }
		var form = $(this);
		var data = form.serializeArray();
		TOP.showMessage(TOP.TEXT.POSTING_DATA, 0, 'success');
        form.find('input, button').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: form.attr('action'),
            data: data,
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
                if(ret.success) location = '/tudu/view?tid={{$tudu.tuduid}}';
                form.find('input, button').attr('disabled', false);
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESS_ERROR);
                form.find('input, button').attr('disabled', false);
            }
        });
	});
});

$(function(){
    $(".lock .text").focus(function(){
        $(this).addClass("focus");
    }).blur(function(){
        $(this).removeClass("focus");
    })
})
-->
</script>
</body>
</html>
