{{strip}}
<div class="toolbar">
    <input name="add" type="button" class="btn wd85" value="添加帐号" />
    <input name="edit" type="button" class="btn wd80" value="帐号编辑" />
    <span class="toolbar-space"></span>
    <input name="unlock" type="button" class="btn wd50" value="解锁" />
    <input name="delete" type="button" class="btn wd50" value="删除" />
    <span class="toolbar-space"></span>
    <select name="addgroup" style="width:120px;">
        <option value="">添加到群组</option>
        {{foreach item=group from=$groups}}
        <option value="{{$group.groupid}}">{{$group.groupname}}</option>
        {{/foreach}}
        <option value="^new">新建群组</option>
    </select>
</div>
{{/strip}}