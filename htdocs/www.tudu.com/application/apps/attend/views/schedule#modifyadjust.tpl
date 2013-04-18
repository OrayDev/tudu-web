<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>{{$LANG.adjust_workday}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1019" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.datepick/jquery.datepick-zh-cn.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/attend/adjust.js?1001" type="text/javascript"></script>
</head>

<body>
<form id="theform" action="/app/attend/schedule/saveadjust" method="post">
{{if $adjust.adjustid}}
<input type="hidden" name="adjustid" value="{{$adjust.adjustid}}" />
{{/if}}
{{include file="attend^tab.tpl" tab="schedule"}}
<div class="tab-panel-body">
    {{include file="schedule^toolbar.tpl" tab="adjust"}}
    <div class="readmailinfo">
        <div class="module">
            <div class="module_title"><strong>{{$LANG.adjust_title}}</strong>&nbsp;<span class="gray">{{$LANG.adjust_hint}}</span></div>
            <div class="line_bold"></div>
            <table border="0" cellspacing="0" cellpadding="5">
                <tr>
                    <td align="right" valign="top">{{$LANG.adjust_subject}}</td>
                    <td><input name="subject" type="text" title="{{$LANG.adjust_subject_hint}}" value="{{$adjust.subject|escape:'html'}}" class="input_text" style="width:227px;" autocomplete="off" maxlength="30" /></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td align="right" valign="top">{{$LANG.affect_user}}</td>
                    <td valign="top"><div class="blank_box" style="height:100px; width:228px;" id="user-box"></div></td>
                    <td valign="top"><a href="javascript:void(0)" id="add-user"><span class="icon icon_plus"></span>{{$LANG.add_user}}</a></td>
                </tr>
                <tr>
                    <td align="right" valign="top">{{$LANG.adjust_date}}</td>
                    <td><div class="static_datepicker"><input name="starttime" id="starttime" type="hidden" value="{{$adjust.starttime|date_format:'%Y-%m-%d'}}" /><input name="endtime" id="endtime" type="hidden" value="{{$adjust.endtime|date_format:'%Y-%m-%d'}}" /><div id="datepicker"></div></div></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td align="right" valign="top">{{$LANG.adjust_to}}{{$LANG.cln}}</td>
                    <td><label><input name="type" type="radio" value="1"{{if !$adjust || $adjust.type == 1}} checked="checked"{{/if}} />工作日</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><input name="type" type="radio" value="0"{{if $adjust.type === 0}} checked="checked"{{/if}} />非工作日</label></td>
                    <td></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="toolbar">
        <div>
            <input type="button" class="btn" value="{{$LANG.save}}" id="save" /><input type="button" id="cancel" class="btn" value="{{$LANG.cancel}}" />
        </div>
    </div>
</div>
</form>

<script type="text/javascript">
$(function(){
    TOP.Label.focusLabel('attend');
    TOP.Frame.title('{{$LANG.adjust_workday}}');
    TOP.Frame.hash('m=app/attend/schedule/modifyadjust');

    Attend.Adjust.setUsers([
    {{foreach item=item from=$users name="users"}}
    '{{$item.uniqueid}}'
    {{if $smarty.foreach.users.index + 1 < count($users)}}
    ,
    {{/if}}
    {{/foreach}}
    ]);

    var role = {{if !$role.admin}}false{{else}}true{{/if}};
    Attend.Adjust.setLang({missing_user_select: '{{$LANG.missing_user_select}}', confirm_delete_adjust: '{{$LANG.confirm_delete_adjust}}', params_invalid_adjust_subject: '{{$LANG.params_invalid_adjust_subject}}'});
    {{if !$role.admin}}
    Attend.Adjust.setDepts('{{$deptids}}');
    {{/if}}
    Attend.Adjust.init(role);
});
</script>
</body>
</html>