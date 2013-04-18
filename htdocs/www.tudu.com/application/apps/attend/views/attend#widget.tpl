<div class="content_box">
    <div class="c_box_title">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td>{{$LANG.current_month}}{{foreach item=item key=key from=$count name=count}}{{assign var=category value="category_"|cat:$key}}{{$LANG[$category]}}<strong>{{$item}}</strong>{{$LANG.times}}{{if $smarty.foreach.count.index + 1 < count($count)}},&nbsp;{{/if}}{{/foreach}}</td>
            <td align="right"><a href="/app/attend/index">{{$LANG.details}}<span class="icon icon_triangle"></span></a></td>
          </tr>
        </table>
    </div>
    <div class="c_box_body">
        <table border="0" cellspacing="0" cellpadding="5" width="100%">
            <tr>
                <td>
                    <div id="hours" class="time_box">
                        <span class="time_number number_0"><em>0</em></span><span class="time_number number_0"><em>0</em></span>
                    </div>
                </td>
                <td style="padding: 0">
                    <div class="time_cln"></div>
                </td>
                <td>
                    <div id="minutes" class="time_box">
                        <span class="time_number number_0"><em>0</em></span><span class="time_number number_0"><em>0</em></span>
                    </div>
                </td>
                <td align="center" valign="center">
                    <div><input name="checkin" type="button" class="btn_sign{{if !$checkin}} btn_sign_first first{{/if}}" value="{{$LANG.checkin}}{{if $checkin}}({{$checkin.createtime|date_time_format:"%H:%M"}}){{/if}}"{{if $checkin}} disabled="disabled"{{/if}} /></div>
                    <div style=" margin-top:10px;"><input name="checkout" type="button" class="btn_sign{{if !$checkout}} first{{/if}}" value="{{$LANG.checkout}}{{if $checkout}}({{$checkout.createtime|date_time_format:"%H:%M"}}){{/if}}" /></div>
                </td>
            </tr>
            <tr>
                <td colspan="3"><p class="darkgray">{{$LANG.work_time}}{{$LANG.cln}}{{if $worktime == '0'}}0{{$LANG.hour}}0{{$LANG.min}}{{else}}{{$worktime|replace:":":$LANG.hour|cat:$LANG.min}}{{/if}}</p></td>
            </tr>
        </table>
    </div>
</div>
<script type="text/javascript">
$(function(){
	jQuery.getScript(TOP._SITES.STATIC + '/js/attend/lunar-1.0.0.js', function() {
		new Lunar({hoursTo: $('#hours'), minutesTo: $('#minutes'), lang: '{{$user.option.language|default:"zh_CN"}}'});
	});
	jQuery.getScript(TOP._SITES.STATIC + '/js/attend/attend.js?1004', function() {
		Attend.Checkin.setLang({checkin: '{{$LANG.checkin}}'});
		Attend.Checkin.init();
	});
});
</script>