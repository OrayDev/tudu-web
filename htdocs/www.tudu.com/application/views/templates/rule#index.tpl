<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.general}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
</head>
<body>
    <div class="position">
      <p><strong class="title">{{$LANG.tudu_rule}}</strong></p>
</div>
    {{include file="setting^tab.tpl" tab="rule"}}
    <div class="tab-panel-body">
            <div class="settingbox">
                <div class="settingdiv">
                    <div style="padding:10px 0"><button class="btn" name="add" onclick="location='/rule/modify'">{{$LANG.create_rule}}</button></div>
                    <h3 class="setting_tit">{{$LANG.tudu_rule}}</h3>
                    <table width="100%" cellspacing="0" cellpadding="5" align="center" class="set_tag">
                      <tr>
                        <td class="file_title" colspan="3" style="padding-top:0"></td>
                      </tr>
                      <tr class="addrtitle">
                        <td class="settingtd">{{$LANG.rule}}&nbsp;</td>
                        <td class="settingtd" width="90" align="center">{{$LANG.rule_status}}</td>
                        <td class="settingtd" width="180" align="right">{{$LANG.operation}}&nbsp;</td>
                      </tr>
                      <tbody id="user-rule-list">
                      {{foreach item=rule from=$rules name="rule"}}
                      <tr id="rule-{{$rule.ruleid}}">
                        <td class="settingtd"><a href="/rule/modify?ruleid={{$rule.ruleid}}">{{$rule.description|format_rule_description:$rule.operation:$rule.value|default:'-'}}</a></td>
                        <td class="settingtd" align="center">{{if $rule.isvalid}}{{$LANG.rule_on}}{{else}}{{$LANG.rule_off}}{{/if}}</td>
                        <td class="settingtd" align="right"><a href="/rule/modify?ruleid={{$rule.ruleid}}" name="edit">[{{$LANG.modify}}]</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="delete" onclick="deleteRule('{{$rule.ruleid}}');">[{{$LANG.delete}}]</a></td>
                      </tr>
                      {{/foreach}}
                      </tbody>
                    </table>
                </div>
            </div>
        <div class="toolbar_position">
        <div class="toolbar">
            <div style="height:24px;"></div>
        </div>
        </div>
    </div>
<script type="text/javascript">
<!--
function deleteRule(ruleid) {
    if (!confirm(TOP.TEXT.CONFIRM_DELETE_RULE)) {
        return false;
    }

	$.ajax({
		type: 'POST',
		dataType: 'json',
		data: {
		   'ruleid': ruleid
		},
		url: '/rule/delete',
		success: function(ret) {
			TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);

			if (ret.success) {
                $('#rule-' + ruleid).remove();
			}
		},
		error: function(res){}
	});
}

$(function(){
	TOP.Label.focusLabel();
    TOP.Frame.title('{{$LANG.mailbox_bind}}');
    TOP.Frame.hash('#m=/rule/');

    $('#user-rule-list tr').mousemove(function(){
		$(this).addClass("current");
    }).mouseout(function(){
        $(this).removeClass("current");
    });
});
-->
</script>
</body>
</html>
