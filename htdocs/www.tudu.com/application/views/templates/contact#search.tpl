<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.search_contact}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1023" type="text/javascript"></script>
<script type="text/javascript">
function getTop() {
    return top;
}

var TOP = getTop();

</script>

<style type="text/css">
<!--
html, body{height:100%}
-->
</style>
</head>
<body class="contacts">
<div class="position">
    <div style="width:190px;float:right">
    <div class="search_box"><input class="input_text" name="keyword" id="keyword" type="text" title="{{$LANG.contact_search}}" value="{{$params.keyword}}" style="width:183px" /><a href="javascript:void(0);" id="dosearch" class="icon icon_search_2"></a></div>
    </div>
    <div style="width:570px;float:right;text-align:right;margin-right:10px">
    <div class="search_box">
    </div>
    </div>
    <div>
	<p><strong class="title">{{$LANG.search_contact}}</strong></p>
	</div>
</div>
<div class="container">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="gird_fix">
      <tr>
        <td valign="top" style="padding-right:5px">
          <div class="c_left">
          <div class="panel">
              <div id="float-toolbar" class="float-toolbar" style="position: relative;display:block;">
              <div class="toolbar">
                  <div>
                     <button class="btn" type="button" name="create">{{$LANG.create_contact}}</button>
                     <select name="group" tabindex="0">
                        <option value="">{{$LANG.add_into_group}}</option>
                        {{foreach item=group from=$groups}}
				        {{if !$group.issystem}}
				        <option value="{{$group.groupid}}">{{$group.groupname}}</option>
				        {{/if}}
                        {{/foreach}}
                     </select>
                     <span class="tb-sep"></span>
                     <button disabled class="btn" type="button" name="tudu">{{$LANG.send_tudu}}</button>
                     {{if 0}}<button disabled class="btn" type="button">发起聊天</button><button disabled class="btn" type="button">写信</button>{{/if}}
                     <button disabled class="btn" type="button" name="delete">{{$LANG.delete}}</button>
                  </div>
                  <div class="page">{{$LANG.total}} <strong><span class="count"></span></strong> {{$LANG.contact_unit}}</div>
              </div>
              <table cellspacing="0" cellpadding="0" class="grid grid_list list_over">
                <thead>
                  <tr>
                    <td class="first" width="30"><input name="checkall" type="checkbox" value=""></td>
                    <td width="350">{{$LANG.true_name}}</td>
                    <td width="150">{{$LANG.mobile}}</td>
                    <td style="min-width:50px;">{{$LANG.email_address}}</td>
                  </tr>
                </thead>
              </table>
              </div>
              
              <div class="panel-body contact-list">
                  <table cellspacing="0" cellpadding="0" class="grid grid_list list_over">
                    <tbody id="contact-list">
                    {{foreach item=user from=$users}}
                    <tr _uid="{{$user.userid}}" _email="{{$user.address}}">
                      <td class="first" align="center" width="30"><input name="addr[]" type="checkbox" value="{{$user.address}}" _identify="{{$user.address|cat:' '|cat:$user.truename|escape:'url'}}" system="1"/></td>
                      <td width="350"><span class="icon icon_sys_contact" title="{{$LANG.addrbook}}"></span>{{$user.truename}}</td>
                      <td align="center" width="150">{{$user.mobile|default:'-'}}</td>
                      <td align="center" style="min-width:50px;">{{$user.userid}}@{{$user.orgid}}</td>
                    </tr>
                    {{/foreach}}
                    {{foreach item=pcontact from=$personal}}
                    <tr _ctid="{{$pcontact.contactid}}" _groups="{{foreach item=groupid from=$pcontact.groups}}{{if strpos($groupid, '^') === false}}{{$groupid}}|{{/if}}{{/foreach}}">
                      <td class="first" align="center" width="30"><input name="addr[]" type="checkbox" value="{{$pcontact.contactid}}" _identify="{{$pcontact.email|cat:' '|cat:$pcontact.truename|escape:'url'}}" /></td>
                      <td width="350"><div><span class="icon icon_contact" title="{{$LANG.personal_addrbook}}"></span>{{$pcontact.truename}}</div><div class="label_div"></div></td>
                      <td align="center" width="150">{{$pcontact.mobile|default:'-'}}</td>
                      <td align="center" style="min-width:50px;">{{$pcontact.email|default:'-'}}</td>
                    </tr>
                    {{/foreach}}
                    {{if !$users && !$personal}}
				    <tr>
				      <td colspan="4" style="text-align:center;padding:35px 0">{{$LANG.empty_contact_info}}</td>
				    </tr>
					{{/if}}
                    </tbody>
                  </table>
              </div>
          </div>
          </div>
        </td>
        {{include file="contact#index^list.tpl"}}
	</tr>
</table>
</div>
<div style="display:none">
<table id="group-tpl" cellspacing="0" cellpadding="0" class="flagbg"><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr><tr><td class="falg_line"></td><td class="tag_txt"></td><td class="tag_close"></td><td class="falg_line"></td></tr><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr></table>
</div>
<script src="{{$options.sites.static}}/js/contact.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
var _CUS_GROUPS = {};
{{foreach item=item from=$groups name=group}}{{if !$item.issystem}}_CUS_GROUPS['{{$item.groupid}}'] = {name: '{{$item.groupname}}', bgcolor: '{{$item.bgcolor}}'};{{/if}}{{/foreach}}

$(function(){

	TOP.Label.focusLabel('');
    TOP.Frame.title('{{$LANG.search_contact}}');
    TOP.Frame.hash('m=contact/search&keyword={{$keyword}}');

    var currUrl = '{{$smarty.server.REQUEST_URI|escape:'url'}}';
    var params = {
		deptid: {{if $params.deptid}}'{{$params.deptid}}'{{else}}null{{/if}}
	};
    var forbid = {
    	liststyle:{{if !$users && !$personal}}false{{else}}true{{/if}}
    };
    Contact.initSearch(currUrl, params, forbid);
    
});
-->
</script>
</body>
</html>
