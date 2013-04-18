{{strip}}
<div class="toolbar">
    <div class="toolbar_nav">
    <a href="/app/attend/schedule/index"{{if $tab == 'schedule' || !$tab}} class="toolbar_nav_on"{{/if}}>{{$LANG.attend_schedule}}</a>
    <a href="/app/attend/schedule/user"{{if $tab == 'plan'}} class="toolbar_nav_on"{{/if}}>排班计划</a>
    {{if $role.admin}}
    <a href="/app/attend/schedule/adjust"{{if $tab == 'adjust'}} class="toolbar_nav_on"{{/if}}>{{$LANG.adjust_workday}}</a>
    {{/if}}
    </div>
</div>
{{/strip}}