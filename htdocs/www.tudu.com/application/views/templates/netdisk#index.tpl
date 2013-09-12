<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.netdisk}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1031" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/netdisk.js?1001" type="text/javascript"></script>
<script type="text/javascript">
<!--
var LH = 'm=netdisk';
if (top == this) {
	location = '/frame#' + LH;
}
-->
</script>

</head>
<body>

<div class="position">
    <p>
    <div class="nd_status" style="float:right;">
    {{$LANG.nd_space}}{{$LANG.cln}}
    <div class="rate_box"><div class="rate_bar" style="width:{{if $root.maxquota > 0}}{{math equation="round(x/y*100, 2)" x=$root.foldersize y=$root.maxquota}}{{else}}0{{/if}}%"><em>{{if $root.maxquota > 0}}{{math equation="round(x/y*100, 2)" x=$root.foldersize y=$root.maxquota}}{{else}}0{{/if}}%</em></div></div>
    {{$LANG.total}} {{$root.maxquota|format_file_size:1000}} / {{$LANG.avalible}} {{math|format_file_size:1000 equation="x-y" x=$root.maxquota y=$root.foldersize}}
    </div>
    <strong class="title">{{$LANG.netdisk}}</strong></p>
</div>
<div class="tab-panel">
    <div id="float-toolbar" class="float-toolbar">
    <div class="toolbar">
        <div><button class="btn" type="button" name="upload">{{$LANG.upload_file}}</button>{{if $folder.folderid == '^root'}}<button class="btn" type="button" name="folder">{{$LANG.create_folder}}</button>{{/if}}<button class="btn" type="button" name="delete" disabled>{{$LANG.delete}}</button><select name="moveto" style="width:100px">
        <option>{{$LANG.move_to}}</option>
        {{if $folder.folderid != '^root'}}
        <option value="^root">{{$LANG.root_folder}}</option>
        {{/if}}
        {{foreach from=$folders item=item}}
        {{if $item.folderid != '^root' && $item.folderid != $folder.folderid}}
        <option value="{{$item.folderid}}">{{if $item.issystem}}{{$LANG.sys_folder[$item.foldername]}}{{else}}{{$item.foldername}}{{/if}}</option>
        {{/if}}
        {{/foreach}}
        </select></div>
    </div>
    <table cellspacing="0" class="grid_thead">
      <tr>
        <td width="30" align="center" style="padding-left:0"><input name="checkall" type="checkbox"></td>
        <td class="title_line"><div class="space">{{$LANG.file_name}}</div></td>
        <td class="title_line" width="115"><div class="space">{{$LANG.file_size}}</div></td>
        <td class="title_line" width="150"><div class="space">{{$LANG.upload_time}}</div></td>
      </tr>
    </table>
    </div>
    <div id="toolbar" class="toolbar">
        <div><button class="btn" type="button" name="upload">{{$LANG.upload_file}}</button>{{if $folder.folderid == '^root'}}<button class="btn" type="button" name="folder">{{$LANG.create_folder}}</button>{{/if}}<button class="btn" type="button" name="delete" disabled>{{$LANG.delete}}</button><select name="moveto" style="width:100px">
        <option>{{$LANG.move_to}}</option>
        {{if $folder.folderid != '^root'}}
        <option value="^root">{{$LANG.root_folder}}</option>
        {{/if}}
        {{foreach from=$folders item=item}}
        {{if $item.folderid != '^root' && $item.folderid != $folder.folderid}}
        <option value="{{$item.folderid}}">{{if $item.issystem}}{{$LANG.sys_folder[$item.foldername]}}{{else}}{{$item.foldername}}{{/if}}</option>
        {{/if}}
        {{/foreach}}
        </select></div>
    </div>
    <table cellspacing="0" class="grid_thead">
      <tr>
        <td width="30" align="center" style="padding-left:0"><input name="checkall" type="checkbox"></td>
        <td class="title_line"><div class="space">{{$LANG.file_name}}</div></td>
        <td class="title_line" width="115"><div class="space">{{$LANG.file_size}}</div></td>
        <td class="title_line" width="150"><div class="space">{{$LANG.upload_time}}</div></td>
      </tr>
    </table>
    <div id="file" class="note_list">
    {{if $folder.folderid == '^root'}}

    {{if $existshare}}
    {{assign var="currUrl" value=$smarty.server.REQUEST_URI|urlencode}}
    <table border="0" cellpadding="0" cellspacing="0" class="nd_file" id="folder-_share" _type="folder">
        <tr>
            <td width="30" style="padding-left:0" align="center"><input type="checkbox" disabled="disabled" /></td>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" class="nd_file_inner">
                    <tr>
                    <td class="file_icon td_height"><span class="fbicon fb_folder_share"></span></td>
                    <td class="file_name">
                    <p><a href="/netdisk/share?folderid=^share&back={{$currUrl}}">{{$LANG.share_folder}}</a></p>
                    </td>
                    </tr>
                </table>
            </td>
            <td width="100">-</td>
            <td width="150">-</td>
        </tr>
    </table>
    {{/if}}

    {{if !$isSort}}
    {{foreach from=$folders item=item}}
    {{if $item.folderid!= '^root'}}
    <table border="0" cellpadding="0" cellspacing="0" class="nd_file" id="folder-{{'^'|replace:'_':$item.folderid}}" _type="folder">
        <tr>
            <td width="30" style="padding-left:0" align="center"><input type="checkbox" disabled="disabled" /></td>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" class="nd_file_inner">
                    <tr>
                    <td class="file_icon">{{if $item.isshare}}<span class="fbicon fb_folder_share"></span>{{else if}}<span class="fbicon fb_folder"></span>{{/if}}</td>
                    <td class="file_name">
                    <p><a href="/netdisk/?folderid={{$item.folderid}}">{{if $item.issystem && isset($LANG.sys_folder[$item.foldername])}}{{$LANG.sys_folder[$item.foldername]}}{{else}}{{$item.foldername}}{{/if}}</a></p>
                    <p class="file_opt"><a href="javascript:void(0);" onclick="Netdisk.rename('folder', '{{$item.folderid}}')">{{$LANG.rename}}</a><a href="javascript:void(0);" onclick="Netdisk.deleteFolder('{{$item.folderid}}')">{{$LANG.delete}}</a>{{if !$item.isshare}}<a href="javascript:void(0);" onclick="Netdisk.shareFolder('{{$item.folderid}}', 1)">{{$LANG.config_share}}</a>{{else if}}<a href="javascript:void(0);" onclick="Netdisk.shareMember('{{$item.folderid}}', 'folder')">{{$LANG.edit_share_member}}</a><a href="javascript:void(0);" onclick="Netdisk.shareFolder('{{$item.folderid}}', 0)">{{$LANG.cancel_share}}</a>{{/if}}</p>
                    </td>
                    </tr>
                </table>
            </td>
            <td width="100">-</td>
            <td width="150">{{$item.createtime|date_format:'%Y-%m-%d %H:%M:%S'|default:'-'}}</td>
        </tr>
    </table>
    {{/if}}
    {{/foreach}}
    {{/if}}
    {{else}}
    <div class="nd_folder_title"><h3>{{if $folder.issystem}}{{$LANG.sys_folder[$folder.foldername]}}{{else}}{{$folder.foldername}}{{/if}}</h3> | <a href="/netdisk/">{{$LANG.up_folder}}</a></div>
    {{/if}}
    {{foreach from=$files item=file}}
    <table border="0" cellpadding="0" cellspacing="0" class="nd_file" id="file-{{$file.fileid}}" _type="file">
        <tr>
            <td width="30" style="padding-left: 0" align="center"><input type="checkbox" name="fileid[]" value="{{$file.fileid}}" /></td>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" class="nd_file_inner">
                    <tr>
                    <td class="file_icon"><span class="fbicon fb_{{$file.filename|file_ext}}"></span></td>
                    <td class="file_name">
                    <p>{{$file.filename}}</p>
                    <p class="file_opt"><a href="{{if $file.isfromattach}}{{$file.attachfileid|get_file_url:'attachment'}}{{elseif $file.fromfileid}}{{$file.fromfileid|get_file_url}}{{else}}{{$file.fileid|get_file_url}}{{/if}}" target="_blank">{{$LANG.download}}</a><a href="javascript:void(0);" onclick="Netdisk.rename('file', '{{$file.fileid}}')">{{$LANG.rename}}</a><a href="javascript:void(0);" onclick="Netdisk.deleteFile('{{$file.fileid}}')">{{$LANG.delete}}</a>{{if !$file.isshare}}<a href="javascript:void(0);" onclick="Netdisk.shareFile('{{$file.fileid}}', 1)">{{$LANG.config_share}}</a>{{else if}}<a href="javascript:void(0);" onclick="Netdisk.shareMember('{{$file.fileid}}', 'file')">{{$LANG.edit_share_member}}</a><a href="javascript:void(0);" onclick="Netdisk.shareFile('{{$file.fileid}}', 0)">{{$LANG.cancel_share}}</a>{{/if}}</p>
                    </td>
                    </tr>
                </table>
            </td>
            <td width="100">{{$file.size|format_file_size}}</td>
            <td width="150">{{$file.createtime|date_format:'%Y-%m-%d %H:%M:%S'|default:'-'}}</td>
        </tr>
    </table>
    {{foreachelse}}
    {{if $folder.folderid != '^root'}}
    <div style="text-align:center;padding:40px 0">{{$LANG.file_list_null}}</div>
    {{/if}}
    {{/foreach}}
	</div>
</div>

<div id="color-panel" style="display:none">
{{foreach item=color from=$bgcolors}}<div class="color_block"><div style="background-color:{{$color}}"></div><input type="hidden" name="color" value="{{$color}}" /></div>{{/foreach}}
</div>

<script type="text/javascript">
<!--
$(function(){
	TOP.Frame.title('{{$LANG.netdisk}}');
	TOP.Frame.hash(LH);
	TOP.Label.focusLabel('netdisk');

	Netdisk.folderId = '{{$folder.folderid}}';
	Netdisk.init();

	new FixToolbar({
    	src: '#toolbar',
    	target: '#float-toolbar'
    });
});
-->
</script>
</body>
</html>