<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
  {{if $post.isforeign}}
    <td valign="top" width="50"><img class="todo_send_icon" src="/logo?ctid={{$post.uniqueid}}&tsid={{$tsid}}"></td>
    <td><p><span class="icon icon_foreign"><!-- online|busy|leave|logout --></span><a href="javascript:void(0);" title="{{$post.poster}}{{if $post.email}}<{{$post.email}}>{{/if}}"><strong class="poster">{{$post.poster}}</strong></a> {{$post.createtime|date_time_format:'%Y-%m-%d %H:%M:%S'}} | {{if !$query.unid}}<a href="/foreign/tudu/view?tid={{$tudu.tuduid}}&fid={{$user.uniqueid}}&ts={{$tsid}}&unid={{$post.uniqueid}}&back={{$query.back}}">{{$LANG.user_post}}</a>{{else}}<a href="/tudu/view?tid={{$tudu.tuduid}}&back={{$query.back}}">{{$LANG.view_all_post}}</a>{{/if}}</p><p class="gray">{{$post.posterinfo}}</p></td>
  {{else}}
    <td valign="top" width="50"><img class="todo_send_icon" src="/logo?unid={{$post.uniqueid}}"></td>
    <td><p><span class="icon icon_logout"><!-- online|busy|leave|logout --></span><a href="javascript:void(0);" title="{{$post.poster}}{{if $post.email}}<{{$post.email}}>{{/if}}"><strong class="poster">{{$post.poster}}</strong></a> {{$post.createtime|date_time_format:'%Y-%m-%d %H:%M:%S'}} | {{if !$query.unid}}<a href="/foreign/tudu/view?tid={{$tudu.tuduid}}&fid={{$user.uniqueid}}&ts={{$tsid}}&unid={{$post.uniqueid}}&back={{$query.back}}">{{$LANG.user_post}}</a>{{else}}<a href="/tudu/view?tid={{$tudu.tuduid}}&back={{$query.back}}">{{$LANG.view_all_post}}</a>{{/if}}</p><p class="gray">{{$post.posterinfo}}</p></td>
  {{/if}}
    <td align="right">
    {{if !$post.isfirst}}
    <p class="gray floor">{{if !$isinvert}}{{math equation="(x)+((y-1)*z)" x=$smarty.foreach.post.index y=$pageinfo.currpage z=$pageinfo.pagesize}}{{else}}{{math equation="z-(y*x-1)" x=$smarty.foreach.post.index y=$pageinfo.currpage z=$tudu.replynum}}{{/if}} {{$LANG.post_floor}}</p>{{if null !== $post.percent}}<p class="gray">{{$LANG.elapsed}}{{math equation="round(x/3600, 1)" x=$post.elapsedtime}}{{$LANG.hour}}{{$LANG.comma}}{{$LANG.title_progress}}{{$post.percent}}%</p>{{/if}}
    {{/if}}
    </td>
  </tr>
</table>