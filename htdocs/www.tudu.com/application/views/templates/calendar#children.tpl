{{strip}}
{{*循环列表显示*}}
{{foreach item=tudu from=$tudus}}
{{if !$tudu.deny}}
<table _tuduid="{{$tudu.tuduid}}"  cellspacing="0" cellpadding="0" class="grid_list_2 gantt_list{{if !$tudu.isread && !in_array('^r', $tudu.labels)}} unread{{/if}}{{if $tudu.type == 'task' && $tudu.isexpired}} expired{{/if}}" _attr="|{{$tudu.type}}|{{if $user.userid == $tudu.to.1}}to|{{/if}}{{if array_key_exists($user.email, $tudu.cc)}}cc|{{/if}}{{if $user.userid == $tudu.from.1}}from|{{/if}}" _labels="{{foreach item=lab from=$tudu.labels}}{{if strpos($lab, '^') === false}}{{$lab}}|{{/if}}{{/foreach}}" _st="{{$tudu.starttime|date_format:'%Y/%m/%d'}}" _et="{{$tudu.endtime|date_format:'%Y/%m/%d'}}" _previd="{{$tudu.prevtuduid}}">
{{*任务类型*}}
  <tr>
    <td class="g_ident">{{if $tudu.istudugroup}}<span class="tree-ec-icon tree-elbow-plus"></span>{{else}}&nbsp;{{/if}}</td>
    <td class="g_in">
      <table class="g_in_table" cellspacing="0" cellpadding="0">
        <tr>
          <td style="padding-left:0">
          <div>
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
            <a href="/tudu/view?tid={{$tudu.tuduid}}&back={{$currUrl}}"{{if $tudu.isdone}} class="gray"{{/if}} _title="{{$tudu.subject|escape:'html'}}" name="subject">{{$tudu.subject|escape:'html'|default:$LANG.null_subject}}</a>
            {{* 列表分页 *}}
            {{tudu_list_pagenav recordcount=$tudu.replynum+1 pagesize=20 url='/tudu/view' query='tid='|cat:$tudu.tuduid|cat:'&back='|cat:$currUrl}}
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
          <td width="80">
            <div class="space">
            <div class="rate_box"><div class="rate_bar" style="width:{{$tudu.percent|default:0}}%;"><em>{{$tudu.percent|default:0}}%</em></div></div>
            <em>{{$tudu.replynum}}/{{$tudu.viewnum}}</em>
            </div>
          </td>
        </tr>
      </table>
    </td>
    <td width="10" class="gantt_ct"></td>
    <td width="650" class="gantt_ct" style="padding:0">
    <div class="gantt_bar_ct">
    <table cellpadding="0" cellspacing="0" border="0" class="gantt_bar_bg"><tr>
    {{if $type == 'week'}}
    {{foreach from=$headers item=item name="bg"}}
    <td width="{{$tdwidth}}" class="{{if $smarty.foreach.bg.index == 0 || $smarty.foreach.bg.index == count($headers) - 1}}gantt_bg_weekend{{/if}}{{if $smarty.foreach.bg.index == 0}} bg_td_first{{/if}}{{if $item - 86400 == strtotime('today') || $item == strtotime('today')}} gantt_bg_today{{/if}}">&nbsp;&nbsp;&nbsp;</td>
    {{/foreach}}
    {{else}}
    {{foreach from=$headers item=item name="bg"}}
    <td width="{{$tdwidth}}" class="{{if in_array(date('w', $item), array('0', '6'), true)}}gantt_bg_weekend{{/if}}{{if $smarty.foreach.bg.index == 0}} bg_td_first{{/if}}{{if ($smarty.foreach.bg.index + 1) % 5 != 0 && $smarty.foreach.bg.index != 0}} bg_none{{/if}}{{if $item - 86400 == strtotime('today') || $item == strtotime('today')}} gantt_bg_today{{/if}}">&nbsp;&nbsp;&nbsp;</td>
    {{/foreach}}
    {{/if}}
    </tr></table>
    {{if $tudu.type != 'notice' && $tudu.type != 'discuss'}}
    {{cal_gantt min=$startdate max=$enddate starttime=$tudu.starttime endtime=$tudu.endtime isexpired=$tudu.isexpired status=$tudu.status completetime=$tudu.completetime istudugroup=$tudu.istudugroup allday=true assign=draw}}
    <div class="gantt_bar{{if $tudu.type == 'task'}} {{if $tudu.istudugroup}}gantt_bar_group{{else}}{{if !$tudu.endtime}}gantt_bar_gray{{else}}gantt_bar_blue{{/if}}{{/if}}{{else}} gantt_bar_yellow{{/if}}" style="width:{{$draw.width}};left:{{$draw.left}}">{{if $draw.leftlimit}}<div class="gantt_bar_ld"></div>{{/if}}{{if $draw.rightlimit}}<div class="gantt_bar_rd"></div>{{/if}}<div class="gantt_bar_cn" style="{{if $draw.leftlimit}}margin-left:8px;_margin-left:0;{{/if}}{{if $draw.rightlimit}}margin-right:8px;_margin-right:0{{/if}}"></div></div>
    {{if $tudu.isexpired && !$tudu.istudugroup}}
    <div class="gantt_bar gantt_bar_red" style="width:{{$draw.exwidth}};left:{{$draw.exleft}}"><div class="gantt_bar_rd"></div><div class="gantt_bar_cn" style="margin-right:8px;_margin-right:0"></div></div>
    {{/if}}
    {{if $tudu.status == 2 && !$tudu.istudugroup}}
    <div class="gantt_bar gantt_bar_green" style="width:{{$draw.exwidth}};left:{{$draw.exleft}}">{{if $draw.exleftlimit}}<div class="gantt_bar_ld"></div>{{/if}}{{if $draw.exrightlimit}}<div class="gantt_bar_rd"></div>{{/if}}<div class="gantt_bar_cn" style="{{if $draw.exleftlimit}}margin-left:8px;_margin-left:0;{{/if}}{{if $draw.exrightlimit}}margin-right:8px;_margin-right:0{{/if}}"></div></div>
    {{/if}}
    {{/if}}
    </div>
    </td>
  </tr>
</table>
{{if $tudu.istudugroup}}
<div class="gantt_children_list"></div>
{{/if}}
{{/if}}
{{*无记录显示内容*}}
{{/foreach}}
{{/strip}}