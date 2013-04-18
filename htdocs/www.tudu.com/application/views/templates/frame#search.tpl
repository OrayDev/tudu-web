<div id="searchform">
<table width="100%" border="0" cellpadding="2" cellspacing="4">
    <tr>
        <th align="right" width="70">{{$LANG.title_keyword}}</th>
        <td><input type="text" name="keyword" id="w_keyword" class="input_text" style="width:150px;" /></td>
        <th align="right" width="70">{{$LANG.title_sender}}</th>
        <td><input type="text" class="input_text" style="width:150px;" name="from" id="inputfrom" /></td>
    </tr>
    <tr>
        <th align="right" width="70">{{$LANG.status}}{{$LANG.cln}}</th>
        <td><select name="status" style="width:160px">
        <option value="">{{$LANG.unassigned}}</option>
        <option value="0">{{$LANG.tudu_status_0}}</option>
        <option value="1">{{$LANG.tudu_status_1}}</option>
        <option value="2">{{$LANG.tudu_status_2}}</option>
        <option value="3">{{$LANG.tudu_status_3}}</option>
        <option value="4">{{$LANG.tudu_status_4}}</option>
        </select></td>
        <th align="right" width="70">{{$LANG.title_receiver}}</th>
        <td><input type="text" class="input_text" style="width:150px;" name="to" id="inputto" /></td>
    </tr>
    <tr>
        <th align="right" width="70">{{$LANG.title_board}}</th>
        <td><select name="bid" style="width:160px">
        <option value="">{{$LANG.unassigned}}</option>
              {{foreach from=$boards item=board}}
              {{if $board.type == 'zone' && $board.children}}
              <optgroup label="{{$board.boardname}}">
                  {{foreach from=$board.children item=item}}
                  <option value="{{$item.boardid}}"{{if $tudu.boardid == $item.boardid}} selected="selected"{{/if}}>{{$item.boardname|escape:'html'}}</option>
                  {{/foreach}}
              </optgroup>
              {{/if}}
              {{/foreach}}
        </select></td>
        <th align="right" width="70">{{$LANG.subject_class}}{{$LANG.cln}}</th>
        <td><select name="classid" style="width:160px" disabled="disabled">
        <option value="">{{$LANG.unassigned}}</option>
        </select></td>
    </tr>
    <tr>
        <th align="right" width="70">{{$LANG.title_folder}}</th>
        <td>
        <select name="cat" style="width:160px">
        {{foreach from=$labels item=label}}
        <option value="{{$label.labelalias}}">{{$label.displayname}}</option>
        {{/foreach}}
        </select>
        </td>
        <th align="right" width="70">{{$LANG.read}}/{{$LANG.unread}}{{$LANG.cln}}</th>
        <td>
        <select name="unread" style="width:160px">
        <option value="">{{$LANG.unassigned}}</option>
        <option value="1">{{$LANG.unread}}</option>
        <option value="0">{{$LANG.read}}</option>
        </select></td>
    </tr>
    <tr>
        <th align="right" width="70">{{$LANG.starttime}}{{$LANG.cln}}</th>
        <td>
        <select name="createtime[start]" style="width:160px">
        <option value="">{{$LANG.unassigned}}</option>
        <option value="{{math equation="x-(y * 86400)" x=$smarty.now y=30}}">{{$LANG.around_month}}</option>
        <option value="{{math equation="x-(y * 86400)" x=$smarty.now y=7}}">{{$LANG.around_week}}</option>
        </select>
        </td>
        <th align="right" width="70">{{$LANG.endtime}}{{$LANG.cln}}</th>
        <td>
        <select name="endtime[start]" style="width:160px">
        <option value="">{{$LANG.unassigned}}</option>
        <option value="{{math equation="x-(y * 86400)" x=$smarty.now y=30}}">{{$LANG.around_month}}</option>
        <option value="{{math equation="x-(y * 86400)" x=$smarty.now y=7}}">{{$LANG.around_week}}</option>
        </select></td>
    </tr>
</table>
</div>