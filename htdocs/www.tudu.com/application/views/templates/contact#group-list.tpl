<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.group_manage}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>

<script type="text/javascript">
var TOP = getTop();
</script>
<style type="text/css">
<!--
html, body {
	height:100%
}
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
      <p><strong class="title">{{$LANG.group_manage}}</strong></p>
</div>
    {{include file="setting^tab.tpl" tab="group"}}
    <div class="tab-panel-body">
            <div class="settingbox">
                <div class="settingdiv">
                    <div style="padding:10px 0"><button class="btn" name="add" onclick="location='/contact/group.modify?back={{$smarty.server.REQUEST_URI|escape:'url'}}'">{{$LANG.create_group}}</button></div>
                    <h3 class="setting_tit">{{$LANG.group_manage}}</h3>
                    <table width="100%" cellspacing="0" cellpadding="5" align="center" class="set_tag">
                      <tr>
                        <td class="file_title" colspan="2" style="padding-top:0"></td>
                      </tr>
                      <tr class="addrtitle">
                        <td class="settingtd">{{$LANG.group_name}}&nbsp;</td>
                        <td class="settingtd" width="180" align="right">&nbsp;{{$LANG.operation}}&nbsp;</td>
                      </tr>
                      <tbody id="user-email-list">
                      {{foreach item=item from=$groups name="group"}}
                      {{if !$item.issystem}}
                      <tr id="group-{{$item.groupid}}">
                        <td class="settingtd"><a href="javascript:void(0)" class="tag_icon" style="background-color:{{$item.bgcolor}};margin-right:15px;"></a><a href="/contact/group.modify?gid={{$item.groupid}}">{{$item.groupname}}</a></td>
                        <td class="settingtd" align="right"><a href="/contact/group.modify?gid={{$item.groupid}}&back={{$smarty.server.REQUEST_URI|escape:'url'}}" name="edit">[{{$LANG.modify}}]</a>&nbsp;&nbsp;<a href="javascript:void(0)" name="delete" onclick="deleteGroup('{{$item.groupid}}');">[{{$LANG.delete}}]</a>&nbsp;&nbsp;<a href="javascript:void(0);" onclick="sortGroup('{{$item.groupid}}', 'up');"{{if $smarty.foreach.group.index == 0}} class="gray"{{/if}}>↑</a>&nbsp;&nbsp;<a href="javascript:void(0);" onclick="sortGroup('{{$item.groupid}}', 'down');"{{if $smarty.foreach.group.index == count($groups) - 1}} class="gray"{{/if}}>↓</a></td>
                      </tr>
                      {{/if}}
                      {{/foreach}}
                      </tbody>
                    </table>
                </div>
            </div>
        <div class="toolbar_position">
        <div class="toolbar">
            <div style="height:24px;"></div>
        </div>
        </div>
    </div>
    <div class="pop_wrap" id="color_panel" style="width:131px;position:absolute;display:none;z-index:10">
    <div class="color_list">
    {{foreach item=color from=$bgcolors}}<div class="color_block"><div style="background-color:{{$color}}"></div><input type="hidden" name="color" value="{{$color}}" /></div>{{/foreach}}
    </div>
    </div>
<script type="text/javascript">
<!--
function deleteGroup(groupId) {
    if (!confirm('{{$LANG.confirm_delete_group}}')) {
        return false;
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {
           'gid': groupId
        },
        url: '/contact/group.delete',
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, ret.success ? 'success': null);

            if (ret.success) {
                location.reload();
            }
        },
        error: function(res){}
    });
}

function sortGroup(groupid, type) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {
           'groupid': groupid,
           'type': type
        },
        url: '/contact/group.sort',
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, ret.success ? 'success': null);

            if (ret.success) {
                location.reload();
            }
        },
        error: function(res){}
    });
}


function selectColor(groupid) {
    var block = $('#group-' + groupid + ' .tag_icon'),
        panel = $('#color_panel');
    var offset = block.offset();

    if ($('#color_panel:visible').size()) {
        panel.hide();
    }

    panel.css({top: offset.top + block.height() + 'px', left: offset.left + 'px'})
    .show(300);

    panel.find('div.color_block')
    .unbind('click')
    .bind('click', function(){
        var color = $(this).find('input[name="color"]').val();

        block.css('background-color', color);

        panel.hide(300);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '/contact/group.save',
            data: {groupid: groupid, bgcolor: color},
            success: function(ret) {
                if (!ret.success) {
                    TOP.showMessage(ret.message, 5000);
                }
            },
            error: function(res){
            }
        });
    });
}

$(function(){
	TOP.Label.focusLabel('board');
    TOP.Frame.title('{{$LANG.group_manage}}');
    TOP.Frame.hash('#m=contact/group.list');

    $('#color_panel .color_block')
    .mouseover(function(){this.style.borderColor = '#9bbb59'})
    .mouseout(function(){this.style.borderColor = '#fff'});

    $('#user-email-list tr').mousemove(function(){
		$(this).addClass("current");
    }).mouseout(function(){
        $(this).removeClass("current");
    });

    $('.tag_icon').click(function(e){
        selectColor($(this).parents('tr').attr('id').replace('group-', ''));
    });

    $(document.body).bind('click', function(){$('#color_panel').hide();});
    $('#color_panel').bind('click', function(e){
        TOP.stopEventBuddle(e);
    });
});
-->
</script>
</body>
</html>
