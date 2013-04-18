<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.general}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/selectboard.control.js" type="text/javascript"></script>

</head>
<body>
    <div class="position">
      <p><strong class="title">{{$LANG.tudu_rule}}</strong></p>
</div>
    {{include file="setting^tab.tpl" tab="rule"}}
    <div class="tab-panel-body">
    <form id="ruleform" action="/rule/{{$action}}" method="post">
    <input name="ruleid" type="hidden" value="{{$rule.ruleid}}" />
            <div class="settingbox">
            <div class="setting_common">
                <div class="settingdiv">
                    <h3 class="setting_tit" style="display:inline;">{{$LANG.create_rule}}</h3><span class="gray">({{$LANG.rule_tips}})</span>
                    <div class="line_bold"></div>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:10px 0">
                      <tr>
                        <td align="right" width="150">{{$LANG.rule_switch}}{{$LANG.cln}}</td>
                        <td width="180"><label for="valid-on"><input type="radio" value="1" id="valid-on" name="isvalid"{{if !$rule || $rule.isvalid}} checked="checked"{{/if}} />{{$LANG.rule_on}}</label><label for="valid-off"><input type="radio" value="0" id="valid-off" name="isvalid"{{if $rule && !$rule.isvalid}} checked="checked"{{/if}} />{{$LANG.rule_off}}</label></td>
                        <td></td>
                      </tr>
                    </table>
                    <div class="line_bold" style="border-top-width:1px;"></div>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:10px 0">
                      <tr>
                        <td align="right" width="150" valign="top" rowspan="4">{{$LANG.condition_title}}{{$LANG.cln}}</td>
                        <td style="padding:0 15px">
                            <table border="0" cellspacing="0" cellpadding="0" style="margin:0" id="filter-list">
                                <tr>
                                    <td width="30" style="padding:4px 0">
                                        <input type="hidden" name="filters[]" value="0" />
                                        {{if $filters.from.filterid}}
                                        <input type="hidden" name="filterid-0" value="{{$filters.from.filterid}}" />
                                        {{/if}}
                                        <input type="hidden" name="what-0" value="from" />
                                        <input type="checkbox" value="1" name="isvalid-0"{{if $filters.from.isvalid}} checked="checked"{{/if}} />
                                    </td>
                                    <td style="padding:4px 2px" width="40" align="right"><a href="javascript:void(0)" id="link-from">{{$LANG.tudu_sender}}</a></td>
                                    <td style="padding:4px 2px" width="65"><select name="type-0">
                                    <option value="contain"{{if $filters.from.type == 'contain'}} selected="selected"{{/if}}>{{$LANG.rule_keyword.contain}}</option>
                                    <option value="exclusive"{{if $filters.from.type == 'exclusive'}} selected="selected"{{/if}}>{{$LANG.rule_keyword.exclusive}}</option>
                                    </select>
                                    </td>
                                    <td style="padding:4px 2px" width="200"><input type="text" class="input_text" value="{{$filters.from.valuestring}}" id="input-from" style="width:178px" /><input type="hidden" value="{{$filters.from.valuestring}}" name="value-0" id="from" /></td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0">
                                        <input type="hidden" name="filters[]" value="1" />
                                        {{if $filters.to.filterid}}
                                        <input type="hidden" name="filterid-1" value="{{$filters.to.filterid}}" />
                                        {{/if}}
                                        <input type="hidden" name="what-1" value="to" />
                                        <input type="checkbox" value="1" name="isvalid-1"{{if $filters.to.isvalid}} checked="checked"{{/if}} />
                                    </td>
                                    <td style="padding:4px 2px" align="right"><a href="javascript:void(0)" id="link-to">{{$LANG.tudu_accepter}}</a></td>
                                    <td style="padding:4px 2px"><select name="type-1">
                                    <option value="contain"{{if $filters.to.type == 'contain'}} selected="selected"{{/if}}>{{$LANG.rule_keyword.contain}}</option>
                                    <option value="exclusive"{{if $filters.to.type == 'exclusive'}} selected="selected"{{/if}}>{{$LANG.rule_keyword.exclusive}}</option>
                                    </select>
                                    </td>
                                    <td style="padding:4px 2px"><input type="text" class="input_text" value="{{$filters.to.valuestring}}" id="input-to" style="width:178px" /><input type="hidden" value="{{$filters.to.valuestring}}" name="value-1" id="to" /></td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0">
                                        <input type="hidden" name="filters[]" value="2" />
                                        {{if $filters.cc.filterid}}
                                        <input type="hidden" name="filterid-2" value="{{$filters.cc.filterid}}" />
                                        {{/if}}
                                        <input type="hidden" name="what-2" value="cc" />
                                        <input type="checkbox" value="1" name="isvalid-2"{{if $filters.cc.isvalid}} checked="checked"{{/if}} />
                                    </td>
                                    <td style="padding:4px 2px" align="right"><a href="javascript:void(0)" id="link-cc">{{$LANG.tudu_cc}}</a></td>
                                    <td style="padding:4px 2px"><select name="type-2">
                                    <option value="contain"{{if $filters.cc.type == 'contain'}} selected="selected"{{/if}}>{{$LANG.rule_keyword.contain}}</option>
                                    <option value="exclusive"{{if $filters.cc.type == 'exclusive'}} selected="selected"{{/if}}>{{$LANG.rule_keyword.exclusive}}</option>
                                    </select>
                                    </td>
                                    <td style="padding:4px 2px"><input type="text" class="input_text" value="{{$filters.cc.valuestring}}" id="input-cc" style="width:178px" /><input type="hidden" value="{{$filters.cc.valuestring}}" name="value-2" id="cc" /></td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0">
                                        <input type="hidden" name="filters[]" value="3" />
                                        {{if $filters.subject.filterid}}
                                        <input type="hidden" name="filterid-3" value="{{$filters.subject.filterid}}" />
                                        {{/if}}
                                        <input type="hidden" name="what-3" value="subject" />
                                        <input type="checkbox" value="1" name="isvalid-3"{{if $filters.subject.isvalid}} checked="checked"{{/if}} />
                                    </td>
                                    <td style="padding:4px 2px" align="right">{{$LANG.subject}}</td>
                                    <td style="padding:4px 2px"><select name="type-3">
                                    <option value="contain"{{if $filters.subject.type == 'contain'}} selected="selected"{{/if}}>{{$LANG.rule_keyword.contain}}</option>
                                    <option value="exclusive"{{if $filters.subject.type == 'exclusive'}} selected="selected"{{/if}}>{{$LANG.rule_keyword.exclusive}}</option>
                                    </select>
                                    </td>
                                    <td style="padding:4px 2px;"><input type="text" class="input_text" value="{{$filters.subject.valuestring}}" name="value-3" style="width:178px" /></td>
                                </tr>
                            </table>
                        </td>
                      </tr>
                    </table>

                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:10px 0">
                      <tr>
                        <td align="right" width="150" valign="top" rowspan="4">{{$LANG.then_title}}{{$LANG.cln}}</td>
                        <td style="padding:0 15px">
                            <table border="0" cellspacing="0" cellpadding="0" style="margin:0">
                                <tr>
                                    <td style="padding:4px 0">
                                    <label for="op-label"><input name="operation" type="radio" id="op-label" value="label"{{if (!$rule || $rule.operation == 'label')}} checked="checked"{{/if}} />&nbsp;{{$LANG.mark_label}}</label>
                                    <select name="value" id="labels">
                                        {{foreach name="label" from=$labels item=item}}
                                        {{if !$item.issystem}}
                                        <option value="{{$item.labelid}}"{{if $rule.value == $item.labelid && $rule.operation == 'label'}} selected="selected"{{/if}}>{{$item.labelalias}}</option>
                                        {{/if}}
                                        {{/foreach}}
                                    </select>
                                    </td>
                                </tr>
                                <tr>
                                   <td style="padding:4px 0"><label for="op-starred"><input name="operation" type="radio" id="op-starred" value="starred"{{if $rule.operation == 'starred'}} checked="checked"{{/if}} />&nbsp;{{$LANG.rule_keyword.starred}}</label></td>
                                </tr>
                                <tr>
                                   <td style="padding:4px 0"><label for="op-ignore"><input name="operation" type="radio" id="op-ignore" value="ignore"{{if $rule.operation == 'ignore'}} checked="checked"{{/if}} />&nbsp;{{$LANG.rule_keyword.ignore}}</label></td>
                                </tr>
                            </table>
                        </td>
                      </tr>
                    </table>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:10px 0">
                      <tr>
                        <td align="right" width="150" valign="top">{{$LANG.high_grade}}{{$LANG.cln}}</td>
                        <td>
                            <div id="more" style="cursor:pointer;width:70px;">
                                <span class="{{if !$rule.mailremind || !$rule.mailremind.isvalid}}icon_unfold{{else}}icon_fold{{/if}}"></span><a style="padding-left: 8px;" href="javascript:void(0)">{{$LANG.more_setting}}</a>
                            </div>
                        </td>
                      </tr>
                      <tbody id="more-rulesetting"{{if !$rule.mailremind || !$rule.mailremind.isvalid}} style="display:none;"{{/if}}>
                      <tr>
                        <td align="right" width="150" valign="top">{{$LANG.mail_remind_operation}}{{$LANG.cln}}</td>
                        <td>
                            <label for="email-remind"><input type="checkbox" id="email-remind" name="mailremind" value="1"{{if $rule.mailremind.isvalid}} checked="checked"{{/if}} /><span style="padding-left: 6px;">{{$LANG.mail_remind}}</span></label>
                        </td>
                      </tr>
                      <tr id="set-mailbox"{{if !$rule.mailremind.isvalid}} style="display:none;"{{/if}}>
                        <td align="right" width="150" valign="top">&nbsp;</td>
                        <td style="line-height:24px;">
                            <div>{{$LANG.mailbox}}{{$LANG.cln}}<input type="text" name="mailbox" value="{{$mailbox}}" class="input_text" style="width:180px;" /><span style="padding-left: 8px;" class="gray">{{$LANG.mail_remind_tips}}</span></div>
                            <div>{{$LANG.apply_board}}{{$LANG.cln}}</div>
                            <div id="board-selector"></div>
                        </td>
                      </tr>
                      </tbody>
                    </table>
                </div>
            </div>
            </div>
        <div class="toolbar_position">
        <div class="toolbar">
            <div style="height:24px;"><input class="btn" type="submit" value="{{$LANG.save}}" /><input class="btn" type="button" value="{{$LANG.cancel}}" onclick="location='/rule/'" /></div>
        </div>
        </div>
        </form>
    </div>
<script type="text/javascript">
<!--

/**
 * 选择联系人连接
 *
 * @return
 */
function initUserSelector(link, target, mailinput, group, maxCount) {
    group = undefined == group ? false : group;

    $(link).click(function(){
        var instance = this;
        var title = $(this).text();

        var val = $(target).val();
        var selected = {users: [], groups: []}, userid = null;
        if (val) {
            val = val.split("\n");
            for (var i = 0, c = val.length; i < c; i++) {
                if (val[i].indexOf('@') != -1) {
                    selected.users.push(val[i].split('@')[0]);
                } else {
                    selected.groups.push(val[i].split(' ')[0]);
                }
            }
        } else {
            selected = null;
        }

        TOP.Frame.CastSelector.show({
            mtitle: title,
            onConfirm: function(ret){
                var users = TOP.Cast.get('users'),
                    groups = TOP.Cast.get('groups'),
                    val = [], v = [];

                mailinput.removeAll();
                for (var i = 0, c = ret.users.length; i < c; i++) {
                    for (var j = 0, k = users.length; j < k; j++) {
                        if (users[j].userid == ret.users[i]) {
                            mailinput.appendItem(users[j].truename, {name: users[j].truename, _id: users[j].username, title: users[j].username});
                            break;
                        }
                    }
                }

                for (var i = 0, c = ret.groups.length; i < c; i++) {
                    for (var j = 0, k = groups.length; j < k; j++) {
                        if (groups[j].groupid == ret.groups[i]) {
                            mailinput.appendItem(groups[j].groupname, {name: groups[j].groupname, _id: groups[j].groupid, title: TOP.TEXT.GROUP + ':' + groups[j].name});
                            break;
                        }
                    }
                }
            },
            selected: selected,
            noGroup: !group,
            maxCount:maxCount
        });
    });
}

function initMailInput(id, target, valct, group, max) {
    var group = (undefined == group ? false : group),
        data = {users: TOP.Cast.get('users')},
        cols = {users: ['truename', 'username', 'pinyin']},
        tpls = {users: '{truename} <span class="gray">&lt;{username}&gt;</span>'};

    if (group) {
        data.groups = TOP.Cast.get('groups');
        cols.groups = ['groupname'];
        tpls.groups = '{groupname}  <span class="gray">&lt;' + TOP.TEXT.GROUP + '&gt;</span>';
    }

    var mi = new $.mailinput({
        id: id,
        target: $(target),
        width: '180px',
        autoComplete:{
            data: data,
            url: '/frame/cast',
            onLoaded: castLoaded,
            columns: cols,
            width: 200,
            template: tpls,
            onSelect: function(item){
                var data = item.data;
                mi.setText('');

                var name = data.truename ? data.truename : data.groupname;
                var id   = data.username ? data.username : data.groupid;
                var title= id.indexOf('@') != -1 ? id : TOP.TEXT.GROUP + ':' + id;

                mi.appendItem(name, {name: name, _id: id, title: title});
                mi.focus();
            },
            nogroup: !group
        },
        maxCount: max,
        onAppend: function(item){
            var name  = item.text().replace(instance.separator, ''),
                identify = item.attr('_id');

            identify = identify ? identify.replace(/^[%|#]/, '') : '';

            var isValid = true,
                matched = false,
                user    = TOP.Cast.get('users'),
                group   = TOP.Cast.get('groups');

            var attr = {
                _id : identify,
                name: name,
                text: name ,
                title: name + (identify ? '<' + identify + '>' : '')
            };

            match:
            do {
                if (!identify || -1 !== identify.indexOf('@')) {
                    if (this.getItems('[name="'+name+'"][_id="'+identify+'"]').size() > 1) {
                        item.remove();
                        return ;
                    }

                    for (var i = 0, c = user.length; i < c; i++) {
                        if ((!identify && name == user[i].truename) || identify == user[i].username) {
                            matched = true;
                            break match;
                        }
                    }

                    if (!matched) {
                        if (-1 === identify.indexOf('@')) {
                            isValid = false;
                        }
                    }
                }

                if (identify && -1 === identify.indexOf('@')) {
                    for (var i = 0, c = group.length; i < c; i++) {
                        if (identify == group[i].groupid) {
                            matched = true;
                            break match;
                        }
                    }
                }
            } while (false);

            if (!matched) {
                isValid = false;
            }

            if (isValid) {
                updateInputVal.call(this, $(valct));
            } else {
                item.addClass('red');
                attr['name']  = '';
                attr['text']  = identify ? identify : name;
                attr['_id']   = '';
                attr['title'] = TOP.TEXT.INVALID_TO_USER;
            }
            delete attr.text;
            item.attr(attr);
        },
        onRemove: function(){updateInputVal.call(this, $(valct))}
    });

    var users = $(valct).val();
    if (users) {
        users = users.split("\n");
        for (var i = 0, c = users.length; i < c; i++) {
            var a = users[i].split(' ');

            if (!a[0] || !a[1]) {
                continue ;
            }

            if (mi.getItems('[name="'+a[1]+'"][_id="'+a[0]+'"]').size() > 1) {
                continue ;
            }

            mi.appendItem(a[1], {name: a[1], _id: a[0], title: a[0]});
        }
    }

    return mi;
}

/**
 * 更新联系人输入框数据
 *
 * @param valInput
 * @param textInput
 * @param list
 * @return
 */
function updateInputVal(valInput) {
    var list = this.getItems(''),
        vals  = [];
    list.each(function(){
        var o = $(this);
        vals.push(o.attr('_id') + ' ' + o.attr('name'));
    });

    valInput.val(vals.join("\n"));
}

function castLoaded(data) {
    TOP.Cast.set('users', data.data.users);
    TOP.Cast.set('depts', data.data.depts);
    TOP.Cast.set('groups', data.data.groups);

    this.data.users  = TOP.Cast.get('users');
    this.data.groups = TOP.Cast.get('groups');
}

/**
 *
 */
function affectRule(ruleId) {
    TOP.ajax({
        type: 'POST',
        dataType: 'json',
        data: {ruleid: ruleId},
        url: '/rule/affect',
        success: function(ret){
            TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
        },
        error: function(){}
    });
}

$(function(){
    TOP.Label.focusLabel();
    TOP.Frame.title('{{$LANG.mailbox_bind}}');
    TOP.Frame.hash('#m=/rule/modify{{if $rule.ruleid}}&ruleid={{$rule.ruleid}}{{/if}}');

    var miFROM = initMailInput('mi-from', '#input-from', '#from', null, 1);
    var miTO   = initMailInput('mi-to', '#input-to', '#to');
    var miCC   = initMailInput('mi-cc', '#input-cc', '#cc');

    initUserSelector('#link-from', '#from', miFROM, false, 1);
    initUserSelector('#link-to', '#to', miTO);
    initUserSelector('#link-cc', '#cc', miCC);

    if (!$('#labels option').size()) {
        $('#op-label').attr({'disabled': 'disabled', 'checked': false});
    }

    $("#more").bind('click', function(){
        var icon = $(this).find('span');
        $('#more-rulesetting').toggle();
        if (icon.hasClass('icon_unfold')) {
            icon.removeClass('icon_unfold').addClass('icon_fold');
        } else {
            icon.removeClass('icon_fold').addClass('icon_unfold');
        }
    });
    $('#email-remind').bind('click', function(){
        if ($(this).attr('checked')) {
            $('#set-mailbox').show();
        } else {
            $('#set-mailbox').hide();
        }
    });

    var selected = [];
    {{foreach item=boardid from=$rule.mailremind.boards}}
    selected.push(
        {boardid:'{{$boardid}}'}
    );
    {{/foreach}}

    new BoardSelector({appendTo: $('#board-selector'), selected: selected, sort: false});
    $('div.selector_left_title').text('{{$LANG.board_sum}}');
    $('div.selector_right_title').text('{{$LANG.already_add_remind_board}}');

    $('#ruleform').submit(function(){return false;});
    $('#ruleform').submit(function(){
        var form = $(this),
            isValid = true,
            validFilter = 0,
            enable = $('#valid-on:checked').size();
        $('#filter-list tr').each(function(){
            var o = $(this),
                idx = o.find(':hidden[name="filters[]"]').val();
            if (o.find(':checkbox[name="isvalid-' + idx + '"]:checked').size()) {
                var val = o.find('input[name="value-' + idx + '"]').val().replace(/\s+/ig, '');

                if (!val.length) {
                    TOP.showMessage(TOP.TEXT.RULE_FILTER_VALUE);
                    isValid = false;
                    return ;
                }

                validFilter++;
            }
        });

        if (!$(':radio[name="operation"]:checked').size()) {
            TOP.showMessage(TOP.TEXT.RULE_NEET_OPERATION);
            return ;
        }

        if (!isValid) {
            return ;
        }

        if (validFilter == 0) {
            TOP.showMessage(TOP.TEXT.RULE_NO_FILTER);
            return ;
        }

        var data = form.serializeArray();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: form.attr('action'),
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);

                if (ret.success && ret.data.ruleid) {
                    if (!enable) {
                        location = '/rule/';
                    } else {
                        var d = TOP.Frame.Dialog.show({
                            title: '{{$LANG.rule_affect_title}}',
                            body: '<div class="screen_lock"><div><span class="icon icon_attention_big"></span><strong>{{$LANG.rule_affect_tips}}</strong></div></div>',
                            buttons: [
                               {
                                   text: TOP.TEXT.YES,
                                   cls: 'btn',
                                   events: {click: function(){
                                       //TOP.showMessage('{{$LANG.wait_for_rule_affect}}', 'success');
                                       affectRule(ret.data.ruleid);
                                       d.close();
                                       location = '/rule/';
                                   }}
                               },
                               {
                                   text: TOP.TEXT.NO,
                                   cls: 'btn',
                                   events: {click: function(){d.close();location = '/rule/';}}
                               }
                            ]
                        });
                    }
                }
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
                return false;
            }
        });
    });
});
-->
</script>
</body>
</html>