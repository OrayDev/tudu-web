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
<form action="/email/{{$action}}" id="theform" method="post" class="tab_panel">
    <input type="hidden" name="action" value="{{$action}}" />
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
                            <td width="200"><input type="text" class="input_text" name="address" id="address" value="{{$email.address}}"{{if $email}} readonly="true"{{/if}} style="width:180px;{{if $email}}color:#aaa;{{/if}}" /></td>
                            <td></td>
                          </tr>
                          <tr>
                            <td align="right" width="150">{{$LANG.mailbox_password}}{{$LANG.cln}}</td>
                            <td><input type="password" class="input_text" name="password" id="password"{{if $email}} value="                "{{/if}} style="width:180px;" /></td>
                            <td></td>
                          </tr>
                          <tr>
                            <td align="right" width="150">{{$LANG.mailbox_advanced}}{{$LANG.cln}}</td>
                            <td><input type="checkbox" id="advance" /></td>
                            <td></td>
                          </tr>
                          <tbody id="mailbox_advanced"{{if !$email || $email.type == 0 || !$email.port}} style="display:none"{{/if}}>
                          <tr>
                            <td align="right" width="150">{{$LANG.mailbox_protocol}}{{$LANG.cln}}</td>
                            <td><select name="protocol" id="protocol">
                            <option value="imap"{{if !$email || $email.ptorocol == 'imap'}} selected="selected"{{/if}}>IMAP</option>
                            <option value="pop3"{{if $email.protocol == 'pop3'}} selected="selected"{{/if}}>POP</option>
                            </select></td>
                            <td><span class="gray">{{$LANG.protocol_hint}}</span></td>
                          </tr>
                          <tr>
                            <td align="right" width="150">{{$LANG.mailbox_host}}{{$LANG.cln}}</td>
                            <td><input type="text" class="input_text" name="host" id="host" value="{{$email.host}}" style="width:180px;" /></td>
                            <td><span class="gray"><span _name="protocol">{{$mailbox.protocol|strtoupper|default:'IMAP'}}</span>{{$LANG.imaphost_hint}}</span></td>
                          </tr>
                          <tr>
                            <td align="right" width="150">{{$LANG.mailbox_port}}{{$LANG.cln}}</td>
                            <td><input type="text" class="input_text" name="port" id="port" value="{{$email.port}}" style="width:180px;" /></td>
                            <td><span class="gray"><span _name="protocol">{{$mailbox.protocol|strtoupper|default:'IMAP'}}</span>{{$LANG.imapport_hint}}</span></td>
                          </tr>
                          <tr>
                            <td align="right" width="150">{{$LANG.mailbox_isssl}}{{$LANG.cln}}</td>
                            <td><input type="checkbox" name="isssl" id="isssl"{{if $email.isssl == 1}} checked="checked"{{/if}} value="1" /></td>
                            <td><span class="gray">{{$LANG.ssl_hint}}</span></td>
                          </tr>
                          <tr id="type-tr"{{if !$mailbox || $mailbox.type == 0}} style="display:none"{{/if}}>
                            <td align="right" width="150">{{$LANG.mailbox_type}}{{$LANG.cln}}</td>
                            <td><label for="type-olivemail"><input id="type-olivemail" type="radio" name="type" value="1"{{if $email.type == 1}} checked="checked"{{/if}} />{{$LANG.type_olivemail}}</label>&nbsp;&nbsp;<label for="type-other"><input id="type-other" type="radio" name="type" value="2"{{if $email.type == 2}} checked="checked"{{/if}} />{{$LANG.type_other}}</label></td>
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
            <div><button class="btn" type="submit">{{$LANG.save}}</button><button class="btn" type="button" name="cancel">{{$LANG.cancel}}</button></div>
        </div>
        </div>
    </div>
</form>
<script type="text/javascript">
<!--
var _MAILBOXES = {{$supports}}

$(function(){
	TOP.Label.focusLabel('');
    TOP.Frame.title('{{$LANG.mailbox_bind}}');
    TOP.Frame.hash('#m=setting/mailbox');

    $('button[name="cancel"]').click(function(){
        location = '/email/';
    });

    $('#protocol').change(function(){

        $('span[_name="protocol"]').text($('#protocol option:selected').text());


        var email = $('#address').val(),
            hostname = this.value + 'host';
        if (TOP.isEmail(email)) {
        	var arr = email.split('@');
            var domain = arr[1];
            if (undefined !== _MAILBOXES[domain]) {
                if (undefined == _MAILBOXES[domain][hostname]) {
                	$('#host').val('');
                } else {
                    $('#host').val(_MAILBOXES[domain][hostname]);
                }
            }
        }
    });

    $('#address').blur(function(){
        var val = this.value;
        if (TOP.isEmail(val)) {
            var arr = val.split('@');
            var domain = arr[1];
            if (typeof(_MAILBOXES[domain]) != 'undefined') {
                if (_MAILBOXES[domain]) {
                    var hostname = _MAILBOXES[domain].protocol + 'host';
                    $('#isssl').attr('checked', (undefined !== _MAILBOXES[domain].isssl));
                    $('#protocol').val(_MAILBOXES[domain].protocol);
                    $('span[_name="protocol"]').text($('#protocol option:selected').text());
                    $('#host').val(_MAILBOXES[domain][hostname]);
                    if (_MAILBOXES[domain].type) {
                        $(':radio[name="type"][value="'+_MAILBOXES[domain].type+'"]').attr('checked', true);
                    } else {
                        $(':radio[name="type"]').attr('checked', false);
                    }
                } else {
                    $('#host').val('');
                }
                $('#type-tr').hide();
            } else {
                $('#host').val('');
                $('#advance').attr('checked', true);
                $('#mailbox_advanced').show();
                $('#type-tr').show();
            }
        } else {
            if (!$('#host:visible').size()) {
                $('#host').val('');
            }
        }
    });

    $('#type-olivemail').click(function(){
        $('#host').val(_MAILBOXES['oray.com'].imaphost);
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

        if (!address || !TOP.isEmail(address)) {
            TOP.showMessage('{{$LANG.invalid_email_address}}');
            return $('#address').focus();
        }
        {{if !$email}}
        if (!$('#password').val()) {
        	TOP.showMessage('{{$LANG.missing_email_password}}');
            return $('#password').focus();
        }
        {{/if}}
        if (!$('#host').val()) {
        	$('#advance').attr('checked', true);
            $('#mailbox_advanced').show();
            TOP.showMessage('{{$LANG.missing_email_host}}');
            return $('#host').focus();

        }

        {{if !$email}}
        if (!$('#password').val()) {
            TOP.showMessage('{{$LANG.missing_email_password}}');
            return $('#password').focus();
        }
        {{/if}}

        var data = form.serializeArray();

        TOP.showMessage('{{$LANG.checking_mailbox}}', 5000, 'success');

        form.find('input, select, button').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
            	TOP.showMessage(ret.message, 10000, ret.success ? 'success': '');
                form.find('input, select, button').attr('disabled', false);
                var _$ = TOP.getJQ();

                if (ret.success) {
                    if (!TOP.Frame.Mailbox.exists(address)) {
                        TOP.Frame.Mailbox.append(address);
                    }

                    if (_$('#user_mailbox_list ul li[email]').size() > 0) {
                    	_$('#user_mailbox_list ul li[name="add-mailbox"]').remove();
                    }
                    TOP.checkMailboxs();
                    location = '/email/';
                }

                if (ret.data && ret.data.advance) {
                    $('#advance').attr('checked', true);
                    $('#mailbox_advanced').show();
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
