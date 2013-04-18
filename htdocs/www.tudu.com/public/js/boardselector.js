/**
 * 版块选择框
 * 
 * 参数列表
 * input 输入框（没有则自动创建）
 * name 表单域名称
 * scope 文档域
 * 
 * @params {object} params
 * @return
 */
var BoardSelector = function(params){
	this.init(params);
};

BoardSelector.prototype = {
	
	_selected: null,
	
	_maxWeight: 0,
	
	/**
	 * 初始化控件
	 * 
	 */
	init: function(params) {
		this._scope = params.scope ? params.scope : document.body;
		this._wrapper = $('<div class="board-select-wrapper"><div class="board-select-arrow"></div></div>');
		
		this._events = {};
		
		if (!params.boards) {
			throw 'Undefined board list';
			return ;
		}
		
		this._boards = params.boards;
		
		if (params.favor) {
			this._favor = params.favor;
		}
		
		if (params.input) {
			this._input = params.input;
			this._wrapper.insertBefore(this._input);
			this._input.appendTo(this._wrapper);
		} else {
			this._input = $('<input type="text" />', this._scope);
		}
		this._input.addClass('board-select-input');
		
		if (params.name) {
			this._valuePlace = $('input[name="'+params.name+'"]');
			if (0 == this._valuePlace.size()) {
				this._valuePlace = $('<input type="hidden" name="'+params.name+'" />', this._scope);
			} else {
				if (this._valuePlace.val()) {
					this.select(this._valuePlace.val());
				}
			}
		}

		var me = this;
		this._input.bind('focus blur', function(e){
			if (e.type == 'focus') {
				me.showMenu();
			} else {
				if (!me.isMenuActive()) {
					var name = this.value;
					name = name.replace(/^\s+|\s+$/, '');
	
					var boardId = me.find(name);
					if (!boardId) {
						return me.clearSelect();
					}
					me.select(boardId);
				}
			}
		}).bind('keyup', function(){
			var keyword = this.value;
			keyword = keyword.replace(/^\s+|\s+$/, '');
			
			this.value = keyword;
			
			me.search(keyword);
		});
		
		this._wrapper.find('.board-select-arrow').bind('click', function(){
			if (!me._input.is(':disabled')) {
				me._input.focus();
			}
		});
		
		$(document.body).bind('click', function(){
			if (!me.isMenuActive()) {
				me.hideMenu();
			}
		});
	},
	
	bind: function(type, callback) {
		if (typeof callback !== 'function') {
			return this;
		}
		
		if (undefined === this._events[type]) {
			this._events[type] = [];
		}
		
		this._events[type].push(callback);
		
		return this;
	},
	
	triggerEvent: function(type) {
		if (undefined !== this._events[type]) {
			var events = this._events[type];
			for (var i = 0, c = events.length; i < c; i++) {
				events[i].call(this);
			}
		}
	},
	
	/**
	 * 显示下拉菜单
	 */
	showMenu: function() {
		var me = this;
		if (!this._menu) {
			this._menu = $('<div class="board-select-menu"></div>', this._scope);
			this._menuBody = $('<div class="board-select-menu-inner"></div>', this._scope);
			
			// 修正IE6覆盖问题
			if ($.browser.msie && $.browser.version < '7.0') {
				this._menu.append('<iframe frameborder="0" style="position:absolute;width:100%;height:100%;z-index:-1"></iframe>');
			}
			
			this._menu.append(this._menuBody);
			
			this._menu.bind('click', function(e){
				var ele = e.srcElement ? $(e.srcElement) : $(e.target),
					item = ele.closest('.board-select-item');
				
				if (ele.hasClass('icon_flag')) {
					me.toggleFavor(item.attr('_boardid'));
					me._input.focus();
					return TOP.stopEventBuddle(e);
				} else {
					me.select(item.attr('_boardid'), item.text());
				}
			}).bind('mouseover mouseout', function(e){
				var ele = e.srcElement ? $(e.srcElement) : $(e.target);
				
				me._menuBody.find('.board-select-item-hover').removeClass('board-select-item-hover');
				
				if (e.type == 'mouseover') {
					var item = ele.closest('.board-select-item');
					item.addClass('board-select-item-hover');
				}
			});
		}
		
		this.buildMenu();
		
		var offset = this._input.offset(),
			css    = {left: offset.left, top: offset.top + this._input.outerHeight(), position: 'absolute', width: this._input.outerWidth() + 'px'};
		
		this._menu.appendTo(this._scope);
		this._menu.css(css).show();
	},
	
	/**
	 * 隐藏下拉菜单
	 */
	hideMenu: function() {
		if (this._menu) {
			this._menu.hide();
		}
	},
	
	/**
	 * 搜索
	 */
	search: function(keyword) {
		if (!keyword.length) {
			return this.buildMenu();
		}
		
		this._menuBody.empty();
		
		var b = this._boards, len = b.length, html = [], fhtml = [], favor = [], matches = 0;
		// 常用版块
		
		for (var i in b) {
			if (undefined == b[i].children || b[i].children.length <= 0) {
				continue ;
			}
			
			var children = b[i].children,
				ch = [];
			for (var k = 0, l = children.length; k < l; k++) {
				var h = '',
					boardName = this.stripTags(children[k].boardname);
				
				if (-1 == children[k].boardname.indexOf(keyword)) {
					continue ;
				}
				
				if (children[k].weight >= 5) {
					h = '<div class="board-select-item" _boardid="'+children[k].boardid+'" _weight="'+children[k].weight+'"><a href="javascript:void(0);" class="icon icon_flag icon_flag_active" title="'+TOP.TEXT.SET_FAVOR_BOARD+'"></a><span>'+boardName+'</span></div>';
					fhtml[fhtml.length] = h;
					favor.push(children[k]);
				} else {
					h = '<div class="board-select-item" _boardid="'+children[k].boardid+'"><a href="javascript:void(0);" class="icon icon_flag" title="'+TOP.TEXT.SET_FAVOR_BOARD+'"></a><span>'+boardName+'</span></div>';
				}
				
				ch[ch.length] = h;
				matches++;
			}
			
			if (ch.length > 0) {
				html[html.length] = '<div class="board-select-menu-section"><div class="board-select-title">'+b[i].boardname+'</div>' + ch.join('') + '</div>';
			}
		}
		
		if (matches > 0) {
			fhtml.sort(function(p, n){
				var op = $(p),
					on = $(n);
				if (parseInt(op.attr('_weight')) < parseInt(on.attr('_weight'))) {
					return 1;
				}
				
				return -1;
			});
			
			fhtml = ['<div class="board-select-menu-section board-favor-list"'+(fhtml.length > 0 ? '' : ' style="display:none"')+'><div class="board-select-title">常用版块</div>', fhtml.join('') , '</div>'].join('');
			
			this._menuBody.html([fhtml, html.join('')].join(''));
		} else {
			this._menuBody.html('<div style="padding: 20px;text-align:center">'+TOP.TEXT.SEARCH_NULL_RESULT+'</div>');
		}
	},
	
	/**
	 * 查找
	 */
	find: function(name) {
		if (!typeof (name) == 'string' || !name) {
			return ''
		}
		
		var b = this._boards;
		for (var i in b) {
			if (undefined == b[i].children || b[i].children.length <= 0) {
				continue ;
			}
			
			var children = b[i].children;
			for (var k = 0, l = children.length; k < l; k++) {
				if (children[k].boardname == name) {
					return children[k].boardid;
				}
			}
		}
		
		return null;
	},
	
	/**
	 * 重建菜单项目
	 */
	buildMenu: function() {
		var b = this._boards, len = b.length, html = [], fhtml = [], favor = [];
		// 常用版块
		
		this._menuBody.empty();
		for (var i in b) {
			if (undefined == b[i].children || b[i].children.length <= 0) {
				continue ;
			}
			
			html[html.length] = '<div class="board-select-menu-section"><div class="board-select-title">'+b[i].boardname+'</div>';
			var children = b[i].children;
			for (var k = 0, l = children.length; k < l; k++) {
				var h = '',
					boardName = this.stripTags(children[k].boardname),
					weight = children[k].weight;
				if (weight >= 5) {
					h = '<div class="board-select-item" _boardid="'+children[k].boardid+'" _weight="'+children[k].weight+'"><a href="javascript:void(0);" class="icon icon_flag icon_flag_active" title="'+TOP.TEXT.SET_FAVOR_BOARD+'"></a><span>'+boardName+'</span></div>';
					fhtml[fhtml.length] = h;
					favor.push(children[k]);
					
					this._maxWeight = Math.max(this._maxWeight, weight);
				} else {
					h = '<div class="board-select-item" _boardid="'+children[k].boardid+'"><a href="javascript:void(0);" class="icon icon_flag" title="'+TOP.TEXT.SET_FAVOR_BOARD+'"></a><span>'+boardName+'</span></div>';
				}
				
				html[html.length] = h;
			}
			html[html.length] = '</div>';
		}
		
		fhtml.sort(function(p, n){
			var op = $(p),
				on = $(n);

			if (parseInt(op.attr('_weight')) < parseInt(on.attr('_weight'))) {
				return 1;
			}
			
			return -1;
		});
		
		fhtml = ['<div class="board-select-menu-section board-favor-list"'+(fhtml.length > 0 ? '' : ' style="display:none"')+'><div class="board-select-title">常用版块</div>', fhtml.join('') , '</div>'].join('');
		
		this._menuBody.html([fhtml, html.join('')].join(''));
	},
	
	/**
	 * 选择版块
	 */
	select: function(boardId) {
		if (boardId == this.getValue) {
			return ;
		}
		
		if (this._valuePlace) {
			this._valuePlace.val(boardId);
		}
		
		var text = '',
			b = this._boards;
		for (var i in b) {
			if (undefined == b[i].children || b[i].children.length <= 0) {
				continue ;
			}
			
			var children = b[i].children;
			for (var k = 0, l = children.length; k < l; k++) {
				if (children[k].boardid == boardId) {
					this._selected = children[k];
					text = children[k].boardname;
					break ;
				}
			}
		}
		
		this._input.val(text);
		
		this.hideMenu();
		
		this.triggerEvent('select');
	},

	clearSelect: function(clearInput) {
		this._selected = null;

		if (clearInput) {
			var text = this._input.attr('title') ? this._input.attr('title') : '';
			this._input.val(text);
		}

		if (this._valuePlace) {
			this._valuePlace.val('');
		}
		
		this.triggerEvent('select');
	},
	
	getSelected: function() {
		return this._selected;
	},
	
	getValue: function() {
		return this._selected ? this._selected.boardid : null;
	},

	/**
	 * 修改常用状态
	 */
	toggleFavor: function(boardId) {
		//this._menuBody.find('.board-favor-list div.board-select-item[_boardid="'++'"]')
		var me = this,
			favorList = this._menuBody.find('div.board-favor-list:eq(0)'),
			items = this._menuBody.find('div.board-select-item[_boardid="'+boardId+'"]'),
			icons = items.find('.icon_flag');
		
		var action = icons.eq(0).hasClass('icon_flag_active') ? 'remove' : 'add'
		
		icons.toggleClass('icon_flag_active');
		
		if (action == 'remove') {
			favorList.find('div.board-select-item[_boardid="'+boardId+'"]').remove();
			if (!favorList.find('div.board-select-item').size()) {
				favorList.hide();
			}
		} else {
			var boardid = items.attr('_boardid'),
			    boardName = this.stripTags(items.text());
			
			this._maxWeight = this._maxWeight > 5 ? this._maxWeight++ : 5;
			var html = '<div class="board-select-item" _boardid="'+boardid+'" weight="'+this._maxWeight+'"><a href="javascript:void(0);" class="icon icon_flag icon_flag_active"></a><span>'+boardName+'</span></div>';
			favorList.show();
			favorList.find('div.board-select-title').after(html);
		}
		
		var b = this._boards;
		for (var i in b) {
			var children = b[i].children;
			if (children) {
				for (var k = 0, l = children.length; k < l; k++) {
					if (children[k].boardid == boardId) {
						children[k].weight = action == 'add' ? this._maxWeight : 0;
						break;
					}
				}
			}
		}
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {bid: boardId},
			url: '/board/'+action+'-favor',
			success: function(ret) {
				if (ret.success) {
					if (ret.data) {
						me._favor = ret.data;
					}
				}
			},
			error: function(res){
				//TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
	},
	
	stripTags: function(text) {
		if (!text) {
			return '';
		}
		return text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
	},
	
	disabled: function() {
		this._input.attr('disabled', 'disabled');
	},
	
	enabled: function() {
		this._input.removeAttr('disabled');
	},
	
	isMenuActive: function() {
		return document.activeElement == this._input[0]
			 || (this._menu && this._menu.is(':visible') && this._menuBody.find('.board-select-item-hover').size() > 0);
	}
};