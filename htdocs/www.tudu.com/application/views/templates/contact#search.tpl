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
              <div id="float-toolbar" class="float-toolbar">
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
                  <div class="page">{{$LANG.total}} <strong><span id="count"></span></strong> {{$LANG.contact_unit}}</div>
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
              <div id="toolbar" class="toolbar">
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
                  <div class="page">{{$LANG.total}} <strong><span id="count"></span></strong> {{$LANG.contact_unit}}</div>
              </div>
              <div class="panel-body contact-list">
                  <table cellspacing="0" cellpadding="0" class="grid grid_list list_over">
                      <thead>
                          <tr>
                          <td class="first" width="30"><input name="checkall" type="checkbox" value=""></td>
                          <td width="350">{{$LANG.true_name}}</td>
                          <td width="150">{{$LANG.mobile}}</td>
                          <td style="min-width:50px;">{{$LANG.email_address}}</td>
                          </tr>
                      </thead>
                    <tbody id="contact-list">
                    {{foreach item=user from=$users}}
                    <tr _uid="{{$user.userid}}" _email="{{$user.address}}">
                      <td class="first" align="center"><input name="addr[]" type="checkbox" value="{{$user.address}}" _identify="{{$user.address|cat:' '|cat:$user.truename|escape:'url'}}" system="1"/></td>
                      <td>{{$user.truename}}</td>
                      <td align="center">{{$user.mobile|default:'-'}}</td>
                      <td align="center">{{$user.userid}}@{{$user.orgid}}</td>
                    </tr>
                    {{/foreach}}
                    {{foreach item=pcontact from=$personal}}
                    <tr _ctid="{{$pcontact.contactid}}" _groups="{{foreach item=groupid from=$pcontact.groups}}{{if strpos($groupid, '^') === false}}{{$groupid}}|{{/if}}{{/foreach}}">
                      <td class="first" align="center"><input name="addr[]" type="checkbox" value="{{$pcontact.contactid}}" _identify="{{$pcontact.email|cat:' '|cat:$pcontact.truename|escape:'url'}}" /></td>
                      <td><div>{{$pcontact.truename}}</div><div class="label_div"></div></td>
                      <td align="center">{{$pcontact.mobile|default:'-'}}</td>
                      <td align="center">{{$pcontact.email|default:'-'}}</td>
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
<script type="text/javascript">
<!--
var _CUS_GROUPS = {};
{{foreach item=item from=$groups name=group}}{{if !$item.issystem}}_CUS_GROUPS['{{$item.groupid}}'] = {name: '{{$item.groupname}}', bgcolor: '{{$item.bgcolor}}'};{{/if}}{{/foreach}}

$(function(){

	TOP.Label.focusLabel('');
    TOP.Frame.title('{{$LANG.search_contact}}');
    TOP.Frame.hash('m=contact/search&keyword={{$keyword}}');

	var _DEPT_TREE = new $.tree({
        id: 'dept-tree',
        idKey: 'id',
        idPrefix: 'dept-',
        cls: 'contact-dept-tree',
        template: '<a href="/contact/?deptid={id}">{name}</a>'
	});
	_DEPT_TREE.appendTo('#dept-tree-ct');

	var length = parseInt($("#contact-list tr").length);
	{{if !$users && !$personal}}
		$('#count').text(length-1);
		$(".list_over tbody tr").mousemove(function(){
			$(this).removeClass();
			$(this).css("cursor", "text");
		});
	{{else}}
		$('#count').text(length);

		$("#contact-list tr").mousemove(function(){
	        $(this).addClass("over");
	    }).mouseout(function(){
	        $(this).removeClass("over");
	    }).each(function(){
	        var o = $(this),
	        	groups = o.attr('_groups'),
	        	email = o.attr('_email');
	        if (groups) {
	        	groups = groups.split('|');
		        for (var i = 0, c = groups.length; i < c; i++) {
		            if (!groups[i] || groups[i].indexOf('^') != -1) {
		                continue;
		            }
		            appendGroup($(this), groups[i]);
		        }
	        }
	        o.find('td:not(:eq(0))').click(function(){
		        if (email !== undefined) {
		        	location = '/contact/view?email=' + o.attr('_email') + '&back={{$smarty.server.REQUEST_URI|escape:'url'}}';
		        } else {
		        	location = '/contact/view?ctid=' + o.attr('_ctid') + '&back={{$smarty.server.REQUEST_URI|escape:'url'}}';
		        }
	        });
	    });
	{{/if}}

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
    // 发送图度
    $('button[name="tudu"]').click(function(){
        if (!$(':checkbox[name="addr[]"]:checked').size()) return ;
        var to = [];
        $(':checkbox[name="addr[]"]:checked').each(function(){
            to.push($(this).attr('_identify'));
        });
        location = '/tudu/modify/?to=' + to.join(encodeURIComponent("\n"));
    });

    // 删除联系人
    $('button[name="delete"]').click(function(){
        if (!$(':checkbox[name="addr[]"]:checked').size()) return ;
        var ctid = [];
        $(':checkbox[name="addr[]"]:checked').each(function(){
            ctid.push(this.value);
        });
        deleteContact(ctid.join(','), '/contact/?type=contact');
    });
    TOP.keyhint('#keyword', 'input_tips', true, document.body);

    TOP.Cast.load(function(cast) {
        var depts  = TOP.Cast.get('depts'),
            parent = null,
            deptid = null;

        for (var i = 0, c = depts.length; i < c; i++) {
            var deptid = depts[i].deptid;
            var node = new $.treenode({
	            data: {
	                id: deptid,
	                name: depts[i].deptname
	            },
	            events: {
                    mouseover: function(e){$(this).find('.tree-node-el:eq(0)').addClass('tree-node-over');TOP.stopEventBuddle(e)},
                    mouseout: function(e){$(this).find('.tree-node-el:eq(0)').removeClass('tree-node-over');TOP.stopEventBuddle(e)},
                    click: function(e){location = '/contact/?deptid=' + this.id.replace('dept-', '');TOP.stopEventBuddle(e);}
	            }
	        });

            if (depts[i].parentid) {
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
        var node = _DEPT_TREE.find('{{$params.deptid}}', true);
        if (node) {
            if (node.parent) {
                node.parent.expand();
            }
            $('#dept-{{$params.deptid}} .tree-node-el:eq(0)').addClass('tree-node-selected');
        }
        {{/if}}
    });

    //$(window).bind('resize', function(){ajustSize();});
    ajustSize();

    $('button[name="create"]').click(function(){
    	location = '/contact/modify';
    });

    // 添加到组
    $('select[name="group"]').change(function(){
    	if(this.value){
            addMember(this.value, '/contact/?type=contact&groupid=' + this.value);
        }
    });

    new FixToolbar({
    	src: '#toolbar',
    	target: '#float-toolbar',
    	width: $('table[class="gird_fix"] td:eq(0)').width()
    });
});

function search(keyword) {
	if (!keyword) {
        return false;
    } else {
    	location.href = '/contact/search?keyword=' + encodeURIComponent(keyword);
    }
}

function addMember(groupId, back) {
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
			   location = back;
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

function getKey() {
	var key = [];
	$(':checkbox[name="addr[]"]:checked').each(function(){
		key.push(this.value);
	});

	return key;
}

function ajustSize() {
   	var height = Math.max(document.body.clientHeight - 20, $('.c_left').height());

   	$('.contacts_box').css({minHeight: height + 'px'});
}

function onResize() {
   	var winWidth = document.body.clientWidth;
	if(winWidth<=800){
		$(".c_left").addClass("less_left");
		$(".c_right").addClass("less_right");
	} else{
		$(".c_left").removeClass("less_left");
		$(".c_right").removeClass("less_right");
	}
}
window.onresize = onResize;

onResize();

function deleteContact(contactId, back) {
	if (!confirm(TOP.TEXT.CONFIRM_DELETE_CONTACT)) {
		return false;
	}

	var ctid = new Array();
	ctid = contactId.split(',');
	for (var i = 0; i < ctid.length; i++) {
		if (ctid[i].indexOf('@') > -1) {
			TOP.showMessage(TOP.TEXT.NOT_DELETE_SYSTEM_CONTACT);
			return false;
		}
	}

	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: '/contact/delete?ctid=' + contactId,
		success: function(ret) {
		   TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
		   if (ret.success) {
			   TOP.Contact.clear();
			   location = back;
		   }
		},
		error: function(res) {
		    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
		}
	});
}

function appendGroup(obj, groupid) {
    if (_CUS_GROUPS[groupid] == undefined) return ;
    if ($('#' + obj.attr('id') + '-group-' + groupid).size()) return ;

    var e = $('#group-tpl').clone(),
    	group = _CUS_GROUPS[groupid],
        ct = obj.find('div.label_div'),
        ctid = obj.attr('_ctid'),
        close = e.find('.tag_close');
    e
    .attr({'id': 'contact-' + obj.attr('_ctid') + '-group-' + groupid, 'title': group.name})
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
