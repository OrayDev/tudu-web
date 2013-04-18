<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1019" type="text/javascript"></script>
</head>

<body style="padding:0 5px 5px">
  {{include file="attend^tab.tpl" tab="review"}}
  <div class="tab-panel-body">
    <div class="toolbar">
       <div class="toolbar_nav">
           <a href="/app/attend/category/index" class="toolbar_nav_on">{{$LANG.attend_category}}</a>
       </div>
    </div>
    <div class="readmailinfo">
        <div class="module">
            <p><input name="add" type="button" class="btn" value="{{$LANG.create_category}}" onclick="location='/app/attend/category/modify'" /></p>
            <div class="table_list_wrap" style="margin-top:20px;">
                <div class="table_list_title"><strong>{{$LANG.attend_category}}</strong></div>
                <table width="100%" cellspacing="0" cellpadding="5" align="center" class="table_list">
                    <colgroup>
                        <col />
                        <col width="100" />
                    </colgroup>
                    <tr>
                        <th align="left">{{$LANG.category_name}}</th>
                        <th align="left">{{$LANG.operate}}</th>
                    </tr>
                    <tbody id="category-list">
                        {{foreach item=category from=$categories}}
                        <tr id="{{$category.categoryid}}">
                            <td align="left"{{if $category.status == 0}} class="red"{{/if}}>{{$category.categoryname}}</td>
                            <td><a href="/app/attend/category/modify?categoryid={{$category.categoryid}}">[{{$LANG.modify}}]</a>{{if $category.issystem}}{{if $category.status == 1}}&nbsp;<a href="javascript:void(0)" onclick="Attend.Category.updateStatus('{{$category.categoryid}}', 0)">[{{$LANG.stop_using}}]</a>{{else}}&nbsp;<a href="javascript:void(0)" onclick="Attend.Category.updateStatus('{{$category.categoryid}}', 1)">[{{$LANG.start_using}}]</a>{{/if}}{{else}}&nbsp;<a href="javascript:void(0)" onclick="Attend.Category.deleteCategory('{{$category.categoryid}}')">[{{$LANG.delete}}]</a>{{/if}}</td>
                        </tr>
                        {{/foreach}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="toolbar">
        <div style="height:24px;"></div>
    </div>
  </div>
</body>
<script type="text/javascript">
var Lang = {reviewer: '{{$LANG.reviewer}}'};
$(function(){
    TOP.Label.focusLabel('attend');
    TOP.Frame.title('{{$LANG.attend_flow}} - {{$LANG.attend_category}}');
    TOP.Frame.hash('m=app/attend/category/index');
    Attend.Category.setLang({confirm_stop_category: '{{$LANG.confirm_stop_category}}', confirm_delete_category: '{{$LANG.confirm_delete_category}}'});
    Attend.Category.init();
});
</script>
<script src="{{$options.sites.static}}/js/attend/category.js?1001" type="text/javascript"></script>
</html>