<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>{{$label.displayname}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script type="text/javascript">
function getTop() {
    return parent;
}

var TOP = getTop();

</script>
</head>

<body>

{{if $label}}
<div class="position">
  {{if !$unread}}
  {{if $label.issystem}}
  {{strip}}
  <p><span class="fr"><span class="icon icon_view_list"></span><a href="/tudu/?search={{$label.labelalias}}" name="tuduchart">&nbsp;{{$LANG.switch_standard_view}}</a></span><strong class="title">{{$label.displayname}}</strong>{{if $label.labelalias != 'drafts'}}({{$LANG.label_total|sprintf:$label.totalnum}} <span id="ur_ct"{{if $label.unreadnum <= 0}} style="display:none"{{/if}}>{{$LANG.among}}{{$LANG.comma}}<a href="/tudu/?search={{$label.labelalias}}&unread=1" class="red">{{$LANG.unread_tudu}}</a>&nbsp;<span id="l_unread">{{$label.unreadnum}}</span> {{$LANG.tudu_unit}}</span>){{else}}({{$LANG.label_total|sprintf:$label.totalnum}}{{$LANG.tudu}}){{/if}}
  {{if $label.labelalias == 'done' && $label.unreadnum > 0}}
  &nbsp;&nbsp;{{$LANG.mark_all}}<a href="javascript:void(0)" class="red" onclick="markLabelRead('{{$label.labelalias}}');">{{$LANG.read}}</a>
  {{/if}}
  </p>
  {{/strip}}
  {{else}}
  <p><strong class="title">{{$label.displayname}}</strong>{{if $label.labelalias != 'drafts'}}({{$LANG.label_total|sprintf:$label.totalnum}} <span id="ur_ct"{{if $label.unreadnum <= 0}} style="display:none"{{/if}}>{{$LANG.among}}{{$LANG.comma}}<a href="/tudu/?search=cat&cat={{$label.labelalias}}&unread=1" class="red">{{$LANG.unread_tudu}}</a>&nbsp;<span id="l_unread">{{$label.unreadnum}}</span> {{$LANG.tudu_unit}}</span>){{else}}({{$LANG.label_total|sprintf:$label.totalnum}}{{$LANG.tudu}}){{/if}}</p>
  {{/if}}
  {{else}}
  {{if $label.issystem}}
  <p><strong class="title">{{$LANG.unread_tudu}}</strong>| <a href="/tudu/?search={{$label.labelalias}}">{{$LANG.back}} "{{$label.displayname}}"</a></p>
  {{else}}
  <p><strong class="title">{{$LANG.unread_tudu}}</strong>| <a href="/tudu/?search=cat&cat={{$label.labelalias}}">{{$LANG.back}} "{{$label.displayname}}"</a></p>
  {{/if}}
  <div class="searchtip">{{$LANG.label_unread|sprintf:$label.displayname:$label.unreadnum}}{{$LANG.unread_tudu}}</div>
  {{/if}}
</div>
{{/if}}

<div class="panel-body">
  <div id="float-toolbar" class="float-toolbar">
  {{include file="ganttchart^toolbar.tpl"}}
  <table cellspacing="0" class="grid_thead">
    <tr>
      <td style="line-height:20px;"><div class="space"><a href="javascript:void(0);" onClick="submitSort('?{{$query}}', 1,{{$sort[1]}});return false;">{{$LANG.subject}}{{if $sort[0]==1}}{{if $sort[1]==0}}↑{{else}}↓{{/if}}{{/if}}</a></div></td>
      <td width="100" class="title_line" style="line-height:20px;"><div class="space">{{$LANG.percent}}</div></td>
      <td width="650" style="line-height:20px;padding:0">
        <table cellspacing="0" cellpadding="0" border="0" class="gantt_header" width="100%" height="22">
          <tr>
          {{foreach item=item from=$headers name=header}}
          {{if $type == 'week'}}
          {{assign var=weekday value='D'|date:$item|strtolower}}
          {{assign var=weekkey value='date_'|cat:$weekday}}
          <td width="1%">{{$LANG[$weekkey]}}({{$item|date_format:'%m-%d'}})</td>
          {{else}}
          {{if ($smarty.foreach.header.index + 1) % 5 == 0 || $smarty.foreach.header.index == 0}}
          <td width="1%">{{$item|date_format:'%d'}}</td>
          {{else}}
          <td width="1%" class="half_border"><div>&nbsp;&nbsp;&nbsp;</div></td>
          {{/if}}
          {{/if}}
          {{/foreach}}
          </tr>
        </table>
      </td>
    </tr>
  </table>
  </div>
  <div id="toolbar">{{include file="ganttchart^toolbar.tpl"}}</div>
  {{include file="tudu#index^list-gantt.tpl"}}
</div>
<script type="text/javascript">
var Calendar = Calendar || {};
Calendar.Tips = {
    html: '<div class="float_tips"><div class="float_tips_body"></div></div>',

    ele: null,

    show: function(content, left, top) {
       if (null === this.ele) {
           this.ele = $(this.html);
           this.ele.appendTo(document.body).hide();
       }

       this.ele.find('.float_tips_body').html(content);
       this.ele.show();

       var width = this.ele.width(),
           bodyWidth = $(document.body).width(),
           pos = {
    	       left : left ? left + 20 : 0,
    	       top : top ? top + 10 : 0
           };

       if (width + left > bodyWidth) {
   	       pos.left = bodyWidth - width - 25;
       }

       this.ele.css({
           left : pos.left + 'px',
           top : pos.top + 'px'
       });
    },

    hide: function() {
        if (this.ele) {
            this.ele.find('.float_tips_body').empty();
            this.ele.hide();
        }
    }
};

$(function(){
	TOP.Frame.title('{{$label.displayname}}');
	TOP.Label.focusLabel('');
	TOP.Frame.hash('m=tudu&search={{$label.labelalias}}&chart=gantt&type={{$type}}&sd={{$startdate}}');

	var total = 0, unread = 0;
	$('.grid_list_wrap table.grid_list_2').each(function(){
		var o = $(this), tuduid = o.attr('_tuduid');
		total ++;

		if (o.hasClass('unread')) {
		    unread++;
		}

		o.mouseover(function(e){
			o.addClass('over');

			var subject   = o.find('a[name="subject"]').attr('_title'),
			    startTime = o.attr('_st'),
			    endTime   = o.attr('_et');

		    o.find('a[name="subject"],div.gantt_bar').mousemove(function(e){
		    	var scrollTop = document.documentElement ? document.documentElement.scrollTop : document.body.scrollTop;

		    	Calendar.Tips.show('<p>'+subject+'</p><p>{{$LANG.time}}{{$LANG.cln}}'+startTime+' - '+endTime+'</p>', e.clientX, e.clientY + scrollTop);
		    })
		    .mouseout(function(){
		    	Calendar.Tips.hide();
			});
		})
		.mouseout(function(){
			o.removeClass('over');
		});

		initCollspan(o);
	});

	$('#tudu-total').text(total);
	if (unread > 0) {
	    $('#l_unread').text(unread);
	    $('#ur_ct').show();
	}

	{{if $label.labelalias == 'inbox' || $label.labelalias == 'todo' || $label.labelalias == 'review'}}
	$('div.grid_list_title span.toggle_tudu').click(function(){
		var o = $(this),
		    wrap = o.parents('div.grid_list_group'),
		    expanded = o.hasClass('icon_elbow_minus');

		wrap.find('div.grid_list_group_ct').toggle();
		if (expanded) {
			o.removeClass('icon_elbow_minus').addClass('icon_elbow_plus');
		} else {
			o.removeClass('icon_elbow_plus').addClass('icon_elbow_minus');
		}
	});
	{{/if}}

	new FixToolbar({
    	src: '#toolbar',
    	target: '#float-toolbar'
    });
});

function initCollspan(o) {
    var treeicon = o.find('.tree-ec-icon'),
        tuduid   = o.attr('_tuduid'),
        chct     = o.next('div.gantt_children_list');
    treeicon.click(function(){
        var me = $(this);

        if (me.hasClass('tree-elbow-minus')) {
            me.removeClass('tree-elbow-minus');

            chct.hide();
        } else {
            me.addClass('tree-elbow-minus');

            if (!chct.find('.grid_list_2').size()) {
                if (tuduid.indexOf('ch-') != -1) {
                    var tid = tuduid.replace('ch-', '');
                } else {
                    var tid = tuduid;
                }
                chct.load('/calendar/children?currUrl={{$smarty.server.REQUEST_URI|escape:'url'}}&type={{$type}}&sd={{$startdate}}&ed={{$enddate}}&tid=' + tid, function(){
                    chct.find('.grid_list_2').each(function(){
                        var child = $(this), id = child.attr('_tuduid');
                        child.mouseover(function(e){
                            child.addClass('over');

                            var subject   = child.find('a[name="subject"]').attr('_title'),
                                startTime = child.attr('_st'),
                                endTime   = child.attr('_et');

                            child.find('a[name="subject"]:eq(0),div.gantt_bar:eq(0)').mousemove(function(e){
                                var scrollTop = document.documentElement ? document.documentElement.scrollTop : document.body.scrollTop;

                                var html = '<p>'+subject+'</p><p>{{$LANG.time}}{{$LANG.cln}}'+startTime+' - '+endTime+'</p>';
                                if (child.attr('_previd')) {
                                    var p = child.parent().find('table.grid_list_2[_tuduid="'+child.attr('_previd')+'"]:eq(0)');
                                    if (p.size()) {
                                        html += '<p>{{$LANG.prev_tudu}}{{$LANG.cln}}'+p.find('a[name="subject"]').attr('_title')+'</p>';
                                    }
                                }

                                Calendar.Tips.show(html, e.clientX, e.clientY + scrollTop);
                            })
                            .mouseout(function(){
                                Calendar.Tips.hide();
                            });
                        })
                        .mouseout(function(){
                            child.removeClass('over');
                        });

                        initCollspan(child);
                    });
                });
            }

            chct.show();
        }
    });
}

function submitSort(url, sorttype, sortasc) {
    if (sorttype != '{{$sort[0]}}') {
        sortasc = 0;
    }
    location = '/tudu/' + url + '&chart=gantt&type={{$type}}&sorttype=' + sorttype + '&sortasc=' + sortasc;
}
</script>
</body>
</html>