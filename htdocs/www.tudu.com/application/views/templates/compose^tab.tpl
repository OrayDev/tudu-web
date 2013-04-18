<div class="tab-panel-header">
    <table cellspacing="0" cellpadding="0" class="composetab">
      <tr>
      {{if $tab == "tudu" || $access.task}}
        <td><div class="composetab_sel{{if $tab == "tudu" || !$tab}} on{{/if}}"><div><a id="tudu" href="/tudu/modify?type=tudu{{if $newwin}}&newwin=1{{/if}}">{{$LANG.tudu}}</a></div></div></td>
      {{/if}}
      {{if $tab == "discuss" || $access.discuss}}
        <td><div class="composetab_sel{{if $tab == "discuss"}} on{{/if}}"><div><a id="discuss" href="/tudu/modify?type=discuss{{if $newwin}}&newwin=1{{/if}}">{{$LANG.discuss}}</a></div></div></td>
      {{/if}}
      {{if $tab == "notice" || $access.notice}}
        <td><div class="composetab_sel{{if $tab == "notice"}} on{{/if}}"><div><a id="notice" href="/tudu/modify?type=notice{{if $newwin}}&newwin=1{{/if}}">{{$LANG.notice}}</a></div></div></td>
      {{/if}}
      {{if $tab == "meeting" || $access.meeting}}
        <td><div class="composetab_sel{{if $tab == "meeting"}} on{{/if}}"><div><a id="meeting" href="/tudu/modify?type=meeting{{if $newwin}}&newwin=1{{/if}}">{{$LANG.meeting}}</a></div></div></td>
      {{/if}}
      {{if $tab == "board" || $access.board}}
        <td><div class="composetab_sel{{if $tab == "board"}} on{{/if}}"><div><a href="/board/modify{{if $newwin}}?newwin=1{{/if}}">{{$LANG.board}}</a></div></div></td>
      {{/if}}
      </tr>
    </table>
</div>