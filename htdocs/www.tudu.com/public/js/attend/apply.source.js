/**
 * 考勤申请JS功能
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: apply.source.js 2769 2013-03-07 10:09:47Z chenyongfa $
 */
var Attend = Attend || {};

Attend.Apply = {
    /**
     * 编辑器
     */
    editor: null,

    /**
     * 抄送控件
     */
    ccInput: null,

    /**
     * 代办控件
     */
    targetInput: null,

    /**
     * 网盘窗口
     */
    filedialog: null,

    /**
     * 上传
     */
    upload: null,

    /**
     * 用户设置的默认编辑器字体
     */
    editorCss: {},

    /**
     * 初始化页面
     */
    init: function(forbid, targetParams, back) {
        var o = this;

        $('button[name="back"]').bind('click', function(){
            location = back;
        });

        // 编辑器
        var h = $(window).height(),
            ch = $(document.body).height(),
            editorHeight = Math.max($('#content').height() + (h - ch - 15), 200);
        $('#content').css('height', editorHeight + 'px');
        this.editor = new TOP.Editor(document.getElementById('content'), {
            resizeType : 1,
            width: '100%',
            minHeight: 200,
            themeType : 'tudu',
            css: o.editorCss,
            scope: window,
            pasteType: 2,
            disabled: forbid.editor,
            ctrl: {
                13: function(){$('#action').val('send');o.send('send');}
            }
        }, jQuery);

        // 地图
        $('#map-btn').click(function(){
            o.editor.getEditor().loadPlugin('googlemap', function() {
                o.editor.getEditor().plugin.mapDialog();
            });
        });

        // 初始化抄送人控件
        this.ccInput = new TOP.ContactInput({
            id: 'cc-input', target: $('#i-cc'), valuePlace: $('#cc'), group: true, jq: jQuery
        });
        this.initSelectLink('#select-cc', this.ccInput, $('#cc'), true);

        if (targetParams.init && $('#row-target').size()) {
            this.targetInput = new TOP.ContactInput({
                id: 'target-input', target: $('#i-target'), valuePlace: $('#target'), group: false, jq: jQuery, maxCount: 1,
                depts: targetParams.depts, contact: false
            });
            this.initSelectLink('#select-target', this.targetInput, $('#target'), false, 1, targetParams.depts.join(','), ['common']);
            if (forbid.target) {
                this.targetInput.disabled();
            }
        }

        // 抄送
        $('#add-cc').bind('click', function(){
            if ($(this).hasClass('disabled')) {
                return ;
            }

            o.toggleCC();
        });

        // 代办
        $('#add-target').bind('click', function(){
            if (!targetParams.init || $(this).hasClass('disabled')) {
                return ;
            }

            o.toggleTarget();
        });

        $('#period').bind('keyup', function(){
            this.value = this.value.replace(/[^0-9.]+/, '');
            if (this.value.indexOf('.') != -1) {
                var val = this.value.split('.');
                if (typeof val[1] != 'undefined' && val[1].length > 1) {
                    this.value = val[0] + '.' + val[1].substring(0, 1);
                }
            }
        }).blur(function(){
            $(this).val(this.value);
        });

        // 截图
        if (!TOP.Browser.isIE && !TOP.Browser.isFF) {
            $('#screencp-btn').remove();
        } else {
            $('#link-capture').bind('click', function(){
                if (!Capturer.getCapturer()) {
                    return Capturer.install();
                }

                Capturer.setEditor(o.editor);
                Capturer.startCapture();
            });
        }

        // 网盘
        $('#netdisk-btn').click(function(){
            if (o.filedialog === null) {
                o.filedialog = new FileDialog({id: 'netdisk-dialog'});
            }

            o.filedialog.show();
        });

        $(':radio[name="categoryid"]').bind('click', function(){
            if (this.value == '^checkin') {
                $('#row-timetype').hide();
                $('#row-startdate').hide();
                $('#row-enddate').hide();
                $('#row-date').hide();
                $('#row-checkin').show();
                $('#row-checkintime').show();
                $('#row-total').hide();
                o.updateTimeInput(true);
            } else {
                o.changeCategory(this.value);
                $('#row-timetype').show();
                $('#row-checkin').hide();
                $('#row-checkintime').hide();
                var isallady = parseInt($(':radio[name="isallday"]:checked').val());
                if (isallady) {
                    $('#row-startdate').show();
                    $('#row-enddate').show();
                    $('#row-date').hide();
                } else {
                    $('#row-startdate').hide();
                    $('#row-enddate').hide();
                    $('#row-date').show();
                }
                $('#row-total').show();
                o.updateTimeInput(false);
            }
        });

        $(':radio[name="isallday"]').bind('click', function(){
            var isallady = parseInt(this.value);
            if (isallady) {
                $('#row-startdate').show();
                $('#row-enddate').show();
                $('#row-date').hide();
            } else {
                $('#row-startdate').hide();
                $('#row-enddate').hide();
                $('#row-date').show();
            }
            o.updateTimeInput(false);
        });

        $('#startdate').datepick({
            showOtherMonths: true,
            selectOtherMonths: true,
            firstDay: 0,
            showAnim: 'slideDown',
            showSpeed: 'fast',
            showTimePanel: false,
            showButtonPanel: false,
            onSelect: function(dates) {
               $('#enddate').datepick('option', {minDate: dates});
               o.updateTimeInput(false);
            }
        });

        $('#enddate').datepick({
            showOtherMonths: true,
            selectOtherMonths: true,
            firstDay: 0,
            showAnim: 'slideDown',
            showSpeed: 'fast',
            showTimePanel: false,
            showButtonPanel: false,
            onSelect: function(dates) {
               $('#startdate').datepick('option', {maxDate: dates});
               o.updateTimeInput(false);
            }
        });

        $('#date').datepick({
            showOtherMonths: true,
            selectOtherMonths: true,
            firstDay: 0,
            showAnim: 'slideDown',
            showSpeed: 'fast',
            showTimePanel: false,
            showButtonPanel: false,
            onSelect: function(dates) {
                o.checkVal();
                o.updateTimeInput(false);
            }
        });
        $('#starthour').stepper({step: 1, max:23, min: 0, callback: function(){o.checkVal();o.updateTimeInput(false);}});
        $('#startmin').stepper({step: 15, max:59, min: 0, callback: function(){o.checkVal();o.updateTimeInput(false);}});
        $('#endhour').stepper({step: 1, max:23, min: 0, callback: function(){o.checkVal();o.updateTimeInput(false);}});
        $('#endmin').stepper({step: 15, max:59, min: 0, callback: function(){o.checkVal();o.updateTimeInput(false);}});

        // 补签时间部分
        $('#checkindate').datepick({
            showOtherMonths: true,
            selectOtherMonths: true,
            firstDay: 0,
            showAnim: 'slideDown',
            showSpeed: 'fast',
            showTimePanel: false,
            showButtonPanel: false,
            onSelect: function(dates) {
               o.updateTimeInput(true);
            }
        });
        $('#checkinhour').stepper({step: 1, max:23, min: 0, callback: function(){o.updateTimeInput(true);}});
        $('#checkinmin').stepper({step: 15, max:59, min: 0, callback: function(){o.updateTimeInput(true);}});
        $(':radio[name="checkintype"]').bind('click', function(){
            o.updateTimeInput(true);
        });

        $('#theform').submit(function(){return false;});
        $('button[name="send"],button[name="save"]').bind('click', function(){
            $('#action').val(this.name);
            o.send(this.name);
        });

        $('button[name="preview"]').click(function(){
            o.getFormPreview('#theform', '/app/attend/apply/preview', '_blank');
        });

        setTimeout(function(){o.clearCast();}, 1000);
    },

    /**
     * 更新时间
     *
     * @param {Object} isCheckin
     */
    updateTimeInput: function(isCheckin) {
        if (isCheckin) {
            if (!$(':radio[name="checkintype"]:checked').size()) {
                return ;
            }

            var checkintype = parseInt($(':radio[name="checkintype"]:checked').val()),
                cd = $('#checkindate').val();

            if (!checkindate) {
                return ;
            }

            var ch = $('#checkinhour').val(),
                cm = $('#checkinmin').val();

            if (checkintype) {
                $('#endtime').val(cd + ' ' + ch + ':' + cm);
                $('#starttime').val('');
            } else {
                $('#starttime').val(cd + ' ' + ch + ':' + cm);
                $('#endtime').val('');
            }
        } else {
            if (!$(':radio[name="isallday"]:checked').size()) {
                return ;
            }

            var isAllday = parseInt($(':radio[name="isallday"]:checked').val());
            if (isAllday) {
                var sd = $('#startdate').val(),
                    ed = $('#enddate').val();
                $('#starttime').val(sd);
                $('#endtime').val(ed);
            } else {
                var date = $('#date').val();

                if (!date) {
                    return ;
                }

                var sh = $('#starthour').val(),
                    sm = $('#startmin').val(),
                    eh = $('#endhour').val(),
                    em = $('#endmin').val();

                $('#starttime').val(date + ' ' + sh + ':' + sm);
                $('#endtime').val(date + ' ' + eh + ':' + em);
            }
        }
    },

    /**
     * 检验时间值
     */
    checkVal: function() {
        var date = $('#date').val();

        if (!date) {
            return;
        }

        var s = new Date(),
            e = new Date(),
            sd = date.split('-'),
            ed = date.split('-');

        s.setFullYear(sd[0]);
        s.setMonth(sd[1]);
        s.setDate(sd[2]);

        e.setFullYear(ed[0]);
        e.setMonth(ed[1]);
        e.setDate(ed[2]);

        if (e.getTime() > s.getTime()) {
            return;
        } else if (e.getTime() == s.getTime()) {
            var sh = $('#starthour').val(),
                sm = $('#startmin').val(),
                eh = $('#endhour').val(),
                em = $('#endmin').val();

            s.setHours(sh);
            s.setMinutes(sm);
            e.setHours(eh);
            e.setMinutes(em);

            if (s.getTime() > e.getTime()) {
                $('#endhour').val(s.getHours());
                $('#endmin').val(s.getMinutes());
            }
        }
    },

    /**
     * 加载月申请总小时
     *
     * @param {Object} id
     */
    changeCategory: function(id) {
        $.ajax({
            method: 'GET',
            dataType: 'json',
            url: '/app/attend/apply/summary?categoryid=' + id,
            success: function(ret) {
                if (ret.success && ret.data) {
                    var name = $('label[for="c-'+id+'"]').text();
                    $('#type-sum').text('本月已申请 ' + name + ' ' + ret.data.summary + ' 小时');
                }
            },
            error: function(res) {}
        });
    },

    /**
     * 添加抄送、删除抄送
     */
    toggleCC: function() {
        if (!$('#row-cc:visible').size()) {
            $('#cc, #i-cc').attr('disabled', false);
            $('#row-cc').show();
            $('#add-cc').text(TOP.TEXT.DELETE_CC);
        } else {
            $('#cc, #i-cc').attr('disabled', true);
            $('#row-cc').hide();
            $('#add-cc').text(TOP.TEXT.ADD_CC);
        }
    },

    /**
     * 添加代办，删除代办
     */
    toggleTarget: function() {
        if (!$('#row-target:visible').size()) {
            $('#target, #i-target').attr('disabled', false);
            $('#row-target').show();
            $('#type-sum').hide();
            $('#add-target').text('删除代办');
        } else {
            $('#target, #i-target').attr('disabled', true);
            $('#row-target').hide();
            $('#type-sum').show();
            $('#add-target').text('添加代办');
        }
    },

    /**
     * 构造表单，提交预览或页面数据传递
     * @param form
     * @param target
     * @return
     */
    getFormPreview: function(form, address, target) {
        $('#postcontent').val(Attend.Apply.editor.getSource());
        var data = $(form).serializeArray();
        var form = $('<form action="'+address+'" method="post" target="'+target+'" style="display:none"></form>');
        for (var key in data) {
            form.append('<textarea name="' + data[key].name + '">' + data[key].value + '</textarea>');
        }
        form.append('<input name="autosave" value="1" />');
        form.appendTo(document.body).submit();
    },

    /**
     * 是否执行清除CAST数据
     */
    clearCast: function() {
        var loadTime = TOP.Cast.getTime();
        if (loadTime) {
            $.ajax({
                type: 'GET',
                dataType: 'json',
                url: '/tudu/clear-cast?loadtime=' + loadTime,
                success: function(ret) {
                    if (ret.data && ret.data.clear) {
                        TOP.Cast.clear();
                    }
                },
                error: function() {}
            });
        }
    },

    /**
     * 选择联系人连接
     */
    initSelectLink: function(obj, mailInput, valInput, containGroup, limit, childOf, panels) {
        if (!containGroup) {
            containGroup = false;
        }

        $(obj).click(function(){
            var instance = this;
            var title = $(this).text();

            var val = valInput.val();
            var selected = [], userid = null;
            if (val) {
                val = val.split("\n");
                for (var i = 0, c = val.length; i < c; i++) {
                    var a = val[i].split(' ');
                    selected.push({
                        _id: a[0].replace(/^#+/, ''),
                        name: a[1]
                    });
                }
            } else {
                selected = null;
            }

            var html = '<div class="pop pop_linkman"><div class="pop_header"><strong>'+TOP.TEXT.SELECT_CONTACT+'</strong><a class="icon icon_close close"></a></div><div class="pop_body"></div><div class="pop_footer"><button type="button" name="confirm" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" class="btn close">'+TOP.TEXT.CANCEL+'</button></div></div>';

            var Win = TOP.Frame.TempWindow;
            Win.append(html, {
                width:470,
                draggalbe: true,
                onShow: function() {
                    Win.center();
                },
                onClose: function() {
                    Win.destroy();
                }
            });

            var params = {appendTo: Win.find('div.pop_body'), enableGroup: containGroup, selected: selected, mailInput: mailInput, childOf: undefined !== childOf ? childOf : null};
            if (undefined !== limit) {
                params.maxSelect = limit;
            }
            if (undefined !== panels) {
                params.panels = panels;
            }

            var selector = new TOP.ContactSelector(params);
            var panel = TOP.Cookie.get('CONTACT-PANEL');
            if (!panel) {
                panel = 'common';
            }
            selector.switchPanel(panel);

            Win.find('button[name="confirm"]').bind('click', function(){
                var se = selector.getSelected();

                for (var i = 0, c = se.length; i < c; i++) {
                    var p = {};

                    if (se[i].groupid) {
                        p.title = se[i].name + '&lt;' + TOP.TEXT.GROUP + '&gt;';
                        p._id = se[i].groupid
                    } else {
                        p.title = se[i].name + (se[i].email ? '$lt;' + se[i].email + '&gt;' : '');
                        p._id = se[i].email ? se[i].email : '';
                    }
                    p.name = se[i].name;
                    mailInput.addItem(se[i].name, p);
                }
                Win.close();
            });

            Win.show();
        });
    },

    /**
     * 发送/保存
     *
     * @param {Object} action
     */
    send: function(action, callback) {
        var form = $('#theform'),
            o    = this;

        if (action != 'autosave') {
            if (!$(':radio[name="categoryid"]:checked').size() && !$('#categoryid').val()) {
                return TOP.showMessage('请选择申请类型');
            }
            var categoryid = form.find('input[name="categoryid"]:checked').val();

            if (categoryid != '^checkin' && !$(':radio[name="isallday"]:checked').size()) {
                return TOP.showMessage('请选择时间类型');
            }

            if (categoryid == '^checkin' && !$(':radio[name="checkintype"]:checked').size()) {
                return TOP.showMessage('请选择补签类型');
            }

            var cc = this.ccInput.getItems()
                i = 0;

            if (this.targetInput !== null) {
                var to = this.targetInput.getItems();
                to.each(function(){
                    if ($(this).attr('title') == TOP.TEXT.INVALID_TO_USER) {
                        i++;
                    }
                });
            }

            cc.each(function (){
                if ($(this).attr('title') == TOP.TEXT.INVALID_TO_USER) {
                    i++;
                }
            });
            if (i >0) {
                return TOP.showMessage(TOP.TEXT.TUDU_ACCEPTER_EMAIL_ERROR);
            }

            if (categoryid == '^checkin' && !$('#checkindate').val()) {
                $('#checkindate').focus();
                return TOP.showMessage('请输入补签时间');
            }

            if (categoryid != '^checkin') {
                var isAllday = $(':radio[name="isallday"]:checked').val();
                if (parseInt(isAllday)) {
                    if (!$('#startdate').val()) {
                        $('#startdate').focus();
                        return TOP.showMessage('请输入开始时间');
                    }
                    if (!$('#enddate').val()) {
                        $('#enddate').focus();
                        return TOP.showMessage('请输入结束时间');
                    }
                } else {
                    if (!$('#date').val()) {
                        $('#date').focus();
                        return TOP.showMessage('请输入开始时间');
                    }
                }

                if (!$('#period').val()) {
                    $('#period').focus();
                    return TOP.showMessage('请输入申请小时总数');
                }
            }

            if (!TOP.Device.iOS && !TOP.Device.Android && this.editor !== null && this.editor.isNull()) {
                this.editor.focus();
                return TOP.showMessage('请输入申请理由');
            }
        }

        if (TOP.Device.iOS || TOP.Device.Android) {
            var src = $('#content').val();
            src = src.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br />');
        } else {
            var src = this.editor.getSource();
            if (!checkContentImage(src, this.editor, function(){o.send(action, callback);})) {
                return ;
            }

            var reg = /<img([^>]+)src="([^"]+)"([^>]+)_aid="([^"]+)"([^>]+)\/>/ig;
            form.find(':hidden[name="file[]"]').remove();
            while ((result = reg.exec(src)) != null) {
                form.append('<input type="hidden" name="file[]" value="'+result[4]+'" />');
            }

            src = src.replace(reg, '<img$1src="AID:$4"$3_aid="$4"$5/>');
            src = src.replace(/\s+id="[^"]+"/g, '');
        }

        $('#postcontent').val(src);

        if (action != 'autosave') {
            if (!whileUploading(this.upload, TOP.TEXT.WAITING_UPLOAD, function(){Modify.send(action, callback);}, form)) {
                return ;
            }

            if ($('#attach-list div.upload_error').size()) {
                if (!confirm(TOP.TEXT.COMPOSE_UPLOAD_FAILURE)) {
                    return ;
                }
            }
        }

        var data = form.serializeArray();

        if (action != 'autosave') {
            TOP.showMessage(TOP.TEXT.POSTING_DATA, 0, 'success');
            form.find(':input').attr('disabled', true);
        }

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                if (action != 'autosave') {
                    TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
                }
                if (ret.data) {
                    $('#ftid').val(ret.data.tuduid);
                }
                if (ret.success) {
                    if (typeof(callback) == 'function') {
                        return callback();
                    }
                    var url = '';
                    if (action == 'send' && ret.data) {
                        if (typeof(_NEW_WIN) != 'undefined' && _NEW_WIN) {
                            url = '/app/attend/apply/view?tid=' + ret.data.tuduid + '&newwin=1';
                        } else {
                            url = '/app/attend/apply/view?tid=' + ret.data.tuduid;
                        }

                        location = url;
                    } else {
                        form.find(':input:not([_disabled])').attr('disabled', false);
                    }
                    return ;
                } else {
                    form.find(':input:not([_disabled])').attr('disabled', false);
                }
            },
            error: function(res) {
                form.find(':input:not([_disabled])').attr('disabled', false);
                if (action != 'autosave') {
                    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
                }
            }
        });
    }
};

/**
 * 网盘附件文件选择
 * 
 * @param params
 * @return
 */
var FileDialog = function(params) {
    this._settings = $.extend({}, params);

    this.listCt = this._settings.listCt ? this._settings.listCt : $('#attach-list');
    this.list   = this._settings.list ? this._settings.list : $('#attach-list td.bd_upload');
};
FileDialog.filetpl = [
    '<div class="filecell"><input type="hidden" name="nd-attach[]" value="" /><input type="hidden" name="attach[]" value="" />'
    ,'<div class="attsep"><div class="attsep_file"><span class="icon icon_add"></span><span class="filename"></span>&nbsp;<span class="filesize"></span></div>'
    ,'<div class="attsep_del"><a href="javascript:void(0)" name="delete">' + TOP.TEXT.DELETE + '</a></div>'
    ,'<div class="clear"></div></div></div>'
].join('');
FileDialog.tpl = '<div class="pop pop_linkman"><div class="pop_header"><strong>'+TOP.TEXT.NETDISK_ATTACH+'</strong><a class="icon icon_close close"></a></div><div class="pop_body" style="padding:10px"><p class="gray">'+TOP.TEXT.SELECT_NETDISK_FILE+'</p></div><div class="pop_footer"><button type="button" name="confirm" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" name="cancel" class="btn close">'+TOP.TEXT.CANCEL+'</button></div></div>';
FileDialog.prototype = {

    win: null,

    upload: null,

    _settings: null,

    list: null,

    listCt: null,

    init: function() {
        var Win = TOP.Frame.TempWindow, _o = this;
        Win.append(FileDialog.tpl, this._settings.id, {
            width: 300,
            draggable: true,
            onClose: function() {
                Win.destroy();
            }
        });

        if (this._settings.upload) {
            this.initUpload();
        }

        var netdiskPanel = new TOP.NetdiskPanel();
        netdiskPanel.renderTo(Win.find('.pop_body'));

        Win.find('button[name="confirm"]').click(function(){
            var selected = netdiskPanel.getFileSelected();
            
            for (var i = 0, c = selected.length; i < c; i++) {
                if (!selected[i].fileid) {
                    continue ;
                }
                _o.appendToAttachment(selected[i].fileid, selected[i].filename, selected[i].filesize)
            }

            Win.close();
        });

        Win.show();
    },

    show: function() {
        this.init();
    },

    appendToAttachment: function(fileid, filename, filesize) {
        var _o = this;
        var el = $(FileDialog.filetpl);

        if (_o.list.find('#nd-file-' + fileid).size()) {
            return ;
        }

        el
        .attr('id', 'nd-file-' + fileid)
        .find('.filename').text(filename);

        el.find(':hidden[name="nd-attach[]"]').val(fileid);
        el.find(':hidden[name="attach[]"]').val(fileid);

        filesize = filesize > 1024 ? Math.round(filesize / 1024, 2) + 'KB' : filesize + 'bytes',
        el.find('.filesize').text('(' + filesize + ')');
        el.find('a[name="delete"]').click(function(){
            el.remove();
            if (!_o.list.find('.filecell').size()) {
                _o.listCt.hide();
            }
        });

        _o.list.append(el);
        _o.listCt.show();
    }
};

/**
 * 截图
 */
var Capturer = {
    capturer: null,
    editor: null,
    uploaddialog: null,
    uploadurl: null,
    installed: null,

    setEditor: function(editor) {
        this.editor = editor;
    },

    setUploadUrl: function(url) {
        this.uploadurl = url;
    },

    install: function() {
        var btns = [
           {
               text: TOP.TEXT.INSTALL_ONLINE,
               cls: 'btn',
               events: {click: function(){
                   if (confirm(TOP.TEXT.INSTALL_LEAVE_CONFIRM)) {
                       top.location = '/plugin/screencapture?back=' + encodeURIComponent(TOP.Frame.hash());
                       Win.close();
                   }
               }}
           },
           {
               text: TOP.TEXT.CANCEL,
               cls: 'btn close',
               events: {click: function(){Win.close()}}
           }
        ];

        var Win = TOP.Frame.Dialog.show({
            title: TOP.TEXT.CAPTURER_INSTALL_TIPS,
            body: '<div class="screen_lock"><div><span class="icon icon_attention_big"></span><strong>'+TOP.TEXT.UNINSTALL_CAPTURER_TOOLS_TIPS+'</strong></div><ul><li>'+TOP.TEXT.AFFTER_INSTALL+TOP.TEXT.COMMA+TOP.TEXT.CLICK+'&nbsp;<span class="icon icon_screencp"></span><a>'+TOP.TEXT.CAPTURER+'</a>&nbsp;'+TOP.TEXT.COMMA+TOP.TEXT.USE_CAPTURER_NOW+'</li></ul></div>',
            buttons: btns
        });
    },

    getCapturer: function() {
        if (null === this.capturer && false !== this.installed) {
            var me = this;

            this.capturer = new ScreenCapture({
                onCaptured: function() {
                    me.uploaddialog = TOP.Frame.Dialog.show({
                        body:  '<div>' + TOP.TEXT.FILE_UPLOADING + '</div><div class="progress_large" style="width:420px;margin:10px 0"><div class="bar"></div></div>',
                        title: TOP.TEXT.FILE_UPLOADING,
                        close: false
                    }).getWin();
                },
                onUploaded: function(uploader) {
                    if (uploader) {
                        me.uploaddialog.dialog.container.find('div.progress_large div.bar').css('width', '95%');

                        var response = uploader.HttpReault.match(/\{.*\}/);

                        var ret;
                        try {
                            eval('ret=' + response + ';');
                        } catch (e) {}

                        if (ret.fileid) {
                            if (me.editor !== null) {
                                var url = '/attachment/img?fid=' + ret.fileid;

                                html = '<img src="'+ url +'" _aid="'+ret.fileid+'" /><br />';
                                me.editor.pasteHTML(html);
                            }
                        } else {
                            TOP.showMessage(TOP.TEXT.CAPTURER_UPLOAD_FILE_ERROR);
                        }
                    } else {
                        TOP.showMessage(TOP.TEXT.CAPTURER_START_UPLOAD_ERROR);
                    }

                    setTimeout(function(){me.uploaddialog.hide();}, 500);
                },
                uploadUrl: me.uploadurl
            });

            if (!this.capturer.init()) {
                this.capturer.destroy();

                this.installed = false;
                this.capturer = null;
                return null;
            }
        }
        return this.capturer;
    },

    startCapture: function() {
        this.getCapturer().startCapture();
    }
};

/**
 *  上传类
 */
function initAttachment(params, list, container) {
    var config = {
        buttonImageUrl: '',
        buttonWidth: '70',
        buttonHeight: '16',
        buttonTextLeftPadding: 20,
        buttonTextTopPadding: 1,
        buttonPlaceholderId: 'upload-btn',
        postParams: {}
    };

    for (var k in params) {
        if (typeof(TuduUpload.defaults[k]) != 'undefined') {
            config[k] = params[k];
        }
    }

    $('.upload_btn')
    .mouseover(function(){$('#upload-link').css('text-decoration', 'underline');})
    .mouseout(function(){$('#upload-link').css('text-decoration', 'none');});

    var upload = new TuduUpload(config);
    var handler = new Tudu.Attachment();
    handler.list = list;
    handler.container = container;
    handler.setUpload(upload);

    return upload;
}

/**
 * 图片上传
 *
 * @param {Object} editors
 * @param {Object} uploadParams
 */
function initPicInsert(editors, uploadParams){
    var currEditor = null;
    var auth = uploadParams ? uploadParams.auth : null, picupload = null, picup = null;
    var d = menuDialog('pic-dia', {
        body: $('#pic-modal'),
        oncreate: function(){
            $('#pic-modal .tab-header li a').click(function(){
                $('#pic-modal .tab-header li').removeClass('active');
                var o = $(this), name = o.attr('name');
                o.parent().addClass('active');
                $('#pic-modal div.tab-body').hide();
                $('#tb-' + name).show();
            });

            $('#pic-modal button[name="cancel"]').click(function(){
                d.hide();
            });

            $('#pic-modal button[name="confirm"]').click(function(){
                var url = $('#picurl').val();
                if (url) {
                    currEditor.pasteHTML('<img src="' + url + '" alt="" /><br />', true);
                }
                d.hide();
            });

            // 上传图片
            if (uploadParams) {
                var config = {
                    buttonWidth: '280',
                    buttonHeight: '24',
                    fileTypes: '*.jpg;*.jpeg;*.gif;*.png',
                    buttonPlaceholderId: 'pic-upload-btn',
                    postParams: {}
                };

                for (var k in uploadParams) {
                    if (typeof(TuduUpload.defaults[k]) != 'undefined') {
                        config[k] = uploadParams[k];
                    }
                }

                $('.imgupload').mouseover(function(){
                    $('button[name="browse"]').mouseover();
                }).mouseout(function(){
                    $('button[name="browse"]').mouseout();
                });
                var filename = $('#filename');

                picupload = new TuduUpload(config);
                picup = new Tudu.EditorUpload({
                    upload: picupload,
                    onFileQueue: function(file){
                        var files = [];
                        for (var k in this._files) {
                            files.push(this._files[k].name);
                        }

                        filename.val(files.join(','));
                    }
                });
                $('button[name="upload"]').click(function(){
                    picup.startUpload();
                });
            }
        }
    });
    d.hide();

    for (var ele in editors) {
        $(ele).mousedown(function(e){
            TOP.stopEventBuddle(e);
        }).click(function(e){
            currEditor = editors['#' + this.id];
            TOP.stopEventBuddle(e);
            var offset = $(this).offset(), left = offset.left - 22, top = offset.top + 16;

            if (null != picup) {
                picup.cleanFileQueue();
                $('#filename').val('');

                picup.onComplete = function(){
                    for (var i = 0, c = this._success.length; i < c; i++) {
                        var aid = this._success[i].aid, url = '/attachment/img?fid=' + aid, html = '<img src="' + url + '" _aid="' + aid + '" /><br />';
                        currEditor.pasteHTML(html);
                    }
                    d.hide();
                };
            }

            d.css({
                left: left,
                top: top
            }).show();

            $('#pic-modal .tab-header li:eq(0) a').click();
            $('#picfile').val('');
            $('#picurl').val('http://');

            TOP.stopEventBuddle(e);
        });
    }
}

//显示下拉功能框
function menuDialog(id, params) {
    if (!id || !params.body) {
        return ;
    }
    var obj = $('#' + id);

    if (!obj.size()) {
        obj = $('<div>')
              .addClass('modal-dialog')
              .attr('id', id);

        obj
        .appendTo(params.appendTo ? params.appendTo : document.body)
        .bind('click', function(e){
            TOP.stopEventBuddle(e);
        });

        if (typeof(params.body) == 'string') {
            obj.html(params.body);
        } else {
            obj.append(params.body.show());
        }

        if (typeof(params.oncreate) == 'function') {
            params.oncreate.call(obj);
        }

        $(window).bind('click', function(){
            obj.hide();
        });
        $('#replyform').bind('click', function(){
            obj.hide();
        });
    }

    obj.css({
        position: 'absolute',
        zIndex: 100,
        top: params.top ? params.top + 'px' : 0,
        left: params.left ? params.left + 'px' : 0
    }).show();

    return obj;
}
// 处理附件img标签
// 处理 base64格式 img标签
function checkContentImage(html, editor, callback) {
    // 检查是否存在base64格式src图片标签
    var b64reg = /<img[^>]+src="data:image\/\w+;base64,([^">]+)"([^>]+)\/>/ig;
    var arr  = html.match(b64reg);

    if (!arr || !arr.length) {
        return true;
    }

    var win = TOP.Frame.Dialog.show({
        body:  '<div>' + TOP.TEXT.IMG_UPLOADING + '</div><div class="progress_upload"></div>',
        title: TOP.TEXT.FILE_UPLOADING,
        close: false
    }).getWin();

    win.show();

    // 显示上传窗口
    var complete = 0,
        count    = arr.length,
        imgs     = [];
    for (var i = 0; i < count; i++) {
        var img  = arr[i],
            data = img.match(/data:image\/\w+;base64,[^"]+/ig);

        imgs[imgs.length] = img;

        if (!data.length) {
            ++complete;
            continue ;
        }

        data = data[0].split(',')[1];

        $.ajax({
            url: '/attachment/proxy',
            type: 'POST',
            dataType: 'json',
            data: {data: data, label: i},
            success: function(ret) {
                
                if (ret.success && ret.data) {
                    var img = imgs[ret.data.label];
                    var tag = img
                        .replace(/src="[^"]+"/, 'src="/attachment/img?fid=' + ret.data.fileid + '"')
                        .replace('\>', '_aid="'+ret.data.fileid+'" \>');

                    html = html.replace(img, tag);
                    editor.setSource(html);
                } else {
                    TOP.showMessage(ret.message);
                }

                if (++complete >= count && typeof callback == 'function') {
                    win.close();
                    callback();
                }
            },
            error: function(res) {
                if (++complete >= count && typeof callback == 'function') {
                    win.close();
                }

                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
                return false;
            }
        });

        if (complete >= count) {
            callback();
        }
    }
}

//点击 发送 或 回复 附件上传中处理 延迟发送
function whileUploading(upload, msg, completeCallback, form) {
    if (null != upload) {
        if (upload.isUploading()) {
            if (form) {
                form.find(':input').attr('disabled', true);
            }

            upload.setParam('upload_complete_handler', function(){
                var stats = this.getStats();
                if (stats.files_queued == 0 && !stats.in_progress) {
                    if (form) {
                        form.find(':input').attr('disabled', false);
                    }
                    if (typeof(completeCallback) == 'function') {
                        completeCallback();
                    }
                }
            });

            var message = [
                '<div class="msg-progress" id="msg-progress"><div></div></div><span id="msg-txt-progress">0%</span>',
                msg,
                ' [<a href="javascript:void(0);">' + TOP.TEXT.CANCEL + '</a>]'
            ].join('');

            TOP.showMessage(message, 0, 'success');
            var progress = upload.totalProgress();
            TOP.getJQ()('#msg-progress div').width(progress + '%');
            TOP.getJQ()('#msg-txt-progress').text(progress + '%');

            TOP.getJQ()('#result-top a').click(function(){
                TOP.showMessage();
                upload
                .setParam('upload_complete_handler', function(){})
                .setParam('upload_progress_handler', function(file, uploaded, total){
                    if (TOP.getJQ()('#msg-progress').size()) {
                        TOP.getJQ()('#msg-progress div').style('width', upload.totalProgress + '%');
                    }
                    upload.onProgress.call(this, file, uploaded, total);
                });
                if (form) {
                    form.find(':input').attr('disabled', false);
                }
            });

            return false;
        }
    }

    return true;
}