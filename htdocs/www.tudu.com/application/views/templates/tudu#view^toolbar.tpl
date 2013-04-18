{{strip}}
<div>
<button class="btn wd50" type="button" name="back">{{$LANG.back}}</button>
<span class="tb-sep"></span>

{{if $access.accept}}<button class="btn wd60 b" type="button" name="accept">{{$LANG.accept}}</button>{{/if}}
{{if $access.claim}}<button class="btn wd60 b" type="button" name="claim">{{$LANG.claim}}</button>{{/if}}
{{if $access.reject}}<button class="btn wd60 b" type="button" name="reject">{{$LANG.refuse}}</button>{{/if}}
{{if $access.divide}}<button class="btn wd50" type="button" name="divide" _more="{{if $moreaccepter}}1{{else}}0{{/if}}">{{$LANG.tudu_divide}}</button>{{/if}}
{{if $access.forward}}<button class="btn wd50" type="button" name="forward">{{$LANG.forward}}</button>{{/if}}
{{if $access.agree}}<button class="btn wd60 b" type="button" name="agree" _type="{{$tudu.type}}">{{$LANG.agree}}</button>{{/if}}
{{if $access.disagree}}<button class="btn wd70 b" type="button" name="disagree" _type="{{$tudu.type}}">{{$LANG.disagree}}</button>{{/if}}
{{if $access.modify}}<button class="btn wd50" type="button" name="modify">{{$LANG.modify}}</button>{{/if}}
{{*
{{if $access.resetvote}}<button class="btn wd80" type="button" name="reset">{{$LANG.resetvote}}</button>{{/if}}
*}}
{{if $access.confirm}}<button class="btn wd50" type="button" name="done">{{$LANG.confirm_tudu}}</button>{{/if}}
{{if $access.cancel}}<button class="btn wd50" type="button" name="cancel">{{$LANG.suspend}}</button>{{/if}}
{{if $access.undone}}<button class="btn wd80" type="button" name="canceldone">{{$LANG.cancel_done}}</button>{{/if}}

{{if $tudu.type == 'discuss' && $access.sender}}
{{if !$tudu.isdone}}<button class="btn wd80" type="button" name="close">{{$LANG.close_discuss}}</button>
{{else}}<button class="btn wd80" type="button" name="reopen">{{$LANG.reopen_discuss}}</button>{{/if}}
{{/if}}

{{if $access.invite}}
<button class="btn wd50" type="button" name="invite">{{$LANG.invite}}</button>
{{/if}}

{{if $access.ignore}}
{{if in_array('^i', $tudu.labels)}}
<button class="btn wd50" type="button" name="ignore">{{$LANG.ignore}}</button>
{{elseif in_array('^g', $tudu.labels)}}
<button class="btn wd85" type="button" name="inbox">{{$LANG.move_to_inbox}}</button>
{{/if}}
{{/if}}

{{if $access.delete}}
<button class="btn wd50" type="button" name="delete">{{$LANG.delete}}</button>
{{/if}}

{{if $tudu.uniqueid && in_array('^all', $tudu.labels)}}
<div class="select-tabs-wrap mark"><div class="select-tabs"><span class="select-tabs-text">{{$LANG.signas}}...</span> <a href="javascript:void(0);"></a></div></div>
{{else}}
<button class="btn wd85" type="button" name="inbox">{{$LANG.add_to_inbox}}</button>
{{/if}}

{{if $access.merge}}
<button class="btn wd80" type="button" name="merge">{{$LANG.append_group}}</button>
{{/if}}

{{if $access.modify}}
<button class="btn wd85" name="foreign" type="button">{{$LANG.foreign_manage}}</button>
{{/if}}
</div>
{{pagenavigator url=$pageinfo.url query=$pageinfo.query currpage=$pageinfo.currpage recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl" numcount=5}}
{{/strip}}