/**
 * 图度左导航菜单
 */
$.Menu = function(setting) {
	$.extend(this, $.Menu.defaults, setting || {});
	
	this.init();
};

$.Menu.defaults = {
	// 系统标签填充处
	sysTarget: '#sys_label_list ul',
	// 用户自动义标签填充处
	userTarget: '#user_label_list ul',
	// 标签
	labels: [],
	// 隐藏的菜单ID
	dropLabelId: null,
	// 触发元素
	dropTarget: '#f_morelabel_td',
	// 外围容器 css Class
	dropWrapCls: 'option-menu-wrap label-menu',
	// 菜单inner Css样式
	dropMenuCss: {width: '200px'},
	// 最大高度
	dropMaxHeight: 350,
	// 是否重新设定显示位置
	dropResetpos: true,
	// 是否创建分隔符
	dropSeparate: true,
	// 菜单项选中时触发
	onSelect: function(){}
};

(function($) {

$.Menu.prototype = {
	/**
	 * 隐藏的菜单列表对象
	 */
	_dropMenu: null,
	
	/**
	 * 隐藏的菜单项
	 */
	_dropItems: [],
	
	/**
	 * 左导航选中菜单项
	 */
	_lastSwitchLabel: null,
	
	/**
	 * 初始化
	 */
	init: function() {
		var _o = this;
		
		if (_o.labels.length) {
			// 添加左导航菜单
			_o.appendMenu();
			// 设置默认隐藏的菜单项
			_o.setDefaultDropItems();
			// 隐藏的菜单
			_o._dropMenu = new $.Dropdown({
			    id: _o.dropLabelId,
			    wrapCls: _o.dropWrapCls,
			    maxHeight: _o.dropMaxHeight,
			    target: $(_o.dropTarget),
			    resetpos: _o.dropResetpos,
			    separate: _o.dropSeparate,
			    menuCss:_o.dropMenuCss,
			    items: _o.getDropItems(),
			    onSelect: function(){
			        var href = this.find('a:eq(0)').attr('href');
	
			        if (href && -1 == href.indexOf('javascript:')) {
			            $('#mainframe')[0].contentWindow.location = this.find('a:eq(0)').attr('href');
			        }
			    },
			    onHide: function(){
			    	Frame.switchFolder('#' + TOP.Frame.getLastSwitchLabel());
			    }
			});
		}
	},
	
	/**
	 * 标签排序（冒泡排序法）
	 */
	sortLabels: function(labels) {
		var _o = this,
			_labels = [],
			sysLabels = [],
			userLabels = [],
			temp = null,
			flag = 0;
		// 分类系统标签与用户自定义标签
		for (i = 0; i < labels.length; i++) {
			if (labels[i].issystem) {
				// 系统标签
				sysLabels.push(labels[i]);
			} else {
				// 用户自定义标签
				userLabels.push(labels[i]);
			}
		}
		// 系统标签排序
		for (i = 0; i < sysLabels.length; i++) {
			flag = 0;
			for (var j = sysLabels.length - 2; j >= i; j--) {
				if (sysLabels[j+1].ordernum > sysLabels[j].ordernum) {
				    temp = sysLabels[j+1];
				    sysLabels[j+1] = sysLabels[j];
				    flag = 1;
				    sysLabels[j] = temp;
			    }
			}
			if (flag == 0) {
				break ;
			}
		}
		
		for (i = 0; i < sysLabels.length; i++) {
			_labels.push(sysLabels[i]);
		}
		// 用户自定义标签排序
		for (i = 0; i < userLabels.length; i++) {
			flag = 0;
			for (var j = userLabels.length - 2; j >= i; j--) {
				if (userLabels[j+1].ordernum > userLabels[j].ordernum) {
				    temp = userLabels[j+1];
				    userLabels[j+1] = userLabels[j];
				    flag = 1;
				    userLabels[j] = temp;
			    }
			}
			if (flag == 0) {
				break ;
			}
		}
		
		for (i = 0; i < userLabels.length; i++) {
			_labels.push(userLabels[i]);
		}
		
		return _labels;
	},
	
	/**
	 * 添加可见左导航菜单
	 */
	appendMenu: function(labels) {
		var _o = this;
		
		if (typeof labels == 'undefined' || !labels.length) {
			labels = this.labels;
		}
		
		// 标签排序
		labels = _o.sortLabels(labels);
		
		for (var i = 0; i < labels.length; i++) {
			// 过滤数据
			if (typeof labels[i].labelalias == 'undefined') {
				continue;
			}
			
			// 系统标签部分
			if (labels[i].issystem) {
				// 系统标签(可见HTML)
				var sysTpl = '<li id="f_'+labels[i].labelalias+'_td" order="'+labels[i].ordernum+'"><a id="f_'+labels[i].labelalias+'" href="/tudu/?search='+encodeURIComponent(labels[i].labelalias)+'" target="main" onClick="Frame.switchFolder(\''+labels[i].labelalias+'\')"><span>'+labels[i].labelname+'</span><span class="lab_tudu_count"></span></a></li>';
				// 系统标签(不可见HTML)
				var hideTpl = '<a target="main" href="/tudu/?search='+encodeURIComponent(labels[i].labelalias)+'">'+labels[i].labelname+'<span class="lab_tudu_count"></span></a>';
				// 特殊处理系统标签（我审核）
				if (labels[i].labelalias == 'review') {
					sysTpl = '<li id="f_'+labels[i].labelalias+'_td" order="'+labels[i].ordernum+'"><a id="f_'+labels[i].labelalias+'" href="/tudu/converge?search='+encodeURIComponent(labels[i].labelalias)+'" target="main" onClick="Frame.switchFolder(\''+labels[i].labelalias+'\')"><span>'+labels[i].labelname+'</span><span class="lab_tudu_count"></span></a></li>';
					hideTpl = '<a target="main" href="/tudu/converge?search='+encodeURIComponent(labels[i].labelalias)+'">'+labels[i].labelname+'<span class="lab_tudu_count"></span></a>';
				}
				// 显示
				if (labels[i].isshow == 1) {
					$(this.sysTarget).append(sysTpl);
					// 更新标签未读数
					this.updateLabels(labels[i].labelalias, labels[i].totalnum+'|'+labels[i].unreadnum);
				// 有内容时显示
				} else if (labels[i].isshow == 2 && labels[i].totalnum > 0){
					$(this.sysTarget).append(sysTpl);
					// 更新标签未读数
					this.updateLabels(labels[i].labelalias, labels[i].totalnum+'|'+labels[i].unreadnum);
				// 隐藏
				} else {
					this._dropItems.push({
						order: labels[i].ordernum,
						name: labels[i].labelalias,
						issystem: 1,
						count: labels[i].totalnum+'|'+labels[i].unreadnum,
						body: hideTpl
					});
				}
			// 用户自定义标签部分
			} else {
				var tpl = '<li id="f_'+labels[i].labelid+'_td" order="'+labels[i].ordernum+'"><a id="f_'+labels[i].labelid+'" href="/tudu/?search=cat&cat='+encodeURIComponent(labels[i].labelalias)+'" onClick="Frame.switchFolder(\''+labels[i].labelid+'\')" target="main"><span class="tag_icon" style="background-color:'+labels[i].bgcolor+';margin:6px 3px 0 0"></span><span class="labelname">'+labels[i].labelname+'</span><span class="lab_tudu_count"></span></a></li>';
				// 显示
				if (labels[i].isshow == 1) {
					$(this.userTarget).append(tpl);
					// 更新标签未读数
					this.updateLabels(labels[i].labelid, labels[i].totalnum+'|'+labels[i].unreadnum);
				// 有内容时显示
				} else if (labels[i].isshow == 2 && labels[i].totalnum > 0 ){
					$(this.userTarget).append(tpl);
					// 更新标签未读数
					this.updateLabels(labels[i].labelid, labels[i].totalnum+'|'+labels[i].unreadnum);
				// 隐藏
				} else {
					this._dropItems.push({
						order: labels[i].ordernum,
						name: labels[i].labelalias,
						issystem: 0,
						count: labels[i].totalnum+'|'+labels[i].unreadnum,
					    body: '<span class="menu-square" style="background:'+labels[i].bgcolor+'"></span><a target="main" href="/tudu/?search=cat&cat='+encodeURIComponent(labels[i].labelalias)+'">'+labels[i].labelname+'<span class="lab_tudu_count"></span></a>'
					});
				}
			}
		}
	},
	
	/**
	 * 更新标签未读数
	 */
	updateLabels: function(label, count) {
		a = $('#f_' + label);
		if (!a.size()) {
			return false;
		}
		
		if (label == 'sent' || label == 'ignore') {
			return false;
		}
		
		count = count.split('|');
		if (label != 'starred' && label != 'todo' && label != 'review') {
			if (label == 'drafts') {
				count[1] = count[0];
			}
			if (count[1] > 0) {
				a
				.find('.lab_tudu_count')
				.text('(' + count[1] + ')');
				
				if (label != 'drafts') {
					a.addClass('b');
				}
			} else {
				a
				.removeClass('b')
				.find('.lab_tudu_count')
				.text('');
			}
		}
		
		// 星标关注、我执行、我审批 特殊处理标签未读数
		if (label == 'starred' || label == 'todo' ||  label == 'review') {
			this.updateCount(a, count);
		}
	},
	
	/**
	 * 标签未读数
	 * 我执行、星标关注
	 */
	updateCount: function(obj, count) {
		if (count[1] > 0) {
			obj
			.find('.lab_tudu_count')
			.text('(' + count[1] + '/' + count[0] + ')');
		} else if(count[0] == 0){
			obj
			.find('.lab_tudu_count')
			.text('');
		} else {
			obj
			.find('.lab_tudu_count')
			.text('(' + count[0] + ')');
		}
		if (count[1] > 0) {
			obj.addClass('b');
		} else {
			obj.removeClass('b');
		}
	},
	
	/**
	 * 重新加载左导航菜单
	 */
	reloadMenu: function(labels) {
		if ($('#sb-middle .on').attr('id') != 'f_morelabel_td') {
			this._lastSwitchLabel = $('#sb-middle .on').attr('id');
		}
		// 清空隐藏的菜单项
		this._dropItems = [];
		// 清空所有左导航菜单
		this.clear();
		// 添加左导航菜单
		this.appendMenu(labels);
		// 重新设置默认标签项
		this.setDefaultDropItems();
		// 设置分隔符
		this._dropMenu.updateSeparate(true);
		// 添加隐藏的菜单项
		if (!$('#'+this.dropLabelId).size()) {
			this._dropMenu.items = this.getDropItems();
		} else {
			for (var i = 0; i < this._dropItems.length; i++) {
				this._dropMenu.addItem(this._dropItems[i]);
			}
		}
		// 重新选中上次选中的菜单项
		if (this._lastSwitchLabel !== null) {
			Frame.switchFolder('#' + this._lastSwitchLabel);
		}
	},
	
	/**
	 * 清空菜单
	 */
	clear: function() {
		// 清空系统标签（显示）
		$(this.sysTarget).empty();
		// 清空用户自定义的标签（显示）
		$(this.userTarget).empty();
		// 清空隐藏的菜单项
		this._dropMenu.clear();
	},
	
	/**
	 * 设置默认的隐藏菜单项
	 */
	setDefaultDropItems: function() {
		this._dropItems.push({
		    body: '<a href="/label/" target="main">'+TEXT.TAGS_MANAGE+'</a>'
		});
		this._dropItems.push({
		    body: '<a href="javascript:void(0)" target="main">'+TEXT.CREATE_LABEL+'</a>',
		    event: {
		        click: function(){
					Frame.Labels.create();
		        }
		    }
		});
	},
	
	/**
	 * 获取隐藏的菜单项
	 */
	getDropItems: function() {
		return this._dropItems;
	}
};
	
$.fn.labelmenu = function(params) {
	var o = new $.Menu(params);
	
	return o;
};
	
})(jQuery);