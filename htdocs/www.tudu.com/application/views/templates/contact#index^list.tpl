<td valign="top" width="190">
  <div class="c_right">
    <div class="contacts_box">
      <div class="contacts_title"><a href="/contact">{{$LANG.addrbook}}</a></div>
      <div class="contacts_main">
        <div id="dept-tree-ct">
        </div>
      </div>
      <div class="contacts_title"><a href="/contact/group-modify" style="float:right"><font style="font-size:12px;font-weight:normal">{{$LANG.create_group}}</font></a><a href="/contact/?type=contact">{{$LANG.personal_addrbook}}</a></div>
      <div style="line-height:20px;">
        <ul class="contact_group_list">
        <li{{if array_key_exists('groupid', $params) && !$params.groupid}} class="selected"{{/if}}><a href="/contact/?type=contact&groupid=0"><span class="tag_icon" style="background-color:#ffffff;margin:6px 3px 0 0"></span>{{$LANG.group_ungroup}}</a></li>
        <li{{if $params.groupid == '^n'}} class="selected"{{/if}}><a href="/contact/?type=contact&groupid=^n"><span class="tag_icon" style="background-color:#729c3b;margin:6px 3px 0 0"></span>{{$LANG.group_nearly}}</a></li>
      	{{foreach item=group from=$groups}}
      	{{if $group.issystem}}
      	{{assign var="groupkey" value="group_"|cat:$group.groupname}}
      	<li{{if $params.groupid == $group.groupid}} class="selected"{{/if}}><a href="/contact/?type=contact&groupid={{$group.groupid}}"><span class="tag_icon" style="background-color:{{$group.bgcolor}};margin:6px 3px 0 0"></span>{{$LANG[$groupkey]}}</a></li>
		{{else}}
		<li{{if $params.groupid == $group.groupid}} class="selected"{{/if}}><a href="/contact/?type=contact&groupid={{$group.groupid}}"><span class="tag_icon" style="background-color:{{$group.bgcolor}};margin:6px 3px 0 0"></span>{{$group.groupname}}</a></li>
		{{/if}}
		{{/foreach}}
		</ul>
      </div>
    </div>
  </div>
</td>