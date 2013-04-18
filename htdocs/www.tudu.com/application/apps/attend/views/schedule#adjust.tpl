<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>{{$LANG.adjust_workday}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1019" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/attend/adjust.js?1001" type="text/javascript"></script>
</head>

<body style="padding:0 5px 5px">
  {{include file="attend^tab.tpl" tab="schedule"}}
  <div class="tab-panel-body">
    {{include file="schedule^toolbar.tpl" tab="adjust"}}
    <div class="readmailinfo">
        <div class="module">
            <p><input name="add" type="button" class="btn" value="{{$LANG.new_subject}}" onclick="location='/app/attend/schedule/modifyadjust'" /></p>
            <div class="table_list_wrap" style="margin-top:20px;">
                <div class="table_list_title"><strong>{{$LANG.adjust_workday}}</strong></div>
                <table width="100%" cellspacing="0" cellpadding="5" align="center" class="table_list">
                    <colgroup>
                        <col />
                        <col width="250" />
                        <col width="100" />
                        <col width="80" />
                    </colgroup>
                    <tr>
                        <th align="left">{{$LANG.adjust_subject}}</th>
                        <th align="left">{{$LANG.adjust_time}}</th>
                        <th align="left">{{$LANG.adjust_to}}</th>
                        <th align="left">{{$LANG.operate}}</th>
                    </tr>
                    <tbody id="adjust-list">
                        {{foreach item=item from=$adjusts}}
                        <tr id="{{$flow.flowid}}">
                            <td align="left">{{$item.subject|escape:'html'}}</td>
                            <td>{{$item.starttime|date_format:'%Y-%m-%d'}}到{{$item.endtime|date_format:'%Y-%m-%d'}}</td>
                            <td>{{if $item.type == 0}}{{$LANG.not_work_day}}{{else}}{{$LANG.work_day}}{{/if}}</td>
                            <td><a href="/app/attend/schedule/modifyadjust?adjustid={{$item.adjustid}}">[{{$LANG.modify}}]</a>&nbsp;<a href="javascript:void(0)" onclick="Attend.Adjust.deleteAdjust('{{$item.adjustid}}')">[{{$LANG.delete}}]</a></td>
                        </tr>
                        {{foreachelse}}
                        <tr>
                            <td colspan="4" style="text-align:center;padding:35px 0">{{$LANG.null_adjust_list}}</td>
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
$(function(){
    TOP.Label.focusLabel('attend');
    TOP.Frame.title('{{$LANG.adjust_workday}}');
    TOP.Frame.hash('m=app/attend/schedule/adjust');

    Attend.Adjust.setLang({confirm_delete_adjust: '{{$LANG.confirm_delete_adjust}}'});
    
    Attend.Adjust.initList();
});
</script>
</html>