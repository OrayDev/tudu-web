<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$board.boardname|escape:'html'}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/card.js?1001" type="text/javascript"></script>
<script type="text/javascript">
<!--
var LH = 'm=board&bid={{$board.boardid}}';
if (top == this) {
    location = '/frame#' + LH;
}
-->
</script>

</head>
<body>

{{include file="^boardnav.tpl" showSearch = true}}
<div class="board_subarea">
    <!-- <div class="board_option"><select name="" style="width:160px;"><option>跳转至版块</option></select></div> -->
    {{strip}}
    <p><strong class="f14">{{$board.boardname|escape:'html'}}</strong><!-- &nbsp;&nbsp;(&nbsp;共1250个图度,11345个回复&nbsp;)-->{{if $access.modify}}&nbsp;&nbsp;<a href="/board/modify?bid={{$board.boardid}}">[{{$LANG.modify}}]</a>{{/if}}&nbsp;&nbsp;<a href="javascript:void(0)" id="board-attention">[{{if !$board.isattention}}{{$LANG.add_attention}}{{else}}{{$LANG.remove_attention}}{{/if}}]</a>
    {{if $access.close}}&nbsp;&nbsp;<a href="javascript:void(0);" onclick="Board.closeBoard('{{$board.boardid}}', {{if $board.status != 2}}true{{else}}false{{/if}})">[{{if $board.status != 2}}{{$LANG.close_board}}{{else}}{{$LANG.open_board}}{{/if}}]</a>{{/if}}
    {{*&nbsp;&nbsp;<a href="javascript:void(0);" onclick="Board.clearBoard('{{$board.boardid}}')">[{{$LANG.clear_board}}]</a>*}}
    {{if $access.delete}}&nbsp;&nbsp;<a href="javascript:void(0);" onclick="Board.deleteBoard('{{$board.boardid}}')">[{{$LANG.delete_board}}]</a>{{/if}}
    </p>
    {{/strip}}
    {{if $board.attribute}}
    <p><span class="gray">{{$LANG.attribute}}{{$LANG.cln}}</span>{{foreach name=attribute item=attribute from=$board.attribute}}{{if !$smarty.foreach.attribute.first}}、{{/if}}{{$attribute}}{{/foreach}}</p>
    {{/if}}
    <p>
        {{if $board.moderators}}
        <span class="gray">{{$LANG.moderators}}{{$LANG.cln}}</span>
        {{foreach name=foo item=moderator from=$board.moderators}}{{if !$smarty.foreach.foo.first}},{{/if}}{{$moderator}}{{/foreach}}&nbsp;&nbsp;
        {{/if}}
        {{if $board.groups}}
        <input id="board-groups" type="hidden" value="{{foreach item=item from=$board.groups}}{{$item|cat:"\n"}}{{/foreach}}" />
        <span class="gray">{{$LANG.join_members}}{{$LANG.cln}}</span>
        <span id="groups-name"></span>
        {{/if}}
    </p>
    {{if $board.memo}}
    <div class="fold_box">
    <span href="javascript:void(0);" class="icon icon_fold"></span>
    {{$board.memo|default:"&nbsp;"}}
    </div>
    {{/if}}
</div>

<form action="" method="post" class="panel">
    <div class="panel-body">
    {{if count($classes) > 0}}
    <div class="board-class-list">
        <a href="/board/?bid={{$board.boardid}}"{{if !$pageinfo.query.cid}} class="class-current"{{/if}}>全部</a>
        {{foreach item=class from=$classes}}
        <a href="/board/?bid={{$board.boardid}}&cid={{$class.classid}}"{{if $pageinfo.query.cid == $class.classid}} class="class-current"{{/if}}>{{$class.classname}}</a>
        {{/foreach}}
    </div>
    {{/if}}
    <div id="float-toolbar" class="float-toolbar">
    {{include file="board#index^toolbar_tudu.tpl"}}
    <table cellspacing="0" class="grid_thead">
      <tr>
        <td width="30" align="center" style="padding-left:0;"><input name="checkall" type="checkbox" value="{{$tudu.tuduid}}"></td>
        <td width="20">&nbsp;</td>
        <td width="20">&nbsp;</td>
        <td class="title_line" align="center" width="40"><div style="padding-left:4px"><span class="mailtitle"></span></div></td>
        <td class="title_line"><div class="space">{{$LANG.subject}}</div></td>
        <td width="100" class="title_line"><div class="space">{{$LANG.sender}}</div></td>
        <td width="100" class="title_line"><div class="space">{{$LANG.receiver}}</div></td>
        <td width="110" class="title_line"><div class="space">{{$LANG.endtime}}</div></td>
        <td width="90" class="title_line"><div class="space">{{$LANG.tudu_percent}}</div></td>
        <td width="100" class="title_line" style="padding-left:0"><div class="space">{{$LANG.lastpost}}</div></td>
      </tr>
    </table>
    </div>
    <div id="toolbar">{{include file="board#index^toolbar_tudu.tpl"}}</div>
    {{include file="board#tudu^list.tpl"}}
    </div>
</form>

<div class="pop_wrap" id="move-tudu-win" style="display:none;position:absolute;background:#ebf4d8;">
<form id="move-tudu" action="/tudu-mgr/move" method="post" enctype="multipart/form-data">
    <div class="pop">
        <div class="pop_header"><strong></strong><a class="icon icon_close close"></a></div>
        <div class="pop_body" id="reply-edit">
            <input type="hidden" name="fbid" value="{{$board.boardid}}" />
            <table cellspacing="2" cellpadding="3" border="0">
                <tr>
                    <td>{{$LANG.move_to}}{{$LANG.cln}}</td>
                </tr>
                <tr>
                    <td>
                    <select name="bid" style="width:195px">
                        <option value="">{{$LANG.select_board}}</option>
                        {{foreach from=$boards item=item}}
                        {{if $item.type == 'zone' && $item.children}}
                        <optgroup label="{{$item.boardname|escape:'html'}}">
                            {{foreach from=$item.children item=child}}
                            <option value="{{$child.boardid}}"{{if $tudu.boardid == $child.boardid}} selected="selected"{{/if}}{{if $child.isclassify}} _classify="1"{{/if}}>{{$child.boardname|escape:'html'}}</option>
                            {{/foreach}}
                        </optgroup>
                        {{/if}}
                        {{/foreach}}
                    </select>
                    </td>
                </tr>
                <tr id="toclass" style="display:none">
                    <td>
                    <select name="cid" style="width:195px">
                        <option value="">{{$LANG.select_class}}</option>
                    </select>
                    </td>
                </tr>
            </table>
        </div>

        <div class="pop_footer">
        <div>
        <button name="confirm" type="submit" class="btn">{{$LANG.confirm}}</button><button type="button" class="btn close">{{$LANG.cancel}}</button>
        </div>
        </div>
    </div>
</form>
</div>
<script src="{{$options.sites.static}}/js/board.tudulist.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
$(function(){
    var _LABEL_COUNT = {
        {{foreach item=item from=$labels name=label}}'{{$item.labelalias}}' : {{$item.unreadnum}}{{if $smarty.foreach.label.index + 1 < count($labels)}},{{/if}}{{/foreach}}
    };

    TOP.Frame.title('{{$board.boardname|escape:'html'}}');
    TOP.Label.focusLabel('board');
    TOP.Frame.hash(LH);

    Board.tuduList.boardId = '{{$board.boardid}}';
    Board.tuduList.currUrl = '{{$smarty.server.REQUEST_URI|escape:'url'}}';
    Board.tuduList.attention = {{if !$board.isattention}}'add'{{else}}'remove'{{/if}};
    Board.setLang({
        removeattention: '[{{$LANG.remove_attention}}]',
        addattention: '[{{$LANG.add_attention}}]',
        boardname: '{{$board.boardname|escape:'html'|truncate:12}}',
        notspecify: '{{$LANG.not_specify}}'
    });
    Board.tuduList.init();
});

{{if $board.groups}}
/**
 * 处理板块参与人名称
 */
window.onload = function() {
	Board.setGroups();
}
{{/if}}
-->
</script>

</body>
</html>
