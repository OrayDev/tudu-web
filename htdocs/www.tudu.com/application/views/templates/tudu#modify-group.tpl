<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.create_tudu}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
{{if $newwin}}
<script src="{{$options.sites.static}}/js/all.js" type="text/javascript"></script>
<script src="/static/js?f=lang&lang={{$user.option.language}}" type="text/javascript"></script>
{{/if}}
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick-zh-cn.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/xheditor-1.1.9/xheditor-zh-cn.min.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/upload.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/common.js?1034" type="text/javascript"></script>

</head>
<body style="padding:0 5px 5px">
<form action="/compose/group" id="theform" method="post" class="tab_panel">
<input type="hidden" id="action" name="action" value="" />
<input type="hidden" id="type" name="type" value="task" />
<input type="hidden" id="ftid" name="ftid" value="{{$tudu.tuduid}}" />
<input type="hidden" id="classname" name="classname" value="{{$tudu.classname}}" />
{{if $divide}}
<input type="hidden" name="divide" value="1" />
{{/if}}
<input type="hidden" id="issend" name="issend" value="1" />
    {{include file="compose^tab.tpl" tab="group"}}
    <div class="tab-panel-body">
        <div class="toolbar">
            <div><button class="btn" type="button" name="send">{{$LANG.send}}</button>{{if 0}}<button class="btn" type="button">{{$LANG.send_on_time}}</button>{{/if}}{{if !$tudu.tuduid || $tudu.isdraft || !$divide}}<button class="btn" type="button" name="save">{{$LANG.save_draft}}</button><button class="btn" type="button" name="preview">{{$LANG.preview}}</button>{{/if}}<span class="compose_msg"></span></div>
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
                            <option value="{{$child.boardid}}"{{if $tudu.boardid == $child.boardid}} selected="selected"{{/if}}{{if $child.isclassify && !$isforward}} _classify="1"{{/if}} _needconfirm="{{if $child.needconfirm}}1{{else}}0{{/if}}">{{$child.boardname}}</option>
                            {{/foreach}}
                        </optgroup>
                        {{/if}}
                        {{/foreach}}
                        </select>
                        {{else}}
                        <select id="board" name="board" disabled="disabled" _disabled="true" tabindex="0">
                        <option value="">{{$LANG.select_board}}</option>
                        {{foreach from=$boards item=item}}
                        {{if $item.type == 'zone' && $item.children}}
                        <optgroup label="{{$item.boardname}}">
                            {{foreach from=$item.children item=child}}
                            <option value="{{$child.boardid}}"{{if $tudu.boardid == $child.boardid}} selected="selected"{{/if}}{{if $child.isclassify && !$isforward}} _classify="1"{{/if}} _needconfirm="{{if $child.needconfirm}}1{{else}}0{{/if}}">{{$child.boardname}}</option>
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
                        <td width="90" {{if !$tudu.classid && !$classes}} style="display:none"{{/if}}><select style="width:90px;" name="classid" id="classid"{{if $divide}} disabled="disabled" _disabled="true"{{/if}}>
                        <option value="">--{{$LANG.none}}--</option>
                        {{if $classes}}
                        {{foreach from=$classes item=class}}
                        <option value="{{$class.classid}}"{{if $tudu.classid === $class.classid}} selected="selected"{{/if}} title="{{$class.classname}}">{{$class.classname}}</option>
                        {{/foreach}}
                        {{/if}}
                        </select></td>
                        <td class="info_forms info_input"><input class="input_text" name="subject" id="subject" type="text" value="{{$tudu.subject|escape:'html'}}" tabindex="1"{{if $board.protect || $divide}} readonly="true"{{/if}}{{if $divide}} disabled="disabled" _disabled="true"{{/if}} /></td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"><a href="javascript:void(0)" id="select-cc">{{$LANG.cc}}</a></td>
                        <td class="info_forms info_input" style="padding-right:10px;*"><input id="i-cc" tabindex="3" class="input_text" type="text" readonly="readonly" value="{{foreach from=$tudu.cc item=cc name=cc}}{{if !$smarty.foreach.cc.first}};{{/if}}{{$cc.0}}{{/foreach}}"{{if $divide}} disabled="disabled"{{/if}} /><input type="hidden" name="cc" id="cc" value="{{foreach item=item key=key from=$tudu.cc}}{{$item.3|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}" /></td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.starttime}}</td>
                        <td class="info_forms" width="185"><input type="text" tabindex="4" class="input_text" id="starttime" name="starttime" value="{{$tudu.starttime|date_format:'%Y-%m-%d'}}" readonly="readonly" style="width:178px;"{{if $divide}} disabled="disabled" _disabled="true"{{/if}} /></td>
                        <td class="info_txt">{{$LANG.epalsedtime}}</td>
                        <td class="info_forms" width="65"><input style="width:60px;" tabindex="5" class="input_text" name="totaltime" id="totaltime" value="{{if $tudu.totaltime}}{{math equation="x/3600" x=$tudu.totaltime}}{{/if}}" type="text"{{if $divide}} disabled="disabled" _disabled="true"{{/if}} /></td>
                        <td class="info_forms"><select style="width:60px" name=""><option>{{$LANG.hour}}</option></select></td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.endtime}}</td>
                        <td class="info_forms" style="width:185px;"><input type="text" tabindex="6" class="input_text" name="endtime" id="endtime" readonly="readonly" value="{{$tudu.endtime|date_format:'%Y-%m-%d'}}" style="width:178px;"{{if $divide}} disabled="disabled" _disabled="true"{{/if}} /></td>
                        <td class="info_txt">{{$LANG.tudu_percent}}</td>
                        <td class="info_forms info_input">
                           <input type="text" class="input_text" tabindex="7" id="percent" name="percent" value="{{if $isforward}}{{$tudu.selfpercent|default:0}}{{else}}{{$tudu.percent|default:0}}{{/if}}%"  style="width:60px;" disabled="disabled" />
                        </td>
                      </tr>
                    </table>
                </div>
                {{if !$divide}}
                <div class="info_box">
                    <div class="attlist">
                        <span class="add"><span class="icon icon_tpl"></span><a href="javascript:void(0)" name="tpllist" _textarea="content">{{$LANG.add_tpl_list}}</a></span>{{if $access.upload}}<span class="upload_btn"><span id="upload-btn"></span></span><span class="add"><span class="icon icon_add"></span><a href="javascript:void(0)" id="upload-link">{{$LANG.file_upload}}</a></span>{{/if}}{{if $access.upload && $user.maxnetdiskquota > 0}}<span class="add" id="netdisk-btn"><span class="icon icon_nd_attach"></span><a href="javascript:void(0)">{{$LANG.netdisk_attach}}</a></span>{{/if}}<span class="add" id="ch-map-btn"><span class="icon icon_map"></span><a href="javascript:void(0)">{{$LANG.map}}</a></span><span class="add" id="pic-btn"><span class="icon icon_photo"></span> <a href="javascript:void(0)" id="insert-pic">{{$LANG.picture}}</a></span>
                    </div>
                </div>
                {{/if}}
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
                        <td class="info_forms info_input"><textarea class="form_textarea" id="content" cols="" rows="">{{if !$isforward}}{{$tudu.content|tudu_format_content|escape:'html'}}{{/if}}</textarea><textarea id="postcontent" name="content" style="display:none;"></textarea></td>
                      </tr>
                    </table>
                </div>
                <div class="info_box">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"></td>
                        <td class="info_forms">
                            <div class="setting_box">
                                <input name="priority" type="checkbox" value="1" id="urgent"{{if $tudu.priority}} checked="checked"{{/if}}{{if $divide}} disabled="disabled"{{/if}} /><label for="urgent">{{$LANG.urgent}}</label>&nbsp;&nbsp;<input name="privacy" type="checkbox" value="1" id="secrecy"{{if $tudu.privacy}} checked="checked"{{/if}}{{if $divide}} disabled="disabled"{{/if}} /><label for="secrecy">{{$LANG.private}}</label>&nbsp;&nbsp;<label for="notifyall" title="{{$LANG.notify_tips}}"><input name="notifyall" type="checkbox" value="1" id="notifyall"{{if $tudu.notifyall}} checked="checked"{{/if}}{{if $divide}} disabled="disabled"{{/if}} />{{$LANG.remind_all}}</label><!-- &nbsp;&nbsp;<input name="" type="checkbox" value="" id="text"><label for="text">{{$LANG.pure_text}}</label>-->&nbsp;&nbsp;<label for="isauth" title="{{$LANG.auth_tips}}"><input type="checkbox" name="isauth" value="1" id="isauth" {{if $isdevide}} disabled="disabled"{{/if}}{{if $tudu.isauth}} checked="checked"{{/if}} />{{$LANG.foreign_auth}}</label>&nbsp;&nbsp;<label for="needconfirm" title="{{$LANG.tudu_need_confirm_tips}}"><input type="checkbox" name="needconfirm" value="1" id="needconfirm"{{if $tudu.needconfirm}} checked="checked"{{/if}} />{{$LANG.tudu_need_confirm}}</label>
                            </div>
                            <div class="cycle_wrap" id="block-private"{{if !$tudu.privacy}} style="display:none"{{/if}}>
                            	<div class="content_box3">
                            		<strong>{{$LANG.private_work}}</strong>
                            		<div class="line_bold"></div>
                            		<p class="gray">{{$LANG.private_tips}}</p><br />
                            		<input name="open_pwd" type="checkbox" id="open_pwd"{{if $tudu.password}} checked="checked"{{/if}} value="1" /><label for="open_pwd">{{$LANG.open_password}}</label><span class="gray">{{$LANG.open_pwd_tips}}</span>
                            		<div id="show_password" style="height:28px; line-height:28px;"><input name="password" type="text" id="password" class="input_text" style="width:178px;" maxlength="16" value="{{$tudu.password}}"{{if !$tudu.password}} disabled="disabled"{{/if}} onkeyup="this.value=this.value.replace(/[^\x01-\xff]+/,'')" /></div>
                            	</div>
                            </div>
                        </td>
                      </tr>
                    </table>
                </div>
                <div class="info_box" id="divide-panel">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.tudu_divide}}</td>
                        <td class="info_forms" style="padding-right:16px">
                        	<div id="child-edit" class="child-edit-box">
                        		<div id="ch-sim-edit">
                                <table cellpadding="0" cellspacing="0" border="0" name="edit-table">
                                <tr>
                                	<td width="60" align="right">{{$LANG.subject}}{{$LANG.cln}}</td>
                                	<td><input type="text" class="input_text" id="sim-subject" style="width:100%" /></td>
                                	<td width="80" align="right"><a href="javascript:void(0)" id="sim-to-select">{{$LANG.receiver}}</a>{{$LANG.cln}}</td>
                                	<td width="180"><input type="text" class="input_text" id="sim-to-text" style="width:100%" /><input type="hidden" id="sim-to" /></td>
                                </tr>
                                </table>
                                <table cellpadding="0" cellspacing="0" border="0" name="edit-table">
                                <tr>
                                	<td width="60"></td>
                                	<td><button type="button" name="save-child">{{$LANG.add_divide}}</button></td>
                                	<td width="180" align="right"><a href="javascript:void(0);" name="full">{{$LANG.child_full_edit}}</a></td>
                                </tr>
                                </table>
                                </div>

                                <div id="ch-full-edit" style="display:none">
                                <div class="info_box">
                                    <table cellspacing="0" cellpadding="0">
                                      <tr>
                                        <td class="info_txt">{{$LANG.belong_board}}</td>
                                        <td class="info_forms">
                                        <select id="ch-bid" name="ch-bid" tabindex="0">
                                        </select>
                                        </td>
                                      </tr>
                                    </table>
                                    <table cellspacing="0" cellpadding="0">
                                      <tr>
                                        <td class="info_txt">{{$LANG.subject}}</td>
                                        <td width="90" id="ch-class-td" style="display:none;"><select style="width:90px;" name="ch-classid" id="ch-classid" />
                                        <option value="">--{{$LANG.inherit_parent}}--</option>
                                        </select></td>
                                        <td class="info_forms info_input"><input class="input_text" id="ch-subject" name="ch-subject" type="text" value="" tabindex="51" /></td>
                                      </tr>
                                    </table>
                                    <table cellspacing="0" cellpadding="0">
                                      <tr>
                                        <td class="info_txt"><a href="javascript:void(0)" id="ch-select-to">{{$LANG.receiver}}</a></td>
                                        <td class="info_forms" style="padding-right:10px;*"><input id="ch-to-text" tabindex="52" class="input_text" type="text" readonly="readonly" value="" /><input type="hidden" id="ch-to" name="ch-to" value="" /></td>
                                      </tr>
                                    </table>
                                    <table cellspacing="0" cellpadding="0">
                                      <tr>
                                        <td class="info_txt"><a href="javascript:void(0)" id="ch-select-cc">{{$LANG.cc}}</a></td>
                                        <td class="info_forms info_input" style="padding-right:10px;*"><input id="ch-cc-text" tabindex="53" class="input_text" type="text" readonly="readonly" value="" /><input type="hidden" id="ch-cc" name="ch-cc" value="" /></td>
                                      </tr>
                                    </table>
                                    <table cellspacing="0" cellpadding="0">
                                      <tr>
                                        <td class="info_txt">{{$LANG.starttime}}</td>
                                        <td class="info_forms" width="185"><input type="text" tabindex="54" class="input_text" id="ch-starttime" name="ch-starttime" value="" readonly="readonly" style="width:178px;" /></td>
                                        <td class="info_txt">{{$LANG.epalsedtime}}</td>
                                        <td class="info_forms" width="65"><input style="width:60px;" tabindex="55" class="input_text" id="ch-totaltime" name="ch-totaltime" value="" type="text" /></td>
                                        <td class="info_forms"><select style="width:60px"><option>{{$LANG.hour}}</option></select></td>
                                      </tr>
                                    </table>
                                    <table cellspacing="0" cellpadding="0">
                                      <tr>
                                        <td class="info_txt">{{$LANG.endtime}}</td>
                                        <td class="info_forms" style="width:185px;"><input type="text" tabindex="56" class="input_text" id="ch-endtime" name="ch-endtime" readonly="readonly" value="" style="width:178px;" /></td>
                                        <td class="info_txt">{{$LANG.tudu_percent}}</td>
                                        <td class="info_forms info_input">
                                           <input type="text" class="input_text" tabindex="57" id="ch-percent" name="ch-percent" value=""  style="width:60px;" />
                                        </td>
                                      </tr>
                                    </table>
                                </div>
                                <div class="info_box">
                                    <div class="attlist">
                                        <span class="add"><span class="icon icon_tpl"></span><a href="javascript:void(0)" name="ch-tpllist" _textarea="ch-content">{{$LANG.add_tpl_list}}</a></span>{{if $access.upload}}<span class="upload_btn"><span id="ch-upload-btn"></span></span><span class="add"><span class="icon icon_add"></span><a href="javascript:void(0)" id="upload-link">{{$LANG.file_upload}}</a></span>{{/if}}{{if $access.upload && $user.maxnetdiskquota > 0}}<span class="add" id="ch-netdisk-btn"><span class="icon icon_nd_attach"></span><a href="javascript:void(0)">{{$LANG.netdisk_attach}}</a></span>{{/if}}<span class="add" id="map-btn"><span class="icon icon_map"></span><a href="javascript:void(0)">{{$LANG.map}}</a></span><span class="add" id="pic-btn"><span class="icon icon_photo"></span> <a href="javascript:void(0)" id="ch-insert-pic">{{$LANG.picture}}</a></span>
                                    </div>
                                </div>
                                <div id="ch-attach-list" class="info_box att_container" style="display:none">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                      <tr>
                                        <td class="info_txt"></td>
                                        <td class="bd_upload">
                                        </td>
                                      </tr>
                                    </table>
                                </div>
                                <div class="info_box">
                                    <table cellspacing="0" cellpadding="0">
                                      <tr>
                                        <td class="info_txt">{{$LANG.content}}</td>
                                        <td class="info_forms info_input"><textarea class="form_textarea" id="ch-content" name="ch-content" cols="" rows=""></textarea></td>
                                      </tr>
                                    </table>
                                </div>
                                <div class="info_box">
                                    <table cellspacing="0" cellpadding="0">
                                      <tr>
                                        <td class="info_txt"></td>
                                        <td class="info_forms">
                                            <div class="setting_box">
                                                <input type="checkbox" value="1" id="ch-priority" name="ch-priority" /><label for="ch-priority">{{$LANG.urgent}}</label>&nbsp;&nbsp;<input type="checkbox" value="1" id="ch-privacy" name="ch-privacy" /><label for="ch-privacy">{{$LANG.private}}</label>&nbsp;&nbsp;<label for="ch-notifyall" title="{{$LANG.notify_tips}}"><input type="checkbox" value="1" id="ch-notifyall" name="ch-notifyall" /></input>{{$LANG.remind_all}}</label><!-- &nbsp;&nbsp;<input name="" type="checkbox" value="" id="text"><label for="text">{{$LANG.pure_text}}</label>-->&nbsp;&nbsp;<label for="ch-isauth" title="{{$LANG.auth_tips}}"><input type="checkbox" name="ch-isauth" value="1" id="ch-isauth" />{{$LANG.foreign_auth}}</label>&nbsp;&nbsp;<label for="ch-needconfirm" title="{{$LANG.tudu_need_confirm_tips}}"><input type="checkbox" name="ch-needconfirm" value="1" id="ch-needconfirm" />{{$LANG.tudu_need_confirm}}</label>
                                            </div>
				                            <div class="cycle_wrap" id="ch-block-private" style="display:none">
				                            	<div class="content_box3">
				                            		<strong>{{$LANG.private_work}}</strong>
				                            		<div class="line_bold"></div>
				                            		<p class="gray">{{$LANG.private_tips}}</p><br />
				                            		<input name="ch-open_pwd" type="checkbox" id="ch-open_pwd" value="1" /><label for="ch-open_pwd">{{$LANG.open_password}}</label><span class="gray">{{$LANG.open_pwd_tips}}</span>
				                            		<div id="show_password" style="height:28px; line-height:28px;"><input name="ch-password" type="text" id="ch-password" class="input_text" style="width:178px;" maxlength="16" disabled="disabled" onkeyup="this.value=this.value.replace(/[^\x01-\xff]+/,'')" /></div>
				                            	</div>
				                            </div>
                                        </td>
                                      </tr>
                                    </table>
                                </div>
                                <div class="info_box" style="margin-top:5px;">
                                    <table cellpadding="0" cellspacing="0" border="0" name="edit-table">
                                    <tr>
                                    	<td width="70"></td>
                                    	<td><button type="button" name="save-child">{{$LANG.add_divide}}</button><button type="button" name="save-ch-tudu" _tid="" onclick="TuduGroup.sendTudu();" style="display:none;">{{$LANG.save_divide}}</button></td>
                                    	<td width="180" align="right"><a href="javascript:void(0);" name="simple">{{$LANG.child_simple_edit}}</a></td>
                                    </tr>
                                    </table>
                                </div>
                                </div>
                        	</div>
                            <div id="children-list" class="tudu_children_wrap">
                            	{{foreach item=child from=$children}}
                            	   {{if !$child.isdraft}}
                            	    <div id="child-{{$child.tuduid}}" class="tudu_children_item">
                            	    <div class="child_info">
                                    <table cellpadding="0" cellspacing="0" border="0" name="info-table" class="child_info_table">
                                    <tr>
                                        <td><span name="subject">{{$child.subject}}</span>
                                        	<input name="content-{{$child.tuduid}}" type="hidden" />
                                        </td>
                                        <td width="130">{{$LANG.receiver}}{{$LANG.cln}}<span name="to">{{foreach item=to from=$child.to name="to"}}{{$to.0}}{{if $smarty.foreach.to.index < count($child.to)}},{{/if}}{{/foreach}}</span></td>
                                        <td width="90" name="endtime" align="center">{{$child.endtime|default:'-'|date_format:'%Y-%m-%d'}}</td>
                                        <td width="80" align="right"><a href="javascript:void(0)" name="edit" onclick="TuduGroup.editNotDraftChild('{{$child.tuduid}}');">[{{$LANG.modify}}]</a>&nbsp;<span class="gray">[{{$LANG.delete}}]</span></td>
                                    </tr>
                                    </table>
                                    </div>
                                    </div>
                            	   {{/if}}
                            	{{/foreach}}
                            </div>
                        </td>
                      </tr>
                    </table>
                </div>
            </div>
        <div class="toolbar">
            <div><button class="btn" type="button" name="send">{{$LANG.send}}</button>{{if 0}}<button class="btn" type="button">{{$LANG.send_on_time}}</button>{{/if}}{{if !$tudu.tuduid || $tudu.isdraft || !$divide}}<button class="btn" type="button" name="save">{{$LANG.save_draft}}</button><button class="btn" type="button" name="preview">{{$LANG.preview}}</button>{{/if}}<span class="compose_msg"></span></div>
        </div>
    </div>
</form>

{{if $newwin}}
{{include file="compose^newwin_fix.tpl" type="task"}}
{{/if}}
{{*
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
*}}

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

{{* 子图度 *}}
<div id="child-tpl" class="child_info" style="display:none">
<input type="hidden" name="chidx[]" />
<input type="hidden" name="ftid" />
<input type="hidden" name="bid" />
<input type="hidden" name="classid" />
<input type="hidden" name="type" value="task" />
<input type="hidden" name="subject" />
<input type="hidden" name="to" />
<input type="hidden" name="to-text" />
<input type="hidden" name="cc" />
<input type="hidden" name="cc-text" />
<input type="hidden" name="totaltime" />
<input type="hidden" name="percent" />
<input type="hidden" name="starttime" />
<input type="hidden" name="endtime" />
<input type="hidden" name="content" />
<input type="hidden" name="priority" />
<input type="hidden" name="privacy" />
<input type="hidden" name="needconfirm" />
<input type="hidden" name="notifyall" />
<input type="hidden" name="password" />
<input type="hidden" name="open_pwd" />
<input type="hidden" name="isauth" />
<table cellpadding="0" cellspacing="0" border="0" name="info-table" class="child_info_table">
<tr>
	<td><a href="javascript:void(0)" name="subject"></a></td>
	<td width="130">{{$LANG.receiver}}{{$LANG.cln}}<span name="to"></span></td>
	<td width="90" name="endtime" align="center"></td>
	<td width="80" align="right"><a href="javascript:void(0)" name="edit">[{{$LANG.modify}}]</a>&nbsp;<a href="javascript:void(0)" name="delete">[{{$LANG.delete}}]</a></td>
</tr>
</table>
</div>

</body>
<script type="text/javascript">
$(function(){
    TOP.Frame.title('{{$LANG.tudu}}');
    TOP.Label.focusLabel();
    {{if !$tudu.tuduid || $tudu.isdraft}}
    TOP.Frame.hash('m=compose{{if $smarty.server.QUERY_STRING}}&{{$smarty.server.QUERY_STRING}}{{/if}}');
    {{else}}
    TOP.Frame.hash('m=view&tid={{$tudu.tuduid}}{{if $divide}}&divide=1{{/if}}');
    {{/if}}

    var h = $(window).height(),
    ch = $(document.body).height();

    var editorHeight = Math.max($('#content').height() + (h - ch - 15), 200);
    $('#content').css('height', editorHeight + 'px');
    _EDITOR = initEditor('#content');
    {{if $divide}}
    disableEditor('0');
    {{/if}}

    $('#board').change(function(){
    	if ($('#board option:selected').attr('_needconfirm') == 1) {
            $('#needconfirm').attr('checked', true);
            if (!$('#ch-bid option:selected').val()) {
            	$('#ch-needconfirm').attr('checked', true);
            }
        } else {
        	$('#needconfirm').attr('checked', false);
        	if (!$('#ch-bid option:selected').val()) {
            	$('#ch-needconfirm').attr('checked', false);
            }
        }
    	if ($('#board option:selected').attr('_classify') == 1) {
        	$('#classid').empty();
        } else {
            $('#classid').empty();
        	$('#classid').prepend('<option value="">--{{$LANG.none}}--</option>');
        }
        loadClasses(this.value, '#classid');
    });

    if ($('#board').val()) {
    	if ($('#board option:selected').attr('_classify') == 1) {
    		$('#classid option:first').remove();
        }
    }

    {{if $access.upload}}
    {{if !$divide}}
    // 初始化上传
    _UPLOAD = initAttachment({
        uploadUrl: '{{$options.sites.file}}{{$upload.cgi.upload}}',
        postParams: {'cookies': '{{$cookies}}'},
        fileSizeLimit: '{{$uploadsizelimit}}'
    }, $('#attach-list'), $('#attach-list td.bd_upload'));
    {{/if}}
    // 子任务上传
    TuduGroup.upload = initAttachment({
    	buttonPlaceholderId: 'ch-upload-btn',
        uploadUrl: '{{$options.sites.file}}{{$upload.cgi.upload}}',
        postParams: {'cookies': '{{$cookies}}'},
        fileSizeLimit: '{{$uploadsizelimit}}'
    }, $('#ch-attach-list'), $('#ch-attach-list td.bd_upload'));
    {{/if}}

    {{if !$divide}}
    var ccInput = new TOP.ContactInput({
        id: 'cc-input', target: $('#i-cc'), valuePlace: $('#cc'), group: true, jq: jQuery
    });

    initSelectLink('#select-cc', ccInput, $('#cc'), true);

    //$('#percent').stepper({step: 25, max:100, format: 'percent'});

    initTimePicker();
    {{/if}}

    TuduGroup.init();

    initPicInsert({'#insert-pic': _EDITOR, '#ch-insert-pic': TuduGroup._editor} {{if $access.upload}}, {
        postParams: {'cookies': '{{$cookies}}'},
        fileSizeLimit: '{{$uploadsizelimit}}',
        auth: '{{$upload.auth}}'
    }{{/if}});

    $('button[name="save"], button[name="send"]').click(function(){
        $('#action').val(this.name);

        if (!$('input[name="chidx[]"]').size()) {
            return TOP.showMessage(TOP.TEXT.NOT_ANY_TUDU_DIVIDE);
        }

        if (TuduGroup.currIndex !== null) {
			if (!confirm(TOP.TEXT.SUBMIT_DIVIDE_IN_MODIFY)) {
				return false;
			}
        }

        composeSubmit('#theform');
    });

    $('#mode-group :radio[name="mode"]').click(function(){
        $('div.method').hide();
        $('#mode-' + this.value).show();
    });

	// 私密任务div控制
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
	// 分工私密任务div控制
    $('#ch-privacy').click(function(){
    	var checked = this.checked;
        $('#ch-block-private').css('display', checked ? '' : 'none');
        if (checked) {
        	window.scrollTo(0, $('#ch-block-private').offset().top);
        }
    	if ($(this).attr('checked') == false) {
        	$('#ch-password').val('');
            $('#ch-open_pwd').attr('checked', false);
            $('#ch-password').attr('disabled', true);
        }
    });
    $('#ch-open_pwd').click(function(){
    	if ($(this).attr('checked') == true) {
			$("input[name='ch-password']").each(function() {
    			$(this).attr('disabled', false);
    			$('#ch-password').focus();
    		});
    	} else {
    	    $("input[name='ch-password']").each(function() {
    			$(this).attr('disabled', true);
    			$('#ch-password').val('');
    		});
    	}
    });

    initUnloadEvent('#theform'{{if !$tudu.tuduid || $tudu.isdraft}}, true{{/if}});
    {{if count($tudu) && (!$tudu.tuduid || $tudu.isdraft)}}_FORM_DATA = {};{{/if}}

    {{if $tudu.isdraft || !$tudu.tuduid}}
    autosaveTudu = new Tudu.AutoSave({
    	form: $('#theform'),
    	time: 30000,
    	func: Tudu.TuduSubmit,
    	forcesave: {{$tudu.autosave|default:0}}
    });
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

$('#totaltime').keyup(function(){
	if (this.value) {
		$(this).attr('_man', 1);
	} else {
		$(this).removeAttr('_man');
	}
});

$('#map-btn').click(function(){
	_EDITOR.showIframeModal('Google 地图','/googlemap/googlemap.html',function(v){_EDITOR.pasteHTML('<img src="'+v+'" />');},538,404);
});

{{foreach item=child from=$children}}
{{if $child.isdraft}}
TuduGroup.appendChild({
	ftid: '{{$child.tuduid}}',
	subject: '{{$child.subject}}',
	starttime: '{{$child.starttime|date_format:'%Y-%m-%d'}}',
	endtime: '{{$child.endtime|date_format:'%Y-%m-%d'}}',
	to: [{{foreach item=item from=$child.to name=to}}'{{$item.3}} {{$item.0}}'{{if !$smarty.foreach.to.last}},{{/if}}{{/foreach}}].join("\n"),
	'to-text': [{{foreach item=item from=$child.to name=totxt}}'{{$item.0}}'{{if !$smarty.foreach.totxt.last}},{{/if}}{{/foreach}}].join(","),
	cc: [{{foreach item=item from=$child.cc name=cc}}'{{$item.3}} {{$item.0}}'{{if !$smarty.foreach.cc.last}},{{/if}}{{/foreach}}].join("\n"),
	'cc-text': [{{foreach item=item from=$child.cc name=cctxt}}'{{$item.0}}'{{if !$smarty.foreach.cctxt.last}},{{/if}}{{/foreach}}].join(","),
	totaltime: '{{$child.totaltime}}',
	percent: '{{$child.percent}}',
	priority: '{{$child.priority}}',
	privacy: '{{$child.privacy}}',
	notifyall: '{{$child.notifyall}}',
	bid: '{{$child.boardid}}',
	classid: '{{$child.classid}}',
	password: '{{$child.password}}',
	open_pwd: '{{$child.open_pwd}}',
	isdraft: '{{$child.isdraft}}',
	needconfirm: '{{$child.needconfirm}}',
	isauth: '{{$child.isauth}}'
});
{{/if}}
{{/foreach}}
$('button[name="preview"]').click(function(){
    getFormPreview('#theform', '/tudu/preview', '_blank');
});
$('div.tab-panel-header a[href^="/tudu/modify"]').click(function(){
	getFormPreview('#theform', this.href, '_self');
	return false;
});

$('a[name="tpllist"]').click(function(e) {
	var textarea = $('a[name="tpllist"]').attr('_textarea'),
		boardId = $('#board').val();
	e.srcElement = $(this).parent('span.add');
	Tudu.Template.showMenu(e ,textarea, boardId);
    TOP.stopEventBuddle(e);
});

var filedialog = new FileDialog({id: 'netdisk-dialog'});
$('#netdisk-btn').click(function(){
    filedialog.show();
});
</script>
</html>
