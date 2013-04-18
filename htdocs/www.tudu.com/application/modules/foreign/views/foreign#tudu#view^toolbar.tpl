{{strip}}
<div>
<button class="btn" type="button" name="close">{{$LANG.close}}</button>
<span class="tb-sep"></span>
{{if $access.reply && $tudu.type != 'notice'}}<button class="btn" type="button" name="reply">{{$LANG.reply}}</button>{{/if}}

{{if $access.claim}}<button class="btn wd60 b" type="button" name="claim">{{$LANG.claim}}</button>{{/if}}
{{if $access.accept}}<button class="btn wd60 b" type="button" name="accept">{{$LANG.accept}}</button>{{/if}}
{{if $access.reject}}<button class="btn wd60 b" type="button" name="reject">{{$LANG.refuse}}</button>{{/if}}
{{if $access.agree}}<button class="btn wd60 b" type="button" name="agree">{{$LANG.agree}}</button>{{/if}}
{{if $access.disagree}}<button class="btn wd70 b" type="button" name="disagree">{{$LANG.disagree}}</button>{{/if}}
{{if $access.forward}}<button class="btn" type="button" name="forward">{{$LANG.forward}}</button>{{/if}}

</div>
{{pagenavigator url=$pageinfo.url query=$pageinfo.query currpage=$pageinfo.currpage recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="foreign^pagenav.tpl" numcount=5}}
{{/strip}}