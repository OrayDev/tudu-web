<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.tudu_flows}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1031" type="text/javascript"></script>
<script type="text/javascript">
<!--
var LH = 'm=flow/search&keyword={{$keyword}}';
if (top == this) {
	location = '/frame#' + LH;
}
-->
</script>
</head>
<body>
{{assign var="currUrl" value=$smarty.server.REQUEST_URI|urlencode}}
<div class="position">
	<p style="float:right;"><input name="keyword" id="keyword" type="text" class="input_text" style="width:125px;" value="{{$keyword}}" title="{{$LANG.serach_keywords_tips}}" />&nbsp;<input class="btn" id="dosearch" type="button" value="{{$LANG.serach_button}}" /></p>
	<p><strong class="title">{{$LANG.search_result}}</strong><span>（{{$LANG.total}}&nbsp;<span id="count"></span>&nbsp;{{$LANG.tudu_unit}}）</span></p>
</div>
<div class="toolbar">
    <p><button class="btn" type="button" name="back">{{$LANG.back}}</button></p>
</div>
<div class="board">
    <div class="board_body">
        {{foreach item=flow from=$flows}}
        <div class="category_2" id="flow-{{$flow.flowid}}">
            <table border="0" cellspacing="0" cellpadding="0" class="grid_list">
                <colgroup>
                    <col>
                    <col width="120">
                </colgroup>
            <tr>
                <td>
                    <p><a href="/tudu/modify?bid={{$flow.boardid}}&flowid={{$flow.flowid}}&back={{$currUrl}}"><strong>{{$flow.subject}}</strong></a>&nbsp;<a href="javascript:void(0)" onclick="Flow.showChart('{{$flow.flowid}}')"><span class="icon icon_workflow"></span>{{$LANG.flow_chart}}</a></p>
                    <p>{{$flow.description|strip_tags}}</p>
                </td>
                <td align="right">{{if $flow.access.modify}}<a href="/flow/modify?flowid={{$flow.flowid}}&back={{$currUrl}}">[{{$LANG.modify}}]</a>&nbsp;&nbsp;{{/if}}{{if $flow.access.delete}}<a href="javascript:void(0)" onclick="Flow.deleteFlow('{{$flow.flowid}}', 1)">[{{$LANG.delete}}]</a>{{/if}}</td>
            </tr>
            </table>
        </div>
        {{foreachelse}}
        <div style="padding:50px 10px;text-align:center;">{{$LANG.search_rs_null}}</div>
        {{/foreach}}
    </div>
</div>
<div class="toolbar">
    <p><button class="btn" type="button" name="back">{{$LANG.back}}</button></p>
</div>
<script src="{{$options.sites.static}}/js/flow.js?1003" type="text/javascript"></script>
<script type="text/javascript">
<!--
$(function(){
    TOP.Frame.title('{{$LANG.search_flows}}');
    TOP.Frame.hash(LH);

    Flow.back = '/flow/';
    Flow.initSearchList();
});
-->
</script>

</body>
</html>