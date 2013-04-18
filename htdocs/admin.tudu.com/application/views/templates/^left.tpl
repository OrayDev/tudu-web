<div class="c-left">
    <div class="panel">
        <div class="panel-tl"><div class="panel-tr"><div class="panel-tc"></div></div></div>
        <div class="panel-mc">
            <div class="panel-body">
                <a href="/user/user/add" class="add-account"><strong>{{$LANG.add_user}}</strong></a>
           </div>
        </div>
        <div class="panel-bl"><div class="panel-br"><div class="panel-bc"></div></div></div>
    </div>
    <div class="panel" style="margin-top:7px;">
        <div class="panel-tl"><div class="panel-tr"><div class="panel-tc"></div></div></div>
        <div class="panel-mc">
            <div class="panel-body">
                <ul class="sidebar">
                    <li{{if $tab=="user"}} class="current"{{/if}}><a href="/user/user">{{$LANG.accounts}}</a></li>
                    <li{{if $tab=="dept"}} class="current"{{/if}}><a href="/user/department">{{$LANG.department}}</a></li>
                    <li{{if $tab=="role"}} class="current"{{/if}}><a href="/user/role">{{$LANG.system_role}}</a></li>
                    <li{{if $tab=="group"}} class="current"{{/if}}><a href="/user/group">{{$LANG.user_group}}</a></li>
                    <li{{if $tab=="board"}} class="current"{{/if}}><a href="/board/board">{{$LANG.zone_board}}</a></li>
                    <li{{if $tab=="settings"}} class="current"{{/if}}><a href="/settings/general">{{$LANG.system_general}}</a></li>
                    <li {{if $tab=="secure"}}class="current"{{/if}}><a href="/secure/login">{{$LANG.system_safe}}</a></li>
                    <li class="last{{if $tab=="appstore"}} current{{/if}}"><a href="/secure/login">图度应用</a></li>
                </ul>
           </div>
        </div>
        <div class="panel-bl"><div class="panel-br"><div class="panel-bc"></div></div></div>
    </div>
</div>