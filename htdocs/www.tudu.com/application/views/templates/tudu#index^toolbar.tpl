{{strip}}
{{if $label.labelalias == 'drafts'}}
<div class="toolbar">
    <div><div><button class="btn" type="button" name="delete">{{$LANG.delete}}</button></div></div>
    {{pagenavigator url=$pageinfo.url currpage=$pageinfo.currpage query=$pageinfo.query recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl" numcount=5}}
</div>
{{elseif $label.labelalias == 'ignore'}}
<div class="toolbar">
    <div><div><button class="btn" type="button" name="inbox">{{$LANG.move_to_inbox}}</button>{{*<button class="btn" type="button" name="ignore">{{$LANG.cancel_ignore}}</button>*}}<span class="tb-sep"></span>
    <div class="select-tabs-wrap mark"><div class="select-tabs"><span class="select-tabs-text">{{$LANG.signas}}...</span> <a href="javascript:void(0);"></a></div></div>
    </div></div>
    {{pagenavigator url=$pageinfo.url currpage=$pageinfo.currpage query=$pageinfo.query recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl" numcount=5}}
</div>
{{elseif $label.labelalias == 'todo'}}
<div class="toolbar">
    <div><div><button class="btn" type="button" name="percent">{{$LANG.update_progress}}</button><button class="btn" type="button" name="forward">{{$LANG.forward}}</button><button class="btn" type="button" name="accept">{{$LANG.accept}}</button><button class="btn" type="button" name="reject">{{$LANG.refuse}}</button><button class="btn" type="button" name="ignore">{{$LANG.ignore}}</button><span class="tb-sep"></span>
    <div class="select-tabs-wrap mark"><div class="select-tabs"><span class="select-tabs-text">{{$LANG.signas}}...</span> <a href="javascript:void(0);"></a></div></div>
    </div></div>
    {{if $issort}}
    <div class="page">
    <a href="/tudu/?search={{$label.labelalias}}">{{$LANG.default_sort}}</a>
    </div>
    {{/if}}
</div>
{{elseif $label.labelalias == 'inbox'}}
<div class="toolbar">
    <div><div><button class="btn" type="button" name="ignore">{{$LANG.ignore}}</button><span class="tb-sep"></span>
    <div class="select-tabs-wrap mark"><div class="select-tabs"><span class="select-tabs-text">{{$LANG.signas}}...</span> <a href="javascript:void(0);"></a></div></div>
    </div></div>
    {{if $issort}}
    <div class="page">
    <a href="/tudu/?search=inbox">{{$LANG.default_sort}}</a>
    </div>
    {{/if}}
</div>
{{elseif $label.labelalias == 'review'}}
<div class="toolbar">
    <div><div><button class="btn" type="button" name="agree">{{$LANG.agree}}</button><button class="btn" type="button" name="disagree">{{$LANG.disagree}}</button><span class="tb-sep"></span>
    <div class="select-tabs-wrap mark"><div class="select-tabs"><span class="select-tabs-text">{{$LANG.signas}}...</span> <a href="javascript:void(0);"></a></div></div>
    </div></div>
    {{if $issort}}
    <div class="page">
    <a href="/tudu/?search=review">{{$LANG.default_sort}}</a>
    </div>
    {{/if}}
</div>
{{elseif $label.labelalias == 'sent'}}
<div class="toolbar">
    <div><div><button class="btn" type="button" name="inbox">{{$LANG.add_to_inbox}}</button><button class="btn" type="button" name="confirm">{{$LANG.confirm_tudu}}</button><span class="tb-sep"></span>
    <div class="select-tabs-wrap mark"><div class="select-tabs"><span class="select-tabs-text">{{$LANG.signas}}...</span> <a href="javascript:void(0);"></a></div></div>
    </div></div>
    {{pagenavigator url=$pageinfo.url currpage=$pageinfo.currpage query=$pageinfo.query recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl" numcount=5}}
</div>
{{else}}
<div class="toolbar">
    <div><div><button class="btn" type="button" name="inbox">{{$LANG.add_to_inbox}}</button><span class="tb-sep"></span>
    <div class="select-tabs-wrap mark"><div class="select-tabs"><span class="select-tabs-text">{{$LANG.signas}}...</span> <a href="javascript:void(0);"></a></div></div>
    </div></div>
    {{pagenavigator url=$pageinfo.url currpage=$pageinfo.currpage query=$pageinfo.query recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl" numcount=5}}
</div>
{{/if}}
{{/strip}}