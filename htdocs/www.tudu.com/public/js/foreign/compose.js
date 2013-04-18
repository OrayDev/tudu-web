if (typeof(getTop) != 'function') {
	function getTop() {
	    return top;
	}
}

var TOP = getTop(),
	_CAST_WIN = null,
	_MI_TO = null,
	_MI_CC = null,
	_FORM_DATA = null,
	_EDITOR = null,
	_START_PICKER = null,
	_END_PICKER = null,
	_UPLOAD = null,
	_CLASSES = {};

TOP._FILECGI = TOP._FILECGI || {
	swfupload: null,
	upload: null
};

/**
 * 初始化编辑器
 * @param id
 * @return
 */
function initEditor(id) {
	var editor = $(id).xheditor({
        tools: 'Fontface,FontSize,Separator,Bold,Italic,Underline,Strikethrough,Separator,FontColor,BackColor,Separator,SelectAll,Removeformat,Align,List,Outdent,Indent,Separator,Link,Unlink,Table,Source,Fullscreen',
        loadCSS: '<style type="text/css">p{margin:0;padding:0}</style>',
        skin: 'tudu',
        wordDeepClean: false,
        hoverExecDelay: -1,
        shortcuts: {'ctrl+enter': function(){$('button[name="send"]:eq(0)').click();}}
    });
	
	$('table.xheLayout').css('width', '100%');
	
	return editor;
}

/**
 * 禁用编辑器 － XXX
 * @return
 */
function disableEditor(index) {
	var iframe = document.getElementById('xhe'+index+'_iframe').contentWindow;
	if ($.browser.msie) {
		iframe.document.body.contentEditable = false;
	} else {
		iframe.document.designMode = 'off';
	}
	
	$('#xhe' + index + '_Tool a.xheEnabled').removeClass('xheEnabled');
}

/**
 * 加载主题分类
 * 
 * @param bid
 * @return
 */
function loadClasses(bid, select) {
	if (!bid) {
		return _fillSelect([]);
	}
	
	if (typeof(_CLASSES[bid]) == 'undefined') {
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: '/tudu/classes?bid=' + encodeURIComponent(bid),
			success: function(ret) {
				if (ret.success) {
					_CLASSES[bid] = ret.data;
					_fillSelect(_CLASSES[bid], select);
				}
			},
			error: function(res) {
				return ;
			}
		});
	} else {
		_fillSelect(_CLASSES[bid], select);
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
		for (var i = 0, c = ret.length; i < c; i++) {
			o.append('<option value="'+ret[i].classid+'" title="'+ret[i].classname+'">'+ret[i].classname+'</option>');
		}
		
		o.attr('disabled', false);
	}
}

/**
 * 时间选择器
 * 
 * @return
 */
function initTimePicker() {
	_START_PICKER = $('#starttime').datepick({
        showOtherMonths: true,
        selectOtherMonths: true,
        showAnim: 'slideDown',
        showSpeed: 'fast',
        firstDay: 0,
        onSelect: function(dates){
			$('#endtime').datepick('option', {minDate: dates});
			
			if ($('#vote:checked').size()) {
				$('#endtime').val(calEndTime($('#starttime').val()));
			}
		}
    });
	
    _END_PICKER = $('#endtime').datepick({
        minDate: new Date(),
        showOtherMonths: true,
        selectOtherMonths: true,
        showAnim: 'slideDown',
        showSpeed: 'fast',
        firstDay: 0,
        onSelect: function(dates){$('#starttime').datepick('option', {maxDate: dates});}
    });
}

/**
 * 文件上传
 * @return
 */
function initAttachment(params, list, container) {
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
	.mouseover(function(){$('#upload-link, #ch-upload-link').css('text-decoration', 'underline');})
	.mouseout(function(){$('#upload-link, #ch-upload-link').css('text-decoration', 'none');});
	
	var upload = new TuduUpload(config);
	var handler = new Tudu.Attachment();
	handler.list = list;
	handler.container = container;
	handler.setUpload(upload);
	
	return upload;
}

/**
 * 检查联系人输入框联系人合法性
 * 
 * @param item
 * @param input
 * @return
 */
function checkInputUser(item, input) {
	var instance = this;
	
    TOP.Cast.load(function(data){
    TOP.Contact.load(function(data){
        var users = TOP.Cast.get('users'),
        	groups= TOP.Cast.get('groups'),
        	contacts = TOP.Contact.get('contacts'),
        	contactGroups = TOP.Contact.get('groups'),
            text = item.text().replace(instance.separator, ''),
            identify = item.attr('_id'),
            matched = false;
        
        filter:
        do {
    		for (var i = 0, c = users.length; i < c; i++) {
	            if (text == users[i].truename || text == users[i].address) {
                	identify = users[i].address;
                    item.attr({name: users[i].truename, _id: users[i].address, title: users[i].address});
                    text = users[i].truename;
                    matched = true;
                    break filter;
	            }
	        }
	        
	        for (var i = 0, c = contacts.length; i < c; i++) {
	        	if (text == contacts[i].truename || text == contacts[i].email) {
                	identify = contacts[i].email;
                    item.attr({name: contacts[i].truename, _id: contacts[i].email, title: contacts[i].address, _extend: contacts[i].contactid});
                    text = contacts[i].truename;
                    matched = true;
                    break filter;
	            }
	        }
	        
    	    if (!TOP.isEmail(text)) {
        		for (var i = 0, c = groups.length; i < c; i++) {
    	        	if (text == groups[i].groupname) {
	        			item
	        			.attr({name: groups[i].groupname, _id: groups[i].groupid, title: TOP.TEXT.GROUP + ':' + name})
	        			.addClass('mail_item_group');
	        			text = groups[i].groupname;
	    	        	matched = true;
    	        	}
    	        }
        		
        		for (var i = 0, c = contactGroups.length; i < c; i++) {
        			if (text == contactGroups[i].groupname) {
	        			item
	        			.attr({name: contactGroups[i].groupname, _id: groups[i].groupid, title: TOP.TEXT.GROUP + ':' + name})
	        			.addClass('mail_item_group');
	        			text = contactGroups[i].groupname
	        			matched = true;
		        	}
        		}
        	}
        	
        } while (false);
        
        if (!matched) {
        	var attr = {};
        	if (TOP.isEmail(text)) {
        		attr['_id'] = text;
        		attr['name'] = text.split('@')[0];
        	} else {
        		attr['_id'] = '';
        		attr['name'] = text;
        	}
        	attr['title'] = attr['name'];
        	item.attr(attr);
        }
        
        item.text(text);
        if (input.val()) {
        	var v = input.val();
        	
        	v = v.split("\n");
        	if (item.parent().find('.mail_item[name="'+text+'"][_id="'+identify+'"]').size() > 1) {
        		item.remove();
        		return ;
        	}
        }
        
        updateInputVal(input, instance.target, instance.getItems(':not(.mail_item_error)'));
    });
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
						completeCallback.call(this);
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

//检查编辑器是否为空
function editorCheckNull(editor) {
	if (editor === undefined) {
		editor = _EDITOR;
	}
	var content = editor.getSource(),
        ct = $('<div>').html(content.replace(/&nbsp;/g, ''));
	
	var text = ct.text();
	text = text.replace(/\s/g, '').replace(/\n/g, '').replace(/\r/g, '');

	return text.length > 0 || ct.find('img').size();
}

function composeSubmit(form, callback) {
	form = $(form);
	
	if (!$('#board').val()) {
        $('#board').focus();
        return TOP.showMessage(TOP.TEXT.BOARD_NOT_APPIONT);
    }
	
    if ($('#action').val() == 'send') {
    	if ($('#board option:selected').attr('_classify') == '1'
    		&& ($('#classid').size() && !$('#classid').val()))
    	{
    		$('#classid').focus();
    		return TOP.showMessage(TOP.TEXT.BOARD_MUST_CLASSIFY);
    	}
    	
        if (!$('#subject').val()) {
            $('#subject').focus();
            return TOP.showMessage(TOP.TEXT.TUDU_SUBJECT_IS_NULL);
        }

        if ($('#to').size() && !$('#to').val()) {
        	if (_MI_TO.getItems('.mail_item_error').size() > 0) {
        		return TOP.showMessage(TOP.TEXT.INVALID_TO_USER);
        	} else {
        		return TOP.showMessage(TOP.TEXT.TO_USER_IS_NULL);
        	}
        }
        
        if($('#open_pwd').attr('checked') == true){
        	if(!$('#password').val()){
        		$('#password').focus();
        		return TOP.showMessage(TOP.TEXT.PASSWORD_NOT_EMPTY);
        	}
        	if(/\s+/g.test($('#password').val())){
        		return TOP.showMessage(TOP.TEXT.PASSWORD_YES_SPACE);
        	}
        	if(/[^\x01-\xff]+/.test($('#password').val())) {
        		return TOP.showMessage(TOP.TEXT.PASSWORD_NOT_Byte);
        	}
        }
    }

    if ($('#cycle:checked').size()) {
        if (!$('#endtime').val()) {
            return TOP.showMessage(TOP.TEXT.CYCLETASK_NEET_ENDTIME);
        }

        if ($('#week:checked').size() && $('#mode-week-1:checked').size() && !$(':checkbox[name="week-1-weeks[]"]:checked').size()) {
            return TOP.showMessage(TOP.TEXT.INVALID_TASK_INTERVAL);
        }
    }
    
    // 处理HTML
    var tmpdiv = $('<div>').html(_EDITOR.getSource());
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
	
	if (!whileUploading(TOP.TEXT.WAITING_UPLOAD, function(){composeSubmit(form);}, form)) {
		return ;
	}
	
	if ($('#attach-list div.upload_error').size()) {
		if (!confirm(TOP.TEXT.COMPOSE_UPLOAD_FAILURE)) {
			return ;
		}
	}
	
	var data = form.serializeArray();

    TOP.showMessage(TOP.TEXT.POSTING_DATA, 0, 'success');
    form.find(':input').attr('disabled', true);
    
    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: data,
        url: form.attr('action'),
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
            form.find(':input:not([_disabled])').attr('disabled', false);
            
            if (ret.data) {
                $('#ftid').val(ret.data);
            }
            
            if (ret.success) {
            	if (typeof(callback) == 'function') {
                	return callback();
                }
            	
            	history.back();
            	return ;
            }
        },
        error: function(res) {
        	form.find(':input:not([_disabled])').attr('disabled', false);
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}

//检查编辑器是否为空
function editorCheckNull() {
	var content = _EDITOR.getSource(),
        ct = $('<div>').html(content.replace(/&nbsp;/g, ''));
	
	var text = ct.text();
	text = text.replace(/\s/g, '').replace(/\n/g, '').replace(/\r/g, '');

	return text.length > 0 || ct.find('img').size();
}

function postSubmit(form, callback) {
	form = $(form);
	
	if ($('#attach-list div.upload_error').size()) {
		if (!confirm(TOP.TEXT.COMPOSE_UPLOAD_FAILURE)) {
			return ;
		}
	}
	
	if (!editorCheckNull()) {
		return TOP.showMessage(TOP.TEXT.POST_CONTENT_IS_NULL);
	}
	
	// 处理图片
    var tmpdiv = $('<div>').html(_EDITOR.getSource());
    tmpdiv.find('img').each(function(){
    	var o = $(this);
    	if (o.attr('_aid')) {
    		o.attr('src', 'AID:' + o.attr('_aid'));
    		form.append('<input type="hidden" name="file[]" value="'+o.attr('_aid')+'" />');
    	}
    	
    	$('#content').val(tmpdiv.html());
    });
	
	if (!whileUploading(TOP.TEXT.WAITING_UPLOAD, function(){postSubmit(form);}, form)) {
		return ;
	}
	
	// 处理图片
    var tmpdiv = $('<div>').html(_EDITOR.getSource());
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

    TOP.showMessage(TOP.TEXT.POSTING_DATA, 0, 'success');
    form.find(':input').attr('disabled', true);
    
    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: data,
        url: form.attr('action'),
        success: function(ret) {
            TOP.showMessage(ret.message, 5000, 'success');
            form.find(':input').attr('disabled', false);
            if (ret.success) {
            	if (typeof(callback) == 'function') {
                	return callback();
                }
            	
                history.back();
            }
        },
        error: function(res) {
            form.find(':input').attr('disabled', false);
            TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
        }
    });
}

/**
 * 更新联系人输入框数据
 * 
 * @param valInput
 * @param textInput
 * @param list
 * @return
 */
function updateInputVal(valInput, textInput, list) {
	var texts = [];
    var vals  = [];
    list.each(function(){
        var o = $(this);
        texts.push(o.attr('name'));
        vals.push(o.attr('_id') + ' ' + o.attr('name'));
    });
    
    valInput.val(vals.join("\n"));
    if (textInput) {
    	textInput.val(texts.join(','));
    }
}

/**
 * 选择联系人连接
 * 
 * @return
 */
function initSelectLink(obj, mailInput, valInput, data) {
	
	$(obj).click(function(){
        var instance = this;
        var title = $(this).text();

        var val = valInput.val();
        var selected = [], userid = null;
        if (val) {
            val = val.split("\n");
            for (var i = 0, c = val.length; i < c; i++) {
            	var a = val[i].split(' ');
            	selected.push({
            		email: a[0].replace(/^#+/, ''),
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
        	draggalbe: true,
        	onShow: function() {
				Win.center();
			},
			onClose: function() {
				Win.destroy();
			}
        });
        
        var selector = new TOP.ForwardSelector({appendTo: Win.find('div.pop_body'), data: data, selected: selected, mailInput: mailInput});
		
		Win.find('button[name="confirm"]').bind('click', function(){
			var se = selector.getSelected();
			
			for (var i = 0, c = se.length; i < c; i++) {
				var p = {};
				if (p.groupid) {
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

/**
 * 离开编辑页面提示 -- TEST
 * 
 * @return
 */
function initUnloadEvent(form, isnew) {
	form = $(form);
	_FORM_DATA = _getFormVal();
	
	$('button[name="save"], button[name="send"]').bind('click', function(){_FORM_DATA = _getFormVal()});
	form.bind('submit', function(){_FORM_DATA = _getFormVal()});
	
	TOP.getJQ()('a:not([href^="javascript:"]):not([href^="/tudu/modify"])').bind('click', _leaveDialog);
	TOP.getJQ()('form').bind('submit', _leaveDialog);
	$('a:not([href^="javascript:"]):not([href^="/tudu/modify"])').bind('click', _leaveDialog);
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
		$('#content').val(_EDITOR.getSource());
		if (_compare(_getFormVal(), _FORM_DATA)) {
			return true;
		}
		
		var trigger = $(this);
		
		TOP.Frame.switchFolder();
		TOP.Frame.Dialog.show({
			title: TOP.TEXT.LEAVE_HINT,
			body: '<p style="padding:15px 0;margin-left:-12px"><strong>' + TOP.TEXT.COMPOSE_EXIT_SAVE_HINT + '</strong></p>',
			buttons: [{
				   text: TOP.TEXT.SAVE,
				   cls: 'btn',
				   events: {click: function(){
						if (isnew) {
							$('#action').val('save');
						}
						composeSubmit('#theform', function(){
							if (trigger[0].tagName.toLowerCase() == 'a') {
					    	   location = trigger.attr('href');
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
				       if (trigger[0].tagName.toLowerCase() == 'a') {
				    	   location = trigger.attr('href');
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
		var r = {};
		form.find('select, textarea, input:not(#ftid):not(#issend)').each(function(){
			r[this.name] = this.value;
		});
		
		return r;
	}
	
	function _compare(v1, v2) {
		var l1 = 0, l2 = 0;
		for (var k in v1) {
			if (v1[k] != v2[k]) {
				return false;
			}
			
			l1++;
		}
		
		for (var k in v2) {
			l2++;
		}
		
		return l1 == l2;
	}
	
	function _checkForm() {
		$('#content').val(_EDITOR.getSource());
		
		return _compare(_getFormVal(), _FORM_DATA);
	}
}

function initPicInsert(editors, uploadParams) {
	if (typeof(editors) == 'string') {
		var o = {};
		o[editors] = _EDITOR;
		editors = o;
	}
	
	var currEditor = null;
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
    				currEditor.loadBookmark();
    				currEditor.pasteHTML('<img src="'+url+'" alt="" />', true);
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
					}
				});
				$('button[name="upload"]').click(function(){
					picup.startUpload();
				});
    		}
    	}
    });
	d.hide();
	
	for (var ele in editors) {
		$(ele)
		.mousedown(function(e){editors['#'+this.id].saveBookmark();TOP.stopEventBuddle(e);})
		.click(function(e){
			currEditor = editors['#'+this.id];
			TOP.stopEventBuddle(e);
	        var offset = $(this).offset(),
	            left = offset.left - 22,
	            top  = offset.top + 16;
	        
	        if (null != picup) {
	        	picup.cleanFileQueue();
	        	$('#filename').val('');
	        	
				picup.onComplete = function(){
					currEditor.loadBookmark();
					
					for (var i = 0, c = this._success.length; i < c; i++) {
						var aid = this._success[i].aid,
							url = picurl + '&aid=' + aid,
							html = '<img src="'+ url +'" _aid="'+aid+'" />';
						currEditor.loadBookmark();
						currEditor.pasteHTML(html);
					}
					d.hide();
				};
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
}

// 显示下拉功能框
function menuDialog(id, params) {
	if (!id || !params.body) {
		return ;
	}
	var obj = $('#' + id);
	
	if (!obj.size()) {
	    obj = $('<div>')
			  .addClass('modal-dialog')
			  .attr('id', id);
		
		if (typeof(params.body) == 'string') {
			obj.html(params.body);
		} else {
			obj.append(params.body.show());
		}
		
		obj
		.appendTo(params.appendTo ? params.appendTo : document.body)
		.bind('click mousedown', function(e){
			TOP.stopEventBuddle(e);
		});
		
		if (typeof(params.oncreate) == 'function') {
			params.oncreate.call(obj);
		}
		
		$(window).bind('click', function(){
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

function removeAttach(aid) {
	$('#attach-' + aid).remove();
	if (!$('#attach-list div.filecell').size()) {
		$('#attach-list').hide();
	}
}


/**
 * 联系人选择控件
 * 
 * @param params
 * @return
 */
var ForwardSelector = function(params) {
	this.setParam(params);
	this.init();
}

ForwardSelector.defaultSettings = {
	maxSelect: 0,
	panels: ['contact'],
	enableGroup: false,
	appendTo: null,
	selected: null
};
ForwardSelector.prototype = {

	/**
	 * 选择内容面板
	 */
	_panels: {},
	
	/**
	 * 结果面板
	 */
	_resultPanel: null,
	
	/**
	 * 搜索结果面板
	 */
	_searchPanel: null,
	
	/**
	 * 
	 */
	_container: null,
	
	/**
	 * 设置
	 */
	_settings: null,
	
	/**
	 * 
	 */
	_selectIndex: 0,
	
	/**
	 * 初始化
	 */
	init: function() {
		var _o = this;
		this._container = $('<div>').addClass('contact_selector');
		this._ctLeft = $('<div>').addClass('contact_selector_left');
		this._ctRight = $('<div>').addClass('contact_selector_right');
		this._leftInner = $('<div>').addClass('selector_inner');
		this._rightInner = $('<div>').addClass('selector_inner');
		
		this._container
		.append(this._ctLeft)
		.append($('<div>').addClass('contact_selector_center'))
		.append(this._ctRight);
		
		this._resultPanel = $('<div>').addClass('contact_selected');
		this._searchInput = $('<input type="text" class="input_text contact_search" id="contact_search" />');
		this._leftInner.append(
			$('<div>')
			.addClass('contact_input')
			.append(this._searchInput)
			.append($('<a>').addClass('icon icon_search_2'))
		);
		this._ctRight.append(this._rightInner.append(this._resultPanel));
		
		this._ctGroups = $('<div>').addClass('contact_select_groups');
		this._ctSearchResult = $('<div>').addClass('contact_select_groups search_ct').hide();
		this._searchList = $('<div>').addClass('panel_body').appendTo(this._ctSearchResult);
		for (var i = 0, c = this._settings.panels.length; i < c; i++) {
			this._panels[this._settings.panels[i]] = this.initPanel(this._settings.panels[i]);
			this._ctGroups.append(this._panels[this._settings.panels[i]]);
		}
		
		this._ctLeft.append(this._leftInner.append(this._ctGroups));
		this._ctLeft.append(this._leftInner.append(this._ctSearchResult));
		
		if (this._settings.appendTo != null) {
			this._container.appendTo(this._settings.appendTo);
		}
		
		this._container.append('<div class="clear"></div>');
		
		this._searchTree = new $.tree({
			id: 'search-tree',
			idKey: 'id',
			idPrefix: 'search-',
			cls: 'cast-tree',
			template: '{name}'
		});
		this._searchTree.appendTo(this._searchList);
		
		this._searchInput.bind('keyup', function(){
			var keyword = this.value.replace(/^\s+|\s+$/g, '');
			if (keyword) {
				_o.search(keyword);
			} else {
				_o._searchTree.clear();
				_o._ctGroups.show();
				_o._ctSearchResult.hide();
			}
		});
		
		this.initSelected();
	},
	
	/**
	 * 设置参数
	 */
	setParam: function(key, val) {
		var params = null;
		if (typeof(key) == 'object') {
			params = key;
		} else if (typeof(key) == 'string' && val) {
			params = {};
			params[key] = val;
		}
		
		if (null == params) {
			return ;
		}
		
		this._settings = $.extend(ForwardSelector.defaultSettings, params);
	},
	
	/**
	 * 搜索
	 */
	search: function(keyword) {
		var result = [],
			selectUsers  = {},
			_o = this;
		this._searchTree.clear();
		if (this._castTree) {
			
		}
		
		this._ctGroups.hide();
		this._ctSearchResult.show();
	},
	
	/**
	 * 选择
	 */
	select: function(id, from) {
		var node = from.find(id), _o = this;
		
		if (node) {
			var data = node.getData();
			
			var a = $('<a>').attr('href', 'javascript:void(0);');
			a.append('<input type="hidden" name="member[]" value="'+this._selectIndex+'" />');
			for (var k in data) {
				if (k == 'name') {
					a.append(data[k]);
				}
				
				if (data[k]) {
					a.append('<input type="hidden" name="'+k+'-'+this._selectIndex+'" value="'+data[k]+'" />');
				}
			}
			
			a.bind('click', function(){
				$(this).remove();
				_o.unselect(data);
			});
			
			this._resultPanel.append(a);
			
			node.hide();
		}
	},
	
	/**
	 * 取消选中
	 */
	unselect: function(item) {
		var id = item.id;
		var node = this._contactTree.find(id);
		if (node) {
			node.show();
			
			if (this._settings.mailInput) {
				var data = node.getData();
				var items = this._settings.mailInput.getItems('[_id="'+data.email+'"][name="'+data.name+'"]');
				this._settings.mailInput.removeItem(items);
			}
		}
	},
	
	initSelected: function() {
		if (!this._settings.selected) {
			return ;
		}
		
		var se = this._settings.selected;
		for (var i = 0, c = se.length; i < c; i++) {
			var nodes = this._contactTree.search({email: se[i].email, name: se[i].name});
			for (var k = 0, l = nodes.length; k < l; k++) {
				this.select(nodes[k].get('id'), this._contactTree);
			}
		}
	},
	
	/**
	 * 初始化面版
	 */
	initPanel: function(key) {
		var panel      = $('<div>').addClass('group_panel');
		var paneltitle = $('<div>').addClass('panel_title');
		var panelbody  = $('<div>').addClass('panel_body').css('height', '272px');
		var _o = this;
				
		panel.append(paneltitle);
		panel.append(panelbody);
		
		switch (key) {
			case 'contact':
				paneltitle.text(TEXT.PRIVATE_CONTACT);
				this.initContactList(panelbody);
				break;
		}
		
		return panel;
	},
	
	initContactList: function(body) {
		var _o = this,
		    selectbox   = $('<div>').addClass('select_box list_select_box');
		
		_o._contactTree = new $.tree({
			id: 'last-contact-tree',
			idKey: 'id',
			idPrefix: 'c-',
			cls: 'cast-tree',
			template: '{name}'
		});
		
		_o._contactTree.appendTo(selectbox);
		
		var contact = _o._settings.data;
		
		for (var i = 0, c = contact.length; i < c; i++) {
			var node = new $.treenode({
				data: {
					id: i,
					name: contact[i].name,
					email: contact[i].email
				},
				isLeaf: true,
				events: {
					mouseover: function(){$(this).addClass('tree-node-over');},
					mouseout: function(){$(this).removeClass('tree-node-over');},
					click: function(e){
						_o.select(this.id.replace('c-', ''), _o._contactTree);
						stopEventBuddle(e);
					}
				}
			});
			
			_o._contactTree.appendNode(node);
		}
		
		body.append(selectbox);
	},
	
	/**
	 * 获取选中项目
	 */
	getSelected: function() {
		var ret = [];
		this._resultPanel.find('a').each(function(){
			var a = $(this);
			var o = {name: a.text()};
			
			if (a.find(':hidden[name^="email-"]').size()) {
				o.email = a.find(':hidden[name^="email-"]').val();
			}
			
			if (a.find(':hidden[name^="contactid-"]').size()) {
				o.email = a.find(':hidden[name^="contactid-"]').val();
			}
			
			if (a.find(':hidden[name^="groupid-"]').size()) {
				o.groupid = a.find(':hidden[name^="groupid-"]').val();
			}
			
			ret.push(o);
		});
		return ret;
	},
	
	getSelectedCount: function() {
		return this._resultPanel.find('a').size();
	}
};

/**
 * 
 * @param params
 * @return
 */
var ForwardInput = function(params) {
	this.setParam(params);
	this.init();
};

ForwardInput.prototype = {
	
	/**
	 * 输入框实例
	 */
	_input: null,

	/**
	 * 设置项目
	 */
	_settings: null,
	
	/**
	 * 设置参数
	 */
	setParam: function(key, val){
		var p = {};
		if (typeof(key) == 'string' && undefined != val) {
			p[key] = val;
		} else {
			p = key;
		}
		
		if (null == p || undefined == p) {
			return ;
		}
		
		if (!this._settings) {
			this._settings = {};
		}
		
		for (var k in p) {
			if (k == 'target' || k == 'valuePlace' || k == 'textPlace') {
				if (typeof(p[k]) == 'string') {
					p[k] = $(p[k]);
				}
			}
			
			this._settings[k] = p[k];
		}
	},
	
	/**
	 * 初始化
	 */
	init: function() {
		if (!this._settings.target) {
			return ;
		}
		
		var _o = this;
		var params = {
			id: _o._settings.id,
	        target: _o._settings.target,
	        onAppend: function(item){_o.checkValue.call(_o, item);},
	        onRemove: function(){_o.updateInput();},
	        autoComplete:{
				data: _o._settings.data,
		        columns: ['name', 'email'],
		        width: 280,
		        template: '{name} <span class="gray">&lt;{email}&gt;</span>',
		        onSelect: function(item){
	                var data = item.data;
	                _o._input.setText('');
	                
	                var name = data.name;
	                var id   = data.email;
	                var title= data.name + (data.email ? '&lt;' + data.email + '&gt;' : '');
	                
	                _o._input.appendItem(name, {name: name, _id: id, title: title});
	                _o._input.focus();
		        }
			}
	    };
		
		_o._input = new $.mailinput(params);
		
		if (_o._settings.valuePlace) {
			var v = this._settings.valuePlace.val();
			var arr = v.split("\n");
			for (var i = 0, c = arr.length; i < c; i++) {
				var item = arr[i].split(' ');
				
				if (item.length < 2) {
					continue ;
				}
				
				_o.addItem(item[1], {name: item[1], _id: item[0], title: item[1]});
			}
		}
	},
	
	/**
	 * 检查输入内容
	 */
	checkValue: function(item) {
		var _o = this,
			text = item.text().replace(/[;,]/g, ''),
			identify = item.attr('_id'),
			attr = {
				_id: identify,
				name: text,
				text: text + (identify ? '<span style="gray">&lt;' + identify + '&gt;</span>' : '')
			},
			matched = false;
		for (var i = 0, c = this._settings.data.length; i < c; i++) {
			if ((this._settings.data[i].name == text && this._settings.data[i].email == identify)
				|| this._settings.data[i].email == text
				|| (!identify && this._settings.data[i].name == text))
			{
				attr = {
					_id : this._settings.data[i].email ? this._settings.data[i].email : '',
					name: this._settings.data[i].name,
					title: this._settings.data[i].name + (this._settings.data[i].email ? '<' + this._settings.data[i].email + '>' : '')
				};
				
				if (this._settings.data[i].foreign) {
					attr._id = attr._id;
				}
				
				matched = true;
				break;
			}
		}
		
		if (!matched) {
        	if (isEmail(text)) {
        		attr['_id'] = text;
        		attr['name'] = text.split('@')[0];
        		attr['title'] = attr['name'] + '<' + attr['_id'] + '>';
        	} else {
        		attr['_id'] = '';
        		attr['name'] = text;
        		attr['title'] = attr['name'];
        	}
		}
		
		if (item.parent().find('.mail_item[name="'+attr.name+'"][_id="'+attr._id+'"]').size() > 1
		   || (!attr._id && item.parent().find('.mail_item[name="'+attr.name+'"]').size() > 1))
		{
    		item.remove();
    		return ;
    	}
		
		item.attr(attr).text(attr.name + ';');
		
		_o.updateInput();
	},
	
	/**
	 * 
	 */
	updateInput: function() {
		if (!this._settings.valuePlace) {
			return ;
		}
		var vi = this._settings.valuePlace,
			ti = this._settings.textPlace ? this._settings.textPlace : this._settings.target,
			v = [], t = [];
		
		this.getItems().each(function(){
			var item = $(this);
			v.push([item.attr('_id'), item.attr('name')].join(' '));
			t.push([item.attr('name')].join(' '));
		});
		
		vi.val(v.join("\n"));
		ti.val(t.join(','));
		
		if (typeof this._settings.onUpdate == 'function') {
			this._settings.onUpdate.call(this);
		}
	},
	
	addItem: function(item, params, callback) {
		return this._input.appendItem(item, params, callback);
	},
	
	/**
	 * 获取已输入项目列表
	 */
	getItems: function(filter) {
		return this._input.getItems(filter);
	},
	
	removeItem: function(item) {
		return this._input.removeItem(item);
	},
	
	/**
	 * 获取输入项目数量
	 */
	getCount: function() {
		return this.getItems().size();
	}
};