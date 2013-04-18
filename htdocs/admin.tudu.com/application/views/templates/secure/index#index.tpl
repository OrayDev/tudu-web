<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>系统安全</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/secure.js?1002" type="text/javascript"></script>
</head>
<body>

{{include file="secure/^nav.tpl"}}
{{if $total >= 80}}
<div class="security-level level-high">
    <p><span>目前图度的安全等级：高</span></p>
</div>
{{elseif $total >= 55}}
<div class="security-level level-in-level">
    <p><span>目前图度的安全等级：中</span></p>
</div>
{{else}}
<div class="security-level level-low">
    <p><span>目前图度的安全等级：低</span></p>
</div>
{{/if}}
<div class="setstate-box" style="border-top:none;">
    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-left:-4px">
        <colgroup>
            <col width="110" />
            <col />
        </colgroup>
        <tr>
            <td><span class="setstate{{if $secure.passwordlevel > 0}} setstate-enable{{else}} setstate-disable{{/if}}"></span></td>
            <td valign="bottom"><a href="{{$basepath}}/secure/login/#safety-pw"><strong class="f14 text-title">密码安全等级</strong></a></td>
        </tr>
        <tr>
            <td>强度：{{if $secure.passwordlevel >= 30}}强{{elseif $secure.passwordlevel >= 15}}中{{else}}低{{/if}}</td>
            <td><span class="gray">建议设置密码安全等级为中以上。</span></td>
        </tr>
    </table>
</div>
<div class="setstate-box">
    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-left:-4px">
        <colgroup>
            <col width="110" />
            <col />
        </colgroup>
        <tr>
            <td><span class="setstate{{if $secure.locktime > 0}} setstate-enable{{else}} setstate-disable{{/if}}"></span></td>
            <td valign="bottom"><a href="{{$basepath}}/secure/login/#safety-account"><strong class="f14 text-title">帐号锁定</strong></a></td>
        </tr>
        <tr>
            <td>{{if $secure.locktime > 0}}已开启{{else}}未开启{{/if}}</td>
            <td><span class="gray">可设置用户输入密码错误多少次则锁定该帐号1小时，默认为3次 。</span></td>
        </tr>
    </table>
</div>
<div class="setstate-box">
    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-left:-4px">
        <colgroup>
            <col width="110" />
            <col />
        </colgroup>
        <tr>
            <td><span class="setstate{{if $secure.ishttps > 0}} setstate-enable{{else}} setstate-disable{{/if}}"></span></td>
            <td valign="bottom"><a href="{{$basepath}}/secure/login/#safety-https"><strong class="f14 text-title">HTTPS加密</strong></a></td>
        </tr>
        <tr>
            <td>{{if $secure.ishttps > 0}}已开启{{else}}未开启{{/if}}</td>
            <td><span class="gray">全程使用HTTPS安全连接，可保证图度系统的信息安全性。</span></td>
        </tr>
    </table>
</div>
<div class="setstate-box">
    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-left:-4px">
        <colgroup>
            <col width="110" />
            <col />
        </colgroup>
        <tr>
            <td><span class="setstate{{if $secure.timelimit > 0}} setstate-enable{{else}} setstate-notset{{/if}}"></span></td>
            <td valign="bottom"><a href="{{$basepath}}/secure/login/#safety-time"><strong class="f14 text-title">登录时段</strong></a></td>
        </tr>
        <tr>
            <td>{{if $secure.timelimit > 0}}已开启{{else}}未设置{{/if}}</td>
            <td><span class="gray">设置图度平台的可访问时段，默认为全时段开放。</span></td>
        </tr>
    </table>
</div>
<div class="setstate-box">
    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-left:-4px">
        <colgroup>
            <col width="110" />
            <col />
        </colgroup>
        <tr>
            <td><span class="setstate{{if $secure.isiprule > 0}} setstate-enable{{else}} setstate-notset{{/if}}"></span></td>
            <td valign="bottom"><a href="{{$basepath}}/secure/ip/"><strong class="f14 text-title">IP地址过滤</strong></a></td>
        </tr>
        <tr>
            <td>{{if $secure.isiprule > 0}}已开启{{else}}未设置{{/if}}</td>
            <td><span class="gray">可设置仅允许指定IP访问图度平台。</span></td>
        </tr>
    </table>
</div>

</body>
</html>
