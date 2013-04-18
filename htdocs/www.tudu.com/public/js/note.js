
if (typeof getTop != 'function') {
    function getTop() {
        return parent;
    }
}

var TOP = getTop();

/**
 * 便签相关操作封装
 */
var Note = {

    noteList: null,

    /**
     *
     */
    autoId: 0,

    /**
     * 颜色选择
     */
    colorPicker: null,

    /**
     * 用于记录便签内容
     */
    contents: {},

    /**
     * 当前颜色选择菜单所在的便签
     */
    colorPickerState: {
        init: false, 
        note: null
    },

    /**
     * 颜色
     */
    randColors: ['ffff99', 'ff9966', '66cccc', '99cc66', 'cc99cc', 'cccccc'],

    /**
     * 初始化页面
     */
    init: function() {
        var _o = this;

        $('button[name="create"]').bind('click', function(e) {
            _o.createNode();
            Note.hideColorPicker();
            TOP.stopEventBuddle(e);
        });

        $('button[name="delete"]').bind('click', function(e){
            var noteId = _o.getSelected();

            if (!noteId.length) {
                return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
            }

            Note.hideColorPicker();
            _o.deleteNote(noteId.join(','));
            $('#checkall').attr('checked', false);
            TOP.stopEventBuddle(e);
        });

        _o.noteList = $('#notelist');

        $('input[name="checkall"]').click(function(){
            TOP.checkBoxAll('nid[]', this.checked, document.body);
            var selectCount = $(':checkbox[name="nid[]"]:checked').size();
            $('button[name="delete"]').attr('disabled', selectCount <= 0);
        });

        $(':checkbox[name="nid[]"]').click(function(e){
            var selectCount = $(':checkbox[name="nid[]"]:checked').size();
            $('button[name="delete"]').attr('disabled', selectCount <= 0);
        });

        this.noteList.find('.note_item')
        .each(function(){
            var item = $(this);
            item.find('.note_despt').click(function(){Note.expandNote(item);});

            item.click(function(e){
                var expanded = Note.getExpanded().not(item);
                if (expanded.length) {
                    expanded.each(function(){
                        var o  = $(this);
                        
                        Note.saveNote(o);
                    });
                }

                Note.hideColorPicker();
                TOP.stopEventBuddle(e);
            })
            .bind('mouseover', function(){
                var o = $(this);
                if (!o.hasClass('note_expand')) {
                    $(this).addClass('note_hover');
                }
            })
            .bind('mouseout', function(){
                $(this).removeClass('note_hover');
            });

            item.find('textarea[name="content"]').bind('keyup', function(){
                var content = item.find('textarea[name="content"]').val(),
                    l = content.split("\n").length;
                if (l >= 2) {
                    $(this).css('height', content.split("\n").length * 22 + 'px');
                } else {
                    $(this).css('height', 2 * 22 + 'px');
                }
            });

            if ($.browser.msie && $.browser.version < '7.0') {
                item.find('textarea[name="content"]').bind('blur', function(){
                    _collspanNode();
                });
            }

            item.find('span.note_color_box').bind('click', function(e){
                Note.showColorPicker(item, e);
                TOP.stopEventBuddle(e);
            });
        });

        $(window.document.body).click(function() {
            _collspanNode();
        });

        window.onbeforeunload = function() {
            _collspanNode();
        };

        function _collspanNode(){
            var expanded = Note.getExpanded();
            if (expanded.length) {
                expanded.each(function(){
                    var o  = $(this);

                    Note.saveNote(o);
                });
            }
        }
    },

    /**
     * 随机获取数组
     */
    getRandColors: function(len) {
        this.randColors.sort(function() {
            return Math.random() - 0.5;
        });

        return this.randColors.slice(0, len);
    },

    /**
     * 创建便签
     */
    createNode: function() {

        var html = ['<div class="note_item note_expand"><div class="note_inner"><input type="hidden" name="color" value="FFFF99" />',
                    '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr>',
                    '<td width="30" align="center" style="padding-left:0" valign="top"><input type="checkbox" name="nid[]" value="" /></td>',
                    '<td align="center" style="width:36px;padding-left:0" valign="top"><span class="note_color_box" style="background:#ffff99"></span></td>',
                    '<td><div class="note_despt" style="padding-left:10px;"></div><div class="note_edit"><textarea name="content"></textarea></div></td>',
                    '<td valign="top">-</td>',
                    '<td width="120" valign="top" class="note_time"></td>',
                    '<td width="20" valign="top"><a href="javascript:void(0)" class="icon icon_grab" name="delete"></a></td>',
                    '</tr></table></div>',
                    '</div>'].join('');

        var color    = this.getRandColors(1);
        var expanded = this.getExpanded();
        if (expanded.length) {
            expanded.each(function(){
                var o  = $(this);

                Note.saveNote(o);
            });
        }

        var obj = $(html);

        this.noteList.prepend(obj);

        if (!$.browser.msie) {
            var bg = parseInt('0x' + color);
            var r = (bg & 0xFF0000) / Math.pow(16, 4),   // ff0000
                g = (bg & 0x00FF00) / Math.pow(16, 2),   // 00ff00
                b = bg & 0xFF;

            obj.css('background', 'rgba(' + r + ',' + g + ',' + b + ',0.3)');
        } else {
            obj.css('background', '#' + color);
        }

        obj.find(':hidden[name="color"]').val(color);
        obj.find('.note_color_box').css('background', '#' + color);

        obj
        .bind('click', function(e){
            var expanded = Note.getExpanded().not(obj);
            if (expanded.length) {
                expanded.each(function(){
                    var o  = $(this);

                    Note.saveNote(o);
                });
            }

            Note.hideColorPicker();
            TOP.stopEventBuddle(e);
        })
        .bind('mouseover', function(){
            var o = $(this);
            if (!o.hasClass('note_expand')) {
                $(this).addClass('note_hover');
            }
        })
        .bind('mouseout', function(){
            $(this).removeClass('note_hover');
        })
        .find('.note_edit textarea[name="content"]')
        .bind('keyup', function(){
            var content = obj.find('.note_edit textarea[name="content"]').val(),
                l = content.split("\n").length;
            if (l >= 2) {
                $(this).css('height', content.split("\n").length * 22 + 'px');
            } else {
                $(this).css('height', 2 * 22 + 'px');
            }
        })
        .focus();

        obj.find('.note_despt').click(function(){Note.expandNote(obj);});

        obj.find(':checkbox[name="nid[]"]').click(function(){
            var selectCount = $(':checkbox[name="nid[]"]:checked').size();
            $('button[name="delete"]').attr('disabled', selectCount <= 0);
        });

        obj.find('a[name="delete"]').click(function(){
            $('#checkall').attr('checked', false);
            Note.removeNote(obj);
        });
        obj.find('td.note_time').text(Note.getTime());
        obj.find('.note_color_box').bind('click', function(e){
            Note.showColorPicker(obj, e);
            TOP.stopEventBuddle(e);
        });
        obj.find(':checkbox[name="nid[]"]').click(function(e){
            var selectCount = $(':checkbox[name="nid[]"]:checked').size();
            $('button[name="delete"]').attr('disabled', selectCount <= 0 ? 'disabled' : false);
        });

        if (this.noteList.find('#note-null:visible').size()) {
            this.noteList.find('#note-null:visible').hide();
        }
    },

    /**
     * 展开（编辑）便签
     */
    expandNote: function(o) {
        if (typeof o == 'string') {
            o = this.noteList.find('#note-' + o);
        }
        o.addClass('note_expand');
        o.find('.note_despt').hide();
        o.find('.note_edit').show();

        var nid = o.attr('id').replace('note-', '');
        this.contents[nid] = o.find('textarea[name="content"]').val();

        var text = o.find('textarea[name="content"]'),
            content = text.val(),
            color = o.find(':hidden[name="color"]').val();

        if (!$.browser.msie) {
            color = parseInt('0x' + color);
            var r = (color & 0xFF0000) / Math.pow(16, 4),   // ff0000
                g = (color & 0x00FF00) / Math.pow(16, 2),   // 00ff00
                b = color & 0xFF;

            color = 'rgba(' + r + ',' + g + ',' + b + ',0.3)';
        } else {
            color = '#' + color;
        }
        if (content.split("\n").length >= 2) {
            text.css('height', content.split("\n").length * 22 + 'px').focus();
        } else {
            text.css('height', 2 * 22 + 'px').focus();
        }
        o.css({background: color});
    },

    /**
     * 收缩
     */
    collspanNote: function(o) {
        if (typeof o == 'string') {
            o = this.noteList.find('#note-' + o);
        }

        var nid = o.attr('id').replace('note-', '');
        delete this.contents[nid];

        o.find('.note_despt').show();
        o.find('.note_edit').hide();

        o.removeClass('note_expand');
        o.css('background', '#ffffff');
    },

    /**
     * 更新便签信息
     * 界面更新
     */
    updateNote: function(noteId, note, data) {
        var content = note.find('textarea[name="content"]').val();
        var color   = note.find(':hidden[name="color"]').val();
        content = content.length > 50 ? content.substr(0, 50) + '...' : content;

        if (!note.attr('id') && noteId) {
            note.attr('id', 'note-' + noteId);

            note.find(':checkbox[name="nid[]"]').val(noteId);

            note.find('a[name="delete"]')
            .unbind('click')
            .bind('click', function(){
                Note.deleteNote(noteId);
            });
        }

        if (data && data.updatetime) {
            note.find('td.note_time').text(data.updatetime);
        }

        var color = note.find(':hidden[name="color"]').val();
        note.find('.note_color_box').css('background', '#' + color);
        //note.find('.note_subject').css('background', '#' + color);
        note.find('.note_despt').text(content);
    },

    /**
     * 移除便签
     * 界面更新
     */
    removeNote: function(o) {
        if (typeof o == 'string') {
            o = this.noteList.find('#note-' + o);
        }

        o.remove();
    },

    /**
     * 保存便签
     * 数据提交操作
     */
    saveNote: function(o, force) {
        var noteId = o.attr('id');
        var data   = {
            color: o.find(':hidden[name="color"]').val(),
            content: o.find('textarea[name="content"]').val()
        };
        if (noteId) {
            data.nid = noteId.replace('note-', '');
        }

        if(this.contents[data.nid] != data.content || force) {
            
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '/note/' + (noteId ? 'update' : 'create'),
                data: data,
                success: function(ret) {
                    if(ret && ret.success && ret.data) {
                        Note.updateNote(ret.data.noteid, o, ret.data);
                    }
                },
                error: function(res) {
                    o.find('checkbox').attr('disabled', '');
                }
            });
        }

        Note.collspanNote(o);
        Note.updateNote(noteId, o);
    },

    /**
     * 删除便签
     * 数据提交操作
     */
    deleteNote: function(noteId) {
        if (!confirm(TOP.TEXT.CONFIRM_DELETE_NOTES)) {
            return false;
        }

        if (typeof noteId == 'string') {
            noteId = noteId.split(',');
        }

        for (var i = 0, c = noteId.length; i < c; i++) {
            Note.removeNote(noteId[i]);
        }
        $(':checkbox[name="nid[]"][value=""]:checked').each(function(){
            $(this).parents('div.note_item').remove();
        });

        $('#checkall').attr('checked', false);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {nid: noteId.join(',')},
            url: '/note/delete',
            success: function(ret) {
               if (!ret.success) {
                   TOP.showMessage(ret.message, 5000, null);
               }

               var selectCount = $(':checkbox[name="nid[]"]:checked').size();
                $('button[name="delete"]').attr('disabled', selectCount <= 0);
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
            }
        });
    },

    /**
     * 获取选择的便签
     */
    getSelected: function() {
        var ret = [];
        this.noteList.find(':checkbox[name="nid[]"]:checked').each(function(){
            ret.push(this.value);
        });

        return ret;
    },

    /**
     *
     */
    getExpanded: function() {
        return this.noteList.find('.note_expand');
    },

    /**
     *
     */
    getTime: function() {
        var d = new Date();
        var s = [
            d.getFullYear(), '.',
            parseInt(d.getMonth()) + 1, '.',
            d.getDate(), ' ',
            d.getHours(), ':',
            d.getMinutes()
        ];

        for (var i = 0, c = s.length; i < c; i++) {
            if (s[i] == '.' || s[i] == ' ' || s[i] == ':') {
                continue ;
            }

            if (s[i].length < 2) {
                s[i] = '0' + s[i];
            }
        }

        return s.join('');
    },

    /**
     * 初始化颜色选择器
     */
    initColorPicker: function() {
        var o = this, 
            state = o.colorPickerState;

        this.colorPicker = new $.Dropdown({
            id: 'color-picker',
            menuBody: $('#color-panel').html(),
            maxWidth: '115px',
            onShow: function() {
                if (!state.init) {
                    $('#color-picker div.option-menu div.menu-item')
                    .bind('mouseover', function(){$(this).addClass('menu-over');})
                    .bind('mouseout', function(){$(this).removeClass('menu-over');})
                    .bind('click', function(e){
                        if (null == state.note) {
                            return ;
                        }

                        var color = $(this).find(':hidden[name="color"]').val(),
                            obj   = state.note;

                        obj.find(':hidden[name="color"]').val(color.replace(/^#+/, ''));
                        if (obj.attr('id')) {
                            Note.saveNote(obj, true);
                        } else {
                            obj.find('.note_color_box').css('background', color);
                            //obj.find('.note_subject').css('background', color);
                        }

                        state.note = null;
                        TOP.stopEventBuddle(e);
                        Note.hideColorPicker();
                    });

                    state.init = true;
                }
            }
        });
    },

    /**
     * 显示颜色选择器
     * @param {Object} obj
     * @param {Object} evt
     */
    showColorPicker: function(obj, evt) {
        if (null === this.colorPicker) {
            this.initColorPicker();
        }

        this.colorPickerState.note = obj;

        this.colorPicker.show(evt);
    },

    /**
     * 隐藏颜色选择器
     */
    hideColorPicker: function() {
        if (this.colorPicker) {
            this.colorPicker.hide();
        }
    }
};