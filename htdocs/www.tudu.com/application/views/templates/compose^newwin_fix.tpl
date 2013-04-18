<div id="win-ct">
<div id="castwin" class="pop_wrap" style="width:470px;display:none;position:absolute">
   <div class="pop pop_linkman">
      <div class="pop_header"><strong>{{$LANG.select_contact}}</strong><a class="icon icon_close close"></a></div>
         <div class="pop_body">
            <div>
                <div class="p_body_left">
                    <p><strong>{{$LANG.contact}}</strong></p>
                    <div class="pop_body_inner">
                        <div class="input_box"><input style="width:185px;" class="input_text" name="" id="contact_search" type="text" title="{{$LANG.search_user}}"><a class="icon icon_search_2"></a></div>
                        <div class="list_box" id="contact_box">
                        {{if 0}}
                            <div class="groupopen"><div>快捷通讯录</div></div>
                            <div id="q_contact"></div>
                        {{/if}}
                        <div id="user_select">
                            <div class="groupopen"><div>{{$LANG.dept_cast}}</div></div>
                            <div id="contactbox"></div>
                        </div>
                        <div id="group_select">
                            <div class="groupopen"><div>{{$LANG.group}}</div></div>
                            <div id="group_box"></div>
                        </div>
                        </div>
                        <div class="list_box" id="contact_search_result" style="display:none;">
                                
                        </div>
                    </div>
                </div>
                <div class="p_body_centre"></div>
                <div class="p_body_right">
                    <p><strong id="mtitle"></strong></p>
                    <div class="pop_body_inner">
                        <div class="list_box" id="target-user">
                            
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
           </div>
         </div>
      <div class="pop_footer"><button type="button" name="confirm" class="btn">{{$LANG.confirm}}</button><button type="button" class="btn close">{{$LANG.cancel}}</button></div>
   </div>
</div>
</div>

<script type="text/javascript">
<!--
var html = '<div style="margin:0 0 5px 0"><div style="float:right;text-align:right;width:100px;padding:20px 5px 0 0"><a href="/frame#{{if $type == 'board'}}m=board/modify{{if $board.boardid}}&bid={{$board.boardid}}{{/if}}{{else}}m=compose&type={{$type}}{{if $tudu}}&tid={{$tudu.tuduid}}{{/if}}{{/if}}">{{$LANG.return_frame}}</a></div><div style="margin-right:150px"><img src="/logo?oid={{$user.orgid}}" /></div></div>';
$(document.body).prepend(html);

var _NEW_WIN = true;
-->
</script>