<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1019" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/attend/attend.js?1004" type="text/javascript"></script>
</head>

<body style="padding:0 5px 5px">
  {{assign var="currUrl" value=$smarty.server.REQUEST_URI|urlencode}}
  {{include file="attend^tab.tpl" tab="count"}}
  <div class="tab-panel-body">
    <div class="toolbar">
       <div class="toolbar_nav"><a href="/app/attend/count/index" class="toolbar_nav_on">{{$LANG.attend_gather_count}}</a></div>
    </div>
    <div class="readmailinfo">
        <div class="module" style="padding-bottom:5px;">
            {{assign var="year" value=$smarty.now|date_format:"%Y"}}
            {{assign var="year" value=$year+5}}
            <p>{{if $roles && $roles.sum}}{{$LANG.name}}{{$LANG.cln}}<input id="keywords" name="keywords" type="text" class="input_text" title="{{$LANG.enter_keywords}}" style="width:160px;" value="{{$query.keywords}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{/if}}{{$LANG.search_date}}{{$LANG.cln}}
                <select name="year" id="year">
                    {{section name=year loop=10}}
                    <option value="{{math equation="$year-x" x=$smarty.section.year.index}}">{{math equation="$year-x" x=$smarty.section.year.index}}{{$LANG.year}}</option>
                    {{/section}}
                </select>&nbsp;
                <select name="month" id="month">
                    {{section name=month loop=12}}
                    <option value="{{math equation="x+1" x=$smarty.section.month.index}}">{{math equation="x+1" x=$smarty.section.month.index}}{{$LANG.month}}</option>
                    {{/section}}
                </select>{{if $roles && $roles.sum}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$LANG.search_object}}{{$LANG.cln}}
                <select id="deptid" name="deptid">
                    <option value="">{{$LANG.all_dept}}</option>
                    {{foreach item=dept from=$depts}}
                    {{if $dept.deptid != '^root'}}
                    <option value="{{$dept.deptid}}"{{if $dept.deptid == $query.deptid}} selected="selected"{{/if}}>{{$dept.prefix}}{{$dept.deptname}}</option>
                    {{/if}}
                    {{/foreach}}
                </select>{{/if}}&nbsp;&nbsp;
                <input name="search" type="button" value="{{$LANG.btn_search}}" class="btn" />
            </p>
            <div class="table_list_wrap" style="margin-top:10px;">
                <div class="table_list_title">
                    <strong>{{if $query.deptid}}{{$depts[$query.deptid].deptname}}{{else}}{{$LANG.all_dept}}{{/if}}</strong>
                    <div class="table_list_title_ext"><a href="/app/attend/count/export?type=month{{foreach key=key item=val from=$query}}&{{$key}}={{$val}}{{/foreach}}&url={{$currUrl}}">{{$LANG.export_data}}</a></div>
                </div>
                <table width="100%" cellspacing="0" cellpadding="5" align="center" class="table_list">
                    <tr>
                        <th align="left">{{$LANG.name}}</th>
                        <th align="left">{{$LANG.dept}}</th>
                        <th align="right">{{$LANG.category_late}}({{$LANG.times}})</th>
                        <th align="right">{{$LANG.category_leave}}({{$LANG.times}})</th>
                        <th align="right">{{$LANG.category_unwork}}({{$LANG.times}})</th>
                        {{foreach item=category from=$categories}}
                        <th align="right">{{$category.categoryname}}({{if $category.categoryid == '^checkin'}}{{$LANG.times}}{{else}}{{$LANG.hour}}{{/if}})</td>
                        {{/foreach}}
                        <th align="right">{{$LANG.operate}}</th>
                    </tr>
                    <tbody id="count-list">
                        {{foreach item=item from=$records}}
                        <tr>
                            <td align="left">{{if $item.isabnormalip}}<strong class="red">!</strong>{{/if}}<a href="/app/attend/count/list?unid={{$item.uniqueid}}&date={{$date.year}}&month={{$date.month}}&back={{$currUrl}}">{{$item.truename}}</a></td>
                            <td align="left">{{$item.deptname|default:'-'}}</td>
                            <td align="right">{{assign var=late value=$item.late|default:0}}{{if $late > 0}}<a href="javascript:void(0)" onclick="Attend.Infowin.show('late', '{{$item.uniqueid}}', '{{$date.year}}', '{{$date.month}}')">{{$late}}</a>{{else}}{{$late}}{{/if}}</td>
                            <td align="right">{{assign var=leave value=$item.leave|default:0}}{{if $leave > 0}}<a href="javascript:void(0)" onclick="Attend.Infowin.show('leave', '{{$item.uniqueid}}', '{{$date.year}}', '{{$date.month}}')">{{$leave}}</a>{{else}}{{$leave}}{{/if}}</td>
                            <td align="right">{{assign var=unwork value=$item.unwork|default:0}}{{if $unwork > 0}}<a href="javascript:void(0)" onclick="Attend.Infowin.show('unwork', '{{$item.uniqueid}}', '{{$date.year}}', '{{$date.month}}')">{{$unwork}}</a>{{else}}{{$unwork}}{{/if}}</td>
                            {{foreach item=category from=$categories}}
                            <td align="right">{{foreach item=val key=key from=$item}}{{if $key==$category.categoryid|replace:"^":""}}{{assign var=value value=$val|default:0}}{{if $value > 0}}<a href="javascript:void(0)" onclick="Attend.Infowin.show('{{$category.categoryid}}', '{{$item.uniqueid}}', '{{$date.year}}', '{{$date.month}}')">{{$value}}</a>{{else}}{{$value}}{{/if}}{{/if}}{{/foreach}}</td>
                            {{/foreach}}
                            <td align="right"><a href="/app/attend/count/list?unid={{$item.uniqueid}}&date={{$date.year}}&month={{$date.month}}&back={{$currUrl}}">[{{$LANG.view}}]</a></td>
                        </tr>
                        {{foreachelse}}
                        <tr>
                            {{assign var="sum" value=$categories|@count}}
                            <td colspan="{{$sum+6}}" style="height:80px;text-align:center;">{{$LANG.search_null_or_null_data}}</td>
                        </tr>
                        {{/foreach}}
                    </tbody>
                </table>
            </div>
            <div class="gray" style="margin-top:10px;">注意：若考勤的IP地址和其它的IP地址不一致，则将出现红色感叹号以示警告</div>
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
    TOP.Frame.title('{{$LANG.attend_count}}');
    TOP.Frame.hash('m=app/attend/count/index{{foreach key=key item=val from=$query}}&{{$key}}={{$val}}{{/foreach}}');

    $('#year').val(parseInt('{{$date.year}}'));
    $('#month').val(parseInt('{{$date.month}}'));

    Attend.Count.init();
});
</script>
</html>