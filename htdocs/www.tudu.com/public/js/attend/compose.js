
var TOP = TOP || getTop(),
	_CAST_WIN = null,
	_MI_TO = null,
	_MI_CC = null,
	_FORM_DATA = null,
	_EDITOR = null,
	_START_PICKER = null,
	_END_PICKER = null,
	_UPLOAD = null,
	_CLASSES = {},
	autosaveTudu = null,
	boardSelect = null;

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

    if ($('#action').val() == 'send') {

        if (!$(':radio[name="categoryid"]:checked').size() && !$('#categoryid').val()) {
        	return TOP.showMessage('请选择申请类型');
        }
    }
    
    // 处理HTML
    var src = _EDITOR.getSource();
    
    if (!checkContentImage(src, _EDITOR, function(){composeSubmit(form, callback);})) {
    	return ;
    }
    
    var reg = /<img([^>]+)src="([^"]+)"([^>]+)_aid="([^"]+)"([^>]+)\/>/ig;
    form.find(':hidden[name="file[]"]').remove();
    while ((result = reg.exec(src)) != null) {
    	form.append('<input type="hidden" name="file[]" value="'+result[4]+'" />');
    }
    
    src = src.replace(reg, '<img$1src="AID:$4"$3_aid="$4"$5/>');
    src = src.replace(/\s+id="[^"]+"/g, '');
    
	$('#postcontent').val(src);

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
    		if (!ret) {
    			form.find(':input:not([_disabled])').attr('disabled', false);
    		}
    	
            TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
            
            if (ret.data) {
                $('#ftid').val(ret.data.tuduid);
                if (ret.data.childtid != undefined && ret.data.childtid != null) {
	                var cl = $('#children-list'); 
	                for (var i = 0; i<ret.data.childtid.length; i++) {
	                	cl.find('input[name="ftid-'+i+'"]').val(ret.data.childtid[i]);
	                }
                }
                
                // 处理返回投票选项
                if (ret.data.votes) {
                	var votes = ret.data.votes;
                	for (var k in votes) {
                		var opt = $(':hidden[name="newoption[]"][value="'+k+'"]');
                		if (opt.size()) {
                			$('#option-' + k).attr('id', 'option-' + votes[k]);
                			
                			opt.attr('name', 'optionid[]').val(votes[k]);
                			$('input[name="text-'+k+'"]').attr('name', 'text-' + votes[k]);
                			$('input[name="ordernum-'+k+'"]').attr('name', 'ordernum-' + votes[k]);
                			
                		}
                	}
                }
            }
            
            if (ret.success) {
            	if (typeof(callback) == 'function') {
                	return callback();
                }
            	var url = '';
            	if ($('#action').val() == 'send' && ret.data) {
            		if (typeof(_NEW_WIN) != 'undefined' && _NEW_WIN) {
            			url = '/app/attend/apply/view?tid=' + ret.data.tuduid + '&newwin=1';
            		} else {
            			//url = '/frame#m=view&tid=' + ret.data.tuduid;
            			url = '/app/attend/apply/view?tid=' + ret.data.tuduid;
            		}
            		
            		if (ret.data.contacts) {
            			url += '&ctid=' + ret.data.contacts;
            		}
            		
            		location = url;
            	} else {
            		form.find(':input:not([_disabled])').attr('disabled', false);
            	}
            	return ;
            } else {
            	form.find(':input:not([_disabled])').attr('disabled', false);
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


/**
 * 选择联系人连接
 * 
 * @return
 */
function initSelectLink(obj, mailInput, valInput, containGroup, limit, childOf, panels) {
	if (!containGroup) {
		containGroup = false;
	}
	
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
            		_id: a[0].replace(/^#+/, ''),
            		name: a[1]
            	});
            }
        } else {
            selected = null;
        }
        //alert(selected.length);
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
        
        var params = {appendTo: Win.find('div.pop_body'), enableGroup: containGroup, selected: selected, mailInput: mailInput, childOf: undefined !== childOf ? childOf : null};
        if (undefined !== limit) {
        	params.maxSelect = limit;
        }
        if (undefined !== panels) {
        	params.panels = panels;
        }
        
        var selector = new TOP.ContactSelector(params);
		var panel = TOP.Cookie.get('CONTACT-PANEL');
		if (!panel) {
			panel = 'common';
		}
		selector.switchPanel(panel);
		
		Win.find('button[name="confirm"]').bind('click', function(){
			var se = selector.getSelected();
			
			for (var i = 0, c = se.length; i < c; i++) {
				var p = {};
				
				if (se[i].groupid) {
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
	
	TOP.getJQ()('a:not([href^="javascript:"]):not([href^="/tudu/modify"]):not([target="_blank"])').bind('click', _leaveDialog);
	TOP.getJQ()('form').bind('submit', _leaveDialog);
	
	$('a:not([href^="javascript:"]):not([href^="/tudu/modify"]):not(.xheButton)').bind('click', _leaveDialog);
	TOP.window.onbeforeunload = function() {
		if (!_checkForm()) {
			return TOP.TEXT.COMPOSE_EXIT_WITHOUT_SAVE;
		}
	};
	window.onunload = function(){
		TOP.getJQ()('a').unbind('click', _leaveDialog);
		TOP.getJQ()('form').unbind('submit', _leaveDialog);
		TOP.window.onbeforeunload = function(){};
	};
	
	function _leaveDialog(e) {
		if (_checkForm()) {
			return true;
		}
		
		var trigger = $(this);
		
		TOP.Frame.switchFolder();
		TOP.Frame.Dialog.show({
			title: TOP.TEXT.LEAVE_HINT,
			close: false,
			body: '<p style="padding:15px 0;margin-left:-12px"><strong>' + TOP.TEXT.COMPOSE_EXIT_SAVE_HINT + '</strong></p>',
			buttons: [{
				   text: TOP.TEXT.SAVE,
				   cls: 'btn',
				   events: {click: function(){
						if (typeof(isnew) != 'undefined' && isnew) {
							$('#action').val('save');
						} else {
							$('#action').val('send');
						}
						TOP.window.onbeforeunload = function(){};
						composeSubmit('#theform', function(){
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
		var r = {};
		form.find('select, textarea:not(#postcontent), input:not(#ftid, #fpid, #type, #issend, #action, [name="file[]"], [type="checkbox"]), :checkbox:checked').each(function(){
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
		r['content'] = _EDITOR.getSource();
		return r;
	}
	
	function _compare(v1, v2) {
		var l1 = 0, l2 = 0, vl1, vl2;
		for (var k in v1) {
			vl1 = v1[k].constructor == window.Array ? v1[k].join(',') : v1[k];
			vl2 = v2[k] && v2[k].constructor == window.Array ? v2[k].join(',') : v2[k];
			if (vl1 != vl2) {
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

function initPicInsert(editors, uploadParams) {
	if (typeof(editors) == 'string') {
		var o = {};
		o[editors] = _EDITOR;
		editors = o;
	}
	
	var currEditor = null;
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
							url = '/attachment/img?fid=' + aid,
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


function clearCast() {
	var loadTime = TOP.Cast.getTime();
	if (loadTime) {
		$.ajax({
	        type: 'GET',
	        dataType: 'json',
	        url: '/tudu/clear-cast?loadtime=' + loadTime,
	        success: function(ret) {
	            if (ret.data && ret.data.clear) {
	            	TOP.Cast.clear();
	            }
	        },
	        error: function() {}
	    });
	}
}

/**
 * 构造表单，提交预览或页面数据传递
 * @param form
 * @param target
 * @return
 */
function getFormPreview(form, address, target) {
	$('#postcontent').val(_EDITOR.getSource());
	var data = $(form).serializeArray();
	var form = $('<form action="'+address+'" method="post" target="'+target+'" style="display:none"></form>');
	for (var key in data) {
		form.append('<textarea name="' + data[key].name + '">' + data[key].value + '</textarea>');
	}
	form.append('<input name="autosave" value="1" />');
	form.appendTo(document.body).submit();
}


/**
 * 自动保存图度及回复
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
			_FORM_DATA = this.getFormVal();
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
		
		if (null == _FORM_DATA) {
			_FORM_DATA = {};
		}
		
		if (me.settings.forcesave) {
			_FORM_DATA = {};
			setTimeout(function(){
				me.checkSave();
			}, this.settings.time);
		} else {
			_FORM_DATA = me.getFormVal();
			me.checkSave();
		}
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
		
		_FORM_DATA = me.getFormVal();
		
		this.settings.func.call(this);
	},
	
	// 写提示信息
	writeMsg: function() {
		if (this.timeValue) {
			var newtime = new Date(),
				t = (newtime - this.time)/(1000*60),
				timediff = parseInt(t);

			$('span.compose_msg').html(TOP.TEXT.AUTOSAVE_TIPS_FIRST + this.timeValue + ' ( ' + timediff + TOP.TEXT.AUTOSAVE_TIPS_SECOND + ' )');
		}
	},
	
	// 检查表单内容是否有改变
	checkFormVal: function() {
		var me = this,
			newdata = me.getFormVal();
		var l1 = 0, l2 = 0, vl1, vl2;
		for (var k in newdata) {
			if (k.indexOf('ch-') == -1) {
				vl1 = _FORM_DATA[k] && _FORM_DATA[k].constructor == window.Array ? _FORM_DATA[k].join(',') : _FORM_DATA[k];
				vl2 = newdata[k].constructor != window.Array ? newdata[k] : newdata[k].join(',');
					
				if (vl1 != vl2) {
					return true;
				}
				l1++;
			}
		}
		for (var k in _FORM_DATA) {
			if (k.indexOf('ch-') == -1) {
				l2++;
			}
		}
		
		if (l1 != l2) {return true;}
		
		return false;
	},
	
	// 获取表单内容
	getFormVal: function() {
		form = $(this.settings.form);
		var r = {};
		form.find('select, textarea:not(#postcontent), input:not(#ftid, #fpid, #savetime, #type, #issend, #action, [name="file[]"], [type="checkbox"]), :checkbox:checked').each(function(){
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
		r['content'] = _EDITOR.getSource();
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
		            	me.uploaddialog.dialog.container.find('div.progress_large div.bar').css('width', '100%');
		
		                var response = uploader.HttpReault.match(/\{.*\}/);
		
		                var ret;
		                try {
		                    eval('ret=' + response + ';');
		                } catch (e) {}
		
		                if (ret.fileid) {
		                	if (me.editor == null) {
		                		Modify.appendTOEditor(ret.fileid)
		                	} else {
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
		            me.uploaddialog.hide();
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