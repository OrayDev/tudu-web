<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$tudu.subject}}</title>
{{include file="foreign^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js" type="text/javascript"></script>
<script src="/static/js?f=lang&lang={{$user.option.language}}&ver=1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/xheditor-1.1.9/xheditor-zh-cn.min.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/all.js?2007" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/foreign/tudu.js?1012" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/upload.js?1007" type="text/javascript"></script>
<script type="text/javascript">
<!--
var TOP = getTop();
-->
</script>
</head>
<body>

{{assign var="currUrl" value=$smarty.server.REQUEST_URI|escape:'url'}}
{{include file="foreign^header.tpl" fav=true}}
<div class="panel">
    <div class="panel-body">
      <div id="float-toolbar" class="float-toolbar">
         <div class="toolbar">
         {{include file="foreign#tudu#view^toolbar.tpl"}}
         </div>
      </div>
      <div id="toolbar" class="toolbar">
        {{include file="foreign#tudu#view^toolbar.tpl"}}
      </div>
      <div class="content_box2 todo_wrap">
{{if $remind || $cycle}}
{{strip}}
        <div class="msg">
        {{if $remind}}
        <p>{{if $tudu.privacy || $tudu.priority}}<b class="red">!</b>[{{if $tudu.priority && $tudu.privacy}}<span class="red">{{$LANG.urgent}}</span>+<span class="red">{{$LANG.private}}</span>
        {{elseif $tudu.priority}}<span class="red">{{$LANG.urgent}}</span>
        {{elseif $tudu.privacy}}<span class="red">{{$LANG.private}}</span>{{/if}}]{{/if}}{{$remind}}</p>
        {{/if}}
        {{if $cycleremind}}
        <p>{{$cycleremind}}</p>
        {{/if}}
        </div>
{{/strip}}
{{/if}}
        <div class="todo_title_wrap">
            <div class="todo_title">{{if $tudu.classname}}[{{$tudu.classname}}]{{/if}}{{$tudu.subject|escape:'html'}}{{if in_array('^all', $tudu.labels)}}<a href="javascript:void(0);" id="star" style="margin-left:5px" class="icon icon_attention{{if in_array('^t', $tudu.labels)}} attention{{/if}}" onclick="markStar('{{$tudu.tuduid}}', this);"></a>{{/if}}</div>
            <div class="tag_wrap">
            {{foreach item=label from=$labels}}
                {{if strpos($label.labelid, '^') === false && in_array($label.labelid, $tudu.labels)}}
                <table id="label-{{$label.labelid}}" cellspacing="0" cellpadding="0" class="flagbg" style="background-color:{{$label.bgcolor|default:'#d00'}}">
                  <tr class="falg_rounded_wrap">
                    <td class="falg_rounded"></td>
                    <td colspan="2"></td>
                    <td class="falg_rounded"></td>
                  </tr>
                  <tr>
                    <td class="falg_line"></td>
                    <td class="tag_txt" style="color:#fff">{{$label.labelalias}}</td>
                    <td class="tag_close" onclick="deleteLabel('{{$tudu.tuduid}}', '{{$label.labelalias}}', '{{$label.labelid}}')">&nbsp;</td>
                    <td class="falg_line"></td>
                  </tr>
                  <tr class="falg_rounded_wrap">
                    <td class="falg_rounded"></td>
                    <td colspan="2"></td>
                    <td class="falg_rounded"></td>
                  </tr>
                </table>
                {{/if}}
            {{/foreach}}
            </div>
            <div class="clear"></div>
        </div>
        <div class="todo_content">
            <table border="0" cellspacing="3" cellpadding="0">
              <colgroup>
                <col>
                <col width="200">
                <col>
                <col>
              </colgroup>
              <tr>
                <td align="right"><span class="gray">{{$LANG.title_sender}}</span></td>
                <td>{{$tudu.from.0}}</td>
                <td align="right"><span class="gray">{{$LANG.title_accept_user}}</span></td>
                <td{{if $tudu.cc}} title="{{foreach item=cc from=$tudu.cc name=cc}}{{if !$smarty.foreach.cc.first}},&#13;{{/if}}{{$cc.0}}{{if $cc.3}}<{{if strpos($cc.3, '@')}}{{$cc.3}}{{else}}{{$LANG.group}}{{/if}}>{{/if}}{{/foreach}}"{{/if}}>{{foreach item=cc from=$tudu.cc name=cc}}{{if !$smarty.foreach.cc.first}},{{/if}}{{$cc.0}}{{foreachelse}}-{{/foreach}}</td>
              </tr>
            </table>
            {{if 0}}
            <p><a href="#">所有附件</a>&nbsp;|&nbsp;<a href="#">进度更新</a>&nbsp;|&nbsp;<a href="#">日志</a>&nbsp;|&nbsp;<a href="#">打印</a>&nbsp;|&nbsp;<a href="#">邮件方式发送</a></p>
            {{/if}}
            <p><span class="icon icon_ext"></span><a href="javascript:void(0)" onclick="toggleAttach('{{$tudu.tuduid}}', '{{$tsid}}', '{{$user.uniqueid}}')">{{$LANG.attachment}}</a>&nbsp;&nbsp;&nbsp;<span class="icon icon_log"></span><a href="javascript:void(0)" onclick="toggleLog('{{$tudu.tuduid}}', '{{$tsid}}', '{{$user.uniqueid}}')">{{$LANG.log}}</a>&nbsp;&nbsp;&nbsp;<span class="icon icon_print"></span><a href="/foreign/tudu/print?tid={{$tudu.tuduid}}&fid={{$user.uniqueid}}&ts={{$tsid}}" target="_blank">{{$LANG.print}}</a></p>
        </div>
      </div>
      
      <!-- 附件 -->
      <div class="todo_expand" id="attach-panel" style="display:none;position:relative;">
        <a href="javascript:void(0)" class="icon icon_close" onclick="toggleAttach('{{$tudu.tuduid}}', '{{$tsid}}', '{{$user.uniqueid}}')" style="position:absolute;right:10px;top:10px"></a>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="todo_body">
          <tr>
            <td>
                <div class="todo_bd_tl"><div class="todo_bd_tr"><div class="todo_bd_tc"></div></div></div>
            </td>
          </tr>
          <tr>
            <td>
            <div class="todo_body_con">
                <div id="tudu-attach-list" class="log_list"></div>
            </div>
            </td>
          </tr>
          <tr>
            <td>
            <div style="position:relative">
                <div class="todo_bd_wrap">
                    <div class="todo_bd_bl"><div class="todo_bd_br"><div class="todo_bd_bc"></div></div></div>
                </div>
            </div>
            </td>
          </tr>
        </table>
      </div>

      <!-- 日志 -->
      <div class="todo_expand" id="log-panel" style="display:none;position:relative;">
        <a href="javascript:void(0)" class="icon icon_close" onclick="toggleLog('{{$tudu.tuduid}}', '{{$tsid}}', '{{$user.uniqueid}}')" style="position:absolute;right:10px;top:10px"></a>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="todo_body">
          <tr>
            <td>
                <div class="todo_bd_tl"><div class="todo_bd_tr"><div class="todo_bd_tc"></div></div></div>
            </td>
          </tr>
          <tr>
            <td>
            <div class="todo_body_con">
                <div id="log-list" class="log_list"></div>
            </div>
            </td>
          </tr>
          <tr>
            <td>
            <div style="position:relative">
                <div class="todo_bd_wrap">
                    <div class="todo_bd_bl"><div class="todo_bd_br"><div class="todo_bd_bc"></div></div></div>
                </div>
            </div>
            </td>
          </tr>
        </table>
      </div>

      <!-- 回复 -->
      {{foreach from=$posts item=post name=post}}
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
                {{include file="foreign#tudu#view^profile.tpl"}}
                {{if $post.isfirst}}<a href="javascript:void(0)" class="toggle_content icon_fold" onclick="togglePost('{{$post.postid}}', this)" title=""></a>{{/if}}
                </div>
                <div class="tudu-content-body">
                <div class="line_gray"></div>
                <div class="todo_info post-content">
                    {{$post.content|tudu_format_content}}
                </div>
                {{if $post.lastmodify}}
                <div class="footnote">
                <p class="gray">
                {{assign var=lastposttime value=$post.lastmodify.1|date_time_format:$user.option.dateformat}}
                {{$LANG.post_last_modify|sprintf:$post.lastmodify.2:$lastposttime}}
                </p>
                </div>
                {{/if}}
                {{if $post.attachnum}}
                <div class="line_gray"></div>
                <div class="todo_el">
                    <span><strong>{{$LANG.attachment}}</strong>({{$post.attachnum}}{{$LANG.attach_unit}}<!-- <a href="#">{{$LANG.download_all}}</a> -->)</span>
                    {{foreach item=file from=$post.attachment}}
                    <p><span class="icon ficon {{$file.filename|file_ext}}"></span><a href="{{$file.fileid|tudu_get_attachment_url}}">{{$file.filename}}</a><span class="gray">({{$file.size|format_filesize}})</span><a href="{{$file.fileid|tudu_get_attachment_url:view}}" target="_blank">[{{$LANG.open_file}}]</a></p>
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
                        <td valign="bottom">

                        </td>
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
      {{/foreach}}
    </div>
</div>

<div style="display:none">
<table id="label-tpl" cellspacing="0" cellpadding="0" class="flagbg"><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr><tr><td class="falg_line"></td><td class="tag_txt"></td><td class="tag_close"></td><td class="falg_line"></td></tr><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr></table>
</div>

<script type="text/javascript">
<!--
_TUDU_ID = '{{$tudu.tuduid}}';
_BACK = '{{$back}}';
var _CUS_LABELS = {};
{{foreach item=item from=$labels name=label}}{{if !$item.issystem}}_CUS_LABELS['{{$item.labelid}}'] = {name: '{{$item.labelalias}}', bgcolor: '{{$item.bgcolor}}'};{{/if}}{{/foreach}}

$(function(){
    $('.todo_info a').attr('target', '_blank');

    $('button[name="close"]').click(function(){
        window.close();
    });

    {{if $last}}
    window.scrollTo(0,$('div.todo_expand:last').offset().top);
    {{/if}}

    ajustReplyContent();

    new FixToolbar({
        src: '#toolbar',
        target: '#float-toolbar'
    });
});
function addfav() {
	var url   = '{{$options.sites.tudu}}{{$smarty.server.REQUEST_URI}}',
        title = '{{$tudu.subject}}';

    if (window.sidebar) {
        window.sidebar.addPanel(title,url,"");
    } else if ( document.all) {
        window.external.AddFavorite(url,title);
    }
}
-->
</script>
</body>
</html>