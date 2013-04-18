/**
 * 考勤应用 - 考勤分类
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: category.source.js 2769 2013-03-07 10:09:47Z chenyongfa $
 */
var Attend = Attend || {};

/**
 * 考勤分类
 */
Attend.Category = {
    index: 0,

    input: [],

    disabledBranch: false,

    _lang: {
        params_invalid_category_name: '请输入正确的考勤分类名称',
        params_invalid_flow_name: '请输入正确的审批流程名称',
        confirm_stop_category: '确认停用该考勤分类？',
        confirm_delete_category: '确认删除该考勤分类吗？',
        upper_review: '上级审批',
        uppers_review: '逐级审批',
        assign_reviewer: '指定审批人',
        params_invalid_flow_name: '请输入正确的审批流程名称',
        confirm_delete_review_flow: '确认删除该审批流程吗？',
        not_delete_step_null: '不能删除，至少需保留一个审批步骤'
    },

    stepTpl: ['<tr><td align="center">',
              '<div class="step_box_wrap">',
              '<div class="step_box">',
              '<div class="step_box_tool"><span class="icon icon_stepadd"></span>&nbsp;<span class="icon icon_stepprev"></span><span class="icon icon_stepnext"></span>&nbsp;<span class="icon icon_stepclose"></span></div>',
              '<div class="item">',
              '<a style="display:none;" href="javascript:void(0);" class="step_box_item_close"></a>',
              '<p style="display:none;"><input name="" value="1" disabled="disabled" type="text" class="input_text start" size="5" style="width:30px;"> - <input name="" disabled="disabled" type="text" class="input_text end" size="5" style="width:30px;"> '+Lang.days+'，'+Lang.execute+'</p>',
              '<p><select style="width:137px;"></select></p>',
              '<p class="null_users" style="display:none;">&nbsp;</p>',
              '<p style="display:none;"><a class="a_select">'+Lang.reviewer+'</a> <input name="" value="" type="text" readonly="readonly" class="input_text i_users" size="5" style="width:90px;"><input name="" value="" type="hidden" class="input_text users"></p>',
              '</div><div class="branch_last"></div>',
              '</div></div></td></tr>'
    ].join(''),

    branchTpl: ['<div class="step_box_item">',
                '<a href="javascript:void(0);" class="step_box_item_close"></a>',
                '<p><input name="" type="text" class="input_text start" size="5" style="width:30px;"> - <input name="" type="text" class="input_text end" size="5" style="width:30px;"> '+Lang.days+'，'+Lang.execute+'</p>',
                '<p><select style="width:137px;"></select></p>',
                '<p class="null_users">&nbsp;</p>',
                '<p style="display:none;"><a class="a_select">'+Lang.reviewer+'</a> <input name="" value="" type="text" class="input_text i_users" size="5" style="width:90px;"><input name="" value="" type="hidden" class="input_text users"></p>',
                '</div>'
    ].join(''),
    
    branchId: [],

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
     * 初始化列表
     */
    init: function() {
        $('#category-list tr').mousemove(function(){
            $(this).addClass("current");
        }).mouseout(function(){
            $(this).removeClass("current");
        });
    },

    /**
     * 编辑考勤分类
     */
    initModify: function(action, steps){
        var o = this;

        if (action == 'create') {
            o.index = -1;
            o.addStep();
        } else {
            o.initSteps(steps);
        }

        $('a[name="add-steps"]').bind('click', function(){
            o.addStep();
        });

        $('#theform').submit(function(){return false;});

        $('button[name="save"]').bind('click', function(){
            $('#theform').find('input').removeClass('border_red');
            $('#theform').find('div').removeClass('border_red');
            o.saveCategory('#theform');
        });
    },

    /**
     * 初始化步骤流程
     *
     * @param {Object} steps
     */
    initSteps: function(steps) {
        var o = this;

        o.index = steps.length - 1;
        for (var i = 0, c = steps.length; i < c; i++) {
            var id = steps[i].member,
                branches = steps[i].branches;
            if (typeof branches != 'undefined') {
                var item = $(['<tr><td align="center"><div class="step_box_wrap"><div class="step_box">',
                '<div class="step_box_tool"><span class="icon icon_stepadd"></span>&nbsp;<span class="icon icon_stepprev"></span><span class="icon icon_stepnext"></span>&nbsp;<span class="icon icon_stepclose"></span></div>',
                '<div class="branch_last"></div></div></div></td></tr>'].join(''));

                o.branchId[id] = branches.length - 1;
                for (var j = 0, l = branches.length; j < l; j++) {
                    var html = $(o.branchTpl),
                        key = branches[j].branch,
                        type = parseInt(branches[j].type),
                        option = '';

                    html.attr({'id': 'branch-' + id + '-' + key});
                    html.prepend('<input type="hidden" value="'+key+'" name="branch-'+id+'[]" />');
                    html.find('input.start').attr({'name': 'start-' + id + '-' + key, 'disabled': false}).val(branches[j].start);
                    html.find('input.end').attr({'name': 'end-' + id + '-' + key, 'disabled': false}).val(branches[j].end);
                    html.find('select').attr({'name': 'type-' + id + '-' + key, '_type': type});
                    html.find('input.users').attr({'name': 'users-' + id + '-' + key});
                    html.find('input.i_users').attr({'name': 'i-users-' + id + '-' + key});
                    html.find('.a_select').attr({'id': 'select-' + id + '-' + key});
                    if (branches[j].users != '^upper' && branches[j].users != '^uppers' && typeof branches[j].users != 'undefined') {
                        var users = branches[j].users;
                        users = users.split(',');
                        html.find('input.users').val(users.join("\n"));
                    }

                    if (type == 2) {
                        html.find('p:last').show();
                        html.find('p.null_users').hide();
                        option = '<option value="0">'+o._lang.upper_review+'</option><option value="1">'+o._lang.uppers_review+'</option><option value="2" selected="selected">'+o._lang.assign_reviewer+'</option>';
                    } else if (type == 1) {
                        option = '<option value="0">'+o._lang.upper_review+'</option><option value="1" selected="selected">'+o._lang.uppers_review+'</option><option value="2">'+o._lang.assign_reviewer+'</option>';
                        html.find('p.null_users').show();
                    } else {
                        option = '<option value="0" selected="selected">'+o._lang.upper_review+'</option><option value="1">'+o._lang.uppers_review+'</option><option value="2">'+o._lang.assign_reviewer+'</option>';
                        html.find('p.null_users').show();
                    }
                    html.find('select').html(option)
                    .bind('change', function(){
                        var stepIndex = $(this).closest('tr').attr('id').replace('step-', '');
                        var branchKey = $(this).closest('div').attr('id').replace('branch-'+ stepIndex+'-', '');
                        var t = $(this).val();
                        if (parseInt(t) == 2) {
                            $(this).closest('div').find('p:last').show();
                            $(this).closest('div').find('p.null_users').hide();
                        } else {
                            $(this).closest('div').find('p:last').hide();
                            $(this).closest('div').find('p.null_users').show();
                        }
                        if ($('#step-' + stepIndex).find('.step_box_item').size() <= 1) {
                            $(this).closest('div').find('p.null_users').hide();
                        }
                    });
                    html.find('.step_box_item_close').bind('click', function(){
                        var branch = $(this).closest('div').attr('id');
                            branchKey = branch.split('-')[2],
                            stepKey = branch.split('-')[1];

                        var branchSize = $('#step-' + stepKey).find('.step_box_item').size();
                        if (branchSize >= 2) {
                            $('#branch-' + stepKey + '-' + branchKey).remove();

                            if ($('#step-' + stepKey).find('.step_box_item').size() <= 1) {
                                $('#step-' + stepKey).find('input[name^="branch-"]').remove();
                                $('#step-' + stepKey).find('.mail_input').remove();
                                $('#step-' + stepKey).find('.step_box_item').css({'margin-right': '0px'}).removeAttr('id').children('a').hide();
                                $('#step-' + stepKey).find('.step_box_item select').attr({'name': 'type-' + stepKey}).bind('change', function(){
                                    var type = $('select[name="type-'+stepKey+'"] option:selected').val();
                                    if (parseInt(type) == 2) {
                                        $('#step-' + stepKey).find('p:last').show();
                                    } else {
                                        $('#step-' + stepKey).find('p:last').hide();
                                    }
                                });
                                $('#step-' + stepKey).find('.step_box_item').removeClass().addClass('item').children('p:eq(0)').hide();
                                $('#step-' + stepKey).find('input.start, input.end').attr({'disabled': true});
                                $('#step-' + stepKey).find('input.users').attr({'name': 'users-' + stepKey});
                                $('#step-' + stepKey).find('input.i_users').attr({'name': 'i-users-' + stepKey});
                                $('#step-' + stepKey).find('.a_select').attr({'id': 'select-' + stepKey});
                                // 联系人控件
                                var input = new TOP.ContactInput({id: 'review-input-'+stepKey, target: $('#step-' + stepKey).find('input.i_users'), valuePlace: $('#step-' + stepKey).find('input.users'), group: false, contact: false, review: true,jq: jQuery});
                                $('#review-input-'+stepKey).css({'display': 'inline-block', 'width': '90px', 'height': '16px', 'zoom': 1, '_display': 'inline'});
                                o.initSelectLink('#select-'+stepKey, input, $('#step-' + stepKey).find('input.users'), false, true);
                                if (parseInt($('select[name="type-'+stepKey+'"] option:selected').val()) != 2) {
                                    $('#step-' + stepKey).find('p.null_users').hide();
                                }

                                o.branchId[stepKey] = 0;
                            } else {
                                $('#step-' + stepKey).find('.step_box_item:last').css({'margin-right': '0px'});
                            }
                        }
                    });
                    html.insertBefore(item.find('.branch_last'));
                }
                item.find('.step_box_item:not(:last)').css({'margin-right': '18px'});
            } else {
                var item = $(o.stepTpl),
                    option = '';

                o.branchId[id] = 0;

                item.find('select').attr({'name': 'type-' + id, '_type': steps[i].type});
                item.find('input.users').attr({'name': 'users-' + id});
                item.find('input.i_users').attr({'name': 'i-users-' + id});
                item.find('.a_select').attr({'id': 'select-' + id});
                if (steps[i].users != '^upper' && steps[i].users != '^uppers' && typeof steps[i].users != 'undefined') {
                    var users = steps[i].users;
                    users = users.split(',');
                    item.find('input.users').val(users.join("\n"));
                }

                if (steps[i].type == 2) {
                    item.find('p:last').show();
                    option = '<option value="0">'+o._lang.upper_review+'</option><option value="1">'+o._lang.uppers_review+'</option><option value="2" selected="selected">'+o._lang.assign_reviewer+'</option>';
                } else if (steps[i].type == 1) {
                    option = '<option value="0">'+o._lang.upper_review+'</option><option value="1" selected="selected">'+o._lang.uppers_review+'</option><option value="2">'+o._lang.assign_reviewer+'</option>';
                } else {
                    option = '<option value="0" selected="selected">'+o._lang.upper_review+'</option><option value="1">'+o._lang.uppers_review+'</option><option value="2">'+o._lang.assign_reviewer+'</option>';
                }
                item.find('select').html(option).bind('change', function(){
                    var type = $(this).val();
                    if (parseInt(type) == 2) {
                        $(this).closest('div').find('p:last').show();
                    } else {
                        $(this).closest('div').find('p:last').hide();
                    }
                });
            }

            item.attr({'id': 'step-' + id});
            item.find('.step_box_wrap').prepend('<input type="hidden" value="'+id+'" name="member[]" /><input type="hidden" value="'+steps[i].id+'" name="id-'+id+'" /><input type="hidden" value="'+steps[i].order+'" name="order-'+id+'" />');
            // 添加步骤分支
            item.find('.step_box_tool .icon_stepadd').bind('click', function(){
                if (!o.disabledBranch) {
                    var stepIndex = $(this).closest('tr').attr('id').replace('step-', '');
                    o.addBranch($(this).closest('tr'), stepIndex);
                    o.refreshOrderNum();
                } else {
                    TOP.showMessage('补签审批流程不允许设置分支');
                }
            });

            // 步骤关闭
            item.find('.step_box_tool .icon_stepclose').bind('click', function(){
                var stepIndex = $(this).closest('tr').attr('id').replace('step-', '');
                o.removeStep(stepIndex);
                o.refreshOrderNum();
            });

            // 步骤上移
            item.find('.step_box_tool .icon_stepprev').bind('click', function(){
                var stepIndex = $(this).closest('tr').attr('id').replace('step-', '');
                o.sortStep('up', stepIndex);
                o.refreshOrderNum();
            });

            // 步骤下移
            item.find('.step_box_tool .icon_stepnext').bind('click', function(){
                var stepIndex = $(this).closest('tr').attr('id').replace('step-', '');
                o.sortStep('down', stepIndex);
                o.refreshOrderNum();
            });
            item.insertBefore('#step-add');

            // 处理联系人选择控件等
            item.find('a.a_select').each(function(){
                var attrId = $(this).attr('id'),
                    attrId = attrId.split('-'),
                    stepKey = attrId[1];

                if (attrId.length == 2) {
                    var input = new TOP.ContactInput({id: 'review-input-'+stepKey, target: item.find('input[name="i-users-'+stepKey+'"]'), valuePlace: item.find('input[name="users-'+stepKey+'"]'), group: false, contact: false, review: true,jq: jQuery});
                    $('#review-input-'+stepKey).css({'display': 'inline-block', 'width': '90px', 'height': '16px', 'zoom': 1, '_display': 'inline'});
                    o.initSelectLink($(this), input, item.find('input[name="users-'+stepKey+'"]'), false, true);
                } else if (attrId.length == 3) {
                    var branchKey = attrId[2];
                    var input = new TOP.ContactInput({id: 'review-input-'+stepKey+'-'+branchKey, target: item.find('input[name="i-users-'+stepKey+'-'+branchKey+'"]'), valuePlace: item.find('input[name="users-'+stepKey+'-'+branchKey+'"]'), group: false, contact: false, review: true,jq: jQuery});
                    $('#review-input-'+stepKey+'-'+branchKey).css({'display': 'inline-block', 'width': '90px', 'height': '16px', 'zoom': 1, '_display': 'inline'});
                    o.initSelectLink($(this), input, item.find('input[name="users-'+stepKey+'-'+branchKey+'"]'), false, true);
                }
            });

            var next = $('<tr><td align="center"><span class="icon icon_next"></span></td></tr>');
            next.attr({'id': 'next-' + id});
            next.insertBefore('#step-' + id);
        }
    },

    /**
     * 添加步骤
     */
    addStep: function() {
        var o = this,
            item = $(o.stepTpl),
            id = o.index + 1;

        o.branchId[id] = 0;

        item.attr({'id': 'step-' + id});
        item.find('.step_box select').attr({'name': 'type-' + id}).html('<option value="0" selected="selected">'+o._lang.upper_review+'</option><option value="1">'+o._lang.uppers_review+'</option><option value="2">'+o._lang.assign_reviewer+'</option>').bind('change', function(){
            var type = $('select[name="type-'+id+'"] option:selected').val();
            if (parseInt(type) == 2) {
                item.find('p:last').show();
            } else {
                item.find('p:last').hide();
            }
        });
        item.find('input.users').attr({'name': 'users-' + id});
        item.find('input.i_users').attr({'name': 'i-users-' + id});
        item.find('.a_select').attr({'id': 'select-' + id});
        item.find('.step_box_wrap').prepend('<input type="hidden" value="'+id+'" name="member[]" /><input type="hidden" value="'+id+'" name="order-'+id+'" />');
        // 删除步骤
        item.find('.step_box_tool .icon_stepclose').bind('click', function(){
            o.removeStep(id);
            o.refreshOrderNum();
        });

        // 添加步骤分支
        item.find('.step_box_tool .icon_stepadd').bind('click', function(){
            if (!o.disabledBranch) {
                o.addBranch(item, id);
                o.refreshOrderNum();
            } else {
                TOP.showMessage('补签审批流程不允许设置分支');
            }
        });

        // 步骤上移
        item.find('.step_box_tool .icon_stepprev').bind('click', function(){
            o.sortStep('up', id);
            o.refreshOrderNum();
        });

        // 步骤下移
        item.find('.step_box_tool .icon_stepnext').bind('click', function(){
            o.sortStep('down', id);
            o.refreshOrderNum();
        });
        item.insertBefore('#step-add');

        // 联系人控件
        o.input[id] = new TOP.ContactInput({id: 'review-input-'+id, target: item.find('input.i_users'), valuePlace: item.find('input.users'), group: false, contact: false, review: true,jq: jQuery});
        $('#review-input-'+id).css({'display': 'inline-block', 'width': '90px', 'height': '16px', 'zoom': 1, '_display': 'inline'});
        o.initSelectLink('#select-'+id, o.input[id], item.find('input.users'), false, true);
        o.index ++;

        var next = $('<tr><td align="center"><span class="icon icon_next"></span></td></tr>');
        next.attr({'id': 'next-' + id});
        next.insertBefore('#step-' + id);
    },

    /**
     * 添加分支
     *
     * @param {Object} id
     */
    addBranch: function(obj, id) {
        var o = this,
            branch = $(o.branchTpl),
            bid = o.branchId[id],
            key = bid + 1;

        if (bid == 0) {
            obj.find('.item')
            .removeClass('item')
            .addClass('step_box_item')
            .attr({'id': 'branch-' + id + '-' + bid})
            .children('a').show();

            obj.find('.step_box_item').prepend('<input type="hidden" value="'+bid+'" name="branch-'+id+'[]" />');
            obj.find('.step_box_item').children('p:eq(0)').show();
            obj.find('input.start').attr({'name': 'start-' + id + '-' + bid, 'disabled': false});
            obj.find('input.end').attr({'name': 'end-' + id + '-' + bid, 'disabled': false});

            obj.find('input.users').attr({'name': 'users-' + id + '-' + bid, 'disabled': false});
            obj.find('input.i_users').attr({'name': 'i-users-' + id + '-' + bid, 'disabled': false});
            obj.find('.a_select').attr({'id': 'select-' + id + '-' + bid});
            var input = new TOP.ContactInput({id: 'review-input-'+id + '-' + bid, target: obj.find('input.i_users'), valuePlace: obj.find('input.users'), group: false, contact: false, review: true,jq: jQuery});
            $('#review-input-'+id + '-' + bid).css({'display': 'inline-block', 'width': '90px', 'height': '16px', 'zoom': 1, '_display': 'inline'});
            o.initSelectLink('#select-'+id + '-' + bid, input, obj.find('input.users'), false, true);

            obj.find('select').attr({'name': 'type-' + id + '-' + bid}).bind('change', function(){
                var stepIndex = $(this).closest('tr').attr('id').replace('step-', '');
                var branchKey = $(this).closest('div').attr('id').replace('branch-'+ stepIndex+'-', '');
                var t = $(this).val();
                if (parseInt(t) == 2) {
                    $(this).closest('div').find('p:last').show();
                    $(this).closest('div').find('p.null_users').hide();
                } else {
                    $(this).closest('div').find('p:last').hide();
                    $(this).closest('div').find('p.null_users').show();
                }
            });

            if (parseInt($('select[name="type-' + id + '-' + bid + '"] option:selected').val()) != 2) {
                obj.find('p.null_users').show();
            } else {
                obj.find('p.null_users').hide();
            }

            obj.find('.step_box_item a.step_box_item_close').bind('click', function(){
                $('#branch-' + id + '-' + bid).remove();

                if ($('#step-' + id).find('.step_box_item').size() <= 1) {
                    $('#step-' + id).find('input[name^="branch-"]').remove();
                    $('#step-' + id).find('.mail_input').remove();
                    $('#step-' + id).find('.step_box_item').css({'margin-right': '0px'}).removeAttr('id').children('a').hide();
                    $('#step-' + id).find('.step_box_item select').attr({'name': 'type-' + id}).bind('change', function(){
                        var type = $('select[name="type-'+id+'"] option:selected').val();
                        if (parseInt(type) == 2) {
                            $('#step-' + id).find('p:last').show();
                        } else {
                            $('#step-' + id).find('p:last').hide();
                        }
                    });
                    $('#step-' + id).find('.step_box_item').removeClass().addClass('item').children('p:eq(0)').hide();
                    $('#step-' + id).find('input.start, input.end').attr({'disabled': true});
                    $('#step-' + id).find('input.users').attr({'name': 'users-' + id});
                    $('#step-' + id).find('input.i_users').attr({'name': 'i-users-' + id});
                    $('#step-' + id).find('.a_select').attr({'id': 'select-' + id});
                    // 联系人控件
                    var input = new TOP.ContactInput({id: 'review-input-'+id, target: $('#step-' + id).find('input.i_users'), valuePlace: $('#step-' + id).find('input.users'), group: false, contact: false, review: true,jq: jQuery});
                    $('#review-input-'+id).css({'display': 'inline-block', 'width': '90px', 'height': '16px', 'zoom': 1, '_display': 'inline'});
                    o.initSelectLink('#select-'+id, input, $('#step-' + id).find('input.users'), false, true);
                    if (parseInt($('select[name="type-'+id+'"] option:selected').val()) != 2) {
                        $('#step-' + id).find('p.null_users').hide();
                    }
                    o.branchId[id] = 0;
                }
            });
        }
        $('select[name="type-'+id+'"]').unbind('change');
        $('#select-'+id+'').unbind('click');
        $('#review-input-' + id).remove();

        branch.find('a.step_box_item_close').bind('click', function(){
            var branchSize = $('#step-' + id).find('.step_box_item').size();
            if (branchSize >= 2) {
                branch.remove();

                if ($('#step-' + id).find('.step_box_item').size() <= 1) {
                    $('#step-' + id).find('input[name^="branch-"]').remove();
                    $('#step-' + id).find('.mail_input').remove();
                    $('#step-' + id).find('.step_box_item').css({'margin-right': '0px'}).removeAttr('id').children('a').hide();
                    $('#step-' + id).find('.step_box_item select').attr({'name': 'type-' + id}).bind('change', function(){
                        var type = $('select[name="type-'+id+'"] option:selected').val();
                        if (parseInt(type) == 2) {
                            $('#step-' + id).find('p:last').show();
                        } else {
                            $('#step-' + id).find('p:last').hide();
                        }
                    });
                    $('#step-' + id).find('.step_box_item').removeClass().addClass('item').children('p:eq(0)').hide();
                    $('#step-' + id).find('input.start, input.end').attr({'disabled': true});
                    $('#step-' + id).find('input.users').attr({'name': 'users-' + id});
                    $('#step-' + id).find('input.i_users').attr({'name': 'i-users-' + id});
                    $('#step-' + id).find('.a_select').attr({'id': 'select-' + id});
                    // 联系人控件
                    var input = new TOP.ContactInput({id: 'review-input-'+id, target: $('#step-' + id).find('input.i_users'), valuePlace: $('#step-' + id).find('input.users'), group: false, contact: false, review: true,jq: jQuery});
                    $('#review-input-'+id).css({'display': 'inline-block', 'width': '90px', 'height': '16px', 'zoom': 1, '_display': 'inline'});
                    o.initSelectLink('#select-'+id, input, $('#step-' + id).find('input.users'), false, true);
                    if (parseInt($('select[name="type-'+id+'"] option:selected').val()) != 2) {
                        $('#step-' + id).find('p.null_users').hide();
                    }
 
                    o.branchId[id] = 0;
                } else {
                    $('#step-' + id).find('.step_box_item:last').css({'margin-right': '0px'});
                }
            }
        });

        branch.attr({'id': 'branch-' + id + '-' + key});
        branch.prepend('<input type="hidden" value="'+key+'" name="branch-'+id+'[]" />');
        branch.find('input.start').attr({'name': 'start-' + id + '-' + key});
        branch.find('input.end').attr({'name': 'end-' + id + '-' + key});
        branch.find('input.users').attr({'name': 'users-' + id + '-' + key});
        branch.find('input.i_users').attr({'name': 'i-users-' + id + '-' + key});
        branch.find('.a_select').attr({'id': 'select-' + id + '-' + key});
        branch.find('select').attr({'name': 'type-' + id + '-' + key}).html('<option value="0" selected="selected">'+o._lang.upper_review+'</option><option value="1">'+o._lang.uppers_review+'</option><option value="2">'+o._lang.assign_reviewer+'</option>')
        .bind('change', function(){
            var stepIndex = $(this).closest('tr').attr('id').replace('step-', '');
            var branchKey = $(this).closest('div').attr('id').replace('branch-'+ stepIndex+'-', '');
            var t = $(this).val();
            if (parseInt(t) == 2) {
                $(this).closest('div').find('p:last').show();
                $(this).closest('div').find('p.null_users').hide();
            } else {
                $(this).closest('div').find('p:last').hide();
                $(this).closest('div').find('p.null_users').show();
            }
        });
        branch.find('p.null_users').show();
        branch.insertBefore('#step-' + id + ' .branch_last');
        // 联系人控件
        var input = new TOP.ContactInput({id: 'review-input-'+id + '-' + key, target: branch.find('input.i_users'), valuePlace: branch.find('input.users'), group: false, contact: false, review: true,jq: jQuery});
        $('#review-input-'+id + '-' + key).css({'display': 'inline-block', 'width': '90px', 'height': '16px', 'zoom': 1, '_display': 'inline'});
        o.initSelectLink('#select-'+id + '-' + key, input, branch.find('input.users'), false, true);

        $('#branch-' + id + '-' + key).prev().css({'margin-right': '18px'});

        o.branchId[id]++;
    },

    /**
     * 删除步骤
     *
     * @param {Object} obj
     * @param {Object} id
     */
    removeStep: function(id) {
        var size = $('#step-list tr:not(#step-add)').size();
        if (size > 2) {
            $('#step-' + id).remove();
            $('#next-' + id).remove();
        } else {
            TOP.showMessage(this._lang.not_delete_step_null);
        }
    },

    /**
     * 步骤排序
     */
    sortStep: function(type, stepIndex) {
        var item = $('#step-' + stepIndex),
            next = $('#next-' + stepIndex);
        if (type == 'up') {
            item.insertBefore(item.prev().prev().prev());
            next.insertBefore(next.prev().prev().prev());
        } else if (type == 'down') {
            item.insertAfter(item.next().next());
            next.insertBefore(next.next().next().next());
        }
    },

    /**
     * 刷新步骤排序号
     */
    refreshOrderNum: function() {
        var target = $('.set_flow_step'),
            num = 0;
            
        target.find('input[name^="order-"]').each(function(){
            $(this).val(num);
            num++;
        });
    },

    /**
     * 初始选择联系人窗口
     *
     * @param {Object} obj
     * @param {Object} mailInput
     * @param {Object} valInput
     * @param {Object} containGroup
     * @param {Object} order
     */
    initSelectLink: function(obj, mailInput, valInput, containGroup, order) {
        var me = this;
        if (!containGroup) {
            containGroup = false;
        }

        if (!order) {
            order = false;
        }

        $(obj).click(function(){
            var instance = this;
            var title = $(this).text();

            var val = valInput.val();
            var selected = [], userid = null;
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

            var panels = ['lastcontact', 'common', 'contact'];
            if (order) {
                panels = ['common'];
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
                        p._id   = se[i].groupid
                        p.name  = se[i].name;
                    } else if(se[i].email) {
                        p.title = se[i].name + (se[i].email ? '$lt;' + se[i].email + '&gt;' : '');
                        p._id   = se[i].email ? se[i].email : '';
                        p.name  = se[i].name;
                    } else if (se[i].separator) {
                        p.separator = p._id = p.name = p.title = se[i].separator;
                    }

                    mailInput.addItem(p.name, p);
                }
                Win.close();
            });

            Win.show();
        });
    },

    /**
     * 保存考勤分类
     *
     * @param {Object} form
     */
    saveCategory: function(form) {
        var o = this,
            form = $(form),
            categoryName = form.find('input[name="categoryname"]').val().replace(/^\s+|\s+$/, '');

        if (!categoryName) {
            TOP.showMessage(o._lang.params_invalid_category_name);
            form.find('input[name="categoryname"]').focus();
            return false;
        }

        var i = 0;
        if (o.input.length > 0) {
            for (var key in o.input) {
                var user = o.input[key].getItems();
                user.each(function (){
                    if ($(this).attr('title') == TOP.TEXT.INVALID_TO_USER) {
                        $('#review-input-' + key).addClass('border_red');
                        i ++;
                    }
                });
            }
        }

        if (i > 0) {
            TOP.showMessage('表单红色部分，审批人用户输入格式有误');
            return false;
        }

        var data = form.serializeArray();
        form.find(':input:not([_disabled])').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
                form.find(':input:not([_disabled])').attr('disabled', false);
                if (ret.success) {
                    location = '/app/attend/category/index';
                } else {
                    if (ret.data) {
                        var targets = ret.data;
                        for (var i=0, c=targets.length; i<c; i++) {
                            if (targets[i].indexOf('users') != -1) {
                                var obj = targets[i].split('-'),
                                    stepKey = obj[1];

                                if (obj.length >= 3) {
                                    var branchKey = obj[2];
                                    $('#review-input-' + stepKey + '-' + branchKey).addClass('border_red');
                                } else {
                                    $('#review-input-' + stepKey).addClass('border_red');
                                }
                            } else {
                                $('input[name="' + targets[i] + '"]').addClass('border_red');
                            }
                        }
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
     * 删除考勤分类
     *
     * @param {Object} categoryId
     */
    deleteCategory: function(categoryId) {
        if (!confirm(this._lang.confirm_delete_category)) {
            return false;
        }

        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: '/app/attend/category/delete?categoryid=' + categoryId,
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
                if (ret.success) {
                    location = location;
                }
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
            }
        });
    },

    /**
     * 考勤分类停用、启用
     *
     * @param {Object} categoryId
     * @param {Object} type
     */
    updateStatus: function(categoryId, type) {
        if (type == 0) {
            if (!confirm(this._lang.confirm_stop_category)) {
                return false;
            }
        }

        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: '/app/attend/category/status?categoryid=' + categoryId + '&type=' + type,
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
                if (ret.success) {
                    location = location;
                }
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
            }
        });
    }
};