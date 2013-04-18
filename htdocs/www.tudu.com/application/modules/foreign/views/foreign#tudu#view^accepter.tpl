{{strip}}
<div id="tudu-accepter" class="grid_list_wrap grid_list_group" style="margin:-7px 0 10px; 0">
	<div class="grid_list_title" style="line-height:24px" id="toggle-accepter">
		<span class="icon icon_elbow_minus toggle_accepter" style="cursor:pointer;"></span><h3>{{$LANG.accepter_follow}}</h3><span id="accepter-count"></span>
	</div>
	<table cellspacing="0" class="grid_thead" id="accepter-header">
      <tr>
        <td width="30%" style="line-height:20px"><div class="space"><a href="javascript:void(0);" col="accepterinfo">{{$LANG.column_accepter}}</a></div></td>
        <td width="40%" class="title_line" style="line-height:20px"><div class="space"><a href="javascript:void(0);" col="percent">{{$LANG.percent}}</a></div></td>
        <td class="title_line" style="line-height:20px"><div class="space"><a href="javascript:void(0);" col="elapsedtime">{{$LANG.elapsed}}({{$LANG.hour}})</a></div></td>
        <td width="80" class="title_line" style="line-height:20px"><div class="space"><a href="javascript:void(0);" col="tudustatus">{{$LANG.status}}</a></div></td>
      </tr>
    </table>
	<div id="accepter-list" class="grid_list_group_ct accepter_group">
		<div style="padding:15px 0;text-align:center">{{$LANG.loading_accepter}}</div>
	</div>
</div>
{{/strip}}
<script type="text/javascript">
<!--
var _ACCEPTER_TPL  = '<table id="accepter-{uniqueid}" class="accepter_table" cellspacing="0"><tbody><tr><td width="30%"><a href="/foreign/tudu/view?tid={{$tudu.tuduid}}&ts={{$tsid}}&fid={{$user.uniqueid}}&unid={uniqueid}&back={{$query.back}}">{truename}</a></td><td width="40%"><div class="rate_box"><div style="width:{percent}%;" class="rate_bar"><em>{percent}%</em></div></div></td><td>{elapsedtime}</td><td width="80">{statustext}</td></tr></tbody></table>';
var _ACCEPTERS  = null;
var _COOKIE_KEY = 'TUDU_ACCEPTER_SORT';

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

$('#accepter-header tr td a').bind('click', function(){
	var _this = $(this),
		col   = _this.attr('col'),
		arrow = _this.find('#arrow-accepter-sort'),
		type  = _this.attr('sorttype') == 0 ? 1 : 0;
	
	_ACCEPTERS = sortAccepter(_ACCEPTERS, col, type);
	fillAccepterList(_ACCEPTERS);

	$('#accepter-header tr td a').removeAttr('sorttype');
	changeSortIcon(col, type);

	TOP.Cookie.set(_COOKIE_KEY, '{{$tudu.tuduid}}|' + col + '|' + type);
});

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
		for (var k in records[i]) {
			var reg = new RegExp('{'+k+'}', 'g'),
				val = records[i][k];

			if (k == 'elapsedtime') {
				if (val == 0) {
					val = '-';
				} else {
					val = Math.round(parseInt(val) / 360) / 10;
				}
			} else if (k == 'statustext' && records[i].tudustatus == 3) {
				val = '<span clas="red">' + val + '</span>';
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
    	url: '/foreign/tudu/accepter?tid={{$tudu.tuduid}}&ts={{$tsid}}&fid={{$user.uniqueid}}',
    	success: function(ret){
    		if (ret.data) {
        		$('#accepter-count').text('(' + ret.data.length + ')');

    			var sort = TOP.Cookie.get(_COOKIE_KEY),
    				sortCol  = 'truename',
    				sortType = 0;
				
    			if (sort) {
					sort = sort.split('|');
					
					if (sort.length != 3 || sort[0] != '{{$tudu.tuduid}}') {
						TOP.Cookie.set(_COOKIE_KEY, null);
					} else {
    					sortCol  = sort[1];
    					sortType = sort[2];
					}
    			}
    			
    			_ACCEPTERS = sortAccepter(ret.data, sortCol, sortType);
    			fillAccepterList(_ACCEPTERS);
    			changeSortIcon(sortCol, sortType);
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