/**
 * 讨论创建投票调查
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: vote.js 2484 2012-12-07 10:36:54Z cutecube $
 */
if (typeof(getTop) != 'function') {
    function getTop() {
        return parent;
    }
}

var TOP = TOP || getTop();

var Tudu = Tudu || {};

/**
 * 初始化创建投票调查
 */
Tudu.initVote = {
    /**
     * 投票选项索引
     */
    optionIndex: [],

    /**
     * 投票索引
     */
    voteIndex: 1,

    /**
     * 添加投票调查
     */
    addVote: function() {
        var v = $('#vote-list'),
            o  = $('#vote-tpl').clone(),
            id = this.voteIndex ++,
            _this  = this,
            count = v.find('div.vote_box').size();

        if (count >= 5) {
            TOP.showMessage(TOP.TEXT.TOO_MACH_VOTE);
            return false;
        }

        o.attr('id', 'vote-' + id).show();
        o.find('input[name="title"]').attr('name', 'title-' + id);
        o.find('input[name="maxchoices"]').attr('name', 'maxchoices-' + id).bind('keyup', function(){
            this.value = this.value.replace(/[^0-9]+/, '');
        })
        .blur(function(){
            $(this).val(this.value);
        });
        o.find('input[name="visible"]').attr('name', 'visible-' + id);
        o.find('input[name="privacy"]').attr('name', 'privacy-' + id).bind('click', function(){
            if ($(this).is(':checked')) {
                o.find('input[name="anonymous-' + id + '"]').attr('disabled', true).parent().hide();
            } else {
                o.find('input[name="anonymous-' + id + '"]').attr('disabled', false).parent().show();
            }
        });
        o.find('input[name="anonymous"]').attr('name', 'anonymous-' + id);
        o.find('input[name="isreset"]').attr('name', 'isreset-' + id);
        o.find('input[name="votemember[]"]').val(id);
        o.find('input[name="voteorder"]').attr('name', 'voteorder-' + id);
        o.find('#option-list').attr('id', 'option-list-' + id);

        var voteId = o.attr('id').replace('vote-', '');
        o.find('a.remove_vote').click(function(){
            _this.removeVote(voteId);
        });
        o.find('#option-add').attr('id', 'option-add-' + id).click(function(){
            _this.addOption(voteId);
        });

        v.append(o);
        _this.addOption(voteId, 0);
        _this.addOption(voteId, 0);
        this.updateVoteSort();
    },

    /**
     * 更新投票排序
     */
    updateVoteSort: function() {
        var sortId = 1;
            $('#vote-list div.vote_box').each(function() {
            $(this).find('span.vote-sort').text(sortId);
            $(this).find('input[name^="voteorder-"]').val(sortId);
            sortId ++;
        });
    },

    /**
     * 移除投票
     *
     * @param {Object} id
     */
    removeVote: function(voteId) {
        var count = $('#vote-list div.vote_box').size();

        if (count <= 1) {
            TOP.showMessage(TOP.TEXT.NOT_LEAST_ONE_VOTE);
            return false;
        }

        $('#vote-' + voteId).remove();
        this.updateVoteSort();
    },

    /**
     * 设置选项排序索引
     */
    setOptionIndex: function() {
        var _this = this;
        $('#vote-list div.vote_box').each(function() {
            var id = $(this).attr('id').replace('vote-', ''),
                size = $('#option-list-' + id + ' tr').size();
            _this.optionIndex[id] = size;
        });
    },

    /**
     * 添加投票选项
     */
    addOption: function(voteId, isNew) {
        var ct    = $('#option-list-' + voteId),
            count = ct.find('tr').size(),
            _this = this;

        if (count >= 20) {
            TOP.showMessage(TOP.TEXT.TOO_MUCH_OPTION);
            return false;
        }

        if (isNew) {
            var o = $('#new-option-tpl').clone(), id = 1;
        }
        else {
            var o = $('#option-tpl').clone(), id = 1;
        }

        if (typeof this.optionIndex[voteId] != 'undefined') {
            id = ++this.optionIndex[voteId];
        }
            
        o.attr('id', 'option-' + voteId + '-' + id);
        o.find('input[name="ordernum"]').attr('name', 'ordernum-' + voteId + '-' + id).val(id);
        o.find('input[name="text"]').attr('name', 'text-' + voteId + '-' + id);
        o.find('input[name="newoption[]"]').attr('name', 'newoption-' + voteId + '[]').val(id);

        var optionId = o.attr('id').replace('option-' + voteId + '-', '');
        o.find('a.remove_option').click(function(){
            _this.removeOption(voteId, optionId);
        });

        ct.append(o);
        this.optionIndex[voteId] = id;
    },

    /**
     * 移除投票选项
     */
    removeOption: function(voteId, optionId) {
        var ct = $('#option-list-' + voteId),
            count = ct.find('tr').size();

        if (count <= 2) {
            TOP.showMessage(TOP.TEXT.AT_LEAST_TWO_OPTION);
            return false;
        }

        $('#option-' + voteId + '-' + optionId).remove();
    },

    /**
     * 创建人显示投票参与人
     * 显示切换效果
     *
     * @param {Object} voteId
     */
    toggleAnonymous: function(voteId) {
        var isCkeck = $('input[name="privacy-' + voteId + '"]').is(':checked'),
            input   = $('input[name="anonymous-' + voteId + '"]');
            obj     = input.parent();

        if (isCkeck) {
            input.attr('disabled', true);
            obj.hide();
        } else {
            input.attr('disabled', false);
            obj.show();
        }
    }
};
