<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>打印预览</title>
<style type="text/css">
body{
	font-size:12px;
	color:#000;
	font-family:Verdana, Geneva, sans-serif;
	text-align:center;
	margin:0;
	padding:0;
	line-height:1.6em;
}
div,p{ margin:0; padding:0;}
.wrap{ margin:0 10px; text-align:left;}
.btn{ line-height:1em; height:20px;*padding-top:2px;}
.border_b{
	padding:10px 0; 
	border-bottom:1px solid #000
}
.border_b .post_content{text-indent:24px;}
.option_percent_bar{
	border:#999 1px solid;
    display:inline-block;
    zoom:1;
    *display:inline;
    width:100px;
    margin:0 10px;
}
.option_percent_bar, .option_percent_bar div {
	font-size:0;
    line-height:0;
    height:8px;
}
.option_percent_bar div {
	background-color:#ccc;
}
</style>

<style type="text/css" media="print">
<!--
.tools-np {
	display:none
}
-->
</style>
</head>
<body>
<div id="wrap" class="wrap">
    <div class="tools-np" style="margin-top:10px"><input class="btn" name="" type="button" value="{{$LANG.print}}" style="margin-right:5px;" onclick="doPrint()"/></div>
    <div id="print-area">
	<div>
        <p style="padding-top:5px;font-weight:bold;">{{$LANG.subject}}{{$LANG.cln}}{{if $tudu.classid}}[{{$tudu.classname}}]{{/if}}{{$tudu.subject}}</p>
        <p style="border-bottom:1px solid #000;font-weight:bold;">{{$LANG.attachment}}{{$LANG.cln}}{{$tudu.attachnum}}{{$LANG.attach_unit}}&nbsp;&nbsp;&nbsp;&nbsp;{{$LANG.reply}}{{$LANG.cln}}{{$tudu.replynum}}</strong></p>
    </div>
    <div class="border_b">
    	<div style="margin-bottom:10px;">
            <p>{{$LANG.title_sender}}{{$tudu.from.0}}&nbsp;&nbsp;&nbsp;&nbsp;{{$LANG.title_receiver}}{{$tudu.to.0}}&nbsp;&nbsp;&nbsp;&nbsp;{{$LANG.title_cc}}{{foreach item=cc from=$tudu.cc name=cc}}{{if !$smarty.foreach.cc.first}},{{/if}}{{$cc.0}}{{foreachelse}}-{{/foreach}}</p>
            {{if $tudu.type == 'task'}}
            <p>{{$LANG.title_starttime}}{{$tudu.starttime|date_time_format:$user.option.dateformat:'date'|default:'-'}}&nbsp;&nbsp;&nbsp;&nbsp;{{$LANG.title_endtime}}{{$tudu.endtime|date_time_format:$user.option.dateformat:'date'|default:'-'}}&nbsp;&nbsp;&nbsp;&nbsp;{{$LANG.title_progress}}{{$tudu.percent}}%</p>
            {{/if}}
    	</div>
        <div>
        	<div><strong>{{$LANG.detail}}{{$LANG.cln}}</strong></div>
        	<div>{{$tudu.content|tudu_format_content}}</div>
        	{{if $vote}}
        	<table cellpadding="0" cellspacing="0" border="0">
        	{{foreach name=option item=option from=$vote.options}}
                <tr id="option-{{$option.optionid}}">
                    <td valign="top">{{$option.text|escape:'html'}}</td>
                    <td valign="top" width="280">
                        <div class="option_box">
                            <div class="option_percent_bar">
                                <div class="option_percent_{{math|chr equation="x%4+97" x=$smarty.foreach.option.index}}" style="width:{{if $vote.votecount > 0}}{{math equation="(x/y)*100" x=$option.votecount y=$vote.votecount}}{{else}}0{{/if}}%" id="option-{{$option.optionid}}-percent"></div>
                             </div><span id="option-{{$option.optionid}}-info" class="option_info"><em id="option-{{$option.optionid}}-count">{{$option.votecount}}</em><em id="option-{{$option.optionid}}-percent" class="gray">({{if $vote.votecount > 0}}{{math equation="round((x/y)*100, 2)" x=$option.votecount y=$vote.votecount}}{{else}}0{{/if}}%)</em></span>
                        </div>
                    </td>
                </tr>
                {{/foreach}}
            </table>
        	{{/if}}
        </div>
    </div>
    {{foreach item=post from=$posts name="post"}}
    {{if !$post.isfirst}}
    <div class="border_b">
    	<div><strong>({{$smarty.foreach.post.index}}{{$LANG.post_floor}}) {{$post.poster}} {{$post.createtime|date_time_format:$user.option.dateformat}}</strong></div>
        <div class="post_content">{{$post.content|tudu_format_content}}</div>
    </div>
    {{/if}}
    {{/foreach}}
    </div>
    <div class="tools-np" style="margin:20px 0"><input class="btn" name="" type="button" value="{{$LANG.print}}" onclick="doPrint()" /></div>
</div>
<script type="text/javascript">
<!--
function doPrint() {
	try {
		window.print();
	} catch (e) {
	    alert('{{$LANG.print_err}}');
	}
}
-->
</script>
</body>
</html>