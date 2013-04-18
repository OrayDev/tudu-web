<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.chat_log}}</title>
{{include file="^style.tpl"}}
<style type="text/css">
<!--
body {
	background-image: url(/images/icon/chat_log_bg.jpg);
	background-repeat: repeat-x;
}
.public_chat_header {
    height:65px;
    position:relative;
}
.public_chat_title {
    position:absolute;
    top: 40px;
    right: 0;
}
.toolbar .page {
	top: 5px;
}
-->
</style>
<script src="{{$options.sites.static}}/js/jquery-1.4.2.js" type="text/javascript"></script>
</head>
<body class="chat">
<div class="public_chat_header">
<div class="public_chat_title"><img src="/images/icon/chat_log.gif" border="0" title="聊天记录" /></div>
<img src="/images/icon/chat_log_logo.gif" border="0" />
</div>
<div class="container">
    <div class="panel" id="log-content">
    	<div class="toolbar">
    		{{strip}}
		    <div class="tb_empty"><strong>{{$LANG.chat_log_with|sprintf:'<span class="sendername" id="name"></span>'}}</strong></div>
		    {{if $pageinfo.recordcount > 0}}<div class="page">
            {{if $pageinfo.currpage <= $pageinfo.pagecount && $pageinfo.currpage != 1}}<a href="/public/chat/log?jid={{$jid}}&email={{$email}}&page=1">{{$LANG.page_first}}</a>&nbsp;<a href="/public/chat/log?jid={{$jid}}&email={{$email}}&page={{math equation='x-1' x=$pageinfo.currpage}}">{{$LANG.page_prev}}</a>&nbsp;{{/if}}
            {{$pageinfo.currpage}}/{{$pageinfo.pagecount}}
            {{if $pageinfo.currpage >= 1 && $pageinfo.currpage < $pageinfo.pagecount}}&nbsp;<a href="/public/chat/log?jid={{$jid}}&email={{$email}}&page={{math equation='x+1' x=$pageinfo.currpage}}">{{$LANG.page_next}}</a>&nbsp;<a href="/public/chat/log?jid={{$jid}}&email={{$email}}&page={{$pageinfo.pagecount}}">{{$LANG.page_last}}</a>{{/if}}
            {{if $pageinfo.pagecount >= 1 && $pageinfo.currpage < $pageinfo.pagecount}}&nbsp;<input type="text" name="up_pageinput" class="input_text" style="width:20px;text-align:center" /><button name="gopage" type="button" style="margin:0" onclick="loadChatLog('up');">Go</button>{{/if}}
            </div>{{/if}}
            {{/strip}}
		</div>
		<div class="panel-body">
		    <div class="chat_box" id="chat-content">
		    {{foreach from=$logs key=date item=item}}
            <div class="chat_part">
                <p class="chat_date"><strong class="gray">{{$LANG.date}}{{$LANG.cln}}{{$date|date_format:'%Y-%m-%d'}}</strong></p>
                {{foreach from=$item item=log}}
                <p>{{if $log.senderid == $email}}<span class="blue sendername">{{$info.displayname}}</span>{{elseif $log.senderid == $jid}}<span class="font_c">我</span>{{/if}} <span class="f10 gray">{{$log.createtime|date_format:'%H:%M:%S'}}</span></p>
                <div class="chat_content" style="word-break:break-all;word-wrap:break-word">{{$log.content|escape:'html'|nl2br}}</div>
                {{/foreach}}
            </div>
        	{{foreachelse}}
            <div style="padding:20px 10px;text-align:center;">
                {{$LANG.chat_log_null}}
            </div>
        	{{/foreach}}
		    </div>
		</div>
		<div class="toolbar">
		    {{strip}}
            <div class="tb_empty"></div>
            {{if $pageinfo.recordcount > 0}}<div class="page">
            {{if $pageinfo.currpage <= $pageinfo.pagecount && $pageinfo.currpage != 1}}<a href="/public/chat/log?jid={{$jid}}&email={{$email}}&page=1">{{$LANG.page_first}}</a>&nbsp;<a href="/public/chat/log?jid={{$jid}}&email={{$email}}&page={{math equation='x-1' x=$pageinfo.currpage}}">{{$LANG.page_prev}}</a>&nbsp;{{/if}}
            {{$pageinfo.currpage}}/{{$pageinfo.pagecount}}
            {{if $pageinfo.currpage >= 1 && $pageinfo.currpage < $pageinfo.pagecount}}&nbsp;<a href="/public/chat/log?jid={{$jid}}&email={{$email}}&page={{math equation='x+1' x=$pageinfo.currpage}}">{{$LANG.page_next}}</a>&nbsp;<a href="/public/chat/log?jid={{$jid}}&email={{$email}}&page={{$pageinfo.pagecount}}">{{$LANG.page_last}}</a>{{/if}}
            {{if $pageinfo.pagecount >= 1 && $pageinfo.currpage < $pageinfo.pagecount}}&nbsp;<input type="text" name="down_pageinput" class="input_text" style="width:20px;text-align:center" /><button name="gopage" type="button" style="margin:0" onclick="loadChatLog('down');">Go</button>{{/if}}
            </div>{{/if}}
            {{/strip}}
		</div>
    </div>
</div>
<script type="text/javascript">
<!--
$('#name').text('{{$info.displayname}}');
function loadChatLog(type) {
	if (type == 'up') {
		var page = $('input[name="up_pageinput"]').val();
	} else {
		var page = $('input[name="down_pageinput"]').val();
	}
	location = '/public/chat/log?jid={{$jid}}&email={{$email}}&page=' + page;
}
-->
</script>
</body>
</html>