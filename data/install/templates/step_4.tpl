<!DOCTYPE html>
<html>
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en"> <!--<![endif]-->
<!--[if lt IE 9]> <script src="/js/html5.js" type="text/javascript"></script> <![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>图度开源版安装界面</title>
<link href="/css/install.css" type="text/css" rel="stylesheet">
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
            <li><span class="step-4 step-4-on"></span>完成</li>
        </ol>
    </section>
    <section class="succeed">
        <div class="title">恭喜您，图度v1.0安装成功！</div>
        <p>图度地址：<a href="{$url}">{$domain}</a></p>
        <div style="margin-top:30px;" align="center"><input class="btn-big" type="button" value="登陆图度" onclick="location='{$url}'"></div>
    </section>
</div>
</body>
</html>