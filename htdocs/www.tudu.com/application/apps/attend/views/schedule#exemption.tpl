<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1019" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/attend/plan.js?1002" type="text/javascript"></script>
<style type="text/css">
.color_grid{
    margin:0 5px;
    background-color: #89A136;
}
</style>
</head>

<body>
<form action="/app/attend/schedule/saveexemption" method="post" id="theform">
    {{include file="attend^tab.tpl" tab="schedule"}}
    <div class="tab-panel-body">
    {{include file="schedule^toolbar.tpl" tab="plan"}}
        <div class="readmailinfo">
            <div class="module">
                <div class="module_title"><strong>{{$schedule.name}}{{$LANG.plan}}</strong>&nbsp;<span class="gray">{{$LANG.exemption_title_tips}}</span></div>
                <div class="line_bold"></div>
                <table border="0" cellspacing="0" cellpadding="5">
                    <tr>
                        <td align="right">{{$LANG.plan_name}}</td>
                        <td><input name="name" type="text" class="input_text" style="width:200px;" value="{{$schedule.name}}" _disabled="disabled" disabled="disabled" /></td>
                        <td><a _scid="{{$schedule.scheduleid}}" href="javascript:void(0)" class="color_grid" style="background-color:{{$schedule.bgcolor}}"></a><span class="gray">{{$LANG.exemption_tips}}</span></td>
                    </tr>
                    <tr>
                        <td align="right" valign="top">{{$LANG.affect_users}}</td>
                        <td valign="top"><div id="user-box" class="blank_box" style="height:100px;">
                        </div></td>
                        <td valign="top"><a href="javascript:void(0)" id="add-user"><span class="icon icon_plus"></span>{{$LANG.add_user}}</a></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="toolbar">
            <div style="height:24px;">
                <input type="button" id="save" class="btn" value="{{$LANG.save}}" /><input type="button" id="cancel" class="btn" value="{{$LANG.cancel}}" onclick="location='/app/attend/schedule/index'" />
            </div>
        </div>
    </div>
 </form>
 
<div class="pop_wrap" id="color_panel" style="width:218px;position:absolute;display:none">
    <div class="color_list" style="width:200px;">
    {{foreach item=color from=$bgcolors}}<div class="color_block"><div style="background-color:{{$color}}"></div><input type="hidden" name="color" value="{{$color}}" /></div>{{/foreach}}
    </div>
</div>
</body>
<script type="text/javascript">
$(function(){
    TOP.Label.focusLabel('attend');
    TOP.Frame.title('{{$schedule.name}} - {{$LANG.schedule_plan}}');
    TOP.Frame.hash('m=app/attend/schedule/exemption');

    var role = {{if !$role.admin}}false{{else}}true{{/if}};
    Attend.Plan.initExemption(role);
});
</script>
</html>