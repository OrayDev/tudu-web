<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.note}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/note.js?1002" type="text/javascript"></script>
<script type="text/javascript">
<!--
var LH = 'm=note';
if (top == this) {
    location = '/frame#' + LH;
}
-->
</script>
<style type="text/css">
html,body {
    height:100%
}
.note_edit textarea {
    width:97%;
}
{{*
.note_content{
    position:relative;
    zoom:1;
}
.note_subject{
    top:0;
    right:5px;
    height:18px;
    line-height:18px;
    background:#fff;
    position:absolute;
    overflow:hidden;
    z-index:2;
    -moz-border-radius:5px;
    -webkit-border-radius:5px;
    border-radius:5px;
}
.note_subject a{
    display:inline-block;
    padding:0 5px;
    *display:inline;
}
*}}
</style>
</head>
<body>
{{assign var="currUrl" value=$smarty.server.REQUEST_URI|urlencode}}
<div class="position">
    <p><strong class="title">{{$LANG.my_note}}</strong></p>
</div>
<div class="tab-panel">
    <div id="float-toolbar" class="float-toolbar">
    <div class="toolbar">
        <div><button class="btn" type="button" name="create">{{$LANG.create}}</button><button class="btn" type="button" name="delete" disabled>{{$LANG.delete}}</button></div>
    </div>
    <table cellspacing="0" class="grid_thead">
      <tr>
        <td width="30" align="center" style="padding-left:0"><input name="checkall" type="checkbox"></td>
        <td class="title_line" align="center" style="width:36px;padding-left:0"><span class="note_color_box" style="background:#eee"></span></td>
        <td class="title_line"><div class="space">{{$LANG.note_contene}}</div></td>
        <td class="title_line"><div class="space">{{$LANG.tudu_subject}}</div></td>
        <td width="120"></td>
        <td width="30"></td>
      </tr>
    </table>
    </div>
    <div id="toolbar" class="toolbar">
        <div><button class="btn" type="button" name="create">{{$LANG.create}}</button><button class="btn" type="button" name="delete" disabled>{{$LANG.delete}}</button></div>
    </div>
    <table cellspacing="0" class="grid_thead">
      <tr>
        <td width="30" align="center" style="padding-left:0"><input name="checkall" type="checkbox"></td>
        <td class="title_line" align="center" style="width:36px;padding-left:0"><span class="note_color_box" style="background:#eee"></span></td>
        <td class="title_line"><div class="space">{{$LANG.note_contene}}</div></td>
        <td class="title_line"><div class="space">{{$LANG.tudu_subject}}</div></td>
        <td width="120">&nbsp;</td>
        <td width="30">&nbsp;</td>
      </tr>
    </table>
    <div id="notelist" class="note_list">
        {{foreach item=note from=$notes}}
        <div id="note-{{$note.noteid}}" class="note_item">
           <input type="hidden" name="color" value="{{$note.color|base_convert:10:16|str_pad:6:'0'}}" />
           <div class="note_inner">
           <table cellpadding="0" cellspacing="0" border="0" width="100%">
               <tr>
                   <td width="30" align="center" valign="top" style="padding-left:0"><input type="checkbox" name="nid[]" value="{{$note.noteid}}" /></td>
                   <td align="center" style="width:36px;padding-left:0" valign="top"><span class="note_color_box" style="background:#{{$note.color|base_convert:10:16|str_pad:6:'0'}}"></span></td>
                   <td>
                   <div class="note_content">
                       <div class="note_despt" style="padding-left:10px;">{{$note.content|escape:'html'|truncate:50:'...':false:false}}</div>
                       <div class="note_edit" style="display:none;">
                           <textarea name="content">{{$note.content|escape:'html'}}</textarea>
                       </div>
                       {{*
                       <div class="note_subject" style="background:#{{$note.color|base_convert:10:16|str_pad:6:'0'}}"><a href="/tudu/view?tid={{$note.tuduid}}&back={{$currUrl}}">{{$note.subject|escape:'html'|truncate:20:'...'}}</a></div>
                       *}}
                   </div>
                   </td>
                   <td valign="top">{{if $note.tuduid}}<a href="/tudu/view?tid={{$note.tuduid}}&back={{$currUrl}}" title="{{$note.subject|escape:'html'}}">{{$note.subject|escape:'html'|truncate:20:'...'}}</a>{{else}}-{{/if}}</td>
                   <td width="120" valign="top" class="note_time">{{$note.updatetime|date_format:'%Y.%m.%d %H:%M'}}</td>
                   <td width="20" valign="top"><a href="javascript:void(0)" onclick="Note.deleteNote('{{$note.noteid}}')" class="icon icon_grab"></a></td>
               </tr>
           </table>
           </div>
        </div>
        {{/foreach}}
        <div id="note-null" style="text-align:center;padding:30px 0;{{if $notes}}display: none{{/if}}">{{$LANG.null_note}}</div>
    </div>
</div>

<div id="color-panel" style="display:none">
{{foreach key=key item=color from=$bgcolors}}
{{assign var="colorkey" value="color_"|cat:$key}}
<div class="menu-item"><span class="menu-square" style="background-color:{{$color}}"></span><input type="hidden" name="color" value="{{$color}}" />{{$LANG[$colorkey]}}</div>
{{/foreach}}
</div>

<script type="text/javascript">
<!--
$(function(){
    TOP.Frame.title('{{$LANG.note}}');
    TOP.Frame.hash(LH);
    TOP.Label.focusLabel('note');
    Note.init();

    new FixToolbar({
        src: '#toolbar',
        target: '#float-toolbar'
    });
});
-->
</script>
</body>
</html>