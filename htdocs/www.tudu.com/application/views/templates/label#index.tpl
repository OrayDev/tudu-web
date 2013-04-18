<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.label_manage}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1018" type="text/javascript"></script>
<style type="text/css">
<!--
.color_block {
    display:inline-block;
    *display:inline;
    zoom: 1;
    width:12px;
    height:12px;
    padding:1px;
    border:1px solid #fff;
    cursor:pointer;
    margin:3px;
}
.color_block div{
	width:100%;
    height:100%;
}
.color_list {
    padding:8px;
    width: 113px;
    background:#fff;
    border:#9BBB59 1px solid;
}
-->
</style>
</head>
<body>
	<div class="position">
   	  <p><strong class="title">{{$LANG.label_manage}}</strong></p>
</div>
<div class="tab_panel">
	{{include file="setting^tab.tpl" tab="label"}}
	<div class="tab-panel-body">
        	<div class="settingbox">
                	<table width="96%" cellspacing="0" cellpadding="5" align="center" class="set_tag">
                      <tr>
                        <td class="file_title" colspan="5">{{$LANG.system_label}}</td>
                      </tr>
                      <tr class="addrtitle">
                        <td class="settingtd" width="100">{{$LANG.label_name}}&nbsp;</td>
                        <td class="settingtd" width="90" align="center">&nbsp;&nbsp;{{$LANG.unread_tudu}}&nbsp;</td>
                        <td class="settingtd" width="120" align="center">{{$LANG.total_tudu}}&nbsp;</td>
                        <td class="settingtd" width="200">{{$LANG.operation_label_ways}}&nbsp;</td>
                        <td class="settingtd" align="right">&nbsp;{{$LANG.operation}}&nbsp;</td>
                      </tr>
                      <tbody id="sys-label-list">
                      {{foreach item=label from=$labels.system}}
                      {{if $label.labelalias != 'reviewed'}}
                      <tr id="label-{{$label.labelalias}}">
                        <td class="settingtd"><a href="/tudu?search={{$label.labelalias}}">{{$label.displayname}}</a></td>
                        <td class="settingtd" align="center">{{if $label.labelalias == 'drafts'}}-{{else}}{{if $label.unreadnum > 0}}<a href="/tudu?search={{$label.labelalias}}&unread=1"><strong>{{$label.unreadnum}}</strong></a>{{else}}0{{/if}}{{/if}}</td>
                        <td class="settingtd" align="center">{{$label.totalnum}}</td>
                        <td class="settingtd">
                           {{if $label.labelalias != 'all' && $label.labelalias != 'inbox'}}
                           <a name="show"{{if $label.isshow == 1}} class="label_selected"{{/if}} href="javascript:void(0)" onclick="showLabel(this, '{{$label.labelid}}', 1)">{{$LANG.left_middle_parenthes}}{{$LANG.show}}{{$LANG.right_middle_parenthes}}</a>&nbsp;
                           <a name="hide"{{if $label.isshow == 0}} class="label_selected"{{/if}} href="javascript:void(0)" onclick="showLabel(this, '{{$label.labelid}}', 0)">{{$LANG.left_middle_parenthes}}{{$LANG.hide}}{{$LANG.right_middle_parenthes}}</a>&nbsp;
                           {{if $label.labelalias != 'notice' && $label.labelalias != 'discuss' && $label.labelalias != 'forwarded' && $label.labelalias != 'sent' && $label.labelalias != 'done' && $label.labelalias != 'meeting' && $label.labelalias != 'ignore'}}
                           <a name="sys-operate"{{if $label.isshow == 2}} class="label_selected"{{/if}} href="javascript:void(0)" onclick="showLabel(this, '{{$label.labelid}}', 2)">{{$LANG.left_middle_parenthes}}{{$LANG.show_has_content}}{{$LANG.right_middle_parenthes}}</a>
                           {{/if}}
                           {{else}}
                           &nbsp;
                           {{/if}}
                        </td>
                        <td class="settingtd" align="right">
                           {{if $label.labelalias != 'all'}}
                           <a href="javascript:void(0)" name="sys-up" onclick="sortLabel(this, '{{$label.labelalias}}', 'up', 1)">↑</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="sys-down" onclick="sortLabel(this, '{{$label.labelalias}}', 'down', 1)">↓</a>
                           {{else}}
                           &nbsp;
                           {{/if}}
                        </td>
                      </tr>
                      {{/if}}
                      {{/foreach}}
                      </tbody>
                      <tr>
                        <td colspan="5"></td>
                      </tr>
                      <tr>
                        <td class="file_title" colspan="5"><form id="theform" action="/label/create" method="post"><div class="fl" style="margin-top:5px;">{{$LANG.user_label}}</div><div class="fr"><input class="input_text" name="name" type="text" maxlength="20" />&nbsp;<input class="btn" type="submit" value="{{$LANG.create_label}}"></div></form></td>
                      </tr>
                      <tr class="addrtitle">
                        <td class="settingtd" width="180">{{$LANG.label_name}}&nbsp;</td>
                        <td class="settingtd" width="90" align="center">&nbsp;&nbsp;{{$LANG.unread_tudu}}&nbsp;</td>
                        <td class="settingtd" width="120" align="center">{{$LANG.total_tudu}}&nbsp;</td>
                        <td class="settingtd" width="200">{{$LANG.operation_label_ways}}&nbsp;</td>
                        <td class="settingtd" align="right">&nbsp;{{$LANG.operation}}&nbsp;</td>
                      </tr>
                      <tbody id="user-label-list">
                      {{foreach item=label from=$labels.user}}
                      <tr id="label-{{$label.labelid}}">
                        <td class="settingtd"><a href="javascript:void(0)" class="tag_icon" style="background-color:{{$label.bgcolor}}"></a><div class="tag_name"><a href="/tudu/?search=cat&cat={{$label.labelalias|escape:'url'}}">{{$label.labelalias}}</a></div></td>
                        <td class="settingtd" align="center">{{$label.unreadnum}}</td>
                        <td class="settingtd" align="center">{{$label.totalnum}}</td>
                        <td class="settingtd">
                           <a name="show"{{if $label.isshow == 1}} class="label_selected"{{/if}} href="javascript:void(0)" onclick="showLabel(this, '{{$label.labelid}}', 1)">{{$LANG.left_middle_parenthes}}{{$LANG.show}}{{$LANG.right_middle_parenthes}}</a>&nbsp;
                           <a name="hide"{{if $label.isshow == 0}} class="label_selected"{{/if}} href="javascript:void(0)" onclick="showLabel(this, '{{$label.labelid}}', 0)">{{$LANG.left_middle_parenthes}}{{$LANG.hide}}{{$LANG.right_middle_parenthes}}</a>&nbsp;
                           <a name="sys-operate"{{if $label.isshow == 2}} class="label_selected"{{/if}} href="javascript:void(0)" onclick="showLabel(this, '{{$label.labelid}}', 2)">{{$LANG.left_middle_parenthes}}{{$LANG.show_has_content}}{{$LANG.right_middle_parenthes}}</a>
                        </td>
                        <td class="settingtd" align="right">{{if 0}}<a href="javascript:void(0)" class="b" name="show">[{{$LANG.show}}]</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="hide">[{{$LANG.hide}}]</a>&nbsp;&nbsp;{{/if}}<a href="javascript:void(0)" name="rename" onclick="renameLabel('{{$label.labelid}}', '{{$label.labelalias}}')">[{{$LANG.rename}}]</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="delete" onclick="deleteLabel('{{$label.labelid}}')">[{{$LANG.delete}}]</a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="sortLabel(this, '{{$label.labelid}}', 'up', 0)" name="up">↑</a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="sortLabel(this, '{{$label.labelid}}', 'down', 0)" name="down">↓</a></td>
                      </tr>
                      {{/foreach}}
                      </tbody>
                      <tr id="label-tpl" style="display:none">
                        <td class="settingtd"><a href="javascript:void(0)" onclick="selectColor('{{$label.labelid}}')" class="tag_icon"></a><div class="tag_name"><a href="javascript:void(0)"></a></div></td>
                        <td class="settingtd" align="center">0</td>
                        <td class="settingtd" align="center">0</td>
                        <td class="settingtd">
                           <a href="javascript:void(0)" name="show" class="gray">{{$LANG.left_middle_parenthes}}{{$LANG.show}}{{$LANG.right_middle_parenthes}}</a>&nbsp;
                           <a href="javascript:void(0)" name="hide">{{$LANG.left_middle_parenthes}}{{$LANG.hide}}{{$LANG.right_middle_parenthes}}</a>&nbsp;
                           <a href="javascript:void(0)" name="sys-operate">{{$LANG.left_middle_parenthes}}{{$LANG.show_has_content}}{{$LANG.right_middle_parenthes}}</a>
						</td>
                        <td class="settingtd" align="right">{{if 0}}<a href="javascript:void(0)" class="b" name="show">[{{$LANG.show}}]</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="hide">[{{$LANG.hide}}]</a>&nbsp;&nbsp;{{/if}}<a href="javascript:void(0)" name="rename">[{{$LANG.rename}}]</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="delete">[{{$LANG.delete}}]</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="up">↑</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="down">↓</a></td>
                      </tr>
                    </table>
        	</div>
	</div>
	<div class="toolbar">
    	<div style="height:24px;"></div>
	</div>
</div>


<div class="pop_wrap" id="rename-win-src" style="display:none;position:absolute;background:#ebf4d8;">
<form id="renameform" action="/label/update" method="post" enctype="multipart/form-data">
    <div class="pop">

        <input type="hidden" name="labelid" value="" />
        <div class="pop_header"><strong>{{$LANG.rename}}</strong><a class="icon icon_close close"></a></div>
        <div class="pop_body">
            <p><strong>{{$LANG.input_new_name}}</strong></p>
            <p><input type="text" class="input_text" name="name" value="" style="width:450px;" maxlength="20" /></p>
        </div>

    <div class="pop_footer"><button type="submit" class="btn">{{$LANG.confirm}}</button><button type="button" class="btn close">{{$LANG.cancel}}</button></div>
    </div>
</form>
</div>

<div class="pop_wrap" id="color_panel" style="width:131px;position:absolute;display:none">
<div class="color_list">
{{foreach item=color from=$bgcolors}}<div class="color_block"><div style="background-color:{{$color}}"></div><input type="hidden" name="color" value="{{$color}}" /></div>{{/foreach}}
</div>
</div>

<script type="text/javascript">
<!--
$(function(){
	{{if $reload}}
	TOP.Label.setLabels({{format_label labels=$reloadlabels}}).refreshMenu();
	{{/if}}
	TOP.Label.focusLabel();
	TOP.Frame.title('{{$LANG.label_manage}}');
	TOP.Frame.hash('m=label');

	remarkSort();
	remarkSysSort();

	$('#sys-label-list tr, #user-label-list tr').mousemove(function(){
		$(this).addClass("current");
    }).mouseout(function(){
        $(this).removeClass("current");
    });

    $('#color_panel .color_block')
    .mouseover(function(){this.style.borderColor = '#9bbb59'})
    .mouseout(function(){this.style.borderColor = '#fff'});

    $('.tag_icon').click(function(e){
        selectColor($(this).parents('tr').attr('id').replace('label-', ''));
        TOP.stopEventBuddle(e);
    });

    $(document.body).bind('click', function(){$('#color_panel').hide();});
    $('#color_panel').bind('click', function(e){
        TOP.stopEventBuddle(e);
    });

	$('#theform').submit(function(){return false;});
	$('#theform').submit(function(){
		var form = $(this);
		var name = form.find('input[name="name"]').val();

		var data = form.serializeArray();

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: form.attr('action'),
			data: data,
			success: function(ret) {
			    TOP.showMessage(ret.message, 5000, 'success');

			    if (ret.success) {
			    	var href = '/tudu/?search=cat&cat=' + encodeURIComponent(ret.data.name);
				    var labelid = ret.data.labelid;
			        var item = $('#label-tpl').clone();
			        item.attr('id', 'label-' + ret.data.labelid);

			        item.find('.tag_name a').attr('href', href).text(ret.data.name);
			        item.find('a[name="delete"]').bind('click', function(){deleteLabel(labelid)});
			        item.find('a[name="rename"]').bind('click', function(){renameLabel(labelid, ret.data.name)});
			        item.find('a[name="show"]').bind('click', function(){showLabel(this, labelid, 1)});
			        item.find('a[name="hide"]').bind('click', function(){showLabel(this, labelid, 0)});
			        item.find('a[name="sys-operate"]').bind('click', function(){showLabel(this, labelid, 2)});
			        item.find('a[name="up"]').bind('click', function(){sortLabel(labelid, 'up', 0)});
			        item.find('a[name="down"]').bind('click', function(){sortLabel(labelid, 'down', 0)});
			        item.find('.tag_icon')
			        .css('background-color', ret.data.bgcolor)
			        .click(function(){selectColor(labelid);});
			        $('#user-label-list').prepend(item);
			        item.show();

			       if (ret.data.labels) {
			    	   reloadFrameLabel(ret.data.labels);
				   }

			       remarkSort();
			    }
			},
			error: function(res) {

			}
		});
	});
});

function reloadFrameLabel(labels) {
	var _LABEL = [],
		name = null,
		order = null;
    for (var i in labels) {
    	if (i != 'all' && i != 'reviewed') {
        	if (labels[i].issystem) {
    	    	name = TOP._SYS_LABEL_NAME[labels[i].labelalias];
    	    } else {
    		    name = labels[i].labelalias;
    		}
    		_LABEL.push({
    			labelname: name, labelalias: labels[i].labelalias, labelid: labels[i].labelid, totalnum: labels[i].totalnum,
    			unreadnum: labels[i].unreadnum, ordernum: labels[i].ordernum, isshow: labels[i].isshow, issystem: labels[i].issystem, bgcolor: labels[i].bgcolor
        	});
    	}
    }
    TOP.Label.setLabels(_LABEL).refreshMenu();
}

function showLabel(obj, labelid, type) {
	if ($(obj).hasClass('label_selected')) {
		return ;
	}

	$.ajax({
		type: 'POST',
		data: {labelid: labelid, type: type},
		dataType: 'json',
		url: '/label/show.label',
		success: function(ret) {
		    TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);

		    if (ret.success) {
		    	remarkShow(obj, labelid, type);
		    	if (ret.data) {
		    		reloadFrameLabel(ret.data);
		    	}
		    }
		},
		error: function(res) {
		}
	});
}

function remarkShow(obj, labelid, type) {
	// 当前页面
	if (labelid == '^e' || labelid == '^v') {// 我审批和已审批效果一致
		$('#label-reviewed').find('a.label_selected').removeClass('label_selected');
		$('#label-review').find('a.label_selected').removeClass('label_selected');
		if (type == 1) {
			$('#label-reviewed').find('a[name="show"]').addClass('label_selected');
			$('#label-review').find('a[name="show"]').addClass('label_selected');
		} else if (type == 0) {
			$('#label-reviewed').find('a[name="hide"]').addClass('label_selected');
			$('#label-review').find('a[name="hide"]').addClass('label_selected');
		} else {
			$('#label-reviewed').find('a[name="sys-operate"]').addClass('label_selected');
			$('#label-review').find('a[name="sys-operate"]').addClass('label_selected');
		}
	} else {
		var td = $(obj).parent();
		td.find('a.label_selected').removeClass('label_selected');
		$(obj).addClass('label_selected');
	}
	// 外层框架

}

function deleteLabel(labelid) {
	if (!confirm(TOP.TEXT.CONFIRM_DELETE_LABEL)) {
	    return ;
	}

	$.ajax({
		type: 'POST',
		data: {labelid: labelid},
		dataType: 'json',
		url: '/label/delete',
		success: function(ret) {
		    TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);

		    if (ret.success) {
		        $('#label-' + labelid).remove();
		        if (ret.data) {
		        	reloadFrameLabel(ret.data);
			   }
		       remarkSort();
		    }
		},
		error: function(res) {
		}
	});
}

function renameLabel(labelid, oldName) {
	var oldname = $('#label-' + labelid + ' .tag_name a').text();
    if (!TOP.labelRenameWin) {

    	var win = $('#rename-win-src');

    	TOP.labelRenameWin = TOP.appendWindow('rename-win', win.html(), {
            onShow: function(){},
            onClose: function(){
                TOP.labelRenameWin = TOP.labelRenameWin.destroy();
            }
        });

        var scope = TOP.document.body;
        var renameform = $('#renameform', scope);
        renameform.find('input[name="labelid"]').val(labelid);
        renameform.find('input[name="name"]').val(oldname);

        renameform.submit(function(){return false;});
        renameform.submit(function(){
            var name = renameform.find('input[name="name"]').val();
            if (!name) {
                TOP.labelRenameWin.close();
                TOP.showMessage(TOP.TEXT.MISSING_LABEL_NAME);
                return ;
            }
			if (name == oldName) {
				TOP.labelRenameWin.close();
				return;
			}
            var data = renameform.serializeArray();
            var labelName = name;
            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: data,
                url: renameform.attr('action'),
                success: function(ret){
                    TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);

                    if (ret.success) {
                        var href = '/tudu/?search=cat&cat=' + encodeURIComponent(labelName);

                        $('#label-' + labelid + ' .tag_name a')
                        .attr('href', href)
                        .text(labelName);
                        TOP.labelRenameWin.close();

                        if (ret.data) {
                        	reloadFrameLabel(ret.data);
        			    }
                    }
                },
                error: function(res) {
                    TOP.labelRenameWin.close();
                }
            });
        });
    }

    TOP.labelRenameWin.show();
}

function selectColor(labelid) {
    var block = $('#label-' + labelid + ' .tag_icon'),
        panel = $('#color_panel');
    var offset = block.offset();

    if ($('#color_panel:visible').size()) {
        panel.hide();
    }

    panel.css({top: offset.top - panel.height() - 2 + 'px', left: offset.left + 'px'})
    .show(300);

    panel.find('div.color_block')
    .unbind('click')
    .bind('click', function(){
        var color = $(this).find('input[name="color"]').val();

        block.css('background-color', color);
        TOP.getJQ()('#f_' + labelid + '_td .tag_icon').css('background-color', color);

        panel.hide(300);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '/label/update',
            data: {labelid: labelid, bgcolor: color},
            success: function(ret) {
            	if (ret.success && ret.data) {
            		reloadFrameLabel(ret.data);
			    }
            },
            error: function(res){
            }
        });
    });
}

function sortLabel(obj, labelid, type, issystem) {
	if ($(obj).hasClass('gray')) {
		return ;
	}

	$.ajax({
		type: 'POST',
		dataType: 'json',
		data: {label: labelid, type: type, issystem: issystem},
		url: '/label/sort',
		success: function(ret) {
			if (ret.success && ret.data) {
				var item = $('#label-' + labelid);
				if (type == 'up') {
					item.insertBefore(item.prev());
				} else if (type == 'down') {
					item.insertAfter(item.next());
				}
				if (issystem) {
			    	remarkSysSort();
			    } else {
			    	remarkSort();
			    }
				reloadFrameLabel(ret.data);
			}
		},
		error: function(res) {}
	});
}

function remarkSort() {
	$('#user-label-list tr a.gray').removeClass('gray');
    $('#user-label-list tr:first-child a[name="up"]').addClass('gray');
    $('#user-label-list tr:last-child a[name="down"]').addClass('gray');
}

function remarkSysSort() {
	$('#sys-label-list tr a.gray').removeClass('gray');
    $('#sys-label-list tr:eq(1) a[name="sys-up"]').addClass('gray');
    $('#sys-label-list tr:last-child a[name="sys-down"]').addClass('gray');
}
-->
</script>
</body>
</html>
