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
        <div><button type="button" name="print">{{$LANG.print}}</button><button type="button" name="close">{{$LANG.close}}</button></div>
      </div>
      <div class="content_box2 todo_wrap">
{{if $remind || $cycle}}
        <div class="msg">
        {{if $remind}}
        <p>{{$remind}}</p>
        {{/if}}
        {{if $cycleremind}}
        <p>{{$cycleremind}}</p>
        {{/if}}
        </div>
{{/if}}
        <div class="todo_title_wrap">
            <div class="todo_title">{{if $tudu.classname}}[{{$tudu.classname}}]{{/if}}{{$tudu.subject|escape:'html'|default:$LANG.missing_subject}}</div>
            <div class="tag_wrap"></div>
            <div class="clear"></div>
        </div>
      {{if $tudu.type == 'notice'}}
      <div class="todo_content">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="65" align="right"><span class="gray">{{$LANG.title_sender}}</span></td>
            <td width="140">{{$tudu.from.0}}</td>
            <td width="65" align="right"><span class="gray">{{$LANG.title_accept_user}}</span></td>
            <td{{if $tudu.cc}} title="{{foreach item=cc from=$tudu.cc name=cc}}{{if !$smarty.foreach.cc.first}},{{/if}}{{$cc.0}}{{/foreach}}"{{/if}}>{{foreach item=cc from=$tudu.cc name=cc}}{{if !$smarty.foreach.cc.first}},{{/if}}{{$cc.0}}{{foreachelse}}-{{/foreach}}</td>
          </tr>
        </table>
      </div>
      {{elseif $tudu.type == 'discuss'}}
      <div class="todo_content">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="65" align="right"><span class="gray">{{$LANG.title_sender}}</span></td>
            <td width="140">{{$tudu.from.0}}</td>
            <td width="65" align="right"><span class="gray">{{$LANG.title_accept_user}}</span></td>
            <td{{if $tudu.cc}} title="{{foreach item=cc from=$tudu.cc name=cc}}{{if !$smarty.foreach.cc.first}},{{/if}}{{$cc.0}}{{/foreach}}"{{/if}}>{{foreach item=cc from=$tudu.cc name=cc}}{{if !$smarty.foreach.cc.first}},{{/if}}{{$cc.0}}{{foreachelse}}-{{/foreach}}</td>
          </tr>
          {{if $tudu.endtime}}
          <tr>
            <td width="65" align="right"><span class="gray">{{$LANG.endtime}}{{$LANG.cln}}</span></td>
            <td width="140">{{$tudu.endtime|date_format:'%Y-%m-%d'|default:'-'}}</td>
          </tr>
          {{/if}}
        </table>
      </div>
      {{else}}
      <div class="todo_content">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="65" align="right"><span class="gray">{{$LANG.title_sender}}</span></td>
            <td width="140">{{$tudu.from.0}}</td>
            <td width="65" align="right"><span class="gray">{{$LANG.title_cc}}</span></td>
            <td{{if $tudu.cc}} title="{{foreach item=cc from=$tudu.cc name=cc}}{{if !$smarty.foreach.cc.first}},{{/if}}{{$cc.0}}{{/foreach}}"{{/if}}>{{foreach item=cc from=$tudu.cc name=cc}}{{if !$smarty.foreach.cc.first}},{{/if}}{{$cc.0}}{{foreachelse}}-{{/foreach}}</td>
          </tr>
          <tr>
            <td align="right"><span class="gray">{{$LANG.title_receiver}}</span></td>
            <td title="{{foreach from=$tudu.to item=to name="to"}}{{$to.0}}{{if $smarty.foreach.to.index+1 < count($tudu.to)}},{{/if}}{{/foreach}}">{{foreach from=$tudu.to item=to name="to"}}{{$to.0}}{{if $smarty.foreach.to.index+1 < count($tudu.to)}},{{/if}}{{foreachelse}}-{{/foreach}}</td>
            <td align="right"><span class="gray">{{$LANG.title_status}}</span></td>
            <td>
            {{assign var="keyprefix" value="tudu_status_"}}
            {{assign var="status" value=$keyprefix|cat:$tudu.status}}
            {{$LANG[$status]|default:'-'}}
            </td>
          </tr>
          <tr>
            <td align="right"><span class="gray">{{$LANG.title_starttime}}</span></td>
            <td>{{$tudu.starttime|date_time_format:$user.option.dateformat:'date'|default:'-'}}</td>
            <td align="right"><span class="gray">{{$LANG.title_elapsed}}</span></td>
            <td>{{if $tudu.totaltime > 0}}{{math equation="x/3600" assign="totaltime" x=$tudu.totaltime}}{{$LANG.plan_total|sprintf:$totaltime}}{{/if}}{{math equation="x/3600" assign="elapsed" x=$tudu.elapsedtime|default:0}}{{$LANG.real_elapsed|sprintf:$elapsed}}</td>
          </tr>
          <tr>
            <td align="right"><span class="gray">{{$LANG.title_endtime}}</span></td>
            <td>{{$tudu.endtime|date_time_format:$user.option.dateformat:'date'|default:'-'}}</td>
            <td align="right"><span class="gray">{{$LANG.title_progress}}</span></td>
            <td><div class="rate_box"><div class="rate_bar" style="width:{{$tudu.percent|default:0}}%;"><em>{{$tudu.percent|default:0}}%</em></div></div></td>
          </tr>
          {{if $tudu.parentid}}
          <tr>
            <td align="right"><span class="gray">{{$LANG.tudu_group}}{{$LANG.cln}}</span></td>
            <td colspan="3">{{if $parent}}<a href="/tudu/view?tid={{$tudu.parentid}}&back={{$smarty.server.REQUEST_URI|urlencode}}">{{$parent.subject}}</a>{{else}}<span class="gray">{{$LANG.deny_tudu_group}}</span>{{/if}}</td>
          </tr>
          {{/if}}
        </table>
      </div>
      {{/if}}
      </div>

      {{if count($tudu.accepter) > 1}}
      <div id="tudu-accepter" class="grid_list_wrap grid_list_group" style="margin:-7px 0 10px; 0">
        <div class="grid_list_title" style="line-height:24px" id="toggle-accepter">
            <span class="icon icon_elbow_minus toggle_accepter" style="cursor:pointer;"></span><h3>{{$LANG.accepter_follow}}</h3><span id="accepter-count"></span>
        </div>
        <table cellspacing="0" class="grid_thead" id="accepter-header">
          <tr>
            <td width="30%" style="line-height:20px"><div class="space">{{$LANG.column_accepter}}</div></td>
            <td width="40%" class="title_line" style="line-height:20px"><div class="space">{{$LANG.percent}}</div></td>
            <td class="title_line" style="line-height:20px"><div class="space">{{$LANG.elapsed}}({{$LANG.hour}})</div></td>
            <td width="80" class="title_line" style="line-height:20px"><div class="space">{{$LANG.status}}</div></td>
          </tr>
        </table>
        <div id="accepter-list" class="grid_list_group_ct accepter_group">
            {{foreach from=$tudu.to item=item}}
            <table id="accepter-{uniqueid}" class="accepter_table" cellspacing="0">
            <tbody>
            <tr>
            <td width="30%"><div class="space">{{$item.0}}</div></td>
            <td width="40%"><div class="space"><div class="rate_box rate_box2"><div style="width:0" class="rate_bar"><em></em></div></div></div></td>
            <td><div class="space">-</div></td>
            <td width="80"><div class="space">-</div></td>
            </tr>
            </tbody>
            </table>
            {{/foreach}}
        </div>
      </div>
      {{/if}}

      {{if $children}}
      <div id="tudu-children" class="grid_list_wrap grid_list_group" style="margin:{{if count($tudu.accepter) > 1}}-10px{{else}}-7px{{/if}} 0 10px 0;position:relative;">
      {{strip}}
        <div class="grid_list_title" style="line-height:26px" id="toggle-children">
            <span class="icon icon_elbow_minus toggle_children" style="cursor:pointer;"></span><h3>{{$LANG.tudu_divide}}</h3><span id="children-count">({{$children|@count}})</span>
        </div>
        <table cellspacing="0" class="grid_thead" id="children-header">
          <tr>
            <td width="title_line" align="center" style="width:36px;padding-left:4px;line-height:20px"><span class="mailtitle"></span></td>
            <td width="100" class="title_line"><div class="space">{{$LANG.sender}}</a></div></td>
            <td class="title_line"><div class="space">{{$LANG.subject}}</div></td>
            <td width="110" class="title_line"><div class="space">{{$LANG.column_accepter}}/{{$LANG.column_endtime}}</div></td>
            <td width="90" class="title_line"><div class="space">{{$LANG.column_reply}}</div></td>
            <td width="80" class="title_line"><div class="space">{{$LANG.lastpost}}</div></td>
            <td width="20" style="padding-left:0">&nbsp;</td>
          </tr>
        </table>
        <div id="children-list" class="grid_list_group_ct children_group">
            {{foreach from=$children item=childtudu name="children"}}
            <table id="tudu-{{$childtudu.tuduid}}" cellspacing="0" class="grid_list_2">
              <tr>
                <td class="g_i">
                  <input type="hidden" name="tuduid[]" value="{{$childtudu.tuduid}}" />
                  <div class="g_i_l{{if $childtudu.priority > 0}} ipt{{/if}}"></div>
                  <div class="g_i_c Rr{{if $childtudu.istudugroup}}g{{/if}}{{if !$childtudu.isread}}u{{/if}}{{if $childtudu.special == 1}}_c{{/if}}"></div>
                  <div class="g_i_r{{if $childtudu.attachnum > 0}} el{{/if}}"></div>
                </td>
                <td class="g_in">
                  <table class="g_in_table" cellspacing="0" cellpadding="0">
                    <tr>
                      <td class="sender"><div class="space">{{if !$tudu.tuduid && !$childtudu.from}}{{$LANG.me}}{{else}}{{$childtudu.from.0|default:'-'}}{{/if}}</div></td>
                      <td class="subject">
                      <div class="space">
                      <div>
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
                        {{$childtudu.subject|escape:'html'|default:$LANG.null_subject}}
                      </div>
                      <div class="label_div">

                      </div>
                      </div>
                      </td>
                      <td class="deadline">
                        <div class="space">
                        {{if count($childtudu.accepter) > 1}}
                        <cite>{{$LANG.multi_accepter}}</cite>
                        {{else}}
                        <cite>{{if $childtudu.accepter}}{{if $childtudu.accepter.0==$user.email}}{{$LANG.me}}{{else}}{{$childtudu.to[$childtudu.accepter.0].0|default:'-'}}{{/if}}{{else}}-{{/if}}</cite>
                        {{/if}}
                        <em>{{$childtudu.endtime|date_format:'%Y-%m-%d'|default:'-'}}</em>
                        </div>
                      </td>
                      <td class="rate">
                        <div class="space">
                        <div class="rate_box"><div class="rate_bar" style="width:{{$childtudu.percent|default:0}}%;"><em>{{$childtudu.percent|default:0}}%</em></div></div>
                        <em>{{$childtudu.replynum|default:0}}/{{$childtudu.viewnum|default:0}}</em>
                        </div>
                      </td>
                      <td class="lastupdate"><div class="space"><cite>{{$childtudu.lastposter|default:'-'}}</cite><em>{{$childtudu.lastposttime|date_format:'%m-%d %H:%M'|default:'-'}}</em></div></td>
                      <td width="20" style="padding:0"></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            {{foreachelse}}
            <table cellspacing="0" class="grid_list_2">
              <tr>
                <td class="g_i" style="padding:20px 0">{{$LANG.no_divide}}</td>
              </tr>
            </table>
            {{/foreach}}
        </div>
      {{/strip}}
      </div>
      {{/if}}

      <div class="todo_expand" id="post-{{$post.postid}}">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="todo_body">
          <tr>
            <td>
            	<div class="todo_bd_tl"><div class="todo_bd_tr"><div class="todo_bd_tc"></div></div></div>
            </td>
          </tr>
          <tr>
            <td><div class="todo_body_con">
                <div class="todo_ps_profile">
				{{if $user.option.profilemode == 0}}
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td valign="top" width="50"><a href="javascript:void(0)" title="{{$LANG.user_chat}}" onclick="chat('{{$post.email}}')"><img class="todo_send_icon" src="/logo?unid={{$post.uniqueid}}"></a></td>

                    <td><p><span class="icon icon_{{if $post.imstatus.show=='chat'}}online{{elseif $post.imstatus.show=='dnd'}}busy{{elseif $post.imstatus.show=='away'}}leave{{else}}logout{{/if}}"><!-- online|busy|leave|logout --></span><a href="javascript:void(0)"><strong class="poster">{{$tudu.from.0}}</strong></a> {{$post.createtime|date_time_format:$user.option.dateformat}} </p><p class="gray">{{$post.posterinfo}}</p></td>
                    <td align="right"></td>
                  </tr>
                </table>
                {{else}}
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td><p><span class="icon icon_{{if $post.imstatus.show=='chat'}}online{{elseif $post.imstatus.show=='dnd'}}busy{{elseif $post.imstatus.show=='away'}}leave{{else}}logout{{/if}}"><!-- online|busy|leave|logout --></span><a href="javascript:void(0)"><strong class="poster">{{$tudu.from.0}}</strong></a> {{$post.createtime|date_time_format:$user.option.dateformat}} | <a href="javascript:void(0)" title="{{$LANG.user_chat}}" onclick="chat('{{$post.email}}')">{{$LANG.user_chat}}</a></p></td>
                    <td align="right"></td>
                  </tr>
                </table>
                {{/if}}
				<a href="javascript:void(0)" class="toggle_content icon_fold" onclick="togglePost('{{$post.postid}}', this)" title=""></a>
                </div>
                <div class="tudu-content-body">
                <div class="line_gray"></div>
                <div class="todo_info post-content">
                    {{$tudu.content|tudu_format_content}}
                </div>
                {{if $vote && $vote.options}}
                <div class="todo_info post-vote">
                <table border="0" cellspacing="4" cellpadding="4">
                {{foreach name=option item=option from=$vote.options}}
                <tr id="option-{{$option.optionid}}">
                    <td  valign="top" class="option_input">
                        {{if $vote.maxchoices == 1}}
                        <input type="radio" value="{{$option.optionid}}" name="option[]" id="input-{{$option.optionid}}"/>
                        {{else}}
                        <input type="checkbox" value="{{$option.optionid}}" name="option[]" id="input-{{$option.optionid}}"/>
                        {{/if}}
                    </td>
                    <td valign="top" class="option_text"><label for="input-{{$option.optionid}}">{{$option.text|escape:'html'}}</label></td>
                    <td valign="top"  width="280">
                        <div class="option_box">
                            <div class="option_percent_bar">
                                <div class="option_percent_{{math|chr equation="x%4+97" x=$smarty.foreach.option.index}}" style="width:{{if $vote.votecount > 0}}{{math equation="(x/y)*100" x=$option.votecount y=$vote.votecount}}{{else}}0{{/if}}%" id="option-{{$option.optionid}}-percent"></div>
                             </div><span id="option-{{$option.optionid}}-info" class="option_info"><em id="option-{{$option.optionid}}-count">{{$option.votecount}}</em><em id="option-{{$option.optionid}}-percent" class="gray">({{if $vote.votecount > 0}}{{math equation="round((x/y)*100, 2)" x=$option.votecount y=$vote.votecount}}{{else}}0{{/if}}%)</em></span>
                        </div>
                    </td>
                </tr>
                {{/foreach}}
                <tr>
                    <td colspan="2" align="center"><button type="submit" class="btn">{{$LANG.vote_now}}</button><span id="vote-info" class="gray"></span></td>
                </tr>
                <tr>
                    <td colspan="3" align="center"></td>
                </tr>
                </table>
                </form>
                </div>
                {{/if}}
                {{if $tudu.attachnum}}
                <div class="line_gray"></div>
                <div class="todo_el">
                    <span><strong>{{$LANG.attachment}}</strong>({{$tudu.attachnum}}{{$LANG.attach_unit}}<!-- <a href="#">{{$LANG.download_all}}</a> -->)</span>
                    {{foreach item=file from=$tudu.attachments}}
                    <p><span class="icon ficon {{$file.filename|file_ext}}"></span><span>{{$file.filename}}</span><span class="gray">({{$file.size|format_filesize}})</span></p>
                    {{/foreach}}
                </div>
                {{/if}}
                </div>
            </div></td>
          </tr>
          <tr>
            <td>
            <div style="position:relative">
            	<div class="toolbar_2_wrap" id="bar-post-{{$post.postid}}">
                  <div class="toolbar_2">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td valign="bottom"></td>
                        <td valign="bottom" align="right"><a href="#" class="top">top</a></td>
                      </tr>
                    </table>
                  </div>
                    <div class="todo_bd_bl"><div class="todo_bd_br"><div class="todo_bd_bc"></div></div></div>
                </div>
                <div class="todo_bd_wrap">
                	<div class="toolbar_2"></div>
                    <div class="todo_bd_bl"><div class="todo_bd_br"><div class="todo_bd_bc"></div></div></div>
                </div>
            </div>
            </td>
          </tr>
        </table>
      </div>

      <div class="toolbar">
        <div><button type="button" name="print">{{$LANG.print}}</button><button type="button" name="close">{{$LANG.close}}</button></div>
      </div>
    </div>
</div>
<script type="text/javascript">
<!--
function togglePost(postId, icon) {
    var icon = $(icon);

    var expanded = !icon.hasClass('icon_unfold');

    if (expanded) {
        $('#post-' + postId + ' div.tudu-content-body').hide();
        icon.addClass('icon_unfold');
    } else {
        $('#post-' + postId + ' div.tudu-content-body').show();
        icon.removeClass('icon_unfold');
    }
}

function doPrint() {
    try {
        window.print();
    } catch (e) {
        alert('{{$LANG.print_err}}');
    }
}

$('button[name="close"]').bind('click', function(){window.close();});
$('button[name="print"]').bind('click', function(){doPrint();});
-->
</script>
</body>
</html>
