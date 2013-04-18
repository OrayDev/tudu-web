// tudu 详细页面公用

if (typeof(getTop) != 'function') {
	function getTop() {
	    return parent;
	}
}

var TOP = TOP || getTop(),
	_REPLY_EDITOR = null,
	_UPLOAD = null,
	_TUDU_ID = '',
	_BACK = '',
	_REPLY_DATA = null;

// 标记为
function markTudu(tuduId, fun) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/mark',
        data: {tid: tuduId, fun: fun},
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, 'success');
            if (ret.data && ret.data.labels) {
                var labels = ret.data.labels,
                    nums = {};
                for (var k in labels) {
                	if (labels[k].labelalias != 'todo') {
                		nums[labels[k].labelalias] = labels[k].unreadnum;
                	}
                	if (labels[k].labelalias == 'todo') {
                		nums[labels[k].labelalias] = labels[k].totalnum+'|'+labels[k].unreadnum;
                    }
                }
                TOP.Frame.updateLabels(nums);
            }
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}

function markStar(tuduId, obj) {
	var star = $(obj),
		isstar = star.hasClass('attention');
	
	star.toggleClass('attention');
	
	var func = isstar ? 'unstar' : 'star';
	return markTudu(tuduId, func);
}

// 删除tudu
function deleteTudu(tuduId, back) {
	if (!confirm(TOP.TEXT.CONFIRM_DELETE_TUDU)) {
		return false;
	}

	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: '/tudu-mgr/delete?tid=' + tuduId,
		success: function(ret) {
		   TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
		   if (ret.success) {
			    location = back;
		   }
		},
		error: function(res) {
		    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
		}
	});
}

function deleteContact(contactId) {
	if (!confirm(TOP.TEXT.CONFIRM_DELETE_CONTACT)) {
		return false;
	}
	
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: '/contact/delete?ctid=' + contactId,
		success: function(ret) {
		   TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
		   if (ret.success) {
			    $('#contact-' + contactId).remove();
		   }
		},
		error: function(res) {
		    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
		}
	});
}

// 更新tudu状态
function state(tuduId, act, params) {
    var data = {tid: tuduId};

    if (!params) params = {};
    for (var k in params) {
        data[k] = params[k];
    }
	
    $.ajax({
        type: 'POST',
        data: data,
        dataType: 'json',
        url: '/tudu-mgr/' + act,
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
}

// 添加标签
function addLabel(tuduId, label, jump) {
    if (!tuduId) {
        tuduId = getSelectId();
    }

    if (!tuduId.length) {
        return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {label: label, tid: tuduId, fun: 'add'},
        url: '/tudu-mgr/label',
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, 'success');
            if (jump) {
            	location = jump;
            } else {
            	location.reload();
            }
        },
        error: function(res){}
    });
}

// 移除标签
function deleteLabel(tuduId, label, labelid) {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: {label: label, tid: tuduId, fun: 'del'},
        url: '/tudu-mgr/label',
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, 'success');
            $('#label-' + labelid).remove();
        },
        error: function(res){}
    });
}

function ajustReplyContent() {
	$('div.post-content>li').css({'margin-left': '25px'});
}

// 初始化回复编辑器
function initReplyEditor() {
	if (TOP.Device.Android || TOP.Device.iOS) {
		var editor =  $(id);
		editor.getSource = function(){return $(id).val()};
		editor.setSource = function(content){return $(id).val(content);};
		return editor
	}
	
	_REPLY_EDITOR = $('#content').xheditor({
		tools: 'Fontface,FontSize,Separator,Bold,Italic,Underline,Strikethrough,Separator,FontColor,BackColor,Separator,SelectAll,Removeformat,Align,List,Outdent,Indent,Separator,Link,Unlink,Table,Source,Fullscreen',
        loadCSS: '/css/common.css',
        skin: 'tudu',
        wordDeepClean: false,
        cleanPaste: 0,
        hoverExecDelay: -1,
        shortcuts: {'ctrl+enter': function(){replySubmit('#replyform');}}
    });
	
	$('table.xheLayout').css('width', '100%');
	
	return _REPLY_EDITOR;
}

function removeAttach(aid) {
	$('#attach-' + aid).remove();
	if (!$('#attach-list div.filecell').size()) {
		$('#attach-list').hide();
	}
}

/**
 * 文件上传
 * @return
 */
function initAttachment(params) {
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
	handler.list = $('#attach-list');
	handler.container = $('#attach-list td.bd_upload');
	handler.setUpload(upload);
	
	return upload;
}

// 引用
function reference(postid) {
	$('#reply-table').show();
	$('#input-table').hide();
	
    var html = '<div class="cite_wrap"><strong>{0}</strong><p class="gray">{1}</p>{2}</div><p>&nbsp;</p>';
    var post = $('#post-' + postid);
    var poster = post.find('strong.poster').attr('_poster'),
        floor  = post.find('.floor').text(),
        content = post.find('.post-content').html();
    
    floor = floor ? floor : TOP.TEXT.FIRST_POST;
    html = TOP.formatString(html, TOP.TEXT.REFERENCE, poster + floor, content);
	
	window.scrollTo(0,$('#replyform').offset().top);
    _REPLY_EDITOR.pasteHTML(html);
    _REPLY_EDITOR.focus();
}

// 回复
function replyPost(postid) {
	$('#reply-table').show();
	$('#input-table').hide();
	
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
	_REPLY_EDITOR.pasteHTML(html);
	_REPLY_EDITOR.focus();
}

function goReply(tuduId) {
	location = '/tudu/post?tid=' + tuduId;
}

// 编辑回复
function modifyPost(tuduId, postId) {
	location = '/tudu/post?tid=' + tuduId + '&pid=' + postId;
}

// 检查编辑器是否为空
function editorCheckNull() {
	var content = _REPLY_EDITOR.getSource(),
        ct = $('<div>').html(content.replace(/&nbsp;/g, ''));
	
	var text = ct.text();
	text = text.replace(/\s/g, '').replace(/\n/g, '').replace(/\r/g, '');

	return text.length > 0 || ct.find('img').size();
}

// 回复内容是否为空（包括附件，进度等）
function isNullReply() {
	return (!editorCheckNull()
			&& !$('#attach-list div.filecell').size()
			&& !$('#elapsedtime').val());
}

// 删除回复
function deletePost(tuduId, postId) {
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
}

function togglePost(postId, icon) {
	var icon = $(icon);
	
	var expanded = !icon.hasClass('icon_unfold');
	
	if (expanded) {
		$('#post-' + postId + ' div.tudu-content-body').hide();
		icon.addClass('icon_unfold');
	} else {
		$('#post-' + postId + ' div.tudu-content-body').show();
		icon.removeClass('icon_unfold');
	}
}

// 添加到图度箱
function inboxTudu(tuduId) {
    if (!tuduId) {
        tuduId = getSelectId();
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
}

// 忽略
function ignoreTudu(tuduId, type) {
	if (!tuduId) {
        tuduId = getSelectId();
    }

    if (!tuduId.length) {
    	return TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/tudu-mgr/ignore',
        data: {tid: tuduId, type: type},
        success: function(ret) {
            TOP.showMessage(ret.message, 3000);
            
            if (ret.success) {
                location.reload();
            }
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}

function whileUploading(msg, completeCallback, form) {
	if (null != _UPLOAD) {
		if (_UPLOAD.isUploading()) {
			$(form).find(':input').attr('disabled', true);
			
			_UPLOAD.setParam('upload_complete_handler', function(){
				var stats = this.getStats();
				if (stats.files_queued == 0 && !stats.in_progress) {
					$(form).find(':input').attr('disabled', false);
					if (typeof(completeCallback) == 'function') {
						completeCallback();
					}
				}
			});
			
			var message = [
			    '<div class="msg-progress" id="msg-progress"><div></div></div><span id="msg-txt-progress">0%</span>',
			    msg,
			    ' [<a href="javascript:void(0);">' + TOP.TEXT.CANCEL + '</a>]'
			].join('');
			
			TOP.showMessage(message, 0, 'success');
			var progress = _UPLOAD.totalProgress();
			TOP.getJQ()('#msg-progress div').width(progress + '%');
			TOP.getJQ()('#msg-txt-progress').text(progress + '%');
			
			TOP.getJQ()('#result-top a').click(function(){
				TOP.showMessage();
				_UPLOAD
				.setParam('upload_complete_handler', function(){})
				.setParam('upload_progress_handler', function(file, uploaded, total){
					_UPLOAD.onProgress.call(this, file, uploaded, total);
				});
				$(form).find(':input').attr('disabled', false);
			});
			
			return false;
		}
	}
	
	return true;
}

// 提交回复
function replySubmit(form, callback) {
	form = $(form);
	form.find('input[name="type"]').val('reply');
	
	if ($('#updateprogress:checked').size()) {
        if (!$('#elapsedtime').val()) {
            $('#elapsedtime').focus();
            return alert(TOP.TEXT.ELASPED_TIME_IS_NULL);
        }

        if (!$('#percent').val()) {
            $('#percent').focus();
            return alert(TOP.TEXT.PERCENT_IS_NULL);
        }
    }

    if (!editorCheckNull()) {
    	_REPLY_EDITOR.focus();
        return TOP.showMessage(TOP.TEXT.POST_CONTENT_IS_NULL);
    }
	
	if (!whileUploading(TOP.TEXT.WAITING_UPLOAD, function(){replySubmit(form);}, form)) {
		return ;
	}
	
	if ($('#attach-list div.upload_error').size()) {
		if (!confirm(TOP.TEXT.REPLY_UPLOAD_FAILURE)) {
			return ;
		}
	}
	
	// 处理图片
	var src = _REPLY_EDITOR.getSource();
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
            TOP.showMessage(ret.message, 5000, 'success');
            if (ret.success) {
            	if (typeof(callback) == 'function') {
                	return callback();
                }
            	
            	location = '/tudu/view?tid='+_TUDU_ID+'&page=last&bak='+_BACK+'&reload=1';
            	
            } else {
            	form.find(':input').attr('disabled', false);
            }
        },
        error: function(res) {
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
            form.find(':input').attr('disabled', false);
        }
    });
}

// 插入图片
function initPicInsert(ele, uploadParams) {
	var auth = uploadParams ? uploadParams.auth : null,
		picupload = null,
		picup  = null;
	var d = menuDialog('pic-dia', {
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
    				_REPLY_EDITOR.loadBookmark();
    				_REPLY_EDITOR.pasteHTML('<img src="'+url+'" alt="" />', true);
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
						_REPLY_EDITOR.loadBookmark();
						
						for (var i = 0, c = this._success.length; i < c; i++) {
							var aid = this._success[i].aid,
								url = '/attachment/img?fid=' + aid,
								html = '<img src="'+ url +'" _aid="'+aid+'" />';
	                		_REPLY_EDITOR.loadBookmark();
	                		_REPLY_EDITOR.pasteHTML(html);
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
	.mousedown(function(e){_REPLY_EDITOR.saveBookmark();TOP.stopEventBuddle(e);})
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
}

//显示下拉功能框
function menuDialog(id, params) {
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
}

// 显示日志
function toggleLog(tuduId) {
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
}

// 聊天
function chat(email) {
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
}

//检查编辑器是否为空
function editorIsNull(editor) {
	var content = editor.getSource(),
        ct = $('<div>').html(content.replace(/&nbsp;/g, ''));
	
	var text = ct.text();
	text = text.replace(/\s/g, '').replace(/\n/g, '').replace(/\r/g, '');

	return text.length == 0 && ct.find('img').size() == 0;
}

function initUnloadEvent(form) {
	form = $(form);
	_REPLY_DATA = _getFormVal();
	TOP.getJQ()('a:not([href^="javascript:"])').bind('click', _leaveDialog);
	TOP.getJQ()('form').bind('submit', _leaveDialog);
	$('a:not([href^="javascript:"]):not(#link-fullreply):not([target="_blank"])').bind('click', _leaveDialog);
	TOP.window.onbeforeunload = function() {
		if (!_checkForm()) {
			return TOP.TEXT.COMPOSE_EXIT_WITHOUT_SAVE;
		}
	};
	window.onunload = function(){
		TOP.getJQ()('a').unbind('click', _leaveDialog);
		TOP.getJQ()('form').unbind('submit', _leaveDialog);
	};
	
	function _leaveDialog(e) {
		$('#content').val(_REPLY_EDITOR.getSource());
		
		if (_compare(_getFormVal(), _REPLY_DATA)) {
			return true;
		}
		
		var trigger = $(this);
		
		TOP.Frame.switchFolder();
		var d = TOP.Frame.Dialog.show({
			title: TOP.TEXT.LEAVE_HINT,
			body: '<p style="padding:15px 0;margin-left:-12px"><strong>' + TOP.TEXT.REPLY_EXIT_SAVE_HINT + '</strong></p>',
			buttons: [{
				   text: TOP.TEXT.SEND_REPLY,
				   cls: 'btn',
				   events: {click: function(){
						replySubmit('#replyform', function(){
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
	
	function _getFormVal() {
		var form = $('#replyform');
		var r = {};
		form.find('select, textarea:not(#content, #postcontent), input:not(#tid):not(#bid):not(#type):not(#action):not(#savetime)').each(function(){
			if (!this.name) {
				return ;
			}
			if (this.name.indexOf('[]') != -1) {
				if (!r[this.name]) {
					r[this.name] = [];
				}
				r[this.name].push(this.value);
			} else {
				r[this.name] = this.value;
			}
		});
		r['content'] = _REPLY_EDITOR.getSource();
		
		return r;
	}
	
	function _compare(v1, v2) {
		var l1 = 0, l2 = 0;
		var l1 = 0, l2 = 0, vl1, vl2;
		for (var k in v1) {
			vl1 = v1[k].constructor == window.Array ? v1[k].join(',') : v1[k];
			vl2 = v2[k] && v2[k].constructor == window.Array ? v2[k].join(',') : v2[k];
			
			if (vl1 != vl2) {
				return true;
			}
			l1++;
		}
		
		for (var k in v2) {
			l2++;
		}
		
		return l1 == l2;
	}
	
	function _checkForm() {
		$('#content').val(_REPLY_EDITOR.getSource());
		
		return _compare(_getFormVal(), _REPLY_DATA);
	}
}

var Tudu = Tudu || {};

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
						
						if (ret.success) {
							_o.load(Win.find('div.pop_body'));
						}
					},
					error: function(res) {
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
}

/**
 * 加载模板
 */
var Tudu = Tudu || {};

Tudu.Template = {
	// 显示目录
	menu: null,
	// 板块模板目录列表
	list: {},
	// 模板内容
	content: {},
	
	boardId: null,

	showMenu: function(e) {
		var me = this;
		this.boardId = $('#board').val();
		if (null == this.menu) {
			this.menu = new $.Dropdown({
				id: 'tpl-picker',
				menuBody: '<div class="option_menu_body"><div class="option_menu_title">'+TOP.TEXT.SELECT_TPL+'</div><p class="gray" style="padding:0 4px;">'+TOP.TEXT.TPL_TIPS+'</p><div id="tpl-list"></div></div>',
				maxWidth: '220px',
				menuCss: {marginLeft: 0},
				alwaysBottom:true,
				onShow: function() {
					if(me.boardId) {
						$('#tpl-list').html('<p style="padding:10px">' + TOP.TEXT.LOADING_TPL + '</p>');
						if(me.list[me.boardId] != null) {
							$('#tpl-list').html(me.list[me.boardId]);
						}else {
							Tudu.Template.appendMenu();
						}
					}
				},
				onHide: function() {
					$('#tpl-list').empty();
				}
		    });
		}
		
		this.menu.show(e);
	},
	
	appendMenu: function() {
		var html = [],
		    me = this;
		$.ajax({
			type: 'GET',
			dataType: 'json',
			data: {bid: me.boardId},
			url: '/tudu-mgr/show.tpl',
			success: function(ret) {
			   if(ret.success) {
				   var length = ret.data.length;
				   if(length) {
					   for(var i=0; i<=length-1; i++) {
						   html.push('<a href="javascript:void(0)" class="menu_item" onclick="Tudu.Template.showContent(\''+ret.data[i][0]+'\');">'+ret.data[i][1]+'</a>');
					   }
					   me.list[me.boardId] = html.join('');
					   $('#tpl-list').html(me.list[me.boardId]);
				   } else {
					   me.list[me.boardId] = '<p style="padding:10px">' + TOP.TEXT.NOTHING_TPL + '</p>';
					   $('#tpl-list').html(me.list[me.boardId]);
				   }
			   }
			},
			error: function(res) {
			    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
	},
	
	showContent: function(tplId) {
		var me = this;
		if(me.content[tplId] != null) {
			var value = $('#content').val();
			$('#content').val(value+me.content[tplId]);
		} else {
			Tudu.Template.appendContent(tplId);
		}
	},
	
	appendContent: function(tplId) {
		var me = this;
		$.ajax({
			type: 'GET',
			dataType: 'json',
			data: {bid: me.boardId, tplid: tplId},
			url: '/tudu-mgr/show.content',
			success: function(ret) {
			   if(ret.success) {
				   me.content[tplId] = ret.data;
				   var value = $('#content').val();
				   $('#content').val(value+me.content[tplId]);
			   }
			},
			error: function(res) {
			    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
	}
};

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
				_o.list.hide();
			}
		});
		
		_o.list.append(el);
		_o.listCt.show();
	}
};

/**
 * 自动保存 提交图度回复数据
 */
Tudu.PostSubmit = function() {
	var me = this,
		form = $(this.settings.form);
	form.find('input[name="type"]').val('save');
	
	if (!form.find('input[name="tid"]').val()) {
		return ;
	}
	if (_UPLOAD.isUploading()) {
		return ;
	}
	// 处理图片
    var src = _REPLY_EDITOR.getSource();
    var reg = /(<img[^>]+src="([^"]+)"\s+_aid="([^"]+)"[^>]+\/>)/ig;
    form.find(':hidden[name="file[]"]').remove();
    while ((result = reg.exec(src)) != null) {
    	src = src.replace(result[0], result[0].replace(result[2], 'AID:' + result[3]));
    	form.append('<input type="hidden" name="file[]" value="'+result[3]+'" />');
    }
    src = src.replace(/\s+id="[^"]+"/g, '');
	$('#postcontent').val(src);
	
	var data = form.serializeArray();
	
    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: data,
        url: form.attr('action'),
        success: function(ret) {
	    	if (ret.data) {
                form.find('input[name="fpid"]').val(ret.data.postid);
                form.find('input[name="action"]').val('modify');
            }
            if (ret.success) {
            	me.getTime();
            	me.writeMsg();
            	$('#savetime').val(parseInt((new Date()).getTime() / 1000));
            }
        },
        error: function(res) {
        }
    });
};

/**
 * 自动保存回复
 */
Tudu.AutoSave = function(params) {
	this.setParam(params);
	this.getData(params);
	this.init();
};

Tudu.AutoSave.prototype = {
	// 保存的数据
	data: null,
	
	// 自动保存的详细时间 用于计算时间间隔
	time: null,
	
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
	 * 重新获取表单数据
	 */
	getData: function(key) {
		if (typeof(key) == 'string') {
			_REPLY_DATA = this.getFormVal();
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
		
		if (null == _REPLY_DATA) {
			_REPLY_DATA = {};
		}
		
		_REPLY_DATA = me.getFormVal();
		me.checkSave();
	},
	
	/**
	 *  检查是否执行保存操作
	 */
	checkSave: function() {
		var me = this;
		
		setTimeout(function(){
			me.checkSave();
		}, this.settings.time);
		
		if (me.checkFormVal() === false) {
			me.writeMsg();
			return ; 
		}
		
		_REPLY_DATA = me.getFormVal();
		
		this.settings.func.call(this);
	},
	// 写提示信息
	writeMsg: function() {
		if (this.timeValue) {
			var newtime = new Date(),
				t = (newtime - this.time)/(1000*60),
				timediff = parseInt(t);

			$('#autosave').html(TOP.TEXT.AUTOSAVE_TIPS_FIRST + this.timeValue + ' ( ' + timediff + TOP.TEXT.AUTOSAVE_TIPS_SECOND + ' )');
		}
	},
	// 检查表单内容是否有改变
	checkFormVal: function() {
		var me = this,
			newdata = me.getFormVal();
		var l1 = 0, l2 = 0, v1, v2;
		for (var k in newdata) {
			v1 = _REPLY_DATA[k] && _REPLY_DATA[k].constructor == window.Array ? _REPLY_DATA[k].join(',') : _REPLY_DATA[k];
			v2 = newdata[k].constructor != window.Array ? newdata[k] : newdata[k].join(',');
			
			if (v1 != v2) {
				return true;
			}
			l1++;
		}
		
		for (var k in _REPLY_DATA) {
			l2++;
		}
		if (l1 != l2) {return true;}
		
		return false;
	},
	// 获取表单内容
	getFormVal: function() {
		var form = $(this.settings.form);
		var r = {};
		form.find('select, textarea:not(#content, #postcontent), input:not(#tid):not(#bid):not(#type):not(#action):not(#savetime)').each(function(){
			if (!this.name) {
				return ;
			}
			if (this.name.indexOf('[]') != -1) {
				if (!r[this.name]) {
					r[this.name] = [];
				}
				r[this.name].push(this.value);
			} else {
				r[this.name] = this.value;
			}
		});
		r['content'] = _REPLY_EDITOR.getSource();
		
		return r;
	},
	// 获取保存图度时的时间
	getTime: function() {
		var now = new Date(),
			hours = now.getHours(),
			minutes = now.getMinutes(),
			timeValue = ((hours < 10) ? "0" : "") +  hours
			timeValue += ((minutes < 10) ? ":0" : ":") + minutes;

		this.time = now;
		this.timeValue = timeValue;
		
		return timeValue;
	}
};