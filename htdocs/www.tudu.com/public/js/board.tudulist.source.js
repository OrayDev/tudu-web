/**
 * 板块图度列表Js封装

 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: board.tudulist.source.js 2883 2013-06-04 02:56:03Z cutecube $
 */
var Board = Board || {};
var Tudu = Tudu || {};
var TOP = TOP || getTop();

/**
* 语言
*/
Board.Lang = {};
Board.setLang = function(lang){
	for (var i in lang) {
		Board.Lang[i] = lang[i];
	}
};

/**
 * 板块图度列表
 */
Board.tuduList = {
	currUrl: null,
	boardId: null,
	_vcard: null,
	_vnote: null,
	attention: 'add',
	
	/**
	 * 初始化
	 */
	init: function(){
		var _this = this;
		this._vcard = new Card();
		this._vnote = new Tudu.execNote();
		
		new FixToolbar({
			src: '#toolbar',
			target: '#float-toolbar'
		});
		
		$(".icon_fold").click(function(){
			$(this).toggleClass("icon_unfold");
			$(".fold_box").toggleClass("unfold_box")
		});
		
		$('input[name="checkall"]').click(function(){
			TOP.checkBoxAll('tid[]', this.checked, document.body);
		});
		
		$('button[name="delete"]').click(function(){
			Tudu.deleteTudu();
		});
		
		$('button[name="send"]').click(function(){
			location = '/tudu/modify?bid=' + _this.boardId;
		});
		
		$('#board-attention').bind('click', function(){
			Board.attentionBoard(_this.boardId, _this.attention);
		});
		
		$('button[name="move"]').bind('click', function(){
			var tuduIds = Tudu.getSelectId();
			
			if (tuduIds.length <= 0) {
				return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
			}
			
			Board.moveBoard(tuduIds.join(','), _this.boardId);
		});
		
		$("table.grid_list_2").mouseover(function(e){
			var se = e.srcElement ? e.srcElement : e.target, ose = $(se), tb = ose.parents('table.grid_list_2:eq(0)');
			
			TOP.stopEventBuddle(e);
			
			// 显示内容
			if (se.tagName.toLowerCase() == 'a' && ose.attr('_email')) {
				_this._vcard.show(se, 500);
			}
			
			// 显示便签内容
			if (ose.hasClass('icon_tudu_note')) {
				var tuduId = tb.attr('id').replace('tudu-', '');
				_this._vnote.show(se, tuduId);
			}
			$(this).addClass("over")
		}).mouseout(function(e){
			var se = e.srcElement ? e.srcElement : e.target, ose = $(se), tb = ose.parents('table.grid_list_2:eq(0)');
			
			TOP.stopEventBuddle(e);
			
			// 显示内容
			if (se.tagName.toLowerCase() == 'a' && ose.attr('_email')) {
				_this._vcard.hide();
			}
			
			// 显示便签内容
			if (ose.hasClass('icon_tudu_note')) {
				_this._vnote.hide();
			}
			$(this).removeClass("over")
		}).each(function(e){
			var o = $(this);
			var tuduId = o.attr('id').replace('tudu-', '');
			
			if (o.attr('privacy')) {
				return;
			}
			
			o.find('td.lastupdate').click(function(){
				location = '/tudu/view?tid=' + o.attr('id').replace('tudu-', '') + '&page=last&back=' + _this.currUrl;
			});
			
			o.find('a.icon_attention').bind('click', function(){
				var star = $(this), isstar = star.hasClass('attention');
				
				star.toggleClass('attention');
				
				var func = isstar ? 'unstar' : 'star';
				return Tudu.starTudu(tuduId, func);
			});
			
			o.find('a.icon_tudu_note_add').bind('click', function(e){
				var se = e.srcElement ? $(e.srcElement) : $(e.target);
				return _this._vnote.create(tuduId, function(ret){
					se.removeClass('icon_tudu_note_add').addClass('icon_tudu_note');
					se.removeAttr('title');
					se.attr('href', '/note');
					if (ret.data) {
						se.attr('_note', ret.data.content);
					}
				});
			});
		});
	}
};

Board.classes = {};
/**
 * 移动图度
 */
Board.moveBoard = function(tuduids, boardId){
	var Win = TOP.Frame.TempWindow;
	Win.append($('#move-tudu-win').html(), {
		id: 'move-tudu-win',
		width: 250,
		draggable: true,
		onClose: function(){
			Win.destroy();
		}
	});
	
	Win.find('#move-tudu').submit(function(){
		return false;
	});
	Win.find('#move-tudu').submit(function(){
		var form = $(this);
		
		if (Win.find('select[name="bid"] option:selected').attr('_classify') == '1' &&
		Win.find('select[name="cid"]').size() > 0 &&
		!Win.find('select[name="cid"]').val()) {
			Win.find('select[name="cid"]').focus();
			return TOP.showMessage(TOP.TEXT.BOARD_MUST_CLASSIFY);
		}
		
		var data = {
			'fbid': boardId,
			'bid': Win.find('select[name="bid"]').val(),
			'cid': Win.find('select[name="cid"]').val(),
			'tid': tuduids
		};
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: data,
			url: form.attr('action'),
			success: function(ret){
				TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
				
				if (ret.success) {
					Win.close();
					location.reload();
				}
			},
			error: function(res){
				TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
	});
	
	Win.find('select[name="bid"]').bind('change', function(){
		var bid = this.value, select = $(this), classify = select.find('option:selected').attr('_classify');
		if (undefined == Board.classes[bid]) {
			$.ajax({
				type: 'GET',
				dataType: 'json',
				url: '/tudu/classes?bid=' + bid,
				success: function(ret){
					if (ret.success) {
						Board.classes[bid] = ret.data;
						
						if (!classify && Board.classes[bid].length) {
							Board.classes[bid] = [{
								classid: '',
								classname: Board.Lang.notspecify
							}].concat(Board.classes[bid]);
						}
						
						_fillSelect(Board.classes[bid]);
					}
				},
				error: function(res){
					Win.find('select[name="cid"]').empty();
					Win.find('#toclass').hide();
					return;
				}
			});
		}
		else {
			_fillSelect(Board.classes[bid]);
		}
	});
	
	Win.show();
	
	function _fillSelect(ret){
		var o = Win.find('select[name="cid"]'), p = Win.find('#toclass');
		o.find('option:not(:eq(0))').remove();
		
		if (null === ret || !ret.length) {
			p.hide();
			return o.attr('disabled', true);
		}
		
		p.show();
		for (var i = 0, c = ret.length; i < c; i++) {
			o.append('<option value="' + ret[i].classid + '" title="' + ret[i].classname + '">' + ret[i].classname + '</option>');
		}
		
		o.attr('disabled', false);
	}
};

/**
 * 快捷板块
 *
 * @param {Object} boardId
 * @param {Object} type
 */
Board.attentionBoard = function(boardId, type){
	if (!boardId) {
		return;
	}
	
	if (type != 'remove') {
		type = 'add';
	}
	
	$.ajax({
		type: 'POST',
		dataType: 'json',
		data: {
			bid: boardId,
			type: type
		},
		url: '/board/attention',
		success: function(ret){
			TOP.showMessage(ret.message, 10000, 'success');
			
			if (!ret.success) {
				return;
			}
			
			var text = '', _$ = TOP.getJQ();
			
			if (type == 'add') {
				type = 'remove';
				var text = Board.Lang.removeattention;
				TOP.Frame.Boards.append(boardId, Board.Lang.boardname);
			}
			else {
				type = 'add';
				var text = Board.Lang.addattention;
				TOP.Frame.Boards.remove(boardId);
			}
			
			$('#board-attention').text(text).unbind('click').bind('click', function(){
				Board.attentionBoard(boardId, type)
			});
		},
		error: function(res){
			TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
		}
	});
};

/**
 * 删除板块
 *
 * @param {Object} boardId
 */
Board.deleteBoard = function(boardId){
	if (!confirm(TOP.TEXT.CONFIRM_DELETE_BOARD)) {
		return;
	}
	
	$.ajax({
		type: 'POST',
		dataType: 'json',
		data: {
			bid: boardId
		},
		url: '/board/delete',
		success: function(ret){
			TOP.showMessage(ret.message, 10000, 'success');
			if (ret.success) {
				TOP.getJQ()('#b_' + boardId).remove();
				location = '/board/';
			}
		},
		error: function(res){
			TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
		}
	});
};

/**
 * 关闭板块
 *
 * @param {Object} boardId
 * @param {Object} isClose
 */
Board.closeBoard = function(boardId, isClose){
	if (isClose) {
		if (!confirm(TOP.TEXT.CONFIRM_CLOSE_BOARD)) {
			return;
		}
	}
	
	$.ajax({
		type: 'POST',
		dataType: 'json',
		data: {
			bid: boardId,
			isclose: isClose ? 1 : 0
		},
		url: '/board/close',
		success: function(ret){
			TOP.showMessage(ret.message, 10000, 'success');
			if (ret.success) {
				location.reload();
			}
		},
		error: function(res){
			TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
		}
	});
};

/**
 * 清空板块（已屏蔽）
 *
 * @param {Object} boardId
 */
Board.clearBoard = function(boardId){
	if (!confirm(TOP.TEXT.CONFIRM_CLEAR_BOARD)) {
		return;
	}
	
	$.ajax({
		type: 'POST',
		dataType: 'json',
		data: {
			bid: boardId
		},
		url: '/board/clear',
		success: function(ret){
			TOP.showMessage(ret.message, 10000, 'success');
			if (ret.success) {
				location.reload();
			}
		},
		error: function(res){
			TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
		}
	});
};

/**
 * 处理板块参与人名称
 */
Board.setGroups = function(){
	TOP.Cast.load(function(){
		var avaliable = $('#board-groups').val().split("\n"), users = TOP.Cast.get('users'), groups = TOP.Cast.get('groups'), names = [], titles = [], full = false;
		
		for (var j = 0, c = avaliable.length; j < c; j++) {
			for (var i = 0, ul = users.length; i < ul; i++) {
				if (typeof avaliable[j] == 'undefined' || -1 === avaliable[j].indexOf('@')) {
					continue;
				}
				
				if (avaliable[j] == users[i].username) {
					titles.push('<' + users[i].username + '>' + users[i].truename);
					if (full) {
						break;
					}
					if (names.length > 6) {
						names.push('...');
						full = true;
						break;
					}
					names.push(users[i].truename);
				}
			}
			for (var i = 0, gl = groups.length; i < gl; i++) {
				if (typeof avaliable[j] == 'undefined' || -1 !== avaliable[j].indexOf('@')) {
					continue;
				}
				
				if (avaliable[j] == groups[i].groupid) {
					titles.push(groups[i].groupname + '<' + TOP.TEXT.GROUP + '>');
					if (full) {
						break;
					}
					if (names.length > 6) {
						names.push('...');
						full = true;
						break;
					}
					names.push(groups[i].groupname);
				}
			}
		}
		
		if (names.length > 0 && titles.length) {
			$('#groups-name').attr('title', titles.join(','));
			$('#groups-name').text(names.join(','));
		}
		else {
			$('#groups-name').text('-');
		}
	});
};

/**
 * 获取选中ID
 */
Tudu.getSelectId = function(){
	var ret = [];
	$(':checkbox[name="tid[]"]:checked').each(function(){
		ret.push(this.value);
	});
	
	return ret;
};

/**
 * 星标关注
 *
 * @param {Object} tuduId
 * @param {Object} fun
 */
Tudu.starTudu = function(tuduId, fun){
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: '/tudu-mgr/star',
		data: {
			tid: tuduId,
			fun: fun
		},
		success: function(ret){
			if (ret.data && ret.data) {
				TOP.Label.setLabels(ret.data).refreshMenu();
			}
		},
		error: function(res){
			TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
		}
	});
};

/**
 * 删除图度
 * @param {Object} tuduId
 */
Tudu.deleteTudu = function(tuduId){
	if (!tuduId) {
		tuduId = Tudu.getSelectId();
	}
	
	if (!tuduId.length) {
		return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
	}
	
	if (!confirm(TOP.TEXT.CONFIRM_DELETE_TUDU)) {
		return;
	}
	
	$('#checkall').attr('checked', false);
	
	TOP.showMessage(TOP.TEXT.DELETING_TUDU, 0, 'success');
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: '/tudu-mgr/delete',
		data: {
			tid: tuduId
		},
		success: function(ret){
			TOP.showMessage(ret.message, 10000, 'success');
			
			if (ret.success) {
				location.reload();
			}
		},
		error: function(res){
			TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
		}
	});
};

/**
 * 图度列表便签调用封装
 */
Tudu.execNote = function(){
    this.init();
};
Tudu.execNote.prototype = {
	/**
	 * 新建便签窗口模板
	 */
	tpl: '<div class="pop pop_linkman"><form id="nodeform" method="post" action="/note/create"><input name="tid" value="" type="hidden" /><input name="format" value="1" type="hidden" /><div class="pop_header"><strong>' + TOP.TEXT.CREATE_NOTE + '</strong><a class="icon icon_close close"></a></div><div class="pop_body"><table cellspacing="0" cellpadding="0" border="0"><tr><td valign="top">' + TOP.TEXT.NOTE_CONTENT + '：</td><td><textarea name="content" style="height:90px; width:330px;"></textarea></td></table></div><div class="pop_footer"><button type="submit" name="confirm" class="btn">' + TOP.TEXT.CONFIRM + '</button><button type="button" class="btn close">' + TOP.TEXT.CANCEL + '</button></div></form></div>',
	
	/**
	 * 显示便签的模板
	 */
	tips: '<div class="float_remind"><div class="float_remind_body">' + TOP.TEXT.LOADDING_NOTE_CONTENT_TIPS + '</div></div>',
	
	/**
	 * 实例
	 */
	ele: null,
	isShow: null,
	timer: null,
	
	/**
	 *  初始化
	 */
	init: function(){
	},
	
	/**
	 * 显示的位置
	 *
	 * @param {Object} obj
	 */
	getPos: function(obj){
		var p = this.getAbsolutePosition(obj), left = p.x, top = p.y;
		
		var width = this.ele.width(), bodyWidth = $(window).width(), bodyHeight = $(window).height(), oH = $(obj).outerHeight(), mH = this.ele.height(), sTop = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop, pos = {
			left: left ? left : 0,
			top: top ? top + oH : 0
		};
		
		if (top + oH + mH - sTop > bodyHeight) {
			pos.top = pos.top - mH - oH;
		}
		pos.left = pos.left + 15;
		return pos;
	},
	
	/**
	 * 显示便签内容
	 *
	 * @param {Object} obj
	 * @param {Object} tuduId
	 */
	show: function(obj, tuduId){
		var o = this;
		if (!tuduId.length) {
			return;
		}
		
		if (null === this.ele) {
			this.ele = $(this.tips);
			this.ele.appendTo(document.body).hide();
		}
		
		var content = $(obj).attr('_note');
		if (content) {
			o.setContent(content);
			var pos = o.getPos(obj);
			this.ele.css({
				left: pos.left + 'px',
				top: pos.top + 'px'
			});
			this.ele.show();
			this.isShow = true;
		}
		else {
			o.timer = setTimeout(function(){
				o.ele.find('.float_remind_body').html(TOP.TEXT.LOADDING_NOTE_CONTENT_TIPS);
				var pos = o.getPos(obj);
				o.ele.css({
					left: pos.left + 'px',
					top: pos.top + 'px'
				});
				o.ele.show();
				o.isShow = true;
				clearTimeout(this.timer);
				
				o.getNote(tuduId, function(ret){
					if (ret.data) {
						$(obj).attr('_note', ret.data.content);
						o.setContent(ret.data.content);
					}
				});
			}, 300);
		}
	},
	
	/**
	 * 隐藏
	 */
	hide: function(){
		if (this.timer) {
			clearTimeout(this.timer);
			this.timer = null;
		}
		if (this.isShow) {
			this.ele.find('.float_remind_body').empty();
			this.ele.hide();
		}
		this.isShow = false;
	},
	
	/**
	 * 初始便签内容到tips
	 */
	setContent: function(content){
		this.ele.find('.float_remind_body').html(content);
	},
	
	/**
	 * 获取对象的绝对位置
	 *
	 * @param {Object} o
	 */
	getAbsolutePosition: function(o){
		var p = {
			x: o.offsetLeft,
			y: o.offsetTop
		};
		while (o = o.offsetParent) {
			p.x += o.offsetLeft;
			p.y += o.offsetTop;
		}
		
		return p;
	},
	
	/**
	 * 获取图度便签数据
	 *
	 * @param {Object} tuduId
	 * @param {Function} callback
	 */
	getNote: function(tuduId, callback){
		var _o = this;
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: '/note/get-note?limit=200&tid=' + tuduId,
			success: function(ret){
				var panel = _o.panel;
				if (ret.success && ret.data) {
					if (typeof callback == 'function') {
						callback.call(this, ret);
					}
				}
			},
			error: function(res){
			}
		});
	},
	
	/**
	 * 新建便签
	 *
	 * @param {Object} tuduId
	 */
	create: function(tuduId, callback){
		var Win = TOP.Frame.TempWindow;
		
		Win.append(this.tpl, {
			width: 450,
			draggable: true,
			onClose: function(){
				Win.destroy();
			}
		});
		
		var form = Win.find('#nodeform');
		form.find('input[name="tid"]').val(tuduId);
		form.submit(function(){
			return false;
		});
		form.submit(function(){
			var content = form.find('textarea[name="content"]').val();
			if (!content.length) {
				return TOP.showMessage(TOP.TEXT.INVALID_NOTE_CONTENT);
			}
			
			var data = form.serializeArray();
			form.find('textarea').attr('disabled', true);
			
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: data,
				url: form.attr('action'),
				success: function(ret){
					TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
					form.find('textarea').attr('disabled', false);
					if (ret.success) {
						Win.close();
						if (typeof callback == 'function') {
							callback.call(this, ret);
						}
					}
				},
				error: function(res){
					TOP.showMessage(TOP.TEXT.PROCESSING_ERROR, 5000);
					form.find('textarea').attr('disabled', false);
				}
			});
		});
		
		Win.show();
	}
};

if (typeof(getTop) != 'function') {
	function getTop(){
		return parent;
	}
}