/**
 * 通讯录Js封装

 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: contact.source.js 2883 2013-06-04 02:56:03Z cutecube $
 */
var TOP = TOP || getTop();
var Contact = Contact || {};

/**
 * 删除联系人
 * 个人通信录
 *
 * @param {Object} contactId
 * @param {Object} isSearch
 * @param {Object} back
 */
Contact.deleteContact = function(contactId, isSearch, back) {
    if (!confirm(TOP.TEXT.CONFIRM_DELETE_CONTACT)) {
        return false;
    }

    if (typeof isSearch == 'undefined') {
        isSearch = false;
    }

    if (isSearch) {
        var ctid = new Array();
        ctid = contactId.split(',');
        for (var i = 0; i < ctid.length; i++) {
            if (ctid[i].indexOf('@') > -1) {
                TOP.showMessage(TOP.TEXT.NOT_DELETE_SYSTEM_CONTACT);
                return false;
            }
        }
    }

    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '/contact/delete?ctid=' + contactId,
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
            TOP.Contact.clear();
            if (ret.success && typeof back != 'undefined') {
                location = back;
            } else {
                location = location;
            }
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
};

/**
 * 向联系组添加人员
 *
 * @param {Object} groupId
 */
Contact.addMember = function(groupId) {
    if (!key) {
        var key = Contact.getKey();
    }

    if (!key.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {gid: groupId, key: key, type: 'add'},
        url: '/contact/group',
        success: function(ret) {
           TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
           if (ret.success) {
               TOP.Contact.clear();
               location = '/contact/?type=contact&groupid=' + groupId;
           }
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
};

/**
 * 联系人移除群组
 *
 * @param {Object} groupId
 * @param {Object} contactId
 */
Contact.removeMember = function(groupId, contactId) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {gid: groupId, ctid: contactId, type: 'remove'},
        url: '/contact/group',
        success: function(ret) {
           TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
           TOP.Contact.clear();
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
};

/**
 * 获取选中ID
 */
Contact.getKey = function() {
    var key = [];
    $(':checkbox[name="addr[]"]:checked').each(function(){
        key.push(this.value);
    });

    return key;
};

/**
 * Talk聊天窗口
 *
 * @param {Object} email
 */
Contact.chat = function(email) {
    var T = getTop();
    T.TuduTalk.talk(email, function(){
        if (!$.browser.msie) {
            TOP.TuduTalk.openTalk('tdim://chat?jid=' + email);
            return ;
        }

        var T = getTop();
        var d = TOP.Frame.Dialog.show({
            title: T.TEXT.HINT,
            body: '<p>' + T.TEXT.TALK_HINT + '</p>',
            buttons: [
                {
                    text: T.TEXT.INSTALL_NOW,
                    cls: 'btn',
                    events: {click: function(){
                        window.open();
                    }}
                },
                {
                    text: T.TEXT.DONOT_INSTALL,
                    cls: 'btn',
                    events: {click: function(){
                        d.close();
                    }}
                }
            ]
        });
    });
};

/**
 * 搜索联系人
 *
 * @param {Object} keyword
 */
Contact.search = function(keyword) {
    if (!keyword) {
        return false;
    } else {
        location.href = '/contact/search?keyword=' + encodeURIComponent(keyword);
    }
};

/**
 * 组织架构树
 */
Contact.deptTree = null;

/**
 * 初始搜索页面
 */
Contact.initSearch = function(currUrl, params, forbid) {
    Contact.initDeptTree(params.deptid);

    var length = parseInt($("#contact-list tr").length);
    if (forbid.liststyle) {
        $('span.count').text(length);

        $("#contact-list tr").mousemove(function(){
            $(this).addClass("over");
        }).mouseout(function(){
            $(this).removeClass("over");
        }).each(function(){
            var o = $(this),
                groups = o.attr('_groups'),
                email = o.attr('_email');
            if (groups) {
                groups = groups.split('|');
                for (var i = 0, c = groups.length; i < c; i++) {
                    if (!groups[i] || groups[i].indexOf('^') != -1) {
                        continue;
                    }
                    Contact.appendGroup($(this), groups[i]);
                }
            }
            o.find('td:not(:eq(0))').click(function(){
                if (email !== undefined) {
                    location = '/contact/view?email=' + o.attr('_email') + '&back=' + currUrl;
                } else {
                    location = '/contact/view?ctid=' + o.attr('_ctid') + '&back=' + currUrl;
                }
            });
        });
    } else {
        $('span.count').text(length-1);
        $(".list_over tbody tr").mousemove(function(){
            $(this).removeClass();
            $(this).css("cursor", "text");
        });
    }

    $(document).ready(function() {
        $('#keyword').bind('keyup', function(event) {
            if (event.keyCode == "13") {
                var keyword = $('#keyword').val();
                Contact.search(keyword);
            }
        });
    });

    $('#dosearch').click(function(){
        var keyword = $('#keyword').val();
        Contact.search(keyword);
    });

    $('input[name="checkall"]').click(function(){
        TOP.checkBoxAll('addr[]', this.checked, document.body);

        var selectCount = $(':checkbox[name="addr[]"]:checked').size();
        $('button[name="tudu"]').attr('disabled', selectCount <= 0);
        $('button[name="delete"]').attr('disabled', selectCount <= 0);
    });
    $(':checkbox[name="addr[]"]').click(function(e){
        var selectCount = $(':checkbox[name="addr[]"]:checked').size();
        $('button[name="tudu"]').attr('disabled', selectCount <= 0);
        $('button[name="delete"]').attr('disabled', selectCount <= 0);
        TOP.stopEventBuddle(e);
    });
    // 发送图度
    $('button[name="tudu"]').click(function(){
        if (!$(':checkbox[name="addr[]"]:checked').size()) return ;
        var to = [];
        $(':checkbox[name="addr[]"]:checked').each(function(){
            to.push($(this).attr('_identify'));
        });
        location = '/tudu/modify/?to=' + to.join(encodeURIComponent("\n"));
    });

    // 删除联系人
    $('button[name="delete"]').click(function(){
        var ctid = Contact.getKey();
        if (!ctid.length) {
            return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
        }
        Contact.deleteContact(ctid.join(','), true, '/contact/?type=contact');
    });
    TOP.keyhint('#keyword', 'input_tips', true, document.body);

    $('button[name="create"]').click(function(){
        location = '/contact/modify?back=' + currUrl;
    });

    // 添加到组
    $('select[name="group"]').change(function(){
        if(this.value){
            Contact.addMember(this.value);
        }
    });

    Contact.ajustSize();

    $(window).bind('scroll', function(){
        var scrollTop = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop;
        scrollTop = scrollTop - $('.position').height() - 10;

        if (scrollTop > 0) {
            $('.float-toolbar').css({'position': 'relative', 'top': scrollTop + 'px'});
        } else {
            $('.float-toolbar').css('top', '0px');
        }
    });

    window.onresize = Contact.onResize;

    Contact.onResize();
};

/**
 * 初始通信录列表
 */
Contact.initList = function(currUrl, params, forbid) {
    Contact.initDeptTree(params.deptid);
    // 添加到组
    $('select[name="group"]').change(function(){
        if(this.value){
            var groupId=this.value;
            Contact.addMember(groupId);
        }
    });

    $(document).ready(function() {
        $('#keyword').bind('keyup', function(event) {
            if (event.keyCode == "13") {
                var keyword = $('#keyword').val();
                Contact.search(keyword);
            }
        });
    });

    $('#dosearch').click(function(){
        var keyword = $('#keyword').val();
        Contact.search(keyword);
    });

    TOP.keyhint('#keyword', 'input_tips', true, document.body);

    $('button[name="delete"]').click(function(){
        var ctid = Contact.getKey();
        if (!ctid.length) {
            return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
        }
        Contact.deleteContact(ctid.join(','), false, '/contact/?type=contact');
    });

    $('input[name="checkall"]').click(function(){
        TOP.checkBoxAll('addr[]', this.checked, document.body);

        var selectCount = $(':checkbox[name="addr[]"]:checked').size();
        $('button[name="tudu"]').attr('disabled', selectCount <= 0);
        $('button[name="delete"]').attr('disabled', selectCount <= 0);
    });
    $(':checkbox[name="addr[]"]').click(function(e){
        var selectCount = $(':checkbox[name="addr[]"]:checked').size();
        $('button[name="tudu"]').attr('disabled', selectCount <= 0);
        $('button[name="delete"]').attr('disabled', selectCount <= 0);
        TOP.stopEventBuddle(e);
    });

    $('button[name="tudu"]').click(function(){
        if (!$(':checkbox[name="addr[]"]:checked').size()) return ;
        var to = [];
        $(':checkbox[name="addr[]"]:checked').each(function(){
            to.push($(this).attr('_identify'));
        });
        location = '/tudu/modify/?to=' + to.join(encodeURIComponent("\n"));
    });

    $('button[name="create"]').click(function(){
        location = '/contact/modify?back=' + currUrl;
    });

    if (forbid.group) {
        $('button[name="delGroup"]').attr('disabled', false);
        $('button[name="editGroup"]').attr('disabled', false);

        var groupId = $('input[name="groupid"]').val();

        $('button[name="editGroup"]').click(function(){
            location = '/contact/group.modify?gid=' + groupId + '&back=' + currUrl;
        });
        $('button[name="delGroup"]').click(function(){
            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: {gid: groupId},
                url: '/contact/group.delete',
                success: function(ret) {
                   TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
                   if (ret.success) {
                        location = '/contact/?type=contact';
                   }
                },
                error: function(res) {
                    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
                }
            });
        });
    }

    if (forbid.liststyle) {
        $("#contact-list tr").each(function(){
            var o = $(this);

            o.mousemove(function(){
                o.addClass("over");
            }).mouseout(function(){
                o.removeClass("over");
            });

            o.find('td:not(:eq(0))').click(function(){
                if (params.type == 'contact') {
                   location = '/contact/view?ctid=' + o.attr('_ctid') + '&back=' + currUrl;
                } else {
                   location = '/contact/view?email=' + o.attr('_email') + '&back=' + currUrl;
                }
            });

            var groups = o.attr('_groups');
            if (!groups) {
                return ;
            }
            groups = groups.split('|');
            if (groups.length) {
                for (var i = 0, c = groups.length; i < c; i++) {
                    if (!groups[i] || groups[i].indexOf('^') != -1) {
                        continue;
                    }
                    Contact.appendGroup($(this), groups[i]);
                }
            }
        });
    } else {
        $(".list_over tbody tr").mousemove(function(){
            $(this).removeClass();
            $(this).css("cursor", "text");
        });
    }

    Contact.ajustSize();

    $(window).bind('scroll', function(){
        var scrollTop = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop;
        scrollTop = scrollTop - $('.position').height() - 10;

        if (scrollTop > 0) {
            $('.float-toolbar').css({'position': 'relative', 'top': scrollTop + 'px'});
        } else {
            $('.float-toolbar').css('top', '0px');
        }
    });

    window.onresize = Contact.onResize;

    Contact.onResize();
};

Contact.ajustSize =  function() {
    var height = Math.max(document.body.clientHeight - 30, $('.c_left').height());

    $('.contacts_box').css({minHeight: height - 10 + 'px'});
    if ($.browser.msie && $.browser.version < '7.0') {
        $('.contacts_box').css({height: height - 10 + 'px'});
    }
};
Contact.onResize = function() {
    var winWidth = $('table[class="gird_fix"] td:eq(0)').width();
    if(winWidth<=800){
        $('table[class="gird_fix"] td:eq(0)').css({width: '800px'});
        $('.position').css('width', '995px');
        $('.float-toolbar').css('width', '800px');
        $(".c_left").addClass("less_left");
        $(".c_right").addClass("less_right");
    } else{
        $('table[class="gird_fix"] td:eq(0)').css({width: '100%'});
        $('.position').css('width', '100%');
        $('.float-toolbar').css({'width': winWidth + 'px'});
        $(".c_left").removeClass("less_left");
        $(".c_right").removeClass("less_right");
    }
};

/**
 * 联系组
 *
 * @param {Object} obj
 * @param {Object} groupid
 */
Contact.appendGroup = function(obj, groupid) {
    if (_CUS_GROUPS[groupid] == undefined) {
        return
    }
    if ($('#' + obj.attr('id') + '-group-' + groupid).size()) return ;

    var e = $('#group-tpl').clone(),
        group = _CUS_GROUPS[groupid],
        ct = obj.find('div.label_div'),
        ctid = obj.attr('_ctid'),
        close = e.find('.tag_close');
    e
    .attr({'id': 'ct-' + obj.attr('_ctid') + '-g-' + groupid})
    .css({'background-color': group.bgcolor, 'color': '#fff'});

    e.find('.tag_txt').text(group.name);

    close
    .click(function(evt){
        e.remove();
        Contact.removeMember(groupid, ctid, '/contact/?type=contact')
        TOP.stopEventBuddle(evt);
    })
    .html('&nbsp;')
    .hide();

    e
    .mouseover(function(evt){
        if (!e.timer) {
            e.timer = setTimeout(function(){
                e.find('.tag_close').show();
                clearTimeout(e.timer);
            }, 500);
        }
    })
    .mouseout(function(evt){
        evt = window.event || evt;
        var offset = e.offset();
        var isOver = evt.clientX > offset.left && evt.clientX < offset.left + e.width()
                      && evt.clientY > offset.top && evt.clientY < offset.top + e.height();

        if (!isOver) {
            clearTimeout(e.timer);
            e.timer = null;
            e.find('.tag_close').hide();
        }
    })
    .click(function(evt){
        location = '/contact/?type=contact&groupid=' + encodeURIComponent(groupid);
        TOP.stopEventBuddle(evt);
    });

    ct.append(e);
};

/**
 * 初始化组织架构树
 *
 * @param {Object} deptId
 */
Contact.initDeptTree = function(deptId) {
    if (null == Contact.deptTree) {
        Contact.deptTree = new $.tree({
            id: 'dept-tree',
            idKey: 'id',
            idPrefix: 'dept-',
            cls: 'contact-dept-tree',
            template: '<a href="javascript:void(0);" title="{name}">{name}</a>'
        });
    }
    Contact.deptTree.appendTo('#dept-tree-ct');
    
    TOP.Cast.load(function(cast) {
        var depts  = TOP.Cast.get('depts'),
            parent = null,
            deptid = null;

        for (var i = 0, c = depts.length; i < c; i++) {
            var deptid = depts[i].deptid.replace('^', '_');
            if (depts[i].deptid == '^root') {
                depts[i].deptname = TOP._ORGNAME;
            }
            var node = new $.treenode({
                data: {
                    id: deptid,
                    name: depts[i].deptname
                },
                events: {
                    mouseover: function(e){$(this).find('.tree-node-el:eq(0)').addClass('tree-node-over');TOP.stopEventBuddle(e)},
                    mouseout: function(e){$(this).find('.tree-node-el:eq(0)').removeClass('tree-node-over');TOP.stopEventBuddle(e)},
                    click: function(e){
                        var key = this.id.replace('dept-', '');
                        if (key.indexOf('_') != -1) {
                            key = key.replace('_', '^');
                        }
                        location = '/contact/?deptid=' + key;
                        TOP.stopEventBuddle(e);
                    }
                }
            });

            if (depts[i].parentid) {
                if (depts[i].parentid.indexOf('^') != -1) {
                    depts[i].parentid = depts[i].parentid.replace('^', '_');
                }
                parent = Contact.deptTree.find(depts[i].parentid, true);

                if (parent) {
                    parent.appendChild(node);
                }
            } else {
                Contact.deptTree.appendNode(node);
            }
        }
        Contact.deptTree.find('_root', true).expand();

        if (typeof deptId != 'undefined' && null !== deptId) {
            deptId = deptId.replace('^', '_');
            var node = Contact.deptTree.find(deptId, true);
            if (node) {
                if (node.parent) {
                    node.parent.expand();
                }
                $('#dept-'+deptId+' .tree-node-el:eq(0)').addClass('tree-node-selected');
            }
        }
    });
};

/**
 * 初始联系人查看页
 */
Contact.initView = function(currCtid, currUrl, back) {
    $('button[name="back"]').click(function(){
        location = back;
    });
    
    if ($('button[name="delete"]').size()) {
        $('button[name="delete"]').click(function(){
            Contact.deleteContact(currCtid, false, '/contact/?type=contact');
        });
    }
    
    if ($('button[name="modify"]').size()) {
        $('button[name="modify"]').click(function(){
            location = '/contact/modify?ctid='+currCtid+'&back=' + currUrl;
        });
    }
    
    $('button[name="send"]').click(function(){
        var to = $(this).attr('_to');
        location = '/tudu/modify?to=' + to;
    });
};

if (typeof(getTop) != 'function') {
    function getTop(){
        return parent;
    }
}