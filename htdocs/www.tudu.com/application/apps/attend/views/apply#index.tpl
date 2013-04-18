<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.attend_apply}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1021" type="text/javascript"></script>
<style type="text/css">
.tb-sep{
    display:inline-block;
    border-left:1px solid #9D9D9D;
    border-right:1px solid #FFF;
    height:16px;
    width:0px;
    background-color:#CCC;
    margin:0 15px;
    line-height:normal;
    vertical-align:middle;
}
</style>
</head>
<body>
{{assign var="currUrl" value=$smarty.server.REQUEST_URI|escape:'url'}}
{{include file="attend^tab.tpl" tab="checkin"}}
<div class="tab-panel-body">
    <div class="toolbar">
        <div class="toolbar_nav"><a href="/app/attend/apply/index" class="toolbar_nav_on">我的申请</a><a href="/app/attend/apply/receive">{{$LANG.attend_review}}</a></div>
    </div>
    <div class="readmailinfo">
        <div class="module">
            {{strip}}
            <form method="get" action="?">
            <p>{{$LANG.search_date}}{{$LANG.cln}}<select name="year">
            <option value="2012"{{if $pageinfo.query.year == 2012}} selected="selected"{{/if}}>2012{{$LANG.year}}</option>
            <option value="2013"{{if $pageinfo.query.year == 2013}} selected="selected"{{/if}}>2013{{$LANG.year}}</option>
            <option value="2014"{{if $pageinfo.query.year == 2014}} selected="selected"{{/if}}>2014{{$LANG.year}}</option>
            <option value="2015"{{if $pageinfo.query.year == 2015}} selected="selected"{{/if}}>2015{{$LANG.year}}</option>
            <option value="2016"{{if $pageinfo.query.year == 2016}} selected="selected"{{/if}}>2016{{$LANG.year}}</option>
            <option value="2017"{{if $pageinfo.query.year == 2017}} selected="selected"{{/if}}>2017{{$LANG.year}}</option>
            <option value="2018"{{if $pageinfo.query.year == 2018}} selected="selected"{{/if}}>2018{{$LANG.year}}</option>
            <option value="2019"{{if $pageinfo.query.year == 2019}} selected="selected"{{/if}}>2019{{$LANG.year}}</option>
            <option value="2020"{{if $pageinfo.query.year == 2020}} selected="selected"{{/if}}>2020{{$LANG.year}}</option>
            <option value="2021"{{if $pageinfo.query.year == 2021}} selected="selected"{{/if}}>2021{{$LANG.year}}</option>
            <option value="2022"{{if $pageinfo.query.year == 2022}} selected="selected"{{/if}}>2022{{$LANG.year}}</option>
            </select>&nbsp;
            <select name="month">
            <option value="">{{$LANG.month}}</option>
            <option value="1"{{if $pageinfo.query.month == 1}} selected="selected"{{/if}}>1{{$LANG.month}}</option>
            <option value="2"{{if $pageinfo.query.month == 2}} selected="selected"{{/if}}>2{{$LANG.month}}</option>
            <option value="3"{{if $pageinfo.query.month == 3}} selected="selected"{{/if}}>3{{$LANG.month}}</option>
            <option value="4"{{if $pageinfo.query.month == 4}} selected="selected"{{/if}}>4{{$LANG.month}}</option>
            <option value="5"{{if $pageinfo.query.month == 5}} selected="selected"{{/if}}>5{{$LANG.month}}</option>
            <option value="6"{{if $pageinfo.query.month == 6}} selected="selected"{{/if}}>6{{$LANG.month}}</option>
            <option value="7"{{if $pageinfo.query.month == 7}} selected="selected"{{/if}}>7{{$LANG.month}}</option>
            <option value="8"{{if $pageinfo.query.month == 8}} selected="selected"{{/if}}>8{{$LANG.month}}</option>
            <option value="9"{{if $pageinfo.query.month == 9}} selected="selected"{{/if}}>9{{$LANG.month}}</option>
            <option value="10"{{if $pageinfo.query.month == 10}} selected="selected"{{/if}}>10{{$LANG.month}}</option>
            <option value="11"{{if $pageinfo.query.month == 11}} selected="selected"{{/if}}>11{{$LANG.month}}</option>
            <option value="12"{{if $pageinfo.query.month == 12}} selected="selected"{{/if}}>12{{$LANG.month}}</option>
            </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$LANG.type}}{{$LANG.cln}}<select name="status">
            <option value=""{{if $pageinfo.query.status === null}} selected="selected"{{/if}}>{{$LANG.all}}</option>
            <option value="0"{{if $pageinfo.query.status == '0'}} selected="selected"{{/if}}>{{$LANG.status_0}}</option>
            <option value="1"{{if $pageinfo.query.status == 1}} selected="selected"{{/if}}>{{$LANG.status_1}}</option>
            <option value="2"{{if $pageinfo.query.status == 2}} selected="selected"{{/if}}>{{$LANG.status_2}}</option>
            <option value="3"{{if $pageinfo.query.status == 3}} selected="selected"{{/if}}>{{$LANG.status_3}}</option>
            <option value="4"{{if $pageinfo.query.status == 4}} selected="selected"{{/if}}>{{$LANG.status_4}}</option>
            </select>&nbsp;<select name="categoryid">
            <option{{if !$params.categoryid}} selected="selected"{{/if}} value="">{{$LANG.all_type}}</option>
            {{foreach from=$categories item=category}}
            <option value="{{$category.categoryid}}"{{if $pageinfo.query.categoryid == $category.categoryid}} selected="selected"{{/if}}>{{$category.categoryname}}</option>
            {{/foreach}}
            </select>&nbsp;&nbsp;<input type="submit" value="{{$LANG.btn_search}}" class="btn" />
            <span class="tb-sep"></span>
            <input type="button" class="btn" value="新增考勤申请" onclick="location='/app/attend/apply/modify'" />
            </p>
            </form>
            {{/strip}}
            <div class="table_list_wrap" style="margin-top:20px;">
                <div class="table_list_title"><strong>{{$LANG.attend_apply}}({{$pageinfo.recordcount|intval}})</strong></div>
                <table width="100%" cellspacing="0" cellpadding="5" align="center" class="table_list">
                    <colgroup>
                        <col />
                        <col />
                        <col />
                        <col width="130" />
                        <col width="130" />
                        <col width="120" />
                    </colgroup>
                    <tr>
                        <th align="left">{{$LANG.apply_type}} </th>
                        <th align="left">{{$LANG.begin_time}}</th>
                        <th align="left">{{$LANG.finish_time}}</th>
                        <th align="left">{{$LANG.all_time}}</th>
                        <th align="left">{{$LANG.current_status}}</th>
                        <th align="left">{{$LANG.operate}}</th>
                    </tr>
                    <tbody id="apply-list">
                    {{foreach from=$applies item=item}}
                    <tr>
                        <td align="left">{{$item.categoryname}}</td>
                        <td align="left">{{$item.starttime|date_format:'%Y-%m-%d %H:%M'|default:'-'}}</td>
                        <td align="left">{{$item.endtime|date_format:'%Y-%m-%d %H:%M'|default:'-'}}</td>
                        <td>{{if $item.period}}{{$item.period}}{{$LANG.hour}}{{else}}-{{/if}}</td>
                        <td align="left">{{if $item.status == 0}}{{$LANG.status_0}}{{elseif $item.status == 1}}{{$LANG.status_1}}{{elseif $item.status == 2}}{{$LANG.status_2}}{{elseif $item.status == 3}}{{$LANG.status_3}}{{else}}{{$LANG.status_4}}{{/if}}</td>
                        <td><a href="/app/attend/apply/view?tid={{$item.tuduid}}&back={{$currUrl}}">[{{$LANG.view}}]</a>{{if $item.status < 2}} <a href="javascript:void(0)" onclick="cancelApply('{{$item.applyid}}')">[{{$LANG.cancel_apply}}]</a>{{/if}}</td>
                    </tr>
                    {{foreachelse}}
                    <tr>
                    <td colspan="6" style="padding: 20px 0;text-align: center">{{$LANG.null_attend_apply}}</td>
                    </tr>
                    {{/foreach}}
                    </tbody>
                </table>
            </div>
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
    TOP.Frame.title('{{$LANG.attend_apply}}');
    TOP.Frame.hash('#m=app/attend/apply/index{{foreach key=key item=val from=$pageinfo.query}}&{{$key}}={{$val}}{{/foreach}}');

    $('#apply-list tr').mousemove(function(){
        $(this).addClass("current");
    }).mouseout(function(){
        $(this).removeClass("current");
    });
});

function cancelApply(applyid) {
	if (!confirm('{{$LANG.confirm_cancel_apply}}')) {
		return ;
	}

	$.ajax({
		type: 'POST',
		dataType: 'json',
		data: {applyid: applyid},
		url: '/app/attend/apply/cancel',
		success: function(ret) {
			TOP.showMessage(ret.message, null, ret.success ? 'success' : null);
			if (ret.success) {
				location.assign(location.href);
			}
		},
		error: function(res) {

		}
	});
}
-->
</script>
</body>
</html>