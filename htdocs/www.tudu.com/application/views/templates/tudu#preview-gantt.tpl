<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$tudu.subject}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<style type="text/css" media="print">
.toolbar {
	display:none
}
</style>
</head>
<body style="padding:5px">

<div class="panel">
    <div class="panel-body">
      <div class="toolbar">
        <div class="fr" style="line-height:22px">
        <span class="color_grid" style="background:#83b00d"></span>{{$LANG.time_ahead}}&nbsp;
    <span class="color_grid" style="background:#b6160a"></span>{{$LANG.time_expired}}&nbsp;
    <span class="color_grid" style="background:#0073d0"></span>{{$LANG.time_plan}}&nbsp;
    <span class="color_grid" style="background:#e1e1e1"></span>{{$LANG.time_none}}
        </div>
        <div><button type="button" name="print">{{$LANG.print}}</button><button type="button" name="close">{{$LANG.close}}</button></div>
      </div>
        <table cellspacing="0" class="grid_thead">
          <tr>
            <td class="title_line" align="center" width="40"><div style="padding-left:4px"><span class="mailtitle"></span></div></td>
            <td style="line-height:20px;"><div class="space">{{$LANG.subject}}</div></td>
            <td width="90" class="title_line" style="line-height:20px;"><div class="space">{{$LANG.percent}}</div></td>
            <td width="650" style="line-height:20px;padding:0">
            <table cellspacing="0" cellpadding="0" border="0" class="gantt_header" width="100%" height="22">
            <tr>
            <td width="1%">
              <div class="space" style="float:left;">{{if !$start}}{{$starttime|date_time_format:$user.option.dateformat:'date'|default:'...'}}{{else}}...{{/if}}</div>
              <div style="float:right;">{{if !$end}}{{$endtime|date_time_format:$user.option.dateformat:'date'|default:'...'}}{{else}}...{{/if}}&nbsp;&nbsp;</div>
			</td>
            </tr>
            </table>
            </td>
          </tr>
        </table>
        <div id="tudu-list" class="grid_list_wrap grid_list_group">
          <div class="grid_list_group_ct">
            <table _tuduid="{{$tudu.tuduid}}" cellspacing="0" cellpadding="0" class="grid_list_2 gantt_list{{if !$tudu.isdraft}}{{/if}}{{if $tudu.type == 'task' && $tudu.isexpired}} expired{{/if}}" _st="{{if $tudu.type == 'meeting'}}{{$tudu.starttime|date_format:'%Y/%m/%d %H:%M'}}{{else}}{{$tudu.starttime|date_format:'%Y/%m/%d'}}{{/if}}" _et="{{if $tudu.type == 'meeting'}}{{$tudu.endtime|date_format:'%Y/%m/%d %H:%M'}}{{else}}{{$tudu.endtime|date_format:'%Y/%m/%d'}}{{/if}}" _previd="{{$tudu.prevtuduid}}">
            {{*任务类型*}}
              <tr>
                <td class="g_i"><div class="g_i_l"></div><div class="g_i_c Rr{{if $tudu.istudugroup}}g{{/if}}{{if !$tudu.isread}}u{{/if}}"></div><div class="g_i_r{{if $tudu.attachnum > 0}} el{{/if}}"></div></td>
                <td class="g_in">
                  <table class="g_in_table" cellspacing="0" cellpadding="0">
                    <tr>
                      <td style="padding-left:0">
                      <div>
                        {{*草稿仅显示标题*}}
                        {{*主题分类*}}
                        {{if $tudu.classname}}
                        <a href="/tudu/?search=inbox&cid={{$tudu.classid}}" class="class_link">[{{$tudu.classname}}]</a>
                        {{/if}}
                        {{*主题前面的状态显示*}}
                        {{if $tudu.status > 1}}
                        {{assign var="statusKey" value="tudu_status_"|cat:$tudu.status"}}
                        <span class="gray status">[{{$LANG[$statusKey]}}]</span>
                        {{elseif $tudu.isexpired}}
                        <span class="gray status">[{{$LANG.tudu_timeover}}]</span>
                        {{/if}}
                        <a{{if !$tudu.isdraft}} href="/tudu/view?tid={{$tudu.tuduid}}"{{/if}}{{if $tudu.isdone}} class="gray"{{/if}} _title="{{$tudu.subject|escape:'html'}}" name="subject">{{$tudu.subject|escape:'html'|default:$LANG.null_subject}}</a>
                        {{* 列表分页 *}}
                        {{tudu_list_pagenav recordcount=$tudu.replynum+1 pagesize=20 url='/tudu/view' query='tid='|cat:$tudu.tuduid}}
                        {{*主题后面的待接受跟确认提示*}}
                        {{if !$tudu.isdraft}}
                        {{if $tudu.status >= 2 && !$tudu.isdone && $tudu.sender == $user.email && $tudu.type == 'task'}}
                        <span class="tips_label" style="margin-left:5px">
                            <span class="tips_label_tl"><span class="tips_label_tr"><span class="tips_label_tc"></span></span></span>
                            <span class="tips_label_body" style="text-align:center">{{$LANG.status_waitforconfirm}}</span>
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
                        <em>{{$tudu.replynum|default:0}}/{{$tudu.viewnum|default:0}}</em>
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
                {{if $tudu.type != 'notice' && $tudu.type != 'discuss'}}
                {{cal_gantt min=$starttime max=$endtime starttime=$tudu.starttime endtime=$tudu.endtime isexpired=$tudu.isexpired status=$tudu.status completetime=$tudu.completetime istudugroup=$tudu.istudugroup allday=true assign=draw}}
                <div class="gantt_bar{{if $tudu.type == 'task'}} {{if $tudu.istudugroup}}gantt_bar_group{{else}}{{if !$tudu.endtime}}gantt_bar_gray{{else}}gantt_bar_blue{{/if}}{{/if}}{{else}} gantt_bar_yellow{{/if}}" style="width:{{$draw.width}};left:{{$draw.left}}">{{if $draw.leftlimit}}<div class="gantt_bar_ld"></div>{{/if}}{{if $draw.rightlimit}}<div class="gantt_bar_rd"></div>{{/if}}<div class="gantt_bar_cn" style="{{if $draw.leftlimit}}margin-left:8px;_margin-left:0;{{/if}}{{if $draw.rightlimit}}margin-right:8px;_margin-right:0{{/if}}"></div></div>
                {{if $tudu.endtime && $tudu.isexpired && !$tudu.istudugroup}}
                <div class="gantt_bar gantt_bar_red" style="width:{{$draw.exwidth}};left:{{$draw.exleft}}"><div class="gantt_bar_rd"></div><div class="gantt_bar_cn" style="margin-right:8px;_margin-right:0"></div></div>
                {{/if}}
                {{if $tudu.endtime && $tudu.status == 2 && !$tudu.istudugroup}}
                <div class="gantt_bar gantt_bar_green" style="width:{{$draw.exwidth}};left:{{$draw.exleft}}">{{if $draw.exleftlimit}}<div class="gantt_bar_ld"></div>{{/if}}{{if $draw.exrightlimit}}<div class="gantt_bar_rd"></div>{{/if}}<div class="gantt_bar_cn" style="{{if $draw.exleftlimit}}margin-left:8px;_margin-left:0;{{/if}}{{if $draw.exrightlimit}}margin-right:8px;_margin-right:0{{/if}}"></div></div>
                {{/if}}
                {{/if}}
                </div>
                </td>
              </tr>
            </table>
            <div class="gantt_children_list">
            {{strip}}
            {{foreach item=children from=$childrens}}
            <table _tuduid="{{$children.tuduid}}" cellspacing="0" cellpadding="0" class="grid_list_2 gantt_list{{if !$children.isdraft}}{{/if}}{{if $children.type == 'task' && $children.isexpired}} expired{{/if}}"  _st="{{$children.starttime|date_format:'%Y/%m/%d'}}" _et="{{$children.endtime|date_format:'%Y/%m/%d'}}" _previd="{{$children.prevtuduid}}">
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
                {{cal_gantt min=$starttime max=$endtime starttime=$children.starttime endtime=$children.endtime isexpired=$children.isexpired status=$children.status completetime=$children.completetime istudugroup=$children.istudugroup allday=true assign=draw}}
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
            </div>
          </div>
        </div>
      <div class="toolbar">
        <div><button type="button" name="print">{{$LANG.print}}</button><button type="button" name="close">{{$LANG.close}}</button></div>
      </div>
    </div>
</div>
<script type="text/javascript">
<!--
function doPrint() {
    try {
        window.print();
    } catch (e) {
        alert('{{$LANG.print_err}}');
    }
}

var Tips = {
    html: '<div class="float_tips"><div class="float_tips_body"></div></div>',

    ele: null,

    show: function(content, left, top) {
        if (null === this.ele) {
            this.ele = $(this.html);
            this.ele.appendTo(document.body).hide();
        }

        this.ele.find('.float_tips_body').html(content);
        this.ele.show();

        var width = this.ele.width(),
            bodyWidth = $(document.body).width(),
            pos = {
                left : left ? left + 20 : 0,
                top : top ? top + 10 : 0
            };


        if (width + left > bodyWidth) {
            pos.left = bodyWidth - width - 25;
        }

        this.ele.css({
            left : pos.left + 'px',
            top : pos.top + 'px'
        });
     },

    hide: function() {
        if (this.ele) {
            this.ele.find('.float_tips_body').empty();
            this.ele.hide();
        }
    }
};

$('button[name="close"]').bind('click', function(){window.close();});
$('button[name="print"]').bind('click', function(){doPrint();});
$('#tudu-list table.grid_list_2').each(function(){
    var o = $(this), tuduid = o.attr('_tuduid');

    o.mouseover(function(e){
        o.addClass('over');

        var subject   = o.find('a[name="subject"]').attr('_title'),
            startTime = o.attr('_st'),
            endTime   = o.attr('_et'),
            prepose   = o.attr('_pre');

        if (prepose) {
            prepose = $('#' + prepose + ' a[name="subject"]').attr('_title');
        }

        o.find('a[name="subject"]:eq(0),div.gantt_bar:eq(0),div.gantt_bar:eq(1)').mousemove(function(e){
            var scrollTop = document.documentElement ? document.documentElement.scrollTop : document.body.scrollTop;

            var html = '<p>'+subject+'</p><p>{{$LANG.time}}{{$LANG.cln}}'+startTime+' - '+endTime+'</p>';

            if (o.attr('_previd')) {
                var p = o.parent().find('table.grid_list_2[_tuduid="'+o.attr('_previd')+'"]:eq(0)');
                if (p.size()) {
                    html += '<p>{{$LANG.prev_tudu}}{{$LANG.cln}}'+p.find('a[name="subject"]').attr('_title')+'</p>';
                }
            }

            Tips.show(html, e.clientX, e.clientY + scrollTop);
        })
        .mouseout(function(){
            Tips.hide();
        });
    })
    .mouseout(function(){
        o.removeClass('over');
    });
    initCollspan(o);
});

function initCollspan(o) {
    var treeicon = o.find('.tree-ec-icon'),
        tuduid   = o.attr('_tuduid'),
        chct     = o.next('div.gantt_children_list');
    treeicon.click(function(){
        var me = $(this);

        if (me.hasClass('tree-elbow-minus')) {
            me.removeClass('tree-elbow-minus');

            chct.hide();
        } else {
            me.addClass('tree-elbow-minus');

            if (!chct.find('.grid_list_2').size()) {
                if (tuduid.indexOf('ch-') != -1) {
                    var tid = tuduid.replace('ch-', '');
                } else {
                    var tid = tuduid;
                }
                chct.load('/tudu/children?currUrl={{$smarty.server.REQUEST_URI|escape:'url'}}&view=gantt&sd={{$starttime}}&ed={{$endtime}}&tpl=previewchildgantt&tid=' + tid, function(){
                    chct.find('.grid_list_2').each(function(){
                        var child = $(this), id = child.attr('_tuduid');
                        child.mouseover(function(e){
                            child.addClass('over');

                            var subject   = child.find('a[name="subject"]').attr('_title'),
                                startTime = child.attr('_st'),
                                endTime   = child.attr('_et');

                            child.find('a[name="subject"]:eq(0),div.gantt_bar:eq(0)').mousemove(function(e){
                                var scrollTop = document.documentElement ? document.documentElement.scrollTop : document.body.scrollTop;

                                var html = '<p>'+subject+'</p><p>{{$LANG.time}}{{$LANG.cln}}'+startTime+' - '+endTime+'</p>';
                                if (child.attr('_previd')) {
                                    var p = child.parent().find('table.grid_list_2[_tuduid="'+child.attr('_previd')+'"]:eq(0)');
                                    if (p.size()) {
                                        html += '<p>{{$LANG.prev_tudu}}{{$LANG.cln}}'+p.find('a[name="subject"]').attr('_title')+'</p>';
                                    }
                                }

                                Tips.show(html, e.clientX, e.clientY + scrollTop);
                            })
                            .mouseout(function(){
                                Tips.hide();
                            });
                        })
                        .mouseout(function(){
                            child.removeClass('over');
                        });

                        initCollspan(child);
                    });
                });
            }

            chct.show();
        }
    });
}
-->
</script>
</body>
</html>
