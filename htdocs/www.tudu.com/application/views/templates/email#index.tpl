<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.general}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script type="text/javascript">
<!--
var LH = 'm=setting/mailbox';
if (top == this) {
	location = '/frame#' + LH;
}
-->
</script>

</head>
<body>
    <div class="position">
      <p><strong class="title">{{$LANG.mailbox_bind}}</strong></p>
</div>
    {{include file="setting^tab.tpl" tab="mailbox"}}
    <div class="tab-panel-body">
            <div class="settingbox">
                <div class="settingdiv">
                    <div style="padding:10px 0"><button class="btn" name="add" onclick="location='/email/modify'">{{$LANG.add_mailbox}}</button></div>
                    <h3 class="setting_tit">{{$LANG.my_mailbox}}</h3>
                    <table width="100%" cellspacing="0" cellpadding="5" align="center" class="set_tag">
                      <tr>
                        <td class="file_title" colspan="3" style="padding-top:0"></td>
                      </tr>
                      <tr class="addrtitle">
                        <td class="settingtd">{{$LANG.mailbox_address}}&nbsp;</td>
                        <td class="settingtd" width="90" align="center">&nbsp;&nbsp;{{$LANG.unread_count}}&nbsp;</td>
                        <td class="settingtd" width="180" align="right">&nbsp;{{$LANG.operation}}&nbsp;</td>
                      </tr>
                      <tbody id="user-email-list">
                      {{foreach item=email from=$emails name="email"}}
                      <tr>
                        <td class="settingtd"><a href="{{if $email.type != 2}}/email/login?address={{$email.address|escape:'url'}}{{else}}javascript:void(0);{{/if}}"{{if $email.type != 2}} target="_blank"{{/if}}>{{$email.address}}</a></td>
                        <td class="settingtd" align="center">{{$email.unreadnum|default:'-'}}</td>
                        <td class="settingtd" align="right"><a href="/email/modify?address={{$email.address|escape:'url'}}" name="edit">[{{$LANG.modify}}]</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="delete" onclick="deleteEmail('{{$email.address}}');">[{{$LANG.delete}}]</a>&nbsp;&nbsp;<a href="javascript:void(0);" onclick="sortEmail('{{$email.address}}', 'up');"{{if $smarty.foreach.email.index == 0}} class="gray"{{/if}}>↑</a>&nbsp;&nbsp;<a href="javascript:void(0);" onclick="sortEmail('{{$email.address}}', 'down');"{{if $smarty.foreach.email.index == count($emails) - 1}} class="gray"{{/if}}>↓</a></td>
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
function deleteEmail(address) {
	if (!confirm(TOP.TEXT.CONFIRM_DELETE_MAILBOX)) {
        return false;
    }

	$.ajax({
		type: 'POST',
		dataType: 'json',
		data: {
		   'address': address
		},
		url: '/email/delete',
		success: function(ret) {
			TOP.showMessage(ret.message, 5000, ret.success ? 'success': null);
			var _$ = TOP.getJQ();

			if (ret.success) {
                _$('#user_mailbox_list ul li[email="'+address+'"]').remove();
                if (_$('#user_mailbox_list ul li').size() <= 0) {
                	_$('#user_mailbox_list ul').append('<li name="add-mailbox"><a href="/email/" target="main"><span class="labelname">{{$LANG.top_add_mailbox}}</span></a></li>');
                }
                var unread = 0;
                _$('#user_mailbox_list ul li').each(function(){
                    var uc = $(this).find('span.mail_count').text();
                    uc = parseInt(uc.replace('(', '').replace(')',''));
                    if (uc != NaN) {
                        unread += uc;
                    }
                });

                if (unread > 0) {
                	_$('#mailbox_total').text(unread + '');
                    _$('#user_mailbox_title').css('font-weight', 'bold');
                } else {
                    _$('#mailbox_total').text('');
                    _$('#user_mailbox_title').css('font-weight', 'normal');
                }
                location.reload();
			}
		},
		error: function(res){}
	});
}

function sortEmail(address, type) {
	$.ajax({
        type: 'POST',
        dataType: 'json',
        data: {
           'address': address,
           'type': type
        },
        url: '/email/sort',
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, ret.success ? 'success': null);

            if (ret.success) {
            	TOP.Frame.Mailbox.sort(address, type);

                location.reload();
            }
        },
        error: function(res){}
    });
}

$(function(){
    TOP.Label.focusLabel();
    TOP.Frame.title('{{$LANG.mailbox_bind}}');
    TOP.Frame.hash(LH);

    $('#user-email-list tr').mousemove(function(){
        $(this).addClass("current");
    }).mouseout(function(){
        $(this).removeClass("current");
    });
});
-->
</script>
</body>
</html>
