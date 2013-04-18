{{strip}}
<table class="cal-table" cellspacing="0">
    <tr>
        <td>{{$LANG.cal_sun}}</td>
        <td>{{$LANG.cal_mon}}</td>
        <td>{{$LANG.cal_tues}}</td>
        <td>{{$LANG.cal_wed}}</td>
        <td>{{$LANG.cal_thurs}}</td>
        <td>{{$LANG.cal_fri}}</td>
        <td>{{$LANG.cal_sat}}</td>
        <td>{{$LANG.cal_sun}}</td>
        <td>{{$LANG.cal_mon}}</td>
        <td>{{$LANG.cal_tues}}</td>
        <td>{{$LANG.cal_wed}}</td>
        <td>{{$LANG.cal_thurs}}</td>
        <td>{{$LANG.cal_fri}}</td>
        <td>{{$LANG.cal_sat}}</td>
        <td>{{$LANG.cal_sun}}</td>
        <td>{{$LANG.cal_mon}}</td>
        <td>{{$LANG.cal_tues}}</td>
        <td>{{$LANG.cal_wed}}</td>
        <td>{{$LANG.cal_thurs}}</td>
        <td>{{$LANG.cal_fri}}</td>
        <td>{{$LANG.cal_sat}}</td>
    </tr>
    <tr>
        {{foreach from=$cal.first item=day}}
        {{assign var=date value=$year|cat:'-'|cat:$month|cat:'-'|cat:$day}}
        <td align="center">{{if $day == 0}}&nbsp;{{else}}<div{{if $current == $day}} class="on"{{/if}}><a href="?type=month&sd={{$startdate}}&ed={{$enddate}}&unid={{$params.unid}}&date={{$date}}">{{$day}}</a></div>{{/if}}</td>
        {{/foreach}}
    </tr>
    <tr>
        {{foreach from=$cal.last item=day}}
        {{assign var=date value=$year|cat:'-'|cat:$month|cat:'-'|cat:$day}}
        <td align="center">{{if $day == 0}}&nbsp;{{else}}<div{{if $current == $day}} class="on"{{/if}}><a href="?type=month&sd={{$startdate}}&ed={{$enddate}}&unid={{$params.unid}}&date={{$date}}">{{$day}}</a></div>{{/if}}</td>
        {{/foreach}}
    </tr>
</table>
{{/strip}}