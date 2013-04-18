{{strip}}
<div class="page">
{{if $pagenav.currpage > 1}}&nbsp;<a class="font_c" href="{{page_url url=$pagenav.url page=1 query=$pagenav.query}}">{{$LANG.page_first}}</a>&nbsp;<a class="font_c" href="{{page_url url=$pagenav.url page=$pagenav.prev query=$pagenav.query}}">{{$LANG.page_prev}}</a>&nbsp;{{/if}}
&nbsp;{{$pagenav.currpage}}/{{$pagenav.pagecount}}&nbsp;
{{if $pagenav.currpage < $pagenav.pagecount}}&nbsp;<a class="font_c" href="{{page_url url=$pagenav.url page=$pagenav.next query=$pagenav.query}}">{{$LANG.page_next}}</a>&nbsp;<a href="{{page_url url=$pagenav.url page=$pagenav.pagecount query=$pagenav.query}}">{{$LANG.page_last}}</a>{{/if}}
{{if $pagenav.pagecount > 1}}&nbsp;<input type="text" name="pageinput" class="input_text" style="width:20px;text-align:center" /><button class="btn" name="gopage" type="button" style="margin:0" onclick="location=('{{page_url url=$pagenav.url page='^page' query=$pagenav.query}}').replace('%5Epage', $(this).prev().val())">Go</button>{{/if}}
</div>
{{/strip}}