<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.system_safe}} － {{$LANG.password_safe}}</title>
{{include file="^style.tpl"}}
<script type="text/javascript" src="{{$options.sites.static}}/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="{{$options.sites.static}}/js/frame.js"></script>
<script type="text/javascript" src="{{$options.sites.static}}/js/secure.js?1003"></script>
</head>
<body>

{{include file="secure/^nav.tpl" tab="login"}}
<form id="theform" method="post" action="{{$basepath}}/secure/login/save">
<div class="msgbox">
    <p>注：密码安全等级仅针对用户自行修改的密码，默认密码不受此限制。</p>
</div>
<table border="0" cellspacing="2" cellpadding="3" class="table-form table-form-f12" style="line-height:2.4em;margin:0 0 40px 0">
    <tr>
        <th align="right" valign="top" id="safety-pw">密码安全等级{{$LANG.cln}}</th>
        <td>
            <p style="_padding:5px 0;"><label for="high-level"><input id="high-level" name="pwdlevel" type="radio" value="2"{{if $org.passwordlevel == 2}} checked="checked"{{/if}} />高<span class="gray">（不少于8字符，必需包含字母、数字和特殊字符）</span></label></p>
            <p style="_padding:5px 0;"><label for="middling-level"><input id="middling-level" name="pwdlevel" type="radio" value="1"{{if $org.passwordlevel == 1}} checked="checked"{{/if}} />中（推荐）<span class="gray">（密码不得少于8位字符，必需包含字母和数字）</span></label></p>
            <p style="_padding:5px 0;"><label for="low-level"><input id="low-level" name="pwdlevel" type="radio" value="0"{{if $org.passwordlevel == 0}} checked="checked"{{/if}} />低（不推荐）<span class="gray">（密码不得少于6位字符）</span></label></p>
        </td>
    </tr>
    <tr>
        <th align="right" valign="top" style="padding-top:30px;" id="safety-account">帐号锁定{{$LANG.cln}}</th>
        <td valign="top" style="padding-top:30px;">
            <p style="_padding:5px 0;"><label for="opean"><input id="opean" name="opean" type="checkbox"{{if $org.locktime > 0}} checked="checked"{{/if}} />开启密码安全锁定</label></p>
            <p style="_padding:5px 0;">登录时连续 <input name="locktime" type="text" value="{{$org.locktime}}" class="text" style="width:50px;"{{if $org.locktime <= 0}} disabled="disabled" _disabled="true"{{/if}} /> 次输入密码错误，则<span class="red">自动锁定帐号一个小时</span></p>
        </td>
    </tr>
    <tr>
        <th align="right" valign="top" style="padding-top:30px;" id="safety-https">HTTPS安全连接：</th>
        <td style="padding-top:30px;">
        <p><label for="ishttps"><input id="ishttps" name="ishttps" type="checkbox"{{if $org.ishttps}} checked="checked"{{/if}} />在图度内，全程使用HTTPS安全链接&nbsp;<a href="http://service.oray.com/question/700.html" target="_blank">什么是HTTPS?</a></label></p>
        <p class="gray">设置全程HTTPS后，用户登录图度平台时，将默认全程采用HTTPS加密</p>
        </td>
    </tr>
    <tr>
        <th align="right" valign="top" style="padding-top:30px;" id="safety-time">登录时段：</th>
        <td style="padding-top:30px;">
            <input type="hidden" value="{{$org.timelimit.0|default:16777215}}" name="wd-0" />
            <input type="hidden" value="{{$org.timelimit.1|default:16777215}}" name="wd-1" />
            <input type="hidden" value="{{$org.timelimit.2|default:16777215}}" name="wd-2" />
            <input type="hidden" value="{{$org.timelimit.3|default:16777215}}" name="wd-3" />
            <input type="hidden" value="{{$org.timelimit.4|default:16777215}}" name="wd-4" />
            <input type="hidden" value="{{$org.timelimit.5|default:16777215}}" name="wd-5" />
            <input type="hidden" value="{{$org.timelimit.6|default:16777215}}" name="wd-6" />
            <div id="timelimit-div" style="margin-bottom:40px;">
            <p class="gray" style="_padding:5px 0;"><span class="time-enable"><em></em></span>可访问时段&nbsp;&nbsp;&nbsp;&nbsp;<span class="time-disable"><em></em></span>不可访问时段</p>
            </div>
        </td>
    </tr>
</table>

<div class="tool-btm"><div class="toolbar toolbar-bottom"><input type="submit" class="btn wd50" value="保存"></input></div></div>
</form>
<script type="text/javascript">
<!--
$(function() {
	_TOP.switchMod('secure');
	Secure.Login.init();
});
-->
</script>
</body>
</html>
