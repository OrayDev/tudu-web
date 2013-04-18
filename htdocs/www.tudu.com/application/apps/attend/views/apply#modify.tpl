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
var LH = 'm=app/attend/apply/modify{{if $tudu.tuduid}}&tid={{$tudu.tuduid}}{{/if}}&back={{$back|default:'/app/attend/apply'}}';

if (top == this) {
    location = '/frame#' + LH;
}
-->
</script>
{{/if}}
<script src="{{$options.sites.static}}/js/frame.js?1021" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick.js?1005" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick-zh-cn.js?1005" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/upload.js?1008" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/plugins.js?1004" type="text/javascript"></script>

</head>
<body style="padding:0 5px 5px">
<form action="/app/attend/apply/compose" id="theform" method="post" class="tab_panel">
<input type="hidden" id="action" name="action" value="{{$action}}" />
<input type="hidden" id="issend" name="issend" value="1" />
<input type="hidden" id="ftid" name="ftid" value="{{$tudu.tuduid}}" />
<input type="hidden" id="classname" name="classname" value="{{$tudu.classname}}" />
<input type="hidden" name="starttime" id="starttime" value="{{$apply.starttime|date_format:'%Y-%m-%d %H:%M'}}" />
<input type="hidden" name="endtime" id="endtime" value="{{$apply.endtime|date_format:'%Y-%m-%d %H:%M'}}" />
    <div class="tab-panel-header">
        <table cellspacing="0" cellpadding="0" class="composetab">
          <tr>
          {{if !$tudu}}
          {{if $tab == "tudu" || $access.task}}
            <td><div class="composetab_sel"><div><a id="tudu" href="/tudu/modify?type=tudu{{if $newwin}}&newwin=1{{/if}}">{{$LANG.tudu}}</a></div></div></td>
          {{/if}}
          {{if $tab == "discuss" || $access.discuss}}
            <td><div class="composetab_sel"><div><a id="discuss" href="/tudu/modify?type=discuss{{if $newwin}}&newwin=1{{/if}}">{{$LANG.discuss}}</a></div></div></td>
          {{/if}}
          {{if $tab == "notice" || $access.notice}}
            <td><div class="composetab_sel"><div><a id="notice" href="/tudu/modify?type=notice{{if $newwin}}&newwin=1{{/if}}">{{$LANG.notice}}</a></div></div></td>
          {{/if}}
          {{if $tab == "meeting" || $access.meeting}}
            <td><div class="composetab_sel"><div><a id="meeting" href="/tudu/modify?type=meeting{{if $newwin}}&newwin=1{{/if}}">{{$LANG.meeting}}</a></div></div></td>
          {{/if}}
          {{if $tab == "board" || $access.board}}
            <td><div class="composetab_sel"><div><a href="/board/modify{{if $newwin}}?newwin=1{{/if}}">{{$LANG.board}}</a></div></div></td>
          {{/if}}
          {{/if}}
           <td><div class="composetab_sel on"><div><a href="/app/attend/apply/modify{{if $newwin}}?newwin=1{{/if}}">{{$LANG.attend_apply}}</a></div></div></td>
          </tr>
        </table>
    </div>
    <div class="tab-panel-body">
        <div class="toolbar">
            {{strip}}
            <div>
                <button class="btn" type="button" name="send"{{if $disabled}} disabled="disabled"{{/if}}>{{$LANG.send}}</button>
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
                        {{if $access.moderator}}
                        <a href="javascript:void(0)" class="expand-link{{if $tudu.tuduid}} disabled{{/if}}" id="add-target" style="margin-left:0;">{{if $apply.senderid != $apply.uniqueid}}{{$LANG.delete_agent}}{{else}}{{$LANG.add_agent}}{{/if}}</a>|
                        {{/if}}
                        <a href="javascript:void(0)" class="expand-link" id="add-cc">{{if $tudu.cc}}{{$LANG.delete_cc}}{{else}}{{$LANG.add_cc}}{{/if}}</a>
                      </td>
                      {{/strip}}
                    </tr>
                  </table>
                </div>
                <div class="info_box">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.apply_type}}</td>
                        <td class="info_forms" style="padding-right:10px;*">
                        {{strip}}
                        {{foreach from=$categories item=item}}
                        <label for="c-{{$item.categoryid}}" style="margin-right: 15px;"><input type="radio" value="{{$item.categoryid}}" name="categoryid" id="c-{{$item.categoryid}}"{{if $tudu && !$tudu.isdraft}} disabled="disabled" _disabled="disabled"{{/if}}{{if $apply.categoryid == $item.categoryid}} checked="checked"{{/if}} />{{$item.categoryname}}</label>
                        {{/foreach}}
                        {{/strip}}
                        {{if $tudu && !$tudu.isdraft}}<input type="hidden" name="categoryid" id="categoryid" value="{{$apply.categoryid}}" />{{/if}}
                        </td>
                      </tr>
                    </table>

                    {{if $access.moderator}}
                    <table id="row-target" cellspacing="0" cellpadding="0"{{if $apply.senderid || $apply.senderid == $apply.uniqueid}} style="display:none"{{/if}}>
                      <tr>
                        <td class="info_txt"><a href="javascript:void(0)" id="select-target">{{$LANG.apply_user}}</a></td>
                        <td class="info_forms" style="padding-right:10px;*"><input id="i-target" tabindex="2" class="input_text" type="text" readonly="readonly" value="" /><input type="hidden" name="target" id="target" value="{{if $apply.userinfo}}{{$apply.username}} {{$apply.truename}}{{/if}}" /></td>
                      </tr>
                    </table>
                    {{/if}}
                    <table id="row-cc" cellspacing="0" cellpadding="0"{{if !$tudu.cc}} style="display:none"{{/if}}>
                      <tr>
                        <td class="info_txt"><a href="javascript:void(0)" id="select-cc">{{$LANG.cc}}</a></td>
                        <td class="info_forms info_input" style="padding-right:10px"><input id="i-cc" tabindex="2" class="input_text" type="text" readonly="readonly" value="{{foreach from=$tudu.cc item=cc name=cc}}{{if !$smarty.foreach.cc.first}};{{/if}}{{$cc.0}}{{/foreach}}" /><input type="hidden" name="cc" id="cc" value="{{foreach item=item key=key from=$tudu.cc}}{{$item.3|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}" /></td>
                      </tr>
                    </table>

                    <table id="row-checkin" cellspacing="0" cellpadding="0"{{if $apply.categoryid != '^checkin'}} style="display:none"{{/if}}>
                      <tr>
                        <td class="info_txt">补签类型</td>
                        <td class="info_forms" style="padding-right:10px;*">
                            {{strip}}
                            <label for="t-checkin" style="margin-right: 15px;"><input type="radio" id="t-checkin" name="checkintype" value="0"{{if $tudu && $apply.checkintype == 0}} checked="checked"{{/if}}{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />签到</label>
                            <label for="t-checkout" style="margin-right: 15px;"><input type="radio" id="t-checkout" name="checkintype" value="1"{{if $tudu && $apply.checkintype == 1}} checked="checked"{{/if}}{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />签退</label>
                            {{/strip}}
                        </td>
                      </tr>
                    </table>
                    <table id="row-timetype" cellspacing="0" cellpadding="0"{{if $apply.categoryid == '^checkin'}} style="display:none"{{/if}}>
                      <tr>
                        <td class="info_txt">时间类型</td>
                        <td class="info_forms" style="padding-right:10px;*">
                            {{strip}}
                            <label for="t-day" style="margin-right: 15px;"><input type="radio" id="t-day" name="isallday" value="1"{{if !$tudu}} checked="checked"{{/if}}{{if $tudu && $apply.isallday}} checked="checked"{{/if}}{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />天</label>&nbsp;&nbsp;&nbsp;
                            <label for="t-hour" style="margin-right: 15px;"><input type="radio" id="t-hour" name="isallday" value="0"{{if $tudu && !$apply.isallday}} checked="checked"{{/if}}{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />小时</label>
                            {{/strip}}
                        </td>
                      </tr>
                    </table>
                    <table id="row-checkintime" cellspacing="0" cellpadding="0"{{if $apply.categoryid != '^checkin'}} style="display:none"{{/if}}>
                      <tr>
                        <td class="info_txt">时间</td>
                        <td class="info_forms">
                            {{strip}}
                            <input type="text" tabindex="6" class="input_text" name="checkindate" id="checkindate" readonly="readonly" value="{{if !$tudu.tuduid}}{{$smarty.now|date_format:'%Y-%m-%d'}}{{else}}{{if $tudu && $apply.checkintype == 0}}{{$apply.starttime|date_format:'%Y-%m-%d'}}{{else}}{{$apply.endtime|date_format:'%Y-%m-%d'}}{{/if}}{{/if}}" style="width:148px;"{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />&nbsp;
                            <input type="text" class="input_text" id="checkinhour" name="checkinhour" value="{{if $tudu && $apply.checkintype == 0}}{{$apply.starttime|date_format:'%H'}}{{else}}{{$apply.endtime|date_format:'%H'}}{{/if}}"  style="width:40px;"{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />:<input type="text" class="input_text" id="checkinmin" name="checkinmin" value="{{if $tudu && $apply.checkintype == 0}}{{$apply.starttime|date_format:'%M'}}{{else}}{{$apply.endtime|date_format:'%M'}}{{/if}}"  style="width:40px;"{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />
                            {{/strip}}
                        </td>
                      </tr>
                    </table>
                    <table id="row-date" cellspacing="0" cellpadding="0"{{if ($tudu && $apply.isallday) || $apply.categoryid == '^checkin' || !$tudu}} style="display:none"{{/if}}>
                      <tr>
                        <td class="info_txt">开始时间</td>
                        <td class="info_forms">
                            {{strip}}
                            <input type="text" tabindex="6" class="input_text" name="date" id="date" readonly="readonly" value="{{if !$tudu.tuduid}}{{$smarty.now|date_format:'%Y-%m-%d'}}{{else}}{{$apply.starttime|date_format:'%Y-%m-%d'}}{{/if}}" style="width:148px;"{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />&nbsp;
                            <input type="text" class="input_text" id="starthour" name="starthour" value="{{$apply.starttime|date_format:'%H'}}"  style="width:40px;"{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />:<input type="text" class="input_text" id="startmin" name="startmin" value="{{$apply.starttime|date_format:'%M'}}"  style="width:40px;"{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />
                            &nbsp;至&nbsp;
                            <input type="text" class="input_text" id="endhour" name="endhour" value="{{$apply.endtime|date_format:'%H'}}"  style="width:40px;"{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />:<input type="text" class="input_text" id="endmin" name="endmin" value="{{$apply.endtime|date_format:'%M'}}"  style="width:40px;"{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />
                            {{/strip}}
                        </td>
                      </tr>
                    </table>
                    <table id="row-startdate" cellspacing="0" cellpadding="0"{{if $tudu && !$apply.isallday}} style="display:none"{{/if}}>
                      <tr>
                        <td class="info_txt">开始时间</td>
                        <td class="info_forms">
                            <input type="text" tabindex="6" class="input_text" name="startdate" id="startdate" readonly="readonly" value="{{$apply.starttime|date_format:'%Y-%m-%d'}}" style="width:148px;"{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />
                        </td>
                      </tr>
                    </table>
                    <table id="row-enddate" cellspacing="0" cellpadding="0"{{if $tudu && !$apply.isallday}} style="display:none"{{/if}}>
                      <tr>
                        <td class="info_txt">结束时间</td>
                        <td class="info_forms">
                            <input type="text" tabindex="6" class="input_text" name="enddate" id="enddate" readonly="readonly" value="{{$apply.endtime|date_format:'%Y-%m-%d'}}" style="width:148px;"{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />
                        </td>
                      </tr>
                    </table>
                    <table id="row-total" cellspacing="0" cellpadding="0"{{if $apply.categoryid == '^checkin'}} style="display:none"{{/if}}>
                      <tr>
                        <td class="info_txt">共计</td>
                        <td class="info_forms"><input type="text" id="period" name="period" value="{{$apply.period}}" style="width: 40px;ime-mode:disabled" class="input_text"{{if $disabled}} disabled="disabled" _disabled="disabled"{{/if}} />&nbsp;{{$LANG.hour}}&nbsp;<span id="time-sum"></span>&nbsp;<span id="type-sum"></span>
                        </td>
                      </tr>
                    </table>
                </div>
                <div class="info_box">
                    <div class="attlist">
                        {{if $access.upload}}<span class="upload_btn"><span id="upload-btn"></span></span><span class="add"><span class="icon icon_add"></span><a href="javascript:void(0)" id="upload-link">{{$LANG.file_upload}}</a></span>{{/if}}
                        {{if $access.upload && $user.maxndquota > 0}}<span class="add" id="netdisk-btn"><span class="icon icon_nd_attach"></span><a href="javascript:void(0)">{{$LANG.netdisk_attach}}</a></span>{{/if}}
                        <span class="add" id="screencp-btn"><span class="icon icon_screencp"></span><a href="javascript:void(0)" id="link-capture">{{$LANG.screencapture}}</a></span>
                        <span class="add" id="pic-btn"><span class="icon icon_photo"></span> <a href="javascript:void(0)" id="insert-pic">{{$LANG.picture}}</a></span>
                        <span class="add" id="map-btn"><span class="icon icon_map"></span><a href="javascript:void(0)">{{$LANG.map}}</a></span>
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
                        <td class="info_forms info_input"><textarea style="height:300px;" class="form_textarea" id="content" cols="" rows="">{{$tudu.content|tudu_format_content|escape:'html'}}</textarea><textarea id="postcontent" name="content" style="display:none;"></textarea></td>
                      </tr>
                    </table>
                </div>
            </div>
        <div class="toolbar">
            {{strip}}
            <div>
                <button class="btn" type="button" name="send"{{if $disabled}} disabled="disabled"{{/if}}>{{$LANG.send}}</button>
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

<script src="{{$options.sites.static}}/js/attend/apply.js" type="text/javascript"></script>
<script type="text/javascript">
{{if $newwin}}
var _NEW_WIN = 1;
{{/if}}
var _FORM_DATA = null;

$(function(){
    var tuduId = '{{$tudu.tuduid}}';
    var back = '{{$back|default:'/tudu/view?tid=tuduId'}}';
    TOP.Frame.title('{{$LANG.attend_apply}}');
    TOP.Label.focusLabel();
    {{if !$newwin}}
    TOP.Frame.hash(LH);
    {{/if}}

    var targetParams = {
        init: {{if $access.moderator}}true{{else}}false{{/if}},
        depts: [{{foreach from=$deptids name=dept item=item}}'{{$item}}'{{if $smarty.foreach.dept.index != count($deptids) - 1}},{{/if}}{{/foreach}}]
    };
    var forbid = {
        editor: {{if $disabled}}true{{else}}false{{/if}},
        target:{{if $access.moderator && $tudu.tuduid}}true{{else}}false{{/if}},
        autosave: {{if $tudu.tuduid && !$tudu.isdraft}}true{{else}}false{{/if}}
    };
    {{if $user.option.settings.fontfamily}}
    Attend.Apply.editorCss = {
        'font-family':'{{$user.option.settings.fontfamily}}',
        'font-size':'{{$user.option.settings.fontsize|default:'12px'}}'
    };
    {{/if}}

    Attend.Apply.init(forbid, targetParams, back);
    Capturer.setUploadUrl('{{$options.sites.file}}{{$upload.cgi.upload}}');

    initPicInsert({'#insert-pic': Attend.Apply.editor}{{if $access.upload}}, {
        postParams: {'cookies': '{{$cookies}}'},
        fileSizeLimit: '{{$uploadsizelimit}}',
        auth: '{{$upload.auth}}'
    }{{/if}});

    {{if $access.upload}}
    Attend.Apply.upload = initAttachment({
        uploadUrl: '{{$options.sites.file}}{{$upload.cgi.upload}}',
        postParams: {'cookies': '{{$cookies}}'},
        fileSizeLimit: '{{$uploadsizelimit}}'
    }, $('#attach-list'), $('#attach-list td.bd_upload'));
    {{/if}}

    {{if $apply.categoryid}}
    Attend.Apply.changeCategory('{{$apply.categoryid}}');
    {{/if}}
});
</script>
</html>
