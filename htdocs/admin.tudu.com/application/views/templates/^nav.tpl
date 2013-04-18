{{strip}}
<div class="navigation">
    <a href="{{$basepath}}/"{{if $tab == 'index' || !$tab}} class="current"{{/if}}>后台首页</a>
    <a href="{{$basepath}}/org/"{{if $tab == 'org'}} class="current"{{/if}}>组织信息</a>
    <a href="{{$basepath}}/frame/#{{$basepath}}/user/user"{{if $tab == 'sys'}} class="current"{{/if}}>系统管理</a>
</div>
{{/strip}}