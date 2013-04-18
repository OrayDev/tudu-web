{{strip}}
<div>
{{if !$newwin}}
<button class="btn wd50" type="button" name="back">{{$LANG.back}}</button>
{{else if $newwin}}
<button class="btn wd50" type="button" name="closewin">{{$LANG.close}}</button>
{{/if}}
<span class="tb-sep"></span>

{{if $access.agree}}<button class="btn wd60 b" type="button" name="agree" _type="{{$tudu.type}}">{{$LANG.agree}}</button>{{/if}}
{{if $access.disagree}}<button class="btn wd70 b" type="button" name="disagree" _type="{{$tudu.type}}">{{$LANG.disagree}}</button>{{/if}}
{{if $access.modify}}<button class="btn wd50" type="button" name="modify">{{$LANG.modify}}</button>{{/if}}

{{if $access.cancel}}<button class="btn wd80" type="button" name="cancel">{{$LANG.cancel_apply}}</button>{{/if}}

{{if $access.delete}}
<button class="btn wd50" type="button" name="delete">{{$LANG.delete}}</button>
{{/if}}

{{if $tudu.uniqueid && in_array('^all', $tudu.labels)}}
<div class="select-tabs-wrap mark"><div class="select-tabs"><span class="select-tabs-text">{{$LANG.signas}}...</span> <a href="javascript:void(0);"></a></div></div>
{{else}}
<button class="btn wd85" type="button" name="inbox">{{$LANG.add_to_inbox}}</button>
{{/if}}
</div>
{{pagenavigator url=$pageinfo.url query=$pageinfo.query currpage=$pageinfo.currpage recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl" numcount=5}}
{{/strip}}