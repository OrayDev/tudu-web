/**
 * 群组
 */
var Group = Group || {};

Group = {
	// 新建群组 窗口
	modifyWin: null,
	// 群组Id
	groupId: null,
	// 群组名称
	groupName: null,
	// 群组成员 窗口
	memberWin: null,
	// 成员选择列表
	selector: null,
	
	// 组成员列表
	_groupMember: {},
	
	tpl: {
		modify: '<input type="hidden" name="groupid" value="" /><p style="margin:30px 0;" align="center">群组名称：<input class="text" name="groupname" type="text" size="40"  maxlength="20" /></p>'
	},
	
	/**
	 * 初始化群组管理页面
	 */
	init: function() {
		$('input[name="create"]').click(function() {
			this.modifyWin = Group.groupModifyWin();
		});
		
		$('#group-list tr').rowHover();
		
		var fgid = Cookie.get('FOCUS-GROUP');
		if (fgid) {
			Group.focusGroup(fgid);
			Cookie.set('FOCUS-GROUP', null);
		}
	},
	
	/**
	 * 新建或修改群组名称 窗口显示
	 */
	groupModifyWin: function() {
		var me = this;
		
		if (null === me.modifyWin) {
			me.modifyWin = Admin.window({
	    		width: 400,
	    		id: 'group-modifywin',
	    		title: '编辑群组',
	    		body: me.tpl.modify,
	    		formid: 'group-modifyform',
	    		footer: '<input name="submit" type="submit" class="btn" value="确定"><input name="close" type="button" class="btn" value="关闭" />',
	    		action: BASE_PATH + '/user/group/create',
	    		draggable: true,
	    		onShow: function() {
					var form = Frame.queryParent('#group-modifyform');
					if(!me.groupId) {
						form.find('input[name="groupid"]').val('');
						form.find('input[name="groupname"]').val('');
						form.attr('action', BASE_PATH + '/user/group/create');
					} else {
						form.find('input[name="groupid"]').val(me.groupId);
						form.find('input[name="groupname"]').val(me.groupName);
						form.attr('action', BASE_PATH + '/user/group/update');
					}
				},
	    		onClose: function() {
					me.groupId = null;
					me.groupName = null;
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
	    		    	Group.modify(form);
	    			});
	    		}
	    	});
		}

		me.modifyWin.show();
	},
	
	/**
	 * 更新群组名称
	 */
	update: function(gid, gname) {
		this.groupId = gid;
		this.groupName = gname;
		
		Group.focusGroup(gid);
		
		if(!this.groupId) {
			Message.show('参数错误[groupid]');
		}
		
		Group.groupModifyWin();
	},
	
	/**
	 * 提交更新群组数据
	 */
	modify: function(form) {
		var me = this,
			name = form.find('input[name="groupname"]').val(),
			grupid = form.find('input[name="groupid"]').val(),
			data = form.serializeArray();
		
		if (!name) {
			Message.show('请输入 群组名称');
			form.find('input[name="groupname"]').focus();
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
						Cookie.set('FOCUS-GROUP', ret.data);
					}
					location.reload();
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
	 * 删除群组
	 */
	del: function(gid) {
		if(!gid) {
			Message.show('参数错误[groupid]');
		}
		
		if (!confirm('确定删除此群组吗？')) {
			return false;
		}
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {groupid: gid},
			url: BASE_PATH + '/user/group/delete',
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
	 * 排序
	 */
	sortGroup: function(gid, sort) {
		Group.focusGroup(gid);
		
		$.ajax({
	        type: 'POST',
	        dataType: 'json',
	        data: {
	           'groupid': gid,
	           'sort': sort
	        },
	        url: BASE_PATH + '/user/group/sort',
	        success: function(ret) {
	            if (ret.success) {
	                location.reload();
	            }
	        },
	        error: function(res){}
	    });
	},
	
	/**
	 * 群组成员管理 - 窗口
	 */
	member: function(groupid) {
		this.groupId = groupid;
		var me = this;
		
		Group.focusGroup(groupid);
		
		if (null === me.memberWin) {
			me.memberWin = Admin.window({
	    		width: 445,
	    		id: 'group-memberwin',
	    		title: '群组成员管理',
	    		body: '<input type="hidden" name="groupid" />',
	    		formid: 'group-memberform',
	    		footer: '<input name="submit" type="submit" class="btn" value="确定"><input name="close" type="button" class="btn" value="关闭" />',
	    		action: BASE_PATH + '/user/group/update.member',
	    		draggable: true,
	    		onShow: function() {
					Frame.queryParent('input[name="groupid"]').val(me.groupId);
				},
	    		onClose: function() {
					me.groupId = null;
					Frame.queryParent('input[name="groupid"]').val('');
					me.selector.reset();
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
	    		    	
	    		    	Group.saveMember(form, members);
	    			});
	    		}
	    	});
		}
		
		if (me._groupMember[me.groupId]) {
			me.selector.select(me._groupMember[me.groupId]);
		} else {
			Group.groupMember(me.groupId);
		}
		
		me.memberWin.show();
	},
	
	/**
	 * 获取群组成员
	 */
	groupMember: function(gid) {
		var me = this;
		$.ajax({
	        type: 'GET',
	        dataType: 'json',
	        data: {groupid: gid},
	        url: BASE_PATH + '/user/group/get.member',
	        success: function(ret) {
	            if (ret.success) {
	            	me.selector.select(ret.data.userid);
	            	me._groupMember[gid] = ret.data.userid;
	            	Cookie.set('FOCUS-GROUP', gid);
	            }
	        },
	        error: function(res) {
	        }
	    });
	},
	
	/**
	 * 保存群组成员
	 */
	saveMember: function(form, members) {
		var groupid = form.find('input[name="groupid"]').val(),
			data = form.serializeArray(),
			me = this;
		
		if (!groupid) {
			Message.show('参数错误[groupid]');
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
	                location.reload();
	            }
	        },
	        error: function(res) {
	        	Message.show(Message.PROCESSING_ERROR);
	        	form.find('input, button').attr('disabled', false);
	        	return false;
	        }
	    });
	},
	
	focusGroup: function(groupid){
		groupid = groupid.replace('^', '_');
		$('#group-list tr.focus').removeClass('focus');
		$('#group-' + groupid).rowFocus();
	}
};