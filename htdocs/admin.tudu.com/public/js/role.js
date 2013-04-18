/**
 * 权限
 */
var Role = Role || {};

Role = {
	// 新建权限窗口
	modifyWin: null,
	// 权限Id
	roleId: null,
	// 权限名称
	roleName: null,
	// 权限组成员窗口
	memberWin: null,
	// 成员选择列表
	selector: null,
	
	_roleMember: {},
	
	tpl: {
		modify: '<input type="hidden" name="roleid" value="" /><p style="margin:30px 0;" align="center">权限组名称：<input class="text" name="rolename" type="text" size="40" maxlength="50" /></p>'
	},
	
	/**
	 * 初始化权限管理页面
	 */
	init: function() {
		$('input[name="create"]').click(function() {
			this.modifyWin = Role.roleModifyWin();
		});
		
		$('#role-list tr').rowHover();
		
		var frid = Cookie.get('FOCUS-ROLE');
		if (frid) {
			Role.focusRole(frid);
			Cookie.set('FOCUS-ROLE', null);
		}
	},
	
	initModify: function() {
		$('input[name="cancel"]').click(function() {
			location = BASE_PATH + '/user/role';
		});
		
		$('input[name="save"]').click(function() {
			Role.saveAccess('#accessform');
		});
		
		$('fieldset.form-field table tr').bind('mouseover', function(){
			$(this).find('span.role-intro').show();
		})
		.bind('mouseout', function(){
			$(this).find('span.role-intro').hide();
		});
		
		new FixToolbar({
			target: 'div.tool-btm'
		});
	},
	
	/**
	 * 保存权限组的用户权限
	 */
	saveAccess: function(form) {
		var form = $(form),
			roleid = form.find('input[name="roleid"]').val(),
			data = form.serializeArray();
		
		if (!roleid) {
			Message.show('参数错误[roleid]');
			return false;
		}
		
		form.disable();
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: data,
			url: form.attr('action'),
			success: function(ret) {
				Message.show(ret.message, 5000, ret.success);
				if(ret.success) {
					location = BASE_PATH + '/user/role';
				} else {
					form.enable();
				}
			},
			error: function(res) {
				Message.show(Message.PROCESSING_ERROR);
				form.enable();
				return false;
			}
		});	
	},
	
	/**
	 * 新建或修改权限组名称 窗口显示
	 */
	roleModifyWin: function() {
		var me = this;

		if (null === me.modifyWin) {
			me.modifyWin = Admin.window({
	    		width: 400,
	    		id: 'role-modifywin',
	    		title: '编辑权限组',
	    		body: me.tpl.modify,
	    		formid: 'role-modifyform',
	    		footer: '<input name="submit" type="submit" class="btn" value="确定"><input name="close" type="button" class="btn" value="关闭" />',
	    		action: BASE_PATH + '/user/role/create',
	    		draggable: true,
	    		onShow: function() {
					var form = Frame.queryParent('#role-modifyform');
					if(!me.roleId) {
						form.find('input[name="roleid"]').val('');
						form.find('input[name="rolename"]').val('');
						form.attr('action', BASE_PATH + '/user/role/create');
					} else {
						form.find('input[name="roleid"]').val(me.roleId);
						form.find('input[name="rolename"]').val(me.roleName);
						form.attr('action', BASE_PATH + '/user/role/update');
						Role.focusRole(me.roleId);
					}
				},
	    		onClose: function() {
					me.roleId = null;
					me.roleName = null;
					me.modifyWin.destroy();
					me.modifyWin = null;
				},
	    		init: function() {
	    		    var form = this.find('form');

	    		    this.find('input[name="close"]').click(function() {
	    		    	me.modifyWin.close();
	    		    });

	    		    form.submit(function(){return false;});
	    		    form.submit(function(){
	    		    	Role.modify(form);
	    			});
	    		}
	    	});
		}

		me.modifyWin.show();
	},
	
	/**
	 * 更新权限组名称
	 */
	update: function(rid, rname) {
		this.roleId = rid;
		this.roleName = rname;
		
		if(!this.roleId) {
			Message.show('参数错误[roleid]');
		}
		
		Role.roleModifyWin();
	},
	
	/**
	 * 提交更新权限组数据
	 */
	modify: function(form) {
		var me = this,
			name = form.find('input[name="rolename"]').val(),
			data = form.serializeArray();
		
		if (!name) {
			Message.show('请输入 权限组名称');
			form.find('input[name="rolename"]').focus();
			return false;
		}
		form.find('input, button').attr('disabled', true);
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: data,
			url: form.attr('action'),
			success: function(ret) {
				Message.show(ret.message, 5000, ret.success);
				if(ret.success) {
					form.find('input, button').attr('disabled', false);
					me.modifyWin.close();
					
					if (ret.data) {
						Cookie.set('FOCUS-ROLE', ret.data.roleid);
					}
					
					location.reload();
					//$('.table-list').append('<tr id="'+ret.data.roleid+'"><td class="td-first"><div class="td-space"><span class="icon icon-group-senior"></span>&nbsp;'+ret.data.rolename+'</div></td><td class="td-last"><div class="td-space"><a href="/user/role/modify.access?roleid='+ret.data.roleid+'">[权限]</a> <a href="javascript:void(0);" onclick="Role.member('+ret.data.roleid+')">[成员]</a> <a href="javascript:void(0);" onclick="Role.update(\''+ret.data.roleid+'\', \''+ret.data.rolename+'\');">[重命名]</a> <a href="javascript:void(0);" onclick="Role.del(\''+ret.data.roleid+'\');">[删除]</a></div></td></tr>');
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
	 * 删除权限组
	 */
	del: function(rid) {
		if(!rid) {
			Message.show('参数错误[roleid]');
		}
		
		if (!confirm('确定删除此权限组吗？')) {
			return false;
		}
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {roleid: rid},
			url: BASE_PATH + '/user/role/delete',
			success: function(ret) {
				Message.show(ret.message, 5000, ret.success);
				if(ret.success) {
					location.reload();
				}
			},
			error: function(res) {
				Message.show(Message.PROCESSING_ERROR);
				return false;
			}
		});
	},
	
	/**
	 * 权限组成员 窗口
	 */
	member: function(roleid) {
		var me = this;
		me.roleId = roleid;
		Role.focusRole(roleid);
		
		if (null === me.memberWin) {
			me.memberWin = Admin.window({
	    		width: 445,
	    		id: 'role-memberwin',
	    		title: '权限组成员',
	    		body: '<input type="hidden" name="roleid" />',
	    		formid: 'role-memberform',
	    		footer: '<input name="submit" type="submit" class="btn" value="确定"><input name="close" type="button" class="btn" value="关闭" />',
	    		action: BASE_PATH + '/user/role/update.member',
	    		draggable: true,
	    		onShow: function() {
					me.memberWin = me.memberWin;
					Frame.queryParent('input[name="roleid"]').val(me.roleId);
				},
	    		onClose: function() {
					Frame.queryParent('input[name="roleid"]').val('');
					me.selector.reset();
					me.roleId = null;
					me.memberWin.destroy();
					me.memberWin = null;
				},
	    		init: function() {
	    		    var form = this.find('form'),
	    		    	winbody = this.find('.window-body');
	
	    		    this.find('input[name="close"]').click(function() {
	    		    	me.memberWin.close();
	    		    });
	    		    
	    		    me.selector = new _TOP.UserSelector();
	    		    me.selector.renderTo(winbody);
	
	    		    form.submit(function(){return false;});
	    		    form.submit(function(){
	    		    	var members = me.selector.getUserId();
	    		    	
	    		    	Role.saveMember(form, members);
	    			});
	    		}
	    	});
		}
		
		if (me._roleMember[roleid]) {
			me.selector.select(me._roleMember[roleid]);
		} else {
			Role.roleMember(roleid);
		}
		
		me.memberWin.show();
	},
	
	/**
	 * 获取权限组成员
	 */
	roleMember: function(rid) {
		var me = this;
		$.ajax({
	        type: 'GET',
	        dataType: 'json',
	        data: {roleid: rid},
	        url: BASE_PATH + '/user/role/get.member',
	        success: function(ret) {
	        	//Message.show(ret.message, 5000, ret.success)
	            if (ret.success) {
	            	me.selector.select(ret.data.userid);
	            	me._roleMember[rid] = ret.data.userid;
	            }
	        },
	        error: function(res) {
	        }
	    });
	},
	
	/**
	 * 保存权限组成员
	 */
	saveMember: function(form, members) {
		var roleid = form.find('input[name="roleid"]').val(),
			data = form.serializeArray(),
			me = this;
		
		if (!roleid) {
			Message.show('参数错误[roleid]');
			return false;
		}
		
		form.find('input, button').attr('disabled', true);
		$.ajax({
	        type: 'POST',
	        dataType: 'json',
	        data: data,
	        url: form.attr('action'),
	        success: function(ret) {
	        	Message.show(ret.message, 5000, ret.success)
	            if (ret.success) {
	            	form.find('input, button').attr('disabled', false);
	            	me.memberWin.close();
	            	Cookie.set('FOCUS-ROLE', roleid);
	            	delete me._roleMember[roleid];
	            }
	        },
	        error: function(res) {
	        	Message.show(Message.PROCESSING_ERROR);
	        	form.find('input, button').attr('disabled', false);
	        	return false;
	        }
	    });
	},
	
	focusRole: function(roleid) {
		$('#role-list tr.focus').removeClass('focus');
		$('#role-' + roleid.replace('^', '_')).rowFocus();
	}
};