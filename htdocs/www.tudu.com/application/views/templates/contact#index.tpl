<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.addrbook}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1019" type="text/javascript"></script>
<script type="text/javascript">
<!--
var LH = 'm=contact{{foreach key=key item=val from=$params}}&{{$key}}={{$val}}{{/foreach}}&page={{$pageinfo.currpage}}';
if (top == this) {
	location = '/frame#' + LH;
}
-->
</script>

<style type="text/css">
<!--
html, body{height:100%}
.grid_list td {border-left: 1px solid #fff;border-right: 1px solid #fff;}
.list_over .over td{border-left: 1px solid #f3f3f3;border-right: 1px solid #f3f3f3;}
.list_over .over td.first {border-left: medium none;}
-->
</style>
</head>
<body class="contacts">
<div class="position" style="padding:5px 0;">
    <div style="width:190px;float:right">
    <div class="search_box"><input class="input_text" name="keyword" id="keyword" type="text" title="{{$LANG.contact_search}}" value="{{$params.keyword}}" style="width:183px" /><a href="javascript:void(0)" id="dosearch" class="icon icon_search_2"></a></div>
    </div>
    <div style="width:570px;float:right;text-align:right;margin-right:10px">
    <div class="search_box">
    </div>
    </div>
    {{if $params.type == 'contact'}}
    <strong class="title">{{if $group.groupname}}{{$group.groupname}}{{$groupname}}<input type="hidden" name="groupid" value="{{$params.groupid}}" />{{else}}{{$LANG.personal_addrbook}}{{/if}}</strong>({{$LANG.total}} <strong>{{$pageinfo.recordcount}}</strong> {{$LANG.contact_unit}})
    {{else}}
	<div><strong class="title">{{$LANG.addrbook}}</strong>({{$LANG.total}} <strong>{{$pageinfo.recordcount}}</strong> {{$LANG.contact_unit}})</div>
	{{/if}}
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
	                  {{if $params.groupid != '^n'}}{{if $params.groupid && !$group.issystem}}<button disabled class="btn" type="button" name="editGroup">{{$LANG.edit_group}}</button>{{/if}}{{/if}}
	                  <select name="group" tabindex="0">
                        <option value="">{{$LANG.add_into_group}}</option>
                        {{foreach item=item from=$groups}}
				        {{if !$item.issystem}}
				        <option value="{{$item.groupid}}">{{$item.groupname}}</option>
				        {{/if}}
                        {{/foreach}}
                      </select>
	                  <span class="tb-sep"></span>
	                  <button disabled class="btn" type="button" name="tudu">{{$LANG.send_tudu}}</button>
	                  {{if $params.type == 'contact'}}<button disabled="disabled" class="btn" type="button" name="delete">{{$LANG.delete}}</button>{{/if}}
                      {{if $params.groupid != '^n'}}{{if $params.groupid && !$group.issystem}}<button disabled class="btn" type="button" name="delGroup">{{$LANG.delte_group}}</button>{{/if}}{{/if}}
	                  {{if 0}}<button disabled class="btn" type="button">发起聊天</button><button disabled class="btn" type="button">写信</button>{{/if}}
                  </div>
                  {{pagenavigator url=$pageinfo.url query=$pageinfo.query currpage=$pageinfo.currpage recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl"}}
              </div>
              <table cellspacing="0" cellpadding="0" class="grid grid_list list_over">
                <thead>
                  <tr>
                    <td class="first" width="30"><input name="checkall" type="checkbox" value=""></td>
                    <td width="{{if $params.type != 'contact'}}100{{else}}350{{/if}}">{{$LANG.true_name}}</td>
                    {{if $params.type != 'contact'}}
                    <td width="100">{{$LANG.department}}</td>
                    <td width="120">{{$LANG.user_position}}</td>
                    <td width="120">{{$LANG.tel_num}}</td>
                    {{/if}}
                    <td width="120">{{$LANG.mobile}}</td>
                    <td style="min-width:50px;">{{if $params.type == 'contact'}}{{$LANG.email_address}}{{else}}{{$LANG.tudu_account}}{{/if}}</td>
                  </tr>
                </thead>
              </table>
              </div>
              <div class="panel-body contact-list">
                  <table cellspacing="0" cellpadding="0" class="grid grid_list list_over" style="white-space:nowrap;table-layout: fixed;\9">
                    <tbody id="contact-list">
                    {{if $params.type == 'contact'}}
                    {{foreach item=item from=$contacts}}
                    <tr id="c-{{$item.contactid}}" _ctid="{{$item.contactid}}" _groups="{{foreach item=groupid from=$item.groups}}{{if strpos($groupid, '^') === false}}{{$groupid}}|{{/if}}{{/foreach}}">

                      <td width="30" class="first" align="center"><input name="addr[]" type="checkbox" value="{{$item.contactid}}" _identify="{{$item.email|cat:' '|cat:$item.truename|escape:'url'}}" /></td>
                      <td width="350"><div><span class="icon icon_contact" title="{{$LANG.personal_addrbook}}"></span>{{$item.truename}}</div><div class="label_div"></div></td>
                      <td width="120" align="center">{{$item.mobile|default:'-'}}</td>

                      <td align="center">{{$item.email|default:'-'}}</td>
                    </tr>
                    {{foreachelse}}
                    <tr>
                      <td colspan="4" style="text-align:center;padding:35px 0">{{$LANG.empty_contact_info}}</td>
                    </tr>
                    {{/foreach}}
                    {{else}}
                    {{foreach item=item from=$users}}
                    <tr id="u-{{$item.userid}}" _uid="{{$item.userid}}" _email="{{$item.userid}}@{{$item.orgid}}">
                      <td width="30" class="first" align="center"><input name="addr[]" type="checkbox" value="{{$item.userid}}@{{$item.orgid}}" _identify="{{$item.userid|cat:'@'|cat:$item.orgid|cat:' '|cat:$item.truename|escape:'url'}}" /></td>
                      <td width="100"><span class="icon icon_sys_contact" title="{{$LANG.addrbook}}"></span>{{$item.truename}}</td>
                      <td width="100">{{$item.deptname|default:'-'}}</td>
                      <td width="120">{{$item.position|default:'-'}}</td>
                      <td width="120">{{$item.tel|default:'-'}}</td>
                      <td width="120" align="center" title="{{$contact.mobile}}">{{$item.mobile|default:'-'}}</td>
                      <td align="center">{{$item.userid}}@{{$user.orgid}}</td>
                    </tr>
                    {{foreachelse}}
				    <tr>
				      <td colspan="7" style="text-align:center;padding:35px 0">{{$LANG.empty_contact_info}}</td>
				    </tr>
                    {{/foreach}}
                    {{/if}}
                    </tbody>
                  </table>
              </div>

              <div class="grid_footer"><p>&nbsp;</p></div>
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
	TOP.Label.focusLabel();
    TOP.Frame.title('{{$LANG.addrbook}}');
    TOP.Frame.hash(LH);

    var currUrl = '{{$smarty.server.REQUEST_URI|escape:'url'}}';
    var params = {
		deptid: {{if $params.deptid}}'{{$params.deptid}}'{{else}}null{{/if}},
    	type: '{{$params.type}}'
	};
    var forbid = {
	    group: {{if $params.groupid}}true{{else}}false{{/if}},
    	liststyle:{{if $pageinfo.recordcount > 0}}true{{else}}false{{/if}}
    };
    Contact.initList(currUrl, params, forbid);
});
-->
</script>

</body>
</html>
