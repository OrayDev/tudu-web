<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>基本信息 - 常规</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/base.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.www}}/js/ui/select.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/settings.js" type="text/javascript"></script>
</head>
<body>

{{include file="settings/^nav.tpl" tab="general"}}
<form id="theform" action="{{$basepath}}/settings/general/save" method="post">
<fieldset class="form-field">
	<legend><strong class="f14 text-title">系统设置</strong></legend>
	<div class="line-bold"></div>
	<table border="0" cellspacing="2" cellpadding="3" class="table-form table-form-f12" style="margin:20px 0">
		<tr>
			<th width="110" valign="top" align="right"><p style="margin-top:1px;">系统状态：</p></th>
			<td>
				<p><label for="open"><input name="status" type="radio" value="0" id="open"{{if $org.status == 0}} checked="checked"{{/if}}{{if !$admin.isowner}} disabled="disabled"{{/if}} />开启</label>&nbsp;&nbsp;<label for="close"><input name="status" type="radio" value="1" id="close"{{if $org.status == 1}} checked="checked"{{/if}}{{if !$admin.isowner}} disabled="disabled"{{/if}} />关闭</label></p>
				<p class="gray" style="margin-top:8px;">（关闭系统后，所有用户均无法访问）</p>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset class="form-field">
	<legend><strong class="f14 text-title">时间设置</strong></legend>
	<div class="line-bold"></div>
	<table border="0" cellspacing="2" cellpadding="3" class="table-form table-form-f12" style="margin:20px 0 40px 0">
		<tr>
			<th width="110" align="right">默认时区设置：</th>
			<td><select id="timezone" name="timezone" style="width:280px;">
				{{foreach item=item from=$timezones}}
                {{assign var=zkey value="timezone_"|cat:$item}}
                <option value="Etc/{{$item}}"{{if $org.timezone == 'Etc/'|cat:$item}} selected{{/if}}>{{$LANG[$zkey]}}</option>
                {{/foreach}}
			</select></td>
		</tr>
		<tr>
			<th align="right">默认时间格式：</th>
			<td><select id="dateformat" name="dateformat" style="width:280px;">
				{{assign var=time value='2001-03-14 13:30:55'}}
                {{foreach item=dateformat from=$dateformats}}
                <option value="{{$dateformat}}"{{if $org.dateformat == $dateformat}} selected="selected"{{/if}}>{{$time|date_time_format:$dateformat}}</option>
                {{/foreach}}
			</select></td>
		</tr>
		<tr>
			<th align="right"></th>
			<td></td>
		</tr>
	</table>
</fieldset>
</div>
<div class="tool-btm"><div class="toolbar toolbar-bottom"><input name="save" type="button" class="btn wd50" value="保存"/></div></div>
</form>
<script type="text/javascript">
<!--
$(function() {
	_TOP.switchMod('setting');
	Settings.General.init();
});
-->
</script>
</body>
</html>