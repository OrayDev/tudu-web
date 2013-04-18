<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js" type="text/javascript"></script>
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
            <input name="unid" id="unid" value="{{$query.unid}}" type="hidden" />
            <input name="nunid" id="nunid" value="{{if $query.keywords}}{{$query.unid}}{{/if}}" type="hidden" />
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
                </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$LANG.type}}{{$LANG.cln}}
                <select id="categoryid" name="categoryid">
                    <option value="">{{$LANG.all_type}}</option>
                    <option value="^late"{{if '^late' == $query.categoryid}} selected="selected"{{/if}}>{{$LANG.category_late}}</option>
                    <option value="^leave"{{if '^leave' == $query.categoryid}} selected="selected"{{/if}}>{{$LANG.category_leave}}</option>
                    <option value="^unwork"{{if '^unwork' == $query.categoryid}} selected="selected"{{/if}}>{{$LANG.category_unwork}}</option>
                    <option value="^uncheckin"{{if '^uncheckin' == $query.categoryid}} selected="selected"{{/if}}>{{$LANG.un_checkin}}</option>
                    <option value="^uncheckout"{{if '^uncheckout' == $query.categoryid}} selected="selected"{{/if}}>{{$LANG.un_checkout}}</option>
                    {{foreach item=category from=$categories}}
                    <option value="{{$category.categoryid}}"{{if $category.categoryid == $query.categoryid}} selected="selected"{{/if}}>{{$category.categoryname}}</option>
                    {{/foreach}}
                </select>&nbsp;&nbsp;
                <input name="search" type="button" value="{{$LANG.btn_search}}" class="btn" />
            </p>
            <div class="table_list_wrap" style="margin-top:10px;">
                <div class="table_list_title">
                    <strong>{{$userinfo.truename}}</strong> | <a href="{{$query.back|default:'/app/attend/count/index'}}">{{$LANG.back}}</a>&nbsp;&nbsp;&nbsp;&nbsp;{{$LANG.count}}{{$LANG.cln}}{{$date.year}}{{$LANG.year}}{{$date.month}}{{$LANG.month}} {{$userinfo.truename}} {{$LANG.total}}{{foreach item=item key=key from=$count name=count}}{{if $key == 'late' || $key == 'leave' || $key == 'unwork'}}{{assign var=category value="category_"|cat:$key}}{{$LANG[$category]}}&nbsp;{{if $item > 0}}<a href="javascript:void(0)" onclick="Attend.Infowin.show('{{$key}}', '{{$query.unid}}', '{{$date.year}}', '{{$date.month}}')">{{$item}}</a>{{else}}{{$item}}{{/if}}&nbsp;{{$LANG.times}}{{else}}{{$item.name}}&nbsp;{{if $item.total > 0}}<a href="javascript:void(0)" onclick="Attend.Infowin.show('{{$key}}', '{{$query.unid}}', '{{$date.year}}', '{{$date.month}}')">{{$item.total}}</a>{{else}}{{$item.total}}{{/if}}&nbsp;{{if $key == '^checkin'}}{{$LANG.times}}{{else}}{{$LANG.hour}}{{/if}}{{/if}}{{if $smarty.foreach.count.index + 1 < count($count)}},&nbsp;{{/if}}{{/foreach}}
                    <div class="table_list_title_ext"><a href="/app/attend/count/export?type=date{{foreach key=key item=val from=$query}}&{{$key}}={{$val}}{{/foreach}}&url={{$currUrl}}">{{$LANG.export_data}}</a></div>
                </div>
                <table width="100%" cellspacing="0" cellpadding="5" align="center" class="table_list">
                    <colgroup>
                        <col />
                        <col />
                        <col width="145" />
                        <col />
                        <col />
                        <col />
                        <col width="60" />
                        <col width="60" />
                        <col width="60" />
                        <col width="60" />
                    </colgroup>
                    <tr>
                        <th align="left">{{$LANG.name}}</th>
                        <th align="left">{{$LANG.dept}}</th>
                        <th align="left">{{$LANG.date}}</th>
                        <th align="left">{{$LANG.checkin}}</th>
                        <th align="left">{{$LANG.checkout}}</th>
                        <th align="left">{{$LANG.work_time}}</th>
                        <th align="left">{{$LANG.category_late}}</th>
                        <th align="left">{{$LANG.category_leave}}</th>
                        <th align="left">{{$LANG.category_unwork}}</th>
                        <th align="right">{{$LANG.operate}}</th>
                    </tr>
                    <tbody id="count-list">
                        {{foreach item=item from=$records}}
                        <tr>
                            <td align="left">{{if $item.isabnormalip}}<strong class="red">!</strong>{{/if}}{{$item.truename}}</td>
                            <td align="left">{{$item.deptname|default:'-'}}</td>
                            <td align="left">{{$item.date|date_time_format:"%Y-%m-%d"}}{{assign var="weekday" value=$item.date|date_format:'%w'}}{{assign var=week value="week_"|cat:$weekday}}({{$LANG[$week]}})</td>
                            <td align="left">{{$item.checkintime|date_time_format:"%H:%M"|default:'-'}}{{if $item.checkintime}}&nbsp;({{$item.checkinip}}&nbsp;{{$item.checkinaddress}}){{/if}}</td>
                            <td align="left">{{$item.checkouttime|date_time_format:"%H:%M"|default:'-'}}{{if $item.checkouttime}}&nbsp;({{$item.checkoutip}}&nbsp;{{$item.checkoutaddress}}){{/if}}</td>
                            <td align="left">{{$item.worktime|format_time}}</td>
                            <td align="left"><input type="checkbox" disabled="disabled"{{if $item.islate}} checked="checked"{{/if}} /></td>
                            <td align="left"><input type="checkbox" disabled="disabled"{{if $item.isleave}} checked="checked"{{/if}} /></td>
                            <td align="left"><input type="checkbox" disabled="disabled"{{if $item.iswork}} checked="checked"{{/if}} /></td>
                            <td align="right"><a href="javascript:void(0)" onclick="Attend.Count.showCheckinInfo('{{$item.uniqueid}}', '{{$item.date}}')">[{{$LANG.view}}]</a></td>
                        </tr>
                        {{foreachelse}}
                        <tr>
                            <td colspan="10" style="text-align:center;padding:35px 0"><strong>{{$userinfo.truename}}</strong> {{$LANG.null_attend_data_list}}</td>
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
    TOP.Frame.hash('m=app/attend/count/list{{foreach key=key item=val from=$query}}&{{$key}}={{$val}}{{/foreach}}');

    $('#year').val(parseInt('{{$date.year}}'));
    $('#month').val(parseInt('{{$date.month}}'));
    var back = '{{$query.back|default:'/app/attend/count/index'|escape:'url'}}';

    Attend.Count.setDepts([{{foreach from=$deptids name=dept item=item}}'{{$item}}'{{if $smarty.foreach.dept.index != count($deptids) - 1}},{{/if}}{{/foreach}}]);
    Attend.Count.initList(back);
});
</script>
</html>