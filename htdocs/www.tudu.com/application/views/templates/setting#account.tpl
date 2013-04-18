<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.account_setting}}</title>
{{include file="^style.tpl"}}
<link href="{{$options.sites.static}}/js/Jcrop/css/jquery.Jcrop.css" type="text/css" rel="stylesheet" />
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
</head>
<body>
	<div class="position">
   	  <p><strong class="title">{{$LANG.account_setting}}</strong></p>
</div>
<form id="theform" action="/setting/userinfo" method="post" class="tab_panel">
	{{include file="setting^tab.tpl" tab="account}}
	<div class="tab-panel-body">
        	<div class="settingbox">
            <div class="setting_account">
            	<div class="settingdiv">
                    <h3 class="setting_tit">{{$LANG.change_password}}</h3>
                    <div class="line_bold"></div>
                    <div class="setting_box">
                   	 	<button class="btn" type="button" name="changepwd">{{$LANG.change_password}}</button>
                    	<p class="setting_tips">{{$LANG.change_password_tip}}</p>
                    </div>
                </div>
                <div class="settingdiv">
                    <strong class="setting_tit">{{$LANG.user_base_info}}</strong>&nbsp;<span class="gray">({{$LANG.user_info_tips}})</span>
                    <div class="line_bold"></div>
                	<table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td align="right" width="150">{{$LANG.accountname}}{{$LANG.cln}}</td>
                        <td><span class="gray">{{$userinfo.userid}}@{{$userinfo.orgid}}</span></td>
                      </tr>
                      <tr>
                        <td align="right" width="150">{{$LANG.truename}}{{$LANG.cln}}</td>
                        <td><span class="gray">{{$userinfo.truename}}</span></td>
                      </tr>
                      <tr>
                        <td align="right" width="150">{{$LANG.user_status}}{{$LANG.cln}}</td>
                        <td><span class="gray">{{assign var="status" value="user_status_"|cat:$userinfo.status}}{{$LANG[$status]}}</span></td>
                      </tr>
                      {{if 0}}
                      <tr>
                        <td align="right" width="150">邮箱空间{{$LANG.cln}}</td>
                        <td><span class="gray">2G</span></td>
                      </tr>
                      {{/if}}
                      <tr>
                        <td valign="top" align="right" width="150">{{$LANG.position}}{{$LANG.cln}}</td>
                        <td><p class="gray">{{$userinfo.position}}</p></td>
                      </tr>

                    </table>
                 </div>
                <div class="settingdiv">
                    <h3 class="setting_tit">{{$LANG.user_detail_info}}</h3>
                    <div class="line_bold"></div>
                	<table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td valign="top" align="right" width="150">{{$LANG.avatar}}{{$LANG.cln}}</td>
                        <td><div class="picture"><img id="avatars-img" src="/logo?unid={{$user.uniqueid}}" width="80" height="80" /><input type="hidden" value="" name="avatars" id="avatars" /></div><div style="margin-top:62px;"><button class="btn" name="changeavatar" type="button">更改</button></div></td>
                      </tr>
                      <tr>
                        <td align="right" width="150">{{$LANG.nick}}{{$LANG.cln}}</td>
                        <td><input class="input_text" name="nick" type="text" value="{{$userinfo.nick}}"></td>
                      </tr>
                      <tr>
                        <td align="right" width="150">{{$LANG.gender}}{{$LANG.cln}}</td>
                        <td><input name="gender" type="radio" value="1"{{if $userinfo.gender == 1}} checked="checked"{{/if}} id="m"><label for="m">男</label>&nbsp;&nbsp;<input name="gender" type="radio" value="0"{{if !$userinfo.gender}} checked="checked"{{/if}} id="w"><label for="w">女</label></td>
                      </tr>
                      <tr>
                        <td align="right" width="150">{{$LANG.mailbox}}{{$LANG.cln}}</td>
                        <td><input style="width:230px" class="input_text" name="email" value="{{$userinfo.mailbox}}" type="text" maxlength="50" /></td>
                      </tr>
                      <tr>
                        <td align="right" width="150">{{$LANG.idnumber}}{{$LANG.cln}}</td>
                        <td><input style="width:230px" class="input_text" name="idnumber" value="{{$userinfo.idnumber}}" type="text" maxlength="50" /></td>
                      </tr>
                      <tr>
                        <td align="right" width="150">{{$LANG.birthday}}{{$LANG.cln}}</td>
                        <td>
                        {{assign var="year" value=$smarty.now|date_format:"%Y"}}
                        <select name="bir-year" id="bir-year" style="margin-right:5px;">
	                    <option value=""></option>
	                    {{section name=year loop=70}}
	                    <option value="{{math equation="$year-x" x=$smarty.section.year.index}}">{{math equation="$year-x" x=$smarty.section.year.index}}</option>
	                    {{/section}}
	                    </select>{{$LANG.year}}&nbsp;<select name="bir-month" id="bir-month" style="margin-right:5px;">
	                    <option value=""></option>
	                    {{section name=month loop=12}}
	                    <option value="{{math equation="x+1" x=$smarty.section.month.index}}">{{math equation="x+1" x=$smarty.section.month.index}}</option>
	                    {{/section}}
	                    </select>{{$LANG.month}}&nbsp;<select name="bir-day" id="bir-day" style="">
	                    <option value=""> </option>
	                    </select>{{$LANG.day}}
                        </td>
                      </tr>
                      {{if 0}}
                      <tr>
                        <td align="right" width="150">办公分机{{$LANG.cln}}</td>
                        <td><input style="width:230px" class="input_text" name="" type="text">&nbsp;<a href="javascript:void(0);" class="icon icon_plus"></a></td>
                      </tr>
                      {{/if}}
                      <tr>
                        <td align="right" width="150">{{$LANG.tel}}{{$LANG.cln}}</td>
                        <td><input style="width:230px" class="input_text" name="tel" value="{{$userinfo.tel}}" type="text" maxlength="20" />{{if 0}}&nbsp;<a href="javascript:void(0);" class="icon icon_plus"></a>{{/if}}</td>
                      </tr>
                      <tr>
                        <td align="right" width="150">{{$LANG.mobile}}{{$LANG.cln}}</td>
                        <td><input style="width:230px" class="input_text" name="mobile" value="{{$userinfo.mobile}}" type="text" maxlength="20" />{{if 0}}&nbsp;<a href="javascript:void(0);" class="icon icon_plus"></a>{{/if}}</td>
                      </tr>
                    </table>
                 </div>

            </div>
            </div>
            </div>

      	<div class="toolbar">
        	<div><button class="btn" type="submit">{{$LANG.save_change}}</button><button class="btn" type="button" name="cancel">{{$LANG.cancel}}</button></div>
        </div>
        </div>
</form>
<div class="pop_wrap pop_edit" id="avatar-win-src" style="display:none;position:absolute;background:#ebf4d8;width:470px">
    <div id="avatarwin" class="pop">
        <div class="pop_header"><strong>{{$LANG.avatar_modify}}</strong><a class="icon icon_close close"></a></div>
        <div class="pop_body">
           <form id="uploadform" action="/setting/upload" method="post" enctype="multipart/form-data">
            <p class="gray">{{$LANG.avatar_upload_tips}}</p>
            <p>{{$LANG.select_photo}}{{$LANG.cln}}<input class="input_file" name="avatar-file" id="avatar-file" type="file" /><input type="submit" class="btn" id="uploadbtn" name="confirm" value="{{$LANG.upload_now}}"></p>
           </form>
           <div class="line_thin"></div>

            <div class="edit_wrap" id="avatar-edit" style="display:none">
               <form id="avatarform" action="/setting/updateavatar" method="post">
               <input type="hidden" name="x" /><input type="hidden" name="y" />
               <input type="hidden" name="width" /><input type="hidden" name="height" />
               <input type="hidden" name="hash" value="" />
               <div class="edit_left"><p>{{$LANG.avatar_modify}} </p><div class="edit_box_big"><img src="" id="avatar-src" style="display:none" /></div></div>
               <div class="edit_right"><p>{{$LANG.avatar_thumb}}</p><div class="edit_box_small"><img src="" id="avatar-preview" width="80" height="80" style="display:none" /></div></div>
               <div class="clear"></div>
               </form>
           </div>
    </div>
    <div class="pop_footer"><button type="button" id="avatarsubmit" class="btn" disabled="disabled">{{$LANG.confirm}}</button><button type="button" class="btn close">{{$LANG.cancel}}</button></div>
    </div>
</div>
</body>
<script type="text/javascript">
<!--
var avatarScale;
$(function(){
	TOP.Label.focusLabel('');
    TOP.Frame.title('{{$LANG.account_setting}}');
    TOP.Frame.hash('m=setting/account');

	$('#bir-year, #bir-month').change(fillDateSelect());

	$('button[name="changepwd"]').click(function(){
		location = '/setting/account?type=password';
	});

	$('button[name="changeavatar"]').click(function(){
		avatarWin();
	});

	$('button[name="cancel"]').click(function(){
        location = '/frame/home';
    });

    $('#bir-year, #bir-month').change(function(){fillDateSelect()});

    $('#bir-year').val(parseInt('{{$userinfo.birthyear}}'));
    $('#bir-month').val(parseInt('{{$userinfo.birthmonth}}'));
    fillDateSelect();
    //$('#bir-day').val(parseInt('{{$userinfo.birthdate}}'));
    var d = $('#bir-day')[0];
    for (var i = 0, c = d.options.length; i < c; i++) {
        if (d.options[i].value == '{{$userinfo.birthdate|intval}}') {
            setTimeout(function(){d.options[i].selected = 'selected';}, 10);
            break;
        }
    }

    $('#theform').submit(function(){return false;});
    $('#theform').submit(function(){
        var form = $(this);
        var tel  = form.find('input[name="tel"]').val();
        var mobile = form.find('input[name="mobile"]').val();

        if (tel && !(/^[\d][\-\d]+[\d]$/).test(tel)) {
            TOP.showMessage(TOP.TEXT.INVALID_TEL_NUMBER);
            form.find('input[name="tel"]').focus();
            return ;
        }

        if (mobile && !(/^[\d][\-\d]+[\d]$/).test(mobile)) {
        	TOP.showMessage(TOP.TEXT.INVALID_MOBILE_NUMBER);
            form.find('input[name="mobile"]').focus();
            return ;
        }

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

                if (ret.success) {
                    location.reload(true);  // 强制刷新，更新头像缓存
                }
            },
            error: function(res) {
            	TOP.showMessage(TOP.TEXT.PROCESS_ERROR);
                form.find('input, button, select').attr('disabled', false);
            }
        });
    });
});

function fillDateSelect() {
        var year  = $('#bir-year').val();
        var month = $('#bir-month').val();

        if (!year || !month) {
            return false;
        }

        if (month > 11) {
            month = 0;
            ++year;
        }
        var d = new Date(year, month, 1), i = 0, day = $('#bir-day');
        var days = new Date(d.getTime() - 86400 * 1000).getDate();

        day.empty();
        for (i; i < days; i++) {
            d = i + 1;
            day.append('<option value="'+d+'">'+d+'</option>');
        }
}

function avatarWin() {
    if (!TOP.avatarWin) {
        var win = $('#avatar-win-src');
        TOP.avatarWin = TOP.appendWindow('avatar-win', win.html(), {
            draggable: true,
            width:470,
            onShow: function(){},
            onClose: function(){
                TOP.avatarWin = TOP.avatarWin.destroy();
            }
        });

        var scope = TOP.document.body,
            _$    = TOP.getJQ()
            avatarScale = 1
            jcrop = null;

        _$('#uploadform').submit(function(){return false;});
        _$('#uploadform').submit(function(e){
            var form = $(this),
                userform = _$('#userform'),
                avatarform = _$('#avatarform');

            _$('#avatar-src').attr('src', '');
            _$('#avatar-preview').attr('src', '');

            _$.ajaxUpload({
                url: form.attr("action"),
                file: _$('#avatar-file')[0],
                data: {},
                dataType: "json",
                success: function(ret) {
                    TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);

                    if (ret.success) {
                    	_$('#avatarsubmit').attr('disabled', false);
                        _$('#avatar-edit').show();
                        avatarScale = 1;

                        _$('#avatar-src')
                        .css({width: 'auto', height: 'auto'});

                        avatarform.find(':hidden[name="hash"]').val(ret.data.hash);
                        avatarform.find('img')
                        .attr('src', '/setting/avatar/?hash=' + ret.data.hash + '&rnd=' + Math.random())
                        .hide();

                        TOP.avatarWin.center();
                        avatarform.find('#avatar-src').bind('load', function(){
                            avatarform.find('img').show();

                            var w = this.width,
                                h = this.height;

                            if (w > 233 || h > 198) {
                            	_$('.edit_box_big')
                                .css({width: 233 + 'px', height: 198 + 'px'});

                                if (w > h) {
                                    avatarScale = 233 / w;
                                } else {
                                    avatarScale = 198 / h;
                                }
                            }

                            var width = w * avatarScale,
                                height = h * avatarScale;

                            _$('#avatar-src')
                            .css({width: width + 'px', height: height + 'px'});
                            _$('.edit_box_big')
                            .css({width: width + 'px', height: height + 'px'});

                            if (jcrop) {
                                jcrop.destroy();
                            }

                            jcrop = _$.Jcrop('#avatar-src', {
                                onSelect: _avatarChangeSize,
                                onChange: _avatarChangeSize,
                                aspectRatio: 1
                            });

                            avatarform.find('#avatar-src').unbind('load');
                        });
                    }
                },
                error: function(res){
                }
            });
        });

        $('#avatarsubmit', scope).click(function(){
            var avatarform = $('#avatarform', scope);
            var hash = avatarform.find('input[name="hash"]').val();

            if (!hash) {
                TOP.avatarWin.close();
                return ;
            }

            var data = {
                hash: avatarform.find('input[name="hash"]').val(),
                x: avatarform.find('input[name="x"]').val(),
                y: avatarform.find('input[name="y"]').val(),
                width: avatarform.find('input[name="width"]').val(),
                height: avatarform.find('input[name="height"]').val()
            };

            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: data,
                url: avatarform.attr('action'),
                success: function(ret) {
                    TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);

                    if (ret.success) {
                        if (ret.data.avatar) {
                            $('#avatars').val(ret.data.avatar);
                            $('#avatars-img').attr('src', '/setting/avatar/?hash=' + ret.data.avatar + '&t=' + Math.random());
                        }

                        TOP.avatarWin.close();
                    }
                },
                error: function(res) {
                	TOP.avatarWin.close();
                }
            });
        });
    }

    TOP.avatarWin.show();

    if (!_$.ajaxUpload || !_$.Jcrop) {
        TOP.showMessage(TOP.TEXT.LOADING_AVATAR_LIBRARY);
        _$('#uploadform').find('button, input').attr('disabled', true);
    	TOP.loadJs('{{$options.site.www}}/js/jquery.ajaxupload.js', function(){
        	TOP.loadJs('{{$options.site.www}}/js/jquery.Jcrop.js', function(){
        		TOP.showMessage(null);
            	_$('#uploadform').find('button, input').attr('disabled', false);
            }, function(){TOP.showMessage(TOP.TEXT.LOAD_JS_ERROR, 5000);TOP.avatarWin.close();});
    	}, function(){TOP.showMessage(TOP.TEXT.LOAD_JS_ERROR, 5000);TOP.avatarWin.close();});
    }

    function _avatarChangeSize(coords) {
        var _$ = TOP.getJQ();
        var form = _$('#avatarform');
        if (parseInt(coords.w) > 0)
        {
            var rx = 80 / coords.w;
            var ry = 80 / coords.h;

            _$('#avatar-preview').css({
                width: Math.round(rx * _$('#avatar-src').width()) + 'px',
                height: Math.round(ry * _$('#avatar-src').height()) + 'px',
                marginLeft: '-' + Math.round(rx * coords.x) + 'px',
                marginTop: '-' + Math.round(ry * coords.y) + 'px'
            });

            form.find('input[name="x"]').val(Math.round(coords.x / avatarScale));
            form.find('input[name="y"]').val(Math.round(coords.y / avatarScale));
            form.find('input[name="width"]').val(Math.round(coords.w / avatarScale));
            form.find('input[name="height"]').val(Math.round(coords.h / avatarScale));
        } else {
            form.find('input[name!="hash"]').val('');
            _$('#avatar-preview').css({
                width: '80px',
                height: '80px',
                marginLeft: 0,
                marginTop: 0
            });
        }
    }
}


function nothing(e) {
    TOP.stopEventBuddle(e);
	return null;
}
-->
</script>
</html>
