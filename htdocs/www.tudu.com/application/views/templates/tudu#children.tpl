{{strip}}
	<table cellspacing="0" cellpadding="0" id="toggle-children" width="100%" class="grid_list_title">
	  <tr>
	    <td width="15%" align="left"><span class="icon icon_elbow_minus toggle_children" style="cursor:pointer;"></span><h3>{{$LANG.tudu_divide}}</h3><span id="children-count">({{$tudus|@count}})</span></td>
	    <td align="center" width="70%"><div id="child-unread-info" style="width:100%;text-align:center;display:none;"><span style="color:black">{{$LANG.divide_unread|sprintf:'<span id="child-unread-count"></span>'}}{{$LANG.comma}}{{$LANG.signas}}</span><a href="javascript:void(0)" onclick="readChildren();">{{$LANG.read}}</a></div></td>
	    <td width="15%" align="right"><a href="/tudu/preview-gantt?tid={{$tuduid}}" target="_blank">[{{$LANG.gantt_view}}]</a></td>
	  </tr>
	</table>
	<table cellspacing="0" class="grid_thead" id="children-header">
      <tr>
        <td align="center" width="40"><div style="padding-left:4px"><span class="mailtitle"></span></div></td>
        <td width="100" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="loadChildren(3,{{$sort[1]}});return false;">{{$LANG.sender}}{{if $sort[0]==3}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
        <td class="title_line"><div class="space"><a href="javascript:void(0);" onClick="loadChildren(1,{{$sort[1]}});return false;">{{$LANG.subject}}{{if $sort[0]==1}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
        <td width="110" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="loadChildren(4,{{$sort[1]}});return false;">{{$LANG.column_accepter}}{{if $sort[0]==4}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a>/<a href="javascript:void(0);" onClick="loadChildren(2,{{$sort[1]}});return false;">{{$LANG.column_endtime}}{{if $sort[0]==2}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
        <td width="90" class="title_line"><div class="space">{{$LANG.column_reply}}</div></td>
        <td width="100" class="title_line"><div class="space"><a href="javascript:void(0);" onClick="loadChildren(0,{{$sort[1]}});return false;">{{$LANG.lastpost}}{{if $sort[0]==0}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
        <td width="20" style="padding-left:0">&nbsp;</td>
      </tr>
    </table>
	<div id="children-list" class="grid_list_group_ct children_group">
		{{foreach from=$tudus item=childtudu name="children"}}
		{{if $childtudu.deny}}
		<table id="tudu-{{$childtudu.tuduid}}" cellspacing="0" class="grid_list_2" style="color:#999" _labels="{{foreach item=lab from=$tudu.labels}}{{if strpos($lab, '^') === false}}{{$lab}}|{{/if}}{{/foreach}}" _previd="{{$tudu.prevtuduid}}">
          <tr>
            <td class="g_i">
              <input type="hidden" name="tuduid[]" value="{{$childtudu.tuduid}}" />
              <div class="g_i_l{{if $childtudu.priority > 0}} ipt{{/if}}"></div>
              <a href="/tudu/view?tid={{$childtudu.tuduid}}&newwin=1&back={{$currUrl}}" target="_blank" class="g_i_c Rr{{if $childtudu.istudugroup}}g{{/if}}{{if !$childtudu.isread}}u{{/if}}{{if $childtudu.special == 1}}_c{{/if}}" title="{{$LANG.new_win_tips}}"></a>
              <div class="g_i_r{{if $childtudu.attachnum > 0}} el{{/if}}"></div>
            </td>
            <td class="g_in">
              <table class="g_in_table" cellspacing="0" cellpadding="0">
                <tr>
                  <td class="sender"><div class="space">-</div></td>
                  <td class="subject">
                  <div class="space">
                    {{*主题分类*}}
                    <u>{{$LANG.perm_deny_view_tudu}}</u>
                  </div>
                  </td>
                  <td class="deadline">
                    <div class="space">
                    <cite>-</cite>
                    <em>-</em>
                    </div>
                  </td>
                  <td class="rate">
                    <div class="space">
                    <div class="rate_box rate_box2"><div class="rate_bar" style="width:100%;"><em></em></div></div>
                    <em>-</em>
                    </div>
                  </td>
                  <td class="lastupdate"><div class="space"><cite>-</cite><em>-</em></div></td>
                  <td width="20" style="padding:0"><div class="space"><span href="javascript:void(0);" class="icon icon_attention"></span></div></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
		{{else}}
		<table id="tudu-{{$childtudu.tuduid}}" cellspacing="0" class="grid_list_2{{if !$childtudu.isread && !in_array('^r', $childtudu.labels)}} unread{{/if}}{{if $childtudu.isexpired}} expired{{/if}}" _attr="|{{$childtudu.type}}|{{if in_array($user.email, $childtudu.accepter)}}to|{{/if}}{{if array_key_exists($user.email, $childtudu.cc)}}cc|{{/if}}{{if $user.userid == $childtudu.from.1}}from|{{/if}}" _labels="{{foreach item=lab from=$childtudu.labels}}{{if strpos($lab, '^') === false}}{{$lab}}|{{/if}}{{/foreach}}" _previd="{{$tudu.prevtuduid}}">
          <tr>
            <td class="g_i">
              <input type="hidden" name="tuduid[]" value="{{$childtudu.tuduid}}" />
              <div class="g_i_l{{if $childtudu.priority > 0}} ipt{{/if}}"></div>
              <a href="/tudu/view?tid={{$childtudu.tuduid}}&newwin=1&back={{$currUrl}}" target="_blank" title="{{$LANG.new_win_tips}}" class="g_i_c Rr{{if $childtudu.istudugroup}}g{{/if}}{{if !$childtudu.isread}}u{{/if}}{{if $childtudu.special == 1}}_c{{/if}}"></a>
              <div class="g_i_r{{if $childtudu.attachnum > 0}} el{{/if}}"></div>
            </td>
            <td class="g_in">
              <table class="g_in_table" cellspacing="0" cellpadding="0">
                <tr>
                  <td class="sender"><div class="space"><a _email="{{$childtudu.sender}}" href="/contact/view?email={{$childtudu.sender}}&back={{$currUrl}}">{{if $childtudu.sender==$user.email}}{{$LANG.me}}{{else}}{{$childtudu.from.0|default:'-'}}{{/if}}</a></div></td>
                  <td class="subject">
                  <div class="space">
                    {{*主题分类*}}
                    {{if $childtudu.classname}}
                    <span class="blue">[{{$childtudu.classname}}]</span>
                    {{/if}}
                    {{*主题前面的状态显示*}}
                    {{if $childtudu.status > 1}}
                    {{assign var="statusKey" value="tudu_status_"|cat:$childtudu.status"}}
                    <span class="gray status">[{{$LANG[$statusKey]}}]</span>
                    {{elseif $childtudu.isexpired}}
                    <span class="gray status">[{{$LANG.tudu_timeover}}]</span>
                    {{/if}}
                    <a href="/tudu/view?tid={{$childtudu.tuduid}}&back={{$currUrl}}"{{if $childtudu.isdone}} class="gray"{{/if}} title="{{$childtudu.subject|escape:'html'}}">{{$childtudu.subject|escape:'html'|default:$LANG.null_subject}}</a>
                    {{* 列表分页 *}}
                    {{tudu_list_pagenav recordcount=$tudu.replynum+1 pagesize=20 url='/tudu/view' query='tid='|cat:$childtudu.tuduid|cat:'&back='|cat:$currUrl}}
                    {{*主题后面的待接受跟确认提示*}}
                    {{if $childtudu.status >= 2 && !$childtudu.isdone && $childtudu.sender == $user.username}}
                    <span class="tips_label" style="margin-left:5px">
                        <span class="tips_label_tl"><span class="tips_label_tr"><span class="tips_label_tc"></span></span></span>
                        <span class="tips_label_body" style="text-align:center">{{$LANG.status_waitforconfirm}}</span>
                        <span class="tips_label_bl"><span class="tips_label_br"><span class="tips_label_bc"></span></span></span>
                    </span>
                    {{/if}}
                    {{if in_array($user.username, $childtudu.accepter, true) && ($childtudu.selftudustatus < 2 && !$childtudu.selfaccepttime)}}
                    <span class="tips_label tips_receive" style="margin-left:5px">
                        <span class="tips_label_tl"><span class="tips_label_tr"><span class="tips_label_tc"></span></span></span>
                        <span class="tips_label_body" style="text-align:center">{{$LANG.need_accept}}</span>
                        <span class="tips_label_bl"><span class="tips_label_br"><span class="tips_label_bc"></span></span></span>
                    </span>
                    {{/if}}
                  </div>
                  <div class="label_div">

                  </div>
                  </td>
                  <td class="deadline">
                    <div class="space">
                    {{if $childtudu.acceptmode && !$childtudu.accepttime}}
                    <cite>{{$LANG.await_claim}}</cite>
                    {{elseif !$childtudu.acceptmode && count($childtudu.accepter) > 1}}
                    <cite>{{$LANG.multi_accepter}}</cite>
                    {{else}}
                    <cite>{{if $childtudu.accepter}}<a _email="{{$childtudu.accepter[0]}}" href="/contact/view?email={{$childtudu.accepter[0]}}&back={{$currUrl}}">{{if $childtudu.accepter.0===$user.email}}{{$LANG.me}}{{else}}{{$childtudu.to[$childtudu.accepter.0].0|default:'-'}}{{/if}}</a>{{else}}-{{/if}}</cite>
                    {{/if}}
                    <em>{{$childtudu.endtime|date_format:'%Y-%m-%d'|default:'-'}}</em>
                    </div>
                  </td>
                  <td class="rate">
                    <div class="space">
                    <div class="rate_box"><div class="rate_bar" style="width:{{$childtudu.percent|default:0}}%;"><em>{{$childtudu.percent|default:0}}%</em></div></div>
                    <em>{{$childtudu.replynum}}/{{$childtudu.viewnum}}</em>
                    </div>
                  </td>
                  <td class="lastupdate" title="{{$childtudu.lastposttime|date_format:'%Y-%m-%d %H:%M'}}"><div class="space"><cite>{{$childtudu.lastposter}}</cite><em>{{$childtudu.lastposttime|date_format:'%m-%d %H:%M'}}</em></div></td>
                  <td width="20" style="padding:0"><a href="javascript:void(0);" class="icon icon_attention{{if in_array('^t', $childtudu.labels)}} attention{{/if}}" onClick="markStar('{{$childtudu.tuduid}}');"></a></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
        {{/if}}
        {{foreachelse}}
        <table cellspacing="0" class="grid_list_2">
          <tr>
            <td class="g_i" style="padding:20px 0">{{$LANG.no_divide}}</td>
          </tr>
        </table>
		{{/foreach}}
	</div>
{{/strip}}
<script type="text/javascript">
$('#toggle-children span.toggle_children, #toggle-children h3').click(function(){
	var ico = $('#toggle-children span.icon');
	if (ico.hasClass('icon_elbow_minus')) {
		ico.removeClass('icon_elbow_minus').addClass('icon_elbow_plus');
	} else {
		ico.removeClass('icon_elbow_plus').addClass('icon_elbow_minus');
	}
	$('#children-list').toggle();
	$('#children-header').toggle();
});


$("#children-list table.grid_list_2")
.mouseover(function(){
	$(this).addClass("over")
})
.mouseout(function(){
	$(this).removeClass("over")
});

var chCard = new Card();
$('table.g_in_table td.sender').children('.space').children().each(function(){
	$(this).bind('mouseover', function(){
		chCard.show(this, 500);
	})
	.bind('mouseout', function(){
		chCard.hide();
	});
});
$('table.g_in_table td.deadline').children('.space').children().children().each(function(){
	$(this).bind('mouseover', function(){
		chCard.show(this, 500);
	})
	.bind('mouseout', function(){
		chCard.hide();
	});
});

var unreadnum = $('#children-list table.unread').size();
if (unreadnum > 0) {
	$('#child-unread-info').show();
	$('#child-unread-count').html(unreadnum);
}

function readChildren() {
	var ids = [];
	$('#children-list .grid_list_2').removeClass('unread');
	$('#children-list :hidden[name="tuduid[]"]').each(function(){
		ids.push(this.value);
	});

	$.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/mark',
        data: {tid: ids, fun: 'read'},
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, 'success');
            $('#child-unread-info').hide();
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}
</script>