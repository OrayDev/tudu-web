<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1019" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/attend/plan.js?1002" type="text/javascript"></script>
<style type="text/css">
<!--
.plan-table {
    margin: 5px 0;
}
.plan-table td.td-cp{
    padding: 0 5px;
    height: 31px;
    line-height: 29px;
}
.schedule-list {
    line-height:23px;
}
.schedule-list a{
    display:inline-block;
    *display:inline;
    *zoom:1;
    margin-right:15px;
}
-->
</style>
</head>
<body>
{{include file="attend^tab.tpl" tab="schedule"}}
<form action="/app/attend/schedule/saveplan" method="post" id="theform">
<input type="hidden" id="cyclenum" name="cyclenum" value="1" />
<div class="tab-panel-body">
    {{include file="schedule^toolbar.tpl" tab="plan"}}
    <div class="readmailinfo">
        <div class="module">
            <div class="module_title"><strong>{{$LANG.plan_subject}}</strong>&nbsp;<span class="gray">{{$LANG.plan_desc}}</span><div class="module_title_ext"><a href="http://www.tudu.com/help/question/1154.html" target="_blank">{{$LANG.about_help}}</a></div></div>
            <div class="line_bold"></div>
            <table border="0" cellspacing="0" cellpadding="5">
                <tr>
                    <td align="right" width="120">{{$LANG.schedule_plan}}{{$LANG.cln}}</td>
                    <td width="80">
                        <label for="cycle-week"><input type="radio" id="cycle-week" name="type" value="0" />按周排班</label>
                    </td>
                    <td width="80">
                        <label for="cycle-month"><input type="radio" id="cycle-month" name="type" value="1" checked="checked" />按月排班</label>
                    </td>
                    <td>
                        <div id="month-option">
                        <select name="year" id="year">
                            {{section name=year start=2012 loop=2022}}
                            <option value="{{$smarty.section.year.index}}"{{if $smarty.section.year.index == $query.year}} selected="selected"{{/if}}>{{$smarty.section.year.index}}{{$LANG.year}}</option>
                            {{/section}}
                        </select>
                        <select name="month" id="month">
                            {{section name=month start=1 loop=13}}
                            <option value="{{$smarty.section.month.index}}"{{if $smarty.section.month.index == $query.month}} selected="selected"{{/if}}>{{$smarty.section.month.index}}{{$LANG.month}}</option>
                            {{/section}}
                        </select>
                        </div>
                    </td>
                </tr>
            </table>
            <table border="0" cellspacing="0" cellpadding="5">
                <tr>
                    <td align="right" valign="top" width="120"><a href="javascript:void(0)" id="add-schedule">添加方案</a></td>
                    <td>
                        <div id="schedule-list" class="schedule-list">
                        </div>
                    </td>
                    <td class="gray">(选择排班，添加排班设置)</td>
                </tr>
            </table>
            <table border="0" cellspacing="0" cellpadding="0" class="plan-table" id="plan-table">
                <tr>
                    <td align="right" width="120" class="td-cp"><input id="checkall" type="checkbox" /><a href="javascript:void(0)" id="add-user">{{$LANG.add_user}}</a></td>
                    <td id="grid-ct" class="td-cp">
                        <div id="schedule-grid"></div>
                        <div id="grid-data"></div>
                    </td>
                </tr>
            </table>
            <table border="0" cellspacing="0" cellpadding="5" id="week-option" style="display:none;">
                <tr>
                    <td align="right" width="120">&nbsp;</td>
                    <td><a href="javascript:void(0)" id="add-cyclenum"><span class="icon icon_plus"></span>{{$LANG.add_week_repeat}}</a>&nbsp;<a href="javascript:void(0)" id="remove-cyclenum" style="margin-left:15px;display:none;"><span class="icon icon_minus" style="background-position: 2px 2px;"></span>{{$LANG.remove_week_repeat}}</a></td>
                </tr>
            </table>
            <table border="0" cellspacing="0" cellpadding="5">
                <tr class="gray">
                    <td align="right" width="120">注意{{$LANG.cln}}</td>
                    <td>勾选名称复选框，即可批量对排班进行设置</td>
                </tr>
                <tr>
                    <td align="right" valign="top" width="120">{{$LANG.remark}}{{$LANG.cln}}</td>
                    <td valign="top">
                    <textarea name="memo" class="input_text" style="height:120px; width:400px;" maxlength="300" onpropertychange="if(value.length>300) value=value.substr(0,300)" ></textarea>
                </tr>
            </table>
        </div>
    </div>
    <div class="toolbar">
        <div>
            <input type="button" id="save" class="btn" value="{{$LANG.save_and_continue}}" /><input type="button" id="save-leave" class="btn" value="{{$LANG.save}}" /><input type="button" id="cancel" class="btn" value="{{$LANG.cancel}}" onclick="location='{{if $back}}{{$back}}{{else}}/app/attend/schedule/user{{/if}}'" />
        </div>
    </div>
</div>
</form>

<div class="pop_wrap" id="color_panel" style="width:218px;position:absolute;display:none">
    <div class="color_list" style="width:200px;">
    {{foreach item=color from=$bgcolors}}<div class="color_block"><div style="background-color:{{$color}}"></div><input type="hidden" name="color" value="{{$color}}" /></div>{{/foreach}}
    </div>
</div>
<script type="text/javascript">
<!--
$(function(){
    TOP.Label.focusLabel('attend');
    TOP.Frame.title('{{$LANG.schedule_plan}}');
    TOP.Frame.hash('m=app/attend/schedule/plan');

    var schedules = {};
    schedules['^root'] = {scheduleId: '^root', name: '排班方案', title: '排班方案', color: '', parentid: ''};
    {{foreach from=$schedules key=key item=item}}
    schedules['{{$key}}'] = {scheduleId: '{{$item.scheduleid}}', name: '{{$item.name|truncate:12|escape:'html'}}', title: '{{$item.name|escape:'html'}}', color: '{{$item.bgcolor}}', parentid: '^root'};
    {{/foreach}}
    Attend.Plan.setSchedules(schedules);

    {{if !$role.admin}}
    Attend.Plan.setDepts('{{$deptids}}');
    {{/if}}

    {{if $uniqueid}}
    var uniqueIds = [];
    {{foreach from=$uniqueid item=item}}
    uniqueIds.push('{{$item}}');
    {{/foreach}}
    Attend.Plan.setUsers(uniqueIds);
    {{/if}}

    {{if $plans}}
    var plans = [];
    {{foreach from=$plans key=key item=plan}}
    plans.push({'{{$key}}': [{{foreach from=$plan key=day item=scid name="plan"}}{{if $scid != '^off'}}{'day': '{{$day}}', 'scid': '{{$scid}}'}{{if $smarty.foreach.plan.index + 1 < count($plan)}},{{/if}}{{/if}}{{/foreach}}]});
    {{/foreach}}
    Attend.Plan.setUserPlans(plans);
    {{/if}}

    var back = '{{$back|default:'/app/attend/schedule/user'|escape:'url'}}';

    Attend.Plan.setLang({missing_users: '{{$LANG.missing_user_select}}', weekdays: [{{foreach item=item from=$LANG.weekdays name=weekdays}}'{{$item}}'{{if $smarty.foreach.weekdays.index + 1 < count($LANG.weekdays)}},{{/if}}{{/foreach}}]});
    var role = {{if !$role.admin}}false{{else}}true{{/if}};
    Attend.Plan.init(role, back);
});
-->
</script>
</body>
</html>