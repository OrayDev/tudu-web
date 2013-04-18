{{if $back}}
{{assign var="currUrl" value=$back}}
{{else}}
{{assign var="currUrl" value=$smarty.server.REQUEST_URI|urlencode}}
{{/if}}
{{strip}}
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
      <a href="/tudu/view?tid={{$tudu.tuduid}}&newwin=1&back={{$currUrl}}" target="_blank" class="g_i_c Rr{{if $tudu.istudugroup}}g{{/if}}{{if !$tudu.isread}}u{{/if}}{{if $tudu.special == 1}}_c{{/if}}" title="{{$LANG.new_win_tips}}"></a>
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
            {{if $tudu.status == 2 && $tudu.type == 'task' && $tudu.expirefinished > 0}}
            <span class="red status">[{{$LANG.tudu_expire_finish}}]</span>
            {{else}}
            {{assign var="statusKey" value="tudu_status_"|cat:$tudu.status"}}
            <span class="gray status">[{{$LANG[$statusKey]}}]</span>
            {{/if}}
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
          {{elseif in_array('endtime', $columns)}}
          <td class="deadline">
            <div class="space">
            <span>{{$tudu.endtime|date_format:'%Y-%m-%d'|default:'-'}}</span>
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
      <a href="/tudu/view?tid={{$tudu.tuduid}}&newwin=1&back={{$currUrl}}" target="_blank" class="g_i_c R{{if $tudu.type == 'discuss'}}d{{elseif $tudu.type == 'notice'}}n{{elseif $tudu.type == 'meeting'}}m{{else}}r{{/if}}{{if !$tudu.isread}}u{{/if}}"></a>
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
{{/foreach}}
{{/strip}}