<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$tudu.subject}}</title>
{{include file="foreign^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js" type="text/javascript"></script>
<script src="/static/js?f=lang&lang={{$user.option.language}}&ver=1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/xheditor-1.1.9/xheditor-zh-cn.min.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/all.js?2007" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/foreign/tudu.js?1012" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/upload.js?1007" type="text/javascript"></script>
<script type="text/javascript">
<!--
var TOP = getTop();
-->
</script>
</head>
<body>

{{assign var="currUrl" value=$smarty.server.REQUEST_URI|escape:'url'}}
{{include file="foreign^header.tpl" fav=true}}
<div class="panel">
    <div class="panel-body">
      <div id="float-toolbar" class="float-toolbar">
         <div class="toolbar">
         {{include file="foreign#tudu#view^toolbar.tpl"}}
         </div>
      </div>
      <div id="toolbar" class="toolbar">
        {{include file="foreign#tudu#view^toolbar.tpl"}}
      </div>
      <div class="content_box2 todo_wrap">
{{if $remind || $cycle}}
{{strip}}
        <div class="msg">
        {{if $remind}}
        <p>{{if $tudu.privacy || $tudu.priority}}<b class="red">!</b>[{{if $tudu.priority && $tudu.privacy}}<span class="red">{{$LANG.urgent}}</span>+<span class="red">{{$LANG.private}}</span>
        {{elseif $tudu.priority}}<span class="red">{{$LANG.urgent}}</span>
        {{elseif $tudu.privacy}}<span class="red">{{$LANG.private}}</span>{{/if}}]{{/if}}{{$remind}}</p>
        {{/if}}
        {{if $cycleremind}}
        <p>{{$cycleremind}}</p>
        {{/if}}
        </div>
{{/strip}}
{{/if}}
        <div class="todo_title_wrap">
        	{{assign var="keyprefix" value="tudu_status_"}}
            {{assign var="status" value=$keyprefix|cat:$tudu.status}}
            <div class="todo_title">{{if $tudu.status == 0 || $tudu.status == 3 || $tudu.status == 4}}<span class="gray">[{{$LANG[$status]}}]</span>{{/if}}{{if $tudu.classname}}[{{$tudu.classname}}]{{/if}}{{$tudu.subject|escape:'html'}}{{if in_array('^all', $tudu.labels)}}<a href="javascript:void(0);" id="star" style="margin-left:5px" class="icon icon_attention{{if in_array('^t', $tudu.labels)}} attention{{/if}}" onclick="markStar('{{$tudu.tuduid}}', this);"></a>{{/if}}</div>
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
                    <td class="tag_close" onclick="deleteLabel('{{$tudu.tuduid}}', '{{$label.labelalias}}', '{{$label.labelid}}')">&nbsp;</td>
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
        	{{assign var="SA" value=$tudu.starttime|date_time_format:"%w"}}
            {{assign var="EA" value=$tudu.endtime|date_time_format:"%w"}}
            <table border="0" cellspacing="3" cellpadding="0">
              <colgroup>
				<col>
				<col width="200">
				<col>
				<col>
			  </colgroup>
              <tr>
                <td align="right"><span class="gray">{{$LANG.title_receiver}}</span></td>
                <td title="{{if $tudu.acceptmode && !$tudu.accepttime}}{{$LANG.await_claim}}{{else}}{{foreach from=$tudu.to item=to name="to"}}{{$to.0}}{{if $to.3}}<{{$to.3}}>{{/if}}{{if $smarty.foreach.to.index+1 < count($tudu.to)}},&#13;{{/if}}{{/foreach}}{{/if}}">{{if $tudu.acceptmode && !$tudu.accepttime}}{{$LANG.await_claim}}{{else}}{{foreach from=$tudu.to item=to name="to"}}{{if $to.1 == $user.userid}}{{$LANG.me}}{{else}}{{$to.0}}{{/if}}{{if $smarty.foreach.to.index+1 < count($tudu.to)}},{{/if}}{{foreachelse}}{{$LANG.status_waitaccept}}{{/foreach}}{{/if}}</td>
                <td align="right"><span class="gray">{{$LANG.title_cc}}</span></td>
                <td{{if $tudu.cc}} title="{{foreach item=cc from=$tudu.cc name=cc}}{{if !$smarty.foreach.cc.first}},&#13;{{/if}}{{$cc.0}}{{if $cc.3}}<{{if strpos($cc.3, '@')}}{{$cc.3}}{{else}}{{$LANG.group}}{{/if}}>{{/if}}{{/foreach}}"{{/if}}>{{foreach item=cc from=$tudu.cc name=cc}}{{if !$smarty.foreach.cc.first}},{{/if}}{{if $cc.1 == $user.userid}}{{$LANG.me}}{{else}}{{$cc.0}}{{/if}}{{foreachelse}}-{{/foreach}}</td>
              </tr>
              <tr>
                <td align="right"><span class="gray">{{$LANG.title_progress}}</span></td>
                <td><div class="rate_box"><div class="rate_bar" style="width:{{$tudu.percent|default:0}}%;"><em>{{$tudu.percent|default:0}}%</em></div></div>&nbsp;&nbsp;<span>{{math equation="x/3600" assign="elapsed" x=$tudu.elapsedtime|default:0}}{{$LANG.real_elapsed|sprintf:$elapsed}}</span></td>
                <td align="right"><span class="gray">{{$LANG.periods_of_time}}{{$LANG.cln}}</span></td>
                <td>{{if $tudu.starttime || $tudu.endtime}}{{$tudu.starttime|date_time_format:'%Y-%m-%d'|default:'-'}}{{if $tudu.starttime}}（{{assign var=week value="week_"|cat:$SA}}{{$LANG[$week]}}）{{/if}} {{$LANG.date_to}} {{$tudu.endtime|date_time_format:'%Y-%m-%d'|default:'-'}}{{if $tudu.endtime}}（{{assign var=week value="week_"|cat:$EA}}{{$LANG[$week]}}）{{/if}}{{else}}-{{/if}}</td>
              </tr>
            </table>
            {{if 0}}
            <p><a href="#">所有附件</a>&nbsp;|&nbsp;<a href="#">进度更新</a>&nbsp;|&nbsp;<a href="#">日志</a>&nbsp;|&nbsp;<a href="#">打印</a>&nbsp;|&nbsp;<a href="#">邮件方式发送</a></p>
            {{/if}}
            <p><span class="icon icon_ext"></span><a href="javascript:void(0)" onclick="toggleAttach('{{$tudu.tuduid}}', '{{$tsid}}', '{{$user.uniqueid}}')">{{$LANG.attachment}}</a>&nbsp;&nbsp;&nbsp;<span class="icon icon_log"></span><a href="javascript:void(0)" onclick="toggleLog('{{$tudu.tuduid}}', '{{$tsid}}', '{{$user.uniqueid}}')">{{$LANG.log}}</a>&nbsp;&nbsp;&nbsp;<span class="icon icon_print"></span><a href="/foreign/tudu/print?tid={{$tudu.tuduid}}&fid={{$user.uniqueid}}&ts={{$tsid}}" target="_blank">{{$LANG.print}}</a>&nbsp;&nbsp;&nbsp;<span class="icon icon_order"></span>{{if !$isinvert}}<a href="/foreign/tudu/view?tid={{$tudu.tuduid}}&fid={{$user.uniqueid}}&ts={{$tsid}}&invert=1">{{$LANG.invert_posts}}</a>{{else}}<a href="/foreign/tudu/view?tid={{$tudu.tuduid}}&fid={{$user.uniqueid}}&ts={{$tsid}}&invert=0">{{$LANG.sequence_posts}}</a>{{/if}}</p>
        </div>
      </div>
      
      {{if count($steps) > 0}}
      {{include file="foreign#tudu#view^step.tpl"}}
      {{/if}}

      {{if !($tudu.acceptmode && !$tudu.accepttime) && count($tudu.accepter) > 1}}
      {{include file="foreign#tudu#view^accepter.tpl"}}
      {{/if}}
      
      <!-- 附件 -->
      <div class="todo_expand" id="attach-panel" style="display:none;position:relative;">
        <a href="javascript:void(0)" class="icon icon_close" onclick="toggleAttach('{{$tudu.tuduid}}', '{{$tsid}}', '{{$user.uniqueid}}')" style="position:absolute;right:10px;top:10px"></a>
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
        <a href="javascript:void(0)" class="icon icon_close" onclick="toggleLog('{{$tudu.tuduid}}', '{{$tsid}}', '{{$user.uniqueid}}')" style="position:absolute;right:10px;top:10px"></a>
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

      <!-- 回复 -->
      {{foreach from=$posts item=post name=post}}
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
                {{include file="foreign#tudu#view^profile.tpl"}}
                {{if $post.isfirst}}<a href="javascript:void(0)" class="toggle_content icon_fold" onclick="togglePost('{{$post.postid}}', this)" title=""></a>{{/if}}
                </div>
                <div class="tudu-content-body">
                <div class="line_gray"></div>
                <div class="todo_info post-content">
                    {{if $post.header}}
                    <div class="tudu_review_mark{{if $post.header.val}} review_agree{{else}} review_disagree{{/if}}"><div class="mark_icon"><div class="mark_border"><div class="mark_body"><div class="mark_content">{{$post.header.text}}</div></div></div></div></div>
                    {{/if}}
                    {{$post.content|tudu_format_content}}
                </div>
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
                    <span><strong>{{$LANG.attachment}}</strong>({{$post.attachnum}}{{$LANG.attach_unit}}<!-- <a href="#">{{$LANG.download_all}}</a> -->)</span>
                    {{foreach item=file from=$post.attachment}}
                    <p><span class="icon ficon {{$file.filename|file_ext}}"></span><a href="{{$file.fileid|tudu_get_attachment_url}}">{{$file.filename}}</a><span class="gray">({{$file.size|format_filesize}})</span><a href="{{$file.fileid|tudu_get_attachment_url:view}}" target="_blank">[{{$LANG.open_file}}]</a></p>
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
                        {{if $access.reply}}<span class="icon icon_reply"></span>&nbsp;<a href="javascript:void(0)" onClick="replyPost('{{$post.postid}}');">{{$LANG.reply}}</a>&nbsp;
                        <span class="icon icon_quote"></span>&nbsp;<a href="javascript:void(0)" onClick="reference('{{$post.postid}}');">{{$LANG.reference}}</a>&nbsp;{{/if}}
                        {{if $post.access.modify}}<span class="icon icon_modify"></span>&nbsp;<a href="/tudu/post?tid={{$tudu.tuduid}}&pid={{$post.postid}}&fid={{$user.uniqueid}}&ts={{$tsid}}">{{$LANG.modify}}</a>&nbsp;{{/if}}
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
      <!-- end 回复 -->

      {{if $access.reply}}
      <form id="replyform" action="/foreign/compose/reply" method="post" class="content_box2 reply_wrap">
      <input type="hidden" name="tid" value="{{$tudu.tuduid}}" />
      <input type="hidden" name="action" value="create" />
      <input type="hidden" name="fid" value="{{$user.uniqueid}}" />
      <input type="hidden" name="ts" value="{{$tsid}}" />
        <table cellspacing="0" cellpadding="0" id="reply-table">
        {{if $access.progress}}
              <tr>
                <td><input type="checkbox" value="1" checked="checked" id="updateprogress" name="updateprogress" /><label for="updateprogress">{{$LANG.update_progress}}</label>&nbsp;&nbsp;<span>{{$LANG.elapsed}}&nbsp;&nbsp;<input style="width:30px;" class="input_text" name="elapsedtime" id="elapsedtime" type="text">&nbsp;&nbsp;{{$LANG.hour}}{{if 0}}<select name="unit" id="unit"><option>小时</option></select>{{/if}}</span>&nbsp;&nbsp;<span>{{$LANG.title_progress}}<input type="text" class="input_text" id="percent" name="percent" value="{{if count($tudu.accepter) > 1}}{{$tudu.selfpercent|default:0}}{{else}}{{$tudu.percent|default:0}}{{/if}}%"  style="width:50px;" /></span><a href="javascript:void(0)" onclick="$('#percent').val('100%');" style="margin-left:10px">{{$LANG.percent_100}}</a></td>
              </tr>
        {{/if}}
              <tr>
                <td>
                        {{if $access.upload}}<span class="upload_btn"><span id="upload-btn"></span></span><span class="add"><span class="icon icon_add"></span><a href="javascript:void(0)" id="upload-link">{{$LANG.file_upload}}</a></span>{{/if}}
                        <span class="add"><span class="icon icon_photo"></span> <a href="javascript:void(0)" id="insert-pic">{{$LANG.picture}}</a></span>
                        {{if 0}}
                        <span class="add"><span class="icon icon_screen"></span><a class="">截屏</a></span></span>

                        <span class="font"><span class="icon icon_font"></span><a class="">文字格式</a><span class="icon icon_down"></span></span>
                        {{/if}}
                </td>
              </tr>
              <tr>
                <td style="padding:0 5px">
                <div id="attach-list" class="info_box att_container" style="display:none;margin-right:0">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="bd_upload">

                        </td>
                      </tr>
                    </table>
                </div>
                </td>
              </tr>
              <tr>
                <td><textarea class="form_textarea" name="content" id="content" cols="" rows=""></textarea></td>
              </tr>
              <tr>
                <td>
                <button class="btn_big" style="width:90px;" type="submit">{{$LANG.reply}}</button>
                <div style="float:right;line-height:24px"><a id="link-fullreply" href="/foreign/tudu/post?tid={{$tudu.tuduid}}&fid={{$user.uniqueid}}&ts={{$tsid}}" />{{$LANG.full_reply_mode}}</a></div>
                </td>
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
    <div class="dialog-item" style="text-align:right;padding-right:25px"><button type="button" name="upload">{{$LANG.upload}}</button> <button type="button" name="cancel">{{$LANG.cancel}}</button></div>
    </div>
    <div class="tab-body" id="tb-url" style="display:none">
    <div class="dialog-item"><span class="gray">{{$LANG.network_pic_hint}}</span></div>
    <div class="dialog-item">{{$LANG.pic_url}}{{$LANG.cln}}<input type="text" class="input_text" style="width:220px" name="url" id="picurl" value="http://" /></div>
    <div class="dialog-item" style="text-align:right;padding-right:25px"><button type="button" name="confirm">{{$LANG.confirm}}</button> <button type="button" name="cancel">{{$LANG.cancel}}</button></div>
    </div>
</div>
</div>

<div style="display:none">
<table id="label-tpl" cellspacing="0" cellpadding="0" class="flagbg"><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr><tr><td class="falg_line"></td><td class="tag_txt"></td><td class="tag_close"></td><td class="falg_line"></td></tr><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr></table>
</div>

<script type="text/javascript">
<!--
_TUDU_ID = '{{$tudu.tuduid}}';
_BACK = '{{$back}}';
var _CUS_LABELS = {};
{{foreach item=item from=$labels name=label}}{{if !$item.issystem}}_CUS_LABELS['{{$item.labelid}}'] = {name: '{{$item.labelalias}}', bgcolor: '{{$item.bgcolor}}'};{{/if}}{{/foreach}}

$(function(){
	initReplyEditor();

    $('.todo_info a').attr('target', '_blank');

    $('#percent').stepper({step: 25, max:100, format: 'percent'});

    {{if $access.reply}}
    initPicInsert('#insert-pic'{{if $access.upload}}, {
    	uploadUrl: '{{$options.sites.file}}{{$upload.cgi.upload}}',
        postParams: {'cookies': '{{$cookies}}'},
        flashUrl: '{{$options.upload.cgi.swfupload}}',
        picurl: '/foreign/attachment/img?tid={{$tudu.tuduid}}&ts={{$tsid}}&fid={{$user.uniqueid}}',
        fileSizeLimit: '{{$uploadsizelimit}}',
        auth: '{{$upload.auth}}'
    }{{/if}});

    _UPLOAD = initAttachment({
        uploadUrl: '{{$options.sites.file}}{{$upload.cgi.upload}}',
        flashUrl: '{{$options.upload.cgi.swfupload}}',
        postParams: {'cookies': '{{$cookies}}'},
        fileSizeLimit: '{{$uploadsizelimit}}'
    }, $('#attach-list'), $('#attach-list td.bd_upload'));
    {{/if}}

    $('#updateprogress').click(function(){
        $('#elapsedtime, #unit, #percent').attr('disabled', !this.checked);
    });

    $('button[name="back"]').click(function(){
        location = '{{$query.back|default:'/tudu/?search=inbox'}}';
    });

    $('button[name="close"]').click(function(){
        window.close();
    });

    {{if $access.forward}}
    $('button[name="forward"]').click(function(){
        location = '/foreign/tudu/view?tid={{$tudu.tuduid}}&ts={{$tsid}}&fid={{$user.uniqueid}}&forward=1';
    });
    {{/if}}

    $('button[name="inbox"]').click(function(){
        inboxTudu('{{$tudu.tuduid}}', 'inbox');
    });

    {{if $access.agree}}
    $('button[name="agree"]').click(function(){
        reviewTudu('{{$tudu.tuduid}}', this.name, '{{$user.uniqueid}}', '{{$tsid}}');
    });
    {{/if}}

    {{if $access.disagree}}
    $('button[name="disagree"]').click(function(){
    	reviewTudu('{{$tudu.tuduid}}', this.name, '{{$user.uniqueid}}', '{{$tsid}}');
    });
    {{/if}}

    {{if $access.claim}}
    $('button[name="claim"]').click(function(){
        claimTudu('{{$tudu.tuduid}}', '{{$user.uniqueid}}', '{{$tsid}}');
    });
    {{/if}}

    $('button[name="reply"]').click(function(){
        goReply('{{$tudu.tuduid}}', '{{$tsid}}', '{{$user.uniqueid}}');
    });

    {{if $access.reject}}
    $('button[name="reject"]').click(function(){
        if (!confirm(TOP.TEXT.CONFIRM_REJECT_TUDU)) {
            return ;
        }

        state('{{$tudu.tuduid}}', this.name, {
            fid: '{{$user.uniqueid}}',
            ts: '{{$tsid}}'
        });
    });
    {{/if}}
    {{if $access.accept}}
    $('button[name="accept"]').click(function(){
        state('{{$tudu.tuduid}}', this.name, {
            fid: '{{$user.uniqueid}}',
            ts: '{{$tsid}}'
        });
    });
    {{/if}}

    {{if $last}}
    window.scrollTo(0,$('div.todo_expand:last').offset().top);
    {{/if}}

    $('#replyform').submit(function(){return false;});
    $('#replyform').submit(function(){replySubmit(this);});

    $('#link-fullreply').click(function(){
        if (editorCheckNull()) {
            var content = _REPLY_EDITOR.getSource(),
                form = $('<form method="post"><textarea name="content" style="display:none"></textarea></form>');

            form.attr('action', this.href).find('textarea[name="content"]').val(content);
            form.appendTo(document.body).submit();
            return false;
        }
    });

    ajustReplyContent();

    new FixToolbar({
        src: '#toolbar',
        target: '#float-toolbar'
    });
});

function addfav() {
    var url   = '{{$options.sites.tudu}}{{$smarty.server.REQUEST_URI}}',
        title = '{{$tudu.subject}}';

    if (window.sidebar) {
        window.sidebar.addPanel(title,url,"");
    } else if ( document.all) {
        window.external.AddFavorite(url,title);
    }
}
-->
</script>
</body>
</html>
