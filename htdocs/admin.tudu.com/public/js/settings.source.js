var Settings = {};

/**
 * 登陆页面皮肤设置
 */
Settings.LoginSkin = {
    /**
     * 上传窗口
     */
    uploadWin: null,

    /**
     * 选色器窗口
     */
    colorPickerWin: null,

    /**
     * 选色器模板
     */
    colorPickerTpl: [
        '<table id="color-picker" class="color_picker" cellspacing="0" cellpadding="0" borde="0">',
            '<tr>',
                '<td><div style="background:#7e0000; border-color:#7e0000" _color="#7e0000"></div></td>',
                '<td><div style="background:#802d00; border-color:#802d00" _color="#802d00"></div></td>',
                '<td><div style="background:#844d00; border-color:#844d00" _color="#844d00"></div></td>',
                '<td><div style="background:#8a7e00; border-color:#8a7e00" _color="#8a7e00"></div></td>',
                '<td><div style="background:#486a00; border-color:#486a00" _color="#486a00"></div></td>',
                '<td><div style="background:#005e13; border-color:#005e13" _color="#005e13"></div></td>',
                '<td><div style="background:#00561e; border-color:#00561e" _color="#00561e"></div></td>',
                '<td><div style="background:#005852; border-color:#005852" _color="#005852"></div></td>',
                '<td><div style="background:#005882; border-color:#005882" _color="#005882"></div></td>',
                '<td><div style="background:#003266; border-color:#003266" _color="#003266"></div></td>',
                '<td><div style="background:#001a58; border-color:#001a58" _color="#001a58"></div></td>',
                '<td><div style="background:#01004b; border-color:#01004b" _color="#01004b"></div></td>',
                '<td><div style="background:#30004a; border-color:#30004a" _color="#30004a"></div></td>',
                '<td><div style="background:#500043; border-color:#500043" _color="#500043"></div></td>',
                '<td><div style="background:#660144; border-color:#660144" _color="#660144"></div></td>',
                '<td><div style="background:#700138; border-color:#700138" _color="#700138"></div></td>',
                '<td><div style="background:#535353; border-color:#535353" _color="#535353"></div></td>',
            '</tr>',
            '<tr>',
                '<td><div style="background:#9a0000; border-color:#9a0000" _color="#9a0000"></div></td>',
                '<td><div style="background:#a83f00; border-color:#a83f00" _color="#a83f00"></div></td>',
                '<td><div style="background:#ac6900; border-color:#ac6900" _color="#ac6900"></div></td>',
                '<td><div style="background:#b6a700; border-color:#b6a700" _color="#b6a700"></div></td>',
                '<td><div style="background:#638c0b; border-color:#638c0b" _color="#638c0b"></div></td>',
                '<td><div style="background:#0c7d24; border-color:#0c7d24" _color="#0c7d24"></div></td>',
                '<td><div style="background:#007230; border-color:#007230" _color="#007230"></div></td>',
                '<td><div style="background:#00746d; border-color:#00746d" _color="#00746d"></div></td>',
                '<td><div style="background:#0074aa; border-color:#0074aa" _color="#0074aa"></div></td>',
                '<td><div style="background:#004886; border-color:#004886" _color="#004886"></div></td>',
                '<td><div style="background:#002e74; border-color:#002e74" _color="#002e74"></div></td>',
                '<td><div style="background:#110b64; border-color:#110b64" _color="#110b64"></div></td>',
                '<td><div style="background:#430063; border-color:#430063" _color="#430063"></div></td>',
                '<td><div style="background:#6a005c; border-color:#6a005c" _color="#6a005c"></div></td>',
                '<td><div style="background:#a40054; border-color:#a40054" _color="#a40054"></div></td>',
                '<td><div style="background:#a40032; border-color:#a40032" _color="#a40032"></div></td>',
                '<td><div style="background:#7c7c7c; border-color:#7c7c7c" _color="#7c7c7c"></div></td>',
            '</tr>',
            '<tr>',
                '<td><div style="background:#e6000a; border-color:#e6000a" _color="#e6000a"></div></td>',
                '<td><div style="background:#eb5e00; border-color:#eb5e00" _color="#eb5e00"></div></td>',
                '<td><div style="background:#f49500; border-color:#f49500" _color="#f49500"></div></td>',
                '<td><div style="background:#fff001; border-color:#fff001" _color="#fff001"></div></td>',
                '<td><div style="background:#8ec21f; border-color:#8ec21f" _color="#8ec21f"></div></td>',
                '<td><div style="background:#23ac39; border-color:#23ac39" _color="#23ac39"></div></td>',
                '<td><div style="background:#009a44; border-color:#009a44" _color="#009a44"></div></td>',
                '<td><div style="background:#009e94; border-color:#009e94" _color="#009e94"></div></td>',
                '<td><div style="background:#009fea; border-color:#009fea" _color="#009fea"></div></td>',
                '<td><div style="background:#0067b8; border-color:#0067b8" _color="#0067b8"></div></td>',
                '<td><div style="background:#00469e; border-color:#00469e" _color="#00469e"></div></td>',
                '<td><div style="background:#1d1f88; border-color:#1d1f88" _color="#1d1f88"></div></td>',
                '<td><div style="background:#5e1986; border-color:#5e1986" _color="#5e1986"></div></td>',
                '<td><div style="background:#94077e; border-color:#94077e" _color="#94077e"></div></td>',
                '<td><div style="background:#e40076; border-color:#e40076" _color="#e40076"></div></td>',
                '<td><div style="background:#e6004b; border-color:#e6004b" _color="#e6004b"></div></td>',
                '<td><div style="background:#959595; border-color:#959595" _color="#959595"></div></td>',
            '</tr>',
            '<tr>',
                '<td><div style="background:#ec6941; border-color:#ec6941" _color="#ec6941"></div></td>',
                '<td><div style="background:#f19048; border-color:#f19048" _color="#f19048"></div></td>',
                '<td><div style="background:#f7b250; border-color:#f7b250" _color="#f7b250"></div></td>',
                '<td><div style="background:#fff15b; border-color:#fff15b" _color="#fff15b"></div></td>',
                '<td><div style="background:#b3d465; border-color:#b3d465" _color="#b3d465"></div></td>',
                '<td><div style="background:#7ec269; border-color:#7ec269" _color="#7ec269"></div></td>',
                '<td><div style="background:#31b06d; border-color:#31b06d" _color="#31b06d"></div></td>',
                '<td><div style="background:#11b4ae; border-color:#11b4ae" _color="#11b4ae"></div></td>',
                '<td><div style="background:#00b3ee; border-color:#00b3ee" _color="#00b3ee"></div></td>',
                '<td><div style="background:#4489c9; border-color:#4489c9" _color="#4489c9"></div></td>',
                '<td><div style="background:#556db4; border-color:#556db4" _color="#556db4"></div></td>',
                '<td><div style="background:#5e529f; border-color:#5e529f" _color="#5e529f"></div></td>',
                '<td><div style="background:#8957a0; border-color:#8957a0" _color="#8957a0"></div></td>',
                '<td><div style="background:#ad5c9f; border-color:#ad5c9f" _color="#ad5c9f"></div></td>',
                '<td><div style="background:#ea679f; border-color:#ea679f" _color="#ea679f"></div></td>',
                '<td><div style="background:#eb6975; border-color:#eb6975" _color="#eb6975"></div></td>',
                '<td><div style="background:#a0a0a0; border-color:#a0a0a0" _color="#a0a0a0"></div></td>',
            '</tr>',
            '<tr>',
                '<td><div style="background:#f29b75; border-color:#f29b75" _color="#f29b75"></div></td>',
                '<td><div style="background:#f5b180; border-color:#f5b180" _color="#f5b180"></div></td>',
                '<td><div style="background:#f9cb8a; border-color:#f9cb8a" _color="#f9cb8a"></div></td>',
                '<td><div style="background:#fff699; border-color:#fff699" _color="#fff699"></div></td>',
                '<td><div style="background:#cbe198; border-color:#cbe198" _color="#cbe198"></div></td>',
                '<td><div style="background:#acd598; border-color:#acd598" _color="#acd598"></div></td>',
                '<td><div style="background:#88c998; border-color:#88c998" _color="#88c998"></div></td>',
                '<td><div style="background:#85ccc8; border-color:#85ccc8" _color="#85ccc8"></div></td>',
                '<td><div style="background:#7dcbf4; border-color:#7dcbf4" _color="#7dcbf4"></div></td>',
                '<td><div style="background:#89abda; border-color:#89abda" _color="#89abda"></div></td>',
                '<td><div style="background:#8d97ca; border-color:#8d97ca" _color="#8d97ca"></div></td>',
                '<td><div style="background:#8e82bb; border-color:#8e82bb" _color="#8e82bb"></div></td>',
                '<td><div style="background:#aa89bc; border-color:#aa89bc" _color="#aa89bc"></div></td>',
                '<td><div style="background:#c490bd; border-color:#c490bd" _color="#c490bd"></div></td>',
                '<td><div style="background:#f19ebf; border-color:#f19ebf" _color="#f19ebf"></div></td>',
                '<td><div style="background:#f19c9c; border-color:#f19c9c" _color="#f19c9c"></div></td>',
                '<td><div style="background:#c9c9c9; border-color:#c9c9c9" _color="#c9c9c9"></div></td>',
            '</tr>',
        '</table>'
    ].join(''),

    /**
     * 初始化页面
     */
    init: function() {
        var _this = this;

        new FixToolbar({
            target: 'div.tool-btm'
        });

        // 选中模板
        $('.skinsetting div:not(.custom)').click(function(){
            _this.selectSkin($(this));
        });

        // 自定义颜色
        $('#custom-color').click(function(){
            _this.initColorPicker(); 
        });

        // 添加图片
        $('#custom-pic').click(function(){
            _this.initUpload();
        });

        // 预览
        $('input[name="preview"]').click(function(){
            var selected = _this.getSelected();
            window.open(BASE_PATH + '/settings/page/preview?type=' + selected.type + '&value=' + encodeURIComponent(selected.value));
        });

        // 保存
        $('input[name="save"]').click(function() {
            _this.save('#theform');
        });
    },

    /**
     * 初始化颜色取色器
     */
    initColorPicker: function() {
        var me = this;
        if (this.colorPickerWin === null) {
            this.colorPickerWin = Admin.window({
                id: 'win-colorPicker',
                width: 376,
                title:'自定义登陆背景颜色',
                formid: 'colorform',
                body: me.colorPickerTpl,
                footer: '<input name="submit" type="submit" class="btn" value="确定"><input name="close" type="button" class="btn" value="取消" />',
                action: BASE_PATH + '/settings/page/save-color',
                draggable: true,
                init: function(){
                    var form = this.find('form');
                    form.submit(function(){return false;});
                    form.submit(function(){
                        me.saveColor(form);
                    });

                    this.find('input[name="close"]').click(function() {
                        me.colorPickerWin.close();
                    });

                    var td = this.find('.color_picker td');
                    var table = this.find('.color_picker');
                    td.bind('mousedown', function(){
                        var se = table.find('div.selected');
                        var color = se.attr('_color');
                        se.parent().removeClass();
                        se.removeClass().css({
                            'border-color': color
                        });

                        $(this).find('div').addClass('selected').css({
                            'border-color': '#ffffff'
                        });
                        $(this).find('div').parent().addClass('selected');
                    });

                    td.bind('mouseover', function(){
                        $(this).find('div:not(.selected)').css({
                            'border-color': '#ffffff'
                        });
                    }).bind('mouseout', function(){
                        var color = $(this).find('div').attr('_color');
                        $(this).find('div:not(.selected)').css({
                            'border-color': color
                        });
                    });
                },
                onShow: function(){
                },
                onClose: function(){
                    me.colorPickerWin.destroy();
                    me.colorPickerWin = null;
                }
            });
        }

        this.colorPickerWin.show();
    },

    /**
     * 保存提交颜色
     */
    saveColor: function(form) {
        var me = this,
            color = this.getSelectedColor(form);

        if (!color) {
            Message.show('请先选择颜色');
            return false;
        }

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: form.attr('action'),
            data: {color: color},
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);

                if(ret.success && ret.data) {
                    if (me.colorPickerWin !== null) {
                        me.colorPickerWin.close();
                    }

                    if (ret.data.color) {
                        var color = ret.data.color;
                        me.updateColorBox(color);
                    } else {
                        location.assign(location.href);
                    }
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                return false;
            }
        });
    },

    /**
     * 更新自定义颜色
     *
     * @param {Object} color
     */
    updateColorBox: function(color) {
        var obj = $('#skin-color');

        if (obj.size() <= 0) {
            location.assign(location.href);
        }

        obj.find('input[name="value"]').val('CUSTOM:' + color);
        obj.find('img').css({'background': color});

        if ($('#skin-color:hidden').size()) {
            obj.show();
        }

        this.selectSkin(obj);
    },

    /**
     * 获取选中的颜色
     */
    getSelectedColor: function(form) {
        var selected = form.find('td.selected');

        if (selected.size() <= 0) {
            return null;
        }

        var color = selected.find('div').attr('_color');
        return color;
    },

    /**
     * 初始化图片上传
     */
    initUpload: function() {
        var me = this;
        if (this.uploadWin === null) {
            this.uploadWin = Admin.window({
                id: 'login-uploadwin',
                width: 550,
                title:'上传登陆图片',
                formid: 'login-uploadform',
                body: ['<div class="realwin"><p class="gray">(请点击“浏览”，在您电脑中选择您要上传的照片。)</p>',
                       '<div>选择照片：<input id="imagefile" type="file" name="file" class="btn" size="40" />&nbsp;<input id="uploadfile" type="button" name="upload" class="btn" value="立即上传" /><input id="fileurl" type="hidden" name="fileurl" value="" /><input id="filetype" type="hidden" name="filetype" value="" /></div>',
                       '<p class="gray">支持jpg、png、gif，文件大小不超过2M。</p>',
                       '<div class="line-solid"></div>',
                       '<div id="preview-img" style="display:none;"><p>缩略图</p>',
                       '<div class="preview-box"><img id="login-img" src=""></div></div>',
                       '</div>'
                       ].join(''),
                footer: '<input name="submit" type="submit" disabled="disabled" class="btn" value="确定"><input name="close" type="button" class="btn" value="取消" />',
                action: BASE_PATH + '/settings/page/save-image',
                draggable: true,
                init: function(){
                    var form = this.find('form');
                    form.submit(function(){return false;});
                    form.submit(function(){
                        me.saveImage(form);
                    });

                    this.find('input[name="close"]').click(function() {
                        me.uploadWin.close();
                    });

                    this.find('input[name="upload"]').bind('click', function() {
                        me.uploadFile('#login-uploadform');
                    });

                    Frame.queryParent('#login-img').bind('load', function(){
                        var w = this.width;
                        if (w > 450) {
                            $(this).css({
                                width: '450px',
                                height: 'auto'
                            });
                        }
                        var h = this.height;
                        if (h > 300) {
                            $(this).css({
                                width: 'auto',
                                height: '300px'
                            });
                        }
                        if (me.uploadWin !== null) {
                            me.uploadWin.center();
                        }
                    });
                },
                onShow: function(){
                },
                onClose: function(){
                    me.uploadWin.destroy();
                    me.uploadWin = null;
                }
            });
        }

        this.uploadWin.show();
    },

    /**
     * 上传文件
     *
     * @param {Object} form
     */
    uploadFile: function(form) {
        Frame.queryParent('#fileurl').val('');
        Frame.queryParent('input[name="submit"]').attr('disabled', true);

        var file = Frame.queryParent('#imagefile');
        var filepath = file.val();

        if (filepath.length <= 0) {
            Message.show('请选择您要上传的图片');
            return false;
        }

        var extStart = filepath.lastIndexOf('.'); 
        var ext      = filepath.substring(extStart,filepath.length).toUpperCase(); 
        if (ext.length > 0 && ext != ".PNG" && ext != ".GIF" && ext != ".JPG" && ext != ".JPEG") {
            Message.show('仅限上传jpg、png、gif格式的图片');
            Frame.queryParent('#preview-img').hide();
            file.val('');
            return false;
        }

        this.upload(form);
    },

    /**
     * 上传
     */
    upload: function(form) {
        var me = this,form = $(form);

        Frame.getJQ().ajaxUpload({
            url: BASE_PATH + '/settings/page/upload',
            file: Frame.queryParent('#imagefile'),
            data: {},
            dataType: 'text',
            success: function(ret) {
                var ret = ret.match(/\{[^\}]+\}\}/).toString();
                ret = (new Function('return '+ ret +';'))();

                Message.show(ret.message, 5000, ret.success);

                if (!ret.success) {
                    Frame.queryParent('#imagefile').val('');
                    Frame.queryParent('#fileurl').val('');
                    Frame.queryParent('#filetype').val('');
                    Frame.queryParent('#preview-img').hide();
                    Frame.queryParent('input[name="submit"]').attr('disabled', true);
                }

                if (ret.success && ret.data.fileurl) {
                    Frame.queryParent('#preview-img').show();
                    Frame.queryParent('#login-img').attr('src', BASE_PATH + '/settings/page/file?hash=' + ret.data.fileurl);
                    Frame.queryParent('#fileurl').val(ret.data.fileurl);
                    Frame.queryParent('#filetype').val(ret.data.filetype);
                    Frame.queryParent('input[name="submit"]').attr('disabled', false);
                }

                if (me.uploadWin !== null) {
                    me.uploadWin.center();
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                Frame.queryParent('#imagefile').val('');
                Frame.queryParent('#fileurl').val('');
                Frame.queryParent('#filetype').val('');
                Frame.queryParent('#preview-img').hide();
                Frame.queryParent('input[name="submit"]').attr('disabled', true);
                return false;
            }
        });
    },

    /**
     * 保存图片数据
     *
     * @param {Object} form
     */
    saveImage: function(form) {
        var me       = this,
            form     = $(form),
            fileurl  = form.find('input[name="fileurl"]').val(),
            filetype = form.find('input[name="filetype"]').val();

        if (fileurl.length <= 0) {
            Message.show('获取图片路径失败，请重试');
            return false;
        }

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: form.attr('action'),
            data: {fileurl: fileurl, filetype: filetype},
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);

                if(ret.success && ret.data) {
                    if (me.uploadWin !== null) {
                        me.uploadWin.close();
                    }

                    if (ret.data.fileurl) {
                        var fileUrl = ret.data.fileurl;
                        me.updatePicBox(fileUrl);
                    } else {
                        location.assign(location.href);
                    }
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                return false;
            }
        });
    },

    /**
     * 更新自定义图片
     *
     * @param {Object} fileUrl
     */
    updatePicBox: function(fileUrl) {
        var obj = $('#skin-pic');

        if (obj.size() <= 0) {
            location.assign(location.href);
        }

        obj.find('input[name="value"]').val('CUSTOM:' + fileUrl);
        obj.find('img').attr('src', BASE_PATH + '/settings/page/file?hash=' + fileUrl);

        if ($('#skin-pic:hidden').size()) {
            obj.show();
        }

        this.selectSkin(obj);
    },

    /**
     * 选定皮肤
     * @param {Object} id
     */
    selectSkin: function(obj) {
        $('.skinsetting div').removeClass('skin_select');
        obj.addClass('skin_select');
    },

    /**
     * 获取选中登陆页的信息
     */
    getSelected: function() {
        var form = $('#theform'),
            selected = form.find('.skin_select');

        if (selected.size() <= 0) {
            Message.show('请选择你要预览的登陆页面模板');
            return false;
        }

        var type = selected.find('input[name="type"]').val(),
            value = selected.find('input[name="value"]').val();

        return {type: type, value: value};
    },

    /**
     * 提交保存
     */
    save: function(form){
        var form = $(form),
            selected = form.find('.skin_select'),
            type = selected.find('input[name="type"]').val(),
            value = selected.find('input[name="value"]').val();

        if (selected.size() <= 0) {
            Message.show('请选择登陆页面模板');
            return false;
        }

        form.find('input, button').attr('disabled', true);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {type: type, value: value},
            url: form.attr('action'),
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);
                form.find('input, button').attr('disabled', false);
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                form.find('input, button').attr('disabled', false);
                return false;
            }
        });
    }
};

/**
 * 常规设置
 */
Settings.General = {
    /**
     * 初始化系统设置的常规页面
     */
    init: function() {
        $('input[name="save"]').click(function() {
            Settings.General.save('#theform');
        });

        new FixToolbar({
            target: 'div.tool-btm'
        });

        if (UI.SingleSelect) {
            var timeZoneSelect = new UI.SingleSelect({
                select: '#timezone',
                id: 'timezone-select',
                cls: 'select',
                maxHeight: 150,
                menuCls: 'option'
            });
            timeZoneSelect.appendTo($('#timezone').parent());

            var timeformatSelect = new UI.SingleSelect({
                select: '#dateformat',
                id: 'dateformat-select',
                cls: 'select',
                menuCls: 'option'
            });
            timeformatSelect.appendTo($('#dateformat').parent());
        }
    },

    /**
     * 保存系统信息
     */
    save: function(form) {        
        var form = $(form),
            data = form.serializeArray();
        form.find('input:not([name="status"]), button, select').attr('disabled', true);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);
                form.find('input:not([name="status"]), button, select').attr('disabled', false);
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                form.find('input:not([name="status"]), button, select').attr('disabled', false);
                return false;
            }
        });
    }
};

/**
 * 主题设置
 */
Settings.Theme = {
    /**
     * 初始化系统设置的登录页设置的页面
     */
    init: function() {
        $('input[name="save"]').click(function() {
            Settings.Theme.save('#theform');
        });

        $('.skinsetting div').click(function(){
            Settings.Theme.selectSkin($(this).find('input[name="skin"]').val());
        });

        new FixToolbar({
            target: 'div.tool-btm'
        });
    },

    /**
     * 选择皮肤
     */
    selectSkin: function(id) {
        $('.skin_select').removeClass('skin_select');

        $('#skin-' + id).addClass('skin_select');
        $('#skin-' + id + ' :radio[name="skin"]').attr('checked', true);
    },

    /**
     * 保存设置
     */
    save: function(form) {
        var form = $(form),
            data = form.serializeArray(),
            skin = $(':radio[name="skin"]:checked').val();

        form.find('input, button').attr('disabled', true);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                Message.show(ret.message, 5000, ret.success);
                form.find('input, button').attr('disabled', false);
                history.go(1);
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                form.find('input, button').attr('disabled', false);
                return false;
            }
        });
    }
};