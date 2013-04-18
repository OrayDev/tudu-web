{{assign var=back value=$smarty.server.REQUEST_URI|urlencode}}
<table cellspacing="0" class="grid_thead">
  <tr>
    <td width="30" align="center" style="padding-left:0;"><input name="checkall" type="checkbox" value="{{$tudu.tuduid}}"></td>
    <td width="20">&nbsp;</td>
    <td class="title_line" align="center" width="40"><div style="padding-left:4px"><span class="mailtitle"></span></div></td>
    <td class="title_line"><div class="space">{{$LANG.subject}}</div></td>
    <td width="100" class="title_line"><div class="space">{{$LANG.sender}}</div></td>
    <td width="100" class="title_line"><div class="space">{{$LANG.receiver}}</div></td>
    <td width="110" class="title_line"><div class="space">{{$LANG.endtime}}</div></td>
    <td width="90" class="title_line"><div class="space">{{$LANG.tudu_percent}}</div></td>
    <td width="100" class="title_line" style="padding-left:0"><div class="space">{{$LANG.lastpost}}</div></td>
  </tr>
</table>
<div id="tudu-list" class="grid_list_wrap">
{{foreach item=tudu from=$tudus}}
{{* 隐私主题 /板块 *}}
{{if !$ismoderators && !$tudu.labels && ($tudu.privacy || $board.privacy)}}
{{if ($tudu.to && array_key_exists($user.username, $tudu.to)) || ($tudu.cc && array_key_exists($user.username, $tudu.cc)) || ($tudu.bcc && array_key_exists($user.username, $tudu.bcc))}}
<table cellspacing="0" cellpadding="0" class="grid_list_2" style="color:#999" privacy='1'>
  <tr>
    <td class="g_cb"><input type="checkbox" value="" disabled="disabled"></td>
    <td width="20"><span class="icon icon_attention"></span></td>
    <td class="g_i">
      <div class="g_i_l{{if $tudu.important}} ipt{{/if}}"></div>
      <a href="/tudu/view?tid={{$tudu.tuduid}}&newwin=1" target="_blank" class="g_i_c Rr{{if $tudu.istudugroup}}g{{/if}}{{if !$tudu.isread}}u{{/if}}{{if $tudu.special == 1}}_c{{/if}}" title="{{$LANG.new_win_tips}}"></a>
      <div class="g_i_r{{if $tudu.attachnum > 0}} el{{/if}}"></div>
    </td>
    <td class="g_in">
      <table class="g_in_table" cellspacing="0" cellpadding="0">
        <tr>
          <td class="subject">
            <div class="space">
            <u>{{$LANG.privacy_tudu_subject}}</u>
            </div>
          </td>
          <td class="sender"><div class="space">-</div></td>
          <td class="accepter"><div class="space">-</div></td>
          <td class="deadline"><div class="space">-</div></td>
          <td class="rate">
            <div class="space">
            <div class="rate_box rate_box2"><div class="rate_bar" style="width:100%;"><em></em></div></div>
            <span class="gray">-</span>
            </div>
          </td>
          <td class="lastupdate" style="padding:0"><div class="space"><p>-</p></div></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
{{/if}}
{{else}}
<table id="tudu-{{$tudu.tuduid}}" cellspacing="0" cellpadding="0" class="grid_list_2{{if !$tudu.isread}} unread{{/if}}{{if $tudu.type == 'task' && $tudu.isexpired}} expired{{/if}}">
  <tr>
    <td class="g_cb">
      <input name="type" type="hidden" value="{{$tudu.type}}" />
      <input name="tid[]" type="checkbox" value="{{$tudu.tuduid}}">
    </td>
    <td width="20"><a href="javascript:void(0);" class="icon icon_attention{{if in_array('^t', $tudu.labels)}} attention{{/if}}" title="{{if in_array('^t', $tudu.labels)}}{{$LANG.cancel_starred}}{{else}}{{$LANG.mark_starred}}{{/if}}"></a></td>
    <td class="g_i">
      <div class="g_i_l{{if $tudu.important}} ipt{{/if}}"></div>
      <a href="/tudu/view?tid={{$tudu.tuduid}}&newwin=1&back={{$back}}" target="_blank" class="g_i_c R{{if $tudu.type == 'discuss'}}d{{elseif $tudu.type == 'notice'}}n{{elseif $tudu.type == 'meeting'}}m{{else}}r{{/if}}{{if $tudu.istudugroup}}g{{/if}}u{{if $tudu.type == 'task' && $tudu.special == 1}}_c{{/if}}" title="{{$LANG.new_win_tips}}"></a>
      <div class="g_i_r{{if $tudu.attachnum > 0}} el{{/if}}"></div>
    </td>
    <td class="g_in">
      <table class="g_in_table" cellspacing="0" cellpadding="0">
        <tr>
          <td class="subject">
          <div class="space">
          {{*草稿仅显示标题*}}
          {{if $tudu.isdraft}}
            <a href="/tudu/view?tid={{$tudu.tuduid}}&back={{$back}}">{{$tudu.subject|escape:'html'|default:$LANG.null_subject}}</a>
            {{*主题分类*}}
            {{if $tudu.classname}}
            <a href="/board/?bid={{$board.boardid}}&cid={{$tudu.classid}}" class="class_link">[{{$tudu.classname}}]</a>
            {{/if}}
            {{elseif $tudu.type == 'task'}}
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
            {{if $tudu.classname}}
            <a href="/board/?bid={{$board.boardid}}&cid={{$tudu.classid}}" class="class_link">[{{$tudu.classname}}]</a>
            {{/if}}
            <a href="/tudu/view?tid={{$tudu.tuduid}}&back={{$back}}" title="{{$tudu.subject|escape:'html'}}"{{if $tudu.isdone}} class="gray"{{/if}}>{{if $tudu.displaydate}}[{{$tudu.starttime|date_time_format:'%Y%m%d'}}]{{/if}}{{$tudu.subject|escape:'html'|default:$LANG.null_subject}}</a>
            {{* 列表分页 *}}
            {{tudu_list_pagenav recordcount=$tudu.replynum+1 pagesize=20 url='/tudu/view' query='tid='|cat:$tudu.tuduid|cat:'&back='|cat:$back}}
            {{* 便签 *}}
            {{if $tudu.mark}}<span class="icon icon_tudu_note" title="{{$LANG.note}}"></span>{{/if}}
            {{*主题后面的待接受跟确认提示*}}
            {{if $tudu.status >= 2 && !$tudu.isdone && $tudu.sender == $user.username}}
            <span class="tips_label" style="margin-left:5px">
                <span class="tips_label_tl"><span class="tips_label_tr"><span class="tips_label_tc"></span></span></span>
                <span class="tips_label_body" style="text-align:center">{{$LANG.status_waitforconfirm}}</span>
                <span class="tips_label_bl"><span class="tips_label_br"><span class="tips_label_bc"></span></span></span>
            </span>
            {{/if}}
            {{if $tudu.status <= 0 && $tudu.accepter == $user.username && !$tudu.accepttime}}
            <span class="tips_label tips_receive" style="margin-left:5px">
                <span class="tips_label_tl"><span class="tips_label_tr"><span class="tips_label_tc"></span></span></span>
                <span class="tips_label_body" style="text-align:center">{{$LANG.need_accept}}</span>
                <span class="tips_label_bl"><span class="tips_label_br"><span class="tips_label_bc"></span></span></span>
            </span>
            {{/if}}
          {{else}}
            {{*置顶*}}
            {{if $tudu.istop}}
            <span class="gray">[{{$LANG.ontop}}]</span>
            {{/if}}
            {{if $tudu.classname}}
            <a href="/board/?bid={{$board.boardid}}&cid={{$tudu.classid}}" class="class_link">[{{$tudu.classname|escape:'html'}}]</a>
            {{/if}}
            <a href="/tudu/view?tid={{$tudu.tuduid}}&back={{$smarty.server.REQUEST_URI|escape:'url'}}" title="{{$tudu.subject|escape:'html'}}">{{if $tudu.displaydate && $tudu.type == 'meeting'}}[{{$tudu.starttime|date_time_format:'%Y%m%d'}}]{{/if}}{{$tudu.subject|escape:'html'|default:$LANG.null_subject}}</a>
            {{if $tudu.special == 2}}<span class="icon icon_vote"></span>{{/if}}
            {{* 便签 *}}
            {{if $tudu.mark}}<span class="icon icon_tudu_note" title="{{$LANG.note}}"></span>{{/if}}
          {{/if}}
            </div>
          </td>
          <td class="sender"><div class="space"><a _email="{{$tudu.from.3}}" href="/contact/view?email={{$tudu.from.3}}&back={{$back}}">{{if $tudu.from.3==$user.username}}{{$LANG.me}}{{else}}{{$tudu.from.0|default:'-'}}{{/if}}</a></div></td>
          <td class="accepter">
          <div class="space">
          {{if $tudu.acceptmode && !$tudu.accepttime}}
          <cite>{{$LANG.await_claim}}</cite>
          {{elseif !$tudu.acceptmode && count($tudu.accepter) > 1}}
          <cite>{{$LANG.multi_accepter}}</cite>
          {{else}}
          <cite>{{if $tudu.accepter}}<a _email="{{$tudu.accepter.0}}" href="/contact/view?email={{$tudu.accepter.0}}&back={{$back}}">{{if $tudu.accepter.0===$user.username}}{{$LANG.me}}{{else}}{{$tudu.to[$tudu.accepter.0].0|default:'-'}}{{/if}}</a>{{else}}-{{/if}}</cite>
          {{/if}}
          </div>
          </td>
          <td class="deadline"><div class="space">{{$tudu.endtime|date_format:'%Y-%m-%d'|default:$LANG.none}}</div></td>
          <td class="rate">
          <div class="space">
          {{if $tudu.type == 'task' && (!$tudu.flowid || 0 === strpos('^', $tudu.flowid))}}
            <div class="rate_box"><div class="rate_bar" style="width:{{$tudu.percent|default:0}}%;"><em>{{$tudu.percent|default:0}}%</em></div></div>
          {{else}}
            <div class="rate_box rate_box2"><div class="rate_bar" style="width:100%;"></div></div>
          {{/if}}
            <em>{{$tudu.replynum}}/{{$tudu.viewnum}}</em>
          </div>
          </td>
          <td class="lastupdate" style="padding:0" title="{{$tudu.lastposttime|date_format:'%Y-%m-%d %H:%M'}}"><div class="space"><cite>{{$tudu.lastposter}}</cite><em>{{$tudu.lastposttime|date_format:'%m-%d %H:%M'}}</em></div></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
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
</div>