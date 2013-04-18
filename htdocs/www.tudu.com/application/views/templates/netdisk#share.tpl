<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.netdisk}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1022" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/netdisk.js?1001" type="text/javascript"></script>

</head>
<body>
<div class="position">
    <p>
    <div class="nd_status" style="float:right;">
    {{$LANG.nd_space}}{{$LANG.cln}}
    <div class="rate_box"><div class="rate_bar" style="width:{{math equation="round(x/y*100, 2)" x=$root.foldersize y=$root.maxquota}}%"><em>{{math equation="round(x/y*100, 2)" x=$root.foldersize y=$root.maxquota}}%</em></div></div>
    {{$LANG.total}} {{$root.maxquota|format_file_size:1000}} / {{$LANG.avalible}} {{math|format_file_size:1000 equation="x-y" x=$root.maxquota y=$root.foldersize}}
    </div>
    <strong class="title">{{$LANG.netdisk}}</strong></p>
</div>
<div class="tab-panel">
    <div id="float-toolbar" class="float-toolbar">
    <div class="toolbar">
        <div><button class="btn" type="button" name="upload" disabled>{{$LANG.upload_file}}</button><button class="btn" type="button" name="folder" disabled>{{$LANG.create_folder}}</button><button class="btn" type="button" name="delete" disabled>{{$LANG.delete}}</button><select name="moveto" style="width:100px" disabled>
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
        <div><button class="btn" type="button" name="upload" disabled>{{$LANG.upload_file}}</button><button class="btn" type="button" name="folder" disabled>{{$LANG.create_folder}}</button><button class="btn" type="button" name="delete" disabled>{{$LANG.delete}}</button><select name="moveto" style="width:100px" disabled>
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
    {{assign var="currUrl" value=$smarty.server.REQUEST_URI|urlencode}}
    <div class="nd_folder_title"><h3>{{$LANG.share_folder}}{{if $folderid != '^share'}}{{if $username}}<span class="icon icon_sub"></span>{{$username}}{{/if}}{{if $folderid}}{{if $folder.issystem}}<span class="icon icon_sub"></span>{{$LANG.sys_folder[$folder.foldername]}}{{else}}<span class="icon icon_sub"></span>{{$folder.foldername}}{{/if}}{{/if}}{{/if}}</h3> | <a href="{{$back|default:'/netdisk/'}}">{{$LANG.up_folder}}</a></div>
    {{if $users}}
    {{foreach from=$users item=item}}
    <table border="0" cellpadding="0" cellspacing="0" class="nd_file" id="user-{{'^'|replace:'_':$item.ownerid}}" _type="user">
        <tr>
            <td width="30" style="padding-left:0" align="center"><input type="checkbox" disabled="disabled" /></td>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" class="nd_file_inner">
                    <tr>
                    <td class="file_icon td_height"><span class="fbicon fb_folder_share"></span></td>
                    <td class="file_name">
                    <p><a href="/netdisk/share?ownerid={{$item.ownerid}}&back={{$currUrl}}">{{$item.ownertruename}}</a></p>
                    </td>
                    </tr>
                </table>
            </td>
            <td width="100">-</td>
            <td width="150">-</td>
        </tr>
    </table>
    {{/foreach}}
    {{/if}}
    {{if $folders}}
    {{foreach from=$folders item=item}}
    <table border="0" cellpadding="0" cellspacing="0" class="nd_file" id="folder-{{'^'|replace:'_':$item.objectid}}" _type="folder">
        <tr>
            <td width="30" style="padding-left:0" align="center"><input type="checkbox" disabled="disabled" /></td>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" class="nd_file_inner">
                    <tr>
                    <td class="file_icon td_height"><span class="fbicon fb_folder_share"></span></td>
                    <td class="file_name">
                    <p><a href="/netdisk/share?folderid={{$item.objectid}}&ownerid={{$item.ownerid}}&back={{$currUrl}}">{{if $item.issystem && isset($LANG.sys_folder[$item.foldername])}}{{$LANG.sys_folder[$item.foldername]}}{{else}}{{$item.foldername}}{{/if}}</a></p>
                    </td>
                    </tr>
                </table>
            </td>
            <td width="100">-</td>
            <td width="150">{{$item.createtime|date_format:'%Y-%m-%d %H:%M:%S'|default:'-'}}</td>
        </tr>
    </table>
    {{/foreach}}
    {{/if}}
    {{if $files}}
    {{foreach from=$files item=file}}
    <table border="0" cellpadding="0" cellspacing="0" class="nd_file" id="file-{{if $file.objectid}}{{$file.objectid}}{{else}}{{$file.fileid}}{{/if}}" _type="file">
        <tr>
            <td width="30" style="padding-left: 0" align="center"><input type="checkbox" disabled="disabled" /></td>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" class="nd_file_inner">
                    <tr>
                    <td class="file_icon"><span class="fbicon fb_{{$file.filename|file_ext}}"></span></td>
                    <td class="file_name">
                    <p>{{$file.filename}}</p>
                    <p class="file_opt"><a href="{{if $file.attachfileid}}{{$file.attachfileid|get_file_url:'attachment':'':$file.fromuniqueid}}{{elseif $file.fromfileid}}{{$file.fromfileid|get_file_url:'netdisk':'':$file.fromuniqueid}}{{else}}{{if $file.objectid}}{{$file.objectid|get_file_url:'netdisk':'':$file.ownerid}}{{else}}{{$file.fileid|get_file_url}}{{/if}}{{/if}}" target="_blank">{{$LANG.download}}</a><a href="javascript:void(0);" onclick="Netdisk.saveToNd('{{if $file.attachfileid}}{{$file.attachfileid}}{{elseif $file.fromfileid}}{{$file.fromfileid}}{{else}}{{if $file.objectid}}{{$file.objectid}}{{else}}{{$file.fileid}}{{/if}}{{/if}}', '{{if $file.fromuniqueid}}{{$file.fromuniqueid}}{{else}}{{if $file.ownerid}}{{$file.ownerid}}{{else}}{{$ownerid}}{{/if}}{{/if}}', '{{if $file.attachfileid}}1{{else}}0{{/if}}')">{{$LANG.attach_save_to_nd}}</a></p>
                    </td>
                    </tr>
                </table>
            </td>
            <td width="100">{{$file.size|format_file_size}}</td>
            <td width="150">{{$file.createtime|date_format:'%Y-%m-%d %H:%M:%S'|default:'-'}}</td>
        </tr>
    </table>
    {{/foreach}}
    {{/if}}
    {{if !$folders && !$files && !$users}}
    <div style="text-align:center;padding:40px 0">{{$LANG.file_list_null}}</div>
    {{/if}}
    </div>
</div>

<script type="text/javascript">
<!--
$(function(){
	TOP.Frame.title('{{$LANG.netdisk}}');
	TOP.Frame.hash('m=netdisk/share{{if $folderid}}&folder={{$folderid}}{{/if}}{{if $ownerid}}&ownerid={{$ownerid}}{{/if}}');
	TOP.Label.focusLabel('netdisk');

	Netdisk.folderId = '{{$folderid}}';
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