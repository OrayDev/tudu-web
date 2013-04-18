<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.board_search}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/boardsearch.source.js" type="text/javascript"></script>
</head>
<body>
    <div class="position">
        <p><a href="/board/">{{$LANG.board_home}}</a><span class="icon icon_sub"></span><a href="/board/search-form">{{$LANG.search}}</a></p>
    </div>
<form id="theform" action="/board/search" method="get" class="panel panel_bg">
    <div class="search_tab"><strong>{{$LANG.search}}</strong></div>
    <div class="search_content">
        <div class="s_words"><input class="input_text" name="keyword" type="text"><input type="submit" class="btn" value="{{$LANG.search}}"></div>
        <div class="s_condition">
            <strong class="f14">{{$LANG.search_option}}</strong>
            <div class="line_bold"></div>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td align="right" width="130"><a href="javascript:void(0)" id="select-from">{{$LANG.sender}}</a>{{$LANG.cln}}</td>
                <td><input class="input_text" name="from" type="text" id="inputfrom" /><input id="from" type="hidden" value="" /></td>
              </tr>
              <tr>
                <td align="right"><a href="javascript:void(0)" id="select-to">{{$LANG.receiver}}</a>{{$LANG.cln}}</td>
                <td><input class="input_text" name="to" type="text" id="inputto" /><input id="to" type="hidden" value="" /></td>
              </tr>
              <tr>
                <td align="right">{{$LANG.tudu_type}}{{$LANG.cln}}</td>
                <td><input name="type[]" type="checkbox" value="task" id="t-task"><label for="t-task">{{$LANG.tudu}}</label>&nbsp;&nbsp;&nbsp;<input name="type[]" type="checkbox" value="discuss" id="t-discuss"><label for="t-discuss">{{$LANG.discuss}}</label>&nbsp;&nbsp;&nbsp;<input name="type[]" type="checkbox" value="notice" id="t-notice"><label for="t-notice">{{$LANG.notice}}</label>&nbsp;&nbsp;&nbsp;</td>
              </tr>
              <tr>
               <td align="right">{{$LANG.search_time}}{{$LANG.cln}}</td>
               <td><select name="time">
               <option value="">{{$LANG.all_time}}</option>
               <option value="5">5{{$LANG.days}}</option>
               <option value="10">10{{$LANG.days}}</option>
               <option value="15">15{{$LANG.days}}</option>
               <option value="30">30{{$LANG.days}}</option>
               </select>&nbsp;&nbsp;&nbsp;<input name="timetype" type="radio" value="0" id="after" checked="checked" /><label for="after">{{$LANG.between}}</label>&nbsp;&nbsp;&nbsp;<input name="timetype" type="radio" value="1" id="before"><label for="before">{{$LANG.before}}</label></td>
              </tr>
              <tr>
               <td align="right">{{$LANG.sort_type}}{{$LANG.cln}}</td>
               <td><select name="sorttype">
               <option value="lastposttime">{{$LANG.reply_time}}</option>
               <option value="endtime">{{$LANG.end_time}}</option>
               </select>&nbsp;&nbsp;&nbsp;<input name="sortasc" type="radio" value="0" id="drop" checked="checked" /><label for="drop">{{$LANG.sort_desc}}</label>&nbsp;&nbsp;&nbsp;<input name="sortasc" type="radio" value="1" id="rise" /><label for="rise">{{$LANG.sort_asc}}</label></td>
              </tr>
              <tr>
                <td align="right" valign="top">{{$LANG.search_area}}{{$LANG.cln}}</td>
                <td>
                <select multiple="multiple" name="bid[]" style="width:200px;height:150px">
                    {{foreach from=$boards item=board}}
                    {{if $board.type == 'zone' && $board.children}}
                    <optgroup label="{{$board.boardname|escape:'html'}}">
                        {{foreach from=$board.children item=item}}
                        <option value="{{$item.boardid}}">{{$item.boardname|escape:'html'}}</option>
                        {{/foreach}}
                    </optgroup>
                    {{/if}}
                    {{/foreach}}
                </select>
                </td>
              </tr>
            </table>
         </div>
    </div>
</form>
<script type="text/javascript">
<!--
$(function(){
    TOP.Frame.title('{{$LANG.board_search}}');
    TOP.Frame.hash('m=board/search-form');
    BoardSearch.init();
});
-->
</script>
</body>
</html>
