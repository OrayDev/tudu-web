<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1019" type="text/javascript"></script>
<style type="text/css">
<!--
.reset {
    width: 120px;
    height: auto;
    padding: 10px;
}
.reset div {
    text-align:center;
    width: 100%;
    text-overflow:ellipsis;
    white-space:nowrap;
    overflow:hidden;
}
.border_red {
    border-color: #c00;
}
.step_box_item{
    *display:inline;
    width:137px;
}
.mail_input{
    width: 90px;
    height:16px;
    overflow: hidden;
    display:inline-block;
    *display:inline;
    zoom:1;
}
-->
</style>
</head>

<body style="padding:0 5px 5px">
<form id="theform" action="/app/attend/category/save" method="post">
<input name="action" value="{{$action}}" type="hidden" />
{{if $action == 'update'}}
<input name="categoryid" value="{{$category.categoryid}}" type="hidden" />
{{/if}}
  {{include file="attend^tab.tpl" tab="review"}}
  <div class="tab-panel-body">
    <div class="toolbar">
        <div class="toolbar_nav">
            <a href="/app/attend/category/index" class="toolbar_nav_on">{{$LANG.attend_category}}</a>
        </div>
    </div>
    <div class="readmailinfo">
        <div class="module">
            <div class="module_title"><strong>{{if $action == 'update'}}{{$LANG.update_attend_category}}{{else}}{{$LANG.new_attend_category}}{{/if}}</strong><div class="module_title_ext"><a href="http://www.tudu.com/help/question/1151.html" target="_blank">{{$LANG.about_help}}</a></div></div>
            <div class="line_bold"></div>
            <p style="padding:5px;">{{$LANG.category_name}} <input name="categoryname" value="{{$category.categoryname|escape:'html'}}" type="text" class="input_text" style="width:170px;"{{if $category.issystem}} disabled="disabled" _disabled="disabled"{{/if}} /></p>
            <div class="set_flow">
                <div class="set_flow_title">{{$LANG.set_flow}}</div>
                <table border="0" cellspacing="10" cellpadding="0" class="set_flow_step">
                    <tr>
                        <td align="center"><span class="step_eliptic"><span>{{$LANG.apply_start}}</span></span></td>
                    </tr>
                    <tbody id="step-list">
                        <tr id="step-add">
                            <td align="center"><span class="icon icon_plus"></span>&nbsp;<a href="javascript:void(0)" name="add-steps" style="vertical-align:middle;">{{$LANG.add_flow_step}}</a></td>
                        </tr>
                    </tbody>
                    <tr>
                        <td align="center"><span class="icon icon_next"></span></td>
                    </tr>
                    <tr>
                        <td align="center"><span class="step_eliptic"><span>{{$LANG.apply_end}}</span></span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="toolbar">
        <div style="height:24px;"><button class="btn" name="save">{{$LANG.save}}</button><button class="btn" name="cancel" onclick="location='/app/attend/category/index'">{{$LANG.cancel}}</button></div>
    </div>
  </div>
</form>
</body>
<script type="text/javascript">
var Lang = {reviewer: '{{$LANG.reviewer}}', days: '{{$LANG.days}}', execute: '{{$LANG.execute}}'};
$(function(){
    TOP.Label.focusLabel('attend');
    TOP.Frame.title({{if $action == 'update'}}'{{$LANG.update_attend_category}}'{{else}}'{{$LANG.new_attend_category}}'{{/if}});
    TOP.Frame.hash('m=app/attend/category/modify{{if $category.categoryid}}&categoryid={{$category.categoryid}}{{/if}}');

    var steps = [];
    {{foreach item=step key=index from=$category.flowsteps}}
    {{if $step.branches}}
    var branches = [];
    {{foreach item=branch key=key from=$step.branches}}
    branches.push({branch: '{{$key}}', type: '{{$branch.type}}', start: '{{$branch.start}}', end: '{{$branch.end}}', users: '{{$branch.users|replace:"\n":","}}'});
    {{/foreach}}
    steps.push({member: '{{$index}}', id: '{{$step.id}}', order: '{{$index}}', branches: branches});
    {{else}}
    steps.push({member: '{{$index}}', id: '{{$step.id}}', order: '{{$index}}', type: '{{$step.type}}', users: '{{$step.users|replace:"\n":","}}'});
    {{/if}}
    {{/foreach}}
    Attend.Category.setLang({params_invalid_category_name: '{{$LANG.params_invalid_category_name}}', params_invalid_flow_name: '{{$LANG.params_invalid_flow_name}}'});
    {{if $action == 'update' && $category.categoryid == '^checkin'}}
    Attend.Category.disabledBranch = true;
    {{/if}}
    Attend.Category.initModify('{{$action}}', steps);
});
</script>
<script src="{{$options.sites.static}}/js/attend/category.js?1001" type="text/javascript"></script>
</html>