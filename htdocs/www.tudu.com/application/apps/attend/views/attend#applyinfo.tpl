{{strip}}
{{if !in_array($categoryid, $general)}}
<table border="0" cellspacing="0" cellpadding="6" class="attendance_table">
    {{assign var=period value=0}}
    {{foreach item=apply from=$applys}}
    {{if $categoryid == '^checkin'}}
    {{assign var=period value=$period+1}}
    <tr><th align="right">申请日期：</th><td colspan=2>{{$apply.checkintime}}({{if $apply.type == 0}}签到{{else}}签退{{/if}})</td></tr>
    {{else}}
    {{assign var=period value=$period+$apply.period}}
    <tr><th align="right">申请日期：</th><td>{{$apply.start}} -- {{$apply.end}}</td><th>计：{{$apply.period}}{{$LANG.hour}}</th></tr>
    {{/if}}
    <tr><th align="right">&nbsp;</th><td colspan=2>{{$apply.content|strip_tags|truncate:200:'...':false:false}}</td></tr>
    {{/foreach}}
</table>
<div style="margin-top:25px;color:#000;"><strong>总计：{{$period}}{{if $categoryid == '^checkin'}}{{$LANG.times}}{{else}}{{$LANG.hour}}{{/if}}</strong></div>
{{else}}
<table border="0" cellspacing="0" cellpadding="6" class="attendance_table">
{{foreach item=item key=key from=$checkins}}
    <tr><th align="right">签到日期：</th><td>{{$key|date_time_format:"%Y-%m-%d"}}</td></tr>
    <tr><th align="right">上班签到：</th><td>{{$item.checkintime|date_time_format:"%H:%M"|default:'-'}}{{if $item.checkintime}}<span class="gray">&nbsp;({{$item.checkinip}}&nbsp;{{$item.checkinaddress}})</span>{{/if}}</td></tr>
    <tr><th align="right">下班签退：</th><td>{{$item.checkouttime|date_time_format:"%H:%M"|default:'-'}}{{if $item.checkouttime}}<span class="gray">&nbsp;({{$item.checkoutip}}&nbsp;{{$item.checkoutaddress}})</span>{{/if}}</td></tr>
{{/foreach}}
</table>
{{/if}}
<script type="text/javascript">
$(function(){
    $('#win-title').text('{{$title}}');
});
</script>
{{/strip}}