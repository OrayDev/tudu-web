<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>{{$LANG.attend_checkin}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1019" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/attend/lunar-1.0.0.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/attend/attend.js?1004" type="text/javascript"></script>
</head>

<body>
    {{include file="attend^tab.tpl" tab="checkin"}}
    <div class="tab-panel-body">
        <div class="toolbar">
            <div class="toolbar_nav"><a href="/app/attend/apply/index">我的申请</a><a href="/app/attend/apply/receive">{{$LANG.attend_review}}</a></div>
        </div>
        <div class="readmailinfo">
            <div class="module">
                <div class="module_title"><strong>{{$LANG.today_plan}}{{$LANG.cln}}{{if $plan.scheduleid == '^off' || !$plan}}{{$LANG.rest}}{{else}}{{$plan.name}}&nbsp;&nbsp;{{if $plan.scheduleid != '^exemption'}}{{$LANG.onwork_time}} {{$plan.checkintime|default:'免签'}}-{{$plan.checkouttime|default:'免签'}}{{/if}}{{/if}}</strong>&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" class="btn" value="{{$LANG.attend_apply}}" onclick="location='/app/attend/apply/modify'"></div>
                <div class="line_bold"></div>
                <table border="0" cellspacing="0" cellpadding="5" style="margin-left:-5px;">
                    <tr>
                        <td colspan="3"><p id="date"></p><p class="gray" id="lunar"></p></td>
                        <td rowspan="2" valign="bottom" style="padding-left:20px;padding-bottom: 23px">
                            <div><input name="checkin" type="button" class="btn_sign{{if !$checkin}} first{{/if}}" value="{{$LANG.checkin}}{{if $checkin}}({{$checkin.createtime|date_time_format:"%H:%M"}}){{/if}}"{{if $checkin}} disabled="disabled"{{/if}} />&nbsp;&nbsp;<span class="gray checkin">{{if $checkin}}({{$checkin.ip}} {{if !$checkin.address}}{{$LANG.unknow}}{{else}}{{$checkin.address}}{{/if}}){{/if}}</span></div>
                            <div style="margin-top:13px;"><input name="checkout" type="button" class="btn_sign{{if !$checkout}} first{{/if}}" value="{{$LANG.checkout}}{{if $checkout}}({{$checkout.createtime|date_time_format:"%H:%M"}}){{/if}}" />&nbsp;&nbsp;<span class="gray checkout">{{if $checkout}}({{$checkout.ip}} {{if !$checkout.address}}{{$LANG.unknow}}{{else}}{{$checkout.address}}{{/if}}){{/if}}</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td valign="bottom">
                            <div id="hours" class="time_box">
                                <span class="time_number number_0"><em>2</em></span><span class="time_number number_0"><em>3</em></span>
                            </div>
                        </td>
                        <td>
                            <div class="time_cln"></div>
                        </td>
                        <td valign="bottom">
                            <div id="minutes" class="time_box">
                                <span class="time_number number_0"><em>2</em></span><span class="time_number number_0"><em>3</em></span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="module">
                <div class="module_title"><strong>{{$LANG.base_info}}</strong></div>
                <div class="line_bold"></div>
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="right">{{$LANG.work_time}}{{$LANG.cln}}</td>
                        <td>{{if $worktime == '0'}}0{{$LANG.hour}}0{{$LANG.min}}{{else}}{{$worktime|replace:":":$LANG.hour|cat:$LANG.min}}{{/if}}</td>
                    </tr>
                    <tr>
                        <td align="right">{{$LANG.month_count}}{{$LANG.cln}}</td>
                        <td>{{foreach item=item key=key from=$count name=count}}{{if $key == 'late' || $key == 'leave' || $key == 'unwork'}}{{assign var=category value="category_"|cat:$key}}{{$LANG[$category]}}&nbsp;{{if $item > 0}}<a href="javascript:void(0)" onclick="Attend.Infowin.show('{{$key}}', '{{$unid}}', '{{$date.year}}', '{{$date.month}}')">{{$item}}</a>{{else}}{{$item}}{{/if}}&nbsp;{{$LANG.times}}{{else}}{{$item.name}}&nbsp;{{if $item.total > 0}}<a href="javascript:void(0)" onclick="Attend.Infowin.show('{{$key}}', '{{$unid}}', '{{$date.year}}', '{{$date.month}}')">{{$item.total}}</a>{{else}}{{$item.total}}{{/if}}&nbsp;{{if $key == '^checkin'}}{{$LANG.times}}{{else}}{{$LANG.hour}}{{/if}}{{/if}}{{if $smarty.foreach.count.index + 1 < count($count)}},&nbsp;{{/if}}{{/foreach}}</td>
                    </tr>
                    <tr>
                        <td><strong>{{$LANG.schedule_plan}}{{$LANG.cln}}</strong></td>
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
                            <input type="hidden" id="unid" name="unid" value="{{$unid}}" />
                        </td>
                    </tr>
                </table>
                <div id="schedule-calendar"></div>
            </div>
        </div>
  </div>
</body>
<script type="text/javascript">
$(function(){
    TOP.Label.focusLabel('attend');
    TOP.Frame.title('{{$LANG.attend_checkin}}');
    TOP.Frame.hash('m=app/attend/index');

    $('#year').val(parseInt('{{$date.year}}'));
    $('#month').val(parseInt('{{$date.month}}'));

    new Lunar({dateTo: $('#date'), lunarTo: $('#lunar'), hoursTo: $('#hours'), minutesTo: $('#minutes'), lang: '{{$user.option.language|default:"zh_CN"}}'});
    Attend.Checkin.setLang({checkin: '{{$LANG.checkin}}'});
    Attend.Checkin.init();
    Attend.Checkin.loadCalendar('{{$date.year}}', '{{$date.month}}', '{{$unid}}');
});
</script>
</html>