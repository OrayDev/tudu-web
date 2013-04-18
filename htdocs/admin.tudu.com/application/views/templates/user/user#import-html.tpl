<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>批量导入帐号</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.ajaxupload.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/swfupload.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/user.js?1005" type="text/javascript"></script>
</head>
<body>

{{include file="user/^nav.tpl" tab="import"}}
<div>
    <div style="padding:5px 0; line-height:2em;">
        <p>选择用于导入的csv文件：<a href="{{$basepath}}/user/user/import.csv" class="udl">下载csv模板</a></p>
        <form action="{{$basepath}}/user/user/upload-csv" id="uploadform" method="post" enctype="multipart/form-data">
           <input id="csvfile" name="file" type="text" class="text" size="50" />&nbsp;<div style="position:relative;display:inline-block;zoom:1;*display:inline;"><div id="btnupload" style="position:absolute;"><span id="spanButtonPlaceHolder"></span></div><span><input id="btn-upload" type="button" value="导入" /></span></div>
        </form>
    </div>
</div>

<div class="title-bar"><strong class="f14 text-title">上传结果</strong></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list table-header">
    <tr>
        <th width="120" align="left"><div class="td-space">真实姓名</div></th>
        <th width="140" align="left"><div class="td-space">帐号</div></th>
        <th width="95" align="left"><div class="td-space">性别</div></th>
        <th width="95" align="left"><div class="td-space">帐号状态</div></th>
        <th width="200" align="left"><div class="td-space">所属部门</div></th>
        <th width="95" align="left"><div class="td-space">所属权限组</div></th>
        <th class="td-last" align="left"><div class="td-space">备注</div></th>
    </tr>
</table>
<form id="userform" method="post" action="{{$basepath}}/user/user/save">
<div id="import-list">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">
    <tr>
        <td class="td-null">请先上传csv文件</td>
    </tr>
</table>
</div>
</form>

<script type="text/javascript">
<!--
$(function() {
    _TOP.switchMod('');
    var _ORG_NAME = '{{$org.orgname}}',
        flashUrl = '{{$options.sites.www}}/images/swfupload.swf',
        cookies = '{{$cookies}}';
    User.initImport(flashUrl, cookies, _ORG_NAME);
});
-->
</script>
</body>
</html>