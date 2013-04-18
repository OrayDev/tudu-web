<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.discuss}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
{{if $newwin}}
{{include file="^newwin.tpl"}}
{{else}}
<script type="text/javascript">
<!--
var LH = '';
{{if !$tudu.isdraft}}
LH = 'm=compose{{if $tudu.tuduid}}&tid={{$tudu.tuduid}}{{else}}&type=discuss{{/if}}{{if $reopen}}&reopen=1{{/if}}';
{{else}}
LH = 'm=view&tid={{$tudu.tuduid}}';
{{/if}}

if (top == this) {
	location = '/frame#' + LH;
}
-->
</script>
{{/if}}
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick-zh-cn.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/upload.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/compose.js?1038" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/plugins.js?1004" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/boardselector.js?1003" type="text/javascript"></script>

</head>
<body style="padding:0 5px 5px">
<form action="/compose/send" id="theform" method="post" class="tab_panel">
<input type="hidden" id="action" name="action" value="{{$action}}" />
<input type="hidden" id="type" name="type" value="discuss" />
<input type="hidden" id="issend" name="issend" value="1" />
<input type="hidden" id="ftid" name="ftid" value="{{$tudu.tuduid}}" />
<input type="hidden" id="classname" name="classname" value="{{$tudu.classname}}" />
<input type="hidden" id="cid" value="{{$tudu.classid}}" />
{{if $reopen}}<input type="hidden" name="isclose" value="0" />{{/if}}
    {{include file="compose^tab.tpl" tab="discuss"}}
    <div class="tab-panel-body">
        <div class="toolbar">
            {{strip}}
            <div>
            	<button class="btn" type="button" name="send">{{$LANG.send}}</button>
            	{{if $tudu.tuduid && !$tudu.isdraft}}
                <button class="btn" type="button" name="back">{{$LANG.back}}</button>
                {{/if}}
            	{{if 0}}<button class="btn" type="button">{{$LANG.send_on_time}}</button>{{/if}}
            	{{if !$tudu.tuduid || $tudu.isdraft}}
            	<button class="btn" type="button" name="save">{{$LANG.save_draft}}</button>
            	<button class="btn" type="button" name="preview">{{$LANG.preview}}</button>
            	{{/if}}
            	<span class="compose_msg"></span>
            </div>
            {{/strip}}
        </div>
            <div class="readmailinfo">
                <div class="info_box">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.belong_board}}</td>
                        <td class="info_forms info_input">
                        <input id="board-input" type="text" class="input_text" tabindex="1"{{if $tudu.tuduid && !$tudu.isdraft}} disabled="disabled" _disabled="true"{{/if}} value="{{$tudu.boardname}}" title="{{$LANG.select_board}}" />
                        <input type="hidden" id="bid" name="bid" value="{{$tudu.boardid}}" />
                        </td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.subject}}</td>
                        <td width="90" id="class-td"{{if !$tudu.classid && !$classes}} style="display:none"{{/if}}><select style="width:90px;" name="classid" id="classid"{{if $tudu.classid && ($board.protect || $isforward)}} disabled="disabled"{{/if}}>
                        {{if $board && !$board.isclassify}}<option value="">--{{$LANG.none}}--</option>{{/if}}
                        {{if $classes}}
                        {{foreach from=$classes item=class}}
                        <option value="{{$class.classid}}"{{if $tudu.classid === $class.classid}} selected="selected"{{/if}} title="{{$class.classname}}">{{$class.classname}}</option>
                        {{/foreach}}
                        {{/if}}
                        </select></td>
                        <td class="info_forms info_input"><input class="input_text" tabindex="1" name="subject" id="subject" type="text" value="{{$tudu.subject|escape:'html'}}"{{if $board.protect}} readonly="true"{{/if}} maxlength="50" /></td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"><a href="javascript:void(0)" id="select-cc">{{$LANG.accept_user}}</a></td>
                        <td class="info_forms info_input" style="padding-right:10px"><input id="i-cc" tabindex="2" class="input_text" type="text" readonly="readonly" value="{{foreach from=$tudu.cc item=cc name=cc}}{{if !$smarty.foreach.cc.first}};{{/if}}{{$cc.0}}{{/foreach}}" /><input type="hidden" name="cc" id="cc" value="{{foreach item=item key=key from=$tudu.cc}}{{$item.3|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}" /></td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.endtime}}</td>
                        <td class="info_forms"><input type="text" tabindex="3" class="input_text" name="endtime" id="endtime" readonly="readonly" value="{{$tudu.endtime|date_format:'%Y-%m-%d'}}" style="width:178px;" /></td>
                      </tr>
                    </table>
                </div>
                <div class="info_box">
                    <div class="attlist">
                        <span class="add"><span class="icon icon_tpl"></span><a href="javascript:void(0)" name="tpllist" _textarea="content">{{$LANG.add_tpl_list}}</a></span>
                        {{if $access.upload}}<span class="upload_btn"><span id="upload-btn"></span></span><span class="add"><span class="icon icon_add"></span><a href="javascript:void(0)" id="upload-link">{{$LANG.file_upload}}</a></span>{{/if}}
                        {{if $access.upload && $user.maxndquota > 0}}<span class="add" id="netdisk-btn"><span class="icon icon_nd_attach"></span><a href="javascript:void(0)">{{$LANG.netdisk_attach}}</a></span>{{/if}}
                        <span class="add" id="screencp-btn"><span class="icon icon_screencp"></span><a href="javascript:void(0)" id="link-capture">{{$LANG.screencapture}}</a></span>
                        <span class="add" id="pic-btn"><span class="icon icon_photo"></span> <a href="javascript:void(0)" id="insert-pic">{{$LANG.picture}}</a></span><span class="add"><span class="icon icon_vote"></span><a id="research" href="javascript:void(0)">{{$LANG.vote}}</a></span>
                        {{if 0}}<span class="font"><span class="icon icon_font"></span><a class="">文字格式</a><span class="icon icon_down"></span></span>{{/if}}
                    </div>
                </div>
                <div id="attach-list" class="info_box att_container"{{if $tudu.attachnum <= 0}} style="display:none"{{/if}}>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"></td>
                        <td class="bd_upload">
                            {{foreach item=file from=$tudu.attachments}}
                            <div class="filecell" id="attach-{{$file.fileid}}">
                            <input type="hidden" name="attach[]" value="{{$file.fileid}}" />
                            <div class="attsep">
                            <div class="attsep_file">
                            <span class="icon icon_add"></span><span class="filename">{{$file.filename}}</span>&nbsp;<span class="filesize">({{if $file.size < 1024}}{{$file.size}}bytes{{else}}{{math|round equation="x/1024" x=$file.size}}KB{{/if}})</span></div>
                            <div class="attsep_del"><a href="javascript:void(0)" name="delete" onClick="removeAttach('{{$file.fileid}}');">{{$LANG.delete}}</a></div><div class="clear"></div></div>
                            </div>
                            {{/foreach}}
                            {{if $ndfile}}
                            {{foreach item=file from=$ndfile}}
                            <div class="filecell" id="attach-{{$file.fileid}}">
                            <input type="hidden" name="attach[]" value="{{$file.fileid}}" />
                            <input type="hidden" name="nd-attach[]" value="{{$file.fileid}}" />
                            <div class="attsep">
                            <div class="attsep_file">
                            <span class="icon icon_add"></span><span class="filename">{{$file.filename}}</span>&nbsp;<span class="filesize">({{if $file.size < 1024}}{{$file.size}}bytes{{else}}{{math|round equation="x/1024" x=$file.size}}KB{{/if}})</span></div>
                            <div class="attsep_del"><a href="javascript:void(0)" name="delete" onClick="removeAttach('{{$file.fileid}}');">{{$LANG.delete}}</a></div><div class="clear"></div></div>
                            </div>
                            {{/foreach}}
                            {{/if}}
                        </td>
                      </tr>
                    </table>
                </div>
                <div class="info_box" id="vote-panel"{{if !$votes}} style="display:none"{{/if}}>
                <input type="hidden" name="vote" value="{{if $votes}}1{{else}}0{{/if}}" />
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"></td>
                        <td class="info_forms">
                            <div>
                                <div class="content_box3" id="vote-list">
                                    <button id="add-vote" type="button">{{$LANG.add_vote}}</button>
                                    {{foreach item=vote from=$votes}}
                                    <div id="vote-{{$vote.voteid}}" class="vote_box" style="margin-top:10px;position: relative;zoom:1;">
                                        <input name="votemember[]" type="hidden" value="{{$vote.voteid}}" />
                                        <input name="voteid-{{$vote.voteid}}" type="hidden" value="{{$vote.voteid}}" />
                                        <input name="voteorder-{{$vote.voteid}}" type="hidden" value="{{$vote.ordernum}}" />
                                        <strong>{{$LANG.vote_now}}<span class="vote-sort"></span></strong><a class="step_box_item_close" href="javascript:void(0);" style="top:6px;" onClick="Tudu.initVote.removeVote('{{$vote.voteid}}');"></a>
                                        <div class="line_bold"></div>
                                        <p style="line-height: 25px;"><strong>{{$LANG.vote_subject}}</strong>&nbsp;<input class="input_text" name="title-{{$vote.voteid}}" type="text" maxlength="30" style="width:375px" value="{{$vote.title}}" /></p>
                                        <p style="line-height: 25px;"><strong>{{$LANG.vote_option}}</strong>&nbsp;<span class="gray">({{$LANG.option_tips}})</span></p>
                                        <div class="vote-option-box" style="width:500px;float:left">
                                            <table cellspacing="0" cellpadding="0" class="setting_table" style="width:500px">
                                            <colgroup>
                                                <col width="90" />
                                                <col />
                                            </colgroup>
                                            <tr>
                                                <td valign="top" align="center">{{$LANG.order_num}}</td>
                                                <td>{{$LANG.option_content}}</td>
                                            </tr>
                                            <tbody id="option-list-{{$vote.voteid}}">
                                            {{foreach item=option from=$vote.options}}
                                            <tr id="option-{{$vote.voteid}}-{{$option.optionid}}">
                                                <td valign="top" align="right"><input type="hidden" name="optionid-{{$vote.voteid}}[]" value="{{$option.optionid}}" /><input class="input_text" name="ordernum-{{$vote.voteid}}-{{$option.optionid}}" value="{{$option.ordernum|default:0}}" style="width:60px" type="text" /></td>
                                                <td><input class="input_text" name="text-{{$vote.voteid}}-{{$option.optionid}}" type="text" maxlength="200" style="width:350px" value="{{$option.text}}" /><a class="icon icon_close_g remove_option" href="javascript:void(0);" onClick="Tudu.initVote.removeOption('{{$vote.voteid}}', '{{$option.optionid}}');"></a></td>
                                            </tr>
                                            {{/foreach}}
                                            </tbody>
                                            <tr>
                                                <td style="padding-left:16px"><span class="font_c">+</span>&nbsp;<a href="javascript:void(0);" onClick="Tudu.initVote.addOption('{{$vote.voteid}}', 1);">{{$LANG.add_option}}</a></td>
                                                <td></td>
                                            </tr>
                                            </table>
                                        </div>
                                        <div style="margin-left: 500px;border-left:1px solid #ccc;padding-left: 20px;">
                                            <table cellspacing="0" cellpadding="0" class="setting_table" style="width:auto;margin-bottom:0;">
                                              <tr>
                                                <td valign="top">{{$LANG.vote_configs}}{{$LANG.cln}} <input style="width:30px;" class="input_text" value="{{$vote.maxchoices}}" name="maxchoices-{{$vote.voteid}}" type="text" /><span class="gray">{{$LANG.max_choice_tips}}</span></td>
                                              </tr>
                                              <tr>
                                                <td valign="top"><label><input name="visible-{{$vote.voteid}}" type="checkbox" value="1"{{if $vote.visible}} checked="checked"{{/if}} />{{$LANG.after_view_vote_result}}</label></td>
                                              </tr>
                                              <tr>
                                                <td valign="top"><div style="float:left;"><label><input name="privacy-{{$vote.voteid}}" type="checkbox" value="1"{{if $vote.privacy}} checked="checked"{{/if}} onClick="Tudu.initVote.toggleAnonymous('{{$vote.voteid}}');" />{{$LANG.open_vote_voter}}</label></div><div style="margin-left: 140px;{{if $vote.privacy}}display:none;{{/if}}"><label><input name="anonymous-{{$vote.voteid}}" type="checkbox" value="1"{{if $vote.anonymous}} checked="checked"{{/if}} />{{$LANG.vote_anonymous}}</label></div></td>
                                              </tr>
                                              <tr>
                                                <td valign="top"><label><input name="isreset-{{$vote.voteid}}" type="checkbox" value="1"{{if $vote.isreset}} checked="checked"{{/if}} />{{$LANG.reset_vote_tips}}</label></td>
                                              </tr>
                                            </table>
                                        </div>
                                        <div style="clear:both;font-size:0;line-height:0;height:0"></div>
                                    </div>
                                    {{/foreach}}
                                </div>
                            </div>
                        </td>
                      </tr>
                    </table>
                    </div>
                <div class="info_box">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.content}}</td>
                        <td class="info_forms info_input"><textarea class="form_textarea" id="content" cols="" rows="">{{$tudu.content|tudu_format_content|escape:'html'}}</textarea><textarea id="postcontent" name="content" style="display:none;"></textarea></td>
                      </tr>
                    </table>
                </div>
                <div class="info_box">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"></td>
                        <td class="info_forms">
                            <input name="notifyall" type="checkbox" value="1" id="notifyall"{{if $tudu.notifyall}} checked="checked"{{/if}}></input><label for="notifyall" title="{{$LANG.discuss_notifyall_tips}}">{{$LANG.post_remind}}</label>&nbsp;&nbsp;<label for="privacy" title="{{$LANG.private_discuss_tips}}"><input type="checkbox" name="privacy" value="1" id="privacy"{{if $tudu.privacy}} checked="checked"{{/if}} />{{$LANG.private_discuss}}</label>&nbsp;&nbsp;<label for="isauth" title="{{$LANG.auth_tips}}"><input type="checkbox" name="isauth" value="1" id="isauth"{{if $tudu.isauth}} checked="checked"{{/if}} />{{$LANG.foreign_auth}}</label>
                        </td>
                      </tr>
                    </table>
                </div>
            </div>
        <div class="toolbar">
            {{strip}}
            <div>
                <button class="btn" type="button" name="send">{{$LANG.send}}</button>
                {{if $tudu.tuduid && !$tudu.isdraft}}
                <button class="btn" type="button" name="back">{{$LANG.back}}</button>
                {{/if}}
                {{if 0}}<button class="btn" type="button">{{$LANG.send_on_time}}</button>{{/if}}
                {{if !$tudu.tuduid || $tudu.isdraft}}
                <button class="btn" type="button" name="save">{{$LANG.save_draft}}</button>
                <button class="btn" type="button" name="preview">{{$LANG.preview}}</button>
                {{/if}}
                <span class="compose_msg"></span>
            </div>
            {{/strip}}
        </div>
    </div>
</form>
{{if 0}}
{{include file="compose^newwin_fix.tpl" type="discuss"}}
{{/if}}

{{* 投票调查 *}}
<div style="display:none;">
    <table>
        <tr id="new-option-tpl">
            <td valign="top" align="right"><input type="hidden" name="newoption[]" value="" /><input class="input_text" name="ordernum" style="width:60px" type="text" /></td>
            <td><input class="input_text" name="text" type="text" maxlength="200" style="width:350px" /><a class="icon icon_close_g remove_option" href="javascript:void(0);"></a></td>
        </tr>
        <tr id="option-tpl">
            <td colspan="2"><input name="ordernum" value="" type="hidden" /><input type="hidden" name="newoption[]" value="" /><input class="input_text" name="text" type="text" maxlength="200" /><a class="icon icon_close_g remove_option" href="javascript:void(0);"></a></td>
        </tr>
    </table>
</div>
<div id="vote-tpl" class="vote_box" style="margin-top:10px;position: relative;display:none;">
    <input name="votemember[]" type="hidden" value="" />
    <input name="voteorder" type="hidden" value="" />
    <strong>{{$LANG.vote_now}}<span class="vote-sort"></span></strong><a class="step_box_item_close remove_vote" href="javascript:void(0);" style="top:6px;"></a>
    <div class="line_bold"></div>
    <p style="line-height: 25px;"><strong>{{$LANG.vote_subject}}</strong>&nbsp;<input class="input_text" name="title" type="text" maxlength="30" style="width:375px" value="" /></p>
    <p style="line-height: 25px;"><strong>{{$LANG.vote_option}}</strong>&nbsp;<span class="gray">({{$LANG.option_tips}})</span></p>
    <div class="vote-option-box" style="width:500px;float:left">
        <table cellspacing="0" cellpadding="0" class="setting_table" style="width:500px">
        <colgroup>
            <col width="90" />
            <col />
        </colgroup>
          <tr>
            <td colspan="2">{{$LANG.option_content}}</td>
          </tr>
          <tbody id="option-list">
          </tbody>
          <tr>
            <td style="padding-left:16px"><span class="font_c">+</span>&nbsp;<a id="option-add" href="javascript:void(0);">{{$LANG.add_option}}</a></td>
            <td></td>
          </tr>
        </table>
    </div>
    <div style="margin-left: 500px;border-left:1px solid #ccc;padding-left: 20px;">
        <table cellspacing="0" cellpadding="0" class="setting_table" style="width:auto;margin-bottom:0;">
          <tr>
            <td valign="top">{{$LANG.vote_configs}}{{$LANG.cln}} <input style="width:30px;" class="input_text" value="1" name="maxchoices" type="text" /><span class="gray">{{$LANG.max_choice_tips}}</span></td>
          </tr>
          <tr>
            <td valign="top"><label><input name="visible" type="checkbox" value="1" />{{$LANG.after_view_vote_result}}</label></td>
          </tr>
          <tr>
            <td valign="top"><div style="float:left;"><label><input name="privacy" type="checkbox" value="1" />{{$LANG.open_vote_voter}}</label></div><div style="margin-left: 140px;"><label><input name="anonymous" type="checkbox" value="1" />{{$LANG.vote_anonymous}}</label></div></td>

          </tr>
          <tr>
            <td valign="top"><label><input name="isreset" type="checkbox" value="1" />{{$LANG.reset_vote_tips}}</label></td>
          </tr>
        </table>
    </div>
    <div style="clear:both;font-size:0;line-height:0;height:0"></div>
</div>

{{* 图片 *}}
<div id="pic-modal" class="pic-modal" style="width:320px;display:none;">
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
</body>
<script src="{{$options.sites.static}}/js/tudu/vote.js" type="text/javascript"></script>
<script type="text/javascript">
{{if $newwin}}
var _NEW_WIN = 1;
{{/if}}
$(function(){
	var tuduId = '{{$tudu.tuduid}}';
    TOP.Frame.title('{{$LANG.discuss}}');
    TOP.Label.focusLabel();
    {{if !$newwin}}
    TOP.Frame.hash(LH);
    {{/if}}
	{{if $votes}}
	Tudu.initVote.updateVoteSort();
    Tudu.initVote.setOptionIndex();

    $('input[name^="maxchoices-"]').bind('keyup', function(){
        this.value = this.value.replace(/[^0-9]+/, '');
    })
    .blur(function(){
        $(this).val(this.value);
    });
	{{/if}}

	$('button[name="back"]').click(function() {
    	location = '{{$back|default:'/tudu/view?tid=tuduId'}}';
    });

    var h = $(window).height(),
        ch = $(document.body).height();

    var editorHeight = Math.max($('#content').height() + (h - ch - 15), 200);

    var editorCss = {};
    {{if $user.option.fontfamily || $user.option.fontsize}}
    editorCss = {
        'font-family':'{{$user.option.fontfamily|default:'SimSun'}}',
        'font-size':'{{$user.option.fontsize|default:'12px'}}'

    };
    {{else}}
    var editorCss = {};
    {{/if}}

    $('#content').css('height', editorHeight + 'px');
    _EDITOR = initEditor('content', editorCss, {{if $board && $board.protect && $tudu && !$tudu.isdraft}}true{{else}}false{{/if}});
    {{if $board.protect}}
    setTimeout(function(){_EDITOR.disabled();}, 500);
    {{/if}}

    initPicInsert('#insert-pic'{{if $access.upload}}, {
        postParams: {'cookies': '{{$cookies}}'},
        fileSizeLimit: '{{$uploadsizelimit}}',
        auth: '{{$upload.auth}}'
    }{{/if}});

    {{if $access.upload}}
    // 初始化上传
    _UPLOAD = initAttachment({
        uploadUrl: '{{$options.sites.file}}{{$upload.cgi.upload}}',
        postParams: {'cookies': '{{$cookies}}'},
        fileSizeLimit: '{{$uploadsizelimit}}'
    }, $('#attach-list'), $('#attach-list td.bd_upload'));
    {{/if}}

    TOP.keyhint('#board-input', 'black', true, document);
    boardSelect = new BoardSelector({
        input: $('#board-input'),
        name: 'bid',
        boards: {{$boards|@format_board_list}}
    });

    boardSelect.bind('select', function(){
        var item = this.getSelected(),
            bid  = item ? item.boardid : null;

        $('#class-td').hide();
    	if (item && item.isclassify) {
        	$('#classid').empty();
        } else {
            $('#classid').empty();
            $('#classid').prepend('<option value="">--{{$LANG.none}}--</option>');
        }
    	if (item && item.privacy) {
			TOP.showMessage(TOP.TEXT.TUDU_MODIFY_PRIVACY_TIPS, 3000, 'success');
		}
    	if (bid) {
        	loadClasses(bid, '#classid');
    	}
    });

	$('#classid').bind('change', function(){
		var items = $('option:selected', $(this));
		items.each(function(){
			if ($(this).val() == '^add-class') {
				var bid = boardSelect.getValue();
				createClass(bid);
			}
		});
	});

	if ($('#bid').val()) {
	    loadClasses($('#bid').val(), '#classid', $('#cid').val());
	    $('#cid').val('');
	}

    var ccInput = new TOP.ContactInput({
        id: 'cc-input', target: $('#i-cc'), valuePlace: $('#cc'), group: true, jq: jQuery
    });

    initSelectLink('#select-cc', ccInput, $('#cc'), true);

    _START_PICKER = $('#endtime').datepick({
        showOtherMonths: true,
        selectOtherMonths: true,
        showAnim: 'slideDown',
        showSpeed: 'fast',
        firstDay: 0
    });

    {{if $reopen}}
    var date = new Date(),
        month = date.getMonth().toString().length == 1 ? "0" + (date.getMonth() + 1).toString() : (date.getMonth() + 1).toString(),
    	day = date.getDate().toString().length == 1 ? "0" + date.getDate().toString() : date.getDate().toString();

    $('#endtime').val(date.getFullYear() + '-' + month + '-' + day);
    {{/if}}

    $('button[name="save"], button[name="send"]').click(function(){
        $('#action').val(this.name);
        var cc = ccInput.getItems(),
    		i = 0;

        cc.each(function (){
        	if ($(this).attr('title') == TOP.TEXT.INVALID_TO_USER) {
        		i++;
        	}
    	});
        if (i >0) {return TOP.showMessage(TOP.TEXT.TUDU_ACCEPTER_EMAIL_ERROR);}

        composeSubmit('#theform');
    });

    $('#research').click(function(){
    	{{if $votes}}
    	return false;
    	{{else}}
        var isvote = !$('#vote-panel:visible').size();
        $('input[name="vote"]').val(isvote ? 1 : 0);
        $('#vote-panel').css('display', isvote ? '' : 'none');
        var vl = $('#vote-list div.vote_box').size();
        if (isvote && vl <= 0) {
            Tudu.initVote.addVote();
        }
        {{/if}}
    });

    $('#add-vote').bind('click', function(){
    	Tudu.initVote.addVote();
    });

    $('#classid').change(function(){
        var o = $(this);
        if (o.val()) {
            $('#classname').val($(this).find('option:selected').text());
        } else {
            $('#classname').val('');
        }
    });

    if (!TOP.Browser.isIE && !TOP.Browser.isFF) {
		$('#screencp-btn').remove();
	} else {
		var capturer = Capturer.setUploadUrl('{{$options.sites.file|default:$options.sites.www}}{{$upload.cgi.upload}}{{if !$options.sites.file}}&cookies={{$cookies}}{{/if}}');
		Capturer.setEditor(_EDITOR);
		$('#link-capture').bind('click', function(){
			if (!Capturer.getCapturer()) {
                return Capturer.install();
            }
            Capturer.startCapture();
        });
	}

	$('button[name="preview"]').click(function(){
		getFormPreview('#theform', '/tudu/preview', '_blank');
	});
	$('div.tab-panel-header a[href^="/tudu/modify"]').click(function(){
		getFormPreview('#theform', this.href, '_self');
		return false;
	});

	$('a[name="tpllist"]').click(function(e) {
		var boardId = boardSelect.getValue();
		e.srcElement = $(this).parent('span.add')[0];
		Tudu.Template.showMenu(e, _EDITOR, boardId);
	 	TOP.stopEventBuddle(e);

	});

	var filedialog = null;
	$('#netdisk-btn').click(function(){
	    if (filedialog === null) {
	        filedialog = new FileDialog({id: 'netdisk-dialog'});
	    }

	    filedialog.show();
	});

    $('#theform').submit(function(){return false;});

    initUnloadEvent('#theform'{{if !$tudu.tuduid || $tudu.isdraft}}, true{{/if}});
    {{if $tudu && (!$tudu.tuduid || $tudu.isdraft)}}_FORM_DATA = {};{{/if}}

	{{if $tudu.isdraft || !$tudu.tuduid}}
	autosaveTudu = new Tudu.AutoSave({
		form: $('#theform'),
		time: 30000,
		func: Tudu.TuduSubmit,
		forcesave: {{$tudu.autosave|default:0}}
	});
	{{/if}}

	clearCast();
});
</script>
</html>
