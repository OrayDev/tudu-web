<table border="0" cellspacing="0" cellpadding="0" class="calendar-table">
    <tr>
        <th>{{assign var=week0 value="week_"|cat:"0"}}{{$LANG[$week0]}}</th>
        <th>{{assign var=week1 value="week_"|cat:"1"}}{{$LANG[$week1]}}</th>
        <th>{{assign var=week2 value="week_"|cat:"2"}}{{$LANG[$week2]}}</th>
        <th>{{assign var=week3 value="week_"|cat:"3"}}{{$LANG[$week3]}}</th>
        <th>{{assign var=week4 value="week_"|cat:"4"}}{{$LANG[$week4]}}</th>
        <th>{{assign var=week5 value="week_"|cat:"5"}}{{$LANG[$week5]}}</th>
        <th>{{assign var=week6 value="week_"|cat:"6"}}{{$LANG[$week6]}}</th>
    </tr>
    {{if $isfirstline}}
    <tr>
        {{foreach item=item key=key from=$firstline}}
        <td title="{{if $item.scheduleid == '^off'}}{{$LANG.rest}}{{elseif $item.scheduleid == '^exemption'}}{{$item.name}}{{else}}{{if $item.scheduleid != '^off' && $item.scheduleid}}{{$LANG.schedule_plan}}{{$LANG.cln}}{{$item.name}}&nbsp;&nbsp;{{/if}}{{$LANG.onwork_time}}{{$LANG.cln}}{{$item.checkintime|default:'免签'}}-{{$item.checkouttime|default:'免签'}}{{/if}}" _datetime="{{$item.datetime}}" _uid="{{$uniqueid}}" class="{{if $today == $item.datetime}}today{{/if}}{{if $curtime < $item.datetime}} gray{{else}} pointer{{/if}}{{if $key==0}} weekend{{elseif $key==6}} weekend board_right{{/if}}"><div{{if $item.mark}} class="date_mark"{{/if}}><strong>{{$item.day}}</strong><em>{{if $item.scheduleid != '^off' && $item.scheduleid}}{{$item.name|escape:'html'}}{{else}}&nbsp;{{/if}}</em></div></td>
        {{/foreach}}
    </tr>
    {{/if}}
    {{foreach item=midline key=k from=$midlines}}
    <tr>
        {{foreach item=item key=key from=$midline}}
        <td title="{{if $item.scheduleid == '^off'}}{{$LANG.rest}}{{elseif $item.scheduleid == '^exemption'}}{{$item.name}}{{else}}{{if $item.scheduleid != '^off' && $item.scheduleid}}{{$LANG.schedule_plan}}{{$LANG.cln}}{{$item.name}}&nbsp;&nbsp;{{/if}}{{$LANG.onwork_time}}{{$LANG.cln}}{{$item.checkintime|default:'免签'}}-{{$item.checkouttime|default:'免签'}}{{/if}}" _datetime="{{$item.datetime}}" _uid="{{$uniqueid}}" class="{{if $today == $item.datetime}}today{{/if}}{{if $curtime < $item.datetime}} gray{{else}} pointer{{/if}}{{if $key==0}} weekend{{elseif $key==6}} weekend board_right{{/if}}{{if !$lastline && $k+1==count($midlines)}} board_bottom{{/if}}"><div{{if $item.mark}} class="date_mark"{{/if}}><strong>{{$item.day}}</strong><em>{{if $item.scheduleid != '^off' && $item.scheduleid}}{{$item.name|escape:'html'}}{{else}}&nbsp;{{/if}}</em></div></td>
        {{/foreach}}
    </tr>
    {{/foreach}}
    {{if $islastline}}
    <tr>
        {{foreach item=item key=key from=$lastline}}
        <td title="{{if $item.scheduleid == '^off'}}{{$LANG.rest}}{{elseif $item.scheduleid == '^exemption'}}{{$item.name}}{{else}}{{if $item.scheduleid != '^off' && $item.scheduleid}}{{$LANG.schedule_plan}}{{$LANG.cln}}{{$item.name}}&nbsp;&nbsp;{{/if}}{{$LANG.onwork_time}}{{$LANG.cln}}{{$item.checkintime|default:'免签'}}-{{$item.checkouttime|default:'免签'}}{{/if}}" _datetime="{{$item.datetime}}" _uid="{{$uniqueid}}" class="{{if $today == $item.datetime}}today{{/if}}{{if $curtime < $item.datetime}} gray{{else}} pointer{{/if}}{{if $key==0}} weekend{{elseif $key==6}} weekend board_right{{/if}} board_bottom"><div{{if $item.mark}} class="date_mark"{{/if}}><strong>{{$item.day}}</strong><em>{{if $item.scheduleid != '^off' && $item.scheduleid}}{{$item.name|escape:'html'}}{{else}}&nbsp;{{/if}}</em></div></td>
        {{/foreach}}
    </tr>
    {{/if}}
</table>
<script type="text/javascript">
$(function(){
    $('.calendar-table tr td:not(.gray)').bind('click', function(){
        var date = $(this).attr('_datetime'),
            uniqueId = $(this).attr('_uid');
        Attend.Count.showCheckinInfo(uniqueId, date);
    });
});
</script>