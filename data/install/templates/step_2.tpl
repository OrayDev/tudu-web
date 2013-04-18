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
    <section class="step">
        <ol>
            <li style="margin:0;"><span class="step-1"></span>欢迎</li>
            <li><span class="step-2 step-2-on"></span>环境检查</li>
            <li><span class="step-3 step-3-on"></span>配置</li>
            <li><span class="step-4"></span>完成</li>
        </ol>
    </section>
    <form id="theform" action="/install.php" method="post" class="config">
        <table border="0" cellspacing="6" cellpadding="6" class="table-form" align="center">
            <tr>
                <td colspan="2"><div class="title">数据库信息</div></td>
            </tr>
            <tr>
                <th align="right">数据库服务器 ：</th>
                <td><input name="dbinfo[host]" type="text" class="text" style="width:330px" value="{$mysql.host}" /></td>
            </tr>
            <tr>
                <th align="right">数据库端口号 ：</th>
                <td><input name="dbinfo[port]" type="text" class="text" style="width:330px" value="{$mysql.port}" /></td>
            </tr>
            <tr>
                <th align="right">数据库账号 ：</th>
                <td><input name="dbinfo[user]" type="text" class="text" style="width:330px" value="{$mysql.user}" /></td>
            </tr>
            <tr>
                <th align="right">数据库密码 ：</th>
                <td><input name="dbinfo[password]" type="text" class="text" style="width:330px" value="{$mysql.password}" /></td>
            </tr>
            <tr>
                <th align="right">数据库名 ：</th>
                <td><input name="dbinfo[database]" type="text" class="text" style="width:330px" value="{$mysql.database}" /></td>
            </tr>
            <tr>
                <td colspan="2"><div class="title">HTTPSQS信息</div></td>
            </tr>
            <tr>
                <th align="right">服务器地址 ：</th>
                <td><input name="httpsqs[host]" type="text" class="text" style="width:330px" value="{$httpsqs.host}" /></td>
            </tr>
            <tr>
                <th align="right">服务器端口号 ：</th>
                <td><input name="httpsqs[port]" type="text" class="text" style="width:330px" value="{$httpsqs.port}" /></td>
            </tr>
            <tr>
                <td colspan="2"><div class="title">Memcache信息</div></td>
            </tr>
            <tr>
                <th align="right">服务器地址 ：</th>
                <td><input name="memcache[host]" type="text" class="text" style="width:330px" value="{$memcache.host}" /></td>
            </tr>
            <tr>
                <th align="right">服务器端口号 ：</th>
                <td><input name="memcache[port]" type="text" class="text" style="width:330px" value="{$memcache.port}" /></td>
            </tr>
            <tr>
                <td colspan="2"><div class="title">图度云办公系统信息</div></td>
            </tr>
            <tr>
                <th align="right">云办公系统ID ：</th>
                <td><input name="tudu[orgid]" type="text" class="text" style="width:330px" /></td>
            </tr>
            <tr>
                <th align="right">公司名称 ：</th>
                <td><input name="tudu[orgname]" type="text" class="text" style="width:330px" /></td>
            </tr>
            <tr>
                <th align="right">超级管理员账号 ：</th>
                <td><input name="tudu[userid]" type="text" class="text" style="width:330px" value="admin" /></td>
            </tr>
            <tr>
                <th align="right">密码 ：</th>
                <td><input name="tudu[password]" type="password" class="text" style="width:330px" /></td>
            </tr>
            <tr>
                <th align="right">确认密码 ：</th>
                <td><input name="tudu[password2]" type="password" class="text" style="width:330px" /></td>
            </tr>
            <tr>
                <th align="right">&nbsp;</th>
                <td><div style="margin-top:25px;"><input type="hidden" name="step" value="{$step}" /><input class="btn-big btn-big-40" id="next" type="submit" value="下一步">&nbsp;&nbsp;&nbsp;&nbsp;<a href="/install.php?step=1" class="back">上一步</a></div></td>
            </tr>
        </table>
    </form>
</div>
</body>
<script type="text/javascript">
<!--
$(function(){
    Install.initConfig();
});
-->
</script>
</html>