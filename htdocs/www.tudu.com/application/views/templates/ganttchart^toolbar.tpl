{{strip}}
<div class="toolbar">
    <div class="fr" style="line-height:22px">
    <span class="color_grid" style="background:#83b00d"></span>{{$LANG.time_ahead}}&nbsp;
    <span class="color_grid" style="background:#b6160a"></span>{{$LANG.time_expired}}&nbsp;
    <span class="color_grid" style="background:#0073d0"></span>{{$LANG.time_plan}}&nbsp;
    <span class="color_grid" style="background:#e1e1e1"></span>{{$LANG.time_none}}
    </div>
    <div>
    <a href="/tudu/?search={{$label.labelalias}}&chart=gantt&type={{$type}}&sd={{$params.prev}}" class="arrow_time arrow_prev"></a><span style="font-weight:bold">
    {{if $type == 'week'}}
    {{$startdate|date_format:'%Y'}}{{$LANG.year}}{{$startdate|date_format:'%m'|intval}}{{$LANG.month}}&nbsp;
    {{$LANG.sequence_no}}{{$weeknum}}
    {{$LANG.week}}
    {{else}}
    {{$startdate|date_format:'%Y'}}{{$LANG.year}}{{$startdate|date_format:'%m'|intval}}{{$LANG.month}}
    {{/if}}
    </span><a href="/tudu/?search={{$label.labelalias}}&chart=gantt&type={{$type}}&sd={{$params.next}}" class="arrow_time arrow_next"></a>
    <button class="btn" name="week" type="button" onclick="location='/tudu/?search={{$label.labelalias}}&chart=gantt&type=week'">{{$LANG.week}}</button>
    <button class="btn" name="month" type="button" onclick="location='/tudu/?search={{$label.labelalias}}&chart=gantt&type=month'">{{$LANG.month}}</button>
    </div>
</div>
{{/strip}}