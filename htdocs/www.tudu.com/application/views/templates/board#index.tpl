<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.board|escape:'html'}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script type="text/javascript">
<!--
var LH = 'm=board';
if (top == this) {
    location = '/frame#' + LH;
}
-->
</script>
</head>
<body>
{{include file="^boardnav.tpl" showSearch = true showSwitch = true}}
<div class="content">
    {{if $myboards}}
    <div class="board attention" id="z-myboard">
        <div class="board_title"><h2><a href="javascript:void(0)" onClick="BoardList.toggle('b-_myboard');">我的版块</a></h2><div class="fold"><a href="javascript:void(0)" onClick="BoardList.toggle('b-_myboard');" id="b-_myboard-icon" class="icon_fold" _bid="_myboard"></a></div></div>
        <div class="board_body" id="b-_myboard">
            {{foreach key=key item=board from=$myboards}}
            <div class="category_2" id ="my-{{'^'|str_replace:'_':$board.boardid}}"{{if $board.status == 2}} style="display: none;"{{/if}}>
                <table cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="icon_wrap"><img src="/images/icon/board.gif"/></td>
                    <td class="">
                          <dl>
                            <dt><a href="?bid={{$board.boardid}}"><strong>{{$board.boardname|escape:'html'}}</strong></a><!-- ##暂不支持## &nbsp;(今日:{{$stats[$board.boardid].today}}) --></dt>
                            <dd>{{$board.memo|strip_tags}}</dd>
                            {{if $board.moderators}}<dd><span>{{$LANG.moderators}}{{$LANG.cln}}{{foreach name=foo item=moderator from=$board.moderators}}{{if !$smarty.foreach.foo.first}},{{/if}}{{$moderator}}{{/foreach}}</span>{{if $board.groupsperson}}&nbsp;&nbsp;&nbsp;&nbsp;<span title="{{foreach name=group item=group from=$board.groupsperson}}{{if !$smarty.foreach.group.first}},&#13;{{/if}}{{$group.1}}{{if $group.0}}<{{if strpos($group.0, '@')}}{{$group.0}}{{else}}{{$LANG.group}}{{/if}}>{{/if}}{{/foreach}}">{{$LANG.join_members}}{{$LANG.cln}}{{foreach name=group item=group from=$board.groupsperson}}{{if $smarty.foreach.group.index < 6}}{{if !$smarty.foreach.group.first}},{{/if}}{{$group.1}}{{/if}}{{/foreach}}{{if $board.groupsperson && count($board.groupsperson) > 6}}...{{/if}}</span>{{/if}}</dd>{{/if}}
                        </dl>
                    </td>
                    <td class="board_count" align="center">{{$stats[$board.boardid].tudu}}&nbsp;/&nbsp;{{$stats[$board.boardid].post}}</td>
                    <td class="last_reply">
                    {{if count($stats[$board.boardid].last) == 4}}
                        <p class="black">{{$stats[$board.boardid].last[1]|escape:'html'}}</p>
                        <p>{{$stats[$board.boardid].last[3]}} - {{$stats[$board.boardid].last[2]|date_format:'%Y-%m-%d %H:%M'}}</p>
                    {{/if}}
                    </td>
                    <td width="30">
                        <a href="javascript:void(0)" name="up">↑</a>&nbsp;<a href="javascript:void(0)" name="down">↓</a>
                    </td>
                  </tr>
                </table>
            </div>
            {{/foreach}}
        </div>
    </div>
    {{/if}}

    {{foreach key=key item=zone from=$boards}}
    <div class="board" id="z-{{'^'|str_replace:'_':$zone.boardid}}">
        <div class="board_title"><h2><a href="javascript:void(0)" onClick="BoardList.toggle('b-{{'^'|str_replace:'_':$zone.boardid}}');">{{$zone.boardname}}</a></h2><div class="fold"><span>{{if $zone.moderators}}{{$LANG.zone_moderators}}{{$LANG.cln}}{{foreach name=foo item=moderator from=$zone.moderators}}{{if !$smarty.foreach.foo.first}},{{/if}}{{$moderator}}{{/foreach}}&nbsp;&nbsp;{{/if}}</span><a href="javascript:void(0)" onClick="BoardList.toggle('b-{{'^'|str_replace:'_':$zone.boardid}}');" id="b-{{'^'|str_replace:'_':$zone.boardid}}-icon" class="icon_fold" _bid="{{'^'|str_replace:'_':$zone.boardid}}"></a></div><div class="arrow"><a class="icon_arrow arr_up" href="javascript:void(0)"></a><a class="icon_arrow arr_down" href="javascript:void(0)"></a></div></div>
        <div class="board_body" id="b-{{'^'|str_replace:'_':$zone.boardid}}">
            {{foreach key=key item=board from=$zone.children}}
            <div class="category_2" id ="{{'^'|str_replace:'_':$board.boardid}}"{{if $board.status == 2}} style="display: none;"{{/if}}>
                <table cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="icon_wrap"><img src="/images/icon/board.gif"/></td>
                    <td class="">
                          <dl>
                            <dt><a href="?bid={{$board.boardid}}"><strong>{{$board.boardname|escape:'html'}}</strong></a><!-- ##暂不支持## &nbsp;(今日:{{$stats[$board.boardid].today}}) --></dt>
                            <dd>{{$board.memo|strip_tags}}</dd>
                            {{if $board.moderators}}<dd><span>{{$LANG.moderators}}{{$LANG.cln}}{{foreach name=foo item=moderator from=$board.moderators}}{{if !$smarty.foreach.foo.first}},{{/if}}{{$moderator}}{{/foreach}}</span>{{if $board.groupsperson}}&nbsp;&nbsp;&nbsp;&nbsp;<span title="{{foreach name=group item=group from=$board.groupsperson}}{{if !$smarty.foreach.group.first}},&#13;{{/if}}{{$group.1}}{{if $group.0}}<{{if strpos($group.0, '@')}}{{$group.0}}{{else}}{{$LANG.group}}{{/if}}>{{/if}}{{/foreach}}">{{$LANG.join_members}}{{$LANG.cln}}{{foreach name=group item=group from=$board.groupsperson}}{{if $smarty.foreach.group.index < 6}}{{if !$smarty.foreach.group.first}},{{/if}}{{$group.1}}{{/if}}{{/foreach}}{{if $board.groupsperson && count($board.groupsperson) > 6}}...{{/if}}</span>{{/if}}</dd>{{/if}}
                        </dl>
                    </td>
                    <td class="board_count" align="center">{{$stats[$board.boardid].tudu}}&nbsp;/&nbsp;{{$stats[$board.boardid].post}}</td>
                    <td class="last_reply">
                    {{if count($stats[$board.boardid].last) == 4}}
                        <p class="black">{{$stats[$board.boardid].last[1]|escape:'html'}}</p>
                        <p>{{$stats[$board.boardid].last[3]}} - {{$stats[$board.boardid].last[2]|date_format:'%Y-%m-%d %H:%M'}}</p>
                    {{/if}}
                    </td>
                    <td width="30">
                        <a href="javascript:void(0)" name="up">↑</a>&nbsp;<a href="javascript:void(0)" name="down">↓</a>
                    </td>
                  </tr>
                </table>
            </div>
            {{/foreach}}
        </div>
    </div>
    {{/foreach}}
</div>
</body>
<script src="{{$options.sites.static}}/js/boardlist.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
$(function(){
    TOP.Frame.title('{{$LANG.board_list}}');
    TOP.Label.focusLabel('board');
    TOP.Frame.hash(LH);

    BoardList.setLang({expand: '[{{$LANG.expand_zone}}]', collapse: '[{{$LANG.collapse_zone}}]'});
    BoardList.init();
});
-->
</script>

</html>
