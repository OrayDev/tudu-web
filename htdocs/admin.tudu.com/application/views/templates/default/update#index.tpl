<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>图度在线升级</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<style type="text/css">
.infobox{ clear:both; margin-bottom:10px; padding:30px; text-align:center; border-top:4px solid #DEEFFA; border-bottom:4px solid #DEEEFA; background:#F2F9FD; zoom:1; }
.infotitle1{ margin-bottom:10px; color:#09C; font-size:14px; font-weight:700; }
.infotitle2{ margin-bottom:10px; color:#090; font-size:14px; font-weight:700; }
.infotitle3{ margin-bottom:10px; color:#C00; font-size:14px; font-weight:700; }
.lightlink{ color:#666; text-decoration:underline;}
</style>
</head>
<body>
<p style="padding:4px 5px 9px; height:16px;line-height:16px;"><strong class="f14 text-title">在线升级</strong>&nbsp;&nbsp;<span class="gray">当前使用的版本:{{$tuduversion}}</span></p>
{{if $operation}}
<div id="operation-box" class="infobox">
    {{if $unupgrade || $islastest}}
    <h4 class="infotitle2">您目前使用的已经是最新版本，不需要升级</h4>
    {{elseif $diswriteable}}
    <h4 class="infotitle3">目录"{{$rootpath}}"不可写，无法下载更新包</h4>
    {{elseif $checkfinsh}}
    <form action="{{$basepath}}/update/" id="upgradeform" method="post">
        <input name="fileurl" type="hidden" value="{{$lastest.url}}" />
        <input name="filemd5" type="hidden" value="{{$lastest.md5}}" />
        <input name="operation" type="hidden" value="upgrade" />
        <h4 class="infotitle2">查询到有新的更新，最新版本：{{$lastest.version}}</h4>
        <p style="margin-top:25px;"><input id="upgrade" type="button" class="btn wd90" value="立即更新" /></p>
    </form>
    <script type="text/javascript">
        $('#upgrade').click(function(){
            if (!confirm('确定立即升级图度系统吗？')) {
                return false;
            }

            $('#upgradeform').submit();
            $('#operation-box').empty();
            $('#operation-box').append('<h4 class="infotitle2">正在处理更新，请耐心等待！</h4>');
        });
    </script>
    {{/if}}
</div>
{{else}}
<div id="check" class="infobox">
    <h4 class="infotitle1">正在检测新的升级版本</h4>
    <img src="{{$basepath}}/img/upgrade_loader.gif" />
    <p><a href="{{$basepath}}/update/?operation=check" class="lightlink">如果您的浏览器没有自动跳转，请点击这里</a></p>
    <script type="text/javascript">
        setTimeout("redirect('{{$basepath}}/update/?operation=check');", 0);
    </script>
</div>
{{/if}}
<script type="text/javascript">
$(function() {
    _TOP.switchMod('upgrade');
});
function redirect(url) {
    window.location.replace(url);
}
</script>
</body>
</html>