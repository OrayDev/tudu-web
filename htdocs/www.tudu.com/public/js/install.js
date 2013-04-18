/**
 * 图度开源安装向导(JS对象)
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: install.js 2799 2013-03-29 10:04:06Z chenyongfa $
 */

var Install = {    
    initConfig: function() {
        var form = $('#theform'), _o = this;
        form.submit(function(){return false;});
        form.submit(function(){
            if (!$('input[name="dbinfo[host]"]').val().replace(/^\s+|\s+$/, '')) {
                return _o.showMessage('请输入数据库服务器地址', false);
            }
            if (!$('input[name="dbinfo[port]"]').val().replace(/^\s+|\s+$/, '')) {
                return _o.showMessage('请输入数据库服务器端口号', false);
            }
            if (!$('input[name="dbinfo[database]"]').val().replace(/^\s+|\s+$/, '')) {
                return _o.showMessage('请输入数据库名', false);
            }
            if (!$('input[name="dbinfo[user]"]').val().replace(/^\s+|\s+$/, '')) {
                return _o.showMessage('请输入数据库账号', false);
            }
            if (!$('input[name="httpsqs[host]"]').val().replace(/^\s+|\s+$/, '')) {
                return _o.showMessage('请输入HttpSQS服务器地址', false);
            }
            if (!$('input[name="httpsqs[port]"]').val().replace(/^\s+|\s+$/, '')) {
                return _o.showMessage('请输入HttpSQS服务器端口号', false);
            }
            if (!$('input[name="memcache[host]"]').val().replace(/^\s+|\s+$/, '')) {
                return _o.showMessage('请输入Memcache服务器地址', false);
            }
            if (!$('input[name="memcache[port]"]').val().replace(/^\s+|\s+$/, '')) {
                return _o.showMessage('请输入Memcache服务器端口号', false);
            }
            if (!$('input[name="tudu[orgid]"]').val().replace(/^\s+|\s+$/, '')) {
                return _o.showMessage('请输入云办公系统ID', false);
            }
            if (!$('input[name="tudu[orgname]"]').val().replace(/^\s+|\s+$/, '')) {
                return _o.showMessage('请输入公司名称', false);
            }
            if (!$('input[name="tudu[userid]"]').val().replace(/^\s+|\s+$/, '')) {
                return _o.showMessage('请输入超级管理员账号', false);
            }
            if (!$('input[name="tudu[password]"]').val().replace(/^\s+|\s+$/, '')) {
                return _o.showMessage('请输入超级管理员密码', false);
            }
            if ($('input[name="tudu[password]"]').val() != $('input[name="tudu[password2]"]').val()) {
                return _o.showMessage('两次输入的密码不一致', false);
            }

            var data = form.serializeArray();

            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: data,
                url: form.attr('action'),
                success: function(ret) {
                    if (!ret.success) {
                        return _o.showMessage(ret.message, false);
                    } else {
                        location = ret.data.url;
                    }
                },
                error: function(res) {}
            });
        }); 
    },

    showMessage: function(msg, success, timeout, callback) {
        var o = $('#msg-info');
        if (!o.size()) {
            o = $('<div id="msg-info" class="msg"><span></span></div>');
            o.prependTo(document.body);
        }
        if (o[0].timer) {
            clearTimeout(o[0].timer);
        }
        if (!msg) {
            return o.fadeOut();
        }
        if (typeof(timeout) != 'number') {
            timeout = 6000;
        }

        o.find('span:eq(0)').toggleClass('error', !(success == undefined || success)).html(msg);

        if (o.is(':hidden')) {
            o.fadeIn();
        }
        if (timeout > 0) {
            o[0].timer = setTimeout(function(){
                o.fadeOut('normal', callback);
            }, timeout);
        }

        return success;
    },
 };
