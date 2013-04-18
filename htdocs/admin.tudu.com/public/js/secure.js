
var Secure = {};

/**
 * 登录
 */
Secure.Login = {
	
	/**
	 * 初始化系统安全登录设置页面
	 */
	init: function() {
		$('input[name="opean"]').click(function() {
	    	if ($(this).attr('checked') == true) {
				$('input[name="locktime"]')
				.val(3)
				.removeAttr('_disabled')
				.removeAttr('disabled')
				.focus();
	    	} else { 
	    		$('input[name="locktime"]')
	    		.disable()
	    		.attr('_disabled', '_diabled')
	    		.val(0);
	    	}
	    });

		if ($('input[name="opean"]').attr('checked') == true) {
			$('input[name="locktime"]').bind('keyup', function(){
				this.value = this.value.replace(/[^1-9]+/, '');
			})
			.blur(function(){
				$('input[name="locktime"]').val(this.value);
			});
		}
		
		new FixToolbar({
			target: 'div.tool-btm'
		});
		
		var grid = new SelectGrid({
			cls: 'timegrid',
			rowHeader: ['日', '一', '二', '三', '四', '五','六'],
			colHeader: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23],
			onChange: function() {
				this.eachRow(function(index){
					var arr = this.getRowSelected(index);
					var val = 0;
					for (var i = 0; i < arr.length; i++) {
						val = val | Math.pow(2, arr[i]);
					}
					$('input[name="wd-' + index + '"]').val(val);
				});
			}
		});
		grid.appendTo('#timelimit-div');
		
		$('input[name^="wd-"]').each(function(){
			var v = this.value ? parseInt(this.value) : 0,
				vl = null,
				index = this.name.replace('wd-', '');
			for (var i = 0; i < 24; i++) {
				vl = Math.pow(2, i);
				if ((v & vl) == vl) {
					grid.select(index, i);
				}
			}
		});
		
		$('#theform').submit(function() {return false;});
		$('#theform').submit(function() {

			Secure.Login.save("#theform");
		});
	},
	
	/**
	 * 提交保存
	 */
	save: function(form) {
		if ($('input[name="opean"]').attr('checked') == false) {
			$('input[name="locktime"]').val('0');
		}
		
		if ($('#istimelimit').attr('checked')) {
			var t = 0;
			$('input[name^="wd-"]').each(function(){
				t += parseInt(this.value);
			});
			
			if (t <= 0) {
				if (!confirm('确认要禁止所有时段的访问？')) {
					return false;
				}
			}
		}
		
		var form = $(form),
			data = form.serializeArray();
		form.disable();
		$.ajax({
	        type: 'POST',
	        dataType: 'json',
	        data: data,
	        url: form.attr('action'),
	        success: function(ret) {
				Message.show(ret.message, 5000, ret.success);
				form.enable();
	        },
	        error: function(res) {
	        	Message.show(Message.PROCESSING_ERROR);
	        	form.disable();
	        	return false;
	        }
	    });
	}
};

/**
 * 系统日志
 */
Secure.Log = {
	
	logWin: null,
	
	tpl: {
		win: '<table style=" line-height:20px;margin:8px 5px;"><tr><td align="right" valign="top" class="gray">操作时间：</td><td><div style="width:300px;" id="creatime" class="green"></div></td></tr><tr><td align="right" valign="top" class="gray">操作帐号：</td><td><div style="width:300px;" id="account" class="green"></div></td>	<tr><td align="right" valign="top" class="gray">来源IP：</td><td><div style="width:300px;" id="ip" class="green"></div></td></tr><tr><td align="right" valign="top" class="gray">操作模块：</td><td><div style="width:300px;" id="module" class="green"></div></td></tr><tr><td align="right" valign="top" class="gray">描述：</td><td><div style="width:300px;line-height:22px;word-break:break-all;word-wrap:break-word;" id="description" class="green"></div></td></tr></table>'
	},
	
	data: {},
	
	id: null,
	/**
	 * 初始化日志列表
	 */
	init: function(count) {
		var me = this;
		if (count > 0) {
			$('#log-list tr')
			.css('cursor', 'pointer')
			.each(function() {
				var o = $(this);
	
		        o.mousemove(function(){
		            o.addClass("over");
		        }).mouseout(function(){
		            o.removeClass("over");
		        });
			})
			.bind('click', function(){
				me.id = $(this).attr('id');
				Secure.Log.getData(parseInt(me.id));
				this.logWin = Secure.Log.showLogWin();
			});
		}
		
		$('#keywords').bind('keyup', function(e){
			this.value = this.value.replace(/[^\x00-\xff]+/, '');
			var keyCode = e.keyCode ? e.keyCode : e.which;
			if (keyCode == 13) {
				Secure.Log.seachLog();
			}
		})
		.blur(function(){
			$('#keywords').val(this.value);
		});
		
		if (UI.SingleSelect) {
			var ms = new UI.SingleSelect({
				select: '#module',
				id: 'module-select',
				cls: 'select',
				css: {width: '80px'},
				menuCls: 'option'
			});
			ms.appendTo($('#module').parent());
			
			var fms = new UI.SingleSelect({
				select: '#f-module',
				id: 'f-module-select',
				cls: 'select',
				css: {width: '80px'},
				menuCls: 'option'
			});
			fms.appendTo($('#f-module').parent());
			
			fms.bind('change', function(){
				ms.select(this.getValue());
			});
			ms.bind('change', function(){
				fms.select(this.getValue());
			});
		}
		
		$('#starttime').datepick({
	        showOtherMonths: true,
	        selectOtherMonths: true,
	        showAnim: 'slideDown',
	        showSpeed: 'fast',
	        width:'183px',
	        firstDay: 0,
	        onSelect: function(dates){
				$('#endtime').datepick('option', {minDate: dates});
			}
	    });
		
	    $('#endtime').datepick({
	        showOtherMonths: true,
	        selectOtherMonths: true,
	        showAnim: 'slideDown',
	        showSpeed: 'fast',
	        width:'183px',
	        firstDay: 0,
	        onSelect: function(dates){
	    		$('#starttime').datepick('option', {maxDate: dates});
	    	}
	    });
	    
	    $('#f-starttime').datepick({
	        showOtherMonths: true,
	        selectOtherMonths: true,
	        showAnim: 'slideDown',
	        showSpeed: 'fast',
	        width:'183px',
	        firstDay: 0,
	        onSelect: function(dates){
				$('#f-endtime').datepick('option', {minDate: dates});
			}
	    });
		
	    $('#f-endtime').datepick({
	        showOtherMonths: true,
	        selectOtherMonths: true,
	        showAnim: 'slideDown',
	        showSpeed: 'fast',
	        width:'183px',
	        firstDay: 0,
	        onSelect: function(dates){
	    		$('#f-starttime').datepick('option', {maxDate: dates});
	    	}
	    });
	},
	
	/**
	 * 显示详细日志窗口
	 */
	showLogWin: function() {
		var me = this;
		
		if (null === me.logWin) {
			me.logWin = Admin.window({
	    		width: 400,
	    		id: 'logwin',
	    		title: '后台操作日志',
	    		body: me.tpl.win,
	    		form: false,
	    		footer: '<input name="close" type="button" class="btn" value="确定" />',
	    		draggable: true,
	    		onShow: function() {
					Secure.Log.writeData();
				},
	    		onClose: function() {
					me.logWin.destroy();
					me.logWin = null;
				},
	    		init: function() {
					var obj = Frame.queryParent('#logwin');
					/*
					obj.find('input[name="prev"]').click(function() {
						Secure.Log.getData(parseInt(me.id) - 1);
						Secure.Log.writeData();
	    		    });
					
					obj.find('input[name="next"]').click(function() {
						Secure.Log.getData(parseInt(me.id) + 1);
						Secure.Log.writeData();
	    		    });*/
					
	    		    this.find('input[name="close"]').click(function() {
	    		    	me.logWin.close();
	    		    	me.id = null;
	    		    });
	    		}
	    	});
		}
		me.logWin.show();
	},
	
	/**
	 * 获取表格某行数据
	 */
	getData: function(id) {
		var me = this,
			obj = $('#log-list tr');
		me.data['creatime'] = obj.eq(id).find('td').eq(0).text();
		me.data['userid'] = obj.eq(id).find('td').eq(1).text();
		me.data['ip'] = obj.eq(id).find('td').eq(2).text();
		me.data['local'] = obj.eq(id).find('td').eq(3).text();
		me.data['module'] = obj.eq(id).find('td').eq(4).text();
		me.data['description'] = obj.eq(id).find('td').eq(5).text();
		//me.id = id;
	},
	
	/**
	 * 向窗口写入新数据
	 */
	writeData: function() {
		var me = this,
			obj = Frame.queryParent('#logwin');
		if (null !== me.data) {
			obj.find('#creatime').html(me.data['creatime']);
			obj.find('#account').html(me.data['userid']);
			obj.find('#ip').html(me.data['ip'] + '&nbsp;(' + me.data['local'] + ')');
			obj.find('#module').html(me.data['module']);
			obj.find('#description').html(me.data['description']);
		}
		/*
		if (me.id <= 0) {
			obj.find('input[name="prev"]').attr('disabled', true);
    	} else {
    		obj.find('input[name="prev"]').attr('disabled', false);
    	}
		if (me.id >= $('#log-list tr').size() - 1) {
			obj.find('input[name="next"]').attr('disabled', true);
		} else {
			obj.find('input[name="next"]').attr('disabled', false);
		}*/
	}
};

/**
 * 前台登录日志
 */
Secure.loginLog = {
	
	init: function(count) {
		if (count > 0) {
			$('#log-list tr')
			.css('cursor', 'pointer')
			.each(function() {
				var o = $(this);
	
		        o.mousemove(function(){
		            o.addClass("over");
		        }).mouseout(function(){
		            o.removeClass("over");
		        });
			});
		}
		
		$('#starttime').datepick({
	        showOtherMonths: true,
	        selectOtherMonths: true,
	        showAnim: 'slideDown',
	        showSpeed: 'fast',
	        width:'183px',
	        firstDay: 0,
	        onSelect: function(dates){
				$('#endtime').datepick('option', {minDate: dates});
			}
	    });
		
	    $('#endtime').datepick({
	        showOtherMonths: true,
	        selectOtherMonths: true,
	        showAnim: 'slideDown',
	        showSpeed: 'fast',
	        width:'183px',
	        firstDay: 0,
	        onSelect: function(dates){
	    		$('#starttime').datepick('option', {maxDate: dates});
	    	}
	    });
	    
	    $('#keywords').bind('keyup', function(e){
	    	var keyCode = e.keyCode ? e.keyCode : e.which;
			if (keyCode == 13) {
				Secure.Log.seachLog();
			}
	    });
	    $('button[name="search"]').click(function(){
	    	Secure.loginLog.seachLog();
	    });
	},
	
	/**
	 * 搜索日志
	 */
	seachLog: function() {
		var keywords = $('#keywords').val().replace(/^\s+|\s+$/, ''),
			starttime = $('#starttime').val(),
			endtime = $('#endtime').val();
		
		if (!keywords && !starttime && !endtime) {
			return location.href = BASE_PATH + '/secure/log/login';
		}
		
		location.href = BASE_PATH + '/secure/log/login?keywords=' + keywords + '&starttime=' + starttime + '&endtime=' + endtime;
	}
};

/**
 * IP地址过滤
 */
Secure.Ip = {
	
	selectStart: null,
		
	/**
	 * 初始化
	 */
	init: function() {
		var selector = new _TOP.UserSelector($('#exception-checkbox'), true, $, true);
		
		$('input.text').bind('focus blur mouseover mouseout', function(e) {
			if ($(this).is(':disabled')) {
				return ;
			}
			
			if (e.type == 'focus' || e.type == 'mouseover') {
				$(this).addClass('text-hover');
			} else {
				if (e.type == 'blur' || document.activeElement != this) {
					$(this).removeClass('text-hover');
				}
			}
		});

		$('#iptext').keyhint();
		
		$('#access-ip')[0].onselectstart = function(){return false;};
		$('#search-rs')[0].onselectstart = function(){return false;};
		
		if ($('#ipfilter').attr('checked') == true) {
			$('#iptext').removeAttr('disabled').removeAttr('_disabled');
			$('#add').removeAttr('disabled').removeAttr('_disabled');
			$('#exception').removeAttr('disabled').removeAttr('_disabled');
			$('input[name="search-ip"]').removeAttr('disabled').removeAttr('_disabled');
			$('label[for="exception"]').removeClass();
		}
		
		if ($('#exception').attr('checked') == true) {
			$('#exception-checkbox').show();
		}
		
		if (iprule.length > 0) {
			for (var i=0;i<iprule.length;i++) {
				var a = $('<a class="select-item" _ip="' + iprule[i] + '">' + iprule[i] + '<input name="iprule[]" type="hidden" value="' + iprule[i] + '" /></a>');
				a.bind('click', function(e){
					Secure.Ip.selected($(this).attr('_ip'), e);
				});
				$('#access-ip').prepend(a);
			}
			if ($('#ipfilter').attr('checked') == true) {
				$('#delete').attr('disabled', false);
			}
		}
		
		if (exception.length > 0) {
			var ex = new Array();
			for (var i = 0; i < exception.length; i++) {
				if (exception[i].indexOf('@') != -1) {
					ex = exception[i].split('@');
					selector.select(ex[0]);
				} else {
					selector.select('group_' + exception[i]);
				}
			}
		}
		
		$('#ipfilter').click(function(){
			if ($('#ipfilter').attr('checked') == true) {
				$('#iptext').removeAttr('disabled').removeAttr('_disabled');
				$('#add').removeAttr('disabled').removeAttr('_disabled');
				$('#delete').removeAttr('disabled').removeAttr('_disabled');
				$('#exception').removeAttr('disabled').removeAttr('_disabled');
				$('input[name="search-ip"]').removeAttr('disabled').removeAttr('_disabled');
				$('label[for="exception"]').removeClass();
				$('#iptext').focus();
				selector.enabled();
			} else {
				$('#iptext').attr({disabled: true, '_disabled': '_disabled'});
				$('#delete').attr({disabled: true, '_disabled': '_disabled'});
				$('#add').attr({disabled: true, '_disabled': '_disabled'});
				$('#exception').attr({disabled: true, '_disabled': '_disabled'});
				$('input[name="search-ip"]').attr({disabled: true, '_disabled': '_disabled'});
				$('label[for="exception"]').addClass('gray');
				selector.disabled();
			}
		});
		
		new FixToolbar({
			target: 'div.tool-btm'
		});
		
		$('#iptext').bind('keyup', function(event) {
            if (event.keyCode == "13") {
            	var ip = $('#iptext').val();
            	_writeIp(ip);
            }
            Util.stopEventBuddle(event);
		});
		$('#iptext').bind('keydown, keypress', function(event) {
			var keyCode = event.keyCode ? event.keyCode : event.which;
            Util.stopEventBuddle(event);
            if (keyCode == 13) {
            	event.preventDefault();
            }
		});
				
		$('#add').click(function(){
			var ip = $('#iptext').val();
			_writeIp(ip);
		});
		
		$('#delete').click(function(){
			var ip = [];
			if ($('#access-ip').is(':visible')) {
				$('#access-ip a').each(function(){
					if ($(this).hasClass('selected')){
						ip.push($(this).attr('_ip'));
					}
				});
			} else {
				$('#search-rs a').each(function(){
					if ($(this).hasClass('selected')){
						ip.push($(this).attr('_ip'));
					}
				});
			}
			
			if (ip == '') {
				return Message.show("你未选择要删除的IP"); 
			}
			
			for(var i=0;i<ip.length;i++){
				$('#access-ip').find('a[_ip="' + ip[i] + '"]').remove();
				if ($('#search-rs').find('a[_ip="' + ip[i] + '"]').size()){
					$('#search-rs').find('a[_ip="' + ip[i] + '"]').remove();
				}
			}
			
			if ($('#access-ip a').size() > 0) {
				$('#delete').attr('disabled', false);
			} else {
				$('#delete').attr('disabled', true);
			}
		});
		
		$('input[name="search-ip"]').bind('keyup', function(){
			Secure.Ip.search(this.value);
		});
		
		$('#exception').click(function() {
			if ($('#exception').attr('checked') == true) {
				$('#exception-checkbox').show();
			} else {
				if ($('input[name="userid[]"]').size() > 0 || $('input[name="groupid[]"]').size() > 0) {
					$('#exception').attr('checked', true)
					return Message.show("请先移除已选择的帐号及群组"); 
				}
				$('#exception-checkbox').hide();
			}
			
			window.scrollTo(0, document.body.offsetHeight);
		});
		
		$('#theform').submit(function() {return false;});
		$('#theform').submit(function() {
			Secure.Ip.save("#theform");
		});
		
		// 验证IP地址
		function _checkIp(ip) {
			if (!ip.length || ip == '请填写IP地址，支持通配符 “*”') {
				$('#iptext').focus();
				return Message.show('请输入IP地址');
			}
			var patrn = /^([0-9]{1,3}|[*])\.([0-9]{1,3}|[*])\.([0-9]{1,3}|[*])\.([0-9]{1,3}|[*])$/;
			if(!patrn.exec(ip)){ 
				return Message.show("您输入的IP格式不正确"); 
			}
			var checkip = new Array();
			checkip = ip.split('.');
			if(parseInt(checkip[0]) > 255 || parseInt(checkip[1]) > 255 || parseInt(checkip[2]) > 255 || parseInt(checkip[3]) > 255)
	        { 
				return Message.show("您输入的IP范围不正确，必须是0~255之间");
	        }
			
			if (parseInt(checkip[0]) == 127 && parseInt(checkip[1]) == 0 && parseInt(checkip[2]) == 0 && parseInt(checkip[3]) == 1) {
				alert('不允许添加此IP地址');
				return false;
			}
			
			if (parseInt(checkip[0]) == 0) {
				alert('不允许添加以“0”开头的IP地址');
				return false;
			}
			
			if(parseInt(checkip[0]) == 192 && parseInt(checkip[1]) == 168) { 
				if (!confirm("您输入的IP是IANA的保留IP（留作局域网使用）,确定添加此IP吗？")) {
					return false;
				}
	        }
			
			if (parseInt(checkip[0]) == 172 && (parseInt(checkip[1]) >= 16 && parseInt(checkip[1]) <= 31)) {
				if (!confirm("您输入的IP是IANA的保留IP（留作局域网使用）,确定添加此IP吗？")) {
					return false;
				}
			}
			
			if (parseInt(checkip[0]) == 10) {
				if (!confirm("您输入的IP是IANA的保留IP（留作局域网使用）,确定添加此IP吗？")) {
					return false;
				}
			}
			
			return true;
		}
		
		// 写入IP
		function _writeIp(ip) {
			if (_checkIp(ip) && _eqIp(ip)) {
				var a = $('<a class="select-item" _ip="' + ip + '" href="javascript:void(0);">' + ip + '<input name="iprule[]" type="hidden" value="' + ip + '" /></a>');
				a.bind('click', function(e){
					Secure.Ip.selected(ip, e);
				});
				
				$('#access-ip').prepend(a);
				$('#iptext').val('').focus();
				if ($('#access-ip a').size() > 0) {
					$('#delete').attr('disabled', false);
				}
			}
		}
		
		// 判断是否已经输入过的IP
		function _eqIp(ip) {
			var rs = [];
			$('#access-ip a').each(function(){
				rs.push($(this).attr('_ip'));
			});
			
			for (var i = 0; i < rs.length; i++) {
				if (rs[i] == ip) {
					return Message.show("您已经添加过此IP地址了");
				}
			}
			
			return true;
		}
	},
	
	/**
	 * 查找IP
	 */
	search: function(keyword) {
		if (!_TOP.Util.trim(keyword)) {
			$('#access-ip').show();
			return $('#search-rs').hide();
		}
		
		this.selectStart = null;
		
		var rs = [];
		$('#access-ip a').each(function(){
			rs.push($(this).attr('_ip'));
		});
		$('#search-rs').empty();
		for (var i = 0; i < rs.length; i++) {
			if (rs[i].indexOf(keyword) >= 0) {
				var a = $('<a class="select-item" _ip="' + rs[i] + '" href="javascript:void(0)">' + rs[i] + '<input name="iprule[]" type="hidden" value="' + rs[i] + '" /></a>');
				a.bind('click', function(e){
					//Secure.Ip.rsSelected($(this).attr('_ip'), e);
					if (a.hasClass('selected')) {
						Secure.Ip.unSelected($(this).attr('_ip'), e);
					} else {
						Secure.Ip.isSelected($(this).attr('_ip'), e);
					}
				});
				$('#search-rs').append(a);
			}
		}
		
		$('#access-ip').hide();
		$('#search-rs').show();
	},
	
	rsSelected: function(ip, e) {
		if (!$('#ipfilter:checked').size()) {
			return ;
		}
		
		var me = this;
		var a = $('#search-rs').find('a[_ip="' + ip + '"]');
		if (e) {
			if (e.shiftKey && this.selectStart && this.selectStart.attr('_ip') != ip) {
				var flag = null;
				$('#search-rs a').each(function(){
					var attrIp = $(this).attr('_ip');
					if (attrIp == me.selectStart.attr('_ip') || ip == attrIp) {
						if (flag === null) {
							flag = true;
						} else {
							flag = false;
						}
					}
					
					if (flag == true) {
						if (me.selectStart.hasClass('select')) {
							$('#search-rs').find('a[_ip="' + attrIp + '"]').addClass('selected');
						} else {
							$('#search-rs').find('a[_ip="' + attrIp + '"]').removeClass('selected');
						}
					}
				});
				
				if (me.selectStart.hasClass('select')) {
					$('#search-rs').find('a[_ip="' + ip + '"]').addClass('selected');
				} else {
					$('#search-rs').find('a[_ip="' + ip + '"]').removeClass('selected');
				}
				
				this.selectStart = a;
				
				return ;
			}
			
			if (!e.ctrlKey) {
				$('#search-rs a').removeClass('selected');
			}
		} else {
			$('#search-rs a').removeClass('selected');
		}
		
		if (e) {
			this.selectStart = a;
		}
		
		if (a.hasClass('selected')) {
			$('#search-rs').find('a[_ip="' + ip + '"]').removeClass('selected');
		} else {
			$('#search-rs').find('a[_ip="' + ip + '"]').addClass('selected');
		}
	},
	
	selected: function(ip, e) {
		if (!$('#ipfilter:checked').size()) {
			return ;
		}
		
		var me = this;
		var a = $('#access-ip').find('a[_ip="' + ip + '"]');
		if (e) {
			if (e.shiftKey && this.selectStart && this.selectStart.attr('_ip') != ip) {
				var flag = null;
				$('#access-ip a').each(function(){
					var attrIp = $(this).attr('_ip');
					if (attrIp == me.selectStart.attr('_ip') || ip == attrIp) {
						if (flag === null) {
							flag = true;
						} else {
							flag = false;
						}
					}
					
					if (flag == true) {
						if (me.selectStart.hasClass('selected')) {
							Secure.Ip.isSelected(attrIp);
						} else {
							Secure.Ip.unSelected(attrIp);
						}
					}
				});
				
				if (me.selectStart.hasClass('selected')) {
					Secure.Ip.isSelected(ip);
				} else {
					Secure.Ip.unSelected(ip);
				}
				
				this.selectStart = a;
				
				return ;
			}
			
			if (!e.ctrlKey) {
				$('#access-ip a').removeClass('selected');
			}
		} else {
			$('#access-ip a').removeClass('selected');
		}
		
		if (e) {
			this.selectStart = a;
		}
		
		if (a.hasClass('selected')) {
			Secure.Ip.unSelected(ip);
		} else {
			Secure.Ip.isSelected(ip);
		}
	},
	
	/**
	 * IP选中状态
	 */
	isSelected: function(ip) {
		$('#access-ip').find('a[_ip="' + ip + '"]').addClass('selected');
	},
	
	/**
	 * IP选中状态
	 */
	unSelected: function(ip) {
		$('#access-ip').find('a[_ip="' + ip + '"]').removeClass('selected');
	},
	
	/**
	 * 保存
	 */
	save: function(form) {
		var form = $(form),
			data = form.serializeArray();
		
		form.disable();
		$.ajax({
	        type: 'POST',
	        dataType: 'json',
	        data: data,
	        url: form.attr('action'),
	        success: function(ret) {
				Message.show(ret.message, 5000, ret.success);
				form.enable();
	        },
	        error: function(res) {
	        	Message.show(Message.PROCESSING_ERROR);
	        	form.enable();
	        	return false;
	        }
	    });
	}
};

/**
 * 
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
	rowHeader: [],
	colHeader: []
};
SelectGrid.prototype = {
	
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
	 * 初始化表格
	 */
	_initTable: function() {
		var r = this._cfg.row,
			c = this._cfg.cell;
		
		this._table = document.createElement('table');
		this._table.className = this._cfg.cls,
		this._table.cellPadding = 0,
		this._table.cellSpacing = 0,
		this._table.border = 0,
		this._table.onselectstart = function(){return false;};
		
		if (this._cfg.id) {
			this._table.id = this._cfg.id;
		}
		
		// 行列
		for (var i = 0; i <= r; i++) {
			var tr = this._table.insertRow(i);
			
			if (i == r) {
				tr.className += ' last';
			}
			if (i == 0) {
				tr.className += ' timegrid-time';
			}
			
			for (var j = 0; j <= c; j++) {
				var isth = (i == 0 || j == 0),
					cell = (isth ? document.createElement('th') : document.createElement('td'));
				
				tr.appendChild(cell);
				if (isth) {
					if (i == 0) {
						if (undefined != this._cfg.colHeader[j - 1]) {
							cell.innerHTML = '<div>' + this._cfg.colHeader[j - 1] + '</div>';
							cell._cell = j - 1;
						} else {
							cell.innerHTML = '<div align="right"><span class="time-angle"></span></div>';
						}
					} else {
						if (undefined != this._cfg.rowHeader[i - 1]) {
							cell.innerHTML = '<div>' + this._cfg.rowHeader[i - 1] + '</div>';
							cell._row = i - 1;
						}
					}
					
					if (j == 0) {
						cell.width = this._cfg.rowHeaderWidth;
					}
				} else {
					cell._row  = i - 1;
					cell._cell = j - 1;
					cell.appendChild(document.createElement('div'));
				}
				if (j == c) {
					cell.className = 'last';
				}
			}
		}
		
		var td = $(this._table).find('td'),
			colHeader = $(this._table).find('tr:eq(0) th:not(:eq(0))'),
			rowHeader = $(this._table).find('tr:not(:eq(0)) th');
		var me = this;
		td.bind('mousedown', function(){
			if (!me._enabled) {
				return ;
			}
			
			var d = $(this);
			me._start = {
				row: d.attr('_row'),
				cell: d.attr('_cell'),
				enable: !d.hasClass('enable')
			};
			$(this).toggleClass('enable');
			
		}).bind('mouseover', function(){
			if (!me._start) {
				$(this).addClass('over');
				return false;
			}
			
			var d = $(this),
				table = $(me._table),
				row = d.attr('_row'),
				cell = d.attr('_cell');
			
			var minRow = Math.min(me._start.row, row) + 1,
				maxRow = Math.max(me._start.row, row) + 1,
				minCell = Math.min(me._start.cell, cell),
				maxCell = Math.max(me._start.cell, cell);
			
			table.find('td div').removeClass();
			
			var r, c;
			var cls = me._start.enable ? 'enable' : 'disable';
			for (r = minRow; r <= maxRow; r++) {
				var mtr = table.find('tr:eq('+r+')');
				for (c = minCell; c <= maxCell; c++) {
					mtr.find('td:eq('+c+') div').addClass(cls);
				}
			}
		}).bind('mouseout', function(){
			$(this).removeClass('over');
		});
		
		$(this._table).find('tr:eq(0) th:eq(0)')
		.bind('mouseover', function(){$(this).addClass('over');})
		.bind('mouseout', function(){$(this).removeClass('over');})
		.bind('click', function(){me.selectAll();me.onChange();});
		
		colHeader.each(function(i){
			var o = $(this),
				col = o.attr('_cell');
			o.bind('mousedown', function(){
				var enable = me.selectColumn(i);
				
				me._startHeader = {
					type: 'col',
					index: col,
					enable: enable
				};
			})
			.bind('mouseover', function(){
				o.addClass('over');
				$(me._table).find('tr').each(function(){$(this).find('td:eq('+col+')').addClass('over');});
				
				var table = $(me._table);
				
				table.find('td div').removeClass();
				if (me._startHeader && me._startHeader.type == 'col') {
					var maxCol = Math.max(me._startHeader.index, col),
						minCol = Math.min(me._startHeader.index, col);
					var cls = me._startHeader.enable ? 'enable' : 'disable';
					for (var i = 1, c = me._cfg.row; i <= c; i++) {
						for (var j = minCol; j <= maxCol; j++) {
							table.find('tr:eq('+i+') td:eq('+j+') div').addClass(cls);
						}
					}
				}
			})
			.bind('mouseout', function(){
				o.removeClass('over');
				$(me._table).find('tr').each(function(){$(this).find('td:eq('+col+')').removeClass('over');});
			});
		});
		rowHeader.each(function(i){
			var o = $(this),
				parent = o.parent(),
				row = o.attr('_row');
			o.bind('mousedown', function(){
				var enable = me.selectRow(i);
				
				me._startHeader = {
					type: 'row',
					index: row,
					enable: enable
				};
			})
			.bind('mouseover', function(){
				o.addClass('over');
				parent.find('td').addClass('over');
				
				var table = $(me._table);
				
				table.find('td div').removeClass();
				if (me._startHeader && me._startHeader.type == 'row') {
					var maxRow = Math.max(me._startHeader.index, row) + 1,
						minRow = Math.min(me._startHeader.index, row) + 1;
					var cls = me._startHeader.enable ? 'enable' : 'disable';
					for (var i = minRow; i <= maxRow; i++) {
						for (var j = 0, c = me._cfg.cell; j < c; j++) {
							table.find('tr:eq('+i+') td:eq('+j+') div').addClass(cls);
						}
					}
				}
			})
			.bind('mouseout', function(){o.removeClass('over');parent.find('td').removeClass('over');});
		});
		
		$(window).bind('mouseup', _evtMouseUp);
		$(document.body).bind('mouseup', _evtMouseUp);
		
		function _evtMouseUp() {
			var isChange = me._start != null || me._startHeader != null,
			table    = $(me._table);
			me._start = null;
			me._startHeader = null;
			
			table.find('td div.enable').each(function(){
				$(this).parent('td').addClass('enable');
			});
			table.find('td div.disable').each(function(){
				$(this).parent('td').removeClass('enable');
			});
			table.find('td div').removeClass();
			
			if (isChange) {
				me.onChange();
			}
		}
	},
	
	onChange: function() {
		if (typeof this._cfg.onChange == 'function') {
			this._cfg.onChange.call(this);
		}
	},
	
	/**
	 * 遍历所有行
	 */
	eachRow: function(callback) {
		if (typeof (callback) != 'function') {
			return ;
		}
		
		var index = 0, me = this;
		$(this._table).find('tr:gt(0)').each(function(){
			callback.call(me, index)
			index ++;
		});
	},
	
	/**
	 * 获取指定行选定单元的索引数组
	 * 
	 * @param int index
	 * @return Array
	 */
	getRowSelected: function(index) {
		var ret = [];
		$(this._table).find('tr:eq('+(index + 1)+') td.enable').each(function(){
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
	getColumnSelected: function(index) {
		var ret = [];
		$(this._table).find('tr:gt(0)').each(function(i){
			var tr = $(this);
			if (tr.find('td:eq('+index+')').hasClass('enable')) {
				ret.push(i);
			}
		});
		
		return ret;
	},
	
	/**
	 * 选定
	 */
	select: function(row, cell) {
		$(this._table).find('td[_row="'+row+'"][_cell="'+cell+'"]').addClass('enable');
	},
	
	/**
	 * 
	 */
	selectAll: function() {
		if (this.isAll()) {
			$(this._table).find('td').removeClass('enable');
		} else {
			$(this._table).find('td').addClass('enable');
		}
	},
	
	/**
	 * 
	 */
	isAll: function() {
		for (var i = 0; i < this._cfg.row; i++) {
			if (this.getRowSelected(i).length < this._cfg.cell) {
				return false;
			}
		}
		
		return true;
	},
	
	/**
	 * 
	 */
	selectRow: function(index) {
		var isAdd = this.getRowSelected(index).length != this._cfg.cell;
		if (!isAdd) {
			$(this._table).find('tr:eq('+(index+1)+') td').removeClass('enable');
		} else {
			$(this._table).find('tr:eq('+(index+1)+') td').addClass('enable');
		}
		
		return isAdd;
	},
	
	/**
	 * 
	 */
	selectColumn: function(index) {
		var isAdd = this.getColumnSelected(index).length != this._cfg.row;
		
		$(this._table).find('tr').each(function(){
			if (isAdd) {
				$(this).find('td:eq('+index+')').addClass('enable');
			} else {
				$(this).find('td:eq('+index+')').removeClass('enable');
			}
		});
		
		return isAdd;
	},
	
	/**
	 * 
	 */
	enabled: function() {
		this._enabled = true;
	},
	
	/**
	 * 
	 */
	disabled: function() {
		this._enabled = false;
		this._start = false;
	},
	
	/**
	 * 
	 */
	appendTo: function(parent) {
		$(parent).append(this._table);
	}
};