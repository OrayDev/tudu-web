<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.login_log}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>

</head>
<body>
    <div class="position">
       <p><strong class="title">{{$LANG.login_log}}</strong></p>
    </div>
	{{include file="setting^tab.tpl" tab="log"}}
	<div class="tab-panel-body">
        <div class="settingbox">
            <div class="settingdiv">
            <table width="100%" cellspacing="0" cellpadding="5" align="center" class="set_tag" style="margin-bottom:10px">
              <tr>
                <td class="file_title" colspan="3">{{$LANG.one_week_login_log}}</td>
              </tr>
              <tr class="addrtitle">
                <td class="settingtd">{{$LANG.login_time}}</td>
                <td class="settingtd" width="90" align="center">{{$LANG.login_source_ip}}</td>
                <td class="settingtd" align="right">{{$LANG.login_ip_address}}</td>
              </tr>
              <tbody id="log-list">
              {{foreach item=log from=$logs}}
              <tr id="{{$log.loginlogid}}">
                <td class="settingtd">{{$log.createtime|date_format:'%Y-%m-%d %H:%M'}}</td>
                <td class="settingtd" align="left">{{$log.ip|default:$LANG.unknow_ip}}</td>
                <td class="settingtd" align="right">{{$log.local|default:$LANG.unknow_ip_address}}</td>
              </tr>
              {{foreachelse}}
              <tr>
                <td class="settingtd" colspan="3" style="text-align:center;padding:35px 0">{{$LANG.login_log}}</td>
              </tr>
              {{/foreach}}
              </tbody>
            </table>
            <div class="fr" style="margin:0 ">{{pagenavigator url=$pageinfo.url query=$pageinfo.query currpage=$pageinfo.currpage recordcount=$pageinfo.recordcount pagecount=$pageinfo.pagecount template="^pagenav.tpl" numcount=5}}</div>
            </div>
        </div>
    </div>
    <div class="toolbar">
    	<div style="height:24px;"></div>
    </div>
<script type="text/javascript">
<!--
$(function(){
	TOP.Label.focusLabel('');
	TOP.Frame.title('{{$LANG.login_log}}');
	TOP.Frame.hash('m=setting/log&page={{$pageinfo.currpage}}');

	if (parseInt({{$pageinfo.recordcount}}) > 0) {
    	$('#log-list tr').mousemove(function(){
    		$(this).addClass("current");
        }).mouseout(function(){
            $(this).removeClass("current");
        });
	}
});
-->
</script>
</body>
</html>