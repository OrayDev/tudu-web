<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>TUDU</title>

{{include file="^style.tpl"}}
<link rel="shortcut icon" href="{{$smarty.const.PROTOCOL}}//{{$user.orgid}}.tudu.com/favicon.ico" type="image/x-icon">
<link href="{{$options.sites.static}}/js/Jcrop/css/jquery.Jcrop.css" type="text/css" rel="stylesheet" />
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>

<style type="text/css">
<!--
html{overflow:hidden;height:100%}
-->
</style>
<script type="text/javascript">
//Frame.LockScreen.check();
</script>
</head>
<body class="frameset">
<div class="container-top">
    <div class="logo" style="padding-top:4px"><a href="#"><img src="/logo?oid={{$user.orgid}}" border="0" /></a></div>
    <div class="search">
        <form action="/tudu/" target="main" method="get">
            <input type="hidden" name="search" value="query" />
            <input type="hidden" name="cat" value="all" />
            <p id="quick-tools">{{if $user.isadmin}}<a href="https://{{$smarty.server.HTTP_HOST}}/admin/login/?sid={{$sid}}" target="_blank" id="admin-link">{{$LANG.admin}}</a>&nbsp;|&nbsp;{{/if}}<a href="javascript:void(0)" id="adv_search">{{$LANG.advsearch}}</a>&nbsp;|&nbsp;<a href="{{$options.sites.www}}/help/index.html" target="_blank">{{$LANG.help}}</a>&nbsp;-&nbsp;<a href="{{$options.sites.www}}/down/tudu_manual.pdf" target="_blank">{{$LANG.tudu_help_book}}</a>&nbsp;|&nbsp;{{if $user.isadmin || $user.isowner}}<a href="http://www.tudu.com/suggest/" target="_blank">{{$LANG.suggest}}</a>&nbsp;|&nbsp;{{/if}}<a href="{{$options.sites.www}}/login/logout">{{$LANG.logout}}</a></p>
            <div class="search_input">
                <input id="searchinput" name="keyword" type="text" title="{{$LANG.search_tudu}}" />
                <button type="submit" class="icon icon_search" style="border:none;cursor:pointer"></button>
            </div>
       </form>
    </div>
    <div class="quicklink">
        <p class="black"><strong id="truename">{{$user.truename}}</strong></p>
        <p><a href="/frame/home" target="main">{{$LANG.tudu_homepage}}</a>&nbsp;|&nbsp;<a href="/setting/" target="main">{{$LANG.config}}</a>{{if $access.skin}}&nbsp;-&nbsp;<a href="/setting/skin" target="main">{{$LANG.change_skin}}</a>{{/if}}</p>
    </div>
</div>

<div class="fb-top-wrap">
    <div class="fb-top"><div class="fb-top-left"></div></div>
</div>

<div class="container-main">
    <!-- content-left   -->
    <div class="content-left">
		<div class="sb-top">
			<div class="sb-top-inner">
				<ul>
					<li><a href="/tudu/modify" target="main"><span class="icon_big i_b_create"></span>{{$LANG.compose}}</a></li>
					{{if $access.flow}}<li><a href="/flow/" target="main"><span class="icon_big i_b_workflow"></span>{{$LANG.tudu_flows}}</a></li>{{/if}}
					<li><a href="/contact/" target="main"><span class="icon_big i_b_contact"></span>{{$LANG.contacts}}</a></li>
				</ul>
			</div>
		</div>
        <!--  sb-middle-wrap   -->
		<div class="sb-middle-wrap">
			<div class="sb-middle" id="sb-middle">
				<div class="sys_folder_list" id="sys_label_list">
					<ul>

					</ul>
				</div>

				<div class="sys_folder_list" id="user_label_list">
				   <ul>

				   </ul>
				</div>

				<div class="sys_folder_list">
				   <ul>
				      <li id="f_morelabel_td"><a href="javascript:void(0)" id="f_morelabel" onClick="Label.focusLabel('morelabel')">{{$LANG.more_labels}}...</a></li>
				   </ul>
				</div>

				<div class="sep_line"></div>
				<div class="panel-body panel-body-noheader my_tab">
                    <div class="list_toggle_title" id="user_mailbox_title"><a class="icon icon_conf fr" style="padding:0" href="/email/" target="main" title="{{$LANG.set_bind_mailbox}}"></a><span class="tree-ec-icon tree-elbow-plus"></span><a href="javascript:void(0)" style="padding:0" class="nobg">{{$LANG.my_mailbox}}<span id="mailbox_total"></span></a></div>
                    <div class="sys_folder_list" id="user_mailbox_list" style="display:none">
                    <ul>
                    {{foreach item=mailbox from=$mailboxes}}
                    <li email="{{$mailbox.address}}"><a href="{{if $mailbox.type != 2}}/email/login?address={{$mailbox.address|escape:'url'}}{{else}}javascript:void(0){{/if}}"{{if $mailbox.type != 2}} target="_blank"{{/if}} title="{{$mailbox.address}}"><span class="labelname">{{$mailbox.address|truncate:13}}</span><span class="mail_count"></span></a></li>
                    {{foreachelse}}
                    <li name="add-mailbox"><a href="/email/" target="main"><span class="labelname">{{$LANG.top_add_mailbox}}</span></a></li>
                    {{/foreach}}
                    </ul>
                    </div>
                    <div class="tree-clear"></div>
                </div>

				{{if 0}}
				<div class="sep_line"></div>
				<div class="touch_list">
					<a href="#"><span class="icon icon_chat"></span><span>{{$LANG.chat_history}}</span></a>
				</div>
				{{/if}}
				<div class="sep_line"></div>
				<div class="panel-body panel-body-noheader my_tab">
                    <div class="list_toggle_title" id="user_board_title"><a class="icon icon_conf fr" style="padding:0" href="/board/manage" target="main" title="{{$LANG.set_attention_board}}"></a><span class="tree-ec-icon tree-elbow-plus"></span><a href="{{helper url(array('module' => 'default', 'controller' => 'board', 'action' => 'index'))}}" target="main" id="f_board_td" style="padding:0" class="nobg">{{$LANG.tudu_board}}</a><span class="icon icon_board" style="margin-left:5px"></span></div>
                    <div class="sys_folder_list" id="user_board_list" style="display:none">
                    <ul>
                    {{foreach item=board from=$boards}}
                    <li id="b_{{$board.boardid|replace:'^':'_'}}"><a href="/board/?bid={{$board.boardid}}" onClick="Label.focusLabel()" target="main" title="{{$board.boardname|escape:'html'}}">{{$board.boardname|escape:'html'|truncate:12}}</a></li>
                    {{foreachelse}}
                    <li name="attention-board"><a href="/board/manage" target="main">{{$LANG.add_attention_board}}</a></li>
                    {{/foreach}}
                    </ul>
                    </div>
                    <div class="tree-clear"></div>
                </div>
                <div class="sep_line"></div>
				<div class="my_note">
					<ul>
						<li><span id="f_netdisk_td"><a href="/netdisk/" target="main" id="netdisk" onClick="Label.focusLabel('netdisk')">{{$LANG.netdisk}}</a></span>|<span id="f_note_td"><a href="/note/" target="main" id="note" onClick="Label.focusLabel('note')">{{$LANG.note}}</a></span>|<span id="f_calendar_td"><a href="/calendar/" target="main" id="calendar" onClick="Label.focusLabel('calendar')">{{$LANG.calendar}}</a></span></li>
						<!-- <li><a href="#" target="main" id="f_all_td">所有图度</a>|<a href="#" target="main" id="f_sheet_td">工作表</a></li> -->
					</ul>
				</div>
			</div>
       	</div>
        <!-- end sb-middle-wrap -->
		<div class="sb-bottom-wrap">
			<div class="sb-bottom" id="sb-bottom">
                {{*<div class="sb-bottom_inner"><strong><a href="javascript:void(0)" onClick="loginMailbox()">{{$LANG.my_mailbox}}<span id="recentmail"></span></a></strong></div>*}}
				<div class="fb-bottom-left"></div>
			</div>
        </div>
	</div>
    <!-- end content-left   -->
	<div class="fb-left-split">
		<table width="6" height="100%" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td height="100%"><a href="javascript:void(0);" onFocus="this.blur();" class="fb-split-arrow" id="toggleSb"><span></span></a></td>
		  </tr>
		</table>
	</div>
	<div class="content-main">
	    {{if strpos($smarty.server.HTTP_USER_AGENT, 'MSIE 6') !== false}}
	    <iframe height="100%" frameborder="0" scrolling="yes" allowtransparency="true" class="iframe-main" marginheight="0" marginwidth="0" name="main" id="mainframe" src="/frame/home"></iframe>
	    {{else}}
		<iframe height="100%" frameborder="0" scrolling="auto" allowtransparency="true" class="iframe-main" marginheight="0" marginwidth="0" name="main" id="mainframe" src="/frame/home"></iframe>
		{{/if}}
	</div>

</div>


<div id="win-ct">
    <div id="castwin" class="pop_wrap" style="width:470px;display:none;position:absolute">
       <div class="pop pop_linkman">
          <div class="pop_header"><strong>{{$LANG.select_contact}}</strong><a href="javascript:void(0);" class="icon icon_close close"></a></div>
             <div class="pop_body">
                <div>
                    <div class="p_body_left">
                        <p><strong>{{$LANG.contact}}</strong></p>
                        <div class="pop_body_inner">
                            <div class="input_box"><input style="width:185px;" class="input_text" name="" id="contact_search" type="text" title="{{$LANG.search_user}}"><a class="icon icon_search_2"></a></div>
                            <div class="list_box" id="contact_box">
                            {{if 0}}
                                <div class="groupopen"><div>快捷通讯录</div></div>
                                <div id="q_contact"></div>
                            {{/if}}
                            <div id="user_select">
                                <div class="groupopen"><div>{{$LANG.dept_cast}}</div></div>
                                <div id="contactbox"></div>
                            </div>
                            <div id="group_select">
                                <div class="groupopen"><div>{{$LANG.group}}</div></div>
                                <div id="group_box"></div>
                            </div>
                            </div>
                            <div class="list_box" id="contact_search_result" style="display:none;">

                            </div>
                        </div>
                    </div>
                    <div class="p_body_centre"></div>
                    <div class="p_body_right">
                        <p><strong id="mtitle"></strong></p>
                        <div class="pop_body_inner">
                            <div class="list_box" id="target-user">

                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
               </div>
             </div>
          <div class="pop_footer"><button type="button" name="confirm" class="btn">{{$LANG.confirm}}</button><button type="button" class="btn close">{{$LANG.cancel}}</button></div>
       </div>
    </div>
    <div id="searchwin" class="pop_wrap" style="width:530px;display:none;position:absolute">
       <form id="advsearch_form" action="/tudu/" target="main" method="get">
       <input type="hidden" name="search" value="adv" />
       <div class="pop pop_linkman">
          <div class="pop_header"><strong>{{$LANG.tudu_adv_search}}</strong><a href="javascript:void(0);" class="icon icon_close close"></a></div>
             <div class="pop_body" style="padding:10px 0;height:150px">

             </div>
          <div class="pop_footer"><button type="submit" class="btn">{{$LANG.find}}</button><button type="button" class="btn close">{{$LANG.cancel}}</button></div>
       </div>
       </form>
    </div>
</div>

<script src="{{$options.sites.static}}/js/jquery.extend.js?1009?1006" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/xheditor-1.1.9/xheditor-zh-cn.min.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.Jcrop.js" type="text/javascript"></script>
<script src="/static/js?f=lang&lang={{$user.option.language}}&ver=1013" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/all.js?2032" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/talk.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/tips.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/upload2.js?1001" type="text/javascript"></script>

<script type="text/javascript">
var _FILECGI = {
	upload: '{{$options.sites.file}}{{$upload.cgi.upload}}',
	download: '{{$options.sites.file}}{{$upload.cgi.download}}',
	swfupload: '/images/swfupload.swf'
};
var _ORGNAME = '{{$org.orgname}}';
var _SYS_LABEL_NAME = {
    inbox: '{{$LANG.label_inbox}}',
    todo: '{{$LANG.label_todo}}',
    review: '{{$LANG.label_review}}',
    drafts: '{{$LANG.label_drafts}}',
    starred: '{{$LANG.label_starred}}',
    notice: '{{$LANG.label_notice}}',
    discuss: '{{$LANG.label_discuss}}',
    meeting: '{{$LANG.label_meeting}}',
    forwarded: '{{$LANG.label_forwarded}}',
    sent: '{{$LANG.label_sent}}',
    done: '{{$LANG.label_done}}',
    ignore: '{{$LANG.label_ignore}}',
    reviewed: '{{$LANG.label_reviewed}}'
};
Label.setLabels({{format_label labels=$labels}}).refreshMenu();

(function(){

var m = $('#sb-middle').css('overflow', 'auto');
var b = $('#sb-bottom');
var h = m.offset().top + b.height();

function onResize() {
    var height = document.body.offsetHeight - h;
    var height_2 = document.body.offsetHeight - 73;
    var height_3 = document.body.offsetHeight - 70;
    m.height(Math.max(height, 10));
    $(".content-left").height(height_2);
    $('#mainframe').height(height_3);
    //$('#tip').html([document.body.offsetHeight,m.offset().top,b.height()].join('|'));
    $('#sb-middle').parent().css('bottom', b.height());
    $('#sb-middle').parent().css('top', $('.sb-top').outerHeight());
}

onResize();

if (!Device.Android && !TOP.iOS) {
    window.onresize = onResize;
}

$('#toggleSb').click(function(){
    $(document.body).toggleClass('frame-left-collapsed');
});

$('#tudu-extend a, .sb-top-inner a').click(function(){
	Label.focusLabel(this);
});

$('#adv_search').click(function(){
    Frame.SearchForm.show();
});

$('#lock-screen').click(function(){
	var nodialog = Cookie.get('lockscreen_nohint');

	if (!nodialog) {
		var d = Frame.Dialog.show({
			title: '{{$LANG.lock_tips}}',
			body: '<div class="screen_lock"><div><span class="icon icon_attention_big"></span><strong>{{$LANG.lock_tips_title}}</strong></div><ul><li>{{$LANG.lock_tips_1}}</li><li>{{$LANG.lock_tips_2}}</li><li>{{$LANG.lock_tips_3}}</li></ul></div>',
			footer: '<input type="checkbox" id="lock-nohint" /><label for="lock-nohint">{{$LANG.never_show_hint}}</label>',
			buttons: [
			   {
				   text: TEXT.CONFIRM,
				   cls: 'btn',
				   events: {click: function(){
				       if ($('#lock-nohint:checked').size()) {
				    	   Cookie.set('lockscreen_nohint', 1, {expires: 10*86400000*365});
				       }
				       d.close();
				       Frame.LockScreen.lock('/logo?oid={{$user.orgid}}');
				   }}
			   },
			   {
	               text: TEXT.CANCEL,
	               cls: 'btn',
	               events: {click: function(){d.close()}}
	           }
			]
		});
	} else {
		Frame.LockScreen.lock('/logo?oid={{$user.orgid}}');
	}
});

$('#user_label_title .tree-ec-icon, #user_label_title a.nobg').click(function(){
	$('#user_label_title .tree-ec-icon').toggleClass('tree-elbow-minus');
	$('#user_label_list').toggle();
});
$('#user_mailbox_title .tree-ec-icon, #user_mailbox_title a.nobg').click(function(){
    $('#user_mailbox_title .tree-ec-icon').toggleClass('tree-elbow-minus');
    $('#user_mailbox_list').toggle();
});

$('#user_board_title .tree-ec-icon').click(function(){
    $(this).toggleClass('tree-elbow-minus');
    $('#user_board_list').toggle();
});

keyhint('#searchinput', 'gray');

})();

var _MAIL_INTERVAL = null;

setTimeout(function(){
	checkMailboxs();
}, 3000);

// 檢查綁定郵箱
function checkMailboxs() {
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '/email/check',
        success: function(ret){
            /*if (ret.message) {
                showMessage(ret.message);
            }*/
            if (ret.data && ret.data.notbind) {
                if (_MAIL_INTERVAL) {
                    clearInterval(_MAIL_INTERVAL);
                    _MAIL_INTERVAL = null;
                }
                $('#user_mailbox_title').css('font-weight', 'normal')
                .find('#mailbox_total').text('');
                return ;
            }
            if (ret.data) {
                var total = 0;
                for (var k in ret.data) {
                    var txt = '';
                    if (undefined != ret.data[k].recent && ret.data[k].recent > 0) {
                        total += parseInt(ret.data[k].recent);
                        txt = '(' + ret.data[k].recent + ')';
                        $('#user_mailbox_list ul li[email="' + k + '"] a').css('font-weight', 'bold')
                        .find('span.mail_count').text(txt);
                    } else {
                    	$('#user_mailbox_list ul li[email="' + k + '"] a').css('font-weight', 'normal')
                        .find('span.mail_count').text('');
                    }
                }

                if (total > 0) {
                	$('#user_mailbox_title').css('font-weight', 'bold')
                	.find('#mailbox_total').text('(' + total + ')');
                } else {
                	$('#user_mailbox_title').css('font-weight', 'normal')
                    .find('#mailbox_total').text('');
                }
            }
        },
        error: function(){}
    });

    if (!_MAIL_INTERVAL) {
    	_MAIL_INTERVAL = setInterval(checkMailboxs, 5 * 60000);
    }
}

// 下载地址
var TALK_URL = '{{$im.downloadurl}}';
if ((/\.NET CLR [2-9]/).test(ua)) {
    TALK_URL = '{{$im.installurl}}';
}

{{if $checklog}}
$(function(){
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: '/frame/ip',
		success: function(ret){
		    if (ret.message) {
		        TOP.showMessage(ret.message, 0, ret.success ? 'success': null);
		    }
		},
		error: function(){}
	});
});
{{/if}}
</script>

</body>
</html>