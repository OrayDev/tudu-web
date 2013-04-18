/**
 * 考勤应用
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: attend.source.js 2715 2013-01-24 10:01:08Z chenyongfa $
 */
var Attend = Attend || {};

/**
 * 考勤提醒
 */
Attend.Messager = {
    // 签到提醒
    checkinMessager: null,

    // 签退提醒
    checkoutMessager: null,

    // 上班迟到提醒
    lateMessager: null,

    // 下班早退提醒
    leaveMessager: null,

    // 提醒过期时间
    outTime: [],

    // 签到提醒窗口 setTimtout
    checkinMsg: null,

    // 签退提醒窗口 setTimtout
    checkoutMsg: null,

    // 当前服务器时间
    currTime: null,

    // 当前的提醒窗口
    currMsg: {
        checkin: false,
        checkout: false
    },

    // 延迟提示的提醒窗口
    delayedMsg: [],

    // reget 考勤签到签退提醒 setTimtout
    reGet: null,

    /**
     * 获取签到签退提醒的部分
     */
    getCheckinTips: function() {
        var o = this;
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: '/app/attend/getcheckintips',
            success: function(ret) {
                if (ret.success && ret.data) {
                    var tips   = ret.data.tips;
                    o.currTime = ret.data.currtime;
                    o.setMessager(tips);
                }
            },
            error: function() {}
        });
    },

    /**
     * 获取时间
     */
    getTime: function() {
        if (this.currTime === null) {
            var currTime = Date.parse(new Date());
            currTime = currTime.toString();
            currTime = currTime.substring(0, 10);

            return currTime;
        }

        return this.currTime;
    },

    /**
     * 计算时间差 毫秒差
     */
    calculateTime: function(time) {
        var currTime = this.getTime();

        currTime = currTime * 1000;
        time     = time * 1000;

        return time - currTime;
    },

    /**
     * 计算Cookie时间
     */
    getExpireTime: function() {
        var date = new Date(),
            curr = Date.parse(date);
        date.setDate(date.getDate() + 1);
        date.setHours(0, 0, 0, 0);

        return date - curr;
    },

    /**
     * 设置提示
     */
    setMessager: function(tips) {
        if (tips === null) {
            return ;
        }

        var o        = this,
            reGet    = false,
            checkin  = tips.checkin,
            checkout = tips.checkout;

        o.outTime['checkin']  = checkin.outtime;
        o.outTime['checkout'] = checkout.outtime;

        // 签到部分
        if (checkin.isshow && !o.currMsg.checkin) {
            o.showMessager(0);
        } else if (!checkin.isshow && checkin.starttime != '^off') {
            reGet = true;

            var timer = o.calculateTime(checkin.starttime);
            if (timer > 0) {
                o.delayedMsg[0] = setTimeout(function(){
                    if (o.reGet !== null) {
                        clearTimeout(o.reGet);
                    }

                    o.showMessager(0);
                }, timer);
            }
        }

        // 签退部分
        if (checkout.isshow && !o.currMsg.checkout) {
            o.showMessager(1);
        } else if (!checkout.isshow && checkout.starttime != '^off') {
            reGet = true;

            var timer = o.calculateTime(checkout.starttime);
            if (timer > 0) {
                o.delayedMsg[1] = setTimeout(function(){
                    if (o.reGet !== null) {
                        clearTimeout(o.reGet);
                    }

                    o.showMessager(1);
                }, timer);
            }
        }

        var cookieCheckin = Cookie.get('CHECKIN-MSG'),
            cookieCheckout = Cookie.get('CHECKOUT-MSG');
        // 重新获取是否还要考勤签到签退提醒
        if (reGet && (cookieCheckin === null || cookieCheckout === null)) {
            // 30分钟  1800000
            o.reGet = setTimeout(function(){
                o.getCheckinTips();
            }, 1800000);
        }
    },

    /**
     * 显示提醒窗口
     * 上下班签到签退提醒
     */
    showMessager: function(type) {
        // 下班提醒
        if (type == 1) {
            this.initCheckoutMessager();
        // 上班提醒
        } else {
            this.initCheckinMessager();
        }
    },

    /**
     * 初始化签到提醒窗口
     */
    initCheckinMessager: function() {
        var o = this,
            cookieCheckin = Cookie.get('CHECKIN-MSG');

        if (cookieCheckin !== null && cookieCheckin) {
            return ;
        }

        if (this.checkinMessager === null) {
            this.checkinMessager = Messager.window({
                id: 'checkin-messager',
                title:'温馨提示',
                initClose: false,
                showTimer: true,
                timeFrom: '/app/attend/getTime',
                anims: {type: 'fade', speed: 800},
                body: '<div style="padding:21px"><p>温馨提示：今天您还没有签到哦！</p><p>现在时间：<span class="timer"></span></p></div>',
                footer: '<button type="button" name="btn-checkin" class="btn">签到</button><button type="button" name="after" class="btn">稍后再签</button>',
                onClose: function() {
                    o.checkinMessager = null;
                },
                init: function() {
                    o.currMsg.checkin = true;

                    this.find('a.close').click(function() {
                        o.checkinMessager.close();
                        Cookie.set('CHECKIN-MSG', 1, {expires: o.getExpireTime()});
                    });

                    // 稍后提醒 10min
                    this.find('button[name="after"]').click(function() {
                        var currTime = Date.parse(o.checkinMessager.getTime());
                        currTime = currTime.toString();
                        currTime = currTime.substring(0, 10);

                        o.checkinMessager.close();

                        if (o.outTime['checkin'] != '^off' && parseInt(currTime) <= o.outTime['checkin']) {
                            o.currMsg.checkin = true;

                            // 10分钟 600000
                            o.checkinMsg = setTimeout(function(){
                                o.initCheckinMessager();
                            }, 600000);
                        }
                    });

                    // 签到
                    this.find('button[name="btn-checkin"]').click(function() {
                        o.closeCheckinMessager();

                        Attend.Checkin.signIn(0, function(ret) {
                            if (ret.success && ret.data) {
                                var status = ret.data.status;
                                if (status == 1 || status == 3) {
                                    o.initLateMessager();
                                }
                                o.getCheckinTips();
                            }
                        });
                    });
                }
            });
        }

        this.checkinMessager.show();
    },

    /**
     * 初始化签退提醒窗口
     */
    initCheckoutMessager: function() {
        var o = this,
            cookieCheckout = Cookie.get('CHECKOUT-MSG');

        if (cookieCheckout !== null && cookieCheckout) {
            return ;
        }

        if (this.checkoutMessager === null) {
            this.checkoutMessager = Messager.window({
                id: 'checkout-messager',
                title:'温馨提示',
                initClose: false,
                showTimer: true,
                timeFrom: '/app/attend/getTime',
                anims: {type: 'fade', speed: 800},
                body: '<div style="padding:21px"><p>温馨提示：下班不要忘记签退哦！</p><p>现在时间：<span class="timer"></span></p></div>',
                footer: '<button type="button" name="btn-checkout" class="btn">签退</button><button type="button" name="after" class="btn">稍后再签</button>',
                onClose: function() {
                    o.checkoutMessager = null;
                },
                init: function(){
                    o.currMsg.checkout = true;

                    this.find('a.close').click(function() {
                        o.checkoutMessager.close();
                        Cookie.set('CHECKOUT-MSG', 1, {expires: o.getExpireTime()});
                    });

                    // 稍后提醒 10min
                    this.find('button[name="after"]').click(function() {
                        var currTime = Date.parse(o.checkoutMessager.getTime());
                        currTime = currTime.toString();
                        currTime = currTime.substring(0, 10);

                        o.checkoutMessager.close();

                        if (o.outTime['checkout'] != '^off' && parseInt(currTime) <= o.outTime['checkout']) {
                            o.currMsg.checkout = true;

                            // 10分钟 600000
                            o.checkoutMsg = setTimeout(function(){
                                o.initCheckoutMessager();
                            }, 600000);
                        }
                    });

                    // 签退
                    this.find('button[name="btn-checkout"]').click(function() {
                        o.closeCheckoutMessager();

                        Attend.Checkin.signIn(1, function(ret) {
                            if (ret.success && ret.data) {
                                var status = ret.data.status;
                                if (status == 2) {
                                    o.initLeaveMessager();
                                }
                            }
                        });
                    });
                }
            });
        }

        this.checkoutMessager.show();
    },

    /**
     * 初始化签到迟到提醒 （5s后自动关闭）
     */
    initLateMessager: function() {
        var o = this;
        if (this.lateMessager === null) {
            this.lateMessager = Messager.window({
                id: 'late-messager',
                title:'温馨提示',
                autoCloseTime: 5000,
                anims: {type: 'fade', speed: 800},
                body: '<div style="padding:35px 21px;"><p>今天您迟到了，明天可不要再迟到咯~</p></div>',
                footer: '<button type="button" name="confirm" class="btn">确定</button>',
                init: function(){
                    this.find('button[name="confirm"]').click(function() {
                        o.lateMessager.close();
                    });
                },
                onClose: function() {
                    o.lateMessager = null;
                }
            });
        }

        this.lateMessager.show();
    },

    /**
     * 初始化签退早退提醒 （5s后自动关闭）
     */
    initLeaveMessager: function() {
        var o = this;
        if (this.leaveMessager === null) {
            this.leaveMessager = Messager.window({
                id: 'unwork-messager',
                title:'温馨提示',
                autoCloseTime: 5000,
                anims: {type: 'fade', speed: 800},
                body: '<div style="padding:35px 21px;"><p>今天您早退了，如果有事记得先请假哦！</p></div>',
                footer: '<button type="button" name="confirm" class="btn">确定</button>',
                init: function(){
                    this.find('button[name="confirm"]').click(function() {
                        o.leaveMessager.close();
                    });
                },
                onClose: function() {
                    o.leaveMessager = null;
                }
            });
        }

        this.leaveMessager.show();
    },

    /**
     * 关闭签到提醒窗口
     */
    closeCheckinMessager: function() {
        if (this.checkinMessager !== null) {
            this.checkinMessager.close();
        }
    },

    /**
     * 关闭签退提醒窗口
     */
    closeCheckoutMessager: function() {
        if (this.checkoutMessager !== null) {
            this.checkoutMessager.close();
        }
    },

    /**
     * 清除签到提醒窗口 setTimeout
     */
    clearCheckinMsg: function() {
        if (this.checkinMsg !== null) {
            clearTimeout(this.checkinMsg);
            this.checkinMsg = null;
        }
    },

    /**
     * 清除签退提醒窗口 setTimeout
     */
    clearCheckoutMsg: function() {
        if (this.checkoutMsg !== null) {
            clearTimeout(this.checkoutMsg);
            this.checkoutMsg = null;
        }
    },

    /**
     * 清除延迟提示的提醒窗口
     *
     * @param {Object} type
     */
    clearDelayedMsg: function(type) {
        if (typeof this.delayedMsg[type] != 'undefined' && this.delayedMsg[type] !== null) {
            clearTimeout(this.delayedMsg[type]);
            this.delayedMsg[type] = null;
        }
    }
};

/**
 * 考勤签到签退
 */
Attend.Checkin = {
    _lang: {
        checkin: '上班签到'
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
     * 考勤应用首页
     * 初始化js事件
     */
    init: function() {
        var me = this;

        // 上班签到
        $('input[name="checkin"]').click(function() {
            TOP.Attend.Messager.clearDelayedMsg(0);
            TOP.Attend.Messager.clearCheckinMsg();
            TOP.Attend.Messager.closeCheckinMessager();

            var btn = $('input[name="checkin"]');
            me.signIn(0, function(ret) {
                if (ret.success && ret.data) {
                    var status = ret.data.status;
                    if (status == 1 || status == 3) {
                        TOP.Attend.Messager.initLateMessager();
                    }
                    TOP.Attend.Messager.getCheckinTips();

                    if (btn.hasClass('first')) {
                        var data = ret.data,
                            name = 'checkin',
                            btnName = me._lang.checkin;

                        btnName = btnName + '(' + data.time + ')';
                        $('input[name="' + name + '"]').val(btnName);
                        $('span.' + name).html('(' + data.ip + '&nbsp;' + data.address + ')');
                        $('input[name="' + name + '"]').attr('disabled', true);
                    } else {
                        location.assign(location.href);
                    }
                }
            });
        });

        // 下班签退
        $('input[name="checkout"]').click(function() {
            TOP.Attend.Messager.clearDelayedMsg(1);
            TOP.Attend.Messager.clearCheckoutMsg();
            TOP.Attend.Messager.closeCheckoutMessager();

            me.signIn(1, function(ret) {
                if (ret.success && ret.data) {
                    var status = ret.data.status;
                    if (status == 2 || status == 3) {
                        TOP.Attend.Messager.initLeaveMessager();
                    }

                    location.assign(location.href);
                }
            });
        });

        $('#month').bind('change', function(){
            var year = $('#year').val(),
                month = $(this).val(),
                unid = $('#unid').val();

            me.loadCalendar(year, month, unid);
        });
        $('#year').bind('change', function(){
            var year = $(this).val(),
                month = $('#month').val(),
                unid = $('#unid').val();

            me.loadCalendar(year, month, unid);
        });
    },

    /**
     *
     * @param {Object} year
     * @param {Object} month
     */
    loadCalendar: function(year, month, unid) {
        $('#schedule-calendar').load('/app/attend/schedule/calendar?year=' + year + '&month=' + month + '&unid=' + unid);
    },

    /**
     * 提交签到签退操作
     *
     * @param {Object} type
     * @param {Function} callback
     */
    signIn: function(type, callback) {
        var o = this;

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '/app/attend/checkin',
            data: {type: type},
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);

                if (typeof callback == 'function') {
                    callback.call(o, ret);
                }
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
            }
        });
    }
};

/**
 * 各类明细窗口
 */
Attend.Infowin = {
    win: null,

    winTpl:[
       '<div class="pop">',
            '<div class="pop_header"><strong id="win-title"></strong><a class="icon icon_close close"></a></div>',
            '<div class="pop_body" style="height:250px;padding:0;overflow:auto;">',
                '<div id="win-content" style="padding: 10px 22px;"></div>',
            '</div>',
            '<div class="pop_footer">',
                '<button type="button" name="close" class="btn">关闭</button>',
            '<div>',
        '</div>'
    ].join(''),

    show: function(categoryId, uniqueId, year, month) {
        var o = this;

        o.win = TOP.appendWindow('infowin', o.winTpl, {
            width: 500,
            draggable: true,
            onShow: function() {
                // 关闭窗口
                o.win.find('a.icon_close, button[name="close"]').bind('click', function(){
                    o.win.close();
                    return false;
                });

                o.getInfo(categoryId, uniqueId, year, month);
            },
            onClose: function() {
                o.win.destroy();
            }
        });
        o.win.show();
    },

    getInfo: function(categoryId, unId, year, month) {
        var o = this,
            _$ = TOP.getJQ();

        _$('#win-content').load('/app/attend/attend/applyinfo?cid=' + categoryId + '&unid=' + unId + '&year=' + year + '&month=' + month);
    }
};

/**
 * 考勤统计
 */
Attend.Count = {
    /**
     * 考勤明细窗口模板
     */
    checkinInfoTpl:[
        '<div class="pop">',
            '<div class="pop_header"><strong id="win-title">考勤明细</strong><a class="icon icon_close close"></a></div>',
            '<div class="pop_body">',
                '<table border="0" cellspacing="0" cellpadding="6" class="attendance_table">',
                    '<tr><th align="right">查询日期：</th><td id="date"></td></tr>',
                    '<tr><th align="right">上班签到：</th><td><span id="checkintime">-</span>&nbsp;<span class="gray" id="checkin-ip"></span></td></tr>',
                    '<tr><th align="right">下班签退：</th><td><span id="checkouttime">-</span>&nbsp;<span class="gray" id="checkout-ip"></span></td></tr>',
                    '<tr><th align="right">工作时长：</th><td id="worktime">0小时0分</td></tr>',
                    '<tr><th align="right">考勤状况：</th><td id="status">-</td></tr>',
                    '<tr name="memo" style="display:none;"><th align="right" valign="top">备注：</th><td><div class="remark_info" style="width:380px;"></div></td></tr>',
                '</table>',
            '</div>',
            '<div class="pop_footer">',
                '<button type="button" name="close" class="btn">关闭</button>',
            '<div>',
        '</div>'
    ].join(''),

    /**
     * 考勤明细窗口
     */
    checkinInfoWin: null,

    /**
     * 考勤汇总统计
     */
    init: function() {
        var resizeTimer = null;

        TOP.keyhint('#keywords', 'gray', true, document.body);

        $('#count-list tr').mousemove(function(){
            $(this).addClass("current");
        }).mouseout(function(){
            $(this).removeClass("current");
        });

        $('input[name="search"]').bind('click', function(){
            var year  = $('#year option:selected').val(),
                month = $('#month option:selected').val(),
                url   = '/app/attend/count/index?year='+year+'&month='+month;

            if ($('#deptid').size()) {
                var deptid = $('#deptid option:selected').val();
                url += '&deptid='+deptid;
            }

            if ($('#keywords').size()) {
                var keywords = $('#keywords').val() != $('#keywords').attr('title') ? $('#keywords').val().replace(/^\s+|\s+$/, '') : '';
                url += '&keywords='+encodeURIComponent(keywords);
            }

            location.href = url;
        });

        var tableWidth = $('table.table_list').outerWidth(true);

        function onResize() {
            var bodyWidth  = document.body.offsetWidth,
                width      = Math.max(tableWidth, bodyWidth - 10);

            if (tableWidth >= bodyWidth) {
                width += 40;
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
     * 考勤统计 个人月每天考勤信息
     * 页面
     */
    initList: function(back) {
        var _o = this,
            resizeTimer = null;

        TOP.keyhint('#keywords', 'gray', true, document.body);

        if ($('#nunid').val()) {
            $('#keywords').change(function(){
                $('#nunid').val('');
            });
        }

        $('#count-list tr').mousemove(function(){
            $(this).addClass("current");
        }).mouseout(function(){
            $(this).removeClass("current");
        });

        $('input[name="search"]').bind('click', function(){
            var unid = $('#unid').val(),
                year = $('#year option:selected').val(),
                month = $('#month option:selected').val(),
                categoryid = $('#categoryid  option:selected').val(),
                url = '/app/attend/count/list?&year=' + year + '&month=' + month + '&categoryid=' + categoryid;

            if ($('#keywords').size()) {
                var keywords = $('#keywords').val() != $('#keywords').attr('title') ? $('#keywords').val().replace(/^\s+|\s+$/, '') : '';
                url += '&keywords='+encodeURIComponent(keywords);
            }

            if (typeof keywords != 'undefined' && keywords.length > 0) {
                var nunid = $('#nunid').val();
                if (!nunid) {
                    location = '/app/attend/count/index?year=' + year + '&month=' + month + '&keywords=' + encodeURIComponent(keywords);
                    return;
                } else {
                    unid = nunid;
                }
            }

            url += '&unid=' + unid + '&back=' + back;

            location.href = url;
        });

        new $.autocomplete({
            target: $('#keywords'),
            data: {users: TOP.Cast.get('attend')},
            url: '/frame/cast',
            onLoaded: _o.castLoaded,
            columns: {users: ['truename', 'username', 'pinyin']},
            width: 165,
            arrowSupport: true,
            template: {
                users:'{truename} <span class="gray">&lt;{username}&gt;</span>'
            },
            onSelect: function(item){
                $('#keywords').val(item.data.truename);
                $('#nunid').val(item.data.uniqueid);
            }
        });

        var tableWidth = $('table.table_list').outerWidth(true);

        function onResize() {
            var bodyWidth  = document.body.offsetWidth,
                width      = Math.max(tableWidth, bodyWidth - 10);

            if (tableWidth >= bodyWidth) {
                width += 40;
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
     * 
     * @param {Object} ret
     */
    castLoaded: function(ret) {
        TOP.Cast.set('users', ret.data.users);
        TOP.Cast.set('depts', ret.data.depts);
        TOP.Cast.set('groups', ret.data.groups);

        var users = ret.data.users,
            userArr = [],
            depts = Attend.Count.deptIds;
        if (depts.length > 0) {
            for (var i = 0, c = users.length; i < c; i++) {
                if (TOP.Util.inArray(users[i].deptid, depts)) {
                    userArr.push(users[i]);
                }
            }

            this.data = {users: userArr};
            TOP.Cast.set('attend', userArr);
        } else {
            this.data = {users: users};
            TOP.Cast.set('attend', users);
        }
    },

    deptIds: [],

    setDepts: function(arr) {
        this.deptIds = arr;
    },

    /**
     * 考勤明细
     *
     * @param {Object} unId
     * @param {Object} date
     */
    showCheckinInfo: function(unId, date) {
        var o = this;

        o.checkinInfoWin = TOP.appendWindow('checkininfo', o.checkinInfoTpl, {
            width: 500,
            draggable: true,
            onShow: function() {
                // 关闭窗口
                o.checkinInfoWin.find('a.icon_close, button[name="close"]').bind('click', function(){
                    o.checkinInfoWin.close();
                    return false;
                });

                o.getInfo(unId, date);
            },
            onClose: function() {
                o.checkinInfoWin.destroy();
            }
        });
        o.checkinInfoWin.show();
    },

    /**
     * 获取考勤明细
     *
     * @param {Object} unId
     * @param {Object} date
     */
    getInfo: function(unId, date) {
        var o = this,
            _$ = TOP.getJQ();

        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: '/app/attend/attend/info?unid=' + unId + '&date=' + date,
            success: function(ret) {
                if (ret.success && ret.data) {
                    var info = ret.data;
                    _$('#date').text(info.date);
                    if (typeof info.checkin != 'undefined') {
                        _$('#checkintime').text(info.checkin.time);
                        _$('#checkin-ip').html('(' + info.checkin.ip + '&nbsp;' + info.checkin.address + ')');
                    }
                    if (typeof info.checkout != 'undefined') {
                        _$('#checkouttime').text(info.checkout.time);
                        _$('#checkout-ip').html('(' + info.checkout.ip + '&nbsp;' + info.checkout.address + ')');
                    }
                    if (typeof info.worktime != 'undefined') {
                        _$('#worktime').html(info.worktime);
                    }
                    if (typeof info.status != 'undefined') {
                        _$('#status').html(info.status);
                    }
                    if (typeof info.memo != 'undefined') {
                        o.checkinInfoWin.find('tr[name="memo"]').show();
                        _$('.remark_info').html(info.memo);
                    }
                    o.checkinInfoWin.center();
                }
            },
            error: function(res) {}
        });
    }
};
