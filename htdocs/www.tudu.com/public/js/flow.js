/**
 * 工作流
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: flow.js 2789 2013-03-21 10:09:13Z chenyongfa $
 */
if (typeof(getTop) != 'function') {
    function getTop() {
        return parent;
    }
}

var TOP = TOP || getTop();
/**
 * 工作流
 */
var Flow = {
    /**
     * 返回地址
     */
    back: null,

    /**
     * 板块
     */
    boards: null,
    
    /**
     * 
     */
    classes: {},

    /**
     * 板块选择器
     */
    boardSelect: null,

    /**
     * 编辑器
     */
    editor: null,

    /**
     * 抄送输入框
     */
    ccInput: null,

    /**
     * 可用人群输入框
     */
    avaliableInput: null,

    /**
     * 上传
     */
    upload: null,

    /**
     * 网盘文件窗口
     */
    filedialog: null,

    /**
     * 流程图窗口
     */
    chartWin: null,

    /**
     * 工作流ID
     */
    flowId: null,

    /**
     * 保存Cookie信息的名称
     */
    cookieName: 'FLOWOPEN',

    /**
     * 工作流列表
     */
    initList: function() {
        var o = this;

        TOP.keyhint('#keyword', 'gray', true, document.body);

        $(".board .category_2").mouseover(function(){
            $(this).addClass("over")
        }).mouseout(function(){
            $(this).removeClass("over")
        });

        // 回车搜索
        $(document).ready(function() {
            $('#keyword').bind('keyup', function(event) {
                if (event.keyCode == "13") {
                    var keyword = $('#keyword').val();
                    o.search(keyword);
                }
            });
        });

        // 按钮搜索
        $('#dosearch').click(function(){
            var keyword = $('#keyword').val();
            o.search(keyword);
        });

        var bid = this.getCookie();
        if (bid === null)  {
            $('.board_title:eq(0) .fold a').removeClass('icon_unfold');
            $('.board_body:eq(0)').show();
        } else {
            bid = bid.split(',');

            for (var i=0; i<bid.length; i++) {
                $('#f-' + bid[i]).show();
                $('a[_bid="'+bid[i]+'"]').removeClass('icon_unfold');
            }
        }
    },

    /**
     * 搜索
     */
    search: function(keyword) {
        if (!keyword || keyword == $('#keyword').attr('title')) {
            return false;
        } else {
            location.href = '/flow/search?keyword=' + encodeURIComponent(keyword);
        }
    },

    /**
     * 搜索列表
     */
    initSearchList: function() {
        var o = this;

        TOP.keyhint('#keyword', 'gray', true, document.body);

        $(".board .category_2").mouseover(function(){
            $(this).addClass("over")
        }).mouseout(function(){
            $(this).removeClass("over")
        });

        // 回车搜索
        $(document).ready(function() {
            $('#keyword').bind('keyup', function(event) {
                if (event.keyCode == "13") {
                    var keyword = $('#keyword').val();
                    o.search(keyword);
                }
            });
        });

        // 按钮搜索
        $('#dosearch').click(function(){
            var keyword = $('#keyword').val();
            o.search(keyword);
        });

        $('button[name="back"]').bind('click', function(){
            location = o.back;
        });

        var length = parseInt($(".board .category_2").size());
        $('#count').text(length);
    },

    /**
     * 工作流流程图窗口html
     */
    chartWinTpl: [
        '<div class="pop">',
            '<div class="pop_header"><strong id="win-title">'+TOP.TEXT.CHART+'</strong><a class="icon icon_close close"></a></div>',
            '<div class="pop_body" style="padding: 0">',
                '<iframe width="100%" height="400" frameborder="0" scrolling="auto" allowtransparency="true" marginheight="0" marginwidth="0" name="flowchart" id="flowchart" src=""></iframe>',
            '</div>',
            '<div class="pop_footer">',
                '<button type="button" name="close" class="btn">'+TOP.TEXT.CLOSE+'</button>',
            '<div>',
        '</div>'
    ].join(''),

    /**
     * 流程图
     */
    showChart: function(flowId) {
        if (typeof flowId == 'undefined') {
            return false;
        }
        var me = this;
        me.chartWin = TOP.appendWindow('chart-' + flowId, me.chartWinTpl, {
            width: 380,
            draggable: true,
            onShow: function() {
                // 取消、关闭窗口
                me.chartWin.find('a.icon_close, button[name="close"]').bind('click', function(){
                    me.chartWin.close();
                    return false;
                });

                me.chartWin.find('.pop_body iframe').attr('src', '/flow/chart?flowid=' + flowId);
            },
            onClose: function() {
                me.chartWin.destroy();
            }
        });

        me.chartWin.show();
    },

    /**
     * 删除图度工作流
     */
    deleteFlow: function(flowId, isSearch) {
        var o = this;

        if (!isSearch) {
            isSearch = false;
        }

        if (!confirm(TOP.TEXT.CONFIRM_DELETE_FLOW)) {
            return false;
        }

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {flowid: flowId},
            url: '/flow/delete',
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : '');
                if (ret.success) {
                    $('#flow-' + flowId).remove();
                    if (isSearch) {
                        location.reload();
                    }
                }
            },
            error: function(res){}
        });
    },

    /**
     * 保存展开的项目
     */
    setCookie: function() {
        var bid = [],
            me = this;
        $('.board').find('a.icon_fold').each(function(){
            if (!$(this).hasClass('icon_unfold')) {
                bid.push($(this).attr('_bid'));
            }
        });

        if (bid.length <= 0) {
            bid = null;
        } else {
            bid = bid.join(',');
        }

        TOP.Cookie.set(me.cookieName, bid);
    },

    /**
     * 获取展开的项目
     */
    getCookie: function() {
        return TOP.Cookie.get(this.cookieName);
    },

    /**
     * 展开/收缩
     *
     * 工作流列表 板块分区
     */
    toggle: function(bid) {
        $('#'+bid+'-icon').toggleClass("icon_unfold");
        $('#'+bid).toggle();

        this.setCookie();
    },

    /**
     * 更新前一步骤
     */
    refreshPrevId: function() {
        var me = this,
            target = $('#steps-' + me.flowId);

        target.find('input[name^="prev-"]').each(function(){
            var pervId = $(this).val();
            if (pervId != '^head' && pervId != '^break') {
                var key = target.find('tr[_stepid="'+pervId+'"]').attr('_key');
                $(this).val(key);
            }
        });
    },

    /**
     * 
     * @param {Object} isBasic
     */
    showBasic: function(isBasic) {
        if (isBasic) {
            $('#steps-div').hide();
            $('#basic-div').show();
            $('#steps-header').hide();
            $('#basic-header').show();
            this.editor.focus();
        } else {
            $('#basic-div').hide();
            $('#steps-div').show();
            $('#basic-header').hide();
            $('#steps-header').show();
        }
    },

    /**
     * 设置步骤窗口
     */
    stepsDialog: null,

    /**
     * 设置步骤窗口html
     */
    stepsDialogTpl: [
        '<div class="pop">',
            '<div class="pop_header"><strong id="win-title">'+TOP.TEXT.SET_STEP_INFO+'</strong><a class="icon icon_close close"></a></div>',
            '<div class="pop_body steps_box">',
                '<input id="users" name="users" value="" type="hidden" />',
                '<div id="base_step" style="color: #000000;">',
                    '<table cellspacing="3" cellpadding="3">',
                    '<tr>',
                        '<td class="steps_txt">'+TOP.TEXT.STEP_TYPE+'</td>',
                        '<td class="info_forms"><label for="review"><input type="radio" value="1" id="review" name="type" checked="checked" /><span class="steps_ml_mr">'+TOP.TEXT.REVIEW+'</span></label><label for="execute"><input type="radio" value="0" id="execute" name="type" /><span class="steps_ml">'+TOP.TEXT.EXECUTE+'</span></label></td>',
                    '</tr>',
                    '<tr>',
                        '<td class="steps_txt">'+TOP.TEXT.INSERT_TO+'</td>',
                        '<td><select id="position" name="position"><option value="-1" _stepid="^head">'+TOP.TEXT.START+'</option></select><span class="steps_ml">'+TOP.TEXT.AFTER_LINE+'</span></td>',
                    '</tr>',
                    '<tr>',
                        '<td class="steps_txt">'+TOP.TEXT.STEP_SUBJECT+'</td>',
                        '<td><input id="subject" name="subject" maxlength="30" type="text" class="input_text" style="width:280px;" /></td>',
                    '</tr>',
                    '<tr>',
                        '<td class="steps_txt steps_txt_top">'+TOP.TEXT.DESCRIPTION+'</td>',
                        '<td><textarea id="description" name="description" maxlength="50" cols="40" rows="3" style="width:280px;" class="form_textarea"></textarea></td>',
                    '</tr>',
                    '<tr>',
                        '<td class="steps_txt steps_txt_top"><span id="review-txt">'+TOP.TEXT.IF_REVIEWER_DISAGREE+'</span><span id="execute-txt" style="display:none;">'+TOP.TEXT.IF_RECEIVER_REJECT+'</span></td>',
                        '<td>',
                            '<p style="line-height: 0px;"><label for="break"><input id="break" name="prev" type="radio" value="^break" checked="checked" style="margin:0;padding:0;height:13px;width:13px;" /><span class="steps_ml">'+TOP.TEXT.STOP_FLOW+'</span></label></p>',
                            '<p style="line-height: 0px;"><label for="prev"><input id="prev" name="prev" type="radio" value="^head" style="margin:0;padding:0;height:13px;width:13px;" /><span class="steps_ml steps_mr">'+TOP.TEXT.STEP_RETURN+'</span></label><select id="prev-select" disabled="disabled"><option value="^head">'+TOP.TEXT.START+'</option></select></p>',
                        '</td>',
                    '</tr>',
                    '</table>',
                '</div>',
                '<div id="review-div" style="display:none;">',
                    '<div class="br"><br /></div>',
                    '<p style="margin:10px;color: #000000;">'+TOP.TEXT.REVIEW_TYPE,
                        '<select id="review-type" class="steps_ml" style="width:360px;">',
                            '<option value="0">--'+TOP.TEXT.SELECT_REVIEW_TYPE+'--</option>',
                            '<option value="1">'+TOP.TEXT.ASSIGN_REVIEWER+'</option>',
                            '<option value="2">'+TOP.TEXT.HIGHER_REVIEW+'</option>',
                        '</select>',
                    '</p>',
                    '<div class="br"><br /></div>',
                    '<div id="select-review-user"></div>',
                '</div>',
                '<div id="execute-div" style="display:none;">',
                    '<div id="select-execute-user"></div>',
                    '<p style="margin-left:6px;color: #000000;"><label for="claim"><input type="checkbox" id="claim" value="claim" />'+TOP.TEXT.TUDU_CLAIM_MODE+'（'+TOP.TEXT.TUDU_CLAIM_TIPS+'）</label></p>',
                '</div>',
            '</div>',
            '<div class="pop_footer">',
                '<button type="button" name="next" class="btn">'+TOP.TEXT.NEXT+'</button>',
                '<button type="button" name="save" class="btn" style="display:none;">'+TOP.TEXT.SAVE+'</button>',
                '<button type="button" name="prev" class="btn" style="display:none;">'+TOP.TEXT.PREV+'</button>',
                '<button type="button" name="cancel" class="btn">'+TOP.TEXT.CANCEL+'</button>',
            '<div>',
        '</div>'
    ].join(''),

    /**
     * 新建步骤的索引
     */
    index: 1,

    /**
     * 步骤ID
     */
    stepId: null,

    /**
     * 联系人选择器
     */
    selector: null,

    /**
     * 步骤内容是否有修改过
     */
    isChanged: false,

    /**
     * 步骤类型
     */
    type: null,

    /**
     * 设置步骤
     * @param stepId
     */
    setSteps: function(stepId, type) {
        var flowId = this.flowId,
            o = this;

        if (typeof stepId != 'undefined') {
            this.stepId = stepId;
        }

        if (typeof type != 'undefined') {
            this.type = type;
        }

        o.stepsDialog = TOP.appendWindow('steps-' + flowId, o.stepsDialogTpl, {
            width: 460,
            draggable: true,
            onShow: function() {
                var _$ = TOP.getJQ();

                // 取消、关闭窗口
                o.stepsDialog.find('a.icon_close, button[name="cancel"]').bind('click', function(){
                    o.stepsDialog.close();
                    return false;
                });
                // 步骤类型选择
                o.stepsDialog.find('input[name="type"]').bind('click', function(){
                    var type = parseInt(_$(this).val());
                    o.toggleTxt(type);
                });

                _$('#prev-select').bind('change', function(){
                    var val = $(this).val();
                    _$('#prev').val(val);
                });

                // 下拉框联动
                _$('#position').bind('change', function(){
                    var prev = _$('#prev-select').val();
                    _$('#prev-select option[value="^head"]').nextAll().remove();
                    o.setOptionItems();
                    o.refreshOptions();

                    if (_$('#prev-select option[value="'+prev+'"]').size()) {
                        _$('#prev-select').val(prev);
                        _$('#prev').val(prev);
                    } else {
                        o.selectLastOption();
                    }
                });

                _$('input[name="prev"]').bind('click', function(){
                    prev = _$('input[name="prev"]:checked').val();
                    if (prev == '^break') {
                        _$('#prev-select').attr('disabled', true);
                    } else {
                        _$('#prev-select').attr('disabled', false);
                    }
                });

                o.setOptionItems(true);
                o.setOptionItems();

                if (o.stepId !== null && o.type === null) {
                    _$('#subject').val($('input[name="subject-'+stepId+'"]').val());
                    _$('#description').val($('input[name="description-'+stepId+'"]').val());
                    _$('#users').val($('input[name="users-'+stepId+'"]').val());

                    o.stepsDialog.find('button[name="next"]').hide();
                    o.stepsDialog.find('button[name="save"]').show();

                    var type = parseInt($('input[name="type-'+stepId+'"]').val()),
                        order = $('input[name="order-'+stepId+'"]').val(),
                        prev = $('input[name="prev-'+stepId+'"]').val();
                    if (type != 1) {
                        _$('#execute').attr('checked', true);
                        o.toggleTxt(parseInt(type));
                    }

                    if (type == 2) {
                        _$('#claim').attr('checked', true);
                    }
                    setTimeout(function(){_$('#position').val(parseInt(order) - 1);o.refreshOptions();}, 1);

                    if (prev == '^break') {
                        _$('#break').attr('checked', true);
                        _$('#prev').attr('checked', false);
                    } else {
                        _$('#prev-select').attr('disabled', false);
                        _$('#break').attr('checked', false);
                        _$('#prev').attr('checked', true).val(prev);
                        setTimeout(function(){_$('#prev-select option[value="'+prev+'"]').attr('selected', 'selected');}, 10);
                    }
                    _$('#execute').attr('disabled', true);
                    _$('#review').attr('disabled', true);

                } else if (o.stepId !== null && o.type !== null) {
                    _$('#base_step').hide();
                    _$('#users').val($('input[name="users-'+stepId+'"]').val());
                    o.stepsDialog.find('button[name="save"]').show();
                    o.stepsDialog.find('button[name="prev"]').hide();
                    o.stepsDialog.find('button[name="next"]').hide();
                    if (o.type != 1) {
                        _$('#execute').attr('checked', true);
                        _$('#review').attr('checked', false);
                    } else {
                        _$('#review').attr('checked', true);
                        _$('#execute').attr('checked', false);
                    }
                    if (o.type == 1) {
                        _$('#win-title').text(TOP.TEXT.SELECT_REVIEW_TYPE);
                        _$('#execute-div').find('div.contact_selector').remove();
                        _$('#review-div').show();
                        if (_$('#review-div').find('div.contact_selector').size() <= 0) {
                            setTimeout(function(){_$('#review-type option:first').attr('selected', 'selected');}, 1);
                            o.selector = null;
                        }
                        if (_$('#users').val() == '^upper') {
                            setTimeout(function(){_$('#review-type option[value="2"]').attr('selected', 'selected');}, 1);
                        } else if (_$('#users').val()) {
                            setTimeout(function(){_$('#review-type option[value="1"]').attr('selected', 'selected');}, 1);
                            if (null === o.selector) {
                                o.initSelector(_$('#select-review-user'), _$('#users'), false, true);
                            }
                        }
                    } else {
                        _$('#win-title').text(TOP.TEXT.SELECT_RECEIVER);
                        _$('#review-div').find('div.contact_selector').remove();
                        _$('#execute-div').show();
                        if (_$('#execute-div').find('div.contact_selector').size() <= 0) {
                            o.selector = null;
                        }
                        if (null === o.selector) {
                            o.initSelector(_$('#select-execute-user'), _$('#users'), false, false);
                        }
                        var type = parseInt($('input[name="type-'+stepId+'"]').val());
                        if (type == 2) {
                            _$('#claim').attr('checked', true);
                        }
                    }

                    o.stepsDialog.center();
                } else {
                    o.selectLastOption();
                    setTimeout(function(){_$('#position option:last').attr('selected', true);}, 1);
                    o.stepsDialog.find('button[name="save"]').hide();
                    o.stepsDialog.find('button[name="next"]').show();
                }

                _$('#review-type').bind('change', function(){
                    var val = parseInt(_$('#review-type option:selected').val());
                    if (val == 1) {
                        _$('#users').val('');
                        _$('div.br').empty();
                        if (null === o.selector) {
                            o.initSelector(_$('#select-review-user'), _$('#users'), false, true);
                        }
                    } else {
                        _$('div.br').empty();
                        _$('div.br').append('<br />');
                        _$('#users').val('^upper');
                        _$('#select-review-user').empty();
                        o.selector = null;
                    }
                    o.stepsDialog.center();
                });

                o.stepsDialog.find('button[name="next"]').bind('click', function(){
                    var subject = _$('#subject').val().replace(/\s+/, ''),
                        desc = _$('#description').val().replace(/\s+/, '');
                    if (!subject) {
                        TOP.showMessage(TOP.TEXT.PARAMS_INVALID_STEP_SUBJECT);
                        return false;
                    }
                    /*if (!desc) {
                        TOP.showMessage(TOP.TEXT.PARAMS_INVALID_STEP_DESCRIPTION);
                        return false;
                    }*/

                    var type = parseInt(_$('input[name="type"]:checked').val());
                    _$('#base_step').hide();
                    o.stepsDialog.find('button[name="cancel"]').hide();
                    o.stepsDialog.find('button[name="save"]').show();
                    o.stepsDialog.find('button[name="prev"]').show();
                    o.stepsDialog.find('button[name="next"]').hide();
                    if (type == 1) {
                        _$('#win-title').text(TOP.TEXT.SELECT_REVIEW_TYPE);
                        _$('#execute-div').find('div.contact_selector').remove();
                        _$('#review-div').show();
                        if (_$('#review-div').find('div.contact_selector').size() <= 0) {
                            setTimeout(function() {_$('#review-type option:first').attr('selected', true);}, 10);
                            o.selector = null;
                        }
                        if (_$('#users').val() == '^upper') {
                            _$('#review-type').val('2');
                        } else if (_$('#users').val()) {
                            _$('#review-type').val('1');
                            if (null === o.selector) {
                                o.initSelector(_$('#select-review-user'), _$('#users'), false, true);
                            }
                        }
                    } else {
                        _$('#win-title').text(TOP.TEXT.SELECT_RECEIVER);
                        _$('#review-div').find('div.contact_selector').remove();
                        _$('#execute-div').show();
                        if (_$('#execute-div').find('div.contact_selector').size() <= 0) {
                            o.selector = null;
                        }
                        if (null === o.selector) {
                            o.initSelector(_$('#select-execute-user'), _$('#users'), false, false);
                        }
                    }

                    o.stepsDialog.center();
                });
                _$('#prev').val(_$('#prev-select option:selected').val());
                o.stepsDialog.find('button[name="prev"]').bind('click', function(){
                    _$('#win-title').text(TOP.TEXT.SET_STEP_INFO);
                    o.stepsDialog.find('button[name="cancel"]').show();
                    o.stepsDialog.find('button[name="next"]').show();
                    o.stepsDialog.find('button[name="save"]').hide();
                    o.stepsDialog.find('button[name="prev"]').hide();
                    _$('#review-div').hide();
                    _$('#execute-div').hide();
                    _$('#base_step').show();
                    o.stepsDialog.center();
                });

                if (_$('#position').outerWidth() > 250) {
                    _$('#position').css({'width': 250});
                }
                if (_$('#prev-select').outerWidth() > 250) {
                    _$('#prev-select').css({'width': 250});
                }

                /*var position = _$('#position option:selected').val();
                _$('#prev-select option').each(function(){
                    if (_$(this).val() > o.stepId) {
                        _$(this).remove();
                    }
                });*/
                
                o.stepsDialog.find('button[name="save"]').bind('click', function(){
                    var type = parseInt(_$('input[name="type"]:checked').val());
                    
                    if ((o.type !== null && type == 1) || (o.stepId === null && type == 1)) {
                        if (_$('#review-type option:selected').val() == 0) {
                            TOP.showMessage(TOP.TEXT.PARAMS_INVALID_REVIEW_TYPE);
                            return false;
                        }
                    }

                    if (null !== o.selector) {
                        var se = o.selector.getSelected();

                        if (se.length <= 0) {
                            if (type == 1) {
                                TOP.showMessage(TOP.TEXT.PARAMS_INVALID_REVIEWER);
                                return false;
                            } else {
                                TOP.showMessage(TOP.TEXT.PARAMS_INVALID_RECEIVER);
                                return false;
                            }
                        }

                        o.saveSelected(se);
                    }

                    if (_$('#claim:checked').val() == 'claim' && null !== o.selector) {
                        var se = o.selector.getSelected();
                        if (se.length <= 1) {
                            TOP.showMessage('认领模式下，执行必须为多个');
                            return false;
                        }
                    }

                    o.saveStep();
                    o.stepsDialog.close();
                    return false;
                });
            },
            onClose: function() {
                o.stepId = null;
                o.type = null;
                o.selector = null;
                o.stepsDialog.destroy();
            }
        });
        o.stepsDialog.show();
    },

    /**
     * 保存步骤
     */
    saveStep: function(){
        var _$ = TOP.getJQ(),
            o = this;

        var subject = _$('#subject').val(),
            type = parseInt(_$('input[name="type"]:checked').val()),
            users = _$('#users').val(),
            prev = _$('input[name="prev"]:checked').val(),
            desc = _$('#description').val(),
            position = _$('#position option:selected').attr('_stepid'),
            isClaim = _$('#claim:checked').val();

        if (isClaim == 'claim') {
            type = 2;
        }
        
        prev = prev == '^break' ? prev : _$('#prev-select').val();

        if (this.stepId !== null) {
            var target = $('#step-'+this.stepId);

            target.find('input[name^="users-"]').val(users);
            target.find('input[name^="type-"]').val(type);
            if (users != '^upper') {
                target.find('div.username').text(o.getUserName(users));
                target.find('div.username').attr('title', o.getUserNameTips(users));
            } else {
                target.find('div.username').text(TOP.TEXT.HIGHER_REVIEW);
                target.find('div.username').attr('title', TOP.TEXT.HIGHER_REVIEW);
            }

            o.toggleImg(target, type);

            if (this.type === null) {
                target.find('input[name^="subject-"]').val(subject);
                target.find('span[name^="subject-"]').text(subject);
                target.find('input[name^="description-"]').val(desc);
                target.find('input[name^="prev-"]').val(prev);
                
                target.insertAfter($('#step-'+position.replace('^', '_')));
            }
        } else {
            var list = _$('#steps-' + this.flowId),
                item = $(o.stepItem);
            
            item.attr({'id': 'step-' + o.index});
            item.find('img[name="img"]').attr('name', 'img-' + o.index);
            item.find('input[name="member[]"]').val(o.index);
            item.find('span[name="subject"]').attr('name', 'subject-' + o.index).text(subject);
            item.find('input[name="subject"]').attr('name', 'subject-' + o.index).val(subject);
            item.find('input[name="type"]').attr('name', 'type-' + o.index).val(type);
            item.find('input[name="prev"]').attr('name', 'prev-' + o.index).val(prev);
            item.find('input[name="description"]').attr('name', 'description-' + o.index).val(desc);
            item.find('input[name="users"]').attr('name', 'users-' + o.index).val(users);
            item.find('input[name="order"]').attr('name', 'order-' + o.index);
            if (users != '^upper') {
                item.find('div.username').text(o.getUserName(users));
                item.find('div.username').attr('title', o.getUserNameTips(users));
            } else {
                item.find('div.username').text(TOP.TEXT.HIGHER_REVIEW);
                item.find('div.username').attr('title', TOP.TEXT.HIGHER_REVIEW);
            }

            var id = item.attr('id').replace('step-', '');
            item.find('a[name="update-user"]').bind('click', function(){
                o.setSteps(id, type);
            });
            item.find('a[name="modify-step"]').bind('click', function(){
                o.setSteps(id);
            });
            item.find('a[name="delete-step"]').bind('click', function(){
                o.deleteStep(id);
            });

            item.bind('mouseover', function(){$(this).addClass('over')})
                .bind('mouseout', function(){$(this).removeClass('over')});

            item.insertAfter($('#step-'+position.replace('^', '_')));
            o.toggleImg(item, type);

            o.index ++;
        }

        o.refreshOrderNum();
        o.isChanged = true;
        if ($('#null-steps').size() > 0) {
            $('#null-steps').remove();
        }
    },

    stepItem: [
        '<tr>',
            '<td>',
                '<input name="member[]" value="" type="hidden" />',
                '<input name="type" value="" type="hidden" />',
                '<input name="subject" value="" type="hidden" />',
                '<input name="description" value="" type="hidden" />',
                '<input name="users" value="" type="hidden" />',
                '<input name="prev" value="" type="hidden" />',
                '<input name="order" value="" type="hidden" />',
                '<img name="img" src="" align="absmiddle" />&nbsp;&nbsp;<span name="subject"></span>',
            '</td>',
            '<td>',
                '<div class="username"></div>',
            '</td>',
            '<td>',
                '<a href="javascript:void(0)" name="update-user">['+TOP.TEXT.REVIEWER+']</a>&nbsp;<a href="javascript:void(0)" name="modify-step">['+TOP.TEXT.EDIT+']</a>&nbsp;<a href="javascript:void(0);" name="delete-step">['+TOP.TEXT.DELETE+']</a>',
            '</td>',
        '</tr>'
    ].join(''),

    nullItem: [
        '<tr id="null-steps">',
            '<td colspan="3" style="text-align:center;height:100px">'+TOP.TEXT.STEP_NULL+'，'+TOP.TEXT.PLEASE+'<a href="javascript:void(0)" name="create-steps">'+TOP.TEXT.CREATE_STEP+'</a></td>',
        '</tr>'
    ].join(''),

    /**
     * 保存已选择的联系人
     * @param type
     */
    saveSelected: function(se){
        var users = [];
        for (var i = 0, c = se.length; i < c; i++) {
            if (se[i].groupid) {
                users.push(se[i].groupid + ' ' + se[i].name);
            } else if(se[i].email) {
                users.push(se[i].email + ' ' + se[i].name);
            } else if (se[i].separator) {
                users.push(se[i].separator);
            }
        }
        TOP.getJQ()('#users').val(users.join("\n"));
    },

    /**
     * title提示
     */
    getUserNameTips: function(selected) {
        var selected = selected.split("\n"),
            tips = [];

        for (var i = 0, c = selected.length; i < c; i++) {
            var a = selected[i].split(' ');
            if (typeof a[1] != 'undefined') {
                tips.push('<'+a[0]+'>'+a[1]);
            }
        }

        return tips.join(',');
    },

    /**
     * 处理用户地址与用户名，返回用户名
     */
    getUserName: function(selected){
        var selected = selected.split("\n"),
            names = [];
        for (var i = 0, c = selected.length; i < c; i++) {
            var a = selected[i].split(' ');
            if (typeof a[1] != 'undefined') {
                names.push(a[1]);
            }

            if (names.length > 2) {
                names.push('...');
                break ;
            }
        }

        return names.join(',');
    },

    /**
     * 联系人选择
     */
    initSelector: function(obj, valInput, containGroup, order){
        var o = this;
        if (!containGroup) {
            containGroup = false;
        }

        if (!order) {
            order = false;
        }

        var panels = ['lastcontact', 'common', 'contact'];
        if (order) {
            panels = ['common'];
        }

        var selected = [],
            val = valInput.val();
        if (val != '^upper' && val.length > 0) {
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

        this.selector = new TOP.ContactSelector({appendTo: obj, enableGroup: containGroup, selected: selected, order: order, panels: panels});
        var panel = TOP.Cookie.get('CONTACT-PANEL');
        if (!panel) {
            panel = 'common';
        }
        this.selector.switchPanel(panel);
    },

    /**
     * 刷新步骤排序号
     */
    refreshOrderNum: function(){
        var target = $('#steps-'+this.flowId),
            num = 0;
        target.find('input[name^="order-"]').each(function(){
            $(this).val(num);
            num++;
        });
    },

    /**
     * 刷新下拉框选项
     */
    refreshOptions: function(){
        var id = TOP.getJQ()('#position option:selected').attr('_stepid');//this.stepId;//TOP.getJQ()('#position option:selected').attr('_stepid');
        TOP.getJQ()('#prev-select option[value="'+id+'"]').nextAll().remove();
    },

    /**
     * 选择最后的一项
     */
    selectLastOption: function(){
        setTimeout(function() {
            TOP.getJQ()('#prev-select option:last').attr('selected', true);
            Flow.updatePrevValue();
        }, 1 );
    },

    /**
     * 写入上一步骤ID
     */
    updatePrevValue: function(){
        var prev = TOP.getJQ()('#prev-select option:last').val();
        TOP.getJQ()('#prev').val(prev);
    },

    /**
     * 图片
     * @param target
     * @param type
     */
    toggleImg: function(target, type){
        if (type == 1) {
            target.find('img[name^="img-"]').attr('src', '/images/icon/flow_examine.gif');
            target.find('a[name="update-user"]').text('['+TOP.TEXT.REVIEWER+']');
        } else {
            target.find('img[name^="img-"]').attr('src', '/images/icon/flow_execute.gif');
            target.find('a[name="update-user"]').text('['+TOP.TEXT.RECEIVER+']');
        }
    },

    /**
     * 改变文字
     * @param type
     */
    toggleTxt: function(type){
        var _$ = TOP.getJQ();
        if (type == 1) {
            _$('#review-txt').show();
            _$('#execute-txt').hide();
        } else {
            _$('#review-txt').hide();
            _$('#execute-txt').show();
        }
    },

    /**
     * 设置下拉框选项
     */
    setOptionItems: function(isOrder){
        var flowId = this.flowId,
            o = this;
            items = o.getOptionItems();

        if (typeof isOrder == 'undefined') {
            isOrder = false;
        }

        var options = '';
        if (isOrder) {
            for (var i=0; i<items.length; i++) {
                if (o.stepId !== null && o.stepId == items[i].stepid) {
                    continue;
                }
                options += '<option value='+items[i].order+' _stepid="'+items[i].stepid+'">'+items[i].subject+'</option>';
            }
            TOP.getJQ()('#position').append(options);
        } else {
            for (var i=0; i<items.length; i++) {
                if ((o.stepId !== null && o.stepId == items[i].stepid)) {
                    continue;
                }
                options += '<option value='+items[i].stepid+'>'+items[i].subject+'</option>';
            }
            TOP.getJQ()('#prev-select').append(options);
        }
    },

    /**
     * 获取下拉框选项
     */
    getOptionItems: function(){
        var flowId = this.flowId,
            obj = $('#steps-' + flowId),
            o = this,
            items = [];

        obj.find('input[name="member[]"]').each(function(){
            var id = $(this).val(),
                stepid = $('input[name="id-'+id+'"]').val(),
                order = $('input[name="order-'+id+'"]').val(),
                subject = $('input[name="subject-'+id+'"]').val();
            items.push({stepid: id, subject: subject, order: order});
        });

        return items;
    },
    
    /**
     * 删除步骤
     */
    deleteStep: function(stepId) {
        if (!confirm(TOP.TEXT.CONFIRM_DELETE_STEP)) {
            return false;
        }

        $('#step-' + stepId).remove();

        var o = this,
            list = $('#steps-' + this.flowId);

        if (list.find('tr').size() <= 1) {
            item = $(o.nullItem);

            item.find('a[name="create-steps"]').bind('click', function(){
                o.setSteps();
            });

            list.append(item);
        }

        o.refreshOrderNum();
        o.isChanged = true;
    },

    /**
     * 离开提示
     */
    initLeaveEvent: function() {
        var o = this;
        // 获取是否有更改过的状态
        if (o.isChanged) {
            // 弹窗
            TOP.Frame.Dialog.show({
                title: TOP.TEXT.LEAVE_HINT,
                body: '<p style="padding:15px 0;margin-left:-12px"><strong>' + TOP.TEXT.FLOW_SAVE_LEAVE_CONFIRM + '</strong></p>',
                buttons: [
                    {
                        text: TOP.TEXT.CONFIRM,
                        cls: 'btn',
                        events: {click: function(){
                            TOP.Frame.Dialog.close();
                            location = o.back;
                        }}
                    },
                    {
                        text: TOP.TEXT.CANCEL,
                        cls: 'btn',
                        events: {click: function(){TOP.Frame.Dialog.close();}}
                    }
                ]
            });
        } else {
            location = o.back;
        }
    },

    /**
     * 新建/更新工作流页面
     */
    initModify: function() {
        var o = this;

        TOP.keyhint('input[name="description"]', 'gray', true, document.body);
        TOP.keyhint('input[name="elapsedtime"]', 'gray', true, document.body);

        // 初始化板块选择控件
        this.boardSelect = new BoardSelector({
            input: $('#board-input'),
            name: 'bid',
            boards: o.boards
        });
        this.boardSelect.bind('select', function() {
            var bid  = $('#bid').val();

            o.loadClasses(bid, '#classid');
        });

        $('#classid').bind('change', function() {
            var bid = $('#bid').val();
            
            if (bid && this.value == '^add-class') {
                o.createClass(bid, '#classid');
            }
        });

        // 初始化编辑器
        var h = $(window).height(),
        ch = $(document.body).height();
        var editorHeight = Math.max($('#content').height() + (h - ch - 15), 200);
        $('#content').css('height', editorHeight + 'px');
        this.editor = new TOP.Editor(document.getElementById('content'), {
            resizeType : 1,
            width: '100%',
            minHeight: 200,
            themeType : 'tudu',
            scope: window
        }, jQuery);

        // 可用人群选择控件
        this.avaliableInput = new TOP.ContactInput({
            id: 'avaliable-input', target: $('#i-avaliable'), valuePlace: $('#avaliable'), group: true, contact: false,
            jq: jQuery, valueItems: ['_id']
        });
        o.initSelectLink('#select-avaliable', o.avaliableInput, $('#avaliable'), ['common'], true, false);
        // 可用人群添加人群
        var avaliable = $('#avaliable').val().split("\n");
        for(var i = 0, c = avaliable.length; i < c; i++) {
            if (!avaliable[i].length) {
                continue ;
            }
            this.avaliableInput.addItem(avaliable[i], {name: avaliable[i], _id: (-1 != avaliable[i].indexOf('@') ? '' : avaliable[i]), title: avaliable[i]});
        }

        // 抄送人选择控件
        this.ccInput = new TOP.ContactInput({
            id: 'cc-input', target: $('#i-cc'), valuePlace: $('#cc'), group: true, contact: true, jq: jQuery
        });
        o.initSelectLink('#select-cc', o.ccInput, $('#cc'), ['lastcontact', 'common', 'contact'], true, false);
        $('#flowform').submit(function(){return false;});
        // 保存按钮
        $('button[name="save"]').click(function(){
            o.saveFlow();
        });
        $('button[name="next"]').click(function(){
            o.saveBasic();
        });
        // 加载模板
        $('a[name="tpllist"]').bind('click', function(e){
            var textarea = $('a[name="tpllist"]').attr('_textarea'),
                   boardId = o.boardSelect.getValue();
            e.srcElement = $(this).parent('span.add')[0];
            Tudu.Template.showMenu(e ,o.editor, boardId);
            TOP.stopEventBuddle(e);
        });
        // 网盘附件
        $('#netdisk-btn').click(function(){
            if (o.filedialog === null) {
                o.filedialog = new FileDialog({id: 'netdisk-dialog'});
            }

            o.filedialog.show();
        });
        // 截屏
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
        // 取消按钮
        $('button[name="f-cancel"]').bind('click', function(){
            if ($('input[name="action"]').val() == 'create') {
                location = o.back;
            } else {
                location.reload();
                //o.showBasic(false);
            }
        });

        $(".grid_list tr").mouseover(function(){
            $(this).addClass("over")
        }).mouseout(function(){
            $(this).removeClass("over")
        });

        $('input[name="elapsedtime"]').bind('keyup', function(){
            this.value = this.value.replace(/[^0-9]+/, '');
        }).blur(function(){
            $('input[name="elapsedtime"]').val(this.value);
        });

        o.setAvalible();

        $('button[name="cancel"]').bind('click', function(){
            o.initLeaveEvent();
        });

        $('a[name="create-steps"]').bind('click', function(){
            o.setSteps();
        });

        o.refreshPrevId();
        this.initUnloadEvent();
    },
    
    initUnloadEvent: function() {
        var me = this;
        //$('a:not(.xheButton):not([href^="javascript:"]))').bind('click', _leaveDialog);
        TOP.getJQ()('a:not([href^="javascript:"]):not([target="_blank"])').bind('click', _leaveDialog);
        TOP.getJQ()('form').bind('submit', _leaveDialog);
        
        window.onunload = function(){
            TOP.getJQ()('a').unbind('click', _leaveDialog);
            TOP.getJQ()('form').unbind('submit', _leaveDialog);
        };
        
        // 离开页面时提示
        function _leaveDialog() {
            if (!$('#steps-div:visible').size()) {
                return true;
            }
            
            var trigger = $(this);
            
            TOP.Frame.Dialog.show({
                title: TOP.TEXT.FLOW_LEAVE_HINT,
                body: '<p style="padding:15px 0;margin-left:-12px"><strong>' + TOP.TEXT.COMPOSE_EXIT_SAVE_HINT + '</strong></p>',
                buttons: [{
                       text: TOP.TEXT.SAVE,
                       cls: 'btn',
                       events: {click: function(){
                            if (typeof(isnew) != 'undefined' && isnew) {
                                $('#action').val('save');
                            } else {
                                $('#action').val('send');
                            }
                            
                            TOP.window.onbeforeunload = function(){};
                            me.saveFlow(function() {
                                if (trigger[0].tagName.toLowerCase() == 'a') {
                                       if ((trigger[0].target && trigger[0].target == 'main')
                                            || trigger.parents('body')[0] == document.body) {
                                           location = trigger.attr('href');
                                       } else {
                                           TOP.location = trigger.attr('href');
                                       }
                               } else {
                                   trigger.unbind('submit', _leaveDialog).submit();
                               }
                                
                            });
                            TOP.Frame.Dialog.close();
                       }}
                   },
                   {
                       text: TOP.TEXT.DISCARD,
                       cls: 'btn',
                       events: {click: function(){
                              TOP.window.onbeforeunload = function(){};
                           if (trigger[0].tagName.toLowerCase() == 'a') {
                               if ((trigger[0].target && trigger[0].target == 'main')
                                    || trigger.parents('body')[0] == document.body) {
                                   location = trigger.attr('href');
                               } else {
                                   TOP.location = trigger.attr('href');
                               }
                           } else {
                               trigger.unbind('submit', _leaveDialog).submit();
                           }
                           TOP.Frame.Dialog.close();
                       }}
                   },
                   {
                       text: TOP.TEXT.CANCEL,
                       cls: 'btn',
                       events: {click: function(){TOP.Frame.Dialog.close()}}
                   }
                ]
            });
            
            return false;
        }
    },
    
    /**
     * 加载主题分类
     */
    loadClasses: function(bid, select, classid, isChild) {
        if (!bid) {
            return _fillSelect([], select);
        }
        
        if (!isChild) {
            isChild = false;
        }
        
        var me = this;
        if (typeof(this.classes[bid]) == 'undefined') {
            $.ajax({
                type: 'GET',
                dataType: 'json',
                url: '/tudu/classes?bid=' + encodeURIComponent(bid),
                success: function(ret) {
                    if (ret.success) {
                        me.classes[bid] = ret.data;
                        _fillSelect(me.classes[bid], select);
                        if (classid) {
                            _selectText(me.classes[bid], select, classid);
                        }
                    }
                },
                error: function(res) {
                    return ;
                }
            });
        } else {
            _fillSelect(me.classes[bid], select);
            if (classid) {
                _selectText(me.classes[bid], select, classid);
            }
        }
        
        function _fillSelect(ret, select) {
            var o = $(select),
                p = o.parent();
            o.find('option:not(:eq(0))').remove();
            
            if (null === ret || !ret.length) {
                p.hide();
                $('#classname').val('');
                return o.attr('disabled', true);
            }
            
            p.show();
            var html = [];
            for (var i = 0, c = ret.length; i < c; i++) {
                html.push('<option value="'+ret[i].classid+'" title="'+ret[i].classname+'">'+ret[i].classname+'</option>');
            }
            
            o.append(html.join(''));
            
            o.attr('disabled', false);
        }
        
        function _selectText(data, select, classid) {
            var o = $(select);
            o.val(classid);
        }
    },
    
    /**
     * 新建主题分类
     */
    createClass: function(bid, select, isChild) {
        var _$ = TOP.getJQ(),
            me = this;
        var btns = [
           {
               text: TOP.TEXT.CONFIRM,
               cls: 'btn',
               events: {click: function(){
                   _sumbitClass();
               }}
           },
           {
                text: TOP.TEXT.CANCEL,
                cls: 'btn close',
                events: {click: function(){
                    Win.close();
                    $(select + ' option:first').attr('selected', 'selected');
                }}
            }
        ];
        
        var sl = $(select);
        
        var Win = TOP.Frame.Dialog.show({
            title: TOP.TEXT.CREATE_BOARD_CLASS,
            body: '<div style="margin:10px;"><form id="classform" action="/board/classes"><div>' + TOP.TEXT.BOARD_CLASS_SUBJECT + TOP.TEXT.CLN + '<input class="input_text" name="classname" type="text" style="width:300px;" value="" /></div></form></div>',
            buttons: btns
        });
        
        function _sumbitClass() {
            var form = _$('#classform'),
                className = form.find('input[name="classname"]').val().replace(/^\s+|\s+$/, '');
            
            if (!bid) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR + ' Missing[bid]');
                return false;
            }
            
            if (!className.length) {
                TOP.showMessage(TOP.TEXT.INVALID_CLASS_NAME);
                form.find('input[name="classname"]').focus();
                return false;
            }
            
            TOP.showMessage(TOP.TEXT.POSTING_DATA, 0, 'success');
            form.find(':input').attr('disabled', true);

            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: {classname: className, bid: bid},
                url: form.attr('action'),
                success: function(ret) {
                    TOP.showMessage(ret.message, 10000, ret.success ? 'success' : null);
                    form.find(':input').attr('disabled', false);
                    if (ret.success) {
                        var data = ret.data;
                        if (data) {
                            sl.prepend('<option title="'+data.cn+'" value="'+data.cid+'">'+data.cn+'</option>');
                            sl.val(data.cid);
                        }
                        // 由于新添加了主题分类，所以清空保存的数据
                        me.classes = {};
                        Win.close();
                    }
                },
                error: function(res) {
                    form.find(':input').attr('disabled', false);
                    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
                }
            });
        }
    },

    setAvalible: function() {
        TOP.Cast.load(function(){
            var avaliable = $('#avaliable').val().split("\n"),
                users = TOP.Cast.get('users'),
                groups = TOP.Cast.get('groups'),
                names = [],
                titles = [],
                full = false;

            for (var j = 0, c = avaliable.length; j < c; j++) {
                for (var i = 0, ul = users.length; i < ul; i++) {
                    if (typeof avaliable[j] == 'undefined' || -1 === avaliable[j].indexOf('@')) {
                        continue ;
                    }

                    if (avaliable[j] == users[i].username) {
                        titles.push('<'+users[i].username+'>'+users[i].truename);
                        if (full) {
                            break;
                        }
                        if (names.length > 3) {
                            names.push('...');
                            full = true;
                            break;
                        }
                        names.push(users[i].truename);
                    }
                }
                for (var i = 0, gl = groups.length; i < gl; i++) {
                    if (typeof avaliable[j] == 'undefined' || -1 !== avaliable[j].indexOf('@')) {
                        continue ;
                    }

                    if (avaliable[j] == groups[i].groupid) {
                        titles.push(groups[i].groupname+'<'+TOP.TEXT.GROUP+'>');
                        if (full) {
                            break;
                        }
                        if (names.length > 3) {
                            names.push('...');
                            full = true;
                            break;
                        }
                        names.push(groups[i].groupname);
                    }
                }
            }

            if (names.length > 0 && titles.length) {
                $('.todo_content table td.avaliable-titles').attr('title', titles.join(','));
                $('#avaliable-names').text(names.join(','));
            } else {
                $('#avaliable-names').text('-');
            }
        });
    },

    setCc: function() {
        TOP.Cast.load(function(){
            var cc = $('#cc').val().split("\n"),
                users = TOP.Cast.get('users'),
                groups = TOP.Cast.get('groups'),
                names = [],
                titles = [],
                full = false;

            for (var j = 0, c = cc.length; j < c; j++) {
                if (typeof cc[j] == 'undefined') break;
                var flowCc = cc[j].split(" ");
                for (var i = 0, uc = users.length; i < uc; i++) {
                    if (typeof flowCc[0] == 'undefined' || -1 === flowCc[0].indexOf('@')) {
                        continue ;
                    }

                    if (flowCc[0] == users[i].username) {
                        titles.push('<'+users[i].username+'>'+users[i].truename);
                        if (full) {
                            break;
                        }
                        if (names.length > 5) {
                            names.push('...');
                            full = true;
                            break;
                        }
                        names.push(users[i].truename);
                    }
                }
                for (var i = 0, gc = groups.length; i < gc; i++) {
                    if (typeof flowCc[0] == 'undefined' || -1 !== flowCc[0].indexOf('@')) {
                        continue ;
                    }

                    if (flowCc[0] == groups[i].groupid) {
                        titles.push(groups[i].groupname+'<'+TOP.TEXT.GROUP+'>');
                        if (full) {
                            break;
                        }
                        if (names.length > 5) {
                            names.push('...');
                            full = true;
                            break;
                        }
                        names.push(groups[i].groupname);
                    }
                }
            }

            if (names.length > 0 && titles.length) {
                $('.todo_content table td.cc-titles').attr('title', titles.join(','));
                $('#cc-names').text(names.join(','));
            } else {
                $('#cc-names').text('-');
            }
        });
    },

    /**
     * 保存基本信息
     */
    saveBasic: function() {
        var o = this,
            form = $('#flowform'),
            subject = $('input[name="subject"]').val(),
            description = $('input[name="description"]').val(),
            elapsedtime = $('input[name="elapsedtime"]').val(),
            avaliable = $('#avaliable').val(),
            board = this.boardSelect.getSelected(),
            cc = $('#cc').val(),
            content = o.editor.getSource();

        if (!description || description == $('input[name="description"]').attr('title')) {
            description = '-';
        }

        if (!elapsedtime || elapsedtime <= 0) {
            elapsedtime = '-';
        }

        if (!o.boardSelect.getValue()) {
            return TOP.showMessage(TOP.TEXT.BOARD_NOT_APPIONT);
        }

        if (!subject) {
            $('input[name="subject"]').focus();
            return TOP.showMessage('请输入工作流名称');
        }

        if (!avaliable) {
            return TOP.showMessage('请输入工作流可用人群');
        }
        if (!content) {
            return TOP.showMessage('请输入工作流内容');
        }

        var boardName = board.boardname;

        $('#flow-subject').text(subject);
        $('#flow-description').text(description);
        $('#flow-elapsedtime').text(elapsedtime);
        if (elapsedtime != '-') {
            $('#lang-elapsedtime').show();
        }
        $('#belong-board').text(boardName);
        o.setAvalible();
        o.setCc();
        o.showBasic(false);
        o.isChanged = true;
    },

    /**
     * 保存工作流
     */
    saveFlow: function(callback) {
        var form = $('#flowform'),
            o = this;

        if (!o.boardSelect.getValue()) {
            return TOP.showMessage(TOP.TEXT.BOARD_NOT_APPIONT);
        }

        if (!$('input[name="subject"]').val()) {
            $('input[name="subject"]').focus();
            return TOP.showMessage(TOP.TEXT.TUDU_SUBJECT_IS_NULL);
        }

        var desc = form.find('input[name="description"]').val(),
            elapsedtime = form.find('input[name="elapsedtime"]').val();

        if (desc == form.find('input[name="description"]').attr('title')) {
            form.find('input[name="description"]').val('');
        }

        if (elapsedtime == form.find('input[name="elapsedtime"]').attr('title')) {
            form.find('input[name="elapsedtime"]').val('');
        }

        // 处理内容HTML
        var src = o.editor.getSource();
        var reg = /<img([^>]+)src="([^"]+)"([^>]+)_aid="([^"]+)"([^>]+)\/>/ig;
        form.find(':hidden[name="file[]"]').remove();
        while ((result = reg.exec(src)) != null) {
            form.append('<input type="hidden" name="file[]" value="'+result[4]+'" />');
        }

        src = src.replace(reg, '<img$1src="AID:$4"$3_aid="$4"$5/>');
        src = src.replace(/\s+id="[^"]+"/g, '');

        $('#postcontent').val(src);

        var data = form.serializeArray();
        form.find(':input').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
                form.find(':input:not([_disabled])').attr('disabled', false);
                if (ret.success && ret.data) {
                    //location = '/flow/modify?flowid=' + ret.data.flowid;
                    if (typeof callback == 'function') {
                        callback.call(this);
                    } else {
                        location = '/flow/';
                    }
                }
            },
            error: function(res) {
                form.find(':input:not([_disabled])').attr('disabled', false);
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
            }
        });
    },

    /**
     * 初始化选择联系人窗口
     */
    initSelectLink: function(obj, mailInput, valInput, panels, containGroup, order) {
        var me = this;
        if (!containGroup) {
            containGroup = false;
        }

        if (!order) {
            order = false;
        }

        $(obj).click(function(){
            var instance = this;
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
                draggable: true,
                onShow: function() {
                    Win.center();
                },
                onClose: function() {
                    Win.destroy();
                }
            });

            var selector = new TOP.ContactSelector({appendTo: Win.find('div.pop_body'), enableGroup: containGroup, selected: selected, mailInput: mailInput, order: order, panels: panels});
            var panel = TOP.Cookie.get('CONTACT-PANEL');
            if (!panel) {
                panel = 'common';
            }
            selector.switchPanel(panel);

            Win.find('button[name="confirm"]').bind('click', function(){
                var se = selector.getSelected();

                if (order) {
                    mailInput.clear();
                }

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
    }
};