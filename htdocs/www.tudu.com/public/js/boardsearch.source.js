/**
 * 板块图度搜索

 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: boardsearch.source.js 2790 2013-03-22 02:31:43Z chenyongfa $
 */
var BoardSearch = {
    /**
     * 初始化搜索结果
     */
    init: function() {
        var _o = this;

        $('#theform').submit(function(){
            var form = $(this);

            if (!form.find('input[name="keyword"]').val()
                && !form.find('input[name="to"]').val()
                && !form.find('input[name="from"]').val()
                && !form.find('input[name="time"]').val())
            {
                form.find('input[name="keyword"]').focus();
                TOP.showMessage('{{$LANG.missing_search_condition}}');
                return false;
            }
        });

        var matchData = {};
        matchData.users = TOP.Cast.get('users');

        new $.autocomplete({
            target: $('#inputfrom'),
            data: matchData,
            loadMethod: function() {
                var _v = this,
                    keyword = $('#inputfrom').val();
                TOP.Cast.load(function(){
                    TOP.Contact.load(function(){
                        _v.data.users = TOP.Cast.get('users');
                        _v._initMatchList(keyword);
                    })
                });
            },
            columns: {users: ['truename', 'username', 'pinyin']},
            width: 220,
            arrowSupport: true,
            template: {
                users:'{truename} <span class="gray">&lt;{username}&gt;</span>'
            },
            onSelect: function(item){
                $('#inputfrom').val(item.data.truename);
            }
        });

        new $.autocomplete({
            target: $('#inputto'),
            data: matchData,
            loadMethod: function() {
                var _v = this,
                    keyword = $('#inputto').val();
                TOP.Cast.load(function(){
                    TOP.Contact.load(function(){
                        _v.data.users = TOP.Cast.get('users');
                        _v._initMatchList(keyword);
                    })
                });
            },
            columns: {users: ['truename', 'username', 'pinyin']},
            width: 220,
            arrowSupport: true,
            template: {
                users:'{truename} <span class="gray">&lt;{username}&gt;</span>'
            },
            onSelect: function(item){
                $('#inputto').val(item.data.truename);
            }
        });

        this.initSelectLink({obj: '#select-from', valInput: '#inputfrom', tempInput: '#from', maxCount: 1, enableGroup: false});
        this.initSelectLink({obj: '#select-to', valInput: '#inputto', tempInput: '#to', maxCount: 1, enableGroup: false});
    },

    /**
     * 初始化联系人选择窗口
     *
     * @param {Object} config
     */
    initSelectLink: function(config) {
        var _o = this;

        $(config.obj).click(function(){
            var title = $(this).text();
            var val = $(config.tempInput).val();
            var selected = new Array();
            if (val) {
                val = val.split("\n");
                for (var i = 0, c = val.length; i < c; i++) {
                    var l = val[i];
                    if (l.indexOf('>') == 0 || l.indexOf('+') == 0) {
                        selected.push({separator: l});
                    } else {
                        var a = l.split(' ');
                        selected.push({
                            _id: a[0].replace(/^#+/, ''),
                            name: a[1]
                        });
                    }
                }
            } else {
                selected = null;
            }

            var html = '<div class="pop pop_linkman"><div class="pop_header"><strong>'+TOP.TEXT.SELECT_CONTACT+'</strong><a class="icon icon_close close"></a></div><div class="pop_body"></div><div class="pop_footer"><button type="button" name="confirm" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" class="btn close">'+TOP.TEXT.CANCEL+'</button></div></div>';

            var Win = TOP.Frame.TempWindow;
            Win.append(html, {
                width:470,
                draggable: true,
                onShow: function() {
                    Win.center();
                },
                onClose: function() {
                    Win.destroy();
                }
            });

            var selector = new TOP.ContactSelector({appendTo: Win.find('div.pop_body'), enableGroup: config.enableGroup, maxCount:config.maxCount, panels: ['common'], selected: selected});

            if (title.length) {
                Win.find('div.contact_selector_left').prepend('<p>'+TOP.TEXT.CONTACT+'</p>');
                Win.find('div.contact_selector_right').prepend('<p>'+title+'</p>');
            }

            Win.find('button[name="confirm"]').bind('click', function(){
                var se = selector.getSelected(),
                    val = [],
                    temp = [];

                for (var i = 0, c = se.length; i < c; i++) {
                    if(se[i].email) {
                        val.push(se[i].name);
                        temp.push(se[i].email + ' ' + se[i].name);
                    }
                }

                $(config.valInput).val(val.join(''));
                $(config.tempInput).val(temp.join("\n"));

                Win.close();
            });

            Win.show();
        });
    }
};

if (typeof(getTop) != 'function') {
    function getTop() {
        return parent;
    }
}

var TOP = TOP || getTop();