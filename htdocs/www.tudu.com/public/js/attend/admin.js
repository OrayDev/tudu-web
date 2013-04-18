var Attend = Attend || {};

Attend.Admin = {
	
	userWin: null,
	
	selector: null,
	
	_data: null,
	
	/**
	 * 读取用户信息
	 */
	loadUsers: function(callback) {
		var me = this;
		
		if (null === this._data) {
			$.ajax({
				type: 'GET',
				dataType: 'json',
				url: '/admin/user/user/struct.load?group=1',
				success: function(ret) {
					if (ret.success && ret.data) {
						me._data = {
							user: ret.data.users,
							group: ret.data.groups
						};
						
						callback(me._data);
					}
				},
				error: function(res) {}
			});
		} else {
			callback(me._data);
		}
	},
	
	/**
	 * 选择联系人连接
	 * 
	 * @return
	 */
	userSelectWin: function(title, input) {
		var me = this, selected = [];
		
		if (input) {
			var val = input.target.val();
			val = val.split("\n");
			if (val.length) {
				for (var i = 0, l = val.length; i < l; i++) {
					var v = val[i];
					if (-1 != v.indexOf('@')) {
						v = v.split('@')[0];
					} else {
						v = 'group_' + v;
					}
					
					selected.push(v);
				}
			}
		}

		if (null === me.userWin) {
			me.selector = new _TOP.UserSelector(null, true, null, true);
			
			me.userWin = Admin.window({
				id: 'attend-userwin',
	    		width: 445,
	    		title: title,
	    		body: '<input type="hidden" name="deptid" />',
	    		formid: 'moderatorform',
	    		footer: '<input name="submit" type="submit" value="确定"><input name="close" type="button" value="关闭" />',
	    		action: '?',
	    		draggable: true,
	    		onClose: function() {
					me.selector.reset();
					me.userWin.destroy();
					me.userWin = null;
				},
	    		init: function() {
	    		    var form = this.find('form'),
	    		    	winbody = this.find('.window-body');

	    		    this.find('input[name="close"]').click(function() {
	    		    	me.userWin.close();
	    		    });
	    		    me.selector.renderTo(winbody);
	    		    
	    		    form.bind('submit', function() {return false;});
	    		    if (input) {
		    		    form.bind('submit', function() {
		    		    	input.clear();
		    		    	winbody.find('div.select-box[_name="select-box"] a.select-item').each(function() {
		    		    		var o = $(this),
		    		    			name = o.text(),
		    		    			id   = o.find('input[name="userid[]"]').size()
		    		    				 ? o.find('input[name="address[]"]').val().replace('.tudu.com', '')
		    		    				 : o.find('input[name="groupid[]"]').val();
		    		    		var title = (id && -1 != id.indexOf('@')) ? id : '群组';
		    		    		
		    		    		input.appendItem(name, {_id: id, name: name, title: title});
		    		    	});
		    		    	
		    		    	me.userWin.close();
		    		    });
	    		    }
	    		}
			});
		}
		
		if (selected.length) {
	    	me.selector.select(selected);
	    }
		
		me.userWin.show();
	},

	/**
	 * 创建联系人输入
	 */
	createInput: function(obj, id) {
		var me     = this;
		var input = new $.mailinput({
			id: id,
			target: obj,
			onAppend: function(item) {
				me.checkInputValue(this, item);
			},
			onUpdate: function() {
				me.updateInput(input);
			},
			onRemove: function() {
				me.updateInput(input);
			},
			autoComplete: {
				data: me._data,
				loadMethod: function() {
					var inst    = this;
					var keyword = this.target.val();
					me.loadUsers(function(){
						inst.data = me._data;
						
						inst._initMatchList(keyword);
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
		        	group: '{groupname}  <span class="gray">&lt;群组&gt;</span>'
		        },
		        onSelect: function(item){
		        	var data = item.data;
		        	var name = data.truename ? data.truename : data.groupname;
	                var id   = data.username ? data.username : (data.email ? data.email : data.groupid);
	                var title= -1 != id.indexOf('@') ? id : '群组:' + id;
	                
	                input.setText('');
		        	input.appendItem(name, {name: name, _id: id, title: title});
		        }
			}
		});
		
		return input;
	},

	checkInputValue: function(input, item) {
		var me = this,
			id = item.attr('_id'),
			match = false;
		
		this.loadUsers(function() {
			var data = me._data;
			if (id) {
				if (-1 != id.indexOf('@')) {
					var users = data.user;
					for (var i = 0, l = users.length; i < l; i++) {
						if (id == users[i].username) {
							match = true;
							break;
						}
					}
				} else {
					var groups = data.group;
					for (var i = 0, l = groups.length; i < l; i++) {
						if (id == groups[i].groupid) {
							match = true;
							break;
						}
					}
				}
			} else {
				var text = item.text().replace(';', '');
				var users = data.user;
				for (var i = 0, l = users.length; i < l; i++) {
					if (text == users[i].truename || text == users[i].username) {
						item.attr('_id', users[i].username);
						item.attr('title', users[i].username);
						item.text(users[i].truename + ';');
						input.setText('');
						match = true;
						break;
					}
				}
			}
			
			if (!match) {
				item.addClass('mail_item_error');
			} else {
				id = item.attr('_id');
				if (id && input.getItems('[_id="'+id+'"]').size() > 1) {
					item.remove();
				}
			}
			
			me.updateInput(input);
		});
	},

	updateInput: function(input) {
		var target = input.target,
			arr    = [];
		input.getItems().each(function() {
			arr.push($(this).attr('_id'));
		});

		target.val(arr.join("\n"));
	},

	fillMailInput: function(input) {
		var items  = input.target.val(),
			users  = this._data.user,
			groups = this._data.group;
		items = items.split("\n");

		for (var i = 0, l = items.length; i < l; i++) {
			var id = items[i];
			
			if (!id) {
				continue ;
			}

			if (-1 != id.indexOf('@')) {
				for (var j = 0, l = users.length; j < l; j++) {
					if (users[j].username == id) {
						input.appendItem(users[j].truename, {_id: id, name: users[j].truename, title: id});
					}
				}
			} else {
				for (var j = 0, l = groups.length; j < l; j++) {
					if (groups[j].groupid == id) {
						input.appendItem(groups[j].groupname, {_id: id, name: groups[j].groupname, title: '群组'});
					}
				}
			}
		}
	},
	
	init: function() {
		var me = this;
		
		var adminInput = this.createInput($('#admin'), 'admin-input');
		var defInput   = this.createInput($('#def'), 'def-input');
		var scInput    = this.createInput($('#sc'), 'sc-input');
		var sumInput   = this.createInput($('#sum'), 'sum-input');
		
		adminInput._input.bind('focus blur focusin focusout', function(e) {
			var hint = $(this).closest('tr').find('span.gray');
			if (e.type == 'focus' || e.type == 'focusin') {
				hint.show();
			} else {
				hint.hide();
			}
		});
		defInput._input.bind('focus blur focusin focusout', function(e) {
			var hint = $(this).closest('tr').find('span.gray');
			if (e.type == 'focus' || e.type == 'focusin') {
				hint.show();
			} else {
				hint.hide();
			}
		});
		scInput._input.bind('focus blur focusin focusout', function(e) {
			var hint = $(this).closest('tr').find('span.gray');
			if (e.type == 'focus' || e.type == 'focusin') {
				hint.show();
			} else {
				hint.hide();
			}
		});
		sumInput._input.bind('focus blur focusin focusout', function(e) {
			var hint = $(this).closest('tr').find('span.gray');
			if (e.type == 'focus' || e.type == 'focusin') {
				hint.show();
			} else {
				hint.hide();
			}
		});
		
		$('#link-admin').bind('click', function(){
			me.userSelectWin($(this).text(), adminInput);
		});
		
		$('#link-def').bind('click', function(){
			me.userSelectWin($(this).text(), defInput);
		});
		
		$('#link-sc').bind('click', function(){
			me.userSelectWin($(this).text(), scInput);
		});
		
		$('#link-sum').bind('click', function(){
			me.userSelectWin($(this).text(), sumInput);
		});
		
		$('#more').bind('click', function(){
			if (this.checked) {
				$('#more-tbody').show();
			} else {
				$('#more-tbody').hide();
			}
		});
		
		$('#theform').bind('submit', function() {return false;});
		$('#theform').bind('submit', function() {
			var form = $(this);
			var data = form.serializeArray();
			var status   = $('input[name="status"]:checked').val();
			var custatus = $('#currentstatus').val();
			var msg      = '';

			if (custatus != status) {
				if (status == 2) {
					msg = '保存设置后，所有员工均不可使用该应用，是否确认保存？';
				} else {
					msg = '保存设置后，将在次日开始生效，是否确认保存？';
				}
				
				if (!confirm(msg)) {
					return ;
				}
			}
			
			form.find('input').attr('disabled', 'disabled');
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: data,
				url: '/app/attend/admin/save',
				success: function(ret) {
					Message.show(ret.message, 5000, ret.success);
					
					if (ret.success) {
						location = '/admin/appstore/appstore/list/';
					} else {
						form.find('input').removeAttr('disabled');
					}
				},
				error: function(res) {
					form.find('input').removeAttr('disabled');
				}
			});
		});
		
		this.loadUsers(function() {
			me.fillMailInput(adminInput);
			me.fillMailInput(defInput);
			me.fillMailInput(scInput);
			me.fillMailInput(sumInput);
		});
		
		// 显示提示
		var isShow = Cookie.get('TUDU-APPTIPS-ATTEND');
		if (!isShow) {
			var tipsWin = Admin.window({
				id: 'attend-tips',
	    		width: 445,
	    		title: '提示',
	    		body: '<div style="padding: 10px;line-height: 24px">因考勤应用设计审批设置、排班设置等，因此请确保每个部门负责人均不为空，以防使用考勤申请时出错。</div>',
	    		footer: '<div style="float:right"><input type="button" value="设置部门负责人" id="btn-dept" /><input class="close" name="close" type="button" value="确定" /></div><div style="text-align:left;padding: 0 0 0 10px;"><label for="check-hint"><input type="checkbox" name="hint-display" id="check-hint" />不再提醒 </label></div>',
	    		action: '?',
	    		draggable: true,
	    		onClose: function() {
					if (Frame.queryParent('input[name="hint-display"]:checked').size()) {
						Cookie.set('TUDU-APPTIPS-ATTEND', 1, {expires: 86400 * 365});
					}
					
					tipsWin.destroy();
				},
				init: function() {
					this.find('#btn-dept').bind('click', function() {
						location = BASE_PATH + '/user/department';
						tipsWin.close();
					});
				}
			});
			tipsWin.show();
		}
	},
	
	initApp: function() {
		var initWin = Admin.window({
			id: 'attend-init',
    		width: 350,
    		title: '初始化考勤应用',
    		body: '<div style="padding: 10px;line-height: 24px">系统正在初始化应用环境，请勿刷新页面。</div>',
    		footer: '',
    		action: '',
    		closeable: false,
    		draggable: true,
    		onClose: function() {
				initWin.destroy();
			}
		});
		initWin.show();
		
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: '/app/attend/admin/init',
			success: function(ret) {
				Message.show(ret.message, 5000, ret.success);
				initWin.close();
				
				if (ret.success) {
					location.assign(location.href);
				}
			},
			error: function(res) {
				//location.assign(location.href);
			}
		});
	}

}