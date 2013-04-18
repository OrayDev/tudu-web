{{assign var="currUrl" value=$smarty.server.REQUEST_URI|urlencode}}
{{strip}}
<table cellspacing="0" class="grid_thead">
  <tr>
    <td width="30" align="center" style="padding-left:0"><input name="checkall" type="checkbox" /></td>
    {{if in_array('star', $columns)}}<td width="20">&nbsp;</td>{{/if}}
    <td class="title_line" align="center" width="40"><div style="padding-left:4px"><span class="mailtitle"></span></div></td>
{{foreach from=$columns item=column}}
    {{if $column == 'sender'}}
    <td width="100" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 3,{{$sort[1]}});return false;">{{$LANG.sender}}{{if $sort[0]==3}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'subject'}}
    <td class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 1,{{$sort[1]}});return false;">{{$LANG.subject}}{{if $sort[0]==1}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'accepter_endtime'}}
    <td width="110" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 4,{{$sort[1]}});return false;">{{$LANG.column_accepter}}{{if $sort[0]==4}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a>/<a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 2,{{$sort[1]}});return false;">{{$LANG.column_endtime}}{{if $sort[0]==2}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'reply'}}
    <td width="90" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 6,{{$sort[1]}});return false;">{{$LANG.tudu_percent}}{{if $sort[0]==6}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'lastpost'}}
    <td width="100" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 0,{{$sort[1]}});return false;">{{$LANG.lastpost}}{{if $sort[0]==0}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'endtime'}}
    <td width="110" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 2,{{$sort[1]}});return false;">{{$LANG.column_endtime}}{{if $sort[0]==2}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{elseif $column == 'starttime'}}
    <td width="130" class="title_line" style="padding-left:0"><div class="space"><a href="javascript:void(0);" onClick="Tudu.List.sort('converge?{{$query}}', 5,{{$sort[1]}});return false;">{{$LANG.meeting_time}}{{if $sort[0]==5}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
    {{/if}}
{{/foreach}}
  </tr>
</table>
{{if $label.labelalias == 'inbox' || $label.labelalias == 'todo' || $params.search == 'review'}}
{{*循环分组*}}
{{foreach key=key item=group from=$tudus}}
{{if $group}}
<div id="tudu-group-{{$key}}" class="grid_list_wrap grid_list_group">
<div class="grid_list_title"><span class="icon icon_elbow_minus toggle_tudu"></span><h3>{{if $LANG.list_group[$key]}}{{$LANG.list_group[$key]}}{{else}}{{$key}}{{/if}}</h3> (<span class="grid_list_count">{{$group|@count}}</span>)</div>
<div class="grid_list_group_ct">
{{foreach item=tudu from=$group}}
<table id="tudu-{{$tudu.tuduid}}" cellspacing="0" class="grid_list_2{{if !$tudu.isread && !in_array('^r', $tudu.labels)}} unread{{/if}}{{if $tudu.type == 'task' && $tudu.isexpired}} expired{{/if}}" _attr="|{{if $tudu.istudugroup}}group{{else}}{{$tudu.type}}{{/if}}|{{if in_array($user.email, $tudu.accepter)}}to|{{/if}}{{if array_key_exists($user.email, $tudu.cc)}}cc|{{/if}}{{if $user.userid == $tudu.from.1}}from|{{/if}}" _labels="{{foreach item=lab from=$tudu.labels}}{{if strpos($lab, '^') === false}}{{$lab}}|{{/if}}{{/foreach}}" _status="{{$tudu.selftudustatus}}"{{if $tudu.selfaccepttime}} _accepted="1"{{/if}}{{if $tudu.far}} style="display:none;" _far="1"{{/if}}>
{{*任务类型*}}
{{if $tudu.type == 'task'}}
  <tr>
    <td class="g_cb"><input name="tid[]" type="checkbox" value="{{$tudu.tuduid}}"{{if $key == 'await' || $key == 'doing' || $key == 'waitconfirm'}} ignore="false"{{/if}} ></td>
    {{if in_array('star', $columns)}}
    <td width="20"><a href="javascript:void(0);" class="icon icon_attention{{if in_array('^t', $tudu.labels)}} attention{{/if}}" title="{{if in_array('^t', $tudu.labels)}}{{$LANG.cancel_starred}}{{else}}{{$LANG.mark_starred}}{{/if}}"></a></td>
    {{/if}}
    <td class="g_i">
      <div class="g_i_l{{if $tudu.priority > 0}} ipt{{/if}}"></div>
      <a href="/tudu/view?tid={{$tudu.tuduid}}&newwin=1&back={{$currUrl}}" target="_blank" class="g_i_c Rr{{if $tudu.istudugroup}}g{{/if}}{{if !$tudu.isread}}u{{/if}}{{if $tudu.special == 1}}_c{{/if}}" title="{{$LANG.new_win_tips}}"></a>
      <div class="g_i_r{{if $tudu.attachnum > 0}} el{{/if}}"></div>
    </td>
    <td class="g_in">
      <table class="g_in_table" cellspacing="0" cellpadding="0">
        <tr>
          <td class="sender"><div class="space"><a _email="{{$tudu.sender}}" href="/contact/view?email={{$tudu.sender}}&back={{$currUrl}}">{{if $tudu.sender==$user.email}}{{$LANG.me}}{{else}}{{$tudu.from.0|default:'-'}}{{/if}}</a></div></td>
          <td class="subject">
          <div class="space">
          {{*草稿仅显示标题*}}
          {{if $tudu.isdraft}}
            <a href="/tudu/view?tid={{$tudu.tuduid}}&back={{$currUrl}}" title="{{$tudu.subject|escape:'html'}}">{{$tudu.subject|escape:'html'|default:$LANG.null_subject}}</a>
          {{else}}
            {{*主题分类*}}
            {{if $tudu.classname}}
            <a href="/tudu/?search={{$label.labelalias}}&cid={{$tudu.classid}}" class="class_link">[{{$tudu.classname}}]</a>
            {{/if}}
            {{*主题前面的状态显示*}}
            {{if $tudu.status > 1}}
            {{if $tudu.status == 2 && $tudu.type == 'task' && $tudu.expirefinished > 0}}
            <span class="red status">[{{$LANG.tudu_expire_finish}}]</span>
            {{else}}
            {{assign var="statusKey" value="tudu_status_"|cat:$tudu.status"}}
            <span class="gray status">[{{$LANG[$statusKey]}}]</span>
            {{/if}}
            {{elseif $tudu.isexpired}}
            <span class="gray status">[{{$LANG.tudu_timeover}}]</span>
            {{/if}}
            <a href="/tudu/view?tid={{$tudu.tuduid}}&back={{$currUrl}}"{{if $tudu.isdone}} class="gray"{{/if}} title="{{$tudu.subject|escape:'html'}}">{{if $tudu.displaydate}}[{{$tudu.starttime|date_time_format:'%Y%m%d'}}]{{/if}}{{$tudu.subject|escape:'html'|default:$LANG.null_subject}}</a>
            {{* 列表分页 *}}
            {{tudu_list_pagenav recordcount=$tudu.replynum+1 pagesize=20 url='/tudu/view' query='tid='|cat:$tudu.tuduid|cat:'&back='|cat:$currUrl}}
            {{* 便签 *}}
            {{if $tudu.mark}}<span class="icon icon_tudu_note" title="{{$LANG.note}}"></span>{{/if}}
            {{*主题后面的待接受跟确认提示*}}
            {{if $label.labelalias != 'review'}}
            {{if $tudu.status >= 2 && !$tudu.isdone && $tudu.sender == $user.email}}
            <span class="tips_label" style="margin-left:5px">
                <span class="tips_label_tl"><span class="tips_label_tr"><span class="tips_label_tc"></span></span></span>
                <span class="tips_label_body" style="text-align:center">{{$LANG.status_waitforconfirm}}</span>
                <span class="tips_label_bl"><span class="tips_label_br"><span class="tips_label_bc"></span></span></span>
            </span>
            {{/if}}
            {{if in_array($user.email, $tudu.accepter, true) && ($tudu.selftudustatus < 2 && !$tudu.selfaccepttime)}}
            <span class="tips_label tips_receive" style="margin-left:5px">
                <span class="tips_label_tl"><span class="tips_label_tr"><span class="tips_label_tc"></span></span></span>
                <span class="tips_label_body" style="text-align:center">{{$LANG.need_accept}}</span>
                <span class="tips_label_bl"><span class="tips_label_br"><span class="tips_label_bc"></span></span></span>
            </span>
            {{/if}}
            {{else}}
            {{if in_array('^e', $tudu.labels) && $key != 'reviewed'}}
            <span class="tips_label tips_review" style="margin-left:5px">
                <span class="tips_label_tl"><span class="tips_label_tr"><span class="tips_label_tc"></span></span></span>
                <span class="tips_label_body" style="text-align:center">{{$LANG.need_review}}</span>
                <span class="tips_label_bl"><span class="tips_label_br"><span class="tips_label_bc"></span></span></span>
            </span>
            {{/if}}
            {{/if}}
          {{/if}}
          </div>
          {{*标签*}}
          <div class="label_div">

          </div>
          </td>
          {{if in_array('accepter_endtime', $columns)}}
          <td class="deadline">
            <div class="space">
            {{if count($tudu.accepter) > 1}}
            <cite>{{$LANG.multi_accepter}}</cite>
            {{else}}
            <cite>{{if $tudu.accepter}}<a href="/contact/view?email={{$tudu.accepter[0]}}&back={{$currUrl}}">{{if $tudu.accepter.0===$user.email}}{{$LANG.me}}{{else}}{{$tudu.to[$tudu.accepter.0].0|default:'-'}}{{/if}}</a>{{else}}-{{/if}}</cite>
            {{/if}}
            <em>{{$tudu.endtime|date_format:'%Y-%m-%d'|default:'-'}}</em>
            </div>
          </td>
          {{elseif in_array('endtime', $columns)}}
          <td class="deadline">
            <div class="space">
            <span>{{$tudu.endtime|date_format:'%Y-%m-%d'|default:'-'}}</span>
            </div>
          </td>
          {{elseif in_array('starttime', $columns)}}
          <td style="width:130px">
            <span>{{$tudu.starttime|date_format:'%Y-%m-%d %H:%M'|default:'-'}}</span>
          </td>
          {{/if}}
          <td class="rate">
            <div class="space">
            {{if $tudu.appid == 'attend'}}
            <div class="rate_box rate_box2"><div class="rate_bar" style="width:100%;"></div></div>
            {{else}}
            <div class="rate_box"><div class="rate_bar" style="width:{{$tudu.percent|default:0}}%;"><em>{{$tudu.percent|default:0}}%</em></div></div>
            {{/if}}
            <em>{{$tudu.replynum}}/{{$tudu.viewnum}}</em>
            </div>
          </td>
          <td class="lastupdate" style="padding:0" title="{{$tudu.lastposttime|date_format:'%Y-%m-%d %H:%M'}}"><div class="space"><cite>{{$tudu.lastposter}}</cite><em>{{$tudu.lastposttime|date_format:'%m-%d %H:%M'}}</em></div></td>
        </tr>
      </table>
    </td>
  </tr>
{{*公告或讨论*}}
{{else}}
  <tr>
    <td class="g_cb"><input name="tid[]" type="checkbox" value="{{$tudu.tuduid}}"{{if $key == 'meeting'}} ignore="false"{{/if}} ></td>
    <td width="20"><a href="javascript:void(0);" class="icon icon_attention{{if in_array('^t', $tudu.labels)}} attention{{/if}}" title="{{if in_array('^t', $tudu.labels)}}{{$LANG.cancel_starred}}{{else}}{{$LANG.mark_starred}}{{/if}}"></a></td>
    <td class="g_i">
      <div class="g_i_l{{if $tudu.priority > 0}} ipt{{/if}}"></div>
      <a href="/tudu/view?tid={{$tudu.tuduid}}&newwin=1&back={{$currUrl}}" target="_blank" class="g_i_c R{{if $tudu.type == 'discuss'}}d{{elseif $tudu.type == 'notice'}}n{{elseif $tudu.type == 'meeting'}}m{{else}}r{{/if}}{{if !$tudu.isread}}u{{/if}}" title="{{$LANG.new_win_tips}}"></a>
      <div class="g_i_r{{if $tudu.attachnum > 0}} el{{/if}}"></div>
    </td>
    <td class="g_in">
      <table class="g_in_table" cellspacing="0" cellpadding="0">
        <tr>
          <td class="sender"><div class="space"><a _email="{{$tudu.sender}}" href="/contact/view?email={{$tudu.sender}}&back={{$currUrl}}">{{if $tudu.sender==$user.email}}{{$LANG.me}}{{else}}{{$tudu.from.0|default:'-'}}{{/if}}</a></div></td>
          <td class="subject">
              <div class="space">
                {{*主题分类*}}
                {{if $tudu.classname}}
                <a href="/tudu/?search={{$label.labelalias}}&cid={{$tudu.classid}}" class="class_link">[{{$tudu.classname}}]</a>
                {{/if}}
                {{*置顶*}}
                {{if $tudu.istop}}
                <span class="gray">[{{$LANG.ontop}}]</span>
                {{/if}}
                <a href="/tudu/view?tid={{$tudu.tuduid}}&back={{$currUrl}}" title="{{$tudu.subject|escape:'html'}}">{{if $tudu.displaydate}}[{{$tudu.starttime|date_time_format:'%Y%m%d'}}]{{/if}}{{$tudu.subject|escape:'html'|default:$LANG.null_subject}}</a>
                {{* 列表分页 *}}
                {{tudu_list_pagenav recordcount=$tudu.replynum+1 pagesize=20 url='/tudu/view' query='tid='|cat:$tudu.tuduid|cat:'&back='|cat:$currUrl}}
                {{if $tudu.special == 2}}<span class="icon icon_vote"></span>{{/if}}
                {{* 便签 *}}
                {{if $tudu.mark}}<span class="icon icon_tudu_note" title="{{$LANG.note}}"></span>{{/if}}
                {{if $tudu.type == 'meeting' && in_array($user.email, $tudu.accepter, true) && ($tudu.selftudustatus < 2 && !$tudu.selfaccepttime)}}
                <span class="tips_label tips_receive" style="margin-left:5px">
                    <span class="tips_label_tl"><span class="tips_label_tr"><span class="tips_label_tc"></span></span></span>
                    <span class="tips_label_body" style="text-align:center">{{$LANG.need_accept}}</span>
                    <span class="tips_label_bl"><span class="tips_label_br"><span class="tips_label_bc"></span></span></span>
                </span>
                {{/if}}
                {{if $tudu.type == 'notice' && $label.labelalias == 'review' && in_array('^e', $tudu.labels) && $key != 'reviewed'}}
                <span class="tips_label tips_review" style="margin-left:5px">
                    <span class="tips_label_tl"><span class="tips_label_tr"><span class="tips_label_tc"></span></span></span>
                    <span class="tips_label_body" style="text-align:center">{{$LANG.need_review}}</span>
                    <span class="tips_label_bl"><span class="tips_label_br"><span class="tips_label_bc"></span></span></span>
                </span>
                {{/if}}
              </div>
              {{*标签*}}
              <div class="label_div">

              </div>
          </td>
          <td class="deadline"><div class="space"><em>-</em></div></td>
          <td class="rate">
            <div class="space">
            <div class="rate_box rate_box2"><div class="rate_bar" style="width:100%;"></div></div>
            <em>{{$tudu.replynum}}/{{$tudu.viewnum}}</em>
            </div>
          </td>
          <td class="lastupdate" style="padding:0" title="{{$tudu.lastposttime|date_format:'%Y-%m-%d %H:%M'}}"><div class="space"><cite>{{$tudu.lastposter}}</cite><em>{{$tudu.lastposttime|date_format:'%m-%d %H:%M'}}</em></div></td>
        </tr>
      </table>
    </td>
  </tr>
{{/if}}
</table>
{{/foreach}}
</div>
{{if ($params.unread && $labels[$key].unreadnum > count($tudus[$key])) || (!$params.unread && $labels[$key].totalnum > count($tudus[$key]))}}
<div id="unupdate-{{$key}}" class="load_more_bar" _type="{{$key}}" style="height:30px;line-height:30px;text-align:center;background-color:#ffffea;border-bottom:1px solid #EEEEEE;">
<a name="show_outdate" href="javascript:void(0);">{{$LANG.load_more_tudu}}↓</a>
</div>
{{/if}}
</div>
{{/if}}
{{foreachelse}}
<table cellspacing="0" cellpadding="0" class="grid_list_2_null" width="100%">
  <tr>
    <td style="text-align:center;padding:35px 0">
      {{$LANG.tudulistnull}}
    </td>
  </tr>
</table>
{{/foreach}}
{{else}}{{*else inbox*}}
<div id="tudu-list" class="grid_list_wrap">
{{*循环列表显示*}}
{{foreach item=tudu from=$tudus}}
<table id="tudu-{{$tudu.tuduid}}" cellspacing="0" class="grid_list_2{{if !$tudu.isread && !in_array('^r', $tudu.labels)}} unread{{/if}}{{if $tudu.type == 'task' && $tudu.isexpired}} expired{{/if}}" _attr="|{{$tudu.type}}|{{if $user.userid == $tudu.to.1}}to|{{/if}}{{if array_key_exists($user.email, $tudu.cc)}}cc|{{/if}}{{if $user.userid == $tudu.from.1}}from|{{/if}}" _labels="{{foreach item=lab from=$tudu.labels}}{{if strpos($lab, '^') === false}}{{$lab}}|{{/if}}{{/foreach}}">
{{*任务类型*}}
{{if $tudu.type == 'task'}}
  <tr>
    <td class="g_cb"><input name="tid[]" type="checkbox" value="{{$tudu.tuduid}}"></td>
    {{if in_array('star', $columns)}}
    <td width="20"><a href="javascript:void(0);" class="icon icon_attention{{if in_array('^t', $tudu.labels)}} attention{{/if}}" title="{{if in_array('^t', $tudu.labels)}}{{$LANG.cancel_starred}}{{else}}{{$LANG.mark_starred}}{{/if}}" title="{{if in_array('^t', $tudu.labels)}}{{$LANG.cancel_starred}}{{else}}{{$LANG.mark_starred}}{{/if}}"></a></td>
    {{/if}}
    <td class="g_i">
      <div class="g_i_l{{if $tudu.priority > 0}} ipt{{/if}}"></div>
      {{if $label.labelalias == 'drafts'}}
      <div class="g_i_c Rr{{if !$tudu.isdraft || $tudu.istudugroup}}g{{/if}}u{{if $tudu.special == 1}}_c{{/if}}"></div>
      {{else}}
      <div class="g_i_c Rr{{if $tudu.istudugroup}}g{{/if}}{{if !$tudu.isread}}u{{/if}}{{if $tudu.special == 1}}_c{{/if}}"></div>
      {{/if}}
      <div class="g_i_r{{if $tudu.attachnum > 0}} el{{/if}}"></div>
    </td>
    <td class="g_in">
      <table class="g_in_table" cellspacing="0" cellpadding="0">
        <tr>
          {{if in_array('sender', $columns)}}
          <td class="sender"><div class="space"><a href="/contact/view?email={{$tudu.sender}}&back={{$currUrl}}">{{if $tudu.sender==$user.email}}{{$LANG.me}}{{else}}{{$tudu.from.0|default:'-'}}{{/if}}</a></div></td>
          {{/if}}
          <td class="subject">
          <div class="space">
          {{*草稿仅显示标题*}}
          {{if $tudu.isdraft}}
            <a href="/tudu/view?tid={{$tudu.tuduid}}&back={{$currUrl}}" title="{{$tudu.subject|escape:'html'}}">{{$tudu.subject|escape:'html'|default:$LANG.null_subject}}</a>
          {{elseif $label.labelalias == 'drafts'}}
            <a href="/tudu/view?tid={{$tudu.tuduid}}&&#100;ivide=1&back={{$currUrl}}" title="{{$tudu.subject|escape:'html'}}">{{$tudu.subject|escape:'html'|default:$LANG.null_subject}}</a>
          {{else}}
            {{*主题分类*}}
            {{if $tudu.classname}}
            <a href="/tudu/?search={{$label.labelalias}}&cid={{$tudu.classid}}" class="class_link">[{{$tudu.classname}}]</a>
            {{/if}}
            {{*主题前面的状态显示*}}
            {{if $tudu.status > 1}}
            {{assign var="statusKey" value="tudu_status_"|cat:$tudu.status"}}
            <span class="gray status">[{{$LANG[$statusKey]}}]</span>
            {{elseif $tudu.isexpired}}
            <span class="gray status">[{{$LANG.tudu_timeover}}]</span>
            {{/if}}
            <a href="/tudu/view?tid={{$tudu.tuduid}}&back={{$currUrl}}"{{if $tudu.isdone}} class="gray"{{/if}} title="{{$tudu.subject|escape:'html'}}">{{$tudu.subject|escape:'html'|default:$LANG.null_subject}}</a>
            {{* 列表分页 *}}
            {{tudu_list_pagenav recordcount=$tudu.replynum+1 pagesize=20 url='/tudu/view' query='tid='|cat:$tudu.tuduid|cat:'&back='|cat:$currUrl}}
            {{* 便签 *}}
            {{if $tudu.mark}}<span class="icon icon_tudu_note" title="{{$LANG.note}}"></span>{{/if}}
            {{*主题后面的待接受跟确认提示*}}
            {{if $tudu.status >= 2 && !$tudu.isdone && $tudu.sender == $user.email}}
            <span class="tips_label" style="margin-left:5px">
                <span class="tips_label_tl"><span class="tips_label_tr"><span class="tips_label_tc"></span></span></span>
                <span class="tips_label_body" style="text-align:center">{{$LANG.status_waitforconfirm}}</span>
                <span class="tips_label_bl"><span class="tips_label_br"><span class="tips_label_bc"></span></span></span>
            </span>
            {{/if}}
            {{if $tudu.accepter == $user.email && !$tudu.accepttime}}
            <span class="tips_label tips_receive" style="margin-left:5px">
                <span class="tips_label_tl"><span class="tips_label_tr"><span class="tips_label_tc"></span></span></span>
                <span class="tips_label_body" style="text-align:center">{{$LANG.need_accept}}</span>
                <span class="tips_label_bl"><span class="tips_label_br"><span class="tips_label_bc"></span></span></span>
            </span>
            {{/if}}
          {{/if}}
          </div>
          {{*标签*}}
          <div class="label_div">

          </div>
          </td>
          {{if in_array('accepter_endtime', $columns)}}
          <td class="deadline">
            <div class="space">
            {{if count($tudu.accepter) > 1}}
            <cite>{{$LANG.multi_accepter}}</cite>
            {{else}}
            <cite>{{if $tudu.accepter}}<a href="/contact/view?email={{$tudu.accepter[0]}}&back={{$currUrl}}">{{if $tudu.accepter==$user.email}}{{$LANG.me}}{{else}}{{$tudu.to[$tudu.accepter.0].0|default:'-'}}{{/if}}</a>{{else}}-{{/if}}</cite>
            {{/if}}
            <em>{{$tudu.endtime|date_format:'%Y-%m-%d'|default:'-'}}</em>
            </div>
          </td>
          {{/if}}
          {{if in_array('starttime', $columns)}}
          <td style="width:130px">
            <div class="space">
            <span>{{$tudu.starttime|date_format:'%Y-%m-%d %H:%M'|default:'-'}}</span>
            </div>
          </td>
          {{/if}}
          {{if in_array('reply', $columns)}}
          <td class="rate">
            <div class="space">
            <div class="rate_box"><div class="rate_bar" style="width:{{$tudu.percent|default:0}}%;"><em>{{$tudu.percent|default:0}}%</em></div></div>
            <em>{{$tudu.replynum}}/{{$tudu.viewnum}}</em>
            </div>
          </td>
          {{/if}}
          {{if in_array('lastpost', $columns)}}
          <td class="lastupdate" style="padding:0" title="{{$tudu.lastposttime|date_format:'%Y-%m-%d %H:%M'}}"><div class="space"><cite>{{$tudu.lastposter}}</cite><em>{{$tudu.lastposttime|date_format:'%m-%d %H:%M'}}</em></div></td>
          {{/if}}
        </tr>
      </table>
    </td>
  </tr>
{{*公告或讨论*}}
{{else}}
  <tr>
    <td class="g_cb"><input name="tid[]" type="checkbox" value="{{$tudu.tuduid}}"></td>
    {{if in_array('star', $columns)}}
    <td width="20"><a href="javascript:void(0);" class="icon icon_attention{{if in_array('^t', $tudu.labels)}} attention{{/if}}" title="{{if in_array('^t', $tudu.labels)}}{{$LANG.cancel_starred}}{{else}}{{$LANG.mark_starred}}{{/if}}"></a></td>
    {{/if}}
    <td class="g_i">
      <div class="g_i_l{{if $tudu.priority > 0}} ipt{{/if}}"></div>
      <div class="g_i_c R{{if $tudu.type == 'discuss'}}d{{elseif $tudu.type == 'notice'}}n{{elseif $tudu.type == 'meeting'}}m{{else}}r{{/if}}{{if !$tudu.isread}}u{{/if}}"></div>
      <div class="g_i_r{{if $tudu.attachnum > 0}} el{{/if}}"></div>
    </td>
    <td class="g_in">
      <table class="g_in_table" cellspacing="0" cellpadding="0">
        <tr>
          {{if in_array('sender', $columns)}}
          <td class="sender"><div class="space"><a href="/contact/view?email={{$tudu.sender}}&back={{$currUrl}}">{{if $tudu.sender==$user.email}}{{$LANG.me}}{{else}}{{$tudu.from.0|default:'-'}}{{/if}}</a></div></td>
          {{/if}}
          <td class="subject">
              <div class="space">
                {{*主题分类*}}
                {{if $tudu.classname}}
                <a href="/tudu/?search={{$label.labelalias}}&cid={{$tudu.classid}}" class="class_link">[{{$tudu.classname}}]</a>
                {{/if}}
                {{*置顶*}}
                {{if $tudu.istop}}
                <span class="gray">[{{$LANG.ontop}}]</span>
                {{/if}}
                <a href="/tudu/view?tid={{$tudu.tuduid}}&back={{$currUrl}}" title="{{$tudu.subject|escape:'html'}}">{{$tudu.subject|escape:'html'|default:$LANG.null_subject}}</a>
                {{* 列表分页 *}}
                {{tudu_list_pagenav recordcount=$tudu.replynum+1 pagesize=20 url='/tudu/view' query='tid='|cat:$tudu.tuduid|cat:'&back='|cat:$currUrl}}
                {{if $tudu.special == 2}}<span class="icon icon_vote"></span>{{/if}}
                {{* 便签 *}}
                {{if $tudu.mark}}<span class="icon icon_tudu_note" title="{{$LANG.note}}"></span>{{/if}}
              </div>
              {{*标签*}}
              <div class="label_div">

              </div>
          </td>
          {{if in_array('accepter_endtime', $columns)}}
          <td class="deadline"><div class="space"><em>-</em></div></td>
          {{/if}}
          {{if in_array('reply', $columns)}}
          <td class="rate">
            <div class="space">
            <div class="rate_box rate_box2"><div class="rate_bar" style="width:100%;"></div></div>
            <em>{{$tudu.replynum}}/{{$tudu.viewnum}}</em>
            </div>
          </td>
          {{/if}}
          {{if in_array('starttime', $columns)}}
          <td style="width:130px">
            <div class="space"><span>{{$tudu.starttime|date_format:'%Y-%m-%d %H:%M'|default:'-'}}</span></div>
          </td>
          {{/if}}
          {{if in_array('lastpost', $columns)}}
          <td class="lastupdate" style="padding:0" title="{{$tudu.lastposttime|date_format:'%Y-%m-%d %H:%M'}}"><div class="space"><cite>{{$tudu.lastposter}}</cite><em>{{$tudu.lastposttime|date_format:'%m-%d %H:%M'}}</em></div></td>
          {{/if}}
        </tr>
      </table>
    </td>
  </tr>
{{/if}}
</table>
{{*无记录显示内容*}}
{{foreachelse}}
<table cellspacing="0" cellpadding="0" class="grid_list_2_null" width="100%">
  <tr>
    <td style="text-align:center;padding:35px 0">
      {{$LANG.tudulistnull}}
    </td>
  </tr>
</table>
{{/foreach}}
</div>
{{/if}}{{*endif inbox*}}
{{if $label.labelalias != 'drafts'}}
<div class="grid_footer"><p><span class="gray">{{$LANG.select}}</span><a href="javascript:void(0)" class="select-type" name="all">{{$LANG.all}}</a> - <a href="javascript:void(0)" class="select-type" name="none" >{{$LANG.none}}</a> - <a href="javascript:void(0)" class="select-type" name="read" >{{$LANG.read}}</a> - <a href="javascript:void(0)" class="select-type" name="unread" >{{$LANG.unread}}</a></p></div>
{{else}}
<div class="grid_footer"><p>&nbsp;</p></div>

{{/if}}
{{/strip}}