<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>基本信息－界面皮肤</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/settings.js?1001" type="text/javascript"></script>
</head>
<body>

{{include file="settings/^nav.tpl" tab="theme"}}
<div class="msgbox">
    <p>设置个性化的皮肤，员工首次登录图度前台默认以该肤色显示</p>
</div>
<form id="theform" action="{{$basepath}}/settings/theme/save" method="post">
<div class="settingbox" style="margin-bottom:40px;">
	<div id="skinlist" class="skinsetting">
	   <div id="skin-8"{{if null === $org.skin || $org.skin == 8}} class="skin_select"{{/if}}>
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><img src="{{$options.sites.static}}/img/spacer.gif" class="face8" /></td>
              </tr>
              <tr>
                <td><input type="radio" value="8" name="skin"{{if null === $org.skin || $org.skin == '8'}} checked="checked"{{/if}} />青绿</td>
              </tr>
            </table>
        </div>
		<div id="skin-0"{{if $org.skin === 0 || $org.skin === '0'}} class="skin_select"{{/if}}>
			<table cellspacing="0" cellpadding="0">
			  <tr>
				<td><img src="{{$options.sites.static}}/img/spacer.gif" class="face0" /></td>
			  </tr>
			  <tr>
				<td><input type="radio" value="0" name="skin"{{if $org.skin === 0 || $org.skin === '0'}} checked="checked"{{/if}} />漫绿</td>
			  </tr>
			</table>
		</div>
		<div id="skin-4"{{if $org.skin == 4}} class="skin_select"{{/if}}>
			<table cellspacing="0" cellpadding="0">
			  <tr>
				<td><img src="{{$options.sites.static}}/img/spacer.gif" class="face4" /></td>
			  </tr>
			  <tr>
				<td><input type="radio" value="4" name="skin"{{if $org.skin == '4'}} checked="checked"{{/if}} />咖啡</td>
			  </tr>
			</table>
		</div>
		<div id="skin-2"{{if $org.skin == 2}} class="skin_select"{{/if}}>
			<table cellspacing="0" cellpadding="0">
			  <tr>
				<td><img src="{{$options.sites.static}}/img/spacer.gif" class="face2" /></td>
			  </tr>
			  <tr>
				<td><input type="radio" value="2" name="skin"{{if $org.skin == '2'}} checked="checked"{{/if}} />中国红</td>
			  </tr>
			</table>
		</div>
		<div id="skin-1"{{if $org.skin == 1}} class="skin_select"{{/if}}>
			<table cellspacing="0" cellpadding="0">
			  <tr>
				<td><img src="{{$options.sites.static}}/img/spacer.gif" class="face1" /></td>
			  </tr>
			  <tr>
				<td><input type="radio" value="1" name="skin"{{if $org.skin == '1'}} checked="checked"{{/if}} />粉红</td>
			  </tr>
			</table>
		</div>
		<div id="skin-6"{{if $org.skin == 6}} class="skin_select"{{/if}}>
			<table cellspacing="0" cellpadding="0">
			  <tr>
				<td><img src="{{$options.sites.static}}/img/spacer.gif" class="face6" /></td>
			  </tr>
			  <tr>
				<td><input type="radio" value="6" name="skin"{{if $org.skin == '6'}} checked="checked"{{/if}} />梦幻紫</td>
			  </tr>
			</table>
		</div>
		<div id="skin-5"{{if $org.skin == 5}} class="skin_select"{{/if}}>
			<table cellspacing="0" cellpadding="0">
			  <tr>
				<td><img src="{{$options.sites.static}}/img/spacer.gif" class="face5" /></td>
			  </tr>
			  <tr>
				<td><input type="radio" value="5" name="skin"{{if $org.skin == '5'}} checked="checked"{{/if}} />普兰</td>
			  </tr>
			</table>
		</div>
		<div id="skin-3"{{if $org.skin == 3}} class="skin_select"{{/if}}>
			<table cellspacing="0" cellpadding="0">
			  <tr>
				<td><img src="{{$options.sites.static}}/img/spacer.gif" class="face3" /></td>
			  </tr>
			  <tr>
				<td><input type="radio" value="3" name="skin"{{if $org.skin == '3'}} checked="checked"{{/if}} />灰调</td>
			  </tr>
			</table>
		</div>
		<div id="skin-7"{{if $org.skin == 7}} class="skin_select"{{/if}}>
			<table cellspacing="0" cellpadding="0">
			  <tr>
				<td><img src="{{$options.sites.static}}/img/spacer.gif" class="face7" /></td>
			  </tr>
			  <tr>
				<td><input type="radio" value="7" name="skin"{{if $org.skin == '7'}} checked="checked"{{/if}} />黑色</td>
			  </tr>
			</table>
		</div>
	</div>
</div>
</div>
<div class="tool-btm"><div class="toolbar toolbar-bottom"><input name="save" type="button" class="btn wd50" value="保存"/></div></div>
</form>
<script type="text/javascript">
<!--
var _IS_PASSPORT={{if $admin.ispassport}}true{{else}}false{{/if}},
	_HAS_SKIN   ={{if null !== $admin.skin && '' !== $admin.skin}}true{{else}}false{{/if}};
$(function() {
	_TOP.switchMod('setting');
	Settings.Theme.init();
});
-->
</script>
</body>
</html>
