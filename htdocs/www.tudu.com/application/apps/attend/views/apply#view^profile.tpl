<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
  {{if $post.isforeign}}
    <td valign="top" width="50"><a href="javascript:void(0)" title="{{$post.poster}}{{if $post.email}}<{{$post.email}}>{{/if}}"><img class="todo_send_icon" _email="{{$post.email}}" _url="/contact/view?email={{$post.email}}&back={{$currUrl}}" src="/logo?ctid={{$post.uniqueid}}&tsid={{$user.tsid}}"></a></td>

    <td><p><a href="/contact/view?email={{$post.email}}&back={{$currUrl}}" title=""><strong class="poster" _email="{{$post.email}}" _url="/contact/view?email={{$post.email}}&back={{$currUrl}}">{{if $user.truename == $post.poster}}{{$LANG.me}}{{else}}{{$post.poster}}{{/if}}</strong></a> {{$post.createtime|date_time_format:$user.option.dateformat}} | {{if !$query.unid}}<a href="/tudu/view?tid={{$tudu.tuduid}}&unid={{$post.uniqueid}}&back={{$query.back}}">{{$LANG.user_post}}</a>{{else}}<a href="/tudu/view?tid={{$tudu.tuduid}}&back={{$query.back}}">{{$LANG.view_all_post}}</a>{{/if}}</p><p class="gray">{{$post.posterinfo}}</p></td>
  {{else}}
    <td valign="top" width="50"><div class="list_avatar"><span class="icon icon_{{if $post.imstatus.show=='chat'}}online{{elseif $post.imstatus.show=='dnd'}}busy{{elseif $post.imstatus.show=='away'}}leave{{elseif $post.imstatus.show=='disturbance'}}disturb{{else}}logout{{/if}} status_icon"><!-- online|busy|leave|logout --></span><a href="javascript:void(0)" title="{{$LANG.user_chat}}" onclick="Tudu.chat('{{$post.email}}')"><img _email="{{$post.email}}"{{if $post.uniqueid == '^system'}} _name="{{$post.poster}}"{{/if}} _url="/contact/view?email={{$post.email}}&back={{$currUrl}}" class="todo_send_icon" src="/logo?unid={{$post.uniqueid}}"></a></div></td>
    <td><p><a href="/contact/view?email={{$post.email}}&back={{$currUrl}}"><strong class="poster" _poster="{{$post.poster}}" _email="{{$post.email}}" _url="/contact/view?email={{$post.email}}&back={{$currUrl}}">{{if $user.truename == $post.poster}}{{$LANG.me}}{{else}}{{$post.poster}}{{/if}}</strong></a> {{$post.createtime|date_time_format:$user.option.dateformat}} | {{if !$query.unid}}<a href="/tudu/view?tid={{$tudu.tuduid}}&unid={{$post.uniqueid}}{{if !$newwin}}&back={{$currUrl}}{{/if}}{{if $newwin}}&newwin=1{{/if}}">{{$LANG.user_post}}</a>{{else}}<a href="/tudu/view?tid={{$tudu.tuduid}}{{if !$newwin}}&back={{$currUrl}}{{/if}}{{if $newwin}}&newwin=1{{/if}}">{{$LANG.view_all_post}}</a>{{/if}}</p><p class="gray">{{$post.posterinfo}}</p></td>
  {{/if}}
    <td align="right">
    {{if !$post.isfirst}}
    <p class="gray floor">{{if !$isinvert}}{{math equation="(x)+((y-1)*z)" x=$smarty.foreach.post.index y=$pageinfo.currpage z=$pageinfo.pagesize}}{{else}}{{math equation="z-(y*x-1)" x=$smarty.foreach.post.index y=$pageinfo.currpage z=$tudu.replynum}}{{/if}} {{$LANG.post_floor}}</p>{{if null !== $post.percent}}<p class="gray">{{$LANG.elapsed}}{{math equation="round(x/3600, 1)" x=$post.elapsedtime}}{{$LANG.hour}}{{$LANG.comma}}{{$LANG.title_progress}}{{$post.percent}}%</p>{{/if}}
    {{/if}}
    </td>
  </tr>
</table>