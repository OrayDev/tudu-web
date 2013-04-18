var Tudu = Tudu || {};

/**
 * 添加星标关注
 */
Tudu.star = function(tuduId, fun) {
	if (tuduId) {
		if (typeof fun !== 'string') {
			fun = $(fun).hasClass('attention') ? 'unstar' : 'star';
		}
		
		if(fun == 'star') {
			$('#star').addClass('attention');
		} else  if (fun == 'unstar') {
			$('#star').removeClass('attention');
		}
	}
	
	if (!tuduId && Tudu.List) {
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

/**
 * 标记已读/未读
 */
Tudu.mark = function(tuduId, fun) {
	if (!tuduId && Tudu.List) {
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
 * 标记所有已读
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
        },
        error: function(res){}
    });
};

/**
 * 移除所有标签
 */
Tudu.removeAll = function(tuduId){
	if (!tuduId) {
        tuduId = Tudu.getSelectId();
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

// 删除回复
Tudu.deletePost = function(tuduId, postId) {
    if (!confirm(TOP.TEXT.CONFIRM_DELETE_POST)) {
        return ;
    }
    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {tid: tuduId, pid: postId},
        url: '/tudu-mgr/delete-post',
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, 'success');
            if (ret.success) {
                location.reload();
            }
        },
        error: function(res){}
    });
};

// 移除附件
Tudu.removeAttach = function(aid) {
	$('#attach-' + aid).remove();
	if (!$('#attach-list div.filecell').size()) {
		$('#attach-list').hide();
	}
};

/**
 * 忽略图度
 */
Tudu.ignore = function(tuduId, type) {
	if (!tuduId && Tudu.List) {
		tuduId = Tudu.List.getSelectId();
    }

	if (!tuduId || !tuduId.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    $(':checkbox[name="checkall"]').attr('checked', false);
    $('button.btn').attr('disabled', 'disabled');
    TOP.checkBoxAll('tid[]', false, document.body);
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/ignore',
        data: {tid: tuduId, type: type},
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, ret.success ? 'success': '');
            $('button.btn').removeAttr('disabled');
            if (ret.success) {
                //location.reload();
            	location.assign(location.href);
            }
        },
        error: function(res) {
        	$('button.btn').removeAttr('disabled');
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
};

// 移动到图度箱 或 添加到图度箱
Tudu.inbox = function(tuduId){
    if (!tuduId && Tudu.List) {
        tuduId = Tudu.List.getSelectId();
    }

    if (!tuduId.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    $('#checkall').attr('checked', false);
    $('button.btn').attr('disabled', 'disabled');
    TOP.checkBoxAll('tid[]', false, document.body);
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/inbox',
        data: {tid: tuduId},
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, 'success');
            $('button.btn').removeAttr('disabled');
            if (ret.success) {
                location.assign(location.href);
            }
        },
        error: function(res) {
        	$('button.btn').removeAttr('disabled');
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
};

// 更新tudu状态   拒绝、取消、接受、确认、取消确认
Tudu.state = function(tuduId, act, params){
	var data = {tid: tuduId};

    if (!params) params = {};
    for (var k in params) {
        data[k] = params[k];
    }
	
    $('button.btn').attr('disabled', 'disabled');
    $.ajax({
        type: 'POST',
        data: data,
        dataType: 'json',
        url: '/tudu-mgr/' + act,
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, 'success');
            $('button.btn').removeAttr('disabled');
            if (ret.success) {
                location.assign(location.href);
            }
        },
        error: function(res) {
        	$('button.btn').removeAttr('disabled');
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
};

// 删除图度
Tudu.deleteTudu = function(tuduId, back){
	if (!confirm(TOP.TEXT.CONFIRM_DELETE_TUDU)) {
		return false;
	}

	$('button.btn').attr('disabled', 'disabled');
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: '/tudu-mgr/delete?tid=' + tuduId,
		success: function(ret) {
		   TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
		   $('button.btn').removeAttr('disabled');
		   if (ret.success) {
			    location = back;
		   }
		},
		error: function(res) {
			$('button.btn').removeAttr('disabled');
		    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
		}
	});
};

// 聊天
Tudu.chat = function(email) {
	var T = getTop();
	T.TuduTalk.talk(email, function(){
		if (!$.browser.msie) {
			TOP.TuduTalk.openTalk('tdim://chat?jid=' + email);
			return ;
		}
		
		var T = getTop();
		var d = T.dialog({
			title: T.TEXT.HINT,
			body: '<p>' + T.TEXT.TALK_HINT + '</p>',
			buttons: [
                {
                    text: T.TEXT.INSTALL_NOW,
                    cls: 'btn',
                    events: {click: function(){
                        window.open(TOP.TALK_URL);
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

Tudu.View = {
	
	/**
	 * 当前图度ID
	 */
	_tuduId: null,
	
	/**
	 * 权限
	 */
	_access: null,
	
	/**
	 * 名片
	 */
	_vcard: null,
	
	/**
	 * 标签
	 */
	_labels: null,
	
	/**
	 * 是否新窗口打开
	 */
	_isNewWin: false,
	
	/**
	 * 模板
	 */
	_tpls: {
		'label': '<table id="label-tpl" cellspacing="0" cellpadding="0" class="flagbg"><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr><tr><td class="falg_line"></td><td class="tag_txt"></td><td class="tag_close">&nbsp;</td><td class="falg_line"></td></tr><tr class="falg_rounded_wrap"><td class="falg_rounded"></td><td colspan="2"></td><td class="falg_rounded"></td></tr></table>',
		savetonetdisk : '<div class="pop pop_linkman"><form id="ndform" method="post" action="/netdisk/save-attach"><div class="pop_header"><strong>'+TOP.TEXT.SAVE_TO+'</strong><a class="icon icon_close close"></a></div><div class="pop_body" style="padding: 5px;"><input type="hidden" name="fileid" /><input type="hidden" name="folderid" /><p class="gray">'+TOP.TEXT.PLEASE_SAVE_TO+':</p><div class="netdisk_panel"></div></div><div class="pop_footer"><button type="submit" name="confirm" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" class="btn close">'+TOP.TEXT.CANCEL+'</button></div></form></div>'
	},
	
	_ndTree: null,
	
	/**
	 * 工作流ID
	 */
	flowId: null,
	
	/**
	 * 考勤申请ID
	 */
	applyId: null,
	
	sameReview: false,
	
	// 初始化显示页
	init: function(tuduId, back, currUrl) {
		if (!tuduId) {
			return ;
		}
		
		this._tuduId = tuduId;
	
		// 内容链接处理
		$('.todo_info a').attr('target', '_blank');
		// 返回
		$('button[name="back"]').click(function(){
	        location = back;
	    });
		// 关闭
		$('button[name="closewin"]').click(function(){
	        window.close();
	    });
		
		this.initMarkMenu();
		
		// 用户名片
		this._vcard = new Card();
		$('img.todo_send_icon, strong.poster').each(function(){
			$(this).bind('mouseover', function(){
				Tudu.View._vcard.show(this, 500);
			})
			.bind('mouseout', function(){
				Tudu.View._vcard.hide();
			});
	    });
		
		// 允许转发图度{
		$('button[name="forward"]').click(function(){
	        var url = '/tudu/view?tid=' + tuduId + '&forward=1&back=' + currUrl;
	        if (Tudu.View._isNewWin) {
				url += '&newwin=1';
			}
			location = url;
	    });
		
		// 允许分工图度
	    $('button[name="divide"]').click(function(){
	    	var moreAccepter = $(this).attr('_more');
	    	var url = '/tudu/view?tid=' + tuduId + '&isdivide=1&maccepter=' + moreAccepter + '&back=' + currUrl;
	    	if (Tudu.View._isNewWin) {
				url += '&newwin=1';
			}
			location = url;
	    });
		
		// 允许编辑修改图度
		$('button[name="modify"]').click(function(){
			var url = '/app/attend/apply/modify?tid=' + tuduId + '&back=' + currUrl;
			if (Tudu.View._isNewWin) {
				url += '&newwin=1';
			}
			location = url;
	    });
		
		// 会议邀请
		$('button[name="invite"]').click(function(){
			var url = '/tudu/view?tid=' + tuduId + '&invite=1&back=' + currUrl;
			if (Tudu.View._isNewWin) {
				url += '&newwin=1';
			}
			location = url;
		});

		// 申请审核
		$('button[name="apply"]').click(function(){
			var url = '/tudu/view?tid=' + tuduId + '&apply=1&back=' + currUrl;
			if (Tudu.View._isNewWin) {
				url += '&newwin=1';
			}
			location = url;
	    });
		// 同意申请
		$('button[name="agree"]').bind('click', function(){
			var type = $(this).attr('_type');

			Tudu.ReviewWin.show({
				title: TOP.TEXT.REVIEW_CONFIRM,
				tips: TOP.TEXT.CONFIRM_TO_REVIEW_AGREE,
				next: false,
				agree: 1,
				tids: [tuduId]
			});
		});
		
		// 不同意申请
		$('button[name="disagree"]').bind('click', function(){
			Tudu.ReviewWin.show({
				title: TOP.TEXT.REVIEW_CONFIRM,
				tips: TOP.TEXT.CONFIRM_TO_REVIEW_DISAGREE,
				agree: 0,
				tids: [tuduId]
			});
		});

	    // 外部链接管理
	    $('button[name="foreign"]').click(function(){
	        Tudu.Foreign.tuduId =  tuduId;
	        Tudu.Foreign.window();
	    });
	    
	    // 认领
	    $('button[name="claim"]').click(function(){
	    	Tudu.state(tuduId, this.name);
	    });
		
		// 允许删除图度{
		$('button[name="delete"]').click(function(){
			Tudu.deleteTudu(tuduId, back);
		});
		
		// 允许取消图度
	    $('button[name="cancel"]').click(function(){
		    if (!confirm('确认要取消该考勤申请？')) {
		        return ;
		    }
			
		    $.ajax({
		        type: 'POST',
		        dataType: 'json',
		        data: {applyid: Tudu.View.applyId},
		        url: '/app/attend/apply/cancel',
		        success: function(ret) {
		            TOP.showMessage(ret.message, null, ret.success ? 'success' : null);
		            if (ret.success) {
		                location.assign(location.href);
		            }
		        },
		        error: function(res) {
		        }
		    });
	    });
		
		// 接受图度任务
		$('button[name="accept"]').click(function(){
			Tudu.state(tuduId, this.name);
	    });
		
		// 允许拒绝图度
		$('button[name="reject"]').click(function(){
	    	if (!confirm(TOP.TEXT.CONFIRM_REJECT_TUDU)) {
	            return ;
	        }

	    	Tudu.state(tuduId, this.name);
	    });
		
		// 确认图度
		$('button[name="done"]').click(function(){
			Tudu.state(tuduId, this.name, {isdone: 1});
	    });
		
		// 取消确认图度
	    $('button[name="canceldone"]').click(function(){
	    	Tudu.state(tuduId, 'done', {isdone: 0});
	    });
	    
	    // 移动到图度箱
	    $('button[name="inbox"]').click(function(){
	    	Tudu.inbox(tuduId);
	    });
	    
	    $('button[name="ignore"]').click(function(){
	    	Tudu.ignore(tuduId);
	    });
	    
	    $('button[name="reopen"]').click(function(){
	    	var url = '/tudu/modify?tid=' + tuduId + '&reopen=1&back=' + currUrl;
			if (Tudu.View._isNewWin) {
				url += '&newwin=1';
			}
			location = url;
	        //Tudu.state(tuduId, 'close', {isclose: 0});
	    });
	    
		$('button[name="close"]').click(function(){
	        Tudu.state(tuduId, 'close', {isclose: 1});
	    });
	    
	    $('button[name="reset"]').click(function(){
	    	if (!confirm(TOP.TEXT.CONFIRM_RESET_VOTE)) {
				return false;
			}

			$.ajax({
				type: 'GET',
				dataType: 'json',
				url: '/tudu-mgr/reset.vote?tid=' + tuduId,
				success: function(ret) {
				   TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
				   if (ret.success) {
					    location.reload();
				   }
				},
				error: function(res) {
				    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
				}
			});
	    });
	    
	    // 投票
	    var voteform = $('#voteform');
		if (voteform.size()) {
			if (this._access.vote) {
				voteform.submit(function(){return false;});
				voteform.submit(function(){
			    	Tudu.vote();
			    });
			}
			
			var voterDialog = null;
			$('#view-voter').bind('click', function(){
				if (voterDialog === null) {
					voterDialog = new Tudu.VoterViewer({id: 'voter-dialog', tid: tuduId, back: currUrl});
		        }

				voterDialog.show();
			});
		}
	},
	
	/**
	 * 附件保存到网盘
	 */
	attachToNd: function(fileId) {
		var win = TOP.Frame.TempWindow,
			me = this;
		
		win.append(me._tpls.savetonetdisk, {
			width: 500,
			draggable: true,
			onClose: function() {
				win.destroy();
			}
		});
		
		win.find('input[name="fileid"]').val(fileId);
		var panel = win.find('.netdisk_panel');
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: '/netdisk/list',
			success: function(ret) {
				if (ret.success && ret.data) {
					var folders = ret.data.folders;
					
					var _$ = TOP.getJQ();
					
					me.ndTree = new _$.tree({
						id: 'netdisk-tree',
						idKey: 'id',
						idPrefix: 'nd-',
						cls: 'netdisk-tree'
					});
					
					var parent = new _$.treenode({
						data: {
							id: 'fo-_root',
							name: TOP.TEXT.NETDISK
						},
						content: '<span class="icon ficon folder"></span><span class="nd_foldername" name="folderid[]" _folderid="^root">{name}</span>',
						events: {
							click: function(e){
								TOP.stopEventBuddle(e);
							}
						}
					});
					me.ndTree.appendNode(parent);
					for (var folderid in folders) {
						if (folderid == '^root') {
							continue ;
						}
						var node = new _$.treenode({
							data: {
								id: 'fo-' + folderid.replace('^', '_'),
								name: folders[folderid].foldername
							},
							content: '<span class="icon ficon folder"></span><span class="nd_foldername" name="folderid[]" _folderid="'+folderid+'">{name}</span>',
							events: {
								click: function(e){
									TOP.stopEventBuddle(e);
								}
							}
						});
						
						parent = me.ndTree.find('fo-_root', true);

		                if (parent) {
		                    parent.appendChild(node);
		                }
					}
					me.ndTree.find('fo-_root', true).expand();
					
					me.ndTree.appendTo(panel);
					
					panel.find('span[name="folderid[]"]').bind('click', function(e){
						panel.find('div.tree-node-el').removeClass('tree-node-selected');
						if ($(this).parent().hasClass('tree-node-selected')) {
							$(this).parent().removeClass('tree-node-selected');
						} else {
							$(this).parent().addClass('tree-node-selected');
						}
						win.find('input[name="folderid"]').val($(this).attr('_folderid'));
						TOP.stopEventBuddle(e);
					});
				}
			},
			error: function(res) {}
		});
		
		var form = win.find('#ndform');
		form.submit(function(){return false;});
		form.submit(function(){
			var folderId = win.find('input[name="folderid"]').val();
			if (!folderId) {
				return TOP.showMessage(TOP.TEXT.SAVE_PATH_NULL, 5000);
			}
			var data = form.serializeArray();
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: data,
				url: form.attr('action'),
				success: function(ret) {
					TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
					
					if (ret.success) {
						win.close();
					}
				},
				error: function(res) {
					TOP.showMessage(TOP.TEXT.PROCESSING_ERROR, 5000);
				}
			});
		});
		
		win.show();
	},
	
	/**
	 * 回复
	 * 
	 *  回复 楼主 XXX
	 *  回复 第几楼 XXX
	 */ 
	replyPost: function(postid) {
		var html = '';
		
		if (postid) {
		    html = '<p><strong>{0}</strong><span class="gray" style="margin-left:5px">{1}</span><span class="gray" style="margin-left:5px">{2}</span></p><p>&nbsp;</p>';
		    var post = $('#post-' + postid),
		        poster = post.find('strong.poster').attr('_poster'),
		        floor  = post.find('.floor').text();
		    floor = floor ? floor : TOP.TEXT.FIRST_POST;
		    
		    html = TOP.formatString(html, TOP.TEXT.REPLY, floor, poster);
		}
		
		window.scrollTo(0,$('#replyform').offset().top);
		
		var editor = Tudu.Reply.getEditor();
		editor.pasteHTML(html);
		editor.focus();
	},
	
	/**
	 * 回复 - 引用
	 * 
	 * 引用：
	 * XXX 楼主\第几楼
	 */
	reference: function(postid) {
	    var html = '<div class="cite_wrap"><strong>{0}</strong><p class="gray">{1}</p>{2}</div><p>&nbsp;</p>';
	    var post = $('#post-' + postid);
	    var poster = post.find('strong.poster').attr('_poster'),
	        floor  = post.find('.floor').text(),
	        content = post.find('.post-content').html();
	    
	    floor = floor ? floor : TOP.TEXT.FIRST_POST;
	    html = TOP.formatString(html, TOP.TEXT.REFERENCE, poster + floor, content);
		
		window.scrollTo(0,$('#replyform').offset().top);
		
		var editor = Tudu.Reply.getEditor();
		editor.pasteHTML(html);
		editor.focus();
	},
	
	/**
	 * 设置是否新窗口
	 */
	setIsNewWin: function(bool) {
		this._isNewWin = bool;
	},
	
	/**
	 * 设置访问权限
	 */
	setAccess: function(access) {
		this._access = access;
		
		return this;
	},
	
	/**
	 * 设置标签列表
	 */
	setLabels: function(labels) {
		this._labels = labels;
		
		return this;
	},
	
	/**
	 * 是否允许进行该操作
	 */
	isEnable: function(access) {
		return this._access && this._access[access];
	},
	
	/**
	 * 是否新窗口
	 */
	isNewWin: function() {
		return this._isNewWin;
	},
	
	// 回复 - 收起或展开图度内容
	togglePost: function(postId, icon) {
		var icon = $(icon);
		
		var expanded = !icon.hasClass('icon_unfold');
		
		if (expanded) {
			$('#post-' + postId + ' div.tudu-content-body').hide();
			icon.addClass('icon_unfold');
		} else {
			$('#post-' + postId + ' div.tudu-content-body').show();
			icon.removeClass('icon_unfold');
		}
	},
	
	// 回复 - 显示日志
	toggleLog: function(tuduId) {
		var panel = $('#log-panel');
		if ($('#log-panel:visible').size()) {
			panel.hide();
		} else {
			panel.show();
			if (!$('#log-table').size()) {
				$('#log-list').html('<span style="margin:10px">' + TOP.TEXT.LOADING_LOG + '</span>')
				.load('/tudu/log?tid=' + tuduId);
			}
		}
	},
	
	/**
	 * 附件
	 */
	toggleAttach: function(tuduId) {
		var panel = $('#attach-panel');
		if ($('#attach-panel:visible').size()) {
			panel.hide();
		} else {
			panel.show();
			if (!$('#attach-table').size()) {
				$('#tudu-attach-list').html('<span style="margin:10px">' + TOP.TEXT.LOADING_ATTACH + '</span>')
				.load('/tudu/attach?tid=' + tuduId);
			}
		}
	},
	
	/**
     * 便签
     * @param {Object} tuduId
     */
    toggleNote: function(tuduId) {
        Tudu.Note.initNote(tuduId);
    },
	
	/**
	 * 初始化下拉菜单
	 */
	initMarkMenu: function() {
		var markItems = [{
			body: TOP.TEXT.UNREAD,
		    event: {
		        click: function(){
		            Tudu.mark(Tudu.View._tuduId, 'unread');
		        }
		    }
		},
		{
		    body: TOP.TEXT.READ,
		    event: {
		        click: function(){
			        Tudu.mark(Tudu.View._tuduId, 'read');
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
					Tudu.removeAll(Tudu.View._tuduId);
		        }
		    }
		},
		{
		    body: TOP.TEXT.CANCEL_STAR,
		    name: 'cancel-star',
		    style: 'display:none',
		    event: {
		        click: function(){
			        Tudu.star(Tudu.View._tuduId, 'unstar');
		        }
		    }
		},
		'-'];
		
		var labels = Tudu.View._labels;
		if (null != labels) {
			for (var labelid in labels) {
				if (labels[labelid].issystem || !labels[labelid].labelalias) {
					continue ;
				}
				
			    var sitem = {
			    	body: '<span class="menu-square" style="background:'+labels[labelid].bgcolor+'"></span>' + labels[labelid].labelname,
			    	labelid: labelid,
			    	name: labels[labelid].labelname,
			        event: {
			            click: function() {
			                var alias = this.attr('name');
			                
			                Tudu.addLabel(Tudu.View._tuduId, alias, function(ret){
			                	if (ret.success) {
			                		location.assign(location.href);
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
			        Tudu.star(Tudu.View._tuduId, 'star');
		        }
		    }
		});
		markItems.push('-');
		markItems.push({
		    body: TOP.TEXT.CREATE_LABEL,
		    event: {
		        click: function(){
					var ids = Tudu.View.getSelectId();
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
			    _fixMenuItems();
			});
		});
		
		// 修正菜单显示项目（根据选中的图度项目）
		function _fixMenuItems() {
			if ($('div.tag_wrap').find('.flagbg').size() > 0 || $('#star').hasClass('attention')) {
        		$('div.option-menu-wrap').find('[name="sep"]').show();
		    } else {
		    	$('div.option-menu-wrap').find('[name="sep"]').hide();
		    }
		    if ($('div.tag_wrap').find('.flagbg').size() > 0) {
		    	$('div.option-menu-wrap').find('[name="cancel-label"]').show();
		    } else {
		    	$('div.option-menu-wrap').find('[name="cancel-label"]').hide();
		    }
		    if ($('#star').hasClass('attention')) {
		    	$('div.option-menu-wrap').find('[name="cancel-star"]').show();
		    } else {
		    	$('div.option-menu-wrap').find('[name="cancel-star"]').hide();
		    }
		}
	},
	
	appendLabel: function(obj, labelid) {
		if (this._labels[labelid] == undefined) return ;
	    if ($('#' + obj.attr('id') + '-label-' + labelid).size()) return ;

		var e     = $(this._tpls.label),
		    label = this._labels[labelid],
		    ct    = obj.find('div.label_div'),
	        tid   = obj.attr('id').replace('tudu-', '');
		e
		.attr({'id': obj.attr('id') + '-label-' + labelid, 'title': label.labelname, '_alias': label.labelalias, '_labelid': labelid})
		.css({'background-color': label.bgcolor, 'color': '#fff'});

		e.find('.tag_txt').text(label.labelname);
		e.find('.tag_close')
		.bind('click', function(){
			var o = $(this).closest('table.flagbg');
				id = o.attr('id'),
				alias = o.attr('_alias');
			var arr = id.split('-');
			if (!arr[1] || !arr[3]) {
				return ;
			}
			
			var tuduId  = arr[1],
			    labelid = arr[3];
			Tudu.removeLabel(tuduId, alias, labelid);
		})
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
	        var offset = e.offset(),
	            pageTop= document.documentElement ? document.documentElement.scrollTop : document.body.scrollTop,
	            evtTop = evt.clientY + pageTop;
	        var isOver = evt.clientX > offset.left && evt.clientX < offset.left + e.width()
	                      && evtTop > offset.top && evtTop < offset.top + e.height();

	        if (!isOver) {
	         	clearTimeout(e.timer);
	         	e.timer = null;
		        e.find('.tag_close').hide();
	        }
	    })
		
		ct.append(e);
		
		return e;
	}
};

/**
 * 图度便签
 */
Tudu.Note = {
    isLoadNote: false,
    panel: $('#note-panel'),
    content: null,

    initNote: function(tuduId) {
        var _o = this,
            panel = this.panel;
        panel.find('textarea[name="notecontent"]').bind('click', function(e){
            _o.content = panel.find('textarea[name="notecontent"]').val();
            panel.find('div.note_edit').addClass('note_expand');
            TOP.stopEventBuddle(e);
        }).bind('keyup', function(){
            var content = panel.find('textarea[name="notecontent"]').val(),
                l = content.split("\n").length;
            if (l >= 2) {
                $(this).css('height', content.split("\n").length * 22 + 'px');
            }
        });

        if ($('#note-panel:visible').size()) {
            panel.hide();
        } else {
            if(!this.isLoadNote) {
                this.loadNote(tuduId);
            } else {
                panel.show();
                panel.find('textarea[name="notecontent"]').focus();
            }
        }

        $(window.document.body).click(function() {
            _collspanNode();
        });

        window.onbeforeunload = function() {
            _collspanNode();
        };

        function _collspanNode(){
            var expanded = panel.find('.note_expand');
            if (expanded.length) {
                _o.saveNote(tuduId);
            }
        }
    },

    /**
     * 加载便签
     * @param {Object} tuduId
     * @param {Object} panel
     */
    loadNote: function(tuduId) {
        var _o = this;
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: '/note/get-note?tid=' + tuduId,
            success: function(ret) {
                var panel = _o.panel;
                if (ret.success && ret.data) {
                    var content = ret.data.content;
                    panel.find('textarea[name="notecontent"]').val(content);
                    l = content.split("\n").length;
                    if (l >= 2) {
                        panel.find('textarea[name="notecontent"]').css('height', content.split("\n").length * 22 + 'px');
                    }
                    _o.content = content;
                    _o.updateNote(ret.data.noteid, tuduId);
                }

                panel.show();
                panel.find('textarea[name="notecontent"]').focus();
                panel.find('div.note_edit').addClass('note_expand');
                _o.isLoadNote = true;
            },
            error: function(res) {}
        });
    },

    /**
     * 更新便签事件等
     */
    updateNote: function(noteId, tuduId) {
        var _o = this,
            panel = _o.panel;

        panel.find('input[name="noteid"]').val(noteId);
        panel.find('a[name="delete"]')
        .unbind('click')
        .bind('click', function(){
            _o.deleteNote(tuduId);
        });
    },

    /**
     * 保存便签
     */
    saveNote: function(tuduId) {
        var _o      = this;
            noteId  = this.panel.find('input[name="noteid"]').val(),
            content = this.panel.find('textarea[name="notecontent"]').val(),
            data    = {
                tid: tuduId,
                content: content
            };

        if (noteId) {
            data.nid = noteId;
        }

        if (this.content != content) {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '/note/' + (noteId ? 'update' : 'create'),
                data: data,
                success: function(ret) {
                    if(ret && ret.success && ret.data) {
                        _o.updateNote(ret.data.noteid, tuduId);
                    }
                },
                error: function(res) {}
            });
        }

        this.panel.find('div.note_edit').removeClass('note_expand');
        _o.updateNote(noteId);
    },

    /**
     * 删除便签
     */
    deleteNote: function() {
        if (!confirm(TOP.TEXT.CONFIRM_DELETE_NOTES)) {
            return false;
        }

        var _o = this;
            noteId = this.panel.find('input[name="noteid"]').val();

        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {nid: noteId},
            url: '/note/delete',
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? true : false);
                if (ret.success) {
                    var panel = _o.panel;
                    panel.find('a[name="delete"]').unbind('click');
                    panel.find('input[name="noteid"]').val('');
                    panel.find('textarea[name="notecontent"]').val('');
                    panel.find('textarea[name="notecontent"]').css('height', '22px');
                    panel.hide();
                }
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
            }
        });
    }
};

/**
 * 图度回复
 */
Tudu.Reply = {
	/**
	 * 表单对象
	 */
	_form: null,
	
	/**
	 * 虚拟表单对象
	 */
	_vform: null,
	
	/**
	 * 附件上传对象
	 */
	_attachment: null,
	
	/**
	 * 编辑器
	 */
	_editor: null,
	
	/**
	 * 自动保存
	 */
	_autoSave: null, 
	
	/**
	 * 初始化回复表单
	 */
	init: function(params) {
		var me = this;
		if (undefined !== params.form) {
			this.setForm(params.form);
		}
		
		if (params.progress) {
			$('#percent').stepper({step: 25, max:100, format: 'percent'});
		}
		
		// 调整输入框位置
		$('div.post-content>li').css({'margin-left': '25px'});
		
		this._editor = new TOP.Editor(document.getElementById('content'), {
			resizeType : 1,
			width: '100%',
			minHeight: 200,
			themeType : 'tudu',
			scope: window,
			ctrl: {
				13: function(){Tudu.Reply.send('reply');}
			}
		}, jQuery);
		
		// 初始化附件上传
		if (typeof params.upload == 'object') {
			this.initAttachment(params.upload, $('#attach-list'), $('#attach-list td.bd_upload'));
			this.initPic('#insert-pic', params.upload);
		} else {
			this.initPic('#insert-pic');
		}
		
		this._vform = new VirtualForm({
			fields: this._form.find('input, textarea'),
			filter: {compare: '#postcontent, #content, #tid, #bid, #type, #action, #savetime, #fpid'},
			editor: {content: this._editor}
		});
		
		if (!Tudu.View.isNewWin()) {
			Tudu.Reply.initUnloadEvent();
		}
		
		// 截屏
		if (!TOP.Browser.isIE && !TOP.Browser.isFF) {
			$('#screencp-btn').remove();
		} else {
			$('#link-capture').bind('click', function(){
				if (!Capturer.getCapturer()) {
					return Capturer.install();
				}
				
	            Capturer.startCapture();
	        });
		}
		
		$('a[name="tpllist"]').click(function(e) {
			var boardId = $('input[name="bid"]').val();
	    	e.srcElement = $(this).parent('span.add')[0];
			Tudu.Template.showMenu(e, 'content', boardId);
			TOP.stopEventBuddle(e);
		});
		
		this._autoSave = new Tudu.AutoSave({
			form: Tudu.Reply._vform,
			time: 30000,
			func: Tudu.Reply.send,
			self: Tudu.Reply,
			forcesave: 0,
			roundComplete: function() {
	    		if (!me._autoSave.lastSaveTime) {
	    			return ;
	    		}
	    		var now = new Date(),
		    		hour = now.getHours(),
					minute = now.getMinutes(),
					time = [((hour < 10) ? "0" + hour : hour),
					        ((minute < 10) ? "0" + minute : minute)].join(':'),
	    			diff = Math.round((now - me._autoSave.lastSaveTime) / 60000);
	    		
				$('span.compose_msg').html(TOP.TEXT.AUTOSAVE_TIPS_FIRST + time + ' ( ' + diff + TOP.TEXT.AUTOSAVE_TIPS_SECOND + ' )');
				$('#savetime').val(parseInt(now.getTime() / 1000));
	    	}
		});
	},
	
	getEditor: function() {
		return this._editor;
	},
	
	setForm: function(form) {
		if (typeof(form) == 'string') {
			form = $(form);
		}
		
		this._form = form;
		
		this._form.submit(function(){return false;});
		this._form.submit(function(){
	    	Tudu.Reply.send('reply');
	    });
		
		return this;
	},
	
	initAttachment: function(params, list, container) {
		var config = {
	        buttonImageUrl: '',
	        buttonWidth: '70',
	        buttonHeight: '16',
	        buttonTextLeftPadding: 20,
	        buttonTextTopPadding: 1,
	        buttonPlaceholderId: 'upload-btn',
	        postParams: {}
	    };
		
		for (var k in params) {
			if (typeof(TuduUpload.defaults[k]) != 'undefined') {
				config[k] = params[k];
			}
		}
		
		$('.upload_btn')
		.mouseover(function(){$('#upload-link').css('text-decoration', 'underline');})
		.mouseout(function(){$('#upload-link').css('text-decoration', 'none');});

		var upload = new TuduUpload(config);
		var handler = new Tudu.Attachment();
		handler.list = list;
		handler.container = container;
		handler.setUpload(upload);
		
		this._attachment = upload;
	},
	
	// 初始化图片上传
	initPic: function(ele, uploadParams) {
		var auth = uploadParams ? uploadParams.auth : null,
			picupload = null,
			picup  = null,
			me = this;
		var d = this.menuDialog('pic-dia', {
	        body: $('#pic-modal'),
	        oncreate: function() {
	    		$('#pic-modal .tab-header li a').click(function(){
	    			$('#pic-modal .tab-header li').removeClass('active');
	    			var o = $(this),
	    				name = o.attr('name');
	    			o.parent().addClass('active');
	    			$('#pic-modal div.tab-body').hide();
	    			$('#tb-' + name).show();
	    		});
	    		
	    		$('#pic-modal button[name="piccancel"]').click(function(){
	    			d.hide();
	    		});
	    		
	    		$('#pic-modal button[name="confirm"]').click(function(){
	    			var url = $('#picurl').val();
	    			if (url) {
	    				me._editor.loadBookmark();
	    				me._editor.pasteHTML('<img src="'+url+'" alt="" />', true);
	    			}
	    			d.hide();
	    		});
	    		
	    		// 上传图片
	    		if (uploadParams) {
	    			var config = {
				        buttonWidth: '280',
				        buttonHeight: '24',
				        fileTypes: '*.jpg;*.jpeg;*.gif;*.png',
				        buttonPlaceholderId: 'pic-upload-btn',
				        postParams: {}
				    };
					
					for (var k in uploadParams) {
						if (typeof(TuduUpload.defaults[k]) != 'undefined') {
							config[k] = uploadParams[k];
						}
					}
					
					$('.imgupload')
					.mouseover(function(){$('button[name="browse"]').mouseover();})
					.mouseout(function(){$('button[name="browse"]').mouseout();});
					var filename = $('#filename');

					picupload = new TuduUpload(config);
					picup = new Tudu.EditorUpload({
						upload: picupload,
						onFileQueue: function(file){
							var files = [];
							for (var k in this._files) {
								files.push(this._files[k].name);
							}
							
							filename.val(files.join(','));
						},
						onComplete: function(){
							me._editor.loadBookmark();
							
							for (var i = 0, c = this._success.length; i < c; i++) {
								var aid = this._success[i].aid,
									url = '/attachment/img?fid=' + aid,
									html = '<a href="' + url + '" target="_blank"><img src="'+ url +'" _aid="'+aid+'" /></a>';
								me._editor.loadBookmark();
								me._editor.pasteHTML(html);
							}
							d.hide();
						}
					});
					$('button[name="upload"]').click(function(){
						picup.startUpload();
					});
	    		}
	    	}
	    });
		d.hide();
		
		$(ele)
		.mousedown(function(e){me._editor.saveBookmark();TOP.stopEventBuddle(e);})
		.click(function(e){
			TOP.stopEventBuddle(e);
	        var offset = $(this).offset(),
	            left = offset.left - 22,
	            top  = offset.top + 16;
	        
	        if (null != picup) {
	        	picup.cleanFileQueue();
	        	$('#filename').val('');
	        }
	        
	        d.css({
	        	left: left,
	        	top: top
	        }).show();
	        
	        $('#pic-modal .tab-header li:eq(0) a').click();
			$('#picfile').val('');
			$('#picurl').val('http://');
			
	        TOP.stopEventBuddle(e);
	    });
	},
	
	//显示下拉功能框
	menuDialog: function(id, params) {
		if (!id || !params.body) {
			return ;
		}
		var obj = $('#' + id);
		
		if (!obj.size()) {
		    obj = $('<div>')
				  .addClass('modal-dialog')
				  .attr('id', id);
		    
		    obj
			.appendTo(params.appendTo ? params.appendTo : document.body)
			.bind('click', function(e){
				TOP.stopEventBuddle(e);
			});
		    
			if (typeof(params.body) == 'string') {
				obj.html(params.body);
			} else {
				obj.append(params.body.show());
			}
			
			if (typeof(params.oncreate) == 'function') {
				params.oncreate.call(obj);
			}
			
			$(window).bind('click', function(){
				obj.hide();
			});
			$('#replyform').bind('click', function(){
				obj.hide();
			});
		}
		
		obj.css({
			position: 'absolute',
			zIndex: 100,
			top: params.top ? params.top + 'px' : 0,
			left: params.left ? params.left + 'px' : 0
		}).show();
		
		return obj;
	},
	
	/**
	 * 发送回复
	 */
	send: function(action, callback) {
		if (!this._form || !this._form.size()) {
			return ;
		}
		
		var isSend = action != 'autosave',
			me = this;
		
		this._form.find('input[name="type"]').val(action);
		if (isSend) {
			
		    if (this._editor && this._editor.isNull()) {
		    	this._editor.focus();
		        return TOP.showMessage(TOP.TEXT.POST_CONTENT_IS_NULL);
		    }
			
			if (!this.whileUploading(this._attachment, TOP.TEXT.WAITING_UPLOAD, function(){Tudu.Reply.send('send');})) {
				return ;
			}
			
			if ($('#attach-list div.upload_error').size()) {
				if (!confirm(TOP.TEXT.REPLY_UPLOAD_FAILURE)) {
					return ;
				}
			}
		} else {
			this._form.find('input[name="type"]').val('save');
			
			if (!this._form.find('input[name="tid"]').val()) {
				return ;
			}
			
			if (this._attachment && this._attachment.isUploading()) {
				return ;
			}
		}
		
		// 处理图片
		var src = this._editor.getSource();
		var reg = /<img([^>]+)src="([^"]+)"([^>]+)_aid="([^"]+)"([^>]+)\/>/ig;
		
		if (action != 'autosave') {
			if (!checkContentImage(src, this._editor, function(){Tudu.Reply.send(action, callback);})) {
		    	return ;
		    }
		}
		
	    this._form.find(':hidden[name="file[]"]').remove();
	    while ((result = reg.exec(src)) != null) {
	    	this._form.append('<input type="hidden" name="file[]" value="'+result[4]+'" />');
	    }
	    
	    src = src.replace(reg, '<img$1src="AID:$4"$3_aid="$4"$5/>');
	    src = src.replace(/\s+id="[^"]+"/g, '');
	    
	    $('#postcontent').val(src);
		
	    var data = this._form.serializeArray();
	    
	    if (isSend) {
	    	this._form.find(':input').attr('disabled', true);
	    }
	    $.ajax({
	        type: 'POST',
	        dataType: 'json',
	        data: data,
	        url: this._form.attr('action'),
	        success: function(ret) {
	            if (isSend) {
            		TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
            		if (ret.success) {
            			if (Tudu.View.isNewWin()) {
            				location = '/tudu/view?tid='+_TUDU_ID+'&newwin=1';
            			} else {
            				location = '/tudu/view?tid='+_TUDU_ID+'&page=last&back='+_BACK+'&reload=1';
            			}
            		}
            	} else if (ret.data) {
	            	me._form.find('input[name="fpid"]').val(ret.data.postid);
	            	me._form.find('input[name="action"]').val('modify');
	            }
	            
	            if (ret.success) {
	            	if (typeof(callback) == 'function') {
	                	return callback();
	                }
	            }
	        },
	        error: function(res) {
	        	if (isSend) {
		            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
		            me._form.find(':input').attr('disabled', false);
	        	}
	        }
	    });
	},
	
	whileUploading: function(msg, callback) {
		if (null != this._attachment) {
			var me = this;
			if (this._attachment.isUploading()) {
				this._form.find(':input').attr('disabled', true);
				
				this._attachment.setParam('upload_complete_handler', function(){
					var stats = this.getStats();
					if (stats.files_queued == 0 && !stats.in_progress) {
						this._form.find(':input').attr('disabled', false);
						if (typeof(callback) == 'function') {
							callback();
						}
					}
				});
				
				var message = [
				    '<div class="msg-progress" id="msg-progress"><div></div></div><span id="msg-txt-progress">0%</span>',
				    msg,
				    ' [<a href="javascript:void(0);">' + TOP.TEXT.CANCEL + '</a>]'
				].join('');
				
				TOP.showMessage(message, 0, 'success');
				var progress = this._attachment.totalProgress();
				TOP.getJQ()('#msg-progress div').width(progress + '%');
				TOP.getJQ()('#msg-txt-progress').text(progress + '%');
				
				TOP.getJQ()('#result-top a').click(function(){
					TOP.showMessage();
					me._attachment
					.setParam('upload_complete_handler', function(){})
					.setParam('upload_progress_handler', function(file, uploaded, total){
						me._attachment.onProgress.call(this, file, uploaded, total);
					});
					me._form.find(':input').attr('disabled', false);
				});
				
				return false;
			}
		}
		
		return true;
	},
	
	// 离开页面提示
	initUnloadEvent: function(){
		var me = this;
		TOP.getJQ()('a:not([href^="javascript:"])').bind('click', _leaveDialog);
		$('a:not([href^="javascript:"]):not(#link-fullreply):not([target="_blank"]):not(.xheButton)').bind('click', _leaveDialog);

		TOP.getJQ()('form').bind('submit', _leaveDialog);
		
		TOP.window.onbeforeunload = function() {
			if (!me._vform.invariant()) {
				return TOP.TEXT.COMPOSE_EXIT_WITHOUT_SAVE;
			}
		};
		window.onunload = function(){
			TOP.getJQ()('a').unbind('click', _leaveDialog);
			TOP.getJQ()('form').unbind('submit', _leaveDialog);
		};
		
		function _leaveDialog(e) {
			if (me._vform.invariant()) {
				return true;
			}
			
			var trigger = $(this);
			
			TOP.Label.focusLabel();
			TOP.Frame.Dialog.show({
				title: TOP.TEXT.LEAVE_HINT,
				body: '<p style="padding:15px 0;margin-left:-12px"><strong>' + TOP.TEXT.REPLY_EXIT_SAVE_HINT + '</strong></p>',
				buttons: [{
					   text: TOP.TEXT.SEND_REPLY,
					   cls: 'btn',
					   events: {click: function(){
							Tudu.Reply.send('reply', function(){
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
	}
};

/**
 * 查看投票情况
 * voterDialog
 */
Tudu.VoterViewer = function(params) {
	this._settings = $.extend({}, params);
};
Tudu.VoterViewer.tpl = '<div class="pop pop_linkman"><div class="pop_header"><strong>' + TOP.TEXT.VOTE_DETAIL_INFO + '</strong><a class="icon icon_close close"></a></div><div class="pop_body" style="padding:10px"></div><div class="pop_footer"><button type="button" name="confirm" class="btn">' + TOP.TEXT.CONFIRM + '</button></div></div>';
Tudu.VoterViewer.prototype = {

  	win: null,

  	_settings: null,

  	init: function() {
  		var _o = this;
  		
		_o.win = TOP.appendWindow(_o._settings.id, Tudu.VoterViewer.tpl, {
			width: 500,
			draggable: true,
			onShow: function() {
				_o.getOptions();
				
				_o.win.find('a.icon_close, button[name="confirm"]').bind('click', function(){
					_o.win.close();
					return false;
				});
			},
			onClose: function() {
				_o.win.destroy();
			}
		});

  		_o.win.show();
	},

	show: function() {
		this.init();
	},

	voters: [],
	
	/**
	 * 获得投票选项
	 */
	getOptions: function() {
		var _o = this;
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: '/tudu/vote-options?tid=' + _o._settings.tid,
			success: function(ret) {
				if (ret.success && ret.data) {
					var options = ret.data;
					    html = [];
					_o.win.find('div.pop_body').append('<table style="width:100%;"><tr height=25><td width="60" style="text-align:right;color:#000000;"><strong>'+TOP.TEXT.VOTE_OPTIONS+TOP.TEXT.CLN+'</strong></td><td><select id="vote-options"></select></td></tr><tr height=25><td style="text-align:right;vertical-align:top;color:#000000;"><div style="margin-top:3px;"><strong>'+TOP.TEXT.VOTE_VOTER+TOP.TEXT.CLN+'</strong></div></td><td><div id="voters"></div></td></tr></table>');
					
					for(var i=0; i<options.length; i++) {
						html.push('<option value="'+options[i].optionid+'">'+options[i].text+'</option>');
						_o.voters[options[i].optionid] = options[i].voters;
					}
					
					TOP.getJQ()('#vote-options').append(html.join(''));
					
					var firstOptionId = TOP.getJQ()("#vote-options option:first").val();
					_o.appendVoters(firstOptionId);
					
					TOP.getJQ()('#vote-options').bind('change', function(){
						var optionId = $(this).children('option:selected').val();
						_o.appendVoters(optionId);
					});
				}
			},
			error: function(res) {}
		});
  	},
  	
  	/**
  	 * 写入参与人
  	 */
  	appendVoters: function(optionId) {
  		var _o = this,
  			voters = _o.voters[optionId];
  		TOP.getJQ()('#voters').empty();
  		var html = ['<table style="width:100%"><tr>'], tr = 1;
  		if (voters !== null) {
	  		for (var i=0; i<voters.length; i++) {
	  			if (voters[i].length) {
	  				var user = voters[i].split(' ');
	  				html.push('<td width="20%"><a href="/contact/view?email=' + user[0] + '&back=' + _o._settings.back + '" target="blank">' + user[1] + '</a></td>');
	  			}
	  			if (html.length - 1 == 5 * tr) {
	  				html.push('</tr><tr>');
	  				tr++;
	  			}
	  		}
	  		html.push('</tr></table>');
	  		TOP.getJQ()('#voters').append(html.join(''));
	  		_o.win.find('a.close').bind('click', function(){
				_o.win.close();
	  		});
  		} else {
  			TOP.getJQ()('#voters').append('<table style="width:100%"><tr><td">'+TOP.TEXT.VOTER_NULL_TIPS+'</td></tr></table>');
  		}
  	}
};

/**
 * 截图
 */
var Capturer = {
	
	capturer: null,
	
	editor: null,
	
	uploaddialog: null,
	
	uploadurl: null,
	
	installed: null,
	
	setEditor: function(editor) {
		this.editor = editor;
	},
	
	setUploadUrl: function(url) {
		this.uploadurl = url;
	},

	install: function() {
		var btns = [
		   {
			   text: TOP.TEXT.INSTALL_ONLINE,
			   cls: 'btn',
			   events: {click: function(){
			   	   if (confirm(TOP.TEXT.INSTALL_LEAVE_CONFIRM)) {
			   		   top.location = '/plugin/screencapture?back=' + encodeURIComponent(TOP.Frame.hash());
			   		   Win.close();
			   	   }
			   }}
		   },
		   {
               text: TOP.TEXT.CANCEL,
               cls: 'btn close',
               events: {click: function(){Win.close()}}
           }
		];
		
		var Win = TOP.Frame.Dialog.show({
			title: TOP.TEXT.CAPTURER_INSTALL_TIPS,
			body: '<div class="screen_lock"><div><span class="icon icon_attention_big"></span><strong>'+TOP.TEXT.UNINSTALL_CAPTURER_TOOLS_TIPS+'</strong></div><ul><li>'+TOP.TEXT.AFFTER_INSTALL+TOP.TEXT.COMMA+TOP.TEXT.CLICK+'&nbsp;<span class="icon icon_screencp"></span><a>'+TOP.TEXT.CAPTURER+'</a>&nbsp;'+TOP.TEXT.COMMA+TOP.TEXT.USE_CAPTURER_NOW+'</li></ul></div>',
			buttons: btns
		});
	},
	
	getCapturer: function() {
		if (null === this.capturer && false !== this.installed) {
			var me = this;
			
			this.capturer = new ScreenCapture({
				onCaptured: function() {
		    		me.uploaddialog = TOP.Frame.Dialog.show({
		                body:  '<div>' + TOP.TEXT.FILE_UPLOADING + '</div><div class="progress_large" style="width:420px;margin:10px 0"><div class="bar"></div></div>',
		                title: TOP.TEXT.FILE_UPLOADING,
		                close: false
		            }).getWin();
		        },
		        onUploaded: function(uploader) {
		            if (uploader) {
		            	me.uploaddialog.dialog.container.find('div.progress_large div.bar').css('width', '95%');
		
		                var response = uploader.HttpReault.match(/\{.*\}/);
		
		                var ret;
		                try {
		                    eval('ret=' + response + ';');
		                } catch (e) {}
		
		                if (ret.fileid) {
		                	if (me.editor !== null) {
		                		//Modify.appendTOEditor(ret.fileid)
			                    var url = '/attachment/img?fid=' + ret.fileid;
			
			                    html = '<img src="'+ url +'" _aid="'+ret.fileid+'" />';
			                    me.editor.loadBookmark();
			                    me.editor.pasteHTML(html);
		                    }
		                } else {
		                    TOP.showMessage(TOP.TEXT.CAPTURER_UPLOAD_FILE_ERROR);
		                }
		            } else {
		                TOP.showMessage(TOP.TEXT.CAPTURER_START_UPLOAD_ERROR);
		            }
		            
		            setTimeout(function(){me.uploaddialog.hide();}, 500);
		        },
		        uploadUrl: me.uploadurl
			});
			
			if (!this.capturer.init()) {
				this.capturer.destroy();
				
				this.installed = false;
				this.capturer = null;
				return null;
			}
		}
		return this.capturer;
	},
	
	startCapture: function() {
		this.getCapturer().startCapture();
	}
};

/**
 * 审批窗口
 * 
 */
Tudu.ReviewWin = {
	
	_win: null,
	
	_tpl: [
		'<form id="review-form" action="/app/attend/apply/review" method="post" enctype="multipart/form-data">',
		'<div class="pop">',
		'<input type="hidden" name="labelid" value="" />',
		'<input type="hidden" name="review" value="1" />',
		'<input type="hidden" name="action" value="send" />',
		'<div class="pop_header"><strong></strong><a class="icon icon_close close"></a></div>',
		'<div class="pop_body" id="review-edit">',
		'<input type="hidden" name="tid" value="" />',
		'<table cellspacing="2" cellpadding="0" width="535">',
		'<tr>',
		'<td style="padding:4px 0;"><span id="review-hints" style="color: #000"></span><span class="gray">('+TOP.TEXT.REVIEW_SUGGEST+')</span></td>',
		'</tr>',
		'</table>',
		'<table cellspacing="2" cellpadding="0" width="535">',
		'<tr>',
		'<td><textarea class="form_textarea" name="content" id="review-content" cols="" rows="" style="width:450px;height:120px"></textarea></td>',
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
			$('button.btn').removeAttr('disabled');
	
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

			$('button.btn').attr('disabled', 'disabled');
		    $('#review-preparelist a').click(function(){return false;});
	
		    Win.find('button[name="retry"]').show();
	
			failures = [];
			
			Win.find('#review-content').val(editor.getSource());
	
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
		editor = new TOP.Editor(TOP.document.getElementById('review-content'), {
            resizeType : 0,
            width: '100%',
            minHeight: 150,
            themeType : 'tudu',
            statusbar: false,
            scope: TOP,
			ctrl: {13: function(){Win.find('button[name="confirm"]:eq(0)').click();}}
        }, TOP.getJQ());
		
		editor.focus();
	}
};


/**
 * 外发管理
 */
Tudu.Foreign = {
		
	tuduId: null,
	
	wintpl: '<div id="foreignwin" class="pop"><div class="pop_header"><strong>'+TOP.TEXT.TUDU_FOREIGN+'</strong><a class="icon icon_close close"></a></div><div class="pop_body" style="padding:0"></div><div class="pop_footer"><button type="button" class="btn close">'+TOP.TEXT.CLOSE+'</button></div></div>',
	
	/**
	 * 是否已更新
	 */
	updated: false,
	
	window: function() {
		var _o = this, Win = TOP.Frame.TempWindow;
		Win.append(this.wintpl, {
			width: 750,
			draggable: false,
			title: TOP.TEXT.TUDU_FOREIGN,
			onShow: function() {
				_o.load(Win.find('div.pop_body'));
			},
			onClose: function() {
				if (_o.updated) {
					location.reload();
				}
			}
		});
		
		this.updated = false;
		
		Win.show();
	},
	
	load: function(obj) {
		var Win = TOP.Frame.TempWindow,
			_o  = this;
		obj.empty();
		obj.load('/tudu/foreign?tid=' + this.tuduId, function(){
			if (Win.find('#to').size()) {
				new TOP.ContactInput({
			        id: 'to-input', target: Win.find('#i-to'), valuePlace: Win.find('#to'), group: false, org: false
			    });
			}
			new TOP.ContactInput({
		        id: 'cc-input', target: Win.find('#i-cc'), valuePlace: Win.find('#cc'), group: false, org: false
		    });
			
			TOP.Frame.TempWindow.center();
			
			Win.find('#addbtn').bind('click', function(){
				Win.find('#foreignedit').show();
				Win.find('#addforeign').hide();
				Win.center();
			});
			Win.find('#canceladd').bind('click', function(){
				Win.find('#foreignedit').hide();
				Win.find('#addforeign').show();
				Win.center();
			});
			
			Win.find('a[name="delete"]').each(function(){
				$(this).bind('click', function() {
					_o.deleteForeign(this.id.replace('del-', ''));
				});
			});
			
			Win.find('#foreignform').submit(function(){return false;});
			Win.find('#foreignform').submit(function(){
				var to = Win.find('#to'),
					cc = Win.find('#cc');
				
				if (!to.val() && !cc.val()) {
					return alert('请填写需要添加访问的人员');
				}
				
				_o.updated = true;
				
				$('button.btn').attr('disabled', 'disabled');
				$.ajax({
					type: 'POST',
					dataType: 'json',
					data: {
						tid: _o.tuduId,
						to: to.val(),
						cc: cc.val()
					},
					url: '/tudu-mgr/foreign.add',
					success: function(ret) {
						TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
						$('button.btn').removeAttr('disabled');
						if (ret.success) {
							_o.load(Win.find('div.pop_body'));
						}
					},
					error: function(res) {
						$('button.btn').removeAttr('disabled');
						TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
					}
				});
			});
		});
	},
	
	deleteForeign: function(uniqueId) {
		if (!uniqueId) {
			alert('没有要删除的项目');
		}
		
		if (!confirm('删除记录后该地址将失效，确实要删除该外部访问连接？')) {
			return false;
		}
		
		var _o = this,
			Win = TOP.Frame.TempWindow;
		
		this.updated = true;
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {tid: this.tuduId, uniqueid: uniqueId},
			url: '/tudu-mgr/foreign.delete',
			success: function(ret) {
				TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
				
				if (ret.success) {
					_o.load(Win.find('div.pop_body'));
				}
			},
			error: function(res) {
				TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
				return false;
			}
		});
	}
};

/**
 * 虚拟表单对象
 * @param param
 * @return
 */
var VirtualForm = function(params) {
	if (!params.fields) {
		return null;
	}
	
	this._fields = params.fields;
	if (params.filter) {
		this._filter = params.filter;
	}
	if (params.editor) {
		this._editors = params.editor;
	}
	
	this.save();
};
VirtualForm.prototype = {
	
	/**
	 * 表单值暂存
	 */
	_data: null,
	
	/**
	 * 
	 */
	_filter: null,
	
	/**
	 * 表单内容列表
	 */
	_fields: null,
	
	/**
	 * 提交表单
	 */
	submit: function(action, method, target) {
		method = method ? method : 'post';
		target = target ? target : '_self';
	
		var data = this.serialize(),
			form = $('<form method="'+method+'" action="'+action+'" target="'+target+'" style="display:none"></form>');
		for (var k in data) {
			if (data[k].constructor == window.Array) {
				for (var i = 0, c = data[k].length; i < c; i++) {
					form.append('<textarea name="'+k+'">'+data[k][i]+'</textarea>');
				}
			} else {
				form.append('<textarea name="'+k+'">'+data[k]+'</textarea>');
			}
		}
		
		form.appendTo(document.body);
		form.submit();
	},
	
	/**
	 * 序列化表单数据(Json)
	 */
	serialize: function(filter) {
		var ret = {};
		var fields = filter ? this._fields.not(filter) : this._fields;
		
		fields.each(function() {
			if (!this.name) {
				return ;
			}
			
			if (this.type == 'checkbox' && !this.checked) {
				return ;
			}
			
			if (this.name.indexOf('[]') != -1) {
				if (!ret[this.name]) {
					ret[this.name] = [];
				}
				ret[this.name].push(this.value);
			} else {
				ret[this.name] = this.value;
			}
		});
		
		if (typeof this._editors == 'object') {
			for (var k in this._editors) {
				if (typeof this._editors[k].getSource != 'function') {
					continue ;
				}
				
				ret[k] = this._editors[k].getSource();
			}
		}
		
		return ret;
	},
	
	/**
	 * 检查表单变化
	 */
	invariant: function() {
		return this.compare(this.serialize(this._filter.compare), this._data);
	},
	
	/**
	 * 与上一次保存的表单内容作对比
	 */
	compare: (function() {
		return function(v1, v2) {
			var v1 = this.serialize(this._filter.compare),
				v2 = this._data, c1 = 0, c2 = 0;
			
			for (var k in v1) {
				if (v2[k] == undefined) {
					return false;
				}
				if (v1[k].constructor == window.Array) {
					if (!_compareArray(v1[k], v2[k])) {
						return false;
					}
				} else {
					if (v1[k] != v2[k]) {
						return false;
					}
				}
				
				c1++;
			}
			
			for (var k in v2) {
				c2++;
			}
			
			return c1 == c2;
		}
		
		/**
		 * 
		 */
		function _compareArray(arr1, arr2) {
			if (arr1.constructor != window.Array 
				|| arr2.constructor != window.Array)
			{
				return false;
			}
			
			if (arr1.length != arr2.length) {
				return false;
			}
			
			for (var i = 0, c = arr1.length; i < c; i++) {
				if (arr1[i] != arr2[i]) {
					return false;
				}
			}
			
			return true;
		}
	})(),
	
	/**
	 * 保存当前表单状态
	 */
	save: function() {
		this._data = this.serialize(this._filter.compare);
	},
	
	/**
	 * 
	 */
	setFields: function(fields) {
		this._fields = fields;
	},
	
	/**
	 * 
	 */
	clear: function() {
		this._data = {};
	}
};

/**
 * 网盘附件文件选择
 * 
 * @param params
 * @return
 */
var FileDialog = function(params) {
	this._settings = $.extend({}, params);
	
	this.listCt = this._settings.listCt ? this._settings.listCt : $('#attach-list');
	this.list   = this._settings.list ? this._settings.list : $('#attach-list td.bd_upload');
};
FileDialog.filetpl = [
	'<div class="filecell"><input type="hidden" name="nd-attach[]" value="" /><input type="hidden" name="attach[]" value="" />'
	,'<div class="attsep"><div class="attsep_file"><span class="icon icon_add"></span><span class="filename"></span>&nbsp;<span class="filesize"></span></div>'
    ,'<div class="attsep_del"><a href="javascript:void(0)" name="delete">' + TOP.TEXT.DELETE + '</a></div>'
    ,'<div class="clear"></div></div></div>'
].join('');
FileDialog.tpl = '<div class="pop pop_linkman"><div class="pop_header"><strong>'+TOP.TEXT.NETDISK_ATTACH+'</strong><a class="icon icon_close close"></a></div><div class="pop_body" style="padding:10px"><p class="gray">'+TOP.TEXT.SELECT_NETDISK_FILE+'</p></div><div class="pop_footer"><button type="button" name="confirm" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" name="cancel" class="btn close">'+TOP.TEXT.CANCEL+'</button></div></div>';
FileDialog.prototype = {
	
	win: null,

	upload: null,
	
	_settings: null,
	
	list: null,
	
	listCt: null,
	
	init: function() {
		var Win = TOP.Frame.TempWindow, _o = this;
		Win.append(FileDialog.tpl, this._settings.id, {
			width: 300,
			draggable: true,
			onClose: function() {
				Win.destroy();
			}
		});
	
		if (this._settings.upload) {
			this.initUpload();
		}
		
		var netdiskPanel = new TOP.NetdiskPanel();
		netdiskPanel.renderTo(Win.find('.pop_body'));
		
		Win.find('button[name="confirm"]').click(function(){
			var selected = netdiskPanel.getFileSelected();
			
			for (var i = 0, c = selected.length; i < c; i++) {
				if (!selected[i].fileid) {
					continue ;
				}
				_o.appendToAttachment(selected[i].fileid, selected[i].filename, selected[i].filesize)
			}
			
			Win.close();
		});
		
		Win.show();
	},
	
	show: function() {
		this.init();
	},
	
	appendToAttachment: function(fileid, filename, filesize) {
		var _o = this;
		var el = $(FileDialog.filetpl);
		
		if (_o.list.find('#nd-file-' + fileid).size()) {
			return ;
		}
		
		el
		.attr('id', 'nd-file-' + fileid)
		.find('.filename').text(filename);
		
		el.find(':hidden[name="nd-attach[]"]').val(fileid);
		el.find(':hidden[name="attach[]"]').val(fileid);
		
		filesize = filesize > 1024 ? Math.round(filesize / 1024, 2) + 'KB' : filesize + 'bytes',
		el.find('.filesize').text('(' + filesize + ')');
		el.find('a[name="delete"]').click(function(){
			el.remove();
			if (!_o.list.find('.filecell').size()) {
				_o.listCt.hide();
			}
		});
		
		_o.list.append(el);
		_o.listCt.show();
	}
};


/**
 * 自动保存图度及回复
 */
Tudu.AutoSave = function(params) {
	this.setParam(params);
	this.init();
};

Tudu.AutoSave.prototype = {
	
	// 保存的数据
	data: null,
	
	// 自动保存的详细时间 用于计算时间间隔
	lastSaveTime: null,
	
	// 自动保存的简单时间（H：i）
	timeValue: null,
	
	/**
	 * 设置项
	 */
	settings: null,
	
	/**
	 * 设置参数
	 */  
	setParam: function(key, val) {
		var p = {};
		if (typeof(key) == 'string' && undefined != val) {
			p[key] = val;
		} else {
			p = key;
		}
		
		if (null == p || undefined == p) {
			return ;
		}
		
		if (null == this.settings) {
			this.settings = {};
		}
		
		for (var k in p) {
			this.settings[k] = p[k];
		}
	},
	
	/**
	 * 初始化
	 */
	init: function() {
		var me = this;
		
		if (null == this.settings.form || null == this.settings.time || null == this.settings.func) {
			return;
		}
		
		if (me.settings.forcesave) {
			this.settings.form.clear();
		}
		
		setTimeout(function(){
			me.checkSave();
		}, this.settings.time);
	},
	
	/**
	 *  检查是否执行保存操作
	 */
	checkSave: function() {
		var me = this;
		
		setTimeout(function(){
			me.checkSave();
		}, this.settings.time);
		
		if (me.settings.form.invariant()) {
			if (typeof this.settings.roundComplete == 'function') {
				this.settings.roundComplete();
			}
			return ;
		}
		
		this.lastSaveTime = new Date();
		this.settings.form.save();
		this.settings.func.call(this.settings.self, 
			'autosave', 
			typeof this.settings.roundComplete == 'function'
			? this.settings.roundComplete
			: null
		);
	}
};


/**
 * 图度模板
 */
Tudu.Template = {
	// 显示目录
	menu: null,
	// 板块模板目录列表
	list: {},
	// 模板内容
	content: {},
	// 版块模板数据
	tplList : {},
	// 模板
	boardId: null,
	// 写入的表单控件
	textarea: null,
	// 显示目录
	showMenu: function(e, textarea, boardId) {
		var me = this;
		this.textarea = textarea;
		this.boardId = boardId;
		if (null == this.menu) {
			this.menu = new $.Dropdown({
				id: 'tpl-picker',
				menuBody: '<div class="option_menu_body"><div class="option_menu_title"><div class="search_tpl"><input id="search_tpl" class="input_text search_input" type="text" /><a class="icon icon_search_2"></a></div></div><p class="gray" style="padding:0 4px;">'+TOP.TEXT.TPL_TIPS+'</p><div id="tpl-list" class="tpl-list"></div><div id="search-list" class="tpl-list" style="display:none"></div></div>',
				maxWidth: '220px',
				offsetLeft: 0,
				alwaysBottom: true,
				onShow: function() {
					if(me.boardId) {
						$('#tpl-list').html('<p style="padding:10px 5px;">' + TOP.TEXT.LOADING_TPL + '</p>');
						if(me.list[me.boardId] != null) {
							$('#tpl-list').html(me.list[me.boardId]);
						}else {
							Tudu.Template.appendMenu(this);
						}
					} else {
						$('#tpl-list').html('<p style="padding:10px 5px">' + TOP.TEXT.BOARD_NOT_APPIONT + '</p>');
					}
					$('#search_tpl').bind('click', function(e){
						TOP.stopEventBuddle(e);
					}).bind('keyup', function(){
						var keyword = this.value.replace(/^\s+|\s+$/g, '');
						if (keyword) {
							me.search(keyword);
						} else {
							$('#search-list').empty();
							$('#tpl-list').show();
							$('#search-list').hide();
						}
					});
				},
				onHide: function() {
					$('#tpl-list').empty();
					$('#search_tpl').val('');
					$('#tpl-list').show();
					$('#search-list').hide();
					$('#tpl-list, #search-list').css({'height': 'auto'});
				}
		    });
		}
		
		this.menu.show(e);
		
		if ($('#tpl-list a.menu_item').size() > 10) {
		   $('#tpl-list, #search-list').css({'height': '200px'});
	    }
	},
	
	/**
	 * 搜索
	 */
	search: function(keyword) {
		var me = this,
			length = me.tplList[me.boardId].length,
			tpl = me.tplList[me.boardId],
			keyword = keyword.toLowerCase();
		$('#search-list').empty();
		
		if (length) {
			for (var i = 0, c = length; i < c; i++) {
				if (tpl[i][1].toLowerCase().indexOf(keyword) >= 0) {
					$('#search-list').append('<a href="javascript:void(0)" class="menu_item" onclick="Tudu.Template.showContent(\''+tpl[i][0]+'\');">'+tpl[i][1]+'</a>');
				}
			}
		}
		
		$('#tpl-list').hide();
		$('#search-list').show();
	},
	
	// 获得模板的名称及Id，填充目录
	appendMenu: function(_o) {
		var html = [],
		    me = this;
		$.ajax({
			type: 'GET',
			dataType: 'json',
			data: {bid: me.boardId},
			url: '/tudu/tpl.list',
			success: function(ret) {
			   if(ret.success) {
				   var length = ret.data.length;
				   me.tplList[me.boardId] = ret.data;
				   if(length) {
					   for(var i=0; i<=length-1; i++) {
						   html.push('<a href="javascript:void(0)" class="menu_item" onclick="Tudu.Template.showContent(\''+ret.data[i][0]+'\');">'+ret.data[i][1]+'</a>');
					   }
					   me.list[me.boardId] = html.join('');
					   $('#tpl-list').html(me.list[me.boardId]);
				   } else {
					   me.list[me.boardId] = '<p style="padding:10px 5px;">' + TOP.TEXT.NOTHING_TPL + '</p>';
					   $('#tpl-list').html(me.list[me.boardId]);
				   }
			   }
			   
			   if ($('#tpl-list a.menu_item').size() > 10) {
				   $('#tpl-list, #search-list').css({'height': '200px'});
			   }
			},
			error: function(res) {
			    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
	},
	// 填充模板内容到表单控件
	showContent: function(tplId) {
		var me = this;
		if(me.content[tplId] != null) {
			if(me.textarea == "ch-content") {
				var value = $('#ch-content').val();
			    $('#ch-content').val(value+me.content[tplId]);
			} else {
			    var value = $('#content').val();
			    $('#content').val(value+me.content[tplId]);
		    }
		} else {
			Tudu.Template.appendContent(tplId);
		}
	},
	
	// 通过模板ID，获得模板内容
	appendContent: function(tplId) {
		var me = this;
		$.ajax({
			type: 'GET',
			dataType: 'json',
			data: {bid: me.boardId, tplid: tplId},
			url: '/tudu/tpl.content',
			success: function(ret) {
			   if(ret.success) {
				   me.content[tplId] = ret.data;
				   if(me.textarea == "ch-content") {
					   var value = $('#ch-content').val();
					   $('#ch-content').val(value+me.content[tplId]);
				   } else {
					   var value = $('#content').val();
					   $('#content').val(value+me.content[tplId]);
				   }
			   }
			},
			error: function(res) {
			    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
	}
	
};


//处理附件img标签
//处理 base64格式 img标签
function checkContentImage(html, editor, callback) {
	// 检查是否存在base64格式src图片标签
	var b64reg = /<img[^>]+src="data:image\/\w+;base64,([^">]+)"([^>]+)\/>/ig;
	var arr  = html.match(b64reg);
	
	if (!arr || !arr.length) {
		return true;
	}
	
	var win = TOP.Frame.Dialog.show({
		body:  '<div>' + TOP.TEXT.IMG_UPLOADING + '</div><div class="progress_upload"></div>',
		title: TOP.TEXT.FILE_UPLOADING,
		close: false
	}).getWin();
	
	win.show();
	
	// 显示上传窗口
	var complete = 0,
		count    = arr.length,
		imgs     = [];
	for (var i = 0; i < count; i++) {
		var img  = arr[i],
			data = img.match(/data:image\/\w+;base64,[^"]+/ig);
		
		imgs[imgs.length] = img;

		if (!data.length) {
			++complete;
			continue ;
		}
		
		data = data[0].split(',')[1];
		
		$.ajax({
			url: '/attachment/proxy',
			type: 'POST',
			dataType: 'json',
			data: {data: data, label: i},
			success: function(ret) {
				
				if (ret.success && ret.data) {
					var img = imgs[ret.data.label];
					var tag = img
						.replace(/src="[^"]+"/, 'src="/attachment/img?fid=' + ret.data.fileid + '"')
						.replace('\>', '_aid="'+ret.data.fileid+'" \>');
					
					html = html.replace(img, tag);
					editor.setSource(html);
				} else {
					TOP.showMessage(ret.message);
				}
				
				if (++complete >= count && typeof callback == 'function') {
					win.close();
					callback();
				}
			},
			error: function(res) {
				if (++complete >= count && typeof callback == 'function') {
					win.close();
				}
				
				TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
				return false;
			}
		});
		
		if (complete >= count) {
			callback();
		}
	}
}