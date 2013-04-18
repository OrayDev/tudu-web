/**
 * 考勤应用 - 排班设置
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: schedule.source.js 2767 2013-03-06 09:30:50Z chenyongfa $
 */
var Attend = Attend || {};

Attend.Schedule = {
    _lang: {
        confirm_delete_schedule: '您确定要删除此排班方案吗？',
        please_add_step: '请先添加方案',
        params_invalid_schedule_name: '请正确输入排班方案名称',
        week_1: '星期一',
        week_2: '星期二',
        week_3: '星期三',
        week_4: '星期四',
        week_5: '星期五',
        week_6: '星期六',
        week_0: '星期日',
        worktime_error: '上班时间不能大于下班时间',
        worktime_not_equal: '上下班时间不能相等',
        late_must_int: '请正确输入迟到标准的时间值，必须为正整数',
        leave_must_int: '请正确输入早退标准的时间值，必须为正整数',
        time_area_error: '迟到标准或早退标准时间区域设置有误'
    },

    /**
     * 设置语言
     * @param {Object} lang
     */
    setLang: function(lang) {
        var _o = this;
        for (var i in lang) {
            _o._lang[i] = lang[i];
        }
    },

    /**
     * 
     */
    init: function() {
        var resizeTimer = null; 
        $('#schedule-list tr').mousemove(function(){
            $(this).addClass("current");
        }).mouseout(function(){
            $(this).removeClass("current");
        });

        $('#plan-_default').load('/app/attend/schedule/defaultRule');

        var tableWidth = $('table.table_list').outerWidth(true);

        function onResize() {
            var bodyWidth  = document.body.offsetWidth;
            var width      = Math.max(tableWidth, bodyWidth - 10);

            if (tableWidth >= bodyWidth) {
                width += 60;
            }

            $('.tab-panel-body').css('width', width);
            resizeTimer = null;
        }

        window.onresize = function() {
            if (resizeTimer == null) {
                resizeTimer = setTimeout(function(){
                    onResize();
                }, 100);
            }
        }
        onResize();
    },

    /**
     * 删除排班方案
     *
     * @param {Object} id
     */
    deletePlan: function(id) {
        if (!confirm(this._lang.confirm_delete_schedule)) {
            return false;
        }

        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: '/app/attend/schedule/delete?scheduleid=' + id,
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
                if (ret.success) {
                    location = location;
                }
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
            }
        });
    },

    currentId: null,

    /**
     * 添加排班方案
     */
    initModify: function() {
        var _o = this;

        $('#theform').submit(function(){return false;});
        $('input[name="name"]').focus();

        $('button[name="save"]').bind('click', function(){
            _o.savePlan();
        });

        $('.color_grid').click(function(e){
            _o.selectColor(this);
            TOP.stopEventBuddle(e);
        });

        $(document.body).bind('click', function(){$('#color_panel').hide(300);});
        $('#color_panel').bind('click', function(e){
            TOP.stopEventBuddle(e);
        });

        $('input[name="late-standard"], input[name="late-checkin"], input[name="leave-checkout"]').bind('keyup', function(){
            this.value = this.value.replace(/[^0-9]+/, '');
        })
        .blur(function(){
            $(this).val(this.value);
        });

        $('select[name="checkin-hour"]').bind('change', function(){
            if (!$(this).val()) {
                $('select[name="checkin-min"] option:first').attr('selected', true);
                $('input[name="late-standard"], input[name="late-checkin"]').attr('disabled', true).attr('_disabled', 'disabled');
            } else {
                if (!$('select[name="checkin-min"]').val()) {
                    $('select[name="checkin-min"]').val('00');
                }
                $('input[name="late-standard"], input[name="late-checkin"]').attr('disabled', false);
                $('input[name="late-standard"], input[name="late-checkin"]').removeAttr('_disabled');
            }
        });

        $('select[name="checkin-min"]').bind('change', function(){
            if (!$('select[name="checkin-hour"]').val()) {
                $('select[name="checkin-min"] option:first').attr('selected', true);
            } else {
                if (!$(this).val()) {
                    $(this).val('00');
                }
            }
        });

        $('select[name="checkout-hour"]').bind('change', function(){
            if (!$(this).val()) {
                $('select[name="checkout-min"] option:first').attr('selected', true);
                $('input[name="leave-standard"], input[name="leave-checkout"]').attr('disabled', true).attr('_disabled', 'disabled');
            } else {
                if (!$('select[name="checkout-min"]').val()) {
                    $('select[name="checkout-min"]').val('00');
                }
                $('input[name="leave-checkout"]').attr('disabled', false);
                $('input[name="leave-checkout"]').removeAttr('_disabled');
            }
        });

        $('select[name="checkout-min"]').bind('change', function(){
            if (!$('select[name="checkout-hour"]').val()) {
                $('select[name="checkout-min"] option:first').attr('selected', true);
            } else {
                if (!$(this).val()) {
                    $(this).val('00');
                }
            }
        });

        var resizeTimer = null,
            contentHeight = $('div.readmailinfo').height();

        function onResize(){
            var bodyHeight = document.documentElement.clientHeight,
                height     = bodyHeight - 137;

            if (height < contentHeight) {
                height = contentHeight;
            }

            $('div.readmailinfo').css('height', height);
            resizeTimer = null;
        }

        window.onresize = function() {
            if (resizeTimer == null) {
                resizeTimer = setTimeout(function(){
                    onResize();
                }, 100);
            }
        }

        onResize();
    },

    /**
     * 选择颜色
     */
    selectColor: function(obj) {
        var block = $(obj),
            panel = $('#color_panel');
        var offset = block.offset();

        if ($('#color_panel:visible').size()) {
            panel.hide();
        }

        panel.css({top: offset.top + block.height()  + 'px', left: offset.left + 'px'})
        .show(300);

        panel.find('div.color_block')
        .unbind('click')
        .bind('click', function(){
            var color = $(this).find('input[name="color"]').val();

            block.css('background-color', color);
            $('#theform').find('input[name="bgcolor"]').val(color);

            panel.hide(300);
        });
    },

    /**
     * 保存方案
     *
     * @param {Object} isNext
     */
    savePlan: function(id) {
        var _o = this,
            form = $('#theform');

        if (form.find('input[name="scheduleid"]').val() != '^default') {
            var name = $('input[name="name"]').val().replace(/\s+/, '');
            if (!name) {
                TOP.showMessage(_o._lang.params_invalid_schedule_name);
                $('input[name="name"]').focus();
                return false;
            }
        }

        if (form.find('input[name="scheduleid"]').val() != '^default') {
            if (!_o.judgeData()) {
                return false;
            }
        } else {
            if (!_o.judgeDefaultData()) {
                return false;
            }
        }

        var data = form.serializeArray();
        form.find(':input:not([_disabled])').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
                form.find(':input:not([_disabled])').attr('disabled', false);
                if (ret.success) {
                    location = '/app/attend/schedule/index';
                }
            },
            error: function(res) {
                form.find(':input:not([_disabled])').attr('disabled', false);
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
            }
        });
    },

    /**
     * 判断默认排班数据的正确性
     */
    judgeDefaultData: function() {
        var o = this,
            member = [];
        $('input[name="member[]"]').each(function(){
            member.push($(this).val());
        });

        for (var k=0; k<member.length; k++) {
            var i = member[k],
                week = i==0 ? o._lang.week_0 : i==1 ? o._lang.week_1 : i==2 ? o._lang.week_2 : i==3 ? o._lang.week_3 : i==4 ? o._lang.week_4 : i==5 ? o._lang.week_5 : i==6 ? o._lang.week_6 : '';
            if ($('select[name="checkintime-hour-'+i+'"]').val() == $('select[name="checkouttime-hour-'+i+'"]').val()) {
                if ($('select[name="checkintime-min-'+i+'"]').val() > $('select[name="checkouttime-min-'+i+'"]').val()) {
                    TOP.showMessage(week + o._lang.worktime_error);
                    return false;
                } else if ($('select[name="checkintime-min-'+i+'"]').val() == $('select[name="checkouttime-min-'+i+'"]').val()) {
                    TOP.showMessage(week + o._lang.worktime_not_equal);
                    return false;
                }
            } else {
                if ($('select[name="checkintime-hour-'+i+'"]').val() > $('select[name="checkouttime-hour-'+i+'"]').val()) {
                    TOP.showMessage(week + o._lang.worktime_error);
                    return false;
                }
            }
        }

        var latestandard = $('input[name="latestandard"]').val().replace(/\s+/, ''),
            latecheckin = $('input[name="latecheckin"]').val().replace(/\s+/, ''),
            leavecheckout = $('input[name="leavecheckout"]').val().replace(/\s+/, '');

        if (isNaN(latestandard) || latestandard.indexOf('.') >= 0 || latestandard.indexOf('-') >= 0
            || isNaN(latecheckin) || latecheckin.indexOf('.') >= 0 || latecheckin.indexOf('-') >= 0){
            TOP.showMessage(o._lang.late_must_int);
            return false;
        }

        if (isNaN(leavecheckout) || leavecheckout.indexOf('.') >= 0 || leavecheckout.indexOf('-') >= 0){
            TOP.showMessage(o._lang.leave_must_int);
            return false;
        }

        if (parseInt(latestandard) > parseInt(latecheckin)) {
            TOP.showMessage(o._lang.time_area_error);
            return false;
        }

        return true;
    },

    /**
     * 判断数据的正确性
     */
    judgeData: function() {
        var o = this,
            latestandard = $('input[name="late-standard"]').val().replace(/\s+/, ''),
            latecheckin = $('input[name="late-checkin"]').val().replace(/\s+/, ''),
            leavecheckout = $('input[name="leave-checkout"]').val().replace(/\s+/, '');

        if ($('select[name="checkin-min"]').val() && $('select[name="checkout-min"]').val()) {
            if ($('select[name="checkin-hour"]').val() == $('select[name="checkout-hour"]').val()) {
                if ($('select[name="checkin-min"]').val() > $('select[name="checkout-min"]').val()) {
                    TOP.showMessage(o._lang.worktime_error);
                    return false;
                } else if ($('select[name="checkin-min"]').val() == $('select[name="checkout-min"]').val()) {
                    TOP.showMessage(o._lang.worktime_not_equal);
                    return false;
                }
            }else {
                if ($('select[name="checkin-hour"]').val() > $('select[name="checkout-hour"]').val()) {
                    TOP.showMessage(o._lang.worktime_error);
                    return false;
                }
            }
        }

        if (isNaN(latestandard) || latestandard.indexOf('.') >= 0 || latestandard.indexOf('-') >= 0
            || isNaN(latecheckin) || latecheckin.indexOf('.') >= 0 || latecheckin.indexOf('-') >= 0){
            TOP.showMessage(o._lang.late_must_int);
            return false;
        }

        if (isNaN(leavecheckout) || leavecheckout.indexOf('.') >= 0 || leavecheckout.indexOf('-') >= 0){
            TOP.showMessage(o._lang.leave_must_int);
            return false;
        }

        if (parseInt(latestandard) > parseInt(latecheckin)) {
            TOP.showMessage(o._lang.time_area_error);
            return false;
        }

        return true;
    },

    /**
     * 
     */
    initUserPlan: function() {
        var _o = this;
        $('#month').bind('change', function(){
            var year = $('#year').val(),
                month = $(this).val(),
                unid = $('#unid').val();

            _o.loadCalendar(year, month, unid);
        });
        $('#year').bind('change', function(){
            var year = $(this).val(),
                month = $('#month').val(),
                unid = $('#unid').val();

            _o.loadCalendar(year, month, unid);
        });
    },

    /**
     *
     * @param {Object} year
     * @param {Object} month
     */
    loadCalendar: function(year, month, unid) {
        $('#schedule-calendar').load('/app/attend/schedule/calendar?year=' + year + '&month=' + month + '&unid=' + unid);
    }
};