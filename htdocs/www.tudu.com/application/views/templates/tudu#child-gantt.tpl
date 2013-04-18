{{strip}}
{{foreach item=children from=$tudus}}
<table _tuduid="{{$children.tuduid}}" _tuduid="{{$children.tuduid}}" cellspacing="0" cellpadding="0" class="grid_list_2 gantt_list{{if !$children.isdraft}}{{if !$children.isread && !in_array('^r', $children.labels)}} unread{{/if}}{{/if}}{{if $children.type == 'task' && $children.isexpired}} expired{{/if}}"  _st="{{$children.starttime|date_format:'%Y/%m/%d'}}" _et="{{$children.endtime|date_format:'%Y/%m/%d'}}" _previd="{{$children.prevtuduid}}">
{{*任务类型*}}
  <tr>
    <td class="g_ident">{{if $children.istudugroup}}<span class="tree-ec-icon tree-elbow-plus"></span>{{else}}&nbsp;{{/if}}</td>
    <td class="g_i"><div class="g_i_l"></div><div class="g_i_c Rr{{if $children.istudugroup}}g{{/if}}{{if !$children.isread}}u{{/if}}"></div><div class="g_i_r{{if $children.attachnum > 0}} el{{/if}}"></div></td>
    <td class="g_in">
      <table class="g_in_table" cellspacing="0" cellpadding="0">
        <tr>
          <td style="padding-left:0">
          <div>
          {{*草稿仅显示标题*}}
            {{*主题分类*}}
            {{if $children.classname}}
            <a href="/tudu/?search=inbox&cid={{$children.classid}}" class="class_link">[{{$children.classname}}]</a>
            {{/if}}
            {{*主题前面的状态显示*}}
            {{if $children.status > 1}}
            {{assign var="statusKey" value="tudu_status_"|cat:$children.status"}}
            <span class="gray status">[{{$LANG[$statusKey]}}]</span>
            {{elseif $children.isexpired}}
            <span class="gray status">[{{$LANG.tudu_timeover}}]</span>
            {{/if}}
            <a{{if !$children.isdraft}} href="/tudu/view?tid={{$children.tuduid}}"{{/if}}{{if $children.isdone}} class="gray"{{/if}} _title="{{$children.subject|escape:'html'}}" name="subject">{{$children.subject|escape:'html'|default:$LANG.null_subject}}</a>
            {{* 列表分页 *}}
            {{tudu_list_pagenav recordcount=$children.replynum+1 pagesize=20 url='/tudu/view' query='tid='|cat:$children.tuduid}}
            {{*主题后面的待接受跟确认提示*}}
            {{if !$children.isdraft}}
            {{if $children.status >= 2 && !$children.isdone && $children.sender == $user.email}}
            <span class="tips_label" style="margin-left:5px">
                <span class="tips_label_tl"><span class="tips_label_tr"><span class="tips_label_tc"></span></span></span>
                <span class="tips_label_body" style="text-align:center">{{$LANG.status_waitforconfirm}}</span>
                <span class="tips_label_bl"><span class="tips_label_br"><span class="tips_label_bc"></span></span></span>
            </span>
            {{/if}}
            {{if $children.accepter == $user.email && !$children.accepttime}}
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
            <div class="rate_box"><div class="rate_bar" style="width:{{$children.percent|default:0}}%;"><em>{{$children.percent|default:0}}%</em></div></div>
            <em>{{$children.replynum|default:0}}/{{$children.viewnum|default:0}}</em>
            </div>
          </td>
        </tr>
      </table>
    </td>
    <td width="10" class="gantt_ct"></td>
    <td width="650" class="gantt_ct" style="padding:0">
    <div class="gantt_bar_ct">
    <table cellpadding="0" cellspacing="0" border="0" class="gantt_bar_bg"><tr>
    {{foreach from=$headers item=item name="bg"}}
    <td width="{{$tdwidth}}" class="{{if $smarty.foreach.bg.index == 0 || $smarty.foreach.bg.index == count($headers) - 1}}gantt_bg_weekend{{/if}}{{if $smarty.foreach.bg.index == 0}} bg_td_first{{/if}}{{if $item - 86400 == strtotime('today') || $item == strtotime('today')}} gantt_bg_today{{/if}}">&nbsp;&nbsp;&nbsp;</td>
    {{/foreach}}
    </tr></table>
    {{if $children.type != 'notice' && $children.type != 'discuss'}}
    {{cal_gantt min=$startdate max=$enddate starttime=$children.starttime endtime=$children.endtime isexpired=$children.isexpired status=$children.status completetime=$children.completetime istudugroup=$children.istudugroup allday=true assign=draw}}
    <div class="gantt_bar{{if $children.type == 'task'}} {{if $children.istudugroup}}gantt_bar_group{{else}}{{if !$children.endtime}}gantt_bar_gray{{else}}gantt_bar_blue{{/if}}{{/if}}{{else}} gantt_bar_yellow{{/if}}" style="width:{{$draw.width}};left:{{$draw.left}}">{{if $draw.leftlimit}}<div class="gantt_bar_ld"></div>{{/if}}{{if $draw.rightlimit}}<div class="gantt_bar_rd"></div>{{/if}}<div class="gantt_bar_cn" style="{{if $draw.leftlimit}}margin-left:8px;_margin-left:0;{{/if}}{{if $draw.rightlimit}}margin-right:8px;_margin-right:0{{/if}}"></div></div>
    {{if $children.endtime && $children.isexpired && !$children.istudugroup}}
    <div class="gantt_bar gantt_bar_red" style="width:{{$draw.exwidth}};left:{{$draw.exleft}}"><div class="gantt_bar_rd"></div><div class="gantt_bar_cn" style="margin-right:8px;_margin-right:0"></div></div>
    {{/if}}
    {{if $children.endtime && $children.status == 2 && !$children.istudugroup}}
    <div class="gantt_bar gantt_bar_green" style="width:{{$draw.exwidth}};left:{{$draw.exleft}}">{{if $draw.exleftlimit}}<div class="gantt_bar_ld"></div>{{/if}}{{if $draw.exrightlimit}}<div class="gantt_bar_rd"></div>{{/if}}<div class="gantt_bar_cn" style="{{if $draw.exleftlimit}}margin-left:8px;_margin-left:0;{{/if}}{{if $draw.exrightlimit}}margin-right:8px;_margin-right:0{{/if}}"></div></div>
    {{/if}}
    {{/if}}
    </div>
    </td>
  </tr>
</table>
{{if $children.istudugroup}}
<div class="gantt_children_list"></div>
{{/if}}
{{/foreach}}
{{/strip}}