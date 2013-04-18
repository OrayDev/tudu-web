<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>基本信息</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
</head>
<body>
<div class="title-bar"><strong class="f14">基本信息</strong></div>
<form id="infoform" action="{{$basepath}}/org/info/save" method="post" class="form-separate">
<input type="hidden" name="action" value="{{$action}}" />
<input type="hidden" id="ids-val" value="{{$info.industry}}" />
<input type="hidden" id="province-val" value="{{$info.province}}" />
<input type="hidden" id="city-val" value="{{$info.city}}" />
{{if $info.realnamestatus == 2}}
<input type="hidden" name="entirename" value="{{$info.entirename}}" />
{{/if}}
    <fieldset class="form-field first" style="border-top:0;">
        <table border="0" cellspacing="0" cellpadding="5" class="table-form">
            <tr>
                <th align="right" width="90">组织全称：</th>
                <td><input class="text-big" value="{{$info.entirename}}" name="entirename" type="text" size="45" style="width:360px;" tabindex="1"{{if $info.realnamestatus == 2}} disabled="disabled"{{/if}} /><span style="color:#ec6e0a;margin-left:5px;">*</span></td>
            </tr>
            <tr>
                <th align="right" width="90">组织简称：</th>
                <td><input class="text-big" value="{{$org.orgname}}" name="orgname" type="text" size="45" style="width:360px;" tabindex="1" /></td>
            </tr>
        </table>
    </fieldset>
    <fieldset class="form-field">
        <table border="0" cellspacing="0" cellpadding="5" class="table-form">
            <tr>
                <th align="right" width="90" valign="top">组织简介：</th>
                <td><textarea class="text-big" id="intro" name="intro" cols="45" rows="5" style="width:360px; height:77px;" tabindex="10">{{$org.intro}}</textarea><span style="margin: 0 0 0 10px" class="gray" id="intro-hint">还可以输入300个字符</span></td>
            </tr>
        </table>
    </fieldset>
    <table border="0" cellspacing="0" cellpadding="5" class="table-form">
        <tr>
            <th align="right" width="90"></th>
            <td><input type="submit" value="保存修改" class="btn-big" tabindex="10" /></td>
        </tr>
    </table>
</form>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/base.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/select.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/province.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/industry.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/org.js?1001" type="text/javascript"></script>
<script type="text/javascript">
<!--
Org.Info.init();
-->
</script>
</body>
</html>