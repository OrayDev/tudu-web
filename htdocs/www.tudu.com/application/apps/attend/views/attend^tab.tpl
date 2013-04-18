<div class="tab-panel-header">
    <table cellspacing="0" cellpadding="0" class="composetab">
      <tr>
        <td><div class="composetab_sel{{if $tab == "checkin" || !$tab}} on{{/if}}"><div><a id="checkin" href="/app/attend/attend/index">{{$LANG.attend_checkin}}</a></div></div></td>
        {{if $roles.admin || $roles.def}}<td><div class="composetab_sel{{if $tab == "review"}} on{{/if}}"><div><a id="review" href="/app/attend/category/index">{{$LANG.attend_flow}}</a></div></div></td>{{/if}}
        {{if $roles.admin || $roles.sc}}<td><div class="composetab_sel{{if $tab == "schedule"}} on{{/if}}"><div><a id="schedule" href="/app/attend/schedule/index">{{$LANG.attend_class}}</a></div></div></td>{{/if}}
        <td><div class="composetab_sel{{if $tab == "count"}} on{{/if}}"><div><a id="count" href="/app/attend/count/index">{{$LANG.attend_count}}</a></div></div></td>
      </tr>
    </table>
</div>