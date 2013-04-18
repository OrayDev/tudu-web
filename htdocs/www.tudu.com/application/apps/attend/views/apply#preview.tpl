<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$tudu.subject}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<style type="text/css" media="print">
.toolbar {
	display:none
}
</style>
</head>
<body style="padding:5px">
<div class="panel">
    <div class="panel-body">
        <div class="toolbar">
            <div><button type="button" name="print">{{$LANG.print}}</button><button type="button" name="close">{{$LANG.close}}</button></div>
        </div>
        <div class="content_box2 todo_wrap">
            <div class="todo_title_wrap">
                <div class="todo_title">
                [考勤]{{$apply.subject|escape:'html'|default:$LANG.missing_subject}}
                {{if $apply.categoryid == '^checkin'}}
                {{if $apply.checkintype == 0}}签到{{elseif $apply.checkintype == 1}}签退{{/if}}
                {{/if}}
                </div>
                <div class="tag_wrap"></div>
                <div class="clear"></div>
            </div>
            <div class="todo_content">
                <table border="0" cellspacing="3" cellpadding="0">
                  <colgroup>
                    <col>
                    <col width="170">
                    <col>
                    <col>
                  </colgroup>
                  <tr>
                    <td align="right"><span>{{$LANG.apply_user}}{{$LANG.cln}}</span></td>
                    <td class="black" title="{{foreach from=$apply.to item=to name="to"}}{{$to.0}}{{if $to.3}}<{{$to.3}}>{{/if}}{{if $smarty.foreach.to.index+1 < count($apply.to)}},&#13;{{/if}}{{/foreach}}" nowrap="nowrap">{{foreach from=$apply.to item=to name="to"}}{{$to.0}}{{if $smarty.foreach.to.index+1 < count($apply.to)}},&#13;{{/if}}{{/foreach}}</td>
                    <td align="right"><span>{{$LANG.title_cc}}</span></td>
                    <td class="black"{{if $apply.cc}} title="{{foreach item=cc from=$apply.cc name=cc}}{{if !$smarty.foreach.cc.first}},&#13;{{/if}}{{$cc.0}}{{if $cc.3}}<{{if strpos($cc.3, '@')}}{{$cc.3}}{{else}}{{$LANG.group}}{{/if}}>{{/if}}{{/foreach}}"{{/if}} nowrap="nowrap">{{foreach item=cc from=$apply.cc name=cc}}{{if $smarty.foreach.cc.index < 6}}{{if $cc.3 == $user.username}}{{$LANG.me}}{{else}}{{$cc.0}}{{/if}}{{if $smarty.foreach.cc.index + 1 < count($apply.cc)}},{{/if}}{{/if}}{{foreachelse}}-{{/foreach}}{{if $apply.cc && count($apply.cc) > 6}}...{{/if}}</td>
                  </tr>
                  <tr>
                    <td align="right"><span>{{$LANG.apply_type}}{{$LANG.cln}}</span></td>
                    <td class="black">{{$apply.categoryname}}</td>
                    <td align="right"><span>{{$LANG.apply_time}}{{$LANG.cln}}</span></td>
                    <td class="black">
                    {{strip}}
                    {{if $apply.categoryid == '^checkin'}}
                    {{if $apply.checkintype == 0}}
                    {{$apply.starttime|date_format:'%Y-%m-%d %H:%M'|default:'-'}}
                    {{elseif $apply.checkintype == 1}}
                    {{$apply.endtime|date_format:'%Y-%m-%d %H:%M'|default:'-'}}
                    {{/if}}
                    {{else}}
                    {{if $apply.isallday == 0}}
                    {{$apply.starttime|date_format:'%Y-%m-%d %H:%M'|default:'-'}} {{$LANG.date_to}} {{$apply.endtime|date_format:'%Y-%m-%d %H:%M'|default:'-'}}
                    {{elseif $apply.isallday == 1}}
                    {{$apply.starttime|date_format:'%Y-%m-%d'|default:'-'}} {{$LANG.date_to}} {{$apply.endtime|date_format:'%Y-%m-%d'|default:'-'}}
                    {{/if}}
                    {{if $apply.period}}&nbsp;共{{$apply.period}}小时{{/if}}
                    {{/if}}
                    {{/strip}}
                    </td>
                  </tr>
                </table>
            </div>
        </div>
        <div class="todo_expand" id="post-{{$post.postid}}">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="todo_body">
              <tr>
                <td>
                	<div class="todo_bd_tl"><div class="todo_bd_tr"><div class="todo_bd_tc"></div></div></div>
                </td>
              </tr>
              <tr>
                <td><div class="todo_body_con">
                    <div class="todo_ps_profile">
    				{{if $user.option.profilemode == 0}}
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td valign="top" width="50"><img class="todo_send_icon" src="/logo?unid={{$user.uniqueid}}"></td>
    
                        <td><p><a href="javascript:void(0)"><strong class="poster">{{$apply.from.0}}</strong></a> {{$apply.createtime|date_time_format:$user.option.dateformat}} </p><p class="gray">{{$post.posterinfo}}</p></td>
                        <td align="right"></td>
                      </tr>
                    </table>
                    {{else}}
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td><p><a href="javascript:void(0)"><strong class="poster">{{$apply.from.0}}</strong></a> {{$apply.createtime|date_time_format:$user.option.dateformat}}</p></td>
                        <td align="right"></td>
                      </tr>
                    </table>
                    {{/if}}
    				<a href="javascript:void(0)" class="toggle_content icon_fold" onclick="togglePost('{{$post.postid}}', this)" title=""></a>
                    </div>
                    <div class="tudu-content-body">
                    <div class="line_gray"></div>
                    <div class="todo_info post-content">
                        {{$apply.content|tudu_format_content}}
                    </div>
                    {{if $apply.attachnum}}
                    <div class="line_gray"></div>
                    <div class="todo_el">
                        <span><strong>{{$LANG.attachment}}</strong>({{$apply.attachnum}}{{$LANG.attach_unit}})</span>
                        {{foreach item=file from=$apply.attachments}}
                        <p><span class="icon ficon {{$file.filename|file_ext}}"></span><span>{{$file.filename}}</span><span class="gray">({{$file.size|format_filesize}})</span></p>
                        {{/foreach}}
                        {{foreach item=file from=$apply.ndattach}}
                        <p><span class="icon ficon {{$file.filename|file_ext}}"></span><span>{{$file.filename}}</span><span class="gray">({{$file.size|format_filesize}})</span></p>
                        {{/foreach}}
                    </div>
                    {{/if}}
                    </div>
                </div></td>
              </tr>
              <tr>
                <td>
                <div style="position:relative">
                	<div class="toolbar_2_wrap" id="bar-post-{{$post.postid}}">
                      <div class="toolbar_2">
                      <table width="100%" border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td valign="bottom"></td>
                            <td valign="bottom" align="right"><a href="#" class="top">top</a></td>
                          </tr>
                        </table>
                      </div>
                        <div class="todo_bd_bl"><div class="todo_bd_br"><div class="todo_bd_bc"></div></div></div>
                    </div>
                    <div class="todo_bd_wrap">
                    	<div class="toolbar_2"></div>
                        <div class="todo_bd_bl"><div class="todo_bd_br"><div class="todo_bd_bc"></div></div></div>
                    </div>
                </div>
                </td>
              </tr>
            </table>
        </div>
        <div class="toolbar">
            <div><button type="button" name="print">{{$LANG.print}}</button><button type="button" name="close">{{$LANG.close}}</button></div>
        </div>
    </div>
</div>
<script type="text/javascript">
<!--
function togglePost(postId, icon) {
    var icon = $(icon);

    var expanded = !icon.hasClass('icon_unfold');

    if (expanded) {
        $('#post-' + postId + ' div.tudu-content-body').hide();
        icon.addClass('icon_unfold');
    } else {
        $('#post-' + postId + ' div.tudu-content-body').show();
        icon.removeClass('icon_unfold');
    }
}

function doPrint() {
    try {
        window.print();
    } catch (e) {
        alert('{{$LANG.print_err}}');
    }
}

$('button[name="close"]').bind('click', function(){window.close();});
$('button[name="print"]').bind('click', function(){doPrint();});
-->
</script>
</body>
</html>