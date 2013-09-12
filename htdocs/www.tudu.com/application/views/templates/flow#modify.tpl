<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.create_flow}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script type="text/javascript">
<!--
var LH = 'm=flow/modify{{if $flow.flowid}}&flowid={{$flow.flowid}}{{/if}}';
if (top == this) {
	location = '/frame#' + LH;
}
-->
</script>
<style type="text/css">
.info_txt {width: 90px}
</style>
</head>
<body>
<form id="flowform" action="/flow/save" method="post" class="tab_panel">
{{if $action == 'update'}}
<input name="flowid" type="hidden" value="{{$flow.flowid}}" />
<input name="action" type="hidden" value="update" />
{{else}}
<input name="action" type="hidden" value="create" />
{{/if}}
  {{if $action == 'update'}}
  <div id="basic-header" class="tab-panel-header position" style="display:none;">
    <p><strong class="title">基本设置</strong> | <a onclick="Flow.saveBasic();" href="javascript:void(0)">{{$LANG.back}}</a></p>
  </div>
  <div id="steps-header" class="tab-panel-header position">
    <p><strong class="title">{{$LANG.tudu_flows}}</strong> | <a href="{{$back|default:'/flow/'}}">{{$LANG.return_list}}</a></p>
  </div>
  {{else}}
  <div class="tab-panel-header position">
     <p><strong class="title">{{$LANG.create_flow}}</strong>|<a style="margin-left:5px;" href="{{$back|default:'/flow/'}}">{{$LANG.return_list}}</a></p>
  </div>
  {{/if}}
  <div id="basic-div" class="tab-panel-body"{{if $action == 'update'}} style="display:none;"{{/if}}>
    <div class="toolbar">
      {{if $action == 'update'}}
      <button class="btn" type="button" name="save"{{if $action == 'update' && !$flow.access.modify}} disabled="disabled"{{/if}}>{{$LANG.save}}</button>
      {{else}}
      <button class="btn" type="button" name="next">{{$LANG.next_step}}</button>
      {{/if}}
      <button class="btn" type="button" name="f-cancel">{{$LANG.cancel}}</button>
    </div>
    <div class="readmailinfo" style="padding-top:5px;">
      <div class="info_box">
        <table cellspacing="0" cellpadding="0">
          <tr>
            <td class="info_txt">{{$LANG.belong_board}}</td>
            <td class="info_forms info_input">
              <input id="board-input" type="text" class="input_text" tabindex="1"{{if $flow.flowid}} disabled="disabled" _disabled="true"{{/if}} value="{{$flow.boardname}}" />
              <input type="hidden" id="bid" name="bid" value="{{$flow.boardid}}" />
            </td>
          </tr>
        </table>
        <table cellspacing="0" cellpadding="0">
          <tr>
            <td class="info_txt">{{$LANG.flow_subject}}</td>
            <td width="90" id="class-td"{{if !$flow.classid || !$classes}} style="display:none"{{/if}}><select style="width:90px;" name="classid" id="classid">
            {{if $board && !$board.isclassify}}<option value="">--{{$LANG.none}}--</option>{{/if}}
            {{if $classes}}
            {{foreach from=$classes item=class}}
            <option value="{{$class.classid}}"{{if $flow.classid === $class.classid}} selected="selected"{{/if}} title="{{$class.classname}}">{{$class.classname}}</option>
            {{/foreach}}
            {{/if}}
            </select></td>
            <td class="info_forms info_input">
              <input type="text" class="input_text" name="subject" maxlength="30" tabindex="1" value="{{$flow.subject}}" />
            </td>
          </tr>
        </table>
        <table cellspacing="0" cellpadding="0">
          <tr>
            <td class="info_txt">{{$LANG.flow_description}}</td>
            <td class="info_forms info_input">
              <input type="text" class="input_text" name="description" maxlength="50" tabindex="1" value="{{$flow.description}}" title="{{$LANG.flow_description_tips}}" />
            </td>
          </tr>
        </table>
        <table cellspacing="0" cellpadding="0">
          <tr>
            <td class="info_txt"><a href="javascript:void(0)" id="select-avaliable">{{$LANG.avaliable}}</a></td>
            <td class="info_forms info_input" style="padding-right:10px;"><input class="input_text" tabindex="1" id="i-avaliable" type="text" readonly="readonly" value="" /><input type="hidden" id="avaliable" name="avaliable" value="{{foreach key=key item=item from=$flow.avaliable}}{{$item|cat:"\n"}}{{/foreach}}" /></td>
          </tr>
        </table>
        <table cellspacing="0" cellpadding="0">
          <tr>
            <td class="info_txt"><a href="javascript:void(0)" id="select-cc">{{$LANG.cc}}</a></td>
            <td class="info_forms info_input" style="padding-right:10px;"><input id="i-cc" tabindex="1" class="input_text" type="text" readonly="readonly" value="{{foreach from=$flow.cc item=cc name=cc}}{{if !$smarty.foreach.cc.first}};{{/if}}{{$cc.0}}{{/foreach}}" /><input type="hidden" name="cc" id="cc" value="{{foreach item=item key=key from=$flow.cc}}{{$item.3|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}" /></td>
          </tr>
        </table>
        <table cellspacing="0" cellpadding="0">
          <tr>
            <td class="info_txt">{{$LANG.flow_elapsedtime}}</td>
            <td class="info_forms info_input">
              <input type="text" class="input_text" name="elapsedtime" id="elapsedtime" tabindex="1" value="{{$flow.elapsedtime}}" title="-" style="width:60px;" /> {{$LANG.flow_day}}<span class="gray">{{$LANG.flow_day_tips}}</span>
            </td>
          </tr>
        </table>
      </div>
      <div class="info_box">
        <div class="attlist" style="margin: 0 0 0 103px">
            <span class="add"><span class="icon icon_tpl"></span><a href="javascript:void(0)" name="tpllist" _textarea="content">{{$LANG.add_tpl_list}}</a></span>
            {{if $access.upload}}<span class="upload_btn"><span id="upload-btn"></span></span><span class="add"><span class="icon icon_add"></span><a href="javascript:void(0)" id="upload-link">{{$LANG.file_upload}}</a></span>{{/if}}
            {{if $access.upload && $user.maxndquota > 0}}<span class="add" id="netdisk-btn"><span class="icon icon_nd_attach"></span><a href="javascript:void(0)">{{$LANG.netdisk_attach}}</a></span>{{/if}}
            <span class="add" id="screencp-btn"><span class="icon icon_screencp"></span><a href="javascript:void(0)" id="link-capture">{{$LANG.screencapture}}</a></span>
            <span class="add" id="pic-btn"><span class="icon icon_photo"></span> <a href="javascript:void(0)" id="insert-pic">{{$LANG.picture}}</a></span>
        </div>
      </div>

      <div id="attach-list" class="info_box att_container"{{if $flow.countattach <= 0}} style="display:none"{{/if}}>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td class="info_txt"></td>
            <td class="bd_upload">
                {{foreach item=file from=$flow.attachments}}
                <div class="filecell" id="attach-{{$file.fileid}}">
                <input type="hidden" name="attach[]" value="{{$file.fileid}}" />
                <div class="attsep">
                <div class="attsep_file">
                <span class="icon icon_add"></span><span class="filename">{{$file.filename}}</span>&nbsp;<span class="filesize">({{if $file.size < 1024}}{{$file.size}}bytes{{else}}{{math|round equation="x/1024" x=$file.size}}KB{{/if}})</span></div>
                <div class="attsep_del"><a href="javascript:void(0)" name="delete" onClick="Modify.removeAttach('{{$file.fileid}}');">{{$LANG.delete}}</a></div><div class="clear"></div></div>
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
            <td class="info_forms info_input"><textarea id="content" cols="" rows="" style="width:100%;height:180px">{{$flow.content|tudu_format_content|escape:'html'}}</textarea><textarea id="postcontent" name="content" style="display:none;"></textarea></td>
          </tr>
        </table>
      </div>
      <div class="info_box">
        <table cellspacing="0" cellpadding="0">
          <tr>
            <td class="info_txt"></td>
            <td class="info_forms">
              <div class="setting_box option_box">{{$LANG.flow_content_tips}}</div>
            </td>
          </tr>
        </table>
      </div>
    </div>
    <div class="toolbar">
      {{if $action == 'update'}}
      <button class="btn" type="button" name="save"{{if $action == 'update' && !$flow.access.modify}} disabled="disabled"{{/if}}>{{$LANG.save}}</button>
      {{else}}
      <button class="btn" type="button" name="next">{{$LANG.next_step}}</button>
      {{/if}}
      <button class="btn" type="button" name="f-cancel">{{$LANG.cancel}}</button>
    </div>
  </div>
  <div id="steps-div"{{if $action == 'create'}} style="display:none;"{{/if}}>
    <div class="toolbar" style="padding: 8px;">
      <p><strong class="f14" style="color: #000000;" id="flow-subject">{{$flow.subject}}</strong>&nbsp;<a onclick="Flow.showBasic(true);" href="javascript:void(0)">[{{$LANG.modify_settings}}]</a></p>
    </div>
    <div class="content_box2">
        <div class="msg"><p>{{$LANG.description}}{{$LANG.cln}}<span id="flow-description">{{$flow.description|default:'-'}}</span></p></div>
        <div class="todo_content">
            <table cellspacing="3" cellpadding="0" border="0">
                <colgroup>
                    <col>
                    <col width="170">
                    <col>
                    <col>
                </colgroup>
                <tr>
                    <td align="right">{{$LANG.belong_board}}{{$LANG.cln}}</td>
                    <td class="black" colspan="3"><span id="belong-board">{{$flow.boardname}}</span></td>
                </tr>
                <tr>
                    <td align="right">{{$LANG.avaliable}}{{$LANG.cln}}</td>
                    <td class="black avaliable-titles"><div id="avaliable-names" style="white-space:nowrap;"></div></td>
                    <td align="right">{{$LANG.cc}}{{$LANG.cln}}</td>
                    <td class="black cc-titles"{{if $flow.cc}} title="{{foreach item=cc from=$flow.cc name=cc}}{{if !$smarty.foreach.cc.first}},&#13;{{/if}}{{$cc.0}}{{if $cc.3}}<{{if strpos($cc.3, '@')}}{{$cc.3}}{{else}}{{$LANG.group}}{{/if}}>{{/if}}{{/foreach}}"{{/if}}><div id="cc-names">{{foreach from=$flow.cc item=cc name=cc}}{{if $smarty.foreach.cc.index < 6}}{{if $cc.3 == $user.username}}{{$LANG.me}}{{else}}{{$cc.0}}{{/if}}{{if $smarty.foreach.cc.index + 1 < count($flow.cc)}},{{/if}}{{/if}}{{foreachelse}}-{{/foreach}}{{if $flow.cc && count($flow.cc) > 6}}...{{/if}}</div></td>
                </tr>
                <tr>
                    <td align="right">{{$LANG.flow_elapsedtime}}{{$LANG.cln}}</td>
                    <td class="black"><span id="flow-elapsedtime">{{$flow.elapsedtime|default:'-'}}{{if $flow.elapsedtime}}{{$LANG.flow_day}}{{/if}}</span><span id="lang-elapsedtime" style="display:none;">{{$LANG.flow_day}}</span></td>
                    <td align="right">&nbsp;</td>
                    <td class="black">&nbsp;</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="grid_list_wrap">
        <div class="grid_list_title" style="line-height:24px;"><strong class="font_c">{{$LANG.setting_flow_steps}}</strong>{{if ($action == 'update' && $flow.access.modify) || $access.create}}&nbsp;<a href="javascript:void(0)" name="create-steps">[{{$LANG.create_flow_steps}}]</a>{{/if}}</div>
        <table cellspacing="0" cellpadding="0" class="grid grid_list flow_list">
            <colgroup>
                <col>
                <col width="180">
                <col width="200">
            </colgroup>
            <thead>
                <tr>
                    <td style="border-left:none;">{{$LANG.steps_subject}}</td>
                    <td>{{$LANG.examine}}/{{$LANG.receiver}}</td>
                    <td style="border-right:0;">{{$LANG.operation}}</td>
                </tr>
            </thead>
            <tbody id="steps-{{$flow.flowid|default:'create'}}">
                <tr id="step-_head">
                    <td><img src="/images/icon/flow_start.gif" align="absmiddle" />&nbsp;&nbsp;{{$LANG.step_start}}</td>
                    <td><span class="gray">－</span></td>
                    <td><span class="gray">－</span></td>
                </tr>
                {{foreach key=key item=step from=$flow.steps}}
                <tr id="step-{{$key}}" _stepid="{{$step.stepid}}" _key="{{$key}}">
                    <td>
                        <input name="member[]" value="{{$key}}" type="hidden" />
                        <input name="id-{{$key}}" value="{{$step.id}}" type="hidden" />
                        <input name="type-{{$key}}" value="{{$step.type}}" type="hidden" />
                        <input name="prev-{{$key}}" value="{{$step.prev}}" type="hidden" />
                        <input name="subject-{{$key}}" value="{{$step.subject}}" type="hidden" />
                        <input name="description-{{$key}}" value="{{$step.description}}" type="hidden" />
                        <input name="users-{{$key}}" value="{{$step.users}}" type="hidden" />
                        <input name="order-{{$key}}" value="{{$key}}" type="hidden" />
                        <img name="img-{{$key}}"{{if $step.type == 1}} src="/images/icon/flow_examine.gif"{{else}} src="/images/icon/flow_execute.gif"{{/if}} align="absmiddle" />&nbsp;&nbsp;<span name="subject-{{$key}}">{{$step.subject}}</span>
                    </td>
                    <td><div class="username"{{if $step.type == 1}}{{if $step.sections == '^upper'}} title="{{$LANG.higher_review}}"{{else}} title="{{foreach from=$step.sections name=stepuser item=users}}{{foreach from=$users name=itemuser item=item}}<{{$item.username}}>{{$item.truename}}{{if $smarty.foreach.itemuser.index < count($users) - 1}},{{/if}}{{/foreach}}{{if $smarty.foreach.stepuser.index < count($step.sections) - 1}},{{/if}}{{/foreach}}"{{/if}}{{else}}title="{{foreach from=$step.sections name=stepuser item=user}}{{foreach from=$user name=itemuser item=item}}<{{$item.username}}>{{$item.truename}}{{if $smarty.foreach.itemuser.index < count($user) - 1}},{{/if}}{{/foreach}}{{/foreach}}"{{/if}}>{{if $step.sections == '^upper'}}{{$LANG.higher_review}}{{else}}{{$step.username}}{{/if}}</div></td>
                    <td>
                        {{if $flow.access.modify}}
                        {{if $step.type == 1}}
                        <a name="update-user" href="javascript:void(0)" onclick="Flow.setSteps('{{$key}}', 1);">[{{$LANG.reviewer}}]</a>&nbsp;
                        {{else}}
                        <a name="update-user" href="javascript:void(0)" onclick="Flow.setSteps('{{$key}}', 0);">[{{$LANG.column_accepter}}]</a>&nbsp;
                        {{/if}}
                        <a href="javascript:void(0)" onclick="Flow.setSteps('{{$key}}');">[{{$LANG.modify}}]</a>&nbsp;
                        <a href="javascript:void(0);" onclick="Flow.deleteStep('{{$key}}')">[{{$LANG.delete}}]</a>
                        {{else}}
                        <span class="gray">－</span>
                        {{/if}}
                    </td>
                </tr>
                {{foreachelse}}
                <tr id="null-steps">
                    <td colspan="3" style="text-align:center;height:100px">{{$LANG.flow_steps_null}}{{if ($action == 'update' && $flow.access.modify) || $access.create}}{{$LANG.comma}}{{$LANG.please}}<a href="javascript:void(0)" name="create-steps">{{$LANG.create_flow_steps}}</a>{{/if}}</td>
                </tr>
                {{/foreach}}
            </tbody>
            <tbody>
                <tr>
                    <td><img src="/images/icon/flow_finish.gif" align="absmiddle" />&nbsp;&nbsp;{{$LANG.step_end}}</td>
                    <td><span class="gray">－</span></td>
                    <td><span class="gray">－</span></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="toolbar">
      <p><button name="save" class="btn"{{if $action == 'update' && !$flow.access.modify}} disabled="disabled"{{/if}}>{{$LANG.save}}</button><button name="cancel" type="button" class="btn">{{$LANG.cancel}}</button></p>
    </div>
  </div>
</form>

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

<script src="{{$options.sites.static}}/js/xheditor-1.1.9/xheditor-zh-cn.min.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/upload.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/plugins.js?1004" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/boardselector.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/tudu2.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/flow.js?1004" type="text/javascript"></script>
<script type="text/javascript">
<!--
$(function(){
    TOP.Frame.title('{{if $action == 'update'}}{{$LANG.tudu_flows}}{{else}}{{$LANG.create_flow}}{{/if}}');
    TOP.Frame.hash(LH);

    Flow.flowId = "{{$flow.flowid|default:'create'}}";
    Flow.back = "{{$back|default:'/flow/'}}";
    Flow.boards = {{$boards|@json_encode}};
    Flow.index = {{$flow.steps|@count}};
    Flow.initModify();

    Flow.upload = initAttachment({
    	buttonPlaceholderId: 'upload-btn',
        uploadUrl: '{{$options.sites.file}}{{$upload.cgi.upload}}',
        postParams: {'cookies': '{{$cookies}}'},
        fileSizeLimit: '{{$uploadsizelimit}}'
    }, $('#attach-list'), $('#attach-list td.bd_upload'));

    initPicInsert({'#insert-pic': Flow.editor} {{if $access.upload}}, {
        postParams: {'cookies': '{{$cookies}}'},
        fileSizeLimit: '{{$uploadsizelimit}}',
        auth: '{{$upload.auth}}'
    }{{/if}});

    Capturer.setUploadUrl('{{$options.sites.file}}{{$upload.cgi.upload}}');
});
-->
</script>

</body>
</html>