{{strip}}
<div class="tabs">
    <a href="{{$basepath}}/secure/login/"{{if $tab == 'login'}} class="current"{{/if}}><strong>帐号登录</strong></a>
    <a href="{{$basepath}}/secure/ip/"{{if $tab == 'ip'}} class="current"{{/if}}><strong>IP地址过滤</strong></a>
    <a href="{{$basepath}}/secure/log/"{{if $tab == 'log'}} class="current"{{/if}}><strong>后台操作日志</strong></a>
    <a href="{{$basepath}}/secure/log/login"{{if $tab == 'loginlog'}} class="current"{{/if}}><strong>前台登录日志</strong></a>
</div>
{{/strip}}