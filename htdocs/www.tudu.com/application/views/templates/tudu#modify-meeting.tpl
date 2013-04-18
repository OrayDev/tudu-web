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
{{if !$isInvite && !$tudu.isdraft}}
LH = 'm=compose{{if $tudu.tuduid}}&tid={{$tudu.tuduid}}{{else}}&type=meeting{{/if}}';
{{else}}
LH = 'm=view&tid={{$tudu.tuduid}}{{if $isInvite}}&invite=1{{/if}}';
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
<script src="{{$options.sites.static}}/js/compose.js?1039" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/plugins.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/boardselector.js?1003" type="text/javascript"></script>

</head>
<body style="padding:0 5px 5px">
<form action="/compose/send" id="theform" method="post" class="tab_panel">
<input type="hidden" id="action" name="action" value="{{$action}}" />
<input type="hidden" id="type" name="type" value="meeting" />
<input type="hidden" id="issend" name="issend" value="1" />
<input type="hidden" id="ftid" name="ftid" value="{{$tudu.tuduid}}" />
<input type="hidden" id="classname" name="classname" value="{{$tudu.classname}}" />
<input type="hidden" id="cid" value="{{$tudu.classid}}" />
{{if $isInvite}}
<input type="hidden" id="myname" value="{{$user.truename}}" />
<input type="hidden" name="invite" value="1" />
{{/if}}
    {{include file="compose^tab.tpl" tab="meeting"}}
    <div class="tab-panel-body">
        <div class="toolbar">
            <div>
            	<button class="btn" type="button" name="send">{{$LANG.send}}</button>
            	{{if 0}}<button class="btn" type="button">{{$LANG.send_on_time}}</button>{{/if}}
            	{{if !$tudu.tuduid || $tudu.isdraft}}
            	<button class="btn" type="button" name="save">{{$LANG.save_draft}}</button>
            	<button class="btn" type="button" name="preview">{{$LANG.preview}}</button>
            	{{/if}}
            	{{if $tudu.tuduid && !$tudu.isdraft}}
                <button class="btn" type="button" name="back">{{$LANG.back}}</button>
    			{{/if}}
            	<span class="compose_msg"></span>
            </div>
        </div>
            <div class="readmailinfo" style="padding-top:5px;">
                <div class="info_box">
        	   	<table cellspacing="0" cellpadding="0">
        	        <tr>
        	        <td class="info_txt">&nbsp;</td>
        			<td class="info_forms">
        			    <a href="javascript:void(0)" id="add-cc" class="expand-link" style="margin-left:0">{{$LANG.add_cc}}</a></td>
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
                        <td width="90" id="class-td"{{if !$tudu.classid && !$classes}} style="display:none"{{/if}}><select style="width:90px;" name="classid" id="classid"{{if $tudu.classid && ($board.protect || $isforward) || $isInvite}} disabled="disabled"{{/if}}>
                        {{if $board && !$board.isclassify}}<option value="">--{{$LANG.none}}--</option>{{/if}}
                        {{if $classes}}
                        {{foreach from=$classes item=class}}
                        <option value="{{$class.classid}}"{{if $tudu.classid === $class.classid}} selected="selected"{{/if}} title="{{$class.classname}}">{{$class.classname}}</option>
                        {{/foreach}}
                        {{/if}}
                        </select></td>
                        <td class="info_forms info_input"><input class="input_text" tabindex="1" name="subject" id="subject" type="text" value="{{$tudu.subject|escape:'html'}}"{{if $board.protect || $isInvite}} readonly="true"{{/if}} maxlength="50" /></td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"><a href="javascript:void(0)" id="select-to">{{$LANG.attendee}}</a></td>
                        <td class="info_forms info_input" style="padding-right:10px"><input id="i-to" tabindex="2" class="input_text" type="text" readonly="readonly" value="{{if !$isInvite}}{{foreach from=$tudu.to item=to name=to}}{{if !$smarty.foreach.to.first}};{{/if}}{{$to.0}}{{/foreach}}{{/if}}" /><input type="hidden" name="to" id="to" value="{{if !$isInvite}}{{foreach item=item key=key from=$tudu.to}}{{$item.3|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}{{/if}}" /></td>
                      </tr>
                    </table>
                    <table id="row-cc" cellspacing="0" cellpadding="0"{{if !$tudu.cc}} style="display:none"{{/if}}>
                      <tr>
                        <td class="info_txt"><a href="javascript:void(0)" id="select-cc">{{$LANG.cc}}</a></td>
                        <td class="info_forms info_input" style="padding-right:10px;"><input id="i-cc" tabindex="3" class="input_text" type="text" readonly="readonly" value="{{foreach from=$tudu.cc item=cc name=cc}}{{if !$smarty.foreach.cc.first}};{{/if}}{{$cc.0}}{{/foreach}}" /><input type="hidden" name="cc" id="cc" value="{{foreach item=item key=key from=$tudu.cc}}{{$item.3|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}" /></td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.locate}}</td>
                        <td class="info_forms info_input"><input class="input_text" tabindex="1" name="location" id="location" type="text" value="{{$meeting.location|escape:'html'}}"{{if $board.protect}} readonly="true"{{/if}}{{if $isInvite}} disabled="disabled"{{/if}} /></td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.starttime}}</td>
                        <td class="info_forms"><input type="text" tabindex="3" class="input_text" name="startdate" id="startdate" readonly="readonly" value="{{if $tudu.starttime}}{{$tudu.starttime|date_format:'%Y-%m-%d'}}{{else}}{{$smarty.now|date_format:'%Y-%m-%d'}}{{/if}}" style="width:178px;"{{if $isInvite}} disabled="disabled"{{/if}} />
                        <select id="st" style="width:90px"{{if $meeting.isallday}} disabled="disabled"{{/if}}{{if $isInvite}} disabled="disabled"{{/if}}>
                            {{section name=starttime loop=48}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.starttime.index*1800}}
                            <option value="{{$timestamp|date_format:'%H:%M'}}"{{if ($tudu.starttime && date('H:i', $tudu.starttime) == date('H:i', $timestamp)) || date('H:i', $timestamp) == "09:00"}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H:%M'}}</option>
                            {{/section}}
                        </select>
                        <input type="hidden" name="starttime" id="starttime" value="{{if $tudu.starttime}}{{$tudu.starttime|date_format:'%Y-%m-%d %H:%M'}}{{else}}{{$smarty.now|date_format:'%Y-%m-%d 09:00'}}{{/if}}" />
                        <label for="allday"><input type="checkbox" name="allday" id="allday" value="1"{{if $meeting.isallday}} checked="checked"{{/if}}{{if $isInvite}} disabled="disabled"{{/if}} />{{$LANG.allday}}</label>
                        </td>
                      </tr>
                    </table>
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.endtime}}</td>
                        <td class="info_forms"><input type="text" tabindex="3" class="input_text" name="enddate" id="enddate" readonly="readonly" value="{{if $tudu.endtime}}{{$tudu.endtime|date_format:'%Y-%m-%d'}}{{else}}{{$smarty.now|date_format:'%Y-%m-%d'}}{{/if}}" style="width:178px;"{{if $isInvite}} disabled="disabled"{{/if}} />
                        <select id="et" style="width:90px"{{if $meeting.isallday}} disabled="disabled"{{/if}}{{if $isInvite}} disabled="disabled"{{/if}}>
                            {{section name=endtime loop=48}}
                            {{assign var=timestamp value=0|mktime:0:$smarty.section.endtime.index*1800}}
                            <option value="{{$timestamp|date_format:'%H:%M'}}"{{if ($tudu.starttime && date('H:i', $tudu.endtime) == date('H:i', $timestamp)) || date('H:i', $timestamp) == "10:00"}} selected="selected"{{/if}}>{{$timestamp|date_format:'%H:%M'}}</option>
                            {{/section}}
                        </select>
                        <input type="hidden" name="endtime" id="endtime" value="{{if $tudu.endtime}}{{$tudu.endtime|date_format:'%Y-%m-%d %H:%M'}}{{else}}{{$smarty.now|date_format:'%Y-%m-%d 10:00'}}{{/if}}" />
                        </td>
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
                            {{if !$invite}}
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
                            {{/if}}
                        </td>
                      </tr>
                    </table>
                </div>
                <div class="info_box">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt">{{$LANG.content}}</td>
                        <td class="info_forms info_input"><textarea class="form_textarea" id="content" cols="" rows="">{{if !$isInvite}}{{$tudu.content|tudu_format_content|escape:'html'}}{{/if}}</textarea><textarea id="postcontent" name="content" style="display:none;"></textarea></td>
                      </tr>
                    </table>
                </div>
                <div class="info_box">
                    <table cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="info_txt"></td>
                        <td class="info_forms">
                            <div class="setting_box">
                                <label for="notifyall" title="{{$LANG.meeting_notifyall_tips}}"><input name="notifyall" type="checkbox" value="1" id="notifyall"{{if $tudu.notifyall}} checked="checked"{{/if}}{{if $isInvite}} disabled="disabled"{{/if}} />{{$LANG.mobile_remind}}</label><label for="remindbefore" title="{{$LANG.meeting_remindbefore_tips}}">&nbsp;&nbsp;<input type="checkbox" value="1" name="remindbefore" id="remindbefore"{{if !$tudu.tuduid || $meeting.notifytime}} checked="checked"{{/if}}{{if $board.protect}} disabled="disabled"{{/if}}{{if $isInvite}} disabled="disabled"{{/if}} />{{$LANG.meeting_notify_setting}}</label>{{if !$tudu.tuduid || $tudu.isdraft || $cycle}}&nbsp;&nbsp;<input name="cycle" type="checkbox" value="1" id="cycle"{{if $cycle}} checked="checked"{{/if}}{{if $board.protect}} disabled="disabled"{{/if}}{{if $isInvite}} disabled="disabled"{{/if}} /><label for="cycle" title="{{$LANG.meeting_cycle_tips}}">{{$LANG.repeat_cycle}}</label>{{/if}}&nbsp;&nbsp;<label for="isauth" title="{{$LANG.auth_tips}}"><input type="checkbox" name="isauth" value="1" id="isauth"{{if $tudu.isauth}} checked="checked"{{/if}}{{if $board.protect}} disabled="disabled"{{/if}}{{if $isInvite}} disabled="disabled"{{/if}} />{{$LANG.foreign_auth}}</label>
                            </div>

                            <div class="cycle_wrap" id="block-extend"{{if $tudu.tuduid && !$cycle && !$meeting.notifytime}} style="display:none"{{/if}}>
                                <div class="content_box3">
                                    <div id="block-notify"{{if $tudu.tuduid && !$meeting.notifytime}} style="display:none"{{/if}}>
                                        <strong>{{$LANG.meeting_notify_setting}}</strong>
                                        <div class="line_bold"></div>
                                        <p class="gray">{{$LANG.meeting_notify_tips}}</p>
                                        <table cellspacing="0" cellpadding="0" class="setting_table">
                                          <tr>
                                            <td valign="top" width="70">{{$LANG.notify_setting}}{{$LANG.cln}}</td>
                                            <td>
                                                <select name="notifytype" style="width:150px"{{if $board.protect}} disabled="disabled"{{/if}}>
                                                <option value="1"{{if $meeting.notifytype===1}} selected="seelcted"{{/if}}>{{$LANG.meeting_notify_5min}}</option>
                                                <option value="2"{{if $meeting.notifytype==2 || !$meeting.notifytype}} selected="seelcted"{{/if}}>{{$LANG.meeting_notify_10min}}</option>
                                                <option value="4"{{if $meeting.notifytype==4}} selected="seelcted"{{/if}}>{{$LANG.meeting_notify_15min}}</option>
                                                <option value="8"{{if $meeting.notifytype==8}} selected="seelcted"{{/if}}>{{$LANG.meeting_notify_30min}}</option>
                                                <option value="16"{{if $meeting.notifytype==16}} selected="seelcted"{{/if}}>{{$LANG.meeting_notify_1hour}}</option>
                                                <option value="32"{{if $meeting.notifytype==32}} selected="seelcted"{{/if}}>{{$LANG.meeting_notify_2hour}}</option>
                                                <option value="64"{{if $meeting.notifytype==64}} selected="seelcted"{{/if}}>{{$LANG.meeting_notify_prev9}}</option>
                                                <option value="128"{{if $meeting.notifytype==128}} selected="seelcted"{{/if}}>{{$LANG.meeting_notify_prev14}}</option>
                                                <option value="256"{{if $meeting.notifytype==256}} selected="seelcted"{{/if}}>{{$LANG.meeting_notify_prev18}}</option>
                                                </select>
                                            </td>
                                          </tr>
                                        </table>
                                    </div>
                                    <div id="block-cycle"{{if !$cycle}} style="display:none"{{/if}}>
                                    {{if $cycle}}
                                    <input type="hidden" name="cycleid" value="{{$cycle.cycleid}}" />
                                    {{/if}}
                                    <strong>{{$LANG.repeat_cycle}}</strong>
                                    <div class="line_bold"></div>
                                    <p class="gray">{{$LANG.cycle_tips}}</p>
                                    <table cellspacing="0" cellpadding="0" class="setting_table">
                                      <tr>
                                        <td valign="top" width="125" align="right">{{$LANG.cycle_mode}}{{$LANG.cln}}</td>
                                        <td>
                                            <div id="mode-group" class="mode">
                                                <p><input name="mode" type="radio" value="day" id="day"{{if !$cycle || $cycle.mode == 'day'}} checked="checked"{{/if}} /><label for="day">{{$LANG.cycle_mode_day}}</label></p>
                                                <p><input name="mode" type="radio" value="week" id="week"{{if $cycle.mode == 'week'}} checked="checked"{{/if}} /><label for="week">{{$LANG.cycle_mode_week}}</label></p>
                                                <p><input name="mode" type="radio" value="month" id="month"{{if $cycle.mode == 'month'}} checked="checked"{{/if}} /><label for="month">{{$LANG.cycle_mode_month}}</label></p>
                                            </div>
                                            <div class="method" id="mode-day"{{if $cycle && $cycle.mode != 'day'}} style="display:none"{{/if}}>
                                                <p><input name="type-day" type="radio" value="1" id="mode-1"{{if !$cycle || $cycle.type == 1}} checked="checked"{{/if}} /><label for="mode-1">{{$LANG.cycle_every}}</label> <input style="width:40px;" class="input_text" value="{{$cycle.day|default:1}}" type="text" name="day-1-day" id="day-1-day" /> {{$LANG.day}}</p>
                                                <p><input name="type-day" type="radio" value="2" id="mode-2"{{if $cycle.mode == 'day' && $cycle.type == 2}} checked="checked"{{/if}} /><label for="mode-2">{{$LANG.cycle_every_workday}}</label></p>
                                                <p><input name="type-day" type="radio" value="3" id="mode-3"{{if $cycle.mode == 'day' && $cycle.type == 3}} checked="checked"{{/if}} /><label for="mode-3">{{$LANG.cycle_every_complete}}</label> <input style="width:40px;" class="input_text" value="{{$cycle.day|default:1}}" type="text" name="day-3-day" id="day-3-day" /> {{$LANG.day}}{{$LANG.cycle_recreate}}</p>
                                            </div>
                                            <div class="method" id="mode-week"{{if $cycle.mode != 'week'}} style="display:none"{{/if}}>
                                                <p><input name="type-week" type="radio" value="1" id="mode-week-1"{{if !$cycle || $cycle.type == 1}} checked="checked"{{/if}} /><label for="mode-week-1">{{$LANG.repeat_cycle_is}}</label> <input style="width:30px;" name="week-1-week" id="week-1-week" class="input_text" value="{{$cycle.week|default:1}}" type="text" /> {{$LANG.week}}{{$LANG.cycle_after}}</p>
                                                <div class="weeks-group">
                                                    <label for="weekday-0"><input type="checkbox" name="week-1-weeks[]" value="0" id="weekday-0"{{if is_array($cycle.weeks) && in_array(0, $cycle.weeks)}} checked="checked"{{/if}} />{{$LANG.week_0}}</label>
                                                    <label for="weekday-1"><input type="checkbox" name="week-1-weeks[]" value="1" id="weekday-1"{{if (is_array($cycle.weeks) && in_array(1, $cycle.weeks)) || !$cycle}} checked="checked"{{/if}} />{{$LANG.week_1}}</label>
                                                    <label for="weekday-2"><input type="checkbox" name="week-1-weeks[]" value="2" id="weekday-2"{{if is_array($cycle.weeks) && in_array(2, $cycle.weeks)}} checked="checked"{{/if}} />{{$LANG.week_2}}</label>
                                                    <label for="weekday-3"><input type="checkbox" name="week-1-weeks[]" value="3" id="weekday-3"{{if is_array($cycle.weeks) && in_array(3, $cycle.weeks)}} checked="checked"{{/if}} />{{$LANG.week_3}}</label>
                                                    <label for="weekday-4"><input type="checkbox" name="week-1-weeks[]" value="4" id="weekday-4"{{if is_array($cycle.weeks) && in_array(4, $cycle.weeks)}} checked="checked"{{/if}} />{{$LANG.week_4}}</label>
                                                    <label for="weekday-5"><input type="checkbox" name="week-1-weeks[]" value="5" id="weekday-5"{{if is_array($cycle.weeks) && in_array(5, $cycle.weeks)}} checked="checked"{{/if}} />{{$LANG.week_5}}</label>
                                                    <label for="weekday-6"><input type="checkbox" name="week-1-weeks[]" value="6" id="weekday-6"{{if is_array($cycle.weeks) && in_array(6, $cycle.weeks)}} checked="checked"{{/if}} />{{$LANG.week_6}}</label>
                                                </div>
                                                <p><input name="type-week" type="radio" value="3" id="mode-week-3"{{if $cycle.type == 3}} checked="checked"{{/if}}><label for="mode-week-3"{{if $cycle.mode == 'day' && $cycle.type == 3}} checked="checked"{{/if}} />{{$LANG.cycle_every_complete}}</label> <input style="width:30px;" class="input_text" value="{{$cycle.week|default:1}}" type="text" name="week-3-week" id="week-3-week" /> {{$LANG.week}}{{$LANG.cycle_recreate}}</p>
                                            </div>
                                            <div class="method" id="mode-month"{{if $cycle.mode != 'month'}} style="display:none"{{/if}}>
                                                <p><input name="type-month" type="radio" value="1" id="mode-month-1"{{if !$cycle || $cycle.type == 1}} checked="checked"{{/if}} /><label for="mode-month-1">{{$LANG.cycle_every}}</label> <input style="width:30px;" class="input_text" value="{{$cycle.month|default:1}}" name="month-1-month" id="month-1-month" type="text" /> {{$LANG.cycle_month}}{{$LANG.cycle_number}}<input type="text" style="width:40px;" class="input_text" value="{{$cycle.day|default:1}}" name="month-1-day" id="month-1-day" />  {{$LANG.day}}</p>
                                                <p><input name="type-month" type="radio" value="2" id="mode-month-2"{{if $cycle.type == 2}} checked="checked"{{/if}} /><label for="mode-month-2">{{$LANG.cycle_every}}</label> <input style="width:30px;" class="input_text" value="{{$cycle.month|default:1}}" name="month-2-month" id="month-2-month" type="text" /> {{$LANG.cycle_month}}{{$LANG.cycle_after}} <select name="month-2-at" style="width:80px">
                                                <option value="1"{{if $cycle.at == 1}} selected="selected"{{/if}}>{{$LANG.month_week_first}}</option>
                                                <option value="2"{{if $cycle.at == 2}} selected="selected"{{/if}}>{{$LANG.month_week_second}}</option>
                                                <option value="3"{{if $cycle.at == 3}} selected="selected"{{/if}}>{{$LANG.month_week_third}}</option>
                                                <option value="4"{{if $cycle.at == 4}} selected="selected"{{/if}}>{{$LANG.month_week_forth}}</option>
                                                <option value="0"{{if $cycle.at == 0}} selected="selected"{{/if}}>{{$LANG.month_week_last}}</option>
                                                </select> <select name="month-2-what" style="width:60px">
                                                <option value="workday"{{if $cycle.what == 'workday'}} selected="selected"{{/if}}>{{$LANG.work_day}}</option>
                                                <option value="weekend"{{if $cycle.what == 'weekend'}} selected="selected"{{/if}}>{{$LANG.week_end}}</option>
                                                <option value="day"{{if $cycle.what == 'day'}} selected="selected"{{/if}}>{{$LANG.cycle_what_days}}</option>
                                                <option value="mon"{{if $cycle.what == 'mon'}} selected="selected"{{/if}}>{{$LANG.week_1}}</option>
                                                <option value="tue"{{if $cycle.what == 'tue'}} selected="selected"{{/if}}>{{$LANG.week_2}}</option>
                                                <option value="wed"{{if $cycle.what == 'wed'}} selected="selected"{{/if}}>{{$LANG.week_3}}</option>
                                                <option value="thu"{{if $cycle.what == 'thu'}} selected="selected"{{/if}}>{{$LANG.week_4}}</option>
                                                <option value="fri"{{if $cycle.what == 'fri'}} selected="selected"{{/if}}>{{$LANG.week_5}}</option>
                                                <option value="sat"{{if $cycle.what == 'sat'}} selected="selected"{{/if}}>{{$LANG.week_6}}</option>
                                                <option value="sun"{{if $cycle.what == 'sun'}} selected="selected"{{/if}}>{{$LANG.week_0}}</option>
                                                </select></p>
                                                <p><input name="type-month" type="radio" value="3" id="mode-month-3"{{if $cycle.type == 3}} checked="checked"{{/if}} /><label for="mode-month-3">{{$LANG.cycle_every_complete}}</label> <input style="width:30px;" class="input_text" value="{{$cycle.month|default:1}}" type="text" name="month-3-month" id="month-3-month" /> {{$LANG.cycle_month}}{{$LANG.cycle_recreate}}</p>
                                            </div>
                                            <div class="clear"></div>
                                        </td>
                                      </tr>
                                    </table>
                                    <div class="line_d"></div>
                                    <table cellspacing="0" cellpadding="0" class="setting_table">
                                      <tr>
                                        <td valign="top" width="125" align="right">{{$LANG.repeat_range}}{{$LANG.cln}}</td>
                                        <!-- <td width="200" valign="top">开始 <select style="width:170px;" name=""><option>2010年5月12日</option></select></td> -->
                                        <td>
                                            <div>
                                                <p><input name="endtype" type="radio" value="0" id="endtype-0"{{if !$cycle || $cycle.endtype == 0}} checked="checked"{{/if}} /><label for="endtype-0">{{$LANG.deadline_none}}</label></p>
                                                <p><input name="endtype" type="radio" value="1" id="endtype-1"{{if $cycle.endtype == 1}} checked="checked"{{/if}} /><label for="endtype-1">{{$LANG.repeat}}</label> <input type="text" class="input_text" id="endcount" name="endcount" value="{{$cycle.endcount|default:1}}"  style="width:40px;" /> {{$LANG.cycle_times}}</p>
                                                <p><input name="endtype" type="radio" value="2" id="endtype-2"{{if $cycle.endtype == 2}} checked="checked"{{/if}} /><label for="endtype-2">{{$LANG.cycle_endtime}} </label> <input type="text" class="input_text" id="enddate" name="enddate" value="{{if $cycle.enddate}}{{$cycle.enddate|date_format:'%Y-%m-%d'}}{{else}}{{math|date_format:'%Y-%m-%d' equation="86400*365+x" x=$smarty.now}}{{/if}}" readonly="readonly" style="width:120px;" /></p>
                                            </div>
                                        </td>
                                      </tr>
                                    </table>
                                    <div class="line_d"></div>
                                    <table cellspacing="0" cellpadding="0" class="setting_table">
                                      <tr>
                                        <td valign="top" width="125" align="right">{{$LANG.tudu_display_date}}{{$LANG.cln}}</td>
                                        <td>
                                        	<div>
                                        		<p><label for="displaydate-1" title="{{$LANG.show_starttime_tips}}"><input name="displaydate" type="radio" value="1" id="displaydate-1"{{if $cycle.displaydate == 1}} checked="checked"{{/if}} />{{$LANG.show_starttime}}</label></p>
                                        		<p><label for="displaydate-0" title="{{$LANG.hide_starttime_tips}}"><input name="displaydate" type="radio" value="0" id="displaydate-0"{{if !$cycle || $cycle.displaydate == 0}} checked="checked"{{/if}} />{{$LANG.hide_starttime}}</label></p>
                                        	</div>
                                        </td>
                                      </tr>
                                    </table>

                                    <div class="line_d"></div>
                                    <table cellspacing="0" cellpadding="0" class="setting_table">
                                      <tr>
                                        <td valign="top" width="125" align="right">{{$LANG.cycle_keep_attach}}{{$LANG.cln}}</td>
                                        <td>
                                            <div>
                                                <p><label for="keepattach-1"><input name="iskeepattach" type="radio" value="1" id="keepattach-1"{{if !$cycle || $cycle.iskeepattach == 1}} checked="checked"{{/if}} />{{$LANG.yes}}<span class="gray">({{$LANG.keep_attach_tips}})</span></label></p>
                                                <p><label for="keepattach-0"><input name="iskeepattach" type="radio" value="0" id="keepattach-0"{{if $cycle && $cycle.iskeepattach == 0}} checked="checked"{{/if}} />{{$LANG.no}}</label></p>
                                            </div>
                                        </td>
                                      </tr>
                                    </table>
                                    </div>
                                </div>

                            </div>
                        </td>
                      </tr>
                    </table>
                </div>
            </div>
        <div class="toolbar">
            <div>
            	<button class="btn" type="button" name="send">{{$LANG.send}}</button>
            	{{if 0}}<button class="btn" type="button">{{$LANG.send_on_time}}</button>{{/if}}
            	{{if !$tudu.tuduid || $tudu.isdraft}}
            	<button class="btn" type="button" name="save">{{$LANG.save_draft}}</button>
            	<button class="btn" type="button" name="preview">{{$LANG.preview}}</button>
            	{{/if}}
            	{{if $tudu.tuduid && !$tudu.isdraft}}
                <button class="btn" type="button" name="back">{{$LANG.back}}</button>
    			{{/if}}
            	<span class="compose_msg"></span>
            </div>
        </div>
    </div>
</form>
{{if 0}}
{{include file="compose^newwin_fix.tpl" type="discuss"}}
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
    TOP.Frame.title('{{$LANG.meeting}}');
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

    var editorCss = {};
    {{if $user.option.fontfamily || $user.option.fontsize}}
    editorCss = {
        'font-family':'{{$user.option.fontfamily|default:'SimSun'}}',
        'font-size':'{{$user.option.fontsize|default:'12px'}}'
    };
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

    $('#st').change(function(){
        if ($('#startdate').val() == $('#enddate').val() && this.value > $('#et').val()) {
             $('#et').val($('#st').val());
        }
    });

    $('#et').change(function(){
        if ($('#startdate').val() == $('#enddate').val() && this.value < $('#st').val()) {
            this.value = $('#st').val();
        }
    });

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
            bid  = item ? item.boardid : null;;

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
            loadClasses(this.getValue(), '#classid');
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

    var isInvite = $('#theform :hidden[name="invite"]').size();
    var toInput = new TOP.ContactInput({
        id: 'to-input', target: $('#i-to'), valuePlace: $('#to'), group: true,
        onUpdate: function() {
            if (isInvite) {
                var to = this._settings.valuePlace.val().split("\n"), toArr = [], names = [];
                for (var i = 0, c = to.length; i < c; i++) {
                    var a = to[i].split(' ');
                    if (a[1]) {
                        toArr.push({email: a[0], name: a[1]});
                        names.push(a[1]);
                    }
                }

                var source = _EDITOR.getSource();

                var div = $('<div>');
                div.html(source);

                if (!toArr.length) {
                    div.find('p[_name="invite"]').remove();
                } else {
                    var text = TOP.formatString(TOP.TEXT.INVITE_INFO, $('#myname').val(), names.join(','));
                    var html = '<strong>'+TOP.TEXT.INVITE+TOP.TEXT.CLN+'</strong><span style="color:#aaa">'+text+'</span>';

                    if (div.find('p[_name="invite"]').size()) {
                        div.find('p[_name="invite"]').html(html);
                    } else {
                        div.prepend('<p _name="invite">'+html+'</p><br />');
                    }
                }

                _EDITOR.setSource(div.html());
            }
        },
        jq: jQuery
    });
    initSelectLink('#select-to', toInput, $('#to'), true);
    var ccInput = new TOP.ContactInput({
        id: 'cc-input', target: $('#i-cc'), valuePlace: $('#cc'), group: true, jq: jQuery
    });
    initSelectLink('#select-cc', ccInput, $('#cc'), true);

    _START_PICKER = $('#startdate').datepick({
        showOtherMonths: true,
        selectOtherMonths: true,
        showAnim: 'slideDown',
        showSpeed: 'fast',
        firstDay: 0,
        onSelect: function(dates){
            $('#enddate').datepick('option', {minDate: dates});
            $('#starttime').val($('#startdate').val() + ' ' + $('#st:enabled').val());
            $('#endtime').val($('#enddate').val() + ' ' + $('#et:enabled').val());
        }
    });

    _END_PICKER = $('#enddate').datepick({
        minDate: new Date(),
        showOtherMonths: true,
        selectOtherMonths: true,
        showAnim: 'slideDown',
        showSpeed: 'fast',
        firstDay: 0,
        onSelect: function(dates){
            $('#startdate').datepick('option', {maxDate: dates});
            $('#endtime').val($('#enddate').val() + ' ' + $('#et:enabled').val());
            $('#starttime').val($('#startdate').val() + ' ' + $('#st:enabled').val());
        }
    });

    $('button[name="save"], button[name="send"]').click(function(){
        $('#action').val(this.name);
        var to = toInput.getItems(),
			i = 0;

        to.each(function (){
        	if ($(this).attr('title') == TOP.TEXT.INVALID_TO_USER) {
        		i++;
        	}
		});
        if (i >0) {return TOP.showMessage(TOP.TEXT.TUDU_ACCEPTER_EMAIL_ERROR);}

        composeSubmit('#theform');
    });

    // cycle
    $('#endcount').stepper({step: 1, max:100, min: 1});
    $('#day-1-day').stepper({step: 1, max:365, min: 1});
    $('#day-3-day').stepper({step: 1, max:365, min: 1});
    $('#week-1-week').stepper({step: 1, max:54, min: 1});
    $('#week-3-week').stepper({step: 1, max:54, min: 1});
    $('#month-1-month').stepper({step: 1, max:12, min: 1});
    $('#month-1-day').stepper({step: 1, max:365, min: 1});
    $('#month-2-month').stepper({step: 1, max:12, min: 1});
    $('#month-3-month').stepper({step: 1, max:12, min: 1});

    $('#cycle').click(function(){
        var checked = this.checked;
        $('#block-extend').css('display', checked || $('#block-notify:visible').size() ? '' : 'none');
        $('#block-cycle').css('display', checked ? '' : 'none');
        if (checked) {
            window.scrollTo(0, $('#block-cycle').offset().top);
        }
    });

    $('#remindbefore').click(function(){

        var checked = this.checked;
        $('#block-extend').css('display', checked || $('#block-cycle:visible').size() ? '' : 'none');
        $('#block-notify').css('display', checked ? '' : 'none');
        if (checked) {
            window.scrollTo(0, $('#block-notify').offset().top);
        }
    });

    $('#mode-group :radio[name="mode"]').click(function(){
        $('div.method').hide();
        $('#mode-' + this.value).show();
    });
    $('#classid').change(function(){
        var o = $(this);
        if (o.val()) {
            $('#classname').val($(this).find('option:selected').text());
        } else {
            $('#classname').val('');
        }
    });

    $('#allday').click(function(){
        $('#st, #et').attr('disabled', this.checked);
        if (this.checked) {
            $('#starttime').val($('#startdate').val());
            $('#endtime').val($('#enddate').val());
        } else {
        	$('#starttime').val($('#startdate').val() + ' ' +  $('#st:enabled').val());
            $('#endtime').val($('#enddate').val() + ' ' + $('#et:enabled').val());
        }
    });

    $('#st').change(function(){
    	$('#starttime').val($('#startdate').val() + ' ' +  $('#st:enabled').val());
    });

    $('#et').change(function(){
    	$('#endtime').val($('#enddate').val() + ' ' + $('#et:enabled').val());
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

	var IS_CC = false;
	{{if !$tudu.tuduid || $tudu.isdraft}}
	IS_CC   = TOP.Cookie.get('MEETING-EXP-CC');
	IS_CC   = IS_CC === null || IS_CC == 1;

	if (IS_CC) $('#row-cc').show();
	{{/if}}
	var tools  = {cc: {{if $tudu.cc}}true{{else}}IS_CC{{/if}}};
	var expand = {cc: false};

	if (tools.cc) {
		$('#add-cc').text(TOP.TEXT.DELETE_CC);
		expand.cc = true;
	}

	$('#add-cc').bind('click', function(){
		if ($(this).hasClass('disabled')) {
			return ;
		}

		toggleCc();
	});

	function toggleCc() {
		if (!expand.cc) {
			$('#cc, #i-cc').attr('disabled', false);
			$('#row-cc').show();
			$('#add-cc').text(TOP.TEXT.DELETE_CC);
		} else {
			$('#cc, #i-cc').attr('disabled', true);
			$('#row-cc').hide();
			$('#add-cc').text(TOP.TEXT.ADD_CC);
		}

		expand.cc = !expand.cc;
		TOP.Cookie.set('MEETING-EXP-CC', expand.cc ? 1 : 0, {expires: 86400000 * 365});
	}
});
</script>
</html>
