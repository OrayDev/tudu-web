<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1019" type="text/javascript"></script>
</head>

<body>
    {{include file="attend^tab.tpl" tab="schedule"}}
    <div class="tab-panel-body">
    {{include file="schedule^toolbar.tpl" tab="plan"}}
        <div class="readmailinfo">
            <div class="module">
                <div class="module_title"><strong>{{$userinfo.truename}}&nbsp;{{$LANG.schedule_plan}}</strong>&nbsp;|&nbsp;<a href="{{if $back}}{{$back}}{{else}}/app/attend/schedule/user{{/if}}">{{$LANG.back}}</a></div>
                <div class="line_bold"></div>
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>{{$LANG.schedule_plan}}{{$LANG.cln}}</td>
                        <td style="padding:15px 0;">
                            {{assign var="year" value=$smarty.now|date_format:"%Y"}}
                            {{assign var="year" value=$year+5}}
                            <select name="year" id="year">
                                {{section name=year loop=10}}
                                <option value="{{math equation="$year-x" x=$smarty.section.year.index}}">{{math equation="$year-x" x=$smarty.section.year.index}}{{$LANG.year}}</option>
                                {{/section}}
                            </select>&nbsp;
                            <select name="month" id="month">
                                {{section name=month loop=12}}
                                <option value="{{math equation="x+1" x=$smarty.section.month.index}}">{{math equation="x+1" x=$smarty.section.month.index}}{{$LANG.month}}</option>
                                {{/section}}
                            </select>
                            <input type="hidden" id="unid" name="unid" value="{{$userinfo.uniqueid}}" />
                        </td>
                    </tr>
                </table>
                <div id="schedule-calendar"></div>
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
    TOP.Frame.title('{{$userinfo.truename}} - {{$LANG.schedule_plan}}');
    TOP.Frame.hash('m=app/attend/schedule/userplan&year={{$date.year}}&month={{$date.month}}&unid={{$userinfo.uniqueid}}');

    $('#year').val(parseInt('{{$date.year}}'));
    $('#month').val(parseInt('{{$date.month}}'));

    Attend.Schedule.initUserPlan();
    Attend.Schedule.loadCalendar('{{$date.year}}', '{{$date.month}}', '{{$userinfo.uniqueid}}');
});
</script>
<script src="{{$options.sites.static}}/js/attend/attend.js?1004" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/attend/schedule.js?1002" type="text/javascript"></script>
</html>