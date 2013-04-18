<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.tudu}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.2.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
</head>

<body class="home">
<div class="container">
    <div class="c_right">
        <div class="panel panel_change">
            <div class="panel_top"><div class="panel_t_l"><div class="panel_t_r"><div class="panel_t_m"></div></div></div></div>
            <div id="widget-panel"></div>

  <div class="content_box">
            <div class="c_box_title">
                {{assign var="weekday" value=$smarty.now|date_format:'%w'}}
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td><strong>{{$LANG.weather_today}}<span id="area">{{$LANG.curr_local}}</span>{{$LANG.weather}}</strong> <a href="javascript:void(0);" id="change-area">[{{$LANG.change_area}}]</a></td>
                    <td align="right"><p class="font_c">{{$smarty.now|date_format:'%m-%d'}}  {{assign var=week value="week_"|cat:$weekday}}{{$LANG[$week]}}</p></td>
                  </tr>
                </table>
            </div>
            <div class="c_box_body">
              <table id="weather-ct" width="100%" border="0" cellspacing="0" cellpadding="0" class="weather_box">
              <tr>
                <td align="center" id="weather-wrap" >
                <table id="weather-table" style="display:none" border="0" cellspacing="0" cellpadding="0" class="weather_box">
                  <tr id="forecast-list">
                    <td id="current-info" align="left" style="padding:0 10px 0 0">
                    <div class="weather_now">
                      <strong class="f14" id="curr-temp"></strong>
                      <p>{{$LANG.current}}{{$LANG.cln}}<span id="curr-condition"></span></p>
                      <p id="curr-humidity"></p>
                    </div>
                    </td>
                  </tr>
                </table>
                <div id="weather-message" style="padding:20px 0"></div>
              </td>
              </tr>
              </table>
          </div>
        </div>
  <div class="content_box">
            <div class="c_box_title"><strong>{{$LANG.notice}}</strong><span class="icon Rnu"></span></div>
            <div class="c_box_body">
                <ul>
                {{foreach item=item from=$notices}}
                    <li><span class="icon icon_square"></span><a href="/tudu/view?tid={{$item.tuduid}}">{{$item.subject|escape:'html'}}   ({{$item.createtime|date_format:'%Y-%m-%d'}})</a></li>
                {{foreachelse}}
                    <li style="padding:20px 0;text-align:center">{{$LANG.notice_is_null}}</li>
                {{/foreach}}
                </ul>
            </div>
         </div>

         </div>

    </div>
  <div class="c_left">
        {{assign var="hour" value=$smarty.now|date_format:'%H'|intval}}
        <div class="user">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                 <td rowspan="2" width="60"><div class="user_photo"><a href="/setting/account"><img class="user_mask" src="/logo?unid={{$user.uniqueid}}"></a></div></td>
                <td><strong class="f14">{{if $hour > 6 && $hour < 13}}{{$LANG.good_morning}}{{elseif $hour >= 13 && $hour < 18}}{{$LANG.good_afternoon}}{{else}}{{$LANG.good_evening}}{{/if}}{{$LANG.comma}}{{$user.truename}} </strong></td>
                <td></td>
              </tr>
              <tr>
                <td>{{$LANG.tudu}}{{$LANG.cln}}<strong>{{$labels.inbox.unreadnum}}</strong><span class="font_c">{{$LANG.tudu_unit}}<a href="/tudu/?search=inbox&unread=1">{{$LANG.unread_tudu}}</a>{{$LANG.comma}}</span><strong>{{$labels.starred.totalnum}}</strong><span class="font_c">{{$LANG.tudu_unit}}<a href="/tudu/?search=starred">{{$LANG.starred_tudu}}</a></span></td>
                <td align="right">{{if 0}}<a href="#">我执行</a> | <a href="#">我发起</a> | <a href="#">讨论</a> | <a href="#">公告</a>{{/if}}</td>
              </tr>
            </table>
        </div>
        <div class="line_part"></div>
        <div class="panel panel_border">
            <div class="panel_header"><strong class="f14">{{$LANG.new_tudu}}</strong></div>
            <div class="panel_body">

                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                  {{foreach name=tudu item=tudu from=$tudus}}
                    <td>
                    <div class="infor_div">
                        <div class="photo_wrap"><img src="/logo?email={{$tudu.from.1}}@{{$tudu.from.2}}" /></div>

                        <div class="infor_box">
                            <p>{{if $tudu.sender == $user.username}}{{$LANG.me}}{{else}}{{$tudu.from.0}}{{/if}} <span class="gray"></span></p><p class="blue"><span class="icon R{{if $tudu.type=='discuss'}}d{{elseif $tudu.type=='notice'}}n{{else}}r{{/if}}u{{if $tudu.type == 'task' && $tudu.cycleid}}_c{{/if}}"></span>&nbsp;<a id="{{$tudu.tuduid|replace:$user.uniqueid:''|replace:'-':''}}" href="/tudu/view?tid={{$tudu.tuduid}}" title="{{$tudu.subject|escape:'html'}}">{{$tudu.subject|escape:'html'}}</a></p>
                            {{if $tudu.type == 'task'}}
                            <div class="execute_wrap"><span class="execute">{{$LANG.accept_user}}{{$LANG.cln}}{{if count($tudu.accepter) > 1}}{{$LANG.multi_accepter}}{{elseif $tudu.accepter.0 == $user.username}}{{$LANG.me}}{{else}}{{$tudu.to[$tudu.accepter.0].0|default:'-'}}{{/if}}</span>
                                {{if $tudu.type != 'task' || $tudu.appid == 'attend' || $tudu.flowid}}
                                <div class="rate_box rate_box2"><div class="rate_bar" style="width:100%;"></div></div>
                                {{else}}
                                <div class="rate_box"><div class="rate_bar" style="width:{{$tudu.percent|default:0}}%;"><div class="rate_percent"><em>{{$tudu.percent|default:'0'}}%</em></div></div></div>
                                {{/if}}
                            </div>
                            {{/if}}
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    {{if $smarty.foreach.tudu.index < 2 && count($tudus) > 2}}
                    <div class="line_part_2"></div>
                    {{/if}}
                    </td>
                    {{if ($smarty.foreach.tudu.index + 1) % 2 == 0}}
                    </tr>
                    <tr>
                    {{/if}}
                    {{/foreach}}
                  </tr>
                </table>
            </div>
        </div>
        <div class="panel panel_border">
            <div class="panel_header"><strong class="f14">{{$LANG.commend_func}}</strong></div>
            <div class="panel_body">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td>
                        <div class="infor_div">
                            <span class="icon_big i_b_board"></span><a href="/board"><strong class="f14">{{$LANG.tudu_board}}</strong></a>
                        </div>
                        <div class="line_part3"></div>
                    </td>
                    <td>
                        <div class="infor_div">
                            &nbsp;
                        </div>
                         <div class="line_part3"></div>
                    </td>
                  </tr>
                  <tr>
                    <td>
                        <div class="infor_div">
                        <span class="icon_big i_b_skin"></span><a href="/setting/skin"><strong class="f14">{{$LANG.change_skin}}</strong></a>
                        </div>
                    </td>
                    <td>
                        <div class="infor_div">
                        <span class="icon_big i_b_tab"></span><a href="/label/"><strong class="f14">{{$LANG.label}}</strong></a>
                        </div>
                    </td>
                  </tr>
                </table>
            </div>
        </div>
        <div class="panel">
        	<p style="padding:2px 0;"><a href="http://www.tudu.com/" target="_blank">{{$LANG.tudu_web}}</a>&nbsp;|&nbsp;<a href="http://www.tudu.com/help/index.html" target="_blank">{{$LANG.tudu_help}}</a>&nbsp;|&nbsp;<a href="mailto:800@tudu.com" target="_blank">{{$LANG.give_feedback}}</a>&nbsp;|&nbsp;<a href="http://weibo.com/tudu1" target="_blank">{{$LANG.tudu_weibo}}</a></p>
        	<p style="padding:2px 0;" class="gray">{{$LANG.tudu_version}}{{$LANG.cln}}{{$smarty.const.TUDU_VERSION}}&nbsp;|&nbsp;Copyright © 2013 Tudu. All Rights Reserved.</p>
        </div>
    </div>
</div>

<div class="pop_wrap" id="area-panel" style="width:300px;position:absolute;display:none">
<div class="c_box_body" style="margin:0">
<form id="localform" method="post" action="">
<input type="text" class="input_text" style="width:150px" name="local" id="local_input" value="" /> <button type="submit" id="local_submit">{{$LANG.change_area}}</button><button type="button" id="local_cancel">{{$LANG.cancel}}</button>
</form>
</div>
</div>

</body>
<script type="text/javascript">
$(function(){
	TOP.Label.setLabels({{format_label labels=$labels}});
	TOP.Frame.title('TUDU');
	TOP.Frame.hash('');

	setTimeout(function(){
	    TOP.Label.refreshMenu().focusLabel();
	}, 100);

	var ts = new Date().getTime() / 1000;
    if (TOP._CACHE.weather && TOP._CACHE.weather.expired > ts) {
    	showWeather(TOP._CACHE.weather);
    } else {
		loadWeather(function(ret){
			if (ret.success) {
				TOP._CACHE.weather = ret.data;

				if (TOP._CACHE.weather) {
				    TOP._CACHE.weather.expired = ts + 3600;
				}

				showWeather(TOP._CACHE.weather);
			} else {
			    TOP.showMessage(ret.message);
			}
		}, null, TOP.Cookie.get('location_code'));
    }

    $('#change-area').click(function(e){
        var offset = $('#weather-ct').parent().offset();
        var panel = $('#area-panel');

        panel.css({left: offset.left + 'px', top: offset.top + 'px'});

        panel.toggle();

        $('#local_input').val('').focus();

        TOP.stopEventBuddle(e);
    });

    $(window).bind('click', function(){
        $('#area-panel').hide();
    });
    $('#local_cancel').bind('click', function(){
        $('#area-panel').hide();
    });
    $('#area-panel').click(function(e){TOP.stopEventBuddle(e);});

    $('#localform').submit(function(){return false;});
    $('#localform').submit(function(){
        var local = $('#local_input').val();
    	TOP.Cookie.set('location_code', encodeURIComponent(local), {expires: 86400000 * 7});
    	$('#area-panel').hide();
    	$('#area').text(local);

    	$('#weather-table').hide();
        $('#weather-message').text('{{$LANG.weather_loading}}').show();

    	loadWeather(function(ret){
            if (ret.success) {
                TOP._CACHE.weather = ret.data;
                TOP._CACHE.weather.expired = ts + 3600;

                showWeather(TOP._CACHE.weather);
            } else {
                TOP.showMessage(ret.message);
            }
        }, null, encodeURIComponent(local), true);
    });
});

//加载天气
function loadWeather(callback, lang, loc) {
    var TOP = getTop(),
        $   = TOP.getJQ();

    // get location by HTML5 API
    /*if (!loc && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(loc){
                loc = ',,,' + loc.coords.latitude * 1000000 + ',' + loc.coords.longitude * 1000000;
                TOP.loadWeather(callback, lang, loc, remeberLoc);
            },
            function(){TOP.showMessage('xxx');}
        );
    }*/

    if (!loc) {
        loc = '';
    }

    if (!lang) {
        lang = '';
    }

    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '/frame/weather?lang=' + lang + '&loc=' + loc,
        success: function(ret) {
            callback(ret);
        },
        error: function() {
            TOP.showMessage(TOP.TEXT.LOAD_WEATHER_FAILURE);
        }
    });
}

// 显示天气
function showWeather(data) {
    if (!data || (!data.current && !data.forecast)) {
        $('#weather-table').hide();
        $('#weather-message').text('{{$LANG.weather_null}}').show();
        return ;
    }

	var area = null;

    area = data.postal_code ? data.postal_code : data.city;
    $('#area').text(area);

    if (data.postal_code) {
        TOP.Cookie.set('location_code', encodeURIComponent(data.postal_code), {expires: 86400000 * 7});
    }

    $('#forecast-list td:not(#current-info)').remove();
    if (data.current) {
        for (var k in data.current) {
            $('#curr-' + k).text(data.current[k]);
        }
    }

    if (data.forecast) {
        var forecast = data.forecast,
            list = $('#forecast-list');
        for (var i = 0, c = forecast.length; i < c; i++) {
            if (i > 2) {
                break;
            }

            var td = $('<td style="padding: 0 5px;">').css({'text-align': 'center'});
            var weekday = forecast[i].day_of_week;
            var temp    = forecast[i].low+'/'+forecast[i].high+'&deg;C';
            if (i == 0) {
                weekday = '{{$LANG.date_today}}';
                $('#curr-temp').html(temp);
            }
            var icon = forecast[i].icon.split('/');
            icon = icon[icon.length - 1];
            td.append('<p class="f14">' + weekday + '</p>');
            td.append('<img src="{{$options.sites.static}}/images/weather/' + icon + '" title="'+forecast[i].condition+'" alt="'+forecast[i].condition+'" />');
            td.append('<p>'+temp+'</p>');
            list.append(td);
        }
    }

    $('#weather-table').show();
    $('#weather-message').text('').hide();
}

// 考勤
if (undefined != TOP._APPLIST.attend) {
    $('#widget-panel').load('/app/attend/widget');
}
</script>

</html>

