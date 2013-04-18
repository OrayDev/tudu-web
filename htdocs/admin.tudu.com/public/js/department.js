var Department = {
	
	tpls: {
		item: '<div class="dept-item"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-list"><tr><td class="td-first" align="left"><div class="td-space"><span class="tree-list-icon"></span><span class="dept-name"></span></div></td><td width="170" class="td-last" align="left"><div class="td-space dept-leader"></div></td><td width="210" class="td-last" align="left"><div class="td-space"><a href="javascript:void(0)" name="moderator">[负责人]</a> <a href="javascript:void(0)" name="member">[成员]</a> <a href="javascript:void(0)" name="rename">[编辑]</a> <a href="javascript:void(0)" name="delete">[删除]</a></div></td><td width="50" align="left"><div class="td-space"><a href="javascript:void(0);" name="up">↑</a> <a href="javascript:void(0);" name="down">↓</a></div></td></tr></table><div class="dept-children"></div></div>',
		modify: '<div id="dept-modify-form"><input type="hidden" name="deptid" value="" /><p style="margin:5px 0">部门名称</p><input type="text" class="text" style="width:370px" value="" name="name" maxlength="20" /><p style="margin:5px 0">上级部门</p></div>'
	},
	
	depts: null,
	
	/**
	 * 部门列表
	 */
	list: null,
	
	/**
	 * 展开状态记录
	 */
	expandStatus: {},
	
	/**
	 * 编辑窗口
	 */
	modifyWin: null,
	
	/**
	 * 成员窗口
	 */
	memberWin: null,
	
	/**
	 * 负责人窗口
	 */
	moderatorWin: null,
	
	// 选择器
	selector: null,
	
	// 简单选择器
	SingleSelector: null,
	
	// 用户信息
	users: {},
	
	// 企业名称
	orgname: null,
	
	/**
	 * 初始化部门管理页面
	 */
	init: function(depts, orgname) {
		var me = this;
		this.depts = depts;
		this.orgname = orgname;
		this.list = $('#dept-list');
		
		this.list.empty();
		
		$('input[name="create"]').bind('click', function(){
			Department.modify();
		});
		
		var loader = _TOP.SingleLoader.getLoader('user', {
			url: BASE_PATH + '/user/user/struct.load',
			method: 'GET'
		});
		
		loader.load(function(ret){
			var i, l = ret.data.users.length;
			for (i = 0; i < l; i++) {
				me.users[ret.data.users[i].userid] = ret.data.users[i].truename;
			}
			me.initDeptList();
			
			var fdid = Cookie.get('FOCUS-DEPT');
			if (fdid) {
				Department.focusDept(fdid);
				Cookie.set('FOCUS-DEPT', null);
			}
		});
	},
	
	/**
	 * 
	 */
	initDeptList: function() {
		var i, c = this.depts.length;
		this.list.empty();
		if (c <= 0) {
			this.list.append('<div id="dept-null" style="padding:30px 0;text-align:center">没有部门</div>');
		} else {
			for (i = 0; i < c; i++) {
				this.appendDept(this.depts[i]);
			}
		}
		// 展开第一层
		Department.expand(this.depts[0].deptid);
		$('#dept-list .dept-item').each(function(){
			var o = $(this);
			if (o.find('>.dept-children .dept-item').size()) {
				var icon = o.find('>table.table-list .tree-list-icon'),
					deptid = o.attr('id').replace('dept-', '');
				icon.addClass('ti-minus').bind('click', function(){
					var ico = $(this);
					if (ico.hasClass('ti-minus')) {
						Department.collspan(deptid);
					} else {
						Department.expand(deptid);
					}
				});
				
				if (!Department.expandStatus[deptid]) {
					Department.collspan(deptid);
				}
			}
		});
		
		$('#dept-list table').rowHover();
	},
	
	/**
	 * 添加页面元素
	 */
	appendDept: function(item) {
		var parent = item.parentid ? $('#dept-' + item.parentid + ' div.dept-children:eq(0)') : this.list,
			ele = $(this.tpls.item);
		var moderatorsName = [];
		
		if (!parent.size()) {
			return ;
		}
		
		var me = this, i, l = item.moderators.length;
		if (l <= 0) {
			moderatorsName = '-';
		} else {
			for (i = 0; i < l; i++) {
				moderatorsName.push(me.users[item.moderators[i]]);
			}
		}
		
		
		ele
		.attr({id: 'dept-' + item.deptid, '_parentid': item.parentid})
		.find('span.dept-name').text(item.deptname);
		ele.find('div.dept-children').attr({id: 'dept-children-' + item.deptid});
		ele.find('div.dept-leader').html('<span>'+moderatorsName+'<input type="hidden" value="'+item.moderators+'" id="moderator-'+item.deptid+'" /></sapn>');
		
		ele.find('a[name="delete"]').bind('click', function(){
			Department.deleteDept(item.deptid);
		});
		ele.find('a[name="member"]').bind('click', function(){
			Department.member(item.deptid);
		});
		ele.find('a[name="moderator"]').bind('click', function(){
			Department.moderator(item.deptid);
		});
		ele.find('a[name="rename"]').bind('click', function(){
			Department.modify(item.deptid);
		});
		ele.find('a[name="up"]').bind('click', function(){
			Department.sortDept(item.deptid, 'up');
		});
		ele.find('a[name="down"]').bind('click', function(){
			Department.sortDept(item.deptid, 'down');
		});
		
		if (item.deptid == '_root') {
			ele.find('span.dept-name').text(me.orgname);
			ele.find('a[name="member"]').remove();
			ele.find('a[name="rename"]').remove();
			ele.find('a[name="delete"]').remove();
		}
		
		parent.append(ele);
		
		if (item.firstnode && !item.lastnode) {
			ele.find('a[name="up"]').addClass('lightgray');
			ele.find('.tree-list-icon').addClass('ti-mid');
		} else if (item.firstnode && item.lastnode) {
			ele.find('a[name="up"]').addClass('lightgray');
			ele.find('a[name="down"]').addClass('lightgray');
			ele.find('.tree-list-icon').addClass('ti-last');
			ele.find('>.dept-children').css('background-image', 'none');
		} else if (item.lastnode) {
			ele.find('a[name="down"]').addClass('lightgray');
			ele.find('.tree-list-icon').addClass('ti-last');
			ele.find('>.dept-children').css('background-image', 'none');
		} else {
			ele.find('.tree-list-icon').addClass('ti-mid');
		}
	},
	
	/**
	 * 展开
	 */
	expand: function(deptid) {
		var me = this,
			ele = $('#dept-' + deptid);
		var parentid = ele.attr('_parent');
		ele.find('#dept-children-' + deptid).show();
		ele.find('>table.table-list span.tree-list-icon').removeClass('ti-plus').addClass('ti-minus');
		
		if (parentid) {
			expand(parentid);
		}
		
		me.expandStatus[deptid] = '1';
	},
	
	/**
	 * 折叠
	 */
	collspan: function(deptid) {
		var me = this,
			ele = $('#dept-' + deptid);
		ele.find('#dept-children-' + deptid).hide();
		ele.find('>table.table-list span.tree-list-icon').removeClass('ti-minus').addClass('ti-plus');
		delete me.expandStatus[deptid];
	},
	
	/**
	 * 
	 */
	modify: function(deptid) {
		var me = this;
		if (deptid) {
			
		}
		
		if (null === me.modifyWin) {
			me.modifyWin = Admin.window({
				id: 'dept-modifywin',
	    		width: 400,
	    		title: '编辑部门',
	    		body: me.tpls.modify,
	    		formid: 'dept-modifyform',
	    		footer: '<input name="submit" type="submit" class="btn" value="确定"><input name="close" type="button" class="btn" value="关闭" />',
	    		action: BASE_PATH + '/user/department/create',
	    		draggable: true,
	    		init: function() {
	    		    var form = this.find('form');

	    		    this.find('input[name="close"]').click(function() {
	    		    	me.modifyWin.close();
	    		    });
	    		    
	    		    form.submit(function(){return false;});
	    		    form.submit(function(){
	    		    	Department.saveDept(form);
	    			});
	    		},
	    		onShow: function() {
	    			var form = Frame.queryParent('#dept-modifyform');
	    			
	    			var opt = [{text: '无', value: ''}];
	    			for (var i = 0, c = me.depts.length; i < c; i++) {
	    				if (me.depts[i].deptid != '_root') {
	    					opt[opt.length] = {text: me.depts[i].prefix + me.depts[i].deptname, value: me.depts[i].deptid};
	    				}
	    			}
	    			
	    			me.parentSelect = new UI.SingleSelect({
    					id: 'target-board',
    					name: 'parentid',
    					cls: 'select',
    					maxHeight: 150,
    					options: opt,
    					menuCls: 'option',
    					css: {width:'368px'},
    					options: opt,
    					scope: _TOP.document.body
    				});
	    		    me.parentSelect.appendTo(Frame.queryParent('#dept-modify-form'));
	    			
	    			if (deptid) {
	    				var obj = $('#dept-' + deptid),
	    				    name = obj.find('span.dept-name:eq(0)').text(),
	    				    parentid = obj.attr('_parentid');
	    				
	    				setTimeout(function(){
	    					//$(parentSelect).find('option[value="'+parentid+'"]').attr('selected', 'selected');
	    					me.parentSelect.select(parentid);
	    				}, 100);
	    				
	    				form.find('input[name="name"]').val(name);
	    				form.find('input[name="deptid"]').val(deptid);
	    				form.attr('action', BASE_PATH + '/user/department/update');

	    				Department.focusDept(deptid);
	    			} else {
	    				form.find('input[name="name"]').val('');
	    				form.find('select[name="parentid"]').val('');
	    				form.attr('action', BASE_PATH + '/user/department/create');
	    			}
	    		},
	    		onClose: function() {
	    			me.modifyWin.destroy();
	    			me.parentSelect.destroy();
					me.modifyWin = null;
	    		}
	    	});
		}
		
		me.modifyWin.show();
	},
	
	// 部门成员列表
	_deptMember: {},
	
	/**
	 * 选择部门成员
	 */
	member: function(deptid) {
		var me = this;
		if (null === this.memberWin) {
			me.SingleSelector = new _TOP.UserSelector(null, false);
			
			this.memberWin = Admin.window({
				id: 'dept-memberwin',
	    		width: 445,
	    		title: '部门成员',
	    		body: '<input type="hidden" name="deptid" /><p class="gray" style="margin:5px 0">注：一个帐号仅能属于一个部门，若加入已分配的帐号将会改变其所属部门。</p>',
	    		formid: 'dept-memberform',
	    		footer: '<input name="submit" type="submit" class="btn" value="确定"><input name="close" type="button" class="btn" value="关闭" />',
	    		action: BASE_PATH + '/user/department/member',
	    		draggable: true,
	    		onClose: function(){
					me.SingleSelector.reset();
					me.memberWin.destroy();
					me.memberWin = null;
				},
				onShow: function() {
					Frame.queryParent('#dept-memberform').find(':hidden[name="deptid"]').val(deptid);

					if (me._deptMember[deptid]) {
						if (me._deptMember[deptid].length) {
							me.SingleSelector.select(me._deptMember[deptid]);
	            		}
					} else {
						Department.deptMember(deptid);
					}
					Department.focusDept(deptid);
				},
	    		init: function() {
	    		    var form = this.find('form'),
	    		    	winbody = this.find('.window-body');

	    		    this.find('input[name="close"]').click(function() {
	    		    	me.memberWin.close();
	    		    });

	    		    me.SingleSelector.renderTo(winbody);
	    		    
	    		    form.submit(function(){return false;});
	    		    form.submit(function(){
	    		    	Department.saveMember(form);
	    			});
	    		}
			});
		}
		
		
		this.memberWin.show();
	},
	
	/**
	 * 获取部门成员
	 */
	deptMember: function(deptid) {
		var me = this;
		$.ajax({
	        type: 'GET',
	        dataType: 'json',
	        data: {deptid: deptid},
	        url: BASE_PATH + '/user/department/get.member',
	        success: function(ret) {
	            if (ret.success) {
	            	if (ret.data.userid) {
	            		if (ret.data.userid.length) {
	            			me.SingleSelector.select(ret.data.userid);
	            		}
	            		me._deptMember[deptid] = ret.data.userid;
	            	}
	            	Cookie.set('FOCUS-DEPT', deptid);
	            }
	        },
	        error: function(res) {
	        }
	    });
	},
	
	/**
	 * 负责人
	 */
	moderator: function(deptid) {
		var me = this;
		if (null === me.moderatorWin) {
			me.selector = new _TOP.UserSelector();
			
			me.moderatorWin = Admin.window({
				id: 'dept-moderatorwin',
	    		width: 445,
	    		title: '部门负责人',
	    		body: '<input type="hidden" name="deptid" />',
	    		formid: 'moderatorform',
	    		footer: '<input name="submit" type="submit" value="确定"><input name="close" type="button" value="关闭" />',
	    		action: BASE_PATH + '/user/department/moderator',
	    		draggable: true,
	    		onClose: function(){
					me.selector.reset();
					me.moderatorWin.destroy();
					me.moderatorWin = null;
				},
	    		init: function() {
	    		    var form = this.find('form'),
	    		    	winbody = this.find('.window-body');

	    		    this.find('input[name="close"]').click(function() {
	    		    	me.moderatorWin.close();
	    		    });
	    		    me.selector.renderTo(winbody);
	    		    
	    		    form.submit(function(){return false;});
	    		    form.submit(function(){
	    		    	Department.saveModerator(form);
	    			});
	    		}
			});
		}
		
		Frame.queryParent('#moderatorform').find(':hidden[name="deptid"]').val(deptid);
		var moderators = $('#moderator-' + deptid).val(),
			deptModerators = new Array();
		deptModerators = moderators.split(",");
		me.selector.select(deptModerators);
		
		Department.focusDept(deptid);
		
		me.moderatorWin.show();
	},
	
	/**
	 * 保存部门负责人
	 */
	saveModerator: function(form) {
		var uid = [],
			me = this,
			deptid = form.find('input[name="deptid"]').val();
		
		form.find(':hidden[name="userid[]"]').each(function(){
			uid.push(this.value);
		});
		
		if (!deptid) {
			Message.show('参数错误[deptid]');
			return false;
		}
		
		form.find('input, button').attr('disabled', true);
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {deptid: deptid, userid: uid.join(',')},
			url: form.attr('action'),
			success:function(ret) {
				Message.show(ret.message, 5000, ret.success);
				if (ret.success) {
					form.find('input, button').attr('disabled', false);
					$('div.dept-item').remove();
					Department.depts = [];
					for (var i=0; i<ret.data.length; i++) {
						if (ret.data[i].parentid == null) {
							ret.data[i].parentid = '';
						}
						Department.depts.push({deptid: ret.data[i].deptid.replace('^', '_'), deptname: ret.data[i].deptname, moderators: ret.data[i].moderators, parentid: ret.data[i].parentid.replace('^', '_'), ordernum: ret.data[i].ordernum, prefix: ret.data[i].prefix, firstnode: ret.data[i].firstnode, lastnode: ret.data[i].lastnode});
					}
					Department.initDeptList();
					me.moderatorWin.close();
					
					Cookie.set('FOCUS-DEPT', deptid);
				}
			},
			error: function(res) {
				Message.show(Message.PROCESSING_ERROR);
				form.find('input, button').attr('disabled', false);
				return false;
			}
		});
		
	},
	
	/**
	 * 保存成员
	 */
	saveMember: function(form) {
		var a = [],
			me = this,
			deptid = form.find('input[name="deptid"]').val();
		
		form.find(':hidden[name="userid[]"]').each(function(){
			a.push(this.value);
		});
		
		form.find('input, button').attr('disabled', true);
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {deptid: deptid, userid: a.join(',')},
			url: BASE_PATH + '/user/department/member',
			success:function(ret) {
				Message.show(ret.message, 5000, ret.success);
				if (ret.success) {
					form.find('input, button').attr('disabled', false);
					me.memberWin.close();
					me._deptMember[deptid] = ret.data.userid;
					
					Cookie.set('FOCUS-DEPT', deptid);
				}
			},
			error: function(res) {
				Message.show(Message.PROCESSING_ERROR);
				form.find('input, button').attr('disabled', false);
				return false;
			}
		});
	},
	
	/**
	 * 删除部门
	 * 
	 * @param {string} deptId
	 */
	deleteDept: function(deptId) {
		if (!confirm('确定删除此部门吗？')) {
			return false;
		}
		
		if (!deptId) {
			Message.show('参数错误[deptid]');
			return false;
		}
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {deptid: deptId},
			url: BASE_PATH + '/user/department/delete',
			success: function(ret) {
				Message.show(ret.message, 5000, ret.success);
				if (ret.success) {
					var o = $('#dept-' + deptId),
						p = o.parent('div.dept-children');
					
					if (ret.data) {
						Department.depts = [];
						for (var i=0; i<ret.data.length; i++) {
							if (ret.data[i].parentid == null) {
								ret.data[i].parentid = '';
							}
							Department.depts.push({deptid: ret.data[i].deptid.replace('^', '_'), deptname: ret.data[i].deptname, moderators: ret.data[i].moderators, parentid: ret.data[i].parentid.replace('^', '_'), ordernum: ret.data[i].ordernum, prefix: ret.data[i].prefix, firstnode: ret.data[i].firstnode, lastnode: ret.data[i].lastnode});
						}
						Department.initDeptList();
					}
					
					Cookie.set('FOCUS-DEPT', null);
				}
			},
			error: function(res) {
				Message.show(Message.PROCESSING_ERROR);
				return false;
			}
		});
	},
	
	/**
	 * 保存命名
	 * 
	 * @param {string} deptid
	 * @param {string} name
	 */
	saveDept: function() {
		var form = Frame.queryParent('#dept-modifyform');
		
		var name = form.find('input[name="name"]').val(),
			parentid = form.find('select[name="parentid"]').val();
		if (!Util.trim(name)) {
			return Message.show('请输入部门名称');
		}
		
		var data = form.serializeArray();
		form.find('input, button, select').attr('disabled', 'disabled');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: data,
			url: form.attr('action'),
			success: function(ret) {
				Message.show(ret.message, 5000, ret.success);
				if (ret.success && ret.data) {
					form.find('input, button, select').attr('disabled', false);
					$('div.dept-item').remove();
					Department.depts = [];
					for (var i=0; i<ret.data.depts.length; i++) {
						if (ret.data.depts[i].parentid == null) {
							ret.data.depts[i].parentid = '';
						}
						Department.depts.push({deptid: ret.data.depts[i].deptid.replace('^', '_'), deptname: ret.data.depts[i].deptname, moderators: ret.data.depts[i].moderators, parentid: ret.data.depts[i].parentid.replace('^', '_'), ordernum: ret.data.depts[i].ordernum, prefix: ret.data.depts[i].prefix, firstnode: ret.data.depts[i].firstnode, lastnode: ret.data.depts[i].lastnode});
					}
					
					Cookie.set('FOCUS-DEPT', ret.data.deptid);
					
					if (parentid) {
						Department.expandStatus[parentid] = true;
					}
					
					Department.initDeptList();
					Department.modifyWin.close();
				}
				
				form.find('input, button, select').removeAttr('disabled');
			},
			error: function(res) {
				Message.show(Message.PROCESSING_ERROR);
				//form.enable();
				form.find('input, button, select').removeAttr('disabled');
				return false;
			}
		});
	},
	
	/**
	 * 排序
	 */
	sortDept: function(deptid, type) {
		var o = $('#dept-' + deptid),
			parent = o.parent('.dept-children'),
			swap = type == 'up' ? o.prev() : o.next();
		
		if (!swap.size()) {
			return ;
		}
		
		if (type == 'up') {
			if (!o.next().size()) {
				swap.find('>table.table-list span.tree-list-icon').removeClass('ti-mid').addClass('ti-last');
				o.find('>table.table-list span.tree-list-icon').removeClass('ti-last').addClass('ti-mid');
			}
			o.insertBefore(swap);
		} else {
			if (!swap.next().size()) {
				o.find('>table.table-list span.tree-list-icon').removeClass('ti-mid').addClass('ti-last');
				swap.find('>table.table-list span.tree-list-icon').removeClass('ti-last').addClass('ti-mid');
			}
			o.insertAfter(swap);
		}
		
		o.find('a[name="up"],a[name="down"]').removeClass('lightgray');
		swap.find('a[name="up"],a[name="down"]').removeClass('lightgray');
		
		parent.find('>div.dept-item:eq(0) a[name="up"]').addClass('lightgray');
		parent.find('>div.dept-item:last-child a[name="down"]').addClass('lightgray');
		
		Department.focusDept(deptid);
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: BASE_PATH + '/user/department/sort',
			data: {deptid: deptid, type: type},
			success: function(){},
			error: function(){}
		});
	},
	
	focusDept: function(deptid) {
		$('#dept-list tr.focus').removeClass('focus');
		if ($('#dept-' + deptid.replace('^', '_')).size()) {
			$('#dept-' + deptid.replace('^', '_')).rowFocus();
		}
	}
};