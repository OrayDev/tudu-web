<table width="100%" border="0" cellspacing="0" cellpadding="2" id="log-table" class="log_table">
  <thead>
    <tr>
      <th width="200">{{$LANG.log_time}}</th>
      <th width="120">{{$LANG.log_operator}}</th>
      <th align="left">{{$LANG.log_detail}}</th>
    </tr>
  </thead>
  <tbody>
  {{foreach name="log" from=$logs item=log}}
  {{format_log_detail detail=$log.detail action=$log.action assign="logcontent"}}
    <tr>
      <td align="center">{{$log.logtime|date_time_format:'%Y-%m-%d %H:%M:%S'}}</td>
      <td align="center">{{$log.truename}}</td>
      <td nowrap="nowrap" class="log_content" title="{{$logcontent}}">{{$logcontent}}</td>
    </tr>
  {{/foreach}}
  </tbody>
</table>