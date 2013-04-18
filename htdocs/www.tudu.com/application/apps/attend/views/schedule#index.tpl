<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>{{$LANG.attend_class}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1019" type="text/javascript"></script>
</head>

<body>
    {{include file="attend^tab.tpl" tab="schedule"}}
    <div class="tab-panel-body">
        {{include file="schedule^toolbar.tpl" tab="schedule"}}
        <div class="readmailinfo">
            <div class="module">
                <p><input name="add" type="button" class="btn" value="{{$LANG.create_plan}}" onclick="location='/app/attend/schedule/modify'" /></p>
                <div class="table_list_wrap" style="margin-top:20px;">
                    <div class="table_list_title"><strong>{{$LANG.attend_schedule}}</strong></div>
                    <table width="100%" cellspacing="0" cellpadding="5" align="center" class="table_list">
                        <colgroup>
                            <col width="100" />
                            <col />
                            <col />
                            <col />
                            <col />
                            <col />
                            <col />
                            <col />
                            <col width="80" />
                        </colgroup>
                        <tr>
                            <th align="left">{{$LANG.plan_name}}</th>
                            <th align="right">{{$LANG.checkin}}</th>
                            <th align="right">{{$LANG.checkout}}</th>
                            <th align="right">{{$LANG.late_standard}}</th>
                            <th align="right">{{$LANG.late_checkin}}</th>
                            <th align="right">{{$LANG.leave_standard}}</th>
                            <th align="right">{{$LANG.leave_checkout}}</th>
                            <th align="right">{{$LANG.work_time}}</th>
                            <th align="left">{{$LANG.operate}}</th>
                        </tr>
                        <tbody id="schedule-list">
                            <tr id="plan-_exemption">
                                <td align="left">免签班</td>
                                <td align="right">免签</td>
                                <td align="right">免签</td>
                                <td align="right">-</td>
                                <td align="right">-</td>
                                <td align="right">-</td>
                                <td align="right">-</td>
                                <td align="right">-</td>
                                <td align="left">{{if $role.admin}}<a href="/app/attend/schedule/exemption">[{{$LANG.modify}}]</a>{{else}}<span class="gray">[{{$LANG.modify}}]</span>{{/if}}</td>
                            </tr>
                            <tr id="plan-_default">
                                <td align="left">默认班</td>
                                <td align="right">09:00</td>
                                <td align="right">18:00</td>
                                <td align="right">1-60{{$LANG.minute}}</td>
                                <td align="right">60{{$LANG.minute_after}}</td>
                                <td align="right">1-60{{$LANG.minute}}</td>
                                <td align="right">60{{$LANG.minute_before}}</td>
                                <td align="right">8.0{{$LANG.hour}}</td>
                                <td align="left">{{if $role.admin}}<a href="/app/attend/schedule/modify?scheduleid=^default">[{{$LANG.modify}}]</a>{{else}}<span class="gray">[{{$LANG.modify}}]</span>{{/if}}</td>
                            </tr>
                            {{foreach item=item from=$schedules}}
                            <tr id="plan-{{$item.scheduleid}}">
                                <td align="left" title="{{$item.name}}">{{$item.name|truncate:15}}</td>
                                <td align="right">{{$item.checkintime|default:'免签'}}</td>
                                <td align="right">{{$item.checkouttime|default:'免签'}}</td>
                                <td align="right">{{if $item.checkintime}}{{$item.latestandard|default:'0'}}-{{$item.latecheckin|default:'-'}}{{$LANG.minute}}{{else}}-{{/if}}</td>
                                <td align="right">{{if $item.latecheckin}}{{$item.latecheckin|cat:$LANG.minute_after}}{{else}}-{{/if}}</td>
                                <td align="right">{{if $item.checkouttime}}{{$item.leavestandard|default:'0'}}-{{$item.leavecheckout|default:'-'}}{{$LANG.minute}}{{else}}-{{/if}}</td>
                                <td align="right">{{if $item.leavecheckout}}{{$item.leavecheckout|cat:$LANG.minute_before}}{{else}}-{{/if}}</td>
                                <td align="right">{{if $item.worktime}}{{$item.worktime|cat:$LANG.hour}}{{else}}-{{/if}}</td>
                                <td align="left"><a href="/app/attend/schedule/modify?scheduleid={{$item.scheduleid}}">[{{$LANG.modify}}]</a>&nbsp;<a href="javascript:void(0)" onclick="Attend.Schedule.deletePlan('{{$item.scheduleid}}')">[{{$LANG.delete}}]</a></td>
                            </tr>
                            {{/foreach}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="toolbar">
            <div style="height:24px;"></div>
        </div>
  </div>
</body>
<script type="text/javascript">
var Lang = {modify: '{{$LANG.modify}}', 'delete': '{{$LANG.delete}}'};
$(function(){
    TOP.Label.focusLabel('attend');
    TOP.Frame.title('{{$LANG.attend_class}}');
    TOP.Frame.hash('m=app/attend/schedule/index');

    Attend.Schedule.setLang({confirm_delete_schedule: '{{$LANG.confirm_delete_schedule}}'});
    Attend.Schedule.init();
});
</script>
<script src="{{$options.sites.static}}/js/attend/schedule.js?1002" type="text/javascript"></script>
</html>