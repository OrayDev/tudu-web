<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.create_tudu}}</title>
{{include file="foreign^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js" type="text/javascript"></script>
<script src="/static/js?f=lang&lang={{$user.option.language}}" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/all.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick-zh-cn.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/xheditor-1.1.9/xheditor-zh-cn.min.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/foreign/compose.js?1011" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/upload.js?1007" type="text/javascript"></script>

</head>
<body style="padding:0 5px 5px">
{{include file="foreign^header.tpl"}}
<form action="/foreign/compose/send" id="theform" method="post" class="tab_panel">
<input type="hidden" id="action" name="action" value="" />
<input type="hidden" id="type" name="type" value="task" />
<input type="hidden" id="myname" value="{{$user.truename}}" />
{{if $isforward}}
<input type="hidden" name="forward" value="1" />
{{/if}}
<input type="hidden" id="tid" name="tid" value="{{$tudu.tuduid}}" />
<input type="hidden" name="fid" value="{{$user.uniqueid}}" />
<input type="hidden" name="ts" value="{{$tsid}}" />
<input type="hidden" id="classname" name="classname" value="{{$tudu.classname}}" />
<input type="hidden" id="issend" name="issend" value="1" />
    <div class="tab-panel-header">
        <table cellspacing="0" cellpadding="0" class="composetab">
          <tr>
            <td><div class="composetab_sel on"><div><a href="javascript:void(0)">{{$LANG.forward}}</a></div></div></td>
          </tr>
        </table>
    </div>
    <div class="tab-panel-body">
        <div class="toolbar">
            <div><button class="btn" type="button" name="back">{{$LANG.back}}</button><button class="btn" type="button" name="send">{{$LANG.send}}</button>{{if 0}}<button class="btn" type="button">{{$LANG.send_on_time}}</button>{{/if}}{{if !$tudu.tuduid || $tudu.isdraft}}<button class="btn" type="button" name="save">{{$LANG.save_draft}}</button><button class="btn" type="button" name="preview">{{$LANG.preview}}</button>{{/if}}</div>
        </div>
            <div class="readmailinfo">
                <div class="info_box">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.belong_board}}</td>
                        <td class="info_forms">
                        {{if !$tudu.tuduid || $tudu.isdraft}}
                        <select id="board" name="bid" tabindex="0">
                        <option value="">{{$LANG.select_board}}</option>
                        {{foreach from=$boards item=item}}
                        {{if $item.type == 'zone' && $item.children}}
                        <optgroup label="{{$item.boardname}}">
                            {{foreach from=$item.children item=child}}
                            <option value="{{$child.boardid}}"{{if $tudu.boardid == $child.boardid}} selected="selected"{{/if}}{{if $child.isclassify && !$isforward}} _classify="1"{{/if}}>{{$child.boardname}}</option>
                            {{/foreach}}
                        </optgroup>
                        {{/if}}
                        {{/foreach}}
                        </select>
                        {{elseif $isforward}}
                        <select id="board" name="board" disabled="disabled" _disabled="true" tabindex="0">
                        <option value="">{{$LANG.select_board}}</option>
                        {{foreach from=$boards item=board}}
                        <option value="{{$board.boardid}}"{{if $tudu.boardid == $board.boardid}} selected="selected"{{/if}}{{if $board.isclassify && !$isforward}} _classify="1"{{/if}}>{{$board.boardname}}</option>
                        {{/foreach}}
                        </select>
                        <input type="hidden" id="bid" name="bid" value="{{$tudu.boardid}}" />
                        {{else}}
                        <select id="board" name="board" disabled="disabled" _disabled="true" tabindex="0">
                        <option value="">{{$LANG.select_board}}</option>
                        {{foreach from=$boards item=item}}
                        {{if $item.type == 'zone' && $item.children}}
                        <optgroup label="{{$item.boardname}}">
                            {{foreach from=$item.children item=child}}
                            <option value="{{$child.boardid}}"{{if $tudu.boardid == $child.boardid}} selected="selected"{{/if}}{{if $child.isclassify && !$isforward}} _classify="1"{{/if}}>{{$child.boardname}}</option>
                            {{/foreach}}
                        </optgroup>
                        {{/if}}
                        {{/foreach}}
                        </select>
                        <input type="hidden" id="bid" name="bid" value="{{$tudu.boardid}}" />
                        {{/if}}
                        </td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.subject}}</td>
                        <td width="90" id="class-td"{{if !$tudu.classid && !$classes}} style="display:none"{{/if}}><select style="width:90px;" name="classid" id="classid"{{if $tudu.classid && ($board.protect || $isforward)}} disabled="disabled"{{/if}}>
                        <option value="">--{{$LANG.select_class}}--</option>
                        {{if $classes}}
                        {{foreach from=$classes item=class}}
                        <option value="{{$class.classid}}"{{if $tudu.classid === $class.classid}} selected="selected"{{/if}} title="{{$class.classname}}">{{$class.classname}}</option>
                        {{/foreach}}
                        {{/if}}
                        </select></td>
                        <td class="info_forms info_input"><input class="input_text" name="subject" id="subject" type="text" value="{{$tudu.subject}}" tabindex="1"{{if $board.protect || $isforward}} readonly="true"{{/if}} /></td>
                      </tr>
                    </table>
                    {{if $tudu.istudugroup && !in_array($user.email, $tudu.accepter)}}
                    <input type="hidden" name="to" id="to" value="{{foreach item=item key=key from=$tudu.to}}{{$key|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}" />
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"><a href="javascript:void(0)" id="select-cc">{{$LANG.cc}}</a></td>
                        <td class="info_forms info_input" style="padding-right:10px;*"><input id="i-cc" tabindex="3" class="input_text" type="text" readonly="readonly" value="{{foreach from=$tudu.cc item=cc name=cc}}{{if !$smarty.foreach.cc.first}};{{/if}}{{$cc.0}}{{/foreach}}" /><input type="hidden" name="cc" id="cc" value="{{foreach item=item key=key from=$tudu.cc}}{{$key|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}" /></td>
                      </tr>
                    </table>
                    {{else}}
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"><a href="javascript:void(0)" id="select-to">{{$LANG.receiver}}</a></td>
                        <td class="info_forms" style="padding-right:10px;*"><input id="i-to" tabindex="2" class="input_text" type="text" readonly="readonly" value="{{foreach from=$tudu.to item=to name=to}}{{if !$smarty.foreach.to.first}};{{/if}}{{$to.0}}{{/foreach}}" /><input type="hidden" name="to" id="to" value="{{foreach item=item key=key from=$tudu.to}}{{$key|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}" /></td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"><a href="javascript:void(0)" id="select-cc">{{$LANG.cc}}</a></td>
                        <td class="info_forms info_input" style="padding-right:10px;*"><input id="i-cc" tabindex="3" class="input_text" type="text" readonly="readonly" value="{{foreach from=$tudu.cc item=cc name=cc}}{{if !$smarty.foreach.cc.first}};{{/if}}{{$cc.0}}{{/foreach}}" /><input type="hidden" name="cc" id="cc" value="{{foreach item=item key=key from=$tudu.cc}}{{$key|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}" /></td>
                      </tr>
                    </table>
                    {{/if}}
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.starttime}}</td>
                        <td class="info_forms" width="185"><input type="text" tabindex="4" class="input_text" id="starttime" name="starttime" value="{{$tudu.starttime|date_format:'%Y-%m-%d'}}" readonly="readonly" style="width:178px;"{{if $isforward && count($tudu.accepter) > 1}} disabled="disabled"{{/if}} /></td>
                        <td class="info_txt">{{$LANG.epalsedtime}}</td>
                        <td class="info_forms" width="65"><input style="width:60px;" tabindex="5" class="input_text" name="totaltime" id="totaltime" value="{{if $tudu.totaltime}}{{math equation="x/3600" x=$tudu.totaltime}}{{/if}}" type="text" /></td>
                        <td class="info_forms"><select style="width:60px" name=""><option>{{$LANG.hour}}</option></select></td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.endtime}}</td>
                        <td class="info_forms" style="width:185px;"><input type="text" tabindex="6" class="input_text" name="endtime" id="endtime" readonly="readonly" value="{{$tudu.endtime|date_format:'%Y-%m-%d'}}" style="width:178px;"{{if $isforward && count($tudu.accepter) > 1}} disabled="disabled"{{/if}} /></td>
                        <td class="info_txt">{{$LANG.tudu_percent}}</td>
                        <td class="info_forms info_input">
                           <input type="text" class="input_text" tabindex="7" id="percent" name="percent" value="{{if $isforward && count($tudu.accepter) > 1}}{{$tudu.selfpercent|default:0}}{{else}}{{$tudu.percent|default:0}}{{/if}}%"  style="width:60px;" />
                        </td>
                      </tr>
                    </table>
                </div>
                <div class="info_box">
                    <div class="attlist">
                        {{if $access.upload}}<span class="upload_btn"><span id="upload-btn"></span></span><span class="add"><span class="icon icon_add"></span><a href="javascript:void(0)" id="upload-link">{{$LANG.file_upload}}</a></span>{{/if}}<span class="add" id="map-btn"><span class="icon icon_map"></span><a href="javascript:void(0)">{{$LANG.map}}</a></span><span class="add" id="pic-btn"><span class="icon icon_photo"></span> <a href="javascript:void(0)" id="insert-pic">{{$LANG.picture}}</a></span>
                    </div>
                </div>
                <div id="attach-list" class="info_box att_container"{{if !$tudu.attachments}} style="display:none"{{/if}}>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"></td>
                        <td class="bd_upload">
                            {{foreach item=file from=$tudu.attachments}}
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
                        <td class="info_forms info_input"><textarea class="form_textarea" name="content" id="content" cols="" rows="">{{if !$isforward}}{{$tudu.content|tudu_format_content|escape:'html'}}{{/if}}</textarea></td>
                      </tr>
                    </table>
                </div>
            </div>
        <div class="toolbar">
            <div><button class="btn" type="button" name="back">{{$LANG.back}}</button><button class="btn" type="button" name="send">{{$LANG.send}}</button></div>
        </div>
    </div>
</form>

{{if $newwin}}
{{include file="compose^newwin_fix.tpl" type="task"}}
{{/if}}
{{if 0}}
    <div class="pop_wrap" style="width:500px;display:none;">
       <div class="pop">
          <div class="pop_header"><strong>所属板块</strong><a href="javascript:void(0);" class="icon icon_close"></a></div>
             <div class="pop_body">
                <div>
                    <div class="p_body_left">
                        <p><strong>选择所属分区</strong></p>
                        <div class="pop_body_inner">
                            <div class="input_box"><input class="input_text input_tips" name="" type="text" value="输入字母或拼音搜索"><a class="icon icon_search_2"></a></div>
                            <div class="list_box">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="gird_fix list_over">
                                  <tr>
                                    <td>产品部需求收集版</td>
                                    <td align="right"><span class="icon icon_triangle"></span></td>
                                  </tr>
                                  <tr class="select over">
                                    <td>向日葵v2.0发布</td>
                                    <td align="right"><span class="icon icon_triangle"></span></td>
                                  </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="p_body_right">
                        <p><strong>选择所属板块</strong></p>
                        <div class="pop_body_inner">
                            <div class="input_box"><input class="input_text input_tips" name="" type="text" value="输入字母或拼音搜索"><a class="icon icon_search_2"></a></div>
                            <div class="list_box">
                                <a href="#">向日葵客户端界面</a>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
               </div>
             </div>
          <div class="pop_footer"><button type="button" class="btn">确定</button><button type="button" class="btn">取消</button></div>
       </div>
    </div>
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
var _CONTACTS = [];
_CONTACTS.push({name: '{{$tudu.from.0}}',email: '{{$tudu.sender}}'});
{{foreach from=$users item=item}}
{{if in_array($item.role, array('from', 'to', 'cc')) && ($item.email || $item.truename)}}
_CONTACTS.push({name: '{{$item.truename}}',email: '{{$item.email}}', foreign: {{if $item.isforeign}}true{{else}}false{{/if}}{{if $item.isforeign}}, contactid: '{{$item.uniqueid}}'{{/if}}});
{{/if}}
{{/foreach}}

$(function(){
    var h = $(window).height(),
    ch = $(document.body).height();

    var editorHeight = Math.max($('#content').height() + (h - ch - 15), 200);
    $('#content').css('height', editorHeight + 'px');
    _EDITOR = initEditor('#content');
    {{if $board.protect && !$isforward}}
    disableEditor('0');
    {{/if}}

    initTimePicker();

    var toInput = new TOP.ForwardInput({
        id: 'to-input', target: $('#i-to'), valuePlace: $('#to'), data: _CONTACTS,
        onUpdate: function() {
            var to = this._settings.valuePlace.val().split("\n"), toArr = [];
            for (var i = 0, c = to.length; i < c; i++) {
                var a = to[i].split(' ');
                if (a[1]) {
                    toArr.push(a[1]);
                }
            }

            var source = _EDITOR.getSource();

            var div = $('<div>');
            div.html(source);

            if (!toArr.length) {
                div.find('p[_name="forward"]').remove();
            } else {
                var text = TOP.formatString(TOP.TEXT.FORWARD_INFO, $('#myname').val(), toArr.join(','));
                var html = '<strong>'+TOP.TEXT.FORWARD+TOP.TEXT.CLN+'</strong><span style="color:#aaa">'+text+'</span>';

                if (div.find('p[_name="forward"]').size()) {
                    div.find('p[_name="forward"]').html(html);
                } else {
                    div.prepend('<p _name="forward">'+html+'</p><br />');
                }
            }

            _EDITOR.setSource(div.html());

        }
    });
    var ccInput = new TOP.ForwardInput({
        id: 'cc-input', target: $('#i-cc'), valuePlace: $('#cc'), data: _CONTACTS
    });

    $('#board').change(function(){loadClasses(this.value, '#classid');});

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

    initSelectLink('#select-to', toInput, $('#to'), _CONTACTS);
    initSelectLink('#select-cc', ccInput, $('#cc'), _CONTACTS);


    $('#percent').stepper({step: 25, max:100, format: 'percent'});

    $('button[name="save"], button[name="send"]').click(function(){
        $('#action').val(this.name);

        composeSubmit('#theform');
    });

    $('button[name="back"]').click(function(){
        history.back();
    });

    $('#enddate').datepick({
        minDate: new Date(),
        showOtherMonths: true,
        selectOtherMonths: true,
        firstDay: 0,
        showAnim: 'slideDown',
        showSpeed: 'fast'
    });

    $('#secrecy').click(function(){
        var checked = this.checked;
        $('#block-private').css('display', checked ? '' : 'none');
        if (checked) {
            window.scrollTo(0, $('#block-private').offset().top);
        }
        if ($(this).attr('checked') == false) {
            $('#password').val('');
            $('#open_pwd').attr('checked', false);
            $('#password').attr('disabled', true);
        }
    });

    $('#open_pwd').click(function(){
        if ($(this).attr('checked') == true) {
            $("input[name='password']").each(function() {
                $(this).attr('disabled', false);
                $('#password').focus();
            });
        } else {
            $("input[name='password']").each(function() {
                $(this).attr('disabled', true);
                $('#password').val('');
            });
        }
    });

    _INIT_CYCLE_INPUT = false;
    $('#theform').submit(function(){return false;});

    $('#cycle').click(function(){
        {{if $cycle}}
        return false;
        {{else}}
        var checked = this.checked;
        $('#block-cycle').css('display', checked ? '' : 'none');
        if (checked) {
            window.scrollTo(0, $('#block-cycle').offset().top);
        }
        {{/if}}
    });

    $('#classid').change(function(){
        var o = $(this);
        if (o.val()) {
            $('#classname').val($(this).find('option:selected').text());
        } else {
            $('#classname').val('');
        }
    });

    $('#mode-group :radio[name="mode"]').click(function(){
        $('div.method').hide();
        $('#mode-' + this.value).show();
    });

    {{if !$isforward}}
    initUnloadEvent('#theform'{{if !$tudu.tuduid || $tudu.isdraft}}, true{{/if}});
    {{if $tudu && (!$tudu.tuduid || $tudu.isdraft)}}_FORM_DATA = {};{{/if}}
    {{/if}}
});

$('#map-btn').click(function(){
    _EDITOR.showIframeModal('Google 地图','/googlemap/googlemap.html',function(v){_EDITOR.pasteHTML('<img src="'+v+'" />');},538,404);
});

$('div.tab-panel-header a[href^="/tudu/modify"]').click(function(){
    getFormPreview('#theform', this.href, '_self');
    return false;
});
</script>
</html>
