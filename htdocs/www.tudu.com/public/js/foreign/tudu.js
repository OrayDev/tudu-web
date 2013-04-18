// tudu 详细页面公用

if (typeof(getTop) != 'function') {
	function getTop() {
	    return top;
	}
}

var TOP = getTop(),
	_REPLY_EDITOR = null,
	_UPLOAD = null,
	_TUDU_ID = '',
	_BACK = '';

TOP._FILECGI = TOP._FILECGI || {
	swfupload: null,
	upload: null
};

var FixToolbar = function(params) {
    if (!params.src || !params.target) {
        return ;
    }
    var me = this;
    this.src    = typeof params.src == 'string' ? $(params.src) : params.src;
    this.target = typeof params.target == 'string' ? $(params.target) : params.target;

    if (params.width && undefined !== params.width) {
    	this.target.css('width', params.width + 'px');
    } else {
    	this.target.css('width', $(document.body).width() + 'px');
    }
    _showTarget();

    $(window).bind('scroll', function(){
    	_showTarget();
    }).bind('resize', function(){
    	if (params.width && undefined !== params.width) {
        	me.target.css('width', params.width + 'px');
        } else {
        	me.target.css('width', $(document.body).width() + 'px');
        }
    });

    function _showTarget() {
    	var scrollTop = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop;

        if (me.src[0].offsetTop < scrollTop) {
            me.target.show();
        } else {
            me.target.hide();
        }
    };
};

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
        url: '/foreign/tudu-mgr/' + act,
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

// 审批
function reviewTudu(tuduId, isAllow, uid, tsId) {
	var type = true;
	if (isAllow == 'disagree') {
		type = false;
	}
	$.ajax({
        type: 'POST',
        data: {tid: tuduId, type: type, fid: uid, ts: tsId},
        dataType: 'json',
        url: '/foreign/tudu-mgr/review',
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

// 认领
function claimTudu(tuduId, uid, tsId) {
	$.ajax({
        type: 'POST',
        data: {tid: tuduId, fid: uid, ts: tsId},
        dataType: 'json',
        url: '/foreign/tudu-mgr/claim',
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

function ajustReplyContent() {
	$('div.post-content>li').css({'margin-left': '25px'});
}

// 初始化回复编辑器
function initReplyEditor() {
	_REPLY_EDITOR = $('#content').xheditor({
		tools: 'Fontface,FontSize,Separator,Bold,Italic,Underline,Strikethrough,Separator,FontColor,BackColor,Separator,SelectAll,Removeformat,Align,List,Outdent,Indent,Separator,Link,Unlink,Table,Source,Fullscreen',
        loadCSS: '/css/common.css',
        skin: 'tudu',
        wordDeepClean: false,
        hoverExecDelay: -1,
        shortcuts: {'ctrl+enter': function(){replySubmit('#replyform');}}
    });
	
	$('table.xheLayout').css('width', '100%');
	
	return _REPLY_EDITOR;
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
    var poster = post.find('.poster').text(),
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
	        poster = post.find('.poster').text(),
	        floor  = post.find('.floor').text();
	    floor = floor ? floor : TOP.TEXT.FIRST_POST;
	    
	    html = TOP.formatString(html, TOP.TEXT.REPLY, floor, poster);
	}
	
	window.scrollTo(0,$('#replyform').offset().top);
	_REPLY_EDITOR.pasteHTML(html);
	_REPLY_EDITOR.focus();
}

function goReply(tuduId, ts, fid) {
	location = '/foreign/tudu/post?tid=' + tuduId + '&ts=' + ts + '&fid=' + fid;
}

// 编辑回复
function modifyPost(tuduId, postId, ts, fid) {
	location = '/foreign/tudu/post?tid=' + tuduId + '&pid=' + postId + '&ts=' + ts + '&fid=' + fid;
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
	var tmpdiv = $('<div>').html(_REPLY_EDITOR.getSource());
    tmpdiv.find('img').each(function(){
    	var o = $(this);
    	if (o.attr('_aid')) {
    		o.attr('src', 'AID:' + o.attr('_aid'));
    		form.append('<input type="hidden" name="file[]" value="'+o.attr('_aid')+'" />');
    	}
    });
    tmpdiv.find('*[id]').each(function(){
		$(this).removeAttr('id');
	});
	$('#content').val(tmpdiv.html());
	
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
            	
            	location.reload();
            	
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
		picup  = null,
		picurl = uploadParams.picurl ? uploadParams.picurl : '/foreign/attachment/img';
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
    		
    		$('#pic-modal button[name="cancel"]').click(function(){
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
								url = picurl + '&aid=' + aid,
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
function toggleLog(tuduId, ts, fid) {
	var panel = $('#log-panel');
	if ($('#log-panel:visible').size()) {
		panel.hide();
	} else {
		panel.show();
		if (!$('#log-table').size()) {
			$('#log-list').html('<span style="margin:10px">' + TOP.TEXT.LOADING_LOG + '</span>')
			.load('/foreign/tudu/log?tid=' + tuduId + '&ts=' + ts + '&fid=' + fid);
		}
	}
}

// 显示附件列表
function toggleAttach(tuduId, ts, fid) {
	var panel = $('#attach-panel');
	if ($('#attach-panel:visible').size()) {
		panel.hide();
	} else {
		panel.show();
		if (!$('#attach-table').size()) {
			$('#tudu-attach-list').html('<span style="margin:10px">' + TOP.TEXT.LOADING_ATTACH + '</span>')
			.load('/foreign/tudu/attach?tid=' + tuduId + '&ts=' + ts + '&fid=' + fid);
		}
	}
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
	
	TOP.window.onbeforeunload = function() {
		if (!editorIsNull(_REPLY_EDITOR)) {
			return TOP.TEXT.COMPOSE_EXIT_WITHOUT_SAVE;
		}
	};
}