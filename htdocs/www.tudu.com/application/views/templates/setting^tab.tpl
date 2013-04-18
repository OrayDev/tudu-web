<div class="tab-panel-header setting_tab">
  <ul class="tabtitle">
    <li {{if !$tab || $tab == 'general'}} class="selected"{{/if}}><a href="/setting/">{{$LANG.general}}</a></li>
    <li {{if $tab == 'account'}} class="selected"{{/if}}><a href="/setting/account">{{$LANG.account}}</a></li>
    {{if $access.skin}}<li {{if $tab == 'skin'}} class="selected"{{/if}}><a href="/setting/skin">{{$LANG.change_skin}}</a></li>{{/if}}
    <li {{if $tab == 'label'}} class="selected"{{/if}}><a href="/label/">{{$LANG.tags_manage}}</a></li>
    <li {{if $tab == 'rule'}} class="selected"{{/if}}><a href="/rule/">{{$LANG.tudu_rule}}</a></li>
    <li {{if $tab == 'mailbox'}} class="selected"{{/if}}><a href="/email/">{{$LANG.mailbox_bind}}</a></li>
    <li {{if $tab == 'board'}} class="selected"{{/if}}><a href="/board/manage">{{$LANG.board_manage}}</a></li>
    <li {{if $tab == 'group'}} class="selected"{{/if}}><a href="/contact/group.list">{{$LANG.group_manage}}</a></li>
    <li {{if $tab == 'log'}} class="selected"{{/if}}><a href="/setting/log">{{$LANG.login_log}}</a></li>
  </ul>
</div>