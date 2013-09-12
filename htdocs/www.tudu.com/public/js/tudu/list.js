/**
 * Tudu
 */
var Tudu = Tudu || {};

/**
 * 添加星标关注
 */
Tudu.star = function(tuduId, fun) {
	if (tuduId) {
		if(fun == 'star') {
			$('#star').addClass('attention');
		} else  if (fun == 'unstar') {
			$('#star').removeClass('attention');
		}
	}
	
	if (!tuduId) {
        tuduId = Tudu.List.getSelectId();
        for (var i = 0, c = tuduId.length; i < c; i++) {
	        if(fun == 'star') {
            	$('#tudu-' + tuduId[i]).find('a.icon_attention').addClass('attention');
            } else if (fun == 'unstar') {
            	$('#tudu-' + tuduId[i]).find('a.icon_attention').removeClass('attention');
            }
        }
	}
	
	if (!tuduId.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    $(':checkbox[name="checkall"]').attr('checked', false);
    TOP.checkBoxAll('tid[]', false, document.body);
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/star',
        data: {tid: tuduId, fun: fun},
        success: function(ret) {
        	if (ret.success && ret.data) {
                TOP.Label.setLabels(ret.data).refreshMenu();
            }
        },
        error: function(res) {
        	TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
};

Tudu.inbox = function(tuduId) {
	
	if (!tuduId) {
        tuduId = Tudu.List.getSelectId();
    }

    if (!tuduId.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    $('#checkall').attr('checked', false);
    TOP.checkBoxAll('tid[]', false, document.body);
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/inbox',
        data: {tid: tuduId},
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, 'success');
            
            if (ret.success) {
                location.reload();
            }
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
};

/**
 * 标记已读/未读
 */
Tudu.mark = function(tuduId, fun) {
	if (!tuduId) {
        tuduId = Tudu.List.getSelectId();
        for (var i = 0, c = tuduId.length; i < c; i++) {
            if (fun == 'read' || fun == 'unread') {
                var cls = {task: ['Rr', 'Rru'], notice: ['Rn', 'Rnu'], discuss: ['Rd', 'Rdu'], group:['Rrg', 'Rrgu'], meeting:['Rm', 'Rmu']};
                if ($('#tudu-' + tuduId[i]).hasClass('unread') == (fun == 'unread')) {
                    delete tuduId[i];
                    continue;
                }

                $('#tudu-' + tuduId[i]).toggleClass('unread');
                var type = $('#tudu-' + tuduId[i]).attr('_attr').split('|')[1];
                var ca = (fun == 'read' ? 0 : 1), cr = (fun == 'read' ? 1 : 0);
                $('#tudu-' + tuduId[i] + ' .g_i_c')
                .removeClass(cls[type][cr])
                .addClass(cls[type][ca]);
            }
        }
    }

    if (!tuduId.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    $(':checkbox[name="checkall"]').attr('checked', false);
    TOP.checkBoxAll('tid[]', false, document.body);
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/mark',
        data: {tid: tuduId, fun: fun},
        success: function(ret) {
            if (ret.success && ret.data) {
                TOP.Label.setLabels(ret.data).refreshMenu();
            }
        },
        error: function(res) {
        	TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
};

/**
 * 移除标签
 */
Tudu.markLabelRead = function(label) {
	$.ajax({
		type: 'POST',
		dataType: 'json',
		data: {label: label, fun: 'allread', read: 1},
		url: '/tudu-mgr/mark',
		success: function(ret) {
			TOP.showMessage(ret.message, 5000, 'success');
			//location.reload();
			location.assign(location.href);
		},
		error: function(res) {
		    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
		}
	});
};

/**
 * 移除标签
 */
Tudu.removeLabel = function(tuduId, label, labelid) {
	$.ajax({
        type: 'POST',
        dataType: 'json',
        data: {label: label, tid: tuduId, fun: 'del'},
        url: '/tudu-mgr/label',
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, 'success');
            
            $('#label-' + labelid).remove();
            $('#tudu-'+tuduId+'-label-' + labelid).remove();
            
            var obj = $('#tudu-' + tuduId);
                lbs = obj.attr('_labels').split('|'),
                lc  = 0,
                nlbs = [];
            for (var i = 0, c = lbs.length; i < c; i++) {
            	if (lbs[i] == labelid) {
            		continue ;
            	} else if (undefined !== Tudu.List._labels[lbs[i]]) {
            		nlbs.push(lbs[i]);
            		if (lc < 3) {
            			Tudu.List.appendLabel(obj, lbs[i]);
            		}
            		lc ++;
            	}
            }
            
            obj.attr('_labels', nlbs.join('|') + '|');

            if (nlbs.length <= 3) {
                obj.find('div.label_div a.list_label_indent').removeClass('list_label_more list_label_collspan');
            }
        },
        error: function(res){}
    });
};

/**
 * 移除所有标签
 */
Tudu.removeAll = function(tuduId){
	if (!tuduId) {
        tuduId = Tudu.List.getSelectId();
    }

    if (!tuduId.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }
    
    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {tid: tuduId, fun: 'remove'},
        url: '/tudu-mgr/label',
        success: function(ret) {
        	for (var i = 0, c = tuduId.length; i < c; i++) {
        		var o = $('#tudu' + tuduId[i]);
        		o.attr('_labels', '').find('div.label_div a.list_label_indent').removeClass('list_label_more').removeClass('list_label_collspan');
        	}
        	
        	TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
            location.assign(location.href);
        },
        error: function(res){}
    });
};

/**
 * 添加标签
 */
Tudu.addLabel = function(tuduId, label, callback) {
	$.ajax({
        type: 'POST',
        dataType: 'json',
        data: {label: label, tid: tuduId, fun: 'add'},
        url: '/tudu-mgr/label',
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
            
            if (typeof callback == 'function') {
            	callback.call(Tudu, ret);
            }
        },
        error: function(res){}
    });
};

/**
 * 批量确认图度
 */
Tudu.confirm = function() {
	tuduId = Tudu.List.getSelectId();
    if (!tuduId.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }
    tuduId = tuduId.join(',');
    
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/done',
        data: {tid: tuduId, isdone: 1},
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, ret.success ? 'success': '');
            location.assign(location.href);
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
},

/**
 * 拒绝图度
 */
Tudu.reject = function(tuduId) {
	if (!tuduId && Tudu.List) {
        tuduId = Tudu.List.getSelectId();
    }

    if (!tuduId || !tuduId.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    if (!confirm(TOP.TEXT.CONFIRM_REJECT_TUDU)) {
        return false;
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/reject',
        data: {tid: tuduId.join(',')},
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, ret.success ? 'success': '');
            //location.reload();
            location.assign(location.href);
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
};

/**
 * 接受图度
 */
Tudu.accept = function(tuduId) {
	if (!tuduId && Tudu.List) {
        tuduId = Tudu.List.getSelectId();
    }

    if (!tuduId || !tuduId.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    if (!confirm(TOP.TEXT.CONFIRM_ACCEPT_TUDU)) {
        return false;
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/accept',
        data: {tid: tuduId.join(',')},
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, ret.success ? 'success': '');
            //location.reload();
            location.assign(location.href);
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
};

/**
 * 删除草稿
 */
Tudu.discard = function(tuduId) {
	if (!tuduId && Tudu.List) {
		tuduId = Tudu.List.getSelectId();
    }

	if (!tuduId || !tuduId.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

	if (!confirm(TOP.TEXT.CONFIRM_TO_DELETE_DRAFTS)) {
        return false;
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/discard',
        data: {tid: tuduId},
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, 'success');
            //location.reload();
            location.assign(location.href);
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
};

Tudu.ignore = function(tuduId, type) {
	if (!tuduId && Tudu.List) {
		tuduId = Tudu.List.getSelectId();
    }

	if (!tuduId || !tuduId.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    $(':checkbox[name="checkall"]').attr('checked', false);
    TOP.checkBoxAll('tid[]', false, document.body);
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/ignore',
        data: {tid: tuduId, type: type},
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, ret.success ? 'success': '');

            if (ret.success) {
                //location.reload();
            	location.assign(location.href);
            }
        },
        error: function(res) {
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
	tpl: '<div class="pop pop_linkman"><form id="nodeform" method="post" action="/note/create"><input name="tid" value="" type="hidden" /><input name="format" value="1" type="hidden" /><div class="pop_header"><strong>'+TOP.TEXT.CREATE_NOTE+'</strong><a class="icon icon_close close"></a></div><div class="pop_body"><table cellspacing="0" cellpadding="0" border="0"><tr><td valign="top">'+TOP.TEXT.NOTE_CONTENT+'：</td><td><textarea name="content" style="height:90px; width:330px;"></textarea></td></table></div><div class="pop_footer"><button type="submit" name="confirm" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" class="btn close">'+TOP.TEXT.CANCEL+'</button></div></form></div>',
	
	/**
	 * 显示便签的模板
	 */
	tips: '<div class="float_remind"><div class="float_remind_body">'+TOP.TEXT.LOADDING_NOTE_CONTENT_TIPS+'</div></div>',
	
	/**
	 * 实例
	 */
	ele: null,
	isShow: null,
	timer: null,
	
	/**
	 *  初始化
	 */
	init: function(){},
	
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
	show: function(obj, tuduId) {
		var o = this;
		if (!tuduId.length) {
			return ;
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
		} else {
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
	hide: function() {
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
			error: function(res){}
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

/**
 * 图度列表相关功能
 */
Tudu.List = {
	
	/**
	 * 排序类型
	 */
	_sortType: null,
	
	/**
	 * 标签列表
	 */
	_labels: null,
	
	/**
	 * 名片
	 */
	_vcard: null,
	
	/**
	 * 便签
	 */
	_vnote: null,
	
	/**
	 * 模板
	 */
	_tpls: {
		'label': '<table id="label-tpl" cellspacing="0" cellpadding="0" class="flagbg"><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr><tr><td class="falg_line"></td><td class="tag_txt"></td><td class="tag_close">&nbsp;</td><td class="falg_line"></td></tr><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr></table>'
	},

	/**
	 * 初始化列表页
	 */
	init: function(labelalias) {
		var _this = this;
		this._vcard  = new Card();
		this._vnote  = new Tudu.execNote();
		this._labels = TOP.Label.getCustomerLabels('labelid');
		
		// 复选框全选
		$(':checkbox[name="checkall"]').click(function(){
			$(':checkbox[name="checkall"]').attr('checked', this.checked);
			TOP.checkBoxAll('tid[]', this.checked, document.body);
		});
		
		// 列表事件绑定
		$('div.grid_list_wrap').each(function(){
			var o = $(this);
			// 鼠标单击效果
			o.bind('click', function(e){
				var se = e.srcElement ? $(e.srcElement) : $(e.target);
				
				if (!se.closest('table.grid_list_2').size()) {
					return ;
				}
				
				var tb  = se.closest('table.grid_list_2'),
					tuduId = tb.attr('id').replace('tudu-', '');
				
				if (!tuduId) {
					return ;
				}
				// 点击星标关注
				if (se.is('a.icon_attention')) {
					isstar = se.hasClass('attention');

					se.toggleClass('attention');
					
					var func = isstar ? 'unstar' : 'star';
					return Tudu.star(tuduId, func);
					
				// 点击最后更新
				} else if (se.closest('td.lastupdate').size()) {
					location = '/tudu/view?tid=' + tuduId + '&page=last&back=' + encodeURIComponent(location.href);
				// 标签事件
				} else if (se.is('.tag_close')) {
					var l = se.closest('table.flagbg'),
						alias = l.attr('_alias'),
						labelid = l.attr('_labelid');
					Tudu.removeLabel(tuduId, alias, labelid);
				// 点击标签
				} else if (se.closest('table.flagbg').size()) {
					var alias = se.closest('table.flagbg').attr('_alias')
					location = '/tudu/?search=cat&cat=' + encodeURIComponent(alias);
				} else if (se.is('a.icon_tudu_note_add')) {
					return _this._vnote.create(tuduId, function(ret) {
						se.removeClass('icon_tudu_note_add').addClass('icon_tudu_note');
						se.removeAttr('title');
						se.attr('href', '/note');
						if (ret.data) {
							se.attr('_note', ret.data.content);
						}
					});
				}
			});
		});
		
		// 添加标签
		$("table.grid_list_2")
		.bind('mouseover', function(e){
			var se = e.srcElement ? e.srcElement : e.target,
				ose = $(se),
				tb  = ose.parents('table.grid_list_2:eq(0)');

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
			
			tb.addClass('over');
		})
		.bind('mouseout', function(e){
			var se = e.srcElement ? e.srcElement : e.target,
				ose = $(se),
				tb  = ose.parents('table.grid_list_2:eq(0)');

			TOP.stopEventBuddle(e);

			// 显示内容
			if (se.tagName.toLowerCase() == 'a' && ose.attr('_email')) {
				_this._vcard.hide();
			}
			
			// 显示便签内容
			if (ose.hasClass('icon_tudu_note')) {
				_this._vnote.hide();
			}
			
			tb.removeClass('over');
		})
		.each(function(){
			var o = $(this),
			    l = o.attr('_labels');

			if (!l) {
			    return ;
			}

			l = l.split('|');

			var lc = 0;
			for (var i = 0, c = l.length; i < c; i++) {
			    if (!l[i] || l[i].indexOf('^') != -1) {
				    continue;
			    }
			    
			    // 加上更多
			    if (lc >= 3) {
			    	Tudu.List.appendLabelMore($(this));
			    	break;
			    }

			    if (undefined !== Tudu.List._labels[l[i]]) {
			    	var lb = Tudu.List.appendLabel($(this), l[i]);
			    	lc ++;
			    }
			}
			
			if (!o.find('.list_label_more').size()) {
				o.find('div.label_div').append('<a href="javascript:void(0);" onclick="Tudu.List.expandLabels(\''+o.attr('id')+'\', this)" onclick="" class="list_label_indent"></a>');
			}
		});
		
		// 下拉菜单
		if (labelalias != 'draft') {
			this.initMarkMenu();
		}
		
		// 三个月未更新显示
		if (labelalias == 'inbox' || labelalias == 'todo') {
			$('a[name="show_outdate"]').click(function(){
				var parent = $(this).parent(),
					key = parent.attr('_type');
				parent.remove();
				$('#tudu-group-' + key).find('div.grid_list_group_ct table[_far="1"]').show();
				Tudu.List.calGroupCount();
			});
		}
		
		// 列表分组处理
		if (labelalias == 'todo' || labelalias == 'inbox' || labelalias == 'review') {
			Tudu.List.calGroupCount();
			
			$('div.grid_list_title h3').click(function(){
				var ct = $(this).parents('div.grid_list_group');
				var check = ct.find(':checkbox[name="tid[]"]:visible:checked').size() != ct.find(':checkbox[name="tid[]"]:visible').size();
	
				ct.find(':checkbox[name="tid[]"]:visible').attr('checked', check);
			});
			$('div.grid_list_title span.toggle_tudu').click(function(){
				var o = $(this),
				    wrap = o.parents('div.grid_list_group'),
				    expanded = o.hasClass('icon_elbow_minus');
	
				wrap.find('div.grid_list_group_ct').toggle();
				if (expanded) {
					o.removeClass('icon_elbow_minus').addClass('icon_elbow_plus');
				} else {
					o.removeClass('icon_elbow_plus').addClass('icon_elbow_minus');
				}
			});
		}
		
		// 列表下筛选
		$('a.select-type').click(function(){
			var type = this.name;

			switch (type) {
			case 'all':
				$(':checkbox[name="checkall"]').attr('checked', true);
			    TOP.checkBoxAll('tid[]', true, document.body);
			    break;
			case 'none':
				$(':checkbox[name="checkall"], :checkbox[name="tid[]"]').attr('checked', false);
				break;
			case 'read':
				$(':checkbox[name="checkall"], :checkbox[name="tid[]"]').attr('checked', false);
			    $('table.grid_list_2').not('.unread').find(':checkbox[name="tid[]"]:visible').attr('checked', true);
			    break;
			case 'unread':
				$(':checkbox[name="checkall"], :checkbox[name="tid[]"]').attr('checked', false);
			    $('table.grid_list_2.unread :checkbox[name="tid[]"]:visible').attr('checked', true);
			    break;
			}
		});
		
		// 我执行
		if (labelalias == 'todo') {
			$('button[name="percent"]').bind('click', function(){
				var tuduIds = Tudu.List.getSelectId();
				
				if (!tuduIds.length) {
			        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
			    }

				var valid = false;
			    for (var i in tuduIds) {
			        var item = $('#tudu-' + tuduIds[i]),
			            status = item.attr('_status'),
			            accepted = item.attr('_accepted');

			        if (status < 2 && accepted) {
			            valid = true;
			            break;
			        }
			    }

			    if (!valid) {
			    	return TOP.showMessage(TOP.TEXT.INVALID_BATCH_ITEMS);
			    }

				Tudu.ReplyWin.show({
					action: '/compose/reply',
					title: TOP.TEXT.PROGRESS,
					progress: true,
					forward: false,
					tids: tuduIds
				});
			});
			
			$('button[name="forward"]').bind('click', function(){
				var tuduIds = Tudu.List.getSelectId();
				//alert(tuduIds.length);exit();
				if (!tuduIds.length) {
			        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
			    }

				var valid = false;
			    for (var i in tuduIds) {
			        var item = $('#tudu-' + tuduIds[i]),
			            status = item.attr('_status'),
			            accepted = item.attr('_accepted');

			        if (status < 2) {
			            valid = true;
			            break;
			        }
			    }

			    if (!valid) {
			    	return TOP.showMessage(TOP.TEXT.INVALID_BATCH_ITEMS);
			    }

				Tudu.ReplyWin.show({
					action: '/compose-tudu/send',
					title: TOP.TEXT.FORWARD,
					progress: false,
					forward: true,
					tids: tuduIds
				});
			});
			
			$('button[name="accept"]').bind('click', function(){
				Tudu.accept();
			});
			
			$('button[name="reject"]').bind('click', function(){
				Tudu.reject();
			});
		}
		
		if (labelalias == 'review') {
			$('button[name="agree"]').bind('click', function(){
				var tuduIds = Tudu.List.getSelectId();
				if (!tuduIds.length) {
			        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
			    }
				Tudu.ReviewWin.show({
					title: TOP.TEXT.REVIEW_CONFIRM,
					tips: TOP.TEXT.CONFIRM_TO_REVIEW_AGREE,
					tids: Tudu.List.getSelectId(),
					agree: 1,
					batch: true
				});
			});
			
			$('button[name="disagree"]').bind('click', function(){
				var tuduIds = Tudu.List.getSelectId();
				if (!tuduIds.length) {
			        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
			    }
				
				//Tudu.review(null, '0');
				Tudu.ReviewWin.show({
					title: TOP.TEXT.REVIEW_CONFIRM,
					tips: TOP.TEXT.CONFIRM_TO_REVIEW_DISAGREE,
					tids: Tudu.List.getSelectId(),
					agree: 0,
					batch: true
				});
			});
		}
		
		if (labelalias == 'sent') {
			$('button[name="confirm"]').bind('click', function(){
				Tudu.confirm();
			});
		}
		
		// 按钮事件
		$('button[name="inbox"]').click(function(){
			Tudu.inbox();
		});
		// 删除草稿
		$('button[name="delete"]').click(function(){
			Tudu.discard();
		});
		// 忽略
		$('button[name="ignore"]').click(function(){
		    Tudu.ignore(null, labelalias != 'ignore' ? null : 'remove');
		});
	},
	
	/**
	 * 计算分组统计数
	 */
	calGroupCount: function() {
		$('div .grid_list_group').each(function(){
			var o = $(this);
			o.find('div.grid_list_title span.grid_list_count')
			.text(o.find('table.grid_list_2:visible').size());
		});
	},
	
	/**
	 * 设置标签列表
	 */
	setLabels: function(labels) {
		this._labels = labels;
		
		return this;
	},
	
	/**
	 * 设置排序类型
	 */
	setSortType: function(type) {
		this._sortType = type;
		
		return this;
	},
	
	/**
	 * 获取选中ID
	 */
	getSelectId: function() {
		var ret = [];
		$(':checkbox[name="tid[]"]:checked').each(function(){
			ret.push(this.value);
		});

		return ret;
	},
	
	/**
	 * 列表排序
	 */
	sort: function(url, type, asc) {
		if (type != this._sortType) {
			asc = (type == 6) ? 1 : 0;
		}
		location = '/tudu/' + url + '&sorttype=' + type + '&sortasc=' + asc;
	},
	
	/**
	 * 初始化下拉菜单
	 */
	initMarkMenu: function() {
		var markItems = [{
			body: TOP.TEXT.UNREAD,
		    event: {
		        click: function(){
		            Tudu.mark(null, 'unread');
		        }
		    }
		},
		{
		    body: TOP.TEXT.READ,
		    event: {
		        click: function(){
			        Tudu.mark(null, 'read');
		        }
		    }
		},
		{
		    name: 'sep',
		    'class': 'menu-step',
		    style: 'display:none'
		},
		{
		    body: TOP.TEXT.CANCEL_LABEL,
		    name: 'cancel-label',
		    style: 'display:none',
		    event: {
		        click: function(){
					Tudu.removeAll();
		        }
		    }
		},
		{
		    body: TOP.TEXT.CANCEL_STAR,
		    name: 'cancel-star',
		    style: 'display:none',
		    event: {
		        click: function(){
			        Tudu.star(null, 'unstar');
		        }
		    }
		},
		'-'];
		
		var labels = Tudu.List._labels;
		if (null != labels) {
			for (var labelid in labels) {
			    var sitem = {
			    	body: '<span class="menu-square" style="background:'+labels[labelid].bgcolor+'"></span>' + labels[labelid].labelname,
			    	labelid: labelid,
			    	name: labels[labelid].labelname,
			        event: {
			            click: function() {
			                var ids = Tudu.List.getSelectId(),
			                	alias = this.attr('name');
			                if (!ids.length) {
			        	        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
			        	    }
			                Tudu.addLabel(ids, alias, function(ret){
			                	TOP.checkBoxAll('tid[]', false, document.body);
			                	
			                	var labels = TOP.Label.getCustomerLabels('labelalias');
			                	
			                	if (undefined === labels[alias]) {
			                		return ;
			                	}
			                	
			                	var label = labels[alias];
			                	
			                	if (ret.success) {
			                		var unread = 0;
			                		for (var i = 0, c = ids.length; i < c; i++) {
			                			var tr = $('#tudu-' + ids[i]);
			                			var lbs = tr.attr('_labels');
			                			if (tr.hasClass('unread')) {
			                				unread ++;
			                			}
			                			var lbs = lbs.split('|');
			                			
			                			tr.attr('_labels', tr.attr('_labels') + '|' + label.labelid);
			                			
			                			var ct = tr.find('div.label_div'),
			                				ab = ct.find('a.list_label_indent');
			                			
			                			if (lbs.length <= 3 || ct.find('a.list_label_collspan').size()) {
			                				if (!ab.size()) {
				                				ct.append('<a href="javascript:void(0)" onclick="Tudu.List.expandLabels(\''+ids[i]+'\', this)" class="list_label_indent"></a>');
				                			}
			                				Tudu.List.appendLabel($('#tudu-' + ids[i]), label.labelid);
			                			} else {
			                				ct.find('a.list_label_indent').addClass('list_label_more');
			                			}
			                		}
			                		
			                		TOP.Label.updateLabel(alias, {
			                			unreadnum: label.unreadnum + unread,
			                			totalnum: label.totalnum + ids.length
			                		});
			                	}
			                });
			            }
			        }
			    };
			    markItems.push(sitem);
			}
			markItems.push('-');
		}
		
		markItems.push({
		    body: '<a class="icon icon_attention attention"></a>' + TOP.TEXT.MARK_STAR,
		    event: {
		        click: function(){
			        Tudu.star(null, 'star');
		        }
		    }
		});
		markItems.push('-');
		markItems.push({
		    body: TOP.TEXT.CREATE_LABEL,
		    event: {
		        click: function(){
					var ids = Tudu.List.getSelectId();
			        if (!ids.length) {
				        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
				    }
			        
					TOP.Label.create(function(ret){
						if (ret.success && ret.data) {
							Tudu.addLabel(ids, ret.data.name, function(ret){
								if (ret.success) {
									//location.reload();
									location.assign(location.href);
								}
							});
						}
					});
		        }
		    }
		});

		$('div.select-tabs-wrap').each(function(){
		    new $.Dropdown({
		        target: this,
		        items: markItems,
		        maxHeight: 300
		    });

		    $(this).click(function(){
			    var tuduIds = Tudu.List.getSelectId();
				if (tuduIds.length) {
					_fixMenuItems(tuduIds);
				} else {
					$('div.option-menu-wrap').find('[name="cancel-label"]').hide();
					$('div.option-menu-wrap').find('[name="cancel-star"]').hide();
					$('div.option-menu-wrap').find('[name="sep"]').hide();
				}
			});
		});
		
		// 修正菜单显示项目（根据选中的图度项目）
		function _fixMenuItems(tuduIds) {
			if (!tuduIds) {
				return ;
			}
			var label = 0, star = 0;
			for (var i in tuduIds) {
				o = $('#tudu-' + tuduIds[i]);
				if (o.find('table.flagbg').size()) {
					label ++;
				}
				if (o.find('a.icon_attention').hasClass('attention')) {
					star ++;
				}
			}
			if (!label) {
				$('div.option-menu-wrap').find('[name="cancel-label"]').hide();
			} else {
				$('div.option-menu-wrap').find('[name="cancel-label"]').show();
			}
			if (!star) {
				$('div.option-menu-wrap').find('[name="cancel-star"]').hide();
			} else {
				$('div.option-menu-wrap').find('[name="cancel-star"]').show();
			}
			if (label || star) {
				$('div.option-menu-wrap').find('[name="sep"]').show();
			} else {
				$('div.option-menu-wrap').find('[name="sep"]').hide();
			}
		}
	},

	appendLabelMore: function(obj) {
		var ct = obj.find('div.label_div');
		
		if (ct.size() > 0) {
			ct.append('<a href="javascript:void(0)" onclick="Tudu.List.expandLabels(\''+obj.attr('id')+'\', this)" class="list_label_indent list_label_more"></a>');
		}
	},

	/**
	 * 展开标签
	 */
	expandLabels: function(id, obj) {
		var b = $(obj);
		if (!b.hasClass('list_label_more') && !b.hasClass('list_label_collspan')) {
			return ;
		}
		
		if (b.hasClass('list_label_more')) {
			var o = $('#' + id),
		        l = o.attr('_labels');
	
			if (!l) {
			    return ;
			}
		
			l = l.split('|');
			for (var i = 0, c = l.length; i < c; i++) {
			    if (!l[i] || l[i].indexOf('^') != -1) {
				    continue;
			    }
			    var lb = Tudu.List.appendLabel(o, l[i]);
			}
			
			b.removeClass('list_label_more').addClass('list_label_collspan');
		} else {
			var o = $('#' + id).find('div.label_div');
			var c = 0;
			o.find('table.flagbg').each(function() {
				if (c >= 3) {
					$(this).remove();
				}
				c++;
			});
			
			b.removeClass('list_label_collspan').addClass('list_label_more');
		}
	},
	
	/**
	 * 添加标签
	 */
	appendLabel: function(obj, labelid, full) {
		if (this._labels[labelid] == undefined) return ;
	    if ($('#' + obj.attr('id') + '-label-' + labelid).size()) return ;

		var e     = $(this._tpls.label),
		    label = this._labels[labelid],
		    ct    = obj.find('div.label_div'),
	        tid   = obj.attr('id').replace('tudu-', ''),
	        tct   = e.find('.tag_txt');
		e
		.attr({'id': obj.attr('id') + '-label-' + labelid, 'title': label.labelname, '_alias': label.labelalias, '_labelid': labelid})
		.css({'background-color': label.bgcolor, 'color': '#fff'});
		
		var text = (label.labelname.length > 3 && !full) 
		         ? label.labelname.substr(0, 3) + '...'
		         : label.labelname;

		tct.text(text).attr('_title', label.labelname).attr('_text', text);
		e.find('.tag_close').hide();

		e
	    .mouseover(function(evt){
	        if (!e.timer) {
		        e.timer = setTimeout(function(){
		            e.find('.tag_close').show();
		            if (tct.attr('_text') != tct.attr('_title')) {
		            	tct.text(tct.attr('_title'));
		            }
		            clearTimeout(e.timer);
		        }, 500);
	        }
	    })
	    .mouseout(function(evt){
	        evt = window.event || evt;
	        var offset = e.offset(),
	            pageTop= document.documentElement ? document.documentElement.scrollTop : document.body.scrollTop,
	            evtTop = evt.clientY + pageTop;
	        var isOver = evt.clientX > offset.left && evt.clientX < offset.left + e.width()
	                      && evtTop > offset.top && evtTop < offset.top + e.height();

	        if (!isOver) {
	         	clearTimeout(e.timer);
	         	e.timer = null;
		        e.find('.tag_close').hide();
		        if (tct.attr('_text') != tct.attr('_title')) {
	            	tct.text(tct.attr('_text'));
	            }
	        }
	    })
		
		if (ct.find('a.list_label_indent').size()) {
	    	ct.find('a.list_label_indent').before(e);
	    } else {
	    	ct.append(e);
	    }
		
		return e;
	}
};

Tudu._editorCss = {};
Tudu.setEditorCss = function(css) {
    Tudu._editorCss = css;
};
    
Tudu.getEditorCss = function() {
    return Tudu._editorCss;
};


/**
 * 审批窗口
 * 
 */
Tudu.ReviewWin = {
	
	_win: null,
	
	_tpl: [
		'<form id="review-form" action="/compose-tudu/send" method="post" enctype="multipart/form-data">',
		'<div class="pop">',
		'<input type="hidden" name="labelid" value="" />',
		'<input type="hidden" name="review" value="1" />',
		'<input type="hidden" name="action" value="send" />',
		'<div class="pop_header"><strong></strong><a class="icon icon_close close"></a></div>',
		'<div class="pop_body" id="review-edit">',
		'<input type="hidden" name="tid" value="" />',
		'<table cellspacing="2" cellpadding="0" width="455">',
		'<tr>',
		'<td style="padding:4px 0;"><span id="review-hints" style="color: #000"></span><span class="gray">('+TOP.TEXT.REVIEW_SUGGEST+')</span></td>',
		'</tr>',
		'</table>',
		'<table cellspacing="2" cellpadding="0" width="455">',
		'<tr>',
		'<td><textarea name="content" id="review-content" cols="" rows="" style="width:520px;height:150px"></textarea></td>',
		'</tr>',
		'</table>',
		'</div>',
		'<div class="pop_body" id="review-queue-list" style="padding:1px;display:none">',
		'<table cellspacing="0" class="grid_thead" id="children-header">',
		'<tr>',
		'<td width="title_line" style="padding-left:4px;line-height:20px">'+TOP.TEXT.SUBJECT+'</td>',
		'<td width="60" align="right" class="title_line" style="padding-right:16px">'+TOP.TEXT.STATUS+'</td>',
		'</tr>',
		'</table>',
		'<div style="height:150px;overflow:auto;">',
		'<ul class="queue_list" id="review-preparelist">',
		'</ul>',
		'</div>',
		'</div>',
		'<div class="pop_footer" style="padding-left:5px">',
		'<div class="footer_left" id="review-detail"></div>',
		'<div class="footer_right" id="review-submit">',
		'<button name="confirm" type="submit" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" class="btn close">'+TOP.TEXT.CANCEL+'</button>',
		'</div>',
		'<div class="footer_right" id="review-retry" style="display:none">',
		'<button name="retry" type="submit" class="btn">'+TOP.TEXT.REPLY+'</button><button type="button" class="btn close">'+TOP.TEXT.CLOSE+'</button>',
		'</div>',
		'</div>',
		'</div>',
		'</form>'
	].join(''),
	
	/**
	 * 显示审批确认窗口
	 */
	show: function(params) {
		var Win = TOP.Frame.TempWindow, batch = null, editor = null;
		
		Win.append(this._tpl, {
		    id: 'review-win',
			width: 580,
			draggable: true,
		    onShow: function(){
	    	   if (null != editor) {
	        	   editor.focus();
	    	   }
		    },
		    onClose: function(){
	    	  editor.destroy();
	    	  Win.destroy();
	
	    	  if (batch && batch.getStatus() >= TOP.Tudu.Queue.Status.Complete) {
	    		  TOP.getFrameDoc().location.reload();
	    	  }
		    }
		});
		
		var queue = [];
		if (typeof(params) != 'undefined') {
		    for (var k in params) {
		        switch (k) {
	    	        case 'title':
	    		        Win.find('.pop_header strong').text(params[k]);
	    		        break;
	    	        case 'tips':
	    	        	Win.find('#review-hints').html(params[k]);
	    	        	break;
	    	        case 'next':
	    	        	if (params[k]) {
	    	        		Win.find('#review-detail').prepend('<button name="addreviewer" type="button" class="btn" style="line-height:normal;">'+TOP.TEXT.ADD_REVIEWER+'</button>');
	    	        		
	    	        		if (params.tids) {
		    	        		var tid = params.tids[0],
		    	        			content = '';
		    	        		
		    	        		Win.find('button[name="addreviewer"]').bind('click', function(){
		    	        			if (editor) {
			    	        			content = editor.getSource();
			    	        		} else {
			    	        			content = Win.find('#review-content').val();
			    	        		}
		    	        			
		    	        			var form = TOP.getJQ()('<form action="/tudu/view?tid='+tid+'&review=1&back='+currUrl+'" target="main" method="post"><textarea name="content">'+content+'</textarea></form>');
		    	        			
		    	        			Win.find('#review-form').after(form);
		    	        			
		    	        			form.submit();
		    	        			
		    	        			Win.close();
		    	        		});
	    	        		}
	    	        	}
	    	        	break;
	    	        case 'tids':
	        	        var list = Win.find('#review-preparelist');
	        	        if (params.batch) {
		        	        for (var i = 0, c = params[k].length; i < c; i++) {
		        	            var subject    = $('#tudu-' + params[k][i] + ' .g_in_table td.subject a').text(),
		        	                status     = $('#tudu-' + params[k][i]).attr('_status'),
		        	                isaccepted = $('#tudu-' + params[k][i]).attr('_accepted');
		    	                subject = subject.length > 15 ? subject.substring(0, 15) + '...' : subject;
		    	                if (status <= 2) {
		    	                   queue.push({'tid': params[k][i], 'ftid': params[k][i]});
		 	                	   list.append('<li style="padding-left:4px;" id="reviewitem-'+params[k][i]+'"><span class="column_right icon icon_loading" style="margin-right:16px">'+TOP.TEXT.WAIT+'</span><a href="/tudu/view?tid='+params[k][i]+'&back='+params.back+'" target="main">'+subject+'</a></li>');
		    	                } else {
		 	                	   list.append('<li style="padding-left:4px;color:#aaa" id="batchitem-'+params[k][i]+'"><span class="column_right" style="margin-right:16px">'+TOP.TEXT.WAIT+'</span><span>'+subject+'</span></li>');
		    	                }
		        	        }
		        	        list.find('a').bind('click', function(){return false;});
	        	        } else {
	        	        	queue.push({'tid': params[k][0], 'ftid': params[k][0]});
	        	        }
	        	        break;
		        }
		    }
		}
	
		var failures = [];
		batch = new TOP.Tudu.Queue({
			data: queue,
			async: true,
	        func: function(data, callback) {
				if (editor) {
					editor.disabled();
				} else {
					Win.find('#review-content').attr('disabled', 'disabled');
				}
			
	            $.ajax({
	                type: 'POST',
	                dataType: 'json',
	                data: data,
	                url: Win.find('#review-form').attr('action'),
	                success: function(ret) {
	            		if (!params.batch) {
	            			TOP.showMessage(ret.message, 5000, ret.success ? 'success': null);
	            			//location.reload();
	            			location.assign(location.href);
	            		} else {
	            	
		            	    Win.find('#reviewitem-' + data.tid + ' span.column_right')
		                    .removeClass('icon icon_loading red')
		                    .text(ret.success ? TOP.TEXT.SUCCESS : TOP.TEXT.FAILURE);
		
		                    if (!ret.success) {
		                    	Win.find('#reviewitem-' + data.tid + ' span.column_right').addClass('red');
		                    	Win.find('#reviewitem-' + data.tid).addClass('failure');
		                    	failures.push({'tid': data.tid, 'ftid': data.ftid});
		                    }
		                    
		                    Win.find('#reviewitem-' + data.tid).attr('title', ret.message);
		
		                    callback.call(this, data);
	            		}
	                },
	                error: function(res) {
	                	
	                	if (editor) {
	        				editor.enabled();
	        			} else {
	        				Win.find('#review-content').removeAttr('disabled');
	        			}
	                	
	                	Win.find('#reviewitem-' + data.tid + ' span.column_right').addClass('red');
	                	Win.find('#reviewitem-' + data.tid).addClass('failure').attr('title', TOP.TEXT.PROCESSING_ERROR);
	                    failures.push({'tid': data.tid, 'ftid': data.ftid});
	                    callback.call(this);
	                }
	            });
	        }
	    });
		batch.addEventListener('complete', function(){
			if (!Win.find('#review-preparelist li.failure').size()) {
				Win.find('button[name="retry"]').hide();
			}
	
			Win.find('button,input').removeAttr('disabled');
	
			if (failures.length) {
				var a = [];
				for (var i in failures) {
				    a.push(failures[i]);
				}
			    batch.setParam('queue', a);
			}
	
			Win.find('#review-preparelist a').unbind('click');
	        /*Win.find('#batch-preparelist a').bind('click', function(){
	        	getTop().getJQ()('#mainframe').attr('src', this.href);
	            getTop().Frame.TempWindow.close();
	            return false;
	        });*/
		});
		Win.find('button[name="confirm"]').bind('click', function(){
			if (params.batch) {
				Win.find('#review-submit').hide();
				Win.find('#review-retry').show();
		
				Win.find('#review-queue-list').show();
				Win.find('#review-edit').hide();
			}
		});
		Win.find('#review-form').submit(function(){return false;});
		Win.find('#review-form').submit(function(){

		    $('#review-preparelist a').click(function(){return false;});
	
		    Win.find('button[name="retry"]').show();
	
			failures = [];
	
			var content = Win.find('#review-content').val();
	
			if (!content && params.progress) {
				var percent = Win.find('#percent').val();
			    content = '<p>' + TOP.TEXT.PROGRESS + TOP.TEXT.CLN + percent + '</p>';
			}
	
    		batch.setParam('common', {
    		    'action': 'send',
    		    'agree': params.agree,
	            'review': 1,
	            'content': content
	        });
	
			Win.find('#review-preparelist li.failure span.column_right').addClass('icon icon_loading');
	
			Win.find('button,input').attr('disabled', 'disabled');
	
			batch.start();
			
			if (!params.batch) {
				Win.close();
			}
		});
	
		Win.show();
	
		// 编辑器，需要目标textarea可见时方可初始化
		editor = new TOP.UEditor('review-content', {initialFrameHeight: 150, zIndex: 9000}, TOP, TOP.getJQ(), function(){
			if (!this.hasContents() && typeof Tudu.getEditorCss().fontfamily != 'undefined' && typeof Tudu.getEditorCss().fontsize != 'undefined') {
				this.setContent('<p style="font-family:' + Tudu.getEditorCss().fontfamily + ';font-size:' + Tudu.getEditorCss().fontsize + '"></p>');
			}
		});
		
		editor.focus();
	}
};

/**
 * 回复窗口
 */
Tudu.ReplyWin = {
	
	/**
	 * @type {Object}
	 */	
	_win: null,
	
	/**
	 * @type {string}
	 */
	_tpl: [
		'<form id="batch-reply" action="" method="post" enctype="multipart/form-data">',
		'<div class="pop">',
		'<input type="hidden" name="labelid" value="" />',
		'<div class="pop_header"><strong></strong><a class="icon icon_close close"></a></div>',
		'<div class="pop_body" id="reply-edit">',
		'<input type="hidden" name="tid" value="" />',
		'<table cellspacing="2" cellpadding="0" width="455" id="forward-table">',
		'<tr>',
		'<td style="padding:4px 0;" width="60">'+TOP.TEXT.RECEIVER+TOP.TEXT.CLN,
		'<td style="padding:4px 0;"><input type="text" id="batch-to-text" name="batchto" value="" class="input_text" style="width:100%" /><input type="hidden" id="batch-to" name="to" value="" /></td>',
		'</tr>',
		'</table>',
		'<table cellspacing="2" cellpadding="0" width="455" id="progress-table">',
		'<tr>',
		'<td style="padding:4px 0"><span style="color:#000">'+TOP.TEXT.ECLPASE+'&nbsp;&nbsp;<input style="width:30px;" class="input_text" name="elapsedtime" id="elapsedtime" type="text">&nbsp;&nbsp;'+TOP.TEXT.HOUR+'</span>&nbsp;&nbsp;<span style="color:#000">'+TOP.TEXT.PROGRESS+'<input type="text" class="input_text" id="percent" name="percent" value="0%"  style="width:50px;" /></span><a href="javascript:void(0)" onclick="$(\'#percent\').val(\'100%\');" style="margin-left:10px">'+TOP.TEXT.SET_TUDU_COMPLETE+'</a></td>',
		'</tr>',
		'</table>',
		'<table cellspacing="2" cellpadding="0" width="455">',
		'<tr>',
		'<td><textarea name="content" id="replycontent" cols="" rows="" style="width:520px;height:150px"></textarea></td>',
		'</tr>',
		'</table>',
		'</div>',
		'<div class="pop_body" id="queue-list" style="padding:1px;display:none">',
		'<table cellspacing="0" class="grid_thead" id="children-header">',
		'<tr>',
		'<td width="title_line" style="padding-left:4px;line-height:20px">'+TOP.TEXT.SUBJECT+'</td>',
		'<td width="60" align="right" class="title_line" style="padding-right:16px">'+TOP.TEXT.STATUS+'</td>',
		'</tr>',
		'</table>',
		'<div style="height:150px;overflow:auto;">',
		'<ul class="queue_list" id="batch-preparelist">',
		'</ul>',
		'</div>',
		'</div>',
		'<div class="pop_footer">',
		'<div id="batch-submit">',
		'<button name="confirm" type="submit" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" class="btn close">'+TOP.TEXT.CANCEL+'</button>',
		'</div>',
		'<div id="batch-retry" style="display:none">',
		'<button name="retry" type="submit" class="btn">'+TOP.TEXT.REPLY+'</button><button type="button" class="btn close">'+TOP.TEXT.CLOSE+'</button>',
		'</div>',
		'</div>',
		'</div>',
		'</form>'
	].join(''),
	
	/**
	 * 显示批量回复对话框
	 */
	show: function(params) {
		var Win = TOP.Frame.TempWindow, batch = null, editor = null;
		
		Win.append(this._tpl, {
		    id: 'reply-win',
			width: 580,
			draggable: true,
		    onShow: function(){
	    	   if(params.progress) {
	    		   Win.find('#percent').stepper({step: 25, max:100, format: 'percent'});
	    	   }

	    	   if (null != editor) {
	        	   editor.focus();
	    	   }
		    },
		    onClose: function(){
	    	  editor.destroy();
	    	  Win.destroy();

	    	  if (batch && batch.getStatus() >= TOP.Tudu.Queue.Status.Complete) {
	    		  TOP.getFrameDoc().location.reload();
	    	  }
		    }
		});
		
		if (params.forward) {
			Win.find('#progress-table').hide();
			Win.find('#forward-table').show();
		} else {
			Win.find('#forward-table').hide();
			Win.find('#progress-table').show();
		}
		
		var queue = [];
		if (typeof(params) != 'undefined') {
		    for (var k in params) {
		        switch (k) {
	    	        case 'title':
	    		        Win.find('.pop_header strong').text(params[k]);
	    		        break;
	    	        case 'action':
	    	        	Win.find('#batch-reply').attr('action', params[k]);
	        	        break;
	    	        case 'tids':
	        	        var list = Win.find('#batch-preparelist');
	        	        for (var i = 0, c = params[k].length; i < c; i++) {
	        	            var subject    = $('#tudu-' + params[k][i] + ' .g_in_table td.subject a').text(),
	        	                status     = $('#tudu-' + params[k][i]).attr('_status'),
	        	                isaccepted = $('#tudu-' + params[k][i]).attr('_accepted');
	    	                subject = subject.length > 15 ? subject.substring(0, 15) + '...' : subject;
	    	                if ((params.forward && status < 2) || (params.progress && isaccepted && status <= 2)) {
	    	                   queue.push({'tid': params[k][i], 'ftid': params[k][i]});
	 	                	   list.append('<li style="padding-left:4px;" id="batchitem-'+params[k][i]+'"><span class="column_right icon icon_loading" style="margin-right:16px">'+TOP.TEXT.WAIT+'</span><a href="/tudu/view?tid='+params[k][i]+'&back='+params.back+'" target="main">'+subject+'</a></li>');
	    	                } else {
	 	                	   list.append('<li style="padding-left:4px;color:#aaa" id="batchitem-'+params[k][i]+'"><span class="column_right" style="margin-right:16px">'+TOP.TEXT.WAIT+'</span><span>'+subject+'</span></li>');
	    	                }
	        	        }
	        	        list.find('a').bind('click', function(){return false;});
	        	        break;
		        }
		    }
		}

		var failures = [];
		batch = new TOP.Tudu.Queue({
			data: queue,
			async: true,
	        func: function(data, callback) {
	            $.ajax({
	                type: 'POST',
	                dataType: 'json',
	                data: data,
	                url: Win.find('#batch-reply').attr('action'),
	                success: function(ret) {
	            	    Win.find('#batchitem-' + data.tid + ' span.column_right')
	                    .removeClass('icon icon_loading red')
	                    .text(ret.success ? TOP.TEXT.SUCCESS : TOP.TEXT.FAILURE);

	                    if (!ret.success) {
	                    	Win.find('#batchitem-' + data.tid + ' span.column_right').addClass('red');
	                    	Win.find('#batchitem-' + data.tid).addClass('failure');
	                    	failures.push({'tid': data.tid, 'ftid': data.ftid});
	                    }
	                    
	                    Win.find('#batchitem-' + data.tid).attr('title', ret.message)

	                    callback.call(this, data);
	                },
	                error: function(res) {
	                	Win.find('#batchitem-' + data.tid + ' span.column_right').addClass('red');
	                	Win.find('#batchitem-' + data.tid).addClass('failure').attr('title', TOP.TEXT.PROCESSING_ERROR);
	                    failures.push({'tid': data.tid, 'ftid': data.ftid});
	                    callback.call(this);
	                }
	            });
	        }
	    });
		batch.addEventListener('complete', function(){
			if (!Win.find('#batch-preparelist li.failure').size()) {
				Win.find('button[name="retry"]').hide();
			}

			Win.find('button,input').removeAttr('disabled');

			if (failures.length) {
				var a = [];
				for (var i in failures) {
				    a.push(failures[i]);
				}
			    batch.setParam('queue', a);
			}

			Win.find('#batch-preparelist a').unbind('click');
	        /*Win.find('#batch-preparelist a').bind('click', function(){
	        	getTop().getJQ()('#mainframe').attr('src', this.href);
	            getTop().Frame.TempWindow.close();
	            return false;
	        });*/
		});
		Win.find('#batch-reply button[name="confirm"]').bind('click', function(){
			if (params.forward) {
	            if (!Win.find('#batch-to').val()) {
	                TOP.showMessage('{{$LANG.tudu_params_invalid_to}}');
	                return false;
	            }
	        }

			if (params.progress) {
	            if (!Win.find('#elapsedtime').val()) {
	                Win.find('#elapsedtime').focus();
	                alert(TOP.TEXT.ELASPED_TIME_IS_NULL);
	                return false;
	            }

	            if (!Win.find('#percent').val()) {
	                Win.find('#percent').focus();
	                alert(TOP.TEXT.PERCENT_IS_NULL);
	                return false;
	            }
	        }

			Win.find('#batch-submit').hide();
			Win.find('#batch-retry').show();

			Win.find('#queue-list').show();
			Win.find('#reply-edit').hide();
		});
		Win.find('#batch-reply').submit(function(){return false;});
		Win.find('#batch-reply').submit(function(){

		    $('#batch-preparelist a').click(function(){return false;});

		    Win.find('button[name="retry"]').show();

			failures = [];

			var content = Win.find('#replycontent').val();

			if (!content && params.progress) {
				var percent = Win.find('#percent').val();
			    content = '<p>' + TOP.TEXT.PROGRESS + TOP.TEXT.CLN + percent + '</p>';
			}

			if (params.forward) {
				batch.setParam('common', {
	               'to' : Win.find('#batch-to').val(),
	               'forward': 1,
	               'issend':1,
	               'updateprogress': 0,
	               'action': 'send',
	               'type': 'task',
	               'content': content
	            });
			} else {
	    		batch.setParam('common', {
	               'elapsedtime' : Win.find('#elapsedtime').val(),
	               'percent': Win.find('#percent').val(),
	               'content': content,
	               'updateprogress': 1
	            });
			}

			Win.find('#batch-preparelist li.failure span.column_right').addClass('icon icon_loading');

			Win.find('button,input').attr('disabled', 'disabled');

			batch.start();
		});

		Win.show();

		// 编辑器，需要目标textarea可见时方可初始化
		editor = new TOP.UEditor('replycontent', {initialFrameHeight: 150, zIndex: 9000}, TOP, TOP.getJQ(), function(){
            if (!this.hasContents() && typeof Tudu.getEditorCss().fontfamily != 'undefined' && typeof Tudu.getEditorCss().fontsize != 'undefined') {
                this.setContent('<p style="font-family:' + Tudu.getEditorCss().fontfamily + ';font-size:' + Tudu.getEditorCss().fontsize + '"></p>');
            }
        });

		editor.focus();

		// 转发联系人输入框
	    if (params.forward) {
	    	 //initMailInput(Win.find('#batch-to'), Win.find('#batch-to-text'), editor);
	    	 
	    	 new TOP.ContactInput({
    	        id: 'batch-to-input', target: Win.find('#batch-to-text'), valuePlace: Win.find('#batch-to'), group: false, jq: TOP.getJQ(),
    	        onUpdate: function() {
	    		 	var to = this._settings.valuePlace.val().split("\n"), toArr = [];
	                for (var i = 0, c = to.length; i < c; i++) {
	                    var a = to[i].split(' ');
	                    if (a[1]) {
	                        toArr.push(a[1]);
	                    }
	                }

	                var source = editor.getSource();

	                var div = $('<div>');
	                div.html(source);

	                if (!toArr.length) {
	                    div.find('p[_name="forward"]').remove();
	                } else {
	                    var text = TOP.formatString(TOP.TEXT.FORWARD_INFO, TOP.getJQ()('#myname').text(), toArr.join(','));
	                    var html = '<strong>'+TOP.TEXT.FORWARD+TOP.TEXT.CLN+'</strong><span style="color:#aaa">'+text+'</span>';

	                    if (div.find('p[_name="forward"]').size()) {
	                        div.find('p[_name="forward"]').html(html);
	                    } else {
	                        div.prepend('<p _name="forward">'+html+'</p><br />');
	                    }
	                }

	                editor.setSource(div.html());
	    	 	}
    	     })
	    }
	}
};