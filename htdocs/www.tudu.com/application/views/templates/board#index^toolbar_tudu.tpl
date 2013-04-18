<div class="toolbar">
    <div><div>{{if $access.deletetudu}}<button class="btn" type="button" name="delete">{{$LANG.delete}}</button>{{/if}}{{if $ismoderators}}<button class="btn" type="button" name="move">{{$LANG.move_tudu}}</button><span class="tb-sep"></span>{{/if}}<button class="btn" type="button" name="send"{{if $board.status == 2}} disabled="disabled"{{/if}}>{{$LANG.create_tudu}}</button>{{if 0}}<select name="mark">
    <option value="">{{$LANG.signas}}</option>
    <option value="unread">{{$LANG.unread}}</option>
    <option value="read">{{$LANG.read}}</option>
    </select>{{/if}}</div></div>
    {{pagenavigator url=$pageinfo.url currpage=$pageinfo.currpage query=$pageinfo.query recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl" numcount=5}}
</div>