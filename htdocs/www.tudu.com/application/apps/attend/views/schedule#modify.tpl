<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>{{$LANG.attend_class}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1019" type="text/javascript"></script>
<style type="text/css">
.color_grid{
    margin:0 5px;
    background-color: #89A136;
}
</style>
</head>

<body>
<form id="theform" action="/app/attend/schedule/save" method="post">
    <input name="scheduleid" value="{{$schedule.scheduleid}}" type="hidden" />
    <input name="ruleid" value="{{$schedule.ruleid}}" type="hidden" />
    <input name="action" value="{{if $schedule.scheduleid}}update{{else}}create{{/if}}" type="hidden" />
    <input name="bgcolor" value="{{$schedule.bgcolor}}" type="hidden" />
    {{include file="attend^tab.tpl" tab="schedule"}}
    <div class="tab-panel-body">
    {{include file="schedule^toolbar.tpl" tab="schedule"}}
        <div class="readmailinfo">
            <div class="module">
                <div id="custom-plan">
                {{if $schedule.scheduleid == '^default'}}
                <div class="module_title"><strong>{{$LANG.default_schedule_plan}}</strong>&nbsp;&nbsp;&nbsp;&nbsp;<span class="gray">{{$LANG.default_schedule_plan_tips}}</span><div class="module_title_ext"><a href="http://www.tudu.com/help/question/1153.html" target="_blank">{{$LANG.about_help}}</a></div></div>
                {{else}}
                <div class="module_title"><strong>{{$LANG.custom_schedule_plan}}</strong>&nbsp;&nbsp;&nbsp;&nbsp;<span class="gray">{{$LANG.custom_schedule_plan_tips}}</span><div class="module_title_ext"><a href="http://www.tudu.com/help/question/1153.html" target="_blank">{{$LANG.about_help}}</a></div></div>
                {{/if}}
                <div class="line_bold"></div>
                {{if $schedule.scheduleid == '^default'}}
                <table border="0" cellspacing="0" cellpadding="5">
                    <tr>
                        <td align="right">{{$LANG.plan_name}}</td>
                        <td><input name="name" type="text" class="input_text" style="width:200px;" value="{{$schedule.name|escape:'html'}}" _disabled="disabled" disabled="disabled" /><a href="javascript:void(0)" class="color_grid" style="background-color:{{$schedule.bgcolor}}"></a></td>
                        <td><span class="gray">{{$LANG.default_plan_system_tips}}</span></td>
                    </tr>
                </table>
                <p><strong>{{$LANG.setting_plan}}{{$LANG.cln}}</strong></p>
                {{assign var=rules value=$schedule.rules}}
                <table border="0" cellspacing="0" cellpadding="5" class="table_schedule_set">
                    <colgroup>
                        <col />
                        <col width="200" />
                        <col width="200" />
                    </colgroup>
                    <tr>
                        <th align="right"><label><input name="status-1" type="checkbox" value="1"{{if $rules.1.status}} checked="checked"{{/if}} />{{$LANG.week_1}}{{$LANG.cln}}</label><input name="member[]" type="hidden" value="1" /><input type="hidden" value="1" name="week-1" /><input type="hidden" value="{{$rules.1.ruleid}}" name="ruleid-1" /></th>
                        <td>{{$LANG.checkin}}&nbsp;<select name="checkintime-hour-1" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.1.checkintime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>{{$LANG.cln}}<select name="checkintime-min-1" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.1.checkintime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                        <td>{{$LANG.checkout}}&nbsp;<select name="checkouttime-hour-1" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.1.checkouttime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>{{$LANG.cln}}<select name="checkouttime-min-1" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.1.checkouttime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                    </tr>
                    <tr>
                        <th align="right"><label><input name="status-2" type="checkbox" value="1"{{if $rules.2.status}} checked="checked"{{/if}} />{{$LANG.week_2}}{{$LANG.cln}}</label><input name="member[]" type="hidden" value="2" /><input type="hidden" value="2" name="week-2" /><input type="hidden" value="{{$rules.2.ruleid}}" name="ruleid-2" /></th>
                        <td>{{$LANG.checkin}}&nbsp;<select name="checkintime-hour-2" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.2.checkintime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>{{$LANG.cln}}<select name="checkintime-min-2" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.2.checkintime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                        <td>{{$LANG.checkout}}&nbsp;<select name="checkouttime-hour-2" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.2.checkouttime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>{{$LANG.cln}}<select name="checkouttime-min-2" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.2.checkouttime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                    </tr>
                    <tr>
                        <th align="right"><label><input name="status-3" type="checkbox" value="1"{{if $rules.3.status}} checked="checked"{{/if}} />{{$LANG.week_3}}{{$LANG.cln}}</label><input name="member[]" type="hidden" value="3" /><input type="hidden" value="3" name="week-3" /><input type="hidden" value="{{$rules.3.ruleid}}" name="ruleid-3" /></th>
                        <td>{{$LANG.checkin}}&nbsp;<select name="checkintime-hour-3" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.3.checkintime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>{{$LANG.cln}}<select name="checkintime-min-3" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.3.checkintime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                        <td>{{$LANG.checkout}}&nbsp;<select name="checkouttime-hour-3" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.3.checkouttime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>{{$LANG.cln}}<select name="checkouttime-min-3" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.3.checkouttime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                    </tr>
                    <tr>
                        <th align="right"><label><input name="status-4" type="checkbox" value="1"{{if $rules.4.status}} checked="checked"{{/if}} />{{$LANG.week_4}}{{$LANG.cln}}</label><input name="member[]" type="hidden" value="4" /><input type="hidden" value="4" name="week-4" /><input type="hidden" value="{{$rules.4.ruleid}}" name="ruleid-4" /></th>
                        <td>{{$LANG.checkin}}&nbsp;<select name="checkintime-hour-4" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.4.checkintime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>{{$LANG.cln}}<select name="checkintime-min-4" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.4.checkintime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                        <td>{{$LANG.checkout}}&nbsp;<select name="checkouttime-hour-4" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.4.checkouttime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>{{$LANG.cln}}<select name="checkouttime-min-4" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.4.checkouttime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                    </tr>
                    <tr>
                        <th align="right"><label><input name="status-5" type="checkbox" value="1"{{if $rules.5.status}} checked="checked"{{/if}} />{{$LANG.week_5}}{{$LANG.cln}}</label><input name="member[]" type="hidden" value="5" /><input type="hidden" value="5" name="week-5" /><input type="hidden" value="{{$rules.5.ruleid}}" name="ruleid-5" /></th>
                        <td>{{$LANG.checkin}}&nbsp;<select name="checkintime-hour-5" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.5.checkintime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>{{$LANG.cln}}<select name="checkintime-min-5" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.5.checkintime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                        <td>{{$LANG.checkout}}&nbsp;<select name="checkouttime-hour-5" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.5.checkouttime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>：<select name="checkouttime-min-5" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.5.checkouttime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                    </tr>
                    <tr>
                        <th align="right"><label><input name="status-6" type="checkbox" value="1"{{if $rules.6.status}} checked="checked"{{/if}} />{{$LANG.week_6}}{{$LANG.cln}}</label><input name="member[]" type="hidden" value="6" /><input type="hidden" value="6" name="week-6" /><input type="hidden" value="{{$rules.6.ruleid}}" name="ruleid-6" /></th>
                        <td>{{$LANG.checkin}}&nbsp;<select name="checkintime-hour-6" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.6.checkintime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>{{$LANG.cln}}<select name="checkintime-min-6" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.6.checkintime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                        <td>{{$LANG.checkout}}&nbsp;<select name="checkouttime-hour-6" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.6.checkouttime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>{{$LANG.cln}}<select name="checkouttime-min-6" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.6.checkouttime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                    </tr>
                    <tr>
                        <th align="right"><label><input name="status-0" type="checkbox" value="1"{{if $rules.0.status}} checked="checked"{{/if}} />{{$LANG.week_0}}{{$LANG.cln}}</label><input name="member[]" type="hidden" value="0" /><input type="hidden" value="0" name="week-0" /><input type="hidden" value="{{$rules.0.ruleid}}" name="ruleid-0" /></th>
                        <td>{{$LANG.checkin}}&nbsp;<select name="checkintime-hour-0" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.0.checkintime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>{{$LANG.cln}}<select name="checkintime-min-0" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.0.checkintime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                        <td>{{$LANG.checkout}}&nbsp;<select name="checkouttime-hour-0" style="width:50px;">
                            {{section name=hour loop=24}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                            <option value="{{$timestamp|date_format:'%H'}}"{{if $rules.0.checkouttime.0 == date('G', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                            {{/section}}
                        </select>{{$LANG.cln}}<select name="checkouttime-min-0" style="width:50px;">
                            {{section name=minute loop=60}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                            <option value="{{$timestamp|date_format:'%M'}}"{{if $rules.0.checkouttime.1 == date('i', $timestamp)}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                            {{/section}}
                        </select></td>
                    </tr>
                    <tr>
                        <th colspan="3" style=" padding:0">&nbsp;</th>
                    </tr>
                    <tr>
                        <th align="right">{{$LANG.late_standard}}{{$LANG.cln}}</th>
                        <td colspan="2"><input name="latestandard" type="text" style="width:50px;" maxlength="3" value="{{$schedule.latestandard|default:'0'}}" /> - <input name="latecheckin" type="text" style="width:50px;" maxlength="3" value="{{$schedule.latecheckin}}" />&nbsp;{{$LANG.late_standard_tips}}</td>
                    </tr>
                    <tr>
                        <th align="right">{{$LANG.leave_standard}}{{$LANG.cln}}</th>
                        <td colspan="2">满足工作时长后，早退标准为&nbsp;<input name="leavecheckout" type="text" style="width:50px;" maxlength="3" value="{{$schedule.leavecheckout}}" />&nbsp;分钟内签退算早退，之前算旷工</td>
                    </tr>
                </table>
                {{else}}
                <p style="padding:5px;">{{$LANG.plan_name}}&nbsp;&nbsp;&nbsp;&nbsp;<input name="name" type="text" class="input_text" style="width:200px;" maxlength="32" value="{{$schedule.name|escape:'html'}}"{{if $schedule.issystem}} _disabled="disabled" disabled="disabled"{{/if}} /><a href="javascript:void(0)" class="color_grid" style="background-color:{{if $schedule.scheduleid}}{{$schedule.bgcolor}}{{else}}{{$randomcolor}}{{/if}}"></a></p>
                <p style="padding:5px;">{{$LANG.checkin}}&nbsp;&nbsp;&nbsp;&nbsp;<select name="checkin-hour" style="width:50px;">
                    <option value="">免签</option>
                    {{section name=hour loop=24}}
                    {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                    <option value="{{$timestamp|date_format:'%H'}}"{{if $schedule.checkintime.0 == date('G', $timestamp) || (!$schedule.scheduleid && date('G', $timestamp) == "09")}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                    {{/section}}
                </select>{{$LANG.cln}}<select name="checkin-min" style="width:50px;">
                    <option value="">-</option>
                    {{section name=minute loop=60}}
                    {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                    <option value="{{$timestamp|date_format:'%M'}}"{{if $schedule.checkintime.1 == date('i', $timestamp) || (!$schedule.scheduleid && date('i', $timestamp) == "00")}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                    {{/section}}
                </select>&nbsp;&nbsp;&nbsp;&nbsp;{{$LANG.late_standard}}&nbsp;<input name="late-standard" maxlength="3" type="text" style="width:50px;" value="{{if !$schedule.scheduleid}}1{{else}}{{$schedule.latestandard}}{{/if}}"{{if $schedule.scheduleid && !$schedule.checkintime}} disabled="disabled" _disabled="disabled"{{/if}} /> - <input name="late-checkin" type="text" style="width:50px;" maxlength="3" value="{{if !$schedule.scheduleid}}60{{else}}{{$schedule.latecheckin}}{{/if}}"{{if $schedule.scheduleid && !$schedule.checkintime}} disabled="disabled" _disabled="disabled"{{/if}} />&nbsp;{{$LANG.late_standard_tips}}</p>
                <p style="padding:5px;">{{$LANG.checkout}}&nbsp;&nbsp;&nbsp;&nbsp;<select name="checkout-hour" style="width:50px;">
                    <option value="">免签</option>
                    {{section name=hour loop=24}}
                    {{assign var=timestamp value=0|mktime:0:$smarty.section.hour.index*3600}}
                    <option value="{{$timestamp|date_format:'%H'}}"{{if $schedule.checkouttime.0 == date('G', $timestamp) || (!$schedule.scheduleid && date('G', $timestamp) == "18")}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H'}}</option>
                    {{/section}}
                </select>{{$LANG.cln}}<select name="checkout-min" style="width:50px;">
                    <option value="">-</option>
                    {{section name=minute loop=60}}
                    {{assign var=timestamp value=0|mktime:0:$smarty.section.minute.index*60}}
                    <option value="{{$timestamp|date_format:'%M'}}"{{if $schedule.checkouttime.1 == date('i', $timestamp) || (!$schedule.scheduleid && date('i', $timestamp) == "00")}} selected="selected"{{/if}}>{{$timestamp|date_format:'%M'}}</option>
                    {{/section}}
                </select>&nbsp;&nbsp;&nbsp;&nbsp;满足工作时长后，早退标准为&nbsp;<input name="leave-checkout" type="text" style="width:50px;" maxlength="3" value="{{if !$schedule.scheduleid}}60{{else}}{{$schedule.leavecheckout}}{{/if}}"{{if $schedule.scheduleid && !$schedule.checkouttime}} disabled="disabled" _disabled="disabled"{{/if}} />&nbsp;分钟内签退算早退，之前算旷工</p>
                </div>
                {{/if}}
            </div>
        </div>
    </div>
    <div class="toolbar">
        <div><button class="btn" name="save">{{$LANG.save}}</button><button class="btn" name="cancel" onclick="location='/app/attend/schedule/index'">{{$LANG.cancel}}</button></div>
    </div>
</form>

<div class="pop_wrap" id="color_panel" style="width:218px;position:absolute;display:none">
    <div class="color_list" style="width:200px;">
    {{foreach item=color from=$bgcolors}}<div class="color_block"><div style="background-color:{{$color}}"></div><input type="hidden" name="color" value="{{$color}}" /></div>{{/foreach}}
    </div>
</div>
<script type="text/javascript">
var Lang = {modify: '{{$LANG.modify}}', 'delete': '{{$LANG.delete}}'};
$(function(){
    TOP.Label.focusLabel('attend');
    TOP.Frame.title('{{$LANG.attend_class}}');
    TOP.Frame.hash('m=app/attend/schedule/modify{{if $schedule.scheduleid}}&scheduleid={{$schedule.scheduleid}}{{/if}}{{foreach from=$scheduleids item=id}}&id[]={{$id}}{{/foreach}}');

    Attend.Schedule.setLang({
        confirm_delete_schedule: '{{$LANG.confirm_delete_schedule}}', please_add_step: '{{$LANG.please_add_step}}', params_invalid_schedule_name: '{{$LANG.params_invalid_schedule_name}}',
        week_1: '{{$LANG.week_1}}', week_2: '{{$LANG.week_2}}', week_3: '{{$LANG.week_3}}', week_4: '{{$LANG.week_4}}', week_5: '{{$LANG.week_5}}', week_6: '{{$LANG.week_6}}', week_0: '{{$LANG.week_0}}',
        worktime_error: '{{$LANG.worktime_error}}', worktime_not_equal: '{{$LANG.worktime_not_equal}}', time_area_error: '{{$LANG.time_area_error}}',
        late_must_int: '{{$LANG.late_must_int}}', leave_must_int: '{{$LANG.leave_must_int}}'
    });
    Attend.Schedule.initModify();
});
</script>
<script src="{{$options.sites.static}}/js/attend/schedule.js?1002" type="text/javascript"></script>
</body>
</html>