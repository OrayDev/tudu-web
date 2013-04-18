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
      <p><strong class="title">{{$LANG.mailbox_bind}}</strong></p>
</div>
<form action="/setting/savemailbox" id="theform" method="post" class="tab_panel">
    {{include file="setting^tab.tpl" tab="mailbox"}}
    <div class="tab-panel-body">
            <div class="settingbox">
            <div class="setting_common">
                <div class="settingdiv">
                    <h3 class="setting_tit">{{$LANG.mailbox_base}}</h3>
                    <div class="line_bold"></div>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td align="right" width="150">{{$LANG.mailbox_address}}{{$LANG.cln}}</td>
                            <td width="180"><input type="text" class="input_text" name="address" id="address" value="{{$mailbox.address}}" /></td>
                            <td></td>
                          </tr>
                          <tr>
                            <td align="right" width="150">{{$LANG.mailbox_password}}{{$LANG.cln}}</td>
                            <td><input type="password" class="input_text" name="password" id="password" /></td>
                            <td>{{if $mailbox}}<span class="gray">{{$LANG.password_hint}}</span>{{/if}}</td>
                          </tr>
                          <tr>
                            <td align="right" width="150">{{$LANG.mailbox_advanced}}{{$LANG.cln}}</td>
                            <td><input type="checkbox" id="advance" /></td>
                            <td></td>
                          </tr>
                          <tbody id="mailbox_advanced"{{if !$mailbox || $mailbox.type == 0 || !$mailbox.imapport}} style="display:none"{{/if}}>
                          <tr>
                            <td align="right" width="150">{{$LANG.mailbox_imaphost}}{{$LANG.cln}}</td>
                            <td><input type="text" class="input_text" name="imaphost" id="imaphost" value="{{$mailbox.imaphost}}" /></td>
                            <td><span class="gray">{{$LANG.imaphost_hint}}</span></td>
                          </tr>
                          <tr>
                            <td align="right" width="150">{{$LANG.mailbox_imapport}}{{$LANG.cln}}</td>
                            <td><input type="text" class="input_text" name="port" id="port" value="{{$mailbox.port}}" /></td>
                            <td><span class="gray">{{$LANG.imapport_hint}}</span></td>
                          </tr>
                          <tr>
                            <td align="right" width="150">{{$LANG.mailbox_isssl}}{{$LANG.cln}}</td>
                            <td><input type="checkbox" name="isssl" id="isssl"{{if $mailbox.isssl == 1}} checked="checked"{{/if}} /></td>
                            <td><span class="gray">{{$LANG.ssl_hint}}</span></td>
                          </tr>
                          <tr id="type-tr"{{if !$mailbox || $mailbox.type == 0}} style="display:none"{{/if}}>
                            <td align="right" width="150">{{$LANG.mailbox_type}}{{$LANG.cln}}</td>
                            <td><label for="type-olivemail"><input id="type-olivemail" type="radio" name="type" value="1"{{if $mailbox.type == 1}} checked="checked"{{/if}} />{{$LANG.type_olivemail}}</label>&nbsp;&nbsp;<label for="type-other"><input id="type-other" type="radio" name="type" value="2"{{if $mailbox.type == 2}} checked="checked"{{/if}} />{{$LANG.type_other}}</label></td>
                            <td><span class="gray">{{$LANG.type_hint}}</span></td>
                          </tr>
                          </tbody>
                        </table>
                </div>

            </div>
            </div>
            </div>
        <div class="toolbar_position">
        <div class="toolbar">
            <div><button class="btn" type="submit">{{$LANG.save_change}}</button><button class="btn" type="button" name="cancel">{{$LANG.cancel}}</button></div>
        </div>
        </div>
    </div>
</form>
<script type="text/javascript">
<!--
var _MAILBOXES = {{$supports}}

$(function(){
	TOP.Label.focusLabel('');
    TOP.Frame.setTitle('{{$LANG.general}}');
    TOP.Frame.setLH('#m=setting/mailbox');

    $('button[name="cancel"]').click(function(){
        location = '/frame/home';
    });

    $('#address').blur(function(){
        var val = this.value;
        if (TOP.isEmail(val)) {
            var arr = val.split('@');
            var domain = arr[1];
            if (typeof(_MAILBOXES[domain]) != 'undefined') {
                if (_MAILBOXES[domain]) {
                    $('#imaphost').val(_MAILBOXES[domain].imaphost);
                    if (_MAILBOXES[domain].type) {
                        $(':radio[name="type"][value="'+_MAILBOXES[domain].type+'"]').attr('checked', true);
                    } else {
                    	$(':radio[name="type"]').attr('checked', false);
                    }
                } else {
                	$('#imaphost').val('');
                }
                $('#type-tr').hide();
            } else {
            	$('#imaphost').val('');
            	$('#advance').attr('checked', true);
                $('#mailbox_advanced').show();
                $('#type-tr').show();
            }
        } else {
            if (!$('#imaphost:visible').size()) {
            	$('#imaphost').val('');
            }
        }
    });

    $('#type-olivemail').click(function(){
    	$('#imaphost').val(_MAILBOXES['oray.com'].imaphost);
    });

    $('#advance').click(function(){
        var checked = this.checked;
        if (checked) {
            $('#mailbox_advanced').show();
        } else {
            $('#mailbox_advanced').hide();
        }
    });

    $('#theform').submit(function(){return false;});
    $('#theform').submit(function(){
        var form = $(this),
            address = $('#address').val();

        if (address && !TOP.isEmail(address)) {
            TOP.showMessage('{{$LANG.invalid_email_address}}');
            return $('#address').focus();
        }

        var data = form.serializeArray();

        TOP.showMessage(TOP.TEXT.POSTING_DATA);

        form.find('input, select, button').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, 'success');
                form.find('input, select, button').attr('disabled', false);
                var _$ = TOP.getJQ();
                _$('#user_mailbox_list ul li').remove();
                if (address) {
                    _$('#user_mailbox_list ul').append('<li email="'+address+'"><a target="main" onclick="loginMailbox(\''+address+'\')" href="javascript:void(0);"><span class="labelname">'+address+'</span><span class="mail_count"></span></a></li>');
                }
                if (ret.success) {
                    TOP.checkMailbox();
                }
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
                form.find('input, select, button').attr('disabled', false);
            }
        });
    });
});
-->
</script>
</body>
</html>
