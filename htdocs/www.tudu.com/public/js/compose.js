if (typeof(getTop) != 'function') {
	function getTop() {
	    return parent;
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
	_CLASSES = {},
	autosaveTudu = null,
	boardSelect = null;

/**
 * 初始化编辑器
 * @param id
 * @return
 */
function initEditor(id, css, disabled) {
	var editor = new TOP.Editor(document.getElementById('content'), {
		resizeType : 1,
		width: '100%',
		minHeight: 200,
		themeType : 'tudu',
		css: css,
		scope: window,
		disabled: disabled,
		ctrl: {13: function(){$('button[name="send"]:eq(0)').click();}}
	}, jQuery);
	
	return editor;
}

/**
 * 加载主题分类
 * 
 * @param bid
 * @return
 */
function loadClasses(bid, select, classid) {
	if (!bid) {
		return _fillSelect([], select);
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
					if (classid) {
						_selectText(_CLASSES[bid], select, classid);
					}
				}
			},
			error: function(res) {
				return ;
			}
		});
	} else {
		_fillSelect(_CLASSES[bid], select);
		if (classid) {
			_selectText(_CLASSES[bid], select, classid);
		}
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
		var html = [];
		for (var i = 0, c = ret.length; i < c; i++) {
			html.push('<option value="'+ret[i].classid+'" title="'+ret[i].classname+'">'+ret[i].classname+'</option>');
		}
		o.append(html.join(''));
		
		o.attr('disabled', false);
	}
	
	function _selectText(data, select, classid) {
		var o = $(select);
		o.val(classid);
	}
}

/**
 * 新建主题分类
 *
 * 公告、讨论、会议
 */
function createClass(bid) {
	var btns = [
	   {
		   text: TOP.TEXT.CONFIRM,
		   cls: 'btn',
		   events: {click: function(){
               var _$ = TOP.getJQ(),
			       form = _$('#classform'),
			       className = form.find('input[name="classname"]').val().replace(/^\s+|\s+$/, '');
			
			   if (!bid) {
				   TOP.showMessage(TOP.TEXT.PROCESSING_ERROR + ' Missing[bid]');
				   return false;
			   }
			
			   if (!className.length) {
				   TOP.showMessage(TOP.TEXT.INVALID_CLASS_NAME);
				   form.find('input[name="classname"]').focus();
			       return false;
			   }
			
			   TOP.showMessage(TOP.TEXT.POSTING_DATA, 0, 'success');
			   form.find(':input').attr('disabled', true);
			
			   $.ajax({
			       type: 'POST',
			       dataType: 'json',
			       data: {classname: className, bid: bid},
			       url: form.attr('action'),
			       success: function(ret) {
			           TOP.showMessage(ret.message, 10000, ret.success ? 'success' : null);
			           form.find(':input').attr('disabled', false);
			           if (ret.success) {
			        	   var data = ret.data;
			        	   if (data) {
			        		  $('#classid').prepend('<option title="'+data.cn+'" value="'+data.cid+'">'+data.cn+'</option>');
			        		  $('#classid').val(data.cid);
			        	   }
			        	   // 由于新添加了主题分类，所以清空保存的数据
			        	   _CLASSES = {};
			        	   Win.close();
			           }
			       },
			       error: function(res) {
			    	   form.find(':input').attr('disabled', false);
			           TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			       }
			   });
		   }}
	   },
	   {
        text: TOP.TEXT.CANCEL,
        cls: 'btn close',
        events: {click: function(){
        	Win.close();
        	$('#classid option:first').attr('selected', 'selected');
        }}
    }
	];
	
	var Win = TOP.Frame.Dialog.show({
		title: TOP.TEXT.CREATE_BOARD_CLASS,
		body: '<div style="margin:10px;"><form id="classform" action="/board/classes"><div>' + TOP.TEXT.BOARD_CLASS_SUBJECT + TOP.TEXT.CLN + '<input class="input_text" name="classname" type="text" style="width:300px;" value="" /></div></form></div>',
		buttons: btns
	});
}

function _sumbitClass(bid, win) {
	
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
	
	if (!$('#bid').val()) {
        $('#board-input').focus();
        return TOP.showMessage(TOP.TEXT.BOARD_NOT_APPIONT);
    }
	
	if (boardSelect) {
		var board = boardSelect.getSelected();
		if (board.flowonly) {
			return TOP.showMessage(TOP.TEXT.BOARD_MUST_FLOWONLY_FLOW_NULL);
		}
	}
	
	var i = 0;
	if (typeof(reviewInput) !== 'undefined') {
    	var reviewers = reviewInput.getItems();
    	reviewers.each(function (){
        	if ($(this).attr('title') == TOP.TEXT.INVALID_TO_USER) {
        		i++;
        	}
		});
    }
	
	if (i > 0) {return TOP.showMessage(TOP.TEXT.TUDU_ACCEPTER_EMAIL_ERROR);}
	
    if ($('#action').val() == 'send') {
    	if (boardSelect) {
    		var board = boardSelect.getSelected();
    		if (board.isclassify && ($('#classid').size() && $('#classid').val() == '^add-class')) {
    			$('#classid').focus();
        		return TOP.showMessage(TOP.TEXT.BOARD_MUST_CLASSIFY);
    		}
    	}
    	
        if (!$('#subject').val()) {
            $('#subject').focus();
            return TOP.showMessage(TOP.TEXT.TUDU_SUBJECT_IS_NULL);
        }

        if ($('#to').size()) {
        	if (_MI_TO && _MI_TO.getItems('.mail_item_error').size() > 0) {
        		return TOP.showMessage(TOP.TEXT.INVALID_TO_USER);
        	}
        	
        	if (!$('#to').val()) {
				return TOP.showMessage(TOP.TEXT.TO_USER_IS_NULL);
        	}
        } else if ($('#cc').size()) {
	        if (!$('#cc').val() && !$('#bcc').val()) {
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
		
		if ($('#vote-panel:visible').size()) {
			var votePanel = $('#vote-panel'), num = 0, obj = null;
			votePanel.find('input[name^="text-"]').each(function() {
				if (!$(this).val()) {
					num ++;
					obj = $(this);
					return false;
				}
			});
			if (num > 0) {obj.focus();return TOP.showMessage(TOP.TEXT.MISSING_VOTE_OPTION);}
			
			var k = 0, target = null;
			votePanel.find('input[name^="title-"]').each(function() {
                if (!$(this).val()) {
					k ++;
                    target = $(this).focus();
                    return false;
                }
            });
			if (k > 0) {target.focus();return TOP.showMessage(TOP.TEXT.MISSING_VOTE_TITLE);}
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
            			url = '/tudu/view/?tid=' + ret.data.tuduid + '&newwin=1';
            		} else {
            			//url = '/frame#m=view&tid=' + ret.data.tuduid;
            			url = '/tudu/view/?tid=' + ret.data.tuduid;
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

function postSubmit(form, callback) {
	form = $(form);
	
	if ($('#attach-list div.upload_error').size()) {
		if (!confirm(TOP.TEXT.COMPOSE_UPLOAD_FAILURE)) {
			return ;
		}
	}
	
	if (!whileUploading(TOP.TEXT.WAITING_UPLOAD, function(){postSubmit(form);}, form)) {
		return ;
	}
	
	if (TOP.Device.Android || TOP.Device.iOS) {
	    var src = $('#content').val();
        src = src.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br />');
	} else {
    	// 处理图片
        var src = _EDITOR.getSource();
        var reg = /<img([^>]+)src="([^"]+)"([^>]+)_aid="([^"]+)"([^>]+)\/>/ig;
        form.find(':hidden[name="file[]"]').remove();
        while ((result = reg.exec(src)) != null) {
        	form.append('<input type="hidden" name="file[]" value="'+result[4]+'" />');
        }
        
        src = src.replace(reg, '<img$1src="AID:$4"$3_aid="$4"$5/>');
        src = src.replace(/\s+id="[^"]+"/g, '');
	}
    
	$('#postcontent').val(src);
	
    var data = form.serializeArray();
	var tuduId = form.find('input[name="tid"]').val();

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
            	
    	        if (typeof(_NEW_WIN) != 'undefined' && _NEW_WIN) {
    	        	location = '/tudu/view?tid=' + tuduId + '&newwin=1';
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
 * 选择联系人连接
 * 
 * @return
 */
function initSelectLink(obj, mailInput, valInput, containGroup, order) {
	if (!containGroup) {
		containGroup = false;
	}

	if (!order) {
        order = false;
    }
	
	$(obj).click(function(){
        var instance = this;
        var title = $(this).text();

        var val = valInput.val();
        var selected = [], userid = null;
        if (val) {
            val = val.split("\n");
            for (var i = 0, c = val.length; i < c; i++) {
                var l = val[i];
                if (l.indexOf('>') == 0 || l.indexOf('+') == 0) {
                    selected.push({separator: l});
                } else {
                    var a = l.split(' ');
                    selected.push({
                        _id: a[0].replace(/^#+/, ''),
                        name: a[1]
                    });
                }
            }
        } else {
            selected = null;
        }

		var panels = ['lastcontact', 'common', 'contact'];
        if (order) {
            panels = ['common'];
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
        
        var selector = new TOP.ContactSelector({appendTo: Win.find('div.pop_body'), enableGroup: containGroup, selected: selected, mailInput: mailInput, order: order, panels: panels});
		var panel = TOP.Cookie.get('CONTACT-PANEL');
		if (!panel) {
			panel = 'common';
		}
		selector.switchPanel(panel);
		
		Win.find('button[name="confirm"]').bind('click', function(){
			var se = selector.getSelected();
			
			if (order) {
                mailInput.clear();
            }
			
			for (var i = 0, c = se.length; i < c; i++) {
				var p = {};
				
				if (se[i].groupid) {
					p.title = se[i].name + '&lt;' + TOP.TEXT.GROUP + '&gt;';
					p._id = se[i].groupid;
					p.name = se[i].name;
				} else if(se[i].email) {
					p.title = se[i].name + (se[i].email ? '$lt;' + se[i].email + '&gt;' : '');
					p._id = se[i].email ? se[i].email : '';
					p.name = se[i].name;
				} else if (se[i].separator) {
                    p.separator = p._id = p.name = p.title = se[i].separator;
                }

				mailInput.addItem(p.name, p);
			}
			Win.close();
		});
        
        Win.show();
    });
}

/**
 * 计算结束时间
 * 
 * @param starttime
 * @param nexttype
 * @param nextparam
 * @return
 */
function calEndtime(starttime) {
	if (!starttime) return ;
	
	var date = new Date(), ret = new Date(),
		arr  = starttime.split('-'),
		form = $('#theform'),
		mode = form.find(':radio[name="mode"]:checked').val(),
		type = form.find(':radio[name="type-'+mode+'"]:checked').val(),
		prefix = mode + '-' + type + '-';
	
	date.setFullYear(arr[0]);
	date.setMonth(parseInt(arr[1].replace(/^0+/, '')) - 1);
	date.setDate(parseInt(arr[2].replace(/^0+/, '')));
	ts = date.getTime();
	
	var day   = form.find(':input[name="'+ prefix +'day"]').val(),
		week  = form.find(':input[name="'+ prefix +'week"]').val(),
	    month = form.find(':input[name="'+ prefix +'month"]').val(),
	    at    = form.find(':input[name="'+ prefix +'at"]').val(),
	    what  = form.find(':input[name="'+ prefix +'what"]').val(),
	    weeks = form.find(':input[name="'+ prefix +'weeks[]:checked"]').val(),
	    key   = mode + '-' + type;
	
	var days = {sun:0, mon:1, tue:2, wed:3, thu:4, fri: 5, sat:6};
	
	switch (key) {
		// day
		case 'day-1':
			ret.setTime(ts + 86400000 * parseInt(day));
			break;
		// workday
		case 'day-2':
			ret.setTime(ts + 86400000);
			while (ret.getDay() == 6 || ret.getDay() == 0) {
				ret.setTime(ret.getTime() + 86400000);
			}
			break;
		// week
		case 'week-1':
			
			break;
		case 'month-1':
			var m = date.getMonth() + parseInt(month);
			
			ret.setFullYear(date.getFullYear() + m / 11);
			m = m > 11 ? m % 11 : m;
			ret.setMonth(m);
			ret.setDate(day);
			break;
		case 'month-2':
			var m = date.getMonth() + parseInt(month);
			
			ret.setFullYear(date.getFullYear() + m / 11);
			ret.setMonth(m > 11 ? m % 11 : m);
			
			if (at != '0') {
				ret.setDate(1);
				at = parseInt(at);
				c = 0;
				for (; ; ret.setTime(ret.getTime() + 86400000)) {
					if (ret.getDay() == days[what]) {
						c++
						if (c == at) {
							break;
						}
					}
				}
			} else {
				ret.setDate(31);
				while (ret.getDay() != days[what]) {
					ret.setTime(ret.getTime() - 86400000)
				}
			}
			break;
	}
	
	return ret.getFullYear() + '-' + _pad(ret.getMonth() + 1) + '-' + _pad(ret.getDate());
	
	function _pad(s) {
		if (s >= 10) return s
		return '0' + s;
	}
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
    				currEditor.pasteHTML('<img src="'+url+'" alt="" /><br />', true);
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
		.mousedown(function(e){TOP.stopEventBuddle(e);})
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
					for (var i = 0, c = this._success.length; i < c; i++) {
						var aid = this._success[i].aid,
							url = '/attachment/img?fid=' + aid,
							html = '<img src="'+ url +'" _aid="'+aid+'" /><br />';
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
 * 加载模板
 */
var Tudu = Tudu || {};

Tudu.Template = {
	// 显示目录
	menu: null,
	// 板块模板目录列表
	list: {},
	// 版块模板数据
	tplList : {},
	// 模板内容
	content: {},
	// 模板
	boardId: null,
	// 写入的表单控件
	editor: null,
	// 显示目录
	showMenu: function(e, editor, boardId) {
		var me = this;
		this.editor = editor;
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
				   if ($('#tpl-list a.menu_item').size() > 10) {
					   $('#tpl-list, #search-list').css({'height': '200px'});
				   }
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
			me.editor.pasteHTML(me.content[tplId]);
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
				   me.editor.pasteHTML(me.content[tplId]);
			   }
			},
			error: function(res) {
			    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
	}
	
};

/**
 * 自动保存 提交图度数据
 */
Tudu.TuduSubmit = function() {
	var me = this,
		form = $(this.settings.form);
	form.find('input[name="action"]').val('save');
	
	var bid = boardSelect.getValue();
	if (!bid) {
		return false;
	}
	
	if (_UPLOAD.isUploading()) {
		return false;
	}
	
	// 处理HTML
    var src = _EDITOR.getSource();
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
                $('#ftid').val(ret.data.tuduid);
                if (ret.data.childtid != undefined && ret.data.childtid != null) {
	                var cl = $('#children-list'); 
	                for (var i = 0; i<ret.data.childtid.length; i++) {
	                	cl.find('input[name="ftid-'+i+'"]').val(ret.data.childtid[i]);
	                }
                }
                
                if (ret.data.votes) {
                	votes = ret.data.votes;
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
            	me.getTime();
            	me.writeMsg();
            }
        },
        error: function(res) {
        }
    });
	
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
    var src = _EDITOR.getSource();
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
	    		var fpid = form.find(':hidden[name="fpid"]');
                fpid.val(ret.data.postid);
                form.find(':hidden[name="action"]').val('modify');
            }
            if (ret.success) {
            	me.getTime();
            	me.writeMsg();
            }
        },
        error: function(res) {
        }
    });
};

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
				vl2 = newdata[k] && newdata[k].constructor == window.Array ? newdata[k].join(',') : newdata[k];
					
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
		
		                var fileid = ret.fileid ? ret.fileid : (ret.data ? ret.data.fileid : null);
		                if (fileid) {
		                    var url = '/attachment/img?fid=' + fileid;
		
		                    html = '<img src="'+ url +'" _aid="'+fileid+'" /><br />';
		                    me.editor.pasteHTML(html);
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