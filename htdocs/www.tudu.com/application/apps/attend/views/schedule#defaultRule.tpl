<td align="left">默认班<span class="gray">({{assign var=w value="week_"|cat:$week}}{{$LANG[$w]}})</span></td>
<td align="right">{{$rule.checkintime|default:'-'}}</td>
<td align="right">{{$rule.checkouttime|default:'-'}}</td>
<td align="right">{{if $rule}}{{$rule.latestandard|default:'0'}}-{{$rule.latecheckin|default:'-'}}{{$LANG.minute}}{{else}}-{{/if}}</td>
<td align="right">{{if $rule.latecheckin}}{{$rule.latecheckin|cat:$LANG.minute_after}}{{else}}-{{/if}}</td>
<td align="right">{{if $rule}}{{$rule.leavestandard|default:'0'}}-{{$rule.leavecheckout|default:'-'}}{{$LANG.minute}}{{else}}-{{/if}}</td>
<td align="right">{{if $rule.leavecheckout}}{{$rule.leavecheckout|cat:$LANG.minute_before}}{{else}}-{{/if}}</td>
<td align="right">{{if $rule.worktime}}{{$rule.worktime|cat:$LANG.hour}}{{else}}-{{/if}}</td>
<td align="left">{{if $role.admin}}<a href="/app/attend/schedule/modify?scheduleid=^default">[{{$LANG.modify}}]</a>{{else}}<span class="gray">[{{$LANG.modify}}]</span>{{/if}}</td>