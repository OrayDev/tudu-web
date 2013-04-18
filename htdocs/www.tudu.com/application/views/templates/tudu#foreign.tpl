<div id="tudu-foreigners" class="grid_list_wrap grid_list_group" style="padding:5px;">
    <div id="addforeign" style="padding:5px;"><button class="btn" id="addbtn" type="button">{{$LANG.add_foreign}}</button></div>
    <form id="foreignform" action="/tudu/foreign.add" method="post">
    <table id="foreignedit" cellspacing="2" cellpadding="2" border="0" style="display:none" width="100%">
    {{if $tudu.type == 'meeting'}}
      <tr>
        <td width="55" style="padding-left:5px">{{if $tudu.type == 'meeting'}}{{$LANG.attendee}}{{else}}{{$LANG.column_accepter}}{{/if}}{{$LANG.cln}}</td>
        <td width="670"><input type="text" name="i-to" id="i-to" readonly="readonly" /><input type="hidden" name="to" id="to" value="" /></td>
        <td></td>
      </tr>
    {{/if}}
    {{if $tudu.type != 'meeting'}}
      <tr>
        <td width="55" style="padding-left:5px">{{if $tudu.type == 'task'}}{{$LANG.column_cc}}{{$LANG.cln}}{{else}}{{$LANG.title_accept_user}}{{/if}}</td>
        <td width="670"><input type="text" name="i-cc" id="i-cc" readonly="readonly" /><input type="hidden" name="cc" id="cc" value="" /></td>
        <td></td>
      </tr>
    {{/if}}
      <tr>
        <td width="55" style="padding-left:5px"></td>
        <td width="670"><input class="btn" type="submit" value="{{$LANG.submit}}" /><input class="btn" type="button" id="canceladd" value="{{$LANG.cancel}}" /></td>
        <td></td>
      </tr>
    </table>
    </form>
{{if ($tudu.type == 'task' || $tudu.type == 'meeting') && !$tudu.istudugroup}}
    <div class="grid_list_title" style="line-height:24px">
        <h3>{{if $tudu.type == 'meeting'}}{{$LANG.attendee}}{{else}}{{$LANG.column_accepter}}{{/if}}</h3>
    </div>
    <div class="grid_list_group_ct accepter_group" style="height:110px;overflow:auto;">
    <table cellspacing="0" class="grid_thead" style="table-layout:fixed">
      <tr>
        <td width="20%" style="line-height:20px"><span class="space">{{$LANG.contact}}</span></td>
        <td class="title_line" style="line-height:20px"><span class="space">{{$LANG.link}}</span></td>
        {{if $tudu.isauth}}<td width="80" align="center" class="title_line" style="line-height:20px"><span class="space">{{$LANG.auth_code}}</span></td>{{/if}}
        <td width="100" class="title_line" style="line-height:20px" align="center">{{$LANG.operation}}</td>
      </tr>
    </table>
    {{foreach from=$foreigner.to item=item}}
        <table id="u-{{$uniqueid}}" class="accepter_table" cellspacing="0" style="table-layout:fixed">
        <tbody>
        <tr>
        <td width="20%" title="{{$item.info.1}}{{if $item.info.0}}&lt;{{$item.info.0}}&gt;{{/if}}">{{$item.info.1}}</td>
        <td style="overflow:hidden;white-space:nowrap;"><a href="javascript:void(0)" onclick="TOP.copyToClipboard('https://{{$tudu.orgid}}.tudu.com/foreign/tudu?tid={{$tudu.tuduid}}&fid={{$item.uniqueid}}&ts={{$user.tsid}}')" title="https://{{$tudu.orgid}}.tudu.com/foreign/tudu?tid={{$tudu.tuduid}}&fid={{$item.uniqueid}}&ts={{$user.tsid}}">https://{{$tudu.orgid}}.tudu.com/foreign/tudu?tid={{$tudu.tuduid}}&fid={{$item.uniqueid}}&ts={{$user.tsid}}</a></td>
        {{if $tudu.isauth}}<td width="80" align="center">{{$item.authcode}}</td>{{/if}}
        <td width="100" align="center">[<a href="javascript:void(0)" onclick="TOP.copyToClipboard('https://{{$tudu.orgid}}.tudu.com/foreign/tudu?tid={{$tudu.tuduid}}&fid={{$item.uniqueid}}&ts={{$user.tsid}}')">{{$LANG.copy_link}}</a>]{{if $tudu.type != 'task'}}[<a href="javascript:void(0)" name="delete" id="del-{{$item.uniqueid}}">{{$LANG.delete}}</a>]{{/if}}</td>
        </tr>
        </tbody>
        </table>
    {{foreachelse}}
    <div style="height:30px;text-align:center;line-height:30px">{{$LANG.had_not_foreign}}</div>
    {{/foreach}}
    </div>
{{/if}}
{{if $tudu.type != 'meeting'}}
    <div class="grid_list_title" style="line-height:24px">
        <h3>{{if $tudu.type == 'task'}}{{$LANG.column_cc}}{{else}}{{$LANG.title_accept_user}}{{/if}}</h3>
    </div>
    <div class="grid_list_group_ct accepter_group" style="height:110px;overflow:auto;">
    <table cellspacing="0" class="grid_thead" style="table-layout:fixed">
      <tr>
        <td width="20%" style="line-height:20px"><span class="space">{{$LANG.contact}}</span></td>
        <td class="title_line" style="line-height:20px"><span class="space">{{$LANG.link}}</span></td>
        {{if $tudu.isauth}}<td width="80" align="center" class="title_line" style="line-height:20px"><span class="space">{{$LANG.auth_code}}</span></td>{{/if}}
        <td width="100" class="title_line" style="line-height:20px" align="center"><span class="space">{{$LANG.operation}}</span></td>
      </tr>
    </table>
    {{foreach from=$foreigner.cc item=item}}
        <table id="u-{{$uniqueid}}" class="accepter_table" cellspacing="0" style="table-layout:fixed">
        <tbody>
        <tr>
        <td width="20%" title="{{$item.info.1}}{{if $item.info.0}}&lt;{{$item.info.0}}&gt;{{/if}}">{{$item.info.1}}</td>
        <td style="overflow:hidden;white-space:nowrap;"><a href="javascript:void(0)" onclick="TOP.copyToClipboard('https://{{$tudu.orgid}}.tudu.com/foreign/tudu?tid={{$tudu.tuduid}}&fid={{$item.uniqueid}}&ts={{$user.tsid}}')" title="https://{{$tudu.orgid}}.tudu.com/foreign/tudu?tid={{$tudu.tuduid}}&fid={{$item.uniqueid}}&ts={{$user.tsid}}">https://{{$tudu.orgid}}.tudu.com/foreign/tudu?tid={{$tudu.tuduid}}&fid={{$item.uniqueid}}&ts={{$user.tsid}}</a></td>
        {{if $tudu.isauth}}<td width="80" align="center">{{$item.authcode}}</td>{{/if}}
        <td width="100" align="center">[<a href="javascript:void(0)" onclick="TOP.copyToClipboard('https://{{$tudu.orgid}}.tudu.com/foreign/tudu?tid={{$tudu.tuduid}}&fid={{$item.uniqueid}}&ts={{$user.tsid}}')">{{$LANG.copy_link}}</a>][<a href="javascript:void(0)" name="delete" id="del-{{$item.uniqueid}}">{{$LANG.delete}}</a>]</td>
        </tr>
        </tbody>
        </table>
    {{foreachelse}}
    <div style="height:30px;text-align:center;line-height:30px">{{$LANG.had_not_foreign}}</div>
    {{/foreach}}
    </div>
{{/if}}
</div>