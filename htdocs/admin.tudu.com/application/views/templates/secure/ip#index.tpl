<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.system_safe}} － IP地址过滤</title>
{{include file="^style.tpl"}}
<script type="text/javascript" src="{{$options.sites.static}}/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="{{$options.sites.static}}/js/jquery.tree.js"></script>
<script type="text/javascript" src="{{$options.sites.static}}/js/frame.js"></script>
<script type="text/javascript" src="{{$options.sites.static}}/js/secure.js?1003"></script>
</head>
<body>

{{include file="secure/^nav.tpl" tab="ip"}}
<div class="msgbox">
	<p>注：可设置允许指定IP访问图度平台。</p>
</div>
<form id="theform" method="post" action="{{$basepath}}/secure/ip/save">
<table border="0" cellspacing="2" cellpadding="3" class="table-form table-form-f12" style="line-height:2.4em; margin:0 0 40px -3px;width:550px">
	<tr>
		<th align="right" valign="top" width="100">IP地址过滤{{$LANG.cln}}</th>
		<td>
			<p style="_padding:5px 0;"><label for="ipfilter"><input id="ipfilter" name="ipfilter" type="checkbox" value="1"{{if $org.isiprule == 1}} checked="checked"{{/if}} />开启IP过滤，仅允许以下IP地址访问</label></p>
			<p style="_padding:5px 0;"><input type="text" id="iptext" class="text" style="width:250px;" title="请填写IP地址，支持通配符 “*”" disabled="disabled" value="" />&nbsp;&nbsp;<input id="add" value="添加" type="button" class="btn wd50" disabled="disabled" _disabled="disabled" /></p>
			<p style="_padding:5px 0;" class="gray">填写格式如：122.122.122.122</p>
			<div style="text-align:left">
    			<div class="select-box" style="width:250px;height:205px;">
    				<div class="search-box">
                    	<input class="text" type="text" style="width:238px;" name="search-ip" disabled="disabled">
                    	<span class="icon icon-search"></span>
                    </div>
                    <div id="access-ip" class="select-box-inner" style="height:170px;-moz-user-select:none;">
                    </div>
                    <div id="search-rs" class="select-box-inner" style="height:170px;display:none;-moz-user-select:none;">
                    </div>
    			</div>
    			<div style="float:left;">
        			<p>&nbsp;</p>
        			<p>&nbsp;&nbsp;<input name="delete" id="delete" class="btn wd50" value="删除" type="button" disabled="disabled" _disabled="disabled" /></p>
        		</div>
    		</div>
		</td>
	</tr>
	<tr>
		<th align="right"></th>
		<td>
			<p style="_padding:5px 0;"><label for="exception" class="gray"><input id="exception" name="exception" type="checkbox" value="1" disabled="disabled" _disabled="disabled" {{if $iprule.exception != null}} checked="checked"{{/if}} />设置指定帐号不受IP过滤限制</label></p>
			<div id="exception-checkbox" style="display:none;">

			</div>
		</td>
	</tr>
</table>
<div class="tool-btm"><div class="toolbar toolbar-bottom"><input type="submit" class="btn wd50" value="保存"></input></div></div>
</form>
<script type="text/javascript">
<!--
var iprule = [], exception = [];
{{foreach from=$iprule.rule item=item}}
iprule.push('{{$item}}');
{{/foreach}}
{{foreach from=$iprule.exception item=item}}
exception.push('{{$item}}');
{{/foreach}}
$(function() {
	_TOP.switchMod('secure');
	Secure.Ip.init();
});
-->
</script>
</body>
</html>
