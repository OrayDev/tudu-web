<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1019" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/attend/plan.js?1002" type="text/javascript"></script>
</head>

<body style="padding:0 5px 5px">
<input type="hidden" id="year" value="{{$query.year}}" />
<input type="hidden" id="month" value="{{$query.month}}" />
{{assign var="currUrl" value=$smarty.server.REQUEST_URI|urlencode}}
  {{include file="attend^tab.tpl" tab="schedule"}}
  <div class="tab-panel-body">
    {{include file="schedule^toolbar.tpl" tab="plan"}}
    <div class="readmailinfo">
        <div class="module">
            <form method="get" action="?">
            {{assign var="year" value=$smarty.now|date_format:"%Y"}}
            <p>{{$LANG.name}}{{$LANG.cln}}<input id="keyword" name="keyword" type="text" class="input_text" title="{{$LANG.enter_keywords}}" style="width:160px;" value="{{$query.keyword}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$LANG.search_object}}{{$LANG.cln}}
                <select id="deptid" name="deptid">
                    <option value="">所有部门</option>
                    {{foreach item=dept from=$depts}}
                    <option value="{{$dept.deptid}}"{{if $pageinfo.query.deptid == $dept.deptid}} selected="selected"{{/if}}>{{$dept.prefix}}{{$dept.deptname}}</option>
                    {{/foreach}}
                </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$LANG.search_date}}{{$LANG.cln}}
                <select name="year" id="year">
                    {{section name=year start=2012 loop=2023}}
                    <option value="{{$smarty.section.year.index}}"{{if $smarty.section.year.index == $query.year}} selected="selected"{{/if}}>{{$smarty.section.year.index}}{{$LANG.year}}</option>
                    {{/section}}
                </select>&nbsp;
                <select name="month" id="month">
                    {{section name=month start=1 loop=13}}
                    <option value="{{$smarty.section.month.index}}"{{if $smarty.section.month.index == $query.month}} selected="selected"{{/if}}>{{$smarty.section.month.index}}{{$LANG.month}}</option>
                    {{/section}}
                </select>&nbsp;&nbsp;
                <input type="submit" value="{{$LANG.btn_search}}" class="btn" />
            </p>
            </form>
            <div class="table_list_wrap" style="margin-top:10px;">
                <div class="table_list_title">
                    <strong>{{$LANG.schedule_plan}}</strong>
                    <div style="padding: 10px 0"><button name="modify" class="btn">{{$LANG.edit_plan}}</button></div>
                </div>
                <table width="100%" cellspacing="0" cellpadding="5" align="center" class="table_list">
                    <colgroup>
                        <col width="170"/>
                        <col width="170"/>
                        <col />
                        <col width="100" />
                    </colgroup>
                    <tr>
                        <th align="left"><input type="checkbox" id="checkall" />&nbsp;{{$LANG.name}}</th>
                        <th align="left">{{$LANG.dept}}</th>
                        <th align="left">{{$LANG.remark}}</th>
                        <th align="right">{{$LANG.operate}}</th>
                    </tr>
                    <tbody id="user-list">
                        {{foreach item=item from=$users}}
                        <tr>
                            <td align="left" title="{{$item.userid}}@{{$item.orgid}}"><input type="checkbox" name="uniqueid" value="{{$item.uniqueid}}" />&nbsp;{{$item.truename}}</td>
                            <td align="left">{{$item.deptname|default:'-'}}</td>
                            <td align="left" title="{{$item.memo|escape:'html'}}">{{$item.memo|escape:'html'|truncate:35|default:'-'}}</td>
                            <td align="right"><a href="/app/attend/schedule/userplan?unid={{$item.uniqueid}}&year={{$query.year}}&month={{$query.month}}&back={{$currUrl}}">[{{$LANG.view}}]</a>&nbsp;<a href="/app/attend/schedule/plan?uniqueid={{$item.uniqueid}}&year={{$query.year}}&month={{$query.month}}&back={{$currUrl}}">[{{$LANG.schedule_plan}}]</a></td>
                        </tr>
                        {{foreachelse}}
                        <tr>
                            <td colspan="4" style="text-align:center;padding:35px 0">{{$LANG.search_null_or_null_data}}</td>
                        </tr>
                      {{/foreach}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="toolbar">
        <div style="height:24px;"></div>
        {{pagenavigator url=$pageinfo.url query=$pageinfo.query currpage=$pageinfo.currpage recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl"}}
    </div>
  </div>
</body>
<script type="text/javascript">
$(function(){
    TOP.Label.focusLabel('attend');
    TOP.Frame.title('{{$LANG.schedule_plan}}');
    TOP.Frame.hash('m=app/attend/schedule/user{{foreach key=key item=val from=$query}}&{{$key}}={{$val}}{{/foreach}}');
    var currUrl = '{{$smarty.server.REQUEST_URI|escape:'url'}}';
    Attend.Plan.initList(currUrl);

    $('#checkall').bind('click', function() {
        $('input[name="uniqueid"]').attr('checked', this.checked);
    });
});
</script>
</html>