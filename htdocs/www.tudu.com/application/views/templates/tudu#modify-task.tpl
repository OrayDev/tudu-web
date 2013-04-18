<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
{{if $newwin}}
{{include file="^newwin.tpl"}}
{{else}}
<script type="text/javascript">
<!--
var LH = '';
{{if !$isforward && !$isreview && !$tudu.isdraft}}
LH = 'm=compose{{if $smarty.server.QUERY_STRING}}&{{$smarty.server.QUERY_STRING}}{{/if}}';
{{else}}
LH = 'm=view&tid={{$tudu.tuduid}}{{if $isfroward}}&forward=1{{/if}}{{if $isapply}}&apply=1{{/if}}{{if $isreview}}&review=1{{/if}}';
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
<script src="{{$options.sites.static}}/js/tudu2.js?1060" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/plugins.js?1004" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/boardselector.js?1004" type="text/javascript"></script>
</head>

<body style="padding:0 5px 5px">
<form action="/compose-tudu/send" id="theform" method="post" class="tab_panel">
<input type="hidden" id="action" name="action" value="{{$action}}" />
<input type="hidden" id="type" name="type" value="task" />
<input type="hidden" id="savetime" value="" />
{{if $isforward}}
<input type="hidden" id="myname" value="{{$user.truename|escape:'html'}}" />
<input type="hidden" name="forward" value="1" />
{{/if}}
{{if $isdivide}}
<input type="hidden" name="divide" value="1" />
{{/if}}
{{if $isapply}}
<input type="hidden" name="apply" value="1" />
{{/if}}
{{if $isreview}}
<input type="hidden" name="review" value="1" />
<input type="hidden" name="agree" value="1" />
{{/if}}
{{if $flowid}}
<input type="hidden" id="flow-id" value="{{$flowid}}" />
<input type="hidden" id="flow-bid" value="{{$board.boardid}}" />
{{/if}}
<input type="hidden" id="ftid" name="ftid" value="{{$tudu.tuduid}}" />
<input type="hidden" id="classname" name="classname" value="{{$tudu.classname|escape:'html'}}" />
<input type="hidden" id="issend" name="issend" value="1" />
<input type="hidden" id="cid" value="{{$tudu.classid}}" />

{{include file="compose^tab.tpl" tab="tudu"}}

<div class="tab-panel-body">
	{{strip}}
	<div class="toolbar">
        <div>
        	<button class="btn" type="button" name="send">{{$LANG.send}}</button>
        	{{if $tudu.tuduid && !$tudu.isdraft}}
            <button class="btn" type="button" name="back">{{$LANG.back}}</button>
            {{/if}}
			{{if !$tudu.tuduid || $tudu.isdraft}}
			<button class="btn" type="button" name="save">{{$LANG.save_draft}}</button>
			<button class="btn" type="button" name="preview">{{$LANG.preview}}</button>
			{{/if}}
			<span class="compose_msg"></span>
		</div>
    </div>
	{{/strip}}

	<div class="readmailinfo" style="padding-top:5px;">
	   {{if $isreview}}
	   <div class="msg" style="margin:0 10px;"><p>{{$LANG.agree_edit_hint}}</p></div>
	   {{/if}}
	   <div class="msg" style="margin:0 10px;{{if (!$tudu.flowid && !$steps) || $isforward}}display:none;{{/if}}" id="flow-steps">
	   {{if $tudu.tuduid && $tudu.isdraft}}
	   {{$flowhtml}}
	   {{else}}
	   <span>工作流程：</span>
	   {{foreach from=$steps name=step item=item}}
	   {{if $item.future}}
	   <span class="icon icon_flow_arrow"></span>{{/if}}
	   {{if $item.type == 1}}
	   {{foreach from=$item.users item=u}}
	   {{if $pidx && $sidx}}
	   <span class="icon{{if $pidx != $u.processindex || $sidx != $u.stepid}} icon_flow_arrow{{else}} icon_flow_plus{{/if}}"></span>
	   {{/if}}
	   {{assign var=pidx value=$u.processindex}}
	   {{assign var=sidx value=$u.stepid}}
	   {{if !$u.future || $u.status >= 2}}
	   <span title="<{{$u.email}}>{{$u.truename}}">{{$u.truename}}
	   {{if $u.status == 2}}({{$LANG.agree}}){{elseif $u.status == 3}}({{$LANG.disagree}}){{else}}({{$LANG.wait_review}}){{/if}}</span>
	   {{else}}
	   <span title="<{{$u.email}}>{{$u.truename}}">{{$u.truename}}({{$LANG.future_review}})</span>
	   {{/if}}
	   {{/foreach}}
	   {{else}}
	   {{if count($item.users) > 1}}
	   <span title="{{foreach from=$item.users name=stepuser item=user}}<{{$user.email}}>{{$user.truename}}{{if $smarty.foreach.stepuser.index < count($item.users) - 1}},{{/if}}{{/foreach}}">{{$LANG.multi_accepter}}</span>
	   {{else}}
	   <span title="<{{$item.users.0.email}}>{{$item.users.0.truename}}">{{$item.users.0.truename}}</span>
	   {{/if}}
	   {{/if}}
	   {{/foreach}}
	   {{/if}}
	   </div>
	   {{if  !$isapply}}
	   <div class="info_box">
	   	<table cellspacing="0" cellpadding="0">
	        <tr>
	        <td class="info_txt">&nbsp;</td>
	        {{strip}}
			<td class="info_forms">
			{{if !$children && !$isdivide}}<a href="javascript:void(0)" class="expand-link" id="add-reviewer" style="margin-left:0">{{$LANG.add_review}}</a>|{{/if}}
			<a href="javascript:void(0)" id="add-cc" class="expand-link"{{if $children || $isdivide}} style="margin-left:0"{{/if}}>{{$LANG.add_cc}}</a>-<a href="javascript:void(0)" id="add-bcc" class="expand-link">{{$LANG.add_bcc}}</a>
			{{if !$isdivide && !$isreview}}|<a href="javascript:void(0)" id="add-date" class="expand-link">{{$LANG.add_date}}</a>{{/if}}
			{{if $tudu.tuduid && !$tudu.isdraft && !$tudu.istudugroup && !$isdivide && !$isforward && !$isreview}}|<a href="javascript:void(0)" id="add-percent" class="expand-link{{if $issynchro}} gray{{/if}}">{{$LANG.add_percent}}</a>{{/if}}
			{{if !$isforward && !$isdivide && !$isreview}}|<a href="javascript:void(0)" id="add-divide" class="expand-link">{{$LANG.add_divide}}</a>{{/if}}
			</td>
			{{/strip}}
			</tr>
		</table>
	   </div>
	   {{/if}}

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

        <table id="tudu-flow" cellspacing="0" cellpadding="0"{{if !$tudu.flowid && !$flows}} style="display:none;"{{/if}}>
          <tr>
            <td class="info_txt">工作流</td>
            <td class="info_forms info_input" style="padding-right:10px;">
              {{if $tudu.tuduid && !$tudu.isdraft}}<input type="hidden" name="flowid" value="{{$tudu.flowid}}" />{{/if}}
              <select style="width:100%;" name="flowid" id="flowid"{{if $tudu.tuduid && !$tudu.isdraft}} disabled="disabled"{{/if}}>
                <option value="">--不使用工作流--</option>
                {{if $flows}}
                {{foreach from=$flows item=flow}}
                <option value="{{$flow.flowid}}"{{if $tudu.flowid == $flow.flowid}} selected="selected"{{/if}} title="{{$flow.subject}}" _classid="{{$flow.classid}}">{{$flow.subject}}</option>
                {{/foreach}}
                {{/if}}
              </select>
            </td>
          </tr>
        </table>

		<table cellspacing="0" cellpadding="0">
          <tr>
            <td class="info_txt">{{$LANG.subject}}</td>
            <td width="90" id="class-td"{{if !$tudu.classid && !$classes}} style="display:none"{{/if}}><select style="width:90px;" name="classid" id="classid"{{if $tudu.classid && ($board.protect || ($isforward && !$access.modify) || $isdivide || $isapply)}} disabled="disabled" _disabled="true"{{/if}}>
            {{if $board && !$board.isclassify}}<option value="">--{{$LANG.none}}--</option>{{/if}}
            {{if $classes}}
            {{foreach from=$classes item=class}}
            <option value="{{$class.classid}}"{{if $tudu.classid === $class.classid}} selected="selected"{{/if}} title="{{$class.classname}}">{{$class.classname}}</option>
            {{/foreach}}
            {{/if}}
            </select></td>
            <td class="info_forms info_input"><input class="input_text" name="subject" id="subject" type="text" value="{{$tudu.subject|escape:'html'}}" tabindex="1"{{if $board.protect || ($isforward && !$access.modify) || $isdivide || $isapply || $isreview}} disabled="disabled" _disabled="true"{{/if}} maxlength="50" /></td>
          </tr>
        </table>

        <table id="row-reviewer" cellspacing="0" cellpadding="0"{{if !$reviewer && !$isapply && !$isreview}} style="display:none"{{/if}}>
          <tr>
            <td class="info_txt"><a href="javascript:void(0)" id="select-reviewer">{{$LANG.reviewer}}</a></td>
            <td class="info_forms" style="padding-right:10px;*"><input id="i-reviewer" tabindex="2" class="input_text" type="text" readonly="readonly" value="" /><input type="hidden" name="reviewer" id="reviewer" value="{{if !$isforward}}{{foreach item=item key=key from=$reviewer}}{{$item.userinfo|cat:"\n"}}{{/foreach}}{{/if}}" /></td>
          </tr>
        </table>

{{if !$isapply}}
		<table id="row-to" cellspacing="0" cellpadding="0">
          <tr>
            <td class="info_txt"><a href="javascript:void(0)" id="select-to">{{$LANG.receiver}}</a></td>
            <td class="info_forms" style="padding-right:10px;"><input id="i-to" tabindex="2" class="input_text" type="text" readonly="readonly" value="" /><input type="hidden" name="to" id="to" value="{{foreach item=item key=key from=$tudu.to}}{{$item.userinfo|cat:"\n"}}{{/foreach}}"{{if $isapply}} disabled="disabled" _disabled="true"{{/if}} /></td>
          </tr>
        </table>
{{/if}}
        <table id="row-cc" cellspacing="0" cellpadding="0"{{if !$tudu.cc}} style="display:none"{{/if}}>
          <tr>
            <td class="info_txt"><a href="javascript:void(0)" id="select-cc">{{$LANG.cc}}</a></td>
            <td class="info_forms info_input" style="padding-right:10px;"><input id="i-cc" tabindex="3" class="input_text" type="text" readonly="readonly" value="{{foreach from=$tudu.cc item=cc name=cc}}{{if !$smarty.foreach.cc.first}};{{/if}}{{$cc.0}}{{/foreach}}" /><input type="hidden" name="cc" id="cc" value="{{foreach item=item key=key from=$tudu.cc}}{{$item.3|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}" /></td>
          </tr>
        </table>

        <table id="row-bcc" cellspacing="0" cellpadding="0"{{if !$tudu.bcc}} style="display:none"{{/if}}>
          <tr>
            <td class="info_txt"><a href="javascript:void(0)" id="select-bcc">{{$LANG.bcc}}</a></td>
            <td class="info_forms" style="padding-right:10px;"><input id="i-bcc" tabindex="4" class="input_text" type="text" readonly="readonly" value="{{foreach from=$tudu.bcc item=bcc name=bcc}}{{if !$smarty.foreach.bcc.first}};{{/if}}{{$bcc.0}}{{/foreach}}" /><input type="hidden" name="bcc" id="bcc" value="{{foreach item=item key=key from=$tudu.bcc}}{{$item.3|cat:" "|cat:$item.0|cat:"\n"}}{{/foreach}}" /></td>
          </tr>
        </table>

		<table id="row-date" cellspacing="0" cellpadding="0"{{if !$tudu.starttime && !$tudu.endtime && !$tudu.totaltime}} style="display:none"{{/if}}>
          <tr>
            <td class="info_txt">{{$LANG.starttime}}</td>
            <td class="info_forms" width="185"><input type="text" tabindex="4" class="input_text" id="starttime" name="starttime" value="{{if !$tudu.tuduid}}{{$smarty.now|date_format:'%Y-%m-%d'}}{{else}}{{$tudu.starttime|date_format:'%Y-%m-%d'}}{{/if}}" readonly="readonly" style="width:178px;"{{if ($isforward && count($tudu.accepter) > 1) || $isreview ||$isdivide}} disabled="disabled" _disabled="true"{{/if}} /></td>
            <td class="info_txt">{{$LANG.endtime}}</td>
            <td class="info_forms" width="185"><input type="text" tabindex="6" class="input_text" name="endtime" id="endtime" readonly="readonly" value="{{$tudu.endtime|date_format:'%Y-%m-%d'}}" style="width:178px;"{{if ($isforward && count($tudu.accepter) > 1) || $isreview || $isdivide}} disabled="disabled" _disabled="true"{{/if}} /></td>
            <!--
            <td class="info_txt">{{$LANG.epalsedtime}}</td>
            <td class="info_forms" width="185"><input style="width:60px;"{{if $isreview || $isdivide}} disabled="disabled"{{/if}} tabindex="5" class="input_text" name="totaltime" id="totaltime" value="{{if $tudu.totaltime}}{{math equation="x/3600" x=$tudu.totaltime}}{{/if}}" type="text" /><select style="width:60px;margin-left:3px" name=""{{if $isreview || $isdivide}} disabled="disabled" _disabled="true"{{/if}}><option>{{$LANG.hour}}</option></select></td>
             -->
            <td></td>
            <td></td>
            <td></td>
          </tr>
        </table>

		<table id="row-percent" cellspacing="0" cellpadding="0" style="display:none">
          <tr>
            <td class="info_txt">{{$LANG.tudu_percent}}</td>
            <td class="info_forms" id="percent-list">

            </td>
          </tr>
        </table>
       </div>

       {{if !$isdivide && !((!$tudu.tuduid || !$tudu.isdraft) && $board.protect)}}
	   <div class="info_box">
	   	{{strip}}
	        <div class="attlist">
	            <span class="add"><span class="icon icon_tpl"></span><a href="javascript:void(0)" name="tpllist" _textarea="content">{{$LANG.add_tpl_list}}</a></span>
				{{if $access.upload}}<span class="upload_btn"><span id="upload-btn"></span></span><span class="add"><span class="icon icon_add"></span><a href="javascript:void(0)" id="upload-link">{{$LANG.file_upload}}</a></span>{{/if}}
				{{if $access.upload && $user.maxndquota > 0}}<span class="add" id="netdisk-btn"><span class="icon icon_nd_attach"></span><a href="javascript:void(0)">{{$LANG.netdisk_attach}}</a></span>{{/if}}
				<span class="add" id="screencp-btn"><span class="icon icon_screencp"></span><a href="javascript:void(0)" id="link-capture">{{$LANG.screencapture}}</a></span>
				<span class="add" id="pic-btn"><span class="icon icon_photo"></span> <a href="javascript:void(0)" id="insert-pic">{{$LANG.picture}}</a></span>
				<span class="add" id="map-btn"><span class="icon icon_map"></span><a href="javascript:void(0)">{{$LANG.map}}</a></span>
	        </div>
		{{/strip}}
	    </div>
        {{/if}}
	    <div id="attach-list" class="info_box att_container"{{if $tudu.attachnum <= 0 || $isforward  || $isreview}} style="display:none"{{/if}}>
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
	                <div class="attsep_del">{{if !$board.protect}}<a href="javascript:void(0)" name="delete" onClick="Modify.removeAttach('{{$file.fileid}}');">{{$LANG.delete}}</a>{{/if}}</div><div class="clear"></div></div>
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
	                <div class="attsep_del">{{if !$board.protect}}<a href="javascript:void(0)" name="delete" onClick="Modify.removeAttach('{{$file.fileid}}');">{{$LANG.delete}}</a>{{/if}}</div><div class="clear"></div></div>
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
                <td class="info_forms info_input"><textarea class="form_textarea" id="content" cols="" rows="">{{$tudu.content|tudu_format_content|escape:'html'}}</textarea><textarea id="postcontent" name="content" style="display:none;"></textarea></td>
              </tr>
            </table>
        </div>

		<div class="info_box">
	        <table cellspacing="0" cellpadding="0">
	          <tr>
	            <td class="info_txt"></td>
	            <td class="info_forms">
	            {{if (!$isforward || $access.modify) && !$isapply && !$isreview}}
	            	{{strip}}
	                <div class="setting_box option_box">
	                    <label for="urgent" title="{{$LANG.task_priority_tips}}"{{if ($isforward && !$access.modify) || $isdivide || $isreview}} class="gray"{{/if}}><input name="priority" type="checkbox" value="1" id="urgent"{{if $tudu.priority}} checked="checked"{{/if}}{{if ($isforward && !$access.modify) || $isdivide || $isreview}} disabled="disabled" _disabled="true"{{/if}} />{{$LANG.urgent}}</label>
                        <label for="secrecy" title="{{$LANG.task_secrecy_tips}}"{{if ($isforward && !$access.modify) || $isdivide || $isreview}} class="gray"{{/if}}><input name="privacy" type="checkbox" value="1" id="secrecy"{{if $tudu.privacy}} checked="checked"{{/if}}{{if ($isforward && !$access.modify) || $isdivide || $isreview}} disabled="disabled" _disabled="true"{{/if}} />{{$LANG.private}}</label>
						<label for="notifyall" title="{{$LANG.notify_tips}}"{{if ($isforward && !$access.modify) || $isdivide || $isreview}} class="gray"{{/if}}><input name="notifyall" type="checkbox" value="1" id="notifyall"{{if $tudu.notifyall}} checked="checked"{{/if}}{{if ($isforward && !$access.modify) || $isdivide || $isreview}} disabled="disabled" _disabled="true"{{/if}} />{{$LANG.remind_all}}</label>
						<label for="cycle" title="{{$LANG.task_cycle_tips}}"{{if $isforward || $isdivide || $isreview}} class="gray"{{/if}}><input name="cycle" type="checkbox" value="1" id="cycle"{{if $cycle}} checked="checked"{{/if}}{{if $isforward || $isdivide || $isreview}} disabled="disabled" _disabled="true"{{/if}} />{{$LANG.repeat_cycle}}</label>
						<label for="isauth" title="{{$LANG.auth_tips}}"{{if ($isforward && !$access.modify) || $isdivide || $isreview}} class="gray"{{/if}}><input type="checkbox" name="isauth" value="1" id="isauth"{{if $tudu.isauth}} checked="checked"{{/if}}{{if ($isforward && !$access.modify) || $isdivide}} disabled="disabled" _disabled="true"{{/if}} />{{$LANG.foreign_auth}}</label>
						<label for="needconfirm" title="{{$LANG.tudu_need_confirm_tips}}"{{if ($isforward && !$access.modify) || $isdivide || $isreview}} class="gray"{{/if}}><input type="checkbox" name="needconfirm" value="1" id="needconfirm"{{if $tudu.needconfirm || (!$tudu.tuduid && $board && $board.needconfirm)}} checked="checked"{{/if}}{{if ($isforward && !$access.modify) || $isdivide || $isreview}} disabled="disabled" _disabled="true"{{/if}} />{{$LANG.tudu_need_confirm}}</label>
						<label for="acceptmode" title="{{$LANG.tudu_claim_tips}}"{{if $isforward || $isdivide || $isreview || $tudu.istudugroup || $issynchro}} class="gray"{{/if}}><input type="checkbox" id="acceptmode" name="acceptmode" value="1"{{if ($tudu.isdraft && $tudu.acceptmode) || ($tudu.acceptmode && !$tudu.accepttime)}} checked="checked"{{/if}}{{if $isforward || $isdivide || $isreview || $tudu.istudugroup || $issynchro}} disabled="disabled"{{/if}}{{if $tudu.status == 3}} onClick="Modify.showClaimUser(this.id, 0, '{{$tudu.tuduid}}')"{{/if}} />{{$LANG.tudu_claim_mode}}</label>
	                </div>
					{{/strip}}
			    {{/if}}
				</td>
			  </tr>
			 </table>
	     </div>

		 <div class="info_box" id="extend-box"{{if ($isforward && !$access.modify) || $isdivide || $isreview || $isapply ||(!$tudu.privacy && !$cycle)}} style="display:none"{{/if}}>
		 	<table cellspacing="0" cellpadding="0">
              <tr>
                <td class="info_txt"></td>
                <td class="info_forms">
                	<div class="cycle_wrap" id="block-privacy"{{if ($isforward && !$access.modify) || $isdivide || $isreview || $isapply || !$tudu.privacy}} style="display:none"{{/if}}>
                        <div class="content_box3">
                            <strong>{{$LANG.private_work}}</strong>
                            <div class="line_bold"></div>
                            <p class="gray">{{$LANG.private_tips}}</p><br />
                            <input name="open_pwd" type="checkbox" id="open_pwd"{{if $tudu.password}} checked="checked"{{/if}} value="1" /><label for="open_pwd">{{$LANG.open_password}}</label><span class="gray">{{$LANG.open_pwd_tips}}</span>
                            <div id="show_password" style="height:28px; line-height:28px;"><input name="password" type="text" id="password" class="input_text" style="width:178px;{{if !$tudu.password}}display:none;{{/if}}" maxlength="16" value="{{$tudu.password}}"{{if !$tudu.password}} disabled="disabled"{{/if}} onkeyup="this.value=this.value.replace(/[^\x01-\xff]+/,'')" /></div>
                        </div>
                    </div>

					<div class="cycle_wrap" id="block-cycle"{{if !$cycle}} style="display:none"{{/if}}>
                    {{if $cycle}}
                    <input type="hidden" name="cycleid" value="{{$cycle.cycleid}}" />
                    {{/if}}
                        <div class="content_box3">
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
                                        <p><input name="type-day" type="radio" value="1" id="mode-1"{{if !$cycle || $cycle.type == 1}} checked="checked"{{/if}} /><label for="mode-1">{{$LANG.cycle_every}}</label> <input style="width:40px;" class="input_text" value="{{if $cycle.day==0}}1{{else}}{{$cycle.day}}{{/if}}" type="text" name="day-1-day" id="day-1-day" /> {{$LANG.day}}</p>
                                        <p><input name="type-day" type="radio" value="2" id="mode-2"{{if $cycle.mode == 'day' && $cycle.type == 2}} checked="checked"{{/if}} /><label for="mode-2">{{$LANG.cycle_every_workday}}</label></p>
                                        <p><input name="type-day" type="radio" value="3" id="mode-3"{{if $cycle.mode == 'day' && $cycle.type == 3}} checked="checked"{{/if}} /><label for="mode-3">{{$LANG.cycle_every_complete}}</label> <input style="width:40px;" class="input_text" value="{{if $cycle.day==0}}1{{else}}{{$cycle.day}}{{/if}}" type="text" name="day-3-day" id="day-3-day" /> {{$LANG.day}}{{$LANG.cycle_recreate}}</p>
                                    </div>
                                    <div class="method" id="mode-week"{{if $cycle.mode != 'week'}} style="display:none"{{/if}}>
                                        <p><input name="type-week" type="radio" value="1" id="mode-week-1"{{if !$cycle || $cycle.type == 1}} checked="checked"{{/if}} /><label for="mode-week-1">{{$LANG.repeat_cycle_is}}</label> <input style="width:30px;" name="week-1-week" id="week-1-week" class="input_text" value="{{if $cycle.week == 0}}1{{else}}{{$cycle.week}}{{/if}}" type="text" /> {{$LANG.week}}{{$LANG.cycle_after}}</p>
                                        <div class="weeks-group">
                                            <label for="weekday-0"><input type="checkbox" name="week-1-weeks[]" value="0" id="weekday-0"{{if is_array($cycle.weeks) && in_array(0, $cycle.weeks)}} checked="checked"{{/if}} />{{$LANG.week_0}}</label>
                                            <label for="weekday-1"><input type="checkbox" name="week-1-weeks[]" value="1" id="weekday-1"{{if (is_array($cycle.weeks) && in_array(1, $cycle.weeks)) || !$cycle}} checked="checked"{{/if}} />{{$LANG.week_1}}</label>
                                            <label for="weekday-2"><input type="checkbox" name="week-1-weeks[]" value="2" id="weekday-2"{{if is_array($cycle.weeks) && in_array(2, $cycle.weeks)}} checked="checked"{{/if}} />{{$LANG.week_2}}</label>
                                            <label for="weekday-3"><input type="checkbox" name="week-1-weeks[]" value="3" id="weekday-3"{{if is_array($cycle.weeks) && in_array(3, $cycle.weeks)}} checked="checked"{{/if}} />{{$LANG.week_3}}</label>
                                            <label for="weekday-4"><input type="checkbox" name="week-1-weeks[]" value="4" id="weekday-4"{{if is_array($cycle.weeks) && in_array(4, $cycle.weeks)}} checked="checked"{{/if}} />{{$LANG.week_4}}</label>
                                            <label for="weekday-5"><input type="checkbox" name="week-1-weeks[]" value="5" id="weekday-5"{{if is_array($cycle.weeks) && in_array(5, $cycle.weeks)}} checked="checked"{{/if}} />{{$LANG.week_5}}</label>
                                            <label for="weekday-6"><input type="checkbox" name="week-1-weeks[]" value="6" id="weekday-6"{{if is_array($cycle.weeks) && in_array(6, $cycle.weeks)}} checked="checked"{{/if}} />{{$LANG.week_6}}</label>
                                        </div>
                                        <p><input name="type-week" type="radio" value="3" id="mode-week-3"{{if $cycle.type == 3}} checked="checked"{{/if}}><label for="mode-week-3"/>{{$LANG.cycle_every_complete}}</label> <input style="width:30px;" class="input_text" value="{{if $cycle.week == 0}}1{{else}}{{$cycle.week}}{{/if}}" type="text" name="week-3-week" id="week-3-week" /> {{$LANG.week}}{{$LANG.cycle_recreate}}</p>
                                    </div>
                                    <div class="method" id="mode-month"{{if $cycle.mode != 'month'}} style="display:none"{{/if}}>
                                        <p><input name="type-month" type="radio" value="1" id="mode-month-1"{{if !$cycle || $cycle.type == 1}} checked="checked"{{/if}} /><label for="mode-month-1">{{$LANG.cycle_every}}</label> <input style="width:30px;" class="input_text" value="{{if $cycle.month == 0 }}1{{else}}{{$cycle.month}}{{/if}}" name="month-1-month" id="month-1-month" type="text" /> {{$LANG.cycle_month}}{{$LANG.cycle_number}}<input type="text" style="width:40px;" class="input_text" value="{{if $cycle.day == 0 }}1{{else}}{{$cycle.day}}{{/if}}" name="month-1-day" id="month-1-day" />  {{$LANG.day}}</p>
                                        <p><input name="type-month" type="radio" value="2" id="mode-month-2"{{if $cycle.type == 2}} checked="checked"{{/if}} /><label for="mode-month-2">{{$LANG.cycle_every}}</label> <input style="width:30px;" class="input_text" value="{{if $cycle.month == 0 }}1{{else}}{{$cycle.month}}{{/if}}" name="month-2-month" id="month-2-month" type="text" /> {{$LANG.cycle_month}}{{$LANG.cycle_after}} <select name="month-2-at" style="width:80px">
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
                                        <p><input name="type-month" type="radio" value="3" id="mode-month-3"{{if $cycle.type == 3}} checked="checked"{{/if}} /><label for="mode-month-3">{{$LANG.cycle_every_complete}}</label> <input style="width:30px;" class="input_text" value="{{if $cycle.month == 0 }}1{{else}}{{$cycle.month}}{{/if}}" type="text" name="month-3-month" id="month-3-month" /> {{$LANG.cycle_month}}{{$LANG.cycle_recreate}}</p>
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
                </td>
			  </tr>
			</table>
		 </div>

		 <div class="info_box" id="divide-box"{{if !$children && !$isdivide}} style="display:none;"{{/if}}>
		 	<input id="user-msg" value="{{$user.username}}" _name="{{$user.truename}}" type="hidden" />
		 	<table cellspacing="0" cellpadding="0">
              <tr>
                <td class="info_txt">{{$LANG.tudu_divide}}</td>
                <td class="info_forms" style="padding-right:16px">
                  <div class="child-edit-box">
                  <div class="msg" style="margin:0 10px;display:none;" id="ch-flow-steps"></div>
                    <div class="info_box">
                       <div id="child-edit-form">
                   	   <table cellspacing="0" cellpadding="0">
                         <tr>
                           <td class="info_txt">&nbsp;</td>
                		   <td class="info_forms"><a href="javascript:void(0)" id="ch-add-review" class="expand-link" style="margin-left:0">{{$LANG.add_review}}</a>|<a href="javascript:void(0)" id="ch-add-prev" class="expand-link">{{$LANG.add_prev_tudu}}</a>|<a href="javascript:void(0)" id="ch-add-cc" class="expand-link">{{$LANG.add_cc}}</a>-<a href="javascript:void(0)" id="ch-add-bcc" class="expand-link">{{$LANG.add_bcc}}</a>|<a href="javascript:void(0)" id="ch-add-date" class="expand-link">{{$LANG.add_date}}</a>|{{if $tudu.tuduid && !$tudu.isdraft}}<a href="javascript:void(0)" id="ch-add-percent" class="expand-link">{{$LANG.add_percent}}</a>|{{/if}}<a href="javascript:void(0)" id="add-content" class="expand-link">{{$LANG.add_content}}</a></td>
                		 </tr>
                	   </table>
                	   <table cellspacing="0" cellpadding="0">
                         <tr>
                           <td class="info_txt">{{$LANG.belong_board}}</td>
                           <td class="info_forms info_input">
                           <input type="text" class="input_text" id="ch-bid-input" title="{{$LANG.inherit_parent}}" />
                           <input type="hidden" name="ch-bid" id="ch-bid" />
                           </td>
                         </tr>
                       </table>
                       <table cellspacing="0" cellpadding="0" id="ch-row-prev" style="display:none;">
                         <tr>
                           <td class="info_txt">{{$LANG.prev_tudu}}</td>
                           <td><select name="ch-prev" id="ch-prev" />
                           <option value="">-</option>
                           </select></td>
                         </tr>
                       </table>
                       <table id="ch-tudu-flow" cellspacing="0" cellpadding="0" style="display:none;">
                          <tr>
                            <td class="info_txt">工作流</td>
                            <td class="info_forms info_input" style="padding-right:10px;">
                              <select style="width:100%;" name="ch-flowid" id="ch-flowid">
                                <option value="">--不使用工作流--</option>
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
                       <table id="ch-row-reviewer" cellspacing="0" cellpadding="0" style="display:none;">
                         <tr>
                           <td class="info_txt"><a href="javascript:void(0)" id="ch-select-reviewer">{{$LANG.reviewer}}</a></td>
                           <td class="info_forms" style="padding-right:10px;*"><input id="ch-reviewer-text" tabindex="51" class="input_text" type="text" readonly="readonly" value="" /><input type="hidden" id="ch-reviewer" name="ch-reviewer" value="" /></td>
                         </tr>
                       </table>
                       <table id="ch-row-to" cellspacing="0" cellpadding="0">
                         <tr>
                           <td class="info_txt"><a href="javascript:void(0)" id="ch-select-to">{{$LANG.receiver}}</a></td>
                           <td class="info_forms" style="padding-right:10px;*"><input id="ch-to-text" tabindex="51" class="input_text" type="text" readonly="readonly" value="" /><input type="hidden" id="ch-to" name="ch-to" value="" /></td>
                         </tr>
                       </table>
                       <table id="ch-row-cc" cellspacing="0" cellpadding="0" style="display:none;">
                         <tr>
                           <td class="info_txt"><a href="javascript:void(0)" id="ch-select-cc">{{$LANG.cc}}</a></td>
                           <td class="info_forms info_input" style="padding-right:10px;*"><input id="ch-cc-text" tabindex="51" class="input_text" type="text" readonly="readonly" value="" /><input type="hidden" id="ch-cc" name="ch-cc" value="" /></td>
                         </tr>
                       </table>
                       <table id="ch-row-bcc" cellspacing="0" cellpadding="0" style="display:none;">
                         <tr>
                           <td class="info_txt"><a href="javascript:void(0)" id="ch-select-bcc">{{$LANG.bcc}}</a></td>
                           <td class="info_forms info_input" style="padding-right:10px;*"><input id="ch-bcc-text" tabindex="51" class="input_text" type="text" readonly="readonly" value="" /><input type="hidden" id="ch-bcc" name="ch-bcc" value="" /></td>
                         </tr>
                       </table>
                       <table id="ch-row-date" cellspacing="0" cellpadding="0" style="display:none;">
                         <tr>
                           <td class="info_txt">{{$LANG.starttime}}</td>
                           <td class="info_forms" width="185"><input type="text" tabindex="4" class="input_text" id="ch-starttime" name="ch-starttime" value="" readonly="readonly" style="width:178px;" /></td>
                           <td class="info_txt">{{$LANG.endtime}}</td>
                           <td class="info_forms" width="185"><input type="text" tabindex="6" class="input_text" name="ch-endtime" id="ch-endtime" readonly="readonly" value="" style="width:178px;" /></td>
                           <!--
                           <td class="info_txt">{{$LANG.epalsedtime}}</td>
                           <td class="info_forms" width="185"><input style="width:60px;" tabindex="5" class="input_text" name="ch-totaltime" id="ch-totaltime" value="" type="text" /><select style="width:60px;margin-left:3px" name=""><option>{{$LANG.hour}}</option></select></td>
                           -->
                           <td></td>
                           <td></td>
                           <td></td>
                         </tr>
                       </table>
                       <table id="ch-row-percent" cellspacing="0" cellpadding="0" style="display:none;">
                         <tr>
                           <td class="info_txt">{{$LANG.tudu_percent}}</td>
                           <td class="info_forms" id="ch-percent-list">

                           </td>
                         </tr>
                       </table>
                       <div id="ch-row-content" style="display:none;">
                	   	{{strip}}
                	        <div class="attlist">
                	            <span class="add"><span class="icon icon_tpl"></span><a href="javascript:void(0)" name="ch-tpllist" _textarea="ch-content">{{$LANG.add_tpl_list}}</a></span>
                				{{if $access.upload}}<span class="upload_btn"><span id="ch-upload-btn"></span></span><span class="add"><span class="icon icon_add"></span><a href="javascript:void(0)" id="upload-link">{{$LANG.file_upload}}</a></span>{{/if}}
                				{{if $access.upload && $user.maxndquota > 0}}<span class="add" id="ch-netdisk-btn"><span class="icon icon_nd_attach"></span><a href="javascript:void(0)">{{$LANG.netdisk_attach}}</a></span>{{/if}}
                				<span class="add" id="ch-screencp-btn"><span class="icon icon_screencp"></span><a href="javascript:void(0)" id="ch-link-capture">{{$LANG.screencapture}}</a></span>
                				<span class="add" id="ch-map-btn"><span class="icon icon_map"></span><a href="javascript:void(0)">{{$LANG.map}}</a></span>
                				<span class="add" id="pic-btn"><span class="icon icon_photo"></span> <a href="javascript:void(0)" id="ch-insert-pic">{{$LANG.picture}}</a></span>
                	        </div>
                		{{/strip}}
							<div id="ch-attach-list" class="info_box att_container" style="display:none">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <td class="info_txt"></td>
                                    <td class="bd_upload">
                                    </td>
                                  </tr>
                                </table>
                            </div>
                            <table cellspacing="0" cellpadding="0">
                              <tr>
                                <td class="info_txt">{{$LANG.content}}</td>
                                <td class="info_forms info_input"><textarea class="form_textarea" id="ch-content" name="ch-content" cols="" rows=""></textarea></td>
                              </tr>
                            </table>
                    	    <table cellspacing="0" cellpadding="0">
                              <tr>
                                <td class="info_txt"></td>
                                <td class="info_forms">
                                    <div class="setting_box">
                                        <input type="checkbox" value="1" id="ch-priority" name="ch-priority"{{if $tudu.priority}} checked="checked"{{/if}} /><label for="ch-priority" title="{{$LANG.task_priority_tips}}">{{$LANG.urgent}}</label>&nbsp;&nbsp;<input type="checkbox" value="1" id="ch-privacy" name="ch-privacy"{{if $tudu.privacy}} checked="checked"{{/if}} /><label for="ch-privacy" title="{{$LANG.task_secrecy_tips}}">{{$LANG.private}}</label>&nbsp;&nbsp;<label for="ch-notifyall" title="{{$LANG.notify_tips}}"><input type="checkbox" value="1" id="ch-notifyall" name="ch-notifyall" /></input>{{$LANG.remind_all}}</label><!-- &nbsp;&nbsp;<input name="" type="checkbox" value="" id="text"><label for="text">{{$LANG.pure_text}}</label>-->&nbsp;&nbsp;<label for="ch-isauth" title="{{$LANG.auth_tips}}"><input type="checkbox" name="ch-isauth" value="1" id="ch-isauth"{{if $tudu.isauth}} checked="checked"{{/if}} />{{$LANG.foreign_auth}}</label>&nbsp;&nbsp;<label for="ch-needconfirm" title="{{$LANG.tudu_need_confirm_tips}}"><input type="checkbox" name="ch-needconfirm" value="1" id="ch-needconfirm"{{if $tudu.needconfirm}} checked="checked"{{/if}} />{{$LANG.tudu_need_confirm}}</label>&nbsp;&nbsp;<input type="checkbox" id="ch-acceptmode" name="ch-acceptmode" value="1" /><label for="ch-acceptmode" title="{{$LANG.tudu_claim_tips}}">{{$LANG.tudu_claim_mode}}</label>
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
                	   <table cellpadding="0" cellspacing="0" border="0">
                         <tr>
                        	<td width="70"></td>
                        	<td><button class="btn" type="button" name="save-child">{{$LANG.add_divide}}</button></td>
                        	<td align="right"><a name="preview-gantt" href="javascript:void(0)">{{$LANG.preview_gantt}}</a></td>
                         </tr>
                       </table>
                       </div>
                	   <div id="children-list" class="tudu_children_wrap">
                	   </div>
                    </div>
                  </div>
                </td>
              </tr>
            </table>

		 </div>
	</div>
	{{strip}}
    <div class="toolbar">
        <div>
            <button class="btn" type="button" name="send">{{$LANG.send}}</button>
            {{if $tudu.tuduid && !$tudu.isdraft}}
            <button class="btn" type="button" name="back">{{$LANG.back}}</button>
            {{/if}}
            {{if !$tudu.tuduid || $tudu.isdraft}}
            <button class="btn" type="button" name="save">{{$LANG.save_draft}}</button>
            <button class="btn" type="button" name="preview">{{$LANG.preview}}</button>
            {{/if}}
            <span class="compose_msg"></span>
        </div>
    </div>
    {{/strip}}
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

{{* 子图度 *}}
<div id="child-tpl" class="child_info" style="display:none">
<input type="hidden" name="chidx[]" />
<input type="hidden" name="ftid" />
<input type="hidden" name="bid" />
<input type="hidden" name="classid" />
<input type="hidden" name="flowid" />
<input type="hidden" name="isdraft" />
<input type="hidden" name="type" value="task" />
<input type="hidden" name="subject" />
<input type="hidden" name="ch-to" />
<input type="hidden" name="ch-to-text" />
<input type="hidden" name="cc" />
<input type="hidden" name="cc-text" />
<input type="hidden" name="bcc" />
<input type="hidden" name="bcc-text" />
<input type="hidden" name="reviewer" />
<input type="hidden" name="reviewer-text" />
<input type="hidden" name="totaltime" />
<input type="hidden" name="starttime" />
<input type="hidden" name="endtime" />
<input type="hidden" name="content" />
<input type="hidden" name="priority" />
<input type="hidden" name="privacy" />
<input type="hidden" name="needconfirm" />
<input type="hidden" name="notifyall" />
<input type="hidden" name="acceptmode" />
<input type="hidden" name="password" />
<input type="hidden" name="open_pwd" />
<input type="hidden" name="prev" />
<input type="hidden" name="isauth" />
<input type="hidden" name="ismodified" value="1" />
<table cellpadding="0" cellspacing="0" border="0" name="info-table" class="child_info_table">
<tr>
	<td><a href="javascript:void(0)" name="subject"></a></td>
	<td width="230">{{$LANG.receiver}}{{$LANG.cln}}<span name="to"></span></td>
	<td width="90" name="endtime" align="center"></td>
	<td width="80" align="right"><a href="javascript:void(0)" name="edit">[{{$LANG.modify}}]</a>&nbsp;<a href="javascript:void(0)" name="delete">[{{$LANG.delete}}]</a><span class="gray" name="gray-delete">[{{$LANG.delete}}]</span></td>
</tr>
</table>
</div>

<script type="text/javascript">
var tuduId = '{{$tudu.tuduid}}';
var back = '{{$back|default:'/tudu/view?tid=tuduId'}}';
var _BOARDS = {{$boards|@json_encode}};
TOP.Frame.title('{{$LANG.tudu}}');
TOP.Label.focusLabel();
{{if $newwin}}
var _NEW_WIN = 1;
{{else}}
TOP.Frame.hash(LH);
{{/if}}

var IS_CC = false, IS_BCC = false, IS_DATE = false, IS_REVIEW = false;
{{if !$tudu.tuduid || $tudu.isdraft}}
IS_CC   = TOP.Cookie.get('TUDU-EXP-CC');
IS_DATE = TOP.Cookie.get('TUDU-EXP-DATE');
IS_REVIEW = false;

IS_CC = IS_CC === null || IS_CC == 1;
IS_DATE = IS_DATE === null || IS_DATE == 1;

if (IS_CC) $('#row-cc').show();
if (IS_BCC) $('#row-bcc').show();
if (IS_DATE) $('#row-date').show();
if (IS_REVIEW) $('#row-reviewer').show();
{{/if}}

var action = {{if $tudu.tuduid}}{{if $isforward}}'forward'{{elseif $isreview}}'review'{{elseif $isapply}}'apply'{{else}}'update'{{/if}}{{else}}'create'{{/if}};
var tools = {
	cc: {{if $tudu.cc}}true{{else}}IS_CC{{/if}},
	bcc: {{if $tudu.bcc}}true{{else}}IS_BCC{{/if}},
	date: {{if $tudu.starttime || $tudu.endtime || $tudu.totaltime}}true{{else}}IS_DATE{{/if}},
	percent: {{if $accepters}}false{{else}}true{{/if}},
	divide: {{if $isdivide || count($children) > 0}}true{{else}}false{{/if}},
	addPercent: {{if !$tudu.tuduid || $tudu.isdraft}}false{{else}}true{{/if}},
	reviewer: {{if $reviewer || $isreview}}true{{else}}IS_REVIEW{{/if}}
};

var forbid = {
	date: {{if $isdivide}}true{{else}}false{{/if}},
	cc: {{if $isdivide}}true{{else}}false{{/if}},
	autosave: {{if $tudu.tuduid && !$tudu.isdraft}}true{{else}}false{{/if}},
	unload: {{if $tudu && !$tudu.isdraft}}true{{else}}false{{/if}},
	claim: {{if $tudu.istudugroup}}false{{else}}true{{/if}},
	flow: {{if $tudu.flowid}}true{{else}}false{{/if}},
	editor: {{if $isdivide || ($board && $board.protect && $tudu && !$tudu.isdraft && !$isreview && !$isforward)}}true{{else}}false{{/if}}
};
Modify.issynchro = {{if $issynchro}}true{{else}}false{{/if}};

Modify.isModify  = {{if $tudu.tuduid && !$isforward && !$isreview && !$isapply && !$isdivide}}true{{else}}false{{/if}};
{{if $user.option.settings.fontfamily}}
Modify.editorCss = {
    'font-family':'{{$user.option.settings.fontfamily}}',
    'font-size':'{{$user.option.settings.fontsize|default:'12px'}}'
};
{{/if}}

var accepter = new Array();
{{foreach item=accepter from=$cannotdelaccepter}}
accepter.push({username:'{{$accepter.username}}', truename: '{{$accepter.truename}}'});
{{/foreach}}
Modify.init('task', action, forbid, tools, back, accepter);

{{if $tudu.acceptmode}}
$('#add-percent').addClass('disabled');
{{/if}}

{{if $access.upload}}
{{if !$isdivide}}
Modify.upload = initAttachment({
	buttonPlaceholderId: 'upload-btn',
    uploadUrl: '{{$options.sites.file}}{{$upload.cgi.upload}}',
    postParams: {'cookies': '{{$cookies}}'},
    fileSizeLimit: '{{$uploadsizelimit}}'
}, $('#attach-list'), $('#attach-list td.bd_upload'));
{{/if}}

Modify.chUpload = initAttachment({
	buttonPlaceholderId: 'ch-upload-btn',
    uploadUrl: '{{$options.sites.file}}{{$upload.cgi.upload}}',
    postParams: {'cookies': '{{$cookies}}'},
    fileSizeLimit: '{{$uploadsizelimit}}'
}, $('#ch-attach-list'), $('#ch-attach-list td.bd_upload'));
{{/if}}

initPicInsert({'#insert-pic': Modify.editor, '#ch-insert-pic': Modify.chEditor} {{if $access.upload}}, {
    postParams: {'cookies': '{{$cookies}}'},
    fileSizeLimit: '{{$uploadsizelimit}}',
    auth: '{{$upload.auth}}'
}{{/if}});

Capturer.setUploadUrl('{{$options.sites.file}}{{$upload.cgi.upload}}');

{{if !$reviewer && !$issynchro}}
{{foreach item=accepter from=$accepters}}
Modify.appendPercentList({
	truename: '{{$accepter.truename}}',
	email: '{{$accepter.email}}',
	percent: '{{$accepter.percent}}'
}, $('#percent-list'), '');
{{/foreach}}
{{/if}}

{{foreach item=child from=$children}}
Modify.appendChild({
	ftid: '{{$child.tuduid}}',
	subject: '{{$child.subject}}',
	starttime: '{{$child.starttime|date_format:'%Y-%m-%d'}}',
	endtime: '{{$child.endtime|date_format:'%Y-%m-%d'}}',
	'ch-to': [{{foreach item=item from=$child.to name=to}}'{{$item}}'{{if !$smarty.foreach.to.last}},{{/if}}{{/foreach}}].join("\n"),
	'ch-to-text': [{{foreach item=item from=$child.totext name=totxt}}'{{$item}}'{{if !$smarty.foreach.totxt.last}},{{/if}}{{/foreach}}].join(","),
	cc: [{{foreach item=item from=$child.cc name=cc}}'{{$item.3}} {{$item.0}}'{{if !$smarty.foreach.cc.last}},{{/if}}{{/foreach}}].join("\n"),
	'cc-text': [{{foreach item=item from=$child.cc name=cctxt}}'{{$item.0}}'{{if !$smarty.foreach.cctxt.last}},{{/if}}{{/foreach}}].join(","),
	bcc: [{{foreach item=item from=$child.bcc name=bcc}}'{{$item.3}} {{$item.0}}'{{if !$smarty.foreach.bcc.last}},{{/if}}{{/foreach}}].join("\n"),
	'bcc-text': [{{foreach item=item from=$child.bcc name=bcctxt}}'{{$item.0}}'{{if !$smarty.foreach.bcctxt.last}},{{/if}}{{/foreach}}].join(","),
	totaltime: '{{if $child.totaltime}}{{math equation="x/3600" x=$child.totaltime}}{{/if}}',
	priority: '{{$child.priority}}',
	privacy: '{{$child.privacy}}',
	notifyall: '{{$child.notifyall}}',
	bid: '{{$child.boardid}}',
	classid: '{{$child.classid}}',
	flowid: '{{$child.flowid}}',
	password: '{{$child.password}}',
	open_pwd: '{{$child.open_pwd}}',
	isdraft: '{{if $child.isdraft}}true{{else}}false{{/if}}',
	needconfirm: '{{$child.needconfirm}}',
	acceptmode: {{if ($child.isdraft && $child.acceptmode) || ($child.acceptmode && !$child.accepttime)}}'{{$child.acceptmode}}'{{else}}''{{/if}},
	isauth: '{{$child.isauth}}',
	isdone: '{{$child.isdone}}',
	prev: '{{$child.prevtuduid}}',
	nodetype: '{{$child.nodetype}}',
	ismodified: {{if $child.isdraft}}1{{else}}0{{/if}}
});
{{/foreach}}
{{if $claimAccepters}}
var claimUsers = new Array();
{{foreach item=item from=$claimAccepters}}
claimUsers.push({tuduid: '{{$item.tuduid}}', email: '{{$item.email}}', truename: '{{$item.truename}}'});
{{/foreach}}
Modify.claimUsers = claimUsers;
{{/if}}
</script>
</body>
</html>