<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.post}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
{{if $newwin}}
{{include file="^newwin.tpl"}}
{{/if}}
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/upload.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/compose.js?1038" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/plugins.js?1004" type="text/javascript"></script>
{{if !$newwin}}
<script type="text/javascript">
<!--
var LH = 'm=post&tid={{$tudu.tuduid}}&pid={{$post.postid}}';
if (top == this) {
	location = location.href + '&newwin=1';//'/frame#' + LH;
}
-->
</script>
{{/if}}
</head>
<body style="padding:0 5px 5px">
<form action="/compose/reply" id="theform" method="post" class="tab_panel">
<input type="hidden" id="action" name="action" value="{{if !$post.postid}}create{{else}}modify{{/if}}" />
<input type="hidden" id="type" name="type" value="" />
<input type="hidden" id="tid" name="tid" value="{{$tudu.tuduid}}" />
<input type="hidden" id="board" name="bid" value="{{$tudu.boardid}}" />
<input type="hidden" id="fpid" name="fpid" value="{{$post.postid}}" />
<input type="hidden" id="savetime" name="savetime" value="{{$post.savetime}}" />
<div class="tab-panel-header">
    <table cellspacing="0" cellpadding="0" class="composetab">
      <tr>
        <td><div class="composetab_sel on"><div><a href="javascript:void(0)">{{$LANG.post}}</a></div></div></td>
      </tr>
    </table>
</div>

<div class="tab-panel-body">
    <div class="toolbar">
        <div><button class="btn" type="button" name="send">{{$LANG.send}}</button><button class="btn" type="button" name="back">{{$LANG.back}}</button><span class="compose_msg"></span></div>
    </div>
    <div class="readmailinfo">
        <div class="info_box">
        {{if $access.progress && $tudu.selfaccepttime}}
        	<table cellspacing="0" cellpadding="0">
              <tr>
                <td class="info_txt"></td>
                <td class="info_forms"><span>{{$LANG.elapsed}}</span>&nbsp;&nbsp;<input style="width:30px;" class="input_text" name="elapsedtime" id="elapsedtime" type="text" value="{{$post.elapsedtime/3600}}" />&nbsp;&nbsp;{{$LANG.hour}}{{if 0}}<select name="unit" id="unit"><option>小时</option></select>{{/if}}&nbsp;&nbsp;<span>{{$LANG.title_progress}}</span><input type="text" class="input_text" tabindex="7" id="percent" name="percent" value="{{if $post.issend}}{{$post.percent|default:$tudu.selfpercent}}{{else}}{{$tudu.selfpercent|default:0}}{{/if}}%"  style="width:60px;" /><a href="javascript:void(0)" onclick="$('#percent').val('100%');" style="margin-left:10px">{{$LANG.percent_100}}</a></td>
              </tr>
           </table>
        {{/if}}
        </div>
        <div class="info_box">
            <div class="attlist">
                <span class="add"><span class="icon icon_tpl"></span><a href="javascript:void(0)" name="tpllist" _textarea="content">{{$LANG.add_tpl_list}}</a></span>
                {{if $access.upload}}<span class="upload_btn"><span id="upload-btn"></span></span><span class="add"><span class="icon icon_add"></span><a href="javascript:void(0)" id="upload-link">{{$LANG.file_upload}}</a></span>{{/if}}
                {{if $access.upload && $user.maxndquota > 0}}<span class="add" id="netdisk-btn"><span class="icon icon_nd_attach"></span><a href="javascript:void(0)">{{$LANG.netdisk_attach}}</a></span>{{/if}}
                <span class="add" id="screencp-btn"><span class="icon icon_screencp"></span><a href="javascript:void(0)" id="link-capture">{{$LANG.screencapture}}</a></span>
                <span class="add" class="pic-btn"><span class="icon icon_photo"></span> <a href="javascript:void(0)" id="insert-pic">{{$LANG.picture}}</a></span>
            </div>
        </div>
        <div id="attach-list" class="info_box att_container"{{if $post.attachnum <= 0}} style="display:none"{{/if}}>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td class="info_txt"></td>
                <td class="bd_upload">
                    {{foreach item=file from=$post.attachments}}
                    <div class="filecell" id="attach-{{$file.fileid}}">
                    <input type="hidden" name="attach[]" value="{{$file.fileid}}" />
                    <div class="attsep">
                    <div class="attsep_file">
                    <span class="icon icon_add"></span><span class="filename">{{$file.filename}}</span>&nbsp;<span class="filesize">({{if $file.size < 1024}}{{$file.size}}bytes{{else}}{{math|intval equation="x/1024" x=$file.size}}KB{{/if}})</span></div>
                    <div class="attsep_del"><a href="javascript:void(0)" name="delete" onClick="removeAttach('{{$file.fileid}}');">{{$LANG.delete}}</a></div><div class="clear"></div></div>
                    </div>
                    {{/foreach}}
                </td>
              </tr>
            </table>
        </div>
        <div class="info_box">
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td class="info_txt">{{$LANG.content}}</td>
                <td class="info_forms info_input"><textarea style="height:300px;" class="form_textarea" id="content" cols="" rows="">{{$post.content|tudu_format_content|escape:'html'}}</textarea><textarea id="postcontent" name="content" style="display:none;"></textarea></td>
              </tr>
            </table>
        </div>
    </div>
    <div class="toolbar">
        <div><button class="btn" type="button" name="send">{{$LANG.send}}</button><button class="btn" type="button" name="back">{{$LANG.back}}</button><span class="compose_msg"></span></div>
    </div>
</div>
</form>

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

TOP.Frame.title('{{$LANG.post}}');
TOP.Label.focusLabel();
{{if !$newwin}}TOP.Frame.hash(LH);{{/if}}

{{if $newwin}}var _NEW_WIN = 1;{{/if}}
var h = $(window).height(),
ch = $(document.body).height();
var editorHeight = Math.max($('#content').height() + (h - ch - 15), 200);

{{if $user.option.settings.fontfamily}}
var editorCss = {
    'font-family':'{{$user.option.settings.fontfamily}}',
    'font-size':'{{$user.option.settings.fontsize|default:'12px'}}'
};
{{else}}
var editorCss = {};
{{/if}}

$('#content').css('height', editorHeight + 'px');
_EDITOR = initEditor('content', editorCss, {{if $board && $board.protect && $tudu && !$tudu.isdraft}}true{{else}}false{{/if}});

initPicInsert('#insert-pic'{{if $access.upload}}, {
    postParams: {'cookies': '{{$cookies}}'},
    fileSizeLimit: '{{$uploadsizelimit}}',
    auth: '{{$upload.auth}}'
}{{/if}});

{{if $access.upload}}
//初始化上传
_UPLOAD = initAttachment({
    uploadUrl: '{{$options.sites.file}}{{$upload.cgi.upload}}',
    postParams: {'cookies': '{{$cookies}}'},
    fileSizeLimit: '{{$uploadsizelimit}}'
}, $('#attach-list'), $('#attach-list td.bd_upload'));

var filedialog = null;
$('#netdisk-btn').click(function(){
    if (filedialog === null) {
        filedialog = new FileDialog({id: 'netdisk-dialog'});
    }

    filedialog.show();
});
{{/if}}

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

{{if $post.savetime}}
$('span.compose_msg').html(TOP.TEXT.AUTOSAVE_TIPS_FIRST + '{{$post.savetime|date_format:'%H:%M'}}' + ' ( ' + '{{math|intval equation="(x-y)/60" x=$smarty.now y=$post.savetime}}' + TOP.TEXT.AUTOSAVE_TIPS_SECOND + ' )');
{{/if}}

$('button[name="back"]').click(function(){
	if ('{{$back}}') {
    	var tuduId = $('#tid').val();
    	var url = '{{$back|default:'/tudu/view?tid=tuduId'}}';
    	location = '{{$back|default:'/tudu/view?tid=tuduId'}}';
    	getForm($('#theform'), url);
	} else {
		history.back();
	}
});

$('button[name="send"]').click(function(){
	$('#type').val('reply');
	postSubmit('#theform');
});

$('a[name="tpllist"]').click(function(e) {
    var boardId = $('#board').val();
    e.srcElement = $(this).parent('span.add')[0];
	Tudu.Template.showMenu(e, _EDITOR, boardId);
 	TOP.stopEventBuddle(e);
});

$('#updateprogress').click(function(){
	$('#elapsedtime, #unit, #percent').attr('disabled', !this.checked);
});

$('#percent').stepper({step:25, max:100, format:'percent'});

$('#theform').submit(function(){return false;});

function getForm(form, address) {
	$('#postcontent').val(_EDITOR.getSource());
	var data = $(form).serializeArray();
	var form = $('<form action="'+address+'" method="post" style="display:none"></form>');
	for (var key in data) {
		form.append('<textarea name="' + data[key].name + '">' + data[key].value + '</textarea>');
	}
	form.appendTo(document.body).submit();
}

$(function(){
    form = $('#theform');
    _FORM_DATA = _getFormVal();

    $('button[name="save"], button[name="send"]').bind('click', function(){_FORM_DATA = _getFormVal()});
    form.bind('submit', function(){_FORM_DATA = _getFormVal()});

    TOP.getJQ()('a:not([href^="javascript:"])').bind('click', _leaveDialog);
    TOP.getJQ()('form').bind('submit', _leaveDialog);
    $('a:not([href^="javascript:"]):not(#link-fullreply):not(.xheButton)').bind('click', _leaveDialog);

    TOP.window.onbeforeunload = function() {
        if (!_checkForm()) {
            return TOP.TEXT.COMPOSE_EXIT_WITHOUT_SAVE;
        }
    };
    window.onunload = function(){
        TOP.getJQ()('a').unbind('click', _leaveDialog);
        TOP.getJQ()('form').unbind('submit', _leaveDialog);
        TOP.window.onbeforeunload = function(){};
    };

    function _getFormVal() {
        var r = {};
        form.find('select, textarea:not(#postcontent), input:not(#ftid, #fpid, #savetime, #type, #issend, #action, [name="file[]"], [type="checkbox"]), :checkbox:checked').each(function(){
            if (!this.name) {
                return ;
            }
            if (!this.value) {
                return ;
            }
            r[this.name] = this.value;
        });
        r['content'] = _EDITOR.getSource();
        return r;
    }

    function _compare(v1, v2) {
        var l1 = 0, l2 = 0, vl1, vl2;
        for (var k in v1) {
        	vl1 = v1[k].constructor == window.Array ? v1[k].join(',') : v1[k];
            vl2 = v2[k] && v2[k].constructor == window.Array ? v2[k].join(',') : v2[k];

            if (vl1 != vl2) {
                return false;
            }

            l1++;
        }

        for (var k in v2) {
            l2++;
        }

        return l1 == l2;
    }

    function _checkForm() {
        $('#content').val(_EDITOR.getSource());

        return _compare(_getFormVal(), _FORM_DATA);
    }

    function _leaveDialog(e) {
        if (_checkForm()) {
            return true;
        }

        var trigger = $(this);

        TOP.Label.focusLabel('');
        var d = TOP.Frame.Dialog.show({
            title: TOP.TEXT.LEAVE_HINT,
            body: '<p style="padding:15px 0;margin-left:-12px"><strong>' + TOP.TEXT.REPLY_EXIT_SAVE_HINT + '</strong></p>',
            buttons: [{
                   text: TOP.TEXT.SEND_REPLY,
                   cls: 'btn',
                   events: {click: function(){
            	        postSubmit('#theform', function(){
                            if (trigger[0].tagName.toLowerCase() == 'a') {
                               location = trigger.attr('href');
                            } else {
                               trigger.unbind('submit', _leaveDialog).submit();
                            }
                        });
            	        TOP.Frame.Dialog.close();
                   }}
               },
               {
                   text: TOP.TEXT.DISCARD,
                   cls: 'btn',
                   events: {click: function(){
                       if (trigger[0].tagName.toLowerCase() == 'a') {
                           location = trigger.attr('href');
                       } else {
                           trigger.unbind('submit', _leaveDialog).submit();
                       }
                       TOP.Frame.Dialog.close();
                   }}
               },
               {
                   text: TOP.TEXT.CANCEL,
                   cls: 'btn',
                   events: {click: function(){TOP.Frame.Dialog.close()}}
               }
            ]
        });

        return false;
    }

    {{if !$post.issend}}
	var autosavePost = new Tudu.AutoSave({
		form: $('#theform'),
		time: 30000,
		func: Tudu.PostSubmit,
		forcesave: {{if ($post.content || $post.attachments) && !$post.savetime}}1 {{else}}0{{/if}}
	});
	{{/if}}
});

</script>
</html>
