/**
 * 考勤应用 - 农历日期
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: lunar-1.0.0.source.js 1957 2012-07-02 06:54:25Z web_op $
 */

/**
 * 语言
 */
var Lang = {
    year: '年',
    month: {
        zh_CN: '月',
        zh_TW: '月',
        en_US: {
            1: 'January',
            2: 'February',
            3: 'March',
            4: 'April',
            5: 'May',
            6: 'June',
            7: 'July',
            8: 'August',
            9: 'Setptember',
            10: 'October',
            11: 'November',
            12: 'December'
        }
    },
    day: '日',
    date: {
        1: '1st', 2: '2nd', 3: '3rd', 4: '4th', 5: '5th', 6: '6th', 7: '7th', 8: '8th', 9: '9th', 10: '10th',
        11: '11st', 12: '12nd', 13: '13rd', 14: '14th', 15: '15th', 16: '16th', 17: '17th', 18: '18th', 19: '19th', 20: '20th',
        21: '21st', 22: '22nd', 23: '23rd', 24: '24th', 25: '25th', 26: '26th', 27: '27th', 28: '28th', 29: '29th', 30: '30th',
        31: '31st'
    },
    week: {
        0: '星期日',
        1: '星期一',
        2: '星期二',
        3: '星期三',
        4: '星期四',
        5: '星期五',
        6: '星期六'
    },
    en_week: {
        0: 'Sunday',
        1: 'Monday',
        2: 'Tuesday',
        3: 'Wednesday',
        4: 'Thursday',
        5: 'Friday',
        6: 'Saturday'
    },
    lunar: {zh_TW: '農曆', zh_CN: '农历'},
    dateNumber: {
        1: '初一', 2: '初二', 3: '初三', 4: '初四', 5: '初五', 6: '初六', 7: '初七', 8: '初八', 9: '初九', 10: '初十',
        11: '十一', 12: '十二', 13: '十三', 14: '十四', 15: '十五', 16: '十六', 17: '十七', 18: '十八', 19: '十九', 20: '二十',
        21: '廿一', 22: '廿二', 23: '廿三', 24: '廿四', 25: '廿五', 26: '廿六', 27: '廿七', 28: '廿八', 29: '廿九', 30: '三十',
        31: '三十一'
    },
    monthNumber: {1: '正', 2: '二', 3: '三', 4: '四', 5: '五', 6: '六', 7: '七', 8: '八', 9: '九', 10: '十', 11: '十一', 12: '十二'}
};

/**
 * 农历
 * @param {Object} params
 */
var Lunar = function(params){
    this._settings = {};
    this.setParam(params);

    this.init();
};

Lunar.defaultSettings = {
    lang: 'zh_CN',
    firstYear: 1998,
    lastYear: 2031,
    dateTo: null,
    lunarTo: null,
    hoursTo: null,
    minutesTo: null,
    lunarCal: [
        new tagLunarCal( 27,  5, 3, 43, 1, 0, 0, 1, 0, 0, 1, 1, 0, 1, 1, 0, 1 ),
        new tagLunarCal( 46,  0, 4, 48, 1, 0, 0, 1, 0, 0, 1, 0, 1, 1, 1, 0, 1 ),
        new tagLunarCal( 35,  0, 5, 53, 1, 1, 0, 0, 1, 0, 0, 1, 0, 1, 1, 0, 1 ),
        new tagLunarCal( 23,  4, 0, 59, 1, 1, 0, 1, 0, 1, 0, 0, 1, 0, 1, 0, 1 ),
        new tagLunarCal( 42,  0, 1,  4, 1, 1, 0, 1, 0, 1, 0, 0, 1, 0, 1, 0, 1 ),
        new tagLunarCal( 31,  0, 2,  9, 1, 1, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1, 0 ),
        new tagLunarCal( 21,  2, 3, 14, 0, 1, 0, 1, 1, 0, 1, 0, 1, 0, 1, 0, 1 ),
        new tagLunarCal( 39,  0, 5, 20, 0, 1, 0, 1, 0, 1, 1, 0, 1, 0, 1, 0, 1 ),
        new tagLunarCal( 28,  7, 6, 25, 1, 0, 1, 0, 1, 0, 1, 0, 1, 1, 0, 1, 1 ),
        new tagLunarCal( 48,  0, 0, 30, 0, 0, 1, 0, 0, 1, 0, 1, 1, 1, 0, 1, 1 ),
        new tagLunarCal( 37,  0, 1, 35, 1, 0, 0, 1, 0, 0, 1, 0, 1, 1, 0, 1, 1 ),
        new tagLunarCal( 25,  5, 3, 41, 1, 1, 0, 0, 1, 0, 0, 1, 0, 1, 0, 1, 1 ),
        new tagLunarCal( 44,  0, 4, 46, 1, 0, 1, 0, 1, 0, 0, 1, 0, 1, 0, 1, 1 ),
        new tagLunarCal( 33,  0, 5, 51, 1, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1, 0, 1 ),
        new tagLunarCal( 22,  4, 6, 56, 1, 0, 1, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0 ),
        new tagLunarCal( 40,  0, 1,  2, 1, 0, 1, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0 ),
        new tagLunarCal( 30,  9, 2,  7, 0, 1, 0, 1, 0, 1, 0, 1, 1, 0, 1, 0, 1 ),
        new tagLunarCal( 49,  0, 3, 12, 0, 1, 0, 0, 1, 0, 1, 1, 1, 0, 1, 0, 1 ),
        new tagLunarCal( 38,  0, 4, 17, 1, 0, 1, 0, 0, 1, 0, 1, 1, 0, 1, 1, 0 ),
        new tagLunarCal( 27,  6, 6, 23, 0, 1, 0, 1, 0, 0, 1, 0, 1, 0, 1, 1, 1 ),
        new tagLunarCal( 46,  0, 0, 28, 0, 1, 0, 1, 0, 0, 1, 0, 1, 0, 1, 1, 0 ),
        new tagLunarCal( 35,  0, 1, 33, 0, 1, 1, 0, 1, 0, 0, 1, 0, 0, 1, 1, 0 ),
        new tagLunarCal( 24,  4, 2, 38, 0, 1, 1, 1, 0, 1, 0, 0, 1, 0, 1, 0, 1 ),
        new tagLunarCal( 42,  0, 4, 44, 0, 1, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1 ),
        new tagLunarCal( 31,  0, 5, 49, 1, 0, 1, 0, 1, 1, 0, 1, 0, 1, 0, 1, 0 ),
        new tagLunarCal( 21,  2, 6, 54, 0, 1, 0, 1, 0, 1, 0, 1, 1, 0, 1, 0, 1 ),
        new tagLunarCal( 40,  0, 0, 59, 0, 1, 0, 0, 1, 0, 1, 1, 0, 1, 1, 0, 1 ),
        new tagLunarCal( 28,  6, 2,  5, 1, 0, 1, 0, 0, 1, 0, 1, 0, 1, 1, 1, 0 ),
        new tagLunarCal( 47,  0, 3, 10, 1, 0, 1, 0, 0, 1, 0, 0, 1, 1, 1, 0, 1 ),
        new tagLunarCal( 36,  0, 4, 15, 1, 1, 0, 1, 0, 0, 1, 0, 0, 1, 1, 0, 1 ),
        new tagLunarCal( 25,  5, 5, 20, 1, 1, 1, 0, 1, 0, 0, 1, 0, 0, 1, 1, 0 ),
        new tagLunarCal( 43,  0, 0, 26, 1, 1, 0, 1, 0, 1, 0, 1, 0, 0, 1, 0, 1 ),
        new tagLunarCal( 32,  0, 1, 31, 1, 1, 0, 1, 1, 0, 1, 0, 1, 0, 1, 0, 0 ),
        new tagLunarCal( 22,  3, 2, 36, 0, 1, 1, 0, 1, 0, 1, 1, 0, 1, 0, 1, 0 )
    ]
};

Lunar.prototype = {
    solarYear: null,
    solarMonth: null,
    solarDate: null,
    solarHours: null,
    solarMinutes: null,
    solarCal: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
    solarDays: [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365, 396,  0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335, 366, 397],

    /**
     * 初始化
     */
    init: function() {
        var _o = this,
            today = new Date();

        _o.solarYear  = _o.getFullYear(today);
        _o.solarMonth = today.getMonth() + 1;
        _o.solarDate  = today.getDate();
        _o.solarHours = today.getHours();
        _o.solarMinutes = today.getMinutes();

        if (_o._settings.hoursTo != null) {
            _o.setTime(_o._settings.hoursTo, 'hours');
        }

        if (_o._settings.minutesTo != null) {
            _o.setTime(_o._settings.minutesTo, 'minutes');
        }

        if (_o._settings.dateTo != null) {
            _o.appendDate();
        }

        if (_o._settings.lang != 'en_US' && _o._settings.lunarTo != null) {
            if (_o.solarYear >= _o._settings.firstYear && _o.solarYear <= _o._settings.lastYear ) {
                _o.appendLunar();
            }
        }
    },

    /**
     * 
     * @param {Object} obj
     * @param {Object} type
     */
    setTime: function(obj, type) {
        var _o = this,
            firstNum = 0,
            secondNum = 0;

        if (type == 'hours') {
            var hours = String(_o.solarHours);
            if (hours.length < 2) {
                hours = '0' + hours;
            }

            firstNum = hours.substring(0,1);
            secondNum = hours.substring(1,2);
        } else if (type == 'minutes') {
            var minutes = String(_o.solarMinutes);
            if (minutes.length < 2) {
                minutes = '0' + minutes;
            }

            firstNum = minutes.substring(0,1);
            secondNum = minutes.substring(1,2);
        }

        obj.find('.time_number:first').removeClass('number_0').addClass('number_' + firstNum);
        obj.find('.time_number:last').removeClass('number_0').addClass('number_' + secondNum);
    },

    /**
     * 写入农历日期
     */
    appendLunar: function() {
        var _o   = this,
            lang = _o._settings.lang,
            html = Lang.lunar[lang],
            sm = null, d = null, leap = null, y = null, acc = null,
            lunarYear = null, lunarMonth = null, lunarDate = null;

        sm = _o.solarMonth - 1;
        if (sm < 0 || sm > 11) {
            return false;
        }

        leap = _o.getLeap(_o.solarYear);
        if (sm == 1) {
            d = leap + 28;
        } else {
            d = _o.solarCal[sm];
        }

        if (_o.solarDate < 1 || _o.solarDate > d) {
            return false;
        }

        y = _o.solarYear - _o._settings.firstYear;
        acc = _o.solarDays[leap * 14 + sm] + _o.solarDate;

        if (acc <= _o._settings.lunarCal[y].baseDays ) {
            y--;
            lunarYear = _o.solarYear - 1;
            leap = _o.getLeap(lunarYear);
            sm += 12;
            acc = _o.solarDays[leap * 14 + sm] + _o.solarDate;
        } else {
            lunarYear = _o.solarYear;
        }

        var l1 = _o._settings.lunarCal[y].baseDays;
        for (i = 0; i < 13; i++) {
            var l2 = l1 + _o._settings.lunarCal[y].monthDays[i] + 29;
            if (acc <= l2) {
                break;
            }
            l1 = l2;
        }

        lunarMonth = i + 1;
        lunarDate = acc - l1;
        im = _o._settings.lunarCal[y].intercalation;

        if (im != 0 && lunarMonth > im) {
            lunarMonth--;
            if (lunarMonth == im) {
                lunarMonth = -im;
            }
        }

        if (lunarMonth > 12) {
            lunarMonth -= 12;
        }

        // 处理闰月情况
        if (lunarMonth < 0) {
            html += '闰';
        }
        lunarMonth = lunarMonth.toString().replace('-', '');

        html += Lang.monthNumber[lunarMonth];
        html += Lang.month[lang];
        html += Lang.dateNumber[lunarDate];

        _o._settings.lunarTo.html(html);
    },

    /**
     * 写入日期
     */
    appendDate: function() {
        var _o   = this,
            dNow = _o.getDateInfo(),
            lang = _o._settings.lang,
            html = null;

        if (lang == 'en_US') {
            html = Lang.date[dNow.day];
            html += '&nbsp;';
            html += Lang.month[lang][dNow.month];
            html += ',&nbsp;';
            html += dNow.year;
            html += '&nbsp;';
            html += Lang.en_week[dNow.week];
        } else {
            html = dNow.year;
            html += Lang.year;
            html += dNow.month;
            html += Lang.month[lang];
            html += dNow.day;
            html += Lang.day;
            html += '&nbsp;&nbsp;';
            html += Lang.week[dNow.week];
        }

        _o._settings.dateTo.html(html);
    },

    /**
     * 设置参数
     *
     * @param {Object} key
     * @param {Object} val
     */
    setParam: function(key, val) {
        var params = null;
        if (typeof(key) == 'object') {
            params = key;
        } else if (typeof(key) == 'string' && val) {
            params = {};
            params[key] = val;
        }

        if (null == params) {
            return ;
        }

        this._settings = $.extend({}, Lunar.defaultSettings, params);
    },

    /**
     * 获取日期信息
     */
    getDateInfo: function() {
        var today = new Date();

        return {year: this.getFullYear(today), month: today.getMonth() + 1, day: today.getDate(), week: today.getDay()};
    },

    /**
     * 是否為閏年, 返回 0 為平年, 1 為閏年
     *
     * @param {Object} year
     */
    getLeap: function(year) {
        if (year % 400 == 0) {
            return 1;
        } else if (year % 100 == 0) {
            return 0;
        } else if (year % 4 == 0) {
            return 1;
        } else {
            return 0;
        }
    },

    /**
     * 
     * @param {Object} date
     */
    getFullYear: function(date) {
        var year = date.getYear();
        if (year < 1000) {
            year += 1900;
        }

        return year;
    }
};

function tagLunarCal(d, i, w, k, m1, m2, m3, m4, m5, m6, m7, m8, m9, m10, m11, m12, m13) {
    this.baseDays = d;         /* 到民國 1 月 1 日到農曆正月初一的累積日數 */
    this.intercalation = i;    /* 閏月月份. 0==此年沒有閏月 */
    this.baseWeekday = w;      /* 此年民國 1 月 1 日為星期幾再減 1 */
    this.baseKanChih = k;      /* 此年民國 1 月 1 日之干支序號減 1 */
    this.monthDays = [m1, m2, m3, m4, m5, m6, m7, m8, m9, m10, m11, m12, m13]; /* 此農曆年每月之大小, 0==小月(29日), 1==大月(30日) */
}