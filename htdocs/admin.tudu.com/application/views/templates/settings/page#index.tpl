<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>基本信息－登陆页设置</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/settings.js" type="text/javascript"></script>
</head>
<body>

{{include file="settings/^nav.tpl" tab="page"}}
<div class="title-bar"><strong class="f14">选择模板</strong></div>
<form id="theform" action="{{$basepath}}/settings/page/save" method="post" class="settingbox">
    <div class="skinsetting">
        <div id="skin-face01"{{if $loginskin === null || !array_key_exists('selected', $loginskin) || ($loginskin.selected.value == 'SYS:default' && $loginskin.selected.type == 'color')}} class="skin_select"{{/if}}>
            <input type="hidden" name="type" value="color" />
            <input type="hidden" name="value" value="SYS:default" />
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><img src="{{$basepath}}/img/spacer.gif" class="login_face01"></td>
              </tr>
              <tr>
                <td>默认</td>
              </tr>
            </table>
        </div>
        <div id="skin-face02"{{if $loginskin.selected.value == 'SYS:#58789e' && $loginskin.selected.type == 'color'}} class="skin_select"{{/if}}>
            <input type="hidden" name="type" value="color" />
            <input type="hidden" name="value" value="SYS:#58789e" />
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><img src="{{$basepath}}/img/spacer.gif" class="login_face02"></td>
              </tr>
              <tr>
                <td>湖蓝</td>
              </tr>
            </table>
        </div>
        <div id="skin-face03"{{if $loginskin.selected.value == 'SYS:#c57592' && $loginskin.selected.type == 'color'}} class="skin_select"{{/if}}>
            <input type="hidden" name="type" value="color" />
            <input type="hidden" name="value" value="SYS:#c57592" />
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><img src="{{$basepath}}/img/spacer.gif" class="login_face03"></td>
              </tr>
              <tr>
                <td>玫红</td>
              </tr>
            </table>
        </div>
        <div id="skin-face04"{{if $loginskin.selected.value == 'SYS:#99ac71' && $loginskin.selected.type == 'color'}} class="skin_select"{{/if}}>
            <input type="hidden" name="type" value="color" />
            <input type="hidden" name="value" value="SYS:#99ac71" />
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><img src="{{$basepath}}/img/spacer.gif" class="login_face04"></td>
              </tr>
              <tr>
                <td>腾绿</td>
              </tr>
            </table>
        </div>
        <div id="skin-color"{{if !$loginskin.selected.issystem && $loginskin.selected.type == 'color'}} class="skin_select"{{/if}}{{if !array_key_exists('color', $loginskin)}} style="display:none;"{{/if}}>
            <input type="hidden" name="type" value="color" />
            <input type="hidden" name="value" value="CUSTOM:{{$loginskin.color.value}}" />
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><img src="{{$basepath}}/img/spacer.gif" style="background:{{$loginskin.color.value}}" /></td>
              </tr>
              <tr>
                <td>自定义颜色</td>
              </tr>
            </table>
        </div>
        <div id="custom-color" class="custom">
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><img src="{{$basepath}}/img/spacer.gif" class="login_face05"></td>
              </tr>
              <tr>
                <td>自定义</td>
              </tr>
            </table>
        </div>
    </div>
    <div class="skinsetting">
        <div id="skin-face11"{{if $loginskin.selected.value == 'SYS:bg_03.jpg' && $loginskin.selected.type == 'pic'}} class="skin_select"{{/if}}>
            <input type="hidden" name="type" value="pic" />
            <input type="hidden" name="value" value="SYS:bg_03.jpg" />
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><img src="{{$basepath}}/img/spacer.gif" class="login_face11"></td>
              </tr>
              <tr>
                <td>晨曦</td>
              </tr>
            </table>
        </div>
        <div id="skin-face01"{{if $loginskin.selected.value == 'SYS:bg_02.jpg' && $loginskin.selected.type == 'pic'}} class="skin_select"{{/if}}>
            <input type="hidden" name="type" value="pic" />
            <input type="hidden" name="value" value="SYS:bg_02.jpg" />
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><img src="{{$basepath}}/img/spacer.gif" class="login_face12"></td>
              </tr>
              <tr>
                <td>余晖</td>
              </tr>
            </table>
        </div>
        <div id="skin-face13"{{if $loginskin.selected.value == 'SYS:bg_01.jpg' && $loginskin.selected.type == 'pic'}} class="skin_select"{{/if}}>
            <input type="hidden" name="type" value="pic" />
            <input type="hidden" name="value" value="SYS:bg_01.jpg" />
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><img src="{{$basepath}}/img/spacer.gif" class="login_face13"></td>
              </tr>
              <tr>
                <td>春叶</td>
              </tr>
            </table>
        </div>
        <div id="skin-face14"{{if $loginskin.selected.value == 'SYS:bg_04.jpg' && $loginskin.selected.type == 'pic'}} class="skin_select"{{/if}}>
            <input type="hidden" name="type" value="pic" />
            <input type="hidden" name="value" value="SYS:bg_04.jpg" />
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><img src="{{$basepath}}/img/spacer.gif" class="login_face14"></td>
              </tr>
              <tr>
                <td>秋麦</td>
              </tr>
            </table>
        </div>
        <div id="skin-pic"{{if !$loginskin.selected.issystem && $loginskin.selected.type == 'pic'}} class="skin_select"{{/if}}{{if !array_key_exists('image', $loginskin)}} style="display:none;"{{/if}}>
            <input type="hidden" name="type" value="pic" />
            <input type="hidden" name="value" value="CUSTOM:{{$loginskin.image.fileurl}}" />
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><img src="{{$basepath}}/settings/page/file?hash={{$loginskin.image.fileurl}}"></td>
              </tr>
              <tr>
                <td>自定义图片</td>
              </tr>
            </table>
        </div>
        <div id="custom-pic" class="custom">
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><img src="{{$basepath}}/img/spacer.gif" class="login_face15"></td>
              </tr>
              <tr>
                <td>添加图片</td>
              </tr>
            </table>
        </div>
    </div>
</form>
<div class="tool-btm"><div class="toolbar toolbar-bottom"><input name="preview" type="button" class="btn wd50" value="预览" /><input name="save" type="button" class="btn wd50" value="{{$LANG.save}}" /></div></div>

<script type="text/javascript">
<!--
$(function() {
    _TOP.switchMod('setting');
    Settings.LoginSkin.init();
});
-->
</script>
</body>
</html>