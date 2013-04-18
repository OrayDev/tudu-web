<div class="toolbar">
    <div class="tb_empty"><strong class="f14">{{$LANG.search_result}}</strong>&nbsp;|&nbsp;<a name="back" href="javascript:void(0)" onclick="loadContent('{{$back}}')">{{$LANG.back}}</a></div>
</div>
<div class="panel-body">
    <div class="chat_box" id="chat-content">
        <div class="chat_part">
            <p class="chat_date"><strong class="gray">{{$LANG.date}}{{$LANG.cln}}{{$date|date_format:'%Y-%m-%d'}}</strong></p>
            {{foreach from=$logs item=log}}
            <div class="chat_log"  id="log-{{$log.chatlogid}}">
            {{if $log.senderid == $user.email}}
            <p>
            <div style="float:right;"><a href="javascript:void(0)" name="del-log" style="display: none;" onclick="deleteChatLog('{{$log.chatlogid}}')">[{{$LANG.delete_record}}]</a></div>
            <span class="font_c">{{$user.truename}}</span> <span class="f10 gray">{{$log.createtime|date_format:'%H:%M:%S'}}</span>
            </p>
            {{else}}
            <p>
            <div style="float:right;"><a href="javascript:void(0)" name="del-log" style="display: none;" onclick="deleteChatLog('{{$log.chatlogid}}')">[{{$LANG.delete_record}}]</a></div>
            <span class="blue sendername">{{$log.senderid}}</span> <span class="f10 gray">{{$log.createtime|date_format:'%H:%M:%S'}}</span>
            </p>
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
</div>
<div class="toolbar">
    <div class="tb_empty"></div>
</div>