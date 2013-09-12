<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.notice}}</title>
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
LH = 'm=compose{{if $tudu.tuduid}}&tid={{$tudu.tuduid}}{{else}}&type=notice{{/if}}';
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
<script src="{{$options.sites.static}}/js/compose.js?1044" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/plugins.js?1004" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/boardselector.js?1003" type="text/javascript"></script>

</head>
<body style="padding:0 5px 5px">
<form action="/compose-tudu/send" id="theform" method="post" class="tab_panel">
<input type="hidden" id="action" name="action" value="{{$action}}" />
<input type="hidden" id="type" name="type" value="notice" />
<input type="hidden" id="issend" name="issend" value="1" />
<input type="hidden" id="ftid" name="ftid" value="{{$tudu.tuduid}}" />
<input type="hidden" id="classname" name="classname" value="{{$tudu.classname}}" />
<input type="hidden" id="cid" value="{{$tudu.classid}}" />
    {{include file="compose^tab.tpl" tab="notice"}}
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
            <div class="readmailinfo" style="padding-top:5px;">
            	<div class="info_box">
            	  <table cellspacing="0" cellpadding="0">
	                <tr>
	                  <td class="info_txt">&nbsp;</td>
	                  {{strip}}
	                  <td class="info_forms">
	                    {{if $reviewer || $tudu.isdraft || !$tudu.tuduid}}<a href="javascript:void(0)" class="expand-link" id="add-reviewer" style="margin-left:0;">{{$LANG.add_review}}</a>|{{/if}}
	                    <a href="javascript:void(0)" class="expand-link" id="add-bcc">{{$LANG.add_bcc}}</a>|
	                    <a href="javascript:void(0)" class="expand-link" id="add-toptime">{{$LANG.add_toptime}}</a>
	                  </td>
	                  {{/strip}}
	                </tr>
		          </table>
            	</div>
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
                        {{if !$board || ($board && !$board.isclassify)}}<option value="">--{{$LANG.none}}--</option>{{/if}}
                        {{if $classes}}
                        {{foreach from=$classes item=class}}
                        <option value="{{$class.classid}}"{{if $tudu.classid === $class.classid}} selected="selected"{{/if}} title="{{$class.classname}}">{{$class.classname}}</option>
                        {{/foreach}}
                        {{/if}}
                        </select></td>
                        <td class="info_forms info_input"><input class="input_text" name="subject" id="subject" type="text" value="{{$tudu.subject|escape:'html'}}" tabindex="1"{{if $board.protect}} readonly="true"{{/if}} maxlength="50" /></td>
                      </tr>
                    </table>

                    <table id="row-reviewer" cellspacing="0" cellpadding="0"{{if !$reviewer}} style="display:none"{{/if}}>
			          <tr>
			            <td class="info_txt"><a href="javascript:void(0)" id="select-reviewer">{{$LANG.reviewer}}</a></td>
			            <td class="info_forms" style="padding-right:10px;*"><input id="i-reviewer" tabindex="2" class="input_text" type="text" readonly="readonly" value="" /><input type="hidden" name="reviewer" id="reviewer" value="{{foreach item=item key=key from=$reviewer}}{{$item.userinfo|cat:"\n"}}{{/foreach}}" /></td>
			          </tr>
			        </table>

                    <table id="row-cc" cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"><a href="javascript:void(0)" id="select-cc">{{$LANG.accept_user}}</a></td>
                        <td class="info_forms info_input" style="padding-right:10px"><input id="i-cc" tabindex="2" class="input_text" type="text" readonly="readonly" value="{{foreach from=$tudu.cc item=cc name=cc}}{{if !$smarty.foreach.cc.first}};{{/if}}{{$cc.0}}{{/foreach}}" /><input type="hidden" name="cc" id="cc" value="{{foreach item=item key=key from=$tudu.cc}}{{$item.3|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}" /></td>
                      </tr>
                    </table>
                    <table id="row-bcc" cellspacing="0" cellpadding="0"{{if !$tudu.bcc}} style="display:none"{{/if}}>
			          <tr>
			            <td class="info_txt"><a href="javascript:void(0)" id="select-bcc">{{$LANG.bcc}}</a></td>
			            <td class="info_forms" style="padding-right:10px;"><input id="i-bcc" tabindex="4" class="input_text" type="text" readonly="readonly" value="{{foreach from=$tudu.bcc item=bcc name=bcc}}{{if !$smarty.foreach.bcc.first}};{{/if}}{{$bcc.0}}{{/foreach}}" /><input type="hidden" name="bcc" id="bcc" value="{{foreach item=item key=key from=$tudu.bcc}}{{$item.3|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}" /></td>
			          </tr>
			        </table>
                    <table id="row-toptime" cellspacing="0" cellpadding="0"{{if !$tudu.endtime}} style="display:none"{{/if}}>
                      <tr>
                        <td class="info_txt"><span id="toptime">{{$LANG.toptime}}</span></td>
                        <td class="info_forms"><input type="text" tabindex="6" class="input_text" name="endtime" id="endtime" readonly="readonly" value="{{$tudu.endtime|date_format:'%Y-%m-%d'}}" style="width:178px;" /></td>
                      </tr>
                    </table>
                </div>
                <div class="info_box">
                    <div class="attlist">
                        <span class="add"><span class="icon icon_tpl"></span><a href="javascript:void(0)" name="tpllist" _textarea="content">{{$LANG.add_tpl_list}}</a></span>
                        {{if $access.upload}}<span class="upload_btn"><span id="upload-btn"></span></span><span class="add"><span class="icon icon_add"></span><a href="javascript:void(0)" id="upload-link">{{$LANG.file_upload}}</a></span>{{/if}}
                        {{if $access.upload && $user.maxndquota > 0}}<span class="add" id="netdisk-btn"><span class="icon icon_nd_attach"></span><a href="javascript:void(0)">{{$LANG.netdisk_attach}}</a></span>{{/if}}
                        <span class="add" id="screencp-btn"><span class="icon icon_screencp"></span><a href="javascript:void(0)" id="link-capture">{{$LANG.screencapture}}</a></span>
                        <span class="add" id="pic-btn"><span class="icon icon_photo"></span> <a href="javascript:void(0)" id="insert-pic">{{$LANG.picture}}</a></span>
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
                <div class="info_box">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.content}}</td>
                        <td class="info_forms info_input"><textarea id="content" cols="" rows="" style="width:100%;height:180px">{{$tudu.content|tudu_format_content|escape:'html'}}</textarea><textarea id="postcontent" name="content" style="display:none;"></textarea></td>
                      </tr>
                    </table>
                </div>
                <div class="info_box">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"></td>
                        <td class="info_forms">
                            <input name="notifyall" type="checkbox" value="1" id="notifyall"{{if $tudu.notifyall}} checked="checked"{{/if}}></input><label for="notifyall" title="{{$LANG.notice_notifyall_tips}}">{{$LANG.mobile_remind}}</label>&nbsp;&nbsp;<label for="privacy" title="{{$LANG.private_notice_tips}}"><input type="checkbox" name="privacy" value="1" id="privacy"{{if $tudu.privacy}} checked="checked"{{/if}} />{{$LANG.private_notice}}</label>&nbsp;&nbsp;<label for="isauth" title="{{$LANG.auth_tips}}"><input type="checkbox" name="isauth" value="1" id="isauth"{{if $tudu.isauth}} checked="checked"{{/if}} />{{$LANG.foreign_auth}}</label>
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
{{include file="compose^newwin_fix.tpl" type="notice"}}
{{/if}}

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
<script type="text/javascript">
{{if $newwin}}
var _NEW_WIN = 1;
{{/if}}
$(function(){
	var tuduId = '{{$tudu.tuduid}}';
    TOP.Frame.title('{{$LANG.notice}}');
    TOP.Label.focusLabel();
    {{if !$newwin}}
    TOP.Frame.hash(LH);
    {{/if}}

    $('button[name="back"]').click(function() {
    	location = '{{$back|default:'/tudu/view?tid=tuduId'}}';
    });

    var h = $(window).height(),
    ch = $(document.body).height();

    var editorHeight = Math.max($('#content').height() + (h - ch - 15), 200);
    var editorDisabled = {{if $board && $board.protect && $tudu && !$tudu.isdraft}}true{{else}}false{{/if}};
    _EDITOR = initEditor('content', {initialFrameHeight: editorHeight}, function(){
        if (editorDisabled) this.disable();
        if (!this.hasContents()) {
        {{if $user.option.settings.fontfamily}}
        this.setContent('<p style="font-family:{{$user.option.settings.fontfamily}};font-size:{{$user.option.settings.fontsize|default:'12px'}}"></p>');
        {{/if}}
        }
        
        this.commands['send'] = {
            execCommand: function() {
                composeSubmit('#theform');
            }
        };
        this.addshortcutkey({
            "send": "ctrl+13"
        });
    });

	$('a[name="tpllist"]').click(function(e) {
        var boardId = boardSelect.getValue();
        e.srcElement = $(this).parent('span.add')[0];
    	Tudu.Template.showMenu(e, _EDITOR, boardId);
     	TOP.stopEventBuddle(e);
    });

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
        boards: {{$boards|@json_encode}}
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

    var bccInput = new TOP.ContactInput({
        id: 'bcc-input', target: $('#i-bcc'), valuePlace: $('#bcc'), group: true, jq: jQuery
    });
	initSelectLink('#select-bcc', bccInput, $('#bcc'), true);

	var reviewInput = new TOP.ContactInput({
        id: 'review-input', target: $('#i-reviewer'), valuePlace: $('#reviewer'), group: false, contact: false, jq: jQuery, review:true
    });
	initSelectLink('#select-reviewer', reviewInput, $('#reviewer'), false, true);

    $('#endtime').datepick({
        minDate: new Date(),
        showOtherMonths: true,
        selectOtherMonths: true,
        firstDay: 0,
        showAnim: 'slideDown',
        showSpeed: 'fast'
    });

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

    $('#theform').submit(function(){return false;});

    initUnloadEvent('#theform'{{if !$tudu.tuduid || $tudu.isdraft}}, true{{/if}});
    {{if $tudu && (!$tudu.tuduid || $tudu.isdraft)}}_FORM_DATA = {};{{/if}}

    $('button[name="preview"]').click(function(){
        getFormPreview('#theform', '/tudu/preview', '_blank');
    });
    $('div.tab-panel-header a[href^="/tudu/modify"]').click(function(){
    	getFormPreview('#theform', this.href, '_self');
    	return false;
    });

    $('#classid').change(function(){
        var o = $(this);
        if (o.val()) {
            $('#classname').val($(this).find('option:selected').text());
        } else {
            $('#classname').val('');
        }
    });

    var filedialog = null;
    $('#netdisk-btn').click(function(){
        if (filedialog === null) {
            filedialog = new FileDialog({id: 'netdisk-dialog'});
        }

        filedialog.show();
    });

    if (!TOP.Browser.isIE && !TOP.Browser.isFF) {
		$('#screencp-btn').remove();
	} else {
		var capturer = Capturer.setUploadUrl('{{$options.sites.file}}{{$upload.cgi.upload}}');
        Capturer.setEditor(_EDITOR);
        $('#link-capture').bind('click', function(){
            if (!Capturer.getCapturer()) {
                return Capturer.install();
            }
            Capturer.startCapture();
        });
	}

    {{if $tudu.isdraft || !$tudu.tuduid}}
    autosaveTudu = new Tudu.AutoSave({
    	form: $('#theform'),
    	time: 30000,
    	func: Tudu.TuduSubmit,
    	forcesave: {{$tudu.autosave|default:0}}
    });
    {{/if}}

    clearCast();

    var IS_TOPTIME = false, IS_BCC = false, IS_REVIEW = false;
    {{if !$tudu.tuduid || $tudu.isdraft}}
    IS_TOPTIME = TOP.Cookie.get('NOTICE-EXP-TOPTIME');

    IS_TOPTIME = IS_TOPTIME === null || IS_TOPTIME == 1;

    if (IS_TOPTIME) $('#row-toptime').show();
    {{/if}}

    var tools = {
        bcc: {{if $tudu.bcc}}true{{else}}IS_BCC{{/if}},
        toptime: {{if $tudu.endtime}}true{{else}}IS_TOPTIME{{/if}},
        review: {{if $reviewer}}true{{else}}IS_REVIEW{{/if}}
    };
    var expand = {
		bcc: false,
		toptime: false,
		review: false
	};

    if (tools.toptime) {
		$('#add-toptime').text(TOP.TEXT.DELETE_TOPTIME);
		expand.toptime = true;
	}
    if (tools.review) {
		$('#add-reviewer').text(TOP.TEXT.DELETE_REVIEW);
		expand.review = true;
	}
    if (tools.bcc) {
		$('#add-bcc').text(TOP.TEXT.DELETE_BCC);
		expand.bcc = true;
	}

    {{*
    {{if !$tudu.isdraft && $tudu.tuduid}}
    $('#add-reviewer').addClass('disabled');
    $('#select-reviewer').addClass('disabled');
    $('#select-reviewer').unbind('click');
    reviewInput.disabled();
    {{/if}}
    *}}

    // 事件
	$('#add-bcc').bind('click', function(){
		if ($(this).hasClass('disabled')) {
			return ;
		}

		toggleBcc();
	});
	$('#add-toptime').bind('click', function(){
		if ($(this).hasClass('disabled')) {
			return ;
		}
		toggleToptime();
	});
	$('#add-reviewer').bind('click', function(){
		if ($(this).hasClass('disabled')) {
			return ;
		}
		toggleReview();
	});

	function toggleBcc() {
		if (!expand.bcc) {
			$('#bcc, #i-bcc').attr('disabled', false);
			$('#row-bcc').show();
			$('#add-bcc').text(TOP.TEXT.DELETE_BCC);
		} else {
			$('#bcc, #i-bcc').attr('disabled', true);
			$('#row-bcc').hide();
			$('#add-bcc').text(TOP.TEXT.ADD_BCC);
		}

		expand.bcc = !expand.bcc;
		TOP.Cookie.set('NOTICE-EXP-BCC', expand.bcc ? 1 : 0, {expires: 86400000 * 365});
	}
	function toggleToptime() {
		if (!expand.toptime) {
			$('#toptime').attr('disabled', false);
			$('#row-toptime').show();
			$('#add-toptime').text(TOP.TEXT.DELETE_TOPTIME);
		} else {
			$('#toptime').attr('disabled', true);
			$('#row-toptime').hide();
			$('#add-toptime').text(TOP.TEXT.ADD_TOPTIME);
		}

		expand.toptime = !expand.toptime;
		TOP.Cookie.set('NOTICE-EXP-TOPTIME', expand.toptime ? 1 : 0, {expires: 86400000 * 365});
	}
	function toggleReview() {
		if (!expand.review) {
			$('#reviewer, #i-reviewer').attr('disabled', false);
			$('#row-reviewer').show();
			$('#add-reviewer').text(TOP.TEXT.DELETE_REVIEW);
		} else {
			$('#reviewer, #i-reviewer').attr('disabled', true);
			$('#row-reviewer').hide();
			$('#add-reviewer').text(TOP.TEXT.ADD_REVIEW);
		}

		expand.review = !expand.review;
	}
});
</script>

</html>
