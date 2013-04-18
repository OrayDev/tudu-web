<!DOCTYPE html>
<html>
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en"> <!--<![endif]-->
<!--[if lt IE 9]> <script src="/js/html5.js" type="text/javascript"></script> <![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>图度开源安装向导</title>
<link href="/css/install.css" type="text/css" rel="stylesheet">
<script src="/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="/js/install.js" type="text/javascript"></script>
</head>
<body>
<header class="header">
    <div class="logo"><a href="http://www.tudu.com" target="_blank"><img src="/images/install/logo_130328.gif" border="0" /></a>&nbsp;<span class="text"><em>丨</em>云办公系统 v1.0</span></div>
</header>
<div class="container">
    <form id="theform" method="get" action="?" onsubmit="return checked();">
    <section class="step">
        <ol>
            <li style="margin:0;"><span class="step-1"></span>欢迎</li>
            <li><span class="step-2 step-2-on"></span>环境检查</li>
            <li><span class="step-3"></span>配置</li>
            <li><span class="step-4"></span>完成</li>
        </ol>
    </section>
    <section class="check">
        <div class="caption"><div class="title">环境检查</div></div>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">
            <colgroup>
                <col/>
                <col width="25%"/>
                <col width="30%"/>
            </colgroup>
            <tr>
                <th align="left">项目</th>
                <th align="left">最低要求</th>
                <th align="left">当前</th>
            </tr>
            <tr>
                <td align="left">操作系统</td>
                <td align="left">{$default.os_version}</td>
                <td align="left"><span class="osversion icon-big ib-succeed"></span>{$env.os_version.current}</td>
            </tr>
            <tr>
                <td align="left">PHP版本</td>
                <td align="left">{$default.php_version}</td>
                <td align="left"><span class="phpversion icon-big ib-succeed"></span>{$env.php_version.current}</td>
            </tr>
            <tr>
                <td align="left">附件上传</td>
                <td align="left">{$default.attachmentupload}</td>
                <td align="left"><span class="attachmentupload icon-big ib-succeed"></span>{$env.attachmentupload.current}</td>
            </tr>
            <tr>
                <td align="left">GD 库</td>
                <td align="left">{$default.gdversion}</td>
                <td align="left"><span class="gdversion icon-big ib-succeed"></span>{$env.gdversion.current}</td>
            </tr>
            <tr>
                <td align="left">磁盘空间</td>
                <td align="left">{$default.diskspace}</td>
                <td align="left"><span class="diskspace icon-big ib-succeed"></span>{$env.diskspace.current}</td>
            </tr>
        </table>
        <div class="caption"><div class="title">目录、文件权限检查</div></div>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">
            <colgroup>
                <col/>
                <col width="15%"/>
                <col width="20%"/>
            </colgroup>
            <tr>
                <th align="left">目录文件</th>
                <th align="left">所需权限</th>
                <th align="left">当前权限</th>
            </tr>
            {$dirfile}
        </table>
        <div class="caption"><div class="title">PHP所需模块检查</div></div>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">
            <colgroup>
                <col/>
                <col width="20%"/>
            </colgroup>
            <tr>
                <th align="left">PHP模块</th>
                <th align="left">检查结果</th>
            </tr>
            {$exts}
        </table>
        <div class="caption"><div class="title">函数依赖性检查</div></div>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">
            <colgroup>
                <col/>
                <col width="20%"/>
            </colgroup>
            <tr>
                <th align="left">函数名称</th>
                <th align="left">检查结果</th>
            </tr>
            {$func}
        </table>
        <div align="center" style="margin-top:50px;"><input type="hidden" name="step" value="{$step}" /><input class="btn-big btn-big-40" id="next" type="submit" value="下一步" />&nbsp;&nbsp;&nbsp;&nbsp;<a href="/install.php?step=0" class="back">上一步</a></div>
    </section>
    </form>
</div>
<script type="text/javascript">
$(function(){
    if ('{$env.os_version.success}' == 'false') {
        $('span.osversion').removeClass('ib-succeed').addClass('ib-fail');
    }
    if ('{$env.php_version.success}' == 'false') {
        $('span.phpversion').removeClass('ib-succeed').addClass('ib-fail');
    }
    if ('{$env.gdversion.success}' == 'false') {
        $('span.gdversion').removeClass('ib-succeed').addClass('ib-fail');
    }
    if ('{$env.attachmentupload.success}' == 'false') {
        $('span.attachmentupload').removeClass('ib-succeed').addClass('ib-fail');
    }
    if ('{$env.diskspace.success}' == 'false') {
        $('span.diskspace').removeClass('ib-succeed').addClass('ib-fail');
    }
});
function checked() {
    if ($('span.ib-fail').size() > 0) {
        return Install.showMessage('图度云办公系统所需环境检查结果不通过，请先整理你服务器的环境', false);
    }

    return true;
}
</script>
</body>
</html>