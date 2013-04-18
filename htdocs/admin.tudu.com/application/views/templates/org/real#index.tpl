<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>企业实名认证</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
</head>

<body>
<div class="title-bar"><strong class="f14">企业实名认证</strong></div>
<p style="margin:8px 0;"><span class="gray">进行企业实名认证后，即</span> <strong class="red">可获得500用户数、空间将扩展到2G！</strong></p>
{{if $realname && $realname.memo && $info.realnamestatus != 2}}
<div class="msgbox" style="margin-bottom:5px;">
    <p>失败原因：{{$realname.memo}}</p>
</div>
{{/if}}
<form id="realform" action="/org/real/save" method="post" enctype="multipart/form-data">
    <div class="realname-box">
        <div class="realname-left">
            <div class="realname-file">
                <span class="realname-state {{if $info.realnamestatus == 2}}state-verified{{elseif $info.realnamestatus == 1}}state-verifying{{else}}state-unverified{{/if}}"></span>
                <table border="0" cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td height="320" class="f14" align="center">
                        {{if $realname && $realname.realnameid}}
                        <img id="realname-img" src="{{$options.sites.www}}/file/?type=realname&fid={{$realname.realnameid}}&oid={{$info.orgid}}" />
                        {{else}}
                        <p class="gray">你还没有上传营业执照</p>
                        {{/if}}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="realname-right">
            <div class="realname-step" style="margin-bottom:12px;">
                <h3>认证流程：</h3>
                <div><img src="{{$basepath}}/img/realname_step_20120227.gif" border="0" usemap="#Map">
<map name="Map"><area shape="rect" coords="70,13,134,31" href="{{$basepath}}/org/info">
</map></div>
            </div>
            <div class="realname-step">
                <h3>认证规则：</h3>
                <ol>
                    <li>营业执照在经营期限内</li>
                    <li>含本年度的年检章(每年3-6月年检)</li>
                    <li>营业执照上的企业名称应与注册的组织名称一致</li>
                </ol>
            </div>
            <div class="tips-list">
                <ul>
                    <li>文件仅用于企业实名认证，不会用于其他商业用途</li>
                </ul>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    {{if $info.realnamestatus == 0 || ($realname && $realname.status == 2)}}
    <div style="margin-top:20px;position:relative;">
    <input id="real-upload" type="button" value="本地上传" class="btn f14" />&nbsp;<span class="gray">上传前请先完善<a href="{{$basepath}}/org/info/" style="font-weight:bold;">基本信息</a>，支持jpg、png、gif格式，大小不能超过2MB</span>
    </div>
    {{/if}}
</form>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/org.js?1000" type="text/javascript"></script>
<script type="text/javascript">
<!--
$(function() {
    Org.Realname.init();
});
-->
</script>
</body>
</html>