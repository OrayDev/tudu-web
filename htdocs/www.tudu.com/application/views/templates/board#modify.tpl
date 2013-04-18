<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.create_board}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
{{if $newwin}}
{{include file="^newwin.tpl"}}
{{/if}}
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/upload.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/board.js?1005" type="text/javascript"></script>

</head>
<body style="padding:0 5px 5px">
<form id="theform" action="/board/{{$action}}" method="post" class="tab_panel">
    <input type="hidden" name="bid" value="{{$board.boardid}}" />
    {{include file="compose^tab.tpl" tab="board"}}
    <div class="tab-panel-body">
        <div class="toolbar">
            <div><button class="btn" type="button" name="submit">{{$LANG.save}}</button><button class="btn" type="button" name="cancel">{{$LANG.cancel}}</button></div>
        </div>
            <div class="readmailinfo">
                <div class="info_box">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.belong_zone}}</td>
                        <td class="info_forms">
                        <select name="parentid" id="parentid">
                        <option value="">{{$LANG.select_zone}}</option>
                        {{foreach from=$zones item=zone}}
                        <option value="{{$zone.boardid}}"{{if $board.parentid == $zone.boardid}} selected="selected"{{/if}}>{{$zone.boardname}}</option>
                        {{/foreach}}
                        </select>
                        </td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.board_name}}</td>
                        <td class="info_forms info_input"><input class="input_text" name="name" id="name" type="text" value="{{$board.boardname|escape:'html'}}" /></td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"><a href="javascript:void(0)" id="select-moderators">{{$LANG.moderators}}</a></td>
                        <td class="info_forms info_input" style="padding-right:10px;*"><input class="input_text" id="i-moderators" type="text" readonly="readonly" value="{{foreach item=item from=$board.moderators name=md}}{{if !$smarty.foreach.md.first}};{{/if}}{{$item}}{{/foreach}}" /><input type="hidden" id="moderators" name="moderators" value="{{foreach key=key item=item from=$board.moderators}}{{$key|cat:'@'|cat:$user.orgid|cat:' '|cat:$item|cat:"\n"}}{{/foreach}}" /></td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"><a href="javascript:void(0)" id="select-groups">{{$LANG.join_members}}</a></td>
                        <td class="info_forms info_input" style="padding-right:10px;*"><input class="input_text" id="i-groups" type="text" readonly="readonly" value="{{foreach item=item from=$board.groups name=group}}{{if !$smarty.foreach.group.first}};{{/if}}{{if false !== strpos($item, '@')}}{{$item}}{{else}}{{$groups[$item].groupname}}{{/if}}{{/foreach}}" /><input type="hidden" id="groups" name="groups" value="{{foreach key=key item=item from=$board.groups}}{{$item|cat:"\n"}}{{/foreach}}" /></td>
                      </tr>
                    </table>
                </div>

                <div class="info_box">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.content}}</td>
                        <td class="info_forms info_input"><textarea class="form_textarea" name="memo" id="memo" cols="" rows="">{{$board.memo|escape:'html'}}</textarea></td>
                      </tr>
                    </table>
                </div>

                <div class="info_box">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"></td>
                        <td class="info_forms">
                            <div class="setting_box">
                                <input type="checkbox" id="classes"{{if $classes}} checked="checked"{{/if}} /><label for="classes" title="{{$LANG.board_classes_tips}}">{{$LANG.subject_class}}</label> <input type="checkbox" name="privacy" value="1" id="privacy"{{if !$board.boardid || $board.privacy}} checked="checked"{{/if}} /><label for="privacy" title="{{$LANG.board_secrecy_tips}}">{{$LANG.board_privacy}}</label> <input type="checkbox" name="protect" value="1" id="protect"{{if $board.protect}} checked="checked"{{/if}} /><label for="protect" title="{{$LANG.board_disedit_tips}}">{{$LANG.disable_edit}}</label> <input type="checkbox" name="templates" value="1" id="template"{{if $templates}} checked="checked"{{/if}} /><label for="template" title="{{$LANG.board_tpl_tips}}">{{$LANG.set_tpl}}</label> <label for="needconfirm" title="{{$LANG.board_tudu_need_confirm_tips}}"><input type="checkbox" name="needconfirm" value="1" id="needconfirm"{{if !$board}} checked="checked"{{/if}}{{if $board.needconfirm}} checked="checked"{{/if}} />{{$LANG.tudu_need_confirm}}</label> <label for="flowonly" title="{{$LANG.flow_only_tips}}"><input type="checkbox" name="flowonly" value="1" id="flowonly"{{if $board.flowonly}} checked="checked"{{/if}} />{{$LANG.flow_only}}</label>
                            </div>
                            <div class="cycle_wrap"{{if !$classes}} style="display:none"{{/if}} id="class-box">
                                <div class="content_box3">
                                    <strong>{{$LANG.add_subject_class}}</strong>
                                    <div class="line_bold"></div>
                                    <p class="gray">{{$LANG.subject_class_tips}}</p>
                                    <table cellspacing="0" cellpadding="0" class="setting_table">
                                    <tr>
                                       <td colspan="2" class="settingtd" style="line-height:24px;padding:5px 0">{{$LANG.classname}}{{$LANG.cln}}<input class="input_text" id="classname" name="classname" type="text" maxlength="20" style="width:430px" /><input class="btn" type="button" style="margin-left:5px" name="addclass" onclick="Board.Class.addClass()" value="{{$LANG.add_class}}" /></td>
                                    </tr>
                                    <tbody id="class-list">
                                    {{foreach from=$classes key=index item=item}}
                                    <tr id="class-{{$item.classid}}" style="line-height:24px">
                                      <td class="settingtd" name="classname">{{$item.classname}}</td>
                                      <td class="settingtd" align="right">
                                      <input type="hidden" name="classid[]" value="{{$item.classid}}" />
                                      <input name="classname-{{$item.classid}}" value="{{$item.classname}}" type="hidden" />
                                      <input name="ordernum-{{$item.classid}}" style="width:60px" type="hidden" value="{{$index}}" />
                                      <a href="javascript:void(0);" name="rename" onclick="Board.Class.renameClass('{{$item.classid}}')">{{$LANG.rename}}</a>&nbsp;&nbsp;<a href="javascript:void(0);" name="rename" onclick="Board.Class.removeClass('{{$item.classid}}')">{{$LANG.delete}}</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="up" onclick="Board.Class.sortClass('{{$item.classid}}', 'up');">↑</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="down" onclick="Board.Class.sortClass('{{$item.classid}}', 'down');">↓</a>
                                      </td>
                                    </tr>
                                    {{/foreach}}
                                    </tbody>
                                    <tr id="class-tpl" style="display:none;line-height:24px">
                                      <td class="settingtd" name="classname"></td>
                                      <td class="settingtd" align="right">
                                      <input type="hidden" name="newclass[]" value="" />
                                      <input name="ordernum" style="width:60px" type="hidden" value="" />
                                      <input name="classname" value="" type="hidden" />
                                      <a href="javascript:void(0);" name="rename">{{$LANG.rename}}</a>&nbsp;&nbsp;<a href="javascript:void(0);" name="remove">{{$LANG.delete}}</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="up">↑</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="down">↓</a>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td colspan="2" style="padding-top:5px"><input name="isclassify" type="checkbox" value="1" id="classify"{{if $board.isclassify}} checked="checked"{{/if}} /><label for="classify">{{$LANG.force_classify}}</label>&nbsp;&nbsp;<span class="gray">{{$LANG.force_classify_tips}}</span></td>
                                    </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="cycle_wrap" id="template-box"{{if !$templates}} style="display:none"{{/if}}>
                                <div class="content_box3">
                                    <strong>{{$LANG.set_tpl}}</strong>
                                    <div class="line_bold"></div>
                                    <p class="gray">{{$LANG.tpl_tips}}</p>
                                    <table cellspacing="0" cellpadding="0" class="setting_table" style="margin:0;">
                                    <tr>
                                       <td colspan="2" class="settingtd" style="line-height:24px;padding:5px 0">
                                        <table cellspacing="0" cellpadding="0">
					                      <tr>
					                        <td width="60px">{{$LANG.tpl_name}}{{$LANG.cln}}</td>
					                        <td><input class="input_text" name="templateName" type="text" value="" style="width:100%"/></td>
					                      </tr>
					                      <tr>
					                        <td valign="top">{{$LANG.tpl_content}}{{$LANG.cln}}</td>
					                        <td><textarea class="form_textarea" id="templateContent" name="templateContent" cols="" rows=""></textarea></td>
					                      </tr>
					                      <tr>
					                        <td>&nbsp;</td>
					                        <td><input class="btn" type="button" name="save-tpl" value="{{$LANG.add_tpl}}" onclick="Board.Template.saveTemplate()" /></td>
					                      </tr>
					                    </table>
                                       </td>
                                    </tr>

                                    <tbody id="tpl-list">
                                    {{foreach item=template key=key from=$templates}}
                                    <tr id="tpl-{{$key}}" style="line-height:24px">
                                      <td class="settingtd" name="tplname">{{$template.name}}</td>
                                      <td class="settingtd" align="right">
                                         <input type="hidden" name="number[]" value="{{$key}}" />
                                         <input type="hidden" name="tplid-{{$key}}" value="{{$template.templateid}}" />
                                         <input type="hidden" name="tplname-{{$key}}" value="{{$template.name}}" />
                                         <input name="tplordernum-{{$key}}" type="hidden" value="{{$template.ordernum}}" />
                                         <textarea style="display:none" name="tplcontent-{{$key}}">{{$template.content|escape:'html'}}</textarea>
                                         <a href="javascript:void(0);" name="modify" onclick="Board.Template.editTemplate('{{$key}}')">[{{$LANG.modify}}]</a>&nbsp;&nbsp;<a href="javascript:void(0);" name="delete" onclick="Board.Template.deleteTemplate('{{$key}}')">[{{$LANG.delete}}]</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="up" onclick="Board.Template.sortTemplate('{{$key}}', 'up');">↑</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="down" onclick="Board.Template.sortTemplate('{{$key}}', 'down');">↓</a>
                                      </td>
                                    </tr>
                                    {{/foreach}}
                                    </tbody>

                                    <tr id="tpl-new" style="display:none;line-height:24px">
                                      <td class="settingtd" name="tplname"></td>
                                      <td class="settingtd" align="right">
                                         <input type="hidden" name="newtpl[]" value="" />
                                         <input name="tplname" type="hidden" value="" />
                                         <input name="tplordernum" type="hidden" value="" />
                                         <textarea style="display:none" name="tplcontent"></textarea>
                                         <a href="javascript:void(0);" name="modify">[{{$LANG.modify}}]</a>&nbsp;&nbsp;<a href="javascript:void(0);" name="delete">[{{$LANG.delete}}]</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="up">↑</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="down">↓</a>
                                      </td>
                                    </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                      </tr>
                    </table>
                 </div>
            </div>
        <div class="toolbar">
            <div><button class="btn" type="button" name="submit">{{$LANG.save}}</button><button class="btn" type="button" name="cancel">{{$LANG.cancel}}</button></div>
        </div>
    </div>
</form>

<div class="pop_wrap" id="rename-win-src" style="display:none;position:absolute;background:#ebf4d8;">
<div class="pop">
    <input type="hidden" name="classid" value="" />
    <div class="pop_header"><strong>{{$LANG.rename}}</strong><a class="icon icon_close close"></a></div>
    <div class="pop_body">
        <p><strong>{{$LANG.input_new_name}}</strong></p>
        <p><input type="text" class="input_text" name="classname" value="" style="width:450px;" maxlength="20" /></p>
    </div>
<div class="pop_footer"><button type="button" name="confirm" class="btn">{{$LANG.confirm}}</button><button type="button" class="btn close">{{$LANG.cancel}}</button></div>
</div>
</div>

{{if 0}}
{{include file="compose^newwin_fix.tpl" type="board"}}
{{/if}}
<script type="text/javascript">
_CLASS_AUTOID = 0;

$(function(){
    TOP.Frame.title({{if $action == 'create'}}TOP.TEXT.CREATE_BOARD{{else}}TOP.TEXT.MODIFY_BOARD + '-' + '{{$board.boardname}}'{{/if}});
	{{if !$newwin}}
    TOP.Frame.hash('m=board/modify{{if $board}}&bid={{$board.boardid}}{{/if}}');
    {{/if}}

    {{if $user.option.fontfamily || $user.option.fontsize}}
    var editorCss = {
        'font-family':'{{$user.option.fontfamily|default:'SimSun'}}',
        'font-size':'{{$user.option.fontsize|default:'12px'}}'
    };
    {{else}}
    var editorCss = {};
    {{/if}}
    Board.setEditorCss(editorCss);

    Board.initModify();
});
</script>
</body>
</html>
