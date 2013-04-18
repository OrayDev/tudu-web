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
                  <table cellspacing="0" cellpadding="0" class="grid grid_list list_over">
                    <tbody id="contact-list">
                    {{if $params.type == 'contact'}}
                    {{foreach item=item from=$contacts}}
                    <tr id="c-{{$item.contactid}}" _ctid="{{$item.contactid}}" _groups="{{foreach item=groupid from=$item.groups}}{{if strpos($groupid, '^') === false}}{{$groupid}}|{{/if}}{{/foreach}}">
                      <td width="30" class="first" align="center"><input name="addr[]" type="checkbox" value="{{$item.contactid}}" _identify="{{$item.email|cat:' '|cat:$item.truename|escape:'url'}}" /></td>
                      <td width="350"><div>{{$item.truename}}</div><div class="label_div"></div></td>
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
                      <td width="100">{{$item.truename}}</td>
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
<script type="text/javascript">
<!--
var _CUS_GROUPS = {};
{{foreach item=item from=$groups name=group}}{{if !$item.issystem}}_CUS_GROUPS['{{$item.groupid}}'] = {name: '{{$item.groupname}}', bgcolor: '{{$item.bgcolor}}'};{{/if}}{{/foreach}}

$(function(){

	TOP.Label.focusLabel();
    TOP.Frame.title('{{$LANG.addrbook}}');
    TOP.Frame.hash(LH);

	var _DEPT_TREE = new $.tree({
        id: 'dept-tree',
        idKey: 'id',
        idPrefix: 'dept-',
        cls: 'contact-dept-tree',
        template: '<a href="javascript:void(0);" title="{name}">{name}</a>'
	});
	_DEPT_TREE.appendTo('#dept-tree-ct');

    // 添加到组
    $('select[name="group"]').change(function(){
    	if(this.value){
	    	var groupId=this.value;
	    	addMember(groupId);
    	}
    });

    $(document).ready(function() {
		$('#keyword').bind('keyup', function(event) {
            if (event.keyCode == "13") {
            	var keyword = $('#keyword').val();
            	search(keyword);
            }
		});
	});

    $('#dosearch').click(function(){
        var keyword = $('#keyword').val();
        search(keyword);
    });

    {{if $params.type == 'contact'}}
    // 删除联系人
    $('button[name="delete"]').click(function(){
        if (!$(':checkbox[name="addr[]"]:checked').size()) return ;
        var ctid = [];
        $(':checkbox[name="addr[]"]:checked').each(function(){
            ctid.push(this.value)
        });
        deleteContact(ctid.join(','), '/contact/?type=contact');
    });
    {{/if}}

    {{if $params.groupid}}
    $('button[name="delGroup"]').attr('disabled', false);
    $('button[name="editGroup"]').attr('disabled', false);

    var groupId = $('input[name="groupid"]').val();

    $('button[name="editGroup"]').click(function(){
        location = '/contact/group.modify?gid=' + groupId + '&back={{$smarty.server.REQUEST_URI|escape:'url'}}';
    });
    $('button[name="delGroup"]').click(function(){
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {gid: groupId},
            url: '/contact/group.delete',
            success: function(ret) {
               TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
               if (ret.success) {
                    location = '/contact/?type=contact';
               }
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
            }
        });
    });
{{/if}}

    $('input[name="checkall"]').click(function(){
    	TOP.checkBoxAll('addr[]', this.checked, document.body);

    	var selectCount = $(':checkbox[name="addr[]"]:checked').size();
        $('button[name="tudu"]').attr('disabled', selectCount <= 0);
        $('button[name="delete"]').attr('disabled', selectCount <= 0);
    });
    $(':checkbox[name="addr[]"]').click(function(e){
    	var selectCount = $(':checkbox[name="addr[]"]:checked').size();
        $('button[name="tudu"]').attr('disabled', selectCount <= 0);
        $('button[name="delete"]').attr('disabled', selectCount <= 0);
        TOP.stopEventBuddle(e);
    });

    $('button[name="tudu"]').click(function(){
        if (!$(':checkbox[name="addr[]"]:checked').size()) return ;
        var to = [];
        $(':checkbox[name="addr[]"]:checked').each(function(){
            to.push($(this).attr('_identify'));
        });
        location = '/tudu/modify/?to=' + to.join(encodeURIComponent("\n"));
    });
    TOP.keyhint('#keyword', 'input_tips', true, document.body);

    TOP.Cast.load(function(cast) {
        var depts  = TOP.Cast.get('depts'),
            parent = null,
            deptid = null;

        for (var i = 0, c = depts.length; i < c; i++) {
            var deptid = depts[i].deptid.replace('^', '_');
            if (depts[i].deptid == '^root') {
            	depts[i].deptname = TOP._ORGNAME;
            }
            var node = new $.treenode({
	            data: {
	                id: deptid,
	                name: depts[i].deptname
	            },
	            events: {
                    mouseover: function(e){$(this).find('.tree-node-el:eq(0)').addClass('tree-node-over');TOP.stopEventBuddle(e)},
                    mouseout: function(e){$(this).find('.tree-node-el:eq(0)').removeClass('tree-node-over');TOP.stopEventBuddle(e)},
                    click: function(e){
                        var key = this.id.replace('dept-', '');
                        if (key.indexOf('_') != -1) {
                        	key = key.replace('_', '^');
                        }
                        location = '/contact/?deptid=' + key;
                        TOP.stopEventBuddle(e);
                    }
	            }
	        });

            if (depts[i].parentid) {
                if (depts[i].parentid.indexOf('^') != -1) {
                	depts[i].parentid = depts[i].parentid.replace('^', '_');
                }
                parent = _DEPT_TREE.find(depts[i].parentid, true);

                if (parent) {
                    parent.appendChild(node);
                }
            } else {
                _DEPT_TREE.appendNode(node);
            }
        }
        _DEPT_TREE.find('_root', true).expand();

        {{if $params.deptid}}
        var node = _DEPT_TREE.find('{{$params.deptid|replace:"^":"_"}}', true);
        if (node) {
            if (node.parent) {
                node.parent.expand();
            }
            $('#dept-{{$params.deptid|replace:"^":"_"}} .tree-node-el:eq(0)').addClass('tree-node-selected');
        }
        {{/if}}
    });

    {{if $pageinfo.recordcount > 0}}
    $("#contact-list tr")
    .each(function(){
        var o = $(this);

        o.mousemove(function(){
            o.addClass("over");
        }).mouseout(function(){
            o.removeClass("over");
        });

        o.find('td:not(:eq(0))').click(function(){
            {{if $params.type == 'contact'}}
            location = '/contact/view?ctid=' + o.attr('_ctid') + '&back={{$smarty.server.REQUEST_URI|escape:'url'}}';
            {{else}}
            location = '/contact/view?email=' + o.attr('_email') + '&back={{$smarty.server.REQUEST_URI|escape:'url'}}';
            {{/if}}
        });

        var groups = o.attr('_groups');
        if (!groups) {
            return ;
        }
        groups = groups.split('|');
        if (groups.length) {
            for (var i = 0, c = groups.length; i < c; i++) {
                if (!groups[i] || groups[i].indexOf('^') != -1) {
                    continue;
                }
                appendGroup($(this), groups[i]);
            }
        }
    });
    {{else}}
    $(".list_over tbody tr").mousemove(function(){
		$(this).removeClass();
		$(this).css("cursor", "text");
	});
	{{/if}}

    ajustSize();

    $('button[name="create"]').click(function(){
    	location = '/contact/modify?back={{$smarty.server.REQUEST_URI|escape:'url'}}';
    });

    $(window).bind('scroll', function(){
		var scrollTop = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop;
		scrollTop = scrollTop - $('.position').height() - 10;

		if (scrollTop > 0) {
			$('.float-toolbar').css({'position': 'relative', 'top': scrollTop + 'px'});
		} else {
			$('.float-toolbar').css('top', '0px');
		}
	});

    window.onresize = onResize;

    onResize();
});

function search(keyword) {
	if (!keyword) {
        return false;
    } else {
    	location.href = '/contact/search?keyword=' + encodeURIComponent(keyword);
    }
}

function ajustSize() {
   	var height = Math.max(document.body.clientHeight - 30, $('.c_left').height());

	$('.contacts_box').css({minHeight: height - 10 + 'px'});
	if ($.browser.msie && $.browser.version < '7.0') {
		$('.contacts_box').css({height: height - 10 + 'px'});
	}
}

function getEmail() {
	var email = [];
	$(':checkbox[name="addr[]"]:checked').each(function(){
		email.push(this.value);
	});

	return email;
}

function onResize() {
   	var winWidth = $('table[class="gird_fix"] td:eq(0)').width();
	if(winWidth<=800){
		$('table[class="gird_fix"] td:eq(0)').css({width: '800px'});
		$('.position').css('width', '995px');
		$('.float-toolbar').css('width', '800px');
		$(".c_left").addClass("less_left");
		$(".c_right").addClass("less_right");
	} else{
		$('table[class="gird_fix"] td:eq(0)').css({width: '100%'});
		$('.position').css('width', '100%');
		$('.float-toolbar').css({'width': winWidth + 'px'});
		$(".c_left").removeClass("less_left");
		$(".c_right").removeClass("less_right");
	}
}

function getKey() {
    var key = [];
    $(':checkbox[name="addr[]"]:checked').each(function(){
        key.push(this.value);
    });

    return key;
}

function addMember(groupId) {
    if (!key) {
        var key = getKey();
    }

    if (!key.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {gid: groupId, key: key, type: 'add'},
        url: '/contact/group',
        success: function(ret) {
           TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
           if (ret.success) {
               TOP.Contact.clear();
       	       location = '/contact/?type=contact&groupid=' + groupId;
           }
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}

function removeMember(groupId, contactId) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {gid: groupId, ctid: contactId, type: 'remove'},
        url: '/contact/group',
        success: function(ret) {
           TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
           TOP.Contact.clear();
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}

{{if $params.type == 'contact'}}
function deleteContact(contactId) {
    if (!confirm(TOP.TEXT.CONFIRM_DELETE_CONTACT)) {
        return false;
    }

    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '/contact/delete?ctid=' + contactId,
        success: function(ret) {
           TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
           TOP.Contact.clear();
           location.reload();
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}
{{/if}}

function appendGroup(obj, groupid) {
    if (_CUS_GROUPS[groupid] == undefined) {
        return
    }
    if ($('#' + obj.attr('id') + '-group-' + groupid).size()) return ;

    var e = $('#group-tpl').clone(),
        group = _CUS_GROUPS[groupid],
        ct = obj.find('div.label_div'),
        ctid = obj.attr('_ctid'),
        close = e.find('.tag_close');
    e
    .attr({'id': 'ct-' + obj.attr('_ctid') + '-g-' + groupid})
    .css({'background-color': group.bgcolor, 'color': '#fff'});

    e.find('.tag_txt').text(group.name);

    close
    .click(function(evt){
        e.remove();
        removeMember(groupid, ctid, '/contact/?type=contact')
        TOP.stopEventBuddle(evt);
    })
    .html('&nbsp;')
    .hide();

    e
    .mouseover(function(evt){
        if (!e.timer) {
            e.timer = setTimeout(function(){
                e.find('.tag_close').show();
                clearTimeout(e.timer);
            }, 500);
        }
    })
    .mouseout(function(evt){
        evt = window.event || evt;
        var offset = e.offset();
        var isOver = evt.clientX > offset.left && evt.clientX < offset.left + e.width()
                      && evt.clientY > offset.top && evt.clientY < offset.top + e.height();

        if (!isOver) {
            clearTimeout(e.timer);
            e.timer = null;
            e.find('.tag_close').hide();
        }
    })
    .click(function(evt){
        location = '/contact/?type=contact&groupid=' + encodeURIComponent(groupid);
        TOP.stopEventBuddle(evt);
    });

    ct.append(e);
}
-->
</script>
</body>
</html>
