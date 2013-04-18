<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$tudu.subject}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
{{if $newwin}}
{{include file="^newwin.tpl"}}
{{/if}}
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
{{if !$newwin}}
<script type="text/javascript">
<!--
var LH = 'm=view&tid={{$tudu.tuduid}}&page={{$pageinfo.currpage}}{{if $jumpfloor}}&floor={{$floor}}{{/if}}';
if (top == this) {
    location = location.href + '&newwin=1';
}
-->
</script>
{{/if}}
</head>
<body>
{{assign var="currUrl" value=$smarty.server.REQUEST_URI|escape:'url'}}
<input type="hidden" id="invert" value="{{$isinvert}}" />

{{include file="^boardnav.tpl" last="true"}}
<div class="panel">
    <div class="panel-body">
      <div id="float-toolbar" class="float-toolbar">
         <div class="toolbar">
         {{include file="tudu#view^toolbar.tpl"}}
         </div>
      </div>
      <div id="toolbar" class="toolbar">
        {{include file="tudu#view^toolbar.tpl"}}
      </div>
      <div class="content_box2 todo_wrap">
{{if 0}}
        <div class="msg"><p>
        {{if $access.receiver}}
        {{assign var="info" value="tudu_receiver_info_$tudu.status"}}
        {{else}}
        {{assign var="info" value="tudu_info_$tudu.status"}}
        {{/if}}
        </p></div>
{{/if}}
        <div class="todo_title_wrap">
            <div class="todo_title">
                {{if $tudu.privacy || $tudu.priority}}
                {{strip}}
                <span class="gray" style="font-weight:normal;font-size:12px;"><strong class="red">!</strong>[{{if $tudu.priority && $tudu.privacy}}<span class="red">{{$LANG.urgent}}</span>+<span class="red">{{$LANG.private}}</span>
                {{elseif $tudu.priority}}<span class="red">{{$LANG.urgent}}</span>
                {{elseif $tudu.privacy}}<span class="red">{{$LANG.private}}</span>{{/if}}]</span>
                {{/strip}}
                {{/if}}
                {{if $tudu.classname}}[{{$tudu.classname}}]{{/if}}{{$tudu.subject|escape:'html'}}<a href="javascript:void(0);" id="star" style="margin-left:5px" class="icon icon_attention{{if in_array('^t', $tudu.labels)}} attention{{/if}}" onclick="Tudu.star('{{$tudu.tuduid}}', this);" title="{{if in_array('^t', $tudu.labels)}}{{$LANG.cancel_starred}}{{else}}{{$LANG.mark_starred}}{{/if}}"></a>
            </div>
            <div class="tag_wrap">
            {{foreach item=label from=$labels}}
                {{if strpos($label.labelid, '^') === false && in_array($label.labelid, $tudu.labels)}}
                <table id="label-{{$label.labelid}}" cellspacing="0" cellpadding="0" class="flagbg" style="background-color:{{$label.bgcolor|default:'#d00'}}">
                  <tr class="falg_rounded_wrap">
                    <td class="falg_rounded"></td>
                    <td colspan="2"></td>
                    <td class="falg_rounded"></td>
                  </tr>
                  <tr>
                    <td class="falg_line"></td>
                    <td class="tag_txt" style="color:#fff">{{$label.labelalias}}</td>
                    <td class="tag_close" onClick="Tudu.removeLabel('{{$tudu.tuduid}}', '{{$label.labelalias}}');$('#label-{{$label.labelid}}').remove();">&nbsp;</td>
                    <td class="falg_line"></td>
                  </tr>
                  <tr class="falg_rounded_wrap">
                    <td class="falg_rounded"></td>
                    <td colspan="2"></td>
                    <td class="falg_rounded"></td>
                  </tr>
                </table>
                {{/if}}
            {{/foreach}}
            </div>
            <div class="clear"></div>
        </div>
        <div class="todo_content">
            <table border="0" cellspacing="4" cellpadding="0">
              <colgroup>
                <col>
                <col width="200">
                <col>
                <col>
              </colgroup>
              <tr>
                <td align="right"><span>{{$LANG.title_sender}}</span></td>
                <td class="black" title="{{$tudu.from.0}}<{{$tudu.from.3}}>">{{if $tudu.from.3 == $user.username}}{{$LANG.me}}{{else}}{{$tudu.from.0}}{{/if}}</td>
                <td align="right"><span>{{$LANG.title_accept_user}}</span></td>
                <td class="black"{{if $tudu.cc}} title="{{foreach item=cc from=$tudu.cc name=cc}}{{if !$smarty.foreach.cc.first}},&#13;{{/if}}{{$cc.0}}{{if $cc.3}}<{{if strpos($cc.3, '@')}}{{$cc.3}}{{else}}{{$LANG.group}}{{/if}}>{{/if}}{{/foreach}}"{{/if}}>{{foreach item=cc from=$tudu.cc name=cc}}{{if $smarty.foreach.cc.index < 6}}{{if $cc.3 == $user.username}}{{$LANG.me}}{{else}}{{$cc.0}}{{/if}}{{if $smarty.foreach.cc.index + 1 < count($tudu.cc)}},{{/if}}{{/if}}{{foreachelse}}-{{/foreach}}{{if $tudu.cc && count($tudu.cc) > 6}}...{{/if}}</td>
              </tr>
              {{if $tudu.endtime}}
              <tr>
                <td align="right"><span>{{$LANG.endtime}}{{$LANG.cln}}</span></td>
                <td class="black" colspan="3">{{$tudu.endtime|date_format:'%Y-%m-%d'}}</td>
              </tr>
              {{/if}}
            </table>
            {{if 0}}
            <p><a href="#">所有附件</a>&nbsp;|&nbsp;<a href="#">进度更新</a>&nbsp;|&nbsp;<a href="#">日志</a>&nbsp;|&nbsp;<a href="#">打印</a>&nbsp;|&nbsp;<a href="#">邮件方式发送</a></p>
            {{/if}}
            <p><span class="icon icon_ext"></span><a href="javascript:void(0)" onclick="Tudu.View.toggleAttach('{{$tudu.tuduid}}')">{{$LANG.attachment}}</a>&nbsp;&nbsp;&nbsp;<span class="icon icon_log"></span><a href="javascript:void(0)" onclick="Tudu.View.toggleLog('{{$tudu.tuduid}}')">{{$LANG.log}}</a>&nbsp;&nbsp;&nbsp;<span class="icon icon_print"></span><a href="/tudu/print?tid={{$tudu.tuduid}}" target="_blank">{{$LANG.print}}</a>&nbsp;&nbsp;&nbsp;<span class="icon icon_order"></span>{{if !$isinvert}}<a href="/tudu/view?tid={{$tudu.tuduid}}{{if !$newwin}}&back={{$smarty.server.REQUEST_URI|urlencode}}{{/if}}&invert=1{{if $newwin}}&newwin=1{{/if}}">{{$LANG.invert_posts}}</a>{{else}}<a href="/tudu/view?tid={{$tudu.tuduid}}{{if !$newwin}}&back={{$smarty.server.REQUEST_URI|urlencode}}{{/if}}&invert=0{{if $newwin}}&newwin=1{{/if}}">{{$LANG.sequence_posts}}</a>{{/if}}{{if !$newwin}}&nbsp;&nbsp;&nbsp;<span class="icon icon_newwin"></span><a target="_blank" href="/tudu/view?tid={{$tudu.tuduid}}&newwin=1">{{$LANG.new_win}}</a>{{/if}}&nbsp;&nbsp;&nbsp;<span class="icon icon_edit_note"></span><a href="javascript:void(0)" onclick="Tudu.View.toggleNote('{{$tudu.tuduid}}')">{{$LANG.note}}</a></p>
        </div>
      </div>

      {{if $contacts}}
      <div class="todo_expand" id="contact-panel" style="position:relative;">
        <a href="javascript:void(0)" class="icon icon_close" onclick="$('#contact-panel').remove()" style="position:absolute;right:10px;top:10px"></a>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="todo_body">
          <tr>
            <td>
                <div class="todo_bd_tl"><div class="todo_bd_tr"><div class="todo_bd_tc"></div></div></div>
            </td>
          </tr>
          <tr>
            <td>
            <div class="todo_body_con send_result" style="line-height:25px;padding:0 15px;">
                <strong style="font-size:14px">{{$LANG.tudu_sent_success}}</strong>
                <p>{{$LANG.send_foreigner}}</p>
                <ul class="sent_contact_list" style="margin:0">
                {{foreach from=$contacts item=item}}
                {{if $item.truename || $item.email}}
                <li id="contact-{{$item.uniqueid}}"><span class="icon icon_square"></span>{{if $item.truename}}{{$item.truename}}{{if $item.email}}&lt;{{$item.email}}&gt;{{/if}}{{else}}{{$item.email}}({{$LANG.contact_save_as}}{{$item.email}}){{/if}}&nbsp;[<a href="javascript:void(0);" onclick="Tudu.deleteContact('{{$item.uniqueid}}')">{{$LANG.delete}}</a>]</li>
                {{/if}}
                {{/foreach}}
                </ul>
            </div>
            </td>
          </tr>
          <tr>
            <td>
            <div style="position:relative">
                <div class="todo_bd_wrap">
                    <div class="todo_bd_bl"><div class="todo_bd_br"><div class="todo_bd_bc"></div></div></div>
                </div>
            </div>
            </td>
          </tr>
        </table>
      </div>
      <script type="text/javascript">
      TOP.Contact.clear();
      </script>
      {{/if}}

      <!-- 便签 -->
      <div class="todo_expand" id="note-panel" style="display:none;position:relative;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="todo_body">
          <tr>
            <td>
                <div class="todo_bd_tl"><div class="todo_bd_tr"><div class="todo_bd_tc"></div></div></div>
            </td>
          </tr>
          <tr>
            <td>
            <div class="todo_body_con">
                <table cellpadding="0" cellspacing="0" border="0" width="100%">
    	        <tr>
    	           <td>
                   <div class="note_edit">
                       <input type="hidden" name="noteid" value="" />
                       <textarea name="notecontent" style="height:22px"></textarea>
                   </div>
                   </td>
    	           <td width="30" valign="top"><a href="javascript:void(0)" name="delete" class="icon icon_grab"></a></td>
                   <td width="25" valign="top"><a href="javascript:void(0)" class="icon icon_close" onclick="Tudu.View.toggleNote('{{$tudu.tuduid}}')"></a></td>
    	        </tr>
    	        </table>
            </div>
            </td>
          </tr>
          <tr>
            <td>
            <div style="position:relative">
                <div class="todo_bd_wrap">
                    <div class="todo_bd_bl"><div class="todo_bd_br"><div class="todo_bd_bc"></div></div></div>
                </div>
            </div>
            </td>
          </tr>
        </table>
      </div>

      <!-- 附件 -->
      <div class="todo_expand" id="attach-panel" style="display:none;position:relative;">
        <a href="javascript:void(0)" class="icon icon_close" onclick="Tudu.View.toggleAttach('{{$tudu.tuduid}}')" style="position:absolute;right:10px;top:10px"></a>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="todo_body">
          <tr>
            <td>
                <div class="todo_bd_tl"><div class="todo_bd_tr"><div class="todo_bd_tc"></div></div></div>
            </td>
          </tr>
          <tr>
            <td>
            <div class="todo_body_con">
                <div id="tudu-attach-list" class="log_list"></div>
            </div>
            </td>
          </tr>
          <tr>
            <td>
            <div style="position:relative">
                <div class="todo_bd_wrap">
                    <div class="todo_bd_bl"><div class="todo_bd_br"><div class="todo_bd_bc"></div></div></div>
                </div>
            </div>
            </td>
          </tr>
        </table>
      </div>

      <!-- 日志 -->
      <div class="todo_expand" id="log-panel" style="display:none;position:relative;">
        <a href="javascript:void(0)" class="icon icon_close" onclick="Tudu.View.toggleLog('{{$tudu.tuduid}}')" style="position:absolute;right:10px;top:10px"></a>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="todo_body">
          <tr>
            <td>
                <div class="todo_bd_tl"><div class="todo_bd_tr"><div class="todo_bd_tc"></div></div></div>
            </td>
          </tr>
          <tr>
            <td>
            <div class="todo_body_con">
                <div id="log-list" class="log_list"></div>
            </div>
            </td>
          </tr>
          <tr>
            <td>
            <div style="position:relative">
                <div class="todo_bd_wrap">
                    <div class="todo_bd_bl"><div class="todo_bd_br"><div class="todo_bd_bc"></div></div></div>
                </div>
            </div>
            </td>
          </tr>
        </table>
      </div>

      {{foreach from=$posts item=post name=post}}
      <div class="todo_expand" id="post-{{$post.postid}}" _floor="{{if !$isinvert}}{{math equation="(x)+((y-1)*z)" x=$smarty.foreach.post.index y=$pageinfo.currpage z=$pageinfo.pagesize}}{{else}}{{math equation="z-(y*x-1)" x=$smarty.foreach.post.index y=$pageinfo.currpage z=$tudu.replynum}}{{/if}}">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="todo_body">
          <tr>
            <td>
                <div class="todo_bd_tl"><div class="todo_bd_tr"><div class="todo_bd_tc"></div></div></div>
            </td>
          </tr>
          <tr>
            <td><div class="todo_body_con">
                <div class="todo_ps_profile">
                {{include file="tudu#view^profile.tpl"}}
                {{if $post.isfirst}}<a href="javascript:void(0)" class="toggle_content icon_fold" onclick="Tudu.View.togglePost('{{$post.postid}}', this)" title=""></a>{{/if}}
                </div>
                <div class="tudu-content-body">
                <div class="line_gray"></div>
                <div class="todo_info post-content">
                    {{$post.content|tudu_format_content}}
                </div>
                {{if $post.isfirst && $votes}}
                <div class="post-vote">
                {{foreach item=vote from=$votes}}
                <div id="vote-{{$vote.voteid}}">
                    <form name="voteform" id="voteform-{{$vote.voteid}}" action="/tudu-mgr/vote" method="post">
                        <input type="hidden" name="tuduid" value="{{$tudu.tuduid}}" />
                        <input type="hidden" name="voteid" value="{{$vote.voteid}}" />
                        <input type="hidden" name="maxchoices-{{$vote.voteid}}" value="{{$vote.maxchoices}}" />
                        <table border="0" cellspacing="4" cellpadding="4">
                        <tr>
                          <td colspan="3" align="center"><strong>{{$vote.title}}</strong></td>
                        </tr>
                        <tr>
                          <td colspan="3">
                            {{strip}}
                            <span class="b">{{if $vote.maxchoices == 1}}{{$LANG.single_choice}}{{else}}{{$LANG.more_choice}}{{/if}}</span>{{if $vote.maxchoices > 1}}<span class="gray">（{{$LANG.choice_maximum}}{{$vote.maxchoices}}{{$LANG.option}}）</span>{{/if}}
                            <span class="vote-visible gray">{{if $vote.visible && !$vote.isvoted}}{{$LANG.comma}}{{$LANG.after_view_vote_result}}{{/if}}</span>
                            <span class="gray">{{$LANG.comma}}{{$LANG.total}}<span class="vote-count">{{$vote.countvoter}}</span>{{$LANG.vote_ticket}}</span>
                            <span class="vote-privacy"{{if !$vote.privacy || ($vote.visible && !$vote.isvoted)}} style="display:none;"{{/if}}>{{$LANG.comma}}<a href="javascript:void(0)" class="view-voter" _voteid="{{$vote.voteid}}">{{$LANG.view_vote_info}}</a></span>
                            {{/strip}}
                          </td>
                        </tr>
                        {{foreach name=option item=option from=$vote.options}}
                        <tr id="option-{{$option.optionid}}">
                          {{if $vote.enabled && !$tudu.isdone}}
                          <td  valign="top" class="option_input" style="padding-left:0">
                            {{if $vote.maxchoices == 1}}
                            <input type="radio" value="{{$option.optionid}}" name="option-{{$vote.voteid}}[]" id="input-{{$option.optionid}}"/>
                            {{else}}
                            <input type="checkbox" value="{{$option.optionid}}" name="option-{{$vote.voteid}}[]" id="input-{{$option.optionid}}"/>
                            {{/if}}
                          </td>
                          {{/if}}
                          <td valign="top" class="option_text"><label for="input-{{$option.optionid}}">{{$option.text|escape:'html'}}</label></td>
                          <td valign="top"  width="280">
                            <div class="option_box vote-viewer"{{if $vote.visible && !$vote.isvoted}} style="display:none;"{{/if}}>
                                <div class="option_percent_bar">
                                    <div class="option_percent_{{math|chr equation="x%5+97" x=$smarty.foreach.option.index}}" style="{{if $option.votecount > 0}}width:{{math equation="(x/y)*100" x=$option.votecount y=$vote.votecount}}%;{{else}}display:none;{{/if}}" id="option-{{$option.optionid}}-percent-bar"></div>
                                 </div>
                                 <span id="option-{{$option.optionid}}-info" class="option_info"><em id="option-{{$option.optionid}}-count">{{$option.votecount}}</em><em id="option-{{$option.optionid}}-percent" class="gray">({{if $vote.votecount > 0}}{{math equation="round((x/y)*100, 2)" x=$option.votecount y=$vote.votecount}}{{else}}0{{/if}}%)</em></span>
                            </div>
                          </td>
                        </tr>
                        {{/foreach}}
                        {{if $vote.enabled && !$tudu.isdone}}
                        <tr>
                          <td colspan="3"><button type="submit" class="btn">{{$LANG.vote_now}}</button><span class="vote-info gray"></span>&nbsp;&nbsp;<a id="download-{{$vote.voteid}}" style="display:none" href="/tudu/export-vote?tid={{$tudu.tuduid}}&voteid={{$vote.voteid}}">下载查看</a></td>
                        </tr>
                        {{else}}
                        <tr>
                            <td colspan="3">
                            <span class="vote-info gray">
                            {{if $vote.expired}}
                            {{$LANG.vote_is_expired}}
                            {{elseif $vote.isvoted}}
                            {{$LANG.already_vote}}
                            {{elseif $tudu.isdone}}
                            {{$LANG.vote_is_close}}
                            {{/if}}
                            </span>&nbsp;&nbsp;<a href="/tudu/export-vote?tid={{$tudu.tuduid}}&voteid={{$vote.voteid}}">下载查看</a>
                            </td>
                        </tr>
                        {{/if}}
                        </table>
                    </form>
                </div>
                {{/foreach}}
                </div>
                {{/if}}
                {{if $post.lastmodify}}
                <div class="footnote">
                <p class="gray">
                {{assign var=lastposttime value=$post.lastmodify.1|date_time_format:$user.option.dateformat}}
                {{$LANG.post_last_modify|sprintf:$post.lastmodify.2:$lastposttime}}
                </p>
                </div>
                {{/if}}
                {{if $post.attachnum}}
               <div class="line_gray"></div>
               <div class="todo_el">
                    <span><strong>{{$LANG.attachment}}</strong>({{$post.attachnum}}{{$LANG.attach_unit}}<a href="#">{{$LANG.download_all}}</a>)</span>
                    {{foreach item=file from=$post.attachment}}
                    <p><span class="icon ficon {{$file.filename|file_ext}}"></span><a href="{{$file.fileid|tudu_get_attachment_url}}">{{$file.filename}}</a><span class="gray">({{$file.size|format_filesize}})</span><a href="{{$file.fileid|tudu_get_attachment_url:'view'}}" target="_blank">[{{$LANG.open_file}}]</a>&nbsp;&nbsp;<a onclick="Tudu.View.attachToNd('{{$file.fileid}}');">[{{$LANG.attach_save_to_nd}}]</a></p>
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
                        <td valign="bottom">
                        {{if $access.reply}}<span class="icon icon_reply"></span>&nbsp;<a href="javascript:void(0)" onClick="Tudu.View.replyPost('{{$post.postid}}');">{{$LANG.reply}}</a>&nbsp;
                        <span class="icon icon_quote"></span>&nbsp;<a href="javascript:void(0)" onClick="Tudu.View.reference('{{$post.postid}}');">{{$LANG.reference}}</a>&nbsp;{{/if}}
                        {{if $post.access.modify}}<span class="icon icon_modify"></span>&nbsp;<a href="/tudu/post?tid={{$tudu.tuduid}}&pid={{$post.postid}}{{if $newwin}}&newwin=1{{/if}}">{{$LANG.modify}}</a>&nbsp;{{/if}}
                        {{if $post.access.delete}}<span class="icon icon_del"></span>&nbsp;<a href="javascript:void(0)" onClick="Tudu.deletePost('{{$post.tuduid}}','{{$post.postid}}')">{{$LANG.delete}}</a>&nbsp;{{/if}}
                        </td>
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
      {{/foreach}}

      <div id="edit-floor" style="display:none;"></div>

      {{if $access.reply}}
      <form id="replyform" action="/compose/reply" method="post" class="content_box2 reply_wrap">
      <input type="hidden" name="tid" value="{{$tudu.tuduid}}" />
      <input type="hidden" id="action" name="action" value="{{if !$unreply.postid}}create{{else}}modify{{/if}}" />
      <input type="hidden" id="type" name="type" value="" />
      <input type="hidden" id="board" name="bid" value="{{$tudu.boardid}}" />
      <input type="hidden" id="fpid" name="fpid" value="{{$unreply.postid}}" />
      <input type="hidden" id="savetime" name="savetime" value="" />
        <table cellspacing="0" cellpadding="0" id="reply-table">
              <tr>
                <td>
                        <span class="fr"><span class="icon icon_reply_full"></span> <a id="link-fullreply" href="/tudu/post?tid={{$tudu.tuduid}}&back={{$smarty.server.REQUEST_URI|escape:'url'}}{{if $newwin}}&newwin=1{{/if}}" />{{$LANG.full_reply_mode}}</a></span>
                        <span class="add"><span class="icon icon_tpl"></span> <a href="javascript:void(0)" name="tpllist">{{$LANG.add_tpl_list}}</a></span>
                        {{if $access.upload}}<span class="upload_btn"><span id="upload-btn"></span></span><span class="add"><span class="icon icon_add"></span><a href="javascript:void(0)" id="upload-link">{{$LANG.file_upload}}</a></span>{{/if}}
                        {{if $access.upload && $user.maxndquota > 0}}<span class="add" id="netdisk-btn"><span class="icon icon_nd_attach"></span><a href="javascript:void(0)">{{$LANG.netdisk_attach}}</a></span>{{/if}}
                        <span class="add" id="screencp-btn"><span class="icon icon_screencp"></span><a href="javascript:void(0)" id="link-capture">{{$LANG.screencapture}}</a></span>
                        <span class="add"><span class="icon icon_photo"></span> <a href="javascript:void(0)" id="insert-pic">{{$LANG.picture}}</a></span>
                </td>
              </tr>
              <tr>
                <td style="padding:0 5px">
                <div id="attach-list" class="info_box att_container" style="{{if count($unreply.attachments) <= 0}}display:none; {{/if}}margin-right:0">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="bd_upload">
                            {{foreach item=file from=$unreply.attachments}}
                            <div class="filecell" id="attach-{{$file.fileid}}">
                            <input type="hidden" name="attach[]" value="{{$file.fileid}}" />
                            <div class="attsep">
                            <div class="attsep_file">
                            <span class="icon icon_add"></span><span class="filename">{{$file.filename}}</span>&nbsp;<span class="filesize">({{if $file.size < 1024}}{{$file.size}}bytes{{else}}{{math|intval equation="x/1024" x=$file.size}}KB{{/if}})</span></div>
                            <div class="attsep_del"><a href="javascript:void(0)" name="delete" onClick="Tudu.removeAttach('{{$file.fileid}}');">{{$LANG.delete}}</a></div><div class="clear"></div></div>
                            </div>
                            {{/foreach}}
                        </td>
                      </tr>
                    </table>
                </div>
                </td>
              </tr>
              <tr>
                <td><textarea class="form_textarea" id="content" name="editor" disabled="disabled" _disabled="disabled" cols="" rows="" style="width:100%;height:180px">{{$unreply.content}}</textarea><textarea id="postcontent" name="content" style="display:none;"></textarea></td>
              </tr>
              <tr>
                <td><button class="btn b" style="width:90px;" type="submit">{{$LANG.reply}}</button>&nbsp;&nbsp;<span class="compose_msg"></span></td>
              </tr>
            </table>
      </form>
      {{/if}}
    </div>
</div>

<div id="pic-modal" class="pic-modal" style="width:320px;display:none">
<div class="tab-header">
    <ul>
        {{if $access.upload}}<li class="active"><a href="javascript:void(0)" name="upload">{{$LANG.upload_pic}}</a></li>{{/if}}
        <li><a href="javascript:void(0)" name="url">{{$LANG.network_pic}}</a></li>
    </ul>
</div>
<div class="dialog-body">
    <div class="tab-body" id="tb-upload">
    <div class="dialog-item"><span class="gray">{{$LANG.upload_pic_hint}}</span></div>
    <div class="dialog-item">
    <span class="imgupload" style="position:absolute;float:right;"><div id="pic-upload-btn"></div></span>
    {{$LANG.select_pic}}{{$LANG.cln}}<input type="text" class="input_text" name="filename" id="filename" style="width:125px;margin-right:3px" /><button type="button" name="browse">{{$LANG.browse}}</button>
    </div>
    <div class="dialog-item" style="text-align:right;padding-right:25px"><button type="button" name="upload">{{$LANG.upload}}</button> <button type="button" name="piccancel">{{$LANG.cancel}}</button></div>
    </div>
    <div class="tab-body" id="tb-url" style="display:none">
    <div class="dialog-item"><span class="gray">{{$LANG.network_pic_hint}}</span></div>
    <div class="dialog-item">{{$LANG.pic_url}}{{$LANG.cln}}<input type="text" class="input_text" style="width:220px" name="url" id="picurl" value="http://" /></div>
    <div class="dialog-item" style="text-align:right;padding-right:25px"><button type="button" name="confirm">{{$LANG.confirm}}</button> <button type="button" name="piccancel">{{$LANG.cancel}}</button></div>
    </div>
</div>
</div>

<script src="{{$options.sites.static}}/js/tudu/view.js?1017" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/upload.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/card.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/plugins.js?1004" type="text/javascript"></script>
<script type="text/javascript">
<!--
var _TUDU_ID = '{{$tudu.tuduid}}';
    _BACK = '{{$query.back|default:'/tudu/?search=inbox'}}';
    currUrl = '{{$smarty.server.REQUEST_URI|escape:'url'}}';

$(function(){
    {{if $newwin}}Tudu.View.setIsNewWin(true);{{/if}}
    {{if !$newwin}}
    TOP.Frame.hash(LH);
    {{/if}}
    TOP.Label.setLabels({{format_label labels=$labels}});
    TOP.Frame.title('{{$LANG.discuss}} - {{$tudu.subject|escape:'javascript'}}');

    setTimeout(function(){
        TOP.Label.refreshMenu();
    }, 100);


    {{if $user.option.settings.fontfamily}}
    var editorCss = {
        'font-family':'{{$user.option.settings.fontfamily}}',
        'font-size':'{{$user.option.settings.fontsize|default:'12px'}}'
    };
    Tudu.SetEditorCss(editorCss);
    {{/if}}

    var access = {
        'reply': {{if $access.reply}}1{{else}}0{{/if}},
        'upload': {{if $access.upload}}1{{else}}0{{/if}},
        'vote': {{if $votes && !$tudu.isdone}}1{{else}}0{{/if}}
    };

    Tudu.View.setLabels(TOP.Label.getLabels()).setAccess(access);
    Tudu.View.init('{{$tudu.tuduid}}', _BACK, currUrl);

    {{if $access.reply}}
    Tudu.Reply.init({
        {{if $access.upload}}
        upload: {
            uploadUrl: '{{$options.sites.file}}{{$upload.cgi.upload}}',
            postParams: {'cookies': '{{$cookies}}'},
            fileSizeLimit: '{{$uploadsizelimit}}',
            auth: ''
        },
        {{/if}}
        form: '#replyform',
        progress: {{if $access.progress}}1{{else}}0{{/if}}
    });
    {{/if}}

    {{if $last}}
    window.scrollTo(0,$('div.todo_expand:last').offset().top);
    {{/if}}

    {{if $jumpfloor}}
    window.scrollTo(0,$('div[_floor="{{$floor}}"]').offset().top - 35);
    {{/if}}

    {{if $access.reply && $access.upload}}
    var filedialog = null;
    $('#netdisk-btn').click(function(){
        if (filedialog === null) {
            filedialog = new FileDialog({id: 'netdisk-dialog'});
        }

        filedialog.show();
    });
    Capturer.setUploadUrl('{{$options.sites.file}}{{$upload.cgi.upload}}');
    Capturer.setEditor(Tudu.Reply.getEditor());
    {{/if}}

    new FixToolbar({
        src: '#toolbar',
        target: '#float-toolbar'
    });
});
-->
</script>
</body>
</html>
