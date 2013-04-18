<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.contact}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script type="text/javascript">
<!--
{{if $email}}
var LH = 'm=contact/view&email={{$profile.userid}}@{{$profile.orgid}}';
{{else}}
var LH = 'm=contact/view&ctid={{$contact.contactid}}';
{{/if}}
if (top == this) {
	location = '/frame#' + LH;
}
-->
</script>
</head>
<body>
{{if $email}}
<div class="tab_panel">
	<div class="toolbar">
        <div>
        <button class="btn" type="button" name="back">{{$LANG.back}}</button><span class="tb-sep"></span><button class="btn" type="button" name="send">{{$LANG.send_tudu}}</button>
        </div>
    </div>
	<div class="tab-panel-body">
	   <div class="settingbox" style="padding:18px">
        <table width="100%" border="0" cellspacing="1" cellpadding="2">
		  <tr>
		    <td align="center" valign="middle" width="70">
		        <img src="/logo?unid={{$profile.uniqueid}}" width="40" height="40" />
		    </td>
		    <td style="line-height:18px">
		        <p><span class="f14 b">{{$userinfo.truename}}</span>&nbsp;&nbsp;&nbsp;[<a href="/tudu?search=query&cat=all&to={{$userinfo.truename|escape:'url'}}">{{$LANG.contact_tudu}}</a>]</p>
		        <p>{{$imstatus.chat}}&nbsp;</p>
		    </td>
		  </tr>
		</table>
		<table width="100%" border="0" cellspacing="4" cellpadding="5" style="margin-top:10px">
          <tr>
            <td width="60" align="right">{{$LANG.user_position}}{{$LANG.cln}}</td>
            <td>{{$userinfo.position|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.department}}{{$LANG.cln}}</td>
            <td>{{$dept.deptname|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.tudu_account}}{{$LANG.cln}}</td>
            <td>{{$profile.userid}}@{{$profile.orgid}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.mailbox}}{{$LANG.cln}}</td>
            <td>{{$userinfo.mailbox|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.tel_num}}{{$LANG.cln}}</td>
            <td>{{$userinfo.tel|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.mobile}}{{$LANG.cln}}</td>
            <td>{{$userinfo.mobile|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.birthday}}{{$LANG.cln}}</td>
            <td>{{$userinfo.birthday|date_format:'%Y-%m-%d'|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.constellation}}{{$LANG.cln}}</td>
            <td>-</td>
          </tr>
        </table>
        </div>
    </div>
    <div class="toolbar">
        <div>
        <button class="btn" type="button" name="back">{{$LANG.back}}</button><span class="tb-sep"></span><button class="btn" type="button" name="send">{{$LANG.send_tudu}}</button>
        </div>
    </div>
</div>
{{else}}
<div class="tab_panel">
	<div class="toolbar">
        <div>
        <button class="btn" type="button" name="back">{{$LANG.back}}</button><span class="tb-sep"></span><button class="btn" type="button" name="send">{{$LANG.send_tudu}}</button><span class="tb-sep"></span><button class="btn" type="button" name="modify">{{$LANG.edit_info}}</button><button class="btn" type="button" name="delete">{{$LANG.delete}}</button>
        </div>
    </div>
	<div class="tab-panel-body">
	   <div class="settingbox" style="padding:18px">
        <table width="100%" border="0" cellspacing="1" cellpadding="2">
		  <tr>
		    <td align="center" valign="middle" width="70">
		        <img src="{{if $contact.fromuser}}/logo?email={{$contact.email}}{{else}}/logo?ctid={{$contact.contactid}}&tsid={{$user.tsid}}&{{1000|rand:9999}}{{/if}}" width="40" height="40" />
		    </td>
		    <td style="line-height:18px">
		        <p><span class="f14 b">{{$contact.truename}}</span></p>
		    </td>
		  </tr>
		</table>
		<table width="100%" border="0" cellspacing="4" cellpadding="5" style="margin-top:10px">
          <tr>
            <td width="80" align="right">{{$LANG.email_addr}}{{$LANG.cln}}</td>
            <td>{{$contact.email|default:'-'}}{{if $contact.email}}<span class="gray">{{$LANG.email_tips}}</span>{{/if}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.mobile}}{{$LANG.cln}}</td>
            <td>{{$contact.mobile|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.corporation}}{{$LANG.cln}}</td>
            <td>{{$contact.properties.corporation|default:'- '}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.user_position}}{{$LANG.cln}}</td>
            <td>{{$contact.properties.position|default:' -'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.tel_num}}{{$LANG.cln}}</td>
            <td>{{$contact.properties.tel|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.fax}}{{$LANG.cln}}</td>
            <td>{{$contact.properties.fax|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.address}}{{$LANG.cln}}</td>
            <td>{{$contact.properties.address|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.birthday}}{{$LANG.cln}}</td>
            <td>{{$contact.properties.birthday|date_format:'%Y-%m-%d'|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">QQ/MSN{{$LANG.cln}}</td>
            <td>{{$contact.properties.im|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.secondary_email}}{{$LANG.cln}}</td>
            <td>{{$contact.properties.mailbox|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.remark}}{{$LANG.cln}}</td>
            <td>{{$contact.memo|default:'-'}}</td>
          </tr>
          <tr>
            <td align="right">{{$LANG.contact_group}}{{$LANG.cln}}</td>
            <td>
            {{if $count==0}}
            -
            {{else}}
				{{foreach item=group from=$groups}}
				{{if !$group.issystem}}
				<a href="/contact/?type=contact&groupid={{$group.groupid}}" style="margin-right:5px;">{{if in_array($group.groupid, $groupid)}}{{$group.groupname}}{{/if}}</a>
				{{/if}}
				{{/foreach}}
			{{/if}}
			</td>
          </tr>
        </table>
        </div>
    </div>
    <div class="toolbar">
        <div>
        <button class="btn" type="button" name="back">{{$LANG.back}}</button><span class="tb-sep"></span><button class="btn" type="button" name="send">{{$LANG.send_tudu}}</button><span class="tb-sep"></span><button class="btn" type="button" name="modify">编辑资料</button><button class="btn" type="button" name="delete">删除</button>
        </div>
    </div>
</div>
{{/if}}
<script type="text/javascript">
<!--
$(function(){
	TOP.Label.focusLabel('');
	TOP.Frame.title('{{$LANG.contact}}');
	TOP.Frame.hash(LH);

	$('button[name="back"]').click(function(){
		{{if $email}}
		location = '{{$back|default:"/contact/"}}';
		{{else}}
		location = '{{$back|default:"/contact/?type=contact"}}';
		{{/if}}
	});

	{{if !$email}}
	$('button[name="modify"]').click(function(){
		location = '/contact/modify?ctid={{$contact.contactid}}&back={{$smarty.server.REQUEST_URI|escape:'url'}}';
	});
	$('button[name="delete"]').click(function(){
		deleteContact('{{$contact.contactid}}', '/contact/?type=contact');
	});
	{{/if}}

	$('button[name="send"]').click(function(){
		{{if $email}}
		location = '/tudu/modify?to={{$email|cat:' '|cat:$userinfo.truename|escape:'url'}}';
		{{else}}
		location = '/tudu/modify/?to={{$contact.email|cat:' '|cat:$contact.truename|escape:'url'}}';
		{{/if}}
	});

});

{{if !$email}}
function deleteContact(contactId, back) {
	if (!confirm(TOP.TEXT.CONFIRM_DELETE_CONTACT)) {
		return false;
	}

	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: '/contact/delete?ctid=' + contactId,
		success: function(ret) {
		   TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
		   if (ret.success) {
			    location = back;
		   }
		},
		error: function(res) {
		    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
		}
	});
}
{{/if}}
-->
</script>
</body>
</html>
