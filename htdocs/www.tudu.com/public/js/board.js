if (typeof(getTop) != 'function') {
    function getTop() {
        return parent;
    }
}

var TOP = TOP || getTop();

/**
 * 版块对象方法
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: board.js 2790 2013-03-22 02:31:43Z chenyongfa $
 */
var Board = {

    _inputModerator: null,

    _inputGroups: null,

    _castWin: null,

    _editor: null,

    _editorCss: {},

    _tpl: {
        contactWin: '<div class="pop pop_linkman"><div class="pop_header"><strong>'+TOP.TEXT.SELECT_CONTACT+'</strong><a class="icon icon_close close"></a></div><div class="pop_body"></div><div class="pop_footer"><button type="button" name="confirm" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" class="btn close">'+TOP.TEXT.CANCEL+'</button></div></div>'
    },

    setEditorCss: function(css) {
        this._editorCss = css;
    },

    getEditorCss: function() {
        return this._editorCss;
    },

    /**
     * 初始化版块编辑页
     * 
     */
    initModify: function() {
        var me = this;
        // 取消按钮
        $('button[name="cancel"]').click(function(){
            location = '/board';
        });

        $('#tudu, #discuss, #notice, #group').click(function(){
            var type = $(this).attr('id');
            location = '/tudu/modify?type='+type+'{{if $tudu.boardid}}&bid={{$tudu.boardid}}{{/if}}{{if $newwin}}&newwin=1{{/if}}';
        });

        var h = $(window).height(),
        ch = $(document.body).height();

        Board.Class.tidyClassSort();

        var editorHeight = Math.max($('#memo').height() + (h - ch - 15), 200)
        $('#memo').css('height', editorHeight + 'px');

        this._editor = new TOP.Editor(document.getElementById('memo'), {
            resizeType : 1,
            width: '100%',
            minHeight: 200,
            themeType : 'tudu',
            css: Board.getEditorCss(),
            scope: window
        }, jQuery);

        $('#template').click(function(){
            var checked = this.checked;
            if ($('#tpl-list tr').size() && !checked) {
                TOP.showMessage(TOP.TEXT.DELETE_TEMPLATES_TO_DISABLE);
                return false;
            }
            $('#template-box').css('display', checked ? '' : 'none');
            Board.Template.init();
        });

        if ($('#template').attr('checked') == true) {
            Board.Template.init();
            Board.Template.tidyTemplateSort();
        }

        $('#classes').click(function(){
            if (this.checked) {
                $('#class-box').show();
            } else {
                $('#class-box').hide();
            }
        });

        var moderators = $('#moderators').val().split("\n");
        var groups = $('#groups').val().split("\n");

        this._inputModerator = new TOP.ContactInput({
            id: 'moderator-input', target: $('#i-moderators'), valuePlace: $('#moderators'), group: false, contact: false,
            jq: $,
            valueItems: ['_id', 'name'],
            onUpdate: function() {
                var mod = $('#moderators').val(), val = [];
                if (mod) {
                    var arr = mod.split("\n");
                    for(var i = 0, c = arr.length; i < c; i++) {
                        var item = arr[i].split(' ');
                        var userid, name;
                        if (item[0].indexOf('@')) {
                            item[0] = item[0].split('@');
                            userid = item[0][0];
                        } else {
                            userid = item[0];
                        }
                        name = item[1];

                        val.push(userid + ' ' + name);
                    }
                }

                $('#moderators').val(val.join("\n"));
            }
        });

        this._inputGroups = new TOP.ContactInput({
            id: 'groups-input', target: $('#i-groups'), valuePlace: $('#groups'), contact: false,
            jq: $,
            group:true,
            valueItems: ['_id']
        });
        this._inputGroups.clear();
        for(var i = 0, c = groups.length; i < c; i++) {
            if (!groups[i].length) {
                continue ;
            }

            this._inputGroups.addItem(groups[i], {name: groups[i], _id: (-1 != groups[i].indexOf('@') ? '' : groups[i]), title: groups[i]});
        }

        $('#select-moderators').click(function(){
            var title = $(this).text();
            var input = $('#i-' + this.id.replace('select-', ''));
            var valInput = $('#' + this.id.replace('select-', ''));

            var val = me._inputModerator.getItems();
            var selected = [], userid = null;

            val.each(function(){
                var o = $(this);
                selected.push({_id: o.attr('_id')});
            });

            var Win = TOP.Frame.TempWindow;
            Win.append(me._tpl.contactWin, {
                width:470,
                draggable: true,
                onShow: function() {
                    Win.center();
                },
                onClose: function() {
                    Win.destroy();
                }
            });

            var selector = new TOP.ContactSelector({
                appendTo: Win.find('div.pop_body'), 
                enableGroup: false, 
                selected: selected,
                mailInput: me._inputModerator,
                panels: ['common']
            });

            Win.find('button[name="confirm"]').bind('click', function(){
                var se = selector.getSelected();
                me._inputModerator.clear();

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
                    me._inputModerator.addItem(se[i].name, p);
                }
                Win.close();
            });

            Win.show();
        });

        $('#select-groups').click(function(){
            var selected = [];

            var val = me._inputGroups.getItems();
            val.each(function(){
                var o = $(this);
                selected.push({_id: o.attr('_id')});
            });

            var Win = TOP.Frame.TempWindow;
            Win.append(me._tpl.contactWin, {
                width:470,
                draggable: true,
                onShow: function() {
                    Win.center();
                },
                onClose: function() {
                    Win.destroy();
                }
            });

            var selector = new TOP.ContactSelector({
                appendTo: Win.find('div.pop_body'), 
                enableGroup: true, 
                selected: selected,
                mailInput: me._inputGroups,
                panels: ['common']
            });

            Win.find('button[name="confirm"]').bind('click', function(){
                var se = selector.getSelected();
                me._inputGroups.clear();

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
                    me._inputGroups.addItem(se[i].name, p);
                }
                Win.close();
            });

            Win.show();
        });

        $('#theform').submit(function(){return false;});
        $('button[name="submit"]').click(function(){
            var form = $('#theform');

            $('#memo').val(me._editor.getSource());

            if (!$('#parentid').val()) {
                $('#parentid').focus();
                return TOP.showMessage(TOP.TEXT.SELECT_BOARD_PARENT);
            }

            if (!$('#name').val()) {
                $('#name').focus();
                return TOP.showMessage(TOP.TEXT.BOARD_NAME_IS_NULL);
            }

            if (!$('#moderators').val()) {
                return TOP.showMessage(TOP.TEXT.BOARD_MODERATORS_IS_NULL);
            }

            if (Board.Template.currentId) {
                return TOP.showMessage(TOP.TEXT.TPL_IN_MODIFY);
            }

            if (!$('#classes').attr('checked')) {
                $('#class-list :input').attr('disabled', true);
                $('#classify').attr('disabled', true);
            } else {
                var classCount = 0
                $('#class-list input[name^="classname-"]').each(function(){
                    var text = this.value.replace(/\s+/, '');
                    if (text.length > 0) {
                        classCount ++;
                    }
                });
                if (classCount <= 0 && $('#classify:checked').size()) {
                    return TOP.showMessage(TOP.TEXT.NOT_ANY_CLASSES);
                }
            }

            var data = form.serializeArray();

            TOP.showMessage(TOP.TEXT.POSTING_DATA, 0, 'success');
            form.find(':input').attr('disabled', true);

            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: data,
                url: form.attr('action'),
                success: function(ret) {
                    TOP.showMessage(ret.message, 10000, ret.success ? 'success' : null);
                    form.find(':input').attr('disabled', false);
                    if (ret.success) {
                        if (ret.data) {
                            location = '/board/?bid=' + ret.data
                        } else {
                            location = '/board/';
                        }
                    }
                },
                error: function(res) {
                    form.find(':input').attr('disabled', false);
                    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
                }
            });
        });
    }
};

/**
 * 主题分类
 */
Board.Class = {
    /**
     * 添加分类
     */
    addClass: function() {
        var ct = $('#class-list'),
            count = ct.find('tr').size(),
            classname = $('#classname').val().replace(/^\s+|\s+$/, '');

        if (!classname.length) {
            TOP.showMessage(TOP.TEXT.INVALID_CLASS_NAME);
            $('#classname').focus();
            return false;
        }

        var o  = $('#class-tpl').clone(),
            id = 'new-' + (++_CLASS_AUTOID);
        o.attr('id', 'class-' + id).show();
        o.find('td[name="classname"]').text(classname);
        o.find('input[name="classname"]').attr('name', 'classname-' + id).val(classname);
        o.find('input[name="ordernum"]').attr('name', 'ordernum-' + id);
        o.find('input[name="newclass[]"]').val(id);
        o.find('input[name="classname"]').attr('name', 'classname-' + id);
        o.find('a[name="remove"]').click(function(){
            Board.Class.removeClass(id);
        });
        o.find('a[name="rename"]').click(function(){Board.Class.renameClass(id);});
        o.find('a[name="up"]').click(function(){Board.Class.sortClass(id, 'up');});
        o.find('a[name="down"]').click(function(){Board.Class.sortClass(id, 'down');});

        ct.append(o);

        $('#classname').val('').focus();
        Board.Class.tidyClassSort();
    },

    /**
     * 移除分类
     *
     * @param {Object} id
     */
    removeClass: function(id) {
        if (!confirm(TOP.TEXT.INVALID_CLASS_NAME)) {
            return ;
        }

        var ct = $('#class-list'),
        count = ct.find('tr').size();

        $('#class-' + id).remove();

        if (count <= 1) {
            $('#classes').attr('checked', false);
            $('#classify').attr('checked', false);
            $('#class-box').hide();
            return ;
        } else {
            Board.Class.tidyClassSort();
        }
    },

    /**
     * 更新排序号
     */
    tidyClassSort: function() {
        var ct = $('#class-list'),
            ordernum = 1;

        ct.find('tr').each(function(){
            var tr = $(this);
            id = tr.attr('id').replace('class-', '');
            tr.find('input:[name="ordernum-'+id+'"]').val(ordernum);
            ordernum++;
        });

        ct.find('a.gray').removeClass('gray');
        ct.find('tr:first-child a[name="up"]').addClass('gray');
        ct.find('tr:last-child a[name="down"]').addClass('gray');
    },

    /**
     * 分类排序
     *
     * @param {Object} id
     * @param {Object} type
     */
    sortClass: function(id, type) {
        var item = $('#class-' + id);

        if (type == 'up') {
            if (!item.prev().size()) {
                return ;
            }

            item.insertBefore(item.prev());
        } else if (type == 'down') {
            if (!item.next().size()) {
                return ;
            }

            item.insertAfter(item.next());
        }

        Board.Class.tidyClassSort();
    },

    /**
     * 重命名主题分类
     */
    renameClass: function(classid) {
        var oldname = $('#class-' + classid + ' td[name="classname"]').text();
        if (!TOP.labelRenameWin) {

            var win = $('#rename-win-src');

            TOP.classRenameWin = TOP.appendWindow('class-rename-win', win.html(), {
                draggable: true,
                onShow: function(){},
                onClose: function(){
                    TOP.classRenameWin = TOP.classRenameWin.destroy();
                }
            });

            var scope = TOP.document.body;
            var renameform = $('#class-rename-win', scope);
            renameform.find('input[name="classid"]').val(classid);
            renameform.find('input[name="classname"]').val(oldname);

            renameform.find('button[name="confirm"]').click(function(){
                var name = renameform.find('input[name="classname"]').val().replace(/^\s+|\s+$/, '');
                if (!name) {
                    TOP.classRenameWin.close();
                    TOP.showMessage(TOP.TEXT.INVALID_CLASS_NAME);
                    return ;
                }

                $('#class-' + classid + ' td[name="classname"]').text(name);
                $('#class-' + classid + ' input[name="classname-'+classid+'"]').val(name);
                TOP.classRenameWin.close();
            });
        }

        TOP.classRenameWin.show();
    }
};

/**
 * 模板
 */
Board.Template = {

    editor: null,

    currentId: null,

    init: function() {
        if (null === this.editor) {
            this.editor = new TOP.Editor(document.getElementById('templateContent'), {
                resizeType : 1,
                width: '100%',
                minHeight: 150,
                themeType : 'tudu',
                css: Board.getEditorCss(),
                scope: window,
                ctrl: {
                    13: function(){$('input[name="save-tpl"]').click();}
                }
            }, jQuery);
        }
    },

    /**
     * 添加模版
     */
    appendTemplate: function(tplname, tplcontent) {
        var o  = $('#tpl-new').clone(),
            id = 'new-' + (++_CLASS_AUTOID),
            tpl = $('#tpl-list'),
            _o = this;

        o.attr('id', 'tpl-' + id).show();
        o.find('td[name="tplname"]').text(tplname);
        o.find('input[name="newtpl[]"]').attr('name', 'template[]').val(id);
        o.find('input[name="tplordernum"]').attr('name', 'tplordernum-' + id);
        o.find('input[name="tplname"]').attr('name', 'tplname-' + id).val(tplname);
        o.find('textarea[name="tplcontent"]').attr('name', 'tplcontent-' + id).val(tplcontent);
        o.find('a[name="modify"]').click(function(){
            _o.editTemplate(id);
        });
        o.find('a[name="delete"]').click(function(){
            _o.deleteTemplate(id);
        });
        o.find('a[name="up"]').click(function(){
            _o.sortTemplate(id, 'up');
        });
        o.find('a[name="down"]').click(function(){
            _o.sortTemplate(id, 'down');
        });

        tpl.append(o);
        _o.tidyTemplateSort();
    },

    /**
     * 编辑模板
     */
    editTemplate: function(id) {
        var o = $('#tpl-' + id),
            tplname = o.find('input[name="tplname-' + id + '"]').val(),
            tplcontent = o.find('textarea[name="tplcontent-' + id + '"]').val();

        $('input[name="templateName"]').val(tplname);
        this.editor.setSource(tplcontent);
        $('textarea[name="templateContent"]').val(tplcontent);
        $('input[name="save-tpl"]').val(TOP.TEXT.SAVE_TPL);
        this.currentId = id;
    },

    /**
     * 保存模板
     */
    saveTemplate: function() {
        
        var tpl = $('#tpl-list'),
            count = tpl.find('tr').size(),
            tplname = $('input[name="templateName"]').val().replace(/^\s+|\s+$/, ''),
            tplcontent = this.editor.getSource(),
            _o = this;

        if (!tplname.length) {
            TOP.showMessage(TOP.TEXT.INVALID_TPL_NAME);
            $('input[name="templateName"]').focus();
            return false;
        }

        var _div = $('<div>').html(tplcontent);
        if (!_div.text().replace(/^\s+|\s+$/, '').length && !_div.find('img').size()) {
            TOP.showMessage(TOP.TEXT.INVALID_TPL_CONTENT);
            $('textarea[name="templateContent"]').focus();
            return false;
        }

        if(!this.currentId){
            _o.appendTemplate(tplname, tplcontent);
            $('input[name="save-tpl"]').val(TOP.TEXT.ADD_TPL);

        } else {
            var o = $('#tpl-' + this.currentId),
                tplid = o.find('input[name="tplid-' + this.currentId + '"]').val(),
                tplname = $('input[name="templateName"]').val().replace(/^\s+|\s+$/, ''),
                tplcontent = this.editor.getSource();

            o.find('td[name="tplname"]').text(tplname);
            o.find('input[name="tplname-' + this.currentId + '"]').val(tplname);
            o.find('textarea[name="tplcontent-' + this.currentId + '"]').val(tplcontent);

            $('input[name="save-tpl"]').val(TOP.TEXT.ADD_TPL);
            this.currentId = null;
        }

        this.editor.setSource('');
        $('textarea[name="templateContent"]').val('');
        $('input[name="templateName"]').val('').focus();
    },

    /**
     * 删除模板
     */
    deleteTemplate: function(id) {
        
        if (!confirm(TOP.TEXT.CONFIRM_DELETE_TPL)) {
            return ;
        }

        var _o = this,
            tpl = $('#tpl-list'),
            count = tpl.find('tr').size();

        $('#tpl-' + id).remove();
        this.currentId = null;

        if (count <= 1) {
            $('#template').attr('checked', false);
            $('#template-box').hide();
            return ;
        } else {
            _o.tidyTemplateSort();
        }
    },

    /**
     * 更新排序序号
     */
    tidyTemplateSort: function() {
        var ct = $('#tpl-list'),
            ordernum = 1;

        ct.find('tr').each(function(){
            var tr = $(this),
                id = tr.attr('id').replace('tpl-', '');
            tr.find('input:[name="tplordernum-'+id+'"]').val(ordernum);
            ordernum++;
        });

        ct.find('a.gray').removeClass('gray');
        ct.find('tr:first-child a[name="up"]').addClass('gray');
        ct.find('tr:last-child a[name="down"]').addClass('gray');
    },

    /**
     * 模板排序
     */
    sortTemplate: function(id, type) {
        var _o = this,
            item = $('#tpl-' + id);

        if (type == 'up') {
            if (!item.prev().size()) {
                return ;
            }

            item.insertBefore(item.prev());
        } else if (type == 'down') {
            if (!item.next().size()) {
                return ;
            }

            item.insertAfter(item.next());
        }

        _o.tidyTemplateSort();
    }
};