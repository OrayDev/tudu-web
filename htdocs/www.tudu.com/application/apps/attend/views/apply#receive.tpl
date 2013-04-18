<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.attend_review}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1021" type="text/javascript"></script>
</head>
<body>
{{assign var="currUrl" value=$smarty.server.REQUEST_URI|escape:'url'}}
{{include file="attend^tab.tpl" tab="checkin"}}
<div class="tab-panel-body">
    <div class="toolbar">
        <div class="toolbar_nav"><a href="/app/attend/apply/index">我的申请</a><a href="/app/attend/apply/receive" class="toolbar_nav_on">{{$LANG.attend_review}}</a></div>
    </div>
    <div class="readmailinfo">
        <div class="module" style="padding-bottom:5px;">
            {{strip}}
            <form method="get" action="?">
            <p>{{$LANG.name}}{{$LANG.cln}}<input type="text" class="input_text" name="keyword" value="{{$query.keyword}}" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            {{$LANG.search_date}}{{$LANG.cln}}<select name="year">
            <option value="2012"{{if $query.year == 2012}} selected="selected"{{/if}}>2012{{$LANG.year}}</option>
            <option value="2013"{{if $query.year == 2013}} selected="selected"{{/if}}>2013{{$LANG.year}}</option>
            <option value="2014"{{if $query.year == 2014}} selected="selected"{{/if}}>2014{{$LANG.year}}</option>
            <option value="2015"{{if $query.year == 2015}} selected="selected"{{/if}}>2015{{$LANG.year}}</option>
            <option value="2016"{{if $query.year == 2016}} selected="selected"{{/if}}>2016{{$LANG.year}}</option>
            <option value="2017"{{if $query.year == 2017}} selected="selected"{{/if}}>2017{{$LANG.year}}</option>
            <option value="2018"{{if $query.year == 2018}} selected="selected"{{/if}}>2018{{$LANG.year}}</option>
            <option value="2019"{{if $query.year == 2019}} selected="selected"{{/if}}>2019{{$LANG.year}}</option>
            <option value="2020"{{if $query.year == 2020}} selected="selected"{{/if}}>2020{{$LANG.year}}</option>
            <option value="2021"{{if $query.year == 2021}} selected="selected"{{/if}}>2021{{$LANG.year}}</option>
            <option value="2022"{{if $query.year == 2022}} selected="selected"{{/if}}>2022{{$LANG.year}}</option>
            </select>&nbsp;
            <select name="month">
            <option value="">{{$LANG.month}}</option>
            <option value="1"{{if $query.month == 1}} selected="selected"{{/if}}>1{{$LANG.month}}</option>
            <option value="2"{{if $query.month == 2}} selected="selected"{{/if}}>2{{$LANG.month}}</option>
            <option value="3"{{if $query.month == 3}} selected="selected"{{/if}}>3{{$LANG.month}}</option>
            <option value="4"{{if $query.month == 4}} selected="selected"{{/if}}>4{{$LANG.month}}</option>
            <option value="5"{{if $query.month == 5}} selected="selected"{{/if}}>5{{$LANG.month}}</option>
            <option value="6"{{if $query.month == 6}} selected="selected"{{/if}}>6{{$LANG.month}}</option>
            <option value="7"{{if $query.month == 7}} selected="selected"{{/if}}>7{{$LANG.month}}</option>
            <option value="8"{{if $query.month == 8}} selected="selected"{{/if}}>8{{$LANG.month}}</option>
            <option value="9"{{if $query.month == 9}} selected="selected"{{/if}}>9{{$LANG.month}}</option>
            <option value="10"{{if $query.month == 10}} selected="selected"{{/if}}>10{{$LANG.month}}</option>
            <option value="11"{{if $query.month == 11}} selected="selected"{{/if}}>11{{$LANG.month}}</option>
            <option value="12"{{if $query.month == 12}} selected="selected"{{/if}}>12{{$LANG.month}}</option>
            </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$LANG.type}}{{$LANG.cln}}<select name="status">
            <option value=""{{if $query.status === null}} selected="selected"{{/if}}>{{$LANG.all}}</option>
            <option value="0"{{if $query.status == '0'}} selected="selected"{{/if}}>{{$LANG.status_0}}</option>
            <option value="1"{{if $query.status == 1}} selected="selected"{{/if}}>{{$LANG.status_1}}</option>
            <option value="2"{{if $query.status == 2}} selected="selected"{{/if}}>{{$LANG.status_2}}</option>
            <option value="3"{{if $query.status == 3}} selected="selected"{{/if}}>{{$LANG.status_3}}</option>
            <option value="4"{{if $query.status == 4}} selected="selected"{{/if}}>{{$LANG.status_4}}</option>
            </select>&nbsp;<select name="categoryid">
            <option{{if !$params.categoryid}} selected="selected"{{/if}} value="">{{$LANG.all_type}}</option>
            {{foreach from=$categories item=category}}
            <option value="{{$category.categoryid}}"{{if $query.categoryid == $category.categoryid}} selected="selected"{{/if}}>{{$category.categoryname}}</option>
            {{/foreach}}
            </select>&nbsp;&nbsp;<input type="submit" value="{{$LANG.btn_search}}" class="btn" />
            </p>
            </form>
            {{/strip}}
            <div class="table_list_wrap" style="margin-top:20px;">
                <div class="table_list_title"><strong>{{$LANG.attend_review}}({{$pageinfo.recordcount|intval}})</strong></div>
                <table width="100%" cellspacing="0" cellpadding="5" align="center" class="table_list">
                    <colgroup>
                        <col />
                        <col />
                        <col />
                        <col width="150" />
                        <col width="150" />
                        <col width="90" />
                        <col width="130" />
                        <col width="60" />
                    </colgroup>
                    <tr>
                        <th align="left">{{$LANG.name}}</th>
                        <th align="left">{{$LANG.dept}}</th>
                        <th align="left">{{$LANG.apply_type}}</th>
                        <th align="left">{{$LANG.begin_time}}</th>
                        <th align="left">{{$LANG.finish_time}}</th>
                        <th align="left">{{$LANG.all_time}}</th>
                        <th align="left">{{$LANG.current_status}}</th>
                        <th align="left">{{$LANG.operate}}</th>
                    </tr>
                    <tbody id="apply-list">
                    {{foreach from=$applies item=item}}
                    <tr>
                        <td>{{if !$item.isallday && $item.categoryid != '^checkin'}}{{assign var=applytime value=$item.endtime-$item.starttime}}{{assign var=applyhour value=$applytime/3600}}{{assign var=time value=$applyhour-$item.period}}{{if $time < -0.25 || $time > 0.25}}<strong class="red">!</strong>{{/if}}{{/if}} {{$item.truename}}</td>
                        <td>{{$item.deptname|default:'-'}}</td>
                        <td align="left">{{$item.categoryname}}</td>
                        <td align="left">{{$item.starttime|date_format:'%Y-%m-%d %H:%M'|default:'-'}}</td>
                        <td align="left">{{$item.endtime|date_format:'%Y-%m-%d %H:%M'|default:'-'}}</td>
                        <td>{{if $item.period}}{{$item.period}}{{$LANG.hour}}{{else}}-{{/if}}</td>
                        <td align="left">
                        {{if $item.reviewstatus === 0 && $item.status != 4}}
                        {{$LANG.status_5_1}}
                        {{else}}
                        {{assign var=langkey value='status_'|cat:$item.status}}
                        {{$LANG[$langkey]}}
                        {{/if}}
                        </td>
                        <td><a href="/app/attend/apply/view?tid={{$item.tuduid}}&back={{$currUrl}}">[{{$LANG.view}}]</a></td>
                    </tr>
                    {{foreachelse}}
                    <tr>
                    <td colspan="8" style="padding: 20px 0;text-align: center">{{$LANG.null_attend_apply}}</td>
                    </tr>
                    {{/foreach}}
                    </tbody>
                </table>
            </div>
            <div class="gray" style="margin-top:10px;">注意：若申请的考勤和实际的考勤不一致，则将出现红色叹号以示警告</div>
        </div>
    </div>
    <div class="toolbar">
        <div style="height:28px;"></div>
        {{pagenavigator url=$pageinfo.url query=$pageinfo.query currpage=$pageinfo.currpage recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl"}}
    </div>
</div>
<script type="text/javascript">
<!--
$(function(){
    TOP.Label.focusLabel();
    TOP.Frame.title('{{$LANG.attend_review}}');
    TOP.Frame.hash('#m=app/attend/apply/receive{{foreach key=key item=val from=$query}}&{{$key}}={{$val}}{{/foreach}}');

    $('#apply-list tr').mousemove(function(){
        $(this).addClass("current");
    }).mouseout(function(){
        $(this).removeClass("current");
    });
});
-->
</script>
</body>
</html>