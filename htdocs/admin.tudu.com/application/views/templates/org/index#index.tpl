<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>组织信息</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.ajaxupload.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/common.js?1003" type="text/javascript"></script>
<style>
html,body{
    height:100%;
    overflow:hidden;
}
</style>
<script type="text/javascript">
var SITES = {
'static': '{{$options.sites.static}}',
'tudu': '{{$options.sites.www}}'
};

var BASE_PATH = '{{$basepath}}';
</script>
</head>
<body class="frameset">
{{include file="^header.tpl"}}
{{include file="^nav.tpl" tab="org"}}

<div class="container-main">
    <!-- content-left   -->
    <div class="content-left">
        <ul class="sidebar" id="sidebar">
            <li id="nav-info" class="first current" ><a href="{{$basepath}}/org/info/" target="main">基本信息</a></li>
            <li id="nav-logo"><a href="{{$basepath}}/org/info/logo" target="main">组织LOGO</a></li>
            {{if $admin.isowner}}
            <li id="nav-pwd"><a href="{{$basepath}}/org/password/" target="main">修改密码</a></li>
            {{/if}}
        </ul>
    </div>
    <!-- end content-left   -->
    <div class="content-main">
        {{if strpos($smarty.server.HTTP_USER_AGENT, 'MSIE 6') === false}}
        <iframe height="100%" frameborder="0" scrolling="auto" allowtransparency="true" class="iframe-main" marginheight="0" marginwidth="0" name="main" id="mainframe" src="{{$basepath}}/org/info"></iframe>
        {{else}}
        <iframe height="100%" frameborder="0" scrolling="auto" allowtransparency="true" class="iframe-main" marginheight="0" marginwidth="0" name="main" id="mainframe" src="{{$basepath}}/org/info"></iframe>
        {{/if}}
    </div>
</div>
<script type="text/javascript">
(function(){
    var s = $('#sidebar').css('overflow', 'auto');
    var t = s.offset().top
    function onResize(){
        var height = document.body.offsetHeight - t;
        $('#sidebar').height(height + 12);
        $('#mainframe').height(height + 12);
    }

    window.onresize = onResize;

    onResize();
})();

$("#sidebar li").click(function(){
    switchMod(this.id.replace('nav-', ''));
});
</script>
</body>
</html>