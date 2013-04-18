<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>组织LOGO</title>
{{include file="^style.tpl"}}
<link href="{{$options.sites.static}}/js/Jcrop/css/jquery.Jcrop.css" type="text/css" rel="stylesheet"/>
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<style type="text/css">
.logo-preview{
    overflow:hidden;
    width:170px;
    height:50px;
    border: 1px solid #D6D6D6;
}
.logo-preview div{
	overflow:hidden;
}
.logo-preview img{
    border: 0 none;
}
</style>
</head>
<body>
<div class="title-bar"><strong class="f14">组织LOGO</strong></div>
<form id="logoform" action="{{$basepath}}/org/info/logo-save" method="post">
<input type="hidden" name="x" />
<input type="hidden" name="y" />
<input type="hidden" name="width" />
<input type="hidden" name="height" />
<input type="hidden" id="hash" name="hash" />
    <div style="margin-top:24px;position:relative;">
        <p><button type="button" class="btn f14" style="line-height:normal;">本地上传</button>&nbsp;&nbsp;<button onclick="Org.Logo.revert()" type="button" value="" class="btn f14" style="line-height:normal;">恢复默认</button></p>
        <p class="gray">支持jpg、png、gif，尺寸建议为170*50，文件大小不超过 500K</p>
        <div id="uploadcover" style="position:absolute;top:0;left:0;width:80px;height:25px;cursor:pointer"><span id="swfplace"></span></div>
    </div>
    <div class="logo-box">
        <div class="logo-box-left">
            <div class="logo-edit" id="edit-box-big"><div class="logo-add"><img id="logo-img" src="{{$options.sites.www}}/logo/?oid={{$admin.orgid}}" /></div></div>
        </div>
        <div class="logo-box-right" id="logo-preview-div" style="display:none">
            <p>预览：</p>
            <div class="logo-preview"><div><img id="logo-preview" src="{{$options.sites.www}}/logo/?oid={{$admin.orgid}}" width="170" height="50" /></div></div>
        </div>
        <div class="clear"></div>
    </div>
    <p id="btn-bar" style="display:none"><button type="submit" id="save" class="btn-big" style="line-height:normal;">保存</button>&nbsp;&nbsp;<button id="cancel" type="button" onclick="location.reload()" class="btn-big" style="line-height:normal;">取消</button></p>
</form>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/swfupload.js?1000" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.Jcrop.min.js?1000" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/org.js?1000" type="text/javascript"></script>
<script type="text/javascript">
<!--
$(function() {
    Org.Logo
    .setCookies('{{$cookies}}')
    .setFlashUrl('{{$smarty.const.PROTOCOL}}//{{$admin.orgid}}.tudu.com{{$basepath}}/images/swfupload.swf')
    .init();
});
-->
</script>
</body>
</html>
