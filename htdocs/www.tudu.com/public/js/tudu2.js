if (typeof(getTop) != 'function') {
	function getTop() {
	    return parent;
	}
}

var TOP = getTop(),
    _FORM_DATA = null,
    _UPLOAD = null;

var Tudu = Tudu || {};
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
 * （图度|回复）撰写相关
 */
var Compose = {
	
	/**
	 * 表单数据记录
	 */
	formData: null,
	
	/**
	 * 附件上传对象
	 */
	_attachUpload: null,
	
	/**
	 * 编辑器
	 */
	_editor: null,
	
	/**
	 * 初始化编辑表单对象
	 */
	init: function(params) {
		this.setParam(params);
	},
	
	/**
	 * 设置参数
	 */
	setParam: function(params) {
		if (!typeof (params) == 'object') {
			return ;
		}
		
		for (var k in params) {
			switch (k) {
				case 'attachUpload':
				case 'attachupload':
				case 'upload':
					this._attachUpload = params[k];
					break;
				case 'form':
					this._form = typeof params[k] == 'string' ? $(params[k]) : params[k];
					break;
				case 'editor':
					this._editor = params[k];
					break;
			}
		}
	},

	/**
	 * 发送回复
	 */
	sendPost: function(action, callback) {
		if (!this._form || !this._form.size()) {
			return ;
		}
		
		var isSend = action != 'autosave',
			me = this;
		
		this._form.find('input[name="type"]').val(action);
		if (isSend) {
			if (this._form.find('input[name="percent"]').size()) {
				var percent = this._form.find('input[name="percent"]').val(),
					elapsedtime = this._form.find('input[name="elapsedtime"]').val(),
					currentPercent = $('#current-percent').val();
		    }
			
		    if (this._editor && this._editor.isNull()) {
		    	this._editor.focus();
		        return TOP.showMessage(TOP.TEXT.POST_CONTENT_IS_NULL);
		    }
			
			if (!whileUploading(this._attachUpload, TOP.TEXT.WAITING_UPLOAD, function(){Compose.sendPost('reply');}), this._form) {
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
			
			if (this._attachUpload.isUploading()) {
				return ;
			}
		}
		
		// 处理图片
		var src = this._editor.getSource();
		
		if (action !== 'autosave') {
			if (!checkContentImage(src, this._editor, function(){Compose.sendPost(action, callback);})) {
		    	return ;
		    }
		}
		
		var reg = /<img([^>]+)src="([^"]+)"([^>]+)_aid="([^"]+)"([^>]+)\/>/ig;
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
            			if (typeof(_NEW_WIN) != 'undefined' && _NEW_WIN) {
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
	
	initPicInsert: function(ele, uploadParams) {
		var auth = uploadParams ? uploadParams.auth : null,
			picupload = null,
			picup  = null,
			me = this;
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
	}
};

/**
 * 编辑页面
 */
var Modify = {
	
	tuduId: null,
	
	action: null,
	
	classes: {},
	
	flows: {},
	
	toInput: null,
	
	ccInput: null,
	
	bccInput: null,
	
	reviewInput: null,
	
	editor: null,
	
	chEditor: null,
	
	chToInput: null,
	
	chCcInput: null,
	
	chBccInput: null,
	
	chReviewerInput: null,
	
	autoSave: null,
	
	expand: {
		cc: false,
		bcc: false,
		date: false,
		percent: false,
		divide: false,
		review: false
	},
	
	isInitDivide: false,
	
	chExpand: {
		cc: false,
		bcc: false,
		date: false,
		percent: false,
		content: false,
		prev: false,
		review: false
	},
	
	inited: {
		cycle: false,
		privacy: false,
		divide: false
	},
	
	datepickers: null,
	
	chDatepickers: null,
	
	filedialog: null,
	
	tuduPercent: '0%',
	
	upload: null,
	
	chUpload: null,
	
	// 自动ID
	autoIndex: 0,
	
	// 当前ID
	currIndex: null,
	
	tools: null,
	
	divide: null,
	
	// 是否分工
	isDivide: null,
	
	accepter: null,
	
	acceptmode: null,
	
	currEditor: null,
	
	capturer: null,
	
	boardSelect: null,
	
	chBoardSelect: null,
	
	editorCss:{},
	
	issynchro: false,
	
	isModify: false,
		
	init: function(type, action, forbid, tools, back, accepter) {
		var me = this;
		
		this.action = action;
		
		if (tools) me.tools = tools;
		
		TOP.keyhint('#board-input', 'black', true, document);
		this.boardSelect = new BoardSelector({
			input: $('#board-input'),
			name: 'bid',
			boards: _BOARDS
		});

		TOP.keyhint('#ch-bid-input', 'black', true, document);
		this.chBoardSelect = new BoardSelector({
			input: $('#ch-bid-input'),
			name: 'ch-bid',
			boards: _BOARDS
		});
		
		if (tools.cc) {
			$('#add-cc').text(TOP.TEXT.DELETE_CC);
			me.expand.cc = true;
		}
		
		if (tools.bcc) {
			$('#add-bcc').text(TOP.TEXT.DELETE_BCC);
			me.expand.bcc = true;
		}

		if (tools.date) {
			$('#add-date').text(TOP.TEXT.DELETE_DATE);
			this.dateInit();
			me.expand.date = true;
		}
		
		if (tools.reviewer) {
			$('#add-reviewer').text(TOP.TEXT.DELETE_REVIEW);
			$('#add-divide').addClass('disabled');
			me.expand.review = true;
		}
		
		this.boardSelect.bind('select', function(){
			me.boardChange();
		});
		$('#flowid').bind('change', function() {
		    var item = $(this).find('option[value="'+this.value+'"]');
		    if (item.attr('_classid')) {
		        $('#classid option[value="'+item.attr('_classid')+'"]').attr('selected', 'selected');
		    }
		});
		
		$('#classid').bind('change', function(){
			var items = $('option:selected', $(this));
			items.each(function(){
				if ($(this).val() == '^add-class') {
					var bid = Modify.boardSelect.getValue();
					me.createClass(bid, '#classid');
				}
			});
		});
		
		$('#flowid').bind('change', function(){
			me.flowChange('#flowid');
		});
		
		$('#acceptmode').click(function(){
	    	if ($(this).attr('checked') == true) {
	    		$('#add-divide, #add-percent').addClass('disabled');
	    		me.acceptmode = 'claim';
	    	} else {
	    		$('#add-divide, #add-percent').removeClass('disabled');
	    		me.acceptmode = null;
	    	}
	    });
		if ($('#acceptmode').attr('checked') == true) {
			$('#add-divide').addClass('disabled');
			me.acceptmode = 'claim';
		}
		
		me.isDivide = tools.divide;
		me.accepter = accepter;
		
		// 编辑器
		var h = $(window).height(),

        ch = $(document.body).height();
        var editorHeight = Math.max($('#content').height() + (h - ch - 15), 200);
        $('#content').css('height', editorHeight + 'px');
		this.editor = new TOP.Editor(document.getElementById('content'), {
			resizeType : 1,
			width: '100%',
			minHeight: 200,
			themeType : 'tudu',
			css: Modify.editorCss,
			scope: window,
			pasteType: 2,
			disabled: (forbid.editor && action != 'forward'),
			ctrl: {
				13: function(){$('#action').val('send');Modify.send('send');}
			}
		}, jQuery);

	    me.chEditorInit();
	    
	    $('#map-btn').click(function(){
			me.editor.getEditor().loadPlugin('googlemap', function() {
				me.editor.getEditor().plugin.mapDialog();
			});
	    });

		// 初始化任务内容
		if (type == 'task') {
			this.toInput = new TOP.ContactInput({
		        id: 'to-input', target: $('#i-to'), valuePlace: $('#to'), group: false, accepter: accepter, review: action != 'forward', synchro: action != 'forward', type: 'to',
				onBefore: function() {
					this.setParam('divide', me.expand.divide);
					if (me.expand.divide) {
						TOP.showMessage(TOP.TEXT.TUDU_GROUP_NOT_SYNCHRO);
					}
				},
				onUpdate: function() {
					var _this = this, percent = true;
					if (!me.expand.divide) {
						this._settings.jq('label[for="acceptmode"]').removeClass('gray');
						this._settings.jq('#acceptmode').attr('disabled', false);
					}
					this.getItems().each(function(){
						var item = $(this);
						if (item.hasClass('icon_flow_arrow')) {
							_this._settings.jq('label[for="acceptmode"]').addClass('gray');
							_this._settings.jq('#acceptmode').attr('checked', false);
							_this._settings.jq('#acceptmode').attr('disabled', true);
							percent = false;
						}
					});
					if (percent) {
						_this._settings.jq('#add-percent').removeClass('gray');
					} else {
						_this._settings.jq('#add-percent').addClass('gray');
						me.expand.percent = false;
                        _this._settings.jq('#row-percent').hide();
                        _this._settings.jq('#add-percent').text(TOP.TEXT.ADD_PERCENT);
					}

					var to = this._settings.valuePlace.val().split("\n"), toArr = [], names = [];
	                for (var i = 0, c = to.length; i < c; i++) {
	                    var a = to[i].split(' ');
	                    if (a[1]) {
	                    	var o = {email: a[0]};
	                    	delete a[0];
	                    	o.name = a.join(' ');
	                        toArr.push(o);
	                        names.push(o.name);
	                    }
	                }
	                
		        	if (action == 'forward') {
		                var source = me.editor.getSource();

		                var div = $('<div>');
		                div.html(source);

		                if (!toArr.length) {
		                    div.find('p[_name="forward"]').remove();
		                } else {
		                    var text = TOP.formatString(TOP.TEXT.FORWARD_INFO, $('#myname').val(), names.join(','));
		                    var html = '<strong>'+TOP.TEXT.FORWARD+TOP.TEXT.CLN+'</strong><span style="color:#aaa">'+text+'</span>';

		                    if (div.find('p[_name="forward"]').size()) {
		                        div.find('p[_name="forward"]').html(html);
		                    } else {
		                    	if (div.find('p[_name="review"]').size()) {
		                    		div.find('p[_name="review"]').after('<p _name="forward">'+html+'</p><br />');
		                    	} else {
		                    		div.prepend('<p _name="forward">'+html+'</p><br />');
		                    	}
		                    }
		                }
		                me.editor.setSource(div.html());
		            }
		            me.updatePercentList($('#percent-list'), toArr, '');
		        },
		        jq: jQuery
		    });
			
			if (action == 'review') {
				var order = true;
			} else {
				var order = false;
			}

			me.initSelectLink('#select-to', me.toInput, $('#to'), false, action != 'forward', action != 'forward', TOP.TEXT.SELECT_TO_TIPS, TOP.TEXT.SWITCH_EXECUTE_TYPE, accepter);
			
			this.reviewInput = new TOP.ContactInput({
		        id: 'review-input', target: $('#i-reviewer'), valuePlace: $('#reviewer'), group: false, contact: false, jq: jQuery, review:true, 
		        onUpdate: function() {
					var to = this._settings.valuePlace.val().split("\n"), toArr = [], names = [];
	                for (var i = 0, c = to.length; i < c; i++) {
	                    var a = to[i].split(' ');
	                    if (a[1]) {
	                    	var o = {email: a[0]};
	                    	delete a[0];
	                    	o.name = a.join(' ');
	                        toArr.push(o);
	                        names.push(o.name);
	                    }
	                }

	                if (action == 'review' || action == 'apply' || action == 'forward') {

	                    var source = me.editor.getSource();

		                var div = $('<div>');
		                div.html(source);
		                
	                	var text = TOP.formatString(TOP.TEXT.REVIEW_INFO, names.join(','));

	                	if (names.length > 0) {
		                    var html = '<strong>'+TOP.TEXT.REVIEW+TOP.TEXT.CLN+'</strong><span style="color:#aaa">'+text+'</span>';
		                    
		                    if (div.find('p[_name="review"]').size()) {
		                        div.find('p[_name="review"]').html(html);
		                    } else {
		                        div.prepend('<p _name="review">'+html+'</p><br />');
		                    }
	                	} else {
	                		div.find('p[_name="review"]').remove();
	                	}
	                	
	                	me.editor.setSource(div.html());
	                }
				}
		    });
			me.initSelectLink('#select-reviewer', me.reviewInput, $('#reviewer'), false, true, false, TOP.TEXT.SELECT_REVIEWER_TIPS, TOP.TEXT.SWITCH_REVIEW_TYPE);
		}
		this.ccInput = new TOP.ContactInput({
	        id: 'cc-input', target: $('#i-cc'), valuePlace: $('#cc'), group: true, jq: jQuery
	    });
		me.initSelectLink('#select-cc', me.ccInput, $('#cc'), true);
		
		this.bccInput = new TOP.ContactInput({
	        id: 'bcc-input', target: $('#i-bcc'), valuePlace: $('#bcc'), group: true, jq: jQuery
	    });
		me.initSelectLink('#select-bcc', me.bccInput, $('#bcc'), true);
		
		if (!this.inited.cycle && $('#block-cycle:visible').size()) {
			// cycle
			$('#mode-group :radio[name="mode"]').click(function(){
		        $('div.method').hide();
		        $('#mode-' + this.value).show();
		    });
			
			$('#endcount').stepper({step: 1, max:100, min: 1});
		    $('#day-1-day').stepper({step: 1, max:365, min: 1});
		    $('#day-3-day').stepper({step: 1, max:365, min: 1});
		    $('#week-1-week').stepper({step: 1, max:54, min: 1});
		    $('#week-3-week').stepper({step: 1, max:54, min: 1});
		    $('#month-1-month').stepper({step: 1, max:12, min: 1});
		    $('#month-1-day').stepper({step: 1, max:365, min: 1});
		    $('#month-2-month').stepper({step: 1, max:12, min: 1});
		    $('#month-3-month').stepper({step: 1, max:12, min: 1});
		    
		    $('#enddate').datepick({
		        minDate: new Date(),
		        showOtherMonths: true,
		        selectOtherMonths: true,
		        firstDay: 0,
		        showAnim: 'slideDown',
		        showSpeed: 'fast'
		    });
		    
		    this.inited.cycle = true;
		}
		
		// 事件
		$('#add-cc').bind('click', function(){
			if ($(this).hasClass('disabled')) {
				return ;
			}
			
			me.toggleCC();
		});
		$('#add-bcc').bind('click', function(){
			if ($(this).hasClass('disabled')) {
				return ;
			}
			
			me.toggleBcc();
		});
		$('#add-date').bind('click', function(){
			if ($(this).hasClass('disabled')) {
				return ;
			}
			me.toggleDate();
		});
		$('#add-divide').bind('click', function(){
			if ($(this).hasClass('disabled')) {
				return ;
			}
			me.toggleDivide();
		});
		$('#add-reviewer').bind('click', function(){
			if ($(this).hasClass('disabled')) {
				return ;
			}
			me.toggleReview();
		});
		if (!tools.addPercent) {
			$('#add-percent').addClass('gray');
		}
		$('#add-percent').bind('click', function(){
			if (!tools.addPercent || $(this).hasClass('gray')) {
				return ;
			}
			if (!me.expand.percent) {
				if (!$('#to').val()) {
					TOP.showMessage(TOP.TEXT.TO_USER_IS_NULL);
					return ;
				}
			}
			me.togglePercent();
		});
		$('#cycle').bind('click', function(){
			me.toggleCycle(this.checked);
		});
		$('#open_pwd').bind('click', function(){
			$('#password').attr('disabled', !this.checked);
			if (!this.checked) {
				$('#password').val('').hide();
			} else {
				$('#password').val('').show();
			}
		});
		$('#secrecy').bind('click', function(){
			me.togglePrivacy(this.checked);
		});
		$('a[name="tpllist"]').bind('click', function(e){
			var editor  = me.editor,
		   	    boardId = me.boardSelect.getValue();
		   	e.srcElement = $(this).parent('span.add')[0];
			Tudu.Template.showMenu(e ,editor, boardId);
		  	TOP.stopEventBuddle(e);
		});

		$('#netdisk-btn').click(function(){
			if (me.filedialog === null) {
			    me.filedialog = new FileDialog({id: 'netdisk-dialog'});
			}

			me.filedialog.show();
		});
		
		if (!TOP.Browser.isIE && !TOP.Browser.isFF) {
			$('#screencp-btn').remove();
		} else {
			$('#link-capture').bind('click', function(){
				if (!Capturer.getCapturer()) {
					return Capturer.install();
				}
				
				Capturer.setEditor(me.editor);
	            Capturer.startCapture();
	        });
		}
		
		if (tools.divide) {
			$('#add-divide').text(TOP.TEXT.DELETE_DIVIDE);
			if (!me.isInitDivide) {
				me.divideInit();
			}
			me.addDivide(true);
			me.expand.divide = true;
			if (!me.isModify) {
				me.toInput.disabled();
			}
		}
		
		$('button[name="back"]').bind('click', function(){
			location = back;
		});
		
		$('a[name="preview-gantt"]').bind('click', function(){
			Modify.getFormPreview('#theform', '/tudu/preview-gantt', '_blank');
		});
		
		var form = $('#theform');

		$('button[name="send"],button[name="save"]').bind('click', function(){
			$('#action').val(this.name);
			Modify.send(this.name);
		});
		
		// 自动保存
		var vForm = new VirtualForm({
			fields: form.find('input, textarea, select'),
			filter: {compare: '#postcontent, #content, #tid, #bid, #type, #action, #savetime, #ftid'},
			editor: {content: me.editor}
		});
		
		$('#discuss, #notice, #meeting').bind('click', function(){
			form.find('textarea[name="content"]').val(me.editor.getSource());
			vForm.submit(this.href);
	        return false;
		});
		
		$('button[name="preview"]').bind('click', function(){
			vForm.setFields(form.find('input, textarea'));
			vForm.submit('/tudu/preview', 'post', '_blank');
			return false;
		});
		
		if (!forbid.autosave) {
			this.autoSave = new Tudu.AutoSave({
				form: vForm,
				time: 30000,
				func: Modify.send,
				forcesave: 0,
				self: Modify,
				roundComplete: function() {
		    		if (!me.autoSave.lastSaveTime) {
		    			return ;
		    		}
		    		var now = new Date(),
			    		hour = now.getHours(),
						minute = now.getMinutes(),
						time = [((hour < 10) ? "0" + hour : hour),
						        ((minute < 10) ? "0" + minute : minute)].join(':'),
		    			diff = Math.round((now - me.autoSave.lastSaveTime) / 60000);
		    		
					$('span.compose_msg').html(TOP.TEXT.AUTOSAVE_TIPS_FIRST + time + ' ( ' + diff + TOP.TEXT.AUTOSAVE_TIPS_SECOND + ' )');
					$('#savetime').val(parseInt(now.getTime() / 1000));
		    	}
			});
		}
		
		if (!forbid.claim) {
			me.acceptmode = 'claim';
		}
		
		if (me.acceptmode != 'claim') {
			this.checkChild();
		}
		
		if (forbid.flow){
			$('#add-percent').addClass('gray');
			tools.addPercent = false;
			me.toggleReview();
			me.toggleFlow(true, false);
		}
		
		if ($('#bid').val()) {
            me.boardChange();
        }
		
		if (typeof (Modify.initUnloadEvent) == 'function' && !forbid.unload) {
			Modify.initUnloadEvent('tudu', vForm, true);
	    }
		
		form.submit(function(){return false;});
		form.submit(function(){
			Modify.send($('#action').val());
		});
		
		if (me.issynchro){
			$('label[for="acceptmode"]').addClass('gray');
            $('#acceptmode').attr('checked', false);
            $('#acceptmode').attr('disabled', true);
		}
		
		me.clearCast();
		
		setTimeout(function(){vForm.save();}, 1000);
		
		if (accepter.length > 0) {
			var ids = [];
			var to = [];
			for (var k = 0; k < accepter.length; k++) {
				ids.push(accepter[k].username);
				to.push(accepter[k]);
			}
			
			this.toInput.getItems().each(function(){
                var item = $(this);
				if (typeof item.attr('_id') != 'undefined' && !TOP.Util.inArray(item.attr('_id'), ids)) {
					to.push({
						username: item.attr('_id'),
						truename: item.attr('name')
					});
				}
            });
			this.toInput.clear();
			var size = 0;
			for (var l = 0; l < to.length; l++) {
				size++;
				_appendUser(to[l].truename, to[l].username);
				if (size != to.length) {
					_appendUser('+', '+', true);
				}
			}
		}
		
		function _appendUser(userName, userEmail, separator) {
			var params = {};
            params.title = userName + (userEmail ? '$lt;' + userEmail + '&gt;' : '');
            params._id = userEmail ? userEmail : '';
            if (separator) {
                params.name = params._id = params.title = params.separator = '+';
            }
            Modify.toInput.addItem(userName, params);
        }
	},
	
	toggleFlow: function(isFlow, isChild) {
		var me = this;
		if (!isChild) {
			isChild = false;
		}
		
		if (!isChild) {
			if (isFlow) {
				if (me.expand.divide) {
					me.toggleDivide();
				}
				if (me.expand.review) {
					me.toggleReview();
				}
				$('#add-reviewer, #add-divide').addClass('disabled');
				
				if (me.action != 'forward') {
				    $('#row-to').hide();
				}
				
				me.toInput.clear();
				
				$('#cycle, #isauth, #acceptmode').attr('disabled', true);
				$('label[for="cycle"], label[for="isauth"], label[for="acceptmode"]').addClass('gray');
			} else {
				$('#flow-steps').hide();
				if (!me.expand.divide){
					$('#add-reviewer').removeClass('disabled')
				}
				$('#add-divide').removeClass('disabled');
				$('#row-to').show();
				$('#cycle, #isauth, #acceptmode').attr('disabled', false);
				$('label[for="cycle"], label[for="isauth"], label[for="acceptmode"]').removeClass('gray');
			}
			var attach = me.curFlowAttch.attach;
			for (var i=0; i<attach.length; i++) {
				$('#attach-list td.bd_upload').find('#attach-' + attach[i]).remove();
			}
			if (!$('#attach-list div.filecell').size()) {
				$('#attach-list').hide();
			}
			me.curFlowAttch.attach = [];
		} else {
			if (isFlow) {
				$('#ch-row-to').hide();
				me.chToInput.clear();
				if (me.chReviewerInput !== null) {
					$('#ch-row-reviewer').hide();
					me.chReviewerInput.clear();
				}
				$('#ch-add-review').addClass('disabled');
				$('#ch-isauth, #ch-acceptmode').attr('disabled', true);
				$('label[for="ch-isauth"], label[for="ch-acceptmode"]').addClass('gray');
				
				$('#ch-add-percent').addClass('gray');
				me.chExpand.percent = false;
				$('#ch-row-percent').hide();
				$('#ch-add-percent').text(TOP.TEXT.ADD_PERCENT);
				
			} else {
				if (me.isFromTudu) {
					$('#ch-add-percent').removeClass('gray');
				}
				$('#ch-row-to').show();
				$('#ch-add-review').removeClass('disabled');
				$('#ch-flow-steps').empty();
				$('#ch-flow-steps').hide();
				$('#ch-isauth, #ch-acceptmode').attr('disabled', false);
				$('label[for="ch-isauth"], label[for="ch-acceptmode"]').removeClass('gray');
				
			}
			var attach = me.curFlowAttch.chAttach;
			for (var i=0; i<attach.length; i++) {
				$('#ch-attach-list td.bd_upload').find('#ch-attach-' + attach[i]).remove();
			}
			if (!$('#ch-attach-list div.filecell').size()) {
				$('#ch-attach-list').hide();
			}
			me.curFlowAttch.chAttach = [];
		}
		
	},
	
	claimUsers: null,
	
	showClaimUser: function(objId, isChild, tuduId) {
		var me = this;
		if ($('#' + objId).attr('checked') == false) {
			return ;
		}
		
		if (!tuduId) {
			return ;
		}
		
		if (me.claimUsers !== null) {
			var params = [], toArr = [], toStr = null;
			
			if (isChild) {
				var to = $('#ch-to').val().split("\n");
			} else {
				var to = $('#to').val().split("\n");
			}
			
            for (var i = 0, c = to.length; i < c; i++) {
                var a = to[i].split(' ');
                if (a[1]) {
                    toArr.push(a[0]);
                }
            }
            
            if (toArr.length) {
            	toStr = toArr.join('|');
            }

			for (var i=0; i < claimUsers.length; i++) {
				if (claimUsers[i].tuduid == tuduId && toStr.indexOf(claimUsers[i].email) == -1) {
					params.title = claimUsers[i].truename + (claimUsers[i].email ? '$lt;' + claimUsers[i].email + '&gt;' : '');
					params._id = claimUsers[i].email ? claimUsers[i].email : '';
					if (isChild) {
						me.chToInput.addItem(claimUsers[i].truename, params);
					} else {
						me.toInput.addItem(claimUsers[i].truename, params);
					}
				}
			}
		}
	},
	
	/**
	 * 构造表单，提交预览或页面数据传递
	 * @param form
	 * @param target
	 * @return
	 */
	getFormPreview: function(form, address, target) {
		$('#postcontent').val(Modify.editor.getSource());
		var data = $(form).serializeArray();
		var form = $('<form action="'+address+'" method="post" target="'+target+'" style="display:none"></form>');
		for (var key in data) {
			form.append('<textarea name="' + data[key].name + '">' + data[key].value + '</textarea>');
		}
		form.append('<input name="autosave" value="1" />');
		form.appendTo(document.body).submit();
	},
	
	/**
	 * 是否执行清除CAST数据
	 */
	clearCast: function() {
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
	},
	
	// 离开页面提示
	initUnloadEvent: function(type, vform, isnew){
		// 判断离开回复提示的类型 
		var editor  = this._editor,
			form    = $('#theform');
		
		$('button[name="save"], button[name="send"]').bind('click', function(){vform.save();});
		form.bind('submit', function(){vform.save();});
		TOP.getJQ()('a:not([href^="javascript:"]):not([href^="/tudu/modify"]):not([target="_blank"])').bind('click', _leaveDialog);
		$('a:not(.xheButton):not([href^="javascript:"]):not([href^="/tudu/modify"])').bind('click', _leaveDialog);
	
		TOP.getJQ()('form').bind('submit', _leaveDialog);
		TOP.window.onbeforeunload = function() {
			if (!vform.invariant()) {
				return TOP.TEXT.COMPOSE_EXIT_WITHOUT_SAVE;
			}
		};
		window.onunload = function(){
			TOP.getJQ()('a').unbind('click', _leaveDialog);
			TOP.getJQ()('form').unbind('submit', _leaveDialog);
		};
		
		function _leaveDialog(e) {
			if (vform.invariant()) {
				return true;
			}
			
			var trigger = $(this);
			
			TOP.Label.focusLabel();
			
			TOP.Frame.Dialog.show({
				title: TOP.TEXT.LEAVE_HINT,
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
							Modify.send('save', function(){
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
	},
	
	/**
	 * 移除附件
	 */
	removeAttach: function(aid) {
		$('#attach-' + aid).remove();
		if (!$('#attach-list div.filecell').size()) {
			$('#attach-list').hide();
		}
	},
	
	/**
	 * 工作附件
	 */
	curFlowAttch: {
		attach: [],
		chAttach: []
	},
	
	/**
	 * 加载工作流数据
	 */
	loadFlow: function(flowId, isChild) {
		var me = this;
		if (!isChild) {
			isChild = false;
		}
		
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: '/tudu/flow?flowid=' + encodeURIComponent(flowId),
			success: function(ret) {
				if (ret.success && ret.data) {
					var flow = ret.data.flow,
						attach = ret.data.attach;
					_appendEditor(flow.content, isChild);
					if (flow.elapsedtime) {
						_setEndDate(flow.elapsedtime, isChild);
					}
					
					_setCc(flow.cc, isChild);
					_setProcess(ret.data.steps, isChild);
					_setAttach(attach, isChild);
				}
			},
			error: function(res) {
				return ;
			}
		});
		
		// 附件html元素
		var itemtpl = [
        	'<div class="filecell"><input type="hidden" name="attach[]" value="" />'
        	,'<div class="attsep"><div class="attsep_file"><span class="icon icon_add"></span><span class="filename"></span>&nbsp;<span class="filesize"></span></div>'
            ,'<div class="attsep_del"><a href="javascript:void(0)" name="delete">' + TOP.TEXT.DELETE + '</a></div>'
            ,'<div class="clear"></div></div></div>'
        ].join('');
		
		function _setCc(cc, isChild) {
			if (cc.length <= 0) {
				return ;
			}
			for (var i=0; i<cc.length; i++) {
				var title = cc[i].name + (cc[i].email ? '$lt;' + cc[i].email + '&gt;' : '');
                var id = cc[i].email ? cc[i].email : '';
				var params = {title: title, _id: id};
				
				if (!isChild) {
					if (!me.expand.cc) {
	                    me.toggleCC();
	                }
					me.ccInput.addItem(cc[i].name, params);
				} else {
					if (!me.chExpand.cc) {
						$('#ch-cc, #ch-cc-text').attr('disabled' ,false);
		                $('#ch-row-cc').show();
		                $('#ch-add-cc').text(TOP.TEXT.DELETE_CC);

						me.chExpand.cc = true;
					}
					me.chCcInput.addItem(cc[i].name, params);
				}
				
			}
		}
		
		// 处理显示工作流程
		function _setProcess(steps, isChild) {
			if (!isChild) {
				var obj = '#flow-steps';
			} else {
				var obj = '#ch-flow-steps';
			}
			
			if (steps.length <= 0) {
				$(obj).empty();
				$(obj).hide();
				return ;
			}
			$(obj).html(steps);
			$(obj).show();
		}
		
		// 附件列表
		function _setAttach(attach, isChild) {
			if (!isChild) {
				var container = $('#attach-list td.bd_upload'),
					list = $('#attach-list');
			} else {
				var container = $('#ch-attach-list td.bd_upload'),
					list = $('#ch-attach-list');
			}
			
			if ($('#attach-list div.filecell').size() <= 0 && attach.length <= 0) {
				container.empty();
				list.hide();
				return ;
			}
			if (attach.length > 0) {
				list.show();
				for (var i=0; i<attach.length; i++) {
					var el = $(itemtpl),
						fileId = attach[i].fileid;
					
					if (!isChild) {
						if (list.find('#attach-' + fileId).size()) {
							return ;
						}
						me.curFlowAttch.attach.push(fileId);
						el.attr('id', 'attach-' + fileId);
					} else {
						if (list.find('#ch-attach-' + fileId).size()) {
							return ;
						}
						me.curFlowAttch.chAttach.push(fileId);
						el.attr('id', 'ch-attach-' + fileId);
					}
					
					el.find('.filename').text(attach[i].filename);
					el.find(':hidden[name="attach[]"]').val(fileId);
					
					fileSize = attach[i].size > 1024 ? Math.round(attach[i].size / 1024, 2) + 'KB' : attach[i].size + 'bytes',
					el.find('.filesize').text('(' + fileSize + ')');
					el.find('a[name="delete"]').bind('click', function(e){
						var e = $(e.target).closest('.filecell');
						e.remove();
						if (!$('#attach-list div.filecell').size()) {
							$('#attach-list').hide();
						}
					});
					el.appendTo(container);
				}
			}
		} 
		
		// 截止时间
		function _setEndDate(elapsedtime, isChild) {
			if (!isChild) {
				var st = $('#starttime').val(),
					target = $('#endtime');
			} else {
				var date = new Date(),
					st = formatTime(date, 'yyyy-mm-dd'),
					target = $('#ch-endtime');
				
				$('#ch-starttime').val(st);
				if (!me.chExpand.date) {
					me.chDateInit();
					$('#ch-starttime, #ch-endtime').attr('disabled', false);
					$('#ch-row-date').show();
					$('#ch-add-date').text(TOP.TEXT.DELETE_DATE);
					me.chExpand.date = true;
				} 
			}
			var arr = st.split('-'),
			    date = new Date();
			date.setFullYear(arr[0]);
			date.setMonth(arr[1]);
			date.setDate(arr[2]);
			date.setDate(date.getDate() + elapsedtime);
			
			var time = formatTime(date, 'yyyy-mm-dd');
			target.val(time);
		}
		
		// 向编辑器添加内容
		function _appendEditor(content, isChild) {
			if (!isChild) {
				var value = $('#content').val();
				me.editor.setSource(value + content);
			} else {
				if (!me.chExpand.content) {
					$('#ch-row-content').show();
					$('#add-content').text(TOP.TEXT.DELETE_CONTENT);
					
					me.chEditorInit();
					me.chExpand.content = true;
				}
				var value = $('#ch-content').val();
				me.chEditor.setSource(value + content);
			}
		}
	},
	
	/**
	 * 更改工作流
	 */
	flowChange: function(obj, isChild) {
		var flowId = $(obj).val();
		
		if (!isChild) {
			isChild = false;
		}
		if (flowId) {
			this.toggleFlow(true, isChild)
			this.loadFlow(flowId, isChild);
		} else {
			this.toggleFlow(false, isChild)
		}
	},
	
	boardChange: function() {
		if ($('#board-input').attr('disabled')) {
			return ;
		}
		this.toggleFlow(false, false);
		
		var item = this.boardSelect.getSelected(),
			bid  = item ? item.boardid : null;
		
		var isNeedConfirm = item && item.needconfirm;
		$('#needconfirm').attr('checked', isNeedConfirm);
		
		if (!this.chBoardSelect.getValue()) {
			$('#ch-needconfirm').attr('checked', isNeedConfirm);
		}
		
		$('#class-td').hide();
		if (item && item.isclassify) {
	    	$('#classid').empty();
	    } else {
	        $('#classid').empty();
	        $('#classid').prepend('<option value="">--' + TOP.TEXT.NONE + '--</option>');
	    }
		
		if (item && item.privacy) {
			TOP.showMessage(TOP.TEXT.TUDU_MODIFY_PRIVACY_TIPS, 3000, 'success');
		}

		if (bid) {
			var flowOnly = item && item.flowonly;
			if ($('#flow-id').val() && $('#flow-bid').val() == bid) {
				this.loadFlows(bid, '#tudu-flow', '#flowid', $('#flow-id').val(), flowOnly);
				this.flowChange('#flow-id', false);
            } else {
				this.loadFlows(bid, '#tudu-flow', '#flowid', null, flowOnly);
			}

			if ($('#flow-bid').val() != bid) {
				$('#flow-id, #flow-bid').val('');
			}
			
			var classId = null;
			if ($('#cid').val()) {
				classId = $('#cid').val();
			}
			
			this.loadClasses(bid, '#classid', classId);
			$('#cid').val('');
		}
	},
	
	/**
	 * 新建主题分类
	 */
	createClass: function(bid, select, isChild) {
		var _$ = TOP.getJQ(),
			me = this;
		var btns = [
		   {
			   text: TOP.TEXT.CONFIRM,
			   cls: 'btn',
			   events: {click: function(){
			   	   _sumbitClass();
			   }}
		   },
		   {
                text: TOP.TEXT.CANCEL,
                cls: 'btn close',
                events: {click: function(){
                	Win.close();
                	$(select + ' option:first').attr('selected', 'selected');
                }}
            }
 		];
 		
 		var Win = TOP.Frame.Dialog.show({
 			title: TOP.TEXT.CREATE_BOARD_CLASS,
 			body: '<div style="margin:10px;"><form id="classform" action="/board/classes"><div>' + TOP.TEXT.BOARD_CLASS_SUBJECT + TOP.TEXT.CLN + '<input class="input_text" name="classname" type="text" style="width:300px;" value="" /></div></form></div>',
 			buttons: btns
 		});
 		
 		function _sumbitClass() {
 			var form = _$('#classform'),
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
	                		// 图度子分工
	                		if (isChild) {
	                			$('#ch-classid').prepend('<option title="'+data.cn+'" value="'+data.cid+'">'+data.cn+'</option>');
		                		$('#ch-classid').val(data.cid);
		                	// 图度
	                		} else {
	                			$('#classid').prepend('<option title="'+data.cn+'" value="'+data.cid+'">'+data.cn+'</option>');
		                		$('#classid').val(data.cid);
	                		}
	                	}
	                	// 由于新添加了主题分类，所以清空保存的数据
	                	me.classes = {};
	                	Win.close();
	                }
	            },
	            error: function(res) {
	            	form.find(':input').attr('disabled', false);
	                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
	            }
	        });
 		}
	},
	
	percentNum: 0,
	
	appendPercentList: function(param, list, prefix) {
		if (!prefix) prefix = '';
		
		if (!param) return ;
		
		if (this.percentNum == 0) {
			list.empty();
		}
		
		var p = $('<p class="percent-item">');
		p.append('<input type="hidden" name="'+prefix+'toidx[]" value="'+this.percentNum+'" />')
		 .append('<input type="hidden" name="'+prefix+'to-'+this.percentNum+'" value="'+ param.email + ' ' + param.truename +'" />');
		
		var pinput = $('<input type="text" class="input_text" style="width:60px" name="'+prefix+'to-percent-'+this.percentNum+'" />');
		p.append(pinput);
		pinput.stepper({step: 25, max:100, format: 'percent'});
		pinput.val(param.percent + '%');
		
		p.append('&nbsp;&nbsp;'+ param.truename + (param.email ? '<span class="gray">&lt;'+param.email+'&gt;</span>' : ''));
		
		list.append(p);
		
		this.percentNum = this.percentNum + 1;
		
	},
	
	updatePercentList: function(list, to, prefix) {
		if (!prefix) prefix = '';
		if (!to) {
			var t = $('#' + prefix + 'to').val(), arr;
			t = t.split("\n");
			to = [];
			for (var i = 0, c = t.length; i < c; i++) {
				arr = t[i].split(' ');
				var o = {email: arr[0]};
				delete arr[0];
				o.name = arr.join(' ');
				to.push(o);
			}
		}
		
		var num = [], percent = [];
		if (list.find('p.percent-item').size()) {
			list.find('p.percent-item').each(function(){
				n = $(this).find('input[name="'+prefix+'toidx[]"]').val();
				num.push(n);
			});
			for (var i = 0, c = num.length; i < c; i++) {
				list.find('p.percent-item').each(function(){
					key = $(this).find('input[name="'+prefix+'to-'+num[i]+'"]').val();
					per = $(this).find('input[name="'+prefix+'to-percent-'+num[i]+'"]').val();
					percent[key] = per;
				});
			}
		}
		
		list.empty();
		
		for(var i = 0, c = to.length; i < c; i++) {
			if (percent[to[i].email + to[i].name]) {
				tuduPercent = percent[to[i].email + to[i].name];
			} else {
				tuduPercent = this.tuduPercent;
			}
			
			var p = $('<p class="percent-item">');
			p.append('<input type="hidden" name="'+prefix+'toidx[]" value="'+i+'" />')
			 .append('<input type="hidden" name="'+prefix+'to-'+i+'" value="'+ to[i].email + to[i].name +'" />');
			
			var pinput = $('<input type="text" class="input_text" style="width:60px" name="'+prefix+'to-percent-'+i+'" value="'+tuduPercent+'" />');
			p.append(pinput);
			pinput.stepper({step: 25, max:100, format: 'percent'});
			
			p.append('&nbsp;&nbsp;'+ to[i].name + (to[i].email ? '<span class="gray">&lt;'+to[i].email+'&gt;</span>' : ''));
			
			list.append(p);
		}
	},
	
	toggleCC: function() {
		if (!this.expand.cc) {
			$('#cc, #i-cc').attr('disabled', false);
			$('#row-cc').show();
			$('#add-cc').text(TOP.TEXT.DELETE_CC);
		} else {
			$('#cc, #i-cc').attr('disabled', true);
			$('#row-cc').hide();
			$('#add-cc').text(TOP.TEXT.ADD_CC);
		}
		
		this.expand.cc = !this.expand.cc;
		
		TOP.Cookie.set('TUDU-EXP-CC', this.expand.cc ? 1 : 0, {expires: 86400000 * 365});
	},
	
	toggleBcc: function() {
		if (!this.expand.bcc) {
			$('#bcc, #i-bcc').attr('disabled', false);
			$('#row-bcc').show();
			$('#add-bcc').text(TOP.TEXT.DELETE_BCC);
		} else {
			$('#bcc, #i-bcc').attr('disabled', true);
			$('#row-bcc').hide();
			$('#add-bcc').text(TOP.TEXT.ADD_BCC);
		}
		
		this.expand.bcc = !this.expand.bcc;
	},
	
	togglePercent: function() {
		if (!this.expand.percent) {
			$('#row-percent').show();
			$('#add-percent').text(TOP.TEXT.DELETE_PERCENT);
		} else {
			$('#row-percent').hide();
			$('#add-percent').text(TOP.TEXT.ADD_PERCENT);
		}
		
		this.expand.percent = !this.expand.percent;
	},
	
	setReviewInfo: function(editor, names) {
		var source = editor.getSource();
		
        var div = $('<div>');
        div.html(source);
        
    	var text = TOP.formatString(TOP.TEXT.REVIEW_INFO, names.join(','));

    	if (names.length > 0) {
            if (names.length > 1) {
            	var html = '<strong>'+TOP.TEXT.REVIEW+TOP.TEXT.CLN+'</strong><span style="color:#aaa">'+ TOP.TEXT.REVIEW_MORE + text+'</span>';
            } else if (names.length == 1) {
            	var html = '<strong>'+TOP.TEXT.REVIEW+TOP.TEXT.CLN+'</strong><span style="color:#aaa">'+text+'</span>';
            }
            
            if (div.find('p[_name="review"]').size()) {
                div.find('p[_name="review"]').html(html);
            } else {
                div.prepend('<p _name="review">'+html+'</p><br />');
            }
    	} else {
    		div.find('p[_name="review"]').remove();
    	}
    	
    	editor.setSource(div.html());
	},
	
	toggleReview: function() {
		if (!this.expand.review) {
			var names = [];
			this.reviewInput.getItems().each(function(){
				var item = $(this);
				names.push([item.attr('name')]);
			});
			this.setReviewInfo(this.editor, names);
			
			$('#reviewer, #i-reviewer').removeAttr('disabled');
			$('#row-reviewer').show();
			$('#add-reviewer').text(TOP.TEXT.DELETE_REVIEW);
			$('#add-divide').addClass('disabled');
		} else {
			var source = this.editor.getSource(),
				div = $('<div>');
		    div.html(source);
		    div.find('p[_name="review"]').remove();
		    this.editor.setSource(div.html());
			$('#reviewer, #i-reviewer').attr('disabled', true);
			$('#row-reviewer').hide();
			$('#add-reviewer').text(TOP.TEXT.ADD_REVIEW);
			$('#add-divide').removeClass('disabled');
		}
		
		this.expand.review = !this.expand.review;
		
		TOP.Cookie.set('TUDU-EXP-REVIEW', this.expand.review ? 1 : 0, {expires: 86400000 * 365});
	},

	toggleDivide: function() {
		if (!this.expand.divide) {
			$('#divide-box').show();
			$('#add-divide').text(TOP.TEXT.DELETE_DIVIDE);
			if (!this.isInitDivide) {
				this.divideInit();
			}
			this.addDivide();
			
			var newAccepter = new Array(),
				userEmail = $('#user-msg').val(),
				userName = $('#user-msg').attr('_name');
			for (var i=0; i<this.accepter.length; i++) {
				newAccepter.push({email: this.accepter[i].email, truename: this.accepter[i].truename});
			}

			if (!this.toInput.getItems('[_id="' + userEmail + '"]').size()) {
			    newAccepter.push({email: userEmail, truename: userName});
			    this.toInput.setParam('accepter', newAccepter);
			}

			window.scrollTo(0, document.body.offsetHeight);
			
			$('#row-reviewer').hide();
			$('#add-reviewer').addClass('disabled');
			$('#reviewer, #i-reviewer').attr('disabled', 'disabled');

		} else {
			
			var me = this;
			if (!me.tools.percent) {
				$('#add-percent')
				.removeClass('gray')
				.bind('click', function(){
					if (!me.expand.percent) {
						if (!$('#to').val()) {
							TOP.showMessage(TOP.TEXT.TO_USER_IS_NULL);
							return ;
						}
					}
					me.togglePercent();
				});
			}
			if ($('#percent-list').find('p.percent-item').size() > 0) {
				$('#percent-list').find('input').each(function (){$(this).attr('disable', false);});
			}
			me.initSelectLink('#select-to', me.toInput, $('#i-to'), false);
			$('label[for="cycle"]').removeClass('gray');
			$('#cycle').attr('disabled', false);
			
			if (me.acceptmode != 'claim') {
				$('label[for="acceptmode"]').removeClass('gray');
				$('#acceptmode').attr('disabled', false);
			}
			
			me.clearDivide();
			$('#children-list').find('div.tudu_children_item').each(function(){
				$(this).remove();
			});
			$('#divide-box').hide();
			me.toInput.setParam('accepter', me.accepter);
			$('#add-divide').text(TOP.TEXT.ADD_DIVIDE);
			
			$('#add-reviewer').removeClass('disabled');
		}
		
		this.expand.divide = !this.expand.divide;
	},
	
	/**
	 * 加载日期控件
	 */
	dateInit: function() {
		if (!this.datepickers) {
			this.datepickers = {};
			
			if ($('#starttime').size()) {
				this.datepickers.starttime = $('#starttime').datepick({
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
			}
			
			if ($('#endtime').size()) {
				this.datepickers.endtime = $('#endtime').datepick({
			        minDate: new Date(),
			        showOtherMonths: true,
			        selectOtherMonths: true,
			        showAnim: 'slideDown',
			        showSpeed: 'fast',
			        firstDay: 0,
			        onSelect: function(dates){}
			    });
			}
		}
	},
	
	toggleDate: function() {
		if (!this.expand.date) {
			this.dateInit();
			$('#starttime, #endtime').attr('disabled', false);
			$('#row-date').show();
			$('#add-date').text(TOP.TEXT.DELETE_DATE);
		} else {
			$('#starttime, #endtime').attr('disabled', true);
			$('#row-date').hide();
			$('#add-date').text(TOP.TEXT.ADD_DATE);
		}
		
		this.expand.date = !this.expand.date;
		
		TOP.Cookie.set('TUDU-EXP-DATE', this.expand.date ? 1 : 0, {expires: 86400000 * 365});
	},
	
	/**
	 * 初始化重复周期
	 */
	toggleCycle: function(expand) {
		if (expand) {
			if (!this.inited.cycle) {
				// cycle
				$('#mode-group :radio[name="mode"]').click(function(){
			        $('div.method').hide();
			        $('#mode-' + this.value).show();
			    });
				
				$('#endcount').stepper({step: 1, max:100, min: 1});
			    $('#day-1-day').stepper({step: 1, max:365, min: 1});
			    $('#day-3-day').stepper({step: 1, max:365, min: 1});
			    $('#week-1-week').stepper({step: 1, max:54, min: 1});
			    $('#week-3-week').stepper({step: 1, max:54, min: 1});
			    $('#month-1-month').stepper({step: 1, max:12, min: 1});
			    $('#month-1-day').stepper({step: 1, max:365, min: 1});
			    $('#month-2-month').stepper({step: 1, max:12, min: 1});
			    $('#month-3-month').stepper({step: 1, max:12, min: 1});
			    
			    $('#enddate').datepick({
			        minDate: new Date(),
			        showOtherMonths: true,
			        selectOtherMonths: true,
			        firstDay: 0,
			        showAnim: 'slideDown',
			        showSpeed: 'fast'
			    });
			    
			    this.inited.cycle = true;
			}
			
			$('#extend-box, #block-cycle').show();
			
			this.expand.cycle = true;
		} else {
			$('#block-cycle').hide();
			if (!$('#bolck-cycle:visible').size() && !$('#block-privacy:visible').size()) {
				$('#extend-box').hide();
			}
			
			this.expand.cycle = false;
		}
	},
	
	/**
	 * 切换私密
	 */
	togglePrivacy: function(expand) {
		if (expand) {
			if (!this.inited.privacy) {
				this.inited.privacy = true;
			}
			
			$('#extend-box, #block-privacy').show();
			
			this.expand.privacy = true;
		} else {
			$('#block-privacy').hide();
			$('#password').val('').hide();
			$('#open_pwd').attr('disabled', true).attr('checked', false);
			if (!$('#bolck-cycle:visible').size() && !$('#block-privacy:visible').size()) {
				$('#extend-box').hide();
			}
			
			this.expand.privacy = false;
		}
	},
	
	/**
	 * 初始化分工
	 */
	divideInit: function() {
		var me = this;
		
		me.divide = 'create';

		this.chBoardSelect.bind('select', function(){
			if (me.divide == 'update') {
				return ;
			}
			var item = this.getSelected(),
				boardid = item ? item.boardid : null,
				flowOnly = item && item.flowonly;
			if (item && item.needconfirm) {
	            $('#ch-needconfirm').attr('checked', true);
	        } else {
	        	$('#ch-needconfirm').attr('checked', false);
	        }
			if (item && item.isclassify) {
		    	$('#ch-classid').empty();
		    } else {
		        $('#ch-classid').empty();
		        if ($('#bid').val() == $('#ch-bid').val()) {
		        	$('#ch-classid').prepend('<option value="">--' + TOP.TEXT.INHERIT_PARENT + '--</option>');
		        } else {
		        	$('#ch-classid').prepend('<option value="">--' + TOP.TEXT.NONE + '--</option>');
		        }
		    }
			if (item && item.privacy) {
				TOP.showMessage(TOP.TEXT.TUDU_MODIFY_PRIVACY_TIPS, 3000, 'success');
			}
			if (me.childIsFlow || !me.isFromTudu) {
				me.loadFlows(boardid, '#ch-tudu-flow', '#ch-flowid', null, flowOnly);
			}
			me.loadClasses(boardid, '#ch-classid', null, true);
			
			if (null == boardid) {
				var board = me.boardSelect.getSelected(),
				    bid   = board ? board.boardid : null,
					flowOnly = board && board.flowonly;
				me.loadClasses(bid, '#ch-classid', null, true);
				me.loadFlows(bid, '#ch-tudu-flow', '#ch-flowid', null, flowOnly);
			}
		});
		
		$('#ch-flowid').bind('change', function(){
			me.flowChange('#ch-flowid', true);
		});
		
		if (me.chToInput === null) {
			me.chToInput = new TOP.ContactInput({
		        id: 'ch-to-input', 
		        target: $('#ch-to-text'), 
		        valuePlace: $('#ch-to'), 
		        group: false,
				review: true,
				synchro: true,
		        onUpdate: function() {
					var _this = this, percent = true;
                    this._settings.jq('label[for="ch-acceptmode"]').removeClass('gray');
                    this._settings.jq('#ch-acceptmode').attr('disabled', false);
                    this.getItems().each(function(){
                        var item = $(this);
                        if (item.hasClass('icon_flow_arrow')) {
                            _this._settings.jq('label[for="ch-acceptmode"]').addClass('gray');
                            _this._settings.jq('#ch-acceptmode').attr('checked', false);
                            _this._settings.jq('#ch-acceptmode').attr('disabled', true);
							percent = false;
                        }
                    });
					if (me.divide != 'create') {
						if (percent) {
							_this._settings.jq('#ch-add-percent').removeClass('gray');
						} else {
							_this._settings.jq('#ch-add-percent').addClass('gray');
							me.chExpand.percent = false;
							_this._settings.jq('#ch-row-percent').hide();
							_this._settings.jq('#ch-add-percent').text(TOP.TEXT.ADD_PERCENT);
						}
					}

					var chTo = this._settings.valuePlace.val().split("\n"), chToArr = [];
	                for (var i = 0, c = chTo.length; i < c; i++) {
	                    var a = chTo[i].split(' ');
	                    if (a[1]) {
	                    	chToArr.push({email: a[0], name: a[1]});
	                    }
	                }
	                me.updatePercentList($('#ch-percent-list'), chToArr, 'ch-')
				},
		        jq: jQuery
		    });
		}
		
		me.chReviewerInput = new TOP.ContactInput({
	        id: 'ch-reviewer-input', 
	        target: $('#ch-reviewer-text'), 
	        valuePlace: $('#ch-reviewer'), 
	        group: false,
	        review: true,
	        onUpdate: function() {
				
			},
	        jq: jQuery
	    });
		
		me.initSelectLink('#ch-select-reviewer', me.chReviewerInput, $('#ch-reviewer'), false, true, false, TOP.TEXT.SELECT_REVIEWER_TIPS, TOP.TEXT.SWITCH_REVIEW_TYPE);
		me.initSelectLink('#ch-select-to', me.chToInput, $('#ch-to'), false, true, true, TOP.TEXT.SELECT_TO_TIPS, TOP.TEXT.SWITCH_EXECUTE_TYPE);
		
		if (me.chCcInput === null) {
			me.chCcInput = new TOP.ContactInput({
		        id: 'ch-cc-input', 
		        target: $('#ch-cc-text'), 
		        valuePlace: $('#ch-cc'), 
		        group: true,
		        jq: jQuery
		    });
		}
		
		me.initSelectLink('#ch-select-cc', me.chCcInput, $('#ch-cc'), true);
		
		if (me.chBccInput === null) {
			me.chBccInput = new TOP.ContactInput({
		        id: 'ch-bcc-input', 
		        target: $('#ch-bcc-text'), 
		        valuePlace: $('#ch-bcc'), 
		        group: true,
		        jq: jQuery
		    });
		}
		
		me.initSelectLink('#ch-select-bcc', me.chBccInput, $('#ch-bcc'), true);
		
		$('#ch-add-cc').bind('click', function(){
			_toggleCc();
		});
		$('#ch-add-bcc').bind('click', function(){
			_toggleBcc();
		});
		$('#ch-add-review').bind('click', function(){
			if (!$(this).hasClass('disabled')) {
				_toggleReviewer();
			}
		});
		$('#ch-add-date').bind('click', function(){
			_toggleDate();
		});
		if (me.divide == 'create') {
			$('#ch-add-percent').addClass('gray');
		}
		$('#ch-add-percent').bind('click', function(){
			if (me.divide == 'create' || $(this).hasClass('gray')) {
				return ;
			}
			if (!me.chExpand.percent) {
				if (!$('#ch-to').val()) {
					TOP.showMessage(TOP.TEXT.TO_USER_IS_NULL);
					//return me.toInput.focus();
					return ;
				}
			}
			_togglePercent();
		});
		$('#add-content').bind('click', function(){
			_toggleContent();
		});
		$('#ch-add-prev').bind('click', function(){
			_togglePrev();
		});
		
		$('a[name="ch-tpllist"]').click(function(e) {
	    	var editor  = me.chEditor,
	    		boardId = me.chBoardSelect.getValue();
	    	if(!boardId) {
	    		var boardId = me.boardSelect.getValue();
	    	}
			Tudu.Template.showMenu(e ,editor, boardId);
	   		TOP.stopEventBuddle(e);
	    });
		
		$('#urgent').click(function(){
	    	if ($(this).attr('checked') == true) {
	    		$('#ch-priority').attr('checked', true);
	    	} else {
	    		$('#ch-priority').attr('checked', false);
	    	}
	    });
		$('#secrecy').click(function(){
	    	if ($(this).attr('checked') == true) {
	    		$('#ch-privacy').attr('checked', true);
	    	} else {
	    		$('#ch-privacy').attr('checked', false);
	    	}
	    	var checked = this.checked;
	        $('#ch-block-private').css('display', checked ? '' : 'none');
	    });
		$('#needconfirm').click(function(){
	    	if ($(this).attr('checked') == true) {
	    		$('#ch-needconfirm').attr('checked', true);
	    	} else {
	    		$('#ch-needconfirm').attr('checked', false);
	    	}
	    });
		$('#notifyall').click(function(){
	    	if ($(this).attr('checked') == true) {
	    		$('#ch-notifyall').attr('checked', true);
	    	} else {
	    		$('#ch-notifyall').attr('checked', false);
	    	}
	    });
		$('#isauth').click(function(){
	    	if ($(this).attr('checked') == true) {
	    		$('#ch-isauth').attr('checked', true);
	    	} else {
	    		$('#ch-isauth').attr('checked', false);
	    	}
	    });
		
		$('#ch-privacy').click(function(){
	    	var checked = this.checked;
	        $('#ch-block-private').css('display', checked ? '' : 'none');
	        if (checked) {
	        	window.scrollTo(0, $('#ch-block-private').offset().top);
	        }
	    	if ($(this).attr('checked') == false) {
	        	$('#ch-password').val('');
	            $('#ch-open_pwd').attr('checked', false);
	            $('#ch-password').attr('disabled', true);
	        }
	    });
	    $('#ch-open_pwd').click(function(){
	    	if ($(this).attr('checked') == true) {
				$("input[name='ch-password']").each(function() {
	    			$(this).attr('disabled', false);
	    			$('#ch-password').focus();
	    		});
	    	} else {
	    	    $("input[name='ch-password']").each(function() {
	    			$(this).attr('disabled', true);
	    			$('#ch-password').val('');
	    		});
	    	}
	    });
		
		$('#ch-map-btn').bind('click', function(){
			//me.chEditor.showIframeModal('Google 地图','/googlemap/googlemap.html',function(v){me.chEditor.pasteHTML('<img src="'+v+'" />');},538,404);
			me.chEditor.getEditor().loadPlugin('googlemap', function() {
                me.chEditor.getEditor().plugin.mapDialog();
            });
	    });
		
		var fd = new FileDialog({id: 'ch-netdisk-dialog', list: $('#ch-attach-list td.bd_upload'), listCt: $('#ch-attach-list')});

	    $('#ch-netdisk-btn').click(function(){
	        fd.show();
	    });
	    
	    if (!TOP.Browser.isIE && !TOP.Browser.isFF) {
			$('#ch-screencp-btn').remove();
		} else {
			$('#ch-link-capture').bind('click', function(){
				if (!Capturer.getCapturer()) {
					return Capturer.install();
				}
				
				Capturer.setEditor(me.chEditor);
	            Capturer.startCapture();
	        });
		}
	    
	    $('button[name="save-child"]').bind('click', function(){
	    	if (!$('#ch-subject').val()) {
				return TOP.showMessage(TOP.TEXT.TUDU_SUBJECT_IS_NULL);
			}
			/*
			if (!$('#ch-to').val()) {
				$('#ch-to').focus();
				return TOP.showMessage(TOP.TEXT.TO_USER_IS_NULL);
			}*/
	        
	        var to = me.chToInput.getItems();
			var i = 0;
	        to.each(function (){
	        	if ($(this).attr('title') == TOP.TEXT.INVALID_TO_USER) {
	        		i++;
	        	}
			});

	        if (me.chCcInput !== null) {
		        var cc = me.chCcInput.getItems();
		        cc.each(function (){
		        	if ($(this).attr('title') == TOP.TEXT.INVALID_TO_USER) {
		        		i++;
		        	}
				});
	        }
	        
	        if (me.chReviewerInput) {
	        	var reviewers = me.chReviewerInput.getItems();
	        	reviewers.each(function (){
	            	if ($(this).attr('title') == TOP.TEXT.INVALID_TO_USER) {
	            		i++;
	            	}
	    		});
	        }

	        if (i >0) {return TOP.showMessage(TOP.TEXT.TUDU_ACCEPTER_EMAIL_ERROR);}
	        
			if ($('#ch-acceptmode').attr('checked') == true) {
		        var chto = $('#ch-to').val().split("\n");
		        if (chto.length <= 1) {
		        	return TOP.showMessage(TOP.TEXT.TUDU_CLAIM_ACCEPTER_LESS_THAN_ONE);
		        }
			}
	        
			if (null !== me.chUpload && me.chUpload.isUploading()) {
				return TOP.showMessage(TOP.TEXT.SAVE_DIVIDE_WHILE_UPLOADING);
			}
			//子图度私密任务密码验证
			if($('#ch-open_pwd').attr('checked') == true){
	        	if(!$('#ch-password').val()){
	        		$('#ch-password').focus();
	        		return TOP.showMessage(TOP.TEXT.PASSWORD_NOT_EMPTY);
	        	}
	        	if(/\s+/g.test($('#ch-password').val())){
	        		$('#ch-password').focus();
	        		return TOP.showMessage(TOP.TEXT.PASSWORD_YES_SPACE);
	        	}
	        	if(/[^\x01-\xff]+/.test($('#ch-password').val())) {
	        		$('#ch-password').focus();
	        		return TOP.showMessage(TOP.TEXT.PASSWORD_NOT_Byte);
	        	}
	        }
			
			if ($('#ch-classid').attr('disabled') == false) {
				if ($('#ch-classid option:selected').text() != '--' + TOP.TEXT.INHERIT_PARENT + '--') {
					if ($('#bid').val() != $('#ch-bid').val()) {
						if ($('#ch-classid').size() && (!$('#ch-classid').val() || $('#ch-classid').val() == '^add-class')) {
							$('#ch-classid').focus();
				    		return TOP.showMessage(TOP.TEXT.BOARD_MUST_CLASSIFY);
						}
					}
				}
			}
			
			var item = me.chBoardSelect.getSelected(),
			    flowOnly = item && item.flowonly;
            if (flowOnly && !$('#ch-flowid').val()) {
				if ($('#ch-flowid option').size() <= 1) {
                    return TOP.showMessage(TOP.TEXT.BOARD_MUST_FLOWONLY_FLOW_NULL);
                } else {
                    return TOP.showMessage(TOP.TEXT.BOARD_MUST_FLOWONLY);
                }
            }
			
			var prevId = $('#ch-prev').val();
			if (prevId) {
				if (!$('#' + prevId).size()) {
					return TOP.showMessage(TOP.TEXT.PREV_TUDU_NOT_EXOSTS);
				} else if (me.currIndex !== null) {
					if (!me.checkPrev('child-' + me.currIndex, prevId)) {
						return false;
					}
				}
			}
			
	    	me.saveChild(me.currIndex);
	    	me.currIndex = null;
	    });
		
		me.isInitDivide = true;
		
		function _togglePercent() {
			if (!me.chExpand.percent) {
				$('#ch-row-percent').show();
				$('#ch-add-percent').text(TOP.TEXT.DELETE_PERCENT);
			} else {
				$('#ch-row-percent').hide();
				$('#ch-add-percent').text(TOP.TEXT.ADD_PERCENT);
			}
			me.chExpand.percent = !me.chExpand.percent;
		}
		
		function _toggleReviewer() {
			if (!me.chExpand.review) {
				$('#ch-reviewer, ch-reviewer-text').removeAttr('disabled');
				$('#ch-row-reviewer').show();
				$('#ch-add-review').text(TOP.TEXT.DELETE_REVIEW);
			} else {
				$('#ch-reviewer, ch-reviewer-text').attr('disabled', 'disabled');
				$('#ch-row-reviewer').hide();
				$('#ch-add-review').text(TOP.TEXT.ADD_REVIEW);
			}
			me.chExpand.review = !me.chExpand.review;
		}

		function _toggleContent() {
			if (!me.chExpand.content) {
				$('#ch-row-content').show();
				$('#add-content').text(TOP.TEXT.DELETE_CONTENT);
				
				me.chEditorInit();
				me.chEditor.focus();
			} else {
				$('#ch-row-content').hide();
				$('#add-content').text(TOP.TEXT.ADD_CONTENT);
			}
			
			me.chExpand.content = !me.chExpand.content;
		}
		
		function _togglePrev() {
			if (!me.chExpand.prev) {
				$('#ch-row-prev').show();
				$('#ch-add-prev').text(TOP.TEXT.DELETE_PREV);
			} else {
				$('#ch-row-prev').hide();
				$('#ch-add-prev').text(TOP.TEXT.ADD_PREV);
			}
			
			me.chExpand.prev = !me.chExpand.prev;
		}
		
		function _toggleDate() {
			if (!me.chExpand.date) {
				me.chDateInit();
				$('#ch-starttime, #ch-endtime').attr('disabled', false);
				$('#ch-row-date').show();
				$('#ch-add-date').text(TOP.TEXT.DELETE_DATE);
			} else {
				$('#ch-starttime, #ch-endtime').attr('disabled', true);
				$('#ch-row-date').hide();
				$('#ch-add-date').text(TOP.TEXT.ADD_DATE);
			}
			
			me.chExpand.date = !me.chExpand.date;
		}
		
		function _toggleCc() {
			if (!me.chExpand.cc) {
				$('#ch-cc, #ch-cc-text').attr('disabled' ,false);
				$('#ch-row-cc').show();
				$('#ch-add-cc').text(TOP.TEXT.DELETE_CC);
			} else {
				$('#ch-cc, #ch-cc-text').attr('disabled' ,true);
				$('#ch-row-cc').hide();
				$('#ch-add-cc').text(TOP.TEXT.ADD_CC);
			}
			
			me.chExpand.cc = !me.chExpand.cc;
		}
		
		function _toggleBcc() {
			if (!me.chExpand.cc) {
				$('#ch-bcc, #ch-bcc-text').attr('disabled' ,false);
				$('#ch-row-bcc').show();
				$('#ch-add-bcc').text(TOP.TEXT.DELETE_BCC);
			} else {
				$('#ch-bcc, #ch-bcc-text').attr('disabled' ,true);
				$('#ch-row-bcc').hide();
				$('#ch-add-bcc').text(TOP.TEXT.ADD_BCC);
			}
			
			me.chExpand.cc = !me.chExpand.cc;
		}
	},
	
	checkPrev: function(id, prevId) {
		var prev = $('#' + prevId);
		
		// 指定了自己
		if (prevId == id) {
			TOP.showMessage(TOP.TEXT.PREV_IS_SELF);
			return false;
		}
		
		var pp, count = 50;
		while (prev.size()) {
			pp = prev.find('input[name^="prev-"]').val();
			if (!pp) {
				break;
			}
			
			if (pp == id) {
				TOP.showMessage(TOP.TEXT.CYCLE_PREV_TUDU_ID);
				return false;
			}
			
			prev = $('#' + pp);
			
			count--;
			if (count <= 0) {
				break;
			}
		}
		
		return true;
	},
	
	chEditorInit: function() {
		var me = this;
		if (me.chEditor === null) {
			me.chEditor = new TOP.Editor(document.getElementById('ch-content'), {
	            resizeType : 1,
				width: '100%',
	            minHeight: 200,
	            themeType : 'default',
				css: Modify.editorCss,
	            scope: window
	        }, jQuery);
			me.chEditor.focus();
		}
	},
	
	chDateInit: function() {
		var me = this;
		if (!me.chDatepickers) {
			me.chDatepickers = {}
			if ($('#ch-starttime').size()) {
				me.chDatepickers.chStarttime = $('#ch-starttime').datepick({
			        showOtherMonths: true,
			        selectOtherMonths: true,
			        showAnim: 'slideDown',
			        showSpeed: 'fast',
			        firstDay: 0,
			        onSelect: function(dates){
						$('#ch-endtime').datepick('option', {minDate: dates});
					}
			    });
			}
			
			if ($('#ch-endtime').size()) {
				me.chDatepickers.chEndtime = $('#ch-endtime').datepick({
			        minDate: new Date(),
			        showOtherMonths: true,
			        selectOtherMonths: true,
			        showAnim: 'slideDown',
			        showSpeed: 'fast',
			        firstDay: 0,
			        onSelect: function(dates){$('#ch-starttime').datepick('option', {maxDate: dates});}
			    });
			}
		}
	},
	
	/**
	 * 保存编辑表单内容
	 */
	saveChild: function(chidx) {
		if (null == chidx) {
			chidx = this.appendChild();
		}
		var id = 'child-' + chidx;
		
		var o = $('#' + id),
			editForm = $('#child-edit-form'),
			me = this;
		
		o.find('input[name^="to-"]').remove();
		o.find('input[name^="toidx-"]').remove();
		
		// 更新附件内字段
		o.find('input[name="attach-'+chidx+'[]"], input[name="nd-attach-'+chidx+'[]"], input[name="file-'+chidx+'[]"]').remove();
		editForm.find('#ch-attach-list div.filecell').each(function(){
			var obj = $(this),
				fileid = this.id,
				filename = obj.find('span.filename').text(),
				filesize = obj.find('span.filesize').text().replace(/[\(\)]/g, '').replace('KB', '000').replace('bytes', ''),
				fid      = obj.find('input[name="attach[]"]').val(),
				ndfileid = obj.find('input[name="nd-attach[]"]').val();
			
			o.append('<input type="hidden" _fileid="'+fid+'" _filename="'+filename+'" _filesize="'+filesize+'" _ndfileid="'+ndfileid+'" name="attach-' + chidx +'[]" value="'+fid+'" />');
			if (ndfileid) {
				o.append('<input type="hidden" name="nd-attach-' + chidx +'[]" value="'+fid+'" />');
			}
		});
		
		o.find('input[name="ch-to-' + chidx + '"]').val($('#ch-to').val());
		o.find('input[name="ch-to-text-' + chidx + '"]').val($('#ch-to-text').val());
		o.find('input[name="ismodified-' + chidx + '"]').val(1);
		
		o.find('input:hidden').each(function(){
			if (this.name == 'attach-' + chidx + '[]' || this.name == 'file-' + chidx + '[]' || this.name == 'ch-to-' + chidx || this.name == 'ch-to-text-' + chidx) {
				return ;
			}
			
			var id = 'ch-' + this.name.replace('-' + chidx, '');
			if ($('#' + id).size()) {
				if ($('#' + id).attr('type') == 'checkbox') {
					this.value = $('#' + id).attr('checked') ? $('#' + id).val() : '';
				} else {
					this.value = $('#' + id).val();
				}
			}
		});
		
		var t = $('#ch-to').val(), arr, to = [];
		t = t.split("\n");
		for (var i = 0, c = t.length; i < c; i++) {
			arr = t[i].split(' ');
			to.push({email: arr[0], name: arr[1]});
		}
		
		var num = [], percent = [], list = $('#ch-percent-list');
		if (list.find('p.percent-item').size()) {
			list.find('p.percent-item').each(function(){
				n = $(this).find('input[name="ch-toidx[]"]').val();
				num.push(n);
			});
			for (var i = 0, c = num.length; i < c; i++) {
				list.find('p.percent-item').each(function(){
					if (!$(this).find('input[name="ch-to-'+num[i]+'"]').size()) {
						return ;
					}
					
					key = $(this).find('input[name="ch-to-'+num[i]+'"]').val();
					per = $(this).find('input[name="ch-to-percent-'+num[i]+'"]').val();
					percent[key] = per;
				});
			}
		}
		
		var tuduPercent;
		for(var i = 0, c = to.length; i < c; i++) {
			if (!to[i].email || !to[i].name) {
				continue;
			}
			
			tuduPercent = percent[to[i].email + ' ' + to[i].name] ? percent[to[i].email + ' ' + to[i].name] : '';
			
			o.find('div.child_info')
		     .prepend('<input type="hidden" name="toidx-'+chidx+'[]" value="'+i+'" />')
			 .prepend('<input type="hidden" name="to-'+i+'-'+chidx+'" value="'+ to[i].email + ' ' + to[i].name +'" />')
			 .prepend('<input type="hidden" name="to-percent-'+i+'-'+chidx+'" value="'+tuduPercent+'" />');
		}
		
		if (o.find('input[name="classid-' + chidx + '"]').val()) {
			o.find('input[name="classid-' + chidx + '"]').val($('#ch-classid option:selected').val());
			if (o.find('input[name="classname-' + chidx + '"]').size()) {
				o.find('input[name="classname-' + chidx + '"]').val($('#ch-classid option:selected').text());
			} else  {
				o.find('div.child_info').prepend('<input type="hidden" name="classname-'+chidx+'" value="'+$('#ch-classid option:selected').text()+'" />');
			}
		}
		
		// 处理HTML
		if (me.chEditor !== null) {
		    var src = me.chEditor.getSource();
		    var reg = /<img([^>]+)src="([^"]+)"([^>]+)_aid="([^"]+)"([^>]+)\/>/ig;
		    while ((result = reg.exec(src)) != null) {
		    	o.append('<input type="hidden" name="file-'+chidx+'[]" value="'+result[4]+'" />');
		    }
		    
		    src = src.replace(reg, '<img$1src="AID:$4"$3_aid="$4"$5/>').replace(/\s+id="[^"]+"/g, '');
		    
		    o.find('input[name="content-'+chidx+'"]').val(src);
		}
		
		me.flowhtml[chidx] = $('#ch-flow-steps .flowhtml').clone();
		
		me.refreshChildInfo(chidx);
		me.clearDivide();
		
		if ($('#urgent').attr('checked') == true) {
			$('#ch-priority').attr('checked', true);
		}
		if ($('#secrecy').attr('checked') == true) {
			$('#ch-privacy').attr('checked', true);
		}
		if ($('#needconfirm').attr('checked') == true) {
			$('#ch-needconfirm').attr('checked', true);
		}
		if ($('#notifyall').attr('checked') == true) {
			$('#ch-notifyall').attr('checked', true);
		}
		if ($('#isauth').attr('checked') == true) {
			$('#ch-isauth').attr('checked', true);
		}
		
		me.divide = 'create';
		me.isFromTudu = false;
		me.childIsFlow = false
		$('#ch-add-percent').addClass('gray');
		me.chExpand.percent = false;
		$('#ch-row-percent').hide();
		$('#ch-add-percent').text(TOP.TEXT.ADD_PERCENT);
	},
	
	/**
	 * 添加子图度
	 */
	appendChild: function(params) {
		var me = this,
			list = $('#children-list'),
			item = $('<div class="tudu_children_item"></div>'),
			table = $('#child-tpl').clone(),
			idx   = me.autoIndex++;
		
		item.attr('id', 'child-' + idx);
		table.removeAttr('id');
		
		table.find('input').each(function(){
			if (this.name == 'chidx[]') {
				this.value = idx;
				return ;
			}
			
			if (params && typeof(params[this.name]) !== 'undefined') {
				this.value = params[this.name];
			}
			
			this.name = this.name + '-' + idx;
		});
		
		table.find('a[name="delete"]').bind('click', function(){
			if (!confirm(TOP.TEXT.CONFIRM_DELETE_DIVIDE)) {
				return false;
			}
			me.removeChild(idx);
		});
		
		if (params && params['isdone'] && typeof(params['isdone']) !== 'undefined') {
			table.addClass('gray');
			table.find('a[name="edit"], a[name="subject"]').addClass('gray');
		} else {
			table.find('a[name="edit"], a[name="subject"]').bind('click', function(){
				me.clearDivide();
				me.editChild(idx);
				/*if($('#ch-password').val()) {
					$('#ch-block-private').show();
					$('#ch-password').attr('disabled', false);
				}
				
				if (params && !params['isdraft'] && typeof(params['isdraft']) !== 'undefined') {
					me.divide = 'update';
					$('#ch-add-percent').removeClass('gray');
					me.chExpand.percent = true;
					$('#ch-row-percent').show();
					$('#ch-add-percent').text(TOP.TEXT.DELETE_PERCENT);
				} else {
					me.divide = 'create';
					$('#ch-add-percent').addClass('gray');
					me.chExpand.percent = false;
					$('#ch-row-percent').hide();
					$('#ch-add-percent').text(TOP.TEXT.ADD_PERCENT);
				}*/
			});
		}
		
		item
		.append(table.show())
		.append('<div class="child_edit_area"></div>')
		.appendTo(list);
		
		if (typeof(params) !== 'undefined' && typeof(params['isdraft']) !== 'undefined' && params['isdraft'] == 'false') {
			table.find('a[name="delete"]').hide();
			table.find('span[name="gray-delete"]').show();
		} else {
			table.find('span[name="gray-delete"]').hide();
		}
		
		/*if (typeof(params['isdraft']) !== 'undefined' && params['isdraft']) {
			
		}*/
		
		me.refreshChildInfo(idx);
		
		me.checkChild();
		
		return idx;
	},
	
	/**
	 * 移除子图度
	 */
	removeChild: function(chidx) {
		var o = $('#child-' + chidx),
			me = this,
			ftid = o.find('input[name="ftid-'+chidx+'"]').val();

		if (ftid) {
			$.ajax({
				type: 'GET',
				dataType: 'json',
				url: '/tudu-mgr/discard?tid[]=' + ftid,
				success: function(ret) {
				   TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
				   if (ret.success) {
					    o.remove();
					    $('#ch-prev option[value="child-'+chidx+'"]').remove();
					    me.checkChild();
				   }
				},
				error: function(res) {
				    TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
				}
			});
		} else {
			o.remove();
			$('#ch-prev option[value="child-'+chidx+'"]').remove();
			me.checkChild();
		}
	},
	
	isLoaded: [],
	childStatus: [],
	modifyPercent: [],
	
	/**
	 * 修改子图度
	 */
	editChild: function (chidx) {
		var me = this;
		if (chidx !== null) {
			me.clearDivide();
			if (typeof me.isLoaded[chidx] != 'undefined' && me.isLoaded[chidx] === true) {
				return _edit(chidx);
			}
			var o = $('#child-' + chidx);
			var ftid = o.find('input[name="ftid-'+chidx+'"]').val();
			if (ftid) {
				TOP.showMessage(TOP.TEXT.LOADING_SUB_TUDU, 5, 'success');
				$.ajax({
					type: 'GET',
					dataType: 'json',
					url: '/tudu/tudu?tid=' + ftid,
					success: function(ret){
						if (ret.data) {
							for (var k in ret.data) {
								if (o.find('input[name="'+k+'-'+chidx+'"]').size()) {
									if (k == 'acceptmode') continue ;
									o.find('input[name="'+k+'-'+chidx+'"]').val(ret.data[k]);
								}
								if (ret.data['password']) {
									o.find('input[name="open_pwd-'+chidx+'"]').val('1');
								}
								if (k == 'attachments') {
									var attach = ret.data[k];
									for (var i = 0, c = attach.length; i < c; i++) {
										o.append('<input type="hidden" name="attach-'+chidx+'[]" value="'+attach[i].fileid+'" _fileid="'+attach[i].fileid+'" _filename="'+attach[i].filename+'" _filesize="'+attach[i].size+'" />');
									}
								}
								if (k == 'accepters') {
									if (ret.data['accepters'] && ret.data['ismodifypercent']) {
										var accepters = ret.data['accepters'];
										$('#ch-percent-list').empty();
										for (var i = 0, c = accepters.length; i < c; i++) {
											o.find('div.child_info')
										     .prepend('<input type="hidden" name="toidx-'+chidx+'[]" value="'+i+'" />')
											 .prepend('<input type="hidden" name="to-'+i+'-'+chidx+'" value="'+ accepters[i].email + ' ' + accepters[i].truename +'" />')
											 .prepend('<input type="hidden" name="to-percent-'+i+'-'+chidx+'" value="'+accepters[i].percent+'" />');
										}
									}
								}
								
								if (k == 'nodetype') {
									if (ret.data['nodetype'] !== 'leaf') {
										$('#ch-reviewer, ch-reviewer-text').attr('disabled', 'disabled');
										$('#ch-row-reviewer').hide();
										$('#ch-add-review').addClass('disabled').text(TOP.TEXT.ADD_REVIEW);
										me.chExpand.review = false;
									} else {
										$('#ch-add-review').removeClass('disabled');
									}
								}

								if (k == 'prevtuduid') {
									o.find('input[name="prev-'+chidx+'"]').val(ret.data[k]);
								}
								/*if (k == 'isdraft' && !ret.data[k]) {
									$('#ch-starttime').attr('disabled', true);
								}*/
								if (k == 'flowhtml') {
									me.flowhtml[chidx] = ret.data['flowhtml'];
								}
							}
							if ((ret.data['acceptmode'] && ret.data['isdraft']) || (ret.data['acceptmode'] && !ret.data['accepttime'])) {
								o.find('input[name="acceptmode-'+chidx+'"]').val(ret.data['acceptmode']);
							}
							if (ret.data['status'] == 3) {
								me.childStatus[chidx] = true; 
							}
							me.modifyPercent[chidx] = ret.data['ismodifypercent'];
						}
						_edit(chidx);
						me.isLoaded[chidx] = true;
					},
					error: function(res){}
				});
			} else {
				_edit(chidx);
			}
		} else {
			_edit(chidx);
		}
		
		function _edit(idx) {
			me.setChildParams(idx);
			
			if (null !== idx && undefined !== idx) {
				$('button[name="save-child"]').text(TOP.TEXT.SAVE_CHANGE);
			}
		}
	},
	
	checkChild: function() {
		if (!$('#add-divide').size()) {
			return ;
		}
		
		if ($('#children-list div.tudu_children_item').size()) {
			$('#add-divide').addClass('disabled');
			$('label[for="cycle"], label[for="acceptmode"]').addClass('gray');
			$('#cycle, #acceptmode').attr('disabled', true);
		} else {
			$('#add-divide').removeClass('disabled');
			$('label[for="cycle"], label[for="acceptmode"]').removeClass('gray');
			$('#cycle, #acceptmode').attr('disabled', false);
		}
	},
	
	/**
	 * 分工是否有工作流
	 */
	childIsFlow: false,
	
	/**
	 * 是否有来源图度，非草稿状态
	 */
	isFromTudu: false,
	
	/**
	 * 记录分工 工作流程
	 */
	flowhtml: [],
	
	/**
	 * 设置参数
	 */
	setChildParams: function(chidx) {
		var me = this,
			o = $('#child-edit-form');

		me.currIndex = chidx;
		if (null !== chidx) {
			var target = $('#child-' + chidx);
			$('#ch-acceptmode').attr('checked', false);
			if (me.childStatus.length && me.childStatus[chidx]) {
				$('#ch-acceptmode').bind('click', function(){
					if ($(this).attr('checked') == false) {
						return ;
					}
					var childId = target.find('input[name="ftid-'+chidx+'"]').val();
					me.showClaimUser('ch-acceptmode', true, childId);
				});
			} else {
				$('#ch-acceptmode').unbind('click');
			}
			
			if (target.find('input[name="ftid-' + chidx + '"]').val()) {
				me.isFromTudu = true;
			}
			
			if (target.find('input[name="flowid-'+chidx+'"]').val()) {
				me.childIsFlow = true;
				me.divide = 'update';
				me.loadFlows(target.find('input[name="bid-'+chidx+'"]').val(), '#ch-tudu-flow', '#ch-flowid', target.find('input[name="flowid-'+chidx+'"]').val());
				$('#ch-tudu-flow').show();
			} else {
				me.childIsFlow = false;
				$('#ch-flowid').attr('disabled', false);
				$('#ch-tudu-flow').val('');
				$('#ch-tudu-flow').hide();
			}
			
			if (target.find('input[name="classid-'+chidx+'"]').val()) {
				me.divide = 'update';
				me.loadClasses(target.find('input[name="bid-'+chidx+'"]').val(), '#ch-classid', target.find('input[name="classid-'+chidx+'"]').val());
				$('#ch-class-td').show();
			} else {
				$('#ch-classid').val('');
				$('#ch-class-td').hide();
			}
			
			if (!me.childIsFlow) {
				if (target.find('input[name^="toidx-"]').size()) {
					var num = parseInt(target.find('input[name^="toidx-"]').val()) + 1;
					for (var i = 0, c = num; i < c; i++) {
						if (target.find('input[name="to-' + i + '-' + chidx + '"]').size()) {
							var percent = target.find('input[name="to-percent-' + i + '-' + chidx + '"]').val();
							var to = target.find('input[name="to-' + i + '-' + chidx + '"]').val();
							arr = to.split(' ');
							me.appendPercentList({
								email: arr[0],
								truename: arr[1],
								percent: percent ? parseInt(percent) : 0
							}, $('#ch-percent-list'), 'ch-');
						}
					}
				}
			}
			
			target.find('input:hidden').each(function(){
				var id = this.name.replace('-' + chidx, '');
				if (id == 'priority' || id == 'privacy' || id == 'notifyall' || id == 'open_pwd' || id == 'isauth' || id == 'needconfirm' || id == 'acceptmode') {
					if (this.value == 1 || this.value == 'true') {
						$('#ch-' + id).attr('checked', true);
					}
			    } else if (id == 'ch-to' || id == 'ch-to-text') {
			    	$('#' + id).val(this.value);
			    } else if (id == 'cc') {
			    	if (this.value) {
				    	$('#ch-row-cc').show();
				    	me.chExpand.cc = true;
				    	$('#ch-add-cc').text(TOP.TEXT.DELETE_CC);
				    	$('#ch-cc').val(this.value);
			    	}
			    } else if (id == 'bcc') {
			    	if (this.value) {
				    	$('#ch-row-bcc').show();
				    	me.chExpand.bcc = true;
				    	$('#ch-add-bcc').text(TOP.TEXT.DELETE_BCC);
				    	$('#ch-bcc').val(this.value);
			    	}
			    } else if (id == 'reviewer' && !me.childIsFlow) {
			    	if (this.value) {
				    	$('#ch-row-reviewer').show();
				    	me.chExpand.review = true;
				    	$('#ch-add-review').text(TOP.TEXT.DELETE_REVIEW);
				    	$('#ch-reviewer').val(this.value);
			    	} else {
			    		$('#ch-row-reviewer').hide();
			    		$('#ch-reviewer').val('');
			    	}
			    } else if (id == 'content') {
				    if (this.value) {
				    	$('#ch-row-content').show();
				    	me.chEditorInit();
						me.chEditor.focus();
				    	me.chExpand.content = true;
				    	$('#add-content').text(TOP.TEXT.DELETE_CONTENT);
				    	$('#ch-content').val(this.value);
						me.chEditor.setSource(this.value);
			    	}
			    } else if (id == 'starttime' || id == 'endtime') {
			    	if (this.value) {
				    	$('#ch-row-date').show();
				    	me.chDateInit();
				    	me.chExpand.date = true;
				    	$('#ch-add-date').text(TOP.TEXT.DELETE_DATE);
				    	$('#ch-' + id).val(this.value);
			    	}
				} else if (id == 'prev') {
					if (this.value) {
						$('#ch-row-prev').show();
						$('#ch-add-prev').text(TOP.TEXT.DELETE_PREV);
						
						if (this.value.indexOf('child-') === 0) {
							var index = this.value;
						} else {
							var ftid = $('#children-list input[name^="ftid-"][value="'+this.value+'"]');
							if (ftid.size()) {
								ftid = ftid[0].name;
								var index = ftid.replace('ftid-', 'child-');
							}
						}
						$('#ch-prev').val(index);
						me.chExpand.prev = true;
						
					} else {
						$('#ch-row-prev').hide();
						$('#ch-add-prev').text(TOP.TEXT.ADD_PREV);
					}
				} else if ($('#ch-' + id).size()) {
					$('#ch-' + id).val(this.value);
					if (id == 'bid') {
						me.chBoardSelect.select(this.value);
						if (target.find('input[name="ftid-' + chidx + '"]').val()) {
							me.chBoardSelect.disabled();
						}
					}
				}
			});
			
			if ($('#ch-privacy').attr('checked')) {
				$('#ch-block-private').show();
				if ($('#open_pwd').attr('checked')) {
					$('#ch-open_pwd').attr('checked', true);
					$('#ch-password').attr('disabled', false);
				}
			}
			// 附件
		    if (me.chUpload != null) {
		    	var handler = me.chUpload.handler;
		    	handler.removeAll();
		    	$('#ch-attach-list').hide();
		    	o.find('input[name="file[]"]').remove();
		    	target.find('input[name="attach-'+chidx+'[]"]').each(function(){
		    		var att = $(this);
		    		var item = handler.add(att.attr('_fileid'), att.attr('_filename'), att.attr('_filesize'));
		    		
		    		if (undefined !== att.attr('_ndfileid') && att.attr('_ndfileid') !== 'undefined') {
		    			$('#' + att.attr('_fileid')).append('<input type="hidden" name="nd-attach[]" value="'+att.attr('_fileid')+'" />');
		    		}
		    		item.success(this.value);
		    	});
		    	
		    	target.find('input[name="file-'+chidx+'[]"]').each(function(){
		    		o.append('<input type="hidden" name="file[]" value="'+this.value+'" />')
		    	});
		    }
		    
		    // 内容
		    var tempdiv = $('<div>').append(target.find('input[name="content-'+chidx+'"]').val());
			tempdiv.find('img').each(function(){
				var c = $(this);
				if (c.attr('_aid')) {
					c.attr('src', '/attachment/img?fid=' + c.attr('_aid'));
				}
			});
			$('#ch-content').val(tempdiv.html());
			
			var cc = $('#ch-cc').val().split("\n");
			var bcc = $('#ch-bcc').val().split("\n");
			
			if (me.chCcInput !== null) {
				me.chCcInput.clear();
			}
			if (me.chBccInput !== null) {
				me.chBccInput.clear();
			}
			
			me.showCc(cc);
			me.showBcc(bcc);
			
			if (!me.childIsFlow) {
				var to = $('#ch-to').val().split("\n");
				var reviewer = $('#ch-reviewer').val().split("\n");
				
				me.chToInput.clear();
				
				if (me.chReviewerInput !== null) {
					me.chReviewerInput.clear();
				}
				
				me.showTo(to, me.chToInput);
				
				var list = $('#ch-percent-list');
				list.empty();
				target.find(':hidden[name="toidx-'+chidx+'[]"]').each(function(){
					var i = this.value;
					var info = target.find(':hidden[name="to-'+i+'-'+chidx+'"]').val(),
					    percent = target.find(':hidden[name="to-percent-'+i+'-'+chidx+'"]').val();
					
					var arr = info.split(' ', 2);
					me.appendPercentList({email: arr[0], truename: arr[1], percent: percent ? parseInt(percent) : 0}, $('#ch-percent-list'), 'ch-');
				});
				
				if (reviewer.length) {
					me.showReviewer(reviewer);
				}
			}
			
			if($('#ch-password').val()) {
				$('#ch-block-private').show();
				$('#ch-password').attr('disabled', false);
			}
			
			if (!me.childIsFlow) {
				var isdraft = target.find('input[name="isdraft-' + chidx + '"]').val();
				if (isdraft == 'false') {
					me.divide = 'update';
					if ($('#ch-percent-list p.percent-item').size() > 0) {
						$('#ch-add-percent').removeClass('gray');
						me.chExpand.percent = true;
						$('#ch-row-percent').show();
						$('#ch-add-percent').text(TOP.TEXT.DELETE_PERCENT);
					}
				} else {
					me.divide = 'create';
					$('#ch-add-percent').addClass('gray');
					me.chExpand.percent = false;
					$('#ch-row-percent').hide();
					$('#ch-add-percent').text(TOP.TEXT.ADD_PERCENT);
				}
			}
			
			if (me.isFromTudu && !me.childIsFlow) {
				$('#ch-add-percent').removeClass('gray');
			}
			
			if (me.childIsFlow) {
				me.chToInput.clear();
				if (me.chReviewerInput !== null) {
					me.chReviewerInput.clear();
				}
				$('#ch-row-to').hide();
				$('#ch-add-review').addClass('disabled');
				
				$('#ch-add-percent').addClass('gray');
				me.chExpand.percent = false;
				$('#ch-row-percent').hide();
				$('#ch-add-percent').text(TOP.TEXT.ADD_PERCENT);
				
				if (me.flowhtml[chidx] !== null) {
					$('#ch-flow-steps').append(me.flowhtml[chidx]);
					$('#ch-flow-steps').show();
				} else {
					$('#ch-flow-steps').hide();
				}
				
				$('#ch-isauth').attr('disabled', true);
				$('label[for="ch-isauth"]').addClass('gray');
				
				$('#ch-acceptmode').attr('disabled', true);
				$('label[for="ch-acceptmode"]').addClass('gray');
			}
			if (!me.modifyPercent[chidx]) {
				me.chExpand.percent = false;
				$('#ch-add-percent').addClass('gray');
				$('#ch-row-percent').hide();
				$('#ch-add-percent').text(TOP.TEXT.ADD_PERCENT);
			}
		}
		$('#ch-subject').focus();
	},
	
	/**
	 * 清空分工数据，及表单
	 */
	clearDivide: function() {
		var me = this,
			o = $('#child-edit-form');
		//$('#ch-class-td').hide();
		$('#ch-block-private').hide();
		$('#ch-open_pwd').attr('checked', false);
		$('#ch-bid').attr('disabled', false);
		$('#ch-starttime').attr('disabled', false);
		$('#ch-acceptmode').attr('disabled', false);
		$('label[for="ch-acceptmode"]').removeClass('gray');
		me.chToInput.clear();
		me.chReviewerInput.clear();
		if (me.chCcInput !== null) {
			me.chCcInput.clear();
		}
		if (me.chBccInput !== null) {
			me.chBccInput.clear();
		}
		me.chBoardSelect.enabled();
		// 父类的主题分类与工作流显示
		var board = me.boardSelect.getSelected(),
            bid   = board ? board.boardid : null,
            flowOnly = board && board.flowonly;
        me.loadClasses(bid, '#ch-classid', null, true);
        me.loadFlows(bid, '#ch-tudu-flow', '#ch-flowid', null, flowOnly);
		
		o.find('input[name="ch-password"]').attr('disabled', true);
		o.find('input:not(:checkbox), textarea, select').val('');
		o.find(':checkbox').attr('checked', false);
		o.find('#ch-attach-list div.filecell').remove();
		o.find('#ch-attach-list').hide();
		o.find('#ch-percent-list p.percent-item').each(function(){
			$(this).remove();
		});
		
		$('#ch-bid-input').val($('#ch-bid-input').attr('title'));
		$('#ch-flowid').find('option:not(:eq(0))').remove();
		$('#ch-tudu-flow').hide();
		$('#ch-flow-steps').empty();
		$('#ch-flow-steps').hide();
		$('#ch-row-to').show();
		if ($('#ch-add-review').hasClass('disabled')) {
			$('#ch-add-review').removeClass('disabled');
		}
		$('#ch-isauth, #ch-acceptmode').attr('disabled', false);
		$('label[for="ch-isauth"], label[for="ch-acceptmode"]').removeClass('gray');
		
		$('button[name="save-child"]').text(TOP.TEXT.ADD_DIVIDE);

		if (me.chEditor !== null) {
			me.chEditor.setSource('');
		}

		me.isFromTudu = false;
		me.childIsFlow = false;
		me.divide = 'create';
	},
	
	/**
	 * 添加分工
	 */
	addDivide: function(isDivide) {
		var me = this,
			params = {},
			userEmail = $('#user-msg').val(),
			userName = $('#user-msg').attr('_name');
		
		$('#add-percent').addClass('gray').unbind('click');
		$('#add-percent').text(TOP.TEXT.ADD_PERCENT);
		me.expand.percent = false;
		if ($('#percent-list').find('p.percent-item').size() > 0) {
			//$('#percent-list').find('p.percent-item').each(function (){$(this).remove();});
			$('#percent-list').find('input').each(function (){$(this).attr('disable', true);});
			$('#row-percent').hide();
		}
		
		if (me.isDivide) {
			if (!me.isModify) {
				$('#select-to').addClass('gray').unbind('click');
			}
		} else {
			var items = me.toInput.getItems(),
				emails = [],
				to = [],
				append = true;
			items.each(function (){
				var item = $(this);
				if (!item.hasClass('mail_item_separator')) {
					emails.push(item.attr('_id'));
					to.push(item.attr('_id') + ' ' + item.attr('name'));
				}
			});
			if (!emails.length) {
				_appendUser(userName, userEmail);
			} else {
				Modify.toInput.clear();
				for (var i=0; i < emails.length; i++) {
					if (emails[i] == userEmail) {
						append = false;
					}
				}
				if (append) {
					to.push(userEmail + ' ' + userName);
				}
                me.showTo(to, Modify.toInput);
			}
		}
		
		if ($('#row-percent:visible').size()) {
			$('#row-percent').hide();
			me.expand.percent = false;
		}
		
		$('label[for="cycle"]').addClass('gray');
		$('label[for="acceptmode"]').addClass('gray');
		$('#cycle').attr('checked', false).attr('disabled', true);
		$('#acceptmode').attr('checked', false).attr('disabled', true);
		if ($('#bolck-cycle:visible').size()) {
			$('#block-cycle').hide();
			me.expand.cycle = false;
		}
		if (!$('#bolck-cycle:visible').size() && !$('#block-privacy:visible').size()) {
			$('#extend-box').hide();
		}
		
		function _appendUser(userName, userEmail, separator) {
			params.title = userName + (userEmail ? '$lt;' + userEmail + '&gt;' : '');
			params._id = userEmail ? userEmail : '';
			if (separator) {
				//p.separator = p._id = p.name = p.title = '+';
				params.name = params._id = params.title = params.separator = '+';
			}
			Modify.toInput.addItem(userName, params);
		}
	},
	
	// 刷新分工列表项目显示内容
	refreshChildInfo: function(chidx) {
		var obj = $('#child-' + chidx);
		
		obj.find('a[name="subject"]').text(obj.find('input[name="subject-'+chidx+'"]').val());
		if (obj.find('input[name="ch-to-text-'+chidx+'"]').val()) {
			var html = '',
			    to = obj.find('input[name="ch-to-text-'+chidx+'"]').val();
			to = to.split(',');
			for (var i = 0; i < to.length; i++) {
				if (to[i].indexOf('>') == 0) {
					html += '<span class="icon icon_flow_arrow"></span>';
					continue;
				}
				if (to[i].indexOf('+') == 0) {
					html += ';';
					continue;
				}
				html += '<span>'+to[i]+'</span>';
			}
			obj.find('span[name="to"]').html(html);
		} else {
			obj.find('span[name="to"]').text(TOP.TEXT.INDERTERMINATE);
		}
		
		var endtime = obj.find('input[name="endtime-'+chidx+'"]').val();
		if (!endtime) {
			endtime = TOP.TEXT.INHERIT_PARENT;
		}
		
		var subject = obj.find('input[name="subject-'+chidx+'"]').val(),
			ftid    = obj.find('input[name="ftid-' + chidx + '"]').val(),
			prev    = $('#ch-prev');
		
		var val = ftid ? ftid : 'ch-' + chidx;
		if (prev.find('option[value="child-'+chidx+'"]').size()) {
			prev.find('option[value="child-'+chidx+'"]').text(subject);
		} else {
			prev.append('<option value="child-'+chidx+'">'+subject+'</option>');
		}
		
		obj.find('td[name="endtime"]').text(endtime);
	},
	
	/**
	 * 处理执行人
	 */
	showTo: function(to, input) {
		var _id;
		if (to.length) {
			for (var i = 0, c = to.length; i < c; i++) {
				var a = to[i].split(' ');
				
				if (!a[0] && !a[1]) {
					continue;
				}
				
				if (a.length < 2) {
                    if (a[0].indexOf('>') == 0 || a[0].indexOf('+') == 0) {
                        input.addItem(a.join(' '), {separator: a[0]});
                    }
                    continue ;
                }
				
				_id = a[0];
				if (a[0]) {
					_id = _id.replace(/^(#|%)([.+]#)?/, '');
				}
				
				input.addItem(a[1], {name: a[1], _id: _id, title: a[0]});
			}
		}
	},
	
	/**
	 * 处理执行人
	 */
	showReviewer: function(to) {
		var _id,
			_o = this;
		if (to.length) {
			for (var i = 0, c = to.length; i < c; i++) {
				var a = to[i].split(' ');

				if (!a[0] && !a[1]) {
                    continue;
                }

				if (a.length < 2) {
                    if (a[0].indexOf('>') == 0 || a[0].indexOf('+') == 0) {
                        _o.chReviewerInput.addItem(a.join(' '), {separator: a[0]});
                    }
                    continue ;
                }

				_id = a[0];
				if (a[0]) {
					_id = _id.replace(/^(#|%)([.+]#)?/, '');
				}
				
				_o.chReviewerInput.addItem(a[1], {name: a[1], _id: _id, title: a[0]});
			}
		}
	},
	
	/**
	 * 处理抄送人
	 */
	showCc: function(cc) {
		var _id,
			_o = this;
		if (cc.length) {
			for (var i = 0, c = cc.length; i < c; i++) {
				var a = cc[i].split(' ');
				
				if (!a[0] && !a[1]) {
					continue;
				}
				
				_id = a[0];
				if (a[0]) {
					_id = _id.replace(/^(#|%)([.+]#)?/, '');
				}
				
				if (!_id || TOP.isEmail(_id)) {
					_o.chCcInput.addItem(a[1], {name: a[1], _id: _id, title: _id});
				} else {
					_o.chCcInput.addItem(a[1], {name: a[1], _id: _id, title: TOP.TEXT.GROUP + ':' + a[1]});
				}
			}
		}
	},
	
	/**
	 * 密送
	 */
	showBcc: function(bcc) {
		var _id,
		_o = this;
	if (bcc.length) {
		for (var i = 0, c = bcc.length; i < c; i++) {
			var a = bcc[i].split(' ');
			
			if (!a[0] && !a[1]) {
				continue;
			}
			
			_id = a[0];
			if (a[0]) {
				_id = _id.replace(/^(#|%)([.+]#)?/, '');
			}
			
			if (!_id || TOP.isEmail(_id)) {
				_o.chBccInput.addItem(a[1], {name: a[1], _id: _id, title: _id});
			} else {
				_o.chBccInput.addItem(a[1], {name: a[1], _id: _id, title: TOP.TEXT.GROUP + ':' + a[1]});
			}
		}
	}
	},
	
	/**
	 * 更新列表数据
	 */
	updateListData: function(tuduId) {
		var me = this,
			o = $('#child-edit-form'),
			table = $('#child-'+tuduId),
			strTo = new Array();
		
		table.find('span[name="subject"]').text(o.find('input[name="ch-subject"]').val());
		
		var to = o.find('input[name="ch-to"]').val().split("\n");
		if (to.length) {
			for (var i = 0, c = to.length; i < c; i++) {
				var a = to[i].split(' ');
				if (!a[0] && !a[1]) {
					continue;
				}
				if (a[1]) {
					strTo.push(a[1]);
				}
			}
		}
		
		if (strTo.length) {
			table.find('span[name="to"]').text('');
			for (var i = 0, c = strTo.length; i < c; i++) {
				table.find('span[name="to"]').append(strTo[i] + ',');
			}
		}
		
		var endtime = o.find('input[name="ch-endtime"]').val();
		if (!endtime) {
			endtime = TOP.TEXT.INHERIT_PARENT;
		}
		
		var subject = o.find('input[name="ch-subject"]').val(),
			prev    = $('#ch-prev');
	
		if (prev.find('option[value="'+tuduId+'"]').size()) {
			opt.val(ftid).text(subject);
		}
		
		table.find('td[name="endtime"]').text(endtime);
	},
	
	/**
	 * 加载工作流
	 */
	loadFlows: function(bid, target, select, flowid, flowOnly) {
		if (!bid) {
			return _fillSelect([], target, select);
		}
		
		if (typeof flowOnly == 'undefined') {
			flowOnly = false;
		}
		
		var me = this;
		if (typeof(this.flows[bid]) == 'undefined') {
			$.ajax({
				type: 'GET',
				dataType: 'json',
				url: '/flow/flows?bid=' + encodeURIComponent(bid),
				success: function(ret) {
					if (ret.success) {
						me.flows[bid] = ret.data;
						_fillSelect(me.flows[bid], target, select);
						if (flowid) {
							_selectText(select, flowid);
						}
						if (!$('#ch-bid').val()) {
							me.loadFlows(bid, '#ch-tudu-flow', '#ch-flowid', null, flowOnly);
						}
					}
				},
				error: function(res) {
					return ;
				}
			});
		} else {
			_fillSelect(me.flows[bid], target, select);
			if (flowid) {
				_selectText(select, flowid);
			}
			if (!$('#ch-bid').val()) {
				_fillSelect(me.flows[bid], '#ch-tudu-flow', '#ch-flowid');
            }
		}
		
		function _fillSelect(ret, target, select) {
			var s = $(select),
				t = $(target);
			
			s.find('option:not(:eq(0))').remove();
			
			if (null === ret || !ret.length) {
				t.hide();
				if (flowOnly) {
					TOP.showMessage(TOP.TEXT.BOARD_MUST_FLOWONLY_FLOW_NULL);
				}
				return s.attr('disabled', true);
			}
			
			t.show();
			var html = [];
			for (var i = 0, c = ret.length; i < c; i++) {
				html.push('<option value="'+ret[i].flowid+'" title="'+ret[i].subject+'" _classid="'+ret[i].classid+'">'+ret[i].subject+'</option>');
			}
			s.append(html.join(''));
			
			s.attr('disabled', false);
		}
		
		function _selectText(select, flowid) {
			var s = $(select);
			s.val(flowid);
			
			if (me.isFromTudu) {
				s.attr('disabled', true);
			}
		}
	},
	
	/**
	 * 加载主题分类
	 */
	loadClasses: function(bid, select, classid, isChild) {
		if (!bid) {
			return _fillSelect([], select);
		}
		
		if (!isChild) {
			isChild = false;
		}
		
		var me = this;
		if (typeof(this.classes[bid]) == 'undefined') {
			$.ajax({
				type: 'GET',
				dataType: 'json',
				url: '/tudu/classes?bid=' + encodeURIComponent(bid),
				success: function(ret) {
					if (ret.success) {
						me.classes[bid] = ret.data;
						_fillSelect(me.classes[bid], select);
						if (classid) {
							_selectText(me.classes[bid], select, classid);
						}
						if (!$('#ch-bid').val()) {
							me.loadClasses(bid, '#ch-classid', null, true);
						}
					}
				},
				error: function(res) {
					return ;
				}
			});
		} else {
			_fillSelect(me.classes[bid], select);
			if (classid) {
				_selectText(me.classes[bid], select, classid);
			}
			if (!$('#ch-bid').val()) {
                _fillSelect(me.classes[bid], '#ch-classid');
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
			
			if (select == '#ch-classid') {
				$(select).bind('change', function(){
					var items = $('option:selected', $(this));
					items.each(function(){
						if ($(this).val() == '^add-class') {
							me.createClass(bid, select, isChild);
						}
					});
				});
			}
		}
		
		function _selectText(data, select, classid) {
			var o = $(select);
			o.val(classid);
		}
	},
	
	/**
	 * 禁用某部分功能
	 */
	disable: function(id) {
		
	},
	
	/**
	 * 发送图度
	 */
	send: function(action, callback) {
		var form = $('#theform'),
			me   = this;

		if (!$('#bid').val()) {
			if (action != 'autosave') {
				$('#board').focus();
	        	return TOP.showMessage(TOP.TEXT.BOARD_NOT_APPIONT);
			} else {
				return ;
			}
	    }
		
		if (action != 'autosave') {
			if ($('#divide-box:visible').size() && ($('#ch-subject').val() || $('#ch-bid').val())) {
				if (!confirm(TOP.TEXT.SUBMIT_DIVIDE_IN_MODIFY)) {
					return ;
				}
			}
			
			if ($('#acceptmode').attr('checked') == true && !$('#flowid').val()) {
		        var to = $('#to').val().split("\n");
		        if (to.length <= 1) {
		        	return TOP.showMessage(TOP.TEXT.TUDU_CLAIM_ACCEPTER_LESS_THAN_ONE);
		        }
			}
		}

		var to = me.toInput.getItems();
		var i = 0;
        to.each(function (){
        	if ($(this).attr('title') == TOP.TEXT.INVALID_TO_USER) {
        		i++;
        	}
		});

        if (me.ccInput !== null) {
	        var cc = me.ccInput.getItems();
	        cc.each(function (){
	        	if ($(this).attr('title') == TOP.TEXT.INVALID_TO_USER) {
	        		i++;
	        	}
			});
        }
        
        if (me.reviewInput) {
        	var reviewers = me.reviewInput.getItems();
        	reviewers.each(function (){
            	if ($(this).attr('title') == TOP.TEXT.INVALID_TO_USER) {
            		i++;
            	}
    		});
        }

        if (i > 0) {return TOP.showMessage(TOP.TEXT.TUDU_ACCEPTER_EMAIL_ERROR);}
        
		if (action == 'autosave') {
			$('#action').val('save');
			$('#issend').val('0');
		}

	    if (action == 'send') {
	    	var item = me.boardSelect.getSelected(),
				bid  = item ? item.boardid : null;
	    	if (item && item.isclassify && ($('#classid').size() && $('#classid').val() == '^add-class'))
	    	{
	    		$('#classid').focus();
	    		return TOP.showMessage(TOP.TEXT.BOARD_MUST_CLASSIFY);
	    	}
			
			var flowOnly = item && item.flowonly;
			if (flowOnly && !$('#flowid').val()) {
				if ($('#flowid option').size() <= 1) {
					return TOP.showMessage(TOP.TEXT.BOARD_MUST_FLOWONLY_FLOW_NULL);
				} else {
					return TOP.showMessage(TOP.TEXT.BOARD_MUST_FLOWONLY);
				}
            }

	        if (!$('#subject').val()) {
	            $('#subject').focus();
	            return TOP.showMessage(TOP.TEXT.TUDU_SUBJECT_IS_NULL);
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
	    
	    if (action != 'autosave') {
		    if (!whileUploading(this.upload, TOP.TEXT.WAITING_UPLOAD, function(){Modify.send(action, callback);}, form)) {
				return ;
			}
	    }

	    if (TOP.Device.iOS || TOP.Device.Android) { 
	        var src = $('#content').val();
            src = src.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br />');
	    } else {
    	    // 处理HTML
    	    var src = me.editor.getSource();
    	    
    	    if (action !== 'autosave') {
    		    if (!checkContentImage(src, me.editor, function(){Modify.send(action, callback);})) {
    		    	return ;
    		    }
    	    }
    	    
    	    var reg = /<img([^>]+)src="([^"]+)"([^>]+)_aid="([^"]+)"([^>]+)\/>/ig;
    	    form.find(':hidden[name="file[]"]').remove();
    	    while ((result = reg.exec(src)) != null) {
    	    	form.append('<input type="hidden" name="file[]" value="'+result[4]+'" />');
    	    }

    	    src = src.replace(reg, '<img$1src="AID:$4"$3_aid="$4"$5/>');
    	    src = src.replace(/\s+id="[^"]+"/g, '');
	    }

		$('#postcontent').val(src);

		if ($('#attach-list div.upload_error').size()) {
			if (!confirm(TOP.TEXT.COMPOSE_UPLOAD_FAILURE)) {
				return ;
			}
		}

		var data = form.serializeArray();

		if (action != 'autosave') {
			TOP.showMessage(TOP.TEXT.POSTING_DATA, 0, 'success');
	    	form.find(':input').attr('disabled', true);
		}

	    $.ajax({
	        type: 'POST',
	        dataType: 'json',
	        data: data,
	        url: form.attr('action'),
	        success: function(ret) {
	    		if (action != 'autosave') {
	    			TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
	    		}

	            if (ret.data) {
	                $('#ftid').val(ret.data.tuduid);
	                if (ret.data.children != undefined && ret.data.children != null) {
		                var cl = $('#children-list'); 
		                for (var k in ret.data.children) {
		                    cl.find('input[name="ftid-'+k+'"]').val(ret.data.children[k]);
		                }
	                }

	                // 处理返回投票选项
	                if (ret.data.votes) {
	                	var votes = ret.data.votes;
	                	for (var k in votes) {
	                		var opt = $(':hidden[name="newoption[]"][value="'+k+'"]');
	                		if (opt.size()) {
	                			$('#option-' + k + ' a.remove_option').remove();
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
	            	if (action == 'send' && ret.data) {
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
	        	if (action != 'autosave') {
	        		TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
	        	}
	        }
	    });
	},
	
	initSelectLink: function(obj, mailInput, valInput, containGroup, order, synchro, tips, switchTips, accepter) {
		var me = this;
		if (!containGroup) {
			containGroup = false;
		}
		
		if (!order) {
			order = false;
		}
		
		if (!synchro) {
			synchro = false;
		}
		
		if (typeof accepter == 'undefined') {
            accepter = [];
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
	        	draggable: true,
	        	onShow: function() {
					Win.center();
				},
				onClose: function() {
					Win.destroy();
				}
	        });
	        
	        var selector = new TOP.ContactSelector({appendTo: Win.find('div.pop_body'), enableGroup: containGroup, selected: selected, accepter: accepter, mailInput: mailInput, order: order, synchro: synchro, panels: panels, switchModeTips: switchTips});
	        var panel = TOP.Cookie.get('CONTACT-PANEL');
			if (!panel) {
				panel = 'common';
			}
			selector.switchPanel(panel);
			
			Win.find('p[_type="tips"]').remove();
			if (tips) {
			    Win.find('div.pop_body').append('<p style="padding-left:5px;" _type="tips">' + tips + '</p>');
			}
			
			Win.find('button[name="confirm"]').bind('click', function(){
				var se = selector.getSelected();

				if (order) {
					mailInput.clear();
				}
				
				for (var i = 0, c = se.length; i < c; i++) {
					var p = {};

					if (se[i].groupid) {
						p.title = se[i].name + '&lt;' + TOP.TEXT.GROUP + '&gt;';
						p._id   = se[i].groupid
						p.name  = se[i].name;
					} else if(se[i].email) {
						p.title = se[i].name + (se[i].email ? '$lt;' + se[i].email + '&gt;' : '');
						p._id   = se[i].email ? se[i].email : '';
						p.name  = se[i].name;
					} else if (se[i].separator) {
						p.separator = p._id = p.name = p.title = se[i].separator;
					}

					mailInput.addItem(p.name, p);
				}
				Win.close();
			});
	        
	        Win.show();
	    });
	},
	
	/**
	 * 截屏添加到编辑器
	 */
	appendTOEditor: function(fileid) {
		var editor = this.currEditor;
		var url = '/attachment/img?fid=' + fileid;

	    html = '<img src="'+ url +'" _aid="'+fileid+'" />';
	    editor.loadBookmark();
	    editor.pasteHTML(html);
	}
};

//点击 发送 或 回复 附件上传中处理 延迟发送
function whileUploading(upload, msg, completeCallback, form) {

	if (null != upload) {
		if (upload.isUploading()) {
			if (form) {
				form.find(':input').attr('disabled', true);
			}
			
			upload.setParam('upload_complete_handler', function(){
				var stats = this.getStats();
				if (stats.files_queued == 0 && !stats.in_progress) {
					if (form) {
						form.find(':input').attr('disabled', false);
					}
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
			var progress = upload.totalProgress();
			TOP.getJQ()('#msg-progress div').width(progress + '%');
			TOP.getJQ()('#msg-txt-progress').text(progress + '%');
			
			TOP.getJQ()('#result-top a').click(function(){
				TOP.showMessage();
				upload
				.setParam('upload_complete_handler', function(){})
				.setParam('upload_progress_handler', function(file, uploaded, total){
					if (TOP.getJQ()('#msg-progress').size()) {
						TOP.getJQ()('#msg-progress div').style('width', upload.totalProgress + '%');
					}
					upload.onProgress.call(this, file, uploaded, total);
				});
				if (form) {
					form.find(':input').attr('disabled', false);
				}
			});
			
			return false;
		}
	}
	
	return true;
}

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
		
		                var fileid = ret.fileid ? ret.fileid : (ret.data ? ret.data.fileid : null);
		                if (fileid) {
		                	if (me.editor !== null) {
		                		//Modify.appendTOEditor(ret.fileid)
			                    var url = '/attachment/img?fid=' + fileid;
			
			                    html = '<img src="'+ url +'" _aid="'+fileid+'" /><br />';
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
 *  上传类
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
	.mouseover(function(){$('#upload-link').css('text-decoration', 'underline');})
	.mouseout(function(){$('#upload-link').css('text-decoration', 'none');});

	var upload = new TuduUpload(config);
	var handler = new Tudu.Attachment();
	handler.list = list;
	handler.container = container;
	handler.setUpload(upload);
	
	return upload;
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

var _SORTTYPE = null;
function submitSort(url, sorttype, sortasc) {
	if (sorttype != _SORTTYPE) {
		sortasc = 0;
	}
	location = '/tudu/' + url + '&sorttype=' + sorttype + '&sortasc=' + sortasc;
}

function initPicInsert(editors, uploadParams) {
	/*if (typeof(editors) == 'string') {
		var o = {};
		o[editors] = _EDITOR;
		editors = o;
	}*/
	
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
    		
    		$('#pic-modal button[name="piccancel"]').click(function(){
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

// 处理附件img标签
// 处理 base64格式 img标签
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

/**
 * 格式化时间
 */
var DATE_FORMAT_INS = ['yyyy', 'yy', 'mm', 'm', 'dd', 'd', 'h', 'i', 's'];
function formatTime(date, format) {
	if (!date instanceof Date || !date.getFullYear) {
		return '';
	}

	var str, val, ret = format;
	for (var i = 0, c = DATE_FORMAT_INS.length; i < c; i++) {
		str = DATE_FORMAT_INS[i];
		if (format.indexOf(str) != -1) {
			switch (str) {
				case 'yyyy':
				case 'yy':
					val = date.getFullYear();
					break;
				case 'mm':
				case 'm':
					val = date.getMonth();
					break;
				case 'dd':
				case 'd':
					val = date.getDate();
					break;
				case 'h':
					val = date.getHours();
					break;
				case 'i':
					val = date.getMinutes();
					break;
				case 's':
					val = date.getSeconds();
					break;
			}

			if (i <= 5) {
				val = val + '';
				if (val.length < str.length) {
					var a = [], j, l;
					for (j = 0, l = str.length - val.length; j< l; j++) {
						a.push('0');
					}
					a.push(val);
					val = a.join('');
				}
			}

			ret = ret.replace(str, val);
		}
	}

	return ret;
}