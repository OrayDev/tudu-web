<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$flow.subject}}</title>
{{include file="^style.tpl"}}
<style type="text/css">
<!--
html, body {
	height:100%;
	padding: 0;
	margin: 0
}

.otrvml_obj {behavior: url(#default#VML);display:inline-block;z-index: 2}
.otrvml_line {behavior: url(#default#VML);display:inline-block;z-index: 1}
-->
</style>
</head>

<body>
<div>
    <div style="padding: 15px;">
    <p class="position" style="margin-bottom: 5px"><strong class="title" style="margin-left: 0;">{{$flow.subject}}</strong></p>
    <p class="gray">{{$flow.description|strip_tags}}</p>
    </div>
</div>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td><div id="container"></div></td>
    </tr>
</table>
</body>
<script type="text/javascript" src="{{$options.sites.static}}/js/otr/otr.js"></script>
<script type="text/javascript" src="{{$options.sites.static}}/js/otr/graph.js"></script>
<script type="text/javascript" src="{{$options.sites.static}}/js/otr/chart/flow.js?1003"></script>
<script type="text/javascript">
<!--
// 定义节点填充
var nodebg = new OTR.Graph.Color({
    x: 0, y: 0, dx: 0, dy: 32,
    colors: [[0, '#f1f3f5'], [1, '#dee2e5']]
});

// 流程图
var flow = new OTR.Chart.Flow({
    id: 'chart-flow',
    nodeBackground: nodebg,
    branchBackground: nodebg,
    nodeBorderColor: '#c6cdd6',
    nodeBoarderRadius: 20,
    branchBorderColor: '#c6cdd6',
    fontColor: '#333333',
    lineColor: '#5c85d8',
    lineWeight: 3,
    nodeMarginVer: 12,
    fontFamily: '微软雅黑',
    lineText: true,
    padding:5
});

flow.addNode(new OTR.Chart.Flow.Node({
    type: OTR.Chart.Flow.TYPE.NODE,
    id: '^head',
    text: '{{$LANG.step_start}}'
}));
{{foreach item=step from=$flow.steps}}
{{if $flow.type == 0}}
flow.addNode(new OTR.Chart.Flow.Node({
    type: OTR.Chart.Flow.TYPE.BRANCH,
    id: '{{$step.stepid}}',
    text: '{{$step.subject}}',
    wayTrue: '{{$step.next}}',
    wayFalse: '{{if $step.prev == '^break'}}^head{{else}}{{$step.prev}}{{/if}}'
}));
{{else}}
flow.addNode(new OTR.Chart.Flow.Node({
    type: OTR.Chart.Flow.TYPE.NODE,
    id: '{{$step.stepid}}',
    text: '{{$step.subject}}'
}));
{{/if}}
{{/foreach}}
flow.addNode(new OTR.Chart.Flow.Node({
    type: OTR.Chart.Flow.TYPE.NODE,
    id: '^end',
    text: '{{$LANG.step_end}}'
}));

flow.renderTo(document.getElementById('container'));

function ajustSize() {
    var cf = document.getElementById('chart-flow'),
        ct = document.getElementById('container');

    var cw = ct.offsetWidth,
        tw = cf.offsetWidth;

    pl = Math.max(0, cw - tw) / 2;

    ct.style.paddingLeft = pl + 'px';
}

window.onresize = ajustSize;
window.onload   = ajustSize;
-->
</script>
</html>