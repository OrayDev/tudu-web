/**
 * 考勤应用 - 排班设置
 *
 * @version    $Id: plan.source.js 2767 2013-03-06 09:30:50Z chenyongfa $
 */
var Attend = Attend || {};

var TOP = typeof(getTop) == 'function' ? getTop() : parent;

Attend.Plan = {
	/**
	 * 排班方案选择窗口
	 */
	_scheduleWin: null,
	
	/**
	 * 排班方案选择窗口模板
	 */
	_scheduleWinTpl: ['<div class="pop pop_linkman">', '<div class="pop_header"><strong>选择排班方案</strong><a class="icon icon_close close"></a></div>', '<div class="pop_body" style="padding:10px;">', '<div id="schedule-list-tree" class="calendar_cast_tree_panel" style="width:210px;"></div>', '</div>', '<div class="pop_footer"><button type="button" name="confirm" class="btn">' + TOP.TEXT.CONFIRM + '</button><button type="button" class="btn close">' + TOP.TEXT.CANCEL + '</button></div>', '</div>'].join(''),
	
	/**
	 * 排班方案树
	 */
	_scheduleTree: null,
	
	/**
	 * 当前选择的排班方案Id
	 */
	_currSelectScid: null,
	
	/**
	 * 排班方案列表
	 */
	_schedules: {},
	
	/**
	 * 排班计划
	 */
	_plans: [],
	
	/**
	 * 保存的表格状态
	 */
	_gridData: null,
	
	/**
	 * 保存的人员
	 */
	_gridUsers: [],
	
	/**
	 * 当前的添加的排班方案
	 */
	_currSchedules: null,
	
	/**
	 * 部门ID
	 */
	deptIds: null,
	
	/**
	 * 用户ID
	 */
	uniqueIds: null,
	
	_lang: {
		missing_users: '没有选择关联的用户',
		weekdays: ['一', '二', '三', '四', '五', '六', '日']
	},
	
	/**
	 * 设置语言
	 * @param {Object} lang
	 */
	setLang: function(lang){
		var _o = this;
		for (var i in lang) {
			_o._lang[i] = lang[i];
		}
	},
	
	/**
	 * 初始化页面
	 */
	init: function(role, back){
		var me = this;
		// 保存
		$('#save, #save-leave').bind('click', function(){
			me.save(this.id == 'save');
		});
		
		$(document.body).bind('click', function(){
			$('#color_panel').hide(300);
		});
		$('#color_panel').bind('click', function(e){
			TOP.stopEventBuddle(e);
		});
		
		// 月排班
		$('#cycle-month').bind('click', function(){
			$('#week-option').hide();
			$('#month-option').show();
			$('#cyclenum').val(1);
			me.initGrid(1, $('#plan-table tr.sc-tr').size(), 1);
		});
		
		// 周排班
		$('#cycle-week').bind('click', function(){
			$('#month-option').hide();
			$('#week-option').show();
			me.initGrid('week', $('#plan-table tr.sc-tr').size(), 1);
		});
		
		$('#year, #month').bind('change', function(){
			var ids = [];
			$('input[name^="user-"]').each(function(){
				ids.push(this.value);
			});
			
			url = '/app/attend/schedule/plan';
			
			if (ids.length) {
				url += '?email=' + ids.join(',');
				
				var year = $('#year').val(), month = $('#month').val();
				
				url += '&year=' + year + '&month=' + month + '&back=' + back;
				location.assign(url);
			}
			//me.initGrid(1, $('#plan-table tr.sc-tr').size(), 1);
		});
		
		// 添加方案
		$('#add-schedule').bind('click', function(){
			var selected = [];
			$('#schedule-list a').each(function(){
				var id = $(this).attr('_scid');
				selected.push(id);
			});
			me.scheduleSelectWin(selected);
		});
		
		// 添加用户
		$('#add-user').bind('click', function(){
			me.userSelectWin(role, false);
		});
		
		$('#checkall').click(function(){
			TOP.checkBoxAll('member[]', this.checked, document.body);
		});
		
		// 添加周期循环
		$('#add-cyclenum').bind('click', function(){
			var cycleNum = parseInt($('#cyclenum').val());
			
			if (cycleNum >= 4) {
				return;
			}
			cycleNum++;
			$('#cyclenum').val(cycleNum);
			
			me.saveGrid();
			me.initGrid('week', $('#plan-table tr.sc-tr').size(), cycleNum);
			$('#remove-cyclenum').show();
			me.restoreGrid();
		});
		$('#remove-cyclenum').bind('click', function(){
			var cycleNum = parseInt($('#cyclenum').val());
			
			if (cycleNum <= 1) {
				cycleNum = 1;
			}
			else {
				cycleNum--;
			}
			
			me.saveGrid();
			$('#cyclenum').val(cycleNum);
			me.initGrid('week', $('#plan-table tr.sc-tr').size(), cycleNum);
			me.restoreGrid();
			
			if (cycleNum <= 1) {
				$(this).hide();
			}
		});
		
		if (me.uniqueIds !== null) {
			me.setGridUsers(me.uniqueIds);
		}
		else {
			var selectScids = [];
			if (me._plans.length > 0) {
				selectScids = this.getSelectScids();
			}
			
			this.refreshScheduleList(selectScids);
		}
	},
	
	/**
	 * 获取选中的方案
	 */
	getSelectScids: function(){
		if (this._plans.length <= 0) {
			return;
		}
		
		var selectScids = [], plans = this._plans;
		
		for (var i = 0; i < plans.length; i++) {
			var plan = plans[i];
			for (var unid in plan) {
				for (var j = 0; j < plan[unid].length; j++) {
					var item = plan[unid][j];
					if (!TOP.Util.inArray(item.scid, selectScids)) {
						selectScids.push(item.scid);
					}
				}
			}
		}
		
		return selectScids;
	},
	
	/**
	 * 排班方案选择窗口
	 */
	scheduleSelectWin: function(selected){
		var me = this;
		
		me._scheduleWin = TOP.appendWindow('schedule-win', me._scheduleWinTpl, {
			width: 255,
			draggable: true,
			onShow: function(){
				me.initScheduleTree(selected);
				
				me._scheduleWin.find('button[name="confirm"]').bind('click', function(){
					var scheduleids = [];
					me._scheduleWin.find('input[name="scheduleid[]"]').each(function(){
						if (this.value != '^root' && this.value) {
							scheduleids.push(this.value);
						}
					});
					
					if (!scheduleids.length) {
						TOP.showMessage('请至少选择一个方案');
						return false;
					}
					
					me.refreshScheduleList(scheduleids);
					me._scheduleWin.close();
				});
			},
			onClose: function(){
				me._scheduleTree.clear();
				me._scheduleTree = null;
				me._scheduleWin.destroy();
				me._scheduleWin = null;
			}
		});
		
		me._scheduleWin.show();
	},
	
	/**
	 * 初始化排班方案选择树
	 */
	initScheduleTree: function(selected){
		var me = this, _$ = TOP.getJQ(), schedules = this._schedules;
		
		me._scheduleTree = new _$.tree({
			id: 'schedule-tree',
			idKey: 'id',
			idPrefix: 'schedule-',
			cls: 'cast-tree'
		});
		
		for (var i in schedules) {
			var item = schedules[i];
			var node = new _$.treenode({
				data: {
					id: 's-' + item.scheduleId.replace('^', '_'),
					scheduleid: item.scheduleId,
					name: item.name,
					title: item.title,
					parentid: item.parentid
				},
				isLeaf: false,
				content: '<input type="checkbox" name="scheduleid[]" value="{scheduleid}" /><span title="{title}">{name}</span>',
				events: {
					click: function(e){
						me._scheduleTree.find(this.id.replace('schedule-', ''), true).toggle();
						TOP.stopEventBuddle(e);
					}
				}
			});
			
			if (item.parentid) {
				var parent = me._scheduleTree.find('s-' + item.parentid.replace('^', '_'), true);
				if (parent) {
					parent.appendChild(node);
				}
				else {
					me._scheduleTree.appendNode(node);
				}
			}
			else {
				me._scheduleTree.appendNode(node);
			}
			
			var checkbox = new _$.checkbox({
				name: 'scheduleid[]',
				id: 'schedule-' + item.scheduleId.replace('^', '_'),
				replace: node.ele.find(':checkbox[name="scheduleid[]"]'),
				states: {
					normal: {
						value: '',
						cls: ''
					},
					half: {
						value: '',
						cls: 'checkbox-half'
					},
					checked: {
						value: item.scheduleId,
						cls: 'checkbox-checked'
					}
				}
			});
			checkbox.bind('click', function(e){
				if (this.state() === 'half') {
					this.state('checked');
				}
				TOP.stopEventBuddle(e);
			});
		}
		
		var root = me._scheduleTree.find('s-_root', true);
		if (root) {
			root.expand();
		}
		
		me._scheduleTree.appendTo(_$('#schedule-list-tree'));
		
		if (selected.length > 0) {
			for (var i = 0; i < selected.length; i++) {
				TOP.getCheckbox('id', 'schedule-' + selected[i].replace('^', '_'), _$('#schedule-tree')).state('checked');
			}
			_checkParent('_root');
		}
		
		var scheduleChecks = TOP.getCheckbox('name', 'scheduleid[]', _$('#schedule-tree'));
		scheduleChecks.bind('click', function(){
			var el = _$('#schedule-s-' + this.id.replace('schedule-', ''));
			_checkAll(el.find('ul'), this.state());
			
			var node = me._scheduleTree.find('s-' + this.id.replace('schedule-', ''), true);
			
			if (node.get('parentid')) {
				_checkParent(node.get('parentid'));
			}
			
			var ct = node.get('parentid') ? _$('#schedule-s-' + node.get('parentid')) : _$('#schedule-tree');
			
			if (_checkIsAll(_$('#schedule-tree'))) {
				TOP.getCheckbox('id', 'schedule-_root', ct).state('checked');
			}
			else {
				TOP.getCheckbox('id', 'schedule-_root', ct).state('normal');
			}
		});
		
		function _checkParent(scheduleid){
			scheduleid = scheduleid.replace('^', '_');
			var node = me._scheduleTree.find('s-' + scheduleid, true), a = TOP.getCheckbox('id', 'schedule-' + scheduleid, _$('#schedule-tree')), ct = _$('#schedule-s-' + scheduleid + ' ul'), st;
			
			if (_checkIsAll(ct)) {
				st = 'checked';
			}
			else 
				if (ct.find('div.checkbox-checked').size()) {
					st = 'half';
				}
				else {
					st = 'normal';
				}
			
			a.state(st);
			
			if (node.get('parentid')) {
				_checkParent(node.get('parentid'));
			}
		}
		
		function _checkIsAll(ct){
			var schedules = TOP.getCheckbox('name', 'scheduleid[]', ct), isall = true;
			
			schedules.each(function(){
				if (this.state() !== 'checked') {
					isall = false;
					return;
				}
			});
			
			return isall;
		}
		
		function _checkAll(ct, state){
			TOP.getCheckbox('name', 'scheduleid[]', ct).state(state);
		}
	},
	
	/**
	 * 选择颜色
	 */
	selectColor: function(obj, realTime, callback){
		var block = $(obj), panel = $('#color_panel'), offset = block.offset(), me = this;
		
		if ($('#color_panel:visible').size()) {
			panel.hide();
		}
		
		panel.css({
			top: offset.top + block.height() + 'px',
			left: offset.left + 'px'
		}).show(300);
		
		panel.find('div.color_block').unbind('click').bind('click', function(){
			var color = $(this).find('input[name="color"]').val();
			
			panel.hide(300);
			if (!realTime) {
				$('#theform').find('input[name="bgcolor"]').val(color);
				block.css('background-color', color);
			}
			else {
				var scheduleid = typeof block.parent().attr('_scid') != 'undefined' ? scheduleid = block.parent().attr('_scid') : scheduleid = block.attr('_scid');
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: '/app/attend/schedule/updatecolor',
					data: {
						scheduleid: scheduleid,
						bgcolor: color
					},
					success: function(ret){
						TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
						if (ret.success) {
							block.css('background-color', color);
							if (typeof callback == 'function') {
								callback.call(me, scheduleid, color);
							}
						}
					},
					error: function(res){
					}
				});
			}
		});
	},
	
	/**
	 * 免签班
	 */
	initExemption: function(role){
		var me = this;
		// 添加用户
		$('#add-user').bind('click', function(){
			me.userSelectWin(role, true);
		});
		
		// 移除列表用户
		$('#user-box').bind('click', function(e){
			var src = e.srcElement ? $(e.srcElement) : $(e.target);
			
			var item = src.closest('p');
			
			item.remove();
		}).bind('mouseover mouseout', function(e){
			var src = e.srcElement ? $(e.srcElement) : $(e.target);
			src = src.closest('p');
			
			if (e.type == 'mouseover') {
				src.addClass('over');
			}
			else {
				src.removeClass('over');
			}
		});
		
		$('#theform').submit(function(){
			return false;
		});
		
		$('.color_grid').click(function(e){
			me.selectColor(this, true);
			TOP.stopEventBuddle(e);
		});
		
		$(document.body).bind('click', function(){
			$('#color_panel').hide(300);
		});
		$('#color_panel').bind('click', function(e){
			TOP.stopEventBuddle(e);
		});
		
		// 保存
		$('#save').bind('click', function(){
			me.saveExemption();
		});
	},
	
	/**
	 * 保存免签班
	 */
	saveExemption: function(){
		var form = $('#theform');
		var data = form.serializeArray();
		
		if (!$('input[name="user[]"]').size()) {
			return TOP.showMessage(this._lang.missing_users);
		}
		
		form.find(':input:not([_disabled])').attr('disabled', true);
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: data,
			url: form.attr('action'),
			success: function(ret){
				TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
				form.find(':input:not([_disabled])').attr('disabled', false);
				location = '/app/attend/schedule/index';
			},
			error: function(res){
				TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
				form.find(':input:not([_disabled])').attr('disabled', false);
			}
		});
	},
	
	/**
	 * 设置方案列表
	 */
	setSchedules: function(schedules){
		this._schedules = schedules;
	},
	
	/**
	 * 用户排班计划
	 *
	 * @param {Object} plans
	 */
	setUserPlans: function(plans){
		this._plans = plans;
	},
	
	/**
	 * 保存设置
	 */
	save: function(stay){
		if (!$('input[name="user[]"]').size()) {
			return TOP.showMessage(this._lang.missing_users);
		}
		
		var gd = $('#grid-data');
		var colNum = this.grid.getConfig('cell');
		var type = $('input[name="type"]:checked').val();
		gd.find('input').remove();
		gd.append('<input name="valuenum" type="hidden" value="' + colNum + '" />');
		
		var gridData = this.grid.getGridData();
		for (var row in gridData) {
			for (var cell in gridData[row]) {
				var sid = gridData[row][cell];
				var val = '^off';
				var member = $('input[_user="u-' + sid.user + '"]').val();
				
				if (typeof sid.scid != 'undefined' && sid.scid != '') {
					val = sid.scid;
					if (!val) {
						val = '^off';
					}
				}
				
				c = parseInt(cell) + 1;
				if (type == 0 && c % 7 == 0) {
					c = c - 7;
				}
				gd.append('<input name="value-' + member + '-' + c + '" type="hidden" value="' + val + '" />');
			}
		}
		
		var form = $('#theform');
		var data = form.serializeArray();
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: data,
			url: form.attr('action'),
			success: function(ret){
				TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
				if (ret.success) {
					if (stay) {
						location = '/app/attend/schedule/plan';
					}
					else {
						location = '/app/attend/schedule/user';
					}
				}
			},
			error: function(res){
				TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
	},
	
	/**
	 * 更新方案的颜色
	 *
	 * @param {Object} scid
	 * @param {Object} color
	 */
	updateScheduleColor: function(scid, color){
		if (typeof this._schedules[scid] != 'undefined') {
			this._schedules[scid].color = color;
		}
	},
	
	/**
	 * 刷新方案列表
	 */
	refreshScheduleList: function(scheduleids){
		var me = this, panel = $('#schedule-list');
		
		if (typeof scheduleids != 'undefined' && scheduleids.length > 0) {
			panel.empty();
			this._currSchedules = new Array();
			for (var i = 0, c = scheduleids.length; i < c; i++) {
				if (typeof this._schedules[scheduleids[i]] != 'undefined') {
					var schedule = this._schedules[scheduleids[i]];
					if (schedule.scheduleId != '^root') {
						panel.append('<a _scid="' + schedule.scheduleId + '" href="javascript:void(0)" title="' + schedule.title + '"><span class="color_grid" style="background:' + schedule.color + '"></span><span>' + schedule.name + '</span></a>');
						this._currSchedules.push(schedule.scheduleId);
						panel.find('a[_scid="' + schedule.scheduleId + '"] span.color_grid').bind('click', function(e){
							me.selectColor(this, true, function(scheduleid, color){
								me.updateScheduleColor(scheduleid, color);
								if (me.grid) {
									me.grid.updateSchedules(this._schedules);
									me.grid.updateGridColor(scheduleid, color);
								}
							});
							TOP.stopEventBuddle(e);
						});
					}
				}
			}
			
			panel.find('a').each(function(){
				$(this).bind('click', function(){
					var scheduleId = $(this).attr('_scid');
					me._currSelectScid = scheduleId;
					me.grid.setCurrentSchedule(scheduleId);
				});
			});
		}
		
		me.saveGrid();
		this.initGrid($('input[name="type"]:checked').val(), $('#plan-table tr.sc-tr').size(), $('#cyclenum').val());
		me.restoreGrid();
		
		if (me._plans.length > 0) {
			me.selectGridTd(me._plans);
		}
	},
	
	/**
	 * 刷新控件
	 */
	refreshGrid: function(selected){
		var table = $('#plan-table'), groupIds = [], me = this, type = $('input[name="type"]:checked').val();
		
		if (type == 1) {
			var ids = [], needLoadUser = [];
			$('input[name^="user-"]').each(function(){
				ids.push(this.value);
			});
		}
		
		this.saveGrid();
		table.find('tr.sc-tr').remove();
		
		this._gridUsers = [];
		for (var i = 0; i < selected.length; i++) {
			var se = selected[i];
			if (se.groupid) {
				groupIds.push(se.groupid);
			}
			else {
				if (type == 1 && !TOP.Util.inArray(se.email, ids)) {
					needLoadUser.push(se.email);
				}
				this.appendGridUsers(table, i, se.email, se.name);
			}
		}
		
		if (groupIds.length > 0) {
			this.getGroupUsers(groupIds, function(ret){
				if (ret.success && ret.data) {
					var users = ret.data;
					
					TOP.Cast.load(function(){
						var castUsers = TOP.Cast.get('users');
						for (var uid in users) {
							for (var i = 0, l = castUsers.length; i < l; i++) {
								if (!TOP.Util.inArray(users[uid].email, me._gridUsers) && castUsers[i].uniqueid == users[uid].uniqueid) {
									if (type == 1 && !TOP.Util.inArray(users[uid].email, ids)) {
										needLoadUser.push(users[uid].email);
									}
									me.appendGridUsers(table, i, users[uid].email, users[uid].truename);
								}
							}
						}
						
						var len = me._gridUsers.length;
						$('#grid-ct').attr('rowspan', len + 1);
						me.initGrid(type, len, $('#cyclenum').val());
						me.restoreGrid();
						
						if (type == 1 && needLoadUser.length > 0) {
							me.loadUserPlans(needLoadUser, function(ret){
								if (ret.success && ret.data) {
									me.chooseGridTd(ret.data);
								}
							});
						}
					});
				}
			});
		}
		else {
			var len = this._gridUsers.length;
			$('#grid-ct').attr('rowspan', len + 1);
			this.initGrid(type, len, $('#cyclenum').val());
			this.restoreGrid();
			
			if (type == 1 && needLoadUser.length > 0) {
				me.loadUserPlans(needLoadUser, function(ret){
					if (ret.success && ret.data) {
						me.chooseGridTd(ret.data);
					}
				});
			}
		}
	},
	
	/**
	 * ajax后，选中
	 */
	chooseGridTd: function(plans){
		if (!this.grid) {
			return;
		}
		
		var table = $('#plan-table'), selectScids = [];
		for (var userName in plans) {
			var row = table.find('.sc-td[_uid="' + userName + '"]').parent().prevAll().length - 1, plan = plans[userName];
			
			for (var day in plan) {
				if (typeof this._schedules[plan[day]] != 'undefined') {
					var scheduleId = this._schedules[plan[day]].scheduleId, cell = parseInt(day) - 1;
					
					this.grid.select(row, cell, scheduleId, userName);
					if (!TOP.Util.inArray(plan[day], selectScids)) {
						selectScids.push(plan[day]);
					}
				}
			}
		}
		
		$('#schedule-list a').each(function(){
			var scid = $(this).attr('_scid');
			if (!TOP.Util.inArray(scid, selectScids)) {
				selectScids.push(scid);
			}
		});
		
		if (selectScids.length > 0) {
			this.refreshScheduleList(selectScids);
		}
	},
	
	/**
	 * 加载用户排班计划
	 *
	 * @param {Object} needLoadUser
	 */
	loadUserPlans: function(needLoadUser, callback){
		var me = this;
		var year = $('#year').val(), month = $('#month').val(), url = '/app/attend/schedule/loadplans?email=' + needLoadUser.join(',');
		
		url += '&year=' + year + '&month=' + month;
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: url,
			success: function(ret){
				if (typeof callback == 'function') {
					callback.call(me, ret);
				}
			},
			error: function(res){
				TOP.showMessage('加载用户排班计划失败');
			}
		});
	},
	
	/**
	 * 设置控件用户
	 */
	setGridUsers: function(ids){
		var me = this, table = $('#plan-table');
		table.find('tr.sc-tr').remove();
		this._gridUsers = [];
		
		TOP.Cast.load(function(){
			var users = TOP.Cast.get('users');
			for (var i = 0, l = users.length; i < l; i++) {
				if (TOP.Util.inArray(users[i].uniqueid, ids)) {
					var name = '<span title="' + users[i].truename + '&lt;' + users[i].username + '&gt;">' + users[i].truename + '</span>';
					table.append('<tr class="sc-tr"><td align="right" class="td-cp sc-td" _uid="' + users[i].username + '" _unid="' + users[i].uniqueid + '"><label for="u-' + i + '"><input type="hidden" name="user-' + i + '" value="' + users[i].username + '" /><input type="hidden" name="user[]" value="' + i + '" /><input _user="u-' + users[i].username + '" id="u-' + i + '" type="checkbox" name="member[]" value="' + i + '" />' + name + '</label></td></tr>');
					me._gridUsers.push(users[i].username);
				}
			}
			
			var len = me._gridUsers.length;
			$('#grid-ct').attr('rowspan', len + 1);
			
			var selectScids = [];
			if (me._plans.length > 0) {
				selectScids = me.getSelectScids();
			}
			
			me.refreshScheduleList(selectScids);
		});
	},
	
	/**
	 * 添加用户
	 */
	appendGridUsers: function(table, member, email, name){
		var nameHtml = '<span title="' + name + '&lt;' + email + '&gt;">' + name + '</span>';
		table.append('<tr class="sc-tr"><td align="right" class="td-cp sc-td" _uid="' + email + '"><label for="u-' + member + '"><input type="hidden" name="user-' + member + '" value="' + email + '" /><input type="hidden" name="user[]" value="' + member + '" /><input _user="u-' + email + '" id="u-' + member + '" type="checkbox" name="member[]" value="' + member + '" />' + nameHtml + '</label></td></tr>');
		this._gridUsers.push(email);
	},
	
	/**
	 * 获取群组用户
	 */
	getGroupUsers: function(groupid, callback){
		var me = this;
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: '/app/attend/schedule/getgroupusers?groupid=' + groupid.join(','),
			success: function(ret){
				if (typeof callback == 'function') {
					callback.call(me, ret);
				}
			},
			error: function(res){
				TOP.showMessage('加载群组用户失败');
			}
		});
	},
	
	/**
	 * 保存表格状态
	 */
	saveGrid: function(){
		if (!this.grid) {
			return;
		}
		
		this._gridData = this.grid.getGridData();
	},
	
	/**
	 * 恢复格子选定状态
	 */
	restoreGrid: function(){
		if (!this.grid || this._gridData === null) {
			return;
		}
		
		if (this._gridData) {
			for (var row in this._gridData) {
				for (var cell in this._gridData[row]) {
					var sid = this._gridData[row][cell];
					if (typeof sid.scid != 'undefined' && sid.scid != '') {
						if (this._currSchedules && !TOP.Util.inArray(sid.scid, this._currSchedules)) {
							continue;
						}
						if (this._gridUsers.length > 0 && !TOP.Util.inArray(sid.user, this._gridUsers)) {
							continue;
						}
						else 
							if (this._gridUsers.length > 0 && TOP.Util.inArray(sid.user, this._gridUsers)) {
								for (var i = 0; i < this._gridUsers.length; i++) {
									if (sid.user == this._gridUsers[i]) {
										sid.row = i;
									}
								}
							}
						
						this.grid.select(sid.row, cell, sid.scid, sid.user);
					}
				}
			}
			
			this._gridData = [];
		}
		
		if (this._gridUsers) {
			this._gridUsers = [];
		}
	},
	
	/**
	 * 选中表格
	 *
	 * @param {Object} plans
	 */
	selectGridTd: function(){
		if (!this.grid || this._plans.length <= 0) {
			return;
		}
		
		var table = $('#plan-table'), plans = this._plans;
		
		for (var i = 0; i < plans.length; i++) {
			var plan = plans[i];
			for (var unid in plan) {
				var row = table.find('.sc-td[_unid="' + unid + '"]').parent().prevAll().length - 1, userName = table.find('.sc-td[_unid="' + unid + '"]').attr('_uid');
				
				for (var j = 0; j < plan[unid].length; j++) {
					var item = plan[unid][j];
					if (typeof this._schedules[item.scid] != 'undefined') {
						var scheduleId = this._schedules[item.scid].scheduleId, cell = parseInt(item.day) - 1;
						
						this.grid.select(row, cell, scheduleId, userName);
					}
				}
			}
		}
		
		this._plans = [];
	},
	
	/**
	 * 初始化控件
	 *
	 * @param {Object} type
	 * @param {Object} rowNum
	 * @param {Object} cycleNum
	 */
	initGrid: function(type, rowNum, cycleNum){
		var header = null, colHeader = [], weekends = [], me = this;
		
		if (type == 'week' || type == 0) {
			header = this._lang.weekdays;
			
			for (var i = 0, l = cycleNum; i < l; i++) {
				colHeader = colHeader.concat(header);
				
				weekends.push(5 + i * 7);
				weekends.push(6 + i * 7);
			}
		}
		else {
			var year = $('#year').val(), month = parseInt($('#month').val());
			
			var temp = new Date();
			temp.setFullYear(year, month, 0);
			var days = temp.getDate();
			
			for (var i = 0; i < days; i++) {
				colHeader[colHeader.length] = i + 1;
				
				temp.setDate(i + 1);
				var weekday = temp.getDay();
				if (weekday == 0 || weekday == 6) {
					weekends.push(i);
				}
			}
		}
		
		$('#schedule-grid').empty();
		this.grid = new SelectGrid({
			id: 'plan-grid',
			cls: 'timegrid',
			row: rowNum,
			cell: colHeader.length,
			colHeader: colHeader,
			rowMultiSelect: true,
			draggable: true,
			schedules: me._schedules
		});
		this.grid.appendTo('#schedule-grid');
		
		var g = $('#plan-grid');
		for (var i = 0, l = weekends.length; i < l; i++) {
			g.find('tr').each(function(){
				$(this).find('td:eq(' + weekends[i] + ')').addClass('grid-weekend');
			});
		}
		
		if (me._currSelectScid) {
			me.grid.setCurrentSchedule(me._currSelectScid);
		}
	},
	
	/**
	 * 设置用户列表
	 */
	setUsers: function(ids){
		if (!ids || ids.length <= 0) {
			return;
		}
		
		this.uniqueIds = ids;
	},
	
	/**
	 * 设置显示的部门
	 *
	 * @param {Object} ids
	 */
	setDepts: function(ids){
		if (!ids || ids.length <= 0) {
			return;
		}
		
		this.deptIds = ids;
	},
	
	/**
	 * 用户选择窗口
	 */
	userSelectWin: function(isGroup, exemption){
		if (!isGroup) {
			isGroup = false;
		}
		
		// 是否为免签方案
		if (!exemption) {
			exemption = false;
		}
		
		var html = '<div class="pop pop_linkman"><div class="pop_header"><strong>' + TOP.TEXT.SELECT_CONTACT + '</strong><a class="icon icon_close close"></a></div><div class="pop_body"></div><div class="pop_footer"><button type="button" name="confirm" class="btn">' + TOP.TEXT.CONFIRM + '</button><button type="button" class="btn close">' + TOP.TEXT.CANCEL + '</button></div></div>';
		var Win = TOP.Frame.TempWindow;
		Win.append(html, {
			width: 470,
			draggalbe: true,
			onShow: function(){
				Win.center();
			},
			onClose: function(){
				Win.destroy();
			}
		});
		
		var selected = [];
		// 免签方案
		if (exemption) {
			var userbox = $('#user-box');
			$('input:hidden[name="user[]"]').each(function(){
				selected.push({
					_id: this.value
				});
			});
			// 自定义排班设置
		}
		else {
			$('input[name="user[]"]').each(function(){
				var userName = $('input[name="user-' + this.value + '"]').val();
				selected.push({
					_id: userName
				});
			});
		}
		
		var selector = new TOP.ContactSelector({
			appendTo: Win.find('div.pop_body'),
			enableGroup: isGroup,
			selected: selected,
			panels: ['common'],
			childOf: this.deptIds
		});
		
		Win.show();
		
		Win.find('button[name="confirm"]').bind('click', function(){
			var selected = selector.getSelected();
			// 免签方案
			if (exemption) {
				userbox.empty();
				for (var i = 0, l = selected.length; i < l; i++) {
					var se = selected[i];
					var id = se.groupid ? se.groupid : se.email, email = se.groupid ? TOP.TEXT.GROUP : se.email;
					userbox.append('<p><input type="hidden" name="user[]" value="' + id + '" />' + se.name + '<span class="gray">&lt;' + email + '&gt;</span></p>');
				}
				// 自定义排班设置
			}
			else {
				Attend.Plan.refreshGrid(selected);
			}
			Win.close();
		});
	},
	
	/**
	 * 初始化列表
	 */
	initList: function(currUrl){
		TOP.keyhint('#keyword', 'gray', true, document.body);
		$('#user-list tr').mousemove(function(){
			$(this).addClass("current");
		}).mouseout(function(){
			$(this).removeClass("current");
		});
		
		$('button[name="modify"]').bind('click', function(){
			var ids = [];
			$('input[name="uniqueid"]').each(function(){
				if (this.checked) {
					ids.push(this.value);
				}
			});
			
			url = '/app/attend/schedule/plan?back=' + currUrl;
			
			if (ids.length) {
				url += '&uniqueid=' + ids.join(',');
				
				var year = $('#year').val(), month = $('#month').val();
				
				url += '&year=' + year + '&month=' + month;
			}
			
			location.assign(url);
		});
	}
};

/**
 * 表格选择
 * @return
 */
var SelectGrid = function(params) {
	this._cfg = $.extend({}, SelectGrid.defaultConfig, params);
	this._initTable();
};
SelectGrid.defaultConfig = {
	row: 7,
	cell: 24,
	cls: '',
	rowHeaderWidth: 36,
	rowHeader: null,
	colHeader: null,
	rowMultiSelect: true,// 行是否允许多选
	colMultiSelect: true, // 列是否允许多选
	draggable: true
};
SelectGrid.prototype = {

	/**
	 * @type {Object}
	 */
	_gridData: null,
	
	/**
	 * @type {Object}
	 */
	_cfg: null,
	
	/**
	 *
	 */
	_table: null,
	
	/**
	 *
	 */
	_start: null,
	
	/**
	 *
	 */
	_startHeader: null,
	
	/**
	 *
	 */
	_enabled: true,
	
	/**
	 * 当前选择的方案
	 *
	 * @type {Object}
	 */
	_currentSchedule: null,
	
	/**
	 * 初始化表格
	 */
	_initTable: function(){
		var r = this._cfg.row, c = this._cfg.cell;
		
		this._gridData = new Array();
		this._table = document.createElement('table');
		this._table.className = this._cfg.cls, this._table.cellPadding = 0, this._table.cellSpacing = 0, this._table.border = 0, this._table.onselectstart = function(){
			return false;
		};
		
		if (this._cfg.id) {
			this._table.id = this._cfg.id;
		}
		
		if (!this._cfg.rowHeader) {
			c--;
		}
		
		if (!this._cfg.colHeader) {
			r--;
		}
		
		// 行列
		for (var i = 0; i <= r; i++) {
			this._gridData[i] = new Array();
			var tr = this._table.insertRow(i);
			
			if (i == r) {
				tr.className += ' last';
			}
			if (i == 0) {
				tr.className += ' timegrid-time';
			}
			
			for (var j = 0; j <= c; j++) {
				var isth = ((this._cfg.colHeader && i == 0) || (this._cfg.rowHeader && j == 0)), cell = (isth ? document.createElement('th') : document.createElement('td'));
				
				tr.appendChild(cell);
				if (isth) {
					if (i == 0) {
						if (!this._cfg.rowHeader || (this._cfg.colHeader && undefined != this._cfg.colHeader[j - 1])) {
							var num = this._cfg.rowHeader ? j - 1 : j;
							cell.innerHTML = '<div>' + this._cfg.colHeader[num] + '</div>';
							cell._cell = num;
						}
						else {
							cell.innerHTML = '<div align="right"><span class="time-angle"></span></div>';
						}
					}
					else {
						if (this._cfg.rowHeader && undefined != this._cfg.rowHeader[i - 1]) {
							var num = this._cfg.colHeader ? i - 1 : i;
							cell.innerHTML = '<div>' + this._cfg.rowHeader[num] + '</div>';
							cell._row = num;
						}
					}
					
					if (j == 0 && this._cfg.rowHeader && this._cfg.colHeader) {
						cell.width = this._cfg.rowHeaderWidth;
					}
				}
				else {
					cell._row = this._cfg.colHeader ? i - 1 : i;
					cell._cell = this._cfg.rowHeader ? j - 1 : j;
					var div1 = document.createElement('div');
					var div2 = document.createElement('div');
					
					div1.style.cssText = "display:none;";
					div1.className = 'over';
					
					div2.style.cssText = "display:block;";
					div2.className = 'select';
					div2.setAttribute('_scid', '');
					
					cell.appendChild(div1);
					cell.appendChild(div2);
					
					var userMember = $('input[name="user[]"]:eq(' + cell._row + ')').val();
					var userName = $('input[name="user-' + userMember + '"]').val();
					this._gridData[cell._row][cell._cell] = {
						row: cell._row,
						user: userName,
						scid: ''
					};
				}
				if (j == c) {
					cell.className = 'last';
				}
			}
		}
		
		var td = $(this._table).find('td'), colHeader = this._cfg.rowHeader ? $(this._table).find('tr:eq(0) th:not(:eq(0))') : $(this._table).find('tr:eq(0) th'), rowHeader = $(this._table).find('tr:not(:eq(0)) th');
		var me = this, cfg = this._cfg;
		td.bind('mousedown', function(){
			if (!me._enabled) {
				return;
			}
			
			if (!me.checkCurrentSchedule() || !me.checkHasUsers()) {
				return;
			}
			
			var d = $(this), selected = d.hasClass('enable');
			me._start = {
				row: d.attr('_row'),
				cell: d.attr('_cell'),
				enable: !d.hasClass('enable'),
				scheduleId: d.find('div.select').attr('_scid')
			};
			
			// 不允许行多选
			if (!cfg.rowMultiSelect) {
				me.unselectColumn(me._start.cell);
			}
			
			// 不允许列多选
			if (!cfg.colMultiSelect) {
				me.unselectRow(me._start.row);
			}
			
			var sc = me.getCurrentSchedule();
			var row = d.parent("tr").prevAll().length - 1;
			var cell = d.prevAll().length;
			var userMember = $('input[name="user[]"]:eq(' + row + ')').val();
			var userName = $('input[name="user-' + userMember + '"]').val();
			
			if (d.find('div.select').attr('_scid') != sc.scheduleId) {
				selected = false;
				me._start.enable = true;
			}
			
			if (selected) {
				d.removeClass('enable');
				d.find('div.select').attr('_scid', '').css({
					'background': ''
				});
				me._gridData[row][cell] = {
					'row': row,
					'user': userName,
					'scid': ''
				};
			}
			else {
				d.addClass('enable');
				d.find('div.select').attr('_scid', sc.scheduleId).css({
					'background': sc.color
				});
				me._gridData[row][cell] = {
					'row': row,
					'user': userName,
					'scid': sc.scheduleId
				};
			}
			
			// 是否批量
			if (me.isBatch()) {
				var batch = me.getBatchRow();
				if (TOP.Util.inArray(row, batch)) {
					var table = $(me._table);
					for (var i = 0; i < batch.length; i++) {
						var td = table.find('tr:eq(' + (batch[i] + 1) + ') td:eq(' + cell + ')'), userMember = $('input[name="user[]"]:eq(' + batch[i] + ')').val(), userName = $('input[name="user-' + userMember + '"]').val();
						if (selected) {
							td.removeClass('enable');
							td.find('div.select').attr('_scid', '').css({
								'background': ''
							});
							me._gridData[batch[i]][cell] = {
								'row': batch[i],
								'user': userName,
								'scid': ''
							};
						}
						else {
							td.addClass('enable');
							td.find('div.select').attr('_scid', sc.scheduleId).css({
								'background': sc.color
							});
							me._gridData[batch[i]][cell] = {
								'row': batch[i],
								'user': userName,
								'scid': sc.scheduleId
							};
						}
					}
				}
			}
		});
		
		td.bind('mouseover', function(){
			if (!me._start) {
				$(this).addClass('over');
				$(this).find('div.over').show();
				$(this).find('div.select').hide();
				return false;
			}
			
			if (!me.checkCurrentSchedule() || !me.checkHasUsers()) {
				return;
			}
			
			if (cfg.draggable) {
				var d = $(this), table = $(me._table), row = d.attr('_row'), cell = d.attr('_cell');
				
				var minRow = Math.min(me._start.row, row) + 1, maxRow = Math.max(me._start.row, row) + 1, minCell = Math.min(me._start.cell, cell), maxCell = Math.max(me._start.cell, cell);
				
				table.find('td div.select').removeClass('enable');
				table.find('td div.select').removeClass('disable');
				
				var r, c;
				var cls = me._start.enable ? 'enable' : 'disable';
				var sc = me.getCurrentSchedule();
				var scheduleId = me._start.enable ? sc.scheduleId : '';
				for (r = minRow; r <= maxRow; r++) {
					var mtr = table.find('tr:eq(' + r + ')'), userMember = $('input[name="user[]"]:eq(' + (r - 1) + ')').val(), userName = $('input[name="user-' + userMember + '"]').val();
					isChecked = $('input[name="member[]"]:eq(' + (r - 1) + ')').is(':checked');
					for (c = minCell; c <= maxCell; c++) {
						mtr.find('td:eq(' + c + ') div.select').addClass(cls).attr('_scid', scheduleId).css({
							'background': color
						});
						me._gridData[r - 1][c] = {
							row: r - 1,
							user: userName,
							scid: scheduleId
						};
						
						if (isChecked) {
							var batch = me.getBatchRow();
							var table = $(me._table);
							for (var i = 0; i < batch.length; i++) {
								var td = table.find('tr:eq(' + (batch[i] + 1) + ') td:eq(' + c + ')'), member = $('input[name="user[]"]:eq(' + batch[i] + ')').val(), user = $('input[name="user-' + member + '"]').val();
								
								td.find('div.select').addClass(cls).attr('_scid', scheduleId).css({
									'background': color
								});
								me._gridData[batch[i]][c] = {
									'row': batch[i],
									'user': user,
									'scid': scheduleId
								};
							}
						}
					}
				}
			}
		}).bind('mouseout', function(){
			$(this).removeClass('over');
			$(this).find('div.over').hide();
			$(this).find('div.select').show();
		});
		
		// 全选
		if (this._cfg.colHeader && this._cfg.rowHeader) {
			$(this._table).find('tr:eq(0) th:eq(0)').bind('mouseover', function(){
				$(this).addClass('over');
			}).bind('mouseout', function(){
				$(this).removeClass('over');
			}).bind('click', function(){
				me.selectAll();
				me.onChange();
			});
		}
		
		if (this._cfg.colHeader && this._cfg.rowMultiSelect) {
			colHeader.each(function(i){
				var o = $(this), col = o.attr('_cell');
				o.bind('mousedown', function(){
					if (!me.checkCurrentSchedule() || !me.checkHasUsers()) {
						return;
					}
					var enable = me.selectColumn(i);
					
					me._startHeader = {
						type: 'col',
						index: col,
						enable: enable
					};
				}).bind('mouseover', function(){
					o.addClass('over');
					/*$(me._table).find('tr').each(function(){
					 $(this).find('td:eq('+col+')').addClass('over');
					 $(this).find('td:eq('+col+') div.over').show();
					 $(this).find('td:eq('+col+') div.select').hide();
					 });*/
					var table = $(me._table);
					
					table.find('td div.select').removeClass('enable');
					table.find('td div.select').removeClass('disable');
					if (me._startHeader && me._startHeader.type == 'col') {
						var maxCol = Math.max(me._startHeader.index, col), minCol = Math.min(me._startHeader.index, col);
						var cls = me._startHeader.enable ? 'enable' : 'disable';
						var sc = me.getCurrentSchedule();
						var scheduleId = me._startHeader.enable ? sc.scheduleId : '';
						for (var i = 1, c = me._cfg.row; i <= c; i++) {
							var userMember = $('input[name="user[]"]:eq(' + (i - 1) + ')').val(), userName = $('input[name="user-' + userMember + '"]').val();
							for (var j = minCol; j <= maxCol; j++) {
								table.find('tr:eq(' + i + ') td:eq(' + j + ') div.select').addClass(cls).attr('_scid', scheduleId).css({
									'background': color
								});
								me._gridData[i - 1][j] = {
									row: i - 1,
									user: userName,
									scid: scheduleId
								};
							}
						}
					}
				}).bind('mouseout', function(){
					o.removeClass('over');
					/*$(me._table).find('tr').each(function(){
					 $(this).find('td:eq('+col+')').removeClass('over');
					 $(this).find('td:eq('+col+') div.over').hide();
					 $(this).find('td:eq('+col+') div.select').show();
					 });*/
				});
			});
		}
		
		if (this._cfg.rowHeader && this._cfg.colMultiSelect) {
			rowHeader.each(function(i){
				var o = $(this), parent = o.parent(), row = o.attr('_row');
				o.bind('mousedown', function(){
					if (!me.checkCurrentSchedule() || !me.checkHasUsers()) {
						return;
					}
					var enable = me.selectRow(i);
					
					me._startHeader = {
						type: 'row',
						index: row,
						enable: enable
					};
				}).bind('mouseover', function(){
					o.addClass('over');
					parent.find('td').addClass('over');
					
					var table = $(me._table);
					
					table.find('td div.select').removeClass('enable');
					table.find('td div.select').removeClass('disable');
					if (me._startHeader && me._startHeader.type == 'row') {
						var maxRow = Math.max(me._startHeader.index, row) + 1, minRow = Math.min(me._startHeader.index, row) + 1;
						var cls = me._startHeader.enable ? 'enable' : 'disable';
						var sc = me.getCurrentSchedule();
						var scheduleId = me._startHeader.enable ? sc.scheduleId : '';
						for (var i = minRow; i <= maxRow; i++) {
							var userMember = $('input[name="user[]"]:eq(' + (i - 1) + ')').val(), userName = $('input[name="user-' + userMember + '"]').val();
							for (var j = 0, c = me._cfg.cell; j < c; j++) {
								table.find('tr:eq(' + i + ') td:eq(' + j + ') div.select').addClass(cls).attr('_scid', scheduleId).css({
									'background': color
								});
								me._gridData[i - 1][j] = {
									row: i - 1,
									user: userName,
									scid: scheduleId
								};
							}
						}
					}
				}).bind('mouseout', function(){
					o.removeClass('over');
					parent.find('td').removeClass('over');
				});
			});
		}
		
		$(window).bind('mouseup', _evtMouseUp);
		$(document.body).bind('mouseup', _evtMouseUp);
		
		function _evtMouseUp(){
			var isChange = me._start != null || me._startHeader != null, table = $(me._table);
			me._start = null;
			me._startHeader = null;
			
			var sc = me.getCurrentSchedule();
			table.find('td div.enable').each(function(){
				$(this).parent('td').addClass('enable');
				$(this).attr('_scid', sc.scheduleId).css({
					'background': sc.color
				});
			});
			table.find('td div.disable').each(function(){
				$(this).parent('td').removeClass('enable');
				$(this).attr('_scid', '').css({
					'background': ''
				});
			});
			table.find('td div.select').removeClass('enable');
			table.find('td div.select').removeClass('disable');
			
			if (isChange) {
				me.onChange();
			}
		}
	},
	
	/**
	 * 设置当前操作的方案
	 *
	 * @param {Object} scheduleId
	 */
	setCurrentSchedule: function(scheduleId){
		var schedules = this._cfg.schedules;
		this._currentSchedule = schedules[scheduleId];
	},
	
	/**
	 * 返回当前操作的方案
	 */
	getCurrentSchedule: function(){
		return this._currentSchedule;
	},
	
	/**
	 * 检查是否有设置当前操作的方案
	 */
	checkCurrentSchedule: function(){
		if (!this._currentSchedule) {
			if (!$('#schedule-list a').size()) {
				TOP.showMessage('请先添加排班方案', 5000);
			}
			else {
				TOP.showMessage('请选择排班方案', 5000);
			}
			return false;
		}
		
		return true;
	},
	
	/**
	 * 检查是否有设置用户
	 */
	checkHasUsers: function(){
		if (!$('input[name="user[]"]').size()) {
			TOP.showMessage('请添加人员', 5000);
			return false;
		}
		
		return true;
	},
	
	onChange: function(){
		if (typeof this._cfg.onChange == 'function') {
			this._cfg.onChange.call(this);
		}
	},
	
	/**
	 * 获取配置内容
	 */
	getConfig: function(name){
		return this._cfg[name];
	},
	
	/**
	 * 获取表格数据
	 */
	getGridData: function(){
		return this._gridData;
	},
	
	/**
	 * 遍历所有行
	 */
	eachRow: function(callback){
		if (typeof(callback) != 'function') {
			return;
		}
		
		var index = 0, me = this;
		$(this._table).find('tr:gt(0)').each(function(){
			callback.call(me, index)
			index++;
		});
	},
	
	/**
	 * 获取指定行选定单元的索引数组
	 *
	 * @param int index
	 * @return Array
	 */
	getRowSelected: function(index){
		var ret = [];
		$(this._table).find('tr:eq(' + (index + 1) + ') td.enable').each(function(){
			ret.push($(this).attr('_cell'));
		});
		
		return ret;
	},
	
	/**
	 * 获取某列选定单元的行索引
	 *
	 * @param int index
	 * @return Array
	 */
	getColumnSelected: function(index){
		var ret = [];
		$(this._table).find('tr:gt(0)').each(function(i){
			var tr = $(this);
			if (tr.find('td:eq(' + index + ')').hasClass('enable')) {
				var scheduleId = tr.find('td:eq(' + index + ') div.select').attr('_scid'), userMember = $('input[name="user[]"]:eq(' + i + ')').val(), userName = $('input[name="user-' + userMember + '"]').val();
				ret.push({
					'row': i,
					'user': userName,
					'scid': scheduleId
				});
			}
		});
		
		return ret;
	},
	
	/**
	 * 更新方案
	 * @param {Object} schedules
	 */
	updateSchedules: function(schedules){
		if (typeof schedules != 'undefined') {
			this._cfg.schedules = schedules;
		}
	},
	
	/**
	 * 更新表格的颜色
	 * @param {Object} scid
	 * @param {Object} color
	 */
	updateGridColor: function(scid, color){
		$(this._table).find('td div.select[_scid="' + scid + '"]').css({
			'background': color
		});
	},
	
	/**
	 * 选定
	 */
	select: function(row, cell, scheduleId, userName){
		var a = $(this._table).find('tr:eq(' + (row + 1) + ') td:eq(' + cell + ')'), schedules = this._cfg.schedules, color = schedules[scheduleId].color;
		a.addClass('enable');
		a.find('div.select').attr('_scid', scheduleId).css({
			'background': color
		});
		this._gridData[row][cell] = {
			'row': row,
			'user': userName,
			'scid': scheduleId
		};
	},
	
	/**
	 *
	 */
	selectAll: function(){
		if (this.isAll()) {
			$(this._table).find('td').removeClass('enable');
			$(this._table).find('td div.select').attr('_scid', '').css({
				'background': ''
			});
		}
		else {
			var sc = this.getCurrentSchedule();
			$(this._table).find('td').addClass('enable');
			$(this._table).find('td div.select').attr('_scid', sc.scheduleId).css({
				'background': sc.color
			});
		}
	},
	
	/**
	 *
	 */
	isAll: function(){
		for (var i = 0; i < this._cfg.row; i++) {
			if (this.getRowSelected(i).length < this._cfg.cell) {
				return false;
			}
		}
		
		return true;
	},
	
	/**
	 * 是否批量操作
	 */
	isBatch: function(){
		var isBatch = false;
		if ($('input:checked[name="member[]"]').size() > 1) {
			isBatch = true;
		}
		
		return isBatch;
	},
	
	/**
	 * 获取批量操作行索引
	 */
	getBatchRow: function(){
		var rows = [];
		$('input[name="member[]"]').each(function(i){
			if ($(this).is(':checked')) {
				rows.push(i);
			}
		});
		
		return rows;
	},
	
	/**
	 *
	 */
	clearSelect: function(){
		$(this._table).find('td').removeClass('enable');
		$(this._table).find('td div.select').attr('_scid', '').css({
			'background': ''
		});
	},
	
	/**
	 *
	 */
	selectRow: function(index){
		var isAdd = this.getRowSelected(index).length != this._cfg.cell;
		if (!isAdd) {
			$(this._table).find('tr:eq(' + (index + 1) + ') td').removeClass('enable');
			$(this._table).find('tr:eq(' + (index + 1) + ') td div.select').attr('_scid', '').css({
				'background': ''
			});
		}
		else {
			var sc = this.getCurrentSchedule();
			$(this._table).find('tr:eq(' + (index + 1) + ') td').addClass('enable');
			$(this._table).find('tr:eq(' + (index + 1) + ') td div.select').attr('_scid', sc.scheduleId).css({
				'background': sc.color
			});
		}
		
		return isAdd;
	},
	
	unselectRow: function(index){
		$(this._table).find('tr:eq(' + (index + 1) + ') td').removeClass('enable');
		$(this._table).find('tr:eq(' + (index + 1) + ') td div.select').attr('_scid', '').css({
			'background': ''
		});
	},
	
	/**
	 *
	 */
	selectColumn: function(index){
		var me = this, isAdd = this.getColumnSelected(index).length != this._cfg.row, sc = me.getCurrentSchedule();
		if (!isAdd) {
			if ($(this._table).find('tr td:eq(' + index + ') div.select').attr('_scid') != sc.scheduleId) {
				isAdd = true;
			}
		}
		
		$(this._table).find('tr').each(function(r){
			var td = $(this).find('td:eq(' + index + ')');
			if ($(this).find('td').size()) {
				var userMember = $('input[name="user[]"]:eq(' + (r - 1) + ')').val(), userName = $('input[name="user-' + userMember + '"]').val();
				if (isAdd) {
					td.addClass('enable');
					td.find('div.select').attr('_scid', sc.scheduleId).css({
						'background': sc.color
					});
					me._gridData[r][index] = {
						'row': r,
						'user': userName,
						'scid': sc.scheduleId
					};
				} else {
					td.removeClass('enable');
					td.find('div.select').attr('_scid', '').css({
						'background': ''
					});
					me._gridData[r][index] = {
						'row': r,
						'user': userName,
						'scid': ''
					};
				}
			}
		});
		
		return isAdd;
	},
	
	unselectColumn: function(index){
		$(this._table).find('tr').each(function(){
			$(this).find('td:eq(' + index + ')').removeClass('enable');
			$(this).find('td:eq(' + index + ') div.select').attr('_scid', '').css({
				'background': ''
			});
		});
	},
	
	/**
	 *
	 */
	enabled: function(){
		this._enabled = true;
	},
	
	/**
	 *
	 */
	disabled: function(){
		this._enabled = false;
		this._start = false;
	},
	
	/**
	 *
	 */
	appendTo: function(parent){
		$(parent).append(this._table);
	}
};