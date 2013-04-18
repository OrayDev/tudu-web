<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.post}}</title>
{{include file="foreign^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/xheditor-1.1.9/xheditor-zh-cn.min.js?1001" type="text/javascript"></script>
<script src="/static/js?f=lang&lang={{$user.option.language}}&ver=1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/all.js?2007" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/foreign/compose.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/upload.js?1007" type="text/javascript"></script>

</head>
<body style="padding:0 5px 5px">
{{include file="foreign^header.tpl" fav=true}}
<form action="/foreign/compose/reply" id="theform" method="post" class="tab_panel">
<input type="hidden" id="action" name="action" value="{{if !$post.postid}}create{{else}}modify{{/if}}" />
<input type="hidden" id="tid" name="tid" value="{{$tudu.tuduid}}" />
<input type="hidden" id="fpid" name="fpid" value="{{$post.postid}}" />
<input type="hidden" name="fid" value="{{$user.uniqueid}}" />
<input type="hidden" name="ts" value="{{$tsid}}" />
<div class="tab-panel-header">
    <table cellspacing="0" cellpadding="0" class="composetab">
      <tr>
        <td><div class="composetab_sel on"><div><a href="javascript:void(0)">{{$LANG.post}}</a></div></div></td>
      </tr>
    </table>
</div>
<div class="tab-panel-body">
    <div class="toolbar">
        <div><button class="btn" type="button" name="send">{{$LANG.send}}</button><button class="btn" type="button" name="back">{{$LANG.back}}</button></div>
    </div>
    <div class="readmailinfo">
        <div class="info_box">
        {{if $access.progress && $tudu.selfaccepttime}}
        	<table cellspacing="0" cellpadding="0">
              <tr>
                <td class="info_txt"></td>
                <td class="info_forms"><input type="checkbox" value="1" id="updateprogress" name="updateprogress"{{if !$post.postid}} checked="checked"{{/if}} /><label for="updateprogress">{{$LANG.update_progress}}</label>&nbsp;&nbsp;<span>{{$LANG.elapsed}}</span>&nbsp;&nbsp;<input style="width:30px;" class="input_text" name="elapsedtime" id="elapsedtime" type="text" value="{{$post.elapsedtime/3600}}" {{if $post.postid}} disabled="disabled"{{/if}}>&nbsp;&nbsp;{{$LANG.hour}}{{if 0}}<select name="unit" id="unit"><option>小时</option></select>{{/if}}&nbsp;&nbsp;<span>{{$LANG.title_progress}}</span><input type="text" class="input_text" tabindex="7" id="percent" name="percent" value="{{if count($tudu.accepter) > 0}}{{$tudu.selfpercent|default:0}}{{else}}{{$tudu.percent|default:0}}{{/if}}%"  style="width:60px;"{{if $post.postid}} disabled="disabled"{{/if}} /><a href="javascript:void(0)" onclick="$('#percent').val('100%');" style="margin-left:10px">{{$LANG.percent_100}}</a></td>
              </tr>
           </table>
        {{/if}}
        </div>
        <div class="info_box">
            <div class="attlist">
                {{if $access.upload}}<span class="upload_btn"><span id="upload-btn"></span></span><span class="add"><span class="icon icon_add"></span><a href="javascript:void(0)" id="upload-link">{{$LANG.file_upload}}</a></span>{{/if}}<span class="add" class="pic-btn"><span class="icon icon_photo"></span> <a href="javascript:void(0)" id="insert-pic">{{$LANG.picture}}</a></span>
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
                <td class="info_forms info_input"><textarea style="height:300px;" class="form_textarea" name="content" id="content" cols="" rows="">{{$post.content|tudu_format_content|escape:'html'}}</textarea></td>
              </tr>
            </table>
        </div>
    </div>
    <div class="toolbar">
        <div><button class="btn" type="button" name="send">{{$LANG.send}}</button><button class="btn" type="button" name="back">{{$LANG.back}}</button></div>
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

var h = $(window).height(),
ch = $(document.body).height();
var editorHeight = Math.max($('#content').height() + (h - ch - 15), 200);
$('#content').css('height', editorHeight + 'px');
_EDITOR = initEditor('#content');

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

$('button[name="back"]').click(function(){
	history.back();
});

$('button[name="send"]').click(function(){
	postSubmit('#theform');
});

$('#updateprogress').click(function(){
	$('#elapsedtime, #unit, #percent').attr('disabled', !this.checked);
});

$('#percent').stepper({step:25, max:100, format:'percent'});

$('#theform').submit(function(){return false;});

</script>
</html>
