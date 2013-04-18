{{strip}}
<div id="tudu-flow" class="grid_list_wrap grid_list_group" style="margin:-7px 0 10px; 0">
	<div class="grid_list_title" style="line-height:24px" id="toggle-flow">
		<span class="icon icon_elbow_minus toggle_flow" style="cursor:pointer;"></span><h3>{{$LANG.tudu_flow}}</h3>
	</div>
	<div id="step-list" class="grid_list_group_ct grid_list_btm_line flow_group" style="padding:4px 5px;">
		<span class="step_item" title="{{$tudu.from.0}}">{{$tudu.from.0}}</span>
		{{strip}}
		{{foreach from=$steps name=step item=item}}
		{{if $item.type == 1}}
		{{foreach from=$item.users item=u}}
		<span class="icon icon_flow_arrow"></span>
		{{if !$u.future || $u.status >= 2}}
        <a href="javascript:void(0);" class="step_item{{if $query.unid == $u.uniqueid}} step_item_selected{{/if}}"{{if $u.stepid == $tudu.stepid && $u.status == 1}} style="font-weight:bold;"{{/if}}{{if $u.status > 2 && $query.unid != $u.uniqueid}} style="color:#c00"{{/if}} title="{{$u.truename}} {{if $u.status == 2}}{{$LANG.examine}}{{$LANG.agree}}{{elseif $u.status == 3}} {{$LANG.examine}}{{$LANG.disagree}}{{else}} {{$LANG.wait_review}}{{/if}}">
        {{$u.truename}}
        {{if $u.status == 2}}({{$LANG.agree}}){{elseif $u.status == 3}}({{$LANG.disagree}}){{else}}({{$LANG.wait_review}}){{/if}}
        </a>
        {{else}}
        <span class="step_item" style="color:#999">{{$u.truename}}({{$LANG.future_review}})</span>
        {{/if}}
        {{/foreach}}
        {{else}}

        {{if count($item.users) > 1}}
        <span class="icon icon_flow_arrow"></span>
        {{if !$item.future}}
        <a href="javascript:void(0);" class="step_item"{{if $item.stepid == $tudu.stepid}} style="font-weight:bold;"{{/if}} title="{{foreach from=$item.users name=stepuser item=user}}{{$user.truename}}{{if $smarty.foreach.stepuser.index < count($item.users) - 1}},{{/if}}{{/foreach}}" title="{{$LANG.multi_accepter}} {{$LANG.execute}}">
        {{$LANG.multi_accepter}}
        </a>
        {{else}}
        <span class="step_item" style="color:#999" title="{{foreach from=$item.users name=stepuser item=u}}{{if $u.uniqueid == $user.uniqueid}}{{$LANG.me}}{{else}}{{$u.truename}}{{/if}}{{if $smarty.foreach.stepuser.index < count($item.users) - 1}},{{/if}}{{/foreach}}">
        {{$LANG.multi_accepter}}
        </span>
        {{/if}}
        {{else}}
        {{if !$item.future}}
        <span class="icon icon_flow_arrow"></span>
        <a href="javascript:void(0);" class="step_item{{if $query.unid == $item.users.0.uniqueid}} step_item_selected{{/if}}"{{if $item.users.0.stepid == $tudu.stepid}} style="font-weight:bold;"{{elseif $item.users.0.status == 4}} style="text-decoration:line-through"{{/if}} title="{{$item.users.0.truename}} {{if $item.users.0.stepid == $tudu.stepid}}{{$tudu.percent|default:0}}%{{else}}{{$LANG.execute}}{{/if}}">
        {{$item.users.0.truename}}
        {{if $item.users.0.stepid == $tudu.stepid}}({{$tudu.percent|default:0}}%){{/if}}
        </a>
        {{else}}
        <span class="icon icon_flow_arrow"></span>
        <span style="color:#999">
        {{$item.users.0.truename}}
        {{if $item.users.0.stepid == $tudu.stepid}}({{$tudu.percent|default:0}}%){{/if}}
        </span>
        {{/if}}
        {{/if}}

        {{/if}}
		{{/foreach}}
		{{/strip}}
	</div>
</div>
{{/strip}}
<script type="text/javascript">
<!--
$('#toggle-flow span, #toggle-flow h3').click(function(){
	var ico = $('#toggle-flow span.icon');
	if (ico.hasClass('icon_elbow_minus')) {
		ico.removeClass('icon_elbow_minus').addClass('icon_elbow_plus');
	} else {
		ico.removeClass('icon_elbow_plus').addClass('icon_elbow_minus');
	}
	$('#step-list').toggle();
});

-->
</script>