/*
 * jQuery Stepper
 *
 * Copyright (c) 2010 Hiro <hiro@oray.com>
 * 
 * $Id: jquery.stepper.js 2627 2013-01-08 02:04:34Z chenyongfa $
 */
;(function($) { // secure $ jQuery alias

$.fn.stepper = function(opts) {

    opts = $.extend({ 
        min: 0,
        max: 10,
        step: 1,
        start: 0,
        decimals: 0,
        format: '',
        symbol: '',
        callback: null
    }, opts || {});

    opts.pow = Math.pow(10, opts.decimals);

    var keys = {
        BACK: 8,
        TAB: 9,
        LEFT: 37,
        UP: 38,
        RIGHT: 39,
        DOWN: 40,
        PGUP: 33,
        PGDN: 34,
        HOME: 36,
        END: 35,
        PERIOD: 190,
        MINUS: 109,
        NUMPAD_DECIMAL: 110,
        NUMPAD_SUBTRACT: 109
    };

    function spin(o, v, adjust) {
        var value = parseFloat(o.value);
        if (undefined == adjust || adjust) {
            var n = Math.round(value * opts.pow) % (opts.step * opts.pow);
            if (n != 0) {
                value -= n / opts.pow;
                if (v < 0) v = 0;
            }
        }
        value = parseFloat(value) + parseFloat(v);
        if (isNaN(value)) value = opts.start;
        if (value < opts.min) value = opts.min;
        if (value > opts.max) value = opts.max;

        o.value = format(value);

        if (typeof opts.callback == 'function') {
            return opts.callback();
        }
    }

    function format(v) {
        v = Math.round(parseFloat(v)*opts.pow) / opts.pow;
        if (opts.format == 'percent') {
            return v + '%';
        }
        return v;
    }

    function allowedKey(key){
        // add support for numeric keys 0-9
        if (key >= 96 && key <= 105) {
            key = 'NUMPAD';
        }

        switch (key) {
            case keys.TAB :
            case keys.BACK :
            case keys.LEFT :
            case keys.RIGHT :
            case keys.PERIOD :
            case keys.MINUS :
            case keys.NUMPAD_DECIMAL :
            case keys.NUMPAD_SUBTRACT :
            case 'NUMPAD' :
                return true;
            default : 
                return (/[0-9\-\.]/).test(String.fromCharCode(key));
        }
    }

    function getRealWidth(o) {
        var display = o.css('display');

        if (display != 'none' && display !== null && !o.parents(':hidden').size()) {
            return o.outerWidth();
        }

        var c = o.clone();
        c.css({'position': 'absolute', left: -999});
        c.appendTo(document.body);

        var w = c.outerWidth();

        c.remove();

        return w;
    }

    return this.each(function() {
        var self = this;
        if (this.type != 'text') return;
        var input = $(this).addClass('stepper-input').attr('autocomplete', 'off'),
            width = getRealWidth(input);

        var ns = $('<span class="stepper-wrap"><span class="stepper-inner"></span></span>').css('width', width);

        input.wrap(ns);

        var plus = $('<button class="stepper-plus" type="button"></button>'); // plus button
        var minus = $('<button class="stepper-minus" type="button"></button>'); // minus button

        input.after(plus, minus);

        if (!self.value) self.value = format(opts.start);

        input
        .bind("keydown.stepper", function(e) {
            var keyCode = (window.event ? event.keyCode : (e.which ? e.which : null));
            switch (keyCode) {
                // plus
                case keys.UP :
                case keys.PGUP :
                    spin(self, opts.step);
                    break;
                //minus
                case keys.DOWN :
                case keys.PGDN :
                    spin(self, -opts.step);
                    break;
            }
            $('#hint').html(keyCode);
            return allowedKey(keyCode);
        })
        .bind('DOMMouseScroll mousewheel', function(e, delta) {
            if (e.wheelDelta) delta = e.wheelDelta/120;
            if (e.detail) delta = -e.detail/3;
            if ($.browser.opera) delta = -e.wheelDelta;
            if (delta > 0)
                spin(self, opts.step);
            else if (delta < 0)
                spin(self, -opts.step);
            return false;
        })
        .bind('blur', function(e) {
            spin(self, 0, false);
        })

        plus
        .bind('click', function(e){
            spin(self, opts.step);
        });

        minus
        .bind('click', function(e){
            spin(self, -opts.step);
        });
    })
};

})(jQuery); // confine scope