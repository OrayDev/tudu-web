<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.general}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
</head>
<body>
<div class="position">
    <p><strong class="title">{{$LANG.general}}</strong></p>
</div>
<form action="/setting/save" id="theform" method="post" class="tab_panel">
    {{include file="setting^tab.tpl"}}
    <div class="tab-panel-body">
        <div class="settingbox">
            <div class="setting_common">
                <div class="settingdiv">
                    <h3 class="setting_tit">{{$LANG.language}}</h3>
                    <div class="line_bold"></div>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="right" width="150">{{$LANG.default_lang}}{{$LANG.cln}}</td>
                            <td><select name="language">
                            <option value="zh_CN"{{if $user.option.language == 'zh_CN'}} selected="selected"{{/if}}>{{$LANG.lang_zh_cn}}</option>
                            <option value="zh_TW"{{if $user.option.language == 'zh_TW'}} selected="selected"{{/if}}>{{$LANG.lang_zh_tw}}</option>
                            <option value="en_US"{{if $user.option.language == 'en_US'}} selected="selected"{{/if}}>{{$LANG.lang_en_us}}</option>
                            </select></td>
                        </tr>
                    </table>
                </div>
                <div class="settingdiv">
                    <h3 class="setting_tit">{{$LANG.font_settings}}</h3>
                    <div class="line_bold"></div>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="right" width="150">{{$LANG.default_fontfamily}}{{$LANG.cln}}</td>
                            <td><select name="fontfamily">
                            <option value="">{{$LANG.default_fontfamily}}</option>
                            <option value="SimSun"{{if $user.option.fontfamily == 'SimSun'}} selected="selected"{{/if}}>{{$LANG.simsun}}</option>
                            <option value="NSimSun"{{if $user.option.fontfamily == 'NSimSun'}} selected="selected"{{/if}}>{{$LANG.nsimsun}}</option>
                            <option value="FangSong_GB2312"{{if $user.option.fontfamily == 'FangSong_GB2312'}} selected="selected"{{/if}}>{{$LANG.fangsong_GB2312}}</option>
                            <option value="KaiTi_GB2312"{{if $user.option.fontfamily == 'KaiTi_GB2312'}} selected="selected"{{/if}}>{{$LANG.kaiti_GB2312}}</option>
                            <option value="SimHei"{{if $user.option.fontfamily == 'SimHei'}} selected="selected"{{/if}}>{{$LANG.simhei}}</option>
                            <option value="Microsoft YaHei"{{if $user.option.fontfamily == 'Microsoft YaHei'}} selected="selected"{{/if}}>{{$LANG.microsoft_yahei}}</option>
                            <option value="Arial"{{if $user.option.fontfamily == 'Arial'}} selected="selected"{{/if}}>Arial</option>
                            <option value="Arial Black"{{if $user.option.fontfamily == 'Arial Black'}} selected="selected"{{/if}}>Arial Black</option>
                            <option value="Times New Roman"{{if $user.option.fontfamily == 'Times New Roman'}} selected="selected"{{/if}}>Times New Roman</option>
                            <option value="Courier New"{{if $user.option.fontfamily == 'Courier New'}} selected="selected"{{/if}}>Courier New</option>
                            <option value="Tahoma"{{if $user.option.fontfamily == 'Tahoma'}} selected="selected"{{/if}}>Tahoma</option>
                            <option value="Verdana"{{if $user.option.fontfamily == 'Verdana'}} selected="selected"{{/if}}>Verdana</option>
                            </select></td>
                        </tr>
                        <tr>
                            <td align="right" width="150">{{$LANG.default_fontsize}}{{$LANG.cln}}</td>
                            <td><select name="fontsize">
                            <option value="">{{$LANG.default_fontsize}}</option>
                            <option value="9px"{{if $user.option.fontsize == '9px'}} selected="selected"{{/if}}>9px</option>
                            <option value="10px"{{if $user.option.fontsize == '10px'}} selected="selected"{{/if}}>10px</option>
                            <option value="12px"{{if $user.option.fontsize == '12px'}} selected="selected"{{/if}}>12px</option>
                            <option value="14px"{{if $user.option.fontsize == '14px'}} selected="selected"{{/if}}>14px</option>
                            <option value="16px"{{if $user.option.fontsize == '16px'}} selected="selected"{{/if}}>16px</option>
                            <option value="18px"{{if $user.option.fontsize == '18px'}} selected="selected"{{/if}}>18px</option>
                            <option value="24px"{{if $user.option.fontsize == '24px'}} selected="selected"{{/if}}>24px</option>
                            <option value="32px"{{if $user.option.fontsize == '32px'}} selected="selected"{{/if}}>32px</option>
                            </select></td>
                        </tr>
                    </table>
                </div>
                <div class="settingdiv">
                    <h3 class="setting_tit">{{$LANG.timezone}}</h3>
                    <div class="line_bold"></div>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td align="right" width="150">{{$LANG.default_timezone}}{{$LANG.cln}}</td>
                        <td><select name="timezone">
                        {{foreach item=item from=$timezones}}
                        {{assign var=zkey value="timezone_"|cat:$item}}
                        <option value="Etc/{{$item}}"{{if $user.option.timezone == 'Etc/'|cat:$item}} selected{{/if}}>{{$LANG[$zkey]}}</option>
                        {{/foreach}}
                        </select></td>
                      </tr>
                      <tr>
                        <td align="right" width="150">{{$LANG.default_timeformat}}{{$LANG.cln}}</td>
                        <td><select name="dateformat">
                        {{assign var=time value='2001-03-14 13:30:55'}}
                        {{foreach item=dateformat from=$dateformats}}
                        <option value="{{$dateformat}}"{{if $user.option.dateformat == $dateformat}} selected="selected"{{/if}}>{{$time|date_time_format:$dateformat}}</option>
                        {{/foreach}}
                        </select></td>
                      </tr>
                    </table>
                </div>
                <div class="settingdiv">
                    <h3 class="setting_tit">{{$LANG.display}}</h3>
                    <div class="line_bold"></div>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td align="right" width="150">{{$LANG.in_tudu_list}}{{$LANG.cln}}</td>
                        <td>{{$LANG.pagesize}}&nbsp;&nbsp;<select name="pagesize">
                        <option value="10"{{if $user.option.pagesize == 10}} selected="selected"{{/if}}>10</option>
                        <option value="20"{{if $user.option.pagesize == 20}} selected="selected"{{/if}}>20</option>
                        <option value="25"{{if $user.option.pagesize == 25}} selected="selected"{{/if}}>25({{$LANG.recommand}})</option>
                        <option value="30"{{if $user.option.pagesize == 30}} selected="selected"{{/if}}>30</option>
                        <option value="40"{{if $user.option.pagesize == 40}} selected="selected"{{/if}}>40</option>
                        <option value="50"{{if $user.option.pagesize == 50}} selected="selected"{{/if}}>50</option>
                        </select>&nbsp;&nbsp;{{$LANG.tudu_unit}}{{$LANG.tudu}}</td>
                      </tr>
                      <tr>
                        <td align="right" width="150">{{$LANG.in_reply_list}}{{$LANG.cln}}</td>
                        <td>{{$LANG.pagesize}}&nbsp;&nbsp;<select name="replysize">
                        <option value="20"{{if $user.option.replysize == 20}} selected="selected"{{/if}}>20({{$LANG.recommand}})</option>
                        <option value="50"{{if $user.option.replysize == 50}} selected="selected"{{/if}}>50</option>
                        <option value="100"{{if $user.option.replysize == 100}} selected="selected"{{/if}}>100</option>
                        </select>&nbsp;&nbsp;{{$LANG.reply_unit}}{{$LANG.reply}}</td>
                      </tr>
                      <tr>
                        <td align="right" width="150">{{$LANG.only_display}}{{$LANG.cln}}</td>
                        <td><select name="expiredfilter">
                        <option value="0"{{if $user.option.expiredfilter == 0}} selected="selected"{{/if}}>{{$LANG.all}}</option>
                        <option value="5"{{if $user.option.expiredfilter == 5}} selected="selected"{{/if}}>5</option>
                        <option value="15"{{if $user.option.expiredfilter == 15}} selected="selected"{{/if}}>15</option>
                        <option value="30"{{if $user.option.expiredfilter == 30}} selected="selected"{{/if}}>30</option>
                        </select>&nbsp;&nbsp;{{$LANG.tudu_recently}}</td>
                      </tr>
                      <tr>
                        <td align="right" width="150">{{$LANG.post_sort}}{{$LANG.cln}}</td>
                        <td>
                        <select name="postsort">
                            <option value="0"{{if $user.option.postsort != 1}} selected="selected"{{/if}}>{{$LANG.sequence_posts}}</option>
                            <option value="1"{{if $user.option.postsort == 1}} selected="selected"{{/if}}>{{$LANG.invert_posts}}</option>
                        </select>
                        </td>
                      </tr>
                      {{if 0}}
                      <tr>
                        <td align="right" width="150">标记为已读：</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;超过&nbsp;&nbsp;<select style="width:100px;" name=""><option>7({{$LANG.recommand}})</option></select>&nbsp;&nbsp;天的未读图度</td>
                      </tr>
                      {{/if}}
                    </table>
                 </div>
            </div>
        </div>
        <div class="toolbar_position">
          <div class="toolbar">
            <div><button class="btn" type="submit">{{$LANG.save_change}}</button><button class="btn" type="button" name="cancel">{{$LANG.cancel}}</button></div>
          </div>
        </div>
    </div>
</form>
<script type="text/javascript">
<!--
$(function(){
    TOP.Label.focusLabel('');
    TOP.Frame.title('{{$LANG.general}}');
    TOP.Frame.hash('#m=setting');

    $('button[name="cancel"]').click(function(){
        location = '/frame/home';
    });

    $('#theform').submit(function(){return false;});
    $('#theform').submit(function(){
        var form = $(this);

        var data = form.serializeArray();

        TOP.showMessage(TOP.TEXT.POSTING_DATA, 5000, 'success');

        form.find('input, select, button').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
                form.find('input, select, button').attr('disabled', false);
                // 刷新大框架
                TOP.location.reload();
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
                form.find('input, select, button').attr('disabled', false);
            }
        });
    });
});
-->
</script>
</body>
</html>
