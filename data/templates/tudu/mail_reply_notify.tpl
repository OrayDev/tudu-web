<html xmlns="http://www.w3.org/1999/xhtml">
<body>
<div style="line-height:16px;font-size:12px;">
<p style="margin-bottom:10px;">Hi, {$name}</p>

<p><strong>您刚收到一个新的图度回复</strong></p>
<p>{$subject}</p>
<p>发起人：{$sender}</p>
<p>更新日期：{$lastupdate}</p>
<p>{$content}</p>

<p style="margin:10px 0">详细请点击：<a href="{$url}" target="_blank">{$url}</a></p>
<p>如果通过点击以上链接无法访问，请将该网址复制并粘贴到浏览器地址栏中。</p>
{$authinfo}
<hr />
<p>这是一封系统发送邮件，<span style="color:#f00">请不要直接回复。</span></p>
<p style="margin-top:25px;text-align:right">本服务由<a href="http://www.tudu.com/" target="_blank">图度工作系统</a>提供</p>
</div>
</body>
</html>