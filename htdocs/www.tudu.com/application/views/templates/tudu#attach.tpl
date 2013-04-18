<table width="100%" border="0" cellspacing="0" cellpadding="2" id="attach-table" class="log_table">
  <thead>
    <tr>
      <th align="left">{{$LANG.attach_name}}</th>
      <th align="left" width="120">{{$LANG.attach_poster}}</th>
      <th align="left" width="200">{{$LANG.upload}}{{$LANG.time}}</th>
    </tr>
  </thead>
  <tbody>
  {{foreach from=$attachs item=attach}}
  {{assign var=createtime value=$attach.createtime|date_time_format:$user.option.dateformat}}
  {{if $attach.isattach}}
    <tr>
      <td align="left"><span class="icon ficon {{$attach.filename|file_ext}}"></span><a href="{{$attach.fileid|tudu_get_attachment_url}}">{{$attach.filename}}</a>&nbsp;({{$attach.size|format_file_size}})</td>
      <td align="left">{{$attach.poster}}</td>
      <td nowrap="nowrap" class="log_content">{{$createtime}}</td>
    </tr>
  {{/if}}
  {{foreachelse}}
    <tr>
      <td colspan="3"><div style="padding:20px 215px;text-align:center;">{{$LANG.tudu_attach_list_null}}</div></td>
    </tr>
  {{/foreach}}
  </tbody>
</table>