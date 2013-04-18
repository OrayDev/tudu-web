<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>组织架构</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.tree.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/base.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/select.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/department.js?1004" type="text/javascript"></script>
</head>
<body>

<p style="padding:4px 5px 9px; height:16px;line-height:16px;"><strong class="f14 text-title">组织架构</strong></p>
<div id="float-toolbar" class="float-toolbar">
<div class="toolbar">
    <input name="create" type="button" class="btn wd85" value="新建部门"/>
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">
    <tr>
        <th class="td-first" align="left"><div class="td-space">部门名称</div></th>
        <th width="170" align="left"><div class="td-space">负责人</div></th>
        <th width="210" align="left"><div class="td-space">操作</div></th>
        <th width="50" class="td-last" align="left"><div class="td-space">排序</div></th>
    </tr>
</table>
</div>
<div id="toolbar">
<div class="toolbar">
    <input name="create" type="button" class="btn wd80" value="新建部门"/>
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list">
    <tr>
        <th class="td-first" align="left"><div class="td-space">部门名称</div></th>
        <th width="170" align="left"><div class="td-space">负责人</div></th>
        <th width="210" align="left"><div class="td-space">操作</div></th>
        <th width="50" class="td-last" align="left"><div class="td-space">排序</div></th>
    </tr>
</table>
</div>
<div id="dept-list" style="background:#fff;">
</div>
<div class="list-btm-bar"></div>

<script type="text/javascript">
var _DEPTS = [];
{{foreach from=$depts item=item}}
_DEPTS.push({deptid: '{{$item.deptid|replace:"^":"_"}}', deptname: '{{$item.deptname|escape:'javascript'}}', moderators: [{{foreach name=foo item=moderator from=$item.moderators}}{{if !$smarty.foreach.foo.first}},{{/if}}'{{$moderator|escape:'javascript'}}'{{/foreach}}], parentid: '{{$item.parentid|replace:"^":"_"}}', ordernum: '{{$item.ordernum}}', prefix: '{{$item.prefix}}', 'firstnode': {{if $item.firstnode}}1{{else}}0{{/if}}, 'lastnode': {{if $item.lastnode}}1{{else}}0{{/if}}});
{{/foreach}}
var _ORGNAME = '{{$org.orgname|default:$org.orgid}}';
_TOP.switchMod('dept');
Department.init(_DEPTS, _ORGNAME);

new FixToolbar({
    src: '#toolbar',
    target: '#float-toolbar'
});
</script>
</body>
</html>