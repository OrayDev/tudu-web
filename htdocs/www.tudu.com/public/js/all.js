/**
 * all.js 包含子框架对顶层框架所有功能的调用以及公用的非功能实现对象
 * 除Frame对象外，本文件内容不对应实际需求功能，只提供基础或公用对象和方法封装
 * 原则上禁止子框架页直接访问顶层页面的DOM对象。如有需要，请修改或扩展Frame对象
 */

/**
 * @namespace Tudu
 */
var Tudu   = {};

/**
 * @type {Object}
 */
var _CACHE = {};

/**
 * 当前静态内容版本
 */
var version = '1.7.0';

/*
 * 浏览器
 */
var ua = navigator.userAgent.toLowerCase();
var Browser = {
	isIE: ua.indexOf('msie') != -1,
	isFF: ua.indexOf('firefox') != -1,
	isWebkit: ua.indexOf('khtml') != -1 || ua.indexOf('webkit') != -1
};

/**
 * 访问设备
 */
var Device = {
	Android: ua.indexOf('android') != -1,
	iPad:    ua.indexOf('ipad') != -1,
	iPhone:  ua.indexOf('iphone') != -1,
	iOS:	 ua.indexOf('ipad') != -1 || ua.indexOf('iphone') != -1
};

/**
 * 站点
 */
var _SITES = {
	tudu: location.hostname
};

/**
 * 获取顶层对象
 */
function getTop() {
	return window;
}

if (undefined !== TOP) {
	TOP = getTop();
} else {
	var TOP  = getTop();
}

/**
 * 队列批处理对象封装
 * 
 * @class Tudu.Queue
 */
Tudu.Queue = function(params) {
	this.init();
	this.setParam(params);
};

/**
 * 状态代码
 */
Tudu.Queue.Status = {
	Prepared: 0,
	Running: 1,
	Complete: 2,
	Pause: 3,
	Cancel: 4
};

Tudu.Queue.prototype = {
	/**
	 * 支持绑定事件
	 * start : 队列开始
	 * complete: 队列处理结束
	 * roundstart: 每轮执行开始
	 * roundcomplete: 没轮执行结束
	 * cancel: 取消
	 * pause: 暂停
	 * resume: 继续
	 */
	_evts: {'start':true, 'complete': true, 'roundstart':true, 'roundcomplete':true, 'cancel':true, 'pause':true, 'resume':true},
	
	/**
	 * 队列数据
	 */
	_queue: null,
	
	/**
	 * 公用数据
	 */
	_common: null,
	
	/**
	 * 队列处理方法
	 */
	_func: null,
	
	/**
	 * 方法是否异步执行
	 */
	_isAsync: false,
	
	/**
	 * 绑定事件
	 */
	_events: null,
	
	/**
	 * 是否在运行中
	 */
	_status: null,
	
	init: function() {
		this._status = Tudu.Queue.Status.Prepared;
		this._events = {};
		this._queue  = [];
		this._common = {};
	},
	
	/**
	 * 开始执行
	 */
	start: function() {
		this._status = Tudu.Queue.Status.Running;
		this.triggerEvent('start');
		
		this.run();
	},
	
	/**
	 * 执行队列
	 */
	run: function(){
		var _o = this;
		
		if (_o._status >= Tudu.Queue.Status.Complete) {
			return ;
		}
		
		var data = _o._queue.shift();
		if (!data) {
			_o._status = Tudu.Queue.Status.Complete;
			_o.triggerEvent('complete');
			return ;
		}
		
		data = $.extend({}, _o._common, data);
		
		_o.triggerEvent('roundstart', data);
		if (_o._isAsync) {
			_o._func.call(_o, data, function(data){
				_o.triggerEvent('roundcomplete', [data]);
				
				_o.run();
			});
		} else {
			_o._func.call(_o, data);
			
			_o.triggerEvent('roundcomplete', [data]);
			
			_o.run();
		}
	},
	
	/**
	 * 入队
	 */
	push: function(item){
		this._queue.push(item);
	},
	
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
		
		for (var k in p) {
			switch (k) {
			case 'queue':
			case 'data':
				this._queue = p[k];
				break;
			case 'common':
			case 'commonData':
				this._common = p[k];
				break;
			case 'func':
			case 'function':
				this._func = p[k];
				break;
			case 'async':
				this._isAsync = p[k];
				break;
			case 'sync':
				this._isAsync = !p[k];
				break;
			}
		}
	},
	
	/**
	 * 暂停
	 */
	pause: function() {
		this._status = Tudu.Queue.Status.Pause;
		
		this.triggerEvent('pause');
	},
	
	/**
	 * 
	 */
	resume: function() {
		this.run();
		
		this.triggerEvent('resume');
	},
	
	/**
	 * 取消队列
	 * 停止队列执行并丢弃队列数据
	 */
	cancel: function(){
		this._status = Tudu.Queue.Status.Cancel;
		this.discard();
		
		this.triggerEvent('cancel');
	},
	
	/**
	 * 丢弃队列中的数据
	 */
	discard: function() {
		this._queue = [];
	},
	
	/**
	 * 获取对象状态
	 */
	getStatus: function() {
		return this._status;
	},
	
	/**
	 * 触发事件
	 */
	triggerEvent: function(evt, args) {
		if (!args) {
			args = [];
		}
		
		if (undefined != this._events[evt] && this._events[evt].length) {
			for (var i = 0, c = this._events[evt].length; i < c; i++) {
				if (typeof(this._events[evt][i]) == 'function') {
					this._events[evt][i].apply(this, args);
				}
			}
		}
	},
	
	/**
	 * 添加事件监听
	 */
	addEventListener: function(evt, func) {
		if (undefined != this._evts[evt] && typeof(func) == 'function') {
			if (undefined == this._events[evt]) {
				this._events[evt] = [];
			}
			
			this._events[evt].push(func);
		}
	},
	
	/**
	 * 移除事件监听
	 */
	removeEventListener: function(evt, func) {
		if (undefined != this._evts[evt]) {
			return ;
		}
		
		if (undefined == func) {
			delete this._events[evt];
		}
		
		for (var i = 0, c = this._events[evt].length; i < c; i++) {
			if (func == this._events[evt][i]) {
				delete this._events[evt][i];
			}
		}
	}
};

/**
 * @class Frame
 */
var Frame = {
	
	/**
	 * 主框架元素ID
	 */
	_frameId: 'mainframe',
	
	/**
	 * 是否重新加载地址栏hash
	 */
	reloadHash: true,
	
	// 用于记录上次选中的导航菜单项
	lastSwitchLabel: null,
	
	/**
	 * 主框架document对象
	 */
	mainDoc: function() {
		getJQ()('#' + this._frameId)[0].contentWindow.document;
	},
	
	/**
	 * 获取框架内对象
	 */
	getMain: function(selector) {
		return getJQ()(selector, Frame.mainDoc());
	},
	
	/**
	 * 设置主框架高度，侧边栏联动
	 */
	setMainHeight: function(height) {
		height = Math.max(height, document.body.offsetHeight);
		$('div.content-main').css('height', height + 'px');
		$('#mainframe').css('height', height + 'px');
		$('div.content-left').css('height', (height - 3) + 'px');
		$(document.body).css({'overflow': 'auto', 'x-overflow': 'hidden'});
	},
	
	/**
	 * 设置地址栏URL hash串
	 */
	hash: function(str) {
		try {
			if (undefined !== str) {
				this.reloadHash = false;
				getTop().location.hash = str;
			} else {
				return getTop().location.hash;
			}
		} catch (e)
		{}
	},
	
	/**
	 * 更新标题栏内容
	 */
	title: function(str) {
		try {
			if (undefined !== str) {
				getTop().document.title = str;
			} else {
				return getTop().document.title;
			}
		} catch (e)
		{}
	},
	
	/**
	 * 搜索框内容设置
	 */
	searchWord: function(str) {
		if (undefined == str) {
			getJQ()('#searchinput').val(str);
		} else {
			return getJQ()('#searchinput').val();
		}
	},
	
	// 返回上次选中的导航菜单项
	getLastSwitchLabel: function() {
		return this.lastSwitchLabel;
	},
	
	/**
	 * 收放侧边栏
	 */
	toggleSide: function() {
		var $ = getJQ();
		if (display == undefined) {
			$(document.body).toggleClass('frame-left-collapsed');
		} else {
			if (display == true) {
				$(document.body).removeClass('frame-left-collapsed');
			} else {
				$(document.body).addClass('frame-left-collapsed');
			}
		}
	},
	
	skin: function(skin) {
		$('link[rel="stylesheet"]:eq(1)').attr('href', '/css/skin_' + skin + '.css');
	}
};

/**
 * 消息显示
 * 
 * @class Tudu.Message
 */
Frame.Message = {
		
	_timer: null,
	
	/**
	 * 提醒消息队列
	 */
	_queue: new Tudu.Queue({
		async: true,
		func: function(data, callback) {
			if (Frame.Message._timer) {
				clearTimeout(Frame.Message._timer);
			}
			
			if (!data.content) {
				$('#result-top').fadeOut('normal', callback);
				return ;
			}
			
			
			var ele = $('#result-top');
			cls = data.cls ? 'result_' + data.cls : 'result_failure';
			if (!ele.size()) {
				ele = $('<div>');
				ele
				.attr('id', 'result-top')
				.hide()
				.css({position: 'absolute', top: '35px'});
				getJQ()('body').append(ele);
			}
			//alert(data.content);
			ele.html(data.content)
			.removeClass()
			.addClass(cls)
			.fadeIn();
			ele.css('left', (window.document.body.offsetWidth - ele.width()) / 2 + 'px');
			
			if (data.timeout !== 0) {
				var timeout = data.timeout;
				if (!timeout) {
					timeout = 10000;
				}
				
				Frame.Message._timer = setTimeout(function(){
					ele.fadeOut('normal', callback);
					clearTimeout(Frame.Message._timer);
				}, timeout);
			}
		}
	}),
	
	/**
	 * 显示提醒
	 */
	show: function(content, timeout, cls) {
		this.push(content, timeout, cls);
		
		this.showAll();
	},
	
	/**
	 * 显示所有消息
	 */
	showAll: function() {
		this._queue.start();
	},
	
	/**
	 * 压入队列显示
	 */
	push: function(content, timeout, cls) {
		this._queue.push({
			content: content, 
			timeout: timeout,
			cls: cls
		});
	}
};

/**
 * alias
 */
var Message = Frame.Message;

/**
 * 绑定邮箱列表功能调用
 */
Frame.Mailbox = {

	/**
	 * 是否存在
	 */
	exists: function(address) {
		return getJQ()('#user_mailbox_list ul li[email="'+address+'"]').size() > 0;
	},
	
	/**
	 * 添加邮箱
	 */
	append: function(address) {
		getJQ()('#user_mailbox_list ul')
		.prepend('<li email="'+address+'"><a target="_blank" href="/email/login?address='+address+'"><span class="labelname">'+address+'</span><span class="mail_count"></span></a></li>');
	},
	
	/**
	 * 删除
	 */
	remove: function(address) {
		getJQ()('#user_mailbox_list ul li[email="'+address+'"]').remove();
	},
	
	/**
	 * 排序
	 */
	sort: function(address, type) {
		var obj = getJQ()('#user_mailbox_list ul li[email="'+address+'"]');
        if (type == 'up') {
            obj.prev().before(obj);
        } else {
            obj.next().after(obj);
        }
	}
};

/**
 * 自定义标签功能调用
 */
var Label = {
	
	/**
	 * 标签数据列表
	 * 
	 */
	labels: [],
	
	/**
	 * 新建标签窗口
	 */
	tpl: {'create': '<div class="pop pop_linkman"><form id="labelform" method="post" action="/label/create"><div class="pop_header"><strong>'+TEXT.CREATE_LABEL+'</strong><a class="icon icon_close close"></a></div><div class="pop_body"><p>'+TEXT.LABEL_NAME+'：<input type="text" class="input_text" style="width:325px" maxlength="50" name="name"></p></div><div class="pop_footer"><button type="submit" name="confirm" class="btn">'+TEXT.CONFIRM+'</button><button type="button" class="btn close">'+TEXT.CANCEL+'</button></div></form></div>'},
	
	/**
	 * 标签菜单
	 */
	menu: null,
	
	/**
	 * 是否正在处理菜单输出
	 */
	rendering: false,
	
	/**
	 * 写入标签数据
	 */
	setLabels: function(labels) {
		if (this.rendering) {
			return ;
		}
		
		this.labels = labels;
		
		return this;
	},
	
	/**
	 * 获取标签列表
	 */
	getLabels: function(index) {
		if (typeof index == 'string' && index) {
			var r = {},
				l = Label.labels;
			for (var i = 0, c = l.length; i < c; i++) {
				if (undefined == l[i][index]) {
					return r;
				}
				
				r[l[i][index]] = l[i];
			}
			
			return r;
		}
		
		return this.labels;
	},
	
	getCustomerLabels: function(index) {
		if (typeof index == 'string' && index) {
			var r = {},
				l = Label.labels;
			for (var i = 0, c = l.length; i < c; i++) {
				if (undefined == l[i][index]) {
					return r;
				}
				
				if (l[i].issystem) {
					continue ;
				}
				
				r[l[i][index]] = l[i];
			}
			
			return r;
		}
		
		return this.labels;
	},
	
	/**
	 * 更新标签内容
	 */
	updateLabel: function(alias, data) {
		for (var i = 0, c = this.labels.length; i < c; i++) {
			if (this.labels[i].labelalias == alias) {
				for (var k in data) {
					this.labels[i][k] = data[k];
				}
				break ;
			}
		}
		
		return this;
	},
	
	/**
	 * 重新加载菜单
	 */
	refreshMenu: function(callback) {
		var menuBody  = [],
			sideSys   = [],
			sidePer   = [],
			sideIsSys = true,
			menuIsSys = true;
		
		if (this.rendering) {
			return ;
		}
		
		this.rendering = true;
		
		for (var i = 0, c = this.labels.length; i < c; i++) {
			var label = this.labels[i],
				isshow = label.isshow == 1 || (label.isshow == 2 && label.totalnum > 0),
				count = label.unreadnum > 0 ? '(' + label.unreadnum + ')' : '';
			
			if (label.issystem) {
				if (label.labelalias == 'review' || label.labelalias == 'starred' || label.labelalias == 'todo') {
					if (label.totalnum > 0) {
						count = '(' + (label.unreadnum > 0 ? label.unreadnum + '/' : '') + label.totalnum + ')';
					} else {
						count = '';
					}
				} else if (label.labelalias == 'ignore') {
					count = '';
				}
			} else {
				count = label.unreadnum > 0 ? '(' + label.unreadnum + ')' : '';
			}
			
			if (label.labelalias == 'drafts' || label.labelalias == 'ignore') {
				label.unreadnum = 0;
			}
			
			if (isshow) {
				label.labelname = label.labelname ? label.labelname : '';
				if (label.issystem) {
					var labelName = label.labelname.length <= 8 ? label.labelname : label.labelname.substr(0, 8) + '...';
					if (label.labelalias == 'review') {
						sideSys[sideSys.length] = '<li order="'+label.ordernum+'" id="f_'+label.labelalias+'_td"><a onclick="Label.focusLabel(\''+label.labelalias+'\')" target="main" href="/tudu/converge?search='+label.labelalias+'" id="f_inbox" class="'+(label.unreadnum ? 'b' : '')+'"><span>'+labelName+'</span><span class="lab_tudu_count">'+count+'</span></a></li>';
					} else {
						sideSys[sideSys.length] = '<li order="'+label.ordernum+'" id="f_'+label.labelalias+'_td"><a onclick="Label.focusLabel(\''+label.labelalias+'\')" target="main" href="/tudu/?search='+label.labelalias+'" id="f_inbox" class="'+(label.unreadnum ? 'b' : '')+'"><span>'+labelName+'</span><span class="lab_tudu_count">'+count+'</span></a></li>';
					}
				} else {
					var labelName = label.labelalias.length <= 8 ? label.labelalias : label.labelalias.substr(0, 8) + '...';
					sidePer[sidePer.length] = '<li order="'+label.ordernum+'" id="f_'+label.labelid+'_td"><a target="main" onclick="Label.focusLabel(\''+label.labelid+'\')" href="/tudu/?search=cat&cat='+label.labelalias+'" id="f_'+label.labelid+'" class="'+(label.unreadnum ? 'b' : '')+'"><span style="background-color:'+label.bgcolor+'; margin: 6px 3px 0 0;" class="tag_icon"></span><span class="labelname">'+labelName+'</span><span class="lab_tudu_count">'+count+'</span></a></li>';
				}
			} else {
				if (label.issystem) {
					if (label.labelalias == 'review') {
						menuBody[menuBody.length] = '<div class="menu-item" order="'+label.ordernum+'" name="'+label.labelname+'" issystem="1"><a href="/tudu/converge?search='+label.labelalias+'" target="main" class="'+(label.unreadnum ? 'b' : '')+'">'+label.labelname+'<span class="lab_tudu_count">'+count+'</span></a></div>';
					} else {
						menuBody[menuBody.length] = '<div class="menu-item" order="'+label.ordernum+'" name="'+label.labelname+'" issystem="1"><a href="/tudu/?search='+label.labelalias+'" target="main" class="'+(label.unreadnum ? 'b' : '')+'">'+label.labelname+'<span class="lab_tudu_count">'+count+'</span></a></div>';
					}
				} else {
					menuBody[menuBody.length] = '<div class="menu-item" order="'+label.ordernum+'" name="'+label.labelname+'" issystem="0"><span style="background:'+label.bgcolor+'" class="menu-square"></span><a href="/tudu/?search=cat&cat='+label.labelalias+'" target="main" class="'+(label.unreadnum ? 'b' : '')+'">'+label.labelname+'<span class="lab_tudu_count">'+count+'</span></a></div>';
				}
			}
		}
		
		menuBody[menuBody.length] = '<div class="menu-step"></div>';
		menuBody[menuBody.length] = '<div class="menu-item"><a target="main" href="/label/">'+TEXT.TAGS_MANAGE+'</a></div>';
		menuBody[menuBody.length] = '<div class="menu-item" onclick="Label.create();"><a target="main" href="javascript:void(0)">'+TEXT.CREATE_LABEL+'</a></div>';
		
		if (null == this.menu) {
			this.menu = new $.Dropdown({
			    id: 'side-label-menu',
			    wrapCls: 'option-menu-wrap label-menu',
			    maxHeight: 350,
			    target: $('#f_morelabel_td'),
			    resetpos: true,
			    separate: true,
			    menuCss:{width: '200px'},
			    onHide: function(){
			    	Label.focusLabel(Frame.getLastSwitchLabel());
			    }
			});
			
			this.menu._initMenu();
		}
		
		this.clearAll();
		
		this.menu.setBody(menuBody.join(''));
		$('#sys_label_list ul').html(sideSys.join(''));
		$('#user_label_list ul').html(sidePer.join(''));
		
		if (sidePer.length <= 0) {
			$('#user_label_list').hide();
		} else {
			$('#user_label_list').show();
		}
		
		$('#side-label-menu div.menu-item')
		.bind('click', function(){
			var o = $(this);
			var href = o.find('a:eq(0)').attr('href');
			
	        if (href && -1 == href.indexOf('javascript:')) {
	            $('#mainframe')[0].contentWindow.location = o.find('a:eq(0)').attr('href');
	        }
		 })
		.bind('mouseover', function(){$(this).addClass('menu-over');})
		.bind('mouseout', function(){$(this).removeClass('menu-over');});
		
		this.rendering = false;
		
		return this;
	},
	
	focusLabel: function(id) {
		var $ = getJQ();
		
		if ($('#sb-middle .on').attr('id') != 'f_morelabel_td') {
			Frame.lastSwitchLabel = '#' + $('#sb-middle .on').attr('id');
		}
		
		$('#sb-middle .on').removeClass('on');
		
		if (!id) return ;
		
		if (typeof(id) == 'string') {
			if (id.indexOf('#') != -1) {
				$(id).addClass('on');
			} else {
			$('#f_'+id+'_td').addClass('on');}
		} else {
			$(id).addClass('on');
		}
	},
	
	/**
	 * 显示创建标签窗口
	 */
	create: function(callback) {
		var Win = Frame.TempWindow,
			me = this;
		
		Win.append(Label.tpl.create, {
			width: 450,
			draggable: true,
			onClose: function() {
				Win.destroy();
			}
		});
		
		var form = Win.find('#labelform');
		form.submit(function(){return false;});
		form.submit(function(){
			var name = form.find(':text').val().replace(/^\s+|\s+$/, ''),
				input = form.find(':text');
			if (!name.length) {
				return showMessage(TEXT.MISSING_LABEL_NAME);
			}
			
			var data = form.serializeArray();
			input.attr('disabled', true);
			
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: data,
				url: form.attr('action'),
				success: function(ret) {
					showMessage(ret.message, 5000, ret.success ? 'success' : null);
					input.attr('disabled', false);
					if (ret.success) {
						if (ret.data.labels) {
				    	   TOP.Label.setLabels(ret.data.labels).refreshMenu();
					    }
						Win.close();
					}
					
					if (typeof callback == 'function') {
						callback.call(TOP.Label, ret);
					}
				},
				error: function(res) {
					showMessage(TEXT.PROCESSING_ERROR, 5000);
					input.attr('disabled', false);
				}
			});
		});
		
		Win.show();
	},
	
	/**
	 * 清空标签列表
	 */
	clearAll: function() {
		if (this.menu) {
			this.menu.clear();
		}
		
		return this;
	},
	
	/**
	 * 隐藏标签表单
	 */
	hideMenu: function() {
		if (this.menu) {
			this.menu.hide();
		}
	}
};

/**
 * 快捷标签功能调用
 */
Frame.Boards = {
	/**
	 * 添加快捷版块
	 */
	append: function(id, name) {
		getJQ()('#user_board_list ul')
		.append('<li id="b_'+id.replace('^', '_')+'"><a href="/board/?bid='+id+'" target="main">'+name+'</a></li>');
	},
	
	/**
	 * 移除快捷
	 */
	remove: function(id) {
		getJQ()('#b_' + id.replace('^', '_')).remove();
	}
};

/**
 * 编辑器
 */
var Editor = function(obj, params, jq){
    this.init(obj, params, jq);
};
Editor.idCss = 'KE_css';
Editor.tools = [
    'fontname', 'fontsize', '|', 'bold', 'italic', 'underline', 'strikethrough',
    'forecolor', 'hilitecolor', '|', 'selectall', 'removeformat', 'alignmenu', 'listmenu',
    'outdent', 'indent', '|', 'link', 'unlink', 'table', 'source', 'fullscreen'
];
Editor.prototype = {
    _obj: null,
    
    _editor: null,
    
    _params: null,
    
    _isEditor: true,
	
	_jq: null,
    
    init: function(obj, params, jq){
        /*if (Device.Android || Device.iOS) {
            //this._editor = $(obj);
            this._isEditor = false;
            $(obj).removeAttr('disabled').removeAttr('readonly');
            this._obj = $(obj);
            
            var content = this._obj.val();
            content = content.replace(/\n/g, '').replace(/\r/g, '').replace(/\t/g, '').replace(/\s/g, '')
                      .replace(/<br>/g, "\n").replace(/<br \/>/g, "\n").replace(/<\/p>/g, "\n")
                      .replace(/&nbsp;/g, ' ').replace(/<[^>]+>/g, '');
            this._obj.val(content);
            
            return ;
        }*/
        params.items = Editor.tools;
        params.basePath   = '/js/kindeditor-4.1.5/';
        params.themesPath = params.basePath + 'themes/';
        params.filterMode = false;
		this._jq     = jq;
		
		if (obj) {
		    this._obj = $(obj);
		}
		
		var fillEmpty = $.browser.msie ? '&#09;' : '<br />';

        //this._obj    = $(obj);
		var me  = this;
		var scope = params.scope ? params.scope : window;
		// 动态加载编辑器文件
		if (!scope.KindEditor) {
			jq.getScript('/js/kindeditor-4.1.5/kindeditor.js?1005', function() {
				jq.getScript('/js/kindeditor-4.1.5/lang/zh_CN.js?1005', function() {
					
					me._editor = scope.KindEditor.create(obj, params);
					if (undefined !== params.statusbar && false === params.statusbar) {
						me._editor.statusbar.hide();
					}
					if (true === params.disabled) {
						me._editor.readonly(true);
					}
					if (params.ctrl) {
						for (var k in params.ctrl) {
							jq(me._editor.edit.doc.body).bind('keydown', function(e){
								var code = e.keyCode ? e.keyCode : e.which;
								if (e.ctrlKey && !e.altKey && !e.shiftKey && (code == 13)) {
                                    params.ctrl[k]();
                                }

							})
						}
					}

					if (params.css && me._editor.isEmpty()) {
						var html = '<p style="';
						for (var sk in params.css) {
							html += sk + ':' + params.css[sk] + ';';
						}
						html += '">' + fillEmpty + '</p>';
						me._editor.html(html);
					}
				});
			});
		} else {
			this._editor = scope.KindEditor.create(obj, params);
			
			if (undefined !== params.statusbar && false === params.statusbar) {
				this._editor.statusbar.hide();
			}
			if (true === params.disabled) {
				this._editor.readonly(true);
			}
			
			if (params.ctrl) {
			    var me = this;
			    setTimeout(function() {
			        if (me._editor && me._editor.edit) {
        				for (var k in params.ctrl) {
        					//scope.K.ctrl(this._editor.edit.doc, k, params.ctrl[k]);
        					jq(me._editor.edit.doc.body).bind('keydown', function(e){
        						var code = e.keyCode ? e.keyCode : e.which;
        						
        						if (e.ctrlKey && !e.altKey && !e.shiftKey && (code == 13)) {
        							params.ctrl[k]();
        						}
        					});
        				}
			        }
			    }, 500);
			}
			if (params.css && this._editor.isEmpty()) {
				var html = '<p style="';
				for (var sk in params.css) {
					html += sk + ':' + params.css[sk] + ';';
				}

				html += '">' + fillEmpty + '</p>';

				this._editor.html(html);
			}
		}
    },
    
    disabled: function() {
        if (this._isEditor) {
            this._editor.readonly();
        } else {
            this._obj.attr('readonly', 'readonly');
        }
    },
    
    enabled: function() {
        if (this._isEditor && this._editor) {
            this._editor.readonly(false);
        } else {
            this._obj.removeAttr('readonly');
        }
    },
    
    destroy: function(){
        if (this._isEditor && this._editor) {
            this._editor.remove();
        }
    },
    
    getSource: function() {
        if (this._isEditor && this._editor) {
            return this._editor.html();
        } else if (this._obj) {
            this._obj.val();
        }
        
        return '';
    },
    
    setSource: function(html) {
        if (this._isEditor && this._editor) {
            this._editor.html(html);
        }
    },
    
    pasteHTML: function(html) {
        if (this._isEditor) {
            this._editor.insertHtml(html);
        }
    },
    
    focus: function() {
    	if (this._editor) {
    		this._editor.focus();
    	}
    },
    
    isNull: function() {
        if (this._isEditor && this._editor) {
            return this._editor.isEmpty();
        }
        
        return false;
    },
    
    getEditor: function() {
        return this._editor;
    }
};

var Cookie = {
	set: function(key, value, options) {
		options = options || {};
	    if (value === null) {
	        value = '';
	        options.expires = -1;
	    }
	    var expires = '';
	    if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
	        var date;
	        
	        if (typeof options.expires == 'number') {
	            date = new Date();
	            date.setTime(date.getTime() + options.expires);
	        } else {
	            date = options.expires;
	        }
	        expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
	    }
	    var path = options.path ? '; path=' + options.path : '';
	    var domain = options.domain ? '; domain=' + options.domain : '';
	    var secure = options.secure ? '; secure' : '';
	    
	    document.cookie = [key, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
	},
	
	get: function(name) {
		var v = null;
	    if (document.cookie && document.cookie != '') {
	        var cookies = document.cookie.split(';');
	        for (var i = 0; i < cookies.length; i++) {
	            var cookie = $.trim(cookies[i]);
	            if (cookie.substring(0, name.length + 1) == (name + '=')) {
	                v = decodeURIComponent(cookie.substring(name.length + 1));
	                break;
	            }
	        }
	    }
	    return v;
	}
};

/**
 * 顶层框架页文档对象等内容
 * 框架内对外层对象的控制，只允许通过获取顶层框架的window对象进行调用
 * 以下函数对于子框架页面来说就是 top 对象的成员函数
 */

/**
 * ajax请求
 */
function ajax(param) {
	return $.ajax(param);
}

/**
 * 顶层框架Document对象
 * @return
 */
function getDoc() {
	return getTop().document;
}

/**
 * 获取内层框架document对象
 * @return
 */
function getFrameDoc() {
	return getJQ()('#mainframe')[0].contentWindow.document;
}

/**
 * 获取顶层jQuery对象
 * @return {Object}
 */
function getJQ() {
	return jQuery
}

/**
 * 
 * @param msg
 * @param timeout
 * @param cls
 * @return
 */
function showMessage(msg, timeout, cls) {
	return Frame.Message.show(msg, timeout, cls);
}

/**
 * 
 * @param str
 * @return
 */
function isEmail(str) {
	if (!str) return false;
	return (/^[\d\w]+([\._\-]\w+)*@\w+([\._\-]\w+)+/).test(str);
}

/**
 * 动态加载js
 * @param url
 * @param success
 * @param failure
 * @return
 */
function loadJS(url, success, failure) {
	if (!url) return ;
	
	var url = url.indexOf('://') != -1 ? url : '/js/' + url;
	
	if (getJQ()('script[src="'+url+'"]').size()) return callback();
	
	var tag = document.createElement('script');
	tag.type = 'text/javascript';
	tag.src = fileName;
	
	if (typeof(failure) != 'function') {
		failure = function(e){};
	}
	
	if (typeof(success) != 'function') {
		success = function(e){};
	}
	
	if (getTop().BROWSER.isIE) {
		tag.onreadystatechange = function(e){if (this.readyState == 'complete'){success(e);}};
	} else {
		tag.onload = function(e){success(e);};
	}
	tag.onerror = function(e){failure(e);}
	
	document.body.appendChild(tag);
}

	
/**
 * 选择框全选择
 * 
 * @param name
 * @param ischeck
 * @param scope
 * @return
 */
function checkBoxAll(name, ischeck, scope) {
	var $ = getJQ();
	if (scope) {
		$(':checkbox[name="'+name+'"]:visible', scope).attr('checked', ischeck);
	} else {
		$(':checkbox[name="'+name+'"]:visible').attr('checked', ischeck);
	}
}

/**
 * 输入框提示
 * @param id
 * @param css
 * @param ignore
 * @param scope
 * @return
 */
function keyhint(id, css, ignore, scope) {
	var $ = getJQ(), input;
	
	if (scope) {
		input = $(id, scope);
	} else {
		input = $(id, document.body);
	}
	
	input.focus(function(){
		input.filter('.'+css).removeClass(css).val('');
	}).blur(function(){
		if (this.value == '')
			input.addClass(css).val(this.title);
	}).each(function(){
		if(this.value == '' || this.value == this.title)
			input.addClass(css).val(this.title);
	});
	
	input.parents('form:eq(0)').submit(function(){
		if (ignore) {
			input.filter('.'+css).removeClass(css).val('');
		} else if (input.hasClass(css) || input.val()== ''){
			alert(input.attr('title'));
			input.focus();
			return false;
		}
	});
}

/**
 * 停止事件冒泡
 * @return
 */
function stopEventBuddle(e) {
	e.cancelBubble = true;
	if (e.stopPropagation) {
		e.stopPropagation();
	}
}

/**
 * 格式化字符串
 * formatString(xxx{0}xxx{1}, $0, $1)
 * @return
 */
function formatString() {
	var args = Array.prototype.slice.call(arguments, 1);
	var source = arguments[0];
	for (var i in args) {
		source = source.replace(new RegExp("\\{" + i + "\\}", "g"), args[i]);
	}
	return source;
}

/**
 * 添加窗口元素
 * 
 * @param id
 * @param obj
 * @param opts
 * @return void
 */
function appendWindow(id, obj, opts) {
	var win = getJQ()('<div>'),
		$   = getJQ();
	
	opts = opts ? opts : {};
	
	win
	.addClass('pop_wrap')
	.attr('id', id)
	.css({position: 'absolute', top: '1px', left: '10px', background: '#ebf4d8'})
	.append(obj);
	
	win.appendTo('#win-ct');
	
	return win.window(opts);
}

/**
 * 复制文本到系统剪贴版
 * 
 * @param text
 * @return void
 */
function copyToClipboard(txt) {
	 if(window.clipboardData) {
		 window.clipboardData.clearData();
	 	window.clipboardData.setData("Text", txt);
	 } else if(navigator.userAgent.indexOf("Opera") != -1) { 
		 window.location = txt;
	 } else if (window.netscape) {
		 try {
			 netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect"); 
		 } catch (e) {
			 alert("被浏览器拒绝！\n请在浏览器地址栏输入'about:config'并回车\n然后将'signed.applets.codebase_principal_support'设置为'true'");
		 }
		 var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard); 
		 if (!clip)
			 return;
		 var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable); 
		 	if (!trans)
		 		return;
		 trans.addDataFlavor('text/unicode');
		 var str = new Object();
		 var len = new Object();
		 var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString); 
		 var copytext = txt;
		 str.data = copytext;
		 trans.setTransferData("text/unicode",str,copytext.length*2);
		 var clipid = Components.interfaces.nsIClipboard;
		 if (!clip)
		 return false;
		 clip.setData(trans,null,clipid.kGlobalClipboard);
	 }
	 alert("内容已复制到剪切版"); 
}

/**
 * SessionID
 */
var _SID = Cookie.get('sid');


/**
 * 顶层框架扩展功能
 * 以下内容属于all.js
 */
var Frame = Frame || {};

var Cast = {
	/**
	 * 组织架构数据
	 */
	_data: {},
	
	/**
	 * 是否正在加载
	 */
	_loading: false,
	
	/**
	 * 加载完成后回调
	 */
	_callback: [],
	
	/**
	 * 记录加载的时间
	 */
	_loadTime: null,
	
	/**
	 * 加载组织架构列表
	 */
	load: function(callback){
		var _o = this;
		if (this._loading) {
			if (typeof (callback) == 'function') {
				_o._callback.push(callback);
				return ;
			}
		} else if (!_o._data.users || !_o._data.depts) {
			if (typeof (callback) == 'function') {
				_o._callback.push(callback);
			}
		}
		
		if (!_o._data.users || !_o._data.depts) {
			_o._loading = true;
	        $.ajax({
	            type: 'GET',
	            dataType: 'json',
	            url: '/frame/cast',
	            success: function(ret) {
		            if (ret.success) {
			            if (ret.data && ret.data.users && ret.data.depts) {
			                _o._data = {
			                	users: ret.data.users,
			                	depts: ret.data.depts
					        };
			                _o._data.groups = ret.data.groups;
			                
			                for (var i = 0, c = _o._callback.length; i < c; i++) {
			                	_o._callback[i](_o.data);
			                }
			            }
			            _o._loadTime = String(Date.parse(new Date())).substr(0,10);
		            }
		            
		            _o._callback = [];
		            _o._loading = false;
	            },
	            error: function(res) {
	            	_o._callback = [];
		            _o._loading = false;
	            }
	        });
	        
	        return ;
		}
		
		if (_o._callback.length) {
			for (var i = 0, c = _o._callback.length; i < c; i++) {
				_o._callback[i](_o._data);
		    }
			_o._callback = [];
		} else if (typeof(callback) == 'function') {
			callback(_o._data);
		}
	},
	
	/**
	 * 刷新
	 */
	reload: function(){
		this._data = null;
		
		this.load();
	},
	
	/**
	 * 清除数据
	 */
	clear: function() {
		this._data = {};
	},
	
	/**
	 * 获取cast加载时间
	 */
	getTime: function() {
		return this._loadTime;
	},
	
	/**
	 * 更新数据
	 */
	set: function(key, val) {
		if (null == this._data) {
			this._data = {};
		}
		this._data[key] = val;
	},
	
	/**
	 * 获取数据
	 */
	get: function(key) {
		if (undefined == key) {
			return this._data;
		}
		
		if (null == this._data || typeof(this._data[key]) == 'undefined') {
			return false;
		}
		
		return this._data[key];
	}
};

var Contact = {
	/**
	 * 组织架构数据
	 */
	_data: {},
	
	/**
	 * 是否正在加载
	 */
	_loading: false,
	
	/**
	 * 加载完成后回调
	 */
	_callback: [],
	
	/**
	 * 加载组织架构列表
	 */
	load: function(callback){
		var _o = this;
		if (this._loading) {
			if (typeof (callback) == 'function') {
				_o._callback.push(callback);
				return ;
			}
		} else if (!_o._data.contacts || !_o._data.groups) {
			if (typeof (callback) == 'function') {
				_o._callback.push(callback);
			}
		}
		
		if (!_o._data.contacts || !_o._data.groups) {
			_o._loading = true;
	        $.ajax({
	            type: 'GET',
	            dataType: 'json',
	            url: '/frame/contact',
	            success: function(ret) {
		            if (ret.success) {
			            if (ret.data && ret.data.contacts && ret.data.groups) {
			                _o._data = {
			                	contacts: ret.data.contacts,
			                	groups: ret.data.groups,
			                	lastcontact: ret.data.lastcontact
					        };
			                
			                for (var i = 0, c = _o._callback.length; i < c; i++) {
			                	_o._callback[i](_o.data);
			                }
			            }
		            }
		            
		            _o._callback = [];
		            _o._loading = false;
	            },
	            error: function(res) {
	            	_o._callback = [];
		            _o._loading = false;
	            }
	        });
	        
	        return ;
		}
		
		if (_o._callback.length) {
			for (var i = 0, c = _o._callback.length; i < c; i++) {
				_o._callback[i](_o._data);
		    }
			_o._callback = [];
		} else if (typeof(callback) == 'function') {
			callback(_o._data);
		}
	},
	
	/**
	 * 刷新
	 */
	reload: function(){
		this._data = null;
		
		this.load();
	},
	
	/**
	 * 更新数据
	 */
	set: function(key, val) {
		if (null == this._data) {
			this._data = {};
		}
		this._data[key] = val;
	},
	
	/**
	 * 获取数据
	 */
	get: function(key) {
		if (undefined == key) {
			return this._data;
		}
		
		if (null == this._data || typeof(this._data[key]) == 'undefined') {
			return false;
		}
		
		return this._data[key];
	},
	
	/**
	 * 清空数据
	 */
	clear: function() {
		//this._data = {};
		delete this._data.contacts;
		delete this._data.groups;
	}
};

/**
 * 锁屏
 */
Frame.LockScreen = {
	
	//_tpl: '<div id="lock-mask" class="lock_wrap"><form id="unlockform" method="post" action="/frame/unlock"><div class="lock_logo"><img src="" /></div><div class="lock_content"><div class="lock_title"><strong>'+TOP.TEXT.LOCK_SCREEN_TIPS+'</strong></div><div style="text-align:center;">'+TOP.TEXT.TUDU_PWD+TOP.TEXT.CLN+'<input class="input_text" name="password" type="password" /><input class="btn" type="submit" value="'+TOP.TEXT.CONFIRM+'"></div><div class="unlock_info"></div></div></form></div>',
	
	_tpl: [
	'<div id="lock-mask" class="lock_wrap">',
	'<div style="width:428px;margin:135px auto 0;">',
	'<div class="lock-logo"><img src="img/logo.gif" /></div>',
	'<div class="lock-content">',
		'<div class="lock-inner">',
			'<form id="unlockform" method="post" action="/frame/unlock" class="lock">',
				'<p id="unlock-tips" class="lock-tips" style="display:hidden"></p>',
				'<p class="lock-title"><strong>'+TOP.TEXT.LOCK_SCREEN_TIPS+'</strong></p>',
				'<p style="font-size:14px; padding-left:50px;"><label for="password">'+TOP.TEXT.TUDU_PWD+TOP.TEXT.CLN+'</label>&nbsp;<input id="unlock-password" class="text" name="password" type="password"  autocomplete="off" onfocus="this.className=\'text focus\';" blur="this.className=\'text\';" />&nbsp;<input type="submit" value="'+TOP.TEXT.CONFIRM+'" class="lock-btn"/></p>',
			'</form>',
		'</div>',
	'</div>',
	'</div>',
	'</div>'
	].join(''),
	
	/**
	 * 遮罩
	 */
	_mask: null,
	
	/**
	 * 重试次数
	 */
	_retry: 0,
	
	/**
	 * 检查锁屏
	 */
	check: function(){
		var isLock = getTop().Cookie.get('lockscreen');

		if (isLock == 1) {
		    location = '/login/logout';
		}
	},
	
	/**
	 * 锁屏
	 */
	lock: function(logo) {
		var _o = this;
		
		$(window).unbind('beforeunload');
		$(window).bind('beforeunload', function(e){
			return TOP.TEXT.LOCKSCREEN_TIPS;
		});
		
		if (null == this._mask) {
			_o._mask = $(this._tpl);
		}
		if ($.browser.msie && $.browser.version < '7.0') {
			_o._mask.append('<iframe frameborder="0" src="javascript:void(0);" style="height:100%;display:none;"></iframe>');
		}
		
		_o._mask.find('img').attr('src', logo);
		_o._mask.css({'z-index': 9999, 'height': $(document.body).height() + 'px'});
		
		_o._mask.find('input[name="password"]').keyup(function(){_o._mask.find('.unlock_info').text('');});
		
		_o._mask.find('#unlockform').submit(function(){return false;});
		_o._mask.find('#unlockform').submit(function(){
			var password = _o._mask.find('input[name="password"]').val();
			
			if (!password) {
				return alert(TOP.TEXT.PASSWORD_IS_NULL);
			}
			
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: {password: password},
				url: '/frame/unlock',
				success: function(ret) {
					if (ret.success) {
						return Frame.LockScreen.unlock();
					}
					
					_o._mask.find('#unlock-tips').text(ret.message).show();
					if (++_o._retry >= 3) {
						location = '/login/logout';
					}
				},
				error: function(res){}
			});
		});
		
		$(document.body).append(_o._mask);
		
		TOP.Cookie.set('lockscreen', 1, {path: '/'});
	},
	
	/**
	 * 解锁
	 */
	unlock: function() {
		var TOP = getTop(),
		$   = getJQ();
	
		$(window).unbind('beforeunload');
		this._mask.remove();
		this._mask = null;
		this._retry = 0;
		TOP.Cookie.set('lockscreen', 0, {path: '/'});
	}
};

/**
 * 对话框
 * 整个框架页面有且只有一个实例，第一次调用时创建，关闭不会被销毁
 * 只处理对话框功能，如果需要窗口内操作，请另行实现
 */
Frame.Dialog = {
	/**
	 * @type {String}
	 */
	_el: $('<div class="pop_wrap" id="tudu-dialog"><div class="pop pop_linkman"><div class="pop_header"><strong></strong><a href="javascript:void(0);" class="icon icon_close close"></a></div><div class="pop_body"></div><div class="pop_footer"><div class="footer_left"></div><div class="footer_right"></div></div></div></div>'),
	
	/**
	 * @type {Object}
	 */
	_win: null,
	
	/**
	 * 设置
	 */
	setParam: function(params) {
		if (typeof(params) != 'object') {
			return ;
		}
		
		this._el.find('div.footer_right, div.footer_left').empty();
		
		for (var k in params) {
			switch (k) {
				case 'title':
					this._el.find('div.pop_header strong').text(params[k]);
					break;
				case 'body':
					this._el.find('div.pop_body').html(params[k]);
					break;
				case 'footer':
					this._el.find('div.footer_left').html(params[k]);
					break;
				case 'close':
					if (false === params[k]) {
						this._el.find('div.pop_header a.close').hide();
					} else {
						this._el.find('div.pop_header a.close').show();
					}
					break;
				case 'button':
				case 'buttons':
					var btns = params[k];
					for (var i = 0, c = btns.length; i < c; i++) {
						var btn = $('<button type="button"></button>');
						btn.text(btns[i].text);
						if (btns[i].cls) {
							btn.addClass(btns[i].cls);
						}
						
						if (btns[i].events) {
							for (var k in btns[i].events) {
								btn.bind(k, btns[i].events[k]);
							}
						}
						
						this._el.find('div.footer_right').append(btn);
					}
					break;
			}
		}
		
		if (undefined == params.buttons && undefined == params.footer) {
			this._el.find('div.pop_footer').hide();
		} else {
			this._el.find('div.pop_footer').show();
		}
	},
	
	/**
	 * 显示对话框
	 */
	show: function(params) {
		this.setParam(params);
		
		if (null == this._win) {
			this._win = this._el.window({
				width: 470,
				draggable: true
			});
		}
		
		this._win.show();
		
		return this;
	},
	
	/**
	 * 关闭
	 */
	close: function() {
		this._win.close();
	},
	
	/**
	 * 获取窗口元素引用
	 */
	getWin: function() {
		return this._win;
	}
};

/**
 * 高级搜索窗口
 */
Frame.SearchForm = {
	
	/**
	 * 元素
	 */
	_id: 'searchwin',
	
	/**
	 * 窗口
	 */
	_win: null,
	
	/**
	 * 匹配内容
	 */
	data: null,
	
	/**
	 * 主题分类
	 */
	_CLASSES: {},
	
	/**
	 * 显示窗口
	 */
	show: function() {
		var _o = this;
		
		if (null == _o._win) {
			_o._win = $('#' + _o._id).window({
				width: 530,
				draggable: true,
				onShow: function() {
					$('#searchwin div.pop_body').load('/frame/search', function(){
						_o._win.center();
						
						$('select[name="bid"]').change(function(){
							_o.loadClasses(this.value, 'select[name="classid"]');
						});
						
						$('#advsearch_form').submit(function(){
							var kw = $(this).find(':text[name="keyword"]').val();
							if (kw) {
								Frame.searchWord(kw);
							}
							_o.close();
						});
						
						var matchData = {};
						matchData.users = Cast.get('users');
						
						new $.autocomplete({
					        target: $('#inputfrom'),
					        data: matchData,
					        loadMethod: function() {
					            var _v = this,
					                keyword = $('#inputfrom').val();
					            Cast.load(function(){
					                Contact.load(function(){
										_v.data.users = Cast.get('users');
					                    _v._initMatchList(keyword);
					                })
					            });
					        },
					        columns: {users: ['truename', 'username', 'pinyin']},
					        width: 155,
					        arrowSupport: true,
					        template: {
					            users:'{truename} <span class="gray">&lt;{username}&gt;</span>'
					        },
					        onSelect: function(item){
					            $('#inputfrom').val(item.data.truename);
					        }
					    });

					    new $.autocomplete({
					        target: $('#inputto'),
					        data: matchData,
					        loadMethod: function() {
					            var _v = this,
					                keyword = $('#inputto').val();
					            Cast.load(function(){
					                Contact.load(function(){
										_v.data.users = Cast.get('users');
                                        _v._initMatchList(keyword);
					                })
					            });
					        },
					        columns: {users: ['truename', 'username', 'pinyin']},
					        width: 155,
					        arrowSupport: true,
					        template: {
					            users:'{truename} <span class="gray">&lt;{username}&gt;</span>'
					        },
					        onSelect: function(item){
					            $('#inputto').val(item.data.truename);
					        }
					    });
					});
					$('#searchform :text, #searchform select').val('');
				}
			});
		}
		
		_o._win.show();
	},
	
	/**
	 * 关闭
	 */
	close: function() {
		this._win.close();
	},
	
	loadClasses: function(bid, select) {
		var _o = this;
		
		if (!bid) {
			return _o._fillSelect([]);
		}
		
		if (typeof(_o._CLASSES[bid]) == 'undefined') {
			$.ajax({
				type: 'GET',
				dataType: 'json',
				url: '/tudu/classes?bid=' + encodeURIComponent(bid),
				success: function(ret) {
					if (ret.success) {
						_o._CLASSES[bid] = ret.data;
						_o._fillSelect(_o._CLASSES[bid], select);
					}
				},
				error: function(res) {
					return ;
				}
			});
		} else {
			_o._fillSelect(_o._CLASSES[bid], select);
		}
	},
		
	_fillSelect: function(ret, select) {
		var o = $(select),
			p = o.parent();
		o.find('option:not(:eq(0))').remove();

		if (null === ret || !ret.length) {
			p.attr('disabled', true);
			return o.attr('disabled', true);
		}
		
		p.attr('disabled', false);
		for (var i = 0, c = ret.length; i < c; i++) {
			o.append('<option value="'+ret[i].classid+'" title="'+ret[i].classname+'">'+ret[i].classname+'</option>');
		}
		
		o.attr('disabled', false);
	}
};

/**
 * 临时窗口，内容可由子框架页面添加
 * 关闭后会销毁
 */
Frame.TempWindow = {
	/**
	 * 
	 */
	_win: null,
	
	/**
	 * 
	 */
	_container: null,
	
	/**
	 * 添加窗口
	 */
	append: function(html, opts) {
		this.destroy();
	
		this._container  = $('<div>');
		
		this._container
		.addClass('pop_wrap')
		.css({position: 'absolute', top: '1px', left: '10px', background: '#ebf4d8'})
		.append(html);
		
		if (opts.id) {
			this._container.attr('id', opts.id);
			delete opts.id;
		}
		
		//this._win.appendTo('#win-ct');
		getJQ()(window.document.body).append(this._container);
		
		this._win = this._container.window(opts);
	},
	
	/**
	 * 显示窗口
	 */
	show: function() {
		this._win.show();
	},
	
	center: function() {
		this._win.center();
	},
	
	/**
	 * 关闭窗口
	 */
	close: function() {
		this._win.close();
	},
	
	/**
	 * 关闭窗口
	 */
	destroy: function() {
		if (null == this._win) {
			return ;
		}
		
		this._win.destroy();
		this._container.remove();
		
		this._container = null;
		this._win = null;
	},
	
	/**
	 * 在当前窗口中查找元素
	 */
	find: function(selector) {
		if (this._container) {
			return this._container.find(selector);
		}
	}
};

/**
 * 联系人选择控件
 * 
 * @param params
 * @return
 */
var ContactSelector = function(params) {
	this._panels = {};
	this._settings = {};
	this.setParam(params);
	
	this.jq = this._settings.jq ? this._settings.jq : $;
	this.init();
}

ContactSelector.defaultSettings = {
	maxSelect: 0,
	panels: ['lastcontact', 'common', 'contact'],
	enableGroup: true,
	appendTo: null,
	selected: null,
	switchModeTips: TOP.SWITCH_REVIEW_TYPE
};
ContactSelector.prototype = {

	/**
	 * 选择内容面板
	 */
	_panels: null,
	
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
	 * 
	 */
	jq:null,
	
	/**
	 * 初始化
	 */
	init: function() {
		var _o = this;
		
		this._container = _o.jq('<div>').addClass('contact_selector');
		
		this._ctLeft = _o.jq('<div>').addClass('contact_selector_left');
		this._ctRight = _o.jq('<div>').addClass('contact_selector_right');
		this._leftInner = _o.jq('<div>').addClass('selector_inner');
		this._rightInner = _o.jq('<div>').addClass('selector_inner');
		
		this._container
		.append(this._ctLeft)
		.append(_o.jq('<div>').addClass('contact_selector_center'))
		.append(this._ctRight);
		
		this._resultPanel = _o.jq('<div>').addClass('contact_selected');
		this._searchInput = _o.jq('<input type="text" class="input_text contact_search" id="contact_search" />');
		this._leftInner.append(
				_o.jq('<div>')
			.addClass('contact_input')
			.append(this._searchInput)
			.append(_o.jq('<a>').addClass('icon icon_search_2'))
		);
		this._ctRight.append(this._rightInner.append(this._resultPanel));
		
		this._ctGroups = _o.jq('<div>').addClass('contact_select_groups');
		this._ctSearchResult = _o.jq('<div>').addClass('contact_select_groups search_ct').hide();
		this._searchList = _o.jq('<div>').addClass('panel_body').appendTo(this._ctSearchResult);
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
		
		this._searchTree = new _o.jq.tree({
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
		
		this._settings = $.extend({}, ContactSelector.defaultSettings, params);
	},
	
	/**
	 * 搜索
	 */
	search: function(keyword) {
		var result = [],
			selectUsers  = {},
			_o = this,
			keyword = keyword.toLowerCase();
		this._searchTree.clear();
		
		if (this._castTree) {
			var users = Cast.get('users');
			var childOf = _o._settings.childOf;
			for (var i = 0, c = users.length; i < c; i++) {
				if (users[i].truename.toLowerCase().indexOf(keyword) >= 0 
					|| users[i].username.toLowerCase().indexOf(keyword) >= 0
					|| (users[i].pinyin && users[i].pinyin.indexOf(keyword) >= 0)) 
				{
					if (this._resultPanel.find('input[name^="email-"][value="'+users[i].username+'"]').size()) {
						continue ;
					}
					
					if (childOf) {
						var id = users[i].deptid;
						if (users[i].deptid && users[i].deptid.indexOf('_') != -1) {
							id = users[i].deptid.replace('_', '^');
						}
						if (childOf.indexOf(id) == -1) {
							continue;
						}
					}
					
					var node = new _o.jq.treenode({
						data: {
							id: 'u-' + users[i].userid,
							name: users[i].truename,
							email: users[i].username
						},
						isLeaf: true,
						events: {
							mouseover: function(){_o.jq(this).addClass('tree-node-over');},
							mouseout: function(){_o.jq(this).removeClass('tree-node-over');},
							click: function(e){
								_o.select(this.id.replace('search-', ''), _o._searchTree);
								stopEventBuddle(e);
							},
							dblclick: function(e) {
								stopEventBuddle(e);
							}
						}
					});
					
					this._searchTree.appendNode(node);
				}
			}
			
			if (_o._settings.enableGroup) {
				var groups = Cast.get('groups');
				for (var i = 0, c = groups.length; i < c; i++) {
					if (groups[i].groupname.toLowerCase().indexOf(keyword) >= 0) {
						if (this._resultPanel.find('input[name^="groupid-"][value="'+groups[i].groupid+'"]').size()) {
							continue ;
						}
						
						var node = new _o.jq.treenode({
							data: {
								id: 'g-' + groups[i].groupid,
								groupid: groups[i].groupid,
								groupname: groups[i].groupname,
								name: groups[i].groupname + '<span class="gray"><'+TOP.TEXT.GROUP+'></span>'
							},
							isLeaf: true,
							events: {
								mouseover: function(){_o.jq(this).addClass('tree-node-over');},
								mouseout: function(){_o.jq(this).removeClass('tree-node-over');},
								click: function(e){
									_o.select(this.id.replace('search-', ''), _o._searchTree);
									stopEventBuddle(e);
								},
								dblclick: function(e) {
									stopEventBuddle(e);
								}
							}
						});
						
						this._searchTree.appendNode(node);
					}
				}
			}
		}
		
		if (this._contactTree) {
			var contacts = Contact.get('contacts');
			for (var i = 0, c = contacts.length; i < c; i++) {
				if ((contacts[i].truename.toLowerCase().indexOf(keyword) >= 0
					|| (contacts[i].email && contacts[i].email.toLowerCase().indexOf(keyword) >= 0)
					|| (contacts[i].pinyin && contacts[i].pinyin.indexOf(keyword) >= 0))
					&& (!contacts[i].fromuser && !selectUsers[contacts[i].email]))
				{
					if (this._resultPanel.find('input[name^="contactid-"][value="'+contacts[i].contactid+'"]').size()) {
						continue ;
					}
					
					var node = new _o.jq.treenode({
						data: {
							id: 'u-' + contacts[i].contactid,
							contactid: contacts[i].contactid,
							name: contacts[i].truename,
							email: contacts[i].email
						},
						isLeaf: true,
						events: {
							mouseover: function(){_o.jq(this).addClass('tree-node-over');},
							mouseout: function(){_o.jq(this).removeClass('tree-node-over');},
							click: function(e){
								_o.select(this.id.replace('search-', ''), _o._searchTree);
								stopEventBuddle(e);
							},
							dblclick: function(e) {
								stopEventBuddle(e);
							}
						}
					});
					
					this._searchTree.appendNode(node);
				}
			}
		}
		
		this._ctGroups.hide();
		this._ctSearchResult.show();
	},
	
	/**
	 * 选择
	 */
	select: function(id, from) {
		var count = this.getSelectedCount(),
			_o = this;
		
		if (from) {
			var node = from.find(id, true);
			if (node == null) {
				return ;
			}
			node.hide();
		}
		
		var data = node.getData();
		
		if (data.email && this._resultPanel.find(':hidden[name^="email"][value="'+data.email+'"]').size()) {
			return ;
		}
		
		if (this._settings.maxCount > 0 && count >= this._settings.maxCount) {
			showMessage(formatString(TEXT.TO_USER_SELECT_MAX_COUNT, _o._settings.maxCount), 2000);
			return;
		}
		
		this._selectIndex ++;
		
		var a = _o.jq('<div>').attr('href', 'javascript:void(0);').addClass('contact_item');
		a.append('<input type="hidden" name="member[]" value="'+this._selectIndex+'" />');
		for (var k in data) {
			if (k == 'id') {
				continue ;
			}
			
			if (k == 'name') {
				if (this._settings.order) {
					a.append('<span class="contact_sort"><a class="icon_arrow arr_up" href="javascript:void(0);"></a><a class="icon_arrow arr_down" href="javascript:void(0);"></a></span>');
				}
				if (_o._settings.enableGroup && !data.email) {
					a.append('<span>' + data['groupname'] + '</span>');
				} else {
					a.append('<span>' + data[k] + '</span>');
				}
				
			}
			
			if (data[k]) {
				if (k == 'name' && _o._settings.enableGroup && !data.email) {
					a.append('<input type="hidden" name="'+k+'-'+this._selectIndex+'" value="'+data['groupname']+'" />');
				} else {
					a.append('<input type="hidden" name="'+k+'-'+this._selectIndex+'" value="'+data[k]+'" />');
				}
			}
		}
		
		if (_o._settings.enableGroup && !data.email) {
			a.append('<span class="gray"><'+TOP.TEXT.GROUP+'></span>');
		}
		
		if (data.email) {
			if (this._castTree) {
				var nodes = this._castTree.search({email: data.email});
				for (var i = 0, c = nodes.length; i < c; i++) {
					nodes[i].hide();
				}
			}
			
			if (this._contactTree) {
				var nodes = this._contactTree.search({email: data.email});
				for (var i = 0, c = nodes.length; i < c; i++) {
					nodes[i].hide();
				}
			}
			
			if (this._lastContactTree) {
				var nodes = this._lastContactTree.search({email: data.email});
				for (var i = 0, c = nodes.length; i < c; i++) {
					nodes[i].hide();
				}
			}
		}
		
		a.mousemove(function(){
            a.addClass("contact_item_hover");
        }).mouseout(function(){
            a.removeClass("contact_item_hover");
        });
		
		if (this._settings.order) {
			if (this._resultPanel.find('div.contact_item').size() > 0) {
				if (!this._nextSeparator) {
					if (this._settings.synchro) {
						this._nextSeparator = '+';
					} else {
						this._nextSeparator = '>';
					}
				}

				var next = _o.jq('<div>').addClass('contact_next');
				var icon = $('<a href="javascript:void(0);" class="icon '+(this._nextSeparator == '>' ? 'icon_arr_next' : 'icon_arr_plus')+'" title="' + this._settings.switchModeTips + '"></a>');
				icon.bind('click', function(){
					if (typeof _o._settings.accepter != 'undefined' && _o._settings.accepter.length > 0) {
						TOP.showMessage(TOP.TEXT.TUDU_GROUP_NOT_SYNCHRO);
						return ;
					}
					var o = $(this);
					if (o.hasClass('icon_arr_next')) {
						o.removeClass('icon_arr_next').addClass('icon_arr_plus');
						o.parent('div').find('input[name="separator"]').val('+');
					} else {
						o.removeClass('icon_arr_plus').addClass('icon_arr_next');
						o.parent('div').find('input[name="separator"]').val('>');
					}
				});
				next.append('<input type="hidden" name="separator" value="'+this._nextSeparator+'" />');
				next.append(icon);
				this._resultPanel.append(next);
				
				if (this._settings.synchro) {
					this._nextSeparator = '+';
				} else {
					this._nextSeparator = '>';
				}
			}
			
			a.find('a.arr_up')
			.bind('mouseover', function(e){
				stopEventBuddle(e);
				$(this).addClass('arr_up_hover');
			})
			.bind('mouseout', function(e){
				stopEventBuddle(e);
				$(this).removeClass('arr_up_hover');
			})
			.bind('click', function(e){
				if (_o.jq(this).hasClass('arr_up_disabled')) {
					stopEventBuddle(e);
					return ;
				}
				var b = _o.jq(this).parent().parent();
				b.insertBefore(b.prevAll('div.contact_item:first'));
				_o.tidyOrderClass();
				_o.appendArrow();
				stopEventBuddle(e);
			});
			
			a.find('a.arr_down')
			.bind('mouseover', function(e){
				stopEventBuddle(e);
				$(this).addClass('arr_down_hover');
			})
			.bind('mouseout', function(e){
				stopEventBuddle(e);
				$(this).removeClass('arr_down_hover');
			})
			.bind('click', function(e){
				if (_o.jq(this).hasClass('arr_down_disabled')) {
					stopEventBuddle(e);
					return ;
				}
				var b = _o.jq(this).parent().parent();
				b.insertAfter(b.nextAll('div.contact_item:first'));
				_o.tidyOrderClass();
				_o.appendArrow();
				stopEventBuddle(e);
			});
		}
		
		this._resultPanel.append(a);
		
		_o.tidyOrderClass();
		
		a.bind('click', function(){
			var remove = true;
			
			if (typeof _o._settings.accepter != 'undefined' && _o._settings.accepter.length > 0) {
				var ids = [];
				for (var k = 0; k < _o._settings.accepter.length; k++) {
					ids.push(_o._settings.accepter[k].username);
				}
				
				if (typeof data.email != 'undefined' && TOP.Util.inArray(data.email, ids)) {
					TOP.showMessage(TOP.TEXT.TUDU_DIVIDE_ACCEPTER_TIPS);
					remove = false;
				}
			}
			
			if (remove) {
				if (_o._settings.order) {
					if (!_o.jq(this).next('div.contact_next').size()) {
						_o.jq(this).prev('div.contact_next').remove();
					} else {
						_o.jq(this).next('div.contact_next').remove();
					}
				}
				
				_o.jq(this).remove();
				_o.unselect(data);
				
				_o.tidyOrderClass();
			}
		});
		
		
	},
	
	/**
	 * 排序箭头样式
	 */
	tidyOrderClass: function() {
		if (!this._settings.order) {return ;}
		this._resultPanel.find('a.arr_up').removeClass('arr_up_disabled');
		this._resultPanel.find('a.arr_down').removeClass('arr_down_disabled');
		
		this._resultPanel.find('a.arr_up:first').addClass('arr_up_disabled');
		this._resultPanel.find('a.arr_down:last').addClass('arr_down_disabled');
	},
	
	/**
	 * 向下的前头
	 */
	appendArrow: function() {
		var _o = this;
		this._resultPanel.find('div.contact_next').each(function(){
			_o.jq(this).remove();
		});
		if (this._settings.synchro) {
			_o._resultPanel.find('div.contact_item:not(div.contact_item:last)').after('<div class="contact_next"><input type="hidden" value="+" name="separator"><a class="icon icon_arr_plus" href="javascript:void(0);"></a></div>');
		} else {
			_o._resultPanel.find('div.contact_item:not(div.contact_item:last)').after('<div class="contact_next"><input type="hidden" value=">" name="separator"><a class="icon icon_arr_next" href="javascript:void(0);"></a></div>');
		}
		this._resultPanel.find('div.contact_next').each(function(){
			var o = $(this).find('a.icon_arr_next, a.icon_arr_plus');
			o.bind('click', function(){
	            if (o.hasClass('icon_arr_next')) {
	                o.removeClass('icon_arr_next').addClass('icon_arr_plus');
	                o.parent('div').find('input[name="separator"]').val('+');
	            } else {
	                o.removeClass('icon_arr_plus').addClass('icon_arr_next');
	                o.parent('div').find('input[name="separator"]').val('>');
	            }
			});
		});
	},
	
	/**
	 * 取消选中
	 */
	unselect: function(item) {
		var minput = null;
		if (this._settings.mailInput) {
			minput = this._settings.mailInput;
		}
		
		if (item.groupid && this._groupTree) {
			var node = this._groupTree.find(item.groupid.replace('^', '_'), true);
			if (node) {
				node.show();
				
				if (minput) {
					var data = node.getData();
					var items = minput.getItems('[_id="'+data.groupid+'"][name="'+data.name+'"]');
					minput.removeItem(items);
				}
			}
		}
		
		if (item.groupid && this._castGroups) {
			var node = this._castGroups.find('g-' + item.groupid.replace('^', '_'), true);
			if (node) {
				node.show();
				
				if (minput) {
					var data = node.getData();
					var items = minput.getItems('[_id="'+data.groupid+'"][name="'+data.name+'"]');
					minput.removeItem(items);
				}
			}
		}
		
		if (item.userid && this._castTree) {
			var node = this._castTree.find('u-' + item.userid, true);
			
			if (node) {
				node.show();
			}
			
			if (this._contactTree) {
				var nodes = this._contactTree.search({email: item.email, fromuser: true});
				for (var i = 0, c = nodes.length; i < c; i++) {
					nodes[i].show();
				}
			}
			
			if (this._lastContactTree) {
				var nodes = this._lastContactTree.search({email: item.email});
				for (var i = 0, c = nodes.length; i < c; i++) {
					nodes[i].show();
				}
			}
			
			if (minput) {
				var data = node.getData();
				var items = minput.getItems('[_id="'+data.email+'"][name="'+data.name+'"]');
				minput.removeItem(items);
			}
		}
		
		if (item.contactid && this._contactTree) {
			var nodes = this._contactTree.search({contactid: item.contactid});
			for (var i = 0, c = nodes.length; i < c; i++) {
				nodes[i].show();
			}
			
			if (item.fromuser && this._castTree) {
				var unodes = this._castTree.search({email: item.email});
				for (var i = 0, c = unodes.length; i < c; i++) {
					unodes[i].show();
				}
			}
			
			if (this._lastContactTree) {
				var nnodes = this._lastContactTree.search({email: item.email, fromuser: item.fromuser});
				for (var i = 0, c = nnodes.length; i < c; i++) {
					nnodes[i].show();
				}
			}
			
			if (minput && nodes.length) {
				var data = nodes[0].getData();
					email = data.email;
				var items = minput.getItems('[_id="'+data.email+'"][name="'+data.name+'"]');
				minput.removeItem(items);
			}
		}
		
		var keyword = this._searchInput.val().replace(/^\s+|\s+$/g, '');
		if (keyword) {
			this.search(keyword, false);
		}
	},
	
	initSelected: function() {
		if (!this._settings.selected) {
			return ;
		}
		
		var _o = this;
		Cast.load(function(){
		Contact.load(function(){
			var se = _o._settings.selected;
			selected:
			for (var i = 0, c = se.length; i < c; i++) {
				
				if (se[i].separator) {
					_o._nextSeparator = se[i].separator;
					continue ;
				}

				if (se[i].contactid && _o._contactTree) {
					var node = _o._contactTree.search({contactid: se[i].contactid}),
						from = _o._contactTree;
					if (!node.length) {
						node = _o._lastContactTree.search({contactid: se[i].contactid});
						from = _o._lastContactTree;
					}
					if (node.length) {
						_o.select(node[0].get('id'), from);
					}
					continue ;
				}
				
				if (se[i]._id && -1 != se[i]._id.indexOf('@')) {
					_o.select('u-' + se[i]._id.split('@')[0], _o._castTree);
					if (_o._contactTree) {
						var nodes = _o._contactTree.search({email: se[i]._id, name: se[i].name});
						for (var k = 0, l = nodes.length; k < l; k++) {
							_o.select(nodes[k].get('id'), _o._contactTree);
							continue selected;
						}
					}
					
					if (_o._castTree) {
						var nodes = _o._castTree.search({email: se[i]._id, name: se[i].name});
						for (var k = 0, l = nodes.length; k < l; k++) {
							_o.select(nodes[k].get('id'), _o._castTree);
						}
					}
				} else if (!se[i]._id) {
					if (_o._contactTree) {
						var nodes = _o._contactTree.search({name: se[i].name});
						for (var k = 0, l = nodes.length; k < l; k++) {
							_o.select(nodes[k].getData('contactid'), _o._contactTree)
						}
					}
					
					if (_o._lastContactTree) {
						var nodes = _o._lastContactTree.search({name: se[i].name});
						for (var k = 0, l = nodes.length; k < l; k++) {
							if (nodes[k].getData('name') == se[i].name) {
								_o.select('u-' + nodes[k].getData('contactid'), _o._lastContactTree);
							}
						}
					}
				} else if (_o._settings.enableGroup) {
					if (_o._castGroups) {
						_o.select('g-' + se[i]._id.replace('^', '_'), _o._castGroups);
					}
					if (_o._groupTree) {
						_o.select(se[i]._id, _o._groupTree);
					}
				}
			}
		});
		});
	},
	
	/**
	 * 初始化面版
	 */
	initPanel: function(key) {
		var _o = this;
		var panel      = _o.jq('<div>').addClass('group_panel');
		var paneltitle = _o.jq('<div>').addClass('panel_title');
		var panelbody  = _o.jq('<div>').addClass('panel_body');
		
		paneltitle.bind('click', function(){
			if (_o._settings.panels.length > 1) {
				_o.switchPanel(key);
			}
		});
		
		panel.append(paneltitle);
		panel.append(panelbody);
		
		panelbody.css('height', 220 + (3 - this._settings.panels.length) * 26 + 'px');
		
		switch (key) {
			case 'lastcontact':
				paneltitle.text(TEXT.LAST_CONTACT);
				this.initLastContactList(panelbody);
				break;
			case 'common':
				paneltitle.text(TEXT.COMMON_CONTACT);
				this.initCastList(panelbody);
				break;
			case 'contact':
				paneltitle.text(TEXT.PRIVATE_CONTACT);
				this.initContactList(panelbody);
				break;
		}
		
		return panel;
	},
	
	initLastContactList: function(body) {
		var _o = this,
		    selectbox   = _o.jq('<div>').addClass('select_box list_select_box');
		
		_o._lastContactTree = new _o.jq.tree({
			id: 'last-contact-tree',
			idKey: 'id',
			idPrefix: 'lc-',
			cls: 'cast-tree',
			template: '{name}'
		});
		
		_o._lastContactTree.appendTo(selectbox);
		
		Contact.load(function(){
			var contact = Contact.get('lastcontact');
			
			for (var i = 0, c = contact.length; i < c; i++) {
				var node = new _o.jq.treenode({
					data: {
						id: 'u-' + contact[i].contactid,
						contactid: contact[i].contactid,
						name: contact[i].truename,
						email: contact[i].email
					},
					isLeaf: true,
					events: {
						mouseover: function(){_o.jq(this).addClass('tree-node-over');},
						mouseout: function(){_o.jq(this).removeClass('tree-node-over');},
						click: function(e){
							_o.select(this.id.replace('lc-', ''), _o._lastContactTree);
							stopEventBuddle(e);
						}
					}
				});
				
				_o._lastContactTree.appendNode(node);
			}
			
			body.append(selectbox);
		});
	},
	
	initCastList: function(body) {
		var _o = this,
			selectbox = _o.jq('<div>').addClass('select_box');
		Cast.load(function(){
			var users = Cast.get('users'),
				depts = Cast.get('depts'),
				groups = Cast.get('groups');
			
			// 组织构架树
			_o._castTree = new _o.jq.tree({
				id: 'cast-tree',
				idKey: 'id',
				idPrefix: 'cast-',
				cls: 'cast-tree',
				template: '{name}'
			});
			
			_o._castTree.appendTo(selectbox);
			
			var childOf = _o._settings.childOf;
			for (var i = 0, c = depts.length; i < c; i++) {
				if (childOf) {
					var id = depts[i].deptid;
					if (depts[i].deptid && depts[i].deptid.indexOf('_') != -1) {
						id = depts[i].deptid.replace('_', '^');
					}
					if (childOf.indexOf(id) == -1) {
						continue;
					}
				}
				
				if (depts[i].deptid == '^root') {
					depts[i].deptname = TOP._ORGNAME;
				}
				if (depts[i].deptid && depts[i].deptid.indexOf('^') != -1) {
					depts[i].deptid = depts[i].deptid.replace('^', '_');
				}
				if (depts[i].parentid && depts[i].parentid.indexOf('^') != -1) {
					depts[i].parentid = depts[i].parentid.replace('^', '_');
				}
				var node = new _o.jq.treenode({
						data: {
						id: 'd-' + depts[i].deptid,
						name: depts[i].deptname
					},
					events: {
						dblclick: function(e){
							var node = _o._castTree.find(this.id.replace('cast-', ''), true);
							if (node) {
								var children = node.getChildren(true);
								for (var i = 0, c = children.length; i < c; i++) {
									if (children[i].isLeaf) {
										_o.select(children[i].get('id'), _o._castTree);
									}
								}
							}
							stopEventBuddle(e);
						},
						click: function(e){_o._castTree.find(this.id.replace('cast-', ''), true).toggle();stopEventBuddle(e);}
					}
				});
				
				if (!depts[i].parentid || !_o._castTree.find('d-' + depts[i].parentid, true)) {
					_o._castTree.appendNode(node);
				} else {
					_o._castTree.find('d-' + depts[i].parentid, true).appendChild(node);
				}
			}
			
			for (var i = 0, c = users.length; i < c; i++) {
				var node = new _o.jq.treenode({
						data: {
						id: 'u-' + users[i].userid,
						userid: users[i].userid,
						name: users[i].truename,
						email: users[i].username
					},
					isLeaf: true,
					events: {
						mouseover: function(){_o.jq(this).addClass('tree-node-over');},
						mouseout: function(){_o.jq(this).removeClass('tree-node-over');},
						click: function(e){
							_o.select(this.id.replace('cast-', ''), _o._castTree);
							stopEventBuddle(e);
						}
					}
				});
				
				var deptid = users[i].deptid ? users[i].deptid : '_root';
				var dept = _o._castTree.find('d-' + deptid, true);
				if (dept) {
					dept.appendChild(node);
				} else {
					if (!childOf)
						_o._castTree.appendNode(node);
				}
			}
			
			var root = _o._castTree.find('d-_root', true);
			if (root) {
				root.expand();
			}
			if (_o._settings.enableGroup) {
				// 组织构架树
				_o._castGroups = new _o.jq.tree({
					id: 'cast-group-tree',
					idKey: 'id',
					idPrefix: 'cg-',
					cls: 'cast-tree',
					template: '{name}'
				});
				
				_o._castGroups.appendTo(selectbox);
				
				var groupTopnode = new _o.jq.treenode({
					data: {id: 'group-top', name: TEXT.CONTACT_GROUP},
					events: {
						click: function(e){_o._castGroups.find(this.id.replace('cg-', ''), true).toggle();stopEventBuddle(e);}
					}
				});
				_o._castGroups.appendNode(groupTopnode);
				groupTopnode.expand();
				
				for (var i = 0, c = groups.length; i < c; i++) {
					var node = new _o.jq.treenode({
							data: {
							id: 'g-' + groups[i].groupid.replace('^', '_'),
							groupid: groups[i].groupid,
							groupname: groups[i].groupname,
							name: groups[i].groupname
						},
						isLeaf: true,
						events: {
							mouseover: function(){_o.jq(this).addClass('tree-node-over');},
							mouseout: function(){_o.jq(this).removeClass('tree-node-over');},
							click: function(e){
								_o.select(this.id.replace('cg-', ''), _o._castGroups);
								stopEventBuddle(e);
							}
						}
					});
					
					_o._castGroups.find('group-top').appendChild(node);
				}
			}
			
			body.append(selectbox);
		});
	},
	
	initContactList: function(body) {
		var _o = this,
			selectbox = _o.jq('<div>').addClass('select_box');
		Contact.load(function(){
			var contacts = Contact.get('contacts'),
				groups   = Contact.get('groups');
			
			// 联系人树
			_o._contactTree = new _o.jq.tree({
				id: 'contact-tree',
				idKey: 'id',
				idPrefix: 'contact-',
				cls: 'cast-tree',
				template: '{name}'
			});
			
			_o._contactTree.appendTo(selectbox);
			
			var topnode = new _o.jq.treenode({
				data: {id: 'top', name: TEXT.CONTACT},
				events: {
					click: function(e){_o._contactTree.find('top', true).toggle();stopEventBuddle(e);}
				}
			});
			
			_o._contactTree.appendNode(topnode);
			topnode.expand();
			
			if (_o._settings.enableGroup) {
				_o._groupTree = new _o.jq.tree({
					id: 'contact-group-tree',
					idKey: 'id',
					idPrefix: 'contact-group-',
					cls: 'cast-tree',
					template: '{name}'
				});
				
				_o._groupTree.appendTo(selectbox);
				
				var groupTopnode = new _o.jq.treenode({
					data: {id: 'top', name: TEXT.CONTACT_GROUP},
					events: {
						click: function(e){_o._groupTree.find('top', true).toggle();stopEventBuddle(e);}
					}
				});
				
				_o._groupTree.appendNode(groupTopnode);
				groupTopnode.expand();
			}
			
			var cnode = new _o.jq.treenode({
				data: {
					id: '_d',
					name: TEXT.NO_GROUP_CONTACT
				},
				events: {
					click: function(e){_o._contactTree.find('_d', true).toggle();stopEventBuddle(e);}
				}
			});
			_o._contactTree.find('top').appendChild(cnode);
			
			// 添加联系组
			var gid;
			for (var i = 0, c = groups.length; i < c; i++) {
				gid = groups[i].groupid;
				if (gid  == '^n') {
					continue ;
				}
				
				var groupid = gid.replace('^', '_');
				var cnode = new _o.jq.treenode({
					data: {
						id: groupid,
						groupname: groups[i].groupname,
						name: groups[i].groupname
					},
					events: {
						dblclick: function(e){
							var node = _o._contactTree.find(this.id.replace('contact-', ''), true);
							if (node) {
								var children = node.getChildren(true);
								for (var i = 0, c = children.length; i < c; i++) {
									if (children[i].isLeaf) {
										_o.select(children[i].get('id'), _o._contactTree);
									}
								}
							}
							stopEventBuddle(e);
						},
						click: function(e){_o._contactTree.find(this.id.replace('contact-', ''), true).toggle();stopEventBuddle(e);}
					}
				});
				_o._contactTree.find('top').appendChild(cnode);
				
				if (_o._settings.enableGroup) {
					var gnode = new _o.jq.treenode({
						data: {
							id: groupid,
							groupid: groups[i].groupid,
							groupname: groups[i].groupname,
							name: groups[i].groupname
						},
						isLeaf: true,
						events: {
							mouseover: function(){_o.jq(this).addClass('tree-node-over');},
							mouseout: function(){_o.jq(this).removeClass('tree-node-over');},
							click: function(e){_o.select(this.id.replace('contact-group-', ''), _o._groupTree);stopEventBuddle(e);}
						}
					});
					
					_o._groupTree.find('top').appendChild(gnode);
				}
			}
			
			for (var i = 0, c = contacts.length; i < c; i++) {
				if (!contacts[i].groups || !contacts[i].groups.length) {
					contacts[i].groups = ['_d'];
				}
				
				for (var k = 0, l = contacts[i].groups.length; k < l; k++) {
					
					var groupid = contacts[i].groups[k].replace('^', '_');
					var node = new _o.jq.treenode({
						data: {
							id: groupid + '-' + contacts[i].contactid,
							contactid: contacts[i].contactid,
							name: contacts[i].truename,
							email: contacts[i].email,
							fromuser: contacts[i].fromuser
						},
						isLeaf: true,
						events: {
							mouseover: function(){_o.jq(this).addClass('tree-node-over');},
							mouseout: function(){_o.jq(this).removeClass('tree-node-over');},
							click: function(e){_o.select(this.id.replace('contact-', ''), _o._contactTree);stopEventBuddle(e);}
						}
					});
					
					var g = _o._contactTree.find(groupid, true);
					
					if (g != null) {
						g.appendChild(node)
					}
				}
			}
			
			body.append(selectbox);
		});
	},
	
	/**
	 * 切换选择面板
	 */
	switchPanel: function(key) {
		if (this._panels[key] == undefined) {
			return ;
		}
		
		for (var k in this._panels) {
			this._panels[k].find('.panel_body').hide();
		}
		
		Cookie.set('CONTACT-PANEL', key);
		
		this._panels[key].find('.panel_body').show();
	},
	
	/**
	 * 获取选中项目
	 */
	getSelected: function() {
		var _o = this;
		var ret = [];
		this._resultPanel.find('div').each(function(){
			var a = _o.jq(this);
			
			if (a.hasClass('contact_item')) {
				var o = {name: a.find('input[name^="name-"]').val()};
				
				if (a.find('input[name^="email-"]').size()) {
					o.email = a.find('input[name^="email-"]').val();
				}
				
				if (a.find('input[name^="contactid-"]').size()) {
					o.contactid = a.find('input[name^="contactid-"]').val();
				}
				
				if (a.find('input[name^="groupid-"]').size()) {
					o.groupid = a.find('input[name^="groupid-"]').val();
				}
			} else {
				var o = {separator: a.find('input[name="separator"]').val()};
			}
			
			ret.push(o);
		});
		
		return ret;
	},
	
	getSelectedCount: function() {
		return this._resultPanel.find('div.contact_item').size();
	}
};

/**
 * 
 * @param params
 * @return
 */
var ContactInput = function(params) {
	this.setParam(params);
	this.init();
};

ContactInput.prototype = {
	
	/**
	 * 输入框实例
	 */
	_input: null,

	/**
	 * 设置项
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
		
		if (null == this._settings) {
			this._settings = {
				group: true,
				org: true,
				user: true,
				contact: true,
				valueItems: ['_id', 'name']
			};
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
		
		var matchData = {};
		if (this._settings.user) {
			matchData.user = Cast.get('users');
		}
		if (this._settings.contact) {
			matchData.contact = Contact.get('contacts');
		}
		if (this._settings.group) {
			matchData.group = Cast.get('groups');
		}
		if (this._settings.group && this._settings.contact) {
			matchData.contactgroup = Contact.get('groups');
		}
		
		var _o = this;
		var params = {
			id: _o._settings.id,
	        target: _o._settings.target,
	        maxCount: _o._settings.maxCount ? _o._settings.maxCount : 0,
	        onAppend: function(item){
				_o.checkValue.call(_o, item);
	        },
			onBefore: function() {
				if (typeof _o._settings.onBefore == 'function') {
                    _o._settings.onBefore.call(_o);
                }
				if (_o._settings.divide) {
					return true;
				}
				return false;
			},
	        onUpdate: function() {
	        	_o.updateInput();
	        },
	        onRemove: function(){
	        	var items = _o.getItems(),
	        		inputUser = [];
	        	items.each(function (){
	        		inputUser.push($(this).attr('_id'));
				});
	        	if (typeof(_o._settings.accepter) != 'undefined') {
	    			var newAcept = inputUser.join(',');
	    			for (var i=0; i<_o._settings.accepter.length; i++) {
	    				if (newAcept.indexOf(_o._settings.accepter[i].username) == -1) {
	    					_o.addItem(_o._settings.accepter[i].truename, {name: _o._settings.accepter[i].truename, _id: _o._settings.accepter[i].username, title: _o._settings.accepter[i].truename});
	    					TOP.showMessage(TOP.TEXT.TUDU_DIVIDE_ACCEPTER_TIPS);
	    				}
	    			}
	    		}
	        	
	        	_o.updateInput();
	        },
	        autoComplete:{
				data: matchData,
		        loadMethod: function() {
	        		var _v = this,
	        			keyword = _o._input.getText();
	        		Cast.load(function(){
	        		Contact.load(function(){
	        			if (_o._settings.depts) {
	        				var users = Cast.get('users'),
	        					userArr = [],
	        					depts   = _o._settings.depts;
	        				for (var i = 0, c = users.length; i < c; i++) {
	        					if (Util.inArray(users[i].deptid, depts)) {
	        						userArr.push(users[i]);
	        					}
	        				}
	        				_v.data.user = userArr;
	        			} else {
	        				_v.data.user = Cast.get('users');
	        			}
	        			
	        			if (_o._settings.contact) {
	        				_v.data.contact = [];
	        				var contacts = Contact.get('contacts');
	        				for (var i = 0, c = contacts.length; i < c; i++) {
	        					if (!contacts[i].fromuser) {
	        						_v.data.contact.push(contacts[i]);
	        					}
	        				}
	        				if (_o._settings.group) {
			        			_v.data.contacgroup = Contact.get('groups');
		        			}
	        			}
	        			
	        			if (_o._settings.group) {
		        			_v.data.group = Cast.get('groups');
	        			}
						_v._initMatchList(keyword);
	        		});
	        		});
	        	},
		        columns: {
	        		user: ['truename', 'username', 'pinyin'],
	        		group: ['groupname'],
	        		contact: ['truename', 'email', 'pinyin'], 
	        		contactgroup: ['groupname']
	            },
		        width: 280,
		        template: {
		        	user:'{truename} <span class="gray">&lt;{username}&gt;</span>',
		        	group: '{groupname}  <span class="gray">&lt;' + TEXT.GROUP + '&gt;</span>',
		        	contact:'{truename} <span class="gray">&lt;{email}&gt;</span>',
		        	contactgroup: '{groupname}  <span class="gray">&lt;' + TEXT.GROUP + '&gt;</span>'
		        },
		        onSelect: function(item){
	                var data = item.data;
	                _o._input.setText('');
	                
	                var name = data.truename ? data.truename : data.groupname;
	                var id   = data.username ? data.username : (data.email ? data.email : data.groupid);
	                var title= isEmail(id) ? id : TEXT.GROUP + ':' + id;
	                
	                _o._input.appendItem(name, {name: name, _id: id, title: title});
	                _o._input.focus();
		        }
			}
	    };
		
		if (this._settings.jq) {
			params.jq = this._settings.jq;
		}
		
		_o._input = new $.mailinput(params);
		
		if (_o._settings.valuePlace.size()) {
			var v = this._settings.valuePlace.val();
			if (v) {
				var arr = v.split("\n");
				for (var i = 0, c = arr.length; i < c; i++) {
					var item = arr[i].split(' ');
					
					if (item.length < 2) {
						if (item[0].indexOf('>') == 0 || item[0].indexOf('+') == 0) {
							_o.addItem(item.join(' '), {separator: item[0]});
						}
						continue ;
					}
					
					var id = item.shift(),
						name = item.join(' ');
					_o.addItem(name, {name: name, _id: id, title: name});
				}
			}
		}
	},
	
	/**
	 * 检查输入内容
	 */
	checkValue: function(item) {
		if (!this._input) {
			return ;
		}
		
		var _o = this;
		Cast.load(function(){
		Contact.load(function(){
			var contact = {}, groups = {};
			var text = item.text().replace(/[;,]/g, '').replace(/^\s+|\s+$/g, ''),
				identify = item.attr('_id');

			var matched = false;
			
			identify = identify ? identify.replace(/^[%|#]/, '') : '';
			
			if (_o._settings.user) {
				if (_o._settings.depts) {
					var users = Cast.get('users'), userArr = [], depts = _o._settings.depts;
					for (var i = 0, c = users.length; i < c; i++) {
						if (Util.inArray(users[i].deptid, depts)) {
							userArr.push(users[i]);
						}
					}
					contact.user = userArr;
				} else {
					contact.user = Cast.get('users');
				}
			}
			
			if (_o._settings.contact) {
				contact.contact = Contact.get('contacts');
			}
			
			if (_o._settings.group) {
				groups.groups = Cast.get('groups');
			}
			
			if (_o._settings.group && _o._settings.group) {
				groups.contactgroups = Contact.get('groups');
			}
			
			var attr = {
				_id : identify,
				name: text,
				text: text + (identify ? '<span class="gray">&lt' + identify + '&gt;</span>' : ''),
				title: text + (identify ? '<' + identify + '>' : '')
			};

			var isValid = true;

			// 检查联系人
			match:
			do {
				if (!identify || -1 !== identify.indexOf('@')) {

					if ((identify && item.parent().find('.mail_item[name="'+text+'"][_id="'+identify+'"]').size() > 1)
						|| (!identify && item.parent().find('.mail_item[name="'+text+'"]').size() > 1)) {
		        		item.remove();
		        		return ;
		        	}
					
					for (var k in contact) {
						var u = contact[k];
						for (var i = 0, c = u.length; i < c; i++) {

							if ((!identify && (text == u[i].truename || text == (k == 'user' ? u[i].username : u[i].email)))
								|| (identify == (k == 'user' ? u[i].username : u[i].email)))
							{
								attr = {
									name: u[i].truename, 
									_id: (k == 'user' ? u[i].username : u[i].email ? u[i].email : '')
								};
								attr.title = attr.name + (attr._id && attr._id ? '<' + attr._id + '>' : '');
								attr.text  = attr.name + (attr._id && attr._id ? '<span class="gray">&lt;' + attr._id + '&gt;</span>' : '');
								
								matched = true;
								break match;
							}
						}
					}
					
					if(!matched) {
						if (-1 === text.indexOf('@')) {
							isValid = false;
						}
						
						if(-1 !== text.indexOf('@') || -1 !== identify.indexOf('@')) {
							var domain = isEmail(text) ? text.split('@')[1] : identify.split('@')[1];
							
							if(/^[\w-]+(\.tudu\.com+)+$/.test(domain)) {
								isValid = false;
							}
						}
					}
				}
				
				// 检查群组
				if (identify && -1 === identify.indexOf('@')) {

					if (item.parent().find('.mail_item[name="'+text+'"][_id="'+identify+'"]').size() > 1) {
		        		item.remove();
		        		return ;
		        	}

					for (var k in groups) {
						var g = groups[k];
						for (var i = 0, c = g.length; i < c; i++) {
							if (identify == g[i].groupid) {
								attr = {
									name: g[i].groupname,
									_id: identify
								};
								attr.title = attr.name + '<' + TEXT.GROUP + '>';
								attr.text  = Util.encodeHTML(attr.name) + '<span class="gray">&lt;' + TEXT.GROUP + '&gt;</span>';
								
								matched = true;
								break match;
							}
						}
					}
				}
				
			} while (false);
			
			if (!matched && !_o._settings.contact) {
				isValid = false;
			}
			
			if (!matched && !identify && isValid) {
	        	if (isEmail(text)) {
	        		attr['_id'] = text;
	        		attr['name'] = text.split('@')[0];
	        		attr['text'] = attr['name'] + (attr['_id'] ? '<span class="gray">&lt;' + text + '&gt;</span>' : '')
	        		attr['title'] = attr['name'] + (attr['_id'] ? '<' + attr['_id'] + '>' : '');
	        	} else {
	        		attr['_id'] = '';
	        		attr['name'] = text;
	        		attr['text'] = text;
	        		attr['title'] = attr['name'];
	        	}
			}

			if (!isValid) {
				attr['name']  = '';
				attr['text']  = identify ? identify : text;
				attr['_id']   = '';
				attr['title'] = TEXT.INVALID_TO_USER;
				item.addClass('red');
			}
			

			var hint = _o._settings.type == 'to' ? TEXT.SWITCH_EXECUTE_TYPE : TEXT.SWITCH_REVIEW_TYPE;

            if (_o._settings.review) {
                if (_o._settings.synchro) {
                    if (item.prev(':not(.mail_item_separator)').size()) {
                        _o._input.appendSeparator('+', item, hint);
                    }
                    
                    if (item.next('div').next('.mail_item').size()) {
                        _o._input.appendSeparator('+', item.next('.mail_item'), hint);
                    }
                } else {
                    if (item.prev(':not(.mail_item_separator)').size()) {
                        _o._input.appendSeparator('>', item, hint);
                    }
                    
                    if (item.next('div').next('.mail_item').size()) {
                        _o._input.appendSeparator('>', item.next('.mail_item'), hint);
                    }
                }
            }

			item.html(attr.text + ';');
			
			delete attr.text;
			item.attr(attr);
			
			_o.updateInput();
		});
		});
	},
	
	/**
	 * this._settings.accepter
	 */
	updateInput: function() {
		var me = this;
		if (!this._settings.valuePlace || !this._input) {
			return ;
		}
		
		var vi = this._settings.valuePlace,
			ti = this._settings.textPlace ? this._settings.textPlace : this._settings.target,
			v = [], t = [];
		
		this.getItems().each(function(){
			var item = $(this);
			
			if (item.hasClass('mail_item')) {
				var id = item.attr('_id') ? item.attr('_id') : '';
				var val = [];
				
				for (var i = 0, c = me._settings.valueItems.length; i < c; i++) {
					val.push(item.attr(me._settings.valueItems[i]));
				}
				
				v.push(val.join(' '));
				t.push([item.attr('name')].join(' '));
			} else if (item.hasClass('mail_item_separator')) {
				var separator = item.attr('_separator');
				v.push(separator);
				t.push(separator);
			}
		});
		
		vi.val(v.join("\n"));
		ti.val(t.join(','));
		
		if (typeof this._settings.onUpdate == 'function') {
			this._settings.onUpdate.call(this);
		}
	},
	
	addItem: function(item, params, callback) {
		if (!this._input) {
			return ;
		}
		
		if (params.separator) {
			return this._input.appendSeparator(item);
		}
		
		return this._input.appendItem(item, params, callback);
	},
	
	/**
	 * 获取已输入项目列表
	 */
	getItems: function(filter) {
		if (!this._input) {
			return ;
		}
		return this._input.getItems(filter);
	},
	
	/**
	 * 移除项目
	 */
	removeItem: function(item) {
		if (this._input) {
			this._input.removeItem(item);
		}
		this.updateInput();
	},
	
	/**
	 * 清空输入框
	 */
	clear: function() {
		if (!this._input) {
			return ;
		}
		this._input.clear();
		if (this._settings.valuePlace) {
			this._settings.valuePlace.val('');
		}
		if (this._settings.textPlace) {
			this._settings.textPlace.val('');
		}
	},
	
	/**
	 * 获取输入项目数量
	 */
	getCount: function() {
		return this.getItems().size();
	},
	
	disabled: function() {
		return this._input.disabled();
	},
	
	enabled: function() {
		return this._input.enabled();
	}
};

var NDUpload = {
	
	wintpl: '<div class="pop pop_linkman"><div class="pop_header"><strong>'+TEXT.UPLOAD_FILE+'</strong><a class="icon icon_min hide"></a><a class="icon icon_close"></a></div><div class="pop_body" style="padding:10px"><iframe id="nd-uploadframe" frameborder="0" style="width:100%;height:300px" scrolling="auto" src="javascript:void(0)"></iframe></div><div class="pop_footer"><button type="button" name="confirm" class="btn" disabled="disabled">'+TEXT.CONFIRM+'</button></div></div>',
	
	info: $('<div class="nd_upload_info"><table border="0" cellspacint="0" cellpadding="0"><tr><td rowspan="2" class="info_progress">0%</td><td>'+TEXT.SPEED+'<span class="speed">0KB/s<span></td></tr><tr><td>'+TEXT.FILE+' <span class="total">0</span>/<span class="complete">0</span></td></tr></table></div>'),
	
	win: null,
	
	uploaded: false,
	
	uploading: false,
	
	showDialog: function(folderid) {
		if (!folderid) {
			folderid = '^root';
		}
	
		var _o = this;
		if (null === this.win) {
			this.win = appendWindow('netdisk-upload', _o.wintpl, {
				width: 400,
				draggable: true,
				onClose: function() {
					var url = getJQ()('#mainframe')[0].contentWindow.location;
					if ((/\/netdisk/).test(url) && _o.uploaded) {
						getJQ()('#mainframe')[0].contentWindow.location.reload();
					}
					_o.win.destroy();
					_o.info.remove();
					_o.uploaded = false;
					_o.win = null;
				}
			});
			
			_o.info.appendTo(document.body).hide();
			
			this.win.find('a.icon_close').bind('click', function(){
				if (_o.uploading) {
					if (!confirm(TEXT.UPLOAD_CLOSE_HINT)) {
						return ;
					}
				}
				var size = TOP.getJQ()('#quick-tools').find('span[id="nd-upload-info"]').size();
				if (size > 0) {
					getJQ()('#quick-tools').find('span[id="nd-upload-info"]').remove();
				}
				_o.win.close();
			});
			this.win.find('button[name="confirm"]').bind('click', function(){
				_o.win.close();
			});
			
			this.win.find('.icon_min').bind('click', function(){
				_o.win.hide();
				
				var obj = $('<span id="nd-upload-info"><a href="javascript:void(0)">'+TEXT.ND_UPLOAD+'</a>&nbsp;|&nbsp;</span>');
				
				obj.bind('click', function(){
					_o.win.show();
					_o.info.hide();
					$(this).remove();
				})
				.bind('mouseover', function(){
					var offset = $(this).offset();
					_o.info.css('left', offset.left + 'px');
					_o.info.show();
				})
				.bind('mouseout', function(){_o.info.hide();});
				
				var size = getJQ()('#quick-tools').find('span[id="nd-upload-info"]').size();
				if (size <= 0) {
					getJQ()('#quick-tools').prepend(obj);
				}
			});
			
			if (!$.browser.msie) {
				this.win.find('#nd-uploadframe').attr('src', '/netdisk/upload?type=ajax&folderid=' + folderid);
			} else {
				this.win.find('#nd-uploadframe').attr('src', '/netdisk/upload?type=flash&folderid=' + folderid);
			}
		}
		
		this.win.show();
	},
	
	confirmEnabled: function(boolean) {
		if (this.win) {
			this.win.find('button[name="confirm"]').attr('disabled', !boolean);
		}
	},
	
	setProgress: function(progress) {
		this.info.find('.info_progress').text(progress);
	},
	
	setSpeed: function(speed) {
		this.info.find('.speed').html(speed);
	},
	
	setFilecount: function(total, complete) {
		this.info.find('.total').text(total);
		this.info.find('.complete').text(complete);
	}
};

var NetdiskPanel = function(parent) {
	if (parent) {
		this.parent = parent;
	}
	this.init();
};

NetdiskPanel.prototype = {
	
	parent: null,
	
	_tree: null,
	
	_el: null,
		
	init: function() {
		this._el = $('<div class="netdisk_panel"></div>');
	
		var _o = this;
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: '/netdisk/list',
			success: function(ret) {
				if (ret.success && ret.data) {
					var files = ret.data.files,
						folders = ret.data.folders;
					
					_o._tree = new $.tree({
						id: 'netdisk-tree',
						idKey: 'id',
						idPrefix: 'nd-',
						cls: 'netdisk-tree'
					});
					
					for (var folderid in folders) {
						if (folderid == '^root') {
							continue ;
						}
						var node = new $.treenode({
							data: {
								id: 'fo-' + folderid.replace('^', '_'),
								name: folders[folderid].foldername
							},
							content: '<span class="icon ficon folder"></span>{name}',
							events: {
								click: function(e){_o._tree.find(this.id.replace('f-', ''), true).toggle();stopEventBuddle(e);}
							}
						});
						
						_o._tree.appendNode(node);
					}
					
					for (var i = 0, c = files.length; i < c; i++) {
						var ext = files[i].filename.split('.').pop(), fileId;
						//if (files[i].fromfileid) {
							//fileId = files[i].fromfileid.replace('^', '_');
						//} else {
							fileId = files[i].fileid.replace('^', '_');
						//}
						var node = new $.treenode({
							data: {
								id: 'f-' + fileId,
								name: files[i].filename,
								filesize: files[i].size
							},
							content: '<input name="fileid[]" type="checkbox" value="'+fileId+'" /><span class="icon ficon '+ext+'"></span>{name}',
							isLeaf: true,
							events: {
								click: function(e){stopEventBuddle(e);}
							}
						});
						
						var folder = _o._tree.find('fo-' + files[i].folderid.replace('^', '_'), true);
						
						if (folder) {
							folder.appendChild(node);
						} else {
							_o._tree.appendNode(node);
						}
					}
					
					_o._tree.appendTo(_o._el);
					
					_o._el.find(':checkbox[name="fileid[]"]').bind('click', function(e){stopEventBuddle(e);});
				}
			},
			error: function(res) {}
		});
	},
	
	getFileSelected: function() {
		var file = [], _o = this;
		this._el.find(':checkbox[name="fileid[]"]:checked').each(function(){
			var node = _o._tree.find('f-' + this.value, true);
			if (node) {
				file.push({fileid: this.value, filename: node.get('name'), filesize: node.get('filesize')});
			}
		});
		
		return file;
	},
	
	renderTo: function(parent) {
		this._el.appendTo(parent);
	}
};

var Util = {
	
	/**
	 * 复制对象
	 */
	apply: function() {
		var r = {};
		for (var i = 0, c = arguments.length; i < c; i++) {
			if (typeof arguments[i] != 'object') {
				continue ;
			}
			for (var k in arguments[i]) {
				r[k] = arguments[i][k];
			}
		}
		
		return r;
	},
	
	encodeHTML: function(string) {
		if (typeof string !== 'string' || !string) {
			return string;
		}
		
		return string.replace(/&/g, '&amp;')
		        .replace(/</g, '&lt;')
		        .replace(/>/g, '&gt;')
		        .replace(/"/g, '&quot;')
		        .replace(/'/g, '&#39;');
	},
	
	inArray: function(value, arr) {
		for (var i = 0, c = arr.length; i < c; i++) {
			if (value == arr[i]) {
				return true;
			}
		}
		
		return false;
	}
};

var UI={version:"1.0",browser:{msie:$.browser.msie,firefox:$.browser.mozilla,opera:$.browser.opera,webkit:$.browser.webkit,version:$.browser.version},clsPfx:"tui-",autoId:0,components:{},extend:(function(){var b=function(d){for(var c in d){this[c]=d[c]}};var a=Object.prototype.constructor;return function(h,e,g){if(UI.isObject(e)){g=e;e=h;h=g.constructor!=a?g.constructor:function(){e.apply(this,arguments)}}var d=function(){},f,c=e.prototype;d.prototype=c;f=h.prototype=new d();f.constructor=h;h.superclass=c;if(c.constructor==a){c.constructor=e}h.override=function(i){UI.override(h,i)};f.superclass=f.supr=(function(){return c});f.override=b;UI.override(h,g);h.extend=function(i){UI.extend(h,i)};return h}})(),override:function(a,c){if(c){var b=a.prototype;UI.apply(b,c);if(UI.browser.msie&&c.toString!=a.toString){b.toString=c.toString}}},apply:function(d,e,b){if(b){UI.apply(d,b)}if(d&&e&&typeof e=="object"){for(var a in e){d[a]=e[a]}}return d},require:function(a,b,d){var c=b.createElement("script");c.type="text/javascript";c.src=a;if(typeof d=="function"){if(UI.browser.msie){c.onreadystatechange=function(){if(c.readyState==4){d.call(b,true)}}}else{c.onload=function(){d.call(b,true)}}c.onerror=function(){d.call(b,false)}}b.appendChild(c)},getAutoId:function(){return UI.autoId++},isArray:function(a){return a&&a.length&&/function\s+Array/.test(a.constructor.toString())},isObject:function(a){return a&&typeof(a)=="object"},isFunction:function(a){return a&&typeof(a)=="function"},setComponent:function(b,a){this.components[b]=a},deleteComponent:function(a){if(this.components[a]){delete this.components[a]}},get:function(a){return this.components[a]}};UI.Dom={get:function(a,b){return jQuery(a,b)},convert:function(a,b){return jQuery(a,b)},create:function(a,b){return jQuery(a,b)},cancelBuddle:function(a){a.cancelBubble=true;if(a.stopPropagation){a.stopPropagation()}return false}};UI.Component=function(a){this._id=typeof(a.id)=="string"?a.id:"tui-"+UI.getAutoId();this.setConfig(a).init();UI.setComponent(this._id,this);if(a.scope){this._scope=this._config.scope}else{this._scope=document.body}};UI.Component.prototype={_config:null,_el:null,_events:null,_isRendered:false,_id:null,_scope:null,setConfig:function(a,b){if(null===this._config){this._config={}}if(typeof a=="string"&&undefined!==b){this._config[a]=b}else{if(typeof a=="object"){this._config=UI.apply(this._config,a)}}return this},getConfig:function(a){if(undefined===a){return this._config}return this._config[a]},init:function(){},getEl:function(){return this._el},render:function(){if(!this._isRendered){this._render()}if(this._el){if(this._config.cls){this._el.addClass(this._config.cls)}if(this._config.css){this._el.css(this._config.css)}}this._isRendered=true},_render:function(){},appendTo:function(a){if(!this._isRendered){this.render()}if(null!==this._el){this._el.appendTo(a)}return this},prependTo:function(a){if(!this._isRendered){this.render()}if(null!==this._el){this._el.prependTo(a)}return this},replace:function(a){if(!this._isRendered){this.render()}if(typeof a=="string"){a=$(a)}a.after(this._el);a.remove()},bind:function(a,b){if(!this._events){this._events={}}if(typeof this._events[a]=="undefined"){this._events[a]=[]}if(typeof b=="function"){this._events[a].push(b)}return this},unbind:function(b,e){if(!this._events||typeof this._events[b]=="undefined"){return}if(undefined===e){delete this._events[b];return this}for(var a=0,d=this._events[b].length;a<d;a++){if(e==this._events[b][a]){delete this._events[b][a]}}return this},triggerEvent:function(d,a){if(!this._events||typeof this._events[d]=="undefined"){return this}if(!a){a=[]}for(var b=0,e=this._events[d].length;b<e;b++){this._events[d][b].apply(this,a)}return this},focus:function(){this._el.focus();return this},blur:function(){this._el.blur();return this},disabled:function(){},enabled:function(){},getId:function(){return this._id},destroy:function(){this._destroy();for(var a in this._events){delete this._events[a]}delete this._events;delete this._config;this._el.remove();delete this._el;UI.deleteComponent(this._id);return null},_destroy:function(){}};
if(undefined!==UI){UI.SingleSelect=function(a){UI.SingleSelect.superclass.constructor.call(this,a)};UI.extend(UI.SingleSelect,UI.Component,{_input:null,_options:null,_isShow:false,_value:null,init:function(){this._options=[];if(this._config.select){var a=this._config.select;if(typeof a=="string"){a=UI.Dom.get(a)}this._input=a;var d=[];a.find("option").each(function(){d[d.length]={value:this.value,text:UI.Dom.get(this).text()}});this._options=d;var f=this;a.bind("change",function(){f.select(this.value)});this._value=a.val();if(!this._config.defaultText){this._config.defaultText=a.find("option:eq(0)").text()}if(!this._config.css){this._config.css={width:a.outerWidth()+"px"}}else{if(!this._config.css.width){this._config.css.width=a.outerWidth()+"px"}}}else{if(typeof this._config.name=="string"){this._input=UI.Dom.convert('<input type="hidden" name="'+this._config.name+'" />')}}if(this._config.options&&UI.isArray(this._config.options)){for(var b=0,e=this._config.options.length;b<e;b++){this.addOption(this._config.options[b])}}},addOption:function(a){if(typeof(a)=="object"){this._options[this._options.length]=a}else{if(typeof(a=="string")){this._options[this._options.length]={value:a,text:a}}}return this},select:function(e){var a=this._options;for(var d=0,f=a.length;d<f;d++){if(e==a[d].value){this._input.val(e);this.updateSelectText(a[d]);break}}var b=this._value;this._value=e;if(e!=b){this.triggerEvent("change")}return this},getValue:function(){return this._value},updateSelectText:function(a){if(this._textCt){this._textCt.html(a.text)}return this},appendSelectOption:function(a){},isShow:function(a){if(undefined!==a){this._isShow=a}return this._isShow},getMenu:function(){if(this._menu){return this._menu}return null},isHover:function(){return this._menu.find("div."+UI.clsPfx+"select-option-hover").size()>0},showMenu:function(){if(!this._menu||!this._el){return}this._menu.show();this._el.css("z-index",20);this._el.addClass(UI.clsPfx+"select-expand");if(this._config.maxHeight&&this._config.maxHeight>0){if(this._menu.height()>this._config.maxHeight){this._menu.css("height",this._config.maxHeight+"px")}}if(this._config.menuWidth){this._menu.css("width",this._config.menuWidth+"px")}else{this._menu.css("width",this._el.outerWidth()-2+"px")}this.isShow(true);this.triggerEvent("show");if(UI.browser.msie&&UI.browser.version<"7.0"){var b=this._el.parent(),c=b.css("position"),a=b.css("z-index");b.data("mcs",{position:c?c:"static","z-index":a?a:0});b.css({position:"relative","z-index":20})}return this},hideMenu:function(){if(!this._menu||!this._el||!this.isShow()){return}this._menu.hide();this._el.css("z-index",10);this._el.removeClass(UI.clsPfx+"select-expand");this._menu.find("div."+UI.clsPfx+"select-option-hover").removeClass(UI.clsPfx+"select-option-hover");this._menu.css("height","auto");this.isShow(false);this.triggerEvent("hide");if(UI.browser.msie&&UI.browser.version<"7.0"){var a=this._el.parent(),b=a.data("mcs");if(b){a.css(b)}}return this},toggleMenu:function(){if(this.isShow()){this.hideMenu()}else{this.showMenu()}return this},empty:function(){this._options=[];this._value="";if(this._menu){this._menu.html("");var a=this._config.defaultText?this._config.defaultText:"";this._textCt.text(a)}if(this._input){this._input.val("")}},refreshMenu:function(){if(!this._menu){return}var f=[];for(var d=0,e=this._options.length;d<e;d++){var b=this._options[d],a=['<div class="'+UI.clsPfx+'select-option" _value="'+this._options[d].value+'"><div class="'+UI.clsPfx+'select-option-body">'];if(b.body){a[a.length]=b.body}else{if(b.text){a[a.length]=b.text}}a[a.length]="</div></div>";f[f.length]=a.join("")}this._menu.html(f.join(""));return this},_render:function(){this._el=UI.Dom.convert('<div id="'+this._id+'" class="'+UI.clsPfx+'select"><span class="'+UI.clsPfx+'select-arrow"></span></div>',this._scope);this._textCt=UI.Dom.convert('<span class="'+UI.clsPfx+'text"></span>',this._scope);this._menu=UI.Dom.convert('<div class="'+UI.clsPfx+'select-menu"></div>',this._scope);if(!this._config.tabIndex){this._config.tabIndex=1}this._el.attr("tabindex",this._config.tabIndex);if(this._config.defaultText){this._textCt.text(this._config.defaultText)}this.refreshMenu();if(this._config.menuCls){this._menu.addClass(this._config.menuCls)}this._menu.hide();this._el.prepend(this._textCt);this._menu.appendTo(this._el);if(this._input){this._el.append(this._input)}var a=this._id;this._el.bind("click keydown",function(f){var d=UI.get(a);UI.Dom.cancelBuddle(f);if(f.type=="keydown"){var c=f.keyCode?f.keyCode:f.which,h=d.getMenu();var b=h.find("div."+UI.clsPfx+"select-option-hover:eq(0)"),g=UI.clsPfx+"select-option-hover";if(c==38){if(!b.size()){h.find("div:last-child").addClass(g)}else{b.removeClass(g);b.prev().addClass(g)}}else{if(c==40){if(!b.size()){h.find("div:eq(0)").addClass(g)}else{b.removeClass(g);b.next().addClass(g)}}else{if(c==13){if(b.size()){b.removeClass(g);d.select(b.attr("_value"))}d.toggleMenu()}}}return}d.toggleMenu();d.triggerEvent("click")}).bind("mouseover mouseout",function(c){var b=UI.get(a);if(c.type=="mouseover"){b.getEl().addClass(UI.clsPfx+"select-hover")}else{b.getEl().removeClass(UI.clsPfx+"select-hover")}});this._menu.bind("mouseover mouseout click",function(d){var c=d.srcElement?UI.Dom.get(d.srcElement):UI.Dom.get(d.target);var b=c.closest("."+UI.clsPfx+"select-option");if(b.size()){if(d.type=="mouseover"){UI.Dom.cancelBuddle(d);b.addClass(UI.clsPfx+"select-option-hover")}else{if(d.type=="mouseout"){UI.Dom.cancelBuddle(d);b.removeClass(UI.clsPfx+"select-option-hover")}else{UI.get(a).select(b.attr("_value"))}}}});this._el.bind("blur",function(){var b=UI.get(a);if(b&&!b.isHover()){b.hideMenu()}});UI.Dom.get(this._scope).bind("click",function(){var b=UI.get(a);if(b&&!b.isHover()){b.hideMenu()}});if(this._config.selected){this.select(this._config.selected)}if(this._input){this._input.hide()}if(this._value){this.select(this._value)}}})};