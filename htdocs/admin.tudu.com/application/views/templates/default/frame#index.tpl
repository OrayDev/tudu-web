<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>图度后台</title>
{{include file="^style.tpl"}}
<link href="{{$options.sites.static}}/js/Jcrop/css/jquery.Jcrop.css" type="text/css" rel="stylesheet"/>
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.Jcrop.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.ajaxupload.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.tree.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/common.js?1003" type="text/javascript"></script>
<script type="text/javascript">
<!--
var BASE_PATH = '{{$basepath}}';
var SITES = {
'static': '{{$options.sites.static}}',
'tudu': '{{$options.sites.www}}'
};
-->
</script>
<style>
html,body{
    height:100%;
    overflow:hidden;
}
</style>
</head>
<body class="frameset">
{{include file="^header.tpl"}}
{{include file="^nav.tpl" tab="sys"}}
<div class="container-main">
    <!-- content-left   -->
    <div class="content-left">
        <ul class="sidebar" id="sidebar">
            <li id="nav-user" class="first"><a href="{{$basepath}}/user/user/" target="main">帐号</a></li>
            <li id="nav-dept"><a href="{{$basepath}}/user/department/" target="main">组织架构</a></li>
            <li id="nav-role"><a href="{{$basepath}}/user/role/" target="main">权限</a></li>
            <li id="nav-group"><a href="{{$basepath}}/user/group/" target="main">群组</a></li>
            <li id="nav-board"><a href="{{$basepath}}/board/board/" target="main">分区管理</a></li>
            <li id="nav-setting"><a href="{{$basepath}}/settings/general" target="main">基本设置</a></li>
            <li id="nav-secure"><a href="{{$basepath}}/secure/" target="main">系统安全</a></li>
            <li id="nav-appstore" class="last"><a href="{{$basepath}}/appstore/appstore/" target="main">增值应用</a></li>
            <li id="nav-upgrade" class="last"><a href="{{$basepath}}/update/" target="main">在线升级</a></li>
        </ul>
    </div>
    <!-- end content-left   -->
    <div class="content-main">
        <iframe height="100%" frameborder="0" scrolling="auto" allowtransparency="true" class="iframe-main" marginheight="0" marginwidth="0" name="main" id="mainframe" src="{{$basepath}}/user/user/"></iframe>
    </div>

</div>
<script type="text/javascript">
(function(){

    var s = $('#sidebar').css('overflow', 'auto');
    var t = s.offset().top
    function onResize(){
        var height = document.body.offsetHeight - t;
        $('#sidebar').height(height);
        $('#mainframe').height(height);
    }

    window.onresize = onResize;
    onResize();
})()

$("#sidebar li").click(function(){
    switchMod(this.id.replace('nav-', ''));
});

</script>
</body>
</html>
