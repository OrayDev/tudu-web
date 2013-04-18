{{strip}}
<div id="tudu-accepter" class="grid_list_wrap grid_list_group" style="margin:-7px 0 10px; 0">
	<div class="grid_list_title" style="line-height:18px" id="toggle-accepter">
		<span class="icon toggle_accepter icon_elbow_plus" style="cursor:pointer;"></span><h3>{{$LANG.attendee}}</h3><span id="accepter-count"></span>
	</div>
	<table cellspacing="0" class="grid_thead" id="accepter-header" style="display:none;">
      <tr>
        <td style="line-height:20px"><div class="space"><a href="javascript:void(0);" col="accepterinfo" class="accepterinfo">{{$LANG.attendee}}</a></div></td>
        <td width="80" class="title_line" style="line-height:20px"><div class="space"><a href="javascript:void(0);" col="tudustatus" class="tudustatus">{{$LANG.status}}</a></div></td>
      </tr>
    </table>
	<div id="accepter-list" class="grid_list_group_ct accepter_group" style="display:none;">
		<div style="padding:15px 0;text-align:center">{{$LANG.loading_attendee}}</div>
	</div>
</div>
{{/strip}}
<script type="text/javascript">
<!--
var _ACCEPTER_TPL  = '<table id="accepter-{uniqueid}" class="accepter_table" cellspacing="0"><tbody><tr><td><a _email="{accepterinfo}" _url="/contact/view?email={accepterinfo}&back={{$currUrl}}" href="/tudu/view?tid={{$tudu.tuduid}}&unid={uniqueid}&back={{$currUrl}}">{truename}</a></td><td width="80">{statustext}</td></tr></tbody></table>';
var _ACCEPTERS  = null;

$('#toggle-accepter span, #toggle-accepter h3').click(function(){
	var ico = $('#toggle-accepter span.icon');
	if (ico.hasClass('icon_elbow_minus')) {
		ico.removeClass('icon_elbow_minus').addClass('icon_elbow_plus');
	} else {
		ico.removeClass('icon_elbow_plus').addClass('icon_elbow_minus');
	}
	$('#accepter-list').toggle();
	$('#accepter-header').toggle();
});

$('#accepter-header tr td a.accepterinfo').bind('click', function(){
	var _this = $(this),
		col   = _this.attr('col'),
		arrow = _this.find('#arrow-accepter-sort'),
		type  = _this.attr('sorttype') == 0 ? 1 : 0;

	_ACCEPTERS = sortAccepter(_ACCEPTERS, col, type);
	fillAccepterList(_ACCEPTERS);

	$('#accepter-header tr td a').removeAttr('sorttype');
	changeSortIcon(col, type);
});

$('#accepter-header tr td a.tudustatus').bind('click', function(){
	var _this = $(this),
		col   = _this.attr('col'),
		arrow = _this.find('#arrow-accepter-sort'),
		type  = _this.attr('sorttype') == 0 ? 1 : 0;

	_ACCEPTERS = sortStatus(_ACCEPTERS, col, type);
	fillAccepterList(_ACCEPTERS);

	$('#accepter-header tr td a').removeAttr('sorttype');
	changeSortIcon(col, type);
});

function sortStatus(records, col, type) {
	if (!records.length || records.length <= 1) {
		return records;
	}

	var accept = [], reject = [], wait = [], unread = [];
	for (var i = 0, l = records.length; i < l; i++) {
		if (records[i].tudustatus < 2) {
    		if (records[i].isread == 0 || null === records[i].isread) {
    			unread.push(records[i]);
    		} else {
    			if (records[i].accepttime !== null) {
    				accept.push(records[i]);
    			} else {
    				wait.push(records[i]);
    			}
    		}
		}
		if (records[i].tudustatus == 3) {
			reject.push(records[i]);
		}
	}

	sorts = new Array();
	if (type == 0) {
		return sorts.concat(accept, reject, wait, unread);
	} else {
		return sorts.concat(unread, wait, reject, accept);
	}
}

//
function changeSortIcon(col, type) {
	var a = $('#accepter-header a[col="'+col+'"]'),
		arrow = a.find('#arrow-accepter-sort');

	if (!arrow.size()) {
		$('#arrow-accepter-sort').remove();
		arrow = $('<span id="arrow-accepter-sort"></span>');
		a.append(arrow);
	}

	var arrowText = type == 1 ? '↑' : '↓';
	arrow.text(arrowText);
	a.attr('sorttype', type);
}

// 执行人排序
function sortAccepter(records, col, type) {
	if (!records.length || records.length <= 1) {
		return records;
	}

	records.sort(function(a, b){
		var x1 = a[col],x2 = b[col];
		if (col == 'elapsedtime') {
			x1 = parseFloat(x1);
			x2 = parseFloat(x2);
		}

		var b = (type == 1 ? x1 > x2 : x1 < x2);
		return (b ? 1 : -1);
	});

	return records
}

function fillAccepterList(records) {
	var list = $('#accepter-list').empty();
	for (var i = 0, l = records.length; i < l; i++) {
		var html = _ACCEPTER_TPL;

		if (records[i].tudustatus < 2) {
			if (records[i].isread == 0 && !records[i].accepttime) {
				records[i].statustext = '{{$LANG.unread}}';
			} else {
				records[i].statustext = records[i].accepttime ? '{{$LANG.status_accepted}}' : '{{$LANG.status_waitaccept}}';
			}
		}

		for (var k in records[i]) {
			var reg = new RegExp('{'+k+'}', 'g'),
				val = records[i][k];

			if (k == 'truename') {
				if (val == '{{$user.truename}}') {
					val = '{{$LANG.me}}';
				}
				if (records[i].forwardfrom) {
					val += '&nbsp;(' + records[i].forwardfrom + '&nbsp;' + TOP.TEXT.VIEW_INVITE +')';
				}
			}

			if (k == 'accepterinfo') {
				val = val.split(' ')[0];
			}

			html = html.replace(reg, val !== null ? val : '&nbsp;');
		}
		list.append(html);
	}
}

function loadAccepter() {
	$.ajax({
    	type: 'GET',
    	dataType: 'json',
    	url: '/tudu/accepter?tid={{$tudu.tuduid}}',
    	success: function(ret){
    		if (ret.data) {
        		$('#accepter-count').text('(' + ret.data.length + ')');

    			var sortCol  = 'tudustatus',
    				sortType = 0;

    			_ACCEPTERS = sortStatus(ret.data, sortCol, sortType);
    			fillAccepterList(_ACCEPTERS);
    			changeSortIcon(sortCol, sortType);

    			var aceptCard = new Card();
    			$('table.accepter_table').find('a').each(function(){
    				$(this).bind('mouseover', function(){
    					aceptCard.show(this, 2000);
    				})
    				.bind('mouseout', function(){
    					aceptCard.hide();
    				});
    			});
    		}
    	},
    	error: function(res){
        	$('#accepter-list').html('<div style="padding:15px 0;text-align:center"><a href="javascript:void(0)" onclick="loadAccepter()">{{$LANG.load_accepter_failure}}</a></div>');
        }
    });
}

$(window).bind('load', function(){
	loadAccepter();
});
-->
</script>