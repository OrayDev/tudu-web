<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.edit_contact}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>

</head>
<body>
<form id="theform" action="/contact/save" method="post" class="tab-panel">
<input type="hidden" name="contactid" value="{{$contact.contactid}}"/>
<input type="hidden" name="uniqueid" value="{{$contact.uniqueid}}"/>
<div class="tab-panel">
    <div class="tab-panel-body">
      <div class="toolbar">
        <div><button class="btn" type="submit" name="save">{{$LANG.save}}</button><button class="btn" type="button" name="cancle">{{$LANG.cancel}}</button></div>
      </div>
      <div class="settingbox">
      <div class="setting_account">
      	<div>
	        <table width="100%" border="0" cellspacing="0" cellpadding="0">
	           <tr>
	             <td valign="top" align="right" width="150">{{$LANG.avatar}}{{$LANG.cln}}</td>
	             <td><div class="picture"><img id="avatars-img" src="{{if $contact.fromuser}}/logo?email={{$contact.email}}{{else}}/logo?ctid={{$contact.contactid}}&tsid={{$user.tsid}}&{{1000|rand:9999}}{{/if}}" width="80" height="80" /><input type="hidden" value="" name="avatars" id="avatars" /></div><div style="margin-top:62px;"><button class="btn" name="changeavatar" type="button"{{if $contact.fromuser}} disabled{{/if}}>{{$LANG.avatar_change}}</button></div></td>
	           </tr>
	           <tr>
	             <td align="right" width="150">{{$LANG.true_name}}{{$LANG.cln}}</td>
	             <td><input class="input_text" name="truename" type="text" value="{{$contact.truename}}" /><span class="red">{{$LANG.must_tips}}</span></td>
	           </tr>
	           <tr>
	             <td align="right" width="150">{{$LANG.email_addr}}{{$LANG.cln}}</td>
	             <td><input class="input_text" name="email" type="text" value="{{$contact.email}}" maxlength="255"{{if $contact.fromuser}} disabled="disabled"{{/if}} /><span class="gray">{{$LANG.email_tips}}</span></td>
	           </tr>
	           <tr>
	             <td align="right" width="150">{{$LANG.mobile}}{{$LANG.cln}}</td>
	             <td><input class="input_text" name="mobile" value="{{$contact.mobile}}" type="text" maxlength="20" /></td>
	           </tr>
	        </table>
			<div align="right" style="width:150px;margin:10px 0" id="more"><span class="tree-ec-icon tree-elbow-plus" style="visibility: visible;"></span><a href="javascript:void(0)">{{$LANG.more_info}}</a></div>
	        <div id="moreinfo" style="display:none;">
	        <table width="100%" style="margin:0;">
	           <tr>
	           	 <td align="right" width="150">{{$LANG.corporation}}{{$LANG.cln}}</td>
	           	 <td><input class="input_text" name="corporation" value="{{$contact.properties.corporation}}" type="text" /></td>
	           </tr>
	           <tr>
                 <td align="right" width="150">{{$LANG.user_position}}{{$LANG.cln}}</td>
                 <td><input class="input_text" name="position" value="{{$contact.properties.position}}" type="text" /></td>
               </tr>
	           <tr>
	           	 <td align="right" width="150">{{$LANG.tel_num}}{{$LANG.cln}}</td>
	           	 <td><input class="input_text" name="tel" value="{{$contact.properties.tel}}" type="text" /></td>
	           </tr>
	           <tr>
	           	 <td align="right" width="150">{{$LANG.fax}}{{$LANG.cln}}</td>
	           	 <td><input class="input_text" name="fax" value="{{$contact.properties.fax}}" type="text" /></td>
	           </tr>
	           <tr>
	           	 <td align="right" width="150">{{$LANG.address}}{{$LANG.cln}}</td>
	           	 <td><input class="input_text" name="address" value="{{$contact.properties.address}}" type="text" /></td>
	           </tr>
	           <tr>
	             <td align="right" width="150">{{$LANG.birthday}}{{$LANG.cln}}</td>
	             <td>
	               <select name="bir-year" id="bir-year" style="margin-right:5px;">
		             <option value="">{{$LANG.year}}</option>
		             {{section name=year loop=70}}
		             <option value="{{math equation="2010-x" x=$smarty.section.year.index}}">{{math equation="2010-x" x=$smarty.section.year.index}}</option>
		             {{/section}}
		           </select>&nbsp;<select name="bir-month" id="bir-month" style="margin-right:5px;">
		             <option value="">{{$LANG.month}}</option>
		             {{section name=month loop=12}}
		             <option value="{{math equation="x+1" x=$smarty.section.month.index}}">{{math equation="x+1" x=$smarty.section.month.index}}</option>
		             {{/section}}
		           </select>&nbsp;<select name="bir-day" id="bir-day" style="">
		              <option value="">{{$LANG.day}}</option>
		           </select>
	             </td>
	           </tr>
	           <tr>
	           	 <td align="right" width="150">QQ/MSN{{$LANG.cln}}</td>
	           	 <td><input class="input_text" name="im" value="{{$contact.properties.im}}" type="text" /></td>
	           </tr>
	           <tr>
	           	 <td align="right" width="150">{{$LANG.secondary_email}}{{$LANG.cln}}</td>
	           	 <td><input class="input_text" name="mailbox" value="{{$contact.properties.mailbox}}" type="text" /></td>
	           </tr>
	           <tr>
	           	 <td valign="top" align="right" width="150">{{$LANG.remark}}{{$LANG.cln}}</td>
	           	 <td><textarea rows="5" cols="24" name="memo">{{$contact.memo}}</textarea></td>
	           </tr>
			</table>
			</div>

			<div style="margin-bottom:15px;">

			<div align="right" style="float:left; width:155px; line-height:24px;">{{$LANG.contact_group}}{{$LANG.cln}}</div>
            <div id="grouplist" style="margin-left:155px; line-height:24px; ">
                {{foreach item=group from=$groups}}
                {{if !$group.issystem}}
                <p><input type="checkbox" name="group[]" value="{{$group.groupid}}" {{if in_array($group.groupid, $groupid)}}checked="checked"{{/if}} />{{$group.groupname}}</p>
                {{/if}}
                {{/foreach}}
            </div>
            <div style="margin-left:155px;clear:both;">
            <div style="line-height:20px;"><a id="creategroup">+{{$LANG.create_group}}</a></div>
            <div id="newgroup" style="display:none;" class="position">
              <input class="input_text" type="text" name="groupname" />&nbsp;&nbsp;<button class="btn" type="button" name="enter">{{$LANG.confirm}}</button>&nbsp;&nbsp;<button class="btn" type="button" name="cal">{{$LANG.cancel}}</button>
            </div>
            </div>
            </div>
            {{if 0}}
            <table width="100%" style="margin:0;">
                <tr>
                   <td>
                       <div><a id="creategroup">+{{$LANG.create_group}}</a></div>
                       <div id="newgroup" style="display:none;" class="position">
                          <input class="input_text" type="text" name="groupname" />&nbsp;&nbsp;<button class="btn" type="button" name="enter">{{$LANG.confirm}}</button>&nbsp;&nbsp;<button class="btn" type="button" name="cal">{{$LANG.cancel}}</button>
                       </div>
                   </td>
                </tr>
            </table>
            {{/if}}
	     </div>
	  </div>
      </div>
      <div class="toolbar">
        <div><button class="btn" type="submit" name="save">{{$LANG.save}}</button><button class="btn" type="button" name="cancle">{{$LANG.cancel}}</button></div>
      </div>
    </div>
</div>
</form>
<div class="pop_wrap pop_edit" id="avatar-win-src" style="display:none;position:absolute;background:#ebf4d8;width:470px">
    <div id="avatarwin" class="pop">
        <div class="pop_header"><strong>{{$LANG.avatar_modify}}</strong><a class="icon icon_close close"></a></div>
        <div class="pop_body">
           <form id="uploadform" action="/contact/upload" method="post" enctype="multipart/form-data">
            <p class="gray">{{$LANG.avatar_upload_tips}}</p>
            <p>{{$LANG.select_photo}}{{$LANG.cln}}<input class="input_file" name="avatar-file" id="avatar-file" type="file" /><input type="submit" class="btn" id="uploadbtn" name="confirm" value="{{$LANG.upload_now}}"></p>
           </form>
           <div class="line_thin"></div>

            <div class="edit_wrap" id="avatar-edit" style="display:none">
               <form id="avatarform" action="/contact/updateavatar" method="post">
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
<script type="text/javascript">
<!--
$(function(){
	TOP.Frame.title('{{$LANG.edit_contact}}');
	{{if $contact.contactid}}
	TOP.Frame.hash('m=contact/modify&ctid={{$contact.contactid}}');
	{{else}}
	TOP.Frame.hash('m=contact/modify');
	{{/if}}

	$('#bir-year, #bir-month').change(function(){fillDateSelect()});
    $('#bir-year').val(parseInt('{{$birthdayinfo.birthyear}}'));
    $('#bir-month').val(parseInt('{{$birthdayinfo.birthmonth}}'));
    fillDateSelect();
    $('#bir-day').val(parseInt('{{$birthdayinfo.birthdate}}'));

    $('button[name="cancle"]').click(function(){
    	location = '{{$back|default:"/contact/?type=contact"}}';
    });

    $("#more").bind('click', function(){
    	var icon = $(this).find('span.tree-ec-icon');
    	$('#moreinfo').toggle();
    	icon.toggleClass('tree-elbow-minus');
    });

    $('button[name="changeavatar"]').click(function(){
		avatarWin();
	});

	// 添加联系组
    $('#creategroup').click(function(){
    	$('#newgroup').show();
    });
	// 取消添加联系组
    $('button[name="cal"]').click(function(){
        $('input[name="groupname"]').val('');
    	$('#newgroup').hide();
    });
	// 确定提交联系组数据
    $('button[name="enter"]').click(function(){
        var groupName = $('input[name="groupname"]').val();
    	$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {groupname: groupName},
			url: '/contact/group.save',
			success: function(ret) {
			   TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
			   $('input[name="groupname"]').val('');
		       $('#newgroup').hide();
		       $('#grouplist').append('<p><input type="checkbox" name="group[]" value="'+ret.data.groupid+'" checked="checked" />'+ret.data.groupname+'</p>');
		       TOP.Contact.clear();
			},
			error: function(res) {
			    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
    });

    $('#theform').submit(function(){return false;});
	$('#theform').submit(function(){
		if (!$('input[name="truename"]').val()) {
            $('input[name="truename"]').focus();
            return TOP.showMessage(TOP.TEXT.MISSING_PEOPLE_NAME);
        }
		/*if (!$('input[name="email"]').val()) {
            $('input[name="email"]').focus();
            return TOP.showMessage(TOP.TEXT.MISSING_EMAIL_ADDRESS);
        }
        if ($('input[name="email"]').attr('disabled', true)) {
        	$('input[name="email"]').attr('disabled', false);
        }*/
		var form = $(this);
		var data = form.serializeArray();
		TOP.showMessage(TOP.TEXT.POSTING_DATA, 0, 'success');
        form.find('input, button, select').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: form.attr('action'),
            data: data,
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);

                if(ret.success) {
                	TOP.Contact.clear();
                	if (ret.data && ret.data.contactid) {
                        location = '/contact/view?ctid=' + ret.data.contactid;
                    } else {
                        location.assign('/contact/?type=contact');
                    }
                }
                form.find('input, button, select').attr('disabled', false);
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
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
                //userform = _$('#userform'),
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
                        .attr('src', '/contact/avatar/?hash=' + ret.data.hash + '&rnd=' + Math.random())
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

        _$('#avatarsubmit').click(function(){
            var avatarform = _$('#avatarform');
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
                    TOP.showMessage(ret.message, 5000, 'success');

                    if (ret.success) {
                        if (ret.data.avatar) {
                            $('#avatars').val(ret.data.avatar);
                            $('#avatars-img').attr('src', '/contact/avatar/?hash=' + ret.data.avatar + '&t=' + Math.random());
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
    	TOP.loadJs('{{$options.site.tudu}}/js/jquery.ajaxupload.js', function(){
        	TOP.loadJs('{{$options.site.tudu}}/js/jquery.Jcrop.js', function(){
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
-->
</script>
</body>
</html>