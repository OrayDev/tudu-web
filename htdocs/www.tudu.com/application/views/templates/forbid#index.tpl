<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>TUDU</title>
<script type="text/javascript">
if(self != top) {
    top.location = self.location;
}
</script>
{{include file="^style.tpl"}}
<style type="text/css">
<!--
html{overflow:hidden;height:100%}
-->
</style>
</head>
<body>
<div style="width:428px;margin:135px auto 0;">
    <div class="lock-logo"><div class="fr" style="padding:42px 10px 0 0"><a href="/login/logout">返回登录页</a></div><div><img src="/logo?orgid=" /></div></div>
    <div class="lock-content">
        <div class="lock-inner">
            <div class="lock" style="height:auto">
                <form action="{{$options.sites.www}}/login/login-admin" method="post">
                    <table border="0" cellspacing="2" cellpadding="5" width="100%">
                        <tr>
                            <th colspan="2" align="center" style="padding-bottom:10px;"><strong style="color:#2b425d; font-size:16px;">
                            {{if $forbid.ip}}您的IP不在可访问地址列表内，{{if $user.admintype}}可直接登录后台{{else}}请咨询系统管理员{{/if}}
                            {{elseif $forbid.time}}非可访问时段，{{if $user.admintype}}可直接登录后台{{else}}请咨询系统管理员{{/if}}
                            {{else}}系统已经关闭，请咨询系统管理员
                            {{/if}}
                            </strong></th>
                        </tr>
                        {{if $user.admintype}}
                        <tr>
                            <th align="right" width="65" class="f14">帐号：</th>
                            <td><strong class="f14">{{$user.username}}</strong><input type="hidden" name="email" value="{{$user.username}}" /></td>
                        </tr>
                        <tr>
                            <th align="right"><label for="pw" class="f14">密码：</label></th>
                            <td><input id="pw" name="password" type="password" class="text" onfocus="this.className='text focus';" onblur="this.className='text';" style="width:250px" /></td>
                        </tr>
                        <tr valign="top">
                            <th align="right"><label for="code" style="display:block;margin-top:5px;_margin-top:10px;" class="f14">验证码：</label></th>
                            <td>
                            <input id="seccode" name="seccode" type="text" class="text" autocomplete="off" onfocus="this.className='text focus';" onblur="this.className='text';" style="width:250px" />
                            <p class="gray" style="padding:6px 0;">请输入您在下图中看到的字符，不区分大小写</p>
                            <p style="margin-top:3px;"><img id="img-seccode" src="{{$options.sites.www}}/seccode/?ns=adlogin&r={{0|rand:9999}}" style="vertical-align: text-bottom;" /> <a href="javascript:void(0);" onclick="changeSeccode(); return false;">看不清，换一个</a></p>
                            </td>
                        </tr>
                        <tr>
                            <td align="right"></td>
                            <td><input type="submit" value="登录"/></td>
                        </tr>

                        {{/if}}
                    </table>
                </form>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
<!--
function changeSeccode() {
    document.getElementById('img-seccode').src = '{{$options.sites.www}}/seccode/?ns=adlogin&r=' + Math.random();
}
-->
</script>
</body>
</html>