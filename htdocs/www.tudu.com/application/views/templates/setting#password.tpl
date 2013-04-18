<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.change_password}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
</head>
<body>
	<div class="position">
   	  <p><a href="/setting/account"><strong class="title">{{$LANG.goback_account}}</strong></a></p>
</div>
<form id="theform" action="/setting/password" method="post" class="panel">
	<div class="tab-panel-body">
      	<div class="toolbar">
        	<div class="toolbar_tips"><p><strong class="f14 font_c">{{$LANG.change_password}}</strong></p></div>
        </div>
        	<div class="settingbox">
            <div class="setting_password">
            <p class="font_c">{{$LANG.password_tips}}</p>
              <div class="settingdiv">
                	<table width="100%" border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td align="right" width="150">{{$LANG.current_password}}{{$LANG.cln}}</td>
                            <td width="240px;"><input class="input_text" id="opassword" name="opassword" type="password" style="width:220px" /></td>
                            <td></td>
                          </tr>
                          <tr>
                            <td align="right">{{$LANG.input_new_password}}{{$LANG.cln}}</td>
                            <td><input class="input_text" id="password" name="password" type="password" style="width:220px" maxlength="16" /></td>
                            <td><p id="tip" class="gray"><span id="tip-icon" style="margin-right:5px"></span><span id="pwd-err"></span><span id="pwd-info">{{assign var=tipkey value="password_level_not_match_"|cat:$user.option.passwordlevel}}{{$LANG[$tipkey]}}</span></p></td>
                          </tr>
                          <tr>
                            <td align="right" ></td>
                            <td><script type="text/javascript" src="{{$options.sites.static}}/js/pse.js"></script></td>
                            <td></td>
                          </tr>
                          <tr>
                            <td align="right">{{$LANG.input_re_password}}{{$LANG.cln}}</td>
                            <td><input class="input_text" id="repassword" name="repassword" type="password" style="width:220px" maxlength="16" /></td>
                            <td><p id="ch-tip" class="gray"><span id="ch-tip-icon" style="margin-right:5px"></span><span id="ch-pwd-err"></span></p></td>
                          </tr>
                	</table>
                </div>

            </div>
            </div>
            </div>
      	<div class="toolbar">
        	<div><button class="btn" type="submit">{{$LANG.confirm}}</button><button class="btn" type="button" name="cancel">{{$LANG.cancel}}</button></div>
        </div>
    </div>
</form>
<script type="text/javascript">
<!--
$(function(){
	TOP.Label.focusLabel('');
	TOP.Frame.title('{{$LANG.account_setting}}');
	TOP.Frame.hash('m=setting/account&type=password');

	$('#password')
	.keyup(function(){
		SetPwdStrengthEx(this.value);
	})
	.blur(function(){
		isvalid = checkPassword();

		if (isvalid) {
			$('#pwd-err, #pwd-info').hide();
		    $('#tip-icon').removeClass('icon_cross').addClass('icon icon_tick');
		}

		if ($('#repassword').val()) {
			comparePwd();
		}
	});

	$('#repassword').blur(function(){
		comparePwd();
	});

	$('button[name="cancel"]').click(function(){
        location = '/setting/account';
    });

	$('#theform').submit(function(){return false;});
    $('#theform').submit(function(){
        if (!checkPassword()) {
            $('#password').focus();
            return false;
        }

        var form = $(this);

        var data = form.serializeArray();

        form.find('input, button, select').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: form.attr('action'),
            data: data,
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
                form.find('input, button, select').attr('disabled', false);
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESS_ERROR);
                form.find('input, button, select').attr('disabled', false);
            }
        });
    });
});

function comparePwd() {
	var password = $('#password').val(),
		repassword = $('#repassword').val();

	if (password != repassword) {
		$('#ch-tip').removeClass('gray').addClass('red');
		$('#ch-tip-icon').removeClass('icon_tick').addClass('icon icon_cross');
		$('#ch-pwd-err').text(TOP.TEXT.CONFIRM_PASSWORD_UNMATCH);
	} else {
		$('#ch-pwd-err').text('');
		$('#ch-tip-icon').removeClass('icon_cross').addClass('icon icon_tick');
	}
}

function checkPassword() {
	var password = $('#password').val(),
	    err = $('#pwd-err'),
	    tip = $('#tip');
        ico = $('#tip-icon');

    $('#pwd-err, #pwd-info').show();
    err.text('');
	if (!password) {
	    tip.removeClass('gray').addClass('red');
	    ico.removeClass('icon_tick').addClass('icon icon_cross');
	    return false;
	}

	var lev = 0;

	if ((/[a-zA-Z]+/).test(password)) {
	    lev++;
	}

	if ((/[0-9]+/).test(password)) {
        lev++;
    }

	if ((/[^0-9a-zA-Z]+/).test(password)) {
        lev++;
    }
	{{if $user.option.passwordlevel == 0}}
	if (password.length < 6) {
        tip.removeClass('gray').addClass('red');
        err.text(TOP.TEXT.PASSWORD_TOO_SHORT);
        $('#pwd-info').hide();
        ico.removeClass('icon_tick').addClass('icon icon_cross');
        return false;
    }
	{{elseif $user.option.passwordlevel >= 2}}
	if (password.length < 8 || lev < 3) {
		tip.removeClass('gray').addClass('red');
	    err.text(TOP.TEXT.PASSWORD_SAFE_LEVEL_HEIGHT);
	    $('#pwd-info').hide();
	    ico.removeClass('icon_tick').addClass('icon icon_cross');
	    return false;
	}
	{{elseif $user.option.passwordlevel == 1}}
	if (password.length < 8 || lev < 2) {
        tip.removeClass('gray').addClass('red');
        err.text(TOP.TEXT.PASSWORD_SAFE_LEVEL_MIDDLE);
        $('#pwd-info').hide();
        ico.removeClass('icon_tick').addClass('icon icon_cross');
        return false;
    }
	{{/if}}

	$('#pwd-info').hide();
	return true;
}
-->
</script>
</body>
</html>
