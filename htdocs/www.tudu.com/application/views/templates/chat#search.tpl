<div class="toolbar">
    {{strip}}
    <div class="tb_empty"><strong class="f14">{{$LANG.search_result}}</strong>&nbsp;|&nbsp;<a name="back" href="javascript:void(0)" onclick="loadContent('/chat/log-list?email=' + $('#otherid').val())">{{$LANG.back}}</a></div>
    {{if $pageinfo.recordcount > 0}}<div class="page">
    {{if $pageinfo.currpage > 1}}<a href="javascript:void(0)" onclick="searchPage(1);">{{$LANG.page_first}}</a>&nbsp;<a href="javascript:void(0)" onclick="searchPage({{math equation="x-1" x=$pageinfo.currpage}});">{{$LANG.page_prev}}</a>&nbsp;{{/if}}
    {{$pageinfo.currpage}}/{{$pageinfo.pagecount}}
    {{if $pageinfo.currpage < $pageinfo.pagecount}}&nbsp;<a href="javascript:void(0)" onclick="searchPage({{math equation="x+1" x=$pageinfo.currpage}});">{{$LANG.page_next}}</a>&nbsp;<a href="javascript:void(0)" onclick="searchPage({{$pageinfo.pagecount}});">{{$LANG.page_last}}</a>{{/if}}
    {{if $pageinfo.pagecount > 1}}&nbsp;<input type="text" name="pageinput" class="input_text" style="width:20px;text-align:center" /><button name="gopage" type="button" style="margin:0" onclick="searchPage(parseInt($(this).prev().val()));">Go</button>{{/if}}
    </div>{{/if}}
    {{/strip}}
</div>
<div class="panel-body">
    <div class="chat_box" id="chat-content" style="padding:0">
    {{foreach from=$logs item=log}}
        <div class="chat_part chat_part_btline" style="padding-left:10px;padding-right:10px">
            {{if $log.senderid == $user.email}}
            <div><a name="context" href="javascript:void(0)" onclick="loadChatDetail('{{if $query.target == '^groups'}}{{$query.groupid}}{{else}}{{$log.otherid}}{{/if}}', {chatlogid: '{{$log.chatlogid}}'}, '{{if $log.otherid}}user{{else}}group{{/if}}')" class="fr" style="display:none">查看前后信息</a><span class="font_c">{{$user.truename}}</span> <span class="f10 gray">{{$log.createtime|date_format:'%Y-%m-%d %H:%M:%S'}}</span></div>
            {{else}}
            <div><a name="context" href="javascript:void(0)" onclick="loadChatDetail('{{if $query.target == '^groups'}}{{$query.groupid}}{{else}}{{$log.otherid}}{{/if}}', {chatlogid: '{{$log.chatlogid}}'}, '{{if $log.otherid}}user{{else}}group{{/if}}')" class="fr" style="display:none">查看前后信息</a><span class="blue sendername">{{$log.senderid}}</span> <span class="f10 gray">{{$log.createtime|date_format:'%Y-%m-%d %H:%M:%S'}}</span></div>
            {{/if}}
            <div class="chat_content" style="word-break:break-all;word-wrap:break-word">{{$log.content|strip_tags:'br'|nl2br}}</div>
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
    {{if $pageinfo.currpage > 1}}<a href="javascript:void(0)" onclick="searchPage(1);">{{$LANG.page_first}}</a>&nbsp;<a href="javascript:void(0)" onclick="searchPage({{math equation="x-1" x=$pageinfo.currpage}});">{{$LANG.page_prev}}</a>&nbsp;{{/if}}
    {{$pageinfo.currpage}}/{{$pageinfo.pagecount}}
    {{if $pageinfo.currpage < $pageinfo.pagecount}}&nbsp;<a href="javascript:void(0)" onclick="searchPage({{math equation="x+1" x=$pageinfo.currpage}});">{{$LANG.page_next}}</a>&nbsp;<a href="javascript:void(0)" onclick="searchPage({{$pageinfo.pagecount}});">{{$LANG.page_last}}</a>{{/if}}
    {{if $pageinfo.pagecount > 1}}&nbsp;<input type="text" name="pageinput" class="input_text" style="width:20px;text-align:center" /><button name="gopage" type="button" style="margin:0" onclick="searchPage(parseInt($(this).prev().val()));">Go</button>{{/if}}
    </div>{{/if}}
    {{/strip}}
</div>
<script type="text/javascript">
/*$('#chat-content .chat_part, #chat-content .chat_part *')
.bind('mouseover', function(){
	if ($(this).hasClass('chat_part')) {
	    var o = $(this);
	} else {
	    var o = $(this).parents('.chat_part');
	}

	o.addClass('chat_part_over');
	o.find('a[name="context"]').show();

	var others = $('#chat-content .chat_part').not(this);
	others.removeClass('chat_part_over');
    others.find('a[name="context"]').hide();
});*/
/*.bind('mouseout', function(){
	if ($(this).hasClass('chat_part')) {
        var o = $(this);
    } else {
        var o = $(this).parents('.chat_part');
    }

	$(this).removeClass('chat_part_over');
	$(this).find('a[name="context"]').hide();
});*/

$('#chat-content').bind('mouseover', function(e){
	var srcEle = e.srcElement ? e.srcElement : e.target,
		o = null;
	if ($(srcEle).hasClass('chat_part')) {
		o = $(srcEle);
	} else {
		o = $(srcEle).parents('.chat_part:eq(0)');
	}

	if (o && o.size()) {
		o.addClass('chat_part_over');
	    o.find('a[name="context"]').show();

	    var others = $('#chat-content .chat_part').not(o);
	    others.removeClass('chat_part_over');
	    others.find('a[name="context"]').hide();
	}
});

function chatOver(obj){
    if ($(obj).hasClass('chat_part')) {
        var o = $(obj);
    } else {
        var o = $(obj).parents('.chat_part');
    }

    o.addClass('chat_part_over');
    o.find('a[name="context"]').show();

    var others = $('#chat-content .chat_part').not(obj);
    others.removeClass('chat_part_over');
    others.find('a[name="context"]').hide();
}

{{if $keys}}
highlight(['{{$keys}}']);
{{/if}}

function searchPage(page) {
	var query = [];
	{{foreach from=$query item=val key=key}}
	query.push('{{$key}}={{if $key == 'keyword'}}{{$val|escape:'url'}}{{else}}{{$val}}{{/if}}');
	{{/foreach}}
	query.push('page=' + page)
    url = '/chat/search?' + query.join('&');

	$('#log-content').load(url, function(){
        replaceSenderName('group');

        TOP.Frame.hash('m=' + url.replace('?', '&').replace(/^\/+?/, ''));

        ajustSize();
    });
}
</script>