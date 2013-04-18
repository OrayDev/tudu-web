<div class="toolbar">
    <input type="hidden" id="uid" value="{{$log.senderid}}" />
    {{strip}}
    <div class="tb_empty" id="chat-title"><strong>{{if $group}}{{$LANG.discuss_log_with|sprintf:'<span class="sendername"></span>'}}{{else}}{{$LANG.chat_log_with|sprintf:'<span class="sendername"></span>'}}{{/if}}</strong>{{if $email}}&nbsp;&nbsp;<a href="javascript:void(0)" name="delete-log" onclick="deleteChatLogWin('{{$email}}')">[{{$LANG.delete_records}}]</a>{{/if}}</div>
    <div class="page" style="top: 4px;">
        {{if !$lastpage}}
        <a href="javascript:void(0)" onclick="loadChatLog('{{if $email}}{{$email}}{{else}}{{$group}}{{/if}}', {page: {{math equation="x+1" x=$page}}}{{if $group}}, 'group'{{/if}});">{{$LANG.page_prev}}</a>
        {{/if}}
        {{if $page != 1}}&nbsp;&nbsp;
        <a href="javascript:void(0)" onclick="loadChatLog('{{if $email}}{{$email}}{{else}}{{$group}}{{/if}}', {page: {{math equation="x-1" x=$page}}}{{if $group}}, 'group'{{/if}});">{{$LANG.page_next}}</a>
        {{/if}}
        {{if !$lastpage || $page != 1}}&nbsp;&nbsp;<input type="text" name="pageinput" class="input_text" style="width:20px;text-align:center" /><button class="btn" name="gopage" type="button" style="margin:0" onclick="loadChatLog('{{if $email}}{{$email}}{{else}}{{$group}}{{/if}}', {ispage: 1, page: $(this).prev().val()}{{if $group}}, 'group'{{/if}});">Go</button>{{/if}}
    </div>
    {{/strip}}
</div>
<div class="panel-body">
    <div class="chat_box" id="chat-content">
    {{foreach from=$logs key=date item=item}}
        <div class="chat_part">
            <p class="chat_date"><strong class="gray">{{$LANG.date}}{{$LANG.cln}}{{$date|date_format:'%Y-%m-%d'}}</strong></p>
            {{foreach from=$item item=log}}
            <div class="chat_log" id="log-{{$log.chatlogid}}">
            {{if $log.senderid == $user.email}}
            <div>
                <div style="float:right;"><a href="javascript:void(0)" name="del-log" style="display: none;" onclick="deleteChatLog('{{$log.chatlogid}}', '{{$email}}')")">[{{$LANG.delete_record}}]</a></div>
                <span class="font_c">{{$user.truename}}</span> <span class="f10 gray">{{$log.createtime|date_format:'%H:%M:%S'}}</span>
            </div>
            {{else}}
            <div>
                <div style="float:right;"><a href="javascript:void(0)" name="del-log" style="display: none;" onclick="deleteChatLog('{{$log.chatlogid}}', '{{$email}}')">[{{$LANG.delete_record}}]</a></div>
                <span class="blue sendername">{{$log.senderid}}</span> <span class="f10 gray">{{$log.createtime|date_format:'%H:%M:%S'}}</span>
            </div>
            {{/if}}
            <div class="chat_content" style="word-break:break-all;word-wrap:break-word">{{$log.content|strip_tags:'br'|nl2br}}</div>
            </div>
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
    <div class="page" style="top: 4px;">
        {{if !$lastpage}}
        <a href="javascript:void(0)" onclick="loadChatLog('{{if $email}}{{$email}}{{else}}{{$group}}{{/if}}', {page: {{math equation="x+1" x=$page}}}{{if $group}}, 'group'{{/if}});">{{$LANG.page_prev}}</a>
        {{/if}}
        {{if $page != 1}}&nbsp;&nbsp;
        <a href="javascript:void(0)" onclick="loadChatLog('{{if $email}}{{$email}}{{else}}{{$group}}{{/if}}', {page: {{math equation="x-1" x=$page}}}{{if $group}}, 'group'{{/if}});">{{$LANG.page_next}}</a>
        {{/if}}
        {{if !$lastpage || $page != 1}}&nbsp;&nbsp;<input type="text" name="pageinput" class="input_text" style="width:20px;text-align:center" /><button class="btn" name="gopage" type="button" style="margin:0" onclick="loadChatLog('{{if $email}}{{$email}}{{else}}{{$group}}{{/if}}', {ispage: 1, page: $(this).prev().val()}{{if $group}}, 'group'{{/if}});">Go</button>{{/if}}
    </div>
    {{/strip}}
</div>