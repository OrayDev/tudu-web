<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.tudu_flows}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1032" type="text/javascript"></script>
<script type="text/javascript">
<!--
var LH = 'm=flow';
if (top == this) {
	location = '/frame#' + LH;
}
-->
</script>
</head>
<body>
{{assign var="currUrl" value=$smarty.server.REQUEST_URI|urlencode}}
<div class="position">
	<p style="float:right;"><input name="keyword" id="keyword" type="text" class="input_text" style="width:125px;" title="{{$LANG.serach_keywords_tips}}" />&nbsp;<input class="btn" id="dosearch" type="button" value="{{$LANG.serach_button}}" /></p>
	<p><strong class="title">{{$LANG.tudu_flows}}</strong>{{if $access.create}}<a href="/flow/modify?back={{$currUrl}}">[{{$LANG.create_flow}}]</a>{{/if}}</p>
</div>
<div class="board">
    {{if $favors && count($favors)}}
    <div class="board_title">
        <h2><a href="javascript:void(0);" onClick="Flow.toggle('f-_favor');">{{$LANG.favor_flow}}</a></h2>
        <div class="fold"><a class="icon_fold icon_unfold" _bid="^favor" href="javascript:void(0);" onClick="Flow.toggle('f-_favor');" id="f-_favor-icon"></a></div>
    </div>
    <div class="board_body" id="f-_favor" style="display:none;">
        {{foreach item=item from=$favors}}
        <div class="category_2" id="flow-_favor-{{$item.flowid}}">
            <table border="0" cellspacing="0" cellpadding="0" class="grid_list">
                <colgroup>
                    <col>
                    <col width="120">
                </colgroup>
                <tr>
                    <td>
                        <p><a href="/tudu/modify?bid={{$item.boardid}}&flowid={{$item.flowid}}&back={{$currUrl}}"><strong>{{$item.subject}}</strong></a>&nbsp;<a href="javascript:void(0);" onclick="Flow.showChart('{{$item.flowid}}')"><span class="icon icon_workflow"></span>{{$LANG.flow_chart}}</a></p>
                        <p>{{$item.description|strip_tags}}</p>
                    </td>
                    <td align="right">{{if $item.access.modify}}<a href="/flow/modify?flowid={{$item.flowid}}&back={{$currUrl}}">[{{$LANG.modify}}]</a>&nbsp;&nbsp;{{/if}}{{if $item.access.delete}}<a href="javascript:void(0)" onclick="Flow.deleteFlow('{{$item.flowid}}')">[{{$LANG.delete}}]</a>{{/if}}</td>
                </tr>
            </table>
        </div>
        {{/foreach}}
    </div>
    {{/if}}

    {{foreach item=flow from=$flows}}
    <div _type="zone-item">
    <div class="board_title">
		<h2><a href="javascript:void(0);" onClick="Flow.toggle('f-{{'^'|str_replace:'_':$flow.boardid}}');">{{$flow.boardname}}</a></h2>
		<div class="fold"><a class="icon_fold icon_unfold" _bid="{{'^'|str_replace:'_':$flow.boardid}}" href="javascript:void(0);" onClick="Flow.toggle('f-{{'^'|str_replace:'_':$flow.boardid}}');" id="f-{{'^'|str_replace:'_':$flow.boardid}}-icon"></a></div>
	</div>
	<div class="board_body" id="f-{{'^'|str_replace:'_':$flow.boardid}}" style="display:none;">
	    {{foreach item=item from=$flow.children}}
		<div class="category_2" id="flow-{{$item.flowid}}" _type="flow-item">
			<table border="0" cellspacing="0" cellpadding="0" class="grid_list">
				<colgroup>
					<col>
					<col width="120">
				</colgroup>
				<tr>
					<td>
						<p><a href="/tudu/modify?bid={{$item.boardid}}&flowid={{$item.flowid}}&back={{$currUrl}}"><strong>{{$item.subject}}</strong></a>&nbsp;<a href="javascript:void(0);" onclick="Flow.showChart('{{$item.flowid}}')" style="text-decoration:none;"><span class="icon icon_flow" style="margin-top:2px;vertical-align:top"></span><span class="flow_chart">{{$LANG.flow_chart}}</span></a></p>
						<p>{{$item.description|strip_tags}}</p>
					</td>
					<td align="right">{{if $item.access.modify}}<a href="/flow/modify?flowid={{$item.flowid}}&back={{$currUrl}}">[{{$LANG.modify}}]</a>&nbsp;&nbsp;{{/if}}{{if $item.access.delete}}<a href="javascript:void(0)" onclick="Flow.deleteFlow('{{$item.flowid}}')">[{{$LANG.delete}}]</a>{{/if}}</td>
				</tr>
			</table>
		</div>
		{{foreachelse}}
		<div class="category_2">
		    <table border="0" cellspacing="0" cellpadding="0" class="grid_list">
		        <tr>
		            <td style="height:80px;text-align:center;">{{$LANG.flow_null}}{{if $access.create}}{{$LANG.comma}}{{$LANG.please}}<a href="/flow/modify?back={{$currUrl}}">{{$LANG.create_flow}}</a>{{/if}}</td>
		        </tr>
		    </table>
		</div>
		{{/foreach}}
	</div>
	</div>
	{{/foreach}}
	<div id="flownull" style="display:none;background: #f4f4f4;padding: 50px 0;text-align:center">{{$LANG.flow_null}}{{if $access.create}}{{$LANG.comma}}{{$LANG.please}}<a href="/flow/modify?back={{$currUrl}}">{{$LANG.create_flow}}</a>{{/if}}</div>
</div>
<script src="{{$options.sites.static}}/js/flow.js?1004" type="text/javascript"></script>
<script type="text/javascript">
<!--
$(function(){
    TOP.Frame.title('{{$LANG.tudu_flows}}');
    TOP.Frame.hash(LH);

    Flow.initList();

    if (!$('div[_type="flow-item"]').size()) {
        $('#flownull').show();
        $('div[_type="zone-item"]').hide();
    }
});
-->
</script>
</body>

</html>